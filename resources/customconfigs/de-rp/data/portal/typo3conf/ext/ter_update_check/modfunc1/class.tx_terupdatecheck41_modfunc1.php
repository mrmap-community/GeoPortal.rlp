<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Christian Welzel (gawain@camlann.de)
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
 * Module extension (addition to function menu) 'Check for updates' for the 'ter_update_check' extension.
 *
 * @author	Christian Welzel <gawain@camlann.de>
 */

require_once(PATH_t3lib."class.t3lib_extobjbase.php");

class tx_terupdatecheck41_modfunc1 extends t3lib_extobjbase {

	function modMenu() {
	    return Array (
		"tx_ter_update_check_display_installed" => "",
		"tx_ter_update_check_display_devupd" => "",
		"tx_ter_update_check_display_diff" => "",
		"tx_ter_update_check_display_files" => "",
	    );
	}

	function main() {
	    global $LANG;

	    $this->doc =& $this->pObj->doc;
	    $this->content = '';

	    $content = $LANG->getLL('display_shy') . ':&nbsp;&nbsp;' .
		t3lib_BEfunc::getFuncCheck(0,
		    'SET[display_shy]', 
		    $this->pObj->MOD_SETTINGS['display_shy'], 
		    '',
		    '&SET[function]=tx_terupdatecheck41_modfunc1');

	    if($this->pObj->CMD[doUpdate]==1) return $this->do_update();

	    if(is_file(PATH_site.'typo3temp/extensions.xml.gz')) {
			$tmp  = $this->do_show();
			$tmp .= $LANG->getLL('display_nle').':&nbsp;&nbsp;'.
			    t3lib_BEfunc::getFuncCheck(0,
				'SET[tx_ter_update_check_display_installed]',
				$this->pObj->MOD_SETTINGS['tx_ter_update_check_display_installed'], 
				'',
				'&SET[function]=tx_terupdatecheck41_modfunc1');
			$tmp .= '<br/>';
			$tmp .= $LANG->getLL('display_dev').':&nbsp;&nbsp;'.
			    t3lib_BEfunc::getFuncCheck(0,
				'SET[tx_ter_update_check_display_devupd]',
				$this->pObj->MOD_SETTINGS['tx_ter_update_check_display_devupd'], 
				'',
				'&SET[function]=tx_terupdatecheck41_modfunc1');
			$tmp .= '<br/>';
			$tmp .= $LANG->getLL('display_files').':&nbsp;&nbsp;'.
			    t3lib_BEfunc::getFuncCheck(0,
				'SET[tx_ter_update_check_display_files]',
				$this->pObj->MOD_SETTINGS['tx_ter_update_check_display_files'], 
				'',
				'&SET[function]=tx_terupdatecheck41_modfunc1');
			$content .= $this->pObj->doc->section($LANG->getLL('header_upd_ext'), $tmp, 0, 1);
	    }
	    $tmp  = '<input type="submit" value="'.$LANG->getLL('button_get_vers').'" '.
		'onclick="document.location=\'index.php?SET[function]=tx_terupdatecheck41_modfunc1&CMD[doUpdate]=1\';return false;" />';

	    if(is_file(PATH_site.'typo3temp/extensions.xml.gz')) {
			$tmp .= ' ('.$LANG->getLL('note_last_update').': '.date('Y-m-d H:i',filemtime(PATH_site.'typo3temp/extensions.xml.gz')).')';
	    }

	    $tmp .= '<br/><br/>';
	    $tmp .= $this->pObj->doc->section($LANG->getLL('header_priv_notc'), $LANG->getLL('msg_privacy'));
	    $content .= $this->pObj->doc->section($LANG->getLL('header_vers_ret'), $tmp, 0, 1);

	    return $content;
	}

	function do_show() {
	    global $LANG;

	    $tmp = & $this->pObj->getInstalledExtensions();
	    $list = & $tmp[0];

	    $content[] = '<table border="0" cellpadding="2" cellspacing="1">'.
	        '<tr class="bgColor5">'.
		    '<td></td>'.
		    '<td>'.$LANG->getLL('tab_mod_name').'</td>'.
		    '<td>'.$LANG->getLL('tab_mod_key').'</td>'.
		    '<td>'.$LANG->getLL('tab_mod_loc_ver').'</td>'.
		    '<td>'.$LANG->getLL('tab_mod_rem_ver').'</td>'.
		    '<td>'.$LANG->getLL('tab_mod_location').'</td>'.
		    '<td>'.$LANG->getLL('tab_mod_comment').'</td>'.
		'</tr>';

	    $diff = $this->pObj->MOD_SETTINGS['tx_ter_update_check_display_devupd'] ? 1 : 1000;

#		$this->pObj->xmlhandler->searchExtensionsXML('', '', '', true);
#	    $this->pObj->xmlhandler->loadExtensionsXML();

	    reset($list);
	    while (list($name,) = each($list)) {
			$data = & $list[$name];

			$this->pObj->xmlhandler->searchExtensionsXML($name, '', '', false, true);

			if(!is_array($this->pObj->xmlhandler->extensionsXML[$name])) continue;

			$v = & $this->pObj->xmlhandler->extensionsXML[$name][versions];
			$versions = array_keys($v);
			$lastversion = end($versions);

	        if( (t3lib_extMgm::isLoaded($name) || $this->pObj->MOD_SETTINGS['tx_ter_update_check_display_installed']) &&
		    ($data[EM_CONF][shy] == 0 || $this->pObj->MOD_SETTINGS['display_shy']) &&
		    $this->pObj->versionDifference($lastversion, $data[EM_CONF][version], $diff))
		{
	            $imgInfo = @getImageSize($this->pObj->getExtPath($name,$data['type']).'/ext_icon.gif');
		    if (is_array($imgInfo)) {
		        $icon = '<img src="'.$GLOBALS['BACK_PATH'].$this->pObj->typeRelPaths[$data['type']].$name.'/ext_icon.gif'.'" '.$imgInfo[3].' alt="" />';
		    } elseif ($extInfo['_ICON']) {
		        $icon = $extInfo['_ICON'];
		    } else {
		        $icon = '<img src="clear.gif" width="1" height="1" alt="" />';
		    }

		    $comment = '<table cellpadding="0" cellspacing="0" width="100%">';
		    foreach($versions as $vk) {
			$va = & $v[$vk];

			if(t3lib_div::int_from_ver($vk) < t3lib_div::int_from_ver($data[EM_CONF][version])) continue;

			$comment .= '<tr><td valign="top" style="padding-right:2px;border-bottom:1px dotted gray">'.$vk.'</td>'.
				    '<td valign="top" style="border-bottom:1px dotted gray">'.nl2br($va[uploadcomment]).'</td></tr>';
		    }
		    $comment .= '</table>';

		    $currentMd5Array = $this->pObj->serverExtensionMD5Array($name,$data);

		    $warn = '';
		    if (strcmp($data['EM_CONF']['_md5_values_when_last_written'],serialize($currentMd5Array)))   {
		        $warn = '<tr class="bgColor4" style="color:red"><td colspan="7">'.$GLOBALS['TBE_TEMPLATE']->rfw('<br /><strong>'.$name.': '.$LANG->getLL('msg_warn_diff').'</strong>').'</td></tr>'."\n";
			if($this->pObj->MOD_SETTINGS['tx_ter_update_check_display_files'] == 1) {
			    $affectedFiles = $this->pObj->findMD5ArrayDiff($currentMd5Array,unserialize($data['EM_CONF']['_md5_values_when_last_written']));
			    if (count($affectedFiles))
				$warn .= '<tr class="bgColor4"><td colspan="7"><strong>'.$LANG->getLL('msg_modified').'</strong><br />'.$GLOBALS['TBE_TEMPLATE']->rfw(implode('<br />',$affectedFiles)).'</td></tr>'."\n";
			}
		    }

		    $content[] = '<tr class="bgColor4"><td valign="top">'.$icon.'</td>'.
			        '<td valign="top"><a href="?CMD[importExtInfo]='.$name.'">'.$data[EM_CONF][title].'</a></td>'.
				'<td valign="top">'.$name.'</td>'.
			        '<td valign="top" align="right">'.$data[EM_CONF][version].'</td>'.
				'<td valign="top" align="right">'.$lastversion.'</td>'.
				'<td valign="top" nowrap="nowrap">'.$this->pObj->typeLabels[$data['type']].(strlen($data['doubleInstall'])>1?'<strong> '.$GLOBALS['TBE_TEMPLATE']->rfw($extInfo['doubleInstall']).'</strong>':'').'</td>'.
				'<td valign="top">'.$comment.'</td></tr>'."\n".
				$warn.
				'<tr class="bgColor4"><td colspan="7"><hr style="margin:0px" /></td></tr>'."\n";
		}
	    }

	    $content[] = '</table><br/>';

	    return implode('',$content);
	}

	function do_update()	{
	    global $LANG;

	    $tmp = $this->pObj->content;
	    $this->pObj->content = '';
	    $this->pObj->fetchMetaData('extensions');
	    $msg = $this->pObj->content;
	    $this->pObj->content = $tmp;

	    $tmp = '';
	    if(strpos($msg, 'Error') === false) {
	        $tmp .= $LANG->getLL('msg_succ').'<br/><br/>';
	        $tmp .= '<input type="submit" value="'.$LANG->getLL('button_show_ext').'" '.
	                'onclick="document.location=\'index.php?SET[function]=tx_terupdatecheck41_modfunc1\';return false;" />';
	    } else {
	        $tmp .= $LANG->getLL('msg_fail_ter2');
	    }

	    return $this->pObj->doc->section($LANG->getLL('header_vers_ret'), $tmp, 0, 1);

	}

}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/ter_update_check/modfunc1/class.tx_terupdatecheck41_modfunc1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/ter_update_check/modfunc1/class.tx_terupdatecheck41_modfunc1.php"]);
}

?>
