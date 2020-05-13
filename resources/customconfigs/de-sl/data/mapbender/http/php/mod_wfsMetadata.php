<?php
# http://www.mapbender.org/index.php/Administration
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
require_once dirname(__FILE__) . "/../../core/globalSettings.php";

$con = db_connect(DBSERVER,OWNER,PW);
db_select_db(DB,$con);

function display_text($string) {
    $string = eregi_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]", "<a href=\"\\0\" target=_blank>\\0</a>", $string);   
    $string = eregi_replace("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@([0-9a-z](-?[0-9a-z])*\.)+[a-z]{2}([zmuvtg]|fo|me)?$", "<a href=\"mailto:\\0\" target=_blank>\\0</a>", $string);   
    $string = eregi_replace("\n", "<br>", $string);
    return $string;
}  

?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de">
	<head>
		<title>WFS Metadata</title>
		<meta name="description" content="Metadata" xml:lang="de" />
		<meta name="keywords" content="Metadata" xml:lang="de" />		
		<meta http-equiv="cache-control" content="no-cache">
		<meta http-equiv="pragma" content="no-cache">
		<meta http-equiv="expires" content="0">
		<meta http-equiv="content-language" content="de" />
		<meta http-equiv="content-style-type" content="text/css" />		
<?php
	echo '<meta http-equiv="Content-Type" content="text/html; charset='.CHARSET.'">';	
?>
		
	</head>
	<body id="top">

	
	<div>
	<a href="javascript:window.print()">Print <img src="../img/search/icon_print.gif" width="14" height="14" alt="" /></a>
	<a href="javascript:window.close()">Close <img src="../img/search/icon_close.gif" width="14" height="14" alt="" /></a>
	</div>
	<div></div>
	<div></div>
	
	<div>
<?php
	$wfs_conf_id = $_GET['wfs_conf_id'];
	//for testing only
	#$wfs_conf_id = 1;
	$wfs_id = $_GET['wfs_id'];
	//for testing only
	#$wfs_id = 1;
	
	$sql_id = "SELECT * FROM wfs WHERE wfs_id = $1";
	$v_id = array($wfs_id);
	$t_id = array('i');
	$res_wfs = db_prep_query($sql_id,$v_id,$t_id);
	$row_wfs = db_fetch_array($res_wfs);
	$wfs = array();
	
	$sql_dep = "SELECT mb_group_name FROM mb_group AS a, mb_user AS b, mb_user_mb_group AS c WHERE b.mb_user_id = $1  AND b.mb_user_id = c.fkey_mb_user_id AND c.fkey_mb_group_id = a.mb_group_id AND b.mb_user_department = a.mb_group_description LIMIT 1";
	$v_dep = array($row_wfs['wfs_owner']);
	$t_dep = array('i');
	$res_dep = db_prep_query($sql_dep, $v_dep, $t_dep);
	$row_dep = db_fetch_array($res_dep);
	$wfs['WFS ID'] = $row_wfs['wfs_id'];
	$wfs['WFS Titel'] = $row_wfs['wfs_title'];
	$wfs['WFS Zusammenfassung'] = $row_wfs['wfs_abstract'];
	//$featuretype['Koordinatensysteme'] = $row_wfs['featuretype_srs'];
	//$featuretype['Geometrietyp'] = $geomType;

	$wfs['Capabilities-Dokument'] = "<a href = '../geoportal/getCapabilities_wfs.php?wfs_id=".$row_wfs['wfs_id']."' target=_blank>Capabilities-Dokument</a>";
	//$featuretype['DescribeFeature-Dokument'] = "<a href = '../geoportal/describeFeatureType_wfs.php?wfs_id=".$row['wfs_id']."&featureType_id=".$row['featuretype_id']."' target=_blank>DescribeFeature-Dokument</a>";
	if ($row_wfs['wfs_timestamp']) {
		$layer['Datum der Registrierung'] = date("d.m.Y",$row_wfs['wfs_timestamp']); 
	}
	else {
		$layer['Datum der Registrierung'] = "Keine Angabe"; 
	}
	$wfs['Registrierende Stelle'] = $row_dep['mb_group_name'];
	
	$wfs['Geb&uuml;hren'] = $row_wfs['fees'];
	$wfs['Zugriffsbeschr&auml;nkung'] = $row_wfs['accessconstraints'];
	$wfs['Ansprechpartner'] = $row_wfs['individualname'];
	$wfs['Organisation'] = $row_wfs['providername'];
	$wfs['Adresse'] = $row_wfs['deliverypoint'];
	$wfs['Stadt'] = $row_wfs['city'];
	$wfs['PLZ'] = $row_wfs['postalcode'];
	$wfs['Telefon'] = $row_wfs['voice'];
	$wfs['Fax'] = $row_wfs['facsimile'];
	$wfs['E-Mail'] = $row_wfs['electronicmailaddress'];
	$wfs['Land'] = $row_wfs['country'];
	
	echo "<table class='contenttable-0-wide'>\n";
	$t_a = "\t<tr>\n\t\t<th>\n\t\t\t";
	$t_b = "\n\t\t</th>\n\t\t<td>\n\t\t\t";
	$t_c = "\n\t\t</td>\n\t</tr>\n";

	$keys = array_keys($wfs);
	for ($j=0; $j<count($wfs); $j++) {
		echo $t_a . utf8_encode($keys[$j]) . $t_b . display_text($wfs[$keys[$j]]) . $t_c;
	}
	
	echo "</td></tr></table>\n";
?>
	</div>
	<div></div>
	</body>
</html>
