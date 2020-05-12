<?php
if (!defined ('TYPO3_MODE'))     die ('Access denied.');
$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['typo3/template.php'] = t3lib_extMgm::extPath('pmktextarea').'class.ux_template.php';
?>
