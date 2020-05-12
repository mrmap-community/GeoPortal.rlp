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
 * Plugin tx_realurlmanagement_setup.
 *
 * @author	Juraj Sulek <juraj@sulek.sk>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 */
require ('init.php');
require ('template.php');
$LANG->includeLLFile("EXT:realurlmanagement/mod1/locallang_setup.php");

class tx_realurlmanagement_setup_clickmenu extends t3lib_div {
	
	function init(){
		global $LANG;
		
	}
	
	
	
	
	function showModule(){
		global $LANG,$BEUSER;
		// problem s moc dlhymi linkami prerobit to cez javascript alebo tak nejako
		// teda dat na stranku skryte elemenenty a potom len tieto naplnat obsahom
		$this->init();
		
		$this->myArray=array(
			'_DEFAULT'=>array(
				'init'=>array(),
				'redirects'=>array(),
				'preVars'=>array()
			),
			'www.test.sk'=>array(),
			'www.sme.sk'=>'_DEFAULT',
			'www.aaa.sk'=>'test.sk',
		);

	}
	
	
	
	
	
	
	
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/realurlmanagement/mod1/tx_realurlmanagement_setup_clickmenu.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/realurlmanagement/mod1/tx_realurlmanagement_setup_clickmenu.php']);
}

// Make instance:
$SOBE = t3lib_div::makeInstance('tx_realurlmanagement_setup_clickmenu');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>