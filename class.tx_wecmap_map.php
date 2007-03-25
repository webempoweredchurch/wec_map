<?php
/***************************************************************
* Copyright notice
*
* (c) 2007 Foundation For Evangelism (info@evangelize.org)
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

require_once(t3lib_extMgm::extPath('wec_map').'class.tx_wecmap_marker.php');
require_once(t3lib_extMgm::extPath('wec_map').'class.tx_wecmap_cache.php');

/**
 * Main class for the wec_map extension.  This class sits between the various 
 * frontend plugins and address lookup service to render map data.  All map
 * services implement this abstract class.
 * 
 * @author Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class tx_wecmap_map {
	var $lat;
	var $long;
	var $zoom;
	var $markers;
	var $width;
	var $height;
				
	var $js;
	var $key;
	
	/** 
	 * Class constructor stub.  Override in the map_service classes. Look there for
	 * examples.
	 */
	function tx_wecmap_map() {}
	
	/**
	 * Stub for the drawMap function.  Individual map services should implement
	 * this method to output their own HTML and Javascript.
	 * 
	 */
	function drawMap() {}
	
	
	/**
	 * Stub for the autoCenterAndZoom function.  Individual map services should
	 * implement this method to perform their own centering and zooming based
	 * on map attributes.
	 */
	function autoCenterAndZoom(){}
	
	/**
	 * Calculates the center and lat/long spans from the current markers.
	 *
	 * @access	private
	 * @return	array		Array of lat/long center and spans.  Array keys
	 *						are lat, long, latSpan, and longSpan.
	 */
	function getLatLongData() {
		$latlong = $this->getLatLongBounds();
		
		$minLat = $latlong['minLat'];
		$maxLat = $latlong['maxLat'];
		$minLong = $latlong['minLong'];
		$maxLong = $latlong['maxLong'];
		
		/* Calculate the span of the lat/long boundaries */
		$latSpan = $maxLat-$minLat;
		$longSpan = $maxLong-$minLong;
		
		/* Calculate center lat/long based on boundary markers */
		$lat = ($minLat + $maxLat) / 2;
		$long = ($minLong + $maxLong) / 2;
		
		return array(
			'lat' => $lat, 
			'long' => $long,
			'latSpan' => $latSpan,
			'longSpan' => $longSpan,
		);
		
	}
		
	
	/*
	 * Sets the center value for the current map to specified values.
	 *
	 * @param	float		The latitude for the center point on the map.
	 * @param	float		The longitude for the center point on the map.
	 * @return	none
	 */
	function setCenter($lat, $long) {
		$this->lat  = $lat;
		$this->long = $long;
		$this->zoom = $zoom;
	}
	
	/**
	 * Sets the zoom value for the current map to specified values.
	 *
	 * @param	integer		The initial zoom level for the map.
	 * @return	none
	 */
	function setZoom($zoom) {
		$this->zoom = $zoom;
	}
	
	/**
	 * Calculates the bounds for the latitude and longitude based on the
	 * defined markers.
	 *
	 * @return	array	Array of minLat, minLong, maxLat, and maxLong.
	 */
	function getLatLongBounds() {
		$minLat = 360;
		$maxLat = -360;
		$minLong = 360;
		$maxLong = -360;
		
		/* Find min and max zoom lat and long */		
		foreach($this->markers as $key => $markers) {			
			foreach( $markers as $marker ) {
				if ($marker->getLatitude() < $minLat) 
					$minLat = $marker->getLatitude();
				if ($marker->getLatitude() > $maxLat) 
					$maxLat = $marker->getLatitude();

				if ($marker->getLongitude() < $minLong) 
					$minLong = $marker->getLongitude();
				if ($marker->getLongitude() > $maxLong) 
					$maxLong = $marker->getLongitude();
			}
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
		
		return array('maxLat' => $maxLat, 'maxLong' => $maxLong, 'minLat' => $minLat, 'minLong' => $minLong);
	}
	
	/**
	 * Adds an address to the currently list of markers rendered on the map.
	 *
	 * @param	string		The street address.
	 * @param	string		The city name.
	 * @param	string		The state or province.
	 * @param	string		The ZIP code.
	 * @param	string		The country name.
	 * @param	string		The title for the marker popup.
	 * @param	string		The description to be displayed in the marker popup.
	 * @param	integer		Minimum zoom level for marker to appear.
	 * @param	integer		Maximum zoom level for marker to appear.
	 * @return	none
	 * @todo	Zoom levels are very Google specific.  Is there a generic way to handle this?
	 */
	function addMarkerByAddress($street, $city, $state, $zip, $country, $title='', $description='', $minzoom = 0, $maxzoom = 17) {

		/* Geocode the address */
		$lookupTable = t3lib_div::makeInstance('tx_wecmap_cache');
		$latlong = $lookupTable->lookup($street, $city, $state, $zip, $country, $this->key);
 
		/* Create a marker at the specified latitude and longitdue */
		$this->addMarkerByLatLong($latlong['lat'], $latlong['long'], $title, $description, $minzoom, $maxzoom);	
	}
	
	
	/**
	 * Adds a lat/long to the currently list of markers rendered on the map.
	 *
	 * @param	float		The latitude.
	 * @param	float		The longitude.
	 * @param	string		The title for the marker popup.
	 * @param	string		The description to be displayed in the marker popup.
	 * @param	integer		Minimum zoom level for marker to appear.
	 * @param	integer		Maximum zoom level for marker to appear.
	 * @return	none
	 * @todo	Zoom levels are very Google specific.  Is there a generic way to handle this?
	 */
	function addMarkerByLatLong($lat, $long, $title='', $description='', $minzoom = 0, $maxzoom = 17) {		
		$latlong = array();
		$latlong['lat'] = $lat;
		$latlong['long'] = $long;
		
		if($latlong['lat']!='' && $latlong['long']!='') {
			$classname = t3lib_div::makeInstanceClassname($this->getMarkerClassName());
			$this->markers[$minzoom.':'.$maxzoom][] = new $classname(count($this->markers), 
											  $latlong['lat'], 
											  $latlong['long'], 
											  $title, 
											  $description,
											  $this->prefillAddress);
		}
	}
	
	/**
	 * Adds an address string to the current list of markers rendered on the map.
	 *
	 * @param	string		The full address string.
	 * @param	string		The title for the marker popup.
	 * @param	string		The description to be displayed in the marker popup.
	 * @param	integer		Minimum zoom level for marker to appear.
	 * @param	integer		Maximum zoom level for marker to appear.
	 * @return	none
	 * @todo	Zoom levels are very Google specific.  Is there a generic way to handle this?
	 **/
	function addMarkerByString($string, $title='', $description='', $minzoom = 0, $maxzoom = 17) {
		
		// first split the string into it's components. It doesn't need to be perfect, it's just
		// put together on the other end anyway
		$address = explode(',', $string);

		$street = $address[0];
		$city = $address[1];
		$state = $address[2];
		$country = $address[3];
		
		/* Geocode the address */
		$lookupTable = t3lib_div::makeInstance('tx_wecmap_cache');
		$latlong = $lookupTable->lookup($street, $city, $state, $zip, $country, $this->key);
 
		/* Create a marker at the specified latitude and longitdue */
		$this->addMarkerByLatLong($latlong['lat'], $latlong['long'], $title, $description, $minzoom, $maxzoom);
	}
	
	/**
	 * Returns the classname of the marker class.
	 * @return	string	The name of the marker class.
	 */
	function getMarkerClassName() {
		return $this->markerClassName;
	}
	
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_map.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_map.php']);
}


?>