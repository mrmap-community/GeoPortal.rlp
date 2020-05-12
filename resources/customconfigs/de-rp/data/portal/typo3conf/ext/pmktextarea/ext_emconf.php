<?php

########################################################################
# Extension Manager/Repository config file for ext: "pmktextarea"
#
# Auto generated 06-05-2008 16:42
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'PMK Textarea Widget',
	'description' => 'Adds resizing, linenumbers, improved Tabkey handling and other neat features to standard textareas.',
	'category' => 'be',
	'shy' => 0,
	'version' => '0.3.0',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Peter Klein',
	'author_email' => 'peter@umloud.dk',
	'author_company' => 'Umloud Untd',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'php' => '4.3.0-0.0.0',
			'typo3' => '3.9.9-0.0.0',
		),
		'conflicts' => array(
			'skingreyman' => '0.0.0-',
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:20:{s:9:"ChangeLog";s:4:"c2e6";s:10:"README.txt";s:4:"4e61";s:21:"class.ux_template.php";s:4:"b6d5";s:21:"ext_conf_template.txt";s:4:"faa6";s:12:"ext_icon.gif";s:4:"ad52";s:17:"ext_localconf.php";s:4:"fd4d";s:13:"locallang.xml";s:4:"86a9";s:15:"pmk_textarea.js";s:4:"1544";s:14:"doc/manual.sxw";s:4:"638c";s:12:"res/find.gif";s:4:"3c33";s:18:"res/fsize_down.gif";s:4:"e8cf";s:16:"res/fsize_up.gif";s:4:"b948";s:12:"res/jump.gif";s:4:"0d81";s:16:"res/lnum_off.gif";s:4:"782a";s:15:"res/lnum_on.gif";s:4:"cc03";s:16:"res/maximize.gif";s:4:"ddbf";s:16:"res/minimize.gif";s:4:"89e4";s:24:"res/statusbar_resize.gif";s:4:"c958";s:16:"res/wrap_off.gif";s:4:"278e";s:15:"res/wrap_on.gif";s:4:"86dd";}',
);

?>