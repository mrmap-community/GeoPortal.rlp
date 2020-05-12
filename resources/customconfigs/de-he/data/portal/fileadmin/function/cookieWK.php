<?php
#ini_set("session.use_trans_sid","1"); changed armin 2010.09.07
ini_set ('url_rewriter.tags', 'a=href,area=href,frame=src,input=src,fieldset=');
#session_start();
include_once(dirname(__FILE__)."/../../../mapbender/core/globalSettings.php");
#session_name(MB_SESSION_NAME);
session_start();

if(!isset($_SESSION["mb_user_name"])) {
	$isAuthenticated = authenticate ('guest','guest');
	if($isAuthenticated != false){
		$_SESSION["mb_user_password"] = 'guest';
		$_SESSION["mb_user_id"] = $isAuthenticated["mb_user_id"];
		$_SESSION["mb_user_name"] = 'guest';
		$_SESSION["mb_user_ip"] =  $_SERVER['REMOTE_ADDR'];
       		$_SESSION["HTTP_HOST"]=$_SERVER["HTTP_HOST"];
		$_SESSION["epsg"]="EPSG:25832";
		$_SESSION["mb_myBBOX"]="";
		$_SESSION["mb_user_gui"]="BPlan_Viewer_Wetterau";
//		$_SESSION["mb_user_gui"]="";
		$_SESSION["layer_preview"]=0;
		$_SESSION["mb_user_spatial_suggest"]='nein';
		$_SESSION["mb_lang"]="de";
	}
	require_once(dirname(__FILE__)."/../../../mapbender/http/php/mb_getGUIs.php");
	$arrayGUIs = mb_getGUIs($isAuthenticated["mb_user_id"]);
	$_SESSION["mb_user_guis"] = $arrayGUIs;
}
//session_write_close(); //we wan't to write gml to session too afterwards ;-)

function authenticate ($name,$pw){
	$con = db_connect(DBSERVER,OWNER,PW);
	db_select_db(DB,$con);
	$sql = "SELECT * FROM mb_user WHERE mb_user_name = $1 AND mb_user_password = $2";
	$v = array($name,md5($pw)); // wird in unserer Lösung immer md5 genutzt?
	$t = array('s','s');
	$res = db_prep_query($sql,$v,$t);
	if($row = db_fetch_array($res)){
		return $row;
	} else {
		return false;
	}
}
?>
