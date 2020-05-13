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
 * class 'quixplorer_upload' for the 't3quixplorer' extension.
 * functions for file upload
 *
 * @author	Mads Brunn <brunn@mail.dk>
 */
/***************************************************************

     The Original Code is fun_up.php, released on 2003-04-02.

     The Initial Developer of the Original Code is The QuiX project.
	 
	 quix@free.fr
	 http://www.quix.tk
	 http://quixplorer.sourceforge.net

****************************************************************/


require_once ("t3quixplorer_div.php");


class t3quixplorer_upload{
	
	function main($dir) {		
		global $LANG,$HTTP_POST_FILES;

		$GLOBALS["T3Q_DEBUG"]["fileuploads"]=$HTTP_POST_FILES;
		
		$this->content=array();
	
		// Execute
		if(t3lib_div::_POST("confirm") && t3lib_div::_POST("confirm")=="true") {

			
			$cnt=count($_FILES['userfile']['name']);
			
			$GLOBALS["T3Q_DEBUG"]["uploadedfiles"] = $cnt;
			
			$err=false;
			$err_avaliable=isset($_FILES['userfile']['error']);
		
			// upload files & check for errors
			for($i=0;$i<$cnt;$i++) {
				$errors[$i]=NULL;
				$tmp = $_FILES['userfile']['tmp_name'][$i];
				$items[$i] = stripslashes($_FILES['userfile']['name'][$i]);
				if($err_avaliable) $up_err = $_FILES['userfile']['error'][$i];
				else $up_err=(file_exists($tmp)?0:4);
				$abs = t3quixplorer_div::get_abs_item($dir,$items[$i]);
			
				if($items[$i]=="" || $up_err==4) continue;
				if($up_err==1 || $up_err==2) {
					$errors[$i]=$LANG->getLL("error.miscfilesize");
					$err=true;	continue;
				}
				if($up_err==3) {
					$errors[$i]=$LANG->getLL("error.miscfilepart");
					$err=true;	continue;
				}
				if(!@is_uploaded_file($tmp)) {
					$errors[$i]=$LANG->getLL("error.uploadfile");
					$err=true;	continue;
				}
				if(@file_exists($abs)) {
					$errors[$i]=$LANG->getLL("error.itemdoesexist");
					$err=true;	continue;
				}
				
				// Upload
				if(function_exists("move_uploaded_file")) {
					$ok = @move_uploaded_file($tmp, $abs);
				} else {
					$ok = @copy($tmp, $abs);
					@unlink($tmp);	// try to delete...
				}
				
				if($ok===false) {
					$errors[$i]=$LANG->getLL("error.uploadfile");
					$err=true;	continue;
				}
			}

			if($err) {			// there were errors
				$err_msg="";
				for($i=0;$i<$cnt;$i++) {
					if($errors[$i]==NULL) continue;
					$err_msg .= $items[$i]." : ".$errors[$i].'<br />';
				}
				t3quixplorer_div::showError($err_msg);
			}
			
			header("Location: ".t3quixplorer_div::make_link("list",$dir,NULL));
			return;
		}
		
		
		// List
		$this->content[]='
		  <br />
		  <form enctype="multipart/form-data" action="'.t3quixplorer_div::make_link("upload",$dir,NULL).'" method="POST">
		    <input type="hidden" name="MAX_FILE_SIZE" value="'.t3quixplorer_div::get_max_file_size().'">
			<input type="hidden" name="confirm" value="true">
		  <table>
		  ';
		  
		for($i=1;$i<11;$i++) {
			$this->content[]='
			  <tr>
			    <td nowrap="nowrap">
			      <input name="userfile[]" type="file" size="40">
				</td>
			  </tr>';
		}
		$this->content[]='
		  </table>
		  <br />
		  <table>
		    <tr>
			  <td>
			    <input type="submit" value="'.$LANG->getLL("message.btnupload").'">
			  </td>
			  <td>
			    <input type="button" value="'.$LANG->getLL("message.btncancel").'" onClick="javascript:location=\''.t3quixplorer_div::make_link("list",$dir,NULL).'\';">
			  </td>
			</tr>
		  </form>
		  </table>
		  <br />';
		
		return implode("",$this->content);
	}


}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/t3quixplorer/mod1/t3quixplorer_upload.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/t3quixplorer/mod1/t3quixplorer_upload.php"]);
}
?>
