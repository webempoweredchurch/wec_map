<?php

########################################################################
# Extension Manager/Repository config file for ext: "wec_map"
#
# Auto generated 01-05-2006 16:18
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
	'module' => '',
	'state' => 'alpha',
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
	'version' => '0.0.5',
	'_md5_values_when_last_written' => 'a:29:{s:9:"ChangeLog";s:4:"37e4";s:10:"README.txt";s:4:"04fb";s:25:"class.tx_wecmap_cache.php";s:4:"2ce7";s:23:"class.tx_wecmap_map.php";s:4:"cdaf";s:26:"class.tx_wecmap_marker.php";s:4:"a38d";s:12:"ext_icon.gif";s:4:"1bdc";s:17:"ext_localconf.php";s:4:"b666";s:14:"ext_tables.php";s:4:"f80f";s:14:"ext_tables.sql";s:4:"46a9";s:16:"locallang_db.php";s:4:"ed16";s:14:"wec_map.tmproj";s:4:"11fc";s:14:"doc/manual.sxw";s:4:"7200";s:19:"doc/wizard_form.dat";s:4:"d0e7";s:20:"doc/wizard_form.html";s:4:"8454";s:52:"geocode_service/class.tx_wecmap_geocode_geocoder.php";s:4:"ee8a";s:52:"geocode_service/class.tx_wecmap_geocode_worldkit.php";s:4:"1d08";s:49:"geocode_service/class.tx_wecmap_geocode_yahoo.php";s:4:"cdf2";s:20:"images/mm_20_red.png";s:4:"453d";s:23:"images/mm_20_shadow.png";s:4:"f77b";s:49:"map_service/google/class.tx_wecmap_map_google.php";s:4:"d51a";s:30:"map_service/google/google.tmpl";s:4:"a65c";s:47:"map_service/yahoo/class.tx_wecmap_map_yahoo.php";s:4:"cff2";s:28:"map_service/yahoo/yahoo.tmpl";s:4:"a46c";s:27:"pi1/class.tx_wecmap_pi1.php";s:4:"17a8";s:19:"pi1/flexform_ds.xml";s:4:"f6a6";s:17:"pi1/locallang.php";s:4:"7020";s:27:"pi2/class.tx_wecmap_pi2.php";s:4:"5fbb";s:19:"pi2/flexform_ds.xml";s:4:"6315";s:17:"pi2/locallang.php";s:4:"7020";}',
	'constraints' => array(
		'depends' => array(
			'php' => '3.0.0-',
			'typo3' => '3.5.0-',
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