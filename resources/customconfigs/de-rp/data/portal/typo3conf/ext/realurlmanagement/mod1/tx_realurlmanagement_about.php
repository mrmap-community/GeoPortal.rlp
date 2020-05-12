<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005-2006 Juraj Sulek (juraj@sulek.sk)
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
 * Plugin realurl_management.
 *
 * @author	Juraj Sulek <juraj@sulek.sk>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   41: class tx_realurlmanagement_about extends t3lib_SCbase
 *   50:     function showModule()
 *
 * TOTAL FUNCTIONS: 1
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
class tx_realurlmanagement_about extends t3lib_SCbase {

	var $helpfunc;

	/**
	 * return the About site (mod-option - About)
	 *
	 * @return	string		HTML - About site
	 */
	function showModule(){
		return '';


		return $this->pObj->doc->section($LANG->getLL("alias_title"),$return_content,0,1);
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/realurlmanagement/mod1/tx_realurlmanagement_about.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/realurlmanagement/mod1/tx_realurlmanagement_about.php']);
}

?>