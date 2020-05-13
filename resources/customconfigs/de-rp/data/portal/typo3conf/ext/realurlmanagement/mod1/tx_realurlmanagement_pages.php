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
 *   45: class tx_realurlmanagement_pages extends t3lib_SCbase
 *   67:     function getPagesEditValue($value)
 *   86:     function getPagesOnePage($IMAGES_OUT,$title,$id,$bgCol='')
 *  209:     function editwholepage(wholepage,segment)
 *  367:     function getPagesTreeImages($data,$data_nach)
 *  392:     function showModule()
 *
 * TOTAL FUNCTIONS: 5
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
class tx_realurlmanagement_pages extends t3lib_SCbase {
	var $action='';
	var $actionError='';
	var $action_hash='';
	var $action_pageid=0;
	var $action_cacheid=0;
	var $showEditInput=false;
	var $action_save=false;
	var $action_array=array();
	var $pageTitle='';
	var $shownCacheId='';

	var $lang_array=array();

	var $helpfunc;

	/**
	 * Split the pagename by last '/' symbol
	 *
	 * @param	string		$value: pagename
	 * @return	array		array('1'=>value after '/', '2'=>value before '/')
	 */
	function getPagesEditValue($value){
		$position=strrpos($value,'/');
		if($position===false){
			$return_array=array('1'=>$value,'2'=>'');
		}else{
			$return_array=array('1'=>substr($value,$position+1),'2'=>substr($value,0,$position+1));
		}
		return $return_array;
	}

	/**
	 * HTML of records from 1 page
	 *
	 * @param	array		[1]=>HTML for images for page, [2]=>HTML for images under page
	 * @param	string		page title
	 * @param	int		page id
	 * @param	string		class for background color
	 * @return	string		HTML for 1 page
	 */
	function getPagesOnePage($IMAGES_OUT,$title,$id,$bgCol=''){
		global $BACK_PATH,$LANG;

		$cells=array();
		$i=1;
		$res2=$GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'cache_id, page_id, language_id, rootpage_id, mpvar,hash,pagepath,expire',
			'tx_realurl_pathcache',
			'page_id='.$id);

		$emptyRow1='';
		$emptyRow2='';
		if(($this->pObj->perms_pages_delete)||($this->pObj->perms_pages_create)){
			$emptyRow1='<td class="bgColor5" nowrap="nowrap">&nbsp;</td>';
			$deleteRecord=$emptyRow1;
			$createRecord=$emptyRow1;
		};
		if(($this->pObj->perms_pages_edit)||($this->pObj->perms_pages_expire)){
			$emptyRow2='<td class="bgColor5" nowrap="nowrap">&nbsp;</td>';
			$editRecord=$emptyRow2;
			$expireRecord=$emptyRow2;
		};
		if($GLOBALS['TYPO3_DB']->sql_num_rows($res2)==0){
			/* ked vytvaram uplne novy */
			/*
			if($this->pObj->perms_pages_create){
				$aHrefCreate = 'index.php?mode='.$this->pObjMOD_SETTINGS['mode'].'&depth='.$this->pObj->MOD_SETTINGS['depth'].'&id='.$this->pObj->pageuid.'&act=createNew&tstamp='.time();
				$emptyRow1='<td class="bgColor5" nowrap="nowrap">&nbsp;<a href="'.htmlspecialchars($aHrefCreate).'" onclick="'.htmlspecialchars('return confirm('.$LANG->JScharCode($LANG->getLL('getPages_createWarning')).');').'"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/new_el.gif','width="11" height="12"').' border="0" title="'.$LANG->getLL('getPages_create',1).'" alt="" /></a>&nbsp;</td>';
			};*/
			/* ked vytvaram uplne novy end */
			$return_page_tree.='
			<tr>
				<td nowrap="nowrap"'.$bgCol.'>'.$IMAGES_OUT[1].htmlspecialchars(t3lib_div::fixed_lgd($title,$this->pObj->tLen)).'&nbsp;</td>
				<td'.$bgCol.' nowrap="nowrap"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/line.gif','width="5" height="16"').' alt="" /></td>
				'.$emptyRow1.'
				'.$emptyRow2.'
				<td'.$bgCol.' nowrap="nowrap">&nbsp;</td>
				<td nowrap="nowrap"'.$bgCol.'>&nbsp;</td>
			</tr>
			';
			return $return_page_tree;
		}
		$actionError='';
		while($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res2)){
			$this->shownCacheId.=$row2['cache_id'].',';
			$tr_id=' id="id_'.$row2['cache_id'].'_'.$row2['page_id'].'_'.$row2['hash'].'"';
			$editline=$row2['pagepath'];
			$expireline=$this->helpfunc->getDateTime($row2['expire']);
			if($this->pObj->perms_pages_delete){
				$aHrefDelete = 'index.php?mode='.$this->pObj->MOD_SETTINGS['mode'].'&depth='.$this->pObj->MOD_SETTINGS['depth'].'&id='.$this->pObj->pageuid.'&act=delete&delete=one&act_cacheid='.$row2['cache_id'].'&act_pageid='.$row2['page_id'].'&act_hash='.$row2['hash'].'&tstamp='.time();
				$deleteRecord='<td class="bgColor5" nowrap="nowrap">&nbsp;<a href="'.htmlspecialchars($aHrefDelete).'" onclick="'.htmlspecialchars('return confirm('.$LANG->JScharCode($LANG->getLL('deleteWarning')).');').'"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/garbage.gif','width="12" height="12"').' border="0" title="'.$LANG->getLL('deleteItem',1).'" alt="" /></a>&nbsp;</td>';
			};
			if($this->pObj->perms_pages_expire){
				$aHrefExpire = 'index.php?mode='.$this->pObj->MOD_SETTINGS['mode'].'&depth='.$this->pObj->MOD_SETTINGS['depth'].'&id='.$this->pObj->pageuid.'&act=expire&expire=one&act_cacheid='.$row2['cache_id'].'&act_pageid='.$row2['page_id'].'&act_hash='.$row2['hash'].'&tstamp='.time();
				$expireRecord='<td class="bgColor5" nowrap="nowrap">&nbsp;<a href="'.htmlspecialchars($aHrefExpire).'" onclick="'.htmlspecialchars('return confirm('.$LANG->JScharCode($LANG->getLL('getPages_changeExpireWarning')).');').'"><img'.t3lib_iconWorks::skinImg('../','gfx/set_expire.gif','width="9" height="9"').' border="0" title="'.$LANG->getLL('getPages_changeExpire',1).'" alt="" /></a>&nbsp;</td>';
				if(($this->action=='expire')&&($row2['cache_id']==$this->action_cacheid)&&($row2['page_id']==$this->action_pageid)&&($row2['hash']==$this->action_hash)&&($this->showEditInput)){
					$expireRecord=$emptyRow2;
					$expireInput['cb']='';
					$expireInput['integer']=0;
					$expireInput['text']='';
					if($row2['expire']!=0){
						$expireInput['cb']='checked="checked"';
						$expireInput['integer']=$row2['expire'];
						$expireInput['text']=date('H:i d-m-Y',$row2['expire']);
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
					$expireline.='<input type="hidden" name="act_cacheid" value="'.$row2['cache_id'].'" />';
					$expireline.='<input type="hidden" name="act_pageid" value="'.$row2['page_id'].'" />';
					$expireline.='<input type="hidden" name="act_hash" value="'.$row2['hash'].'" />';
					$expireline.='<input type="hidden" name="act" value="expire" />';

					$this->helpfunc->writeJSForDateTimeValidation();

					if($this->actionError!=''){
						/* if alias exist show the error text begin */
						$actionError='
						<tr'.$tr_id.'>
							<td nowrap="nowrap"'.$bgCol.'>'.$IMAGES_OUT[2].'&nbsp;</td>
							<td'.$bgCol.' nowrap="nowrap"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/line.gif','width="5" height="16"').' alt="" /></td>
							'.$emptyRow1.'
							'.$emptyRow2.'
							<td'.$bgCol.' nowrap="nowrap" colspan="2" style="color:red;"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/icon_fatalerror.gif','width="18" height="16"').' border="0" title="'.$this->actionError.'" alt="" /><strong>'.$this->actionError.'</strong>&nbsp;</td>
						</tr>';
						$tr_id='';
						/* if alias exist show the error text end */
					};
				};
			};
			if($this->pObj->perms_pages_edit){
				$aHrefEdit = 'index.php?mode='.$this->pObj->MOD_SETTINGS['mode'].'&depth='.$this->pObj->MOD_SETTINGS['depth'].'&id='.$this->pObj->pageuid.'&act=edit&act_cacheid='.$row2['cache_id'].'&act_pageid='.$row2['page_id'].'&act_hash='.$row2['hash'].'&tstamp='.time();
				$editRecord='<td class="bgColor5" nowrap="nowrap">&nbsp;<a href="'.htmlspecialchars($aHrefEdit).'" onclick="'.htmlspecialchars('return confirm('.$LANG->JScharCode($LANG->getLL('getPages_editWarning')).');').'"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/edit2.gif','width="12" height="12"').' border="0" title="'.$LANG->getLL('edit',1).'" alt="" /></a>&nbsp;</td>';
				if(($this->action=='edit')&&($row2['cache_id']==$this->action_cacheid)&&($row2['page_id']==$this->action_pageid)&&($row2['hash']==$this->action_hash)&&($this->showEditInput)){
					$wholePath=$editline;
					if($this->nameExist){$editline=$this->pageTitle; $wholePath=$this->pageTitle;}
					$editValues=$this->getPagesEditValue($editline);
					$editRecord=$emptyRow2;
					$editline='<input type="text" size="60" value="'.$this->helpfunc->decodePageName($editValues[1]).'" id="action_path" name="action_path"/>';
					$editline.='&nbsp;';
					$editline.='<input type="image" '.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/saveandclosedok.gif','').' width="21" height="16" name="editsave" id="editsave" title="'.$LANG->getLL('edit_saveandclose').'"/>';
					$editline.='&nbsp;';
					$editline.='<input type="image" '.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/closedok.gif','').' width="21" height="16" name="closedok" id="closedok" title="'.$LANG->getLL('edit_close').'"/>';
					$editline.='<input type="hidden" name="act_cacheid" value="'.$row2['cache_id'].'" />';
					$editline.='<input type="hidden" name="act_pageid" value="'.$row2['page_id'].'" />';
					$editline.='<input type="hidden" name="act_hash" value="'.$row2['hash'].'" />';
					$editline.='<input type="hidden" name="page_before" value="'.$editValues[2].'" />';
					$editline.='<input type="hidden" name="act_wholepage" value="'.$wholePath.'" />';
					$editline.='<input type="hidden" name="act_segment" value="'.$editValues[1].'" />';
					$editline.='<input type="hidden" name="act" value="edit" />';
					if($this->pObj->perms_pages_editWholeURL){
						$editline.='<br /><input type="checkbox" value="YES" name="act_editwholepage" id="act_editwholepage" onclick="editwholepage(\''.$wholePath.'\',\''.$editValues[1].'\');" />&nbsp;<label for="act_editwholepage">'.$LANG->getLL('getPages_editWholePage').'</label>';
						$this->pObj->doc->postCode.='
							<script type="text/javascript">
								function editwholepage(wholepage,segment){
									if(document.getElementById(\'act_editwholepage\').checked){
										document.getElementById(\'action_path\').value=wholepage;
									}else{
										document.getElementById(\'action_path\').value=segment;
									}
								}
							</script>';
					};
				};
			};
			if($this->pObj->perms_pages_create){
				$aHrefCreate = 'index.php?mode='.$this->pObjMOD_SETTINGS['mode'].'&depth='.$this->pObj->MOD_SETTINGS['depth'].'&id='.$this->pObj->pageuid.'&act=create&act_cacheid='.$row2['cache_id'].'&act_pageid='.$row2['page_id'].'&act_hash='.$row2['hash'].'&tstamp='.time();
				$createRecord='<td class="bgColor5" nowrap="nowrap">&nbsp;<a href="'.htmlspecialchars($aHrefCreate).'" onclick="'.htmlspecialchars('return confirm('.$LANG->JScharCode($LANG->getLL('getPages_createWarning')).');').'"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/new_el.gif','width="11" height="12"').' border="0" title="'.$LANG->getLL('getPages_create',1).'" alt="" /></a>&nbsp;</td>';
			};

			if(($this->actionError!='')&&($row2['cache_id']==$this->action_cacheid)&&($row2['page_id']==$this->action_pageid)&&($row2['hash']==$this->action_hash)){
				$actionError='
				<tr'.$tr_id.'>
					<td nowrap="nowrap"'.$bgCol.'>'.$IMAGES_OUT[2].'&nbsp;</td>
					<td'.$bgCol.' nowrap="nowrap"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/line.gif','width="5" height="16"').' alt="" /></td>
					'.$emptyRow1.'
					'.$emptyRow2.'
					<td'.$bgCol.' nowrap="nowrap" colspan="2" style="color:red;"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/icon_fatalerror.gif','width="18" height="16"').' border="0" title="'.$this->actionError.'" alt="" /><strong>'.$this->actionError.'</strong>&nbsp;</td>
				</tr>';
				$tr_id='';
			};

			if($i==1){
				$cells[]='
					<tr'.$tr_id.'>
						<td nowrap="nowrap"'.$bgCol.'>'.$IMAGES_OUT[1].htmlspecialchars(t3lib_div::fixed_lgd($title,$this->pObj->tLen)).'&nbsp;</td>
						<td'.$bgCol.' nowrap="nowrap"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/line.gif','width="5" height="16"').' alt="" /></td>
						'.$deleteRecord.'
						'.$emptyRow2.'
						<td'.$bgCol.' nowrap="nowrap"><strong>'.$LANG->getLL("getPages_language").':</strong>&nbsp;</td>
						<td'.$bgCol.' nowrap="nowrap">'.$this->helpfunc->getLanguage($row2['language_id'],$this->lang_array).'</td>
					</tr>
					'.$actionError.'
					<tr>
						<td nowrap="nowrap"'.$bgCol.'>'.$IMAGES_OUT[2].'&nbsp;</td>
						<td'.$bgCol.' nowrap="nowrap"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/line.gif','width="5" height="16"').' alt="" /></td>
						'.$createRecord.'
						'.$editRecord.'
						<td'.$bgCol.' nowrap="nowrap"><strong>'.$LANG->getLL("getPages_pagepath").':</strong>&nbsp;</td>
						<td'.$bgCol.' nowrap="nowrap">'.$editline.'</td>
					</tr>
					<tr>
						<td nowrap="nowrap"'.$bgCol.'>'.$IMAGES_OUT[2].'&nbsp;</td>
						<td'.$bgCol.' nowrap="nowrap"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/line.gif','width="5" height="16"').' alt="" /></td>
						'.$emptyRow1.'
						'.$expireRecord.'
						<td'.$bgCol.' nowrap="nowrap"><strong>'.$LANG->getLL("getPages_expire").':</strong>&nbsp;</td>
						<td'.$bgCol.' nowrap="nowrap">'.$expireline.'</td>
					</tr>';
				if(intval($row2['rootpage_id'])!=0){
					$cells[]='
					<tr>
						<td nowrap="nowrap"'.$bgCol.'>'.$IMAGES_OUT[2].'&nbsp;</td>
						<td'.$bgCol.' nowrap="nowrap"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/line.gif','width="5" height="16"').' alt="" /></td>
						'.$emptyRow1.'
						'.$emptyRow2.'
						<td'.$bgCol.' nowrap="nowrap"><strong>'.$LANG->getLL("getPages_rootpageId").':</strong>&nbsp;</td>
						<td'.$bgCol.' nowrap="nowrap">'.$row2['rootpage_id'].'</td>
					</tr>';
				};
				if(trim($row2['mpvar'])!=''){
					$cells[]='
					<tr>
						<td nowrap="nowrap"'.$bgCol.'>'.$IMAGES_OUT[2].'&nbsp;</td>
						<td'.$bgCol.' nowrap="nowrap"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/line.gif','width="5" height="16"').' alt="" /></td>
						'.$emptyRow1.'
						'.$emptyRow2.'
						<td'.$bgCol.' nowrap="nowrap"><strong>'.$LANG->getLL("getPages_mpvar").':</strong>&nbsp;</td>
						<td'.$bgCol.' nowrap="nowrap">'.$row2['mpvar'].'</td>
					</tr>';
				};
				$i=2;
			}else{
				$cells[]='
					<tr>
						<td nowrap="nowrap"'.$bgCol.'>'.$IMAGES_OUT[2].'&nbsp;</td>
						<td'.$bgCol.' nowrap="nowrap"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/line.gif','width="5" height="16"').' alt="" /></td>
						'.$emptyRow1.'
						'.$emptyRow2.'
						<td'.$bgCol.' nowrap="nowrap" colspan="2"><hr/></td>
					</tr>
					<tr'.$tr_id.'>
						<td nowrap="nowrap"'.$bgCol.'>'.$IMAGES_OUT[2].'&nbsp;</td>
						<td'.$bgCol.' nowrap="nowrap"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/line.gif','width="5" height="16"').' alt="" /></td>
						'.$deleteRecord.'
						'.$emptyRow2.'
						<td'.$bgCol.' nowrap="nowrap"><strong>'.$LANG->getLL("getPages_language").':</strong>&nbsp;</td>
						<td'.$bgCol.' nowrap="nowrap">'.$this->helpfunc->getLanguage($row2['language_id'],$this->lang_array).'</td>
					</tr>
					'.$actionError.'
					<tr>
						<td nowrap="nowrap"'.$bgCol.'>'.$IMAGES_OUT[2].'&nbsp;</td>
						<td'.$bgCol.' nowrap="nowrap"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/line.gif','width="5" height="16"').' alt="" /></td>
						'.$createRecord.'
						'.$editRecord.'
						<td'.$bgCol.' nowrap="nowrap"><strong>'.$LANG->getLL("getPages_pagepath").':</strong>&nbsp;</td>
						<td'.$bgCol.' nowrap="nowrap">'.$editline.'</td>
					</tr>
					<tr>
						<td nowrap="nowrap"'.$bgCol.'>'.$IMAGES_OUT[2].'&nbsp;</td>
						<td'.$bgCol.' nowrap="nowrap"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/line.gif','width="5" height="16"').' alt="" /></td>
						'.$emptyRow1.'
						'.$expireRecord.'
						<td'.$bgCol.' nowrap="nowrap"><strong>'.$LANG->getLL("getPages_expire").':</strong>&nbsp;</td>
						<td'.$bgCol.' nowrap="nowrap">'.$expireline.'</td>
					</tr>';
				if(intval($row2['rootpage_id'])!=0){
					$cells[]='
					<tr>
						<td nowrap="nowrap"'.$bgCol.'>'.$IMAGES_OUT[2].'&nbsp;</td>
						<td'.$bgCol.' nowrap="nowrap"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/line.gif','width="5" height="16"').' alt="" /></td>
						'.$emptyRow1.'
						'.$emptyRow2.'
						<td'.$bgCol.' nowrap="nowrap"><strong>'.$LANG->getLL("getPages_rootpageId").':</strong>&nbsp;</td>
						<td'.$bgCol.' nowrap="nowrap">'.$row2['rootpage_id'].'</td>
					</tr>';
				};
				if(trim($row2['mpvar'])!=''){
					$cells[]='
					<tr>
						<td nowrap="nowrap"'.$bgCol.'>'.$IMAGES_OUT[2].'&nbsp;</td>
						<td'.$bgCol.' nowrap="nowrap"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/line.gif','width="5" height="16"').' alt="" /></td>
						'.$emptyRow1.'
						'.$emptyRow2.'
						<td'.$bgCol.' nowrap="nowrap"><strong>'.$LANG->getLL("getPages_mpvar").':</strong>&nbsp;</td>
						<td'.$bgCol.' nowrap="nowrap">'.$row2['mpvar'].'</td>
					</tr>';
				};
			};
			$actionError='';
		};
		$cells[]='
			<tr>
				<td nowrap="nowrap"'.$bgCol.'>'.$IMAGES_OUT[2].'&nbsp;</td>
				<td'.$bgCol.' nowrap="nowrap"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/line.gif','width="5" height="16"').' alt="" /></td>
				'.$emptyRow1.'
				'.$emptyRow2.'
				<td'.$bgCol.' nowrap="nowrap" colspan="2">&nbsp;</td>
			</tr>
		';
		return implode('
',$cells);
	}


	/**
	 * return the html for the image with page and the images under the page (the first column)
	 *
	 * @param	array		$data: actuall data
	 * @param	array		$data_nach: next data
	 * @return	array		[1]=>images for page, [2]=>images under page
	 */
	function getPagesTreeImages($data,$data_nach){
		global $BACK_PATH;
		$IMAGES_OUT[1]=$data['HTML'];
		$IMAGES_OUT[2]='';
		$images1=explode('src="',$data_nach['HTML']);
		unset($images1[0]);
		foreach($images1 as $img){
			$images2[]=strrchr(substr($img,0,strpos($img,'"')),'/');
		}
		foreach($images2 as $img2){
			if(($img2=='/line.gif')||($img2=='/join.gif')||($img2=='/joinbottom.gif')){
				$IMAGES_OUT[2].='<img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/ol/line.gif','width="18" height="16"').' alt="" />';
			}
			if($img2=='/blank.gif'){
				$IMAGES_OUT[2].='<img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/ol/blank.gif','width="18" height="16"').' alt="" />';
			}
		}
		return $IMAGES_OUT;
	}

	/**
	 * return the Pages site (mod-option - Pages)
	 *
	 * @return	string		HTML - Pages site
	 */
	function showModule(){
		global $LANG,$BE_USER,$BACK_PATH;
		$selected_set=t3lib_div::_GP('SET');
		if(!$this->pObj->perms_pages_show){return '';}
		if(intval($this->pObj->pageuid)==0){return $this->pObj->doc->section($LANG->getLL("getPages_titel"),$LANG->getLL("getPages_pleaseSelectASite"),0,1);}

		$deleteShownRecord='';
		if($this->pObj->perms_pages_deleteShown){
			$aHrefDelete = 'index.php?mode='.$this->pObj->MOD_SETTINGS['mode'].'&depth='.$this->pObj->MOD_SETTINGS['depth'].'&id='.$this->pObj->pageuid.'&act=delete&delete=shown&tstamp='.time();
			$aHrefDelete = 'javascript:submitForm(\'deleteshown\');';
			$deleteShownRecord='<a href="'.htmlspecialchars($aHrefDelete).'" onclick="'.htmlspecialchars('return confirm('.$LANG->JScharCode($LANG->getLL('getPages_deleteShownWarning')).');').'"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/garbage.gif','width="12" height="12"').' border="0" title="'.$LANG->getLL('getPages_deleteShown',1).'" alt="" />'.$LANG->getLL('getPages_deleteShown',1).'</a>';
		}

		$expireShownRecord='';
		if($this->pObj->perms_pages_expireShown){
			$aHrefExpire = 'index.php?mode='.$this->pObj->MOD_SETTINGS['mode'].'&depth='.$this->pObj->MOD_SETTINGS['depth'].'&id='.$this->pObj->pageuid.'&act=expireshown&tstamp='.time();
			$aHrefExpire='javascript:submitForm(\'expireshown\');';
			$expireShownRecord='<a href="'.htmlspecialchars($aHrefExpire).'" onclick="'.htmlspecialchars('return confirm('.$LANG->JScharCode($LANG->getLL('getPages_expireShownWarning')).');').'"><img'.t3lib_iconWorks::skinImg('../','gfx/set_expireAll.gif','width="12" height="9"').' border="0" title="'.$LANG->getLL('getPages_expireShown',1).'" alt="" />'.$LANG->getLL('getPages_expireShown',1).'</a>';
		}

		/* depth selector begin */
		$return_content_depth=$LANG->sL('LLL:EXT:lang/locallang_mod_web_perm.php:Depth',1).': ';
		$return_content_depth.=t3lib_BEfunc::getFuncMenu($this->pObj->pageuid,'SET[depth]',$this->pObj->MOD_SETTINGS['depth'],$this->pObj->MOD_MENU['depth']);
		$return_content.=$return_content_depth;
		$return_content.=$this->pObj->doc->spacer(10);
		if($this->pObj->perms_pages_deleteShown){
			$return_content.=$deleteShownRecord;
			$return_content.=$this->pObj->doc->spacer(5);
		};
		if($this->pObj->perms_pages_expireShown){
			$return_content.=$expireShownRecord;
			$return_content.=$this->pObj->doc->spacer(5);
		};
		$return_content.=$this->pObj->doc->spacer(15);
		/* depth selector end */
		$this->lang_array=$this->helpfunc->getLangArray();
		/* actions begin */
		$this->action=t3lib_div::_GP('act')==''?t3lib_div::_GP('act2'):t3lib_div::_GP('act');
		$this->action_showcacheid=t3lib_div::_GP('showcacheid');
		$this->action_hash=t3lib_div::_GP('act_hash');
		$this->action_pageid=intval(t3lib_div::_GP('act_pageid'));
		$this->action_cacheid=intval(t3lib_div::_GP('act_cacheid'));
		/* delete only one page begin */
		if(($this->action=='delete')&&($this->pObj->perms_pages_delete)){
			if(($this->action_pageid!=0)&&($this->action_cacheid!=0)&&($this->action_hash!='')){
				$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_realurl_pathcache','cache_id='.$this->action_cacheid.' AND page_id='.$this->action_pageid.' AND hash='.$GLOBALS['TYPO3_DB']->fullQuoteStr($this->action_hash,'tx_realurl_pathcache'));
				$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_realurl_decodecache','page_id='.$this->action_pageid);
				$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_realurl_encodecache','page_id='.$this->action_pageid);
			}
		};
		/* delete only one file end */
		/* delete showns begin */
		if(($this->action=='deleteshown')&&($this->pObj->perms_pages_deleteShown)&&($this->action_showcacheid!='')){
			$delete_uids=trim($this->pObj->pageuid.','.$this->helpfunc->extGetTreeList($this->pObj->pageuid,$this->pObj->MOD_SETTINGS['depth'],0,$this->pObj->perms_clause),',');
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_realurl_pathcache','cache_id IN('.$this->action_showcacheid.')');
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_realurl_decodecache','page_id IN('.$delete_uids.')');
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_realurl_encodecache','page_id IN('.$delete_uids.')');
		};
		/* delete showns end */
		$this->showEditInput=false;
		$action_scroll='';
		$this->action_save=false;
		$this->actionError='';
		/* create begin */
		/* ked vytvaram uplne novy */
		if($this->action=="createNew"){

		}
		/* ked vytvaram uplne novy end */
		if(($this->action=='create')&&($this->action_pageid!=0)&&($this->action_cacheid!=0)&&($this->action_hash!='')){
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
				'tx_realurl_pathcache',
				'cache_id='.$this->action_cacheid.' AND page_id='.$this->action_pageid.' AND hash='.$GLOBALS['TYPO3_DB']->fullQuoteStr($this->action_hash,'tx_realurl_pathcache'),
				array('expire'=>time())
			);
			$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'page_id,language_id,rootpage_id,mpvar,hash,pagepath',
				'tx_realurl_pathcache',
				'cache_id='.$this->action_cacheid.' AND page_id='.$this->action_pageid.' AND hash='.$GLOBALS['TYPO3_DB']->fullQuoteStr($this->action_hash,'tx_realurl_pathcache')
			);
			if($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0){
				$row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				$page=$row['pagepath'].'_new_'.time();
				$hash=substr(md5($page),0,10);
				$insert_array['page_id']=$row['page_id'];
				$insert_array['language_id']=$row['language_id'];
				$insert_array['expire']=0;
				$insert_array['rootpage_id']=$row['rootpage_id'];
				$insert_array['mpvar']=$row['mpvar'];
				$insert_array['pagepath']=$page;
				$insert_array['hash']=$hash;
				$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_realurl_pathcache',$insert_array);
				$this->action='edit';
				$this->action_cacheid=$GLOBALS['TYPO3_DB']->sql_insert_id();
				$this->action_hash=$hash;
			};
		}

		/* create end */
		/* expire begin */
		if(($this->action=='expire')&&($this->action_pageid!=0)&&($this->action_cacheid!=0)&&($this->action_hash!='')&&($this->pObj->perms_pages_expire)){
			$action_scroll='id_'.$this->action_cacheid.'_'.$this->action_pageid.'_'.$this->action_hash;
			$retarray=$this->helpfunc->changeExpireDate('tx_realurl_pathcache','cache_id='.$this->action_cacheid.' AND page_id='.$this->action_pageid.' AND hash='.$GLOBALS['TYPO3_DB']->fullQuoteStr($this->action_hash,'tx_realurl_pathcache'),'expire');
			$this->action_save=$retarray['action_save'];
			$this->showEditInput=$retarray['showEditInput'];
		}
		if(($this->action=='expireshown')&&($this->pObj->perms_pages_expireShown)&&($this->action_showcacheid!='')){
			/*$delete_uids=trim($this->pObj->pageuid.','.$this->helpfunc->extGetTreeList($this->pObj->pageuid,$this->pObj->MOD_SETTINGS['depth'],0,$this->pObj->perms_clause),',');*/
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
				'tx_realurl_pathcache',
				'cache_id IN('.$this->action_showcacheid.')',
				array('expire'=>time())
			);
		};
		/* expire end */
		/* edit begin */
		if(($this->action=='edit')&&($this->pObj->perms_pages_edit)){
			$this->showEditInput=true;
			$action_scroll='id_'.$this->action_cacheid.'_'.$this->action_pageid.'_'.$this->action_hash;
			if((intval(t3lib_div::_GP('editsave_x'))!=0)&&(intval(t3lib_div::_GP('editsave_y'))!=0)){
				$this->action_save=true;
				$this->showEditInput=false;
				$action_scroll='id_'.$this->action_cacheid.'_'.$this->action_pageid.'_'.$this->action_hash;
			}
			if((intval(t3lib_div::_GP('closedok_x'))!=0)&&(intval(t3lib_div::_GP('closedok_y'))!=0)){
				$this->action_save=false;
				$this->showEditInput=false;
				$action_scroll='id_'.$this->action_cacheid.'_'.$this->action_pageid.'_'.$this->action_hash;
			}
			if(($this->action_save)&&($this->action_pageid!=0)&&($this->action_cacheid!=0)&&($this->action_hash!='')){
				$action_scroll='id_'.$this->action_cacheid.'_'.$this->action_pageid.'_'.$this->action_hash;
				$newTitle1=t3lib_div::_GP('action_path');
				$action_wholePage=t3lib_div::_GP('act_editwholepage');
				if($newTitle1!=''){
					if(($action_wholePage=='YES' && $newTitle1!=t3lib_div::_GP('act_wholepage') && $this->pObj->perms_pages_editWholeURL) || ($action_wholePage!='YES' && $newTitle1!=t3lib_div::_GP('act_segment'))){
						/* ak sa nic nezmenilo tak nemusim nic robit */
						if($action_wholePage!='YES'){
							$newTitle=t3lib_div::_GP('page_before').$this->helpfunc->encodePageName($newTitle1);
						}else{
							/* pokial chceme editovat celu url tak potom musime vycistit vsetky jej casti */
							$newTitleArray=t3lib_div::trimExplode('/',$newTitle1);
							foreach($newTitleArray as $key=>$val){
								$newTitleArray[$key]=$this->helpfunc->encodePageName($val);
							}
							$newTitle=implode($newTitleArray,'/');
						}
						/* overenie ci uz existuje taka adresa */
						/* hash ziskam */
						$res_num=$GLOBALS['TYPO3_DB']->exec_SELECTquery(
							'cache_id',
							'tx_realurl_pathcache',
							'(cache_id <> '.$this->action_cacheid.' OR page_id <> '.$this->action_pageid.' OR  NOT hash='.$GLOBALS['TYPO3_DB']->fullQuoteStr($this->action_hash,'tx_realurl_pathcache').') AND pagepath='.$GLOBALS['TYPO3_DB']->fullQuoteStr($newTitle,'tx_realurl_pathcache')
						);
						if($GLOBALS['TYPO3_DB']->sql_num_rows($res_num) >0 ){
							$this->actionError=$LANG->getLL('getPages_editSaveExist',1);
							$this->showEditInput=true;
							$this->pageTitle=$newTitle;
						}else{
							$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
								'tx_realurl_pathcache',
								'cache_id='.$this->action_cacheid.' AND page_id='.$this->action_pageid.' AND hash='.$GLOBALS['TYPO3_DB']->fullQuoteStr($this->action_hash,'tx_realurl_pathcache'),
								array(
									'pagepath'=>$newTitle,
									'hash'=>substr(md5($newTitle),0,10)
								)
							);
							$this->showEditInput=false;
							$action_scroll='id_'.$this->action_cacheid.'_'.$this->action_pageid.'_'.substr(md5($newTitle),0,10);
						}
					};
				}else{
					$this->actionError=$LANG->getLL('getPages_editSaveEmpty',1);
					$this->showEditInput=true;
				};
			};
		};
		/* edit end */

		/* pocas editovania a po jeho skonceni skocit na miesto kde som editoval begin */
		if($action_scroll!=''){
			$this->pObj->doc->postCode.='
			<script language="javascript" type="text/javascript">
				document.getElementById(\''.$action_scroll.'\').scrollIntoView();
			</script>';
		}

		/* actions end */

		$tree = t3lib_div::makeInstance('t3lib_pageTree');
		$tree->init('AND '.$this->pObj->perms_clause);

		/* this site */
		$HTML=t3lib_iconWorks::getIconImage('pages',$this->pObj->pageinfo,$BACK_PATH,'');
		/* table header */
		$return_page_tree='
			<tr>
				<td class="bgColor2"><strong>'.$LANG->getLL('getPages_table_row1_header',1).'</strong>&nbsp;</td>
				<td class="bgColor2 nopadding"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/line.gif','width="5" height="16"').' alt="" /></td>
				<td class="bgColor2" colspan="4"><strong>'.$LANG->getLL('getPages_table_row2_header',1).'</strong>&nbsp;</td>
			</tr>';

		/* it this page is selected begin */
		if($this->pObj->MOD_SETTINGS['depth']==0){
			$return_page_tree.=$this->getPagesOnePage(array('1'=>$HTML,'2'=>'&nbsp;'),$this->pObj->pageinfo['title'],$this->pObj->pageinfo['uid']);
			$return_page_tree='<table border="0" cellspacing="0" cellpadding="0">'.$return_page_tree.'</table>';
			$return_content.=$this->pObj->doc->section('',$return_page_tree);
			return $return_content;
		}
		/* it this page is selected end */

		$tree->tree[]=Array('row'=>$this->pObj->pageinfo,'HTML'=>$HTML);

		// Create the tree from $this->id:
		$tree->getTree($this->pObj->pageuid,$this->pObj->MOD_SETTINGS['depth'],'');

		$bgCol=' class="bgColor-20"';
		$data_vor=array();
		$make=0;
		foreach($tree->tree as $data)	{
			if($make!=0){
				$bgCol=$bgCol==''?' class="bgColor-20"':'';
				$return_page_tree.=$this->getPagesOnePage($this->getPagesTreeImages($data_vor,$data),$data_vor['row']['title'],$data_vor['row']['uid'],$bgCol);
			};
			$make=1;
			$data_vor=$data;
		}
		$bgCol=$bgCol==''?' class="bgColor-20"':'';
		$return_page_tree.=$this->getPagesOnePage(array('1'=>$data_vor['HTML'],'2'=>'&nbsp;'),$data_vor['row']['title'],$data_vor['row']['uid'],$bgCol);

			// Wrap rows in table tags:
		$return_page_tree='<table border="0" cellspacing="0" cellpadding="0">'.$return_page_tree.'</table>';

			// Adding the content as a section:
		$return_content.=$this->pObj->doc->section('',$return_page_tree);
		$return_content.='<input type="hidden" name="showcacheid" value="'.trim($this->shownCacheId,',').'" /><input type="hidden" value="" id="act2" name="act2" />';

		return $this->pObj->doc->section($LANG->getLL("getPages_titel"),$return_content,0,1);
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/realurlmanagement/mod1/tx_realurlmanagement_pages.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/realurlmanagement/mod1/tx_realurlmanagement_pages.php']);
}
?>