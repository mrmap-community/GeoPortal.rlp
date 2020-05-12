function switchServices(targetTopic) {
        $("#umschalter").empty();
        if(targetTopic == 'photovoltaik') {
                // ändere Schalter-Img und Link
                var umschalterHtml = '<img src="../geoportal/solardachkataster/img/tab_photo_aktiv.png"></a>';
                umschalterHtml += '<a title="Zur Solarthermie-Ansicht" href="javascript:switchServices(\'solarthermie\');">';
                umschalterHtml += '<img alt="Zur Solarthermie-Ansicht" src="../geoportal/solardachkataster/img/tab_thermie_inaktiv.png"></a>';
                $("#umschalter").html(umschalterHtml);

                //ändere Legenden-Img
                var legendImgHtml = '<img id="staticLegendImg" src="../geoportal/solardachkataster/img/legende_pv.gif" />';
                $("#legende_photo").empty().html(legendImgHtml);
        }
        else {
                // ändere Schalter-Img und Link
                var umschalterHtml = '<a title="Zur Photovoltaik Ansicht" href="javascript:switchServices(\'photovoltaik\');">';
                umschalterHtml += '<img alt="Zur Photovoltaik Ansicht" src="../geoportal/solardachkataster/img/tab_photo_inaktiv.png">';
                umschalterHtml += '</a><img src="../geoportal/solardachkataster/img/tab_thermie_aktiv.png">';
                $("#umschalter").html(umschalterHtml);

                //ändere Legenden-Img
                var legendImgHtml = '<img id="staticLegendImg" src="../geoportal/solardachkataster/img/legende_st.gif" />';
                $("#legende_photo").empty().html(legendImgHtml);
        }

        //tausche Sichtbarkeit WMS-Dienste
        changeWms(targetTopic);
}

function changeWms(topic) {
    var ind = getMapObjIndexByName("mapframe1");
    mb_mapObjremoveWMS(ind,1);
    mb_mapObj[ind].zoom(true, 1.0);
    lock_maprequest = true; //done to prohibit save wmc for each wms
    mb_execloadWmsSubFunctions();
    lock_maprequest = false;

    if(topic == 'photovoltaik') {
            if(Mapbender.userId == "2") {
                    var wmsCap = "http://geoportal.saarland.de/gdi-sl/mapserv?map=/mapfiles/gdisl/solarkataster/saarland_pv_ext.map&VERSION=1.1.1&REQUEST=GetCapabilities&SERVICE=WMS";
            }
            else {
                    var wmsCap = "http://geoportal.saarland.de/gdi-sl/mapserv?map=/mapfiles/gdisl/solarkataster/saarland_pv.map&VERSION=1.1.1&REQUEST=GetCapabilities&SERVICE=WMS";    
            }
    }
    else {
            if(Mapbender.userId == "2") {
                     var wmsCap = "http://geoportal.saarland.de/gdi-sl/mapserv?map=/mapfiles/gdisl/solarkataster/saarland_th_ext.map&VERSION=1.1.1&REQUEST=GetCapabilities&SERVICE=WMS";
            }
            else {
                    var wmsCap = "http://geoportal.saarland.de/gdi-sl/mapserv?map=/mapfiles/gdisl/solarkataster/saarland_th.map&VERSION=1.1.1&REQUEST=GetCapabilities&SERVICE=WMS";
            }
    }

    mod_addWMS_load(wmsCap, {
            callback: controlLayers
    });
}

function controlLayers (opt) {
    var ind = getMapObjIndexByName("mapframe1");
    var map = mb_mapObj[ind];
    var loadedWms = map.wms[map.wms.length - 1];
    handleSelectedWms(map.elementName, loadedWms.wms_id, 'visible', 1);
    handleSelectedWms(map.elementName, loadedWms.wms_id, 'querylayer', 1);

}
