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
 * Plugin 'Map' for the 'wec_map' extension.
 *
 * @author	Web-Empowered Church Team <map@webempoweredchurch.org>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('wec_map').'class.tx_wecmap_shared.php');

/**
 * Simple frontend plugin for displaying an address on a map.
 *
 * @author Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class tx_wecmap_pi1 extends tslib_pibase {
	var $prefixId = 'tx_wecmap_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_wecmap_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'wec_map';	// The extension key.
	var $pi_checkCHash = TRUE;
	var $sidebarLinks = array();

	/**
	 * Draws a Google map based on an address entered in a Flexform.
	 * @param	array		Content array.
	 * @param	array		Conf array.
	 * @return	string	HTML / Javascript representation of a Google map.
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		// check for WEC Map API static template inclusion
		if(empty($conf['output']) && !(empty($conf['marker.']['title']) && empty($conf['marker.']['description']))) {
			global $LANG;
			if(!is_object($LANG)) {
				require_once(t3lib_extMgm::extPath('lang').'lang.php');
				$LANG = t3lib_div::makeInstance('language');
				$LANG->init($GLOBALS['TSFE']->config['config']['language']);
			}
			$LANG->includeLLFile('EXT:wec_map/locallang_db.xml');
			$out .= $LANG->getLL('wecApiTemplateNotIncluded');
			// syslog start
				t3lib_div::sysLog('WEC Map API template not included on page id '.$GLOBALS['TSFE']->id, 'wec_map', 3);
			// syslog end
			return $out;
		}
		
		// check for WEC Simple Map static template inclusion
		if(empty($conf['marker.']['title']) && empty($conf['marker.']['description'])) {
			global $LANG;
			if(!is_object($LANG)) {
				require_once(t3lib_extMgm::extPath('lang').'lang.php');
				$LANG = t3lib_div::makeInstance('language');
				$LANG->init($GLOBALS['TSFE']->config['config']['language']);
			}
			$LANG->includeLLFile('EXT:wec_map/locallang_db.xml');
			$out .= $LANG->getLL('pi1TemplateNotIncluded');
			// syslog start
				t3lib_div::sysLog('WEC Simple Map template not included on page id '.$GLOBALS['TSFE']->id, 'wec_map', 3);
			// syslog end
			return $out;
		}
		
		/* Initialize the Flexform and pull the data into a new object */
		$this->pi_initPIflexform();
		$piFlexForm = $this->cObj->data['pi_flexform'];

		// get configuration from flexform or TS. Flexform values take
		// precedence.
		$width = $this->pi_getFFvalue($piFlexForm, 'mapWidth', 'mapConfig');
		empty($width) ? $width = $conf['width']:null;

		$height = $this->pi_getFFvalue($piFlexForm, 'mapHeight', 'mapConfig');
		empty($height) ? $height = $conf['height']:null;
		$this->height = $height;

		$mapControlSize = $this->pi_getFFvalue($piFlexForm, 'mapControlSize', 'mapControls');
		(empty($mapControlSize) || $mapControlSize == 'none') ? $mapControlSize = $conf['controls.']['mapControlSize']:null;

		$overviewMap = $this->pi_getFFvalue($piFlexForm, 'overviewMap', 'mapControls');
		empty($overviewMap) ? $overviewMap = $conf['controls.']['showOverviewMap']:null;

		$mapType = $this->pi_getFFvalue($piFlexForm, 'mapType', 'mapControls');
		empty($mapType) ? $mapType = $conf['controls.']['showMapType']:null;

		$googleEarth = $this->pi_getFFvalue($piFlexForm, 'googleEarth', 'mapControls');
		empty($googleEarth) ? $googleEarth = $conf['controls.']['showGoogleEarth']:null;
		
		$initialMapType = $this->pi_getFFvalue($piFlexForm, 'initialMapType', 'mapConfig');
		empty($initialMapType) ? $initialMapType = $conf['initialMapType']:null;

		$scale = $this->pi_getFFvalue($piFlexForm, 'scale', 'mapControls');
		empty($scale) ? $scale = $conf['controls.']['showScale']:null;

		$showInfoOnLoad = $this->pi_getFFvalue($piFlexForm, 'showInfoOnLoad', 'mapConfig');
		empty($showInfoOnLoad) ? $showInfoOnLoad = $conf['showInfoOnLoad']:null;

		$showDirs = $this->pi_getFFvalue($piFlexForm, 'showDirections', 'mapConfig');
		empty($showDirs) ? $showDirs = $conf['showDirections']:null;

		$showWrittenDirs = $this->pi_getFFvalue($piFlexForm, 'showWrittenDirections', 'mapConfig');
		empty($showWrittenDirs) ? $showWrittenDirs = $conf['showWrittenDirections']:null;

		$prefillAddress = $this->pi_getFFvalue($piFlexForm, 'prefillAddress', 'mapConfig');
		empty($prefillAddress) ? $prefillAddress = $conf['prefillAddress']:null;

		$this->showSidebar = $conf['showSidebar'];

		$centerLat = $conf['centerLat'];

		$centerLong = $conf['centerLong'];

		$zoomLevel = $conf['zoomLevel'];
		
		$maxAutoZoom = $conf['maxAutoZoom'];

		$mapName = $conf['mapName'];
		if(empty($mapName)) $mapName = 'map'.$this->cObj->data['uid'];
		$this->mapName = $mapName;

		// get this from flexform only. If empty, we check the TS, see below.
		$street      = $this->pi_getFFvalue($piFlexForm, 'street', 'default');
		$city        = $this->pi_getFFvalue($piFlexForm, 'city', 'default');
		$state       = $this->pi_getFFvalue($piFlexForm, 'state', 'default');
		$zip         = $this->pi_getFFvalue($piFlexForm, 'zip', 'default');
		$country     = $this->pi_getFFvalue($piFlexForm, 'country', 'default');
		$title       = $this->pi_getFFvalue($piFlexForm, 'title', 'default');
		$description = $this->pi_getFFvalue($piFlexForm, 'description', 'default');

		/* Create the map class and add markers to the map */
		include_once(t3lib_extMgm::extPath('wec_map').'map_service/google/class.tx_wecmap_map_google.php');
		$className = t3lib_div::makeInstanceClassName('tx_wecmap_map_google');
		$map = new $className(null, $width, $height, $centerLat, $centerLong, $zoomLevel, $mapName);

		// evaluate config to see which map controls we need to show
		if($mapControlSize == 'large') {
			$map->addControl('largeMap');
		} else if ($mapControlSize == 'small') {
			$map->addControl('smallMap');
		} else if ($mapControlSize == 'zoomonly') {
			$map->addControl('smallZoom');
		}

		$map->setMaxAutoZoom($maxAutoZoom);

		if($scale) $map->addControl('scale');
		if($overviewMap) $map->addControl('overviewMap');
		if($mapType) $map->addControl('mapType');
		if($initialMapType) $map->setType($initialMapType);
		if($googleEarth) $map->addControl('googleEarth');

		// check whether to show the directions tab and/or prefill addresses and/or written directions
		if($showDirs && $showWrittenDirs && $prefillAddress) $map->enableDirections(true, $mapName.'_directions');
		if($showDirs && $showWrittenDirs && !$prefillAddress) $map->enableDirections(false, $mapName.'_directions');
		if($showDirs && !$showWrittenDirs && $prefillAddress) $map->enableDirections(true);
		if($showDirs && !$showWrittenDirs && !$prefillAddress) $map->enableDirections();

		// see if we need to open the marker bubble on load
		if($showInfoOnLoad) $map->showInfoOnLoad();

		// determine if an address has been set through flexforms. If not, process TS
		if(empty($zip) && empty($state) && empty($city)) {

			// add icons
			if(!empty($conf['icons.'])) {
				foreach( $conf['icons.'] as $key => $value ) {
					$map->addMarkerIcon($value);
				}
				
			} else {
				$iconID = '';
			}
			
			
			// loop through markers
			foreach($conf['markers.'] as $marker) {

				// use the icon specified in the marker config
				$iconID = $marker['iconID'];

				// determine if address was entered by string or separated
				if(array_key_exists('address', $marker)) {

					$title = tx_wecmap_shared::render($marker, $conf['marker.']['title.']);
					$description = tx_wecmap_shared::render($marker, $conf['marker.']['description.']);
					
					// add address by string
					$markerObj = $map->addMarkerByString($marker['address'], $title, $description, 0, 17, $iconID);

					$this->addSidebarItem($markerObj, $marker);
				
				// add address by lat and long only
				} else if(array_key_exists('lat', $marker) && array_key_exists('long', $marker)) {

					$title = tx_wecmap_shared::render($marker, $conf['marker.']['title.']);
					$description = tx_wecmap_shared::render($marker, $conf['marker.']['description.']);
					$lat     = $marker['lat'];
					$long    = $marker['long'];

					// add the marker to the map
					$markerObj = $map->addMarkerByLatLong($lat, $long, $title, $description, 0, 17, $iconID);
			
					$this->addSidebarItem($markerObj, $marker);
					
				} else {
					
					$title = tx_wecmap_shared::render($marker, $conf['marker.']['title.']);
					$description = tx_wecmap_shared::render($marker, $conf['marker.']['description.']);
					
					// add the marker to the map
					$markerObj = $map->addMarkerByAddress($marker['street'], $marker['city'], $marker['state'],
											 $marker['zip'], $marker['country'], $title,
											 $description, 0, 17, $iconID);
			
					$this->addSidebarItem($markerObj, $marker);
					
				}
			}
		} else {
			// put all the data into an array
			$marker['city']        = $city;
			$marker['state']       = $state;
			$marker['street']      = $street;
			$marker['zip']         = $zip;
			$marker['country']     = $country;
			$marker['title']       = $title;
			$marker['description'] = $description;

			$title = tx_wecmap_shared::render($marker, $conf['marker.']['title.']);
			$description = tx_wecmap_shared::render($marker, $conf['marker.']['description.']);

			// add the marker to the map
			$markerObj = $map->addMarkerByAddress($street, $city, $state, $zip, $country, $title, $description, 0, 17);
			$this->addSidebarItem($markerObj, $marker);
		}
		
		// gather all the content together
		$content = array();
		$content['map'] = $map->drawMap();
		if($showWrittenDirs) $content['directions'] = $this->getDirections();
		$content['sidebar'] = $this->getSidebar();

		// run all the content pieces through TS to assemble them
		$output = tx_wecmap_shared::render($content, $conf['output.']);

		return $this->pi_wrapInBaseClass($output);
	}
	
	function getDirections() {
		$out = tx_wecmap_shared::render(array('map_id' => $this->mapName), $this->conf['directions.']);
		return $out;
	}
	
	/**
	 * adds a sidebar item corresponding to the given marker.
	 * Does so only if the sidebar is enabled.
	 *
	 * @return void
	 **/
	function addSidebarItem(&$marker, $data) {
		if(!($this->showSidebar && is_object($marker))) return;
		$data['onclickLink'] = $marker->getClickJS();
		$this->sidebarLinks[] = tx_wecmap_shared::render($data, $this->conf['sidebarItem.']);
	}
	
	function getSidebar() {
		if(empty($this->sidebarLinks)) return null;

		$c = '';
		foreach( $this->sidebarLinks as $link ) {
			$c .= $link;
		}
		$out = tx_wecmap_shared::render(array('map_height' => $this->height, 'map_id' => $this->mapName, 'content' => $c), $this->conf['sidebar.']);

		return $out;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/pi1/class.tx_wecmap_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/pi1/class.tx_wecmap_pi1.php']);
}

?>
