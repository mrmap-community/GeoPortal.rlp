<?php
GLOBAL $Nr;

$URL=$_SERVER["REDIRECT_URL"];

include("navi.php");
switch ($_REQUEST["do"]) {
	case "search":
		include("start_01.php");
		break;
	case "list":
		include("start_02.php");
		break;
	case "map":
		include("start_03.php");
		break;
	default:
		include("start_00.php");
		break;
}
?>