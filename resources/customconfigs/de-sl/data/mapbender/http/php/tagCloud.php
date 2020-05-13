<?php
require_once(dirname(__FILE__)."/../../core/globalSettings.php");
require_once(dirname(__FILE__)."/../classes/class_json.php");
$con = db_connect(DBSERVER,OWNER,PW);
db_select_db(DB,$con);
//$pathToSearchScript = '/portal/servicebereich/suche.html?cat=dienste&searchfilter=';  alte Version
//$pathToSearchScript = '/mapbender/template/template_erw_suche.html?cat=dienste&searchfilter=';  //Version für Saarland
$pathToSearchScript = '/index.php/de/suchergebnis?cat=dienste&';  //2. Version für Saarland
$languageCode = 'de';
$maxFontSize = 40;
$minFontSize = 10;
$maxObjects = 10;
$outputFormat = 'html';
$hostName = $_SERVER['HTTP_HOST'];
//read out information from database:

if (isset($_REQUEST["type"]) & $_REQUEST["type"] != "") {
	$testMatch = $_REQUEST["type"];	
 	if (!($testMatch == 'keywords' or $testMatch == 'topicCategories')){ 
		//echo 'type: <b>'.$testMatch.'</b> is not valid.<br/>'; 
		echo 'Parameter <b>type</b> is not valid (keywords,topicCategories).<br/>'; 
		die(); 		
 	}
	$type = $testMatch;
	$testMatch = NULL;
}
if (isset($_REQUEST["outputFormat"]) & $_REQUEST["outputFormat"] != "") {
	$testMatch = $_REQUEST["outputFormat"];	
 	if (!($testMatch == 'html' or $testMatch == 'json')){ 
		//echo 'outputFormat: <b>'.$testMatch.'</b> is not valid.<br/>'; 
		echo 'Parameter <b>outputFormat</b> is not valid (html or json).<br/>'; 
		die(); 		
 	}
	$outputFormat = $testMatch;
	$testMatch = NULL;
}
if (isset($_REQUEST["scale"]) & $_REQUEST["scale"] != "") {
	$testMatch = $_REQUEST["scale"];	
 	if (!($testMatch == 'linear')){ 
		//echo 'scale: <b>'.$testMatch.'</b> is not valid.<br/>'; 
		echo 'Parameter <b>scale</b> is not valid (linear).<br/>'; 
		die(); 		
 	}
	$scale = $testMatch;
	$testMatch = NULL;
}
if (isset($_REQUEST["maxObjects"]) & $_REQUEST["maxObjects"] != "") {
	$testMatch = $_REQUEST["maxObjects"];	
 	if (!(($testMatch == '10') or ($testMatch == '15') or ($testMatch == 20) or ($testMatch == '25') or ($testMatch == '30'))){ 
		//echo 'maxObjects: <b>'.$testMatch.'</b> is not valid.<br/>'; 
		echo 'Parameter <b>maxObjects</b> is not valid (10,15,20,25,30).<br/>'; 
		die(); 		
 	}
	$maxObjects = (integer)$testMatch;
	$testMatch = NULL;
}
if (isset($_REQUEST["maxFontSize"]) & $_REQUEST["maxFontSize"] != "") {
	$testMatch = $_REQUEST["maxFontSize"];	
 	if (!(($testMatch == '10') or ($testMatch == '20') or ($testMatch == '30') or ($testMatch == '40'))){ 
		//echo 'maxFontSize: <b>'.$testMatch.'</b> is not valid.<br/>'; 
		echo 'Parameter <b>maxFontSize</b> is not valid (10,20,30,40).<br/>'; 
		die(); 		
 	}
	$maxFontSize = (integer)$testMatch;
	$testMatch = NULL;
}
//
if (isset($_REQUEST["languageCode"]) & $_REQUEST["languageCode"] != "") {
	//validate to wms, wfs
	$testMatch = $_REQUEST["languageCode"];	
 	if (!($testMatch == 'de' or $testMatch == 'en' or  $testMatch == 'fr')){ 
		//echo 'languageCode: <b>'.$testMatch.'</b> is not valid.<br/>'; 
		echo 'Parameter <b>languageCode</b> is not valid (de,fr,en).<br/>'; 
		die(); 		
 	}
	$languageCode = $testMatch;
	$testMatch = NULL;
}

/*
if (isset($_REQUEST["hostName"]) & $_REQUEST["hostName"] != "") {
	//validate to some hosts
	$testMatch = $_REQUEST["hostName"];	
 	if (!($testMatch == 'www.geoportal.rlp' or $testMatch == 'www.geoportal.rlp.de' or  $testMatch == 'www.gdi-rp-dienste3.rlp.de' or  $testMatch == '10.7.101.151' or $testMatch == '10.7.101.252' )){ 
		echo 'hostName: <b>'.$testMatch.'</b> is not a valid server of gdi-rp.<br/>'; 
		die(); 		
 	}
	$hostName = $testMatch;
	$testMatch = NULL;
}
*/
if (isset($_REQUEST["hostName"]) & $_REQUEST["hostName"] != "") {
	//validate to some hosts
	$testMatch = $_REQUEST["hostName"];	
	//look for whitelist in mapbender.conf
	$HOSTNAME_WHITELIST_array = explode(",",HOSTNAME_WHITELIST);
	if (!in_array($testMatch,$HOSTNAME_WHITELIST_array)) {
		echo "Requested <b>hostName</b> not in whitelist! Please control your mapbender.conf.";
		$e = new mb_notice("Whitelist: ".HOSTNAME_WHITELIST);
		$e = new mb_notice($testMatch." not found in whitelist!");
		die(); 	
	}
	$hostName = $testMatch;
	$testMatch = NULL;
}


if ($outputFormat == 'json'){
	$classJSON = new Mapbender_JSON;
}

if ($languageCode == 'en'){
	$pathToSearchScript = '/portal/en/service/search.html?cat=dienste&searchfilter=';
}



if ($type == 'keywords'){
	$sql = "select a.keyword, sum(a.count) from ("; 
	$sql .= "(select keyword, count(*) from keyword INNER JOIN  layer_keyword  ON (layer_keyword.fkey_keyword_id = keyword.keyword_id) GROUP BY keyword.keyword) union ";
	$sql .= "(select keyword, count(*) from keyword INNER JOIN  wmc_keyword  ON (wmc_keyword.fkey_keyword_id = keyword.keyword_id) GROUP BY keyword.keyword) union ";
	$sql .= "(select keyword, count(*) from keyword INNER JOIN  wfs_featuretype_keyword  ON (wfs_featuretype_keyword.fkey_keyword_id = keyword.keyword_id)";
	$sql .= " GROUP BY keyword.keyword)) as a WHERE (a.keyword <> '' AND a.keyword <> 'ATKIS' AND a.keyword <> 'DLM' AND a.keyword <> 'Landschaftsmodell') GROUP BY a.keyword ORDER BY sum DESC LIMIT $1";
	$showName = 'keyword';
}

if ($type == 'topicCategories') {
	$sql = "select a.md_topic_category_code_".$languageCode.", a.md_topic_category_id,sum(a.count) from ("; 
	$sql .= "(select md_topic_category_code_".$languageCode.",md_topic_category_id, count(*) from md_topic_category INNER JOIN  layer_md_topic_category  ON (layer_md_topic_category.fkey_md_topic_category_id = md_topic_category.md_topic_category_id) GROUP BY md_topic_category.md_topic_category_code_".$languageCode.",md_topic_category.md_topic_category_id) union ";
	$sql .= "(select md_topic_category_code_".$languageCode.",md_topic_category_id, count(*) from  md_topic_category INNER JOIN  wfs_featuretype_md_topic_category  ON (wfs_featuretype_md_topic_category.fkey_md_topic_category_id = md_topic_category.md_topic_category_id) GROUP BY md_topic_category.md_topic_category_code_".$languageCode.",md_topic_category.md_topic_category_id) union ";
	$sql .= "(select md_topic_category_code_".$languageCode.",md_topic_category_id, count(*) from md_topic_category INNER JOIN  wmc_md_topic_category  ON (wmc_md_topic_category.fkey_md_topic_category_id = md_topic_category.md_topic_category_id)";
	$sql .= " GROUP BY md_topic_category.md_topic_category_code_".$languageCode.",md_topic_category.md_topic_category_id)) as a WHERE a.md_topic_category_code_".$languageCode." <> '' GROUP BY a.md_topic_category_code_".$languageCode.", a.md_topic_category_id ORDER BY sum DESC LIMIT $1";
	$showName = 'md_topic_category_code_'.$languageCode;
}
#sql
#select a.md_topic_category_code_de,a.md_topic_category_id, sum(a.count) from ((select md_topic_category_code_de,md_topic_category_id, count(*) from md_topic_category INNER JOIN  layer_md_topic_category  ON (layer_md_topic_category.fkey_md_topic_category_id = md_topic_category.md_topic_category_id) GROUP BY md_topic_category.md_topic_category_code_de,md_topic_category.md_topic_category_id) union (select md_topic_category_code_de,md_topic_category_id, count(*) from  md_topic_category INNER JOIN  wfs_featuretype_md_topic_category  ON (wfs_featuretype_md_topic_category.fkey_md_topic_category_id = md_topic_category.md_topic_category_id) GROUP BY md_topic_category.md_topic_category_code_de,md_topic_category.md_topic_category_id) union (select md_topic_category_code_de,md_topic_category_id, count(*) from md_topic_category INNER JOIN  wmc_md_topic_category  ON (wmc_md_topic_category.fkey_md_topic_category_id = md_topic_category.md_topic_category_id) GROUP BY md_topic_category.md_topic_category_code_de,md_topic_category.md_topic_category_id)) as a WHERE a.md_topic_category_code_de <> '' GROUP BY a.md_topic_category_code_de, a.md_topic_category_id ORDER BY sum DESC LIMIT 20


$v = array($maxObjects);
$t = array('i');
$res = db_prep_query($sql,$v,$t);
$tags = array();
$i = 0;
//max pixelsize

$inc = ($maxFontSize-$minFontSize)/$maxObjects;//maybe 10 or 5 or ...
$maxWeight = 0;

while($row = db_fetch_array($res)){
	if ((integer)$row['sum'] >= $maxWeight ) {
		$maxWeight = (integer)$row['sum'];
	} 
	if ($type == 'topicCategories') {
		$tags[$i] = array('weight'  =>$row['sum'], 'tagname' =>$row[$showName], 'url'=>'http://'.$hostName.$pathToSearchScript.'searchText=*&resultTarget=file&outputFormat=json&isoCategories='.$row['md_topic_category_id'].'&languageCode='.$languageCode);
	}
	if ($type == 'keywords') {
		$tags[$i] = array('weight'  =>$row['sum'], 'tagname' =>$row[$showName], 'url'=>'http://'.$hostName.$pathToSearchScript.'searchText='.$row[$showName].'&resultTarget=file&outputFormat=json&languageCode='.$languageCode);
	}

	$i++;
}
//normalize the tag cloud with some max value for pixelsize or set them to linear scale!

for($i=0; $i<count($tags); $i++){
	if ($scale == 'linear'){
		$tags[$i]['weight'] = $maxFontSize-($i*$inc);
	} else {
		$tags[$i]['weight'] = $tags[$i]['weight']*$maxFontSize/$maxWeight;
	}
}

if ($outputFormat == 'html'){
	echo "<html>";
	echo "<title>Mapbender Tag Cloud</title>";
	echo "<style type=\"text/css\">";
	echo "#tagcloud{";
		echo "color: #dda0dd;";
		echo "font-family: Arial, verdana, sans-serif;";
		echo "width:650px;";
		echo "border: 1px solid black;";
		echo "text-align: center;";
	echo "}";

	echo "#tagcloud a{";
	echo "      color: #871e32;";
	echo "      text-decoration: none;";
	echo "      text-transform: capitalize;";
	echo "}";
	echo "</style>";
	echo "<body>";
	echo "</body>";
	echo "</html>";
	echo "<div id=\"tagcloud\">";
	/*** create a new tag cloud object ***/
	$tagCloud = new tagCloud($tags);
	echo $tagCloud -> displayTagCloud();
	echo "</div>";
	echo "</body>";
	echo "</html>";
}

if ($outputFormat == 'json'){
	$tagCloudJSON = new stdClass;
	$tagCloudJSON->tagCloud = (object) array(
		'maxFontSize' => $maxFontSize, 
		'maxObjects' => $maxObjects,
		'tags' => array()
	);
	shuffle($tags);
	for($i=0; $i<count($tags);$i++){
    		$tagCloudJSON->tagCloud->tags[$i]->title = $tags[$i]['tagname'];
		$tagCloudJSON->tagCloud->tags[$i]->url = $tags[$i]['url'];
		$tagCloudJSON->tagCloud->tags[$i]->weight = $tags[$i]['weight'];
   	 }
#echo "json";
	$tagCloudJSON = $classJSON->encode($tagCloudJSON);
	echo $tagCloudJSON;
}

class tagCloud{

/*** the array of tags ***/
private $tagsArray;


public function __construct($tags){
 /*** set a few properties ***/
 $this->tagsArray = $tags;
}

/**
 *
 * Display tag cloud
 *
 * @access public
 *
 * @return string
 *
 */
public function displayTagCloud(){
 $ret = '';
 shuffle($this->tagsArray);
 foreach($this->tagsArray as $tag)
    {
    $ret.='<a style="font-size: '.$tag['weight'].'px;" href="'.$tag['url'].'" title="'.$tag['tagname'].'">'.$tag['tagname'].'</a>'."\n";
    }
 return $ret;
}


} /*** end of class ***/






?>
