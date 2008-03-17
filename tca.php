<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_wecmap_external"] = Array (
	"ctrl" => $TCA["tx_wecmap_external"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "title,url"
	),
	"feInterface" => $TCA["tx_wecmap_external"]["feInterface"],
	"columns" => Array (
		"title" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_map/locallang_db.xml:tx_wecmap_external.title",
			"config" => Array (
				"type" => "input",
				"size" => "32",
				"max" => "128",
			)
		),
		"url" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:wec_map/locallang_db.xml:tx_wecmap_external.url",
			"config" => Array (
				"type" => "input",
				"size" => "32",
				"max" => "128",
				'wizards' => Array(
				        '_PADDING' => 2,
				        'link' => Array(
				                'type' => 'popup',
				                'title' => 'Link',
				                'icon' => 'link_popup.gif',
				                'script' => 'browse_links.php?mode=wizard',
				                'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1',
								'params' => Array(
									'allowedExtensions' => 'kml, xml, kmz',
									'blindLinkOptions' => 'mail, page, spec'
								)
				        ),

				)
			),
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "title, url")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "title, url"),
	),
);

?>