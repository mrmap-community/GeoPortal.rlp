<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2003 Kasper Skårhøj (kasper@typo3.org)
*  (c) 2004-2006 Robert Lemke (robert@typo3.org)
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
 * Class/Function which manipulates the item-array for table/field pages_tx_rlmptmplselector_main_tmpl.
 *
 * @author	Kasper Skårhøj <kasper@typo3.com>
 * @author Robert Lemke <robert@typo3.org>
 */

require_once (PATH_t3lib."class.t3lib_page.php");
require_once (PATH_t3lib."class.t3lib_tstemplate.php");
require_once (PATH_t3lib."class.t3lib_tsparser_ext.php");

class tx_rlmptmplselector_addfilestosel {
   var $dir = "templatePathMain";
   var $branch = 'main.';

   /**
    * Manipulating the input array, $params, adding new selectorbox items.
    * 
    * @param	array		$params: Parameters of the user function caller
    * @param	object		$pObj: Reference to the parent object calling this function
    * @return	void		The result is in the manipulated $params array
    */
	function main(&$params,&$pObj)	{
			
		$thePageId = $params["row"]["uid"];
		if (!is_numeric($thePageId)) {
			$this->getParams = t3lib_div::_GET("edit");
			$this->getParams_arrayKeys = array_keys($this->getParams['pages']);
			$thePageId = $this->getParams_arrayKeys[0];
		}
		
		$template = t3lib_div::makeInstance("t3lib_tsparser_ext"); 		// Defined global here!
		$template->tt_track = 0; 													// Do not log time-performance information
		$template->init();
		$rootLine = t3lib_BEfunc::BEgetRootLine($thePageId);
		$template->runThroughTemplates($rootLine,$template_uid); 		// This generates the constants/config + hierarchy info for the template.
		$template->generateConfig();

			// GETTING configuration for the extension:
		$confArray = unserialize($GLOBALS["TYPO3_CONF_VARS"]["EXT"]["extConf"]["rlmp_tmplselector"]);

			// Use external HTML template files:
		if ($confArray['templateMode']=='file') {
       
		     // Finding value for the path containing the template files
		   $readPath = t3lib_div::getFileAbsFileName($template->setup["plugin."]["tx_rlmptmplselector_pi1."][$this->dir]);
		     // If that direcotry is valid, is a directory then select files in it:
		   if (@is_dir($readPath))   {
		        //getting all HTML files in the directory:
		      $template_files = t3lib_div::getFilesInDir ($readPath,'html,htm',1,1);
		      
		        // Start up the HTML parser:
		      $parseHTML = t3lib_div::makeInstance ('t3lib_parseHTML');
		      
		        // Traverse that array:
		      foreach ($template_files as $htmlFilePath) {
		         // Reset vars:
		         $selectorBoxItem_title='';
		         $selectorBoxItem_icon='';
		         
		           // Reading the content of the template document ...
		         $content = t3lib_div::getUrl ($htmlFilePath);
		           // ... and extracting the content of the title-tags:
		         $parts = $parseHTML->splitIntoBlock('title',$content);
		         $titleTagContent = $parseHTML->removeFirstAndLastTag($parts[1]);
		           // Setting the item label:
		         $selectorBoxItem_title = trim ($titleTagContent.' ('.basename($htmlFilePath).')');
		         
		           // Trying to look up an image icon for the template
		         $fI = t3lib_div::split_fileref($htmlFilePath);
		         $testImageFilename=$readPath.$fI['filebody'].'.gif';
		         if (@is_file($testImageFilename)) {
		           $selectorBoxItem_icon = '../'.substr ($testImageFilename,strlen(PATH_site));
		         }
		         
		           // Finally add the new item:
		         $params["items"][] = Array(
		           $selectorBoxItem_title,
		           basename ($htmlFilePath),
		           $selectorBoxItem_icon
		         );
				}
			}
		}

			// Don't use external files - do it the TS way instead
		if ($confArray['templateMode']=='ts') {

		     // Finding value for the path containing the template files
		   $readPath = t3lib_div::getFileAbsFileName('uploads/tf/');
		   
			$tmplObjects = $template->setup["plugin."]["tx_rlmptmplselector_pi1."]["templateObjects."][$this->branch];
				// Traverse template objects
			if (is_array ($tmplObjects)) {
				reset ($tmplObjects);
				while ($tmplObject = each ($tmplObjects)) {
					$k = $tmplObject["key"];
					$v = $tmplObject["value"];
					if ($v == 'TEMPLATE') {
						if (is_array ($tmplObjects[$k.'.']['tx_rlmptmplselector.'])) {
				         $selectorBoxItem_title=$tmplObjects[$k.'.']['tx_rlmptmplselector.']['title'];
				         unset ($selectorBoxItem_icon);

				         $fI = t3lib_div::split_fileref(trim ($tmplObjects[$k.'.']['tx_rlmptmplselector.']['imagefile']));
				         $testImageFilename=$readPath.$fI['filebody'].'.gif';
				         if (@is_file($testImageFilename)) {
				           $selectorBoxItem_icon = '../'.substr ($testImageFilename,strlen(PATH_site));
				         }

				         $params["items"][] = Array(
				           $selectorBoxItem_title,
				           $k,
				           $selectorBoxItem_icon
				         );
						}
					}
				}
			}					
		}
	}
}

class tx_rlmptmplselector_addfilestosel_ca extends tx_rlmptmplselector_addfilestosel {
   var $dir = "templatePathSub";
   var $branch = 'sub.';
}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/rlmp_tmplselector/class.tx_rlmptmplselector_addfilestosel.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/rlmp_tmplselector/class.tx_rlmptmplselector_addfilestosel.php"]);
}

?>