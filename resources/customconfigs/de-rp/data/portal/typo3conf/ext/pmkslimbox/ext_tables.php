<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (t3lib_extMgm::isLoaded('t3mootools')) {
	t3lib_extMgm::addStaticFile($_EXTKEY,'static/slimbox_t3mootools/', 'SlimBox t3m');
	t3lib_extMgm::addStaticFile($_EXTKEY,'static/slimboxplus_t3mootools/', 'SlimBoxPlus t3m');
	t3lib_extMgm::addStaticFile($_EXTKEY,'static/mediabox_t3mootools/', 'MediaBox t3m');
	t3lib_extMgm::addStaticFile($_EXTKEY,'static/slimboxmagnify_t3mootools/', 'SlimBoxMagnify t3m');
}
else {
	t3lib_extMgm::addStaticFile($_EXTKEY,'static/slimbox/', 'SlimBox');
	t3lib_extMgm::addStaticFile($_EXTKEY,'static/slimboxplus/', 'SlimBoxPlus');
	t3lib_extMgm::addStaticFile($_EXTKEY,'static/mediabox/', 'MediaBox');
	t3lib_extMgm::addStaticFile($_EXTKEY,'static/slimboxmagnify/', 'SlimBoxMagnify');
}
/*
	t3lib_extMgm::addStaticFile($_EXTKEY,'static/slimbox/', 'SlimBox');
	t3lib_extMgm::addStaticFile($_EXTKEY,'static/slimboxplus/', 'SlimBoxPlus');
	t3lib_extMgm::addStaticFile($_EXTKEY,'static/mediabox/', 'MediaBox');
	t3lib_extMgm::addStaticFile($_EXTKEY,'static/slimboxmagnify/', 'SlimBoxMagnify');
*/
?>