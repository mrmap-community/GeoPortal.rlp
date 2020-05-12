<?php

# $Id: index.php 8797 2014-03-07 11:28:34Z armin11 $
# http://www.mapbender.org/index.php/Owsproxy
# Module maintainer Uli
# Copyright (C) 2002 CCGIS 
#
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

require(dirname(__FILE__) . "/../../conf/mapbender.conf");
require_once OWS3_HOME.'/application/app/bootstrap.php.cache';
require_once OWS3_HOME.'/application/app/AppKernel.php';

use Saarland\Ows3Bundle\Request\Request;

require_once(dirname(__FILE__)."/../../core/globalSettings.php");
require_once(dirname(__FILE__) . "/../../http/classes/class_administration.php");
require_once(dirname(__FILE__) . "/../../http/classes/class_connector.php");
require_once(dirname(__FILE__) . "/../../http/classes/class_mb_exception.php");
require_once(dirname(__FILE__) . "/./classes/class_QueryHandler.php");
/***** conf *****/
$imageformats = array("image/png","image/gif","image/jpeg", "image/jpg");
$width = 400;
$height = 400;
/***** conf *****/

$con = db_connect(DBSERVER,OWNER,PW);
db_select_db(DB,$con);

$postdata = $HTTP_RAW_POST_DATA;
$owsproxyService = $_REQUEST['wms']; //ToDo: change this to 'service' in the apache url-rewriting
$query = new QueryHandler();
// an array with keys and values toLowerCase -> caseinsensitiv
$reqParams = $query->getRequestParams();
$e = new mb_notice("incoming request: ".OWSPROXY."/".$_REQUEST['sid']."/".$_REQUEST['wms'].$query->getRequest());
$e = new mb_notice("owsproxy requested from: ".$_SERVER["REMOTE_ADDR"]);

if (defined("OWSPROXY_SESSION_GRABBING_WHITELIST")) {
	$whiteListArray = explode(",", OWSPROXY_SESSION_GRABBING_WHITELIST);
	if (in_array($_SERVER["REMOTE_ADDR"], $whiteListArray)) {
		$grabbingAllowed = true;
		$e = new mb_notice("Grabbing allowed for IP: ".$_SERVER["REMOTE_ADDR"]);
	} else {
		$grabbingAllowed = false;
		$e = new mb_notice("Grabbing not allowed for IP: ".$_SERVER["REMOTE_ADDR"]."!");
	}
} else {
	$grabbingAllowed = false;
}

$e = new mb_notice("Initial session_id: ".session_id());
//The session can be set by a given cookie value or was newly created by core/globalSettings.php
//either empty (without mb_user_id value) - when the corresponding session file was lost or timed out
//or filled, when there was an actual mapbender session before
//check if mb_user_id is given and is an string with an integer:
	$e = new mb_notice("userFromSession: ".getUserFromSession());
	
//Possibility to grap an existing session:
if (defined("OWSPROXY_ALLOW_SESSION_GRABBING") && OWSPROXY_ALLOW_SESSION_GRABBING == true) {
	if ($grabbingAllowed) { //for this ip
		$currentSession = session_id();
		//check for existing session in session storage - maybe the request came from outside - e.g. other browser, other application
		$existSession = Mapbender::session()->storageExists($_REQUEST["sid"]);
		if ($existSession) {
			$e = new mb_notice("storage exists");
		} else {
			$e = new mb_notice("storage does not exist!");
		}
		if ($existSession && $currentSession !== $_REQUEST["sid"]) {
			//there is a current session for the requested url
			$e = new mb_notice("A current session exists for this url and will be used!");
			//$oldsessionId = session_id();
			$tmpSession = session_id();
			//do the following only, if a user is in this session - maybe it is a session which was generated from an external application and therefor it is empty!
			//grab session, cause it is allowed
			session_id($_REQUEST["sid"]);
			$e = new mb_notice("Grabbed session with id: ".session_id());
			//kill dynamical session
			//@unlink($tmpSessionFile);
			$e = new mb_notice("Following user was found and will be used for authorization: ".Mapbender::session()->get('mb_user_id'));
			//$foundUserId = Mapbender::session()->get('mb_user_id');
			if (getUserFromSession() == false || getUserFromSession() <=  0) {
				$e = new mb_notice("No user found in the existing session - switch to the initial old one!");	
				session_id($tmpSession);
			} else {
				//delete session as it will not be needed any longer
				$e = new mb_notice("Some reasonable user id found in grabbed session. Following temporary session will be deleted: ".$tmpSession);
				Mapbender::session()->storageDestroy($tmpSession);
				unset($tmpSession);
				}
		} else {
			$e = new mb_notice("Maybe either a session does not exist for the requested SID and/or the current session is equal to the requested SID. No grabbing should be done! The variable tmpSession will not be created.");
		}
	}
}

//After this there maybe the variable $tmpSession or not. If it is not there, an existing session was grabbed and shouldn't be deleted after, because a user is logged in or the logged in user requested the service!!!
//check if current session has the same id as the session which is requested in the owsproxy url
//exchange them, if they differ and redirect to an new one with the current session, they don't differ if the session was grabbed - e.g. when printing secured services via mapbender itself.
if (session_id() !== $_REQUEST["sid"]) {
	//get all request params which are original
	//build reuquest
	$redirectUrl = OWSPROXY."/".session_id()."/".$_REQUEST['wms'].$query->getRequest();
	$redirectUrl = str_replace(":80:80",":80",$redirectUrl);
	$e = new mb_notice("IDs differ - redirect to new owsproxy url: ".$redirectUrl);
	header("Location: ".$redirectUrl);
	die();
} else {
	$e = new mb_notice("Current session_id() identical to requested SID!");
}
//this is the request which may have been redirected
//check for given user session with user_id which can be tested against the authorization
/*$foundUserId = Mapbender::session()->get('mb_user_id');
$e = new mb_exception("Found user id: ".$foundUserId ." of type: ".gettype($foundUserId));
$foundUserId = (integer)$_SESSION['mb_user_id'];
$e = new mb_exception("Found user id: ".$foundUserId ." of type: ".gettype($foundUserId));
$foundUserId = getUserFromSession();*/

if(getUserFromSession() == false || getUserFromSession() <=  0){	
	//Define the session to be temporary - it should be deleted afterwards, cause there is no user in it! This file can be deleted after the request was more or less successful. It will be generated every time again.
	$tmpSession = session_id();
	$e = new mb_notice(" session_id(): ".session_id());
	$e = new mb_notice("user_id not found in session!");
	//if configured in mapbender.conf, create guest session so that also proxied service can be watched in external applications when they are available to the anonymous user
	//only possible for webapplications - in case of desktop applications the user have to use his credentials and http_auth module
	if (defined("OWSPROXY_ALLOW_PUBLIC_USER") && OWSPROXY_ALLOW_PUBLIC_USER && defined("PUBLIC_USER") && PUBLIC_USER != "") {
		//setSession();
  		Mapbender::session()->set("mb_user_id",PUBLIC_USER);
		Mapbender::session()->set("external_proxy_user",true);
		Mapbender::session()->set("mb_user_ip",$_SERVER['REMOTE_ADDR']);
		$e = new mb_notice("Permission allowed for public user with id: ".PUBLIC_USER);
	} else {
		$e = new mb_notice("Permission denied - public user not allowed to access ressource!");
		//kill actual session  
		$e = new mb_notice("delete temporary session file: ".$tmpSession);
		Mapbender::session()->storageDestroy($tmpSession);
		throwE(array("Permission denied"," - no current session found and ","public user not allowed to access ressource!"));
		unset($tmpSession);
		die();
	}
} else {
	/*$e = new mb_exception("mb_user_id found in session: ".getUserFromSession());
	if (isset($tmpSession)) {
		$e = new mb_exception("tmpSessionFile: exists! - It was set before grabbing!");
	} else {
		$e = new mb_exception("tmpSessionFile: does not exist!");
	}*/
}
//start the session to be able to write urls to it - for 
session_start();//maybe it was started by globalSettings.php
$n = new administration;
//Extra security - IP check 
if (defined("OWSPROXY_BIND_IP") && OWSPROXY_BIND_IP == true) {
	if(Mapbender::session()->get('mb_user_ip') != $_SERVER['REMOTE_ADDR']){
		throwE(array("Session not identified.","Permission denied.","Please authenticate."));
		die();	
	}
}
$e = new mb_notice("user id for authorization test: ".getUserFromSession()); 
$wmsId = $n->getWmsIdFromOwsproxyString($query->getOwsproxyServiceId());

//get authentication infos if they are available in wms table! if not $auth = false
if ($reqParams['request'] !== 'external') {
$auth = $n->getAuthInfoOfWMS($wmsId);
}
if ($auth['auth_type']==''){
	unset($auth);
}
/*************  workflow ************/
$n = new administration();

if(count($_REQUEST) > 0){
    foreach($_REQUEST as $key => $value){
	if(strtoupper($key) === "SERVICE")
	    $found = true;
    }
    if(!$found)
	$query->setParam("service", "WMS");
}
switch (strtolower($reqParams['request'])) {
	case 'getcapabilities':
		$arrayOnlineresources = checkWmsPermission($query->getOwsproxyServiceId());
		$query->setOnlineResource($arrayOnlineresources['wms_getcapabilities']);
		$request = $query->getRequest();
		if(isset($auth)){
			getCapabilities($request,$auth);
        } else {
			getCapabilities($request);
		}
		break;
	case 'getfeatureinfo':
		$arrayOnlineresources = checkWmsPermission($query->getOwsproxyServiceId());
		$query->setOnlineResource($arrayOnlineresources['wms_getfeatureinfo']);
		$request = $query->getRequest();
		//Ergaenzungen secured UMN Requests
		if(version_compare(phpversion(),"5.3") < 0 || !$arrayOnlineresources["wms_spatialsec"]) { # use mapbender 2 owsproxy
			$log_id = false;
			if($n->getWmsfiLogTag($arrayOnlineresources['wms_id'])==1) {
				#do log to db
				#get price out of db
				$price=intval($n->getWmsfiPrice($arrayOnlineresources['wms_id']));
				$log_id = $n->logWmsGFIProxyRequest($arrayOnlineresources['wms_id'],$_SESSION['mb_user_id'],$request,$price);
			}
            if(isset($auth)){
                getFeatureInfo($log_id, $request, $auth);
            } else {
                getFeatureInfo($log_id, $request);
            }
		} else {  # use mapbender3 owsproxy- "ows3"
			$ows3proxyUrl = urldecode($request);
			$log_id = false;
			if($n->getWmsfiLogTag($arrayOnlineresources['wms_id'])==1) {
				#do log to db
				#get price out of db
				$price=intval($n->getWmsfiPrice($arrayOnlineresources['wms_id']));
				$log_id = $n->logWmsGFIProxyRequest($arrayOnlineresources['wms_id'],$_SESSION['mb_user_id'],$request,$price);
			}
			# remove all query parameter
			foreach ($_REQUEST as $key => $value) {
				unset($_REQUEST[$key]);
			}
			foreach ($_GET as $key => $value) {
				unset($_GET[$key]);
			}
			foreach ($_POST as $key => $value) {
				unset($_POST[$key]);
			}
			# set query parameter for ows3
			$_GET["url"] = $ows3proxyUrl;
			if(isset($auth)){
				$_GET["username"] = $auth['username'];
				$_GET["password"] = $auth['password'];
				$_GET["auth_type"] = $auth['auth_type'];
			}
			if(is_integer($log_id)){
				$_GET["log_id"] = $log_id;
			}
			if(!$_SESSION['mb_user_id'] && !is_int($_SESSION['mb_user_id'])){
				Mapbender::session()->set("mb_user_id", $userInformation[0]);  
				Mapbender::session()->set("mb_user_name", "NAME");
				Mapbender::session()->set("mb_user_password", "XXX");
			}
			$_GET["xxxx"] =  Mapbender::session()->get("mb_user_id");#$userInformation[0];
//			$kernel = new AppKernel('dev', true);
//			$kernel->loadClassCache();
//			$request= Request::createFromGlobals();
//			$kernel->handle($request)->send();

			$newurl = OWS3_URL;
			$num = 0;
			foreach ($_GET as $key => $value)
			{
				$newurl .= ($num === 0 ? '?' : '&') . $key . "=" . urlencode($value);
				$num++;
			}
			$cont = file_get_contents($newurl);
			for($i = 1; $i < count($http_response_header); $i++)
			{
				$head = $http_response_header[$i];
				$headHelp = strtolower($head);
				if(is_bool(strpos($headHelp, "cookie"))
				&& is_bool(strpos($headHelp, "user-agent"))
				&& is_bool(strpos($headHelp, "content-length"))
				&& is_bool(strpos($headHelp, "referer"))
				&& is_bool(strpos($headHelp, "host"))){
					header($head);
			}
		}
		echo $cont;
		}
		break;
	case 'getmap':
		$arrayOnlineresources = checkWmsPermission($owsproxyService);
		$query->setOnlineResource($arrayOnlineresources['wms_getmap']);
		$layers = checkLayerPermission($arrayOnlineresources['wms_id'],$reqParams['layers']);
		if($layers===""){
			throwE("Permission denied");
			die();
		}
		$query->setParam("layers",urldecode($layers));//the decoding of layernames dont make problems - but not really good names will be requested also ;-)
		//Following is only needed for high quality print and is vendor specific for mapservers mapfiles!
		if (defined("OWSPROXY_SUPPORT_HQ_PRINTING") && OWSPROXY_SUPPORT_HQ_PRINTING) {
			//if url has integrated mapfile - exchange it
			//$e = new mb_notice("owsproxy/http/index.php: OWSPROXY_SUPPORT_HQ_PRINTING is set");
			if ($reqParams['mapbenderhighqualityprint'] === "true") {
				//exchange mapfiles with high quality ones
				$request = preg_replace("/\.map/","_4.map",$query->getRequest());	
			} else {
				$request = $query->getRequest();
			}
		} else {
			$request = $query->getRequest();
		}
		//$request = $query->getRequest();
	    // Ergaenzungen secured UMN Requests
		if(version_compare(phpversion(),"5.3") < 0 || !$arrayOnlineresources["wms_spatialsec"]) { # use mapbender 2 owsproxy
			#log proxy requests
			if($n->getWmsLogTag($arrayOnlineresources['wms_id'])==1) {#do log to db
				#get price out of db
				$price=intval($n->getWmsPrice($arrayOnlineresources['wms_id']));
				$log_id = $n->logFullWmsProxyRequest($arrayOnlineresources['wms_id'],$_SESSION['mb_user_id'],$request,$price,0);
			}
			if(isset($auth)){
				getImage($log_id, $request,$auth);
            } else {
				getImage($log_id, $request);
			}
		} else {  # use mapbender3 owsproxy- "ows3"
			$ows3proxyUrl = urldecode($request);
            $log_id = false;
            if ($n->getWmsLogTag($arrayOnlineresources['wms_id']) == 1) {#log proxy requests
                #do log to db
                #get price out of db
                $price = intval($n->getWmsPrice($arrayOnlineresources['wms_id']));
                $log_id = $n->logFullWmsProxyRequest($arrayOnlineresources['wms_id'], $_SESSION['mb_user_id'], $request,
                                                     $price, 0, true);
            }
            foreach ($_REQUEST as $key => $value) {
                unset($_REQUEST[$key]);
            }
            foreach ($_GET as $key => $value) {
                unset($_GET[$key]);
            }
            foreach ($_POST as $key => $value) {
                unset($_POST[$key]);
            }
            $_GET["url"] = $ows3proxyUrl;
            if (isset($auth)) {
                $_GET["username"] = $auth['username'];
                $_GET["password"] = $auth['password'];
                $_GET["auth_type"] = $auth['auth_type'];
            }
            if (is_integer($log_id)) {
                $_GET["log_id"] = $log_id;
            }
            if (!$_SESSION['mb_user_id'] && !is_int($_SESSION['mb_user_id'])) {
                Mapbender::session()->set("mb_user_id", $userInformation[0]);
                Mapbender::session()->set("mb_user_name", "NAME");
                Mapbender::session()->set("mb_user_password", "XXX");
            }
            $_GET["xxxx"] = Mapbender::session()->get("mb_user_id"); #$userInformation[0];
//			$kernel = new AppKernel('dev', true);
//			$kernel->loadClassCache();
//			$request= Request::createFromGlobals();
//			$kernel->handle($request)->send();

            $newurl = OWS3_URL;
            $num = 0;
            foreach ($_GET as $key => $value) {
                $newurl .= ($num === 0 ? '?' : '&') . $key . "=" . urlencode($value);
                $num++;
            }
            $cont = file_get_contents($newurl);
            for ($i = 1; $i < count($http_response_header); $i++) {
                $head = $http_response_header[$i];
                $headHelp = strtolower($head);
                if (is_bool(strpos($headHelp, "cookie")) && is_bool(strpos($headHelp, "user-agent")) && is_bool(strpos($headHelp,
                                                                                                                       "content-length"))
                        && is_bool(strpos($headHelp, "referer")) && is_bool(strpos($headHelp, "host"))) {
                    header($head);
                }
            }
            echo $cont;
        }
		break;
	case 'map':
		$arrayOnlineresources = checkWmsPermission($owsproxyService);
		$query->setOnlineResource($arrayOnlineresources['wms_getmap']);
		$layers = checkLayerPermission($arrayOnlineresources['wms_id'],$reqParams['layers']);
		if($layers===""){
			throwE("Permission denied");
			die();
		}
		$query->setParam("layers",urldecode($layers));
		$request = $query->getRequest();
		if(isset($auth)){
			getImage(false, $url,$auth);
        } else {
			getImage(false, $url);
		}
		break;	
	case 'getlegendgraphic':
		$url = getLegendUrl($query->getOwsproxyServiceId());
		if (isset ($reqParams['sld']) && $reqParams['sld'] != "") { 
			$url = $url . getConjunctionCharacter($url) . "SLD=".$reqParams['sld']; 
		} 
		if(isset($auth)){
			getImage(false, $url, $auth);
        } else {
			getImage(false, $url);
		}
		break;
	case 'external':
		getExternalRequest($query->getOwsproxyServiceId());
		break; 
	case 'getfeature':
		$arrayFeatures = array($reqParams['typename']);
		$arrayOnlineresources = checkWfsPermission($query->getOwsproxyServiceId(), $arrayFeatures);
		$query->setOnlineResource($arrayOnlineresources['wfs_getfeature']);
		$request = $query->getRequest();
		$request = stripslashes($request);
		getFeature($request);
		break;
	// case wfs transaction (because of raw POST the request param is empty)
	case '':
		$arrayFeatures = getWfsFeaturesFromTransaction($HTTP_RAW_POST_DATA);
		$arrayOnlineresources = checkWfsPermission($query->getOwsproxyServiceId(), $arrayFeatures);
		$query->setOnlineResource($arrayOnlineresources['wfs_transaction']);
		$request = $query->getRequest();
		doTransaction($request, $HTTP_RAW_POST_DATA);
		break;
	default:
}
//why delete session here - only if it was temporary?
if (isset($tmpSession) && Mapbender::session()->storageExists($tmpSession)) {
	$e = new mb_notice("Following temporary session will be deleted: ".$tmpSession);
	Mapbender::session()->storageDestroy($tmpSession);
}


/*********************************************************/

function throwE($e)
{
	global $reqParams, $imageformats;
	if(in_array($reqParams['format'],$imageformats)){
		throwImage($e);
    } else {
		throwText($e);	
	}
}

function throwImage($e)
{
	global $width,$height;
	$image = imagecreate($width,$height);
	$transparent = ImageColorAllocate($image,155,155,155); 
	ImageFilledRectangle($image,0,0,$width,$height,$transparent);
	imagecolortransparent($image, $transparent);
	$text_color = ImageColorAllocate ($image, 233, 14, 91);
	if (count($e) > 1){
		for($i=0; $i<count($e); $i++){
			$imageString = $e[$i];
			ImageString ($image, 3, 5, $i*20, $imageString, $text_color);
		}
	} else {
		if (is_array($e)) {
			$imageString = $e[0];
		} else {
			$imageString = $e;
		}
		if ($imageString == "") {
			$imageString = "An unknown error occured!";
		}
		ImageString ($image, 3, 5, $i*20, $imageString, $text_color);
	}
	responseImage($image);
}

function throwText($e)
{
	echo join(" ", $e);
}

function responseImage($im)
{
	global $reqParams;
	$format = $reqParams['format'];
	$format="image/gif";
	if($format == 'image/png'){header("Content-Type: image/png");}
	if($format == 'image/jpeg' || $format == 'image/jpg'){header("Content-Type: image/jpeg");}
	if($format == 'image/gif'){header("Content-Type: image/gif");}
 
	if($format == 'image/png'){imagepng($im);}
	if($format == 'image/jpeg' || $format == 'image/jpg'){imagejpeg($im);}
	if($format == 'image/gif'){imagegif($im);}	
}
function completeURL($url){
	global $reqParams;
	$mykeys = array_keys($reqParams);
	for($i=0; $i<count($mykeys);$i++){
        if ($i > 0) {
            $url .= "&";
        }
		$url .= $mykeys[$i]."=".urlencode($reqParams[$mykeys[$i]]);
	}
	return $url;
}

/**
 * fetch and returns an image to client
 * 
 * @param string the original url of the image to send
 */
function getImage($log_id, $or)
{
	global $reqParams;
    $header = "Content-Type: ".$reqParams['format'];
	#log the image_requests to database
	#log the following to table mb_proxy_log
	#timestamp,user_id,getmaprequest,amount pixel,price - but do this only for wms to log - therefor first get log tag out of wms!
	#
	#
	if (func_num_args() == 3) { //new for HTTP Authentication
		$auth = func_get_arg(2);
        getDocumentContent($log_id, $or, $header, $auth);
    } else {
        getDocumentContent($log_id, $or, $header);
	}
}

/**
 * fetchs and returns the content of the FeatureInfo Response
 * 
 * @param string the url of the FeatureInfoRequest
 * @return string the content of the FeatureInfo document
 */
function getFeatureInfo($log_id, $url)
{
	global $reqParams;
	if (func_num_args() == 3) { //new for HTTP Authentication
		$auth = func_get_arg(2);
        getDocumentContent($log_id, $url, false, $auth);
    } else {
		getDocumentContent($log_id, $url);
	}
}

/**
 * fetchs and returns the content of WFS GetFeature response
 * 
 * @param string the url of the GetFeature request
 * @return echo the content of the GetFeature document
 */
function getFeature($url)
{
	global $reqParams;
	
	header("Content-Type: ".$reqParams['info_format']);
	$content = getDocumentContent(false, $url);
	$content = matchUrls($content);
	echo $content;
}

/**
 * simulates a post request to host
 * 
 * @param string host to send the request to
 * @param string port of host to send the request to
 * @param string method to send data (should be "POST")
 * @param string path on host
 * @param string data to send to host
 * @return string hosts response
 */
function sendToHost($host, $port, $method, $path, $data)
{
	$buf = '';
    if (empty($method))
        $method = 'POST';
    $method = mb_strtoupper($method);
    $fp = fsockopen($host, $port);
    fputs($fp, "$method $path HTTP/1.1\r\n");
    fputs($fp, "Host: $host\r\n");
    fputs($fp,"Content-type: application/xml\r\n");
    fputs($fp, "Content-length: " . strlen($data) . "\r\n");
    fputs($fp, "Connection: close\r\n\r\n");
    if ($method == 'POST')
        fputs($fp, $data);
    while (!feof($fp)) $buf .= fgets($fp,4096);
    fclose($fp);
    return $buf;
}

/**
 * get wfs featurenames that are touched by a tansaction request defined in XML $data
 * 
 * @param string XML that contains the tansaction request
 * @return array array of touched feature names
 */
function getWfsFeaturesFromTransaction($data)
{
	new mb_notice("owsproxy.getWfsFeaturesFromTransaction.data: ".$data);
	if(!$data || $data == ""){
		return false;
	}
	$features = array();
	$values = NULL;
	$tags = NULL;
	$parser = xml_parser_create();
	xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
	xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1);
	xml_parse_into_struct($parser,$data,$values,$tags);

	$code = xml_get_error_code ($parser);
	if ($code) {
		$line = xml_get_current_line_number($parser);
		$col = xml_get_current_column_number($parser);
		$mb_notice = new mb_notice("OWSPROXY invalid Tansaction XML: ".xml_error_string($code) .  " in line " . $line. " at character ". $col);
		die();
	}
	xml_parser_free($parser);
	
	$insert = false;
	$insertlevel = 0;
	foreach ($values as $element) {
		//features touched by insert
		if(strtoupper($element['tag']) == "WFS:INSERT" && $element['type'] == "open"){
			$insert = true;
			$insertlevel = $element[level];
		}
		if($insert && $element[level] == $insertlevel + 1 && $element['type'] == "open"){
			array_push($features, $element['tag']);
		}
		if(strtoupper($element['tag']) == "WFS:INSERT" && $element['type'] == "close"){
			$insert = false;
		}
		//updated features
		if(strtoupper($element['tag']) == "WFS:UPDATE" && $element['type'] == "open"){
			array_push($features, $element['attributes']["typeName"]);
		}
		//deleted features
		if(strtoupper($element['tag']) == "WFS:DELETE" && $element['type'] == "open"){
			array_push($features, $element['attributes']["typeName"]);
		}
	}
	return $features;
}

/**
 * sends the data of WFS Transaction and echos the response
 * 
 *  @param string url to send the WFS Transaction to
 *  @param string WFS Transaction data
 */
function doTransaction($url, $data)
{
	$arURL = parse_url($url);
	$host = $arURL["host"];
	$port = $arURL["port"]; 
    if ($port == '')
        $port = 80;

	$path = $arURL["path"];
	$method = "POST";
	$result = sendToHost($host,$port,$method,html_entity_decode($path),$data);
	
	//delete header from result
	$result = mb_eregi_replace("^[^<]*", "", $result);
	$result = mb_eregi_replace("[^>]*$", "", $result);
	
	echo $result;
}

function matchUrls($content)
{
	//check if isset owsproxyUrls else create
	$owsproxyUrls = Mapbender::session()->get('owsproxyUrls');
	if ($owsproxyUrls == false) {
		$e = new mb_notice("owsproxyUrls does not exist - create it!");
		$owsproxyUrls = array();
		$owsproxyUrls['id'] = array();
		$owsproxyUrls['url'] = array();
		Mapbender::session()->set('owsproxyUrls',$owsproxyUrls);
	}
	$pattern = "/[\"|\'](https*:\/\/[^\"|^\']*)[\"|\']/";
	preg_match_all($pattern,$content,$matches);
	for($i=0; $i<count($matches[1]); $i++){
		$req = $matches[1][$i];
		$notice = new mb_notice("owsproxy found URL ".$i.": ".$req);
		#$notice = new mb_exception("owsproxy id:".$req);
		$id = registerURL($req);
		$extReq = setExternalRequest($id);
		$notice = new mb_notice("MD5 URL ".$id." - external link: ".$extReq);
		$content = str_replace($req,$extReq,$content);
	}
	return $content;
}

function setExternalRequest($id)
{
	global $reqParams,$query;
//	$extReq = "http://".$_SESSION['HTTP_HOST'] ."/owsproxy/". $reqParams['sid'] ."/".$id."?request=external";
	$extReq = OWSPROXY ."/". $reqParams['sid'] ."/".$id."?request=external";
	return $extReq;
}

function getExternalRequest($id)
{
	//get owsproxyUrls from session
	$owsproxyUrls = Mapbender::session()->get('owsproxyUrls');
	for ($i = 0; $i < count($owsproxyUrls["url"]); $i++) {
        	if ($id == $owsproxyUrls["id"][$i]) {
            		$cUrl = $owsproxyUrls["url"][$i];
			$query_string = removeOWSGetParams($_SERVER["QUERY_STRING"]);
			if($query_string != ''){
				$cUrl .= getConjunctionCharacter($cUrl).$query_string;
			}	
			$metainfo = get_headers($cUrl,1);
			// just for the stupid InternetExplorer
			header('Pragma: private');
			header('Cache-control: private, must-revalidate');
			header("Content-Type: ".$metainfo['Content-Type']);
            		//$content = getDocumentContent(false, $cUrl, headers_list());
			$content = getDocumentContent(false, $cUrl, $metainfo);
			#$content = matchUrls($content);			
        	} else {
			$e = new mb_exception("owsproxy/http/index.php: No key found for this URL in session!");
		}	
	} 
}

function removeOWSGetParams($query_string)
{
	$r = preg_replace("/.*request=external&/","",$query_string);
	return "";
}

function getConjunctionCharacter($url)
{
	if(strpos($url,"?")){ 
		if(strpos($url,"?") == strlen($url)){ 
			$cchar = "";
		}else if(strpos($url,"&") == strlen($url)){
			$cchar = "";
		}else{
			$cchar = "&";
		}
	}
	if(strpos($url,"?") === false){
		$cchar = "?";
	} 
	return $cchar;  
}

function registerUrl($url)
{
	//get owsproxy urls from session
	//
	$owsproxyUrls = Mapbender::session()->get('owsproxyUrls');
    	if (!in_array($url, $owsproxyUrls["url"])) {
		$id = md5($url);
        	array_push($owsproxyUrls["url"], $url);
        	array_push($owsproxyUrls["id"], $id);
    	} else {
       		for ($i = 0; $i < count($owsproxyUrls["url"]); $i++) {
            		if ($url == $owsproxyUrls["url"][$i]) {
                		$id = $owsproxyUrls["id"][$i];
	}
			}			
		}
	Mapbender::session()->set('owsproxyUrls',$owsproxyUrls);
	return $id;
}

function getCapabilities($url)
{
	global $arrayOnlineresources;
	global $sid,$wms;
    $t = array(htmlentities($arrayOnlineresources["wms_getcapabilities"]), htmlentities($arrayOnlineresources["wms_getmap"]),
        htmlentities($arrayOnlineresources["wms_getfeatureinfo"]));
	$new = OWSPROXY ."/". $sid ."/".$wms."?";
	$r = str_replace($t,$new,$arrayOnlineresources["wms_getcapabilities_doc"]);
	header("Content-Type: application/xml");
	echo $r;
}

/**
 * gets the original url of the requested legend graphic
 * 
 * @param string owsproxy md5
 * @return string url to legend graphic
 */
function getLegendUrl($wms)
{
	global $reqParams;
	//get wms id
	$sql = "SELECT * FROM wms WHERE wms_owsproxy = $1";
	$v = array($wms);
	$t = array("s");
	$res = db_prep_query($sql, $v, $t);	
	if($row = db_fetch_array($res)) {
		$wmsid = $row["wms_id"];
		$getLegendUrl = $row["wms_getlegendurl"];
	} else {
		throwE(array("No wms data available."));
		die();	
	}
	//get the url
	$sql = "SELECT layer_style.legendurl ";
	$sql .= "FROM layer_style JOIN layer ";
	$sql .= "ON layer_style.fkey_layer_id = layer.layer_id ";
	$sql .= "WHERE layer.layer_name = $2 AND layer.fkey_wms_id = $1 ";
	$sql .= "AND layer_style.name = $3 AND layer_style.legendurlformat = $4";
    if ($reqParams['style'] == '') {
        $style = 'default';
    } else {
        $style = $reqParams['style'];
    }
    //$v = array($wmsid, $reqParams['layer'], $reqParams['style'], $reqParams['format']);
    $v = array($wmsid, $reqParams['layer'], $style, $reqParams['format']);
	$t = array("i", "s", "s", "s");
	$res = db_prep_query($sql, $v, $t);
	if($row = db_fetch_array($res)) {
		if (strpos($row["legendurl"],'http') !== 0) {
			$e = new mb_notice("combine legendurls!");
			return $getLegendUrl.$row["legendurl"];
		}
		return $row["legendurl"];
	} else {
		throwE(array("No legend available."));
		die();
	}
}

/**
 * validated access permission on requested wms
 * 
 * @param string OWSPROXY md5
 * @return array array with detailed information about requested wms
 */
function checkWmsPermission($wms)
{
	global $con, $n;
	$myguis = $n->getGuisByPermission($_SESSION["mb_user_id"],true);
	$mywms = $n->getWmsByOwnGuis($myguis);
	$sql = "SELECT * FROM wms WHERE wms_owsproxy = $1";
	$v = array($wms);
	$t = array("s");
	$res = db_prep_query($sql, $v, $t);
	$service = array();
	if($row = db_fetch_array($res)){
		$service["wms_id"] = $row["wms_id"];
		$service["wms_getcapabilities"] = $row["wms_getcapabilities"];	
		$service["wms_getmap"] = $row["wms_getmap"];
		$service["wms_getfeatureinfo"] = $row["wms_getfeatureinfo"];
		$service["wms_getcapabilities_doc"] = $row["wms_getcapabilities_doc"];
		$service["wms_spatialsec"] = $row["wms_spatialsec"];
	}
	
	if(!$row || count($mywms) == 0){
		throwE(array("No wms data available."));
		die();	
	}
	
	if(!in_array($service["wms_id"], $mywms)){
		throwE(array("Permission denied."," -> ".$service["wms_id"], implode(",", $mywms)));
		die();
	}
	return $service;
}

/**
 * validates the access permission by getting the appropriate wfs_conf
 * to each feature requested and check the wfs_conf permission
 * 
 * @param string owsproxy md5
 * @param array array of requested featuretype names
 * @return array array with detailed information on reqested wfs
 */
function checkWfsPermission($wfsOws, $features)
{
	global $con, $n;
	$myconfs = $n->getWfsConfByPermission($_SESSION["mb_user_id"]);
	
	//check if we know the features requested
	if(count($features) == 0){
		throwE(array("No wfs_feature data available."));
		die();
	}
	
	//get wfs
	$sql = "SELECT * FROM wfs WHERE wfs_owsproxy = $1";
	$v = array($wfsOws);
	$t = array("s");
	$res = db_prep_query($sql, $v, $t);
	$service = array();
	if($row = db_fetch_array($res)){
		$service["wfs_id"] = $row["wfs_id"];
		$service["wfs_getcapabilities"] = $row["wfs_getcapabilities"];	
		$service["wfs_getfeature"] = $row["wfs_getfeature"];
		$service["wfs_describefeaturetype"] = $row["wfs_describefeaturetype"];
		$service["wfs_transaction"] = $row["wfs_transaction"];
		$service["wfs_getcapabilities_doc"] = $row["wfs_getcapabilities_doc"];
    } else {
		throwE(array("No wfs data available."));
		die();	
	}
	
	foreach($features as $feature){
	
		//get appropriate wfs_conf
		$sql = "SELECT wfs_conf.wfs_conf_id FROM wfs_conf ";
		$sql.= "JOIN wfs_featuretype ";
		$sql.= "ON wfs_featuretype.featuretype_id = wfs_conf.fkey_featuretype_id ";
		$sql.= "WHERE wfs_featuretype.featuretype_name = $2 ";
		$sql.= "AND wfs_featuretype.fkey_wfs_id = $1";
		$v = array($service["wfs_id"], $feature);
		$t = array("i","s");
		$res = db_prep_query($sql, $v, $t);
		if(!($row = db_fetch_array($res))){
			$notice = new mb_exception("Permissioncheck failed no wfs conf for wfs ".$service["wfs_id"]." with feturetype ".$feature);
			throwE(array("No wfs_conf data for featuretype ".$feature));
			die();	
		}
		$conf_id = $row["wfs_conf_id"];
		
		//check permission
		if(!in_array($conf_id, $myconfs)){
			$notice = new mb_exception("Permissioncheck failed:".$conf_id." not in ".implode(",", $myconfs));
			throwE(array("Permission denied."," -> ".$conf_id, implode(",", $myconfs)));
			die();
		}
	}

	return $service;
}

function checkLayerPermission($wms_id, $l)
{
	global $n, $owsproxyService;
//	$notice = new mb_exception("owsproxy: checkLayerpermission: wms: ".$wms_id.", layer: ".$l);
	$myl = explode(",",$l);
	$r = array();
	foreach($myl as $mysl){
		if($n->getLayerPermission($wms_id, $mysl, $_SESSION["mb_user_id"]) === true){
			array_push($r, $mysl);
		}		
	}
	$ret = implode(",",$r);
	return $ret;
}

function getDocumentContent($log_id, $url, $header = false)
{
	global $reqParams, $n;
    if (func_num_args() == 4) { //new for HTTP Authentication
        $auth = func_get_arg(3);
		$d = new connector($url, $auth);
    } else {
		$d = new connector($url);
	}
	$content = $d->file;
	if(strtoupper($reqParams["request"]) == "GETMAP"){ // getmap
		$pattern_exc = '~EXCEPTION~i';
		preg_match($pattern_exc, $content, $exception);
		if(!$content){
			if($log_id != null && is_integer($log_id)){
				$n->updateWmsLog(0, "Mb2OWSPROXY - unable to load: ".$url, "text/plain", $log_id);
			}
			header("Content-Type: text/plain");
			echo "Mb2OWSPROXY - unable to load: ".$url;
		} else if(count($exception) > 0){
			if($log_id != null && is_integer($log_id)){
				$n->updateWmsLog(0, $content, $reqParams["exceptions"], $log_id);
			}
			header("Content-Type: ".$reqParams["exceptions"]);
			echo $content;
		} else {
			$source = new Imagick();
			$source->readImageBlob($content);
			$numColors = $source->getImageColors();
			if($log_id != null && is_integer($log_id)){
				$n->updateWmsLog($numColors <= 1 ? -1 : 1, null, null, $log_id);
			}
			header("Content-Type: ".$reqParams['format']);
			echo $content;
		}
		return true;
	} else if(strtoupper($reqParams["request"]) == "GETFEATUREINFO"){ // getmap
//		header("Content-Type: ".$reqParams['info_format']);
//		$content = matchUrls($content);
//		echo $content;
		$pattern_exc = '~EXCEPTION~i';
		preg_match($pattern_exc, $content, $exception);
		if(!$content){
			if($log_id != null){
				$n->updateWmsFiLog("Mb2OWSPROXY - unable to load: ".$url, "text/plain", $log_id);
			}
			header("Content-Type: text/plain");
			echo "Mb2OWSPROXY - unable to load: ".$url;
		} else if(count($exception) > 0){
			if($log_id != null){
				$n->updateWmsFiLog($content, "application/xml", $log_id);
			}
			header("Content-Type: application/xml");
			echo $content;
		} else {
			header("Content-Type: ".$reqParams['info_format']);
			if($log_id != null){
				$n->updateWmsFiLog(null, null, $log_id);
			}
			$content = matchUrls($content);
			echo $content;
		}
		return true;
	} else {
	if (header !== false) { 
		header($header);
	}
        echo $content;
	}
}

function getUserFromSession()
{
	if (Mapbender::session()->get('mb_user_id')) {
		if ((integer)Mapbender::session()->get('mb_user_id') >= 0) {
			$foundUserId = (integer)Mapbender::session()->get('mb_user_id');
			//$e = new mb_exception("user id: ".$foundUserId." found in session");
		} else {
			$foundUserId = false;
			//$e = new mb_exception("user id not found or not casted to integer");
			//$e = new mb_exception("Newly initialized session - no logged in mapbender user for this session!");
		}
	} else {
		$foundUserId = false;
		//$e = new mb_exception("user id not found or not casted to integer");
		//$e = new mb_exception("Newly initialized session - no logged in mapbender user for this session!");
	}
	return $foundUserId;
}

?>
