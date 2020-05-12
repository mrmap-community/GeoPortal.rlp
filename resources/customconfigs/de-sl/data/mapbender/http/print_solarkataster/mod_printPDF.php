<?php
# $Id: mod_printPDF.php
# http://www.mapbender.org/index.php/mod_printPDF.php
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

$confFile = basename($_REQUEST["conf"]);
if (!preg_match("/^[a-zA-Z0-9_-]+(\.[a-zA-Z0-9]+)$/", $confFile) || 
	!file_exists($confFile)) {

	$errorMessage = _mb("Invalid configuration file");
	echo htmlentities($errorMessage, ENT_QUOTES, CHARSET);
	$e = new mb_exception($errorMessage);
	die;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta http-equiv="cache-control" content="no-cache" />
	<meta http-equiv="pragma" content="no-cache" />
	<meta http-equiv="expires" content="0" />
	<?php printf("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=%s\" />",CHARSET);	?>
	<title>Print PDF</title>

	<?php
	//FIXME:
	//setlocale(LC_ALL, "de_DE.utf8");

	require_once(dirname(__FILE__)."/../print_solarkataster/" . $confFile);

	printf("
	<script type=\"text/javascript\">
		var target = '%s';
		var comment1 = '%s';
		var comment1_length = %s;
		var comment2 = '%s';
		var comment2_length = %s;
		var label_button = '%s';
		var type = '%s';
	</script>",
	$_REQUEST["target"],$label_comment1,$comment1_length,$label_comment2,$comment2_length,$label_button,$type
	);
	?>

	<script type="text/javascript">
	<!--
	var size;
	var format;
	var map_width;
	var map_height;

	var pt = window.opener.parent;

	function mod_legend_print(){
		var mod_legend_target = target;
		var ind = pt.getMapObjIndexByName(mod_legend_target);
		var layers;

		document.forms[0].layers.value = "";
		document.forms[0].wms_id.value = "";
		document.forms[0].wms_title.value = "";
		document.forms[0].legendurl.value = "";

		for(var i=0; i<pt.mb_mapObj[ind].wms.length; i++){
			layers = pt.mb_mapObj[ind].wms[i].getLayers(pt.mb_mapObj[ind]);
			if(layers != "" && layers){

				if(i>0 && document.forms[0].wms_id.value!=''){
				    document.forms[0].layers.value += "___";
				    document.forms[0].wms_id.value += "___";
				    document.forms[0].wms_title.value += "___";
				    document.forms[0].legendurl.value += "___";
				}

				document.forms[0].wms_id.value += pt.mb_mapObj[ind].wms[i].wms_id;
				document.forms[0].wms_title.value += pt.mb_mapObj[ind].wms[i].wms_title;

				for(var j=0; j<layers.length; j++){
					var layer = layers[j];
					var title = pt.mb_mapObj[ind].wms[i].getTitleByLayerName(layers[j]);
					var layerStyle = pt.mb_mapObj[ind].wms[i].getCurrentStyleByLayerName(layers[j]);
					if(layerStyle==false){
						var temp_legendurl = pt.mb_mapObj[ind].wms[i].getLegendUrlByGuiLayerStyle(layers[j],"default");
					}
					else{
						var temp_legendurl = pt.mb_mapObj[ind].wms[i].getLegendUrlByGuiLayerStyle(layers[j],layerStyle);
					}

					//---------- legendurl ----------
					tmp_name = "";
					if(j>0){
						document.forms[0].layers.value += ",";
					}
						document.forms[0].layers.value += title;
					if(j>0){
						document.forms[0].legendurl.value += ",";
					}
					if (temp_legendurl!= '' || !temp_legendurl == 'true'){
						document.forms[0].legendurl.value += temp_legendurl;
					}else{
						document.forms[0].legendurl.value +='0';
					}
				}
			}
			else{
				if(i>0 && document.forms[0].wms_id.value!=''){
				    document.forms[0].layers.value += "___";
				    document.forms[0].wms_id.value += "___";
				    document.forms[0].wms_title.value += "___";
				    document.forms[0].legendurl.value += "___";
			    }

				document.forms[0].layers.value += "0";
			    document.forms[0].wms_id.value += "0";
			    document.forms[0].wms_title.value += "0";
			    document.forms[0].legendurl.value += "0";
			}
		}//for
		//alert(document.forms[0].layers.value+"---"+document.forms[0].wms_id.value+"---"+document.forms[0].wms_title.value+"---"+document.forms[0].legendurl.value);

	}

	function validate(){
		size = document.getElementById('size').options[document.getElementById('size').selectedIndex].value;
		format = document.getElementById('format').options[document.getElementById('format').selectedIndex].value;
 

		if(size != "false" && format != "false"){
			var ind = pt.getMapObjIndexByName(target);
			var map_el = pt.mb_mapObj[ind].getDomElement();
			var coord = pt.mb_mapObj[ind].extent.toString().split(",");
			var centerX = parseFloat(coord[0]) + (parseFloat(coord[2]) - parseFloat(coord[0]))/2
			var centerY = parseFloat(coord[1]) + (parseFloat(coord[3]) - parseFloat(coord[1]))/2
			if(size == "A4" && format == "portrait"){
				map_width = <?php echo $a4p_map_width; ?>;
				map_height = <?php echo $a4p_map_height; ?>;
			}
			if(size == "A4" && format == "landscape"){
				map_width = <?php echo $a4l_map_width; ?>;
				map_height = <?php echo $a4l_map_height; ?>;
			}
			if(size == "A3" && format == "portrait"){
				map_width = <?php echo $a3p_map_width; ?>;
				map_height = <?php echo $a3p_map_height; ?>;
			}
			if(size == "A3" && format == "landscape"){
				map_width = <?php echo $a3l_map_width; ?>;
				map_height = <?php echo $a3l_map_height; ?>;
			}
			if(size == "A2" && format == "portrait"){
				map_width = <?php echo $a2p_map_width; ?>;
				map_height = <?php echo $a2p_map_height; ?>;
			}
			if(size == "A2" && format == "landscape"){
				map_width = <?php echo $a2l_map_width; ?>;
				map_height = <?php echo $a2l_map_height; ?>;
			}
			if(size == "A1" && format == "portrait"){
				map_width = <?php echo $a1p_map_width; ?>;
				map_height = <?php echo $a1p_map_height; ?>;
			}
			if(size == "A1" && format == "landscape"){
				map_width = <?php echo $a1l_map_width; ?>;
				map_height = <?php echo $a1l_map_height; ?>;
			}
			if(size == "A0" && format == "portrait"){
				map_width = <?php echo $a0p_map_width; ?>;
				map_height = <?php echo $a0p_map_height; ?>;
			}
			if(size == "A0" && format == "landscape"){
				map_width = <?php echo $a0l_map_width; ?>;
				map_height = <?php echo $a0l_map_height; ?>;
			}
			var pos = pt.makeClickPos2RealWorldPos(target, map_width , map_height);
			var prevscale= pt.mb_getScale(target);
			//pt.mb_mapObj[ind].extent = coord[0] + "," + pos[1] + "," + pos[0] + "," +  coord[3];

                        /** makeExtent is a workaround function to get printPDF functions working if
                        * printPDF is shown in a popup
                        */
			pt.mb_mapObj[ind].extent = pt.Mapbender.makeExtent(
				parseFloat(coord[0]),
				pos[1],
				pos[0],
				parseFloat(coord[3])
			);

			pt.mb_mapObj[ind].setDimensions(Math.round(map_width), Math.round(map_height));

			//pt.setMapRequest(target);
			if (pt.mb_mapObj[ind].epsg === "EPSG:4326"){
				pt.mb_repaint(target, parseFloat(coord[0]), pos[1], pos[0], parseFloat(coord[3]));
			}
			else{
				pt.mb_repaintScale(target, null, null, prevscale);
			}

		document.form1.map_url.value = '';
			for(var i=0; i<pt.mb_mapObj[ind].wms.length; i++){
				if(pt.mb_mapObj[ind].wms[i].gui_wms_visible > 0){
					if(pt.mb_mapObj[ind].wms[i].mapURL != false && pt.mb_mapObj[ind].wms[i].mapURL != 'false' && pt.mb_mapObj[ind].wms[i].mapURL != ''){
						if(document.form1.map_url.value != ""){
							document.form1.map_url.value += "___";
						}
						document.form1.map_url.value += pt.mb_mapObj[ind].wms[i].mapURL;
					}
				}
			}

			//overview_url
			var ind_overview = pt.getMapObjIndexByName('overview');

			//alert ("l�nge: " + length+ " - " + ind_overview + name + pt.mb_mapObj[ind_overview].wms.length);
			if(pt.mb_mapObj[ind_overview].mapURL != false ){
				document.forms[0].overview_url.value = pt.mb_mapObj[ind_overview].mapURL;
			}
		}
	}
	function refreshParams(){
		var f = document.forms[0];
		size = document.getElementById('size').options[document.getElementById('size').selectedIndex].value;
		format = document.getElementById('format').options[document.getElementById('format').selectedIndex].value;

		if(size != "" && format != ""){
			var ind = pt.getMapObjIndexByName(target);
			var map_el = pt.mb_mapObj[ind].getDomElement();
			var coord = pt.mb_mapObj[ind].extent.toString().split(",");
			var centerX = parseFloat(coord[0]) + (parseFloat(coord[2]) - parseFloat(coord[0]))/2
			var centerY = parseFloat(coord[1]) + (parseFloat(coord[3]) - parseFloat(coord[1]))/2

			var pos = pt.makeClickPos2RealWorldPos(target, map_width , map_height);
			var prevscale= pt.mb_getScale(target);

                        /** makeExtent is a workaround function to get printPDF functions working if
                        * printPDF is shown in a popup
                        */
			pt.mb_mapObj[ind].extent = pt.Mapbender.makeExtent(
				parseFloat(coord[0]),
				pos[1],
				pos[0],
				parseFloat(coord[3])
			);

			pt.mb_mapObj[ind].width = Math.round(map_width);
			pt.mb_mapObj[ind].height = Math.round(map_height);
			map_el.style.width = Math.round(map_width);
			map_el.style.height = Math.round(map_height);
			//pt.setMapRequest(target);		
                        if (pt.mb_mapObj[ind].epsg === "EPSG:4326"){
				pt.mb_repaint(target, parseFloat(coord[0]), pos[1], pos[0], parseFloat(coord[3]));
			}
			else{
				pt.mb_repaintScale(target, null, null, prevscale);
			}

			f.map_url.value = '';
			for(var i=0; i<pt.mb_mapObj[ind].wms.length; i++){
				if(pt.mb_mapObj[ind].wms[i].gui_wms_visible > 0){
					if(pt.mb_mapObj[ind].wms[i].mapURL != false && pt.mb_mapObj[ind].wms[i].mapURL != 'false' && pt.mb_mapObj[ind].wms[i].mapURL != ''){
						if(f.map_url.value != ""){
							f.map_url.value += "___";
						}
						f.map_url.value += pt.mb_mapObj[ind].wms[i].mapURL;
					}
				}
			}

			//overview_url
			var ind_overview = pt.getMapObjIndexByName('overview');

			//alert ("l�nge: " + length+ " - " + ind_overview + name + pt.mb_mapObj[ind_overview].wms.length);
			if(pt.mb_mapObj[ind_overview].mapURL != false ){
				f.overview_url.value = pt.mb_mapObj[ind_overview].mapURL;
			}
		}

		f.map_scale.value = pt.mb_getScale(target);
		f.epsg.value = pt.mb_mapObj[ind].epsg;

		// mypermanentImage (permanent highlight from geometry.js)
		if(pt.$(".mapSymbol").length > 0){
			var permanentImage_x = parseInt(pt.$(".mapSymbol").position().left);
			var permanentImage_y = parseInt(pt.$(".mapSymbol").position().top);
			var objImage = pt.$(".mapSymbol");
			var permanentHighlightImage = objImage.attr("src");
			var permanentHighlightImage = objImage.attr("src");
			var permanentImage_width = objImage.attr("width");
			var permanentImage_height = objImage.attr("height"); 
			if(permanentImage_x && permanentImage_y){
				var x_pos = permanentImage_x;
				var y_pos = permanentImage_y;
				document.forms[0].mypermanentImage.value = permanentHighlightImage + '___' +x_pos+'___'+y_pos+'___'+permanentImage_width+'___'+permanentImage_height;
			}
		}
		
		mod_legend_print();
	}
	function printMap(){
		size = "A4";
		format = "portrait";
		if(size != "false" && (format == "portrait" || format == "landscape")){
			refreshParams();
			if(document.form1.c1.value != comment1){
				document.form1.comment1.value = document.form1.c1.value;
			}
			if(document.form1.c2.value != comment2){
				document.form1.comment2.value = document.form1.c2.value;
			}
			if(document.form1.mylegendcheckbox.checked == 0){
				document.form1.mylegend.value = 'false';
			}else{
				document.form1.mylegend.value = 'true';
			}

			// write the measured coordinates

			if (pt.mod_measure_RX != undefined && pt.mod_measure_RY != undefined) {
				var tmp_x = '';
				var tmp_y = '';
				for(i = 0; i<pt.mod_measure_RX.length; i++) {
					if(tmp_x != '') {
						tmp_x += ','
					}
					tmp_x += pt.mod_measure_RX[i];
				}
				for(i = 0; i<pt.mod_measure_RY.length; i++) {
					if(tmp_y != '') {
						tmp_y += ','
					}
					tmp_y += pt.mod_measure_RY[i];
				}
				document.forms['form1'].elements['measured_x_values'].value = tmp_x;
				document.forms['form1'].elements['measured_y_values'].value = tmp_y;
			}

			document.form1.submit();
		}
		else{
			alert('<?php echo _mb("No format selected")."!"?>');
		}
	}

	function checkCommentLength(obj,maxLength){
		if(obj.value.length > maxLength){
			obj.value = obj.value.substr(0,maxLength);
		}
	}


-->
</script>


	<?php include("../include/dyn_css.php"); ?>

<style type="text/css">
body { background-color:#FFFFFF}

input, select, textarea, p {
  font: normal 14px arial, verdana, tahoma, sans-serif;
  color: #FFFFFF;
  border-width:0px;
  border-style:solid;
  border-color:#FFFFFF;
  margin:10px;
  }
</style>


</head>

<body onload="validate();printMap();">


<form name="form1" method="post" action="../print_solarkataster/mod_printPDF_pdf.php?&sessionID=<?php echo SID; ?>" target="_self">
<p id="container_size">
	<label for="size"></label>
	<select style="display:none" id="size" name="size" onchange="validate();">
		<option style="display:none" value="A4">Din A4</option>
		<?php
		for($i = 4; $i >= 0; $i--) {
			if(${"a".$i}) {
				printf("<option value=\"A%s\">%s</option>",$i,${"label_format_a".$i});
			}
		}
		?>
	</select>
</p>

<p id="container_orientation">
	<label for="format"></label>
	<select style="display:none" id="format" name="format" onchange="validate();">
		<option style="display:none" value="portrait"><?php echo $label_portrait; ?></option>
	</select>
</p>

<?php if($highquality === TRUE): ?>
	<p id="container_quality">
		<label for="quality"></label>
		<input style="display:none" type="hidden" id="quality" name="quality" value="2" checked="checked" /> <?php echo $label_72dpi; ?>
		<input style="display:none" type="hidden" id="quality" name="quality" value="2" /> <?php echo $label_288dpi; ?>
	</p>
<?php endif; ?>

<?php
ini_set("error_reporting",E_ALL);
ini_set("display_errors","on");
	for($i = 1; $i <= 2; $i++) {
		$max_comment_length = ${"comment".$i."_length"};
		$label_hint         = ($max_comment_length > -1) ? sprintf(" <em>"._mb("max.")." %s)</em>",$max_comment_length) : NULL;
		$javascript         = ($max_comment_length > -1) ? sprintf(" onblur=\"checkCommentLength(this,%s)\"",$max_comment_length) : NULL;

		$html  = sprintf("<p id=\"container_comment%s\">",$i);
		$html .= sprintf("<label for=\"c%s\"></label> ",$i,${"label_comment".$i},$label_hint);
		$html .= sprintf("<textarea style=\"display:none\" id=\"c%s\" name=\"c%s\" cols=\"20\" rows=\"2\"%s></textarea> ",$i,$i,$javascript);
		$html .= "</p>";

		echo $html;
	}
?>

<?php if($legend === TRUE): ?>
	<p id="container_legend">
		<label for="mylegendcheckbox"></label>
		<input type="" id="mylegendcheckbox" name="mylegendcheckbox" value="false" />
	</p>
<?php else: ?>
	<input type="hidden" name="mylegendcheckbox" value="false" />
<?php endif; ?>



<input type="hidden" name="size" value="A4"/>
<input type="hidden" name="orientation" value="portrait"/>
<input type="hidden" name="map_url" />
<input type="hidden" name="overview_url" />
<input type="hidden" name="wms_title" />
<input type="hidden" name="wms_id" />
<input type="hidden" name="layers" />
<input type="hidden" name="legendurl" />
<input type="hidden" name="map_scale" />
<input type="hidden" name="epsg" />
<input type="hidden" name="thema" value="<?php echo $_REQUEST["thema"]; ?>" />
<input type="hidden" name="highlightx" value="<?php echo $_REQUEST["x"]; ?>" />
<input type="hidden" name="highlighty" value="<?php echo $_REQUEST["y"]; ?>" />
<input type="hidden" name="conf" value="<?php echo $_REQUEST["conf"]; ?>" />
<input type="hidden" name="eignung" value="<?php echo $_REQUEST["eignung"]; ?>" />
<input type="hidden" name="modulf" value="<?php echo $_REQUEST["modulf"]; ?>" />

<input type="hidden" name="dachtyp" value="<?php if ($_REQUEST["thema"]=="Photovoltaik") echo $_REQUEST["dachtyp"]; ?>" />
<input type="hidden" name="modulf09" value="<?php if ($_REQUEST["thema"]=="Photovoltaik") echo $_REQUEST["modulf09"]; ?>" />
<input type="hidden" name="modulf15" value="<?php if ($_REQUEST["thema"]=="Photovoltaik") echo $_REQUEST["modulf15"]; ?>" />

<input type="hidden" name="co215" value="<?php if ($_REQUEST["thema"]=="Photovoltaik") echo $_REQUEST["co215"]; ?>" />
<input type="hidden" name="co209" value="<?php if ($_REQUEST["thema"]=="Photovoltaik") echo $_REQUEST["co209"]; ?>" />
<input type="hidden" name="stro15" value="<?php if ($_REQUEST["thema"]=="Photovoltaik") echo $_REQUEST["strom15"]; ?>" />
<input type="hidden" name="stro09" value="<?php if ($_REQUEST["thema"]=="Photovoltaik") echo $_REQUEST["strom09"]; ?>" />
<input type="hidden" name="empfehl" value="<?php if ($_REQUEST["thema"]=="Photovoltaik") echo $_REQUEST["empfehl"]; ?>" />

<input type="hidden" name="usernam" value="<?php echo $_REQUEST["usernam"]; ?>" />

<input type="hidden" name="mypermanentImage" value="" />

<input type="hidden" name="comment1" />
<input type="hidden" name="comment2" />
<input type="hidden" name="mylegend" value="true" >
<input type="hidden" name="measured_x_values" />
<input type="hidden" name="measured_y_values" />

</form>

<div style="position:absolute;top:0px;left:0px;right:125px;bottom:500px;z-Index:15;background-color:#FFFFFF" width=100% height=100%><p style="color:#000000">PDF wird erstellt. <img src="../img/indicator_wheel.gif"></p></div>

</body>

</html>
