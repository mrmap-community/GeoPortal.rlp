<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

/********************* installed plugins related conditions starts ************/
// if 'tm_shared_lib' hast not been installed, disable features
// these settings needs 'tm_shared_lib' to be installed before 'skin_grey_2'
if(!$tm_shared_lib) {	
	if(!$skin_grey_2Conf)
		$skin_grey_2Conf = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['skin_grey_2']);
	$skin_grey_2Conf['enable.']['TinyMCEStyles']=false;
	$skin_grey_2Conf['enable.']['ModuleIconSet']='standard'; // this could not be needed if not used XCLASS
			
	if (t3lib_div::int_from_ver(TYPO3_version) >= 4001000)
		$Typo4_1=1;
	if (t3lib_div::int_from_ver(TYPO3_version) >= 4000000)
		$Typo4=1;
	}
/********************* installed plugins related conditions ends **************/




if (TYPO3_MODE=='BE')	{

	$presetSkinImgs = is_array($TBE_STYLES['skinImg']) ? $TBE_STYLES['skinImg'] : array();	// Means, support for other extensions to add own icons...

	if(!isset($skin_grey_2Conf['enable.']['stylesheetTheme'])) // needs when updating this plugin
		$theme='original';
	else	$theme=$skin_grey_2Conf['enable.']['stylesheetTheme'];
	
	if(!$Typo4 && $skin_grey_2Conf['enable.']['stylesheetTheme']=='t3skin')
		$skin_grey_2Conf['enable.']['stylesheetTheme']='modern_skin';	
	
	if($skin_grey_2Conf['enable.']['stylesheetTheme']=='modern_skin' && !$tm_contentaccess)
		$skin_grey_2Conf['enable.']['IconSet']='modern_skin';
	
	if(!isset($skin_grey_2Conf['enable.']['IconSet']) || $skin_grey_2Conf['enable.']['IconSet']=='default')  // needs when updating this plugin
		$icons1='icons/';
	if($skin_grey_2Conf['enable.']['IconSet']=='modern_skin')
		$icons1='icons_modern/';
	elseif($skin_grey_2Conf['enable.']['IconSet']=='older_style')
		$icons1='icons_old/';		
	$icons=$icons1; // basic icon set
	
	if($skin_grey_2Conf['enable.']['stylesheetTheme']=='modern_skin')
		$skin_grey_2Conf['enable.']['TinyMCEStyles']=false;
	/**
	 * Setting up backend styles and colors
	*/
	  
/*	$TBE_STYLES = array( 
		
			# DEPRECIATED: // not used
#		'background' => t3lib_extMgm::extRelPath($_EXTKEY).'img/background.gif',	// Background image generally in the backend
#		'logo' => t3lib_extMgm::extRelPath($_EXTKEY).'img/the_logo_image.gif',	// Logo in alternative backend, top left: 129x32 pixels
#		'logo_login' => t3lib_extMgm::extRelPath($_EXTKEY).'img/login_logo_image.gif'	// Login-logo: 333x63 pixels
	);
*/
$TBE_STYLES['mainColors'] = array(	// Always use #xxxxxx color definitions!
			'bgColor' => '#F4F4F4',		
			'bgColor2' => '#F2F2F2',	
			'bgColor3' => '#EEEEEE',	
			'bgColor4' => '#F2F2F2',		
			'bgColor5' => '#F9F9F9',
			'bgColor6' => '#ABABAB',
			
	);

$TBE_STYLES['colorschemes'][0]='-|class-main1,-|class-main2,-|class-main3,-|class-main4,-|class-main5';
$TBE_STYLES['colorschemes'][1]='-|class-main11,-|class-main12,-|class-main13,-|class-main14,-|class-main15';
$TBE_STYLES['colorschemes'][2]='-|class-main21,-|class-main22,-|class-main23,-|class-main24,-|class-main25';
$TBE_STYLES['colorschemes'][3]='-|class-main31,-|class-main32,-|class-main33,-|class-main34,-|class-main35';
$TBE_STYLES['colorschemes'][4]='-|class-main41,-|class-main42,-|class-main43,-|class-main44,-|class-main45';
$TBE_STYLES['colorschemes'][5]='-|class-main51,-|class-main52,-|class-main53,-|class-main54,-|class-main55';

$TBE_STYLES['styleschemes'][0]['all'] = 'CLASS: formField';
$TBE_STYLES['styleschemes'][1]['all'] = 'CLASS: formField1';
$TBE_STYLES['styleschemes'][2]['all'] = 'CLASS: formField2';
$TBE_STYLES['styleschemes'][3]['all'] = 'CLASS: formField3';
$TBE_STYLES['styleschemes'][4]['all'] = 'CLASS: formField4';
$TBE_STYLES['styleschemes'][5]['all'] = 'CLASS: formField5';

$TBE_STYLES['styleschemes'][0]['check'] = 'CLASS: checkbox';
$TBE_STYLES['styleschemes'][1]['check'] = 'CLASS: checkbox';
$TBE_STYLES['styleschemes'][2]['check'] = 'CLASS: checkbox';
$TBE_STYLES['styleschemes'][3]['check'] = 'CLASS: checkbox';
$TBE_STYLES['styleschemes'][4]['check'] = 'CLASS: checkbox';
$TBE_STYLES['styleschemes'][5]['check'] = 'CLASS: checkbox';

$TBE_STYLES['styleschemes'][0]['radio'] = 'CLASS: radio';
$TBE_STYLES['styleschemes'][1]['radio'] = 'CLASS: radio';
$TBE_STYLES['styleschemes'][2]['radio'] = 'CLASS: radio';
$TBE_STYLES['styleschemes'][3]['radio'] = 'CLASS: radio';
$TBE_STYLES['styleschemes'][4]['radio'] = 'CLASS: radio';
$TBE_STYLES['styleschemes'][5]['radio'] = 'CLASS: radio';

$TBE_STYLES['styleschemes'][0]['select'] = 'CLASS: select';
$TBE_STYLES['styleschemes'][1]['select'] = 'CLASS: select';
$TBE_STYLES['styleschemes'][2]['select'] = 'CLASS: select';
$TBE_STYLES['styleschemes'][3]['select'] = 'CLASS: select';
$TBE_STYLES['styleschemes'][4]['select'] = 'CLASS: select';
$TBE_STYLES['styleschemes'][5]['select'] = 'CLASS: select';

$TBE_STYLES['borderschemes'][0]= array('','','','wrapperTable');
$TBE_STYLES['borderschemes'][1]= array('','','','wrapperTable1');
$TBE_STYLES['borderschemes'][2]= array('','','','wrapperTable2');
$TBE_STYLES['borderschemes'][3]= array('','','','wrapperTable3');
$TBE_STYLES['borderschemes'][4]= array('','','','wrapperTable4');
$TBE_STYLES['borderschemes'][5]= array('','','','wrapperTable5');



	// Setting the relative path to the extension in temp. variable:	
$temp_eP = t3lib_extMgm::extRelPath($_EXTKEY);

	// Setting login box image rotation folder:
$TBE_STYLES['loginBoxImage_rotationFolder'] = $temp_eP.'loginimages/';

$stylesheetFolder=$temp_eP.'stylesheets/';

	// Setting up stylesheets (See template() constructor!)
	// Alternative stylesheet to the default "typo3/stylesheet.css" stylesheet.
if($skin_grey_2Conf['enable.']['own_CSS_file']=='replace_all')
	$TBE_STYLES['stylesheet'] = $stylesheetFolder.'empty.css'; // if not set used default CSS-file - that's why an empty CSS-file used	
else	$TBE_STYLES['stylesheet'] = $stylesheetFolder.'stylesheet_basic_'.$theme.'.css';

// Additional stylesheet (not used by default).  Set BEFORE any in-document styles
	
if($skin_grey_2Conf['enable.']['own_CSS_file']=='replace_all')
	$TBE_STYLES['stylesheet2'] = '';
elseif($skin_grey_2Conf['enable.']['TinyMCEStyles'])
	$TBE_STYLES['stylesheet2'] = $stylesheetFolder.'TinyMCEStyles_'.$theme.'.css';
else	$TBE_STYLES['stylesheet2'] = $stylesheetFolder.'normalStyles_'.$theme.'.css';

	// Additional stylesheet. Set AFTER any in-document styles and additional default in-document styles.
if ($skin_grey_2Conf['enable.']['styleSheetFile_post'] && $skin_grey_2Conf['enable.']['own_CSS_file']=='replace_stylesheet_post')
	$TBE_STYLES['styleSheetFile_post'] = $skin_grey_2Conf['enable.']['styleSheetFile_post'];
elseif($skin_grey_2Conf['enable.']['own_CSS_file']!='replace_all') {
	$TBE_STYLES['styleSheetFile_post'] = $stylesheetFolder.'stylesheet_post_'.$theme.'.css';
		// Additional default in-document styles.
	if($skin_grey_2Conf['enable.']['stylesheetTheme']=='modern_skin' || $skin_grey_2Conf['enable.']['stylesheetTheme']=='t3skin') {
		if($skin_grey_2Conf['enable.']['stylesheetTheme']=='modern_skin')
			$stylesheetFolder = t3lib_div::getIndpEnv('TYPO3_SITE_URL').t3lib_extMgm::siteRelPath('skin_grey_2').'stylesheets_modern_skin/';
		else	$stylesheetFolder = t3lib_div::getIndpEnv('TYPO3_SITE_URL').'typo3/sysext/t3skin/stylesheets/';
		if($Typo4_2) {
			$TBE_STYLES['stylesheets']['modulemenu'] = $stylesheetFolder.'modulemenu.css';
			$TBE_STYLES['stylesheets']['backend-style'] = $stylesheetFolder.'backend-style.css';
			}
		}
	}
else	{ // replace all CSS files
	$TBE_STYLES['styleSheetFile_post'] = $skin_grey_2Conf['enable.']['styleSheetFile_post'];
	if($Typo4_2) {
		$TBE_STYLES['stylesheets']['modulemenu']   = '';
		$TBE_STYLES['stylesheets']['backend-style']  = '';
		}
}

	// Additional default in-document styles
if ($skin_grey_2Conf['enable.']['ModuleIconSet']=='skin_grey')
	$size=14;
elseif ($skin_grey_2Conf['enable.']['ModuleIconSet']=='standard' || $skin_grey_2Conf['enable.']['ModuleIconSet']=='win_xp' || $skin_grey_2Conf['enable.']['ModuleIconSet']=='modern_skin')
	$size=16;
elseif (is_numeric($skin_grey_2Conf['enable.']['ModuleIconSize']))
	$size=intval($skin_grey_2Conf['enable.']['ModuleIconSize']);
else	$size=16;

	// find from a file selected basic button set related CSS (dimensions and background properties)
if($skin_grey_2Conf['enable.']['own_CSS_file']!='replace_all') {	
	if($skin_grey_2Conf['enable.']['IconSet']=='modern_skin')
		$iconSetCSS=t3lib_div::getURL(PATH_site .substr(t3lib_extMgm::extRelPath('skin_grey_2'),3).'stylesheets/stylesheet_modern_skin.css');
	elseif($skin_grey_2Conf['enable.']['stylesheetTheme']!='modern_skin')
		$iconSetCSS=t3lib_div::getURL(PATH_site .substr(t3lib_extMgm::extRelPath('skin_grey_2'),3).'stylesheets/stylesheet_skin_grey_2.css');
	
	if($skin_grey_2Conf['enable.']['stylesheetTheme']=='modern_skin' && $tm_contentaccess && $skin_grey_2Conf['enable.']['IconSet']!='modern_skin')
		$iconSetCSS .='
a.fullform img, td.reloadIcon span, td.noactiveClassSave span, #save_top, #save_bottom {background: transparent url('.t3lib_div::getIndpEnv('TYPO3_SITE_URL') .substr(t3lib_extMgm::extRelPath('skin_grey_2'),3).'backgrounds/buttonbase.gif) no-repeat 50% 50% !important; }
.closedok-be,.closedok-fe,.saveandclosedok-be,saveandclosedok-fe, .savedoknew-be, .savedokview-be, .savedokview-fe, .savedoknew-fe, .savedok-be, .savedok-fe,
.deletedok-be,.deletedok-fe, .undo-be .undo-fe { 
	background-image: none !important;
	margin:0 !important;
}
		';
	elseif($skin_grey_2Conf['enable.']['IconSet']!='modern_skin')
		$iconSetCSS .='
a.fullform img, td.reloadIcon span,td.noactiveClassSave span, #save_top, #save_bottom {	background: transparent url('.t3lib_div::getIndpEnv('TYPO3_SITE_URL') .substr(t3lib_extMgm::extRelPath('skin_grey_2'),3).'backgrounds/buttonbase.gif) no-repeat 50% 50% !important; }
		';
/*
	if($Typo4_2 && $skin_grey_2Conf['enable.']['IconSet']!='modern_skin')
		$iconSetCSS .='
 .buttonsright img { height:16px; width:16px; } 
		';
*/
//$skin_grey_2Conf['enable.']['stylesheetTheme']=='modern_skin' || 
	if($skin_grey_2Conf['enable.']['stylesheetTheme']=='t3skin')
		$iconSetCSS .='
span.noactiveClass a {
	position:relative;
	top:1px;
}
		';
	elseif($skin_grey_2Conf['enable.']['IconSet']=='modern_skin' && $skin_grey_2Conf['enable.']['stylesheetTheme']!='modern_skin')
		$iconSetCSS .='
#save_top, #save_bottom { background-image: none !important }		';
	$TBE_STYLES['inDocStyles_TBEstyle'] .= '
	
	/* selected basic button set related CSS (positioinings, dimensions and background properties) */
	
	'
.$iconSetCSS.'
	';
	if($skin_grey_2Conf['enable.']['stylesheetTheme']=='t3skin') {
		if($Typo4_1)
			$image='loginimage_4_1.jpg';
		elseif($Typo4)	
			$image='one.jpg';		
		$TBE_STYLES['inDocStyles_TBEstyle'] .= '
	body#typo3-index-php DIV#loginimage { background: transparent url('.t3lib_div::getIndpEnv('TYPO3_SITE_URL') .'typo3/sysext/t3skin/images/login/'.$image.') no-repeat 0% 100% !important}
		';
		if($Typo4_1) 
			$TBE_STYLES['inDocStyles_TBEstyle'] .= '
	body#typo3-index-php DIV#loginimage { height:165px !important;	}
			';
		}
		if($Typo4_2) {
			$TBE_STYLES['inDocStyles_TBEstyle'] .= '
	/* take off padding and margin from some pages */
body#ext-cms-layout-db-layout-php, 
body#typo3-alt-doc-php, 
body#typo3-db-list-php, 
body#ext-tstemplate-ts-index-php, 
body#typo3-mod-web-perm-index-php, 
body#typo3-mod-web-info-index-php, 
body#typo3-mod-web-func-index-php, 
body#ext-version-cm1-index-php, 
body#ext-setup-mod-index-php, 
body#typo3-mod-user-ws-index-php, 
body#typo3-mod-user-ws-workspaceforms-php, 
body#typo3-mod-php, 
body#ext-tsconfig-help-mod1-index-php, 
body#typo3-mod-tools-em-index-php, 
body#ext-lowlevel-dbint-index-php, 
body#ext-lowlevel-config-index-php { 
	padding: 0; 
	margin: 0; 
	overflow: auto; 
	height: 100%; 
}
#ext-cms-layout-db-new-content-el-php { overflow:scroll! important; }
#ext-cms-layout-db-new-content-el-php form { margin-left:10px important; }
		';		
		}
		
	if($skin_grey_2Conf['enable.']['styleSheetFile_post'] && $skin_grey_2Conf['enable.']['own_CSS_file']=='add' && is_file(PATH_site .$skin_grey_2Conf['enable.']['styleSheetFile_post'])) {
		$TBE_STYLES['inDocStyles_TBEstyle'] .='
		
	/* add some own CSS from a CSS file start */
		';
		$TBE_STYLES['inDocStyles_TBEstyle'] .= t3lib_div::getURL(PATH_site .$skin_grey_2Conf['enable.']['styleSheetFile_post']);
		$TBE_STYLES['inDocStyles_TBEstyle'] .='
	/* add some own CSS from a CSS file end */
	
		';
		}		
	}
	
	$TBE_STYLES['inDocStyles_TBEstyle'] .= '
	/* different kind of module menus depending on module icon settings */
	/* traditional "Icons in topframe" and  "Left frame menu" */
	';
	if($skin_grey_2Conf['enable.']['ModuleIconSet']=='win_xp' || $skin_grey_2Conf['enable.']['ModuleIconSet']=='standard' || $skin_grey_2Conf['enable.']['ModuleIconSet']=='skin_grey' || $skin_grey_2Conf['enable.']['ModuleIconSet']=='modern_skin')
		$TBE_STYLES['inDocStyles_TBEstyle'] .= '
	#typo3-alt-menu-php .c-mainitem img,
	#typo3-alt-intro-php .c-mainitem img {
		height:16px !important; 
		width:16px !important; 
	}';
	$TBE_STYLES['inDocStyles_TBEstyle'] .= '
	#typo3-alt-intro-php .c-subitem-row img,
	TABLE#typo3-topMenu TR TD.c-menu A IMG, 
	TABLE#typo3-vmenu TR.c-subitem-row TD IMG,
	TABLE#typo3-vmenu TR.c-subitem-row-HL TD IMG {
		height:'.$size.'px !important; 
		width:'.$size.'px !important; 
	}
	#typo3-alt-menu-php .c-mainitem .c-iconCollapse img { /* must set fixed values */
		height:16px !important; 
		width:18px !important
	}
	/* the definition below needs tm_contentaccess with enable.topmenu_spacers = 1 and icons in the top frame */
	.acm_spacer {	
		height:'.($size-2).'px !important; 
		width:8px !important;
	} 
	/* new leftside menu */
	.menuSection ul li img {
		height:'.$size.'px !important; 
		width:'.$size.'px !important; 
	}
	/* alternative alt_intro.php used with "modern_skin" CSS theme */
	.about-icon-image img {
		height:'.$size.'px !important; 
		width:'.$size.'px !important; 
	}
	/* tm_topapps module menu */
	.menuItems img.menulayerItemIcon {
		height:'.$size.'px !important; 
		width:'.$size.'px !important; 
	}
	';	

		// Alternative dimensions for frameset sizes:
	$TBE_STYLES['dims']['topFrameH']=41;			// Top frame heigth
	$TBE_STYLES['dims']['shortcutFrameH']=25;		// Shortcut frame height
	$TBE_STYLES['dims']['selMenuFrame']=180;		// Width of the selector box menu frame
	$TBE_STYLES['dims']['leftMenuFrameW'] = ($skin_grey_2Conf['dims_leftmenuframe']) ? $skin_grey_2Conf['dims_leftmenuframe'] : false;
	$TBE_STYLES['dims']['navFrameWidth'] = ($skin_grey_2Conf['dims_navframe']) ? $skin_grey_2Conf['dims_navframe'] : false;
	
		// Setting roll-over background color for click menus:
		// Notice, this line uses the the 'scriptIDindex' feature to override another value in this array (namely $TBE_STYLES['mainColors']['bgColor5']), for a specific script "typo3/alt_clickmenu.php"
	$TBE_STYLES['scriptIDindex']['typo3/alt_clickmenu.php']['mainColors']['bgColor5']='#E8F0F5';

		// Setting up auto detection of alternative icons:
	$TBE_STYLES['skinImgAutoCfg']=array(
		'absDir' => t3lib_extMgm::extPath($_EXTKEY).$icons.'',
		'relDir' => t3lib_extMgm::extRelPath($_EXTKEY).$icons.'',
		'forceFileExtension' => 'gif',	// Force to look for PNG alternatives...
		#'scaleFactor' => 2/3,	// Scaling factor, default is 1
	);

	if($skin_grey_2Conf['enable.']['ModuleIconSet']!='standard')
		$icons='icons/'; // set base on the default folder
	/*
	if($skin_grey_2Conf['enable.']['ModuleIconSet']=='win_xp')
		$icons='win_xp_moduleicons/';	
	elseif($skin_grey_2Conf['enable.']['ModuleIconSet']=='skin_grey')
		$icons='skin_grey_moduleicons/';
	*/
		// Manual setting up of alternative icons. This is mainly for module icons which has a special prefix:
	$TBE_STYLES['skinImg'] = array_merge($presetSkinImgs, array (
		'gfx/ol/blank.gif' => array('clear.gif','width="18" height="16"'),		
		'gfx/minimize.frame.gif' => array($temp_eP.'backgrounds/minimize.frame.gif','width="10" height="200"'),
		'gfx/maximize.frame.gif' => array($temp_eP.'backgrounds/maximize.frame.gif','width="10" height="200"'),
		'gfx/minimize.frame.over.gif' => array($temp_eP.'backgrounds/minimize.frame.over.gif','width="10" height="200"'),
		'gfx/maximize.frame.over.gif' => array($temp_eP.'backgrounds/maximize.frame.over.gif','width="10" height="200"'),
		
		'MOD:web/website.gif'  => array($temp_eP.$icons.'module_web.gif',''),
		'MOD:web_layout/layout.gif'  => array($temp_eP.$icons.'module_web_layout.gif','width="'.$size.'" height="'.$size.'"'),
		'MOD:web_view/view.gif'  => array($temp_eP.$icons.'module_web_view.gif','width="'.$size.'" height="'.$size.'"'),
		'MOD:web_list/list.gif'  => array($temp_eP.$icons.'module_web_list.gif','width="'.$size.'" height="'.$size.'"'),
		'MOD:web_info/info.gif'  => array($temp_eP.$icons.'module_web_info.gif','width="'.$size.'" height="'.$size.'"'),
		'MOD:web_perm/perm.gif'  => array($temp_eP.$icons.'module_web_perms.gif','width="'.$size.'" height="'.$size.'"'),
		'MOD:web_perm/legend.gif'  => array($temp_eP.$icons.'legend.gif','width="'.$size.'" height="'.$size.'"'),
		'MOD:web_func/func.gif'  => array($temp_eP.$icons.'module_web_func.gif','width="'.$size.'" height="'.$size.'"'),
		'MOD:web_ts/ts1.gif'  => array($temp_eP.$icons.'module_web_ts.gif','width="'.$size.'" height="'.$size.'"'),
		'MOD:web_modules/modules.gif' => array($temp_eP.$icons.'module_web_modules.gif','width="'.$size.'" height="'.$size.'"'),
		'MOD:file/file.gif'  => array($temp_eP.$icons.'module_file.gif','width="'.$size.'" height="'.$size.'"'),
		'MOD:file_list/list.gif'  => array($temp_eP.$icons.'module_file_list.gif','width="'.$size.'" height="'.$size.'"'),
		'MOD:file_images/images.gif'  => array($temp_eP.$icons.'module_file_images.gif','width="'.$size.'" height="'.$size.'"'),
		'MOD:doc/document.gif'  => array($temp_eP.$icons.'module_doc.gif','width="'.$size.'" height="'.$size.'"'),
		'MOD:user/user.gif'  => array($temp_eP.$icons.'module_user.gif','width="'.$size.'" height="'.$size.'"'),
		'MOD:user_task/task.gif'  => array($temp_eP.$icons.'module_user_taskcenter.gif','width="'.$size.'" height="'.$size.'"'),
		'MOD:user_setup/setup.gif'  => array($temp_eP.$icons.'module_user_setup.gif','width="'.$size.'"height="'.$size.'"'),
		'MOD:tools/tool.gif'  => array($temp_eP.$icons.'module_tools.gif','width="'.$size.'" height="'.$size.'"'),
		'MOD:tools_beuser/beuser.gif'  => array($temp_eP.$icons.'module_tools_user.gif','width="'.$size.'" height="'.$size.'"'),
		'MOD:tools_em/em.gif'  => array($temp_eP.$icons.'module_tools_em.gif','width="'.$size.'" height="'.$size.'"'),
		'MOD:tools_em/install.gif'  => array($temp_eP.$icons.'module_tools_em.gif','width="'.$ize.'" height="'.$size.'"'),
		'MOD:tools_dbint/db.gif'  => array($temp_eP.$icons.'module_tools_dbint.gif','width="'.$size.'" height="'.$size.'"'),
		'MOD:tools_config/config.gif'  => array($temp_eP.$icons.'module_tools_config.gif','width="'.$size.'" height="'.$size.'"'),
		'MOD:tools_install/install.gif'  => array($temp_eP.$icons.'module_tools_install.gif','width="'.$size.'" height="'.$size.'"'),
		'MOD:tools_log/log.gif'  => array($temp_eP.$icons.'module_tools_log.gif','width="'.$size.'" height="'.$size.'"'),
		'MOD:tools_txphpmyadmin/thirdparty_db.gif'  => array($temp_eP.$icons.'module_tools_phpmyadmin.gif','width="'.$size.'" height="'.$size.'"'),
		'MOD:tools_isearch/isearch.gif' => array($temp_eP.$icons.'module_tools_isearch.gif','width="'.$size.'" height="'.$size.'"'),
		'MOD:help/help.gif'  => array($temp_eP.$icons.'module_help.gif','width="'.$size.'" height="'.$size.'"'),
		'MOD:help_about/info.gif'  => array($temp_eP.$icons.'module_help_about.gif','width="'.$size.'" height="'.$size.'"'),
		'MOD:help_aboutmodules/aboutmodules.gif'  => array($temp_eP.$icons.'module_help_aboutmodules.gif','width="'.$size.'" height="'.$size.'"'),
		'MOD:help_cshmanual/ext_icon.gif'  => array($temp_eP.$icons.'module_help_cshmanual.gif','width="'.$size.'" height="'.$size.'"'),
	));
	if($skin_grey_2Conf['enable.']['IconSet']=='modern_skin') {
		$temp_eP_modern=$temp_eP.'icons_modern/';
		$TBE_STYLES['skinImg'] = array_merge($TBE_STYLES['skinImg'], array (
		'MOD:web_txversionM1/cm_icon.gif'  => array($temp_eP_modern.'module_web_version.gif','width="'.$size.'" height="'.$size.'"'),
		'MOD:user_ws/sys_workspace.png'  => array($temp_eP_modern.'module_user_workspace.gif','width="'.$size.'" height="'.$size.'"'),
		'MOD:tools_txextdevevalM1/moduleicon.gif'  => array($temp_eP_modern.'module_tools_extdeval.gif','width="'.$size.'" height="'.$size.'"'),
		'MOD:help_txtsconfighelpM1/moduleicon.gif'  => array($temp_eP_modern.'module_help_typoscript.gif','width="'.$size.'" height="'.$size.'"'),
		));
		}
	/*
	if (t3lib_extMgm::isloaded('impexp') || t3lib_extMgm::isloaded('taskcenter'))	{
		$TBE_STYLES['skinImg']['EXT:impext/export.gif.gif'] = array($temp_eP.$icons.'ext/impexp/export.gif','');
	}
	if (t3lib_extMgm::isloaded('sys_notepad') || t3lib_extMgm::isloaded('taskcenter'))	{
		$TBE_STYLES['skinImg']['EXT:sys_notepad/ext_icon.gif'] = array($temp_eP.$icons.'ext/sys_notepad/ext_icon.gif','');
	}
		
	if (t3lib_extMgm::isloaded('taskcenter') || t3lib_extMgm::isloaded('taskcenter_modules'))	{
		$TBE_STYLES['skinImg']['EXT:taskcenter_recent/ext_icon.gif'] = array($temp_eP.$icons.'ext/taskcenter_recent/ext_icon.gif','');
		$TBE_STYLES['skinImg']['EXT:taskcenter_modules/ext_icon.gif'] = array($temp_eP.$icons.'ext/taskcenter_modules/ext_icon.gif','');
	
	}
	*/
	
	$icons=$icons1;
		// Adding icon for photomarathon extensions' backend module, if enabled:
	if (t3lib_extMgm::isloaded('user_photomarathon'))	{
		$TBE_STYLES['skinImg']['MOD:web_uphotomarathon/tab_icon.gif'] = array($temp_eP.$icons.'ext/user_photomarathon/tab_icon.gif','');
	}
		// Adding icon for templavoila extensions' backend module, if enabled:
	if (t3lib_extMgm::isloaded('templavoila'))	{
		$TBE_STYLES['skinImg']['MOD:web_txtemplavoilaM1/moduleicon.gif'] = array($temp_eP.$icons.'ext/templavoila/mod1/moduleicon.gif','width="'.$size.'" height="'.$size.'"');
		$TBE_STYLES['skinImg']['MOD:web_txtemplavoilaM2/moduleicon.gif'] = array($temp_eP.$icons.'ext/templavoila/mod1/moduleicon.gif','width="'.$size.'" height="'.$size.'"');
	}
	// Adding icon for extension manager' backend module, if enabled:
		$TBE_STYLES['skinImg']['MOD:tools_em/install.gif'] = array($temp_eP.$icons.'ext/templavoila/mod1/moduleicon.gif','');
		$TBE_STYLES['skinImg']['MOD:tools_em/uninstall.gif'] = array($temp_eP.$icons.'ext/templavoila/mod1/moduleicon.gif','');
	
	// Adding icon for dam extensions' backend module, if enabled:
	if (t3lib_extMgm::isloaded('dam')) {
		$TBE_STYLES['skinImg']['MOD:txdamM1/moduleicon.gif'] = array($temp_eP.$icons.'ext/dam/icon_tx_dam.png','');
		$TBE_STYLES['skinImg']['MOD:txdamM1_list/moduleicon.gif'] = array($temp_eP.$icons.'ext/dam/mod_list/moduleicon.png','');
		$TBE_STYLES['skinImg']['MOD:txdamM1_txdamMtools/moduleicon.gif'] = array($temp_eP.$icons.'ext/dam/mod_tools/moduleicon.png','');
	}
	if (t3lib_extMgm::isloaded('dam_info'))	{
		$TBE_STYLES['skinImg']['MOD:txdamM1_txdaminfoM1/moduleicon.gif'] = array($temp_eP.$icons.'ext/dam_info/mod1/moduleicon.png','');
	}
	if (t3lib_extMgm::isloaded('dam_index')) {
		$TBE_STYLES['skinImg']['MOD:txdamM1_txdamindexM1/moduleicon.gif'] = array($temp_eP.$icons.'ext/dam_index/mod1/moduleicon.png','');
	}
	if (t3lib_extMgm::isloaded('dam_file')) {
		$TBE_STYLES['skinImg']['MOD:txdamM1_txdamfileM1/moduleicon.gif'] = array($temp_eP.$icons.'ext/dam_file/mod1/moduleicon.png','');
	}
	if (t3lib_extMgm::isloaded('dam_catedit')) {
		$TBE_STYLES['skinImg']['MOD:txdamM1_txdamcateditM1/moduleicon.gif'] = array($temp_eP.$icons.'ext/dam_catedit/mod1/moduleicon.png','');
	}
}

$temp_eP_icons= $temp_eP.$icons.'gfx/'; // path for skin images

/**
 * Redifination of following tables
 * Backend users - Those who login into the TYPO3 administration backend
 * Backend usergroups - Much permission criterias are based on membership of backend groups.
 * System filemounts - Defines filepaths on the server which can be mounted for users so they can upload and manage files online by eg. the Filelist module
 * System languages - Defines possible languages used for translation of records in the system
 */
 
t3lib_div::loadTCA("be_users");
$TCA['be_users']['columns']['usergroup']['config']['wizards']['edit']['icon']=$temp_eP_icons.'edit2.gif';
$TCA['be_users']['columns']['usergroup']['config']['wizards']['add']['icon']=$temp_eP_icons.'add.gif';
$TCA['be_users']['columns']['usergroup']['config']['wizards']['list']['icon']=$temp_eP_icons.'list.gif';
$TCA['be_users']['columns']['file_mountpoints']['config']['wizards']['edit']['icon']=$temp_eP_icons.'edit2.gif';
$TCA['be_users']['columns']['file_mountpoints']['config']['wizards']['add']['icon']=$temp_eP_icons.'add.gif';
$TCA['be_users']['columns']['file_mountpoints']['config']['wizards']['list']['icon']=$temp_eP_icons.'list.gif';
$TCA['be_users']['columns']['TSconfig']['config']['wizards']['0']['icon']=$temp_eP_icons.'wizard_tsconfig.gif';

t3lib_div::loadTCA("be_groups");
$TCA['be_groups']['columns']['file_mountpoints']['config']['wizards']['edit']['icon']=$temp_eP_icons.'edit2.gif';
$TCA['be_groups']['columns']['file_mountpoints']['config']['wizards']['add']['icon']=$temp_eP_icons.'add.gif';
$TCA['be_groups']['columns']['file_mountpoints']['config']['wizards']['list']['icon']=$temp_eP_icons.'list.gif';
$TCA['be_groups']['columns']['TSconfig']['config']['wizards']['0']['icon']=$temp_eP_icons.'wizard_tsconfig.gif';

t3lib_div::loadTCA("sys_template");
$TCA['sys_template']['columns']['basedOn']['config']['wizards']['edit']['icon']=$temp_eP_icons.'edit2.gif';
$TCA['sys_template']['columns']['basedOn']['config']['wizards']['add']['icon']=$temp_eP_icons.'add.gif';
$TCA['sys_template']['columns']['config']['config']['wizards']['0']['icon']=$temp_eP_icons.'wizard_tsconfig.gif';
?>