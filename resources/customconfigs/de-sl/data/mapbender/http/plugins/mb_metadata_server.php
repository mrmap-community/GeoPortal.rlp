<?php
require_once dirname(__FILE__) . "/../../core/globalSettings.php";
require_once dirname(__FILE__) . "/../classes/class_user.php";
require_once dirname(__FILE__) . "/../classes/class_wms.php";//already includes iso19139!
require_once dirname(__FILE__) . "/../classes/class_Uuid.php";
require_once dirname(__FILE__) . "/../../tools/wms_extent/extent_service.conf";

$ajaxResponse = new AjaxResponse($_POST);

function abort ($message) {
	global $ajaxResponse;
	$ajaxResponse->setSuccess(false);
	$ajaxResponse->setMessage($message);
	$ajaxResponse->send();
	die();
}

function DOMNodeListObjectValuesToArray($domNodeList) {
	$iterator = 0;
	$array = array();
	foreach ($domNodeList as $item) {
    		$array[$iterator] = $item->nodeValue; // this is a DOMNode instance
    		// you might want to have the textContent of them like this
    		$iterator++;
	}
	return $array;
}

function getWms ($wmsId = null) {
	$user = new User(Mapbender::session()->get("mb_user_id"));
	$wmsIdArray = $user->getOwnedWms();

	if (!is_null($wmsId) && !in_array($wmsId, $wmsIdArray)) {
		abort(_mb("You are not allowed to access this WMS."));
	}
	return $wmsIdArray;
}

function getLayer ($layerId = null) {
	$user = new User(Mapbender::session()->get("mb_user_id"));
	$wmsIdArray = $user->getOwnedWms();
	if (!is_array($wmsIdArray) || count($wmsIdArray) === 0) {
		abort(_mb("No metadata sets available."));
	}
	$wmsId = wms::getWmsIdByLayerId($layerId);
	if (is_null($wmsId) || !in_array($wmsId, $wmsIdArray)) {
		abort(_mb("You are not allowed to access WMS " . $wmsId));
	}
	return;
}

function extractPolygonArray($domXpath, $path) {
	$polygonalExtentExterior = array();
	if ($domXpath->query($path.'/gml:Polygon/gml:exterior/gml:LinearRing/gml:posList')) {
		//read posList
		$exteriorRingPoints = $domXpath->query($path.'/gml:Polygon/gml:exterior/gml:LinearRing/gml:posList');
		$exteriorRingPoints = DOMNodeListObjectValuesToArray($exteriorRingPoints);
		if (count($exteriorRingPoints) > 0) {
			//poslist is only space separated
			$exteriorRingPointsArray = explode(' ',$exteriorRingPoints[0]);
			for ($i = 0; $i <= count($exteriorRingPointsArray)/2-1; $i++) {
				$polygonalExtentExterior[$i]['x'] = $exteriorRingPointsArray[2*$i];
				$polygonalExtentExterior[$i]['y'] = $exteriorRingPointsArray[(2*$i)+1];
			}
		}
	} else {
		//try to read coordinates
		$exteriorRingPoints = $domXpath->query($path.'/gml:Polygon/gml:exterior/gml:LinearRing/gml:coordinates');
		$exteriorRingPoints = DOMNodeListObjectValuesToArray($exteriorRingPoints);
		if (count($exteriorRingPoints) > 0) {
			//two coordinates of one point are comma separated
			//problematic= ", " or " ," have to be deleted before
			$exteriorRingPoints[0] = str_replace(', ',',',str_replace(' ,',',',$exteriorRingPoints[0]));
			$exteriorRingPointsArray = explode(' ',$exteriorRingPoints[0]);
			for ($i = 0; $i <= count($exteriorRingPointsArray)-1;$i++) {
				$coords = explode(",",$exteriorRingPointsArray[$i]);
				$polygonalExtentExterior[$i]['x'] = $coords[0];
				$polygonalExtentExterior[$i]['y'] = $coords[1];
			}
		}
	}
	return $polygonalExtentExterior;
}

function gml2wkt($gml) {
	//function to create wkt from given gml multipolygon
	//DOM
	$polygonalExtentExterior = array();
	$gmlObject = new DOMDocument();
	libxml_use_internal_errors(true);
	try {
		$gmlObject->loadXML($gml);
		if ($gmlObject === false) {
			foreach(libxml_get_errors() as $error) {
        			$err = new mb_exception("mb_metadata_server.php:".$error->message);
    			}
			throw new Exception("mb_metadata_server.php:".'Cannot parse GML!');
			return false;
		}
	}
	catch (Exception $e) {
    		$err = new mb_exception("mb_metadata_server.php:".$e->getMessage());
		return false;
	}
	//if parsing was successful
	if ($gmlObject !== false) {
		//read crs from gml
		$xpath = new DOMXPath($gmlObject);
		$xpath->registerNamespace('gml','http://www.opengis.net/gml');
		$MultiSurface = $xpath->query('/gml:MultiSurface');
		if ($MultiSurface->length == 1) { //test for DOM!
			$crs = $xpath->query('/gml:MultiSurface/@srsName');
			$crsArray = DOMNodeListObjectValuesToArray($crs);
			$crsId = end(explode(":",$crsArray[0]));
			//count surfaceMembers
			$numberOfSurfaces = count(DOMNodeListObjectValuesToArray($xpath->query('/gml:MultiSurface/gml:surfaceMember')));
			for ($k = 0; $k < $numberOfSurfaces; $k++) {
				$polygonalExtentExterior[] = extractPolygonArray($xpath, '/gml:MultiSurface/gml:surfaceMember['. (string)($k + 1) .']');
			}
		} else { 
			$polygonalExtentExterior[0] = extractPolygonArray($xpath, '/');
		}
		$crs = $xpath->query('/gml:Polygon/@srsName');
		$crsArray = DOMNodeListObjectValuesToArray($crs);
		$crsId = end(explode(":",$crsArray[0]));
		if (!isset($crsId) || $crsId =="" || $crsId == NULL) {
			//set default to lonlat wgs84
			$crsId = "4326";
		}
		$mbMetadata = new Iso19139();
		$wkt = $mbMetadata->createWktPolygonFromPointArray($polygonalExtentExterior);
		return $wkt;
	}
}

function getExtentGraphic($layer_4326_box) {
	$area_4326_box = explode(',',EXTENTSERVICEBBOX);
	if ($layer_4326_box[0] <= $area_4326_box[0] || $layer_4326_box[2] >= $area_4326_box[2] || $layer_4326_box[1] <= $area_4326_box[1] || $layer_4326_box[3] >= $area_4326_box[3]) {
		if ($layer_4326_box[0] < $area_4326_box[0]) {
			$area_4326_box[0] = $layer_4326_box[0]; 
		}
		if ($layer_4326_box[2] > $area_4326_box[2]) {
			$area_4326_box[2] = $layer_4326_box[2]; 
		}
		if ($layer_4326_box[1] < $area_4326_box[1]) {
			$area_4326_box[1] = $layer_4326_box[1]; 
		}
		if ($layer_4326_box[3] > $area_4326_box[3]) {
			$area_4326_box[3] = $layer_4326_box[3]; 
		}

		$d_x = $area_4326_box[2] - $area_4326_box[0]; 
		$d_y = $area_4326_box[3] - $area_4326_box[1];
			
		$new_minx = $area_4326_box[0] - 0.05*($d_x);
		$new_maxx = $area_4326_box[2] + 0.05*($d_x);
		$new_miny = $area_4326_box[1] - 0.05*($d_y);
		$new_maxy = $area_4326_box[3] + 0.05*($d_y);

		if ($new_minx < -180) $area_4326_box[0] = -180; else $area_4326_box[0] = $new_minx;
		if ($new_maxx > 180) $area_4326_box[2] = 180; else $area_4326_box[2] = $new_maxx;
		if ($new_miny < -90) $area_4326_box[1] = -90; else $area_4326_box[1] = $new_miny;
		if ($new_maxy > 90) $area_4326_box[3] = 90; else $area_4326_box[3] = $new_maxy;
	}
	$getMapUrl = EXTENTSERVICEURL."VERSION=1.1.1&REQUEST=GetMap&SERVICE=WMS&LAYERS=".EXTENTSERVICELAYER."&STYLES=&SRS=EPSG:4326&BBOX=".$area_4326_box[0].",".$area_4326_box[1].",".$area_4326_box[2].",".$area_4326_box[3]."&WIDTH=120&HEIGHT=120&FORMAT=image/png&BGCOLOR=0xffffff&TRANSPARENT=TRUE&EXCEPTIONS=application/vnd.ogc.se_inimage&minx=".$layer_4326_box[0]."&miny=".$layer_4326_box[1]."&maxx=".$layer_4326_box[2]."&maxy=".$layer_4326_box[3];
	return $getMapUrl;
}

switch ($ajaxResponse->getMethod()) {
	case "getWms" :
		$wmsIdArray = getWms();
		
		$wmsList = implode(",", $wmsIdArray);
		$sql = <<<SQL
	
SELECT wms.wms_id, wms.wms_title, to_timestamp(wms.wms_timestamp),to_timestamp(wms.wms_timestamp_create), wms_version, m.status_comment, wms_id
FROM wms LEFT JOIN mb_wms_availability AS m
ON wms.wms_id = m.fkey_wms_id 
WHERE wms_id IN ($wmsList);

SQL;
		$res = db_query($sql);
		$resultObj = array(
			"header" => array(
				_mb("WMS ID"),
				_mb("title"),
				_mb("last change"),
				_mb("creation"),
				_mb("version"),
				_mb("status"),
				_mb("wms id")
			), 
			"data" => array()
		);

		while ($row = db_fetch_row($res)) {
			// convert NULL to '', NULL values cause datatables to crash
			$walk = array_walk($row, create_function('&$s', '$s=strval($s);'));
			$resultObj["data"][]= $row;
		}
		$ajaxResponse->setResult($resultObj);
		$ajaxResponse->setSuccess(true);
		break;

	case "getWmsMetadata" :
		$wmsId = $ajaxResponse->getParameter("id");
		getWms($wmsId);

		$wms = new wms();
		$wms->createObjFromDBNoGui($wmsId);//here the owsproxyurls will be read out - to make previews with proxy urls

		$fields = array(
			"wms_id", 
			"wms_abstract", 
			"wms_title", 
			"fees", 
			"accessconstraints", 
			"contactperson", 
			"contactposition", 
			"contactvoicetelephone", 
			"contactfacsimiletelephone", 
			"contactorganization", 
			"address", 
			"city", 
			"stateorprovince", 
			"postcode", 
			"country", 
			"contactelectronicmailaddress",
			"wms_timestamp", 
			"wms_timestamp_create",
			"wms_network_access",
			"wms_max_imagesize",
			"fkey_mb_group_id",
			"inspire_annual_requests"
		);

		$resultObj = array();
		foreach ($fields as $field) {
			if ($field == "wms_timestamp" || $field == "wms_timestamp_create") {
				if ($wms->$field != "") {
	
					$resultObj[$field] = date('d.m.Y', $wms->$field);
					
				}
			}
			else {
				$resultObj[$field] = $wms->$field;
				//$e = new mb_exception("mb_metadata_server: resultObject[".$field."]=".$wms->$field);	
			}
		}
		
		// layer searchable
		$resultObj["layer_searchable"] = array();
		foreach ($wms->objLayer as $layer) {
			if (intval($layer->layer_searchable) === 1) {
				$resultObj["layer_searchable"][] = intval($layer->layer_uid);
			}
		}
		
		$keywordSql = <<<SQL
	
SELECT DISTINCT keyword FROM keyword, layer_keyword 
WHERE keyword_id = fkey_keyword_id AND fkey_layer_id IN (
	SELECT layer_id from layer, wms 
	WHERE fkey_wms_id = wms_id AND wms_id = $wmsId
) ORDER BY keyword

SQL;

		$keywordRes = db_query($keywordSql);
		$keywords = array();
		while ($keywordRow = db_fetch_assoc($keywordRes)) {
			$keywords[]= $keywordRow["keyword"];
		}

		$resultObj["wms_keywords"] = implode(", ", $keywords);

		$termsofuseSql = <<<SQL
SELECT fkey_termsofuse_id FROM wms_termsofuse WHERE fkey_wms_id = $wmsId
SQL;

		$termsofuseRes = db_query($termsofuseSql);
		if ($termsofuseRes) {
			$termsofuseRow = db_fetch_assoc($termsofuseRes);
			$resultObj["wms_termsofuse"] = $termsofuseRow["fkey_termsofuse_id"];
		}
		else {
			$resultObj["wms_termsofuse"] = null;
		}
		$resultObj['wms_network_access'] = $resultObj['wms_network_access'] == 1 ? true : false;
		if (is_null($resultObj['inspire_annual_requests']) || $resultObj['inspire_annual_requests'] == "") {
			$resultObj['inspire_annual_requests'] = "0";
		}
		//get contact information from group relation
		//check if fkey_mb_group_id has been defined before - in service table
		if ($resultObj["fkey_mb_group_id"] == "" || !isset($resultObj["fkey_mb_group_id"])){
			$e = new mb_notice("fkey_mb_group_id is null or empty");
			//check if primary group is set 
			$user = new User;
			$userId = $user->id;
			//$e = new mb_exception("user id:".$userId);
			$sql = <<<SQL
	
SELECT fkey_mb_group_id, mb_group_name, mb_group_title, mb_group_address, mb_group_email, mb_group_postcode, mb_group_city, mb_group_logo_path, mb_group_voicetelephone FROM (SELECT fkey_mb_group_id FROM mb_user_mb_group WHERE fkey_mb_user_id = $1 AND mb_user_mb_group_type = 2) AS a LEFT JOIN mb_group ON a.fkey_mb_group_id = mb_group.mb_group_id

SQL;
			$v = array($userId);
			$t = array('i');
			$res = db_prep_query($sql,$v,$t);
			$row = array();
			if ($res) {
				$row = db_fetch_assoc($res);
				$resultObj["fkey_mb_group_id"] = $row["fkey_mb_group_id"];
				$resultObj["mb_group_title"] = $row["mb_group_title"];
				$resultObj["mb_group_address"] = $row["mb_group_address"];
				$resultObj["mb_group_email"] = $row["mb_group_email"];
				$resultObj["mb_group_postcode"] = $row["mb_group_postcode"];
				$resultObj["mb_group_city"] = $row["mb_group_city"];
				$resultObj["mb_group_logo_path"] = $row["mb_group_logo_path"];
				$resultObj["mb_group_voicetelephone"] = $row["mb_group_voicetelephone"];
			}
		} else {
			//get current fkey_mb_group_id and the corresponding data
			$sql = <<<SQL
	
SELECT mb_group_name, mb_group_title, mb_group_address, mb_group_email, mb_group_postcode, mb_group_city, mb_group_logo_path, mb_group_voicetelephone FROM mb_group WHERE mb_group_id = $1

SQL;
			$v = array($resultObj["fkey_mb_group_id"]);
			$t = array('i');
			$res = db_prep_query($sql,$v,$t);
			$row = array();
			if ($res) {
				$row = db_fetch_assoc($res);
				$resultObj["mb_group_title"] = $row["mb_group_title"];
				$resultObj["mb_group_address"] = $row["mb_group_address"];
				$resultObj["mb_group_email"] = $row["mb_group_email"];
				$resultObj["mb_group_postcode"] = $row["mb_group_postcode"];
				$resultObj["mb_group_city"] = $row["mb_group_city"];
				$resultObj["mb_group_logo_path"] = $row["mb_group_logo_path"];
				$resultObj["mb_group_voicetelephone"] = $row["mb_group_voicetelephone"];
			}
			else {
				$resultObj["fkey_mb_group_id"] = null;
			}
		}
		$ajaxResponse->setResult($resultObj);
		$ajaxResponse->setSuccess(true);

		break;
	
	case "getLayerMetadata" :
		$layerId = $ajaxResponse->getParameter("id");
		getLayer($layerId);
		//new - only layers with latlonbboxes are supported!
		$sql = <<<SQL
	
SELECT layer_id, layer_name, layer_title, layer_abstract, layer_searchable, inspire_download, fkey_wms_id as wms_id 
FROM layer WHERE layer_id = $layerId;

SQL;
		$res = db_query($sql);

		$resultObj = array();
		while ($row = db_fetch_assoc($res)) {
			foreach ($row as $key => $value) {
				$resultObj[$key] = $value;
				$e = new mb_notice("plugins/mb_metadata_server.php: get ".$value." for ".$key);
			}
		}

		$sql = <<<SQL
SELECT fkey_md_topic_category_id 
FROM layer_md_topic_category 
WHERE fkey_layer_id = $layerId AND fkey_metadata_id ISNULL
SQL;
		$res = db_query($sql);
		while ($row = db_fetch_assoc($res)) {
			$resultObj["layer_md_topic_category_id"][]= $row["fkey_md_topic_category_id"];
		}

		$sql = <<<SQL
SELECT fkey_inspire_category_id 
FROM layer_inspire_category 
WHERE fkey_layer_id = $layerId AND fkey_metadata_id ISNULL
SQL;
		$res = db_query($sql);
		while ($row = db_fetch_assoc($res)) {
			$resultObj["layer_inspire_category_id"][]= $row["fkey_inspire_category_id"];
		}

		$sql = <<<SQL
SELECT fkey_custom_category_id 
FROM layer_custom_category 
WHERE fkey_layer_id = $layerId AND fkey_metadata_id ISNULL
SQL;
		$res = db_query($sql);
		while ($row = db_fetch_assoc($res)) {
			$resultObj["layer_custom_category_id"][]= $row["fkey_custom_category_id"];
		}

		$sql = <<<SQL
SELECT keyword FROM keyword, layer_keyword 
WHERE keyword_id = fkey_keyword_id AND fkey_layer_id = $layerId
SQL;
		$res = db_query($sql);

		$resultObj["layer_keyword"] = array();
		while ($row = db_fetch_assoc($res)) {
			$resultObj["layer_keyword"][]= $row["keyword"];
		}

		$resultObj['inspire_download'] = $resultObj['inspire_download'] == 1 ? true : false;
		//get wgs84Bbox for relevant layer - to be bequeathed to the metadata
		/*$sql = <<<SQL
SELECT minx, miny, maxx, maxy from layer_epsg WHERE fkey_layer_id = $1 AND epsg = 'EPSG:4326'
SQL;
		$res = db_query($sql);*/
		
		//read out values
		
		//get coupled MetadataURLs from md_metadata and ows_relation_metadata table
		$sql = <<<SQL
SELECT metadata_id, uuid, link, linktype, md_format, relation.relation_type, origin FROM mb_metadata 
INNER JOIN (SELECT * from ows_relation_metadata 
WHERE fkey_layer_id = $layerId ) as relation ON 
mb_metadata.metadata_id = relation.fkey_metadata_id WHERE relation.relation_type IN ('capabilities','external','metador','upload', 'internal')
SQL;
		$res = db_query($sql);
		$resultObj["md_metadata"]->metadata_id = array();
		$resultObj["md_metadata"]->uuid = array();
		$resultObj["md_metadata"]->origin = array();
		$resultObj["md_metadata"]->linktype = array();
		$resultObj["md_metadata"]->link = array();
		$resultObj["md_metadata"]->internal = array();
		$i = 0;
		while ($row = db_fetch_assoc($res)) {
			$resultObj["md_metadata"]->metadata_id[$i]= $row["metadata_id"];
			$resultObj["md_metadata"]->link[$i]= $row["link"];
			$resultObj["md_metadata"]->uuid[$i]= $row["uuid"];
			$resultObj["md_metadata"]->origin[$i]= $row["origin"];
			$resultObj["md_metadata"]->linktype[$i]= $row["linktype"];
			if ($row["relation_type"] == "internal") {
				$resultObj["md_metadata"]->internal[$i] = 1;
			} else {
				$resultObj["md_metadata"]->internal[$i] = 0;
			}
			$i++;
		}
		$ajaxResponse->setResult($resultObj);
		$ajaxResponse->setSuccess(true);
		break;
	case "getLayerByWms" :
		$wmsId = $ajaxResponse->getParameter("id");
//		getWms($wmsId);
		$sql = <<<SQL
	
SELECT layer_id, f_count_layer_couplings(layer_id) as count_coupling, f_collect_inspire_cat_layer(layer_id) AS inspire_cats, layer_pos, layer_parent, layer_name, layer_title, layer_abstract, layer_searchable, inspire_download 
FROM layer WHERE fkey_wms_id = $wmsId ORDER BY layer_pos;

SQL;
		$res = db_query($sql);
		$rows = array();
		while ($row = db_fetch_assoc($res)) {
			$rows[] = $row;
		}
//		$left = 1;
//
//		function createNode ($left, $right, $row) {
//			$inspireCatsArray = explode(",",str_replace("}","",str_replace("{","",$row["inspire_cats"])));
//			if (count($inspireCatsArray) >= 0) {
//				$inspireCats = 1;
//			} else {
//				$inspireCats = 0;
//				}
//			return array(
//				"left" => $left,
//				"right" => $right,
//				"parent" => $row["layer_parent"] !== "" ? intval($row["layer_parent"]) : null,
//				"pos" => intval($row["layer_pos"]),
//				"attr" => array (
//					"layer_id" => intval($row["layer_id"]),
//					"layer_name" => $row["layer_name"],
//					"layer_title" => $row["layer_title"],
//					"layer_abstract" => $row["layer_abstract"],
//					"layer_searchable" => intval($row["layer_searchable"]),
//					"layer_coupling" => intval($row["count_coupling"]),
//					"inspire_cats" => intval($inspireCats)
//				)
//			);
//		}
//
//		function addSubTree ($rows, $i, $left) {
//			$nodeArray = array();
//			$addNewNode = true;
//			for ($j = $i; $j < count($rows); $j++) {
//				$row = $rows[$j];
//				$pos = intval($row["layer_pos"]);
//				$parent = $row["layer_parent"] !== "" ? intval($row["layer_parent"]) : null;
//				
//				// first node of subtree
//				if ($addNewNode) {
//					$nodeArray[]= createNode($left, null, $row);
//					$addNewNode = false;
//				}
//				else {
//					// new sub tree
//					if ($parent === $nodeArray[count($nodeArray)-1]["pos"]) {
//						$addedNodeArray = addSubTree($rows, $j, ++$left);
//						
//						$nodeArray[count($nodeArray)-1]["right"] = 
//							$nodeArray[count($nodeArray)-1]["left"] + 
//							2 * count($addedNodeArray) + 1;
//						
//						$left = $nodeArray[count($nodeArray)-1]["right"] + 1;
//
//						$nodeArray = array_merge($nodeArray, $addedNodeArray);
//						$j += count($addedNodeArray) - 1;
//						$addNewNode = true;
//						
//					}
//					// siblings
//					elseif ($parent === $nodeArray[count($nodeArray)-1]["parent"]) {
//						$nodeArray[count($nodeArray)-1]["right"] = ++$left;
//						$nodeArray[]= createNode(++$left, null, $row);
//					}
//				}
//			}
//			if (is_null($nodeArray[count($nodeArray)-1]["right"])) {
//				$nodeArray[count($nodeArray)-1]["right"] = ++$left;
//			}
//			return $nodeArray;
//		}
		
//        $nodeArray = addSubTree($rows, 0, 1);
		
        
        function newNode ($row) {
			$inspireCatsArray = explode(",",str_replace("}","",str_replace("{","",$row["inspire_cats"])));
			if (count($inspireCatsArray) >= 0) {
				$inspireCats = 1;
			} else {
				$inspireCats = 0;
				}
			return array(
// Die nächsten beiden Zeilen sind auskommentiert, da ich mir nicht sicher bin, diese nötig sind, oder nicht
//				"left" => $left,
//				"right" => $right,
				"parent" => $row["layer_parent"] !== "" ? intval($row["layer_parent"]) : null,
				"pos" => intval($row["layer_pos"]),
				"attr" => array (
					"layer_id" => intval($row["layer_id"]),
					"layer_name" => $row["layer_name"],
					"layer_title" => $row["layer_title"],
					"layer_abstract" => $row["layer_abstract"],
					"layer_searchable" => intval($row["layer_searchable"]),
					"layer_coupling" => intval($row["count_coupling"]),
					"inspire_download" => intval($row["inspire_download"]),
					"inspire_cats" => intval($inspireCats)
				)
			);
		}
        function createObjectsTree ($rows) {
            $treeArray = array();
            $parentIdxs = array();
            // add root element
            foreach ($rows as $row) {
                if($row['layer_parent'] === null || $row['layer_parent'] === ""){
                    $treeArray[] = newNode($row);
                    $currentNodeNum = $row['layer_pos'];
                } else {
                    if(!in_array(intval($row["layer_parent"]), $parentIdxs)){
                        $parentIdxs[] = intval($row["layer_parent"]);
                    }
                }
            }
            sort($parentIdxs, SORT_NUMERIC);
            foreach ($parentIdxs as $parentIdx) {
                for($i = 0; $i < count($rows); $i++) {
                    if($rows[$i]['layer_parent'] !== null
                            && $rows[$i]['layer_parent'] !== ""
                            && intval($rows[$i]["layer_parent"]) === $parentIdx){
                        $node = newNode($rows[$i]);
                        $treeArray = addNode($treeArray, $node, $parentIdx);
                    }
                }
            }
            return $treeArray;
        }
        
        function addNode(&$array, $node, $parentNum) {
            foreach($array as &$item) {
                if($item["pos"] === $parentNum){
                    if(!isset($item["children"])){
                        $item["children"] = array();
                    }
                    $item["children"][] = $node;
                } else {
                    if(isset($item["children"])) {
                        addNode($item["children"], $node, $parentNum);
                    }
                }
            }
            return $array;
        }
        
		$nodeArray = createObjectsTree($rows);
		$resultObj = array(
			"nodesTree" => $nodeArray
		);
		$ajaxResponse->setResult($resultObj);
		$ajaxResponse->setSuccess(true);
		break;
	case "save":
		$data = $ajaxResponse->getParameter("data");
		try {
			$wmsId = intval($data->wms->wms_id);
		}
		catch (Exception $e) {
			$ajaxResponse->setSuccess(false);
			$ajaxResponse->setMessage(_mb("Invalid WMS ID."));
			$ajaxResponse->send();						
		}
		getWms($wmsId);
		$wms = new wms();	
		$wms->createObjFromDBNoGui($wmsId,false);//here the original urls will be used - cause the object will used to update the wms table
		$columns = array(
			"wms_abstract", 
			"wms_title", 
			"fees", 
			"accessconstraints", 
			"contactperson", 
			"contactposition", 
			"contactvoicetelephone", 
			"contactfacsimiletelephone", 
			"contactorganization", 
			"address", 
			"city", 
			"stateorprovince", 
			"postcode", 
			"country", 
			"contactelectronicmailaddress",
			"wms_termsofuse",
			"wms_network_access",
			"wms_max_imagesize",
			"fkey_mb_group_id",
			"inspire_annual_requests"
		);
		foreach ($columns as $c) {
			if ($c == 'wms_termsofuse' && $data->wms->$c == "0") {
				$value = null;
			} else {
				if ($c == 'inspire_annual_requests' && $data->wms->$c == "") {
					$value = "0";
				} else {
					$value = $data->wms->$c;
				}
			}
			if (!is_null($value)) {
				$wms->$c = $value;
			}
		}

//		if (is_array($data->wms->layer_searchable)) {
//			foreach ($wms->objLayer as &$layer) {
//				$layer->layer_searchable = 0;//why
//				$e = new mb_notice("mb_metadata_server.php: Check layer with id ".$layer->layer_uid." to be searchable");
//				for ($i = 0; $i < count($data->wms->layer_searchable); $i++) {
//					//$e = new mb_exception("mb_metadata_server.php: Layer with id ".$id." found to be searchable");
//					$id = $data->wms->layer_searchable[$i];
//					$e = new mb_notice("mb_metadata_server.php: Layer with id ".$id." found to be searchable");
//					if ($id == intval($layer->layer_uid)) {
//						$e = new mb_notice("mb_metadata_server.php: Layer identical - update it in wms object");
//						$layer->layer_searchable = 1;					
//					} else {
//						continue; //with next 
//					}
//					unset($id);
//					//$layer->layer_searchable = 1;
//					//break;
//				}
//			}
//		}
        foreach ($wms->objLayer as &$layer) {
            $layer->layer_searchable = 0;//why
        }
        if (is_array($data->wms->layer_searchable)) {
            for ($i = 0; $i < count($data->wms->layer_searchable); $i++) {
                setSearchable($data->wms->layer_searchable[$i], $wms->objLayer);
            }
        }
        

		try {
			$layerId = intval($data->layer->layer_id);
		}
		catch (Exception $e) {
		  	$ajaxResponse->setSuccess(false);
			$ajaxResponse->setMessage(_mb("Could not read layer ID ".$data->layer->layer_id));
			$ajaxResponse->send();						
		}
		if ($layerId) {
			$e = new mb_notice("Got following layer id from wms metadata editor client: ".$layerId);
			try {
				$layer = &$wms->getLayerReferenceById($layerId);
			}
			catch (Exception $e) {
				$ajaxResponse->setSuccess(false);
				$ajaxResponse->setMessage(_mb("Could not get layer with ID ".$layerId." from wms object by reference!"));
				$ajaxResponse->send();						
			}
			$columns = array(
				"layer_abstract",
				"layer_title",
				"layer_keyword",
				"inspire_download",
				"layer_md_topic_category_id",
				"layer_inspire_category_id",
				"layer_custom_category_id"
			);
			//extract relevant information from json and fill them into the wms object // both are filled together!!
			foreach ($columns as $c) {
				$value = $data->layer->$c;
				$e = new mb_notice("plugins/mb_metadata_server.php: layer entry for ".$c.": ".$data->layer->$c);
				if ($c === "layer_keyword") {
					$layer->$c = explode(",", $value);
					foreach ($layer->$c as &$val) {
						$val = trim($val);
					}
				}
				elseif ($c === "layer_md_topic_category_id"
					|| $c === "layer_inspire_category_id"
					|| $c === "layer_custom_category_id"
				) {
					if (!is_array($value)) {
						$layer->$c = array($value);
					}
					else {
						$layer->$c = $value;
					}
				}
				elseif ($c === "inspire_download") {
					if ($value == "on") {
						$layer->$c = intval('1');
					} else {
						$layer->$c = intval('0');
					}
				}
				else {
					if (!is_null($value)) {
						$layer->$c = $value;
					}
				}
			}
		}
		if ($wms->wms_network_access == "on") {
			$wms->wms_network_access = intval('1');
		} else {
			$wms->wms_network_access = intval('0');
		}

		if (defined("TWITTER_NEWS") && TWITTER_NEWS == true && $ajaxResponse->getParameter("twitterNews") == true) {
    	    		$wms->twitterNews = true;
			$twitterIsConfigured = true;
			//$e = new mb_exception("twitter configured");
    		} else {
			$wms->twitterNews = false;
			$twitterIsConfigured = false;
			//$e = new mb_exception("twitter not configured");
		}
    		if(defined("GEO_RSS_FILE") && GEO_RSS_FILE != "" && $ajaxResponse->getParameter("setGeoRss") == true) {
        		$wms->setGeoRss = true;
			$rssIsConfigured = true;
			//$e = new mb_exception("rss configured");
    		} else {
			$rssIsConfigured = false;
			$wms->setGeoRss = false;
			//$e = new mb_exception("rss not configured");
		}

		$messResult = "Updated WMS metadata for ID " . $wmsId.". ";
		//Add helpful hint if publishing is demanded, but not configured in mapbender.conf - do this before update object - cause otherwise it will not give back the right attributes
		if (!$wms->twitterNews && ($ajaxResponse->getParameter("twitterNews") == true)) {
			$messResult .= " Publishing via twitter was requested, but this is not configured. Please check your mapbender.conf! ";
		}
		if (!$wms->setGeoRss && ($ajaxResponse->getParameter("setGeoRss") == true)) {
			$messResult .= " Publishing via rss was requested, but this is not configured. Please check your mapbender.conf! ";
		}

		//try {
		$e = new mb_exception("update object in db");
		$wms->overwriteCategories = true;
		$wms->updateObjInDB($wmsId,true);
		//}
		//catch (Exception $e) {
		//	$ajaxResponse->setSuccess(false);
		//	$ajaxResponse->setMessage(_mb("Could not update wms object in database!"));
		//	$ajaxResponse->send();						
		//}
		$e = new mb_exception("object in db updated");
		
		$ajaxResponse->setMessage($messResult);
		$ajaxResponse->setSuccess(true);		
		break;
	case "getContactMetadata" :
		$mbGroupId = $ajaxResponse->getParameter("id");
		$sql = <<<SQL
SELECT mb_group_name, mb_group_title, mb_group_address, mb_group_email, mb_group_postcode, mb_group_city, mb_group_logo_path, mb_group_voicetelephone FROM mb_group WHERE mb_group_id = $1
SQL;
		$v = array($mbGroupId);
		$t = array('i');
		$res = db_prep_query($sql,$v,$t);
		$row = array();
		if ($res) {
			$row = db_fetch_assoc($res);
			$resultObj["fkey_mb_group_id"] = $mbGroupId;
			$resultObj["mb_group_name"] = $row["mb_group_name"];
			$resultObj["mb_group_title"] = $row["mb_group_title"];
			$resultObj["mb_group_address"] = $row["mb_group_address"];
			$resultObj["mb_group_email"] = $row["mb_group_email"];
			$resultObj["mb_group_postcode"] = $row["mb_group_postcode"];
			$resultObj["mb_group_city"] = $row["mb_group_city"];
			$resultObj["mb_group_logo_path"] = $row["mb_group_logo_path"];
			$resultObj["mb_group_voicetelephone"] = $row["mb_group_voicetelephone"];	
		}
		$ajaxResponse->setResult($resultObj);
		$ajaxResponse->setSuccess(true);
		break;
	case "getLicenceInformation" :
		$termsofuseId = $ajaxResponse->getParameter("id");
		$sql = <<<SQL
SELECT name, symbollink, description, descriptionlink, isopen FROM termsofuse WHERE termsofuse_id = $1
SQL;
		$v = array($termsofuseId);
		$t = array('i');
		$res = db_prep_query($sql,$v,$t);
		$row = array();
		if ($res) {
			$row = db_fetch_assoc($res);
			$resultObj["termsofuse_id"] = $termsofuseId;
			$resultObj["name"] = $row["name"];
			$resultObj["symbollink"] = $row["symbollink"];
			$resultObj["description"] = $row["description"];
			$resultObj["descriptionlink"] = $row["descriptionlink"];
			$resultObj["isopen"] = $row["isopen"];
			$ajaxResponse->setResult($resultObj);
			$ajaxResponse->setSuccess(true);
		} else {
			$ajaxResponse->setSuccess(false);
		}
		break;
	case "getWmsIdByLayerId" :
		$layerId = $ajaxResponse->getParameter("layerId");
		$sql = <<<SQL
SELECT fkey_wms_id from layer where layer_id = $1
SQL;
		$v = array($layerId);
		$t = array('i');
		$res = db_prep_query($sql,$v,$t);
		$row = array();
		if ($res) {
			$row = db_fetch_assoc($res);
			$resultObj["wms_id"]= $row['fkey_wms_id']; 
		}
		$ajaxResponse->setResult($resultObj);
		$ajaxResponse->setSuccess(true);
		break;
	case "getMetadataAddon" :
		$metadataId = $ajaxResponse->getParameter("metadataId");
		$mbMetadata = new Iso19139();
		$result = $mbMetadata->createFromDBInternalId($metadataId);
		if ($result) {
			//map metadata object to json return object
			$resultObj["metadata_id"]= $metadataId; //is not part of the object TODO!
			$resultObj["uuid"] = $mbMetadata->fileIdentifier; //char
			$resultObj["origin"] = $mbMetadata->origin; //char
			$resultObj["link"] = $mbMetadata->href; //char
			$resultObj["linktype"] = $mbMetadata->type; //char
			$resultObj["title"] = $mbMetadata->title; //char -- prefill from layer/ft
			$resultObj["abstract"] = $mbMetadata->abstract; //char - prefill from layer/ft
			$resultObj["format"] = $mbMetadata->dataFormat; //char
			$resultObj["ref_system"] = $mbMetadata->refSystem; //char
			$resultObj["spatial_res_type"] = $mbMetadata->spatialResType; //integer
			$resultObj["spatial_res_value"] = $mbMetadata->spatialResValue; //char
			$resultObj["inspire_charset"] = $mbMetadata->inspireCharset; //char
			$resultObj["lineage"] = $mbMetadata->lineage; //text
			$resultObj["tmp_reference_1"] = $mbMetadata->tmpExtentBegin; //text
			$resultObj["tmp_reference_2"] = $mbMetadata->tmpExtentEnd; //text
			$resultObj["west"] = $mbMetadata->wgs84Bbox[0];
			$resultObj["south"] = $mbMetadata->wgs84Bbox[1];
			$resultObj["east"] = $mbMetadata->wgs84Bbox[2];
			$resultObj["north"] = $mbMetadata->wgs84Bbox[3];
			$resultObj["downloadlink"] = $mbMetadata->downloadLinks[0]; //only the first link!
			$resultObj["inspire_whole_area"] = $mbMetadata->inspireWholeArea;
			$resultObj["inspire_actual_coverage"] = $mbMetadata->inspireActualCoverage;
			$resultObj["overview_url"] = $mbMetadata->getExtentGraphic($mbMetadata->wgs84Bbox);
			$export2csw = $mbMetadata->export2Csw; //boolean
			$resultObj["update_frequency"] = $mbMetadata->updateFrequency; //text
			//check for existing polygon
			//$e = new mb_exception("mb_metadata_server.php: count of polygon points ".count($mbMetadata->polygonalExtentExterior));
			if (count($mbMetadata->polygonalExtentExterior) >= 1) {	
				$e = new mb_notice("mb_metadata_server.php: count of polygon points ".count($mbMetadata->polygonalExtentExterior));
				$resultObj["has_polygon"] = true;
			} else {
				$resultObj["has_polygon"] = false;	
			}
			switch ($export2csw) {
				case "t" :
					$resultObj["export2csw"] = true;
					break;
				case "f" :
					$resultObj["export2csw"] = false;
					break;
				default:
				break;	
			}
			$inspire_top_consistence = $mbMetadata->inspireTopConsistence; //boolean
			switch ($inspire_top_consistence) {
				case "t" :
					$resultObj["inspire_top_consistence"] = true;
					break;
				case "f" :
					$resultObj["inspire_top_consistence"] = false;
					break;
				default:
				break;	
			}
			switch ($mbMetadata->inspireDownload) {
				case 0 :
					$resultObj["inspire_download"] = false;
					break;
				case 1 :
					$resultObj["inspire_download"] = true;
					break;
				default:
				break;	
			}
			//categories and keywords
			$resultObj["md_md_topic_category_id"] = $mbMetadata->isoCategories;
			$resultObj["md_custom_category_id"] = $mbMetadata->customCategories;
			$resultObj["md_inspire_category_id"] = $mbMetadata->inspireCategories;
			//only pull keywords without a thesaurus name!!
			for ($i = 0; $i < count($mbMetadata->keywords); $i++) {
				if ($mbMetadata->keywordsThesaurusName[$i] == "" or $mbMetadata->keywordsThesaurusName[$i] == "none") {
					$resultObj["keywords"][] = $mbMetadata->keywords[$i];	
				}
			}
			$resultObj["keywords"] = implode(",",$resultObj["keywords"]);
			//give back result:
			$ajaxResponse->setResult($resultObj);
			$ajaxResponse->setSuccess(true);
			break;
		} else {
			//could not read metadata from db
			$ajaxResponse->setMessage(_mb("Could not get metadata object from database!"));
			$ajaxResponse->setSuccess(false);
			break;
		}
	case "getInitialLayerMetadata" :
		$layerId = $ajaxResponse->getParameter("layerId");
		$metadataId = $ajaxResponse->getParameter("metadataId");
		$sql = <<<SQL
SELECT layer_title, layer_abstract, minx as west, miny as south, maxx as east, maxy as north   
FROM layer INNER JOIN layer_epsg ON layer.layer_id = layer_epsg.fkey_layer_id WHERE layer_id = $1 AND epsg = 'EPSG:4326';
SQL;
		$v = array($layerId);
		$t = array('i');
		$res = db_prep_query($sql,$v,$t);
		$row = array();
		if ($res) {
			$row = db_fetch_assoc($res);
			$resultObj["title"]= $row['layer_title']; //serial
			$resultObj["abstract"] = $row["layer_abstract"]; //char	
			$resultObj["west"]= $row['west']; //double
			$resultObj["south"] = $row["south"]; //double
			$resultObj["east"]= $row['east']; //double
			$resultObj["north"] = $row["north"]; //double
		}
		$ajaxResponse->setResult($resultObj);
		$ajaxResponse->setSuccess(true);
		break;
	case "updateMetadataAddon" :
		$metadataId = $ajaxResponse->getParameter("metadataId");
		$resourceId = $ajaxResponse->getParameter("resourceId");
		$resourceType = $ajaxResponse->getParameter("resourceType");
		//get json data from ajax call
		$data = $ajaxResponse->getParameter("data");
		//initialize actual metadata object from db!
		$mbMetadata = new Iso19139();
		$result = $mbMetadata->createFromDBInternalId($metadataId);
		if ($result) {
			if ($data->export2csw) {
				$mbMetadata->export2Csw = 't';
			} else {
				$mbMetadata->export2Csw = 'f';
			}
			if ($data->inspire_top_consistence) {
				$mbMetadata->inspireTopConsistence = 't';
			} else {
				$mbMetadata->inspireTopConsistence = 'f';
			}
			if ($data->inspire_download) {
				$mbMetadata->inspireDownload = 1;
			} else {
				$mbMetadata->inspireDownload = 0;
			}
			//$mbMetadata->fileIdentifier = $metadataId;
			$mbMetadata->href = $data->link;
			$mbMetadata->title = $data->title;
			$mbMetadata->abstract = $data->abstract;
			$mbMetadata->dataFormat = $data->format;
			$mbMetadata->refSystem = $data->ref_system;
			$mbMetadata->tmpExtentBegin = $data->tmp_reference_1;
			$mbMetadata->tmpExtentEnd = $data->tmp_reference_2;
			$mbMetadata->lineage = $data->lineage;
			$mbMetadata->spatialResType = $data->spatial_res_type;
			$mbMetadata->spatialResValue = $data->spatial_res_value;
			$mbMetadata->inspireCharset = $data->inspire_charset;
			$mbMetadata->updateFrequency = $data->update_frequency;
			$mbMetadata->downloadLinks = array($data->downloadlink);
			//$mbMetadata->polygonalExtentExterior = null; //this will delete existing polygons!
			if (isset($data->inspire_whole_area) && $data->inspire_whole_area != "") {
				$mbMetadata->inspireWholeArea = $data->inspire_whole_area;
			} else {
				$mbMetadata->inspireWholeArea = 0;
			}
			if (isset($data->inspire_actual_coverage) && $data->inspire_actual_coverage != "") {
				$mbMetadata->inspireActualCoverage = $data->inspire_actual_coverage;
			} else {
				$mbMetadata->inspireActualCoverage = 0;
			}
			//categories ...
			//new for keywords and classifications:
			if (isset($data->keywords) && $data->keywords != "") {
				$mbMetadata->keywords = array_map('trim',explode(',',$data->keywords));
				//for all those keywords don't set a special thesaurus name
				foreach ($mbMetadata->keywords as $keyword) {
					$mbMetadata->keywordsThesaurusName[] = "none";
				}
			}
			if (isset($data->md_md_topic_category_id)) {
				$mbMetadata->isoCategories = $data->md_md_topic_category_id;
			}
			if (isset($data->md_inspire_category_id)) {
				$mbMetadata->inspireCategories = $data->md_inspire_category_id;
			}
			if (isset($data->md_custom_category_id)) {
				$mbMetadata->customCategories = $data->md_custom_category_id;
			}
			//use information from bbox!
			if (isset($data->west)) {
				$mbMetadata->wgs84Bbox[0] = $data->west;
			}
			if (isset($data->east)) {
				$mbMetadata->wgs84Bbox[2] = $data->east;
			}
			if (isset($data->north)) {
				$mbMetadata->wgs84Bbox[3] = $data->north;
			}
			if (isset($data->south)) {
				$mbMetadata->wgs84Bbox[1] = $data->south;
			}	
			//try to update metadata object (only mb_metadata)
			$res = $mbMetadata->updateMetadataById($metadataId);
			if (!$res) {
				//could not update metadata in db
				$ajaxResponse->setMessage(_mb("Could not update metadata object in database!"));
				$ajaxResponse->setSuccess(false);
			} else {
				//update relations for keywords and categories
				$mbMetadata->insertKeywordsAndCategoriesIntoDB($metadataId,$resourceType,$resourceId);
				$ajaxResponse->setMessage(_mb("Edited metadata was updated in the mapbender database!"));
				$ajaxResponse->setSuccess(true);
			}
		} else {
			//could not read metadata from db
			$ajaxResponse->setMessage(_mb("Could not get metadata object from database!"));
			$ajaxResponse->setSuccess(false);
		}
		break;
	case "getOwnedMetadata" :
		$layerId = $ajaxResponse->getParameter("layerId");
		$user = new User(Mapbender::session()->get("mb_user_id"));
		//$e = new mb_exception("plugins/mb_metadata_wfs_server.php: user_id: ".$user->id);
		//Here a new handling is needed. Get coupled metadata entries from owned services - ows / content /md_relation / title of the coupled metadata maybe extented with union on owned metadata entries from big metador

$sql = <<<SQL
select distinct metadata_id, title from (
select metadata_id, title from (select fkey_metadata_id from (select layer_id from
 (select wms_id from wms where wms_owner = $1) wms inner join layer on wms.wms_id = layer.fkey_wms_id) layer 
inner join ows_relation_metadata on layer.layer_id = ows_relation_metadata.fkey_layer_id where layer.layer_id <> $2) ows_r_m inner 
join mb_metadata on ows_r_m.fkey_metadata_id = mb_metadata.metadata_id union 
select distinct metadata_id, title from (select fkey_metadata_id from (select featuretype_id 
from (select wfs_id from wfs where wfs_owner = $1) wfs inner join wfs_featuretype on 
wfs.wfs_id = wfs_featuretype.fkey_wfs_id) featuretype inner join ows_relation_metadata on 
featuretype.featuretype_id = ows_relation_metadata.fkey_featuretype_id) ows_r_m inner 
join mb_metadata on ows_r_m.fkey_metadata_id = mb_metadata.metadata_id) as foo order by metadata_id
SQL;
		$v = array($user->id, $layerId);
		$t = array('i','i');
		$res = db_prep_query($sql,$v,$t);
		$row = array();
		$resultObj = array();
		$i = 0;
		while ($row = db_fetch_assoc($res)) {
			$resultObj[$i]->metadataId = $row['metadata_id']; //integer
			$resultObj[$i]->metadataTitle = $row["title"]; //char	
			$i++;
		}
		$ajaxResponse->setResult($resultObj);
		$ajaxResponse->setSuccess(true);
		break;
	case "insertMetadataAddon" :
		$resourceId = $ajaxResponse->getParameter("resourceId");
		$resourceType = $ajaxResponse->getParameter("resourceType");
		$data = $ajaxResponse->getParameter("data");
		//normaly the link is only set if a link has been created
		//if a record has been created, the link element will be empty 
		//use this to distinguish between the to different inserts!
		//this insert should also push one entry in the ows_relation_metadata table! - after the insert into md_metadata
		//origin
		if ($data->kindOfMetadataAddOn == 'internallink') {
			//instantiate existing metadata
			$mbMetadata = new Iso19139();
			$mbMetadata->createFromDBInternalId($data->internal_relation);
			//$e = new mb_exception("plugins/mb_metadata_server.php: created object from db with id: ".$data->internal_relation);
			$result = $mbMetadata->setInternalMetadataLinkage($data->internal_relation,$resourceType,$resourceId);
			//insert a simple relation to an internal metadata entry - but how can this be distinguished?
			//we need a new column with type of relation - maybe called internal
			$ajaxResponse->setMessage($result['message']);
			$ajaxResponse->setSuccess($result['success']);
			//go out here
			break;
		}
		//$e = new mb_exception("outside case");
		if ($data->kindOfMetadataAddOn == 'link') {
			//generate metador entry
			$origin = 'external';
		} else {
			$origin = 'metador';
		}
		//export
		if ($data->export2csw == "on") {
			$data->export2csw = 't';
		} else {
			$data->export2csw = 'f';
		}
		//consistence
		if ($data->inspire_top_consistence == "on") {
			$data->inspire_top_consistence = 't';
		} else {
			$data->inspire_top_consistence = 'f';
		}
		//generate a uuid for the record:
		$uuid = new Uuid();
		//initialize database objects
		//are initialized from class_iso19139
		$mbMetadata = new Iso19139();
		$randomid = new Uuid();
		//read out json objects 
		if (isset($data->link)) {
			$mbMetadata->href = $data->link;
		}
		if (isset($data->export2csw)) {
			$mbMetadata->export2Csw = $data->export2csw;
		} else {
			$mbMetadata->export2Csw = 'f';
		}
		if (isset($data->title)) {
			$mbMetadata->title = $data->title;
		}
		if (isset($data->abstract)) {
			$mbMetadata->abstract = $data->abstract;
		}
		if (isset($data->format)) {
			$mbMetadata->dataFormat = $data->format;
		}
		if (isset($data->ref_system)) {
			$mbMetadata->refSystem = $data->ref_system;
		}
		if (isset($data->inspire_top_consistence)) {
			$mbMetadata->inspireTopConsistence = $data->inspire_top_consistence;
		} else {
			$mbMetadata->inspireTopConsistence = "f";
		}
		if (isset($data->tmp_reference_1)) {
			$mbMetadata->tmpExtentBegin = $data->tmp_reference_1;
		}
		if (isset($data->tmp_reference_2)) {
			$mbMetadata->tmpExtentEnd = $data->tmp_reference_2;
		}
		if (isset($data->lineage)) {
			$mbMetadata->lineage = $data->lineage;
		}
		if (isset($data->spatial_res_type)) {
			$mbMetadata->spatialResType = $data->spatial_res_type;
		}
		if (isset($data->spatial_res_value)) {
			$mbMetadata->spatialResValue = $data->spatial_res_value;
		}
		if (isset($data->inspire_charset)) {
			$mbMetadata->inspireCharset = $data->inspire_charset;
		}
		if (isset($data->update_frequency)) {
			$mbMetadata->updateFrequency = $data->update_frequency;
		}
		if (isset($data->update_frequency)) {
			$mbMetadata->downloadLinks = array($data->downloadlink);
		}
		//new for keywords and classifications:
		if (isset($data->keywords) && $data->keywords != "") {
			$mbMetadata->keywords = array_map('trim',explode(',',$data->keywords));
			//for all those keywords don't set a special thesaurus name
			foreach ($mbMetadata->keywords as $keyword) {
				$mbMetadata->keywordsThesaurusName[] = "none";
			}
		}
		if (isset($data->md_md_topic_category_id)) {
			$mbMetadata->isoCategories = $data->md_md_topic_category_id;
		}
		if (isset($data->md_inspire_category_id)) {
			$mbMetadata->inspireCategories = $data->md_inspire_category_id;
		}
		if (isset($data->md_custom_category_id)) {
			$mbMetadata->customCategories = $data->md_custom_category_id;
		}
		//use information from bbox!
		if (isset($data->west)) {
			$mbMetadata->wgs84Bbox[0] = $data->west;
		}
		if (isset($data->east)) {
			$mbMetadata->wgs84Bbox[2] = $data->east;
		}
		if (isset($data->north)) {
			$mbMetadata->wgs84Bbox[3] = $data->north;
		}
		if (isset($data->south)) {
			$mbMetadata->wgs84Bbox[1] = $data->south;
		}	
		$e = new mb_exception("whole area: ".$data->inspire_whole_area);
		if (isset($data->inspire_whole_area) && $data->inspire_whole_area != "") {
			$mbMetadata->inspireWholeArea = $data->inspire_whole_area;
		} else {
			$mbMetadata->inspireWholeArea = 0;
		}
		if (isset($data->inspire_actual_coverage) && $data->inspire_actual_coverage != "") {
			$mbMetadata->inspireActualCoverage = $data->inspire_actual_coverage;
		} else {
			$mbMetadata->inspireActualCoverage = 0;
		}
		if ($data->inspire_download == "on") {
			$mbMetadata->inspireDownload = 1;
		} else {
			$mbMetadata->inspireDownload = 0;
		}
		//Check if origin is external and export2csw is activated!
		if ($origin == 'external' ) {
			//harvest link from location, parse the content for datasetid and push xml into data column
			$mdOwner = Mapbender::session()->get("mb_user_id");
			$mbMetadata->randomId = $randomid;
			$mbMetadata->format = "text/xml";
			$mbMetadata->type = "ISO19115:2003";
			$mbMetadata->origin = "external";
			$mbMetadata->owner = $mdOwner;
			$result = $mbMetadata->insertToDB($resourceType,$resourceId);
			if ($result['value'] == false){
				$e = new mb_exception("Problem while storing metadata to mb_metadata table!");
				$e = new mb_exception($result['message']);
				abort($result['message']);
			} else {
				$ajaxResponse->setMessage("Stored metadata from external link to mapbender database!");
				$ajaxResponse->setSuccess(true);
				$e = new mb_notice("Stored metadata from external link to mapbender database!");
			}	
		} else { //fill thru metador
			$mbMetadata->origin = "metador";
			$mbMetadata->fileIdentifier = $uuid;
			$mbMetadata->randomId = $randomid;
			$result = $mbMetadata->insertToDB($resourceType,$resourceId);
			$e = new mb_exception("test to metadata insert/update via metador!");
			if ($result['value'] == false) {
				$e = new mb_exception("Problem while storing metadata from editor to mb_metadata table!");
				$e = new mb_exception($result['message']);
				abort($result['message']);
			} else {
				$e = new mb_notice("Metadata with id ".$randomid." stored from editor to db!");
				$ajaxResponse->setMessage("Metadata with id ".$randomid." stored from editor to db!");
				$ajaxResponse->setSuccess(true);
			}
		}		
		break;
	case "deleteInternalMetadataLinkage" :
		$metadataId = $ajaxResponse->getParameter("metadataId");
		$resourceType = $ajaxResponse->getParameter("resourceType");
		$resourceId = $ajaxResponse->getParameter("resourceId");
		$mbMetadata = new Iso19139();
		$result = $mbMetadata->deleteInternalMetadataLinkage($resourceType, $resourceId, $metadataId);
		$ajaxResponse->setSuccess($result['success']);
		$ajaxResponse->setMessage($result['message']);
		break;
	case "deleteMetadataAddon" :
		$metadataId = $ajaxResponse->getParameter("metadataId");
		$resourceId = $ajaxResponse->getParameter("resourceId");
		$resourceType = $ajaxResponse->getParameter("resourceType");		
		$mbMetadata = new Iso19139();
		$result = $mbMetadata->deleteMetadataAddon($resourceType, $resourceId, $metadataId); //$contentType = "layer" or "featuretype" or ...
		$ajaxResponse->setSuccess($result['success']);
		$ajaxResponse->setMessage($result['message']);
		break;
	case "importGmlAddon":
		$filename = $ajaxResponse->getParameter("filename");
		$metadataId = $ajaxResponse->getParameter("metadataId");
		$gml = file_get_contents($filename);
		if (!$gml){
			abort(_mb("Reading file ".$filename." failed!"));
		}
		$wktPolygon = gml2wkt($gml);
		if ($wktPolygon) {
			//insert polygon into database
			$sql = <<<SQL
UPDATE mb_metadata SET bounding_geom = $2 WHERE metadata_id = $1			
SQL;
			$v = array($metadataId, $wktPolygon);
			//$e = new mb_exception($metadataId);
			$t = array('i','POLYGON');
			$res = db_prep_query($sql,$v,$t);
			if (!$res) {
				abort(_mb("Problem while storing geometry into database!"));
			} else {
				//build new preview url if possible and give it back in ajax response
				
				$ajaxResponse->setMessage("Stored successfully geometry into database!");
				$ajaxResponse->setSuccess(true);
			}
		} else {
			abort(_mb("Converting GML to WKT failed!"));
		}
		//parse gml and extract multipolygon to wkt representation
		//push multipolygon into database
		
	break;
	case "deleteGmlPolygon" :
		$metadataId = $ajaxResponse->getParameter("metadataId");
		$e = new mb_notice("metadataId: ".$metadataId);
		$sql = <<<SQL
UPDATE mb_metadata SET bounding_geom = NULL WHERE metadata_id = $1			
SQL;
		$v = array($metadataId);
		//$e = new mb_exception($metadataId);
		$t = array('i');
		$res = db_prep_query($sql,$v,$t);
		if (!$res) {
			abort(_mb("Problem while deleting geometry from database!"));
		} else {
			$ajaxResponse->setMessage("Deleted surrounding geometry from metadata record!");
			$ajaxResponse->setSuccess(true);
		}
	break;
	case "importXmlAddon" :
		//this case is similar to insert the metadata from external link, but came from internal file from tmp folder which has been uploaded before
		$resourceId = $ajaxResponse->getParameter("resourceId");
		$resourceType = $ajaxResponse->getParameter("resourceType");
		$filename = $ajaxResponse->getParameter("filename");
		//normaly the link is only set if a link has been created
		//if a record has been created, the link element will be empty 
		//use this to distinguish between the to different inserts!
		//this insert should also push one entry in the ows_relation_metadata table! - after the insert into md_metadata
		$randomid = new Uuid();	
		$e = new mb_notice("File to load: ".$filename);
		$metaData = file_get_contents($filename);
		if (!$metaData){
			abort(_mb("Reading file ".$filename." failed!"));
		}
		$mbMetadata = new Iso19139();
		$mdOwner = Mapbender::session()->get("mb_user_id");
		$mbMetadata->randomId = $randomid;
		$mbMetadata->metadata = $metaData;
		$mbMetadata->format = "text/xml";
		$mbMetadata->type = "ISO19115:2003";
		$mbMetadata->origin = "upload";
		$mbMetadata->owner = $mdOwner;
		$result = $mbMetadata->insertToDB($resourceType,$resourceId);
		if ($result['value'] == false){
			$e = new mb_exception("Problem while storing uploaded metadata xml to mb_metadata table!");
			$e = new mb_exception($result['message']);
			abort($result['message']);
				
		} else {
			$e = new mb_notice("Metadata with random id ".$randomid." stored to db!");
			$ajaxResponse->setMessage("Uploaded metadata object inserted into md_metadata table!");
			$ajaxResponse->setSuccess(true);
		}
		break;		
	default: 
		$ajaxResponse->setSuccess(false);
		$ajaxResponse->setMessage(_mb("An unknown error occured."));
		break;
}

function setSearchable($layer_searchable_id, $objLayer){
    foreach ($objLayer as $layer) {
        if ($layer_searchable_id == intval($layer->layer_uid)) {
            $layer->layer_searchable = 1;
//            $e = new mb_notice("mb_metadata_server.php: Layer identical - update it in wms object");
            $layerParent= $layer;
            foreach ($objLayer as $subLayer) {
                if (intval($layerParent->layer_pos) == intval($subLayer->layer_parent)) {
                    $subLayers[] = $subLayer;
                }
            }
            if(isset($subLayers)){
                setSearchableSublayer($layerParent, $subLayers, $objLayer);
                unset($subLayers);
                unset($layerParent);
            }
        }
    }
}

function setSearchableSublayer($layerParent, $subLayers, $mainLayers){
    foreach ($subLayers as $subLayer) {
        $subLayer->layer_searchable = 1;
//        $e = new mb_notice("mb_metadata_server.php: Layer identical - update it in wms object");
        $newLayerParent = $subLayer;
        foreach ($mainLayers as $newSubLayer) {
            if ($newSubLayer->layer_parent!="" && intval($newLayerParent->layer_pos) == intval($newSubLayer->layer_parent)) {
                $newSubLayers[] = $newSubLayer;
            }
        }
        if(isset($newSubLayers)){
            setSearchableSublayer($newLayerParent, $newSubLayers, $mainLayers);
            unset($newSubLayers);
            unset($newLayerParent);
        }
    }
}


$ajaxResponse->send();
?>
