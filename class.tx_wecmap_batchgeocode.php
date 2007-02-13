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

require_once(t3lib_extMgm::extPath('wec_map').'class.tx_wecmap_cache.php');

/**
 * Performs autmated geocoding for any address information. 
 * 
 * @author Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class tx_wecmap_batchgeocode {
	
	var $tables;
	var $geocodedAddresses;
	var $geocodeLimit;
	var $processedAddresses;
	
	
	/**
	 * Default constructor.
	 *
	 * @return		none
	 */
	function tx_wecmap_batchgeocode($limit=10) {
		$this->tables = array();	
		$this->geocodedAddresses = 0;
		$this->processedAddresses = 0;
		$this->geocodeLimit = $limit;
	}
	
	/**
	 * Adds a specific tables to the list of tables that should be geocoded.
	 *
	 * @param		string		The name of the table.
	 * @return		none
	 */
	function addTable($table) {
		$this->tables[] = $table;
	}
	
	
	/**
	 * Traverses the TCA and adds all mappable tables to the list of tables that
	 * should be geocoded.
	 *
	 * @return		none
	 */
	function addAllTables() {
		global $TCA;
		
		foreach($TCA as $tableName => $tableContents) {
			if($tableContents['ctrl']['EXT']['wec_map']['isMappable']) {
				$this->tables[] = $tableName;
			}
		}
	}
	
	/**
	 * Main function to initiate geocoding of all address-related tables.
	 *
	 * @return		none
	 */
	function geocode() {
		foreach($this->tables as $table) {		
			if($this->stopGeocoding()) {
				return;
			} else {			
				$this->geocodeTable($table);
			}
		}
	}
	
	/**
	 * Performs geocoding on an individual table.
	 *
	 * @param		string		Name of the table.
	 * @return		none
	 */
	function geocodeTable($table) {
		global $TCA, $TYPO3_DB;
		
		$addressFields = $TCA[$table]['ctrl']['EXT']['wec_map']['addressFields'];
		
		$result = $TYPO3_DB->exec_SELECTquery('*', $table, "");
		while($row = $TYPO3_DB->sql_fetch_assoc($result)) {
			
			if($this->stopGeocoding()) {
				return;
			} else {
				$this->geocodeRecord($row, $addressFields);
			}
		}		
	}
	
	
	/**
	 * Performs geocoding on an individual row.
	 *
	 * @param		array		The associative array of the record to be geocoded.
	 * @param		array		The array mapping address elements to individual fields in the record.
	 * @return		none
	 */
	function geocodeRecord($row, $addressFields) {
		$street = $row[$addressFields['street']];
		$city = $row[$addressFields['city']];
		$state = $row[$addressFields['state']];
		$zip = $row[$addressFields['zip']];
		$country = $row[$addressFields['country']];
		
		// increment total count
		$this->processedAddresses++;
		
		tx_wecmap_cache::lookupWithCallback($street, $city, $state, $zip, $country, '', false, $this);		
	}
	
	/**
	 * Callback function for tx_wecmap_cache::lookup().  Called when a lookup
	 * is not cached and must use external geocoding services. Increments an
	 * internal counter of how many external lookups we've made.
	 *
	 * @return		none
	 */
	function callback_lookupThroughGeocodeService() {
		$this->geocodedAddresses++;
	}
	
	
	/**
	 * Utility function to determine whether batch geocoding should be stopped.
	 *
	 * @return		boolean		True/false whethr batch geocoding should be stopped.
	 */
	function stopGeocoding() {		
		if($this->geocodedAddresses >= $this->geocodeLimit) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Getter function for the total number of addresses processed.
	 * 
	 * @return		The total number of addresses processed.  This includes both
	 *				cached and non-cached.
	 */
	function processedAddresses() {
		return $this->processedAddresses;
	}
	
	
	/**
	 * Getter function for the total number of addresses geocoded.
	 *
	 * @return		The total number of addresses geocoded by external services.
	 *				This does not include cached addresses.
	 */
	function geocodedAddresses() {
		return $this->geocodedAddresses;
	}
	
	/**
	 * Cound of all records containing address-related data.
	 *
	 * @return		integer		The count of all records with addresses.
	 */
	function recordCount() {
		global $TYPO3_DB;
		
		$recordCount = 0;
		
		foreach($this->tables as $table) {		
			$result = $TYPO3_DB->exec_SELECTquery('COUNT(*)', $table, "");
			$row = $TYPO3_DB->sql_fetch_assoc($result);
			$recordCount += $row['COUNT(*)'];
		}
		
		return $recordCount;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_batchgeocode.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_batchgeocode.php']);
}

?>