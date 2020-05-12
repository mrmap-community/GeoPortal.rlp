<?php
	# $Id: index.php 7196 2010-12-11 09:58:21Z apour $
	# maintained by http://www.mapbender.org/index.php/User:Astrid_Emde
	# http://www.mapbender.org/index.php/Mapbender_Portal
	#
	# Copyright 2009, Open Source Geospatial Foundation. All rights reserved.
	#
	# This program is dual licensed under the GNU General Public License: 
	# http://svn.mapbender.org/trunk/mapbender/gpl.txt
	# and Simplified BSD license:
	# http://svn.osgeo.org/mapbender/trunk/mapbender/license/simplified_bsd.txt
	#
	# This file is part of Mapbender.

	include_once dirname(__FILE__)."/../core/system.php";

	/*****************************************************/
	// Laedt die HTML Datei

	if(isset($_REQUEST['lang'])) $lang = $_REQUEST['lang']; // Sprachunterscheidung
	else $lang = 'de_DE';


	$pageULR = 'http://'.$_SERVER["HTTP_HOST"].'/'; // URL der Seite

	if(strstr($lang,'en')) $htmlstring = file_get_contents($pageULR.'index.php/en/cardviewer/kartenvieweren?sid='.session_id());
	else if(strstr($lang,'fr')) $htmlstring = file_get_contents($pageULR.'index.php/fr/cardvue/kartenviewerfr?sid='.session_id());
	else $htmlstring = file_get_contents($pageULR.'index.php/de/kartenviewer/kartenviewerde?sid='.session_id());



	// GET HEADER - START
	ob_start();
?>
<meta name="author" content="The Mapbender Development Team">
<meta name="description" content="Initial start page for the geoportal software Mapbender">
<meta name="keywords" content="Mapbender, Geoportal, GIS, SDI, GDI, OSGeo, Free Software, OGC, WMS, WFS, WFS Transactional, WMS Client, Digitizing"> 
<meta http-equiv="cache-control" content="no-cache">
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="expires" content="0">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Welcome to the Mapbender Portal</title>
<link rel="stylesheet" type="text/css" href="css/mapbender.css">
<link rel="shortcut icon" href="img/favicon.ico">
<script type="text/javascript">
<!--
function changeTarget()
{
	if (window.frames[0].document.forms['loginForm'].target){  
		window.frames['login'].document.forms['loginForm'].setAttribute("target","_blank");
	}
	else{
		window.frames[0].document.forms['loginForm'].setAttribute("target","_blank");
	}
}
// -->
</script>
<?php
	// GET HEADER - END
	$HEAD = ob_get_contents();
	ob_end_clean();

	// GET BODY - START
	ob_start();
?>
<table width="70%" align="center" cellpadding="10" style="-moz-border-radius:8px; border:0px #000000 solid;">
	<tr>
		<td colspan="2">
			<div class="mapbender_headline" >
				<font color="#000000">Welcome to Ma</font><font color="#0000CE">p</font><font color="#C00000">b</font><font color="#000000">ender</font>
			</div>
		</td>
	</tr>
	<tr>
		<td width="35%">
			<br>
			<a href="http://www.mapbender.org" target="_blank"><b>Homepage</b></a><br>
			<br>
			<a href="http://www.mapbender.org/index.php/Tutorials" target="_blank"><b>Documentation</b></a><br>
			<br>
			<a href="http://www.mapbender.org/Mapbender_Gallery" target="_blank"><b>Application Gallery</b></a><br>
			<br>
			<a href="http://wms.wheregroup.com/mapbender/frames/login.php?name=mb&password=mb&mb_user_myGui=mapbender_user" target="_blank"><b>Mapbender User Map</b></a><br>
			<br>
			<a href="http://www.osgeo.org/mapbender" target="_blank"><b>OSGeo InfoSheet</b></a><br>		
			<br>
			<br>
			<br>
			<a href="http://www.mapbender.org/download/" target="_blank"><b>Download</b></a><br>
			<br>
			<a href="http://www.mapbender.org/index.php/SVN" target="_blank"><b>Source Code Repository</b></a><br>
			<br>
			<a href="http://lists.osgeo.org/mailman/listinfo/mapbender_users" target="_blank"><b>User Mailing List</b></a><br>
			<br>
			<a href="http://lists.osgeo.org/mailman/listinfo/mapbender_dev" target="_blank"><b>Devel Mailing List</b></a><br>
			<br>
			<a href="http://www.mapbender.org/index.php/Bugs" target="_blank"><b>Bug &amp; Issue Tracker</b></a><br>
			<br>
			<br>
			<br>
			<a href="http://mapbender.osgeo.org/" target="_blank"><b>Mapbender Development Server</b></a><br>
			<br>
			<a href="http://www.osgeo.org" target="_blank">
			<br>
			<img  title="Link to the OSGeo portal" src = "./img/OSGeo_project.png" alt="" />
			</a>
			<br><br>
			Mapbender is an official OSGeo Project licensed under the <a class="a_small" href="http://svn.mapbender.org/trunk/mapbender/gpl.txt" title="GNU GPL license" alt="Link to the GNU license">GNU GPL</a> and <a class="a_small" href="http://svn.osgeo.org/mapbender/trunk/mapbender/license/simplified_bsd.txt">Simplified BSD license</a>
			<br>
		</td>
		<td WIDTH="65%" STYLE="line-height:133%">
			<br>
			<div>	
				<font color="#ff0000" size="3">Mapbender Version <?php echo MB_VERSION_NUMBER." ".MB_VERSION_APPENDIX?><br><br> (<?php echo date("F jS, Y",MB_RELEASE_DATE);?>)</font>
			</div>	
			<br>
			<p>
				<font color="#000000">Ma</font><font color="#0000CE">p</font><font color="#C00000">b</font><font color="#000000">ender</font> is the geospatial portal site management software for OGC OWS architectures. Mapbender has a data model to manage, display, navigate, query and catalog OGC compliant web map and feature services (WMS and transactional WFS). The basic authentication module can be connected with existing systems and allows multi client capable user manangement, authorization, group and service administration. <a class='a_small' href="http://www.mapbender.org/index.php/Mapbender_Security_Proxy" title="Mapbender OWS Security Proxy">OWS Security Proxy</a> acts as a facade for OGC services and controls access and logs operations on services and layers on user and group basis.
			</p>
			<br>
			<table width="75%" cellspacing="1" cellpadding="4" STYLE="-moz-border-radius:6px; border:1px #DFDFDF solid;">
				<tr>
					<td>
						<font color="#000000">Ma</font><font color="#0000CE">p</font><font color="#C00000">b</font><font color="#000000">ender</font> Login
						<br>
						<iframe  id='login' name='login' scrolling="no" frameborder='0' src ='http://93.89.10.171/mapbender/frames/login.php' style ='width:400px;height:100px;' >
						</iframe>
					</td>
				</tr>
			</table>
			<br>
			<table width="75%" cellspacing="1" cellpadding="4" style="-moz-border-radius:6px; border:1px #DFDFDF solid;">
				<tr>
					<td>		
					Create new user (neuen Benutzer anlegen)

					<iframe  id='createUser' name='createUser' scrolling="auto" frameborder='0' src ='http://93.89.10.171/mapbender/php/mod_createUser.php' style ='width:400px;height:242px;' >
					</iframe>
			</table>
			<br>
			<table width="75%" cellspacing="1" cellpadding="4" style="-moz-border-radius:6px; border:0px #808080 solid;">
				<tr>
					<td>
						Have a lot of fun with <font align="left" color="#000000">Ma</font><font color="#0000CE">p</font><font color="#C00000">b</font><font color="#000000">ender</font> !
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<?php
	// GET BODY - END
	$BODY = ob_get_contents();
	ob_end_clean();

	$htmlstring = str_replace('[%MB_HEADER%]', $HEAD, $htmlstring);
	$htmlstring = str_replace('[%MB_CONTENT%]', $BODY, $htmlstring);
	echo $htmlstring;
?>