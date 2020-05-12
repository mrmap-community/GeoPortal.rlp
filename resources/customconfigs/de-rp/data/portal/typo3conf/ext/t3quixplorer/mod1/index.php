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
					
/***************************************************************

     The Original Code is index.php, released on 2003-04-02.

     The Initial Developer of the Original Code is The QuiX project.
	 
	 quix@free.fr
	 http://www.quix.tk
	 http://quixplorer.sourceforge.net

****************************************************************/


	
/** 	
 * Module 'Quixplorer' for the 't3quixplorer' extension.
 *
 * @author	Mads Brunn <brunn@mail.dk>
 */
	


	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);	
require ("conf.php");
require_once ("t3quixplorer_div.php");
require ($BACK_PATH."init.php");
require ($BACK_PATH."template.php");
$LANG->includeLLFile("EXT:t3quixplorer/mod1/locallang.php");
#include ("locallang.php");
require_once (PATH_t3lib."class.t3lib_scbase.php");
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]

class tx_t3quixplorer_module1 extends t3lib_SCbase {
	var $pageinfo;

	/**
	 * 
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		
		parent::init();

		/*
		if (t3lib_div::_GP("clear_all_cache"))	{
			$this->include_once[]=PATH_t3lib."class.t3lib_tcemain.php";
		}
		*/
	}

	/**
	 * Main function of the module. Write the content to $this->content
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS,$FILEICONS;
		
		
//echo t3lib_div::view_array( $GLOBALS['BE_USER']->uc);



		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;
		
		if (($this->id && $access) || ($BE_USER->user["admin"] && !$this->id))	{
			$GLOBALS["T3Q_DEBUG"] = array();
			$GLOBALS["T3Q_VARS"] = array();
			
			$GLOBALS["T3Q_DEBUG"]["client"] = $CLIENT;
			
			$config = unserialize($GLOBALS["TYPO3_CONF_VARS"]["EXT"]["extConf"]["t3quixplorer"]);
			$GLOBALS["T3Q_VARS"] = array_merge($GLOBALS["T3Q_VARS"],$config);


			if(!strlen(trim($GLOBALS["T3Q_VARS"]["editable_ext"]))){
				$GLOBALS["T3Q_VARS"]["editable_ext"] = "\.phpcron$|\.ts$|\.tmpl$|\.txt$|\.php$|\.php3$|\.phtml$|\.inc$|\.sql$|\.pl$|\.htm$|\.html$|\.shtml$|\.dhtml$|\.xml$|\.js$|\.css$|\.cgi$|\.cpp$\.c$|\.cc$|\.cxx$|\.hpp$|\.h$|\.pas$|\.p$|\.java$|\.py$|\.sh$\.tcl$|\.tk$";
			} 


			if(!strlen(trim($GLOBALS["T3Q_VARS"]["home_dir"]))){
				$GLOBALS["T3Q_VARS"]["home_dir"] = ereg_replace('/$','',PATH_site);
			}

			if(!strlen(trim($GLOBALS["T3Q_VARS"]["home_url"]))){
				$GLOBALS["T3Q_VARS"]["home_url"] = t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST');
			}


			
			$GLOBALS["T3Q_VARS"]["images_ext"] = "\.png$|\.bmp$|\.jpg$|\.jpeg$|\.gif$";
			$GLOBALS["T3Q_VARS"]["super_mimes"] = array(
				// dir, exe, file
				"dir"	=> array($LANG->getLL("mime.dir"),"folder.gif"),
				"exe"	=> array($LANG->getLL("mime.exe"),"exe.gif","\.exe$|\.com$|\.bin$"),
				"file"	=> array($LANG->getLL("mime.file"),"default.gif")
			);
			$GLOBALS["T3Q_VARS"]["used_mime_types"] = array(
				// text
				"text"	=> array($LANG->getLL("mime.text"),"txt.gif","\.txt$"),
				
				// programming
				"php"	=> array($LANG->getLL("mime.php"),"php.gif","\.php$|\.php3$|\.phtml$|\.inc$"),
				"sql"	=> array($LANG->getLL("mime.sql"),"src.gif","\.sql$"),
				"perl"	=> array($LANG->getLL("mime.perl"),"pl.gif","\.pl$"),
				"html"	=> array($LANG->getLL("mime.html"),"html.gif","\.htm$|\.html$|\.shtml$|\.dhtml$|\.xml$"),
				"js"	=> array($LANG->getLL("mime.js"),"js.gif","\.js$"),
				"css"	=> array($LANG->getLL("mime.css"),"src.gif","\.css$"),
				"cgi"	=> array($LANG->getLL("mime.cgi"),"exe.gif","\.cgi$"),
				//"py"	=> array($LANG->getLL("mime.py"),"py.gif","\.py$"),
				//"sh"	=> array($LANG->getLL("mime.sh"),"sh.gif","\.sh$"),
				// C++
				"cpps"	=> array($LANG->getLL("mime.cpps"),"cpp.gif","\.cpp$|\.c$|\.cc$|\.cxx$"),
				"cpph"	=> array($LANG->getLL("mime.cpph"),"h.gif","\.hpp$|\.h$"),
				// Java
				"javas"	=> array($LANG->getLL("mime.javas"),"java.gif","\.java$"),
				"javac"	=> array($LANG->getLL("mime.javac"),"java.gif","\.class$|\.jar$"),
				// Pascal
				"pas"	=> array($LANG->getLL("mime.pas"),"src.gif","\.p$|\.pas$"),
				
				// images
				"gif"	=> array($LANG->getLL("mime.gif"),"image.gif","\.gif$"),
				"jpg"	=> array($LANG->getLL("mime.jpg"),"image.gif","\.jpg$|\.jpeg$"),
				"bmp"	=> array($LANG->getLL("mime.bmp"),"image.gif","\.bmp$"),
				"png"	=> array($LANG->getLL("mime.png"),"image.gif","\.png$"),
				
				// compressed
				"zip"	=> array($LANG->getLL("mime.zip"),"zip.gif","\.zip$"),
				"tar"	=> array($LANG->getLL("mime.tar"),"tar.gif","\.tar$"),
				"gzip"	=> array($LANG->getLL("mime.gzip"),"tgz.gif","\.tgz$|\.gz$"),
				"bzip2"	=> array($LANG->getLL("mime.bzip2"),"tgz.gif","\.bz2$"),
				"rar"	=> array($LANG->getLL("mime.rar"),"tgz.gif","\.rar$"),
				//"deb"	=> array($LANG->getLL("mime.deb"),"package.gif","\.deb$"),
				//"rpm"	=> array($LANG->getLL("mime.rpm"),"package.gif","\.rpm$"),
				
				// music
				"mp3"	=> array($LANG->getLL("mime.mp3"),"mp3.gif","\.mp3$"),
				"wav"	=> array($LANG->getLL("mime.wav"),"sound.gif","\.wav$"),
				"midi"	=> array($LANG->getLL("mime.midi"),"midi.gif","\.mid$"),
				"real"	=> array($LANG->getLL("mime.real"),"real.gif","\.rm$|\.ra$|\.ram$"),
				//"play"	=> array($LANG->getLL("mime.play"),"mp3.gif","\.pls$|\.m3u$"),
				
				// movie
				"mpg"	=> array($LANG->getLL("mime.mpg"),"video.gif","\.mpg$|\.mpeg$"),
				"mov"	=> array($LANG->getLL("mime.mov"),"video.gif","\.mov$"),
				"avi"	=> array($LANG->getLL("mime.avi"),"video.gif","\.avi$"),
				"flash"	=> array($LANG->getLL("mime.flash"),"flash.gif","\.swf$"),
				
				// Micosoft / Adobe
				"word"	=> array($LANG->getLL("mime.word"),"word.gif","\.doc$"),
				"excel"	=> array($LANG->getLL("mime.excel"),"spread.gif","\.xls$"),
				"pdf"	=> array($LANG->getLL("mime.pdf"),"pdf.gif","\.pdf$")
			);
			$GLOBALS["T3Q_VARS"]["date_fmt"] = "d/m/Y H:i";

			
			if(t3lib_div::_GET("action")) $GLOBALS["T3Q_VARS"]["action"]=t3lib_div::_GET("action");
			else $GLOBALS["T3Q_VARS"]["action"]="list";
			if($GLOBALS["T3Q_VARS"]["action"]=="post" && t3lib_div::_POST("do_action")) {
				$GLOBALS["T3Q_VARS"]["action"]=t3lib_div::_POST("do_action");
			}
			if($GLOBALS["T3Q_VARS"]["action"]=="") $GLOBALS["T3Q_VARS"]["action"]="list";
	

			#if(t3lib_div::_GP("dir")){
			#	$mdata = array( "storedFolder"=>t3lib_div::_GET("dir") );
			#	$GLOBALS['BE_USER']->pushModuleData("tools_txt3quixplorerM1",$mdata);
			#}

		
			#$mdata = $GLOBALS['BE_USER']->getModuleData("tools_txt3quixplorerM1");

/*			
			if(t3lib_div::_GET("dir")){ 
				$GLOBALS["T3Q_VARS"]["dir"] = t3lib_div::_GET("dir");
			} else if($mdata["storedFolder"]){
				$GLOBALS["T3Q_VARS"]["dir"] = $mdata["storedFolder"];
			} else {
				 $GLOBALS["T3Q_VARS"]["dir"]="";
			}
*/

	

/*
			if(t3lib_div::_GET("dir")){ 
				$GLOBALS["T3Q_VARS"]["dir"] = t3lib_div::_GET("dir");
			} elseif(t3lib_div::_POST("jumptodir")){
				 $GLOBALS["T3Q_VARS"]["dir"]=t3lib_div::_POST("jumpdir");
			} else {
				 $GLOBALS["T3Q_VARS"]["dir"]="";
			}
*/


			if(t3lib_div::_POST("jumptodir")){
				 $GLOBALS["T3Q_VARS"]["dir"]=t3lib_div::_POST("jumpdir");
			} elseif(t3lib_div::_GET("dir")){
				$GLOBALS["T3Q_VARS"]["dir"] = t3lib_div::_GET("dir");
			} else {
				 $GLOBALS["T3Q_VARS"]["dir"]="";
			}

			if($GLOBALS["T3Q_VARS"]["dir"]==".") $GLOBALS["T3Q_VARS"]["dir"]="";
			
			if(t3lib_div::_GET("item")) $GLOBALS["T3Q_VARS"]["item"]=t3lib_div::_GET("item");
			else $GLOBALS["T3Q_VARS"]["item"]="";
	
			if(t3lib_div::_GET("order")) $GLOBALS["T3Q_VARS"]["order"] = t3lib_div::_GET("order");
			else $GLOBALS["T3Q_VARS"]["order"]="name";
			if($GLOBALS["T3Q_VARS"]["order"]=="") $GLOBALS["T3Q_VARS"]["order"] = "name";
			
			if(t3lib_div::_GET("srt")) $GLOBALS["T3Q_VARS"]["srt"]=t3lib_div::_GET("srt");
			else $GLOBALS["T3Q_VARS"]["srt"]="yes";
			if($GLOBALS["T3Q_VARS"]["srt"]=="") $GLOBALS["T3Q_VARS"]["srt"]=="yes";

				// Draw the header.
			$this->doc = t3lib_div::makeInstance("bigDoc");
			$this->doc->backPath = $BACK_PATH;

			$numfiles = count(unserialize(stripslashes($_COOKIE["copymoveitems"])));
		



				// JavaScript
			$this->doc->JScode = '
				<script language="javascript" type="text/javascript">
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}
				</script>


				<script language="JavaScript1.2" type="text/javascript">
				<!--
					// Checkboxes
					function Toggle(e) {
						if(e.checked) {
							Highlight(e);
							document.selform.toggleAllC.checked = AllChecked();
						} else {
							UnHighlight(e);
							document.selform.toggleAllC.checked = false;
						}
				   	}
				
					function ToggleAll(e) {
						if(e.checked) CheckAll();
						else ClearAll();
					}
					
					function CheckAll() {
						var ml = document.selform;
						var len = ml.elements.length;
						for(var i=0; i<len; ++i) {
							var e = ml.elements[i];
							if(e.name == "selitems[]") {
								e.checked = true;
								Highlight(e);
							}
						}
						ml.toggleAllC.checked = true;
					}
				
					function ClearAll() {
						var ml = document.selform;
						var len = ml.elements.length;
						for (var i=0; i<len; ++i) {
							var e = ml.elements[i];
							if(e.name == "selitems[]") {
								e.checked = false;
								UnHighlight(e);
							}
						}
						ml.toggleAllC.checked = false;
					}
				   
					function AllChecked() {
						ml = document.selform;
						len = ml.elements.length;
						for(var i=0; i<len; ++i) {
							if(ml.elements[i].name == "selitems[]" && !ml.elements[i].checked) return false;
						}
						return true;
					}
					

					function NumChecked() {
						ml = document.selform;
						len = ml.elements.length;
						num = 0;
						for(var i=0; i<len; ++i) {
							if(ml.elements[i].name == "selitems[]" && ml.elements[i].checked) ++num;
						}
						return num;
					}
					
					
					// Row highlight
				
					function Highlight(e) {
						var r = null;
						if(e.parentNode && e.parentNode.parentNode) {
							r = e.parentNode.parentNode;
						} else if(e.parentElement && e.parentElement.parentElement) {
							r = e.parentElement.parentElement;
						}
						if(r && r.className=="foobar") {
							r.className = "morefoobar";
						}
					}
				
					function UnHighlight(e) {
						var r = null;
						if(e.parentNode && e.parentNode.parentNode) {
							r = e.parentNode.parentNode;
						} else if (e.parentElement && e.parentElement.parentElement) {
							r = e.parentElement.parentElement;
						}
						if(r && r.className=="morefoobar") {
							r.className = "foobar";
						}
					}

					// Copy / Move / Delete
					
					function Copy() {
						if(NumChecked()==0) {
							alert("'.$LANG->getLL("error.miscselitems").'");
							return;
						}
						document.selform.do_action.value = "copy";
						document.selform.submit();
					}
					
					function Move() {
						if(NumChecked()==0) {
							alert("'.$LANG->getLL("error.miscselitems").'");
							return;
						}
						document.selform.do_action.value = "move";
						document.selform.submit();
					}

					function Paste() {
						num='.$numfiles.';
						if(confirm("'.$LANG->getLL("error.miscpasteitems").'")) {
							document.selform.do_action.value = "paste";
							document.selform.submit();
						}
					}

					function Clear() {
						if(confirm("'.$LANG->getLL("error.miscclearclipboard").'")) {
							document.selform.do_action.value = "clear";
							document.selform.submit();
						}
					}

					
					function Delete() {
						num=NumChecked();
						if(num==0) {
							alert("'.$LANG->getLL("error.miscselitems").'");
							return;
						}
						if(confirm("'.$LANG->getLL("error.miscdelitems").'")) {
							document.selform.do_action.value = "delete";
							document.selform.submit();
						}
					}
					
					function Archive() {
						if(NumChecked()==0) {
							alert("'.$LANG->getLL("error.miscselitems").'");
							return;
						}
						document.selform.do_action.value = "arch";
						document.selform.submit();
					}

					function chwrap() {
						if(document.editfrm.wrap.checked) {
							document.editfrm.code.wrap="soft";
						} else {
						document.editfrm.code.wrap="off";
						}
					}
					
					function scrollToLine(linenumber){
						content = new String(document.all.code.value);
						var splitstr = content.split("\n");

						lineheight = document.all.code.scrollHeight / splitstr.length;
						document.all.code.scrollTop = (linenumber - 1) * lineheight;
						client="'.$CLIENT["BROWSER"].'";
						if(client=="net"){
							caretposition = 0;
							for(i = 0;i < linenumber-1; i++){
								caretposition +=  splitstr[i].length + 1 ;  
							}
							document.all.code.selectionStart = caretposition 
							document.all.code.selectionEnd = caretposition
							document.all.code.focus();
						}
					}

					function jumpToLine(linenumber){
						window.location="'.t3quixplorer_div::make_link("edit",$GLOBALS["T3Q_VARS"]["dir"],$GLOBALS["T3Q_VARS"]["item"]).'#top";
						scrollToLine(linenumber);
					}
					

					function scroll(){
						content = new String(document.all.code.value);
  					  var splitstr = content.split("\n");

  					  lineheight = document.all.code.scrollHeight / splitstr.length;
  					  document.all.code.scrollTop = (document.all.linenumber.value - 1) * lineheight;

					client="'.$CLIENT["BROWSER"].'";
                                          if(client=="net"){
                                             caretposition = 0;
                                             for(i = 0;i < document.all.linenumber.value-1; i++){
                                               caretposition +=  splitstr[i].length + 1 ;  
                                             }
                                             document.all.code.selectionStart = caretposition 
                                             document.all.code.selectionEnd = caretposition
                                             document.all.code.focus();
                                          }
                                          //if(client="msie"){
                                             //document.all.code.focus();
                                             //sel = document.all.code.createTextRange();
                                             //sel.collapse();
                                             //sel.text = "TYPOCONSULT";
                                             //caretposition = 0;
                                             //for(i = 0;i < document.all.linenumber.value-1; i++){
                                             //    caretposition +=  splitstr[i].length + 1 ;  
                                             //}
                                             //alert(caretposition); 
                                             //sel.moveStart("character",caretposition);
                                             //sel.moveEnd("character",caretposition);
                                             //sel.scrollIntoView();
                                             //document.all.code.selectionStart = caretposition 
                                             //document.all.code.selectionEnd = caretposition
                                             //document.all.code.focus();
                                          //}
					}

function keyhandler(e){
  if(e==13){
    scroll();
  }
}



function setSelectionRange(input, selectionStart, selectionEnd) {
  if (input.setSelectionRange) {
    input.focus();
    input.setSelectionRange(selectionStart, selectionEnd);
  }
  else if (input.createTextRange) {
    var range = input.createTextRange();
    range.collapse(true);
    range.moveEnd(\'character\', selectionEnd);
    range.moveStart(\'character\', selectionStart);
    range.select();
  }
}

function replaceSelection (input, replaceString) {
	if (input.setSelectionRange) {
		var selectionStart = input.selectionStart;
		var selectionEnd = input.selectionEnd;
		input.value = input.value.substring(0, selectionStart)+ replaceString + input.value.substring(selectionEnd);
    
		if (selectionStart != selectionEnd){ 
			setSelectionRange(input, selectionStart, selectionStart + 	replaceString.length);
		}else{
			setSelectionRange(input, selectionStart + replaceString.length, selectionStart + replaceString.length);
		}

	}else if (document.selection) {
		var range = document.selection.createRange();

		if (range.parentElement() == input) {
			var isCollapsed = range.text == \'\';
			range.text = replaceString;

			 if (!isCollapsed)  {
				range.moveStart(\'character\', -replaceString.length);
				range.select();
			}
		}
	}
}


// We are going to catch the TAB key so that we can use it, Hooray!
function catchTab(item,e){
        scrollPos = item.scrollTop;
        client="'.$CLIENT["BROWSER"].'";
	if(client=="net"){	

		c=e.which;
	}else{
		c=e.keyCode;
	}	
	if(c==9){
		replaceSelection(item,String.fromCharCode(9));
		setTimeout("document.getElementById(\'"+item.id+"\').focus();document.getElementById(\'"+item.id+"\').scrollTop=scrollPos;",0);
		return false;
	}
		    
}
                                        

					
				
				// -->
				</script>




			';
			$this->doc->postCode='
				<script language="javascript" type="text/javascript">
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = '.intval($this->id).';
				</script>
			';

			$this->content.= '<a name="top"></a>';
			$this->content.=$this->doc->startPage($LANG->getLL("title"));
			$this->content.=$this->doc->header($LANG->getLL("title"));

			$this->content.=$this->doc->spacer(5);


			// Render content:
			$this->moduleContent();

			
			// ShortCut
			if ($BE_USER->mayMakeShortcut())	{
				$this->content.=$this->doc->spacer(20).$this->doc->section("",$this->doc->makeShortcutIcon("id,dir,item,action",implode(",",array_keys($this->MOD_MENU)),$this->MCONF["name"]));
			}

		
			$this->content.=$this->doc->spacer(10);
		} else {
				// If no access or if ID == zero
		
			$this->doc = t3lib_div::makeInstance("mediumDoc");
			$this->doc->backPath = $BACK_PATH;
		
			$this->content.=$this->doc->startPage($LANG->getLL("title"));
			$this->content.=$this->doc->header($LANG->getLL("title"));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->spacer(10);
		}
		$GLOBALS["T3Q_DEBUG"]['vars']=$GLOBALS["T3Q_VARS"];

		$GLOBALS["T3Q_DEBUG"]['absoluteextensionpath'] = t3lib_extMgm::extPath("t3quixplorer"); 
		$GLOBALS["T3Q_DEBUG"]['relativeextensionpath'] = t3lib_extMgm::extRelPath("t3quixplorer");
		//$GLOBALS["T3Q_DEBUG"]['fileicons'] = $FILEICONS;
		$GLOBALS["T3Q_DEBUG"]["cpitems"] = unserialize(stripslashes($_COOKIE["copymoveitems"]));
		//$GLOBALS["T3Q_DEBUG"]["cpitems"] =  //unserialize("a:5:{i:0;s:40:\"c:\\typo3apache\\htdocs\\quickstart/GPL.txt\";i:1;s:44:\"c:\\typo3apache\\htdocs\\quickstart/LICENSE.txt\";i:2;s:44:\"c:\\typo3apache\\htdocs\\quickstart/Package.txt\";i:3;s:41:\"c:\\typo3apache\\htdocs\\quickstart/TODO.txt\";i:4;s:42:\"c:\\typo3apache\\htdocs\\quickstart/clear.gif\";}"); //$_COOKIE["copymoveitems"];
		$GLOBALS["T3Q_DEBUG"]["cookies"] = $_COOKIE;
		



		//$this->content.=$this->getDebugArray();
	}

	/**
	 * Prints out the module HTML
	 */
	function printContent()	{

		$this->content.=$this->doc->endPage();
		echo $this->content;
	}

	function getDebugArray(){
		return t3lib_div::view_array($GLOBALS["T3Q_DEBUG"]);
	}

	/**
	 * Generates the module content
	 */
	function moduleContent()	{
		global $LANG;
		$content = array();
		$GLOBALS["T3Q_DEBUG"]['initial_errors']= count($GLOBALS["T3Q_ERRORS"]);

		switch ($GLOBALS["T3Q_VARS"]["action"]){

			case "edit":
				require("t3quixplorer_edit.php");
				$editObj = t3lib_div::makeInstance("t3quixplorer_edit");
				$content[]= $this->doc->section($LANG->getLL("message.actedit"),$editObj->main($GLOBALS["T3Q_VARS"]["dir"],$GLOBALS["T3Q_VARS"]["item"]));
				break;


			case "extract":
				require("t3quixplorer_extract.php");
				$extractObj = t3lib_div::makeInstance("t3quixplorer_extract");
				$content[]= $this->doc->section($LANG->getLL("message.actextract"),$extractObj->main($GLOBALS["T3Q_VARS"]["dir"],$GLOBALS["T3Q_VARS"]["item"]));
				break;

			case "arch":
				require("t3quixplorer_archive.php");
				$archObj = t3lib_div::makeInstance("t3quixplorer_archive");
				$content[]= $this->doc->section($LANG->getLL("message.actarchive"),$archObj->main($GLOBALS["T3Q_VARS"]["dir"]));
				break;
				
			case "copy":	
			case "move":
			case "paste":
			case "clear":

				require("t3quixplorer_copymove.php");
				$copymoveObj = t3lib_div::makeInstance("t3quixplorer_copymove");
				$copymoveObj->main($GLOBALS["T3Q_VARS"]["dir"]);
				//$content[]= $this->doc->section($LANG->getLL("message.actedit"),$editObj->main($GLOBALS["T3Q_VARS"]["dir"],$GLOBALS["T3Q_VARS"]["item"]));
				break;

			case "rename":
				require("t3quixplorer_rename.php");
				$renameObj = t3lib_div::makeInstance("t3quixplorer_rename");
				$content[]= $this->doc->section($LANG->getLL("message.actrename"),$renameObj->main($GLOBALS["T3Q_VARS"]["dir"],$GLOBALS["T3Q_VARS"]["item"]));
				break;



			case "chmod":
				require("t3quixplorer_chmod.php");
				$chmodObj = t3lib_div::makeInstance("t3quixplorer_chmod");
				$content[]=$this->doc->section($LANG->getLL("message.actperms"),$chmodObj->main($GLOBALS["T3Q_VARS"]["dir"],$GLOBALS["T3Q_VARS"]["item"]));
				break;
				
			case "delete":
				require("t3quixplorer_delete.php");
				$deleteObj = t3lib_div::makeInstance("t3quixplorer_delete");
				$deleteObj->main($GLOBALS["T3Q_VARS"]["dir"]); //no content added
				break;
				
			case "mkitem":
				require("t3quixplorer_mkitem.php");
				$mkitemObj = t3lib_div::makeInstance("t3quixplorer_mkitem");
				$mkitemObj->main($GLOBALS["T3Q_VARS"]["dir"]); //no content added
				break;

			case "upload":
				require("t3quixplorer_upload.php");
				$uploadObj = t3lib_div::makeInstance("t3quixplorer_upload");
				$content[]= $this->doc->section($LANG->getLL("message.actupload"),$uploadObj->main($GLOBALS["T3Q_VARS"]["dir"]));
				break;
				
			case "download":
				require("t3quixplorer_download.php");
				$downloadObj = t3lib_div::makeInstance("t3quixplorer_download");
				$downloadObj->main($GLOBALS["T3Q_VARS"]["dir"],$GLOBALS["T3Q_VARS"]["item"]);
				break;
			
			case "search":
				require("t3quixplorer_search.php");
				$searchObj = t3lib_div::makeInstance("t3quixplorer_search");
				$content[]= $this->doc->section($LANG->getLL("message.actsearch"),$searchObj->main($GLOBALS["T3Q_VARS"]["dir"]));
				break;

				
			case "list":
			default:
				require("t3quixplorer_listdir.php");
				$listdirObj = t3lib_div::makeInstance("t3quixplorer_listdir");
				$content[]=$listdirObj->main($GLOBALS["T3Q_VARS"]["dir"]);
				break;

		}

		$this->content.=implode("",$content);

	}
	
}



if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/t3quixplorer/mod1/index.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/t3quixplorer/mod1/index.php"]);
}




// Make instance:
$SOBE = t3lib_div::makeInstance("tx_t3quixplorer_module1");
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>