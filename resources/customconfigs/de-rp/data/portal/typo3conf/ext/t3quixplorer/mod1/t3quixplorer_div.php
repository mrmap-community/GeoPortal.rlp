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
 * class 'quixplorer_div' for the 't3quixplorer' extension.
 * contains helper functions used in many of the other classes of this extension
 *
 * @author	Mads Brunn <brunn@mail.dk>
 */
/***************************************************************

     The Original Code is fun_extra.php, released on 2003-04-02.

     The Initial Developer of the Original Code is The QuiX project.
	 
	 quix@free.fr
	 http://www.quix.tk
	 http://quixplorer.sourceforge.net

****************************************************************/



class t3quixplorer_div{


	function make_link($_action,$_dir,$_item=NULL,$_order=NULL,$_srt=NULL,$_lang=NULL) {
							// make link to next page
		if($_action=="" || $_action==NULL) $_action="list";
		if($_dir=="") $_dir=NULL;
		if($_item=="") $_item=NULL;
		if($_order==NULL) $_order=$GLOBALS["T3Q_VARS"]["order"];
		if($_srt==NULL) $_srt=$GLOBALS["T3Q_VARS"]["srt"];
		
		$link="index.php"."?action=".$_action;
		if($_dir!=NULL) $link.="&dir=".$_dir;
		if($_item!=NULL) $link.="&item=".$_item;
		if($_order!=NULL) $link.="&order=".$_order;
		if($_srt!=NULL) $link.="&srt=".$_srt;
		if($_lang!=NULL) $link.="&lang=".$_lang;
		
		return $link;
	}
	//------------------------------------------------------------------------------
	function get_abs_dir($dir) {			// get absolute path
		$abs_dir=$GLOBALS["T3Q_VARS"]["home_dir"];
		if($dir!="") $abs_dir.="/".$dir;
		return $abs_dir;
	}
	//------------------------------------------------------------------------------
	function get_abs_item($dir, $item) {		// get absolute file+path
		return t3quixplorer_div::get_abs_dir($dir)."/".$item;
	}
	//------------------------------------------------------------------------------
	function get_rel_item($dir,$item) {		// get file relative from home
		if($dir!="") return $dir."/".$item;
		else return $item;
	}
	//------------------------------------------------------------------------------
	function get_is_file($dir, $item) {		// can this file be edited?
		return @is_file(t3quixplorer_div::get_abs_item($dir,$item));
	}
	//------------------------------------------------------------------------------
	function get_is_dir($dir, $item) {		// is this a directory?
		return @is_dir(t3quixplorer_div::get_abs_item($dir,$item));
	}
	//------------------------------------------------------------------------------
	function parse_file_type($dir,$item) {		// parsed file type (d / l / -)
		$abs_item = t3quixplorer_div::get_abs_item($dir, $item);
		if(@is_dir($abs_item)) return "d";
		if(@is_link($abs_item)) return "l";
		return "-";
	}
	//------------------------------------------------------------------------------
	function get_file_perms($dir,$item) {		// file permissions
		return @decoct(@fileperms(t3quixplorer_div::get_abs_item($dir,$item)) & 0777);
	}
	//------------------------------------------------------------------------------
	function parse_file_perms($mode) {		// parsed file permisions
		if(strlen($mode)<3) return "---------";
		$parsed_mode="";
		for($i=0;$i<3;$i++) {
			// read
			if(($mode{$i} & 04)) $parsed_mode .= "r";
			else $parsed_mode .= "-";
			// write
			if(($mode{$i} & 02)) $parsed_mode .= "w";
			else $parsed_mode .= "-";
			// execute
			if(($mode{$i} & 01)) $parsed_mode .= "x";
			else $parsed_mode .= "-";
		}
		return $parsed_mode;
	}
	//------------------------------------------------------------------------------
	function get_file_size($dir, $item) {		// file size
		return @filesize(t3quixplorer_div::get_abs_item($dir, $item));
	}
	//------------------------------------------------------------------------------
	function parse_file_size($size) {		// parsed file size
		if($size >= 1073741824) {
			$size = round($size / 1073741824 * 100) / 100 . " GB";
		} elseif($size >= 1048576) {
			$size = round($size / 1048576 * 100) / 100 . " MB";
		} elseif($size >= 1024) {
			$size = round($size / 1024 * 100) / 100 . " KB";
		} else $size = $size . " Bytes";
		if($size==0) $size="-";
	
		return $size;
	}
	//------------------------------------------------------------------------------
	function get_file_date($dir, $item) {		// file date
		return @filemtime(t3quixplorer_div::get_abs_item($dir, $item));
	}
	//------------------------------------------------------------------------------
	function parse_file_date($date) {		// parsed file date
		return @date($GLOBALS["T3Q_VARS"]["date_fmt"],$date);
	}
	//------------------------------------------------------------------------------
	function get_is_image($dir, $item) {		// is this file an image?
		if(!t3quixplorer_div::get_is_file($dir, $item)) return false;
		$fileinfo = t3lib_div::split_fileref(t3quixplorer_div::get_abs_item($dir,$item));
		return t3lib_div::inList($GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],strtolower($fileinfo['fileext']));
	}
	//-----------------------------------------------------------------------------
	function get_is_editable($dir, $item) {		// is this file editable?
		if(!t3quixplorer_div::get_is_file($dir, $item)) return false;
		if(@eregi($GLOBALS["T3Q_VARS"]["editable_ext"] ,$item)) return true;
		return false;
	}
	function get_is_archive($dir, $item)  {         
		if (eregi('\.zip$|\.tar\.gz$|\.tgz$|\.tar\.bz2$|\.tbz2$', $item)) {
			return true;
		} else {
			return false;
		}        
	}

	//-----------------------------------------------------------------------------
	function get_mime_type($dir, $item, $query) {	// get file's mimetype
		if(t3quixplorer_div::get_is_dir($dir, $item)) {			// directory
			$mime_type	= $GLOBALS["T3Q_VARS"]["super_mimes"]["dir"][0];
			$image		= $GLOBALS["T3Q_VARS"]["super_mimes"]["dir"][1];
			
			if($query=="img") return $image;
			else return $mime_type;
		}
					// mime_type
		foreach($GLOBALS["T3Q_VARS"]["used_mime_types"] as $mime) {
			list($desc,$img,$ext)	= $mime;
			if(@eregi($ext,$item)) {
				$mime_type	= $desc;
				$image		= $img;
				if($query=="img") return $image;
				else return $mime_type;
			}
		}
		
		if((function_exists("is_executable") &&
			@is_executable(t3quixplorer_div::get_abs_item($dir,$item))) ||
			@eregi($GLOBALS["T3Q_VARS"]["super_mimes"]["exe"][2],$item))		
		{						// executable
			$mime_type	= $GLOBALS["T3Q_VARS"]["super_mimes"]["exe"][0];
			$image		= $GLOBALS["T3Q_VARS"]["super_mimes"]["exe"][1];
		} else {					// unknown file
			$mime_type	= $GLOBALS["T3Q_VARS"]["super_mimes"]["file"][0];
			$image		= $GLOBALS["T3Q_VARS"]["super_mimes"]["file"][1];
		}
		
		if($query=="img") return $image;
		else return $mime_type;
	}
	//------------------------------------------------------------------------------
	function get_show_item($dir, $item) {		// show this file?
		if($item == "." || $item == ".." ||	(substr($item,0,1)=="." && intval($GLOBALS["T3Q_VARS"]["show_hidden"])==0)) return false;
			
		if($GLOBALS["T3Q_VARS"]["no_access"]!="" && @eregi($GLOBALS["T3Q_VARS"]["no_access"],$item)) return false;
		
		//if(intval($GLOBALS["T3Q_VARS"]["show_hidden"])==0) {
		//	$dirs=explode("/",$dir);
		//	foreach($dirs as $i) if(substr($i,0,1)==".") return false;
		//}
		
		return true;
	}
	//------------------------------------------------------------------------------
	function copy_dir($source,$dest) {		// copy dir
		global $LANG;
		$ok = true;
		
		if(!@mkdir($dest,0777)) return false;
		if(($handle=@opendir($source))===false){
			t3quixplorer_div::showError($LANG->getLL("error.opendir"));
			return false;
		}

		while(($file=readdir($handle))!==false) {
			if(($file==".." || $file==".")) continue;
			
			$new_source = $source."/".$file;
			$new_dest = $dest."/".$file;
			if(@is_dir($new_source)) {
				$ok=copy_dir($new_source,$new_dest);
			} else {
				$ok=@copy($new_source,$new_dest);
			}
		}
		closedir($handle);
		return $ok;
	}
	//------------------------------------------------------------------------------
	function remove($item) {			// remove file / dir
		$ok = true;
		if(@is_link($item) || @is_file($item)) $ok=@unlink($item);
		elseif(@is_dir($item)) {
			if(($handle=@opendir($item))===false){
				t3quixplorer_div::showError($LANG->getLL("error.opendir"));
				return false;
			}
	
			while(($file=readdir($handle))!==false) {
				if(($file==".." || $file==".")) continue;
				
				$new_item = $item."/".$file;
				if(!@file_exists($new_item)){
					t3quixplorer_div::showError($LANG->getLL("error.readdir"));
					return false;
				}
				//if(!t3quixplorer_div::get_show_item($item, $new_item)) continue;
				
				if(@is_dir($new_item)) {
					$ok=t3quixplorer_div::remove($new_item);
				} else {
					$ok=@unlink($new_item);
				}
			}
			
			closedir($handle);
			$ok=@rmdir($item);
		}
		return $ok;
	}
	//------------------------------------------------------------------------------
	function get_max_file_size() {			// get php max_upload_file_size
		$max = get_cfg_var("upload_max_filesize");
		if(@eregi("G$",$max)) {
			$max = substr($max,0,-1);
			$max = round($max*1073741824);
		} elseif(@eregi("M$",$max)) {
			$max = substr($max,0,-1);
			$max = round($max*1048576);
		} elseif(@eregi("K$",$max)) {
			$max = substr($max,0,-1);
			$max = round($max*1024);
		}
		
		return $max;
	}
	//------------------------------------------------------------------------------
	function down_home($abs_dir) {			// dir deeper than home?
		$real_home = @realpath($GLOBALS["T3Q_VARS"]["home_dir"]);
		$real_dir = @realpath($abs_dir);
		
		if($real_home===false || $real_dir===false) {
			if(@eregi("\\.\\.",$abs_dir)) return false;
		} else if(strcmp($real_home,@substr($real_dir,0,strlen($real_home)))) {
			return false;
		}
		return true;
	}
	//------------------------------------------------------------------------------
	function id_browser() {
		$browser=$GLOBALS['__SERVER']['HTTP_USER_AGENT'];
		
		if(ereg('Opera(/| )([0-9].[0-9]{1,2})', $browser)) {
			return 'OPERA';
		} else if(ereg('MSIE ([0-9].[0-9]{1,2})', $browser)) {
			return 'IE';
		} else if(ereg('OmniWeb/([0-9].[0-9]{1,2})', $browser)) {
			return 'OMNIWEB';
		} else if(ereg('(Konqueror/)(.*)', $browser)) {
			return 'KONQUEROR';
		} else if(ereg('Mozilla/([0-9].[0-9]{1,2})', $browser)) {
			return 'MOZILLA';
		} else {
			return 'OTHER';
		}
	}
	
	function showError($err_str){
		global $LANG,$BACK_PATH;
		//setCookie("copymoveitems",serialize(array()),0,'/');
		//setCookie("action","",0,'/');
		//setCookie("dir","",0,'/');
		$content= array();
		//include backend template if not already included by the mainscript
		require_once ($BACK_PATH."template.php");
		$errordoc = t3lib_div::makeInstance("bigDoc");
		$errordoc->backPath = $BACK_PATH;
		$content[]=$errordoc->startPage($LANG->getLL("title")).$errordoc->header($LANG->getLL("title")).$errordoc->spacer(5).$errordoc->section($LANG->getLL("error.header"),$err_str);
		$content[]=$errordoc->spacer(20).$errordoc->endPage();
		echo implode("",$content);
		exit;
	}
}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/t3quixplorer/mod1/t3quixplorer_div.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/t3quixplorer/mod1/t3quixplorer_div.php"]);
}

?>
