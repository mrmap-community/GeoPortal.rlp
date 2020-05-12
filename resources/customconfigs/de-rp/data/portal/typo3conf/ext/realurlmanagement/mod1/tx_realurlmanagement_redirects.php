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
 *   48: class tx_realurlmanagement_redirects extends t3lib_SCbase
 *   55:     function showModule()
 *  576:     function return_URL($url)
 *  586:     function return_Destination($url)
 *  596:     function return_LastReferer($url)
 *
 *              SECTION: clear an url that it has become from selectbox
 *  621:     function clearUrlFromInput($url)
 *  644:     function checkUrlHash($urlHash)
 *
 * TOTAL FUNCTIONS: 6
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
class tx_realurlmanagement_redirects extends t3lib_SCbase {
	var $helpfunc;
	/**
	 * return the dbClean site (mod-option - dBClean)
	 *
	 * @return	string		HTML - dbClean site
	 */
	function showModule(){
		global $LANG,$BACK_PATH;
		if(!$this->pObj->perms_redirects_show){return '';};
		/* action begin */
		$action=t3lib_div::_GP('act');
		$action_urlhash=t3lib_div::_GP('act_urlhash');
		$action_edittype=t3lib_div::_GP('act_type');
		/* */
		$action_movePerm=@intval(t3lib_div::_GP('act_movePerm'));
		/* */
		if(($action=='edit')||($action=='create')){
			/*$action_NewSelectDestination=$this->clearUrlFromSelect(t3lib_div::_GP('act_destinationNewSelect'));
			$action_NewInputDestination=$this->clearUrlFromInput(t3lib_div::_GP('act_destinationNewInput'));
			$useDestination=$action_NewSelectDestination!=''?$action_NewSelectDestination:$action_NewInputDestination;*/
			/* */
			/*$action_NewSelectUrl=$this->clearUrlFromSelect(t3lib_div::_GP('act_urlNewSelect'));
			$action_NewInputUrl=$this->clearUrlFromInput(t3lib_div::_GP('act_urlNewInput'));
			$useUrl=$action_NewSelectUrl!=''?$action_NewSelectUrl:$action_NewInputUrl;*/
			$useDestination=$this->clearUrlFromInput(t3lib_div::_GP('act_destinationNewInput'));
			$useUrl=$this->clearUrlFromInput(t3lib_div::_GP('act_urlNewInput'));
		};
		/* */
		$action_scroll='';
		$action_save=false;
		$showEditInput=true;
		$actionError='';
		if((intval(t3lib_div::_GP('editsave_x'))!=0)&&(intval(t3lib_div::_GP('editsave_y'))!=0)){
			$action_save=true;
			$showEditInput=false;
		};
		if((intval(t3lib_div::_GP('closedok_x'))!=0)&&(intval(t3lib_div::_GP('closedok_y'))!=0)){
			$showEditInput=false;
		};
		/* edit begin */
		if(($action=='edit')&&($this->pObj->perms_redirects_edit)&&(intval($action_urlhash)!=0)){
			/* moveperm begin */
			if(($action_edittype=='movedperm')&&($action_save)){
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_realurl_redirects','url_hash='.$action_urlhash,array('has_moved'=>intval($action_movePerm)));
			};
			/* moveperm end */

			$action_oldUrl=t3lib_div::_GP('act_urlOld');
			$action_oldDestination=t3lib_div::_GP('act_destinationOld');
			/* urledit begin */
			if(($action_edittype=='url')&&($action_save)){
				if($useUrl==''){
					$actionError=$LANG->getLL('redirect_editUrlEmpty',1);
					$showEditInput=true;
					$action_save=false;
				}else if($useUrl==$action_oldDestination){
					$actionError=$LANG->getLL('redirect_urlAndDestinationAreEqual',1);
					$showEditInput=true;
					$action_save=false;
				}else if($useUrl==$action_oldUrl){
					//without changes i don't need to change the database data
				}else{
					$useUrlHash=intval(t3lib_div::md5int($useUrl));
					$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_realurl_redirects','url_hash='.$action_urlhash,array('url'=>$useUrl,'url_hash'=>$useUrlHash));
					$action_urlhash=$useUrlHash;
				};
			};
			/* urledit end */
			/* destination begin */
			if(($action_edittype=='destination')&&($action_save)){
				if($useDestination==''){
					$actionError=$LANG->getLL('redirect_editDestinationEmpty',1);
					$showEditInput=true;
					$action_save=false;
				}else if($useDestination==$action_oldUrl){
					$actionError=$LANG->getLL('redirect_urlAndDestinationAreEqual',1);
					$showEditInput=true;
					$action_save=false;
				}else if($useDestination==$action_oldDestination){
					//without changes i don't need to change the database data
				}else{
					$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_realurl_redirects','url_hash='.$action_urlhash,array('destination'=>$useDestination));
				};
			};
			/* destination end */
			$action_scroll='id_'.$action_urlhash;
		};
		/* edit end */
		/* clear counter begin */
		if(($action=='clearCount')&&($this->pObj->perms_redirects_clearCounter)&&(intval($action_urlhash)!=0)){
			$action_scroll='id_'.$action_urlhash;
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_realurl_redirects','url_hash='.$action_urlhash,array('counter'=>0));
		};
		/* clear counter end */
		/* delete begin */
		if(($action=='delete')&&($this->pObj->perms_redirects_delete)){
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_realurl_redirects','url_hash='.$action_urlhash);
		}
		/* delete end */
		/* create begin */
		if(($action=='create')&&($action_save)){
			if($useUrl==''){
				$actionError=$LANG->getLL('redirect_editUrlEmpty',1);
				$showEditInput=true;
				$action_save=false;
			}else if($useDestination==''){
				$actionError=$LANG->getLL('redirect_editDestinationEmpty',1);
				$showEditInput=true;
				$action_save=false;
			}else if($useDestination==$useUrl){
				$actionError=$LANG->getLL('redirect_urlAndDestinationAreEqual',1);
				$showEditInput=true;
				$action_save=false;
			}else{
				$useUrlHash=intval(t3lib_div::md5int($useUrl));
				if($this->checkUrlHash($useUrlHash)){
					$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_realurl_redirects',array('url'=>$useUrl,'destination'=>$useDestination,'has_moved'=>intval($action_movePerm),'url_hash'=>$useUrlHash));
					$showEditInput=false;
				}else{
					$actionError=$LANG->getLL('redirect_redirectFromThisUrlExist',1);
					$showEditInput=true;
					$action_save=false;
				}
			}
		}

		/* create end */


		if($action_scroll!=''){
			$this->pObj->doc->postCode.='
			<script language="javascript" type="text/javascript">
				document.getElementById(\''.$action_scroll.'\').scrollIntoView();
			</script>';
		}
		/* action end */
		$createRow='';
		$deleteRow='';
		$emptyDeleteRow='';
		$emptyRow='<td class="bgColor5" nowrap="nowrap">&nbsp;</td>';
		if($this->pObj->perms_redirects_create||$this->pObj->perms_redirects_delete){
			$createRow='<td class="bgColor2">&nbsp;</td>';
			$deleteRow=$emptyRow;
			$emptyDeleteRow=$deleteRow;
		};

		if($this->pObj->perms_redirects_create){
			$aHrefCreate = 'index.php?mode='.$this->pObjMOD_SETTINGS['mode'].'&depth='.$this->pObj->MOD_SETTINGS['depth'].'&id='.$this->pObj->pageuid.'&act=create&tstamp='.time();
			$createRow='<td class="bgColor2" nowrap="nowrap">&nbsp;<a href="'.htmlspecialchars($aHrefCreate).'" onclick="'.htmlspecialchars('return confirm('.$LANG->JScharCode($LANG->getLL('redirect_createWarning')).');').'"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/new_el.gif','width="11" height="12"').' border="0" title="'.$LANG->getLL('redirect_create',1).'" alt="" /></a>&nbsp;</td>';
		}
		$return_table='
			<tr>
				'.$createRow.'
				<td class="bgColor2" colspan="2"><strong>'.$LANG->getLL('redirect_tableheader1',1).'</strong>&nbsp;</td>
				<td class="bgColor2" colspan="2"><strong>'.$LANG->getLL('redirect_tableheader2',1).'</strong>&nbsp;</td>
			</tr>';
		/* create begin */
		if(($this->pObj->perms_redirects_create)&&($action=='create')&&$showEditInput){
			/*$selectUrl_option='<option value=""></option>';
			$selectDestination_option='<option value=""></option>';
			$selectUrl_find=false;
			$selectDestination_find=false;
			$res_select=$GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'pagepath',
				'tx_realurl_pathcache',
				'1=1',
				'',
				'pagepath');
			if($GLOBALS['TYPO3_DB']->sql_num_rows($res_select)>0){
				while($row_select = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_select)){
					$selectUrl_option.='<option value="'.$row_select['pagepath'].'"';
					if(trim($row_select['pagepath'],'/')==trim($useUrl,'/')){
						$selectUrl_option.=' selected="selected"';
						$selectUrl_find=true;
					};
					$selectUrl_option.='>'.$row_select['pagepath'].'</option>';

					$selectDestination_option.='<option value="'.$row_select['pagepath'].'"';
					if(trim($row_select['pagepath'],'/')==trim($useDestination,'/')){
						$selectDestination_option.=' selected="selected"';
						$selectDestination_find=true;
					};
					$selectDestination_option.='>'.$row_select['pagepath'].'</option>';
				};
			};

			$createControls_Url='
						<input type="text" size="50" name="act_urlNewInput" value="'.(!$selectUrl_find?$useUrl:'').'" />
						<br/>
						<select name="act_urlNewSelect">
							'.$selectUrl_option.'
						</select>';

			$createControls_Destination='
						<input type="text" size="50" name="act_destinationNewInput" value="'.(!$selectDestination_find?$useDestination:'').'" />
						<br/>
						<select name="act_destinationNewSelect">
							'.$selectDestination_option.'
						</select>';*/
			$createControls_Url='<input type="text" size="50" name="act_urlNewInput" value="'.$useUrl.'" />';
			$createControls_Destination='<input type="text" size="50" name="act_destinationNewInput" value="'.$useDestination.'" />';

			$createControls_movedPerm='
				<select name="act_movePerm">
					<option value="0" '.(intval($action_movePerm)==0?'selected="selected"':'').'>'.$LANG->getLL("redirect_MovedPermanentlyNo").'</option>
					<option value="1" '.(intval($action_movePerm)==1?'selected="selected"':'').'>'.$LANG->getLL("redirect_MovedPermanentlyYes").'</option>
				</select>';

			$createControls_buttons='<input type="image" '.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/saveandclosedok.gif','').' width="21" height="16" name="editsave" id="editsave" title="'.$LANG->getLL('edit_saveandclose').'"/>';
			$createControls_buttons.='&nbsp;';
			$createControls_buttons.='<input type="image" '.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/closedok.gif','').' width="21" height="16" name="closedok" id="closedok" title="'.$LANG->getLL('edit_close').'"/>';
			$createControls_buttons.='<input type="hidden" name="act" value="create" />';

			if($actionError!=''){
				$return_table.='
				<tr>
					'.$emptyDeleteRow.'
					'.$emptyRow.'
					'.$emptyRow.'
					<td class="bgColor-20" nowrap="nowrap" colspan="2" style="color:red;"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/icon_fatalerror.gif','width="18" height="16"').' border="0" title="'.$actionError.'" alt="" /><strong>'.$actionError.'</strong>&nbsp;</td>
				</tr>';
			};

			$return_table.='
				<tr>
					'.$emptyDeleteRow.'
					'.$emptyRow.'
					'.$emptyRow.'
					<td class="bgColor-20" nowrap="nowrap"><strong>'.$LANG->getLL("redirect_URL").':</strong>&nbsp;</td>
					<td class="bgColor-20" nowrap="nowrap">'.$createControls_Url.'</td>
				</tr>
				<tr>
					'.$emptyDeleteRow.'
					'.$emptyRow.'
					'.$emptyRow.'
					<td class="bgColor-20" nowrap="nowrap" colspan="2">&nbsp;</td>
				</tr>
				<tr>
					'.$emptyDeleteRow.'
					'.$emptyRow.'
					'.$emptyRow.'
					<td class="bgColor-20" nowrap="nowrap"><strong>'.$LANG->getLL("redirect_Destination").':</strong>&nbsp;</td>
					<td class="bgColor-20" nowrap="nowrap">'.$createControls_Destination.'</td>
				</tr>
				<tr>
					'.$emptyDeleteRow.'
					'.$emptyRow.'
					'.$emptyRow.'
					<td class="bgColor-20" nowrap="nowrap" colspan="2">&nbsp;</td>
				</tr>
				<tr>
					'.$emptyDeleteRow.'
					'.$emptyRow.'
					'.$emptyRow.'
					<td class="bgColor-20" nowrap="nowrap"><strong>'.$LANG->getLL("redirect_MovedPermanently").':</strong>&nbsp;</td>
					<td class="bgColor-20" nowrap="nowrap">'.$createControls_movedPerm.'</td>
				</tr>
				<tr>
					'.$emptyDeleteRow.'
					'.$emptyRow.'
					'.$emptyRow.'
					<td class="bgColor-20" nowrap="nowrap" colspan="2">&nbsp;</td>
				</tr>
				<tr>
					'.$emptyDeleteRow.'
					'.$emptyRow.'
					'.$emptyRow.'
					<td class="bgColor-20" nowrap="nowrap">&nbsp;</td>
					<td class="bgColor-20" nowrap="nowrap">'.$createControls_buttons.'</td>
				</tr>
				<tr>
					'.$emptyDeleteRow.'
					'.$emptyRow.'
					'.$emptyRow.'
					<td class="bgColor-20" nowrap="nowrap" colspan="2">&nbsp;</td>
				</tr>';
		};
		/* create end */

		$canBeOrdered=',url,destination,last_referer,counter,tstamp,has_moved,';
		$orderBy=t3lib_div::_GP('ordBy');
		$orderAscDesc=t3lib_div::_GP('ordAscDesc');
		if($orderAscDesc!='asc'){$orderAscDesc='desc';}
		if(strpos($canBeOrdered,$orderBy)===false){$orderBy='url';}
		/* pagebrowser begin */
		$res_pageBrowser=$GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'count(url) AS countuid',
			'tx_realurl_redirects',
			''
		);
		$row_pageBrowser = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_pageBrowser);
		$this->pageBrowser['count']=$row_pageBrowser['countuid'];
		$this->pageBrowser['showElements']=$this->pObj->pagebrowser_redirect['showElements'];
		$this->pageBrowser['showPages']=$this->pObj->pagebrowser_redirect['showPages'];
		$this->pageBrowser['pointer']=intval(t3lib_div::_GP('pb_pointer'));
		$this->pageBrowser['URL']='&pb_pointer='.$this->pageBrowser['pointer'];
		$this->pageBrowser['FORM_HIDDEN']='<input type="hidden" name="pb_pointer" value="'.$this->pageBrowser['pointer'].'" />';
		$this->pageBrowser['oldURL']='index.php?mode='.$this->pObj->MOD_SETTINGS['mode'].'&depth='.$this->pObj->MOD_SETTINGS['depth'].'&id='.$this->pObj->pageuid.'&tstamp='.time().'&ordBy='.$orderBy.'&ordAscDesc='.$orderAscDesc;
		$pageBrowser=($this->pageBrowser['count'] > $this->pageBrowser['showElements'])?$this->helpfunc->getPageBrowser($this->pageBrowser):'';
		/* pagebrowser end */
		$orderByRows=$this->helpfunc->getOrderBy('url,destination,last_referer,counter,tstamp,has_moved',$orderBy,$orderAscDesc,$this->pageBrowser['URL']);

		$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'url,destination,last_referer,counter,tstamp,has_moved,url_hash',
			'tx_realurl_redirects',
			'1=1',
			'',
			$orderBy.' '.$orderAscDesc,
			($this->pageBrowser['pointer']*$this->pageBrowser['showElements']).','.$this->pageBrowser['showElements']);

		$bgCol='';
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
			$tr_id=' id="id_'.$row['url_hash'].'"';
			$editUrlRow=$emptyRow;
			$editDestinationRow=$emptyRow;
			$editMovePermRow=$emptyRow;
			$clearCounterRow=$emptyRow;
			$editMovePermRow_edit=intval($row['has_moved'])==0?$LANG->getLL("redirect_MovedPermanentlyNo"):$LANG->getLL("redirect_MovedPermanentlyYes");
			$editUrlRow_edit=$this->return_URL($row['url']);
			$editDestinationRow_edit=$this->return_Destination($row['destination']);
			if($this->pObj->perms_redirects_edit){
				/* url begin */
				$aHrefEditUrl = 'index.php?mode='.$this->pObj->MOD_SETTINGS['mode'].'&depth='.$this->pObj->MOD_SETTINGS['depth'].'&id='.$this->pObj->pageuid.'&act=edit&act_type=url&act_urlhash='.$row['url_hash'].'&ordBy='.$orderBy.'&ordAscDesc='.$orderAscDesc.'&tstamp='.time().$this->pageBrowser['URL'];
				$editUrlRow='<td class="bgColor5" nowrap="nowrap">&nbsp;<a href="'.htmlspecialchars($aHrefEditUrl).'" onclick="'.htmlspecialchars('return confirm('.$LANG->JScharCode($LANG->getLL('alias_editWarning')).');').'"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/edit2.gif','width="12" height="12"').' border="0" title="'.$LANG->getLL('edit',1).'" alt="" /></a>&nbsp;</td>';
				if(($action=='edit')&&($action_edittype=='url')&&($row['url_hash']==$action_urlhash)&&($showEditInput)){
					/*$select_option='<option value=""></option>';
					$select_find=false;
					$res_select=$GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'pagepath',
						'tx_realurl_pathcache',
						'1=1',
						'',
						'pagepath');
					if($GLOBALS['TYPO3_DB']->sql_num_rows($res_select)>0){
						while($row_select = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_select)){
							$select_option.='<option value="'.$row_select['pagepath'].'"';
							if(trim($row_select['pagepath'],'/')==trim($row['url'],'/')){
								$select_option.=' selected="selected"';
								$select_find=true;
							};
							$select_option.='>'.$row_select['pagepath'].'</option>';
						};
					};

					$editUrlRow=$emptyRow;
					$editUrlRow_edit='
						<input type="text" size="50" name="act_urlNewInput" value="'.(!$select_find?$row['url']:'').'" />
						<br/>
						<select name="act_urlNewSelect">
							'.$select_option.'
						</select>
						<br/>';*/

					$editUrlRow=$emptyRow;
					$editUrlRow_edit='<input type="text" size="50" name="act_urlNewInput" value="'.$row['url'].'" />';


					$editUrlRow_edit.='<input type="image" '.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/saveandclosedok.gif','').' width="21" height="16" name="editsave" id="editsave" title="'.$LANG->getLL('edit_saveandclose').'"/>';
					$editUrlRow_edit.='&nbsp;';
					$editUrlRow_edit.='<input type="image" '.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/closedok.gif','').' width="21" height="16" name="closedok" id="closedok" title="'.$LANG->getLL('edit_close').'"/>';
					$editUrlRow_edit.='<input type="hidden" name="act_urlhash" value="'.$row['url_hash'].'" />';
					$editUrlRow_edit.='<input type="hidden" name="act_urlOld" value="'.$row['url'].'"/>';
					$editUrlRow_edit.='<input type="hidden" name="act_destinationOld" value="'.$row['destination'].'"/>';
					$editUrlRow_edit.='<input type="hidden" name="act" value="edit" />';
					$editUrlRow_edit.='<input type="hidden" name="act_type" value="url" />';
					$editUrlRow_edit.=$this->pageBrowser['FORM_HIDDEN'];
				};
				/* url end */

				/* url begin */
				$aHrefEditDestination = 'index.php?mode='.$this->pObj->MOD_SETTINGS['mode'].'&depth='.$this->pObj->MOD_SETTINGS['depth'].'&id='.$this->pObj->pageuid.'&act=edit&act_type=destination&act_urlhash='.$row['url_hash'].'&ordBy='.$orderBy.'&ordAscDesc='.$orderAscDesc.'&tstamp='.time().$this->pageBrowser['URL'];
				$editDestinationRow='<td class="bgColor5" nowrap="nowrap">&nbsp;<a href="'.htmlspecialchars($aHrefEditDestination).'" onclick="'.htmlspecialchars('return confirm('.$LANG->JScharCode($LANG->getLL('alias_editWarning')).');').'"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/edit2.gif','width="12" height="12"').' border="0" title="'.$LANG->getLL('edit',1).'" alt="" /></a>&nbsp;</td>';
				if(($action=='edit')&&($action_edittype=='destination')&&($row['url_hash']==$action_urlhash)&&($showEditInput)){
					/*$select_option='<option value=""></option>';
					$select_find=false;
					$res_select=$GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'pagepath',
						'tx_realurl_pathcache',
						'1=1',
						'',
						'pagepath');
					if($GLOBALS['TYPO3_DB']->sql_num_rows($res_select)>0){
						while($row_select = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_select)){
							$select_option.='<option value="'.$row_select['pagepath'].'"';
							if(trim($row_select['pagepath'],'/')==trim($row['destination'],'/')){
								$select_option.=' selected="selected"';
								$select_find=true;
							};
							$select_option.='>'.$row_select['pagepath'].'</option>';
						};
					};

					$editDestinationRow=$emptyRow;
					$editDestinationRow_edit='
						<input type="text" size="50" name="act_destinationNewInput" value="'.(!$select_find?$row['destination']:'').'" />
						<br/>
						<select name="act_destinationNewSelect">
							'.$select_option.'
						</select>
						<br/>';*/
					$editDestinationRow=$emptyRow;
					$editDestinationRow_edit='<input type="text" size="50" name="act_destinationNewInput" value="'.$row['destination'].'" />';

					$editDestinationRow_edit.='<input type="image" '.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/saveandclosedok.gif','').' width="21" height="16" name="editsave" id="editsave" title="'.$LANG->getLL('edit_saveandclose').'"/>';
					$editDestinationRow_edit.='&nbsp;';
					$editDestinationRow_edit.='<input type="image" '.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/closedok.gif','').' width="21" height="16" name="closedok" id="closedok" title="'.$LANG->getLL('edit_close').'"/>';
					$editDestinationRow_edit.='<input type="hidden" name="act_urlhash" value="'.$row['url_hash'].'" />';
					$editDestinationRow_edit.='<input type="hidden" name="act_destinationOld" value="'.$row['destination'].'"/>';
					$editDestinationRow_edit.='<input type="hidden" name="act_urlOld" value="'.$row['url'].'"/>';
					$editDestinationRow_edit.='<input type="hidden" name="act" value="edit" />';
					$editDestinationRow_edit.='<input type="hidden" name="act_type" value="destination" />';
					$editDestinationRow_edit.=$this->pageBrowser['FORM_HIDDEN'];
				};
				/* url end */


				/* MovedPerm begin */
				$aHrefEditMovePerm = 'index.php?mode='.$this->pObj->MOD_SETTINGS['mode'].'&depth='.$this->pObj->MOD_SETTINGS['depth'].'&id='.$this->pObj->pageuid.'&act=edit&act_type=movedperm&act_urlhash='.$row['url_hash'].'&ordBy='.$orderBy.'&ordAscDesc='.$orderAscDesc.'&tstampel='.$row['tstamp'].'&tstamp='.time().$this->pageBrowser['URL'];
				$editMovePermRow='<td class="bgColor5" nowrap="nowrap">&nbsp;<a href="'.htmlspecialchars($aHrefEditMovePerm).'" onclick="'.htmlspecialchars('return confirm('.$LANG->JScharCode($LANG->getLL('redirect_movedPermanentEditWarning')).');').'"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/edit2.gif','width="12" height="12"').' border="0" title="'.$LANG->getLL('edit',1).'" alt="" /></a>&nbsp;</td>';
				if(($action=='edit')&&($action_edittype=='movedperm')&&($row['url_hash']==$action_urlhash)&&($showEditInput)){
					$editMovePermRow=$emptyRow;
					$editMovePermRow_edit='
						<select name="act_movePerm">
							<option value="0" '.(intval($row['has_moved'])==0?'selected="selected"':'').'>'.$LANG->getLL("redirect_MovedPermanentlyNo").'</option>
							<option value="1" '.(intval($row['has_moved'])==1?'selected="selected"':'').'>'.$LANG->getLL("redirect_MovedPermanentlyYes").'</option>
						</select>';
					$editMovePermRow_edit.='&nbsp';
					$editMovePermRow_edit.='<input type="image" '.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/saveandclosedok.gif','').' width="21" height="16" name="editsave" id="editsave" title="'.$LANG->getLL('edit_saveandclose').'"/>';
					$editMovePermRow_edit.='&nbsp;';
					$editMovePermRow_edit.='<input type="image" '.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/closedok.gif','').' width="21" height="16" name="closedok" id="closedok" title="'.$LANG->getLL('edit_close').'"/>';
					$editMovePermRow_edit.='<input type="hidden" name="act_urlhash" value="'.$row['url_hash'].'" />';
					$editMovePermRow_edit.='<input type="hidden" name="act" value="edit" />';
					$editMovePermRow_edit.='<input type="hidden" name="act_type" value="movedperm" />';
					$editMovePermRow_edit.=$this->pageBrowser['FORM_HIDDEN'];
				};
				/* MovedPerm end */
			};
			/* clear counter value */
			if(($this->pObj->perms_redirects_clearCounter)&&(intval($row['counter'])!=0)){
				$aHrefClearCounter = 'index.php?mode='.$this->pObj->MOD_SETTINGS['mode'].'&depth='.$this->pObj->MOD_SETTINGS['depth'].'&id='.$this->pObj->pageuid.'&act=clearCount&act_urlhash='.$row['url_hash'].'&ordBy='.$orderBy.'&ordAscDesc='.$orderAscDesc.'&tstamp='.time().$this->pageBrowser['URL'];
				$clearCounterRow='<td class="bgColor5" nowrap="nowrap">&nbsp;<a href="'.htmlspecialchars($aHrefClearCounter).'" onclick="'.htmlspecialchars('return confirm('.$LANG->JScharCode($LANG->getLL('redirect_clearCountWarning')).');').'"><img'.t3lib_iconWorks::skinImg('../','gfx/clear_counts.gif','width="12" height="14"').' border="0" title="'.$LANG->getLL('redirect_clearCount',1).'" alt="" /></a>&nbsp;</td>';
			};
			if($this->pObj->perms_redirects_delete){
				$aHrefDelete = 'index.php?mode='.$this->pObj->MOD_SETTINGS['mode'].'&depth='.$this->pObj->MOD_SETTINGS['depth'].'&id='.$this->pObj->pageuid.'&act=delete&act_urlhash='.$row['url_hash'].'&ordBy='.$orderBy.'&ordAscDesc='.$orderAscDesc.'&tstamp='.time().$this->pageBrowser['URL'];
				$deleteRow='<td class="bgColor5" nowrap="nowrap">&nbsp;<a href="'.htmlspecialchars($aHrefDelete).'" onclick="'.htmlspecialchars('return confirm('.$LANG->JScharCode($LANG->getLL('deleteWarning')).');').'"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/garbage.gif','width="12" height="12"').' border="0" title="'.$LANG->getLL('deleteItem',1).'" alt="" /></a>&nbsp;</td>';
			};

			if(($actionError!='')&&($action_urlhash==$row['url_hash'])){
				$return_table.='
				<tr'.$tr_id.'>
					'.$emptyDeleteRow.'
					'.$emptyRow.'
					'.$emptyRow.'
					<td'.$bgCol.' nowrap="nowrap" colspan="2" style="color:red;"><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/icon_fatalerror.gif','width="18" height="16"').' border="0" title="'.$actionError.'" alt="" /><strong>'.$actionError.'</strong>&nbsp;</td>
				</tr>';
				$tr_id='';
			};


			/* clear counter value */
			$return_table.='
				<tr'.$tr_id.'>
					'.$deleteRow.'
					'.$editUrlRow.'
					'.$orderByRows['url'].'
					<td'.$bgCol.' nowrap="nowrap"><strong>'.$LANG->getLL("redirect_URL").':</strong>&nbsp;</td>
					<td'.$bgCol.' nowrap="nowrap">'.$editUrlRow_edit.'&nbsp;</td>
				</tr>
				<tr>
					'.$emptyDeleteRow.'
					'.$editDestinationRow.'
					'.$orderByRows['destination'].'
					<td'.$bgCol.' nowrap="nowrap"><strong>'.$LANG->getLL("redirect_Destination").':</strong>&nbsp;</td>
					<td'.$bgCol.' nowrap="nowrap">'.$editDestinationRow_edit.'&nbsp;</td>
				</tr>
				<tr>
					'.$emptyDeleteRow.'
					'.$clearCounterRow.'
					'.$orderByRows['counter'].'
					<td'.$bgCol.' nowrap="nowrap"><strong>'.$LANG->getLL("redirect_Counter").':</strong>&nbsp;</td>
					<td'.$bgCol.' nowrap="nowrap">'.$row['counter'].'&nbsp;</td>
				</tr>
				<tr>
					'.$emptyDeleteRow.'
					'.$emptyRow.'
					'.$orderByRows['last_referer'].'
					<td'.$bgCol.' nowrap="nowrap"><strong>'.$LANG->getLL("redirect_lastreferer").':</strong>&nbsp;</td>
					<td'.$bgCol.' nowrap="nowrap">'.$this->return_LastReferer($row['last_referer']).'&nbsp;</td>
				</tr>
				<tr>
					'.$emptyDeleteRow.'
					'.$emptyRow.'
					'.$orderByRows['tstamp'].'
					<td'.$bgCol.' nowrap="nowrap"><strong>'.$LANG->getLL("redirect_Tstamp").':</strong>&nbsp;</td>
					<td'.$bgCol.' nowrap="nowrap">'.$this->helpfunc->getDateTime($row['tstamp']).'&nbsp;</td>
				</tr>
				<tr>
					'.$emptyDeleteRow.'
					'.$editMovePermRow.'
					'.$orderByRows['has_moved'].'
					<td'.$bgCol.' nowrap="nowrap"><strong>'.$LANG->getLL("redirect_MovedPermanently").':</strong>&nbsp;</td>
					<td'.$bgCol.' nowrap="nowrap">'.$editMovePermRow_edit.'&nbsp;</td>
				</tr>
				<tr>
					'.$emptyDeleteRow.'
					'.$emptyRow.'
					'.$emptyRow.'
					<td colspan="2" '.$bgCol.'>&nbsp;</td>
				</tr>';


			$bgCol=$bgCol==''?' class="bgColor-20"':'';
		};



		/* delete button end */
		$return_content=$pageBrowser.'<table cellspacing="0" cellpadding="0">'.$return_table.'</table>'.$pageBrowser;
		return $this->pObj->doc->section($LANG->getLL('redirects_title',1),$return_content,0,1);
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$url: ...
	 * @return	[type]		...
	 */
	function return_URL($url){
		return '<a href="'.$GLOBALS['BACK_PATH'].$url.'">'.$url.'</a>';
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$url: ...
	 * @return	[type]		...
	 */
	function return_Destination($url){
		return $url;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$url: ...
	 * @return	[type]		...
	 */
	function return_LastReferer($url){
		return $url;
	}


	/**
	 * clear an url that it has become from selectbox
	 *
	 * @param	string		url
	 * @return	string		cleared url
	 */
	/*function clearUrlFromSelect($url){
		$url=trim($url);
		if($url!=''){
			$url.='/';
		};
		return $url;
	}*/

	/**
	 * clear an url that it has become from input
	 *
	 * @param	string		url
	 * @return	string		cleared url
	 */
	function clearUrlFromInput($url){
		$url=trim($url);
		/*if($url!=''){
			$url=str_replace('\\','/',$url);
			$urlArr=t3lib_div::trimExplode('/',$url);
			$return_url=array();
			foreach($urlArr as $urlPart){
				if($urlPart!=''){
					$urlPart=$this->helpfunc->encodePageName($urlPart);
				};
				$return_url[]=$urlPart;
			};
			$url=implode($return_url,'/');
		};*/
		return $url;
	}

	/**
	 * check if the redirect from an url already exist or not
	 *
	 * @param	int		url hash value
	 * @return	boolean		true if not exist false if exist
	 */
	function checkUrlHash($urlHash){
		$res_check=$GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'url',
			'tx_realurl_redirects',
			'url_hash='.intval($urlHash)
			);
		if($GLOBALS['TYPO3_DB']->sql_num_rows($res_check)>0) return false;
		return true;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/realurlmanagement/mod1/tx_realurlmanagement_redirects.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/realurlmanagement/mod1/tx_realurlmanagement_redirects.php']);
}
?>