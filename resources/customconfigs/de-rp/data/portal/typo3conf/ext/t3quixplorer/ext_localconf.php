<?php
if (!defined ("TYPO3_MODE")) 	die ("Access denied.");

$TYPO3_CONF_VARS["BE"]["unzip"]["list"]["pre_lines"] = 3;
$TYPO3_CONF_VARS["BE"]["unzip"]["list"]["post_lines"] = 2;
$TYPO3_CONF_VARS["BE"]["unzip"]["list"]["split_char"] = " ";
$TYPO3_CONF_VARS["BE"]["unzip"]["list"]["file_pos"] = 3;

$TYPO3_CONF_VARS["BE"]["unzip"]["unzip"]["pre_lines"] = 1;
$TYPO3_CONF_VARS["BE"]["unzip"]["unzip"]["post_lines"] = 0;
$TYPO3_CONF_VARS["BE"]["unzip"]["unzip"]["split_char"] = ":";
$TYPO3_CONF_VARS["BE"]["unzip"]["unzip"]["file_pos"] = 1;


?>
