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

require_once('conf.php'); // Get back path and TYPO3 mod path
require_once($BACK_PATH. 'init.php');
require_once('../class.tx_wecmap_cache.php');
require_once('class.tx_wecmap_recordhandler.php');

$cmd = htmlspecialchars(t3lib_div::_GP('cmd'));
$uid  = htmlspecialchars(t3lib_div::_GP('record'));
$page = htmlspecialchars(t3lib_div::_GP('page'));
$itemsPerPage = htmlspecialchars(t3lib_div::_GP('itemsPerPage'));
$count = htmlspecialchars(t3lib_div::_GP('count'));
$latitude = htmlspecialchars(t3lib_div::_GP('latitude'));
$longitude = htmlspecialchars(t3lib_div::_GP('longitude'));

if($cmd == 'deleteAll') {
	tx_wecmap_cache::deleteAll();
} else if($cmd == 'deleteSingle') {
	tx_wecmap_cache::deleteByUID($uid);
} else if($cmd == 'updatePagination') {
	echo makePagination($page, $count, $itemsPerPage);
} else if($cmd == 'saveRecord') {
	tx_wecmap_cache::updateByUID($uid, $latitude, $longitude);
}

/**
 * Displays the pagination
 *
 * @return String
 **/
function makePagination($page, $count, $itemsPerPage) {
	$pages = ceil(($count-1)/$itemsPerPage);
	$content = array();
	$content[] = '<div id="pagination">';
	if($pages == 1) return null;

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

	$content[] = '</div>';
	return implode(' ', $content);

}
?>