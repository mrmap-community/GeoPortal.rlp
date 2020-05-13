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

class tx_q4usearch_pi4 extends tslib_pibase {
	var $prefixId = 'tx_q4usearch_pi4';		// Same as class name
	var $scriptRelPath = 'pi4/class.tx_q4usearch_pi4.php';	// Path to this script relative to the extension dir.
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

		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		if(t3lib_div::GPvar('L')==1) {
			$Lang='en';
		} else {
			$Lang='de';
		}

		// TagClouds
		$i=1;
		foreach(array('topicCategories','keywords') as $type) {
			$url='http://localhost/mapbender/php/tagCloud.php?type='.$type.'&scale=linear&maxObjects=25&maxFontSize=30&languageCode='.$Lang.'&outputFormat=json&hostName='.$_SERVER['HTTP_HOST'];
			$DATA=json_decode(file_get_contents($url));
			$content.='
				<div class="tab'.$i.'">';
			if(is_array($DATA->tagCloud->tags)) {
				foreach($DATA->tagCloud->tags as $tag) {
					$content.='
					<a href="'.$tag->url.'" title="'.$tag->title.'" style="font-size:'.$tag->weight.'px">'.$tag->title.'</a>';
				}
			}
			$content.='
				</div>';
			$i++;
		}
		
		//Bilder
		$url='http://localhost/mapbender/geoportal/mod_initialStartWmc.php?languageCode='.$Lang.'&outputFormat=json&hostName='.$_SERVER['HTTP_HOST'];
		if ($Lang == 'de') {
			$searchUrlWMC="http://".$_SERVER['HTTP_HOST']."/portal/nc/servicebereich/erweiterte-suche.html?cat=dienste&searchResources=wmc&orderBy=rank&languageCode=de";
		}
		if ($Lang == 'en') {
			$searchUrlWMC="http://".$_SERVER['HTTP_HOST']."/portal/nc/en/servicebereich/erweiterte-suche.html?cat=dienste&languageCode=en&searchResources=wmc";
		}

		$DATA=json_decode(file_get_contents($url));

		$content.='
			<div class="tab'.$i.'">';
		if(is_array($DATA->initialWmcDocs)) {
			foreach($DATA->initialWmcDocs as $tag) {
				$content.='
				<div class="wmcdocs">
					<a href="'.$tag->loadUrl.'" title="'.$tag->abstract.'"><img src="'.$tag->previewUrl.'" width="200"/></a>
					<div class="title">
						'.$tag->title.'
						<a href="'.$tag->metadataUrl.'" target="_blank" class="right"><img src="/fileadmin/design/wmcdocs_icon.png" alt="metadata"/></a>
					</div>
				</div>';
			}
		}
		$content.='
			<div class="wmcdoc"><div class="title"><a href="'.$searchUrlWMC.'">'.$this->pi_getLL('furtherWmcResults').'</a></div></div>
			<div class="clr"></div></div>';
		$i++;

		
		$content='
		<ul class="tabs">
			<li class="tab1">'.$this->pi_getLL('topicCategories').'</li>
			<li class="tab2">'.$this->pi_getLL('keywords').'</li>
			<li class="tab3">'.$this->pi_getLL('situation').'</li>
		</ul>
		<div class="panes">
		'.$content.'
		</div>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				jQuery(".panes").children("div").css("display", "none");
				jQuery(".tabs li").click(function(){
					jQuery(".panes").children("div").css("display", "none");
					jQuery(".tabs li").removeClass("active");
					jQuery(".panes div."+this.className).css("display", "block");
					jQuery(this).addClass("active");
				});

				jQuery(".panes div.tab3").css("display", "block");
				jQuery(".tabs li.tab3").addClass("active");
			});
		</script>
		';

		return $this->pi_wrapInBaseClass('<div class="news-latest-container">'.$content.'</div>');
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/q4u_search/pi4/class.tx_q4usearch_pi4.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/q4u_search/pi4/class.tx_q4usearch_pi4.php']);
}

?>
