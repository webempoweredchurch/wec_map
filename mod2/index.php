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

	// DEFAULT initialization of a module [BEGIN]
$LANG->includeLLFile('EXT:wec_map/mod2/locallang.xml');
require_once(PATH_t3lib.'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]

require_once(t3lib_extMgm::extPath('wec_map').'class.tx_wecmap_cache.php');
require_once(t3lib_extMgm::extPath('wec_map').'class.tx_wecmap_shared.php');


/**
 * Module 'Map FE Users' for the 'wec_map' extension.
 *
 * @author	Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package	TYPO3
 * @subpackage	tx_wecmap
 */
class  tx_wecmap_module1 extends t3lib_SCbase {
	var $pageinfo;

	/**
	 * Initializes the Module
	 * @return	void
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		parent::init();

		/*
		if (t3lib_div::_GP('clear_all_cache'))	{
			$this->include_once[] = PATH_t3lib.'class.t3lib_tcemain.php';
		}
		*/
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	void
	 */
	function menuConfig()	{
		global $LANG;
		$this->MOD_MENU = Array (
			'function' => Array (
				'1' => $LANG->getLL('function1'),
				'2' => $LANG->getLL('function2'),
			)
		);
		parent::menuConfig();
	}

	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 * @return	[type]		...
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{

				// Draw the header.
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form='<form action="" method="POST">';

				// JavaScript
			$this->doc->JScode = '
				<script language="javascript" type="text/javascript">
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}
				</script>
			';
			$this->doc->postCode='
				<script language="javascript" type="text/javascript">
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = 0;
				</script>
				<style type="text/css">
					.dirmenu a:link, .dirmenu a:visited {
						text-decoration: underline;
					}
					.description {
						margin-top: 8px;
					}
					
				</style>
			';

			$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);

			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
			$this->content.=$this->doc->divider(5);


			// Render content:
			$this->moduleContent();


			// ShortCut
			if ($BE_USER->mayMakeShortcut())	{
				$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
			}

			$this->content.=$this->doc->spacer(10);
		} else {
				// If no access or if ID == zero

			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;

			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->spacer(10);
		}
	}

	/**
	 * Prints out the module HTML
	 *
	 * @return	void
	 */
	function printContent()	{
		$this->content.=$this->doc->endPage();
		echo $this->content;
	}

	/**
	 * Generates the module content
	 *
	 * @return	void
	 */
	function moduleContent()	{

		switch((string)$this->MOD_SETTINGS['function'])	{
			case 1:
				$this->content.=$this->showMap();
			break;

			case 2:
				$this->content .= $this->mapSettings();
			break;

		}
	}

	function linkSelf($addParams)	{
		return htmlspecialchars('index.php?id='.$this->pObj->id.'&showLanguage='.rawurlencode(t3lib_div::_GP('showLanguage')).$addParams);
	}

	/**
	 * Show map settings
	 *
	 * @return String
	 **/
	function mapSettings() {

		if(t3lib_div::_GP('tx-wecmap-mod1-submit')) {

			$scale = t3lib_div::_GP('tx-wecmap-mod1-scale');

			if($scale == 'on') {
				$scale = 1;
			} else {
				$scale = 0;
			}

			$minimap = t3lib_div::_GP('tx-wecmap-mod1-minimap');

			if($minimap == 'on') {
				$minimap = 1;
			} else {
				$minimap = 0;
			}

			$maptype = t3lib_div::_GP('tx-wecmap-mod1-maptype');

			if($maptype == 'on') {
				$maptype = 1;
			} else {
				$maptype = 0;
			}

			$mapcontrolsize = t3lib_div::_GP('tx-wecmap-mod1-mapcontrolsize');

			// build data array
			$data = array('scale' => $scale, 'minimap' => $minimap, 'maptype' => $maptype, 'mapcontrolsize' => $mapcontrolsize);

			// save to user config
			$GLOBALS['BE_USER']->pushModuleData('tools_txwecmapM2', $data);
		}

		// get module config
		$conf = $GLOBALS['BE_USER']->getModuleData('tools_txwecmapM2');

		// t3lib_div::debug($conf);

		// get config options
		$scale = $conf['scale'];
		$minimap = $conf['minimap'];
		$maptype = $conf['maptype'];
		$mapcontrolsize = $conf['mapcontrolsize'];

		$form = array();
		$form[] = '<form method="POST">';
		$form[] = '<table>';

		// scale option
		$form[] = '<tr>';
		$form[] = '<td><label for="tx-wecmap-mod1-scale">Show Scale:</label></td>';
		if($scale) {
			$form[] = '<td><input type="checkbox" name="tx-wecmap-mod1-scale" id="tx-wecmap-mod1-scale" checked="checked"/></td>';
		} else {
			$form[] = '<td><input type="checkbox" name="tx-wecmap-mod1-scale" id="tx-wecmap-mod1-scale" /></td>';
		}
		$form[] = '</tr><tr>';

		// minimap option
		$form[] = '<tr>';
		$form[] = '<td><label for="tx-wecmap-mod1-minimap">Show Minimap:</label></td>';
		if($minimap) {
			$form[] = '<td><input type="checkbox" name="tx-wecmap-mod1-minimap" id="tx-wecmap-mod1-minimap" checked="checked"/></td>';
		} else {
			$form[] = '<td><input type="checkbox" name="tx-wecmap-mod1-minimap" id="tx-wecmap-mod1-minimap" /></td>';
		}
		$form[] = '</tr>';

		// maptype option
		$form[] = '<tr>';
		$form[] = '<td><label for="tx-wecmap-mod1-maptype">Show Maptype:</label></td>';
		if($maptype) {
			$form[] = '<td><input type="checkbox" name="tx-wecmap-mod1-maptype" id="tx-wecmap-mod1-maptype" checked="checked"/></td>';
		} else {
			$form[] = '<td><input type="checkbox" name="tx-wecmap-mod1-maptype" id="tx-wecmap-mod1-maptype" /></td>';
		}
		$form[] = '</tr>';

		$form[] = '<tr>';
		$form[] = '<td style="vertical-align: top;">Map Control Size:</td>';
		$form[] = '<td>';
		if($mapcontrolsize == 'large') {
			$form[] = '<input type="radio" class="radio" name="tx-wecmap-mod1-mapcontrolsize" value="large" checked="checked" id="mapcontrolsize_0" />';
		} else {
			$form[] = '<input type="radio" class="radio" name="tx-wecmap-mod1-mapcontrolsize" value="large" id="mapcontrolsize_0" />';
		}
		$form[] = '<label for="mapcontrolsize_0">Large</label><br />';

		if($mapcontrolsize == 'small') {
			$form[] = '<input type="radio" class="radio" name="tx-wecmap-mod1-mapcontrolsize" value="small" checked="checked" id="mapcontrolsize_1" />';
		} else {
			$form[] = '<input type="radio" class="radio" name="tx-wecmap-mod1-mapcontrolsize" value="small" id="mapcontrolsize_1" />';
		}
		$form[] = '<label for="mapcontrolsize_1">Small</label><br />';

		if($mapcontrolsize == 'zoomonly') {
			$form[] = '<input type="radio" class="radio" name="tx-wecmap-mod1-mapcontrolsize" value="zoomonly" checked="checked" id="mapcontrolsize_2" />';
		} else {
			$form[] = '<input type="radio" class="radio" name="tx-wecmap-mod1-mapcontrolsize" value="zoomonly" id="mapcontrolsize_2" />';
		}
		$form[] = '<label for="mapcontrolsize_2">Zoom only</label><br />';

		if($mapcontrolsize == 'none' || empty($mapcontrolsize)) {
			$form[] = '<input type="radio" class="radio" name="tx-wecmap-mod1-mapcontrolsize" value="none" checked="checked" id="mapcontrolsize_3" />';
		} else {
			$form[] = '<input type="radio" class="radio" name="tx-wecmap-mod1-mapcontrolsize" value="none" id="mapcontrolsize_3" />';
		}

		$form[] = '<label for="mapcontrolsize_3">None</label>';
		$form[] = '</td>';
		$form[] = '</tr>';


		$form[] = '</table>';
		$form[] = '<input type="submit" name="tx-wecmap-mod1-submit" id="tx-wecmap-mod1-submit" value="Save" />';
		$form[] = '</form>';


		return implode(chr(10), $form);
	}

	/**
	 * Shows map
	 *
	 * @return String
	 **/
	function showMap() {
		global $LANG;
		/* Create the Map object */
		$width = 500;
		$height = 500;
		$conf = $GLOBALS['BE_USER']->getModuleData('tools_txwecmapM2');

		// t3lib_div::debug($GLOBALS['BE_USER']->uc['moduleData']['tools_txwecmapM2']);

		// get options
		$scale = $conf['scale'];
		$minimap = $conf['minimap'];
		$maptype = $conf['maptype'];
		$mapcontrolsize = $conf['mapcontrolsize'];

		$streetField = $this->getAddressField('street');
		$cityField = $this->getAddressField('city');
		$stateField = $this->getAddressField('state');
		$zipField = $this->getAddressField('zip');
		$countryField = $this->getAddressField('country');

		include_once(t3lib_extMgm::extPath('wec_map').'map_service/google/class.tx_wecmap_map_google.php');
		$className=t3lib_div::makeInstanceClassName('tx_wecmap_map_google');
		$map = new $className($apiKey, $width, $height);

		// evaluate map controls based on configuration
		switch ($mapcontrolsize) {
			case 'large':
				$map->addControl('largeMap');
				break;

			case 'small':
				$map->addControl('smallMap');
				break;

			case 'zoomonly':
				$map->addControl('smallZoom');
				break;
			default:
				// do nothing
				break;
		}


		if($scale) $map->addControl('scale');
		if($minimap) $map->addControl('overviewMap');
		if($maptype) $map->addControl('mapType');
		$map->enableDirections(false, 'directions');

		/* Select all frontend users */
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_users', '');

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
				if(!in_array($row[$countryField], $countries) && !empty($row[$countryField])) {

					// add this country to the array
					$countries[] = $row[$countryField];

					// add a little info so users know what to do
					$title = '';
					$description = '<div class="description">'.sprintf($LANG->getLL('country_zoominfo_desc'), $row[$countryField]).'</div>';

					// add a marker for this country and only show it between zoom levels 0 and 2.
					$map->addMarkerByAddress(null, $row[$cityField], $row[$stateField], $row[$zipField], $row[$countryField], $title, $description, 0,2);
				}


				// if we haven't added a marker for this zip code yet, do so.
				if(!in_array($row[$cityField], $cities) && !empty($cityField)) {

					// add this country to the array
					$cities[] = $row[$cityField];

					// add a little info so users know what to do
					$title = '';
					$description = '<div class="description">'.$LANG->getLL('area_zoominfo_desc').'</div>';

					// add a marker for this country and only show it between zoom levels 0 and 2.
					$map->addMarkerByAddress(null, $row[$cityField], $row[$stateField], $row[$zipField], $row[$countryField], $title, $description, 3,7);
				}

				// make title and description
				$title = '<div style="font-size: 110%; font-weight: bold;">'.$row['name'].'</div>';
				$content = '<div>'.$row[$streetField].'<br />'.$row[$cityField].', '.$row[$stateField].' '.$row[$zipField].'<br />'. $row[$countryField].'</div>';


				// add all the markers starting at zoom level 3 so we don't crowd the map right away.
				// if private was checked, don't use address to geocode
				if($private) {
					$map->addMarkerByAddress(null, $row[$cityField], $row[$stateField], $row[$zipField], $row[$countryField], $title, $content, 8);
				} else {
					$map->addMarkerByAddress($row[$streetField], $row[$cityField], $row[$stateField], $row[$zipField], $row[$countryField], $title, $content, 8);
				}
			}
		}

		$content = $map->drawMap();
		$content .= '<div id="directions"></div>';
		return $content;
	}

	function returnEditLink($uid,$title) {
		$tablename = 'fe_users';
		$params = '&edit['.$tablename.']['.$uid.']=edit';
		$out .=    '<a href="#" onclick="'.
		t3lib_BEfunc::editOnClick($params,$GLOBALS['BACK_PATH']).
		'">';
		$out .= $title;
		$out .= '<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/edit2.gif','width="11" height="12"').' title="Edit me" border="0" alt="" />';
		$out .= '</a>';
		return $out;
	}

	/**
	 * Gets the address mapping from the TCA.
	 *
	 * @param		string		Name of the field to retrieve the mapping for.
	 * @return		name		Name of the field containing address data.
	 */
	function getAddressField($field) {
		$fieldName = $GLOBALS['TCA']['fe_users']['ctrl']['EXT']['wec_map']['addressFields'][$field];
		if($fieldName == '') {
			$fieldName = $field;
		}

		return $fieldName;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/mod2/index.php'])	{
include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/mod2/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_wecmap_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>
