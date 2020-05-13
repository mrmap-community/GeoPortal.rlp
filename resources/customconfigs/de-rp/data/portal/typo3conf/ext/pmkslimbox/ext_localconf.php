<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2007 Peter Klein (peter@umloud.dk)
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
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

if (!defined ("TYPO3_MODE")) 	die ('Access denied.');

/**
 * function to be used in TypoScript
 * this is a small wrapper for adding the Slimbox script after the Mootools Library.
 * This is needed because headerdata added with "page.headerData" is added BEFORE
 * the headerdata which is added using PHP.
 */
 if (!class_exists('user_slimbox')) {
	class user_slimbox {
		var $cObj;
		function old_addJS($content,$conf) {
			$GLOBALS['TSFE']->additionalHeaderData['slimbox'] = $this->cObj->stdWrap($conf['jsdata'], $conf['jsdata.']);
		}
		
		function addJS($content,$conf) {
			$jsdata = $this->cObj->stdWrap($conf['jsdata'], $conf['jsdata.']);
			$jsfile = preg_replace('|^'.PATH_site.'|i','',t3lib_div::getFileAbsFileName($this->cObj->stdWrap($conf['jsfile'], $conf['jsfile.'])));
			if ($jsfile!='') $GLOBALS['TSFE']->additionalHeaderData['slimbox'] .= chr(10).'<script type="text/javascript" src="'.$jsfile.'"></script>';
			if ($jsdata!='') $GLOBALS['TSFE']->additionalHeaderData['slimbox'] .= chr(10).'<script type="text/javascript">'.chr(10).'/*<![CDATA[*/'.chr(10).'<!--'.chr(10).$jsdata.chr(10).'// -->'.chr(10).'/*]]>*/'.chr(10).'</script>';
		}

		function addJSRet($content,$conf) {
			$return="";
			$jsdata = $this->cObj->stdWrap($conf['jsdata'], $conf['jsdata.']);
			$jsfile = preg_replace('|^'.PATH_site.'|i','',t3lib_div::getFileAbsFileName($this->cObj->stdWrap($conf['jsfile'], $conf['jsfile.'])));
			if ($jsfile!='') $return .= chr(10).'<script type="text/javascript" src="'.$jsfile.'"></script>';
			if ($jsdata!='') $return .= chr(10).'<script type="text/javascript">'.chr(10).'/*<![CDATA[*/'.chr(10).'<!--'.chr(10).$jsdata.chr(10).'// -->'.chr(10).'/*]]>*/'.chr(10).'</script>';
			return $return;
		}

	}
}
?>