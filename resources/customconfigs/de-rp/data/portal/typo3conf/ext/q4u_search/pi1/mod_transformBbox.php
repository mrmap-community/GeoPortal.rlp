<?php
require("/data/mapbender/http/php/mb_validateSession.php");

$bbox = explode(",", $bboxStr);

function transform ($x, $y, $oldEPSG, $newEPSG) {
	if (is_null($x) || !is_numeric($x) || 
		is_null($y) || !is_numeric($y) ||
		is_null($oldEPSG) || !is_numeric($oldEPSG) ||
		is_null($newEPSG) || !is_numeric($newEPSG)) {
		return null;
	}
	if(SYS_DBTYPE=='pgsql'){
		$con = db_connect(DBSERVER, OWNER, PW);
		$sqlMinx = "SELECT X(transform(GeometryFromText('POINT(".pg_escape_string($x)." ".pg_escape_string($y).")',".pg_escape_string($oldEPSG)."),".pg_escape_string($newEPSG).")) as minx";
		$resMinx = db_query($sqlMinx);
		$minx = floatval(db_result($resMinx,0,"minx"));
		
		$sqlMiny = "SELECT Y(transform(GeometryFromText('POINT(".pg_escape_string($x)." ".pg_escape_string($y).")',".pg_escape_string($oldEPSG)."),".pg_escape_string($newEPSG).")) as miny";
		$resMiny = db_query($sqlMiny);
		$miny = floatval(db_result($resMiny,0,"miny"));
	}else{
		$con_string = "host=" . GEOS_DBSERVER . " port=" . GEOS_PORT . 
			" dbname=" . GEOS_DB . "user=" . GEOS_OWNER . 
			"password=" . GEOS_PW;
		$con = pg_connect($con_string) or die ("Error while connecting database");
		/*
		 * @security_patch sqli done
		 */
		$sqlMinx = "SELECT X(transform(GeometryFromText('POINT(".pg_escape_string($x)." ".pg_escape_string($y).")',".pg_escape_string($oldEPSG)."),".pg_escape_string($newEPSG).")) as minx";
		$resMinx = pg_query($con,$sqlMinx);
		$minx = floatval(pg_fetch_result($resMinx,0,"minx"));
		
		$sqlMiny = "SELECT Y(transform(GeometryFromText('POINT(".pg_escape_string($x)." ".pg_escape_string($y).")',".pg_escape_string($oldEPSG)."),".pg_escape_string($newEPSG).")) as miny";
		$resMiny = pg_query($con,$sqlMiny);
		$miny = floatval(pg_fetch_result($resMiny,0,"miny"));
	}
	return array("x" => $minx, "y" => $miny);
}

if (!is_null($bbox) && is_array($bbox) && count($bbox) === 4) {
			//$e = new mb_exception($bbox[0]);
			$newBbox = array();
			for ($i = 0; $i < count($bbox); $i+=2) {
				/*$e = new mb_exception("i: ".$i);
				$e = new mb_exception("bbox1: ".$bbox[$i]);
				$e = new mb_exception("bbox2: ".$bbox[$i+1]);
				$e = new mb_exception("epsg1: ".$oldEPSG);
				$e = new mb_exception("epsg2: ".$newEPSG);*/
				$pt = transform(
					floatval($bbox[$i]), 
					floatval($bbox[$i+1]), 
					$oldEPSG, 
					$newEPSG
				);
				array_push($newBbox,$pt["x"]);
				array_push($newBbox,$pt["y"]);
			}	
}	 

$GML = "<FeatureCollection xmlns:gml='http://www.opengis.net/gml'><boundedBy><Box srsName='EPSG:".$newEPSG."'>";
$GML .= "<coordinates>".$newBbox[0].",".$newBbox[1]." ".$newBbox[2];
$GML .= ",".$newBbox[3]."</coordinates></Box>";
$GML .= "</boundedBy><featureMember><gemeinde><title>BBOX</title><the_geom><MultiPolygon srsName=\"EPSG:";
$GML .= $newEPSG."\"><polygonMember><Polygon><outerBoundaryIs><LinearRing><coordinates>";
$GML .= $newBbox[0].",".$newBbox[1]." ".$newBbox[2].",";
$GML .= $newBbox[1]." ".$newBbox[2].",".$newBbox[3]." ";
$GML .= $newBbox[0].",".$newBbox[3]." ".$newBbox[0].",".$newBbox[1];
$GML .= "</coordinates></LinearRing></outerBoundaryIs></Polygon></polygonMember></MultiPolygon></the_geom></gemeinde></featureMember></FeatureCollection>";
//header("Content-type: application/txt; charset=" . CHARSET);
//echo $GML;
Mapbender::session()->set("GML",$GML);
?>
