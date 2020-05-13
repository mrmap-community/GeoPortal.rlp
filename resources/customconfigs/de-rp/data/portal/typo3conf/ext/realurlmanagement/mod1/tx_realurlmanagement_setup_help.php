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
 * Plugin tx_realurlmanagement_setup_help.
 *
 * @author	Juraj Sulek <juraj@sulek.sk>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 */
require ("conf.php");
require ($BACK_PATH."init.php");
require ($BACK_PATH."template.php");
$LANG->includeLLFile("EXT:realurlmanagement/mod1/locallang_setup.php");

class tx_realurlmanagement_setup_help extends t3lib_div {
	var $help;
	var $helpArray;
	
	function init(){
		$this->help=t3lib_div::_GP('help');
		$tempArray=t3lib_div::trimExplode("--",$this->help);
		foreach($tempArray as $key=>$val){
			if($key>0){
				$this->helpArray[$key]=$this->helpArray[$key-1].'--'.$val;
			}else{
				$this->helpArray[$key]=$val;
			};
		}
		
	}
	
	
	
	
	function showModule(){
		global $LANG;
		$content='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="Expires" content="0" />
<meta http-equiv="cache-control" content="no-cache, must-revalidate" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>'.str_replace('--','->',$this->help).'</title>
<style type="text/css">
body{margin:0px; padding:0px;}
</style>
</head>
<body>';
		
		$topmenu='';
	
		/*	*/
		if(count($this->helpArray)>0){
			foreach($this->helpArray as $key=>$val){
				$topmenu.='<li><a href="tx_realurlmanagement_setup_help.php?help='.$val.'">'.str_replace('--','->',$val).'</a></li>';
			};
			$content.='
			<ul class="topmenu">
				'.$topmenu.'
			<ul>';
		};
		
		
		
		$getContent=$LANG->getLL($this->help,0);
		$getContent=str_replace('</LINK>','</a>',$getContent);
		$getContent=str_replace('<LINK "','<a href="tx_realurlmanagement_setup_help.php?help=',$getContent);
		
		$content.=$getContent;
		
		
		$content.='</body></html>';
		echo $content;
	}
	
	
	
	
	
	
	
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/realurlmanagement/mod1/tx_realurlmanagement_setup_help.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/realurlmanagement/mod1/tx_realurlmanagement_setup_help.php']);
}

// Make instance:
$SOBE = t3lib_div::makeInstance('tx_realurlmanagement_setup_help');
$SOBE->init();

// Include files?
//foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->showModule();

?>