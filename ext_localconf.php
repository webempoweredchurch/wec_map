<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_wecmap_pi1 = < plugin.tx_wecmap_pi1.CSS_editor
',43);

## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_wecmap_pi2 = < plugin.tx_wecmap_pi2.CSS_editor
',43);

t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_wecmap_pi1.php','_pi1','list_type',1);
t3lib_extMgm::addPItoST43($_EXTKEY,'pi2/class.tx_wecmap_pi2.php','_pi2','list_type',1);

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

t3lib_extMgm::addService($_EXTKEY,'geocode','tx_wecmap_geocode_google',
	array(

		'title' => 'Google Maps Address Lookup',
		'description' => '',

		'subtype' => '',

		'available' => TRUE,
		'priority' => 70,
		'quality' => 70,

		'os' => '',
		'exec' => '',

		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'geocode_service/class.tx_wecmap_geocode_google.php',
		'className' => 'tx_wecmap_geocode_google',
	)
);	
	
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