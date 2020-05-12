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
 *   44: class tx_realurlmanagement_errors extends t3lib_SCbase
 *   52:     function getErrorDescription($string)
 *   64:     function getErrorLastReferer($url)
 *   78:     function getErrorUrl($url)
 *   87:     function showModule()
 *
 * TOTAL FUNCTIONS: 4
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
class tx_realurlmanagement_errors extends t3lib_SCbase {
	var $helpfunc;
	/**
	 * return formated description for getErrors function, now it doesn't do enything yet
	 *
	 * @param	string		$string: Description
	 * @return	string		formated Description
	 */
	function getErrorDescription($string){
		/* doplnit spracovanie error textu napriklad prelozit */
		return $string;
	}


	/**
	 * return formated lastReferer url for getErrors function
	 *
	 * @param	string		$url: url
	 * @return	string		formated lastReferer url
	 */
	function getErrorLastReferer($url){
		if(trim($url)!=''){
			return '<a href="'.$url.'" target="_blank">'.$url.'</a>';
		}else{
			return '&nbsp;';
		}
	}

	/**
	 * return formated url for getErrors function, now it doesn't do enything yet
	 *
	 * @param	string		$url: URL
	 * @return	string		formated URL
	 */
	function getErrorUrl($url){
		return $url;
	}

	/**
	 * return the Errors site (mod-option - Errors)
	 *
	 * @return	string		HTML - Errors site
	 */
	function showModule(){
		global $LANG,$BACK_PATH;
		if(!$this->pObj->perms_errors_show){return '';};
		/* actions begin */
		$action=t3lib_div::_GP('act');
		$action_url_hash=intval(t3lib_div::_GP('act_urlhash'));
		$action_crdate=intval(t3lib_div::_GP('act_crdate'));
		if(($action_crdate!=0)&&($action_url_hash!=0)){
			if(($action=='delete')&&($this->pObj->perms_errors_delete)){
				$delete_action=t3lib_div::_GP('delete');
				$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_realurl_errorlog','url_hash='.$action_url_hash.' AND cr_date='.$action_crdate);
			}
			if(($action=='clearCount')&&($this->pObj->perms_errors_clearCount)){
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_realurl_errorlog','url_hash='.$action_url_hash.' AND cr_date='.$action_crdate,array('counter'=>0));
			}
		}
		/* actions end */
		/* get order by begin */
		$canBeOrdered=',counter,cr_date,tstamp,error,url,last_referer,';
		$orderBy=t3lib_div::_GP('ordBy');
		$orderAscDesc=t3lib_div::_GP('ordAscDesc');
		if($orderAscDesc!='asc'){$orderAscDesc='desc';}
		if(strpos($canBeOrdered,$orderBy)===false){$orderBy='counter';}
		/* pagebrowser begin */
		$res_pageBrowser=$GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'count(url_hash) AS countuid',
			'tx_realurl_errorlog',
			''
		);
		$row_pageBrowser = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_pageBrowser);
		$this->pageBrowser['count']=$row_pageBrowser['countuid'];
		$this->pageBrowser['showElements']=$this->pObj->pagebrowser_error['showElements'];
		$this->pageBrowser['showPages']=$this->pObj->pagebrowser_error['showPages'];
		$this->pageBrowser['pointer']=intval(t3lib_div::_GP('pb_pointer'));
		$this->pageBrowser['URL']='&pb_pointer='.$this->pageBrowser['pointer'];
		$this->pageBrowser['FORM_HIDDEN']='<input type="hidden" name="pb_pointer" value="'.$this->pageBrowser['pointer'].'" />';
		$this->pageBrowser['oldURL']='index.php?mode='.$this->pObj->MOD_SETTINGS['mode'].'&depth='.$this->pObj->MOD_SETTINGS['depth'].'&id='.$this->pObj->pageuid.'&tstamp='.time().'&ordBy='.$orderBy.'&ordAscDesc='.$orderAscDesc;
		$pageBrowser=($this->pageBrowser['count'] > $this->pageBrowser['showElements'])?$this->helpfunc->getPageBrowser($this->pageBrowser):'';
		/* pagebrowser end */
		$orderByRows=$this->helpfunc->getOrderBy('counter,cr_date,tstamp,error,url,last_referer',$orderBy,$orderAscDesc,$this->pageBrowser['URL']);
		/* get order by end */

		//$line_style='style="background-image:url('.$BACK_PATH.'gfx/line.gif);background-position:left top;background-repeat:repeat-y;"';
		$return_content.=$pageBrowser;
		$return_content.='
			<table border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td class="bgColor2" colspan="4"><strong>'.$LANG->getLL('error_table_row2_header',1).'</strong></td>
				</tr>';
		$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'url_hash, url, error, last_referer, counter,cr_date,tstamp',
			'tx_realurl_errorlog',
			'1=1',
			'',
			$orderBy.' '.$orderAscDesc,
			($this->pageBrowser['pointer']*$this->pageBrowser['showElements']).','.$this->pageBrowser['showElements']);
		$bgCol='';
		$emptyDeleteRow='';
		if($this->pObj->perms_errors_delete){
			$emptyDeleteRow='<td class="bgColor5" nowrap="nowrap">&nbsp;</td>';
		};
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
			$deleteRecord='';
			if($this->pObj->perms_errors_delete){
				$aHrefDelete = 'index.php?mode='.$this->pObj->MOD_SETTINGS['mode'].'&depth='.$this->pObj->MOD_SETTINGS['depth'].'&id='.$this->pObj->pageuid.'&act=delete&act_urlhash='.$row['url_hash'].'&act_crdate='.$row['cr_date'].'&ordBy='.$orderBy.'&ordAscDesc='.$orderAscDesc.'&tstamp='.time().$this->pageBrowser['URL'];
				$deleteRecord='<td class="bgColor5" nowrap="nowrap">&nbsp;<a href="'.htmlspecialchars($aHrefDelete).'" onclick="'.htmlspecialchars('return confirm('.$LANG->JScharCode($LANG->getLL('deleteWarning')).');').'"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/garbage.gif','width="12" height="12"').' border="0" title="'.$LANG->getLL('deleteItem',1).'" alt="" /></a>&nbsp;</td>';
			};
			$clearCountRecord=$emptyDeleteRow;
			if(($this->pObj->perms_errors_clearCount)&&(intval($row['counter'])>0)){
				$aHrefClearCount = 'index.php?mode='.$this->pObj->MOD_SETTINGS['mode'].'&depth='.$this->pObj->MOD_SETTINGS['depth'].'&id='.$this->pObj->pageuid.'&act=clearCount&act_urlhash='.$row['url_hash'].'&act_crdate='.$row['cr_date'].'&ordBy='.$orderBy.'&ordAscDesc='.$orderAscDesc.'&tstamp='.time().$this->pageBrowser['URL'];
				$clearCountRecord='<td class="bgColor5" nowrap="nowrap">&nbsp;<a href="'.htmlspecialchars($aHrefClearCount).'" onclick="'.htmlspecialchars('return confirm('.$LANG->JScharCode($LANG->getLL('error_clearCountWarning')).');').'"><img'.t3lib_iconWorks::skinImg('../','gfx/clear_counts.gif','width="12" height="14"').' border="0" title="'.$LANG->getLL('error_clearCount',1).'" alt="" /></a>&nbsp;</td>';
			}
			$return_content.='
				<tr>
					'.$deleteRecord.'
					'.$orderByRows['counter'].'
					<td'.$bgCol.' nowrap="nowrap"><strong>'.$LANG->getLL("error_counter").':</strong>&nbsp;</td>
					<td'.$bgCol.' nowrap="nowrap">'.$row['counter'].'&nbsp;</td>
				</tr>
				<tr>
					'.$clearCountRecord.'
					'.$orderByRows['cr_date'].'
					<td'.$bgCol.' nowrap="nowrap"><strong>'.$LANG->getLL("error_crdate").':</strong>&nbsp;</td>
					<td'.$bgCol.' nowrap="nowrap">'.$this->helpfunc->getDateTime($row['cr_date']).'&nbsp;</td>
				</tr>
				<tr>
					'.$emptyDeleteRow.'
					'.$orderByRows['tstamp'].'
					<td'.$bgCol.' nowrap="nowrap"><strong>'.$LANG->getLL("error_tstamp").':</strong>&nbsp;</td>
					<td'.$bgCol.' nowrap="nowrap">'.$this->helpfunc->getDateTime($row['tstamp']).'&nbsp;</td>
				</tr>
				<tr>
					'.$emptyDeleteRow.'
					'.$orderByRows['error'].'
					<td'.$bgCol.' nowrap="nowrap"><strong>'.$LANG->getLL("error_error").':</strong>&nbsp;</td>
					<td'.$bgCol.'>'.$this->getErrorDescription($row['error']).'&nbsp;</td>
				</tr>
				<tr>
					'.$emptyDeleteRow.'
					'.$orderByRows['url'].'
					<td'.$bgCol.' nowrap="nowrap"><strong>'.$LANG->getLL("error_url").':</strong>&nbsp;</td>
					<td'.$bgCol.' nowrap="nowrap">'.$this->getErrorUrl($row['url']).'&nbsp;</td>
				</tr>
				<tr>
					'.$emptyDeleteRow.'
					'.$orderByRows['last_referer'].'
					<td'.$bgCol.' nowrap="nowrap"><strong>'.$LANG->getLL("error_lastreferer").':</strong>&nbsp;</td>
					<td'.$bgCol.' nowrap="nowrap">'.$this->getErrorLastReferer($row['last_referer']).'&nbsp;</td>
				</tr>
				<tr>
					'.$emptyDeleteRow.'
					<td class="bgColor5" nowrap="nowrap">&nbsp;</td>
					<td'.$bgCol.' nowrap="nowrap" colspan="2">&nbsp;</td>
				</tr>';
			$bgCol=$bgCol==''?' class="bgColor-20"':'';
		};


		$return_content.='
			</tbody>
		</table>';
		$return_content.=$pageBrowser;
		return $this->pObj->doc->section($LANG->getLL('error_title',1),$return_content,0,1);
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/realurlmanagement/mod1/tx_realurlmanagement_errors.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/realurlmanagement/mod1/tx_realurlmanagement_errors.php']);
}
?>