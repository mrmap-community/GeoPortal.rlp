<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2003 Gary (gniemcew@yahoo.com)
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is 
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
 * Plugin 'PHP Script' for the 'page_php_content' extension.
 *
 * @author	Gary <gniemcew@yahoo.com>
 */
# q.ms001 | 07.06.2006 | Bugfixing mit dem öffnenden Short-php-Tag

require_once(PATH_tslib."class.tslib_pibase.php");

class tx_pagephpcontent_pi1 extends tslib_pibase
{
	var $prefixId = "tx_pagephpcontent_pi1";
	var $scriptRelPath = "pi1/class.tx_pagephpcontent_pi1.php";
	var $extKey = "page_php_content";

	//
	// PHP Script Page Content Type
	//
	// Adds a "PHP Script" non-cached page content type. You can enter a snippet of PHP code as a part of a page and have
	// it executed when building a page without writing an extension or going through tag processing with parseFunc.
	// Uses the dreaded PHP eval().
	//
	
	function ext_getContentVar($key)
	{
		return $this->cObj->data[$key];
	}
	function main($content,$conf)
	{
		$parse["parseFunc"]="< lib.parseFunc";
		$parse["parseFunc."]["allowTags"]="b,i,u,a,img,br,div,center,pre,font,hr,sub,sup,p,strong,em,li,ul,ol,blockquote,strike,span,table,tr,td,th,tbody,dl,dt,dd,h1,h2";
		$parse["parseFunc."]["nonTypoTagStdWrap."]["encapsLines."]["addAttributes."]["P."]["style"]="";
		$parse["parseFunc."]["nonTypoTagStdWrap."]["encapsLines."]["addAttributes."]["PRE."]["style"]="";

		$this->local_cObj = t3lib_div::makeInstance('tslib_cObj'); // Local cObj.

		$phpCode = $this->ext_getContentVar("tx_pagephpcontent_php_code");
	
		ob_start();
#		eval("?" . chr(62) . $phpCode . chr(60) . "?");  #q.ms001
		eval("?" . chr(62) . $phpCode );  #q.ms001
		$content = ob_get_contents();
		ob_end_clean();

		if(strpos($content,"PARSE")===0) {
			$content = $this->local_cObj->stdWrap(substr($content,5), $parse);
			// alle <br> in <br /> umwandeln
			$content=str_replace("<br>","<br />",$content);
		}
		
		return $content;
	}
}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/page_php_content/pi1/class.tx_pagephpcontent_pi1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/page_php_content/pi1/class.tx_pagephpcontent_pi1.php"]);
}

?>