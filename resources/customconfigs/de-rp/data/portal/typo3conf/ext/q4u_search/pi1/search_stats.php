<?php
include_once(dirname(__FILE__)."/../../../../fileadmin/function/util.php");

$db=new DB_MYSQL;

$date=date("H:i t.m.Y");
$sql="INSERT INTO search_stats (searchtext, date, ip) VALUES ('".$db->v($_REQUEST["searchtext"])."', '".$date."', '".$db->v($_SERVER["REMOTE_ADDR"])."')";
//security warning CERT  15.04.2015
//$db->query($sql);
?>
