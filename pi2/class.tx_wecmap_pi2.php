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
require_once(t3lib_extMgm::extPath('wec_map').'class.tx_wecmap_shared.php');
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
	var $sidebarLinks = array();

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

		// check for WEC Map API static template inclusion
		if(empty($conf['output']) && !(empty($conf['marker.']['title']) && empty($conf['marker.']['description']))) {
			global $LANG;
			$LANG->includeLLFile('EXT:wec_map/locallang_db.xml');
			$out .= $LANG->getLL('wecApiTemplateNotIncluded');
			return $out;
		}
		
		// check for WEC FE Map static template inclusion
		if(empty($conf['marker.']['title']) && empty($conf['marker.']['description'])) {
			global $LANG;
			$LANG->includeLLFile('EXT:wec_map/locallang_db.xml');
			$out .= $LANG->getLL('pi2TemplateNotIncluded');
			return $out;
		}
		
		/* Initialize the Flexform and pull the data into a new object */
		$this->pi_initPIflexform();
		$piFlexForm = $this->cObj->data['pi_flexform'];

		// get config from flexform or TS. Flexforms take precedence.
		$width = $this->pi_getFFvalue($piFlexForm, 'mapWidth', 'default');
		empty($width) ? $width = $conf['width']:null;

		$height = $this->pi_getFFvalue($piFlexForm, 'mapHeight', 'default');
		empty($height) ? $height = $conf['height']:null;
		$this->height = $height;

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

		$showWrittenDirs = $this->pi_getFFvalue($piFlexForm, 'showWrittenDirections', 'default');
		empty($showWrittenDirs) ? $showWrittenDirs = $conf['showWrittenDirections']:null;

		$prefillAddress = $this->pi_getFFvalue($piFlexForm, 'prefillAddress', 'default');
		empty($prefillAddress) ? $prefillAddress = $conf['prefillAddress']:null;
		
		$showRadiusSearch = $this->pi_getFFvalue($piFlexForm, 'showRadiusSearch', 'default');
		empty($showRadiusSearch) ? $showRadiusSearch = $conf['showRadiusSearch']:null;
		
		$showSidebar = $this->pi_getFFvalue($piFlexForm, 'showSidebar', 'default');
		empty($showSidebar) ? $showSidebar = $conf['showSidebar']:null;
		$this->showSidebar = $showSidebar;

		$kml = $conf['kml'];

		$centerLat = $conf['centerLat'];

		$centerLong = $conf['centerLong'];

		$zoomLevel = $conf['zoomLevel'];

		$mapName = $conf['mapName'];
		if(empty($mapName)) $mapName = 'map'.$this->cObj->data['uid'];
		$this->mapName = $mapName;


		/* Create the Map object */
		include_once(t3lib_extMgm::extPath('wec_map').'map_service/google/class.tx_wecmap_map_google.php');
		$className=t3lib_div::makeInstanceClassName('tx_wecmap_map_google');
		$map = new $className(null, $width, $height, $centerLat, $centerLong, $zoomLevel, $mapName);

		// get kml urls for each included record
		if(!empty($kml)) {
			$where = 'uid IN ('.$kml.')';
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('url', 'tx_wecmap_external', $where);			
			foreach( $res as $key => $url ) {
				$map->addKML($url['url']);
			}
		}
		
		// process radius search
		if($showRadiusSearch) {

			// check for POST vars for our map. If there are any, proceed.
			$pRadius = intval(t3lib_div::_POST($mapName.'_radius'));

			if(!empty($pRadius)) {
				$pAddress = strip_tags(t3lib_div::_POST($mapName.'_address'));
				$pCity    = strip_tags(t3lib_div::_POST($mapName.'_city'));
				$pState   = strip_tags(t3lib_div::_POST($mapName.'_state'));
				$pZip     = strip_tags(t3lib_div::_POST($mapName.'_zip'));
				$pCountry = strip_tags(t3lib_div::_POST($mapName.'_country'));
				$pKilometers = intval(t3lib_div::_POST($mapName.'_kilometers'));

				$map->addMarkerIcon($conf['homeicon.']);
				$map->addMarkerByAddress($pAddress, $pCity, $pState, $pZip, $pCountry, 'Source', '',0 , 17, 'homeicon');
				$map->setCenterByAddress($pAddress, $pCity, $pState, $pZip, $pCountry);
				$map->setRadius($pRadius, $pKilometers);
				
			}
			
		}
		
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

		// set up groups:
		// country
		$countryConf = array();
		$countryConf['icon'] = $conf['groups.']['country.']['icon.'];
		$countryConf['minzoom'] = $conf['groups.']['country.']['zoom.']['min'];
		$countryConf['maxzoom'] = $conf['groups.']['country.']['zoom.']['max'];
		// country icon, if configured
		if(!empty($countryConf['icon']['imagepath'])) {
			$map->addMarkerIcon($countryConf['icon']);			
		} else {
			$countryConf['icon']['iconID'] ? null : $countryConf['icon']['iconID'] = null;
		}

		
		// city
		$cityConf = array();
		$cityConf['icon'] = $conf['groups.']['city.']['icon.'];
		$cityConf['minzoom'] = $conf['groups.']['city.']['zoom.']['min'];
		$cityConf['maxzoom'] = $conf['groups.']['city.']['zoom.']['max'];
		// country icon, if configured
		if(!empty($cityConf['icon']['imagepath'])) {
			$map->addMarkerIcon($cityConf['icon']);			
		} else {
			$cityConf['icon']['iconID'] ? null : $cityConf['icon']['iconID'] = null;
		}

		// single
		$singleConf = array();
		$singleConf['icon'] = $conf['groups.']['single.']['icon.'];
		$singleConf['minzoom'] = $conf['groups.']['single.']['zoom.']['min'];
		$singleConf['maxzoom'] = $conf['groups.']['single.']['zoom.']['max'];

		// country icon, if configured
		if(!empty($singleConf['icon']['imagepath'])) {
			$map->addMarkerIcon($singleConf['icon']);			
		} else {
			$singleConf['icon']['iconID'] ? null : $singleConf['icon']['iconID'] = null;
		}
		
		
		// check whether to show the directions tab and/or prefill addresses and/or written directions
		if($showDirs && $showWrittenDirs && $prefillAddress) $map->enableDirections(true, $mapName.'_directions');
		if($showDirs && $showWrittenDirs && !$prefillAddress) $map->enableDirections(false, $mapName.'_directions');
		if($showDirs && !$showWrittenDirs && $prefillAddress) $map->enableDirections(true);
		if($showDirs && !$showWrittenDirs && !$prefillAddress) $map->enableDirections();

		$streetField  = $this->getAddressField('street');
		$cityField    = $this->getAddressField('city');
		$stateField   = $this->getAddressField('state');
		$zipField     = $this->getAddressField('zip');
		$countryField = $this->getAddressField('country');


		// start where clause
		$where = '1=1';

		// if a user group was set, make sure only those users from that group
		// will be selected in the query
		if($userGroups) {
			$where .= tx_wecmap_shared::listQueryFromCSV('usergroup', $userGroups, 'fe_users');
		}

		// if a storage folder pid was specified, filter by that
		if($pid) {
			$where .= tx_wecmap_shared::listQueryFromCSV('pid', $pid, 'fe_users', 'OR');
		}

		// filter out records that shouldn't be shown, e.g. deleted, hidden
		$where .= $this->cObj->enableFields('fe_users');

		/* Select all frontend users */
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_users', $where);

		// create country and zip code array to keep track of which country and state we already added to the map.
		// the point is to create only one marker per country on a higher zoom level to not
		// overload the map with all the markers, and do the same with zip codes.
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

					// combine title config to pass to render function
					$title_conf = array('title' => $conf['marker.']['title'], 'title.' => $conf['marker.']['title.']);

					// add a little info so users know what to do
					$title = tx_wecmap_shared::render(array('name' => $this->pi_getLL('country_zoominfo_title')), $title_conf);
					$description = sprintf($this->pi_getLL('country_zoominfo_desc'), $row[$countryField]);

					// add a marker for this country and only show it between the configured zoom level.
					$map->addMarkerByAddress(null, $row[$cityField], $row[$stateField], $row[$zipField], $row[$countryField], $title, $description, $countryConf['minzoom'], $countryConf['maxzoom'], $countryConf['icon']['iconID']);
				}


				// if we haven't added a marker for this zip code yet, do so.
				if(!in_array($row[$cityField], $cities) && !empty($row[$cityField]) && !empty($row[$zipField])) {

					// add this country to the array
					$cities[] = $row[$cityField];

					// combine title config to pass to render function
					$title_conf = array('title' => $conf['marker.']['title'], 'title.' => $conf['marker.']['title.']);
					
					// add a little info so users know what to do
					$title = tx_wecmap_shared::render(array('name' => 'Info'), $title_conf);

					$count = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('count(*)', 'fe_users', $cityField.'="'. $row[$cityField] .'"');
					$count = $count[0]['count(*)'];

					// extra processing if private is turned on
					if($private) {
						$maxzoom = $singleConf['maxzoom'];
						if($count == 1) {
							$description = sprintf($this->pi_getLL('citycount_si'),$row[$cityField]);
						} else {
							$description = sprintf($this->pi_getLL('citycount_pl'),$count, $row[$cityField]);
						}

					} else {
						$maxzoom = $cityConf['maxzoom'];
						$description = sprintf($this->pi_getLL('city_zoominfo_desc'), $row[$cityField]);
					}

					// add a marker for the city level and only show it 
					// either from city-min to single-max or city-min to city-max, depending on privacy settings
					$marker = $map->addMarkerByAddress(null, $row[$cityField], $row[$stateField], $row[$zipField], $row[$countryField], $title, $description, $cityConf['minzoom'],$maxzoom, $cityConf['icon']['iconID']);
				}

				// make title and description
				$content = tx_wecmap_shared::render($row, $conf['marker.']);

				// unless we are using privacy, add individual markers as well
				if(!$private) {
					$marker = $map->addMarkerByAddress($row[$streetField], $row[$cityField], $row[$stateField], $row[$zipField], $row[$countryField], '', $content, $singleConf['minzoom'], $singleConf['maxzoom'], $singleConf['icon']['iconID']);
					$this->addSidebarItem($marker, $row['name']);
				}
			}

		}

		// gather all the content together
		$content = array();
		$content['map'] = $map->drawMap();
		if($showRadiusSearch) 	$content['addressForm'] = $this->getAddressForm();
		if($showWrittenDirs)  	$content['directions']  = $this->getDirections();
		if($showSidebar)		$content['sidebar']     = $this->getSidebar();

		// run all the content pieces through TS to assemble them
		$output = tx_wecmap_shared::render($content, $conf['output.']);

		return $this->pi_wrapInBaseClass($output);
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
	
	/**
	 * adds a sidebar item corresponding to the given marker.
	 * Does so only if the sidebar is enabled.
	 *
	 * @return void
	 **/
	function addSidebarItem(&$marker, $title) {
		if(!($this->showSidebar && is_object($marker))) return;
		$data = array();
		$data['onclickLink'] = $marker->getClickJS();
		$data['title'] = $title;
		$this->sidebarLinks[] = tx_wecmap_shared::render($data, $this->conf['sidebarItem.']);
	}
	
	function getAddressForm() {
		$out = tx_wecmap_shared::render(array('map_id' => $this->mapName), $this->conf['addressForm.']);
		return $out;
	}
	
	function getDirections() {
		$out = tx_wecmap_shared::render(array('map_id' => $this->mapName), $this->conf['directions.']);
		return $out;
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



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/pi2/class.tx_wecmap_pi2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/pi2/class.tx_wecmap_pi2.php']);
}

?>
