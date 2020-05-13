<?php
$GLOBALS['TSFE']->set_no_cache();
if($this->data['uid'] == 115) {

        require_once("/data/mapbender/core/globalSettings.php");#new version armin
	$con = db_connect(DBSERVER,OWNER,PW);
	db_select_db(DB,$con);
	
	
	require_once("/data/mapbender/http/php/mb_getGUIs.php");
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
	//$e = new mb_exception($sql_list_guis);
	$res_list_guis = db_query($sql_list_guis);
/*
	$sql_list_guis = "SELECT DISTINCT gui_id,gui_name,gui_description FROM gui WHERE gui_id IN (";
	function db_quote($str) {
	    return "'".$str."'";
	}
	$sql_list_guis .= implode(', ', array_map('db_quote', $_SESSION["mb_user_guis"]));
	$sql_list_guis .= ") ";
	$sql_list_guis .= " AND gui_public=1 ";
	$sql_list_guis .= "ORDER BY gui_name";
	$res_list_guis = db_query($sql_list_guis);
*/
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
	
	// URL zu wms in Session speichern
	if(isset($_REQUEST["wms1"])) {
		$_SESSION["command"]="addwms";
		$_SESSION["wms"]=array(str_replace("&amp;","&",urldecode($_REQUEST["wms1"])));
	}
	// EPSG der StartBBOX in SESSION schreiben
	if(isset($_REQUEST["mb_myBBOXEpsg"])) {
		$_SESSION["mb_myBBOXEpsg"]=$_REQUEST["mb_myBBOXEpsg"];
	}
	// StartBBOX in SESSION schreiben
	if(isset($_REQUEST["mb_myBBOX"])) {
		$_SESSION["mb_myBBOX"]=$_REQUEST["mb_myBBOX"];
	}
	// georssURL in Session speichern
	if(isset($_REQUEST["georssURL"])&&$_REQUEST["georssURL"]!="") {
		//$_SESSION["command"]="addwms";
		$_SESSION["georssURL"]=$_REQUEST["georssURL"];
	}
	
	if(db_numrows($res_list_guis)>0) {
		$content='|	
			<form  action="../mapbender/frames/index.php" method="get"  target="geop_map">
			<fieldset class="data">
			<select name="gui_id" id="gui_id" onchange="if(this.value!=\'\') submit()">
				<option value="">Anwendung auswählen</option>';
			while($row = db_fetch_array($res_list_guis)) {		   
				$sel=($row[0]==$_SESSION["mb_user_gui"])?'selected="selected"':'';
				$content.='
				<option value="'.$row[0].'" '.$sel.'>'.$row[1].'</option>';		
			}
		$content.='	
			</select>
			</fieldset>
			</form>';
	} else {
		
	  $content = '|';
	
	}
} else {

  $content = '|';

}
?>
