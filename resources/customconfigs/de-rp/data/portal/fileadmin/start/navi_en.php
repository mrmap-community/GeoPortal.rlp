<?php
$FILES=array();
$DIR="fileadmin/design/images/start/";

include_once("fileadmin/function/function.php");

if($dh=opendir($DIR)) {
	while( ($file=readdir($dh)) !== false) {
		if($file!="." && $file!="..") {
			$parts=split("\.",$file);
			if(count($parts)>1) {
				switch(strtolower($parts[count($parts)-1])) {
					case "jpg":
					case "jpeg":
					case "gif":
					case "png":
						$FILES[]=$file;
						break;
				}
			}
		}
	}
	closedir($dh);
}

if($_REQUEST["nr"]=="") {
	srand ((double)microtime()*1000000);
	$Nr=rand(0,count($FILES)-1);
} else {
	$Nr=$_REQUEST["nr"];
}

print "<img id=\"startimage\" src=\"fileadmin/design/images/start/".$FILES[$Nr]."\" width=\"450\" height=\"150\" alt=\"\" />";

switch ($_REQUEST["do"]) {
	case "search":
?>
	<a id="geodaten_suchen_help" href="en/service/help/search-data.html"><img id="geodaten_suchen" src="fileadmin/design/images/start_search_data_act.gif" width="150" height="99" alt="Search data" /></a>
	<a id="ergebnis_waehlen_help" href="en/service/help/choose-result.html"><img id="ergebnis_waehlen" src="fileadmin/design/images/start_choose_result.gif" width="150" height="99" alt="Choose result" /></a>
	<a id="karte_anzeigen_help" href="en/service/help/show-map.html"><img id="karte_anzeigen" src="fileadmin/design/images/start_show_map.gif" width="150" height="99" alt="Show map" /></a>
<?php
		break;
	case "list":
?>
	<a id="geodaten_suchen_help" href="en/service/help/search-data.html"><img id="geodaten_suchen" src="fileadmin/design/images/start_search_data.gif" width="150" height="99" alt="Search data" /></a>
	<a id="ergebnis_waehlen_help" href="en/service/help/choose-result.html"><img id="ergebnis_waehlen" src="fileadmin/design/images/start_choose_result_act.gif" width="150" height="99" alt="Choose result" /></a>
	<a id="karte_anzeigen_help" href="en/service/help/show-map.html"><img id="karte_anzeigen" src="fileadmin/design/images/start_show_map.gif" width="150" height="99" alt="Show map" /></a>
<?php
		break;
	case "map":
?>
	<a id="geodaten_suchen_help" href="en/service/help/search-data.html"><img id="geodaten_suchen" src="fileadmin/design/images/start_search_data.gif" width="150" height="99" alt="Search data" /></a>
	<a id="ergebnis_waehlen_help" href="en/service/help/choose-result.html"><img id="ergebnis_waehlen" src="fileadmin/design/images/start_choose_result.gif" width="150" height="99" alt="Choose result" /></a>
	<a id="karte_anzeigen_help" href="en/service/help/show-map.html"><img id="karte_anzeigen" src="fileadmin/design/images/start_show_map_act.gif" width="150" height="99" alt="Show map" /></a>
<?php
		break;
	default:
?>
	<a id="geodaten_suchen_help" href="en/service/help/search-data.html"><img id="geodaten_suchen" src="fileadmin/design/images/start_search_data.gif" width="150" height="99" alt="Search data" /></a>
	<a id="ergebnis_waehlen_help" href="en/service/help/choose-result.html"><img id="ergebnis_waehlen" src="fileadmin/design/images/start_choose_result.gif" width="150" height="99" alt="Choose result" /></a>
	<a id="karte_anzeigen_help" href="en/service/help/show-map.html"><img id="karte_anzeigen" src="fileadmin/design/images/start_show_map.gif" width="150" height="99" alt="Show map" /></a>
<?php
		break;
}
?>
</div>
</div>
