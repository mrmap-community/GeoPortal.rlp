var $umschalter = $(this);

var UmschalterApi = function (o) {
	var that = this;
	
	Mapbender.events.init.register(function () {
		//initiales Laden der Reiter
		that.init();
	});
	
	this.init = function () {
        $("#umschalter").empty();
        // ändere Schalter-Img und Link
        var umschalterHtml = '<img src="../geoportal/solardachkataster/img/tab_photo_aktiv.png">';
        umschalterHtml += '<img alt="Zur Solarthermie-Ansicht" style="cursor:pointer;" id="umschalter1" src="../geoportal/solardachkataster/img/tab_thermie_inaktiv.png">';
        $("#umschalter").html(umschalterHtml);
        
        $("#umschalter1").click(function () {
        	that.switchServices("solarthermie");
        });                

        //ändere Legenden-Img
        var legendImgHtml = '<img id="staticLegendImg" src="../geoportal/solardachkataster/img/legende_pv.gif" />';
        $("#legende_photo").empty().html(legendImgHtml);
    };
		
	this.switchServices = function (targetTopic) {
        $("#umschalter").empty();
        if(targetTopic == 'photovoltaik') {
                // ändere Schalter-Img und Link
                var umschalterHtml = '<img src="../geoportal/solardachkataster/img/tab_photo_aktiv.png">';
                umschalterHtml += '<img alt="Zur Solarthermie-Ansicht" style="cursor:pointer;" id="umschalter1" src="../geoportal/solardachkataster/img/tab_thermie_inaktiv.png">';
                $("#umschalter").html(umschalterHtml);
                
                $("#umschalter1").click(function () {
                	that.switchServices("solarthermie");
                });                

                //ändere Legenden-Img
                var legendImgHtml = '<img id="staticLegendImg" src="../geoportal/solardachkataster/img/legende_pv.gif" />';
                $("#legende_photo").empty().html(legendImgHtml);
        }
        else {
                // ändere Schalter-Img und Link
                var umschalterHtml = '<img alt="Zur Photovoltaik Ansicht" style="cursor:pointer;" id="umschalter2" src="../geoportal/solardachkataster/img/tab_photo_inaktiv.png">';
                umschalterHtml += '<img src="../geoportal/solardachkataster/img/tab_thermie_aktiv.png">';
                $("#umschalter").html(umschalterHtml);
                
                $("#umschalter2").click(function () {
                	that.switchServices("photovoltaik");
                });

                //ändere Legenden-Img
                var legendImgHtml = '<img id="staticLegendImg" src="../geoportal/solardachkataster/img/legende_st.gif" />';
                $("#legende_photo").empty().html(legendImgHtml);
        }
        
        //tausche Sichtbarkeit WMS-Dienste
        that.loadWms(targetTopic);
	};
	
	this.loadWms = function (topic) {
	    var ind = getMapObjIndexByName("mapframe1");
	    mb_mapObjremoveWMS(ind,1);
	    mb_mapObj[ind].zoom(true, 1.0);
	    lock_maprequest = true; //done to prohibit save wmc for each wms
	    mb_execloadWmsSubFunctions();
	    lock_maprequest = false;

	    if(topic == 'photovoltaik') {
	    	var wmsId = options.pvWms;
	    }
	    else {
	    	var wmsId = options.stWms;
	    }

	    mod_addWMSById_load(options.serviceContainer,wmsId);	
	};
};

$umschalter.mapbender(new UmschalterApi(options));