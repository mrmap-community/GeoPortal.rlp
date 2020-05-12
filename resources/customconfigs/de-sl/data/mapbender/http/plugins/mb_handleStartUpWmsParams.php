<?php
/**
 * Package: handleStartUpWmsParams
 *
 * Description:
 * Mapbender is initialized with params for visibility and queryability of layers
 * given as parameters visiblelayers and querylayers (comma-separated lists of layer names) or
 * visiblelayers_regexpr and querylayers_regexpr (regular expressions that match layer names)
 *
 * Files:
 *  - http/plugins/mb_handleStartUpWmsParams.php
 *
 * SQL:
 * > INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment,
 * > e_title, e_element, e_src, e_attributes, e_left, e_top, e_width,
 * > e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file,
 * > e_mb_mod, e_target, e_requires, e_url) VALUES('<appId>','handleStartUpWmsParams',
 * > 2,1,'handle wms params given in startup URL','handleStartUpWmsParams','div','','',1,1,2,2,5,'','',
 * > 'div','../plugins/mb_handleStartUpWmsParams.php','',
 * > 'mapframe1','','http://www.mapbender.org/index.php/HandleStartUpWmsParams');
 * >
 * > INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context,
 * > var_type) VALUES('<appId>', 'handleStartUpWmsParams', 'errormessages',
 * > 'log', 'log or alert if layer are not present, default is log' ,'var');
 *
 *
 * Help:
 * http://www.mapbender.org/HandleStartUpWmsParams
 *
 * Maintainer:
 * http://www.mapbender.org/User:Verena_Diewald
 *
 *
 * License:
 * Copyright (c) 2009, Open Source Geospatial Foundation
 * This program is dual licensed under the GNU General Public License
 * and Simplified BSD license.
 * http://svn.osgeo.org/mapbender/trunk/mapbender/license/license.txt
 */
require_once(dirname(__FILE__)."/../php/mb_validateSession.php");

$apiVisibleLayers = Mapbender::session()->get("visiblelayers");
$apiQueryLayers = Mapbender::session()->get("querylayers");

$apiDisableLayers = Mapbender::session()->get("disablelayers");
$apiDisableQueryLayers = Mapbender::session()->get("disablequerylayers");

$apiBackgroundWms = Mapbender::session()->get("backgroundwms");

$apiVisibleLayersRegExpr = Mapbender::session()->get("visiblelayers_regexpr");
$apiQueryLayersRegExpr = Mapbender::session()->get("querylayers_regexpr");

$apiDisableLayersRegExpr = Mapbender::session()->get("disablelayers_regexpr");
$apiDisableQueryLayersRegExpr = Mapbender::session()->get("disablequerylayers_regexpr");

echo "var apiVisibleLayers = '" . $apiVisibleLayers . "';";
echo "var apiVisibleLayerArray = apiVisibleLayers.split(',');";
echo "var apiQueryLayers = '" . $apiQueryLayers . "';";
echo "var apiQueryLayerArray = apiQueryLayers.split(',');";

echo "var apiDisableLayers = '" . $apiDisableLayers . "';";
echo "var apiDisableLayerArray = apiDisableLayers.split(',');";
echo "var apiDisableQueryLayers = '" . $apiDisableQueryLayers . "';";
echo "var apiDisableQueryLayerArray = apiDisableQueryLayers.split(',');";

echo "var apiBackgroundWms = '" . $apiBackgroundWms . "';";

echo "var checkForVisibleLayerArray = new Array();";
echo "var checkForQueryLayerArray = new Array();";

echo "var checkForDisableLayerArray = new Array();";
echo "var checkForDisableQueryLayerArray = new Array();";

echo "var apiVisibleLayersRegExpr = '" . $apiVisibleLayersRegExpr . "';";
echo "var apiQueryLayersRegExpr = '" . $apiQueryLayersRegExpr . "';";

echo "var apiDisableLayersRegExpr = '" . $apiDisableLayersRegExpr . "';";
echo "var apiDisableQueryLayersRegExpr = '" . $apiDisableQueryLayersRegExpr . "';";
?>

options.errormessages = typeof options.errormessages === "undefined" ? "log" : options.errormessages;

var errormessages = options.errormessages;

// set background WMS
if(typeof Mapbender.events.setBackgroundIsReady != "undefined") {
        Mapbender.events.setBackgroundIsReady.register(function () {
             if (apiBackgroundWms !== '') {
                        var newBackgroundWms = parseInt(apiBackgroundWms);
                        if (mod_setBackground_active !== false && wms[mod_setBackground_active]) {
                                wms[mod_setBackground_active].gui_wms_visible = 0;
                        }
                        wms[newBackgroundWms].gui_wms_visible = 2;
                        mod_setBackground_active = newBackgroundWms;
                        $("#setBackground > *").get(0).value = newBackgroundWms;
                        zoom(mod_setBackground_target, true, 1.0);
                }
        });
}
else {
        if (apiBackgroundWms !== '') {
                var newBackgroundWms = parseInt(apiBackgroundWms);
                if (mod_setBackground_active !== false && wms[mod_setBackground_active]) {
                        wms[mod_setBackground_active].gui_wms_visible = 0;
                }
                wms[newBackgroundWms].gui_wms_visible = 2;
                mod_setBackground_active = newBackgroundWms;
                $("#setBackground > *").get(0).value = newBackgroundWms;
                zoom(mod_setBackground_target, true, 1.0);
        }

}

// set queryability and visibility
eventAfterLoadWMS.register(function () {
	if(apiVisibleLayers != '' || apiQueryLayers != '' ||
		apiVisibleLayersRegExpr != '' || apiQueryLayersRegExpr != '' ||
		apiDisableLayers != '' || apiDisableQueryLayers != '' ||
		apiDisableLayersRegExpr != '' || apiDisableQueryLayersRegExpr != ''
		 ){

		var currentVisibleLayerMatchTrue = false;
		var currentQueryLayerMatchTrue = false;
		var currentDisableLayerMatchTrue = false;
		var currentDisableQueryLayerMatchTrue = false;
		var mapObject = Mapbender.modules[options.target[0]];
		var wmsArray = mapObject.wms;

		for (var i in wmsArray) {
			var currentWms = wmsArray[i];

			for (var j in currentWms.objLayer) {
				var currentLayer = currentWms.objLayer[j];

				// parameter apiDisableLayers
				for (var k in apiDisableLayerArray) {
					var disableLayer = apiDisableLayerArray[k];

					if (disableLayer == currentLayer.layer_name && currentLayer.gui_layer_status == '1' &&
						(currentLayer.gui_layer_selectable == 1 || currentLayer.gui_layer_visible == 1)
						) {
						currentLayer.gui_layer_visible = 0;
						checkForDisableLayerArray.push(currentLayer.layer_name);
					}
				}

				// parameter apiDisableQueryLayers
				for (var k in apiDisableQueryLayerArray) {
					var disableQueryLayer = apiDisableQueryLayerArray[k];

					if (disableQueryLayer == currentLayer.layer_name && currentLayer.gui_layer_queryable) {
						currentLayer.gui_layer_querylayer = 0;
						checkForDisableQueryLayerArray.push(currentLayer.layer_name);
					}
				}

				// parameter apiVisibleLayers
				for (var k in apiVisibleLayerArray) {
					var visibleLayer = apiVisibleLayerArray[k];

					if (visibleLayer == currentLayer.layer_name && currentLayer.gui_layer_status == '1' &&
						(currentLayer.gui_layer_selectable == 1 || currentLayer.gui_layer_visible == 1)
						) {
						currentLayer.gui_layer_visible = 1;
						checkForVisibleLayerArray.push(currentLayer.layer_name);
					}
				}

				// parameter apiQueryLayers
				for (var k in apiQueryLayerArray) {
					var queryLayer = apiQueryLayerArray[k];

					if (queryLayer == currentLayer.layer_name && currentLayer.gui_layer_queryable) {
						currentLayer.gui_layer_querylayer = 1;
						checkForQueryLayerArray.push(currentLayer.layer_name);
					}
				}

				// parameter apiDisableLayersRegExpr
				if(apiDisableLayersRegExpr !== "") {
					var pattern = new RegExp(apiDisableLayersRegExpr);
					var currentDisableLayerMatch = currentLayer.layer_name.match(pattern);

					if (currentDisableLayerMatch && currentLayer.gui_layer_status == '1' &&
						(currentLayer.gui_layer_selectable == 1 || currentLayer.gui_layer_visible == 1)
						) {
						currentLayer.gui_layer_visible = 0;
						currentDisableLayerMatchTrue = true;
					}
				}

				// parameter apiDisableQueryLayersRegExpr
				if (apiDisableQueryLayersRegExpr !== "") {
					var pattern = new RegExp(apiDisableQueryLayersRegExpr);
					var currentDisableQueryLayerMatch = currentLayer.layer_name.match(pattern);

					if (currentDisableQueryLayerMatch && currentLayer.gui_layer_queryable) {
						currentLayer.gui_layer_querylayer = 0;
						currentDisableQueryLayerMatchTrue = true;
					}
				}

				// parameter apiVisibleLayersRegExpr
				if(apiVisibleLayersRegExpr !== "") {
					var pattern = new RegExp(apiVisibleLayersRegExpr);
					var currentVisibleLayerMatch = currentLayer.layer_name.match(pattern);

					if (currentVisibleLayerMatch && currentLayer.gui_layer_status == '1' &&
						(currentLayer.gui_layer_selectable == 1 || currentLayer.gui_layer_visible == 1)
						) {
						currentLayer.gui_layer_visible = 1;
						currentVisibleLayerMatchTrue = true;
					}
				}

				// parameter apiQueryLayersRegExpr
				if (apiQueryLayersRegExpr !== "") {
					var pattern = new RegExp(apiQueryLayersRegExpr);
					var currentQueryLayerMatch = currentLayer.layer_name.match(pattern);

					if (currentQueryLayerMatch && currentLayer.gui_layer_queryable) {
						currentLayer.gui_layer_querylayer = 1;
						currentQueryLayerMatchTrue = true;
					}
				}

			}
			mb_restateLayers(options.target[0], currentWms.wms_id);
		}

		if(apiVisibleLayers !== "") {
			var visibleLayerNotFound = array_diff(apiVisibleLayerArray, checkForVisibleLayerArray);
			if(visibleLayerNotFound.length > 0) {
				var visibleLayerString = visibleLayerNotFound.join(",");
				var visibleLayerNotFoundMsg = "Visible layer " + visibleLayerString + " not found.";
				if(errormessages == 'alert'){
					alert(visibleLayerNotFoundMsg);
				}else{
					new Mapbender.Exception(visibleLayerNotFoundMsg);
				}
			}
		}

		if(apiQueryLayers !== "") {
			var queryLayerNotFound = array_diff(apiQueryLayerArray, checkForQueryLayerArray);
			if(queryLayerNotFound.length > 0) {
				var queryLayerString = queryLayerNotFound.join(",");
				var queryLayerNotFoundMsg = "Query layer " + queryLayerString + " not found.";
				if(errormessages == 'alert'){
					alert(queryLayerNotFoundMsg);
				}else{
					new Mapbender.Notice(queryLayerNotFoundMsg);
				}
			}
		}

		if (apiVisibleLayersRegExpr !== "") {
			if(currentVisibleLayerMatchTrue === false) {
				var visibleLayerNotFoundMsg = "No visible layer matches the given regular expression.";
				if(errormessages == 'alert'){
					alert(visibleLayerNotFoundMsg);
				}else{
					new Mapbender.Exception(visibleLayerNotFoundMsg);
				}
			}
		}

		if (apiQueryLayersRegExpr !== "") {
			if(currentQueryLayerMatchTrue === false) {
				var queryLayerNotFoundMsg = "No queryable layer matches the given regular expression.";
				if(errormessages == 'alert'){
					alert(queryLayerNotFoundMsg);
				}else{
					new Mapbender.Exception(queryLayerNotFoundMsg);
				}
			}
		}

		if(apiDisableLayers !== "") {
			var disableLayerNotFound = array_diff(apiDisableLayerArray, checkForDisableLayerArray);
			if(disableLayerNotFound.length > 0) {
				var disableLayerString = disableLayerNotFound.join(",");
				var disableLayerNotFoundMsg = "Disable Layer: Disable layer " + disableLayerString + " not found.";
				if(errormessages == 'alert'){
					alert(disableLayerNotFoundMsg);
				}else{
					new Mapbender.Exception(disableLayerNotFoundMsg);
				}
			}
		}

		if(apiDisableQueryLayers !== "") {
			var disableQueryLayerNotFound = array_diff(apiDisableQueryLayerArray, checkForDisableQueryLayerArray);
			if(disableQueryLayerNotFound.length > 0) {
				var disableQueryLayerString = disableQueryLayerNotFound.join(",");
				var disableQueryLayerNotFoundMsg = "Disable Query Layer: Query layer " + disableQueryLayerString + " not found.";
				if(errormessages == 'alert'){
					alert(disableQueryLayerNotFoundMsg);
				}else{
					new Mapbender.Exception(disableQueryLayerNotFoundMsg);
				}

			}
		}

		if (apiDisableLayersRegExpr !== "") {
			if(currentDisableLayerMatchTrue === false) {
				var disableLayerNotFoundMsg = "Disable Layer: No visible layer matches the given regular expression.";
				if(errormessages == 'alert'){
					alert(disableLayerNotFoundMsg);
				}else{
					new Mapbender.Exception(disableLayerNotFoundMsg);
				}
			}
		}

		if (apiDisableQueryLayersRegExpr !== "") {
			if(currentDisableQueryLayerMatchTrue === false) {
				var disableQueryLayerNotFoundMsg = "Disable Query Layer: No queryable layer matches the given regular expression.";
				if(errormessages == 'alert'){
					alert(disableQueryLayerNotFoundMsg);
				}else{
					new Mapbender.Exception(disableQueryLayerNotFoundMsg);
				}
			}
		}

	    eventAfterMapRequest.trigger({
	    	map: mapObject
	    });
	}
});

function array_diff(a1, a2) {
	var a=[], diff=[];
  	for(var i=0;i < a1.length;i++) {
  		a[a1[i]]=true;
  	}

  	for(var i=0;i < a2.length;i++) {
  		if(a[a2[i]]) {
  			delete a[a2[i]];
  		}
  		else {
  			a[a2[i]]=true;
  		}
  	}

  	for(var k in a) {
  		diff.push(k);
  	}
  	return diff;
}