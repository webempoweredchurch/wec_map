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
 * Service 'Geocoder.us Address Lookup' for the 'wec_map' extension.
 *
 * @author	Web-Empowered Church Team <map@webempoweredchurch.org>
 */


require_once(PATH_t3lib.'class.t3lib_svbase.php');

/**
 * Service providing lat/long lookup via the geocoder.us service.
 *
 * @author Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class tx_wecmap_geocode_geocoder extends t3lib_svbase {
	var $prefixId = 'tx_wecmap_geocode_geocoder';		// Same as class name
	var $scriptRelPath = 'geocode_service/class.tx_wecmap_geocode_geocoder.php';	// Path to this script relative to the extension dir.
	var $extKey = 'wec_map';	// The extension key.

	var $url = 'http://rpc.geocoder.us/service/rest?address=';


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

		switch($country) {
			case 'USA':
			case 'US':
			case 'U.S.':
			case 'U.S.A.':
			case 'United States':
			case 'United States of America':
				/* Keep it all if its the US. */
				break;
			default:
				return null;
				break;
		}


		$address = $street.', '.$city.', '.$state.' '.$zip;
		$address = str_replace(' ', '%20', $address);

		$xml = t3lib_div::getURL($this->url.'['.$address.']');
		$latlong = array();
		if($xml != "couldn't find this address! sorry") {
			$xml2arr = t3lib_div::xml2array($xml);

			// if $xml2arr is not an array, it couldn't be parsed
			if(!is_array($xml2arr)) {
				if (TYPO3_DLOG) t3lib_div::devLog('Geocoder.us: '.$address.': $xml2arr was no array.', 'wec_map_geocode', 3, $xml);
				if (TYPO3_DLOG) t3lib_div::devLog('Geocoder.us: '.$address.': Instead, it was:', 'wec_map_geocode', 3, $xml2arr);
				$latlong = null;
			} else {
				$latlong['lat'] = $xml2arr['geo:Point']['geo:lat'];
				$latlong['long'] = $xml2arr['geo:Point']['geo:long'];
				if (TYPO3_DLOG) t3lib_div::devLog('Geocoder.us: '.$address, 'wec_map_geocode', -1);
			}

		} else {
			if (TYPO3_DLOG) t3lib_div::devLog('Geocoder.us: '.$address, 'wec_map_geocode', 2);
			$latlong = null;
		}

		return $latlong;
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/geocode_service/class.tx_wecmap_geocode_geocoder.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/geocode_service/class.tx_wecmap_geocode_geocoder.php']);
}

?>