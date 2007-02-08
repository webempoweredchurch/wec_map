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

// include all the necessary TYPO3 files
define('TYPO3_MOD_PATH', '../typo3conf/ext/wec_map/mod1/');
$BACK_PATH='../../../../typo3/';
require($BACK_PATH. 'init.php');

// create an instance of our batch geocode class
require_once(t3lib_extMgm::extPath('wec_map').'class.tx_wecmap_batchgeocode.php');
$batchGeocode = t3lib_div::makeInstance('tx_wecmap_batchgeocode');

// add all tables to check which ones need geocoding and do it
$batchGeocode->addAllTables();
$batchGeocode->geocode();

// some testing output for jeff to work with
// echo 'Geocoded addresses: ' .$batchGeocode->geocodedAddresses() . '<br />';
// echo 'Processed addresses: ' .$batchGeocode->processedAddresses();

$processedAddresses = $batchGeocode->processedAddresses();
$totalAddresses = $batchGeocode->recordCount();

$progressBarWidth = round($processedAddresses / $totalAddresses * 100);

$content = '<div id="bar" style="width:300px; height:20px; border:1px solid black">
				<div id="progress" style="width:'.$progressBarWidth.'%; height:20px; background-color:red"></div>
			</div>
			<p>Processed '.$processedAddresses.' records of '.$totalAddresses.'</p>';

echo $content;
?>