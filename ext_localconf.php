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

t3lib_extMgm::addService($_EXTKEY,  'addressLookup' /* sv type */,  'tx_wecmap_sv1' /* sv key */,
		array(

			'title' => 'Geocoder.us Address Lookup',
			'description' => '',

			'subtype' => '',

			'available' => TRUE,
			'priority' => 50,
			'quality' => 50,

			'os' => '',
			'exec' => '',

			'classFile' => t3lib_extMgm::extPath($_EXTKEY).'sv1/class.tx_wecmap_sv1.php',
			'className' => 'tx_wecmap_sv1',
		)
	);
	
	
?>