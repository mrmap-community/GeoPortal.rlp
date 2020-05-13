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
 *   41: class tx_realurlmanagement_dbclean extends t3lib_SCbase
 *   49:     function showModule()
 *
 * TOTAL FUNCTIONS: 1
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
class tx_realurlmanagement_dbclean extends t3lib_SCbase {
	var $helpfunc;

	/**
	 * return the dbClean site (mod-option - dBClean)
	 *
	 * @return	string		HTML - dbClean site
	 */
	function showModule(){
		global $LANG,$BACK_PATH;
		if(!$this->pObj->perms_tableClean_show){return '';};
		$tableNames=array('tx_realurl_chashcache','tx_realurl_errorlog','tx_realurl_pathcache','tx_realurl_redirects','tx_realurl_uniqalias','tx_realurl_urldecodecache','tx_realurl_urlencodecache');
		/* action begin */
		$counter=0;
		$action=t3lib_div::_GP('act');
		if($action=='clearTables'){
			foreach($tableNames as $table){
				if(t3lib_div::_GP($table)=='yes'){
					$GLOBALS['TYPO3_DB']->exec_DELETEquery($table,'1=1');
					$counter++;
				};
			};
			$return_content.='<img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/icon_ok.gif','width="18" height="16"').' border="0" title="'.$LANG->getLL('dbClean_cleanOKMessage',1).'" alt="" />'.$counter.$LANG->getLL('dbClean_cleanOKMessage',1);
			$return_content.=$this->pObj->doc->spacer(10);
		};

		/* action end */
		$return_content.='
			<table border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td class="bgColor2" colspan="3"><strong>'.$LANG->getLL('dbClean_tableHeader_tables',1).'</strong></td>
					<td class="bgColor2" colspan="2"><strong>'.$LANG->getLL('dbClean_tableHeader_description',1).'</strong></td>
				</tr>';
		$bgCol='';

		foreach($tableNames as $table){
			$return_content.='
				<tr>
					<td'.$bgCol.'><input type="checkbox" value="yes" name="'.$table.'" id="'.$table.'"'.(t3lib_div::_GP($table)=='yes'?' checked="checked"':'').' /></td>
					<td'.$bgCol.'>&nbsp;</td>
					<td'.$bgCol.'><strong><label for="'.$table.'">'.$table.'</label>:</strong></td>
					<td'.$bgCol.'>&nbsp;</td>
					<td'.$bgCol.'>'.$LANG->getLL('dbClean_'.$table.'_Description',1).'</td>
				</tr>
				<tr>
					<td'.$bgCol.' colspan="5">&nbsp;</td>
				</tr>';
			$bgCol=$bgCol==''?' class="bgColor-20"':'';
		}

		$return_content.='</table>';
		/* delete button begin */
		$return_content.='
			<input type="hidden" name="act" value="clearTables"/>
			<input type="hidden" name="tstamp" value="'.time().'" />
			<input type="submit" value="'.$LANG->getLL('dbClean_clearButton',1).'" />';

		/* delete button end */
		return $this->pObj->doc->section($LANG->getLL('dbClean_title',1),$return_content,0,1);
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/realurlmanagement/mod1/tx_realurlmanagement_dbclean.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/realurlmanagement/mod1/tx_realurlmanagement_dbclean.php']);
}
?>