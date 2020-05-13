//var defaultTouch = null;

eventInit.register(function () {
    try {
        var map = Mapbender.modules[options.target];
        var defaultTouch = new MapTouch(map, $(map.getDomElement()), map.getDomElement());
        defaultTouch.activate();
        $.extend(map, {defaultTouch: defaultTouch});
        return true;
    } catch (e) {
        return false;
    }
});
var defaultTouch;
function MapTouch(map, $map, mapDom){
    this.map = map;
    this.$elm = $map;
    this.elm = mapDom;

    this.started = false;
    this.startPos = 0, this.stopPos = 0;
    this.timestamp = 0;
    this.dblclick = false;
    this.move = false;
    this.pinch = false;
    this.pinchObj = null;
    this.activ = false;
    defaultTouch = this;

    this.activate = function() {
        this.elm.addEventListener("touchstart", defaultTouch.startTouch, true);
        this.elm.addEventListener("touchmove", defaultTouch.moveTouch, true);
        this.elm.addEventListener("touchend", defaultTouch.endTouch, true);
        this.activ = true;
    };
    this.activateTouchstart = function() {
        this.elm.addEventListener("touchstart", defaultTouch.startTouch, true);
    };
    this.activateTouchmove = function() {
        this.elm.addEventListener("touchmove", defaultTouch.moveTouch, true);
    };
    this.activateTouchend = function() {
        this.elm.addEventListener("touchend", defaultTouch.endTouch, true);
    };
    this.setActivate = function() {
        this.activ = true;
    };


    this.deactivate = function() {
        this.elm.removeEventListener("touchstart", defaultTouch.startTouch, true);
        this.elm.removeEventListener("touchmove", defaultTouch.moveTouch, true);
        this.elm.removeEventListener("touchend", defaultTouch.endTouch, true);
        this.activ = false;
    };
    this.deactivateTouchstart = function() {
        this.elm.removeEventListener("touchstart", defaultTouch.startTouch, true);
    };
    this.deactivateTouchmove = function() {
        this.elm.removeEventListener("touchmove", defaultTouch.moveTouch, true);
    };
    this.deactivateTouchend = function() {
        this.elm.removeEventListener("touchend", defaultTouch.endTouch, true);
    };
    this.setDeactivate = function() {
        this.activ = true;
    };

    this.isActiv = function() {
        return this.activ;
    }
//
//    this.registerObject = function(obj) {
//        this.obj = obj;
//        alert(this.obj);
//    };

    this.startTouch = function(event) {
        event.preventDefault();
        var elm = findElement(event, "");
        if (isSingleTouch(event)) {
            defaultTouch.startPos = new Mapbender.Point(
                    event.touches[0].pageX - defaultTouch.$elm.offset().left,
                    event.touches[0].pageY - defaultTouch.$elm.offset().top);
            defaultTouch.stopPos = new Point(defaultTouch.startPos.x, defaultTouch.startPos.y);
            var timestamp = new Date().getTime();
            if (timestamp - defaultTouch.timestamp < 300) {
                defaultTouch.dblclick = true;
            }
            defaultTouch.timestamp = timestamp;
        } else if (isMultiTouch(event)) {
            defaultTouch.dblclick = false;
            defaultTouch.move = false;
            defaultTouch.pinch = true;
            defaultTouch.pinchObj = new Pinch(defaultTouch);
            defaultTouch.pinchObj.pinchStart(event);
        }
        return true;
    };
    this.moveTouch = function(event) {
        event.preventDefault();
        var elm = findElement(event, "");
        if (isSingleTouch(event)) {
            defaultTouch.moveMapMove(event);
            defaultTouch.move = true;
        } else if(isMultiTouch(event)) {
            defaultTouch.pinchObj.pinchRun(event);
        }
        return true;
    };
    this.endTouch = function(event) {
        event.preventDefault();
        var elm = findElement(event, "");
        if (defaultTouch.dblclick) {
            defaultTouch.zoomSingle(event);
            defaultTouch.dblclick = false;
        } else {
            if (defaultTouch.move) {
                defaultTouch.moveMapStop(event);
                defaultTouch.move = false;
            } else if (defaultTouch.pinch){
                defaultTouch.pinchObj.pinchStop(event);
                defaultTouch.pinch = false;
            }
        }
        return true;
    };

    this.moveMapMove = function(event){
        this.stopPos = new Mapbender.Point(
                event.touches[0].pageX - this.$elm.offset().left,
                event.touches[0].pageY - this.$elm.offset().top);
        var dif = this.stopPos.minus(this.startPos);
        this.map.moveMap(dif.x, dif.y);
    };

    this.moveMapStop = function(event){
        var dif = this.stopPos.minus(this.startPos);
        var mapCenter = new Mapbender.Point(
                parseInt(parseInt(this.map.getWidth()) / 2),
                parseInt(parseInt(this.map.getHeight()) / 2)
        );
        var center = mapCenter.minus(dif);
        var realCenter = this.map.convertPixelToReal(center);
        this.map.moveMap(dif.x, dif.y);
        this.map.zoom(false, 1.0, realCenter);
        this.startPos = null;
        this.stopPos = null;
    };

    this.zoomSingle = function(event){
        var pos = this.map.convertPixelToReal(this.stopPos);
        var extentAfterZoom = this.map.calculateExtentAfterZoom(
                true,
                2.0,
                pos.x,
                pos.y
        );
        var newPos = this.map.convertRealToPixel(
                pos,
                extentAfterZoom
        );
        var diff = newPos.minus(this.stopPos);

        var newSouthEast = this.map.convertPixelToReal(
                (new Point(0, this.map.getHeight())).plus(diff),
                extentAfterZoom
        );
        var newNorthWest = this.map.convertPixelToReal(
                (new Point(this.map.getWidth(), 0)).plus(diff),
                extentAfterZoom
        );
        var newExtent = new Mapbender.Extent(newSouthEast, newNorthWest);
        this.map.setExtent(newExtent);
        this.map.setMapRequest();
    };

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

    function Pinch(mmtouch){
        this.mmtouch = mmtouch;
        this.scale = 1;

        this.pinchStart = function(event) {
            this.pinchCenter = new Mapbender.Point(
                        parseInt((event.touches[0].pageX - this.mmtouch.$elm.offset().left +
                         event.touches[1].pageX - this.mmtouch.$elm.offset().left) / 2),
                        parseInt((event.touches[0].pageY - this.mmtouch.$elm.offset().top +
                         event.touches[1].pageY - this.mmtouch.$elm.offset().top) / 2));

            this.pos0 = new Mapbender.Point(
                        event.touches[0].pageX - this.mmtouch.$elm.offset().left,
                        event.touches[0].pageY - this.mmtouch.$elm.offset().top);
            this.pos1 = new Mapbender.Point(
                        event.touches[1].pageX - this.mmtouch.$elm.offset().left,
                        event.touches[1].pageY - this.mmtouch.$elm.offset().top);
            this.startDist = Math.sqrt(
                        Math.pow(this.pos0.x - this.pos1.x, 2) +
                        Math.pow(this.pos0.y - this.pos1.y, 2));
        }

        this.pinchRun = function(event) {

            this.lastPinchCenter = new Mapbender.Point(
                        parseInt((event.touches[0].pageX - this.mmtouch.$elm.offset().left +
                         event.touches[1].pageX - this.mmtouch.$elm.offset().left) / 2),
                        parseInt((event.touches[0].pageY - this.mmtouch.$elm.offset().top +
                         event.touches[1].pageY - this.mmtouch.$elm.offset().top) / 2));
    //        var dif = this.lastCenter.minus(this.center);
            var pos0 = new Mapbender.Point(
                        event.touches[0].pageX - this.mmtouch.$elm.offset().left,
                        event.touches[0].pageY - this.mmtouch.$elm.offset().top);
            var pos1 = new Mapbender.Point(
                        event.touches[1].pageX - this.mmtouch.$elm.offset().left,
                        event.touches[1].pageY - this.mmtouch.$elm.offset().top);
            var dist = Math.sqrt(
                        Math.pow(pos0.x - pos1.x, 2) +
                        Math.pow(pos0.y - pos1.y, 2));
            this.scale = dist / this.startDist;

            var index = this.mmtouch.map.history.getCurrentIndex();
            var width = parseInt(parseInt(this.mmtouch.$elm.css("width")) * this.scale);
            var height = parseInt(parseInt(this.mmtouch.$elm.css("height")) * this.scale);
            var leftAt = parseInt(this.lastPinchCenter.x * this.scale);
            var topAt = parseInt(this.lastPinchCenter.y * this.scale);
            var left = this.lastPinchCenter.x - leftAt;
            var top = this.lastPinchCenter.y - topAt;
            $("#" + this.mmtouch.map.elementName + "_request_" + (index) + " div img").css({
                    position: "absolute",
                    width: width + "px",
                    height: height + "px",
                    left: left + "px",
                    top: top + "px"
            });
        }
        this.pinchStop = function(event) {
            var pos = this.mmtouch.map.convertPixelToReal(this.lastPinchCenter);
//            var dist = this.startDist * this.scale;
//            var isZoomIn = false;
//            if (this.startDist - dist < 0){
//                isZoomIn = true;
//            }
            var extentAfterZoom = this.mmtouch.map.calculateExtentAfterZoom(
                    true,
                    this.scale,
                    pos.x,
                    pos.y
            );
            var newPos = this.mmtouch.map.convertRealToPixel(
                    pos,
                    extentAfterZoom
            );

            var diff = newPos.minus(this.lastPinchCenter);

            var newSouthEast = this.mmtouch.map.convertPixelToReal(
                    (new Point(0, this.mmtouch.map.getHeight())).plus(diff),
                    extentAfterZoom
            );
            var newNorthWest = this.mmtouch.map.convertPixelToReal(
                    (new Point(this.mmtouch.map.getWidth(), 0)).plus(diff),
                    extentAfterZoom
            );
            var newExtent = new Mapbender.Extent(newSouthEast, newNorthWest);
            this.mmtouch.map.setExtent(newExtent);
            this.mmtouch.map.setMapRequest();
        }
    }
}