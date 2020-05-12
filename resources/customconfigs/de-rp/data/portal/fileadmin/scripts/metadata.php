<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
    "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">

<head>
	<title>GeoPortal Rheinland-Pfalz - Metadaten</title>
	<base href="<?php print "http://".$_SERVER["SERVER_NAME"]."/portal/"; ?>" />

	<meta http-equiv="content-type" content="text/html; charset=utf-8" />

	<meta name="description" content="Metadaten" xml:lang="de" />
	<meta name="keywords" content="Metadaten" xml:lang="de" />
	<meta name="author" content="Q4U GmbH" />
	<meta name="publisher" content="Q4U GmbH" />
	<meta name="copyright" content="Q4U GmbH" />

	<meta http-equiv="content-language" content="de" />
	<meta http-equiv="content-style-type" content="text/css" />

	<link rel="stylesheet" type="text/css" href="fileadmin/design/css/screen.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="fileadmin/design/css/print.css" media="print" />
</head>

<body id="top" class="popup">

<div id="header_graybottom"></div>
<div id="header_gray">
<a href="javascript:window.print()">Drucken <img src="fileadmin/design/images/icon_print.gif" width="14" height="14" alt="" /></a>
<a href="javascript:window.close()">Fenster schließen <img src="fileadmin/design/images/icon_close.gif" width="14" height="14" alt="" /></a>
</div>
<div id="header_redbottom"></div>
<div id="header_red"></div>

<div class="content">
<?php

$fields = array(
	"title" => "Titel",
	"abstract" => "Zusammenfassung",
	"categorie" => "Klassifikation / Themenbereich",
	"keyword" => "Schlüsselwörter",
	"dateStamp" => "Metadatendatum",
	"Contact_country" => "Land",
	"metadataStandard" => "Metadatenstandard",
	"metadataLanguage" => "Metadatensprache",
	"parentidentifier" => "Übergeordneter Datensatz",
	"hierachyLevel" => "Ebene",
	"HEADER1" => "Daten-Verteiler (Anbieter)",
	"MD_Distributor_organisationName" => "Organisation",
	"MD_Distributor_individualName" => "Ansprechpartner",
	"MD_Distributor_role" => "Funktion",
	"MD_Distributor_email" => "E-Mail",
	"MD_Distributor_phone" => "Telefon",
	"link" => "Internet-Adresse",
	"HEADER2" => "Datenbeschreibung",
	"geographicname" => "Geografischer Name",
	"referencesystem" => "Referenzsystem",
	"HEADER3" => "Ausdehnung",
	"westBoundLongitude" => "West Koordinate",
	"eastBoundLongitude" => "Ost Koordinate",
	"northBoundLongitude" => "Nord Koordinate",
	"southBoundLongitude" => "Süd Koordinate",
	"HEADER4" => "weitere Angaben",
	"MD_Distributor_distributorFormat" => "Datenformat",
	"useConstraints" => "Nutzungsbedingungen",
	"MD_Distributor_costs" => "Kosten",
	"language" => "Datensatzsprache",
	"onlineressource" => "Online-Quelle",
	"MD_Distributor_metadataID" => "Metadatenidenitifikator",
	"HEADER5" => "Metadatenanbietende Stelle",
	"Contact_organisationName" => "Organisation",
	"Contact_individualName" => "Ansprechpartner",
	"Contact_role" => "Funktion",
	"Contact_email" => "E-Mail",
	"Contact_phone" => "Telefon",
	"Contact_facsimile" => "Faxnummer",
);

include(dirname(__FILE__)."/../function/crypt.php");
if($_REQUEST["meta"]!="") {
	DecodeParameter($_REQUEST["meta"]);
}

?>
<table class="contenttable-0-wide">
<?php
$xml_begin=$xml_end=$xml_content=$xml_data=false;

$xml_id=$_REQUEST["XMLID"];
$xml_cat=$_REQUEST["XMLCAT"];
$file=$_REQUEST["XMLFILE"];
if(file_exists($file)) {
	$xmlFile = file($file);
	$parser = xml_parser_create();
	xml_set_element_handler($parser, "startElement", "endElement");
	xml_set_character_data_handler($parser, "cdata");

	foreach($xmlFile as $elem) {
		xml_parse($parser, $elem);
	}

	xml_parser_free($parser);
}
?>
</table>
</div>

<div id="footer_red"></div>

</body>

</html>
<?php
function startElement($parser, $element_name, $element_attribute) {
	global $data;
	global $xml_begin, $xml_end, $xml_content, $xml_data, $xml_typ;
	global $xml_id,$xml_cat,$xml_pre;

	$element_name = strtolower($element_name);

	switch($element_name) {
		case "result":
			$xml_begin=true;
			break;
		case "member":
			$data=array();
			$xml_content=true;
			break;
		case "md_distributor":
		case "contact":
			$xml_pre=$element_name."_";
			break;
		case "categorie":
		case "keyword":
			if($data[$element_name]!="") $data[$element_name].=", ";
			if($xml_content) {
				$xml_typ=$element_name;
				$xml_data=true;
			}
			break;
		case "ready":
			$xml_content=true;
			$xml_data=true;
			$xml_typ=$element_name;
			break;
		default:
			if($xml_content) {
				$xml_typ=$element_name;
				$xml_data=true;
			}
			break;
	}
}

function cdata($parser, $element_inhalt) {
	global $data;
	global $xml_begin, $xml_end, $xml_content, $xml_data, $xml_typ;
	global $xml_id,$xml_cat,$xml_pre;

	if($xml_begin && $xml_data) {
		$data[$xml_pre.$xml_typ].=$element_inhalt;
	}
}

function endElement($parser, $element_name) {
	global $data;
	global $xml_begin, $xml_end, $xml_content, $xml_data, $xml_typ;
	global $xml_id,$xml_cat,$xml_pre;
	global $fields;

	$element_name = strtolower($element_name);

	switch($element_name) {
		case "ready":
			$xml_content=false;
			$xml_data=false;
			if($data["ready"]=="true") $xml_end=true;
			break;
		case "result":
			$xml_begin=false;
			break;
		case "member":
			$head='';
			if($data["id"]==$xml_id) {
				foreach($fields as $key => $value) {
					if($data[strtolower($key)]!="") {
						$title=($value=="")?$key:$value;
						$text=nl2br(trim(urldecode($data[strtolower($key)])));
						if(strpos($text,"http://")===0 || strpos($text,"https://")===0) {
							$text='<a href="'.$text.'" target="_blank">'.$text.'</a>';
						}
						print $head.'
						  <tr>
						    <th>'.$title.'</th>
						    <td>'.$text.'</td>
						  </tr>';
						$head='';
					} elseif (substr($key,0,6)=="HEADER") {
						$head='
						  <tr>
						    <th colspan="2"><h2>'.$value.'</h2></th>
						  </tr>';
					}
				}
			}
			$xml_content=false;
			break;

		case "md_distributor":
		case "contact":
			$xml_pre="";
			break;
		default:
			$xml_data=false;
			break;
	}
}
?>