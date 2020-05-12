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
 * class 'quixplorer_edit' for the 't3quixplorer' extension.
 * Class to a edit a text file
 *
 * @author	Mads Brunn <brunn@mail.dk>
 */
/***************************************************************

     The Original Code is fun_edit.php, released on 2003-03-31.

     The Initial Developer of the Original Code is The QuiX project.
	 
	 quix@free.fr
	 http://www.quix.tk
	 http://quixplorer.sourceforge.net

****************************************************************/



require_once ("t3quixplorer_div.php");

class t3quixplorer_edit{


	var $highLightStyles = array(
		'prespace' 			=> array('<span style="">','</span>'),	// Space before any content on a line
		'objstr_postspace' 	=> array('<span style="">','</span>'),	// Space after the object string on a line
		'operator_postspace' => array('<span style="">','</span>'),	// Space after the operator on a line
		'operator' 			=> array('<span style="color: black; font-weight: bold;">','</span>'),	// The operator char

		'value' 			=> array('<span style="color: #cc0000;">','</span>'),	// The value of a line
		'objstr' 			=> array('<span style="color: #0000cc;">','</span>'),	// The object string of a line
		'value_copy' 		=> array('<span style="color: #006600;">','</span>'),	// The value when the copy syntax (<) is used; that means the object reference
		'value_unset' 		=> array('<span style="background-color: #66cc66;">','</span>'),	// The value when an object is unset. Should not exist.
		'ignored'			=> array('<span style="background-color: #66cc66;">','</span>'),	// The "rest" of a line which will be ignored.
		'default' 			=> array('<span style="background-color: #66cc66;">','</span>'),	// The default style if none other is applied.
		'comment' 			=> array('<span style="color: #666666; font-style: italic;">','</span>'),	// Comment lines
		'condition'			=> array('<span style="background-color: maroon; color: #ffffff; font-weight: bold;">','</span>'),	// Conditions
		'error' 			=> array('<span style="background-color: yellow; border: 1px red dashed; font-weight: bold;">','</span>'),	// Error messages
		'linenum' 			=> array('<span style="background-color: #eeeeee;">','</span>'),	// Line numbers
	);
	var $highLightStyles_analytic = array(
		'prespace' 			=> array('<span style="background-color: #cccc99;">','</span>'),	// Space before any content on a line
		'objstr_postspace' 	=> array('<span style="background-color: #cccc99;">','</span>'),	// Space after the object string on a line
		'operator_postspace' => array('<span style="background-color: #cccc99;">','</span>'),	// Space after the operator on a line
		'operator' 			=> array('<span style="color: black; font-weight: bold; background-color: #cc6600;">','</span>'),	// The operator char
		'value' 			=> array('<span style="background-color: #ffff00; color: #cc0000;">','</span>'),	// The value of a line
		'objstr' 			=> array('<span style="background-color: #99ffff; color: #0000cc;">','</span>'),	// The object string of a line
		'value_copy' 		=> array('<span style="color: #006600;">','</span>'),	// The value when the copy syntax (<) is used; that means the object reference
		'value_unset' 		=> array('<span style="background-color: #66cc66;">','</span>'),	// The value when an object is unset. Should not exist.
		'ignored'			=> array('<span style="background-color: #66cc66;">','</span>'),	// The "rest" of a line which will be ignored.
		'default' 			=> array('<span style="background-color: #66cc66;">','</span>'),	// The default style if none other is applied.
		'comment' 			=> array('<span style="color: #666666; font-style: italic;">','</span>'),	// Comment lines
		'condition'			=> array('<span style="background-color: maroon; color: #ffffff; font-weight: bold;">','</span>'),	// Conditions
		'error' 			=> array('<span style="background-color: yellow; border: 1px red dashed; font-weight: bold;">','</span>'),	// Error messages
		'linenum' 			=> array('<span style="background-color: #eeeeee;">','</span>'),	// Line numbers
	);

	var $highLightBlockStyles = 'border-left: black solid 3px;';

	
	function savefile($file_name) {			// save edited file
		global $LANG;
		$code = t3lib_div::_POST("code");
		if(t3lib_div::_POST("win_to_unix_br")){
			$code = str_replace(chr(13).chr(10),chr(10),$code);
		}
		$fp = @fopen($file_name, "w");
		if($fp===false) t3quixplorer_div::showError(basename($file_name).": ".$LANG->getLL("error.savefile"));
		fputs($fp, $code);
		@fclose($fp);
	}
	
	function main($dir, $item) {		
		global $LANG;
                //echo t3lib_div::view_array(t3lib_div::_POST());
		
		$content= array();

		if(!t3quixplorer_div::get_is_file($dir, $item)) t3quixplorer_div::showError($item.": ".$LANG->getLL("error.fileexists"));
		if(!t3quixplorer_div::get_show_item($dir, $item)) t3quixplorer_div::showError($item.": ".$LANG->getLL("error.accessfile"));
		
		$fname = t3quixplorer_div::get_abs_item($dir, $item);
		
		if(t3lib_div::_POST("dosave") && t3lib_div::_POST("dosave")=="yes") {
			// Save / Save As
			$item=basename(stripslashes(t3lib_div::_POST("fname")));
			$fname2=t3quixplorer_div::get_abs_item($dir, $item);
			if(!isset($item) || $item=="") t3quixplorer_div::showError($LANG->getLL("error.miscnoname"));
			if($fname!=$fname2 && @file_exists($fname2)) t3quixplorer_div::showError($item.": ".$LANG->getLL("error.itemdoesexist"));
			$this->savefile($fname2);
			$fname=$fname2;
		}
		
		// open file
		$fp = @fopen($fname, "r");
		if($fp===false) t3quixplorer_div::showError($item.": ".$LANG->getLL("error.openfile"));
		@fclose($fp);
		
		$fileContent = t3lib_div::getUrl($fname);
		
		// header
		$s_item=t3quixplorer_div::get_rel_item($dir,$item);	if(strlen($s_item)>50) $s_item="...".substr($s_item,-47);
		
		$theight = ($GLOBALS["T3Q_VARS"]["textarea_height"] && is_numeric($GLOBALS["T3Q_VARS"]["textarea_height"]))?$GLOBALS["T3Q_VARS"]["textarea_height"]:20;
	
		// Form
		$content[]=$s_item;
		$content[]= '
		  <br />
		    <form name="editfrm" method="post" action="'.t3quixplorer_div::make_link("edit",$dir,$item).'">
                      <input type="hidden" name="dosave" value="yes">
		      <textarea name="code" id="code" rows="'.$theight.'" cols="150" wrap="off"  onkeydown="return catchTab(this,event)"  >'.t3lib_div::formatForTextarea($fileContent).'</textarea>
		  ';
			
		
		$content[]= '
		      <br />
			  <table>
				<tr>
				  <td>Convert windows linebreaks (13-10) to unix (10):</td>
				  <td><input type="checkbox" name="win_to_unix_br" value="1"'.(TYPO3_OS=="WIN"?"":" CHECKED ").'></td>
				  <td>&nbsp;&nbsp;
				</tr>

		';

		$fileinfo = t3lib_div::split_fileref(t3quixplorer_div::get_abs_item($dir,$item));

		$lang = t3lib_div::_GP("highlight_lang");
		$ext = $fileinfo['fileext'];

		if(!$lang){
			$lang = $ext;
		}
		
		$content[]= '

			  </table>
			  <br />
		      <table>
			    	<tr>
				  		<td>
				    		<input type="text" name="fname" value="'.$item.'">
				  		</td>
		          <td>
				    		<input type="button" name="savenow" value="'.$LANG->getLL("message.btnsave").'" onclick="document.all.editfrm.submit()" >
				  		</td>
				  		<td>
				    		<input type="reset" value="'.$LANG->getLL("message.btnreset").'">
				  		</td>
				  		<td>
		            <input type="button" value="'.$LANG->getLL("message.btnclose").'" onClick="javascript:location=\''.t3quixplorer_div::make_link("list",$dir,NULL).'\';">
				  		</td>
				  		<td>
		            &nbsp;'.$LANG->getLL("message.btnscroll").' &nbsp;<input type="Text" name="linenumber" onkeypress="keyhandler(event.keyCode)"  value="'.t3lib_div::_GP("linenumber").'" size="5"> &nbsp;(+ ENTER)
              </td></tr></table><br />';


		if($lang=='php' || $lang=='php3' || $lang=='inc'){
			$inputCode = $fileContent;
			$lines = split("\n",$inputCode);

			foreach($lines as $linenumber => $line){
				if(preg_match('/\s*\bfunction\b\s*([a-z_][a-z0-9_]*)\s*/iA', $line, $match)) {
					//print_r($match);
					$functions[] = array('linenumber' => $linenumber, 'functionname' => $match[1]);
				}
			}
			
			

			if(is_array($functions) && !empty($functions)){
				
				//print_r($functions);
				
				
				reset($functions);
				
				$content[] = '
				<table class="bgColor5"><tr><td>'.$LANG->getLL("message.btnfunction").'<select name="function_index" onchange="jumpToLine(parseInt(this.options[this.selectedIndex].value)+1)"><option value=""></option>
				';

				foreach($functions as $function){
					$content[] = '
						<option value="'.$function['linenumber'].'">'.$function['functionname'].'</option>
					';
				}
				
				$content[] = '
				</select></td></tr></table><br />
				';
			}
		}



              $content[] = '
			    <table>
			    	<tr>
			    		<td>
			    			<input type="checkbox" name="highlight" '.(t3lib_div::_GP("highlight")? ' checked="checked" ' : '').' value="1">&nbsp;'.$LANG->getLL("message.highlight").'
			    		</td>
			    		<td>
			    			<select name="highlight_lang">
			    				<option value=""></option>
			    				<option value="php" '.($lang=='php' || $lang=='php3' || $lang=='inc'? ' selected ' : '').' >PHP</option>
			    				<option value="ts" '.($lang=='ts'? ' selected ' : '').'>TypoScript</option>
			    				<option value="xml" '.($lang=='xml'? ' selected ' : '').'>XML</option>
			    				<option value="sql" '.($lang=='sql'? ' selected ' : '').'>SQL</option>
			    				<option value="html4strict" '.($lang=='html' || $lang=='htm' || $lang=='html4strict' || $lang=='tmpl' ? ' selected ' : '').' >HTML</option>
			    				<option value="javascript" '.($lang=='js' || $lang=='javascript' ? ' selected ' : '').' >Javascript</option>
			    				<option value="perl" '.($lang=='perl' || $lang=='pl' ? ' selected ' : '').'>Perl</option>
			    				<option value="css" '.($lang=='css' ? ' selected ' : '').'>CSS</option>
			    				<option value="smarty" '.($lang=='smarty' ? ' selected ' : '').'>Smarty</option>
			    			</select>
			    			
			    		</td>
			    	</tr>
			    	
			    </table>

			    				
			  </form>

			 <br />
			<script language="JavaScript1.2" type="text/javascript">
			<!--
				if(document.editfrm) document.editfrm.code.focus();
			// -->
			</script>';
		

		if(t3lib_div::_GP("highlight") && t3lib_div::_GP("highlight_lang")){
			require_once ("geshi.php");
			$inputCode = $fileContent;
			switch(t3lib_div::_GP("highlight_lang")){
				case 'php':
				case 'xml':
				case 'sql':
				case 'html4strict':
				case 'javascript':
				case 'perl':
				case 'css':
				case 'smarty':
					$geshi = new GeSHi($inputCode, t3lib_div::_GP("highlight_lang"),'geshi/');
					$geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
                                        $geshi->set_link_target('_blank'); 
                                       $geshi->set_line_style("font-family:'Courier New', Courier, monospace; color: black; font-weight: normal; font-style: normal;");
					$content[] = '<hr />'.$geshi->parse_code(); 
					
					break;	
				case 'ts':
  		  	require_once(PATH_t3lib.'class.t3lib_tsparser.php');
  				$tsparser = t3lib_div::makeInstance("t3lib_TSparser");
  				//$tsparser->highLightStyles = $this->highLightStyles_analytic;
  				//$tsparser->highLightBlockStyles_basecolor= '';
  				//$tsparser->highLightBlockStyles = $this->highLightBlockStyles;
  				$tsparser->highLightStyles = $this->highLightStyles;  
  				$tsparser->lineNumberOffset=1;
	  			$formattedContent = $tsparser->doSyntaxHighlight($inputCode, array($tsparser->lineNumberOffset), 0);
  				$content[]='<hr />'.$formattedContent;		  
  				break;
  			default:
  				break;
			}
		} else {
        $inputCode = $fileContent;
        $lines = split("\n",$inputCode);
        foreach($lines as $k => $v){
         // $lines[$k] = '<font color="black">'.str_pad(($k+1),4,' ',STR_PAD_LEFT).':</font> '.htmlspecialchars($v);
	 	$lines[$k] = '<li ondblclick="jumpToLine('.($k+1).')" >'.htmlspecialchars($v).'</li>';
        }
  			//$formattedContent = implode('<br />',$lines);
			$formattedContent = implode('',$lines);
			
  			$formattedContent = ereg_replace('['.chr(10).chr(13).']','',$formattedContent);
			
  			//$content[] ='<hr /><pre class="ts-hl">'.$formattedContent.'</pre>';
			$content[] ='<hr /><pre class="ts-hl"><ol>'.$formattedContent.'</ol></pre>';
		}
		

		return implode("",$content);
	}



}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/t3quixplorer/mod1/t3quixplorer_edit.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/t3quixplorer/mod1/t3quixplorer_edit.php"]);
}

?>