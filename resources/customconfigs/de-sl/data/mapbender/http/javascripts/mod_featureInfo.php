<?php
# $Id: mod_featureInfo.php 8796 2014-03-06 15:32:21Z armin11 $
# http://www.mapbender.org/index.php/mod_featureInfo.php
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
include '../include/dyn_js.php';
//defaults for element vars
?>
var ignoreWms = typeof ignoreWms === "undefined" ? [] : ignoreWms;

if(typeof(featureInfoLayerPopup)==='undefined')
	var featureInfoLayerPopup = 'false';
if(typeof(featureInfoPopupHeight)==='undefined')
	var featureInfoPopupHeight = '200';
if(typeof(featureInfoPopupWidth)==='undefined')
	var featureInfoPopupWidth = '270';
if(typeof(featureInfoPopupPosition)==='undefined')
	var featureInfoPopupPosition = 'center';
var reverseInfo = typeof reverseInfo === "undefined" ? "false" : reverseInfo;
if(typeof(featureInfoLayerPreselect)==='undefined')
	var featureInfoLayerPreselect = false;
if(typeof(featureInfoDrawClick)==='undefined')
	var featureInfoDrawClick = false;
if(typeof(featureInfoCircleColor)==='undefined')
	var featureInfoCircleColor = '#ff0000';
if(typeof(featureInfoCollectLayers)==='undefined')
	var featureInfoCollectLayers = false;

var mod_featureInfo_elName = "<?php echo $e_id;?>";
var mod_featureInfo_frameName = "";
var mod_featureInfo_target = "<?php echo $e_target[0]; ?>";
var mod_featureInfo_mapObj = null;

var mod_featureInfo_img_on = new Image(); mod_featureInfo_img_on.src =  "<?php  echo preg_replace("/_off/","_on",$e_src);  ?>";
var mod_featureInfo_img_off = new Image(); mod_featureInfo_img_off.src ="<?php  echo $e_src;  ?>";
var mod_featureInfo_img_over = new Image(); mod_featureInfo_img_over.src = "<?php  echo preg_replace("/_off/","_over",$e_src);  ?>";

if (featureInfoDrawClick) {
	var standingHighlightFeatureInfo = null;
	Mapbender.events.afterMapRequest.register( function(){
		if(standingHighlightFeatureInfo){
			standingHighlightFeatureInfo.paint();
		}
	});
}

eventInit.register(function () {
	mb_regButton(function init_featureInfo1(ind){
		mod_featureInfo_mapObj = getMapObjByName(mod_featureInfo_target);
		mb_button[ind] = document.getElementById(mod_featureInfo_elName);
		mb_button[ind].img_over = mod_featureInfo_img_over.src;
		mb_button[ind].img_on = mod_featureInfo_img_on.src;
		mb_button[ind].img_off = mod_featureInfo_img_off.src;
		mb_button[ind].status = 0;
		mb_button[ind].elName = mod_featureInfo_elName;
		mb_button[ind].fName = mod_featureInfo_frameName;
		mb_button[ind].go = function () {
                        if ($.extend(mod_featureInfo_mapObj).defaultTouch) {
                            $.extend(mod_featureInfo_mapObj).defaultTouch.deactivate();
                        }
			mod_featureInfo_click();
		};
		mb_button[ind].stop = function () {
			mod_featureInfo_disable();
                        if ($.extend(mod_featureInfo_mapObj).defaultTouch) {
                            $.extend(mod_featureInfo_mapObj).defaultTouch.activate();
                        }
		};
	});
});

/**
 * some things from http://stackoverflow.com/a/10997390/11236
 * function changes the order of cs-values for a given get parameter 
 */
function changeURLValueOrder(url, param){
    var newAdditionalURL = "";
    var tempArray = url.split("?");
    var baseURL = tempArray[0];
    var additionalURL = tempArray[1];
    var temp = "";
    if (additionalURL) {
        tempArray = additionalURL.split("&");
        for (i=0; i<tempArray.length; i++){
            	if(tempArray[i].split('=')[0] != param){
                	newAdditionalURL += temp + tempArray[i];
                	temp = "&";
            	} else {
			//get value and sort it in other direction
			var oldValue = tempArray[i].split('=')[1];
			var oldValueArray = oldValue.split(",");
			var newValue = '';
			for (var j = 0; j < oldValueArray.length; j++) {
				newValue = newValue+oldValueArray[oldValueArray.length - (j+1)]+',';
			}
			newValue = newValue.replace(/,+$/,'');
		}
        }
    }
    var rows_txt = temp + "" + param + "=" + newValue;
    return baseURL + "?" + newAdditionalURL + rows_txt;
}

function mod_featureInfo_click(){   
	var el = mod_featureInfo_mapObj.getDomElement();
	
	if (el) {
		$(el).bind("click", mod_featureInfo_event)
			.css("cursor", "help");
	}
}
function mod_featureInfo_disable(){
	var el = mod_featureInfo_mapObj.getDomElement();

	if (el) {
		$(el).unbind("click", mod_featureInfo_event)
			.css("cursor", "default");
	}
}
function mod_featureInfo_event(e){
	var point = mod_featureInfo_mapObj.getMousePosition(e);
	if (featureInfoDrawClick) {
		var map = Mapbender.modules[options.target];
		if(standingHighlightFeatureInfo !== null){ 
			standingHighlightFeatureInfo.clean();
		}else{
			standingHighlightFeatureInfo = new Highlight(
				[options.target],
				"standingHighlightFeatureInfo", 
				{"position":"absolute", "top":"0px", "left":"0px", "z-index":100}, 
				2);
		}
		//calculate realworld position
		realWorldPoint = Mapbender.modules[options.target].convertPixelToReal(point);
		//get coordinates from point
		var ga = new GeometryArray();
		//TODO set current epsg!
		ga.importPoint({
			coordinates:[realWorldPoint.x,realWorldPoint.y,null]
		},Mapbender.modules[options.target].getSRS())
		var m = ga.get(-1,-1);
		standingHighlightFeatureInfo.add(m, featureInfoCircleColor);
		standingHighlightFeatureInfo.paint();
		map.setMapRequest();
	}
	eventBeforeFeatureInfo.trigger({"fName":mod_featureInfo_target});
	//TODO that code should go to featureInfo Redirect module
	if(document.getElementById("FeatureInfoRedirect")){
		//fill the frames
		for(var i=0; i<mod_featureInfo_mapObj.wms.length; i++){
			var req = mod_featureInfo_mapObj.wms[i].getFeatureInfoRequest(mod_featureInfo_mapObj, point);
			if(req)
				window.frames.FeatureInfoRedirect.document.getElementById(mod_featureInfo_mapObj.wms[i].wms_id).src = req;
		}
	}
	else {
		//maybe someone will show all selectable layers in a window before 
		if (featureInfoLayerPreselect) {
			$("#featureInfo_preselect").remove();
			//build list of possible featureInfo requests
			urls = mod_featureInfo_mapObj.getFeatureInfoRequestsForLayers(point, ignoreWms, Mapbender.modules[options.target].getSRS(), realWorldPoint, featureInfoCollectLayers);
			if (urls.length == 0 || typeof urls.length =='undefined') {
				alert("<?php echo _mb("Please enable some layer to be requestable");?>!");
				return false;
			}
			if (urls.length == 1) {
				//don't show interims window!
				//open featureInfo directly
				if(featureInfoLayerPopup == 'true'){
					$("<div><iframe frameborder='0' height='100%' width='100%' id='featureInfo' title='<?php echo _mb("Information");?>' src='" + urls[0].request + "'></iframe></div>").dialog({
						bgiframe: true,
						autoOpen: true,
						modal: false,
						title: '<?php echo _mb("Information");?>',
						width:parseInt(featureInfoPopupWidth, 10),
						height:parseInt(featureInfoPopupHeight, 10),
						position:dialogPosition,
						buttons: {
							"Ok": function(){
								$(this).dialog('close').remove();
							}
						}
					}).parent().css({position:"fixed"});
					return false;
				} else {
					window.open(urls[0].request, "" , "width="+featureInfoPopupWidth+",height="+featureInfoPopupHeight+",scrollbars=yes,resizable=yes");
					return false;
				}
			}
			featureInfoList = "<table border='1'>";
			if (reverseInfo == "true") {
				for(var i=0;i<urls.length;i++){
					if (featureInfoCollectLayers) { 
						featureInfoList += "<tr><td valign='top'><a style='text-decoration:  underline' href='"+urls[i].request+"' target='_blank'>"+urls[i].title+"</a></td><td>";
						//get legend urls if available
						var legend = urls[i].legendurl.split(",");
						for(var k=0;k<legend.length;k++){
							featureInfoList +="<img src='"+legend[k]+"' alt='<?php echo _mb("No legend available");?>!'/><br>";
						}
						featureInfoList += "</td></tr>";
					} else {
					if (urls[i].inBbox) {
						if (urls[i].legendurl !== "empty" ) {
							featureInfoList += "<tr><td valign='top'><a style='text-decoration:  underline' href='"+urls[i].request+"' target='_blank'>"+urls[i].title+"</a></td><td><img src='"+urls[i].legendurl+"' alt='<?php echo _mb("No legend available");?>!'/></td></tr>";
						} else {
							featureInfoList += "<tr><td valign='top'><a style='text-decoration:  underline' href='"+urls[i].request+"' target='_blank'>"+urls[i].title+"</a></td><td><img src='' alt='<?php echo _mb("No legend available");?>!'/></td></tr>";
						}
					}

					}
				}
			} else {
				for(var i=urls.length-1; i>=0; i--){
					if (featureInfoCollectLayers) { 
						featureInfoList += "<tr><td valign='top'><a style='text-decoration:  underline' href='"+urls[i].request+"' target='_blank'>"+urls[i].title+"</a></td><td>";
						//get legend urls if available
						var legend = urls[i].legendurl.split(",");
						for(var k=0;k<legend.length;k++){
							featureInfoList +="<img src='"+legend[k]+"' alt='<?php echo _mb("No legend available");?>!'/><br>";
						}
						featureInfoList += "</td></tr>";
					} else {
					if (urls[i].inBbox) {
						if (urls[i].legendurl !== "empty" ) {
							featureInfoList += "<tr><td valign='top'><a style='text-decoration:  underline' href='"+urls[i].request+"' target='_blank'>"+urls[i].title+"</a></td><td><img src='"+urls[i].legendurl+"' alt='<?php echo _mb("No legend available");?>!'/></td></tr>";
						} else {
							featureInfoList += "<tr><td valign='top'><a style='text-decoration:  underline' href='"+urls[i].request+"' target='_blank'>"+urls[i].title+"</a></td><td><img src='' alt='<?php echo _mb("No legend available");?>!'/></td></tr>";
							}
						}
					}
				}
			}
			featureInfoList += "</table>";
			$("<div id='featureInfo_preselect'></div>").dialog({
				bgiframe: true,
				autoOpen: true,
				modal: false,
				title: '<?php echo _mb("Please choose a requestable Layer");?>',
				width:parseInt(featureInfoPopupWidth, 10),
				height:parseInt(featureInfoPopupHeight, 10),
				position:dialogPosition,
				buttons: {
					"Close": function(){
						if(standingHighlightFeatureInfo !== null){ 
							standingHighlightFeatureInfo.clean();
						}
						$(this).dialog('close').remove();
					}
				}
			}).parent().css({position:"fixed"});
			$("#featureInfo_preselect").append(featureInfoList);
		} else {
			urls = mod_featureInfo_mapObj.getFeatureInfoRequests(point, ignoreWms);
			if(urls){
				for(var i=0;i<urls.length;i++){ //To change order : var i=urls.length-1; i>=0; i--
					//TODO: also rewind the LAYERS parameter for a single WMS FeatureInfo REQUEST if needed?
					var cnt = i;
					if (reverseInfo == 'true') {
						if (typeof(urls[i]) !== "undefined") {
							urls[i] = changeURLValueOrder(urls[i], 'LAYERS');
						}
					}
					if(featureInfoPopupPosition.length == 2 && !isNaN(featureInfoPopupPosition[0]) && !isNaN(featureInfoPopupPosition[1])) {
						var dialogPosition = [];
						dialogPosition[0] = featureInfoPopupPosition[0]+cnt*25;
						dialogPosition[1] = featureInfoPopupPosition[1]+cnt*25;
					}
					else {
						var dialogPosition = featureInfoPopupPosition;
					}
					if(featureInfoLayerPopup == 'true'){
						$("<div><iframe frameborder='0' height='100%' width='100%' id='featureInfo_"+ i + "' title='<?php echo _mb("Information");?>' src='" + urls[i] + "'></iframe></div>").dialog({
							bgiframe: true,
							autoOpen: true,
							modal: false,
							title: '<?php echo _mb("Information");?>',
							width:parseInt(featureInfoPopupWidth, 10),
							height:parseInt(featureInfoPopupHeight, 10),
							position:dialogPosition,
							buttons: {
								"Ok": function(){
									$(this).dialog('close').remove();
								}
							}
						}).parent().css({position:"fixed"});
					}
					else
						window.open(urls[i], "" , "width="+featureInfoPopupWidth+",height="+featureInfoPopupHeight+",scrollbars=yes,resizable=yes");		
				} //end for
			} //end if urls
			else
				alert(unescape("Please select a layer! \n Bitte waehlen Sie eine Ebene zur Abfrage aus!"));
		}
		setFeatureInfoRequest(mod_featureInfo_target,point.x,point.y);
	}
}
