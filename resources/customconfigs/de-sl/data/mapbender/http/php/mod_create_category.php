<?php

function debug($text) {
    if($fh = fopen("/var/log/php/logger.txt", "a")){
        fwrite($fh, "\n\r".$text);
        fclose($fh);
    }
}
require_once(dirname(__FILE__)."/../../core/globalSettings.php");
require_once(dirname(__FILE__)."/../../conf/geoportal.conf");
require_once(dirname(__FILE__)."/../classes/class_connector.php");

GLOBAL $searchId, $uid, $searchResources, $page, $pageN, $pageItemCount;

$start = microtime(true);
$pageItemCount = 2;
$totalcount = 0;
//$pageURL = "http://93.89.10.171/mapbender/php/mod_create_category.php?";
$pageURL = 'http://'.$_SERVER["HTTP_HOST"].'/mapbender/php/mod_create_category.php?'; // URL der Seite

$tempFolder = TMPDIR;
$xslFolder = dirname(__FILE__)."/../extensions/xsl";
//$tempFolder = "C:\\WhereGroup\\tmp";
//$xslFolder = "C:\\WhereGroup\\projects\\kvk-saarland\\trunk\\mapbender\\http\\extensions\\xsl";
//if(Mapbender::session()->get("mb_user_id") == '') {
    $mapBenderURL = INDEX."?".GET_GUEST_GUI;
//} else {
//    $mapBenderURL = INDEX."?".GET_GUEST_GUI."&name=".Mapbender::session()->get("mb_user_name")."&password=".Mapbender::session()->get("mb_user_password");
//}


$resultXml = new DOMDocument('1.0');
$resultXml->encoding = "UTF-8";
$resultXml->formatOutput = true;
$root = $resultXml->createElement("result");
$root = $resultXml->appendChild($root);




$xslt = new XSLTProcessor();
$docXsl = new DOMDocument('1.0');

if (isset($_REQUEST['searchText']) && $_REQUEST['searchText']!=''){
    if (isset($_REQUEST["searchId"]) && $_REQUEST["searchId"] != "" && isset($_REQUEST["uid"]) && $_REQUEST["uid"] != "") {
        $searchId = $_REQUEST["searchId"];
        $uid = $_REQUEST["uid"];
    } else {
        $ID = md5(microtime(true));
        $searchId = $ID;
        $uid = $ID;
    }
    $searchResources = (isset($_REQUEST['searchResources']) && $_REQUEST['searchResources'] !="") ? $_REQUEST['searchResources']: "wms";
    $pageN = (isset($_REQUEST['page']) && $_REQUEST['page'] !="") ? intval($_REQUEST['page']): intval(1);
    $page = "*";//(isset($_REQUEST['page']) && $_REQUEST['page'] !="") ? $_REQUEST['page']: "*";
//    $catalog_number = "*";//(isset($_REQUEST['catalogNumber']) && $_REQUEST['catalogNumber'] !="") ? $_REQUEST['catalogNumber']: "*";
    $sortBy = (isset($_REQUEST['orderby']) && $_REQUEST['orderby'] !="") ? $_REQUEST['orderby']: "title";
    $searchText = $_REQUEST['searchText'];
    $hasConstraints = (isset($_REQUEST['hasConstraints']) && $_REQUEST['hasConstraints'] !="") ? $_REQUEST['hasConstraints']: "true";

    $format = (isset($_REQUEST['format']) && $_REQUEST['format'] !="") ? $_REQUEST['format']: "html";
    $root->appendChild(new DOMAttr("uid", $uid));
    $root->appendChild(new DOMAttr("searchId", $searchId));
    $root->appendChild(new DOMAttr("searchResources", $searchResources));
    $root->appendChild(new DOMAttr("page", $page));
//    $root->appendChild(new DOMAttr("catalog_number", $catalog_number));
    $root->appendChild(new DOMAttr("sortBy", $sortBy));
    $root->appendChild(new DOMAttr("mapBenderURL", $mapBenderURL));

} else {
    $docXsl->load($xslFolder.DIRECTORY_SEPARATOR."catalog_misspars.xsl");
    $xslt->importStylesheet($docXsl);
    $result = $xslt->transformToXml($resultXml);
    echo $result;
    die();
}

$pageURL .= "searchResources=".$searchResources."&orderby=".$sortBy."&searchText=".$searchText;
$hasConstraintsInt = ($hasConstraints== "true") ? "1" : "0";
//debug("hasConstraints:".$hasConstraints.$hasConstraintsInt);
$url = 'http://'.$_SERVER["HTTP_HOST"].'/mapbender/php/mod_start_search.php?searchId='.$searchId.'&uid='.$uid.'&searchText='.$searchText.'&searchResources='.$searchResources.'&hasConstraints='.$hasConstraints.'&categorySearch=true';
//file_get_contents($url);
//if(!isset($_REQUEST['direct']))
 $x = new connector($url);
//debug($url);

$serviceElmName = "wmsresult";

$serviceElt1 = $resultXml->createElement("MapBenderWMS");
$resultXml->documentElement->appendChild($serviceElt1);
$mbwms = new MBWms($searchId, $searchResources, $page, $tempFolder);
$mbwms->readIntoXml($resultXml, $serviceElt1, $serviceElmName);

$serviceElt2 = $resultXml->createElement("OpenSearchWMS");
$resultXml->documentElement->appendChild($serviceElt2);
$oswms = new OpenSearchWms($uid, $page, $tempFolder);
$oswms->readIntoXml($resultXml, $serviceElt2, $serviceElmName);

if($format == "json") {
    $docXsl->load($xslFolder.DIRECTORY_SEPARATOR."catalog_result_json.xsl");
} else {
    $docXsl->load($xslFolder.DIRECTORY_SEPARATOR."catalog_result_html.xsl");
}
$xslt->importStylesheet($docXsl);

$end = microtime(true);
$root->appendChild(new DOMAttr("totalTime", round($end - $start, 2)));
//$root->appendChild(new DOMAttr("startTime", intval($start * 1000)));
$root->appendChild(new DOMAttr("page", $pageN));
$root->appendChild(new DOMAttr("pagesCount", ceil(floatval($totalcount) / floatval($pageItemCount))));
$root->appendChild(new DOMAttr("totalCount", $totalcount));
$root->appendChild(new DOMAttr("pageURL", $pageURL));
$root->appendChild(new DOMAttr("pageItemCount", $pageItemCount));
//debug("time:".($end - $start)."-".$totalcount."-".floatval($totalcount)."-".floatval($pageItemCount)."-".(floatval($totalcount) / floatval($pageItemCount))."-".ceil(floatval($totalcount) / floatval($pageItemCount))."-"."-"."-");

debug($resultXml->saveXml());

$result = $xslt->transformToXml($resultXml);

// data output
echo $result;


class OpenSearchWms {
    var $count = 0;
    var $files = array();
    
    function __construct($uid, $request_p, $folder){
        $osfile = $folder.DIRECTORY_SEPARATOR.$uid."_os.xml";
        $osfiles_ = $folder.DIRECTORY_SEPARATOR.$uid."_os*_".$request_p.".xml";
//        if ($catalog_number == null) {
//            $catalog_number = "*";
//        }
        $countElts = 0;
        if (count(glob($osfile)) == 1) {
            $os = new DOMDocument('1.0');
            $os->load($osfile);
            $xpath = new DOMXPath($os);
            $this->count = $xpath->query("/interfaces/opensearchinterface")->length;
        }
        foreach (glob($osfiles_) as $filename) {
            $this->files[] = $filename;
        }

    }

    public function readIntoXml($resultXml, $serviceElt, $elmName) {
        GLOBAL $totalcount, $pageItemCount, $pageN;
        foreach ($this->files as $filename) {
            $xml = new DOMDocument();
            $xml->load($filename);
//            $osxml = utf8_encode(file_get_contents($filename));
//            $xml->load($osxml);
//            debug("OpenSearchWms xml:".$xml->saveXml());
            $root = $xml->documentElement;
            $xpath = new DOMXPath($xml);
            $path = "/resultlist/result[./wmscapurl/text()!='']";
            $nodes = $xpath->query($path);
            foreach($nodes as $node) {
                $totalcount++;
//                if ($totalcount >= ($pageN - 1) * $pageItemCount
//                        && $totalcount <= ($pageN - 1) * $pageItemCount + $pageItemCount) {
                    $url = $xpath->query("./wmscapurl/text()", $node)->item(0)->wholeText;
                    $title = $xpath->query("./title/text()", $node)->item(0)->wholeText;
                    $abstract = $xpath->query("./abstract/text()", $node)->item(0)->wholeText;
                    $abstract = substr($abstract, 0, strripos(substr($abstract, 0, 50), " "))."...";
                    $resNode = $resultXml->createElement($elmName);
                    $resNode->appendChild(new DOMAttr("title", $title));
                    $resNode->appendChild(new DOMAttr("abstract", $abstract));
                    $resNode->appendChild(new DOMAttr("url", $url));
    //                $resultXml->documentElement->appendChild($resNode);
                    $serviceElt->appendChild($resNode);
//                }
            }
        }
        if (count($this->files) != $this->count) {
            $message = "";
            if (count($this->files) == 0)
                    $message = "Wegen Zeitüberschreitung liefert OpenSearch kein Ergebnis";
            else
                    $message = "Wegen Zeitüberschreitung liefert OpenSearch nur einige Ergebnise";
            $serviceElt->appendChild(new DOMAttr("error", $message));
        }
    }
}

class MBWms {
    var $files = array();

    function __construct($searchId, $searchResources, $page, $folder){
        $pat = $folder.DIRECTORY_SEPARATOR.$searchId."_".$searchResources."_".$page.".xml";
        foreach (glob($pat) as $filename) {
            $this->files[] = $filename;
        }
    }

    public function readIntoXml($resultXml, $serviceElt, $elmName) {
        GLOBAL $hasConstraintsInt, $totalcount, $pageItemCount, $pageN;
        foreach ($this->files as $filename) {
            $xml = new DOMDocument();
            $xml->load($filename);
//            debug("MBWms xml:".$xml->saveXml());
            $root = $xml->documentElement;
            $xpath = new DOMXPath($xml);
            $path = "/wms/srv[./hasConstraints/text()='".$hasConstraintsInt."']";
            $nodes = $xpath->query($path);
            foreach($nodes as $node) {
                $totalcount ++;
//                if ($totalcount >= ($pageN - 1) * $pageItemCount
//                        && $totalcount <= ($pageN - 1) * $pageItemCount + $pageItemCount) {
                    $title = $xpath->query("./title/text()", $node)->item(0)->wholeText;
                    $abstract = $xpath->query("./abstract/text()", $node)->item(0)->wholeText;
                    $abstract = substr($abstract, 0, strripos(substr($abstract, 0, 50), " "))."...";
                    $path = "./layer";
                    $nodesLay = $xpath->query($path, $node);
                    $ids = array();
                    $visibles = array();
                    $queryables = array();
                    foreach($nodesLay as $nodeLay) {
                        $ids[] = $xpath->query("./id/text()", $nodeLay)->item(0)->wholeText;
                        $visibles[] = $xpath->query("./loadable/text()", $nodeLay)->item(0)->wholeText;
                        $queryables[] = $xpath->query("./queryable/text()", $nodeLay)->item(0)->wholeText;
                    }
                    $resNode = $resultXml->createElement($elmName);
                    $resNode->appendChild(new DOMAttr("title", $title));
                    $resNode->appendChild(new DOMAttr("abstract", $abstract));
                    $resNode->appendChild(new DOMAttr("layerId", implode(",", $ids)));
    //                $resNode->appendChild(new DOMAttr("layerVisible", implode(",", $visibles)));
    //                $resNode->appendChild(new DOMAttr("layerQueryable", implode(",", $queryables)));
                    $serviceElt->appendChild($resNode);
//                }
            }
        }
    }
}
?>
