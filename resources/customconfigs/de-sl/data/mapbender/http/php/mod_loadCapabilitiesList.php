<?php
# $Id: mod_loadCapabilitiesList.php 8491 2012-09-18 15:11:04Z verenadiewald $
# http://www.mapbender.org/index.php/Administration
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

$e_id="loadWMSList";
require_once(dirname(__FILE__)."/../php/mb_validatePermission.php");
/*
 * @security_patch irv done
 */
//security_patch_log(__FILE__,__LINE__);

$guiList = $_POST["guiList"];
$wmsID = $_POST["wmsID"];
$guiID_ = $_POST["guiID_"];
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
<?php
echo '<meta http-equiv="Content-Type" content="text/html; charset='.CHARSET.'">';	
?>
<title>Load WMS from Catalog</title>
<?php
include '../include/dyn_css.php';
?>
<style type="text/css">
  	<!--
  	body{
      background-color: #ffffff;
  		font-family: Arial, Helvetica, sans-serif;
  		font-size : 12px;
  		color: #808080
  	}
  	.list_guis{
  		font-family: Arial, Helvetica, sans-serif;
  		font-size : 12px;
  		color: #808080;
  	}
.text1{
   font-family: Arial, Helvetica, sans-serif;
   font-size : 15px;
   position:absolute;
   top:190px;
}
.select1{
   position:absolute;
   top:210px;
   width:270px;
}
.text2{
   font-family: Arial, Helvetica, sans-serif;
   font-size : 15px;
   position:absolute;
   top:190px;
   left:300px;
}
.select2{
   position:absolute;
   top:210px;
   left:300px;
}
.getcapabilities{
   font-family: Arial, Helvetica, sans-serif;
   font-size : 15px;
   position:absolute;
   top:570px;
}

  	-->
</style>
<script language="JavaScript">
function validate(wert){
   if(wert == 'guiList'){
      var listIndex = document.form1.guiList.selectedIndex;
      if(listIndex<0){
		   alert("Please select a GUI.");
			return false;
      }
      else{
         var gui_id=document.form1.guiList.options[listIndex].value;
			document.form1.action='../php/mod_loadwms.php<?php echo SID;?>';
			document.form1.submit();
      }
   }
}
function load(){
      if(document.form1.guiList.selectedIndex<0){
		   alert("Please select a GUI.");
			return false;
      }
      var gui_ind = document.form1.guiList.selectedIndex;
      var ind = document.form1.wmsID.selectedIndex;
      var ind2 = document.form1.guiID_.selectedIndex;
			var indexWMSList = document.form1.wmsID.selectedIndex;
			var permission = true;

			var selectedWmsId = document.form1.wmsID.options[document.form1.wmsID.selectedIndex].value;
			for (i = 0; i < document.form1.wmsList.length; i++) {
						if (document.form1.wmsList.options[i].value == selectedWmsId){
							 permission = false;							 
							 alert ('The WMS (' + selectedWmsId + ') is already loaded in this application.');
							 break;
						}
			}			 
			
  			if (permission) { // only check if permission is not false 
        	var loadConfirmed = confirm("Load " + document.form1.wmsID.options[ind].text + " FROM " + document.form1.guiID_.options[ind2].value + " INTO "+document.form1.guiList.options[gui_ind].value+" ?");
          if(loadConfirmed){
             document.form1.submit();
          }
          else{
             document.form1.guiID_.selectedIndex = -1;
          }
			}	
			
}
</script>
</head>
<body>

<?php

require_once(dirname(__FILE__)."/../classes/class_administration.php");
$admin = new administration();
$ownguis = $admin->getGuisByOwner(Mapbender::session()->get("mb_user_id"),true);


###INSERT
if(isset($wmsID) && isset($guiID_)){
    
    //add wms: unlink the application cache file in cache directory
	#$admin->clearJsCacheFile($guiList);
	
	
	$sql_pos = "SELECT MAX(gui_wms_position) AS my_gui_wms_position FROM gui_wms WHERE fkey_gui_id = $1";
	$v = array($guiList);
	$t = array('s');
	$res_pos = db_prep_query($sql_pos,$v,$t);
	if(db_result($res_pos,0,"my_gui_wms_position") > -1){
		$gui_wms_position = db_result($res_pos,0,"my_gui_wms_position") + 1;
	}
	else{
		$gui_wms_position = 0;
	}

	$sql = "SELECT * FROM gui_wms WHERE fkey_gui_id = $1 AND fkey_wms_id = $2";
	$v = array($guiID_,$wmsID);
	$t = array('s','i');
	$res = db_prep_query($sql,$v,$t);
	$cnt = 0;
	while($row = db_fetch_array($res)){
		$sql_ins = "INSERT INTO gui_wms (fkey_gui_id,fkey_wms_id,gui_wms_position,gui_wms_mapformat,";
		$sql_ins .= "gui_wms_featureinfoformat,gui_wms_exceptionformat,gui_wms_epsg,gui_wms_visible,gui_wms_opacity,gui_wms_sldurl) ";
		$sql_ins .= "VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10)";
		$v = array($guiList,$wmsID,$gui_wms_position,$row["gui_wms_mapformat"],$row["gui_wms_featureinfoformat"],
		$row["gui_wms_exceptionformat"],$row["gui_wms_epsg"],$row["gui_wms_visible"],$row["gui_wms_opacity"],$row["gui_wms_sldurl"]);
		$t = array('s','i','i','s','s','s','s','i','i','s');
		db_prep_query($sql_ins,$v,$t);
		$cnt++;
	}

	$sql = "SELECT * FROM gui_layer WHERE fkey_gui_id = $1 AND gui_layer_wms_id = $2";
	$v = array($guiID_, $wmsID);
	$t = array("s", "i");
	$res = db_prep_query($sql, $v, $t);
	$cnt = 0;
	while($row = db_fetch_array($res)){
		$sql_ins = "INSERT INTO gui_layer (fkey_gui_id,fkey_layer_id,gui_layer_wms_id,gui_layer_status,gui_layer_selectable,";
		$sql_ins .= "gui_layer_visible,gui_layer_queryable,gui_layer_querylayer,gui_layer_minscale,gui_layer_maxscale,";
		$sql_ins .= "gui_layer_priority,gui_layer_style,gui_layer_wfs_featuretype,gui_layer_title) ";
		$sql_ins .= "VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14)";
		$v = array($guiList,$row["fkey_layer_id"],$wmsID,$row["gui_layer_status"],$row["gui_layer_selectable"],
		$row["gui_layer_visible"],$row["gui_layer_queryable"],$row["gui_layer_querylayer"],$row["gui_layer_minscale"],
		$row["gui_layer_maxscale"],$row["gui_layer_priority"],$row["gui_layer_style"],$row["gui_layer_wfs_featuretype"],$row["gui_layer_title"]);
		$t = array('s','i','i','i','i','i','i','i','i','i','i','s','s');
		db_prep_query($sql_ins,$v,$t);
		$cnt++;
	}
}

echo "<form name='form1' action='" . $self."' method='post'>";

echo "<table cellpadding='0' cellspacing='0' border='0'>";
echo "<tr>";
echo "<td>";
if (count($ownguis)>0){
	echo"GUI";
	echo"<br>";
	 
	$sql = "SELECT * FROM gui WHERE gui_id IN (";
	$v = $ownguis;
	$t = array();
	for ($i = 1; $i <= count($ownguis); $i++){
		if ($i > 1) { 
			$sql .= ",";
		}
		$sql .= "$".$i;
		array_push($t, "s");
	}
	$sql .= ") ORDER BY gui_name";	
	$res = db_prep_query($sql, $v, $t);
	$count=0;
	echo"<select size='8' name='guiList' style='width:200px' onClick='submit()'>";
	while($row = db_fetch_array($res)){
		$gui_name[$count]=$row["gui_name"];
		$gui_description[$count]=$row["gui_description"];
		$count++;
		echo "<option  value='".$row["gui_id"]."' ";
		if($guiList && $guiList == $row["gui_name"]){
			echo "selected";
		}
		echo ">".$row["gui_name"]."</option>";
	}
	
	$arrayGUIs = Mapbender::session()->get("mb_user_guis");
	echo count($arrayGUIs);
	echo "</select><br><br>";
	
	echo "</td>";
	echo "<td>";
	echo"WMS";
	echo"<br>";
	if(isset($guiList) && $guiList!=""){
		$sql = "SELECT DISTINCT wms_id, wms.wms_title,wms.wms_abstract, gui_wms_position FROM gui_wms ";
		$sql .= "JOIN gui ON gui_wms.fkey_gui_id = gui.gui_id JOIN wms ON gui_wms.fkey_wms_id=wms.wms_id ";
		$sql .= "AND gui_wms.fkey_gui_id=gui.gui_id WHERE gui.gui_name = $1 ORDER BY gui_wms_position";
		$v = array($guiList);
		$t = array('s');
		$res = db_prep_query($sql,$v,$t);	
		$count=0;
		echo"<select size='8' name='wmsList' style='width:200px'>";
	
		while($row = db_fetch_array($res)){
			if ($row["wms_title"]!=""){
				echo "<option title='".htmlentities($row["wms_abstract"],ENT_QUOTES,"UTF-8")."' value='".$row["wms_id"]."' ";
				echo ">".$row["wms_title"]."</option>";
			}
			$count++;
		}
		echo "</select><br><br>";
	}else{
		echo"<select size='8' name='wmsList' style='width:200px' on Click='submit()'>";
		echo "</select><br><br>";
	}
	echo "</td>";
	echo "<tr></table><br>";
	
	echo"<div class='text1'>Load WMS</div>";
	$sql = "SELECT DISTINCT wms.wms_id,wms.wms_title,wms.wms_abstract,wms.wms_owner FROM gui_wms JOIN wms ON ";
	$sql .= "wms.wms_id = gui_wms.fkey_wms_id WHERE gui_wms.fkey_gui_id IN(";
	$v = $arrayGUIs;
	$t = array();
	for ($i = 1; $i <= count($arrayGUIs); $i++){
		if ($i > 1) {
			$sql .= ",";
		}
		$sql .= "$" . $i;
		array_push($t, "s");
	}
	$sql .= ") ORDER BY wms.wms_title";
	$res = db_prep_query($sql, $v, $t);
	echo "<select class='select1' name='wmsID' size='20' onchange='submit()'>";
	$cnt = 0;
	while($row = db_fetch_array($res)){
		echo "<option title='".htmlentities($row["wms_abstract"],ENT_QUOTES,"UTF-8")."' value='".$row["wms_id"]."' ";
		if($row["wms_owner"] == Mapbender::session()->get("mb_user_id")){
			echo "style='color:green' ";	
		}
		else{
			echo "style='color:red' ";
		}
		if(isset($wmsID) && $wmsID == $row["wms_id"]){
			echo "selected";
			$wms_getcapabilities = $row["wms_getcapabilities"];
		}
		echo ">".$row["wms_title"]."</option>";
		$cnt++;
	}
	echo "</select>";
	
	if(isset($wmsID)){
		echo "<div class='text2'>FROM:</div>";
		$sql = "SELECT * from gui_wms WHERE fkey_wms_id = $1 ORDER BY fkey_gui_id";
		$v = array($wmsID);
		$t = array("s");
		$res = db_prep_query($sql, $v, $t);
		echo "<select class='select2' name='guiID_' size='20' onchange='load()'>";
		$cnt = 0;
		while($row = db_fetch_array($res)){
			echo "<option value='".$row["fkey_gui_id"]."' ";
			echo ">".$row["fkey_gui_id"]."</option>";
			$cnt++;
		}
	echo "</select>";
}
echo "</form>";
}else{
	echo "There are no guis available for this user. Please create a gui first.";
}
echo "<div class='getcapabilities'>" . $wms_getcapabilities . "</div>";
?>
</body>
</html>
