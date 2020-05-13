/**
 * Package: ResizeMapsize
 *
 * Description:
 * This modul dynamically resizes the mapframe in relation to the 
 * browsersize. Thanks to Antje Jacobs and Marko Samson.
 * 
 * Files:
 *  - http/javascripts/mod_resizeMapsize.js
 *
 * SQL:
 * > INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, 
 * > e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, 
 * > e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, 
 * > e_target, e_requires, e_url) VALUES('<gui_id>','resizeMapsize',2,1,
 * > 'resize_mapsize','img','../img/button_blink_red/resizemapsize_off.png',
 * > 'onclick = "adjustDimension()" onmouseover=''this.src = this.src.replace(/_off/,"_over");'' onmouseout=''this.src = this.src.replace(/_over/, "_off");'' title="Kartenfenstergröße optimieren"',
 * > 838,40,24,24,3,'filter:Chroma(color=#C2CBCF);','','',
 * > 'mod_resize_mapsize.php','','mapframe1','','');
 * > 
 * > INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, 
 * > context, var_type) VALUES('<gui_id>', 'resizeMapsize', 'adjust_height', 
 * > '-35', 'to adjust the height of the mapframe on the bottom of the window',
 * > 'var');
 * >
 * > INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, 
 * > context, var_type) VALUES('gui1', 'resizeMapsize', 'adjust_width', '-45', 
 * > 'to adjust the width of the mapframe on the right side of the window',
 * > 'var');
 * >
 * > INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, 
 * > context, var_type) VALUES('gui1', 'resizeMapsize', 'resize_option', 
 * > 'button', 'auto (autoresize on load), button (resize by button)' ,'var');
 *
 * Help:
 * http://www.mapbender.org/ResizeMapsize
 *
 * Maintainer:
 * http://www.mapbender.org/User:Christoph_Baudson
 * 
 * Parameters:
 * adjust_height    - *[optional]* {Integer} adjust the maximum size of the 
 * 						mapframe. A positive number will enlarge the maximum 
 * 						mapframe size, an negative number will reduce the 
 * 						maximum mapframe size. Default: -90
 * adjust_width		- *[optional]* {Integer} adjust the maximum size of the 
 * 						mapframe. A positive number will enlarge the maximum 
 * 						mapframe size, an negative number will reduce the 
 * 						maximum mapframe size. Default: -45
 * resize_option	- *[optional]* {String} choose between automatic 
 * 						resizing on startup and resizing by clicking the 
 * 						button. The element var value 'auto' means automatic 
 * 						resizing, the value 'button' means resizing clicking 
 * 						the button. If element var resize_option doesn't 
 * 						exist, automatic resizing will be set as standard 
 * 						configuration. 
 *
 * License:
 * Copyright (c) 2009, Open Source Geospatial Foundation
 * This program is dual licensed under the GNU General Public License 
 * and Simplified BSD license.  
 * http://svn.osgeo.org/mapbender/trunk/mapbender/license/license.txt
 */

if (!options.resize_option) {
	options.resize_option = "auto";
}
var resize_option = options.resize_option;

if (typeof options.adjust_width == "undefined") {
	options.adjust_width = -45;
}
var adjust_width = options.adjust_width;

if (typeof options.adjust_height === "undefined") {
	options.adjust_height = -90;
}
var adjust_height = options.adjust_height;

var map;
var mapExtent;
var map_frame = options.target;
var map_frame_left;
var map_frame_top;

var legend_width = (Mapbender.modules["legend"]) ? 
	Mapbender.modules["legend"].width : 0;

var width_temp, height_temp;

function frameWidth(){
  	return $(window).width();
}

function frameHeight(){
	if($("#centercol").length > 0) {
		return $("#centercol").height();
	}
	else {
  		return $(window).height();
	}	
}

function adjustDimension(skipMapRequest) {
	if($("#centercol").length > 0) {
		var centercoltop = $("#centercol").position().top;
		var maptop = parseInt(map.getDomElement().style.top, 10);
		var offsetTop = maptop;
		var mapheight = frameHeight() - offsetTop  + adjust_height;
		var mapwidth = frameWidth() - map_frame_left - legend_width + adjust_width;
	}
	else {
		var mapheight = frameHeight() - parseInt(map.getDomElement().style.top, 10) - 25;
		var mapwidth = frameWidth() - map_frame_left - legend_width + adjust_width;
	}
	
	map.setWidth(mapwidth);
	map.setHeight(mapheight);

	if (mapExtent !== undefined) {
		map.calculateExtent(mapExtent);
	}
	if (!skipMapRequest) {
		map.setMapRequest();
		mapExtent = undefined;
	}
}

function rebuild() {
	if (mapExtent === undefined) {
		mapExtent = map.extent;
	}

	setTimeout(function () {
		// has to be called twice: the first request does a resize, 
		// which will remove potential scrollbars.
		// After the scrollbars are removed, the window width and height
		// have changed, so another functiona call is necessary to fill
		// this space
		adjustDimension(true);
		//adjustDimension();
		setTimeout(function () {
			adjustDimension(false);
		}, 300);
	}, 100);
}

function control(skipMapRequest){
	adjustDimension(skipMapRequest);
  
	if (!width_temp && frameWidth()) {
		$(window).bind("resize", rebuild);
		width_temp = frameWidth();
		height_temp = frameHeight();
	}
	//adjustDimension(skipMapRequest);

}

if (resize_option == 'auto'){
	Mapbender.events.afterInit.register(function() {
		init(false);
	});
}

function init(skipMapRequest) {
	map = Mapbender.modules[options.target];
	map_frame_left = map.left;
	if (mapExtent === undefined) {
		mapExtent = map.extent;
	}

	map_frame_top = map.top;
	control((skipMapRequest === true) ? true : false);
}

$(this).click(function () {
	init(false);
}).mouseover(function () {
	if (options.src) {
		this.src = options.src.replace(/_off/, "_over");
	}
}).mouseout(function () {
	if (options.src) {
		this.src = options.src;
	}
});
