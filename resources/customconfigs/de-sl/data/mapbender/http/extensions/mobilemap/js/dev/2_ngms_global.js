// Global
var map, clickCtrl, measureControls;
Proj4js.defs["EPSG:31466"] = "+proj=tmerc +lat_0=0 +lon_0=6 +k=1 +x_0=2500000 +y_0=0 +ellps=bessel +datum=potsdam +units=m +no_defs";
Proj4js.defs["EPSG:4326"] = "+proj=longlat +ellps=WGS84 +datum=WGS84 +no_defs";
var mapProj = new OpenLayers.Projection("EPSG:31466");
var wgs84Proj = new OpenLayers.Projection("EPSG:4326");
var mymapbounds = new OpenLayers.Bounds(2525932,5442177,2602113,5504810); //SL GK3
var myzoombounds = "off"; //new OpenLayers.Bounds(3460280,5482455,3462440,5484561); //Anfangsausdehnung (deaktiv auf "off" setzen)
var mymaxscale = 2000000;
var myminscale = 500;
var myzoomlevels = 16;
var myscales = [2000000, 1500000, 1000000, 500000, 250000, 150000, 100000, 50000, 25000, 15000, 10000, 8000, 5000, 3000, 1000, 500]; //Maßstäbe die dargestellt werden sollen
var zoomSelect = true; //Soll Select aus myscales dargestellt werden? (true/false)
var projUnits = 'm';
var searchMode = 'mapbendersearch'; //Suchmodus (google/streetsearch/mapbendersearch)
var mapbendersearchurl = 'query/searchproxy.php?resultTarget=web&outputFormat=json&searchEPSG='; //Url bzw. Proxy zur Mapbender Ortsuche
var searchEPSG = '31466'; //EPSG Anfrage für Mapbender-Suche
var searchZoom = 10; //Zoomlevel in der Karte
var defaultHand = "r"; //Anfangs Händigkeit (r/l)
var googleGeocodeAdmin = "Saarland"; //Administrative Einheit in der eine Meldung kommen soll falls geocodierter Punkt außerhalb liegt
var directLayerChange = "on"; //Wechsel aus Layersteuerung direkt bei Klick auf Ebene (on/off)
var pageTransition = {transition: "fade"}; //Objekt, Seitenübergänge (vgl. jQuerymobile, z.B. fade, pop, slide, none)
var toolColor = "#003A91"; //Farbe der Toolumrandungen
var featurequerySrc = "EPSG:31466"; //EPSG der FeatureQuery

//Openlayers Objekte
//OpenLayers Kombinations Symbole (Objekt) zur Markierung des Suchergebnis u. Abfragepunktes (Featurequery), mögliche Namen: "star", "cross", "x", "square", "triangle", "circle" 
var olSearchSymbol = { graphicName: 'cross',
					 strokeColor: '#00FFFF',
					 strokeWidth: 1,
					 fillOpacity: 0,
					 pointRadius: 11 };					 
var olFeaturequerySymbol =	{graphicName: 'circle',
						strokeColor: '#00FFFF', //'#871D33',
						fillColor: '#00FFFF',
						strokeWidth: 3,
						fillOpacity: 0.2,
						pointRadius: 15,
						graphicName: 'circle'};

//OpenLayers Symbol (Objekt) GPS-Lokalisierung,  mögliche Namen: "star", "cross", "x", "square", "triangle", "circle" 
var olGpsSymbol = { externalGraphic: 'css/images/gpspoint.png',
					graphicHeight: 16,
					graphicWidth:16
};
					 
//OpenLayers Symbol (Objekt) zur Markierung der GPS-Kreisfarbe + Stil
var olGpscircleStyle = { fillOpacity: 0.2,
						fillColor: '#06C',
						strokeColor: '#06C',
						strokeOpacity: 0.6 };
			
// default style für Zeichnungen
var sketchSymbolizers = {
	"Point": {
		pointRadius: 6,
		graphicName: "square",
		fillColor: "white",
		fillOpacity: 1,
		strokeWidth: 2,
		strokeOpacity: 0.8,
		strokeColor: "#00FFFF"
	},
	"Line": {
		strokeWidth: 3,
		strokeOpacity: 1,
		//strokeDashstyle: "dash",
		strokeColor: "#FF0000"
		
	},
	"Polygon": {
		strokeWidth: 3,
		strokeOpacity: 1,
		strokeColor: "#FF0000",
		fillColor: "white",
		fillOpacity: 0.5
	}
};
//Styles für Markierungen					
var umkreisstyles = new OpenLayers.StyleMap({
	"default": new OpenLayers.Style(null, {
		rules: [
			new OpenLayers.Rule({
				symbolizer: {
					"Point": {
						pointRadius: 5,
						graphicName: "circle",
						fillColor: "white",
						fillOpacity: 0.6,
						strokeWidth: 1,
						strokeOpacity: 1,
						strokeColor: "#CC0000"
					},
					"Line": {
						strokeWidth: 3,
						strokeOpacity: 1,
						strokeColor: "#CC0000"
					},
					"Polygon": {
						strokeWidth: 2,
						strokeOpacity: 1,
						fillColor: "#CC0000",
						strokeColor: "#CC0000"
					}
				}
			})
		]
	})
});	

//Hilfsfunktion zur Ermittlung des Symbols
var poicontext = {
	getGraphic: function(feature) {                    
		return feature.attributes.symbol;
	}
};

//Template für POIs
var poitemplate = {
	externalGraphic: 'config/img/symbol/'+ "${getGraphic}", graphicHeight: 26, graphicWidth:26, cursor:"pointer"
};

//Template für selektierte POIs
var selectpoiTemplate = {
		externalGraphic: 'config/img/symbol/'+ "${getGraphic}", graphicHeight: 26, graphicWidth:26, cursor:"pointer", fillOpacity: 0.7
};

//Definieren der POI-Styles
var poiStyle = new OpenLayers.Style(poitemplate, {context: poicontext});
var poiStyleselect = new OpenLayers.Style(selectpoiTemplate, {context: poicontext});

//StyleMap der POIs
var styleMapPoi = new OpenLayers.StyleMap({
	'default':poiStyle,
	'select':poiStyleselect
});

//default Style für Zeich- / Messfunktion)
var style = new OpenLayers.Style();
style.addRules([
	new OpenLayers.Rule({symbolizer: sketchSymbolizers})
]);
var styleMap = new OpenLayers.StyleMap({"default": style});	
				
