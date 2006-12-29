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
		
		/* Pull values from Flexform object into individual variables */
		$description = $this->pi_getFFvalue($piFlexForm, "description", "default");
		$street = $this->pi_getFFvalue($piFlexForm, "street", "default");
		$city = $this->pi_getFFvalue($piFlexForm, "city", "default");
		$state = $this->pi_getFFvalue($piFlexForm, "state", "default");
		$zip = $this->pi_getFFvalue($piFlexForm, "zip", "default");
		
		$apiKey = $this->pi_getFFvalue($piFlexForm, "apiKey", "mapConfig");
		$width = $this->pi_getFFvalue($piFlexForm, "mapWidth", "mapConfig");
		$height = $this->pi_getFFvalue($piFlexForm, "mapHeight", "mapConfig");
		
		$largeMap = $this->pi_getFFvalue($piFlexForm, "largeMap", "mapControls");
		$overviewMap = $this->pi_getFFvalue($piFlexForm, "overviewMap", "mapControls");
		$mapType = $this->pi_getFFvalue($piFlexForm, "mapType", "mapControls");

		/* Create the map class and add markers to the map */
		include_once(t3lib_extMgm::extPath('wec_map').'map_service/google/class.tx_wecmap_map_google.php');
		$className=t3lib_div::makeInstanceClassName("tx_wecmap_map_google");
		$map = new $className($apiKey, $width, $height);	

		if($largeMap) $map->addControl('largeMap');
		if($overviewMap) $map->addControl('overviewMap');
		if($mapType) $map->addControl('mapType');

		$map->addMarkerByAddress($street, $city, $state, $zip, $country, "This is my title", $description);
		$content = $map->drawMap();
		
		return $this->pi_wrapInBaseClass($content);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/pi1/class.tx_wecmap_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/pi1/class.tx_wecmap_pi1.php']);
}

?>