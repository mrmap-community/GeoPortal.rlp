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
 * Plugin tx_realurlmanagement_setupbrowsetree.
 *
 * @author	Juraj Sulek <juraj@sulek.sk>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 */
require_once(PATH_t3lib."class.t3lib_treeview.php");


class tx_realurlmanagement_setupbrowsetree extends t3lib_treeView {
	var $helpfunc;
	var $action;
	var $actionId;
	var $actionHash;
	var $actionLivePath;
	var $actionError;
	var $pObj;
	
	function getIcon($row) {
		return $this->wrapIcon($row['icon'],$row);
	}
	
	function wrapIcon($icon,$row)	{
		$iconWrapped='<a href="#" onclick="setLayerObj(document.getElementById(\'contentMenuTable_'.$row['id'].'\').innerHTML,0)">'.$icon.'</a>';
		
		return $iconWrapped;
	}
	
	function wrapTitle($title,$row,$bank=0)	{
		if($this->action=='edit' && $this->actionId==$row['id'] && md5($row[id].'*'.$row['title'].'*'.$row['icon'].'*'.serialize($row['TCA']['livePath']).$GLOBALS['TYPO3_CONF_VARS']['BE']['installToolPassword'])==$row['TCA']['hash']){
			return $this->setEditArea($row);
		}else{
			if($row['TCA']['help']){
				return '<a href="tx_realurlmanagement_setup_help.php?help='.$row['TCA']['help'].'" target="_blank">'.$title.'</a>';
			}else{
				return $title;
			}
			return '<a href="#" class="helptag">'.$title.'<span>'.$row['TCA']['help'].'</span></a>';
			
			return parent::wrapTitle($title,$row,$bank);
		};
	}
	
	/* sachen zum edit begin */
	
	
	function setSaveCloseButton($row){
		$retContent='&nbsp;';
		$retContent.='<input type="image" '.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/saveandclosedok.gif','').' width="21" height="16" name="editsave" id="editsave" title="'.$GLOBALS['LANG']->getLL('edit_saveandclose').'" />';
		$retContent.='&nbsp;';
		$retContent.='<input type="image" '.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/closedok.gif','').' width="21" height="16" name="closedok" id="closedok" title="'.$GLOBALS['LANG']->getLL('edit_close').'" />';
		$retContent.='<input type="hidden" name="act" value="'.$this->action.'" />';
		$retContent.='<input type="hidden" name="actid" value="'.$this->actionId.'" />';
		$retContent.='<input type="hidden" name="acthash" value="'.$this->actionHash.'" />';
		if($this->actionError!=''){
			$retContent.="<div style=\"color:red; font-weight:bold;\">".$this->actionError."</div>";
		};
		
		return $retContent;
	}
	
	function setEditArea($row){
		switch($row['TCA']['type']){
			case('text'):{
				return $this->setEditAreaText($row);
				break;
			};
			case('data'):{
				return $this->setEditAreaData($row);
				break;
			};
			case('select'):{
				return $this->setEditAreaSelect($row);
				break;
			};
			case('boolean'):{
				return $this->setEditAreaBolean($row);
				break;
			}
		};
		return 'edit';
	}
	
	function setEditAreaSelect($row){
		if($row['TCA']['values']!=''){
			$selectOptions=t3lib_div::trimExplode(",",$row['TCA']['values']);
			$retContent='<select name="actValue" id="actValue">';
			foreach($selectOptions as $opt){
				$retContent.='<option value="'.$opt.'"'.($opt==$row['title']?' selected="selected"':'').'>'.$opt.'</option>';
			};
			$retContent.='</select>';
			$retContent.=$this->setSaveCloseButton($row);
		}else{
			$retContent='error';
		};
		return $retContent;
	}
	
	function setEditAreaText($row){
		return '<input type="text" size="'.$row['TCA']['size'].'" name="actValue" id="actValue" value="'.$row['title'].'" />'.$this->setSaveCloseButton($row);
	}
	
	
	function setEditAreaData($row){
		$tempArr=$this->data;
		
		/*
		treba este dorobit nie je to otestovane
		if($row['TCA']['path']!=''){
			$pathArr=t3lib_div::trimExplode(';',$row['TCA']['path']);
			$i=0;
			while($i<count($pathArr)){
				$tempArr=$tempArr[$pathArr[$i]];
				$i++;
			};
		}*/
		
		$retContent='<select name="actValue" id="actValue">';
		if($row['TCA']['dataPlus']!=''){
			$retContent.=$row['TCA']['dataPlus'];
		}
		foreach($tempArr as $key=>$val){
			if($row['TCA']['pid']!=$key){
				if($val['title']==$row['title']){
					$retContent.='<option value="'.$val['title'].'" selected="selected">'.$val['title'].'</option>';
				}else{
					$retContent.='<option value="'.$val['title'].'">'.$val['title'].'</option>';
				}
			};
		}
		$retContent.='</select>';
		$retContent.=$this->setSaveCloseButton($row);
		return $retContent;
	}
	
	function setEditAreaBolean($row){
		$retContent='<input type="checkbox" name="actValue" id="actValue" value="1"'.($row['title']==1?' checked="checked"':'').' />';
		$retContent.=$this->setSaveCloseButton($row);
		return $retContent;
	}
	
	/* sachen zum edit end */
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/realurlmanagement/mod1/tx_realurlmanagement_setupbrowsetree.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/realurlmanagement/mod1/tx_realurlmanagement_setupbrowsetree.php']);
}

?>