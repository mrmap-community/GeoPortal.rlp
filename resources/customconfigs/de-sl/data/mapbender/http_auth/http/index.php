<?php

require(dirname(__FILE__) . "/../../conf/mapbender.conf");
require_once OWS3_HOME . '/application/app/bootstrap.php.cache';
require_once OWS3_HOME . '/application/app/AppKernel.php';

use Saarland\Ows3Bundle\Request\Request;

require_once(dirname(__FILE__) . "/../../core/globalSettings.php");
require(dirname(__FILE__) . "/../../http/classes/class_administration.php");
require(dirname(__FILE__) . "/../../http/classes/class_connector.php");
require_once(dirname(__FILE__) . "/../../http/classes/class_mb_exception.php");
require(dirname(__FILE__) . "/../../owsproxy/http/classes/class_QueryHandler.php");

//database connection
$db = db_connect($DBSERVER, $OWNER, $PW);
db_select_db(DB, $db);
/* * *** conf **** */
$imageformats = array("image/png", "image/gif", "image/jpeg", "image/jpg");
$width = 400;
$height = 400;
/* * *** conf **** */

//control if digest auth is set, if not set, generate the challenge with getNonce()
if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Digest realm="' . REALM .
            '",qop="auth",nonce="' . getNonce() . '",opaque="' . md5(REALM) . '"');
    die('Text to send if user hits Cancel button');
}

//read out the header in an array
$requestHeaderArray = http_digest_parse($_SERVER['PHP_AUTH_DIGEST']);

//error if header could not be read
if (!($requestHeaderArray)) {
    echo 'Following Header information cannot be validated - check your clientsoftware!<br>';
    echo $_SERVER['PHP_AUTH_DIGEST'] . '<br>';
    die();
}

//get mb_username and email out of http_auth username string
$userIdentification = explode(';', $requestHeaderArray['username']);
$mbUsername = $userIdentification[0];
$mbEmail = $userIdentification[1];

$userInformation = getUserInfo($mbUsername, $mbEmail);

if ($userInformation[0] == '-1') {
    die('User with name: ' . $mbUsername . ' and email: ' . $mbEmail . ' not known to security proxy!');
}

if ($userInformation[1] == '') { //check if digest exists in db - if no digest exists it should be a null string!
    die('User with name: ' . $mbUsername . ' and email: ' . $mbEmail . ' has no digest - please set a new password and try again!');
}

//first check the stale!
if ($requestHeaderArray['nonce'] == getNonce()) {
    // Up-to-date nonce received
    $stale = false;
} else {
    // Stale nonce received (probably more than x seconds old)
    $stale = true;
    //give another chance to authenticate
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Digest realm="' . REALM . '",qop="auth",nonce="' . getNonce() . '",opaque="' . md5(REALM) . '" ,stale=true');
}
// generate the valid response to check the request of the client
$A1 = $userInformation[1];
$A2 = md5($_SERVER['REQUEST_METHOD'] . ':' . $requestHeaderArray['uri']);
$valid_response = $A1 . ':' . getNonce() . ':' . $requestHeaderArray['nc'];
$valid_response .= ':' . $requestHeaderArray['cnonce'] . ':' . $requestHeaderArray['qop'] . ':' . $A2;

$valid_response = md5($valid_response);

if ($requestHeaderArray['response'] != $valid_response) {//the user have to authenticate new - cause something in the authentication went wrong
    //Monitoring Ausgaben wegen hauefigen Nutzeranfragen bzgl http_auth Login
    $e = new mb_exception("Authentifizierungsproblem - ValidResponse :" . $valid_response . " : Browser Response :" . $requestHeaderArray['response'] . " Username: " . $mbUsername);
    $e = new mb_exception("Authentifizierungsproblem - premd5response: " . $A1 . ":" . getNonce() . ":" . $requestHeaderArray['nc'] . ":" . $requestHeaderArray['cnonce'] . ":" . $requestHeaderArray['qop'] . ":" . $_SERVER['REQUEST_METHOD'] . ":" . $requestHeaderArray['uri']);
    die('Authentication failed - sorry, you have to authenticate once more!');
}
//if we are here - authentication has been done well!
//let's do the proxy things (came from owsproxy.php):
$postdata = $HTTP_RAW_POST_DATA;
$layerId = $_REQUEST['layer_id'];
//new option for nested layers
$withChilds = false;
if (isset($_REQUEST["withChilds"]) && $_REQUEST["withChilds"] === "1") {
    $withChilds = true;
}

$query = new QueryHandler();

// an array with keys and values toLoserCase -> caseinsensitiv
$reqParams = $query->getRequestParams();

$n = new administration();

$wmsId = getWmsIdByLayerId($layerId);
$owsproxyString = $n->getWMSOWSstring($wmsId);

if (!$owsproxyString) {
    die('The requested resource does not exists or the routing through mapbenders owsproxy is not activated!');
}
//get authentication infos if they are available in wms table! if not $auth = false
$auth = $n->getAuthInfoOfWMS($wmsId);

if ($auth['auth_type'] == '') {
    unset($auth);
}

$e = new mb_notice("REQUEST to HTTP_AUTH: " . strtolower($reqParams['request']));

//what the proxy does
switch (strtolower($reqParams['request'])) {

    case 'getcapabilities':
        $arrayOnlineresources = checkWmsPermission($wmsId, $userInformation[0]);
        $query->setOnlineResource($arrayOnlineresources['wms_getcapabilities']);
        //$request = preg_replace("/(.*)frames\/login.php/", "$1php/wms.php?layer_id=".$layerId, LOGIN);
        if (isset($_SERVER["HTTPS"])) {
            $urlPrefix = "https://";
        } else {
            $urlPrefix = "http://";
        }
        if (defined("MAPBENDER_PATH") && MAPBENDER_PATH != '') {
            $request = MAPBENDER_PATH . "/php/wms.php?layer_id=" . $layerId;
        } else {
            $request = $urlPrefix . $_SERVER['HTTP_HOST'] . "/mapbender/php/wms.php?layer_id=" . $layerId;
        }
        if ($withChilds) {
            $requestFull .= $request . '&withChilds=1&REQUEST=GetCapabilities&VERSION=1.1.1&SERVICE=WMS';
        } else {
            $requestFull .= $request . '&REQUEST=GetCapabilities&VERSION=1.1.1&SERVICE=WMS';
        }
        if (isset($auth)) {
            getCapabilities($request, $requestFull, $auth);
        } else {
            getCapabilities($request, $requestFull);
        }
        break;
    case 'getfeatureinfo':
        $arrayOnlineresources = checkWmsPermission($wmsId, $userInformation[0]);
        $query->setOnlineResource($arrayOnlineresources['wms_getfeatureinfo']);
        $request = $query->getRequest();
        if (version_compare(phpversion(), "5.3") < 0 || !$arrayOnlineresources["wms_spatialsec"]) { # use mapbender 2 owsproxy
            $log_id = false;
            if ($n->getWmsfiLogTag($arrayOnlineresources['wms_id']) == 1) {
                #do log to db
                #get price out of db
                $price = intval($n->getWmsfiPrice($arrayOnlineresources['wms_id']));
                $log_id = $n->logWmsGFIProxyRequest($arrayOnlineresources['wms_id'], $userInformation[0], $request,
                                                    $price);
            }
            if (isset($auth)) {
                getFeatureInfo($log_id, $request, $auth);
            } else {
                getFeatureInfo($log_id, $request);
            }
        } else {
            # use mapbender3 owsproxy- "ows3"
            $ows3proxyUrl = urldecode($request);
            $log_id = false;
            if ($n->getWmsfiLogTag($arrayOnlineresources['wms_id']) == 1) {
                #do log to db
                #get price out of db
                $price = intval($n->getWmsfiPrice($arrayOnlineresources['wms_id']));
                $log_id = $n->logWmsGFIProxyRequest($arrayOnlineresources['wms_id'], $userInformation[0], $request,
                                                    $price);
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
            if (isset($auth)) {
                $_GET["username"] = $auth['username'];
                $_GET["password"] = $auth['password'];
                $_GET["auth_type"] = $auth['auth_type'];
            }
            if (is_integer($log_id)) {
                $_GET["log_id"] = $log_id;
            }
//            if(!$_SESSION['mb_user_id'] && !is_int($_SESSION['mb_user_id'])){
            Mapbender::session()->set("mb_user_id", $userInformation[0]);
            Mapbender::session()->set("mb_user_name", $userIdentification[0]);
            Mapbender::session()->set("mb_user_password", "DUMMY");
//            }
            $_GET["xxxx"] = Mapbender::session()->get("mb_user_id"); #$userInformation[0];
//          $kernel = new AppKernel('dev', true);
//          $kernel->loadClassCache();
//          $request= Request::createFromGlobals();
//          $kernel->handle($request)->send();

            $newurl = OWS3_URL;
            $num = 0;
            $hasKeyService = false;
            foreach ($_GET as $key => $value) {
                if (strtolower($key) === 'service') {
                    $hasKeyService = true;
                }
                $newurl .= ($num === 0 ? '?' : '&') . $key . "=" . urlencode($value);
                $num++;
            }
            $newurl .= $hasKeyService ? '' : ($num === 0 ? '?' : '&') . "service=WMS";
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
    case 'getmap':
        $arrayOnlineresources = checkWmsPermission($wmsId, $userInformation[0]);
        $query->setOnlineResource($arrayOnlineresources['wms_getmap']);
        $layers = checkLayerPermission($wmsId, $reqParams['layers'], $userInformation[0]);
        if ($layers == '') {
            throwE("GetMap permission denied on layer with id " . $layerId);
            die();
        }
        $query->setParam("layers", urldecode($layers));
        $request = $query->getRequest();
        if (version_compare(phpversion(), "5.3") < 0 || !$arrayOnlineresources["wms_spatialsec"]) { # use mapbender 2 owsproxy
            #log proxy requests
            $log_id = false;
            if ($n->getWmsLogTag($wmsId) == 1) {
                #do log to db
                #TODO read out size of bbox and calculate price
                #get price out of db
                $price = intval($n->getWmsPrice($wmsId));
                $log_id = $n->logFullWmsProxyRequest($arrayOnlineresources['wms_id'], $userInformation[0], $request,
                                                     $price, 0);
            }
            if (isset($auth)) {
                getImageII($log_id, $request, $auth);
            } else {
                getImageII($log_id, $request);
            }
        } else {
            $ows3proxyUrl = urldecode($request);
            $log_id = false;
            if ($n->getWmsLogTag($arrayOnlineresources['wms_id']) == 1) {#log proxy requests
                #do log to db
                #get price out of db
                $price = intval($n->getWmsPrice($arrayOnlineresources['wms_id']));
                $log_id = $n->logFullWmsProxyRequest($arrayOnlineresources['wms_id'], $userInformation[0], $request,
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
            $hasKeyService = false;
            foreach ($_GET as $key => $value) {
                if (strtolower($key) === 'service') {
                    $hasKeyService = true;
                }
                $newurl .= ($num === 0 ? '?' : '&') . $key . "=" . urlencode($value);
                $num++;
            }
            $newurl .= $hasKeyService ? '' : ($num === 0 ? '?' : '&') . "service=WMS";
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
    case 'getlegendgraphic':
        $url = getLegendUrl($wmsId);
        if (isset($reqParams['sld']) && $reqParams['sld'] != "") {
            $url = $url . getConjunctionCharacter($url) . "SLD=" . $reqParams['sld'];
        }
        if (isset($auth)) {
            getImage($url, $auth);
        } else {
            getImage($url);
        }
        break;
    default:
        echo 'Your are logged in as: <b>' . $requestHeaderArray['username'] . '</b> and requested the layer with id=<b>' . $layerId . '</b> but your request is not a valid OWS request';
}

//functions for http_auth 
//**********************************************************************************************
// function to parse the http auth header
function http_digest_parse($txt)
{
    // protect against missing data
    $needed_parts = array('nonce' => 1, 'nc' => 1, 'cnonce' => 1, 'qop' => 1, 'username' => 1, 'uri' => 1, 'response' => 1);
    $data = array();
    $keys = implode('|', array_keys($needed_parts));
    preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);
    foreach ($matches as $m) {
        $data[$m[1]] = $m[3] ? $m[3] : $m[4];
        unset($needed_parts[$m[1]]);
    }
    return $needed_parts ? false : $data;
}

// function to get relevant user information from mb db
function getUserInfo($mbUsername, $mbEmail)
{
    $result = array();
    if (preg_match('#[@]#', $mbEmail)) {
        $sql = "SELECT mb_user_id, mb_user_digest FROM mb_user where mb_user_name = $1 AND mb_user_email = $2";
        $v = array($mbUsername, $mbEmail);
        $t = array("s", "s");
    } else {
        $sql = "SELECT mb_user_id, mb_user_aldigest As mb_user_digest FROM mb_user where mb_user_name = $1";
        $v = array($mbUsername);
        $t = array("s");
    }
    $res = db_prep_query($sql, $v, $t);
    if (!($row = db_fetch_array($res))) {
        $result[0] = "-1";
    } else {
        $result[0] = $row['mb_user_id'];
        $result[1] = $row['mb_user_digest'];
    }
    return $result;
}

function getNonce()
{
    global $nonceLife;
    $time = ceil(time() / $nonceLife) * $nonceLife;
    return md5(date('Y-m-d H:i', $time) . ':' . $_SERVER['REMOTE_ADDR'] . ':' . NONCEKEY);
}

//**********************************************************************************************
//functions of owsproxy/http/index.php
//**********************************************************************************************
function throwE($e)
{
    global $reqParams, $imageformats;

    if (in_array($reqParams['format'], $imageformats)) {
        throwImage($e);
    } else {
        throwText($e);
    }
}

/* function throwImage($e)
  {
  global $reqParams;
  if (!$reqParams['width'] || !$reqParams['height']) { //width or height are not set by ows request - maybe for legendgraphics
  $width = 300;
  $height = 20;
  }
  $image = imagecreate($width,$height);
  $transparent = ImageColorAllocate($image,155,155,155);
  ImageFilledRectangle($image,0,0,$width,$height,$transparent);
  imagecolortransparent($image, $transparent);
  $text_color = ImageColorAllocate ($image, 233, 14, 91);
  for($i=0; $i<count($e); $i++){
  ImageString ($image, 3, 5, $i*20, $e[$i], $text_color);
  }
  responseImage($image);
  } */

function throwImage($e)
{
    global $width, $height;
    $image = imagecreate($width, $height);
    $transparent = ImageColorAllocate($image, 155, 155, 155);
    ImageFilledRectangle($image, 0, 0, $width, $height, $transparent);
    imagecolortransparent($image, $transparent);
    $text_color = ImageColorAllocate($image, 233, 14, 91);
    if (count($e) > 1) {
        for ($i = 0; $i < count($e); $i++) {
            $imageString = $e[$i];
            ImageString($image, 3, 5, $i * 20, $imageString, $text_color);
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
        ImageString($image, 3, 5, $i * 20, $imageString, $text_color);
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
    if ($format == 'image/png') {
        header("Content-Type: image/png");
    }
    if ($format == 'image/jpeg' || $format == 'image/jpg') {
        header("Content-Type: image/jpeg");
    }
    if ($format == 'image/gif') {
        header("Content-Type: image/gif");
    }
    if ($format == 'image/png') {
        imagepng($im);
    }
    if ($format == 'image/jpeg' || $format == 'image/jpg') {
        imagejpeg($im);
    }
    if ($format == 'image/gif') {
        imagegif($im);
    }
}

function completeURL($url)
{
    global $reqParams;
    $mykeys = array_keys($reqParams);
    for ($i = 0; $i < count($mykeys); $i++) {
        if ($i > 0) {
            $url .= "&";
        }
        $url .= $mykeys[$i] . "=" . urlencode($reqParams[$mykeys[$i]]);
    }
    return $url;
}

/**
 * fetch and returns an image to client
 * 
 * @param string the original url of the image to send
 */
function getImage($or)
{
    global $reqParams;
    header("Content-Type: " . $reqParams['format']);
    if (func_num_args() == 2) { //new for HTTP Authentication
        $auth = func_get_arg(1);
        echo getDocumentContent($or, $auth);
    } else {
        echo getDocumentContent($or);
    }
}

/**
 * fetch and returns an image to client
 * 
 * @param string the original url of the image to send
 */
function getImageII($log_id, $or)
{
    global $reqParams;
    $header = "Content-Type: " . $reqParams['format'];
    #log the image_requests to database
    #log the following to table mb_proxy_log
    #timestamp,user_id,getmaprequest,amount pixel,price - but do this only for wms to log - therefor first get log tag out of wms!
    #
	#
	if (func_num_args() == 3) { //new for HTTP Authentication
        $auth = func_get_arg(2);
        getDocumentContentII($log_id, $or, $header, $auth);
    } else {
        getDocumentContentII($log_id, $or, $header);
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

function matchUrls($content)
{
    if (!session_is_registered("owsproxyUrls")) {
        $_SESSION["owsproxyUrls"] = array();
        $_SESSION["owsproxyUrls"]["id"] = array();
        $_SESSION["owsproxyUrls"]["url"] = array();
    }
    $pattern = "/[\"|\'](https*:\/\/[^\"|^\']*)[\"|\']/";
    preg_match_all($pattern, $content, $matches);
    for ($i = 0; $i < count($matches[1]); $i++) {
        $req = $matches[1][$i];
        $e = new mb_exception("Gefundene URL " . $i . ": " . $req);
        #$notice = new mb_notice("owsproxy id:".$req);
        $id = registerURL($req);
        $extReq = setExternalRequest($id);
        $e = new mb_exception("MD5 URL " . $id . "-Externer Link: " . $extReq);
        $content = str_replace($req, $extReq, $content);
    }
    return $content;
}

function setExternalRequest($id)
{
    global $reqParams, $query;
    $extReq = "http://" . $_SESSION['HTTP_HOST'] . "/owsproxy/" . $reqParams['sid'] . "/" . $id . "?request=external";
    return $extReq;
}

function getExternalRequest($id)
{
    for ($i = 0; $i < count($_SESSION["owsproxyUrls"]["url"]); $i++) {
        if ($id == $_SESSION["owsproxyUrls"]["id"][$i]) {
            $cUrl = $_SESSION["owsproxyUrls"]["url"][$i];
            $query_string = removeOWSGetParams($_SERVER["QUERY_STRING"]);
            if ($query_string != '') {
                $cUrl .= getConjunctionCharacter($cUrl) . $query_string;
            }
            $metainfo = get_headers($cUrl, 1);
            // just for the stupid InternetExplorer
            header('Pragma: private');
            header('Cache-control: private, must-revalidate');

            header("Content-Type: " . $metainfo['Content-Type']);

            $content = getDocumentContent($cUrl, false);
            #$content = matchUrls($content); //In the case of http_auth - this is not possible cause we cannot save them in the header - maybe we could create a special session to do so later on? 			
            echo $content;
        }
    }
}

function removeOWSGetParams($query_string)
{
    $r = preg_replace("/.*request=external&/", "", $query_string);
    #return $r;
    return "";
}

function getConjunctionCharacter($url)
{
    if (strpos($url, "?")) {
        if (strpos($url, "?") == strlen($url)) {
            $cchar = "";
        } else if (strpos($url, "&") == strlen($url)) {
            $cchar = "";
        } else {
            $cchar = "&";
        }
    }
    if (strpos($url, "?") === false) {
        $cchar = "?";
    }
    return $cchar;
}

function registerUrl($url)
{
    if (!in_array($url, $_SESSION["owsproxyUrls"]["url"])) {
        $e = new mb_exception("Is noch net drin!");
        $id = md5($url);
        $e = new mb_exception("ID: " . $id . "  URL: " . $url . " will be written to session");
        array_push($_SESSION["owsproxyUrls"]["url"], $url);
        array_push($_SESSION["owsproxyUrls"]["id"], $id);
    } else {
        $e = new mb_exception("It was found! Search content and return ID!");
        for ($i = 0; $i < count($_SESSION["owsproxyUrls"]["url"]); $i++) {
            $e = new mb_exception("Content " . $i . " : proxyurl:" . $_SESSION["owsproxyUrls"]["url"][$i] . " - new: " . $url);
            if ($url == $_SESSION["owsproxyUrls"]["url"][$i]) {
                $e = new mb_exception("Identical! ID:" . $_SESSION["owsproxyUrls"]["id"][$i] . " will be used");
                $id = $_SESSION["owsproxyUrls"]["id"][$i];
            }
        }
    }
    return $id;
}

function getCapabilities($request, $requestFull)
{
    global $arrayOnlineresources;
    global $layerId;
    header("Content-Type: application/xml");
    if (func_num_args() == 3) { //new for HTTP Authentication
        $auth = func_get_arg(2);
        $content = getDocumentContent($requestFull, $auth);
    } else {
        $content = getDocumentContent($requestFull);
    }
    //show temporal content fo capabilities
    $e = new mb_notice("content from wms.php fascade after going thru curl: " . $content);
    //loading as xml
    libxml_use_internal_errors(true);
    try {
        $capFromFascadeXmlObject = simplexml_load_string($content);
        if ($capFromFascadeXmlObject === false) {
            foreach (libxml_get_errors() as $error) {
                $err = new mb_exception("http_auth/index.php: " . $error->message);
            }
            throw new Exception("http_auth/index.php: " . 'Cannot parse Metadata XML!');
            echo "<error>http_auth/index.php: Cannot parse Capabilities XML!</error>";
            die();
        }
    } catch (Exception $e) {
        $err = new mb_exception("http_auth/index.php: " . $e->getMessage());
        echo "<error>http_auth/index.php: " . $e->getMessage() . "</error>";
        die();
    }
    //exchanging urls in some special fields
    //
	//GetCapabilities, GetMap, GetFeatureInfo, GetLegendGraphics, ...
    $capFromFascadeXmlObject->registerXPathNamespace("xlink", "http://www.w3.org/1999/xlink");
    //Mapping of urls for wms 1.1.1 which should be exchanged 
    $urlsToChange = array(
        '/WMT_MS_Capabilities/Capability/Request/GetCapabilities/DCPType/HTTP/Get/OnlineResource/@xlink:href',
        '/WMT_MS_Capabilities/Capability/Request/GetCapabilities/DCPType/HTTP/Post/OnlineResource/@xlink:href',
        '/WMT_MS_Capabilities/Capability/Request/GetMap/DCPType/HTTP/Get/OnlineResource/@xlink:href',
        '/WMT_MS_Capabilities/Capability/Request/GetMap/DCPType/HTTP/Post/OnlineResource/@xlink:href',
        '/WMT_MS_Capabilities/Capability/Request/GetFeatureInfo/DCPType/HTTP/Get/OnlineResource/@xlink:href',
        '/WMT_MS_Capabilities/Capability/Request/GetFeatureInfo/DCPType/HTTP/Post/OnlineResource/@xlink:href',
        '/WMT_MS_Capabilities/Capability/Layer/Layer/Style/LegendURL/OnlineResource/@xlink:href'
    );
    foreach ($urlsToChange as $xpath) {
        $href = $capFromFascadeXmlObject->xpath($xpath);
        $e = new mb_notice("old href: " . $href[0]);
        $e = new mb_notice("href replaced: " . replaceOwsUrls($href[0], $layerId));
        $href[0][0] = replaceOwsUrls($href[0], $layerId);
    }
    echo $capFromFascadeXmlObject->asXML();
}

function replaceOwsUrls($owsUrl, $layerId)
{
    $new = "http_auth/" . $layerId . "?";
    $pattern = "#owsproxy/[a-z0-9]{32}\/[a-z0-9]{32}\?#m";
    $httpAuthUrl = preg_replace($pattern, $new, $owsUrl);
    //replace 
    //also replace the getcapabilities url with authenticated one ;-)
    if (defined("MAPBENDER_PATH") && MAPBENDER_PATH != '') {
        $wmsUrl = parse_url(MAPBENDER_PATH);
        $path = $wmsUrl['path'];
        $pattern = "#" . $path . "/php/wms.php\?layer_id=" . $layerId . "&#m";
    } else {
        $pattern = "#mapbender/php/wms.php\?layer_id=" . $layerId . "&#m";
    }
    $httpAuthUrl = preg_replace($pattern, "/" . $new, $httpAuthUrl);
    //use always https for url
    if (defined("HTTP_AUTH_PROXY") && HTTP_AUTH_PROXY != '') {
        $parsed_url = parse_url(HTTP_AUTH_PROXY);
        if ($parsed_url['scheme'] == "https") {
            $httpAuthUrl = preg_replace("#http:#", "https:", $httpAuthUrl);
            $httpAuthUrl = preg_replace("#:80/#", ":443/", $httpAuthUrl);
        }
    }
    return $httpAuthUrl;
}

/**
 * gets the original url of the requested legend graphic
 * 
 * @param string owsproxy md5
 * @return string url to legend graphic
 */
function getLegendUrl($wmsId)
{
    global $reqParams;
    //get wms_getlegendurl
    $sql = "SELECT wms_getlegendurl FROM wms WHERE wms_id = $1";
    $v = array($wmsId);
    $t = array("i");
    $res = db_prep_query($sql, $v, $t);
    if ($row = db_fetch_array($res)) {
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

    $v = array($wmsId, $reqParams['layer'], $style, $reqParams['format']);
    $t = array("i", "s", "s", "s");
    $res = db_prep_query($sql, $v, $t);
    if ($row = db_fetch_array($res)) {
        if (strpos($row["legendurl"], 'http') !== 0) {
            $e = new mb_notice("combine legendurls!");
            return $getLegendUrl . $row["legendurl"];
        }
        return $row["legendurl"];
    } else {
        throwE(array("No legendurl available."));
        die();
    }
}

/**
 * validated access permission on requested wms
 * 
 * @param wmsId integer, userId - integer
 * @return array array with detailed information about requested wms
 */
function checkWmsPermission($wmsId, $userId)
{
    global $con, $n;
    $myguis = $n->getGuisByPermission($userId, true);
    $mywms = $n->getWmsByOwnGuis($myguis);

    $sql = "SELECT * FROM wms WHERE wms_id = $1";
    $v = array($wmsId);
    $t = array("s");
    $res = db_prep_query($sql, $v, $t);
    $service = array();
    if ($row = db_fetch_array($res)) {
        $service["wms_id"] = $row["wms_id"];
        $service["wms_getcapabilities"] = $row["wms_getcapabilities"];
        $service["wms_getmap"] = $row["wms_getmap"];
        $service["wms_getfeatureinfo"] = $row["wms_getfeatureinfo"];
        $service["wms_getcapabilities_doc"] = $row["wms_getcapabilities_doc"];
        $service["wms_spatialsec"] = $row["wms_spatialsec"];
    }
    if (!$row || count($mywms) == 0) {
        throwE(array("No wms data available."));
        die();
    }

    if (!in_array($service["wms_id"], $mywms)) {
        throwE(array("Permission denied.", " -> " . $service["wms_id"], implode(",", $mywms)));
        die();
    }
    return $service;
}

function checkLayerPermission($wms_id, $l, $userId)
{
    global $n, $owsproxyService;
    $e = new mb_notice("owsproxy: checkLayerpermission: wms: " . $wms_id . ", layer: " . $l . ' user_id: ' . $userId);
    $myl = explode(",", $l);
    $r = array();
    foreach ($myl as $mysl) {
        if ($n->getLayerPermission($wms_id, $mysl, $userId) === true) {
            array_push($r, $mysl);
        }
    }
    $ret = implode(",", $r);
    return $ret;
}

function getDocumentContent($url)
{
    if (func_num_args() == 2) { //new for HTTP Authentication
        $auth = func_get_arg(1);
        $d = new connector($url, $auth);
    } else {
        $d = new connector($url);
    }
    return $d->file;
}

//**********************************************************************************************
//extra functions TODO: push them in class_administration.php 

/**
 * selects the wms id for a given layer id.
 *
 * @param <integer> the layer id
 * @return <string|boolean> either the id of the wms as integer or false when none exists
 */
function getWmsIdByLayerId($id)
{
    $sql = "SELECT fkey_wms_id FROM layer WHERE layer_id = $1";
    $v = array($id);
    $t = array('i');
    $res = db_prep_query($sql, $v, $t);
    $row = db_fetch_array($res);
    if ($row) return $row["fkey_wms_id"];
    else return false;
}

function getDocumentContentII($log_id, $url, $header = false)
{
    global $reqParams, $n;
    if (func_num_args() == 4) { //new for HTTP Authentication
        $auth = func_get_arg(3);
        $d = new connector($url, $auth);
    } else {
        $d = new connector($url);
    }
    $content = $d->file;
    if (strtoupper($reqParams["request"]) == "GETMAP") { // getmap
        $pattern_exc = '~EXCEPTION~i';
        preg_match($pattern_exc, $content, $exception);
        if (!$content) {
            if ($log_id != null && is_integer($log_id)) {
                $n->updateWmsLog(0, "Mb2OWSPROXY - unable to load: " . $url, "text/plain", $log_id);
            }
            header("Content-Type: text/plain");
            echo "Mb2OWSPROXY - unable to load: " . $url;
        } else if (count($exception) > 0) {
            if ($log_id != null && is_integer($log_id)) {
                $n->updateWmsLog(0, $content, $reqParams["exceptions"], $log_id);
            }
            header("Content-Type: " . $reqParams["exceptions"]);
            echo $content;
        } else {
            $source = new Imagick();
            $source->readImageBlob($content);
            $numColors = $source->getImageColors();
            if ($log_id != null && is_integer($log_id)) {
                $n->updateWmsLog($numColors <= 1 ? -1 : 1, null, null, $log_id);
            }
            header("Content-Type: " . $reqParams['format']);
            echo $content;
        }
        return true;
    } else if (strtoupper($reqParams["request"]) == "GETFEATUREINFO") { // getmap
//		header("Content-Type: ".$reqParams['info_format']);
//		$content = matchUrls($content);
//		echo $content;
        $pattern_exc = '~EXCEPTION~i';
        preg_match($pattern_exc, $content, $exception);
        if (!$content) {
            if ($log_id != null) {
                $n->updateWmsFiLog("Mb2OWSPROXY - unable to load: " . $url, "text/plain", $log_id);
            }
            header("Content-Type: text/plain");
            echo "Mb2OWSPROXY - unable to load: " . $url;
        } else if (count($exception) > 0) {
            if ($log_id != null) {
                $n->updateWmsFiLog($content, "application/xml", $log_id);
            }
            header("Content-Type: application/xml");
            echo $content;
        } else {
            header("Content-Type: " . $reqParams['info_format']);
            if ($log_id != null) {
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

?>
