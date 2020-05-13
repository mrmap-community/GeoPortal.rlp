<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_q4usearch_pi1 = < plugin.tx_q4usearch_pi1.CSS_editor
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_q4usearch_pi1.php','_pi1','list_type',1);
t3lib_extMgm::addPItoST43($_EXTKEY,'pi2/class.tx_q4usearch_pi2.php','_pi2','list_type',1);
t3lib_extMgm::addPItoST43($_EXTKEY,'pi3/class.tx_q4usearch_pi3.php','_pi3','list_type',1);
t3lib_extMgm::addPItoST43($_EXTKEY,'pi4/class.tx_q4usearch_pi4.php','_pi4','list_type',1);
?>