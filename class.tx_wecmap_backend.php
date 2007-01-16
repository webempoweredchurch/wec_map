<?php
/***************************************************************
* Copyright notice
*
* (c) 2005 Foundation for Evangelism
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
 * Plugin 'Map' for the 'wec_map' extension.
 *
 * @author	Web Empowered Church Team <map@webempoweredchurch.org>
 */

define('PATH_tslib', t3lib_extMgm::extPath('cms').'tslib/');
require_once(PATH_t3lib.'class.t3lib_tstemplate.php');
require_once(PATH_t3lib.'class.t3lib_page.php');
require_once(PATH_t3lib.'class.t3lib_timetrack.php');
require_once(PATH_t3lib.'class.t3lib_userauth.php');
require_once(PATH_tslib.'class.tslib_feuserauth.php');
require_once(PATH_tslib.'class.tslib_fe.php');
require_once(PATH_tslib.'class.tslib_content.php');

require_once('class.tx_wecmap_map.php');
require_once('class.tx_wecmap_cache.php');
require_once('map_service/google/class.tx_wecmap_map_google.php');

/**
 * Main class for the wec_map extension.  This class sits between the various 
 * frontend plugins and address lookup service to render map data.
 * 
 * @author Web Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class tx_wecmap_backend {
	
	function checkGeocodeStatus($PA, $fobj) {
		
		$row = $PA['row'];
		
		return tx_wecmap_backend::drawGeocodeStatus($row);
				
	}
	
	function checkGeocodeStatusFF($PA, $fobj) {
		$row = $PA['row']['pi_flexform'];
		$row = t3lib_div::xml2array($row);
		$row = $row['data']['default']['lDEF'];
		
		$data = array();
		$data['street'] = $row['street']['vDEF'];
		$data['city'] = $row['city']['vDEF'];
		$data['zip'] = $row['zip']['vDEF'];
		$data['state'] = $row['state']['vDEF'];
		$data['country'] = $row['country']['vDEF'];
		
		return tx_wecmap_backend::drawGeocodeStatus($data);
	}
	
	function drawGeocodeStatus($address) {
		$row = $address;

		$newlat = t3lib_div::_GP('lat');
		$newlong = t3lib_div::_GP('long');
		
		$origlat = t3lib_div::_GP('original_lat');
		$origlong = t3lib_div::_GP('original_long');
		
		if (empty($newlat) && empty($newlong)) {
			tx_wecmap_cache::delete($row['street'], $row['city'], $row['state'], $row['zip'], $row['country']);
		}

		if((($newlat != $origlat) or ($newlong != $origlong)) and (!empty($newlat) && !empty($newlong))) {
			tx_wecmap_cache::insert($row['street'], $row['city'], $row['state'], $row['zip'], $row['country'], $newlat, $newlong);
		}

		$latlong = tx_wecmap_cache::lookup($row['street'], $row['city'], $row['state'], $row['zip'], $row['country']);
		$status = tx_wecmap_cache::status($row['street'], $row['city'], $row['state'], $row['zip'], $row['country']);
		
		
		switch($status) {
			case -1:
				$status = "Geocoding failed.";
				break;
			case 0:
				$status = "Geocoding has not been performed for this address.";
				break;
			case 1:
				$status = "Geocoding successful!";
				break;
		}
		
		$form = '<label for="lat">Latitude</label> <input name="lat" value="'.$latlong['lat'].'" />
				 <label for="tx_wecmap[long]">Longitude</label>  <input name="long" value="'.$latlong['long'].'" />
				 <input type="hidden" name="original_lat" value="'.$latlong['lat'].'" />
				 <input type="hidden" name="original_long" value="'.$latlong['long'].'" />';
		
		return '<p>'.$status.'</p><p>'.$form.'</p>';
	}
	
	function drawMap($PA, $fobj) {
		$row = $PA['row'];
		
		$width = "400";
		$height = "400";
		$apiKey = "ABQIAAAApTKWZGXBnodwNIHa961YyxSfIPKRHQxvlXxuimPYzBQZi0LLbBSre1ZDkwf9rmUuOtM3M0THLTyvsQ";
		
		$className=t3lib_div::makeInstanceClassName("tx_wecmap_map_google");
		$map = new $className($apiKey, $width, $height);
		
		$map->addMarkerByAddress($row['street'], $row['city'], $row['state'], $row['zip'], $row['country']);
		//$content = $map->drawMap();
		
		
		return $content;
	}
	
}