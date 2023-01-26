<?php
//http://localhost/mapbender_trunk/plugins/mb_downloadFeedClient.php
require_once dirname(__FILE__) . "/../../core/globalSettings.php";
require_once dirname(__FILE__) . "/../classes/class_user.php";
if (isset($_REQUEST['url']) & $_REQUEST['url'] != "") {
        //validate
        $testMatch = $_REQUEST["url"];
        if (preg_match('#^(http|https):\/\/#i', $testMatch) && filter_var($testMatch, FILTER_VALIDATE_URL)) {
                        $testMatch = htmlspecialchars($testMatch, ENT_QUOTES);
                        $url = urldecode($testMatch);
        } else {
                echo 'Parameter <b>url</b> is not a valid url.<br/>';
                die();
        }
        $testMatch = NULL;
}
//TODO: languageCode support
//languageCode: de, en, fr
//get language parameter out of mapbender session if it is set else set default language to de_DE
$sessionLang = Mapbender::session()->get("mb_lang");
if (isset($sessionLang) && ($sessionLang!='')) {
        $e = new mb_notice("mod_showMetadata.php: language found in session: ".$sessionLang);
        $language = $sessionLang;
        $langCode = explode("_", $language);
        $langCode = $langCode[0]; # Hopefully de or s.th. else
        $languageCode = $langCode; #overwrite the GET Parameter with the SESSION information
}
if (isset($_REQUEST["languageCode"]) & $_REQUEST["languageCode"] != "") {
        //validate to csv integer list
        $testMatch = $_REQUEST["languageCode"];
        if (!($testMatch == 'de' or $testMatch == 'fr' or $testMatch == 'en')){
                //echo 'languageCode: <b>'.$testMatch.'</b> is not valid.<br/>';
                echo 'Parameter <b>languageCode</b> is not valid (de,fr,en).<br/>';
                die();
        }
        $languageCode = $testMatch;
        $testMatch = NULL;
}
$localeObj->setCurrentLocale($languageCode);
?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo _mb("INSPIRE ATOM Feed Client");?></title>
<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0;">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="description" content="INSPIRE ATOM Feed Client" xml:lang="en" />
<meta name="keywords" content="INSPIRE SDI GDI Download" xml:lang="en" />
<meta http-equiv="cache-control" content="no-cache" />
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="expires" content="0" />
<meta http-equiv="content-language" content="en" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="../extensions/OpenLayers-2.13.1/theme/default/style.css" type="text/css" />
<link type="text/css" href="../extensions/jquery-ui-1.12.1/jquery-ui.min.css" rel="Stylesheet" />
<link rel="stylesheet" href="../extensions/bootstrap-3.3.6-dist/css/bootstrap.min.css" type="text/css" />
<link rel="stylesheet" href="../extensions/bootstrap-select-1.9.3/dist/css/bootstrap-select.css" type="text/css" />
<style type="text/css">


.olControlTextButtonPanel.vpanel {
        top: 8px;
        right: 8px;
        left: auto;
}

a { cursor: pointer; }

.squareItemInactive:after,
.squareItemActive:after {
                content:url("../extensions/OpenLayers-2.13.1/img/select.png");
}

.navigateItemInactive:after,
.navigateItemActive:after {
    content: url("../extensions/OpenLayers-2.13.1/img/move.png");
}
body {max-width: 921px;}
body,td, form, input, select{
  font-size: 12px;
  line-height: 16px;
  font-family: "Verdana","Arial",sans-serif;
  color: black;
  margin-top: 10px;
  margin-bottom: 10px;
  margin-right: 10px;
  margin-left: 10px;
}
.nav-tabs > li.active > a, .nav-tabs > li.active > a:focus, .nav-tabs > li.active > a:hover {background-color:#f8f8f8;}
.navbar-brand{font-size:14px;}
div, p a {overflow-wrap:break-word;word-wrap: break-word;}
div.olControlZoom{left:12px}
.olControlPanPanel{margin-top:70px;left:9px;}
.olControlPanPanel div {background-image: url("../img/misc/pan-panel.png");background-color: rgba(0, 60, 136, 0.5);cursor: pointer;height: 18px;position: absolute;width: 18px;}
.olControlPanNorthItemInactive.olButton{border-top-right-radius: 4px;border-top-left-radius: 4px;background-position: 2px 1px;width:22px;height:22px;left: 6px;top: 0px;}
.olControlPanSouthItemInactive.olButton{border-bottom-right-radius: 4px;border-bottom-left-radius: 4px;background-position: 20px 3px;width:22px;height:22px;left: 6px;top: 46px;}
.olControlPanEastItemInactive.olButton{border-top-right-radius: 4px;border-bottom-right-radius: 4px;background-position: 21px 20px;width:22px;height:22px;left: 18px;top: 23px;}
.olControlPanWestItemInactive.olButton{border-top-left-radius: 4px;border-bottom-left-radius: 4px;background-position: 0px 20px;width:22px;height:22px;left: -5px;top: 23px;}
select, textarea {
  font: 0.9em Verdana, Arial, sans-serif;
}
.inspire_loading {
    -webkit-animation:spin 4s linear infinite;
    -moz-animation:spin 4s linear infinite;
    animation:spin 4s linear infinite;

}
@-moz-keyframes spin { 100% { -moz-transform: rotate(360deg); } }
@-webkit-keyframes spin { 100% { -webkit-transform: rotate(360deg); } }
@keyframes spin { 100% { -webkit-transform: rotate(360deg); transform:rotate(360deg); } }
select {max-width: 100%;}
#mapframe_dataset_list {
  position: absolut;
  top: 0;
  left: 0;
  width: 100%;
  max-width: 800px;
  margin-bottom:10px !important;
  height: calc(100vh - 300px);
  max-height: 600px;
  border: 1px solid #ccc;
}
#mapframe {
}

#mapframe_file_list {
  width: 100%;
  max-width: 800px;
  margin-bottom:10px !important;
  height: calc(100vh - 300px);
  max-height: 600px;
  border: 1px solid #ccc;
}
.bootstrap-select {
  z-index: 1500;
}
.example_service_feed {cursor:pointer;}
#input_feed_url, #dataset_info, #representations {
  background-color: #f8f8f8;
  border-bottom: 1px solid #ddd;
  border-bottom-left-radius: 4px;
  border-bottom-right-radius: 4px;
  border-left: 1px solid #ddd;
  border-right: 1px solid #ddd;
  padding: 10px;
}
</style>
<!--<script src="../extensions/OpenLayers-2.13.1/OpenLayers.mobile.js"></script>//TODO: bugs with control panels-->
<script src="../extensions/OpenLayers-2.13.1/OpenLayers.js"></script>
<script src="../extensions/jquery-1.12.0.min.js"></script>
<script src="../extensions/jquery-ui-1.12.1/jquery-ui.min.js"></script>
<script src="../extensions/bootstrap-3.3.6-dist/js/bootstrap.min.js"></script>
<script src="../extensions/bootstrap-select-1.9.3/dist/js/bootstrap-select.min.js"></script>
<script src="../extensions/bootstrap-select-1.9.3/dist/js/i18n/defaults-de_DE.js"></script>
<script src="../javascripts/mb_downloadFeedClient.php"></script>
</head>
<body onload="init()">
<!-- Navbar -->
<nav class="navbar navbar-default">
  <div class="container-fluid">
    <!-- Titel und Schalter werden für eine bessere mobile Ansicht zusammengefasst -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Navigation ein-/ausblenden</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a style="float:none;display:block;padding:10px 15px;height:auto;" class="navbar-brand" href="#"><?php echo _mb("INSPIRE ATOM Feed Client");?>
      <img style="display:inline;margin-right:7px;" alt="INSPIRE symbol" src="../img/misc/inspire_eu_klein.png" title="INSPIRE">
      <img style="display:inline;margin-right:7px;" alt="European Union symbol" src="../img/misc/eu.png" title="<?php echo _mb("Implements European Standards");?>" ></a>
    </div>
    <!-- Alle Navigationslinks, Formulare und anderer Inhalt werden hier zusammengefasst und können dann ein- und ausgeblendet werden -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav navbar-right">
        <li><a href="#"><?php if ((integer)(Mapbender::session()->get("mb_user_id")) > 0) { echo _mb("Your logged in as ").Mapbender::session()->get("mb_user_name");} else { echo _mb("Your not logged in - please authenticate!");}?></a></li>
      </ul>

    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>
<!-- Modal Meldungen TODO: maybe usefull for input dialog - service feed url?-->
<!--<div class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="title" id="parse_service_feed_modal">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      Load and parse ServiceFeed ...
    </div>
  </div>
</div>
<div class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="title" id="parse_dataset_feed_modal">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      Load and parse DatasetFeed ...
    </div>
  </div>
</div>-->
<div id="loading_image_service" style="display: none;"><p><img class="inspire_loading" src="../img/inspire_tr_36.png" style="margin-left: auto; margin-right: auto;"/><?php echo _mb("Loading Service Feed ...");?></p></div>
<!-- <button class="btn btn-primary" type="button" id="stop_parsing"><?php echo _mb("reset");?></button> TODO: check if it is possible to use it if some error orccurs or if it possible to interrupt a ajax call / timeout handling? -->
<div id="loading_image_dataset" style="display: none;"><p><img class="inspire_loading" src="../img/inspire_tr_36.png" style="margin-left: auto; margin-right: auto;"/><?php echo _mb("Loading Dataset Feed ...");?></p></div>
<!-- Tabs-Navs -->
<ul class="nav nav-tabs" role="tablist" id="mytabs">
  <li role="presentation" class="active"><a href="#input_feed_url" role="tab" data-toggle="tab"><?php echo _mb("Url to feed");?></a></li>
  <li role="presentation"><a href="#dataset_info" role="tab" data-toggle="tab" id="tab_header_datasets"><?php echo _mb("Datasets");?><span id="tab_header_number_datasets"></span></a></li>
  <li role="presentation"><a href="#representations" role="tab" data-toggle="tab" id="tab_header_representations"><?php echo _mb("Representations");?><span id="tab_header_number_representations"></span></a></li>
</ul>
<!-- Tab-Inhalte -->
<div class="tab-content" style="height:100%">
  <div role="tabpanel" class="tab-pane active" id="input_feed_url">
    <form id="service_feed_form" style="margin:0 !important;">
      <div class="input-group" style="margin:0 0 10px 0">
        <span class="input-group-btn">
          <button type="button" class="btn btn-default" onclick="$('#download_feed_url').val('');">
            <img style="margin-top: -3px;" src="../img/misc/delete.png" onclick="$('#download_feed_url').val('');"/>
          </button>
        </span>
        <input name="download_feed_url" id="download_feed_url" class="required form-control" type="text" <?php if (isset($url)) {echo " value=\"".htmlspecialchars($url)."\"";} else { echo " value=\"\"";}?>/>
        <span class="input-group-btn">
          <button class="btn btn-primary" type="button" id="download_feed_button">
            <img style="margin-top: -3px;" src="../img/misc/refresh.png" />
          </button>
        </span>
      </div>
      <div class="dropdown" id="example_div">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo _mb("Example feeds");?> <span class="caret"></span></button>
                <ul class="dropdown-menu" id="example_feeds">
                        <li><a class="example_service_feed" value="http://www.geoportal.rlp.de/mapbender/php/mod_inspireDownloadFeed.php?id=2b009ae4-aa3e-ff21-870b-49846d9561b2&type=SERVICE&generateFrom=wmslayer&layerid=30694">Orthofotos Rheinland-Pfalz (WMS Datenquelle)</a></li>
                        <li><a class="example_service_feed" value="http://www.geoportal.rlp.de/mapbender/php/mod_inspireDownloadFeed.php?id=14bd842d-c9d4-64e1-68c8-f3007d004ae3&type=SERVICE&generateFrom=wfs&wfsid=344">INSPIRE Protected Sites Rheinland-Pfalz (WFS Datenquelle)</a></li>
                        <li><a class="example_service_feed" value="http://geoportal.saarland.de/mapbender/php/mod_inspireDownloadFeed.php?id=de59f284-8abc-4989-9ef3-6544121da16d&type=SERVICE&generateFrom=wfs&wfsid=245">Bodenkarte Saarland (WFS Datenquelle)</a></li>
                        <li><a class="example_service_feed" value="http://www.geoportal.hessen.de/mapbender//php/mod_inspireDownloadFeed.php?id=5cf8456c-abf4-b593-0acc-0942341f0957&type=SERVICE&generateFrom=wfs&wfsid=264">Abflussgebiete HQ100 Hessen (WFS Datenquelle)</a></li>
                        <li><a class="example_service_feed" value="http://geo.noe.gv.at/inspire-download/download_service_feed.xml">Diverse Niederösterreich</a></li>
                        <li><a class="example_service_feed" value="https://www.geoportal.ie/geoportal/download/ProtectedSites_SPA/npws-inspire-protected-sites-special-protection-areas.atom.en.xml">Protected Sites Ireland</a></li>
                        <li><a class="example_service_feed" value="https://stadtplan.freiburg.de/feeds/bplaene-freiburg.xml">Bebauungspläne Stadt Freiburg</a></li>
                        <li><a class="example_service_feed" value="https://www.regionalstatistik.de/genesisws/inspire/pd/00/feeds/sf_32214-01-01-4.xml">Statistik Klärschlamm Destatis</a></li>
                        <li><a class="example_service_feed" value="http://sg.geodatenzentrum.de/web_download/downloadservice.atom.xml">Basisdaten Geodatenzentrum BKG</a></li>
                        <li><a class="example_service_feed" value="http://www.lfu.bayern.de/gdi/dls/biotopkartierung.xml">Diverse Landesamt für Umwelt Bayern</a></li>
                        <li><a class="example_service_feed" value="http://maps.waterschapservices.nl/www/download/nl.xml">Diverse 1 Netherlands</a></li>
                        <li><a class="example_service_feed" value="http://services.rce.geovoorziening.nl/www/download/nl.xml">Diverse 2 Netherlands</a></li>
                        <li><a class="example_service_feed" value="http://mapy.geoportal.gov.pl/wss/service/ATOM/httpauth/atom/SR">Diverse Cadastral Poland</a></li>
                        <li><a class="example_service_feed" value="http://sedsh127.sedsh.gov.uk/Atom_data/ScotGov/ProtectedSites/SG_ProtectedSites.atom.en.xml">Protected Sites Scotland</a></li>
                </ul>
        </div>
        <!--<input type="button" title="Get Feed" id="download_feed_button" value="<?php echo _mb("Get feed content");?>"/>-->
      </form>
  </div>
  <div role="tabpanel" class="tab-pane" id="dataset_info">
    <div style="z-index: 1200; margin:0 0 10px 0;" id="dataset_list">
        <div id="dataset_select"></div>
    </div>
    <div id="mapframe_dataset_list"></div>
    <div id="dataset_information">
      <p>
        <label for="dataset_title"><?php echo _mb("Dataset title");?>:</label>
        <p readonly="readonly" name="dataset_title" id="dataset_title"></p>
      </p>
      <p>
        <label for="dataset_abstract"><?php echo _mb("Dataset abstract");?>:</label>
        <p readonly="readonly" name="dataset_abstract" id="dataset_abstract"></p>
      </p>
      <p>
        <label for="resource_identifier"><?php echo _mb("Resource identifier (linkage to metadata)");?>:</label>
        <p readonly="readonly" id="dataset_identifier"></p>
      </p>
      <p>
        <label for="dataset_rights"><?php echo _mb("Rights");?>:</label>
        <p readonly="readonly" name="dataset_rights" id="dataset_rights"></p>
      </p>
      <p>
        <!--<label for="resource_identifier"><?php echo _mb("Resource identifier");?>:</label>-->
      </p>
    </div>
  </div>
  <div role="tabpanel" class="tab-pane" id="representations">
    <label for="capabilities_hybrid" id="label_capabilities_hybrid"><?php echo _mb("Capabilities (WFS-hybrid)");?>:</label>
    <div id="capabilities_hybrid"></div>
    <div id="representation_select" style="margin:10px 0;">
        <div style="z-index: 1000;" id="dataset_representation_list"></div>
    </div>
    <div id="representation_info">
      <p><?php echo _mb("Select to download dataset")." - "._mb("click on single orange box generate download link below map (if seen)!");?><span id="number_of_tiles"></span></p>
      <div id="search_field"></div>
      <div id="mapframe_file_list"></div>
      <!--<label for="section_list"><?php echo _mb("List of files");?>:</label>-->
      <div id="section_list"></div>
      <!--<select id="section_file_list" multiple='multiple'>-->
      <!--<input type="button" title="Download selected files" value="Download selection"/>-->
      <!--</fieldset>   -->
      <div id="dialog-modal" title="Download Link"></div>
      <div id="number_of_selected_tiles" title="Number of selected tiles"></div>
    </div>
  </div>
</div>
<div id="user_id" value="<?php echo Mapbender::session()->get("mb_user_id");?>" ></div>
<div id="user_name" value="<?php echo Mapbender::session()->get("mb_user_name");?>"></div>
<div id="user_email" value="<?php echo Mapbender::session()->get("mb_user_email");?>"></div>
<div id="django" value="<?php echo Mapbender::session()->get("django");?>"></div>
<div id="session_id" value="<?php echo session_id();?>"></div>
</body>
</html>

