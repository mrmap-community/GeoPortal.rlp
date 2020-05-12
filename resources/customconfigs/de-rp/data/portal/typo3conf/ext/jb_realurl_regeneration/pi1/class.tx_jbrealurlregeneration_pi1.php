<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Jan Bednarik (info@bednarik.org)
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
 * Plugin 'Delete old and create new RealURL links at once.' for the 'jb_realurl_regeneration' extension.
 *
 * @author	Jan Bednarik <info@bednarik.org>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_jbrealurlregeneration_pi1 extends tslib_pibase {
	var $prefixId = 'tx_jbrealurlregeneration_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_jbrealurlregeneration_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'jb_realurl_regeneration';	// The extension key.
	
	/**
	 * [Put your description here]
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
	
	  $GLOBALS['TSFE']->set_no_cache();
	
		$q = $GLOBALS['TYPO3_DB']->exec_SELECTquery('COUNT(cache_id)','tx_realurl_pathcache','');
		$content = '<p>'.str_replace('###X###',mysql_result($q,0),$this->pi_getLL('delete')).'</p>';
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_realurl_pathcache','');
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_realurl_chashcache','');
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_realurl_uniqalias','');
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_realurl_urldecodecache','');
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_realurl_urlencodecache','');
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_realurl_errorlog','');
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_realurl_redirects','');
		
		$q = $GLOBALS['TYPO3_DB']->exec_SELECTquery('COUNT(uid)','pages','doktype<10 AND hidden=0 AND deleted=0');
		$content .= '<p>'.str_replace('###X###',mysql_result($q,0),$this->pi_getLL('create')).'</p>';
		
		$languages = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid','sys_language','hidden=0');
		$langs = Array();
    while ($r = mysql_fetch_row($languages)) {
		  $langs[] = $r[0];
		}
		
		$pages = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title','pages','doktype<10 AND hidden=0 AND deleted=0');
		$content .= '<p>';
		while ($r = mysql_fetch_row($pages)) {
		  $content .= $this->cObj->typolink($r[1],Array('parameter'=>$r[0])).', ';
		  
		  foreach ($langs as $l) {
        $this->cObj->typolink('',Array('parameter'=>$r[0],'additionalParams'=>'&L='.$l)).', ';
      }
		  
		}	
		$content .= '</p>';
		return $content;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jb_realurl_regeneration/pi1/class.tx_jbrealurlregeneration_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jb_realurl_regeneration/pi1/class.tx_jbrealurlregeneration_pi1.php']);
}

?>
