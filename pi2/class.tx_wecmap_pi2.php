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
/**
 * Plugin 'Frontend User Map' for the 'wec_map' extension.
 *
 * @author	Web-Empowered Church Team <map@webempoweredchurch.org>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

/**
 * Frontend User Map plugin for displaying all frontend users on a map.  
 *
 * @author Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class tx_wecmap_pi2 extends tslib_pibase {
	var $prefixId = 'tx_wecmap_pi2';		// Same as class name
	var $scriptRelPath = 'pi2/class.tx_wecmap_pi2.php';	// Path to this script relative to the extension dir.
	var $extKey = 'wec_map';	// The extension key.
	var $pi_checkCHash = TRUE;
	
	/**
	 * Draws a Google map containing all frontend users of a website.
	 * 
	 * @param	array		The content array.
	 * @param	array		The conf array.
	 * @return	string	HTML / Javascript representation of a Google map.
	 */
	function main($content,$conf)	{		
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		/* Initialize the Flexform and pull the data into a new object */
		$this->pi_initPIflexform();
		$piFlexForm = $this->cObj->data['pi_flexform'];
		
		// get config from flexform or TS. Flexforms take precedence.
		$apiKey = $this->pi_getFFvalue($piFlexForm, 'apiKey', 'default');
		empty($apiKey) ? $apiKey = $conf['apiKey']:null;

		$width = $this->pi_getFFvalue($piFlexForm, 'mapWidth', 'default');
		empty($width) ? $width = $conf['width']:null;
		
		$height = $this->pi_getFFvalue($piFlexForm, 'mapHeight', 'default');
		empty($height) ? $height = $conf['height']:null;
		
		$userGroups = $this->pi_getFFvalue($piFlexForm, 'userGroups', 'default');
		empty($userGroups) ? $userGroups = $conf['userGroups']:null;

		$pid = $this->pi_getFFvalue($piFlexForm, 'pid', 'default');
		empty($pid) ? $pid = $conf['pid']:null;

		$mapControlSize = $this->pi_getFFvalue($piFlexForm, 'mapControlSize', 'mapControls');
		(empty($mapControlSize) || $mapControlSize == 'none') ? $mapControlSize = $conf['controls.']['mapControlSize']:null;
		
		$overviewMap = $this->pi_getFFvalue($piFlexForm, 'overviewMap', 'mapControls');
		empty($overviewMap) ? $overviewMap = $conf['controls.']['showOverviewMap']:null;
				
		$mapType = $this->pi_getFFvalue($piFlexForm, 'mapType', 'mapControls');
		empty($mapType) ? $mapType = $conf['controls.']['showMapType']:null;
		
		$initialMapType = $this->pi_getFFvalue($piFlexForm, 'initialMapType', 'default');
		empty($initialMapType) ? $initialMapType = $conf['initialMapType']:null;
				
		$scale = $this->pi_getFFvalue($piFlexForm, 'scale', 'mapControls');
		empty($scale) ? $scale = $conf['controls.']['showScale']:null;
		
		$private = $this->pi_getFFvalue($piFlexForm, 'privacy', 'default');
		empty($private) ? $private = $conf['private']:null;

		$showDirs = $this->pi_getFFvalue($piFlexForm, 'showDirections', 'default');
		empty($showDirs) ? $showDirs = $conf['showDirections']:null;
		
		$prefillAddress = $this->pi_getFFvalue($piFlexForm, 'prefillAddress', 'default');
		empty($prefillAddress) ? $prefillAddress = $conf['prefillAddress']:null;
		
		$centerLat = $conf['centerLat'];
		
		$centerLong = $conf['centerLong'];
		
		$zoomLevel = $conf['zoomLevel'];
		
		$mapName = $conf['mapName'];
		if(empty($mapName)) $mapName = 'map'.$this->cObj->data['uid'];
		
		/* Create the Map object */
		include_once(t3lib_extMgm::extPath('wec_map').'map_service/google/class.tx_wecmap_map_google.php');
		$className=t3lib_div::makeInstanceClassName('tx_wecmap_map_google');
		$map = new $className($apiKey, $width, $height, $centerLat, $centerLong, $zoomLevel, $mapName);
		
		// evaluate map controls based on configuration
		if($mapControlSize == 'large') {
			$map->addControl('largeMap');	
		} else if ($mapControlSize == 'small') {
			$map->addControl('smallMap');	
		} else if ($mapControlSize == 'zoomonly') {
			$map->addControl('smallZoom');	
		}
		if($scale) $map->addControl('scale');
		if($overviewMap) $map->addControl('overviewMap');
		if($mapType) $map->addControl('mapType');
		if($initialMapType) $map->setType($initialMapType);
		
		// check whether to show the directions tab and/or prefill addresses
		if($showDirs && $prefillAddress && !$private) $map->enableDirections(true);
		if($showDirs && !$prefillAddress && !$private) $map->enableDirections();
		
		$streetField = $this->getAddressField('street');
		$cityField = $this->getAddressField('city');
		$stateField = $this->getAddressField('state');
		$zipField = $this->getAddressField('zip');
		$countryField = $this->getAddressField('country');
		
		$where = null;
		// if a user group was set, make sure only those users from that group
		// will be selected in the query
		if($userGroups) {
			$where .= 'usergroup IN ('.$userGroups.')';
		}
		
		// if a storage folder pid was specified, filter by that
		if($pid && $userGroups) {
			$where .= ' AND pid IN ('. $pid .')';
		} else {
			$where .= ' pid IN ('. $pid .')';
		}
		
		// filter out records that shouldn't be shown, e.g. deleted, hidden
		$filter = $this->cObj->enableFields('fe_users');
		
		// if the where clause is empty, add something generic to not mess up the
		// enableFields part.
		if(empty($where)) {
			$where = '1=1';
		}
		$where .= $filter;

		/* Select all frontend users */		
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_users', $where);
		
		// create country and zip code array to keep track of which country and state we already added to the map.
		// the point is to create only one marker per country on a higher zoom level to not
		// overload the map with all the markers and do the same with zip codes.
		$countries = array();
		$cities = array();
		while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))) {
			
			// add check for country and use different field if empty
			// @TODO: make this smarter with TCA or something
			if(empty($row[$countryField]) && $countryField == 'static_info_country') {
				$countryField = 'country';
			} else if(empty($row[$countryField]) && $countryField == 'country') {
				$countryField = 'static_info_country';				
			}
			
			/* Only try to add marker if there's a city */
			if($row[$cityField] != '') {
			
				// if we haven't added a marker for this country yet, do so.
				if(!in_array($row[$countryField], $countries) && !empty($row[$countryField])  && !empty($row[$zipField])  && !empty($row[$cityField])) {

					// add this country to the array
					$countries[] = $row[$countryField];

					// add a little info so users know what to do
					$title = $this->makeTitle(array('name' => $this->pi_getLL('country_zoominfo_title')));
					$description = sprintf($this->pi_getLL('country_zoominfo_desc'), $row[$countryField]);

					// add a marker for this country and only show it between zoom levels 0 and 2.
					$map->addMarkerByAddress(null, $row[$cityField], $row[$stateField], $row[$zipField], $row[$countryField], $title, $description, 0,2);
				}

				
				// if we haven't added a marker for this zip code yet, do so.
				if(!in_array($row[$cityField], $cities) && !empty($row[$cityField]) && !empty($row[$zipField])) {
					
					// add this country to the array
					$cities[] = $row[$cityField];
					
					// add a little info so users know what to do
					$title = $this->makeTitle(array('name' => 'Info'));
					$count = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('count(*)', 'fe_users', $cityField.'="'. $row[$cityField] .'"');
					$count = $count[0]['count(*)'];
					
					// extra processing if private is turned on
					if($private) {
						$maxzoom = 17;
						if($count == 1) {
							$description = sprintf($this->pi_getLL('citycount_si'),$row[$cityField]);
						} else {
							$description = sprintf($this->pi_getLL('citycount_pl'),$count, $row[$cityField]);
						}

					} else {
						$maxzoom = 7;
						$description = sprintf($this->pi_getLL('city_zoominfo_desc'), $row[$cityField]);
					}

					// add a marker for this country and only show it between zoom levels 0 and 2.
					$map->addMarkerByAddress(null, $row[$cityField], $row[$stateField], $row[$zipField], $row[$countryField], $title, $description, 3,$maxzoom);
				}
				
				// make title and description
				$title = $this->makeTitle($row);
				$description = $this->makeDescription($row);
				
				
				// unless we are using privacy, add individual markers as well
				if(!$private) {
					$map->addMarkerByAddress($row[$streetField], $row[$cityField], $row[$stateField], $row[$zipField], $row[$countryField], $title, $description, 8);
				}
			}

		}

		/* Draw the map */
		return $this->pi_wrapInBaseClass($map->drawMap());
	}
	
	function makeTitle($row) {
		$local_cObj = t3lib_div::makeInstance('tslib_cObj'); // Local cObj.
		$local_cObj->start($row, 'fe_users' );
		$output = $local_cObj->cObjGetSingle( $this->conf['marker.']['title'], $this->conf['marker.']['title.'] );
		return $output;
	}
	
	function makeDescription($row) {
		$local_cObj = t3lib_div::makeInstance('tslib_cObj'); // Local cObj.
		$local_cObj->start($row, 'fe_users' );
		$output = $local_cObj->cObjGetSingle( $this->conf['marker.']['description'], $this->conf['marker.']['description.'] );
		return $output;
	}
	
	
	/**
	 * Gets the address mapping from the TCA.
	 *
	 * @param		string		Name of the field to retrieve the mapping for.
	 * @return		name		Name of the field containing address data.
	 */
	function getAddressField($field) {
		if(!$this->loadedTCA) {
			t3lib_div::loadTCA('fe_users');
			$this->loadedTCA = true;
		}
		
		$fieldName = $GLOBALS['TCA']['fe_users']['ctrl']['EXT']['wec_map']['addressFields'][$field];
		if($fieldName == '') {
			$fieldName = $field;
		}
		
		return $fieldName;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/pi2/class.tx_wecmap_pi2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/pi2/class.tx_wecmap_pi2.php']);
}

?>