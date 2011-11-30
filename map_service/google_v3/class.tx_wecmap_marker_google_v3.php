<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2009 Christian Technology Ministries International Inc.
* All rights reserved
* (c) 2011 Jan Bartels Google API V3
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

require_once(t3lib_extMgm::extPath('wec_map').'class.tx_wecmap_marker.php');
require_once(t3lib_extMgm::extPath('wec_map').'class.tx_wecmap_backend.php');
require_once(t3lib_extMgm::extPath('wec_map').'class.tx_wecmap_shared.php');

/**
 * Marker implementation for the Google Maps mapping service.
 *
 * @author Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class tx_wecmap_marker_google_v3 extends tx_wecmap_marker {
	var $index;

	var $latitude;
	var $longitude;

	var $title;
	var $description;
	var $color;
	var $strokeColor;
	var $prefillAddress;
	var $tabLabels;
	var $iconID;

	/**
	 * Constructor for the Google Maps marker class.
	 *
	 * @access	public
	 * @param	integer		Index within the overall array of markers.
	 * @param	float		Latitude of the marker location.
	 * @param	float		Longitude of the marker location.
	 * @param	string		Title of the marker.
	 * @param	string		Description of the marker.
	 * @param 	boolean		Sets whether the directions address should be prefilled with logged in user's address
	 * @param	array 		Labels used on tabs. Optional.
	 * @param	string		Unused for Google Maps.
	 * @param	string		Unused for Google Maps.
	 * @return	none
	 */
	function tx_wecmap_marker_google_v3($index, $latitude, $longitude, $title, $description, $prefillAddress = false, $tabLabels=null, $color='0xFF0000', $strokeColor='0xFFFFFF', $iconID='') {

		global $LANG;
		if(!is_object($LANG)) {
			require_once(t3lib_extMgm::extPath('lang').'lang.php');
			$LANG = t3lib_div::makeInstance('language');

			if(TYPO3_MODE == 'BE') {
				$LANG->init($BE_USER->uc['lang']);
			} else {
				$LANG->init($GLOBALS['TSFE']->config['config']['language']);
			}
		}
		$LANG->includeLLFile('EXT:wec_map/locallang_db.xml');

		$this->index = $index;
		$this->tabLabels = array($LANG->getLL('info'));
		if(is_array($tabLabels)) {
			$this->tabLabels = array_merge($this->tabLabels, $tabLabels);
		}

		$this->prefillAddress = $prefillAddress;

		$this->title = array();
		$this->description = array();

		if(is_array($title)) {
			foreach( $title as $value ) {
				$this->title[] = addslashes($value);
			}
		} else {
			$this->title[] = addslashes($title);
		}

		if(is_array($description)) {
			foreach($description as $value ) {
				$this->description[] = $this->filterNL2BR(addslashes($value));
			}
		} else {
			$this->description[] = $this->filterNL2BR(addslashes($description));
		}

		$this->color = $color;
		$this->strokeColor = $strokeColor;

		$this->latitude = $latitude;
		$this->longitude = $longitude;

		$this->iconID = $iconID;
	}


	/**
	 * Creates the Javascript to add a marker to the page.
	 *
	 * @access public
	 * @return	string	The Javascript to add a marker to the page.
	 */
	function writeJS() {
		$markerContent = array();
		foreach ($this->tabLabels as $index => $label) {
			$markerContent[] = $this->title[$index] . $this->description[$index];
		}

		if ($this->directions) {
			$markerContent[0] .= '<br /><div id="'.$this->mapName.'_dirmenu_'.$this->groupId.'_'. $this->index .'" class="dirmenu" style="white-space: nowrap;">'. $GLOBALS['LANG']->getLL('directions') .': <a href="#" onclick="return WecMap.openDirectionsToHere(\\\'' . $this->mapName . '\\\', ' . $this->groupId . ', ' . $this->index . ');">' . $GLOBALS['LANG']->getLL('toHereFrom') . '</a> - <a href="#" onclick="return WecMap.openDirectionsFromHere(\\\'' . $this->mapName . '\\\', ' . $this->groupId . ', ' . $this->index . ');">'. $GLOBALS['LANG']->getLL('fromHereTo') .'</a></div>';
		}

		return '
WecMap.addBubble("' . $this->mapName . '", ' . $this->groupId . ', ' . $this->index . ', [\''  . implode('\',\'', $this->tabLabels) . '\'], [\'' . implode('\',\'', $markerContent) . '\']);';
	}

	/**
	 * Wrapper method that makes sure directions are properly displayed
	 *
	 * @return string 	the javascript to add the marker
	 **/
	function writeJSwithDirections() {
		$this->directions = true;
		return $this->writeJS();
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 **/
	function writeCreateMarkerJS() {
		if (empty($this->title[0]) && $this->directions) {
			$this->title[0] = 'Address';
		}
		return 'WecMap.addMarker("'. $this->mapName . '", "' . $this->index . '", [' . $this->latitude . ',' . $this->longitude . '], "' . $this->iconID . '", \''. htmlspecialchars(strip_tags($this->title[0])) .'\', '.$this->groupId.', \''.$this->getUserAddress().'\');';
	}

	/**
	 * adds a new tab to the marker
	 *
	 * @return void
	 **/
	function addTab($tabLabel, $title, $description) {
		if(!is_array($this->title)) {
			$temp = $this->title;
			$this->title = array($temp);
		}

		if(!is_array($this->description)) {
			$temp = $this->description;
			$this->description = array($temp);
		}

		if(!is_array($this->tabLabels)) {
			$this->tabLabels = array($GLOBALS['LANG']->getLL('info'));
		}

		$this->tabLabels[] = addslashes($tabLabel);
		$this->title[] = addslashes($title);
		$this->description[] = addslashes($description);
		// TODO: devlog start
		if(TYPO3_DLOG) {
			t3lib_div::devLog($this->mapName.': manually adding tab to marker '.$this->index.' with title '. $title, 'wec_map_api');
		}
		// devlog end
	}

	/**
	 * Gets the address of the user who is currently logged in
	 *
	 * @return string
	 **/
	function getUserAddress() {
		if($this->prefillAddress) {

			if(TYPO3_MODE == 'FE') {
				$feuser_id = $GLOBALS['TSFE']->fe_user->user['uid'];

				if(!empty($feuser_id)) {
					$table = 'fe_users';
					$streetField  = tx_wecmap_shared::getAddressField($table, 'street');
					$cityField    = tx_wecmap_shared::getAddressField($table, 'city');
					$stateField   = tx_wecmap_shared::getAddressField($table, 'state');
					$zipField     = tx_wecmap_shared::getAddressField($table, 'zip');
					$countryField = tx_wecmap_shared::getAddressField($table, 'country');


					$select = $streetField.', '.$cityField.', '.$stateField.', '.$zipField.', '.$country;
					$selectArray = t3lib_div::trimExplode(',', $select, true);
					$select = implode(',', $selectArray);

					$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($select, 'fe_users', '`uid`='.$feuser_id);
					return $rows[0][$streetField].', '.$rows[0][$cityField].', '.$rows[0][$stateField].' '.$rows[0][$zipField].', '.$rows[0][$countryField];
				}
			} else {

			}
		}
		return '';
	}

	/**
	 * Returns the javascript function call to center on this marker
	 *
	 * @return String
	 **/
	function getClickJS() {
		// TODO: devlog start
		if(TYPO3_DLOG) {
			t3lib_div::devLog($this->mapName.': adding marker '.$this->index.'('.strip_tags($this->title[0]).strip_tags($this->description[0]).') to sidebar', 'wec_map_api');
		}
		// devlog end
		return 'WecMap.jumpTo(\'' . $this->mapName . '\', ' . $this->groupId . ', ' . $this->index . ', ' . $this->calculateClickZoom() . ');';
	}

	function getOpenInfoWindowJS() {
		return 'WecMap.openInfoWindow("' . $this->mapName . '", ' . $this->groupId . ', ' . $this->index . ');';
	}

	/**
	 * calculates the optimal zoom level for the click
	 * we want to keep the zoom level around $zoom, but will
	 * choose the max if the marker is only visible under $zoom,
	 * or the min if it's only shown over $zoom.
	 * @return integer
	 **/
	function calculateClickZoom() {
		$zoom = 14;
		if ($zoom < $this->minzoom) {
			$zoom = $this->minzoom;
		} else if ($zoom > $this->maxzoom) {
			$zoom = $this->maxzoom;
		}
		return $zoom;
	}

	/**
	 * Converts newlines to <br/> tags.
	 *
	 * @access	private
	 * @param	string		The input string to filtered.
	 * @return	string		The converted string.
	 */
	function filterNL2BR($input) {
		$order  = array("\r\n", "\n", "\r");
		$replace = '<br />';
		return str_replace($order, $replace, $input);
	}

	/**
	 * strip newlines
	 *
	 * @access	private
	 * @param	string		The input string to filtered.
	 * @return	string		The converted string.
	 */
	function stripNL($input) {
		$order  = array("\r\n", "\n", "\r");
		$replace = '<br />';
		return str_replace($order, $replace, $input);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/map_service/google_v3/class.tx_wecmap_marker_google_v3.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/map_service/google_v3/class.tx_wecmap_marker_google_v3.php']);
}


?>