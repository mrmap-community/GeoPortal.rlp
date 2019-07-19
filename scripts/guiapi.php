<?php
//ini_set('error_reporting', 'E_ALL & ~ E_NOTICE');
require_once(dirname(__FILE__)."/../../core/globalSettings.php");
require_once(dirname(__FILE__)."/../../http/classes/class_mb_exception.php");
require_once(dirname(__FILE__)."/../../http/php/mb_getGUIs.php");

$con = db_connect(DBSERVER,OWNER,PW);
db_select_db(DB,$con);
$sql_list_guis = "SELECT DISTINCT gui.gui_id,gui.gui_name,gui.gui_description, gui_gui_category.fkey_gui_category_id FROM gui LEFT OUTER JOIN gui_gui_category ON (gui.gui_id=gui_gui_category.fkey_gui_id) WHERE gui_id IN (";
function db_quote($str) {
    return "'".$str."'";
}
$sql_list_guis .= implode(', ', array_map('db_quote', $_POST));
$sql_list_guis .= ") ";
$sql_list_guis .= " AND gui_public=1 AND ";
$sql_list_guis .= " (gui_gui_category.fkey_gui_category_id = 2)  "; 
$sql_list_guis .= "ORDER BY gui_name";
$e = new mb_exception($sql_list_guis);
$res_list_guis = db_query($sql_list_guis);


$guis = array();

while (  $row  =  db_fetch_array($res_list_guis) )  {
  $guis[] = $row['gui_name'];
}
echo json_encode($guis);
exit;


?>

