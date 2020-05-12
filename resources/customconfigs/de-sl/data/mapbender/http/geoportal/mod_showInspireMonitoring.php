<?php
require_once dirname(__FILE__) . "/../../core/globalSettings.php";
$registratingDepartments = null;
$sessionLang = Mapbender::session()->get("mb_lang");
$withCounts = true;
if (defined("MAPBENDER_PATH") && MAPBENDER_PATH != '') { 
	$mapbenderUrl = MAPBENDER_PATH;
} else {
	$mapbenderUrl = "http://www.geoportal.saarland.de/mapbender";
}
if (isset($sessionLang) && ($sessionLang!='')) {
	$e = new mb_notice("mod_showMetadata.php: language found in session: ".$sessionLang);
	$language = $sessionLang;
	$langCode = explode("_", $language);
	$langCode = $langCode[0]; 
	$languageCode = $langCode;
}
if (isset($_REQUEST["registratingDepartments"]) & $_REQUEST["registratingDepartments"] != "") {
	//validate to csv integer list
	$testMatch = $_REQUEST["registratingDepartments"];
	$pattern = '/^[\d,]*$/';		
 	if (!preg_match($pattern,$testMatch)){ 
		echo 'Parameter <b>registratingDepartments</b> is not valid.<br/>'; 
		die(); 		
 	}
	$registratingDepartments = $testMatch;
	$testMatch = NULL;
}
if (isset($_REQUEST["withCounts"]) & $_REQUEST["withCounts"] != "") {
	//validate to csv integer list
	$testMatch = $_REQUEST["withCounts"];
	//$pattern = '/^[\d,]*$/';		
 	if (!($testMatch == "false" || $testMatch == "true")){ 
		echo 'Parameter <b>withCounts</b> is not valid.<br/>'; 
		die(); 		
 	}
	$withCounts = $testMatch;
	if ($withCounts == 'false') {
		$withCounts = false;
	}
	$testMatch = NULL;
}

//Array with translations:
switch ($languageCode) {
	case "de":
		$translation['Detail'] = 'Detail';
		$translation['Title'] = 'Titel';
		$translation['Identifier'] = 'Identifikator';
		$translation['Organization'] = 'Organisation';
		$translation['INSPIRE Themes'] = 'INSPIRE Themen';
		$translation['# of View Services'] = 'Zahl der Darstellungsdienste';
		$translation['# of Download Services'] = 'Zahl der Downloaddienste';
		$translation['wmslayergetmap'] = 'Download über WMS Aufrufe';
		$translation['wmslayerdataurl'] = 'Download über direkten Link';
		$translation['wfsrequest'] = 'Download über WFS 1.1.0';
		$translation['downloadlink'] = 'Download über Link aus Metadatensatz';
		$translation['inspireViewServices'] = 'INSPIRE Darstellungsdienste';
		$translation['inspireDownloadServices'] = 'INSPIRE Downloaddienste';
		break;
	case "en":		
		$translation['Detail'] = 'Detail';
		$translation['Title'] = 'Title';
		$translation['Identifier'] = 'Identifier';
		$translation['Organization'] = 'Organization';
		$translation['INSPIRE Themes'] = 'INSPIRE Themen';
		$translation['# of View Services'] = '# of View Services';
		$translation['# of Download Services'] = '# of Download Services';
		$translation['wmslayergetmap'] = 'Download über WMS Aufrufe';
		$translation['wmslayerdataurl'] = 'Download über direkten Link';
		$translation['downloadlink'] = 'Download über Link aus Metadatensatz';
		$translation['wfsrequest'] = 'Download über WFS 1.1.0';
		$translation['inspireViewServices'] = 'INSPIRE Darstellungsdienste';
		$translation['inspireDownloadServices'] = 'INSPIRE Downloaddienste';
		break;
	case "fr":		
		$translation['Detail'] = 'Detail';
		$translation['Title'] = 'Title';
		$translation['Identifier'] = 'Identifier';
		$translation['Organization'] = 'Organization';
		$translation['INSPIRE Themes'] = 'INSPIRE Themen';
		$translation['# of View Services'] = '# of View Services';
		$translation['# of Download Services'] = '# of Download Services';
		$translation['wmslayergetmap'] = 'Download über WMS Aufrufe';
		$translation['wmslayerdataurl'] = 'Download über direkten Link';
		$translation['downloadlink'] = 'Download über Link aus Metadatensatz';
		$translation['wfsrequest'] = 'Download über WFS 1.1.0';
		$translation['inspireViewServices'] = 'INSPIRE Darstellungsdienste';
		$translation['inspireDownloadServices'] = 'INSPIRE Downloaddienste';
		break;
	default: #to english
		$translation['Detail'] = 'Detail';
		$translation['Title'] = 'Title';
		$translation['Identifier'] = 'Identifier';
		$translation['Organization'] = 'Organization';
		$translation['INSPIRE Themes'] = 'INSPIRE Themes';
		$translation['# of View Services'] = '# of View Services';
		$translation['# of Download Services'] = '# of Download Services';
		$translation['wmslayergetmap'] = 'Download über WMS Aufrufe';
		$translation['wmslayerdataurl'] = 'Download über direkten Link';
		$translation['wfsrequest'] = 'Download über WFS 1.1.0';
		$translation['downloadlink'] = 'Download über Link aus Metadatensatz';
		$translation['inspireViewServices'] = 'INSPIRE Darstellungsdienste';
		$translation['inspireDownloadServices'] = 'INSPIRE Downloaddienste';
}
//Do html output
$html = '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$languageCode.'">';
$html .= '<body>';
$metadataStr .= '<head>' . 
		'<title>'.$translation['header'].'</title>' . 
		'<meta name="description" content="'.$translation['header'].'" xml:lang="'.$languageCode.'" />'.
		'<meta name="keywords" content="'.$translation['header'].'" xml:lang="'.$languageCode.'" />'	.	
		'<meta http-equiv="cache-control" content="no-cache">'.
		'<meta http-equiv="pragma" content="no-cache">'.
		'<meta http-equiv="expires" content="0">'.
		'<meta http-equiv="content-language" content="'.$languageCode.'" />'.
		'<meta http-equiv="content-style-type" content="text/css" />'.
		'<meta http-equiv="Content-Type" content="text/html; charset='.CHARSET.'">' ;		
$html .= $metadataStr;
//define the javascripts to include
$html .= '<link type="text/css" href="../extensions/DataTables-1.9.4/media/css/jquery.dataTables.css" rel="Stylesheet" />';
$html .= '<link type="text/css" href="jquery.dataTables.geoportal.css" rel="Stylesheet" />';
//$html .= '<link type="text/css" href="../extensions/jquery-ui-1.8.1.custom/css/custom-theme/jquery-ui-1.8.5.custom.css" rel="Stylesheet" />';
//$html .= '<link type="text/css" href="http://geoportal.saarland.de/fileadmin/design/geoportal.rlp.css" rel="Stylesheet" />';	
$html .= '<script type="text/javascript" src="../extensions/jquery-ui-1.8.1.custom/js/jquery-1.4.2.min.js"></script>';
$html .= '<script type="text/javascript" src="urlencode.js"></script>';
$html .= '<script type="text/javascript" src="../extensions/jquery-ui-1.8.1.custom/js/jquery-ui-1.8.1.custom.min.js"></script>';
$html .= '<script type="text/javascript" src="../extensions/DataTables-1.9.4/media/js/jquery.dataTables.min.js"></script>';
$html .= '<script type="text/javascript" src="../extensions/jqjson.js"></script>';
//$html .= '<script type="text/javascript" src="http://www.datatables.net/download/build/jquery.dataTables.min.js"></script>';
//TODO: some js for ui-dialog
$html .= '<script type="text/javascript">';

/* functions for calling detail via ajax*/
//call dls information
$html .= "function addDownloadServiceDetails(aData) {";
$html .= "$.getJSON('../php/mod_getDownloadOptions.php?id='+aData['uuid'], function(data) {";
$html .= "var uuid = aData['uuid'];";
//problem with uuid as name - exchange it
$html .= "var dataString = JSON.stringify(data);";
$html .= "dataString = dataString.replace(uuid, 'dummy');";
$html .= "data = JSON.parse(dataString);";
$html .= "$.each(data.dummy.option, function(index, value) {";
$html .= "switch(value.type)";
$html .= "{";
$html .= "case 'wmslayergetmap':";
$html .= "$('#dls').append('<li>".$translation['wmslayergetmap']."</li>');";
$html .= "$('#dls').append('<a href=\"../plugins/mb_downloadFeedClient.php?url='+urlencode('".$mapbenderUrl."/php/mod_inspireDownloadFeed.php?id='+uuid+'&type=SERVICE&generateFrom=wmslayer&layerid='+value.resourceId)+'\" target=\"_blank\"><img src=\"../img/osgeo_graphics/geosilk/raster_download.png\" title=\"".$translation['Title']."\"/></a><br>');";
$html .= "break;";
$html .= "case 'wmslayerdataurl':";
$html .= "$('#dls').append('<li>".$translation['wmslayerdataurl']."</li>');";
$html .= "$('#dls').append('<a href=\"../plugins/mb_downloadFeedClient.php?url='+urlencode('".$mapbenderUrl."/php/mod_inspireDownloadFeed.php?id='+uuid+'&type=SERVICE&generateFrom=dataurl&layerid='+value.resourceId)+'\" target=\"_blank\"><img src=\"../img/osgeo_graphics/geosilk/link_download.png\" title=\"".$translation['Title']."\"/></a><br>');";
$html .= "break;";
$html .= "case 'wfsrequest':";
$html .= "$('#dls').append('<li>".$translation['wfsrequest']."</li>');";
$html .= "$('#dls').append('<a href=\"../plugins/mb_downloadFeedClient.php?url='+urlencode('".$mapbenderUrl."/php/mod_inspireDownloadFeed.php?id='+uuid+'&type=SERVICE&generateFrom=wfs&wfsid='+value.serviceId)+'\" target=\"_blank\"><img src=\"../img/osgeo_graphics/geosilk/vector_download.png\" title=\"".$translation['Title']."\"/></a><br>');";
$html .= "break;";
$html .= "case 'downloadlink':";
$html .= "$('#dls').append('<li>".$translation['downloadlink']."</li>');";
$html .= "$('#dls').append('<a href=\"../plugins/mb_downloadFeedClient.php?url='+urlencode('".$mapbenderUrl."/php/mod_inspireDownloadFeed.php?id='+uuid+'&type=SERVICE&generateFrom=metadata')+'\" target=\"_blank\"><img src=\"../img/osgeo_graphics/geosilk/link_download.png\" title=\"".$translation['Title']."\"/></a><br>');";
$html .= "break;";
$html .= "default:";
$html .= "}";//end switch
$html .= "})";//end for each
$html .= "});";//end ajax call
$html .= "}";//end function
//call vs information
$html .= "function addViewServiceDetails(layerId,aData,i) {";
$html .= "$.getJSON('../php/mod_callMetadata.php?resourceIds='+aData['viewServices'][i].id+'&resultTarget=web', null, function(data) {";
//$html .= "var preview = '<br><img src=\"'+data.wms.srv[0].layer[0].previewURL+'\"/>';";
$html .= "$('#layerentry'+layerId).append('<br>'+data.wms.srv[0].title+' - '+data.wms.srv[0].layer[0].title+'</b>');";
$html .= "$('#layerentry'+layerId).append('<br><img src=\"'+data.wms.srv[0].layer[0].previewURL+'\"/>');";
//show availability information
$html .= "$('#layerentry'+layerId).append('<b>'+data.wms.srv[0].avail+'%</b>');";
$html .= "   });";
$html .= "}";
//function to pull information via ajax call and add it to detail table
$html .= "function fnAddDetails ( oTable, nTr ) {";
$html .= "var aData = oTable.fnGetData( nTr );";
$html .= "if (undefined != aData['viewServices']) {";
$html .= "for (var i = 0; i < aData['viewServices'].length; i++){";
$html .= "var layerId = aData['viewServices'][i].id;";
$html .= "$('#vs').append('<li id=\"layerentry'+layerId+'\" ><a href=\"../php/mod_showMetadata.php?languageCode=de&resource=layer&layout=tabs&id='+aData['viewServices'][i].id+'\" target=\"_blank\">'+layerId+'</a></li>');";
$html .= "addViewServiceDetails(layerId,aData,i);";
$html .= "}";
$html .= "}";
//$html .= "if (aData['numberDownloadServices'] > 0) {";
//get json data for download options from ajax request
$html .= "addDownloadServiceDetails(aData)";
//$html .= "}";
$html .= "}";
//function for open details of current row
$html .= "function fnFormatDetails ( oTable, nTr ) {";
$html .= "var aData = oTable.fnGetData( nTr );";
$html .= "var itemsViewService = [];";
$html .= "var itemsDownloadService = [];";
//generate table with detail 
$html .= "var sOut = '<table id=\"detailInformation\" cellpadding=\"5\" cellspacing=\"0\" border=\"0\" style=\"padding-left:50px;\">';";
//check if some view service is available
$html .= "if (undefined != aData['viewServices']) {";
$html .= "sOut += '<tr id=\"vs\"><td>".$translation['inspireViewServices']."</td><td></td></tr>';";
$html .= "}";
//$html .= "if (aData['numberDownloadServices'] > 0) {";
//get json data for download options from ajax request
$html .= "sOut += '<tr id=\"dls\"><td>".$translation['inspireDownloadServices']."</td><td></td></tr>';";
//$html .= "}";
$html .= "sOut += '</table>';";
$html .= "return sOut;";
$html .= "}";
$html .= "$(document).ready(function() {";
$html .= "    var oTable = $('#example').dataTable( {";
//$html .= "\"aoColumnDefs\": [";
//$html .= "{ \"bSortable\": false, \"aTargets\": [ 0 ] }";
//$html .= "],";
$html .= "\"bProcessing\": true,";
$html .= "\"bServerSide\": true,";
//$html .= "\"iSortCol_0\": 1,";
//internationalization
$html .= "\"oLanguage\": {";
$html .= "      \"sSearch\": \"Volltextsuche:\",";
$html .= "	\"sInfo\": \"Zeige _START_ bis _END_ von _TOTAL_ Einträgen\",";
$html .= "	\"sLengthMenu\": \"Zeige _MENU_ Einträge\",";
$html .= "	\"oPaginate\": {";
$html .= "		\"sFirst\": \"Erste\",";
$html .= "		\"sLast\": \"Letzte\",";
$html .= "		\"sNext\": \"Nächste\",";
$html .= "		\"sPrevious\": \"Vorige\"";
$html .= " 	   }";
$html .= "    },";
$html .= "\"sAjaxDataProp\": \"aaData\",";
if ($registratingDepartments != null) {
	$html .= "\"sAjaxSource\": \"mod_pullInspireMonitoring.php?registratingDepartments=".$registratingDepartments."\",";
} else {
	$html .= "\"sAjaxSource\": \"mod_pullInspireMonitoring.php\",";
}
/*$html .= "\"aoColumns\": [";
//$html .= "\"<img src='../img/add.png'>\",";
$html .= "{ \"mData\": \"detailImage\" , \"bSortable\": false },";		
$html .= "{ \"mData\": \"title\" },";
$html .= "{ \"mData\": \"uuid\" },";
$html .= "{ \"mData\": \"organization\" , \"bSortable\": false },";
if ($withCounts) {
	$html .= "{ \"mData\": \"inspireCategories\" , \"bSortable\": false },";
	$html .= "{ \"mData\": \"numberViewServices\" , \"bSortable\": false },";
	$html .= "{ \"mData\": \"numberDownloadServices\" , \"bSortable\": false }";
} else {
	$html .= "{ \"mData\": \"inspireCategories\" , \"bSortable\": false }";
}
$html .= "]";*/
$html .= "\"aoColumns\": [";
//$html .= "\"<img src='../img/add.png'>\",";
$html .= "{ \"mData\": \"detailImage\", \"bSortable\": false },";		
$html .= "{ \"mData\": \"title\", \"bSortable\": false },";
$html .= "{ \"mData\": \"uuid\" , \"bSortable\": false },";
$html .= "{ \"mData\": \"organization\", \"bSortable\": false },";
if ($withCounts) {
	$html .= "{ \"mData\": \"inspireCategories\", \"bSortable\": false },";
	$html .= "{ \"mData\": \"numberViewServices\", \"bSortable\": false },";
	$html .= "{ \"mData\": \"numberDownloadServices\", \"bSortable\": false }";
} else {
	$html .= "{ \"mData\": \"inspireCategories\", \"bSortable\": false }";
}
$html .= "]";
$html .= "});";
//$html .= "} );";
/* Add event listener for opening and closing details
 * Note that the indicator for showing which row is open is not controlled by DataTables,
 * rather it is done here
*/
$html .= "$('#expander').live('click', function () {";
//close all open nodes
$html .= "var nTr = $(this).parents('tr')[0];";
$html .= "if ( oTable.fnIsOpen(nTr) )";
$html .= "{";
/* This row is already open - close it */
$html .= "this.src = \"../img/gnome/stock_zoom-in.png\";";
//$html .= "oTable.fnDestroy();";
$html .= "oTable.fnClose( nTr );";
//close all open nodes!
/* Close any rows which are already open */
$html .= "}";
$html .= "else";
$html .= "{";
$html .= "$(\"td img\", oTable.fnGetNodes()).each(function () {";
$html .= "if (this.src.match('stock_zoom-out.png')) {";
$html .= "this.src = \"../img/gnome/stock_zoom-in.png\";";
$html .= "oTable.fnClose(this.parentNode.parentNode);";
$html .= "}";
$html .= "});";
/* Open this row */
$html .= "this.src = \"../img/gnome/stock_zoom-out.png\";";
$html .= "oTable.fnClose( nTr );";		
$html .= "oTable.fnOpen( nTr, fnFormatDetails(oTable, nTr), 'details' );";
$html .= "fnAddDetails(oTable, nTr);";
$html .= "}";
$html .= "});";
$html .= "});";
$html .= "$('#uuidref').live('click', function () {";
$html .= "var nTr = $(this).parents('tr')[0];";
//$html .= "alert($(this).text());";
$html .= "var url = \"../php/mod_dataISOMetadata.php?outputFormat=iso19139&id=\"+$(this).text();";
$html .="window.open(url, '_self');";
$html .= "});";
$html .= "</script>";
$html .= "</head>";
$html .= "<table id=\"example\">";
$html .= "    <thead>";
$html .= "        <tr>";
$html .= "            <th>".$translation['Detail']."</th>";
$html .= "            <th>".$translation['Title']."</th>";
$html .= "            <th>".$translation['Identifier']."</th>";
$html .= "            <th>".$translation['Organization']."</th>";
$html .= "            <th>".$translation['INSPIRE Themes']."</th>";
if ($withCounts) {
	$html .= "            <th>".$translation['# of View Services']."</th>";
	$html .= "            <th>".$translation['# of Download Services']."</th>";
}
$html .= "        </tr>";
$html .= "    </thead>";
$html .= "  <tbody>";
/*$html .= "       <tr>";
$html .= "        <td>Row 1 Data 1</td>";
$html .= "          <td>Row 1 Data 2</td>";
$html .= "          <td>etc</td>";
$html .= "          <td>etc</td>";
$html .= "          <td>etc</td>";
$html .= "      </tr>";
$html .=  "     <tr>";
$html .= "          <td>Row 2 Data 1</td>";
$html .= "          <td>Row 2 Data 2</td>";
$html .= "          <td>etc</td>";
$html .= "          <td>etc</td>";
$html .= "          <td>etc</td>";
$html .= "      </tr>";*/
$html .= "  </tbody>";
$html .= " </table>";
//$html.="eof";
$html .= "</body></html>";
echo $html;
?>
