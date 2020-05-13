/**
 * Package: Overview
 *
 * Description:
 * Use the overview map to navigate the main map window. You can select a new 
 * rectangle in the overview map, the details will be displayed in the main 
 * map window. Recenter the main map window section by just clicking a new 
 * position in the overview map (the scale will not change). 
 * 
 * Files:
 *  - http/javascripts/mod_overview.js
 *
 * SQL:
 * > INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, 
 * > e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, 
 * > e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, 
 * > e_mb_mod, e_target, e_requires, e_url) VALUES ('<app_id>','overview',
 * > 2,1,'OverviewFrame','Overview','div','','',15,25,150,150,2,
 * > 'overflow:hidden;',
 * > '<div id="overview_maps" style="position:absolute;left:0px;right:0px;"></div>',
 * > 'div','../plugins/mb_overview.js','mod_box1.js','mapframe1','mapframe1',
 * > 'http://www.mapbender.org/index.php/Overview');
 * > 
 * > INSERT INTO gui_element_vars (fkey_gui_id, fkey_e_id, var_name, 
 * > var_value, context, var_type) VALUES ('<app_id>', 'overview', 
 * > 'overview_wms', '0', 'wms that shows up as overview' ,'var');
 * >
 * > INSERT INTO gui_element_vars (fkey_gui_id, fkey_e_id, var_name, 
 * > var_value, context, var_type) VALUES ('<app_id>', 'overview', 
 * > 'skipWmsIfSrsNotSupported', '0', 
 * > 'if set to 1, it skips the WMS request if the current SRS is not supported by the WMS; if set to 0, the WMS is always queried. Default is 0, because of backwards compatibility' ,
 * > 'var');
 *
 * Help:
 * http://www.mapbender.org/Overview
 *
 * Maintainer:
 * http://www.mapbender.org/User:Christoph_Baudson
 * 
 * Parameters:
 * overview_wms                - wms that shows up as overview
 * skipWmsIfSrsNotSupported    - *[optional]* if set to 1, it skips the WMS 
 * 			request if the current SRS is not supported by the WMS; if set 
 * 			to 0, the WMS is always queried. Default is 0, because of 
 * 			backwards compatibility
 *
 * License:
 * Copyright (c) 2009, Open Source Geospatial Foundation
 * This program is dual licensed under the GNU General Public License 
 * and Simplified BSD license.  
 * http://svn.osgeo.org/mapbender/trunk/mapbender/license/license.txt
 */

if (typeof options.overview_wms === "undefined") {
	options.overview_wms = 0;	
}

if (typeof options.enable_mouse === "undefined") {
	options.enable_mouse = 1;	
}
options.overview_wms = parseInt(options.overview_wms, 10);

if (!options.target) {
	new Mb_exception(options.id + " needs a target.");
	return;
}

var box;
var map;
var $this = $(this);

Mapbender.events.initMaps.register(function () {
        var touch;
	var isTouchable = false;
        try {document.createEvent("TouchEvent"); isTouchable = true; } catch(e){}
	$this.data("isMap", true);

	cntWms = Mapbender.modules['mapframe1'].wms.length;
	if(parseInt(cntWms,10)-1 < options.overview_wms) {
		alert("Overview index wms error. Please check element var 'overview_wms' of application element overview.");
	}
	
	$this.mapbender(new Mapbender.Map({
		id: options.id, 
		width: options.width, 
		height: options.height, 
		wms: wms,
		wmsIndexOverview: options.overview_wms
		
	})); 

	map = Mapbender.modules[options.id];
	map.isOverview = true;

	map.skipWmsIfSrsNotSupported = 
		options.skipWmsIfSrsNotSupported === 1 ? true : false;

	//
	// this line is for backwards compatibility
	//
	mb_mapObj.push(map);


	if (isTouchable) {
            box = new Mapbender.BoxMobile({target: options.id});
            touch = new mapTouch(map,$(map.getDomElement()), map.getDomElement(), box);
            touch.activate();
        } else {
            // add select area behaviour
            box = new Mapbender.Box({
                    target: options.id
            });
            $(map.getDomElement()).mousedown(function (e) {
                    if (options.enable_mouse == '0') {
                            return false;
                    }
                    box.start(e);
                    return false;
            }).mouseup(function (e) {
                    var targetMap = Mapbender.modules[options.target];
                    if (!targetMap) {
                            return false;
                    }
                    box.stop(e, function (extent) {
                            if (typeof extent === "undefined") {
                                    return false;
                            }
                            if (extent.constructor === Mapbender.Extent) {
                                    targetMap.calculateExtent(extent);
                                    targetMap.setMapRequest();
                            }
                            else if (extent.constructor === Mapbender.Point) {
                                    targetMap.setCenter(extent);
                                    targetMap.setMapRequest();
                            }
                    });
                    return false;
            });
        }

	// if the setBackground module is active,
	// the overview wms might be hidden.
	// so we activate it here.
	var ovWmsArray = map.wms;
	if (typeof ovWmsArray !== "object" || ovWmsArray.length === 0) {
		return;
	}
	ovWmsArray[0].gui_wms_visible = 1;

        function mapTouch(map, $map, mapDom, box){
            this.map = map;
            this.$elm = $map;
            this.elm = mapDom;
            this.box = box;
            this.activate = function() {
                this.elm.addEventListener("touchstart", touch.startTouch, true);
                this.elm.addEventListener("touchmove", touch.moveTouch, true);
                this.elm.addEventListener("touchend", touch.endTouch, true);
            }
            this.deactivate = function() {
                this.elm.removeEventListener("touchstart", touch.startTouch, true);
                this.elm.removeEventListener("touchmove", touch.moveTouch, true);
                this.elm.removeEventListener("touchend", touch.endTouch, true);
            }
            this.startTouch = function(event) {
                if (options.enable_mouse == '0') {
                        return false;
                }
                event.preventDefault();
                var elm = findElement(event, "");
                if (isSingleTouch(event)) {
                    touch.startPos = new Point(
                        event.touches[0].pageX - touch.$elm.offset().left,
                        event.touches[0].pageY - touch.$elm.offset().top);
                    var stopPos = new Point(touch.startPos.x, touch.startPos.y);
                    touch.box.start(touch.startPos, stopPos);
                } else if (isMultiTouch(event)) {
                    var startPos = new Point(
                        event.touches[0].pageX - touch.$elm.offset().left,
                        event.touches[0].pageY - touch.$elm.offset().top);
                    var stopPos = new Point(
                        event.touches[1].pageX - touch.$elm.offset().left,
                        event.touches[1].pageY - touch.$elm.offset().top);
                    touch.box.start(startPos, stopPos);
                }
                return true;
            }
            this.moveTouch = function(event) {
                event.preventDefault();
                var elm = findElement(event, "");
                if (isSingleTouch(event)) {
                    var stopPos = new Point(
                        event.touches[0].pageX - touch.$elm.offset().left,
                        event.touches[0].pageY - touch.$elm.offset().top);
                    touch.box.run(touch.startPos, stopPos);
                } else if (isMultiTouch(event)) {
                    var startPos = new Point(
                        event.touches[0].pageX - touch.$elm.offset().left,
                        event.touches[0].pageY - touch.$elm.offset().top);
                    var stopPos = new Point(
                        event.touches[1].pageX - touch.$elm.offset().left,
                        event.touches[1].pageY - touch.$elm.offset().top);
                    touch.box.run(startPos, stopPos);
                }
                return true;
            }
            this.endTouch = function(event) {
                var targetMap = Mapbender.modules[options.target];
                if (!targetMap) {
                        return false;
                }
                return touch.box.stop(function (extent) {
                        if (typeof extent === "undefined") {
                                return false;
                        }
                        if (extent.constructor === Mapbender.Extent) {
                                targetMap.calculateExtent(extent);
                                targetMap.setMapRequest();
                        }
                        else if (extent.constructor === Mapbender.Point) {
                                targetMap.setCenter(extent);
                                targetMap.setMapRequest();
                        }
                });
            }
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
        function isSingleTouch (event) {
            return event.touches && event.touches.length == 1;
        }
        function isMultiTouch(event) {
            return event.touches && event.touches.length > 1;
        }
});

//
// update the rectangle indicating the current extent 
// of the target map after each map request in the target
//
Mapbender.events.init.register(function () {
	options.$target.mapbender(function () {
		var targetMap = this;
		targetMap.events.afterMapRequest.register(function () {
		
			var min = map.convertRealToPixel(targetMap.extent.min);
			var max = map.convertRealToPixel(targetMap.extent.max);
			
			if (min.x < 0) {
				min.x = 0;
			}
			if (max.x > map.getWidth()) {
				max.x = map.getWidth();
			}
			if (min.y > map.getHeight()) {
				min.y = map.getHeight();
			}
			if (max.y < 0) {
				max.y = 0;
			}
			var diffX = max.x - min.x;
			if (diffX < 8) {
				var centerX = 0.5 * (max.x + min.x);
				min.x = centerX - 4;
				max.x = centerX + 4;
			}
			var diffY = min.y - max.y;
			if (diffY < 8) {
				var centerY = 0.5 * (max.y + min.y);
				min.y = centerY + 4;
				max.y = centerY - 4;
			}
			var extent = new Mapbender.Extent(min, max);
		
			box.draw(extent);
		});
	});
});
