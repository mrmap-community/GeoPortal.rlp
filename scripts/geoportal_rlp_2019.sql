INSERT INTO gui (gui_id, gui_name, gui_description, gui_public) VALUES ('Geoportal-RLP_2019','Geoportal-RLP_2019','geheime GUI',1);
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','changeEPSG_Button',1,1,'','Kartenprojektion ändern','A','','',NULL ,NULL,NULL ,NULL,NULL ,'','<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
<path fill-rule="evenodd" clip-rule="evenodd" d="M5.79707 11C5.73231 10.3643 5.7 9.69336 5.7 9C5.7 8.30664 5.73231 7.6357 5.79707 7H3.34141C3.12031 7.62556 3 8.29873 3 9C3 9.70127 3.12031 10.3744 3.34141 11H5.79707ZM5.9309 12H3.80269C4.45843 13.1336 5.47437 14.0327 6.69401 14.5409C6.35031 13.8478 6.09615 12.9816 5.9309 12ZM12.2029 11H14.6586C14.8797 10.3744 15 9.70127 15 9C15 8.29873 14.8797 7.62556 14.6586 7H12.2029C12.2677 7.6357 12.3 8.30664 12.3 9C12.3 9.69336 12.2677 10.3643 12.2029 11ZM12.0691 12C11.9038 12.9816 11.6497 13.8478 11.306 14.5409C12.5256 14.0327 13.5416 13.1336 14.1973 12H12.0691ZM7.1138 11H10.8862C10.9599 10.3744 11 9.70127 11 9C11 8.29873 10.9599 7.62556 10.8862 7H7.1138C7.0401 7.62556 7 8.29873 7 9C7 9.70127 7.0401 10.3744 7.1138 11ZM7.26756 12C7.61337 13.7934 8.25972 15 9 15C9.74028 15 10.3866 13.7934 10.7324 12H7.26756ZM5.9309 6C6.09615 5.01844 6.35031 4.15217 6.69401 3.45913C5.47437 3.9673 4.45843 4.86643 3.80269 6H5.9309ZM12.0691 6H14.1973C13.5416 4.86643 12.5256 3.9673 11.306 3.45913C11.6497 4.15217 11.9038 5.01844 12.0691 6ZM7.26756 6H10.7324C10.3866 4.2066 9.74028 3 9 3C8.25972 3 7.61337 4.2066 7.26756 6ZM9 17C4.58172 17 1 13.4183 1 9C1 4.58172 4.58172 1 9 1C13.4183 1 17 4.58172 17 9C17 13.4183 13.4183 17 9 17Z" fill="currentColor"/>
</svg>EPSG','A','../plugins/mb_button.js','','changeEPSG','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','changeEPSG_Button','css','/* INSERT changeEPSG_Button -> elementVar -> css(text/css) */
#changeEPSG {
    height: auto !important;
    width: calc(100% - 10px) !important;
    padding: 5px !important;
    margin: 5px;
}
/* END INSERT changeEPSG_Button -> elementVar -> css(text/css) */','','text/css');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','jq_datatables',1,1,'Includes the jQuery plugin datatables, use like this $(selector).datatables(options)','','','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','../plugins/jq_datatables.js','../extensions/dataTables-1.5/media/js/jquery.dataTables.min.js','','','http://www.datatables.net/');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','jq_datatables','defaultCss','../extensions/dataTables-1.5/media/css/demo_table_jui.css','','file/css');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','changeEPSG',1,1,'change EPSG, Postgres required, overview is targed for full extent
position:fixed;bottom:15px;left:15px;','Kartenprojektion ändern','select','','',15,NULL ,186,24,1000,'padding:3px;font-size:12px;border:solid 1px #ABADB3;','<option value="">undefined</option>','select','mod_changeEPSG.php','../extensions/proj4js/lib/proj4js-compressed.js','overview,mapframe1','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','changeEPSG','projections','EPSG:4326;Geographic Coordinates,EPSG:25832;UTM zone 32N,EPSG:31467;Gauss-Krueger 3','','php_var');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','kml',1,1,'','','div','','',NULL ,NULL,NULL ,NULL,NULL ,'','','div','','../../lib/mb.ui.displayKmlFeatures.js','','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','jq_upload',1,1,'','','','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','','../plugins/jq_upload.js','','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','jq_ui_widget',1,1,'jQuery UI widget','','','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','','../extensions/jquery-ui-1.8.16.custom/development-bundle/ui/jquery.ui.widget.js','','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','wfsConfTree',1,1,'list-style-type:none;position:relative;display:inline-block;top:0px;padding: 0;  margin: 0;z-index:305','Flurstück suchen','ul','','title="Nach Flurstücken suchen"',NULL ,NULL,NULL ,NULL,NULL ,'','','ul','../plugins/wfsConfTree_single.js','','mapframe1','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','wfsConfTree','FLST_Form_css','/* INSERT wfsconftree -> elementVar -> FLST_Form_css(text/css) */
#wfsForm {
font-family: Helvetica, Roboto,Arial,sans-serif;
letter-spacing: 1px;
}
#wfsForm br {line-height:0;}
#wfsForm input{}
#wfsForm span {
    display: block;
    margin: 7px 0 0 0;
}

#wfsForm span a {
position: absolute;
margin: -29px 0 0px 0;
}

#wfsForm span a img{
margin-left: 5px;
}
div.helptext {
left:0;
top:0;
min-height: 100%;
min-width: 100%;
box-sizing: border-box;
margin:0px;
}

#wfsForm span select, #wfsForm input {
    display: block;
    left: 0px;
    width: 100% !important;
    border: 1px solid #777;
    line-height: 1.5;
    padding: 5px 5px 5px 20px;
	position: unset;
}
#wfsForm_Submit {
margin: 25px 0px 0px 0px;
}

#progressWheel table {
background-color: rgba(205,205,205,0.8);
width: 100%;
height: 100%;
top: 0;
left: 0;
position: absolute;
}
#progressWheel img {
width: 45px;
left: 40%;
position: absolute;
top: 40%;
}

/* END INSERT wfsconftree -> elementVar -> FLST_Form_css(text/css) */','','text/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','wfsConfTree','specialButtonFlst','/* INSERT wfsconftree -> elementVar -> wfsconftree MenuButton(text/css) */
#menuitem_flst{
   padding: 0px 0px 0px 43px;
   text-decoration: none;
   background-image: url(../img/geoportal2019/search_white.svg);
   background-repeat: no-repeat;
   background-position: left+19px center;
}
.menuitem_flst_on {
   color: #333 !important;
   background-image: url(../img/geoportal2019/search_over.svg) !important;
   background-color: #EEE !important;
   border-bottom: 1px solid red !important;
}
#menuitem_flst:hover{
   color: #333;
   background-image: url(../img/geoportal2019/search_over.svg);
   background-color: #EEE !important;
}
.open, .search {
border:none !important;
}
/* END INSERT wfsconftree -> elementVar -> wfsconftree MenuButton(text/css) */','','text/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','wfsConfTree','wfs_spatial_request_conf_filename','wfs_default.conf','','php_var');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','jq_ui_position',1,1,'jQuery UI position','','','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','','../extensions/jquery-ui-1.8.16.custom/development-bundle/ui/minified/jquery.ui.position.min.js','','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','mobile_Map',1,0,'','','div','','',NULL ,NULL,NULL ,NULL,NULL ,'','','div','../plugins/mb_mobile.js','','mapframe1','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','sessionWmc',1,1,'','Please confirm','div','','',NULL ,NULL,NULL ,NULL,NULL ,'','','div','../plugins/mb_sessionWmc.js','','','mapframe1','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','sessionWmc','displayTermsOfUse','1','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','sessionWmc','specialCondition','<fieldset><p>Mit der weiteren Nutzung des Geoportals Rheinland-Pfalz akzeptieren Sie unsere <a class="external-link"  target="_parent" href="../../article/Rechtshinweis/">Nutzungsbedingungen</a>.</p></fieldset>','<fieldset><p>Fill specialCondition</p></fieldset>','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','sessionWmc','specialConditionCSS','a.external-link{
font-size:inherit;
line-height:inherit;
font-family:inherit;
color:#D51F28;
Background:url("../../portal/fileadmin/design/extlink.png")right center no-repeat, URL("../../portal/fileadmin/design/bullet_red.png")left center no-repeat;
padding:0 13px 0 9px;
}
a.external-link:hover{
text-decoration:underline;
}
','','text/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','sessionWmc','tou_css','#sessionWmc_constraint_form tbody tr {display:block;}
  #sessionWmc_constraint_form tbody td {display:block;padding: 0 0 15px 3px;} 
  #sessionWmc_constraint_form fieldset {font-size: 0.88em;line-height:  165%;}','','text/css');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','jq_ui',1,1,'The jQuery UI core','','','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','','../extensions/jquery-ui-1.8.16.custom/development-bundle/ui/minified/jquery.ui.core.min.js','','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','jq_ui','css','../extensions/jquery-ui-1.7.2.custom/css/ui-lightness/jquery-ui-1.7.2.custom.css','','file/css');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','app_metadata',1,1,'','','div','','class="toggleAppMetadata" title="App-Metadaten"',NULL ,NULL,NULL ,NULL,NULL ,'','<div id="appMetadataLogo"><img src="https://www.geoportal.rlp.de/static/useroperations/images/logo-gdi.png"></div><div id="appMetadataTitle">GeoPortal.rlp</div><svg style="transform:rotate(180deg);" width="17" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M16.0142 11.6191L14.6042 13.0291L9.01416 7.43914L3.42416 13.0291L2.01416 11.6191L9.01416 4.61914L16.0142 11.6191Z" stroke="currentColor" stroke-width="2"></path>
</svg>
<div id="appMetadataContainer" style="overflow-y: scroll;line-height: 15px;font-size: 12px;font-weight:1;">Standardkartenviewer des GeoPortal.rlp</div>','div','../plugins/mb_appMetadataContainer.js','','','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','app_metadata','css','/* INSERT app_metadata -> elementVar -> css(text/css) */

#app_metadata {
float: left;
position: relative;
box-sizing: border-box;
font-family: Helvetica,Roboto,Arial,sans-serif;
color: #777;
font-style: normal;
font-weight:700;
letter-spacing: 1px;
padding-right:10px;
line-height: 50px;
display: block;
cursor:pointer;
margin-left: 20px;
border-left: 2px solid transparent;
border-right: 2px solid transparent;
height:51px;
}

#app_metadata:hover {
color:#333;
background-color: #EEE;
}



.appMetadataContainerOpened svg {
transform: rotate(0deg) !important;
}

#app_metadata svg {
padding: 0px 5px;
margin: 16px auto;
}
#app_metadata svg, #appMetadataLogo, #appMetadataTitle {
position:relative;
float:left;
}

#appMetadataLogo {
width:100px;
top:0;
bottom:0;
position:absolute;
}
#appMetadataLogo img {
max-height:49px;
max-width:100px;
border:none;
margin: auto 0;
top: 0;
bottom: 0;
position: absolute;
right: 0;
}

#appMetadataTitle{
max-width: calc(100vw - 664px);
max-height: 50px;
overflow:hidden;
margin-left:110px;
}

#appMetadataContainer{
margin-top: 52px;
width: 452px;
padding: 5px;
list-style-type: none;
position: absolute;
display: none;
max-height:150px;
box-shadow: 0px 5px 10px -2px rgb(201, 202, 202);
overflow: hidden;
background-color:white;
border-left: 2px solid #DDD;
border-right: 2px solid #DDD;
margin-left: -3px;
box-sizing: border-box;
border-bottom:2px solid #DDD;
overflow-y: auto;
font-size:12px;
font-weight:normal;
line-height:15px;
}
#appMetadataContainer br {margin-top: 5px;display: block;}

.appMetadataContainerOpened {
background-color: #EEE;
color:#333 !important;
border-left: 2px solid #DDD !important;
border-right: 2px solid #DDD !important;
}

@media (max-width: 968px) {
#app_metadata{line-height:25px;font-weight:normal;}
#appMetadataTitle{width: calc(100vw - 672px);}
#appMetadataContainer{width: calc(100vw - 519px);min-width:238px;max-height:150px;}
}
@media (max-width: 820px) {
#appMetadataTitle{display:none;}
#app_metadata svg{float:right !important;}
#app_metadata{width:150px;}
#appMetadataContainer{width:238px;max-height:300px;}
}

@media (max-width: 675px) {
#app_metadata{display:none;}
}

/* END INSERT app_metadata-> elementVar -> css(text/css) */','','text/css');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','Div_collection2',1,1,'NAVIGATION Put existing divs in new div object. List the elements comma-separated under target, and make sure they have a title.','','div','','',NULL ,NULL,NULL ,NULL,100,'width:100%;background-color:rgba(255,255,255,0.97);position:relative;top:0em;right: 0;box-shadow: 0 5px 10px -2px rgb(201, 202, 202);display:inline-block;','','div','../plugins/mb_div_collection.js','','mapsContainer,toolbarContainer,toolbar,app_metadata,jsonAutocompleteGazetteer','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','applicationMetadata',1,1,'','Application info','div','','',NULL ,NULL,NULL ,NULL,NULL ,'','','div','../plugins/mod_applicationMetadata.php','','','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','applicationMetadata','displayTermsOfUse','1','','var');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','tinySliderModule',1,1,'','','','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','','../extensions/tiny-slider-master/dist/min/tiny-slider.js','','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','body',1,1,'body (obligatory)Javascripts: ../geoportal/mod_revertGuiSessionSettings.php
','','body','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','','../extensions/wz_jsgraphics.js,geometry.js,../extensions/RaphaelJS/raphael-1.4.7.min.js,../extensions/spectrum-min.js,../extensions/uuid.js,../extensions/tokml.js,../extensions/togpx.js','','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','body','includeWhileLoading','../geoportal/geoportal_splash.php','','php_var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','body','jq_ui_theme','../extensions/jquery-ui-1.7.2.custom/css/smoothness/jquery-ui-1.7.2.custom.css','UI Theme from Themeroller','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','body','popupcss','../css/popup.css','file css','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','body','tablesortercss','../css/tablesorter.css','file css','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','body','fontsize','.ui-widget{font-size:0.9em !important}','','text/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','body','print_css','../geoportal/print_div.css','','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','body','use_load_message','true','','php_var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','body','favicon','../img/favicon.png','favicon','php_var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','body','jq_ui_effects_transfer','.ui-effects-transfer { z-index:1003; border: 2px dotted gray; } ','','text/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','body','jq_ui_autocomplete_css','../css/jquery.ui.autocomplete.2019.css','file/css','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','body','buttonsCSS','/* INSERT body -> elementVar -> buttonCSS(text/css) */
.myOnClass,.myOverClass{background-color:#EEE !important;color:#333 !important;}
.myOnClass{border-bottom: 1px solid #d62029 !important;}
#Div_collection2 img{border-bottom:1px solid transparent;}
#zoomFull:hover,#zoomOut1:hover,#zoomIn1:hover {background-color: #EEE;cursor:pointer;}
/* END INSERT body -> elementVar -> buttonCSS(text/css) */','','text/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','body','css_file_wfstree_single','../css/wfsconftree2.css','','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','body','ui-dialog-override-css','/* INSERT body -> elementVar -> ui-dialog-override-css(text/css) */
.ui-dialog {
max-width: 85vw;
max-height: 85vh;
position:absolute;
padding-top:unset !important;
padding-right:unset !important;
padding-bottom:1.2em !important;
padding-left:unset !important;
box-shadow: 0 5px 10px -2px rgb(201, 202, 202);
}
		.ui-dialog-content{
			max-width: 80vw;
			max-height: 70vh;
		}
.ui-corner-all {
    -moz-border-radius: unset;
    -webkit-border-radius: unset;
}
.ui-widget-content {
    border: 1px solid #aaa;
}

.ui-widget {
    font-family: Helvetica,Arial,sans-serif;
    font-size: 1.1em;
    letter-spacing: 1px;
}

.ui-widget-header {
    border-top: none;
    border-right: none;
    border-left: none;
    border-bottom: 1px solid #aaaaaa;
    background: none;
}
#loadwmc_wmclist, #kml-from-wmc {
padding:0.1em !important;
}
/* END INSERT body -> elementVar -> ui-dialog-override-css(text/css) */','','text/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','body','cacheGuiHtml','false','','php_var');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','logo',1,0,'layout Logo unten links ','Hier gelangen Sie zum Geoportal Rheinland-Pfalz','img','../img/logo-geoportal.png','onclick="javascript:window.open(''https://www.geoportal.rlp.de'','''','''');"',NULL ,NULL,NULL ,NULL,NULL ,'margin-left: 15px;margin-top:3px;width:12em;position:relative;cursor:pointer;float:left;','','','','','','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','vis_timeline',2,1,'VIS Core','','','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','','../extensions/vis/dist/vis.js','','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','vis_timeline','file_vis_css','../extensions/vis/dist/vis.css','','file/css');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','digitize_widget',2,1,'Digitize
notwendig für Context menu','Digitize distance','img','../img/button_blue_red/measure_off.png','',1,1,1,1,NULL ,'display:none','','','../plugins/mb_digitize_widget.php','../extensions/JSON-Schema-Instantiator/instantiator.js,../widgets/w_digitize.js,../extensions/RaphaelJS/raphael-1.4.7.min.js','mapframe1','jq_ui_dialog,jq_ui_widget','http://www.mapbender.org/index.php/Digitize');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','digitize_widget','digitizePointDiameter','7','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','digitize_widget','featureCategoriesSchema','{
  "$schema": "http://json-schema.org/draft-04/schema#",
  "id": "/",
  "type": "object",
  "properties": {
    "categories": {
      "id": "categories",
      "type": "object",
      "properties": {
        "Editable-Data": {
          "id": "Editable-Data",
          "type": "string",
          "default": "Editable-Data"
        },
        "Style-Data": {
          "id": "Style-Data",
          "type": "string",
          "default": "Style-Data"
        },
       "Fix-Data": {
          "id": "Fix-Data",
          "type": "string",
          "default": "Fix-Data"
        },
        "Custom-Data": {
          "id": "Custom-Data",
          "type": "string",
          "default": "Custom-Data"
        }
      },
      "required": [
        "Fix-Data",
        "Editable-Data",
        "Style-Data",
        "Custom-Data"
      ]
    }
  },
  "required": [
    "categories"
  ]
}','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','digitize_widget','lineStrokeDefault','#808080','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','digitize_widget','lineStrokeSnapped','#F30','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','digitize_widget','lineStrokeWidthDefault','2','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','digitize_widget','lineStrokeWidthSnapped','2','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','digitize_widget','opacity','0.5','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','digitize_widget','pointAttributesSchema','{
  "$schema": "http://json-schema.org/draft-04/schema#",
  "id": "/",
  "type": "object",
  "properties": {
    "Point": {
      "id": "Point",
      "type": "object",
      "properties": {
        "created": {
          "id": "created",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Fix-Data"
            },
            "value": {
              "id": "value",
              "type": "string",
              "default": ""
            }
          },
          "required": [
            "category",
            "value"
          ]
        },
        "description": {
          "id": "description",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Editable-Data"
            },
            "value": {
              "id": "value",
              "type": "string",
              "default": "description"
            }
          }
        },
        "iconOffsetX": {
          "id": "iconOffsetX",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Style-Data"
            },
            "value": {
              "id": "value",
              "type": "integer",
              "default": -10
            }
          }
        },
        "iconOffsetY": {
          "id": "iconOffsetY",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Style-Data"
            },
            "value": {
              "id": "value",
              "type": "integer",
              "default": -10
            }
          }
        },
        "marker-color": {
          "id": "marker-color",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Style-Data"
            },
            "value": {
              "id": "value",
              "type": "string",
              "default": "#7e7e7e"
            }
          }
        },
        "marker-size": {
          "id": "marker-size",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Style-Data"
            },
            "value": {
              "id": "value",
              "type": "integer",
              "default": 34
            }
          }
        },
        "marker-symbol": {
          "id": "marker-symbol",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Style-Data"
            },
            "value": {
              "id": "value",
              "type": "string",
              "default": "marker"
            }
          }
        },
        "name": {
          "id": "name",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Editable-Data"
            },
            "value": {
              "id": "value",
              "type": "string",
              "default": "name"
            }
          }
        },
        "title": {
          "id": "title",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Editable-Data"
            },
            "value": {
              "id": "value",
              "type": "string",
              "default": "title"
            }
          }
        },
        "updated": {
          "id": "updated",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Fix-Data"
            },
            "value": {
              "id": "value",
              "type": "string",
              "default": ""
            }
          }
        },
        "uuid": {
          "id": "uuid",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Fix-Data"
            },
            "value": {
              "id": "value",
              "type": "integer",
              "default": -10
            }
          }
        }
      },
      "required": [
        "created",
        "description",
        "iconOffsetX",
        "iconOffsetY",
        "marker-color",
        "marker-size",
        "marker-symbol",
        "name",
        "title",
        "updated",
        "uuid"
      ]
    }
  },
  "required": [
    "Point"
  ]
}','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','digitize_widget','pointFillDefault','#B2DFEE','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','digitize_widget','pointFillSnapped','#FF0000','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','digitize_widget','pointStrokeDefault','#FF0000','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','digitize_widget','pointStrokeSnapped','#FF0000','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','digitize_widget','pointStrokeWidthDefault','2','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','digitize_widget','polygonAttributesSchema','{
  "$schema": "http://json-schema.org/draft-04/schema#",
  "id": "/",
  "type": "object",
  "properties": {
    "Polygon": {
      "id": "Polygon",
      "type": "object",
      "properties": {
        "created": {
          "id": "created",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Fix-Data"
            },
            "value": {
              "id": "value",
              "type": "string",
              "default": ""
            }
          },
          "required": [
            "category",
            "value"
          ]
        },
        "description": {
          "id": "description",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Editable-Data"
            },
            "value": {
              "id": "value",
              "type": "string",
              "default": "description"
            }
          }
        },
        "fill": {
          "id": "fill",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Style-Data"
            },
            "value": {
              "id": "value",
              "type": "string",
              "default": "#555555"
            }
          }
        },
        "fill-opacity": {
          "id": "fill-opacity",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Style-Data"
            },
            "value": {
              "id": "value",
              "type": "number",
              "default": 0.5
            }
          }
        },
        "marker-size": {
          "id": "marker-size",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Style-Data"
            },
            "value": {
              "id": "value",
              "type": "integer",
              "default": 34
            }
          }
        },
        "marker-symbol": {
          "id": "marker-symbol",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Style-Data"
            },
            "value": {
              "id": "value",
              "type": "string",
              "default": "marker"
            }
          }
        },
        "name": {
          "id": "name",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Editable-Data"
            },
            "value": {
              "id": "value",
              "type": "string",
              "default": "name"
            }
          }
        },
        "stroke": {
          "id": "stroke",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Style-Data"
            },
            "value": {
              "id": "value",
              "type": "string",
              "default": "#555555"
            }
          }
        },
        "stroke-opacity": {
          "id": "stroke-opacity",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Style-Data"
            },
            "value": {
              "id": "value",
              "type": "integer",
              "default": 1
            }
          }
        },
        "stroke-width": {
          "id": "stroke-width",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Style-Data"
            },
            "value": {
              "id": "value",
              "type": "integer",
              "default": 2
            }
          }
        },
        "title": {
          "id": "title",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Editable-Data"
            },
            "value": {
              "id": "value",
              "type": "string",
              "default": "title"
            }
          }
        },
        "updated": {
          "id": "updated",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Fix-Data"
            },
            "value": {
              "id": "value",
              "type": "string",
              "default": ""
            }
          }
        },
        "uuid": {
          "id": "uuid",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Fix-Data"
            },
            "value": {
              "id": "value",
              "type": "integer",
              "default": -10
            }
          }
        }
      },
      "required": [
        "created",
        "description",
        "fill",
        "fill-opacity",
        "marker-size",
        "marker-symbol",
        "name",
        "stroke",
        "stroke-opacity",
        "stroke-width",
        "title",
        "updated",
        "uuid"
      ]
    }
  },
  "required": [
    "Polygon"
  ]
}','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','digitize_widget','polygonFillDefault','#FFFF00','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','digitize_widget','polygonFillSnapped','#FC3','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','digitize_widget','polygonStrokeWidthDefault','1','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','digitize_widget','polygonStrokeWidthSnapped','3','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','digitize_widget','polylineAttributesSchema','{
  "$schema": "http://json-schema.org/draft-04/schema#",
  "id": "/",
  "type": "object",
  "properties": {
    "Polyline": {
      "id": "Polyline",
      "type": "object",
      "properties": {
        "created": {
          "id": "created",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Fix-Data"
            },
            "value": {
              "id": "value",
              "type": "string",
              "default": ""
            }
          },
          "required": [
            "category",
            "value"
          ]
        },
        "description": {
          "id": "description",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Editable-Data"
            },
            "value": {
              "id": "value",
              "type": "string",
              "default": "description"
            }
          }
        },
        "marker-size": {
          "id": "marker-size",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Style-Data"
            },
            "value": {
              "id": "value",
              "type": "integer",
              "default": 34
            }
          }
        },
        "marker-symbol": {
          "id": "marker-symbol",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Style-Data"
            },
            "value": {
              "id": "value",
              "type": "string",
              "default": "marker"
            }
          }
        },
        "name": {
          "id": "name",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Editable-Data"
            },
            "value": {
              "id": "value",
              "type": "string",
              "default": "name"
            }
          }
        },
        "stroke": {
          "id": "stroke",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Style-Data"
            },
            "value": {
              "id": "value",
              "type": "string",
              "default": "#555555"
            }
          }
        },
        "stroke-opacity": {
          "id": "stroke-opacity",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Style-Data"
            },
            "value": {
              "id": "value",
              "type": "integer",
              "default": 1
            }
          }
        },
        "stroke-width": {
          "id": "stroke-width",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Style-Data"
            },
            "value": {
              "id": "value",
              "type": "integer",
              "default": 2
            }
          }
        },
        "title": {
          "id": "title",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Editable-Data"
            },
            "value": {
              "id": "value",
              "type": "string",
              "default": "title"
            }
          }
        },
        "updated": {
          "id": "updated",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Fix-Data"
            },
            "value": {
              "id": "value",
              "type": "string",
              "default": ""
            }
          }
        },
        "uuid": {
          "id": "uuid",
          "type": "object",
          "properties": {
            "category": {
              "id": "category",
              "type": "string",
              "default": "Fix-Data"
            },
            "value": {
              "id": "value",
              "type": "integer",
              "default": -10
            }
          }
        }
      },
      "required": [
        "created",
        "description",
        "marker-size",
        "marker-symbol",
        "name",
        "stroke",
        "stroke-opacity",
        "stroke-width",
        "title",
        "updated",
        "uuid"
      ]
    }
  },
  "required": [
    "Polyline"
  ]
}','','var');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','scaleText',2,1,'Scale-description field position:fixed;bottom:15px;left:337px;','Maßstab per Texteingabe','form','','action="window.location.href" onsubmit="return mod_scaleText()" ',NULL ,NULL,NULL ,NULL,1000,'','<input type="text" value="Bsp.: 1500" onfocus="clearField(this)"><script type=text/javascript>function clearField( field ){if(field.value==field.defaultValue)field.value='''';}</script>','form','mod_scaleText.php','','mapframe1','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','addWMS',2,1,'add a WMS to the running application
','Kartenebene hinzufügen','A','','',NULL ,NULL,NULL ,NULL,NULL ,'','<svg width="16" height="16" viewBox="0 1 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
<path fill-rule="evenodd" clip-rule="evenodd" d="M8 2H6V6H2V8H6V12H8V8H12V6H8V2Z" fill="currentColor"/>
</svg>WMS URL hinzuladen','A','mod_addWMS.php','mod_addWMSgeneralFunctions.js','treeGDE,mapframe1','loadData','http://www.mapbender.org/index.php/AddWMS');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','addWMS','css','input{box-sizing:border-box;}','','text/css');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','savewmc',2,1,'save workspace as WMC
SRC:../img/button_hessen/wmcsave_off.png
Width:28
Height:29
Z-INDEX:1001
Styles:position:absolute
','Kartenzusammenstellung speichern','a','','',NULL ,NULL,NULL ,NULL,NULL ,'','<svg version="1.0" xmlns="http://www.w3.org/2000/svg"
 width="12.000000pt" height="12.000000pt" viewBox="0 0 1280.000000 1280.000000"
 preserveAspectRatio="xMidYMid meet">
<g transform="translate(0.000000,1280.000000) scale(0.100000,-0.100000)"
fill="currentColor" stroke="none">
<path d="M4560 9980 l0 -2240 -1050 -2 -1050 -3 1969 -2427 c1083 -1335 1972
-2433 1975 -2440 4 -9 8 -10 12 -1 3 6 892 1104 1975 2440 l1970 2428 -1048 5
-1048 5 -3 2238 -2 2237 -1850 0 -1850 0 0 -2240z"/>
<path d="M310 2885 l0 -2305 6090 0 6090 0 -2 2293 -3 2292 -727 3 -728 2 0
-1566 0 -1567 -1392 7 c-766 3 -2340 11 -3498 16 -1158 5 -2615 13 -3237 16
l-1133 6 0 1554 0 1554 -730 0 -730 0 0 -2305z"/>
</g>
</svg>Speichern','a','mod_savewmc.php','','mapframe1','jq_ui_dialog','http://www.mapbender.org/index.php/SaveWMC');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','savewmc','lzwCompressed','false','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','savewmc','overwrite','1','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','savewmc','saveInSession','1','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','savewmc','dialogHeight','650','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','savewmc','css','/* INSERT savewmc -> elementVar -> css(text/css) */
#savewmc_saveWMCForm label {
margin: 7px 0px 4px 0px;}
#savewmc_wmctype{
width:100%;
box-sizing:border-box;
padding:5px;
border:1px solid #777;
font-familiy: Helvetica,Arial,Sans-serif;}
#savewmc_wmcname,#savewmc_wmcabstract,#savewmc_wmckeywords {width:100%;
box-sizing:border-box;
padding:5px 5px 5px 8px;
border:1px solid #777;
font-familiy: Helvetica,Arial,Sans-serif;
letter-spacing:1px;}
.wmcIsoTopicCategory {
margin-right: 10px;}
/* END INSERT savewmc -> elementVar -> css(text/css) */','','text/css');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','featureInfo1',2,1,'FeatureInfoRequest','Datenabfrage','img','../img/geoportal2019/infoabfrage_off.svg','',NULL ,NULL,NULL ,NULL,3,'cursor:pointer;','','','mod_featureInfo.php','','mapframe1','','http://www.mapbender.org/index.php/FeatureInfo');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','featureInfo1','featureInfoDrawClick','true','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','featureInfo1','featureInfoLayerPopup','true','display featureInfo in dialog popup','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','featureInfo1','featureInfoLayerPreselect','true','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','featureInfo1','featureInfoPopupHeight','600','height of the featureInfo dialog popup','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','featureInfo1','featureInfoPopupWidth','550','width of the featureInfo dialog popup','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','featureInfo1','reverseInfo','true','Reorder featureInfo result','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','featureInfo1','featureInfoPopupPosition','[20,80]','position of the featureInfoPopup [left,top]','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','featureInfo1','featureInfoCollectLayers','true','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','featureInfo1','featureInfoShowKmlTreeInfo','true','only if kmltree included in gui','var');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','resizeMapsize',2,1,'resize_mapsize -auto-','','div','','',1,1,1,1,NULL ,'div','','','mod_resize_mapsize.js','','mapframe1','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','resizeMapsize','adjust_width','','to adjust the width of the mapframe on the right side of the window','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','resizeMapsize','adjust_height','','to adjust the height of the mapframe on the bottom of the window','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','resizeMapsize','resize_option','auto','auto (autoresize on load), button (resize by button)','var');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','layout_nutzungsbedingungen',2,0,'layout, ','Nutzungsbedingungen','div','','onclick="javascript:window.open(''http://10.176.178.10/mapbender/php/mod_getWmcDisclaimer.php?&id=25&languageCode=de&withHeader=true&hostName='','''','''');"',NULL ,NULL,NULL ,NULL,5,'position:fixed;width:150px;bottom:0px;right:0px;background-color:rgba(255,255,255,1);cursor:pointer;','Nutzungsbedingungen','div','','','','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','mousewheelZoom',2,1,'adds mousewheel zoom to map module (target)','Mousewheel zoom','div','','',NULL ,NULL,NULL ,NULL,NULL ,'','','div','mod_mousewheelZoom.js','../extensions/jquery.mousewheel.min.js','mapframe1','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','mousewheelZoom','factor','2','The factor by which the map is zoomed on each mousewheel unit','var');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','jq_ui_mouse',2,1,'jQuery UI mouse','','','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','','../extensions/jquery-ui-1.8.16.custom/development-bundle/ui/minified/jquery.ui.mouse.min.js','','jq_ui_widget','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','printPDF',2,1,'pdfprint','Druck','div','','',NULL ,NULL,250,231,5,'','<div id="printPDF_working_bg"></div><div id="printPDF_working"><img src="../img/indicator_wheel.gif" style="padding:10px 0 0 10px">Generating PDF</div><div id="printPDF_input"><form id="printPDF_form" action="../print/printFactory.php"><div id="printPDF_selector"></div><div class="print_option"><input type="hidden" id="map_url" name="map_url" value=""/><input type="hidden" id="legend_url" name="legend_url" value=""/><input type="hidden" id="opacity" name="opacity" value=""/> <input type="hidden" id="overview_url" name="overview_url" value=""/><input type="hidden" id="map_scale" name="map_scale" value=""/><input type="hidden" name="measured_x_values" /><input type="hidden" name="measured_y_values" /><input type="hidden" name="map_svg_kml" /><input type="hidden" name="svg_extent" /><input type="hidden" name="map_svg_measures" /><br /></div><div class="print_option" id="printPDF_formsubmit"><input id="submit" type="submit" value="Print"><br /></div></form><div id="printPDF_result"></div></div>','div','../plugins/mb_print.php','../../lib/printbox.js,../extensions/jquery-ui-1.8.16.custom/development-bundle/external/jquery.bgiframe-2.1.2.js,../extensions/jquery.form.min.js,../extensions/wz_jsgraphics.js','mapframe1','','http://www.mapbender.org/index.php/Print');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','printPDF','css','#printPDF input, #printPDF select, #printPDF textarea {box-sizing: border-box;}','','text/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','printPDF','legendColumns','0','define number of columns on legendpage','php_var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','printPDF','reverseLegend','false','define whether the legend should be printed in reverse order','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','printPDF','printLegend','true','define whether the legend should be printed or not','php_var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','printPDF','secureProtocol','true','define blabla','php_var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','printPDF','mbPrintConfig','{"Format wählen": "Dummy_A4.json","A4 Hochformat": "Hochformat_A4.json","A4 Hochformat mit Legende": "Hochformat_A4_Legende_mehrseitig.json","A4 Querformat": "Querformat_A4.json","A3 Hochformat": "Hochformat_A3.json","A3 Querformat": "Querformat_A3.json"}','','var');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','scalebar',2,1,'scalebar','Maßstabsleiste','div','','',NULL ,NULL,NULL ,NULL,NULL ,'background-color: rgba(255,255,255,0.8);  left: 0 !important; display: none;','','div','mod_scalebar_test.js','','mapframe1','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','scalebar','css','#mapframe1_scalebar div {
    margin: -3px 0 0px 15px;}
#mapframe1_scalebar img {
    padding: 6px 5px 5px;}
#mapframe1_scalebar{
background-color: rgba(255,255,255,0.8);
left: 0 !important;}','','text/css');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','mapsContainer',2,1,'This Container THEMEN appends all its target elements to its container inside li-childs
','','div ','','',NULL ,NULL,NULL ,NULL,NULL ,'','<a title="Kartenebenen auswählen" class="toggleLayerTree"><svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M2 3H16V5H2V3Z" fill=""/>
<path d="M2 8H16V10H2V8Z" fill=""/>
<path d="M16 13H2V15H16V13Z" fill=""/>
</svg>Themen</a>','div','../plugins/mb_toolbar_tree.js','','loadWMC_Button,savewmc,WMS_preferencesButton,treeGDE','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','mapsContainer','css','/* INSERT body -> mapsContainer -> css(text/css) */
#mapsContainer {
   position:relative;
   dislay: none;
   float: left;
   border-bottom:1px solid transparent;
}

.toggleLayerTree {
   font-family:Helvetica,Roboto,Arial,sans-serif;
   color: #777;
   font-style: normal;
   font-weight: 700;
   letter-spacing: 1px;
   border-left: 2px solid #DDD;
   padding: 0px 15px;
   line-height: 50px;
   display: block;
   border-bottom: 1px solid transparent;
}
.toggleLayerTree.activeToggle {
background-color: #EEE;
color: #333;
border-bottom:1px solid #d62029 !important;
}

.toggleLayerTree svg {
   fill: currentColor;
   margin-top: -3px;
   margin-right: 4px;
   vertical-align: middle;
}
.toggleLayerTree:hover {
   color: #333;
   cursor:pointer;
   background-color:#EEE
}

#tree2Container {
  margin:1px;
  padding:0;
  list-style-type:none;
  position: absolute;
  /*display: block;
  width: 388px;
  box-shadow: 0px 5px 10px -2px rgb(201, 202, 202);*/
}

#loadWMC_Button,#savewmc, #WMS_preferencesButton {
    line-height: 3;
    font-family: Helvetica,Roboto,Arial,sans-serif;
    color: #fff;
    font-style: normal;
    font-size: 12px;
    letter-spacing: 1px;
    box-sizing: border-box;
    display: block;
    width: 177px;
    cursor: pointer;
    border-bottom: 1px solid transparent;
    background-color: #555;
    border-bottom: 1px solid #efefef;
    float: left;
}
#savewmc svg, #loadWMC_Button svg, #WMS_preferencesButton svg {
    margin-left: 7px;
    margin-right: 5px;
    margin-top: 5px;
    margin-bottom: -3px;
}
#WMS_preferencesButton svg {

    margin-top: 11px;
    margin-left: 9px;
}
/* END INSERT body -> mapsContainer -> css(text/css) */','','text/css');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','mapframe1',2,1,'frame for a map
','','div','','',0,0,690,527,2,'overflow:hidden;background-color:#ffffff','','div','../plugins/mb_map.js','../../lib/history.js,map_obj.js,map.js,wms.js,wfs_obj.js,initWmcObj.php','','','http://www.mapbender.org/index.php/Mapframe');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','mapframe1','slippy','1','1 = Activates an animated, pseudo slippy map','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','mapframe1','skipWmsIfSrsNotSupported','1','if set to 1, it skips the WMS request if the current SRS is not supported by the WMS; if set to 0, the WMS is always queried. Default is 0, because of backwards compatibility','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','mapframe1','wfsConfIdString','94','','text/css');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','deleteSessionWmc',2,1,'delete Session Wmc','Kartenansicht zurücksetzen','A','../img/button_blue_red/repaint_off.png','onclick=''$("#sessionWmc").mapbender().deleteWmc();''
onmouseover=''$(this).addClass("myOverClass");'' onmouseout=''$(this).removeClass("myOverClass");''',NULL ,NULL,NULL ,NULL,NULL ,'position:unset','<svg width="16" height="16" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M2.75736 2.75736C3.84315 1.67157 5.34315 1 7 1C10.3137 1 13 3.68629 13 7C13 10.3137 10.3137 13 7 13C4.027 13 1.55904 10.8377 1.08296 8H3.02282C3.46921 9.78103 5.08057 11.1 7 11.1C9.26437 11.1 11.1 9.26437 11.1 7C11.1 4.73563 9.26437 2.9 7 2.9C5.86782 2.9 4.84282 3.35891 4.10086 4.10086L6 6H1V1L2.75736 2.75736Z" fill="currentColor"/>
</svg>Geoportal Zurücksetzen','','','','mapframe1','sessionWmc','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','measure_widget',2,1,'Measure
../img/button_hessen/messen_off.png','Messwerkzeug','A','','',NULL ,NULL,NULL ,NULL,NULL ,'','<svg width="16" height="16" viewBox="0 0 50.000000 50.000000" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet">
 <!-- Created with Method Draw - http://github.com/duopixel/Method-Draw/ -->

 <g>
  <title>background</title>
  <rect x="-1" y="-1" width="50" height="50" id="canvas_background" fill="none"/>
 </g>
 <g>
  <title>Layer 1</title>
  <path id="svg_3" d="m10.043528,38.202782l-9.281173,9.240414l46.646738,0l0,-46.449539l-37.365564,37.209125zm21.87467,-20.4189c0.336987,-0.330269 0.869781,-0.330269 1.203125,0l2.102036,2.091314c0.333336,0.330252 0.336952,0.866106 0.003628,1.196295c-0.166775,0.164221 -0.386063,0.247162 -0.601707,0.247162c-0.219154,0 -0.434822,-0.082941 -0.597931,-0.247162l-2.10915,-2.091295c-0.331608,-0.33021 -0.331608,-0.864359 0,-1.196314zm-2.901077,2.89426c0.333332,-0.330236 0.869757,-0.330236 1.199606,0l3.906706,3.886665c0.333359,0.330259 0.333359,0.866102 0.003582,1.19272c-0.16674,0.1661 -0.386028,0.250893 -0.60516,0.250893s-0.434948,-0.082926 -0.598099,-0.247269l-3.906635,-3.886709c-0.333355,-0.330177 -0.333355,-0.86611 0,-1.196301zm-2.906431,2.88895c0.333334,-0.330223 0.869699,-0.330223 1.20306,0l2.10202,2.094923c0.33337,0.330032 0.329746,0.866106 -0.003607,1.196293c-0.166803,0.165915 -0.382402,0.249065 -0.597919,0.249065c-0.219297,0 -0.434958,-0.083151 -0.601715,-0.249065l-2.101839,-2.093214c-0.331667,-0.330189 -0.331667,-0.867792 0,-1.198002zm-2.901098,2.89238c0.333305,-0.330252 0.869724,-0.326632 1.203238,0.003601l2.098253,2.091345c0.331589,0.33214 0.331589,0.866096 0,1.196314c-0.166786,0.166069 -0.384172,0.248991 -0.601572,0.248991c-0.215649,0 -0.434942,-0.082922 -0.601555,-0.248991l-2.098364,-2.094954c-0.331629,-0.331932 -0.331629,-0.866116 0,-1.196306zm-2.904753,2.894266c0.331644,-0.331959 0.867996,-0.331959 1.201515,0l3.90307,3.884958c0.333363,0.332088 0.333363,0.868034 0,1.199978c-0.166805,0.166054 -0.382439,0.248959 -0.597921,0.248959c-0.219307,0 -0.438585,-0.082905 -0.605333,-0.248959l-3.901331,-3.888432c-0.331438,-0.332111 -0.331438,-0.866253 0,-1.196505zm-3.607708,13.755163c-0.164837,0.167984 -0.384182,0.250881 -0.601563,0.250881c-0.21567,0 -0.431316,-0.081142 -0.597885,-0.247261l-3.90323,-3.884785c-0.333367,-0.330227 -0.333367,-0.864384 0,-1.196541s0.868009,-0.331909 1.199634,0l3.903044,3.884975c0.331575,0.328522 0.331575,0.862682 0,1.19273zm1.105467,-4.686001c-0.168697,0.167747 -0.387787,0.250759 -0.603462,0.250759c-0.217394,0 -0.434967,-0.081059 -0.597906,-0.247185l-2.105682,-2.094913c-0.333348,-0.330235 -0.333348,-0.864372 0,-1.191025c0.333348,-0.333824 0.869767,-0.337437 1.203253,-0.005352l2.103798,2.096714c0.331587,0.324902 0.331587,0.860786 0,1.191002zm2.900906,-2.890682c-0.166573,0.16589 -0.382233,0.24902 -0.60157,0.24902c-0.21563,0 -0.433065,-0.08313 -0.599789,-0.24902l-2.100113,-2.093208c-0.333366,-0.331932 -0.333366,-0.866116 0,-1.198086s0.869703,-0.328474 1.201326,0.003609l2.100145,2.094959c0.331652,0.330086 0.331652,0.866123 0,1.192726zm17.643661,2.888847l-15.710079,0l15.710079,-15.644333l0,15.644333zm-0.217373,-20.240208c-0.163151,0.165867 -0.382423,0.249041 -0.597939,0.249041c-0.219261,0 -0.438549,-0.083174 -0.605316,-0.249041l-2.101837,-2.091324c-0.333549,-0.330246 -0.333549,-0.864338 0,-1.196292c0.32967,-0.330277 0.86795,-0.330277 1.199455,0l2.102028,2.091287c0.333328,0.331947 0.335228,0.866114 0.003609,1.196329zm4.703983,-1.097187c-0.166698,0.166075 -0.386021,0.247259 -0.601574,0.247259s-0.434937,-0.081184 -0.598038,-0.247259l-3.903034,-3.886715c-0.333321,-0.330194 -0.333321,-0.867958 0,-1.198209s0.873371,-0.326599 1.20327,0.003593l3.902992,3.88688c0.331661,0.33021 0.328037,0.866114 -0.003616,1.19445z" fill-opacity="null" stroke-opacity="null" stroke-width="0" stroke="none" fill="currentColor"/>
 </g>
</svg>Messen','A','../plugins/mb_measure_widget.php','../widgets/w_measure.js,../extensions/RaphaelJS/raphael-1.4.7.min.js','mapframe1','jq_ui_dialog,jq_ui_widget','http://www.mapbender.org/index.php/Measure');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','measure_widget','lineStrokeDefault','#C9F','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','measure_widget','lineStrokeSnapped','#F30','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','measure_widget','lineStrokeWidthDefault','3','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','measure_widget','lineStrokeWidthSnapped','5','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','measure_widget','measurePointDiameter','7','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','measure_widget','opacity','0.4','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','measure_widget','pointFillDefault','#CCF','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','measure_widget','pointFillSnapped','#F90','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','measure_widget','pointStrokeDefault','#FF0000','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','measure_widget','pointStrokeSnapped','#FF0000','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','measure_widget','pointStrokeWidthDefault','2','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','measure_widget','polygonFillDefault','#FFF','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','measure_widget','polygonFillSnapped','#FC3','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','measure_widget','polygonStrokeWidthDefault','1','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','measure_widget','polygonStrokeWidthSnapped','5','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','measure_widget','dialogHeight','300','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','measure_widget','dialogWidth','190','','var');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','resultList',2,1,'position defined in elementVar','Result List','div','','',NULL ,NULL,NULL ,NULL,NULL ,'','','div','mod_ResultList.js','../../lib/resultGeometryListController.js, ../../lib/resultGeometryListModel.js','mapframe1','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','resultList','resultListHeight','350','height of the result list dialog','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','resultList','resultListTitle','Suchergebnisse','title of the result list dialog','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','resultList','resultListWidth','500','width of the result list dialog','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','resultList','tableTools666','[{ "sExtends": "xls",        "sButtonText": "Export to CSV",   "sFileName": "result.csv"  }]','set the initialization options for tableTools','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','resultList','position','[120,119]','position of the result list dialog','var');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','zoomFull',2,1,'zoom to full extent button','Auf gesamte Karte zoomen','img','../img/geoportal2019/globe_off.svg','',NULL ,NULL,NULL ,NULL,103,'','','','mod_zoomFull.js','','mapframe1','','http://www.mapbender.org/index.php/ZoomFull');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','zoomIn1',2,1,'zoomIn button','In die Karte hineinzoomen','img','../img/geoportal2019/plus_off.svg','',NULL ,NULL,NULL ,NULL,103,'','','','mod_zoomIn1.js','','mapframe1','','http://www.mapbender.org/index.php/ZoomIn');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','zoomOut1',2,1,'zoomOut button','Aus der Karte herauszoomen','img','../img/geoportal2019/minus_off.svg','',NULL ,NULL,NULL ,NULL,103,'','','','mod_zoomOut1.js','','mapframe1','','http://www.mapbender.org/index.php/ZoomOut');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','dependentDiv',2,1,'displays infos in a sticky div-tag
font-size: 11px;font-family: "Arial", sans-serif;visibility:visible;','','div','','',NULL ,NULL,NULL ,NULL,300,'position:relative;','','div','mod_dependentDiv.php','','overview','','http://www.mapbender.org/index.php/DependentDiv');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','dependentDiv','CSScoordsDiv','','','text/css');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','resultList_Zoom',2,1,'zoom functionality for resultList','','div','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','../plugins/mb_resultList_Zoom.js','','resultList,mapframe1','','http://www.mapbender.org/resultList_Zoom');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','resultList_Highlight',2,1,'highlighting functionality for resultList works only with overview enabled','','div','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','../plugins/mb_resultList_Highlight.js','','resultList,mapframe1,overview','','http://www.mapbender.org/resultList_Highlight');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','resultList_Highlight','maxHighlightedPoints','500','max number of points to highlight','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','resultList_Highlight','resultHighlightColor','#ff0000','color of the highlighting','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','resultList_Highlight','resultHighlightLineWidth','2','width of the highlighting line','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','resultList_Highlight','resultHighlightZIndex','149','zindex of the highlighting','var');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','overview',2,1,'OverviewFrame','Übersichtskarte','div','','',NULL ,NULL,110,115,3,'margin:10px;overflow:hidden;background-color:#ffffff;right:0;bottom:22px;position:absolute;top:unset;left:unset;','<div id="overview_maps" style=""></div>','div','../plugins/mb_overview.js','map_obj.js,map.js,wms.js,wfs_obj.js,initWmcObj.php','mapframe1','mapframe1','http://www.mapbender.org/index.php/Overview');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','overview','skipWmsIfSrsNotSupported','0','if set to 1, it skips the WMS request if the current SRS is not supported by the WMS; if set to 0, the WMS is always queried. Default is 0, because of backwards compatibility','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','overview','overview_wms','0','wms that shows up as overview','var');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','showCoords_div',2,1,'displays coordinates by onmouseover
../img/button_hessen/showcoords_off.png','Koordinaten anzeigen','A','','onmouseover = "mb_regButton(''init_mod_showCoords_div'')" ',NULL ,NULL,NULL ,NULL,NULL ,'','<svg xmlns="http://www.w3.org/2000/svg" height="16" viewBox="0 0 24.000001 24.000001" width="16"><g transform="matrix(1.2 0 0 1.2 -2.5355937 -1236.2991)"><path d="M7.0000003 1040.3622H17.000001M12.000001 1035.3621v10.0001" fill="none" stroke="currentColor" stroke-width="1.50000012"/><ellipse cx="12.000001" cy="1040.3622" rx="1.499993" ry="1.4999931" fill="currentColor"/><path d="M11.250001 1030.3622v1.9454c-3.8637771.3575-6.9473551 3.4415-7.3046881 7.3046h-1.945312v1.4981h1.945312c.356486 3.864 3.440264 6.9511 7.3046881 7.3086v1.9433h1.5v-1.9433c3.864427-.3575 6.948205-3.4446 7.304687-7.3086h1.945313v-1.4981h-1.945313c-.35733-3.8631-3.440907-6.9471-7.304687-7.3046v-1.9454zm.75 3.4063c.912362 0 1.781158.1826 2.570312.5156.789154.333 1.499679.816 2.095703 1.4121.596024.5962 1.077184 1.3063 1.410156 2.0957.332973.7894.517579 1.6575.517579 2.5703 0 .9129-.184606 1.7811-.517579 2.5704-.332972.7892-.814132 1.4997-1.410156 2.0957-.596024.5959-1.306549 1.0773-2.095703 1.4101-.789154.3328-1.65795.5176-2.570312.5176-.912362 0-1.781159-.1848-2.5703131-.5176-.789153-.3328-1.497726-.8142-2.09375-1.4101-.596024-.596-1.079137-1.3065-1.412109-2.0957-.332973-.7893-.515625-1.6575-.515625-2.5704 0-.9128.182652-1.7809.515625-2.5703.332972-.7894.816085-1.4995 1.412109-2.0957.596024-.5961 1.304597-1.0791 2.09375-1.4121.7891541-.333 1.6579511-.5156 2.5703131-.5156z" overflow="visible" fill-rule="evenodd"/></g></svg>Koordinaten anzeigen','A','mod_coords_div.php','../extensions/mapcode-js-master/mapcode.js,../extensions/mapcode-js-master/ndata.js,../extensions/mapcode-js-master/ctrynams.js','mapframe1','','http://www.mapbender.org/index.php/ShowCoords_div');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','showCoords_div','css','/* INSERT showCoords_div -> elementVar -> css(text/css) */
div.actualcoords {
background-color:#EFEFEF;
padding-top:18px;
padding-right:18px;
padding-bottom:10px;
padding-left:10px;
position:fixed;
width:250px;
left:20px;
top:80px;
font-family: Helvetica,Roboto,Arial,sans-serif;
color: #777;
font-style: normal;
font-size: 12px;
letter-spacing: 1px;
border: 1px solid #aaa !important;
background: #ffffff url(images/ui-bg_flat_75_ffffff_40x100.png) 50% 50% repeat-x;
box-shadow: 0 5px 10px -2px rgb(201, 202, 202);
}
div.selectedcoords {
margin-top:10px;
}
img.mapcodehelp {
margin-bottom:5px;
}
div.selectedmapcode {
margin-top:10px;
}
div.selectedmapcode img:hover {
cursor:pointer;
}
#closeDivButton:hover {
border: 1px solid #999999 !important;
background-color: #EEE;
}
/* END INSERT showCoords_div -> elementVar -> css(text/css) */','','text/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','showCoords_div','useMapcode','true','','var');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','selArea1',2,1,'ABHÄNGIGKEITEN
zoombox
<img..>../img/button_hessen/zoomArea4_off.png','Ausschnitt durch Aufziehen einer Fläche vergrößern','A','','',NULL ,NULL,NULL ,NULL,NULL ,'','<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24.000001" height="19" width="19">
<path d="M15.139163 1041.2225c-.392993 1.7467-2.217698 2.2202-2.217698 2.2202l7.392464 7.4007 2.217697-2.2201z" fill-rule="evenodd" stroke="none" stroke-width="1.56905377" stroke-linejoin="round" transform="matrix(1.04595 0 0 1.0464 -.49952522 -1076.3057)" fill="currentColor"></path>
<path d="M1.1597145 1037.5041c0 4.4746 3.6230358 8.1021 8.0922782 8.1021 4.4692413 0 8.0922783-3.6275 8.0922783-8.1021 0-4.4747-3.623037-8.1023-8.0922783-8.1023-4.4692424 0-8.0922782 3.6276-8.0922782 8.1023z" fill="none" stroke="currentColor" stroke-width="1.56905377" stroke-linecap="round" stroke-linejoin="round" stroke-dashoffset="7" transform="matrix(1.04595 0 0 1.0464 -.49952522 -1076.3057)"></path>
<g fill="none" stroke="currentColor" stroke-width="2.9000001" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9.1501655 1032.8214v8.6M13.450165 1037.1214H4.8501655" overflow="visible" transform="matrix(1.04595 0 0 1.0464 -.39301842 -1075.90534813)"></path>
</g>
<path d="M18.72204773 16.33189514l-2.0919064-2.092796-2.09190744 2.092796 2.09190744 2.092796z" fill-rule="evenodd" fill="currentColor"></path>
</svg>
Auswahl vergrößern','A','mod_selArea.js','mod_box1.js','mapframe1','','http://www.mapbender.org/index.php/SelArea1');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','selArea1','css','#selArea1 svg {
padding: 15px 14px 15px 17px;
border-left: 1px solid #DDD;
color: #777;
background-color: rgba(255,255,255,0.98);}
#selArea1 svg:hover {color: #333;background-color: #EEE;}','','text/css');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','loadwmc',2,1,'load workspace from WMC
SRC: ../img/button_hessen/wmcload_off.png
Attributes: onmouseover=''this.src = this.src.replace(/_off/,"_over");''  onmouseout=''this.src = this.src.replace(/_over/, "_off");''','Meine Themen verwalten','div','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','mod_loadwmc.php','popup.js','mapframe1','jq_ui_dialog,jq_ui_tabs,jq_upload,jq_datatables','http://www.mapbender.org/index.php/LoadWMC');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','loadwmc','deleteWmc','1','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','loadwmc','dialogHeight','550','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','loadwmc','dialogWidth','350','','text/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','loadwmc','loadFromSession','1','','php_var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','loadwmc','mobileUrl','1','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','loadwmc','mobileUrlNewWindow','0','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','loadwmc','publishWmc','0','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','loadwmc','saveWmcTarget','savewmc','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','loadwmc','showPublic','0','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','loadwmc','editWmc','0','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','loadwmc','reinitializeLoadWmc','true','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','loadwmc','allowResize','true','','var');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','loadData',2,1,'IFRAME to load data','','iframe','../html/mod_blank.html','frameborder = "0" ',0,0,1,1,NULL ,'visibility:visible','','iframe','','','','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','jsonAutocompleteGazetteer',2,1,'Client for json webservices like geonames.orgposition:fixed;top:0.5em;left: 1em;','Gazetteer','div','','title="Nach Addressen suchen"',NULL ,NULL,NULL ,NULL,2999,'float:right;position:absolute;right:0px;background-color:white;','','div','../plugins/mod_jsonAutocompleteGazetteer2019.php','','mapframe1','','http://www.mapbender.org/index.php/mod_jsonAutocompleteGazetteer.php');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','jsonAutocompleteGazetteer','gazetteerUrl','https://www.geoportal.rlp.de/mapbender/geoportal/gaz_geom_mobile.php','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','jsonAutocompleteGazetteer','helpText','','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','jsonAutocompleteGazetteer','isGeonames','false','','var');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','scaleDiv',2,1,'','Maßstab','div','','class=''hide-during-splash''',NULL ,NULL,NULL ,NULL,100,'','<svg style=''float:right;transform:rotate(90deg);'' width="17" height="18" viewBox="0 0 18 18" fill="none"  xmlns="http://www.w3.org/2000/svg">
<path d="M16.0142 11.6191L14.6042 13.0291L9.01416 7.43914L3.42416 13.0291L2.01416 11.6191L9.01416 4.61914L16.0142 11.6191Z" stroke="currentColor" stroke-width="2"/>
</svg>','div','../plugins/mb_scaleContainer.js','','scaleSelect, scaleText','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','scaleDiv','css','
/* INSERT scaleDiv -> elementVar -> css(text/css) */

#scaleContainer{
display:none;
margin:0;
padding:0;
list-style-type: none;
float:left;
}

#scaleDiv{
position:fixed;
left:0;
bottom:20px;
background-color:#EEE;
font-family: Helvetica,Roboto,Arial,sans-serif;
color: #777;
font-style: normal;
font-weight: 700;
letter-spacing: 1px;
padding:5px;
border-top: 2px solid #DDD;
border-bottom: 2px solid #DDD;
border-right: 2px solid #DDD;
}
#scaleDiv:hover{
cursor: pointer;
color: #333;
}

.scaleDivOpened svg{
transform: rotate(-90deg) !important;
margin-top: 21%;
}

#scaleContainer select, #scaleContainer input {
left: 0px;
width: 120px;
border: 1px solid #777;
line-height: 1.5;
margin:5px;
font-family: Helvetica, Roboto,Arial,sans-serif;
letter-spacing: 1px;
}
#scaleContainer select{
padding: 5px;
margin-bottom:5px;
}
#scaleContainer input{
padding: 4px 6px 4px 6px;
margin-top:5px;
}

/* END INSERT scaleDiv -> elementVar -> css(text/css) */
','','text/css');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','pan1',2,1,'pan','Ausschnitt verschieben','img','../img/geoportal2019/move_off.svg','',NULL ,NULL,NULL ,NULL,3,'cursor:pointer;padding-top: 14px !important; padding-bottom: 12px !important;','','','mod_pan.js','','mapframe1','','http://www.mapbender.org/index.php/Pan');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','metadataCarouselTinySlider',2,1,'','Metadata carousel','div','','',NULL ,NULL,NULL ,NULL,NULL ,'box-sizing: border-box;','','','../plugins/mod_metadataCarouselTinySlider.php','','','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','metadataCarouselTinySlider','allowResize','true','This element var defines if the viewer should extent wmc to viewer screen size','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','metadataCarouselTinySlider','css_file_metadata_carousel_mb','../css/tiny-slider-mapbender.css','','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','metadataCarouselTinySlider','css_file_metadata_carousel_tinyslider','../extensions/tiny-slider-master/dist/tiny-slider.css','','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','metadataCarouselTinySlider','css_file_metadata_carousel_tinyslider_demo','../css/tiny-slider-demo-style.css','','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','metadataCarouselTinySlider','css_file_metadata_carousel_tinyslider_prism','../extensions/tiny-slider-master/demo/css/prism.css','','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','metadataCarouselTinySlider','maxResults','5','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','metadataCarouselTinySlider','resourceFilter','[]','array of ids to restrict the metadata resources ','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','metadataCarouselTinySlider','searchUrl','../php/mod_callMetadata.php?protocol=https&','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','metadataCarouselTinySlider','slidesPerSide','3','','var');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','copyright',2,1,'a Copyright in the map','Copyright','div','','',0,0,NULL ,NULL,1001,'','','div','mod_termsOfUse.php','','mapframe1','','http://www.mapbender.org/index.php/Copyright');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','copyright','mod_copyright_text','mapbender.org','define a copyright text which should be displayed','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','copyright','css_text_copyright','#mapframe1_copyright div{

color:unset;

background-color:rgba(255,255,255,0.8);

right:0px !important;

bottom:0px !important;

padding:1px 8px;

z-index:1001 !important;}

#mapframe1_copyright div:hover{

background-color:rgba(255,255,255,1);}

#mapframe1_copyright div a{

font-family:Helvetica,Roboto,Arial,sans-serif;

font-size: 12px;}

#mapframe1_copyright div a:hover{} ','','text/css');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','toggleModule',3,1,'','','div','','',1,1,1,1,2,'','','div','mod_toggleModule.php','','pan1','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','kmlTree',3,1,'','Eigene Daten','ul','','',NULL ,NULL,1,1,2,'position:absolute;right:0px;','','ul','../plugins/kmlTree.php','../extensions/togeojson.js,../extensions/jquery-ui-1.8.16.custom/development-bundle/ui/jquery.ui.sortable.js,../extensions/fontIconPicker-2.0.0/jquery.fonticonpicker.js','mapframe1','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','kmlTree','activateRegistrationGroupFilter','1','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','kmlTree','buffer','100','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','kmlTree','kmlTree','../css/kmltree.css','','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','kmlTree','openData_only','1','','var');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','jq_ui_button',4,1,'jQuery UI button','','','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','','../extensions/jquery-ui-1.8.16.custom/development-bundle/ui/jquery.ui.button.js','','jq_ui,jq_ui_widget','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','renderGML',4,0,'renders a gml contained in $_SESSION[''GML'']','','div','','',NULL ,NULL,NULL ,NULL,1,'','','','../javascripts/mod_renderGML.php','','overview,mapframe1','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','jq_ui_droppable',4,1,'jQuery UI droppable','','','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','','../extensions/jquery-ui-1.8.16.custom/development-bundle/ui/minified/jquery.ui.droppable.min.js','','jq_ui,jq_ui_widget,jq_ui_mouse,jq_ui_draggable','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','carouselDiv',4,1,'','Maßstab','div','','',NULL ,NULL,NULL ,NULL,99,'','<div id="carouselDiv_btn">
<svg style=''transform:rotate(0deg);margin: 0 auto;width: 100%;'' width="17" height="18" viewBox="0 0 18 18" fill="none"  xmlns="http://www.w3.org/2000/svg">
<path d="M16.0142 11.6191L14.6042 13.0291L9.01416 7.43914L3.42416 13.0291L2.01416 11.6191L9.01416 4.61914L16.0142 11.6191Z" stroke="currentColor" stroke-width="2"/>
</svg><p class=''carouselDiv_btn_name''>Karten entdecken?</p></div>','div','../plugins/mb_carouselContainer.js','','metadataCarouselTinySlider','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','carouselDiv','css','/* INSERT carouselDiv -> elementVar -> css(text/css) */

#carouselDiv_btn{
position:absolute;
bottom:0px;
background-color:rgba(255,255,255,1);
font-family: Helvetica,Roboto,Arial,sans-serif;
color: #777;
font-style: normal;
font-weight: 700;
letter-spacing: 1px;
padding:5px 20px;
border-top: 2px solid #DDD;
border-left: 2px solid #DDD;
border-right: 2px solid #DDD;
box-sizing: border-box;
}
#carouselDiv_btn:hover{
cursor: pointer;
color: #333;

}
#carouselDiv_btn p{
margin:0;
padding:0;
text-align:center;
}
.carouselDiv_btn_Opened svg{
transform: rotate(-180deg) !important;
}

#carouselContainer{
display:none;
margin:0;
padding:0;
list-style-type: none;
float:left;
position: absolute;
bottom: 20px;
overflow:hidden;
}

#carouselDiv{
width: 100vw;
position: fixed;
bottom: 0;
display: flex;
flex-direction: row;
justify-content: center;
}

.carouselDiv_btn_Opened {
bottom: 20px !important;
border-bottom: 2px solid #DDD;
padding: 5px !important;
}

.tns-liveregion {display:none;}

@media (max-width: 942px) {
#carouselContainer{
width: calc(100vw - 50px);
max-width: 630px;
height: 164px;
}

.carouselDiv_btn_Opened {
width: calc(100vw - 54px) !important;
max-width: 630px;
height: 194px;
}

}

@media (min-width: 943px) {

#carouselContainer{
width: calc(100vw - 477px);
min-width: 630px;
max-width: 630px;

height: 164px;
}

.carouselDiv_btn_Opened {
min-width: 630px;
max-width: 630px;
/*width: calc(100vw - 477px) !important;*/
height: 194px;
}
}

/* END INSERT carouselDiv -> elementVar -> css(text/css) */
','','text/css');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','treeGDE',4,1,'new treegde2 - directory tree, checkbox for visible, checkbox for querylayer

visibility:hidden;overflow:hidden;
visibility:visible;position:relative;display:block,clear:left','','div','','',NULL ,3,NULL ,NULL,300,'','','div','../html/mod_treefolderPlain2019.php','jsTree2019.js','mapframe1','mapframe1','http://www.mapbender.org/index.php/TreeGde');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','treeGDE','localizetree','false','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','treeGDE','metadatalink','true','link for layer-metadata','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','treeGDE','datalink','true','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','treeGDE','menu','wms_up,wms_down,opacity_up,opacity_down,layer_up,layer_down,zoom,hide,change_style,remove','context menu elements','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','treeGDE','imagedir','../img/geoportal2019','image directory','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','treeGDE','enlargetreewidth','false','false, oder ganzzahl für Pixelbreite','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','treeGDE','openfolder','false','initial open folder','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','treeGDE','showstatus','true','show status in folderimages','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','treeGDE','switchwms','true','enables/disables all layer of a wms','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','treeGDE','wmsbuttons','false','wms management buttons','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','treeGDE','activatedimension','true','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','treeGDE','reverse','true','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','treeGDE','alerterror','false','alertbox for wms loading error','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','treeGDE','ficheckbox','true','checkbox for featureInfo requests','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','treeGDE','handlesublayer','true','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','treeGDE','css','/* INSERT treeGDE-> elementVar -> css(text/css) */
.treeGDE3_tr {
 background-color: #555;
 border-bottom: 1px solid #efefef;
 line-height: 3;
}
.treeGDE3_tr b {
 color: #fff;
 margin: 0px 5px 0px 0px;
 
}

.treeGDE3_tr td:first-child {
  box-shadow: unset;
}
.treeGDE3_tr td:last-child {
  width: 100%;
  padding-right:8px;
}
.treeGDE3_tr td:first-child img.action {
  margin-left:8px;
  margin-right:6px;
  cursor:pointer;
}
.treeGDE3_tr td:last-child img.action {
  margin-right: 5px;
  cursor:pointer;
  margin-top: 8px;
  margin-bottom: -5px;
  
}
.treeGDE3_tr td:last-child input {
  margin: 0 7px 0 0;
  vertical-align: middle;
}
		#treeContainer {
			width: 388px;
			min-width: 388px;
			max-width: 100vw;
			box-shadow: 0px 5px 10px -2px rgb(201, 202, 202);
			max-height: 80vh;
			overflow-x: auto;
			resize: horizontal;
			background: url("../img/geoportal2019/greysquare.jpg") no-repeat right bottom;
		}
#treeContainer table{
  width:100%;
}
#contextMenu tr {
  background-color: #333 !important;
}
#contextMenu table td:last-child {
  width: unset !important;
}
#contextMenu {
  /*position:fixed;*/
  box-shadow: 2px 2px 10px 0px black;
  /*top: 63px !important;*/
  /*left: 390px !important;*/
}
.menu td:last-child img {
  margin-bottom: -6px;
  margin-right: 11px;
}
.metadata_link {
  margin: 8px 0px 10px -18px;
  float:right;
}
.treegde2019 {
  width: 13px;
  margin-bottom: -6px;
  margin-right: 5px;
}
/* END INSERT treeGDE-> elementVar -> css(text/css) */
','','text/css');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','jq_ui_tabs',5,1,'horizontal tabs from the jQuery UI framework','','','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','','../extensions/jquery-ui-1.8.16.custom/development-bundle/ui/jquery.ui.tabs.js','','jq_ui,jq_ui_widget','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','legend',5,1,'legend','Legende','div','','',20,NULL ,NULL,NULL ,NULL,'','','div','../javascripts/mod_legendDiv.php','','mapframe1','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','legend','showlayertitle','true','show the layer title in the legend','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','legend','showwmstitle','true','show the wms title in the legend','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','legend','stickylegend','false','parameter to decide wether the legend should stick on the mapframe1','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','legend','reverse','true','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','legend','reverseLegend','true','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','legend','checkbox_on_off','false','display or hide the checkbox to set the legend on/off','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','legend','css_file_legend','../css/legend.css','','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','legend','legendlink','false','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','legend','showgroupedlayertitle','true','show the title of the grouped layers in the legend','var');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','kmlTree_Button',5,1,'','Digitalisieren','A','','',NULL ,NULL,NULL ,NULL,NULL ,'','<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
<path fill-rule="evenodd" clip-rule="evenodd" d="M13.68 2.15L15.85 4.32C16.05 4.52 16.05 4.83 15.85 5.03L14.5 6.39L11.62 3.51L12.97 2.15C13.17 1.95 13.48 1.95 13.68 2.15ZM2 13.13L10.5 4.63L13.38 7.51L4.88 16.01H2V13.13Z" fill="currentColor"/>
</svg>Objekte Digitalisieren','A','../plugins/mb_button.js','','kmlTree','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','kmlTree_Button','digitize_kml_css','../css/digitize_new.css','','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','kmlTree_Button','iconpicker','../extensions/fontIconPicker-2.0.0/css/jquery.fonticonpicker.min.css','','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','kmlTree_Button','iconpickertheme','../extensions/fontIconPicker-2.0.0/themes/grey-theme/jquery.fonticonpicker.grey.min.css','','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','kmlTree_Button','makiicons','../extensions/makiicons/style.css','maki icon css','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','kmlTree_Button','override_css','.ui-menu.digitize-contextmenu {
z-index: 4000 !important; /*old value:4*/
width: 150px !important; /*old value:120(defined somewhere)*/
}','','text/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','kmlTree_Button','spectrum','../extensions/spectrum.css','spectrum color picker css','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','kmlTree_Button','tablesortercss','../css/tablesorter.css','','file/css');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','scaleSelect',5,1,'Scale-Selectbox','Maßstabsauswahl','select','','onchange=''mod_scaleSelect(this)''',NULL ,NULL,NULL ,NULL,NULL ,'','<option value = ''''>Scale</option> <option value=''100''>1 : 100</option> <option value=''250''>1 : 250</option> <option value=''500''>1 : 500</option> <option value=''1000''>1 : 1000</option> <option value=''2500''>1 : 2500</option> <option value=''5000''>1 : 5000</option> <option value=''10000''>1 : 10000</option> <option value=''25000''>1 : 25000</option> <option value=''30000''>1 : 30000</option> <option value=''50000''>1 : 50000</option> <option value=''75000''>1 : 75000</option> <option value=''100000''>1 : 100000</option> <option value=''200000''>1 : 200000</option> <option value=''300000''>1 : 300000</option> <option value=''400000''>1 : 400000</option> <option value=''500000''>1 : 500000</option> <option value=''600000''>1 : 600000</option> <option value=''700000''>1 : 700000</option> <option value=''800000''>1 : 800000</option> <option value=''900000''>1 : 900000</option> <option value=''1000000''>1 : 1000000</option>','select','../plugins/mb_selectScale.js','','mapframe1','','http://www.mapbender.org/index.php?title=ScaleSelect');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','toolbarContainer',5,1,'This toolbar Container appends all its target elements to its container Werkzeuge
','Werkzeuge','div ','','',NULL ,NULL,NULL ,NULL,NULL ,'','<a title="Werkzeuge" class="toggleToolsContainer"><svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
<path fill-rule="evenodd" clip-rule="evenodd" d="M3 3H8V8H3V3ZM10 3H15V8H10V3ZM10 10H15V15H10V10ZM3 10H8V15H3V10Z" fill="currentColor"/>
</svg>
Werkzeuge</a>','div','../plugins/mb_toolbar_cont.js','','changeEPSG_Button,legendButton,printPdfButton,wfsConfTree,coordsLookUp_Button,showCoords_div,measure_widget,kmlTree_Button,addWMS,deleteSessionWmc','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','toolbarContainer','css','/* INSERT toolbarContainer-> elementVar -> css(text/css) */
#toolbarContainer {
   position:relative;
   dislay: block;
   float: left;
   border-bottom:1px solid transparent;
}
#toolsContainer {
   margin:1px;
   padding:0;
   list-style-type:none;
   position: absolute;
   display: block;
   min-width: 100%;
   width: 250px;
   box-shadow: 0px 5px 10px -2px rgb(201, 202, 202);
   overflow:hidden;
}
#toolsContainer li {
   background-color: #555;
   border-bottom: 1px solid #efefef;
   }
#toolsContainer a {
   line-height: 3;
   font-family:Helvetica,Roboto,Arial,sans-serif;
   color: #fff;
   font-style: normal;
   font-size:12px;
   letter-spacing: 1px;
   box-sizing: border-box;
   display: block;
   min-width: 100%;
   cursor:pointer;
   border-bottom: 1px solid transparent;
}
#toolsContainer a svg {
   fill: currentColor;
   margin: -1px 9px 0 18px;
   vertical-align: middle;
}
.toggleToolsContainer {
   font-family:Helvetica,Roboto,Arial,sans-serif;
   color: #777;
   font-style: normal;
   font-weight: 700;
   letter-spacing: 1px;
   border-left: 2px solid #DDD;
   padding: 0px 15px;
   line-height: 50px;
   display: block;
   border-bottom: 1px solid transparent;
}
.toggleToolsContainer.activeToggle {
background-color: #EEE;
color: #333;
border-bottom:1px solid #d62029 !important;
}
.toggleToolsContainer svg {
   fill: currentColor;
   margin-top: -2px;
   margin-right: 0px;
   vertical-align: middle;
}
.toggleToolsContainer:hover {
   color: #333;
   cursor:pointer;
   background-color:#EEE
}
/* END INSERT toolbarContainer-> elementVar -> css(text/css) */','','text/css');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','toolbar',5,1,'This toolbar NAVIGATION appends all its target elements to its container
~modified js~','Navigation','div ','','class=''mb-toolbar''',NULL ,NULL,NULL ,NULL,NULL ,'','','div','../plugins/mb_toolbar.js','','featureInfo1,pan1','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','toolbar','css','.mb-toolbar {
padding:0px;
margin:0px;
float:left;
}
.mb-toolbar li {
border-left: 2px solid #DDD;
float:left;
}

.mb-toolbar li:last-child {
border-right: 2px solid #DDD;
}
/*.mb-toolbar ul{
display:block;
float:left;
margin: 4px 7px 0px 0px;
}*/

.mb-toolbar ul {
margin:0px;
padding:0px;
list-style-type: none;
}

#toolbar img {
padding: 10px;
display: block;
}','','text/css');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','jq_ui_resizable',5,1,'Resizable from the jQuery UI framework','','','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','../plugins/jq_ui_resizable.js','../extensions/jquery-ui-1.8.16.custom/development-bundle/ui/jquery.ui.resizable.js','','jq_ui,jq_ui_mouse,jq_ui_widget','http://jqueryui.com/demos/resizable/');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','jq_ui_draggable',5,1,'Draggable from the jQuery UI framework','','','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','','../extensions/jquery-ui-1.8.16.custom/development-bundle/ui/minified/jquery.ui.draggable.min.js','','jq_ui,jq_ui_mouse,jq_ui_widget','http://jqueryui.com/demos/draggable/');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','jq_ui_autocomplete',5,1,'Module to manage jQuery UI autocomplete module','','div','','',-1,-1,15,15,NULL ,'','','div','','../extensions/jquery-ui-1.8.16.custom/development-bundle/ui/jquery.ui.autocomplete.js','','jq_ui,jq_ui_widget,jq_ui_position','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','overviewToggle',5,1,'2019','Übersichtskarte','div','','class="overviewToggleClosed"',NULL ,NULL,NULL ,NULL,400,'display:flex;align-items:center;position:absolute;right:0px;bottom:20px;background-color:#EEE;border-top:2px solid #DDD;border-left:2px solid #DDD;border-bottom:2px solid #DDD;display:none;','<svg width="17" height="18" viewBox="0 0 18 18" fill="none"  xmlns="http://www.w3.org/2000/svg">
<path d="M16.0142 11.6191L14.6042 13.0291L9.01416 7.43914L3.42416 13.0291L2.01416 11.6191L9.01416 4.61914L16.0142 11.6191Z" stroke="currentColor" stroke-width="2"/>
</svg>','div','../javascripts/mod_overviewToggle2019.js','','overview','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','overviewToggle','css','
/* INSERT overviewToggle -> elementVar -> css(text/css) */

.overviewToggleClosed svg {
float: right;
transform: rotate(-90deg);
}
.overviewToggleOpened svg {
float: left;
transform: rotate(90deg);
}
.overviewToggleOpened, .overviewToggleClosed {
color:#777;padding:5px;
}
.overviewToggleOpened:hover, .overviewToggleClosed:hover {
color:#333;
}

/* END INSERT overviewToggle -> elementVar -> css(text/css) */
','','text/css');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','jq_ui_dialog',5,1,'Module to manage jQuery UI dialog windows with multiple options for customization.','','div','','',-1,-1,15,15,NULL ,'','','div','','../extensions/jquery-ui-1.8.16.custom/development-bundle/ui/jquery.ui.dialog.js','','jq_ui,jq_ui_widget,jq_ui_button,jq_ui_draggable,jq_ui_mouse,jq_ui_position,jq_ui_resizable','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','jq_ui_slider',5,1,'slider from the jQuery UI framework','','','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','','../extensions/jquery-ui-1.8.16.custom/development-bundle/ui/minified/jquery.ui.slider.min.js','','jq_ui,jq_ui_mouse,jq_ui_widget','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','toolbar3',6,1,'This toolbar ZOOM TOOLS TOP RIGHT appends all its target elements to its container','Werkzeuge','div','','class=''mb-toolbar3'' ',NULL ,NULL,NULL ,NULL,NULL ,'','','div','../plugins/mb_toolbar.js','','zoomIn1,zoomFull,zoomOut1,selArea1','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','toolbar3','css','.mb-toolbar3 {
position: fixed;
z-index: 101;
right: 0px;
top: 150px;
box-shadow: 0 5px 10px -2px rgb(201, 202, 202);
}
.mb-toolbar3 ul, .mb-toolbar3 li {
display:block;
margin:0;
padding:0;
}

#toolbar3 ul li {
font-size: 0;
}

#toolbar3 ul li:first-child img{
/*border-top: 2px solid #DDD;*/
}
#toolbar3 ul li:last-child img{
border-bottom: 2px solid transparent;
}
.mb-toolbar3 img {
padding: 15px;
border-bottom: 2px solid #DDD;
border-left: 1px solid #DDD;
background-color:rgba(255,255,255,0.98)
}

#selArea1 {
}','','text/css');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','loadWMC_Button',6,1,'','Kartenzusammenstellungen verwalten','A','','',NULL ,NULL,NULL ,NULL,NULL ,'','<svg width="16" height="16" viewBox="0 0 20 18" fill="none" xmlns="http://www.w3.org/2000/svg">
<path fill-rule="evenodd" clip-rule="evenodd" d="M4.63 1.00001H15.19C16.0618 0.978918 16.8468 1.52517 17.13 2.35001L20 10.79V15C20 16.1046 19.1046 17 18 17H2C0.89543 17 0 16.1046 0 15V10.79L2.78 2.35001C3.03141 1.54123 3.78307 0.992715 4.63 1.00001ZM12.91 13L14.91 11H17.86L15.42 3.68001C15.2824 3.2727 14.8999 2.9989 14.47 3.00001H5.35C4.92008 2.9989 4.53757 3.2727 4.4 3.68001L1.96 11H4.91L6.91 13H12.91Z" fill="currentColor"/>
</svg>Meine Themen','A','../plugins/mb_button.js','','loadwmc','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','loadWMC_Button','dialogHeight','650','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','loadWMC_Button','dialogWidth','700','','var');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','legendButton',7,1,'popup
<IMG..>../img/button_hessen/legend_off.png','Legende anzeigen','A','','',NULL ,NULL,NULL ,NULL,NULL ,'','<svg width="16" height="16" viewBox="0 0 17 18" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M4.25 4C4.25 4.69036 3.69036 5.25 3 5.25C2.30964 5.25 1.75 4.69036 1.75 4C1.75 3.30964 2.30964 2.75 3 2.75C3.69036 2.75 4.25 3.30964 4.25 4Z" fill="currentColor"/>
<path d="M15 5H6V3H15V5Z" fill="currentColor"/>
<path d="M15 15H6V13H15V15Z" fill="currentColor"/>
<path d="M6 10H15V8H6V10Z" fill="currentColor"/>
<path d="M4.25 14C4.25 14.6904 3.69036 15.25 3 15.25C2.30964 15.25 1.75 14.6904 1.75 14C1.75 13.3096 2.30964 12.75 3 12.75C3.69036 12.75 4.25 13.3096 4.25 14Z" fill="currentColor"/>
<path d="M3 10.25C3.69036 10.25 4.25 9.69036 4.25 9C4.25 8.30964 3.69036 7.75 3 7.75C2.30964 7.75 1.75 8.30964 1.75 9C1.75 9.69036 2.30964 10.25 3 10.25Z" fill="currentColor"/>
</svg>Legende anzeigen','A','../plugins/mb_button.js','','legend','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','legendButton','dialogHeight','400','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','legendButton','dialogWidth','550','','var');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','printPdfButton',7,1,'popup
../img/treeKarl/print3_off.svg','Drucken als PDF','A','','',NULL ,NULL,NULL ,NULL,NULL ,'','<svg width="16" height="16" viewBox="0 0 50.000000 50.000000" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet">
 <!-- Created with Method Draw - http://github.com/duopixel/Method-Draw/ -->

 <g>
  <title>background</title>
  <rect x="-1" y="-1" width="50" height="50" id="canvas_background" fill="none"/>
 </g>
 <g>
  <title>Layer 1</title>
  <path id="svg_2" d="m42.185307,15.63532l-4.209507,0c-0.411011,-2.916739 -2.476017,-6.005462 -4.852818,-8.635098c-2.53558,-2.699079 -5.269728,-4.862872 -7.513462,-5.158852c-0.123081,-0.017507 -0.246202,-0.030444 -0.393171,-0.032579l-14.750977,0c-0.236278,0 -0.466591,0.102198 -0.633378,0.287212c-0.164833,0.180561 -0.26212,0.435418 -0.26212,0.696679l0,12.842639l-4.118129,0c-2.192091,0 -3.971192,1.950246 -3.971192,4.353179l0,19.318527l8.089322,0l0,4.525452c0,0.257057 0.091361,0.500706 0.26212,0.692471c0.170756,0.184834 0.395102,0.284744 0.633378,0.284744l26.696383,0c0.234272,0 0.460629,-0.099911 0.631397,-0.284744s0.27404,-0.433392 0.27404,-0.692471l0,-4.525452l8.08931,0l0,-19.318527c0,-2.402933 -1.777119,-4.353179 -3.971195,-4.353179zm-5.917133,27.21112l-24.911258,0l0,-6.804226l24.911258,0l0,6.804226zm0,-23.301897c0,-0.065084 0,-0.136751 -0.005936,-0.208815c-0.029789,-0.148008 -0.123096,-0.29393 -0.246208,-0.435608l-24.659114,0l0,-15.127926l13.873357,0l0,0.002149c0.605639,-0.041425 1.125864,0.613789 1.526926,2.113589c0.363388,1.425781 0.454721,3.284717 0.454721,4.575658c0.001965,0.942279 -0.03775,1.577968 -0.03775,1.577968l-0.073469,1.040393l0.953104,0.010986c0.003977,0 2.202023,0.030303 4.360353,0.565865c2.072975,0.491991 3.693195,1.469241 3.84808,2.577223c0.005936,0.098095 0.005936,0.198055 0.005936,0.289621l0,3.018898z" fill-opacity="null" stroke-opacity="null" stroke-width="0" stroke="#fff" fill="currentColor"/>
 </g>
</svg>Drucken','A','../plugins/mb_button.js','','printPDF','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','printPdfButton','dialogHeight','280','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','printPdfButton','dialogWidth','328','','var');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','coordsLookUp_Button',7,1,'popup
onmousedown=''this.src="../img/button_hessen/coordsearch_on.png"''
../img/button_hessen/coordsearch_off.png','Koordinatensuche','A','','',NULL ,NULL,NULL ,NULL,NULL ,'','<svg version="1.0" xmlns="http://www.w3.org/2000/svg"
 width="12.000000pt" height="12.000000pt" viewBox="0 0 1280.000000 1280.000000"
 >
<g transform="translate(0.000000,1280.000000) scale(0.100000,-0.100000)"
fill="currentColor" stroke="none">
<path d="M6015 12640 c-11 -5 -38 -9 -60 -9 -22 -1 -56 -6 -75 -11 -19 -6 -60
-15 -90 -20 -97 -16 -144 -26 -180 -37 -19 -7 -75 -23 -125 -38 -49 -15 -98
-31 -107 -36 -10 -5 -23 -9 -29 -9 -6 0 -25 -6 -42 -14 -18 -7 -52 -21 -77
-31 -45 -18 -347 -165 -385 -188 -254 -150 -421 -281 -640 -501 -287 -288
-485 -577 -634 -926 -12 -30 -29 -68 -36 -85 -19 -44 -80 -230 -99 -305 -10
-36 -22 -79 -27 -96 -5 -17 -9 -42 -9 -57 0 -14 -5 -38 -11 -54 -11 -28 -19
-87 -40 -288 -13 -129 -6 -562 11 -675 6 -41 18 -120 26 -175 8 -55 19 -114
25 -131 5 -17 9 -42 9 -55 0 -14 6 -48 14 -75 7 -27 19 -76 26 -109 7 -33 18
-80 26 -105 7 -25 20 -74 29 -110 9 -36 21 -78 26 -95 11 -37 53 -181 75 -255
9 -30 20 -63 25 -72 5 -10 9 -24 9 -33 0 -8 6 -29 14 -47 8 -18 22 -60 31 -93
10 -33 21 -64 26 -70 5 -5 9 -16 9 -24 0 -9 11 -46 25 -83 24 -67 49 -135 101
-280 14 -40 32 -91 41 -113 8 -22 19 -49 23 -60 4 -11 13 -33 20 -50 6 -16 19
-50 27 -75 8 -25 21 -58 28 -75 7 -16 19 -48 28 -70 15 -41 63 -164 77 -200 4
-11 16 -40 25 -65 10 -25 24 -61 32 -80 27 -65 31 -75 48 -120 9 -25 21 -53
26 -62 5 -10 9 -22 9 -28 0 -5 11 -33 24 -62 13 -29 43 -100 66 -158 39 -98
59 -145 80 -195 4 -11 23 -56 40 -100 30 -74 50 -120 85 -200 34 -78 45 -104
65 -150 12 -27 28 -66 37 -85 9 -19 24 -54 34 -77 11 -24 27 -62 38 -85 10
-24 25 -59 34 -78 8 -19 31 -70 51 -113 20 -43 36 -80 36 -82 0 -4 40 -91 120
-260 23 -49 54 -115 67 -145 35 -77 171 -360 187 -390 8 -14 47 -90 88 -170
41 -80 85 -163 98 -185 44 -78 111 -196 135 -239 72 -129 221 -349 322 -476
175 -220 380 -370 508 -370 112 0 304 131 461 316 132 154 348 482 480 729 23
44 61 114 83 155 75 139 411 821 411 835 0 2 21 48 46 102 25 54 54 116 64
138 10 22 32 69 48 105 71 155 96 210 142 315 10 22 30 67 45 100 15 33 36 80
47 105 11 25 23 52 27 60 6 11 273 652 368 880 7 19 18 43 23 52 6 10 10 22
10 28 0 5 11 33 24 62 13 29 38 89 56 133 17 44 35 89 39 100 5 11 21 52 36
90 15 39 33 84 40 100 6 17 18 46 25 65 6 19 16 44 21 55 19 44 98 254 119
315 4 14 27 77 50 140 23 63 66 189 96 280 29 91 59 179 65 195 6 17 14 44 19
60 5 17 16 55 25 85 9 30 20 71 25 90 4 19 18 71 30 115 35 132 49 187 59 240
6 28 16 73 22 100 13 60 26 139 39 255 5 47 14 112 20 145 16 94 12 508 -5
638 -32 235 -55 348 -100 492 -6 22 -17 58 -24 80 -59 194 -228 544 -339 700
-157 222 -298 392 -418 506 -376 355 -828 617 -1294 749 -145 41 -132 38 -325
74 -44 8 -107 19 -141 25 -72 13 -729 14 -759 1z m680 -1508 c123 -20 257 -56
365 -99 19 -8 44 -18 55 -22 363 -146 722 -485 901 -851 47 -97 79 -174 100
-240 57 -182 94 -406 94 -565 0 -103 -42 -394 -68 -470 -5 -16 -18 -57 -27
-90 -10 -33 -21 -64 -26 -69 -5 -6 -9 -17 -9 -25 0 -27 -117 -252 -177 -342
-172 -257 -418 -474 -693 -610 -94 -47 -134 -63 -230 -94 -25 -9 -61 -20 -80
-27 -42 -14 -87 -23 -245 -48 -159 -25 -329 -25 -486 0 -168 27 -300 57 -315
73 -4 4 -15 7 -23 7 -23 0 -150 54 -258 110 -268 140 -511 366 -685 640 -40
63 -148 281 -148 299 0 6 -4 19 -9 29 -33 66 -82 273 -100 423 -42 334 37 748
198 1039 13 25 30 54 36 65 65 119 213 305 323 406 277 255 585 401 982 468
67 11 442 6 525 -7z"/>
<path d="M2836 6135 c-14 -16 -26 -32 -26 -38 0 -5 -16 -44 -36 -86 -20 -42
-49 -104 -65 -138 -16 -35 -42 -91 -58 -125 -16 -35 -45 -97 -64 -138 -73
-157 -110 -237 -137 -295 -15 -33 -49 -105 -75 -160 -26 -55 -60 -128 -76
-162 -56 -121 -87 -189 -124 -268 -20 -44 -49 -107 -65 -140 -16 -33 -44 -94
-63 -135 -37 -80 -87 -189 -144 -310 -51 -109 -144 -309 -195 -420 -16 -36
-52 -111 -79 -168 -27 -56 -49 -104 -49 -106 0 -3 -25 -58 -56 -123 -31 -65
-70 -147 -86 -183 -17 -36 -48 -103 -70 -150 -22 -47 -53 -114 -70 -150 -16
-36 -43 -92 -58 -125 -16 -33 -42 -89 -58 -125 -16 -36 -51 -110 -77 -165 -26
-55 -61 -129 -77 -165 -16 -36 -42 -92 -58 -125 -15 -33 -45 -96 -65 -140 -47
-102 -82 -178 -133 -285 -21 -47 -51 -110 -65 -140 -14 -30 -42 -91 -62 -135
-21 -44 -53 -114 -72 -155 -19 -41 -53 -113 -75 -160 -22 -47 -53 -114 -70
-150 -16 -36 -43 -92 -58 -125 -16 -33 -41 -87 -56 -120 -14 -33 -53 -116 -85
-184 -33 -67 -59 -125 -59 -127 0 -2 -27 -59 -60 -126 -33 -68 -60 -131 -60
-140 0 -17 306 -18 6365 -18 l6365 0 0 24 c0 13 -3 26 -7 28 -9 3 -26 37 -68
133 -15 33 -37 83 -50 110 -13 28 -32 70 -43 95 -11 25 -32 72 -47 105 -15 33
-36 80 -47 105 -11 25 -30 68 -43 95 -13 28 -35 77 -50 110 -15 33 -35 79 -46
102 -10 24 -28 64 -40 90 -11 27 -31 71 -44 98 -12 28 -29 66 -38 85 -15 34
-63 139 -113 247 -13 28 -24 54 -24 58 0 3 -17 42 -39 88 -21 45 -45 98 -54
117 -8 19 -30 67 -47 105 -18 39 -40 88 -50 110 -36 82 -49 112 -78 175 -16
36 -39 88 -52 115 -12 28 -33 75 -47 105 -14 30 -31 69 -38 85 -7 17 -27 59
-43 95 -16 36 -40 88 -52 115 -12 28 -38 83 -56 123 -19 41 -34 75 -34 77 0 2
-15 36 -34 77 -18 40 -44 96 -56 123 -12 28 -38 83 -56 123 -19 41 -34 75 -34
77 0 2 -15 36 -34 77 -18 40 -44 96 -56 123 -12 28 -38 83 -56 123 -19 41 -34
75 -34 77 0 2 -15 36 -34 77 -33 72 -61 133 -106 233 -12 28 -38 83 -56 123
-19 41 -34 75 -34 77 0 2 -15 36 -34 77 -18 40 -44 96 -56 123 -12 28 -33 73
-45 100 -13 28 -44 97 -70 155 -26 58 -57 128 -70 155 -42 93 -63 140 -110
245 -26 58 -57 128 -70 155 -12 28 -33 73 -45 100 -12 28 -38 83 -56 123 -19
41 -34 75 -34 77 0 2 -15 36 -34 77 -18 40 -44 96 -56 123 -12 28 -33 73 -45
100 -13 28 -44 97 -70 155 -26 58 -52 110 -58 117 -9 8 -156 12 -542 13 -291
2 -535 2 -542 1 -12 -1 -34 -45 -103 -206 -27 -63 -41 -96 -74 -167 -14 -31
-26 -59 -26 -62 0 -6 -45 -109 -66 -149 -8 -16 -14 -40 -14 -53 l0 -24 433 0
432 0 17 -38 c10 -20 36 -77 59 -127 23 -49 60 -133 84 -185 24 -52 59 -131
80 -175 71 -153 83 -180 113 -245 58 -128 80 -176 102 -225 12 -27 37 -83 56
-123 19 -39 49 -105 68 -145 19 -39 44 -94 56 -122 13 -27 35 -77 50 -110 15
-33 40 -87 54 -120 15 -33 40 -87 56 -120 41 -87 94 -203 120 -260 13 -27 35
-77 50 -110 15 -33 38 -82 50 -110 36 -78 89 -194 120 -260 16 -33 49 -105 73
-160 25 -55 62 -136 82 -180 21 -44 45 -98 55 -120 10 -22 41 -89 69 -149 28
-59 51 -110 51 -112 0 -4 35 -81 110 -239 26 -55 44 -94 110 -240 12 -27 35
-77 50 -110 15 -33 40 -87 54 -120 15 -33 40 -87 56 -120 41 -87 94 -203 120
-260 13 -27 35 -77 50 -110 15 -33 37 -82 50 -110 12 -27 37 -80 55 -117 l33
-68 -5325 0 c-2928 0 -5323 3 -5321 8 2 4 14 30 27 57 13 28 37 79 53 115 16
36 42 92 58 125 75 160 110 235 110 239 0 2 23 53 51 113 28 59 60 127 70 150
35 79 57 128 108 237 28 59 51 110 51 112 0 2 20 46 44 97 68 146 150 323 226
492 25 57 79 172 120 260 16 33 42 89 58 125 16 36 41 90 54 120 14 30 35 78
48 105 12 28 37 83 56 123 19 39 49 105 68 145 19 39 44 95 56 122 13 28 35
77 50 110 15 33 37 83 50 110 32 72 61 135 119 258 28 60 51 110 51 112 0 2
17 41 39 87 39 86 89 193 121 263 10 22 34 74 53 115 19 41 44 98 57 125 12
28 38 84 57 125 32 70 48 104 108 235 13 28 39 84 58 125 19 41 45 98 57 125
13 28 33 74 47 103 l24 52 379 0 380 0 0 25 c0 14 -4 33 -10 43 -5 9 -16 35
-26 57 -24 59 -31 76 -50 120 -9 22 -23 56 -31 75 -8 19 -19 46 -25 60 -16 37
-34 81 -48 115 -15 37 -32 76 -59 138 l-21 47 -404 0 c-222 0 -417 3 -434 6
-25 5 -36 1 -56 -21z"/>
</g>
</svg>Suchen mit Koordinaten','A','../plugins/mb_button.js','','coordsLookup','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','coordsLookUp_Button','dialogHeight','320','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','coordsLookUp_Button','dialogWidth','300','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','coordsLookUp_Button','useMapcode','true','','var');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','coordsLookup',10,1,'','Koordinatensuche','div','','',NULL ,NULL,NULL ,NULL,NULL ,'','','div','mod_coordsLookup.php','','mapframe1','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','coordsLookup','perimeters','[50,200,1000,10000]','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','coordsLookup','projections','EPSG:4326;Geographic Coordinates,EPSG:31466;Gauss-Krueger 2,EPSG:31467;Gauss-Krueger 3,EPSG:31468;Gauss-Krueger 4,EPSG:31469;Gauss-Krueger 5,EPSG:25832;UTM zone 32N','','php_var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','coordsLookup','useMapcode','true','','var');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','WMS_preferencesButton',11,1,'button for configure the preferences of each loaded wms','Kartenebenen Einstellungen','A','','',NULL ,NULL,34,37,1,'','<svg width="16" height="16" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
<path fill-rule="evenodd" clip-rule="evenodd" d="M14.5344 6.3088L14.8064 6.968C17 7.7664 17 7.8592 17 8.1232V9.8C17 10.0624 17 10.1536 14.816 11.024L14.5456 11.6816C15.5264 13.792 15.4624 13.8576 15.2784 14.0464L13.9856 15.3376H13.8256C13.632 15.3376 12.9168 15.0656 11.6928 14.5376L11.0256 14.8128C10.224 17 10.1328 17 9.8752 17H8.2C7.9392 17 7.8512 17 6.9888 14.8224L6.3232 14.5456C4.5088 15.3856 4.2944 15.3856 4.2224 15.3856H4.0832L3.9648 15.2816L2.7728 14.0912C2.592 13.9072 2.5296 13.8432 3.4624 11.6912L3.1904 11.0352C1 10.2336 1 10.1408 1 9.8768V8.2C1 7.9296 1 7.8464 3.1824 6.9856L3.4544 6.328C2.46856 4.21253 2.53581 4.14419 2.73199 3.94483L2.7328 3.944L4.0128 2.664H4.1728C4.3664 2.664 5.0848 2.936 6.3088 3.464L6.9728 3.1888C7.7712 1 7.8688 1 8.1216 1H9.8C10.056 1 10.144 1 11.0064 3.1808L11.6736 3.456C13.4864 2.6176 13.7024 2.6176 13.776 2.6176H13.9136L14.032 2.72L15.224 3.9088C15.4064 4.0928 15.4672 4.1568 14.5344 6.3088ZM6.13793 10.1908C6.61987 11.3492 7.75251 12.1029 9.00713 12.1C10.7149 12.0941 12.0971 10.7096 12.1 9.00181C12.1007 7.74719 11.3451 6.61584 10.1859 6.13589C9.02674 5.65594 7.69255 5.92202 6.80616 6.80993C5.91978 7.69784 5.65598 9.03249 6.13793 10.1908Z" fill="currentColor"/>
</svg>','A','../plugins/mb_button.js','','WMS_preferencesDiv','','http://www.mapbender.org/index.php/mb_button');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','WMS_preferencesButton','dialogHeight','400','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','WMS_preferencesButton','dialogWidth','575','','var');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-RLP_2019','WMS_preferencesDiv',12,1,'','WMS Einstellungen','div','','',9,312,169,19,NULL ,'z-index:9999;','','div','../plugins/mod_WMSpreferencesDiv.php','','mapframe1','jq_ui_dialog','http://www.mapbender.org/index.php/WMS_preferencesDiv');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-RLP_2019','WMS_preferencesDiv','css','/* INSERT body -> WMS_preferenceDiv -> css(text/css) */

#WMS_preferencesDiv table, #WMS_preferencesDiv select {
width: 100%;
}

#WMS_preferencesDiv input:not([type]), #WMS_preferencesDiv input[type="text"]{
background-color:transparent;
width:40px;
}

#WMS_preferencesDiv td:first-child{
width:35%;
min-width:150px;
}
/* END INSERT body -> WMS_preferenceDiv -> css(text/css) */','','text/css');