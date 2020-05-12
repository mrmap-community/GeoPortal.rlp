<?php
# License:
# Copyright (c) 2009, Open Source Geospatial Foundation
# This program is dual licensed under the GNU General Public License 
# and Simplified BSD license.  
# http://svn.osgeo.org/mapbender/trunk/mapbender/license/license.txt

require_once dirname(__FILE__) . "/../../core/globalSettings.php";
require_once dirname(__FILE__) . "/../classes/class_elementVar.php";

define("ELEMENT_PATTERN", "/sessionID/");

class Element {
	
	var $guiId;
	var $id;
	var $pos;
	var $isPublic;
	var $comment;
	var $title;
	var $element;
	var $src;
	var $attributes;
	var $left;
	var $top;
	var $width;
	var $height;
	var $zIndex;
	var $moreStyles;
	var $content;
	var $closeTag;
	var $jsFile;
	var $mbMod;
	var $target;
	var $requires;
	var $helpUrl;
	var $isBodyAndUsesSplashScreen = false;
	var $elementVars = array();
	
	public function __contruct() {
		
	}
	
	public function select ($id, $applicationId) {
		$sql = "SELECT fkey_gui_id, e_id, e_pos, e_public, e_comment, e_public, ".
				"gettext($1, e_title) as e_title, e_element, e_src, e_attributes, " .
				"e_left, e_top, e_width, e_height, e_z_index, e_more_styles, " .
				"e_content, e_closetag, e_js_file, e_mb_mod, e_target, " .
				"e_requires, e_url FROM gui_element WHERE e_id = $2 AND " .
				"fkey_gui_id = $3 LIMIT 1";
		$v = array (Mapbender::session()->get("mb_lang"), $id, $applicationId);
		$t = array ("s", "s", "s");
		$res = db_prep_query($sql, $v, $t);
		$row = db_fetch_array($res);
		if ($row) {
			$this->guiId = $applicationId;
			$this->id = $row["e_id"];
			$this->pos = $row["e_pos"];
			$this->isPublic = $row["e_public"];
			$this->comment = $row["e_comment"];
			$this->title = $row["e_title"];
			$this->element = $row["e_element"];
			$this->src = $row["e_src"];
			$this->attributes = $row["e_attributes"];
			$this->left = $row["e_left"];
			$this->top = $row["e_top"];
			$this->width = $row["e_width"];
			$this->height = $row["e_height"];
			$this->zIndex = $row["e_z_index"];
			$this->moreStyles = $row["e_more_styles"];
			$this->content = $row["e_content"];
			$this->closeTag = $row["e_closetag"];
			$this->jsFile = $row["e_js_file"];
			$this->mbMod = $row["e_mb_mod"];
			$this->target = $row["e_target"];
			$this->requires = $row["e_requires"];
			$this->helpUrl = $row["e_url"];

			$sql = "SELECT var_name FROM gui_element_vars WHERE fkey_gui_id = $1 AND fkey_e_id = $2;";
			$v = array($applicationId, $id);
			$t = array("s", "s");
			$res = db_prep_query($sql, $v, $t);

			while ($row = db_fetch_assoc($res)) {
				$name = $row["var_name"];
				$this->elementVars[]= new ElementVar($applicationId, $id, $name);
			}
			return true;
		}
		return false;		
	}
	
	public function __toString () {
		return $this->toHtml();
	}
	
	public function toSql () {
		$insert = "INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,";
		$insert .= "e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles,";
		$insert .= " e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES (";
		$insert.="'".$this->guiId."',";
		$insert.="'".$this->id."',";
		$insert.="".$this->pos.",";
		$insert.="".$this->isPublic.",";
		$insert.="'".db_escape_string($this->comment)."',";
		$insert.="'".db_escape_string($this->title)."',";
		$insert.="'".$this->element."',";
		$insert.="'".$this->src."',";
		$insert.="'".db_escape_string($this->attributes)."',";
		$insert.="".$this->left.",";
		$insert.="".$this->top.",";
		$insert.="".$this->width.",";
		$insert.="".$this->height.",";
		$insert.="".$this->zIndex.",";		
		$insert.="'".$this->moreStyles."',";
		$insert.="'".db_escape_string($this->content)."',";
		$insert.="'".$this->closeTag."',";
		$insert.="'".$this->jsFile."',";		
		$insert.="'".$this->mbMod."',";
		$insert.="'".$this->target."',";
		$insert.="'".$this->requires."',";
		$insert.="'".$this->helpUrl."'";
		$insert.= ");\n";
		
		for ($i = 0; $i < count($this->elementVars); $i++) {
			$insert .= $this->elementVars[$i]->toSql();
		}
		return preg_replace("/,,/", ",NULL ,", $insert);		
	}
	
	public function getJavaScriptModules () {
		$jsArray = array();
		if ($this->mbMod != "") {
			$moduleArray = explode(",", $this->mbMod);
			for ($i = 0; $i < count($moduleArray); $i++) {
				$currentFile = dirname(__FILE__) . "/../javascripts/" . trim($moduleArray[$i]);
				if (file_exists($currentFile)) {
					array_push($jsArray, $currentFile);
				}
				else {
					$e = new mb_exception("Javascript module not found: " . $currentFile);
				}
			}
		}
		return $jsArray;
	}
	
	public function toHtmlArray () {
		if ($this->isPublic) {
			return array($this->getHtmlOpenTag(), $this->getHtmlContent(), $this->getHtmlCloseTag());	
		}
		return array("", "", "");
	}
	
	public function toHtml () {
		if ($this->isPublic) {
			return implode("", $this->toHtmlArray());
		}
		return "";
	}

	private function getHtmlOpenTag () {
		$openTag = "";
		
		if (!$this->element) {
			return "";
		}
	
		if ($this->id) {
			// tag name
			$openTag .= "<" . $this->element . " ";
			
			// id and name
			$openTag .= "id='" . $this->id . "' ";
			
			$validTags = array(
				"form",
				"iframe",
				"img",
				"applet",
				"button",
				"frame",
				"input",
				"map",
				"object",
				"param",
				"select",
				"textarea"
			);
			if (in_array($this->element, $validTags)) {
				$openTag .= "name='" . htmlentities($this->id, ENT_QUOTES, CHARSET) . "' ";
			}
			
			// attributes
			if ($this->attributes) {
				$openTag .= stripslashes($this->replaceSessionStringByUrlParameters($this->attributes)) . " ";
			}
			
			if ($this->element === "img" && !preg_match("/alt( )*=/", $this->attributes)) {
				$openTag .= "alt='" . htmlentities($this->title, ENT_QUOTES, CHARSET) . "' ";
			}
			
			// title
			if ($this->title) {
				$openTag .= "title='" . htmlentities($this->title, ENT_QUOTES, CHARSET) . "' ";
			}
			else {
				// add a title for iframes
				if ($this->element === "iframe") {
					$openTag .= "title='" . $this->id . "' ";
				}
			}
			
			// src
			if ($this->src) {
   				$openTag .= "src = '" . $this->replaceSessionStringByUrlParameters(
					htmlentities($this->src, ENT_QUOTES, CHARSET)
				);

				// for iframes which are not "loadData", 
				// add additional parameters
				if ($this->closeTag == "iframe" && $this->id != "loadData") {
					if(mb_strpos($this->src, "?")) {
						$openTag .= "&amp;";
					}
					else {
	      				$openTag .= "?";
      				}
	      			$openTag .= "e_id_css=" . $this->id . "&amp;" .
	      					 "e_id=" . $this->id . "&amp;" .
	      					 "e_target=" . $this->target . "&amp;" .
	      					 $this->getUrlParameters();
				}
   				$openTag .= "' ";
			}
			
			// style
			$openTag .= " style = '";
			if ($this->top != "" && $this->left != "") {
				$openTag .= "position:absolute;" .
						 "left:" . $this->left . "px;" .
						 "top:" . $this->top . "px;";
			}
			if ($this->width !== "") {
				$openTag .= "width:" . $this->width . "px;";
			} 
			if ($this->height !== "") {
				$openTag .= "height:" . $this->height . "px;";
			}
			if ($this->zIndex) {
		    	$openTag .= "z-index:" . $this->zIndex . ";";
			}
			if ($this->moreStyles) {
		    	$openTag .= $this->moreStyles;
			}
			$openTag .= "'";
			if (($this->element !== "body") && ($this->id !== "body")) {
				$openTag .= " class='hide-during-splash'";
			}
			$openTag .= ">";

			if ($this->element == "body") {
				$e_id = "body";
				$gui_id = $this->guiId;
				include(dirname(__FILE__)."/../include/dyn_php.php");
				
				$splashScreen = "";
				if (isset($use_load_message) AND $use_load_message != 'false') {
					$this->isBodyAndUsesSplashScreen = true;
					if (isset($htmlWhileLoading) && $htmlWhileLoading != '') {
						$splashScreen .= $htmlWhileLoading; 
					} elseif (isset($includeWhileLoading) && $includeWhileLoading != '' && file_exists(dirname(__FILE__)."/".$includeWhileLoading)) { 
						ob_start();
						include(dirname(__FILE__)."/".$includeWhileLoading);
						$splashScreen .= ob_get_contents();
						ob_end_clean();
					}
					else {
						$splashScreen .= "<img alt='indicator wheel' src='../img/indicator_wheel.gif'>&nbsp;" . 
	"<strong>Ma<span style='font-color:#0000CE'>p</span><span style='font-color:#C00000'>b</span>ender " . 
	MB_VERSION_NUMBER . " " . strtolower(MB_VERSION_APPENDIX) . "</strong>..." .
	"loading application '" . $this->guiId . "'";
					}
				}	
				$openTag .= "<div id='loading_mapbender' " .
								"style='margin:0px;padding:0px;width:100%;height:100%;'>" . 
								$splashScreen . "</div>";
				unset ($e_id, $gui_id);
			}
		}
		return $openTag;
	}
	
	private function getHtmlContent () {
		$htmlContent = "";
		if ($this->content != "" && $this->element) {
			$htmlContent .= stripslashes($this->content);
		}
		return $htmlContent;
	}
	
	private function getHtmlCloseTag () {
		if ($this->element == "body") {
			return "</body>";
		}
		if ($this->closeTag != "") {
			return "</" . $this->closeTag . ">";
		} else {
			if(in_array($this->element, array( "area", "base","br","col","hr","img","input","link","meta","param"))){
				return  "";
			}else{
				return "</". $this->element . ">";
			}
		}
		return "";
	}
	
	private function getUrlParameters () {
		$urlParameters = SID;
		if (isset($this->guiId)) {
			$urlParameters .= "&guiID=" . $this->guiId;
		}
		if (isset($this->id)) {
			$urlParameters .= "&elementID=" . $this->id;
		}
		return htmlentities($urlParameters, ENT_QUOTES, CHARSET);
	}
	
	private function replaceSessionStringByUrlParameters ($string) {
		$urlParameters = $this->getUrlParameters();
		return preg_replace(ELEMENT_PATTERN, $urlParameters, $string);
	}
	
}


?>
