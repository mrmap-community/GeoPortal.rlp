/**
 * Package: measure_widget
 *
 * Description:
 * Measure module with jQuery UI widget factory and RaphaelJS
 *
 * Files:
 *  - http/plugins/mb_measure_widget.js
 *  - http/widgets/w_measure.js
 *
 * SQL:
 * > INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, 
 * > e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, 
 * > e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, 
 * > e_requires, e_url) VALUES('<appId>','measure_widget',2,1,'Measure',
 * > 'Measure distance','img','../img/button_blue_red/measure_off.png','',
 * > NULL ,NULL ,NULL ,NULL ,1,'','','','../plugins/mb_measure_widget.php',
 * > '../widgets/w_measure.js,../extensions/RaphaelJS/raphael-1.4.7.min.js',
 * > 'mapframe1','jq_ui_dialog,jq_ui_widget','http://www.mapbender.org/index.php/Measure');
 * > 
 * > INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type)
 * > VALUES('<app_id>', 'measure_widget', 'lineStrokeDefault', '#808080', '' ,'var');
 * > INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type)
 * > VALUES('<app_id>', 'measure_widget', 'lineStrokeSnapped', '#F30', '' ,'var');
 * > INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type)
 * > VALUES('<app_id>', 'measure_widget', 'lineStrokeWidthDefault', '2', '' ,'var');
 * > INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type)
 * > VALUES('<app_id>', 'measure_widget', 'lineStrokeWidthSnapped', '2', '' ,'var');
 * > INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type)
 * > VALUES('<app_id>', 'measure_widget', 'measurePointDiameter', '7', '' ,'var');
 * > INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type)
 * > VALUES('<app_id>', 'measure_widget', 'opacity', '0.5', '' ,'var');
 * > INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type)
 * > VALUES('<app_id>', 'measure_widget', 'pointFillDefault', '#B2DFEE', '' ,'var');
 * > INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type)
 * > VALUES('<app_id>', 'measure_widget', 'pointFillSnapped', '#FF0000', '' ,'var');
 * > INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type)
 * > VALUES('<app_id>', 'measure_widget', 'pointStrokeDefault', '#FF0000', '' ,'var');
 * > INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type)
 * > VALUES('<app_id>', 'measure_widget', 'pointStrokeSnapped', '#FF0000', '' ,'var');
 * > INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type)
 * > VALUES('<app_id>', 'measure_widget', 'pointStrokeWidthDefault', '2', '' ,'var');
 * > INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type)
 * > VALUES('<app_id>', 'measure_widget', 'polygonFillDefault', '#B2DFEE', '' ,'var');
 * > INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type)
 * > VALUES('<app_id>', 'measure_widget', 'polygonFillSnapped', '#FC3', '' ,'var');
 * > INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type)
 * > VALUES('<app_id>', 'measure_widget', 'polygonStrokeWidthDefault', '1', '' ,'var');
 * > INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type)
 * > VALUES('<app_id>', 'measure_widget', 'polygonStrokeWidthSnapped', '3', '' ,'var');
 *
 * Help:
 * http://www.mapbender.org/Measure_widget
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

var $measure = $(this);

var MeasureApi = function (o) {

	var measureDialog,
		button,
		that = this,
		inProgress = false,
		title = o.title,
		defaultHtml = "<div title='" + title + "'>" +
			"<div class='mb-measure-text'><?php 
				echo nl2br(htmlentities(_mb("Click in the map to start measuring."), ENT_QUOTES, "UTF-8"));
			?></div></div>",
		informationHtml =
			"<div><?php
				echo nl2br(htmlentities(_mb("Last point: "), ENT_QUOTES, "UTF-8"));
			?><span class='mb-measure-clicked-point' /></div>" +
			"<div><?php
				echo nl2br(htmlentities(_mb("Current point: "), ENT_QUOTES, "UTF-8"));
			?><span class='mb-measure-current-point' /></div>" +
			"<div><?php
				echo nl2br(htmlentities(_mb("Distance (to last point): "), ENT_QUOTES, "UTF-8"));
			?><span class='mb-measure-distance-last' />  <span class='mb-measure-distance-last-unit' /></div>" +
			"<div><?php
				echo nl2br(htmlentities(_mb("Distance (total): "), ENT_QUOTES, "UTF-8"));
			?><span class='mb-measure-distance-total' /> <span class='mb-measure-distance-total-unit' /></div>" +
			"<div><?php
				echo nl2br(htmlentities(_mb("Perimeter: "), ENT_QUOTES, "UTF-8"));
			?><span class='mb-measure-perimeter' /> <span class='mb-measure-perimeter-unit' /></div>" +
			"<div><?php
				echo nl2br(htmlentities(_mb("Area: "), ENT_QUOTES, "UTF-8"));
			?><span class='mb-measure-area' /> <span class='mb-measure-area-unit' /></div>" +
                        "<div><?php
				echo nl2br(htmlentities(_mb("Angle: "), ENT_QUOTES, "UTF-8"));
			?><span class='mb-measure-angle' /> <span class='mb-measure-angle-unit' /></div>";

	var hideMeasureData = function () {
		measureDialog.find(".mb-measure-clicked-point").parent().hide();
		measureDialog.find(".mb-measure-current-point").parent().hide();
		measureDialog.find(".mb-measure-distance-last").parent().hide();
		measureDialog.find(".mb-measure-distance-total").parent().hide();
		measureDialog.find(".mb-measure-perimeter").parent().hide();
		measureDialog.find(".mb-measure-area").parent().hide();
                measureDialog.find(".mb-measure-angle").parent().hide();

	};

	var changeDialogContent = function () {
		measureDialog.html(informationHtml);
		hideMeasureData();

		o.$target.unbind("click", changeDialogContent);
	};

	var create = function () {
		//
		// Initialise measure dialog
		//
		measureDialog = $(defaultHtml);
		measureDialog.dialog({
			width: 182,
			height: 280,
			autoOpen: false,
			position: [o.$target.offset().left+760, o.$target.offset().top+285]
			//position: [o.$target.offset().left, o.$target.offset().top]
		}).bind("dialogclose", function () {
			button.stop();
			that.destroy();
		});

		//
		// Initialise button
		//
		button = new Mapbender.Button({
			domElement: $measure.get(0),
			over: o.src.replace(/_off/, "_over"),
			on: o.src.replace(/_off/, "_on"),
			off: o.src,
			name: o.id,
			go: that.activate,
			stop: that.deactivate
		});
	};

	var updateCurrentPoint = function (evt, data) {
		if (data.pos) {
			var p = data.pos;
			measureDialog.find(".mb-measure-current-point").text(
				p.pos.x + " " + p.pos.y
			).parent().show();
		}
	};

	var updateClickedPoint = function (evt, data) {
		if (data.pos) {
			var p = data.pos;
			measureDialog.find(".mb-measure-clicked-point").text(
				p.pos.x + " " + p.pos.y
			).parent().show();


                        var measuredX = $('input[name="measured_x_values"]').val();
                        if(measuredX != "") {
                            measuredX += ",";
		}
                        measuredX += p.pos.x;
                        $('input[name="measured_x_values"]').val(measuredX)

                        var measuredY = $('input[name="measured_y_values"]').val();
                        if(measuredY != "") {
                            measuredY += ",";
                        }
                        measuredY += p.pos.y;
                        $('input[name="measured_y_values"]').val(measuredY);
		}
	};

	var updateCurrentDistance = function (evt, data) {
		if (data.currentDistance) {
			var lastDistanceUnit = "m";
			var displayDistance = data.currentDistance;
			if (displayDistance > 10000){
				displayDistance /= 1000;
				lastDistanceUnit = "km";
			}
			measureDialog.find(".mb-measure-distance-last-unit").html(lastDistanceUnit);
			measureDialog.find(".mb-measure-distance-last").text(Math.round(displayDistance*10)/10).parent().show();
		}
	};

	var updateTotalDistance = function (evt, data) {
		if (data.totalDistance) {
			var totalDistanceUnit = "m";
			var displayTotalDistance = data.totalDistance;
			if (displayTotalDistance > 10000){
				displayTotalDistance = displayTotalDistance / 1000;
				totalDistanceUnit = "km";
			}
			measureDialog.find(".mb-measure-distance-total-unit").html(totalDistanceUnit);
			measureDialog.find(".mb-measure-distance-total").text(Math.round(displayTotalDistance*10)/10).parent().show();
		}
		else {
			measureDialog.find(".mb-measure-distance-total").parent().hide();
		}
	};

	var updatePerimeter = function (evt, data) {
		if (data.perimeter) {
			var unit = "m";
			var displayPerimeter = data.perimeter;
			if (displayPerimeter > 10000){
				displayPerimeter = displayPerimeter / 1000;
				unit = "km";
			}
			measureDialog.find(".mb-measure-perimeter-unit").html(unit);
			measureDialog.find(".mb-measure-perimeter").text(Math.round(displayPerimeter*10)/10).parent().show();

		}
		else {
			//measureDialog.find(".mb-measure-perimeter").parent().hide();
		}
	};

	var updateArea = function (evt, data) {
		if (data.area) {
			var areaUnit = "m&sup2;";
			var area = data.area;
			if (area > 10000000){
				area /= 1000000;
				areaUnit = "km&sup2;";
			}
			else if (area > 100000){
				area /= 10000;
				areaUnit = "ha";
			}
			area = Math.round(area*10)/10;

			measureDialog.find(".mb-measure-area-unit").html(areaUnit);
			measureDialog.find(".mb-measure-area").text(area).parent().show();
		}
		else {
			measureDialog.find(".mb-measure-area").parent().hide();
		}
	};

        var updateAngle = function (evt, data) {
        	if (data.currentAngle) {
			var unit = "°";
			var displayAngle = data.currentAngle;
                        measureDialog.find(".mb-measure-angle-unit").html(unit);
			measureDialog.find(".mb-measure-angle").text(Math.round(displayAngle*10)/10).parent().show();

		}
		else {
			//measureDialog.find(".mb-measure-angle").parent().hide();
		}
	};

	var updateView = function (evt, data) {
		updateCurrentPoint(evt, data);
		updateCurrentDistance(evt, data);
		updateTotalDistance(evt, data);
		updateArea(evt, data);
		updatePerimeter(evt, data);
                updateAngle(evt, data);
	};

	var finishMeasure = function () {
		inProgress = false;
		that.deactivate();
	};

	var reinitializeMeasure = function () {
		inProgress = false;
		that.deactivate();
		that.activate();
	};

	this.activate = function () {
                //remove measured x and y values from print dialog
                $('input[name="measured_x_values"]').val("");
                $('input[name="measured_y_values"]').val("");

		if (o.$target.size() > 0) {
			o.$target
				.mb_measure(o)
				.bind("mb_measureupdate", updateView)
				.bind("mb_measurepointadded", updateClickedPoint)
				.bind("mb_measurelastpointadded", finishMeasure)
				.bind("mb_measurereinitialize", reinitializeMeasure)
				.bind("click", changeDialogContent);
		}
		if (!inProgress) {
			inProgress = true;
			measureDialog.html(defaultHtml);
		}

		measureDialog.dialog("open");
	};

	this.destroy = function () {
		if (o.$target.size() > 0) {
			o.$target.mb_measure("destroy")
				.unbind("mb_measureupdate", updateView)
				.unbind("mb_measurepointadded", updateClickedPoint)
				.unbind("mb_measurelastpointadded", finishMeasure)
				.unbind("mb_measurereinitialize", reinitializeMeasure);
		}
		hideMeasureData();

		if (measureDialog.dialog("isOpen")) {
			measureDialog.dialog("close");
		}
		measureDialog.html(defaultHtml);

                //remove measured x and y values from print dialog
                $('input[name="measured_x_values"]').val("");
                $('input[name="measured_y_values"]').val("");
	};
	
	this.deactivate = function () {
		if (o.$target.size() > 0) {
			o.$target.mb_measure("deactivate");
		}
	};

	create();
};

$measure.mapbender(new MeasureApi(options));
