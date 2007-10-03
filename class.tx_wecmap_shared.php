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
 * General purpose class for the WEC Map extension.  This class
 * provides shared methods used by other classes
 *
 * @author Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class tx_wecmap_shared {

	function makeDescription($row) {
		$local_cObj = t3lib_div::makeInstance('tslib_cObj'); // Local cObj.
		$local_cObj->start($row, 'fe_users' );
		$output = $local_cObj->cObjGetSingle( $this->conf['marker.']['description'], $this->conf['marker.']['description.'] );
		return $output;
	}

	function makeAddress($row) {
		$local_cObj = t3lib_div::makeInstance('tslib_cObj'); // Local cObj.
		$local_cObj->start($row, 'fe_users' );
		$output = $local_cObj->cObjGetSingle( $this->conf['marker.']['address'], $this->conf['marker.']['address.'] );
		return $output;
	}

	function makeTitle($row) {
		$local_cObj = t3lib_div::makeInstance('tslib_cObj'); // Local cObj.
		$local_cObj->start($row, 'fe_users' );
		$output = $local_cObj->cObjGetSingle( $this->conf['marker.']['title'], $this->conf['marker.']['title.'] );
		return $output;
	}

	function makeSidebarLink($link) {
		$local_cObj = t3lib_div::makeInstance('tslib_cObj'); // Local cObj.
		// $local_cObj->start($row, 'fe_users' );
		$output = $local_cObj->cObjGetSingle($this->conf['sidebar'], $this->conf['sidebar.'] );
		return $output;
	}
	
	function wrapAddressString($address) {
		$local_cObj = t3lib_div::makeInstance('tslib_cObj'); // Local cObj.
		$local_cObj->start($row, 'fe_users' );
		$output = $local_cObj->stdWrap($address, $this->conf['marker.']['address.'] );
		return $output;
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
}
?>