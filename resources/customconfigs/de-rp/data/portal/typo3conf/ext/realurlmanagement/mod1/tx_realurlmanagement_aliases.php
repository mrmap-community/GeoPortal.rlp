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
 *   41: class tx_realurlmanagement_aliases extends t3lib_SCbase
 *   61:     function showModule()
 *
 * TOTAL FUNCTIONS: 1
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
class tx_realurlmanagement_aliases extends t3lib_SCbase {
	var $action='';
	var $actionError='';
	var $action_hash='';
	var $action_pageid=0;
	var $action_cacheid=0;
	var $showEditInput=false;
	var $action_save=false;
	var $action_array=array();
	var $pageTitle='';

	var $pageBrowser=array();

	var $helpfunc;

	/**
	 * return the Alias site (mod-option - Alias)
	 *
	 * @return	string		HTML - Alias site
	 */
	function showModule(){
		global $LANG,$BACK_PATH;
		if(!$this->pObj->perms_aliases_show){return '';}
		$return_content='';
		/* actions begin */
		$this->action=t3lib_div::_GP('act');
		$action_uid=intval(t3lib_div::_GP('action_uid'));
		$action_tablename=t3lib_div::_GP('action_tablename');
		$action_alias=$this->helpfunc->encodePageName(t3lib_div::_GP('action_alias'),$action_tablename);
		$action_tstampel=t3lib_div::_GP('tstampel');
		$action_scroll=t3lib_div::_GP('act_scroll');
		/* create new begin */
		if(($this->pObj->perms_aliases_create)&&($this->action=='create')&&($action_uid!=0)&&($action_tablename!='')&&($action_tstampel!=0)){
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
				'tx_realurl_uniqalias',
				'uid='.$action_uid.' AND tablename='.$GLOBALS['TYPO3_DB']->fullQuoteStr($action_tablename,'tx_realurl_uniqalias').' AND tstamp='.$action_tstampel,
				array('expire'=>time())
			);
			$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'tablename,tstamp,field_alias,field_id,value_alias, value_id, lang',
				'tx_realurl_uniqalias',
				'uid='.$action_uid.' AND tablename='.$GLOBALS['TYPO3_DB']->fullQuoteStr($action_tablename,'tx_realurl_uniqalias').' AND tstamp='.$action_tstampel
			);
			if($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0){
				$action_tstampel=time();
				$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				$insert_array['tablename']=$row['tablename'];
				$insert_array['tstamp']=$action_tstampel;
				$insert_array['expire']=0;
				$insert_array['field_alias']=$row['field_alias'];
				$insert_array['field_id']=$row['field_id'];
				$insert_array['value_alias']=$row['value_alias'].'_new_'.$action_tstampel;
				$insert_array['value_id']=$row['value_id'];
				$insert_array['lang']=$row['lang'];
				$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_realurl_uniqalias',$insert_array);
				$this->action='edit';
				$action_uid=$GLOBALS['TYPO3_DB']->sql_insert_id();
			};
		}
		/* create new end */
		/* delete begin */
		if(($this->action=='delete')&&($this->pObj->perms_aliases_delete)&&($action_uid!=0)&&($action_tablename!='')&&($action_tstampel!=0)){
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_realurl_uniqalias','uid='.$action_uid.' AND tablename='.$GLOBALS['TYPO3_DB']->fullQuoteStr($action_tablename,'tx_realurl_uniqalias').' AND tstamp='.$action_tstampel);
		}
		/* delete end */
		/* edit save begin */
		$this->action_save=false; // if the save button was entered
		$this->showEditInput=false; // if the text input field should be shown
		$this->actionError='';
		if(($this->action=='edit')&&($this->pObj->perms_aliases_edit)&&($action_uid!=0)&&($action_tablename!='')&&($action_tstampel!=0)){
				$this->showEditInput=true;
			$action_scroll='id_'.$action_uid.'_'.$action_tablename.'_'.$action_tstampel;
			if((intval(t3lib_div::_GP('editsave_x'))!=0)&&(intval(t3lib_div::_GP('editsave_y'))!=0)){
				$this->action_save=true;
				$this->showEditInput=false;
			}
			if((intval(t3lib_div::_GP('closedok_x'))!=0)&&(intval(t3lib_div::_GP('closedok_y'))!=0)){
				$this->action_save=false;
				$this->showEditInput=false;
			}
			if($this->action_save){
				if($action_alias!=''){
					/* kontrola ci uz taka adresa existuje ak ano tak nic nerob */
					/* nedovolim vobec aby bol rovnaky dva krat ani pre rozdielne tabulky */
					$res_num=$GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'uid,tablename',
						'tx_realurl_uniqalias',
						'value_alias='.$GLOBALS['TYPO3_DB']->fullQuoteStr($action_alias,'tx_realurl_uniqalias')
					);
					$num_num=$GLOBALS['TYPO3_DB']->sql_num_rows($res_num);
					$row_num= $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_num);
					if(($num_num==0)||(($num_num==1)&&(($row_num['uid']==$action_uid)&&($row_num['tablename']==$action_tablename)&&($row_num['tstamp']==$action_tstampel)))){
						$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
							'tx_realurl_uniqalias',
							'uid='.$action_uid.' AND tablename='.$GLOBALS['TYPO3_DB']->fullQuoteStr($action_tablename,'tx_realurl_uniqalias').' AND tstamp='.$action_tstampel,
							array('value_alias'=>$action_alias)
							);
					}else{
						$this->actionError=$LANG->getLL('alias_editSaveExist',1);
						$this->showEditInput=true;
					}
				}else{
					$this->actionError=$LANG->getLL('alias_editSaveEmpty',1);
					$this->showEditInput=true;
				};
			};
		}
		/* expire begin */
		if(($this->action=='expire')&&($action_uid!=0)&&($action_tablename!='')&&($this->pObj->perms_aliases_expire)&&($action_tstampel!=0)){
			$action_scroll='id_'.$action_uid.'_'.$action_tablename.'_'.$action_tstampel;
			$retarray=$this->helpfunc->changeExpireDate('tx_realurl_uniqalias','uid='.$action_uid.' AND tablename='.$GLOBALS['TYPO3_DB']->fullQuoteStr($action_tablename,'tx_realurl_uniqalias').' AND tstamp='.$action_tstampel,'expire');
			$this->action_save=$retarray['action_save'];
			$this->showEditInput=$retarray['showEditInput'];
		}
		/* expire end */



		/* pocas editovania a po jeho skonceni skocit na miesto kde som editoval begin */
		if($action_scroll!=''){
			$this->pObj->doc->postCode.='
			<script language="javascript" type="text/javascript">
				document.getElementById(\''.$action_scroll.'\').scrollIntoView();
			</script>';
		}
		/* end */
		/* edit save end */
		/* actions end */
		/* get order by begin */
		$canBeOrdered=',value_id,value_alias,tstamp,expire,';
		$orderBy=t3lib_div::_GP('ordBy');
		$orderAscDesc=t3lib_div::_GP('ordAscDesc');
		if($orderAscDesc!='desc'){$orderAscDesc='asc';}
		if(strpos($canBeOrdered,$orderBy)===false){$orderBy='tstamp';}
		/* pagebrowser begin */
		$res_pageBrowser=$GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'count(uid) AS countuid',
			'tx_realurl_uniqalias',
			''
		);
		$row_pageBrowser = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_pageBrowser);
		$this->pageBrowser['count']=$row_pageBrowser['countuid'];
		$this->pageBrowser['showElements']=$this->pObj->pagebrowser_alias['showElements'];
		$this->pageBrowser['showPages']=$this->pObj->pagebrowser_alias['showPages'];
		$this->pageBrowser['pointer']=intval(t3lib_div::_GP('pb_pointer'));
		$this->pageBrowser['URL']='&pb_pointer='.$this->pageBrowser['pointer'];
		$this->pageBrowser['FORM_HIDDEN']='<input type="hidden" name="pb_pointer" value="'.$this->pageBrowser['pointer'].'" />';
		$this->pageBrowser['oldURL']='index.php?mode='.$this->pObj->MOD_SETTINGS['mode'].'&depth='.$this->pObj->MOD_SETTINGS['depth'].'&id='.$this->pObj->pageuid.'&tstamp='.time().'&ordBy='.$orderBy.'&ordAscDesc='.$orderAscDesc;
		$pageBrowser=($this->pageBrowser['count'] > $this->pageBrowser['showElements'])?$this->helpfunc->getPageBrowser($this->pageBrowser):'';

		/* pagebrowser end */

		$orderByRows=$this->helpfunc->getOrderBy('tstamp,value_id,value_alias,expire',$orderBy,$orderAscDesc,$this->pageBrowser['URL']);
		/* get order by end */

		$return_content.=$pageBrowser;


		$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid, tstamp, tablename, field_alias, field_id,value_alias,value_id,lang,expire',
			'tx_realurl_uniqalias',
			'1=1',
			'',
			'tablename, '.$orderBy.' '.$orderAscDesc,
			($this->pageBrowser['pointer']*$this->pageBrowser['showElements']).','.$this->pageBrowser['showElements']);
		$row_odlTableName='';
		$return_table='';
		$bgCol='';
		$emptyRow1='';
		$emptyRow2='';
		if(($this->pObj->perms_aliases_delete)||($this->pObj->perms_aliases_create)){
			$emptyRow1='<td class="bgColor5" nowrap="nowrap">&nbsp;</td>';
			$deleteRecord=$emptyRow1;
			$createRecord=$emptyRow1;
		}
		if(($this->pObj->perms_aliases_edit)||($this->pObj->perms_aliases_expire)){
			$emptyRow2='<td class="bgColor5" nowrap="nowrap">&nbsp;</td>';
			$editRecord=$emptyRow2;
			$expireRecord=$emptyRow2;
		};
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
			/* ked nie su rovnake tablename tak vytvor novu tabulku */
			$tr_id=' id="id_'.$row['uid'].'_'.$row['tablename'].'_'.$row['tstamp'].'"';
			if($row_odlTableName!=$row['tablename']){
				$return_table='
				<tr>
					<td class="bgColor2" colspan="5"><strong>'.$row['tablename'].'</strong>&nbsp;</td>
				</tr>';
				$row_odlTableName=$row['tablename'];
			};
			/* edit begin */
			$editline=$row['value_alias'];
			if($this->pObj->perms_aliases_edit){
				$aHrefEdit = 'index.php?mode='.$this->pObj->MOD_SETTINGS['mode'].'&depth='.$this->pObj->MOD_SETTINGS['depth'].'&id='.$this->pObj->pageuid.'&act=edit&action_uid='.$row['uid'].'&action_tablename='.$row['tablename'].'&ordBy='.$orderBy.'&ordAscDesc='.$orderAscDesc.'&tstampel='.$row['tstamp'].'&tstamp='.time().$this->pageBrowser['URL'];
				$editRecord='<td class="bgColor5" nowrap="nowrap">&nbsp;<a href="'.htmlspecialchars($aHrefEdit).'" onclick="'.htmlspecialchars('return confirm('.$LANG->JScharCode($LANG->getLL('alias_editWarning')).');').'"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/edit2.gif','width="12" height="12"').' border="0" title="'.$LANG->getLL('edit',1).'" alt="" /></a>&nbsp;</td>';
				if(($this->action=='edit')&&($row['uid']==$action_uid)&&($row['tablename']==$action_tablename)&&($this->showEditInput)){
					if($aliasExist){$editline=$action_alias;};
					$editRecord=$emptyRow2;
					$editline='<input type="text" size="60" value="'.$this->helpfunc->decodePageName($editline).'" id="action_alias" name="action_alias"/>';
					$editline.='&nbsp;';
					$editline.='<input type="image" '.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/saveandclosedok.gif','').' width="21" height="16" name="editsave" id="editsave" title="'.$LANG->getLL('edit_saveandclose').'"/>';
					$editline.='&nbsp;';
					$editline.='<input type="image" '.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/closedok.gif','').' width="21" height="16" name="closedok" id="closedok" title="'.$LANG->getLL('edit_close').'"/>';
					$editline.='<input type="hidden" name="action_uid" value="'.$row['uid'].'" />';
					$editline.='<input type="hidden" name="action_tablename" value="'.$row['tablename'].'" />';
					$editline.='<input type="hidden" name="tstampel" value="'.$row['tstamp'].'" />';
					$editline.='<input type="hidden" name="act" value="edit" />';
					$editline.=$this->pageBrowser['FORM_HIDDEN'];
				};
			};
			/* edit end */
			/* expire begin */
			$expireline=$this->helpfunc->getDateTime($row['expire']);
			if($this->pObj->perms_aliases_expire){
				$aHrefExpire = 'index.php?mode='.$this->pObj->MOD_SETTINGS['mode'].'&depth='.$this->pObj->MOD_SETTINGS['depth'].'&id='.$this->pObj->pageuid.'&act=expire&expire=one&action_uid='.$row['uid'].'&action_tablename='.$row['tablename'].'&ordBy='.$orderBy.'&ordAscDesc='.$orderAscDesc.'&tstampel='.$row['tstamp'].'&tstamp='.time().$this->pageBrowser['URL'];
				$expireRecord='<td class="bgColor5" nowrap="nowrap">&nbsp;<a href="'.htmlspecialchars($aHrefExpire).'" onclick="'.htmlspecialchars('return confirm('.$LANG->JScharCode($LANG->getLL('alias_changeExpireWarning')).');').'"><img'.t3lib_iconWorks::skinImg('../','gfx/set_expire.gif','width="9" height="9"').' border="0" title="'.$LANG->getLL('alias_changeExpireWarning',1).'" alt="" /></a>&nbsp;</td>';
				if(($this->action=='expire')&&($row['uid']==$action_uid)&&($row['tablename']==$action_tablename)&&($this->showEditInput)){
					$expireRecord=$emptyRow2;
					$expireInput['cb']='';
					$expireInput['integer']=0;
					$expireInput['text']='';
					if($row['expire']!=0){
						$expireInput['cb']='checked="checked"';
						$expireInput['integer']=$row['expire'];
						$expireInput['text']=date('H:i d-m-Y',$row['expire']);
					}
					$expireline='<input type="checkbox" ';
					$expireline.=($this->pObj->isInstalled_Date2Call['input_cb_prop']!='')?$this->pObj->isInstalled_Date2Call['input_cb_prop']:$this->pObj->default_expire_cb_prop;
					$expireline.=' '.$expireInput['cb'].' />';
					$expireline.='<input type="text" ';
					$expireline.=($this->pObj->isInstalled_Date2Call['input_text_prop']!='')?$this->pObj->isInstalled_Date2Call['input_text_prop']:$this->pObj->default_expire_text_prop;
					$expireline.=' value="'.$expireInput['text'].'" />';
					$expireline.='<input type="hidden" name="expirepage" value="'.$expireInput['integer'].'" />';
					$expireline.=($this->pObj->isInstalled_Date2Call['html']!='')?$this->pObj->isInstalled_Date2Call['html']:'';
					$expireline.='&nbsp;';
					$expireline.='<input type="image" '.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/saveandclosedok.gif','').' width="21" height="16" name="editsave" id="editsave" title="'.$LANG->getLL('edit_saveandclose').'"/>';
					$expireline.='&nbsp;';
					$expireline.='<input type="image" '.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/closedok.gif','').' width="21" height="16" name="closedok" id="closedok" title="'.$LANG->getLL('edit_close').'"/>';
					$expireline.='<input type="hidden" name="action_uid" value="'.$row['uid'].'" />';
					$expireline.='<input type="hidden" name="action_tablename" value="'.$row['tablename'].'" />';
					$editline.='<input type="hidden" name="tstampel" value="'.$row['tstamp'].'" />';
					$expireline.='<input type="hidden" name="act" value="expire" />';
					$expireline.=$this->pageBrowser['FORM_HIDDEN'];

					$this->helpfunc->writeJSForDateTimeValidation();
				};
			};
			if($this->pObj->perms_aliases_create){
				$aHrefCreate = 'index.php?mode='.$this->pObj->MOD_SETTINGS['mode'].'&depth='.$this->pObj->MOD_SETTINGS['depth'].'&id='.$this->pObj->pageuid.'&act=create&action_uid='.$row['uid'].'&action_tablename='.$row['tablename'].'&ordBy='.$orderBy.'&ordAscDesc='.$orderAscDesc.'&tstampel='.$row['tstamp'].'&tstamp='.time().$this->pageBrowser['URL'];
				$createRecord='<td class="bgColor5" nowrap="nowrap">&nbsp;<a href="'.htmlspecialchars($aHrefCreate).'" onclick="'.htmlspecialchars('return confirm('.$LANG->JScharCode($LANG->getLL('alias_createWarning')).');').'"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/new_el.gif','width="11" height="12"').' border="0" title="'.$LANG->getLL('alias_create',1).'" alt="" /></a>&nbsp;</td>';
			};
			/* expire end */
			/* delete begin */
			$deleteRecord='';
			if($this->pObj->perms_aliases_delete){
				$aHrefDelete = 'index.php?mode='.$this->pObj->MOD_SETTINGS['mode'].'&depth='.$this->pObj->MOD_SETTINGS['depth'].'&id='.$this->pObj->pageuid.'&act=delete&action_uid='.$row['uid'].'&action_tablename='.$row['tablename'].'&ordBy='.$orderBy.'&ordAscDesc='.$orderAscDesc.'&tstampel='.$row['tstamp'].'&tstamp='.time().$this->pageBrowser['URL'];
				$deleteRecord='<td class="bgColor5" nowrap="nowrap">&nbsp;<a href="'.htmlspecialchars($aHrefDelete).'" onclick="'.htmlspecialchars('return confirm('.$LANG->JScharCode($LANG->getLL('deleteWarning')).');').'"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/garbage.gif','width="12" height="12"').' border="0" title="'.$LANG->getLL('deleteItem',1).'" alt="" /></a>&nbsp;</td>';
			};
			/* delete end */
			/* action error begin */
			if(($this->actionError!='')&&($row['uid']==$action_uid)&&($row['tablename']==$action_tablename)){
				$return_table.='
				<tr'.$tr_id.'>
					'.$emptyRow1.'
					'.$emptyRow2.'
					<td class="bgColor5" nowrap="nowrap">&nbsp;</td>
					<td'.$bgCol.' nowrap="nowrap" colspan="2" style="color:red;"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/icon_fatalerror.gif','width="18" height="16"').' border="0" title="'.$this->actionError.'" alt="" /><strong>'.$this->actionError.'</strong>&nbsp;</td>
				</tr>';
				$tr_id='';
			};

			/* action error end */

			$return_table.='
				<tr'.$tr_id.'>
					'.$deleteRecord.'
					'.$emptyRow2.'
					'.$orderByRows['tstamp'].'
					<td'.$bgCol.' nowrap="nowrap"><strong>'.$LANG->getLL("alias_tstamp").':</strong>&nbsp;</td>
					<td'.$bgCol.' nowrap="nowrap">'.$this->helpfunc->getDateTime($row['tstamp']).'&nbsp;</td>
				</tr>
				<tr>
					'.$createRecord.'
					'.$editRecord.'
					'.$orderByRows['value_alias'].'
					<td'.$bgCol.' nowrap="nowrap"><strong>'.$LANG->getLL("alias_alias").':</strong>&nbsp;</td>
					<td'.$bgCol.' nowrap="nowrap">'.$editline.'&nbsp;</td>
				</tr>
				<tr>
					'.$emptyRow1.'
					'.$emptyRow2.'
					'.$orderByRows['value_id'].'
					<td'.$bgCol.' nowrap="nowrap"><strong>'.$row['field_id'].':</strong>&nbsp;</td>
					<td'.$bgCol.' nowrap="nowrap">'.$row['value_id'].'&nbsp;</td>
				</tr>
				<tr>
					'.$emptyRow1.'
					'.$expireRecord.'
					'.$orderByRows['expire'].'
					<td'.$bgCol.' nowrap="nowrap"><strong>'.$LANG->getLL("alias_expire").':</strong>&nbsp;</td>
					<td'.$bgCol.' nowrap="nowrap">'.$expireline.'&nbsp;</td>
				</tr>

				<tr>
					'.$emptyRow1.'
					'.$emptyRow2.'
					<td class="bgColor5" nowrap="nowrap">&nbsp;</td>
					<td'.$bgCol.' nowrap="nowrap" colspan="2">&nbsp;</td>
				</tr>
				';

			$bgCol=$bgCol==''?' class="bgColor-20"':'';
		};

		$return_content.='<table border="0" cellspacing="0" cellpadding="0">'.$return_table.'</table>';
		$return_content.=$pageBrowser;


		return $this->pObj->doc->section($LANG->getLL("alias_title"),$return_content,0,1);
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/realurlmanagement/mod1/tx_realurlmanagement_aliases.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/realurlmanagement/mod1/tx_realurlmanagement_aliases.php']);
}

?>