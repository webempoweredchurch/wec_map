<?php
// include all the necessary TYPO3 files
define('TYPO3_MOD_PATH', '../typo3conf/ext/wec_map/mod1/');
$BACK_PATH='../../../../typo3/';
require($BACK_PATH. 'init.php');
require('../class.tx_wecmap_cache.php');
require('class.tx_wecmap_recordhandler.php');
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