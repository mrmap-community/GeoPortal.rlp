<?php
// ä
$indicator='<img src="fileadmin/design/indicator.gif" />';
$BundesLaender=array('DE-BW','DE-BY','DE-BE','DE-BR','DE-HB','DE-HH','DE-HE','DE-MV','DE-NI','DE-NW','DE-RP','DE-SL','DE-SN','DE-ST','DE-SH','DE-TH');

function textcut2($text,$size=200) {
  $shorttext=str_replace('<br', ' <br', $text);
  $shorttext=substr(strip_tags($shorttext,'<br>'),0,$size);

	if(strlen($text) >= $size) {
		$shorttext=substr($shorttext,0,strrpos($shorttext,' ')).'&hellip;';
	}
	return($shorttext);
}

function PageCounter($count, $items_page, $url, $typ, $max=1, $pos=0) {
	global $page;

	if(($count>$items_page && $items_page>0) ||
		 ($items_page<-1) ) {

		$pagecounter=explode('|',$page);
		for($i=0;$i<$max;$i++) {
			$pagecounter[$i]=intval($pagecounter[$i]);
		}
		$currentpage=$pagecounter[$pos];
		$npages=($items_page<0)?-$items_page:ceil($count/$items_page);
		$output.='
			<div class="search-pagecounter-container">Seiten: ';
		$pmin=$currentpage+1-5;
		if($pmin<1) $pmin=1;
		if($pmin>1) $output.='&hellip;';
		$pmax=$currentpage+1+5;
		if($pmax>$npages) $pmax=$npages;
		$more=($pmax<$npages)?'&hellip;':'';
		for($i=$pmin;$i<=$pmax;$i++) {
			$pagecounter[$pos]=$i-1;
			$pagearray=implode('|',$pagecounter);
			if($i==$currentpage+1) {
				$output.='<a class="search-pagecounter-item active" href="'.$url.'&amp;cat='.$typ.'&amp;page='.$pagearray.'&amp;pos='.$pos.'">'.$i.'</a>';
			} else {
				$output.='<a class="search-pagecounter-item" href="'.$url.'&amp;cat='.$typ.'&amp;page='.$pagearray.'&amp;pos='.$pos.'">'.$i.'</a>';
			}
		}
		$output.=$more.'
			</div>';
	}
	return $output;
}

function PageCounterOld($count, $items_page, $url, $typ) {
	$output='<div class="search-pagecounter-container">Seiten: ';
	for($i=1,$j=0;$i<$count+1;$i+=$items_page,$j++) {
		if($j==$_REQUEST['page']) {
			$output.='<a class="search-pagecounter-item active" href="'.$url.'&amp;cat='.$typ.'&amp;page='.$j.'">'.($j+1).'</a>';
		} else {
			$output.='<a class="search-pagecounter-item" href="'.$url.'&amp;cat='.$typ.'&amp;page='.$j.'">'.($j+1).'</a>';
		}
	}
	$output.='</div>
	';
	return($output);
}

function SearchChange() {
	$db=new DB_MYSQL;
	$sql='SELECT * FROM search WHERE uid="'.$db->v($_SESSION['mb_user_id']).'" AND id="'.$db->v($_REQUEST['editid']).'"';
	$db->query($sql);
	if($db->num_rows()) {
		$db->next_record();
		if($_REQUEST['searchtext']!=$db->f('searchtext')) {
			$sql='UPDATE search SET name="'.$db->v($_REQUEST['name']).'", searchtext="'.$db->v($_REQUEST['searchtext']).'", datetime='.time().' WHERE uid="'.$db->v($_SESSION['mb_user_id']).'" AND id="'.$db->v($_REQUEST['editid']).'"';
			$db->query($sql);
			if($_REQUEST['editid']!=$_REQUEST['searchid']) {
				return(false);
			} else {
				$_REQUEST['uid']=CreateUUID();
				return(true);
			}
		} else {
			$sql='UPDATE search SET name="'.$db->v($_REQUEST['name']).'", datetime='.time().' WHERE uid="'.$db->v($_SESSION['mb_user_id']).'" AND id="'.$db->v($_REQUEST['editid']).'"';
			$db->query($sql);
			return(false);
		}
	}
}

function SearchSave() {
	$db=new DB_MYSQL;
	$sql='SELECT * FROM search WHERE uid="'.$db->v($_SESSION['mb_user_id']).'" AND searchtext="'.$db->v($_REQUEST['searchtext2']).'" AND name="'.$db->v($_REQUEST['name']).'"';
	$db->query($sql);
	if($db->num_rows()) {
		return(intval($_REQUEST['searchid']));
	} else {
		$sql='INSERT INTO search (SELECT 0, "'.$db->v($_REQUEST['name']).'", uid, "'.$db->v($_REQUEST['searchtext2']).'", '.time().', 0 FROM search WHERE id="'.$db->v($_REQUEST['searchid']).'")';
		$db->query($sql);
		return($db->insert_id());
	}
}

function SearchDelete() {
	$db=new DB_MYSQL;
	$sql='DELETE FROM search WHERE uid="'.$db->v($_SESSION['mb_user_id']).'" AND id="'.$db->v($_REQUEST['deleteid']).'"';
	$db->query($sql);
	if($_REQUEST['searchid']==$_REQUEST['deleteid']) {
		$sql='SELECT * FROM search WHERE uid="'.$db->v($_SESSION['mb_user_id']).'" AND lastsearch=1 ORDER BY datetime desc';
		$db->query($sql);
		if($db->num_rows()) {
			$db->next_record();
			return($db->f('id'));
		}
	} else {
		return(intval($_REQUEST['searchid']));
	}
}

function SearchURL($searchid=0, $cat=false) {
	$page=parse_url($_SERVER['REQUEST_URI']);
	$url=$page['path'];
	if($searchid==0) {
		if($_REQUEST['selectsearch']=='1') $url.='?searchid='.intval($_REQUEST['searchid']).'&amp;selectsearch=1';
		if($_REQUEST['selectsearch']=='0') $url.='?searchid='.intval($_REQUEST['searchid']).'&amp;selectsearch=0';
	} else {
		$url.='?searchid='.intval($searchid).'&amp;selectsearch=1&amp;act=search';
	}

	if($url==$page['path']) $url.='?';

	$url.='&amp;uid='.PtH($_REQUEST['uid']);
	if($cat) $url.='&amp;cat='.PtH($_REQUEST['cat']);

	return $url;
}

function SearchText() {
	$db=new DB_MYSQL;

	if($_REQUEST['searchid']!='') {
#		$sql='SELECT * FROM search WHERE uid="'.$_SESSION['mb_user_id'].'" AND id='.$_REQUEST['searchid'];
		$sql='SELECT * FROM search WHERE uid="'.$db->v(UserID()).'" AND id="'.$db->v($_REQUEST['searchid']).'"';
		$db->query($sql);
		if($db->num_rows()) {
			$db->next_record();
			$searchtext=$db->f('searchtext');
		}
	} elseif($_REQUEST['searchtext']!='') {
		$searchtext=$_REQUEST['searchtext'];
	}
	return($searchtext);
}

function CreateUUID() {
 // The field names refer to RFC 4122 section 4.1.2
 return sprintf('%04x%04x-%04x-%03x4-%04x-%04x%04x%04x',
     mt_rand(0, 65535), mt_rand(0, 65535), // 32 bits for "time_low"
     mt_rand(0, 65535), // 16 bits for "time_mid"
     mt_rand(0, 4095),  // 12 bits before the 0100 of (version) 4 for "time_hi_and_version"
     bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '01', 6, 2)),
         // 8 bits, the last two of which (positions 6 and 7) are 01, for "clk_seq_hi_res"
         // (hence, the 2nd hex digit after the 3rd hyphen can only be 1, 5, 9 or d)
         // 8 bits for "clk_seq_low"
     mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535) // 48 bits for "node"
 );
}

function WriteLastSearch() {
	$db=new DB_MYSQL;

	if($_REQUEST['selectsearch']==1) {
		#$sql='SELECT * FROM search WHERE uid="'.$_SESSION['mb_user_id'].'" AND id='.$_REQUEST['searchid'];
		$sql='SELECT * FROM search WHERE uid="'.$db->v(UserID()).'" AND id="'.$db->v($_REQUEST['searchid']).'"';
		$db->query($sql);
		if($db->num_rows()) {
			$db->next_record();
			if($db->f('lastsearch')==1) return(intval($_REQUEST['searchid']));
		}

		$sql='INSERT INTO search (uid, searchtext, datetime, name, lastsearch)
		           VALUES ("'.$db->v(UserID()).'", "'.$db->v($_REQUEST['searchtext']).'", '.time().', "letzte Suche", 1)';
		$db->query($sql);
		return(intval($_REQUEST['searchid']));
	} else {
		$sql='INSERT INTO search (uid, searchtext, datetime, name, lastsearch)
		           VALUES ("'.$db->v(UserID()).'", "'.$db->v($_REQUEST['searchtext']).'", '.time().', "letzte Suche", 1)';
		$db->query($sql);
		return($db->insert_id());
	}
}

global $AdrCat;
$AdrCat = array(
	'sh' => 'Adresse',
	'str' => 'Straße',
	'wp' => 'Ortsmittelpunkt',
	'g' => 'Gemeinde',
	'vg' => 'Verbandsgemeinde',
	'k' => 'Landkreis',
);

global $searchResources;
$searchResources = array(
	'dataset',
	'wms',
	'wfs',
	'wmc'
);

function getAdrCat() {
	global $L, $AdrCat, $AdrCatCount;
	global $LinkURL;

	if($_REQUEST['cat']=='adressen') {

		$url=$LinkURL.'?searchid='.intval($_REQUEST['searchid']).'&amp;selectsearch='.intval($_REQUEST['selectsearch']).'&amp;uid='.PtH($_REQUEST['uid']).'&amp;cat=adressen&amp;adrcat=';

		$AdrOut='';
		foreach($AdrCat as $key=>$val) {
			if($AdrCatCount[$key]>0) {
				if($_REQUEST['adrcat']!='') {
					$AdrOut.='
			<div class="search-adrcat-single">
				'.$val.' ('.$AdrCatCount[$key].') <a href="'.$url.'">(X)</a>
			</div>';
				} else {
					$AdrOut.='
			<div class="search-adrcat-single">
				<a href="'.$url.$key.'">'.$val.' ('.$AdrCatCount[$key].')</a>
			</div>';
				}
			}
		}
		if($AdrOut!='') {
			$AdrOut='
		<div class="search-adrcat">
			<h2>Adressarten</h2>
		'.$AdrOut.'
		</div>';
		}
	}
	return $AdrOut;
}
function getServiceCat() {
	global $L, $ServiceCat;
	global $LinkURL;

//	$url=$L['SuchURL'].'?searchid='.$_REQUEST['searchid'].'&amp;selectsearch='.intval($_REQUEST['selectsearch']).'&amp;uid='.$_REQUEST['uid'].'&amp;cat=adressen&amp;adrcat=';

	$Sum=array();

	$url=$LinkURL.'?cat=dienste&searchfilter=';

	if($_REQUEST['cat']=='dienste' && count($ServiceCat)>0) {
		$CatOut='';
		foreach($ServiceCat as $Service) {
			// Aufsummieren
			if(count($Service->searchMD->category)>0) {
				foreach($Service->searchMD->category as $Cat) {
					if(count($Cat->subcat)>0) {
						foreach($Cat->subcat as $SubCat) {
							if($SubCat->count>0) {
								$Sum[$Cat->title][$SubCat->title]['count']+=$SubCat->count;
								$Sum[$Cat->title][$SubCat->title]['link']=$SubCat->filterLink;
							}
						}
					}
				}
			}
		}
//*********************************************************************************************
//create new array $b which can be sorted - old array is $a
$a = $Sum;
//*********************************************************************************************
$b = array();
foreach ($a as $key1=>$value1) {
	foreach ($a[$key1] as $key2=>$value2) {
		$b[] = array("MainCat" => $key1, "SubCat" => $key2, "count" => $a[$key1][$key2]['count'], "link" => $a[$key1][$key2]['link']);
	}
}
//********************************************************************************************
foreach($b as $c=>$key) {
	$sort_maincat[] = $key['MainCat'];
	$sort_count[] = $key['count'];
}
//sort array $b
array_multisort($sort_maincat, SORT_DESC, $sort_count, SORT_DESC, $b);
//create new array $c in the same layout as the original array $a 
$c = array();
foreach($b as $row) {
	$c[$row['MainCat']][$row['SubCat']]['count'] = $row['count'];
	$c[$row['MainCat']][$row['SubCat']]['link'] = $row['link'];
}
//*********************************************************************************************
$Sum = $c;
//*********************************************************************************************
$numberFacets = 0;
		foreach($Sum as $Header=>$Cats) {
			$CatOut.='
			<div class="search-srvcat-main">
				'.$Header.'
			</div>';
			$CatOut .= '<ul id="myListCat'.$numberFacets.'" class="search-srvcat-ul">';
			foreach($Cats as $Title=>$Cat) {
				$CatOut.='
				<li class="search-srvcat-sub">
					<a href="'.$url.urlencode($Cat['link']).'">'.$Title.' ('.$Cat['count'].')</a>
				</li>';
			}
$CatOut .= '</ul>';
//$CatOut .= '<div id="myListCat'.$numberFacets.'LoadMore" class="search-srvcat-more">+ mehr</div>';
//$CatOut .= '<div id="myListCat'.$numberFacets.'ShowLess" class="search-srvcat-less">- weniger</div>';
$numberFacets++;
		}
		if($CatOut!='') {
			$CatOut='
		<div class="search-adrcat">
		'.$CatOut.'
		</div>';
		}
	}
	return $CatOut;
}

function sortBySize($a,$b) {
    if ($a['size'] == $b['size']) {
        return strcmp(mb_strtolower($a['title'], 'UTF-8'), mb_strtoupper($b['title'], 'UTF-8'));
    }
    return ($a['size'] < $b['size']) ? -1 : 1;
}

function convertCoordinates($bboxStr,$oldEPSG,$newEPSG) {
    include(dirname(__FILE__).'/mod_transformBbox.php');
}

include(dirname(__FILE__).'/search_functions_filter.php');
include(dirname(__FILE__).'/search_functions_adressen.php');
include(dirname(__FILE__).'/search_functions_wiki.php');
include(dirname(__FILE__).'/search_functions_typo3.php');
include(dirname(__FILE__).'/search_functions_meta.php');
include(dirname(__FILE__).'/search_functions_dienste.php');

?>
