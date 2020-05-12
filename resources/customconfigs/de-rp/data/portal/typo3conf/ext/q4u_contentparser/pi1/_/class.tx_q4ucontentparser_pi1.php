<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Michael Spitz (michael.spitz@q4u.de)
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
 * Plugin '' for the 'q4u_contentparser' extension.
 *
 * @author	Michael Spitz <michael.spitz@q4u.de>
 * ToDo: 
 * - Verarbeitung Innerhalb von JavaScripts aussetzen
 * - Größenanpassungen auch auf andere Objekte anwenden (prüfen, ob bereits ein Style enthalten ist,...)
 * - Different-Linklayout
 * - Zwischenseite für externe Links
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_q4ucontentparser_pi1 extends tslib_pibase {
	var $prefixId = 'tx_q4ucontentparser_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_q4ucontentparser_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'q4u_contentparser';	// The extension key.
	var $pi_checkCHash = TRUE;
	

	/**
	 * [Put your description here]
	 */
	function main($content,$conf)	{
	}
	
	function parse($content,$conf)	{
		global $TSFE;

		$confArray = unserialize($GLOBALS["TYPO3_CONF_VARS"]["EXT"]["extConf"]["q4u_contentparser"]);
		
		// alle <br> in <br /> umwandeln
		$content=str_replace("<br>","<br />",$content);

		// Special-Highlighting
		if($confArray["specialHighlight"]==1) {
			$content=str_replace("###QHIGHLIGHT###","<span class=\"highlight\">",$content);
			$content=str_replace("###/QHIGHLIGHT###","</span>",$content);
		}


		switch ($confArray["emFactor"]) {
			case 0:
				$Faktor=0;
				break;
			case 1:
				$Faktor=12.8;
				break;
			case 2:
				$Faktor=16;
				break;
		}

		if( !($confArray["htmlCharacter"]==1 || 
					$confArray["modifyAnchor"]==1 || 
					$confArray["clearBorder"]==1 || 
					$confArray["linkLayout"]==1 || 
					$Faktor>0)) {
			$output=$content;
		} else {

	    //
			// Trenner
	    //
			$exploder = array('.',' ','-',')','(',']','[',',',';',':','!','?',"\n","\r");
	
	
			//
			// Wörter und Tags filtern
			//
			$a=0;
			for($i=0;$i<strlen($content);$i++) {
				if (strstr($content[$i],"<")) {
	
	        // handle tags
					$u=$i;
					$end[$a].=$content[$i];
	
					do {
						$i++;
						$end[$a].=$content[$i];
					}	while(($content[$i]!=">") AND ($content[($i+1)]!="<") AND ($content[$i]!=""));
	
					if ($content[$i]=="<" AND ($i>($u+1))) {						
						$a++;						
						if ($word[$a]=="") $word[$a] = '~~NO~WORD~~';
					} else {
						if ($word[$a]=="") $word[$a] = '~~NO~WORD~~';
						$a++;						
					}
	
				}
				else if (in_array($content[$i],$exploder)) {
	        // handle new word
					$end[$a].=$content[$i];
					if ($word[$a]=="") $word[$a] = '~~NO~WORD~~';
#					print $word[$a]."<br />";
					$a++;
	
				}	else {	
	        // build word
	        $word[$a] .= $content[$i];
	      }
			}
	
	    //
			// mark words
			//
			$i=0;
			while($word[$i]!="") {
	      if($word[$i] <> '~~NO~WORD~~') {
	        $word[$i] = trim($word[$i]);					
	
					if($confArray["htmlCharacter"]==1) {						
	        	$word[$i]=remap_chars($word[$i]);
	        }
#	        if($GLOBALS["TSFE"]->page["tx_q4uglossar_glossar"]==0 && $word[$i]!="") $words.=$word[$i]." ";
	  			$output .= $word[$i];
				} else {
					if($confArray["modifyAnchor"]==1) {
						if(preg_match("/^<a href=\".*?#[0-9]{1,6}/i", $end[$i])) { #q.ak001
							if(strpos($end[$i],"mailto:")===false) {
								$end[$i]=str_replace("#",$_SERVER['REDIRECT_URL']."#j_",$end[$i]);
							}
						}
					}
					if($confArray["linkLayout"]==1) {
						if(is_array($conf["parse."]) && preg_match("/^<a href=.*/i", $end[$i])) {
							for($j=1,$Do=true;($i+$j)<count($end) && $end[$i+$j]!="</a>";$j++) {
								if(preg_match("/^<img.*/i", $end[$i+$j])) {
									$Do=false;
								}
							}
							if($Do) {	
								foreach($conf["parse."] as $subst) {
									$match="/^<a href=\"(".(str_replace("/","\/",$subst["link"])).")\".*/i";
									if(preg_match($match, $end[$i])) {
										$end[$i]=$subst["before"].$end[$i];
									}
								}
							}
						}
					}

					if($confArray["clearBorder"]==1) {
						$end[$i]=preg_replace("/border=\"[0-9]{0,6}\"/","",$end[$i]);
					}
	
					if($Faktor>0 &&strtolower(substr($end[$i],0,4))=="<img") {
						$img=$end[$i];
						$width=0;
						$height=0;
						$search = "/width\s*=\s*['\"](\d+)['\"]/i";
						preg_match($search, $img, $found);
						if($found[1]>0) {
							$width=$found[1]/$Faktor;
							$search	="/".$found[0]."/i";
							$replace = '';
							$img=preg_replace($search, $replace, $img);
						}
						$search = "/height\s*=\s*['\"](\d+)['\"]/i";
						preg_match($search, $img, $found);
						if($found[1]>0) {
							$height=$found[1]/$Faktor;
							$search	="/".$found[0]."/i";
							$replace = '';
							$img=preg_replace($search, $replace, $img);
						}
						if($height>0 && $width>0) {
	//						$bild='<div style="width=\''.$breite.'em\',height=\''.$hoehe.'em\'>'.$bild.'</div>';
	//						$bild='<div style="width=\''.$breite.'em\',height=\''.$hoehe.'em\'><img style="width=\''.$breite.'em\',height=\''.$hoehe.'em\'" '.substr($bild,5).'</div>';
	//						$bild='<div style="width:'.$breite.'em;height:'.$hoehe.'em"><img style="width:'.$breite.'em;height:'.$hoehe.'em" '.substr($bild,5).'</div>';
							$img='<img style="width:'.$width.'em;height:'.$height.'em" '.substr($img,5);
							$end[$i]=$img;
						}
					}
				}
				$output .= $end[$i];
				$i++;
			}
		}
#		if($GLOBALS["TSFE"]->page["tx_q4uglossar_glossar"]==0) $output.=Glossar($words);

		return $output;
	}

	function remap_chars($html) {
		$replacements = array(
			'128' => '&euro;',
		//	'129' => '',
			'130' => '&sbquo;',
			'131' => '&fnof;',
			'132' => '&bdquo;',
			'133' => '&hellip;',
			'134' => '&dagger;',
			'135' => '&Dagger;',
			'136' => '&circ;',
			'137' => '&permil;',
			'138' => '&Scaron;',
			'139' => '&lsaquo;',
			'140' => '&OElig;',
		//	'141' => '',
			'142' => '&Zcaron;',
		//	'143' => '',
		//	'144' => '',
			'145' => '&lsquo;',
			'146' => '&rsquo;',
			'147' => '&ldquo;',
			'148' => '&rdquo;',
			'149' => '&bull;',
			'150' => '&ndash;',
			'151' => '&mdash;',
			'152' => '&tilde;',
			'153' => '&trade;',
			'154' => '&scaron;',
			'155' => '&rsaquo;',
			'156' => '&oelig;',
		//	'157' => '',
			'158' => '&zcaron;',
			'159' => '&Yuml;',
			'160' => '&nbsp;',
			'161' => '&iexcl;',
			'162' => '&cent;',
			'163' => '&pound;',
			'164' => '&curren;',
			'165' => '&yen;',
			'166' => '&brvbar;',
			'167' => '&sect;',
			'168' => '&uml;',
			'169' => '&copy;',
			'170' => '&ordf;',
			'171' => '&laquo;',
			'172' => '&not;',
			'173' => '&shy;',
			'174' => '&reg;',
			'175' => '&macr;',
			'176' => '&deg;',
			'177' => '&plusmn;',
			'178' => '&sup2;',
			'179' => '&sup3;',
			'180' => '&acute;',
			'181' => '&micro;',
			'182' => '&para;',
			'183' => '&middot;',
			'184' => '&cedil;',
			'185' => '&sup1;',
			'186' => '&ordm;',
			'187' => '&raquo;',
			'188' => '&frac14;',
			'189' => '&frac12;',
			'190' => '&frac34;',
			'191' => '&iquest;',
			'192' => '&Agrave;',
			'193' => '&Aacute;',
			'194' => '&Acirc;',
			'195' => '&Atilde;',
			'196' => '&Auml;',
			'197' => '&Aring;',
			'198' => '&AElig;',
			'199' => '&Ccedil;',
			'200' => '&Egrave;',
			'201' => '&Eacute;',
			'202' => '&Ecirc;',
			'203' => '&Euml;',
			'204' => '&Igrave;',
			'205' => '&Iacute;',
			'206' => '&Icirc;',
			'207' => '&Iuml;',
			'208' => '&ETH;',
			'209' => '&Ntilde;',
			'210' => '&Ograve;',
			'211' => '&Oacute;',
			'212' => '&Ocirc;',
			'213' => '&Otilde;',
			'214' => '&Ouml;',
			'215' => '&times;',
			'216' => '&Oslash;',
			'217' => '&Ugrave;',
			'218' => '&Uacute;',
			'219' => '&Ucirc;',
			'220' => '&Uuml;',
			'221' => '&Yacute;',
			'222' => '&THORN;',
			'223' => '&szlig;',
			'224' => '&agrave;',
			'225' => '&aacute;',
			'226' => '&acirc;',
			'227' => '&atilde;',
			'228' => '&auml;',
			'229' => '&aring;',
			'230' => '&aelig;',
			'231' => '&ccedil;',
			'232' => '&egrave;',
			'233' => '&eacute;',
			'234' => '&ecirc;',
			'235' => '&euml;',
			'236' => '&igrave;',
			'237' => '&iacute;',
			'238' => '&icirc;',
			'239' => '&iuml;',
			'240' => '&eth;',
			'241' => '&ntilde;',
			'242' => '&ograve;',
			'243' => '&oacute;',
			'244' => '&ocirc;',
			'245' => '&otilde;',
			'246' => '&ouml;',
			'247' => '&divide;',
			'248' => '&oslash;',
			'249' => '&ugrave;',
			'250' => '&uacute;',
			'251' => '&ucirc;',
			'252' => '&uuml;',
			'253' => '&yacute;',
			'254' => '&thorn;',
			'255' => '&yuml;',
		);
		foreach($replacements as $char => $entity) {
			$html = str_replace(chr($char),$entity,$html);
		}
		return $html;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/q4u_contentparser/pi1/class.tx_q4ucontentparser_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/q4u_contentparser/pi1/class.tx_q4ucontentparser_pi1.php']);
}

?>