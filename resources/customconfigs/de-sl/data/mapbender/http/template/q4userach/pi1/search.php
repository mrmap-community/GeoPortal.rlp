<?PHP
include_once(dirname(__FILE__).'/../../../../fileadmin/function/config.php');
include_once(dirname(__FILE__).'/../../../../fileadmin/function/util.php');
include_once(dirname(__FILE__).'/../../../../fileadmin/function/function.php');
include_once('config.php');

$unique=$_SERVER['argv'][1];
$searchtext=$_SERVER['argv'][2];
$language=$_SERVER['argv'][3];
$wait=$_SERVER['argv'][4];

$microsPerSecond = 1000000;

if($_REQUEST['searchtext']!='') $searchtext=$_REQUEST['searchtext'];

//$unique='123';
//$searchtext='&';
//$language='0';
//$wait='0';

if($searchtext!='') {
	global $timestamp, $result, $search, $crumb, $db, $db2, $sword, $preview;
	global $contentresults, $newsresults, $language;
	GLOBAL $news_show, $glossar_show;

	$timestamp=time();
	$result=array();
	$search=array();
	$crumb=array();
	$contentresults=0;
	$newsresults=0;
	$sword='';

	$db=new DB_MYSQL;
	$db2=new DB_MYSQL;

	mb_internal_encoding('UTF-8');

	count_search(strip_tags(mb_strtolower($searchtext)));
	usort($search, 'cmp_search');

# 	var_dump($search);

#	$time_start = microtime_float(); ööö
	include('typo3_suche.php');
	find_page($startid);
#	$time_end = microtime_float(); $time = $time_end - $time_start; echo "<strong><em>Did nothing in $time seconds</em></strong><br />\n";

#	$time_start = microtime_float();
	include('ttnews_suche.php');

#	$time_end = microtime_float(); $time = $time_end - $time_start; echo "<strong><em>Did nothing in $time seconds</em></strong><br />\n";
	//$time_end = microtime_float(); $time = $time_end - $time_start;

	usort($result, 'cmp_result');

if($wait==0) $output = fopen('typo3conf/ext/q4u_search/pi1/temp/'.$unique.'.typo3.xml', 'w');
else $output = fopen('typo3conf/ext/q4u_search/pi1/temp/'.$unique.'.meta.xml', 'w');

	fwrite($output,"<searchresult>\n");
	xmlwrite($output,"count",$contentresults);
	xmlwrite($output,"uid",$unique);
	xmlwrite($output,"searchtext",$searchtext);

	for($i=0;$i<$contentresults;$i++) {
		if($i==0) $maxrating=$result[$i]['FOUND'];
#		usleep(($microsPerSecond));
		fwrite($output,"<content>\n");
		if($result[$i]['TYP']=="NEWS") {
			xmlwrite($output, "typ", "news");
			xmlwrite($output, "uid", $result[$i]['UID']);
			xmlwrite($output, "title", $result[$i]['TITLE']);
			xmlwrite($output, "link", $result[$i]['URL']);
			xmlwrite($output, "breadcrumb", $crumb[$result[$i]['UID']]);
			xmlwrite($output, "shorttext", $result[$i]['SHORTTEXT']);
			xmlwrite($output, "datetime", $result[$i]['DATETIME']);
			xmlwrite($output, "rating", round(($result[$i]['FOUND']/$maxrating*100),2));
		} elseif($result[$i]['TYP']=="CONTENT") {
			xmlwrite($output, "typ", "content");
			xmlwrite($output, "uid", $result[$i]['UID']);
			xmlwrite($output, "title", $result[$i]['TITLE']);
			xmlwrite($output, "link", realurl_link($result[$i]['UID'],true,$language).$sword);
			xmlwrite($output, "breadcrumb", $crumb[$result[$i]['UID']]);
			xmlwrite($output, "shorttext", $result[$i]['SHORTTEXT']);
			xmlwrite($output, "rating", round(($result[$i]['FOUND']/$maxrating*100),2));
		} elseif($result[$i]['TYP']=="GLOSSAR") {
			xmlwrite($output, "typ", "glossar");
			xmlwrite($output, "uid", $result[$i]['UID']);
			xmlwrite($output, "title", $result[$i]['TITLE']);
			xmlwrite($output, "link", $result[$i]['URL']);
			xmlwrite($output, "breadcrumb", $crumb[$result[$i]['UID']]);
			xmlwrite($output, "shorttext", $result[$i]['SHORTTEXT']);
			xmlwrite($output, "rating", round(($result[$i]['FOUND']/$maxrating*100),2));
		}
		fwrite($output,"</content>\n");
	}
	fwrite($output,"</searchresult>\n");
	fclose($output);
}

function xmlwrite($handle, $tag, $value) {
	fwrite($handle,'<'.$tag.'>'.urlencode($value).'</'.$tag.'>'."\n");
}

function microtime_float() {
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

function cmp_result($a, $b) {
	if ($a['FOUND'] == $b['FOUND']) {
		if ($a['TYP'] == $b['TYP']) {
			if ($a['TYP'] == "NEWS") {
				return ($a['DATETIME'] > $b['DATETIME']) ? -1 : 1;
			}
			return 0;
		}
		return ($a['TYP'] > $b['TYP']) ? 1 : -1;
	}
	return ($a['FOUND'] > $b['FOUND']) ? -1 : 1;
}
?>