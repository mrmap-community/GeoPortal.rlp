/*
Anmerkungen:
############
Die Suche gibt Dienste (data.wms.srv zurück), die Dienstinformationen (id, title, abstract ect. enthalten). 
Hier werden auch die Zusatzinformationen zur verfügbarkeit ect. abgelegt. 
Diese beinhalten auch das Objekt data.wms.srv.layer, welches über (id, title, abstract, getMapUrl) verfügt. 
Es scheint jedoch immer nur einer dieser Gruppenlayer vorzuliegen.
Es handelt sich bei data.wms.srv.layer wiederum um eine Zusammenfassung von Layern mit dem unterobjekt Objekt data.wms.srv.layer.layer.
Dort finden sich Informationen zum Layer selber (id, title, abstract) und Informationen zur Abfragbarbeit.

Beim Testen fiel auf, dass Services (mit eindeutiger id) auch doppelt mit unterschiedlichen Layern vorkommen können.


Implementierung:
################
Hier werden diese einzelen Objekte als js-Objekte neu definiert um gegen änderungen in der Mapbender-Schnittstelle unabhängig zu sein.

Hier wurde folgende Objekthierarchie festgelegt

"ServiceList" ist eine Zusammenstellung (Array) von Services und trägt die Informationn ob es sich um das Suchergebnis oder die Auswahlliste handelt
diese Information wird beim Einfügen eines "Service" weitegegeben.

Ein "Service" ist ein Array von Gruppenlayern

Ein "Gruppenlayer" ist ein Array von Layern

"Layer" enthält die Layerinformation


*/
var maxlayers = 5;
var maxresults = 40;

// Hintergrundkarten (Urls werden erst beim laden in die Karte besorgt)
var baseinfo = [];
// Angabe Bezeichnung, kommaseperierte Id's
//baseinfo.push(["Hintergrund1","27694","4.8,49,9.9,50"]);
//baseinfo.push(["Hintergrund2","25420","4.8,49,9.9,50"]);
//baseinfo.push(["Hintergrund3","25351,100110","4.8,49,9.9,50"]);

searchUrl = 'mod_mapbender/search_proxy.php?languageCode=de&resultTarget=web&maxResults='+maxresults;


// Initialisierung
function addmyLayer(){

	//Erweiterung OpenLayers - angepasster Click Event
	OpenLayers.Control.Click = OpenLayers.Class(OpenLayers.Control, {
		defaultHandlerOptions: {
			'single': true,
			'pixelTolerance': 0,
			'stopSingle': false
		},

		initialize: function(options) {
			var opts = options || {};
			this.handlerOptions = OpenLayers.Util.applyDefaults(opts.handlerOptions || {},this.defaultHandlerOptions);
			OpenLayers.Control.prototype.initialize.apply(this,arguments);
			this.handler = new OpenLayers.Handler.Click(this,{'click': this.onClick},this.handlerOptions);
		},

		onClick: function( evt ) {
		// click funktion
			var lonlat = map.getLonLatFromViewPortPx(evt.xy); 
			var querylayer = $('#ownlist').find('.query_checked').parent();
			var actuallang = $('#select-lang').val();
			//Punkt erzeugen
			var geompoint = new OpenLayers.Geometry.Point(lonlat.lon, lonlat.lat);
			var geompoint1 = new OpenLayers.Geometry.Point(lonlat.lon, lonlat.lat);
			vector_marker.removeAllFeatures();
			vector_marker.addFeatures([
				new OpenLayers.Feature.Vector(geompoint,{},olSearchSymbol),
				new OpenLayers.Feature.Vector(geompoint1,{},olFeaturequerySymbol)
			]);
			if ($("#select-feature-info").val() === 'p') {
				var fiPopUp = true; 
			} else {
				var fiPopUp = false; 
			}	
		
			if (fiPopUp == true ) {
				setMarkerhint(window.lang.convert('Meldung:'),window.lang.convert('bitte warten...'));
			} else {
				if (querylayer.length == 0) {
					//featureInfo on dhm
					setMarkerhint(window.lang.convert('Meldung:'),window.lang.convert('bitte warten...'));
				} else {
					$.mobile.changePage($("#featureinforesult"),pageTransition);
					//$("#ficontentdiv").text(window.lang.convert("Bitte warten..."));
				}
			}
			//SP: Reset Feature List
			$("#featurelist").empty();
			//SP: DHM Höhe in Ergebnisliste
			getHeight(lonlat.lon, lonlat.lat, actuallang);
			// Query Layer
	 		if(querylayer.length>0){
				//SP: alle queryable layers iterieren
				for (var i = 0; i < querylayer.length; i++) {
					//SP: bei nur einem Feature direkte Referenz, ansonsten array
					var ql = querylayer;
					if (querylayer.length > 1) ql = $(querylayer[i]);
					//SP: check layer visible
					if (ql.find('.layer_checked').length > 0) {
				// Abfrage des ausgewählten Layers
				//if abfrage ob ? vorkommt dann & sonst Fragezeichen
						var featureurl = ql.attr('getmapurl')
								+ '?SERVICE=WMS&REQUEST=getFeatureInfo&VERSION=1.1.1'
								+ '&mapfile='+ ql.attr('name')
								+ '&layers=' + ql.attr('name') + '&QUERY_LAYERS=' + ql.attr('name')
						+ '&SRS=' + featurequerySrc 
						+ '&BBOX=' + map.getExtent().toBBOX()
						+ '&WIDTH=' + map.size.w + '&HEIGHT=' + map.size.h
						+ '&X=' + evt.xy.x + '&Y=' + evt.xy.y
						+ '&INFO_FORMAT=text/html' 
						+ '&FORMAT=image/png' //only for some wms that have problems if this parameter is not given
						+ '&STYLES='; //only for some wms that have problems if this parameter is not given
				
						// Legende für Feature Info
						var legendurl = ql.attr('getmapurl')
								+ 'service=wms&version=1.1.1'
								+ '&request=GetLegendGraphic'
								+ '&format=image/png'
								+ '&layer=' + ql.attr('name');
						
						//SP: check empty feature result!
						featureValid(new FeatureResult(ql.attr('title'), layerInList($('#ownlist'), ql.attr('layerid')), featureurl, legendurl));
						
						//SP: alter Code überflüssig, Popup ???				
				if (fiPopUp) {
					var iframe=$('<iframe src="'+featureurl+'" class="query_iframe">'
						+'<p>Die Abfrage kann leider nicht angezeigt werden.</p></img>'
					);
					setMarkerhint('Abfrageergebnis:',iframe);
				} else {
					var iframe=$('<iframe src="'+featureurl+'" class="query_iframe_full">'
						+'<p>Die Abfrage kann leider nicht angezeigt werden.</p></img>'
					);
					//alert(featureurl);
					//$.mobile.changePage($("#featureinforesult"),pageTransition);
					$("#ficontentdiv").text("");
					$("#ficontentdiv").append(iframe);
					//add onclick delete iframe from page
				}
					} //end if layer checked	
				} //end for
			} else {
				// Standardabfrage, falls keiner ausgewählt ist
				//d.h. dhm!
				var featureurl = 'query/rasterqueryWms.php?coord='+lonlat.lon+ ', '+lonlat.lat+'&lang='+actuallang;
				loadFeature(featureurl);	
			}
	 	},

    		CLASS_NAME: "OpenLayers.Control.Click"
	});

	$("#mapbenderbut").click(function(){
        	$.mobile.changePage($("#mod_mapbender"),pageTransition);
		refresh();
    	});

	//Suchbutton
	$('#mapbendersearchformbut').click(function() {
		searchMaps(searchUrl+'&searchText='+$('#mapbendersearchfield').val());
	});

	//Suchfeld
	//$('#mapbendersearchfield').live('keypress', function(e) {
	$(document).on('keypress', '#mapbendersearchfield', function(e) {
		if(e.keyCode === 13){
			searchMaps(searchUrl+'&searchText='+$('#mapbendersearchfield').val());
		}
	});

	// Alle anderen Layer einklappen
	//$('div[data-role=collapsible]').live('expand', function(){
	$(document).on('expand', 'div[data-role=collapsible]', function(e) {
		$("div[data-role=collapsible]").not($(this)).trigger("collapse");
	});

	//zurück zur Karte-Button
	$(".addToMapBut").click(function(){
		$("div[data-role=collapsible]").not('.ui-collapsible-collapsed').trigger("collapse");
		$.mobile.changePage($("#mappage"),pageTransition);
	});

	addBaselayers();
	
}

//SP: add feature to query results
function addFeatureCallback(feature)
{
	$('#featurelist').append(feature.html);
	refreshFeatureResults();
}
//SP: DHM Höhe abfragen
function getHeight(lon, lat, lang)
{
	var dhm_url = 'query/rasterqueryWms.php?coord=' + lon + ', ' + lat + '&lang=' + lang;
	$.ajax(
	{
		type: 'GET',
		url: dhm_url,
		success: function(data)
		{
			if (data)
			{
				//alert(data);
				if(data.length < 5)
				{
					data = window.lang.convert('Kein Ergebnis!');
				}

				// Dummy Werte
				//data = "<html><head></head><body>Höhe: ~204 [m]</body></html>";
				//data = "Höhe: ~204 [m]";
				
				// zur Featurelist hinzufügen (Anfang)
				var html = $('<div>');
				var collaps = $('<div class="collapsible unselected" data-role="collapsible" data-collapsed="true" data-theme="a" data-content-theme="c" data-inline="true" data-inset="true"></div>');
				collaps.append('<h3>DHM</h3>');
				collaps.append(data);
				html.append(collaps);
				$('#featurelist').prepend(html);
				refreshFeatureResults();
			}
		}
	});
}
//SP: Is feature url valid, call php (PHP Interface über Ajax)
function featureValid(feature)
{
	$.ajax(
	{
		url: 'map.php', //TODO: alter url ----
		type: 'POST',
		data: {feature_url:feature.url},
		success: function(data)
		{
			var valid_url = data.split('\n')[0];
			if (valid_url == "true") addFeatureCallback(feature);
		}
	});
}

// ---------------
// Popupfenster: 
// ---------------

// Popupfenster für Vorschau mit Ajax (deaktiviert)
function preview(url,layerid,bbox){
	$.ajax({
		previewurl: url,
		layerid: layerid,
		bbox: bbox,
		success: function() {		
			$("#preview").find('div[data-role=content]').empty();	
			$("#preview").find('div[data-role=content]').append('<img src="' + $(this).attr("previewurl") + '" alt="image" style="width:200px;">');
			var bbox=$(this).attr("bbox");
			var layerid=$(this).attr("layerid");
			$("#preview_zoom").click(function(){
				var index=getOpenlayersIndex(layerid);
				/*if(index>=0){
					map.zoomToExtent(map.layers[index].getExtent());
				};*/
				zoomToBbox(bbox);
				$.mobile.changePage($("#mappage"),pageTransition);
				
			});
			$('#preview').popup('open');			
		}
	});
}

// Anzeigen eines Popups, bei einer zu großen Auswahl
getInfo = function(content){			
	$("#info_content").empty();
	$("#info_content").append(content);			
	$('#info').popup('open');
}



// ----------------------------
// Funktionen für Openlayers:	
// ----------------------------

// Index des Openlayer Eintrages zurückgeben
function getOpenlayersIndex(layerid){
	for (var i=0; i<map.layers.length; i++){
		if(map.layers[i].name==layerid){
			return i;
		}
	}
	return -1;
}

// Layer in die Karte einfügen
function addOpenlayer(layerid,layername,getMapUrl){
	var openLayer = new OpenLayers.Layer.WMS(layerid,getMapUrl,
			{ layers: layername, format: "image/png", transparent: "TRUE", transitionEffect: 'resize'},
			{ projection: mapProj, units: projUnits, opacity: 0.8, singleTile: true, 'isBaseLayer': false, visibility: true, alwaysInRange: true
			}
		);
	map.addLayer(openLayer);
	map.setLayerIndex(openLayer, 0);

}

// Bestimmter Layer aus Karte entfernen
function removeOpenlayer(layerid){
	var index=getOpenlayersIndex(layerid);
	if(index>=0){
		map.removeLayer(map.layers[index]);
	}
}

// Baselayer auswählen
function setOpenBaselayer(baseid){
	var index=getOpenlayersIndex(baseid);
	if(index>=0){
		map.setBaseLayer(map.layers[index]);
	}
}

// Overlay sichtbar schalten
function selectOpenlayer(layerid,visibility){
	var index=getOpenlayersIndex(layerid);
	if(index>=0){
		map.layers[index].setVisibility(visibility);
	}
}

// Dynamische Hintergrundkarte in die Karte einfügen
function addOpenBaselayer(baselayername,baselayerids){

	// Layerdaten dynamisch laden
	var searchUrl = 'mod_mapbender/search_proxy.php?languageCode=de&resultTarget=web&resourceIds='+baselayerids;
	
	$.getJSON(
		searchUrl, 
		function(data){

		// Hintergrundkarten
		var services=data.wms.srv;
		$.each(services, function(index,srv){
			$.each(srv.layer, function(index, layer){
				if(layer.layer){
					layer=layer.layer[0];
				}
				var layername=layer.name;
				var getMapUrl=srv.getMapUrl;
				var openLayer = new OpenLayers.Layer.WMS(baselayername,getMapUrl,
					{ layers:layername, format:"image/png", transparent:"false", transitionEffect:'resize'},
					{ projection:mapProj, units:projUnits, singleTile:true, 'isBaseLayer':true,  alwaysInRange:true}
							);
				map.addLayers([openLayer]);
			});	
		});
	});
}

// Zoomen auf bestimmte Boundingbox (in WGS84 gegeben!)
function zoomToBbox(bbox){
	var extend=bbox.split(',');
	var p1 = new OpenLayers.LonLat(extend[0],extend[1]).transform(wgs84Proj,mapProj);
	var p2 = new OpenLayers.LonLat(extend[2],extend[3]).transform(wgs84Proj,mapProj);
	map.zoomToExtent(new OpenLayers.Bounds(p1.lon, p1.lat, p2.lon, p2.lat));
}


// -------------------
// Listenfunktionen:
// -------------------

// Anzahl der Dienste zurückgeben
function numServices(){
	return $('#resultlist').find('.service').length;
}

// Anzahl der Layer in einem bestimmten Element zurückgeben
function numResultLayers(element){
	return element.find('.layer').length;
}

// Prüfen ob und wo ein Layer in einer Liste ist
function layerInList(list,id){
	var layers=list.find('.layer');
	for(var i=0; i<layers.length; i++){
		if($(layers[i]).attr('layerid')==id) { return i }
	}
	return -1;
};

// Layer in Auswahl rauf
function up(layerid){
	var position = layerInList($('#ownlist'),layerid);
	if(position>0){
		$($('#ownlist').find('.layer')[position]).after($($('#ownlist').find('.layer')[position-1]));
console.log('todo: layerreihenfolge')
		map.raiseLayer(map.layers[getOpenlayersIndex(layerid)],1);
		validateArrows();
		refresh();	
	}	
}

// Anzahl der Aktuellen Auswahl ausgeben
function numOwnlayers(){
	return $('#ownlist').find('.layer').length;
}



// ----------------------------------------
// Funktioen zur Validierung der Anzeige:
// ----------------------------------------

// Erneuern der Darstellung
function refresh(){
	$('#resultlist').listview('refresh');
	$($('#ownlist').parent()).listview('refresh');
	$('div[data-role=collapsible]').collapsible();
	$('a[data-role=button]').button();
}

//SP: Erneuern Feature List Results
function refreshFeatureResults()
{
	$($('#featurelist').parent()).listview('refresh');
	$('div[data-role=collapsible]').collapsible();
	$('a[data-role=button]').button();
	
	// Sortieren
	$('#featurelist').append(getSorted($('#featurelist').children(), 'data-sort'));
}

//SP: JQuery Objekte nach Attribut sortieren
function getSorted(selector, attrName)
{
    return $($(selector).toArray().sort(function(a, b)
	{
        var aVal = parseInt(a.getAttribute(attrName)),
            bVal = parseInt(b.getAttribute(attrName));
        return aVal - bVal;
    }));
}

// Layerauswahl kennntlich machen
function validateLayers(){
	layers=$('#resultlist').find('.layer');
	$.each(layers, function(index,layer){
		var position = layerInList($('#ownlist'),$(layer).attr('layerid'));
		if(position>=0){
			$(layer).find('.layer_icon').first().attr('class','layer_icon icon layer_remove');
			$(layer).find('.collapsible').first().removeClass('unselected').addClass('selected');
		} else {
			$(layer).find('.layer_icon').first().attr('class','layer_icon icon layer_add');
			$(layer).find('.collapsible').first().removeClass('selected').addClass('unselected');
		}	
	});
}

// Validierung der Pfeile zum verschieben der Layer
function validateArrows(){
	var movers=$('#ownlist').find('.move');
	$(movers[0]).attr('class','icon move arrow_empty');
	if(movers.length>1){
		$(movers[1]).attr('class','icon move arrow_up');
	}
}

// Validierung der Queryable-Auswahl
function query_check(item){
	if($(item).hasClass('query_unchecked')){
		//$('#ownlist').find('.query_checked').addClass('query_unchecked').removeClass('query_checked');
		$(item).addClass('query_checked').removeClass('query_unchecked');	
	} else {
		$(item).addClass('query_unchecked').removeClass('query_checked');
	}
}




// ---------------------------------
// Hinzufüge- + Entfernfunktionen:
// ---------------------------------

// Layer zur Auswahl hinzufügen
function addLayer(layer){
	if(numOwnlayers()>=maxlayers){
		getInfo('Es k&ouml;nnen nicht mehr als '+maxlayers+' Ebenen zur Auswahl hinzugef&uuml;gt werden.');
		return false;
	} else {
		$.mobile.showPageLoadingMsg();
		var position = layerInList($('#ownlist'),$(layer).attr('layerid'));
		if(position<0){
			// Layer hinzufügen
			$('#ownlist').append(OwnLayer(layer.attr('layerid'),layer.attr('title'),layer.attr('name'),layer.attr('desc'),layer.parent().attr('title'),layer.parent().attr('desc'),layer.attr('previewUrl'),layer.attr('queryable')=='true',layer.attr('getMapUrl'),layer.attr('bbox'),layer.parent().attr('avail')));
			validateLayers();
			validateArrows();
			refresh();
			addOpenlayer(layer.attr('layerid'),layer.attr('name'),layer.attr('getMapUrl'));
			$.mobile.hidePageLoadingMsg();
			return true;
		} 
		$.mobile.hidePageLoadingMsg();
		return false;
	}
}

// Layer entfernen
function removeLayer(layer){
	var position = layerInList($('#ownlist'),$(layer).attr('layerid'));
	$($('#ownlist').find('.layer')[layerInList($('#ownlist'),$(layer).attr('layerid'))]).remove();
	validateLayers();
	validateArrows();
	refresh();
	removeOpenlayer($(layer).attr('layerid'));
}

// Layer hinzfügen oder entfernen
function switchLayer(layer){
	var position = layerInList($('#ownlist'),$(layer).attr('layerid'));
	if(position<0){
		addLayer(layer);
	} else {
		removeLayer(layer);
	}
}

// Service mit allen Layern hinzufügen
function addService(service){
	var layers=service.find('.layer');
	for(var i=0; i<layers.length; i++){
		addLayer($(layers[i]));
		if(numOwnlayers()>maxlayers){ break; }
	}
}

// Service mit allen Layern entfernen
function removeService(service){
	$.each(service.find('.layer'), function(index, layer){
		removeLayer($(layer));
	});
}

// Sichtbarkeit eines Layers umschalten
function switchVisibility(layer){
	if($(layer).hasClass('layer')){ // Overlay
		$(layer).find('.layer_visibility').toggleClass('layer_checked').toggleClass('layer_unchecked');
		selectOpenlayer($(layer).attr('layerid'),$(layer).find('.layer_visibility').hasClass('layer_checked'));
	} else {	// Baselayer
		$('#baselayers').find('.base_checked').addClass('base_unchecked').removeClass('base_checked');
		$(layer).addClass('base_checked').removeClass('base_unchecked');
		setOpenBaselayer($(layer).attr('layerids'));
	}
}

//SP: Queryable eines Layers umschalten
function setQueryable(layer, queryable)
{
	if (queryable)
	{
		$(layer).find('.query_queryable').addClass('query_checked').removeClass('query_noinfo');
	}
	else
	{
		$(layer).find('.query_queryable').addClass('query_noinfo').removeClass('query_checked').removeClass('query_unchecked');
	}	
}

//SP: Abfragbarkeit eines Layers umschalten
function setQueryCheck(layer, checked)
{
	if (checked)
	{
		$(layer).find('.query_queryable').addClass('query_checked').removeClass('query_unchecked');	
	}
	else
	{
		$(layer).find('.query_queryable').addClass('query_unchecked').removeClass('query_checked');
	}
}

// Validierung der Baselayer-Auswahl
function base_check(item){
	$('#baselayers').find('.base_checked').addClass('base_unchecked').removeClass('base_checked');
	$(item).addClass('base_checked').removeClass('base_unchecked');
}



// -----------------------
// Daten Laden + Parsen:
// -----------------------

// Standardsuche
function searchMaps(searchurl){
	$.mobile.showPageLoadingMsg();
	$('#search_results').empty();
	$.getJSON(searchurl, function(data){	
		var datacollection=parseMapBenderJson(data);
		appendData(datacollection.services,datacollection.layers,datacollection.nresults);
		$.mobile.hidePageLoadingMsg();
		if(datacollection.nresults>maxresults){
			getInfo('Es gab '+datacollection.nresults
				+' Treffer, es können aber nur '
				+maxresults+' Ergebnisse angezeigt werden.'
				+'</br><b>Schränken Sie Ihre Suche weiter ein.</b>');
		};
	});	
}

// Suche über WMC-Dienste
function searchWmc(wmcurl){
	$.mobile.showPageLoadingMsg();
	$('#search_results').empty();
	$.getJSON(wmcurl, function(data){	
		appendWmc(data);
		$.mobile.hidePageLoadingMsg();
	});	
}

// Parst das JSON-Objekt von MapBender in ein Array [services,layers]
function parseMapBenderJson(json){	
	// Daten generalisieren
	var srvs=json.wms.srv;
	var services=[];
	var layers=[];
	$.each(srvs, function(index,srv){
		// Dienste zusammenfassen
		var dublicated=false;
		for(var i=0; i<services.length; i++){
			if(services[i].id==srv.id){
				dublicated=true;
				break;
			}
		}
		// Dienst freigegeben?
		if(!srv.logged && !srv.nwaccess){
			if(!dublicated){
				services.push(srv);
			}
			// Layer aufnehmen
			$.each(srv.layer, function(index, grplayer){
				if(grplayer.layer){  //wenn sublayer existiert
					//layer rausnehmen, die keinen namen haben  
					$.each(grplayer.layer, function(index, lyr){
						if (lyr.name != "") {
							layers.push([srv.id,lyr]);
						}
					});
				} else {
					if (grplayer.name != "") {
						layers.push([srv.id,grplayer]);
					}
				}
			});
		}
	});
	
	return {"services": services, "layers": layers, "nresults":json.wms.md.nresults};
}

// Fügt die Dienste und Layer in das DOM ein
function appendData(services,layers,nresults){
	$("#resultlist").empty();
	for(var i=0; i<services.length; i++){
		var srv=services[i];
		service=Service(srv.id,srv.title,$(srv).attr('abstract'),srv.getMapUrl,srv.status,(srv.logoUrl==""? 'mod_mapbender/img/defaulicon.png' : srv.logoUrl ),srv.symbolLink,srv.avail);
		// Layer zuordnen
		for(var j=0; j<layers.length; j++){
			var layer=layers[j];
			var serviceid=layer[0];
			var lyr=layer[1];

			if(serviceid==srv.id){
				service.append(ResultLayer(lyr.id,lyr.title,lyr.name,$(lyr).attr('abstract'),lyr.previewURL,lyr.queryable=='1',srv.getMapUrl,lyr.bbox));
			}
		}
		$('#resultlist').append(service);
	}
	$('#resultlist').prepend('<li data-theme="b">Suchergebnis: '+numServices()+' Dienste, '
				+numResultLayers($('#resultlist'))+' Layer '
				+'('+nresults+' Treffer) </li>');
	validateLayers();
	refresh();
}

// Fügt die WMC-Layer in das DOM ein
function appendWmcData(services,layers,layerlist,bbox,crs){
	$("#ownlist").empty();
	var ownlayers = [];
	for(var i=0; i<services.length; i++){
		var srv=services[i];
		// Layer zuordnen
		for(var j=0; j<layers.length; j++){
			var layer=layers[j];
			var serviceid=layer[0];
			var lyr=layer[1];

			if(serviceid==srv.id){
				ownlayers.push(OwnLayer(lyr.id,lyr.title,lyr.name,$(lyr).attr('abstract'),srv.title,$(srv).attr('abstract'),lyr.previewURL,lyr.queryable=='1',srv.getMapUrl,lyr.bbox,srv.avail));
			}
		}
	}


	// Layer richtig sortiert einfügen und selektieren
	for(var i=0; i<layerlist.length; i++){
		for(var j=0; j<ownlayers.length; j++){
			if(layerlist[i].layerId==ownlayers[j].attr('layerid')){
				var layer=ownlayers[j];
				$('#ownlist').append(layer);
				addOpenlayer(layer.attr('layerid'),layer.attr('name'),layer.attr('getMapUrl'));
				if(!layerlist[i].active){ // Auswahl umschalten
					switchVisibility(layer);
				}
				//SP: Layer Abfragbarkeit setzen
				if (layerlist[i].layerQueryable)
				{
					setQueryable(layer, true);
					setQueryCheck(layer, layerlist[i].queryLayer);
				}
				else
				{
					setQueryable(layer, false);
				}
				break;
			}
		}
	}
	validateLayers();
	validateArrows();
	//zoom to wmc extent
	var extend=bbox.split(',');
	var p1 = new OpenLayers.LonLat(extend[0],extend[1]).transform(crs,mapProj);
	var p2 = new OpenLayers.LonLat(extend[2],extend[3]).transform(crs,mapProj);
	map.zoomToExtent(new OpenLayers.Bounds(p1.lon, p1.lat, p2.lon, p2.lat));
	
}

// Alle ausgewählten Overlays entfernen
function clearOwnlist(){
	var layers=$('#ownlist').find('.layer');
	for(var i=0; i<layers.length; i++){
		removeLayer(layers[i]);
	}
}

// WMC-Daten hinzufügen
function appendWmc(json){
	
	// Hintergrundkarte auswählen
	for(var i=0; i<json.backGroundLayer.length; i++){
		var layer=json.backGroundLayer[i];
		if(layer.active){
			var layers=$('#baselayers').find('.baselayer')
			for(var j=0; j<layers.length; j++){
				if($(layers[j]).attr('layerids')==layer.name){
					switchVisibility(layers[j]);
					break;
				}
			}
		}
	}
	// Parsen der bbox aus wmc 
	//var bbox = json.wmc.bbox;
	//var crs = json.wmc.crs;
	// Alle ausgewählten overlays entfernen
	clearOwnlist();

	// Layerids für die Abfrage zusammenstellen
	var layerids="";
	$.each(json.layerList, function(index,layer){
		layerids=layerids+","+layer.layerId;
	});
	layerids=layerids.substr(1,layerids.length);

	// Overlays auswählen
	$.getJSON(searchUrl+'&resourceIds='+layerids, function(data){	
		// Informationen sammeln und Parsen
		var datacollection=parseMapBenderJson(data);
		appendWmcData(datacollection.services,datacollection.layers,json.layerList,json.wmc.bbox,json.wmc.crs);
		$.mobile.hidePageLoadingMsg();
	});
}



// Hintergrunddaten in Collapsible aufnehmen
function addBaselayers(){
	
	$("#baselayers").empty();

	﻿// Vektorlayer
	vector_marker = new OpenLayers.Layer.Vector("Vector Layer", {});
	gps_marker = new OpenLayers.Layer.Vector("gps_marker", {                
		rendererOptions: {zIndexing: true}
	});
	// Hintergrund:Topographie ect.
/*	var atkis_praes_tms = new OpenLayers.Layer.TMS( 
		"Hybrid",
		"http://www.gdi-rp-dienste2.rlp.de/mapcache/tms/",
		{ 
			layername: 'test@UTM32',
			type: "jpeg",
			serviceVersion:"1.0.0",
			gutter:0,
			buffer:0,
			isBaseLayer:true,
			transitionEffect:'resize',
			resolutions: [529.16666666670005270134,396.87500000000000000000,264.58333333330000414207,132.29166666669999585793,66.14583333330000414207,39.68750000000000000000,26.45833333330000058936,13.22916666669999941064,6.61458333329999970118,3.96875000000000000000,2.64583333330000014527,2.11666666670000003236,1.32291666670000007677,0.79375000000000000000,0.26458333330000001204,0.13229166670000001016],
			units: projUnits,
			projection: mapProj,
			sphericalMercator: false
		}
	);*/
	
	$('#baselayers').append(BaseLayer("Saarland Zusammenstellung",atkis_praes_tms.name));	

	// Hintergrund: Luftbild
/*	var luftbilder = new OpenLayers.Layer.WMS( "Luftbild", 
		"http://geo4.service24.rlp.de/wms/dop40_geo4.fcgi?",
		{
		layers: "dop",
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
		}
	);	*/

	$('#baselayers').append(BaseLayer("Luftbilder",luftbilder.name));

/*	var grenze_leer = new OpenLayers.Layer.WMS( "grenze_leer",
		"http://map1.naturschutz.rlp.de/service_basis/mod_wms/wms_getmap.php?mapfile=tk_rlp_gesamt&",
		{
		layers: "grenzen_land",
		format: "image/jpeg",
		transparent: "false",
		transitionEffect: 'resize'
		},
		{
		projection: mapProj,
		units: projUnits,
		singleTile: true,
		alwaysInRange: true,
		'isBaseLayer': true
	} );*/


	//$('#baselayers').append(BaseLayer("Keine Hintergrundkarte",grenze_leer.name));

	map.addLayers([atkis_praes_tms,luftbilder,vector_marker,gps_marker]);

	// Dynamische Hintergrundkarten hinzufügen
	for(i in baseinfo){
		var name=baseinfo[i][0]
		var layerids=baseinfo[i][1];
		$('#baselayers').append(BaseLayer(name,layerids));
		addOpenBaselayer(name,layerids);
	}

	// Erste Hintergrundkarte auswählen
	base_check($('#baselayers').find('.baselayer').first());
}



