/**
 * @author schaef
 */


/***************** Initialer Aufruf, um die Ergebnisdatei eines Dienstes zu öffnen ***************/
function openServ(p,file,cLass,dIv,depth,sFilter,ID){
	var counter = 0;
	var target1 = "#erg" + cLass;
	var target2 = "#" + cLass + "Page";
	var target3 = "#" + cLass + "Cloud";
	$.getJSON(file, function(data){
		var fileServ = data[cLass].srv;
		var content = "";
		var content1 = "";
		var content2 ="";
		var pageInf = data[cLass].md;
		var hitTotal = pageInf.nresults;
		var hitpP = pageInf.rpp;
		var pageCount = Math.ceil(hitTotal / hitpP);
		if ($(target1).has(target2).length == 0) {
			content += "<div id='" + cLass + "Page' class='jPaginate' style='left:100px;'></div></br class='clr'>";
			content += "<div id='" + cLass + "Content'></div>"
			$(content).insertAfter(target3);
			if (pageCount > 1) {
				$(target1).queue(function(){
					content1 = loadPaging(cLass, file, sFilter, ID);
					$(this).dequeue();
				});
			};
			$(target2).html(content1);
			$(target1).queue(function(){
				openServPage(p,cLass,ID,file);
				$(this).dequeue();
			})
		}
	});
}

/***************** Funktionen, die die Drastellung für die einzelnen Dienste beginnt und an die Detaildarstellung weiterleitet ***************/

function openServPage(p,e,ID,file){
	var pFile = folder + ID + "_" + e + "_" + p + ".json";
	if (file == undefined) {
		file = pFile;
	}
	var content2 = "";
	var target1 = "#" + e + "Content";
	$.getJSON(pFile,function(data){
		var fileServ = data[e].srv;
		for (var i = 0; i < fileServ.length; i++) {
			if (e == "wms") {
				content2 += openServWMSlayer(fileServ[i].layer, fileServ[i], e, 0,file);
			} else if(e == "wfs"){
				content2 += openServWFSlayer(fileServ[i].ftype,fileServ[i],e,file);
			} else {
				content2 += openServWMClayer(fileServ[i],fileServ[i],e,file);
			}
		}
		if (e == "wms" || e == "wfs") {
			content2 += '<div><fieldset class="search-dienste"><input type="submit" value="In Karte aufnehmen" /></fieldset><div class="clearer"></div>';
		}
		$(target1).html(content2);
	})
}

function openServWMSlayer(layers, Service,cLass,depth,file) {
	var content ="";
	if(layers == undefined) return ""
	for(var i=0; i<layers.length; i++) {
		if (depth==0) {
			content += '<ul class="search-tree">';
			content += '<li class="search-item">';
		} else {
			content += '<ul class="search-tree" style="display:block">';
			content += '<li>';
		}
		
		
		if (layers[i].layer){
			content += getServDetails(layers[i],Service,cLass,true,file);
		} else {
			content += getServDetails(layers[i],Service,cLass,false,file);
		}
		
		
		content += openServWMSlayer(layers[i].layer,Service,cLass,depth + 1,file);
		content += "</li>";
		content += "</ul>";
	}
	return content;
}

function openServWFSlayer(fType, Service, cLass,file){
	var content = "";
	content += "<ul class='search-tree'>";
	for (var i = 0;i<fType.length;i++){
		content += "<li class='search-item'>";
		if (fType[i].modul){
			content += getServDetails(fType[i],Service,'ftype',true,file);
			content += "<ul class='search-tree' style='display:block'>";
			for (var j = 0;j<fType[i].modul.length;j++){
				content += "<li class='seach-item'>";
				content += getServDetails(fType[i].modul[j],Service,'module',true,file);
				content += "</li>";
			}
			content += "</ul>";
		} else {
			content += getServDetails(fType[i],Service,'ftype',false,file);
		}
		content += "</ul>";
	 }
	content += "</ul>";
	return content;
}

function openServWMClayer(layers,Service,cLass,file) {
	var content ="";
	if(layers == undefined) return "";
	content += "<ul class='search-tree'>";
	content += "<li class='search-item'>";
	content += getServDetails(layers,Service,cLass,0,file);
	content += "</li>";
	content += "</ul>";
	return content;
}

/***************** Hier beginnt die Darstellung der einzelnen Ergebnisse für die Deinste ***************/			

function getServDetails(layers,Service,cLass,hasSub,file){
	switch (cLass) {
//		case "layer":
		case "wms":
			var parameter_name = "LAYER";
			var id_field = "id";
			var type = "wms";
		break;
		case "module":
			var parameter_name = "FEATURETYPE";
			var id_field = "wfs_conf_id";
			var type = "wfs";
		break;
		case "ftype":
			var parameter_name = "FEATURETYPE";
			var id_field = "wfs_conf_id";
			var type = "wfs";
		break;
		case "wmc":
			var parameter_name = "WMC";
			var id_field = "wmc_id";
			var type = "wmc";
		break;
	}
	var content = "";
	content += '<div><div class="search-titleicons"><div class="search-checkbox" style="display:inline;">';
	
	
	//Schloss-Symbol oder Checkbox
	if (cLass == "ftype" || cLass == "wmc") {
		if (cLass == "ftype") {
			content += '<img src="../img/search/clear.png" />';
		};
		if (cLass == "wmc") {
			content += '<img src="../img/search/Mapset.png" />';
		};
	} else {
		if (layers.permission != "true") {
			// hier fehlt noch was
			if (mb_user_id == 2){
				content += '<img src="../img/search/icn_encrypted.png" alt="Schloss" title="Dienste Berechtigung" />';
			} else {
				var string = 'ID=' + layers.id + '&TITLE=' + layers.title + '&TYPE=' + cLass + '&TO=' + layers.permission;
				content += '<a href="/mapbender/template/php/register_service.php?' + string +'" target="_blank"><img src="../img/search/icn_encrypted_mail.png" alt="Schloß" title="Dienste Berechtigung" /></a>';
			}
			
		} else {
//			if (Service.hasConstraints == '1') {
			if (Service.hasConstraints = "true") {
				var string2 = 'id=' + Service.id + '&el=' + cLass + layers.id + '&type=' + cLass;
				content += '<input name="' + parameter_name + '[]" value="' + layers.id + '" id="'+ cLass + layers.id + '" type="checkbox" onclick="return tou(this,' + Service.id + ',\'' + type + '\',\'' + string2 + '\');" />';
			} else {
				content += '<input name="' + parameter_name + '[]" value="' + layers.id + '" id="'+ cLass + layers.id + '" type="checkbox" />';
			}
		}
	}
	
	content += "</div>";
	
	//Icon mit Klapp-Funktion
	switch(cLass){
		//wms-layer
//		case "layer":
		case "wms":
			if (layers.isRoot) {
				if (hasSub == true){
					content += '<img onclick="openclose3(this);" src="../img/search/icn_wms2.png" alt="WMS" title="WMSKartenwerk" />';
				} else {
					content += '<img src="../img/search/icn_wms.png" alt="WMS" title="WMSKartenwerk" />';
				}
			} else {
				if (hasSub == true){
					content += '<img onclick="openclose3(this);" src="../img/search/icn_layer2.png" alt="Layer" />';
				} else {
					content += '<img src="../img/search/icn_layer.png" alt="Layer" />';
				}
			}
			break;
		
		//wfs 
		case "ftype":
			if (hasSub == true) {
				content += '<img onclick="openclose3(this);" src="../img/search/icn_wfs2.png" alt="WFS" title="WFSKartenwerk" />';
			} else {
				content += '<img src="../img/search/icn_wfs.png" alt="WFS" title="WFSKartenwerk" />';
			}
			break;
			
		//wfs-Modul
		case "module":
			switch (layers.type){
				case "0":
					content += '<img onclick="openclose3(this);" src="../img/search/icn_abfragemodul.png" alt="WFS" title="WFSKartenwerk"/>';
					break;
				case "1":
					content += '<img onclick="openclose3(this);" src="../img/search/icn_suchmodul.png" alt="WFS" title="WFSKartenwerk" />';
					break;
			}
			break;
	}
	content += "</div>";
	//Titel
	content += '<div class="search-title" id="' + type + "-" +  layers.id + '"><a href="' + layers.mdLink + '" target="_blank" onclick="metadataWindow = window.open(this.href,\'width=400,height=250,left=50,top=50,scrollbars=yes\');metadataWindow.focus(); return false">' + layers.title + '</a></div>';
	//content += '<div class="search-title"><a href="' + layers.mdLink + '" target="_blank" onclick="window.open(this.href,\'width=400,height=250,left=50,top=50,scrollbars=yes\'); return false">' + layers.title + '</a></div>';
	
	//Icons
	content += '<br class="clr" />'
	content += '<div class="search-icons">'
	
	//Preview
	if (layers.previewURL) {
		content += '<img class="search-icons-preview" src="' + layers.previewURL + '" title="Vorschau" alt="Fehler in Vorschau">';
	}
	
	//Logo
	if (Service.logoURL != '') {
		content += '<img src="' + Service.logoUrl + '" title="' + Service.respOrg + '" height="40"/>';
	}
	
	//Wappen
	if (Service.iso3166 != '') {
		content += '<img src="../img/search/wappen_' + Service.iso3166 + '.png" title="' + Service.iso3166 + '" />';
	} else {
		content += '<img src="../img/search/icn_wappen_grau.png" title="Länderkennung fehlt" />';
	}
	
	//Inspire
	if (layers.inspire == 1) {
		content += '<img src="../img/search/icn_inspire.png" title="Inspire" />';
	}
	
	//AccessConstraints 
	if (Service.hasConstraints = "true"){
		var string3 = 'id=' + Service.id + '&type=' + type + '&lang=de';
		content += '<a href="javascript:opentou(\'' + string3 + '\')"><img src="' + Service.symbolLink + '" title="TermsOfUse" /></a>';
	} else {
		content += '<img src="' + Service.symbolLink + '" title="TermsOfUseOK" />';
	}
	
	// costs
	if (Service.price > 0) {
		content += '<img src="../img/search/icn_euro.png" title="Costs" />';
	}
	
	//logging
	if (Service.logged == true) {
		content += '<img src="../img/search/icn_logging.png" title="Logged" />';
	}
	//Network Restrictions
	if (Service.nwaccess == true) {
		content += '<img src="../img/search/icn_eingeschraenketes_netz.png" title="Network Restrictions" />';
	}
	
	//Status
	switch (Service.status) {
		case '1':
			content += '<img src="../img/search/icn_go.png" title="Monitoring1" />';
			break;
		case '0':
			content += '<img src="../img/search/icn_wait.png" title="Monitoring0" />';
			break;
		case '-1':
			content += '<img src="../img/search/icn_stop.png" title="Monitoring-1" />';
			break;
		case '-2':
			content += '<img src="../img/search/icn_refresh.png" title="Monitoring-2" />';
			break;
		case '-3':
			content += '<img src="../img/search/icn_warning.png" title="Monitoring-3" />';
			break;
		default:
			if (cLass != 'wmc') {
				content += '<img src="../img/search/icn_ampel_grau.png" title="Monitoring nicht aktiv" />';
			}
			break;
	}
	
	// Availability
	if (cLass != 'wmc') {
		if (Service.avail == null) {
			content += '<span class="search-icons-availabilty"><span title="Monitoring nicht aktiv">? %</span></span>';
		}
		else {
			content += '<span class="search-icons-availabilty">' + Service.avail + ' %</span>';
		}
	}
	
	// Geometrietyp
	if(type == 'wfs') {
		switch (layers.geomtype){
			case 'PointPropertyType':
				content +='<img src="../img/search/icn_pkt.png" title="Punktgeometrie" />';
				break;
			case 'GeometryPropertyType':
				content +='<img src="../img/search/icn_geo_unbekannt.png" title="Geometrietyp unbekannt" />';
				break;
			case 'PolygonPropertyType':
				content +='<img src="../img/search/icn_poly.png" title="Flächengeometrie" />';
				break;
			case 'LinePropertyType':
				content +='<img src="../img/search/icn_line.png" title="Liniengeometrie" />';
				break;
			default:
				content +='<img src="../img/search/icn_geo_unbekannt.png" title="Geometrietyp unbekannt" />';
				break;
		}
	}
	
	// Queryable
	if (type == "wms") {
		if (layers.queryable == 1) {
			content += '<img src="../img/search/icn_info.png" title="Queryable" />';
		} else {
			content += '<img src="../img/search/icn_info_grau.png" title="NotQueryable" />';
		}
	}
	
		
	// EPSG
	if (cLass != 'wmc') {
		if (layers.srsProblem = 'false') {
			content += '<img src="../img/search/icn_epsg.png" title="NotEPSG" />';
		}
		else {
			content += '<img src="../img/search/icn_epsg_grau.png" title="EPSG" />';
		}
	}
	content += "</div>";
	
	content += "<div class='search-mapicons'>";
    if (layers.downloadOptions) {
        var ids = "";
        for (uuid in layers.downloadOptions) {
            ids += "," + layers.downloadOptions[uuid].uuid;
        }
        content += '<a href="../../mapbender/php/mod_getDownloadOptions.php?id=' + (ids.substring(1)) + '&amp;outputFormat=html&amp;languageCode=de" target="_blank"'
                + ' onclick="downloadWindow = window.open(this.href,\'downloadWindow\',\'width=600,height=400,left=100,top=100,scrollbars=yes,menubar=yes,toolbar=yes,resizable=yes\');downloadWindow.focus(); return false">'
                + '<img class="category-download" src="../img/gnome/document-save-small.png" title="Download" alt="Download"></a>';
    }
    
	if (layers.permission == 'true') {
		var image = "";
		if (cLass == "wms") {
            
			if (Service.hasConstraints == 'true') {
				var string4 = 'id=' + Service.id + '&el=' + type + "-" +  layers.id + '&type=' + type + '&url=/mapbender/geoportal/mod_index.php?mb_user_myGui=Geoportal-SL&LAYERzoom=1&' + parameter_name + '=' + layers.id + '&callId=' + type + "-" +  layers.id + "-" + file;
				content += '<a onclick="return tou2(this,document.getElementById(\'' + type + "-" +  layers.id + '\'),' + Service.id + ',\'' + type + '\',\'' + string4 + '\');" href="/mapbender/geoportal/mod_index.php?mb_user_myGui=Geoportal-SL&LAYER[zoom]=1&' + parameter_name + '[id]=' + layers.id + '&callId=' + type + "-" +  layers.id + "-" + file + '"><img src="../img/search/icn_zoommap.png" title="Auf Ebenenausdehnung zoomen" /></a>';
			} else {
				content += '<a href=/mapbender/geoportal/mod_index.php?mb_user_myGui=Geoportal-SL&LAYER[zoom]=1&' + parameter_name + '[id]=' + layers.id + '&callId=' + type + "-" +  layers.id + "-" + file + '"><img src="../img/search/icn_zoommap.png" title="Auf Ebenenausdehnung zoomen" /></a>';
			}
		}
		switch (type) {
			case "wms":
				image = "map";
				break;
			case "wmc":
				image = "map";
				break;
			case "wfs":
				switch (layers.type) {
					case "0":
						image = "download";
						break;
					case "1":
						image = "suche";
						break;
				}
				break;
		}
		if (Service.hasConstraints == 'true') {
				//hier fehlt was
				var string5 = 'id=' + Service.id + '&el=' + type + "-" +  layers.id + '&type=' + type + '&lang=de&url=/mapbender/geoportal/mod_index.php?mb_user_myGui=Geoportal-SL&' + parameter_name + '=' + layers.id + '&callId=' + type + "-" +  layers.id + "-" + file;
				content += '<a onclick="return tou2(this,document.getElementById(\'' + type + "-" +  layers.id + '\'),' + Service.id + ',\'' + type + '\',\'' + string5 + '\');" href="/mapbender/geoportal/mod_index.php?mb_user_myGui=Geoportal-SL&' + parameter_name + '[id]=' + layers.id + '&callId=' + type + "-" +  layers.id + "-" + file + '"><img src="../img/search/icn_' + image + '.png" title="In Karte aufnehmen" /></a>';
			} else {
				content += '<a href="/mapbender/frames/index.php?mb_user_myGui=Geoportal-SL&' + parameter_name + '[id]=' + layers.id + '&callId=' + type + "-" +  layers.id + "-" + file + '"><img src="../img/search/icn_' + image + '.png" title="In Karte aufnehmen" /></a>';
			}
	}
	
	if (cLass == 'wmc') {
		content += '<a href="/mapbender/geoportal/mod_index.php?mb_user_myGui=Geoportal-SL&' + parameter_name + '=' + layers.id + '&callId=' + type + "-" +  layers.id + "-" + file + '"><img src="../img/search/icn_map.png" title="" /></a>';
	}
	content += "</div><br class='clr' /> ";
	
	
	// Beschreibung
	content += "<div class='search-info-dep'><b>" + Service.respOrg + "</b></div>";
	content += "<div class='search-info'>" + Service.date + "</div>";
	content += "<div class='search-text'>" + layers.abstract + "</div>";
	content += "</div>";
	if (cLass == "wms" || cLass == "wfs") {
			content += '<input type="hidden" name="callId" value="' + type + "-" +  layers.id + "-" + file + '" />';
		}
	return content;
}
			
/******************** Darstellung der OpenSearchtreffer *************************/			
	
/******************** Beginn der Darstellung  ************************************/

function openOS(e,p,UID) {
	var dIv = "#ergOS-" + e;
//	var ffile = folder+ UID + "_os" + e + "_" + p + ".xml";
	$.get(folder+ UID + "_os" + e + "_" + p + ".xml", function(data){
		var content = "";
		var content1 = "";
		$(dIv).queue(function(){
			content += loadPagingOS(e, p, UID);
			$(this).dequeue();
		})
		$(dIv + "Page").html(content);
		$(dIv).queue(function(){
			openOS2(e,p,UID);
			$(this).dequeue();
		})
	});
}

function openOS2(e,p,UID){
	var dIv = "#ergOS-" + e;
	var ffile = folder+ UID + "_os" + e + "_" + p + ".xml";
	$.get(ffile, function(data){
		var content1 = "";
			var osResults = data.getElementsByTagName("result");
			$('result',data).each(function(){
				var child = $(this);
				content1 += openOSDetail(child,ffile,e);
			})
		$(dIv + "Content").html(content1);
	});
}

/***************** Deteildarstellung der Treffer ******************************/
function openOSDetail(child,ffile,e){
	var content = "";
	var catalogTitle = child.find('catalogtitle').text();
	var catalogTitleLink = child.find('catalogtitlelink').text();
	var title = child.find('title').text();
	var Abstract = child.find('abstract').text();
	var urlmDoring = child.find('urlmdoring').text();
	var wmscapURL = child.find('wmscapurl').text();
	var mbaddURL = child.find('mbaddurl').text();
	var georssURL = child.find('georssurl').text();
	var iso19139URL = child.find('iso19139url').text();
	var insspireURL = child.find('inspireurl').text();
	content += "<div class='search-item'>";
	if (wmscapURL){
		content += "<div class='search-mapicons'>";
		content += "<a href='/mapbender/geoportal/mod_index.php?mb_user_myGui=Geoportal-SL&WMS=" + wmscapURL + "&callId=os" + e + "-" + title + "-" + ffile + "' ><img src='../img/search/icn_map.png' title='Auf Karte anzeigen' /></a>"
		content += "</div>";
	} else if (georssURL) {
		content += "<div class='search-mapicons'>";
		content += "<a href='/mapbender/geoportal/mod_index.php?mb_user_myGui=Geoportal-SL&WMS=" + georssURL + "'><img src='../img/search/icn_map_rss.png' title='Auf Karte anzeigen' /></a>";
		content += "</div>";
	}
	content += "<div class='search-title'>";
	content += "<a target='_blank' href='/mapbender/geoportal/" + decodeURIComponent(catalogTitleLink) + "' onclick='openwindow(this.href); return false'>" + title + "</a>";
	
	content += "</div>";
	content += "<div class='search-metacat'>";
	content += catalogTitle;
	content += "</div>";
	content += "<div class='search-text'>";
	content += Abstract;
	content += "</div>"
	if (urlmDoring) {
		content += "<div class='search-metacat'>";
		content += "<a target='_blank' href='" + decodeURIComponent(urlmDoring) + "' onclick='openwindow(this.href); return false'>Originäre Metadaten</a>"
		content += "</div>"
	}
	if (wmscapURL) {
		content += "<div class='search-metacat'>";
		content += "<a target='_blank' href='" + wmscapURL + "' onclick='openwindow(this.href); return false'>Originäre Metadaten</a>"
		content += "</div>"
	}
	if (iso19139URL || insspireURL) {
		content += "<div class='search-text'>";
		content += "<strong>Alternative Formate:</strong><br />";
		if (iso19139URL){
			content += "<a target='_blank' href='/mapbender/geoportal/" + decodeURIComponent(iso19139URL) + "' onclick='openwindow(this.href); return false'><img src='../img/search/icn_iso19139.png' title='ISO19139' /></a>"
		}
		if (insspireURL){
			content += "<a target='_blank' href='/mapbender/geoportal/" + decodeURIComponent(insspireURL) + "' onclick='openwindow(this.href); return false'><img src='../img/search/icn_inspire.png' title='INSPIRE' /></a>" 
		}
		content += "</div>"
	}
	content += "</div>";
	content += "<br />";
	return content;
}