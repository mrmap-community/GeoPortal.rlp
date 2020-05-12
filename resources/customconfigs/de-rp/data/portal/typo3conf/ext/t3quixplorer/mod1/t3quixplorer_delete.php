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
 * class 't3quixplorer_delete' for the 't3quixplorer' extension.
 * Contains functions to delete files and folders
 *
 * @author	Mads Brunn <brunn@mail.dk>
 */
/***************************************************************

     The Original Code is fun_del.php, released on 2003-03-31.

     The Initial Developer of the Original Code is The QuiX project.
	 
	 quix@free.fr
	 http://www.quix.tk
	 http://quixplorer.sourceforge.net

****************************************************************/
require_once ("t3quixplorer_div.php");


class t3quixplorer_delete{

	function main($dir) {		// delete files/dirs
		global $LANG;
		
		$selitems = t3lib_div::_POST("selitems");
		
		$cnt=count($selitems);
		$GLOBALS["T3Q_DEBUG"]["selected_delete"]=$cnt;
		$err=false;
		
		// delete files & check for errors
		for($i=0;$i<$cnt;++$i) {
			$items[$i] = stripslashes($selitems[$i]);
			$abs = t3quixplorer_div::get_abs_item($dir,$items[$i]);
		
			if(!@file_exists(t3quixplorer_div::get_abs_item($dir, $items[$i]))) {
				$error[$i]=$LANG->getLL("error.itemexist");
				$err=true;	continue;
			}
			if(!t3quixplorer_div::get_show_item($dir, $items[$i])) {
				$error[$i]=$LANG->getLL("error.accessitem");
				$err=true;	continue;
			}
			
			// Delete
			$ok=t3quixplorer_div::remove(t3quixplorer_div::get_abs_item($dir,$items[$i]));
			
			if($ok===false) {
				$error[$i]=$LANG->getLL("error.delitem");
				$err=true;	continue;
			}
			
			$error[$i]=NULL;
		}
		
		if($err) {			// there were errors
			$err_msg="";
			for($i=0;$i<$cnt;++$i) {
				if($error[$i]==NULL) continue;
				
				$err_msg .= $items[$i]." : ".$error[$i]."<br />";
			}
			t3quixplorer_div::showError($err_msg);
		}
		
		header("Location: ".t3quixplorer_div::make_link("list",$dir,NULL));
	}
}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/t3quixplorer/mod1/t3quixplorer_delete.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/t3quixplorer/mod1/t3quixplorer_delete.php"]);
}

?>
