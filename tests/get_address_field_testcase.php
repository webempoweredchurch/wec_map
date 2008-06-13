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

class tx_wecmap_get_address_field_testcase extends tx_phpunit_testcase {

	public function test_get_street_field_for_fe_users() {
		$street = tx_wecmap_shared::getAddressField('fe_users', 'street');
		$this->assertEquals('address', $street);
	}
	
	public function test_get_zip_field_for_fe_users() {
		$street = tx_wecmap_shared::getAddressField('fe_users', 'zip');
		$this->assertEquals('zip', $street);
	}
	
	public function test_get_state_field_for_fe_users() {
		$state = tx_wecmap_shared::getAddressField('fe_users', 'state');
		$this->assertEquals('zone', $state);
	}
	
	public function test_get_country_field_for_fe_users() {
		$country = tx_wecmap_shared::getAddressField('fe_users', 'country');
		$this->assertEquals('static_info_country', $country);
	}
	
	public function test_get_city_field_for_fe_users() {
		$city = tx_wecmap_shared::getAddressField('fe_users', 'city');
		$this->assertEquals('city', $city);
	}
	
	
	public function __construct() {
		global $TCA;
		include_once(t3lib_extMgm::extPath('wec_map').'class.tx_wecmap_shared.php');
	}

}
?>