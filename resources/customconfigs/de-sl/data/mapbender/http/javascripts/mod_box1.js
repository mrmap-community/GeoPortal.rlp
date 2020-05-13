/*
 * License:
 * Copyright (c) 2009, Open Source Geospatial Foundation
 * This program is dual licensed under the GNU General Public License
 * and Simplified BSD license.
 * http://svn.osgeo.org/mapbender/trunk/mapbender/license/license.txt
 */

Mapbender.Box = function (options) {
	var map = (typeof options.target !== "undefined") ?
		getMapObjByName(options.target) : null;

	var color = (typeof options.color !== "undefined") ?
		options.color : "#ff0000";

	var isActive = false;
	var top = 0;
	var topSuffix = "_l_top";
	var topNode;
	var left = 0;
	var leftSuffix = "_l_left";
	var leftNode;
	var bottom = 0;
	var bottomSuffix = "_l_bottom";
	var bottomNode;
	var right = 0;
	var rightSuffix = "_l_right";
	var rightNode;

	var startPos = null;
	var stopPos = null;

	var exists = false;
	var that = this;

    var mousemove = function(e) {
	that.run(e);
	return false;
    };

	this.start = function (e) {
		if (!map) {
			return;
		}
		$(map.getDomElement()).mousemove(mousemove);

		isActive = true;
		var click = map.getMousePosition(e);
		startPos = new Point(click.x, click.y);
		stopPos = new Point(click.x, click.y);
		top = click.y;
		left = click.y;
		bottom = click.y;
		right = click.x;

	};

	this.run = function (e) {
		var pos = map.getMousePosition(e);
		if (pos !== null) {
			stopPos = pos;
			var width = map.width;
			var height = map.height;

			if (startPos.x > stopPos.x) {
				right = startPos.x;
				left = stopPos.x;
			}
			else {
				left = startPos.x;
				right = stopPos.x;
			}
			if (startPos.y > stopPos.y) {
				bottom = startPos.y;
				top = stopPos.y;
			}
			else {
				top = startPos.y;
				bottom = stopPos.y;
			}

			if (!startPos.equals(stopPos)) {
				this.draw();
			}
		}
		return true;
	};

	this.stop = function (e, callback) {
		$(map.getDomElement()).unbind("mousemove", mousemove);

		hideElement(topNode);
		hideElement(leftNode);
		hideElement(rightNode);
		hideElement(bottomNode);


		if (isActive) {
			isActive = false;
			if (typeof callback === "function") {
				return callback(getExtent());
			}
			return getExtent();
		}
		isActive = false;
	};

	var arrangeBox = function (node, left, top, right, bottom) {
		var el = node.style;
		el.height = Math.abs(bottom - top) + "px";
		el.width = Math.abs(right - left) + "px";
		el.top = top + "px";
		el.left = left + "px";
	};

	var displayElement = function (node) {
		node.style.visibility = "visible";
	};

	var hideElement = function (node) {
		node.style.visibility = "hidden";
	};

	var domElementsExist = function () {
		if (!map) {
			return;
		}
		return map.getDomElement(
			).ownerDocument.getElementById(
			map.elementName + topSuffix
			) !== null;
	};

	var createDomElements = function () {
		if (!map) {
			return;
		}
		var map_el = map.getDomElement();
		topNode = map_el.ownerDocument.createElement("div");
		topNode.style.position = "absolute";
		topNode.style.top = "0px";
		topNode.style.left = "0px";
		topNode.style.width = "0px";
		topNode.style.height = "0px";
		topNode.style.overflow = "hidden";
//		topNode.style.zIndex = parseInt(map_el.style.zIndex, 10) + 1;
		topNode.style.zIndex = 100;
		topNode.style.visibility = "visible";
		topNode.style.cursor = "crosshair";
		topNode.style.backgroundColor = color;

		leftNode = topNode.cloneNode(false);
		rightNode = topNode.cloneNode(false);
		bottomNode = topNode.cloneNode(false);

		topNode.id = map.elementName + topSuffix;
		leftNode.id = map.elementName + leftSuffix;
		rightNode.id = map.elementName + rightSuffix;
		bottomNode.id = map.elementName + bottomSuffix;

		map_el.appendChild(topNode);
		map_el.appendChild(leftNode);
		map_el.appendChild(rightNode);
		map_el.appendChild(bottomNode);

		exists = true;
	};

	this.draw = function (extent) {
		if (!map) {
			return;
		}
		if (typeof extent === "object"
			&& extent.constructor === Mapbender.Extent) {

			left = extent.min.x;
			right = extent.max.x;
			bottom = extent.min.y;
			top = extent.max.y;
		}

		arrangeBox(topNode, left, top, right, top + 2);
		arrangeBox(leftNode, left, top, left + 2, bottom);
		arrangeBox(rightNode, right - 2, top, right, bottom);
		arrangeBox(bottomNode, left, bottom - 2, right, bottom);
		displayElement(topNode);
		displayElement(leftNode);
		displayElement(rightNode);
		displayElement(bottomNode);
	};

	var getExtent = function () {
		if (!map) {
			return null;
		}
		var x1 = startPos.x;
		var x2 = stopPos.x;
		var y1 = startPos.y;
		var y2 = stopPos.y;

		var minx = x2;
		var maxx = x1;
		if(x1 < x2){
			minx = x1;
			maxx = x2;
		}

		var miny = y1;
		var maxy = y2;
		if(y1 < y2){
			miny = y2;
			maxy = y1;
		}

		// area or clickpoint ?
		var posMin = map.convertPixelToReal(new Point(minx,miny));
		if((maxx - minx) > 3 && (miny - maxy) > 3){
			var posMax = map.convertPixelToReal(new Point(maxx,maxy));
			return new Mapbender.Extent(posMin, posMax);
		}
		return posMin;
	};

	if (!domElementsExist()) {
		createDomElements();
	}
	else {
		topNode = document.getElementById(map.elementName + topSuffix);
		leftNode = document.getElementById(map.elementName + leftSuffix);
		rightNode = document.getElementById(map.elementName + rightSuffix);
		bottomNode = document.getElementById(map.elementName + bottomSuffix);
	}
};

Mapbender.BoxMobile = function (options) {
    var map = (typeof options.target !== "undefined") ?
    getMapObjByName(options.target) : null;

    var color = (typeof options.color !== "undefined") ?
    options.color : "#ff0000";

    var isActive = false;
    var top = 0;
    var topSuffix = "_l_top";
    var topNode;
    var left = 0;
    var leftSuffix = "_l_left";
    var leftNode;
    var bottom = 0;
    var bottomSuffix = "_l_bottom";
    var bottomNode;
    var right = 0;
    var rightSuffix = "_l_right";
    var rightNode;

    var startPos = null;
    var stopPos = null;

    var exists = false;
    var that = this;

    this.start = function (startPosition, stopPosition) {
        startPos = startPosition;
        stopPos = stopPosition;
        isActive = true;
        top = startPos.y;
        left = startPos.y;
        bottom = startPos.y;
        right = startPos.x;
        return true;
    };

    this.run = function (startPosition, stopPosition) {
        if (startPosition !== null && stopPosition != null) {
            startPos = startPosition
            stopPos = stopPosition;
            var width = map.width;
            var height = map.height;

            if (startPos.x > stopPos.x) {
                right = startPos.x;
                left = stopPos.x;
            }
            else {
                left = startPos.x;
                right = stopPos.x;
            }
            if (startPos.y > stopPos.y) {
                bottom = startPos.y;
                top = stopPos.y;
            }
            else {
                top = startPos.y;
                bottom = stopPos.y;
            }

            if (!startPos.equals(stopPos)) {
                this.draw();
            }
        }
        return true;
    };

    this.stop = function (callback) {
        hideElement(topNode);
        hideElement(leftNode);
        hideElement(rightNode);
        hideElement(bottomNode);

        if (isActive) {
            isActive = false;
            if (typeof callback === "function") {
                return callback(getExtent());
            }
            return getExtent();
        }
        isActive = false;
    };

    var arrangeBox = function (node, left, top, right, bottom) {
        var el = node.style;
        el.height = Math.abs(bottom - top) + "px";
        el.width = Math.abs(right - left) + "px";
        el.top = top + "px";
        el.left = left + "px";
    };

    var displayElement = function (node) {
        node.style.visibility = "visible";
    };

    var hideElement = function (node) {
        node.style.visibility = "hidden";
    };

    var domElementsExist = function () {
        if (!map) {
            return;
        }
        return map.getDomElement(
            ).ownerDocument.getElementById(
            map.elementName + topSuffix
            ) !== null;
    };

    var createDomElements = function () {
        if (!map) {
            return;
        }
        var map_el = map.getDomElement();
        topNode = map_el.ownerDocument.createElement("div");
        topNode.style.position = "absolute";
        topNode.style.top = "0px";
        topNode.style.left = "0px";
        topNode.style.width = "0px";
        topNode.style.height = "0px";
        topNode.style.overflow = "hidden";
        //		topNode.style.zIndex = parseInt(map_el.style.zIndex, 10) + 1;
        topNode.style.zIndex = 100;
        topNode.style.visibility = "visible";
        topNode.style.cursor = "crosshair";
        topNode.style.backgroundColor = color;

        leftNode = topNode.cloneNode(false);
        rightNode = topNode.cloneNode(false);
        bottomNode = topNode.cloneNode(false);

        topNode.id = map.elementName + topSuffix;
        leftNode.id = map.elementName + leftSuffix;
        rightNode.id = map.elementName + rightSuffix;
        bottomNode.id = map.elementName + bottomSuffix;

        map_el.appendChild(topNode);
        map_el.appendChild(leftNode);
        map_el.appendChild(rightNode);
        map_el.appendChild(bottomNode);

        exists = true;
    };

    this.draw = function (extent) {
        if (!map) {
            return;
        }
        if (typeof extent === "object"
            && extent.constructor === Mapbender.Extent) {

            left = extent.min.x;
            right = extent.max.x;
            bottom = extent.min.y;
            top = extent.max.y;
        }

        arrangeBox(topNode, left, top, right, top + 2);
        arrangeBox(leftNode, left, top, left + 2, bottom);
        arrangeBox(rightNode, right - 2, top, right, bottom);
        arrangeBox(bottomNode, left, bottom - 2, right, bottom);
        displayElement(topNode);
        displayElement(leftNode);
        displayElement(rightNode);
        displayElement(bottomNode);
    };

    var getExtent = function () {
        if (!map) {
            return null;
        }
        var x1 = startPos.x;
        var x2 = stopPos.x;
        var y1 = startPos.y;
        var y2 = stopPos.y;

        var minx = x2;
        var maxx = x1;
        if(x1 < x2){
            minx = x1;
            maxx = x2;
        }

        var miny = y1;
        var maxy = y2;
        if(y1 < y2){
            miny = y2;
            maxy = y1;
        }

        // area or clickpoint ?
        var posMin = map.convertPixelToReal(new Point(minx,miny));
        if((maxx - minx) > 3 && (miny - maxy) > 3){
            var posMax = map.convertPixelToReal(new Point(maxx,maxy));
            return new Mapbender.Extent(posMin, posMax);
        }
        return posMin;
    };

    if (!domElementsExist()) {
        createDomElements();
    }
    else {
        topNode = document.getElementById(map.elementName + topSuffix);
        leftNode = document.getElementById(map.elementName + leftSuffix);
        rightNode = document.getElementById(map.elementName + rightSuffix);
        bottomNode = document.getElementById(map.elementName + bottomSuffix);
    }
};

