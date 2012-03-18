<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2009 Christian Technology Ministries International Inc.
* All rights reserved
* (c) 2011 Jan Bartels Google API V3
*
* This file is part of the Web-Empowered Church (WEC)
* (http://WebEmpoweredChurch.org) ministry of Christian Technology Ministries
* International (http://CTMIinc.org). The WEC is developing TYPO3-based
* (http://typo3.org) free software for churches around the world. Our desire
* is to use the Internet to help offer new life through Jesus Christ. Please
* see http://WebEmpoweredChurch.org/Jesus.
*
* You can redistribute this file and/or modify it under the terms of the
* GNU General Public License as published by the Free Software Foundation;
* either version 2 of the License, or (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This file is distributed in the hope that it will be useful for ministry,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the file!
***************************************************************/
/**
 * Service 'Google Maps V3 Address Lookup' for the 'wec_map' extension.
 *
 * @author	j.bartels
 */


require_once(PATH_t3lib.'class.t3lib_svbase.php');
require_once(t3lib_extMgm::extPath('wec_map').'class.tx_wecmap_domainmgr.php');

/**
 * Service providing lat/long lookup via the Google Maps web service.
 *
 * @author Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class tx_wecmap_geocode_google_v3 extends t3lib_svbase {
	var $prefixId = 'tx_wecmap_geocode_google_v3';		// Same as class name
	var $scriptRelPath = 'geocode_service/class.tx_wecmap_geocode_google_v3.php';	// Path to this script relative to the extension dir.
	var $extKey = 'wec_map';	// The extension key.

	/**
	 * Performs an address lookup using the google web service.
	 *
	 * @param	string	The street address.
	 * @param	string	The city name.
	 * @param	string	The state name.
	 * @param	string	The ZIP code.
	 * @param	string	Optional API key for accessing third party geocoder.
	 * @return	array		Array containing latitude and longitude.  If lookup failed, empty array is returned.
	 */
	function lookup($street, $city, $state, $zip, $country, $key='')	{


		if ( t3lib_extMgm::isLoaded('static_info_tables') )
		{
			// format address for Google search based on local address-format for given $country

			// load and init Static Info Tables
			require_once(t3lib_extMgm::extPath('static_info_tables').'class.tx_staticinfotables_div.php');

			// convert $country to ISO3
			$countryCodeType = tx_staticinfotables_div::isoCodeType($country);
			if       ($countryCodeType == 'nr') {
				$countryArray = tx_staticinfotables_div::fetchCountries('', '', '', $country);
			} elseif ($countryCodeType == '2') {
				$countryArray = tx_staticinfotables_div::fetchCountries('', $country, '', '');
			} elseif ($countryCodeType == '3') {
				$countryArray = tx_staticinfotables_div::fetchCountries('', '', $country, '');
			} else {
				$countryArray = tx_staticinfotables_div::fetchCountries($country, '', '', '');
			}

			if(TYPO3_DLOG) {
				t3lib_div::devLog('Google V3: countryArray for '.$country, 'wec_map_geocode', -1, $countryArray);
			}

			if ( is_array( $countryArray ) )
				$country = $countryArray[0]['cn_iso_3'];

			// format address accordingly
			$addressString = $this->formatAddress(',', $street, $city, $zip, $state, $country);  // $country: alpha-3 ISO-code (e. g. DEU)
			if(TYPO3_DLOG) {
				t3lib_div::devLog('Google V3 addressString', 'wec_map_geocode', -1, array( street => $street, city => $city, zip => $zip, state => $state, country => $country, addressString => $addressString ) );
			}
			if ( !$addressString )
				return array();
		}
		else
		{
			$addressString = $street.' '.$city.', '.$state.' '.$zip.', '.$country;	// default: US-format
			// $addressString = $street.','.$zip.' '.$city.','.$country;  			// Alternative German format for better search results
		}

		// build URL
		$lookupstr = trim( $addressString );
	  	# Google requires utf-8; convert query if neccessary
//	  	if ( $GLOBALS['TSFE']->renderCharset != 'utf-8' )
//    		$lookupstr = utf8_encode( $lookupstr );

		$url = 'http://maps.googleapis.com/maps/api/geocode/json?sensor=false&address=' . urlencode( $lookupstr );

		/*
		// Digital signatures for Premier Accounts not yet supported!
		if(!$key) {
			$domainmgr = t3lib_div::makeInstance('tx_wecmap_domainmgr');
			$key = $domainmgr->getKeyV3();
		}
		$url .= 'clientId=' . $clientId;
		$signature = modified_base64( hmac( ... $key ... $url ... ) )
		// see http://gmaps-samples.googlecode.com/svn/trunk/urlsigning/UrlSigner.php-source

		$url .= '&signature=' . $signature;
		*/

		// request Google-service and parse JSON-response
		if(TYPO3_DLOG) {
			t3lib_div::devLog('Google V3: URL '.$url, 'wec_map_geocode', -1 );
		}

		$jsonstr = t3lib_div::getURL($url);

		$response_obj = json_decode( $jsonstr, true );
		if(TYPO3_DLOG) {
			t3lib_div::devLog('Google V3: '.$jsonstr, 'wec_map_geocode', -1, $response_obj);
		}

		$latlong = array();
		if(TYPO3_DLOG) {
			$addressArray = array(
				'street' => $street,
				'city' => $city,
				'state' => $state,
				'zip' => $zip,
				'country' => $country,
			);
			t3lib_div::devLog('Google V3: '.$addressString, 'wec_map_geocode', -1, $addressArray);
		}

		if ( $response_obj['status'] == 'OK' )
		{
			/*
			 * Geocoding worked!
			 */
			if (TYPO3_DLOG) t3lib_div::devLog('Google V3 Answer successful', 'wec_map_geocode', -1 );
			$latlong['lat'] = $response_obj['results'][0]['geometry']['location']['lat'];
			$latlong['long'] = $response_obj['results'][0]['geometry']['location']['lng'];
			if (TYPO3_DLOG) t3lib_div::devLog('Google V3 Answer', 'wec_map_geocode', -1, $latlong);
		}
		else if (  $response_obj['status'] == 'REQUEST_DENIED'
		        || $response_obj['status'] == 'INVALID_REQUEST'
		        )
		{
			/*
			 * Geocoder can't run at all, so disable this service and
			 * try the other geocoders instead.
			 */
			if (TYPO3_DLOG) t3lib_div::devLog('Google V3: '.$response_obj['status'].': '.$addressString.'. Disabling.', 'wec_map_geocode', 3 );
			$this->deactivateService();
			$latlong = null;
		}
		else
		{
			/*
			 * Something is wrong with this address. Might work for other
			 * addresses though.
			 */
			if (TYPO3_DLOG) t3lib_div::devLog('Google V3: '.$response_obj['status'].': '.$addressString.'. Disabling.', 'wec_map_geocode', 2 );
			$latlong = null;
		}

		return $latlong;
	}


	/**
	 * Formatting an address in the format specified
	 *
	 * @param	string		A delimiter for the fields of the returned address
	 * @param	string		A street address
	 * @param	string		A city
	 * @param	string		A country subdivision code (zn_code)
	 * @param	string		A ISO alpha-3 country code (cn_iso_3)
	 * @param	string		A zip code
	 * @return	string		The formated address using the country address format (cn_address_format)
	 */
	function formatAddress ($delim, $streetAddress, $city, $zip, $subdivisionCode='', $countryCode='')	{

		if(TYPO3_MODE == 'FE')
		{
			require_once(t3lib_extMgm::extPath('static_info_tables').'pi1/class.tx_staticinfotables_pi1.php');
			$staticInfoObj = &t3lib_div::getUserObj('&tx_staticinfotables_pi1');
			if ($staticInfoObj->needsInit())
				$staticInfoObj->init();
			return $staticInfoObj->formatAddress($delim, $streetAddress, $city, $zip, $subdivisionCode, $countryCode);
		}

		$conf = $this->loadTypoScriptForBEModule('tx_staticinfotables_pi1');

		global $TYPO3_DB;

		$formatedAddress = '';
		$countryCode = ($countryCode ? trim($countryCode) : $this->defaultCountry);
		$subdivisionCode = ($subdivisionCode ? trim($subdivisionCode) : ($countryCode == $this->defaultCountry ? $this->defaultCountryZone : ''));

		// Get country name
//		$countryName = $this->getStaticInfoName('COUNTRIES', $countryCode);
		$countryName = tx_staticinfotables_div::getTitleFromIsoCode('static_countries', $countryCode, '', FALSE);
		if (!$countryName) {
			return $formatedAddress;
		}

			// Get address format
		$res = $TYPO3_DB->exec_SELECTquery(
			'cn_address_format',
			'static_countries',
			'cn_iso_3='.$TYPO3_DB->fullQuoteStr($countryCode,'static_countries')
		);
		$row = $TYPO3_DB->sql_fetch_assoc($res);
		$TYPO3_DB->sql_free_result($res);
		$addressFormat = $row['cn_address_format'];

			// Get country subdivision name
//		$countrySubdivisionName = $this->getStaticInfoName('SUBDIVISIONS', $subdivisionCode, $countryCode);
		$countrySubdivisionName = tx_staticinfotables_div::getTitleFromIsoCode('static_country_zones', $subdivisionCode, $countryCode, FALSE);

		// Format the address
		$formatedAddress = $conf['addressFormat.'][$addressFormat];
		$formatedAddress = str_replace('%street', $streetAddress, $formatedAddress);
		$formatedAddress = str_replace('%city', $city, $formatedAddress);
		$formatedAddress = str_replace('%zip', $zip, $formatedAddress);
		$formatedAddress = str_replace('%countrySubdivisionCode', $subdivisionCode, $formatedAddress);
		$formatedAddress = str_replace('%countrySubdivisionName', $countrySubdivisionName, $formatedAddress);
		$formatedAddress = str_replace('%countryName', strtoupper($countryName), $formatedAddress);
		$formatedAddress = implode($delim, t3lib_div::trimExplode(';', $formatedAddress, 1));

		return $formatedAddress;
	}


	/**
	 * Loads the TypoScript for the given extension prefix, e.g. tx_cspuppyfunctions_pi1, for use in a backend module.
	 *
	 * @param string $extKey
	 * @return array
	 */
	function loadTypoScriptForBEModule($extKey) {
		require_once(PATH_t3lib . 'class.t3lib_page.php');
		require_once(PATH_t3lib . 'class.t3lib_tstemplate.php');
		require_once(PATH_t3lib . 'class.t3lib_tsparser_ext.php');
		list($page) = t3lib_BEfunc::getRecordsByField('pages', 'pid', 0);
		$pageUid = intval($page['uid']);
		$sysPageObj = t3lib_div::makeInstance('t3lib_pageSelect');
		$rootLine = $sysPageObj->getRootLine($pageUid);
		$TSObj = t3lib_div::makeInstance('t3lib_tsparser_ext');
		$TSObj->tt_track = 0;
		$TSObj->init();
		$TSObj->runThroughTemplates($rootLine);
		$TSObj->generateConfig();
		return $TSObj->setup['plugin.'][$extKey . '.'];
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/geocode_service/class.tx_wecmap_geocode_google_v3.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/geocode_service/class.tx_wecmap_geocode_google_v3.php']);
}

?>
