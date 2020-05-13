<?php
require_once dirname(__FILE__) . "/../../core/globalSettings.php";

$html = '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">';
$metadataStr .= '<head>' .
		'<title>Liste GeoWebServices</title>' .
		'<meta http-equiv="cache-control" content="no-cache">'.
		'<meta http-equiv="pragma" content="no-cache">'.
		'<meta http-equiv="expires" content="0">'.
		'<meta http-equiv="content-language" content="de" />'.
		'<meta http-equiv="content-style-type" content="text/css" />'.
		'<meta http-equiv="Content-Type" content="text/html; charset='.CHARSET.'">' ;
$html .= $metadataStr;
//define the javascripts to include
#$html .= '<link type="text/css" href="../extensions/DataTables-1.9.4/media/css/jquery.dataTables.css" rel="Stylesheet" />';
#$html .= '<link type="text/css" href="jquery.dataTables.geoportal.css" rel="Stylesheet" />';
$html .= '<link type="text/css" href="geowebservices.css" rel="Stylesheet" />';
$html .= '<script type="text/javascript" src="../extensions/jquery-ui-1.8.1.custom/js/jquery-1.4.2.min.js"></script>';
$html .= '<script type="text/javascript" src="urlencode.js"></script>';
$html .= '<script type="text/javascript" src="../extensions/jquery-ui-1.8.1.custom/js/jquery-ui-1.8.1.custom.min.js"></script>';
$html .= '<script type="text/javascript" src="../extensions/DataTables-1.9.4/media/js/jquery.dataTables.min.js"></script>';
$html .= '<script type="text/javascript" src="../extensions/jqjson.js"></script>';
$html .= '<script type="text/javascript">';

$html .= '$(document).ready(function() {';
$html .= '$("#letterTabs").tabs();';

$html .= '});';

$html .= '</script>';

$html .= '</head>';

$navi = true; //Tabs für Buchstabenbereiche ein-/ausschalten
$letter1 = $_GET['letter1'];
$letter2 = $_GET['letter2'];
if ($letter1 == '') {$letter1 = 'A'; $letter2 = 'C';} // Buchstaben am Anfang auf A-C setzen
$sql = "select distinct wms_title, wms_abstract, wms_title_upper, mb_group_name, layer_id from wms_list_view where wms_title is not NULL and substring(wms_title_upper from 1 for 1) BETWEEN '".$letter1."' AND '".$letter2."' order by wms_title_upper;";

$res = db_query($sql);

$html .= '<body>';

$url = "../geoportal/mod_showGeoWebServices.php?";

$html .= '<div id=\'center\'>';
//Navigation (Tabs mit Buchstabenbereichen)
if ($navi == true) {
	$html .= '<ul class=\'search-cat\'>';
	$html .= '<li ';
	if ($letter1=='A') $html .= 'class=\'active first\'';
	$html .= '><a href=\''.$url.'letter1=A&amp;letter2=C\'>A-C</a></li>';
	$html .= '<li ';
	if ($letter1=='D') $html .= 'class=\'active first\'';
	$html .= '><a href=\''.$url.'letter1=D&amp;letter2=F\'>D-F</a></li>';
	$html .= '<li ';
	if ($letter1=='G') $html .= 'class=\'active first\'';
	$html .= '><a href=\''.$url.'letter1=G&amp;letter2=I\'>G-I</a></li>';
	$html .= '<li ';
	if ($letter1=='J') $html .= 'class=\'active first\'';
	$html .= '><a href=\''.$url.'letter1=J&amp;letter2=L\'>J-L</a></li>';
	$html .= '<li ';
	if ($letter1=='M') $html .= 'class=\'active first\'';
	$html .= '><a href=\''.$url.'letter1=M&amp;letter2=O\'>M-O</a></li>';
	$html .= '<li ';
	if ($letter1=='P') $html .= 'class=\'active first\'';
	$html .= '><a href=\''.$url.'letter1=P&amp;letter2=R\'>P-R</a></li>';
	$html .= '<li ';
	if ($letter1=='S') $html .= 'class=\'active first\'';
	$html .= '><a href=\''.$url.'letter1=S&amp;letter2=U\'>S-U</a></li>';
	$html .= '<li ';
	if ($letter1=='V') $html .= 'class=\'active first\'';
	$html .= '><a href=\''.$url.'letter1=V&amp;letter2=Z\'>V-Z</a></li>';
	$html .= '<li ';
	if ($letter1=='1' and $letter2=='9') $html .= 'class=\'active first\'';
	$html .= '><a href=\''.$url.'letter1=1&amp;letter2=9\'>1-9</a></li>';
	$html .= '<li ';
	if ($letter1=='1' and $letter2=='Z') $html .= 'class=\'active first\'';
	$html .= '><a href=\''.$url.'letter1=1&amp;letter2=Z\'>alle</a></li>';
	$html .= '</ul>';
}
$html .= '</div>';

$html .= '<div class="clearer"></div>';
$html .= '<div class="glossar-container">';

// Ausgabe Dienste
while($row = db_fetch_array($res)) {
	$html .= '<dl><dt><a href="../php/mod_showMetadata.php?resource=layer&id='.$row['layer_id'].'"&languageCode=de" onclick="metadataWindow=window.open(this.href,\'width=400,height=250,left=50,top=50,scrollbars=yes\');metadataWindow.focus();return false;" target="_blank" title="Zeige Metadaten">'.$row['wms_title'].'</a></dt>';

	if ($row['mb_group_name'] != '') $html .= '<dd><em>Anbieter: '.$row['mb_group_name'].'</em></dd>';
	$html .= '<dd>'.$row['wms_abstract'].'</dd></dl>';
}
$html .= '</div></body>';

echo $html;
?>

