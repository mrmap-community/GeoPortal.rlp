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
 * class 't3quixplorer_download' for the 't3quixplorer' extension.
 * Contains functions to download a file
 *
 * @author	Mads Brunn <brunn@mail.dk>
 */
/***************************************************************

     The Original Code is fun_down.php, released on 2003-03-31.

     The Initial Developer of the Original Code is The QuiX project.
	 
	 quix@free.fr
	 http://www.quix.tk
	 http://quixplorer.sourceforge.net

****************************************************************/

require_once ("t3quixplorer_div.php");

class t3quixplorer_download{
	
	function main($dir, $item) {		// download file
		global $LANG,$CLIENT;
		
		if(!t3quixplorer_div::get_is_file($dir,$item)) t3quixplorer_div::showError($item.": ".$LANG->getLL("error.fileexist"));
		if(!t3quixplorer_div::get_show_item($dir, $item)) t3quixplorer_div::showError($item.": ".$LANG->getLL("error.accessfile"));
		
		$abs_item = t3quixplorer_div::get_abs_item($dir,$item);
		header('Content-Type: '.(($CLIENT["BROWSER"]=='msie' || $CLIENT["BROWSER"]=='opera')?'application/octetstream':'application/octet-stream'));
		header('Expires: '.gmdate('D, d M Y H:i:s').' GMT');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: '.filesize($abs_item));
		if($CLIENT["BROWSER"]=='msie') {
			header('Content-Disposition: inline; filename="'.$item.'"');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		} else {
			header('Content-Disposition: attachment; filename="'.$item.'"');
			header('Cache-Control: no-cache, must-revalidate');
			header('Pragma: no-cache');
		}
		
		@readfile($abs_item);
		exit;
	}
}


if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/t3quixplorer/mod1/t3quixplorer_download.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/t3quixplorer/mod1/t3quixplorer_download.php"]);
}

?>
