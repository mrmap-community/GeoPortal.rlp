<?php
# $Id:
# http://www.mapbender.org/index.php/mod_printPDF_pdf.php
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

require_once(dirname(__FILE__)."/../php/mb_validateSession.php");
include (dirname(__FILE__)."/../classes/class.ezpdf.php");
include (dirname(__FILE__)."/../classes/class_stripRequest.php");
include (dirname(__FILE__)."/../classes/class_weldMaps2PNG.php");
include (dirname(__FILE__)."/../classes/class_weldOverview2PNG.php");

$confFile = basename($_REQUEST["conf"]);
if (!preg_match("/^[a-zA-Z0-9_-]+(\.[a-zA-Z0-9]+)$/", $confFile) || 
	!file_exists($confFile)) {

	$errorMessage = _mb("Invalid configuration file");
	echo htmlentities($errorMessage, ENT_QUOTES, CHARSET);
	$e = new mb_exception($errorMessage);
	die;
}

include (dirname(__FILE__)."/../print_solarkataster/".$confFile);
include (dirname(__FILE__)."/../classes/class_SaveLegend.php");
include (dirname(__FILE__)."/../print/print_functions.php");

if($log == true){
	include ("../classes/class_log.php");
}

#Globals

$factor = intval($_REQUEST["quality"]);

#$date = date("d.m.Y",strtotime("now"));
$linewidth_dashed = 1;
$linewidth = 0.02;

// DURATION TIME:
function microtime_float(){
   list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}
$time_start = microtime_float();
// END DURATION TIME

$time_end = microtime_float();
$time = $time_end - $time_start;

/* -------------------------------------- */

$size = $_REQUEST["size"];
$format = $_REQUEST["format"];
$map_scale = $_REQUEST["map_scale"];
$overview_url = $_REQUEST["overview_url"];
$epsg = $_REQUEST["epsg"];
if($overview_url=='false'){
	$overview = false;	
}


function setscalebar($scale,$map_width2,$map_width){
	
	  //          550         / 500
		$teiler = $map_width2 / $map_width;
		$scale2 = $scale * $teiler;
		
    	$mb_resolution = 28.35;
        
		if($scale < 16){
	      $value = "10";
	      $unit = "cm";
	      $scalefactor = 10/$scale2;
	      $img_width = round($scalefactor * $mb_resolution);
	   }
	   if($scale >= 16 && $scale < 151){
	      $value = "1";	      
	      $unit = "Meter";
	      $scalefactor = 100/$scale2;
	      $img_width = round($scalefactor * $mb_resolution);
	   }
	   if($scale >= 151 && $scale < 1000 ){ # war <1550
	      $value = "10";	      
	      $unit = "Meter";
	      $scalefactor = 1000/$scale2;
	      $img_width = round($scalefactor * $mb_resolution);
	   }
	   if($scale >= 1000 && $scale < 1550 ){ 
	      $value = "50";	      
	      $unit = "Meter";
	      $scalefactor = 5000/$scale2;
	      $img_width = round($scalefactor * $mb_resolution);
	   }
	   if($scale >= 1550 && $scale < 15050){ # war >=1550
	      $value = "100";	      
	      $unit = "Meter";
	      $scalefactor = 10000/$scale2;
	      $img_width = round($scalefactor * $mb_resolution);
	   }
	   if($scale < 150050 && $scale >= 15050){
	      $value = "1";	      
	      $unit = "Kilometer";
	      $scalefactor = 100000/$scale2;
	      $img_width = round($scalefactor * $mb_resolution);
	   }
	   if($scale < 1500050 && $scale >= 150050){
	      $value = "10";	      
	      $unit = "Kilometer";
	      $scalefactor = 1000000/$scale;
	      $img_width = round($scalefactor * $mb_resolution);
	   }
	   if($scale < 15000050 && $scale >= 1500050){
	      $value = "100";	      
	      $unit = "Kilometer";
	      $scalefactor = 10000000/$scale;
	      $img_width = round($scalefactor * $mb_resolution);
	   }
	   if($scale < 150000001 && $scale >= 15000001){
	      $value = "1000";	      
	      $unit = "Kilometer";
	      $scalefactor = 100000000/$scale;
	      $img_width = round($scalefactor * $mb_resolution);
	   }
	   if($scale >= 150000001){
	      $value = "1000";	      
	      $unit = "Kilometer";
	      $scalefactor = 100000000/$scale;
	      $img_width = round($scalefactor * $mb_resolution);
	   }   
	   $array_scale[0] = $unit;
	   $array_scale[1] = $img_width;
		$array_scale[2] = $value;	   
	   
	   return  $array_scale; 
}


$border = 0.8 * $DPC;

if($matching == true){
   $urls = preg_replace($pattern,$replacement,$_REQUEST["map_url"]);  
}
else{
   $urls = $_REQUEST["map_url"];
}

// Spezial: Wenn Scale < 2200, tausche angeforderte Layer gegen Detail-Layer aus, damit Dienst auch etwas liefert
if($map_scale < 2200) {
	$urls = str_replace("Eignung_mzw,Eignung_wen,Eignung_neu,Eignung_sls","Eignung_det_mzw,Eignung_det_wen,Eignung_det_neu,Eignung_det_sls,Eignung_det_mzw2,Eignung_det_wen2,Eignung_det_sls2,Eignung_det_neu2",$urls);
}

/*
$pattern2 = "/geoportal.lkvk.saarland.de\/solar\/owsproxy\/([a-z0-9]*)\/([a-z0-9]*)\?/";
//$pattern2 = "geoportal.lkvk.saarland.de\/solar\/owsproxy";
$replace_pv = "lapllsun01.lkvk.saarland.de/cgi-bin/mapserv?MAP=/var/project/saarland_pv.map&";
$replace_st = "lapllsun01.lkvk.saarland.de/cgi-bin/mapserv?MAP=/var/project/saarland_th.map&";

if ( $thema == "Photovoltaik" ) {
$urls = preg_replace($pattern2,$replace_pv,$urls);
}

if ( $thema ==	"Solarthermie" ) {
$urls = preg_replace($pattern2,$replace_st,$urls);
}


//$urls = str_replace("geoportal.lkvk.saarland.de/solar","lapllsun01.lkvk.saarland.de",$urls);

$urls = str_replace("saarlouis_pv_ext.map","saarlouis_pv.map",$urls);
$urls = str_replace("saarlouis_th_ext.map","saarlouis_th.map",$urls);

//$urls = str_replace("saarland_pv_ext.map","saarland_pv.map",$urls);
//$urls = str_replace("saarland_th_ext.map","saarland_th.map",$urls);

$urls = str_replace("geoportal.lkvk.saarland.de/freewms/uebersichtsl","lapllgdisl01.lkvk.saarland.de:80/cgi-bin/uebersichtsl",$urls);
$urls = str_replace("geoportal.lkvk.saarland.de/freewms/dop2006","lapllumn01.lkvk.saarland.de:80/cgi-bin/freedop2006",$urls);
*/

//echo $urls."<br>";

$array_urls = explode("___", $urls);

$myURL = new stripRequest($array_urls[0]);
$map_width = round($myURL->get("width"));
$map_height = round($myURL->get("height"));
$map_extent = $myURL->get("BBOX");
if($factor>1){
	for($i=0; $i<count($array_urls); $i++){
		$m = new stripRequest($array_urls[$i]);
		$m->set('width',(intval($m->get('width'))*2.0));
		$m->set('height',(intval($m->get('height'))*2.0));
		if(in_array($m->get('map'),$highqualitymapfiles)){	
			$m->set('map',preg_replace("/\.map/","_4.map",$m->get('map')));			
		}
		$array_urls[$i] = $m->url;
	}
}
$coord = mb_split(",",$map_extent);

// analyse overview url and draw rectangle with position
$o_url = new stripRequest($overview_url);
$overview_width = round($o_url->get("width"));
$overview_height = round($o_url->get("height"));

//echo "hi";

if($factor>1){
	$o_url->set('width',(intval($o_url->get('width'))*2.0));
	$o_url->set('height',(intval($o_url->get('height'))*2.0));
	if(in_array($o_url->get('map'),$highqualitymapfiles)){	
			$o_url->set('map',preg_replace("/\.map/","_4.map",$o_url->get('map')));		
			$overview_url = $o_url->url;	
	}
}


if($matching == true){
	$overview_url = preg_replace($pattern,$replacement,$overview_url);  
}


if ($size == "A4" && $format == "portrait"){
	$overview_left = $a4p_overviewOffset_left;
	$overview_bottom =$a4p_overviewOffset_bottom;
}elseif ($size == "A4" && $format == "landscape"){
	$overview_left = $a4l_overviewOffset_left;
	$overview_bottom =$a4l_overviewOffset_bottom;
}elseif ($size == "A3" && $format == "portrait"){
	$overview_left = $a3p_overviewOffset_left;
	$overview_bottom =$a3p_overviewOffset_bottom;
}elseif ($size == "A3" && $format == "landscape"){
	$overview_left = $a3l_overviewOffset_left;
	$overview_bottom = $a3l_overviewOffset_bottom;
}elseif ($size == "A2" && $format == "portrait"){
	$overview_left = $a2p_overviewOffset_left;
	$overview_bottom =$a2p_overviewOffset_bottom;
}elseif ($size == "A2" && $format == "landscape"){
	$overview_left = $a2l_overviewOffset_left;
	$overview_bottom = $a2l_overviewOffset_bottom;
}elseif ($size == "A1" && $format == "portrait"){
	$overview_left = $a1p_overviewOffset_left;
	$overview_bottom =$a1p_overviewOffset_bottom;
}elseif ($size == "A1" && $format == "landscape"){
	$overview_left = $a1l_overviewOffset_left;
	$overview_bottom = $a1l_overviewOffset_bottom;
}elseif ($size == "A0" && $format == "portrait"){
	$overview_left = $a0p_overviewOffset_left;
	$overview_bottom =$a0p_overviewOffset_bottom;
}elseif ($size == "A0" && $format == "landscape"){
	$overview_left = $a0l_overviewOffset_left;
	$overview_bottom = $a0l_overviewOffset_bottom;
}

$o_extent = $o_url->get("BBOX");

$array_overview_url[0] = $overview_url;
if($log == true){
	$l = new log("printPDF_overview",$array_overview_url);
}

/*
$o_new = new stripRequest($overview_url);
$o_new->set('width',50);
$o_new->set('height',50);
//$o->set('BBOX',$overview_extent);
$o_url_new =$o_new->url;
$array_overview[0] = $overview_url;
$array_overview[1] = $o_url;
*/

/*
 * north arrow
 */
if($size == "A4" && $format == "portrait"){
	$northarrow_left = $a4p_northarrow_left;
	$northarrow_bottom = $a4p_northarrow_bottom;
}elseif ($size == "A4" && $format == "landscape"){
	$northarrow_left = $a4l_northarrow_left;
	$northarrow_bottom = $a4l_northarrow_bottom;
}elseif ($size == "A3" && $format == "portrait"){
	$northarrow_left = $a3p_northarrow_left;
	$northarrow_bottom = $a3p_northarrow_bottom;
}elseif ($size == "A3" && $format == "landscape"){
	$northarrow_left = $a3l_northarrow_left;
	$northarrow_bottom = $a3l_northarrow_bottom;
}elseif ($size == "A2" && $format == "portrait"){
	$northarrow_left = $a2p_northarrow_left;
	$northarrow_bottom = $a2p_northarrow_bottom;
}elseif ($size == "A2" && $format == "landscape"){
	$northarrow_left = $a2l_northarrow_left;
	$northarrow_bottom = $a2l_northarrow_bottom;
}elseif ($size == "A1" && $format == "portrait"){
	$northarrow_left = $a1p_northarrow_left;
	$northarrow_bottom = $a1p_northarrow_bottom;
}elseif ($size == "A1" && $format == "landscape"){
	$northarrow_left = $a1l_northarrow_left;
	$northarrow_bottom = $a1l_northarrow_bottom;
}elseif ($size == "A0" && $format == "portrait"){
	$northarrow_left = $a0p_northarrow_left;
	$northarrow_bottom = $a0p_northarrow_bottom;
}elseif ($size == "A0" && $format == "landscape"){
	$northarrow_left = $a0l_northarrow_left;
	$northarrow_bottom = $a0l_northarrow_bottom;
}

/*
 * special image
 */
if ($size == "A4" && $format == "portrait"){
	$specialImage_left = $a4p_special_left;
	$specialImage_bottom = $a4p_special_bottom;
}elseif ($size == "A4" && $format == "landscape"){
	$specialImage_left = $a4l_special_left;
	$specialImage_bottom = $a4l_special_bottom;
}elseif ($size == "A3" && $format == "portrait"){
	$specialImage_left = $a3p_special_left;
	$specialImage_bottom = $a3p_special_bottom;
}elseif ($size == "A3" && $format == "landscape"){
	$specialImage_left = $a3l_special_left;
	$specialImage_bottom = $a3l_special_bottom;
}elseif ($size == "A2" && $format == "portrait"){
	$specialImage_left = $a2p_special_left;
	$specialImage_bottom = $a2p_special_bottom;
}elseif ($size == "A2" && $format == "landscape"){
	$specialImage_left = $a2l_special_left;
	$specialImage_bottom = $a2l_special_bottom;
}elseif ($size == "A1" && $format == "portrait"){
	$specialImage_left = $a1p_special_left;
	$specialImage_bottom = $a1p_special_bottom;
}elseif ($size == "A1" && $format == "landscape"){
	$specialImage_left = $a1l_special_left;
	$specialImage_bottom = $a1l_special_bottom;
}elseif ($size == "A0" && $format == "portrait"){
	$specialImage_left = $a0p_special_left;
	$specialImage_bottom = $a0p_special_bottom;
}elseif ($size == "A0" && $format == "landscape"){
	$specialImage_left = $a0l_special_left;
	$specialImage_bottom = $a0l_special_bottom;
}

if($log == true){
	$l = new log("printPDF",$array_urls);
}
$pdf = new Cezpdf();

$pdf->Cezpdf(mb_strtolower($size),$format);
$diff=array(196=>'Adieresis',228=>'adieresis',
   214=>'Odieresis',246=>'odieresis',
   220=>'Udieresis',252=>'udieresis',
   223=>'germandbls');
$pdf->selectFont('../classes/fonts/LiberationSans-Regular.afm', array('encoding'=>'WinAnsiEncoding','differences'=>$diff));
if($size == "A4" && $format == "portrait"){
	$mapOffset_left = $a4p_mapOffset_left;
	$mapOffset_bottom = $a4p_mapOffset_bottom;
	$header_height = $a4p_header_height;
	$footer_height = $a4p_footer_height;
}
else{
	$mapOffset_left = $a4l_mapOffset_left;
	$mapOffset_bottom = $a4l_mapOffset_bottom;
	$header_height = $a4l_header_height;
	$header_width = $a4l_header_width;
}
session_write_close();

$i = new weldMaps2PNG(implode("___",$array_urls),$filename, false);

$map_height2 = $map_height;
$map_width2 = $map_width;

if($map_width > 500) {
$map_width = 500;
$map_height = $map_height / ( $map_width2 / 500);
}

if($map_height > 500) {
$map_height = 500;
$map_width = $map_width / ( $map_height2 / 500);
}

$einruecklinks = (500 - $map_width) / 2;
$einrueckunten = (500 - $map_height) / 2;
$mapOffset_left = $mapOffset_left;
$mapOffset_bottom = $mapOffset_bottom + $einrueckunten;

$pdf->addPngFromFile($filename, $mapOffset_left, $mapOffset_bottom, $map_width, $map_height);
//echo $filename;
if($unlink == true){
	unlink($filename);
}

/** ******************************************************************
 * user drawn elements
 */

# mypermanentImage (highlight symbol from geometry.js)
if ($_REQUEST["mypermanentImage"]){
	$array_permanentImage = explode("___", $_REQUEST["mypermanentImage"]);

	if(count($array_permanentImage)>0){
		$permanentImage = $array_permanentImage[0] ;
		if($permanentImage=='false'){
			$permanentImage='';
		}
		$permanentImage_x = $array_permanentImage[1] ;
		$permanentImage_y = $array_permanentImage[2] ;
		$permanentImage_width = $array_permanentImage[3] ;
		$permanentImage_height = $array_permanentImage[4] ;

		$pdf->addPngFromFile($permanentImage, $permanentImage_x + $mapOffset_left , $mapOffset_bottom + $map_height -  $permanentImage_y - $permanentImage_height, $permanentImage_width , $permanentImage_height);
	}
}

$theMeasureConfigArray = array(
   "do_fill" => FALSE,
     "fill_color" => array(
     "r" => 128 / 255,
     "g" => 128 / 255,
     "b" => 128 / 255
   ),
   "do_stroke" => FALSE,
     "stroke_color" => array(
     "r" => 254 / 255,
     "g" => 1 / 255,
     "b" => 1 / 255
   ),
   "line_style" => array(
       "width" => 2,
       "cap" => 'butt',
       "join" => 'miter',
       "dash" => array(10, 6)
       )
   );

if ($_REQUEST["measured_x_values"]!=''){
	addMeasuredItem($pdf, $_REQUEST["measured_x_values"], $_REQUEST["measured_y_values"], $theMeasureConfigArray);
	hideElementsOutsideMapframe($pdf);
}



if($epsg == "EPSG:4326"){

	$text4 = "";
}

if($size == "A4" && $format == "portrait"){
	
	$mySize = 12;
	
	$map_width3 = 500;
    $map_height3 = $map_height;
	
	// KARTENRAHMEN

	$rahmenMargin = 0; // Abstand des Rahmens vom Kartenrand
	
	$pdf->line($mapOffset_left - $rahmenMargin, $mapOffset_bottom + $map_height3 + $rahmenMargin, $mapOffset_left  + $map_width3 + $rahmenMargin, $mapOffset_bottom + $map_height3 + $rahmenMargin);
	$pdf->line($mapOffset_left - $rahmenMargin, $mapOffset_bottom - $rahmenMargin, $mapOffset_left  + $map_width3 + $rahmenMargin, $mapOffset_bottom - $rahmenMargin);
	$pdf->line($mapOffset_left - $rahmenMargin, $mapOffset_bottom + $map_height3 + $rahmenMargin, $mapOffset_left - $rahmenMargin, $mapOffset_bottom - $rahmenMargin);
	$pdf->line($mapOffset_left  + $map_width3 + $rahmenMargin, $mapOffset_bottom + $map_height3 + $rahmenMargin, $mapOffset_left  + $map_width3 + $rahmenMargin, $mapOffset_bottom - $rahmenMargin);
	
	// HIGHLIGHT
	
	/*
	$teiler = $map_width2 / $map_width;
		
	$highlightx = $highlightx/$teiler;
	$highlighty = $highlighty/$teiler;
	
	// ELLIPSE CIRCLE KREIS
	$pdf->setLineStyle(2); // DICKE LINIE
	$pdf->setStrokeColor(0,1,1); // CYAN
	$pdf->ellipse($mapOffset_left + $highlightx, $mapOffset_bottom + $map_height - $highlighty, 50); // KREIS
	$pdf->setStrokeColor(0,0,0); // BLACK
	$pdf->setLineStyle(0.01); // NORMALE LINIE	
	*/
   
   $mySize = 8;
   $pos_hinweis = 20;
   $buff_hinweis = 12;

   if ( $empfehl != '' ) {
   $length = $pdf->getTextWidth($mySize, "Empfohlener Modultyp: ".$empfehl);   // empfehl
   $pdf->addText($mapOffset_left,$mapOffset_bottom - $pos_hinweis,$mySize,"Empfohlener Modultyp: ".$empfehl);
   $pos_hinweis = $pos_hinweis + $buff_hinweis;
   }
   
   if (	$dachtyp == 'Flachdach'	) {
   $length = $pdf->getTextWidth($mySize, $flachdach);   // flachdach
   $pdf->addText($mapOffset_left,$mapOffset_bottom - $pos_hinweis,$mySize,$flachdach);
   $pos_hinweis = $pos_hinweis + $buff_hinweis;
   }

   $length = $pdf->getTextWidth($mySize, $text1);   // Denkmalschutzhinweis $text1
   $pdf->addText($mapOffset_left,$mapOffset_bottom - $pos_hinweis,$mySize,$text1);
   $pos_hinweis = $pos_hinweis + $buff_hinweis;
   $pdf->addText($mapOffset_left,$mapOffset_bottom - $pos_hinweis,$mySize,$text1a);
   $pos_hinweis = $pos_hinweis + $buff_hinweis;
   
   $mySize = 9;
   $length = $pdf->getTextWidth($mySize, $text2);   // Datum $text2
   $pdf->addText($mapOffset_left + $map_width3 - $length,$mapOffset_bottom + $map_height3 + 15,$mySize,$text2);
   
   // Haarlinie unter �berschrift und Datum
   //$pdf->setLineStyle(0.01);      
   //$pdf->line($mapOffset_left, $mapOffset_bottom + $map_height3 + $border + 25, $mapOffset_left + $map_width3, $mapOffset_bottom + $map_height3 + $border + 25);
 
   $mySize = 10;
  
   //$length = $pdf->getTextWidth($mySize, $text3);   // Objekt
   //$pdf->addText($mapOffset_left,$mapOffset_bottom + $map_height3 + 15,$mySize,$text3);
     
   $pdf->setLineStyle(0.01);
   $Size1 = 10;
   $Size2 = 10;
   $spaltenbreite = 104;

   if ($thema == "Photovoltaik") { 
   $pdf->line($mapOffset_left, $mapOffset_bottom - 70, $mapOffset_left + $map_width3, $mapOffset_bottom - 70); // Horizontale Linie 1
   $pdf->line($mapOffset_left + ($spaltenbreite+20), $mapOffset_bottom - 95, $mapOffset_left + $map_width3, $mapOffset_bottom - 95); // Horizontale Linie 2
   $pdf->line($mapOffset_left + ($spaltenbreite+20), $mapOffset_bottom - 50,$mapOffset_left + ($spaltenbreite+30) - 10, $mapOffset_bottom - 120); // Vertikal 1
   $pdf->line($mapOffset_left + ($spaltenbreite*2), $mapOffset_bottom - 50,$mapOffset_left + ($spaltenbreite*2+10) - 10, $mapOffset_bottom - 120); // Vertikal 2
   $pdf->line($mapOffset_left + ($spaltenbreite*3) - 10, $mapOffset_bottom - 50,$mapOffset_left + ($spaltenbreite*3) - 10, $mapOffset_bottom - 120); // Vertikal 3
   $pdf->line($mapOffset_left + ($spaltenbreite*4) - 10, $mapOffset_bottom - 50,$mapOffset_left + ($spaltenbreite*4) - 10, $mapOffset_bottom - 120); // Vertikal 4
   
   }
   else {
   $pdf->line($mapOffset_left, $mapOffset_bottom - 70, $mapOffset_left + 260, $mapOffset_bottom - 70); // Horizontale Linie
   $pdf->line($mapOffset_left + ($spaltenbreite) - 10, $mapOffset_bottom - 50,$mapOffset_left + ($spaltenbreite) - 10, $mapOffset_bottom - 120); // Vertikal 1	   
   }
      
   $mySize = $Size1;
   $length = $pdf->getTextWidth($mySize, $text5);   // Eignung
   $pdf->addText($mapOffset_left,$mapOffset_bottom - 60,$mySize,$text5);
   
   $mySize = $Size2;
   $length = $pdf->getTextWidth($mySize, $text6);   
   $pdf->addText($mapOffset_left,$mapOffset_bottom - 98,$mySize,$text6);
   
   $mySize = $Size1;
   $length = $pdf->getTextWidth($mySize, $text9);   // Modulfl�che
   $pdf->addText($mapOffset_left + ($spaltenbreite+30),$mapOffset_bottom - 60,$mySize,$text9);

   if ($thema == "Solarthermie") {
   
   $mySize = $Size2;
   $length = $pdf->getTextWidth($mySize, $text10);
   $pdf->addText($mapOffset_left + ($spaltenbreite+30),$mapOffset_bottom - 98,$mySize,$text10);

   }
   else {

   $mySize = $Size2;
   $length = $pdf->getTextWidth($mySize, $text10_15);
   $pdf->addText($mapOffset_left + ($spaltenbreite+30),$mapOffset_bottom - 85,$mySize,$text10_15);

   $mySize = $Size2;
   $length = $pdf->getTextWidth($mySize, $text10_09);
   $pdf->addText($mapOffset_left + ($spaltenbreite+30),$mapOffset_bottom - 110,$mySize,$text10_09);

   $mySize = $Size1;
   $length = $pdf->getTextWidth($mySize, $text10a);   // Typ
   $pdf->addText($mapOffset_left + ($spaltenbreite*2+10),$mapOffset_bottom - 60,$mySize,$text10a);
  
   $mySize = $Size2;
   $length = $pdf->getTextWidth($mySize, $text30);   
   $pdf->addText($mapOffset_left + ($spaltenbreite*2+10),$mapOffset_bottom - 85,$mySize,$text30);
   
   $length = $pdf->getTextWidth($mySize, $text31);   
   $pdf->addText($mapOffset_left + ($spaltenbreite*2+10),$mapOffset_bottom - 110,$mySize,$text31);
   
   $mySize = $Size1;
   $length = $pdf->getTextWidth($mySize, $text11);   // Strom
   $pdf->addText($mapOffset_left + ($spaltenbreite*3),$mapOffset_bottom - 60,$mySize,$text11);
   
   $mySize = $Size2;
   $length = $pdf->getTextWidth($mySize, $text12);   
   $pdf->addText($mapOffset_left + ($spaltenbreite*3),$mapOffset_bottom - 85,$mySize,$text12);

   $mySize = $Size2;
   $length = $pdf->getTextWidth($mySize, $text12a);   
   $pdf->addText($mapOffset_left + ($spaltenbreite*3),$mapOffset_bottom - 110,$mySize,$text12a);
   
   $mySize = $Size1;
   $length = $pdf->getTextWidth($mySize, $text13);   // CO2
   $pdf->addText($mapOffset_left + ($spaltenbreite*4),$mapOffset_bottom - 60,$mySize,$text13);
   
   $mySize = $Size2;
   $length = $pdf->getTextWidth($mySize, $text14);   
   $pdf->addText($mapOffset_left + ($spaltenbreite*4),$mapOffset_bottom - 85,$mySize,$text14);
   
   $length = $pdf->getTextWidth($mySize, $text14a);   
   $pdf->addText($mapOffset_left + ($spaltenbreite*4),$mapOffset_bottom - 110,$mySize,$text14a);
   
   }
   
   $mySize = 7;
   $posFuss = $mapOffset_bottom - 135;
   $lineHeight = 12;
   
   $length = $pdf->getTextWidth(8, $text15);   // Fusszeile 1
   $pdf->addText($mapOffset_left,$posFuss,8,$text15);   
   //$pdf->addText($mapOffset_left + ($map_width/2) - ($length/2),30,$mySize,$text15);  // Beispiel f�r zentrierten Text
   $posFuss = $posFuss - $lineHeight;

   $length = $pdf->getTextWidth(8, $text15b);   // Fusszeile 2
   $pdf->addText($mapOffset_left,$posFuss,8,$text15b);   
   $posFuss = $posFuss - ($lineHeight * 2);
   
   $length = $pdf->getTextWidth($mySize, $text15c);   // Fusszeile 3
   $pdf->addText($mapOffset_left,$posFuss,$mySize,$text15c);   
   $posFuss = $posFuss - $lineHeight;
   
   $length = $pdf->getTextWidth($mySize, $text15d);   // Fusszeile 4
   $pdf->addText($mapOffset_left,$posFuss,$mySize,$text15d);   
   $posFuss = $posFuss - $lineHeight;
   
   $length = $pdf->getTextWidth($mySize, $text15e);   // Fusszeile 5
   $pdf->addText($mapOffset_left,$posFuss,$mySize,$text15e);   
   $posFuss = $posFuss - $lineHeight;
   
   $length = $pdf->getTextWidth($mySize, $text15f);   // Fusszeile 6
   $pdf->addText($mapOffset_left,$posFuss,$mySize,$text15f);   
   $posFuss = $posFuss - $lineHeight;
   
   $length = $pdf->getTextWidth($mySize, $text15g);   // Fusszeile 7
   $pdf->addText($mapOffset_left,$posFuss,$mySize,$text15g);   
   $posFuss = $posFuss - $lineHeight;
   
   $length = $pdf->getTextWidth($mySize, $text15h);   // Fusszeile 8
   $pdf->addText($mapOffset_left,$posFuss,$mySize,$text15h);   
   $posFuss = $posFuss - $lineHeight;
   
   //$length = $pdf->getTextWidth($mySize, $text16);   // Fusszeile 2
   //$pdf->addText($mapOffset_left,20,$mySize,$text16);   
   //$pdf->addText($mapOffset_left + ($map_width/2) - ($length/2),20,$mySize,$text16);  // zentriert 

   $mySize = 8;

   if ($text17 != "Kommentar: ") {       
   $length = $pdf->getTextWidth($mySize, $text17);   // Kommentar 1
   $pdf->addText($mapOffset_left,$mapOffset_bottom + $map_height + $border + 80,$mySize,$text17);
   }
   if ($text18 != "Kommentar: ") {       
   $length = $pdf->getTextWidth($mySize, $text18);   // Kommentar 2
   $pdf->addText($mapOffset_left,80,$mySize,$text18);
   }
   
   //special image on the map-page
	
	// LOGOS
	
	$pdf->addPngFromFile($logo_wfus, 55, 760 , $logo_wfus_width, $logo_wfus_height); // left, bottom, width, height
	$pdf->addPngFromFile($logo_sunarea, 470, 30 , $logo_sunarea_width, $logo_sunarea_height);

	// LEGEND	

//echo $thema." ".$map_scale." ".$detailscale;
	
	if ($thema == 'Photovoltaik') { 
	  if ( $map_scale < $detailscale ) {
	  $pdf->addPngFromFile($legende_pv_det, $mapOffset_left+5, $mapOffset_bottom+5 , $legende_pv_det_width, $legende_pv_det_height); 
	  }
	  else {
	  $pdf->addPngFromFile($legende_pv, $mapOffset_left+5, $mapOffset_bottom+5 , $legende_pv_width, $legende_pv_height); 
	  }
	}
	if ($thema == 'Solarthermie') { $pdf->addPngFromFile($legende_st, $mapOffset_left+5, $mapOffset_bottom+5 , $legende_st_width, $legende_st_height); }

}
else{
   $pdf->setColor(1,1,1);
   $ll = array($mapOffset_left + $map_width - $header_width + $border - $linewidth, $mapOffset_bottom - $border + 1);
   $pdf->filledRectangle($ll[0], $ll[1], $header_width,$header_height);
   $pdf->line($ll[0], $ll[1], $ll[0], $ll[1] + $header_height);
   $pdf->line($ll[0], $ll[1] + $header_height, $ll[0] + $header_width, $ll[1] + $header_height);

   $pdf->line($ll[0] + 2, $ll[1] + 2, $ll[0] + 2,  $ll[1] + $header_height - 2);
   $pdf->line($ll[0] + 2,  $ll[1] + $header_height - 2, $ll[0] - 2 + $header_width, $ll[1] + $header_height - 2);
   $pdf->line($ll[0] - 2 + $header_width, $ll[1] + $header_height - 2, $ll[0] - 2 + $header_width, $ll[1] + 2);
   $pdf->line($ll[0] - 2 + $header_width, $ll[1] + 2, $ll[0] + 2, $ll[1] + 2);
   
   $pdf->line($ll[0] + 2, $ll[1] + 110 , $ll[0] - 2 + $header_width, $ll[1] + 110);
   $pdf->line($ll[0] + 2, $ll[1] + 40 , $ll[0] - 2 + $header_width, $ll[1] + 40);
   
   
   //special image on the map-page
   if ($special == true){
   	$pdf->addPngFromFile($specialImage, $specialImage_left, $specialImage_bottom , $specialImage_width, $specialImage_height);
   }
   
    if($epsg == "EPSG:4326"){

	$text4 = "";
	}
	
   $pdf->setColor(0,0,0);
   $mySize = 9;
   $length = $pdf->getTextWidth($mySize, $text1);   
   $pdf->addText($ll[0] + $header_width/2 - $length/2,185,$mySize,$text1);
   
   $mySize = 8;
   $length = $pdf->getTextWidth($mySize, $text2);   
   $pdf->addText($ll[0] + $header_width/2 - $length/2,175,$mySize,$text2);
   $length = $pdf->getTextWidth($mySize, $text3);   
   $pdf->addText($ll[0] + $header_width/2 - $length/2,165,$mySize,$text3);
   $mySize = 9;
   $length = $pdf->getTextWidth($mySize, $text4);   
   $pdf->addText($ll[0] + $header_width/2 - $length/2,155,$mySize,$text4);
   $mySize = 8;
   $length = $pdf->getTextWidth($mySize, $text5);   
   $pdf->addText($ll[0] + $header_width/2 - $length/2,145,$mySize,$text5);   
   
   $mySize = 9;
   $length = $pdf->getTextWidth($mySize, $text6);   
   $pdf->addText($ll[0] + $header_width/2 - $length/2,125,$mySize,$text6);
   $length = $pdf->getTextWidth($mySize, $text7);   
   $pdf->addText($ll[0] + $header_width/2 - $length/2,115,$mySize,$text7);
   $length = $pdf->getTextWidth($mySize, $text8);   
   $pdf->addText($ll[0] + $header_width/2 - $length/2,95,$mySize,$text8);
   $length = $pdf->getTextWidth($mySize, $text9);   
   $pdf->addText($ll[0] + $header_width/2 - $length/2,85,$mySize,$text9);
   $length = $pdf->getTextWidth($mySize, $text10);   
   $pdf->addText($ll[0] + $header_width/2 - $length/2,75,$mySize,$text10);
   
   $mySize = 6;
   $length = $pdf->getTextWidth($mySize, $text11);   
   $pdf->addText($ll[0] + $header_width/2 - $length/2,60,$mySize,$text11); 
   $mySize = 6;
   $length = $pdf->getTextWidth($mySize, $text14);   
   $pdf->addText($ll[0] + $header_width/2 - $length/2,53,$mySize,$text14);
   $length = $pdf->getTextWidth($mySize, $text15);   
   $pdf->addText($ll[0] + $header_width/2 - $length/2,47,$mySize,$text15);
   $length = $pdf->getTextWidth($mySize, $text16);   
   $pdf->addText($ll[0] + $header_width/2 - $length/2,41,$mySize,$text16);
   $length = $pdf->getTextWidth($mySize, $text17);   
   $pdf->addText($ll[0] + $header_width/2 - $length/2,35,$mySize,$text17);                             
}
#Coordinates
$myMinx = "R ".substr(round($coord[0]), 0, 4)." ".substr(round($coord[0]), 4, 3)."";
$myMiny = "H ".substr(round($coord[1]), 0, 4)." ".substr(round($coord[1]), 4, 3)."";
$myMaxx = "R ".substr(round($coord[2]), 0, 4)." ".substr(round($coord[2]), 4, 3)."";
$myMaxy = "H ".substr(round($coord[3]), 0, 4)." ".substr(round($coord[3]), 4, 3)."";

$mySize = 6;
$pdf->addText($mapOffset_left - 3, $mapOffset_bottom, $mySize, $myMinx, -90);
$pdf->addText($mapOffset_left, $mapOffset_bottom  - ($pdf->getFontHeight($mySize)), $mySize, $myMiny);
$pdf->addText($mapOffset_left  + $map_width - ($pdf->getTextWidth($mySize, $myMaxx)), $mapOffset_bottom + $map_height  + 3, $mySize, $myMaxx);
$pdf->addText($mapOffset_left + $map_width + 3, $mapOffset_bottom + $map_height, $mySize, $myMaxy, 90);


if ($overview==true){
	// analyse request, draw rectancle
	$filename = preg_replace("/map_/","overview_",$filename);
	if($size == "A4" && $format == "portrait"){
		$i = new weldOverview2PNG($overview_url,$array_urls[0] ,$filename);

		$pdf->addPngFromFile($filename, $overview_left,$overview_bottom, $overview_width, $overview_height);
		if($unlink == true){
			unlink($filename);
		}
	}
	else{
		$i = new weldOverview2PNG($overview_url,$array_urls[0],$filename);

		//$pdf->addPngFromFile($filename, $mapOffset_left,$mapOffset_bottom, $overview_width, $overview_height);
		$pdf->addPngFromFile($filename, $overview_left,$overview_bottom, $overview_width, $overview_height);
		if($unlink == true){
			unlink($filename);
		}
	}
}

if ($northarrow==true){
	$pdf->addPngFromFile($northarrowImage, $northarrow_left, $northarrow_bottom , $northarrowImage_width, $northarrowImage_height);
}

if($epsg == "EPSG:4326"){

	$scalebar = false;
}

if($scalebar == true){
	if ($size == "A4" && $format == "portrait"){
		$scalebar_left = $map_width;
		$scalebar_bottom = $mapOffset_bottom - 20;
	}elseif ($size == "A4" && $format == "landscape"){
		$scalebar_left = $a4l_scalebar_left;
		$scalebar_bottom = $a4l_scalebar_bottom;
	}elseif ($size == "A3" && $format == "portrait"){
		$scalebar_left = $a3p_scalebar_left;
		$scalebar_bottom = $a3p_scalebar_bottom;
	}elseif ($size == "A3" && $format == "landscape"){
		$scalebar_left = $a3l_scalebar_left;
		$scalebar_bottom = $a3l_scalebar_bottom;
	}elseif ($size == "A2" && $format == "portrait"){
		$scalebar_left = $a2p_scalebar_left;
		$scalebar_bottom = $a2p_scalebar_bottom;
	}elseif ($size == "A2" && $format == "landscape"){
		$scalebar_left = $a2l_scalebar_left;
		$scalebar_bottom = $a2l_scalebar_bottom;
	}elseif ($size == "A1" && $format == "portrait"){
		$scalebar_left = $a1p_scalebar_left;
		$scalebar_bottom = $a1p_scalebar_bottom;
	}elseif ($size == "A1" && $format == "landscape"){
		$scalebar_left = $a1l_scalebar_left;
		$scalebar_bottom = $a1l_scalebar_bottom;
	}elseif ($size == "A0" && $format == "portrait"){
		$scalebar_left = $a0p_scalebar_left;
		$scalebar_bottom = $a0p_scalebar_bottom;
	}elseif ($size == "A0" && $format == "landscape"){
		$scalebar_left = $a0l_scalebar_left;
		$scalebar_bottom = $a0l_scalebar_bottom;
	}
	
	$array_scalebar = setscalebar($map_scale,$map_width2,$map_width);
	
	$scalebar_left = $mapoffset_left + $border + $map_width - $array_scalebar[1];
	$pdf->setLineStyle($scalebar_height, '','', array());
   	$pdf->setColor(0,0,0);
	#$pdf->line($scalebar_left, $scalebar_bottom , $scalebar_left - 200 + $array_scalebar[1], $scalebar_bottom);
	$pdf->filledRectangle($scalebar_left, $scalebar_bottom, $array_scalebar[1],$scalebar_height);
	
	$pdf->setColor(1,1,1);
	$pdf->filledRectangle($scalebar_left + $array_scalebar[1]/4 + 1 , $scalebar_bottom + 1, $array_scalebar[1]/4 - 1 ,$scalebar_height-2);	
	$pdf->setColor(1,1,1);
	$pdf->filledRectangle($scalebar_left + 3*($array_scalebar[1]/4) + 1 , $scalebar_bottom + 1, $array_scalebar[1]/4 - 2 ,$scalebar_height-2);		
		
		
	#$pdf->setColor(1,0,1);
	#$pdf->filledRectangle($scalebar_left  , $scalebar_bottom - 20, 1 * $DPC ,$scalebar_height-2);	
	
	
	# value - Einheiten 
	$pdf->setColor(0,0,0);	
   $mySize = 8;
   $scalebar_height_half = 0.5 * $scalebar_height;  
    
	$myText = 0;
   $length = $pdf->getTextWidth($mySize, $myText);  
   $pdf->addText($scalebar_left - $length/2 ,$scalebar_bottom + 6 ,$mySize,$myText); # 6 war 9
   
	$myText = $array_scalebar[2]/2;
   $length = $pdf->getTextWidth($mySize, $myText);     
   $pdf->addText($scalebar_left + $array_scalebar[1]/2 - $length/2 ,$scalebar_bottom + 6 ,$mySize,$myText); # 6 war 9
	
	$myText = $array_scalebar[2];
   $length = $pdf->getTextWidth($mySize, $myText);  
   $pdf->addText($scalebar_left + $array_scalebar[1] - $length/2 - $length/4 ,$scalebar_bottom + 6 ,$mySize,$myText); # 6 war 9
 
	
	$pdf->setColor(0,0,0);	
   $mySize = 8;
   $scalebar_height_half = 0.5 * $scalebar_height;   
   $myText = $array_scalebar[0];

   #$pdf->addText($scalebar_left + $scalebar_width + 5,$scalebar_bottom - $scalebar_height_half ,$mySize,$myText);
  

	#units  
   $length = $pdf->getTextWidth($mySize, $myText);
   $pdf->addText($scalebar_left + $array_scalebar[1]/2 - $length/2 ,$scalebar_bottom - 9 ,$mySize,$myText); # 9 war 12
 
}


/* ------------------------ 
    new page for legend
   ------------------------ */
if ($legend == true && $_REQUEST["mylegend"]=='true'){

	$pdf->ezNewPage();

	//Pageborder (top, bottom, left, right)
	
	if($size == "A4" && $format == "portrait"){
	  $pdf->ezSetMargins(50,50,80,30);
	} else {
	  $pdf->ezSetMargins(60,35,60,60);
	}
	
	//Requests
	if(CHARSET=="UTF-8"){
		$new_wms_title=utf8_decode($_REQUEST["wms_title"]);
	}else{
		$new_wms_title=$_REQUEST["wms_title"];
	}
	
	if(CHARSET=="UTF-8"){
		$new_layers=utf8_decode($_REQUEST["layers"]);
	}else{
		$new_layers=$_REQUEST["layers"];
	}
	
	$my_wms_id = explode("___",$_REQUEST["wms_id"]);
	$my_wms_title = explode("___",$new_wms_title);
	$my_layers = explode("___",$new_layers);
	if($matching == true){
        $my_legend = preg_replace($pattern,$replacement,$_REQUEST["legendurl"]);  
    }
    else{
        $my_legend = $_REQUEST["legendurl"];
    }
    $my_legend = explode("___",$my_legend);

	//columns
	if($size == "A4" && $format == "portrait"){
	  $pdf->ezColumnsStart(array ('num'=>2, 'gap'=>10));
	} else {
	  $pdf->ezColumnsStart(array ('num'=>3, 'gap'=>10));
	}

	//header from printPDF.conf
	//$pdf->ezText("<b>".$titel."</b>", 13);
	$pdf->ezText("<b><u>".$legendText."</u></b>", 13);

	
	//Seitenraender (top, bottom, left, right)
	if($size == "A4" && $format == "portrait"){
	  $pdf->ezSetMargins(70,35,80,30);
	} else {
	  $pdf->ezSetMargins(100,35,60,60);
	}
	
	//generate the legend---------------------------------------------
	
	// Gesamthoehe Legende / height of the legend
	$sum_legend_height = 0;


	for($i=0; $i<count($my_wms_id); $i++){
		if ($my_wms_id[$i] != '0'){  //wms_id not 0
			$layer = explode(",",$my_layers[$i]);
			$my_legendurls = explode(",",$my_legend[$i]);
			
			$wms_y_position = $pdf->ezText("<b>".$my_wms_title[$i]."</b>", 12, array('spacing'=>1.2));
			$wms_zeilenhoehe   =  $pdf->getFontHeight(12);
				 
			// add this to the height of the legend /addiere dies zur Gesamthoehe Legende
			$sum_legend_height += $wms_zeilenhoehe;
			
			//Layer
			$l = 0;		#l temporary parameter to count the layer /Hilfvariable zum durchz�hlen der angezeigten Layer
			for($j=0; $j<count($my_legendurls); $j++){
				// url with grouped layers------------------
				$temp_url = explode('*',$my_legendurls[$j]);
				$temp_layers = explode('*',$layer[$j]);
	
				for ($q=0; $q <count($temp_url);$q++){  
					if($temp_url[$q] == '1' ){			// Layertitle for the parent of grouped layers	 
						// add this to the height of the legend /addiere dies zur Gesamth�he Legende		
						$layer_y_position = $pdf->ezText($temp_layers[$q], 11, array('spacing'=>1.2));
						$layer_zeilenhoehe   =  $pdf->getFontHeight(12);
						$sum_legend_height += $layer_zeilenhoehe;
						
					}elseif($temp_url[$q] != '0' ){
						$funktionsaufruf = new SaveLegend($temp_url[$q],$legendFilename);
						$imgsize = getimagesize($legendFilename);
						// add this to the height of the legend /addiere dies zur Gesamthoehe der Legende
						$sum_legend_height += $imgsize[1];
	
						//calculate text + picture / Berechnung Groesse Schrift + Bild
						if($l == 0){
						       $y_position = $wms_y_position;
						       $wms_y_position = '';
						}else{
							 $y_position = $pdf->ezText("", 1, array('spacing'=>1.2));
						}
						$layer_zeilenhoehe = $pdf->getFontHeight(11);
						$next_position = $y_position - $layer_zeilenhoehe - $imgsize[1];
						
						// add this to the height of the legend / addiere dies zur Gesamth�he Legende
						$sum_legend_height += $layer_zeilenhoehe;
							
						$l = $l+1;
		
		     			// if text + picture are smaler then the lower margin + textsize, then set a space
						//wenn Schrift + Bild kleiner der unteren Margin + Zeilenhoehe, dann Abstand setzen
						 if($size == "A4" && $format == "portrait" && $next_position <= 35 +$layer_zeilenhoehe){ //90 $layer_zeilenhoehe
							$space = $layer_zeilenhoehe + $imgsize[1];
						  $pdf->ezSetDy(-$space);
						} 
						if($size == "A4" && $format == "landscape" && $next_position <= 35+$layer_zeilenhoehe){//50
							$space = $layer_zeilenhoehe + $imgsize[1];
						  $pdf->ezSetDy(-$space);
						}
				
						//write the header layername / Ueberschrift schreiben
						$legend = $temp_layers[$q]."\n";  //$layer[$j]."\n"; 
						$pdf->ezText($legend, 11, array('spacing'=>1.2));
						
						//$pdf->ezText($url, 9, array('spacing'=>1.2));
						//print the picture / Bild schreiben
						$pdf->ezImage($legendFilename, 0, 'width', 'none', 'left');
						if($unlink == true){
							unlink($legendFilename);
						}
						
					} //if legendurl
				}// for legendurl
	
			  	//frames (x1, y1, x2, y2)
				if($size == "A4" && $format == "portrait"){
					#line  
					$pdf->setLineStyle($linewidth, '', '', array());
					//left
					$pdf->line($mapOffset_left - $border, $mapOffset_bottom - $border - $footer_height, $mapOffset_left - $border, $mapOffset_bottom  + $map_height + $border + $header_height);
					//right
					$pdf->line($mapOffset_left - $border, $mapOffset_bottom  + $map_height + $border + $header_height, $mapOffset_left + $map_width + $border, $mapOffset_bottom + $map_height + $border + $header_height);
					//top
					$pdf->line($mapOffset_left + $map_width + $border, $mapOffset_bottom + $map_height + $border + $header_height, $mapOffset_left + $map_width + $border, $mapOffset_bottom - $border - $footer_height);
					//bottom
					$pdf->line($mapOffset_left + $map_width + $border, $mapOffset_bottom -$border - $footer_height, $mapOffset_left - $border, $mapOffset_bottom - $border - $footer_height);
					
					if ($legendImage!=''){
						//image on top of page
						$pdf->addPngFromFile($legendImage, $mapOffset_left + $map_width + $border - $legendImage_width -6, $mapOffset_bottom + $map_height + $border + $header_height - $legendImage_height - 4 , $legendImage_width, $legendImage_height);
					}    
				} else {
				  $pdf->setLineStyle($linewidth, '', '', array());
				  //left
				  $pdf->line($mapOffset_left - $border, $mapOffset_bottom - $border, $mapOffset_left - $border, $mapOffset_bottom  + $map_height + $border);
				  //right
				  $pdf->line($mapOffset_left - $border, $mapOffset_bottom  + $map_height + $border , $mapOffset_left + $map_width + $border, $mapOffset_bottom + $map_height + $border);
				  //top
				  $pdf->line($mapOffset_left + $map_width + $border, $mapOffset_bottom + $map_height + $border , $mapOffset_left + $map_width + $border, $mapOffset_bottom - $border);
				   //bottom
				   $pdf->line($mapOffset_left + $map_width + $border, $mapOffset_bottom -$border , $mapOffset_left - $border, $mapOffset_bottom - $border);
				   
					if ($legendImage!=''){
						//image on top of page
						$pdf->addPngFromFile($legendImage, $mapOffset_left + $map_width + $border - $legendImage_width -6, $mapOffset_bottom + $map_height + $border - $legendImage_height -4 , $legendImage_width, $legendImage_height);
						//line under legend (only landscape)
						//$pdf->line($mapOffset_left - $border, $mapOffset_bottom + $map_height + $border  - $legendImage_height - 4 , $mapOffset_left + $map_width + $border, $mapOffset_bottom + $map_height + $border - $legendImage_height - 6);
					}
				}
			}// for layers
		} //if wms_id not 0
	}// for wms
	
	/****
    * USER POLYGON:
    */
   if($_REQUEST["measured_x_values"] != ""
      && $_REQUEST["measured_y_values"] != ""
        && is_file($legendFilenameUserPolygon)) {
     // load image
       $myY = $pdf->ezText("<b>temporary Object</b>", 11);
       $pdf->ezSetDy(-15);
       $pdf->ezImage($legendFilenameUserPolygon, 5, 17, 'none', 'left');
       if($unlink == true){
       		unlink($legendFilenameUserPolygon);
       }
       $pdf->ezSetY($myY - 7);
       
        $pdf->ezText("Element", 11, array("left" => 25));
             // deletes image
	    
	    $pdf->ezSetDy(-15);             
                    
   } 
	
$pdf->ezText($legendFooter, 11);
}//legend true
/* ------------------------ 
    end of legend  
   ------------------------ */

ob_clean();
if($download == false){
	$pdf->ezStream();
}
else{
	$content = $pdf->ezOutput();

	$fp = fopen($downloadFile,'w');
	fwrite($fp,$content);
	fclose($fp);
	echo "<html><head><META HTTP-EQUIV=Refresh CONTENT=\"0; URL='".$downloadFile."'\"><style type=\"text/css\">body { background-color:#FFFFFF; margin:10px;} p, a {  font: normal 14px arial, verdana, tahoma, sans-serif;  color: #000000;  border-width:0px;  border-style:solid;  border-color:#FFFFFF;  }  </style></head><body><p>Klicken Sie <a href='".$downloadFile."'>hier</a>, falls das PDF nicht automatisch ge&ouml;ffnet wird.</p></body></html>";
}
?>
