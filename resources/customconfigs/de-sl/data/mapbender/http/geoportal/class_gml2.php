<?php
# $Id: class_gml2.php 3099 2008-10-02 15:29:23Z nimix $
# http://www.mapbender.org/index.php/class_gml2.php
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

require_once(dirname(__FILE__)."/../../core/globalSettings.php");
require_once(dirname(__FILE__)."/../classes/class_connector.php");
require_once(dirname(__FILE__)."/../classes/class_json.php");

class gml2 {
	var $geomtype_point = 'Point';					
	var $geomtype_polygon = 'Polygon';
	var $geomtype_line = 'LineString';
	var $geomtype_multipolygon = 'MultiPolygon'; 
	var $geomtype_multiline = 'MultiLine';
	var $geometries = array();	
	var $member = -1;
	var $geomtype = array();	
	var $keys = array();
	var $values = array();
	var $geometry = array();
	var $bbox = array();
	var $doc;
	var $geomFeaturetypeElement = null;
	
	
	function gml2(){
		$this->geometries = array($this->geomtype_point, $this->geomtype_polygon, $this->geomtype_line, $this->geomtype_multipolygon, $this->geomtype_multiline);
	}
	function getGml($req){
		$x = new connector($req);
		return $x->file;
	}

	function parseFile($req){
		#$data = implode("",file($req));
		$x = new connector($req);
		$data = $x->file;
		$data = $this->removeWhiteSpace($data);
		$this->parseXML($data);		
		#$e = new mb_exception("data = ".$data); 		
	}
	
    function parseGeometry($req){
		#$data = implode("",file($req));
		$x = new connector($req);
		$data = $x->file;
		#$e = new mb_exception("data = ".$data);
		$data = $this->removeWhiteSpace($data);
		$envelopeGeom = $this->parseGeom($data);
		return $envelopeGeom;
	}
	
	function parseGeom($data) {
	    $this->doc = $this->removeWhiteSpace($data);
	    $gmlDoc = new SimpleXMLElement($this->doc);
		
		$gmlDoc->registerXPathNamespace('xls', 'http://www.opengis.net/xls');
		$gmlDoc->registerXPathNamespace('wfs', 'http://www.opengis.net/wfs');
		$gmlDoc->registerXPathNamespace('gml', 'http://www.opengis.net/gml');
		
		// build feature collection
		$featureCollection = new FeatureCollection();
		
		// gmlBounding
		$gmlBounding = $gmlDoc->xpath("//gml:boundedBy/gml:Envelope/gml:pos");
		
		if (count($gmlBounding) > 0) {
		    $envelope = array();
		    foreach ($gmlBounding as $coord) {
		        #$e = new mb_exception("coord = ".$coord);
			    #$coordXml = dom_import_simplexml($coord);
		        array_push($envelope, $coord);
		        #$e = new mb_exception("xml = ".$coordXml);
				
				 
			}
			
			return array_unique($envelope);
		}
		else{
			return "";
		}
	}
	
	function parseXML($data) {
		if (func_num_args() == 2) {
			$this->geomFeaturetypeElement = func_get_arg(1);
		}
		$this->doc = $this->removeWhiteSpace($data);
		return $this->toGeoJSON();
	}

	function removeWhiteSpace ($string) {
		return preg_replace("/\>(\s)+\</", "><", trim($string));
	}
	
	function sepNameSpace($s){
		$c = mb_strpos($s,":"); 
		if($c>0){
			return mb_substr($s,$c+1);
		}
		else{
			return $s;
		}		
	}
	
	function toGeoJSON () {
		$gmlDoc = new SimpleXMLElement($this->doc);
		
		$gmlDoc->registerXPathNamespace('xls', 'http://www.opengis.net/xls');
		$gmlDoc->registerXPathNamespace('wfs', 'http://www.opengis.net/wfs');
		$gmlDoc->registerXPathNamespace('gml', 'http://www.opengis.net/gml');
		
		// build feature collection
		$featureCollection = new FeatureCollection();
		
		// segments of the featzreCollection
		$gmlFeatureMembers = $gmlDoc->xpath("//gml:featureMember");
		
		if(count($gmlFeatureMembers)>0){
			$cnt=0;
			foreach ($gmlFeatureMembers as $gmlFeatureMember) {
				$featureMember_dom = dom_import_simplexml($gmlFeatureMember);
				
				$feature = new Feature();
				if ($this->geomFeaturetypeElement != null) {
					$feature->parse($featureMember_dom, $this->geomFeaturetypeElement);
				}
				else {
					$feature->parse($featureMember_dom);
				}
				if (isset($feature->geometry)) {
					$featureCollection->addFeature($feature);
				}
				$cnt++;
			}
			
			return $featureCollection->toGeoJSON();
		}
		else{
			return "{}";
		}
	}

	
	/**
	 * Exports the file to SHP.
	 * 
	 * @param string $filenamePrefix the filename without an ending like .shp
	 */
	function toShape ($filenamePrefix) {
		$unique = TMPDIR . "/" . $filenamePrefix;
		$fShape = $unique.".shp";
		$fGml = $unique.".gml";
//		$pathOgr = '/appserver/postgresql/templates/postgresql824/bin/ogr2ogr';
		$pathOgr = '/usr/bin/ogr2ogr';
//		$pathOgr = OGR2OGR_PATH;
		$w = $this->toFile($fGml);
		// - get EPSC-Code from GML-File--
		
		$data = file_get_contents($fGml);//		
		$gmlDoc = new SimpleXMLElement($data);
				
		$gmlDoc->registerXPathNamespace('xls', 'http://www.opengis.net/xls');
		$gmlDoc->registerXPathNamespace('gml', 'http://www.opengis.net/gml');

		$gmlBboxes = $gmlDoc->xpath("//gml:Box");	//<gml:Box srsName="http://www.opengis.net/gml/srs/epsg.xml#4326">
		
		if(count($gmlBboxes)>0){
			$bbox_str = $gmlBboxes[0]['srsName'];			
			$pos_raute = strpos($bbox_str, '#');
			$epsg = substr($bbox_str, $pos_raute+1);

		} else {
			$e = new mb_exception("class_gml2.php:toShape() => no EPSG in GML(" . $fGml . ").");
		}
		// ---
		
		
		$str_ogr = $pathOgr.' -a_srs EPSG:'.$epsg.' -f "ESRI Shapefile" "'.$fShape.'" '.$fGml;
		$e = new mb_exception("ogr-Befehl:".$str_ogr);
		
 		$exec = $pathOgr.' -a_srs EPSG:'.$epsg.' -f "ESRI Shapefile" "'.$fShape.'" '.$fGml;
		
		
		/*
		 * @security_patch exec done
		 * Added escapeshellcmd()
		 */

 		//$exec = $pathOgr.' -f "ESRI Shapefile" "'.$fShape.'" '.$fGml;
 		exec(escapeshellcmd($exec));
 		
 		$exec = 'zip -j '.$unique.' '.$unique.'.shp '.$unique.'.dbf '.$unique.'.shx '.$unique.'.gfs '.$unique.'.gml '.$unique.'.prj ';
 		exec(escapeshellcmd($exec));

		$exec = 'rm -f '.$unique.' '.$unique.'.shp '.$unique.'.dbf '.$unique.'.shx '.$unique.'.gfs '.$unique.'.gml '.$unique.'.prj ';
		exec(escapeshellcmd($exec));
		
		$exec = 'chmod 777 '.$unique.'.*';
		exec(escapeshellcmd($exec));
		//echo "<a href='../tmp/".$unique.".zip'>Download ".$prefix."<a>";
	}
	
	/**
	 * Writes a file containing the GML.
	 * 
	 * @param string $path the path to the file.
	 * @param string $path the path to the file.
	 * @return bool true if the file could be saved; else false.
	 */
	function toFile ($file) {
		$handle = fopen($file, "w");
		if (!$handle) {
			$e = new mb_exception("class_gml2.php: Filehandler error (" . $file . ").");
			return false;
		}
		if (!fwrite($handle,$this->__toString())) {
			$e = new mb_exception("class_gml2.php: Could not write file (" . $file . ").");
			fclose($handle);
			return false;
		}
		fclose($handle);
		return true;
	}
	
	function __toString () {
		return $this->doc;
	}



	/**
	 * @deprecated
	 */
	function parsegml($req){
		#$data = implode("",file($req));
		$x = new connector($req);
		$this->parse_xml($x->file);		 		
	}

	/**
	 * @deprecated
	 */
	function parse_xml($data){


		$this->doc = $data;

		$section = false;
		$geom = false;
		$boundedBy = false;
		$coordinates = false;
		$el = -1;
		$parser = xml_parser_create(CHARSET);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1);
		xml_parser_set_option($parser,XML_OPTION_TARGET_ENCODING,CHARSET);
		xml_parse_into_struct($parser,$data,$values,$tags);
		$code = xml_get_error_code ($parser);
		if ($code) {
			$line = xml_get_current_line_number($parser); 
			$mb_exception = new mb_exception(xml_error_string($code) .  " in line " . $line);
		}
		xml_parser_free($parser);
		
		foreach ($values as $element) {
			#$e = new mb_exception($element['tag']);
			if(strtoupper($this->sepNameSpace($element['tag'])) == strtoupper("boundedBy") && $element['type'] == "open"){
				$boundedBy = true;
			}
			if ($boundedBy) {
				if(strtoupper($this->sepNameSpace($element['tag'])) == strtoupper("box")){
					
					$epsgAttr = "";
					if(isset($element['attributes'])){
						$epsgAttr = isset($element['attributes']['srsName']) ? $element['attributes']['srsName'] : "";
					}
		
					if(strstr($epsgAttr,'#') !== false){
						// http://www.opengis.net/gml/srs/epsg.xml#4326
						$parts = explode('#',$epsgAttr);
						$epsg == isset($parts[1]) ? $parts[1] : "";
					}else if (strstr($epsgAttr,':') !== false){
						// EPSG:31466
						// urn:ogc:def:crs:EPSG:6.5:4326 
						$parts = explode(':',$epsgAttr);
						$parts = array_reverse($parts);
						 if(isset($parts[0])){
						 	$epsg =  $parts[0];
						}else{
							$epsg = "";
						}
						new mb_exception(print_r(isset($parts[0]),true));
						new mb_exception(print_r($epsg,true));
					}else {
						$epsg="";
					}
					$this->epsg = $epsg;
				}
				if(strtoupper($this->sepNameSpace($element['tag'])) == strtoupper("coordinates")){
					$this->bbox = explode(",", str_replace(",,","",str_replace(" ",",",trim($element['value']))));
					$boundedBy=false;
				}
			}
			if(strtoupper($this->sepNameSpace($element['tag'])) == strtoupper("featureMember") && $element['type'] == "open"){
				$this->member++;
				$this->keys[$this->member] = array();
				$this->value[$this->member] = array();
				$this->geometry[$this->member] = array();
				$section = true;
				$cnt_geom = 0;
			}
			if($section == true){
				if( in_array($this->sepNameSpace($element['tag']),$this->geometries) && $element['type'] == "open"){
					$geom = true;
					$this->geomtype[$this->member] = $this->sepNameSpace($element['tag']);
				}
				else if(in_array($this->sepNameSpace($element['tag']),$this->geometries) && $element['type'] == "close"){
					$cnt_geom++;
					$geom = false;
				}
				if($geom == true){
					if (strtoupper($this->sepNameSpace($element['tag'])) == strtoupper("coordinates")) {
						$this->geometry[$this->member][$cnt_geom] =  str_replace(",,","",str_replace(" ",",",trim($element['value'])));
						$coordinates = true;
						// XXX: Increment counter to get all geometries of a feature member, 
						// comment it out to only show first geometry of featuremember
						$cnt_geom++;
					}
					else if (!$coordinates && trim($element['value'])) {
						$coords = str_replace(",,","",str_replace(" ",",",trim($element['value'])));
						$tmp_array = explode(",", $coords);
						if (count($tmp_array > 1)) {
							$this->geometry[$this->member][$cnt_geom] =  $coords;
							$coordinates = true;
						   // XXX: Increment counter to get all geometries of a feature member, 
						   // comment it out to only show first geometry of featuremember
							$cnt_geom++;
						}
					}
				}
				else if(strtoupper($this->sepNameSpace($element['tag'])) == strtoupper("featureMember") && $element['type'] == "close"){
					$section = false;	
					$el = -1;
				}
				else{
					$el++;
					$this->values[$this->member][$el] = $element['value'];
					$this->keys[$this->member][$el] = $element['tag'];	
				}
			}
		}
	}	

	/**
	 * @deprecated
	 */
	function getMemberCount(){
		return ($this->member+1);	
	}
	
	/**
	 * @deprecated
	 */
	function getValueBySeparatedKey($memberCount,$keyName){
		for($i=0; $i<count($this->keys[$memberCount]); $i++){
			if($this->sepNameSpace($this->keys[$memberCount][$i]) == $keyName){
				return $this->values[$memberCount][$i];
			}	
		}
	}
	
	/**
	 * @deprecated
	 */
	function getValueByKey($memberCount,$keyName){
		for($i=0; $i<count($this->keys[$memberCount]); $i++){
			if($this->keys[$memberCount][$i] == $keyName){
				return $this->values[$memberCount][$i];
			}	
		}
	}
	
	/**
	 * @deprecated
	 */
	function getXfromMemberAsString($memberCount,$geomCount){
		$t = explode(",",$this->geometry[$memberCount][$geomCount]);
		$x = array();
		for($i=0; $i<(count($t)-1); $i=$i+2){
			array_push($x,$t[$i]);
		}
		return implode(",",$x);
	}
	
	/**
	 * @deprecated
	 */
	function getYfromMemberAsString($memberCount,$geomCount){
		$t = explode(",",$this->geometry[$memberCount][$geomCount]);
		$y = array();
		for($i=1; $i<=(count($t)-1); $i=$i+2){
			array_push($y,$t[$i]);
		}
		return implode(",",$y);
	}
	
	/**
	 * @deprecated
	 */
	function getGeometriesFromMember($memberCount){
		return $this->geometry[$memberCount];	
	}
	
	/**
	 * @deprecated
	 */
	function getGeometryTypeFromMember($memberCount){
		return 	$this->geomtype[$memberCount];
	}

	/**
	 * @deprecated
	 */
	function exportGeometriesToJS ($isInFrame) {
		$prefix = "";
		if ($isInFrame == true) {
			$prefix = "parent.";
		}
		$js = "";
		$js .= "var geom = new ".$prefix."GeometryArray();\n";
		for ($i=0; $i<count($this->geometry); $i++) {
			$js .= $this->exportMemberToJS($i, $isInFrame);
			$js .= "geom.addCopy(q);\n";
		}
		return $js;
	}
	
	/**
	 * @deprecated
	 */
	function exportMemberToJS ($i, $isInFrame) {
		$prefix = "";
		if ($isInFrame == true) {
			$prefix = "parent.";
		}
		$js = "";
		if ($this->getGeometryTypeFromMember($i) == $this->geomtype_point) {
			$js .= "var current_geomtype = ".$prefix."geomType.point;\n";
		}
		elseif ($this->getGeometryTypeFromMember($i) == $this->geomtype_line || $this->getGeometryTypeFromMember($i) == $this->geomtype_multiline) {
			$js .= "var current_geomtype = ".$prefix."geomType.line;\n";
		}
		elseif ($this->getGeometryTypeFromMember($i) == $this->geomtype_polygon || $this->getGeometryTypeFromMember($i) == $this->geomtype_multipolygon) {
			$js .= "var current_geomtype = ".$prefix."geomType.polygon;\n";
		}
		else {
			$e = new mb_notice("unknown geometry type: '".$this->getGeometryTypeFromMember($i)."' or no geometry existing");
			return "";
		}	
		
		$js .= "var q = new ".$prefix."MultiGeometry(current_geomtype);\n";
		
		for ($j=0; $j<count($this->geometry[$i]); $j++) {
			$js .= "q.addGeometry(current_geomtype);\n";
			
			$x_array = explode(",", $this->getXfromMemberAsString($i, $j));
			$y_array = explode(",", $this->getYfromMemberAsString($i, $j));
			
			for ($k=0; $k<count($x_array); $k++) {
				$js .= "q.get(-1).addPointByCoordinates(parseFloat(".$x_array[$k]."), parseFloat(".$y_array[$k]."));\n";
				//$js .= "alert(parseFloat(".$x_array[$k]."), parseFloat(".$y_array[$k]."));";
			}
			$js .= "q.get(-1).close();\n";
		}
//		$js .= "alert(q);\n";
		return $js;
	}

}




class FeatureCollection {
	var $type = "FeatureCollection";
	var $featureArray = array();
	
	public function __construct() {
		
	}
	
	public function addFeature ($aFeature) {
		array_push($this->featureArray, $aFeature);
	}
	
	public function toGeoJSON () {
		$str = "";
		$len = count($this->featureArray); 
		if ($len > 0) {
			$str .= "{\"type\": \"FeatureCollection\", \"features\": [";
			for ($i=0; $i < $len; $i++) {
				if ($i > 0) {
					$str .= ",";
				}	
				$str .= $this->featureArray[$i]->toGeoJSON();
			}
			$str .= "]}";
		}
		return $str;
	}
}

class Feature {
	var $type = "Feature";
	var $fid;
	var $geometry = false;
	var $properties = array();
	var $geomFeaturetypeElement = null;
	
	public function __construct() {
	}
	
	function sepNameSpace($s){
		list($ns,$FeaturePropertyName) = split(":",$s);
		$nodeName = array('ns' => $ns, 'value' => $FeaturePropertyName);
		return $nodeName;
	}
	
	/**
	 * Parses the feature segment of a GML and stores the geometry in the
	 * $geometry variable of the class.
	 * 	
	 * Example of a feature segment of a GML. 
	 * 	<gml:featureMember>
	 * 		<ms:ROUTE fid="ROUTE.228168">
	 * 			<gml:boundedBy>
	 * 				<gml:Box srsName="EPSG:31466">
	 * 					<gml:coordinates>2557381.0,5562371.1 2557653.7,5562526.0</gml:coordinates>
	 * 				</gml:Box>
	 * 			</gml:boundedBy>
	 * 			<ms:geometry>
	 * 				<gml:LineString>
	 * 					<gml:coordinates>
	 * 						2557380.97,5562526 2557390.96,
	 * 						5562523.22 2557404.03,5562518.2 2557422.31,
	 * 						5562512 2557437.16,5562508.37 2557441.79,
	 * 						5562507.49 2557454.31,5562505.1 2557464.27,
	 * 						5562503.97 2557473.24,5562502.97 2557491.67,
	 * 						5562502.12 2557505.65,5562502.43 2557513.78,
	 * 						5562501.12 2557520.89,5562498.79 2557528.5,
	 * 						5562495.07 2557538.9,5562488.91 2557549.5,
	 * 						5562483.83 2557558.55,5562476.61 2557569.07,
	 * 						5562469.82 2557576.61,5562462.72 2557582.75,
	 * 						5562457.92 2557588.57,5562452.56 2557590.38,
	 * 						5562449.69 2557593.57,5562445.07 2557596.17,
	 * 						5562441.31 2557601.71,5562433.93 2557612.97,
	 * 						5562421.03 2557626,5562405.33 2557639.66,
	 * 						5562389.75 2557653.69,5562371.12 
	 * 					</gml:coordinates>
	 * 				</gml:LineString>
	 * 			</ms:geometry>
	 * 			<code>354</code>
	 * 			<Verkehr>0</Verkehr>
	 * 			<rlp>t</rlp>
	 * 		</ms:ROUTE>
	 * 	</gml:featureMember>
	 * 
	 * @return void
	 * @param $domNode DOMNodeObject the feature tag of the GML 
	 * 								(<gml:featureMember> in the above example)
	 */
	public function parse($domNode) {
		if (func_num_args() == 2) {
			$this->geomFeaturetypeElement = func_get_arg(1);
		}

		$currentSibling = $domNode->firstChild;
		
		$this->fid = $currentSibling->getAttribute("fid");
		
		$currentSibling = $currentSibling->firstChild;
		
		while ($currentSibling) {
		
			$name = $currentSibling->nodeName;
			$value = $currentSibling->nodeValue;
			
			$namespace = $this->sepNameSpace($name);
			$ns = $namespace['ns'];
			$columnName = $namespace['value'];
			$isGeomColumn = ($this->geomFeaturetypeElement == null || $columnName == $this->geomFeaturetypeElement);
			
			// check if this node is a geometry node.
			// however, even if it is a property node, 
			// it has a child node, the text node!
			// So we might need to do something more 
			// sophisticated here...
			if ($currentSibling->hasChildNodes() && $isGeomColumn){
				$geomNode = $currentSibling->firstChild; 
					$geomType = $geomNode->nodeName;
					switch ($geomType) {
						case "gml:Polygon" :
							$this->geometry = new GMLPolygon();
							$this->geometry->parsePolygon($geomNode);
							break;
						case "gml:LineString" :
							$this->geometry = new GMLLine();
							$this->geometry->parseLine($geomNode);
							break;
						case "gml:Point" :
							$this->geometry = new GMLPoint();
							$this->geometry->parsePoint($geomNode);
							break;
						case "gml:MultiLineString" :
							$this->geometry = new GMLMultiLine();
							$this->geometry->parseMultiLine($geomNode);
							break;
						case "gml:MultiPolygon" :
							$this->geometry = new GMLMultiPolygon();
							$this->geometry->parseMultiPolygon($geomNode);
							break;
						case "gml:Envelope" :
							$this->geometry = new GMLEnvelope();
							$this->geometry->parseEnvelope($geomNode);
							break;
						default:
							$this->properties[$columnName] = $value;
							break;
					}
			} 
			else {
					$this->properties[$columnName] = $value;
			}
			
			$currentSibling = $currentSibling->nextSibling;
		}
	}
	
	public function toGeoJSON () {
		$str = "";
		$str .= "{\"type\":\"Feature\", \"id\":\"".$this->fid."\", \"geometry\": ";
		if ($this->geometry) {
			$str .= $this->geometry->toGeoJSON();
		}
		else {
			$str .= "\"\"";
		}

		
		$prop = array();
		
		$str .= ", \"properties\": ";
		$cnt = 0;
		foreach ($this->properties as $key => $value) {
				$prop[$key] = $value;
				$cnt ++;
		}

		$json = new Mapbender_JSON();
		$str .= $json->encode($prop); 
		$str .= "}";
		
		return $str;
	}
}

class GMLLine {

	var $pointArray = array();

	public function __construct() {
		
	}

	public function parseLine ($domNode) {
		$currentSibling = $domNode->firstChild;
		while ($currentSibling) {
			
			foreach(explode(' ',trim($currentSibling->nodeValue)) as $cords){
				list($x,$y,$z) = explode(',',$cords);
				$this->addPoint($x, $y);
			}
			$currentSibling = $currentSibling->nextSibling;
		}
	}
	
	protected function addPoint ($x, $y) {
		array_push($this->pointArray, array("x" => $x, "y" => $y));
	}
	
	public function toGeoJSON () {
		$numberOfPoints = count($this->pointArray);
		$str = "";
		if ($numberOfPoints > 0) {
			$str .= "{\"type\": \"LineString\", \"coordinates\":[";
			for ($i=0; $i < $numberOfPoints; $i++) {
				if ($i > 0) {
					$str .= ",";
				}
				$str .= "[".$this->pointArray[$i]["x"].",".$this->pointArray[$i]["y"]."]";
			}
			$str .= "]}";
		}
		else {
			$e = new mb_exception("GMLLine: toGeoJSON: this point is null.");
		}
		return $str;
	}
}

class GMLPoint {

	var $point;

	public function __construct() {
		
	}

	public function parsePoint ($domNode) {
		$currentSibling = $domNode->firstChild;
		while ($currentSibling) {
			list($x, $y, $z) = explode(",", $currentSibling->nodeValue);
			$this->setPoint($x, $y);
			$currentSibling = $currentSibling->nextSibling;
		}
	}
	
	protected function setPoint ($x, $y) {
#		echo "x: " . $x . " y: " . $y . "\n";
		$this->point = array("x" => $x, "y" => $y);
	}
	
	public function toGeoJSON () {
		$str = "";
		if ($this->point) {
			$str .= "{\"type\": \"Point\", \"coordinates\":";
			$str .= "[".$this->point["x"].",".$this->point["y"]."]";
			$str .= "}";
		}
		else {
			$e = new mb_exception("GMLPoint: toGeoJSON: this point is null.");
		}
		return $str;
	}
}

class GMLPolygon {

	var $pointArray = array();
	var $innerRingArray = array();

	public function __construct() {
		
	}

	public function parsePolygon ($domNode) {
		$simpleXMLNode = simplexml_import_dom($domNode);

		$simpleXMLNode->registerXPathNamespace('gml', 'http://www.opengis.net/gml');
		
		$allCoords = $simpleXMLNode->xpath("gml:outerBoundaryIs/gml:LinearRing/gml:coordinates");
			
		$cnt=0;
		foreach ($allCoords as $Coords) {
			$coordsDom = dom_import_simplexml($Coords);
				
//			$name = $coordsDom->nodeName;
//			$value = $coordsDom->nodeValue;				
//			echo "===> name: ".$name. ", Value: ".$value."<br>";
			
			foreach(explode(' ',trim($coordsDom->nodeValue)) as $pointCoords){

				list($x,$y,$z) = explode(',',$pointCoords);
				$this->addPoint($x, $y);
				}
			
			$cnt++;
		}
		
		$innerRingNodeArray = $simpleXMLNode->xpath("gml:innerBoundaryIs/gml:LinearRing");
		if ($innerRingNodeArray) {
			$ringCount = 0;
			foreach ($innerRingNodeArray as $ringNode) {
				$coordinates = $ringNode->xpath("gml:coordinates");
				foreach ($coordinates as $coordinate) {
					$coordsDom = dom_import_simplexml($coordinate);
						
					foreach(explode(' ',trim($coordsDom->nodeValue)) as $pointCoords){
		
						list($x,$y,$z) = explode(',',$pointCoords);
						$this->addPointToRing($ringCount, $x, $y);
					}
				}
				$ringCount++;
			}
		}
	}
	
	protected function addPoint ($x, $y) {
		array_push($this->pointArray, array("x" => $x, "y" => $y));
	}
	
	protected function addPointToRing ($i, $x, $y) {
		if (count($this->innerRingArray) <= $i) {
			array_push($this->innerRingArray, array());
		}
		$index = count($this->innerRingArray);
		$currentIndex = ($i < $index ? $i : $index);
		array_push($this->innerRingArray[$currentIndex], array("x" => $x, "y" => $y));
	}
	
	public function toGeoJSON () {
		$numberOfPoints = count($this->pointArray);
		$str = "";
		if ($numberOfPoints > 0) {
			$str .= "{\"type\": \"Polygon\", \"coordinates\":[[";
			for ($i=0; $i < $numberOfPoints; $i++) {
				if ($i > 0) {
					$str .= ",";
				}
				$str .= "[".$this->pointArray[$i]["x"].",".$this->pointArray[$i]["y"]."]";
			}
			$str .= "]";
			
			for ($i=0; $i < count($this->innerRingArray); $i++) {
				$str .= ",[";
				for ($j=0; $j < count($this->innerRingArray[$i]); $j++) {
					if ($j > 0) {
						$str .= ",";
					}
					$str .= "[".$this->innerRingArray[$i][$j]["x"].",".$this->innerRingArray[$i][$j]["y"]."]";
				}
				$str .= "]";
			}
			$str .= "]}";
		}
		else {
			$e = new mb_exception("GMLPolygon: toGeoJSON: this point is null.");
		}
		return $str;
	}
}

class GMLEnvelope extends GMLPolygon{
/*      <gml:Envelope>
         <gml:lowerCorner>42.943 -71.032</gml:lowerCorner>
         <gml:upperCorner>43.039 -69.856</gml:upperCorner>
      </gml:Envelope>
*/
	public function parseEnvelope ($domNode) {
		$corner1 = $domNode->firstChild;
		$corner2 = $corner1->nextSibling;
		
		list($y1,$x1) = explode(' ',$corner1->nodeValue);
		list($y2,$x2) = explode(' ',$corner2->nodeValue);

		$this->addPoint($x1, $y1);
		$this->addPoint($x1, $y2);
		$this->addPoint($x2, $y2);
		$this->addPoint($x2, $y1);
		$this->addPoint($x1, $y1);
	}
}



class GMLMultiLine {

	var $lineArray = array();

	public function __construct() {
		
	}

	public function parseMultiLine ($domNode) {
		$simpleXMLNode = simplexml_import_dom($domNode);

		$simpleXMLNode->registerXPathNamespace('gml', 'http://www.opengis.net/gml');
		
		$allCoords = $simpleXMLNode->xpath("gml:lineStringMember/gml:LineString/gml:coordinates");
			
		$cnt=0;
		foreach ($allCoords as $Coords) {
			
			$this->lineArray[$cnt] = array();
			
			$coordsDom = dom_import_simplexml($Coords);
				
//			$name = $coordsDom->nodeName;
//			$value = $coordsDom->nodeValue;				
//			echo "===> name: ".$name. ", Value: ".$value."<br>";
			
			foreach(explode(' ',$coordsDom->nodeValue) as $pointCoords){
				list($x,$y,$z) = explode(',',$pointCoords);
				$this->addPoint($x, $y, $cnt);
				}
			
			$cnt++;
		}
		
	}
	
	protected function addPoint ($x, $y, $i) {
		array_push($this->lineArray[$i], array("x" => $x, "y" => $y));
	}
	
	public function toGeoJSON () {
		$numberlineArray = count($this->lineArray);
		$str = "";
		if ($numberlineArray > 0) {
			$str .= "{\"type\": \"MultiLineString\", \"coordinates\":[";
			
			for ($cnt =0; $cnt < $numberlineArray; $cnt++){
				if ($cnt > 0) {
						$str .= ",";
					}
					$str .="[";
			
				for ($i=0; $i < count($this->lineArray[$cnt]); $i++) {
					if ($i > 0) {
						$str .= ",";
					}
					$str .= "[".$this->lineArray[$cnt][$i]["x"].",".$this->lineArray[$cnt][$i]["y"]."]";
				}
				$str .="]";
			}
			$str .= "]}";
			
		}
		else {
			$e = new mb_exception("GMLMultiLine: toGeoJSON: this multiLine is null.");
		}
		return $str;
	}
}

class GMLMultiPolygon {

	var $polygonArray = array();
	var $innerRingArray = array();

	public function __construct() {
		
	}

	protected function addPointToRing ($i, $j, $x, $y) {
		if (count($this->innerRingArray[$i]) <= $j) {
			array_push($this->innerRingArray[$i], array());
		}
		array_push($this->innerRingArray[$i][$j], array("x" => $x, "y" => $y));
	}
	

	public function parseMultiPolygon ($domNode) {
//		echo $domNode->nodeName."<br>";
		$simpleXMLNode = simplexml_import_dom($domNode);

		$simpleXMLNode->registerXPathNamespace('gml', 'http://www.opengis.net/gml');

		$allPolygons = $simpleXMLNode->xpath("gml:polygonMember/gml:Polygon");
		
		$cnt=0;
		foreach ($allPolygons as $polygon) {
			$allCoords = $polygon->xpath("gml:outerBoundaryIs/gml:LinearRing/gml:coordinates");
				
			$this->polygonArray[$cnt] = array();
			foreach ($allCoords as $Coords) {
				
				$coordsDom = dom_import_simplexml($Coords);
					
				foreach (explode(' ',$coordsDom->nodeValue) as $pointCoords) {
					list($x,$y,$z) = explode(',',$pointCoords);
					$this->addPoint($x, $y, $cnt);
				}
			}
			
			$this->innerRingArray[$cnt] = array();
			$innerRingNodeArray = $polygon->xpath("gml:innerBoundaryIs");
			if ($innerRingNodeArray) {
				$ringCount = 0;
				foreach ($innerRingNodeArray as $ringNode) {
					$currentRingNode = $ringNode->xpath("gml:LinearRing");
					foreach ($currentRingNode as $node) {
						$coordinates = $node->xpath("gml:coordinates");
						foreach ($coordinates as $coordinate) {
							$coordsDom = dom_import_simplexml($coordinate);
								
							foreach(explode(' ',$coordsDom->nodeValue) as $pointCoords){
				
								list($x,$y,$z) = explode(',',$pointCoords);
								$this->addPointToRing($cnt, $ringCount, $x, $y);
							}
						}
						$ringCount++;
						
					}
				}
			}
			$cnt++;
			new mb_notice("create multipolygon " . serialize($this->innerRingArray));
		}		
	}
	
	protected function addPoint ($x, $y, $i) {

		array_push($this->polygonArray[$i], array("x" => $x, "y" => $y));
	}
	
	public function toGeoJSON () {
		$numberPolygonArray = count($this->polygonArray);
		$str = "";
		if ($numberPolygonArray > 0) {
			$str .= "{\"type\": \"MultiPolygon\", \"coordinates\":[";
			
			for ($cnt =0; $cnt < $numberPolygonArray; $cnt++){
				if ($cnt > 0) {
					$str .= ",";
				}
				$str .= "[";

				$str .= "[";
				for ($i=0; $i < count($this->polygonArray[$cnt]); $i++) {
					if ($i > 0) {
						$str .= ",";
					}
					$str .= "[".$this->polygonArray[$cnt][$i]["x"].",".$this->polygonArray[$cnt][$i]["y"]."]";
				}
				$str .= "]";
				
				for ($i=0; $i < count($this->innerRingArray[$cnt]); $i++) {
					$str .= ",[";
					for ($j=0; $j < count($this->innerRingArray[$cnt][$i]); $j++) {
						if ($j > 0) {
							$str .= ",";
						}
						$str .= "[".$this->innerRingArray[$cnt][$i][$j]["x"].",".$this->innerRingArray[$cnt][$i][$j]["y"]."]";
					}
					$str .= "]";
				}
				$str .= "]";
			}
			$str .= "]}";
			
		}
		else {
			$e = new mb_exception("GMLMultiPolygon: toGeoJSON: this multiLine is null.");
		}
		return $str;
	}
}
?>
