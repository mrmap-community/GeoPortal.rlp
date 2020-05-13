<?php
# $Id: index.php 6950 2010-09-27 18:13:36Z armin11 $
# http://www.mapbender.org/index.php/index.php
#
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

//require_once("../php/mb_validateSession.php");
require_once dirname(__FILE__) . "/../../conf/mapbender.conf";
require_once dirname(__FILE__) . "/../classes/class_connector.php";
require_once(dirname(__FILE__)."/../classes/class_gui.php");

//session_start();

function auth_user($name,$pw){
        $setEncPw = false;
        $sql = "SELECT * FROM mb_user WHERE mb_user_name = $1 AND mb_user_password = $2";
        $v = array($name,md5($pw));
        $t = array('s','s');
        $res = db_prep_query($sql,$v,$t);
        if($row = db_fetch_array($res)){
                return $row;
        }
        else if(SYS_DBTYPE == 'pgsql' && $setEncPw == true){
                //      unencrypted pw in postgres without md5-support?
                $sql = "SELECT * FROM mb_user WHERE mb_user_name = $1 AND mb_user_password = $2";
                $v = array($name,$pw);
                $t = array('s','s');
                $resn = db_prep_query($sql,$v,$t);
                if($rown = db_fetch_array($resn)){
                        $sqlu = "UPDATE mb_user SET mb_user_password = $1 WHERE mb_user_id = $2";
                        $vu = array(md5($pw),$rown["mb_user_id"]);
                        $tu = array('s','i');
                        $rowu = db_prep_query($sqlu,$vu,$tu);
                        return $rown;
                }
        }
}

//new for geoportal.rlp - some guis has special functions - for normal mapbender installation this doesn't matter
if (Mapbender::session()->get("mb_user_gui") !== false) {
	Mapbender::session()->set("previous_gui",Mapbender::session()->get("mb_user_gui"));
}

//
// check if user is allowed to access current GUI; 
// if not, return to login screen
//
$name= "guest";
$password = "guest";
$mb_user_myGui= "Windpotenzial";
$gui_id = "Windpotenzial";

        require_once dirname(__FILE__)."/../../core/system.php";
        require_once dirname(__FILE__)."/../../lib/class_Mapbender.php";
        require_once dirname(__FILE__)."/../../lib/class_Mapbender_session.php";
        require_once dirname(__FILE__) . "/../../core/globalSettings.php";
        $row = auth_user($name, $password);

		include(dirname(__FILE__) . "/../../conf/session.conf");

Mapbender::session()->set("mb_user_gui","Windpotenzial");
 
//lösche session wmc, wenn von einer in andere Gui gewechselt wird

if (Mapbender::session()->exists("mb_wmc")) {
	Mapbender::session()->delete("mb_wmc");
}


//if (!in_array($gui_id, Mapbender::session()->get("mb_user_guis"))) {
//	$e = new mb_exception("mb_validateSession.php: User: " . Mapbender::session()->get("mb_user_id")  . " not allowed to access GUI " . $gui_id);
//	session_write_close();
//	header("Location: ".LOGIN);
//	die();
 //}
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<!--
Licensing: See the GNU General Public License for more details.
http://www.gnu.org/copyleft/gpl.html
or:
mapbender/licence/
-->
<meta http-equiv="cache-control" content="no-cache">
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="expires" content="0">
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET;?>">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo  $gui_id;?> - presented by Mapbender</title>
<?php
	$sql = "SELECT * FROM gui_element_vars WHERE fkey_e_id = 'body' AND fkey_gui_id = $1 and var_name='favicon' ORDER BY var_name";
	$v = array($gui_id);
	$t = array('s');
	$res = db_prep_query($sql,$v,$t);
	$cnt = 0;
	while($row = db_fetch_array($res)){
		echo "<link rel=\"shortcut icon\" type=\"image/png\" href=\"".$row["var_value"]."\">\n";
	}
?> 
<?php
	// reset CSS
	echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"../css/reset.css\">\n";

	$sql = <<<SQL
	
SELECT DISTINCT e_id, e_element, var_value, var_name, var_type FROM gui_element, gui_element_vars 
WHERE 
	e_id = fkey_e_id 
	AND e_element <> 'iframe' 
	AND gui_element.fkey_gui_id = $1 
	AND gui_element_vars.fkey_gui_id = $1 
	AND var_type='file/css' 
ORDER BY var_name

SQL;

	$v = array($gui_id);
	$t = array('s');
	$res = db_prep_query($sql,$v,$t);
	$cnt = 0;
	while($row = db_fetch_array($res)){
		echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"".$row["var_value"]."\">\n";
	}
?>
<style type="text/css">
<!--
<?php
	$sql = <<<SQL
	
SELECT DISTINCT e_id, e_element, var_value, var_name, var_type FROM gui_element, gui_element_vars 
WHERE 
	e_id = fkey_e_id 
	AND e_element <> 'iframe' 
	AND gui_element.fkey_gui_id = $1 
	AND gui_element_vars.fkey_gui_id = $1 
	AND var_type = 'text/css' 
ORDER BY var_name

SQL;

	$v = array($gui_id);
	$t = array('s');
	$res = db_prep_query($sql,$v,$t);
	$cnt = 0;
	while($row = db_fetch_array($res)){
		echo $row["var_value"] . "\n";
	}
?>
-->
</style>
<script type='text/javascript' src='../javascripts/core.php'></script>
</head>
<?php
	if (defined(LOAD_JQUERY_FROM_GOOGLE) && LOAD_JQUERY_FROM_GOOGLE) {
		echo "<script type='text/javascript' src='http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js'></script>";
	}
	$currentApplication = new gui($gui_id);
	echo $currentApplication->toHtml();
	
	$mapPhpParameters = htmlentities($urlParameters, ENT_QUOTES, CHARSET);
	$mapPhpParameters .= "&amp;".htmlentities($_SERVER["QUERY_STRING"]);
	echo "<script type='text/javascript' src='../javascripts/map.php?".$mapPhpParameters."'></script>";

?>
</html>
