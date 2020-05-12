<?php
#http://www.geoportal.rlp.de/mapbender/php/mod_layerISOMetadataWriteToFolder.php?SERVICE=WMS&outputFormat=iso19139&Id=24356
# $Id: mod_layerISOMetadata.php 235
# http://www.mapbender.org/index.php/Inspire_Metadata_Editor
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


require_once(dirname(__FILE__) . "/../../core/globalSettings.php");
require_once(dirname(__FILE__) . "/../classes/class_connector.php");

$con = db_connect(DBSERVER,OWNER,PW);
db_select_db(DB,$con);

//define the view or table where to read out the layer ids for which metadatafiles should be generated
$wmsView = "wms_search_table";
//$wmsView = '';
//parse request parameter
//make all parameters available as upper case

foreach($_REQUEST as $key => $val) {
	$_REQUEST[strtoupper($key)] = $val;
}
//validate request params

//
if (!isset($_REQUEST['TYPE'])) {
	echo 'GET Parameter Type lacks'; 
	die();
}

if (isset($_REQUEST['TYPE']) and $_REQUEST['TYPE'] != "ALL") {
	//
	echo 'validate: <b>'.$_REQUEST['TYPE'].'</b> is not valid.<br/>'; 
	die();
}

$sql = "SELECT layer_id ";
$sql .= "FROM ".$wmsView;
$v = array();
$t = array();
$res = db_prep_query($sql,$v,$t);

$generatorScript = '/mapbender/php/mod_layerISOMetadata.php?';
$generatorBaseUrl = 'http://'.$_SERVER['HTTP_HOST'].$generatorScript;

echo $generatorBaseUrl."<br>";

while($row = db_fetch_array($res)){
	$generatorUrl = $generatorBaseUrl."SERVICE=WMS&outputFormat=iso19139&id=".$row['layer_id'];
	echo "URL requested : ".$generatorUrl."<br>";
	$generatorInterfaceObject = new connector($generatorUrl);
	$ISOFile = $generatorInterfaceObject->file;
	#echo "Returned value: ".$ISOFile."<br>";
	//generate file identifier:
	$fileId = guid();
	echo "File ID ".$fileId." generated<br>";
	//generate temporary files under tmp
	if($h = fopen(TMPDIR."/metadata/mapbenderLayerMetadata_".$row['layer_id']."_".$fileId."_iso19139.xml","w")){
		if(!fwrite($h,$ISOFile)){
			$e = new mb_exception("mod_layerISOMetadata: cannot write to file: ".TMPDIR."/mapbenderLayerMetadata_".$row['layer_id']."_".$fileId."_iso19139.xml");
		}
	echo "File for ID ".$fileId." written to TMP<br>";
	fclose($h);
	}
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

