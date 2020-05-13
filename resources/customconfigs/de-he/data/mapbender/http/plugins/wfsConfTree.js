/*
 *  default wfsConfIdString is taken from the elementVar "wfsConfIdString" of the mapframe1 element!
 *
 *  */
//some things for i18
var originalI18nObject = {
	"labelNoWfsConfAvailable": "No geometry modules are available. You can load those from the Search.",
	"labelClose": "close"
};

var translatedI18nObject = Mapbender.cloneObject(originalI18nObject);

if(Mapbender.modules.i18n){	
	Mapbender.modules.i18n.queue(options.id, originalI18nObject, function (translatedObject) {
		if (typeof translatedObject !== "object") {
			return;
		}
		translatedI18nObject = translatedObject;
	});
	//Mapbender.modules.i18n.localize(Mapbender.languageId);
}

var $confTree = $(this);
var ConfTree = function(o){
	var that = this;
	var wfsConfIdString = o.wfsConfIdString || "";
	var wfsconfs = wfsConfIdString.split(',');
	// getParams is a global variable that contains the Querystring as a json object
	var getwfsConfIdString = getParams['FEATURETYPE'] || "";
	if(getwfsConfIdString !== ""){
		wfsconfs = wfsconfs.concat(getwfsConfIdString.split(','));
	}
	wfsConfIdString = wfsconfs.join(',');
	var currentWFSConf = {};
	if(Mapbender.modules.loadwmc){
		Mapbender.modules.loadwmc.events.loaded.register(function (obj) {
			if (obj.extensionData && obj.extensionData.WFSCONFIDSTRING) {
				var req = Mapbender.Ajax.Request({
					url: 	"../php/mod_wfs_conf_server.php",
					method:	"getWfsConfsFromId",
					parameters: {
						wfsConfIdString: obj.extensionData.WFSCONFIDSTRING
					},
					callback: function(result,success,message){
						reset(result);
					}
				});
				req.send();
			}
		});
	}
	var $wfsConfDialog = $("<div></div>").dialog({
		width: 350,
		height: 350,
		autoOpen: false,
		buttons:{
			"schliessen" : function(){
					$(this).dialog("close");
				}
			}
	});
	$("button.toggle",$confTree).live('click', function(){
		if($(this).parent().hasClass("open")){
			$(this).parent().removeClass("open");
			$(this).parent().addClass("closed");
		}else{
			$(this).parent().removeClass("closed");
			$(this).parent().addClass("open");

		}
	});
	var reset = function(aWFSConf){
		wfsconfs = [];
		$confTree.children().remove();
		$confTree.append("<li class='emptymessage'>"+translatedI18nObject.labelNoWfsConfAvailable+"</li>");
		$confTree.addClass('conftree');

		var $WFSConffolder = $('<li class="open wfsconf"><ul></ul></li>');
		$confTree.append($WFSConffolder);
		for(var i in  aWFSConf){
			// remove default "no wfs conf"message
			if(i == 0){ $confTree.find(".emptymessage").remove();}
			// group by featyretype
			$featuretypeFolder = $WFSConffolder.find("li.featuregroup_"+aWFSConf[i].featureTypeId);
			wfsconfs.push(aWFSConf[i].id);
			if($featuretypeFolder.size() == 0){
				$featuretypeFolder = $('<li class="open featuregroup_'+ aWFSConf[i].featureTypeId + '"><button class="toggle"></button>'+ aWFSConf[i].abstr + '<ul></ul></li>');
				$WFSConffolder.find(" > ul").append($featuretypeFolder);
				$featuretypeList = $featuretypeFolder.find("ul");
			}else{
				$featuretypeList = $featuretypeFolder.find("ul");
			};
			//parseInt because one version of wfsConf creates this as a string, the other as an int 
			switch(parseInt(aWFSConf[i].type,10)){
					/* search */
					case 0: 
						$wfsconfEntry = $('<li class="search" ><img src="../img/button_blue_red/gazetteer2_off.png" /><button class="remove">remo</button><img class="meta" src="../img/button_blue_red/getArea_over.png" />  <a href="#" class="dialogopen">'+ aWFSConf[i].label +'</a></li>');
					break;
					
					/* digitize */
					case 1:
						$wfsconfEntry = $('<li class="digitize" ><img src="../img/pencil.png" /><button class="remove">remo</button><img class="meta" src="../img/button_blue_red/getArea_over.png" />  <a href="#" class="dialogopen">'+ aWFSConf[i].label +'</a></li>');
					break;

					/* download */
					case 2: 
						$wfsconfEntry = $('<li class="download" ><img src="../img/gnome/document-save.png" /><button class="remove">remo</button><img class="meta" src="../img/button_blue_red/getArea_over.png" />  <a href="#" class="dialogopen">'+ aWFSConf[i].label +'</a></li>');
					break;

			}
			$wfsconfEntry.data("wfsconfId",aWFSConf[i].id);
			$featuretypeList.append($wfsconfEntry);

			$wfsconfEntry.find("img.meta").click((function(wfsConf){ 
				return function(){

				var querystring = 'resource=wfs-conf&id='+wfsConf.id;
				var $iframe = $('<iframe name="'+o.id+'_" style="border:none; width: 100%; height: 100%;" src="../php/mod_showMetadata.php?'+querystring+'"></iframe>');
				$wfsConfDialog.empty();
				$wfsConfDialog.append($iframe);	
				$wfsConfDialog.dialog("open");
				};
			})(aWFSConf[i]));

			$wfsconfEntry.find("a.dialogopen").click((function(wfsConf){ 
				return function(){

				var querystring = 'wfsConfId='+wfsConf.id+'&e_id_css='+o.id+'&e_target='+o.target;
				switch(parseInt(wfsConf.type,10)){

					/* search */
					case 0: 
					/* download */
					case 2: 
						var $iframe = $('<iframe name="'+o.id+'_" style="border:none; width: 100%; height: 100%;" src="../javascripts/mod_wfsGazetteerEditor_client.php?'+querystring+'"></iframe>');
						$wfsConfDialog.empty();
						$wfsConfDialog.append($iframe);	
						$wfsConfDialog.dialog("open");
					break;

					/* digitize */
					case 1:
					break;
				}

				};
			})(aWFSConf[i]));
			$wfsconfEntry.find("button.remove").click(function(){
			
				
				var wfsconfId = $(this).parent().data("wfsconfId");
				// if this was the last entry in the featuregroup, remove it completely	...
//				if($(this).parent().siblings().size() == 0){
//					$(this).parent().parent().parent().remove();
//				}else{
//					$(this).parent().remove();
//				}
				
				var newWFSConf = [];
				for (var i in currentWFSConf){
					if(currentWFSConf[i].id != wfsconfId){
						newWFSConf.push(currentWFSConf[i]);
					}
				}
				reset(newWFSConf);
					
			});
		}
		if(Mapbender.modules.savewmc){
			Mapbender.modules.savewmc.setExtensionData({ WFSCONFIDSTRING: wfsconfs.join(',') });
			Mapbender.modules.savewmc.save({session: true});
		}
	
		// need this so we have a reference to the currently active wfsConfs
		currentWFSConf = aWFSConf;
	};


	if(wfsConfIdString){
		var req = Mapbender.Ajax.Request({
			url: 	"../php/mod_wfs_conf_server.php",
			method:	"getWfsConfsFromId",
			parameters: {
				wfsConfIdString: wfsConfIdString
			},
			callback: function(result,success,message){
				reset(result);
			}
		});
		req.send();
	}


	// addFeaturetypeConf should take precedence
	$('#body').bind('addFeaturetypeConfs',function(evt,obj){
		if(!obj.wfsConfIdString){ return; }
		var req = Mapbender.Ajax.Request({
			url: 	"../php/mod_wfs_conf_server.php",
			method:	"getWfsConfsFromId",
			parameters: {
				wfsConfIdString: obj.wfsConfIdString
			},
			callback: function(result,success,message){
				reset(result);
			}
		});
		req.send();
	});


};

Mapbender.events.init.register(function(){
	$confTree.mapbender(new ConfTree(options));
});
