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
td.geeignet { background-color:#BA55D3 } /* MediumOrchid */
td.sehr  { background-color:#BA55D3 } /* MediumOrchid */ 
ul, li { margin:0px }
</style>

</head>

<script type="text/javascript">

function popupPDF(url) {
	args = 'width=550,height=800,resizable,scrollbars';
  	url = url.replace("ß", "%DF");  
  	ok = window.open(url,'Eignung',args);
  	if (ok) return false;
  	else return true;  
}
</script>
	
<body>

<?php

$modulf = $_GET['modulf']; //           Modulflaeche
$eignung = $_GET['eignung']; //         Solareignung
$x = $_GET['x']; //                     Angeklickter Rechtswert
$y = $_GET['y']; //                     Angeklickter Hochwert

$gemnam = strtolower($_GET['gemnam']);
$usernam = strtolower($_GET['usernam']);

$merzigwadern = array( "bec", "los", "mzg", "mtl", "prl", "wdn", "wsk" );
$stwendel = array( "frs", "mar", "nam", "noh", "non", "oth", "tho", "wen" );
$neunkirchen = array( "epl", "ill", "mer", "sfw", "spe", "neu", "ott" );
$saarlouis = array( "sls", "bou", "wad", "wal", "ueb", "slb", "slz", "dil", "leb", "nal", "ens", "swl", "res" );

#if ( $usernam == $gemnam OR
#     $usernam == 'root' OR
#     $usernam == 'lkvk' OR
#     ( in_array( $gemnam,$stwendel ) AND ( $usernam == 'lkwnd' OR $usernam == 'wfwnd' ) ) OR
#     ( in_array( $gemnam,$saarlouis ) AND ( $usernam == 'wfus1' OR $usernam == 'wfus2' ) ) OR
#     ( in_array( $gemnam,$neunkirchen ) AND ( $usernam =='lknk' OR $usernam == 'wfnk' ) ) ) {

	$print="../../print_solarkataster/mod_printPDF.php?target=mapframe1&sessionID&conf=printPDF_b.conf&x=".$x."&y=".$y."&thema=Solarthermie&co2=".$co2."&eignung=".$eignung."&modulf=".$modulf."&denkmal=".$denkmal."&info=".$info."&usernam=".$usernam;
?>

<table class="eignung" width="500">
  <tr>
    <th class="eignung" colspan=2>Eignung Solarthermie</th>
    <th class="eignung">Modulfl&auml;che</th>
    <!--
    <th class="eignung">W&auml;rmemenge<br>(kWh/m² Jahr)</th>
    <th class="eignung">CO<sub>2</sub>-Einsparung<br>(kg/m² Jahr)</th>
    -->
  </tr>
  <tr>
    <td class=<?php echo $eignung; ?> width=22></td>
    <td class="eignung" style="text-align:right"><nobr><b><?php echo $eignung ?></b></nobr></td>
    <td class="eignung" style="text-align:right"><nobr><?php echo $modulf ?> m²</nobr></td>
    <!--
    <td class="eignung" style="text-align:right"><nobr><?php echo $waermm2 ?></nobr></td>
    <td class="eignung" style="text-align:right"><nobr><?php echo $co2 ?></nobr></td>
    -->
   </tr>
   <tr>
     <td colspan=2 style="border-right:0px;">
	 </td>
	 <td colspan=1 align=right style="border-left:0px;">

	 <img id='printPDF' style='cursor:pointer;' name='printPDF' onclick="return popupPDF('<?php echo $print ?>');" onmouseover='this.src = this.src.replace(/_off/,"_over");' onmouseout='this.src = this.src.replace(/_over/, "_off");' title='PDF Druck' src = 'img/print_sandig_off.png' >
	 </td>
   </tr>
</table>

<p>Die errechneten Potenziale dienen nur als Erstinformation und sind nicht als verbindlich anzusehen. Sie sind kein Ersatz 
f&uuml;r eine Pr&uuml;fung durch eine Fachfirma vor Ort.</p>

<?php
#}
#else
#echo "Sie k&ouml;nnen nur Daten in Ihrer eigenen Gemeinde abfragen.";
?>

</body>
</html>
