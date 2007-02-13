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

class tx_wecmap_batchgeocode_util {
	
	/**
	 * Static function for displaying the status bar and related text.
	 *
	 * @param		integer		The number of addresses the Geocoder has processed.
	 * @param		integer		The total number of addresses.
	 * @param		boolean		True/false value for visiblity of the status bar.
	 * @return		string		HTML output.
	 */
	function getStatusBar($processedAddresses, $totalAddresses, $visible=true) {
		global $LANG, $BE_USER;
		
		$progressBarWidth = round($processedAddresses / $totalAddresses * 100);
		
		if(!is_object($LANG)) {
			require_once(t3lib_extMgm::extPath('lang').'lang.php');
			$LANG = t3lib_div::makeInstance('language');
			$LANG->init($BE_USER->uc['lang']);
		}		
		$LANG->includeLLFile('EXT:wec_map/mod1/locallang.xml');
			
		$content = array();
		if($visible) {
			$content[] = '<div id="status" style="margin-bottom: 5px;">';
		} else {
			$content[] = '<div id="status" style="margin-bottom: 5px; display:none;">';
		
		}
	
		
		$content[] = '<div id="bar" style="width:300px; height:20px; border:1px solid black">
						<div id="progress" style="width:'.$progressBarWidth.'%; height:20px; background-color:red"></div>
					</div>
					<p>'.$LANG->getLL('processedStart').' '.$processedAddresses.' '.$LANG->getLL('processedMid').' '.$totalAddresses.'.</p>';
				
		$content[] = '</div>';
	
		return implode(chr(10), $content);
	
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/mod1/class.tx_wecmap_batchgeocode_util.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/mod1/class.tx_wecmap_batchgeocode_util.php']);
}

?>