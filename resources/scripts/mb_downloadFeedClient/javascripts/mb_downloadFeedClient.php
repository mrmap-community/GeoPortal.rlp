<?php
require_once dirname(__FILE__) . "/../../conf/atomFeedClient.conf";
//require_once dirname(__FILE__) . "/../../tools/wms_extent/extent_service.conf";
require_once dirname(__FILE__) . "/../../core/globalSettings.php";
?>

var mapframe_dataset_list, mapframe_file_list, datasetSelect, file_list, bboxDataset, bboxFiles, formats, sf;
var tiles = [];
var DlSet = {};
DlSet.urls = [];
DlSet.names = [];

function updateFormats() {
            var in_options = {
                'internalProjection': mapframe_dataset_list.baseLayer.projection,
                'externalProjection': new OpenLayers.Projection("EPSG:4326")
            };
            var out_options = {
                'internalProjection': mapframe_dataset_list.baseLayer.projection,
                'externalProjection': new OpenLayers.Projection("EPSG:900913")
            };
            var gmlOptions = {
                featureType: "feature",
                featureNS: "http://example.com/feature"
            };
            var gmlOptionsIn = OpenLayers.Util.extend(
                OpenLayers.Util.extend({}, gmlOptions),
                in_options
            );
            var gmlOptionsOut = OpenLayers.Util.extend(
                OpenLayers.Util.extend({}, gmlOptions),
                out_options
            );
            var kmlOptionsIn = OpenLayers.Util.extend(
                {extractStyles: true}, in_options);
            formats = {
              'in': {
                wkt: new OpenLayers.Format.WKT(in_options),
                geojson: new OpenLayers.Format.GeoJSON(in_options),
                georss: new OpenLayers.Format.GeoRSS(in_options),
                gml2: new OpenLayers.Format.GML.v2(gmlOptionsIn),
                gml3: new OpenLayers.Format.GML.v3(gmlOptionsIn),
                kml: new OpenLayers.Format.KML(kmlOptionsIn),
                atom: new OpenLayers.Format.Atom(in_options),
                gpx: new OpenLayers.Format.GPX(in_options)
              },
              'out': {
                wkt: new OpenLayers.Format.WKT(out_options),
                geojson: new OpenLayers.Format.GeoJSON(out_options),
                georss: new OpenLayers.Format.GeoRSS(out_options),
                gml2: new OpenLayers.Format.GML.v2(gmlOptionsOut),
                gml3: new OpenLayers.Format.GML.v3(gmlOptionsOut),
                kml: new OpenLayers.Format.KML(out_options),
                atom: new OpenLayers.Format.Atom(out_options),
                gpx: new OpenLayers.Format.GPX(out_options)
              }
	};
}

function init(){
	//generate 2 Mapframes
	//add active class to dataset info before showing map, afterwords remove it!
	$('#dataset_info').toggleClass('active');
	mapframe_dataset_list = new OpenLayers.Map('mapframe_dataset_list');
	$('#dataset_info').toggleClass('active');
	$('#representations').toggleClass('active');
	mapframe_file_list = new OpenLayers.Map('mapframe_file_list');
	$('#representations').toggleClass('active');
	<?php echo "\n".$backgroundLayer_1."\n"?>
	<?php echo "\n".$backgroundLayer_2."\n"?>
	//var wms_osm = new OpenLayers.Layer.WMS( "OpenLayers WMS",
        //        "http://osm.omniscale.net/proxy/service?", {layers: 'osm'}, {singleTile: true});
	//var wms_osm = new OpenLayers.Layer.WMS( "OpenLayers WMS",
        //        "http://osm.omniscale.net/proxy/service?", {layers: 'osm'}, {singleTile: true});
	<?php echo "\n".$backgroundLayer_3."\n"?>
	<?php echo "\n".$backgroundLayer_4."\n"?>
	//Vector layer for the georss polygons of the service feed - maybe more than one
	bboxDataset = new OpenLayers.Layer.Vector("Dataset bounding boxes");
	//Vector layer for the georss polygons of one single data feed entry - maybe more than one if the dataset is tiled into different sections



	bboxFiles = new OpenLayers.Layer.Vector("File bounding boxes");
	mapframe_dataset_list.addLayers([<?php echo $addBackgroundLayerUpper; ?>]);
	mapframe_dataset_list.addLayers([bboxDataset]);
	mapframe_dataset_list.addControl(new OpenLayers.Control.MousePosition());
	mapframe_dataset_list.addControl(new OpenLayers.Control.PanPanel());
	mapframe_file_list.addLayers([<?php echo $addBackgroundLayerLower; ?>]);
	mapframe_file_list.addLayers([bboxFiles]);
	mapframe_file_list.addControl(new OpenLayers.Control.MousePosition());
	mapframe_file_list.addControl(new OpenLayers.Control.PanPanel());

	updateFormats();

  /*
* generate gazetteer search form
*/
var options = {
  id: "search_field",
  inputWidth: 300,
  searchEpsg: "4326",
  maxResults: 15,
  gazetteerUrl: "https://" + location.hostname + "/mapbender/geoportal/gaz_geom_mobile.php?",
  isGeonames: false,
  minLength: 3,
  delay: 3,
  drawCentrePoint: true,
  latLonZoomExtension: 0.1,
  zIndex: 100,
  gazetteerFrontImageOn: "../img/button_blue_red/gazetteer3_on.png"
}

var formContainer = $(document.createElement('form')).attr({'id':'json-autocomplete-gazetteer'}).appendTo('#' + options.id);
formContainer.submit(function() {
  return false;
});
if (options.isDraggable){
  //formContainer.draggable();//problem with print module
}
var symbolForInput = $(document.createElement('img')).appendTo(formContainer);
symbolForInput.attr({'id':'symboldForInputId'});
symbolForInput.attr({'src':options.gazetteerFrontImageOn});
symbolForInput.attr({'title':'<?php echo "Geographic names";?>'});
/*$("#symboldForInputId").click(function() {
  that.toggleInput();
});*/
var inputAddress = $(document.createElement('input')).appendTo(formContainer);
inputAddress.attr({'id':'geographicName'});
//default value
inputAddress.val('Search for addresses');
inputAddress.click(function() {
  inputAddress.val('');
});
inputAddress.css('width',options.inputWidth);

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
      //that.zoomToExtent("EPSG:"+options.searchEpsg,ui.item.minx,ui.item.miny,ui.item.maxx,ui.item.maxy);
      var bounds= new OpenLayers.Bounds(ui.item.minx,ui.item.miny,ui.item.maxx,ui.item.maxy);
              mapframe_file_list.zoomToExtent(bounds);
    },
    open: function() {
      $( "#search_field" ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
      //set zindex of ui-autocomplete to high value to show text above map widget
      $('.ui-autocomplete').css('z-index', 99999999999999);
    },
    close: function() {
      $( "#search_field" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );

    }
  });
});
/*
* end of search form
*/



	try {
    		var django = document.getElementById("django").getAttribute("value");
	}
	catch(err) {
  		var django = false;
	}

    if (document.getElementById("user_id").getAttribute("value") == 2 || django != "true"){

      sf = new OpenLayers.Control.SelectFeature(bboxFiles);
      mapframe_file_list.addControl(sf);
      sf.activate();
    }else{
      var b1 = new OpenLayers.Control.Button({
        trigger: function() { sf.multi.deactivate();sf.square.activate();},
        displayClass: "square"
      });

      var b2 = new OpenLayers.Control.Button({
        trigger: function() { sf.multi.activate();sf.square.deactivate();},
        displayClass: "navigate"
      });

      var vpanel = new OpenLayers.Control.TextButtonPanel({
          //vertical: true,
          additionalClass: "vpanel"
      });
      vpanel.addControls([
          b1,b2
      ]);

      mapframe_file_list.addControl(vpanel);

      sf = {
    square: new OpenLayers.Control.SelectFeature(
        [bboxFiles],
        {
            clickout: false, toggle: false,
            multiple: false, hover: false,
            toggleKey: "ctrlKey", // ctrl key removes from selection
            multipleKey: "shiftKey", // shift key adds to selection
            onBeforeSelect: function(e) {
              //this should pretend the selection of mor than x features when using the async download option ;-), put $maxTiles = x; in atomfeedclient.conf
		    if (e.layer.selectedFeatures.length >= <?php if (isset($maxTiles)){ echo $maxTiles;}else{echo 20;} ?>) {
              	    //alert("Only <?php echo $maxTiles?> allowed per download");
              return false;
              }
              //console.log(e);

            },
            box: true
        }
    ),
    multi: new OpenLayers.Control.SelectFeature(
        [bboxFiles],
        {
          clickout: true, toggle: false,
          multiple: false, hover: false,
          toggleKey: "ctrlKey", // ctrl key removes from selection
          multipleKey: "shiftKey"//, // shift key adds to selection
        }
    )
};
mapframe_file_list.addControl(sf.multi);
mapframe_file_list.addControl(sf.square);
sf.multi.activate();

    }

	//initialize mapframes
	mapframe_dataset_list.setCenter(new OpenLayers.LonLat(<?php echo $olCentreLon; ?>, <?php echo $olCentreLat; ?>), <?php echo $olScale; ?>);
	mapframe_file_list.setCenter(new OpenLayers.LonLat(<?php echo $olCentreLon; ?>, <?php echo $olCentreLat; ?>), <?php echo $olScale; ?>);
	resetForm();
	//start parsing when no empty string was found in input for url
	if ($('#download_feed_url').val() != "") {
		resetForm();
		method =  "getServiceFeedObjectFromUrl";
        	data = $("#download_feed_url").val();
		//call server by ajax function
		callServer(data,method);
	}
	//initialize button for load service feed - the first feed will be parsed.
	$(document).ready(function(e) {
    		$('#download_feed_button').click(function() {
			resetForm();
			method =  "getServiceFeedObjectFromUrl";
        		data = $("#download_feed_url").val();
			//call server by ajax function
			callServer(data,method);
    		});
		$('.example_service_feed').click(function() {
			//alert('click');
			resetForm();
			method =  "getServiceFeedObjectFromUrl";
        		data = $( this ).attr('value');
			//call server by ajax function
			callServer(data,method);
    		});
		$('#stop_parsing').click(function () {
  			alert("test");
		});
	});
	//don't show elements of dataset feed
	//
}

//central function to call server by ajax, result is a featureCollection which is generated by mapbenders server component
function callServer(data,method,id) {
	if (id === undefined) id = 0;
	//alert("ajax call begin");
	if (method == "getServiceFeedObjectFromUrl") {
		//$("#parse_service_feed_modal").toggle();
		$("#loading_image_service").css("display","block");
	}
	if (method == "getDatasetFeedObjectFromUrl") {
		//$("#parse_dataset_feed_modal").toggle();
		$("#loading_image_dataset").css("display","block");
	}
	$.ajax({
  		url: '../plugins/mb_downloadFeedServer.php',
  		type: "post",
		async: false, //cause reading the first feed may take longer than the second
		data: {url: data, method: method , id: id},
       		dataType: "json",
  		success: function(result) {
			if (method == "getServiceFeedObjectFromUrl") {
				//hide modal
				//$("#parse_dataset_feed_modal").toggle();
				$("#loading_image_service").css("display","none");
				//select tab
				$('#mytabs a[href="#dataset_info"]').tab('show');
				//draw georss polygons from service feed
				drawMetadataPolygons(result);
				//show datasets in a dropdown list
				showDatasetList(result);
			}
			if (method == "getDatasetFeedObjectFromUrl") {
				//function to draw bboxes of single link to data tile and the corresponding list of tiles
				//alert("ajax datasetfeed read!");
				//hide modal
				//$("#parse_service_feed_modal").toggle();
				$("#loading_image_dataset").css("display","none");
				//select tab
				//$('#mytabs a[href="#representations"]').tab('show');
				showDatasetEntryList(result, id);

			}

 		}
	});
	return false;
}

function resetForm() {
	//reset form
	//don't show elements of dataset feed
	$("#representation_select").css("display","none");
	$("#representation_info").css("display","none");
	$("#dataset_information").css("display","none");
	$("#capabilities_hybrid").css("display","none");
	$("#label_capabilities_hybrid").css("display","none");
	$("#label_dataset_select").css("display","none");
	//change size of outer fieldset
	$("#client").css("height","450px");
	$("#download_link_list").remove();
	$("#download_link").remove();
	//$('#dataset_select').remove();
	$("#tab_header_number_datasets").text("");
	$("#tab_header_number_representations").text("");
	$("#number_of_tiles").text("");
	$('.selectpicker').selectpicker('destroy');
	//$('.dropdown-toggle').dropdown()

}

function showDatasetList(featureCollection,id) {
	resetForm();

	if (id === undefined) id = 0;
	if (featureCollection == null) {
		alert("No parseable content found");
		return false;
	}
	//remove old dataset select option
	$('#dataset_select').remove();
	//delete identifier anchor
	$('#dataset_identifier_link').remove();
	//delete capabilities anchor
	$('#capabilities_link').remove();
	//delete old entries
	var datasetSelect =  $(document.createElement('select')).appendTo('#dataset_list');
	datasetSelect.attr({'id':'dataset_select'});
	var selectOptions = "";
	//iterate over all single features which can be identified with the entries of the inspire service feed
	for(var i=0; i<featureCollection.features.length; ++i) {
		//fill in first title, rights and abstract in fields
		if (i == id) {
			$('#dataset_title').empty();
			$('#dataset_title').append(featureCollection.features[i].properties.title);
			$('#dataset_rights').empty();
			$('#dataset_rights').append(featureCollection.features[i].properties.rights);
			$('#dataset_abstract').empty();
			$('#dataset_abstract').append(featureCollection.features[i].properties.summary);
			//add capabilities anchor
			identifierAnchor =  $(document.createElement('a')).appendTo('#capabilities_hybrid');
			identifierAnchor.attr({'id':'capabilities_link'});
			identifierAnchor.attr({'target':'_blank'});
			identifierAnchor.attr({'href':featureCollection.features[i].properties.capabilitiesLink});
			identifierAnchor.text(featureCollection.features[i].properties.capabilitiesLink);
			//show hybrid link only if it is there
			if (typeof featureCollection.features[i].properties.capabilitiesLink !== 'undefined' && featureCollection.features[i].properties.capabilitiesLink !== '' && featureCollection.features[i].properties.capabilitiesLink !== null) {
				$("#capabilities_hybrid").css("display","block");
				$("#label_capabilities_hybrid").css("display","block");
			}
			//add identifier anchor
			identifierAnchor =  $(document.createElement('a')).appendTo('#dataset_identifier');
			identifierAnchor.attr({'id':'dataset_identifier_link'});
			identifierAnchor.attr({'uuid':featureCollection.features[i].properties.code});
			identifierAnchor.attr({'href':'../php/mod_iso19139ToHtml.php?url='+encodeURIComponent(featureCollection.features[i].properties.metadataLink)});
			identifierAnchor.attr({'target':'_blank'});
			identifierAnchor.text(featureCollection.features[i].properties.namespace+"#"+featureCollection.features[i].properties.code);
		}
		selectOptions = selectOptions+"<option value='"+featureCollection.features[i].properties.datasetFeedLink+"' url='"+featureCollection.features[i].properties.datasetFeedLink+"' optionid='"+i+"'>"+featureCollection.features[i].properties.title+"</option>";

	}
	//add number of datasets to tab header
	$("#tab_header_number_datasets").text(" "+"("+featureCollection.features.length+")");
	datasetSelect.append(selectOptions);
	//following has to be enabled for boootstrap selectboxes
	datasetSelect.addClass('selectpicker');
	datasetSelect.attr({'data-style':'btn-primary'});
	datasetSelect.attr({'data-width':'100%'});
	datasetSelect.attr({'data-live-search':'true'});
	$("#dataset_information").css("display","block");
	$("#label_dataset_select").css("display","block");
	//preselect option
	$("#dataset_select option[optionid='" + id + "']").attr("selected","selected");
	$('#dataset_select').bind('change', function() {
		var $this = $(this);
		optionSelected = $(this).find('option:selected').attr('optionid');
		//alert(optionSelected);
		showDatasetList(featureCollection,optionSelected);
		//zoom viewer to extent and highlight vector of current extent
		drawMetadataPolygon(featureCollection.features[optionSelected]);
		method =  "getDatasetFeedObjectFromUrl";
        	data = $this.val();
		//resetForm();
		callServer(data,method);

	});
	method =  "getDatasetFeedObjectFromUrl";
	//call second feed with first entry for default
        data = featureCollection.features[0].properties.datasetFeedLink;
	$('.selectpicker').selectpicker('refresh');
	datasetFeedObject = callServer(data,method);
}

function showDatasetEntryList(featureCollection, id) {
	if (id === undefined) id = 0;
	if (featureCollection == null) {
		alert("No parseable content found");
		return false;
	}
	//remove old select element for the different possible representations (formats, crs, ...)
	$('#dataset_representation_list').empty();
	//generate new select element
	var datasetEntrySelect =  $(document.createElement('select')).appendTo('#dataset_representation_list');
	datasetEntrySelect.attr({'id':'dataset_representation_select'});
	//initialize options
	var selectROptions = "";
	//iterate over all possible representations, which are modeled as entries in the dataset feed (here features of the featureCollection)
	for(var i=0; i<featureCollection.features.length; ++i) {
		selectROptions = selectROptions+"<option value='"+i+"' url='"+featureCollection.features[i].properties.datasetFeedLink+"'>"+featureCollection.features[i].properties.title+"</option>";
	}
	$("#tab_header_number_representations").text(" "+"("+featureCollection.features.length+")");
	datasetEntrySelect.append(selectROptions);
	//following has to be enabled for boootstrap selectboxes
	datasetEntrySelect.addClass('selectpicker');
	datasetEntrySelect.attr({'data-style':'btn-primary'});
	datasetEntrySelect.attr({'data-width':'100%'});
	datasetEntrySelect.selectpicker('refresh');
	fillSectionList(featureCollection, id);

	$('#dataset_representation_select').bind('change', function() {
		var $this = $(this);
    DlSet.urls = [];
    DlSet.names = [];
		fillSectionList(featureCollection,$this.val());

	});
}

function fillSectionList(featureCollection, k) {
    //console.log(featureCollection)
    bboxFiles.removeAllFeatures();
		$('#section_option').remove();
		//initialize option string
		var selectFOptions = "";
		//count number of links in representation
		var numberOfLinks = featureCollection.features[k].properties.link.length;
		if (numberOfLinks >= 1 || numberOfLinks === undefined) {
			//show list
			$("#representation_select").css("display","block");
			//deactivate mapframe2 by default
			$("#mapframe_file_list").css("display","none");
			//$("#multi_select").css("display","none");
			$("#representation_info").css("display","block");
			//extent size of outer fieldset
			$("#client").css("height","730px");
		} else {
			alert("No links to datasets or parts of them found in feed!");
		}
		$("#download_link_list").remove();
		downloadLinkList = $(document.createElement('ul')).appendTo('#section_list');
		downloadLinkList.attr({'id':'download_link_list'});
    for (var i = 0; i < numberOfLinks; i++) {
			if (featureCollection.features[k].properties.link[i]['@attributes'].bbox == '' || featureCollection.features[k].properties.link[i]['@attributes'].bbox === undefined) {
				//show simple link foreach part
				//$("#download_link").remove();
				//show Downloadlink
				if (featureCollection.features[k].properties.link[i]['@attributes'].length == '' || featureCollection.features[k].properties.link[i]['@attributes'].length === undefined) {
					downloadLink = "<li><a target='_blank' value='download_link_"+i+"' href='"+featureCollection.features[k].properties.link[i]['@attributes'].href+"' title='"+featureCollection.features[k].properties.link[i]['@attributes'].title+"'>"+featureCollection.features[k].properties.link[i]['@attributes'].title+"</li>";
				} else {
					downloadLink = "<li><a target='_blank' value='download_link_"+i+"' href='"+featureCollection.features[k].properties.link[i]['@attributes'].href+"' title='"+featureCollection.features[k].properties.link[i]['@attributes'].title+"'>"+featureCollection.features[k].properties.link[i]['@attributes'].title+" - (~"+featureCollection.features[k].properties.link[i]['@attributes'].length / 1000000 +" MB)"+"</li>";
				}
				//append link
				$('#download_link_list').append(downloadLink);
				//selectFOptions = selectFOptions+"<option value='"+i+"' url='"+featureCollection.features[k].properties.link[i]['@attributes'].href+"' title='"+featureCollection.features[k].properties.link[i]['@attributes'].title+"'  onclick='window.open(\""+featureCollection.features[k].properties.link[i]['@attributes'].href+"\");'>"+featureCollection.features[k].properties.link[i]['@attributes'].title+"</option>";
			} else {
        ext = featureCollection.features[k].properties.link[i]['@attributes'].bbox;
				extArrayNew = new Array();
				extArray = ext.split(" ");
				//sort array to lat lon
				extArrayNew[0] = extArray[1];
				extArrayNew[1] = extArray[0];
				extArrayNew[2] = extArray[3];
				extArrayNew[3] = extArray[2];
                		bound = OpenLayers.Bounds.fromArray(extArrayNew);
				attributes = {id: i, url:featureCollection.features[k].properties.link[i]['@attributes'].href, title: featureCollection.features[k].properties.link[i]['@attributes'].title};
                		box = new OpenLayers.Feature.Vector(bound.toGeometry(),attributes);
                		bboxFiles.addFeatures(box);
				//selectFOptions = selectFOptions+"<option value='"+i+"' url='"+featureCollection.features[k].properties.link[i]['@attributes'].href+"' title='"+featureCollection.features[k].properties.link[i]['@attributes'].title+"' onclick='highlightFeatureIndexById("+i+",true);' onmouseover='highlightFeatureIndexById("+i+",false);'>"+featureCollection.features[k].properties.link[i]['@attributes'].title+"</option>";
			}

               	}
		//set number of links:
		$("#number_of_tiles").text(" "+"("+numberOfLinks+"<?php echo " tile(s)"?>"+")");

		if (bboxFiles.features.length >= 1) {//TODO
			//show mapframe
			$("#mapframe_file_list").css("display","block");
			//extent size of outer fieldset
			$("#client").css("height","900px");
			bound = bboxFiles.getDataExtent();
			//Add wms layer from mb_metadata table (bounding_geom column)
			<?php echo "\n".$metadataPolygonLayer."\n"?>
			//mapframe_file_list.addLayers([wms222]);
			//Add polygon layer from mb_metadata tables bounding_geom column
			mapframe_file_list.zoomToExtent(bound);
			//set number of tiles
			bboxFiles.features.length
      //console.log(bboxFiles.features);
			bboxFiles.events.on({
				"featureselected": function(event){
          var selected_format = document.getElementsByClassName("filter-option pull-left").item(1).innerHTML;
          selected_format = encodeURIComponent(selected_format.split(" ").splice(-1))

					//console.log(event)
          //console.log(selected_format)
        	var feature = event.feature;
        	var id = feature.attributes.id;
					var url_tmp = feature.attributes.url;

          // decode uri as long as its encoded
          // had problemes with already encoded urls
          if(url_tmp != decodeURIComponent(url_tmp)){
            url = decodeURIComponent(url_tmp)
            while(url != url_tmp)
               url_tmp = url;
               url = decodeURIComponent(url_tmp)
          }else{
              url=url_tmp
          }

          //console.log(encodeURIComponent(url))
          //console.log(url)
          //console.log(feature.attributes.title)
          if(encodeURIComponent(url).includes(selected_format)){
            //DlSet.urls.push(btoa(encodeURIComponent(url)));
            //DlSet.names.push(btoa(feature.attributes.title));
            DlSet.urls.push(encodeURIComponent(url));
            DlSet.names.push(feature.attributes.title);
          }

      					$("#download_link").remove();

                // make enties unique, actually a bad workaround because
                // openlayers click events are x times the selection
                DlSet.urls = DlSet.urls.filter((v, i, a) => a.indexOf(v) === i);
                DlSet.names = DlSet.names.filter((v, i, a) => a.indexOf(v) === i);
                      					//show Downloadlink
					//console.log(DlSet.urls.length)
		//create link do django download servie if more than one tile selected
		//if (document.getElementById("user_id").getAttribute("value") == 2){

                if(DlSet.urls.length > 1){

                    downloadLink = $(document.createElement('a')).appendTo('#section_list');
    		            downloadLink.attr({'onclick':"sendtodjango()"});
                    downloadLink.attr({'target':'_blank'});
                    downloadLink.attr({'id':'download_link'});
    		            downloadLink.text("<?php echo _mb("Start download, a zip file will be mailed to you!");?>");

                }else{
          					downloadLink = $(document.createElement('a')).appendTo('#section_list');
          					downloadLink.attr({'href':url});
          					downloadLink.attr({'target':'_blank'});
          					downloadLink.attr({'id':'download_link'});
      					    if (feature.attributes.length == '' || feature.attributes.length === undefined) {
      						          downloadLink.text(feature.attributes.title);
      					    } else {
      						          downloadLink.text(feature.attributes.title+" - (~"+feature.attributes.length / 1000000+" MB)");
      					    }
               }

              //console.log(DlSet.urls);
        },
          "featureunselected": function(event) {
                var feature = event.feature;
                var id = feature.attributes.id;
                var url_tmp = feature.attributes.url;

                if(url_tmp != decodeURIComponent(url_tmp)){
                  url = decodeURIComponent(url_tmp)
                  while(url != url_tmp)
                     url_tmp = url;
                     url = decodeURIComponent(url_tmp)
                }else{
                    url=url_tmp
                }


		            //DlSet.names = arrayRemove(DlSet.names, btoa(feature.attributes.title));
                //DlSet.urls = arrayRemove(DlSet.urls, btoa(encodeURIComponent(url)));
                DlSet.names = arrayRemove(DlSet.names, feature.attributes.title);
                DlSet.urls = arrayRemove(DlSet.urls, encodeURIComponent(url));
                //console.log(encodeURIComponent(url));
                //console.log(DlSet.urls.length);
          }
			});
		}
}

function sendtodjango(){

  DlSet.user_id = document.getElementById("user_id").getAttribute("value");
  DlSet.user_email = document.getElementById("user_email").getAttribute("value");
  DlSet.user_name = document.getElementById("user_name").getAttribute("value");
  DlSet.session_id = document.getElementById("session_id").getAttribute("value");
  DlSet.uuid = uuidv4();
  DlSet.timestamp = Date.now();
  DlSet.scriptname = window.location.pathname.substring(window.location.pathname.lastIndexOf('/')+1);
  DlSet.lang = navigator.language || navigator.userLanguage;
  host = location.protocol.concat("//").concat(window.location.hostname);

  var DlJSON = {user_id: DlSet.user_id, user_name:DlSet.user_name , session_id:DlSet.session_id , user_email: DlSet.user_email, uuid: DlSet.uuid, timestamp: DlSet.timestamp, scriptname: DlSet.scriptname, names:DlSet.names, urls:DlSet.urls, lang:DlSet.lang};
  url = host.concat("/manage/download")
  data = JSON.stringify(DlJSON)
  //console.log(data)

  $.ajax({
  type: "POST",
  url: url,
  data: data,
  statusCode: {
    200: function(responseObject, textStatus, jqXHR) {
      	alert("<?php echo _mb("Download finished!");?>")
    },
    403: function(responseObject, textStatus, jqXHR) {
      	alert("<?php echo _mb("Unauthorized!");?>")
    },
    400: function(responseObject, textStatus, jqXHR) {
      	alert("<?php echo _mb("No space left, please try again later!!");?>")
    },
    409: function(responseObject, textStatus, jqXHR) {
     	alert("<?php echo _mb("Maximun 20 tiles allowed!");?>")
    },
    418: function(responseObject, textStatus, jqXHR) {
     	alert("<?php echo _mb("Host not in whitelist, please contact an Administrator!");?>")
    },
    500: function(responseObject, textStatus, jqXHR) {
     	alert("<?php echo _mb("Something went wrong, please contact an Administrator!");?>")
    }
}});

sf.square.unselectAll();
alert("<?php echo _mb("Download started, email will be send to ");?>".concat(DlSet.user_email))
}


function arrayRemove(arr, value) {
  return arr.filter(function(ele){
  return ele != value;
});
}

function uuidv4() {
  return ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c =>
    (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
  )
}

function drawMetadataPolygons(featureCollection) {
	 var type = "geojson";
         var features = formats['in'][type].read(featureCollection);
         var bounds;
         if(features) {
             if(features.constructor != Array) {
                 features = [features];
             }
             for(var i=0; i<features.length; ++i) {
                 if (!bounds) {
                     bounds = features[i].geometry.getBounds();
                 } else {
                     bounds.extend(features[i].geometry.getBounds());
                 }
             }
	     //delete old features:
       bboxFiles.removeAllFeatures();
	     bboxDataset.removeAllFeatures();
             bboxDataset.addFeatures(features);
             mapframe_dataset_list.zoomToExtent(bounds);
	}
}

function drawMetadataPolygon(feature) {
	 var type = "geojson";
         var features = formats['in'][type].read(feature);
         var bounds;
         if(features) {
             if(features.constructor != Array) {
                 features = [features];
             }
             for(var i=0; i<features.length; ++i) {
                 if (!bounds) {
                     bounds = features[i].geometry.getBounds();
                 } else {
                     bounds.extend(features[i].geometry.getBounds());
                 }
             }
	     //delete old features:
	     bboxDataset.removeAllFeatures();
             bboxDataset.addFeatures(features);
             mapframe_dataset_list.zoomToExtent(bounds);
	}
}

function highlightFeatureIndexById(id, open) {
	features = bboxFiles.features;
	for(var i=0; i<features.length; ++i) {
		if(features[i].attributes.id == id) {
			index = i;
			break;
		}
	}
	//unselect all if one is selected
	sf.unselectAll();
	sf.select(bboxFiles.features[index]);
	if (open) {
		window.open(bboxFiles.features[index].attributes.url,'download_window');
	}
}

