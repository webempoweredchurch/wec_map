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

require_once(t3lib_extMgm::extPath('wec_map').'class.tx_wecmap_map.php');
require_once(t3lib_extMgm::extPath('wec_map').'class.tx_wecmap_cache.php');
require_once(t3lib_extMgm::extPath('wec_map').'map_service/google/class.tx_wecmap_map_google.php');

/**
 * General purpose backend class for the WEC Map extension.  This class
 * provides user functions for displaying geocode status and maps within
 * TCEForms.
 * 
 * @author Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class tx_wecmap_backend {
	
	function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, &$reference) {
		global $TCA;
		$isMappable = $TCA[$table]['ctrl']['EXT']['wec_map']['isMappable'];
	
		if($isMappable) {
			/* Get the names of the fields from the TCA */
			$streetField  = tx_wecmap_backend::getFieldNameFromTable('street', $table);
			$cityField    = tx_wecmap_backend::getFieldNameFromTable('city', $table);
			$stateField   = tx_wecmap_backend::getFieldNameFromTable('state', $table);
			$zipField     = tx_wecmap_backend::getFieldNameFromTable('zip', $table);
			$countryField = tx_wecmap_backend::getFieldNameFromTable('country', $table);
			

			/* Get the row that we're saving */
			$row = t3lib_befunc::getRecord($table, $id);
			
			/* @todo	Eliminate double save */
			tx_wecmap_backend::drawGeocodeStatus($row[$streetField], $row[$cityField], $row[$stateField], $row[$zipField], $row[$countryField]);
		}
		
	}
	
	/**
	 * Checks the geocoding status for the current record.  This function is
	 * mainly responsible for taking backend record data and handing it to
	 * drawGeocodeStatus().
	 *
	 * @param	array	Array with information about the current field.
	 * @param	object	Parent object.  Instance of t3lib_tceforms.
	 * @return	string	HTML output of current geocoding status and editing form.
	 */
	function checkGeocodeStatus($PA, &$fobj) {
		// if geocoding status is disabled, return
		if(!tx_wecmap_backend::getExtConf('geocodingStatus')) return;

		$street = tx_wecmap_backend::getFieldValue('street', $PA);
        $city = tx_wecmap_backend::getFieldValue('city', $PA);
        $state = tx_wecmap_backend::getFieldValue('state', $PA);
        $zip = tx_wecmap_backend::getFieldValue('zip', $PA);
        $country = tx_wecmap_backend::getFieldValue('country', $PA);	
		
		return tx_wecmap_backend::drawGeocodeStatus($street, $city, $state, $zip, $country);
				
	}
	
	/**
	 * Checks the goecoding status for the current FlexForm.  This function is
	 * mainly responsible for taking FlexForm data and handing it to 
	 * drawGeocodeStatus().
	 * 
	 * @param	array	Array with information about the current FlexForm.
	 * @param	object	Parent object.  Instance of t3lib_tceforms.
	 * @return	string	HTML output of current geocoding status and editing form.
	 * @todo	Does our method of digging into FlexForms mess up localization?
	 */
	function checkGeocodeStatusFF($PA, &$fobj) {
		
		// if geocoding status is disabled, return
		if(!tx_wecmap_backend::getExtConf('geocodingStatus')) return;
		
		$street  = tx_wecmap_backend::getFieldValueFromFF('street', $PA);
        $city    = tx_wecmap_backend::getFieldValueFromFF('city', $PA);
        $state   = tx_wecmap_backend::getFieldValueFromFF('state', $PA);
        $zip     = tx_wecmap_backend::getFieldValueFromFF('zip', $PA);
        $country = tx_wecmap_backend::getFieldValueFromFF('country', $PA);

		
		return tx_wecmap_backend::drawGeocodeStatus($street, $city, $state, $zip, $country);
	}
	
	/**
	 * Checks the geocoding status of the address and displays an editing form.
	 *
	 * @param	string	Street portion of the address.
	 * @param	string	City portion of the address.
	 * @param	string	State portion of the address.
	 * @param	string	ZIP code portion of the address.
	 * @param	string	Country portion of the address.
	 * @return	string	HTML output of current geocoding status and editing form.
	 */
	function drawGeocodeStatus($street, $city, $state, $zip, $country) {
		global $LANG;
		$LANG->includeLLFile('EXT:wec_map/locallang_db.xml');
		
		// if there is no info about the user, return different status
		if(!$city) {
			return $LANG->getLL('geocodeNoAddress');
		}
		
		/* Grab the lat and long that were posted */
		$newlat = t3lib_div::_GP('lat');
		$newlong = t3lib_div::_GP('long');
		
		$origlat = t3lib_div::_GP('original_lat');
		$origlong = t3lib_div::_GP('original_long');
				
		/* If the new lat/long are empty, delete our cached entry */
		if (empty($newlat) && empty($newlong) && !empty($origlat) && !empty($origlong)) {
			tx_wecmap_cache::delete($street, $city, $state, $zip, $country);
		}

		/* If the lat/long changed, then insert a new entry into the cache or update it. */
		if((($newlat != $origlat) or ($newlong != $origlong)) and (!empty($newlat) && !empty($newlong))) {
			tx_wecmap_cache::insert($street, $city, $state, $zip, $country, $newlat, $newlong);
		}
		
		/* Get the lat/long and status from the geocoder */
		$latlong = tx_wecmap_cache::lookup($street, $city, $state, $zip, $country);
		$status = tx_wecmap_cache::status($street, $city, $state, $zip, $country);
		
		switch($status) {
			case -1:
				$status = $LANG->getLL('geocodeFailed');
				break;
			case 0:
				$status = $LANG->getLL('geocodeNotPerformed');
				break;
			case 1:
				$status = $LANG->getLL('geocodeSuccessful');
				break;
		}
		
		$form = '<label for="lat">'.$LANG->getLL('latitude').'</label> <input name="lat" value="'.$latlong['lat'].'" />
				 <label for="tx_wecmap[long]">'.$LANG->getLL('longitude').'</label>  <input name="long" value="'.$latlong['long'].'" />
				 <input type="hidden" name="original_lat" value="'.$latlong['lat'].'" />
				 <input type="hidden" name="original_long" value="'.$latlong['long'].'" />';
		
		return '<p>'.$status.'</p><p>'.$form.'</p>';
	}
	
	/**
	 * Draws a backend map.
	 * @param		array		Array with information about the current field.
	 * @param		object		Parent object.  Instance of t3lib_tceforms.
	 * @return		string		HTML to display the map within a backend record.
	 */
	function drawMap($PA, $fobj) {
		$width = '400';
		$height = '400';
		
		$street = tx_wecmap_backend::getFieldValue('street', $PA);
        $city = tx_wecmap_backend::getFieldValue('city', $PA);
        $state = tx_wecmap_backend::getFieldValue('state', $PA);
        $zip = tx_wecmap_backend::getFieldValue('zip', $PA);
        $country = tx_wecmap_backend::getFieldValue('country', $PA);
		$description = $street.'<br />'.$city.', '.$state.' '.$zip.'<br />'.$country;
		
		$className=t3lib_div::makeInstanceClassName('tx_wecmap_map_google');
		$map = new $className($apiKey, $width, $height);
		$map->addMarkerByAddress($street, $city, $state, $zip, $country, '<h1>Address</h1>', $description);

		// add some default controls to the map
		$map->addControl('largeMap');	
		$map->addControl('scale');
		$map->addControl('mapType');
		$map->enableDirections(true);
		
		$content = $map->drawMap();
		
		return $content;
	}
	
	/**
	 * Checks the TCA for address mapping rules and returns the address value.  
	 * If a mapping rule is defined, this tells us what field contains address 
	 * related information.  If no rules are defined, we pick default fields 
	 * to use.
	 *
	 * @param	string	The portion of the address we're trying to map.
	 * @param	array	Array of field related data.
	 * @return	string	The specified portion of the address.
	 * @todo			Refactor this to use getFieldNameForTable().
	 */
	function getFieldValue($key, $PA) {
		global $TCA;
		$table = $PA['table'];
		$ctrlAddressFields = $TCA[$table]['ctrl']['EXT']['wec_map']['addressFields'];
		
        $row = $PA['row'];
        $addressFields = $PA['fieldConf']['config']['params']['addressFields'];
		
		/* If the address mapping array has a mapping for this address, use it */
        if(isset($addressFields[$key])) {
            $fieldName = $addressFields[$key];
        } else {
			/* If the ctrl section of the TCA has a name, use it */
			if(isset($ctrlAddressFields[$key])) {
				$fieldName = $ctrlAddressFields[$key];
			} else {	
				/* Otherwise, use the default name */
            	$fieldName = $key;
			}
        }
		
		/* If the source data has a value for the address field, grab it */
        if (isset($row[$fieldName])) {
            $value = $row[$fieldName];
        } else {
			/* Otherwise, use an empty string */
            $value = '';
        }

        return $value;
    }

	/**
	 * Checks the FlexForm for address mapping rules and returns the address value.  
	 * If a mapping rule is defined, this tells us what field contains address 
	 * related information.  If no rules are defined, we pick default fields 
	 * to use.
	 *
	 * @param	string	The portion of the address we're trying to map.
	 * @param	array	Array of field related data.
	 * @return	string	The specified portion of the address.
	 */
	function getFieldValueFromFF($key, $PA) {
		$addressFields = $PA['fieldConf']['config']['params']['addressFields'];
		
		$flexForm = t3lib_div::xml2array($PA['row']['pi_flexform']);		
		if(is_array($flexForm)) {
			$flexForm = $flexForm['data']['default']['lDEF'];
			
			/* If the address mapping array has a map for this address, use it */
			if(isset($addressFields[$key])) {
				$fieldName = $addressFields[$key];
			} else {
				$fieldName = $key;
			}
		
		
			/* If the source data has a value for the addres field, grab it */
			if (isset($flexForm[$fieldName]['vDEF'])) {
				$value = $flexForm[$fieldName]['vDEF'];
			} else {
				$value = '';
			}
		} else {
			$value = '';
		}
		
        return $value;
	}
	
	/**
	 * Checks the TCA for address mapping rules and returns the field name.  
	 * If a mapping rule is defined, this tells us what field contains address 
	 * related information.  If no rules are defined, we pick default fields 
	 * to use.
	 *
	 * @param	string	The portion of the address we're trying to map.
	 * @param	string	The name of the table that we're trying to map.
	 * @return	string	The specified portion of the address.
	 */
	function getFieldNameFromTable($key, $table) {
		global $TCA;
		$ctrlAddressFields = $TCA[$table]['ctrl']['EXT']['wec_map']['addressFields'];
		
		/* If the ctrl section of the TCA has a name, use it */
		if(isset($ctrlAddressFields[$key])) {
			$fieldName = $ctrlAddressFields[$key];
		} else {	
			/* Otherwise, use the default name */
           	$fieldName = $key;
		}

        return $fieldName;
    }
	
	/**
	 * Gets extConf from TYPO3_CONF_VARS and returns the specified key.
	 *
	 * @param	string	The key to look up in extConf.
	 * @return	mixed	The value of the specified key.
	 */
	function getExtConf($key) {
		/* Make an instance of the Typoscript parser */
		require_once(PATH_t3lib.'class.t3lib_tsparser.php');
		$tsParser = t3lib_div::makeInstance('t3lib_TSparser');
		
		/* Unserialize the TYPO3_CONF_VARS and extract the value using the parser */
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['wec_map']);
		$valueArray = $tsParser->getVal($key, $extConf);

		if (is_array($valueArray)) {
			$returnValue = $valueArray[0];
		} else {
			$returnValue = '';
		}
	
		return $returnValue;
	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_backend.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_backend.php']);
}

?>