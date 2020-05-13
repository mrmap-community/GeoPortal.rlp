<?php
#http://localhost/mapbender/geoportal/mod_readCSWResultsDetail.php?cat_id=1&uuid=...
require_once(dirname(__FILE__)."/../../core/globalSettings.php");
#$con = db_connect(DBSERVER,OWNER,PW);
#db_select_db(DB,$con);
require_once(dirname(__FILE__)."/../classes/class_connector.php");
require_once(dirname(__FILE__)."/../php/mod_validateInspire.php");
require_once(dirname(__FILE__) . "/../../tools/wms_extent/extent_service.conf");
require_once(dirname(__FILE__) . "/../classes/class_iso19139.php");
require_once(dirname(__FILE__) . "/../classes/class_Uuid.php");
//INSPIRE Mapping
require_once(dirname(__FILE__)."/../../conf/isoMetadata.conf");
$languageCode = "de";
$layout = "tabs";
//get language parameter out of mapbender session if it is set else set default language to de_DE
if (isset($_SESSION['mb_lang']) && ($_SESSION['mb_lang']!='')) {
	$e = new mb_notice("mod_readCSWResultsDetail.php: language found in session: ".$_SESSION['mb_lang']);
	$language = $_SESSION["mb_lang"];
	$langCode = explode("_", $language);
	$langCode = $langCode[0]; # Hopefully de or s.th. else
	$languageCode = $langCode; #overwrite the GET Parameter with the SESSION information
}

if (isset($_REQUEST["languageCode"]) & $_REQUEST["languageCode"] != "") {
	//validate to csv integer list
	$testMatch = $_REQUEST["languageCode"];
	if (!($testMatch == 'de' or $testMatch == 'fr' or $testMatch == 'en')){ 
		echo 'languageCode is not valid - it should be de, fr, or en.<br/>'; 
		die(); 		
 	}
	$languageCode = $testMatch;
	$testMatch = NULL;
}
//validate following parameter to prohibit xss - see result pentest 03/2014
//cat_id
if (isset($_REQUEST["cat_id"]) & $_REQUEST["cat_id"] != "") {
	//validate to integer
        $testMatch = $_REQUEST["cat_id"];
        //give max 99 entries - more will be to slow
        $pattern = '/^[0-9]*$/';  
        if (!preg_match($pattern,$testMatch)){
                echo 'Parameter <b>cat_id</b> is not valid (integer).<br/>';
                die();
        }
        $testMatch = NULL;
}
//validate
if (isset($_REQUEST["validate"]) & $_REQUEST["validate"] != "") {
	$testMatch = $_REQUEST["validate"];	
 	if (!($testMatch == 'true' or $testMatch == 'false')){ 
		echo 'Parameter <b>validate</b> is not valid (true,false).<br/>'; 
		die(); 		
 	}
	$testMatch = NULL;
}
//uuid
if (isset($_REQUEST['uuid']) & $_REQUEST['uuid'] != "") {
	//validate cs list of uuids or other identifiers - which?
	$testMatch = $_REQUEST["uuid"];
	$uuid = new Uuid($testMatch);
	$isUuid = $uuid->isValid();
	if (!$isUuid) {
		echo 'Parameter <b>uuid</b> is not a valid uuid (12-4-4-4-8) or a list of uuids!<br/>'; 
		die(); 		
	}
	$testMatch = NULL;
}
//mdtype
if (isset($_REQUEST["mdtype"]) & $_REQUEST["mdtype"] != "") {
	$testMatch = $_REQUEST["mdtype"];	
 	if (!($testMatch == 'html' or $testMatch == 'iso19139' or $testMatch == 'debug' or $testMatch == 'inspire')){ 
		echo 'Parameter <b>mdtype</b> is not valid (iso19139, html, debug, inspire).<br/>'; 
		die(); 		
 	}
	$testMatch = NULL;
}

if(!isset($_REQUEST["cat_id"])) {
	echo "no opensearch id set";
	die();
} else {
	#if(isset($_REQUEST["mdtype"])&($_REQUEST["mdtype"]=='debug') ) {	
	#	echo "opensearch interface no.: ".$_REQUEST["osid"]." will be requested<br>";
	#}
	$cat_id = $_REQUEST["cat_id"];
}

if(!isset($_REQUEST["uuid"])) {
	echo "No uuid of dataset given!";
	die();
} else {
	$uuid = $_REQUEST["uuid"];
}

function getExtentGraphic($layer_4326_box) {
		$rlp_4326_box = array(6.05,48.9,8.6,50.96);
		if ($layer_4326_box[0] <= $rlp_4326_box[0] || $layer_4326_box[2] >= $rlp_4326_box[2] || $layer_4326_box[1] <= $rlp_4326_box[1] || $layer_4326_box[3] >= $rlp_4326_box[3]) {
			if ($layer_4326_box[0] < $rlp_4326_box[0]) {
				$rlp_4326_box[0] = $layer_4326_box[0]; 
			}
			if ($layer_4326_box[2] > $rlp_4326_box[2]) {
				$rlp_4326_box[2] = $layer_4326_box[2]; 
			}
			if ($layer_4326_box[1] < $rlp_4326_box[1]) {
				$rlp_4326_box[1] = $layer_4326_box[1]; 
			}
			if ($layer_4326_box[3] > $rlp_4326_box[3]) {
				$rlp_4326_box[3] = $layer_4326_box[3]; 
			}

			$d_x = $rlp_4326_box[2] - $rlp_4326_box[0]; 
			$d_y = $rlp_4326_box[3] - $rlp_4326_box[1];
			
			$new_minx = $rlp_4326_box[0] - 0.05*($d_x);
			$new_maxx = $rlp_4326_box[2] + 0.05*($d_x);
			$new_miny = $rlp_4326_box[1] - 0.05*($d_y);
			$new_maxy = $rlp_4326_box[3] + 0.05*($d_y);

			if ($new_minx < -180) $rlp_4326_box[0] = -180; else $rlp_4326_box[0] = $new_minx;
			if ($new_maxx > 180) $rlp_4326_box[2] = 180; else $rlp_4326_box[2] = $new_maxx;
			if ($new_miny < -90) $rlp_4326_box[1] = -90; else $rlp_4326_box[1] = $new_miny;
			if ($new_maxy > 90) $rlp_4326_box[3] = 90; else $rlp_4326_box[3] = $new_maxy;
		}
		$getMapUrl = EXTENTSERVICEURL."VERSION=1.1.1&REQUEST=GetMap&SERVICE=WMS&LAYERS=".EXTENTSERVICELAYER."&STYLES=&SRS=EPSG:4326&BBOX=".$rlp_4326_box[0].",".$rlp_4326_box[1].",".$rlp_4326_box[2].",".$rlp_4326_box[3]."&WIDTH=120&HEIGHT=120&FORMAT=image/png&BGCOLOR=0xffffff&TRANSPARENT=TRUE&EXCEPTIONS=application/vnd.ogc.se_inimage&minx=".$layer_4326_box[0]."&miny=".$layer_4326_box[1]."&maxx=".$layer_4326_box[2]."&maxy=".$layer_4326_box[3];
		return $getMapUrl;
}



function display_text($string) {
    $string = eregi_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]", "<a href=\"\\0\" target=_blank>\\0</a>", $string);   
    $string = eregi_replace("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@([0-9a-z](-?[0-9a-z])*\.)+[a-z]{2}([zmuvtg]|fo|me)?$", "<a href=\"mailto:\\0\" target=_blank>\\0</a>", $string);   
    $string = eregi_replace("\n", "<br>", $string);
    return $string;
} 
 
function guid(){
    if (function_exists('com_create_guid')){
        return com_create_guid();
    }else{
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = chr(123)// "{"
                .substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12)
                .chr(125);// "}"
        return $uuid;
    }
}

$sql_csw = "SELECT * from gp_csw WHERE csw_id = $1 ORDER BY csw_id";

#do db select
$v[] = $cat_id;
$t[] = 'i';
$res_csw = db_prep_query($sql_csw, $v, $t);
#initialize count of search interfaces
$cnt_csw = 0;
#initialize result array
$csw_list=array(array());
#fill result array
while($row_csw = db_fetch_array($res_csw)){
	$csw_list[$cnt_csw] ['id'] = $row_csw["csw_id"];
	
	$csw_list[$cnt_csw] ['name'] = $row_csw["csw_name"];
	#echo "CSW Name:".$row_csw["csw_name"];
	$csw_list[$cnt_csw] ['fkey_cat_id'] = $row_csw["fkey_cat_id"];
	#echo "CSW cat_id:".$row_csw["fkey_cat_id"];

	//get urls for getrecords and getrecordbyid from table cat
        $v = (integer)$row_csw["fkey_cat_id"];
	$t = 'i';
	$sql_gr = "select param_value, param_name from cat_op_conf where fk_cat_id = $1 and param_type = 'getrecords'";
	$res_gr = db_prep_query($sql_gr, $v, $t);
	//look after the values preference get/post/post_xml
	while ($row_gr = db_fetch_array($res_gr)) {
		switch ($row_gr['param_name']) {
			case "get" :
				$csw_list[$cnt_csw] ['getrecordsurl_param_name'] = "get";
				if (isset($row_gr['param_value']) && $row_gr['param_value'] != '') {
					$csw_list[$cnt_csw] ['getrecordsurl'] = $row_gr['param_value'];
					break 2;
				}
			break 1;	
			case "post" :
				$csw_list[$cnt_csw] ['getrecordsurl_param_name'] = "post";
				if (isset($row_gr['param_value']) && $row_gr['param_value'] != '') {
					$csw_list[$cnt_csw] ['getrecordsurl'] = $row_gr['param_value'];
					break 2;
				}
			break 1;
			case "post_xml" :
				$csw_list[$cnt_csw] ['getrecordsurl_param_name'] = "post_xml";
				if (isset($row_gr['param_value']) && $row_gr['param_value'] != '' ) {
					$csw_list[$cnt_csw] ['getrecordsurl'] = $row_gr['param_value'];
					break 2;
				}
			break 1;
		}
		
	}
	$e = new mb_notice("<br>getrecords param type: ".$csw_list[$cnt_csw]['getrecordsurl_param_name']."<br>");
	$csw_list[$cnt_csw] ['getrecordsurl'] = rtrim($csw_list[$cnt_csw] ['getrecordsurl'], "?");
	#echo "count csw: ".$cnt_csw;
	#echo "<br>getrecordsurl: ".$csw_list[$cnt_csw]['getrecordsurl']."<br>";

	$sql_grbi = "select * from cat_op_conf where fk_cat_id = $1 and param_type = 'getrecordbyid' and param_name='get'";
	$res_grbi = db_prep_query($sql_grbi, $v, $t);
        $row_grbi = db_fetch_array($res_grbi);
	$csw_list[$cnt_csw] ['getrecordbyidurl'] = $row_grbi['param_value'];
	$csw_list[$cnt_csw] ['getrecordbyidurl'] = rtrim($csw_list[$cnt_csw] ['getrecordbyidurl'], "?");
	#echo "<br>getrecordbyidurl: ".$csw_list[$cnt_csw]['getrecordbyidurl']."<br>";
	$csw_list[$cnt_csw] ['h'] = $row_csw["csw_h"];
	$csw_list[$cnt_csw] ['p'] = $row_csw["csw_p"];
	$cnt_csw++;
}

#echo "\nCount of registrated OpenSearch Interfaces: ".count($os_list)."\n";
#***

	#define new csw get record by id search like:
	#http://www.portalu.de/csw202?request=GetRecordById&service=CSW&version=2.0.2&Id=81FF8BB2-2753-4A95-8C1E-F78C19035780&ElementSetName=full
	$openSearchUrlDetail = $csw_list[0] ['getrecordbyidurl'];
	
	#echo $cat_id;
	
	$url = $openSearchUrlDetail."?request=GetRecordById&service=CSW&version=2.0.2&Id=".$uuid."&ElementSetName=full&OUTPUTSCHEMA=http://www.isotc211.org/2005/gmd";
	#echo $url;

#create connector object
$openSearchObject = new connector($url);
#get results
$openSearchDetail = $openSearchObject->file;
//solve problem with xlink namespace for href attributes:
$openSearchDetail = str_replace('xlink:href', 'xlinkhref', $openSearchDetail);
#http://forums.devshed.com/php-development-5/simplexml-namespace-attributes-problem-452278.html
#http://www.leftontheweb.com/message/A_small_SimpleXML_gotcha_with_namespaces

$md_ident = $iso19139Hash;
#$openSearchDetail = str_replace('xmlns=', 'ns=', $openSearchDetail);
$openSearchDetailXML=simplexml_load_string($openSearchDetail);
#extract objects to iso19139 elements
$openSearchDetailXML->registerXPathNamespace("csw", "http://www.opengis.net/cat/csw/2.0.2");
$openSearchDetailXML->registerXPathNamespace("gml", "http://www.opengis.net/gml");
$openSearchDetailXML->registerXPathNamespace("gco", "http://www.isotc211.org/2005/gco");
$openSearchDetailXML->registerXPathNamespace("gmd", "http://www.isotc211.org/2005/gmd");
$openSearchDetailXML->registerXPathNamespace("gts", "http://www.isotc211.org/2005/gts");
$openSearchDetailXML->registerXPathNamespace("srv", "http://www.isotc211.org/2005/srv");
$openSearchDetailXML->registerXPathNamespace("xlink", "http://www.w3.org/1999/xlink");

//check if only iso19139 data is requested - if so - push the result automatically from the CSW getRecordById request to the user or the validator
if ($_REQUEST['mdtype']=='iso19139' && $_REQUEST['validate'] != 'true') {
	header("Content-type: application/xhtml+xml; charset=UTF-8");
	//delete csw entries from response file
	$MD_Metadata = str_replace('<csw:GetRecordByIdResponse xmlns:csw="http://www.opengis.net/cat/csw/2.0.2">', '', $openSearchDetail);
	$MD_Metadata = str_replace('</csw:GetRecordByIdResponse>', '', $MD_Metadata);
	echo $MD_Metadata;
	die();
}
if ($_REQUEST['mdtype']=='iso19139' && $_REQUEST['validate'] == 'true') {
	$MD_Metadata = str_replace('<csw:GetRecordByIdResponse xmlns:csw="http://www.opengis.net/cat/csw/2.0.2">', '', $openSearchDetail);
	$MD_Metadata = str_replace('</csw:GetRecordByIdResponse>', '', $MD_Metadata);
	validateInspire($MD_Metadata);
}

$j=0;

for($a = 0; $a < count($md_ident); $a++) {
	$resultOfXpath = $openSearchDetailXML->xpath('/csw:GetRecordByIdResponse'.$md_ident[$a]['iso19139']);
	for ($i = 0; $i < count($resultOfXpath); $i++) {
		$md_ident[$a]['value'] = $md_ident[$a]['value'].",".$resultOfXpath[$i];
	}
	$md_ident[$a]['value'] = ltrim($md_ident[$a]['value'],',');
}

//generate output for different parameters mdtype

switch ($_REQUEST["mdtype"]) {
	case "html":
		$mbMetadata = new Iso19139();
		$mbMetadata->readFromUrl($url);
		$html = $mbMetadata->transformToHtml('tabs','de');
		header("Content-type: text/html; charset=UTF-8");
		echo $html;
		die();
	break;
	case "inspire":
		echo "<a href='".$url."'>GetRecordById URL</a><br><br>";
		for($a = 0; $a < count($md_ident); $a++) {
			echo "<b>".$md_ident[$a]['html']."</b>: ".$md_ident[$a]['value']."<br><br>";
		}
		die();
	break;
	case "debug":
		echo "<a href='".$url."'>GetRecordById URL</a><br><br>";
		for($a = 0; $a < count($md_ident); $a++) {
			echo "<b>".$md_ident[$a]['html']."</b>: ".$md_ident[$a]['value']."<br><br>";
		}
		die();
	break;
	default:
		echo "<a href='".$url."'>GetRecordById URL</a><br><br>";
		for($a = 0; $a < count($md_ident); $a++) {
			echo "<b>".$md_ident[$a]['html']."</b>: ".$md_ident[$a]['value']."<br><br>";
		}
		die();
	break;
}

if ($_REQUEST['mdtype']=='debug'){
		echo "DEBUG Metadatenanzeige<br>";
		#define table
		echo "<html><table border=\"1\"><br>";
		echo "<tr>";
		#loop for each detail - tag - sometimes there are other tags in there - if one detail has more than one entry! - maybe this must be interpreted but later!
		foreach ($detail_keys as $detailkey) {
			if (in_array($detailkey, $ibus_names)==false){
				echo  "<td >".$detailkey."</td>";
				}
				else {
				echo "<td bgcolor=\"green\">".$md_ident[array_search($detailkey, $ibus_names)]['html']."(".$detailkey.")</td>";
				}
			#echo "</td>";
			echo "<td>";
			echo $detail_array[$detailkey];
			echo "</tr>";
		}
		echo "</table></html>";
}

if ($_REQUEST['mdtype']=='html'){
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">
	<head>
		<title>GeoPortal Rheinland-Pfalz - Metadaten</title>
		<meta name="description" content="Metadaten" xml:lang="de" />
		<meta name="keywords" content="Metadaten" xml:lang="de" />		
		<meta http-equiv="cache-control" content="no-cache">
		<meta http-equiv="pragma" content="no-cache">
		<meta http-equiv="expires" content="0">
		<meta http-equiv="content-language" content="de" />
		<meta http-equiv="content-style-type" content="text/css" />		
<?php
	echo '<meta http-equiv="Content-Type" content="text/html; charset='.CHARSET.'">';	
?>
		<link rel="stylesheet" type="text/css" href="../../../portal/fileadmin/design/css/screen.css" media="screen" />
		<link rel="stylesheet" type="text/css" href="../../../portal/fileadmin/design/css/print.css" media="print" />
	</head>
	<body id="top" class="popup">

	
	<div id="header_gray">
	<a href="javascript:window.print()">Drucken <img src="../../../portal/fileadmin/design/images/icon_print.gif" width="14" height="14" alt="" /></a>
	<a href="javascript:window.close()">Fenster schlie&szlig;en <img src="../../../portal/fileadmin/design/images/icon_close.gif" width="14" height="14" alt="" /></a>
	</div>
	<div id="header_redbottom"></div>
	<div id="header_red"></div>
	
	<div class="content">
<?php
	echo "<h1>Detailinformationen:</h1>";
	#define table
	echo "<html><table class='contenttable-0-wide'>";
	echo "<tr>";
	#loop for each detail - tag - sometimes there are other tags in there - if one detail has more than one entry! - maybe this must be interpreted but later!
	foreach ($detail_keys as $detailkey) {	
		if (in_array($detailkey, $ibus_names)==true){
			echo "<td>".$md_ident[array_search($detailkey, $ibus_names)]['html']."</td>";
			echo "<td>";
			echo display_text($detail_array[$detailkey]);
			echo "</td></tr>";
		}
	}
	echo "</table></html>";
}

if ($_REQUEST['mdtype']=='inspire') {
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">
	<head>
		<title>GeoPortal Rheinland-Pfalz - Metadaten</title>
		<meta name="description" content="Metadaten" xml:lang="de" />
		<meta name="keywords" content="Metadaten" xml:lang="de" />		
		<meta http-equiv="cache-control" content="no-cache">
		<meta http-equiv="pragma" content="no-cache">
		<meta http-equiv="expires" content="0">
		<meta http-equiv="content-language" content="de" />
		<meta http-equiv="content-style-type" content="text/css" />		
<?php
	echo '<meta http-equiv="Content-Type" content="text/html; charset='.CHARSET.'">';	
?>
		<link rel="stylesheet" type="text/css" href="../../../portal/fileadmin/design/css/screen.css" media="screen" />
		<link rel="stylesheet" type="text/css" href="../../../portal/fileadmin/design/css/print.css" media="print" />
	</head>
	<body id="top" class="popup">
	<div id="header_gray">
	<a href="javascript:window.print()">Drucken <img src="../../../portal/fileadmin/design/images/icon_print.gif" width="14" height="14" alt="" /></a>
	<a href="javascript:window.close()">Fenster schlie&szlig;en <img src="../../../portal/fileadmin/design/images/icon_close.gif" width="14" height="14" alt="" /></a>
	</div>
	<div id="header_redbottom"></div>
	<div id="header_red"></div>
	<div class="content">
<?php
	echo "<img border=\"0\" src=\"img/inspire_tr_100.png\" alt=\"INSPIRE Logo\"><h1>INSPIRE Metadaten:</h1>";
	#define table
	echo "<html><table class='contenttable-0-wide'>";
	echo "<tr>";
	#loop for each detail - tag - sometimes there are other tags in there - if one detail has more than one entry! - maybe this must be interpreted but later!
	foreach ($detail_keys as $detailkey) {
		if (in_array($detailkey, $ibus_names)==true){
			if ($md_ident[array_search($detailkey, $ibus_names)]['inspiremandatory']=='true') {
				echo "<td>".$md_ident[array_search($detailkey, $ibus_names)]['inspire']."</td>";
				echo "<td>";
				echo display_text($detail_array[$detailkey]);
				echo "</td></tr>";
			}
		}
	}
	echo "</table></html>";
	echo '<br><b>INSPIRE output not completly implemented!<b><br>';
}
?>
