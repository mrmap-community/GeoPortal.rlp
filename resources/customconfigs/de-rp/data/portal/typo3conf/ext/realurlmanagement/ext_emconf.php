<?php

########################################################################
# Extension Manager/Repository config file for ext: "realurlmanagement"
#
# Auto generated 04-01-2007 12:18
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'RealURL Management',
	'description' => 'Allows you to delete and change the URL\'s created by RealURL. It is useful after site-renaming, if RealURL create a URL that you don\'t like or if you are done with testing and you want to insert real data.',
	'category' => 'module',
	'shy' => 0,
	'version' => '0.3.1',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'mod1',
	'state' => 'alpha',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Juraj Sulek',
	'author_email' => 'juraj@sulek.sk',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'realurl' => '',
			'php' => '3.0.0-',
			'typo3' => '3.5.0-',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:38:{s:9:"ChangeLog";s:4:"f9be";s:10:"README.txt";s:4:"ee2d";s:30:"class.tx_realurlmanagement.php";s:4:"beb0";s:12:"ext_icon.gif";s:4:"1bdc";s:14:"ext_tables.php";s:4:"97f3";s:14:"doc/manual.sxw";s:4:"ab06";s:19:"doc/wizard_form.dat";s:4:"ed13";s:20:"doc/wizard_form.html";s:4:"363b";s:20:"gfx/clear_counts.gif";s:4:"274a";s:23:"gfx/order_asc_activ.gif";s:4:"a007";s:25:"gfx/order_asc_inactiv.gif";s:4:"8f8a";s:24:"gfx/order_desc_activ.gif";s:4:"d457";s:26:"gfx/order_desc_inactiv.gif";s:4:"22fb";s:18:"gfx/set_expire.gif";s:4:"be7a";s:21:"gfx/set_expireAll.gif";s:4:"2c07";s:20:"gfx/setup/domain.gif";s:4:"ff95";s:25:"gfx/setup/domain_copy.gif";s:4:"ce13";s:34:"gfx/setup/domain_copy_notexist.gif";s:4:"fb16";s:29:"gfx/setup/domain_notexist.gif";s:4:"46d8";s:20:"gfx/setup/others.gif";s:4:"27e0";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"b180";s:14:"mod1/index.php";s:4:"caef";s:18:"mod1/locallang.php";s:4:"e6c4";s:22:"mod1/locallang_mod.php";s:4:"ac97";s:24:"mod1/locallang_setup.php";s:4:"7030";s:19:"mod1/moduleicon.gif";s:4:"2580";s:35:"mod1/tx_realurlmanagement_about.php";s:4:"7448";s:37:"mod1/tx_realurlmanagement_aliases.php";s:4:"4dab";s:37:"mod1/tx_realurlmanagement_dbclean.php";s:4:"410e";s:36:"mod1/tx_realurlmanagement_errors.php";s:4:"3cd9";s:38:"mod1/tx_realurlmanagement_helpfunc.php";s:4:"f70f";s:35:"mod1/tx_realurlmanagement_pages.php";s:4:"8cf4";s:39:"mod1/tx_realurlmanagement_redirects.php";s:4:"0aa3";s:35:"mod1/tx_realurlmanagement_setup.php";s:4:"8bd0";s:45:"mod1/tx_realurlmanagement_setup_clickmenu.php";s:4:"b2cf";s:40:"mod1/tx_realurlmanagement_setup_help.php";s:4:"63b2";s:45:"mod1/tx_realurlmanagement_setupbrowsetree.php";s:4:"3a3e";}',
);

?>