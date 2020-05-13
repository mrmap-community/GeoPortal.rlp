<?php

########################################################################
# Extension Manager/Repository config file for ext: "pmkslimbox"
#
# Auto generated 08-09-2009 13:05
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'PMK SlimBox',
	'description' => 'PMK SlimBox - Use Lightbox effect instead of TYPO3s standard image click-enlarge. - No XCLASS as all is done using pure Typoscript. - Slimbox is a 4kb visual clone of the popular Lightbox JS by Lokesh Dhakar, written using the ultra compact mootools v1.2 framework.',
	'category' => 'fe',
	'shy' => 0,
	'version' => '3.1.0',
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
		'conflicts' => array(
			'kj_imagelightbox2' => '0.0.0-0.0.0',
			'perfectlightbox' => '0.0.0-0.0.0',
			'wsclicklightbox' => '0.0.0-0.0.0',
			'ju_multibox' => '0.0.0-0.0.0',
		),
		'depends' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:65:{s:9:"ChangeLog";s:4:"170f";s:12:"ext_icon.gif";s:4:"82b5";s:17:"ext_localconf.php";s:4:"0505";s:14:"ext_tables.php";s:4:"f324";s:13:"locallang.xml";s:4:"fd19";s:12:"savefile.php";s:4:"c1e8";s:14:"t3mootools.txt";s:4:"c713";s:27:"tt_news_imageMarkerFunc.php";s:4:"3b90";s:14:"doc/manual.sxw";s:4:"3da3";s:17:"res/images/50.gif";s:4:"5d7c";s:17:"res/images/80.png";s:4:"6e47";s:25:"res/images/BlackClose.gif";s:4:"76ba";s:27:"res/images/BlackLoading.gif";s:4:"6ae5";s:24:"res/images/BlackNext.gif";s:4:"5832";s:28:"res/images/BlackPrevious.gif";s:4:"b1ed";s:27:"res/images/MinimalClose.png";s:4:"caa0";s:29:"res/images/MinimalLoading.gif";s:4:"f7c1";s:26:"res/images/MinimalNext.png";s:4:"f7de";s:30:"res/images/MinimalPrevious.png";s:4:"0377";s:25:"res/images/WhiteClose.gif";s:4:"940a";s:27:"res/images/WhiteLoading.gif";s:4:"3a06";s:24:"res/images/WhiteNext.gif";s:4:"63cd";s:28:"res/images/WhitePrevious.gif";s:4:"3a9a";s:25:"res/images/closelabel.gif";s:4:"1daa";s:22:"res/images/loading.gif";s:4:"93a4";s:23:"res/images/loading2.gif";s:4:"0697";s:22:"res/images/magnify.png";s:4:"049c";s:24:"res/images/nextlabel.gif";s:4:"485d";s:24:"res/images/prevlabel.gif";s:4:"d935";s:27:"res/images/sb_printicon.gif";s:4:"7a7c";s:26:"res/images/sb_saveicon.gif";s:4:"9265";s:26:"res/mediaplayer/player.swf";s:4:"fc09";s:28:"res/mediaplayer/swfobject.js";s:4:"66d4";s:22:"res/mediaplayer/yt.swf";s:4:"100e";s:29:"res/scripts/mediaboxAdv99f.js";s:4:"16e7";s:42:"res/scripts/mediaboxAdv99f_uncompressed.js";s:4:"ca08";s:29:"res/scripts/mootools-1.2.1.js";s:4:"3ff8";s:42:"res/scripts/mootools-1.2.1_uncompressed.js";s:4:"443c";s:22:"res/scripts/slimbox.js";s:4:"36ba";s:29:"res/scripts/slimboxMagnify.js";s:4:"dce8";s:42:"res/scripts/slimboxMagnify_uncompressed.js";s:4:"0921";s:35:"res/scripts/slimbox_uncompressed.js";s:4:"878f";s:26:"res/scripts/slimboxplus.js";s:4:"16c1";s:39:"res/scripts/slimboxplus_uncompressed.js";s:4:"1945";s:31:"res/styles/mediaboxAdvBlack.css";s:4:"b6fc";s:33:"res/styles/mediaboxAdvMinimal.css";s:4:"7e39";s:31:"res/styles/mediaboxAdvWhite.css";s:4:"8ca4";s:22:"res/styles/slimbox.css";s:4:"f1b8";s:26:"res/styles/slimboxplus.css";s:4:"2efc";s:29:"static/mediabox/constants.txt";s:4:"14d2";s:25:"static/mediabox/setup.txt";s:4:"287f";s:40:"static/mediabox_t3mootools/constants.txt";s:4:"14d2";s:36:"static/mediabox_t3mootools/setup.txt";s:4:"585b";s:28:"static/slimbox/constants.txt";s:4:"a46c";s:24:"static/slimbox/setup.txt";s:4:"e90a";s:35:"static/slimboxmagnify/constants.txt";s:4:"92d8";s:31:"static/slimboxmagnify/setup.txt";s:4:"cfb0";s:46:"static/slimboxmagnify_t3mootools/constants.txt";s:4:"709b";s:42:"static/slimboxmagnify_t3mootools/setup.txt";s:4:"9c6e";s:32:"static/slimboxplus/constants.txt";s:4:"a462";s:28:"static/slimboxplus/setup.txt";s:4:"33ce";s:43:"static/slimboxplus_t3mootools/constants.txt";s:4:"a462";s:39:"static/slimboxplus_t3mootools/setup.txt";s:4:"4ae2";s:39:"static/slimbox_t3mootools/constants.txt";s:4:"41e4";s:35:"static/slimbox_t3mootools/setup.txt";s:4:"6f15";}',
);

?>