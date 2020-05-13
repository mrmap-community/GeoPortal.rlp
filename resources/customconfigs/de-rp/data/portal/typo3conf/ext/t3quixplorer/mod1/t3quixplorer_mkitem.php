<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2004 Mads Brunn (brunn@mail.dk)
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
 * class 't3quixplorer_mkitem' for the 't3quixplorer' extension.
 * Contains functions to create files or folders
 *
 * @author	Mads Brunn <brunn@mail.dk>
 */
/***************************************************************

     The Original Code is fun_mkitem.php, released on 2003-03-31.

     The Initial Developer of the Original Code is The QuiX project.
	 
	 quix@free.fr
	 http://www.quix.tk
	 http://quixplorer.sourceforge.net

****************************************************************/

class t3quixplorer_mkitem{
	function main($dir) {		
		global $LANG;

		$mkname=t3lib_div::_GP("mkname");
		$mktype=t3lib_div::_GP("mktype");
		
		$mkname=basename(stripslashes($mkname));
		if($mkname=="") t3quixplorer_div::showError($LANG->getLL("error.miscnoname"));
		
		$new = t3quixplorer_div::get_abs_item($dir,$mkname);

		if(!preg_match('/[a-zA-Z0-9\.-_]+/',$mkname)) t3quixplorer_div::showError($mkname.": ".$LANG->getLL("error.invalidfilename"));
		if(@file_exists($new)) t3quixplorer_div::showError($mkname.": ".$LANG->getLL("error.itemdoesexist"));
		
		if($mktype!="file") {
			$ok=@mkdir($new, 0777);
			$err=$LANG->getLL("error.createdir");
		} else {
			$ok=@touch($new);
			$err=$LANG->getLL("error.createfile");
		}
		
		if($ok===false) t3quixplorer_div::showError($err);
		
		header("Location: ".t3quixplorer_div::make_link("list",$dir,NULL));

	}

}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/t3quixplorer/mod1/t3quixplorer_mkitem.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/t3quixplorer/mod1/t3quixplorer_mkitem.php"]);
}
?>