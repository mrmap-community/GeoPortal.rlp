/**
 * Package: mb_metadata_edit
 *
 * Description:
 *
 * Files:
 *
 * SQL:
 * 
 * Help:
 *
 * Maintainer:
 * http://www.mapbender.org/User:Christoph_Baudson
 *
 * License:
 * Copyright (c) 2009, Open Source Geospatial Foundation
 * This program is dual licensed under the GNU General Public License
 * and Simplified BSD license.
 * http://svn.osgeo.org/mapbender/trunk/mapbender/license/license.txt
 */

var $metadataLayer = $(this);
var $metadataForm = $("<form>No layer selected.</form>").appendTo($metadataLayer);

var MetadataLayerApi = function (o) {
	var that = this;
	var validator;
	var formReady = false;
	var wmsId;
	var layerId;
	
	var disabledFields = [
		"layer_custom_category_id", 
		"layer_inspire_category_id", 
		"layer_md_topic_category_id", 
		"layer_keyword", 
		"layer_abstract", 
		"layer_title"
	];

	this.events = {
		initialized: new Mapbender.Event(),
		submit: new Mapbender.Event(),
		showOriginalLayerMetadata : new Mapbender.Event()
	};
		
	this.valid = function () {
		if (validator && validator.numberOfInvalids() > 0) {
			$metadataForm.valid();
			return false;
		}
		return true;
	};

	this.serialize = function (callback) {
		$metadataForm.submit();
		var data = null;
		if (this.valid()) {
			data = {
				layer: $metadataForm.easyform("serialize")
			};
		}
		if ($.isFunction(callback)) {
			callback(data);
		}
		return data !== null ? data.layer : data;
	};

	this.fillForm = function (obj) {
		$(disabledFields).each(function () {
			$("#" + this).removeAttr("disabled");
		});

		layerId = obj.layer_id;

		$metadataForm.easyform("reset");
		
		// get metadata from server
		var req = new Mapbender.Ajax.Request({
			url: "../plugins/mb_metadata_server.php",
			method: "getLayerMetadata",
			parameters: {
				"id": layerId
			},
			callback: function (obj, result, message) {
				if (!result) {
					return;
				}
				$metadataForm.easyform("fill", obj);
				//delete entries of #layer_id_p if given
				$('#layer_id_p').children().remove();
				$('#layer_id_p').append('<a target=\"_blank\"href=\"../php/mod_showMetadata.php?resource=layer&layout=tabs&id='+layerId+'\">Metadata Preview Layer '+layerId+'</a>');
				//delete metadataURL entries
				$('.metadataEntry').remove();
				//fill MetadataURLs into metadata_selectbox_id
				that.fillMetadataURLs(obj);
				that.valid();
				that.enableResetButton();
			}
		});
		req.send();		
	};
	//function generate updated metadataUrl entries TODO: this function is defined in mb_metadata_layer.js before but it cannot be called - maybe s.th. have to be changed
	this.fillMetadataURLs = function (obj) {
		layerId = obj.layer_id;
		//for size of md_metadata records:
		for (i=0;i<obj.md_metadata.metadata_id.length;i++) {
				if (obj.md_metadata.origin[i] == "capabilities") {
					if (obj.md_metadata.internal[i] == 1) {
						$("<tr class='metadataEntry'><td>"+obj.md_metadata.metadata_id[i]+"</td><td><img src='../img/server_map-ilink.png' title='link to metadata from capabilities'/></td><td><a href='../php/mod_dataISOMetadata.php?outputFormat=iso19139&id="+obj.md_metadata.uuid[i]+"' target='_blank'>"+obj.md_metadata.uuid[i]+"</a></td><td><a href='../php/mod_dataISOMetadata.php?outputFormat=iso19139&id="+obj.md_metadata.uuid[i]+"&validate=true' target='_blank'>validate</a></td><td></td><td><img class='' title='delete' src='../img/cross.png' onclick='deleteInternalMetadataLinkage("+obj.md_metadata.metadata_id[i]+","+layerId+");return false;'/></td></tr>").appendTo($("#metadataTable"));
					} else {
						$("<tr class='metadataEntry'><td>"+obj.md_metadata.metadata_id[i]+"</td><td><img src='../img/osgeo_graphics/geosilk/server_map.png' title='capabilities'/></td><td><a href='../php/mod_dataISOMetadata.php?outputFormat=iso19139&id="+obj.md_metadata.uuid[i]+"' target='_blank'>"+obj.md_metadata.uuid[i]+"</a></td><td><a href='../php/mod_dataISOMetadata.php?outputFormat=iso19139&id="+obj.md_metadata.uuid[i]+"&validate=true' target='_blank'>validate</a></td><td></td></tr>").appendTo($("#metadataTable"));
					}
				}
				if (obj.md_metadata.origin[i] == "external") {
					if (obj.md_metadata.internal[i] == 1) {
						$("<tr class='metadataEntry'><td>"+obj.md_metadata.metadata_id[i]+"</td><td><img src='../img/link-ilink.png' title='link to external linkage'/><td><a href='../php/mod_dataISOMetadata.php?outputFormat=iso19139&id="+obj.md_metadata.uuid[i]+"' target='_blank'>"+obj.md_metadata.uuid[i]+"</a></td><td><a href='../php/mod_dataISOMetadata.php?outputFormat=iso19139&id="+obj.md_metadata.uuid[i]+"&validate=true' target='_blank'>validate</a></td><td><img  class='' title='edit' src='../img/pencil.png' onclick='initMetadataAddon("+obj.md_metadata.metadata_id[i]+","+layerId+",false);return false;'/></td><td><img class='' title='delete' src='../img/cross.png' onclick='deleteInternalMetadataLinkage("+obj.md_metadata.metadata_id[i]+","+layerId+");return false;'/></td></tr>").appendTo($("#metadataTable"));
					} else {
						$("<tr class='metadataEntry'><td>"+obj.md_metadata.metadata_id[i]+"</td><td><img src='../img/osgeo_graphics/geosilk/link.png' title='linkage'/><td><a href='../php/mod_dataISOMetadata.php?outputFormat=iso19139&id="+obj.md_metadata.uuid[i]+"' target='_blank'>"+obj.md_metadata.uuid[i]+"</a></td><td><a href='../php/mod_dataISOMetadata.php?outputFormat=iso19139&id="+obj.md_metadata.uuid[i]+"&validate=true' target='_blank'>validate</a></td><td><img  class='' title='edit' src='../img/pencil.png' onclick='initMetadataAddon("+obj.md_metadata.metadata_id[i]+","+layerId+",false);return false;'/></td><td><img class='' title='delete' src='../img/cross.png' onclick='deleteAddedMetadata("+obj.md_metadata.metadata_id[i]+","+layerId+");return false;'/></td></tr>").appendTo($("#metadataTable"));
					}
				}
				if (obj.md_metadata.origin[i] == "upload") {
					if (obj.md_metadata.internal[i] == 1) {
						$("<tr class='metadataEntry'><td>"+obj.md_metadata.metadata_id[i]+"</td><td><img src='../img/up-ilink.png' title='link to external uploaded data'/><td><a href='../php/mod_dataISOMetadata.php?outputFormat=iso19139&id="+obj.md_metadata.uuid[i]+"' target='_blank'>"+obj.md_metadata.uuid[i]+"</a></td><td><a href='../php/mod_dataISOMetadata.php?outputFormat=iso19139&id="+obj.md_metadata.uuid[i]+"&validate=true' target='_blank'>validate</a></td><td><img class='' title='delete' src='../img/cross.png' onclick='deleteInternalMetadataLinkage("+obj.md_metadata.metadata_id[i]+","+layerId+");return false;'/></td></tr>").appendTo($("#metadataTable"));
					} else {
						$("<tr class='metadataEntry'><td>"+obj.md_metadata.metadata_id[i]+"</td><td><img src='../img/button_blue_red/up.png' title='uploaded data'/><td><a href='../php/mod_dataISOMetadata.php?outputFormat=iso19139&id="+obj.md_metadata.uuid[i]+"' target='_blank'>"+obj.md_metadata.uuid[i]+"</a></td><td><a href='../php/mod_dataISOMetadata.php?outputFormat=iso19139&id="+obj.md_metadata.uuid[i]+"&validate=true' target='_blank'>validate</a></td><td><img class='' title='delete' src='../img/cross.png' onclick='deleteAddedMetadata("+obj.md_metadata.metadata_id[i]+","+layerId+");return false;'/></td></tr>").appendTo($("#metadataTable"));
					}
				}
				if (obj.md_metadata.origin[i] == "metador") {
					if (obj.md_metadata.internal[i] == 1) {
						$("<tr class='metadataEntry'><td>"+obj.md_metadata.metadata_id[i]+"</td><td><img src='../img/edit-select-all-ilink.png' title='link to external edited metadata'/><td><a href='../php/mod_dataISOMetadata.php?outputFormat=iso19139&id="+obj.md_metadata.uuid[i]+"' target='_blank'>"+obj.md_metadata.uuid[i]+"</a></td><td><a href='../php/mod_dataISOMetadata.php?outputFormat=iso19139&id="+obj.md_metadata.uuid[i]+"&validate=true' target='_blank'>validate</a></td><td><img  class='' title='edit' src='../img/pencil.png' onclick='initMetadataAddon("+obj.md_metadata.metadata_id[i]+","+layerId+",false);return false;'/></td><td><img class='' title='delete' src='../img/cross.png' onclick='deleteInternalMetadataLinkage("+obj.md_metadata.metadata_id[i]+","+layerId+");return false;'/></td></tr>").appendTo($("#metadataTable"));
					} else {
						$("<tr class='metadataEntry'><td>"+obj.md_metadata.metadata_id[i]+"</td><td><img src='../img/gnome/edit-select-all.png' title='metadata'/><td><a href='../php/mod_dataISOMetadata.php?outputFormat=iso19139&id="+obj.md_metadata.uuid[i]+"' target='_blank'>"+obj.md_metadata.uuid[i]+"</a></td><td><a href='../php/mod_dataISOMetadata.php?outputFormat=iso19139&id="+obj.md_metadata.uuid[i]+"&validate=true' target='_blank'>validate</a></td><td><img  class='' title='edit' src='../img/pencil.png' onclick='initMetadataAddon("+obj.md_metadata.metadata_id[i]+","+layerId+",false);return false;'/></td><td><img class='' title='delete' src='../img/cross.png' onclick='deleteAddedMetadata("+obj.md_metadata.metadata_id[i]+","+layerId+");return false;'/></td></tr>").appendTo($("#metadataTable"));

					}
				}
		}
        if($("#metadataTable").find("tr").length == 0) {
            $("<tr class='metadataEntry'><td><img class='metadataEntry' title='new' src='../img/add.png' onclick='initMetadataAddon("+obj.md_metadata.metadata_id[i]+","+layerId+",true);return false;'/></td></tr>").appendTo($("#metadataTable"));
        } else {
            $("<tr class='metadataEntry'><td collspan='5'><img class='metadataEntry' title='new' src='../img/add.png' onclick='initMetadataAddon("+obj.md_metadata.metadata_id[i]+","+layerId+",true);return false;'/></td></tr>").appendTo($("#metadataTable"));
        }
	}

	this.enableResetButton = function () {
		$("#resetIsoTopicCats").click(function () {
			$("#layer_md_topic_category_id option").removeAttr("selected");
		});
		$("#resetCustomCats").click(function () {
			$("#layer_custom_category_id option").removeAttr("selected");
		});
		$("#resetInspireCats").click(function () {
			$("#layer_inspire_category_id option").removeAttr("selected");
		});
	}
	
	this.fill = function (obj) {
		$metadataForm.easyform("fill", obj);
	};
	
	var showOriginalLayerMetadata = function () {
		that.events.showOriginalLayerMetadata.trigger({
			data : {
				wmsId : wmsId,
				layerData : $metadataForm.easyform("serialize")
			}
		});
	};

	this.getWmsId = function() {
		return wmsId;
	}

	this.getLayerId = function() {
		return layerId;
	}

	this.init = function (obj) {
		delete layerId;
		//delete metadataURL entries
		$('.metadataEntry').remove();
		$metadataForm.easyform("reset");

		wmsId = obj;
		
		if (!wmsId) {
			return;
		}
		
		var formData = arguments.length >= 2 ? arguments[1] : undefined;

		if (!formReady) {
			$metadataForm.load("../plugins/mb_metadata_layer.php", function () {
				$metadataForm.find(".help-dialog").helpDialog();

				$metadataForm.find(".original-metadata-layer").bind("click", function() {
					showOriginalLayerMetadata();
				});	

				validator = $metadataForm.validate({
					submitHandler: function () {
						return false;
					}
				});

				that.events.initialized.trigger({
					wmsId: wmsId
				});
				formReady = true;
			});
			return;
		}
		$(disabledFields).each(function () {
			$("#" + this).attr("disabled", "disabled");
		});
		that.events.initialized.trigger({
			wmsId: wmsId
		});
	};
	
	Mapbender.events.localize.register(function () {
		that.valid();
		var formData = $metadataForm.easyform("serialize");
		formReady = false;
		that.init(wmsId, formData);
	});
	Mapbender.events.init.register(function () {
		that.valid();
	});
};

$metadataLayer.mapbender(new MetadataLayerApi(options));
