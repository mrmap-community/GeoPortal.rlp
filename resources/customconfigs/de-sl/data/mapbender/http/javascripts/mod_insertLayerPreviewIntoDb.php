<?php
#$Id: mod_insertLayerPreviewIntoDb.php 4805 2009-10-20 11:52:57Z christoph $
#$Header: /cvsroot/mapbender/mapbender/http/javascripts/mod_insertWmcIntoDb.php,v 1.19 2006/03/09 14:02:42 uli_rothstein Exp $
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
include(dirname(__FILE__)."/../../conf/geoportal.conf");
require_once(dirname(__FILE__)."/../classes/class_administration.php");
require_once(dirname(__FILE__)."/../classes/class_connector.php");

function savePreview($fileName, $fileContent) {
	if (strlen($fileContent) > 0) {
		$fileMapImg = fopen("..".LAYER_PREVIEW_URL."/".$fileName, 'w+');
		if ($fileMapImg) {
			rewind($fileMapImg);
			$bytesWritten = fwrite($fileMapImg, $fileContent);
			if ($bytesWritten) {
				fflush($fileMapImg);
				ftruncate($fileMapImg, ftell($fileMapImg));
				fclose($fileMapImg);
				return true;
			}
			$e = new mb_exception("..".LAYER_PREVIEW_URL."/".$fileName.": 0 bytes written.");
			return false;
		}
		$e = new mb_exception("..".LAYER_PREVIEW_URL."/".$fileName.": could not open.");
		return false;
	}
	$e = new mb_exception($fileName.": no file content.");
	return false;
}

if ($_POST["data"]) {
	$d = explode("____", $_POST["data"]);	

	$mapurl = $d[0];
	$legendurl = $d[1];
	
	$mapurl = eregi_replace("(&width=)[0-9]+($|[^0-9])", "\\1".LAYER_PREVIEW_WIDTH."\\2", $mapurl);
	$mapurl = eregi_replace("(&height=)[0-9]+($|[^0-9])", "\\1".LAYER_PREVIEW_HEIGHT."\\2", $mapurl);
		
	$adm = new administration();
	$layer_id = Mapbender::session()->get("layer_preview");
	if (!$layer_id) {
		 echo "<script>alert('Could not find wms: ".$wms_getmap."');</script>";
	}
	else {
//		session_write_close();
		$con1 = new connector($mapurl);
		$fileNameMap = $layer_id."_layer_map_preview.png";
		$fileContentMap = $con1->file;
		$success = savePreview($fileNameMap, $fileContentMap);
		if (!$success) $fileNameMap = "";
		
		$con2 = new	connector($legendurl);
		$fileNameLegend = $layer_id."_layer_legend_preview.png";
		$fileContentLegend = $con2->file;
		$success = savePreview($fileNameLegend, $fileContentLegend);
		if (!$success) $fileNameLegend = "";
 		
//		$rlp_4326_box = array(6.10988942079081,48.987785376052,8.58790010810365,50.9273496139233);
//		$rlp_4326_box = array(6.05,48.9,8.6,50.96);
		$sl_4326_box = array(6.2,48.7,7.5,50);
		
		$sql = "SELECT * FROM layer_epsg WHERE fkey_layer_id = $1 AND epsg = 'EPSG:4326'";
		$v = array($layer_id);
		$t = array('i');
		$res = db_prep_query($sql, $v, $t);
		$row = db_fetch_array($res);
		if ($row['minx'] && $row['miny'] && $row['maxx'] && $row['maxy']) {
			$extent_layer_id = $layer_id;
			$layer_4326_box = array($row['minx'], $row['miny'], $row['maxx'], $row['maxy']);
		}
		else {
			$sql = "SELECT * FROM (SELECT fkey_wms_id FROM layer WHERE layer_id = $1 LIMIT 1) AS w, layer_epsg AS e, layer AS l WHERE l.fkey_wms_id = w.fkey_wms_id AND l.layer_pos = 0 AND l.layer_id = e.fkey_layer_id AND e.epsg = 'EPSG:4326'";
			$v = array($layer_id);
			$t = array('i');
			$res = db_prep_query($sql, $v, $t);
			$row = db_fetch_array($res);
			if ($row['epsg'] && $row['minx'] && $row['miny'] && $row['maxx'] && $row['maxy']) {
				$layer_4326_box = array($row['minx'], $row['miny'], $row['maxx'], $row['maxy']);
				$extent_layer_id = $row['layer_id'];
			}
			else {
				$layer_4326_box = $sl_4326_box;
				$extent_layer_id = $layer_id;
			}
		}

		if ($layer_4326_box[0] <= $sl_4326_box[0] || $layer_4326_box[2] >= $sl_4326_box[2] || $layer_4326_box[1] <= $sl_4326_box[1] || $layer_4326_box[3] >= $sl_4326_box[3]) {
			if ($layer_4326_box[0] < $sl_4326_box[0]) {
				$sl_4326_box[0] = $layer_4326_box[0]; 
			}
			if ($layer_4326_box[2] > $sl_4326_box[2]) {
				$sl_4326_box[2] = $layer_4326_box[2]; 
			}
			if ($layer_4326_box[1] < $sl_4326_box[1]) {
				$sl_4326_box[1] = $layer_4326_box[1]; 
			}
			if ($layer_4326_box[3] > $sl_4326_box[3]) {
				$sl_4326_box[3] = $layer_4326_box[3]; 
			}

			$d_x = $sl_4326_box[2] - $sl_4326_box[0]; 
			$d_y = $sl_4326_box[3] - $sl_4326_box[1];
			
			$new_minx = $sl_4326_box[0] - 0.05*($d_x);
			$new_maxx = $sl_4326_box[2] + 0.05*($d_x);
			$new_miny = $sl_4326_box[1] - 0.05*($d_y);
			$new_maxy = $sl_4326_box[3] + 0.05*($d_y);

			if ($new_minx < -180) $sl_4326_box[0] = -180; else $sl_4326_box[0] = $new_minx;
			if ($new_maxx > 180) $sl_4326_box[2] = 180; else $sl_4326_box[2] = $new_maxx;
			if ($new_miny < -90) $sl_4326_box[1] = -90; else $sl_4326_box[1] = $new_miny;
			if ($new_maxy > 90) $sl_4326_box[3] = 90; else $sl_4326_box[3] = $new_maxy;
		}
		$con3 = new connector(LAYER_EXTENT_URL."VERSION=1.1.1&REQUEST=GetMap&SERVICE=WMS&LAYERS=rlp,ows_layer&STYLES=&SRS=EPSG:4326&BBOX=".$sl_4326_box[0].",".$sl_4326_box[1].",".$sl_4326_box[2].",".$sl_4326_box[3]."&WIDTH=120&HEIGHT=120&FORMAT=image/png&BGCOLOR=0xffffff&TRANSPARENT=TRUE&EXCEPTIONS=application/vnd.ogc.se_inimage&layer_id=".$extent_layer_id);
		$fileNameExtent = $layer_id."_layer_extent_preview.png";
		$fileContentExtent = $con3->file;
		$success = savePreview($fileNameExtent, $fileContentExtent);
		if (!$success) $fileNameExtent = "";

		$sql = "SELECT * FROM layer_preview WHERE fkey_layer_id = $1";
		$v = array($layer_id);
		$t = array('i');
		$res = db_prep_query($sql, $v, $t);
		$row = db_fetch_array($res);
		if ($row['fkey_layer_id'] == $layer_id) {
			$sql = "UPDATE layer_preview SET layer_map_preview_filename = $1, layer_extent_preview_filename = $2, layer_legend_preview_filename = $3 WHERE fkey_layer_id = $4";
			$v = array($fileNameMap, $fileNameExtent, $fileNameLegend, $layer_id);
			$t = array('s', 's', 's', 'i');
		}
		else {
			$sql = "INSERT INTO layer_preview (fkey_layer_id, layer_map_preview_filename, layer_extent_preview_filename, layer_legend_preview_filename) VALUES ($1, $2, $3, $4)";
			$v = array($layer_id, $fileNameMap, $fileNameExtent, $fileNameLegend);
			$t = array('i', 's', 's', 's');
		}
			
		$res = db_prep_query($sql, $v, $t);
		if (db_error()) {
			 echo "<script>alert(\"Error while saving layer preview: ".addslashes(db_error())."\");</script>";
		}
		else {
			 echo "<script>try{parent.opener.document.getElementById('".$layer_id."_dp').style.display='';}catch(e){};alert(\"Layer preview has been saved!\")</script>";
		}
	}
}
?>
</body>
<html>
