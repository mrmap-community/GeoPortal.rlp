<?php
/***************************************************************
*	Copyright notice
*
*	(c)	 2001-2006 Kasper Skaarhoj (kasperYYYY@typo3.com) 	All rights reserved
*
*	This script is part of the TYPO3 project. The TYPO3 project is
*	free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 2 of the License, or
*	(at your option) any later version.
*
*	The GNU General Public License can be found at
*	http://www.gnu.org/copyleft/gpl.html.
*	A copy is found in the textfile GPL.txt and important notices to the license
*	from the author is found in LICENSE.txt distributed with these scripts.
*
*
*	This script is distributed in the hope that it will be useful,
*	but WITHOUT ANY WARRANTY; without even the implied warranty of
*	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
*	GNU General Public License for more details.
*
*	This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * TYPO3 Extension Repository
 *
 * @author	Kasper Skårhøj <kasperYYYY@typo3.com>
 */

require_once(t3lib_extMgm::extPath('kickstarter').'class.tx_kickstarter_compilefiles.php');

class tx_kickstarter_wizard extends tx_kickstarter_compilefiles {
	var $varPrefix = 'kickstarter';		// redundant from 'extrep'
	var $siteBackPath = '';
	var $EMmode=1;	// If run from Extension Manager, set to 1.

	var $wizArray=array();

	var $extKey_nusc = 'myext';
	var $extKey = 'my_ext';
	var $printWOP=0;
	var $outputWOP=0;
	var $saveKey='';
	var $pObj;

	var $afterContent;

	var $languages = array(
		'dk' => 'Danish',
		'de' => 'German',
		'no' => 'Norwegian',
		'it' => 'Italian',
		'fr' => 'French',
		'es' => 'Spanish',
		'nl' => 'Dutch',
		'cz' => 'Czech',
		'pl' => 'Polish',
		'si' => 'Slovenian',
		'fi' => 'Finnish',
		'tr' => 'Turkish',
		'se' => 'Swedish',
		'pt' => 'Portuguese',
		'ru' => 'Russian',
		'ro' => 'Romanian',
		'ch' => 'Chinese',
		'sk' => 'Slovak',
		'lt' => 'Lithuanian',
		'is' => 'Icelandic',
		'hr' => 'Croatian',
		'hu' => 'Hungarian',
		'gl' => 'Greenlandic',
		'th' => 'Thai',
		'gr' => 'Greek',
		'hk' => 'Chinese (Trad)',
		'eu' => 'Basque',
		'bg' => 'Bulgarian',
		'br' => 'Brazilian Portuguese',
		'et' => 'Estonian',
		'ar' => 'Arabic',
		'he' => 'Hebrew',
		'ua' => 'Ukrainian',
		'lv' => 'Latvian',
		'jp' => 'Japanese',
		'vn' => 'Vietnamese',
		'ca' => 'Catalan',
		'ba' => 'Bosnian',
		'kr' => 'Korean',
		'eo' => 'Esperanto',
		'my' => 'Bahasa Malaysia',
		'hi' => 'Hindi',
	);
	var $reservedTypo3Fields='uid,pid,endtime,starttime,sorting,fe_group,hidden,deleted,cruser_id,crdate,tstamp';
	var $mysql_reservedFields='data,table,field,key,desc';

		// Internal:
	var $selectedLanguages = array();
	var $usedNames=array();
	var $fileArray=array();
	var $ext_tables=array();
	var $ext_localconf=array();
	var $ext_locallang=array();

	var $color = array('#C8D0B3','#FEE7B5','#EEEEEE');

	var $modData;

	/**
	 * Constructor
	 */
	function tx_kickstarter_wizard() {
		$this->modData = t3lib_div::_POST($this->varPrefix);
	}

	/**
	 * Initializing the wizard.
	 *
	 * @return	void
	 */
	function initWizArray()	{
		$inArray = unserialize(base64_decode($this->modData['wizArray_ser']));
		$this->wizArray = is_array($inArray) ? $inArray : array();
		if (is_array($this->modData['wizArray_upd']))	{
			$this->wizArray = t3lib_div::array_merge_recursive_overrule($this->wizArray,$this->modData['wizArray_upd']);
		}

		$lA = is_array($this->wizArray['languages']) ? current($this->wizArray['languages']) : '';
		if (is_array($lA))	{
			foreach($lA as $k => $v)	{
				if ($v && isset($this->languages[$k]))	{
					$this->selectedLanguages[$k]=$this->languages[$k];
				}
			}
		}
	}

	/**
	 * Switch between the basic operations. Calls the different modules and puts their
	 * content into a basic framework.
	 *
	 * @return	HTML code for the kickstarter containing the module content
	 */
	function mgm_wizard()	{
		$this->wizard =& $this;
		$this->initWizArray();
		$this->sections = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['sections'];

		foreach($this->sections as $k => $v) {
			$this->options[$k] = array($v['title'],$v['description']);
		}

		$saveKey = $this->saveKey = $this->wizArray['save']['extension_key'] = trim($this->wizArray['save']['extension_key']);
		$this->outputWOP = $this->wizArray['save']['print_wop_comments'] ? 1 : 0;

		if ($saveKey)	{
			$this->extKey=$saveKey;
			$this->extKey_nusc=str_replace('_','',$saveKey);
		}

		if ($this->modData['viewResult'])	{
			$this->modData['wizAction']='';
			$this->modData['wizSubCmd']='';
			if ($saveKey)	{
				$content = $this->view_result();
			} else $content = $this->fw('<strong>Error:</strong> Please enter an extension key first!<br /><br />');
		} elseif ($this->modData['WRITE'])	{
				$this->modData['wizAction']='';
			$this->modData['wizSubCmd']='';
			if ($saveKey)	{
				$this->makeFilesArray($this->saveKey);
				$uploadArray = $this->makeUploadArray($this->saveKey,$this->fileArray);
				
				if (t3lib_div::int_from_ver(TYPO3_version) < t3lib_div::int_from_ver('4.0.0'))	{
						// Syntax for TYPO3 3.8 and older
					$this->pObj->importExtFromRep(0,$this->modData['loc'],0,$uploadArray,0,0,1);
				} else {
						// TYPO3 4.0+ syntax
					$this->pObj->importExtFromRep('','',$this->modData['loc'],0,1,$uploadArray);
				}


			} else $content = $this->fw('<strong>Error:</strong> Please enter an extension key first!<br /><br />');
		} elseif ($this->modData['totalForm'])	{
			$content = $this->totalForm();
		} elseif ($this->modData['downloadAsFile'])	{
			if ($saveKey)	{
				$this->makeFilesArray($this->saveKey);
				$uploadArray = $this->makeUploadArray($this->saveKey,$this->fileArray);
				$backUpData = $this->makeUploadDataFromArray($uploadArray);
				$filename='T3X_'.$saveKey.'-'.str_replace('.','_','0.0.0').'.t3x';
				$mimeType = 'application/octet-stream';
				Header('Content-Type: '.$mimeType);
				Header('Content-Disposition: attachment; filename='.$filename);
				echo $backUpData;
				exit;
			} else $content = $this->fw('<strong>Error:</strong> Please enter an extension key first!<br /><br />');
		} else {
			$action = explode(':',$this->modData['wizAction']);
			if ((string)$action[0]=='deleteEl')	{
				unset($this->wizArray[$action[1]][$action[2]]);
			}

			$content = $this->getFormContent();
		}
		$wasContent = $content?1:0;
		$content = '
		<script language="javascript" type="text/javascript">
			function setFormAnchorPoint(anchor)	{
				document.'.$this->varPrefix.'_wizard.action = unescape("'.rawurlencode($this->linkThisCmd()).'")+"#"+anchor;
			}
		</script>
		<table border="0" cellpadding="0" cellspacing="0">
			<form action="' . $this->linkThisCmd() . '" method="POST" name="' . $this->varPrefix . '_wizard">
			<tr>
				<td valign="top">'.$this->sidemenu().'</td>
				<td>&nbsp;&nbsp;&nbsp;</td>
				<td valign="top">'.$content.'
					<input type="hidden" name="'.$this->piFieldName("wizArray_ser").'" value="'.htmlspecialchars(base64_encode(serialize($this->wizArray))).'" /><br />';

		if ((string)$this->modData['wizSubCmd'])	{
			if ($wasContent)	$content.='<input name="update2" type="submit" value="Update..." /> ';
		}
		$content.='
					<input type="hidden" name="'.$this->piFieldName("wizAction").'" value="'.$this->modData["wizAction"].'" />
					<input type="hidden" name="'.$this->piFieldName("wizSubCmd").'" value="'.$this->modData["wizSubCmd"].'" />
					'.$this->cmdHiddenField().'
				</td>
			</tr>
			</form>
		</table>' . $this->afterContent;

		return $content;
	}

	/**
	 * Get form content
	 *
	 * @return	HTML code of special section
	 */
	function getFormContent()	{

		if($this->sections[$this->modData['wizSubCmd']]) {
			$path = t3lib_div::getFileAbsFileName($this->sections[$this->modData['wizSubCmd']]['filepath']);
			require_once($path);
			$section = t3lib_div::makeInstance($this->sections[$this->modData['wizSubCmd']]['classname']);
			$section->wizard = &$this;
			return $section->render_wizard();
		}
	}

	/**
	 * Total form
	 *
	 * @return	HTML
	 */
	function totalForm()	{
		$buf = array($this->printWOP,$this->dontPrintImages);
		$this->printWOP = 1;

		$lines=array();
		foreach($this->options as $k => $v)	{
			// Add items:
			$items = $this->wizArray[$k];
			if (is_array($items))	{
				foreach($items as $k2 => $conf)	{
					$this->modData['wizSubCmd']=$k;
					$this->modData['wizAction']='edit:'.$k2;
					$lines[]=$this->getFormContent();
				}
			}
		}

		$this->modData['wizSubCmd']='';
		$this->modData['wizAction']='';
		list($this->printWOP,$this->dontPrintImages) = $buf;

		$content = implode('<hr />',$lines);
		return $content;
	}

	/**
	 * Side menu
	 *
	 * @return	HTML code of the side menu
	 */
	function sidemenu()	{
#debug($this->modData);
		$actionType = $this->modData['wizSubCmd'].':'.$this->modData['wizAction'];
		$singles = 'emconf,save,ts,TSconfig,languages';
		$lines=array();
		foreach($this->options as $k => $v)	{
			// Add items:
			$items = $this->wizArray[$k];
			$c=0;
			$iLines=array();
			if (is_array($items))	{
				foreach($items as $k2=>$conf)	{
					$dummyTitle = t3lib_div::inList($singles,$k) ? '[Click to Edit]' : '<em>Item '.$k2.'</em>';
					$isActive = !strcmp($k.':edit:'.$k2,$actionType);
					$delIcon = $this->linkStr('<img src="'.$this->siteBackPath.TYPO3_mainDir.'gfx/garbage.gif" width="11" height="12" border="0" title="Remove item" />','','deleteEl:'.$k.':'.$k2);
					$iLines[]='<tr'.($isActive?$this->bgCol(2,-30):$this->bgCol(2)).'><td>'.$this->fw($this->linkStr($this->bwWithFlag($conf['title']?$conf['title']:$dummyTitle,$isActive),$k,'edit:'.$k2)).'</td><td>'.$delIcon.'</td></tr>';
					$c=$k2;
				}
			}
			if (!t3lib_div::inList($singles,$k) || !count($iLines))	{
				$c++;
				$addIcon = $this->linkStr('<img src="'.$this->siteBackPath.TYPO3_mainDir.'gfx/add.gif" width="12" height="12" border="0" title="Add item" />',$k,'edit:'.$c);
			} else {$addIcon = '';}

			$lines[]='<tr'.$this->bgCol(1).'><td nowrap="nowrap"><strong>'.$this->fw($v[0]).'</strong></td><td>'.$addIcon.'</td></tr>';
			$lines = array_merge($lines,$iLines);
		}

		$lines[]='<tr><td>&nbsp;</td><td></td></tr>';

		$lines[]='<tr><td width="150">
		'.$this->fw('Enter extension key:').'<br />
		<input type="text" name="'.$this->piFieldName('wizArray_upd').'[save][extension_key]" value="'.$this->wizArray['save']['extension_key'].'" />
		'.($this->wizArray['save']['extension_key']?'':'<br /><a href="http://typo3.org/1382.0.html" target="_blank"><font color="red">Make sure to enter the right extension key from the beginning here!</font> You can register one here.</a>').'
		</td><td></td></tr>';
# onClick="setFormAnchorPoint(\'_top\')"
		$lines[]='<tr><td><input type="submit" value="Update..."></td><td></td></tr>';
		$lines[]='<tr><td><input type="submit" name="'.$this->piFieldName('totalForm').'" value="Total form"></td><td></td></tr>';

		if ($this->saveKey)	{
			$lines[]='<tr><td><input type="submit" name="'.$this->piFieldName('viewResult').'" value="View result"></td><td></td></tr>';
			$lines[]='<tr><td><input type="submit" name="'.$this->piFieldName('downloadAsFile').'" value="D/L as file"></td><td></td></tr>';
			$lines[]='<tr><td>
			<input type="hidden" name="'.$this->piFieldName('wizArray_upd').'[save][print_wop_comments]" value="0" /><input type="checkbox" name="'.$this->piFieldName('wizArray_upd').'[save][print_wop_comments]" value="1" '.($this->wizArray['save']['print_wop_comments']?' checked="checked"':'').' />'.$this->fw('Print WOP comments').'
			</td><td></td></tr>';
		}

		/* HOOK: Place a hook here, so additional output can be integrated */
		if(is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['sidemenu'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kickstarter']['sidemenu'] as $_funcRef) {
				$lines = t3lib_div::callUserFunction($_funcRef, $lines, $this);
			}
		}

		$content = '<table border="0" cellpadding="2" cellspacing="2">'.implode('',$lines).'</table>';
		return $content;
	}

	/**
	 * View result
	 *
	 * @return	HTML with filelist and fileview
	 */
	function view_result()	{
		$this->makeFilesArray($this->saveKey);

			// Empty the array of files to be overwritten
		$this->wizArray['save']['overwrite_files'] = array();

		$keyA = array_keys($this->fileArray);
		asort($keyA);

		$filesOverview1=array();
		$filesOverview2=array();
		$filesContent=array();

		$filesOverview1[]= '<tr'.$this->bgCol(1).'>
			<td><strong>' . $this->fw('Filename:') . '</strong></td>
			<td><strong>' . $this->fw('Size:') . '</strong></td>
			<td><strong>' . $this->fw('&nbsp;') . '</strong></td>
			<td><strong>' . $this->fw('Overwrite:') . '</strong></td>
		</tr>';

		foreach($keyA as $fileName)	{
			$data = $this->fileArray[$fileName];

			$fI = pathinfo($fileName);
			if (t3lib_div::inList('php,sql,txt,xml',strtolower($fI['extension'])))	{
				$linkToFile='<strong><a href="#'.md5($fileName).'">'.$this->fw("&nbsp;View&nbsp;").'</a></strong>';
				$filesContent[]='<tr' .$this->bgCol(1) .'>
				<td><a name="' . md5($fileName) . '"></a><strong>' . $this->fw($fileName) . '</strong></td>
				</tr>
				<tr>
					<td>' . $this->preWrap($data['content']) . '</td>
				</tr>';
			} else $linkToFile=$this->fw('&nbsp;');

			$line = '<tr' . $this->bgCol(2) . '>
				<td>' . $this->fw($fileName) . '</td>
				<td>' . $this->fw(t3lib_div::formatSize($data['size'])) . '</td>
				<td>' . $linkToFile . '</td>
				<td><input type="checkbox" name="' . $this->piFieldName('wizArray_upd') . '[save][overwrite_files][]" value="' . $fileName . '" checked="checked" /></td>
			</tr>';
			if (strstr($fileName,'/'))	{
				$filesOverview2[]=$line;
			} else {
				$filesOverview1[]=$line;
			}
		}

		$content = '<table border="0" cellpadding="1" cellspacing="2">'.implode('',$filesOverview1).implode('',$filesOverview2).'</table>';
		$content.= $this->fw('<br /><strong>Author name:</strong> '.$GLOBALS['BE_USER']->user['realName'].'
							<br /><strong>Author email:</strong> '.$GLOBALS['BE_USER']->user['email']);


		$content.= '<br /><br />';
		if (!$this->EMmode)	{
			$content.='<input type="submit" name="'.$this->piFieldName('WRITE').'" value="WRITE to \''.$this->saveKey.'\'" />';
		} else {
			$content.='
				<strong>'.$this->fw('Write to location:').'</strong><br />
				<select name="'.$this->piFieldName('loc').'">'.
					($this->pObj->importAsType('G')?'<option value="G">Global: '.$this->pObj->typePaths['G'].$this->saveKey.'/'.(@is_dir(PATH_site.$this->pObj->typePaths['G'].$this->saveKey)?' (OVERWRITE)':' (empty)').'</option>':'').
					($this->pObj->importAsType('L')?'<option value="L">Local: '.$this->pObj->typePaths['L'].$this->saveKey.'/'.(@is_dir(PATH_site.$this->pObj->typePaths['L'].$this->saveKey)?' (OVERWRITE)':' (empty)').'</option>':'').
				'</select>
				<input type="submit" name="'.$this->piFieldName('WRITE').'" value="WRITE" onclick="return confirm(\'If the setting in the selectorbox says OVERWRITE\nthen the marked files of the current extension in that location will be OVERRIDDEN! \nPlease decide if you want to continue.\n\n(Remember, this is a *kickstarter* - NOT AN editor!)\');" />
			';
		}


		$this->afterContent= '<br /><table border="0" cellpadding="1" cellspacing="2">'.implode('',$filesContent).'</table>';
		return $content;
	}



	/**
	 * Encodes extension upload array
	 *
	 * @param	array		$uploadArray: The data array that should be serialized
	 * @return	string		serialized data prepended with md5 checksum
	 */
	function makeUploadDataFromArray($uploadArray)	{
		if (is_array($uploadArray))	{
			$serialized = serialize($uploadArray);
			$md5 = md5($serialized);

			$content=$md5.':';
/*			if ($this->gzcompress)	{
				$content.='gzcompress:';
				$content.=gzcompress($serialized);
			} else {
	*/			$content.=':';
				$content.=$serialized;
//			}
		}
		return $content;
	}
	/**
	 * Make upload array out of extension
	 *
	 * @param	string		$extKey: extension key
	 * @param	array		$files: array with filedata
	 * @return	array of extension files
	 */
	function makeUploadArray($extKey,$files)	{
		$uploadArray=array();
		$uploadArray['extKey']=$extKey;
		$uploadArray['EM_CONF']=Array(
			'title' => '[No title]',
			'description' => '[Enter description of extension]',
			'category' => 'example',
			'author' => $this->userfield('name'),
			'author_email' => $this->userfield('email'),

		);

		$uploadArray['EM_CONF'] = array_merge($uploadArray['EM_CONF'],$this->makeEMCONFpreset(''));

		if (is_array($this->_addArray))	{
			$uploadArray['EM_CONF'] = array_merge($uploadArray['EM_CONF'],$this->_addArray);
		}
		$uploadArray['misc']['codelines']=0;
		$uploadArray['misc']['codebytes']=0;
		$uploadArray['techInfo'] = '';

			// Go through overwrite-files list to determine which files are to be written to disk
			// This allows to change only certain files on disk while keeping all others
		if(is_array($this->wizArray['save']['overwrite_files'])) {
			foreach($this->wizArray['save']['overwrite_files'] as $fileName) {
				$uploadArray['FILES'][$fileName] = $files[$fileName];
			}
		}
		return $uploadArray;
	}


}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/kickstarter/class.tx_kickstarter_wizard.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/kickstarter/class.tx_kickstarter_wizard.php']);
}

?>
