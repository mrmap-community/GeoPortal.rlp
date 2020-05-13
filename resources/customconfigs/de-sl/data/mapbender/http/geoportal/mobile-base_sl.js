// initialize map when page ready
var map;
var mapProj = new OpenLayers.Projection("EPSG:31466");
var mapProjEpsgCode = '31466';
var boundsProj = '2525932,5442177,2602112,5500809';
var projUnits = 'm';
var logo = "<img src='geoportal_logo.png' height='60' width='150' alt='Geoportal Logo'/>";
var init = function (onSelectFeatureFunction) {
    var vector = new OpenLayers.Layer.Vector("Vector Layer", {});
    var geolocate = new OpenLayers.Control.Geolocate({
        id: 'locate-control',
        geolocationOptions: {
            enableHighAccuracy: false,
            maximumAge: 0,
            timeout: 7000
        }
    });
    // create map
    map = new OpenLayers.Map({
        div: "map",
        theme: null,
        projection: mapProj,
        units: projUnits,
	minResolution: 0.01,
        maxExtent: new OpenLayers.Bounds(
		2525932,5442177,2602112,5500809
        ),
        controls: [
            new OpenLayers.Control.Attribution(),
            new OpenLayers.Control.TouchNavigation({
                dragPanOptions: {
                    interval: 100,
                    enableKinetic: true
                }
            }),
            geolocate
           // selectControl
        ],
       /* layers: [
            new OpenLayers.Layer.OSM("OpenStreetMap", null, {
                transitionEffect: 'resize'
            }),
            new OpenLayers.Layer.Bing({
                key: apiKey,
                type: "Road",
                // custom metadata parameter to request the new map style - only useful
                // before May 1st, 2011
                metadataParams: {
                    mapVersion: "v1"
                },
                name: "Bing Road",
                transitionEffect: 'resize'
            }),
            new OpenLayers.Layer.Bing({
                key: apiKey,
                type: "Aerial",
                name: "Bing Aerial",
                transitionEffect: 'resize'
            }),
            new OpenLayers.Layer.Bing({
                key: apiKey,
                type: "AerialWithLabels",
                name: "Bing Aerial + Labels",
                transitionEffect: 'resize'
            }),
            vector,
            sprintersLayer
        ],*/
	layers : [
		new OpenLayers.Layer.WMS( "Luftbild SL",
		"http://geoportal.lkvk.saarland.de/freewms/dop2008?",
		{
		layers: "DOP2008",
		format: "image/jpeg",
		transparent: "On",
		transitionEffect: 'resize'
		},
		{
		projection: mapProj,
		units: projUnits,
		numZoomLevels: 20,
		minScale: 0.1,
		maxScale: 10000000,
		singleTile: false,
		attribution: logo
	} ),
	/*	new OpenLayers.Layer.WMS( "Relief SL",
		"http://geoportal.lkvk.saarland.de/freewms/sl_relief?",
		{
		layers: "SL_RELIEFlow",
		format: "image/png; mode=24bit",
		transparent: "TRUE",
		transitionEffect: 'resize'
		},
		{
		projection: mapProj,
		units: projUnits,
		singleTile: true,
		minScale: 200000,
		maxScale: 100000000,
		'isBaseLayer': false,
		alwaysInRange: true

	} ), */
		new OpenLayers.Layer.WMS( "Saarland Uebersicht",
		"http://geoportal.lkvk.saarland.de/freewms/uebersichtslgesamt?",
		{
		layers: "ATKIS-Praes",
		format: "image/png",
		transparent: "TRUE",
		transitionEffect: 'resize'
		},
		{
		projection: mapProj,
		units: projUnits,
		singleTile: true,
		numZoomLevels: 20,
		minScale: 0.1,
		maxScale: 10000000,
		'isBaseLayer': false,
		alwaysInRange: true
	} ),
		vector
	],
        zoom: 1
    });
    map.addControl(new OpenLayers.Control.Attribution());
    map.zoomToExtent(map.maxExtent);
    var style = {
        fillOpacity: 0.1,
        fillColor: '#000',
        strokeColor: '#f00',
        strokeOpacity: 0.6
    };
    geolocate.events.register("locationupdated", this, function(e) {
        vector.removeAllFeatures();
        vector.addFeatures([
            new OpenLayers.Feature.Vector(
                e.point,
                {},
                {
                    graphicName: 'cross',
                    strokeColor: '#f00',
                    strokeWidth: 2,
                    fillOpacity: 0,
                    pointRadius: 10
                }
            ),
            new OpenLayers.Feature.Vector(
                OpenLayers.Geometry.Polygon.createRegularPolygon(
                    new OpenLayers.Geometry.Point(e.point.x, e.point.y),
                    e.position.coords.accuracy / 2,
                    50,
                    0
                ),
                {},
                style
            )
        ]);
        map.zoomToExtent(vector.getDataExtent());
    });
};
