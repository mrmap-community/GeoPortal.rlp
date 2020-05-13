/*
* $Id: requestGeometryConstructor.js 1882 2007-12-03 09:01:24Z verenadiewald $
* COPYRIGHT: (C) 2001 by ccgis. This program is free software under the GNU General Public
* License (>=v2). Read the file gpl.txt that comes with Mapbender for details.
*/
//http://www.mapbender.org/index.php/requestGeometryConstructor.js

/**
 * @class A class representing a constructor for a geometry/WFS request.
 *
 * @constructor
 * @param {String} geomType type of the {@link RequestGeometryConstructor}
 * @param {String} geomType target of the {@link RequestGeometryConstructor}
 */
function RequestGeometryConstructor(geomTarget){
    /**
 	 * geomTarget target of the {@link RequestGeometryConstructor}
	 *
	 * @type String
	 */
    this.geomTarget = geomTarget;

    var that = this;
    var ind = getMapObjIndexByName(this.geomTarget);
    var myMapObj = mb_mapObj[ind];
    var map_el = myMapObj.getDomElement();

    var box;
    var touch;
    var isTouchable = false;
    try {
        document.createEvent("TouchEvent");
        isTouchable = true;
    } catch(e){}


    if(!map_el.ownerDocument.getElementById(myMapObj.elementName+"_request_geometry_polygon")){

        //create Box Elements
        var el_top = map_el.ownerDocument.createElement("div");
        el_top.style.position = "absolute";
        el_top.style.top = "0px";
        el_top.style.left = "0px";
        el_top.style.zIndex = "500";
        el_top.style.fontSize = "10px";
        el_top.id = myMapObj.elementName+"_request_geometry_polygon";
        map_el.appendChild(el_top);
    }

    if(!map_el.ownerDocument.getElementById(myMapObj.elementName+"_measure_display")){
        //create Box Elements
        var el_top = map_el.ownerDocument.createElement("div");
        el_top.style.position = "absolute";
        el_top.style.top = "0px";
        el_top.style.left = "0px";
        el_top.style.zIndex = "510";
        el_top.id = myMapObj.elementName+"_measure_display";
        map_el.appendChild(el_top);
    }


    this.getGeometry = function(queryType,callbackFunction){
        var target = this.geomTarget;
        s = new Snapping(this.geomTarget);
        callback = callbackFunction;

        var ind = getMapObjIndexByName(target);
        var el = mb_mapObj[ind].getDomElement();
        $(el).unbind("mousedown")
        .unbind("mouseover")
        .unbind("mouseup")
        .unbind("mousemove");
        if (queryType == "point") {
            if ($.extend(myMapObj).defaultTouch) {
                $.extend(myMapObj).defaultTouch.deactivate();
            }
            queryGeom = new Geometry(geomType.point);
            $(el).mousedown(function (e) {
                realWorldPos = mapToReal(target,myMapObj.getMousePosition(e));
                queryGeom.addPoint(realWorldPos);
                callback(target, queryGeom);
                $(el).unbind("mousedown")
                .unbind("mouseover")
                .unbind("mouseup")
                .unbind("mousemove");
                queryGeom = null;
                if ($.extend(myMapObj).defaultTouch) {
                    $.extend(myMapObj).defaultTouch.activate();
                }
            });
        }
        else if (queryType == "polygon") {
            if ($.extend(myMapObj).defaultTouch) {
                $.extend(myMapObj).defaultTouch.deactivate();
            }
            queryGeom = new Geometry(geomType.polygon);
            if(isTouchable){
                $(el).mousedown(function (e) {
                    wfsSpatialRequestStart(e);
                })
            } else {
                $(el).mousedown(function (e) {
                    wfsSpatialRequestStart(e);
                }).mousemove(function (e) {
                    wfsSpatialRequestRun(e);
                });
            }

        }
        else if (queryType == "rectangle") {
            if(isTouchable) {
                if ($.extend(myMapObj).defaultTouch) {
                    $.extend(myMapObj).defaultTouch.deactivate();
                }
                box = new Mapbender.BoxMobile({
                    target: geomTarget
                });
                touch = new selAreaTouch(myMapObj,$(myMapObj.getDomElement()), myMapObj.getDomElement(), box);
                touch.activate();
                queryGeom = new Geometry(geomType.line);
//                $(el).mousedown(function (e) {
//                    box.start(e);
//                    return false;
//                }).mouseup(function (e) {
//                    var targetMap = Mapbender.modules[that.geomTarget];
//                    if (!targetMap) {
//                        return false;
//                    }
//                    box.stop(e, function (extent) {
//                        if (typeof extent === "undefined") {
//                            return false;
//                        }
//                        if (extent.constructor === Mapbender.Extent) {
//                            queryGeom = new Geometry(geomType.line);
//                            queryGeom.addPoint(extent.min);
//                            queryGeom.addPoint(extent.max);
//                            queryGeom.close();
//                            callback(that.geomTarget,queryGeom);
//
//                            $(el)
//                            .css("cursor", "default")
//                            .unbind("mousedown")
//                            .unbind("mouseup")
//                            .unbind("mousemove");
//                            box = null;
//
//                        }
//                    });
//                    return false;
//
//                });
            } else {
                box = new Mapbender.Box({
                    target: geomTarget
                });
                queryGeom = new Geometry(geomType.line);
                $(el).mousedown(function (e) {
                    box.start(e);
                    return false;
                }).mouseup(function (e) {
                    var targetMap = Mapbender.modules[that.geomTarget];
                    if (!targetMap) {
                        return false;
                    }
                    box.stop(e, function (extent) {
                        if (typeof extent === "undefined") {
                            return false;
                        }
                        if (extent.constructor === Mapbender.Extent) {
                            queryGeom = new Geometry(geomType.line);
                            queryGeom.addPoint(extent.min);
                            queryGeom.addPoint(extent.max);
                            queryGeom.close();
                            callback(that.geomTarget,queryGeom);

                            $(el)
                            .css("cursor", "default")
                            .unbind("mousedown")
                            .unbind("mouseup")
                            .unbind("mousemove");
                            box = null;

                        }
                    });
                    return false;

                });
            }

        }
        else if (queryType == "extent") {
            queryGeom = new Geometry(geomType.line);
            var ind = getMapObjIndexByName(target);
            var p0 = mapToReal(target, new Point(0,0));
            var p1 = mapToReal(target, new Point(mb_mapObj[ind].width,mb_mapObj[ind].height));
            queryGeom.addPoint(p0);
            queryGeom.addPoint(p1);
            callback(target, queryGeom);
            $(el).unbind("mousedown")
            .unbind("mouseover")
            .unbind("mouseup")
            .unbind("mousemove");

            queryGeom = null;
        }

    }

    var s;
    var callback;

    var wfsSpatialRequestStart = function(e){
        this.geomTarget = geomTarget;
        var that = this;
        var realWorldPos;
        if(isTouchable) {
            if (queryGeom.count() >= 3) {
            var pos = myMapObj.getMousePosition(e);
                s.check(pos);
            }
        }
        if (s.isSnapped() == true) {
            realWorldPos = s.getSnappedPoint();
            s.clean();
        }
        else {
            realWorldPos = mapToReal(that.geomTarget,myMapObj.getMousePosition(e));
        }
        queryGeom.addPoint(realWorldPos);

        if (queryGeom.count() == 1) {
            s.add(queryGeom.get(0));
        }
        if (s.isSnapped() && queryGeom.count() >= 3 && queryGeom.get(-1).equals(queryGeom.get(0))) {
            queryGeom.close();
            callback(that.geomTarget,queryGeom);
            writeTag(myMapObj.frameName, myMapObj.elementName+"_request_geometry_polygon", "");
            writeTag(myMapObj.frameName, myMapObj.elementName+"_measure_display", "");
            var ind = getMapObjIndexByName("mapframe1");
            var el = mb_mapObj[ind].getDomElement();
            $(el).unbind("mousedown")
            .unbind("mouseover")
            .unbind("mouseup")
            .unbind("mousemove");
            queryGeom = null;
            if ($.extend(myMapObj).defaultTouch) {
                $.extend(myMapObj).defaultTouch.activate();
            }
            return;
        }
        drawDashedLineExt();
    }

    var wfsSpatialRequestRun = function(e){
        this.geomTarget = geomTarget;
        var that = this;
        if (queryGeom.count() >= 3) {
            var pos = myMapObj.getMousePosition(e);
            s.check(pos);
        }
    }

    var drawDashedLineExt = function(e){
        this.geomTarget = geomTarget;
        var that = this;
        var ind = getMapObjIndexByName(that.geomTarget);
        var str_mPoints = "<div style='position:absolute;left:0px;top:0px' ><img src='"+mb_trans.src+"' width='"+mb_mapObj[ind].width+"' height='0'></div>";
        str_mPoints += "<div style='position:absolute;left:0px;top:0px' ><img src='"+mb_trans.src+"' width='0' height='"+mb_mapObj[ind].height+"'></div>";
        if (queryGeom != null) {
            for(var i=0; i<queryGeom.count(); i++){
                var pos = realToMap(that.geomTarget,queryGeom.get(i));
                str_mPoints += "<div style='font-size:1px;position:absolute;top:"+(pos.y-2)+"px;left:"+(pos.x-2)+"px;width:3px;height:3px;background-color:#ff0000'></div>";
            }
            if(queryGeom.count()>1){
                for(var k=1; k<queryGeom.count(); k++){
                    var pos0 = realToMap(that.geomTarget,queryGeom.get(k));
                    var pos1 = realToMap(that.geomTarget,queryGeom.get(k-1));
                    str_mPoints += evaluateDashesExt(pos1,pos0,k);
                }
            }
        }
        writeTag(myMapObj.frameName, myMapObj.elementName+"_request_geometry_polygon", str_mPoints);
    }

    var evaluateDashesExt = function(p1,p0,count){
        this.geomTarget = geomTarget;
        var that = this;
        var ind = getMapObjIndexByName(that.geomTarget);
        var str_dashedLine = "";
        var d = p0.dist(p1);
        var n = Math.round(d);
        var s =  p0.minus(p1).dividedBy(n);
        for(var i=1; i<n; i++){
            var currPoint = p1.plus(s.times(i)).minus(new Point(2,2)).round(0);
            if(currPoint.x >= 0 && currPoint.x <= mb_mapObj[ind].width && currPoint.y >= 0 && currPoint.y <= mb_mapObj[ind].height){
                str_dashedLine += "<div style='font-size:1px;position:absolute;top:"+currPoint.y+"px;left:"+currPoint.x+"px;width:3px;height:3px;background-color:#ff0000'></div>";
            }
        }
        return str_dashedLine;
    }

    var selAreaExtInit = function(e){
        mb_isBF = that.geomTarget;
        mb_zF = that.geomTarget;
    }

    var selAreaExtGet = function(e){
        selAreaExtSetValidClipping(mod_box_stop(e));
        mb_isBF = that.geomTarget;
        mb_zF = that.geomTarget;
    }

    var selAreaExtSetValidClipping = function(coords){
        this.geomTarget = geomTarget;
        var that = this;
        if (queryGeom != null) {
            queryGeom.addPoint(new Point(coords[0],coords[1]));
            queryGeom.addPoint(new Point(coords[2],coords[3]));

            if(queryGeom.count() == 2){
                callback(that.geomTarget,queryGeom);
                var ind = getMapObjIndexByName("mapframe1");
                var el = mb_mapObj[ind].getDomElement();
                $(el).unbind("mousedown")
                .unbind("mouseover")
                .unbind("mouseup")
                .unbind("mousemove");
                queryGeom = null;
            }
            else{
                callback(that.geomTarget,queryGeom);
            }
        }
    }

    function selAreaTouch(map, $map, mapDom, box){
        this.map = map;
        this.$elm = $map;
        this.elm = mapDom;
        this.box = box;
        this.startPos = null;
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
            box = null;
            touch.deactivate();
            if ($.extend(myMapObj).defaultTouch) {
                $.extend(myMapObj).defaultTouch.activate();
            }
            return touch.box.stop(
                function (extent) {
                    if (typeof extent === "undefined") {
                        return false;
                    }
                    if (extent.constructor === Mapbender.Extent) {
                        queryGeom = new Geometry(geomType.line);
                        queryGeom.addPoint(extent.min);
                        queryGeom.addPoint(extent.max);
                        queryGeom.close();
                        callback(that.geomTarget,queryGeom);
                    }
                }
            );
            touch.box = null;
            touch = null;
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
    }
}


