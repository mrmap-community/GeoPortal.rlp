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

$INDEX_WITHOUTPASS = true;
require_once("../php/mb_validateSession.php");
require_once(dirname(__FILE__)."/../classes/class_gui.php");
require_once(dirname(__FILE__)."/../classes/class_cache.php");
require_once dirname(__FILE__) . "/../classes/class_connector.php";

//new for geoportal.rlp - some guis has special functions - for normal mapbender installation this doesn't matter
if (Mapbender::session()->get("mb_user_gui") !== false) {
	Mapbender::session()->set("previous_gui",Mapbender::session()->get("mb_user_gui"));
}
Mapbender::session()->set("mb_user_gui",$gui_id);

//
// check if user is allowed to access current GUI; 
// if not, return to login screen
//
if (!in_array($gui_id, Mapbender::session()->get("mb_user_guis"))) {
	$e = new mb_exception("mb_validateSession.php: User: " . Mapbender::session()->get("mb_user_id")  . " not allowed to access GUI " . $gui_id);
	session_write_close();
	header("Location: ".LOGIN);
	die();
}

/*****************************************************/
// Laedt die HTML Datei

if(isset($_REQUEST['lang'])) $lang = $_REQUEST['lang']; // Sprachunterscheidung
else $lang = 'de_DE';


$pageULR = 'http://'.$_SERVER["HTTP_HOST"].'/'; // URL der Seite

if(strstr($lang,'en')){
	$x = new connector($pageULR.'index.php/en/cardviewer/kartenvieweren?sid='.session_id());
	$htmlstring = $x->file;
	//$htmlstring = file_get_contents($pageULR.'index.php/en/cardviewer/kartenvieweren?sid='.session_id());
} 
else if(strstr($lang,'fr')){
	$x = new connector($pageULR.'index.php/fr/cardvue/kartenviewerfr?sid='.session_id());
	$htmlstring = $x->file;
	//$htmlstring = file_get_contents($pageULR.'index.php/fr/cardvue/kartenviewerfr?sid='.session_id());
} 
else{
	$x = new connector($pageULR.'index.php/de/kartenviewer/kartenviewerde?sid='.session_id());
	$htmlstring = $x->file;
	//$htmlstring = file_get_contents($pageULR.'index.php/de/kartenviewer/kartenviewerde?sid='.session_id());
} 



// GET HEADER - START
ob_start();
?>
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
	//check if element var for caching gui is set to true!
	$sql = "SELECT * FROM gui_element_vars WHERE fkey_gui_id = $1 AND fkey_e_id = 'body' AND var_name='cacheGuiHtml'";
	$v = array($gui_id);
	$t = array('s');
	$res = db_prep_query($sql,$v,$t);
	$row = db_fetch_array($res);
	//$e = new mb_notice("count row: ".count($row['var_name']));
	if (count($row['var_name']) == 1) {
		$activatedGuiHtmlCache = $row['var_value'];
		if ($activatedGuiHtmlCache == 'true') {
			$activatedGuiHtmlCache = true;
		} else {
			$activatedGuiHtmlCache = false;
		}
	} else {
		$activatedGuiHtmlCache = false;
	}
	//use cache is cache is activated
	//instantiate cache if available
	$cache = new Cache();
	//define key name cache
	$cacheKeyElementVars = 'guiElementVars_'.$gui_id;
	/*if ($cache->isActive && $cache->cachedVariableExists($cacheKeyElementVars)) {
		$e = new mb_exception("frames/index.php: read elementVars from ".$cache->cacheType." cache!");
		$res = $cache->cachedVariableFetch($cacheKeyElementVars);
	} else {*/
		//do sql instead
		$sql = "SELECT * FROM gui_element_vars WHERE fkey_e_id = 'body' AND fkey_gui_id = $1 and var_name='favicon' ORDER BY var_name";
		$v = array($gui_id);
		$t = array('s');
		$res = db_prep_query($sql,$v,$t);
		/*if ($cache->isActive) {
			$cache->cachedVariableAdd($cacheKeyElementVars,$res);
		}*/
	//}//uncomment for cache
	$cnt = 0;
	while($row = db_fetch_array($res)){
		echo "<link rel=\"shortcut icon\" type=\"image/png\" href=\"".$row["var_value"]."\">\n";
	}
?> 
<?php
	//reset CSS
	echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"../css/reset.css\">\n";
	//define new key name cache
	$cacheKeyGuiCss = 'guiCss_'.$gui_id;
	/*if ($cache->isActive && $cache->cachedVariableExists($cacheKeyGuiCss)) {
		$e = new mb_exception("frames/index.php: read guiCss from ".$cache->cacheType." cache!");
		$res = $cache->cachedVariableFetch($cacheKeyGuiCss);
	} else {*/

	
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
		/*if ($cache->isActive) {
			$cache->cachedVariableAdd($cacheKeyGuiCss,$res);
		}*/
	//}//for cache
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

<?php
	// GET HEADER - END
	$HEAD = ob_get_contents();
	ob_end_clean();
	
	// GET BODY - START
	ob_start();	
	if (defined(LOAD_JQUERY_FROM_GOOGLE) && LOAD_JQUERY_FROM_GOOGLE) {
		echo "<script type='text/javascript' src='http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js'></script>";
	}
	//cache complete application ;-)
	$cacheKeyGuiHtml = 'guiHtml_'.$gui_id;
	//$e = new mb_notice("frames/index.php: activatedGuiHtmlCache: ". $activatedGuiHtmlCache);
	if ($cache->isActive && $activatedGuiHtmlCache && $cache->cachedVariableExists($cacheKeyGuiHtml)) {
		//$e = new mb_notice("frames/index.php: read gui html from ".$cache->cacheType." cache!");
		echo $cache->cachedVariableFetch($cacheKeyGuiHtml);
	} else {
		$currentApplication = new gui($gui_id);
		$guiHtml = $currentApplication->toHtml();
		if ($cache->isActive) {
				$cache->cachedVariableAdd($cacheKeyGuiHtml,$guiHtml);
		}
		echo $guiHtml;
	}
	$mapPhpParameters = htmlentities($urlParameters, ENT_QUOTES, CHARSET);
	$mapPhpParameters .= "&amp;".htmlentities($_SERVER["QUERY_STRING"]);
	
	echo "<script type='text/javascript' src='../javascripts/map.php?".$mapPhpParameters."'></script>";

	


	
	
	// GET BODY - END
	$BODY = ob_get_contents();
	ob_end_clean();
	
	$BODY = preg_replace("/<body[^>]*>/i","",$BODY);
	$BODY = preg_replace("/<\/body[^>]*>/i","",$BODY);

//	echo htmlspecialchars($HEAD)."<hr/>".  htmlspecialchars($BODY);
	
//	$HEAD = ""; $BODY = "";
	$htmlstring = str_replace('[%MB_HEADER%]', $HEAD, $htmlstring);
	$htmlstring = str_replace('[%MB_CONTENT%]', $BODY, $htmlstring);
	echo $htmlstring;
?>
