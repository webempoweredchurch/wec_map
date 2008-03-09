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

require_once(t3lib_extMgm::extPath('wec_map').'class.tx_wecmap_marker.php');
require_once(t3lib_extMgm::extPath('wec_map').'class.tx_wecmap_backend.php');

/**
 * Marker implementation for the Google Maps mapping service.
 *
 * @author Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class tx_wecmap_marker_google extends tx_wecmap_marker {
	var $index;

	var $latitude;
	var $longitude;

	var $title;
	var $description;
	var $color;
	var $strokeColor;
	var $prefillAddress;
	var $hasTabs;
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
	function tx_wecmap_marker_google($index, $latitude, $longitude, $title, $description, $prefillAddress = false, $tabLabels=null, $color='0xFF0000', $strokeColor='0xFFFFFF', $iconID='') {

		global $LANG;
		if(!is_object($LANG)) {
			require_once(t3lib_extMgm::extPath('lang').'lang.php');
			$LANG = t3lib_div::makeInstance('language');
			$LANG->init($BE_USER->uc['lang']);
		}
		$LANG->includeLLFile('EXT:wec_map/locallang_db.xml');

		$this->index = $index;
		$this->tabLabels = $tabLabels;
		$this->prefillAddress = $prefillAddress;
		$this->hasTabs = false;

		if(is_array($title)) {
			$this->title = array();
			foreach( $title as $value ) {
				$this->title[] = addslashes($value);
			}
		} else {
			$this->title = addslashes($title);
		}

		if(is_array($description)) {
			$this->description = array();
			foreach($description as $value ) {
				$this->description[] = $this->filterNL2BR(addslashes($value));
			}
		} else {
			$this->description = $this->filterNL2BR(addslashes($description));
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
		$arrays = $this->buildTitleAndDescriptionArrays(false);
		$titleArray = $arrays[0];
		$textArray = $arrays[1];

		if(is_array($titleArray)) {
			$this->hasTabs = true;
			return 'createMarkerWithTabs(new GLatLng('.$this->latitude.','.$this->longitude.'), icon_'. $this->mapName . $this->iconID .', '. $titleArray .' ,'. $textArray .')';
		} else {
			$this->hasTabs = false;
			return 'createMarker(new GLatLng('.$this->latitude.','.$this->longitude.'), icon_'. $this->mapName . $this->iconID .', "'.$titleArray.$textArray.'")';
		}

	}

	/**
	 * Creates the Javascript to add a marker with directions to the page.
	 *
	 * @return string 	the javascript to add the marker
	 **/
	function writeJSwithDirections() {

		$arrays = $this->buildTitleAndDescriptionArrays(true);
		$titleArray = $arrays[0];
		$textArray = $arrays[1];

		$this->hasTabs = true;
		return 'createMarkerWithTabs(new GLatLng('.$this->latitude.','.$this->longitude.'), icon_'. $this->mapName . $this->iconID .', '. $titleArray .' ,'. $textArray .')';
	}

	/**
	 * Creates the html to be shown in the directions tab
	 *
	 * @return string	HTML for to-directions
	 **/
	function getDirectionsHTML() {
		global $LANG;

		if(is_array($this->title)) {
			$title = strip_tags($this->title[0]);
		} else {
			$title = strip_tags($this->title);
		}

		$html = '<h1>'. $LANG->getLL('location') .'</h1><div>'. $title .'</div>';
		$html .= '<h2>'. $LANG->getLL('getDirections') .'</h2><div>';
		$html .= $this->stripNL(
			sprintf('<form onsubmit="setDirections_'. $this->mapName .'(\\\'%3$s @ %1$f %2$f\\\', document.getElementById(\\\'tx-wecmap-directions-to-'. $this->mapName .'\\\').value, \\\''. $this->mapName .'\\\'); return false;" action="#" >
			<label for="tx-wecmap-directions-to-'. $this->mapName .'">'. $LANG->getLL('fromHereTo') .'</label><input type="text" name="daddr" value="%4$s" id="tx-wecmap-directions-to-'. $this->mapName .'" />
			<input type="submit" name="submit" value="Go" /></form>',
			$this->latitude,
			$this->longitude,
			$title,
			$this->getUserAddress()
			)
		);
		$html .= $this->stripNL(
			sprintf('<form action="#" onsubmit="setDirections_'. $this->mapName .'(document.getElementById(\\\'tx-wecmap-directions-from-'. $this->mapName .'\\\').value, \\\'%3$s @ %1$f %2$f\\\', \\\''. $this->mapName .'\\\'); return false;">
			<label for="tx-wecmap-directions-from-'. $this->mapName .'">'. $LANG->getLL('toHereFrom') .'</label><input type="text" name="saddr" value="%4$s" id="tx-wecmap-directions-from-'. $this->mapName .'" />
			<input type="submit" name="submit" value="Go" /></form>',
			$this->latitude,
			$this->longitude,
			$title,
			$this->getUserAddress()
			)
		);
		$html .= '</div>';
		return $html;
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
					$streetField = tx_wecmap_backend::getFieldNameFromTable('street', $table);
					$cityField = tx_wecmap_backend::getFieldNameFromTable('city', $table);
					$stateField = tx_wecmap_backend::getFieldNameFromTable('state', $table);
					$zipField = tx_wecmap_backend::getFieldNameFromTable('zip', $table);
					$countryField = tx_wecmap_backend::getFieldNameFromTable('country', $table);

					$select = $streetField.', '.$cityField.', '.$stateField.', '.$zipField.', '.$country;
					$selectArray = t3lib_div::trimExplode(',', $select, true);
					$select = implode(',', $selectArray);

					$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($select, 'fe_users', '`uid`='.$feuser_id);
					return $rows[0][$streetField].', '.$rows[0][$cityField].', '.$rows[0][$stateField].' '.$rows[0][$zipField].', '.$rows[0][$countryField];
				}
			} else {

			}
		}
		return null;
	}

	/**
	 * undocumented function
	 *
	 * @param  boolean	determines whether we want directions or not
	 * @return mixed 	either an array with title and description js arrays or an array with title and description js string
	 **/
	function buildTitleAndDescriptionArrays($directions) {
		global $LANG;

		if(is_array($this->tabLabels) && !empty($this->tabLabels)) {
			$titleArray = '[';
			$first = true;
			foreach( $this->tabLabels as $value ) {
				$value = strip_tags($value);
				if($first) {
					$titleArray .= '"'. $value .'"';
				} else {
					$titleArray .= ', "'. $value .'"';
				}
				$first = false;
			}

			if($directions) {
				$titleArray .= ', "'. $LANG->getLL('directions') .'"]';
			} else {
				$titleArray .= ']';
			}


			$textArray = '[';
			$first = true;

			for($i = 0; $i < count($this->title); $i++) {
				if($first) {
					$textArray .= '"'. $this->title[$i].$this->description[$i] .'"';
				} else {
					$textArray .= ', "'. $this->title[$i].$this->description[$i] .'"';
				}
				$first = false;
			}

			if($directions) {
				$textArray .= ', "'. $this->getDirectionsHTML() .'"]';
			} else {
				$textArray .= ']';
			}
		} else {

			if($directions) {
				$titleArray = '[\''. $LANG->getLL('info') .'\', \''. $LANG->getLL('directions') .'\']';

				$textArray = '[\''.$this->title.$this->description.'\', \''. $this->getDirectionsHTML(). '\']';

			} else {
				$titleArray = $this->title;
				$textArray = $this->description;
			}
		}

		return array($titleArray, $textArray);

	}

	/**
	 * Returns the javascript function call to center on this marker
	 *
	 * @return String
	 **/
	function getClickJS() {
		return $this->mapName.'_triggerMarker('. $this->groupId .', '. $this->index .', '. $this->calculateClickZoom() .');';
	}
	
	/**
	 * calculates the optimal zoom level for the click
	 *
	 * @return integer
	 **/
	function calculateClickZoom() {
		$zoom = 14;
		// we want to keep the zoom level around $zoom, but will
		// choose the max if the marker is only visible under $zoom,
		// or the min if it's only shown over $zoom.
		if($zoom < $this->minzoom) {
			$zoom = $this->minzoom;
		} else if($zoom > $this->maxzoom) {
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
		$newstr = str_replace($order, $replace, $input);

		return $newstr;
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
		$replace = '';
		$newstr = str_replace($order, $replace, $input);

		return $newstr;
	}

	/**
	 * Getter for hasTabs variable
	 *
	 * @return boolean
	 **/
	function hasTabs() {
		return $this->hasTabs;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/map_service/google/class.tx_wecmap_marker_google.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/map_service/google/class.tx_wecmap_marker_google.php']);
}


?>