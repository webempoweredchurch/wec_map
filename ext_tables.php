<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=="BE")    {       
    t3lib_extMgm::addModule("tools","txwecmapM1","",t3lib_extMgm::extPath($_EXTKEY)."mod1/");
    t3lib_extMgm::addModule("tools","txwecmapM2","",t3lib_extMgm::extPath($_EXTKEY)."mod2/");
}

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages,recursive';
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2']='layout,select_key,pages,recursive';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi2']='pi_flexform';

t3lib_extMgm::addPlugin(Array('LLL:EXT:wec_map/locallang_db.php:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');
t3lib_extMgm::addPlugin(Array('LLL:EXT:wec_map/locallang_db.php:tt_content.list_type_pi2', $_EXTKEY.'_pi2'),'list_type');
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:wec_map/pi1/flexform_ds.xml');
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi2', 'FILE:EXT:wec_map/pi2/flexform_ds.xml');

//t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Simple Map");
t3lib_extMgm::addStaticFile($_EXTKEY,"static/fe_user_map/","Frontend User Map");

require_once(t3lib_extMgm::extPath('wec_map').'class.tx_wecmap_backend.php');

$tempColumns = Array (
	'tx_wecmap_map' => array (		
		'exclude' => 1,		
		'label' => 'LLL:EXT:wec_map/locallang_db.php:berecord_maplabel',		
		'config' => array (
			'type' => 'user',
			'userFunc' => 'tx_wecmap_backend->drawMap',
		),
	),
);

t3lib_div::loadTCA('fe_users');
t3lib_extMgm::addTCAcolumns('fe_users', $tempColumns, 1);
$TCA['fe_users']['interface']['showRecordFieldList'] .= ',tx_wecmap_map';
$TCA['fe_users']['ctrl']['dividers2tabs'] = 1;

if (TYPO3_MODE=="BE")    $TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_wecmap_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_wecmap_pi1_wizicon.php';
if (TYPO3_MODE=="BE")    $TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_wecmap_pi2_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi2/class.tx_wecmap_pi2_wizicon.php';
//t3lib_extMgm::addToAllTCAtypes('tt_news', 'title;;1;;,type,editlock,datetime;;2;;1-1-1,author;;3;;,short,bodytext;;4;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|table|image]:rte_transform[flag=rte_enabled|mode=ts];4-4-4,no_auto_pb,--div--;Relations,category,image;;;;1-1-1,imagecaption;;5;;,links;;;;2-2-2,related;;;;3-3-3,news_files;;;;4-4-4,--div--;Blog Post,tx_timtab_trackbacks;;;;1-1-1,tx_timtab_comments_allowed;;;;2-2-2,tx_timtab_ping_allowed;;;;', 3);
t3lib_extMgm::addToAllTCAtypes('fe_users', '--div--;Map,tx_wecmap_map');
?>