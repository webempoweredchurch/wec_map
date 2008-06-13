<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2008 Christian Technology Ministries International Inc.
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

/**
 * General purpose class for the WEC Map extension.  This class
 * provides shared methods used by other classes
 *
 * @author Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class tx_wecmap_shared {

	function render($data, $conf, $table = '') {
		if (!defined('PATH_tslib')) define('PATH_tslib', t3lib_extMgm::extPath('cms').'tslib/');
		require_once(PATH_tslib.'class.tslib_content.php');
		$local_cObj = t3lib_div::makeInstance('tslib_cObj'); // Local cObj.
		$local_cObj->start($data, $table );
		$output = tx_wecmap_shared::cObjGet($conf, $local_cObj);

		return $output;
	}

	function cObjGet($setup, &$cObj, $addKey='')	{
		if (is_array($setup))	{

			$sKeyArray = $setup;
			$content ='';

			foreach($sKeyArray as $theKey => $theValue)	{

				if (!strstr($theKey,'.'))	{
					$conf=$setup[$theKey.'.'];
					$content.=$cObj->cObjGetSingle($theValue,$conf,$addKey.$theKey);	// Get the contentObject
				}
			}
			return $content;
		}
	}
	
	
	function listQueryFromCSV($field, $values, $table, $mode = 'AND') {
		$where = ' AND (';
		$csv = t3lib_div::trimExplode(',', $values);
		for ( $i=0; $i < count($csv); $i++ ) {
			if($i >= 1) {
				$where .= ' '. $mode .' ';
			}
			$where .= $GLOBALS['TYPO3_DB']->listQuery($field, $csv[$i], $table);
		}

		return $where.')';
	}
	
	function getAddressField($table, $field) {
		t3lib_div::loadTCA($table);
 		return $GLOBALS['TCA'][$table]['ctrl']['EXT']['wec_map']['addressFields'][$field];
	}
}
?>