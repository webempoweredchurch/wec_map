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
unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH.'init.php');
require_once($BACK_PATH.'template.php');

require_once(PATH_t3lib.'class.t3lib_install.php');
require_once(PATH_t3lib.'class.t3lib_extmgm.php');

$LANG->includeLLFile('EXT:wec_map/mod1/locallang.xml');
require_once(PATH_t3lib.'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]
	
require_once('../class.tx_wecmap_cache.php');



/**
 * Module 'WEC Map Admin' for the 'wec_map' extension.
 *
 * @author	Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package	TYPO3
 * @subpackage	tx_wecmap
 */
class  tx_wecmap_module1 extends t3lib_SCbase {
	var $pageinfo;
	var $extKey = 'wec_map';

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
		$this->MOD_MENU = array (
			'function' => array (
				'1' => $LANG->getLL('function1'),
				'2' => $LANG->getLL('function2'),
				'3' => $LANG->getLL('function3'),
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
			$this->content.=$this->moduleContent();

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
				$this->content.=$this->geocodeAdmin();
			break;
			case 2:
				$this->content.=$this->apiKeyAdmin();
			break;
			case 3:
				$this->content.=$this->batchGeocode();
			break;
		}
	}

	function linkSelf($addParams)	{
		return htmlspecialchars('index.php?id='.$this->pObj->id.'&showLanguage='.rawurlencode(t3lib_div::_GP('showLanguage')).$addParams);
	}
	
	/**
	 * Rendering the encode-cache content
	 *
	 * @param	array		The Page tree data
	 * @return	string		HTML for the information table.
	 */
	function geocodeAdmin()	{
		global $LANG;
		
		$uid = t3lib_div::_GP('uid');
		$latitude = t3lib_div::_GP('latitude');
		$longitude = t3lib_div::_GP('longitude');
		$cmd = t3lib_div::_GP('cmd');
		switch($cmd) {
			case 'update' : 
				tx_wecmap_cache::updateByUID($uid, $latitude, $longitude);
				unset($cmd);
				unset($uid);
				break;
			case 'delete' :
				if ($uid=="all") {
					tx_wecmap_cache::deleteAll();
				} else {
					tx_wecmap_cache::deleteByUID($uid);
				}
				unset($cmd);
				unset($uid);
				break;
		}

		// Select rows:
		$displayRows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*','tx_wecmap_cache','');

		foreach($displayRows as $row) {				
			// Add icon/title and ID:
			$cells = array();
			$cells[] = '<td><a href="'.$this->linkSelf('&cmd=edit&uid='.$row['address_hash']).'"><img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/edit2.gif','width="11" height="12"').' title="'.$LANG->getLL('editAddress').'" alt="'.$LANG->getLL('editAddress').'" /></a></td>';
			$cells[] = '<td><a href="'.$this->linkSelf('&cmd=delete&uid='.$row['address_hash']).'"><img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/garbage.gif','width="11" height="12"').' title="'.$LANG->getLL('deleteAddress').'" alt="'.$LANG->getLL('deleteAddress').'" /></a></td>';
			
			$cells[] = '<td>'.$row['address'].'</td>';
				
			if ($row['address_hash'] == $uid && $cmd = 'edit') {
				$cells[] = '<td><input name="latitude" value="'.$row['latitude'].'" size="8"/></td>';
				$cells[] = '<td><input name="longitude" value="'.$row['longitude'].'" size="8"/></td>';
				$cells[] = '<td><input type="submit" value="'.$LANG->getLL('updateAddress').'" /></td>';
			} else {
				$cells[] = '<td>'.$row['latitude'].'</td>';
				$cells[] = '<td>'.$row['longitude'].'</td>';
				$cells[] = '<td>&nbsp;</td>';
			}
										
			// Compile Row:
			$output.= '
				<tr class="bgColor'.($cc%2 ? '-20':'-10').'">
					'.implode('
					',$cells).'
				</tr>';
			$cc++;

			$countDisplayed++;
		}

		list($count_allInTable) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('count(*) AS count','tx_wecmap_cache','');

		// Create header:
		$cells = array();
		$cells[] = '<td colspan="2">&nbsp;</td>';
		$cells[] = '<td>'.$LANG->getLL('address').'</td>';
		$cells[] = '<td>'.$LANG->getLL('latitude').'</td>';
		$cells[] = '<td>'.$LANG->getLL('longitude').'</td>';
		$cells[] = '<td>&nbsp;</td>';
		
		$output = '
			<tr class="bgColor5 tableheader">
				'.implode('
				',$cells).'
			</tr>'.$output;

			// Compile final table and return:
		
		$output = '
		<br/>
		<br/>
		'.$LANG->getLL('totalCachedAddresses').': <b>'.$count_allInTable['count'].'</b> '.
			'<a href="'.$this->linkSelf('&cmd=delete&uid=all').'">'.
			'<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/garbage.gif','width="11" height="12"').' title="'.$LANG->getLL('deleteCache').'" alt="'.$LANG->getLL('deleteCache').'" />'.
			'</a>'.
		'<br/>'.
		'<table border="0" cellspacing="1" cellpadding="3" id="tx-wecmap-cache" class="lrPadding c-list">'.$output.'</table>';
		
		
		if ($cmd == 'edit') {
			$output = '<form action="" method="POST"><input name="cmd" type="hidden" value="update">'.$output.'</form>';
		}

		return $output;
	}

	/*
	 * Admin module for setting Google Maps API Key.
	 * @return		string		HTML output of the module.
	 */
	function apiKeyAdmin() {
		global $TYPO3_CONF_VARS, $LANG;
	
		$cmd = t3lib_div::_GP('cmd');
		$newKey = t3lib_div::_GP('key');
		switch($cmd) {
			case 'setkey' : 
				$this->saveApiKey($newKey);
				$apiKey = $newKey;
				
				unset($cmd);
				unset($newKey);
				break;
				
			default :
				$apiKey = $this->getApiKey();
				break;
		}
		
		$content[] = '<p style="margin-bottom:15px;">';
		$content[] = $LANG->getLL('apiInstructions');
		$content[] = '</p>';
		
		$content[] = '<form action="" method="POST">';
		$content[] = '<input name="cmd" type="hidden" value="setkey" />';
		$content[] = '<label for="key">'.$LANG->getLL('googleMapsApiKey').'</label>';
		$content[] = '<input style="width: 50em;" name="key" value="'.$apiKey.'" />';
		$content[] = '<input type="submit" value="'.$LANG->getLL('submit').'"/>';
		$content[] = '</form>';
		
		return implode(chr(10), $content);
	}
	
	/*
	 * Looks up the API key in extConf within localconf.php
	 * @return		string		The Google Maps API key.
	 */
	function getApiKey() {
		
		require_once(t3lib_extMgm::extPath('wec_map').'class.tx_wecmap_backend.php');
		$apiKey = tx_wecmap_backend::getExtConf('apiKey.google');			
		
		return $apiKey;
	}
	
	/*
	 * Saves the API key to extConf in localconf.php.
	 * @param		string		The new Google Maps API Key.
	 * @return		none
	 */	
	function saveApiKey($apiKey) {
		global $TYPO3_CONF_VARS;
		
		$extConf = unserialize($TYPO3_CONF_VARS['EXT']['extConf'][$this->extKey]);
		$extConf['apiKey.']['google'] = $apiKey;
		
		// Instance of install tool
		$instObj = t3lib_div::makeInstance('t3lib_install');
		$instObj->allowUpdateLocalConf =1;
		$instObj->updateIdentity = $this->extKey;

		// Get lines from localconf file
		$lines = $instObj->writeToLocalconf_control();
		$instObj->setValueInLocalconfFile($lines, '$TYPO3_CONF_VARS[\'EXT\'][\'extConf\'][\''.$this->extKey.'\']', serialize($extConf));
		$instObj->writeToLocalconf_control($lines);
		
		t3lib_extMgm::removeCacheFiles();
	}
	
	function batchGeocode() {
		global $TCA, $LANG;
		$content = array();
		
	 	require_once(t3lib_extMgm::extPath('wec_map').'class.tx_wecmap_batchgeocode.php');
		$batchGeocode = t3lib_div::makeInstance('tx_wecmap_batchgeocode');
		$batchGeocode->addAllTables();
		
		$processedAddresses = $batchGeocode->processedAddresses();
		$totalAddresses = $batchGeocode->recordCount();
		$progressBarWidth = round($processedAddresses / $totalAddresses * 100);
		
		$content[] = '<h3>Batch Geocode</h3>';
		
		$content[] = '<p>Address information is available in these tables:<p>';
		$content[] = '<ul>';
		foreach($TCA as $tableName => $tableContents) {
			if($tableContents['ctrl']['EXT']['wec_map']['isMappable']) {
				$title = $LANG->sL($tableContents['ctrl']['title']);
				$content[] = '<li>'.$title.'</li>';
			}
		}
		$content[] = '</ul>';
		
		$content[] = '<script type="text/javascript" src="../contrib/prototype/prototype.js"></script>';
		$content[] = '<script type="text/javascript">
						function startGeocode() {
							var updater;
							
							$(\'startGeocoding\').disable();
							$(\'status\').setStyle({display: \'block\'});
														
							updater = new Ajax.PeriodicalUpdater(\'status\', \'tx_wecmap_batchgeocode_ai.php\',
							{
								method: \'get\',
								frequency: 5,
								decay: 10,
				  			});
						}
						</script>';
							
		$content[] = '<div id="status" style="margin-bottom: 5px; display:none;">';
		$content[] = '<div id="bar" style="width:300px; height:20px; border:1px solid black">
						<div id="progress" style="width:'.$progressBarWidth.'%; height:20px; background-color:red"></div>
					</div>
					<p>Processed '.$processedAddresses.' records of '.$totalAddresses.'</p>';
					
		$content[] = '</div>';
		
		$content[] = '<input id="startGeocoding" type="submit" value="Start Geocoding" onclick="startGeocode(); return false;"/>';
		
		return implode(chr(10), $content);
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/mod1/index.php'])	{
include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/mod1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_wecmap_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>