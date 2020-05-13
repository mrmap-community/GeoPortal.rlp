/**
 * Package: scalebar
 *
 * Description:
 * Displays scalebar in the map. The position currently is hard coded to the 
 * lower left corner of the map frame. 
 * 
 * Files:
 *  - http/javascripts/mod_scalebar.php
 *
 * SQL:
 * > INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, 
 * > e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, 
 * > e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, 
 * > e_mb_mod, e_target, e_requires, e_url) VALUES ('<gui_id>','scalebar',
 * > 2,1,'scalebar','Scalebar','div','','',0,0,0,0,0,'','','div',
 * > 'mod_scalebar.php','','mapframe1','','');
 *
 * Help:
 * http://www.mapbender.org/Scalebar
 *
 * Maintainer:
 * http://www.mapbender.org/User:Christoph_Baudson
 * 
 * License:
 * Copyright (c) 2009, Open Source Geospatial Foundation
 * This program is dual licensed under the GNU General Public License 
 * and Simplified BSD license.  
 * http://svn.osgeo.org/mapbender/trunk/mapbender/license/license.txt
 */

var mod_scalebar_target = options.target;

var mod_scalebar_left = 5;
var mod_scalebar_bottom = 17;

var mod_scalebar_color1 = "white";
var mod_scalebar_color2 = "black";
var mod_scalebar_font = "Arial, Helvetica, sans-serif";
var mod_scalebar_fontsize = "9px";

eventAfterMapRequest.register(function () {
	mod_scalebar();
});

var mod_scalebar = function () {
	var scale = Mapbender.modules[mod_scalebar_target].getScale();
	var ind = getMapObjIndexByName(mod_scalebar_target);
	if(scale < 10){
		var unit = '10&nbsp;cm';
		var factor = 10/scale;
		var img_width = Math.round(factor * mb_resolution);
	}
	if(scale >= 10 && scale < 100){
		var unit = '1&nbsp;m';
		var factor = 100/scale;
		var img_width = Math.round(factor * mb_resolution);
	}
	if(scale < 1000 && scale >= 100){
		var unit = '10&nbsp;m';
		var factor = 1000/scale;
		var img_width = Math.round(factor * mb_resolution);
	}
	if(scale < 10000 && scale >= 1000){
		var unit = '100&nbsp;m';
		var factor = 10000/scale;
		var img_width = Math.round(factor * mb_resolution);
	}
	if(scale < 100000 && scale >= 10000){
		var unit = '1&nbsp;km';
		var factor = 100000/scale;
		var img_width = Math.round(factor * mb_resolution);
	}
	if(scale < 1000000 && scale >= 100000){
		var unit = '10&nbsp;km';
		var factor = 1000000/scale;
	var img_width = Math.round(factor * mb_resolution);
	}
	if(scale < 10000000 && scale >= 1000000){
		var unit = '100&nbsp;km';
		var factor = 10000000/scale;
		var img_width = Math.round(factor * mb_resolution);
	}
	if(scale < 100000000 && scale >= 10000000){
		var unit = '1000&nbsp;km';
		var factor = 100000000/scale;
		var img_width = Math.round(factor * mb_resolution);
	}
	if(scale >= 100000000){
		var unit = '1000&nbsp;km';
		var factor = 100000000/scale;
		var img_width = Math.round(factor * mb_resolution);
	}
	var scalebarTag = "<img src='../img/scalebar_bw.gif' width='"+ img_width  +"' height='6'>&nbsp; ";/*

	scalebarTag += "<div style='position:absolute;left:"+(img_width + 4)+"px;top:5px;color:"+mod_scalebar_color1+";font-family:"+mod_scalebar_font+";font-size:"+mod_scalebar_fontsize+";'><nobr>"+ unit+"</nobr></div>";
	scalebarTag += "<div style='position:absolute;left:"+(img_width + 2)+"px;top:7px;color:"+mod_scalebar_color1+";font-family:"+mod_scalebar_font+";font-size:"+mod_scalebar_fontsize+";'><nobr>"+ unit+"</nobr></div>";
	scalebarTag += "<div style='position:absolute;left:"+(img_width + 3)+"px;top:6px;color:"+mod_scalebar_color2+";font-family:"+mod_scalebar_font+";font-size:"+mod_scalebar_fontsize+";'>"+ unit+"</div>";
*/
	scalebarTag += "<div style='position:absolute;left:"+(img_width + 2)+"px;top:3px;color:"+mod_scalebar_color1+";font-family:"+mod_scalebar_font+";font-size:"+mod_scalebar_fontsize+";'><nobr>"+ unit+"</nobr></div>";
	

	var map_el = mb_mapObj[ind].getDomElement();
	if(!map_el.ownerDocument.getElementById(mb_mapObj[ind].elementName+"_scalebar")){
		//create Box Elements
		el_top = map_el.ownerDocument.createElement("div");
		el_top.style.position = "absolute";
		el_top.style.top = "0px";
		el_top.style.left = "0px";
		el_top.style.width = "100%";
		el_top.style.overflow = "hidden";
		el_top.style.zIndex = "100";
		el_top.style.paddingBottom = "40px";
		el_top.id = mb_mapObj[ind].elementName+"_scalebar";
		map_el.appendChild(el_top);
	}
	mb_arrangeElement("", mod_scalebar_target+"_scalebar", mod_scalebar_left, (mb_mapObj[ind].height - mod_scalebar_bottom));
	writeTag(mb_mapObj[ind].frameName, mb_mapObj[ind].elementName+"_scalebar", scalebarTag);

}