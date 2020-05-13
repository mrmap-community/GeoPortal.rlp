<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Abfrageergebnis</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">

<style type="text/css">
body { background-color:#FFFFFF; font: 13px arial, sans-serif, helvetica; margin: 10px; }
table.eignung { border:solid 2px #FFCC66; background:#FFF1D2; border-collapse:collapse; }
table.rechner { border:solid 2px #FFCC66; background:#FFF1D2; text-align:right; border-collapse:collapse; }
th { border-bottom:solid 1px #9D1D16; border-right:solid 1px #9D1D16; border-left:solid 1px #9D1D16; background:#FFF1D2; text-align:center; padding: 5px; }
td { font-weight: normal; color: #000000; border:solid 1px #9D1D16; padding: 5px; background:#FFF1D2; }  
p {  margin-top: 10px; margin-bottom: 10px; }
h1 { font-weight:bold}
td.Gebäude { background-color:#CCCCCC } /* Grey */
td.bedingt  { background-color:#ffff00 } /* Gelb */
td.geeignet  { background-color:#ffff00 } /* Gelb */
td.gut { background-color:#FF8C00 } /* DarkOrange */
<?php
   if ( $_GET['eignung'] == 'sehr gut geeignet' ) {
   echo "td.sehr { background-color:#FF0000 } /* RED */";
   }
   else {
   echo "td.sehr { background-color:#FF8C00 } /* DarkOrange */";
   }
?>
td.hervorragend  { background-color:#FF0000 } /* Red */ 
</style>

</head>

<script type="text/javascript">

function popupPDF(url) {
  args = 'width=550,height=800,resizable,scrollbars';
  window.open(url,'Eignung',args);
}
</script>
	
<body>

<?php
//Dateiname der Konfigurationsdatei! Enthaelt Jahr, Nennleistung, Modulpreis!
require_once(dirname(__FILE__)."/saarlouis_pv_calc.conf");

$modulf = $_GET['modulf'];
$modulf09 = $modulf;
$modulf15 = $modulf;

$empfehl = $_GET['empfehl'];
$stro09 = $_GET['stro09'];
$stro15 = $_GET['stro15'];
$co209 = $_GET['co209'];
$co215 = $_GET['co215'];
$gemnam = strtolower($_GET['gemnam']);
$usernam = strtolower($_GET['usernam']);

$modulffl = $_GET['modulffl'];
$stro15fl = $_GET['stro15fl'];
$co215fl = $_GET['co215fl'];
$dachtyp = $_GET['dachtyp'];

$stwendel = array( "frs", "mar", "nam", "noh", "non", "oth", "tho", "wen" );
$neunkirchen = array( "epl", "ill", "mer", "sfw", "spe", "neu", "ott" );
$saarlouis = array( "sls", "bou", "wad", "wal", "ueb", "slb", "slz", "dil", "leb", "nal", "ens", "swl", "res" );

#if ( $usernam == $gemnam OR 
#     $usernam == 'root' OR
#     $usernam == 'lkvk' OR
#     ( in_array( $gemnam,$stwendel ) AND ( $usernam == 'lkwnd' OR $usernam == 'wfwnd' ) ) OR
#     ( in_array( $gemnam,$saarlouis ) AND ( $usernam == 'wfus1' OR $usernam == 'wfus2' ) ) OR
#     ( in_array( $gemnam,$neunkirchen ) AND ( $usernam =='lknk' OR $usernam == 'wfnk' ) ) ) {

	if ( $dachtyp == 'Flachdach' ) {
	   $stro15 = $stro15fl;
	   $co215 = $co215fl;
	   $modulf15 = $modulffl;
	   $invest15 = $modulf15 / 7 * 3250;
	}
	
	if ( $empfehl == 'Dünnschicht') {
	   $strom = $stro09;
	   $co2 = $co209;
	   $invest = $invest09;
	   $nennleistung = $nennleistung09;
	   $empfehl1 = "Dünnschicht";
	}
	else {
	   $strom = $stro15;
	   $co2 = $co215;
	   $invest = $invest15;
	   $nennleistung = $nennleistung15;
	   $empfehl1 = "Kristallin";
	}
	
	$eignung = $_GET['eignung'];          //Solareignung
	$x = $_GET['x'];                      //Angeklickter Rechtswert
	$y = $_GET['y'];                      //Angeklickter Hochwert
	
	$heute = date("Ymd");

	if ( $dachtyp == 'Flachdach' ) {
	 	$dachtypAnzeige = "<li>Geb&auml;ude hat Flachdachanteile. Es wird von einer Aufst&auml;nderung bei kristallinen Modulen ausgegangen. Dadurch verringert sich die Modulfl&auml;che.</li>";
	}
	if ( $dachtyp == 'geneigtes Dach' ) { 
		$dachtypAnzeige = "<li>Geneigtes Dach</li>";
	}	
	if ( $empfehl1 != '' ) {	
		$empfehl1 =  "<li>Empfohlener Modultyp: ".$empfehl1. "</li>";
	}

	$print="../../print_solarkataster/mod_printPDF.php?target=mapframe1&sessionID&conf=printPDF_b.conf&x=".$x."&y=".$y."&thema=Photovoltaik&strom09=".$stro09."&strom15=".$stro15."&co209=".$co209."&co215=".$co215."&eignung=".$eignung."&modulf=".$modulf."&modulf09=".$modulf09."&modulf15=".$modulf15."&dachtyp=".$dachtyp."&empfehl=".$empfehl."&denkmal=".$denkmal."&usernam=".$usernam;
?>

<table class="eignung" width="600">

  <tr>
    <th class="eignung" colspan=2>Eignung Photovoltaik</th>
    <th class="eignung">Modulfläche</th>
    <th class="eignung">kWp</th>
	<th class="eignung">Modultyp</th>
	<th class="eignung">Stromertrag (kWh/Jahr)</th>
	<th class="eignung">CO<sub>2</sub>-Einsparung (kg/Jahr)</th>
  </tr>
  
  <tr>
    <td class=<?php echo $eignung; ?> width=22 rowspan=2></td>
	<td class="eignung" style="text-align:right" rowspan=2><nobr><b><?php echo $eignung; ?></b></nobr></td>
	<td class="eignung" style="text-align:right"><nobr><?php echo $modulf15; ?> m²</nobr></td>
    <td class="eignung" style="text-align:right"><nobr><?php echo round($modulf15/7.7,1); ?></nobr></td>
	<td class="eignung" style="text-align:right"><nobr>Kristallin</nobr></td>
	<td class="eignung" style="text-align:right"><nobr><?php echo $stro15; ?></nobr></td>
	<td class="eignung" style="text-align:right"><nobr><?php echo $co215; ?></nobr></td>
   </tr>

  <tr>
    <td class="eignung" style="text-align:right"><nobe><?php echo $modulf09; ?> m²<nobr></td>
	<td class="eignung" style="text-align:right"><nobr><?php echo round($modulf15/10.8,1); ?></nobr></td>
	<td class="eignung" style="text-align:right"><nobr>Dünnschicht</nobr></td>
	<td class="eignung" style="text-align:right"><nobr><?php echo $stro09; ?></nobr></td>
	<td class="eignung" style="text-align:right"><nobr><?php echo $co209; ?></nobr></td>
   </tr>
   
   <tr>
     <td colspan=6 style="border-right:0px;">
	 <ul>
	 <?php
	    echo $dachtypAnzeige;
	    echo $empfehl1;
	 ?>
	 </ul>
	 </td>
	 <td colspan=1 align=right style="border-left:0px;">
	   <img id='printPDF' style='cursor:pointer;' name='printPDF' onclick="popupPDF('<?php echo $print ?>');" onmouseover='this.src = this.src.replace(/_off/,"_over");style.cursor="pointer"' onmouseout='this.src = this.src.replace(/_over/, "_off");style.cursor="default"' title='PDF Druck' src = 'img/print_sandig_off.png' >
	 </td>
   </tr>
</table>

<p>Die errechneten Potenziale dienen nur als Erstinformation und sind nicht als verbindlich anzusehen. Sie sind kein Ersatz 
f&uuml;r eine Pr&uuml;fung durch eine Fachfirma vor Ort.</p>

<?php
#}
#else {
#	echo "Sie k&ouml;nnen nur Daten in Ihrer eigenen Gemeinde abfragen.";
#}

?>

</body>
</html>
