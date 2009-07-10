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



/**
 * Defines a group of markers to display on the map. This class is the interface
 * to the GMarkerManager. Every map has one or more groups, which has one or more
 * markers. Every marker belongs to one group, and every group belongs to one map.
 *
 * @author Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class tx_wecmap_markergroup {
	var $markers;			// array of marker objects
	var $markerCount = 0; 	// convenience variable with number of markers = sizeof($markers);
	var $id;				// unique identifier of this group
	var $mapName;			// the name of the map this group belongs to
	var $minzoom;			// min zoom level for this group
	var $maxzoom;			// max zoom level for this group

	/**
	 * PHP4 Constructor
	 * @param int unique id of this group
	 *
	 * @return void
	 **/
	function tx_wecmap_markergroup($id, $minzoom, $maxzoom) {
		$this->__construct($id, $minzoom, $maxzoom);
	}

	/**
	 * PHP5 constructor
	 *
	 * @return void
	 **/
	function __construct($id, $minzoom, $maxzoom) {
		$this->id = $id;
		$this->minzoom = $minzoom;
		$this->maxzoom = $maxzoom;
	}

	/**
	 * returns the js array
	 *
	 * @return array javascript content
	 **/
	function drawMarkerJS() {
		$jsContent = array();

		foreach ($this->markers as $key => $marker) {
			if ($this->directions) {
				$jsContent[] = $marker->writeJSwithDirections();
				$jsContent[] = $marker->writeCreateMarkerJS();
			} else {
				$jsContent[] = $marker->writeJS();
				$jsContent[] = $marker->writeCreateMarkerJS();
			}
		}
		if (count($jsContent)) {
			$jsContent[] = 'WecMap.addMarkersToManager("' . $this->mapName .'", ' . $this->id . ', ' . $this->minzoom . ', ' . $this->maxzoom . ');';
		}
		return $jsContent;
	}

	/**
	 * adds a marker object to this group
	 *
	 * @return void
	 **/
	function addMarker(&$markerObj) {
		$markerObj->setMinZoom($this->minzoom);
		$markerObj->setMaxZoom($this->maxzoom);
		$markerObj->setMapName($this->mapName);
		$markerObj->setGroupId($this->id);
		$this->markers[] = &$markerObj;
		// TODO: devlog start
		if(TYPO3_DLOG) {
			t3lib_div::devLog($this->mapName.': -----adding marker - start----', 'wec_map_api');
			t3lib_div::devLog($this->mapName.': id:'.$markerObj->getIndex(), 'wec_map_api');
			t3lib_div::devLog($this->mapName.': minzoom: '.$this->minzoom, 'wec_map_api');
			t3lib_div::devLog($this->mapName.': maxzoom: '. $this->maxzoom, 'wec_map_api');
			t3lib_div::devLog($this->mapName.': group: '. $this->id, 'wec_map_api');
			t3lib_div::devLog($this->mapName.': count: '.$this->markerCount, 'wec_map_api');
			t3lib_div::devLog($this->mapName.': title: '.implode(',', $markerObj->getTitle()), 'wec_map_api');
			t3lib_div::devLog($this->mapName.': desc: '.implode(',',$markerObj->getDescription()), 'wec_map_api');
			t3lib_div::devLog($this->mapName.': -----adding marker - end----', 'wec_map_api');
		}
		// devlog end
		$this->markerCount++;
	}

	/**
	 * return min zoom level
	 *
	 * @return int
	 **/
	function getMinZoom() {
		return $this->minzoom;
	}

	/**
	 * return max zoom
	 *
	 * @return int
	 **/
	function getMaxZoom() {
		return $this->maxzoom;
	}

	/**
	 * return the number of markers in this group
	 *
	 * @return int
	 **/
	function getMarkerCount() {
		return $this->markerCount;
	}

	/**
	 * set map name
	 *
	 * @return void
	 **/
	function setMapName($name) {
		$this->mapName = $name;
	}

	/**
	 * Enables directions
	 *
	 * @return void
	 **/
	function setDirections($dirs=true) {
		$this->directions = $dirs;
	}

	/**
	 * Returns whether this group has any markers
	 *
	 * @return boolean
	 **/
	function hasMarkers() {
		if($this->markerCount > 0) {
			return true;
		} else {
			return false;
		}
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_markergroup.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_markergroup.php']);
}


?>