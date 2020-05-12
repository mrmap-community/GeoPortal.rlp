<?php
function search($uid,$searchid,$cat,$page,$userid,$language,$search_info,$search_meta,$search_dienste,$search_adressen,$search_filter) {
	$ajax=true;
	$_REQUEST['uid']=$uid;
	$_REQUEST['searchid']=$searchid;
	$_REQUEST['cat']=$cat;
	$_REQUEST['page']=$page;
	$_SESSION["mb_user_id"]=$userid;

	include("start_search.php");

  $newContent=date("h:i:s");
#  $newContent.=$output;
#  $newContent.="---".$search_info."---".$search_meta."---";
#  $newContent.="---".$_REQUEST["searchtext"]."---";

	$objResponse = new xajaxResponse();

	if($search_filter=="0") {
		$objResponse->addAssign("search-filter","innerHTML", $out["filter"]);
		$Content.="search_filter=".$ready["filter"]."; ";
	}
	if($search_adressen=="0") {
		$newContent.="---Adressen suche---";
		$objResponse->addAssign("search-container-adressen","innerHTML", $out["adressen"]);
		$objResponse->addAssign("search-filter-adr","innerHTML", $out["adressen-filter"]);
		$Content.="search_adressen=".$ready["adressen"]."; ";
		if($ready["adressen"]==1 && $cat=="") $objResponse->addAssign("search-header-indicator-adressen","innerHTML", "");
		$newContent.="--Count:".count($check_array)."---";
	}
	if($search_dienste=="0") {
		$objResponse->addAssign("search-container-dienste","innerHTML", $out["dienste"]);
		$objResponse->addAssign("search-filter-srv","innerHTML", $out["service-filter"]);
		$Content.="search_dienste=".$ready["dienste"]."; ";
		if($ready["dienste"]==1 && $cat=="") $objResponse->addAssign("search-header-indicator-dienste","innerHTML", "");
	}
	if($search_info=="0") {
		$objResponse->addAssign("search-container-info","innerHTML", $out["info"]);
		$Content.="search_info=".$ready["info"]."; ";
		if($ready["info"]==1 && $cat=="") $objResponse->addAssign("search-header-indicator-info","innerHTML", "");
	}
	if($search_meta=="0") {
		$objResponse->addAssign("search-container-meta","innerHTML", $out["meta"]);
		$Content.="search_meta=".$ready["meta"]."; ";
		if($ready["meta"]==1 && $cat=="") $objResponse->addAssign("search-header-indicator-meta","innerHTML", "");
	}

	$timer=false;

// Adressen
	$content="";
	if(file_exists($adressfile)) {
		$content=file_get_contents($adressfile);
		$count=substr_count($content,"<member ");
		$objResponse->addAssign("search-count-adressen","innerHTML", $count);
	}
	if(strpos($content,"<ready>true</ready>")!==false) $objResponse->addAssign("search-indicator-adressen","innerHTML", "");
	else $timer=true;

// Dienste
	$content="";
	$Counter=CountDienste($dienstefile);
	if($Counter['ready']==1) {
		$objResponse->addAssign("search-count-dienste","innerHTML", $Counter['count']);
		$objResponse->addAssign("search-indicator-dienste","innerHTML", "");
	}	else {
		$timer=true;
	}

// Info
	$wcontent=$content="";
	$count=0;
	if(file_exists($typo3file)) {
		$content=file_get_contents($typo3file);
		$count=substr_count($content,"<content>");
	}
	if(file_exists($wikifile)) {
		$wcontent=file_get_contents($wikifile);
		$count+=substr_count($wcontent,"<member>");
	}
	$objResponse->addAssign("search-count-wiki","innerHTML", $count);
	if(strpos($content,"</searchresult>")!==false && strpos($wcontent,"<ready>true</ready>")!==false) $objResponse->addAssign("search-indicator-info","innerHTML", "");
	else $timer=true;

// Meta
	$allready=$count=0;
	if(file_exists($metafile) && filesize($metafile)>0) {
		$indexfile = file($metafile);
		$allready=1;
		$key=0;
		foreach($indexfile as $folder) {
			if(preg_match("/<opensearchinterface>(.+)<\/opensearchinterface>/",$folder,$matches)==0 || trim($matches[1])=="") continue;
			$key++;
			$parts=split("\.",$metafile);
			$filename='';
			for($j=0;$j<count($parts)-1;$j++) {
				if($j>0) $filename.='.';
				$filename.=$parts[$j];
			}
			$filename.=$key.'_1.'.$parts[$j];
			if(file_exists($filename)) {
				$content=file_get_contents($filename);
				if(preg_match("/<totalresults>(.+)<\/totalresults>/",$content,$matches)!=0)
					$count+=$matches[1];
				if(strpos($content,"</resultlist>")===false)
					$allready=0;
			} else {
				$allready=0;
			}
		}
		$objResponse->addAssign("search-count-meta","innerHTML", $count);
	}
	if($allready==0) {
		$timer=true;
	} else {
		$objResponse->addAssign("search-indicator-meta","innerHTML", "");
	}

	if($timer) {
		$Content.="window.setTimeout(\"AjaxSearch()\", 500); ";
		$objResponse->addScript($Content);
	}

// $objResponse->addAssign("searchresults","innerHTML", $newContent);

  return $objResponse->getXML();
}

require("search.common.php");
$xajax->processRequests();
?>