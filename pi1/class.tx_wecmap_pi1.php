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
 * Simple frontend plugin for displaying an address on a map.  
 *
 * @author Web Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class tx_wecmap_pi1 extends tslib_pibase {
	var $prefixId = 'tx_wecmap_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_wecmap_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'wec_map';	// The extension key.
	var $pi_checkCHash = TRUE;
	
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
		
		/* Initialize the Flexform and pull the data into a new object */
		$this->pi_initPIflexform();
		$piFlexForm = $this->cObj->data['pi_flexform'];
		
		// get configuration from flexform or TS. Flexform values take
		// precedence.
		$title = $this->pi_getFFvalue($piFlexForm, "title", "default");
		empty($title) ? $title = $conf['title']:null;
		
		$description = $this->pi_getFFvalue($piFlexForm, "description", "default");
		empty($description) ? $description = $conf['description']:null;
		
		$street = $this->pi_getFFvalue($piFlexForm, "street", "default");
		empty($street) ? $street = $conf['street']:null;
		
		$city = $this->pi_getFFvalue($piFlexForm, "city", "default");
		empty($city) ? $city = $conf['city']:null;
		
		$state = $this->pi_getFFvalue($piFlexForm, "state", "default");
		empty($state) ? $state = $conf['state']:null;
		
		$zip = $this->pi_getFFvalue($piFlexForm, "zip", "default");
		empty($zip) ? $zip = $conf['zip']:null;
		
		$country = $this->pi_getFFvalue($piFlexForm, "country", "default");
		empty($country) ? $country = $conf['country']:null;
		
		$apiKey = $this->pi_getFFvalue($piFlexForm, "apiKey", "mapConfig");
		empty($apiKey) ? $apiKey = $conf['apiKey']:null;

		$width = $this->pi_getFFvalue($piFlexForm, "mapWidth", "mapConfig");
		empty($width) ? $width = $conf['width']:null;
		
		$height = $this->pi_getFFvalue($piFlexForm, "mapHeight", "mapConfig");
		empty($height) ? $height = $conf['height']:null;
		
		$mapControlSize = $this->pi_getFFvalue($piFlexForm, "mapControlSize", "mapControls");
		empty($mapControlSize) ? $mapControlSize = $conf['controls.']['mapControlSize']:null;
		
		$overviewMap = $this->pi_getFFvalue($piFlexForm, "overviewMap", "mapControls");
		empty($overviewMap) ? $overviewMap = $conf['controls.']['showOverviewMap']:null;
				
		$mapType = $this->pi_getFFvalue($piFlexForm, "mapType", "mapControls");
		empty($mapType) ? $mapType = $conf['controls.']['showMapType']:null;
				
		$scale = $this->pi_getFFvalue($piFlexForm, "scale", "mapControls");
		empty($scale) ? $scale = $conf['controls.']['showScale']:null;

		/* Create the map class and add markers to the map */
		include_once(t3lib_extMgm::extPath('wec_map').'map_service/google/class.tx_wecmap_map_google.php');
		$className = t3lib_div::makeInstanceClassName("tx_wecmap_map_google");
		$map = new $className($apiKey, $width, $height);	

		// evaluate config to see which map controls we need to show
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
		
		// put all the data into an array
		$data['city'] = $city;
		$data['state'] = $state;
		$data['street'] = $street;
		$data['zip'] = $zip;
		$data['country'] = $country;
		$data['title'] = $title;
		
		if(empty($description)) $description = $this->makeDescription($data);
		
		// add the marker to the map
		$map->addMarkerByAddress($street, $city, $state, $zip, $country, $title, $description);
		
		// draw the map
		$content = $map->drawMap();
		
		return $this->pi_wrapInBaseClass($content);
	}
	
	function makeDescription($row) {
		$local_cObj = t3lib_div::makeInstance('tslib_cObj'); // Local cObj.
		$local_cObj->start($row, 'fe_users' );
		$output = $local_cObj->cObjGetSingle( $this->conf['marker.']['description'], $this->conf['marker.']['description.'] );
		return $output;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/pi1/class.tx_wecmap_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/pi1/class.tx_wecmap_pi1.php']);
}

?>