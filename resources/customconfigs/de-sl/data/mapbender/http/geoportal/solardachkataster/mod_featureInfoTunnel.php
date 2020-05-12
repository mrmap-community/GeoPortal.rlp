<?php
# $Id: mod_featureInfoTunnel.php 8379 2012-06-18 08:13:47Z verenadiewald $
# http://www.mapbender.org/index.php/mod_featureInfoTunnel.php
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

require_once(dirname(__FILE__)."/../../php/mb_validateSession.php");
include '../include/dyn_js.php';
//defaults for element vars
?>

if(typeof(featureInfoLayerPopup)==='undefined')
	var featureInfoLayerPopup = 'false';
if(typeof(featureInfoPopupHeight)==='undefined')
	var featureInfoPopupHeight = '200';
if(typeof(featureInfoPopupWidth)==='undefined')
	var featureInfoPopupWidth = '270';
if(typeof(featureInfoPopupPosition)==='undefined')
	var featureInfoPopupPosition = 'center';
if(typeof(featureInfoNoResultPopup)==='undefined')
	var featureInfoNoResultPopup = 'false';		

var mod_featureInfoTunnel_elName = "featureInfoTunnel";
var mod_featureInfoTunnel_frameName = "";
var mod_featureInfoTunnel_target = "<?php echo $e_target[0]; ?>";
var mod_featureInfoTunnel_map = null;


var mod_featureInfoTunnel_img_on = new Image(); mod_featureInfoTunnel_img_on.src =  "<?php  echo preg_replace("/_off/","_on",$e_src);  ?>";
var mod_featureInfoTunnel_img_off = new Image(); mod_featureInfoTunnel_img_off.src ="<?php  echo $e_src;  ?>";
var mod_featureInfoTunnel_img_over = new Image(); mod_featureInfoTunnel_img_over.src = "<?php  echo preg_replace("/_off/","_over",$e_src);  ?>";

eventInit.register(function () {
	mb_regButton(function init_featureInfoTunnel(ind){
            mb_button[ind] = document.getElementById(mod_featureInfoTunnel_elName);
            mb_button[ind].img_over = mod_featureInfoTunnel_img_over.src;
            mb_button[ind].img_on = mod_featureInfoTunnel_img_on.src;
            mb_button[ind].img_off = mod_featureInfoTunnel_img_off.src;
            mb_button[ind].status = 0;
            mb_button[ind].elName = mod_featureInfoTunnel_elName;
            mb_button[ind].fName = mod_featureInfoTunnel_frameName;
            mb_button[ind].go = new Function ("mod_featureInfoTunnel_click()");
            mb_button[ind].stop = new Function ("mod_featureInfoTunnel_disable()");

            mod_featureInfoTunnel_map = getMapObjByName(mod_featureInfoTunnel_target);
	});
});

function mod_featureInfoTunnel_click(){
        if ($.extend(mod_featureInfoTunnel_map).defaultTouch) {
            $.extend(mod_featureInfoTunnel_map).defaultTouch.deactivate();
        }
	var domNode = mod_featureInfoTunnel_map.getDomElement();
	if (domNode) {
		$(domNode).bind("click", mod_featureInfoTunnel_event);
	}
	mod_featureInfoTunnel_map.getDomElement().style.cursor = "help";

}
function mod_featureInfoTunnel_disable(){
	var domNode = mod_featureInfoTunnel_map.getDomElement();
	if (domNode) {
		$(domNode).unbind("click", mod_featureInfoTunnel_event);
	}
	
	mod_featureInfoTunnel_map.getDomElement().style.cursor = "default";

        if ($.extend(mod_featureInfoTunnel_map).defaultTouch) {
            $.extend(mod_featureInfoTunnel_map).defaultTouch.activate();
        }
}

function removeProgressWheel () {
	$("#" + mod_featureInfoTunnel_map.elementName + "_progressWheel").empty();
	$("#" + mod_featureInfoTunnel_map.elementName + "_progressWheel").css("visibility","hidden");
}

var currentMarker_mapframe1 = null;

function setHighlightMarker(frameName,x,y) {	
   var scale = parent.mb_getScale(frameName);

	 if (scale < 5001){
	 var width  = 60;
	 var height = 60;
	 }
	 if (scale>=5001 && scale<25001){
	 var width  = 40;
	 var height = 40;
	 }
	 if (scale > 25001) {
	 var width  = 10;
	 var height = 10;
	 }

	if (currentMarker_mapframe1 !== null) {
		currentMarker_mapframe1.remove();
	}
	currentMarker_mapframe1 = new Mapbender.Marker(new Mapbender.Point(x, y), Mapbender.modules.mapframe1, {
		img: {
			url: "../geoportal/solardachkataster/img/marker_fett.png",
			width: width,
			height: height,
			offset: new Mapbender.Point(-parseInt(width / 2, 10), -parseInt(height / 2, 10))
		}
	});
}

function deleteHighlightMarker() {
	if (currentMarker_mapframe1 !== null) {
		currentMarker_mapframe1.remove();
	}		
}	

function mod_featureInfoTunnel_event(e){
	var point = mod_featureInfoTunnel_map.getMousePosition(e);
	
	var map = Mapbender.modules[mod_featureInfoTunnel_target];
	
	//calculate realworld position
	var realWorldPoint = Mapbender.modules[mod_featureInfoTunnel_target].convertPixelToReal(point);
	
	setHighlightMarker(mod_featureInfoTunnel_target,realWorldPoint.x,realWorldPoint.y);
	//map.setMapRequest();
		
	eventBeforeFeatureInfo.trigger({"fName":mod_featureInfoTunnel_target});
	
	//create progress wheel element
	var map_el = mod_featureInfoTunnel_map.getDomElement();
	if (!map_el.ownerDocument.getElementById(mod_featureInfoTunnel_map.elementName + "_progressWheel")) {
		//create progress wheel element

		var $div = $("<div id='" + mod_featureInfoTunnel_map.elementName + "_progressWheel'></div>");
		map_el.appendChild($div.get(0));
	}

	var point = mod_featureInfoTunnel_map.getMousePosition(e);
	var path = '../extensions/ext_featureInfoTunnel.php';
	
//TODO that code should go to featureInfo Redirect module
	var ind = getMapObjIndexByName(mod_featureInfoTunnel_target);
	if(document.getElementById("FeatureInfoRedirect")){
		//fill the frames
		for(var i=0; i<mod_featureInfoTunnel_map.wms.length; i++){
			var req = mod_featureInfoTunnel_map.wms[i].getFeatureInfoRequest(mb_mapObj[ind], point);
			if(req)
				window.frames.FeatureInfoRedirect.document.getElementById(mod_featureInfoTunnel_map.wms[i].wms_id).src = path+"?url="+escape(req);
		}
	}
	else{
		urls = mod_featureInfoTunnel_map.getFeatureInfoRequests(point);
		if(urls){
			for(var i=0;i<urls.length;i++){
				(function () {
					var currentMapObjWidth = point.x;   
					var currentMapObjHeight = point.y;
					$("#" + mod_featureInfoTunnel_map.elementName + "_progressWheel").html("<img src='../img/indicator_wheel.gif'/>");
					$("#" + mod_featureInfoTunnel_map.elementName + "_progressWheel").css({
						position: "absolute",
						top: currentMapObjHeight,
						left: currentMapObjWidth,
						visibility: "visible",
						zIndex: 100
					});
					window.setTimeout("removeProgressWheel()", 10000);
					var currentRequest = urls[i];
					var cnt = i;
					if(featureInfoPopupPosition.length == 2 && !isNaN(featureInfoPopupPosition[0]) && !isNaN(featureInfoPopupPosition[1])) {
						var dialogPosition = [];
						dialogPosition[0] = featureInfoPopupPosition[0]+cnt*25;
						dialogPosition[1] = featureInfoPopupPosition[1]+cnt*25;
					}
					else {
						var dialogPosition = featureInfoPopupPosition;
					}
					$(".fiResultFrame").remove();
					$(".featureInfoTunnel-dialog").remove();
          
          			currentRequest = currentRequest.replace(/EXCEPTIONS=application\/vnd.ogc.se_inimage/g,'EXCEPTIONS=application\/vnd.ogc.se_xml');  
          			
					mb_ajax_post(path, {'url':currentRequest},function(js_code,status){
						if(js_code){
							if(featureInfoLayerPopup == 'true') {
								$("<div><iframe frameborder=0 class='fiResultFrame' id='featureInfo_"+ i + "' style='width:100%;height:100%;' src='" + path + "?url=" + encodeURIComponent(currentRequest) + "'></iframe></div>").dialog({
									bgiframe: true,
									autoOpen: true,
									dialogClass: "featureInfoTunnel-dialog",
									title: '<?php echo _mb("Information");?>',
									modal: false,
									width:parseInt(featureInfoPopupWidth, 10),
									height:parseInt(featureInfoPopupHeight, 10),
									position:dialogPosition,
// This is a workaround if dialogs don't have the appropriate height
//									height: 450,
//									open: function(){
//										$(this).css({
//											"height": parseInt(featureInfoPopupHeight, 10)+ "px"
//										});
//									},
									close: function() {
										$(this).remove();
										
										//Marker remove
										deleteHighlightMarker();
									}
								}).parent().css({position:"fixed"});
								
								$(".noResultFound").dialog("close");
							}
							else{
								window.open(path+"?url=" + encodeURIComponent(currentRequest), "" , "width="+featureInfoPopupWidth+",height="+featureInfoPopupHeight+",scrollbars=yes,resizable=yes");
							}
						}
						
						if(featureInfoNoResultPopup == 'true' && featureInfoLayerPopup == 'true') {
							if($(".fiResultFrame").size() === 0) {
								$(".noResultFound").dialog("close");
								$("<div class='noResultFound'><?php echo _mb("No result");?></div>").dialog({
									bgiframe: true,
									title: "<?php echo _mb("Information");?>",
									autoOpen: true,
									modal: false,
									width:300,
									height:200,
									position:[600,200]
								}).parent().css({position:"fixed"});
							}
						}	
						removeProgressWheel();
					});
				}());
			}
		}
		else
			alert(unescape("Please select a layer! \n Bitte waehlen Sie eine Ebene zur Abfrage aus!"));
	}	
}
