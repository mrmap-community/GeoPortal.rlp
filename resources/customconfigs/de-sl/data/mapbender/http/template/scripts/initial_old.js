/**
 * @author schaef
 */


/*********** Startfunktion bei Aufruf der Seite ***********/

$(document).ready(function(){
	uid = "";
	searchId="";
	var Value = splitURL(document.URL);
	searchText = Value["searchText"];
	registrationDepartments = Value["registrationDepartments"];
	isoCategories = Value["isoCategories"];
	inspireThemes = Value["inspireThemes"];
	customCategories = Value["customCategories"];
	regTimeBegin = Value["regTimeBegin"];
	regTimeEnd = Value["regTimeEnd"];
	timeBegin = Value["timeBegin"];
	timeEnd = Value["timeEnd"];
	searchBox = Value["searchBox"];
	searchTypeBbox = Value["searchTypeBbox"];
	searchResources = Value["searchResources"];
	orderBy += Value["orderBy"];
	

	var scookie = new Array();
	var results = document.cookie.split(";");
	for (var i = 0;i<results.length;i++) {
		var temp = results[i].split("=");
		scookie[temp[0]] = temp[1];
	}
	
//	uid = scookie[" PHPSESSID"];
	uid = Math.random();
	uid = uid.toString();
	searchId = MD5(uid);

	console.info(uid,searchId)
	
	pollingServ(searchId,'all');
	pollingMeta(1,uid);

	
//	var searchstring = "../php/mod_callMetadata.php?" + searchFilter;
	var searchstring = "../php/mod_start_search.php?" + searchFilter + "&uid=" + uid;
//	var searchstring = "../php/mod_start_search.php?" + searchFilter;
	$.ajax({
		url:searchstring,
		dataType:'json',
		success: function(data){
			searchId = data.searchId;
			uid = data.uid;
			mb_user_id = data.mb_user_id;
			console.info(data);
			$(".loader").hide();
		}
	})
})

/**************  Funktionen für den Suchheader ********/

function headerAll(ID){
	var filterFile = folder + ID + "_filter.json";
	var target = "../php/mod_start_search.php?";
	$.getJSON(filterFile, function(data){
		var content = "";
		var sF = data.searchFilter;
		var cat = "";
		if (sF.classes.length > 1) {
			cat = "all";
		} else {
			cat = sF.classes[0].name;
		}
		searchFilter = sF.origURL;
		if (sF.searchText){
			content += "<h3><a href='javascript:newSearchCloud(\"searchText=" + sF.searchText.delLink + "\",\"" + ID + "\",\"" + cat + "\")' title='alle entfernen'>" + sF.searchText.title + "</a></h3>";
			for (var i=0;i<sF.searchText.item.length;i++){
				content += "<a href='javascript:newSearchCloud(\"" + sF.searchText.item[i].delLink + "\",\"" + ID + "\",\"" + cat + "\")' title='alle entfernen'>";
				content += sF.searchText.item[i].title;
				content += "</a>, ";
			}
		}
		if (sF.isoCategories){
			content += "<h3><a href='javascript:newSearchCloud(\"" + sF.isoCategories.delLink + "\",\"" + ID + "\",\"" + cat + "\")' title='alle entfernen' >" + sF.isoCategories.title + "</a></h3>";
			for (var i=0;i<sF.isoCategories.item.length;i++){
				content += "<a href='javascript:newSearchCloud(\"" + sF.isoCategories.item[i].delLink + "\",\"" + ID + "\",\"" + cat + "\")' title='alle entfernen'>";
				content += sF.isoCategories.item[i].title + "";
				content += "</a>, ";
			}
			content += "</br>";
		}
		if (sF.inspireThemes){
			content += "<h3><a href='javascript:newSearchCloud(\"" + sF.inspireThemes.delLink + "\",\"" + ID + "\",\"" + cat + "\")' title='alle entfernen'>" + sF.inspireThemes.title + "</a></h3>";
			for (var i=0;i<sF.inspireThemes.item.length;i++){
				content += "<a href='javascript:newSearchCloud(\"" + sF.inspireThemes.item[i].delLink + "\",\"" + ID + "\",\"" + cat + "\")' title='alle entfernen'>";
				content += sF.inspireThemes.item[i].title;
				content += "</a>, ";
			}
		}
		$("#headerAll").html(content);
	})
}

function clearHeader(){
	$(".displayService").hide();
}

function showHeader(){
	$(".displayService").show();
}

function headerDetail(ID){
	var filterfile = folder + ID + "_filter.json";
	$.getJSON(filterfile, function(data){
	var filterRes = data.searchFilter.maxResults;
		var filterOrd = data.searchFilter.orderFilter;
		var content="";
		content += '<div style="float:left;">';
			content += '<form action="" method="post" onsubmit="return false;">';
				content += '<fieldset>';
					content += '<legend>' + filterOrd.header + '</legend>';
					content += '<select onchange="changeHits(this.options[this.selectedIndex].value,\''+ ID+ '\')">';
						content += '<option value="">' + filterOrd.title + '</option>';
						for (var i = 0; i<filterOrd.item.length; i++) {
							content += '<option value="' + filterOrd.item[i].url + '">'+ filterOrd.item[i].title + '</option>'
						}
					content += '</select>';
				content += '</fieldset>';
			content += '</form>';
		content += '</div>';
		content += '<div style="margin-top:5px;">';
			content += '<form>';
				content += '<fieldset>';
					content += '<legend>' + filterRes.header + '</legend>';
				//	content += '<select onchange="if(this.options[this.selectedIndex].value!=\'\'){window.location.href=document.getElementsByTagName(\'base\')[0].getAttribute(\'href\')+this.options[this.selectedIndex].value;}">';
					content += '<select onchange="changeHits(this.options[this.selectedIndex].value,\''+ ID+ '\')">';
						content += '<option value="">' + filterRes.title + '</option>';
						for (var i = 0; i<filterRes.item.length; i++) {
							content += '<option value="' + filterRes.item[i].url + '">'+ filterRes.item[i].title + '</option>'
						}
					content += '</select>';
				content += '</fieldset>';
			content += '</form>';
		content += '</div>';
		$("#headerDienst").html(content);
	})
}

function changeHits(mode,ID) {
	var sID = MD5(ID);
//	var searchstring = "../php/mod_start_search.php?searchId=" + ID + "&" + mode;
	var searchstring = "../php/mod_callMetadata.php?searchId=" + sID + "&" + mode;
	$(".countService").replaceWith("");
	$("#headerCategory").html("");
	$("#search-container-dienste nobr").remove();
	pollingServ(sID,'all');
	$(".loader").show();
	
	$.getJSON(searchstring, function(data){
		searchId = data.searchId;
		uid = data.uid;
		mb_user_id = data.md_user_id;
//		console.info(data);
		$(".loader").hide();
	})
}

/***** Funktionen für die Darstellung der Tabs **********/

function loadTabDienst (ID) {
	var filterfile = folder + ID + "_filter.json"; 
	$.getJSON(filterfile, function(data){
		$("#tabs-2").html(function(){
			var content = "";
			var sF = data.searchFilter;
			content += '<div class="search-container">';
			content += '<div id="search-container-dienste">';
			content += '<form name="formmaps" action="/mapbender/frames/index.php" method="get">';
			content +='<fieldset class="hidden"><input name="zoomToLayer" type="hidden" value="0" /></fieldset>';
			content +='<fieldset class="hidden"><input name="mb_user_myGui" type="hidden" value="Geoportal-SL" /></fieldset>';
			for ( var i = 0; i<sF.classes.length; i++) {
				var toggle1ID = "erg" + sF.classes[i].name;
				content += '<div class="search-header" onclick="toggle1(\'#'+ toggle1ID +'\',\'' + ID + '\',\'' + sF.origURL + '\')">';
				content += '<span id="' + sF.classes[i].name + 'Pfeil" class="ui-icon ui-icon-triangle-1-e"></span>';
				content += '<h2>'+ sF.classes[i].title + '</h2>';
				content += '<p id="' + sF.classes[i].name + 'Count"></p>'
				content += '</div>';
				content += '<div id="' + toggle1ID + '" style="display:none"></div>';
				content += '</br>';
			}
			content += '</form>';
			content += '</div> ';
			content += '</div>';
			return content
		});
		$("#tabs-2").queue(function(){
			var sF = data.searchFilter;
			for (var i = 0; i < sF.classes.length; i++) {
				loadtime(sF.classes[i].name,ID);
			}
			$(this).dequeue();
		})
		$("#tabs-2").queue(function(){
			var sF = data.searchFilter;
			for (var i = 0;i<sF.classes.length;i++) {
				loadTagCloud(sF.classes[i].name,ID)
			}
			$(this).dequeue();
		})
	})
}

function loadTabServPol(ID,e){
	var filterfile = folder + ID + "_filter.json"; 
	$.getJSON(filterfile, function(data){
		$("#tabs-2").html(function(){
			var content = "";
			var sF = data.searchFilter;
			content += '<div class="search-container">';
			content += '<div id="search-container-dienste">';
			content += '<form name="formmaps" action="/mapbender/frames/index.php" method="get">';
			content +='<fieldset class="hidden"><input name="zoomToLayer" type="hidden" value="0" /></fieldset>';
			content +='<fieldset class="hidden"><input name="mb_user_myGui" type="hidden" value="Geoportal-SL" /></fieldset>';
			for ( var i = 0; i<sF.classes.length; i++) {
				var toggle1ID = "erg" + sF.classes[i].name;
				content += '<div class="search-header" onclick="toggle1(\'#'+ toggle1ID +'\',\'' + ID + '\',\'' + sF.origURL + '\')">';
				content += '<span id="' + sF.classes[i].name + 'Pfeil" class="ui-icon ui-icon-triangle-1-e"></span>';
				content += '<h2>'+ sF.classes[i].title + '</h2>';
				content += '<p id="' + sF.classes[i].name + 'Count"></p>'
				content += '</div>';
				content += '<div id="' + toggle1ID + '" style="display:none"></div>';
				content += '</br>';
			}
			content += '</form>';
			content += '</div> ';
			content += '</div>';
			return content
		});
		$("#search-container-dienste").queue(function(){
			switch(e) {
				case 'all':
					pollingWMS(ID);
					pollingWFS(ID);
					pollingWMC(ID);
					break;
					
				case 'wms':
					pollingWMS(ID);
					break;
				case 'wfs':
					pollingWFS(ID);
					break;
				case 'wmc':
					pollingWMC(ID);
					break;
			}
			$(this).dequeue();
		})
	});
}

function initMetatab(p,UID){
	$.get(folder + UID + "_os.xml", function(data){
		var osIndex = data.getElementsByTagName("opensearchinterface");
		$.each([osIndex], function(index, value){
			for (i = 0; i < value.length; i++) {
				var osId = i+1;
				var content = "";
				content += '<div class="search-header" onclick="toggleOS(\'' + osId + '\',\'' + p + '\',\'' + UID + '\')">';
				content += '<span id="ergOS-' + osId + '-Pfeil" class="ui-icon ui-icon-triangle-1-e"></span>';
				content += '<h2>' + value[i].firstChild.nodeValue + '</h2>';
				content += '<p id="os-' + osId + '"></p></div>';
				content += '<div id="ergOS-' + osId + '" style="display:none;">';
				content += '<div id="ergOS-' + osId + 'Content"></div></br class="clr">';
				content += '<div id="ergOS-' + osId + 'Page" class="jPaginate"></div>';
				content += '</div><br>';
				$("#tabs-3").append(content);
				pollingMeta2(osId,p,UID);
			}
		});
	});
}

function loadTabMeta(osId,p,UID){
	var countos1 = "";
	var countos2 = "";
	var countos3 ="";
	var countges
	var searchFile = folder + UID + "_os" + osId + "_" + p + ".xml";
	$.get(searchFile, function(data){
		var totRes = data.getElementsByTagName("totalresults");
		$.each([totRes], function(index, value){
			var countos = parseInt(value[0].firstChild.nodeValue );
			if (value[0].firstChild.nodeValue != "") {
				$("#os-" + osId + "").append(' (' + value[0].firstChild.nodeValue + ' Treffer)')
			} 
			if ($("#search-container-meta").has('.countMeta').length > 0) {
				var countserv = $(".countMeta")[0].childNodes[0].wholeText;
				countserv = parseInt(countserv);
				countserv = countserv + countos;
				var content3 = "<span class='countMeta'>" + countserv + "</span>";
				$(".countMeta").replaceWith(content3);
			} else {
				var content1 = "<span class='countMeta'>" + countos + "</span>";
				var content2 = "<nobr><span class='countMeta'>" + countos + "</span> Treffer</nobr>";
				$(content2).insertBefore("#statusMetaBod");
				$(content1).insertBefore("#statusMetaTab");
			}
		})
	});
}

function loadtime(e,ID) {
	var countwms = "";
	var countwfs = "";
	var countwmc = "";
	var countserv

	switch(e){
		case 'wms':
			$.getJSON(folder + ID + "_wms_1.json", function(data){
				$("#wmsCount").append(' (' + data.wms.md.nresults + ' Treffer in ' + Math.round(data.wms.md.genTime*100)/100 + ' Sekunden)');
				countwms = parseInt(data.wms.md.nresults);
				if ($("#search-container-dienste").has('.countService').length > 0) {
					var countserv = $(".countService")[0].childNodes[0].wholeText;
					countserv = parseInt(countserv);
					countserv = countserv + countwms;
					var content3 = "<span class='countService'>" + countserv + "</span>";
					$(".countService").replaceWith(content3);
				} else {
					var content1 = "<span class='countService'>" + countwms + "</span>";
					var content2 = "<nobr><span class='countService'>" + countwms + "</span> Treffer</nobr>";
					$(content2).insertBefore("#statusServiceBod");
					$(content1).insertBefore("#statusServiceTab");
				}
				
			});
			break;
		case 'wfs':
			$.getJSON(folder + ID + "_wfs_1.json", function(data){
				$("#wfsCount").append(' (' + data.wfs.md.nresults + ' Treffer in ' + Math.round(data.wfs.md.genTime*100)/100 + ' Sekunden)')
				countwfs = parseInt(data.wfs.md.nresults);
				if ($("#search-container-dienste").has('.countService').length > 0) {
					var countserv = $(".countService")[0].childNodes[0].wholeText;
					countserv = parseInt(countserv);
					countserv = countserv + countwfs;
					var content3 = "<span class='countService'>" + countserv + "</span>";
					$(".countService").replaceWith(content3);
				} else {
					var content1 = "<span class='countService'>" + countwfs + "</span>";
					var content2 = "<nobr><span class='countService'>" + countwfs + "</span> Treffer</nobr>";
					$(content2).insertBefore("#statusServiceBod");
					$(content1).insertBefore("#statusServiceTab");
				}
			});
			break;
		case 'wmc':
			$.getJSON(folder + ID + "_wmc_1.json", function(data){
				$("#wmcCount").append(' (' + data.wmc.md.nresults + ' Treffer in ' + Math.round(data.wmc.md.genTime*100)/100 + ' Sekunden)')
				countwmc = parseInt(data.wmc.md.nresults);
				if ($("#search-container-dienste").has('.countService').length > 0) {
					var countserv = $(".countService")[0].childNodes[0].wholeText;
					countserv = parseInt(countserv);
					countserv = countserv + countwmc;
					var content3 = "<span class='countService'>" + countserv + "</span>";
					$(".countService").replaceWith(content3);
				} else {
					var content1 = "<span class='countService'>" + countwmc + "</span>";
					var content2 = "<nobr><span class='countService'>" + countwmc + "</span> Treffer</nobr>";
					$(content2).insertBefore("#statusServiceBod");
					$(content1).insertBefore("#statusServiceTab");
				}
			});
			break;
	}
	//$("#search-container-dienste").html("<p>" + countdienst + " Treffer</p>")
	//$("#statusService").replaceWith(countserv)
}

/*********** Funktionen für die TagClouds  ***********/

function loadTagCloud(e,ID) {
	var cfile = folder + ID + "_" + e + cFileEnd;
	$.getJSON(cfile, function(data){
		switch(e) {
			case 'wms':
				var toggle2ID = e + "Cloud";
				var tags = data.tagCloud.tags;
				var wmsCloudcontent = "";
				wmsCloudcontent += '<div class="tagcloud" onclick="toggle2(\'#' + toggle2ID + '\')">';
				wmsCloudcontent += '<span id="' + toggle2ID + 'Pfeil" class="ui-icon ui-icon-triangle-1-e"></span>';
				wmsCloudcontent += '<h3>' + data.tagCloud.title + '</h3>';
				wmsCloudcontent += '</div>';
				wmsCloudcontent += '<div class="cloud" id="'+ toggle2ID + '" style="display:none; overflow:auto">';
				for (var i = 0; i<tags.length; i++) {
					wmsCloudcontent += "<a href='javascript:newSearchCloud(\"" + tags[i].url + "\",\"" + ID + "\",\"" + e + "\")' title='" + tags[i].title + "' style='font-size:" + tags[i].weight + "px'><li>" + tags[i].title + "</li></a>";
				}
				wmsCloudcontent += "</div></br class='clr'>";
				$("#ergwms").html(wmsCloudcontent);
				break;
			case 'wfs':
				var toggle2ID = e + "Cloud";
				var tags = data.tagCloud.tags;
				var wfsCloudcontent = "";
				wfsCloudcontent += '<div class="tagcloud" onclick="toggle2(\'#' + toggle2ID + '\')">';
				wfsCloudcontent += '<span id="' + e + 'Pfeil" class="ui-icon ui-icon-triangle-1-e"></span>';
				wfsCloudcontent += "<h3>" + data.tagCloud.title + "</h3>";
				wfsCloudcontent += "</div>";
				wfsCloudcontent += '<div class="cloud" id="'+ toggle2ID + '" style="display:none; overflow:auto">';
				for (var i = 0; i<tags.length; i++) {
					wfsCloudcontent += "<li>"
					wfsCloudcontent += "<a href='javascript:newSearchCloud(\"" + tags[i].url + "\",\"" + ID + "\",\"" + e + "\")' title='" + tags[i].title + "' style='font-size:" + tags[i].weight + "px'>" + tags[i].title + "</a>";
					wfsCloudcontent += "</li>";
				}
				wfsCloudcontent += "</div>";
				$("#ergwfs").html(wfsCloudcontent);
				break;
			case 'wmc':
				var toggle2ID = e + "Cloud";
				var tags = data.tagCloud.tags;
				var wmcCloudcontent = "";
				wmcCloudcontent += '<div class="tagcloud" onclick="toggle2(\'#' + toggle2ID + '\')">';
				wmcCloudcontent += '<span id="' + e + 'Pfeil" class="ui-icon ui-icon-triangle-1-e"></span>';
				wmcCloudcontent += "<h3>" + data.tagCloud.title + "</h3>";
				wmcCloudcontent += "</div>";
				wmcCloudcontent += '<div class="cloud" id="'+ toggle2ID + '" style="display:none; overflow:auto">';
				for (var i = 0; i<tags.length; i++) {
					wmcCloudcontent += "<a href='javascript:newSearchCloud(\"" + tags[i].url + "\",\"" + ID + "\",\"" + e + "\")' title='" + tags[i].title + "' style='font-size:" + tags[i].weight + "px'><li>" + tags[i].title + "</li></a>";
				}
				wmcCloudcontent += "</div>";
				$("#ergwmc").html(wmcCloudcontent);
				break;
		}
		
	});
	
}


function newSearchCloud(URL,ID,e){
	var sID = MD5(ID);
	uid = MD5(uid);
	if (URL.match(/cat.+/)){
		var searchstring = "../php/mod_start_search.php?searchId=" + sID + "&uid=" + uid + "&" + URL;
	} else {
		var searchstring = "../php/mod_start_search.php?cat=Dienst&searchId=" + sID + "&uid=" + uid + "&" + URL;
	}
	$(".countMeta").replaceWith("");
	$(".countService").replaceWith("");
	$("#headerCategory").html("");
	$("#tabs-3").html("");
	$("nobr").remove();
	pollingServ(sID,e);
	pollingMeta(1,uid);
	$(".loader").show();
	
	$.getJSON(searchstring, function(data){
		searchId = data.searchId;
		uid = data.uid;
		mb_user_id = data.md_user_id;
//		console.info(data);
		$(".loader").hide();

	})
}



//********* Funktionen für das Paging **********

function loadPaging(e,File,sFilter,ID) {
	switch(e) {
		case "wms":
		$.getJSON(File, function(data){
			var pageInf = data.wms.md;
			var totalPage = "";
			var hitpP = pageInf.rpp;
			var hitTotal = pageInf.nresults;
			var hitPage = pageInf.p;
			var pageCount = Math.ceil(hitTotal / hitpP);
			if (pageCount < 8) {
				pageDisplay = pageCount;
			} else {
				pageDisplay = 8;
			}
			if (pageCount > 1) {
				$("#wmsPage").paginate({
					count: pageCount,
					start: hitPage,
					display: pageDisplay,
					border: true,
					border_color: '#fff',
					text_color: '#fff',
					background_color: 'black',
					border_hover_color: '#ccc',
					text_hover_color: '#000',
					background_hover_color: '#fff',
					images: false,
					mouse: 'press',
					onChange: function(page){
						var searchstring = "";
						searchstring += "../php/mod_callMetadata.php?searchId=" + ID + "&searchResources=wms&" + sFilter + "&searchPages=" + page;
						$.getJSON(searchstring, function(data){
							$("#tabs-2").queue(function(){
								openServPage(data,page,e,ID);
								$(this).dequeue();
							})
						})
					}
				});
			}
		
		});
		break;
		case "wfs":
		$.getJSON(File, function(data){
			var pageInf = data.wfs.md;
			var totalPage = "";
			var hitpP = pageInf.rpp;
			var hitTotal = pageInf.nresults;
			var hitPage = pageInf.p;
			var pageCount = Math.ceil(hitTotal / hitpP);
			if (pageCount < 8) {
				pageDisplay = pageCount;
			} else {
				pageDisplay = 8;
			}
			if (pageCount > 1) {
				$("#wfsPage").paginate({
					count: pageCount,
					start: hitPage,
					display: pageDisplay,
					border: true,
					border_color: '#fff',
					text_color: '#fff',
					background_color: 'black',
					border_hover_color: '#ccc',
					text_hover_color: '#000',
					background_hover_color: '#fff',
					images: false,
					mouse: 'press',
					onChange: function(page){
						var searchstring = "";
						searchstring += "../php/mod_callMetadata.php?searchId=" + ID + "&searchResources=wfs&" + sFilter + "&searchPages=" + page;
						$.getJSON(searchstring, function(data){
							$("#tabs-2").queue(function(){
								openServPage(data,page,e,ID);
								$(this).dequeue();
							})
						})
					}
				});
			}
		
		});
		break;
		case "wmc":
		$.getJSON(File, function(data){
			var pageInf = data.wmc.md;
			var totalPage = "";
			var hitpP = pageInf.rpp;
			var hitTotal = parseInt(pageInf.nresults);
			var hitPage = parseInt(pageInf.p);
			var pageCount = Math.ceil(hitTotal / hitpP);
			var pageDisplay = "";
			if (pageCount < 8) {
				pageDisplay = pageCount;
			} else {
				pageDisplay = 8;
			}
			if (pageCount > 1) {
				$("#wmcPage").paginate({
					count: pageCount,
					start: hitPage,
					display: pageDisplay,
					border: true,
					border_color: '#fff',
					text_color: '#fff',
					background_color: 'black',
					border_hover_color: '#ccc',
					text_hover_color: '#000',
					background_hover_color: '#fff',
					images: false,
					mouse: 'press',
					onChange: function(page){
						var searchstring = "";
						searchstring += "../php/mod_callMetadata.php?searchId=" + ID + "&searchResources=wmc&" + sFilter + "&searchPages=" + page;
						$.getJSON(searchstring, function(data){
							$("#tabs-2").queue(function(){
								openServPage(data,page,e,ID);
								$(this).dequeue();
							})
						})
					}
				});
			}
		
		});
		break;
	}
}

function loadCreateCategories(sFilter, elmStr) {
//    var searchFilter ="'.urlencode($matches[1][0]).'";
    var format = "html";
    var searchstring = "../php/mod_create_category.php?searchResources=wms&orderby=title&searchText=" + sFilter+"&format="+format;
    
    switch(format) {
        case "json":
            $.getJSON(File, function(data){
                    var pageInf = data.wfs.md;
                    var totalPage = "";
                    var hitpP = pageInf.rpp;
                    var hitTotal = pageInf.nresults;
                    var hitPage = pageInf.p;
                    var pageCount = Math.ceil(hitTotal / hitpP);
                    if (pageCount < 8) {
                            pageDisplay = pageCount;
                    } else {
                            pageDisplay = 8;
                    }
                    if (pageCount > 1) {
                            $("#wfsPage").paginate({
                                    count: pageCount,
                                    start: hitPage,
                                    display: pageDisplay,
                                    border: true,
                                    border_color: '#fff',
                                    text_color: '#fff',
                                    background_color: 'black',
                                    border_hover_color: '#ccc',
                                    text_hover_color: '#000',
                                    background_hover_color: '#fff',
                                    images: false,
                                    mouse: 'press',
                                    onChange: function(page){
                                            var searchstring = "";
                                            searchstring += "../php/mod_callMetadata.php?searchId=" + ID + "&searchResources=wfs&" + sFilter + "&searchPages=" + page;
                                            $.getJSON(searchstring, function(data){
                                                    $("#tabs-2").queue(function(){
                                                            openServPage(data,page,e,ID);
                                                            $(this).dequeue();
                                                    })
                                            })
                                    }
                            });
                    }

            });
            default:
                $.get(searchstring, function(data){
                    $(elmStr).html(data);
                });
                break;
	}
}

function loadPagingOS(e,p,UID){
	$.get(folder + UID + "_os" + e + "_" + p + ".xml", function(data){
		var resultInf = data.getElementsByTagName("npages");
		$.each([resultInf], function(index, value){
			var nPages = value[0].firstChild.nodeValue;
			var pageDisplay;
			if (nPages < 8) {
				pageDisplay = nPages;
			} else {
				pageDisplay = 8;
			}
			if (nPages > 1) {
				$("#ergOS-" + e + "Page").paginate({
					count: nPages,
					start: 1,
					display: pageDisplay,
					border: true,
					border_color: '#fff',
					text_color: '#fff',
					background_color: 'black',
					border_hover_color: '#ccc',
					text_hover_color: '#000',
					background_hover_color: '#fff',
					images: false,
					mouse: 'press',
					onChange: function(page){
						var searchstring = "";
						var cat = e - 1;
						searchstring += "../geoportal/mod_readOpenSearchResults.php?q=" + data.getElementsByTagName('querystring')[0].firstChild.nodeValue + "&request_id=" + UID + "&cat=" + cat + "&p=" + page;
						$.get(searchstring, function(data){
							var target = "#ergOS-" + e;
							$(target).queue(function(){
								openOS2(e, page, UID);
								$(this).dequeue();
							})
						})
					}
				});
			}
		});
	})
}


//********* Funktionen für die Kategoriesuche (rechte Seite) **********


function loadCategories(e,ID) {
	/*
switch(e) {
		case "wms":
*/
		var catFile = folder + ID + "_" + e + "_cat.json";
		$.getJSON(catFile, function(data){
			var filterCat = data.searchMD.category;
			if (filterCat) {
				var content = "";
				var content2 = "";
				for (var i = 0; i < filterCat.length; i++) {
					var tit = filterCat[i].title;
					var tID = "title" + tit.slice(0, 3);
					var tID2 = "#" + tID;
					content2 = $(tID2).html();
					if (filterCat[i].subcat) {
						content += "<h4>" + filterCat[i].title + "</h4>";
						content += "<ul id='" + tID + "'>";
						if ($("#searchCategories").has(tID2).length > 0) {
							content += content2;
							for (var j = 0; j < filterCat[i].subcat.length; j++) {
								var aID = "#" + filterCat[i].subcat[j].title.slice(0, 5);
								if ($("#searchCategories").has(aID).length > 0) {
									var content3 = $(aID).html();
									var acount = content3.split("(");
									acount = acount[1].split(")");
									acount = parseInt(acount[0]);
									var acountges = acount + parseInt(filterCat[i].subcat[j].count);
									var content4 = filterCat[i].subcat[j].title + " (" + acountges + ")";
									content = content.replace(content3, content4);
								}
								else {
									content += "<li>";
									content += "<a id=" + filterCat[i].subcat[j].title.slice(0, 5) + " href='javascript:newSearchCloud(\"" + filterCat[i].subcat[j].filterLink + "\",\"" + data.searchMD.searchId + "\",\"all\")'>" + filterCat[i].subcat[j].title + " (" + filterCat[i].subcat[j].count + ")</a>";
									content += "</li>";
								}
							}
						}
						else {
							for (var j = 0; j < filterCat[i].subcat.length; j++) {
								content += "<li>";
								content += "<a id=" + filterCat[i].subcat[j].title.slice(0, 5) + " href='javascript:newSearchCloud(\"" + filterCat[i].subcat[j].filterLink + "\",\"" + data.searchMD.searchId + "\",\"all\")'>" + filterCat[i].subcat[j].title + " (" + filterCat[i].subcat[j].count + ")</a>";
								content += "</li>";
							}
						}
						content += "</ul>";
					}
					else {
						if ($("#searchCategories").has(tID2).length > 0) {
							content += "<h4>" + filterCat[i].title + "</h4>";
							content += "<ul id='" + tID + "'>";
							content += content2;
							content += "</ul>";
						};
					};
				}
				$("#headerCategory").html(content);
			}
		});
		/*
break;
		case "wfs":
		var catFile = folder + ID + "_" + e + "_cat.json";
		$.getJSON(catFile, function(data){
			var filterCat = data.searchMD.category;
			if (filterCat) {
				var content = "";
				var content2 = "";
				for (var i = 0; i < filterCat.length; i++) {
					var tit = filterCat[i].title;
					var tID = "title" + tit.slice(0, 3);
					var tID2 = "#" + tID;
					content2 = $(tID2).html();
					if (filterCat[i].subcat) {
						content += "<h4>" + filterCat[i].title + "</h4>";
						content += "<ul id='" + tID + "'>";
						if ($("#searchCategories").has(tID2).length > 0) {
							content += content2;
							for (var j = 0; j < filterCat[i].subcat.length; j++) {
								var aID = "#" + filterCat[i].subcat[j].title.slice(0, 5);
								if ($("#searchCategories").has(aID).length > 0) {
									var content3 = $(aID).html();
									var acount = content3.split("(");
									acount = acount[1].split(")");
									acount = parseInt(acount[0]);
									var acountges = acount + parseInt(filterCat[i].subcat[j].count);
									var content4 = filterCat[i].subcat[j].title + " (" + acountges + ")";
									content = content.replace(content3, content4);
								}
								else {
									content += "<li>";
									content += "<a id=" + filterCat[i].subcat[j].title.slice(0, 5) + " href='javascript:newSearchCloud(\"" + filterCat[i].subcat[j].filterLink + "\",\"" + data.searchMD.searchId + "\",\"all\")'>" + filterCat[i].subcat[j].title + " (" + filterCat[i].subcat[j].count + ")</a>";
									content += "</li>";
								}
							}
						}
						else {
							for (var j = 0; j < filterCat[i].subcat.length; j++) {
								content += "<li>";
								content += "<a id=" + filterCat[i].subcat[j].title.slice(0, 5) + " href='javascript:newSearchCloud(\"" + filterCat[i].subcat[j].filterLink + "\",\"" + data.searchMD.searchId + "\",\"all\")'>" + filterCat[i].subcat[j].title + " (" + filterCat[i].subcat[j].count + ")</a>";
								content += "</li>";
							}
						}
						content += "</ul>";
					}
					else {
						if ($("#searchCategories").has(tID2).length > 0) {
							content += "<h4>" + filterCat[i].title + "</h4>";
							content += "<ul id='" + tID + "'>";
							content += content2;
							content += "</ul>";
						};
					};
				}
				$("#headerCategory").html(content);
			}
		});
		break;
		case "wmc":
		var catFile = folder + ID + "_" + e + "_cat.json";
		$.getJSON(catFile, function(data){
			var filterCat = data.searchMD.category;
			if (filterCat) {
				var content = "";
				var content2 = "";
				for (var i = 0; i < filterCat.length; i++) {
					var tit = filterCat[i].title;
					var tID = "title" + tit.slice(0, 3);
					var tID2 = "#" + tID;
					content2 = $(tID2).html();
					if (filterCat[i].subcat) {
						content += "<h4>" + filterCat[i].title + "</h4>";
						content += "<ul id='" + tID + "'>";
						if ($("#searchCategories").has(tID2).length > 0) {
							content += content2;
							for (var j = 0; j < filterCat[i].subcat.length; j++) {
								var aID = "#" + filterCat[i].subcat[j].title.slice(0, 5);
								if ($("#searchCategories").has(aID).length > 0) {
									var content3 = $(aID).html();
									var acount = content3.split("(");
									acount = acount[1].split(")");
									acount = parseInt(acount[0]);
									var acountges = acount + parseInt(filterCat[i].subcat[j].count);
									var content4 = filterCat[i].subcat[j].title + " (" + acountges + ")";
									content = content.replace(content3, content4);
								} else {
									content += "<li>";
									content += "<a id=" + filterCat[i].subcat[j].title.slice(0, 5) + " href='javascript:newSearchCloud(\"" + filterCat[i].subcat[j].filterLink + "\",\"" + data.searchMD.searchId + "\",\"all\")'>" + filterCat[i].subcat[j].title + " (" + filterCat[i].subcat[j].count + ")</a>";
									content += "</li>";
								}
							}
						} else {
							for (var j = 0; j < filterCat[i].subcat.length; j++) {
								content += "<li>";
								content += "<a id=" + filterCat[i].subcat[j].title.slice(0, 5) + " href='javascript:newSearchCloud(\"" + filterCat[i].subcat[j].filterLink + "\",\"" + data.searchMD.searchId + "\",\"all\")'>" + filterCat[i].subcat[j].title + " (" + filterCat[i].subcat[j].count + ")</a>";
								content += "</li>";
							}
						}
						content += "</ul>";
					} else {
						if ($("#searchCategories").has(tID2).length > 0) {
							content += "<h4>" + filterCat[i].title + "</h4>";
							content += "<ul id='" + tID + "'>";
							content += content2;
							content += "</ul>";
						};
					};
				}
				$("#headerCategory").html(content);
			}
		});
		break;
	} */
}
/*

function searchNewCat(Link,ID){
	var searchstring = "../php/mod_start_search.php?cat=Dienst&searchId=" + ID + "&" + Link;
	
	$.getJSON(searchstring, function(data){
		$("#tabs-2").queue(function(){
			loadTabDienst(data.searchId);
			$(this).dequeue();
		})

		$("#headerDienst").queue(function(){
			headerDetail(data.searchId);
			$(this).dequeue();
		})
		
		$("#headerCategory").queue(function(){
			loadCategories(data.searchId);
			$(this).dequeue();
		})
		$("#headerAll").queue(function(){
			headerAll(data.searchId);
			$(this).dequeue();
		})
	})
}
*/

