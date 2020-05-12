
<?php
//
require_once(dirname(__FILE__)."/../../core/globalSettings.php");
//require_once(dirname(__FILE__)."/../../conf/geoportal.conf");
require_once(dirname(__FILE__)."/../classes/class_connector.php"); 
//Script to read Results from CSW 2.0.2 Interfaces from mapbender geoportal
//example requests:
//GetRecords with textfilter


/*gp_csw

CREATE TABLE gp_csw (
    csw_id integer,
    csw_name text,
    fkey_cat_id integer,
    csw_p integer, --start page
    csw_h integer --results per page
);
alter table gp_csw add column hierachylevel char(50);
--insert into gp_csw (csw_id, csw_name, fkey_cat_id, csw_p, csw_h, hierachylevel) values (1,'GDK-DE Datensätze',3,1,10,'dataset');
--insert into gp_csw (csw_id, csw_name, fkey_cat_id, csw_p, csw_h, hierachylevel) values (2,'GDK-DE Dienste',3,1,10,'service');
--http://ims7.bkg.bund.de/geonetwork/srv/en/csw?Request=GetCapabilities&Service=CSW&VERSION=2.0.2
ALTER TABLE mapbender.gp_csw OWNER TO postgres;

select param_value from cat_op_conf where fk_cat_id=2 and param_type='getrecords';

select param_value from cat_op_conf where fk_cat_id=2 and param_type='getrecordsbyid';*/
//$resdir = TMPDIR;
$resdir = "/data/mapbender/http/tmp/";
$con = db_connect(DBSERVER,OWNER,PW);
db_select_db(DB,$con);

function logit($text){
	 	if($h = fopen(TMPDIR."/opensearch_log.txt","a")){
					$content = $text .chr(13).chr(10);
					if(!fwrite($h,$content)){
						#exit;
					}
					fclose($h);
				}	 	
	 }

#test if script was requested over http or from cli
#if it came from cli, use output to tmp folder - > typo3 would find it and will show it in template, there should be an identifier from the gaz.php script which controls the different search moduls
#if it came as http request it should generate its own html window
#Maybe problematic: if requested from command-line, how would mapbender get the content? Should be tested.


#check if requested as cli
if (isset($argv[0])&isset($argv[1])){
	echo "\nthe script was invoked from commandline\n";
	$from_cli=true;
	#do something with the searchstring if needed
	#from cli no pagenumber will be given. Therefor everytime page number 1 will be requested
	$request_p = 1;
	$_REQUEST["q"] = $argv[2];//.$argv[3];//$searchPortaluFilter = $argv[3];
	$cli_id = $argv[1];
	echo "\nID: ".$argv[1]."\n";
	echo "\nSearchstring: ".$argv[2]."\n";
	}
	else
	{
		echo "<html><body>";
		echo "\n<br>no commandline args set!\n<br>";
		$from_cli=false;
	}
#When script was not invoked from cli it should have been invoked per http - check the params
if (!$from_cli){
	#***Validation of GET Parameters
	#handle errors
	//make html frame
	
	if(!isset($_REQUEST["q"]) ) {
		echo "No search string found! Please send a query!<br>";
		die();
		}
	if(!isset($_REQUEST["p"]) ) {
		$request_p = 1;
		}
	else 
		{
		$request_p = $_REQUEST["p"];
		}

	if(!isset($_REQUEST["request_id"]) or $_REQUEST["request_id"]=='') {
		echo "<br> request_id is not set <br>";
		$requeststring="&request_id=from_http";
		$cli_id="from_http";
		}
	else
		{
		echo "<br>request_id is set<br>";
		$cli_id=$_REQUEST["request_id"];
		}
	echo "<br>Search string: <b>".$_REQUEST["q"]."</b> will be send<br>";
}

//convert the the opensearch filter to ogc filter encoding

//extract query string - explode by +
$queryString  = $_REQUEST["q"];
$queryStringParts = explode("+",$queryString);

//$e = new mb_exception($queryString);
//extract single elements into variables
//first entry is allways the query string for any text
$queryText = $queryStringParts[0];

//extract the other elements
for ($i=1;$i < count($queryStringParts);$i++) {
	//check the elements
	$queryParam = explode(":",$queryStringParts[$i]);
	switch ($queryParam[0]) {
		case "x1":
			$x1 = $queryParam[1];
		break;
		case "x2":
			$x2 = $queryParam[1];
		break;	
		case "y1":
			$y1 = $queryParam[1];
		break;	
		case "y2":
			$y2 = $queryParam[1];
		break;
		case "coord":
			$coord = $queryParam[1];
		break;
		case "ranking":
			$ranking = $queryParam[1];
		break;	
	}	
}
//build bbox if all infos are given
$e = new mb_notice("q=: ".$_REQUEST["q"]);
$e = new mb_notice("ranking: ".$ranking);
$e = new mb_notice("coord: ".$coord);
//$e = new mb_exception("geoportal/mod_readCSWResults.php queryText: ".$queryText);
if (isset($x1) && isset($x2) && isset($y1) && isset($y2) && isset($coord)) {
/*	<BBOX>
            <PropertyName>ows:BoundingBox</PropertyName>
            <gml:Envelope>
              <gml:lowerCorner>-180 -90</gml:lowerCorner>
              <gml:upperCorner>180 90</gml:upperCorner>
            </gml:Envelope>
          </BBOX>*/
/*<ogc:Intersects> <ogc:PropertyName>ows:BoundingBox</ogc:PropertyName>
<gml:Envelope>
<gml:lowerCorner>14.05 46.46</gml:lowerCorner>
<gml:upperCorner>17.24 48.42</gml:upperCorner>
</gml:Envelope>
</ogc:Intersects>*/
	$existsSpatialFilter = true;
	//$spatialFilter = "<BBOX>";
	switch ($coord) {
		case "intersect":
			$spatialFilter .= "<ogc:BBOX>";
		break;
		case "inside":
			$spatialFilter .= "<ogc:Within>";
		break;
		case "outside":
			$spatialFilter .= "<ogc:Disjoint>";
		break;
		default:
			$spatialFilter .= "<ogc:BBOX>";
	}
	$spatialFilter .= "<ogc:PropertyName>BoundingBox</ogc:PropertyName>";
	$spatialFilter .= "<gml:Envelope>";
	$spatialFilter .= "<gml:lowerCorner>".$x1." ".$y1."</gml:lowerCorner>";
	$spatialFilter .= "<gml:upperCorner>".$x2." ".$y2."</gml:upperCorner>";
	$spatialFilter .= "</gml:Envelope>";
	switch ($coord) {
		case "intersect":
			$spatialFilter .= "</ogc:BBOX>";
		break;
		case "inside":
			$spatialFilter .= "</ogc:Within>";
		break;
		case "outside":
			$spatialFilter .= "</ogc:Disjoint>";
		break;
		default:
			$spatialFilter .= "/<ogc:BBOX>";
	}
	//$spatialFilter .= "</BBOX>";


} else {
	$existsSpatialFilter = false;
}
//set kind of filter

//debug output
$e = new mb_exception($spatialFilter);


#get the information out of the mapbender-db
#get urls to search interfaces (csw):
$sql_csw = "SELECT * from gp_csw ORDER BY csw_id";
#do db select
$res_csw = db_query($sql_csw);
#initialize count of search interfaces
$cnt_csw = 0;
#initialize result array
$csw_list=array(array());
#fill result array
while($row_csw = db_fetch_array($res_csw)){
	$csw_list[$cnt_csw]['id'] = $row_csw["csw_id"];
	$csw_list[$cnt_csw]['name'] = $row_csw["csw_name"];
	echo "csw_name=".$row_csw["csw_name"];
	$csw_list[$cnt_csw]['hierachylevel'] = $row_csw["hierachylevel"];
	$csw_list[$cnt_csw]['fkey_cat_id'] = $row_csw["fkey_cat_id"];
	//echo "<br>CAT ID from DB: ".$row_csw["fkey_cat_id"]."<br>";
	//get urls for getrecords and getrecordbyid from table cat
        $v = $row_csw["fkey_cat_id"];
	$t = 'i';
	$sql_gr = "select param_value, param_name from cat_op_conf where fk_cat_id = $1 and param_type = 'getrecords'";
	$res_gr = db_prep_query($sql_gr, $v, $t);
	//look after the values preference get/post/post_xml
	while ($row_gr = db_fetch_array($res_gr)) {
		switch ($row_gr['param_name']) {
			case "get" :
				$csw_list[$cnt_csw] ['getrecordsurl_param_name'] = "get";
				if (isset($row_gr['param_value']) || $row_gr['param_value'] != '') {
					$csw_list[$cnt_csw] ['getrecordsurl'] = $row_gr['param_value'];
					break 2;
				}
			break 1;	
			case "post" :
				$csw_list[$cnt_csw] ['getrecordsurl_param_name'] = "post";
				if (isset($row_gr['param_value']) || $row_gr['param_value'] != '') {
					$csw_list[$cnt_csw] ['getrecordsurl'] = $row_gr['param_value'];
					break 2;
				}
			break 1;
			case "post_xml" :
				$csw_list[$cnt_csw] ['getrecordsurl_param_name'] = "post_xml";
				if (isset($row_gr['param_value']) || $row_gr['param_value'] != '' ) {
					$csw_list[$cnt_csw] ['getrecordsurl'] = $row_gr['param_value'];
					break 2;
				}
			break 1;
		}
		
	}
	$e = new mb_notice("<br>getrecords param type: ".$csw_list[$cnt_csw]['getrecordsurl_param_name']."<br>");
	$csw_list[$cnt_csw] ['getrecordsurl'] = rtrim($csw_list[$cnt_csw] ['getrecordsurl'], "?");
	$e = new mb_notice("mod_readCSWResults.php: getrecordsurl: ".$csw_list[$cnt_csw]['getrecordsurl']);
	$sql_grbi = "select param_value from cat_op_conf where fk_cat_id = $1 and param_type = 'getrecordbyid' and param_name='get'";
	$res_grbi = db_prep_query($sql_grbi, $v, $t);
        $row_grbi = db_fetch_array($res_grbi);
	$csw_list[$cnt_csw] ['getrecordbyidurl'] = $row_grbi['param_value'];
	//Delete question marks from end of url
	$csw_list[$cnt_csw] ['getrecordbyidurl'] = rtrim($csw_list[$cnt_csw] ['getrecordbyidurl'], "?");
	$e = new mb_notice("mod_readCSWResults.php: getrecordbyidurl: ".$csw_list[$cnt_csw]['getrecordbyidurl']);
	$csw_list[$cnt_csw] ['h'] = $row_csw["csw_h"];
	$csw_list[$cnt_csw] ['p'] = $row_csw["csw_p"];
	$cnt_csw++;
}


//debug output
if (!$from_cli) {
	echo "<br>Count of registrated OpenSearch Interfaces: ".count($csw_list)."<br>";
}

#get command_line args
#$cli_id="1234567-1234567-1234567-test";

#+++++++++++++++++++++++++++


#if the request came from http and the first request came from a commandline - add a get parameter to the following requests and change set the $cli_id 
#if(!isset($_REQUEST["request_id"]) ) {
#		$cli_id=$_REQUEST["request_id"];
#		$requeststring="&request_id=".$cli_id;
	#	}
	#else
	#	{
	#	$requeststring="";
	#	}


#+++++++++++++++++++++++++++++++++

#string to add to further requests:
$requeststring="&request_id=".$cli_id;
#***write xml with list of opensearch catalogs
#$from_cli=true;# for testing only
#if ($from_cli) {
	#write out xml 'is really no xml!' with opensearch-catalogs
if ($from_cli) {
echo "\nFolder to write to: ".$resdir."\n";
echo "\nFile to open: ".$resdir."/".$cli_id."_os.xml\n";
}
	if($os_catalogs_file_handle = fopen($resdir."/".$cli_id."_os.xml","w")){
		fwrite($os_catalogs_file_handle,"<interfaces>\n");
		for ($i_c = 0; $i_c < count($csw_list); $i_c++) {
			$content = $csw_list[$i_c] ['name'];
			fwrite($os_catalogs_file_handle,"<opensearchinterface>");
			fwrite($os_catalogs_file_handle,$content);
			fwrite($os_catalogs_file_handle,"</opensearchinterface>\n");
		}
		fwrite($os_catalogs_file_handle,"</interfaces>\n");
		fclose($os_catalogs_file_handle);
	}	
	else
	{
		if ($from_cli) {
			echo "\nCouldn't open file!\n";
		}
	}
#}
#$from_cli=false;# for testing only
#***
#***loop for things to do for each registrated search interface - only if the search should be done in all interfaces!
#use only one catalogue if a further page is requested
$start_cat=0;
$end_cat=count($csw_list);
$cat=$_REQUEST["cat"];
if (isset($cat)){
$start_cat=(int)$cat;
$end_cat=(int)$cat+1;
}
for ($i_si = $start_cat; $i_si < $end_cat ; $i_si++) {
	//$i_si = 0;
	//echo "<br>".$csw_list[$i_si]['getrecordsurl']."<br>";
	$openSearchUrl[$i_si]=$csw_list[$i_si] ['getrecordsurl'];
	$openSearchWrapperDetail="mod_readCSWResultsDetail.php";
	//define the right request for the page
	//calculate the startindex for the requested pagenumber
 	$startIndex = ((real)$csw_list[$i_si] ["h"]*((integer)$request_p - 1)) + 1;
	//$number_of_pages=ceil((real)$n_results/(real)$csw_list[$i_si] ['h']);
	$getRecords = '<csw:GetRecords xmlns:csw="http://www.opengis.net/cat/csw/2.0.2"';
	$getRecords .= '            xmlns:gmd="http://www.isotc211.org/2005/gmd"';
	$getRecords .= '            xmlns:ogc="http://www.opengis.net/ogc"';
	$getRecords .= '            xmlns:gml="http://www.opengis.net/gml"';
	$getRecords .= '           maxRecords="'.$csw_list[$i_si] ["h"].'"';
	$getRecords .= '           outputFormat="application/xml"';
	$getRecords .= '           outputSchema="http://www.isotc211.org/2005/gmd"';
	$getRecords .= '           resultType="results"';
	//sortby
	$getRecords .= '           sortby="Title:A"';
	$getRecords .= '           service="CSW"';
	$getRecords .= '          startPosition="'.$startIndex.'"'; //set this for paging - maybe it must be calculated from max count
	$getRecords .= '          version="2.0.2">';

	$getRecords .= '          <csw:Query typeNames="csw:Record">';
	$getRecords .= '          <csw:ElementSetName>summary</csw:ElementSetName>';
	$getRecords .= '          <csw:Constraint version="1.1.0">';

	$getRecords .= '          <ogc:Filter>';
	$getRecords .= '          <ogc:And>';
	if ($queryText != '*') {
		$getRecords .= '          <ogc:PropertyIsLike wildCard="%" singleChar="_" escape="">';
		$getRecords .= '          <ogc:PropertyName>AnyText</ogc:PropertyName>';
		$getRecords .= '          <ogc:Literal>%'.$queryText.'%</ogc:Literal>';
		$getRecords .= '          </ogc:PropertyIsLike>';
	}
	$type = trim($csw_list[$i_si]['hierachylevel']);
	switch ($type) {
    		case ($type=='dataset' || $type=='series' || $type=='service' || $type=='nonGeographicDataset' || $type=='application'):


			$getRecords .= '          <ogc:PropertyIsEqualTo>';
			$getRecords .= '          <ogc:PropertyName>Type</ogc:PropertyName>';
			$getRecords .= '          <ogc:Literal>'.$type.'</ogc:Literal>';
			$getRecords .= '          </ogc:PropertyIsEqualTo>';
		break;
		case 'dataset/series':
			$getRecords .= '          <ogc:Or>';
			$getRecords .= '          <ogc:PropertyIsEqualTo>';
			$getRecords .= '          <ogc:PropertyName>Type</ogc:PropertyName>';
			$getRecords .= '          <ogc:Literal>dataset</ogc:Literal>';
			$getRecords .= '          </ogc:PropertyIsEqualTo>';
			$getRecords .= '          <ogc:PropertyIsEqualTo>';
			$getRecords .= '          <ogc:PropertyName>Type</ogc:PropertyName>';
			$getRecords .= '          <ogc:Literal>series</ogc:Literal>';
			$getRecords .= '          </ogc:PropertyIsEqualTo>';
			$getRecords .= '          </ogc:Or>';
		break;
		default:
	}

/*<?xml version="1.0" encoding="UTF-8"?>
<csw:GetRecords
 xmlns:csw="http://www.opengis.net/cat/csw/2.0.2" 
 xmlns:ogc="http://www.opengis.net/ogc"
 xmlns:gml="http://www.opengis.net/gml"
 service="CSW" 
 version="2.0.2" 
 resultType="results" 
 outputSchema="http://www.isotc211.org/2005/gmd">
  <csw:Query typeNames="gmd:MD_Metadata">
    <csw:ElementSetName>brief</csw:ElementSetName>
    <csw:Constraint version="1.1.0">
      <ogc:Filter>

<ogc:And>
<ogc:PropertyIsLike wildCard="%" singleChar="_" escape="">
<ogc:PropertyName>AnyText</ogc:PropertyName>
<ogc:Literal>Wald</ogc:Literal>
</ogc:PropertyIsLike>

      <ogc:BBOX>
        <ogc:PropertyName>BoundingBox</ogc:PropertyName>
          <gml:Envelope>
            <gml:lowerCorner>5 49</gml:lowerCorner>
            <gml:upperCorner>9 51</gml:upperCorner>
          </gml:Envelope>
      </ogc:BBOX>
</ogc:And>
      </ogc:Filter>
    </csw:Constraint>
  </csw:Query>
</csw:GetRecords>
*/
	//$getRecords .= '          <Literal>dataset</Literal>';
	//deactivate spatialFilter cause there is a bug in geonetwork 2.6.4!
	//http://trac.osgeo.org/geonetwork/ticket/585
	if ($existsSpatialFilter) {
		$getRecords .= $spatialFilter;
	}


	$getRecords .= '          </ogc:And>';
	$getRecords .= '          </ogc:Filter>';

	$getRecords .= '          </csw:Constraint>';          
	$getRecords .= '          </csw:Query>';
	$getRecords .= '</csw:GetRecords>';

//echo "<br>REQUEST for results<br>".htmlentities($getRecords)."<br>";
$e = new mb_notice($getRecords);
//for CSW define POST REQUEST for getrecords
	$cswInterfaceObject = new connector();
	$cswInterfaceObject->set('httpType','POST');
	$postData = $getRecords;
	$postData = stripslashes($postData);
	$dataXMLObject = new SimpleXMLElement($postData);
	$postData = $dataXMLObject->asXML();
	$cswInterfaceObject->set('curlSendCustomHeaders',true);
	$cswInterfaceObject->set('httpPostData', $postData);
	$cswInterfaceObject->set('httpContentType','text/xml');
	//extent url when needed
	$openSearchUrlSearch[$i_si] = $openSearchUrl[$i_si];
	echo "<br><br>".$openSearchUrlSearch[$i_si]."<br>";

	//echo $openSearchUrl[$i_si]."<br>";
	$cswInterfaceObject->load($openSearchUrl[$i_si]);
	#echo "<br>Results: ".htmlentities($cswInterfaceObject->file)."<br><br>";
	$openSearchResult = $cswInterfaceObject->file;
	$openSearchUrlDetail[$i_si]=$csw_list[$i_si] ['getrecordbyidurl']."?";
	$e = new mb_notice("mod_readCSWResults.php: detailurl: ".$openSearchUrlDetail[$i_si]);
	//get resultlists
	//$url=$openSearchUrlSearch[$i_si]."q=".$queryText.$csw_list[$i_si] ['standardfilter']."&h=".$csw_list[$i_si] ['h']."&p=".$request_p;
	$url = $openSearchUrl[$i_si];
	if (!$from_cli) {	
	echo "<br> url: ".$url."<br>";
	}
	else
	{
	
	}
	#save resultset in temporary folder identified by sessionid, katalog_id and page_id! Now there would be more tmp files than before! 
	#this has to be done in order to give the information to typo3
	#**************to be done!************************************
	//if ($existsSpatialFilter) {
	//	$e = new mb_exception('external xml : '.$openSearchResult);
	//}

	$e = new mb_notice('external xml : '.$openSearchResult);
	#parse result to simplexml 
	$openSearchXml =  new SimpleXMLElement($openSearchResult);	
	//$openSearchXml = ($openSearchResult);
	#read out array with docids and plugids
	#read out number of results - there are two ways: with namespaces and without!:
	$n_results = $openSearchXml->xpath('/csw:GetRecordsResponse/csw:SearchResults/@numberOfRecordsMatched');
	$n_results = $n_results[0];
	//if ($csw_list[$i_si] ['version']=='2') {
	//	$opensearchElements=$openSearchXml->channel->children('http://a9.com/-/spec/opensearch/1.1/');
	//	$n_results=$opensearchElements->totalResults;
	//	
	//}
	if (!isset($n_results)) {
		$n_results = 0;
	}	
	if ($from_cli) {	
		logit( "Number of Results in Catalogue ".$i_si.": ".$n_results."\n");
	}
	//calculate number of needed pages to show all results:
	$number_of_pages=ceil((real)$n_results/(real)$csw_list[$i_si] ['h']);
	
	#do some debugging output
	#var_dump($openSearchXml);
	#show total results


#do a html output for showing results of the different opensearch catalogues
//if (!$from_cli) {
	echo "<b>".$n_results."</b> Ergebnisse in Katalog <b>".$csw_list[$i_si] ['name']."</b><br><br>";
	#show Pagenumbers
	if ((int)$request_p>1) {
	echo "<a href=\"mod_readCSWResults.php?q=".$_REQUEST['q']."&p=".(string)((int)$request_p-1)."&cat=".$i_si.$requeststring."\"> Vorige Seite </a> ";
	}

	echo "Seite: <b>".$request_p."</b> von <b>".$number_of_pages."</b>";
	
	if ((int)$request_p < (int)$number_of_pages) {
	echo " <a href=\"mod_readCSWResults.php?q=".$_REQUEST['q']."&p=".(string)((int)$request_p+1)."&cat=".$i_si.$requeststring."\"> Nächste Seite </a>";
	}
	
	echo "<br><br>";
//}
//else
//{
#echo "Keine Blättermöglichkeit in CLI\n";
//}
	
$from_cli=true; //- do this everytime
if ($from_cli) { #do these things if the request was done from the commandline - it is done by the central search function
	#generate the output for each page! Like: xyz_os1_1_10.xml = this means: searchid_os#catalogid_#page_#totalpages.xml
	#open the specific file for writing
	#number of the actual catalog:
	$catalog_number=(int)$i_si+1;
	logit($resdir."/".$cli_id."_os".$catalog_number."_".$request_p.".xml");
	if($os_catalogs_file_handle = fopen($resdir."/".$cli_id."_os".$catalog_number."_".$request_p.".xml","w")){
		fwrite($os_catalogs_file_handle,"<resultlist>\n");
		#logit("<resultlist>\n");
		fwrite($os_catalogs_file_handle,"<querystring>".urlencode($queryString)."</querystring>\n");
		#logit("<querystring>".urlencode($queryText)."</querystring>\n");
		fwrite($os_catalogs_file_handle,"<totalresults>".$n_results."</totalresults>\n");
		#logit("<totalresults>".$n_results."</totalresults>\n");
		fwrite($os_catalogs_file_handle,"<npages>".$number_of_pages."</npages>\n");
		#logit("<npages>".$number_of_pages."</npages>\n");
		fwrite($os_catalogs_file_handle,"<nresults>".(int)$csw_list[$i_si] ['h']."</nresults>\n");
		//write rssurl only, if opensearch version not equal to 1		
		//if ($csw_list[$i_si] ['version']=='1') {
			fwrite($os_catalogs_file_handle,"<rssurl></rssurl>\n");
		//}
		//else
		//{
		//	fwrite($os_catalogs_file_handle,"<rssurl>".urlencode($openSearchXml->channel->link)."</rssurl>\n");
		//}
		#logit("<nresults>".(int)$csw_list[$i_si] ['h']."</nresults>\n");
		#loop for single results in first list
		#problematic: if less than 10 results are in the list, let the loop run only nresults times
			
		if ($n_results < (int)$csw_list[$i_si] ['h']) {
			$upperLimit = $n_results;
		}
		else
		{
			$upperLimit = (int)$csw_list[$i_si] ['h'];
		}
		//parse all gmd:MD_Metadata elements as single xml obbjects
		/*foreach($openSearchXml->xpath('//gmd:MD_Metadata') as $gmd_MD_Metadata){
    			// 'derefence' into a seperate xml tree for performance
    			$gmd_MD_Metadata = simplexml_load_string($gmd_MD_Metadata->asXML());
    			$uuid = $gmd_MD_Metadata->xpath('//gmd:fileIdentifier/gco:CharacterString');
			echo "<br>uuid:".$uuid."<br>";
		} */
		$openSearchXml->registerXPathNamespace('csw', 'http://www.opengis.net/cat/csw/2.0.2');
		$openSearchXml->registerXPathNamespace('gmd', 'http://www.isotc211.org/2005/gmd');
		$openSearchXml->registerXPathNamespace('gco', 'http://www.isotc211.org/2005/gco');
		$openSearchXml->registerXPathNamespace('srv', 'http://www.isotc211.org/2005/srv');
 		for ($i=0; $i < $upperLimit; $i++) {
			//initialize/unset the variables which have been read from iso 19139xml files 
			unset($accessUrl);
			unset($uuid);
			unset($bbox);
			unset($graphicURL);
			unset($isViewService);
			//$uuid = $openSearchXml->xpath('/csw:GetRecordsResponse/gmd:MD_Metadata['.$i.']/gmd:fileIdentifier/gco:CharacterString');
			$index = $i + 1 ;
			//Check for type of record
			$typeOfRecord = $openSearchXml->xpath('/csw:GetRecordsResponse/csw:SearchResults/gmd:MD_Metadata['.$index.']/gmd:hierarchyLevel/gmd:MD_ScopeCode/@codeListValue');
			$typeOfRecord = $typeOfRecord[0];
			#echo "<br><br>".$typeOfRecord."<br>"; 
			$uuid = $openSearchXml->xpath('/csw:GetRecordsResponse/csw:SearchResults/gmd:MD_Metadata['.$index.']/gmd:fileIdentifier/gco:CharacterString');
			$uuid = $uuid[0];
			$dateStamp = $openSearchXml->xpath('/csw:GetRecordsResponse/csw:SearchResults/gmd:MD_Metadata['.$index.']/gmd:dateStamp/*');
			//or date time - check and extract this information
			$dateStamp = substr($dateStamp[0],0,10);
			$title = $openSearchXml->xpath('/csw:GetRecordsResponse/csw:SearchResults/gmd:MD_Metadata['.$index.']/gmd:identificationInfo/*/gmd:citation/gmd:CI_Citation/gmd:title/gco:CharacterString');
			$orgaName = $openSearchXml->xpath('/csw:GetRecordsResponse/csw:SearchResults/gmd:MD_Metadata['.$index.']/gmd:identificationInfo/*/gmd:pointOfContact/gmd:CI_ResponsibleParty/gmd:organisationName/gco:CharacterString');
			$abstract = $openSearchXml->xpath('/csw:GetRecordsResponse/csw:SearchResults/gmd:MD_Metadata['.$index.']/gmd:identificationInfo/*/gmd:abstract/gco:CharacterString');
			$accessUrl = $openSearchXml->xpath('/csw:GetRecordsResponse/csw:SearchResults/gmd:MD_Metadata['.$index.']/gmd:distributionInfo/gmd:MD_Distribution/gmd:transferOptions/gmd:MD_DigitalTransferOptions/gmd:onLine/gmd:CI_OnlineResource/gmd:linkage/gmd:URL');
			
			echo "<br>General first accessUrl: ".$accessUrl[0]."<br>";



			//generate link to original csw
			$cswSearchUrlDetail = $openSearchUrlDetail[$i_si];
			$cswSearchUrlDetail = $cswSearchUrlDetail."request=GetRecordById&service=CSW&version=2.0.2&Id=".$uuid."&ElementSetName=full&OUTPUTSCHEMA=http://www.isotc211.org/2005/gmd";
			$e = new mb_notice("mod_readCSWResults.php: URL for calling full iso19139 service record over csw api: ".$cswSearchUrlDetail);


			//extract title from iso19139 record
			switch ($typeOfRecord) {
    				case 'dataset':
					$graphicURL = $openSearchXml->xpath('/csw:GetRecordsResponse/csw:SearchResults/gmd:MD_Metadata['.$index.']/gmd:identificationInfo/gmd:MD_DataIdentification/gmd:graphicOverview/gmd:MD_BrowseGraphic/gmd:fileName/gco:CharacterString');
					$bbox = $openSearchXml->xpath('/csw:GetRecordsResponse/csw:SearchResults/gmd:MD_Metadata['.$index.']/gmd:identificationInfo/*/*/gmd:EX_Extent/gmd:geographicElement/gmd:EX_GeographicBoundingBox/*/gco:Decimal');
        				break;
				case 'series':
					$graphicURL = $openSearchXml->xpath('/csw:GetRecordsResponse/csw:SearchResults/gmd:MD_Metadata['.$index.']/gmd:identificationInfo/gmd:MD_DataIdentification/gmd:graphicOverview/gmd:MD_BrowseGraphic/gmd:fileName/gco:CharacterString');
					$bbox = $openSearchXml->xpath('/csw:GetRecordsResponse/csw:SearchResults/gmd:MD_Metadata['.$index.']/gmd:identificationInfo/*/*/gmd:EX_Extent/gmd:geographicElement/gmd:EX_GeographicBoundingBox/*/gco:Decimal');
					break;
				case 'application':
					$graphicURL = $openSearchXml->xpath('/csw:GetRecordsResponse/csw:SearchResults/gmd:MD_Metadata['.$index.']/gmd:identificationInfo/gmd:MD_DataIdentification/gmd:graphicOverview/gmd:MD_BrowseGraphic/gmd:fileName/gco:CharacterString');
					$bbox = $openSearchXml->xpath('/csw:GetRecordsResponse/csw:SearchResults/gmd:MD_Metadata['.$index.']/gmd:identificationInfo/*/*/gmd:EX_Extent/gmd:geographicElement/gmd:EX_GeographicBoundingBox/*/gco:Decimal');
					break;
    				case 'service':

					//Graphic URL: <gmd:graphicOverview> <gmd:MD_BrowseGraphic> <gmd:fileName> <gco:CharacterString>http://www.geoportal.rlp.de/mapbender/geoportal/preview/24641_layer_map_preview.jpg</gco:CharacterString> </gmd:fileName> </gmd:MD_BrowseGraphic> </gmd:graphicOverview>
					$graphicURL = $openSearchXml->xpath('/csw:GetRecordsResponse/csw:SearchResults/gmd:MD_Metadata['.$index.']/gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:graphicOverview/gmd:MD_BrowseGraphic/gmd:fileName/gco:CharacterString');
					$orgaName = $openSearchXml->xpath('/csw:GetRecordsResponse/csw:SearchResults/gmd:MD_Metadata['.$index.']/gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:pointOfContact/gmd:CI_ResponsibleParty/gmd:organisationName/gco:CharacterString');
					$title = $openSearchXml->xpath('/csw:GetRecordsResponse/csw:SearchResults/gmd:MD_Metadata['.$index.']/gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:citation/gmd:CI_Citation/gmd:title/gco:CharacterString');
					$abstract = $openSearchXml->xpath('/csw:GetRecordsResponse/csw:SearchResults/gmd:MD_Metadata['.$index.']/gmd:identificationInfo/srv:SV_ServiceIdentification/gmd:abstract/gco:CharacterString');
					$bbox = $openSearchXml->xpath('/csw:GetRecordsResponse/csw:SearchResults/gmd:MD_Metadata['.$index.']/gmd:identificationInfo/*/*/gmd:EX_Extent/gmd:geographicElement/gmd:EX_GeographicBoundingBox/*/gco:Decimal');
					$typeOfService = $openSearchXml->xpath('/csw:GetRecordsResponse/csw:SearchResults/gmd:MD_Metadata['.$index.']/gmd:identificationInfo/srv:SV_ServiceIdentification/srv:serviceType/gco:LocalName');
					$typeOfService = $typeOfService[0];
					///gmd:MD_Metadata/gmd:identificationInfo/*/*/gmd:EX_Extent/gmd:geographicElement/gmd:EX_GeographicBoundingBox/*/gco:Decimal
					//if service is found, the whole record must be called to find the access url to this service - :-(

					#create connector object
					$cswSearchDetailObject = new connector($cswSearchUrlDetail);
					#get results
					$cswSearchDetail = $cswSearchDetailObject->file;
					//echo "<br>".htmlentities($cswSearchDetail)."<br>";
					//solve problem with xlink namespace for href attributes:
					$cswSearchDetail = str_replace('xlink:href', 'xlinkhref', $cswSearchDetail);
					#http://forums.devshed.com/php-development-5/simplexml-namespace-attributes-problem-452278.html
					#http://www.leftontheweb.com/message/A_small_SimpleXML_gotcha_with_namespaces
					#$openSearchDetail = str_replace('xmlns=', 'ns=', $openSearchDetail);
					$cswSearchDetailXML = simplexml_load_string($cswSearchDetail);
					#extract objects to iso19139 elements
					$cswSearchDetailXML->registerXPathNamespace("csw", "http://www.opengis.net/cat/csw/2.0.2");
					$cswSearchDetailXML->registerXPathNamespace("gml", "http://www.opengis.net/gml");
					$cswSearchDetailXML->registerXPathNamespace("gco", "http://www.isotc211.org/2005/gco");
					$cswSearchDetailXML->registerXPathNamespace("gmd", "http://www.isotc211.org/2005/gmd");
					$cswSearchDetailXML->registerXPathNamespace("gts", "http://www.isotc211.org/2005/gts");
					$cswSearchDetailXML->registerXPathNamespace("srv", "http://www.isotc211.org/2005/srv");
					$cswSearchDetailXML->registerXPathNamespace("xlink", "http://www.w3.org/1999/xlink");	
					//first read the inspire kind of impelmentaion of the access to capabilities documents
					$accessUrl = $cswSearchDetailXML->xpath('/csw:GetRecordByIdResponse/gmd:MD_Metadata/gmd:distributionInfo/gmd:MD_Distribution/gmd:transferOptions/gmd:MD_DigitalTransferOptions/gmd:onLine/gmd:CI_OnlineResource/gmd:linkage/gmd:URL');
					/*if ($accessUrl[0] == '') {
						$accessUrl = $cswSearchDetailXML->xpath('/csw:GetRecordByIdResponse/gmd:MD_Metadata/gmd:distributionInfo/gmd:MD_Distribution/gmd:transferOptions/gmd:MD_DigitalTransferOptions/gmd:onLine/gmd:CI_OnlineResource/gmd:linkage');*/
						if ($accessUrl[0] == '') {
							//search for another accessUrl - as defined in csw ap iso
							$accessUrl = $cswSearchDetailXML->xpath('/csw:GetRecordByIdResponse/gmd:MD_Metadata/gmd:identificationInfo/srv:SV_ServiceIdentification/srv:containsOperations/srv:SV_OperationMetadata/srv:connectPoint/gmd:CI_OnlineResource/gmd:linkage/gmd:URL');
						}
					//}

        				break;
    				default:
        				
			}

			$isViewService = false;	
			$accessUrl = $accessUrl[0];
			$typeOfServiceUpper = strtoupper($typeOfService);
			echo "<br>accessUrl: ".$accessUrl."<br>";
			echo "<br>typeOfService:*".$typeOfServiceUpper."*<br>";
			echo "<br>typeOfRecord:*".$typeOfRecord."*<br>";

			$title = $title[0];
			$graphicURL = $graphicURL[0];
			$orgaName = $orgaName[0];
			

			if ($title == '') {
				$title = 'Ohne Titel';
			}
			
			//check for view service type
			if ($typeOfServiceUpper == 'WMS' || $typeOfServiceUpper == 'VIEW'  || strpos($typeOfServiceUpper,'WMS') !== false) {
				$isViewService = true;	
				echo "view service identified<br>";
			}
			//check if service is view or wms and correct it for wms 1.1.1 caabilities request
			if ($typeOfRecord == 'service' && $isViewService) {
				if ($accessUrl != '') {
					$accessUrl = correctWmsUrl($accessUrl);
				} else {
					$accessUrl = '';
				}
			} else {
				echo "<b>no</b> view service identified<br>";
				$isViewService = false;
			}
			//give dummy if orgaName not given
			if ($orgaName == '') {
				$orgaName = 'Organisation unbekannt';
			}
			$abstract = $abstract[0];
			if (isset($bbox) && count($bbox) == 4) {
				$bbox = "(".implode(',',$bbox).")";
			} else {
				$bbox = '';
			}
                        $abstractShort = substr($abstract,0,250)."...";
			//echo "<br>Resultlist:<br>";
			echo "<br><span>";
			if (isset($graphicURL) && $graphicURL != '') {
				echo "<img src='".$graphicURL."' width='100' height='100'/>";
			}
			echo "<a href = '".$openSearchWrapperDetail."?cat_id=".$csw_list[$i_si] ['id']."&uuid=".$uuid."&mdtype=html'>".$title."</a><br>";
			echo $dateStamp." - ".$orgaName."<br>";
			if (isset($bbox) && $bbox != '') {
				echo $bbox."<br>";
			}
			echo $abstractShort."</span>";
			//check for isViewService to allow integration into Viewer by link TODO
			if (isset($accessUrl) && $accessUrl != '') {
				if ($isViewService) {
					echo "<br><span><a href ='".$accessUrl."'>Capabilities</a><br><br></span>";
				} else {
					echo "<br><span><a href ='".$accessUrl."'>Link</a><br><br></span>";
				}
			}
			//output to file in tmp folder:
			if (isset($uuid) && $uuid != ''){
				#Do result XML output to file
				fwrite($os_catalogs_file_handle,"<result>\n");
				#Tags for catalogtitle and link to detailed information
				fwrite($os_catalogs_file_handle,"<catalogtitle>");
				fwrite($os_catalogs_file_handle, $orgaName." (ID=".$uuid.")");
				fwrite($os_catalogs_file_handle,"</catalogtitle>\n");
				fwrite($os_catalogs_file_handle,"<catalogtitlelink>");
				fwrite($os_catalogs_file_handle,urlencode($openSearchWrapperDetail."?cat_id=".$csw_list[$i_si] ['id']."&uuid=".$uuid."&mdtype=html"));
				fwrite($os_catalogs_file_handle,"</catalogtitlelink>\n");
				#Tags for objecttitle and abstract
				fwrite($os_catalogs_file_handle,"<title>");
				fwrite($os_catalogs_file_handle, urlencode($title));
				fwrite($os_catalogs_file_handle,"</title>\n");
				fwrite($os_catalogs_file_handle,"<abstract>");
                               	fwrite($os_catalogs_file_handle, urlencode($abstractShort));
				fwrite($os_catalogs_file_handle,"</abstract>\n");
				#Tag for link to original metadata view
				fwrite($os_catalogs_file_handle,"<urlmdorig>");
				fwrite($os_catalogs_file_handle,urlencode($cswSearchUrlDetail));
				fwrite($os_catalogs_file_handle,"</urlmdorig>\n");
				#if a wms resource is found, the url will be in the list
				if (isset($accessUrl) && $isViewService && $accessUrl != ''){	
					fwrite($os_catalogs_file_handle,"<wmsaccessUrl>");
					fwrite($os_catalogs_file_handle, urlencode($accessUrl));
					fwrite($os_catalogs_file_handle,"</wmsaccessUrl>\n");
					fwrite($os_catalogs_file_handle,"<mbaddurl>");
					fwrite($os_catalogs_file_handle,"testurl");
					fwrite($os_catalogs_file_handle,"</mbaddurl>\n");
				} else {#add empty tags
					fwrite($os_catalogs_file_handle,"<wmsaccessUrl></wmsaccessUrl>\n<mbaddurl></mbaddurl>\n");				
				}
				//if ($typeOfRecord =='application' || $typeOfRecord =='dataset'){
					fwrite($os_catalogs_file_handle,"<accessUrl>");
					fwrite($os_catalogs_file_handle, urlencode($accessUrl));
					fwrite($os_catalogs_file_handle,"</accessUrl>\n");
				/*} else {
					fwrite($os_catalogs_file_handle,"<accessUrl>");
					fwrite($os_catalogs_file_handle,"</accessUrl>\n");
				}*/
				if (isset($graphicURL) && $graphicURL != '' && isValidURL($graphicURL)){
					fwrite($os_catalogs_file_handle,"<graphicUrl>");
					fwrite($os_catalogs_file_handle, urlencode($graphicURL));
					fwrite($os_catalogs_file_handle,"</graphicUrl>\n");
				} else {
					fwrite($os_catalogs_file_handle,"<graphicUrl>");
					fwrite($os_catalogs_file_handle,"</graphicUrl>\n");
				}
				if (isset($bbox)){	
					fwrite($os_catalogs_file_handle,"<georssurl>");
					//$urlToId = $openSearchUrlSearch[$i_si]."q=t01_object.obj_id:".$docuuid.$csw_list[$i_si] ['standardfilter']."&h=".$csw_list[$i_si] ['h']."&p=".$request_p;
					//fwrite($os_catalogs_file_handle,urlencode($urlToId));
					fwrite($os_catalogs_file_handle,"</georssurl>\n");
				}
				else
				{
					fwrite($os_catalogs_file_handle,"<georssurl>");
					fwrite($os_catalogs_file_handle,"</georssurl>\n");
				}
				fwrite($os_catalogs_file_handle,"<iso19139url>");
				fwrite($os_catalogs_file_handle,urlencode($openSearchWrapperDetail."?cat_id=".$csw_list[$i_si] ['id']."&uuid=".$uuid."&mdtype=iso19139"));
				fwrite($os_catalogs_file_handle,"</iso19139url>\n");
				fwrite($os_catalogs_file_handle,"<inspireurl>");
				fwrite($os_catalogs_file_handle,urlencode($openSearchWrapperDetail."?cat_id=".$csw_list[$i_si] ['id']."&uuid=".$uuid."&mdtype=iso19139&validate=true"));
				fwrite($os_catalogs_file_handle,"</inspireurl>\n");
				fwrite($os_catalogs_file_handle,"</result>\n");
			}
	}	
	fwrite($os_catalogs_file_handle,"</resultlist>\n");
	fclose($os_catalogs_file_handle);
}	
}
}
	echo "</body></html>";

function correctWmsUrl($wms_url) {
	//check if last sign is ? or & or none of them
	$lastChar = substr($wms_url,-1);
	//check if getcapabilities is set as a parameter
	$findme = "getcapabilities";
	$posGetCap = strpos(strtolower($wms_url), $findme);
	if ($posGetCap === false) {
		$posGetAmp = strpos(strtolower($wms_url), "?");
		if ($posGetAmp === false) {
			$wms_url .= "?REQUEST=GetCapabilities&VERSION=1.1.1&SERVICE=WMS";
		} else {
			switch ($lastChar) {
				case "?":
					$wms_url .= "REQUEST=GetCapabilities&VERSION=1.1.1&SERVICE=WMS";
				break;
				case "&":
					$wms_url .= "REQUEST=GetCapabilities&VERSION=1.1.1&SERVICE=WMS";
				break;
				default:
					$wms_url .= "&REQUEST=GetCapabilities&VERSION=1.1.1&SERVICE=WMS";
				break;
			 }
		}
	} else {
		//check if version is defined
		$findme1 = "version=";
		$posVersion = strpos(strtolower($wms_url), $findme1);
		if ($posVersion === false) {
			$wms_url .= "&VERSION=1.1.1";
		} else {
			//mapbender only handle 1.1.1
			$wms_url = str_replace('version=1.3.0', 'VERSION=1.1.1', $wms_url);
			$wms_url = str_replace('VERSION=1.3.0', 'VERSION=1.1.1', $wms_url);
		}
		
	}

	//exchange &? with & and &amp; 
	$wms_url = str_replace('&?', '&', $wms_url);
	$wms_url = str_replace('&amp;?', '&', $wms_url);
	$wms_url = str_replace('&amp;', '&', $wms_url);
return $wms_url;
}

function isValidURL($url) {
	return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
}


	





?>
