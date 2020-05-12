<?php
include_once(dirname(__FILE__)."/../../../../fileadmin/function/util.php");

$db=new DB_MYSQL;

$date=date("H:i t.m.Y");
$sql="INSERT INTO search_stats (searchtext, date, ip) VALUES ('".$_REQUEST["searchtext"]."', '".$date."', '".$_SERVER["REMOTE_ADDR"]."')";
 
$db->query($sql);
?>