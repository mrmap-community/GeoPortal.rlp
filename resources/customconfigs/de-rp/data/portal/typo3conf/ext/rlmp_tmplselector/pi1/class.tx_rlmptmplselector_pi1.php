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
 * Plugin 'Template selector' for the 'rlmp_tmplselector' extension.
 *
 * @author	Kasper Skårhøj <kasper@typo3.com>
 * @author Robert Lemke <robert@typo3.org>
 *  
 */


require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(PATH_t3lib.'class.t3lib_page.php');

class tx_rlmptmplselector_pi1 extends tslib_pibase {
	var $prefixId = "tx_rlmptmplselector_pi1";		// Same as class name
	var $scriptRelPath = "pi1/class.tx_rlmptmplselector_pi1.php";	// Path to this script relative to the extension dir.
	var $extKey = "rlmp_tmplselector";	// The extension key.
	
	/**
	 * Reads the template-html file which is pointed to by the selector box on the page
	 * and type parameter send through TypoScript.
	 * cObject (Content Object)
	 *
	 * @param	string		Empty content string passed. Not used.
	 * @param	array		TypoScript properties that belong to this cObj
	 * @param	string		The content of the required file
	 * @return	string		The HTML template
	 */
	
	function main($content,$conf)	{
		global $TSFE;

		// GETTING configuration for the extension:
		$confArray = unserialize($GLOBALS["TYPO3_CONF_VARS"]["EXT"]["extConf"]["rlmp_tmplselector"]);
		$tmplConf = $TSFE->tmpl->setup["plugin."]["tx_rlmptmplselector_pi1."];
		$rootLine = $TSFE->rootLine;
		$pageSelect = t3lib_div::makeInstance('t3lib_pageSelect');
		
			// If we should inherit the template from above the current page, search for the next selected template
			// and make it the default template
		if (is_array ($rootLine)) {
			if (intval($tmplConf['inheritMainTemplates']) == 1) {
				foreach ($rootLine as $rootLinePage) {
					$page = $pageSelect->getPage ($rootLinePage['uid']);					
					if ($page['tx_rlmptmplselector_main_tmpl']) {
						$tmplConf['defaultTemplateFileNameMain'] = $tmplConf['defaultTemplateObjectMain'] = $page['tx_rlmptmplselector_main_tmpl'];
						break;
					}
				}
			}
			if (intval($tmplConf['inheritSubTemplates']) == 1) {
				foreach ($rootLine as $rootLinePage) {
					$page = $pageSelect->getPage ($rootLinePage['uid']);
					if ($page['tx_rlmptmplselector_ca_tmpl']) {
						$tmplConf['defaultTemplateFileNameSub'] = $tmplConf['defaultTemplateObjectSub'] = $page['tx_rlmptmplselector_ca_tmpl'];
						break;
					}
				}
			}
		}

			// Determine mode: If it is 'file', work with external HTML template files
		if ($confArray['templateMode']=='file') {
				// Getting the "type" from the input TypoScript configuration:
			switch ((string)$conf['templateType']) {
				case 'sub':
					$templateFile = $TSFE->page['tx_rlmptmplselector_ca_tmpl'];
					$relPath = $tmplConf['templatePathSub'];
						// Setting templateFile reference to the currently selected value - or the default if not set:
					if (! $templateFile) { $templateFile = $tmplConf['defaultTemplateFileNameSub']; }
					break;
				case 'main':
					default:
					$templateFile = $TSFE->page['tx_rlmptmplselector_main_tmpl'];
					$relPath = $tmplConf['templatePathMain'];
						// Setting templateFile reference to the currently selected value - or the default if not set:
					if (! $templateFile) { $templateFile = $tmplConf['defaultTemplateFileNameMain']; }
				break;
			}
			  
			if ($relPath) {   // if a value was found, we dare to continue
				if (strrpos ($relPath, '/') != strlen ($relPath) - 1) {
					$relPath .= '/';
				 }
				// get absolute filepath:
				$absFilePath = t3lib_div::getFileAbsFileName($relPath.$templateFile);
				if ($absFilePath && @is_file($absFilePath)) {
					$content = t3lib_div::getURL($absFilePath);			
					return $content;
				}
			}
		}
		
			// Don't use external files - do it the TS way instead
		if ($confArray['templateMode']=='ts') {

				// Getting the "type" from the input TypoScript configuration:
			switch ((string)$conf['templateType']) {
				case 'sub':
					$templateObjectNr = $TSFE->page['tx_rlmptmplselector_ca_tmpl'];
					if (!$templateObjectNr) {	$templateObjectNr = $tmplConf['defaultTemplateObjectSub'];	}
					break;
				case 'main':
				default:
					$templateObjectNr = $TSFE->page['tx_rlmptmplselector_main_tmpl'];
					if (!$templateObjectNr) {	$templateObjectNr = $tmplConf['defaultTemplateObjectMain'];	}
				break;
			}

				// Parse the template
			$lConf = &$tmplConf['templateObjects.'][(string)$conf['templateType'].'.'][$templateObjectNr.'.'];
			$content = $this->cObj->TEMPLATE ($lConf);
			return $content;	    	  
		}
	}
}


if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/rlmp_tmplselector/pi1/class.tx_rlmptmplselector_pi1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/rlmp_tmplselector/pi1/class.tx_rlmptmplselector_pi1.php"]);
}

?>