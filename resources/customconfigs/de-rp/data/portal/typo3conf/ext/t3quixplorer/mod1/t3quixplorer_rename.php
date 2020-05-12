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
*  The GNU General Public License can  be found at
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
 * class 'quixplorer_rename' for the 't3quixplorer' extension.
 * Class to a edit a text file
 *
 * @author	Mads Brunn <brunn@mail.dk>
 */




require_once ("t3quixplorer_div.php");

class t3quixplorer_rename{



	
	function main($dir, $item) {		
		global $LANG;
		$this->content= array();

		if(!t3quixplorer_div::get_show_item($dir, $item)) t3quixplorer_div::showError($item.": ".$LANG->getLL("error.accessfile"));

		if(t3lib_div::_GP("cancel")){
			header("Location: ".t3quixplorer_div::make_link("list",$dir,NULL));
		}


		if(t3lib_div::_GP("dorename") &&  $fname = t3lib_div::_GP("fname")){
			if(!preg_match('/[a-zA-Z0-9\.-_]+/',$fname)) t3quixplorer_div::showError($fname.": ".$LANG->getLL("error.invalidfilename"));
			$oldname = t3quixplorer_div::get_abs_item($dir, $item);
			$newname = t3quixplorer_div::get_abs_item($dir, $fname);
			if(!strlen(trim($newname))){
				t3quixplorer_div::showError($LANG->getLL("error.miscnoname"));	
			}

			if(@file_exists($newname)){
				 t3quixplorer_div::showError($newname.": ".$LANG->getLL("error.itemdoesexist"));
			}

			if(rename($oldname,$newname)){
				header("Location: ".t3quixplorer_div::make_link("list",$dir,NULL));
			} else {
				t3quixplorer_div::showError($oldname.": ".$LANG->getLL("error.renamefailed"));
			}
		}



		$this->content[] = '
			<br /><br /><form name="renamefrm" method="post" action="'.t3quixplorer_div::make_link("rename",$dir,$item).'">
			'.$dir.'/&nbsp;<input type="text" name="fname" value="'.$item.'" size="30"><br /><br />
			<input type="submit" value="'.$LANG->getLL('message.btnrename').'" name="dorename">&nbsp;<input type="submit" value="'.$LANG->getLL('message.btncancel').'" name="cancel">
			</form>
		';
		return implode("",$this->content);
	}



}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/t3quixplorer/mod1/t3quixplorer_rename.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/t3quixplorer/mod1/t3quixplorer_rename.php"]);
}

?>