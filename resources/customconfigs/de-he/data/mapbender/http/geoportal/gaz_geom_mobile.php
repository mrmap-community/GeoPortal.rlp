<?php
# $Id$
# http://www.mapbender.org/index.php/gaz_service.php
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
#
# This module has been modified in August and September 2015
# by Christian Benz, Darmstadt, Germany. It was adapted to an 
# address search based on three tables of different size. The
# modifications only refer to the database query part. The input
# and output procedures (e.g. production of json-file) were reused
# from the proceeding version.

require_once(dirname(__FILE__)."/../../core/globalSettings.php");
require_once(dirname(__FILE__)."/../../conf/geoportal.conf");
require_once(dirname(__FILE__)."/../classes/class_mb_exception.php");
require_once(dirname(__FILE__)."/../classes/class_json.php");
require_once(dirname(__FILE__)."/../extensions/JSON.php");

//Check if the result should be delivered as a webservice


IF ($_REQUEST['resultTarget'] != 'web') {
	(isset($_SERVER["argv"][1]))? ($user_id = $_SERVER["argv"][1]) : ($e = new mb_exception("geom: user lacks!"));
	(isset($_SERVER["argv"][2]))? ($sstr = $_SERVER["argv"][2]) : ($e = new mb_exception("geom: string lacks!"));
	(isset($_SERVER["argv"][3]))? ($epsg = $_SERVER["argv"][3]) : ($e = new mb_exception("geom: epsg lacks!"));
	$searchThruWeb = false;
} else {
	$maxResults = 15; //set default
	$outputFormat = 'json'; //set default
	$epsg = 25832;
	
	if (isset($_REQUEST["maxResults"]) & $_REQUEST["maxResults"] != "") {
		//validate integer to 100 - not more
		$testMatch = $_REQUEST["maxResults"];
		//give max 99 entries - more will be to slow
		$pattern = '/^([0-9]{0,1})([0-9]{1})$/';		
 		if (!preg_match($pattern,$testMatch)){ 
			echo '<b>maxResults</b> is not valid.<br/>'; 
			die(); 		
 		}
		$maxResults = $testMatch;
		$testMatch = NULL;
	}
	if (isset($_REQUEST["outputFormat"]) & $_REQUEST["outputFormat"] != "") {
		$testMatch = $_REQUEST["outputFormat"];	
 		if (!($testMatch == 'json')){ 
			echo '<b>outputFormat</b> is not valid.<br/>'; 
			die(); 		
 		}
		$outputFormat = $testMatch;
		$testMatch = NULL;
	}
	if (isset($_REQUEST["searchEPSG"]) & $_REQUEST["searchEPSG"] != "") {
		$testMatch = $_REQUEST["searchEPSG"];	
 		if (!($testMatch == '31467' or $testMatch == '31466' or $testMatch == '31468' or $testMatch == '25832' or $testMatch == '4326')){ 
			echo '<b>searchEPSG</b> is not valid.<br/>'; 
			die(); 		
 		}
		$searchEPSG = $testMatch;
		$testMatch = NULL;
	}
	/*if (isset($_REQUEST["callback"]) & $_REQUEST["callback"] != "") {
		$testMatch = $_REQUEST["callback"];	
		$pattern = '/^jQuery\d+_\d+$/';
		if (!preg_match($pattern,$testMatch)){ 
 		//if (!($testMatch == '31467' or $testMatch == '31468' or $testMatch == '25832' or $testMatch == '4326')){ 
			echo 'callback: <b>'.$testMatch.'</b> is not valid.<br/>'; 
			die(); 		
 		}
		$callback = $testMatch;
		$testMatch = NULL;
	}*/
	//for debugging
	$callback = $_REQUEST["callback"];
	//get searchText as a parameter
	$searchText = $_REQUEST['searchText']; //TODO: filter for insecure texts
	$sstr = $searchText;
	$epsg = $searchEPSG;
	$searchThruWeb = true;
}


$e = new mb_notice("maxResults: ".$maxResults);
$con = pg_connect("host=".GEOMDB_HOST." port=".GEOMDB_PORT." dbname=".GEOMDB_NAME." user=".GEOMDB_USER." password=".GEOMDB_PASSWORD)
or die('Verbindungsaufbau fehlgeschlagen: ' . pg_last_error());



/*------------------------------ZOOMING FACTORS-----------------------------*/
//extensible: adjust zooming factor to object type (Straßen, Wege, Gemarkungen, Gemeinden, Kreise, straßen, straßen+hr) by introducing different factors and using them after the x and y coordinates of an object were retrieved from the database.

/******* conf ***********************************/
$factor = 1;
if (intval($epsg) == 4326) $factor = 0.00001; 

/******* gemeinde *********************/	
$bufferG = 5900*$factor;//1500*$factor; //ADAPTED
$arrayG = array();
$toleranceG = 100*$factor;
/******* strasse *********************/	
$bufferSTR = 500*$factor;
$arraySTR = array();
$toleranceSTR = 100*$factor;
/******* Strasse / Hsnr ****************/
$bufferSH = 100*$factor;
$arraySH = array();
$toleranceSH = 1000*$factor;

$resultStack = array();


$e = new mb_notice("gaz_geom_mobile was invoked with string:".$sstr);


/*---------------------------------------------------------------------------*/
/*--------------------------USER DATA ACQUISITION----------------------------*/
/*---------------------------------------------------------------------------*/

//if file not exists then create new one with respective header
//DOES NOT WORK DUE TO MISSING PERMISSIONS?
//attention server time incorret

//$targetFilePath = '/data/userData/ud'.date('Y_m_d').'.csv';
//if(!file_exists($targetFilePath)){
//	new mb_notice("FILE DOES NOT YET EXIST: ".$targetFilePath);
//	file_put_contents($targetFilePath, "SESSION_ID;SEARCH_REQUEST;DATE\n", LOCK_EX);
	//grant permisson to write
//	chmod($targetFilePath, 0644);	
//}
	
//redundant? (yes, just take the server time)
//date_default_timezone_set('Europe/Berlin');

//prepare entry for search query log
//$searchRequest = session_id().";".$sstr.";".date('d.m.Y H:i:s')."\n";

//write entry in search query log
//file_put_contents($targetFilePath, $searchRequest, FILE_APPEND | LOCK_EX);




/*---------------------------------------------------------------------------*/
/*------------------------------SQL INJECTION--------------------------------*/
/*---------------------------------------------------------------------------*/
//check for suspicious characters among search items and use prepared statments (cf. later)
if(preg_match("/[!\"§$%&\/\{\}\[\]=\?<>;:\'\+@]+/", $searchText, $sqlInjection)){
	new mb_notice("SQL Injection");
	//if suspicious characters were found, stopp script
	exit("no results");
}




/*---------------------------------------------------------------------------*/
/*-----------------------------SEARCH DATABASE-------------------------------*/
/*---------------------------------------------------------------------------*/


//split search text at whitespace or comma
$chunks = preg_split('/[\s+\,]/', $searchText);


//clean search items (i.e. convert to lower case and remove special characters)
for($i = 0; $i < count($chunks); $i++){
	$chunks[$i] = replaceChars(strtolower($chunks[$i]));
}

//counter for total number of results
$currentVal = 0;

//search the three tables for results
search($chunks, $resultStack, $bufferG, $bufferSTR, $bufferSH, $maxResults, $currentVal, $epsg);

	
//OPTIONAL: search for permuted order of search items
//$chunks needs to be permuted (perhaps via adaquate php permute array function?)
//if($maxResults > $currentVal)
//	search($chunks, $resultStack, $bufferG, $bufferSTR, $bufferSH, $maxResults, $currentVal, $epsg);

//in case no results could be found, return search advice
if(count($resultStack) == 0){
	
	$response = "KEINE SUCHERGEBNISSE VORHANDEN";		
	stack_it($resultStack, "", $response, null, null, null, null, null, null, null);
	
	$response = "Empfohlenes Suchformat:";		
	stack_it($resultStack, "", $response, null, null, null, null, null, null, null);
	
	$response = "Musterstraße 10, 65432 Musterstadt";		
	stack_it($resultStack, "", $response, null, null, null, null, null, null, null);
	
}
	
//prepare and echo the json result file
xml_output();



/*---------------------------------------------------------------------------*/
/*----------------------------FUNCTION: SEARCH ------------------------------*/
/*---------------------------------------------------------------------------*/
function search($chunks, &$stack, $buff1, $buff2, $buff3, $max, &$current, $epsg){
	
	//prepare the arguments for query
	$v = $chunks;
	$t = array();
	
	//counts the sql arguments
	$counter = 1;
	//string that finally contains all sql arguments ('$1 || % || $2 || ...')
	$arguments;
	
	//string that finally contains all search items 
	$complSearchText = "";
	
	//inspect all seach items
	foreach($chunks as $chunk){
		//for each seach item, say it's of type string (='s')
		array_push($t, 's');
		
		//add one argument to the sql string (which will be used in the query after 'LIKE')
		$arguments .= "$".$counter++." || '%' || ";
		
		//build one string with all search items (like $searchText at the beginning)
		$complSearchText .= $chunk;
	}
		
	//remove the last 3 characters ('|| '), since they are wast
	$arguments = substr($arguments, 0, -3);
	

	
	/*------------------------------SMALL TABLE----------------------------------*/
	if($current < $max){
		new mb_notice("SMALL TABLE");
						
		//prepare the SQL query to the database				
		$sql = "SELECT name, art,";
		$sql .= "ST_SRID(the_geom) AS srid, AsGML(the_geom) AS gml , ";
		$sql .= "(xmin(the_geom) - ".$buff1.") as minx, ";
		$sql .= "(ymin(the_geom) - ".$buff1.") as miny, ";
		$sql .= "(xmax(the_geom) + ".$buff1.") as maxx, ";
		$sql .= "(ymax(the_geom) + ".$buff1.") as maxy ";
		$sql .= "FROM small_table ";

		$sql .= "WHERE search_string LIKE '%' ||".$arguments;		
		$sql .= "LIMIT ".($max-$current);

		//transform coordinates if necessary
		if (isset($epsg) && is_numeric($epsg) && intval($epsg) != 25832) {
			$sql = str_replace("the_geom", "transform(the_geom,".$epsg.")", $sql);
		}
				
		
		//send SQL query
		$res = db_prep_query($sql, $v, $t);
		
		//output results to mapbender log file
		while($row = db_fetch_array($res)){			

			//prepare string that will be displayed
			$response = $row['name'];//." (".$row['art'].")";
			
			//push search result to the result stack
			stack_it($stack, "", $response, "pref", $row['srid'], $row['minx']/*-$buff1*/, $row['miny'], $row['maxx'], $row['maxy'], $row['gml']);

			//augment number of found addresses			
			$current++;
		}			
	}	
	
	
	/*------------------------------MEDIUM TABLE----------------------------------*/
	if($current < $max){
		new mb_notice("MEDIUM TABLE");

		//prepare the SQL query to the database
		$sql = "SELECT name, art, ";
		$sql .= "ST_SRID(the_geom) AS srid, AsGML(the_geom) AS gml , ";
		$sql .= "(xmin(the_geom) - ".$buff2.") as minx, ";
		$sql .= "(ymin(the_geom) - ".$buff2.") as miny, ";
		$sql .= "(xmax(the_geom) + ".$buff2.") as maxx, ";
		$sql .= "(ymax(the_geom) + ".$buff2.") as maxy ";
		$sql .= "FROM medium_table ";
		$sql .= "WHERE search_string LIKE ".$arguments;
		$sql .= "LIMIT ".($max-$current);

		//transform coordinates if necessary
		if (isset($epsg) && is_numeric($epsg) && intval($epsg) != 25832) {
			$sql = str_replace("the_geom", "transform(the_geom,".$epsg.")", $sql);
		}		
		
		//send SQL query
		$res = db_prep_query($sql, $v, $t);		
		
			
		//push all search results to results stack
		while($row = db_fetch_array($res)){
			
			//prepare string that will be displayed
			$response = $row['name'];
						
			//push search result to the result stack
			stack_it($stack, "", $response, "pref", $row['srid'], $row['minx'], $row['miny'], $row['maxx'], $row['maxy'], $row['gml']);
			
			//augment number of found addresses
			$current++;			
		}
	}
	
	
	/*------------------------------LARGE TABLE----------------------------------*/
	//search if still there is space for search suggestions 
	//and a number is contained in request
	if($current < $max && preg_match("/[0-9]+/", $complSearchText, $dummy) == 1){		
		new mb_notice("using LARGE");
		
		//prepare the SQL query to the database
		$sql = "SELECT strassenname, hausnummer, plz, post_ortsname, zusatz, ";
		$sql .= "ST_SRID(the_geom) AS srid, AsGML(the_geom) AS gml , ";
		$sql .= "(xmin(the_geom) - ".$buff3.") as minx, ";
		$sql .= "(ymin(the_geom) - ".$buff3.") as miny, ";
		$sql .= "(xmax(the_geom) + ".$buff3.") as maxx, ";
		$sql .= "(ymax(the_geom) + ".$buff3.") as maxy ";
		$sql .= "FROM large_table ";
		$sql .= "WHERE search_string LIKE ".$arguments;				
		$sql .= "LIMIT ".($max-$current);

	
		//transform coordinates if necessary
		if (isset($epsg) && is_numeric($epsg) && intval($epsg) != 25832) {
			$sql = str_replace("the_geom", "transform(the_geom,".$epsg.")", $sql);
		}
		
		//send SQL query
		$res = db_prep_query($sql, $v, $t);
					
		//output results to mapbender log file
		while($row = db_fetch_array($res)){
			
			//prepare string that will be displayed
			$response = $row['strassenname']." ".$row['hausnummer'].$row['zusatz'].", ".$row['plz']." ".$row['post_ortsname'];			
			
			//push search result to the result stack
			stack_it($stack, "", $response, "pref", $row['srid'], $row['minx'], $row['miny'], $row['maxx'], $row['maxy'], $row['gml']);

			//augment number of found addresses
			$current++;
		}
	}	
				
}
	
/*---------------------------------------------------------------------------*/
/*-------------------------FUNCTION: REPLACE CHARS --------------------------*/
/*---------------------------------------------------------------------------*/	
//notice that this function has to be kept consistent with the same-named function in the updateDatabase.sql script
function replaceChars($text){
	//
	$search = array( "ä",  "ö",  "ü",  "Ä",  "Ö",  "Ü", "tr.", "ß", "-", " ", "(", ")" );
	$repwith = array("ae", "oe", "ue", "AE", "OE", "UE", "tr", "ss", "", "", "", "");
	//remarks: to remove '.' is wrong since it is usefull for wippershain 114. straße, compromise: substitute 'tr.' by 'tr'
	//space is removed (actually redundant); 
	
	$text = str_replace($search, $repwith, $text);

	return $text;

}

/*---------------------------------------------------------------------------*/
/*----------------------FUNCTION: STACK SEARCH RESULTS-----------------------*/
/*---------------------------------------------------------------------------*/	
function stack_it(&$stack,$category,$showtitle,$prefix,$srid,$minx,$miny,$maxx,$maxy,$gml){
	global $searchThruWeb;
	if (!$searchThruWeb) {
	$doc = new DOMDocument();
	$member = $doc->createElement("member");
	$doc->appendChild($member);
	$member->setAttribute('id',$prefix.count($stack));
	$fc = $doc->createElement("FeatureCollection");
	$member->appendChild($fc);
	$fc->setAttribute("xmlns:gml","http://www.opengis.net/gml");
	$bb = $doc->createElement("boundedBy");
	$fc->appendChild($bb);
	$box = $doc->createElement("Box");
	$bb->appendChild($box);
	$box->setAttribute('srsName',"EPSG:".$srid);
	$c = $doc->createElement("coordinates");
	$box->appendChild($c);
	$coords = $doc->createTextNode($minx.",".$miny." ".$maxx.",".$maxy);
	$c->appendChild($coords);
	$fm = $doc->createElement("featureMember");
	$fc->appendChild($fm);
	$wp = $doc->createElement($category);
	$fm->appendChild($wp);
	$title = $doc->createElement("title");
	$wp->appendChild($title);
	$ttitle = $doc->createTextNode($showtitle);
	$title->appendChild($ttitle);
	$geom = $doc->createElement("the_geom");
	$wp->appendChild($geom);		
	$myNode = @simplexml_load_string($gml);
	$mySNode = dom_import_simplexml($myNode);
	$domNode = $doc->importNode($mySNode, true);
	$geom->appendChild($domNode);	 
	array_push($stack,$member);
	} else {
		//generate simple json objects as array elements
		$classJSON = new Mapbender_JSON;
		$returnJSON = new stdClass;
		$returnJSON->title = $showtitle;
		$returnJSON->category = $category;
		$returnJSON->minx = $minx;
		$returnJSON->miny = $miny;
		$returnJSON->maxx = $maxx;
		$returnJSON->maxy = $maxy;
		$returnJSON = $classJSON->encode($returnJSON);
		array_push($stack,$returnJSON);			
	}		
}

function null_output(){
	global $sstr, $arrayWP, $arrayG, $arrayK, $arraySH, $arraySTR, $arrayV;
	$doc = new DOMDocument('1.0');
	$doc->encoding = CHARSET;
	$result = $doc->createElement("result");
	$doc->appendChild($result);
	$ready = $doc->createElement('ready');
	$result->appendChild($ready);
	$tready = $doc->createTextNode("true");
	$ready->appendChild($tready);
	echo $doc->saveXML();
}

function null_json_output(){
	global $sstr, $arrayWP, $arrayG, $arrayK, $arraySH, $arraySTR, $arrayV;
	echo "{\"totalResultsCount\":0,\"geonames\":[]}";
}




/*---------------------------------------------------------------------------*/
/*-----------------------FUNCTION: PRODUCE JSON OUTPUT-----------------------*/
/*---------------------------------------------------------------------------*/	
function xml_output(){
	global $sstr, $arrayWP, $arrayG, $arrayK, $arraySH, $arraySTR, $arrayV, $resultStack, $searchThruWeb, $callback, $maxResults;
	//what does searchThruWeb mean?
	if (!$searchThruWeb) {
		$doc = new DOMDocument('1.0');
		$doc->encoding = CHARSET;
		$result = $doc->createElement("result");
		$doc->appendChild($result);

		for($i=0; $i<count($resultStack); $i++){
			$domNode = $doc->importNode($resultStack[$i], true); 
			$result->appendChild($domNode);
		}
		
		$ready = $doc->createElement('ready');
		$result->appendChild($ready);
		$tready = $doc->createTextNode("true");
		$ready->appendChild($tready);
		//if ($searchThruWeb) {
		//	header("Content-type: application/xhtml+xml; charset=UTF-8");
		//}
		echo $doc->saveXML();
	} else {
		//generate json object with gml content
		$classJSON = new Mapbender_JSON;
		$returnJSON = new stdClass;
		
		$countGeonames = 0;
		$returnJSON->totalResultsCount = 0;

		$resultStack = array_unique($resultStack);
		$resultStack = array_values($resultStack); //necessary in order to avoid null in resultStack
		
		
		//turn stacked search suggestions into json object
		for($i=0; $i<count($resultStack); $i++){
				if ($countGeonames >= $maxResults) {
					break;
				}
			$returnJSON->geonames[$countGeonames] = $classJSON->decode($resultStack[$i]);
			$countGeonames++;
		}

		
		$returnJSON->totalResultsCount = $countGeonames;
		if ($returnJSON->totalResultsCount == 0) {
			$returnJSON->geonames = array();
		}
		if (isset($callback) && $callback != '') {
			 $returnJSON = $callback."(".$classJSON->encode($returnJSON).")";
		} else {
			  $returnJSON = $classJSON->encode($returnJSON);
		}

	       	echo $returnJSON;	
			
	}	
}

?>

