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
 * Class that adds the wizard icon.
 *
 * @author	Web-Empowered Church Team <servant@webempoweredchurch.org>
 */

class tx_wecmap_pi1_wizicon {
    function proc($wizardItems)    {
        global $LANG;

        $LL = $this->includeLocalLang();

        $wizardItems['plugins_tx_wecmap_pi1'] = array(
            'icon'=>t3lib_extMgm::extRelPath('wec_map').'pi1/ce_wiz.gif',
            'title'=>$LANG->getLLL('pi1_title',$LL),
            'description'=>$LANG->getLLL('pi1_plus_wiz_description',$LL),
            'params'=>'&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=wec_map_pi1'
        );
        return $wizardItems;
    }
    function includeLocalLang()    {
        $llFile = t3lib_extMgm::extPath('wec_map').'pi1/locallang.xml';
		$LOCAL_LANG = t3lib_div::readLLXMLfile($llFile, $GLOBALS['LANG']->lang);
        return $LOCAL_LANG;
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/pi1/class.tx_wecmap_pi1_wizicon.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/pi1/class.tx_wecmap_pi1_wizicon.php']);
}

?>