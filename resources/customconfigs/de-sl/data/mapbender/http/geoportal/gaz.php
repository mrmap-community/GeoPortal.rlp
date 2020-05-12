<?php
# 
# Copyright (C) 2002 CCGIS 
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2, or (at your option)
# any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.

#Function to wrap the search criteria from the portal search and distribute them to the different search moduls:
#Gazetteer Modul:	gaz_geom.php
#wiki Modul:		gaz_wiki.php
#OpenSearch Modul:	mod_readOpenSearchResults.php

require_once(dirname(__FILE__)."/../../conf/mapbender.conf");
require_once(dirname(__FILE__)."/../../conf/geoportal.conf");
require_once(dirname(__FILE__)."/../classes/class_administration.php");
require_once(dirname(__FILE__)."/../classes/class_mb_exception.php");

$n = new mb_notice("-- gaz.php was invoked--");
$con = db_connect(DBSERVER,OWNER,PW);
db_select_db(DB,$con);

$resdir = RESULT_DIR;
//exception wrapper
function throwE($t){
	$e = new mb_notice("portal search: ".$t."!");
}

(isset($_SERVER["argv"][1]))? ($userId = $_SERVER["argv"][1]) : (throwE('userId lacks ...'));
(isset($_SERVER["argv"][2]))? ($searchId = $_SERVER["argv"][2]) : (throwE('searchId lacks ...'));
(isset($_SERVER["argv"][3]))? ($searchText = $_SERVER["argv"][3]) : (throwE('searchText lacks ...'));
(isset($_SERVER["argv"][4]))? ($searchEPSG = $_SERVER["argv"][4]) : (throwE('searchEPSG lacks ...'));
(isset($_SERVER["argv"][5]))? ($registratingDepartments = $_SERVER["argv"][5]) : (throwE('registratingDepartments lacks ...'));
(isset($_SERVER["argv"][6]))? ($isoCategories = $_SERVER["argv"][6]) : (throwE('isoCategories lacks ...'));
(isset($_SERVER["argv"][7]))? ($regTimeBegin = $_SERVER["argv"][7]) : (throwE('regTimeBegin lacks ...'));
(isset($_SERVER["argv"][8]))? ($regTimeEnd = $_SERVER["argv"][8]) : (throwE('regTimeEnd lacks ...'));
(isset($_SERVER["argv"][9]))? ($searchBbox = $_SERVER["argv"][9]) : (throwE('searchBbox lacks ...'));
(isset($_SERVER["argv"][10]))? ($searchTypeBbox = $_SERVER["argv"][10]) : (throwE('searchTypeBbox lacks ...'));
(isset($_SERVER["argv"][11]))? ($searchResources = $_SERVER["argv"][11]) : (throwE('searchResources lacks ...'));
(isset($_SERVER["argv"][12]))? ($timeBegin= $_SERVER["argv"][12]) : (throwE('timeBegin lacks ...'));
(isset($_SERVER["argv"][13]))? ($timeEnd = $_SERVER["argv"][13]) : (throwE('timeEnd lacks ...'));
(isset($_SERVER["argv"][14]))? ($orderBy = $_SERVER["argv"][14]) : (throwE('orderBy lacks ...'));

//define standard searchEPSG if the client has not yet been initialized - therefor the needed EPSG is not known
if(!isset($searchEPSG) || $searchEPSG = '' || empty($searchEPSG)){$searchEPSG = "EPSG:31466";}
else{$searchEPSG = $_SERVER["argv"][4];}

//if the searchText has more than one element the commas has to be exchanged by plus -> opensearch!

$searchTextOS = str_replace(",","+",$searchText);
if ($searchTextOS ==='false' || $searchTextOS ==='*'){
	$searchTextOS = '*';
}

$openSearchFilter = "";
//generate portalu search filter:
//needed filter criteria:
//1. searchText - anyText Field
//2. isoCategories
//3. regTimeBegin
//4. regTimeEnd
//5. searchBbox
//6. searchTypeBbox
//7. timeBegin
//8. timeEnd
//9. orderBy
//check if orderBy is set 
//
if (isset($regTimeBegin) & ($regTimeBegin != 'false')){
	$openSearchFilter .= "+t1:".$regTimeBegin;
}
if (isset($regTimeEnd) & ($regTimeEnd != 'false')){
	$openSearchFilter .= "+t2:".$regTimeEnd;
}
if (isset($searchBbox) & ($searchBbox != 'false')){
	//parse bbox
	$spatialFilterCoords = explode(',',$searchBbox);
	//definition of the spatial filter
	$openSearchFilter .= "+x1:".$spatialFilterCoords[0];
	$openSearchFilter .= "+x2:".$spatialFilterCoords[2];
        $openSearchFilter .= "+y1:".$spatialFilterCoords[1];
        $openSearchFilter .= "+y2:".$spatialFilterCoords[3];
}
if (isset($searchTypeBbox) & ($searchTypeBbox != 'false')){
	if ($searchTypeBbox == 'intersects') {
		$openSearchFilter .= "+coord:intersect";
	}
	if ($searchTypeBbox == 'outside') {
		$openSearchFilter .= "+coord:outside";
	}
	if ($searchTypeBbox == 'inside') {
		$openSearchFilter .= "+coord:inside";
	}
	
}
if (isset($orderBy) & ($orderBy != 'false')){
	if ($orderBy == 'rank') {
		$openSearchFilter .= "+ranking:score";
	}
	if ($orderBy == 'date') {
		$openSearchFilter .= "+ranking:date";
	}
	if ($orderBy == 'title') {
		$openSearchFilter .= "+ranking:title";
	}
	
} else {
	$openSearchFilter .= "+ranking:score";
}
//apply filter
//following part is only set if the portalu opensearch is used!
$searchTextOS .= $openSearchFilter;


// check if some extended is requested - in the old version this is defined by one parameter. The new interface would not distinguish between this two kind of search cases. Every search is a also an extented search. See mod_callMetadata.php




#Geometry search:
$exec = "php5 /data/mapbender/http/geoportal/gaz_geom.php '".$userId."' '".$searchText."' '".$searchEPSG."' '".$resdir."/".$searchId."_geom_ready.xml' > ".$resdir."/".$searchId."_geom.xml &";
exec($exec);

#wiki search:
//$exec = "php5 /data/mapbender/http/geoportal/gaz_wiki.php '".$searchText."' > ".$resdir."/".$searchId."_wiki.xml &";
//exec($exec);

#OpenSearch Search over distributed instances of Portal-U - configuration in mapbender database
$exec = "php5 /data/mapbender/http/geoportal/mod_readOpenSearchResults.php '".$searchId."' '".$searchTextOS."' > ".$resdir."/request_".$searchId."_opensearch.xml &";
exec($exec);
#Search via CSW 2.0.2 AP ISO 1.0 Interfaces:
//$exec = "php5 /data/mapbender/http/geoportal/mod_readCSWResults.php '".$searchId."' '".$searchTextOS."' > ".$resdir."/request_".$searchId."_opensearch.xml &";
//$exec = "php5 /data/mapbender/http/x_geoportal/mod_readOpenSearchResults.php '".$searchId."' '".$searchTextOS."' '+t1:2006-05-01+t2:2010-07-01' > ".$resdir."/request_".$id."_opensearch.xml &";

//exec($exec);

?>
