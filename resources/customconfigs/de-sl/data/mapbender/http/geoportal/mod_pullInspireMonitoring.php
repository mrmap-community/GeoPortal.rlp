<?php
//Server-side script to pull INSPIRE monitoring information out of the mapbender database
// 
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2, or (at your option)
// any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
//
require_once(dirname(__FILE__)."/../../core/globalSettings.php");
require_once dirname(__FILE__)."/../classes/class_connector.php";
require_once dirname(__FILE__) . "/../classes/class_Uuid.php";

if (defined("INSPIRE_CUSTOM_CAT_ID") && INSPIRE_CUSTOM_CAT_ID != "") {
	$inspireCatId = INSPIRE_CUSTOM_CAT_ID;
} else {
	$inspireCatId = 11;
}
$outputFormat = 'json';
$lang = 'de';
$registratingDepartments = null;
$exportObjects = null;
if (defined("MAPBENDER_PATH") && MAPBENDER_PATH != '') { 
	$mapbenderUrl = MAPBENDER_PATH;
} else {
	$mapbenderUrl = "http://geoportal.saarland.de/mapbender";
}
//check http get parameters
if (isset($_REQUEST["outputFormat"]) & $_REQUEST["outputFormat"] != "") {
	$testMatch = $_REQUEST["outputFormat"];	
 	if (!($testMatch == 'json' or $testMatch == 'monitoring' or $testMatch == 'table')){ 
		echo 'outputFormat: is not valid.<br/>'; 
		die(); 		
 	}
	$outputFormat = $testMatch;
	$testMatch = NULL;
}
if (isset($_REQUEST["exportObjects"]) & $_REQUEST["exportObjects"] != "") {
	$testMatch = $_REQUEST["exportObjects"];	
 	if (!($testMatch == 'datasets' or $testMatch == 'services' or $testMatch == 'organizations')){ 
		echo 'outputFormat: is not valid.<br/>'; 
		die(); 		
 	}
	$exportObjects = $testMatch;
	$testMatch = NULL;
}
if (isset($_REQUEST["registratingDepartments"]) & $_REQUEST["registratingDepartments"] != "") {
	//validate to csv integer list
	$testMatch = $_REQUEST["registratingDepartments"];
	$pattern = '/^[\d,]*$/';		
 	if (!preg_match($pattern,$testMatch)){ 
		echo 'registratingDepartments: is not valid.<br/>'; 
		die(); 		
 	}
	$registratingDepartments = $testMatch;
	$testMatch = NULL;
}
if (isset($_REQUEST["language"]) & $_REQUEST["language"] != "") {
	$testMatch = $_REQUEST["language"];	
 	if (!($testMatch == 'de' or $testMatch == 'en' or $testMatch == 'fr')){ 
		echo 'language: is not valid.<br/>'; 
		die(); 		
 	}
	$lang = $testMatch;
	$testMatch = NULL;
}

//database tables GET interface for server side processing options
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* Easy set variables
*/
	
/* Array of database columns which should be read and sent back to DataTables. Use a space where
* you want to insert a non-database field (for example a counter or static image)
*/
$aColumns = array( 'title');
	
/* Indexed column (used for fast and accurate table cardinality) */
$sIndexColumn = "id";

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
* If you just want to use the basic configuration for DataTables with PHP server-side, there is
* no need to edit below this line
*/
	
/* 
* Paging
*/
if (isset( $_REQUEST['iDisplayLength']) && $_REQUEST['iDisplayLength'] != '-1') {
	$jsonLimit = (integer)pg_escape_string( $_REQUEST['iDisplayLength'] );
	
} else {
	$jsonLimit = 2000;
}	
	

$sLimit = "";
if ( isset( $_REQUEST['iDisplayStart'] ) && $_REQUEST['iDisplayLength'] != '-1' )
{
	$sLimit = "OFFSET ".pg_escape_string( $_REQUEST['iDisplayStart'] )." LIMIT ".
	pg_escape_string( $_REQUEST['iDisplayLength'] * 3);
}	
	
/*
* Ordering
*/
/*if ( isset( $_REQUEST['iSortCol_0'] ) )
{
	$sOrder = "ORDER BY  ";
	for ( $i=0 ; $i<intval( $_REQUEST['iSortingCols'] ) ; $i++ )
	{
		if ( $_REQUEST[ 'bSortable_'.intval($_REQUEST['iSortCol_'.$i]) ] == "true" )
		{
			$sOrder .= $aColumns[ intval( $_REQUEST['iSortCol_'.$i] ) ]."
				".pg_escape_string( $_REQUEST['sSortDir_'.$i] ) .", ";
		}
	}
		
	$sOrder = substr_replace( $sOrder, "", -2 );
	if ( $sOrder == "ORDER BY" )
	{
		$sOrder = "";
	}
}*/
	
	
/* 
* Filtering
* NOTE this does not match the built-in DataTables filtering which does it
* word by word on any field. It's possible to do here, but concerned about efficiency
* on very large tables, and MySQL's regex functionality is very limited
 */
$sWhere = "";
if ( $_REQUEST['sSearch'] != "" )
{
	//$e = new mb_exception($_REQUEST['sSearch']);
	$sWhere = "WHERE (";
	for ( $i=0 ; $i<count($aColumns) ; $i++ )
	{
		$sWhere .= $aColumns[$i]." LIKE '%".pg_escape_string( $_REQUEST['sSearch'] )."%' OR ";
	}
	$sWhere = substr_replace( $sWhere, "", -3 );
	$sWhere .= ')';
}
	
/* Individual column filtering */
for ( $i=0 ; $i<count($aColumns) ; $i++ )
{
	if ( $_REQUEST['bSearchable_'.$i] == "true" && $_REQUEST['sSearch_'.$i] != '' )
	{
		if ( $sWhere == "" )
		{
			$sWhere = "WHERE ";
		}
		else
		{
			$sWhere .= " AND ";
		}
		$sWhere .= $aColumns[$i]." LIKE '%".pg_escape_string($_REQUEST['sSearch_'.$i])."%' ";
	}
}
	
	
/*
* SQL queries
* Get data to display
*/

	/*$sWhere
	$sOrder
	$sLimit*/
	
/* Data set length after filtering */
/*$sQuery = "
	SELECT FOUND_ROWS()
";
$rResultFilterTotal = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
$aResultFilterTotal = mysql_fetch_array($rResultFilterTotal);
$iFilteredTotal = $aResultFilterTotal[0];*/
	
/* Total data set length */
/*$sQuery = "
	SELECT COUNT(".$sIndexColumn.")
	FROM   $sTable
";
$rResultTotal = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
$aResultTotal = mysql_fetch_array($rResultTotal);
$iTotal = $aResultTotal[0];*/
	
	
/*
* Output
*/
/*$output = array(
	"sEcho" => intval($_REQUEST['sEcho']),
	"iTotalRecords" => $iTotal,
	"iTotalDisplayRecords" => $iFilteredTotal,
	"aaData" => array()
);*/

//*******************************************************************
//get inspire category information from db
$sql = <<<SQL
	select inspire_category_id, inspire_category_key, inspire_category_code_$lang from inspire_category
SQL;
$result = db_query($sql);
$inspireCategories = array();
while ($row = db_fetch_array($result)) {
	$inspireCategories['key'][$row['inspire_category_id']] = $row['inspire_category_key'];
	$inspireCategories['title'][$row['inspire_category_id']] = $row['inspire_category_code_'.$lang];
}
if ($outputFormat == 'monitoring' || $outputFormat == 'table') {
} else {
//define sql to do a count of inspire relevant data (metadata)
$sqlCount = <<<SQL

select count(metadata_id) from (select distinct (uuid), metadata_id, title,  uuid from mb_metadata where metadata_id in (select distinct (metadata_id) from (select fkey_metadata_id as metadata_id from ows_relation_metadata inner join (select fkey_layer_id from layer_custom_category where fkey_custom_category_id = $inspireCatId ) as foo on foo.fkey_layer_id = ows_relation_metadata.fkey_layer_id
union
select fkey_metadata_id as metadata_id from ows_relation_metadata inner join (select fkey_featuretype_id from wfs_featuretype_custom_category where fkey_custom_category_id = $inspireCatId ) as foo on foo.fkey_featuretype_id = ows_relation_metadata.fkey_featuretype_id
union
select fkey_metadata_id as metadata_id from  mb_metadata_custom_category where fkey_custom_category_id = $inspireCatId ) as foo )) as foo2 $sWhere
SQL;

$resultCount = db_query($sqlCount);
$rowCount = db_fetch_array($resultCount);
$resultCount = $rowCount['count'];

$iTotal = $resultCount;
}
//$e = new mb_exception($rowCount['count']);
//all inspire relevant data


//Define sql to select relevant information out of the registry.
//The direction is from the classified service layer/featuretype information to the coupled metadata.
//The classifications of the layers are used to decide if the resource is in the outgoing table.
if ($outputFormat == 'monitoring' || $outputFormat == 'table') {
$sql = <<<SQL

select metadata_layer.title, metadata_layer.uuid, resource_id, resource_uuid, resource_type, inspire_actual_coverage, inspire_whole_area, service_id, inspire_download, wms_owner as service_owner, wms.inspire_annual_requests, fkey_mb_group_id as service_group, wms_title as service_title, f_collect_inspire_cat_layer(resource_id) as inspire_cat from (select title,uuid ,resource_uuid, layer_id as resource_id, 'layer' as resource_type, fkey_wms_id as service_id, inspire_download, inspire_actual_coverage, inspire_whole_area from (select layer.layer_id, layer.fkey_wms_id, layer.uuid as resource_uuid, layer.inspire_download, layer_custom_category.fkey_custom_category_id from layer inner join layer_custom_category on layer.layer_id = layer_custom_category.fkey_layer_id where layer_custom_category.fkey_custom_category_id = $inspireCatId AND layer_searchable = 1 ORDER BY layer_id) as layer_inspire inner join (select metadata_id, uuid, title, inspire_actual_coverage, inspire_whole_area, fkey_layer_id from mb_metadata inner join ows_relation_metadata on ows_relation_metadata.fkey_metadata_id = mb_metadata.metadata_id) as metadata_relation on metadata_relation.fkey_layer_id = layer_inspire.layer_id) as metadata_layer INNER JOIN wms ON metadata_layer.service_id = wms_id WHERE NOT(wms.wms_network_access = 1)


SQL;
/*
union 

select metadata_featuretype.title, metadata_featuretype.uuid, resource_id, resource_uuid, resource_type,inspire_actual_coverage, inspire_whole_area, service_id, inspire_download, wfs_owner as service_owner, fkey_mb_group_id as service_group, wfs_title as service_title, f_collect_inspire_cat_wfs_featuretype(resource_id) as inspire_cat from (select title, uuid, resource_uuid, featuretype_id as resource_id, 'wfs_featuretype' as resource_type, fkey_wfs_id as service_id, inspire_download, inspire_actual_coverage, inspire_whole_area from (select wfs_featuretype.featuretype_id ,wfs_featuretype.fkey_wfs_id,  wfs_featuretype.inspire_download, wfs_featuretype.uuid as resource_uuid from wfs_featuretype) as featuretype_inspire inner join (select metadata_id, uuid, title, fkey_featuretype_id, inspire_actual_coverage, inspire_whole_area from mb_metadata inner join ows_relation_metadata on ows_relation_metadata.fkey_metadata_id = mb_metadata.metadata_id) as metadata_relation on metadata_relation.fkey_featuretype_id = featuretype_inspire.featuretype_id) as metadata_featuretype INNER JOIN wfs ON metadata_featuretype.service_id = wfs_id 
order by uuid

*/
} else {


$sql = <<<SQL
select distinct (uuid), * from (
select metadata_layer.title, wms.uuid as service_uuid, wms_title || ':' || layer_title as inspire_service_title, metadata_layer.uuid, metadata_layer.datasetid, metadata_layer.datasetid_codespace, resource_id, resource_uuid, resource_type, service_id, inspire_download, wms_owner as service_owner, fkey_mb_group_id as service_group, wms_title as service_title, f_collect_inspire_cat_layer(resource_id) as inspire_cat from (select title, uuid, datasetid, datasetid_codespace, layer_id as resource_id, 'layer' as resource_type, fkey_wms_id as service_id, inspire_download, layer_title, resource_uuid from (select layer.layer_id, layer.layer_title, layer.fkey_wms_id, layer.uuid as resource_uuid, layer.inspire_download, layer_custom_category.fkey_custom_category_id from layer inner join layer_custom_category on layer.layer_id = layer_custom_category.fkey_layer_id where layer_custom_category.fkey_custom_category_id = $inspireCatId AND layer_searchable = 1 ORDER BY layer_id) as layer_inspire inner join (select metadata_id, datasetid, datasetid_codespace, uuid, title, fkey_layer_id from mb_metadata inner join ows_relation_metadata on ows_relation_metadata.fkey_metadata_id = mb_metadata.metadata_id) as metadata_relation on metadata_relation.fkey_layer_id = layer_inspire.layer_id) as metadata_layer INNER JOIN wms ON metadata_layer.service_id = wms_id   

union 

select metadata_featuretype.title, wfs.uuid as service_uuid, wfs_title || ':' || featuretype_title as inspire_service_title, metadata_featuretype.uuid, metadata_featuretype.datasetid, metadata_featuretype.datasetid_codespace, resource_id, resource_uuid, resource_type, service_id, inspire_download, wfs_owner as service_owner, fkey_mb_group_id as service_group, wfs_title as service_title, f_collect_inspire_cat_wfs_featuretype(resource_id) as inspire_cat from (select title, uuid, datasetid, datasetid_codespace, featuretype_id as resource_id, 'wfs_featuretype' as resource_type, resource_uuid, fkey_wfs_id as service_id, inspire_download, featuretype_title from (select wfs_featuretype.featuretype_id , wfs_featuretype.featuretype_title, wfs_featuretype.fkey_wfs_id,  wfs_featuretype.inspire_download, wfs_featuretype.uuid as resource_uuid from wfs_featuretype inner join wfs_featuretype_custom_category on wfs_featuretype.featuretype_id = wfs_featuretype_custom_category.fkey_featuretype_id where wfs_featuretype_custom_category.fkey_custom_category_id = $inspireCatId AND featuretype_searchable = 1 ORDER BY featuretype_id) as featuretype_inspire inner join (select metadata_id, datasetid, datasetid_codespace, uuid, title, fkey_featuretype_id from mb_metadata inner join ows_relation_metadata on ows_relation_metadata.fkey_metadata_id = mb_metadata.metadata_id) as metadata_relation on metadata_relation.fkey_featuretype_id = featuretype_inspire.featuretype_id) as metadata_featuretype INNER JOIN wfs ON metadata_featuretype.service_id = wfs_id




) as foo $sWhere $sOrder $sLimit

SQL;
/*
union

select title, null as service_uuid, null as inspire_service_title, uuid, datasetid, datasetid_codespace, metadata_id as resource_id, null as resource_uuid, null as resource_type, null as service_id, null as inspire_download, fkey_mb_user_id as service_owner, null as service_group, null as service_title, f_collect_inspire_cat_dataset(metadata_id) as inspire_cat from mb_metadata inner join mb_metadata_custom_category on mb_metadata.metadata_id = mb_metadata_custom_category.fkey_metadata_id where mb_metadata_custom_category.fkey_custom_category_id = $inspireCatId
*/
}
$startTime = microtime();
//get all service / owner / fkey_group information for the list of services
$result = db_query($sql);
//initialize result array
$sqlTable = array();
while ($row = db_fetch_array($result)) {
	$sqlTable['uuid'][] = $row['uuid'];
	$sqlTable['title'][] = $row['title'];
	$sqlTable['service_id'][] = $row['service_id'];
	$sqlTable['service_uuid'][] = $row['service_uuid'];
	//$sqlTable['service_uuid'][] = 	"<a href='../php/mod_iso19139ToHtml.php?url=".urlencode($mapbenderUrl."/php/mod_dataISOMetadata.php?outputFormat=iso19139&id=".$row["service_uuid"])."'>".$row["service_uuid"]."</a>";
	$sqlTable['service_title'][] = $row['inspire_service_title'];
	$sqlTable['resource_type'][] = $row['resource_type'];
	$sqlTable['resource_id'][] = $row['resource_id'];
	$sqlTable['resource_uuid'][] = $row['resource_uuid'];
	$sqlTable['service_group'][] = $row['service_group'];
	$sqlTable['service_owner'][] = $row['service_owner'];
	$sqlTable['inspire_actual_coverage'][] = $row['inspire_actual_coverage'];
	$sqlTable['inspire_whole_area'][] = $row['inspire_whole_area'];
	$sqlTable['inspire_annual_requests'][] = $row['inspire_annual_requests']; 
	$sqlTable['inspire_cat'][] = replaceCategories($row['inspire_cat'], $inspireCategories);
	$sqlTable['inspire_cat_monitoring'][] = replaceCategoriesList($row['inspire_cat']);
	$sqlTable['inspire_download'][] = $row['inspire_download'];
	//extract datasetid
	if (isset($row['datasetid']) && $row['datasetid'] != '') {
		if (isset($row['datasetid_codespace']) && $row['datasetid_codespace'] != '') {
			$sqlTable['datasetid'][] = $row['datasetid_codespace']."#".$row['datasetid'];
		} else {
			$sqlTable['datasetid'][] = $row['datasetid'];
		}
		//$sqlTable['datasetid'][] = "test";
	} else {
		$sqlTable['datasetid'][] = METADATA_DEFAULT_CODESPACE.'#'.$row['uuid'];
	}	
}
$groupOwnerArray = array();
$groupOwnerArray[0] = $sqlTable['service_group'];
$groupOwnerArray[1] = $sqlTable['service_owner'];
//get orga information
$groupOwnerArray = getOrganizationInfoForServices($groupOwnerArray);
//exchange category ids with titles and keys
//2 - user_id
//3 - metadatapointofcontactorgname
//multisort?
//push information from groupOwnerArray to sqlTable
$sqlTable['organization'] = $groupOwnerArray[3];
//$sqlTable['userId'] = $groupOwnerArray[2];
$sqlTable['orgaId'] = $groupOwnerArray[4];
$sqlTable['orgaEmail'] = $groupOwnerArray[5];
$sqlTable['adminCode'] = $groupOwnerArray[6];
//TODO: check sorting
//$wfsMatrix = $this->flipDiagonally($wfsMatrix); //- see class_metadata_new.php
//array_multisort($sqlTable['uuid'], SORT_STRING);
//array_multisort($sqlTable['uuid'], SORT_STRING, $sqlTable['resource_type'], SORT_STRING);
//debug output option:

switch ($outputFormat) {
	case 'table':
	for ($i=0; $i < count($sqlTable['uuid']); $i++){
		$rowString = "";
		$rowString .= $sqlTable['datasetid'][$i]."|".$sqlTable['title'][$i]."|".$sqlTable['organization'][$i]."|".$sqlTable['orgaEmail'][$i]."|"."|"."|";
		//metadata exists
		$rowString .= "[X]|";
		//uuid metadata set
		$rowString .= "".$sqlTable['uuid'][$i]."|";
		//conformancy of metadata with regulation
		$rowString .= "[X]|";
		//availability of metadata via csw
		$rowString .= "[X]|";
		//existence of view service always
		$rowString .= "[X]|";
		//id of viewservice
		//use layerid and some other things - problem there are more than one result for each metadata entry!
		$rowString .= "".$sqlTable['resource_id'][$i]."|";
		//existence of download service
		$rowString .= "[X]|";
		//id of downloadservice
		//use downloadservice id - use webservice
		$rowString .= "".$sqlTable['resource_id'][$i]."|";
		//check if data is harmonized - no
		$rowString .= "|";
		//add list with inspire themes
		
		//comment
		$rowString .= "|";
		$rowString .= "<br>";
		echo $rowString;
		
	}
	die();
	break;
	case 'monitoring':
		$metadataIndex = -1;
		$serviceIndex = 0;	
		$orgaIndex = 0;
		$alreadyBuildDls = array();
		$alreadyReadOrgas = array();
		$alreadyBuildVs = array();
		$currentUuid = "";
		$inspireMonitoring = array(
			"datasets" => array(),
			"services" => array(),
			"organizations" => array()
		);
		//loop over all found metadata uuids
		for ($i=0; $i < count($sqlTable['uuid']); $i++){
			//filter for orga_id
			//generate entry only if orga_id is the same as expected
			if (!$registratingDepartments || ($registratingDepartments != null && in_array($groupOwnerArray[4][$i],explode(',',$registratingDepartments)))) {
				if ($sqlTable['uuid'][$i] != $currentUuid) {
					//new metadataset identified - initialize it
					$currentUuid = $sqlTable['uuid'][$i];
					//logit($currentUuid);
					
					$metadataIndex++;
					//$e = new mb_exception("index: ".$metadataIndex);
					$inspireMonitoring['datasets'][$metadataIndex]->datasetid = $sqlTable['datasetid'][$i];
					$inspireMonitoring['datasets'][$metadataIndex]->title = $sqlTable['title'][$i];
					//logit($inspireMonitoring['datasets'][$metadataIndex]->title);
					$inspireMonitoring['datasets'][$metadataIndex]->organization = $sqlTable['organization'][$i];
					$inspireMonitoring['datasets'][$metadataIndex]->orgaEmail = $sqlTable['orgaEmail'][$i];
					$inspireMonitoring['datasets'][$metadataIndex]->relevantArea = $sqlTable['inspire_whole_area'][$i];
					$inspireMonitoring['datasets'][$metadataIndex]->actualArea = $sqlTable['inspire_actual_coverage'][$i];
					$inspireMonitoring['datasets'][$metadataIndex]->metadataExists = "[X]";
					$inspireMonitoring['datasets'][$metadataIndex]->uuid = $sqlTable['uuid'][$i];
					//metadata conform
					$inspireMonitoring['datasets'][$metadataIndex]->metadataConform = "[X]";
					//metadata available
					$inspireMonitoring['datasets'][$metadataIndex]->metadataAvailable = "[X]";
					$inspireMonitoring['datasets'][$metadataIndex]->viewServiceAvailable = "";
					$inspireMonitoring['datasets'][$metadataIndex]->viewServiceId = "";
					$inspireMonitoring['datasets'][$metadataIndex]->downloadServiceAvailable = "";
					$inspireMonitoring['datasets'][$metadataIndex]->downloadServiceId = "";
					$inspireMonitoring['datasets'][$metadataIndex]->datasetConform = "";
					$insCat = '';
					$inspireMonitoring['datasets'][$metadataIndex]->inspireCategories = '';
					$inspireMonitoring['datasets'][$metadataIndex]->numberViewServices = 0;
					$inspireMonitoring['datasets'][$metadataIndex]->numberDownloadServices = 0;
					$inspireMonitoring['datasets'][$metadataIndex]->report = "";
					$inspireMonitoring['datasets'][$metadataIndex]->comment = "";
				}
				if ($metadataIndex > -1) { //prohibit indexes which are not real - otherwise the json array will become an object
					//build view service
					//$e = new mb_exception("resource type: ".$sqlTable['resource_type'][$i]." - uuid: ".$sqlTable['uuid'][$i]);
					//build view services - but only once for each view service uuid (layer uuid)
					if ($sqlTable['resource_type'][$i] == "layer") {
						$inspireMonitoring['datasets'][$metadataIndex]->viewServiceId = $sqlTable['resource_uuid'][$i];
						$inspireMonitoring['datasets'][$metadataIndex]->viewServiceId = $sqlTable['resource_uuid'][$i];
						//get inspire categories					
						$catString = $sqlTable['inspire_cat_monitoring'][$i];
						$insCat .= $sqlTable['inspire_cat_monitoring'][$i];
						if ($insCat != '') {
							$insCat .= ",";
						}
						$inspireMonitoring['datasets'][$metadataIndex]->numberViewServices++;
						if (!in_array($sqlTable['resource_uuid'][$i],$alreadyBuildVs)) {
							//addview view service
							$inspireMonitoring['services'][$serviceIndex]->id = $sqlTable['resource_uuid'][$i];
							//name
							$inspireMonitoring['services'][$serviceIndex]->name = "Darstellungsdienst für ".$sqlTable['title'][$i];
							//typ
							$inspireMonitoring['services'][$serviceIndex]->type = "Darstellungsdienst";
							//url
							$inspireMonitoring['services'][$serviceIndex]->url = MAPBENDER_PATH."/php/wms.php?layer_id=".$sqlTable['resource_id'][$i]."&REQUEST=GetCapabilities&VERSION=1.1.1&SERVICE=WMS&INSPIRE=1";
							//orga
							$inspireMonitoring['services'][$serviceIndex]->organization = $sqlTable['organization'][$i];
							//orga email
							$inspireMonitoring['services'][$serviceIndex]->orgaEmail = $sqlTable['orgaEmail'][$i];
							//metadata exists
							$inspireMonitoring['services'][$serviceIndex]->metadataExists = "[X]";
							//service uuid - view/download difference
							$inspireMonitoring['services'][$serviceIndex]->serviceUuid = $sqlTable['resource_uuid'][$i];
							//metadata conform
							$inspireMonitoring['services'][$serviceIndex]->metadataConform = "[X]";
							//metadata available
							$inspireMonitoring['services'][$serviceIndex]->metadataAvailable = "[X]";
							//service conform
							$inspireMonitoring['services'][$serviceIndex]->serviceConform = "[X]";
							//requests per day
							$inspireMonitoring['services'][$serviceIndex]->requestsPerDay = ceil((integer)$sqlTable['inspire_annual_requests'][$i] / 365);
							//comment
							$inspireMonitoring['services'][$serviceIndex]->report = "";
							//report
							$inspireMonitoring['services'][$serviceIndex]->comment = "";
							//increment amount of view services
					
							/*if ($sqlTable['inspire_download'][$i] == 1) {
								//add further inspire_download service element for this layer
								//increment amount of view services
								$output['aaData'][$metadataIndex]->numberDownloadServices++;
							}*/
							//$inspireMonitoring['datasets'][$metadataIndex]->numberViewServices++;
							$serviceIndex++;
							//Add view service to dataset list
							//$inspireMonitoring['datasets'][$metadataIndex]->viewServiceId = $sqlTable['resource_uuid'][$i];
							$alreadyBuildVs[] = $sqlTable['resource_uuid'][$i];
						}
						
						
					}
					//$e = new mb_exception("generate downloadservice");
					if (!in_array($sqlTable['uuid'][$i],$alreadyBuildDls)) {
						//initialize array of uuids which are already tested for downloadservices!
						//the options are all the same for one single metadata uuid - therefor this has only to be done once for a uuid!
						//the service information is pulled from the first organization - TODO - maybe change this?
					
						//build download services
						//get download options 
						//Pull download options for specific dataset from mapbender database and show them
						$downloadOptionsConnector = new connector("http://localhost".$_SERVER['SCRIPT_NAME']."/../../php/mod_getDownloadOptions.php?id=".$sqlTable['uuid'][$i]);
						//$e = new mb_exception("download options: ".$downloadOptionsConnector->file);
						$downloadOptions = json_decode($downloadOptionsConnector->file);
						if (defined("MAPBENDER_PATH") && MAPBENDER_PATH != '') { 
							$mapbenderUrl = MAPBENDER_PATH;
						} else {
							$mapbenderUrl = "http://geoportal.saarland.de/mapbender";
						}
						if ($downloadOptions != null) {
							$mdUuid = $sqlTable['uuid'][$i];
							
							foreach ($downloadOptions->{$mdUuid}->option as $option) {
								//create download service entries
								//create ids for download services on the fly
								//How to generate UUIDs for INSPIRE Download Service Metadata records (not really needed for INSPIRE!!! See DB Metadaten)
								//12-4-4-4-8
								//dataurl
								//LAYER uuid (12-4), Type (4) - 0001, MD uuid (4-8)
								//wfs
								//WFS uuid (12-4), MD uuid (4-4-8)
								//wmsgetmap
								//LAYER uuid (12-4), Type (4) - 0002, MD uuid (4-8)
								//
								//if (isset($sqlTable['service_uuid'][$i]) &&  $sqlTable['service_uuid'][$i] != '' ) {									//TODO - don't use service uuids from table but from options!
									//TODO - what to do if metadata id is no uuid?????
									$uuid = new Uuid();
									$uuidTest = $uuid->isuuid($mdUuid);
									
									if ($uuidTest) {
										$mdPart = explode('-',$mdUuid);
										//$e = new mb_exception("is uuid");
									} else {
										//$e = new mb_exception("is not uuid");
										$mdPart = array();
										$mdPart[2] = substr($mdUuid,-12,-8);
										$mdPart[3] = substr($mdUuid,-4);
									}
									$servicePart = explode('-',$option->serviceUuid);
									//$mdPart = explode('-',$mdUuid);
									
									switch ($option->type) {
										case "wmslayergetmap":
											$dlsFileIdentifier = $servicePart[0]."-".$servicePart[1]."-"."0002"."-".$mdPart[3]."-".$mdPart[4];
											$capUrl = $mapbenderUrl."/php/mod_inspireDownloadFeed.php?id=".$sqlTable['uuid'][$i]."&type=SERVICE&generateFrom=wmslayer&layerid=".$option->resourceId;
										break;
										case "wmslayerdataurl":
											$dlsFileIdentifier = $servicePart[0]."-".$servicePart[1]."-"."0001"."-".$mdPart[3]."-".$mdPart[4];
											$capUrl = $mapbenderUrl."/php/mod_inspireDownloadFeed.php?id=".$sqlTable['uuid'][$i]."&type=SERVICE&generateFrom=dataurl&layerid=".$option->resourceId;
										break;
										case "wfsrequest":
											$dlsFileIdentifier = $servicePart[0]."-".$servicePart[1]."-".$mdPart[2]."-".$mdPart[3]."-".$mdPart[4];
											$capUrl = $mapbenderUrl."/php/mod_inspireDownloadFeed.php?id=".$sqlTable['uuid'][$i]."&type=SERVICE&generateFrom=wfs&wfsid=".$option->serviceId;
										break;
										case "downloadlink":
											$linkPart = md5($option->link);
											$dlsFileIdentifier =  $mdPart[0]."-".$mdPart[1]."-".$mdPart[2]."-".substr($linkPart, -12, 4)."-".substr($linkPart, -8, 8);
											$capUrl = $mapbenderUrl."/php/mod_inspireDownloadFeed.php?id=".$sqlTable['uuid'][$i]."&type=SERVICE&generateFrom=metadata&id=".$mdUuid;
										break;


									}
								
								/*} else {
									//generate dummy uuid - is not good!
									//$uuid = new Uuid();
									//$dlsFileIdentifier = $uuid;
									$dlsFileIdentifier = "00000000-0000-0000-000000000000";
								}*/
								
								if (!in_array($dlsFileIdentifier,$alreadyBuildDls)) {
								//generate the rest of the service element
								//addview view service
								$inspireMonitoring['services'][$serviceIndex]->id = $dlsFileIdentifier;
								//name
								$inspireMonitoring['services'][$serviceIndex]->name = "Downloadservice für ".$sqlTable['title'][$i];
								//typ
								$inspireMonitoring['services'][$serviceIndex]->type = "Download-Dienst";
								//url
								$inspireMonitoring['services'][$serviceIndex]->url = $capUrl;
								//orga
								$inspireMonitoring['services'][$serviceIndex]->organization = $sqlTable['organization'][$i];
								//orga email
								$inspireMonitoring['services'][$serviceIndex]->orgaEmail = $sqlTable['orgaEmail'][$i];
								//metadata exists
								$inspireMonitoring['services'][$serviceIndex]->metadataExists = "[X]";
								//service uuid - view/download difference
								$inspireMonitoring['services'][$serviceIndex]->serviceUuid = $dlsFileIdentifier;
								//metadata conform
								$inspireMonitoring['services'][$serviceIndex]->metadataConform = "[X]";
								//metadata available
								$inspireMonitoring['services'][$serviceIndex]->metadataAvailable = "[X]";
								//service conform
								$inspireMonitoring['services'][$serviceIndex]->serviceConform = "[X]";
								//requests per day
								$inspireMonitoring['services'][$serviceIndex]->requestsPerDay = 0;
								//comment
								$inspireMonitoring['services'][$serviceIndex]->report = "";
								//report
								$inspireMonitoring['services'][$serviceIndex]->comment = "";
								$inspireMonitoring['datasets'][$metadataIndex]->numberDownloadServices++;
								$serviceIndex++;
								//add download service to dataset list
								$inspireMonitoring['datasets'][$metadataIndex]->downloadServiceId = $dlsFileIdentifier; 
								$alreadyBuildDls[] = $dlsFileIdentifier;
								}

							}
						}
					}
					//$e = new mb_exception("generate organization entry");
					if (!in_array($sqlTable['organization'][$i],$alreadyReadOrgas)) {
						$alreadyReadOrgas[] = $sqlTable['organization'][$i];
						$inspireMonitoring['organizations'][$orgaIndex]->id = $sqlTable['orgaId'][$i];
						$inspireMonitoring['organizations'][$orgaIndex]->name = $sqlTable['organization'][$i];
						switch ($sqlTable['adminCode'][$i]) {
							case "NUTS 1":
								$inspireMonitoring['organizations'][$orgaIndex]->adminLevel = "Land";
							break;
							case "NUTS 2":
								$inspireMonitoring['organizations'][$orgaIndex]->adminLevel = "Regierungsbezirk";
							break;
							case "NUTS 3":
								$inspireMonitoring['organizations'][$orgaIndex]->adminLevel = "Landkreis";
							break;
							case "LAU 1":
								$inspireMonitoring['organizations'][$orgaIndex]->adminLevel = "Verbandsgemeinde/Stadt";
							break;
							case "LAU 2":
								$inspireMonitoring['organizations'][$orgaIndex]->adminLevel = "Gemeinde";
							break;
							default:
								$inspireMonitoring['organizations'][$orgaIndex]->adminLevel = "Andere";
							break;
						}
						
						$orgaIndex++;
					}
					if ($sqlTable['resource_type'][$i] == "wfs_featuretype" && $sqlTable['inspire_download'][$i] == "1") {
						//add download service element
						//$output['aaData'][$metadataIndex]->numberDownloadServices++;
						$insCat .= $sqlTable['inspire_cat_monitoring'][$i];
						if ($insCat != '') {
							$insCat .= ",";
						}
						//$output['aaData'][$metadataIndex]->downloadServices[]->id = $sqlTable['resource_id'][$i];
					}
					//reduce categories if there are double entries
					$arrayInspireCategories = array_unique(explode(',',(rtrim($insCat,','))));
					//$e = new mb_exception($insCat);
					for ($j=0; $j < 34; $j++) {
						$catId = $j+1;
						if (in_array($catId,$arrayInspireCategories)) {
							$arrayInspireCat[$j] = "[X]";
						} else {
							$arrayInspireCat[$j] = "";
						}
					}
					$inspireMonitoring['datasets'][$metadataIndex]->inspireCategories = $arrayInspireCat;
					//set view and downloadservice information
					if ($inspireMonitoring['datasets'][$metadataIndex]->numberViewServices >  0) {
						$inspireMonitoring['datasets'][$metadataIndex]->viewServiceAvailable = "[X]";	
					}
					if ($inspireMonitoring['datasets'][$metadataIndex]->numberDownloadServices >  0) {
						$inspireMonitoring['datasets'][$metadataIndex]->downloadServiceAvailable = "[X]";	
					}
					
					
				}
			}
		}
		if (isset($exportObjects) && $exportObjects != null) {
			//echo "export ".$exportObjects;
			switch ($exportObjects) {
				case "datasets":
					$html = "";
					foreach ($inspireMonitoring["datasets"] as $entry) {
						$row = "";
						$row .= $entry->datasetid."|";
						$row .= $entry->title."|";
						$row .= $entry->organization."|";
						$row .= $entry->orgaEmail."|";
						$row .= $entry->relevantArea."|";
						$row .= $entry->actualArea."|";
						$row .= $entry->metadataExists."|";
						$row .= $entry->uuid."|";
						$row .= $entry->metadataConform."|";
						$row .= $entry->metadataAvailable."|";
						$row .= $entry->viewServiceAvailable."|";
						$row .= $entry->viewServiceId."|";
						$row .= $entry->downloadServiceAvailable."|";
						$row .= $entry->downloadServiceId."|";
						$row .= $entry->datasetConform."|";
						foreach ($entry->inspireCategories as $category) {
							$row .= $category."|";
						}
						$row .= $entry->report."|";
						$row .= $entry->comment;
						$row .= "\r\n";
						$html .= $row;
					}
					header('Content-type: application/octetstream');
					//header('Content-Length: ' . strlen($html));
					header('Content-Disposition: attachment; filename="datasets.csv"'); 
					echo $html;
				break;
				case "services":
					$html = "";
					foreach ($inspireMonitoring["services"] as $entry) {
						$row = "";
						$row .= $entry->id."|";
						$row .= $entry->name."|";
						$row .= $entry->type."|";
						$row .= $entry->url."|";
						$row .= $entry->organization."|";
						$row .= $entry->orgaEmail."|";
						$row .= $entry->metadataExists."|";
						$row .= $entry->serviceUuid."|";
						$row .= $entry->metadataConform."|";
						$row .= $entry->metadataAvailable."|";
						$row .= $entry->serviceConform."|";
						$row .= $entry->requestsPerDay."|";
						$row .= $entry->report."|";
						$row .= $entry->comment;
						$row .= "\r\n";
						$html .= $row;
					}
					header('Content-type: application/octetstream');
					//header('Content-Length: ' . strlen($html));
					header('Content-Disposition: attachment; filename="services.csv"'); 
					echo $html;
				break;
				case "organizations":
				$html = "";
					foreach ($inspireMonitoring["organizations"] as $entry) {
						$row = "";
						$row .= $entry->id."|";
						$row .= $entry->name."|";
						$row .= $entry->adminLevel;
						$row .= "\r\n";
						$html .= $row;
					}
					header('Content-type: application/octetstream');
					header('Content-Length: ' . filesize($html));
					header('Content-Disposition: attachment; filename="organizations.csv"'); 
					echo $html;
				break;
			}
		} else {
			//give away all information as json
			header('Content-Type: application/json; charset='.CHARSET);
			echo json_encode($inspireMonitoring, JSON_NUMERIC_CHECK);
		}
	break;
	default:
		//normal output as json
		$metadataIndex = -1;
		$currentUuid = "";
		if (isset($iTotal)) {
			//$e = new mb_exception("iTotal= ".$iTotal);
			$output = array(
				"sEcho" => intval($_REQUEST['sEcho']),
				"iTotalRecords" => $iTotal,
				"iTotalDisplayRecords" => $iTotal,
				"aaData" => array()
			);
		} else {
			$output = array(
				"sEcho" => intval($_REQUEST['sEcho']),
				"iTotalRecords" => $iTotal,
				"iTotalDisplayRecords" => $iTotal,
				"aaData" => array()
			);
		}
		/*$output = array(
			"aaData" => array()
		);*/
		for ($i=0; $i < count($sqlTable['uuid']); $i++){
			//filter for orga_id
			//generate entry only if orga_id is the same as expected
			if (!$registratingDepartments || ($registratingDepartments != null && in_array($groupOwnerArray[4][$i],explode(',',$registratingDepartments)))) {
				if ($sqlTable['uuid'][$i] != $currentUuid) {
					//new metadataset identified - initialize it
					$currentUuid = $sqlTable['uuid'][$i];
					$metadataIndex++;
					$output['aaData'][$metadataIndex]->detailImage = "<img id=\"expander\" src=\"../img/gnome/stock_zoom-in.png\">";
					//$output['aaData'][$metadataIndex]->title = $sqlTable['title'][$i];

					$output['aaData'][$metadataIndex]->title = "<a href=\"../php/mod_iso19139ToHtml.php?url=".urlencode($mapbenderUrl."/php/mod_dataISOMetadata.php?outputFormat=iso19139&id=".$sqlTable['uuid'][$i])."\">".$sqlTable['title'][$i]."</a>";

					$output['aaData'][$metadataIndex]->uuid = $sqlTable['uuid'][$i];
					$output['aaData'][$metadataIndex]->organization = $sqlTable['organization'][$i];
					$insCat = '';
					$output['aaData'][$metadataIndex]->inspireCategories = '';
					$output['aaData'][$metadataIndex]->numberViewServices = 0;
					$output['aaData'][$metadataIndex]->numberDownloadServices = 0;
				}
				if ($metadataIndex > -1) { //prohibit indexes which are not real - otherwise the json array will become an object 
					if ($sqlTable['resource_type'][$i] == "layer") {
						//addview service element
						$output['aaData'][$metadataIndex]->viewServices[]->id = $sqlTable['resource_id'][$i];
						$catString = $sqlTable['inspire_cat'][$i];
						$insCat .= $sqlTable['inspire_cat'][$i];
						if ($insCat != '') {
							$insCat .= ",";
						}
						//increment amount of view services
						$output['aaData'][$metadataIndex]->numberViewServices++;
						if ($sqlTable['inspire_download'][$i] == 1) {
							//add further inspire_download service element for this layer
							//increment amount of view services
							$output['aaData'][$metadataIndex]->numberDownloadServices++;
						}
					}
					if ($sqlTable['resource_type'][$i] == "wfs_featuretype" && $sqlTable['inspire_download'][$i] == "1") {
						//add download service element
						$output['aaData'][$metadataIndex]->numberDownloadServices++;
						$insCat .= $sqlTable['inspire_cat'][$i];
						if ($insCat != '') {
							$insCat .= ",";
						}
						//$output['aaData'][$metadataIndex]->downloadServices[]->id = $sqlTable['resource_id'][$i];
					}
					//reduce categories if there are double entries
					$output['aaData'][$metadataIndex]->inspireCategories = implode(',',array_unique(explode(',',(rtrim($insCat,',')))));
				}
			}
			if (isset($jsonLimit) && $metadataIndex >= $jsonLimit) {
				break;
			}
		}
		header('Content-Type: application/json; charset='.CHARSET);
		echo json_encode($output, JSON_NUMERIC_CHECK);
		//$endTime = microtime();
		//$diffTime = $endTime - $startTime;
		//echo "<br>".$diffTime."<br>";
		//echo "Ready!";
	break;
}

function getOrganizationInfoForServices($groupOwnerArray) {
	//split array into two lists which are requested in two separate sqls
	$listGroupIds = array();
	$listOwnerIds = array();
	//echo "<br>count groupOwnerArray: ".count($groupOwnerArray[0]);
	for ($i=0; $i < count($groupOwnerArray[0]); $i++){
		$key = $i;
		if (!isset($groupOwnerArray[0][$i]) || is_null($groupOwnerArray[0][$i]) || $groupOwnerArray[0][$i] == 0){
			$listOwnerIds[$key] = $groupOwnerArray[1][$i];
		} else {
			$listGroupIds[$key] = $groupOwnerArray[0][$i];
		}
	}
	//for ownerList
	$metadataContactArray = array();
	$metadataContact = array();
	$listGroupIdsKeys =  array_keys($listGroupIds);
	$listOwnerIdsKeys =  array_keys($listOwnerIds);
	$listOwnerIdsString = implode(",",$listOwnerIds);
	$listGroupIdsString = implode(",",$listGroupIds);
	//do the database requests
	if ($listOwnerIdsString != "") {
		$sql = "SELECT mb_group_name as metadatapointofcontactorgname, mb_group_title as metadatapointofcontactorgtitle, mb_group_id, mb_group_logo_path  as metadatapointofcontactorglogo, mb_group_address as metadatapointofcontactorgaddress, mb_group_email as metadatapointofcontactorgemail, mb_group_postcode as metadatapointofcontactorgpostcode, mb_group_city as metadatapointofcontactorgcity, mb_group_voicetelephone as metadatapointofcontactorgtelephone, mb_group_facsimiletelephone as metadatapointofcontactorgfax, mb_group_admin_code , b.mb_user_id as mb_user_id FROM mb_group AS a, mb_user AS b, mb_user_mb_group AS c WHERE b.mb_user_id IN (".$listOwnerIdsString.") AND b.mb_user_id = c.fkey_mb_user_id AND c.fkey_mb_group_id = a.mb_group_id AND c.mb_user_mb_group_type=2";
		$resultOrgaOwner = db_query($sql);
		$index  = 0;
		while ($row = db_fetch_array($resultOrgaOwner)) {
			//push information into metadataContactArray
			$metadataContactOwnerArray[$index]['metadatapointofcontactorgname'] = $row['metadatapointofcontactorgname'];
			$metadataContactOwnerArray[$index]['metadatapointofcontactorgemail'] = $row['metadatapointofcontactorgemail'];
			$metadataContactOwnerArray[$index]['mb_user_id'] = $row['mb_user_id'];
			$metadataContactOwnerArray[$index]['orga_id'] = $row['mb_group_id'];
			$metadataContactOwnerArray[$index]['admin_code'] = $row['mb_group_admin_code'];
			$index++;
		}
		$index = 0;
		//push information directly into $groupOwnerArray at indizes from 
		for ($i=0; $i < count($listOwnerIdsKeys); $i++){
			//find index of user with special id in array $metadataContactOwnerArray['user_id']
			$index = findIndexInMultiDimArray($metadataContactOwnerArray, $listOwnerIds[$listOwnerIdsKeys[$i]], 'mb_user_id'); 
			$groupOwnerArray[2][$listOwnerIdsKeys[$i]] = $metadataContactOwnerArray[$index]['user_id']; //user_id - 2
			$groupOwnerArray[3][$listOwnerIdsKeys[$i]] = $metadataContactOwnerArray[$index]['metadatapointofcontactorgname']; //orga_name - 3	
			$groupOwnerArray[4][$listOwnerIdsKeys[$i]] = $metadataContactOwnerArray[$index]['orga_id'];
			$groupOwnerArray[5][$listOwnerIdsKeys[$i]] = $metadataContactOwnerArray[$index]['metadatapointofcontactorgemail']; //orga_email 5
			$groupOwnerArray[6][$listOwnerIdsKeys[$i]] = $metadataContactOwnerArray[$index]['admin_code']; // 6
		}
	}
	//for groupList
	if ($listGroupIdsString != "") {
		$sql = "SELECT mb_group_name as metadatapointofcontactorgname, mb_group_title as metadatapointofcontactorgtitle, mb_group_id, mb_group_logo_path  as metadatapointofcontactorglogo, mb_group_address as metadatapointofcontactorgaddress, mb_group_email as metadatapointofcontactorgemail, mb_group_postcode as metadatapointofcontactorgpostcode, mb_group_city as metadatapointofcontactorgcity, mb_group_voicetelephone as metadatapointofcontactorgtelephone, mb_group_facsimiletelephone as metadatapointofcontactorgfax, mb_group_id, mb_group_admin_code FROM mb_group WHERE mb_group_id IN (".$listGroupIdsString.")";
		$resultOrgaGroup = db_query($sql);
		$index  = 0;
		while ($row = db_fetch_array($resultOrgaGroup)) {
			//push information into metadataContactArray
			$metadataContactGroupArray[$index]['metadatapointofcontactorgname'] = $row['metadatapointofcontactorgname'];
			$metadataContactGroupArray[$index]['metadatapointofcontactorgemail'] = $row['metadatapointofcontactorgemail'];
			$metadataContactGroupArray[$index]['mb_group_id'] = $row['mb_group_id'];
			$metadataContactGroupArray[$index]['orga_id'] = $row['mb_group_id'];
			$metadataContactGroupArray[$index]['admin_code'] = $row['mb_group_admin_code'];
			$index++;
		}
		$index = 0;
		//push information directly into $groupOwnerArray at indizes from 
		for ($i=0; $i < count($listGroupIdsKeys); $i++){
			//find index of user with special id in array $metadataContactGroupArray['user_id']
			$index = findIndexInMultiDimArray($metadataContactGroupArray, $listGroupIds[$listGroupIdsKeys[$i]], 'mb_group_id');
			$groupOwnerArray[2][$listGroupIdsKeys[$i]] = $metadataContactGroupArray[$index]['mb_group_id']; //user_id - 2
			$groupOwnerArray[3][$listGroupIdsKeys[$i]] = $metadataContactGroupArray[$index]['metadatapointofcontactorgname']; //orga_name - 3	
			$groupOwnerArray[4][$listGroupIdsKeys[$i]] = $metadataContactGroupArray[$index]['orga_id'];
			$groupOwnerArray[5][$listGroupIdsKeys[$i]] = $metadataContactGroupArray[$index]['metadatapointofcontactorgemail'];
			$groupOwnerArray[6][$listGroupIdsKeys[$i]] = $metadataContactGroupArray[$index]['admin_code'];
		}
	}
	return $groupOwnerArray;
} 

function replaceCategories($idString, $inspireCategories){
	$idString = str_replace('}{',',',$idString);
	$idString = str_replace('{','',str_replace('}','',$idString));
	$idArray = explode(',',$idString);
	$catStringNew = "";
	for ($i=0; $i < count($idArray); $i++){
		if (isset($inspireCategories['title'][$idArray[$i]]) && $inspireCategories['title'][$idArray[$i]] != '') {
			$catStringNew .= $inspireCategories['title'][$idArray[$i]]." (".$inspireCategories['key'][$idArray[$i]]."),";
		}
	}
	$catStringNew = rtrim($catStringNew,',');
	return $catStringNew;
}

function replaceCategoriesList($idString){
	$idString = str_replace('}{',',',$idString);
	$idString = str_replace('{','',str_replace('}','',$idString));
	return $idString;
}

function findIndexInMultiDimArray($multiDimArray, $needle, $columnName) {
    foreach($multiDimArray as $index => $object) {
        if($object[$columnName] == $needle) return $index;
    }
    return FALSE;
}

//for debugging purposes only
function logit($text){
	 if($h = fopen("/tmp/pullInspireMonitoring.log","a")){
				$content = $text .chr(13).chr(10);
				if(!fwrite($h,$content)){
					#exit;
				}
				fclose($h);
			}
	 	
 }

?>
