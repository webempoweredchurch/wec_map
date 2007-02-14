<?php

########################################################################
# Extension Manager/Repository config file for ext: "wec_map"
#
# Auto generated 13-02-2007 17:21
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'WEC Map',
	'description' => 'Mapping extension that connects to geocoding databases and Google Maps API.',
	'category' => 'plugin',
	'shy' => 0,
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'mod1,mod2',
	'state' => 'stable',
	'internal' => 0,
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author' => 'Web-Empowered Church Team',
	'author_email' => 'map@webempoweredchurch.org',
	'author_company' => 'Foundation For Evangelism',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '1.0.1',
	'_md5_values_when_last_written' => 'a:54:{s:13:"CHANGELOG.txt";s:4:"7935";s:10:"README.txt";s:4:"ceaa";s:27:"class.tx_wecmap_backend.php";s:4:"303c";s:32:"class.tx_wecmap_batchgeocode.php";s:4:"6333";s:25:"class.tx_wecmap_cache.php";s:4:"a801";s:23:"class.tx_wecmap_map.php";s:4:"1319";s:26:"class.tx_wecmap_marker.php";s:4:"0be2";s:21:"ext_conf_template.txt";s:4:"1e4d";s:12:"ext_icon.gif";s:4:"9d48";s:17:"ext_localconf.php";s:4:"a901";s:14:"ext_tables.php";s:4:"c0e8";s:14:"ext_tables.sql";s:4:"debc";s:16:"locallang_db.xml";s:4:"e382";s:14:"wec_map.tmproj";s:4:"11fc";s:30:"contrib/prototype/prototype.js";s:4:"76a7";s:14:"doc/manual.sxw";s:4:"8731";s:52:"geocode_service/class.tx_wecmap_geocode_geocoder.php";s:4:"00f0";s:50:"geocode_service/class.tx_wecmap_geocode_google.php";s:4:"2bd0";s:52:"geocode_service/class.tx_wecmap_geocode_worldkit.php";s:4:"6794";s:49:"geocode_service/class.tx_wecmap_geocode_yahoo.php";s:4:"e50f";s:20:"images/mm_20_red.png";s:4:"453d";s:23:"images/mm_20_shadow.png";s:4:"f77b";s:49:"map_service/google/class.tx_wecmap_map_google.php";s:4:"4a6d";s:52:"map_service/google/class.tx_wecmap_marker_google.php";s:4:"b941";s:32:"map_service/google/locallang.xml";s:4:"742f";s:47:"map_service/yahoo/class.tx_wecmap_map_yahoo.php";s:4:"78a3";s:28:"map_service/yahoo/yahoo.tmpl";s:4:"a46c";s:42:"mod1/class.tx_wecmap_batchgeocode_util.php";s:4:"e105";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"3a85";s:14:"mod1/index.php";s:4:"8e74";s:18:"mod1/locallang.xml";s:4:"f4dd";s:22:"mod1/locallang_mod.xml";s:4:"43f7";s:19:"mod1/moduleicon.gif";s:4:"7479";s:34:"mod1/tx_wecmap_batchgeocode_ai.php";s:4:"8638";s:14:"mod2/clear.gif";s:4:"cc11";s:13:"mod2/conf.php";s:4:"5c71";s:14:"mod2/index.php";s:4:"af10";s:18:"mod2/locallang.xml";s:4:"9a17";s:22:"mod2/locallang_mod.xml";s:4:"25bc";s:19:"mod2/moduleicon.gif";s:4:"6bde";s:14:"pi1/ce_wiz.gif";s:4:"6401";s:27:"pi1/class.tx_wecmap_pi1.php";s:4:"76ef";s:35:"pi1/class.tx_wecmap_pi1_wizicon.php";s:4:"dc20";s:19:"pi1/flexform_ds.xml";s:4:"6026";s:17:"pi1/locallang.xml";s:4:"0ea3";s:20:"pi1/static/setup.txt";s:4:"3287";s:14:"pi2/ce_wiz.gif";s:4:"56e0";s:27:"pi2/class.tx_wecmap_pi2.php";s:4:"80fc";s:35:"pi2/class.tx_wecmap_pi2_wizicon.php";s:4:"f426";s:19:"pi2/flexform_ds.xml";s:4:"48e0";s:17:"pi2/locallang.xml";s:4:"046e";s:24:"pi2/static/constants.txt";s:4:"d41d";s:20:"pi2/static/setup.txt";s:4:"2121";}',
	'constraints' => array(
		'depends' => array(
			'php' => '3.0.0-',
			'typo3' => '3.8.0-',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
);

?>