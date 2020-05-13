<div class="box">
<h2><span>Anwendungen</span></h2>
<?php
//ini_set('error_reporting', 'E_ALL & ~ E_NOTICE');
require_once(dirname("__FILE__")."/../mapbender/core/globalSettings.php");
require_once(dirname("__FILE__")."/../mapbender/http/classes/class_exception.php");
$con = db_connect(DBSERVER,OWNER,PW);
db_select_db(DB,$con);
require_once(dirname("__FILE__")."/../mapbender/http/php/mb_getGUIs.php");
$arrayGUIs = mb_getGUIs($_SESSION["mb_user_id"]);

$sql_list_guis = "SELECT DISTINCT gui.gui_id,gui.gui_name,gui.gui_description, gui_gui_category.fkey_gui_category_id FROM gui LEFT OUTER JOIN gui_gui_category ON (gui.gui_id=gui_gui_category.fkey_gui_id) WHERE gui_id IN (";
function db_quote($str) {
    return "'".$str."'";
}
$sql_list_guis .= implode(', ', array_map('db_quote', $_SESSION["mb_user_guis"]));
$sql_list_guis .= ") ";
$sql_list_guis .= " AND gui_public=1 AND ";
$sql_list_guis .= " (gui_gui_category.fkey_gui_category_id = 2)  "; 
$sql_list_guis .= "ORDER BY gui_name";
$e = new mb_exception($sql_list_guis);
$res_list_guis = db_query($sql_list_guis);

//Adresssuche in Session speichern
if($_REQUEST["geomuid"]!="" && $_REQUEST["geomid"]!="") {
	$unique=$_REQUEST["geomuid"];
	$id=$_REQUEST["geomid"];
	$adressfile = "/data/mapbender/http/tmp/".$unique."_geom.xml";
	if(file_exists($adressfile)) {
		$xmlFile = file_get_contents($adressfile);
		$search="<member id=\"".$id."\">";
		$pos=strpos($xmlFile,$search)+strlen($search);
		$xmldata=substr($xmlFile,$pos);
		$pos=strpos($xmldata,"</member>");
		$xmldata=substr($xmldata,0,$pos);
	}
	$_SESSION["GML"]=$xmldata;
}
?>
<form  action="../mapbender/frames/index.php" method="get"  target="geop_map">
<fieldset class="data">
<select name="mb_user_myGui" id="mb_user_myGui" onchange="if(this.value!='') submit()">
<option value="">Anwendungen auswählen</option>
<?php
	while($row = db_fetch_array($res_list_guis))
	{		   
		if($row[0]==$_SESSION["mb_user_gui"]){
		 $sel = "selected";
		}
		else
		{
		$sel = "";
		}
		
	echo "<option value='".$row[0]."' ".$sel.">".$row[1]."</option>\n";		
	}
?>
</select>
</fieldset>
</form>
</div>
