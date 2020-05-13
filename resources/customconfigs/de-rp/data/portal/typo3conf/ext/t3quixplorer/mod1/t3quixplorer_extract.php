<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2004 Mads Brunn (brunn@mail.dk)
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
 * class 'quixplorer_extract' for the 't3quixplorer' extension.
 * Class for copy-move functionality
 *
 * @author	Mads Brunn <brunn@mail.dk>
 */
/***************************************************************/

require_once ("t3quixplorer_div.php");


class t3quixplorer_extract{
	var $overwrite = 0;

	function main($dir,$item){
		//debug($_REQUEST);


		$this->overwrite = t3lib_div::_GP("overwrite");

		//debug($this->overwrite);


		global $LANG;

		$this->content = array();

		$abs_item=t3quixplorer_div::get_abs_item($dir,$item);

		
		if(t3lib_div::_GP("cancel")){
			header("Location: ".t3quixplorer_div::make_link("list",$dir,NULL));
		}


		if(t3lib_div::_GP("do_extract")){
			$this->unpack($abs_item);
			header("Location: ".t3quixplorer_div::make_link("list",$dir,NULL));
		} else {
			$this->content[] = '<form method="post" action="'.t3quixplorer_div::make_link("extract",$dir,$item).'">';


			

			$fileList = $this->getList($abs_item);

			$this->content[]  = sprintf($LANG->getLL('message.numberoffiles'),count($fileList )).'<br /><br />';

			if(count($fileList)){
				$this->content[] = '
					<select name="filesinarchive[]" size="15" multiple="multiple" >
				';

				foreach ($fileList as $file) {
					if($file == ".") continue;

					$this->content[] = '<option value="'.$file.'">'.$file.'</option>';
				}
				$this->content[] = '</select>';

			}
			$this->content[]= '<br /><input type="checkbox" name="overwrite" value="1">&nbsp;'.$LANG->getLL('message.checkoverwrite');
			$this->content[]= '<br /><br /><input type="submit" name="do_extract" value="'.$LANG->getLL('message.doextract').'">&nbsp;';
			$this->content[]= '<input type="submit" name="cancel" value="'.$LANG->getLL('message.btncancel').'">';
			//$this->content[]= '<input type="submit" name="test" value="TEST">';
			$this->content[]= '</form>';
			return implode("",$this->content);
		}

	}



	/**
	 * This function returns a listing of an compressed file for which we have defined wrappers
	 *
	 * @param	string		Compressed file for which listing should get generated
	 * @return	array		List of files in compressed file
	 */




	/**
	 * This function returns a listing of an compressed file for which we have defined wrappers
	 *
	 * @param	string		Compressed file for which listing should get generated
	 * @return	array		List of files in compressed file
	 */
	function getList($file)	{
		// Handle ZIP Extensions
		if (eregi('\.zip$', $file)) {
			return $this->zipGetList($file);
		}
		// Handle TAR.GZ Extensions
		if (eregi('\.tar\.gz$', $file) || eregi('\.tgz$', $file)) {
			return $this->targzGetList($file);
		}
		// Handle TAR.BZ2 Extensions
		if (eregi('\.tar\.bz2$', $file) || eregi('\.tbz2$', $file)) {
			return $this->tarbz2GetList($file);
		}
		return false;
	}


	/**
	 * This function returns a filelisting of a zip file
	 *
	 * @param	string		Return list of files in zip file
	 * @return	array		List of files
	 */
	function zipGetList($file)	{
		if (!(isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['list']['split_char'])&&
		  isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['list']['pre_lines']) &&
		  isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['list']['post_lines']) &&
		  isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['list']['file_pos']))) {
			return array();
		}
		$unzip = $GLOBALS['TYPO3_CONF_VARS']['BE']['unzip_path']?$GLOBALS['TYPO3_CONF_VARS']['BE']['unzip_path']:'unzip';
		$cmd = $unzip.' -l "'.$file.'"';
		exec($cmd, $list, $ret);
		if ($ret) {
			return array();
		}
		$sc = $GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['list']['split_char'];
		$pre = intval($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['list']['pre_lines']);
		$post = intval($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['list']['post_lines']);
		$pos = intval($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['list']['file_pos']);
		while ($pre--) {
			array_shift($list);
		}
		while ($post--) {
			array_pop($list);
		}
		$fl = array();
		foreach ($list as $file) {
			$parts = explode($sc, $file);
			$fparts = array();
			foreach ($parts as $part) {
				if (strlen($part)) $fparts[] = trim($part);
			}
			$fl[] = $fparts[$pos];
		}
		return $fl;
	}

	/**
	 * This function returns a filelisting of a tar.gz file
	 *
	 * @param	string		Return list of files in tar.gz file
	 * @return	array		List of files
	 */
	function targzGetList($file)	{
		$cmd = 'tar -tzf "'.$file.'"';
		exec($cmd, $list, $ret);
		if ($ret) {
			return array();
		}
		return $list;
	}
	/**
	 * This function returns a filelisting of a tar.bz2 file
	 *
	 * @param	string		Return list of files in tar.bz2 file
	 * @return	array		List of files
	 */
	function tarbz2GetList($file)	{
		$cmd = 'tar -tjf "'.$file.'"';
		exec($cmd, $list, $ret);
		if ($ret) {
			return array();
		}
		return $list;
	}


	/**
	 * This function returns the files extracted by the call to the specific unpack-wrapper for the supplied file
	 *
	 * @param	string		File to unpack
	 * @return	array		Files unpacked
	 */
	function unpack($file)	{
		// Handle ZIP Extensions
		if (eregi('\.zip$', $file)) {
			return $this->zipUnpack($file);
		}
		// Handle TAR.GZ Extensions
		if (eregi('\.tar\.gz$', $file) || eregi('\.tgz$', $file)) {
			return $this->targzUnpack($file);
		}
		// Handle TAR.BZ2 Extensions
		if (eregi('\.tar\.bz2$', $file) || eregi('\.tbz2$', $file)) {
			return $this->tarbz2Unpack($file);
		}
		return false;
	}


	/**
	 * This function unpacks a zip file
	 *
	 * @param	string		File to unpack
	 * @return	array		Files unpacked
	 */

	function zipUnpack($file)	{
		if (!(isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['split_char'])&&
		  isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['pre_lines']) &&
		  isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['post_lines']) &&
		  isset($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['file_pos']))) {
			return array();
		}
		$path = dirname($file);
		chdir($path);
		// Unzip without overwriting existing files
		$unzip = $GLOBALS['TYPO3_CONF_VARS']['BE']['unzip_path']?$GLOBALS['TYPO3_CONF_VARS']['BE']['unzip_path']:'unzip';
		if ($this->overwrite) {
			$cmd = $unzip.' -o "'.$file.'"';
		} else {
			$cmd = $unzip.' -n "'.$file.'"';
		}


		$filesinarchive = t3lib_div::_GP("filesinarchive"); 
		if(is_array($filesinarchive) && !empty($filesinarchive)){
			foreach($filesinarchive as $filetoextract){
				$cmd .= ' "'.$filetoextract.'"';
			}
		}

		exec($cmd, $list, $ret);
		if ($ret) {
			return array();
		}
		$sc = $GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['split_char'];
		$pre = intval($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['pre_lines']);
		$post = intval($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['post_lines']);
		$pos = intval($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip']['unzip']['file_pos']);
		while ($pre--) {
			array_shift($list);
		}
		while ($post--) {
			array_pop($list);
		}
		$fl = array();
		foreach ($list as $file) {
			$parts = explode($sc, $file);
			$fparts = array();
			foreach ($parts as $part) {
				if (strlen($part)) $fparts[] = trim($part);
			}
			$fl[] = $fparts[$pos];
		}
		return $fl;
	}


	/**
	 * This function unpacks a tar.gz file
	 *
	 * @param	string		File to unpack
	 * @return	array		Files unpacked
	 */
	function targzUnpack($file)	{
		$path = dirname($file);
		chdir($path);
		if ($this->overwrite) {
			$cmd = 'tar -xzvf "'.$file.'"';
		} else {
			$cmd = 'tar -xzvkf "'.$file.'"';
		}

		$filesinarchive = t3lib_div::_GP("filesinarchive"); 
		if(is_array($filesinarchive) && !empty($filesinarchive)){
			foreach($filesinarchive as $filetoextract){
				$cmd .= ' "'.$filetoextract.'"';
			}
		}

		exec($cmd, $list, $ret);
		if ($this->overwrite) {
			// We are in overwrite mode
			// Check if return value of
			// exec is set == Error
			if ($ret) {
				return array();
			}
		}
		return $list;
	}


	/**
	 * This function unpacks a tar.bz2 file
	 *
	 * @param	string		File to unpack
	 * @return	array		Files unpacked
	 */
	function tarbz2Unpack($file)	{
		$path = dirname($file);
		chdir($path);
		if ($this->overwrite) {
			$cmd = 'tar -xjvf "'.$file.'"';
		} else {
			$cmd = 'tar -xjvkf "'.$file.'"';
		}

		$filesinarchive = t3lib_div::_GP("filesinarchive"); 
		if(is_array($filesinarchive) && !empty($filesinarchive)){
			foreach($filesinarchive as $filetoextract){
				$cmd .= ' "'.$filetoextract.'"';
			}
		}

		exec($cmd, $list, $ret);
		if ($this->overwrite) {
			// We are in overwrite mode
			// Check if return value of
			// exec is set == Error
			if ($ret) {
				return array();
			}
		}
		return $list;
	}



}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/t3quixplorer/mod1/t3quixplorer_copymove.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/t3quixplorer/mod1/t3quixplorer_copymove.php"]);
}
?>
