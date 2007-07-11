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
				'2' => $LANG->getLL('function3'),
				'3' => $LANG->getLL('function2'),
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
				$this->content.=$this->batchGeocode();
			break;
			case 3:
				$this->content.=$this->apiKeyAdmin();
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
	
		$count 	= $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('COUNT(*)', 'tx_wecmap_cache','');
		$count = $count[0]['COUNT(*)'];
	
		require_once('class.tx_wecmap_recordhandler.php');
		$recordhandlerClass = t3lib_div::makeInstanceClassname('tx_wecmap_recordhandler');
		$recordHandler = new $recordhandlerClass($count);
		
		global $LANG;
		
		$uid = t3lib_div::_GP('uid');
		$latitude = t3lib_div::_GP('latitude');
		$longitude = t3lib_div::_GP('longitude');
		$cmd = t3lib_div::_GP('cmd');
		$page = intval(t3lib_div::_GP('page'));
		if(empty($page)) $page = 1;
		
		$limit = $this->getPageLimit($page, $itemsPerPage);

		$output = $recordHandler->displaySearch();
		$output .= $recordHandler->displayTable($page);
		
		if ($cmd == 'edit') {
			$output = '<form action="" method="POST"><input name="cmd" type="hidden" value="update">'.$output.'</form>';
		}
		
		$js = $recordHandler->getJS();
	
		return $js.chr(10).$output;
	}

	/*
	 * Admin module for setting Google Maps API Key.
	 * @return		string		HTML output of the module.
	 */
	function apiKeyAdmin() {
		global $TYPO3_CONF_VARS, $LANG;
		
		$blankDomainValue = 'Enter domain....';
		
		$allDomains = $this->getAllDomains();

		$cmd = t3lib_div::_GP('cmd');
		
		switch($cmd) {
			case 'setkey' :
				
				// transform the POST array to our needs.
				// we then get a simple array in the form:
				// array('domain1', 'domain2', 'key1', 'key2'), etc.
				$post = $_POST;
				unset($post['cmd']);
				unset($post['SET']);
				unset($post['x']);
				unset($post['y']);
				
				ksort($post);
				$post = array_values($post);

				// prepare the two arrays we need in the loop
				$extconfArray = array();
				$returnArray = array();
				
				// get total number of domain->key pairs
				$number = count($post)/2;

				// loop through all the pairs
				for ( $i=0; $i < $number; $i++ ) {
					
					// get the domain and key
					$curKey = $post[$i+$number];
					$curDomain = $post[$i];
					
					// if there is no key, we don't want to save it in extconf
					if(!empty($curKey)) $extconfArray[$curDomain] = $curKey;
					
					// get all but manually added domains
					$domains1 = $this->getRequestDomain();
					$domains2 = $this->getDomainRecords();
					$domains = array_keys(array_merge($domains1, $domains2));
					
					// if there is no domain, or we want to delete a domain, we won't return it.
					// we also make sure not to recommend domains that were just deleted but manually added before
					if(!empty($curDomain) && !(!empty($allDomains[$curDomain]) && empty($curKey) && !in_array($curDomain, $domains))) $returnArray[$curDomain] = $curKey;
					

				}

				// save the domain->key pairs, even if empty
				$this->saveApiKey($extconfArray);

				// sort the array and reverse it so we show filled out records first, empty ones last
				asort($returnArray);
				$allDomains = array_reverse($returnArray);

				break;
				
			default :
				break;
		}
		
		$content = array();
		$content[] = '<style type="text/css" media="screen">input {border: none;}</style>';
		$content[] = '<p style="margin-bottom:15px;">';
		$content[] = $LANG->getLL('apiInstructions');
		$content[] = '</p>';
		
		$content[] = '<form action="" method="POST">';
		$content[] = '<input name="cmd" type="hidden" value="setkey" />';
		
		$index = 0;
		
		// get number of entries that have a key
		$tempDomains = $allDomains;
		foreach( $tempDomains as $key => $value) {
			if(empty($value)) unset($tempDomains[$key]);
		}
		$number = count($tempDomains);
		
		foreach( $allDomains as $key => $value ) {
			
			// show the first summary text above all the already saved domains
			if($number != 0 && $index == 0) {
				$content[] = '<h1>Existing Domains</h1>';
				$content[] = '<p style="margin-bottom:15px;">';
				$content[] = $LANG->getLL('alreadySavedDomains');
				$content[] = '</p>';
			} else if ($number == $index) {
				$content[] = '<h1>Suggested Domains</h1>';
				$content[] = '<p style="margin-bottom:15px;">';
				$content[] = $LANG->getLL('suggestedDomains');
				$content[] = '</p>';
			}
			
			if($index < $number) {
				$deleteButton = '<input type="image" '.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/garbage.gif','width="11" height="12"').' onclick="document.getElementById(\'key_'. $index .'\').value = \'\';" />';	
			} else {
				$deleteButton = null;
			}
			
			$content[] = '<div class="domain-item" style="margin-bottom: 15px;">';
			$content[] = '<div style="width: 25em;"><strong>'. $key .'</strong> '. $deleteButton .'</div>';
			$content[] = '<div><label style="display: none;" for="key_'. $index .'">'.$LANG->getLL('googleMapsApiKey').': </label></div>';
			$content[] = '<div><input style="width: 58em;" name="key_'. $index .'" value="'.$value.'" /></div>';
			$content[] = '<input type="hidden" name="domain_'.$index.'" value="'. $key .'">';
			$content[] = '</div>';
			$index++;
		}
		
		$content[] = '<div id="adddomainbutton" style="margin-bottom: 15px;"><a href="#" onclick="document.getElementById(\'blank-domain\').style.display = \'block\'; document.getElementById(\'adddomainbutton\').style.display = \'none\'; document.getElementById(\'domain_'.$index.'\').value=\''. $blankDomainValue .'\';">Manually add a new API key for domain</a></div>';
		$content[] = '<div class="domain-item" id="blank-domain" style="margin-bottom: 15px; display: none;">';
		$content[] = '<div style="width: 35em;"><label style="display: none;" for="domain_'. $index .'">Domain: </label><input style="width: 12em;" id="domain_'. $index .'" name="domain_'. $index .'" value="" onfocus="this.value=\'\';"/> <input type="image" '.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/garbage.gif','width="11" height="12"').' onclick="document.getElementById(\'key_'. $index .'\').value = \'\'; document.getElementById(\'blank-domain\').style.display =\'none\'; document.getElementById(\'adddomainbutton\').style.display = \'block\'; return false;" /></div>';
		$content[] = '<div><label style="display: none;" for="key_'. $index .'">'.$LANG->getLL('googleMapsApiKey').': </label></div>';
		$content[] = '<div><input style="width: 58em;" name="key_'. $index .'" value="" /></div>';
		$content[] = '</div>';

		$content[] = '<input type="submit" value="'.$LANG->getLL('submit').'"/>';
		$content[] = '</form>';

		return implode(chr(10), $content);
	}
	
	/*
	 * Looks up the API key in extConf within localconf.php
	 * @return		array		The Google Maps API keys.
	 */
	function getApiKeys() {
		
		require_once(t3lib_extMgm::extPath('wec_map').'class.tx_wecmap_backend.php');
		$apiKeys = tx_wecmap_backend::getExtConf('apiKey.google');
		
		return $apiKeys;
	}
	
	/*
	 * Saves the API key to extConf in localconf.php.
	 * @param		string		The new Google Maps API Key.
	 * @return		none
	 */	
	function saveApiKey($dataArray) {
		global $TYPO3_CONF_VARS;

		$extConf = unserialize($TYPO3_CONF_VARS['EXT']['extConf'][$this->extKey]);
		$extConf['apiKey.']['google'] = $dataArray;

		// Instance of install tool
		$instObj = t3lib_div::makeInstance('t3lib_install');
		$instObj->allowUpdateLocalConf = 1;
		$instObj->updateIdentity = $this->extKey;
		
		// Get lines from localconf file
		$lines = $instObj->writeToLocalconf_control();
		// t3lib_div::debug($lines, 'lines');
		$instObj->setValueInLocalconfFile($lines, '$TYPO3_CONF_VARS[\'EXT\'][\'extConf\'][\''.$this->extKey.'\']', serialize($extConf));
		$instObj->writeToLocalconf_control($lines);

		t3lib_extMgm::removeCacheFiles();
	}
	
	/**
	 * Returns an assoc array with domains as key and api key as value
	 *
	 * @return array
	 **/
	function getAllDomains() {
		
		$domainRecords = $this->getDomainRecords();

		// get domains entries from extconf
		$extconfDomains = $this->getApiKeys();

		// get domain from the current http request
		$requestDomain = $this->getRequestDomain();

		// Now combine all the records we got into one array with the domain as key and the api key as value
		return $this->combineAndSort($domainRecords, $extconfDomains, $requestDomain);
	}
	
	/**
	 * Returns an assoc array with domain record domains as keys and api key as value
	 *
	 * @return array
	 **/
	function getDomainRecords() {
		
		// get domain records
		$domainRecords = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('domainName', 'sys_domain', 'hidden=0');
		
		$newArray = array();
		foreach( $domainRecords as $key => $value ) {
			$newArray[$value['domainName']] = '';
		}
		
		return $newArray;
	}
	
	/**
	 * Returns the domain of the current http request
	 *
	 * @return array
	 **/
	function getRequestDomain() {
		// get domain from the current http request
		$requestDomain = t3lib_div::getIndpEnv('HTTP_HOST');

		return array($requestDomain => '');
	}
	
	/**
	 * combine all the arrays, making each key unique and preferring the one that has a value,
	 * then sort so that all empty values are last
	 *
	 * @return array
	 **/
	function combineAndSort($a1, $a2, $a3) {
		if(!is_array($a1)) $a1 = array();
		if(!is_array($a2)) $a2 = array();
		if(!is_array($a3)) $a3 = array();		

		// combine the first and the second
		$temp1 = array();
		foreach( $a1 as $key => $value ) {
			// if there is the same key in array2, check the values
			if(array_key_exists($key, $a2)) {
				
				// if a2 doesn't have a value, use a1's value
				if(empty($a2[$key])) {
					$temp1[$key] = $value;
				} else {
					$temp1[$key] = $a2[$key];
				}
			} else {
				$temp1[$key] = $value;
			}
		}

		$temp2 = array_merge($a2, $temp1);

		// combine the temp and the third
		$temp3 = array();
		foreach( $temp2 as $key => $value ) {
			// if there is the same key in array2, check the values
			if(array_key_exists($key, $a3)) {
				
				// if a3 doesn't have a value, use a1's value
				if(empty($a3[$key])) {
					$temp3[$key] = $value;
				} else {
					$temp3[$key] = $a3[$key];
				}
			} else {
				$temp3[$key] = $value;
			}
		}
				
		// merge the third into the second
		$temp4 = array_merge($a3, $temp3);
		
		// sort by value, reverse, and return
		asort($temp4);
		
		return array_reverse($temp4);
	}
	
	/**
	 * Submodule for the batch geocoder.
	 *
	 * @return		string		HTML output.
	 */
	function batchGeocode() {
		global $TCA, $LANG;
		$content = array();
		
	 	require_once(t3lib_extMgm::extPath('wec_map').'class.tx_wecmap_batchgeocode.php');
		$batchGeocodeClass = t3lib_div::makeInstanceClassname('tx_wecmap_batchgeocode');
		
		/* Set the geocoding limit to 1 so that we only get the count, rather than actually geocoding addresses */
		$batchGeocode = new $batchGeocodeClass(1);
		$batchGeocode->addAllTables();
		$batchGeocode->geocode();
		
		$processedAddresses = $batchGeocode->processedAddresses();
		$totalAddresses = $batchGeocode->recordCount();
		
		$content[] = '<h3>'.$LANG->getLL('batchGeocode').'</h3>';
		$content[] = '<p>'.$LANG->getLL('batchInstructions').'</p>';
		
		$content[] = '<p style="margin-top:1em;">'.$LANG->getLL('batchTables').'</p>';
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
														
							updater = new Ajax.PeriodicalUpdater(\'status\', \'tx_wecmap_batchgeocode_ai.php\', { method: \'get\', frequency: 5, decay: 10 });
						}
						</script>';
		
		require_once(t3lib_extMgm::extPath('wec_map').'mod1/class.tx_wecmap_batchgeocode_util.php');					
		$content[] = tx_wecmap_batchgeocode_util::getStatusBar($processedAddresses, $totalAddresses, false);		
		$content[] = '<input id="startGeocoding" type="submit" value="'.$LANG->getLL('startGeocoding').'" onclick="startGeocode(); return false;"/>';
		
		return implode(chr(10), $content);
	}
	
	/**
	 * Displays the pagination
	 *
	 * @return String
	 **/
	function makePagination($page, $count, $itemsPerPage) {
		$pages = ceil($count/$itemsPerPage);
		$content = array();
		
		if($page !== 1) {
			$content[] = '<a href="?page='. ($page-1) .'">Previous</a>';	
		} else {
			$content[] = '<span style="color: grey;">Previous</span>';	
		} 
		
		for ( $i=0; $i < $pages; $i++ ) { 
			if($page == ($i+1)) {
				$content[] = '<span style="color: grey;">'.($i+1).'</span>';
			} else {
				$content[] = '<a href="?page='. ($i+1) .'">'. ($i+1) .'</a>';				
			}
		}
		
		if($page !== $pages) {
			$content[] = '<a href="?page='. ($page+1) .'">Next</a>';	
		} else {
			$content[] = '<span style="color: grey;">Next</span>';	
		}
		
		return implode(' ', $content);
		
	}
	
	/**
	 * Get record limits for SQL query
	 *
	 * @return String
	 **/
	function getPageLimit($page, $itemsPerPage) {
		if($page == 1) {
			$start = 0;
			$end = $itemsPerPage;
		} else {
			$start = ($page-1)*$itemsPerPage;
			$end = $page*$itemsPerPage;
		}
		
		return $start.','.$end;
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