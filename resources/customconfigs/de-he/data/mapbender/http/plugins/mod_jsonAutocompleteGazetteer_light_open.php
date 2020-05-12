<?php
/**
 * Package: mod_jsonAutocompleteGazetteer
 *
 * Description:
 * This module is a client for a json gazetteer webservice as used by http://www.geonames.org. The default 
 * service is the service from geonames.org which allows searching for 
 * 
 * 
 * Files:
 *  - http/plugins/mod_jsonAutocompleteGazetteer.php
 *
 * SQL:
 * >
 * > INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src,
 * > e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag,
 * > e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('<app_id>','jq_ui_autocomplete',5,1,
 * > 'Module to manage jQuery UI autocomplete module','','div','','',-1,-1,15,15,NULL ,'','','div',
 * > '','../extensions/jquery-ui-1.8.16.custom/development-bundle/ui/jquery.ui.autocomplete.js',
 * > '','jq_ui,jq_ui_widget,jq_ui_position','');
 * >
 * > INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,
 * > e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content,
 * > e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('<app_id>',
 * > 'jsonAutocompleteGazetteer',12,1,'Client for json webservices like geonames.org','Gazetteer',
 * > 'div','','',230,30,NULL ,NULL ,999,'','','div','../plugins/mod_jsonAutocompleteGazetteer.php',
 * > '','mapframe1','','http://www.mapbender.org/index.php/mod_jsonAutocompleteGazetteer');
 * >
 * > INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) 
 * > VALUES('<app_id>', 'jsonAutocompleteGazetteer', 'gazetteerUrl', 'http://10.176.178.10/mapbender/geoportal/gaz_geom_mobile.php',
 * >  '' ,'var');
 * > 
 * > INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type)
 * >  VALUES('<app_id>', 'jsonAutocompleteGazetteer', 'isGeonames', 'true', '' ,'var');
 * > 
 * Help:
 * http://www.mapbender.org/mod_jsonAutocompleteGazetteer
 *
 * Maintainer:
 * http://www.mapbender.org/User:Armin_Retterath
 * 
 * Parameters:
 * none
 * 
 *
 * License:
 * Copyright (c) 2009, Open Source Geospatial Foundation
 * This program is dual licensed under the GNU General Public License 
 * and Simplified BSD license.  
 * http://svn.osgeo.org/mapbender/trunk/mapbender/license/license.txt
 */

require_once(dirname(__FILE__)."/../php/mb_validateSession.php");

?>

var standingHighlight = null;
Mapbender.events.afterMapRequest.register( function(){
	if(standingHighlight){
		standingHighlight.paint();
	}
});

//initialize modul
if (options.gazetteerUrl === undefined) {
	options.gazetteerUrl = 'http://ws.geonames.org/searchJSON?lang=de&';
}
if (options.isGeonames === undefined) {
	options.isGeonames = true;
}
else {
	options.isGeonames = false;
}
if (options.latLonZoomExtension === undefined) {
	options.latLonZoomExtension = 0.1;
}
if (options.minLength === undefined) {
	options.minLength = 3;
}
if (options.delay === undefined) {
	options.delay = 400;
}
if (options.isDraggable === undefined) {
	options.isDraggable = true;
}
if (options.maxResults === undefined) {
	options.maxResults = 15;
}
if (options.inputWidth === undefined) {
	options.inputWidth = 250;
}
if (options.searchEpsg === undefined) {
	options.searchEpsg = "4326";
}
if (options.drawCentrePoint === undefined) {
	options.drawCentrePoint = true;
}
if (options.gazetteerFrontImageOn === undefined) {
	options.gazetteerFrontImageOn = "../img/button_hessen/search_on.png";
}
if (options.gazetteerFrontImageOff === undefined) {
	options.gazetteerFrontImageOff = "../img/button_hessen/search_off.png";
}
if (options.helpText === undefined) {
	options.helpText = "";
}

var JsonAutocompleteGazetteer = function() {
	var that = this;	
	var targetName = options.target;
	var ind = getMapObjIndexByName(targetName);
	var my = mb_mapObj[ind];

	this.zoomToExtent = function(fromSrs,minx,miny,maxx,maxy) {
		var parameters = {
			fromSrs: fromSrs,
			toSrs: Mapbender.modules[targetName].epsg 
		};

		parameters.bbox = parseFloat(minx)+ "," +parseFloat(miny)+ "," +parseFloat(maxx)+ "," +parseFloat(maxy);
			
		//function to transform from one crs to another
		var req = new Mapbender.Ajax.Request({
			url: "../php/mod_coordsLookup_server.php",
			method: "transform",
			parameters: parameters,
			callback: function (obj, success, message) {
				if (!success) {
					new Mapbender.Exception(message);
					return;
				}
				if (options.drawCentrePoint) {
					//generate layer for visualization of point
					if(standingHighlight !== null){ 
						standingHighlight.clean();
					}else{
						standingHighlight = new Highlight(
							[options.target],
							"standingHighlight", 
							{"position":"absolute", "top":"0px", "left":"0px", "z-index":999}, 
							2);
					}
					var point0 = new Point(obj.points[0].x,obj.points[0].y);
					var point1 = new Point(obj.points[1].x,obj.points[1].y);	
					var x = point0.x + (point1.x - point0.x)/2;
					var y = point0.y + (point1.y - point0.y)/2;
					var point = new Point(x,y);
					var ga = new GeometryArray();
					ga.importPoint({
						coordinates:[x,y,null]
					},Mapbender.modules[targetName].epsg)
					var m = ga.get(-1,-1);
					standingHighlight.add(m, "#ff0000");
					//alert(m);
					standingHighlight.paint();
				}
				if (obj.points) {
					if (obj.points.length === 2) {
						var newExtent = new Extent(
							obj.points[0].x,
							obj.points[0].y,
							obj.points[1].x,
							obj.points[1].y
						);
						my.calculateExtent(newExtent);
					}
					my.setMapRequest();
				}
			} 
		});
		req.send();
	};
	this.showSearchHelp = function(){


	}
	this.toggleInput = function(){
		if ($("#geographicName").css("display") == 'none') {
			$("#geographicName").show();
			$("#helpSymbolId").show();
			$("#symboldForInputId").attr({'src':options.gazetteerFrontImageOn});
		} else {
			$("#geographicName").hide();
			$("#helpSymbolId").hide();
			$("#symboldForInputId").attr({'src':options.gazetteerFrontImageOff});
		}
	}

	this.initForm = function() {
		epsg = Mapbender.modules[targetName].epsg.replace('EPSG:', '');
		this.formContainer = $(document.createElement('form')).attr({'id':'json-autocomplete-gazetteer'}).appendTo('#' + options.id);
		this.formContainer.submit(function() {
			return false;
		});
		if (options.isDraggable){
			//this.formContainer.draggable();//problem with print module
		}
		this.symbolForInput = $(document.createElement('img')).appendTo(this.formContainer);
		this.symbolForInput.attr({'id':'symboldForInputId'});
		this.symbolForInput.attr({'src':options.gazetteerFrontImageOn});
		this.symbolForInput.attr({'title':'<?php echo _mb('Address'); ?>'});
		this.symbolForInput.attr('style','float:right;cursor:pointer;width:28px;height:29px:');
		$("#symboldForInputId").click(function() {
			that.toggleInput();
		});
		this.inputAddress = $(document.createElement('input')).appendTo(this.formContainer);
		
		//do the following things only if 
		if (options.helpText != '') {
			this.helpSymbol = $(document.createElement('img')).appendTo(this.formContainer);
			this.helpSymbol.attr('style','display: def !important');
			this.helpText = $(document.createElement('div')).appendTo(this.formContainer);
			this.helpText.attr({'id':'helpTextId'});
			$("#helpTextId").hide();
			$("#helpTextId").append(options.helpText);
			
			this.helpSymbol.attr({'id':'helpSymbolId'});
			this.helpSymbol.attr({'src':'../img/questionmark.png'});
			this.helpSymbol.attr({'width':'22'});
                        this.helpSymbol.attr({'height':'22'});
                        this.helpSymbol.attr({'style':'margin:0 5px -6px 2px'});
		
			$("#helpSymbolId").hover(
				function () {
    					//create dialog
					$("#helpTextId").dialog({ title: "<?php echo _mb('Help for address search'); ?>" });
  				},
				function () {
    					//create dialog
					$("#helpTextId").dialog('close');
  				}
			);
		}
		this.inputAddress.attr({'id':'geographicName'});
		
		//INSERTED BY BENZ----
		this.inputAddress.attr({'placeholder':'<?php echo _mb('Search for addresses'); ?>'});
		//-------------------
		//default value
		//this.inputAddress.val('<?php echo _mb('Search for addresses'); ?>');
		/*this.inputAddress.click(function() {
			that.inputAddress.val('');
		});*/
		
		this.inputAddress.css('width',options.inputWidth);
		$('.ui-menu').css('width','100px');
		$('.ui-menu-item').css('width','100px');
		//set the loading symbol for autoloader class
		//$('.ui-autocomplete-loading').css('background','white url("../img/indicator_wheel.gif") right center no-repeat');
		//http://stackoverflow.com/questions/622122/how-can-i-change-the-css-class-rules-using-jquery
		//$("<style type='text/css'> .ui-autocomplete { position: absolute; cursor: default; background:black; color:white} </style>").appendTo("head");
		$(function() {
			$( "#geographicName" ).autocomplete({
				source: function( request, response ) {
					$.ajax({
						url: options.gazetteerUrl,
						dataType: "jsonp",
						data: {
							outputFormat: 'json',
							resultTarget: 'web',
							searchEPSG: options.searchEpsg,
							maxResults: options.maxResults,
							maxRows: options.maxResults,
							searchText: request.term,
							featureClass: "P",
							style: "full",
							name_startsWith: request.term

						},
						success: function( data ) {
							if (options.isGeonames) {
								response( $.map( data.geonames, function( item ) {
									return {
										label: item.name+" - "+item.fclName+" - "+item.countryName,
										minx: item.lng-options.latLonZoomExtension,
										miny: item.lat-options.latLonZoomExtension,
										maxx: item.lng+options.latLonZoomExtension,
										maxy: item.lat+options.latLonZoomExtension
									}
								}));
							} else {
								response( $.map( data.geonames, function( item ) {
									return {
										label: item.title,
										minx: item.minx,
										miny: item.miny,
										maxx: item.maxx,
										maxy: item.maxy
									}
								}));
							}
						}
					});
				},
				minLength: options.minLength,
				delay: options.delay,
				select: function( event, ui ) {
					that.zoomToExtent("EPSG:"+options.searchEpsg,ui.item.minx,ui.item.miny,ui.item.maxx,ui.item.maxy);
				},
				open: function() {
					$( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
				},
				close: function() {
					$( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
				}
			});
		});
	}
	this.initForm();
}
Mapbender.events.init.register(function() {
	Mapbender.modules[options.id] = $.extend(new JsonAutocompleteGazetteer(),Mapbender.modules[options.id]);	
});





