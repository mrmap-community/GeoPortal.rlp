/**
 * Package: selArea
 *
 * Description:
 * Zoom by rectangle
 *
 * Files:
 *  - http/javascripts/mod_selArea.js
 *
 * SQL:
 * > <SQL for element>
 * >
 * > <SQL for element var>
 *
 * Help:
 * http://www.mapbender.org/SelArea1
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

var that = this;


Mapbender.events.init.register(function () {
    var touch;
    var box;
    var map = Mapbender.modules[options.target];

    var mouseup = function(e) {
        box.stop(e, function (extent) {
            if (typeof extent === "undefined") {
                return false;
            }
            if (extent.constructor === Mapbender.Extent) {
                var xt = map.calculateExtent(extent);
                map.setMapRequest();
            }
            else if (extent.constructor === Mapbender.Point) {
                map.setCenter(extent);
                map.setMapRequest();
            }
        });
        return false;
    };

    var mousedown = function (e) {
        box.start(e);
        return false;
    };

    var isTouchable = false;
    try {
        document.createEvent("TouchEvent");
        isTouchable = true;
    } catch(e){}
    var button = new Mapbender.Button({
        domElement: that,
        over: options.src.replace(/_off/, "_over"),
        on: options.src.replace(/_off/, "_on"),
        off: options.src,
        name: options.id,
        go: function () {
            if (!map) {
                new Mb_exception(options.id + ": " +
                                 options.target + " is not a map!");
                return;
            }
            if(isTouchable) {
                $(map.getDomElement()).css("cursor", "crosshair");

                if ($.extend(map).defaultTouch) {
                    $.extend(map).defaultTouch.deactivate();
                }
                box = new Mapbender.BoxMobile({
                    target: options.target
                });
                touch = new selAreaTouch(map,$(map.getDomElement()), map.getDomElement(), box);
                touch.activate();
            } else {
                box = new Mapbender.Box({
                    target: options.target
                });
            }
            $(map.getDomElement()).css("cursor", "crosshair").mousedown(mousedown).mouseup(mouseup);
        },
        stop: function () {
            if (!map) {
                return;
            }
            if(isTouchable) {
                $(map.getDomElement()).css("cursor", "default");
                touch.deactivate();
                touch = null;
                box = null;
                if ($.extend(map).defaultTouch) {
                    $.extend(map).defaultTouch.activate();
                }
            } else {
                $(map.getDomElement())
                .css("cursor", "default")
                .unbind("mousedown", mousedown)
                .unbind("mouseup", mouseup);
                box = null;
            }
        }
    });
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
            return touch.box.stop(function (extent) {
                if (typeof extent === "undefined") {
                    return false;
                }
                if (extent.constructor === Mapbender.Extent) {
                    var xt = touch.map.calculateExtent(extent);
                    touch.map.setMapRequest();
                }
                else if (extent.constructor === Mapbender.Point) {
                    touch.map.setCenter(extent);
                    touch.map.setMapRequest();
                }
            });
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
});