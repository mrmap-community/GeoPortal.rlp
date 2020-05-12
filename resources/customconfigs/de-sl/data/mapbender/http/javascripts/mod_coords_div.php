<?php
# $Id: mod_coords_div.php 8337 2012-04-27 12:20:58Z astrid_emde $
# http://www.mapbender.org/index.php/mod_coords_div.php
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

require_once(dirname(__FILE__)."/../php/mb_validatePermission.php");
echo "var mod_showCoords_div_target = '".$e_target[0]."';";

include '../include/dyn_js.php';
?>
var displayTarget = displayTarget ? displayTarget : "dependentDiv";

var mod_showCoords_div_win = null;
var mod_showCoords_div_elName = "showCoords_div";
var mod_showCoords_div_frameName = "";
var mod_showCoords_div_img_on = new Image(); mod_showCoords_div_img_on.src = "<?php  echo preg_replace("/_off/","_on",$e_src);  ?>";
var mod_showCoords_div_img_off = new Image(); mod_showCoords_div_img_off.src = "<?php  echo $e_src;  ?>";
var mod_showCoords_div_img_over = new Image(); mod_showCoords_div_img_over.src = "<?php  echo preg_replace("/_off/","_over",$e_src);  ?>";
var mod_showCoords_div_fix = "";
var mod_showCoords_div_mapObj = null;

function init_mod_showCoords_div(ind){
	mod_showCoords_div_mapObj = getMapObjByName(mod_showCoords_div_target );
	
	mb_button[ind] = document.getElementById(mod_showCoords_div_elName);
	mb_button[ind].img_over = mod_showCoords_div_img_over.src;
	mb_button[ind].img_on = mod_showCoords_div_img_on.src;
	mb_button[ind].img_off = mod_showCoords_div_img_off.src;
	mb_button[ind].status = 0;
	mb_button[ind].elName = mod_showCoords_div_elName;
	mb_button[ind].fName = mod_showCoords_div_frameName;
	mb_button[ind].go = mod_showCoords_div_run;
	mb_button[ind].stop = mod_showCoords_div_disable;   
}
function mod_showCoords_div_run(){
        if ($.extend(mod_showCoords_div_mapObj).defaultTouch) {
            $.extend(mod_showCoords_div_mapObj).defaultTouch.deactivate();
        }
	var $map = $(mod_showCoords_div_mapObj.getDomElement());
	$map.bind("mousemove", mod_showCoords_div_display);
	$map.bind("click", mod_showCoords_div_click);
}
function mod_showCoords_div_disable(){
	var $map = $(mod_showCoords_div_mapObj.getDomElement());
	$map.unbind("mousemove", mod_showCoords_div_display);
	$map.unbind("click", mod_showCoords_div_click);
	if(document.getElementById(displayTarget)){
		writeTag("",displayTarget,"");
	}
        if ($.extend(mod_showCoords_div_mapObj).defaultTouch) {
            $.extend(mod_showCoords_div_mapObj).defaultTouch.activate();
        }
}
function mod_showCoords_div_click(e){
	var click = mod_showCoords_div_mapObj.getMousePosition(e);
	if (click === null) {
		return;
	}
	var pos = mod_showCoords_div_mapObj.convertPixelToReal(click);
	mod_showCoords_div_fix = "Selection: " + pos.x + " / " +  pos.y;
	if (mod_showCoords_div_mapObj.epsg =='EPSG:4326' ||  mod_showCoords_div_mapObj.epsg=="EPSG:4258") {
		strDMS = dec2dms(pos.x) + " / " + dec2dms(pos.y);
		 mod_showCoords_div_fix += " <br> " + strDMS;
	}
	mod_showCoord_write(pos.x,pos.y);
}
function mod_showCoords_div_display(e){
	var click = mod_showCoords_div_mapObj.getMousePosition(e);
	if (click === null) {
		return;
	}
	var pos = makeClickPos2RealWorldPos(mod_showCoords_div_target, click.x, click.y);
	mod_showCoord_write(pos[0],pos[1]);
}

function round(x, n) {
	if (n < 1 || n > 14) return false;
	var e = Math.pow(10, n);
	var k = (Math.round(x * e) / e).toString();
	if (k.indexOf('.') == -1) k += '.';
	k += e.toString().substring(1);
	return k.substring(0, k.indexOf('.') + n+1);
}

function dec2dms(x) {
		xD = Math.floor(x); //full deegrees
		xM = (x-xD) * 60; //decimal minutes
		xS = (xM-Math.floor(xM)) * 60;//decimal seconds
		return xD + "°" + Math.floor(xM) + "'" + round(xS,3) + "''";
}


function mod_showCoord_write(x,y){
	if(document.getElementById(displayTarget)){
		var str = x + " / " +  y;
		if (mod_showCoords_div_mapObj.epsg =='EPSG:4326' ||  mod_showCoords_div_mapObj.epsg=="EPSG:4258") {
			strDMS = dec2dms(x) + " / " + dec2dms(y);
			str += " <br> " + strDMS;
		}
		if(mod_showCoords_div_fix != ""){
			str += "<div>" + mod_showCoords_div_fix + "</div>";
		}
		writeTag("",displayTarget, str);
	}
}
