<?php
$unique=$_REQUEST["uid"];
$id=$unique=$_REQUEST["id"];

$adressfile = "typo3conf/ext/q4u_search/pi1/temp/__.geom.xml";

if(file_exists($adressfile)) {
	$xmlFile = file_get_contents($adressfile);

	$search="<member id=\"".$id."\">";
	$pos=strpos($xmlFile,$search)+strlen($search);
	$xmldata=substr($xmlFile,$pos);

	$pos=strpos($xmldata,"</member>");
	$xmldata=substr($xmldata,0,$pos);
}

$_SESSION["GML"]=$xmldata;
?>