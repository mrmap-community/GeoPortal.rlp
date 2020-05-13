<?php

// needed xclass to get a working quick edit mode for the old page module
if (!isset($TYPO3_CONF_VARS['BE']['XCLASS']['ext/cms/layout/db_layout.php']) &&
	t3lib_div::int_from_ver(TYPO3_version) < 4002000) {

	$TYPO3_CONF_VARS['BE']['XCLASS']['ext/cms/layout/db_layout.php'] =
		t3lib_extMgm::extPath($_EXTKEY) . 'src/class.ux_sc_db_layout.php';
}

// hook to add date2cal features for flexforms
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['getFlexFormDSClass'][] =
	'EXT:date2cal/src/class.tx_date2cal_befunc.php:tx_date2cal_befunc';
?>
