<?php
/***************************************************************
*  Copyright notice
*
*  (c)  2001-2006 Kasper Skaarhoj (kasperYYYY@typo3.com)  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 */

require_once(t3lib_extMgm::extPath('kickstarter').'class.tx_kickstarter_sectionbase.php');

class tx_kickstarter_section_module extends tx_kickstarter_sectionbase {
  var $sectionID = 'module';
	/**
	 * Renders the form in the kickstarter; this was add_cat_module()
	 *
	 * @return	HTML
	 */
	function render_wizard() {
		$lines=array();

		$action = explode(':',$this->wizard->modData['wizAction']);
		if ($action[0]=='edit')	{
			$this->regNewEntry($this->sectionID,$action[1]);
			$lines = $this->catHeaderLines($lines,$this->sectionID,$this->wizard->options[$this->sectionID],'&nbsp;',$action[1]);
			$piConf = $this->wizard->wizArray[$this->sectionID][$action[1]];
			$ffPrefix='['.$this->sectionID.']['.$action[1].']';

				// Enter title of the module
			$subContent='<strong>Enter a title for the module:</strong><br />'.
				$this->renderStringBox_lang('title',$ffPrefix,$piConf);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Description
			$subContent='<strong>Enter a description:</strong><br />'.
				$this->renderStringBox_lang('description',$ffPrefix,$piConf);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Description
			$subContent='<strong>Enter a tab label (shorter description):</strong><br />'.
				$this->renderStringBox_lang('tablabel',$ffPrefix,$piConf);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Position
			$optValues = array(
				'web' => 'Sub in Web-module',
				'file' => 'Sub in File-module',
				'user' => 'Sub in User-module',
				'tools' => 'Sub in Tools-module',
				'help' => 'Sub in Help-module',
				'_MAIN' => 'New main module'
			);
			$subContent='<strong>Sub- or main module?</strong><br />'.
				$this->renderSelectBox($ffPrefix.'[position]',$piConf['position'],$optValues).
				$this->resImg('module.png');
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Sub-position
			$optValues = array(
				'0' => 'Bottom (default)',
				'top' => 'Top',
				'web_after_page' => 'If in Web-module, after Web>Page',
				'web_before_info' => 'If in Web-module, before Web>Info',
			);
			$subContent='<strong>Position in module menu?</strong><br />'.
				$this->renderSelectBox($ffPrefix.'[subpos]',$piConf['subpos'],$optValues);
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Admin only
			$subContent = $this->renderCheckBox($ffPrefix.'[admin_only]',$piConf['admin_only']).'Admin-only access!<br />';
			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';

				// Options
			$subContent = $this->renderCheckBox($ffPrefix.'[interface]',$piConf['interface']).'Allow other extensions to interface with function menu<br />';
#			$lines[]='<tr'.$this->bgCol(3).'><td>'.$this->fw($subContent).'</td></tr>';
		}

		/* HOOK: Place a hook here, so additional output can be integrated */
		if(is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_module'])) {
		  foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['add_cat_module'] as $_funcRef) {
		    $lines = t3lib_div::callUserFunction($_funcRef, $lines, $this);
		  }
		}

		$content = '<table border=0 cellpadding=2 cellspacing=2>'.implode('',$lines).'</table>';
		return $content;
	}








	/**
	 * Renders the extension PHP codee; this was
	 *
	 * @param	string		$k: module name key
	 * @param	array		$config: module configuration
	 * @param	string		$extKey: extension key
	 * @return	void
	 */
	function render_extPart($k,$config,$extKey) {
		$WOP='[module]['.$k.']';
		$mN = ($config['position']!='_MAIN'?$config['position'].'_':'').$this->returnName($extKey,'module','M'.$k);
		$cN = $this->returnName($extKey,'class','module'.$k);
		$pathSuffix = 'mod'.$k.'/';

			// Insert module:
		switch($config['subpos'])	{
			case 'top':
				$subPos='top';
			break;
			case 'web_after_page':
				$subPos='after:layout';
			break;
			case 'web_before_info':
				$subPos='before:info';
			break;
		}
		$this->wizard->ext_tables[]=$this->sPS('
			'.$this->WOPcomment('WOP:'.$WOP).'
			if (TYPO3_MODE=="BE")	{
					'.$this->WOPcomment('1. and 2. parameter is WOP:'.$WOP.'[position] , 3. parameter is WOP:'.$WOP.'[subpos]').'
				t3lib_extMgm::addModule("'.
					($config['position']!='_MAIN'?$config['position']:$this->returnName($extKey,'module','M'.$k)).
					'","'.
					($config['position']!='_MAIN'?$this->returnName($extKey,'module','M'.$k):'').
					'","'.
					$subPos.
					'",t3lib_extMgm::extPath($_EXTKEY)."'.$pathSuffix.'");
			}
		');

			// Make conf.php file:
		$content = $this->sPS('
				// DO NOT REMOVE OR CHANGE THESE 3 LINES:
			define(\'TYPO3_MOD_PATH\', \'ext/'.$extKey.'/'.$pathSuffix.'\');
			$BACK_PATH=\'../../../\';
			$MCONF[\'name\']=\''.$mN.'\';

				'.$this->WOPcomment('WOP:'.$WOP.'[admin_only]: If the flag was set the value is "admin", otherwise "user,group"').'
			$MCONF[\'access\']=\''.($config['admin_only']?'admin':'user,group').'\';
			$MCONF[\'script\']=\'index.php\';

			$MLANG[\'default\'][\'tabs_images\'][\'tab\'] = \'moduleicon.gif\';
			$MLANG[\'default\'][\'ll_ref\']=\'LLL:EXT:'.$extKey.'/'.$pathSuffix.'locallang_mod.xml\';
		');
		$this->wizard->EM_CONF_presets['module'][]=ereg_replace('\/$','',$pathSuffix);


		$ll=array();
		$this->addLocalConf($ll,$config,'title','module',$k,1,0,'mlang_tabs_tab');
		$this->addLocalConf($ll,$config,'description','module',$k,1,0,'mlang_labels_tabdescr');
		$this->addLocalConf($ll,$config,'tablabel','module',$k,1,0,'mlang_labels_tablabel');
		$this->addLocalLangFile($ll,$pathSuffix.'locallang_mod.xml','Language labels for module "'.$mN.'" - header, description');

//			$MLANG["default"]["tabs"]["tab"] = "'.addslashes($config["title"]).'";	'.$this->WOPcomment('WOP:'.$WOP.'[title]').'
//			$MLANG["default"]["labels"]["tabdescr"] = "'.addslashes($config["description"]).'";	'.$this->WOPcomment('WOP:'.$WOP.'[description]').'
//			$MLANG["default"]["labels"]["tablabel"] = "'.addslashes($config["tablabel"]).'";	'.$this->WOPcomment('WOP:'.$WOP.'[tablabel]').'

/*
		if (count($this->selectedLanguages))	{
			reset($this->selectedLanguages);
			while(list($lk,$lv)=each($this->selectedLanguages))	{
				if ($lv)	{
					$content.= $this->sPS('
							// '.$this->languages[$lk].' language:
						$MLANG["'.$lk.'"]["tabs"]["tab"] = "'.addslashes($config["title_".$lk]).'";	'.$this->WOPcomment('WOP:'.$WOP.'[title_'.$lk.']').'
						$MLANG["'.$lk.'"]["labels"]["tabdescr"] = "'.addslashes($config["description_".$lk]).'";	'.$this->WOPcomment('WOP:'.$WOP.'[description_'.$lk.']').'
						$MLANG["'.$lk.'"]["labels"]["tablabel"] = "'.addslashes($config["tablabel_".$lk]).'";	'.$this->WOPcomment('WOP:'.$WOP.'[tablabel_'.$lk.']').'
					');
				}
			}
		}
*/
		$content=$this->wrapBody('
			<?php
			',$content,'
			?>
		',0);

		$this->addFileToFileArray($pathSuffix.'conf.php',trim($content));

			// Add title to local lang file
		$ll=array();
		$this->addLocalConf($ll,$config,'title','module',$k,1);
		$this->addLocalConf($ll,array('function1'=>'Function #1'),'function1','module',$k,1,1);
		$this->addLocalConf($ll,array('function2'=>'Function #2'),'function2','module',$k,1,1);
		$this->addLocalConf($ll,array('function3'=>'Function #3'),'function3','module',$k,1,1);
		$this->addLocalLangFile($ll,$pathSuffix.'locallang.xml','Language labels for module "'.$mN.'"');

			// Add clear.gif
		$this->addFileToFileArray($pathSuffix.'clear.gif',t3lib_div::getUrl(t3lib_extMgm::extPath('kickstarter').'res/clear.gif'));

			// Add clear.gif
		$this->addFileToFileArray($pathSuffix.'moduleicon.gif',t3lib_div::getUrl(t3lib_extMgm::extPath('kickstarter').'res/notfound_module.gif'));


			// Make module index.php file:
		$indexContent = $this->sPS('
				// DEFAULT initialization of a module [BEGIN]
			unset($MCONF);
			require_once("conf.php");
			require_once($BACK_PATH."init.php");
			require_once($BACK_PATH."template.php");
			$LANG->includeLLFile("EXT:'.$extKey.'/'.$pathSuffix.'locallang.xml");
			require_once(PATH_t3lib."class.t3lib_scbase.php");
			$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
				// DEFAULT initialization of a module [END]
		');

		$indexContent.= $this->sPS('
			class '.$cN.' extends t3lib_SCbase {
				var $pageinfo;

				/**
				 * Initializes the Module
				 * @return	void
				 */
				function init()	{
					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

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
				 * @return	void
				 */
				function menuConfig()	{
					global $LANG;
					$this->MOD_MENU = Array (
						"function" => Array (
							"1" => $LANG->getLL("function1"),
							"2" => $LANG->getLL("function2"),
							"3" => $LANG->getLL("function3"),
						)
					);
					parent::menuConfig();
				}

				/**
				 * Main function of the module. Write the content to $this->content
				 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
				 *
				 * @return	[type]		...
				 */
				function main()	{
					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

					// Access check!
					// The page will show only if there is a valid page and if this page may be viewed by the user
					$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
					$access = is_array($this->pageinfo) ? 1 : 0;

					if (($this->id && $access) || ($BE_USER->user["admin"] && !$this->id))	{

							// Draw the header.
						$this->doc = t3lib_div::makeInstance("mediumDoc");
						$this->doc->backPath = $BACK_PATH;
						$this->doc->form=\'<form action="" method="POST">\';

							// JavaScript
						$this->doc->JScode = \'
							<script language="javascript" type="text/javascript">
								script_ended = 0;
								function jumpToUrl(URL)	{
									document.location = URL;
								}
							</script>
						\';
						$this->doc->postCode=\'
							<script language="javascript" type="text/javascript">
								script_ended = 1;
								if (top.fsMod) top.fsMod.recentIds["web"] = '.intval($this->id).';
							</script>
						\';

						$headerSection = $this->doc->getHeader("pages",$this->pageinfo,$this->pageinfo["_thePath"])."<br />".$LANG->sL("LLL:EXT:lang/locallang_core.xml:labels.path").": ".t3lib_div::fixed_lgd_pre($this->pageinfo["_thePath"],50);

						$this->content.=$this->doc->startPage($LANG->getLL("title"));
						$this->content.=$this->doc->header($LANG->getLL("title"));
						$this->content.=$this->doc->spacer(5);
						$this->content.=$this->doc->section("",$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,"SET[function]",$this->MOD_SETTINGS["function"],$this->MOD_MENU["function"])));
						$this->content.=$this->doc->divider(5);


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
				 * @return	void
				 */
				function printContent()	{

					$this->content.=$this->doc->endPage();
					echo $this->content;
				}

				/**
				 * Generates the module content
				 *
				 * @return	void
				 */
				function moduleContent()	{
					switch((string)$this->MOD_SETTINGS["function"])	{
						case 1:
							$content="<div align=center><strong>Hello World!</strong></div><br />
								The \'Kickstarter\' has made this module automatically, it contains a default framework for a backend module but apart from it does nothing useful until you open the script \'".substr(t3lib_extMgm::extPath("'.$extKey.'"),strlen(PATH_site))."'.$pathSuffix.'index.php\' and edit it!
								<HR>
								<br />This is the GET/POST vars sent to the script:<br />".
								"GET:".t3lib_div::view_array($_GET)."<br />".
								"POST:".t3lib_div::view_array($_POST)."<br />".
								"";
							$this->content.=$this->doc->section("Message #1:",$content,0,1);
						break;
						case 2:
							$content="<div align=center><strong>Menu item #2...</strong></div>";
							$this->content.=$this->doc->section("Message #2:",$content,0,1);
						break;
						case 3:
							$content="<div align=center><strong>Menu item #3...</strong></div>";
							$this->content.=$this->doc->section("Message #3:",$content,0,1);
						break;
					}
				}
			}
		');

		$SOBE_extras['firstLevel']=0;
		$SOBE_extras['include']=1;
		$this->addFileToFileArray($pathSuffix.'index.php',$this->PHPclassFile($extKey,$pathSuffix.'index.php',$indexContent,"Module '".$config["title"]."' for the '".$extKey."' extension.",$cN,$SOBE_extras));

	}

}


// Include ux_class extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/kickstarter/sections/class.tx_kickstarter_section_module.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/kickstarter/sections/class.tx_kickstarter_section_module.php']);
}


?>