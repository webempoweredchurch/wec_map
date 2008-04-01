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
 * Plugin 'Map' for the 'wec_map' extension.
 *
 * @author	Web-Empowered Church Team <map@webempoweredchurch.org>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

/**
 * Simple frontend plugin for displaying an address on a map.
 *
 * @author Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class tx_wecmap_pi3 extends tslib_pibase {
	var $prefixId = 'tx_wecmap_pi3';		// Same as class name
	var $scriptRelPath = 'pi3/class.tx_wecmap_pi3.php';	// Path to this script relative to the extension dir.
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

		// check for WEC Map API static template inclusion
		if(empty($conf['output']) && !empty($conf['defaulttitle.'])) {
			global $LANG;
			if(!is_object($LANG)) {
				require_once(t3lib_extMgm::extPath('lang').'lang.php');
				$LANG = t3lib_div::makeInstance('language');
				$LANG->init($GLOBALS['TSFE']->config['config']['language']);
			}
			$LANG->includeLLFile('EXT:wec_map/locallang_db.xml');
			$out .= $LANG->getLL('wecApiTemplateNotIncluded');
			return $out;
		}

		// check for WEC Table Map static template inclusion
		if(empty($conf['defaulttitle.'])) {
			global $LANG;
			if(!is_object($LANG)) {
				require_once(t3lib_extMgm::extPath('lang').'lang.php');
				$LANG = t3lib_div::makeInstance('language');
				$LANG->init($GLOBALS['TSFE']->config['config']['language']);
			}
			$LANG->includeLLFile('EXT:wec_map/locallang_db.xml');
			$out .= $LANG->getLL('pi3TemplateNotIncluded');
			return $out;
		}
		/* Initialize the Flexform and pull the data into a new object */
		$this->pi_initPIflexform();
		$piFlexForm = $this->cObj->data['pi_flexform'];

		// get config from flexform or TS. Flexforms take precedence.
		$width = $this->pi_getFFvalue($piFlexForm, 'mapWidth', 'mapConfig');
		empty($width) ? $width = $conf['width']:null;

		$height = $this->pi_getFFvalue($piFlexForm, 'mapHeight', 'mapConfig');
		empty($height) ? $height = $conf['height']:null;
		$this->height = $height;

		$pid = $this->pi_getFFvalue($piFlexForm, 'pid', 'default');
		empty($pid) ? $pid = $conf['pid']:null;

		$mapControlSize = $this->pi_getFFvalue($piFlexForm, 'mapControlSize', 'mapControls');
		(empty($mapControlSize) || $mapControlSize == 'none') ? $mapControlSize = $conf['controls.']['mapControlSize']:null;

		$overviewMap = $this->pi_getFFvalue($piFlexForm, 'overviewMap', 'mapControls');
		empty($overviewMap) ? $overviewMap = $conf['controls.']['showOverviewMap']:null;

		$mapType = $this->pi_getFFvalue($piFlexForm, 'mapType', 'mapControls');
		empty($mapType) ? $mapType = $conf['controls.']['showMapType']:null;

		$initialMapType = $this->pi_getFFvalue($piFlexForm, 'initialMapType', 'mapConfig');
		empty($initialMapType) ? $initialMapType = $conf['initialMapType']:null;

		$scale = $this->pi_getFFvalue($piFlexForm, 'scale', 'mapControls');
		empty($scale) ? $scale = $conf['controls.']['showScale']:null;

		$showDirs = $this->pi_getFFvalue($piFlexForm, 'showDirections', 'mapConfig');
		empty($showDirs) ? $showDirs = $conf['showDirections']:null;

		$showWrittenDirs = $this->pi_getFFvalue($piFlexForm, 'showWrittenDirections', 'mapConfig');
		empty($showWrittenDirs) ? $showWrittenDirs = $conf['showWrittenDirections']:null;

		$prefillAddress = $this->pi_getFFvalue($piFlexForm, 'prefillAddress', 'mapConfig');
		empty($prefillAddress) ? $prefillAddress = $conf['prefillAddress']:null;

		$showRadiusSearch = $this->pi_getFFvalue($piFlexForm, 'showRadiusSearch', 'mapConfig');
		empty($showRadiusSearch) ? $showRadiusSearch = $conf['showRadiusSearch']:null;
		
		$showSidebar = $this->pi_getFFvalue($piFlexForm, 'showSidebar', 'mapConfig');
		empty($showSidebar) ? $showSidebar = $conf['showSidebar']:null;
		$this->showSidebar = $showSidebar;
		
		$tables = $this->pi_getFFvalue($piFlexForm, 'tables', 'default');
		empty($tables) ? $tables = $conf['tables']:null;
		if (!empty($tables)) $tables = explode(',', $tables);

		$kml = $this->pi_getFFvalue($piFlexForm, 'kml', 'default');
		empty($kml) ? $kml = $conf['kml']:null;

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
				$link = trim($url['url']);
				$oldAbs = $GLOBALS['TSFE']->absRefPrefix;
				$GLOBALS['TSFE']->absRefPrefix = t3lib_div::getIndpEnv('TYPO3_SITE_URL');
				$linkConf = Array(
					'parameter' => $link,
					'returnLast' => 'url'
				);
				$link = $this->cObj->typolink('', $linkConf);
				$GLOBALS['TSFE']->absRefPrefix = $oldAbs;
				$map->addKML($link);
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

		// check whether to show the directions tab and/or prefill addresses and/or written directions
		if($showDirs && $showWrittenDirs && $prefillAddress) $map->enableDirections(true, $mapName.'_directions');
		if($showDirs && $showWrittenDirs && !$prefillAddress) $map->enableDirections(false, $mapName.'_directions');
		if($showDirs && !$showWrittenDirs && $prefillAddress) $map->enableDirections(true);
		if($showDirs && !$showWrittenDirs && !$prefillAddress) $map->enableDirections();

		// process radius search
		if($showRadiusSearch) {

			// check for POST vars for our map. If there are any, proceed.
			$pRadius = intval(t3lib_div::_POST($mapName.'_radius'));

			if(!empty($pRadius)) {
				$pAddress    = strip_tags(t3lib_div::_POST($mapName.'_address'));
				$pCity       = strip_tags(t3lib_div::_POST($mapName.'_city'));
				$pState      = strip_tags(t3lib_div::_POST($mapName.'_state'));
				$pZip        = strip_tags(t3lib_div::_POST($mapName.'_zip'));
				$pCountry    = strip_tags(t3lib_div::_POST($mapName.'_country'));
				$pKilometers = intval(t3lib_div::_POST($mapName.'_kilometers'));
				
				$data = array(
					'street' => $pAddress,
					'city'	=> $pCity,
					'state' => $pState,
					'zip' => $pZip,
					'country' => $pCountry
				);
				
				$desc = tx_wecmap_shared::render($data, $conf['defaultdescription.']);
				$map->addMarkerIcon($conf['homeicon.']);
				$map->addMarkerByAddress($pAddress, $pCity, $pState, $pZip, $pCountry, '', $desc ,0 , 17, 'homeicon');
				$map->setCenterByAddress($pAddress, $pCity, $pState, $pZip, $pCountry);
				$map->setRadius($pRadius, $pKilometers);
				
			}
		}
		
		// there are two ways of buiding the SQL query:
		// 1. from the data given via flexform
		// 2. all manually from TS
		// So we check whether it's set via TS, and if not we use FF data
		if(empty($conf['tables.'])) {
			
			foreach( $tables as $table ) {
				
				if(!empty($pid)) {
					$where = '1=1' . tx_wecmap_shared::listQueryFromCSV('pid', $pid, $table, 'OR');
				} else {
					$where = '1=1';
				}
				
				$where .= $this->cObj->enableFields($table);
				
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', $table, $where);

				foreach( $res as $key => $data ) {
					$conf['table'] = $table;

					// get title and description
					list($title,$desc) = $this->getTitleAndDescription($conf, $data);
					$marker = $map->addMarkerByTCA($table, $data['uid'], $title, $desc);
					
					// build parameters to pass to the hook
					$params = array('table' => $table, 'data' => $data, 'markerObj' => &$marker);
					$this->processHook($params);

					$this->addSidebarItem($marker, $data['name']);
				}
			}
		} else {
			foreach( $conf['tables.'] as $table => $tconf ) {

				$table = $tconf['table'];
				
				if(!empty($tconf['where'])) {
					$where = $tconf['where'];
				} else {
					$where = '1=1';
				}
				
				$where .= $this->cObj->enableFields($table);

				$res = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', $table, $where);

				// add icon if configured, else see if we just have an iconID
				// and use that. We assume the icon is added somewhere else.
				if(!empty($tconf['icon.']['imagepath'])) {
					$map->addMarkerIcon($tconf['icon.']);			
				} else {
					$tconf['icon.']['iconID'] ? null : $tconf['icon.']['iconID'] = null;
				}
				
				foreach( $res as $key => $data ) {
					
					// get title and description
					list($title,$desc) = $this->getTitleAndDescription($tconf, $data);
					
					$marker = $map->addMarkerByTCA($table, $data['uid'], $title, $desc, 0, 17, $tconf['icon.']['iconID']);

					// build parameters to pass to the hook
					$params = array('table' => $table, 'data' => $data, 'markerObj' => &$marker);
					$this->processHook($params);

					$this->addSidebarItem($marker, $data['name']);
				}
			}
		}
		
		// $map->addKML('http://kml.lover.googlepages.com/my-vacation-photos.kml');
		// gather all the content together
		$content = array();
		$content['map'] = $map->drawMap();
		if($showRadiusSearch) $content['addressForm'] = $this->getAddressForm();
		if($showWrittenDirs) $content['directions'] = $this->getDirections();
		if($showSidebar) $content['sidebar'] = $this->getSidebar();

		// run all the content pieces through TS to assemble them
		$output = tx_wecmap_shared::render($content, $conf['output.']);

		return $this->pi_wrapInBaseClass($output);
	}
	
	/**
	 * Processes the hook
	 *
	 * @return void
	 **/
	function processHook(&$hookParameters) {
		if (isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tx_wecmap_pi3']['markerHook']))	{
			$hooks =& $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tx_wecmap_pi3']['markerHook'];
			$hookReference = null;
			foreach ($hooks as $hookFunction)	{
				t3lib_div::callUserFunction($hookFunction, $hookParameters, $hookReference);
			}
		}
	}
	
	/**
	 * returns an array with title and description
	 *
	 * @return array
	 **/
	function getTitleAndDescription($conf, $data) {

		// merge the table into the data
		$data = array_merge($data, array('table' => $conf['table']));

		// process title only if TS config is present
		if(!empty($conf['title.'])) {
			$title = tx_wecmap_shared::render($data, $conf['title.'], $conf['table']);
		} else {
			$data['name'] = $this->getRecordTitle($conf['table'], $data);
			$title = tx_wecmap_shared::render($data, $this->conf['defaulttitle.'], $conf['table']);
		}

		// process description also only if TS config is present,
		// otherwise display the address
		if(!empty($conf['description.'])) {
			$desc = tx_wecmap_shared::render($data, $conf['description.'], $conf['table']);
		} else {
			$af = $GLOBALS['TCA']['fe_users']['ctrl']['EXT']['wec_map']['addressFields'];
			$ad = array();
			$ad['street']  = $data[$af['street']];
			$ad['city']    = $data[$af['city']];
			$ad['state']   = $data[$af['state']];
			$ad['zip']     = $data[$af['zip']];
			$ad['country'] = $data[$af['country']];

			$desc = tx_wecmap_shared::render($ad, $this->conf['defaultdescription.'], $conf['table']);						
		}

		return array($title, $desc);
	}
	
	function getRecordTitle($table,$row) {
		global $TCA;

		if (is_array($TCA[$table])) {

		// If configured, call userFunc
		if ($TCA[$table]['ctrl']['label_userFunc'])     {
			$params['table'] = $table;
			$params['row'] = $row;
			$params['title'] = '';

			t3lib_div::callUserFunction($TCA[$table]['ctrl']['label_userFunc'],$params,$this);
			$t = $params['title'];
		} else {

			// No userFunc: Build label
			$t = $row[$TCA[$table]['ctrl']['label']];

			if ($TCA[$table]['ctrl']['label_alt'] && ($TCA[$table]['ctrl']['label_alt_force'] || !strcmp($t,'')))   {
				$altFields=t3lib_div::trimExplode(',',$TCA[$table]['ctrl']['label_alt'],1);
				$tA=array();
				$tA[]=$t;
				if ($TCA[$table]['ctrl']['label_alt_force'])    {
					foreach ($altFields as $fN)     {
						$t = trim(strip_tags($row[$fN]));
						if (!empty($t)) $tA[] = $t;
					}
					$t=implode(', ',$tA);
				}
			}
		}

		return $t;
		}
	}
	
	function getAddressForm() {
		$out = tx_wecmap_shared::render(array('map_id' => $this->mapName), $this->conf['addressForm.']);
		return $out;
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
	function addSidebarItem(&$marker, $title) {
		if(!($this->showSidebar && is_object($marker))) return;
		$data = array();
		$data['onclickLink'] = $marker->getClickJS();
		$data['title'] = $title;
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



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/pi3/class.tx_wecmap_pi3.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/pi3/class.tx_wecmap_pi3.php']);
}

?>