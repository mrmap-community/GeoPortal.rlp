<?php
$TYPO3_CONF_VARS['SYS']['sitename'] = 'New TYPO3 site';
$TYPO3_CONF_VARS['SYS']['doNotCheckReferer'] = '1';
	// Default password is "joh316" :
$TYPO3_CONF_VARS['BE']['installToolPassword'] = 'bacb98acf97e0b6112b1d1b650b84971';

$TYPO3_CONF_VARS['EXT']['extList'] = 'tsconfig_help,context_help,extra_page_cm_options,impexp,sys_note,tstemplate,tstemplate_ceditor,tstemplate_info,tstemplate_objbrowser,tstemplate_analyzer,func_wizards,wizard_crpages,wizard_sortpages,lowlevel,install,belog,beuser,aboutmodules,setup,taskcenter,info_pagetsconfig,viewpage,rtehtmlarea,css_styled_content,t3skin';

$typo_db_extTableDef_script = 'extTables.php';

## INSTALL SCRIPT EDIT POINT TOKEN - all lines after this points may be changed by the install script!

$typo_db_username = '%%TYPO3DBUSER%%';	//  Modified or inserted by TYPO3 Install Tool.
$typo_db_password = '%%TYPO3DBPASSWORD%%';	//  Modified or inserted by TYPO3 Install Tool.

$typo_db_host = 'localhost';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS['SYS']['encryptionKey'] = 'a52b9a09910ed36b33420733a4be122f9991e457c051af9f5f42ca460773d8d0f123762c98263d2bf47fce07dde0af14';	// Modified or inserted by TYPO3 Install Tool. 
$TYPO3_CONF_VARS['SYS']['compat_version'] = '4.0';	//  Modified or inserted by TYPO3 Install Tool.
$typo_db = '%%TYPO3DB%%';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS['SYS']['sitename'] = 'Rheinland Pfalz';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS['GFX']['im_combine_filename'] = 'composite';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS['GFX']["im_path"] = '/usr/bin/';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS['GFX']['im_version_5'] = 'im6';	// Modified or inserted by TYPO3 Install Tool. 
$TYPO3_CONF_VARS['GFX']['im_v5effects'] = '1';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS['BE']['installToolPassword'] = '113d3b23feb258c5a94c3663d269ce7a';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS['BE']['forceCharset'] = 'utf-8';	//  Modified or inserted by TYPO3 Install Tool.
// Updated by TYPO3 Install Tool 31-05-2006 18:48:19
$TYPO3_CONF_VARS['EXT']['extConf']['tt_news'] = 'a:14:{s:13:"useStoragePid";s:1:"1";s:13:"noTabDividers";s:1:"0";s:25:"l10n_mode_prefixLangTitle";s:1:"1";s:22:"l10n_mode_imageExclude";s:1:"1";s:20:"hideNewLocalizations";s:1:"0";s:13:"prependAtCopy";s:1:"1";s:5:"label";s:5:"title";s:9:"label_alt";s:0:"";s:10:"label_alt2";s:0:"";s:15:"label_alt_force";s:1:"0";s:11:"treeOrderBy";s:3:"uid";s:21:"categorySelectedWidth";s:1:"0";s:17:"categoryTreeWidth";s:1:"0";s:18:"categoryTreeHeigth";s:1:"5";}';	//  Modified or inserted by TYPO3 Extension Manager.
$TYPO3_CONF_VARS['EXT']['extList'] = 'extbase,css_styled_content,tsconfig_help,context_help,extra_page_cm_options,impexp,sys_note,tstemplate,tstemplate_ceditor,tstemplate_info,tstemplate_objbrowser,tstemplate_analyzer,func_wizards,wizard_crpages,wizard_sortpages,lowlevel,install,belog,beuser,aboutmodules,setup,taskcenter,info_pagetsconfig,viewpage,rtehtmlarea,tt_news,q4u_contentparser,page_php_content,realurl,q4u_glossar,q4u_search,jb_realurl_regeneration,realurlmanagement,pmktextarea,t3quixplorer,t3skin,t3editor,cshmanual,version,reports,about,opendocs,recycler,scheduler,feedit,statictemplates,info,perm,func,filelist,fluid,workspaces';	// Modified or inserted by TYPO3 Extension Manager. Modified or inserted by TYPO3 Install Tool. Modified or inserted by TYPO3 Core Update Manager. 
$TYPO3_CONF_VARS['EXT']['extConf']['skin_grey_2'] = 'a:10:{s:7:"enable.";a:12:{s:13:"TinyMCEStyles";s:1:"0";s:13:"ModuleIconSet";s:8:"standard";s:17:"ModuleIconSetPath";s:0:"";s:19:"styleSheetFile_post";s:0:"";s:14:"ModuleIconSize";s:2:"16";s:27:"allowCollapseExpandNavFrame";s:1:"0";s:15:"stylesheetTheme";s:8:"original";s:25:"disable_default_CSS-files";s:1:"0";s:9:"userSetup";s:1:"0";s:14:"ownCSSforUsers";s:1:"0";s:19:"shortcutframe_ontop";s:1:"0";s:10:"newTopmenu";s:1:"0";}s:7:"onClick";s:1:"0";s:17:"defaultMainModule";s:3:"web";s:14:"saveModuleInfo";s:1:"0";s:14:"styleSheetFile";s:0:"";s:11:"maxSubItems";s:1:"6";s:10:"leftOffset";s:2:"45";s:14:"subLabelLenght";s:1:"0";s:18:"dims_leftmenuframe";s:3:"150";s:13:"dims_navframe";s:3:"250";}';       // Modified or inserted by TYPO3 Extension Manager. 
$TYPO3_CONF_VARS['EXT']['extConf']['q4u_contentparser'] = 'a:6:{s:8:"emFactor";s:1:"3";s:12:"modifyAnchor";s:1:"0";s:11:"clearBorder";s:1:"1";s:13:"htmlCharacter";s:1:"0";s:16:"specialHighlight";s:1:"0";s:10:"linkLayout";s:1:"1";}';	// Modified or inserted by TYPO3 Extension Manager. 
// Updated by TYPO3 Extension Manager 28-06-2006 12:37:19

$TYPO3_CONF_VARS['EXTCONF']['realurl'] = array(
	'_DEFAULT' => array(
		'init' => array(
			'enableCHashCache' => 1,
			'appendMissingSlash' => 'ifNotFile',
			'enableUrlDecodeCache' => 1,
			'enableUrlEncodeCache' => 1,
		),
		'redirects' => array(),
		'preVars' => array(
			array(
				'GETvar' => 'no_cache',
				'valueMap' => array(
					'nc' => 1,
				),
				'noMatch' => 'bypass',
			),
			array(
				'GETvar' => 'L',
				'valueMap' => array(
					'en' => '1',
				),
				'noMatch' => 'bypass',
			),
		),
		'pagePath' => array(
			'type' => 'user',
			'userFunc' => 'EXT:realurl/class.tx_realurl_advanced.php:&tx_realurl_advanced->main',
			'spaceCharacter' => '-',
			'languageGetVar' => 'L',
			'expireDays' => 7,
###### include your rootpage id here
			'rootpage_id' => 1,
		),
		'fixedPostVars' => array(),
		'postVarSets' => array(
			'_DEFAULT' => array(),
		),
		// configure filenames for different pagetypes
		'fileName' => array(
		  'defaultToHTMLsuffixOnPrev' => 1,
		),
	),
);

$TYPO3_CONF_VARS['EXT']['extConf']['rtehtmlarea'] = 'a:14:{s:21:"noSpellCheckLanguages";s:23:"ja,km,ko,lo,th,zh,b5,gb";s:15:"AspellDirectory";s:15:"/usr/bin/aspell";s:17:"defaultDictionary";s:2:"en";s:14:"dictionaryList";s:2:"en";s:18:"HTMLAreaPluginList";s:198:"TableOperations, SpellChecker, ContextMenu, SelectColor, TYPO3Browsers, InsertSmiley, FindReplace, RemoveFormat, CharacterMap, QuickTag, InlineCSS, DynamicCSS, UserElements, Acronym, TYPO3HtmlParser";s:20:"defaultConfiguration";s:105:"Typical (Most commonly used features are enabled. Select this option if you are unsure which one to use.)";s:12:"enableImages";s:1:"1";s:22:"enableMozillaExtension";s:1:"1";s:16:"forceCommandMode";s:1:"0";s:15:"enableDebugMode";s:1:"0";s:23:"enableCompressedScripts";s:1:"0";s:20:"mozAllowClipboardUrl";s:114:"http://releases.mozilla.org/pub/mozilla.org/extensions/allowclipboard_helper/allowclipboard_helper-0.5.3-fx+mz.xpi";s:18:"plainImageMaxWidth";s:3:"640";s:19:"plainImageMaxHeight";s:3:"680";}';       // Modified or inserted by TYPO3 Extension Manager.releases.mozilla.org/pub/mozilla.org/extensions/allowclipboard_helper/allowclipboard_helper-0.5.3-fx+mz.xpi";s:18:"plainImageMaxWidth";s:3:"640";s:19:"plainImageMaxHeight";s:3:"680";}';       //releases.mozilla.org/pub/mozilla.org/extensions/allowclipboard_helper/allowclipboard_helper-0.5.3-fx+mz.xpi";s:18:"plainImageMaxWidth";s:3:"640";s:19:"plainImageMaxHeight";s:3:"680";}';       //releases.mozilla.org/pub/mozilla.org/extensions/allowclipboard_helper/allowclipboard_helper-0.5.3-fx+mz.xpi";s:18:"plainImageMaxWidth";s:3:"640";s:19:"plainImageMaxHeight";s:3:"680";}';       //releases.mozilla.org/pub/mozilla.org/extensions/allowclipboard_helper/allowclipboard_helper-0.5.3-fx+mz.xpi";s:18:"plainImageMaxWidth";s:3:"640";s:19:"plainImageMaxHeight";s:3:"680";}';       //releases.mozilla.org/pub/mozilla.org/extensions/allowclipboard_helper/allowclipboard_helper-0.5.3-fx+mz.xpi";s:18:"plainImageMaxWidth";s:3:"640";s:19:"plainImageMaxHeight";s:3:"680";}';       //releases.mozilla.org/pub/mozilla.org/extensions/allowclipboard_helper/allowclipboard_helper-0.5.3-fx+mz.xpi";s:18:"plainImageMaxWidth";s:3:"640";s:19:"plainImageMaxHeight";s:3:"680";}';	// 
$TYPO3_CONF_VARS['EXT']['extConf']['rlmp_tmplselector'] = 'a:1:{s:12:"templateMode";s:4:"file";}';       // Modified or inserted by TYPO3 Extension Manager. 
$TYPO3_CONF_VARS['EXT']['extConf']['date2cal'] = 'a:4:{s:11:"calendarCSS";s:22:"calendar-skingrey2.css";s:7:"doCache";s:1:"1";s:8:"datetime";s:1:"1";s:11:"calendarImg";s:0:"";}';	//  Modified or inserted by TYPO3 Extension Manager.
$TYPO3_CONF_VARS['EXT']['extConf']['pmktextarea'] = 'a:27:{s:12:"linenumState";s:1:"1";s:9:"wrapState";s:1:"1";s:11:"showButtons";s:1:"1";s:16:"showMinMaxButton";s:1:"1";s:14:"showWrapButton";s:1:"1";s:17:"showLinenumButton";s:1:"1";s:14:"showFindButton";s:1:"1";s:14:"showJumpButton";s:1:"1";s:15:"showFontButtons";s:1:"1";s:11:"typo3Colors";s:1:"1";s:15:"linenumColWidth";s:2:"47";s:15:"defaultFontSize";s:1:"9";s:13:"defaultHeight";s:1:"0";s:12:"defaultWidth";s:1:"0";s:9:"minHeight";s:1:"0";s:9:"maxHeight";s:1:"0";s:8:"minWidth";s:1:"0";s:8:"maxWidth";s:1:"0";s:5:"lockH";s:1:"0";s:5:"lockW";s:1:"0";s:9:"backColor";s:7:"#C8C8CD";s:11:"borderColor";s:7:"#808080";s:11:"backColorLn";s:7:"#F0F0F0";s:13:"borderColorLn";s:7:"#96969B";s:11:"textColorLn";s:7:"#808080";s:7:"tabChar";s:7:"Tabchar";s:10:"buttonPath";s:0:"";}';	//  Modified or inserted by TYPO3 Extension Manager.
$TYPO3_CONF_VARS['EXT']['extConf']['t3quixplorer'] = 'a:7:{s:9:"no_access";s:5:"^\\.ht";s:11:"show_hidden";s:1:"1";s:8:"home_url";s:0:"";s:8:"home_dir";s:0:"";s:11:"show_thumbs";s:1:"1";s:15:"textarea_height";s:2:"30";s:12:"editable_ext";s:215:"\\.phpcron$|\\.ts$|\\.tmpl$|\\.txt$|\\.php$|\\.php3$|\\.phtml$|\\.inc$|\\.sql$|\\.pl$|\\.htm$|\\.html$|\\.shtml$|\\.dhtml$|\\.xml$|\\.js$|\\.css$|\\.cgi$|\\.cpp$\\.c$|\\.cc$|\\.cxx$|\\.hpp$|\\.h$|\\.pas$|\\.p$|\\.java$|\\.py$|\\.sh$\\.tcl$|\\.tk$";}';	//  Modified or inserted by TYPO3 Extension Manager.
// Updated by TYPO3 Extension Manager 15-04-2008 14:26:00
$TYPO3_CONF_VARS['SYS']['compat_version'] = '4.5';	// Modified or inserted by TYPO3 Install Tool. 
// Updated by TYPO3 Install Tool 13-02-09 17:09:04
$TYPO3_CONF_VARS['EXT']['extList_FE'] = 'extbase,css_styled_content,install,rtehtmlarea,tt_news,q4u_contentparser,page_php_content,realurl,q4u_glossar,q4u_search,jb_realurl_regeneration,realurlmanagement,pmktextarea,t3quixplorer,t3skin,version,feedit,statictemplates,fluid,workspaces';	// Modified or inserted by TYPO3 Extension Manager. 
// Updated by TYPO3 Extension Manager 07-04-10 11:07:04
$TYPO3_CONF_VARS['BE']['disable_exec_function'] = '0';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS['GFX']['gdlib_png'] = '0';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS['GFX']['TTFdpi'] = '96';	//  Modified or inserted by TYPO3 Install Tool.
// Updated by TYPO3 Install Tool 07-04-10 11:15:33
// Updated by TYPO3 Core Update Manager 07-04-10 11:16:54
// Updated by TYPO3 Extension Manager 12-10-10 16:18:55
// Updated by TYPO3 Install Tool 12-10-10 16:29:52
// Updated by TYPO3 Core Update Manager 12-10-10 16:30:01
$TYPO3_CONF_VARS['SYS']['enableDeprecationLog'] = '';	// Modified or inserted by TYPO3 Install Tool. 
$TYPO3_CONF_VARS['BE']['maxFileSize'] = '20480';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS['BE']['allowDonateWindow'] = '0';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS['SYS']['displayErrors'] = '0';
// Updated by TYPO3 Install Tool 01-12-10 14:08:29
// Updated by TYPO3 Extension Manager 21-04-11 11:23:38
$TYPO3_CONF_VARS['SYS']['systemLogLevel'] = '3';	// Modified or inserted by TYPO3 Install Tool. 
$TYPO3_CONF_VARS['SYS']['errorHandlerErrors'] = '22519';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS['SYS']['exceptionalErrors'] = '20725';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS['SYS']['syslogErrorReporting'] = '22519';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS['SYS']['belogErrorReporting'] = '22519';	//  Modified or inserted by TYPO3 Install Tool.
$TYPO3_CONF_VARS['SYS']['setDBinit'] = '';	//  Modified or inserted by TYPO3 Install Tool.
// Updated by TYPO3 Install Tool 01-10-12 16:30:47
// Updated by TYPO3 Extension Manager 01-10-12 16:31:01
$TYPO3_CONF_VARS['INSTALL']['wizardDone']['tx_coreupdates_installsysexts'] = '1';	//  Modified or inserted by TYPO3 Upgrade Wizard.
// Updated by TYPO3 Upgrade Wizard 01-10-12 16:31:01
// Updated by TYPO3 Extension Manager 01-10-12 16:31:08
$TYPO3_CONF_VARS['BE']['versionNumberInFilename'] = '0';	//  Modified or inserted by TYPO3 Install Tool.
// Updated by TYPO3 Install Tool 01-10-12 16:46:56
?>
