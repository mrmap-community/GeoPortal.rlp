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
 * class 't3quixplorer_search' for the 't3quixplorer' extension.
 * contains functions to display searchform and searchresults and
 * to search for files and folders 
 *
 * @author	Mads Brunn <brunn@mail.dk>
 */
/***************************************************************

     The Original Code is fun_search.php, released on 2003-03-31.

     The Initial Developer of the Original Code is The QuiX project.
	 
	 quix@free.fr
	 http://www.quix.tk
	 http://quixplorer.sourceforge.net

****************************************************************/

require_once ("t3quixplorer_div.php");

class t3quixplorer_search{
	function find_item($dir,$pat,&$list,$recur) {	// find items
		$handle=@opendir(t3quixplorer_div::get_abs_dir($dir));
		if($handle===false) return;		// unable to open dir
		
		while(($new_item=readdir($handle))!==false) {
			if(!@file_exists(t3quixplorer_div::get_abs_item($dir, $new_item))) continue;
			if(!t3quixplorer_div::get_show_item($dir, $new_item)) continue;
			
			// match?
			if(@eregi($pat,$new_item)) $list[]=array($dir,$new_item);
			
			// search sub-directories
			if(t3quixplorer_div::get_is_dir($dir, $new_item) && $recur) {
				$this->find_item(t3quixplorer_div::get_rel_item($dir,$new_item),$pat,$list,$recur);
			}
		}
		
		closedir($handle);
	}
	//------------------------------------------------------------------------------
	function make_list($dir,$item,$subdir) {	// make list of found items
		// convert shell-wildcards to PCRE Regex Syntax
		$pat="^".str_replace("?",".",str_replace("*",".*",str_replace(".","\.",$item)))."$";
		
		// search
		$this->find_item($dir,$pat,$list,$subdir);
		if(is_array($list)) sort($list);
		return $list;
	}
	//------------------------------------------------------------------------------
	function print_table($list) {			// print table of found items
		global $BACK_PATH;

		if(!is_array($list)) return;
		
		$imagepath = t3lib_extMgm::extRelPath("t3quixplorer").'mod1/_img/';
		
		$cnt = count($list);
		for($i=0;$i<$cnt;++$i) {
			$dir = $list[$i][0];	$item = $list[$i][1];
			$s_dir=t3lib_div::fixed_lgd($dir,62);	
			$s_item=t3lib_div::fixed_lgd($item,45);
			$link = "";	$target = "";
			
			if(t3quixplorer_div::get_is_dir($dir,$item)) {
				$img = "folder.gif";
				$link = t3quixplorer_div::make_link("list",t3quixplorer_div::get_rel_item($dir, $item),NULL);
			} else {
				$img = t3quixplorer_div::get_mime_type($dir, $item, "img");
				$link = $GLOBALS["T3Q_VARS"]["home_url"]."/".t3quixplorer_div::get_rel_item($dir, $item);
				$target = "_blank";
			}
			
			$this->content[]='
				<tr>
				  <td>
				    <img border="0" align="absmiddle" '.t3lib_iconWorks::skinImg($BACK_PATH,$imagepath.$img,'width="16" height="16"',0).' alt="">&nbsp;
				    <a href="'.$link.'" target="'.$target.'">'.$s_item.'</a>
				  </td>
				  <td>
				    <a href="'.t3quixplorer_div::make_link("list",$dir,NULL).'"> /'.$s_dir.'</a>
				  </td>
				</tr>';
		}
	}
	//------------------------------------------------------------------------------
	function main($dir) {			// search for item
		global $LANG;

		$this->content = array();
		
		
	
		if(t3lib_div::_POST("searchitem")) {
			$searchitem=t3lib_div::_POST("searchitem");
			$subdir=t3lib_div::_POST("subdir")=="y";
			$list=$this->make_list($dir,$searchitem,$subdir);
		} else {
			$searchitem=NULL;
			$subdir=true;
		}
		
		$msg='';
		//if($searchitem!=NULL) $msg.=': (/' .t3quixplorer_div::get_rel_item($dir, $searchitem).')';
		//show_header($msg);
		
		// Search Box
		$this->content[]=' 
			<br />
			  <table>
			    <form name="searchform" action="'.t3quixplorer_div::make_link("search",$dir,NULL).'" method="post">
				<tr>
				  <td>
				    <input name="searchitem" type="text" size="25" value="'.$searchitem.'">
					<input type="submit" value="'.$LANG->getLL("message.btnsearch").'">&nbsp;
					<input type="button" value="'.$LANG->getLL("message.btnclose").'" onClick="javascript:location=\''.t3quixplorer_div::make_link("list",$dir,NULL).'\';">
				  </td>
				</tr>
				<tr>
				  <td>
				    <input type="checkbox" name="subdir" value="y"'.($subdir?" checked>":">").$LANG->getLL("message.miscsubdirs").'
				  </td>
				</tr>
				</form>
			  </table>';
		
		// Results
		if($searchitem!=NULL) {
			$this->content[]='
				<table width="100%" id="typo3-filelist">
				  <tr>
				    <td colspan="2"> </td>
				  </tr>';
				  
			if(count($list)>0) {
				// Table Header
				$this->content[]='
					<tr>
					  <td width="45%" class="c-headLine">'.$LANG->getLL("message.nameheader").'</td>
					  <td width="60%" class="c-headLine">'.$LANG->getLL("message.pathheader").'</td>
					</tr>
					<tr>
					  <td colspan="2"> </td>
					</tr>';
		
				// make & print table of found items
				$this->print_table($list);
	
				$this->content[]='
					<tr>
					  <td colspan="2"> </td>
					</tr>
					<tr>
					  <td class="c-headLine">'.count($list).' '.$LANG->getLL("message.miscitems").'</td>
					  <td class="c-headLine"> </td>
					</tr>
					';
			} else {
				$this->content[]='
				   <tr>
				     <td><br /><br />'.$LANG->getLL("message.miscnoresult").'</td>
				   </tr>';
			}
			$this->content[]='
				   <tr>
				     <td colspan="2">  </TD>
				   </tr>
				 </table>';
		}
		
		return implode("",$this->content);
	}
}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/t3quixplorer/mod1/t3quixplorer_search.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/t3quixplorer/mod1/t3quixplorer_search.php"]);
}

?>