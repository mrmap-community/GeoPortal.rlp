<html>

<head>

<?php
echo '<meta http-equiv="Content-Type" content="text/html; charset='.CHARSET.'">';
#require_once(dirname(__FILE__)."/../../conf/mapbender.conf");
#require_once(dirname(__FILE__)."/../classes/class_administration.php");
#require_once(dirname(__FILE__)."/../php/mb_validateSession.php");
require_once(dirname(__FILE__)."/../../core/globalSettings.php");
require_once(dirname(__FILE__)."/../classes/class_administration.php");
require_once dirname(__FILE__) . "/../classes/class_Uuid.php";
require_once dirname(__FILE__) . "/../classes/class_user.php";
?>

<title>Statistik Geoportal</title>

<style type="text/css">
body
{
font-family: Arial, Helvetica, sans-serif;	
}
h1
{
color: #A52A2A;
font-family: arial, verdana, sans serif;
font-style: italic;
font-weight: bold;
font-size: 175%;
}
</style>

<script language="JavaScript" type="text/javascript">
</script>

</head>

<body>

<table>

<?php



    $sql = "SELECT users,total_authorities,substitute_authorities,publishing_authorities,wms,wms_layer,wfs,wfs_modul,wmc FROM statistik;";
    $v = array();
    $t = array();
    $res = db_prep_query($sql, $v, $t);
	while($row = db_fetch_array($res)) {
		$users=$row['users'];
		$total_authorities=$row['total_authorities'];
		$substitute_authorities=$row['substitute_authorities'];
		$publishing_authorities=$row['publishing_authorities'];
		$wms=$row['wms'];
		$wms_layer=$row['wms_layer'];
		$wfs=$row['wfs'];
		$wfs_modul=$row['wfs_modul'];
		$wmc=$row['wmc'];
	}

echo "<form  method=\"POST\" action=".$_SERVER['PHP_SELF'].">";
echo " <h1>Statistik GeoPortal.rlp</h1>";
echo "<table border='1'>";

echo "<tr align=left height=50>";
echo "<td>";
echo "<font size=\"4\"><b>users</b></font>";
echo "</td>";
echo "<td>";
echo "<font size=\"4\"><b>total_authorities</b></font>";
echo "</td>";
echo "<td>";
echo "<font size=\"4\"><b>substitute_authorities</b></font>";
echo "</td>";
echo "<td>";
echo "<font size=\"4\"><b>publishing_authorities</b></font>";
echo "</td>";
echo "<td>";
echo "<font size=\"4\"><b>wms</b></font>";
echo "</td>";
echo "<td>";
echo "<font size=\"4\"><b>wms_layers</b></font>";
echo "</td>";
echo "<td>";
echo "<font size=\"4\"><b>wfs</b></font>";
echo "</td>";
echo "<td>";
echo "<font size=\"4\"><b>wfs_module</b></font>";
echo "</td>";
echo "<td>";
echo "<font size=\"4\"><b>wmc</b></font>";
echo "</td>";
echo "</tr>";
echo "<tr align=left height=50>";
echo "<td>";
echo "<font color=#000000 size=\"3\">".$users."</font>";
echo "</td>";
echo "<td>";
echo "<font color=#000000 size=\"3\">".$total_authorities."</font>";
echo "</td>";
echo "<td>";
echo "<font color=#000000 size=\"3\">".$substitute_authorities."</font>";
echo "</td>";
echo "<td>";
echo "<font color=#000000 size=\"3\">".$publishing_authorities."</font>";
echo "</td>";
echo "<td>";
echo "<font color=#000000 size=\"3\">".$wms."</font>";
echo "</td>";
echo "<td>";
echo "<font color=#000000 size=\"3\">".$wms_layer."</font>";
echo "</td>";
echo "<td>";
echo "<font color=#000000 size=\"3\">".$wfs."</font>";
echo "</td>";
echo "<td>";
echo "<font color=#000000 size=\"3\">".$wfs_modul."</font>";
echo "</td>";
echo "<td>";
echo "<font color=#000000 size=\"3\">".$wmc."</font>";
echo "</td>";
echo "</tr>";




echo "</form>";	
?>
</table>
</body>
</html>


