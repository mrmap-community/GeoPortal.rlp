<?PHP
GLOBAL $news_search, $news_cats, $news_show;
GLOBAL $glossar_search, $glossar_show;
GLOBAL $timestamp, $search, $db, $db2, $contentresults, $newsresults, $result, $crumb, $language, $sword;

$sql="SELECT uid, pid, title, datetime, short, bodytext, author
        FROM tt_news
       WHERE deleted=0 AND hidden=0 AND $timestamp>=starttime AND ($timestamp<=endtime or endtime=0)
         AND pid IN (".implode(",",$news_search[$language]).",".implode(",",$glossar_search).")
    ORDER BY datetime desc";

$sql="SELECT uid, pid, title, datetime, short, bodytext, author
        FROM tt_news
       WHERE deleted=0 AND hidden=0 AND $timestamp>=starttime AND ($timestamp<=endtime or endtime=0)
         AND ( pid IN (".implode(",",$news_search[$language]).")
          OR  (pid IN (".implode(",",$glossar_search).") AND sys_language_uid=".$language."))
    ORDER BY datetime desc";

#print $sql;

$db->query($sql);

// Krümelspur
if($language==0) {
	$ttnews_crumb="<a href=\"".realurl_link(1,false,$language)."\">GeoPortal.rlp</a> &gt; <a href=\"".realurl_link(8,false,$language)."\">Aktuelles</a> &gt; ";
	$ttglossar_crump="<a href=\"".realurl_link(1,false,$language)."\">GeoPortal.rlp</a> &gt; <a href=\"".realurl_link(10,false,$language)."\">Glossar</a> &gt; ";
} else {
	$ttnews_crumb="<a href=\"".realurl_link(1,false,$language)."\">GeoPortal.rlp</a> &gt; <a href=\"".realurl_link(8,false,$language)."\">News</a> &gt; ";
	$ttglossar_crump="<a href=\"".realurl_link(1,false,$language)."\">GeoPortal.rlp</a> &gt; <a href=\"".realurl_link(10,false,$language)."\">Glossary</a> &gt; ";
}

$wochentag=array("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");
$monat=array("Januar","Februar","März","April","Mai","Juni","Juli","August","September","Oktober","November","Dezember");

if($db->num_rows()) {
  while($db->next_record()) {
  	$uid=$db->f("uid");
  	$pid=$db->f("pid");
   	$title=$db->f("title");
   	$datetime=$db->f("datetime");
   	$short=$db->f("short");
   	$bodytext=$db->f("bodytext");
   	$author=$db->f("author");

		$Suchdatum=date(' j.n.Y j.n.y d.m.y d.m.Y ',$datetime).$wochentag[date('w',$datetime)]." ".$monat[date('n',$datetime)-1];

   	$text=$title." ".$short." ".$bodytext." ".$author." ".$Suchdatum." ".$facts." ".$contact." ".$city;
   	$found=search_text2($text,1);
		if($found>0) {
			$result[$contentresults]['FOUND']=$found;

	  	if($short!="") {
        $shorttext=textcut($short);
	  	} else {
	  		$shorttext=textcut($bodytext);
	  	}

      $tag=$wochentag[date("w",$datetime)].date(", d.m.y",$datetime);

      $result[$contentresults]['SHORTTEXT']=$shorttext;
      $result[$contentresults]['DATETIME']=$datetime;
      $result[$contentresults]['UID']=$uid;
      $result[$contentresults]['TITLE']=$title;

      if(in_array($pid,$news_search[$language])) {
      	$url=($language==0)?$url="id=".$news_show."&tx_ttnews[tt_news]=".$uid:$url="id=".$news_show."&L=".$language."&tx_ttnews[tt_news]=".$uid;
				$url=realurl_news($url,true,$language);
				$result[$contentresults]['URL']=$url.$sword;

      	$result[$contentresults]['TYP']="NEWS";
      	$crumb[$uid]=$ttnews_crumb."<a href=\"".$url."\">".$title."</a>";
    	} elseif(in_array($pid,$glossar_search)) {
				$url=realurl_link($glossar_show,true,$language);
				$result[$contentresults]['URL']=$url.$sword.'&amp;tx_lexicon[id]='.$uid;

    		$result[$contentresults]['TYP']="GLOSSAR";
    		$crumb[$uid]=$ttglossar_crump."<a href=\"".$url.'tx_lexicon[id]='.$uid."\">".$title."</a>";
    	}

      $contentresults++;
   	}
	}
}
?>
