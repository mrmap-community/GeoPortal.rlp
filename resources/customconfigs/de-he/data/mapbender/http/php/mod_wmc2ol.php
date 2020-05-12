<?php
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2, or (at your option)
# any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
#http://www.geoportal.rlp.de/mapbender/php/mod_wmc2ol.php?wmc_id=45_1291218568&withDigitize=1&xID=xCoord&yID=yCoord&LayerSwitcher=1
#http://www.geoportal.rlp.de/mapbender/php/mod_wmc2ol.php?wmc_id=45_1291218568&GEORSS=1&LayerSwitcher=1

require_once(dirname(__FILE__)."/../../core/globalSettings.php");
require_once(dirname(__FILE__)."/../classes/class_user.php");
require_once(dirname(__FILE__)."/../classes/class_administration.php");

$user = new User();
$admin = new administration();
$userId = $user->id;
//check for parameter wmc_id
if(!isset($_GET["wmc_id"])){
	echo 'Error: wmc_id not requested<br>';
	die();	
	//must leave script
}

function _e ($str) {
	return htmlentities($str, ENT_QUOTES, CHARSET);
}

if (!$userId) {
	$userId = PUBLIC_USER;
}



if (isset($_REQUEST["wmc_id"]) & $_REQUEST["wmc_id"] != "") {
	//validate to csv integer list
	$testMatch = $_REQUEST["wmc_id"];
	$pattern = '/^[0-9_]*$/';
	if (!preg_match($pattern,$testMatch)){ 
		//echo 'wmc_id: <b>'.$testMatch.'</b> is not valid.<br/>'; 
		echo 'Parameter <b>wmc_id</b> is not valid (integer_integer or integer).<br/>'; 
		die(); 		
	}
	$wmc_id = $testMatch;
	$testMatch = NULL;
}




//dummy parameter for drawing georss points:

$pointRadius = "10";
$fillColor = "#666666"; //grey

if (isset($_REQUEST["pointRadius"]) & $_REQUEST["pointRadius"] != "") {
	//validate to csv integer list
	$testMatch = $_REQUEST["pointRadius"];
	$pattern = '/^[0-9]{2}|^[1-9]{1}$/';		
	if (!preg_match($pattern,$testMatch)){
		//echo 'pointRadius: <b>'.$testMatch.'</b> is not valid.<br/>'; 
		echo 'Parameter <b>pointRadius</b> is not valid (integer).<br/>'; 
		die();
	}
	$pointRadius = $testMatch;
	$testMatch = NULL;
}


if (isset($_REQUEST["fillColor"]) & $_REQUEST["fillColor"] != "") {
	//validate to csv integer list
	$testMatch = $_REQUEST["fillColor"];
	$pattern = '/^#[0-9a-f]{3}|#[0-9a-f]{6}$/';
	if (!preg_match($pattern,$testMatch)){
		//echo 'fillColor: <b>'.$testMatch.'</b> is not valid.<br/>'; 
		echo 'Parameter <b>fillColor<b> is not valid (html color code).<br/>'; 
		die(); 
 	}
	$fillColor = $testMatch;
	$testMatch = NULL;
}

if (isset($_REQUEST["markerUrl"]) & $_REQUEST["markerUrl"] != "") {
	//validate to csv integer list
	$testMatch = $_REQUEST["markerUrl"];
	$pattern = '/^[\d,]*$/';
 	if (!preg_match($pattern,$testMatch)){
		//echo 'markerUrl: <b>'.$testMatch.'</b> is not valid.<br/>';
		echo 'Parameter </b>markerUrl</b> is not valid.<br/>'; 
		die(); 
 	}
	$fillColor = $testMatch;
	$testMatch = NULL;
}

//**************************************************************************
//functions which may be integrated from class_administration
function getWmsGetMapUrl($wmsId){
	$sql = "SELECT wms_getmap FROM wms WHERE wms_id =$1";
	$v = array($wmsId);
	$t = array("i");
	$res = db_prep_query($sql,$v,$t);
	if ($row = db_fetch_array($res)){
		return $row['wms_getmap'];
	} else {
		return false;
	}
}

//Function to pull layer names from database. They may have been updated since the wmc have been saved!
function getLayerNames($wmsId){
	$sql = "SELECT layer_id, layer_name FROM layer WHERE fkey_wms_id = $1";
	$v = array($wmsId);
	$t = array("i");
	$res = db_prep_query($sql,$v,$t);
	while($row = db_fetch_array($res)){
		$layerNames[$row["layer_id"]] = $row["layer_name"];
	}
	if (count($layerNames) > 0) {
		return $layerNames;
	} else {
		return false;
	}
}

//end of functions which may be included from class_administration in next versions
//**************************************************************************


//Function to create an OpenLayers Javascript from a mapbender wmc document
function createOlFromWMC_id($wmc_id, $pointRadius, $fillColor){
	//$myWmc = new wmc();
	global $user;
	global $userId;
	global $admin;
	//Get WMC out of mb Database
	$sql = "SELECT wmc, wmc_serial_id FROM mb_user_wmc WHERE wmc_serial_id = $1";
	$res = db_prep_query($sql, array($wmc_id), array("s"));
	$wmc = db_fetch_row($res);
	//control if wmc was found else use old wmc_id
	if (!$wmc[0]) {
		$sql = "SELECT wmc, wmc_serial_id FROM mb_user_wmc WHERE wmc_id = $1";
		$res = db_prep_query($sql, array($wmc_id), array("s"));
		$wmc = db_fetch_row($res);
		//echo "Wmc with this id was not found in Database!<br>";
		//die;
	}
	//generate wmc object and update urls of services in this object:
	$wmcId = $wmc[1];
	//$myWmc->createFromDb($wmcId);
	//$updatedWmc = $myWmc->updateUrlsFromDb();//TODO: check why this functions need a session??
	
	//Read out WMC into XML object
	$xml=simplexml_load_string($wmc[0], "SimpleXMLElement", LIBXML_NOBLANKS);
	if ($_REQUEST['withoutBody'] == '1') { 

	} else {
		//generate general html data
		$html='';
		$html.="<html xmlns='http://www.w3.org/1999/xhtml'>\n";
		$html.="<head>\n";
	}
	//define global variables for extent out of WMC File
	$windowWidth=$xml->General->Window->attributes()->width;
	$windowHeight=$xml->General->Window->attributes()->height;
	$htmlWidth=$windowWidth+40;
	$htmlHeight=$windowHeight+70;
	//define CSS 
	$html.="<style type='text/css'>\n";
	$html.=" #map {\n";$html.="width: ".$windowWidth."px;\n";
	$html.="height: ".$windowHeight."px;\n";
	$html.="border: 0px solid black;\n";
	$html.="overflow:visible;\n";
	$html.="}\n";
	$html.=" #srs {\n";
	$html.="font-size: 80%;\n";
	$html.="color: #444;\n";
	$html.="}\n";
	$html.=" #showpos {\n";
	$html.="font-size: 80%;\n";
	$html.="color: #444;\n";
	$html.="}\n";
	$html.=".olControlAttribution {\n";
  	$html.="bottom: 2px !important;\n";
	$html.="margin: -3px;\n";
	$html.="padding-left: 15px;\n";
	$html.="padding-right: 7px;\n";
	$html.="padding-top: 9px;\n";
	$html.="padding-bottom: 7px;\n";
	$html.="background-color: white;\n";
	$html.="font-size:7px;\n";
	$html.="font-family: Verdana;\n";
	$html.="color: black;\n";
  	$html.="}\n";




	$html.="</style>\n";
	//don't show any html, title and/or body tag when integrate it into external website
	if ($_REQUEST['withoutBody'] == '1') { 	
	} else {
		//Generate Title
		$html.="<meta http-equiv=\"Content-Type\" content=\"text/html; charset=".CHARSET."\">\n";
		$html.="<title>".$xml->General->Title."</title>\n";
	}
	//include OL libs from local source - must be minimized, for new functions use the newest openlayers lib - directly from ol site
	$e = new mb_notice('georss request : '.$_REQUEST['GEORSS']);
	if(isset($_REQUEST["withDigitize"]) or isset($_REQUEST["GEORSS"])){
		if(($_REQUEST["withDigitize"]=='1') or ($_REQUEST["GEORSS"]!='')){
			#$html.="<script src='http://".$_SERVER['HTTP_HOST']."/mapbender/extensions/OpenLayers-2.8/OpenLayers.js'></script>\n";
			$html.="<script src='../extensions/OpenLayers-2.9.1/OpenLayers.js'></script>\n";
		}
	} else {
		//use the minimized version
		//$html.="<script src='../../openlayers/build/geoportal_ol_geoportal.js'></script>\n";
		$html.="<script src='../extensions/OpenLayers-2.9.1/OpenLayers.js'></script>\n"; //TODO minimize this or use other lib
	}
	//begin part for javascript code
	$html.="<script type='text/javascript'>\n";
	//check for queryable layers
	$layer_array_queryable=array();
	$layer_array=$xml->LayerList->Layer;
	if(isset($_REQUEST["withDigitize"])){
		if($_REQUEST["withDigitize"]=='1'){
			$html.="var map, controls, select, selectedFeature;
;\n";
		} 
	} else {
		$html.="var map, vectors, controls,select, selectedFeature;
;\n";
	}
	//initialize markers
	$html.="var markers = new OpenLayers.Layer.Markers(\"Markers\", {'calculateInRange': function() { return true; }});\n";
	//initialize logo
	if(isset($_REQUEST["withoutLogo"])){
		if($_REQUEST["withoutLogo"]=='1'){
			$html.="var logo = \"<a href = 'mod_getWmcDisclaimer.php?&id=".$wmcId."&languageCode=de&withHeader=true&hostName=".$hostName."' target='_blank'>"._mb('Terms of use')."</a>\";\n";
		}
	} else {
		$html.="var logo = \"<a href = 'mod_getWmcDisclaimer.php?&id=".$wmcId."&languageCode=de&withHeader=true&hostName=".$hostName."' target='_blank'>"._mb('Terms of use')."</a>\";\n";	
	}
	//$html.="var logo = \"<a href = 'http://www.geoportal.rlp.de' target='_blank'><img src='../img/logo_geoportal_neu.png' height='20' width='120' alt='Geoportal Logo'/></a><br><a href = 'mod_getWmcDisclaimer.php?&id=".$wmcId."&languageCode=de&hostName=".$hostName."' target='_blank'>"._mb('Terms of use')."</a>\";\n";
	//check for some queryable layer in web map context document
	$someLayerQueryable=false;
	for ($i=0; $i<count($layer_array); $i++) {
		$html.="var layer".$i.";\n";
		$mb_extensions=$xml->LayerList->Layer[$i]->Extension->children('http://www.mapbender.org/context');
		$layer_array_queryable[$i]=$mb_extensions->querylayer;
		if (($layer_array_queryable[$i]=='1') and ($xml->LayerList->Layer[$i]->attributes()->hidden=='0') and ($mb_extensions->layer_parent!='')){
			$someLayerQueryable=true;
		} else {
			$layer_array_queryable[$i]=0;
		}
	}
	//define special BBOX
	$out_box=0.3;
	//get min/max extents for olbox
	$minx = $xml->General->BoundingBox->attributes()->minx;
	$miny = $xml->General->BoundingBox['miny'];
	$maxx = $xml->General->BoundingBox['maxx'];
	$maxy = $xml->General->BoundingBox['maxy'];
	//Get epsg code out of WMC
	$xml_epsg=str_replace('EPSG:','',$xml->General->BoundingBox['SRS']);
	$centralx=floor(($maxx+$minx)/2);
	$centraly=floor(($maxy+$miny)/2);
	$dx=$maxx-$minx;//in meters
	$dy=$maxy-$miny;//in meters
	//define zoom levels TODO: maybe those are not needed any longer - check it and maybe forget them cause we work with wms
	$numberZoomLevels=20;
	//define central position in projected system
	$html.="var lat = $centralx;\n"; 
	$html.="var lon = $centraly;\n";
	$centralPointx=($maxx+$minx)/2;
	$centralPointy=($maxy+$miny)/2;
	//startzoom faktor - check if usefull
	$html.="var zoom = 10;\n";
	$html.="\n";
	//start function for initialize client
	$html.="function initGeoportal(){\n";
	//define ol central map object	
	$html.="	map = new OpenLayers.Map('map', { controls: [\n";
	$html.="		new OpenLayers.Control.Navigation(\n";
	if ($_REQUEST["disableMouseScroll"] == '1') {
		$html.="			{zoomWheelEnabled : false}\n";
	}
	$html.="			),\n";
	$html.="		new OpenLayers.Control.PanZoom(),\n";
	$html.="		new OpenLayers.Control.ArgParser(),\n";
        $html.="		new OpenLayers.Control.Attribution()\n";
	$html.="		] });\n";
	//define options for ol map object	
	$html.="	var options = {\n";
	$html.="		projection: \"".$xml->General->BoundingBox['SRS']."\",\n";
	if ($xml->General->BoundingBox['SRS']=='EPSG:4326'){
		echo 'Please choose an other coordinatereferencesystem. Converting Scales to Geographic Coordinates is not yet implemented!';
		return; 
	}
	$html.="		units: \"m\",\n";
	$html.="		fractionalZoom : true,\n";
	$html.="		numZoomLevels: ".$numberZoomLevels.",\n";
	$html.="		minResolution: 0.01\n";
	$html.="	};\n";
	//If GET params mb_myBBOX and mb_myBBOXEpsg are given****
	//Before defining the bounds check if mb_myBBOX and mb_myBBOXEpsg are defined.
	//Check for given mb_myBBOX
	if(isset($_REQUEST["mb_myBBOX"])){
		//Check for numerical values for BBOX
		$array_bbox=explode(',',$_REQUEST["mb_myBBOX"]);
		if ((is_numeric($array_bbox[0])) and (is_numeric($array_bbox[1])) and (is_numeric($array_bbox[2])) and (is_numeric($array_bbox[3])) ) {
			$minx_new=$array_bbox[0];
			$miny_new=$array_bbox[1];
			$maxx_new=$array_bbox[2];
			$maxy_new=$array_bbox[3];
			$centralx=($maxx_new+$minx_new)/2;
			$centraly=($maxy_new+$miny_new)/2;
			if(isset($_REQUEST["mb_myBBOXEpsg"])){
				//Check epsg
				$targetEpsg=intval($_REQUEST["mb_myBBOXEpsg"]);
				if (($targetEpsg >= 1) and ($targetEpsg <= 50001)) {
					#echo "is in the codespace of the epsg registry\n";
					} else {
					#echo "is outside\n";
					echo "alert('The REQUEST parameter mb_myBBOXEpsg is not in the epsg realm - please define another EPSG Code.');";
					return;
				}
				//Check if epsg is equal to BBOXEpsg
				if ($_REQUEST["mb_myBBOXEpsg"]!=$xml_epsg){
					//Transform the given BBOX to epsg of WMC
					$sql= "select asewkt(transform(GeometryFromText ( 'LINESTRING ( ".$array_bbox[0]." ".$array_bbox[1].",".$array_bbox[2]." ".$array_bbox[3]." )', $targetEpsg ),".intval($xml_epsg)."))";
					$e = new mb_notice("mod_wms2ol.php: sql (transform)=".$sql);
					$res = db_query($sql);
					//read out result
					$text_bbox = db_fetch_row($res);
					$e = new mb_notice("mod_wms2ol.php: text_bbox=".$text_bbox[0]);
					$pattern = '~LINESTRING\((.*)\)~i';
					preg_match($pattern, $text_bbox[0], $subpattern);
					$e = new mb_notice("mod_wms2ol.php: subpattern=".$subpattern[1]);
					//exchange blancspaces
					$new_bbox = str_replace(" ", ",", $subpattern[1]);
					//set new BBOX
					$array_bbox_new=explode(',',$new_bbox);
					$minx_new=$array_bbox_new[0];
					$miny_new=$array_bbox_new[1];
					$maxx_new=$array_bbox_new[2];
					$maxy_new=$array_bbox_new[3];
					$centralx=($maxx_new+$minx_new)/2;
					$centraly=($maxy_new+$miny_new)/2;
				}
				else
				{
				//Set the new BBOX unaltered
				$minx_new=$array_bbox[0];
				$miny_new=$array_bbox[1];
				$maxx_new=$array_bbox[2];
				$maxy_new=$array_bbox[3];
				$centralx=($maxx_new+$minx_new)/2;
				$centraly=($maxy_new+$miny_new)/2;
				}
			}
		}
		else
		{
			echo "alert('The REQUEST parameters for mb_myBBOX are not numeric - please give numeric values!');";
			return;
		}
	} 
	//*************************************************************************************
	//define the javascript variable bounds	
	$html.="	var bounds = new OpenLayers.Bounds(".$minx.",".$miny.",".$maxx.",".$maxy.");\n";
	//if some layer defined, create base layer -> first layer in the wmc document
	if (count($layer_array) != 0){
		//get layer id for the base layer - this should be the first layer which is not hidden - in the wmc this should be visible (active)!!
		//first get this id:
		for ($i=0; $i<count($layer_array); $i++) {
			if ($xml->LayerList->Layer[$i]->attributes()->hidden=='0'){ 
				$firstLayerId = $i;
				break;
			}
		}
		$i = $firstLayerId;
		$html.="	layer0 = new OpenLayers.Layer.WMS( \"".str_replace("'","",str_replace('"','',$xml->LayerList->Layer[$i]->Title))."\",\n";
		$extensions=$xml->LayerList->Layer[$i]->Extension->children('http://www.mapbender.org/context');
		$layer_id=dom_import_simplexml($extensions->layer_id)->nodeValue;
		$layer_name=$xml->LayerList->Layer[$i]->Name;
		$wms_id=dom_import_simplexml($extensions->wms_id)->nodeValue;
		$layerNames = getLayerNames($wms_id);
		if (isset($layerNames[(string)$layer_id]) && $layerNames[(string)$layer_id] != "") {
			$layer_name = $layerNames[(string)$layer_id];
		}
		$has_permission=$admin->getLayerPermission($wms_id, $layer_name, $userId);
		if ($has_permission || $layer_id==''){
			$getMapUrl = $xml->LayerList->Layer[$i]->Server->OnlineResource->attributes('http://www.w3.org/1999/xlink')->href;
			if (strpos($getMapUrl, OWSPROXY) === false) {
				if (getWmsGetMapUrl($wms_id) != false) {
					$e = new mb_notice("mod_wmc2ol.php: update GetMap found in database - change url to :".$getMapUrl);
					$getMapUrl = getWmsGetMapUrl($wms_id);
				}
			}
			$html.="		\"".$getMapUrl."\",\n";
			$html.="		{\n";
			//output for testing layer names
			//foreach ($layerNames as $key => $value) {
    			//	$e = new mb_exception("Key: $key; Value: $value");
			//}
			if (isset($layerNames[(string)$layer_id]) && $layerNames[(string)$layer_id] != "") {
				$html.="		layers: \"".$layerNames[(string)$layer_id]."\",\n";
			} else {
				$html.="		layers: \"".$xml->LayerList->Layer[$i]->Name."\",\n";
			}
			//get FormatList and the current active format -> TODO: make a function for getting actual format for request
			$format='png';
			foreach ($xml->LayerList->Layer[$i]->FormatList->Format as $current_format) {
				if ($current_format->attributes()->current=='1'){    
					$format=$current_format;
				}
			}
			$html.="		format: \"".$format."\",\n";
			$html.="		transparent: \"On\"\n";
			$html.="		},\n";
			$html.="		{\n";
			$html.="		maxExtent: new OpenLayers.Bounds(".$minx.",".$miny.",".$maxx.",".$maxy."),\n";
			// then check map.baseLayer.resolutions[0] for
			// a reasonable value.
			$html.="		projection: \"".$xml->General->BoundingBox['SRS']."\",\n";  
			$html.="		units: \"m\",\n"; 
			$html.="		numZoomLevels: ".$numberZoomLevels.",\n";
			$minScale=dom_import_simplexml($extensions->gui_minscale)->nodeValue;
			$maxScale=dom_import_simplexml($extensions->gui_maxscale)->nodeValue;
			$maxScale=$extensions->guiScaleHint->attributes()->max;//this set the maxscale to unknown - for the baselayer
			if (!$maxScale){
				$maxScale='10000000';
			}
			if (!$minScale){
				$minScale='0.1';
			}
			$html.="		minScale: ".$minScale.",\n"; 
			$html.="		maxScale: ".$maxScale.",\n"; 
			$html.="		singleTile: true,\n";
			$html.="		attribution: logo\n";
			//Only necessary for working with scales.
			$html.="	} );\n";
			$html.="	map.addLayer(layer0);\n";
		} else {
			echo "Guest don't have permission on Base-Layer or ".$layer_id." therefor OpenLayers client will not be generated! Check if the layer does exists any longer or if someone has removed the permission to access this service.<br>";
		}
	}
	//create the overlay layers for which the user guest has permissions
	$startLayerId = $firstLayerId+1;
	for ($i=$startLayerId; $i<count($layer_array); $i++) {
		$extensions=$xml->LayerList->Layer[$i]->Extension->children('http://www.mapbender.org/context');
		$wms_id=$extensions->wms_id;
		$layer_id=dom_import_simplexml($extensions->layer_id)->nodeValue;
		$layer_opacity=((double)dom_import_simplexml($extensions->gui_wms_opacity)->nodeValue)/100;
		if (!isset($layer_opacity)) {
			$layer_opacity = 1;
		}
		$layer_name=$xml->LayerList->Layer[$i]->Name;
		$wms_id=dom_import_simplexml($extensions->wms_id)->nodeValue;
		$layerNames = getLayerNames($wms_id);
		if (isset($layerNames[(string)$layer_id]) && $layerNames[(string)$layer_id] != "") {
			$layer_name = $layerNames[(string)$layer_id];
		}
		$has_permission=$admin->getLayerPermission($wms_id, $layer_name, $userId);
		if (($xml->LayerList->Layer[$i]->attributes()->hidden=='0' && $has_permission && $extensions->layer_parent != '') ||
			($layer_id=='' && $xml->LayerList->Layer[$i]->attributes()->hidden=='0')){
			$html.="	layer".$i." = new OpenLayers.Layer.WMS( \"".$xml->LayerList->Layer[$i]->Title."\",\n";
			$getMapUrl = $xml->LayerList->Layer[$i]->Server->OnlineResource->attributes('http://www.w3.org/1999/xlink')->href;
			if (strpos($getMapUrl, OWSPROXY) === false) {
				if (getWmsGetMapUrl($wms_id) != false) {
					$e = new mb_notice("mod_wmc2ol.php: update GetMap found in database - change url to :".$getMapUrl);
					$getMapUrl = getWmsGetMapUrl($wms_id);
				}
			}
			$html.="		\"".$getMapUrl."\",\n";
			$html.="		{\n";
			if (isset($layerNames[(string)$layer_id]) && $layerNames[(string)$layer_id] != "") {
				$html.="		layers: \"".$layerNames[(string)$layer_id]."\",\n";
			} else {
				$html.="		layers: \"".$xml->LayerList->Layer[$i]->Name."\",\n";
			}
			//Get FormatList and the current active format
			$format='png';
			foreach ($xml->LayerList->Layer[$i]->FormatList->Format as $current_format) {
				if ($current_format->attributes()->current=='1'){    
					$format=$current_format;
				}
			}
			$html.="		format: \"".$format."\",\n";
			$html.="		transparent: \"TRUE\"\n";
			$html.="		},\n";
			$html.="		{\n";
			$html.="		maxExtent: new OpenLayers.Bounds(".$minx.",".$miny.",".$maxx.",".$maxy."),\n";       
			$html.="		projection: \"".$xml->General->BoundingBox['SRS']."\",\n";  
			$html.="		units: \"m\",\n"; 
			$html.="		singleTile: true,\n";
			$html.="		opacity: ".$layer_opacity.",\n";
			$html.="		numZoomLevels: ".$numberZoomLevels.",\n";
			//$extensions=$xml->LayerList->Layer[$i]->Extension->children('http://www.mapbender.org/context');
			$minScale=dom_import_simplexml($extensions->gui_minscale)->nodeValue;
			$maxScale=dom_import_simplexml($extensions->gui_maxscale)->nodeValue;
			if (!$maxScale){
				$maxScale='10000000';
			}
			if (!$minScale){
				$minScale='0.1';
			}
			$html.="		minScale: ".$minScale.",\n"; 
			$html.="		maxScale: ".$maxScale.",\n"; 
			$html.="		'isBaseLayer': false\n";
			$html.="	} );\n";
			$html.="	layer".$i.".alwaysInRange = true;\n"; //TODO: there is a problem with calculating the scale hints - therefor this is only a workaround - must be fixed!
			$html.="	map.addLayer(layer".$i.");\n";
		}
	}
	$html.="\n";
	$html.="	markers.alwaysInRange = true;\n";
	$html.="	map.addLayer(markers);\n";
	if(isset($_REQUEST["LayerSwitcher"]) && $_REQUEST["LayerSwitcher"] == '1'){
		$html.="	map.addControl(new OpenLayers.Control.LayerSwitcher());\n";
	}
	if(isset($_REQUEST["mb_drawCentre"])&isset($centralx)&isset($centraly)){
		if ($_REQUEST["mb_drawCentre"]='1'){
			$html.="	var size = new OpenLayers.Size(15,20);\n";
			$html.="	calculateOffset = function(size) {return new OpenLayers.Pixel(-(size.w/2), -size.h); };\n";
			
			$html.="	var icon = new OpenLayers.Icon('../img/marker/red.png',size, null, calculateOffset);\n";
			
			$html.="	markers.addMarker(new OpenLayers.Marker(new OpenLayers.LonLat(".$centralx.",".$centraly."),icon));\n";
		}
		else {
			echo "alert('The REQUEST parameter mb_drawCentre is outside of its realm!');";
			return;
		}
	}
	//Zoom to extent of given mb_myBBOX 
	if(isset($_REQUEST["mb_myBBOX"])){
		$html.="	var newBounds = new OpenLayers.Bounds(".$minx_new.",".$miny_new.",".$maxx_new.",".$maxy_new.");\n";
		$html.="	map.setCenter(new OpenLayers.LonLat(".$centralx.",".$centraly."),zoom);\n";
		$html.="	map.zoomToExtent(newBounds);\n";

	} else {
		$html.="	map.zoomToExtent(bounds);\n";
	}
	if(isset($_REQUEST["showCoords"])){
		if($_REQUEST["showCoords"]=='1'){
			$html.="	var mp = new OpenLayers.Control.MousePosition({'div':OpenLayers.Util.getElement('showpos'),'numDigits':2});\n";
			$html.="	mp.numDigits = 2;\n";
			$html.="	map.addControl(mp);";
		}	
	}
//new part showing georss content **************
	if(isset($_REQUEST["GEORSS"])){
		if($_REQUEST["GEORSS"]!=''){
			$html.="	var pointVectorLayer = new OpenLayers.Layer.Vector(\"Vector Layer\", {\n";
			//$html.="		styleMap: new OpenLayers.StyleMap({\n";
			//$html.="    			pointRadius: \"".$pointRadius."\", // based on feature.attributes.type\n";
			//$html.="   			fillColor: \"".$fillColor."\"\n";
			//$html.="		})\n";
			$html.="	});\n";
			//end of definition of the point vector layer 
			//push the features to the layer
			$html.="	pointVectorLayer.alwaysInRange = true;\n";
			$html.="	map.addLayer(pointVectorLayer);\n";
			$html.="	pointVectorLayer.addFeatures(createFeaturesFromGeoRSS());\n";
			//define the controls for the features in the point vector layer
			//add the vector layer to map
			$html.="	selectControl = new OpenLayers.Control.SelectFeature(pointVectorLayer,
					{onSelect: onFeatureSelect, onUnselect: onFeatureUnselect});\n";
			$html.="	map.addControl(selectControl);\n";
			$html.="	selectControl.activate();  \n";
		}
	}

	//Generate the possibility to do ***GetFeatureInfo*** if this was activated in the originating wmc
	if ($someLayerQueryable && $_REQUEST['withDigitize'] != '1'){
		$html.="	map.events.register('click', map, function (e) {\n";
		//loop for all layers
		for ($i=0; $i<count($layer_array); $i++){
			if ($layer_array_queryable[$i]=='1'){
				$html.="	var url".$i." =  layer".$i.".getFullRequestString({\n";
				$html.="	 REQUEST: \"GetFeatureInfo\",\n";
				$html.="	 FEATURE_COUNT: \"100\",\n";
				$html.="	 EXCEPTIONS: \"application/vnd.ogc.se_xml\",\n";
				$html.="	 BBOX: layer".$i.".map.getExtent().toBBOX(),\n";
				$html.="	 X: e.xy.x,\n";
				$html.="	 Y: e.xy.y,\n";
				$html.="	 INFO_FORMAT: 'text/html',\n";
				$html.="	 QUERY_LAYERS: layer".$i.".params.LAYERS,\n";
				$html.="	 WIDTH: layer".$i.".map.size.w,\n";
				$html.="	 HEIGHT: layer".$i.".map.size.h});\n";
				$html.="	 window.open(url".$i.",target=\"_blank\",\"scrollbars=1,width=250,height=400,left=100,top=200,resizable=1\");\n";	
			}	
		}
		#$html.="	OpenLayers.Event.stop(e);\n";
		$html.="	});\n";
	}
	
	//Define event to pull coordinates from mouseclick to some predefined element (getElementById) - it is needed for ***Digitizing Function***
	if ($_REQUEST['withDigitize'] == '1'){
		$html.="	map.events.register('click', map, function (e) {\n";
		$html.="		var lonlat = map.getLonLatFromViewPortPx(new OpenLayers.Pixel(e.xy.x , e.xy.y) );\n";
		//Delete all markers which are digitized before
		$html.="		while (markers.markers.length > 0 ) {\n";
		$html.="			markers.removeMarker(markers.markers[0]);\n";
		$html.="		}\n";
		$html.="		var size = new OpenLayers.Size(15,20);\n";
		$html.="		calculateOffset = function(size) {return new OpenLayers.Pixel(-(size.w/2), -size.h); };\n";
		$html.="		var icon = new OpenLayers.Icon('../img/marker/red.png',size, null, calculateOffset);\n";
		$html.="		markers.addMarker(new OpenLayers.Marker(lonlat,icon));\n";
		$html.="		var digX = String.substr(lonlat.lon,0,10);\n";
		$html.="		var digY = String.substr(lonlat.lat,0,10);\n";
		if (isset($_REQUEST['xID']) && isset($_REQUEST['yID']) && $_REQUEST['xID'] != '' && $_REQUEST['yID'] != '') {
			$html.="		document.getElementById(\"".$_REQUEST['xID']."\").value = digX;\n";
			$html.="		document.getElementById(\"".$_REQUEST['yID']."\").value = digY;\n";
		}
		$html.="		markers.destroy;\n";
		//$html.="	alert('something to show lonlat: '+lonlat);\n";
		$html.="		OpenLayers.Event.stop(e);\n";
		$html.="	});\n";
	}
	//end of click function for digitizing	

	$html.="}\n";//End of central function initGeoportal()
//\"<div class='georsstitle'>\" + feature.attributes['title'] +\"</div><br/>\"+\"<div class='georssdescription'>\" +feature.attributes['description']+\"</div><br/><div class='georsslink'><a href='\"+feature.attributes['link']+\"' target='_blank'>Weitere Informationen</a></div>\",
	//functions only needed, if georss objects are given - for generating popups - after initGeoportal()!
	if(isset($_REQUEST["GEORSS"])){
		if($_REQUEST["GEORSS"]!=''){	
			$html.="function onPopupClose(evt) {\n";
			$html.="	selectControl.unselect(selectedFeature);\n";
			$html.="}\n";

			$html.="function onFeatureSelect(feature) {\n";
			$html.="	selectedFeature = feature;\n";
			$html.="	popup = new OpenLayers.Popup.FramedCloud(\"chicken\", \n";
			$html.="	feature.geometry.getBounds().getCenterLonLat(),
					null,
					'<div class=\"georsstitle\">' + feature.attributes['title'] +'</div><br/>'+'<div class=\"georssdescription\">' +feature.attributes['description']+'</div><br/><div class=\"georsslink\"><a href=\"'+feature.attributes['link']+'\" target=\"_blank\">Weitere Informationen</a></div>',
					null, true, onPopupClose);\n";
			$html.="	feature.popup = popup;\n";
			$html.="	map.addPopup(popup);\n";
			$html.="}\n";

			$html.="function onFeatureUnselect(feature) {\n";
			$html.="	map.removePopup(feature.popup);\n";
			$html.="	feature.popup.destroy();\n";
			$html.=" 	feature.popup = null;\n";
			$html.="}\n"; 
			
			$html.="function createFeaturesFromGeoRSS() {\n";
			//First check if some georss is send in the request variable GEORSS
			if (isset($_REQUEST['GEORSS']) && $_REQUEST['GEORSS'] != '' && $_REQUEST['GEORSS'] != '1') {
				$georssXml = simplexml_load_string(stripslashes($_REQUEST['GEORSS']));
				if($georssXml===FALSE) {
					//It was not a XML string
					$html.= "alert('The content of the request parameter GEORSS was not a valid XML String!');\n";
					$html.="}\n"; 
				} else {
					//It was a valid XML string
					$featureCount = 0;
					foreach ($georssXml->entry as $item){
						$featureCount++;
					}
					#$featureCountPlus = $featureCount+1;
					$html.="	var pointFeatures = new Array(".$featureCount.");\n";
					//***Generate the Points from GeoRSS***
					for ($i=0; $i<$featureCount; $i++) {
						$georssElements = $georssXml->entry[$i]->children('http://www.georss.org/georss');
						$gmlElements = $georssElements->children('http://www.opengis.net/gml');
						$coords = explode(" ", $gmlElements->Point->pos);
						$geoRssEpsg = $gmlElements->Point->attributes()->srsName;
						$geoRssEpsgId = str_replace('EPSG:','',$geoRssEpsg);
						if ($geoRssEpsgId != $xml_epsg){
							//validate parameters:
							if (is_numeric($coords[0]) && is_numeric($coords[1]) && is_numeric($geoRssEpsgId)  && is_numeric(intval($xml_epsg))) {
								//coords have to be transformed
								$sql= "select asewkt(transform(GeometryFromText ( 'POINT ( ";
								$sql .= $coords[0]." ".$coords[1]." )',". $geoRssEpsgId ."),".intval($xml_epsg)."))";
								//select asewkt(transform(GeometryFromText ( 'POINT ( 7 50 )', 4326 ),31466));

								$e = new mb_notice("mod_wms2ol.php: sql (transform)=".$sql);
								$res = db_query($sql);
								//read out result
								$pointNew = db_fetch_row($res);
								$e = new mb_notice("mod_wms2ol.php: pointNew=".$pointNew[0]);
								$pattern = '~POINT\((.*)\)~i';
								preg_match($pattern, $pointNew[0], $subpattern);
								$e = new mb_notice("mod_wms2ol.php: subpattern=".$subpattern[1]);
								$coords = explode(' ',$subpattern[1]);
							} else {
								echo _mb("Some georss entries does not have numeric values for CRS or coordinates! For security reasons the script will die!");
								die();
							}
						}
						//extract styles for single features
						$imageUrl = $georssXml->entry[$i]->imageUrl->attributes()->href;
						$imageSize = $georssXml->entry[$i]->imageSize;
						//style
						$html .= "	var pointStyle = {\n";
						//validate imageUrl to url
						if (isset($imageUrl) && $imageUrl != '' && parse_url($imageUrl)) {
							if (isset($imageSize) && $imageSize != '' && is_int((integer)$imageSize)) {
								$html .= "			 pointRadius: \"".$imageSize."\",\n";
							} else {
								$html .= "			 pointRadius: \"".$pointRadius."\",\n";
							}
							$html .= "	 		 externalGraphic: \"$imageUrl\"";
							
						} else {
							$html .= "			 pointRadius: \"".$pointRadius."\",\n";
							$html.="   			 fillColor: \"".$fillColor."\"\n";
						}
						$html .= "	             };\n";
						//generate point objects
						$html.="	var point = new OpenLayers.Geometry.Point(";
						$html.="".$coords[0].",".$coords[1].");\n";
						$html.="	pointFeatures[".$i."] = new OpenLayers.Feature.Vector(point, {title: \"".$georssXml->entry[$i]->title."\", description : \"".str_replace("\"", "'",$georssXml->entry[$i]->content)."\", link : \"".$georssXml->entry[$i]->link->attributes()->href."\"}, pointStyle);\n";
						
					}
					$html.="	return pointFeatures;\n"; 
					$html.="}\n"; 
				}
			} else {
	
				//
				if (file_exists('/tmp/georss_test_v1a.xml')) {
					$georssXml = simplexml_load_file('/tmp/georss_test_v1a.xml');
					$featureCount = 0;
					foreach ($georssXml->entry as $item){
						$featureCount++;
					}
					$featureCountPlus = $featureCount+1;
					$html.="	var pointFeatures = new Array(".$featureCountPlus.");\n";
					//***Generate the Points from GeoRSS***
					for ($i=0; $i<$featureCount; $i++) {
						$georssElements = $georssXml->entry[$i]->children('http://www.georss.org/georss');
						$gmlElements = $georssElements->children('http://www.opengis.net/gml');
						$coords = explode(" ", $gmlElements->Point->pos);
						$geoRssEpsg = $gmlElements->Point->attributes()->srsName;
						$geoRssEpsgId = str_replace('EPSG:','',$geoRssEpsg);
						//transform coordinates if other epsg was choosen
						if ($geoRssEpsgId != $xml_epsg){
							//validate parameters:
							if (is_numeric($coords[0]) && is_numeric($coords[1]) && is_numeric($geoRssEpsgId)  && is_numeric(intval($xml_epsg))) {
								//coords have to be transformed
								$sql= "select asewkt(transform(GeometryFromText ( 'POINT ( ";
								$sql .= $coords[0]." ".$coords[1]." )',". $geoRssEpsgId ."),".intval($xml_epsg)."))";
								//select asewkt(transform(GeometryFromText ( 'POINT ( 7 50 )', 4326 ),31466));

								$e = new mb_notice("mod_wms2ol.php: sql (transform)=".$sql);
								$res = db_query($sql);
								//read out result
								$pointNew = db_fetch_row($res);
								$e = new mb_notice("mod_wms2ol.php: pointNew=".$pointNew[0]);
								$pattern = '~POINT\((.*)\)~i';
								preg_match($pattern, $pointNew[0], $subpattern);
								$e = new mb_notice("mod_wms2ol.php: subpattern=".$subpattern[1]);
								$coords = explode(' ',$subpattern[1]);
							} else {
								echo _mb("Some georss entries does not have numeric values for CRS or coordinates! For security reasons the script will die!");
								die();
							}
						}
						//extract styles for single features
						$imageUrl = $georssXml->entry[$i]->imageUrl->attributes()->href;
						$imageSize = $georssXml->entry[$i]->imageSize;
						//style
						$html .= "	var pointStyle = {\n";
						//validate imageUrl to url
						if (isset($imageUrl) && $imageUrl != '' && parse_url($imageUrl)) {
							if (isset($imageSize) && $imageSize != '' && is_int((integer)$imageSize)) {
								$html .= "			 pointRadius: \"".$imageSize."\",\n";
							} else {
								$html .= "			 pointRadius: \"".$pointRadius."\",\n";
							}
							$html .= "	 		 externalGraphic: \"$imageUrl\"";
							
						} else {
							$html .= "			 pointRadius: \"".$pointRadius."\",\n";
							$html.="   			 fillColor: \"".$fillColor."\"\n";
						}
						$html .= "	             };\n";
						$html.="	var point = new OpenLayers.Geometry.Point(";
						$html.="".$coords[0].",".$coords[1].");\n";
						$html.="	pointFeatures[".$i."] = new OpenLayers.Feature.Vector(point, {title: \"".$georssXml->entry[$i]->title."\", description : \"".str_replace("\"", "'",$georssXml->entry[$i]->content)."\", link : \"".$georssXml->entry[$i]->link->attributes()->href."\"}, pointStyle);\n";	
					}
					//***Generate Dummy Point for testing //no styles given
					$html.="	var point = new OpenLayers.Geometry.Point(2594468.92,5530693.03);\n";
				
					$html.="	pointFeatures[".$featureCount."] = new OpenLayers.Feature.Vector(point, {description : \"Testbeschreibung\", link : \"Testlink\"});\n"; 

					$html.="	return pointFeatures;\n"; 
					$html.="}\n"; 
				
				} else {
					$html.="	alert('No GeoRSS found - use only dummy point!');\n";
					$html.="	var point = new OpenLayers.Geometry.Point(2594468.92,5530693.03);\n";
					$html.="	var pointFeature = new OpenLayers.Feature.Vector(point, {description : \"Testbeschreibung\", link : \"Testlink\"});\n"; 
					$html.="	return pointFeature;\n"; 
					$html.="}\n";
				}
			}
			//TODO: validate the content of the XML
			//pull out the relevant content: name, id, description
			//filter out javascript content!
			//transform to specific coordinate reference system
			//generate the single attributes for the layer presentation
		}
	}
	/*if ($_REQUEST["disableMouseScroll"] == 'true') {
		//$html .= "var movemap = new OpenLayers.Control.Navigation({zoomWheelEnabled : false});\n";
		//$html .= "movemap.disableZoomWheel();\n";
		$html .= "controls = map.getControlsByClass('OpenLayers.Control.Navigation');\n";
		$html .= "for(var i = 0; i<controls.length; ++i) {\n";
		$html .= "    controls[i].disableZoomWheel();\n";
		$html .= "}\n";
	}*/
	//end of javascripting part 
	$html.="</script>\n";
	if ($_REQUEST['withoutBody'] == '1') { 	
	} else {
		$html.=" </head>\n";
		$html.="<body onload='initGeoportal()'>\n";
	}
		$html.="<div id='tags'></div>\n";
		$html.="<div id='map' class='smallmap'></div>\n";//class dont exists


	//show textareas with coordinates which are digitized
	if(isset($_REQUEST["withDebug"])){
		if($_REQUEST["withDebug"]=='1'){
			$html.="<input type=\"textarea\" name=\"xCoord\" value=\"0\" id=\"xCoord\"/>\n";
			$html.="<input type=\"textarea\" name=\"yCoord\" value=\"0\" id=\"yCoord\"/>\n";
		}
	}
	//*******************************
        //placeholder for use constraints
	//echo "<a href = 'http://www.mapbender.de' target='_blank'> <img src = '".$_SERVER['HTTP_HOST']."/mapbender/http/img/logo_geoportal_neu.png' title=\"Mapbender Logo\" border=0></a>";
		$html.="<div id='docs'>\n";
		$html.="\n";
		$html.="</div>\n";
	//Show coords if wished
	if($_REQUEST["mb_showCoords"]=='1'){
		//$html.="<div id='srs' class='csrs'>Koordinaten in <a href = '../../../mediawiki/index.php/".$xml->General->BoundingBox['SRS']."' target='_blank'>".$xml->General->BoundingBox['SRS']."</a>:</div>\n"; //only for geoportal.rlp
		$html.="<div id='srs' class='csrs'>Koordinaten in ".$xml->General->BoundingBox['SRS'].":</div>\n";	
	}
	$html.="<div id='showpos'></div>\n";
	$html.="<div id='attribution'></div>\n";
	if ($_REQUEST['withoutBody'] == '1') { 
		
	} else {
		$html.="</body>\n";
		$html.="</html>\n";
	}
	//Print out HTML code
	echo $html;
}
//end of function createOlfromWMC_id()

createOlfromWMC_id($_GET["wmc_id"], $pointRadius, $fillColor);

if (strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === false) {
	$admin->logClientUsage($_SERVER['HTTP_REFERER'], $wmc_id, 1);
}
?>
