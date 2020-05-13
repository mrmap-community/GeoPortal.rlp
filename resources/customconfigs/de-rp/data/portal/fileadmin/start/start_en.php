<?php
GLOBAL $Nr;

$URL=$_SERVER["REDIRECT_URL"];

include("navi_en.php");
switch ($_REQUEST["do"]) {
	case "search":
		include("start_en_01.php");
		break;
	case "list":
		include("start_en_02.php");
		break;
	case "map":
		include("start_en_03.php");
		break;
	default:
		include("start_en_00.php");
		break;
}
?>