<?php
echo	"<table style='padding-top:100px;margin-top:-15px' width='100%'><tr align='center'><td>" .
	"<img style='margin: 0 0 10px 0;' alt='indicator wheel' src='../img/ajax-loader.gif'><img style='padding:10px 15px' alt='geoportal_logo' src='../img/GeoportalHessen_logo_extern.png'><img style='margin: 0 0 10px 0;' alt='indicator wheel' src='../img/ajax-loader.gif'></td></tr>" . 
	"<tr align='center'><td><strong><p style='color:black;font-size:12.8px;font-family:Verdana,Sans Serif !important'>Bitte warten ...</p></strong></td></tr>" .
	"<tr align='center'><td><p style='color:black;font-size:12.8px;font-family:Verdana,Sans Serif !important'>Lade Anwendung: " . $this->guiId . "</p></td></tr>".
	"<tr align='center'><td style='padding:0px 0 0 0'><p style='color:black;font-size:12.8px;font-family:Verdana,Sans Serif !important;'>" .
	"Bei Problemen folgen Sie diesem " .
	"<a style='font-size:12.8px;text-decoration: underline;color:red;font-family:Verdana,Sans Serif !important;' href='http://".$_SERVER['HTTP_HOST']."/portal/hilfe/browsereinstellungen.html' target='_blank'>Link</a></p></td></tr>" .
//	"<tr align='center'><td style='padding:50px 0 0 0'><img alt='logo' src='../img/Mapbender2_poweredby_logo_129x30.png'></td></tr>
	"</table>";
?>
