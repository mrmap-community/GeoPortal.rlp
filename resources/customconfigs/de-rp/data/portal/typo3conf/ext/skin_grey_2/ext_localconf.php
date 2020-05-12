<?php

if (!defined ("TYPO3_MODE"))     die ("Access denied.");

/********************* installed plugins related conditions starts ************/
// if 'tm_shared_lib' hast not been installed, disable features
// these settings needs 'tm_shared_lib' to be installed before 'skin_grey_2'
if(!$tm_shared_lib) { 
	$skin_grey_2Conf = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['skin_grey_2']);
	$skin_grey_2Conf['enable.']['TinyMCEStyles']=false;
	$skin_grey_2Conf['enable.']['allowCollapseExpandNavFrame']=false;
	$skin_grey_2Conf['enable.']['ModuleIconSet']=='standard';
	$skin_grey_2Conf['enable.']['ownCSSforUsers']=false;
	$skin_grey_2Conf['enable.']['userSetup']=false;
	$skin_grey_2Conf['enable.']['newTopmenu']=false;
	$skin_grey_2Conf['enable.']['shortcutframe_ontop']=false;
	$skin_grey_2Conf['saveModuleInfo']=false;
	}
/********************* installed plugins related conditions ends **************/




/********************* TS Config starts ***************************************/
if(t3lib_extMgm::isLoaded('rtehtmlarea')) 
	t3lib_extMgm::addPageTSConfig('
	RTE.default.skin = EXT:'.$_EXTKEY.'/rtehtmlarea/htmlarea.css
	RTE.default.FE.skin = EXT:'.$_EXTKEY.'/rtehtmlarea/htmlarea.css
	');
if ($tm_shared_lib && $skin_grey_2Conf['enable.']['newTopmenu']) {
	$skinGreyUserTSConfig ='
	# user TS Config added by skin_grey_2
	setup.default.noMenuMode = topmenu
	setup.default.maxSubItems = 6
	setup.default.onClick = 0
	setup.default.defaultMainModule = web
	setup.default.leftoffset = 45
	';
    	t3lib_extMgm::addUserTSConfig($skinGreyUserTSConfig);
	}
/********************* TS Config ends *****************************************/
?>
