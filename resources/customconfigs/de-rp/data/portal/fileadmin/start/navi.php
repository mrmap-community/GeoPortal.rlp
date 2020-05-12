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
	<a id="geodaten_suchen_help" href="servicebereich/hilfe/daten-suchen.html"><img id="geodaten_suchen" src="fileadmin/design/images/start_daten_suchen_act.gif" width="150" height="99" alt="Geodaten Suchen" /></a>
	<a id="ergebnis_waehlen_help" href="servicebereich/hilfe/ergebnis-waehlen.html"><img id="ergebnis_waehlen" src="fileadmin/design/images/start_ergebnis_waehlen.gif" width="150" height="99" alt="Ergebnis wählen" /></a>
	<a id="karte_anzeigen_help" href="servicebereich/hilfe/karte-anzeigen.html"><img id="karte_anzeigen" src="fileadmin/design/images/start_karte_anzeigen.gif" width="150" height="99" alt="Karte anzeigen" /></a>
<?php
		break;
	case "list":
?>
	<a id="geodaten_suchen_help" href="servicebereich/hilfe/daten-suchen.html"><img id="geodaten_suchen" src="fileadmin/design/images/start_daten_suchen.gif" width="150" height="99" alt="Geodaten Suchen" /></a>
	<a id="ergebnis_waehlen_help" href="servicebereich/hilfe/ergebnis-waehlen.html"><img id="ergebnis_waehlen" src="fileadmin/design/images/start_ergebnis_waehlen_act.gif" width="150" height="99" alt="Ergebnis wählen" /></a>
	<a id="karte_anzeigen_help" href="servicebereich/hilfe/karte-anzeigen.html"><img id="karte_anzeigen" src="fileadmin/design/images/start_karte_anzeigen.gif" width="150" height="99" alt="Karte anzeigen" /></a>
<?php
		break;
	case "map":
?>
	<a id="geodaten_suchen_help" href="servicebereich/hilfe/daten-suchen.html"><img id="geodaten_suchen" src="fileadmin/design/images/start_daten_suchen.gif" width="150" height="99" alt="Geodaten Suchen" /></a>
	<a id="ergebnis_waehlen_help" href="servicebereich/hilfe/ergebnis-waehlen.html"><img id="ergebnis_waehlen" src="fileadmin/design/images/start_ergebnis_waehlen.gif" width="150" height="99" alt="Ergebnis wählen" /></a>
	<a id="karte_anzeigen_help" href="servicebereich/hilfe/karte-anzeigen.html"><img id="karte_anzeigen" src="fileadmin/design/images/start_karte_anzeigen_act.gif" width="150" height="99" alt="Karte anzeigen" /></a>
<?php
		break;
	default:
?>
	<a id="geodaten_suchen_help" href="servicebereich/hilfe/daten-suchen.html"><img id="geodaten_suchen" src="fileadmin/design/images/start_daten_suchen.gif" width="150" height="99" alt="Geodaten Suchen" /></a>
	<a id="ergebnis_waehlen_help" href="servicebereich/hilfe/ergebnis-waehlen.html"><img id="ergebnis_waehlen" src="fileadmin/design/images/start_ergebnis_waehlen.gif" width="150" height="99" alt="Ergebnis wählen" /></a>
	<a id="karte_anzeigen_help" href="servicebereich/hilfe/karte-anzeigen.html"><img id="karte_anzeigen" src="fileadmin/design/images/start_karte_anzeigen.gif" width="150" height="99" alt="Karte anzeigen" /></a>
<?php
		break;
}
?>
</div>
</div>
