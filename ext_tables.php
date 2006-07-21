<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages,recursive';
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2']='layout,select_key,pages,recursive';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi2']='pi_flexform';

t3lib_extMgm::addPlugin(Array('LLL:EXT:wec_map/locallang_db.php:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:wec_map/pi1/flexform_ds.xml');
t3lib_extMgm::addPlugin(Array('LLL:EXT:wec_map/locallang_db.php:tt_content.list_type_pi2', $_EXTKEY.'_pi2'),'list_type');
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi2', 'FILE:EXT:wec_map/pi2/flexform_ds.xml');

//t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Simple Map");
t3lib_extMgm::addStaticFile($_EXTKEY,"static/fe_user_map/","Frontend User Map");

?>