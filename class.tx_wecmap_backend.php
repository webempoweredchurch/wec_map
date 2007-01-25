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
		
		// get key from configuration
		$conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['wec_map']);
		$BE = $conf['geocodingStatus'];
		
		// if geocoding status is disabled, return
		if(!$BE) return;
				
		$row = $PA['row'];
		
		return tx_wecmap_backend::drawGeocodeStatus($row);
				
	}
	
	function checkGeocodeStatusFF($PA, $fobj) {
		
		// get key from configuration
		$conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['wec_map']);
		$BE = $conf['geocodingStatus'];
		
		// if geocoding status is disabled, return
		if(!$BE) return;
		
		$row = $PA['row']['pi_flexform'];
		if(empty($row)) return tx_wecmap_backend::drawGeocodeStatus($data);
		
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

		// if there is no info about the user, return different status
		if(empty($row['city'])) {
			return 'Cannot determine latitude and longitude.  Please enter an address and save.';
		}
		
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

		$width = "400";
		$height = "400";
		
		$street = tx_wecmap_backend::getFieldValue('street', $PA);
        $city = tx_wecmap_backend::getFieldValue('city', $PA);
        $state = tx_wecmap_backend::getFieldValue('state', $PA);
        $zip = tx_wecmap_backend::getFieldValue('zip', $PA);
        $country = tx_wecmap_backend::getFieldValue('country', $PA);
		$description = $street."<br />".$city.", ".$state." ".$zip."<br />".$country;
		
		$className=t3lib_div::makeInstanceClassName("tx_wecmap_map_google");
		$map = new $className($apiKey, $width, $height);
		$map->addMarkerByAddress($street, $city, $state, $zip, $country, '', $description);

		// add some default controls to the map
		$map->addControl('largeMap');	
		$map->addControl('scale');
		$map->addControl('mapType');
		
		$content = $map->drawMap();
		
		return $content;
	}
	
	function getFieldValue($key, $PA) {
        $row = $PA['row'];
        $addressFields = $PA['fieldConf']['config']['params']['addressFields'];
		
        if(isset($addressFields[$key])) {
            $fieldName = $addressFields[$key];
        } else {
            $fieldName = $key;
        }

        if (isset($row[$fieldName])) {
            $value = $row[$fieldName];
        } else {
            $value = '';
        }

        return $value;
    }
	
}