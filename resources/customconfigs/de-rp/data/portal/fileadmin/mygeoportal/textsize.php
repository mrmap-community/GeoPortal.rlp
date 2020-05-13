<?php
include_once("fileadmin/function/util.php");
include_once("fileadmin/function/function.php");

$db=new DB_MYSQL;
$sql="SELECT * FROM mygeoportal WHERE uid='".UserID()."'";
$db->query($sql);

$textsize["textsize1"]="selected=\"selected\"";
if($db->num_rows()) {
	$db->next_record();
	$textsize=$db->f("textsize");
}

switch($textsize) {
	case "textsize1":
		print "<link rel=\"stylesheet\" type=\"text/css\" href=\"fileadmin/design/css/textsize1.css\" media=\"screen\" />";
		break;
	case "textsize2":
		print "<link rel=\"stylesheet\" type=\"text/css\" href=\"fileadmin/design/css/textsize2.css\" media=\"screen\" />";
		break;
	case "textsize3":
		print "<link rel=\"stylesheet\" type=\"text/css\" href=\"fileadmin/design/css/textsize3.css\" media=\"screen\" />";
		break;
	}

?>