<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2005 Kasper Skaarhoj (kasperYYYY@typo3.com)
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
 * Class for generation of the module menu.
 * Will make the vertical, horizontal, selectorbox based menus AND the "about modules" display.
 * Basically it traverses the module structure and generates output based on that.
 *
 * $Id: class.alt_menu_functions.inc,v 1.26 2005/04/27 09:57:51 typo3 Exp $
 * Revised for TYPO3 3.6 2/2003 by Kasper Skaarhoj
 * XHTML compliant content
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *  444:     function mIconFilename($Ifilename,$backPath)
 * TOTAL FUNCTIONS: 1
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */









/**
 * Class with menu functions
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @author	Tapio Markula
 * @package TYPO3
 * @subpackage core
 */
 
$skin_grey_2_Conf = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['skin_grey_2']);

class ux_ux_alt_menu_functions extends ux_alt_menu_functions {

	/* Returns relative filename to the $Ifilename (for use in img-tags)
	 *
	 * @param	string		Icon filename
	 * @param	string		Back path
	 * @return	string		Result
	 * @see mIconFile()
	 */
	 
	function mIconFilename($Ifilename,$backPath)	{
		global $skin_grey_2_Conf;
		
		$confProperties = t3lib_BEfunc::getModTSconfig($this->pageinfo['uid'],'mod.skin_grey_2');
		
		if(!empty($confProperties['properties']['ModuleIconSetPath']))
			$skin_grey_2_Conf['enable.']['ModuleIconSetPath']=$confProperties['properties']['ModuleIconSetPath'];
		if(!empty($confProperties['properties']['ModuleIconSet']))
			$skin_grey_2_Conf['enable.']['ModuleIconSet']=$confProperties['properties']['ModuleIconSet'];
		
			
		if (t3lib_div::isAbsPath($Ifilename)){
			$Ifilename = '../'.substr($Ifilename,strlen(PATH_site));
			}
		
		if(!empty($skin_grey_2_Conf['enable.']['ModuleIconSetPath'])){
			$Ifilename = str_replace ("icons/module", $skin_grey_2_Conf['enable.']['ModuleIconSetPath'].'/module',$Ifilename);
			}
		else if($skin_grey_2_Conf['enable.']['ModuleIconSet']!='standard'){
			$Ifilename = str_replace ("icons/module", $skin_grey_2_Conf['enable.']['ModuleIconSet'].'_moduleicons/module',$Ifilename);
			}
			
		return $backPath.$Ifilename;
	}

}

?>
