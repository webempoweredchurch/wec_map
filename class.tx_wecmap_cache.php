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

require_once (PATH_t3lib.'class.t3lib_refindex.php');

/**
 * Main address lookup class for the wec_map extension.  Looks up existing
 * values in cache tables or initiates service chain to perform a lookup.
 * Also provides basic administrative functions for managing entries in the 
 *
 * @author Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class tx_wecmap_cache {

	/*
	 * Looks up the latitude and longitude of a specified address. Cache tables
	 * are searched first, followed by external service lookups.
	 *
	 * @param	string		The street address.
	 * @param	string		The city name.
	 * @param	string		The state name.
	 * @param	string		This ZIP code.
	 * @param	string		The country name.
	 * @param	boolean		Force a new lookup for address.
	 * @return	array		Lat/long array for specified address.  Null if lookup fails.
	 */
	function lookup($street, $city, $state, $zip, $country, $key='', $forceLookup=false) {

		/* Lookup the hashed current address in the cache table. */	
		$latlong = tx_wecmap_cache::find($street, $city, $state, $zip, $country);
		
		/* Didn't find a cached match */	
		if (is_null($latlong)) {
			/* Intiate service chain to find lat/long */
			$serviceChain='';
			while (is_object($lookupObj = t3lib_div::makeInstanceService('geocode', '', $serviceChain))) {
				$serviceChain.=','.$lookupObj->getServiceKey();
				$latlong = $lookupObj->lookup($street, $city, $state, $zip, $country, $key);				
				
				/* If we found a match, quit. Otherwise proceed to next best service */
				if($latlong) {
					break;
				}
			}

			tx_wecmap_cache::insert($street, $city, $state, $zip, $country, $latlong['lat'], $latlong['long']);
		}
		
		/* Return the lat/long, either from cache table for from fresh lookup */
		if ($latlong['lat'] == 0 and $latlong['long'] == 0){
			return null;
		} else {
			return $latlong;
		}
	}
	
	
	
	/*
	 * Returns the current geocoding status.  Geocoding may be successfull, failed, or may
	 * not have been attempted.
	 *
	 * @param	string		The street address.
	 * @param	string		The city name.
	 * @param	string		The state name.
	 * @param	string		This ZIP code.
	 * @param	string		The country name.
	 * @return	integer		Status code. -1=Failed, 0=Not Completed, 1=Successfull.
	 */
	function status($street, $city, $state, $zip, $country) {
		//debug("checking status...", "tx_wecmap_cache");
		
		$latlong = tx_wecmap_cache::find($street, $city, $state, $zip, $country);
		
		/* Found a cached match */	
		if ($latlong) {
			if($latlong['lat']==0 and $latlong['long']==0) {
				$statusCode = -1; /* Previous lookup failed */
			} else {			
				$statusCode = 1; /* Previous lookup succeeded */
			}
		} else {
			$statusCode = 0; /* Lookup has not been performed */
		}
		
		return $statusCode;
	}
	
	
	
	/*
	 * Looks up the latitude and longitude of a specified address in the cache table only.
	 *
	 * @param	string		The street address.
	 * @param	string		The city name.
	 * @param	string		The state name.
	 * @param	string		This ZIP code.
	 * @param	string		The country name.
	 * @return	array		Lat/long array for specified address.  Null if lookup fails.
	 */
	function find($street, $city, $state, $zip, $country) {	
		$hash = tx_wecmap_cache::hash($street, $city, $state, $zip, $country);
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*", "tx_wecmap_cache", ' address_hash="'.$hash.'"');
		if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
			return array('lat' => $row['latitude'], 'long' => $row['longitude']);
		} else {
			return null;
		}
	}
	
	
	/*
	 * Inserts an address with a specified latitude and longitdue into the cache table.
	 *
	 * @param	string		The street address.
	 * @param	string		The city name.
	 * @param	string		The state name.
	 * @param	string		This ZIP code.
	 * @param	string		The country name.
	 * @param	string		Latidude.
	 * @param	string		Longitude.
	 * @return	none		
	 */
	function insert($street, $city, $state, $zip, $country, $lat, $long) {		
		/* Check if value is already in DB */
		if (tx_wecmap_cache::find($street,$city,$state,$zip,$country)) {
			/* Update existing entry */
			$latlong = array("latitude" => $lat, "longitude" => $long);
			$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery("tx_wecmap_cache", "address_hash='".tx_wecmap_cache::hash($street, $city, $state, $zip, $country)."'", $latlong);
		} else {
			/* Insert new entry */
			$insertArray = array();
			$insertArray['address_hash'] = tx_wecmap_cache::hash($street, $city, $state, $zip, $country);
			$insertArray['address'] = $street.' '.$city.' '.$state.' '.$zip.' '.$country;
			$insertArray['latitude'] = $lat;
			$insertArray['longitude'] = $long;
		
			/* Write address to cache table */
			$result = $GLOBALS['TYPO3_DB']->exec_INSERTquery("tx_wecmap_cache", $insertArray);
		}		
	} 
	
	function updateByUID($uid, $lat, $long) {
		$latlong = array("latitude" => $lat, "longitude" => $long);
		$result = $GLOBALS['TYPO3_DB']->exec_UPDATEquery("tx_wecmap_cache", "address_hash='".$uid."'", $latlong);
	}
	
	function deleteByUID($uid) {
		$result = $GLOBALS['TYPO3_DB']->exec_DELETEquery("tx_wecmap_cache", "address_hash='".$uid."'");
	}
	
	function deleteAll() {
		$result = $GLOBALS['TYPO3_DB']->exec_DELETEquery("tx_wecmap_cache","");
	}
	
	/*
	 * Deletes a specified address from the cache table.
	 *
	 * @param	string		The street address.
	 * @param	string		The city name.
	 * @param	string		The state name.
	 * @param	string		This ZIP code.
	 * @param	string		The country name.
	 * @return	none
	 */
	function delete($street, $city, $state, $zip, $country) {
		$result = $GLOBALS['TYPO3_DB']->exec_DELETEquery("tx_wecmap_cache", "address_hash='".tx_wecmap_cache::hash($street, $city, $state, $zip, $country)."'");
	}
	
	/*
	 * Creates the address hash, which acts as a unique identifier for the cache table.
	 *
	 * @param	string		The street address.
	 * @param	string		The city name.
	 * @param	string		The state name.
	 * @param	string		This ZIP code.
	 * @param	string		The country name.
	 * @return	string		MD5 hash of the address.
	 */
	function hash($street, $city, $state, $zip, $country) {
		$address_string = $street.' '.$city.' '.$state.' '.$zip.' '.$country;
		return md5($address_string);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_cache.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_cache.php']);
}

?>