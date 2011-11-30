<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2009 Christian Technology Ministries International Inc.
* All rights reserved
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
 * Service 'Google Maps Address Lookup' for the 'wec_map' extension.
 *
 * @author	Web-Empowered Church Team <map@webempoweredchurch.org>
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
class tx_wecmap_geocode_google extends t3lib_svbase {
	var $prefixId = 'tx_wecmap_geocode_google';		// Same as class name
	var $scriptRelPath = 'geocode_service/class.tx_wecmap_geocode_google.php';	// Path to this script relative to the extension dir.
	var $extKey = 'wec_map';	// The extension key.

	/**
	 * Performs an address lookup using the geocoder.us web service.
	 *
	 * @param	string	The street address.
	 * @param	string	The city name.
	 * @param	string	The state name.
	 * @param	string	The ZIP code.
	 * @param	string	Optional API key for accessing third party geocoder.
	 * @return	array		Array containing latitude and longitude.  If lookup failed, empty array is returned.
	 */
	function lookup($street, $city, $state, $zip, $country, $key='')	{

		if(!$key) {
			$domainmgr = t3lib_div::makeInstance('tx_wecmap_domainmgr');
			$key = $domainmgr->getKey();
		}
		// $lookupstr = trim($street.' '.$city.', '.$state.' '.$zip.', '.$country);
        $lookupstr = utf8_encode( trim($street.','.$zip.' '.$city.','.$country) );
		$url = 'http://maps.google.com/maps/geo?'.
				$this->buildURL('q', $lookupstr).
				$this->buildURL('output', 'csv').
				$this->buildURL('key', $key);

		$csv = t3lib_div::getURL($url);
		$latlong = array();
		$csv = explode(',', $csv);
		if(TYPO3_DLOG)
		{
			$addressArray = array(
				'street' => $street,
				'city' => $city,
				'state' => $state,
				'zip' => $zip,
				'country' => $country,
			);
			$addressString = $url;
		}

		switch($csv[0]) {
			case 200:
				/*
				 * Geocoding worked!
				 * 200:  OK
				 */
				if (TYPO3_DLOG) t3lib_div::devLog('Google: '.$addressString, 'wec_map_geocode', -1, $addressArray);
				if (TYPO3_DLOG) t3lib_div::devLog('Google Answer', 'wec_map_geocode', -1, $csv);
				$latlong['lat'] = $csv[2];
				$latlong['long'] = $csv[3];
				break;
			case 500:
			case 610:
				/*
				 * Geocoder can't run at all, so disable this service and
				 * try the other geocoders instead.
				 * 500: Undefined error.  Geocoding may be blocked.
				 * 610: Bad API Key.
				 */
				if (TYPO3_DLOG) t3lib_div::devLog('Google: '.$csv[0].': '.$addressString.'. Disabling.', 'wec_map_geocode', 3, $addressArray);
				$this->deactivateService();
				$latlong = null;
				break;
			default:
				/*
				 * Something is wrong with this address. Might work for other
				 * addresses though.
				 * 601: No address to geocode.
				 * 602: Unknown address.
				 * 603: Can't geocode for contractual reasons.
				 */
				if (TYPO3_DLOG) t3lib_div::devLog('Google: '.$csv[0].': '.$addressString.'. Disabling.', 'wec_map_geocode', 2, $addressArray);
				$latlong = null;
				break;
		}

		return $latlong;
	}

	function buildURL($name, $value){
		if($value) {
			return $name.'='.urlencode($value).'&';
		}
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/geocode_service/class.tx_wecmap_geocode_google.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/geocode_service/class.tx_wecmap_geocode_google.php']);
}

?>
