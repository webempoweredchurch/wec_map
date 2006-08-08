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


require_once(t3lib_extMgm::extPath('wec_map').'class.tx_wecmap_map.php');
require_once('class.tx_wecmap_marker_google.php');

/**
 * Main class for the wec_map extension.  This class sits between the various 
 * frontend plugins and address lookup service to render map data.
 * 
 * @author Web Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class tx_wecmap_map_google extends tx_wecmap_map {
	var $lat;
	var $long;
	var $zoom;
	var $markers;
	var $width;
	var $height;
				
	var $js;
	var $key;
	
	var $markerClassName = 'tx_wecmap_marker_google';
	
	/* 
	 * Class constructor.  Creates javscript array.
	 * @param	string		The Google Maps API Key
	 * @param	string		The latitude for the center point on the map.
	 * @param 	string		The longitude for the center point on the map.
	 * @param	string		The initial zoom level of the map.
	 */
	function tx_wecmap_map_google($key, $width=250, $height=250, $lat='', $long='', $zoom='') {
		$this->prefixId = "tx_wecmap_map_google";
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
	
	function drawMap() {						
		if(!isset($this->lat) or !isset($this->long)) {
			$this->autoCenterAndZoom();
		}
				
		$GLOBALS["TSFE"]->JSeventFuncCalls["onload"][$this->prefixId]="drawMap();";	
		$GLOBALS['TSFE']->additionalHeaderData[] = '<script src="http://maps.google.com/maps?file=api&v=2&key='.$this->key.'" type="text/javascript"></script>';
		
		//$htmlContent .= '<script src="http://maps.google.com/maps?file=api&v=2&key='.$this->key.'" type="text/javascript"></script>';
		
		$htmlContent .= $this->mapDiv('map', $this->width, $this->height);
		$jsContent = array();
		$jsContent[] .= $this->js_createMarker();
		$jsContent[] .= $this->js_drawMapStart();
		$jsContent[] .= $this->js_newGMap2('map');
		$jsContent[] .= $this->js_setCenter('map', $this->lat, $this->long, $this->zoom);
		$jsContent[] .= $this->js_addControl('map', "new GLargeMapControl()");
		$jsContent[] .= $this->js_addControl('map', "new GMapTypeControl()");
		$jsContent[] .= $this->js_icon();
		foreach($this->markers as $marker) {
			$jsContent[] .= $marker->writeJS();
		}
		$jsContent[] .= $this->js_drawMapEnd();
		
		return $htmlContent.t3lib_div::wrapJS(implode(chr(10), $jsContent));
	}
	
	function mapDiv($id, $height, $width) {
		return '<div id="'.$id.'" style="width:'.$width.'px; height:'.$height.'px;"></div>';
	}
	
	function js_createMarker() {
		return 'function createMarker(point, icon, text) {
					var marker = new GMarker(point, icon);
					if(text){
						GEvent.addListener(marker, "click", function() { marker.openInfoWindowHtml(text); });
					}
					return marker;
				}';
	}
	
	function js_drawMapStart() {
		return 'function drawMap() {						
					if (GBrowserIsCompatible()) {';
	}
	
	function js_drawMapEnd() {
		return '} }';
	}
	
	function js_newGMap2($name) {
		return 'var '.$name.' = new GMap2(document.getElementById("'.$name.'"));';
	}	
		
	function js_setCenter($name, $lat, $long, $zoom) {
		return $name.'.setCenter(new GLatLng('.$lat.', '.$long.'), '.$zoom.');';
	}
	
	function js_addControl($name, $control) {
		return $name.'.addControl('.$control.');';
	}
	
	function js_icon() {
		if (TYPO3_MODE=='BE')	{
			$path = t3lib_extMgm::extRelPath('wec_map');
		} else {
			$path = t3lib_extMgm::siteRelPath('wec_map');
		}
		
		return 'var icon = new GIcon();
				icon.image = "'.$path.'images/mm_20_red.png";
				icon.shadow = "'.$path.'images/mm_20_shadow.png";
				icon.iconSize = new GSize(12, 20);
				icon.shadowSize = new GSize(22, 20);
				icon.iconAnchor = new GPoint(6, 20);
				icon.infoWindowAnchor = new GPoint(5, 1);';
				
	}


	/*
 	 * Sets the center and zoom values for the current map dynamically, based
 	 * on the markers to be displayed on the map.
 	 *
 	 * @return	void		No return value needed.  Changes made to object model.
 	 */
	function autoCenterAndZoom() {	
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
		// Should be 17
		$this->setZoom(16 - $zoom);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/map_service/google/class.tx_wecmap_map_google.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/map_service/google/class.tx_wecmap_map_google.php']);
}


?>
