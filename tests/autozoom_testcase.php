<?php
/***************************************************************
* Copyright notice
*
* (c) 2005-2008 Christian Technology Ministries International Inc.
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
 * Test case for WEC Map
 *
 * WARNING: Never ever run a unit test like this on a live site!
 *
 *
 */


class tx_phpunit_test_testcase extends tx_phpunit_testcase {

	public function test_default_max_auto_zoom_is_15() {
		$map = $this->createMap();
		$map->autoCenterAndZoom();
				
		$this->assertEquals(15, $map->zoom);
	}
	
	public function test_max_auto_zoom_setter_with_7() {
		$map = $this->createMap();
		$map->setMaxAutoZoom(7);
		$map->autoCenterAndZoom();
		
		$this->assertEquals(7, $map->zoom);
	}
	
	public function test_max_auto_zoom_is_15_if_setter_empty() {
		$map = $this->createMap();
		$map->setMaxAutoZoom();
		$map->autoCenterAndZoom();
		$this->assertEquals(15, $map->zoom);
	}
	
	public function createMap() {
		include_once(t3lib_extMgm::extPath('wec_map').'map_service/google/class.tx_wecmap_map_google.php');
		$className=t3lib_div::makeInstanceClassName('tx_wecmap_map_google');
		$map = new $className(null, 500, 500, 39.842286, -96.855469, null, $mapName,'name');
		$map->addMarkerByLatLong(39.842286,-96.855469);
		return $map;
	}

}
?>