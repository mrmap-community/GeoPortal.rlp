<?PHP
# ö
function find_page($pid) {
	global $timestamp, $result, $contentresults, $search, $db, $crumb, $nosearchid, $loginsearchid, $language;

	$ids=array();
	$id=0;

	if($language!=0) {
		$sql="SELECT pages.uid, pages.pid, pages_language_overlay.title, pages.no_search, pages.doktype, tt_content.header, tt_content.subheader, tt_content.bodytext, tt_content.records, tt_content.CType
		        FROM pages
		        INNER JOIN pages_language_overlay ON (pages_language_overlay.pid=pages.uid)
		        LEFT OUTER JOIN tt_content ON ( pages.uid=tt_content.pid AND
		        	                                       ((tt_content.deleted=0 AND tt_content.hidden=0 AND (tt_content.CType='text' OR tt_content.CType='textpic' OR tt_content.CType='shortcut') AND
		                                                    $timestamp>=tt_content.starttime AND ($timestamp<=tt_content.endtime or tt_content.endtime=0)) OR tt_content.pid IS NULL))
		       WHERE pages.pid IN ($pid) AND pages.deleted=0 AND pages.hidden=0 AND $timestamp>=pages.starttime AND ($timestamp<=pages.endtime or pages.endtime=0) AND pages_language_overlay.sys_language_uid=".$language." AND tt_content.sys_language_uid=".$language."
		    ORDER BY pages.uid, tt_content.sorting";
	} else {
		$sql="SELECT pages.uid, pages.pid, pages.title, pages.no_search, pages.doktype, tt_content.header, tt_content.subheader, tt_content.bodytext, tt_content.records, tt_content.CType
		        FROM pages LEFT OUTER JOIN tt_content ON ( pages.uid=tt_content.pid AND
		        	                                       ((tt_content.deleted=0 AND tt_content.hidden=0 AND (tt_content.CType='text' OR tt_content.CType='textpic' OR tt_content.CType='shortcut') AND
		                                                    $timestamp>=tt_content.starttime AND ($timestamp<=tt_content.endtime or tt_content.endtime=0)) OR tt_content.pid IS NULL))
		       WHERE pages.pid IN ($pid) AND pages.deleted=0 AND pages.hidden=0 AND $timestamp>=pages.starttime AND ($timestamp<=pages.endtime or pages.endtime=0) AND tt_content.sys_language_uid=0
		    ORDER BY pages.uid, tt_content.sorting";
	}

	$db->query($sql);

	if($db->num_rows()) {
	  while($db->next_record()) {
	  	$uid=$db->f("uid");
	  	$pid=$db->f("pid");
	   	$title=$db->f("title");
	   	$no_search=$db->f("no_search");
	   	$doktype=$db->f("doktype");
	   	$header=$db->f("header");
	   	$bodytext=$db->f("bodytext");
	   	$subheader=$db->f("subheader");

	   	$CType=$db->f("CType");
	   	$records=$db->f("records");

// Nicht zu durchsuchende Zweige
	   	for($i=0;$i<count($nosearchid);$i++) {
	   		if($uid==$nosearchid[$i]) {
	   			continue 2;
	   		}
	   	}

// Nach Login zu durchsuchende Zweige
	   	for($i=0;$i<count($loginsearchid);$i++) {
	   		if($uid==$loginsearchid[$i] && $GLOBALS["TSFE"]->fe_user->user["username"]=="") {
	   			continue 2;
	   		}
	   	}

	   	$ids[]=$uid;

			if($crumb[$pid]=="") {
				$crumb[$uid]="<a href=\"".realurl_link(1,false,$language)."\">GeoPortal.rlp</a> &gt; <a href='".realurl_link($uid,false,$language)."'>$title</a>";
			} else {
			  $crumb[$uid]=$crumb[$pid]." &gt; "."<a href='".realurl_link($uid,false,$language)."'>$title</a>";
			}

			if($doktype==4 || $doktype==254) {
			} else {
		   	if($no_search!=0) { continue; }

		   	if($id!=$uid) {
		   		if($id!=0) {
#		   			$found=search_text($text);
		   			$found=search_text2($text,1);
		   			if($found>0) {
	        		$result[$contentresults]['FOUND']=$found;
	        		$contentresults++;
				   	} else {
				   		unset($result[$contentresults]);
				  	}
		   		}

		   		$result[$contentresults]['UID']=$uid;
		   		$result[$contentresults]['TITLE']=$title;
		   		$result[$contentresults]['TYP']="CONTENT";

		   		$text=$title;
		   		$id=$uid;
		   		$hitlength=0;
		  	}

		  	if($CType=="shortcut") {
		  		if($records!="") {$text.=" ".search_record($records); }
		  	} else {
		  		$text.=" ".$header." ".$subheader." ".$bodytext;

		  		if($hitlength==0 && $bodytext!="") {
		  			$shorttext=textcut($result[$contentresults]['SHORTTEXT']." ".$bodytext);
		  			$result[$contentresults]['SHORTTEXT']=$shorttext;
		  		}
		  	}
		  }
		}

#	$found=search_text($text);
	$found=search_text2($text,1);
	if($found>0) {
		$result[$contentresults]['FOUND']=$found;
		$contentresults++;
 	} else {
 		unset($result[$contentresults]);
	}
	find_page(implode(",",$ids));
	}
}

function search_record($ids) {
	global $timestamp;

	$db2=new DB_MYSQL;

	$record=split(",",$ids);

	$ids=str_replace("tt_content_", "", $ids);

	$sql="SELECT uid, header, subheader, bodytext, CType, records
	        FROM tt_content
	       WHERE uid IN ($ids) AND deleted=0 AND hidden=0 AND (CType='text' OR CType='textpic' OR CType='shortcut') AND $timestamp>=starttime AND ($timestamp<=endtime or endtime=0)";

	$db2->query($sql);

	if($db2->num_rows()) {
	  while($db2->next_record()) {
	  	$uid=$db2->f("uid");

	   	$header=$db2->f("header");
	   	$subheader=$db2->f("subheader");
	   	$bodytext=$db2->f("bodytext");

	   	$CType=$db2->f("CType");
	   	$records=$db2->f("records");

	   	if($CType=="shortcut") {
	  		$recordtext=$recordtext." ".search_record($records);
	  	} else {
				$recordtext=$recordtext." ".$header." ".$subheader." ".$bodytext;
			}
		}
	}
	return($recordtext);
}
?>
