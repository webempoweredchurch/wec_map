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


require_once(PATH_tslib.'class.tslib_pibase.php');

/**
 * Main class for the wec_map extension.  This class sits between the various 
 * frontend plugins and address lookup service to render map data.
 * 
 * @author Web Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class tx_wecmap extends tslib_pibase {
	var $prefixId = 'tx_wecmap';		// Same as class name
	var $scriptRelPath = 'class.tx_wecmap.php';	// Path to this script relative to the extension dir.
	var $extKey = 'wec_map';	// The extension key.
	var $pi_checkCHash = TRUE;

	var $lat;
	var $long;
	var $zoom;
	var $markers;
	var $width;
	var $height;
				
	var $js;
	
	/* 
	 * Class constructor.  Creates javscript array.
	 * @param	string		The Google Maps API Key
	 * @param	string		The latitude for the center point on the map.
	 * @param 	string		The longitude for the center point on the map.
	 * @param	string		The initial zoom level of the map.
	 */
	function tx_wecmap($key, $width=250, $height=250, $lat='', $long='', $zoom='') {
		$this->js = array();
		$this->markerArray = array();
		
		$this->key = $key;

		if($width != '') {
			$this->width = $width;
		} else {
			$this->width = 250;
		}
		
		if($height != '') {
			$this->height = $height;
		} else {
			$this->height = 250;
		}
		
		if ($lat != '' || $long != '') {
			$this->setCenter($lat, $long);
		}
		
		if ($zoom != '') {
			$this->setZoom($zoom);
		}
		
	}
	
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
	
	
	/*
	 * Sets the center and zoom values for the current map dynamically, based
	 * on the markers to be displayed on the map.
	 *
	 * @return	void		No return value needed.  Changes made to object model.
	 */
	function autoCenterAndZoom() {		
		$minLat = 360;
		$maxLat = -360;
		$minLong = 360;
		$maxLong = -360;
		
		$width = 500;
		$height = 700;

		/* Find min and max zoom lat and long */		
		foreach($this->markerArray as $marker) {			
			if ($marker['lat'] < $minLat) 
				$minLat = $marker['lat'];
			if ($marker['lat'] > $maxLat) 
				$maxLat = $marker['lat'];
			
			if ($marker['long'] < $minLong) 
				$minLong = $marker['long'];
			if ($marker['long'] > $maxLong) 
				$maxLong = $marker['long'];
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
		
		/* Calculate the span of the lat/long boundaries */
		$latSpan = $maxLat-$minLat;
		$longSpan = $maxLong-$minLong;
		
		/* Calculate center lat/long based on boundary markers */
		$lat = ($minLat + $maxLat) / 2;
		$long = ($minLong + $maxLong) / 2;
		
		//$pixelsPerLatDegree = pow(2, 17-$zoom);
		//$pixelsPerLongDegree = pow(2,17 - $zoom) *  0.77162458338772;
		$wZoom = 17 - log($this->width, 2) +  log($longSpan, 2);
		$hZoom = 17 - log($this->height, 2) + log($latSpan, 2);
				
		$zoom = ceil(($wZoom > $hZoom) ? $wZoom : $hZoom);
		if ($zoom > 14) {
			$zoom = 14;
		}
		elseif ($zoom < 2) {
			$zoom = 2;
		}
		
		$this->setCenter($lat, $long);
		$this->setZoom($zoom);
	}
	
	/* 
	 * Outputs the HTML and Javascript required for a Google Map.  
	 *
	 * @return	string		The HTML/Javascript representation of the map.
	 */
	function drawMap() {
		$GLOBALS['TSFE']->additionalHeaderData[] = '<script src="http://maps.google.com/maps?file=api&v=1&key='.$this->key.'" type="text/javascript"></script>';
		
		if ($this->lat == '' || $this->long == '' || $this->zoom == '') {
			$this->autoCenterAndZoom();
		}

		$this->js['mapDiv'] = '<div id="map" style="width: '.$this->width.'px; height: '.$this->height.'px"></div>';
		$this->js['start'] = '<script type="text/javascript">
								//<![CDATA[';
		$this->js['createMarker'] = 'function createMarker(point, text) {
				                 		var marker = new GMarker(point);
				                     GEvent.addListener(marker, "click", function() { marker.openInfoWindowHtml(text); });
				                     return marker;
				                 }';						
		$this->js['browserCheckStart'] = 'if (GBrowserIsCompatible()) {';
		$this->js['createMap'] = 'var map = new GMap(document.getElementById("map"));
										  var myPoint = new GPoint('.$this->long.', '.$this->lat.');
										  map.centerAndZoom(myPoint, '.$this->zoom.');
										  map.addControl(new GSmallMapControl());
										  map.addControl(new GMapTypeControl());';	
		$this->js['markers'] = $this->markers;					
		$this->js['browserCheckEnd'] = '}';
		$this->js['end'] = '  //]]>
								 </script>';
		
		return implode(chr(10), $this->js);
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
	function addMarker($street, $city, $state, $zip, $description='') {
		include_once(t3lib_extMgm::extPath('wec_map').'class.tx_wecmap_cache.php');
		$lookupTable = t3lib_div::makeInstance("tx_wecmap_cache");
		$latlong = $lookupTable->lookup($street, $city, $state, $zip);
		
		if($latlong['lat']!='' && $latlong['long']!='') {
			$this->markers = $this->markers.chr(10).'map.addOverlay(createMarker(new GPoint('.$latlong['long'].', '.$latlong['lat'].'), "'.$description.'"));';			
			$this->markerArray[] = $latlong;
		}
	}
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap.php']);
}


?>