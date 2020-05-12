<?php
//http://www.geoportal.rlp.de/mapbender/php/mod_layerISOMetadata.php?SERVICE=WMS&outputFormat=iso19139&Id=24356
// $Id: mod_layerISOMetadata.php 235
// http://www.mapbender.org/index.php/Inspire_Metadata_Editor
// Copyright (C) 2002 CCGIS 
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

//Script to generate a conformant ISO19139 service metadata record for a wms layer which is registrated in the mapbender database. It works as a webservice
//The record will be fulfill the demands of the INSPIRE metadata regulation from 03.12.2008 and the iso19139
require_once(dirname(__FILE__) . "/../../core/globalSettings.php");
require_once(dirname(__FILE__) . "/../classes/class_connector.php");
require_once(dirname(__FILE__) . "/../classes/class_administration.php");
require_once(dirname(__FILE__) . "/../php/mod_validateInspire.php");

$con = db_connect(DBSERVER,OWNER,PW);
db_select_db(DB,$con);

$admin = new administration();

//define the view or table to use as input for metadata generation if this is wished. If not, the data will be directly read from the database tables
$wmsView = "wms_search_table";
$wmsView = '';
//parse request parameter
//make all parameters available as upper case
foreach($_REQUEST as $key => $val) {
	$_REQUEST[strtoupper($key)] = $val;
}
//validate request params
if (isset($_REQUEST['ID']) & $_REQUEST['ID'] != "") {
	//validate integer
	$testMatch = $_REQUEST["ID"];
	$pattern = '/^[\d]*$/';		
 	if (!preg_match($pattern,$testMatch)){
		// echo 'Id: <b>'.$testMatch.'</b> is not valid.<br/>'; 
		echo 'Id is not valid (integer).<br/>'; 
		die(); 		
 	}
	$recordId = $testMatch;
	$testMatch = NULL;
}
//
if ($_REQUEST['OUTPUTFORMAT'] == "iso19139") {
	//Initialize XML document
	$iso19139Doc = new DOMDocument('1.0');
	$iso19139Doc->encoding = 'UTF-8';
	$iso19139Doc->preserveWhiteSpace = false;
	$iso19139Doc->formatOutput = true;
} else {
	//echo 'outputFormat: <b>'.$_REQUEST['OUTPUTFORMAT'].'</b> is not set or valid.<br/>'; 
	echo 'Parameter <b>outputFormat</b> is not set or valid (iso19139).<br/>'; 
	die();
}
//if validation is requested
//
if (isset($_REQUEST['VALIDATE']) and $_REQUEST['VALIDATE'] != "true") {
	//echo 'validate: <b>'.$_REQUEST['VALIDATE'].'</b> is not valid.<br/>'; 
	echo 'Parameter <b>validate</b> is not valid (true).<br/>'; 
	die();
}
//some needfull functions to pull metadata out of the database!
function fillISO19139($iso19139, $recordId) {
        global $wmsView;
	global $admin;
	//read out relevant information from mapbender database:
	if ($wmsView != '') {
		$sql = "SELECT * ";
		$sql .= "FROM ".$wmsView." WHERE layer_id = $1";
	}
	else {
	//next function is for normal mapbender installations and read the info directly from the wms and layer tables
		$sql = "SELECT ";
		$sql .= "layer.layer_id,layer.layer_name, layer.layer_title, layer.layer_abstract, layer.layer_pos, layer.layer_parent, layer.layer_minscale, layer.layer_maxscale, layer.uuid,";
		$sql .= "wms.wms_title, wms.wms_abstract, wms.wms_id, wms.fees, wms.accessconstraints, wms.contactperson, ";
		$sql .= "wms.contactposition, wms.contactorganization, wms.address, wms.city, wms_timestamp, wms_owner, ";
		$sql .= "wms.stateorprovince, wms.postcode, wms.contactvoicetelephone, wms.contactfacsimiletelephone, wms.wms_owsproxy,";
		$sql .= "wms.contactelectronicmailaddress, wms.country, wms.fkey_mb_group_id, ";
		$sql .= "layer_epsg.minx || ',' || layer_epsg.miny || ',' || layer_epsg.maxx || ',' || layer_epsg.maxy  as bbox ";
		$sql .= "FROM wms, layer, layer_epsg WHERE layer_id = $1 and layer.fkey_wms_id = wms.wms_id";
		$sql .= " and layer_epsg.fkey_layer_id=layer.layer_id and layer_epsg.epsg='EPSG:4326'";
	}
	$v = array((integer)$recordId);
	$t = array('i');
	$res = db_prep_query($sql,$v,$t);
	$mapbenderMetadata = db_fetch_array($res);
	
	//Get other needed information out of mapbender database (if not already defined in the view):
	//service data
	if ($wmsView != '') {
		$sql = "SELECT contactorganization, contactelectronicmailaddress ";
		$sql .= "FROM wms WHERE wms_id = $1";
		$v = array((integer)$mapbenderMetadata['wms_id']);
		$t = array('i');
		$res = db_prep_query($sql,$v,$t);
		$serviceMetadata = db_fetch_array($res);
	}
	//infos about the registrating department, check first if a special metadata point of contact is defined in the service table - function from mod_showMetadata - TODO: should be defined in admin class
	if (!isset($mapbenderMetadata['fkey_mb_group_id']) or is_null($mapbenderMetadata['fkey_mb_group_id']) or $mapbenderMetadata['fkey_mb_group_id'] == 0){
		$e = new mb_notice("mod_layerISOMetadata.php: fkey_mb_group_id not found!");
		//Get information about owning user of the relation mb_user_mb_group - alternatively the defined fkey_mb_group_id from the service must be used!
		$sqlDep = "SELECT mb_group_name, mb_group_title, mb_group_id, mb_group_logo_path, mb_group_address, mb_group_email, mb_group_postcode, mb_group_city, mb_group_voicetelephone, mb_group_facsimiletelephone FROM mb_group AS a, mb_user AS b, mb_user_mb_group AS c WHERE b.mb_user_id = $1  AND b.mb_user_id = c.fkey_mb_user_id AND c.fkey_mb_group_id = a.mb_group_id AND c.mb_user_mb_group_type=2 LIMIT 1";
		$vDep = array($mapbenderMetadata['wms_owner']);
		$tDep = array('i');
		$resDep = db_prep_query($sqlDep, $vDep, $tDep);
		$departmentMetadata = db_fetch_array($resDep);
	} else {
		$e = new mb_notice("mod_layerISOMetadata.php: fkey_mb_group_id found!");
		$sqlDep = "SELECT mb_group_name , mb_group_title, mb_group_id, mb_group_logo_path , mb_group_address, mb_group_email, mb_group_postcode, mb_group_city, mb_group_voicetelephone, mb_group_facsimiletelephone FROM mb_group WHERE mb_group_id = $1 LIMIT 1";
		$vDep = array($mapbenderMetadata['fkey_mb_group_id']);
		$tDep = array('i');
		$resDep = db_prep_query($sqlDep, $vDep, $tDep);
		$departmentMetadata = db_fetch_array($resDep);
	}

	//infos about the owner of the service - he is the man who administrate the metadata - register the service
	$sql = "SELECT mb_user_email ";
	$sql .= "FROM mb_user WHERE mb_user_id = $1";
	$v = array((integer)$mapbenderMetadata['wms_owner']);
	$t = array('i');
	$res = db_prep_query($sql,$v,$t);
	$userMetadata = db_fetch_array($res);

	//check if resource is freely available to anonymous user - which are all users who search thru metadata catalogues:
	$hasPermission=$admin->getLayerPermission($mapbenderMetadata['wms_id'],$mapbenderMetadata['layer_name'],PUBLIC_USER);

	//Creating the central "MD_Metadata" node
	$MD_Metadata = $iso19139->createElementNS('http://www.isotc211.org/2005/gmd', 'gmd:MD_Metadata');

	$MD_Metadata = $iso19139->appendChild($MD_Metadata);
	#$MD_Metadata->setAttribute("xmlns:gmd", "http://schemas.opengis.net/iso/19139/20060504/gmd/gmd.xsd");
	$MD_Metadata->setAttribute("xmlns:srv", "http://www.isotc211.org/2005/srv");
	$MD_Metadata->setAttribute("xmlns:gml", "http://www.opengis.net/gml");
	$MD_Metadata->setAttribute("xmlns:gco", "http://www.isotc211.org/2005/gco");
	$MD_Metadata->setAttribute("xmlns:xlink", "http://www.w3.org/1999/xlink");
	$MD_Metadata->setAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
	$MD_Metadata->setAttribute("xsi:schemaLocation", "http://www.isotc211.org/2005/gmd ./xsd/gmd/gmd.xsd http://www.isotc211.org/2005/srv ./xsd/srv/srv.xsd");

	//generate identifier part 
	$identifier = $iso19139->createElement("gmd:fileIdentifier");
	$identifierString = $iso19139->createElement("gco:CharacterString");
	if (isset($mapbenderMetadata['uuid'])) {
		$identifierText = $iso19139->createTextNode($mapbenderMetadata['uuid']);
	}
	else {
		$identifierText = $iso19139->createTextNode("no id found");
	}
	$identifierString->appendChild($identifierText);
	$identifier->appendChild($identifierString);
	$MD_Metadata->appendChild($identifier);

	//generate language part B 10.3 (if available) of the inspire metadata regulation
	$language = $iso19139->createElement("gmd:language");
	$languagecode = $iso19139->createElement("gmd:LanguageCode");
	$languagecode->setAttribute("codeList", "http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/codelist/ML_gmxCodelists.xml#LanguageCode");
	if (isset($mapbenderMetadata['metadata_language'])) {
		$languageText = $iso19139->createTextNode($mapbenderMetadata['metadata_language']);
		$languagecode->setAttribute("codeListValue", $mapbenderMetadata['metadata_language']);
	}
	else {
		$languageText = $iso19139->createTextNode("ger");
		$languagecode->setAttribute("codeListValue", "ger");
	}
	$languagecode->appendChild($languageText);
	$language ->appendChild($languagecode);
	$language = $MD_Metadata->appendChild($language);

	//generate characterset part - first it should be utf8 ;-)
	$characterSet = $iso19139->createElement("gmd:characterSet");
	$characterSetCode = $iso19139->createElement("gmd:MD_CharacterSetCode");
	$characterSetCode->setAttribute("codeList", "http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/Codelist/ML_gmx
Codelists.xml#MD_CharacterSetCode");
	$characterSetCode->setAttribute("codeListValue", "utf8");
	$characterSet->appendChild($characterSetCode);
	$characterSet = $MD_Metadata->appendChild($characterSet);

	//generate MD_Scope part B 1.3 (if available)
	$hierarchyLevel = $iso19139->createElement("gmd:hierarchyLevel");
	$scopecode = $iso19139->createElement("gmd:MD_ScopeCode");
	$scopecode->setAttribute("codeList", "http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/codelist/ML_gmxCodelists.xml#MD_ScopeCode");
	if (isset($mapbenderMetadata['hierarchy_level'])) {
		$scopecode->setAttribute("codeListValue", $mapbenderMetadata['hierarchy_level']);//if such a metadata exists in the mapbender metadata view
		$scopeText = $iso19139->createTextNode($mapbenderMetadata['hierarchy_level']);
	}
	else {
		$scopecode->setAttribute("codeListValue", "service");
		$scopeText = $iso19139->createTextNode("service");
	}
	$scopecode->appendChild($scopeText);
	$hierarchyLevel->appendChild($scopecode);
	$hierarchyLevel=$MD_Metadata->appendChild($hierarchyLevel);

	//iso19139 demands a hierachyLevelName object
	$hierarchyLevelName = $iso19139->createElement("gmd:hierarchyLevelName");
	$hierarchyLevelNameString = $iso19139->createElement("gco:CharacterString");
	$hierarchyLevelNameText = $iso19139->createTextNode('Darstellungsdienst');
	$hierarchyLevelName->appendChild($hierarchyLevelNameString);
	$hierarchyLevelNameString->appendChild($hierarchyLevelNameText);
	$hierarchyLevelName=$MD_Metadata->appendChild($hierarchyLevelName);

	//Part B 10.1 responsible party for the resource
	$contact=$iso19139->createElement("gmd:contact");
	$CI_ResponsibleParty=$iso19139->createElement("gmd:CI_ResponsibleParty");
	$organisationName=$iso19139->createElement("gmd:organisationName");
	$organisationName_cs=$iso19139->createElement("gco:CharacterString");
	if (isset($departmentMetadata['mb_group_name'])) {
		$organisationNameText = $iso19139->createTextNode($departmentMetadata['mb_group_name']); 
	}
	else
	{
		$organisationNameText=$iso19139->createTextNode('department not known');
	}
	$contactInfo=$iso19139->createElement("gmd:contactInfo");
	$CI_Contact=$iso19139->createElement("gmd:CI_Contact");
	$address=$iso19139->createElement("gmd:address");
	$CI_Address=$iso19139->createElement("gmd:CI_Address");
	$electronicMailAddress=$iso19139->createElement("gmd:electronicMailAddress");
	$electronicMailAddress_cs=$iso19139->createElement("gco:CharacterString");
	if (isset($userMetadata['mb_user_email'])) {
		//get email address from ows service metadata out of mapbender database	
		$electronicMailAddressText=$iso19139->createTextNode($userMetadata['mb_user_email']); 
	}
	else
	{
		$electronicMailAddressText=$iso19139->createTextNode('email not yet given');
	}
	$role=$iso19139->createElement("gmd:role");
	$CI_RoleCode=$iso19139->createElement("gmd:CI_RoleCode");
	$CI_RoleCode->setAttribute("codeList", "http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/codelist/ML_gmxCodelists.xml#CI_RoleCode");
	$CI_RoleCode->setAttribute("codeListValue", "pointOfContact");
	$CI_RoleCodeText=$iso19139->createTextNode("pointOfContact");

	//create xml tree
	$organisationName_cs->appendChild($organisationNameText);
	$organisationName->appendChild($organisationName_cs);
	$CI_ResponsibleParty->appendChild($organisationName);
	$electronicMailAddress_cs->appendChild($electronicMailAddressText);
	$electronicMailAddress->appendChild($electronicMailAddress_cs);
	$CI_Address->appendChild($electronicMailAddress);
	$address->appendChild($CI_Address);
	$CI_Contact->appendChild($address);
	$contactInfo->appendChild($CI_Contact);
	$CI_RoleCode->appendChild($CI_RoleCodeText);
	$role->appendChild($CI_RoleCode);
	$CI_ResponsibleParty->appendChild($contactInfo);
	$CI_ResponsibleParty->appendChild($role);
	$contact->appendChild($CI_ResponsibleParty);
	$MD_Metadata->appendChild($contact);

	//generate dateStamp part B 10.2 (if available)
	$dateStamp = $iso19139->createElement("gmd:dateStamp");
	$mddate = $iso19139->createElement("gco:Date");
	if (isset($mapbenderMetadata['wms_timestamp'])) {
		$mddateText = $iso19139->createTextNode(date("Y-m-d",$mapbenderMetadata['wms_timestamp']));
	}
	else {
		$mddateText = $iso19139->createTextNode("2000-01-01");
	}
	$mddate->appendChild($mddateText);
	$dateStamp->appendChild($mddate);
	$dateStamp=$MD_Metadata->appendChild($dateStamp);

	//standard definition - for wms everytime the same ;-)
	$metadataStandardName = $iso19139->createElement("gmd:metadataStandardName");
	$metadataStandardVersion = $iso19139->createElement("gmd:metadataStandardVersion");
	$metadataStandardNameText = $iso19139->createElement("gco:CharacterString");
	$metadataStandardVersionText = $iso19139->createElement("gco:CharacterString");
	$metadataStandardNameTextString = $iso19139->createTextNode("ISO19119");
	$metadataStandardVersionTextString = $iso19139->createTextNode("2005/PDAM 1");
	$metadataStandardNameText->appendChild($metadataStandardNameTextString);
	$metadataStandardVersionText->appendChild($metadataStandardVersionTextString);
	$metadataStandardName->appendChild($metadataStandardNameText);
	$metadataStandardVersion->appendChild($metadataStandardVersionText);
	$MD_Metadata->appendChild($metadataStandardName);
	$MD_Metadata->appendChild($metadataStandardVersion);

	//do the things for identification
	//create nodes
	$identificationInfo=$iso19139->createElement("gmd:identificationInfo");
	$SV_ServiceIdentification=$iso19139->createElement("srv:SV_ServiceIdentification");
	//TODO: add attribut if really needed
	//$SV_ServiceIdentification->setAttribute("id", "dataId");
	$citation=$iso19139->createElement("gmd:citation");
	$CI_Citation=$iso19139->createElement("gmd:CI_Citation");

	//create nodes for things which are defined - howto do the multiplicities? Ask Martin!
	//Create Resource title element B 1.1
	$title=$iso19139->createElement("gmd:title");
	$title_cs=$iso19139->createElement("gco:CharacterString");
	if (isset($mapbenderMetadata['wms_title'])) {
		$titleText = $iso19139->createTextNode($mapbenderMetadata['wms_title']." - ".$mapbenderMetadata['layer_title']);
	}
	else {
		$titleText = $iso19139->createTextNode("title not given");
	}
	$title_cs->appendChild($titleText);
	$title->appendChild($title_cs);
	$CI_Citation->appendChild($title);

	//Create date elements B5.2-5.4 - format will be only a date - no dateTime given
	//Do things for B 5.2 date of publication
	if (isset($mapbenderMetadata['wms_timestamp_create'])) {
		$date1=$iso19139->createElement("gmd:date");
		$CI_Date=$iso19139->createElement("gmd:CI_Date");
		$date2=$iso19139->createElement("gmd:date");
		$gcoDate=$iso19139->createElement("gco:Date");
		$dateType=$iso19139->createElement("gmd:dateType");
		$dateTypeCode=$iso19139->createElement("gmd:CI_DateTypeCode");
		$dateTypeCode->setAttribute("codeList", "http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/codelist/ML_gmxCodelists.xml#CI_DateTypeCode");
		$dateTypeCode->setAttribute("codeListValue", "publication");
		$dateTypeCodeText=$iso19139->createTextNode('publication');
		$dateText= $iso19139->createTextNode(date('Y-m-d',$mapbenderMetadata['wms_timestamp_create']));
		$dateTypeCode->appendChild($dateTypeCodeText);
		$dateType->appendChild($dateTypeCode);
		$gcoDate->appendChild($dateText);
		$date2->appendChild($gcoDate);
		$CI_Date->appendChild($date2);
		$CI_Date->appendChild($dateType);
		$date1->appendChild($CI_Date);
		$CI_Citation->appendChild($date1);
	}
	//Do things for B 5.3 date of revision
	if (isset($mapbenderMetadata['wms_timestamp'])) {
		$date1=$iso19139->createElement("gmd:date");
		$CI_Date=$iso19139->createElement("gmd:CI_Date");
		$date2=$iso19139->createElement("gmd:date");
		$gcoDate=$iso19139->createElement("gco:Date");
		$dateType=$iso19139->createElement("gmd:dateType");
		$dateTypeCode=$iso19139->createElement("gmd:CI_DateTypeCode");
		$dateTypeCode->setAttribute("codeList", "http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/codelist/ML_gmxCodelists.xml#CI_DateTypeCode");
		$dateTypeCode->setAttribute("codeListValue", "revision");
		$dateTypeCodeText=$iso19139->createTextNode('revision');
		$dateText= $iso19139->createTextNode(date('Y-m-d',$mapbenderMetadata['wms_timestamp']));
		$dateTypeCode->appendChild($dateTypeCodeText);
		$dateType->appendChild($dateTypeCode);
		$gcoDate->appendChild($dateText);
		$date2->appendChild($gcoDate);
		$CI_Date->appendChild($date2);
		$CI_Date->appendChild($dateType);
		$date1->appendChild($CI_Date);
		$CI_Citation->appendChild($date1);
	}
	//Do things for B 5.4 date of creation
	if (isset($mapbenderMetadata['wms_timestamp_creation'])) {
		$date1=$iso19139->createElement("gmd:date");
		$CI_Date=$iso19139->createElement("gmd:CI_Date");
		$date2=$iso19139->createElement("gmd:date");
		$gcoDate=$iso19139->createElement("gco:Date");
		$dateType=$iso19139->createElement("gmd:dateType");
		$dateTypeCode=$iso19139->createElement("gmd:CI_DateTypeCode");
		$dateTypeCode->setAttribute("codeList", "http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/codelist/ML_gmxCodelists.xml#CI_DateTypeCode");
		$dateTypeCode->setAttribute("codeListValue", "creation");
		$dateTypeCodeText=$iso19139->createTextNode('creation');
		$dateText= $iso19139->createTextNode(date('Y-m-d',$mapbenderMetadata['wms_timestamp_creation']));
		$dateTypeCode->appendChild($dateTypeCodeText);
		$dateType->appendChild($dateTypeCode);
		$gcoDate->appendChild($dateText);
		$date2->appendChild($gcoDate);
		$CI_Date->appendChild($date2);
		$CI_Date->appendChild($dateType);
		$date1->appendChild($CI_Date);
		$CI_Citation->appendChild($date1);
	}
	$citation->appendChild($CI_Citation);
	$SV_ServiceIdentification->appendChild($citation);

	//Create part for abstract B 1.2
	$abstract=$iso19139->createElement("gmd:abstract");
	$abstract_cs=$iso19139->createElement("gco:CharacterString");
	if (isset($mapbenderMetadata['wms_abstract']) or isset($mapbenderMetadata['layer_abstract'])) {
		$abstractText = $iso19139->createTextNode($mapbenderMetadata['wms_abstract'].":".$mapbenderMetadata['layer_abstract']);
	}
	else {
		$abstractText = $iso19139->createTextNode("not yet defined");
	}
	$abstract_cs->appendChild($abstractText);
	$abstract->appendChild($abstract_cs);
	$SV_ServiceIdentification->appendChild($abstract);

	//Create part for point of contact for service identification
	//Define relevant objects
	$pointOfContact=$iso19139->createElement("gmd:pointOfContact");
	$CI_ResponsibleParty=$iso19139->createElement("gmd:CI_ResponsibleParty");
	$organisationName=$iso19139->createElement("gmd:organisationName");
	$orgaName_cs=$iso19139->createElement("gco:CharacterString");
	$contactInfo=$iso19139->createElement("gmd:contactInfo");
	$CI_Contact=$iso19139->createElement("gmd:CI_Contact");
	$address_1=$iso19139->createElement("gmd:address");
	$CI_Address=$iso19139->createElement("gmd:CI_Address");
	$electronicMailAddress=$iso19139->createElement("gmd:electronicMailAddress");
	$email_cs=$iso19139->createElement("gco:CharacterString");
	$role=$iso19139->createElement("gmd:role");
	$CI_RoleCode=$iso19139->createElement("gmd:CI_RoleCode");
	$CI_RoleCode->setAttribute("codeList", "http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/codelist/ML_gmxCodelists.xml#CI_RoleCode");
	$CI_RoleCode->setAttribute("codeListValue", "publisher");
	if (isset($mapbenderMetadata['contactorganization'])) {
		$resOrgaText = $iso19139->createTextNode($mapbenderMetadata['contactorganization']);
	}
	else {
		$resOrgaText= $iso19139->createTextNode("not yet defined");
	}
	if (isset($mapbenderMetadata['contactelectronicmailaddress'])) {
		$resMailText = $iso19139->createTextNode($mapbenderMetadata['contactelectronicmailaddress']);
	}
	else {
		$resMailText = $iso19139->createTextNode("geoportal@lvgl.saarland.de");
	}
	$resRoleText = $iso19139->createTextNode("publisher");
	$orgaName_cs->appendChild($resOrgaText);
	$organisationName->appendChild($orgaName_cs);
	$CI_ResponsibleParty->appendChild($organisationName);
	$email_cs->appendChild($resMailText);
	$electronicMailAddress->appendChild($email_cs);
	$CI_Address->appendChild($electronicMailAddress);
	$address_1->appendChild($CI_Address);
	$CI_Contact->appendChild($address_1);
	$contactInfo->appendChild($CI_Contact);
	$CI_ResponsibleParty->appendChild($contactInfo);
	$CI_RoleCode->appendChild($resRoleText);
	$role->appendChild($CI_RoleCode);
	$CI_ResponsibleParty->appendChild($role);
	$pointOfContact->appendChild($CI_ResponsibleParty);
	$SV_ServiceIdentification->appendChild($pointOfContact);

	//generate graphical overview part
	$sql = "SELECT layer_preview.layer_map_preview_filename FROM layer_preview WHERE layer_preview.fkey_layer_id=$1";
	$v = array((integer)$mapbenderMetadata["layer_id"]);
	$t = array('i');
	$res = db_prep_query($sql,$v,$t);
	$row = db_fetch_array($res);

	//old version 
/*	if (isset($row['layer_map_preview_filename']) & $row['layer_map_preview_filename'] != '') {
		$graphicOverview=$iso19139->createElement("gmd:graphicOverview");
		$MD_BrowseGraphic=$iso19139->createElement("gmd:MD_BrowseGraphic");
		$fileName=$iso19139->createElement("gmd:fileName");
		$fileName_cs=$iso19139->createElement("gco:characterString");
		$previewFilenameText = $iso19139->createTextNode("http://www.gdi-rp-dienste3.rlp.de/mapbender/x_geoportal/layer_preview/".$row['layer_map_preview_filename']);
		$fileName_cs->appendChild($previewFilenameText);
		$fileName->appendChild($fileName_cs);
		$MD_BrowseGraphic->appendChild($fileName);
		$graphicOverview->appendChild($MD_BrowseGraphic);
		$SV_ServiceIdentification->appendChild($graphicOverview);
	}
*/	
	//use the example version of bavaria
	if (file_exists(PREVIEW_DIR."/".$mapbenderMetadata['layer_id']."_layer_map_preview.jpg")) {	//TODO
		$graphicOverview=$iso19139->createElement("gmd:graphicOverview");
		$MD_BrowseGraphic=$iso19139->createElement("gmd:MD_BrowseGraphic");
		$fileName=$iso19139->createElement("gmd:fileName");
		$fileName_cs=$iso19139->createElement("gco:CharacterString");
		$previewFilenameText = $iso19139->createTextNode("http://geoportal.saarland.de/mapbender/geoportal/preview/".$mapbenderMetadata['layer_id']."_layer_map_preview.jpg"); //TODO use constant for absolute path
		$fileName_cs->appendChild($previewFilenameText);
		$fileName->appendChild($fileName_cs);

		$fileDescription=$iso19139->createElement("gmd:fileDescription");
		$fileDescription_cs=$iso19139->createElement("gco:CharacterString");
		$fileDescription_text=$iso19139->createTextNode("Thumbnail");

		$fileDescription_cs->appendChild($fileDescription_text);
		$fileDescription->appendChild($fileDescription_cs);
		

		$fileType=$iso19139->createElement("gmd:fileType");
		$fileType_cs=$iso19139->createElement("gco:CharacterString");
		$fileType_text=$iso19139->createTextNode("JPEG");

		$fileType_cs->appendChild($fileType_text);
		$fileType->appendChild($fileType_cs);

		$MD_BrowseGraphic->appendChild($fileName);

		$MD_BrowseGraphic->appendChild($fileDescription);
		$MD_BrowseGraphic->appendChild($fileType);

		$graphicOverview->appendChild($MD_BrowseGraphic);
		$SV_ServiceIdentification->appendChild($graphicOverview);
	}
	
	/*example 3: 
	<gmd:graphicOverview>
  		<gmd:MD_BrowseGraphic>
   			 <gmd:fileName>
      				<gco:CharacterString>http://goesr.noaa.gov/browse/datasetIdentifier</gco:CharacterString>
    			</gmd:fileName>
  		</gmd:MD_BrowseGraphic>
	</gmd:graphicOverview>*/
	
	//generate keyword part - for services the inspire themes are not applicable!!!
	//read keywords for resource out of the database:
	$sql = "SELECT keyword.keyword FROM keyword, layer_keyword WHERE layer_keyword.fkey_layer_id=$1 AND layer_keyword.fkey_keyword_id=keyword.keyword_id";
	$v = array((integer)$recordId);
	$t = array('i');
	$res = db_prep_query($sql,$v,$t);
	$descriptiveKeywords=$iso19139->createElement("gmd:descriptiveKeywords");
	$MD_Keywords=$iso19139->createElement("gmd:MD_Keywords");
	while ($row = db_fetch_array($res)) {
		$keyword=$iso19139->createElement("gmd:keyword");
		$keyword_cs=$iso19139->createElement("gco:CharacterString");
		$keywordText = $iso19139->createTextNode($row['keyword']);
		$keyword_cs->appendChild($keywordText);
		$keyword->appendChild($keyword_cs);
		$MD_Keywords->appendChild($keyword);
	}
	//a special keyword for service type wms as INSPIRE likes it ;-)
	$keyword=$iso19139->createElement("gmd:keyword");
	$keyword_cs=$iso19139->createElement("gco:CharacterString");
	$keywordText = $iso19139->createTextNode("infoMapAccessService");
	$keyword_cs->appendChild($keywordText);
	$keyword->appendChild($keyword_cs);
	$MD_Keywords->appendChild($keyword);
	
	//pull special keywords from custom categories:	
	$sql = "SELECT custom_category.custom_category_key FROM custom_category, layer_custom_category WHERE layer_custom_category.fkey_layer_id = $1 AND layer_custom_category.fkey_custom_category_id =  custom_category.custom_category_id AND custom_category_hidden = 0";
	$v = array((integer)$recordId);
	$t = array('i');
	$res = db_prep_query($sql,$v,$t);
	$e = new mb_notice("look for custom categories: ");
	$countCustom = 0;
	while ($row = db_fetch_array($res)) {
		$keyword=$iso19139->createElement("gmd:keyword");
		$keyword_cs=$iso19139->createElement("gco:CharacterString");
		$keywordText = $iso19139->createTextNode($row['custom_category_key']);
		$keyword_cs->appendChild($keywordText);
		$keyword->appendChild($keyword_cs);
		$MD_Keywords->appendChild($keyword);
		$countCustom++;
	}
	$descriptiveKeywords->appendChild($MD_Keywords);
	$SV_ServiceIdentification->appendChild($descriptiveKeywords);

	//Part B 3 INSPIRE Category
	//do this only if an INSPIRE keyword (Annex I-III) is set
	//Resource Constraints B 8
	$resourceConstraints=$iso19139->createElement("gmd:resourceConstraints");
	$MD_LegalConstraints=$iso19139->createElement("gmd:MD_Constraints");
	$useLimitation=$iso19139->createElement("gmd:useLimitation");
	$useLimitation_cs=$iso19139->createElement("gco:CharacterString");
	if(isset($mapbenderMetadata['accessconstraints'])){
		$useLimitationText=$iso19139->createTextNode($mapbenderMetadata['accessconstraints']);
	}
	else
	{
		$useLimitationText=$iso19139->createTextNode("no conditions apply");
	}
 	//TODO: Mapping of constraints between OWS/registry and INSPIRE see View Service Guidance
	$useLimitation_cs->appendChild($useLimitationText);
	$useLimitation->appendChild($useLimitation_cs);
	$MD_LegalConstraints->appendChild($useLimitation);
	$resourceConstraints->appendChild($MD_LegalConstraints);
	$SV_ServiceIdentification->appendChild($resourceConstraints);

	$resourceConstraints=$iso19139->createElement("gmd:resourceConstraints");
	$MD_LegalConstraints=$iso19139->createElement("gmd:MD_LegalConstraints");
	$accessConstraints=$iso19139->createElement("gmd:accessConstraints");
	$MD_RestrictionCode=$iso19139->createElement("gmd:MD_RestrictionCode");
	$MD_RestrictionCode->setAttribute("codeList", "http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/codelist/ML_gmxCodelists.xml#MD_RetrictionCode");
	$MD_RestrictionCode->setAttribute("codeListValue", "otherRestrictions");
	$MD_RestrictionCodeText=$iso19139->createTextNode("otherRestrictions");
	$otherConstraints=$iso19139->createElement("gmd:otherConstraints");
	$otherConstraints_cs=$iso19139->createElement("gco:CharacterString");
	if (isset($mapbenderMetadata['accessconstraints']) & strtoupper($mapbenderMetadata['accessconstraints']) != 'NONE'){
			$otherConstraintsText=$iso19139->createTextNode($mapbenderMetadata['accessconstraints']);
	}
	else {
			$otherConstraintsText=$iso19139->createTextNode("no constraints");
	}
	$otherConstraints_cs->appendChild($otherConstraintsText);
	$otherConstraints->appendChild($otherConstraints_cs);

	$MD_RestrictionCode->appendChild($MD_RestrictionCodeText);
	$accessConstraints->appendChild($MD_RestrictionCode);
		
	$MD_LegalConstraints->appendChild($accessConstraints);
	$MD_LegalConstraints->appendChild($otherConstraints);
	$resourceConstraints->appendChild($MD_LegalConstraints);
		
	$SV_ServiceIdentification->appendChild($resourceConstraints);

	/* example
	<srv:serviceType>
    		<gco:LocalName>view</gco:LocalName>
	</srv:serviceType>*/

	$serviceType=$iso19139->createElement("srv:serviceType");
	$localName=$iso19139->createElement("gco:LocalName");
	$serviceTypeText=$iso19139->createTextNode("view");
	$localName->appendChild($serviceTypeText);
	$serviceType->appendChild($localName);
	$SV_ServiceIdentification->appendChild($serviceType);

	$serviceTypeVersion=$iso19139->createElement("srv:serviceTypeVersion");
	$serviceTypeVersion_cs=$iso19139->createElement("gco:CharacterString");
	$serviceTypeVersionText=$iso19139->createTextNode("1.1.1");

	$serviceTypeVersion_cs->appendChild($serviceTypeVersionText);
	$serviceTypeVersion->appendChild($serviceTypeVersion_cs);
	$SV_ServiceIdentification->appendChild($serviceTypeVersion);

	//Geographical Extent
	$bbox = array();
	//initialize if no extent is defined in the database
	$bbox[0] = -180;
	$bbox[1] = -90;
	$bbox[2] = 180;
	$bbox[3] = 90;
	if (isset($mapbenderMetadata['bbox']) & ($mapbenderMetadata['bbox'] != '')) {
		$bbox = explode(',',$mapbenderMetadata['bbox']);
	}
	$extent=$iso19139->createElement("srv:extent");
	$EX_Extent=$iso19139->createElement("gmd:EX_Extent");
	$geographicElement=$iso19139->createElement("gmd:geographicElement");
	$EX_GeographicBoundingBox=$iso19139->createElement("gmd:EX_GeographicBoundingBox");

	$westBoundLongitude=$iso19139->createElement("gmd:westBoundLongitude");
	$wb_dec=$iso19139->createElement("gco:Decimal");
	$wb_text=$iso19139->createTextNode($bbox[0]);

	$eastBoundLongitude=$iso19139->createElement("gmd:eastBoundLongitude");
	$eb_dec=$iso19139->createElement("gco:Decimal");
	$eb_text=$iso19139->createTextNode($bbox[2]);

	$southBoundLatitude=$iso19139->createElement("gmd:southBoundLatitude");
	$sb_dec=$iso19139->createElement("gco:Decimal");
	$sb_text=$iso19139->createTextNode($bbox[1]);

	$northBoundLatitude=$iso19139->createElement("gmd:northBoundLatitude");
	$nb_dec=$iso19139->createElement("gco:Decimal");
	$nb_text=$iso19139->createTextNode($bbox[3]);

	$wb_dec->appendChild($wb_text);
	$westBoundLongitude->appendChild($wb_dec);
	$EX_GeographicBoundingBox->appendChild($westBoundLongitude);

	$eb_dec->appendChild($eb_text);
	$eastBoundLongitude->appendChild($eb_dec);
	$EX_GeographicBoundingBox->appendChild($eastBoundLongitude);

	$sb_dec->appendChild($sb_text);
	$southBoundLatitude->appendChild($sb_dec);
	$EX_GeographicBoundingBox->appendChild($southBoundLatitude);

	$nb_dec->appendChild($nb_text);
	$northBoundLatitude->appendChild($nb_dec);
	$EX_GeographicBoundingBox->appendChild($northBoundLatitude);

	$geographicElement->appendChild($EX_GeographicBoundingBox);
	$EX_Extent->appendChild($geographicElement);
	$extent->appendChild($EX_Extent);

	$SV_ServiceIdentification->appendChild($extent);

	//read all metadata entries:
	$i=0;
	$sql = <<<SQL

SELECT metadata_id, uuid, link, linktype, md_format, origin, datasetid FROM mb_metadata 
INNER JOIN (SELECT * from ows_relation_metadata 
WHERE fkey_layer_id = $recordId ) as relation ON 
mb_metadata.metadata_id = relation.fkey_metadata_id WHERE mb_metadata.origin IN ('capabilities','external','metador')

SQL;
	$res_metadataurl = db_query($sql);
	if ($res_metadataurl != false) {
		$couplingType=$iso19139->createElement("srv:couplingType");
		$SV_CouplingType=$iso19139->createElement("srv:SV_CouplingType");
		$SV_CouplingType->setAttribute("codeList", "SV_CouplingType");
		$SV_CouplingType->setAttribute("codeListValue", "tight");
		$couplingType->appendChild($SV_CouplingType);
		$SV_ServiceIdentification->appendChild($couplingType);
	}
	
	//declare coupling type:
	/*example from guidance paper:
<srv:couplingType>
	<srv:SV_CouplingType codeList="SV_CouplingType" codeListValue="tight"/>
</srv:couplingType>
<srv:couplingType gco:nilReason="missing"/>
<srv:containsOperations gco:nilReason="missing"/>
<srv:operatesOn xlink:href="http://image2000.jrc.it#image2000_1_nl2_multi"/>*/
	
	//to the things which have to be done for integrating the service into a client like portalu ... they have defined another location to put the GetCap URL than INSPIRE does it

	$containsOperation=$iso19139->createElement("srv:containsOperations");
	$SV_OperationMetadata=$iso19139->createElement("srv:SV_OperationMetadata");

	$operationName=$iso19139->createElement("srv:operationName");
	$operationName_cs=$iso19139->createElement("gco:CharacterString");

	$operationNameText=$iso19139->createTextNode("GetCapabilities");
	
	$operationName_cs->appendChild($operationNameText);
	$operationName->appendChild($operationName_cs);

	//srv DCP **************************************
	$DCP=$iso19139->createElement("srv:DCP");
	$DCPList=$iso19139->createElement("srv:DCPList");
	$DCPList->setAttribute("codeList", "DCPList");
	$DCPList->setAttribute("codeListValue", "WebService");

	$DCP->appendChild($DCPList);
	
	//connectPoint **********************************
	$connectPoint=$iso19139->createElement("srv:connectPoint");
	
	$CI_OnlineResource=$iso19139->createElement("gmd:CI_OnlineResource");

	$gmd_linkage=$iso19139->createElement("gmd:linkage");
	$gmd_URL=$iso19139->createElement("gmd:URL");

	//Check if anonymous user has rights to access this layer - if not ? which resource should be advertised? TODO
	//if ($hasPermission) {
		$gmd_URLText=$iso19139->createTextNode("http://".$_SERVER['HTTP_HOST']."/mapbender/php/wms.php?inspire=1&layer_id=".$mapbenderMetadata['layer_id']."&REQUEST=GetCapabilities&SERVICE=WMS");
	//}
	/*else {
		$serverWithOutPort80 = str_replace(":80","",$_SERVER['HTTP_HOST']);//fix problem when metadata is generated thru curl invocations
		$gmd_URLText=$iso19139->createTextNode("https://".$serverWithOutPort80."/http_auth/".$mapbenderMetadata['layer_id']."?REQUEST=GetCapabilities&SERVICE=WMS");
	}*/
	$gmd_URL->appendChild($gmd_URLText);
	$gmd_linkage->appendChild($gmd_URL);
	$CI_OnlineResource->appendChild($gmd_linkage);
	$connectPoint->appendChild($CI_OnlineResource);

	$SV_OperationMetadata->appendChild($operationName);
	$SV_OperationMetadata->appendChild($DCP);
	$SV_OperationMetadata->appendChild($connectPoint);

	$containsOperation->appendChild($SV_OperationMetadata);

	$SV_ServiceIdentification->appendChild($containsOperation);

	//fill in operatesOn fields with datasetid if given
	/*INSPIRE example: <srv:operatesOn xlink:href="http://image2000.jrc.it#image2000_1_nl2_multi"/>*/
	/*INSPIRE demands a href for the metadata record!*/
	/*TODO: Exchange HTTP_HOST with other baseurl*/
	while ($row_metadata = db_fetch_array($res_metadataurl)) {
			switch ($row_metadata['origin']) {
				case 'capabilities':
					$operatesOn=$iso19139->createElement("srv:operatesOn");
					$operatesOn->setAttribute("xlink:href", "http://".$_SERVER['HTTP_HOST']."/mapbender/php/mod_dataISOMetadata.php?outputFormat=iso19139&id=".$row_metadata['uuid']);
					$operatesOn->setAttribute("uuidref", $row_metadata['datasetid']);
					$SV_ServiceIdentification->appendChild($operatesOn);
				break;
				case 'metador':
					$operatesOn=$iso19139->createElement("srv:operatesOn");
					if (defined('METADATA_DEFAULT_CODESPACE')) {
						$operatesOn->setAttribute("xlink:href", "http://".$_SERVER['HTTP_HOST']."/mapbender/php/mod_dataISOMetadata.php?outputFormat=iso19139&id=".$row_metadata['uuid']);
						$operatesOn->setAttribute("uuidref", METADATA_DEFAULT_CODESPACE."#".$row_metadata['uuid']);
					} else {
						$operatesOn->setAttribute("xlink:href", "http://".$_SERVER['HTTP_HOST']."/mapbender/php/mod_dataISOMetadata.php?outputFormat=iso19139&id=".$row_metadata['uuid']);
						$operatesOn->setAttribute("uuidref", "http://www.mapbender.org#".$row_metadata['uuid']);
					}
					
					$SV_ServiceIdentification->appendChild($operatesOn);
				break;
				case 'external':
					$operatesOn=$iso19139->createElement("srv:operatesOn");
					$operatesOn->setAttribute("xlink:href", "http://".$_SERVER['HTTP_HOST']."/mapbender/php/mod_dataISOMetadata.php?outputFormat=iso19139&id=".$row_metadata['uuid']);
					$operatesOn->setAttribute("uuidref", $row_metadata['uuid']);
					$SV_ServiceIdentification->appendChild($operatesOn);
				break;
				default:
				break;
			}
		}
	
/*
	$serviceTypeVersion_cs->appendChild($serviceTypeVersionText);
	$serviceTypeVersion->appendChild($serviceTypeVersion_cs);
	$SV_ServiceIdentification->appendChild($serviceTypeVersion);
*/
	$identificationInfo->appendChild($SV_ServiceIdentification);

	//distributionInfo - is demanded from iso19139
	$gmd_distributionInfo=$iso19139->createElement("gmd:distributionInfo");
	$MD_Distribution=$iso19139->createElement("gmd:MD_Distribution");
	$gmd_distributionFormat=$iso19139->createElement("gmd:distributionFormat");
	$MD_Format=$iso19139->createElement("gmd:MD_Format");
	$gmd_name=$iso19139->createElement("gmd:name");
	$gmd_version=$iso19139->createElement("gmd:version");
	$gmd_name->setAttribute("gco:nilReason", "inapplicable");
	$gmd_version->setAttribute("gco:nilReason", "inapplicable");
	$gmd_transferOptions=$iso19139->createElement("gmd:transferOptions");
	$MD_DigitalTransferOptions=$iso19139->createElement("gmd:MD_DigitalTransferOptions");
	$gmd_onLine=$iso19139->createElement("gmd:onLine");

	$CI_OnlineResource=$iso19139->createElement("gmd:CI_OnlineResource");

	$gmd_linkage=$iso19139->createElement("gmd:linkage");
	$gmd_URL=$iso19139->createElement("gmd:URL");

	//Check if anonymous user has rights to access this layer - if not ? which resource should be advertised? TODO
	if ($hasPermission) {
		$gmd_URLText=$iso19139->createTextNode("http://".$_SERVER['HTTP_HOST']."/mapbender/php/wms.php?inspire=1&layer_id=".$mapbenderMetadata['layer_id']."&REQUEST=GetCapabilities&SERVICE=WMS");
	}
	else {
		$gmd_URLText=$iso19139->createTextNode("https://".$_SERVER['HTTP_HOST']."/http_auth/".$mapbenderMetadata['layer_id']."?REQUEST=GetCapabilities&SERVICE=WMS");
	}
	$gmd_URL->appendChild($gmd_URLText);
	$gmd_linkage->appendChild($gmd_URL);
	$CI_OnlineResource->appendChild($gmd_linkage);

	//append things which geonetwork needs to invoke service/layer or what else? - Here the name of the layer and the protocol seems to be needed?
	//a problem will occur, if the link to get map is not the same as the link to get caps? So how can we handle this? It seems to be very silly! 
	$gmdProtocol = $iso19139->createElement("gmd:protocol");
	$gmdProtocol_cs = $iso19139->createElement("gco:CharacterString");
	$gmdProtocolText = $iso19139->createTextNode("OGC:WMS-1.1.1-http-get-map");//for ever 'OGC:WMS-1.1.1-http-get-map'

	$gmdName=$iso19139->createElement("gmd:name");
	$gmdName_cs=$iso19139->createElement("gco:CharacterString");
	$gmdNameText=$iso19139->createTextNode($mapbenderMetadata['layer_name']); //Layername?

	$gmdDescription = $iso19139->createElement("gmd:description");
	$gmdDescription_cs = $iso19139->createElement("gco:CharacterString");
	$gmdDescriptionText = $iso19139->createTextNode($mapbenderMetadata['layer_abstract']);//Layer Abstract

	$gmdProtocol_cs->appendChild($gmdProtocolText);
	$gmdProtocol->appendChild($gmdProtocol_cs);
	$CI_OnlineResource->appendChild($gmdProtocol);

	$gmdName_cs->appendChild($gmdNameText);
	$gmdName->appendChild($gmdName_cs);
	$CI_OnlineResource->appendChild($gmdName);

	$gmdDescription_cs->appendChild($gmdDescriptionText);
	$gmdDescription->appendChild($gmdDescription_cs);
	$CI_OnlineResource->appendChild($gmdDescription);
	
//***********************************************************************************
	$gmd_onLine->appendChild($CI_OnlineResource);
	$MD_DigitalTransferOptions->appendChild($gmd_onLine);
	$gmd_transferOptions->appendChild($MD_DigitalTransferOptions);
	$MD_Format->appendChild($gmd_name);
	$MD_Format->appendChild($gmd_version);
	$gmd_distributionFormat->appendChild($MD_Format);
	$MD_Distribution->appendChild($gmd_distributionFormat);
	$MD_Distribution->appendChild($gmd_transferOptions);
	$gmd_distributionInfo->appendChild($MD_Distribution);

	//dataQualityInfo
	$gmd_dataQualityInfo=$iso19139->createElement("gmd:dataQualityInfo");
	$DQ_DataQuality=$iso19139->createElement("gmd:DQ_DataQuality");
	$gmd_scope=$iso19139->createElement("gmd:scope");
	$DQ_Scope=$iso19139->createElement("gmd:DQ_Scope");
	$gmd_level=$iso19139->createElement("gmd:level");
	$MD_ScopeCode=$iso19139->createElement("gmd:MD_ScopeCode");
	$MD_ScopeCodeText=$iso19139->createTextNode("service");
	$MD_ScopeCode->setAttribute("codeList", "http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/codelist/ML_gmxCodelists.xml#MD_ScopeCode");
	$MD_ScopeCode->setAttribute("codeListValue", "service");
	$MD_ScopeCode->appendChild($MD_ScopeCodeText);
	$gmd_level->appendChild($MD_ScopeCode);
	$DQ_Scope->appendChild($gmd_level);
	$gmd_scope->appendChild($DQ_Scope);
	$DQ_DataQuality->appendChild($gmd_scope);

	//gmd:report in dataQualityInfo
	$gmd_report=$iso19139->createElement("gmd:report");
	$DQ_DomainConsistency=$iso19139->createElement("gmd:DQ_DomainConsistency");
	$DQ_DomainConsistency->setAttribute("xsi:type","gmd:DQ_DomainConsistency_Type");
	$gmd_result=$iso19139->createElement("gmd:result");
	$DQ_ConformanceResult=$iso19139->createElement("gmd:DQ_ConformanceResult");
	$gmd_specification=$iso19139->createElement("gmd:specification");
	$CI_Citation=$iso19139->createElement("gmd:CI_Citation");
	$gmd_title=$iso19139->createElement("gmd:title");
	$gmd_title_cs=$iso19139->createElement("gco:CharacterString");
	$gmd_titleText=$iso19139->createTextNode("Service Abstract Suite"); //TODO put in the inspire test suites from mb database!
	$gmd_date=$iso19139->createElement("gmd:date");
	$CI_Date=$iso19139->createElement("gmd:CI_Date");
	$gmd_date_2=$iso19139->createElement("gmd:date");
	$gco_Date=$iso19139->createElement("gco:Date");
	$gco_DateText=$iso19139->createTextNode("2010-03-10"); //TODO put in the info from database
	
	$gmd_dateType=$iso19139->createElement("gmd:dateType");
	$CI_DateTypeCode=$iso19139->createElement("gmd:CI_DateTypeCode");
	$CI_DateTypeCode->setAttribute("codeList","http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/codelist/ML_gmxCodelists.xml#CI_DateTypeCode");
	$CI_DateTypeCode->setAttribute("codeListValue","publication");
	$CI_DateTypeCodeText=$iso19139->createTextNode("publication");
	
	$gmd_explanation=$iso19139->createElement("gmd:explanation");
	$gmd_explanation_cs=$iso19139->createElement("gco:CharacterString");
	$gmd_explanationText=$iso19139->createTextNode("No explanation available");
		
	$gmd_pass=$iso19139->createElement("gmd:pass");
	$gco_Boolean=$iso19139->createElement("gco:Boolean");
	$gco_BooleanText=$iso19139->createTextNode("true"); //TODO maybe set here a string cause it can be unevaluated!! See Implementing Rules
	//generate XML objects
	$gco_Date->appendChild($gco_DateText);
	$gmd_date->appendChild($gco_Date);
	
	$CI_DateTypeCode->appendChild($CI_DateTypeCodeText);
	$gmd_dateType->appendChild($CI_DateTypeCode);
	$CI_Date->appendChild($gmd_date);
	$CI_Date->appendChild($gmd_dateType);
	$gmd_date_2->appendChild($CI_Date);
	$gmd_title_cs->appendChild($gmd_titleText);
	$gmd_title->appendChild($gmd_title_cs);
	$CI_Citation->appendChild($gmd_title);
	$CI_Citation->appendChild($gmd_date_2);
	$gmd_specification->appendChild($CI_Citation);
	$gmd_explanation_cs->appendChild($gmd_explanationText);
	$gmd_explanation->appendChild($gmd_explanation_cs);
	$gco_Boolean->appendChild($gco_BooleanText);
	$gmd_pass->appendChild($gco_Boolean);
	$DQ_ConformanceResult->appendChild($gmd_specification);
	$DQ_ConformanceResult->appendChild($gmd_explanation);
	$DQ_ConformanceResult->appendChild($gmd_pass);
	$gmd_result->appendChild($DQ_ConformanceResult);
	$DQ_DomainConsistency->appendChild($gmd_result);
	$gmd_report->appendChild($DQ_DomainConsistency);
	$DQ_DataQuality->appendChild($gmd_report);
	$gmd_dataQualityInfo->appendChild($DQ_DataQuality);
	//$MD_ScopeCode->setAttribute("codeListValue", "service");
	$MD_Metadata->appendChild($identificationInfo);
	$MD_Metadata->appendChild($gmd_distributionInfo);
	$MD_Metadata->appendChild($gmd_dataQualityInfo);
	return $iso19139->saveXML();
}
	//function to give away the xml data
	function pushISO19139($iso19139Doc, $recordId) {
		//echo $recordId;
		header("Content-type: application/xhtml+xml; charset=UTF-8");
		$xml = fillISO19139($iso19139Doc, $recordId);
		echo $xml;
		die();
	}

function getEpsgByLayerId ($layer_id) { // from merge_layer.php
	$epsg_list = "";
	$sql = "SELECT DISTINCT epsg FROM layer_epsg WHERE fkey_layer_id = $1";
	$v = array($layer_id);
	$t = array('i');
	$res = db_prep_query($sql, $v, $t);
	while($row = db_fetch_array($res)){
		$epsg_list .= $row['epsg'] . " ";
	}
	return trim($epsg_list);
}
function getEpsgArrayByLayerId ($layer_id) { // from merge_layer.php
	//$epsg_list = "";
	$epsg_array=array();
	$sql = "SELECT DISTINCT epsg FROM layer_epsg WHERE fkey_layer_id = $1";
	$v = array($layer_id);
	$t = array('i');
	$res = db_prep_query($sql, $v, $t);
	$cnt=0;
	while($row = db_fetch_array($res)){
		$epsg_array[$cnt] = $row['epsg'];
		$cnt++;
	}
	return $epsg_array;
}

function guid(){
    if (function_exists('com_create_guid')){
        return com_create_guid();
    }else{
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = chr(123)// "{"
                .substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12)
                .chr(125);// "}"
        return $uuid;
    }
}

//do all the other things which had to be done ;-)
if ($_REQUEST['VALIDATE'] == "true"){
	$xml = fillISO19139($iso19139Doc, $recordId);
	validateInspire($xml);
} else {
	pushISO19139($iso19139Doc, $recordId); //throw it out to world!
}
?>

