<!DOCTYPE html>

<html lang="de" xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
		<title>www.geoportal.sl.de | Metadaten</title>
		<base href="<?php print "http://".$_SERVER["SERVER_NAME"]."/mapbender/template/"; ?>" />
		<meta name="author" content="Q4U GmbH" />
		<meta name="publisher" content="Saarland" />
		<meta name="copyright" content="Saarland" />
		<meta name="description" content="Metadaten" />
		<meta name="keywords" content="Metadaten" />
<!--		<link rel="stylesheet" type="text/css" href="fileadmin/design/geoportal.rlp.css" /> -->
		<link rel="stylesheet" type="text/css" href="css/template_erw_suche.css" /> 
		<script type="text/javascript">
			var OK=false;
			function scrolling(el) {
				var height = el.scrollHeight;
				var scroll = el.scrollTop;
//				var height = 500;
//				var scroll = 300;
				var diff = el.scrollHeight - el.scrollTop - el.clientHeight;
				if(diff<=0) { 
					OK=true; 
					btn=document.getElementById('button'); 
					if(btn) { 
						btn.className='buttonvisible'; 
					} 
				} 
			} 
		</script>
		<style type="text/css">
			.buttonhidden{color:#888888;}
			.buttonvisible{color:#0000ff;}
		</style>
</head>

<body id="top" class="popup" onload="scrolling(document.getElementById('scroller'))">
	<div id="blue"></div>
	<a id="print" href="javascript:window.print()">Drucken <img src="../img/search/icn_print.png" width="18" height="18" alt="" /></a>
	<a id="close" href="javascript:window.close()">Fenster schließen <img src="../img/search/icn_close.png" width="18" height="18" alt="" /></a>
	<div class="content">
		<?php 
			// TermsOfUse einlesen 
			//include(dirname(__FILE__)."/../function/crypt.php");
				if($_REQUEST["tou"]!="") {
					DecodeParameter($_REQUEST["tou"]);
				}
				//var_dump($_REQUEST);
				//print "---".$_REQUEST["id"]."---".$_REQUEST["el"]."---".$_REQUEST["ac"]."---".$_REQUEST["wms_id"]."---".$_REQUEST["LAYER"]."---".$_REQUEST["url"]."---";
				$_SERVER["HTTP_HOST"]='localhost';
				$OUT=file_get_contents("http://".$_SERVER["HTTP_HOST"]."/mapbender/php/mod_getServiceDisclaimer.php?type=".$_REQUEST["type"]."&id=".$_REQUEST["id"]."&languageCode=".$_REQUEST['lang']."&withHeader=true");
				print '
				<div id="scroller" style="height:380px;overflow:auto" onscroll="scrolling(this)">
				'.$OUT.'
				</div>
				';

				if($_REQUEST["el"]!="") {
					if($_REQUEST["link"]=="1") {
						if(!$_REQUEST["LAYERzoom"]) {
							print '<a id="button" class="buttonhidden" href="javascript:{opener.touoklink(\'http://'.$_SERVER["SERVER_NAME"].$_REQUEST["url"].'&LAYER[id]='.$_REQUEST["LAYER"].'&callId='.$_REQUEST["callId"].' \','.$_REQUEST["id"].',\''.$_REQUEST["type"].'\');window.close();}" onclick="return OK;">Akzeptieren</a>';
						} else {
							print '<a id="button" class="buttonhidden" href="javascript:{opener.touoklink(\'http://'.$_SERVER["SERVER_NAME"].$_REQUEST["url"].'&LAYER[zoom]='.$_REQUEST["LAYERzoom"].'&LAYER[id]='.$_REQUEST["LAYER"].'&callId='.$_REQUEST["callId"].' \','.$_REQUEST["id"].',\''.$_REQUEST["type"].'\');window.close();}" onclick="return OK;">Akzeptieren</a>';
						}
					} elseif($_REQUEST["link"]=="2") {
						print '<a id="button" class="buttonhidden" href="javascript:{opener.touokdirect(\''.$_REQUEST["el"].'\','.$_REQUEST["id"].',\''.$_REQUEST["type"].'\');window.close();}" onclick="return OK;">Akzeptieren!</a>';
					} else {
						print '<a id="button" class="buttonhidden" href="javascript:{opener.touok(\''.$_REQUEST["el"].'\','.$_REQUEST["id"].',\''.$_REQUEST["type"].'\');window.close();}" onclick="return OK;">Akzeptieren</a>';
					}
				}
			?>

	</div>
</body>
</html>