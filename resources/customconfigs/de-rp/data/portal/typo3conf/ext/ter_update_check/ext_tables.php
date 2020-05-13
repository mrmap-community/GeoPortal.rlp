<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

if (TYPO3_MODE=="BE")	{

    if(t3lib_div::int_from_ver(TYPO3_version) >= 4000000) {
    if(t3lib_div::int_from_ver(TYPO3_version) >= 4001000) {
	t3lib_extMgm::insertModuleFunction(
		"tools_em",
		"tx_terupdatecheck41_modfunc1",
		t3lib_extMgm::extPath($_EXTKEY)."modfunc1/class.tx_terupdatecheck41_modfunc1.php",
		"LLL:EXT:ter_update_check/locallang_db.php:moduleFunction.tx_terupdatecheck2_modfunc1"
	);
	}
	else {
	t3lib_extMgm::insertModuleFunction(
		"tools_em",		
		"tx_terupdatecheck2_modfunc1",
		t3lib_extMgm::extPath($_EXTKEY)."modfunc1/class.tx_terupdatecheck2_modfunc1.php",
		"LLL:EXT:ter_update_check/locallang_db.php:moduleFunction.tx_terupdatecheck2_modfunc1"
	);
	}
    }
    else {
	t3lib_extMgm::insertModuleFunction(
		"tools_em",		
		"tx_ter_update_check_modfunc1",
		t3lib_extMgm::extPath($_EXTKEY)."modfunc1/class.tx_ter_update_check_modfunc1.php",
		"LLL:EXT:ter_update_check/locallang_db.php:moduleFunction.tx_ter_update_check_modfunc1"
	);
    }
}
?>