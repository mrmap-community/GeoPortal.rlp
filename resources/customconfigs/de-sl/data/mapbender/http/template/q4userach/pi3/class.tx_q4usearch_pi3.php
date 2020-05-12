<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Andreas Kapp <ak@q4u.de>
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
 * Plugin 'Q4U Search' for the 'q4u_search' extension.
 *
 * @author	Andreas Kapp <ak@q4u.de>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_q4usearch_pi3 extends tslib_pibase {
	var $prefixId = 'tx_q4usearch_pi3';		// Same as class name
	var $scriptRelPath = 'pi3/class.tx_q4usearch_pi3.php';	// Path to this script relative to the extension dir.
	var $extKey = 'q4u_search';	// The extension key.
	var $pi_checkCHash = TRUE;
	var $file;

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
#		global $output;

		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_initPIflexForm();

		$this->file=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'path' , 'settings');
		$this->url=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'url' , 'settings');
		$this->max=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'count' , 'settings');
		$this->detail=$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'detail' , 'settings');
		$abstract='';

		if(file_exists($this->file) && filesize($this->file)>0) {
			include(dirname(__FILE__).'/../../../../fileadmin/function/config.php');
			if(t3lib_div::GPvar('L')==1) {
				$Lang='en';
			} else {
				$Lang='de';
			}
			$L=$Language[$Lang];

			$output.='<a href="'.$L['KarteURL'].'?GEORSS='.urlencode($this->url).'"><img src="fileadmin/design/icn_georss_22.png" alt="GeoRSS" title="GeoRSS" /></a>';
			try {
				$xmlObject = @simplexml_load_file($this->file);
				if($xmlObject) {
					$i=0;
					foreach($xmlObject->channel->item AS $item) {
						if($i>=$this->max && $this->max>0) break;
						preg_match('/^(NEW WMS: |NEW LAYER: |UPDATED WMS: |DELETED LAYER: |DELETED WMS: )?(.*)$/',$item->title,$matches);
						$text=$icon="";
						if(count($matches)>2) {
							switch($matches[1]) {
								case 'NEW WMS: ':
									$title=$L['NewWmsServer'];
									$icon='<img src="fileadmin/design/icn_new_server_map.png" alt="'.$L['NewWmsServer'].'" title="'.$L['NewWmsServer'].'" />';
									break;
								case 'NEW LAYER: ':
									$title=$L['NewWmsLayer'];
									$icon='<img src="fileadmin/design/icn_new_layer.png" alt="'.$L['NewWmsLayer'].'" title="'.$L['NewWmsLayer'].'" />';
									break;
								case 'UPDATED WMS: ':
									$title=$L['UpdatedWmsServer'];
									$icon='<img src="fileadmin/design/icn_update.png" alt="'.$L['UpdatedWmsServer'].'" title="'.$L['UpdatedWmsServer'].'" />';
									break;
								case 'DELETED LAYER: ':
									$title=$L['DeletedWmsLayer'];
									$icon='<img src="fileadmin/design/icn_new_server_map.png" alt="'.$L['DeletedWmsLayer'].'" title="'.$L['DeletedWmsLayer'].'" />';
									break;
								case 'DELETED WMS: ':
									$title=$L['DeletedWmsServer'];
									$icon='<img src="fileadmin/design/icn_delete_server_map.png" alt="'.$L['DeletedWmsServer'].'" title="'.$L['DeletedWmsServer'].'" />';
									break;
		
							}
							$text=$matches[2];
						} else {
							$text=$item->title;
						}
						if($this->detail==1) $abstract='<p>'.$item->description.'</p>';
						$datum='';
						if($item->pubDate!='') {
							if($ts=strtotime($item->pubDate)) {
								$datum=date('d.m.Y',$ts);
							}
						}

						$output.='
							<div class="news-latest-item">
							<p class="news-latest-date">'.$icon." ".$title."<br>".$datum.'</p>
							<h3><a title="'.$text.'" href="'.$item->link.'" onclick="window.open(this.href, \'Information\', \'width=500,height=600,left=100,top=100,scrollbars=yes,resizable=no\');return false;">'.$text.'</a></h3>
							'.$abstract.'
							</div>';
						$i++;
					}
				}
			}
			catch(Exception $e){};
		}
		return $this->pi_wrapInBaseClass('<div class="news-latest-container">'.$output.'</div>');
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/q4u_search/pi3/class.tx_q4usearch_pi3.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/q4u_search/pi3/class.tx_q4usearch_pi3.php']);
}

?>
