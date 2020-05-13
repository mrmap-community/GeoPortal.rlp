<?php
function mcc_debug($text) {
    if($fh = fopen("/var/log/php/mod_create_category.txt", "a")) {
        fwrite($fh, "\n\r".$text);
        fclose($fh);
    }
}
require_once(dirname(__FILE__)."/../../core/globalSettings.php");
require_once(dirname(__FILE__)."/../../conf/geoportal.conf");
require_once(dirname(__FILE__)."/../../conf/mapbender.conf");
require_once(dirname(__FILE__)."/../classes/class_connector.php");

GLOBAL $searchId, $mb_user_id, $customCategories, $searchResources, $pageN, $itemPerPage;

$start = microtime(true);
$itemPerPage = 10;
$updateTimeMin = 10;
$updateTimeSec = intval($updateTimeMin) * 60;
$removeHelpFiles = false;
$cacheResults = true;

$tempFolder = TMPDIR;

$mapBenderURL = INDEX."?mb_user_myGui=".GUEST_GUI;

$uid = $_SESSION['mb_user_id'];

if (isset($_REQUEST['searchText']) && $_REQUEST['searchText']!='') {
    $ID = md5(microtime(microtime(microtime())));
    $searchId = $ID;
    if (isset($_SESSION['mb_user_id']) && $_SESSION['mb_user_id'] != "") {
        $mb_user_id = $_SESSION['mb_user_id'];
    } else if (Mapbender::session()->get("mb_user_id")!="") {
        $mb_user_id = Mapbender::session()->get("mb_user_id");
    } else {
        $mb_user_id = PUBLIC_USER;
    }
    $searchResources = (isset($_REQUEST['searchResources']) && $_REQUEST['searchResources'] !="") ? $_REQUEST['searchResources']: "wms";
    $pageN = (isset($_REQUEST['page']) && $_REQUEST['page'] !="") ? intval($_REQUEST['page']): intval(1);
    $sortBy = (isset($_REQUEST['orderby']) && $_REQUEST['orderby'] !="") ? $_REQUEST['orderby']: "title";
    $searchText = $_REQUEST['searchText'];
    $hasConstraints = (isset($_REQUEST['hasConstraints']) && $_REQUEST['hasConstraints'] !="") ? $_REQUEST['hasConstraints']: "false";
    $languageCode = "de";
    $format = (isset($_REQUEST['format']) && $_REQUEST['format'] !="") ? $_REQUEST['format']: "html";
} else {
    echo "Missing parameter";
    die();
}

$hasConstraintsInt = ($hasConstraints== "true") ? "1" : "0";

$savedName = "CATEGORY_JSON".$searchText.$searchResources.$hasConstraints.$languageCode;
$savedName = str_replace(" ", "_", $savedName);

if ($cacheResults && Mapbender::session()->exists($savedName)
        && count(glob(Mapbender::session()->get($savedName))) > 0
        && (intval(microtime(true)) - filemtime(Mapbender::session()->get($savedName))) < $updateTimeSec) {
    $sourceFiles = glob(Mapbender::session()->get($savedName));
    $jsonfile = file_get_contents($sourceFiles[0]);
    $sortedJson = json_decode($jsonfile);
    
    $nodes = $sortedJson->srv;

    $srvses = getSrvses($nodes, $pageN, $itemPerPage);

    $md = array(
        "nresults" => count($nodes),
        "p" => $pageN,
        "rpp" => $itemPerPage,
        "genTime" => round(microtime(true) - $start, 2));
    $jsonOutput = array("result" => array("md" => $md,"srv" => $srvses));
} else {
    if(Mapbender::session()->exists($savedName)){
        unlink (Mapbender::session()->get($savedName));
        Mapbender::session()->delete($savedName);
    }

    $url = getMbFiles();
//    mcc_debug("MB :".$url);
    $x = new connector($url);

    $tempJson = array();
    MapBenderWms::checkAndAdd(glob($tempFolder.DIRECTORY_SEPARATOR.$searchId."_wms_*.json"), $languageCode);
    
    MapBenderWmc::checkAndAdd(glob($tempFolder.DIRECTORY_SEPARATOR.$searchId."_wmc_*.json"), $languageCode);

//    MapBenderWms::checkAndAdd(array("./test_wms_1.json"), $languageCode); // test recursive

    OpenSearchWms::checkAndAdd($os_files, $languageCode);
    
    ksort($tempJson);
//    print_r($tempJson);
    $values = array_values($tempJson);
    $keys = array_keys($tempJson);
    if(($fh = fopen($tempFolder.DIRECTORY_SEPARATOR.$searchId."_keys.json","w"))){
        fwrite($fh, json_encode($keys));
        fclose($fh);
    }
    unset($tempJson);
    $totalcount = count($values);
    $sortedSrv = array("srv" => $values);
    if ($removeHelpFiles) {
        $filesToDelete = glob($tempFolder.DIRECTORY_SEPARATOR."*".$searchId."*");
        foreach ($filesToDelete as $fileToDelete) {
                unlink ($fileToDelete);
        }
    }
    
    $resultFile = $tempFolder.DIRECTORY_SEPARATOR.$searchId.$savedName.".json";
    if($cacheResults && $totalcount > 0){
        if(($fh = fopen($resultFile,"w"))){
            fwrite($fh, json_encode($sortedSrv));
            fclose($fh);
        }
        unset($sortedSrv);
    }
    
    $srvses = getSrvses($values, $pageN, $itemPerPage);
    unset($values);
    $md = array(
        "nresults" => $totalcount,
        "p" => $pageN,
        "rpp" => $itemPerPage,
        "genTime" => round(microtime(true) - $start, 2));
    $jsonOutput = array("result" => array("md" => $md,"srv" => $srvses));
    if($totalcount > 0){
        Mapbender::session()->set($savedName, $resultFile);
    }
}

die(json_encode($jsonOutput));

class OpenSearchWms {
    var $filename;

    function __construct($filename) {
        $this->filename = $filename;
    }

    public static function checkAndAdd($files, $languageCode) {
        GLOBAL $tempJson;
//        debug("OS checkAndAdd:".  implode(",", $files));
        $i = 200;
        $completed = array();
        $num = 0;
        while($i != 0) {
            foreach ($files as $file){
//                mcc_debug("OS read:".$i." ".$file);
                if (!in_array($file, $completed)) {
                    if(count(glob($file)) > 0) {
//                        debug($i." file found ".$file);
                        $oswms = new OpenSearchWms($file);
                        $oswms->readIntoArray($num, $tempJson, $languageCode);
                        $completed[] = $file;
                    }
                }
            }
            if (count($files) == count($completed)) {
                break;
            } else {
                usleep(200000);
            }
            $i--;
        }
        foreach ($files as $file){
            if (!in_array($file, $completed)) {
//                mcc_debug("OS file not found:".$file);
            }
        }
    }

    public function readIntoArray($num, $languageCode) {
        GLOBAL $itemPerPage, $pageN, $tempJson, $mapBenderURL;
        $xml = new DOMDocument();
        $filteredXML->encoding = "UTF-8";
        $filteredXML->formatOutput = true;
        $xml->load($this->filename);

        $root = $xml->documentElement;
        $xpath = new DOMXPath($xml);
        $path = "/resultlist/result[./wmscapurl/text()!='']";
        $nodes = $xpath->query($path);
        foreach($nodes as $node) {
            $url = $xpath->query("./wmscapurl/text()", $node)->item(0)->wholeText;
            $title = $xpath->query("./title/text()", $node)->item(0)->wholeText;
            $title = str_replace('"',"'", $title);
            $abstract = $xpath->query("./abstract/text()", $node)->item(0)->wholeText;
            $abstract = $abstract;
            $source = $xpath->query("./source/text()", $node)->item(0)->wholeText;
            $source = $source;
            $jsonAttrs =array(
                "type" => "WMS",
                "id" => getAsId($title."_oswms".$num),
                "title" => $title,
                "abstr" => $abstract === null ? "" : $abstract,
                "source" => $source,
                "mburl" => $mapBenderURL.'&WMS='.$url);
            $tempJson[$jsonAttrs["id"]] = $jsonAttrs;
            $num++;
        }
    }
}

class MapBenderWms {
    var $filename;

    function __construct($filename) {
        $this->filename = $filename;
    }

    public static function checkAndAdd($files, $languageCode) {
        $i = 200;
        $completed = array();
        while($i != 0) {
            foreach ($files as $file){
//                mcc_debug("MB WMS read:".$i." ".$file);
                if (!in_array($file, $completed)) {
                    if(count(glob($file)) > 0) {
//                        debug("MB:".$i." file found ".$file);
                        $oswms = new MapBenderWms($file);
                        $oswms->readIntoArray($file, $languageCode);
                        $completed[] = $file;
                    }
                }
            }
            if (count($files) == count($completed)) {
                break;
            } else {
                usleep(200000);
            }
            $i--;
        }
        foreach ($files as $file){
            if (!in_array($file, $completed)) {
//                mcc_debug("MB WMS file not found:".$file);
            }
        }
    }

    public function readIntoArray($file, $languageCode) {
        GLOBAL $hasConstraintsInt, $itemPerPage, $pageN, $tempFolder, $tempJson, $mapBenderURL;
        $jsonfile = file_get_contents($file);
        $jsonarray = json_decode($jsonfile);
        unset($jsonfile);
        $num = 0;
        foreach($jsonarray->wms->srv as $srv) {
            foreach($srv->layer as $nodeLayer) {
//                mcc_debug(print_r($nodeLayer, 1));
                $layerAttr = array(
                    "type" => "WMS",
                    "uuid" => $nodeLayer->uuid,
                    "id" => getAsId($nodeLayer->title."_mbwms".$nodeLayer->id.$num),
                    "title" => $nodeLayer->title,
                    "abstr" => $nodeLayer->abstract === null ? "" : $nodeLayer->abstract,
                    "source" => "MB",
                    "mburl" => $mapBenderURL.'&LAYER[id]='.$nodeLayer->id,
                    "logged" => (boolean) $nodeLayer->logged,
                    "mdLink" => $nodeLayer->mdLink,
                    "previewURL" => $nodeLayer->previewURL ? $nodeLayer->previewURL : null,
                    "downloadOptions" => $nodeLayer->downloadOptions,
                    "permission" => $nodeLayer->permission,
                    "bbox" => $nodeLayer->bbox);
                if(isset($nodeLayer->layer)){
                    $layers = $this->readLayers($nodeLayer->layer);
                    $layerAttr["subLayer"] = $layers;
                }
                $tempJson[$layerAttr["id"]] = $layerAttr;
                $num++;
            }
        }
    }
    private function readLayers($nodesLay, $layers = array()){
        GLOBAL $mapBenderURL;
        foreach($nodesLay as $nodeLayer) {
            $layerAttr = array(
                "type" => "WMS",
                "uuid" => $nodeLayer->uuid,
                "id" => "mbwms".getAsId($nodeLayer->title).$nodeLayer->id,
                "title" => $nodeLayer->title,
                "abstr" => $nodeLayer->abstract === null ? "" : $nodeLayer->abstract,
                "source" => "MB",
                "mburl" => $mapBenderURL.'&LAYER[id]='.$nodeLayer->id,
                "logged" => (boolean) $nodeLayer->logged,
                "previewURL" => $nodeLayer->previewURL,
                "mdLink" => $nodeLayer->mdLink,
                "downloadOptions" => $nodeLayer->downloadOptions,
                "permission" => $nodeLayer->permission,
                "bbox" => $nodeLayer->bbox);
            if(isset($nodeLayer->layer)){
                $layerAttr["subLayer"] = $this->readLayers($nodeLayer->layer);
            }
            $layers[] = $layerAttr;
        }
        return $layers;
    }
}


class MapBenderWmc {
    var $filename;

    function __construct($filename) {
        $this->filename = $filename;
    }

    public static function checkAndAdd($files, $languageCode) {
//        mcc_debug("MB WMC finde file");
        $i = 200;
        $completed = array();
        while($i != 0) {
            foreach ($files as $file){
//                mcc_debug("MB WMC read:".$i." ".$file);
                if (!in_array($file, $completed)) {
                    if(count(glob($file)) > 0) {
//                        debug("MB:".$i." file found ".$file);
                        $oswmc = new MapBenderWmc($file);
                        $oswmc->readIntoArray($file, $languageCode);
                        $completed[] = $file;
                    }
                }
            }
            if (count($files) == count($completed)) {
                break;
            } else {
                usleep(200000);
            }
            $i--;
        }
        foreach ($files as $file){
            if (!in_array($file, $completed)) {
//                mcc_debug("MB WMC file not found:".$file);
            }
        }
    }

    public function readIntoArray($file, $languageCode) {
        GLOBAL $hasConstraintsInt, $itemPerPage, $pageN, $tempFolder, $tempJson, $mapBenderURL;
        $jsonfile = file_get_contents($file);
        $jsonarray = json_decode($jsonfile);
        $nodes = $jsonarray->wmc->srv;
        $num = 0;
        foreach($nodes as $node) {
            $jsonAttrs =array(
                "type" => "WMC",
                "id" => getAsId($node->title."_mbwmc".$node->id.$num),
                "title" => $node->title,
                "abstr" => $node->abstract === null ? "" : $node->abstract,
                "source" => "MB",
                "date" => $node->date,
                "respOrg" => $node->respOrg,
                "logoUrl" => $node->logoUrl,
                "mdLink" => $node->mdLink,
                "previewURL" => $node->previewURL,
                "iso3166" => $node->iso3166,
                "bbox" => $node->bbox,
                "mburl" => $mapBenderURL.'&WMC='.$node->id);
            $tempJson[$jsonAttrs["id"]] = $jsonAttrs;
            $num++;
        }
    }
}


function getOS_Files () {
    GLOBAL $searchId,$searchText,$tempFolder;
    $con = db_connect(DBSERVER,OWNER,PW);
    db_select_db(DB,$con);
    #***get the information out of the mapbender-db
    #get urls to search interfaces (opensearch):
    $sql_os = "SELECT * from gp_opensearch ORDER BY os_id";
    #do db select
    $res_os = db_query($sql_os);
    #initialize count of search interfaces
    $os_files = array();
    $catalog_number = 1;
    $page_number = 1;
    $hitscount = 100000;
    $start = microtime(true);
    while($row_os = db_fetch_array($res_os)) {
        #OpenSearch Search over distributed instances of Portal-U - configuration in mapbender database
        $exec = "php5 /data/mapbender/http/geoportal/mod_readOpenSearchResults_Single.php";
        $exec .= " '".$searchId."'";
        $exec .= " '".$searchText."'";
        $exec .= " '".$row_os["os_id"]."'";
        $exec .= " '".$row_os["os_name"]."'";
        $exec .= " '".$row_os["os_url"]."'";
        $exec .= " '".$hitscount."'"; // hits count
        $exec .= " '".$row_os["os_standard_filter"]."'";
        $exec .= " '".$row_os["os_version"]."'";
        $exec .= " '".$catalog_number."'";
        $exec .= " '".$page_number."'"; //page number
        $exec .= " '".$tempFolder."/".$searchId."_os".$catalog_number."_".$page_number.".xml'"; //page number."/".$searchId."_os".$catalog_number."_".$request_p.".xml"
        $exec .= " '".$row_os["os_name"]."'";
        $exec .= " > ".$tempFolder."/request_".$searchId."_opensearch.xml &";

        $os_files[] = $tempFolder."/".$searchId."_os".$catalog_number."_".$page_number.".xml";

        exec($exec);
        $catalog_number++;
    }
    return $os_files;
}



function getMbFiles() {
    GLOBAL $searchId, $searchText, $languageCode, $mb_user_id, $hasConstraints, $customCategories, $searchResources, $sortBy,$tempFolder;
    $con = db_connect(DBSERVER,OWNER,PW);
    db_select_db(DB,$con);
    #***get the information out of the mapbender-db
    #get urls to search interfaces (opensearch):
    $sql_mb = "SELECT custom_category_id FROM custom_category WHERE custom_category_code_".$languageCode." LIKE $1  LIMIT 1";
    $v = array($searchText);
    $t = array("s");
    $res = db_prep_query($sql, $v, $t);

    while ($row = db_fetch_assoc($res)) {
        $custom_category_id = $row["custom_category_id"];
    }
    $searchFilter = 'languageCode='.$languageCode;
    $searchFilter .= '&searchEPSG=EPSG:31466';
    $searchFilter .= '&userId='.$mb_user_id;
    $searchFilter .= '&resultTarget=file';
    $searchFilter .= '&hostName='.$_SERVER['HTTP_HOST'];

    $searchFilter .= '&searchText='.str_replace(' ',',',$searchText);
    $searchFilter .= '&customCategoryId='.$custom_category_id;

    $searchFilter .= '&hasConstraints='.$hasConstraints;
    $searchFilter .= '&searchResources='.$searchResources;
    $searchFilter .= '&orderBy='.$sortBy;
    $searchFilter .= '&uid='.$mb_user_id;
    $searchFilter .= '&maxResults=250';
    $searchFilter .= '&resultTarget=file';

    $url = 'http://'.$_SERVER['HTTP_HOST'].'/mapbender/php/mod_callMetadata.php?searchId='.$searchId.'&'.$searchFilter;
    return $url;
}

function getAsId($text) {
    $newtext = html_entity_decode($text);
    $newtext = ereg_replace("[^a-zA-Z0-9\_\-]","", $newtext);
    return $newtext;
}

function getSrvses($nodes, $pageN, $itemProPage){
    $num = 0;
    $start = ($pageN - 1) * $itemProPage;
    $end = $start + $itemProPage;
    $result = array();
    foreach($nodes as $node) {
        if ($num >= $start && $num < $end){
            $result[] = $node;
        }
        $num++;
    }
    return $result;
}
//
//function getWordToSort ($languageCode, $word) {
//    if($languageCode == "de") {
//        $string = utf8_encode($word);
//        $result = "";
//        for($i=0; $i<strlen($word); $i++) {
//            $ch0 = substr($string, $i, 1);
//            if(ord($ch0)==195 && ($i + 3) < strlen($string)) {
//                $ord1 = ord(substr($string, $i+1, 1));
//                $ord2 = ord(substr($string, $i+2, 1));
//                $ord3 = ord(substr($string, $i+3, 1));
//                if($ord1 == 131 && $ord2 == 194) {
//                    switch($ord3){
//                        case 164:// auml
//                            $result .= "ae";
//                            $i += 3;
//                            break;
//                        case 182:// ouml
//                            $result .= "oe";
//                            $i += 3;
//                            break;
//                        case 188:// uuml
//                            $result .= "ue";
//                            $i += 3;
//                            break;
//                        case 159:// eszet
//                            $result .= "ss";
//                            $i += 3;
//                            break;
//                        case 132:// Auml
//                            $result .= "Ae";
//                            $i += 3;
//                            break;
//                        case 150:// Ouml
//                            $result .= "Oe";
//                            $i += 3;
//                            break;
//                        case 156:// Uuml
//                            $result .= "Ue";
//                            $i += 3;
//                            break;
//                        default:
//                            $i += 3;
//                            break;
//                    }
//                }
//            } else {
//                $result .= $ch0;
//            }
//        }
//
//        return $result;
//    } else {
//        return $word;
//    }
//}
?>
                            
                