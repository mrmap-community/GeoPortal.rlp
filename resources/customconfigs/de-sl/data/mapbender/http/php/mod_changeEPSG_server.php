<?php
# $Id: mod_changeEPSG.php 2413 2008-04-23 16:21:04Z christoph $
# http://www.mapbender.org/index.php/mod_changeEPSG.php
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

require(dirname(__FILE__)."/mb_validateSession.php");

$epsgObj = array();

$ajaxResponse = new AjaxResponse($_POST);

switch ($ajaxResponse->getMethod()) {
	case "changeEpsg" :
		if (!Mapbender::postgisAvailable()) {
			$ajaxResponse->setSuccess(false);
			$ajaxResponse->setMessage(_mb("PostGIS is not available. Please contact the administrator."));
			$ajaxResponse->send();
		}

		$epsgArray = $ajaxResponse->getParameter("srs");
		$newSrs = $ajaxResponse->getParameter("newSrs");

		for($i=0; $i < count($epsgArray); $i++){
			// check if parameters are valid geometries to 
			// avoid SQL injections
			$currentEpsg = $epsgArray[$i];
	
			$oldEPSG = preg_replace("/EPSG:/","", $currentEpsg->epsg);
			$oldEPSG = preg_replace("/CRS:/","", $oldEPSG);
			if ($oldEPSG == '84'){$oldEPSG = '4326';}
			$newEPSG = preg_replace("/EPSG:/","", $newSrs);
			$newEPSG = preg_replace("/CRS:/","", $newEPSG);
			if ($newEPSG == '84'){$newEPSG = '4326';}
			 
			$extArray = explode(",", $currentEpsg->extent);
			if (is_numeric($extArray[0]) && is_numeric($extArray[1]) && 
				is_numeric($extArray[2]) && is_numeric($extArray[3]) && 
				is_numeric($oldEPSG) && is_numeric($newEPSG)) {
			
			
				$con = db_connect($DBSERVER,$OWNER,$PW);
				$sqlMinx = "SELECT ST_X(ST_transform(ST_GeometryFromText('POINT(".$extArray[0]." ".$extArray[1].")',".$oldEPSG."),".$newEPSG.")) as minx";
				$resMinx = db_query($sqlMinx);
				$minx = floatval(db_result($resMinx,0,"minx"));
				
				$sqlMiny = "SELECT ST_Y(ST_transform(ST_GeometryFromText('POINT(".$extArray[0]." ".$extArray[1].")',".$oldEPSG."),".$newEPSG.")) as miny";
				$resMiny = db_query($sqlMiny);
				$miny = floatval(db_result($resMiny,0,"miny"));
				
				$sqlMaxx = "SELECT ST_X(ST_transform(ST_GeometryFromText('POINT(".$extArray[2]." ".$extArray[3].")',".$oldEPSG."),".$newEPSG.")) as maxx";
				$resMaxx = db_query($sqlMaxx);
				$maxx = floatval(db_result($resMaxx,0,"maxx"));
				
				$sqlMaxy = "SELECT ST_Y(ST_transform(ST_GeometryFromText('POINT(".$extArray[2]." ".$extArray[3].")',".$oldEPSG."),".$newEPSG.")) as maxy";
				$resMaxy = db_query($sqlMaxy);
				$maxy = floatval(db_result($resMaxy,0,"maxy"));

				if ($currentEpsg->frameName) {
					if (!$resMinx || !$resMiny || !$resMaxx || !$resMaxy) {
						$ajaxResponse->setSuccess(false);
						$ajaxResponse->setMessage($oldEPSG."<-->".$newEPSG._mb("The coordinates could not be projected."));
						$ajaxResponse->send();
					}
				}

				$extenty = $maxy - $miny;
				$extentx = $maxx - $minx;
				$relation_px_x = $currentEpsg->width / $currentEpsg->height;
				$relation_px_y = $currentEpsg->height / $currentEpsg->width;
				$relation_bbox_x = $extentx / $extenty;
		
				if($relation_bbox_x <= $relation_px_x){
					$centerx = $minx + ($extentx/2);
					$minx = $centerx - $relation_px_x * $extenty / 2;
					$maxx = $centerx + $relation_px_x * $extenty / 2;
				}
				if($relation_bbox_x > $relation_px_x){
					$centery = $miny + ($extenty/2);
					$miny = $centery - $relation_px_y * $extentx / 2;
					$maxy = $centery + $relation_px_y * $extentx / 2;
				}

				if ($currentEpsg->frameName) {
					$epsgObj[$i] = array(
						"frameName" => $currentEpsg->frameName,
						"newSrs" => $newSrs,
						"minx" => $minx,
						"miny" => $miny,
						"maxx" => $maxx,
						"maxy" => $maxy
					);
				}
				else {
					$epsgObj[$i] = array(
						"wms" => $currentEpsg->wms,
						"newSrs" => $newSrs,
						"minx" => $minx,
						"miny" => $miny,
						"maxx" => $maxx,
						"maxy" => $maxy
					);
				}
			}
			else {
				$ajaxResponse->setSuccess(false);
				$ajaxResponse->setMessage(_mb("An unknown error occured."));
				$ajaxResponse->send();
			}
		}
		$ajaxResponse->setSuccess(true);
		$ajaxResponse->setResult($epsgObj);
		break;
	default :
		$ajaxResponse->setSuccess(false);
		$ajaxResponse->setMessage(_mb("An unknown error occured."));
		break;
}

$ajaxResponse->send();
?>
