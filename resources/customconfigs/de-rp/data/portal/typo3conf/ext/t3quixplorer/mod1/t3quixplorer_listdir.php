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
 * class 't3quixplorer_listdir' for the 't3quixplorer' extension.
 * Contains functions to display the files and folders in a directory
 *
 * @author	Mads Brunn <brunn@mail.dk>
 */
/***************************************************************

     The Original Code is fun_list.php, released on 2003-03-31.

     The Initial Developer of the Original Code is The QuiX project.
	 
	 quix@free.fr
	 http://www.quix.tk
	 http://quixplorer.sourceforge.net

****************************************************************/

require_once ("t3quixplorer_div.php");

class t3quixplorer_listdir{

		
	function make_list($_list1, $_list2) {		// make list of files
		if($GLOBALS["T3Q_VARS"]["srt"]=="yes") {
			$list1 = $_list1;
			$list2 = $_list2;
		} else {
			$list1 = $_list2;
			$list2 = $_list1;
		}
		
		if(is_array($list1)) {
			while (list($key, $val) = each($list1)) {
				$list[$key] = $val;
			}
		}
		
		if(is_array($list2)) {
			while (list($key, $val) = each($list2)) {
				$list[$key] = $val;
			}
		}
		return $list;
	}


	function make_tables($dir){						// make table of files in dir
		global $LANG;
		$this->dir_list = array();
		$this->file_list = array();
		$this->tot_file_size = 0;
		$this->num_items = 0;
		
		// make tables 
		// also 'return' total filesize & total number of items
		
		
		// Open directory
		$handle = @opendir(t3quixplorer_div::get_abs_dir($dir));
		if($handle===false){
			t3quixplorer_div::showError($LANG->getLL("error.opendir"));
		}
		

		// Read directory
#
#	THIS WHILE LOOP HAS BEEN REPLACED WITH THE CODE BELOW 
#
#		while(($new_item = readdir($handle))!==false) {
#			$abs_new_item = t3quixplorer_div::get_abs_item($dir, $new_item);
#			
#			if(!@file_exists($abs_new_item)){
#				t3quixplorer_div::showError($LANG->getLL("error.readdir"));
#			}
#			if(!t3quixplorer_div::get_show_item($dir, $new_item)) continue;
#			
#			$new_file_size = filesize($abs_new_item);
#			$this->tot_file_size += $new_file_size;
#			$this->num_items++;
#			
#			if(t3quixplorer_div::get_is_dir($dir, $new_item)) {
#				if($GLOBALS["T3Q_VARS"]["order"]=="mod") {
#					$this->dir_list[$new_item] = @filemtime($abs_new_item);
#				} else {	// order == "size", "type" or "name"
#					$this->dir_list[$new_item] = $new_item;
#				}
#			} else {
#				if($GLOBALS["T3Q_VARS"]["order"]=="size") {
#					$this->file_list[$new_item] = $new_file_size;
#				} elseif($GLOBALS["T3Q_VARS"]["order"]=="mod") {
#					$this->file_list[$new_item] = @filemtime($abs_new_item);
#				} elseif($GLOBALS["T3Q_VARS"]["order"]=="type") {
#					$this->file_list[$new_item] = t3quixplorer_div::get_mime_type($dir, $new_item, "type");
#				} else {	// order == "name"
#					$this->file_list[$new_item] = $new_item;
#				}
#			}
#		}


		#	New while-loop as of vers. 1.6
		#	Suggested by Dimitrij Denissenko
		#
		#	"The loop parses all filenames. If  "file_exist()" fails, the whole processing is terminated 
		#	with an error, but it shouldn't. There are always some files/directories (e.g. symbolic links, 
		#	account configuration files)  on my server accounts that are NOT web-readable (because of 
		#	open_basedir restriction or insufficient rights), so I always get a "error.readdir" message when 
		#	a directory contains such a file.  From that point it wold me much wiser not to show these files 
		#	but continue processing. 

		while(($new_item = readdir($handle))!==false) {
			$abs_new_item = t3quixplorer_div::get_abs_item($dir, $new_item);

			if(@file_exists($abs_new_item)){
				if(!t3quixplorer_div::get_show_item($dir, $new_item)) continue;
    
				$new_file_size = filesize($abs_new_item);
				$this->tot_file_size += $new_file_size;
				$this->num_items++;
    
				if(t3quixplorer_div::get_is_dir($dir, $new_item)) {

					if($GLOBALS["T3Q_VARS"]["order"]=="mod") {
						$this->dir_list[$new_item] = @filemtime($abs_new_item);
					} else {    // order == "size", "type" or "name"
						$this->dir_list[$new_item] = $new_item;
					}

				} else {
					if($GLOBALS["T3Q_VARS"]["order"]=="size") {
						$this->file_list[$new_item] = $new_file_size;
					} elseif($GLOBALS["T3Q_VARS"]["order"]=="mod") {
						$this->file_list[$new_item] = @filemtime($abs_new_item);
					} elseif($GLOBALS["T3Q_VARS"]["order"]=="type") {
						$this->file_list[$new_item] = t3quixplorer_div::get_mime_type($dir, $new_item, "type");
					} else {    // order == "name"
						$this->file_list[$new_item] = $new_item;
					}
				}
			}
		}


		closedir($handle);
		
		
		// sort
		if(is_array($this->dir_list)) {
			if($GLOBALS["T3Q_VARS"]["order"]=="mod") {
				if($GLOBALS["T3Q_VARS"]["srt"]=="yes") arsort($this->dir_list);
				else asort($this->dir_list);
			} else {	// order == "size", "type" or "name"
				if($GLOBALS["T3Q_VARS"]["srt"]=="yes") ksort($this->dir_list);
				else krsort($this->dir_list);
			}
		}
		
		// sort
		if(is_array($this->file_list)) {
			if($GLOBALS["T3Q_VARS"]["order"]=="mod") {
				if($GLOBALS["T3Q_VARS"]["srt"]=="yes") arsort($this->file_list);
				else asort($this->file_list);
			} elseif($GLOBALS["T3Q_VARS"]["order"]=="size" || $GLOBALS["T3Q_VARS"]["order"]=="type") {
				if($GLOBALS["T3Q_VARS"]["srt"]=="yes") asort($this->file_list);
				else arsort($this->file_list);
			} else {	// order == "name"
				if($GLOBALS["T3Q_VARS"]["srt"]=="yes") ksort($this->file_list);
				else krsort($this->file_list);
			}
		}
	}

	
	function print_table($dir, $list, $allow){
		global $LANG,$BACK_PATH;
		if(!is_array($list)) return;
		$imagepath = t3lib_extMgm::extRelPath("t3quixplorer").'mod1/_img/';
		
		//$imagepath = $BACKPATH.'/gfx/fileicons/';
		
		$classname = "bgColor-10";

		while(list($item,) = each($list)){
			// link to dir / file
			$abs_item=t3quixplorer_div::get_abs_item($dir,$item);
			$target="";
			//$extra="";
			//if(is_link($abs_item)) $extra=" -> ".@readlink($abs_item);
			if(is_dir($abs_item)) {
				$link = t3quixplorer_div::make_link("list",t3quixplorer_div::get_rel_item($dir, $item),NULL);
			} else { //if(get_is_editable($dir,$item) || get_is_image($dir,$item)) {
				$link = $GLOBALS["T3Q_VARS"]["home_url"]."/".t3quixplorer_div::get_rel_item($dir, $item);
				$target = "_blank";
			} //else $link = "";
			
			//checkbox
			$this->content[]='
				  <tr class="'.$classname.'">
				    <td nowrap="nowrap"><input type="checkbox" name="selitems[]" value="'.$item.'" onclick="javascript:Toggle(this);"></td>';

			//link
			
			$this->content[]='
				        <td nowrap="nowrap"><a href="'.$link.'" target="'.$target.'"><img align="absmiddle" border="0" '.t3lib_iconWorks::skinImg($BACK_PATH,$imagepath.t3quixplorer_div::get_mime_type($dir, $item, "img"),'width="16" height="16"',0).' alt="">&nbsp;'.t3lib_div::fixed_lgd($item,47).'</a></td>
						';


			$this->content[]='
					<td nowrap="nowrap">'.t3quixplorer_div::parse_file_size(t3quixplorer_div::get_file_size($dir,$item)).'</td>	
					';

			$this->content[]='
					<td nowrap="nowrap">'.t3quixplorer_div::get_mime_type($dir, $item, "type").'</td>	
					';
					
		//echo "<TD>".get_mime_type($dir, $item, "type")."</TD>\n";

			$this->content[]='
					<td nowrap="nowrap" >'.t3quixplorer_div::parse_file_date(t3quixplorer_div::get_file_date($dir,$item)).'</td>	
					';

			$this->content[]='
					<td nowrap="nowrap" >
						<a href="'.t3quixplorer_div::make_link("chmod",$dir,$item).'" title="'.$LANG->getLL("message.permlink").'">
						'.t3quixplorer_div::parse_file_type($dir,$item).t3quixplorer_div::parse_file_perms(t3quixplorer_div::get_file_perms($dir,$item)).'
						</a>
					</td>
					';
			
			
			
			$this->content[]='
					<td nowrap="nowrap" >
					  <table>
					    <tr>';
			if(t3quixplorer_div::get_is_editable($dir, $item)) {
				$this->content[]='
					<td>
					  <a href="'.t3quixplorer_div::make_link("edit",$dir,$item).'">
					    <img border="0" align="absmiddle" '.t3lib_iconWorks::skinImg($BACK_PATH,$imagepath.'_edit2.gif','width="16" height="16"',0).' alt="'.$LANG->getLL("message.editlink").'" title="'.$LANG->getLL("message.editlink").'">
					  </a>
					</td>
				';
			} else {
				$this->content[]='
					<td>
					  <img border="0" align="absmiddle" '.t3lib_iconWorks::skinImg($BACK_PATH,$imagepath.'_noedit2.gif','width="16" height="16"',0).' alt="'.$LANG->getLL("message.editlink").'" title="'.$LANG->getLL("message.editlink").'">
					</td>
				';
			}

			if(t3quixplorer_div::get_is_archive($dir,$item)){
				$this->content[]='
					<td>
					<a href="'.t3quixplorer_div::make_link("extract",$dir,$item).'">
					    <img border="0" align="absmiddle"  '.t3lib_iconWorks::skinImg($BACK_PATH,$imagepath.'_extract.gif','width="16" height="16"',0).' alt="'.$LANG->getLL("message.extractlink").'" title="'.$LANG->getLL("message.extractlink").'">
					</a>
					</td>
					';				
			} else {
				$this->content[]='
					<td>
					  <img border="0" align="absmiddle" '.t3lib_iconWorks::skinImg($BACK_PATH,$imagepath.'_noextract.gif','width="16" height="16"',0).' alt="'.$LANG->getLL("message.extractlink").'" title="'.$LANG->getLL("message.extractlink").'">
					</td>
				';
			}


			$this->content[]='
				<td>
				<a href="'.t3quixplorer_div::make_link("rename",$dir,$item).'">
				    <img border="0" align="absmiddle"  '.t3lib_iconWorks::skinImg($BACK_PATH,$imagepath.'_rename.gif','width="16" height="16"',0).' alt="'.$LANG->getLL("message.renamelink").'" title="'.$LANG->getLL("message.renamelink").'">
				</a>
				</td>
				';

			if(t3quixplorer_div::get_is_file($dir,$item)) {
				$this->content[]='
					<td>
					<a href="'.t3quixplorer_div::make_link("download",$dir,$item).'">
					    <img border="0" align="absmiddle"  '.t3lib_iconWorks::skinImg($BACK_PATH,$imagepath.'_download.gif','width="16" height="16"',0).' alt="'.$LANG->getLL("message.downlink").'" title="'.$LANG->getLL("message.downlink").'">
					</a>
					</td>
					';
			} else {
				$this->content[]='
					<td>
					  <img border="0" align="absmiddle" '.t3lib_iconWorks::skinImg($BACK_PATH,$imagepath.'_nodownload.gif','width="16" height="16"',0).' alt="'.$LANG->getLL("message.downlink").'" title="'.$LANG->getLL("message.downlink").'">
					</td>
				';
			}



			$this->content[]='			
					    </tr>  
					  </table>
					</td>
				  </tr>
					';
			
			if($GLOBALS["T3Q_VARS"]["show_thumbs"] && t3quixplorer_div::get_is_image($dir, $item)){

				$this->content[]='
			  	      <tr class="'.$classname.'">
			  	      	<td>&nbsp;</td>
			  	      	<td nowrap="nowrap"><a href="'.$link.'" target="'.$target.'">'.t3lib_BEfunc::getThumbNail($BACK_PATH.'thumbs.php',$abs_item).'</a></td>
			  	      	<td>&nbsp;</td>	
			  	      	<td>&nbsp;</td>
			  	      	<td>&nbsp;</td>
			  	      	<td>&nbsp;</td>
			  	      	<td>&nbsp;</td>
			  	    </tr>  	
						';
			}

			if($classname == "bgColor-10"){
				$classname = "bgColor-20";
			} else {
				$classname = "bgColor-10";
			}

		}
	}
	
	
	function getIsFiles(){
		$files = unserialize(stripslashes($_COOKIE["copymoveitems"]));
		return (count($files) && is_array($files));
	}
	
	
	function main($dir){
		global $LANG,$BACK_PATH;
		$allow=($GLOBALS["T3Q_VARS"]["permissions"]&01)==01;
		$admin=((($GLOBALS["T3Q_VARS"]["permissions"]&04)==04) || (($GLOBALS["T3Q_VARS"]["permissions"]&02)==02));

		$dir_up = dirname($dir);
		if($dir_up==".") $dir_up = "";
	
		if(!t3quixplorer_div::get_show_item($dir_up,basename($dir))){
			t3quixplorer_div::showError($LANG->getLL("error.accesdir"));
		} 

		
		$this->make_tables($dir);
		$s_dir = t3lib_div::fixed_lgd($dir,47);
		$imagepath = t3lib_extMgm::extRelPath("t3quixplorer").'mod1/_img/';

		$_img = '&nbsp;<img width="10" height="10" border="0" align="absmiddle" src="'.t3lib_extMgm::extRelPath("t3quixplorer").'mod1/_img/';
		
		if($GLOBALS["T3Q_VARS"]["srt"]=="yes") {
			$_srt = 'no';	
			$_img = '&nbsp;<img align="absmiddle" border="0" '.t3lib_iconWorks::skinImg($BACK_PATH,$imagepath.'_arrowup.gif','width="10" height="10"',0).' alt="^">';
		} else {
			$_srt = 'yes';	

			$_img = '&nbsp;<img align="absmiddle" border="0" '.t3lib_iconWorks::skinImg($BACK_PATH,$imagepath.'_arrowdown.gif','width="10" height="10"',0).' alt="v">';
		}

		//toolbar start				
		$this->content[]='
		<br />
		  <table cellpadding="0" cellspacing="0" border="0" width="100%">
		    <tr>
			  <td>
			    <table>
				  <tr>
				    <td>
					  <a href="'.t3quixplorer_div::make_link("list",$dir_up,NULL).'">
					    <img border="0" align="absmiddle" '.t3lib_iconWorks::skinImg($BACK_PATH,$imagepath.'_up.gif','width="16" height="16"',0).' alt="'.$LANG->getLL("message.uplink").'" title="'.$LANG->getLL("message.uplink").'">
					  </a>
					</td>
					<td>
					  <a href="'.t3quixplorer_div::make_link("list",NULL,NULL).'">
						<img border="0" align="absmiddle" '.t3lib_iconWorks::skinImg($BACK_PATH,$imagepath.'_home.gif','width="16" height="16"',0).' alt="'.$LANG->getLL("message.homelink").'" title="'.$LANG->getLL("message.homelink").'">
					  </a>									
					</td>
					<td>
					  <a href="javascript:location.reload();">
						<img border="0" align="absmiddle" '.t3lib_iconWorks::skinImg($BACK_PATH,$imagepath.'_refresh.gif','width="16" height="16"',0).' alt="'.$LANG->getLL("message.reloadlink").'" title="'.$LANG->getLL("message.reloadlink").'">
					  </a>														
					</td>
					<td>
					  <a href="'.t3quixplorer_div::make_link("search",$dir,NULL).'">
						<img border="0" align="absmiddle" '.t3lib_iconWorks::skinImg($BACK_PATH,$imagepath.'_search.gif','width="16" height="16"',0).' alt="'.$LANG->getLL("message.searchlink").'" title="'.$LANG->getLL("message.searchlink").'">
					  </a>									
					</td>
					<td>::</td>
					<td>
					  <a href="javascript:Copy();">
						<img border="0" align="absmiddle" '.t3lib_iconWorks::skinImg($BACK_PATH,$imagepath.'__copy.gif','width="16" height="16"',0).' alt="'.$LANG->getLL("message.copylink").'" title="'.$LANG->getLL("message.copylink").'">
					  </a>									
					</td>
					<td>
					  <a href="javascript:Move();">
						<img border="0" align="absmiddle" '.t3lib_iconWorks::skinImg($BACK_PATH,$imagepath.'__cut.gif','width="16" height="16"',0).' alt="'.$LANG->getLL("message.movelink").'" title="'.$LANG->getLL("message.movelink").'">
					  </a>									
					</td>';

			
			if($this->getIsFiles()){
				$this->content[]='
						<td>
						  <a href="javascript:Paste();">
							<img border="0" align="absmiddle" '.t3lib_iconWorks::skinImg($BACK_PATH,$imagepath.'__paste.gif','width="16" height="16"',0).' alt="'.$LANG->getLL("message.pastelink").'" title="'.$LANG->getLL("message.pastelink").'">
						  </a>									
						</td>
						<td>
						  <a href="javascript:Clear();">
							<img border="0" align="absmiddle" '.t3lib_iconWorks::skinImg($BACK_PATH,$imagepath.'__clear.gif','width="16" height="16"',0).' alt="'.$LANG->getLL("message.pastelink").'" title="'.$LANG->getLL("message.clearlink").'">
						  </a>									
						</td>';

			}
					
			$this->content[]='
					<td>
					  <a href="javascript:Delete();">
						<img border="0" align="absmiddle" '.t3lib_iconWorks::skinImg($BACK_PATH,$imagepath.'_delete.gif','width="16" height="16"',0).' alt="'.$LANG->getLL("message.dellink").'" title="'.$LANG->getLL("message.dellink").'">
					  </a>									
					</td>';

		if(get_cfg_var("file_uploads")) {
			$this->content[]='
					<td>
					  <a href="'.t3quixplorer_div::make_link("upload",$dir,NULL).'">
						<img border="0" align="absmiddle" '.t3lib_iconWorks::skinImg($BACK_PATH,$imagepath.'_upload.gif','width="16" height="16"',0).' alt="'.$LANG->getLL("message.uploadlink").'" title="'.$LANG->getLL("message.uploadlink").'">
					  </a>									
					</td>';
		} else {
			$this->content[]='
					<td>
					  <img border="0" align="absmiddle" '.t3lib_iconWorks::skinImg($BACK_PATH,$imagepath.'_upload_.gif','width="16" height="16"',0).' alt="'.$LANG->getLL("message.uploadlink").'" title="'.$LANG->getLL("message.uploadlink").'">
					</td>';		
		}
		$this->content[] = '
					<td>
					  <a href="javascript:Archive();">
						<img border="0" align="absmiddle" '.t3lib_iconWorks::skinImg($BACK_PATH,$imagepath.'_archive.gif','width="16" height="16"',0).' alt="'.$LANG->getLL("message.comprlink").'" title="'.$LANG->getLL("message.comprlink").'">
					  </a>									
					</td>
					';
		
		//ADD ADDITIONAL ICONS TO THE TOOLBAR HERE
		
		$this->content[]='
				  </tr>
				</table>
			   </td>';
		$this->content[]='
			   <td align="right">
			     <table>
				   <form action="'.t3quixplorer_div::make_link("mkitem",$dir,NULL).'" method="POST">
				     <tr>
					   <td>
		                 <select name="mktype">
						   <option value="file">'.$LANG->getLL("mime.file").'</option>
						   <option value="dir">'.$LANG->getLL("mime.dir").'</option>
						 </select>
						 <input name="mkname" type="text" size="15">
						 <input type="submit" value="'.$LANG->getLL("message.btncreate").'">
	                   </td>
					 </tr>
				   </form>
				 </table>					
			   </td>';
		$this->content[]='			
			 </tr>
		   </table>';
		   
		//toolbar end
		
		
		//$this->content[]= $LANG->getLL("message.actdir").": /".t3quixplorer_div::get_rel_item("",$s_dir);		

		
		
		//headers start
/*
		$this->content[]='
		   <table width="100%" cellspacing="0" cellpadding="0" border="0" id="typo3-filelist">
		     <form name="selform" method="POST" action="'.t3quixplorer_div::make_link("post",$dir,NULL).'">
			 <input type="hidden" name="do_action">
			 <input type="hidden" name="first" value="y">
		     <tr>
			   <td colspan="7"><br />'.$LANG->getLL("message.actdir").": /".t3quixplorer_div::get_rel_item("",$s_dir).'<br /><br /></td>
			 </tr>';
*/

		//08.08.2005 Changed display of path.

		$this->content[]='
		   <table width="100%" cellspacing="0" cellpadding="0" border="0" id="typo3-filelist">
		     <form name="selform" method="POST" action="'.t3quixplorer_div::make_link("post",$dir,NULL).'">
			 <input type="hidden" name="do_action">
			 <input type="hidden" name="first" value="y">
		     <tr>
			   <td colspan="7"><br />'.$LANG->getLL("message.actdir").': /<input type="text" size="50" name="jumpdir" value="'.t3quixplorer_div::get_rel_item("",$s_dir).'">&nbsp;<input type="submit" value="'.$LANG->getLL("message.btncd").'" name="jumptodir"><br /><br /></td>
			 </tr>';



		$this->content[]='
			 <tr>
			   <td width="2%" class="c-headLine" nowrap="nowrap">
	             <input type="checkbox" name="toggleAllC" onclick="javascript:ToggleAll(this);">
			   </td>';
			   
		if($GLOBALS["T3Q_VARS"]["order"]=="name") $new_srt = $_srt;	else $new_srt = "yes";
		$this->content[]='
			   <td width="44%" class="c-headLine" nowrap="nowrap">
			     <a href="'.t3quixplorer_div::make_link("list",$dir,NULL,"name",$new_srt).'">'.$LANG->getLL("message.nameheader").($GLOBALS["T3Q_VARS"]["order"]=="name"? $_img :'').'</a>
			   </td>';

		if($GLOBALS["T3Q_VARS"]["order"]=="size") $new_srt = $_srt;	else $new_srt = "yes";
		$this->content[]='
			   <td width="10%" class="c-headLine" nowrap="nowrap">
			     <a href="'.t3quixplorer_div::make_link("list",$dir,NULL,"size",$new_srt).'">'.$LANG->getLL("message.sizeheader").($GLOBALS["T3Q_VARS"]["order"]=="size"? $_img :'').'</a>
			   </td>';

		if($GLOBALS["T3Q_VARS"]["order"]=="type") $new_srt = $_srt;	else $new_srt = "yes";
		$this->content[]='
			   <td width="16%" class="c-headLine" nowrap="nowrap">
			     <a href="'.t3quixplorer_div::make_link("list",$dir,NULL,"type",$new_srt).'">'.$LANG->getLL("message.typeheader").($GLOBALS["T3Q_VARS"]["order"]=="type"? $_img :'').'</a>
			   </td>';

		if($GLOBALS["T3Q_VARS"]["order"]=="mod") $new_srt = $_srt;	else $new_srt = "yes";
		$this->content[]='
			   <td width="14%" class="c-headLine" nowrap="nowrap">
			     <a href="'.t3quixplorer_div::make_link("list",$dir,NULL,"mod",$new_srt).'">'.$LANG->getLL("message.modifheader").($GLOBALS["T3Q_VARS"]["order"]=="mod"? $_img :'').'</a>
			   </td>';
		$this->content[]='
			   <td width="8%" class="c-headLine" nowrap="nowrap">'.$LANG->getLL("message.permheader").'</td>
			   <td width="6%" class="c-headLine" nowrap="nowrap">'.$LANG->getLL("message.actionheader").'</td>
			 </tr>
			 <tr>
			   <td colspan="7">&nbsp;</td>
			 </tr>';
			 
		//headers end
				
		$this->print_table($dir,$this->make_list($this->dir_list,$this->file_list),$allow);

		
		if(function_exists("disk_free_space")) {
			$free=t3quixplorer_div::parse_file_size(disk_free_space(t3quixplorer_div::get_abs_dir($dir)));
		} elseif(function_exists("diskfreespace")) {
			$free=t3quixplorer_div::parse_file_size(diskfreespace(t3quixplorer_div::get_abs_dir($dir)));
		} else $free="?";

		
		$this->content[]='
			<tr>
			   <td colspan="7">&nbsp;</td>
			 </tr>
			 <tr>
			   <td class="c-headLine"></td>
			   <td class="c-headLine">'.$this->num_items.' '.$LANG->getLL("message.miscitems").' ('.$LANG->getLL("message.miscfree").': '.$free.')</td>
			   <td class="c-headLine">'.t3quixplorer_div::parse_file_size($this->tot_file_size).'</td>
			   <td class="c-headLine"> </td>
			   <td class="c-headLine"> </td>
			   <td class="c-headLine"> </td>
			   <td class="c-headLine"> </td>
			 </tr>
			 <tr>
			   <td colspan="7">&nbsp;</td>
			 </tr>
			 ';

		
		
		$this->content[]='
		     </form>
		   </table>';


		
		return implode("",$this->content);
	}
}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/t3quixplorer/mod1/t3quixplorer_listdir.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/t3quixplorer/mod1/t3quixplorer_listdir.php"]);
}

?>