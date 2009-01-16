<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2009 Christian Technology Ministries International Inc.
* All rights reserved
*
* This file is part of the Web-Empowered Church (WEC)
* (http://WebEmpoweredChurch.org) ministry of Christian Technology Ministries 
* International (http://CTMIinc.org). The WEC is developing TYPO3-based
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

require_once('class.tx_wecmap_backend.php');
require_once(PATH_t3lib.'class.t3lib_install.php');

/**
 * Domain <=> API Key manager class for the WEC Map extension.  This class
 * provides user functions for handling domains and API keys
 *
 * @author Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class tx_wecmap_domainmgr {

	var $extKey = 'wec_map';


	function getKey($domain = null) {

		// check to see if this is an update from the old config schema and convert to the new
		$isOld = $this->checkForOldConfig();

		// get key from configuration
		$keyConfig = tx_wecmap_backend::getExtConf('apiKey.google');

		// if we are using the old config, return the old key this time. It will be changed for next time.
		if($isOld) return $keyConfig;

		// get current domain
		if($domain == null)	$domain = t3lib_div::getIndpEnv('HTTP_HOST');

		// loop through all the domain->key pairs we have to find the right one
		$found = false;
		foreach( $keyConfig as $key => $value ) {
			if($domain == $key) {
				$found = true;
				return $value;
			}
		}

		// if we didn't get an exact match, check for partials and guess
		if(!$found) {
			foreach( $keyConfig as $key => $value ) {

				if(strpos($domain, $key) !== false) {
					$found = true;
					return $value;
				}
			}
		} else {
			return null;
		}
	}

	function checkForOldConfig() {
		global $TYPO3_CONF_VARS;

		$keyConfig = tx_wecmap_backend::getExtConf('apiKey.google');
		if(is_array($keyConfig)) return false;

		$key = $keyConfig;
		$domain = t3lib_div::getIndpEnv('HTTP_HOST');
		$this->saveApiKey(array($domain => $key));

		return true;
	}

	function processPost($post) {

		$allDomains = $this->getAllDomains();

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
			if(!empty($curKey) && !empty($curDomain)) $extconfArray[$curDomain] = $curKey;

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

		return array_reverse($returnArray);
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

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_domainmgr.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_domainmgr.php']);
}

?>