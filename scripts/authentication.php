<?php
include_once(dirname(__FILE__)."/../../core/globalSettings.php");
require_once(dirname(__FILE__)."/../classes/class_user.php");
$pw = $_REQUEST['password'];
$name = $_REQUEST['name'];
$e = new mb_notice('SESSION[mb_user_name]: '.Mapbender::session()->get("mb_user_name"));

# Q4U - Michael Spitz - 16.08.2006 - Falls Cookies deaktiviert sind, muss die Session-ID an die Folgeseiten weitergereicht werden
$URLAdd="";
if($_COOKIE[session_name()]=="") {
	$URLAdd="?".session_name()."=".$_REQUEST[session_name()];
}

$isAuthenticated = authenticate($name,$pw);

if(is_array($isAuthenticated) != false) {
	setSession();
	Mapbender::session()->set("mb_user_password",$pw);
  	Mapbender::session()->set("mb_user_id",$isAuthenticated["mb_user_id"]);
	Mapbender::session()->set("mb_user_name",$isAuthenticated["mb_user_name"]);
	Mapbender::session()->set("mb_user_ip",$_SERVER['REMOTE_ADDR']);
	Mapbender::session()->set("mb_user_email",$isAuthenticated["mb_user_email"]);
	Mapbender::session()->set("mb_user_department",$isAuthenticated["mb_user_department"]);
	Mapbender::session()->set("mb_user_organisation_name",$isAuthenticated["mb_user_organisation_name"]);
	Mapbender::session()->set("mb_user_position_name",$isAuthenticated["mb_user_position_name"]);
	Mapbender::session()->set("mb_user_phone",$isAuthenticated["mb_user_phone"]);
	Mapbender::session()->set("Textsize",$isAuthenticated["mb_user_textsize"]);
	Mapbender::session()->set("Glossar",$isAuthenticated["mb_user_glossar"]);
	Mapbender::session()->set("mb_user_spatial_suggest",$isAuthenticated["mb_user_spatial_suggest"]);
	$e = new mb_notice('geoportal/authentication.php: mb_user_newsletter: '.$isAuthenticated["mb_user_newsletter"]);
	if ($isAuthenticated["mb_user_newsletter"] == "t") {
		Mapbender::session()->set("mb_user_newsletter","ja");
	} else {
		Mapbender::session()->set("mb_user_newsletter","nein");
	}
	if ($isAuthenticated["mb_user_allow_survey"] == "t") {
		Mapbender::session()->set("mb_user_allow_survey","ja");
	} else {
		Mapbender::session()->set("mb_user_allow_survey","nein");
	}
	Mapbender::session()->set("mb_user_description",$isAuthenticated["mb_user_description"]);
	Mapbender::session()->set("mb_user_city",$isAuthenticated["mb_user_city"]);
	Mapbender::session()->set("mb_user_postal_code",$isAuthenticated["mb_user_postal_code"]);
	Mapbender::session()->set("epsg","EPSG:31466");
	Mapbender::session()->set("HTTP_HOST",$_SERVER["HTTP_HOST"]);
	Mapbender::session()->set("preferred_gui",$isAuthenticated["fkey_preferred_gui_id"]);
	Mapbender::session()->set("django","true");
//INSERT LAST LOGIN DATE AND TIME
//NEW Filed required "ALTER TABLE mapbender.mb_user ADD COLUMN mb_user_last_login_date date;"
	$sql = "UPDATE mb_user SET";
	$sql .= " mb_user_last_login_date = now()";
	$V[0] = Mapbender::session()->get('mb_user_id');
	$T[0] = 'i';
	$sql .= 'WHERE mb_user_id = $1';
	$res = db_prep_query($sql, $V, $T);
	//UPDATE USER LOGIN DATE and TIME
	require_once(dirname(__FILE__)."/../php/mb_getGUIs.php");
	$arrayGUIs = mb_getGUIs($isAuthenticated["mb_user_id"]);
	Mapbender::session()->set("mb_user_guis",$arrayGUIs);

	$URLAdd="?status=success";
	if($isAuthenticated["timestamp_dsgvo_accepted"] == ""){
		Mapbender::session()->set("dsgvo","no");
	}else{
		Mapbender::session()->set("dsgvo","yes");
	}

	if($_SERVER["HTTPS"] != "on") {
		header ("Location: http://".$_SERVER['HTTP_HOST'].$URLAdd);
	} else  {
		header ("Location: https://".$_SERVER['HTTP_HOST'].$URLAdd);
	}
	session_write_close();

} else if (strpos($isAuthenticated,'Account for user with name') !== false){

	$URLAdd="?status=notactive";
	if($_SERVER["HTTPS"] != "on") {
		header ("Location: http://".$_SERVER['HTTP_HOST'].$URLAdd);
	} else {
		header ("Location: https://".$_SERVER['HTTP_HOST'].$URLAdd);
	}

} else if (strpos($isAuthenticated,'Password failed third time for') !== false){

	$URLAdd="?status=fail3";
	if($_SERVER["HTTPS"] != "on") {
		header ("Location: http://".$_SERVER['HTTP_HOST'].$URLAdd);
	} else {
		header ("Location: https://".$_SERVER['HTTP_HOST'].$URLAdd);
	}

} else {
	$URLAdd="?status=fail";
	if($_SERVER["HTTPS"] != "on") {
		header ("Location: http://".$_SERVER['HTTP_HOST'].$URLAdd);
	} else  {
		header ("Location: https://".$_SERVER['HTTP_HOST'].$URLAdd);
	}

}

function authenticate ($name,$pw){
	$user = new User();
	$returnObject = json_decode($user->authenticateUserByName($name, $pw));
	if ($returnObject->success !== false) {
		$returnObject = json_decode(json_encode($returnObject->result), JSON_OBJECT_AS_ARRAY);
	} else {
	  $returnObject = $returnObject->error->message;
	}
	return $returnObject;
}
function setSession(){
	session_start(); //function is ok cause the session will be closed directly after starting it!
	session_write_close();
}
function killSession(){
	Mapbender::session()->kill();
}
?>
