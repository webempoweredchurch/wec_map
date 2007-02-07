<?php
/***************************************************************
* Copyright notice
*
* (c) 2007 Foundation for Evangelism (info@evangelize.org)
* All rights reserved
*
* This file is part of the Web-Empowered Church (WEC) ministry of the
* Foundation for Evangelism (http://evangelize.org). The WEC is developing 
* TYPO3-based free software for churches around the world. Our desire 
* use the Internet to help offer new life through Jesus Christ. Please
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
 * Service 'Yahoo! Maps Address Lookup' for the 'wec_map' extension.
 *
 * @author	Web-Empowered Church Team <map@webempoweredchurch.org>
 */


require_once(PATH_t3lib.'class.t3lib_svbase.php');

/**
 * Service providing lat/long lookup via the Yahoo! Maps web service.  
 *
 * @author Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class tx_wecmap_geocode_yahoo extends t3lib_svbase {
	var $prefixId = 'tx_wecmap_geocode_yahoo';		// Same as class name
	var $scriptRelPath = 'geocode_service/class.tx_wecmap_geocode_yahoo.php';	// Path to this script relative to the extension dir.
	var $extKey = 'wec_map';	// The extension key.
	var $applicationID = 'webempoweredchurch';
	
	/**
	 * Performs an address lookup using the geocoder.us web service.
	 *
	 * @param	string	The street address.
	 * @param	string	The city name.
	 * @param	string	The state name.
	 * @param	string	The ZIP code.
	 * @return	array		Array containing latitude and longitude.  If lookup failed, empty array is returned.
	 */
	function lookup($street, $city, $state, $zip, $country)	{
		
		if (!($country === "USA" or $country === "US" or $country === '')) {
			$zip = null;
			$state = null;
		}

		$url = 'http://api.local.yahoo.com/MapsService/V1/geocode?'.
				'appid='.$this->applicationID.'&'.
				$this->buildURL('street', $street).
				$this->buildURL('city', $city).
				$this->buildURL('state', $state).
				$this->buildURL('zip', $zip).
				$this->buildURL('country', $country);
														
		$xml = t3lib_div::getURL($url);
		
		if(TYPO3_DLOG) {
			$address = $address.', '.$city.' '.$state.' '.$zip.' '.$country;
		}

		if($xml !== false) {
			$latlong = array();
			$xml = t3lib_div::xml2array($xml);
				
			$latlong['lat'] = $xml['Result']['Latitude'];
			$latlong['long'] = $xml['Result']['Longitude'];
		}
		if (is_null($xml['Result']['Latitude']) or is_null($xml['Result']['Longitude'])) {
			if (TYPO3_DLOG) t3lib_div::devLog('Yahoo! geocode failed for '.$address, 'wec_map', 2);
			return null;
		} else {
			if (TYPO3_DLOG) t3lib_div::devLog('Yahoo! geocode succeeded for '.$address, 'wec_map', -1);		
			return $latlong;
		}
	}
	
	function buildURL($name, $value){
		if($value) {
			return $name.'='.str_replace(' ', '+', $value).'&';
		}
	}
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/geocode_service/class.tx_wecmap_geocode_yahoo.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/geocode_service/class.tx_wecmap_geocode_yahoo.php']);
}

?>