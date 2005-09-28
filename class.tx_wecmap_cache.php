<?php
/***************************************************************
* Copyright notice
*
* (c) 2005 Foundation for Evangelism (info@evangelize.org)
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
 * Plugin 'Map' for the 'wec_map' extension.
 *
 * @author	Web Empowered Church Team <map@webempoweredchurch.org>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

/**
 * Main address lookup class for the wec_map extension.  Looks up existing
 * values in cache tables or initiates service chain to perform a lookup.
 *
 * @author Web Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class tx_wecmap_cache extends tslib_pibase {
	var $prefixId = 'tx_wecmap_cache';		// Same as class name
	var $scriptRelPath = 'class.tx_wecmap_cache.php';	// Path to this script relative to the extension dir.
	var $extKey = 'wec_map';	// The extension key.
	var $pi_checkCHash = TRUE;

	/*
	 * Looks up the latitude and longitude of a specified address. Cache tables
	 * are searched first, followed by external service lookups.
	 *
	 * @param	string		The street address.
	 * @param	string		The city name.
	 * @param	string		The state name.
	 * @param	string		This ZIP code.
	 * @return	array			Lat/long array for specified address.
	 */
	function lookup($street, $city, $state, $zip) {
		$latlong = array();
		
		/* Lookup the hashed current address in the cache table. */
		$address_hash = md5($street.' '.$city.' '.$state.' '.$zip);
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*", "tx_wecmap_cache", ' address_hash="'.$address_hash.'"');
		
		/* Found a cached match */	
		if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			$latlong['lat'] = $row['latitude'];
			$latlong['long'] = $row['longitude'];
		} else {
			/* Intiate service chain to find lat/long */
			$serviceChain='';
			while (is_object($lookupObj = t3lib_div::makeInstanceService('addressLookup', '', $serviceChain))) {
				$serviceChain.=','.$lookupObj->getServiceKey();
				$latlong = $lookupObj->lookup($street, $city, $state, $zip);
				
				/* If we found a match, quit. Otherwise proceed to next best service */
				if($latlong) {
					break;
				}
			}
			
			$insertArray = array();
			$insertArray['address_hash'] = $address_hash;
			$insertArray['latitude'] = $latlong['lat'];
			$insertArray['longitude'] = $latlong['long'];
			
			/* Write address to cache table */
			$result = $GLOBALS['TYPO3_DB']->exec_INSERTquery("tx_wecmap_cache", $insertArray);
		}
		
		/* Return the lat/long, either from cache table for from fresh lookup */
		return $latlong;
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_cache.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_cache.php']);
}

?>