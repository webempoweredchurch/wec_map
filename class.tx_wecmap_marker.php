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
 * Main class for the wec_map extension.  This class sits between the various
 * frontend plugins and address lookup service to render map data.
 *
 * @author Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class tx_wecmap_marker {
	var $index;

	var $latitude;
	var $longitude;

	var $title;
	var $description;
	var $color;
	var $strokeColor;
	var $mapName;

	/**
	 * Constructor stub. See map_service classes for more details on the marker
	 * constructor.
	 *
	 * @return void
	 **/
	function tx_wecmap_marker() {}

	/**
	 * Getter for internal index for this marker.
	 *
	 * @return integer index of the marker
	 **/
	function getIndex() {
		return $this->index;
	}

	/**
	 * Getter for the marker title.
	 *
	 * @return string title of the marker
	 **/
	function getTitle() {
		return $this->title;
	}

	/**
	 * Getter for marker description
	 *
	 * @return string description of the marker
	 **/
	function getDescription() {
		return $this->description;
	}

	/**
	 * Getter for marker color
	 *
	 * @return string marker color
	 **/
	function getColor() {
		return $this->color;
	}

	/**
	 * Getter for the marker stroke color
	 *
	 * @return string marker stroke color
	 **/
	function getStrokeColor() {
		return $this->strokeColor;
	}

	/**
	 * Getter for the latitude
	 *
	 * @return float latitude
	 **/
	function getLatitude() {
		return $this->latitude;
	}

	/**
	 * Getter for the longitude
	 *
	 * @return float longitude
	 **/
	function getLongitude() {
		return $this->longitude;
	}

	/**
	 * Setter for map name this marker is a part of
	 *
	 * @return void
	 **/
	function setMapName($mapName) {
		$this->mapName = $mapName;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_marker.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/class.tx_wecmap_marker.php']);
}


?>