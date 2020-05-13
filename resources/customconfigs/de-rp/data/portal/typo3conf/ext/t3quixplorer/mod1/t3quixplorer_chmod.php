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
 * class 'quixplorer_chmod' for the 't3quixplorer' extension.
 * Class to change permissions of a file or a folder
 *
 * @author	Mads Brunn <brunn@mail.dk>
 */
/***************************************************************

     The Original Code is fun_chmod.php, released on 2003-04-02.

     The Initial Developer of the Original Code is The QuiX project.
	 
	 quix@free.fr
	 http://www.quix.tk
	 http://quixplorer.sourceforge.net

****************************************************************/


require_once ("t3quixplorer_div.php");

class t3quixplorer_chmod{

	function main($dir, $item) {		
		global $LANG;
		
		$content=array();
		
		
		if(!file_exists(t3quixplorer_div::get_abs_item($dir, $item))) t3quixplorer_div::showError($item.": ".$LANG->getLL("error.fileexist"));
		if(!t3quixplorer_div::get_show_item($dir, $item)) t3quixplorer_div::showError($item.": ".$LANG->getLL("error.accessfile"));
		
		if(t3lib_div::_POST("confirm") && t3lib_div::_POST("confirm")=="true") {
			$bin='';
			for($i=0;$i<3;$i++) for($j=0;$j<3;$j++) {
				$tmp="r_".$i.$j;
				if(t3lib_div::_POST($tmp) && t3lib_div::_POST($tmp)=="1" ) $bin.='1';
				else $bin.='0';
			}
			
			if(!@chmod(t3quixplorer_div::get_abs_item($dir,$item),bindec($bin))) {
				t3quixplorer_div::showError($item.": ".$LANG->getLL("error.permchange"));
			}
			header("Location: ".t3quixplorer_div::make_link("link",$dir,NULL));
			return;
		}
		
		$mode = t3quixplorer_div::parse_file_perms(t3quixplorer_div::get_file_perms($dir,$item));
		if($mode===false) t3quixplorer_div::showError($item.": ".$LANG->getLL("error.permread"));
		$pos = "rwx";
		
		$s_item=t3quixplorer_div::get_rel_item($dir,$item);	if(strlen($s_item)>50) $s_item="...".substr($s_item,-47);
		//show_header($GLOBALS["messages"]["actperms"].": /".$s_item);
		
	
		// Form
		$content[]= '
		  <br />
		  <table width="175">
		    <form method="post" action="'.t3quixplorer_div::make_link("chmod",$dir,$item).'">
			<input type="hidden" name="confirm" value="true">
		  ';
			
		  // print table with current perms & checkboxes to change	
		  
		 $permgroups = array(0 => $LANG->getLL("message.miscchmodowner"),1 => $LANG->getLL("message.miscchmodgroup"),2 => $LANG->getLL("message.miscchmodpublic"));
		  
		for($i=0;$i<3;++$i) {
			$content[]= '
			  <tr>
			    <td>
					'.$permgroups[$i].'
				</td>
				';
			for($j=0;$j<3;++$j) {
				$content[]= '<td>'.$pos{$j}.'&nbsp;<input type="checkbox"';
				if($mode{(3*$i)+$j} != "-") $content[]= ' checked';
				$content[]= ' name="r_'.$i.$j.'" value="1"></td>';
			}
			$content[]= '</tr>';
		}
		
		// Submit / Cancel
		$content[]='
		  </table>
		  <br />
		  <table>
		    <tr>
			  <td>
			    <input type="submit" value="'.$LANG->getLL("message.btnchange").'">
			  </td>
			  <td>
			    <input type="button" value="'.$LANG->getLL("message.btncancel").'" onClick="javascript:location=\''.t3quixplorer_div::make_link("list",$dir,NULL).'\';">
			  </td>
			</tr>
		   </form>
		  </table>
		  <br />';
		
		
		return implode("",$content);
	}
}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/t3quixplorer/mod1/t3quixplorer_chmod.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/t3quixplorer/mod1/t3quixplorer_chmod.php"]);
}
?>
