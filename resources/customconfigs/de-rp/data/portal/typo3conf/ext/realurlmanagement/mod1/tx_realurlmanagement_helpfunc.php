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
 *   56: class tx_realurlmanagement_helpfunc extends t3lib_SCbase
 *   72:     function encodePageName($title,$table='pagePath')
 *  104:     function getSpaceCharacter($tableName)
 *  134:     function searchForSpaceCharacter($array)
 *  154:     function decodePageName($title)
 *  168:     function getOrderBy($fields,$orderBy,$orderAscDesc,$additionalUrl)
 *  197:     function getDateTime($date,$format='d. m. Y - H:i:s')
 *  216:     function extGetTreeList($id,$depth,$begin = 0,$perms_clause)
 *  246:     function writeJSForDateTimeValidation()
 *  251:     function typo3FormFieldSet(theField, evallist, is_in, checkbox, checkboxValue)
 *  271:     function typo3FormFieldGet(theField, evallist, is_in, checkbox, checkboxValue, checkbox_off, checkSetValue)
 *  298:     function changeExpireDate($tablename, $where, $expireField)
 *  334:     function getLangArray()
 *  357:     function getLanguage($language,$langArr)
 *  371:     function setArrayTo_GP($array)
 *  381:     function getArrayFrom_GP($string)
 *  391:     function getPageBrowser($pageBrowser)
 *
 * TOTAL FUNCTIONS: 16
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
class tx_realurlmanagement_helpfunc extends t3lib_SCbase {


	/**
	 * Convert a title to something that can be used in an page path:
	 * - Convert spaces to underscores
	 * - Convert non A-Z characters to ASCII equivalents
	 * - Convert some special things like the 'ae'-character
	 * - Strip off all other symbols
	 * Works with the character set defined as "forceCharset"
	 *
	 * @param	string		Input title to clean
	 * @param	string		Input table name
	 * @return	string		Encoded title, passed through rawurlencode() = ready to put in the URL.
	 * @see tx_realurl_advanced::encodeTitle($title)
	 */
	function encodePageName($title,$table='pagePath') {
		$encodingLib = t3lib_div::makeinstance('t3lib_cs');
		$spaceCharacter=$this->getSpaceCharacter($table);
					// Fetch character set:
		$charset = $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] ? $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] : $GLOBALS['TSFE']->defaultCharSet;

			// Convert to lowercase:
		$processedTitle = $encodingLib->conv_case($charset,$title,'toLower');

			// Convert some special tokens to the space character:
		$space = $spaceCharacter ? $spaceCharacter : '_';
		$processedTitle = strtr($processedTitle,' -+_',$space.$space.$space.$space); // convert spaces

			// Convert extended letters to ascii equivalents:
		$processedTitle = $encodingLib->specCharsToASCII($charset,$processedTitle);

			// Strip the rest...:
		$processedTitle = ereg_replace('[^a-zA-Z0-9\\'.$space.']', '', $processedTitle); // strip the rest
		$processedTitle = ereg_replace('\\'.$space.'+',$space,$processedTitle); // Convert multiple 'spaces' to a single one
		$processedTitle = trim($processedTitle,$space);

		// Return encoded URL:
		return rawurlencode($processedTitle);
	}


	/**
	 * this function return the space character for specified table
	 *
	 * @param	string		table for alias
	 * @return	string		space character
	 */
	function getSpaceCharacter($tableName){
		$realUrlConfiguration_original=$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl'];
		$confFile=t3lib_div::getFileAbsFileName('typo3temp/realUrlManagement.conf');
		if(@!file_exists($confFile)){
			$this->searchForSpaceCharacter($realUrlConfiguration_original);
			$setValues['md5']=md5(serialize($realUrlConfiguration_original));
			$setValues['spaceArray']=serialize($this->spaceArray);
			$res = t3lib_div::writeFileToTypo3tempDir($confFile, serialize($setValues));
			if ($res)	die('ERROR: '.$res);
		};
		$getValues=unserialize(t3lib_div::getUrl($confFile));
		if(md5(serialize($realUrlConfiguration_original))!=$getValues['md5']){
		 	/* ak nesedi md5 hash */
			$this->searchForSpaceCharacter($realUrlConfiguration_original);
			$setValues['md5']=md5(serialize($realUrlConfiguration_original));
			$setValues['spaceArray']=serialize($this->spaceArray);
			$res = t3lib_div::writeFileToTypo3tempDir($confFile, serialize($setValues));
			if ($res)	die('ERROR: '.$res);
			$getValues=$setValues;
		};
		$spaceCharacters=unserialize($getValues['spaceArray']);
		return $spaceCharacters[$tableName];
	}

	/**
	 * this function fill the value $this->spaceArray with spaceCharacters
	 *
	 * @param	string		array used for recursivyty
	 * @return	none
	 */
	function searchForSpaceCharacter($array){
		if(is_array($array)&&count($array!=0)){
			while(list($key,$val)=each($array)){
				if(strval($key)=='pagePath'){
					$this->spaceArray['pagePath']=$val['spaceCharacter'];
				}else if(strval($key)=='lookUpTable'){
					$this->spaceArray[$val['table']]=$val['useUniqueCache_conf']['spaceCharacter'];
				}else{
					$this->searchForSpaceCharacter($val);
				}
			}
		}
	}

	/**
	 * decode the title obdained from encodePageName
	 *
	 * @param	string		Input title to decode
	 * @return	string		Dencoded title
	 */
	function decodePageName($title){
		return rawurldecode($title);
	}


	/**
	 * return array width HTML code for orderby collumn
	 *
	 * @param	string		$fields: all fields used for ordering
	 * @param	string		$orderBy: curently selected field
	 * @param	string		$orderAscDesc: 'asc' or 'desc'
	 * @param	string		$additionalUrl: additional URL e.g. pg_pointer,...
	 * @return	array		array width HTML code for orderby collumn e.g. array('field1'=>HTML,'field2'=>HTML)
	 */
	function getOrderBy($fields,$orderBy,$orderAscDesc,$additionalUrl){
		$fieldsArray=explode(',',$fields);
		$returnArray=array();
		$images['desc']=$this->pObj->imageOrderDescActiv;
		$images['asc']=$this->pObj->imageOrderAscActiv;
		if((trim($fields!=''))&&(count($fieldsArray)>0)){
			foreach($fieldsArray as $field){
				$aHref = 'index.php?mode='.$this->pObj->MOD_SETTINGS['mode'].'&depth='.$this->pObj->MOD_SETTINGS['depth'].'&id='.$this->pObj->pageuid.'&tstamp='.time().'&ordBy='.$field.'&ordAscDesc=';
				if($orderBy==$field){
					$aHref.=$orderAscDesc=='asc'?'desc':'asc';
					$aHref.=$additionalUrl;
					$returnArray[$field]='<td class="bgColor5" nowrap="nowrap">&nbsp;<a href="'.htmlspecialchars($aHref).'">'.$images[$orderAscDesc].'</a>&nbsp;</td>';
				}else{
					$aHref.='asc';
					$aHref.=$additionalUrl;
					$returnArray[$field]='<td class="bgColor5" nowrap="nowrap">&nbsp;<a href="'.htmlspecialchars($aHref).'">'.$this->pObj->imageOrderAscInactiv.'</a>&nbsp;</td>';
				}
			}
		}
		return $returnArray;
	}

	/**
	 * convert the date and time from int value to d. m. Y - H:i:s
	 *
	 * @param	int		$date: date int value
	 * @param	string		$format: format of the returned data default this is 'd. m. Y - H:i:s'
	 * @return	string		'none' or 'd. m. Y - H:i:s'
	 */
	function getDateTime($date,$format='d. m. Y - H:i:s'){
		if(intval($date)==0){
			$return_date='none';
		}else{
			$return_date=date($format,$date);
		}
		return $return_date;
	}

	/**
	 * Generates a list of Page-uid's from $id. List does not include $id itself
	 * The only pages excluded from the list are deleted pages.
	 *
	 * @param	integer		Start page id
	 * @param	integer		Depth to traverse down the page tree.
	 * @param	integer		$begin is an optional integer that determines at which level in the tree to start collecting uid's. Zero means 'start right away', 1 = 'next level and out'
	 * @param	string		Perms clause
	 * @return	string		Returns the list with a comma in the end (if any pages selected!)
	 */
	function extGetTreeList($id,$depth,$begin = 0,$perms_clause){
		$depth=intval($depth);
		$begin=intval($begin);
		$id=intval($id);
		$theList='';

		if ($id && $depth>0)	{
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'uid,title',
						'pages',
						'pid='.$id.' AND deleted=0 AND '.$perms_clause
					);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
				if ($begin<=0)	{
					$theList.=$row['uid'].',';
					$this->extPageInTreeInfo[]=array($row['uid'],$row['title'],$depth);
				}
				if ($depth>1)	{
					$theList.=$this->extGetTreeList($row['uid'], $depth-1,$begin-1,$perms_clause);
				}
			}
		}
		return $theList;
	}

	/**
	 * write to postCode javascript required for datetime field evaluation
	 *
	 * @return	none
	 */
	function writeJSForDateTimeValidation(){
	$this->pObj->doc->postCode.='
		<script type="text/javascript">
		  /*<![CDATA[*/
	  		var evalFunc = new evalFunc();
			function typo3FormFieldSet(theField, evallist, is_in, checkbox, checkboxValue)	{
				if (document.editform[theField])	{
					var theFObj = new evalFunc_dummy (evallist,is_in, checkbox, checkboxValue);
					var theValue = document.editform[theField].value;
					if (checkbox && theValue==checkboxValue)	{
						document.editform[theField+"_hr"].value="";
						if (document.editform[theField+"_cb"])	document.editform[theField+"_cb"].checked = "";
					} else {
						document.editform[theField+"_hr"].value = evalFunc.outputObjValue(theFObj, theValue);
						if (document.editform[theField+"_cb"])	document.editform[theField+"_cb"].checked = "on";
					}
				}
			}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$theField, evallist, is_in, checkbox, checkboxValue, checkbox_off, checkSetValue: ...
	 * @return	[type]		...
	 */
			function typo3FormFieldGet(theField, evallist, is_in, checkbox, checkboxValue, checkbox_off, checkSetValue)	{
				if (document.editform[theField])	{
					var theFObj = new evalFunc_dummy (evallist,is_in, checkbox, checkboxValue);
					if (checkbox_off)	{
						if (document.editform[theField+"_cb"].checked)	{
							document.editform[theField].value=checkSetValue;
						} else {
							document.editform[theField].value=checkboxValue;
						}
					}else{
						document.editform[theField].value = evalFunc.evalObjValue(theFObj, document.editform[theField+"_hr"].value);
					}
					typo3FormFieldSet(theField, evallist, is_in, checkbox, checkboxValue);
				}
			}
				 /*]]>*/
		</script>';
	}

	/**
	 * Change the expire date in database
	 *
	 * @param	string		Table name
	 * @param	string		Where part
	 * @param	string		Expire field
	 * @return	none
	 */
	function changeExpireDate($tablename, $where, $expireField){
		$ret['showEditInput']=true;
		$action_array['expirepage']=t3lib_div::_GP('expirepage');
		$action_array['expirepage_cb']=t3lib_div::_GP('expirepage_cb');
		if((intval(t3lib_div::_GP('editsave_x'))!=0)&&(intval(t3lib_div::_GP('editsave_y'))!=0)){
			$ret['action_save']=true;
			$ret['showEditInput']=false;
		}
		if((intval(t3lib_div::_GP('closedok_x'))!=0)&&(intval(t3lib_div::_GP('closedok_y'))!=0)){
			$ret['action_save']=false;
			$ret['showEditInput']=false;
		}
		if($ret['action_save']){
			if(($action_array['expirepage']==0)||($action_array['expirepage_cb']!='on')){
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
					$tablename,
					$where,
					array(strval($expireField)=>0)
				);
			}else{
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
					$tablename,
					$where,
					array(strval($expireField)=>$action_array['expirepage'])
				);
			};
			$ret['showEditInput']=false;
		};
		return $ret;
	}

	/**
	 * get the array from all awailable languages
	 *
	 * @return	array		array
	 */
	function getLangArray(){
		$langArr[0]['title']=$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_general.php:LGL.default_value',1);
		$langArr[0]['flag']='';
		$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'uid, title,flag',
			'sys_language',
			'hidden=0');
		if($GLOBALS['TYPO3_DB']->sql_num_rows($res)>0){
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				$langArr[$row['uid']]['title']=$row['title'];
				$langArr[$row['uid']]['flag']=$row['flag'];
			};
		}
		return $langArr;
	}

	/**
	 * return language from array
	 *
	 * @param	int		language number
	 * @param	array		array width all languages obtained from getLangArray() function
	 * @return	string		language
	 */
	function getLanguage($language,$langArr){
		$flag='';
		if($langArr[$language]['flag']!=''){
			$flag='<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/flags/'.$langArr[$language]['flag'],'width="20" height="12"').' alt="'.$langArr[$language]['title'].'" />&nbsp;';
		};
		return $flag.$langArr[$language]['title'];
	}

	/**
	 * return an urlencoded serialized array, it is usefull if you want to use array as a _GP parameter
	 *
	 * @param	array		array to encode
	 * @return	string		serialized and encoded array
	 */
	function setArrayTo_GP($array){
		return urlencode(serialize($array));
	}

	/**
	 * return array from string obtained from setArrayTo_GP function
	 *
	 * @param	string		serialized and encoded array
	 * @return	array
	 */
	function getArrayFrom_GP($string){
		return unserialize(urldecode($string));
	}

	/**
	 * Pagebrowser
	 *
	 * @param	array		array with pagebrowser setting
	 * @return	string		HTML
	 */
	function getPageBrowser($pageBrowser) {
		$begin_at = $pageBrowser['pointer'] * $pageBrowser['showElements'];
		// Make Next link
		if ($pageBrowser['count'] > $begin_at + $pageBrowser['showElements']) {
			$next = ($begin_at + $pageBrowser['showElements'] > $pageBrowser['count']) ? $pageBrowser['count'] - $pageBrowser['showElements']:$begin_at + $pageBrowser['showElements'];
			$next = intval($next / $pageBrowser['showElements']);
			$return['next'] = '<a href="'.$pageBrowser['oldURL'].'&pb_pointer='.$next.'">'.$GLOBALS['LANG']->getLL('pageBrowser_next').'</a>';
		} else {
			$return['next'] = '<span>'.$GLOBALS['LANG']->getLL('pageBrowser_next').'</span>';
		}
		// Make Previous link
		if ($begin_at) {
			$prev = ($begin_at - $pageBrowser['showElements'] < 0)?0:$begin_at - $pageBrowser['showElements'];
			$prev = intval($prev / $pageBrowser['showElements']);
			$return['prev'] = '<a href="'.$pageBrowser['oldURL'].'&pb_pointer='.$prev.'">'.$GLOBALS['LANG']->getLL('pageBrowser_prev').'</a>';
		} else {
			$return['prev'] = '<span>'.$GLOBALS['LANG']->getLL('pageBrowser_prev').'</span>';
		}

		$firstPage = 0;
		$lastPage = $pages = ceil($pageBrowser['count'] / $pageBrowser['showElements']);
		$actualPage = floor($begin_at / $pageBrowser['showElements']);

		if ($lastPage > $pageBrowser['showPages']) {
			// if there are more pages than allowed in 'maxPages', calculate the first and the lastpage to show. The current page is shown in the middle of the list.
			$precedingPagesCount = floor($pageBrowser['showPages'] / 2);
			$followPagesCount = $pageBrowser['showPages'] - $precedingPagesCount;
			// set firstpage and lastpage
			$firstPage = $actualPage - $precedingPagesCount;
			if ($firstPage < 0) {
				$firstPage = 0;
				$lastPage = $pageBrowser['showPages'];
			} else {
				$lastPage = $actualPage + $followPagesCount;
				if ($lastPage > $pages) {
					$lastPage = $pages;
					$firstPage = $pages - $pageBrowser['showPages'];
				}
			}
		}

		for ($i = $firstPage ; $i < $lastPage; $i++) {
			if (($begin_at >= $i * $pageBrowser['showElements']) && ($begin_at < $i * $pageBrowser['showElements'] + $pageBrowser['showElements'])) {
				$return['browse'] .= '<span>'.$GLOBALS['LANG']->getLL('pageBrowser_page_show').(string)($i + 1).'</span>';
			} else {
				$return['browse'] .= '<a href="'.$pageBrowser['oldURL'].'&pb_pointer='.(string)($i).'" title="'.$GLOBALS['LANG']->getLL('pageBrowser_page_title').(string)($i + 1).'">'.$GLOBALS['LANG']->getLL('pageBrowser_page_show').(string)($i + 1).'</a>';
			}
		}


		$return['browse'] = $return['prev'] . $return['browse'] . $return['next'];

		return '<div class="pagebrowser">'.$return['browse'].'</div>';
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/realurlmanagement/mod1/tx_realurlmanagement_helpfunc.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/realurlmanagement/mod1/tx_realurlmanagement_helpfunc.php']);
}

?>