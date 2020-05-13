<?php
# $Id: mod_dragMapSize.php 8337 2012-04-27 12:20:58Z astrid_emde $
# http://www.mapbender.org/index.php/mod_dragMapSize.php
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
echo "var mod_dragMapSize_target = '".$e_target[0]."';";
?>
var mod_dragMapSize_offset  = 15;
var mod_dragMapSize_active  = false;
var isTouchable = false;
var isMainTouchActiv = false;
try {document.createEvent("TouchEvent"); isTouchable = true; } catch(e){}

eventInit.register(function () {
	mod_dragMapSize_init();
});

eventAfterMapRequest.register(function () {
	mod_dragMapSize_arrange()
});

function mod_dragMapSize_init(){
    var el = document.getElementById("dragMapSize");
    if (isTouchable) {
        $('.ui-icon').css({width:'30px',height:'30px'});
        el.addEventListener("mousedown", mod_dragMapSize_touchOn, true);
        el.addEventListener("mouseup", mod_dragMpaSize_touchOff, true);
        el.addEventListener("touchstart", mod_dragMapSize_touchOn, true);
    } else {
//        $('.ui-icon').css({width:'30px',height:'30px'});
        el.onmousedown = mod_dragMapSize_down;
    }
    $(el).hover(function () {
                    $(this).addClass("ui-state-hover");
            },
            function () {
                    $(this).removeClass("ui-state-hover");
            }
    );
    mod_dragMapSize_arrange();
}

function mod_dragMapSize_arrange(){
	var left = parseInt(document.getElementById(mod_dragMapSize_target).style.left, 10) +
                        parseInt(document.getElementById(mod_dragMapSize_target).style.width, 10) +
                        mod_dragMapSize_offset -
                        (parseInt(document.getElementById('dragMapSize').style.width, 10)/2);
	var top = parseInt(document.getElementById(mod_dragMapSize_target).style.top, 10) +
                        parseInt(document.getElementById(mod_dragMapSize_target).style.height, 10) +
                        mod_dragMapSize_offset -
                        (parseInt(document.getElementById('dragMapSize').style.height, 10)/2);
	mb_arrangeElement('','dragMapSize' , left, top);
}
function mod_dragMapSize_touchOn(e){

    body.addEventListener("touchstart", mod_dragMapSize_touch_down, true);
    body.addEventListener("touchmove", mod_dragMapSize_touch_drag, true);
    body.addEventListener("touchend", mod_dragMapSize_touch_up, true);
    mod_dragMapSize_touch_down(e);
}
function mod_dragMpaSize_touchOff(e){
    body.removeEventListener("touchstart", mod_dragMapSize_touch_down, true);
    body.removeEventListener("touchmove", mod_dragMapSize_touch_drag, true);
    body.removeEventListener("touchend", mod_dragMapSize_touch_up, true);
}

function mod_dragMapSize_touch_down(e){
    e.preventDefault();
    var elm = findElement(e, "");
    if(mod_dragMapSize_active == false && isSingleTouch(e)){

        mb_start_x = e.touches[0].pageX;
        mb_start_y = e.touches[0].pageY;
        mb_end_x = mb_start_x;
        mb_end_y = mb_start_y;

        mod_dragMapSize_active = true;

        //create a div that catches all mouse interactions
        var dragElement = document.getElementById("dragMapSize");
        $('.ui-state-active').css({width:'30px',height:'30px'});
        $(dragElement).addClass("ui-state-active");
        var mouseCatcher = dragElement.parentNode.appendChild(document.createElement('div'));
        mouseCatcher.setAttribute("id", "dragMapSize_helper");
        mouseCatcher.style.position = "absolute";
        mouseCatcher.style.cursor = "move";
        mouseCatcher.style.width = "200px";
        mouseCatcher.style.height = "200px";
        mouseCatcher.style.zIndex = 160;
        if($.browser.msie)
                mouseCatcher.style.background = "url(../img/transparent.gif)";
        mouseCatcher.style.left=(mb_start_x-parseInt(mouseCatcher.style.width)) + "px";
        mouseCatcher.style.top=(mb_start_x-parseInt(mouseCatcher.style.height)) + "px";
    }
    return true;
}

function mod_dragMapSize_touch_drag(e){
    e.preventDefault();
    var elm = findElement(e, "");
    if(mod_dragMapSize_active && isSingleTouch(e)){
//        var x, y;
        mb_end_x = e.touches[0].pageX;
        mb_end_y = e.touches[0].pageY;
        var dif_x = mb_end_x - (parseInt(document.getElementById('dragMapSize').style.width)/2);
        var dif_y = mb_end_y - (parseInt(document.getElementById('dragMapSize').style.height)/2);
        mb_arrangeElement('', "dragMapSize", dif_x, dif_y);
        var mouseCatcher = document.getElementById("dragMapSize_helper");
        mb_arrangeElement('', "dragMapSize_helper", mb_end_x-parseInt(mouseCatcher.style.width), mb_end_y-parseInt(mouseCatcher.style.height));
    }
}

function mod_dragMapSize_touch_up(e){
    e.preventDefault();
    var elm = findElement(e, "");
    body.removeEventListener("touchstart", mod_dragMapSize_touch_down, true);
    body.removeEventListener("touchmove", mod_dragMapSize_touch_drag, true);
    body.removeEventListener("touchend", mod_dragMapSize_touch_up, true);
//    if (isMainTouchActiv) {
//            $.extend(Mapbender.modules.mobile_Map).defaultTouch.activate();
//    }

//    if (isSingleTouch(e)) {
//        mb_end_x = e.touches[0].pageX;
//        mb_end_y = e.touches[0].pageY;


        var dragElement = document.getElementById("dragMapSize");
        $(dragElement).removeClass("ui-state-active");

        var mouseCatcher = document.getElementById("dragMapSize_helper");
        mouseCatcher.parentNode.removeChild(mouseCatcher);

        mod_dragMapSize_active = false;

        targetObject = getMapObjByName(mod_dragMapSize_target);
        var dif_x = (parseFloat(mb_end_x) - parseFloat(mb_start_x));
        var dif_y = (parseFloat(mb_end_y) - parseFloat(mb_start_y));

        if(parseFloat(targetObject.width) + parseFloat(dif_x)<0 ||
            parseFloat(targetObject.height) + parseFloat(dif_y)<0){

            var dif_x = mb_start_x - (parseInt(document.getElementById('dragMapSize').style.width) / 2);
            var dif_y = mb_start_y - (parseInt(document.getElementById('dragMapSize').style.height) / 2);
            mb_arrangeElement('', "dragMapSize", dif_x, dif_y);
            return true;
        }

        var newX = (parseFloat(targetObject.width) + parseFloat(dif_x));
        var newY = (parseFloat(targetObject.height) + parseFloat(dif_y));
        var pos =  makeClickPos2RealWorldPos(mod_dragMapSize_target, newX, newY);
        targetObject.setWidth(targetObject.getWidth() + parseFloat(dif_x));
        targetObject.setHeight(targetObject.getHeight() + parseFloat(dif_y));

        var mybbox = targetObject.getExtent().split(",");
        if (typeof mybbox !== "object" || mybbox.length !== 4) {
                return;
        }
        targetObject.setExtent(mybbox[0], pos[1], pos[0], mybbox[3]);
        targetObject.setMapRequest();
        eventResizeMap.trigger();
//    }
    return true;
}

function findElement(event, tagName) {
    var element = getElement(event);
    while (element.parentNode && (!element.tagName ||
          (element.tagName.toUpperCase() != tagName.toUpperCase()))){
        element = element.parentNode;
        return element;
    }
}

function getElement(event) {
    return event.target || event.srcElement;
}

function isSingleTouch(event) {
    return event.touches && event.touches.length == 1;
}

function isMultiTouch(event) {
    return event.touches && event.touches.length > 1;
}

function mod_dragMapSize_down(e){
	if(mod_dragMapSize_active == false){
                document.onmouseup = mod_dragMapSize_up;
                document.onmousemove = mod_dragMapSize_drag;
                mb_getMousePos(e);
                mb_start_x = clickX;
                mb_start_y = clickY;
                mb_end_x = clickX;
                mb_end_y = clickY;
		mod_dragMapSize_active = true;

		//create a div that catches all mouse interactions
		var dragElement = document.getElementById("dragMapSize");
//                $('.ui-state-active').css({width:'30px',height:'30px'});
		$(dragElement).addClass("ui-state-active");
		var mouseCatcher = dragElement.parentNode.appendChild(document.createElement('div'));
		mouseCatcher.setAttribute("id", "dragMapSize_helper");
		mouseCatcher.style.position = "absolute";
		mouseCatcher.style.cursor = "move";
		mouseCatcher.style.width = "500px";
		mouseCatcher.style.height = "500px";
		mouseCatcher.style.zIndex = 160;
		if($.browser.msie)
			mouseCatcher.style.background = "url(../img/transparent.gif)";
		mouseCatcher.style.left=(mb_start_x-parseInt(mouseCatcher.style.width)) + "px";
		mouseCatcher.style.top=(mb_start_x-parseInt(mouseCatcher.style.height)) + "px";

		return false;
	}
}

function mod_dragMapSize_up(e){
        mb_getMousePos(e);
        mb_end_x = clickX;
        mb_end_y = clickY;
        document.onmouseup = null;
        document.onmousemove = null;
	var dragElement = document.getElementById("dragMapSize");
	$(dragElement).removeClass("ui-state-active");

	var mouseCatcher = document.getElementById("dragMapSize_helper");
	mouseCatcher.parentNode.removeChild(mouseCatcher);

	mod_dragMapSize_active = false;

	targetObject = getMapObjByName(mod_dragMapSize_target);
	var dif_x = (parseFloat(mb_end_x) - parseFloat(mb_start_x));
	var dif_y = (parseFloat(mb_end_y) - parseFloat(mb_start_y));
	if(parseFloat(targetObject.width) + parseFloat(dif_x)<0 ||
		parseFloat(targetObject.height) + parseFloat(dif_y)<0)
	{
		var dif_x = mb_start_x - (parseInt(document.getElementById('dragMapSize').style.width) / 2);
		var dif_y = mb_start_y - (parseInt(document.getElementById('dragMapSize').style.height) / 2);
		mb_arrangeElement('', "dragMapSize", dif_x, dif_y);
		return;
	}
	var newX = (parseFloat(targetObject.width) + parseFloat(dif_x));
	var newY = (parseFloat(targetObject.height) + parseFloat(dif_y));
	var pos =  makeClickPos2RealWorldPos(mod_dragMapSize_target, newX, newY);
	targetObject.setWidth(targetObject.getWidth() + parseFloat(dif_x));
	targetObject.setHeight(targetObject.getHeight() + parseFloat(dif_y));

	var mybbox = targetObject.getExtent().split(",");
	if (typeof mybbox !== "object" || mybbox.length !== 4) {
		return;
	}
	targetObject.setExtent(mybbox[0], pos[1], pos[0], mybbox[3]);
	targetObject.setMapRequest();
	eventResizeMap.trigger();
}

function mod_dragMapSize_drag(e){
	if(mod_dragMapSize_active){
            var x, y;
            if (!e)
            e = window.event;
            mb_getMousePos(e);
            x = clickX;
            y = clickY;
            var dif_x = x - (parseInt(document.getElementById('dragMapSize').style.width)/2);
            var dif_y = y - (parseInt(document.getElementById('dragMapSize').style.height)/2);
            mb_arrangeElement('', "dragMapSize", dif_x, dif_y);
            mb_arrangeElement('', "dragMapSize_helper", x-250, y-250);
	}
}

