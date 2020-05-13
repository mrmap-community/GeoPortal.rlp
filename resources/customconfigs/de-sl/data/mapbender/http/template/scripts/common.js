/**
 * @author schaef
 */

var searchText = "";
var registrationDepartments = "";
var isoCategories = "";
var inspireThemes = "";
var customCategories = "";
var regTimeBegin = "";
var regTimeEnd = "";
var timeBegin = "";
var timeEnd = "";
var searchBox = "";
var searchTypeBbox = "";
var searchResources = "";
var orderBy = "";
var searchFilter = "";
var folder = "../tmp/";
var searchId ="";
var callId="";
var lastFile="";
var uid ="";
var mb_user_id="";
var metafile
var servicefile = folder;
var cFileEnd = "_keywords.json";
var scookie = new Array();
var mb_user_id;
var incontentRightWidth;
var categoryRegex = "/startseite/";

Wappen = new Array(
	'DE',
	'DE-RP',
	'DE-BE',
	'DE-BR',
	'DE-BW',
	'DE-BY',
	'DE-HB',
	'DE-HE',
	'DE-HH',
	'DE-MV',
	'DE-NI',
	'DE-NW',
	'DE-SH',
	'DE-SL',
	'DE-SN',
	'DE-ST',
	'DE-TH'
);

$(function(){

// Tabs
	$('#tabs').tabs();
	//hover states on the static widgets
	$('#dialog_link, ul#icons li').hover(
		function() { $(this).addClass('ui-state-hover'); }, 
		function() { $(this).removeClass('ui-state-hover'); }
	);
});

function toggle1(p,e,ID,sFilter){	
	var ePfeil = e + "Arrow";
	var orgsrc = $(ePfeil).attr('src');
	if (orgsrc != undefined) {
		orgsrc = orgsrc.substring((orgsrc.length - 11));
		if (orgsrc == "arrow_e.gif") {
			$(ePfeil).attr("src", "../img/search/arrow_s.gif");
			$(ePfeil).height(6);
		}
		else {
			$(ePfeil).attr("src", "../img/search/arrow_e.gif");
			$(ePfeil).height(12);
		}
		$(e).toggle();
		switch (e) {
			case "#ergwms":
				if ($(e).has('.searchtree').length < 1) {
					openServ(p,folder + ID + "_wms_" + p + ".json", 'wms', e, 0, sFilter, ID);
				}
				break;
			case "#ergwfs":
				openServ(p,folder + ID + "_wfs_" + p + ".json", 'wfs', e, 0, sFilter, ID);
				break;
			case "#ergwmc":
				openServ(p,folder + ID + "_wmc_" + p + ".json", 'wmc', e, 0, sFilter, ID);
				break;
		}
	} else {
		window.setTimeout("toggle1(" + p + ",'" + e + "','" + ID + "','" +sFilter + "')",200);
	}
}

function toggleOS(e,p,MBID){
	var eArr = "#ergOS-" + e + "-Arrow";
	var dIv = "#ergOS-" + e;
	var orgsrc = $(eArr).attr('src');
	if (orgsrc == undefined) {
		window.setTimeout("toggleOS(" + e + ",'" + p + "','" + MBID + "')",200);
	} else {
		orgsrc = orgsrc.substring((orgsrc.length -11));
		if (orgsrc == "arrow_e.gif") {
			$(eArr).attr("src", "../img/search/arrow_s.gif");
			$(eArr).height(6);
		}
		else {
			$(eArr).attr("src", "../img/search/arrow_e.gif");
			$(eArr).height(12);
		}
	}
	$(dIv).toggle();
	openOS(e,p,MBID);
}

function toggle2(e){	
	var eArr = e + "Arrow"
	var orgsrc = $(eArr).attr('src');
	orgsrc = orgsrc.substring((orgsrc.length -11));
	if (orgsrc == "arrow_e.gif") {
		$(eArr).attr("src","../img/search/arrow_s.gif");
		$(eArr).height(5);
	} else {
		$(eArr).attr("src","../img/search/arrow_e.gif");
		$(eArr).height(10);
	}
	$(e).toggle();
}

function openclose3(e) {
	img="";
	node=e;
	for(i=0;i<10;i++) {
		if(node.nodeName.toLowerCase()=="img" && img=="") {
			img=node;
		}
		node=node.parentNode;
		if(node.nodeName.toLowerCase()=="li") {
			for(j=0;j<node.childNodes.length;j++) {
				if(node.childNodes[j].nodeName.toLowerCase()=="ul") {
					if(node.childNodes[j].style.display=="none") {
						node.childNodes[j].style.display="block";
						if(img.src.indexOf("icn_wms_plus")!=-1) img.src="../img/search/icn_wms2.png";
						if(img.src.indexOf("icn_wfs_plus")!=-1) img.src="../img/search/icn_wfs2.png";
						if(img.src.indexOf("icn_layer_plus")!=-1) img.src="../img/search/icn_layer2.png";
					} else {
						node.childNodes[j].style.display="none";
						if(img.src.indexOf("icn_wms2")!=-1) img.src="../img/search/icn_wms_plus.png";
						if(img.src.indexOf("icn_wfs2")!=-1) img.src="../img/search/icn_wfs_plus.png";
						if(img.src.indexOf("icn_layer2")!=-1) img.src="../img/search/icn_layer_plus.png";
					}
				}
			}
			break;
		}
	}
}

function showTab(tab1,tab2) {
	$("#tabs li:eq(0)").removeClass("ui-tabs-selected ui-state-active")
	$("#tabs-1").addClass("ui-tabs-hide");
	$(tab1).removeClass("ui-tabs-hide");
	$("#tabs li:eq("+ tab2 +")").addClass("ui-tabs-selected ui-state-active")
	if (tab1=="#tabs-3"){
		showHeader();
	}
}

function splitURL(url){
	var param = new Array();
	url = url.split("?");
	if (url.length == 2) {
		searchFilter = url[1];
		url = url[1].split("&");
		for (var i=0; i<url.length; i++){
			var temp = url[i].split("=");
			param[temp[0]] = temp[1];
		}
	}
	return param;
}

/************** Polling-Funktionen für die Dienste **************/
function pollingServ(p,ID,serv) {
    if(typeof(categoryRegex) !== 'undefined'
        && document.location.pathname.search(categoryRegex) !=-1){
        return;
    }
	var searchFile = folder + ID + "_filter.json";
	$.ajax({
		url: searchFile,
		dataType:'json',
		cache: false,
		error: function() {
			window.setTimeout("pollingServ(" + p + ",'" + ID + "','" + serv + "')",1000);
		},
		success: function() {
			loadTabServPol(p,ID,serv);
			headerAll(ID);
			headerDetail(ID);
		}
	});
}

function pollingWMS(p,ID) {
    if(typeof(categoryRegex) !== 'undefined'
        && document.location.pathname.search(categoryRegex) !=-1){
        return;
    }
	var searchFile = folder + ID + "_wms_" + p + ".json";
	$.ajax({
		url: searchFile,
		dataType:'json',
		cache: false,
		error: function() {
			window.setTimeout("pollingWMS(" + p + "," + ID + ")",500);
		},
		success: function() {
			loadtime(p,'wms',ID);
			loadTagCloud('wms',ID);
			loadCategories('wms',ID);
		}
	})
}

function pollingWFS(p,ID) {
    if(typeof(categoryRegex) !== 'undefined'
        && document.location.pathname.search(categoryRegex) !=-1){
        return;
    }
	var searchFile = folder + ID + "_wfs_" + p + ".json";
	$.ajax({
		url: searchFile,
		dataType:'json',
		cache: false,
		error: function() {
			window.setTimeout("pollingWFS('" + p + "," + ID + "')",500);
		},
		success: function() {
			loadtime(p,'wfs',ID);
			loadTagCloud('wfs',ID);
			loadCategories('wfs',ID);
		}
	})
}

function pollingWMC(p,ID) {
    if(typeof(categoryRegex) !== 'undefined'
        && document.location.pathname.search(categoryRegex) !=-1){
        return;
    }
	var searchFile = folder + ID + "_wmc_" + p + ".json";
	$.ajax({
		url: searchFile,
		dataType:'json',
		cache: false,
		error: function() {
			window.setTimeout("pollingWMC('" + p + "," + ID + "')",500);
		},
		success: function() {
			loadtime(p,'wmc',ID);
			loadTagCloud('wmc',ID);
			loadCategories('wmc',ID);
		}
	})
}


/**************** Polling für Metadata ***************/
function pollingMeta(p,UID){
    if(typeof(categoryRegex) !== 'undefined'
        && document.location.pathname.search(categoryRegex) !=-1){
        return;
    }
	var searchFile = folder + UID + "_os.xml";
	$.ajax({
		url: searchFile,
		dataType: 'xml',
		cache: false,
		error: function() {
			window.setTimeout("pollingMeta('" + p + "','" + UID + "')",1001);
		},
		success: function() {
			initMetatab(p,UID);
			
		}
	})
}

function pollingMeta2(osId,p,UID){
    if(typeof(categoryRegex) !== 'undefined'
        && document.location.pathname.search(categoryRegex) !=-1){
        return;
    }
	var searchFile = folder + UID + "_os" + osId + "_" + p + ".xml";
	$.ajax({
		url: searchFile,
		cache: false,
		dataType:'xml',
		error: function() {
			window.setTimeout("pollingMeta2('" + osId + "','" + p + "','" + UID + "')",500);
		},
		success: function() {
			loadMetaCount(osId,p,UID);
			
		}
	})
}

/********* Polling für Adressen **************/

function pollingAdress(UID, tabidx){
    if(typeof(categoryRegex) !== 'undefined'
        && document.location.pathname.search(categoryRegex) !=-1){
        return;
    }
//	var searchFile = folder + UID + "_geom.xml";
    var readyFile = folder + UID + "_geom_ready.xml";
	$.ajax({
		url: readyFile,
		dataType: 'xml',
		cache: false,
		error: function() {
//            if(tabidx){
                window.setTimeout("pollingAdress('" + UID + "','" + tabidx + "')",1000);
//            } else {
//                window.setTimeout("pollingAdress('" + UID + "')",1000);
//            }
		},
		success: function(data) {
//            if(tabidx){
                initAdresstab(UID, tabidx);
//            } else {
//                initAdresstab(UID);
//            }
		}
	})
}

/********* Funktionen aus startsearch von q4u **************/

function tou(elem,id,type,code) {
	if(elem.checked) {
		elem.checked=false;
		jQuery.post(
			"/mapbender/php/mod_acceptedTou_server.php",
			{
				method:"checkAcceptedTou",
				id:1,
				params: "{\"serviceType\":\""+type+"\",\"serviceId\":"+id+"}"
			},
			function(data) {
				if(data.result.success) {
					if(data.result.data==1) {
						elem.checked=true;
					} else {
						var fenster=window.open("/mapbender/template/php/termsofuse.php?" + code, "", "width=500,height=500,left=100,top=100,scrollbars=yes,resizable=no");
						fenster.focus();
					}
				}
			}
		);
		return false;
	}
	return true;
}

function tou2(thiselem,elem,id,type,code) {
	if(elem.checked) {
		return true;
	}
		jQuery.post(
		"/mapbender/php/mod_acceptedTou_server.php",
		{
			method:"checkAcceptedTou",
			id:1,
			params: "{\"serviceType\":\""+type+"\",\"serviceId\":"+id+"}"
		},
		function(data) {
			if(data.result.success==true) {
				if(data.result.data==1) {
					window.location.href=thiselem.href;
				} else {
					var fenster=window.open("/mapbender/template/php/termsofuse.php?link=1&" + code, "TermsOfUse", "width=500,height=500,left=100,top=100,scrollbars=yes,resizable=yes");
					fenster.focus();
				}
			}
		}
	);
	return false;
}

function tou3(thiselem,elem,id,type,code) {
	if(elem.checked) {
		return true;
	}
	jQuery.post(
	"/mapbender/php/mod_acceptedTou_server.php",
		{
			method:"checkAcceptedTou",
			id:1,
			params: "{\"serviceType\":\""+type+"\",\"serviceId\":"+id+"}"
		},
		function(data) {
			if(data.result.success==true) {
				if(data.result.data==1) {
					elem.checked=true;
					thiselem.form.submit();
					found=true;
				} else {
					var fenster=window.open("/mapbender/template/php/termsofuse.php?link=2&" + code, "", "width=500,height=500,left=100,top=100,scrollbars=yes,resizable=no");
				fenster.focus();
			}
			}
		}
	);
	return false;
}

function touokdirect(elemid,id,type) {
	document.getElementById(elemid).checked=true;
	jQuery.post(
		"/mapbender/php/mod_acceptedTou_server.php",
		{
			method:"setAcceptedTou",
			id:1,
			params: "{\"serviceType\":\""+type+"\",\"serviceId\":"+id+"}"
		}
	);
	document.formmaps.submit()
}

function touoklink(url,id,type) { 
	jQuery.post(
		"/mapbender/php/mod_acceptedTou_server.php",
		{
			method:"setAcceptedTou",
			id:1,
			params: "{\"serviceType\":\""+type+"\",\"serviceId\":"+id+"}"
		}
	);
//	url += "&LAYER[id]=" + id;
	window.location.href=url;
}

function touok(elemid,id,type) {
	document.getElementById(elemid).checked=true;
	jQuery.post(
		"/mapbender/php/mod_acceptedTou_server.php",
		{
			method:"setAcceptedTou",
			id:1,
			params: "{\"serviceType\":\""+type+"\",\"serviceId\":"+id+"}"
		}
	);
}

function opentou(tou) {
	var fenster = (window.open("/mapbender/template/php/termsofuse.php?"+tou, "_blank", "width=500,height=500,left=100,top=100,scrollbars=yes,resizable=yes"));
	fenster.focus();
}

function openwindow(Adresse) {
	var Fenster1 = window.open(Adresse, "Informationen", "width=500,height=600,left=100,top=100,scrollbars=yes,resizable=no");
	Fenster1.focus();
}


// Plugin for Popup-Windows
(function($){ 		  
	$.fn.popupWindow = function(instanceSettings){
		
		return this.each(function(){
		
		$(this).click(function(){
		
		$.fn.popupWindow.defaultSettings = {
			centerBrowser:0, // center window over browser window? {1 (YES) or 0 (NO)}. overrides top and left
			centerScreen:0, // center window over entire screen? {1 (YES) or 0 (NO)}. overrides top and left
			height:500, // sets the height in pixels of the window.
			left:0, // left position when the window appears.
			location:0, // determines whether the address bar is displayed {1 (YES) or 0 (NO)}.
			menubar:0, // determines whether the menu bar is displayed {1 (YES) or 0 (NO)}.
			resizable:0, // whether the window can be resized {1 (YES) or 0 (NO)}. Can also be overloaded using resizable.
			scrollbars:0, // determines whether scrollbars appear on the window {1 (YES) or 0 (NO)}.
			status:0, // whether a status line appears at the bottom of the window {1 (YES) or 0 (NO)}.
			width:500, // sets the width in pixels of the window.
			windowName:null, // name of window set from the name attribute of the element that invokes the click
			windowURL:null, // url used for the popup
			top:0, // top position when the window appears.
			toolbar:0 // determines whether a toolbar (includes the forward and back buttons) is displayed {1 (YES) or 0 (NO)}.
		};
		
		settings = $.extend({}, $.fn.popupWindow.defaultSettings, instanceSettings || {});
		
		var windowFeatures =    'height=' + settings.height +
								',width=' + settings.width +
								',toolbar=' + settings.toolbar +
								',scrollbars=' + settings.scrollbars +
								',status=' + settings.status + 
								',resizable=' + settings.resizable +
								',location=' + settings.location +
								',menuBar=' + settings.menubar;

				settings.windowName = this.name || settings.windowName;
				settings.windowURL = this.href || settings.windowURL;
				var centeredY,centeredX;
			
				if(settings.centerBrowser){
						
					if ($.browser.msie) {//hacked together for IE browsers
						centeredY = (window.screenTop - 120) + ((((document.documentElement.clientHeight + 120)/2) - (settings.height/2)));
						centeredX = window.screenLeft + ((((document.body.offsetWidth + 20)/2) - (settings.width/2)));
					}else{
						centeredY = window.screenY + (((window.outerHeight/2) - (settings.height/2)));
						centeredX = window.screenX + (((window.outerWidth/2) - (settings.width/2)));
					}
					window.open(settings.windowURL, settings.windowName, windowFeatures+',left=' + centeredX +',top=' + centeredY).focus();
				}else if(settings.centerScreen){
					centeredY = (screen.height - settings.height)/2;
					centeredX = (screen.width - settings.width)/2;
					window.open(settings.windowURL, settings.windowName, windowFeatures+',left=' + centeredX +',top=' + centeredY).focus();
				}else{
					window.open(settings.windowURL, settings.windowName, windowFeatures+',left=' + settings.left +',top=' + settings.top).focus();	
				}
				return false;
			});
			
		});	
	};
})(jQuery);
