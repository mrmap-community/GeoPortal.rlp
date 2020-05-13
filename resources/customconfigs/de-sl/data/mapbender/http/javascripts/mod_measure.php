<?php
# $Id: mod_measure.php 8336 2012-04-27 12:11:28Z astrid_emde $
# http://www.mapbender.org/index.php/mod_measure.php
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

require_once(dirname(__FILE__)."/../php/mb_validateSession.php");

echo "var mod_measure_target = '".$e_target[0]."';";
?>
var mod_measure_color1 = "white";
var mod_measure_color2 = "black";
var mod_measure_font = "Arial, Helvetica, sans-serif";
var mod_measure_fontsize = "9px";
var mod_measure_basepoint = "#8a2be2";
var mod_measure_linepoint = "#ff00ff";
var mod_measure_bg = "";
var mod_measure_pgsql = true;

var mod_measure_win = null;

var mod_measure_elName = "measure";
var mod_measure_frameName = "";
var mod_measure_mapObj = null;
var mod_measure_epsg;
var mod_measure_width;
var mod_measure_height;
var mod_measure_RX = new Array();
var mod_measure_RY = new Array();
var mod_measure_Dist = new Array();
var mod_measure_TotalDist = new Array();

// global variable
window.eventAfterMeasure = new MapbenderEvent();

var mod_measure_img_on = new Image(); mod_measure_img_on.src = "<?php  echo preg_replace("/_off/","_on",$e_src);  ?>";
var mod_measure_img_off = new Image(); mod_measure_img_off.src = "<?php  echo $e_src;  ?>";
var mod_measure_img_over = new Image(); mod_measure_img_over.src = "<?php  echo preg_replace("/_off/","_over",$e_src);  ?>";



function init_mod_measure(ind){
	mod_measure_mapObj = getMapObjByName(mod_measure_target);
	//ensure we have the div elements in Mapframe
	var map_el = mod_measure_mapObj.getDomElement()
	if(!map_el.ownerDocument.getElementById(mod_measure_target+"_measure_sub")){
		el = map_el.ownerDocument.createElement("div");
		el.style.position = "absolute";
		el.style.top = "0px";
		el.style.left = "0px";
		el.style.zIndex = "98";
		el.style.fontSize = "10px";

		el1 = el.cloneNode(false);
		el2 = el.cloneNode(false);

		el.id = mod_measure_target+"_measure_sub";
		el.style.zIndex = "100";
		el1.id = mod_measure_target+"_measure_display";
		el1.style.zIndex = "22";
		el2.id = mod_measure_target+"_measuring";
		
		map_el.appendChild(el);
		map_el.appendChild(el1);
		map_el.appendChild(el2);
	}
	
	mb_button[ind] = document.getElementById(mod_measure_elName);
	mb_button[ind].img_over = mod_measure_img_over.src;
	mb_button[ind].img_on = mod_measure_img_on.src;
	mb_button[ind].img_off = mod_measure_img_off.src;
	mb_button[ind].status = 0;
	mb_button[ind].elName = mod_measure_elName;
	mb_button[ind].fName = mod_measure_frameName;
	mb_button[ind].go = function () {
                if ($.extend(mod_measure_mapObj).defaultTouch) {
                    $.extend(mod_measure_mapObj).defaultTouch.deactivate();
                }
		mod_measure_go();
	};
	mb_button[ind].stop = function () {
		mod_measure_disable();
                if ($.extend(mod_measure_mapObj).defaultTouch) {
                    $.extend(mod_measure_mapObj).defaultTouch.activate();
                }
	};
	mod_measure_width = mod_measure_mapObj.width;
	mod_measure_height = mod_measure_mapObj.height;
	mod_measure_epsg = mod_measure_mapObj.epsg;
	eventAfterMapRequest.register(function () {
		drawDashedLine();
	});
	mb_registerPanSubElement(mod_measure_target+"_measuring");
}
function mod_measure_go(){
	var el = mod_measure_mapObj.getDomElement();
	if (el) {
		$(el).bind("mousedown", mod_measure_start)
			.bind("mousemove", mod_measure_run);
		el.style.cursor = 'crosshair';
	}

	var measureSub = eventAfterMeasure.trigger({}, "CAT");
	writeTag("",mod_measure_target+"_measure_sub",measureSub);
}
function mod_measure_disable(){
	var el = mod_measure_mapObj.getDomElement();
		$(el).unbind("mousedown", mod_measure_start)
			.unbind("mousemove", mod_measure_run);
	writeTag("",mod_measure_target+"_measure_display","");
	writeTag("",mod_measure_target+"_measure_sub","");
}
function mod_measure_timeout(){
	var el = mod_measure_mapObj.getDomElement();
		$(el).unbind("mousedown", mod_measure_start)
			.unbind("mousemove", mod_measure_run);
}
function mod_measure_disableTimeout(){
	var el = mod_measure_mapObj.getDomElement();
		$(el).bind("mousedown", mod_measure_start)
			.bind("mousemove", mod_measure_run);
}
function mod_measure_start(e){
	var mousepos = mod_measure_mapObj.getMousePosition(e);
	
	var realWorldPos = makeClickPos2RealWorldPos(mod_measure_target,mousepos.x,mousepos.y);
	if(mod_measure_epsg=="EPSG:4326"){
		mod_measure_RX[mod_measure_RX.length] = realWorldPos[0];
		mod_measure_RY[mod_measure_RY.length] = realWorldPos[1];
	}
	else{
		mod_measure_RX[mod_measure_RX.length] = Math.round(realWorldPos[0] * 100)/100;
		mod_measure_RY[mod_measure_RY.length] = Math.round(realWorldPos[1] * 100)/100;
	}
	if(mod_measure_RX.length > 1){
		var dist;
		if(mod_measure_epsg=="EPSG:4326"){
			//convert coordinates to radian
			var lon_from=(mod_measure_RX[mod_measure_RX.length-2]*Math.PI)/180;
			var lat_from=(mod_measure_RY[mod_measure_RY.length-2]*Math.PI)/180;
			var lon_to=(mod_measure_RX[mod_measure_RX.length-1]*Math.PI)/180;
			var lat_to=(mod_measure_RY[mod_measure_RY.length-1]*Math.PI)/180;
			dist=6371229*Math.acos(Math.sin(lat_from)*Math.sin(lat_to)+Math.cos(lat_from)*Math.cos(lat_to)*Math.cos(lon_from-lon_to));
			dist=Math.round(dist*100)/100;
		}
		else{
			var dist_x = Math.abs(mod_measure_RX[mod_measure_RX.length-2] - mod_measure_RX[mod_measure_RX.length-1]);
			var dist_y = Math.abs(mod_measure_RY[mod_measure_RY.length-2] - mod_measure_RY[mod_measure_RY.length-1]);
			dist = Math.round(Math.sqrt(Math.pow(dist_x,2) + Math.pow(dist_y,2))*100)/100;
		}
		mod_measure_Dist[mod_measure_Dist.length] = dist;
		var totalDist = mod_measure_TotalDist[mod_measure_TotalDist.length-1] + dist;
		mod_measure_TotalDist[mod_measure_TotalDist.length] = Math.round(totalDist * 100)/100;
	}
	else{
		mod_measure_Dist[mod_measure_Dist.length] = 0;
		mod_measure_TotalDist[mod_measure_TotalDist.length] = 0;
	}
	drawDashedLine();
}
function drawDashedLine(){
	//check if epsg has changed
	mod_measure_width = mod_measure_mapObj.width;
	mod_measure_height = mod_measure_mapObj.height;
	if(mod_measure_epsg != mod_measure_mapObj.epsg){
		mod_measure_delete();
		mod_measure_epsg = mod_measure_mapObj.epsg;
	}
	var str_mPoints = "<div style='position:absolute;left:0px;top:0px' ><img src='"+mb_trans.src+"' width='"+mod_measure_width+"' height='0'></div>";
	str_mPoints += "<div style='position:absolute;left:0px;top:0px' ><img src='"+mb_trans.src+"' width='0' height='"+mod_measure_height+"'></div>";
	for(var i=0; i<mod_measure_RX.length; i++){
		var pos = makeRealWorld2mapPos(mod_measure_target,mod_measure_RX[i],mod_measure_RY[i]);
		str_mPoints += "<div style='font-size:1px;position:absolute;top:"+(pos[1]-2)+"px;left:"+(pos[0]-2)+"px;width:4px;height:4px;background-color:"+mod_measure_basepoint+"'></div>";
		if(i>0){
			str_mPoints += "<div  style='font-family:"+mod_measure_font+";font-size:"+mod_measure_fontsize+";color:"+mod_measure_color1+";";
			if(mod_measure_bg != ""){
				str_mPoints += "background-color:"+mod_measure_bg+";";
			}
			str_mPoints += "position:absolute;top:"+(pos[1] + 3)+"px;left:"+(pos[0]+3)+"px;z-index:20'>"+mod_measure_TotalDist[i]+"</div>";
			str_mPoints += "<div  style='font-family:"+mod_measure_font+";font-size:"+mod_measure_fontsize+";color:"+mod_measure_color2+";position:absolute;top:"+(pos[1] + 4)+"px;left:"+(pos[0]+4)+"px;z-index:21'>"+mod_measure_TotalDist[i]+"</div>";
		}
	}
	if(mod_measure_RX.length>1){
		for(var k=1; k<mod_measure_RX.length; k++){
			var pos0 = makeRealWorld2mapPos(mod_measure_target,mod_measure_RX[k], mod_measure_RY[k]);
			var pos1 = makeRealWorld2mapPos(mod_measure_target,mod_measure_RX[k-1], mod_measure_RY[k-1]);
			str_mPoints += evaluateDashes(pos1[0],pos1[1],pos0[0],pos0[1],k);
		}
	}
	writeTag("",mod_measure_target+"_measuring",str_mPoints);
}
function evaluateDashes(x1,y1,x2,y2,count){
	var str_dashedLine = "";
	var s = 10;
	var d = Math.sqrt(Math.pow((y1-y2),2) + Math.pow((x1-x2),2)) ;
	var n = Math.round(d/s);
	var s_x =  (x2 - x1)/n;
	var s_y =  (y2 - y1)/n;
	for(var i=1; i<n; i++){
		var x = Math.round(x1 + i * s_x)-2;
		var y = Math.round(y1 + i * s_y)-2;
		if(x >= 0 && x <= mod_measure_width && y >= 0 && y <= mod_measure_height){
			str_dashedLine += "<div style='font-size:1px;position:absolute;top:"+y+"px;left:"+x+"px;width:4px;height:4px;background-color:"+mod_measure_linepoint+"'></div>";
		}
	}
	str_dashedLine += "<div style='font-family:"+mod_measure_font+";font-size:"+mod_measure_fontsize+";color:"+mod_measure_color1+";";
	if(mod_measure_bg != ""){
		str_dashedLine += "background-color:"+mod_measure_bg+";";
	}   
	str_dashedLine += "position:absolute;top:"+(Math.round(y1 + (y2-y1)/2 +3))+"px;left:"+(Math.round(x1 + (x2-x1)/2 +3))+"px'>"+mod_measure_Dist[count]+"</div>";
	str_dashedLine += "<div style='font-family:"+mod_measure_font+";font-size:"+mod_measure_fontsize+";color:"+mod_measure_color2+";position:absolute;top:"+(Math.round(y1 + (y2-y1)/2 + 4))+"px;left:"+(Math.round(x1 + (x2-x1)/2+4))+"px'>"+mod_measure_Dist[count]+"</div>";
	return str_dashedLine;
}
function mod_measure_run(e){
	var mousepos = mod_measure_mapObj.getMousePosition(e);
	var pos = makeClickPos2RealWorldPos(mod_measure_target,mousepos.x,mousepos.y);
	var dist;
	if(mod_measure_epsg=="EPSG:4326"){
		//convert coordinates to radian
		var lon_from=(pos[0]*Math.PI)/180;
		var lat_from=(pos[1]*Math.PI)/180;
		var lon_to=(mod_measure_RX[mod_measure_RX.length-1]*Math.PI)/180;
		var lat_to=(mod_measure_RY[mod_measure_RY.length-1]*Math.PI)/180;
		dist=6371229*Math.acos(Math.sin(lat_from)*Math.sin(lat_to)+Math.cos(lat_from)*Math.cos(lat_to)*Math.cos(lon_from-lon_to));
	}
	else{
		var dist_x = Math.abs(mod_measure_RX[mod_measure_RX.length-1] - pos[0]);
		var dist_y = Math.abs(mod_measure_RY[mod_measure_RY.length-1] - pos[1]);
		dist=Math.sqrt(dist_x*dist_x+dist_y*dist_y);
	}
	if(isNaN(dist) == false && mousepos.x > 0 && mousepos.x < mod_measure_width && mousepos.y > 0 && mousepos.y < mod_measure_height){
		var str_display = "<span style='font-family:"+mod_measure_font+";font-size:"+mod_measure_fontsize+";color:"+mod_measure_color2+";'>"+(Math.round(dist*100)/100)+" m</span>";
		writeTag("", mod_measure_target+"_measure_display",str_display);
		mb_arrangeElement("",mod_measure_target+"_measure_display",mousepos.x +2, mousepos.y - 10);
	}
	else{
		writeTag("",mod_measure_target+"_measure_display","");
	}
}
function mod_measure_close(){
	if(mod_measure_RX.length < 3 || (mod_measure_RX[mod_measure_RX.length-1] == mod_measure_RX[0] && mod_measure_RY[mod_measure_RY.length-1] == mod_measure_RY[0])){return;}
	mod_measure_RX[mod_measure_RX.length] = mod_measure_RX[0];
	mod_measure_RY[mod_measure_RY.length] = mod_measure_RY[0];
	if(mod_measure_RX.length > 1){
		var dist;
		if(mod_measure_epsg=="EPSG:4326"){
			//convert coordinates to radian
			var lon_from=(mod_measure_RX[mod_measure_RX.length-2]*Math.PI)/180;
			var lat_from=(mod_measure_RY[mod_measure_RY.length-2]*Math.PI)/180;
			var lon_to=(mod_measure_RX[mod_measure_RX.length-1]*Math.PI)/180;
			var lat_to=(mod_measure_RY[mod_measure_RY.length-1]*Math.PI)/180;
			dist=6371229*Math.acos(Math.sin(lat_from)*Math.sin(lat_to)+Math.cos(lat_from)*Math.cos(lat_to)*Math.cos(lon_from-lon_to));
			dist=Math.round(dist*100)/100;
		}
		else{
			var dist_x = Math.abs(mod_measure_RX[mod_measure_RX.length-2] - mod_measure_RX[mod_measure_RX.length-1]);
			var dist_y = Math.abs(mod_measure_RY[mod_measure_RY.length-2] - mod_measure_RY[mod_measure_RY.length-1]);
			dist = Math.round(Math.sqrt(Math.pow(dist_x,2) + Math.pow(dist_y,2))*100)/100;
		}
		mod_measure_Dist[mod_measure_Dist.length] = dist;
		var totalDist = mod_measure_TotalDist[mod_measure_TotalDist.length-1] + dist;
		mod_measure_TotalDist[mod_measure_TotalDist.length] = Math.round(totalDist * 100)/100;
	}
	else{
		mod_measure_Dist[mod_measure_Dist.length] = 0;
		mod_measure_TotalDist[mod_measure_TotalDist.length] = 0;
	}
	drawDashedLine();
}
function mod_measure_delete(){
	mod_measure_RX = new Array();
	mod_measure_RY = new Array();
	mod_measure_Dist = new Array();
	mod_measure_TotalDist = new Array();
	writeTag("",mod_measure_target+"_measuring","");
	writeTag("",mod_measure_target+"_measure_display","");
}

var mod_closePolygon_img = new Image();
mod_closePolygon_img.src = "../img/button_gray/closePolygon_off.gif";
mod_closePolygon_img.title = '<?php echo _mb("Close polygon");?>';

eventAfterMeasure.register(function(){
	return mod_closePolygon();
});

function mod_closePolygon(){
	var str =  "<div style='position:absolute;top:25px' onmouseup='mod_closePolygon_go()' ";
	str += "onmouseover='mod_measure_timeout()' onmouseout='mod_measure_disableTimeout()'><img src='"+mod_closePolygon_img.src+"' style='cursor:pointer' title='"+mod_closePolygon_img.title+"'></div>";
	return str;
}
function mod_closePolygon_go(){
	mod_measure_close();
}

var mod_rubber_img = new Image();
mod_rubber_img.src = "../img/button_gray/rubber_off.gif";
mod_rubber_img.title = '<?php echo _mb("Rubber");?>';
eventAfterMeasure.register(function () {
	return mod_rubber();
});

function mod_rubber(){
   var str =  "<div onmouseup='mod_rubber_go()' onmouseover='mod_measure_timeout()' onmouseout='mod_measure_go()'><img src='"+mod_rubber_img.src+"' style='cursor:pointer' title='"+mod_rubber_img.title+"'></div>";
   return str;
}
function mod_rubber_go(){
   mod_measure_delete();
}

var mod_getArea_img = new Image();
mod_getArea_img.src = "../img/button_gray/getArea_off.gif";
mod_getArea_img.title = '<?php echo _mb("Get area");?>';
eventAfterMeasure.register(function () {
	return mod_getArea();
});

function mod_getArea(){
	var str =  "<div id='getAreaButton' style='position:absolute;top:50px' onmouseup='mod_getArea_go()' ";
	str += "onmouseover='mod_measure_timeout()' onmouseout='mod_measure_disableTimeout()'><img src='"+mod_getArea_img.src+"' style='cursor:pointer' title='"+mod_getArea_img.title+"'></div>";
	return str;
}
function mod_getArea_go(){
	if(mod_measure_RX[mod_measure_RX.length -1] == mod_measure_RX[0] && mod_measure_RY[mod_measure_RY.length -1] == mod_measure_RY[0]){
		var ind = getMapObjIndexByName(mod_measure_target);
		var url = "../php/mod_evalArea.php?x=";
		for(var i=0; i<mod_measure_RX.length;i++){
			if(i>0){ url += ",";}
			url += mod_measure_RX[i];
		}
		url += "&y=";
		for(var i=0; i<mod_measure_RY.length;i++){
			if(i>0){ url += ",";}
			url += mod_measure_RY[i];
		}
		var srs = mb_mapObj[ind].epsg.split(":");
		url += "&srs="+ escape(srs[1]);
		url += "&length=" + mod_measure_TotalDist[mod_measure_TotalDist.length-1];
		url += "&distance="  + mod_measure_TotalDist[mod_measure_TotalDist.length-2];
		
		$.get(url, function (json) {
                	var data = eval('(' + json + ')');
			if (!measurePopupInstance) {
				var measurePopupInstance = $("<div />").html("<h3>Fl&auml;che:</h3> " + data.area + " m&sup2;<br><br><h3>Umfang:</h3> " + data.perimeter + " m<br>").dialog({
                                    title: "Messinformationen"});
			}
			else {
				measurePopupInstance.html("<h3>Fl&auml;che:</h3>" + data.area + " m&sup2;<br><br><h3>Umfang:</h3> " + data.perimeter + " m<br>").dialog("open");
			}
			
			return;
		});
		return;

                /*
		if(!mod_measure_win || mod_measure_win == null || mod_measure_win.closed == true){
			mod_measure_win = window.open(url,"mod_measure_win","width=200,height=150,resizable=yes");
		}
		else{
			mod_measure_win.document.location.href = url;
		}
		mod_measure_win.focus();
                */
	}
	else{
                if (!measurePopupInstance) {
                        var measurePopupInstance = $("<div />").html("<h3>Strecke: </h3>" + mod_measure_TotalDist[mod_measure_TotalDist.length-1] + " m").dialog({
                            title: "Messinformation"
                        });
                }
                else {
                        measurePopupInstance.html("<h3>Strecke: </h3>" + mod_measure_TotalDist[mod_measure_TotalDist.length-1] + " m").dialog("open");
                }
                return;

                /*
		if(!mod_measure_win || mod_measure_win == null || mod_measure_win.closed == true){
			mod_measure_win = window.open("","mod_measure_win","width=200,height=150,resizable=yes");
			mod_measure_win.document.open("text/html");
			mod_measure_win.document.write("<span style = 'font-family : Arial, Helvetica, sans-serif;font-size : 12px;font-weight : bold;';>Strecke: " +mod_measure_TotalDist[mod_measure_TotalDist.length-1]+" m</span>");
			mod_measure_win.document.close();        
		}
		else{
			mod_measure_win.document.open("text/html");
			mod_measure_win.document.write("<span style = 'font-family : Arial, Helvetica, sans-serif;font-size : 12px;font-weight : bold;';>Strecke: " +mod_measure_TotalDist[mod_measure_TotalDist.length-1]+" m</span>");
			mod_measure_win.document.close();
		}
		mod_measure_win.focus();
                */
	}
}
