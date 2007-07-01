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

require_once(t3lib_extMgm::extPath('wec_map').'class.tx_wecmap_map.php');
require_once(t3lib_extMgm::extPath('wec_map').'map_service/google/class.tx_wecmap_marker_google.php');
require_once(t3lib_extMgm::extPath('wec_map').'class.tx_wecmap_backend.php');

/**
 * Map implementation for the Google Maps mapping service.
 * 
 * @author Web-Empowered Church Team <map@webempoweredchurch.org>
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
	var $mapName;
				
	var $js;
	var $key;
	var $controls;
	var $type;
	var $directions;
	var $prefillAddress;
	var $directionsDivID;
	var $showInfoOnLoad;
	
	var $lang;
	
	var $markerClassName = 'tx_wecmap_marker_google';
	
	/** 
	 * Class constructor.  Creates javscript array.
	 * @access	public
	 * @param	string		The Google Maps API Key
	 * @param	string		The latitude for the center point on the map.
	 * @param 	string		The longitude for the center point on the map.
	 * @param	string		The initial zoom level of the map.
	 */
	function tx_wecmap_map_google($key, $width=250, $height=250, $lat='', $long='', $zoom='', $mapName='') {
		$this->prefixId = 'tx_wecmap_map_google';
		$this->js = array();
		$this->markers = array();
		
		if(!$key) {
			// get key from configuration
			$keyConfig = tx_wecmap_backend::getExtConf('apiKey.google');
			
			// get current domain
			$domain = t3lib_div::getIndpEnv('HTTP_HOST');
			
			// loop through all the domain->key pairs we got to find the right one
			$found = false;
			foreach( $keyConfig as $key => $value ) {
				if($domain == $key) {
					$found = true;
					$this->key = $value;
				}
			}
			
			// if we didn't get an exact match, check for partials and guess
			if(!$found) {
				foreach( $keyConfig as $key => $value ) {

					if(strpos($domain, $key) !== false) {
						$found = true;
						$this->key = $value;
					}
				}
			}

		} else {
			$this->key = $key;			
		}

		$this->controls = array();
		$this->directions = false;
		$this->directionsDivID = null;
		$this->prefillAddress = false;
		$this->showInfoOnLoad = false;
		$this->width = $width;
		$this->height = $height;
		
		if (($lat != '' && $lat != null) || ($long != '' && $long != null)) {
			$this->setCenter($lat, $long);
		}

		if ($zoom != '' && $zoom != null) {
			$this->setZoom($zoom);
		}
		
		if(empty($mapName)) $mapName = 'map'.rand();
		$this->mapName = $mapName;

		
		if(TYPO3_MODE == 'BE') {
			global $LANG;
			if($LANG->lang == 'default') {
				$this->lang = 'en';
			} else {
				$this->lang = $LANG->lang;
			}			
		} else {
			$this->lang = $GLOBALS['TSFE']->config['config']['language'];
			if(empty($this->lang)) $this->lang = 'en';
		}
	}	
	
	/**
	 * Enables controls for Google Maps, for example zoom level slider or mini 
	 * map. Valid controls are largeMap, smallMap, scale, smallZoom, 
	 * overviewMap, and mapType.
	 *
	 * @access	public
	 * @param	string	The name of the control to add.
	 * @return	none
	 *
	 **/
	function addControl($name) {
		switch ($name)
		{
			case 'largeMap':
				$this->controls[] .= $this->js_addControl($this->mapName, 'new GLargeMapControl()');
				break;
			
			case 'smallMap':
				$this->controls[] .= $this->js_addControl($this->mapName, 'new GSmallMapControl()');
				break;
			
			case 'scale':
				$this->controls[] .= $this->js_addControl($this->mapName, 'new GScaleControl()');
				break;
			
			case 'smallZoom':
				$this->controls[] .= $this->js_addControl($this->mapName, 'new GSmallZoomControl()');
				break;

			case 'overviewMap':
				$this->controls[] .= $this->js_addControl($this->mapName, 'new GOverviewMapControl()');
				break;
					
			case 'mapType':
				$this->controls[] .= $this->js_addControl($this->mapName, 'new GMapTypeControl()');
				break;
			default:
				break;
		}
	}
	
	/**
	 * Sets the initial map type.  Valid defaults from Google are...
	 *   G_NORMAL_MAP: This is the normal street map type.
	 *   G_SATELLITE_MAP: This map type shows Google Earth satellite images.
	 *   G_HYBRID_MAP: This map type shows transparent street maps over Google Earth satellite images.
	 */
	function setType($type) {
		$this->type = $type;
	}
	
	/**
	 * Main function to draw the map.  Outputs all the necessary HTML and
	 * Javascript to draw the map in the frontend or backend.
	 *
	 * @access	public
	 * @return	string	HTML and Javascript markup to draw the map.
	 */
	function drawMap() {		

		/* Initialize locallang.  If we're in the backend context, we're fine.
		   If we're in the frontend, then we need to manually set it up. */
		if(TYPO3_MODE == 'BE') {
			global $LANG;
		} else {
			require_once(t3lib_extMgm::extPath('lang').'lang.php');			
			$LANG = t3lib_div::makeInstance('language');
			$LANG->init($GLOBALS['TSFE']->config['config']['language']);
		}
		$LANG->includeLLFile('EXT:wec_map/map_service/google/locallang.xml');

		$hasKey = $this->hasKey();
		$hasThingsToDisplay = $this->hasThingsToDisplay();
		$hasHeightWidth = $this->hasHeightWidth();
		
		// make sure we have markers to display and an API key
		if ($hasThingsToDisplay && $hasKey && $hasHeightWidth) { 						
			
			if(!isset($this->lat) or !isset($this->long)) {
				$this->autoCenterAndZoom();
			}
		
			/* If we're in the frontend, use TSFE.  Otherwise, include JS manually. */
			if(TYPO3_MODE == 'FE') {
				$GLOBALS['TSFE']->JSeventFuncCalls['onload'][$this->prefixId] .= 'drawMap_'. $this->mapName .'();';	
				$GLOBALS['TSFE']->JSeventFuncCalls['onunload'][$this->prefixId]='GUnload();';	
				$GLOBALS['TSFE']->additionalHeaderData[] = '<script src="http://maps.google.com/maps?file=api&amp;v=2.x&amp;key='.$this->key.'" type="text/javascript"></script>';
				$GLOBALS['TSFE']->additionalHeaderData[] = '<script src="'.t3lib_extMgm::siteRelPath('wec_map').'contrib/prototype/prototype.js" type="text/javascript"></script>';
			} else {
				$htmlContent .= '<script src="http://maps.google.com/maps?file=api&amp;v=2.x&amp;key='.$this->key.'" type="text/javascript"></script>';
				$htmlContent .= '<script src="'.t3lib_div::getIndpEnv('TYPO3_SITE_URL'). t3lib_extMgm::siteRelPath('wec_map').'contrib/prototype/prototype.js" type="text/javascript"></script>';
			}
		
			$htmlContent .= $this->mapDiv($this->mapName, $this->width, $this->height);
			$jsContent = array();
			$jsContent[] = $this->js_createMarker();
			$jsContent[] = $this->js_createMarkerWithTabs();
			$jsContent[] = $this->js_setDirections();
			$jsContent[] = $this->js_errorHandler();
			$jsContent[] = $this->js_drawMapStart();
			$jsContent[] = $this->js_newGMap2($this->mapName);
			$jsContent[] = $this->js_newGDirections();
			$jsContent[] = $this->js_setCenter($this->mapName, $this->lat, $this->long, $this->zoom, $this->type);
			foreach( $this->controls as $control ) {
				$jsContent[] = $control;
			}
			$jsContent[] = $this->js_icon();
			$jsContent[] = $this->js_newGMarkerManager('mgr_'.$this->mapName, $this->mapName);
			$jsContent[] = 'var markers_'. $this->mapName .' = [];';
			$jsContent[] = 'var index_'. $this->mapName .' = 0;';
			foreach($this->markers as $key => $markers) {
				$jsContent[] = 'markers_'. $this->mapName .'[index_'. $this->mapName .'] = [];';
				$key = explode(':',$key);
				foreach( $markers as $marker ) {
					if($this->directions) {
						$jsContent[] = 'markers_'. $this->mapName .'[index_'. $this->mapName .'].push('. $marker->writeJSwithDirections() .');';
					} else {
						$jsContent[] = 'markers_'. $this->mapName .'[index_'. $this->mapName .'].push('. $marker->writeJS() .');';						
					}

				}
				$jsContent[] = 'mgr_'. $this->mapName .'.addMarkers(markers_'. $this->mapName .'[index_'. $this->mapName .'], ' . $key[0] . ', ' . $key[1] . ');';
				$jsContent[] = 'index_'. $this->mapName .'++;';
			}

			$jsContent[] = 'markers_'. $this->mapName .' = markers_'. $this->mapName .'.flatten();';
			$jsContent[] = 'mgr_'. $this->mapName .'.refresh();';
			$jsContent[] = $this->js_initialOpenInfoWindow();
			$jsContent[] = $this->js_drawMapEnd();
		
			// there is no onload() in the BE, so we need to call drawMap() manually.
			if(TYPO3_MODE == 'FE') {
				$manualCall = null;
			} else {
				$manualCall = '<script type="text/javascript">setTimeout("drawMap_'. $this->mapName .'()",500);</script>';
			}
		
			return $htmlContent.t3lib_div::wrapJS(implode(chr(10), $jsContent)).$manualCall;
		} else if (!$hasKey) {
			$error = '<p>'.$LANG->getLL('error_noApiKey').'</p>';
			return $error;
		} else if (!$hasThingsToDisplay) {
			$error = '<p>'.$LANG->getLL('error_nothingToDisplay').'</p>';
			return $error;
		} else if (!$hasHeightWidth) {
			$error = '<p>'.$LANG->getLL('error_noHeightWidth').'</p>';
			return $error;
		}
	}
	
	/**
	 * Adds an address to the currently list of markers rendered on the map. Support tabs.
	 *
	 * @param	string		The street address.
	 * @param	string		The city name.
	 * @param	string		The state or province.
	 * @param	string		The ZIP code.
	 * @param	string		The country name.
	 * @param 	array 		Array of tab labels. Need to be kept short.
	 * @param	array		Array of titles for the marker popup.
	 * @param	array		Array of descriptions to be displayed in the marker popup.
	 * @param	integer		Minimum zoom level for marker to appear.
	 * @param	integer		Maximum zoom level for marker to appear.
	 * @return	none
	 * @todo	Zoom levels are very Google specific.  Is there a generic way to handle this?
	 */
	function addMarkerByAddressWithTabs($street, $city, $state, $zip, $country, $tabLabels = null, $title=null, $description=null, $minzoom = 0, $maxzoom = 17) {
		/* Geocode the address */
		$lookupTable = t3lib_div::makeInstance('tx_wecmap_cache');
		$latlong = $lookupTable->lookup($street, $city, $state, $zip, $country, $this->key);

		/* Create a marker at the specified latitude and longitdue */
		$this->addMarkerByLatLongWithTabs($latlong['lat'], $latlong['long'], $tabLabels, $title, $description, $minzoom, $maxzoom);	
	}
	
	/**
	 * Adds an address string to the current list of markers rendered on the map.
	 *
	 * @param	string		The full address string.
	 * @param	array 		Array of strings to be used as labels on the tabs
	 * @param	array		The titles for the tabs of the marker popup.
	 * @param	array		The descriptions to be displayed in the tabs of the marker popup.
	 * @param	integer		Minimum zoom level for marker to appear.
	 * @param	integer		Maximum zoom level for marker to appear.
	 * @return	none
	 * @todo	Zoom levels are very Google specific.  Is there a generic way to handle this?
	 **/
	function addMarkerByStringWithTabs($string, $tabLabels, $title=null, $description=null, $minzoom = 0, $maxzoom = 17) {
		
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
		$this->addMarkerByLatLongWithTabs($latlong['lat'], $latlong['long'], $tabLabels, $title, $description, $minzoom, $maxzoom);
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
	function addMarkerByLatLongWithTabs($lat, $long, $tabLabels = null, $title=null, $description=null, $minzoom = 0, $maxzoom = 17) {		
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
											  $this->prefillAddress,
											  $tabLabels);
		}
	}
	
	/**
	 * Creates the overall map div.
	 * 
	 * @access	private
	 * @param	string		ID of the div tag.
	 * @param	integer		Width of the map in pixels.
	 * @param	integer		Height of the map in pixels.
	 * @return	string		The HTML for the map div tag.
	 */
	function mapDiv($id, $width, $height) {
		return '<div class="tx-wecmap-directions" id="directions-'. $this->mapName .'"></div><div id="'.$id.'" class="tx-wecmap-map" style="width:'.$width.'px; height:'.$height.'px;"></div>';
	}
	
	/**
	 * Creates the marker creation function in Javascript.
	 * 
	 * @access	private
	 * @return	string		The Javascript code for the marker creation function.
	 */
	function js_createMarker() {
		return 
		'function createMarker(point, icon, text) {
			var marker = new GMarker(point, icon);
			if(text){
				GEvent.addListener(marker, "click", function() { marker.openInfoWindowHtml(text); });
			}
			return marker;
		}';
	}
	
	/**
	 * Creates the function that will set directions
	 *
	 * @access private
	 * @return String	JS function
	 **/
	function js_setDirections() {
		return 'function setDirections_'. $this->mapName .'(fromAddress, toAddress, mapName) {
	      window["gdir_"+mapName].load("from: " + fromAddress + " to: " + toAddress, {locale: "'. $this->lang .'"});
			'. $this->mapName .'.closeInfoWindow();
	    }';
	}
	
	/**
	 * Creates the marker creation function with tabs in Javascript
	 *
	 * @return string	The JS code for the marker creation function with tabs.
	 **/
	function js_createMarkerWithTabs() {
		return 
		'function createMarkerWithTabs(point, icon, title, text) {
			var marker = new GMarker(point, icon);
			var tabs = [];
			for (var i=0; i < text.length; i++) {
				tabs.push(new GInfoWindowTab(title[i], text[i]));
			};
			GEvent.addListener(marker, "click", function() { marker.openInfoWindowTabsHtml(tabs); });			return marker;
		}';
	}
	
	/**
	 * Creates the beginning of the drawMap function in Javascript.
	 *
	 * @access	private
	 * @return	string	The beginning of the drawMap function in Javascript.
	 */
	function js_drawMapStart() {
		return 
		'var '.$this->mapName.';'.chr(10).
		'function drawMap_'. $this->mapName .'() {						
			if (GBrowserIsCompatible()) {';
	}
	
	/**
	 * Creates the end of the drawMap function in Javascript.
	 *
	 * @access	private
	 * @return	string	The end of the drawMap function in Javascript.
	 */
	function js_drawMapEnd() {
		return '} }';
	}
	
	/**
	 * Creates the Google Maps Javascript object.
	 * @access	private
	 * @param	string		Name of the div that this map is attached to.
	 * @return	string		Javascript for the Google Maps object.
	 */
	function js_newGMap2($name) {
		return $name.' = new GMap2(document.getElementById("'.$name.'"));';
	}
	
	/**
	 * Creates the Google Directions Javascript object.
	 *
	 * @access	private
	 * @param	string		Name of the map object that the direction overlay will be shown on.
	 * @return	string		Javascript for the Google Directions object.
	 */
	function js_newGDirections() {
		if($this->directionsDivID == null) {
			return 'gdir_'. $this->mapName .' = new GDirections('. $this->mapName .');'.
			'GEvent.addListener(gdir_'. $this->mapName .', "error", handleErrors_'. $this->mapName .');';			
		} else {
			return 'gdir_'. $this->mapName .' = new GDirections('. $this->mapName .', document.getElementById("'. $this->directionsDivID .'"));'.
			'GEvent.addListener(gdir_'. $this->mapName .', "error", handleErrors_'. $this->mapName .');';
		}

	}
	
	/**
	 * Error handler js function
	 *
	 * @return string 	Javascript
	 **/
	function js_errorHandler() {
		$c = 
			'function handleErrors_'. $this->mapName .'() {
				   if (gdir_'. $this->mapName .'.getStatus().code == G_GEO_UNKNOWN_ADDRESS)
				     alert("No corresponding geographic location could be found for one of the specified addresses. This may be due to the fact that the address is relatively new, or it may be incorrect.\nError code: " + gdir_'. $this->mapName .'.getStatus().code);
				   else if (gdir_'. $this->mapName .'.getStatus().code == G_GEO_SERVER_ERROR)
				     alert("A geocoding or directions request could not be successfully processed, yet the exact reason for the failure is not known.\n Error code: " + gdir_'. $this->mapName .'.getStatus().code);

				   else if (gdir_'. $this->mapName .'.getStatus().code == G_GEO_MISSING_QUERY)
				     alert("The HTTP q parameter was either missing or had no value. For geocoder requests, this means that an empty address was specified as input. For directions requests, this means that no query was specified in the input.\n Error code: " + gdir_'. $this->mapName .'.getStatus().code);

				   else if (gdir_'. $this->mapName .'.getStatus().code == 603)
				    alert("The geocode for the given address or the route for the given directions query cannot be returned due to legal or contractual reasons.\n Error code: " + gdir_'. $this->mapName .'.getStatus().code);

				   else if (gdir_'. $this->mapName .'.getStatus().code == G_GEO_BAD_KEY)
				     alert("The given key is either invalid or does not match the domain for which it was given. \n Error code: " + gdir_'. $this->mapName .'.getStatus().code);

				   else if (gdir_'. $this->mapName .'.getStatus().code == G_GEO_BAD_REQUEST)
				     alert("A directions request could not be successfully parsed.\n Error code: " + gdir_'. $this->mapName .'.getStatus().code);

				   else alert("An unknown error occurred. "+gdir_'. $this->mapName .'.getStatus().code);
			}';
		return $c;
	}
	
	function js_setMapType($name, $type) {
		return $name.'.setMapType('.$type.');';
	}	
	
	/**
	 * Creates the Marker Manager Javascript object.
	 *
	 * @access	private
	 * @param	string		Name of the marker manager.
	 * @param	string		Name of the map this marker manager applies to.
	 * @return	string		Javascript for the marker manager object.
	 */
	function js_newGMarkerManager($mgrName, $map) {
		return 'var ' . $mgrName . ' = new GMarkerManager(' . $map . ');';	
	}	
	
	/**
	 * Creates the map's center point in Javascript.
	 *
	 * @access	private
	 * @param	string		Name of the map to center.
	 * @param	float		Center latitude.
	 * @param	float		Center longitude.
	 * @param	integer		Initial zoom level.
	 * @return	string		Javascript to center and zoom the specified map.
	 */
	function js_setCenter($name, $lat, $long, $zoom, $type) {
		if($type) {
			return $name.'.setCenter(new GLatLng('.$lat.', '.$long.'), '.$zoom.', '.$type.');';
		} else {
			return $name.'.setCenter(new GLatLng('.$lat.', '.$long.'), '.$zoom.');';
		}
	}
	
	
	/**
	 * Creates Javascript to add map controls.
	 *
	 * @access	private
	 * @param	string		Name of the map.
	 * @param	string		Name of the control.
	 * @param	string		Javascript to add a control to the map.
	 */
	function js_addControl($name, $control) {
		return $name.'.addControl('.$control.');';
	}
	
	/**
	 * Creates Javascript to define marker icons.
	 * 
	 * @access	private
	 * @return	string		Javascript definitions for marker icons.
	 * @todo	Add support for custom icons.
	 */
	function js_icon() {
		/* If we're in the backend, get an absolute path.  Frontend can use a relative path. */
		if (TYPO3_MODE=='BE')	{
			$path = t3lib_div::getIndpEnv('TYPO3_SITE_URL').t3lib_extMgm::siteRelPath('wec_map');
		} else {
			$path = t3lib_extMgm::siteRelPath('wec_map');
		}

		return '
		var icon_'. $this->mapName .' = new GIcon();
		icon_'. $this->mapName .'.image = "'.$path.'images/mm_20_red.png";
		icon_'. $this->mapName .'.shadow = "'.$path.'images/mm_20_shadow.png";
		icon_'. $this->mapName .'.iconSize = new GSize(12, 20);
		icon_'. $this->mapName .'.shadowSize = new GSize(22, 20);
		icon_'. $this->mapName .'.iconAnchor = new GPoint(6, 20);
		icon_'. $this->mapName .'.infoWindowAnchor = new GPoint(5, 1);';
				
	}

	/**
	 * Write the javascript to open the info window if there is only one marker
	 *
	 * @return string 	javascript
	 **/
	function js_initialOpenInfoWindow() {
		$markers = reset($this->markers);

		if(count($markers) == 1 && $this->showInfoOnLoad) {
			$content = 'GEvent.trigger(markers_'. $this->mapName .'[0], "click");';
			return $content;
		}
	}

	/**
	 * Sets the center and zoom values for the current map dynamically, based
	 * on the markers to be displayed on the map.
	 * 
	 * @access	private	
	 * @return	none
 	 */
	function autoCenterAndZoom() {	
		
		/* Get center and lat/long spans from parent object */
		$latLongData = $this->getLatLongData();
		
		$lat = $latLongData['lat']; /* Center latitude */
		$long = $latLongData['long']; /* Center longitude */
		$latSpan = $latLongData['latSpan']; /* Total latitude the map covers */
		$longSpan = $latLongData['longSpan']; /* Total longitude the map covers */
	
		//$pixelsPerLatDegree = pow(2, 17-$zoom);
		//$pixelsPerLongDegree = pow(2,17 - $zoom) *  0.77162458338772;
		$wZoom = log($this->width, 2) - log($longSpan, 2);
		$hZoom = log($this->height, 2) - log($latSpan, 2);
		
		/* Pick the lower of the zoom levels since we'd rather show too much */
		$zoom = floor(($wZoom < $hZoom) ? $wZoom : $hZoom);
		
		/* Don't zoom in too far if we only have a single marker.*/
		if ($zoom > 15) {
			$zoom = 15;
		}
		
		$this->setCenter($lat, $long);
		$this->setZoom($zoom);
	}
	
	/**
     * Checks if a map has markers or a 
     * specific center.Otherwise, we have nothing 
     * to draw.
     * @return        boolean        True/false whether the map is valid or not.
     */
    function hasThingsToDisplay() {
        $valid = false;
        
        if(sizeof($this->markers) > 0) {
            $validMarkers = true;
        }
        
        if(isset($this->lat) and isset($this->long)) {
            $validCenter = true;
        }
        
		// If we have an API key along with markers or a center point, it's valid 
        if($validMarkers or $validCenter) {
            $valid = true;
        }
        
        return $valid;
    }

	/**
	 * Checks if an API key has been entered and displays an error message instead of the map if not.
	 *
	 * @return boolean
	 **/
	function hasKey() {
		if($this->key) {
            return true;
        } else {
			return false;
		}
	}
	
	/**
	 * Checks whether the map has a height and width set.
	 *
	 * @return boolean
	 **/
	function hasHeightWidth() {
		if(!empty($this->width) && !empty($this->height)) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Enables directions
	 *
	 * @param boolean	Whether or not to prefill the currently logged in FE user's address already
	 * @param string	The id of the container that will show the written directions
	 * 
	 * @return void
	 **/
	function enableDirections($prefillAddress = false, $divID = null) {
		$this->prefillAddress = $prefillAddress;
		$this->directions = true;
		$this->directionsDivID = $divID;
	}
	
	/**
	 * Makes the marker info bubble show on load if there is only one marker on the map
	 *
	 * @return void
	 **/
	function showInfoOnLoad() {
		$this->showInfoOnLoad = true;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/map_service/google/class.tx_wecmap_map_google.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/map_service/google/class.tx_wecmap_map_google.php']);
}


?>
