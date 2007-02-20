<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

/* Add the frontend plugins */
t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_wecmap_pi1.php','_pi1','list_type',1);
t3lib_extMgm::addPItoST43($_EXTKEY,'pi2/class.tx_wecmap_pi2.php','_pi2','list_type',1);

$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:wec_map/class.tx_wecmap_backend.php:tx_wecmap_backend';


/* Add the Geocoder.us geocoding service. */
t3lib_extMgm::addService($_EXTKEY,'geocode','tx_wecmap_geocode_geocoder',
	array(
	'title' => 'Geocoder.us Address Lookup',
	'description' => '',

	'subtype' => '',

	'available' => TRUE,
	'priority' => 50,
	'quality' => 50,

	'os' => '',
	'exec' => '',

	'classFile' => t3lib_extMgm::extPath($_EXTKEY).'geocode_service/class.tx_wecmap_geocode_geocoder.php',
	'className' => 'tx_wecmap_geocode_geocoder',
	)
);

/* Add the Yahoo! geocoding service */
t3lib_extMgm::addService($_EXTKEY,'geocode','tx_wecmap_geocode_yahoo',
	array(

		'title' => 'Yahoo! Maps Address Lookup',
		'description' => '',

		'subtype' => '',

		'available' => TRUE,
		'priority' => 75,
		'quality' => 75,

		'os' => '',
		'exec' => '',

		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'geocode_service/class.tx_wecmap_geocode_yahoo.php',
		'className' => 'tx_wecmap_geocode_yahoo',
	)
);

/* Add the Google geocoding service */
t3lib_extMgm::addService($_EXTKEY,'geocode','tx_wecmap_geocode_google',
	array(

		'title' => 'Google Maps Address Lookup',
		'description' => '',

		'subtype' => '',

		'available' => TRUE,
		'priority' => 100,
		'quality' => 100,

		'os' => '',
		'exec' => '',

		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'geocode_service/class.tx_wecmap_geocode_google.php',
		'className' => 'tx_wecmap_geocode_google',
	)
);	

/* Add the Worldkit geocoding service. */	
t3lib_extMgm::addService($_EXTKEY,'geocode','tx_wecmap_geocode_worldkit',
	array(
		'title' => 'Worldkit City Lookup',
		'description' => '',

		'subtype' => '',

		'available' => TRUE,
		'priority' => 25,
		'quality' => 25,

		'os' => '',
		'exec' => '',

		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'geocode_service/class.tx_wecmap_geocode_worldkit.php',
		'className' => 'tx_wecmap_geocode_worldkit',
	)
);
	
?>