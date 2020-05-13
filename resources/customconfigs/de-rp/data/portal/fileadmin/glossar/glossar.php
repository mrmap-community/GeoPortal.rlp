<?php
include_once("fileadmin/function/util.php");
include_once("fileadmin/function/function.php");
include_once("config.php");

print "PARSE";

mb_internal_encoding("UTF-8");

global $db, $db2, $timestamp, $language;

$timestamp=time();
$search=$_REQUEST["searchtext"];

$db=new DB_MYSQL;
$db2=new DB_MYSQL;

$language=(int)t3lib_div::GPvar('L');

Lexikon_Menu();
if($search) {
	Lexikon_Search();
} else {
	Lexikon_Data();
}

function Lexikon_Menu() {
	global $lexikon_search_id, $db, $timestamp, $url, $language;

	$search=$_REQUEST["searchtext"];
	$tx_lexicon=$_REQUEST["tx_lexicon"];
	$letter=$tx_lexicon["letter"];
	$cat=$tx_lexicon["cat"];
	$id=$tx_lexicon["id"];

	$firstletter=($cat!="" || $search!="" || $id!="")?1:0;
	if($letter!="" && $search=="") $firstletter=$letter;

	print '<ul class="search-cat glossar-cat">';

	for($i=ord("A");$i<=ord("Z");$i++) {
		$sql="SELECT title
		        FROM tt_news
           WHERE (title LIKE \"".chr($i)."%\" OR title LIKE \"A%\") AND deleted=0 AND hidden=0 AND $timestamp>=starttime AND ($timestamp<=endtime or endtime=0) AND pid IN ('".implode("','",$lexikon_search_id)."') AND sys_language_uid IN (-1,".$language.")";

		$db->query($sql);

		$j=0;

		if($db->num_rows()) {
			while($db->next_record()) {
				if(chr($i)=="A" && mb_strtoupper(mb_substr($db->f("title"), 0, 1))!="A" && mb_strtoupper(mb_substr($db->f("title"), 0, 1))!="Ä") {
					continue;
				}
				if(chr($i)=="O" && mb_strtoupper(mb_substr($db->f("title"), 0, 1))!="O" && mb_strtoupper(mb_substr($db->f("title"), 0, 1))!="Ö") {
					continue;
				}
				if(chr($i)=="U" && mb_strtoupper(mb_substr($db->f("title"), 0, 1))!="U" && mb_strtoupper(mb_substr($db->f("title"), 0, 1))!="Ü") {
					continue;
				}
				if(chr($i)!="A" && chr($i)!="O" && chr($i)!="U" && mb_strtoupper(mb_substr($db->f("title"), 0, 1))!=chr($i)) {
					continue;
				}

				$letter[$i]=1;
				if($firstletter==0) { $firstletter=$i; }

				if($firstletter==$i) {
					if($i==ord("A")) print "<li class=\"active first\"><a href=\"".$url."?tx_lexicon[letter]=$i\">".chr($i)."</a></li>";
					else print "<li class=\"active $class\"><a href=\"".$url."?tx_lexicon[letter]=$i\">".chr($i)."</a></li>";
				} else {
					if($i==ord("A")) print "<li class=\"$class\"><a href=\"".$url."?tx_lexicon[letter]=$i\">".chr($i)."</a></li>";
					else  print "<li><a href=\"".$url."?tx_lexicon[letter]=$i\">".chr($i)."</a></li>";
				}
				$j=1;
				break;
			}
		}
		if($j==0) {
			print "<li class=\"empty\">".chr($i)."</li>";
		}

	}
	print '</ul>';
	print '<div class="clearer"></div>';
	print '<div class="glossar-container">';
	$_REQUEST["tx_lexicon"]["letter"]=$firstletter;
}

function Lexikon_Data() {
	global $lexikon_search_id, $db, $timestamp, $language;

	$tx_lexicon=$_REQUEST["tx_lexicon"];
	$letter=$tx_lexicon["letter"];
	$cat=$tx_lexicon["cat"];
	$id=$tx_lexicon["id"];

#	if(!$letter && !$cat && !id) return;
	$headline=chr($letter);

	if($cat) {
		$sql="SELECT title
		        FROM tt_news_cat
		       WHERE uid='".$db->v($cat)."' AND deleted=0 AND hidden=0 AND $timestamp>=starttime AND ($timestamp<=endtime or endtime=0)";
		$db->query($sql);
		if($db->next_record()) {
			$headline=$db->f("title");
			$sql="SELECT tt_news.uid, tt_news.pid, tt_news.title, tt_news.datetime, tt_news.short, tt_news.bodytext, tt_news.author, tt_news.image, tt_news.tstamp
			        FROM tt_news INNER JOIN tt_news_cat_mm ON tt_news.uid=tt_news_cat_mm.uid_local
			       WHERE tt_news_cat_mm.uid_foreign='".$db->v($cat)."' AND tt_news.deleted=0 AND tt_news.hidden=0 AND $timestamp>=tt_news.starttime AND ($timestamp<=tt_news.endtime or tt_news.endtime=0) AND tt_news.pid IN ('".implode("','",$lexikon_search_id)."') AND tt_news.sys_language_uid IN (-1,".$language.")";
		}
		// print '<h2>'.$headline.'</h2>';
	} elseif($id) {
		$sql="SELECT uid, pid, title, datetime, short, bodytext, author, image, tstamp
		        FROM tt_news
		       WHERE uid='".$db->v($id)."' AND deleted=0 AND hidden=0 AND $timestamp>=starttime AND ($timestamp<=endtime or endtime=0) AND pid IN ('".implode("','",$lexikon_search_id)."') AND sys_language_uid IN (-1,".$language.")";
	} else {
		$sql="SELECT uid, pid, title, datetime, short, bodytext, author, image, tstamp
		        FROM tt_news
		       WHERE (title LIKE \"".$headline."%\" OR title LIKE \"A%\") AND deleted=0 AND hidden=0 AND $timestamp>=starttime AND ($timestamp<=endtime or endtime=0) AND pid IN (".implode(",",$lexikon_search_id).") AND sys_language_uid IN (-1,".$language.")";
		// print '<h2>'.$headline.'</h2>';
	}

	$i=0;
	$db->query($sql);
	while($db->next_record()) {
		$uid=$db->f("uid");
		$pid=$db->f("pid");
		$title=$db->f("title");
		$datetime=$db->f("datetime");
		$short=$db->f("short");
		$bodytext=$db->f("bodytext");
		$author=$db->f("author");
		$image=$db->f("image");
		$tstamp=$db->f("tstamp");

		if($letter!=1 && chr($letter)=="A" && mb_strtoupper(mb_substr($title, 0, 1))!=chr($letter) && mb_strtoupper(mb_substr($title, 0, 1))!="Ä") {
			continue;
		}
		if($letter!=1 && chr($letter)=="O" && mb_strtoupper(mb_substr($title, 0, 1))!=chr($letter) && mb_strtoupper(mb_substr($title, 0, 1))!="Ö") {
			continue;
		}
		if($letter!=1 && chr($letter)=="U" && mb_strtoupper(mb_substr($title, 0, 1))!=chr($letter) && mb_strtoupper(mb_substr($title, 0, 1))!="Ü") {
			continue;
		}

		if($letter!=1 && chr($letter)!="A" && chr($letter)!="O" && chr($letter)!="U" && mb_strtoupper(mb_substr($title, 0, 1))!=chr($letter)) {
			continue;
		}

		$rubrik=Lexikon_Rubrik($uid);

		$Lexikon[$i]["uid"]=$uid;
		$Lexikon[$i]["Title"]=$title;
		$Lexikon[$i]["Rubrik"]=$rubrik;
		$Lexikon[$i]["Bodytext"]=$bodytext;
		$i++;
	}

	if(count($Lexikon) > 0) {
		usort($Lexikon, "lexikon_sort");
	}

	for($i=0;$i<count($Lexikon);$i++) {
print
'<dl>'.
'<dt id="g_'.$Lexikon[$i]["uid"].'">'.$Lexikon[$i]["Title"].'</dt>'.
'<dd>'.$Lexikon[$i]["Bodytext"];

		$sql="SELECT tt_news.uid, tt_news.title
		        FROM tt_news INNER JOIN tt_news_related_mm ON tt_news.uid=tt_news_related_mm.uid_foreign
			     WHERE tt_news_related_mm.uid_local=".$Lexikon[$i]["uid"]." AND tt_news.deleted=0 AND tt_news.hidden=0 AND $timestamp>=tt_news.starttime AND ($timestamp<=tt_news.endtime or tt_news.endtime=0) AND tt_news.pid IN (".implode(",",$lexikon_search_id).") AND tt_news.sys_language_uid IN (-1,".$language.")";

		$db->query($sql);
		if($db->num_rows()) {
			$related="";
			print "<div class=\"glossar-related\">";
			while($db->next_record()) {
				$letter=mb_strtoupper(mb_substr($db->f("title"), 0, 1));
				if($letter=="Ä") $letter="A";
				if($letter=="Ö") $letter="O";
				if($letter=="Ü") $letter="U";
				$letter=ord($letter);

				if($related=="") {
					$related="<a href=\"servicebereich/glossar.html?tx_lexicon[letter]=".$letter."#g_".$db->f("uid")."\">".$db->f("title")."</a>";
				} else {
					$related.=", <a href=\"servicebereich/glossar.html?tx_lexicon[letter]=".$letter."#g_".$db->f("uid")."\">".$db->f("title")."</a>";
				}
			}
			print "Siehe auch: $related\n";
			print "</div>";
		}

print "</dd></dl>\n";
	}
}

function lexikon_sort($a, $b) {
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

function Lexikon_Search() {
	global $timestamp, $search, $db, $db2, $lexikon_search_id, $sword, $language;

	mb_internal_encoding("UTF-8");
	count_search(strip_tags(mb_strtolower($_REQUEST['searchtext'])));
	usort($search, "cmp_search");

	$sql="SELECT uid, pid, title, datetime, short, bodytext, author
	        FROM tt_news
	       WHERE deleted=0 AND hidden=0 AND $timestamp>=starttime AND ($timestamp<=endtime or endtime=0) AND pid IN (".implode(",",$lexikon_search_id).")  AND tt_news.sys_language_uid IN (-1,".$language.")
	    ORDER BY title";

	$db->query($sql);

	$contentresults=0;

	if($db->num_rows()) {
	  while($db->next_record()) {
	  	$uid=$db->f("uid");
	  	$pid=$db->f("pid");
	   	$title=$db->f("title");
	   	$datetime=$db->f("datetime");
	   	$short=$db->f("short");
	   	$bodytext=$db->f("bodytext");
	   	$author=$db->f("author");

	   	$text=$title." ".$bodytext;

#	   	$found=search_text($text);
	    $found=search_text2($title,100,$bodytext,1);
			if($found>0) {
				$rubrik=Lexikon_Rubrik($uid);

				$result[$contentresults]['FOUND']=$found;
				$result[$contentresults]['TITLE']=$title;
				$result[$contentresults]['RUBRIK']=$rubrik;
				$result[$contentresults]['BODYTEXT']=$bodytext;
				$contentresults++;
			}
		}
		usort($result, "cmp_result");
		for($i=0;$i<$contentresults;$i++) {

print
'<dl class="glossar-single-item">'.
'<dt class="glossar-single-title">'.$result[$i]['TITLE'].'</dt>'.
'<dd class="glossar-single-text">'.$result[$i]['BODYTEXT'].'</dd>'.
'</dl>';
		}
	}
}

function Lexikon_Rubrik($id) {
	global $db2;

	$timestamp=time();

	$sql="SELECT *, tt_news_cat.uid AS CatID
	        FROM tt_news_cat_mm, tt_news_cat
	       WHERE tt_news_cat_mm.uid_local='".$id."' AND tt_news_cat_mm.uid_foreign=tt_news_cat.uid AND tt_news_cat.deleted=0 AND tt_news_cat.hidden=0 AND $timestamp>=tt_news_cat.starttime AND ($timestamp<=tt_news_cat.endtime or tt_news_cat.endtime=0)
	    ORDER BY tt_news_cat.title";

	$db2->query($sql);
	while($db2->next_record()) {
		$title='<a href="'.$_SERVER['REDIRECT_URL'].'?tx_lexicon[cat]='.$db2->f("CatID").'">'.$db2->f("title").'</a>';
		if(!$i) {
			$rubrik=$title;
			$i=1;
		}	else {
			$rubrik.=", ".$title;
		}
	}
	return($rubrik);
}

function cmp_result($a, $b) {
	return ($a['FOUND'] > $b['FOUND']) ? -1 : 1;
}
?>
</div>