<?php
# Copyright (C) 2007 terrestris
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
# Verändert von Kai Behncke und Christian Plass IGF Osnabrück, März 2007

session_start();
extract($_GET, EXTR_OVERWRITE);extract($_POST, EXTR_OVERWRITE);
require_once("../php/mb_validateSession.php");
require_once("../../conf/geoportal.conf");
ini_set('error_reporting', 'E_ALL & ~ E_NOTICE');
# postgres-db-params
$host = GEOMDB_HOST;
$port = GEOMDB_PORT;
$dbname = GEOMDB_NAME;
$table_gem = "gis.gemark";
$table_hauspkt = "gis.hauskoordinaten";
$user = GEOMDB_USER;
$password = GEOMDB_PASSWORD;
$con_string = "host=$host port=$port dbname=$dbname user=$user password=$password";
$con = pg_connect ($con_string);
$filter = $_REQUEST['filter'];
$css = $_REQUEST['css'];
$char_str = $_REQUEST['char_str'];
$char_gem = $_REQUEST['char_gem'];
$str = $_REQUEST['str'];
$strschl = $_REQUEST['strschl'];
$gem = $_REQUEST['gem'];
$char_str_klein=strtolower($char_str);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>Gazetteer</title>

<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
<META name="author-mail" content="">
<META name="author" content="">
<META http-equiv="cache-control" content="no-cache">
<META http-equiv="pragma" content="no-cache">
<META http-equiv="expires" content="0">

<style>

a {  text-decoration:none;  color:#006171; font-family:Arial,Verdana;}
a:visited {  color:#555555;}
a:active {  color:#FF0000; }
a:hover {  color:#FFFFFF; text-decoration:none; background-color:#CC3333;}

#letter_normal
{
font-family:Arial,Verdana;
font-size:11px;
font-weight:bold;
float:left;
padding:2px;
color:rgb(192,192,192);
}

#letter_match
{
font-family:Arial,Verdana;
font-size:11px;
font-weight:bold;
float:left;
padding:2px;
}

#gem
{
font-size:12px;
line-height:16px;
padding:2px;
color:#000000;
}
a#gem:visited { color:#555555; }
a#gem:active { color:#FF0000; }
a#gem:hover { color:#FFFFFF; text-decoration:none; background-color:#CC3333; }

#strasse
{
font-size:12px;
line-height:16px;
padding:2px;
color:#000000;
}

a#strasse:visited { color:#555555; }
a#strasse:active { color:#FF0000; }
a#strasse:hover { color:#FFFFFF; text-decoration:none; background-color:#CC3333; }

#hnr
{
font-size:11px;
text-align:center;
vertical-align:middle;
height:20px;
width:30px;
float:left;
}

p
{
border: 0px solid #000000;
font-family:Arial,Verdana;
font-size:13px;
}

p.bold
{
font-size:12px;	
margin:2px;
}

p.ueber
{
font-size:13px;
margin:2px;
color:#000000;
font-weight:normal;
}

table {
width:170px;
border:0px solid #00008b;
border-collapse:collapse;
padding:0px;
margin:2px;
}

td, tr {
border:0px solid #00008b;
margin:0px;
padding-right:5px;
padding-left:5px;
padding-top:2px;
padding-bottom:0px;
}

body {
font-family:Arial,Verdana;
background-color: #FFFFFF;
padding:0px;
margin:0px;
}

.paneltop {
vertical-align:top;
height:22px;
margin:0px;	
border:0px solid #00008b;
background: url(bg_top_10.jpg) left top no-repeat;
}

.panelmiddle {
margin:0px;	
border:0px solid #00008b;
background: url(bg_middle_10.jpg) left top repeat-y;
}

.panelbottom {
vertical-align:top;
height:8px;
margin:0px;
margin-bottom:15px;
border:0px solid #00008b;
background: url(bg_bottom_10.jpg) left bottom no-repeat;
}

div.tt {
  position: absolute;
  display: none;
  background-color: #FBFCC4;
  font-family:Arial,Verdana;
  font-size:11px;
}

</style> 

<script type="text/javascript">
<!--
var mod_gazetteer_target = 'mapframe1';
var scaleCity = 5000;
var myCoords = new Array();
 --> 
 </script> 

<script type="text/javascript" src="gaz_funct.js"></script>

<script type="text/javascript">
Tooltip = null;
document.onmousemove = updateTooltip;

function updateTooltip(e) {
  if (Tooltip != null) {
    x = (document.all) ? window.event.x + Tooltip.offsetParent.scrollLeft : e.pageX;
    y = (document.all) ? window.event.y + Tooltip.offsetParent.scrollTop  : e.pageY;
    
	if ( x > 80 ) { Tooltip.style.left = (x - 100) + "px"; }
	else Tooltip.style.left = (x + 15) + "px";
    
	if ( y > 225 ) { Tooltip.style.top = (y - 70) + "px"; }
	else Tooltip.style.top   = (y + 15) + "px";
  }
}
function showTooltip(id) {
  Tooltip = document.getElementById(id);
  Tooltip.style.display = "inline"
}
function hideTooltip() {
  Tooltip.style.display = "none";
}
</script>

</head>
<!-- 
<div id="1" class="tt">Danach den <img src="../img/gpm.png" width=14 height=14>-Button und dann ein Gebäude anklicken, um die Eignung abzufragen.</div>
	  -->
<?php

$debug = 0;

if ($debug == 1) {
echo '<p><small>char gem: '.$char_gem.'<br>';
echo 'gem: '.$gem.'<br>';
echo 'char str: '.$char_str.'<br>';
echo 'str: '.$str.'<br>';
echo 'hnr: '.$hnr.'<br>';
echo 'zus: '.$zus.'<br></small></p>';
}

echo "<table id=\"alk_tabelle\"><tr><td class=\"paneltop\">"; // paneltop
echo "<p class='ueber'>Gemeindeauswahl</p>";
echo "</td></tr><tr><td class=\"panelmiddle\">"; // panelmiddle

echo "<form name='form_gazatteer' action='$PHP_SELF' method='post'>";
echo "<input type='hidden' id='char_gem' name='char_gem' value='$char_gem'>";
echo "<input type='hidden' id='gem' name='gem' value='$gem'>";
echo "<input type='hidden' id='char_str' name='char_str' value='$char_str'>";
echo "<input type='hidden' id='str' name='str' value='$str'>";
echo "<input type='hidden' id='strschl' name='strschl' value='$strschl'>";

echo "<div>\n";

// build array with first letters of the names in the table
$query_first_letters=pg_query("SELECT DISTINCT gemeind_na FROM $table_gem ORDER BY gemeind_na"); // from $table_gem
$number_of_rows=pg_num_rows($query_first_letters);

$array_first_letters=array();
for ($f=0;$f<$number_of_rows;$f++)
 {
  $first_letter=pg_result($query_first_letters,$f,gemeind_na);
  $first_letter=strtoupper( $first_letter);
  array_push($array_first_letters,$first_letter{0});     
 } 
 $array_first_letters=array_unique($array_first_letters);

 $array_first_letters_endgueltig=array();
 foreach($array_first_letters as $erster_buchstabe)
 array_push($array_first_letters_endgueltig,$erster_buchstabe);

// letters / only letters which appear as first letter in the fieldname of the table are linked
$letters = Array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');   
for($l=0;$l<sizeof($letters);$l++) 
 {   
  $letter_tmp=$letters[$l];
  
if (in_array($letter_tmp, $array_first_letters_endgueltig)) {
    $match=ja;
}
	if ($letter_tmp=='A')  if (in_array('Ä', $array_first_letters_endgueltig))
		$match=ja;
	if ($letter_tmp=='U')
		$match=ja;
	if ($letter_tmp=='O')  if (in_array('Ö', $array_first_letters_endgueltig))
		$match=ja;
	

  if ($match=='')
   {
    echo "<div id='letter_normal'>$letter_tmp</div>";
   }
  else
   {
    echo "<div id='letter_match'><a href=\"javascript:sendCharGem('$letter_tmp')\">$letter_tmp</a></div>";
   }
   $match="";
   
    if ($letter_tmp=='N') echo "</div><div>";
 }

echo "<div style=\"float:none;\"><br></div>";

// Auswahl Gemeinde 
if (isset($char_gem))
{
	if (strlen($gem) > 1)  echo "<p class='bold'>Gemeinde: $gem</p>"; 
	
else
 {
	 
if ($char_gem=='A') {$char_gem="'A%' or gemeind_na like 'Ä%'";}
else if ($char_gem=='U') $char_gem="'U%' or gemeind_na like 'Ü%'";
else if ($char_gem=='O') $char_gem="'O%' or gemeind_na like 'Ö%'";
else $char_gem="'".$char_gem."%'";

	//$sql = "SELECT DISTINCT gemeind_na, y_min, x_max, y_max, x_min FROM $table_gem WHERE gemeind_na like $char_gem ORDER BY gemeind_na"; // from $table_gem
	$sql = "SELECT DISTINCT gemeind_na FROM $table_gem WHERE gemeind_na like $char_gem ORDER BY gemeind_na";
    $result = pg_query($con,$sql);
    
    $cnt  = 0;

    echo "";

 while( pg_fetch_row($result) ){
#	$min_x  = pg_result($result,$cnt,"x_min");
#	$max_x  = pg_result($result,$cnt,"x_max");
#	$min_y  = pg_result($result,$cnt,"y_min");
#	$max_y  = pg_result($result,$cnt,"y_max");
#
  $to = pg_result($result,$cnt,"gemeind_na");
  $gemeindename = $to;
  
#  $gemx = $min_x + (($max_x - $min_x)/2); // für Zoom auf Mittelpunkt und Highlight auf Gemeinde
 # $gemy = $min_y + (($max_y - $min_y)/2); // s.o.
#  
  // Extent auf Mapframe Seitenverhältnis anpassen
#  $diffx = $max_x - $min_x; // differenz max_x / min_x
#  $diffy = $max_y - $min_y; // differenz max_y / min_y 
#  if ($diffx > 500/450 * $diffy ) {
#	  $diffyneu = ( $diffx / (500/450) );
#	  $min_y = $min_y - ( ($diffyneu - $diffy) / 2);
#	  $max_y = $max_y + ( ($diffyneu - $diffy) / 2);
#  }
#  else  {
#	  if ($diffx < 500/450 * $diffy ) {
#	  $diffxneu = ( $diffy * (500/450) );
#	  $min_x = $min_x - ( ($diffxneu - $diffx) / 2);
#	  $max_x = $max_x + ( ($diffxneu - $diffx) / 2);
#  }
 # }
    
  echo "";
  echo "<div style=\"line-height:12px;float:none;\">";
  // echo "<a id='gem' href=\"javascript:sendGem('$to'); parent.mb_repaintScale('mapframe1',$gemx,$gemy,12000); \"onmouseover=\"highlight($gemx,$gemy); \"onmouseout=\"hideHighlight(); \">$to</a>"; // 
 #echo "<a id='gem' href=\"javascript:sendGem('$to'); parent.mb_repaint('mapframe1',$min_x,$min_y,$max_x,$max_y); \"onmouseover=\"highlight($gemx,$gemy); \"onmouseout=\"hideHighlight(); \"onclick=\"hideHighlight(); \">$to</a>"; //
 echo "<a id='gem' href=\"javascript:sendGem('$to') \">$to</a>"; // 
 echo "</div>";
	  
  $cnt++;
 }
 }
}

// Auswahl Straße
 if(strlen($gem) > 1)
  {
	echo "</td></tr><tr><td class=\"panelbottom\"></td></tr>"; // panelbottom
	echo "<tr><td height=4></td></tr>"; // panelbottom		
	echo "<tr><td class=\"paneltop\">"; // paneltop
	echo "<p class='ueber'>Straßenauswahl</p>"; // Strassenauswahl
	echo "</td></tr><tr><td class=\"panelmiddle\">"; // panelmiddle
			
	$query_first_letters=pg_query("SELECT DISTINCT name ,gemeindeteilname FROM $table_hauspkt WHERE post_ortsname like '".$gem."%' ORDER BY name"); // from $table_hauspkt
	$number_of_rows=pg_num_rows($query_first_letters);
	$array_first_letters=array();
	for ($f=0;$f<$number_of_rows;$f++)
	{
		$first_letter=pg_result($query_first_letters,$f,name);
		$first_letter=strtoupper( $first_letter);
		array_push($array_first_letters,$first_letter{0});     
	} 
 
	$array_first_letters=array_unique($array_first_letters);

	$array_first_letters_endgueltig=array();
	foreach($array_first_letters as $erster_buchstabe)
	array_push($array_first_letters_endgueltig,$erster_buchstabe);


	// letters / only letters which appear as first letter in the fieldname of the table are linked
	$letters = Array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');   
	for($l=0;$l<sizeof($letters);$l++) 
	{   
		$letter_tmp=$letters[$l];
		if (in_array($letter_tmp, $array_first_letters_endgueltig)) {
			$match=ja;
			
			if ($letter_tmp=='A')  if (in_array('Ä', $array_first_letters_endgueltig))
			$match=ja;
			if ($letter_tmp=='U')  if (in_array('Ü', $array_first_letters_endgueltig))
			$match=ja;
			if ($letter_tmp=='O')  if (in_array('Ö', $array_first_letters_endgueltig))
			$match=ja;	
		
		}
			if ($match=='')
				{echo "<div id='letter_normal'>$letter_tmp</div>";}
			else
				{
				    echo "<div id='letter_match'><a href=\"javascript:sendCharStr('$letter_tmp')\">$letter_tmp</a></div>";
				    $e = new mb_notice("geopolis_point: erster Buchstabe Straße: ".$letter_tmp);
				}
			$match="";
			
			if ($letter_tmp=='N') echo "</div><div>";			
    }
	echo "<div style=\"float:none;\"><br></div>";
	
	// Auswahl Straße
	if (strlen($str) > 1) echo "<p class='bold'>Hausnummern \"$str\"</p>";
	else
		
		if (strlen($char_str) > 0) {
			
		if ($char_str=='A') $char_str="'A%' or name like 'Ä%'";
		else if ($char_str=='U') $char_str="'U%' or name like 'Ü%'";
		else if ($char_str=='O') $char_str="'O%' or name like 'Ö%'";
		else $char_str="'".$char_str."%'";
		
		
		//$sql = "SELECT DISTINCT name, x, y FROM $table_hauspkt WHERE ( gemeinde like '".$gem."' ) and ( name like $char_str ) ORDER BY name"; // from $table_hauspkt
		$sql = "SELECT DISTINCT name ,gemeindeteilname, strschl  FROM $table_hauspkt WHERE ( post_ortsname like '".$gem."' ) and ( name like $char_str ) ORDER BY name"; // from $table_hauspkt
   		$result = pg_query($con,$sql);
		$cnt  = 0;
		echo "";
		while( pg_fetch_row($result) )
		{
#		$strx = pg_result($result,$cnt,"x");
#		$stry = pg_result($result,$cnt,"y");

		$to = pg_result($result,$cnt,"name");
		$toz = pg_result($result, $cnt, "gemeindeteilname");
		$strto = pg_result($result, $cnt, "strschl");
		$to = $to." (".$toz.")";
  
		if ( $to != $to2 ) {
		echo "<div style=\"line-height:12px;float:none;\">";
		// Link zur Straße MIT Repaint // echo "<a id='strasse' href=\"javascript:sendStr('$to',$strto); parent.mb_repaintScale('mapframe1',$strx,$stry,4000); \"onmouseover=\"highlight($strx,$stry); \"onmouseout=\"hideHighlight(); \"onclick=\"hideHighlight(); \">$to</a>";
#		echo "<a id='strasse' href=\"javascript:sendStr('$to',$strto); \"onmouseover=\"highlight($strx,$stry); \"onmouseout=\"hideHighlight(); \"onclick=\"hideHighlight(); \">$to</a>";
		echo "<a id='strasse' href=\"javascript:sendStr('$to','$strto'); \"onmouseout=\"hideHighlight(); \"onclick=\"hideHighlight(); \">$to</a>";
		echo "</div>";
		}

		$cnt++;
		$to2 = $to;
		}
}
}
  
// Auswahl Hausnummer
if( strlen($str) > 1 )
 {
#	$sql = "SELECT DISTINCT name, hausnummer, zusatz, x, y, gemeinde FROM $table_hauspkt WHERE gemeinde like '".$gem."' and name like '".$str."' ORDER BY hausnummer, zusatz";
	$sql = "SELECT DISTINCT name, hausnummer, zusatz, ST_X(the_geom) as x, ST_Y(the_geom) as y, post_ortsname FROM $table_hauspkt WHERE post_ortsname like '".$gem."' and strschl = ".$strschl." ORDER BY hausnummer, zusatz";
	$e = new mb_notice("geopolis_point: SQL: " .$sql);
	$result = pg_query($con,$sql);
	$cnt  = 0;
	echo "<hr>";
  
  while(pg_fetch_row($result)){
  $mycount++;
  $nx = pg_result($result,$cnt,"x");
  $ny = pg_result($result,$cnt,"y");
  $hnr = pg_result($result,$cnt,"hausnummer");
  $zus = pg_result($result,$cnt,"zusatz");
 
  $to = $hnr . $zus;
  
  echo "<div style=\"line-height:12px;float:none;\">";
  
  echo "<a id='hnr' class='tt' href=\"javascript:zoom_me($nx,$ny,3001); highlight($nx,$ny); \"onmouseover=\"highlight($nx,$ny); \"onmouseout=\"hideHighlight(); \"onclick=\"hideHighlight(); \">".$to."</a>";
#  echo "<a id='hnr' class='tt' onmouseout=\"hideTooltip(); hideHighlight(); \"onclick=\"hideHighlight(); \">".$to."</a>";
    
  $cnt++;
  $z++;
 } 
 echo "</div>";
 
 }
 
 echo "</td></tr><tr><td class=\"panelbottom\"></td></tr></table>"; // panelbottom
 
 ?>
	 
</form>
</body>
</html>
