<?php

########################################################################
# Extension Manager/Repository config file for ext: "page_php_content"
#
# Auto generated 28-06-2006 11:55
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Page PHP Content',
	'description' => 'Adds a non-cached "PHP Script" page content type. You can enter a snippet of PHP code as a part of a page and have it executed when building a page without writing an extension or going through tag processing with parseFunc. Uses the "dreaded" PHP eval() and output buffering.',
	'category' => 'fe',
	'shy' => 0,
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'tt_content',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author' => 'Gary',
	'author_email' => 'gniemcew@yahoo.com',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '1.0.1',
	'constraints' => array(
		'depends' => array(
			'typo3' => '',
			'php' => '',
			'cms' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:11:{s:12:"ext_icon.gif";s:4:"bb03";s:17:"ext_localconf.php";s:4:"d030";s:14:"ext_tables.php";s:4:"7025";s:14:"ext_tables.sql";s:4:"ace3";s:28:"ext_typoscript_constants.txt";s:4:"0d86";s:24:"ext_typoscript_setup.txt";s:4:"0ec6";s:16:"locallang_db.php";s:4:"5e8f";s:35:"pi1/class.tx_pagephpcontent_pi1.php";s:4:"57b2";s:14:"doc/manual.sxw";s:4:"1037";s:19:"doc/wizard_form.dat";s:4:"004f";s:20:"doc/wizard_form.html";s:4:"91b5";}',
);

?>