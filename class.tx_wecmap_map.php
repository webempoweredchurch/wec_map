<?php
/***************************************************************
* Copyright notice
*
* (c) 2005 Foundation for Evangelism
* All rights reserved
*
* This file is part of the Web-Empowered Church (WEC)
* (http://webempoweredchurch.org) ministry of the Foundation for Evangelism
* (http://evangelize.org). The WEC is developing TYPO3-based
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
 * Plugin 'Map' for the 'wec_map' extension.
 *
 * @author	Web Empowered Church Team <map@webempoweredchurch.org>
 */


/*
define('PATH_tslib', t3lib_extMgm::extPath('cms').'tslib/');
require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(PATH_tslib.'class.tslib_content.php');
*/
require_once('class.tx_wecmap_marker.php');
// require_once('map_service/google/class.tx_wecmap_marker_google.php');
// require_once('map_service/google/class.tx_wecmap_map_google.php');


/**
 * Main class for the wec_map extension.  This class sits between the various 
 * frontend plugins and address lookup service to render map data.
 * 
 * @author Web Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class tx_wecmap_map {
	var $prefixId = 'tx_wecmap_map';		// Same as class name
	var $scriptRelPath = 'class.tx_wecmap_map.php';	// Path to this script relative to the extension dir.
	var $extKey = 'wec_map';	// The extension key.
	var $pi_checkCHash = TRUE;

	var $lat;
	var $long;
	var $zoom;
	var $markers;
	var $width;
	var $height;
				
	var $js;
	var $key;
	
	/* 
	 * Class constructor.  Creates javscript array.
	 * @param	string		The Google Maps API Key
	 * @param	string		The latitude for the center point on the map.
	 * @param 	string		The longitude for the center point on the map.
	 * @param	string		The initial zoom level of the map.
	 */
	function tx_wecmap_map($key, $width=250, $height=250, $lat='', $long='', $zoom='') {
		$this->js = array();
		$this->markers = array();
		$this->key = $key;

		$this->width = $width;
		$this->height = $height;
		
		if ($lat != '' || $long != '') {
			$this->setCenter($lat, $long);
		}
		if ($zoom != '') {
			$this->setZoom($zoom);
		}
		
	}
	
	function autoCenterAndZoom(){}
	
	
	
	function drawMap() {}
	
	
	/*
	 * Sets the center value for the current map to specified values.
	 *
	 * @param	string	The latitude for the center point on the map.
	 * @param	string	The longitude for the center point on the map.
	 * @return	void		No return value needed.  Changes made to object model.
	 */
	function setCenter($lat, $long) {
		$this->lat  = $lat;
		$this->long = $long;
		$this->zoom = $zoom;
	}
	
	/*
	 * Sets the zoom value for the current map to specified values.
	 *
	 * @param	string	The initial zoom level for the map.
	 * @return	void		No return value needed.  Changes made to object model.
	 */
	function setZoom($zoom) {
		$this->zoom = $zoom;
	}
	
	function getLatLongBounds() {
		$minLat = 360;
		$maxLat = -360;
		$minLong = 360;
		$maxLong = -360;
		
		/* Find min and max zoom lat and long */		
		foreach($this->markers as $marker) {			
			if ($marker->getLatitude() < $minLat) 
				$minLat = $marker->getLatitude();
			if ($marker->getLatitude() > $maxLat) 
				$maxLat = $marker->getLatitude();
			
			if ($marker->getLongitude() < $minLong) 
				$minLong = $marker->getLongitude();
			if ($marker->getLongitude() > $maxLong) 
				$maxLong = $marker->getLongitude();
		}

		/* If we only have one point, expand the boundaries slightly to avoid
		   inifite zoom value */
		if ($maxLat == $minLat) {
			$maxLat = $maxLat + 0.001;
			$minLat = $minLat - 0.001;
		}
		if ($maxLong == $minLong) {
			$maxLong = $maxLong + 0.001;
			$minLat = $minLat - 0.001;
		}
		
		return array("maxLat" => $maxLat, "maxLong" => $maxLong, "minLat" => $minLat, "minLong" => $minLong);
	}
	
	/*
	 * Adds an address to the currently list of markers rendered on the map.
	 *
	 * @param	string	The street address.
	 * @param	string	The city name.
	 * @param	string	The state or province.
	 *	@param	string	The ZIP code.
	 * @param	string	The description to be displayed in the marker popup.
	 * @return	void		No return needed.  Address added to marker object.
	 */
	function addMarkerByAddress($street, $city, $state, $zip, $country, $title='', $description='') {		
		
		/* Geocode the address */
		include_once(t3lib_extMgm::extPath('wec_map').'class.tx_wecmap_cache.php');
		$lookupTable = t3lib_div::makeInstance("tx_wecmap_cache");
		$latlong = $lookupTable->lookup($street, $city, $state, $zip, $country);
		
		/* Create a marker at the specified latitude and longitdue */
		$this->addMarkerByLatLong($latlong['lat'], $latlong['long'], $title, $description);	
	}
	
	/*
	 * Adds a lat/long to the currently list of markers rendered on the map.
	 *
	 * @param	double	The latitude.
	 * @param	double	The longitude.
	 * @param	string	The description to be displayed in the marker popup.
	 * @return	void		No return needed.  Lat/long added to marker object.
	 */
	function addMarkerByLatLong($lat, $long, $title='', $description='') {		
		$latlong = array();
		$latlong['lat'] = $lat;
		$latlong['long'] = $long;
		
		if($latlong['lat']!='' && $latlong['long']!='') {
			$classname = t3lib_div::makeInstanceClassname($this->getMarkerClassName());
			$this->markers[] = new $classname(count($this->markers), 
											  $latlong['lat'], 
											  $latlong['long'], 
											  $title, 
											  $description);
		}
	}
	
	function getMarkerClassName() {
		return $this->markerClassName;
	}
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_map.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_map.php']);
}


?>