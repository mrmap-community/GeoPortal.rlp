<?php
require_once(dirname("__FILE__")."/../mapbender/core/globalSettings.php");
require_once(dirname("__FILE__")."/../mapbender/http/classes/class_administration.php");
require_once(dirname("__FILE__")."/../mapbender/lib/class_GetApi.php");

$admin = new administration();

if (!isset($_REQUEST["mb_user_myGui"])) {
    if (!isset($_SESSION["mb_user_gui"]) OR ($_SESSION["mb_user_gui"]=="")) {
        $gui_id = "Geoportal-RLP";
    } else {
	$gui_id = urlencode($_SESSION["mb_user_gui"]);
    }
} else {
    //fix CERT-rlp#2018011151000223
    //validate gui with reg_expr
    if (isset($_REQUEST["mb_user_myGui"]) & $_REQUEST["mb_user_myGui"] != "") {
        //validate
        $testMatch = $_REQUEST["mb_user_myGui"];
	echo $testMatch."<br>";
        $pattern = '/^[A-Za-z0-9_\ -]+$/';
        if (!preg_match($pattern,$testMatch)){
            $gui_id = "Geoportal-RLP";
        } else {
            $gui_id = $testMatch;
            $testMatch = NULL;
        }
    }
}

function sanitizeGetParameter($string) {
    return str_replace("\"","",str_replace(">","",str_replace("<","",$string)));
}

// Use services which are given thru result list call to karten.html
//wms
$LAYERID="";
$LAYERZOOM="";
$LAYERVISIBLE="";
$LAYERQUERYABLE="";
//default to
$GET=$gui_id;
//sanitize parameters with central mapbender class ***************************
$getParams = array(
	"WMC" => sanitizeGetParameter($_GET["WMC"]),
	"WMS" => sanitizeGetParameter($_GET["WMS"]),
	"LAYER" => sanitizeGetParameter($_GET["LAYER"]),
	"FEATURETYPE" => sanitizeGetParameter($_GET["FEATURETYPE"]),
	"GEORSS" => sanitizeGetParameter($_GET["GEORSS"]),
	"KML" => sanitizeGetParameter($_GET["KML"]),
	"GEOJSON" => sanitizeGetParameter($_GET["GEOJSON"]),
	"GEOJSONZOOM" => sanitizeGetParameter($_GET["GEOJSONZOOM"]),
	"GEOJSONZOOMOFFSET" => sanitizeGetParameter($_GET["GEOJSONZOOMOFFSET"]),
	"ZOOM" => sanitizeGetParameter($_GET["ZOOM"])
);

$getApi = new GetApi($getParams);

//sanitize parameters with central mapbender class
//$_GET["WMC"] = $getApi->getWmc();
//TODO WMS?
//$_GET["LAYER"] = $getApi->getLayers();
//$_GET["FEATURETYPE"] = $getApi->getFeaturetypes();
$_GET["GEORSS"] = $getApi->getGeoRSSFeeds();
$_GET["KML"] = $getApi->getKml();
$_GET["GEOJSON"] = $getApi->getGeojson();
$_GET["GEOJSONZOOM"] = $getApi->getGeojsonZoom();
$_GET["GEOJSONZOOMOFFSET"] = $getApi->getGeojsonZoomOffset();
$_GET["ZOOM"] = $getApi->getZoom();
//sanitize parameters with central mapbender class ***************************


if(is_array($_GET["LAYER"])) {
    if (isset($_GET["LAYER"]["id"]) && ($_GET["LAYER"]["id"] != "")) {
        $LAYERID="&LAYER[id]=".$_GET["LAYER"]["id"];

        if (isset($_GET["LAYER"]["zoom"]) && ($_GET["LAYER"]["zoom"] == "1")) {
           $LAYERZOOM="&LAYER[zoom]=".$_GET["LAYER"]["zoom"];
        }

        if (isset($_GET["LAYER"]["visible"]) && ($_GET["LAYER"]["visible"] == "0")) {
           $LAYERVISIBLE="&LAYER[visible]=".$_GET["LAYER"]["visible"];
        } else {
           $LAYERVISIBLE="&LAYER[visible]=1";
        }

        if (isset($_GET["LAYER"]["querylayer"]) && ($_GET["LAYER"]["querylayer"] == "0")) {
           $LAYERQUERYABLE="&LAYER[querylayer]=".$_GET["LAYER"]["querylayer"];
        } else {
           $LAYERQUERYABLE="&LAYER[querylayer]=1";
        }

        $LAYERPARAMS=$LAYERVISIBLE.$LAYERQUERYABLE;

        $GET=$gui_id.$LAYERID.$LAYERZOOM.$LAYERPARAMS;
    } else {
        $oldGet=implode(",", $_GET["LAYER"]);
        $GET=$gui_id."&LAYER=".$oldGet;
    }
}

// wfs
$FEATURETYPE="";

if(isset($_GET["WMS"]) && !is_array($_GET["WMS"])) {
    $WMSURL="&WMS=".urlencode($_GET["WMS"]);
    $GET = $gui_id.$WMSURL;
}

if(isset($_GET["WMC"]) & !is_array($_GET["WMC"])) {
    $WMCURL="&WMC=".urlencode($_GET["WMC"]);
    $GET = $gui_id.$WMCURL;
    //new to log external invocation of mapbender
    $wmcid = htmlspecialchars($_GET["WMC"]);
    if (strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === false) {
        $admin->logClientUsage($_SERVER['HTTP_REFERER'], $wmcid, 3);
    }
}

if(isset($_GET["GEORSS"]) && !is_array($_GET["GEORSS"])) {
    $GEORSSURL="&GEORSS=".urlencode($_GET["GEORSS"]);
    $GET = $gui_id.$GEORSSURL;
}

//KML
if(isset($_GET["KML"]) && !is_array($_GET["KML"])) {
    $KMLURL="&KML=".urlencode($_GET["KML"]);
    $GET = $gui_id.$KMLURL;
}

//allow combi of wmc and georss
if(isset($_GET["GEORSS"]) && !is_array($_GET["GEORSS"]) && isset($_GET["WMC"]) && !is_array($_GET["WMC"])) {
    $GEORSSURL="&GEORSS=".urlencode($_GET["GEORSS"]);
    $WMCURL="&WMC=".urlencode($_GET["WMC"]);
    $GET = $gui_id.$WMCURL.$GEORSSURL;
}

if(is_array($_GET["FEATURETYPE"]) && isset($_GET["FEATURETYPE"]["id"]) && ($_GET["FEATURETYPE"] != "")) {
    $FEATURETYPE="&FEATURETYPE=".$_GET["FEATURETYPE"]["id"];
    $GET = $gui_id.$FEATURETYPE;
}

if (isset($_GET["ZOOM"]) && $_GET["ZOOM"] != "") { 
	//add ZOOM to index.php 
	$GET .= "&ZOOM=".implode(",", $_GET["ZOOM"]); 
}

if(isset($_GET["GEOJSON"])) {
       if (is_array($_GET["GEOJSON"])) {
             foreach ($_GET["GEOJSON"] as $geojson) {
                  $GET .= "&GEOJSON[]=".$geojson;
             }
       } else {
                  $GET .= "&GEOJSON=".$geojson;
       }
}
if(isset($_GET["GEOJSONZOOM"])) {
        $GET .= "&GEOJSONZOOM=".$_GET["GEOJSONZOOM"];
}
if(isset($_GET["GEOJSONZOOMOFFSET"])) {
        $GET .= "&GEOJSONZOOMOFFSET=".$_GET["GEOJSONZOOMOFFSET"];
}
$e = new mb_notice("typo3:karten.html: getparams= lang=de&mb_user_myGui=".$GET);
?>
<script type="text/javascript">if(!document.cookie){alert("Für den Betrieb dieser Seite sind Cookies erforderlich. Bitte aktivieren Sie Cookies in Ihrem Browser.")};</script>
<iframe src="http://<?php print $_SERVER["HTTP_HOST"];?>/mapbender/frames/index.php?lang=de&mb_user_myGui=<?php print $GET;?>" name="geop_map" width="100%" height="800" style="border:0px"></iframe>
