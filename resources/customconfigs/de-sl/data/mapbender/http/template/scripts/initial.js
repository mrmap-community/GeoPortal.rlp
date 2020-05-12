/**
 * @author schaef
 */

var pg_bhc = '#fff',
        pg_thc = '#fff',
        pg_bgrc = '#e0e0e0',
        pg_bc = '#ccc',
        pg_tc = '#000',
        pg_bgrhc = '#03439d',
        pg_titc = '#5A5B5F';

/*********** Startfunktion bei Aufruf der Seite ***********/

$(document).ready(function() {
    uid = "";
    searchId = "";
    incontentRightWidth = $("#incontentRight").innerWidth();
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
    orderBy = Value["orderBy"];
    callId = Value["callId"];
    lastFile = Value["lastFile"];
    mb_user_id = Value["mb_user_id"];
    if (Value["catid"]) {
        tabidx = Value["catid"];
    } else {
        tabidx = null;
    }
    uid = Math.random() * 10000000000;
    uid = Math.round(uid);
    uid = uid.toString();
    searchId = uid;


    getrTabsWidth();

//	$("#headerCategory").after("<div id='categoryContent'></div>");

    $.ajax({
        url: '../geoportal/mod_saveSearch.php',
        dataType: 'json',
        success: function(data) {
//			console.info(data);
            displaySavedSearch(data);
        }
    })

    if (callId != undefined) {
        var splitlastFile = lastFile.split("_");
        var splitlastFile2 = splitlastFile[0].split("/");
        var service = callId;
        if (service != "adr") {
            var page = splitlastFile[2].split(".");
            page = parseInt(page[0]);
        }
        searchId = splitlastFile2[2];
        var filterFile = folder + searchId + "_filter.json";
        pollingServ(page, searchId, service);
        pollingMeta(1, searchId);
        pollingAdress(searchId, tabidx);
        if (service == "wms" || service == "wmc" || service == "wfs") {
            showTab('#tabs-3', 2);
            $.getJSON(filterFile, function(data) {
                var string = data.searchFilter.origURL;
                var e = "#erg" + service;
                toggle1(page, e, searchId, string);
            })
        } else if (service == "adr") {
            showTab('#tabs-2', 1);
            pollingAdress(searchId, tabidx);
        } else {
            showTab('#tabs-4', 3);
            toggleOS(1, page, searchId);
        }
        $(".loader").hide();
        if (mb_user_id != 2) {
            $("#search_notlogged").hide();
            $("#savename").attr("value", searchText);
            $("#savesearchtext").attr("value", searchText);
            $("#search_logged").show();
        } else {
            $("#search_logged").hide();
            $("#search_notlogged").show();
        }
    } else {
        pollingServ(1, searchId, 'all');
        pollingMeta(1, uid);
        pollingAdress(uid, tabidx);
        var searchstring = "../php/mod_start_search.php?" + searchFilter + "&uid=" + uid;
        $.ajax({
            url: searchstring,
            dataType: 'json',
            cache: false,
            success: function(data) {
                searchId = data.searchId;
                uid = data.uid;
                mb_user_id = data.mb_user_id;
                $(".loader").hide();
                if (mb_user_id != 2) {
                    $("#search_notlogged").hide();
                    $("#savename").attr("value", searchText);
                    $("#savesearchtext").attr("value", searchText);
                    $("#search_logged").show();
                } else {
                    $("#search_logged").hide();
                    $("#search_notlogged").show();
                }
            }
        })
    }
})

$(window).resize(function() {
    getrTabsWidth();
})

function getrTabsWidth() {
    var divWidth = $("#incontent").css('width');
    var tabsWidth = parseInt(divWidth) - parseInt(incontentRightWidth);
    tabsWidth = tabsWidth + "px";
    $("#incontentLeft").css('width', tabsWidth);
}

/**************  Funktionen für den Suchheader ********/

function headerAll(ID) {
    var filterFile = folder + ID + "_filter.json";
    var target = "../php/mod_start_search.php?";
    $.getJSON(filterFile, function(data) {
        var content = "";
        var sF = data.searchFilter;
        var cat = "";
        if (sF.classes.length > 1) {
            cat = "all";
        } else {
            cat = sF.classes[0].name;
        }
        searchFilter = sF.origURL;
        if (sF.searchText) {
            content += "<a href='javascript:newSearch(\"searchText=" + sF.searchText.delLink + "\",\"" + ID + "\")' title='alle entfernen'><h3>" + sF.searchText.title + "</h3></a>";
            for (var i = 0; i < sF.searchText.item.length; i++) {
                content += "<a href='javascript:newSearch(\"" + sF.searchText.item[i].delLink + "\",\"" + ID + "\")' title='alle entfernen'>";
                content += sF.searchText.item[i].title;
                content += "</a>, ";
            }
        }
        if (sF.isoCategories) {
            content += "<a href='javascript:newSearch(\"" + sF.isoCategories.delLink + "\",\"" + ID + "\")' title='alle entfernen' ><h3>" + sF.isoCategories.title + "</h3></a>";
            for (var i = 0; i < sF.isoCategories.item.length; i++) {
                content += "<a href='javascript:newSearch(\"" + sF.isoCategories.item[i].delLink + "\",\"" + ID + "\")' title='alle entfernen'>";
                content += sF.isoCategories.item[i].title + "";
                content += "</a>, ";
            }
            content += "</br>";
        }
        if (sF.inspireThemes) {
            content += "<a href='javascript:newSearch(\"" + sF.inspireThemes.delLink + "\",\"" + ID + "\")' title='alle entfernen'><h3>" + sF.inspireThemes.title + "</h3></a>";
            for (var i = 0; i < sF.inspireThemes.item.length; i++) {
                content += "<a href='javascript:newSearch(\"" + sF.inspireThemes.item[i].delLink + "\",\"" + ID + "\")' title='alle entfernen'>";
                content += sF.inspireThemes.item[i].title;
                content += "</a>, ";
            }
        }
        if (sF.customCategories) {
            content += "<a href='javascript:newSearch(\"" + sF.customCategories.delLink + "\",\"" + ID + "\")' title='alle entfernen'><h3>" + sF.customCategories.title + "</h3></a>";
            for (var i = 0; i < sF.customCategories.item.length; i++) {
                content += "<a href='javascript:newSearch(\"" + sF.customCategories.item[i].delLink + "\",\"" + ID + "\")' title='alle entfernen'>";
                content += sF.customCategories.item[i].title;
                content += "</a>, ";
            }
        }
        $("#headerAll").html(content);
    })
}

function clearHeader() {
    $(".displayService").hide();
}

function showHeader() {
    $(".displayService").show();
}

function clearLegend() {
    $(".displayLegend").hide();
}

function showLegend() {
    $(".displayLegend").show();
}

function headerDetail(ID) {
    var filterfile = folder + ID + "_filter.json";
    $.getJSON(filterfile, function(data) {
        var filterRes = data.searchFilter.maxResults;
        var filterOrd = data.searchFilter.orderFilter;
        var content = "";
        content += '<div style="float:left;">';
        content += '<form action="" method="post" onsubmit="return false;">';
        content += '<fieldset>';
        content += '<legend>' + filterOrd.header + '</legend>';
        content += '<select onchange="changeHits(this.options[this.selectedIndex].value,\'' + ID + '\')">';
        content += '<option value="">' + filterOrd.title + '</option>';
        for (var i = 0; i < filterOrd.item.length; i++) {
            content += '<option value="' + filterOrd.item[i].url + '">' + filterOrd.item[i].title + '</option>'
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
        content += '<select onchange="changeHits(this.options[this.selectedIndex].value,\'' + ID + '\')">';
        content += '<option value="">' + filterRes.title + '</option>';
        for (var i = 0; i < filterRes.item.length; i++) {
            content += '<option value="' + filterRes.item[i].url + '">' + filterRes.item[i].title + '</option>'
        }
        content += '</select>';
        content += '</fieldset>';
        content += '</form>';
        content += '</div>';
        $("#headerDienst").html(content);
    });
}

function changeHits(mode, ID) {
    var sID = MD5(ID);
    mode = encodeURI(mode);
    var searchstring = "../php/mod_callMetadata.php?searchId=" + sID + "&" + mode;
    $(".countService").replaceWith("");
    $("#categoryContent").html("");
    $("#search-container-dienste nobr").remove();
    pollingServ(1, sID, "all");
    $(".loader").show();

    $.getJSON(searchstring, function(data) {
        if (data) {
            searchId = data.searchId;
            uid = data.uid;
            mb_user_id = data.md_user_id;
        }
        $(".loader").hide();
    })
}

/***** Funktionen für die Darstellung der Tabs **********/

function loadTabServPol(p, ID, serv) {
    var filterfile = folder + ID + "_filter.json";
    $.getJSON(filterfile, function(data) {
        var sF = data.searchFilter;
        var id1 = sF.origURL.split("&");
        for (var i = 0; i < id1.length; i++) {
            var tmp = id1[i].split("=");
            if (tmp[0].toLowerCase() === "userid") {
                mb_user_id = decodeURIComponent(tmp[1]);
                break;
            }
        }

        $("#tabs-3").html(function() {
            var content = "";
            content += '<div class="search-container">';
            content += '<div id="search-container-dienste">';
            content += '<form name="formmaps" action="/mapbender/frames/index.php" method="get">';
            content += '<input name="zoomToLayer" type="hidden" value="0" />';
            content += '<input name="mb_user_myGui" type="hidden" value="Geoportal-SL" />';
            for (var i = 0; i < sF.classes.length; i++) {
                var toggle1ID = "erg" + sF.classes[i].name;
                if (sF.classes[i].name == serv) {
                    p = p;
                } else {
                    p = 1;
                }
                content += '<div class="search-header" onclick="toggle1(' + p + ',\'#' + toggle1ID + '\',\'' + ID + '\',\'' + sF.origURL + '\')">';
                content += '<img id="' + toggle1ID + 'Arrow" src="../img/search/arrow_e.gif" style="height:12px;float:left;" />';
                content += '<img class="icon" src="../img/search/s_' + sF.classes[i].name + '.png" />';
                content += '<h2>' + sF.classes[i].title + '</h2>';
                content += '<p id="' + sF.classes[i].name + 'Count"></p>'
                content += '</div>';
                content += '<div id="' + toggle1ID + '" style="display:none"></div>';
                content += '<br />';
            }
            content += '</form>';
            content += '</div> ';
            content += '</div>';
            return content;
        });
        $("#search-container-dienste").queue(function() {
            if (sF.classes.length > 1) {
                if (serv == "wms") {
                    pollingWMS(p, ID);
                    pollingWFS(1, ID);
                    pollingWMC(1, ID);
                } else if (serv == "wfs") {
                    pollingWMS(1, ID);
                    pollingWFS(p, ID);
                    pollingWMC(1, ID);
                } else if (serv == "wmc") {
                    pollingWMS(1, ID);
                    pollingWFS(1, ID);
                    pollingWMC(p, ID);
                } else {
                    pollingWMS(p, ID);
                    pollingWFS(p, ID);
                    pollingWMC(p, ID);
                }
            } else {
                var service = sF.classes[0].name;
                switch (service) {
                    case 'wms':
                        pollingWMS(p, ID);
                        break;
                    case 'wfs':
                        pollingWFS(p, ID);
                        break;
                    case 'wmc':
                        pollingWMC(p, ID);
                        break;
                }
            }
            $(this).dequeue();
        })
    });
}

function initMetatab(p, UID) {
    if (typeof (categoryRegex) !== 'undefined'
            && document.location.pathname.search(categoryRegex) != -1) {
        return;
    }
    $.get(folder + UID + "_os.xml", function(data) {
        var osIndex = data.getElementsByTagName("opensearchinterface");
        $.each([osIndex], function(index, value) {
            for (i = 0; i < value.length; i++) {
                try {
                    var osId = i + 1;
                    var content = "";
                    content += '<div class="search-header" onclick="toggleOS(\'' + osId + '\',\'' + p + '\',\'' + UID + '\')">';
                    content += '<img id="ergOS-' + osId + '-Arrow" src="../img/search/arrow_e.gif" style="height:12px; margin-right:10px" />';
                    content += '<h2>' + value[i].firstChild.nodeValue + '</h2>';
                    content += '<p id="os-' + osId + '"></p></div>';
                    content += '<div id="ergOS-' + osId + '" style="display:none;">';
                    content += '<div id="ergOS-' + osId + 'Content"></div></br class="clr">';
                    content += '<div id="ergOS-' + osId + 'Page" class="jPaginate"></div>';
                    content += '</div>';
                    $("#tabs-4").append(content);
                    pollingMeta2(osId, p, UID);
                } catch (e) {

                }
            }
        });
    });
}

function initAdresstab(UID, tabidx) {
    var ffile = folder + UID + "_geom.xml";
    $.get(ffile, function(data) {
        var index = 0;
        var result = {};
        $('member', data).each(function() {
            index++;
            var geomIndex = $(this).attr('id');
            var cat_id = $(this).find('title').attr('category_id');
            var title = $(this).find('title').text();
            if (typeof (result[cat_id]) === 'undefined') {
                result[cat_id] = {title: $(this).find('title').attr('category'), data: "", num: 0};
            }//calltype=category&callId=Blattschnitte_mbwms320580&page=1&pathname=%2Fde%2Fstartseite%2Fbasisdaten.html
            result[cat_id].num++;
            result[cat_id].data += "<div class='search-item'>";
            result[cat_id].data += "<div class='search-nr'>" + result[cat_id].num + "</div>";
            result[cat_id].data += "<div class='search-title all' ><a href='/mapbender/geoportal/mod_index.php?mb_user_myGui=Geoportal-SL&geomuid=" + UID + "&geomid=" + geomIndex + "&callId=adr-" + cat_id + "|" + result[cat_id].num + "-" + ffile + "'>" + title + "</a></div>";
            result[cat_id].data += "</div>";
        });
        var content = "";
        content += "<div class='search-container'>";
        content += "<div id='search-container-adress'>";
        content += "<div id='adress-tabs'>";
        content += "<ul>";
        for (cat_id in result) {
            content += '<li><a href="#' + cat_id + '">' + result[cat_id].title + '</a></li>';
        }
        content += "</ul>";
        for (cat_id in result) {
            content += '<div id="' + cat_id + '">';
            content += result[cat_id].data;
            content += "</div>";
        }
        content += "</div>";
        content += "</div>";
        content += "</div>";

        if ($("span.countAdress").length > 0) {
            $("span.countAdress").each(function() {
                this.html(index)
            });
        } else {
            $("<nobr><span class='countAdress'>" + index + "</span> Treffer</nobr>").insertBefore("#statusAdressBod");
            $("<span class='countAdress'>" + index + "</span>").insertBefore("#statusAdressTab");
        }
        $("#tabs-2").html(content);
        $('#adress-tabs').tabs();
        if (tabidx && tabidx != null) {
            $('#adress-tabs').tabs('select', tabidx);
        }
    });
}

function loadMetaCount(osId, p, UID) {
    if (typeof (categoryRegex) !== 'undefined'
            && document.location.pathname.search(categoryRegex) != -1) {
        return;
    }
    var countos1 = "";
    var countos2 = "";
    var countos3 = "";
    var countges
    var searchFile = folder + UID + "_os" + osId + "_" + p + ".xml";
    $.get(searchFile, function(data) {
        var totRes = data.getElementsByTagName("totalresults");
        $.each([totRes], function(index, value) {
            var countos = parseInt(value[0].firstChild.nodeValue);
            if (value[0].firstChild.nodeValue != "") {
                $("#os-" + osId + "").append(' (' + value[0].firstChild.nodeValue + ' Treffer)')
            }
            if ($("#search-container-meta").has('.countMeta').length > 0) {
                var countserv = $(".countMeta:eq(0)").text();
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

function loadtime(p, e, ID) {
    var countwms = "";
    var countwfs = "";
    var countwmc = "";
    var countserv;
    switch (e) {
        case 'wms':
            $.getJSON(folder + ID + "_wms_" + p + ".json", function(data) {
                $("#wmsCount").append(' (' + data.wms.md.nresults + ' Treffer in ' + Math.round(data.wms.md.genTime * 100) / 100 + ' Sekunden)');
                countwms = parseInt(data.wms.md.nresults);
                if ($("#search-container-dienste").has('.countService').length > 0) {
                    var countserv = $(".countService:eq(0)").text();
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
            $.getJSON(folder + ID + "_wfs_" + p + ".json", function(data) {
                $("#wfsCount").append(' (' + data.wfs.md.nresults + ' Treffer in ' + Math.round(data.wfs.md.genTime * 100) / 100 + ' Sekunden)')
                countwfs = parseInt(data.wfs.md.nresults);
                if ($("#search-container-dienste").has('.countService').length > 0) {
                    var countserv = $(".countService:eq(0)").text();
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
            $.getJSON(folder + ID + "_wmc_" + p + ".json", function(data) {
                $("#wmcCount").append(' (' + data.wmc.md.nresults + ' Treffer in ' + Math.round(data.wmc.md.genTime * 100) / 100 + ' Sekunden)')
                countwmc = parseInt(data.wmc.md.nresults);
                if ($("#search-container-dienste").has('.countService').length > 0) {
                    var countserv = $(".countService:eq(0)").text();
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
}

/*********** Funktionen für die TagClouds  ***********/

function loadTagCloud(e, ID) {
    var cfile = folder + ID + "_" + e + cFileEnd;
    var target = "#erg" + e;
    var Cloudcontent = "";
    $.getJSON(cfile, function(data) {
        var toggle2ID = e + "Cloud";
        var tags = data.tagCloud.tags;
        Cloudcontent += '<div class="tagcloud" onclick="toggle2(\'#' + toggle2ID + '\')">';
        Cloudcontent += '<img id="' + toggle2ID + 'Arrow" src="../img/search/arrow_e.gif" style="height:10px" />';
        Cloudcontent += '<h3>' + data.tagCloud.title + '</h3>';
        Cloudcontent += '</div>';
        Cloudcontent += '<div class="cloud" id="' + toggle2ID + '" style="display:none; overflow:auto">';
        for (var i = 0; i < tags.length; i++) {
            Cloudcontent += "<a href='javascript:newSearch(\"" + tags[i].url + "\",\"" + ID + "\")' title='" + tags[i].title + "' style='font-size:" + tags[i].weight + "px'><li style='font-size:" + tags[i].weight + "px'>" + tags[i].title + "</li></a>";
        }
        Cloudcontent += "</div></br class='clr'>";
        $(target).html(Cloudcontent);
    });
}


function newSearch(URL, ID) {
    var sID = MD5(ID);
//	uid = MD5(uid);
    URL = encodeURI(URL);
    if (URL.match(/cat.+/)) {
        var searchstring = "../php/mod_start_search.php?searchId=" + sID + "&uid=" + sID + "&" + URL;
    } else {
        var searchstring = "../php/mod_start_search.php?cat=Dienst&searchId=" + sID + "&uid=" + sID + "&" + URL;
    }
    $(".countMeta").replaceWith("");
    $(".countService").replaceWith("");
    $("#categoryContent").html("");
    $("#tabs-4").html("");
    $("nobr").remove();
    pollingServ(1, sID, "all");
    pollingMeta(1, sID);
    $(".loader").show();

    $.getJSON(searchstring, function(data) {
        if (data) {
            searchId = data.searchId;
            uid = data.uid;
            mb_user_id = data.md_user_id;
        }
        $(".loader").hide();
    })
}



//********* Funktionen für das Paging **********

function loadPaging(e, File, sFilter, ID) {
    $.getJSON(File, function(data) {
        var target = "#" + e + "Page";
        var pageInf = data[e].md;
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
            $(target).paginate({
                count: pageCount,
                start: hitPage,
                display: pageDisplay,
                border: true,
                border_color: pg_bc,
                text_color: pg_tc,
                background_color: pg_bgrc,
                border_hover_color: pg_bhc,
                text_hover_color: pg_thc,
                background_hover_color: pg_bgrhc,
                images: false,
                mouse: 'press',
                onChange: function(page) {
                    var searchstring = "";
                    sFilter = encodeURI(sFilter);
                    searchstring += "../php/mod_callMetadata.php?searchId=" + ID + "&searchResources=" + e + "&" + sFilter + "&searchPages=" + page;
                    $.getJSON(searchstring, function(data) {
                        $("#tabs-3").queue(function() {
                            openServPage(page, e, ID);
                            $(this).dequeue();
                        })
                    })
                }
            });
        }
    });
}

function getLoader() {
    var content = '';
    content += '<div id="search-container-dienste">';
    content += '<img id="loadStatus" src="../img/search/loader_lightblue.gif" height="15 px" align="center">';
    content += '</div>';
    return content;
}

function hasWmsFreeLayer(layer, flag, source) {
    if (layer.subLayer) {
        for (var j = 0; j < layer.subLayer.length; j++) {
            if (hasWmsFreeLayer(layer.subLayer[j], false, false)) {
                flag = true;
            }
        }
    }
    if (!source && !layer.logged) {
        flag = true;
    }
    return flag;
}
function hasWmsNotFreeLayer(layer, flag, source) {
    if (layer.subLayer) {
        for (var j = 0; j < layer.subLayer.length; j++) {
            if (hasWmsNotFreeLayer(layer.subLayer[j], false, false)) {
                flag = true;
            }
        }
    }
    if (!source && layer.logged) {
        flag = true;
    }
    return flag;
}

function createWmsLayerItem(layer, callIdParams, callIds, content, source) {
    if (layer.subLayer) {
        callIds.push(layer.id);
    }
    var publish = true;
    if (source) {
        if (layer.subLayer) {
            publish = !hasWmsNotFreeLayer(layer, false, true);
        } else {
            publish = !layer.logged;
        }
    } else {
        publish = !layer.logged;
    }
    if (publish) {
        content += ' <div class="search-mapicons">';
        if (layer.downloadOptions) {
            var ids = "";
            for (uuid in layer.downloadOptions) {
                ids += "," + layer.downloadOptions[uuid].uuid;
            }
            content += '<a href="../../mapbender/php/mod_getDownloadOptions.php?id=' + (ids.substring(1)) + '&amp;outputFormat=html&amp;languageCode=de" target="_blank"'
                    + ' onclick="downloadWindow = window.open(this.href,\'downloadWindow\',\'width=600,height=400,left=100,top=100,scrollbars=yes,menubar=yes,toolbar=yes,resizable=yes\');downloadWindow.focus(); return false">'
                    + '<img class="category-download" src="../img/gnome/document-save-small.png" title="Download" alt="Download"></a>';
        }
        if (layer.title.indexOf('SL') === 0) {
            content += '<img class="legend-icon" src="../img/search/icn_eingeschraenketes_netz.png" border="0" title="nur über Intranet abrufbar"/>';
        }
        if (layer.bbox) {
            content += '  <a href="' + layer.mburl + '&calltype=category&mb_myBBOX=' + layer.bbox + '&callId=' + callIds.toString() + '&page=' + callIdParams["page"] + '&pathname=' + encodeURIComponent(callIdParams["pathname"]) + '" >';
            content += '  <img src="../img/search/icn_zoommap.png" border="0" title="Auf Ebenenausdehnung zoomen" /></a> ';
        }
        content += '  <a href="' + layer.mburl + '&calltype=category&callId=' + callIds.toString() + '&page=' + callIdParams["page"] + '&pathname=' + encodeURIComponent(callIdParams["pathname"]) + '" >';
        content += '  <img src="../img/search/icn_map.png" border="0" title="In Karte aufnehmen" /></a>';

        content += ' </div>';
    }

    var previewURL = "";
    if (layer.previewURL) {
        previewURL = '<div class="category-preview" data-imgsrc="' + layer.previewURL + '"></div>';
    }
    var mdLink = previewURL + '<b' + (layer.mdLink ? ' class="js-call-metadata" data-metadataurl="' + layer.mdLink + '"' : '') + '>' + layer.title + '</b>';
    if (layer.subLayer) {
        var displaySubLayer = false;
        content += ' <div class="search-titleicons">';
        if (layer.permission !== null && layer.permission !='true') {
            content += ' <img class="openCloseImg" src="../img/search/icn_encrypted.png" alt="Schloss" title="Dienste Berechtigung" />';
        }
        if ($.inArray(layer.id, callIdParams["callIdCat"]) != -1) {
            displaySubLayer = true;
            content += '  <img class="openCloseImg" src="../img/search/icn_wms2_white.png" alt="WMS" title="Sublayers" />';
        } else {
            displaySubLayer = false;
            content += '  <img class="openCloseImg" src="../img/search/icn_wms_white.png" alt="WMS" title="Sublayers" />';
        }
        content += ' </div>';
        content += ' <div class="category-div-inline"><div class="search-info-dep2">' + mdLink + '</div>';
        content += ' <div class="layerInfo">' + layer.abstr + '</div></div>';

        content += '  <ul class="search-tree subLayers' + (displaySubLayer ? "" : " displayNone") + '">';
        for (var j = 0; j < layer.subLayer.length; j++) {
            content += '<li class="search-item category-item" >';
            content += createWmsLayerItem(layer.subLayer[j], callIdParams, callIds, "", false);
            content += '</li>'
        }
        content += '  </ul>';
    } else {
        content += ' <div class="search-titleicons">';
        if (layer.permission !== null && layer.permission !='true') {
            content += '  <img class="openCloseImg" src="../img/search/icn_encrypted.png" alt="Schloss" title="Layer Berechtigung" />';
        }
        content += '  <img src="../img/search/icn_layer2.png" alt="Layer" title="Layer" />';
        content += ' </div>';
        content += ' <div class="category-div-inline"><div class="search-info-dep2">' + mdLink + '</div>';
        content += ' <div class="layerInfo">' + layer.abstr + '</div></div>';
    }
    content += ' <div class="search-text layerInfo js-search-text"><span class="preview-text">Quelle</span>: ' + layer.source + '</div>';
    return content;
}

function createWmcLayerItem(layer, callIdParams, content) {
    content += '  <div class="search-mapicons">';
    if (layer.downloadOptions) {
        var ids = "";
        for (uuid in layer.downloadOptions) {
            ids += "," + layer.downloadOptions[uuid].uuid;
        }
        content += '<a href="../../mapbender/php/mod_getDownloadOptions.php?id=' + (ids.substring(1)) + '&amp;outputFormat=html&amp;languageCode=de" target="_blank"'
                + ' onclick="downloadWindow = window.open(this.href,\'downloadWindow\',\'width=600,height=400,left=100,top=100,scrollbars=yes,menubar=yes,toolbar=yes,resizable=yes\');downloadWindow.focus(); return false">'
                + '<img class="category-download" src="../img/gnome/document-save-small.png" title="Download" alt="Download"></a>';
    }
    content += '   <a href="' + layer.mburl + '&calltype=category&callId=' + layer.title + '&page=' + callIdParams["page"] + '&pathname=' + encodeURIComponent(callIdParams["pathname"]) + '">';
    content += '   <img src="../img/search/icn_map.png" border="0" title="In Karte aufnehmen" /></a>';
    content += '  </div>';
    content += '  <div class="search-titleicons">';
    content += '   <img src="../img/search/Mapset.png" alt="Kartenzusammenstellung - Bild" title="Kartenzusammenstellung">';
    content += '  </div>';
    var previewURL = "";
    if (layer.previewURL) {
        previewURL = '<div class="category-preview" data-imgsrc="' + layer.previewURL + '"></div>';
    }
    var mdLink = previewURL + '<b' + (layer.mdLink ? ' class="js-call-metadata" data-metadataurl="' + layer.mdLink + '"' : '') + '>' + layer.title + '</b>';
    content += ' <div class="category-div-inline"><div class="search-info-dep2">' + mdLink + '</div>';
    content += ' <div class="layerInfo">' + layer.abstr + '</div></div>';
    content += '<div class="search-text layerInfo js-search-text"><span class="preview-text">Quelle</span>: ' + layer.source + '</div>';
    return content;
}

function createCategoryHtmlList(elm, data, millisec, callIdParams) {
    var time = Math.round(parseFloat(millisec / 10)) / 100;
    var srvList = data.result.srv;
    var content = '<div class="results"><span class="services">Darstellungsdienste</span><br/><span class="hits">(' + data.result.md.nresults + ' Treffer in ' + time + ' Sekunden)</span></div>';
    content += '<ul class="search-tree">';
    for (var i = 0; i < srvList.length; i++) {
        content += '<li class="search-item category-item" >';
        if (srvList[i].type == "WMS") {
            content += createWmsLayerItem(srvList[i], callIdParams, [], "", true);
        } else if (srvList[i].type == "WMC") {
            content += createWmcLayerItem(srvList[i], callIdParams, "");
        }
        content += '</li>';
    }
    content += '</ul>';
    elm.html(content);
    elm.find('.js-call-metadata').bind('mouseover', function(e) {
        var $this = $(e.target);
        var prdiv = $this.parent().find('.category-preview');
        var p = $this.offset();
        var img = '<img id="catPreviewImg" style="top:' + (parseInt(p.top) + 30) + 'px;left:' + p.left + 'px;" class="search-icons-preview" src="' + prdiv.attr('data-imgsrc') + '" title="Vorschau" alt="Fehler in Vorschau">';
        $(img).appendTo('body');
    });
    elm.find('.js-call-metadata').bind('mouseout', function(e) {
        $('#catPreviewImg').remove();
    });
    elm.find('.js-call-metadata').bind('click', function(e) {
        var url = $(e.target).attr('data-metadataurl');
        var dialog = $('<div id="metadata-dialog"><iframe class="metadata-iframe" src="' + url + '" scroll="no"></iframe></div>').dialog({
            width: 800,
            height: 500,
            autoOpen: false,
            draggable: true,
            buttons: {
                "schließen": function() {
                    $(this).dialog("destroy");
                    $('body #metadata-dialog').remove();
                }
            }
        });
        dialog.dialog('open');
    });
    $(".search-item").css({listStyleType: "none"});
    $(".results").css({color: pg_titc, textAlign: "left"});
    $("img.openCloseImg").bind("click", function(e) {
        if (this.src.indexOf("icn_wms2_white") != -1) {//close
            $(this).attr("src", $(this).attr("src").replace(/icn_wms2_white/, "icn_wms_white"));
            $(this).parent().parent().find('>ul').addClass("displayNone");
        } else if (this.src.indexOf("icn_wms_white") != -1) {//open
            $(this).attr("src", $(this).attr("src").replace(/icn_wms_white/, "icn_wms2_white"));
            $(this).parent().parent().find('>ul').removeClass("displayNone");
        }
    });
}

function createLegend() {
    var content = '<b>Legende</b><br/>';
    content += '<table class="legend-table">';
    content += '<tr><td><img class="legend-icon" src="../img/search/icn_zoommap.png" border="0" /> Hinzuladen auf Ausdehnung des Darstellungsdienstes</td>';
    content += '<td><img class="legend-icon" src="../img/gnome/document-save-small.png" border="0" /> Downloadlink</td></tr>';
    content += '<tr><td><img class="legend-icon" src="../img/search/icn_map.png" border="0" /> Hinzuladen auf letzte Kartenansicht</td>';
    content += '<td><img class="legend-icon" src="../img/search/icn_eingeschraenketes_netz.png" border="0" /> nur über Intranet abrufbar</td></tr>';
    content += '<tr><td><img class="legend-icon" src="../img/search/icn_wms.png" border="0" /> WMS mit Sublayers</td>';
    content += '<td><img class="legend-icon" src="../img/search/icn_encrypted.png" border="0" /> Zugangsbeschränkung</td></tr>';
    content += '<tr><td><img src="../img/search/Mapset.png" alt="Kartenzusammenstellung - Bild" title="Kartenzusammenstellung"> WMC Dokument (Kartenzusammenstellung)</td><td></td></tr>';
    content += '</table>';
    return content;
}

function loadCreateCategories(sFilter, elmStr) {
//    var searchFilter ="'.urlencode($matches[1][0]).'";
    $(getLoader()).appendTo("#incontent");
    $('.item-page').remove();
    $('.legend-list').css({margin: "0px", padding: "0px"});
    $("#search-container-dienste").css({height: "15px", margin: "0px 0px 10px", padding: "0px 0px 10px"});
    $("#legend-list").css({margin: "0px", padding: "0px"});
    $("<div/>", {id: "catPagesList", width: "100%"}).appendTo("#incontent");
    $("<div/>", {id: "catPageContent", width: "100%"}).css("text-align", "center").appendTo("#incontent");
    $(createLegend()).appendTo("#incontent");
    $("#incontent").css({'padding-bottom': "0px"});
    $("#loadStatus").show();

    var help = document.location.href.split("?");
    var callIdParams = {};
    callIdParams["pathname"] = window.location.pathname;
    callIdParams["page"] = 1;
    callIdParams["callIdCat"] = "";
    if (help[1]) {
        var help1 = help[1].split("&");
        for (var i = 0; i < help1.length; i++) {
            var help2 = help1[i].split("=");
            try {
                callIdParams[help2[0]] = help2[1];
            } catch (e) {
                callIdParams[help2[0]] = '';
            }
        }
    }
    callIdParams["callIdCat"] = callIdParams["callIdCat"].split(",");

    var searchstring = "../geoportal/mod_create_category.php?searchResources=wms,wmc&orderby=title&searchText=" + sFilter;

    var millisec = new Date().getTime();
    $.getJSON(searchstring + "&page=" + callIdParams["page"], function(data) {
        var pageInf = data.result.md;
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
            $("#catPagesList").paginate({
                count: pageCount,
                start: hitPage,
                display: pageDisplay,
                border: true,
                border_color: pg_bc,
                text_color: pg_tc,
                background_color: pg_bgrc,
                border_hover_color: pg_bhc,
                text_hover_color: pg_thc,
                background_hover_color: pg_bgrhc,
                images: false,
                mouse: 'press',
                onChange: function(page) {
                    callIdParams["callIdCat"] = [];
                    callIdParams["page"] = page;
                    var millisec = new Date().getTime();
                    $("#loadStatus").show();
                    $.getJSON(searchstring + "&page=" + page, function(data) {
                        createCategoryHtmlList($("#catPageContent"), data, new Date().getTime() - millisec, callIdParams);
                        $("#loadStatus").hide();
                    })
                }
            });
        }
        createCategoryHtmlList($("#catPageContent"), data, new Date().getTime() - millisec, callIdParams);
        $("#loadStatus").hide();

    });
}

function loadPagingOS(e, p, UID) {
    $.get(folder + UID + "_os" + e + "_" + p + ".xml", function(data) {
        var resultInf = data.getElementsByTagName("npages");
        $.each([resultInf], function(index, value) {
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
                    border_color: pg_bc,
                    text_color: pg_tc,
                    background_color: pg_bgrc,
                    border_hover_color: pg_bhc,
                    text_hover_color: pg_thc,
                    background_hover_color: pg_bgrhc,
                    images: false,
                    mouse: 'press',
                    onChange: function(page) {
                        var searchstring = "";
                        var cat = e - 1;
                        searchstring += "../geoportal/mod_readOpenSearchResults.php?q=" + data.getElementsByTagName('querystring')[0].firstChild.nodeValue + "&request_id=" + UID + "&cat=" + cat + "&p=" + page;
                        $.get(searchstring, function(data) {
                            var target = "#ergOS-" + e;
                            $(target).queue(function() {
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


function loadCategories(e, ID) {
    var catFile = folder + ID + "_" + e + "_cat.json";
    $.getJSON(catFile, function(data) {
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
                                content += "<a id=" + filterCat[i].subcat[j].title.slice(0, 5) + " href='javascript:newSearch(\"" + filterCat[i].subcat[j].filterLink + "\",\"" + data.searchMD.searchId + "\")'>" + filterCat[i].subcat[j].title + " (" + filterCat[i].subcat[j].count + ")</a>";
                                content += "</li>";
                            }
                        }
                    }
                    else {
                        for (var j = 0; j < filterCat[i].subcat.length; j++) {
                            content += "<li>";
                            content += "<a id=" + filterCat[i].subcat[j].title.slice(0, 5) + " href='javascript:newSearch(\"" + filterCat[i].subcat[j].filterLink + "\",\"" + data.searchMD.searchId + "\")'>" + filterCat[i].subcat[j].title + " (" + filterCat[i].subcat[j].count + ")</a>";
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
                    }
                    ;
                }
                ;
            }
            $("#categoryContent").html(content);
        }
    });
}


// Funtionen für die darstellung der gespeicherten Suchen
function displaySavedSearch(data) {
    var content = "<div id='searchTable'>";
    for (var i in data) {
        var sid = Math.random() * 10000000000;
        sid = Math.round(sid);
        sid = sid.toString();
        sid = MD5(sid);
        var target = "edit" + data[i].id;
        //var target2 = "#" + target;
        content += "<table><tbody>";
        content += "<tr>";
        //content += "<td style='width: 200px;'><a href='/portal/index.php/de/suchergebnis?searchText="+ data[i].searchtext + "'>" + data[i].name + "</a></td>";
        content += "<td style='width: 200px;'><a href='javascript:newSearch(\"searchText=" + data[i].searchtext + "\",\"" + sid + "\")'>" + data[i].name + "</a></td>";
        content += "<td style='width: 16px; padding: 2px 2px 0pt 0pt;'><a href='javascript:displayEdit(\"#" + target + "\")'><img width='22' height='22' alt='" + data[i].name + " bearbeiten' src='../img/search/icn_edit.png'></a></td>";
        //content += "<td style='width: 16px; padding: 2px 2px 0pt 0pt;'><span onclick='function(if(confirm(\'Wollen Sie die Suche " + data[i].name + " löschen?\')){this.href+='&amp;ok=1'; editSearch('del')}else{return false;};)'><img width='22' height='22' alt='Suche löschen' src='../img/search/icn_delete.png'></a></td></tr>";
        content += "<td style='width: 16px; padding: 2px 2px 0pt 0pt;'><img width='22' height='22' alt='Suche löschen' src='../img/search/icn_delete.png' onclick='return editSearch(\"del\")'></td></tr>";
        content += "<tr><td colspan='3' id='" + target + "' style='display:none;'>";
        content += "<fieldset>";
        content += "<label for='name'>Name:</label>";
        content += "<input type='text' class='text' name='name' id='editName' value='" + data[i].name + "'>";
        content += "<br class='clr'>";
        content += "<label for='searchtext'>Suchbegriffe:</label>";
        content += "<input type='text' class='text' name='searchtext2' id='editSearchtext' value='" + data[i].searchtext + "'>";
        content += "<br class='clr'>";
        content += "</fieldset>";
        content += "<fieldset class='control'>";
        content += "<input type='hidden' id='editId' value='" + data[i].id + "'>";
        content += "<input type='button' value='Speichern' onclick='editSearch(\"upd\")'>";
        content += "</fieldset>";
        content += "</td>";
        content += "</tbody></table>";
    }
    content += "</div>";
    $(".search-saved").append(content);
}

function displayEdit(t) {
    $(t).show();
}

function editSearch(opt) {
    var searchN = $("#editName").val();
    var searchT = $("#editSearchtext").val();
    var searchI = $("#editId").val();
    var string = "../geoportal/mod_saveSearch.php?";
    if (opt == 'upd') {
        string += "name=" + searchN + "&searchtext=" + searchT + "&id=" + searchI;
    } else if (opt == 'save') {
        string += "name=" + $("#savename").val() + "&searchtext=" + $("#savesearchtext").val();
    } else {
        var x = confirm("Wollen Sie die Suche " + searchN + " löschen?");
        if (x == true) {
            var string = "../geoportal/mod_saveSearch.php?id=" + searchI;
        } else {
            return false;
        }
    }
    $.ajax({
        url: string,
        dataType: 'json',
        success: function(data) {
            $("#searchTable").remove();
            displaySavedSearch(data);
        }
    })
}
