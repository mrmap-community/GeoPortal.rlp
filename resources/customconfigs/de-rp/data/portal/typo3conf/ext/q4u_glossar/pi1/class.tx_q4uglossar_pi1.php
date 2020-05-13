<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Andreas Kapp (andreas.kapp@q4u.de)
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
 * Plugin '' for the 'q4u_glossar' extension.
 *
 * @author	Andreas Kapp <andreas.kapp@q4u.de>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_q4uglossar_pi1 extends tslib_pibase {
	var $prefixId = 'tx_q4uglossar_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_q4uglossar_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'q4u_glossar';	// The extension key.
	var $pi_checkCHash = TRUE;

	/**
	 * [Put your description here]
	 */
	function main($content,$conf)	{
	}

	function parse($content,$conf)	{
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
	        if($GLOBALS["TSFE"]->page["tx_q4uglossar_glossar"]==0 && $word[$i]!="") $words.=$word[$i]." ";
				}
				$i++;
			}
			if($GLOBALS["TSFE"]->page["tx_q4uglossar_glossar"]==0) $output=Glossar($words);

			return $output;
	}
}

function Glossar($content) {
	include_once("fileadmin/function/function.php");
	include_once("fileadmin/function/util.php");
	mb_internal_encoding("UTF-8");

	$timestamp=time();
	$lexikon_search_id=array(20);
	$content=mb_strtolower($content);
	$db=new DB_MYSQL;

	if($_SESSION["Glossar"]=="nein") return;

	$language=(int)t3lib_div::GPvar('L');

	$sql="SELECT *
	        FROM tt_news
	       WHERE deleted=0 AND hidden=0 AND $timestamp>=starttime AND ($timestamp<=endtime or endtime=0) AND pid IN (".implode(",",$lexikon_search_id).")  AND sys_language_uid IN (-1,".$language.")
	    ORDER BY title";

  $i=0;
  $output="";

  $db->query($sql);
  $content_array=str_word_count($content, 1);
	while($db->next_record()) {
		if (array_search(mb_strtolower(trim($db->f("title"))) ,$content_array) !== false) {
			$Lexikon[$i]["id"]=$db->f("uid");
			$Lexikon[$i]["Title"]=$db->f("title");
			$Lexikon[$i]["Bodytext"]=$db->f("bodytext");
			$i++;
		}
	}

	if($language==1) $pathadd="en/";

	if(count($Lexikon) > 0) {
		usort($Lexikon, "Glossar_Sort");
		$output=
		  "<div id=\"glossar\">\n".
		  "<h2><a href=\"servicebereich/glossar.html\">Glossar</a></h2>\n<dl>\n";


		for($i=0;$i<count($Lexikon);$i++) {
			$letter=mb_strtoupper(mb_substr($Lexikon[$i]["Title"], 0, 1));
			if($letter=="Ä") $letter="A";
			if($letter=="Ö") $letter="O";
			if($letter=="Ü") $letter="U";
			$letter=ord($letter);

			$output.=
	"<dt><a href=\"".$pathadd."servicebereich/glossar.html?tx_lexicon[letter]=".$letter."#g_".$Lexikon[$i]["id"]."\">".$Lexikon[$i]["Title"]."</a></dt>\n".
	"<dd>".textcut($Lexikon[$i]["Bodytext"],100)."</dd>\n";
		}
		$output.="</dl>\n</div>\n";
	}

	return $output;
}

function Glossar_Sort($a, $b) {
	$Sorta=mb_strtoupper($a['Title']);
	$Sortb=mb_strtoupper($b['Title']);
	if(mb_substr($Sorta, 0, 1)=="Ä") {
		$Sorta="A".mb_substr($Sorta, 1);
	}
	if(mb_substr($Sortb, 0, 1)=="Ä") {
		$Sortb="A".mb_substr($Sortb, 1);
	}

	if(mb_substr($Sorta, 0, 1)=="Ö") {
		$Sorta="O".mb_substr($Sorta, 1);
	}
	if(mb_substr($Sortb, 0, 1)=="Ö") {
		$Sortb="O".mb_substr($Sortb, 1);
	}

	if(mb_substr($Sorta, 0, 1)=="Ü") {
		$Sorta="U".mb_substr($Sorta, 1);
	}
	if(mb_substr($Sortb, 0, 1)=="Ü") {
		$Sortb="U".mb_substr($Sortb, 1);
	}

	if ($Sorta == $Sortb) {
		return 0;
	}
	return ($Sorta > $Sortb) ? 1 : -1;
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/q4u_glossar/pi1/class.tx_q4uglossar_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/q4u_glossar/pi1/class.tx_q4uglossar_pi1.php']);
}

?>