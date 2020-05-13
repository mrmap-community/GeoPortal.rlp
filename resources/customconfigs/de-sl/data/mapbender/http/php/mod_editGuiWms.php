<?php

# $Id: mod_editGuiWms.php 8491 2012-09-18 15:11:04Z verenadiewald $
# http://www.mapbender.org/index.php/mod_editGuiWms.php
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

$e_id = "editGUI_WMS";
require_once (dirname(__FILE__) . "/../php/mb_validatePermission.php");
require_once (dirname(__FILE__) . "/../classes/class_administration.php");
$admin = new administration();

/*  
 * @security_patch irv done 
 */
//security_patch_log(__FILE__,__LINE__); 

$guiList = $_POST["guiList"];
$wmsList = $_POST["wmsList"];
$up = $_POST["up"];
$down = $_POST["down"];
$del = $_POST["del"];
$this_gui_wms_epsg = $_POST["this_gui_wms_epsg"];
$this_gui_wms_mapformat = $_POST["this_gui_wms_mapformat"];
$this_gui_wms_featureinfoformat = $_POST["this_gui_wms_featureinfoformat"];
$this_gui_wms_exceptionformat = $_POST["this_gui_wms_exceptionformat"];
$this_gui_wms_visible = $_POST["this_gui_wms_visible"];
$this_gui_wms_opacity = $_POST["this_gui_wms_opacity"];
$this_gui_wms_sldurl = $_POST["this_gui_wms_sldurl"];
$this_gui = $_POST["this_gui"];
$this_wms = $_POST["this_wms"];
$this_layer_count = $_POST["this_layer_count"];
$update_content = $_POST["update_content"];

require_once (dirname(__FILE__) . "/../classes/class_wms.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
<?php

echo '<meta http-equiv="Content-Type" content="text/html; charset=' . CHARSET . '">';
echo '<link rel="stylesheet" type="text/css" href="../extensions/jquery-ui-1.7.2.custom/css/smoothness/jquery-ui-1.7.2.custom.css">';
?>
<title>Edit GUI WMS</title>
<?php

include_once '../include/dyn_css.php';

function toImage($text) {
	$angle = 90;
	if (extension_loaded("gd2")) {
		return "<img src='../php/createImageFromText.php?text=" . urlencode($text) . "&angle=" . $angle . "'>";
	}
	return $text;
}
?>

<script language="JavaScript">

<?php

require_once (dirname(__FILE__) . "/../extensions/jquery-ui-1.7.2.custom/js/jquery-1.3.2.min.js");
require_once (dirname(__FILE__) . "/../extensions/jquery-ui-1.7.2.custom/js/jquery-ui-1.7.2.custom.min.js");
require_once (dirname(__FILE__) . "/../extensions/jquery-ui-1.7.2.custom/development-bundle/ui/min.ui.dialog.js");
require_once (dirname(__FILE__) . "/../javascripts/mod_wfsLayerObj_conf.js");
header('Content-type: text/html');
?>

function validate(wert){
	if(wert == 'delete_wms'){
		if(document.form1.wmsList.selectedIndex == -1){
			document.form1.wmsList.style.background = '#ff0000';
		}else{

			var secure = confirm("Remove WMS in this GUI ?");
			if(secure == true){
				document.form1.del.value='true';
				document.form1.submit();
			}
		}
	}

	if(wert == 'up_wms'){
		if(document.form1.wmsList.selectedIndex == -1){
			document.form1.wmsList.style.background = '#ff0000';
		}else{
			if (document.form1.wmsList.selectedIndex>0){
				document.form1.up.value='true';
				document.form1.submit();
			}
		}
	}

	if(wert == 'down_wms'){
		if(document.form1.wmsList.selectedIndex == -1){
			document.form1.wmsList.style.background = '#ff0000';
		}else{
			if (document.form1.wmsList.selectedIndex<document.form1.wmsList.length-1){
				document.form1.down.value='true';
				document.form1.submit();
			}
		}
	}
}
function checkBoxValue(){
   for(var i=0; i<document.forms[0].elements.length; i++){
      if(document.forms[0].elements[i].type == 'checkbox'){
         if(document.forms[0].elements[i].checked == true){
            document.forms[0].elements[i].value = '1';
         }
         else{
            document.forms[0].elements[i].value = '0';
            document.forms[0].elements[i].checked = true;
         }
      }
      if(document.forms[0].elements[i].type == 'text' && ( document.forms[0].elements[i].name.indexOf("minscale") > -1 || document.forms[0].elements[i].name.indexOf("maxscale") > -1 )){
         var nr = parseInt(document.forms[0].elements[i].value);
         if(isNaN(nr) == true){document.forms[0].elements[i].value = 0;}
         else{document.forms[0].elements[i].value = nr;}
      }
   }
   document.forms[0].update_content.value=1;
   document.forms[0].submit();
}
function getAllLayer(){
   var arrayLayer = new Array();
   var cntLayer = 0;
   for(var i=0; i<document.forms[0].elements.length; i++){
      if(document.forms[0].elements[i].name.indexOf("layer_id") > -1){
         arrayLayer[cntLayer] = document.forms[0].elements[i].value;
         cntLayer++;
      }
   }
   return arrayLayer;
}
function setSubs(){
   var arrayLayer = getAllLayer();
   for(var i=0; i<arrayLayer.length; i++){
      if(parseInt(eval("document.forms[0].L_" + arrayLayer[i] + "___layer_parent.value")) > 0){
         eval("document.forms[0].L_" + arrayLayer[i] + "___gui_layer_status.checked = false");
      }
   }
}
function setLayer(def,status){
   var arrayLayer = getAllLayer();
   if(def == 'querylayer'){
      for(var i=1; i<arrayLayer.length; i++){
         if(eval("document.forms[0].L_" + arrayLayer[i] + "___gui_layer_querylayer.disabled == false")){
            eval("document.forms[0].L_" + arrayLayer[i] + "___gui_layer_querylayer.checked = " + status);
         }
      }
   }
   if(def == 'visible'){
      for(var i=1; i<arrayLayer.length; i++){
         eval("document.forms[0].L_" + arrayLayer[i] + "___gui_layer_visible.checked = " + status);
      }
   }
}
function showSld(origUrl){
	var url = document.getElementById("this_gui_wms_sldurl").value;
	if(url==""){
		if(origUrl=="")
			return;
		url=origUrl;
	}
	window.open(url);
}
</script>
<link rel="stylesheet" type="text/css" href="../css/edit_gui.css" />
</head>
<body>
<?php

require_once (dirname(__FILE__) . "/../../conf/mapbender.conf");
$con = db_connect($DBSERVER, $OWNER, $PW);
db_select_db(DB, $con);

$mb_user_id = Mapbender :: session()->get("mb_user_id");

//if changes are made here for gui wms selection, unlink the application cache file in cache directory
if ((isset ($update_content) && $update_content == "1") ||
    ($del && $del == 'true') ||
    ($up && $up == 'true') ||
    ($down && $down == 'true')
) {
    #$admin->clearJsCacheFile($guiList);
}
   
#delete gui_wms from gui
if ($del && $del == 'true') {
	$sql = "SELECT DISTINCT gui_wms_position from gui_wms WHERE fkey_gui_id = $1 and fkey_wms_id = $2";
	$v = array (
		$guiList,
		$wmsList
	);
	$t = array (
		's',
		'i'
	);
	$res = db_prep_query($sql, $v, $t);
	$cnt = 0;
	while ($row = db_fetch_array($res)) {
		$wms_position = $row["gui_wms_position"];
		$cnt++;
	}
	#if($cnt > 1){die("Error: WMS (ID) not unique!");}
	$sql = "Delete from gui_wms where fkey_gui_id = $1 and fkey_wms_id = $2 ";
	$v = array (
		$guiList,
		$wmsList
	);
	$t = array (
		's',
		'i'
	);
	$res = db_prep_query($sql, $v, $t);
	$sql = "Delete from gui_layer where fkey_gui_id = $1 and gui_layer_wms_id = $2";
	$v = array (
		$guiList,
		$wmsList
	);
	$t = array (
		's',
		'i'
	);
	$res = db_prep_query($sql, $v, $t);
	$del = 'false';
	$sql = "UPDATE gui_wms SET gui_wms_position = (gui_wms_position - 1) WHERE gui_wms_position > $1";
	$sql .= " AND fkey_gui_id = $2 ";
	$v = array (
		$wms_position,
		$guiList
	);
	$t = array (
		'i',
		's'
	);
	$res = db_prep_query($sql, $v, $t);

	unset ($wmsList);
}

#update gui_wms_position
if ($up && $up == 'true') {
	if ($wmsList != "") {
		$sql = "SELECT gui_wms_position ";
		$sql .= "FROM gui_wms WHERE fkey_gui_id = $1 AND fkey_wms_id = $2";
		$v = array (
			$guiList,
			$wmsList
		);
		$t = array (
			's',
			'i'
		);
		$res = db_prep_query($sql, $v, $t);
		if ($row = db_fetch_array($res)) {
			$wms_position = $row["gui_wms_position"];
		}
	}
	if ($wms_position > 0) {
		$sql = "UPDATE gui_wms SET ";
		$sql .= "gui_wms_position = $1";
		$sql .= " WHERE fkey_gui_id = $2 AND fkey_wms_id = $3";
		$v = array (
			($wms_position -1
		), $guiList, $wmsList);
		$t = array (
			'i',
			's',
			'i'
		);
		$res = db_prep_query($sql, $v, $t);
		$sql = "UPDATE gui_wms SET ";
		$sql .= "gui_wms_position = $1";
		$sql .= " WHERE gui_wms_position = $2 AND fkey_gui_id = $3 AND fkey_wms_id <> $4 ";
		$v = array (
			$wms_position,
			 ($wms_position -1
		), $guiList, $wmsList);
		$t = array (
			'i',
			'i',
			's',
			'i'
		);
		$res = db_prep_query($sql, $v, $t);
	}
}

if ($down && $down == 'true') {
	$max = 0;
	if ($wmsList != "") {
		$sql = "SELECT gui_wms_position ";
		$sql .= "FROM gui_wms WHERE fkey_gui_id = $1 AND fkey_wms_id = $2";
		$v = array (
			$guiList,
			$wmsList
		);
		$t = array (
			's',
			'i'
		);
		$res = db_prep_query($sql, $v, $t);
		if ($row = db_fetch_array($res)) {
			$wms_position = $row["gui_wms_position"];
		}
		$sql = "SELECT MAX(gui_wms_position) as max FROM gui_wms WHERE fkey_gui_id = $1 ";
		$v = array (
			$guiList
		);
		$t = array (
			's'
		);
		$res = db_prep_query($sql, $v, $t);
		if ($row = db_fetch_array($res)) {
			$max = $row["max"];
		}
	}
	if ($wms_position < $max) {
		$sql = "UPDATE gui_wms SET ";
		$sql .= "gui_wms_position = $1";
		$sql .= " WHERE fkey_gui_id = $2 AND fkey_wms_id = $3";
		$v = array (
			($wms_position +1
		), $guiList, $wmsList);
		$t = array (
			'i',
			's',
			'i'
		);
		$res = db_prep_query($sql, $v, $t);
		$sql = "UPDATE gui_wms SET ";
		$sql .= "gui_wms_position = $1";
		$sql .= " WHERE gui_wms_position = $2 AND fkey_gui_id = $3 AND fkey_wms_id <> $4";
		$v = array (
			$wms_position,
			 ($wms_position +1
		), $guiList, $wmsList);
		$t = array (
			'i',
			'i',
			's',
			'i'
		);
		$res = db_prep_query($sql, $v, $t);
	}
}

/*handle Updates*/
if (isset ($update_content) && $update_content == "1") {
	if (isset ($this_gui_wms_epsg)) {
		$sql = "UPDATE gui_wms set gui_wms_epsg = $1, gui_wms_mapformat = $2, ";
		$sql .= "gui_wms_featureinfoformat = $3, gui_wms_exceptionformat = $4, ";
		$sql .= "gui_wms_visible = $5, gui_wms_opacity = $6, gui_wms_sldurl = $7 ";
		$sql .= "WHERE fkey_gui_id = $8 AND fkey_wms_id = $9";
		$v = array (
			$this_gui_wms_epsg,
			$this_gui_wms_mapformat,
			$this_gui_wms_featureinfoformat,
			$this_gui_wms_exceptionformat,
			$this_gui_wms_visible,
			$this_gui_wms_opacity,
			$this_gui_wms_sldurl,
			$this_gui,
			$this_wms
		);
		$t = array (
			's',
			's',
			's',
			's',
			'i',
			'i',
			's',
			's',
			'i'
		);
		$res = db_prep_query($sql, $v, $t);
	} else {
		$sql = "UPDATE gui_wms set gui_wms_mapformat = $1, ";
		$sql .= "gui_wms_featureinfoformat = $2, gui_wms_exceptionformat = $3, ";
		$sql .= "gui_wms_visible = $4, gui_wms_opacity = $5, gui_wms_sldurl = $6 ";
		$sql .= "WHERE fkey_gui_id = $7 AND fkey_wms_id = $8";
		$v = array (
			$this_gui_wms_mapformat,
			$this_gui_wms_featureinfoformat,
			$this_gui_wms_exceptionformat,
			$this_gui_wms_visible,
			$this_gui_wms_opacity,
			$this_gui_wms_sldurl,
			$this_gui,
			$this_wms
		);
		$t = array (
			's',
			's',
			's',
			'i',
			'i',
			's',
			's',
			'i'
		);
		$res = db_prep_query($sql, $v, $t);
	}

	/* */

	$cnt = 0;
	while (list ($key, $val) = each($_POST)) {
		if (preg_match("/___/", $key)) {
			$myKey = explode("___", $key);
			if ($myKey[1] != "layer_parent" && $myKey[1] != 'layer_id') {
				$sql = "UPDATE gui_layer SET " . $myKey[1] . " = $1 WHERE fkey_gui_id = $2 AND fkey_layer_id = $3";
				$v = array (
					$val,
					$this_gui,
					preg_replace("/L_/",
					"",
					$myKey[0]
				));
				if ($myKey[1] == 'gui_layer_style') {
					$t = array (
						's',
						's',
						'i'
					);
				} else {
					$t = array (
						'i',
						's',
						'i'
					);
				}
				if (!$res = db_prep_query($sql, $v, $t)) {
					echo "FEHLER in ZEILE 288";
				}
			}
		}
	}
}

echo "<form name='form1' action='" . $self . "' method='post'>";
echo "<table cellpadding='3' cellspacing='3' border='0' class='table_top'>";

echo "<tr>";
echo "<td>";
echo "GUI";
echo "</td>";
echo "<td>";
echo "WMS-TITLE";
echo "</td>";
echo "<td>";
echo "</td>";
echo "<tr>";

echo "<td>";

require_once (dirname(__FILE__) . "/../classes/class_administration.php");
$admin = new administration();
$ownguis = $admin->getGuisByOwner(Mapbender :: session()->get("mb_user_id"), true);

$gui_id = array ();
if (count($ownguis) > 0) {
	for ($i = 0; $i < count($ownguis); $i++) {
		$gui_id[$i] = $ownguis[$i];
	}
}

echo "<select size='8' name='guiList' onchange='document.form1.wmsList.selectedIndex = -1;submit()'>";
$selected_gui_id = "";

for ($i = 0; $i < count($ownguis); $i++) {
	echo "<option value='" . $gui_id[$i] . "' ";
	if ($guiList && $guiList == $gui_id[$i]) {
		echo "selected";
		$selected_gui_id = $gui_id[$i];
	} else {
		if ($i == 0) {
			echo "selected";
			$selected_gui_id = $gui_id[$i];
		}
	}
	echo ">" . $gui_id[$i] . "</option>";
}

echo "</select>";
echo "</td>";
echo "<td>";

$sql = "SELECT * from gui_wms JOIN gui ON gui_wms.fkey_gui_id = gui.gui_id JOIN wms ON ";
$sql .= "gui_wms.fkey_wms_id = wms.wms_id AND gui_wms.fkey_gui_id=gui.gui_id WHERE gui.gui_id = $1 ORDER BY gui_wms_position";
$v = array (
	$selected_gui_id
);
$t = array (
	's'
);
$res = db_prep_query($sql, $v, $t);
$count_wms = 0;

# WMS select box on right side
echo "<select size='8' name='wmsList' style='width:300px' onchange='submit()'>";

while ($row = db_fetch_array($res)) {
	echo "<option title='" . htmlentities($row["wms_abstract"], ENT_QUOTES, "UTF-8") . "'  value='" . $row["wms_id"] . "' ";
	if (isset ($wmsList) && $wmsList == $row["wms_id"]) {
		echo "selected";
	}
	echo ">" . $row["gui_wms_position"] . " - " . $row["wms_title"] . "</option>";
	$count_wms++;
}
echo "</select>";
echo "</td><td>";
echo "<input class='myButton' type='button' name='up_wms' value=' up ' onClick='validate(\"up_wms\")'>";
echo "<input type='hidden' name='up' value=''><br><br>";

echo "<input class='myButton' type='button' name='down_wms' value='down'  onClick='validate(\"down_wms\")'>";
echo "<input type='hidden' name='down' value=''><br><br>";

echo "<input class='myButton' type='button' name='delete_wms' value='remove'  onClick='validate(\"delete_wms\")'>";
echo "<input type='hidden' name='del' value=''>";

echo "</td>";
echo "</tr>";
echo "</table>";

if (isset ($wmsList)) {
	#gui_wms
	$sql_gw = "SELECT * FROM gui_wms WHERE fkey_gui_id = $1 AND fkey_wms_id = $2";
	$v = array (
		$guiList,
		$wmsList
	);
	$t = array (
		's',
		'i'
	);
	$res_gw = db_prep_query($sql_gw, $v, $t);
	$cnt_gw = 0;
	while ($row = db_fetch_array($res_gw)) {
		$gui_wms_position[$cnt_gw] = $row["gui_wms_position"];
		$gui_wms_mapformat[$cnt_gw] = $row["gui_wms_mapformat"];
		$gui_wms_featureinfoformat[$cnt_gw] = $row["gui_wms_featureinfoformat"];
		$gui_wms_exceptionformat[$cnt_gw] = $row["gui_wms_exceptionformat"];
		$gui_wms_epsg[$cnt_gw] = $row["gui_wms_epsg"];
		$gui_wms_visible[$cnt_gw] = $row["gui_wms_visible"];
		$gui_wms_opacity[$cnt_gw] = $row["gui_wms_opacity"];
		$gui_wms_sldurl[$cnt_gw] = $row["gui_wms_sldurl"]; # sld url
		$cnt_gw++;
	}
	#wms
	$sql_w = "SELECT * FROM wms WHERE wms_id = $1";
	$v = array (
		$wmsList
	);
	$t = array (
		'i'
	);
	$res_w = db_prep_query($sql_w, $v, $t);
	$cnt_w = 0;
	while ($row = db_fetch_array($res_w)) {
		$wms_id[$cnt_w] = $row["wms_id"];
		$wms_version[$cnt_w] = $row["wms_version"];
		$wms_title[$cnt_w] = $row["wms_title"];
		$wms_abstract[$cnt_w] = htmlentities($row["wms_abstract"], ENT_QUOTES, "UTF-8");
		$wms_getcapabilities[$cnt_w] = $row["wms_getcapabilities"];
		$wms_supportsld[$cnt_w] = $row["wms_supportsld"]; # Buttons zum sld support anzeigen?
		$cnt_w++;
	}
	#wms_format
	$sql_wf = "SELECT * FROM  wms_format WHERE  fkey_wms_id = $1";
	$v = array (
		$wmsList
	);
	$t = array (
		'i'
	);
	$res_wf = db_prep_query($sql_wf, $v, $t);
	$cnt_wf = 0;
	while ($row = db_fetch_array($res_wf)) {
		$data_type[$cnt_wf] = $row["data_type"];
		$data_format[$cnt_wf] = $row["data_format"];
		$cnt_wf++;
	}
	#gui_layer
	$sql_gl = "SELECT l.*, gl.*, sld.sld_user_layer_id, sld.use_sld FROM layer AS l, gui_layer AS gl left outer join sld_user_layer AS sld on sld.fkey_layer_id = gl.fkey_layer_id WHERE l.layer_id = gl.fkey_layer_id AND gl.gui_layer_wms_id = $1 AND gl.fkey_gui_id = $2 AND (sld.fkey_gui_id = $3 or sld.fkey_gui_id is NULL) AND (sld.fkey_mb_user_id = $4 or sld.fkey_mb_user_id is NULL) ORDER BY l.layer_pos";
	$v = array (
		$wmsList,
		$guiList,
		$guiList,
		$mb_user_id
	);
	$t = array (
		'i',
		's',
		's',
		'i'
	);

	$res_gl = db_prep_query($sql_gl, $v, $t);
	$gui_layer_status = array ();
	$gui_layer_title = array ();
	$gui_layer_selectable = array ();
	$gui_layer_visible = array ();
	$gui_layer_queryable = array ();
	$gui_layer_querylayer = array ();
	$gui_layer_minscale = array ();
	$gui_layer_maxscale = array ();
	$gui_layer_priority = array ();
	$gui_layer_style = array ();
	$gui_layer_wfs_featuretype = array ();
	$layer_maxscale = array ();
	$layer_id = array ();
	$layer_parent = array ();
	$layer_name = array ();
	$layer_title = array ();
	$layer_queryable = array ();
	$layer_minscale = array ();
	$layer_maxscale = array ();
	$sld_user_layer_id = array ();
	$use_sld = array ();
	while ($row = db_fetch_array($res_gl)) {
		array_push($gui_layer_status, $row["gui_layer_status"]);
		array_push($gui_layer_title, $row["gui_layer_title"]);
		array_push($gui_layer_selectable, $row["gui_layer_selectable"]);
		array_push($gui_layer_visible, $row["gui_layer_visible"]);
		array_push($gui_layer_queryable, $row["gui_layer_queryable"]);
		array_push($gui_layer_querylayer, $row["gui_layer_querylayer"]);
		array_push($gui_layer_minscale, $row["gui_layer_minscale"]);
		array_push($gui_layer_maxscale, $row["gui_layer_maxscale"]);
		array_push($gui_layer_priority, $row["gui_layer_priority"]);
		array_push($gui_layer_style, $row["gui_layer_style"]);
		array_push($gui_layer_wfs_featuretype, $row["gui_layer_wfs_featuretype"]);
		array_push($layer_id, $row["layer_id"]);
		array_push($layer_parent, $row["layer_parent"]);
		array_push($layer_name, $row["layer_name"]);
		array_push($layer_title, $row["layer_title"]);
		array_push($layer_queryable, $row["layer_queryable"]);
		array_push($layer_minscale, $row["layer_minscale"]);
		array_push($layer_maxscale, $row["layer_maxscale"]);
		array_push($sld_user_layer_id, $row["sld_user_layer_id"]);
		array_push($use_sld, $row["use_sld"]);
	}

	#layer_epsg
	$sql_le = "SELECT * FROM layer_epsg WHERE  fkey_layer_id = $1";
	$v = array (
		$layer_id[0]
	);
	$t = array (
		'i'
	);
	$res_le = db_prep_query($sql_le, $v, $t);
	$cnt_le = 0;
	while ($row = db_fetch_array($res_le)) {
		$epsg[$cnt_le] = $row["epsg"];
		$cnt_le++;
	}

	# Save button
	echo "<div style='position:absolute;left:500px; top:225px;' ><input type='button' style='width:130px;height:80px;background-color:#ffeded' value='Save Settings' onclick='checkBoxValue()'></div>";
	echo "<table class='table_top'>";
	echo "<tr>";
	echo "<td colspan='2'>";
	echo "<a href='" . $wms_getcapabilities[0];
	echo wms :: getConjunctionCharacter($wms_getcapabilities[0]);
	if ($wms_version[0] == "1.0.0") {
		echo "WMTVER=" . $wms_version[0] . "&REQUEST=capabilities";
	} else {
		echo "VERSION=" . $wms_version[0] . "&REQUEST=GetCapabilities&SERVICE=WMS";
	}
	echo "' style='font-size:14px' target='_blank'>LINK: Capabilities</a>";
	echo "</td>";
	echo "<td align = right>WMS ID: " . $wms_id[0] . "</td>";
	echo "<tr>";
	#epsg
	if ($gui_wms_position[0] == 0) {
		echo "<tr>";
		echo "<td>EPSG: </td><td>";
		echo "<select class='mySelect'  name='this_gui_wms_epsg'>";
		for ($i = 0; $i < count($epsg); $i++) {
			echo "<option value='" . $epsg[$i] . "' ";
			if ($epsg[$i] == $gui_wms_epsg[0]) {
				echo "selected";
			}
			echo ">" . $epsg[$i] . "</option>";
		}
		echo "</select>";
		echo "</td>";
		echo "</tr>";
	}
	#format
	echo "<tr>";
	echo "<td>Mapformat: </td><td>";
	echo "<select class='mySelect'  name='this_gui_wms_mapformat'>";
	for ($i = 0; $i < count($data_format); $i++) {
		if ($data_type[$i] == 'map') {
			echo "<option value='" . $data_format[$i] . "' ";
			if ($data_format[$i] == $gui_wms_mapformat[0]) {
				echo "selected";
			}
			echo ">" . $data_format[$i] . "</option>";
		}
	}
	echo "</select>";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td>Infoformat: </td><td>";
	echo "<select class='mySelect'  name='this_gui_wms_featureinfoformat'>";
	echo "<option value='text/html'>text/html</option>";
	for ($i = 0; $i < count($data_format); $i++) {
		if ($data_type[$i] == 'featureinfo') {
			echo "<option value='" . $data_format[$i] . "' ";
			if ($data_format[$i] == $gui_wms_featureinfoformat[0]) {
				echo "selected";
			}
			echo ">" . $data_format[$i] . "</option>";
		}
	}
	echo "</select>";
	echo "</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td>Exceptionformat: </td><td>";
	echo "<select class='mySelect' name='this_gui_wms_exceptionformat'>";
	for ($i = 0; $i < count($data_format); $i++) {
		if ($data_type[$i] == 'exception') {
			echo "<option value='" . $data_format[$i] . "' ";
			if ($data_format[$i] == $gui_wms_exceptionformat[0]) {
				echo "selected";
			}
			echo ">" . $data_format[$i] . "</option>";
		}
	}
	echo "</select>";
	echo "</td>";
	echo "</tr><br>";

	# visibility
	echo "<tr>";
	echo "<td>Visibility: </td><td>";
	echo "<select class='mySelect' name='this_gui_wms_visible'>";
	for ($i = 0; $i < 3; $i++) {
		echo "<option value='" . $i . "' ";
		if ($i == $gui_wms_visible[0]) {
			echo "selected";
		}
		echo ">";
		if ($i == '0') {
			echo "hidden";
		}
		if ($i == '1') {
			echo "visible";
		}
		echo "</option>";
	}
	echo "</select>";
	echo "</td>";
	echo "</tr>";

	# opacity
	echo "<tr>";
	echo "<td>Opacity: </td><td>";
	echo "<select class='mySelect' name='this_gui_wms_opacity'>";
	for ($i = 0; $i <= 100; $i += 10) {
		echo "<option value='" . $i . "' ";
		if ($i - $gui_wms_opacity[0] <= 5 && $i - $gui_wms_opacity[0] >= -4) {
			echo "selected";
		}
		echo ">";
		echo $i . "%";
		echo "</option>";
	}
	echo "</select>";
	echo "</td>";
	echo "</tr>";

	# sld support
	if ($wms_supportsld[0]) {
		echo "<tr>";
		echo "<td>SLD-URL: </td><td>";
		echo "<input type='text' class='myText' name='this_gui_wms_sldurl' id='this_gui_wms_sldurl' title='" . $gui_wms_sldurl[0] . "' value='" . $gui_wms_sldurl[0] . "'>";
		#echo "<a href='' onclick='return window.open(\"editor-start.php\");'><img src='sld_editor.png' border=0></a>";
		#$layer_names = implode(",", $layer_name);
		echo " <a href='javascript:showSld(\"" . $gui_wms_sldurl[0] . "\");'>SLD laden/anzeigen</a>";
		echo "</td>";
		echo "</tr>";
	} else {
		echo "<input type='hidden' value='' name='this_gui_wms_sldurl'>";
	}

	echo "</table><br>";

	echo "<table border='1' cellpadding='1'>";
	echo "<tr>";

	echo "<td>Nr.</td>";
	echo "<td>ID</td>";
	echo "<td>" . toImage('Parent') . "</td>";
	echo "<td>Name</td>";
	echo "<td>Title</td>";
	echo "<td>" . toImage('on/off') . "</td>";
	echo "<td>" . toImage('sel') . "</td>";
	echo "<td>" . toImage('sel_default') . "</td>";
	echo "<td>" . toImage('info') . "</td>";
	echo "<td>" . toImage('info_default') . "</td>";
	echo "<td>" . toImage('minScale 1:') . "</td>";
	echo "<td>" . toImage('maxScale 1:') . "</td>";
	echo "<td>" . toImage('Style') . "</td>";
	echo "<td>" . toImage('Prio') . "</td>";
	echo "<td>" . toImage('setWFS') . "</td>";
	if ($wms_supportsld[0]) {
		echo "<td>" . toImage('SLD') . "</td>";
	}
	echo "</tr>";

	#echo "<tr><td>Nr.</td><td>ID</td><td>Parent</td><td>Name</td><td>Title</td><td>on/off</td><td>sel</td><td>sel_default</td><td>info</td><td>info_default</td><td>minScale 1:</td><td>maxScale 1:</td><td>Prio</td><td>setWFS</td></tr>";
	echo "<tr><td></td><td></td><td></td><td></td><td></td><td>";
	echo "<input type='button' class='LButton' value='Sublayer\noff' onclick='setSubs()'>";
	echo "</td><td></td><td>";
	echo "<nobr><input type='button' class='button_on_off' value='off' onclick='setLayer(\"visible\",false)'>&nbsp;";
	echo "<input type='button' class='button_on_off' value='on' onclick='setLayer(\"visible\",true)'></nobr>";
	echo "</td><td></td><td>";
	echo "<nobr><input type='button' class='button_on_off' value='off' onclick='setLayer(\"querylayer\",false)'>&nbsp;";
	echo "<input type='button' class='button_on_off' value='on' onclick='setLayer(\"querylayer\",true)'></nobr>";
	echo "</td><td></td><td></td><td></td></tr>";

	for ($i = 0; $i < count($layer_id); $i++) {
		#layer_styles
		$sql_styles = "SELECT * FROM layer_style WHERE  fkey_layer_id = $1";
		$v = array (
			$layer_id[$i]
		);
		$t = array (
			'i'
		);
		$res_styles = db_prep_query($sql_styles, $v, $t);
		$cnt_styles = 0;
		$style = array ();
		while ($row = db_fetch_array($res_styles)) {
			$style[$cnt_styles] = $row["name"];
			$cnt_styles++;
		}

		echo "<tr align='center'>";
		echo "<td><input type='text' size='1' name='L_" . $layer_id[$i] . "___layer_nr' disabled value='" . $i . "'></td>";
		echo "<td style='background:lightgrey'><input type='text' size='2' name='L_" . $layer_id[$i] . "___layer_id' value='" . $layer_id[$i] . "' readonly></td>";
		echo "<td><input type='text' size='1' name='L_" . $layer_id[$i] . "___layer_parent' value='" . $layer_parent[$i] . "' readonly></td>";
		echo "<td style='background:lightgrey'><input type='text' size='7' value='" . $layer_name[$i] . "' readonly></td>";
		echo "<td><input type='text' size='12' name='L_" . $layer_id[$i] . "___gui_layer_title' value='" . $gui_layer_title[$i] . "' ></td>";

		echo "<td style='background:lightgrey'><input name='L_" . $layer_id[$i] . "___gui_layer_status' type='checkbox' ";
		if ($gui_layer_status[$i] == 1) {
			echo "checked";
		}
		echo "></td>";

		echo "<td><input name='L_" . $layer_id[$i] . "___gui_layer_selectable' type='checkbox' ";
		if ($gui_layer_selectable[$i] == 1) {
			echo "checked";
		}
		echo "></td>";

		echo "<td style='background:lightgrey'><input name='L_" . $layer_id[$i] . "___gui_layer_visible' type='checkbox' ";
		if ($gui_layer_visible[$i] == 1) {
			echo "checked";
		}
		echo "></td>";

		echo "<td><input name='L_" . $layer_id[$i] . "___gui_layer_queryable' type='checkbox' ";
		if ($gui_layer_queryable[$i] == 1) {
			echo "checked";
		}
		if ($layer_queryable[$i] == 0) {
			echo "disabled";
		}
		echo "></td>";

		echo "<td style='background:lightgrey'><input name='L_" . $layer_id[$i] . "___gui_layer_querylayer' type='checkbox' ";
		if ($gui_layer_querylayer[$i] == 1) {
			echo "checked";
		}
		if ($layer_queryable[$i] == 0) {
			echo "disabled";
		}
		echo "></td>";

		echo "<td><input name='L_" . $layer_id[$i] . "___gui_layer_minscale' type='text' size='5' value='" . $gui_layer_minscale[$i] . "'></td>";
		echo "<td style='background:lightgrey'><input name='L_" . $layer_id[$i] . "___gui_layer_maxscale' type='text' size='5' value='" . $gui_layer_maxscale[$i] . "'></td>";
		/**/
		echo "<td>\n";
		echo "<select class='select_short' name='L_" . $layer_id[$i] . "___gui_layer_style'>\n";
		echo "<option value=''";
		if (count($style) == 0) {
			echo "selected";
		}
		echo ">---</option>\n";
		for ($j = 0; $j < count($style); $j++) {
			echo "<option value='" . $style[$j] . "'";
			if ($style[$j] == $gui_layer_style[$i]) {
				echo "selected";
			}
			echo ">" . $style[$j] . "</option>\n";
		}
		echo "</select></td>\n";
		/**/
		echo "<td><select class='select_short' name='L_" . $layer_id[$i] . "___gui_layer_priority'>";
		for ($j = 0; $j < count($gui_layer_priority); $j++) {
			echo "<option value='" . $j . "'";
			if ($j == $gui_layer_priority[$i]) {
				echo "selected";
			}
			echo ">" . $j;
			echo "</option>";
		}
		echo "</select></td>\n";
		/* wfs configuration */
		echo "<td>";
		if ($i > 0) {
			echo "<input class='button_wfs' id='buttonLayerWfs_" . $layer_id[$i] . "' name='gui_layer_gaz' type='button' onclick='getWfsConfs(\"" . $guiList . "\"," . $wmsList . "," . $layer_id[$i] . ",\"" . $gui_layer_wfs_featuretype[$i] . "\",\"buttonLayerWfs_" . $layer_id[$i] . "\")' value='";
			if ($gui_layer_wfs_featuretype[$i] == "") {
				echo "setWFS";
			} else {
				echo "wfs " . $gui_layer_wfs_featuretype[$i];
			}
			echo "'>";
		}
		echo "</td>";
		if ($wms_supportsld[0]) {
			echo "<td>";
			if ($i > 0) {
				echo "<input class='button3' name='gui_layer_sld' type='button' onclick='window.open(\"../sld/sld_main.php?" . $urlParameters . "&sld_gui_id=" . $guiList . "&sld_wms_id=" . $wms_id[0] . "&sld_layer_name=" . $layer_name[$i] . "\");' value='";
				if ($sld_user_layer_id[$i] != "")
					echo "sld:" . $sld_user_layer_id[$i] . "(" . $use_sld[$i] . ")";
				else
					echo "SLD";
				echo "'>";
			}
			echo "</td>";
		}
		echo "</tr>\n";
		if ($i == 0) {
			echo "<tr><td colspan='16'><hr></td></tr>";
		}
	}
	echo "</table>\n";
	echo "<input type='hidden' name='this_gui' value='" . $guiList . "'>\n";
	echo "<input type='hidden' name='this_wms' value='" . $wmsList . "'>\n";
	echo "<input type='hidden' name='this_layer_count' value='" . $cnt_l . "'>\n";
	echo "<input type='hidden' name='update_content' value=''>\n";
	echo "</form>\n";
}
?>
</body>
</html>
