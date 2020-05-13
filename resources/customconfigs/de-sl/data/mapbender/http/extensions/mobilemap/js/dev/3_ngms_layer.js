//Vektorlayer
var vector_marker = new OpenLayers.Layer.Vector("Vector Layer", {});
//GPS Marker
var gps_marker = new OpenLayers.Layer.Vector("gps_marker", {                
	rendererOptions: {zIndexing: true}
});
//Baselayer
var atkis_praes_tms = new OpenLayers.Layer.TMS( "atkis_praes_tms",
        "http://geoportal.saarland.de/mapcache/tms/",
        { 
		layername: 'karte_sl@GK2',
		type: "jpg",
		serviceVersion:"1.0.0",
        gutter:0,
		buffer:0,
		isBaseLayer:true,
		transitionEffect:'resize',
        resolutions:[529.16666666670005270134,396.87500000000000000000,264.58333333330000414207,132.29166666669999585793,66.14583333330000414207,39.68750000000000000000,26.45833333330000058936,13.22916666669999941064,6.61458333329999970118,3.96875000000000000000,2.64583333330000014527,2.11666666670000003236,1.32291666670000007677,0.79375000000000000000,0.26458333330000001204,0.13229166670000001016],
        units: projUnits,
		projection: mapProj,
        sphericalMercator: false
        }
    );

var luftbilder = new OpenLayers.Layer.WMS( "luftbilder", 
	"http://geoportal.lkvk.saarland.de/freewms/dop2010?",
	{
	layers: "DOP2010",
	format: "image/jpeg",
	transparent: "false",
	transitionEffect: 'resize'
	},
	{
	projection: mapProj,
	units: projUnits,
	singleTile: false,
	alwaysInRange: true,
	'isBaseLayer': true		
} );


//Layer hinzufügen
function addmyLayer(){//Layer hinzufügen
	map.addLayers([atkis_praes_tms, gps_marker, vector_marker]);	
}
