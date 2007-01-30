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
	
	/**
	 * Constructor for the Google Maps marker class.
	 *
	 * @access	public
	 * @param	integer		Index within the overall array of markers.
	 * @param	float		Latitude of the marker location.
	 * @param	float		Longitude of the marker location.
	 * @param	string		Title of the marker.
	 * @param	string		Description of the marker.
	 * @param	string		Unused for Google Maps.
	 * @param	string		Unused for Google Maps.
	 * @return	none
	 */
	function tx_wecmap_marker_google($index, $latitude, $longitude, $title, $description, $color='0xFF0000', $strokeColor='0xFFFFFF') {
		$this->index = $index;
		
		$this->title = addslashes($title);
		$this->description = $this->filterNL2BR(addslashes($description));
		$this->color = $color;
		$this->strokeColor = $strokeColor;
		
		$this->latitude = $latitude;
		$this->longitude = $longitude;
	}
	
	
	/**
	 * Creates the Javascript to add a marker to the page.
	 * 
	 * @access public
	 * @return	string	The Javascript to add a marker to the page.
	 */
	function writeJS() {
		return 'createMarker(new GLatLng('.$this->latitude.','.$this->longitude.'), icon, "'.$this->title.$this->description.'")';
		
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
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/map_service/google/class.tx_wecmap_marker_google.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/map_service/google/class.tx_wecmap_marker_google.php']);
}


?>