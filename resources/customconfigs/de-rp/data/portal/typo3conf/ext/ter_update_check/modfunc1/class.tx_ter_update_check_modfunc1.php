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

class tx_ter_update_check_modfunc1 extends t3lib_extobjbase {

	function modMenu() {
	    return Array (
		"tx_ter_update_check_display_installed" => "",
		"tx_ter_update_check_display_devupd" => "",
	    );
	}

	function main() {
	    global $LANG;

	    $content = $LANG->getLL('display_shy') . ':&nbsp;&nbsp;' .
		t3lib_BEfunc::getFuncCheck(0,
		    'SET[display_shy]', 
		    $this->pObj->MOD_SETTINGS['display_shy'], 
		    '',
		    '&SET[function]=tx_ter_update_check_modfunc1');

	    if($this->pObj->CMD[doUpdate]==1) return $this->do_update();

	    if(is_file(PATH_site.'typo3temp/ter_update_check.tmp')) {
	    	$tmp  = $this->do_show();
		$tmp .= $LANG->getLL('display_nle').':&nbsp;&nbsp;'.
		    t3lib_BEfunc::getFuncCheck(0,
			'SET[tx_ter_update_check_display_installed]',
			$this->pObj->MOD_SETTINGS['tx_ter_update_check_display_installed'], 
			'',
			'&SET[function]=tx_ter_update_check_modfunc1');
		$tmp .= '<br/>';
		$tmp .= $LANG->getLL('display_dev').':&nbsp;&nbsp;'.
		    t3lib_BEfunc::getFuncCheck(0,
			'SET[tx_ter_update_check_display_devupd]',
			$this->pObj->MOD_SETTINGS['tx_ter_update_check_display_devupd'], 
			'',
			'&SET[function]=tx_ter_update_check_modfunc1');
		$content .= $this->pObj->doc->section($LANG->getLL('header_upd_ext'), $tmp, 0, 1);
	    }
	    $tmp  = '<input type="submit" value="'.$LANG->getLL('button_get_vers').'" '.
		'onclick="document.location=\'index.php?SET[function]=tx_ter_update_check_modfunc1&CMD[doUpdate]=1\';return false;" /><br/><br/>';
	    $tmp .= $this->pObj->doc->section($LANG->getLL('header_priv_notc'), $LANG->getLL('msg_privacy'));
	    $content .= $this->pObj->doc->section($LANG->getLL('header_vers_ret'), $tmp, 0, 1);

	    return $content;
	}

	function do_show() {
	    global $LANG;

	    list($list,$cat)=$this->pObj->getInstalledExtensions();
	    $ter_list = unserialize(join('', file(PATH_site.'typo3temp/ter_update_check.tmp')));

	    $content = '<table border="0" cellpadding="2" cellspacing="1">'.
	        '<tr class="bgColor5"><td></td><td>'.$LANG->getLL('tab_mod_name').'</td><td>'.$LANG->getLL('tab_mod_key').
		    '</td><td>'.$LANG->getLL('tab_mod_loc_ver').'</td><td>'.$LANG->getLL('tab_mod_rem_ver').'</td></tr>';

	    $diff = $this->pObj->MOD_SETTINGS['tx_ter_update_check_display_devupd'] ? 1 : 1000;
	    foreach($list as $name => $data) {
	        if( (t3lib_extMgm::isLoaded($name) || $this->pObj->MOD_SETTINGS['tx_ter_update_check_display_installed']) &&
		    ($data[EM_CONF][shy] == 0 || $this->pObj->MOD_SETTINGS['display_shy']) &&
		    $this->pObj->versionDifference($ter_list[$name][EM_CONF][version], $data[EM_CONF][version], $diff))
		{
	            $imgInfo = @getImageSize($this->pObj->getExtPath($name,$data['type']).'/ext_icon.gif');
		    if (is_array($imgInfo)) {
		        $icon = '<img src="'.$GLOBALS['BACK_PATH'].$this->pObj->typeRelPaths[$data['type']].$name.'/ext_icon.gif'.'" '.$imgInfo[3].' alt="" />';
		    } elseif ($extInfo['_ICON']) {
		        $icon = $extInfo['_ICON'];
		    } else {
		        $icon = '<img src="clear.gif" width="1" height="1" alt="" />';
		    }

		    $content .= '<tr class="bgColor4"><td>'.$icon.'</td>'.
			        '<td><a href="?CMD[importExtInfo]='.$ter_list[$name][extRepUid].'">'.$data[EM_CONF][title].'</a></td>'.
				'<td>'.$name.'</td>'.
			        '<td align="right">'.$data[EM_CONF][version].'</td>'.
				'<td align="right">'.$ter_list[$name][EM_CONF][version].'</td></tr>'."\n".
				'<tr class="bgColor4"><td colspan="5"><hr/></td></tr>'."\n";
		}
	    }

	    $content .= '</table><br/>';

	    return $content;
	}

	function do_update()	{
	    global $LANG;

            $repositoryUrl=$this->pObj->repositoryUrl.
                    $this->pObj->repTransferParams().
                    '&tx_extrep[cmd]=currentListing';

	    $fetchData = $this->pObj->fetchServerData($repositoryUrl);

	    $tmp = '';
	    if (is_array($fetchData)) {
		$listArr = $fetchData[0];

		list($ter_list,$ter_cat) = $this->pObj->getImportExtList($listArr);

		if(!t3lib_div::writeFileToTypo3tempDir(PATH_site.'typo3temp/ter_update_check.tmp', serialize($ter_list))) {
		    $tmp .= $LANG->getLL('msg_succ').'<br/><br/>';
		    $tmp .= '<input type="submit" value="'.$LANG->getLL('button_show_ext').'" '.
			'onclick="document.location=\'index.php?SET[function]=tx_ter_update_check_modfunc1\';return false;" />';
		} else {
		    $tmp .= sprintf($LANG->getLL('msg_fail1'), PATH_site.'typo3temp/ter_update_check.tmp');
		}
	    } else {
	        $tmp .= $LANG->getLL('msg_fail2');
	    }

	    return $this->pObj->doc->section($LANG->getLL('header_vers_ret'), $tmp, 0, 1);
	}

}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/ter_update_check/modfunc1/class.tx_ter_update_check_modfunc1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/ter_update_check/modfunc1/class.tx_ter_update_check_modfunc1.php"]);
}

?>
