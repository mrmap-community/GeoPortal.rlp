<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");
$tempColumns = Array (
	"tx_pagephpcontent_php_code" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:page_php_content/locallang_db.php:tt_content.tx_pagephpcontent_php_code",		
		"config" => Array (
			"type" => "text",
			"cols" => "48",	
			"rows" => "20",
		)
	),
);


t3lib_div::loadTCA("tt_content");
t3lib_extMgm::addTCAcolumns("tt_content",$tempColumns,1);


t3lib_div::loadTCA("tt_content");
$TCA["tt_content"]["types"][$_EXTKEY."_pi1"]["showitem"]="CType;;4;button;1-1-1, header;;3;;2-2-2, tx_pagephpcontent_php_code;;;;1-1-1";


t3lib_extMgm::addPlugin(Array("LLL:EXT:page_php_content/locallang_db.php:tt_content.CType", $_EXTKEY."_pi1"),"CType");
?>