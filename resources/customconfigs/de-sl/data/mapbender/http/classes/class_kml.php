<?php
# $Id: class_kml.php 2739 2008-08-05 11:54:58Z christoph $
# http://www.mapbender.org/index.php/class_wmc.php
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

class kml {

		var $kml_id;
		var $lookAt_range = 5000;
		var $lookAt_heading = 0;
		var $lookAt_tilt = 0;
		var $description;
		var $title;
		var $icon;
		var $x;
		var $y;
		var $kml;
					
	function kml($title, $description, $x, $y, $icon) {
  		$this->kml_id = md5(microtime());
  		$this->x = $x;
  		$this->y = $y;
  		$this->icon = $icon;
  		$this->title = $title;
  		$this->description = $description;
	} 

	function setLookAt($range, $heading) {
		$this->lookAt_range = $range;
		$this->lookAt_heading = $heading;
	}
	
	function createObjFromKML($kml_doc) {
		$section = null;
		$values = null;
		$tags = null;

		$data = $kml_doc;
		
		if(!$data){
			echo "document empty.";
			return false;
		}
		
		$this->kml = $data;

		$parser = xml_parser_create(CHARSET);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1);
		xml_parser_set_option($parser,XML_OPTION_TARGET_ENCODING,CHARSET);
		xml_parse_into_struct($parser,$data,$values,$tags);
		xml_parser_free($parser);
		
		$cnt_format = 0;
		$parent = array();
		$myParent = array();
		$cnt_layer = -1;
		$layer_style = array();
		$cnt_styles = -1;
		
		foreach ($values as $element) {
			if(mb_strtoupper($element['tag']) == "KML" && $element['type'] == "open"){
				$section = "kml";
			}
			if ($section == "kml" && mb_strtoupper($element['tag']) == "PLACEMARK" && $element['type'] == "open") {
				$section = "placemark";
			}
			if ($section == "placemark" && mb_strtoupper($element['tag']) == "DESCRIPTION" && $element['type'] == "complete") {
				$this->description = $element['value'];
			}
			if ($section == "placemark" && mb_strtoupper($element['tag']) == "NAME" && $element['type'] == "complete") {
				$this->title = $element['value'];
			}
			if ($section == "placemark" && mb_strtoupper($element['tag']) == "LOOKAT" && $element['type'] == "open") {
				$section = "lookat";
			}
			if ($section == "lookat") {
				
				if (mb_strtoupper($element['tag']) == "RANGE" && $element['type'] == "complete") {
					$this->lookAt_range = $element['value']; 
				}
				if (mb_strtoupper($element['tag']) == "HEADING" && $element['type'] == "complete") {
					$this->lookAt_heading = $element['value']; 
				}
				if (mb_strtoupper($element['tag']) == "TILT" && $element['type'] == "complete") {
					$this->lookAt_tilt = $element['value']; 
				}
			}
			if (mb_strtoupper($element['tag']) == "STYLE" && $element['type'] == "open") {
				$section = "style";
			}
			if ($section == "style" && mb_strtoupper($element['tag']) == "ICONSTYLE" && $element['type'] == "open") {
				$section = "iconstyle";
			}
			if ($section == "iconstyle" && mb_strtoupper($element['tag']) == "ICON" && $element['type'] == "open") {
				$section = "icon";
			}
			if ($section == "icon" && mb_strtoupper($element['tag']) == "HREF" && $element['type'] == "complete") {
				$this->icon = $element['value'];
			}
			if (mb_strtoupper($element['tag']) == "POINT" && $element['type'] == "open") {
				$section = "point";
			}
			if ($section == "point" && mb_strtoupper($element['tag']) == "COORDINATES" && $element['type'] == "complete") {
				$array = explode(",", $element['value']);
				$this->x = $array[0];
				$this->y = $array[1];
			}
		}
		return true;
	}

	function createObjFromDB($kml_id) {
		$this->kml_id = $kml_id;

		$sql = "SELECT kml FROM mb_meetingpoint WHERE mb_meetingpoint_id = $1";
		$v = array($kml_id);
		$t = array('s');
		$res = db_prep_query($sql, $v, $t);
		$row = db_fetch_array($res);
		return $this->createObjFromKML($row['kml']);
	}

	function createKMLFromObj(){
		$kml = "";
		$kml .= "<?xml version=\"1.0\" encoding=\"".CHARSET."\"?>\n";
		$kml .= "<kml xmlns=\"http://earth.google.com/kml/2.0\">\n";
		$kml .= "<Placemark>\n";
		$kml .= "\t<description>" . $this->description . "</description>\n";
		$kml .= "\t<name>" . $this->title . "</name>\n";
		$kml .= "\t<LookAt>\n";
		$kml .= "\t\t<longitude>" . $this->x . "</longitude>\n";
		$kml .= "\t\t<latitude>" . $this->y . "</latitude>\n";
		$kml .= "\t\t<range>" . $this->lookAt_range . "</range>\n";
		$kml .= "\t\t<tilt>" . $this->lookAt_tilt . "</tilt>\n";
		$kml .= "\t\t<heading>" . $this->lookAt_heading . "</heading>\n";
		$kml .= "\t</LookAt>\n";
		$kml .= "\t<visibility>0</visibility>\n";
		$kml .= "\t<Style>\n";
		$kml .= "\t\t<IconStyle>\n";
		$kml .= "\t\t\t<Icon>\n";
		$kml .= "\t\t\t\t<href>" . $this->icon . "</href>\n";
		$kml .= "\t\t\t</Icon>\n";
		$kml .= "\t\t</IconStyle>\n";
		$kml .= "\t</Style>\n";
		$kml .= "\t<Point>\n";
		$kml .= "\t\t<extrude>1</extrude>\n";
		$kml .= "\t\t<coordinates>" . $this->x . "," . $this->y . "</coordinates>\n";
		$kml .= "\t</Point>\n";
		$kml .= "</Placemark>\n";
		$kml .= "</kml>";
		
		$this->kml = $kml;
		
		return $kml;
	}

} 
// end class
?>
