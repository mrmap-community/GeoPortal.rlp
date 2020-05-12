<?php

########################################################################
# Extension Manager/Repository config file for ext: "date2cal"
#
# Auto generated 06-05-2008 16:42
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Date2Calendar',
	'description' => 'Extends all backend date/datetime fields with a calendar (http://www.dynarch.com/projects/calendar). The calendar provides an additional natural language parsing mode (http://datetime.toolbocks.com/). Also it offers a small API to use the calendar in other extensions too.',
	'category' => 'be',
	'shy' => 0,
	'version' => '7.1.0',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => 'bottom',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Stefan Galinski',
	'author_email' => 'stefan.galinski@gmail.com',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'php' => '5.0.0-5.2.99',
			'typo3' => '3.8.0-4.2.99',
		),
		'conflicts' => array(
			'erotea_date2cal' => '',
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:139:{s:9:"ChangeLog";s:4:"4962";s:17:"date2cal.doxyfile";s:4:"0e07";s:16:"de.locallang.xml";s:4:"1aca";s:21:"ext_conf_template.txt";s:4:"9c50";s:12:"ext_icon.gif";s:4:"78f7";s:17:"ext_localconf.php";s:4:"0b0a";s:14:"ext_tables.php";s:4:"69f5";s:13:"locallang.xml";s:4:"9c8d";s:14:"js/date2cal.js";s:4:"117b";s:27:"js/naturalLanguageParser.js";s:4:"3f0b";s:23:"js/jscalendar/ChangeLog";s:4:"5628";s:31:"js/jscalendar/calendar-setup.js";s:4:"827f";s:25:"js/jscalendar/calendar.js";s:4:"e6d7";s:33:"js/jscalendar/lang/calendar-af.js";s:4:"8a39";s:33:"js/jscalendar/lang/calendar-al.js";s:4:"17fb";s:33:"js/jscalendar/lang/calendar-bg.js";s:4:"eaa5";s:40:"js/jscalendar/lang/calendar-big5-utf8.js";s:4:"9eaa";s:35:"js/jscalendar/lang/calendar-big5.js";s:4:"d4f2";s:33:"js/jscalendar/lang/calendar-br.js";s:4:"f4c3";s:33:"js/jscalendar/lang/calendar-ca.js";s:4:"a912";s:38:"js/jscalendar/lang/calendar-cs-utf8.js";s:4:"6119";s:37:"js/jscalendar/lang/calendar-cs-win.js";s:4:"1776";s:33:"js/jscalendar/lang/calendar-da.js";s:4:"c942";s:33:"js/jscalendar/lang/calendar-de.js";s:4:"f811";s:33:"js/jscalendar/lang/calendar-du.js";s:4:"5c26";s:33:"js/jscalendar/lang/calendar-el.js";s:4:"b2b4";s:33:"js/jscalendar/lang/calendar-en.js";s:4:"4681";s:33:"js/jscalendar/lang/calendar-es.js";s:4:"9760";s:33:"js/jscalendar/lang/calendar-fi.js";s:4:"f385";s:33:"js/jscalendar/lang/calendar-fr.js";s:4:"3d36";s:38:"js/jscalendar/lang/calendar-he-utf8.js";s:4:"3e8f";s:38:"js/jscalendar/lang/calendar-hr-utf8.js";s:4:"7d22";s:33:"js/jscalendar/lang/calendar-hr.js";s:4:"e83c";s:33:"js/jscalendar/lang/calendar-hu.js";s:4:"a884";s:33:"js/jscalendar/lang/calendar-it.js";s:4:"4e7a";s:33:"js/jscalendar/lang/calendar-jp.js";s:4:"9071";s:38:"js/jscalendar/lang/calendar-ko-utf8.js";s:4:"ea3e";s:33:"js/jscalendar/lang/calendar-ko.js";s:4:"e0e3";s:38:"js/jscalendar/lang/calendar-lt-utf8.js";s:4:"cfac";s:33:"js/jscalendar/lang/calendar-lt.js";s:4:"0175";s:33:"js/jscalendar/lang/calendar-lv.js";s:4:"77e7";s:33:"js/jscalendar/lang/calendar-nl.js";s:4:"01a6";s:33:"js/jscalendar/lang/calendar-no.js";s:4:"931f";s:38:"js/jscalendar/lang/calendar-pl-utf8.js";s:4:"f92e";s:33:"js/jscalendar/lang/calendar-pl.js";s:4:"d4d0";s:33:"js/jscalendar/lang/calendar-pt.js";s:4:"7d8b";s:33:"js/jscalendar/lang/calendar-ro.js";s:4:"559f";s:33:"js/jscalendar/lang/calendar-ru.js";s:4:"bc19";s:38:"js/jscalendar/lang/calendar-ru_win_.js";s:4:"b1fa";s:33:"js/jscalendar/lang/calendar-si.js";s:4:"7e30";s:33:"js/jscalendar/lang/calendar-sk.js";s:4:"1565";s:33:"js/jscalendar/lang/calendar-sp.js";s:4:"c97f";s:33:"js/jscalendar/lang/calendar-sv.js";s:4:"ee70";s:33:"js/jscalendar/lang/calendar-tr.js";s:4:"b692";s:33:"js/jscalendar/lang/calendar-zh.js";s:4:"9a07";s:29:"js/jscalendar/lang/cn_utf8.js";s:4:"0a98";s:33:"js/jscalendar/skins/menuarrow.gif";s:4:"1f8c";s:34:"js/jscalendar/skins/menuarrow2.gif";s:4:"1f8c";s:37:"js/jscalendar/skins/t3skin2/theme.css";s:4:"7d8d";s:36:"js/jscalendar/skins/t3skin/theme.css";s:4:"a959";s:38:"js/jscalendar/skins/aqua/active-bg.gif";s:4:"f8fb";s:36:"js/jscalendar/skins/aqua/dark-bg.gif";s:4:"949f";s:37:"js/jscalendar/skins/aqua/hover-bg.gif";s:4:"803a";s:38:"js/jscalendar/skins/aqua/menuarrow.gif";s:4:"1f8c";s:38:"js/jscalendar/skins/aqua/normal-bg.gif";s:4:"8511";s:40:"js/jscalendar/skins/aqua/rowhover-bg.gif";s:4:"c097";s:38:"js/jscalendar/skins/aqua/status-bg.gif";s:4:"1238";s:34:"js/jscalendar/skins/aqua/theme.css";s:4:"82c5";s:37:"js/jscalendar/skins/aqua/title-bg.gif";s:4:"8d65";s:37:"js/jscalendar/skins/aqua/today-bg.gif";s:4:"9bef";s:40:"js/jscalendar/skins/skin_grey2/theme.css";s:4:"2b76";s:24:"src/class.jscalendar.php";s:4:"ef7b";s:32:"src/class.tx_date2cal_befunc.php";s:4:"60bd";s:32:"src/class.tx_date2cal_shared.php";s:4:"f7cd";s:32:"src/class.tx_date2cal_wizard.php";s:4:"cbc9";s:29:"src/class.ux_sc_db_layout.php";s:4:"c322";s:14:"doc/manual.sxw";s:4:"9b19";s:23:"doc/html/annotated.html";s:4:"fb8d";s:37:"doc/html/classJSCalendar-members.html";s:4:"7ef8";s:29:"doc/html/classJSCalendar.html";s:4:"590e";s:43:"doc/html/class_8jscalendar_8php-source.html";s:4:"6322";s:36:"doc/html/class_8jscalendar_8php.html";s:4:"3786";s:53:"doc/html/class_8tx__date2cal__befunc_8php-source.html";s:4:"a58a";s:46:"doc/html/class_8tx__date2cal__befunc_8php.html";s:4:"dc92";s:53:"doc/html/class_8tx__date2cal__shared_8php-source.html";s:4:"f001";s:46:"doc/html/class_8tx__date2cal__shared_8php.html";s:4:"c7c6";s:53:"doc/html/class_8tx__date2cal__wizard_8php-source.html";s:4:"073a";s:46:"doc/html/class_8tx__date2cal__wizard_8php.html";s:4:"2644";s:51:"doc/html/class_8ux__sc__db__layout_8php-source.html";s:4:"b240";s:44:"doc/html/class_8ux__sc__db__layout_8php.html";s:4:"1487";s:47:"doc/html/classtx__date2cal__befunc-members.html";s:4:"7eae";s:39:"doc/html/classtx__date2cal__befunc.html";s:4:"9907";s:50:"doc/html/classtx__date2cal__extTables-members.html";s:4:"febe";s:42:"doc/html/classtx__date2cal__extTables.html";s:4:"5604";s:47:"doc/html/classtx__date2cal__shared-members.html";s:4:"12a5";s:39:"doc/html/classtx__date2cal__shared.html";s:4:"9f10";s:47:"doc/html/classtx__date2cal__wizard-members.html";s:4:"3d0e";s:39:"doc/html/classtx__date2cal__wizard.html";s:4:"8c6b";s:45:"doc/html/classux__SC__db__layout-members.html";s:4:"821d";s:37:"doc/html/classux__SC__db__layout.html";s:4:"74fb";s:20:"doc/html/doxygen.css";s:4:"2b5b";s:20:"doc/html/doxygen.png";s:4:"33f8";s:37:"doc/html/ext__emconf_8php-source.html";s:4:"384c";s:30:"doc/html/ext__emconf_8php.html";s:4:"6d19";s:40:"doc/html/ext__localconf_8php-source.html";s:4:"be4c";s:33:"doc/html/ext__localconf_8php.html";s:4:"b998";s:37:"doc/html/ext__tables_8php-source.html";s:4:"2809";s:30:"doc/html/ext__tables_8php.html";s:4:"11be";s:19:"doc/html/files.html";s:4:"5942";s:22:"doc/html/ftv2blank.png";s:4:"8568";s:20:"doc/html/ftv2doc.png";s:4:"c97b";s:29:"doc/html/ftv2folderclosed.png";s:4:"ecb0";s:27:"doc/html/ftv2folderopen.png";s:4:"88b0";s:25:"doc/html/ftv2lastnode.png";s:4:"2d01";s:21:"doc/html/ftv2link.png";s:4:"d5ed";s:26:"doc/html/ftv2mlastnode.png";s:4:"32d9";s:22:"doc/html/ftv2mnode.png";s:4:"c50e";s:21:"doc/html/ftv2node.png";s:4:"0ff4";s:26:"doc/html/ftv2plastnode.png";s:4:"6369";s:22:"doc/html/ftv2pnode.png";s:4:"c97b";s:25:"doc/html/ftv2vertline.png";s:4:"b4b9";s:23:"doc/html/functions.html";s:4:"60a2";s:28:"doc/html/functions_func.html";s:4:"53aa";s:28:"doc/html/functions_vars.html";s:4:"bd03";s:21:"doc/html/globals.html";s:4:"9a69";s:26:"doc/html/globals_vars.html";s:4:"464f";s:19:"doc/html/index.html";s:4:"7686";s:18:"doc/html/main.html";s:4:"1a2c";s:19:"doc/html/pages.html";s:4:"1baf";s:18:"doc/html/tab_b.gif";s:4:"a22e";s:18:"doc/html/tab_l.gif";s:4:"749f";s:18:"doc/html/tab_r.gif";s:4:"9802";s:17:"doc/html/tabs.css";s:4:"9656";s:18:"doc/html/todo.html";s:4:"be17";s:18:"doc/html/tree.html";s:4:"dffb";s:16:"res/calendar.png";s:4:"5307";s:17:"res/calendar2.gif";s:4:"c1e5";s:16:"res/helpIcon.gif";s:4:"d7e5";s:17:"res/helpPage.html";s:4:"41b8";}',
);

?>