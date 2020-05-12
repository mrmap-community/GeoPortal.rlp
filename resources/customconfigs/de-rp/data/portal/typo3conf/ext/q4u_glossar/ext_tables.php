<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$tempColumns = Array (
	"tx_q4uglossar_glossar" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:q4u_glossar/locallang_db.php:pages.tx_q4uglossar_glossar",		
		"config" => Array (
			"type" => "check",
		)
	),
);


t3lib_div::loadTCA("pages");
t3lib_extMgm::addTCAcolumns("pages",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("pages","tx_q4uglossar_glossar;;;;1-1-1");
?>