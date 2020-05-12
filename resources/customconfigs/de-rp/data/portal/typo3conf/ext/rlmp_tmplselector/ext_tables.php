<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

if (TYPO3_MODE=="BE")	include_once(t3lib_extMgm::extPath("rlmp_tmplselector")."class.tx_rlmptmplselector_addfilestosel.php");

$tempColumns = Array (
	"tx_rlmptmplselector_main_tmpl" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:rlmp_tmplselector/locallang_db.php:pages.tx_rlmptmplselector_main_tmpl",		
		"config" => Array (
			"type" => "select",
			"items" => Array (
				Array("LLL:EXT:rlmp_tmplselector/locallang_db.php:pages.tx_rlmptmplselector_main_tmpl.I.0", "0", t3lib_extMgm::extRelPath("rlmp_tmplselector")."dummy_main.gif"),
			),
			"itemsProcFunc" => "tx_rlmptmplselector_addfilestosel->main",
		)
	),
	"tx_rlmptmplselector_ca_tmpl" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:rlmp_tmplselector/locallang_db.php:pages.tx_rlmptmplselector_ca_tmpl",
		"config" => Array (
			"type" => "select",
			"items" => Array (
				Array("LLL:EXT:rlmp_tmplselector/locallang_db.php:pages.tx_rlmptmplselector_ca_tmpl.I.0", "0", t3lib_extMgm::extRelPath("rlmp_tmplselector")."dummy_ca.gif"),
			),
			"itemsProcFunc" => "tx_rlmptmplselector_addfilestosel_ca->main",
		)
	),
);

t3lib_div::loadTCA("pages");
t3lib_extMgm::addTCAcolumns("pages",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("pages","tx_rlmptmplselector_main_tmpl;;;;1-1-1, tx_rlmptmplselector_ca_tmpl");
?>
