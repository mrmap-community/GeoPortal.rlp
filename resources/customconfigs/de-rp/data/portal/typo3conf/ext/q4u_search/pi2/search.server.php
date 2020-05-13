<?php
function search($uid,$searchid,$cat,$page,$userid,$language,$search_meta,$search_dienste) {
	$ajax=true;
	$_REQUEST['uid']=$uid;
	$_REQUEST['searchid']=$searchid;
	$_REQUEST['cat']=$cat;
	$_REQUEST['page']=$page;
	$_SESSION['mb_user_id']=$userid;

	include('start_search.php');

  $newContent='AJAX: '.date('h:i:s');
  $newContent.='---'.$language.'---'.$search_meta.'---'.$search_dienste.'---';
	$newContent.='---Result Dienste '.$ready['dienste'].'---';
	$newContent.='---Result Meta'.$ready['meta'].'---';

	$objResponse = new xajaxResponse();

	if($search_filter=="0") {
		$objResponse->addAssign("search-filter","innerHTML", $out["filter"]);
		$Content.="search_filter=".$ready["filter"]."; ";
	}
	if($search_dienste=='0') {
		$newContent.='---Dienste suche---';
		$objResponse->addAssign('search-container-dienste','innerHTML', $out['dienste']);
		$objResponse->addAssign("search-filter-srv","innerHTML", $out["service-filter"]);
		$Content.='search_dienste='.$ready['dienste'].'; ';
		if($ready['dienste']==1 && $cat=='') $objResponse->addAssign('search-header-indicator-dienste','innerHTML', '');
		$newContent.='--Count:'.count($check_array).'---';
	}
	if($search_meta=='0') {
		$newContent.='---Meta suche---';
		$objResponse->addAssign('search-container-meta','innerHTML', $out['meta']);
		$Content.='search_meta='.$ready['meta'].'; ';
		if($ready['meta']==1 && $cat=='') $objResponse->addAssign('search-header-indicator-meta','innerHTML', '');
	}

	$timer=false;

// Dienste
	$content="";
	$Counter=CountDienste($dienstefile);
	if($Counter['ready']==1) {
		$objResponse->addAssign("search-count-dienste","innerHTML", $Counter['count']);
		$objResponse->addAssign("search-indicator-dienste","innerHTML", "");
	}	else {
		$timer=true;
	}

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
		$newContent.='---Timer---';
		$Content.='window.setTimeout("AjaxSearch()", 500); ';
		$objResponse->addScript($Content);
	}

#	$objResponse->addAssign('searchresults','innerHTML', $newContent);

  return $objResponse->getXML();
}

require('search.common.php');
$xajax->processRequests();
?>
