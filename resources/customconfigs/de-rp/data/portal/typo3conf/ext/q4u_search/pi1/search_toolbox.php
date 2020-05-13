<?php
include_once("fileadmin/function/util.php");

$db=new DB_MYSQL;

if($_REQUEST["act"]=="delete") {
	$sql="DELETE FROM search WHERE uid=1 AND id='".$db->v($_REQUEST["deleteid"])."'";
	$db->query($sql);
}

if($_REQUEST["act"]=="save") {
	$sql="INSERT INTO search (uid, searchtext, datetime, name, lastsearch)
	           VALUES (1, '".$db->v($_REQUEST["searchtext"])."', ".time().", '".$db->v($_REQUEST["name"])."', 0)";
	$db->query($sql);
}

include("search_form.php");
?>