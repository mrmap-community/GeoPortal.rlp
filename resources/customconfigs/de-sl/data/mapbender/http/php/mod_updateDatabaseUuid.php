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

/*
ALTER TABLE wms ADD COLUMN uuid UUID;
ALTER TABLE layer ADD COLUMN uuid UUID;
ALTER TABLE wfs ADD COLUMN uuid UUID;
ALTER TABLE wfs_featuretype ADD COLUMN uuid UUID;
*/


require_once dirname(__FILE__) . "/../../core/globalSettings.php";
require_once dirname(__FILE__) . "/../classes/class_Uuid.php";
/*
//DROP uuid columns
$v = array();
$t = array();
$sql = "ALTER TABLE wms DROP COLUMN uuid;";
$res = db_prep_query($sql,$v,$t);

$v = array();
$t = array();
$sql = "ALTER TABLE layer DROP COLUMN uuid;";
$res = db_prep_query($sql,$v,$t);

$v = array();
$t = array();
$sql = "ALTER TABLE wfs DROP COLUMN uuid;";
$res = db_prep_query($sql,$v,$t);

$v = array();
$t = array();
$sql = "ALTER TABLE wfs_featuretype DROP COLUMN uuid;";
$res = db_prep_query($sql,$v,$t);

//generate new columns
$v = array();
$t = array();
$sql = "ALTER TABLE wms ADD COLUMN uuid UUID;";
$res = db_prep_query($sql,$v,$t);

$v = array();
$t = array();
$sql = "ALTER TABLE layer ADD COLUMN uuid UUID;";
$res = db_prep_query($sql,$v,$t);

$v = array();
$t = array();
$sql = "ALTER TABLE wfs ADD COLUMN uuid UUID;";
$res = db_prep_query($sql,$v,$t);

$v = array();
$t = array();
$sql = "ALTER TABLE wfs_featuretype ADD COLUMN uuid UUID;";
$res = db_prep_query($sql,$v,$t);

*/

//update wms table
$v = array();
$t = array();
$sql = "SELECT wms_id FROM wms WHERE uuid IS NULL;";
$res = db_prep_query($sql,$v,$t);
$countWmsWithoutUuid = 0;
while($row = db_fetch_array($res)){
		$wmsId = $row['wms_id'];
		$uuid = new Uuid();
		$vUpdate = array($uuid,$wmsId);
		$tUpdate = array('s');
		$sqlUpdate = "UPDATE wms set uuid = $1 WHERE wms_id = $2;";
		$resUpdate = db_prep_query($sqlUpdate,$vUpdate,$tUpdate);
		$countWmsWithoutUuid++;
}
echo $countWmsWithoutUuid." WMS updated!";
echo "<br>";
//end -- update wms table
//update layer table
$v = array();
$t = array();
$sql = "SELECT layer_id FROM layer WHERE uuid IS NULL;";
$res = db_prep_query($sql,$v,$t);
$countLayerWithoutUuid = 0;
while($row = db_fetch_array($res)){
		$layerId = $row['layer_id'];
		$uuid = new Uuid();
		$vUpdate = array($uuid,$layerId);
		$tUpdate = array('s');
		$sqlUpdate = "UPDATE layer set uuid = $1 WHERE layer_id = $2;";
		$resUpdate = db_prep_query($sqlUpdate,$vUpdate,$tUpdate);
		$countLayerWithoutUuid++;
}
echo $countLayerWithoutUuid." Layer updated!";
echo "<br>";
//end -- update layer table
//update wfs table
$v = array();
$t = array();
$sql = "SELECT wfs_id FROM wfs WHERE uuid IS NULL;";
$res = db_prep_query($sql,$v,$t);
$countWfsWithoutUuid = 0;
while($row = db_fetch_array($res)){
		$wfsId = $row['wfs_id'];
		$uuid = new Uuid();
		$vUpdate = array($uuid,$wfsId);
		$tUpdate = array('s');
		$sqlUpdate = "UPDATE wfs set uuid = $1 WHERE wfs_id = $2;";
		$resUpdate = db_prep_query($sqlUpdate,$vUpdate,$tUpdate);
		$countWfsWithoutUuid++;
}
echo $countWfsWithoutUuid." Wfs updated!";
echo "<br>";
//end -- update wfs table
//update featuretype table
$v = array();
$t = array();
$sql = "SELECT featuretype_id FROM wfs_featuretype WHERE uuid IS NULL;";
$res = db_prep_query($sql,$v,$t);
$countFeaturetypeWithoutUuid = 0;
while($row = db_fetch_array($res)){
		$featuretypeId = $row['featuretype_id'];
		$uuid = new Uuid();
		$vUpdate = array($uuid,$featuretypeId);
		$tUpdate = array('s');
		$sqlUpdate = "UPDATE wfs_featuretype set uuid = $1 WHERE featuretype_id = $2;";
		$resUpdate = db_prep_query($sqlUpdate,$vUpdate,$tUpdate);
		$countFeaturetypeWithoutUuid++;
}
echo $countFeaturetypeWithoutUuid." Featuretype updated!";
echo "<br>";
//end -- update featuretype table

?>
