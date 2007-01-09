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
 * Plugin 'Frontend User Map' for the 'wec_map' extension.
 *
 * @author	Web Empowered Church Team <map@webempoweredchurch.org>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

/**
 * Frontend User Map plugin for displaying all frontend users on a map.  
 *
 * @author Web Empowered Church Team <map@webempoweredchurch.org>
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
		
		/* Pull values from Flexform object into individual variables */		
		$apiKey = $this->pi_getFFvalue($piFlexForm, "apiKey", "default");
		$width = $this->pi_getFFvalue($piFlexForm, "mapWidth", "default");
		$height = $this->pi_getFFvalue($piFlexForm, "mapHeight", "default");
		$userGroups = $this->pi_getFFvalue($piFlexForm, "userGroups", "default");
		
		$mapControlSize = $this->pi_getFFvalue($piFlexForm, "mapControlSize", "mapControls");
		$overviewMap = $this->pi_getFFvalue($piFlexForm, "overviewMap", "mapControls");
		$mapType = $this->pi_getFFvalue($piFlexForm, "mapType", "mapControls");
		$scale = $this->pi_getFFvalue($piFlexForm, "scale", "mapControls");
		
		/* Create the Map object */
		include_once(t3lib_extMgm::extPath('wec_map').'map_service/google/class.tx_wecmap_map_google.php');
		$className=t3lib_div::makeInstanceClassName("tx_wecmap_map_google");
		$map = new $className($apiKey, $width, $height);
		
		// set map controls as defined in the flex form
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
		
		// if a user group was set, make sure only those users from that group
		// will be selected in the query
		if($userGroups) {
			$where = "usergroup IN (".$userGroups.")";
		}
		
		/* Select all frontend users */		
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*", "fe_users", $where);
		
		// create country and zip code array to keep track of which country and state we already added to the map.
		// the point is to create only one marker per country on a higher zoom level to not
		// overload the map with all the markers and do the same with zip codes.
		$countries = array();
		$cities = array();
		while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))) {

			/* Only try to add marker if there's a city */
			if($row['city'] != '') {

				// if we haven't added a marker for this country yet, do so.
				if(!in_array($row['country'], $countries) && !empty($row['country'])) {
					
					// add this country to the array
					$countries[] = $row['country'];
					
					// add a little info so users know what to do
					$title = 'Info';
					$description = 'Zoom in to see more users from this country: ' . $row['country'];
					
					// add a marker for this country and only show it between zoom levels 0 and 2.
					$map->addMarkerByAddress(null, $row['city'], null, null, $row['country'], $title, $description, 0,2);
				}

				
				// if we haven't added a marker for this zip code yet, do so.
				if(!in_array($row['city'], $cities) && !empty($row['city'])) {
					
					// add this country to the array
					$cities[] = $row['city'];
					
					// add a little info so users know what to do
					$title = 'Info';
					$description = 'Zoom in to see more users from this area.';
					
					// add a marker for this country and only show it between zoom levels 0 and 2.
					$map->addMarkerByAddress(null, $row['city'], null, null, $row['country'], $title, $description, 3,7);
				}
				
				// make title and description
				$title = $this->makeTitle($row);
				$description = $this->makeDescription($row);
				
				// add all the markers starting at zoom level 3 so we don't crowd the map right away.
				$map->addMarkerByAddress($row['address'], $row['city'], $row['zone'], $row['zip'], $row['static_info_country'], $title, $description, 8);
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
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/pi2/class.tx_wecmap_pi2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/pi2/class.tx_wecmap_pi2.php']);
}

?>