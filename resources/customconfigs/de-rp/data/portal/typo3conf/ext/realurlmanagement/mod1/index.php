<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005-2006 Juraj Sulek (juraj@sulek.sk)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Plugin realurl_management.
 *
 * @author	Juraj Sulek <juraj@sulek.sk>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   80: class tx_realurlmanagement_module1 extends t3lib_SCbase
 *  143:     function init()
 *  324:     function setActiveStyleSheet(link, title)
 *  338:     function selected(cal, date)
 *  350:     function closeHandler(cal)
 *  359:     function showCalendar(id, format, showsTime, showsOtherMonths)
 *  399:     function catcalc(cal)
 *  436:     function menuConfig()
 *  505:     function main()
 *  531:     function jumpToUrl(URL)
 *  541:     function submitForm(action)
 *  619:     function printContent()
 *  630:     function moduleContent()
 *
 * TOTAL FUNCTIONS: 12
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */



	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require ("conf.php");
require ($BACK_PATH."init.php");
require ($BACK_PATH."template.php");
$LANG->includeLLFile("EXT:realurlmanagement/mod1/locallang.php");
$LANG->includeLLFile('EXT:lang/locallang_mod_web_perm.xml');
$LANG->includeLLFile('EXT:cms/layout/locallang.xml');


#include ("locallang.php");
require_once (PATH_t3lib."class.t3lib_scbase.php");
require_once (PATH_t3lib.'class.t3lib_pagetree.php');
require_once (PATH_t3lib.'class.t3lib_page.php');
//require_once ('tx_realurlmanagement_setup.php'); //the setup object
require_once ('tx_realurlmanagement_helpfunc.php'); //the help object
require_once ('tx_realurlmanagement_pages.php'); //the pages object
require_once ('tx_realurlmanagement_aliases.php'); //the aliases object
require_once ('tx_realurlmanagement_errors.php'); //the error object
require_once ('tx_realurlmanagement_redirects.php'); //the redirect object
require_once ('tx_realurlmanagement_dbclean.php'); //the dbclean object
require_once ('tx_realurlmanagement_about.php'); //the dbclean object
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]

class tx_realurlmanagement_module1 extends t3lib_SCbase {
	var $pageinfo;
	var $pageuid;
	var	$perms_clause;
	var $tLen=30;//length of pagetitle displayed
	var $spaceArray=array();	//array for spaceCharacters for pageUrls;
	/* images begin */
	var $imageOrderAscInactiv;
	var $imageOrderDescInactiv;
	var $imageOrderAscActiv;
	var $imageOrderDescActiv;
	/* perms begin */
	var $perms_pages_show=false;
	var $perms_pages_delete=false;
	var $perms_pages_deleteShown=false;
	var $perms_pages_expireShown=false;
	var $perms_pages_edit=false;
	var $perms_pages_editWholeURL=false;
	var $perms_pages_expire=false;
	var $perms_pages_create=false;
	/*************/
	var $perms_aliases_show=false;
	var $perms_aliases_edit=false;
	var $perms_aliases_delete=false;
	var $perms_aliases_expire=false;
	var $perms_aliases_create=false;
	/*************/
	var $perms_redirects_show=false;
	var $perms_redirects_edit=false;
	var $perms_redirects_clearCounter=false;
	var $perms_redirects_create=false;
	var $perms_redirects_delete=false;
	/*************/
	var $perms_errors_show=false;
	var $perms_errors_delete=false;
	var $perms_errors_clearCount=false;
	/*************/
	var $perms_tableClean_show=false;
	/*************/
	var $perms_setup_show=false;
	/*************/
	var $perms_about_show=true;
	/* perms end */

	/* pagebrows begin */
	var $pagebrowser_alias=array();
	var $pagebrowser_error=array();
	var $pagebrowser_redirect=array();
	/* pagebrows begin */

	/* show extra fields begin */
	var $showextrafields_page='';
	/* show extra fields end */

	var $isInstalled_Date2Call=array('installed'=>false,'css'=>'','js'=>'','html'=>'','input_cb_prop'=>'','input_text_prop'=>'');
	var $default_expire_cb_prop='';
	var $default_expire_text_prop='name="expirepage_hr" id="expirepage_hr" style="width:115px;" maxlength="20" onchange="typo3FormFieldGet(\'expirepage\',\'datetime\',\'\',1,\'0\');"';


	var $registeredModules=array();
	/**
	 * @return	[type]		...
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		/* perms begin */
		/*************************************************************************************************/
		/* pages begin */
		if(($BE_USER->user["admin"])||($BE_USER->userTS['realUrlManagement.']['pages.']['show']==1)){
			$this->perms_pages_show=true;
		}
		if(($BE_USER->user["admin"])||($BE_USER->userTS['realUrlManagement.']['pages.']['delete']==1)){
			$this->perms_pages_delete=true;
		}
		if(($BE_USER->user["admin"])||($BE_USER->userTS['realUrlManagement.']['pages.']['edit']==1)){
			$this->perms_pages_edit=true;
		}
		if(($BE_USER->user["admin"])||($BE_USER->userTS['realUrlManagement.']['pages.']['editWholeURL']==1)){
			$this->perms_pages_editWholeURL=true;
		}
		if(($BE_USER->user["admin"])||($BE_USER->userTS['realUrlManagement.']['pages.']['deleteShown']==1)){
			$this->perms_pages_deleteShown=true;
		}
		if(($BE_USER->user["admin"])||($BE_USER->userTS['realUrlManagement.']['pages.']['expireShown']==1)){
			$this->perms_pages_expireShown=true;
		}
		if(($BE_USER->user["admin"])||($BE_USER->userTS['realUrlManagement.']['pages.']['changeExpire']==1)){
			$this->perms_pages_expire=true;
		}
		if(($BE_USER->user["admin"])||($BE_USER->userTS['realUrlManagement.']['pages.']['create']==1)){
			$this->perms_pages_create=true;
		}
		/* pages end */
		/*************************************************************************************************/
		/* aliases begin */
		if(($BE_USER->user["admin"])||($BE_USER->userTS['realUrlManagement.']['aliases.']['show']==1)){
			$this->perms_aliases_show=true;
		}
		if(($BE_USER->user["admin"])||($BE_USER->userTS['realUrlManagement.']['aliases.']['delete']==1)){
			$this->perms_aliases_delete=true;
		}
		if(($BE_USER->user["admin"])||($BE_USER->userTS['realUrlManagement.']['aliases.']['edit']==1)){
			$this->perms_aliases_edit=true;
		}
		if(($BE_USER->user["admin"])||($BE_USER->userTS['realUrlManagement.']['aliases.']['changeExpire']==1)){
			$this->perms_aliases_expire=true;
		}
		if(($BE_USER->user["admin"])||($BE_USER->userTS['realUrlManagement.']['aliases.']['create']==1)){
			$this->perms_aliases_create=true;
		}
		/* aliases end */
		/*************************************************************************************************/
		/* errors begin */
		if(($BE_USER->user["admin"])||($BE_USER->userTS['realUrlManagement.']['errors.']['show']==1)){
			$this->perms_errors_show=true;
		}
		if(($BE_USER->user["admin"])||($BE_USER->userTS['realUrlManagement.']['errors.']['delete']==1)){
			$this->perms_errors_delete=true;
		}
		if(($BE_USER->user["admin"])||($BE_USER->userTS['realUrlManagement.']['errors.']['clearCounter']==1)){
			$this->perms_errors_clearCount=true;
		}
		/* errors end */
		/*************************************************************************************************/
		/* table clean begin */
		if(($BE_USER->user["admin"])||($BE_USER->userTS['realUrlManagement.']['tableClean.']['show']==1)){
			$this->perms_tableClean_show=true;
		}
		/* table clean end */
		/*************************************************************************************************/
		/* setup begin */
		if(($BE_USER->user["admin"])||($BE_USER->userTS['realUrlManagement.']['setup.']['show']==1)){
			/*$this->perms_setup_show=true;*/
		}
		/* setup end */
		/*************************************************************************************************/
		/* redirect begin */
		if(($BE_USER->user["admin"])||($BE_USER->userTS['realUrlManagement.']['redirects.']['show']==1)){
			$this->perms_redirects_show=true;
		}
		if(($BE_USER->user["admin"])||($BE_USER->userTS['realUrlManagement.']['redirects.']['edit']==1)){
			$this->perms_redirects_edit=true;
		}
		if(($BE_USER->user["admin"])||($BE_USER->userTS['realUrlManagement.']['redirects.']['clearCounter']==1)){
			$this->perms_redirects_clearCounter=true;
		}
		if(($BE_USER->user["admin"])||($BE_USER->userTS['realUrlManagement.']['redirects.']['create']==1)){
			$this->perms_redirects_create=true;
		}
		if(($BE_USER->user["admin"])||($BE_USER->userTS['realUrlManagement.']['redirects.']['delete']==1)){
			$this->perms_redirects_delete=true;
		}
		/* redirect end */
		/*************************************************************************************************/
		/* about begin */
		if((!$BE_USER->user["admin"])&&($BE_USER->userTS['realUrlManagement.']['about.']['hide']==1)){
			$this->perms_about_show=false;
		}
		/* about end */
		/*************************************************************************************************/


		/* perms end */
		/* pagebrowser begin */
		$tempShowElements=intval($BE_USER->userTS['realUrlManagement.']['pageBrowser.']['recordsOnPage'])==0?20:intval($BE_USER->userTS['realUrlManagement.']['pageBrowser.']['recordsOnPage']);
		$tempShowPages=intval($BE_USER->userTS['realUrlManagement.']['pageBrowser.']['maxPages'])==0?20:intval($BE_USER->userTS['realUrlManagement.']['pageBrowser.']['maxPages']);

		$this->pagebrowser_alias['showElements']=intval($BE_USER->userTS['realUrlManagement.']['pageBrowser.']['aliases.']['recordsOnPage'])==0?$tempShowElements:intval($BE_USER->userTS['realUrlManagement.']['pageBrowser.']['aliases.']['recordsOnPage']);
		$this->pagebrowser_alias['showPages']=intval($BE_USER->userTS['realUrlManagement.']['pageBrowser.']['aliases.']['maxPages'])==0?$tempShowPages:intval($BE_USER->userTS['realUrlManagement.']['pageBrowser.']['aliases.']['maxPages']);

		$this->pagebrowser_error['showElements']=intval($BE_USER->userTS['realUrlManagement.']['pageBrowser.']['errors.']['recordsOnPage'])==0?$tempShowElements:intval($BE_USER->userTS['realUrlManagement.']['pageBrowser.']['errors.']['recordsOnPage']);
		$this->pagebrowser_error['showPages']=intval($BE_USER->userTS['realUrlManagement.']['pageBrowser.']['errors.']['maxPages'])==0?$tempShowPages:intval($BE_USER->userTS['realUrlManagement.']['pageBrowser.']['errors.']['maxPages']);

		$this->pagebrowser_redirect['showElements']=intval($BE_USER->userTS['realUrlManagement.']['pageBrowser.']['redirects.']['recordsOnPage'])==0?$tempShowElements:intval($BE_USER->userTS['realUrlManagement.']['pageBrowser.']['redirects.']['recordsOnPage']);
		$this->pagebrowser_redirect['showPages']=intval($BE_USER->userTS['realUrlManagement.']['pageBrowser.']['redirects.']['maxPages'])==0?$tempShowPages:intval($BE_USER->userTS['realUrlManagement.']['pageBrowser.']['redirects.']['maxPages']);
		/* pagebrowser end */

		/* show extra fields begin */
		$showextrafields_page=$BE_USER->userTS['realUrlManagement.']['showExtraFields.']['pages']!=''?$BE_USER->userTS['realUrlManagement.']['showExtraFields.']['pages']:'';


		/* show extra fields end */




		$this->default_expire_cb_prop=' name="expirepage_cb" onclick="typo3FormFieldGet(\'expirepage\',\'datetime\',\'\',1,\'0\',1,\''.time().'\');" ';
		/* erotea_date2cal 1.2.0 begin */
		if(t3lib_extMgm::isLoaded('erotea_date2cal')){
			$pathToExtension=t3lib_div::resolveBackPath($BACK_PATH.t3lib_extMgm::extRelPath('erotea_date2cal'));
			$dateCal_erotea_date2cal['installed']=true;
			$dateCal_erotea_date2cal['css']='@import url('.$pathToExtension.'jscalendar/calendar-win2k-1.css);';
			$dateCal_erotea_date2cal['js']='
			<script type="text/javascript" src="'.$pathToExtension.'jscalendar/calendar.js"></script>
			<script type="text/javascript" src="'.$pathToExtension.'jscalendar/lang/calendar-en.js"></script>
			<script type="text/javascript" src="'.$pathToExtension.'jscalendar/calendar-setup.js"></script>
			';
			$dateCal_erotea_date2cal['html']='
			<img src="'.$pathToExtension.'jscalendar/img.gif"
				id="expirepage_trigger"
				style="cursor: pointer; border: 1px solid red;"
				title="Date selector"
				onmouseover="this.style.background=\'red\';"
				onmouseout="this.style.background=\'\'"
				/>
			<script type="text/javascript">
			    Calendar.setup({
			        inputField     :    "expirepage_hr",     // id of the input field
			        ifFormat       :    "%H:%M %d-%m-%Y",      // format of the input field
			        button         :    "expirepage_trigger",  //trigger for the calendar
			        align          :    "Bl",           // alignment (defaults to "Bl")
			        singleClick    :    true
					,showsTime      :    true,
		            time24         :    true
				});
			</script>
			';
			$dateCal_erotea_date2cal['input_cb_prop']='';
			$dateCal_erotea_date2cal['input_text_prop']='';
			$this->isInstalled_Date2Call=$dateCal_erotea_date2cal;
		};
		/* erotea_date2cal end */
		/* kj_becalendar 1.0.1 begin */
		if(t3lib_extMgm::isLoaded('kj_becalendar')){
			$pathToExtension=t3lib_div::resolveBackPath($BACK_PATH.t3lib_extMgm::extRelPath('kj_becalendar'));
			if(($BE_USER->userTS['kj_calendar.']['lang']=='de')||($BE_USER->userTS['kj_calendar.']['lang']=='en')){
				$kj_lang=$BE_USER->userTS['kj_calendar.']['lang'];
			}else{
				$kj_lang='en';
			};
			$posibleStyles=array('blue'=>'calendar-blue.css', 'blue2'=>'calendar-blue2.css', 'green'=>'calendar-green.css', 'system'=>'calendar-system.css', 'tas'=>'calendar-tas.css', 'win2k'=>'calendar-win2k-1.css', 'win2k2'=>'calendar-win2k-2.css', 'win2k-cold1'=>'calendar-win2k-cold-1.css', 'win2k-cold2'=>'calendar-win2k-cold-2.css','aqua'=>'aqua/theme.css');
			$kj_style=($posibleStyles[$BE_USER->userTS['kj_calendar.']['style']]!='')?$posibleStyles[$BE_USER->userTS['kj_calendar.']['style']]:'aqua/theme.css';

			$dateCal_kj_becalendar['installed']=true;
			$dateCal_kj_becalendar['css']='@import url('.$pathToExtension.'calendar/skins/'.$kj_style.');';
			$dateCal_kj_becalendar['js']='
					<script type="text/javascript" src="'.$pathToExtension.'calendar/calendar.js"></script>
					<script type="text/javascript" src="'.$pathToExtension.'calendar/calendar-setup.js"></script>
  					<script type="text/javascript" src="'.$pathToExtension.'calendar/lang/calendar-'.$kj_lang.'.js"></script>
					<script type="text/javascript">
							var oldLink = null;

							// code to change the active stylesheet
							function setActiveStyleSheet(link, title) {
							  var i, a, main;
							  for(i=0; (a = document.getElementsByTagName("link")[i]); i++) {
							    if(a.getAttribute("rel").indexOf("style") != -1 && a.getAttribute("title")) {
							      a.disabled = true;
							      if(a.getAttribute("title") == title) a.disabled = false;
							    }
							  }
							  if (oldLink) oldLink.style.fontWeight = \'normal\';
							  oldLink = link;
							  link.style.fontWeight = \'bold\';
							  return false;
							}
							// This function gets called when the end-user clicks on some date.
							function selected(cal, date) {
							  cal.sel.value = date; // just update the date in the input field.
							  if (cal.dateClicked && (cal.sel.id == "sel1" || cal.sel.id == "sel3"))
							    // if we add this call we close the calendar on single-click.
							    // just to exemplify both cases, we are using this only for the 1st
							    // and the 3rd field, while 2nd and 4th will still require double-click.
							    cal.callCloseHandler();
							}

							// And this gets called when the end-user clicks on the _selected_ date,
							// or clicks on the "Close" button.  It just hides the calendar without
							// destroying it.
							function closeHandler(cal) {
							  cal.hide();                        // hide the calendar
							//  cal.destroy();
							  _dynarch_popupCalendar = null;
							}

							// This function shows the calendar under the element having the given id.
							// It takes care of catching "mousedown" signals on document and hiding the
							// calendar if the click was outside.
							function showCalendar(id, format, showsTime, showsOtherMonths) {
							  var el = document.getElementById(id);
							  if (_dynarch_popupCalendar != null) {
							    // we already have some calendar created
							    _dynarch_popupCalendar.hide();                 // so we hide it first.
							  } else {
							    // first-time call, create the calendar.
							    var cal = new Calendar(1, null, selected, closeHandler);
							    // uncomment the following line to hide the week numbers
							    // cal.weekNumbers = false;
							    if (typeof showsTime == "string") {
							      cal.showsTime = true;
							      cal.time24 = (showsTime == "24");
							    }
							    if (showsOtherMonths) {
							      cal.showsOtherMonths = true;
							    }
							    _dynarch_popupCalendar = cal;                  // remember it in the global var
							    cal.setRange(1900, 2070);        // min/max year allowed.
							    cal.create();
							  }
							  _dynarch_popupCalendar.setDateFormat(format);    // set the specified date format
							  _dynarch_popupCalendar.parseDate(el.value);      // try to parse the text in field
							  _dynarch_popupCalendar.sel = el;                 // inform it what input field we use

							  // the reference element that we pass to showAtElement is the button that
							  // triggers the calendar.  In this example we align the calendar bottom-right
							  // to the button.
							  _dynarch_popupCalendar.showAtElement(el.nextSibling, "Br");        // show the calendar

							  return false;
							}

							var MINUTE = 60 * 1000;
							var HOUR = 60 * MINUTE;
							var DAY = 24 * HOUR;
							var WEEK = 7 * DAY;
					</script>';
			$dateCal_kj_becalendar['html']='
				<script type="text/javascript">
					    function catcalc(cal) {
					        var date = cal.date;
					        var time = date.getTime()
					        // use the _other_ field
					        var field = document.getElementById("expirepage_hr");
					        var date2 = new Date(time);
					        field.value = date2.print("%H:%M %e-%m-%Y");
					    }
					    Calendar.setup({
					        inputField     :    "expirepage_hr",
					        ifFormat       :    "%H:%M %e-%m-%Y",
					        showsTime      :    true,
					        timeFormat     :    "24",
					        onUpdate       :    catcalc
					    });
				</script>
			';
			$dateCal_kj_becalendar['input_cb_prop']='';
			$dateCal_kj_becalendar['input_text_prop']='';
			$this->isInstalled_Date2Call=$dateCal_kj_becalendar;
		};
		/* kj_becalendar end */

		parent::init();

		/*
		if (t3lib_div::_GP("clear_all_cache"))	{
			$this->include_once[]=PATH_t3lib."class.t3lib_tcemain.php";
		}
		*/
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	[type]		...
	 */
	function menuConfig()	{
		global $LANG;
		$this->MOD_MENU = Array (
			'depth' => array(
				0 => $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.depth_0',1),
				1 => $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.depth_1',1),
				2 => $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.depth_2',1),
				3 => $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.depth_3',1),
				4 => $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.depth_4',1),
				10 => $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.depth_infi',5)
			),
		);
		$i=1;
		if($this->perms_pages_show){
			$this->MOD_MENU["function"][$i]=$LANG->getLL("realUrl_standardPages");
			$this->registeredModules[$i]='tx_realurlmanagement_pages';
			$i++;
		}
		if($this->perms_aliases_show){
			$this->MOD_MENU["function"][$i]=$LANG->getLL("realUrl_alias");
			$this->registeredModules[$i]='tx_realurlmanagement_aliases';
			$i++;
		};
		if($this->perms_errors_show){
			$this->MOD_MENU["function"][$i]=$LANG->getLL("realUrl_Errors_site");
			$this->registeredModules[$i]='tx_realurlmanagement_errors';
			$i++;
		};
		if($this->perms_redirects_show){
			$this->MOD_MENU["function"][$i]=$LANG->getLL("realUrl_Redirects");
			$this->registeredModules[$i]='tx_realurlmanagement_redirects';
			$i++;
		};
		if($this->perms_tableClean_show){
			$this->MOD_MENU["function"][$i]=$LANG->getLL("realUrl_dbClean");
			$this->registeredModules[$i]='tx_realurlmanagement_dbclean';
			$i++;
		};
		/*if($this->perms_setup_show){
			$this->MOD_MENU["function"][$i]=$LANG->getLL("realUrl_setup");
			$this->registeredModules[$i]='tx_realurlmanagement_setup';
			$i++;
		}*/
		if($this->perms_about_show){
			$this->MOD_MENU["function"][$i]=$LANG->getLL("realUrl_About");
			$this->registeredModules[$i]='tx_realurlmanagement_about';
			$i++;
		};
		if(count($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurlmanagement']['extraModules'])>0){
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurlmanagement']['extraModules'] as $module_extkey=>$module_info){
				/* check if the user has the required properties */
				if(($BE_USER->user["admin"])||(!$BE_USER->userTS['realUrlManagement.']['modules.'][$module_extkey.'.']['hide']==1)){
					$this->MOD_MENU["function"][$i]=$LANG->sL($module_info['text']);
					$this->registeredModules[$i]=$module_info['class'];
					$this->include_once[]=t3lib_extMgm::extPath($module_extkey).$module_info['script'];
					$i++;
				};
			}
		}

		parent::menuConfig();
	}

		// If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	/**
	 * Main function of the module. Write the content to $this->content
	 *
	 * @return	[type]		...
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		$this->perms_clause = $BE_USER->getPagePermsClause(1);

		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;
		$this->pageuid=t3lib_div::_GP('id');
		if (($this->id && $access) || ($BE_USER->user["admin"] && !$this->id))	{

				// Draw the header.
			$this->doc = t3lib_div::makeInstance("mediumDoc");
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form='<form action="" id="editform" name="editform" method="POST">';
			if($this->MOD_SETTINGS["function"]==6){
				$this->doc->bodyTagAdditions=' onmousemove="GL_getMouse(event);" onload="initLayer();" ';
			};
				// JavaScript
			if(@file_exists(PATH_site.'t3lib/jsfunc.evalfield.js')){$tempPathjs_funcEval=t3lib_div::resolveBackPath($BACK_PATH.'../t3lib/jsfunc.evalfield.js');};
			if(@file_exists(PATH_site.'typo3/t3lib/jsfunc.evalfield.js')){$tempPathjs_funcEval=$BACK_PATH.'t3lib/jsfunc.evalfield.js';};

			$this->doc->JScode = '
				<script type="text/javascript" src="'.$tempPathjs_funcEval.'"></script>
				<script type="text/javascript">
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$action: ...
	 * @return	[type]		...
	 */
					function submitForm(action){
						document.getElementById(\'act2\').value=action;
						document.getElementById(\'editform\').submit();
					}
				</script>
			';
			$this->doc->postCode='
				<script type="text/javascript">
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = '.intval($this->id).';
				</script>
			';
			if($this->isInstalled_Date2Call['installed']){
				if($this->isInstalled_Date2Call['css']!=''){
					$this->doc->inDocStylesArray[]=$this->isInstalled_Date2Call['css'];
				};
				if($this->isInstalled_Date2Call['js']!=''){
					$this->doc->JScode.=$this->isInstalled_Date2Call['js'];
				};
			}
			$this->doc->inDocStylesArray[]='
				table img{vertical-align:middle;}
				table td.bgColor2{text-align:left; padding:2px;}
				table td.nopadding{padding:0px!important;}
				a.helptag{position:relative;}
				a.helptag span{visibility:hidden; position:absolute; border:1px solid #686868; background-color:#1B1B1B; color:#FFF; padding:3px; z-index:30;}
				a.helptag span p{color:#fff; width:300px;}
				a.helptag:hover span{ visibility:visible; top:15px; left:20px; }
				div.pagebrowser{padding:0px; margin:10px 0px; text-align:center;}
				div.pagebrowser a,
				div.pagebrowser span{padding:0px 2px;}

			';
			$headerSection = $this->doc->getHeader("pages",$this->pageinfo,$this->pageinfo["_thePath"])."<br/>".$LANG->sL("LLL:EXT:lang/locallang_core.php:labels.path").": ".t3lib_div::fixed_lgd_pre($this->pageinfo["_thePath"],50);

			$this->content.=$this->doc->startPage($LANG->getLL("title"));
			$this->content.=$this->doc->header($LANG->getLL("title"));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->section("",$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,"SET[function]",$this->MOD_SETTINGS["function"],$this->MOD_MENU["function"])));
			$this->content.=$this->doc->divider(5);



			/* images for order begin */
			$this->imageOrderAscInactiv='<img'.t3lib_iconWorks::skinImg('../','gfx/order_asc_inactiv.gif','width="12" height="12"').' border="0" title="'.$LANG->getLL('order_asc_inactive',1).'" alt="" />';
			$this->imageOrderDescInactiv='<img'.t3lib_iconWorks::skinImg('../','gfx/order_desc_inactiv.gif','width="12" height="12"').' border="0" title="'.$LANG->getLL('order_desc_inactive',1).'" alt="" />';
			$this->imageOrderAscActiv='<img'.t3lib_iconWorks::skinImg('../','gfx/order_asc_activ.gif','width="12" height="12"').' border="0" title="'.$LANG->getLL('order_asc_active',1).'" alt="" />';
			$this->imageOrderDescActiv='<img'.t3lib_iconWorks::skinImg('../','gfx/order_desc_activ.gif','width="12" height="12"').' border="0" title="'.$LANG->getLL('order_desc_active',1).'" alt="" />';
			/* images for order end */

			// Render content:
			$this->moduleContent();


			// ShortCut
			if ($BE_USER->mayMakeShortcut())	{
				$this->content.=$this->doc->spacer(20).$this->doc->section("",$this->doc->makeShortcutIcon("id",implode(",",array_keys($this->MOD_MENU)),$this->MCONF["name"]));
			}

			$this->content.=$this->doc->spacer(10);
		} else {
				// If no access or if ID == zero

			$this->doc = t3lib_div::makeInstance("mediumDoc");
			$this->doc->backPath = $BACK_PATH;

			$this->content.=$this->doc->startPage($LANG->getLL("title"));
			$this->content.=$this->doc->header($LANG->getLL("title"));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->spacer(10);
		}
	}

	/**
	 * Prints out the module HTML
	 *
	 * @return	[type]		...
	 */
	function printContent()	{

		$this->content.=$this->doc->endPage();
		echo $this->content;
	}

	/**
	 * Generates the module content
	 *
	 * @return	[type]		...
	 */
	function moduleContent()	{
		$usemodule='tx_realurlmanagement_pages';
		if($this->MOD_SETTINGS["function"]!=0 && $this->registeredModules[$this->MOD_SETTINGS["function"]]!=''){$usemodule=$this->registeredModules[$this->MOD_SETTINGS["function"]];};

		$module=t3lib_div::makeInstance($usemodule);
		$module->pObj = &$this;
		$module->helpfunc=t3lib_div::makeInstance('tx_realurlmanagement_helpfunc');
		$module->helpfunc->pObj=&$this;
		$this->content.=$module->showModule();
	}


}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/realurlmanagement/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/realurlmanagement/mod1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_realurlmanagement_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>