<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Jeff Segars <jeff@webempoweredchurch.org>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH.'init.php');
require_once($BACK_PATH.'template.php');

$LANG->includeLLFile('EXT:wec_map/mod2/locallang.xml');
require_once(PATH_t3lib.'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]
	
require_once('../class.tx_wecmap_cache.php');



/**
 * Module 'Geocode Cache' for the 'wec_geocode' extension.
 *
 * @author	Jeff Segars <jeff@webempoweredchurch.org>
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
		return 'bla';
	}

	/**
	 * Shows map
	 *
	 * @return String
	 **/
	function showMap() {
		/* Create the Map object */
		$width = 500;
		$height = 500;
		
		include_once(t3lib_extMgm::extPath('wec_map').'map_service/google/class.tx_wecmap_map_google.php');
		$className=t3lib_div::makeInstanceClassName("tx_wecmap_map_google");
		$map = new $className($apiKey, $width, $height);

		// evaluate map controls based on configuration
		$map->addControl('largeMap');	

		$map->addControl('scale');
		$map->addControl('overviewMap');
		$map->addControl('mapType');
		
		/* Select all frontend users */		
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*", "fe_users", '');

		// create country and zip code array to keep track of which country and state we already added to the map.
		// the point is to create only one marker per country on a higher zoom level to not
		// overload the map with all the markers and do the same with zip codes.
		$countries = array();
		$cities = array();
		while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))) {

			/* Only try to add marker if there's a city */
			if($row['city'] != '') {

				// if we haven't added a marker for this country yet, do so.
				if(!in_array($row['country'], $countries) && !empty($row['country'])  && !empty($row['zip'])  && !empty($row['city'])) {

					// add this country to the array
					$countries[] = $row['country'];
					
					// add a little info so users know what to do
					$title = 'Info';
					$description = 'Zoom in to see more users from this country: ' . $row['country'];
					
					// add a marker for this country and only show it between zoom levels 0 and 2.
					$map->addMarkerByAddress(null, $row['city'], null, $row['zip'], $row['country'], $title, $description, 0,2);
				}

				
				// if we haven't added a marker for this zip code yet, do so.
				if(!in_array($row['city'], $cities) && !empty($row['city']) && !empty($row['zip'])) {
					
					// add this country to the array
					$cities[] = $row['city'];
					
					// add a little info so users know what to do
					$title = 'Info';
					$description = 'Zoom in to see more users from this area.';
					
					// add a marker for this country and only show it between zoom levels 0 and 2.
					$map->addMarkerByAddress(null, $row['city'], null, $row['zip'], $row['country'], $title, $description, 3,7);
				}
				
				// make title and description
				$title = $this->makeTitle($row);
				$description = $this->makeDescription($row);
				
				
				// add all the markers starting at zoom level 3 so we don't crowd the map right away.
				// if private was checked, don't use address to geocode
				if($private) {
					$map->addMarkerByAddress(null, $row['city'], $row['zone'], $row['zip'], $row['static_info_country'], $title, $description, 8);
				} else {
					$map->addMarkerByAddress($row['address'], $row['city'], $row['zone'], $row['zip'], $row['static_info_country'], $title, $description, 8);
				}
			}
		}
		
		$command = '<script type="text/javascript">drawMap();</script>';
		return $map->drawMap() . $command;
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