/*
 * NetGIS WebGIS Client
 * 
 * (c) Sebastian Pauli, NetGIS, 2017
 */

/**
 * Configuration namespace.
 * @namespace
 * @name config
 * @memberof netgis
 */
netgis.config = 
{
	MAP_CONTAINER_ID:		"map-container",
	
	/** Initial map center coordinate x in main map projection. */
	INITIAL_CENTER_X:		%%center_x_i%%,
	/** Initial map center coordinate y in main map projection. */
	INITIAL_CENTER_Y:		%%center_y_i%%,
	
	/** Initial map zoom scale (e.g. 10000 = 1:10000). */
	INITIAL_SCALE:			%%initial_scale_i%%,
	MAP_SCALES:				[ 500, 1000, 3000, 5000, 8000, 10000, 15000, 25000, 50000, 100000, 150000, 250000, 500000, 1000000, 1500000, 2000000 ],
	
	/** List of available map projections (identifier and proj4 definition). */
	MAP_PROJECTIONS:		[
								[ "EPSG:31467", "+proj=tmerc +lat_0=0 +lon_0=9 +k=1 +x_0=3500000 +y_0=0 +ellps=bessel +datum=potsdam +units=m +no_defs" ],
								[ "EPSG:25832", "+proj=utm +zone=32 +ellps=GRS80 +units=m +no_defs" ],
								[ "EPSG:32632", "+proj=utm +zone=32 +ellps=WGS84 +datum=WGS84 +units=m +no_defs" ]
							],
							
	/** Main projection used by the map view. */
	MAP_PROJECTION:			"EPSG:25832",
	
	/** Map extent (min x, min y, max x, max y). */
	MAP_EXTENT:				[ %%map_extent_csv%% ],
	
	/** Default map layer opacity (0.0 - 1.0). */
	MAP_DEFAULT_OPACITY:	0.8,
	
	/** Maximum number of map view history entries. */
	MAX_HISTORY:			10,
	
	/** Service URLs (avoid proxies by setting to null or empty string). */
	URL_WMC_PROXY:			"./scripts/proxy.php",
	URL_WMC_REQUEST:		"%%server_url%%/mapbender/php/mod_exportWmc2Json.php",
	
	URL_LAYERS_PROXY:		"./scripts/proxy.php",
	URL_LAYERS_REQUEST:		"%%server_url%%/mapbender/extensions/mobilemap/mod_mapbender/search_proxy.php",
	
	URL_SEARCH_PROXY:		"./scripts/proxy.php",
	URL_SEARCH_REQUEST:		"http://www.geoportal.rlp.de/mapbender/geoportal/gaz_geom_mobile.php",
	
	URL_BACKGROUND_HYBRID:	"%%background_hybrid_tms_url%%",
	URL_BACKGROUND_AERIAL:	"%%background_aerial_wms_url%%",

	URL_FEATURE_INFO_PROXY:	"./scripts/proxy.php",
	
	URL_HEIGHT_PROXY:		"./scripts/proxy.php",
	URL_HEIGHT_REQUEST:		"%%server_url%%/mapbender/extensions/mobilemap/query/rasterqueryWms.php?&lang=de" //"http://www.gdi-rp-dienste2.rlp.de/cgi-bin/mapserv.fcgi?map=/data/umn/geoportal/dhm_query/dhm.map&" + "SERVICE=WMS&VERSION=1.1.1&REQUEST=GetFeatureInfo&SERVICE=WMS&LAYERS=mydhm&QUERY_LAYERS=mydhm"

};
