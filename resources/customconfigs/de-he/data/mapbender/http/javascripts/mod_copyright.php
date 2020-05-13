<?php
# $Id: mod_copyright.php 6660 2010-07-30 09:34:33Z christoph $
# http://www.mapbender.org/index.php/mod_copyright.php
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
?>
var mod_copyright_target = options.target;
//test
options.mod_copyright_text = typeof options.mod_copyright_text === "undefined" ? "mapbender.org" : options.mod_copyright_text;

var mod_copyright_text = options.mod_copyright_text;

var mod_copyright_left = 5;
var mod_copyright_bottom = 20;
var mod_copyright_color1 = "white";
var mod_copyright_color2 = "black";
var mod_copyright_font = "Arial, Helvetica, sans-serif";
var mod_copyright_fontsize = "9px";

eventAfterInit.register(function () {
	mod_copyright();
});
function mod_copyright(){
	var myMapObj = Mapbender.modules[mod_copyright_target];

	var str_c = "<div style='z-index:70;font-family:" + mod_copyright_font + ";font-size:" + mod_copyright_fontsize + ";color:" + mod_copyright_color2 + ";position:absolute;bottom:18px;right:5px;background-color:white;padding:0px 4px 2px 4px'>"+mod_copyright_text+"</div>";

	var map_el = myMapObj.getDomElement();
	if(!map_el.ownerDocument.getElementById(myMapObj.elementName+"_copyright")){
		//create Box Elements
		var el_top = map_el.ownerDocument.createElement("div");
		el_top.style.overflow = "hidden";
		el_top.id = myMapObj.elementName+"_copyright";
		map_el.appendChild(el_top);
	}
	writeTag(myMapObj.frameName, myMapObj.elementName+"_copyright", str_c);
}
