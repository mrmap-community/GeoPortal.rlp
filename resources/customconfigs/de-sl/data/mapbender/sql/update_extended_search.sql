INSERT INTO gui (gui_id, gui_name, gui_description, gui_public) VALUES ('Geoportal-SL_erwSuche2','Geoportal-SL_erwSuche2','client element collection',1);
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','test',0,0,'body (obligatory)','','body','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','../geoportal/mod_revertGuiSessionSettings.php','','','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-SL_erwSuche2','test','css_class_bg','body{ background-color: #f3f3f3; }','to define the color of the body','text/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-SL_erwSuche2','test','css_file_body','../css/mapbender.css','file/css','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-SL_erwSuche2','test','favicon','../img/favicon.png','favicon','php_var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-SL_erwSuche2','test','includeWhileLoading','','show splash screen while the application is loading','php_var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-SL_erwSuche2','test','jq_ui_theme','../extensions/jquery-ui-1.7.2.custom/css/ui-customized_4_digitize/jquery-ui-1.7.3.custom.css','UI Theme from Themeroller','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-SL_erwSuche2','test','popupcss','../css/popup.css','file css','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-SL_erwSuche2','test','tablesortercss','../css/tablesorter.css','file css','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-SL_erwSuche2','test','use_load_message','false','show splash screen while the application is loading','php_var');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','zoomOut1',1,1,'zoomOut button','Aus der Karte herauszoomen','img','../img/button_blue_red/zoomOut2_off.png','',300,196,24,24,20,'','','','mod_zoomOut1.js','','mapframe1','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','mb_helpDialog',1,1,'Mapbender wrapper for helptexts in jq dialogs','','div','','',NULL ,NULL,NULL ,NULL,NULL ,'','','div','','mb_helpDialog.js','','jq_ui, jq_metadata','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','jq_metadata',1,1,'jQuery Metadata plugin - selects elements by tag names out of html?','','div','','',NULL ,NULL,NULL ,NULL,NULL ,'','','div','','../extensions/jquery.metadata.2.1/jquery.metadata.min.js','','','http://plugins.jquery.com/project/metadata');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','jq_ui',1,1,'jQuery UI core','','div','','',NULL ,NULL,NULL ,NULL,NULL ,'','','div','','../extensions/jquery-ui-1.8.1.custom/development-bundle/ui/jquery.ui.core.js','','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-SL_erwSuche2','jq_ui','css','../extensions/jquery-ui-1.8.1.custom/css/custom-theme/jquery-ui-1.8.4.custom.css','','file/css');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','jq_ui_widget',1,1,'jQuery UI widget','','','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','','../extensions/jquery-ui-1.8.1.custom/development-bundle/ui/jquery.ui.widget.js','','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','i18n',1,1,'Internationalization module, collects data from all elements and sends them to the server in a single POST request. The strings are translated via gettext only.','Internationalization','div','','',NULL ,NULL,NULL ,NULL,NULL ,'','','div','../plugins/mb_i18n.js','','','','http://www.mapbender.org/Gettext');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','jq_upload',1,1,'','','','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','','../plugins/jq_upload.js','','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','zoomIn1',1,1,'zoomIn button','In die Karte hineinzoomen','img','../img/button_blue_red/zoomIn2_off.png','',300,171,24,24,20,'','','','mod_zoomIn1.js','','mapframe1','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','jq_datatables',1,1,'Includes the jQuery plugin datatables, use like this
$(selector).datatables(options)','','','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','../plugins/jq_datatables.js','../extensions/dataTables-1.5/media/js/jquery.dataTables.min.js','','','http://www.datatables.net/');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-SL_erwSuche2','jq_datatables','defaultCss','../extensions/dataTables-1.5/media/css/demo_table_jui.css','','file/css');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','mapframe1',1,1,'Frame for a map','','div','','',10,180,280,154,20,'overflow:hidden;background-color:#ffffff','','div','../plugins/mb_map.js','../../lib/history.js,map_obj.js,map.js,wms.js,wfs_obj.js,initWmcObj.php','','','http://www.mapbender.org/index.php/Mapframe');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-SL_erwSuche2','mapframe1','skipWmsIfSrsNotSupported','0','if set to 1, it skips the WMS request if the current SRS is not supported by the WMS; if set to 0, the WMS is always queried. Default is 0, because of backwards compatibility','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-SL_erwSuche2','mapframe1','slippy','1','1 = Activates an animated, pseudo slippy map','var');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','body',1,1,'body (obligatory)','','div','','',NULL ,NULL,NULL ,650,NULL ,'position:relative !important;','','div','../geoportal/mod_revertGuiSessionSettings.php','','','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-SL_erwSuche2','body','css_class_bg','body{ background-color: #f3f3f3; }','to define the color of the body','text/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-SL_erwSuche2','body','css_file_body','../css/mapbender.css','file/css','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-SL_erwSuche2','body','extended_search_css','../css/extended_search.css','file/css','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-SL_erwSuche2','body','favicon','../img/favicon.png','favicon','php_var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-SL_erwSuche2','body','includeWhileLoading','','show splash screen while the application is loading','php_var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-SL_erwSuche2','body','jq_ui_theme','../extensions/jquery-ui-1.7.2.custom/css/ui-customized_4_digitize/jquery-ui-1.8.13.custom.css','UI Theme from Themeroller','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-SL_erwSuche2','body','popupcss','../css/popup.css','file css','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-SL_erwSuche2','body','tablesortercss','../css/tablesorter.css','file css','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-SL_erwSuche2','body','use_load_message','false','show splash screen while the application is loading','php_var');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','forward',1,1,'History.forward()','Nach vorne','img','../img/button_blue_red/forward_off_disabled.png','',300,296,24,24,20,'','','','mod_forward.php','','mapframe1,overview0','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','pan1',1,1,'pan','Ausschnitt verschieben','img','../img/button_blue_red/pan_off.png','',300,271,24,24,20,'','','','mod_pan.js','','mapframe1','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','selArea1',1,1,'zoombox','Ausschnitt mit Box aufziehen','img','../img/button_blue_red/selArea_off.png','',300,221,24,24,20,'','','','mod_selArea.js','mod_box1.js','mapframe1','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','zoomFull',1,1,'zoom to full extent button','Auf gesamte Karte zoomen','img','../img/button_blue_red/zoomFull_off.png','',300,246,24,24,20,'','','img','mod_zoomFull.js','','mapframe1','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','mb_div_collection1',2,1,'Put existing divs in new div object. List the elements comma-separated under target, and make sure they have a title.','','div','','',NULL ,NULL,NULL ,NULL,NULL ,'','','div','../plugins/mb_div_collection.js','','mapframe1, zoomIn1,zoomFull,zoomOut1,selArea1,pan1,forward,back,navFrame','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','jq_ui_mouse',2,1,'jQuery UI mouse','','','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','','../extensions/jquery-ui-1.8.1.custom/development-bundle/ui/jquery.ui.mouse.js','','jq_ui_widget','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','back',2,1,'History.back()','Zurück','img','../img/button_blue_red/back_off_disabled.png','',300,321,24,24,20,'','','','mod_back.php','','mapframe1,overview0','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','jq_ui_position',2,1,'jQuery UI position','','','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','','../extensions/jquery-ui-1.8.1.custom/development-bundle/ui/jquery.ui.position.js','','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','navFrame',2,1,'navigation mapborder','','div','','class=''navFrameStyle''',0,0,NULL ,NULL,20,'font-size:1px','','div','mod_navFrame.php','','mapframe1','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','spatialFilter',2,1,'Div which shows the spatial filter options for the metadata search','','div','','class=''spatialFilter''',NULL ,NULL,NULL ,NULL,20,'','	<fieldset
class="searchFieldset">
		<img class="help-dialog" id="helpSpatialFilter" title="Help" help="{text:''Help SpatialFilter''}"  src="../img/questionmark.png" align="right"></img>
		<p>
			<input type=''checkbox'' 
class=''searchcheckbox''
name=''searchBbox'' id=''searchBbox''>
			<label  id="labelSpatialFilter" 
class=''searchlabelSpatialFilter''
name="labelSpatialFilter">Activate Spatial Filter</label>
			<legend id="labelSpatialFilterType"
class=''searchlegend'' name="labelSpatialFilterType">how?</legend>
			<input type=''radio'' name=''searchTypeBbox''
class=''searchTypeBbox-a''  value=''intersects'' id=''searchTypeBbox''>
			<label id="labelIntersects" name="labelIntersects" 
class="labelIntersects-a"
for="bbox">intersects</label>
			<input type=''radio'' name=''searchTypeBbox'' 
class=''searchTypeBbox-b''
value=''outside'' id=''searchTypeBbox''>
			<label id="labelOutside" name="labelOutside"
class="labelOutside-b">outside</label>
			<input type=''radio'' name=''searchTypeBbox'' 
class=''searchTypeBbox-c''
value=''inside'' id=''searchTypeBbox''>
			<label id="labelInside" name="labelInside"
class="labelInside-c">fully inside</label>
		</p>
	</fieldset>','div','','','','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','wholeBackground',2,1,'background for spatial filter mapbender mapframe - cause this cannot be positioned relativly','','div','','',0,0,750,600,NULL ,'color: #008000;position:absolute','','div','','','','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','spatialBackground',2,0,'background for spatial filter mapbender mapframe - cause this cannot be positioned relativly','','div','','',0,160,704,200,1,'color: #008000;background-color:#e3e3e3;position:absolute','','div','','','','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','sandclock',2,1,'displays a sand clock while waiting for requests','','div','','',80,0,0,0,0,'','','div','mod_sandclock.js','','mapframe1','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','mapbender',2,1,'Mapbender-Logo','','div','','onclick="javascript:window.open(''http://www.mapbender.org'','''','''');"',81,-19,1,1,30,'font-size : 10px;font-weight : bold;font-family: Arial, Helvetica, sans-serif;color:white;cursor:help;','<span>Ma</span><span style="color: blue;">P</span><span style="color: red;">b</span><span>ender</span><script type="text/javascript"> mb_registerSubFunctions("mod_mapbender()"); function mod_mapbender(){ document.getElementById("mapbender").style.left = parseInt(document.getElementById("mapframe1").style.left) + parseInt(document.getElementById("mapframe1").style.width) - 90; document.getElementById("mapbender").style.top = parseInt(document.getElementById("mapframe1").style.top) + parseInt(document.getElementById("mapframe1").style.height) -1; } </script>','div','','','','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','loadData',2,0,'IFRAME, um Daten zu laden','','iframe','../html/mod_blank.html','frameborder = "0" ',0,0,1,1,NULL ,'visibility:visible','','iframe','','','','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','renderGML',2,0,'renders a gml contained in $_SESSION[''GML'']','','div','','',NULL ,NULL,NULL ,NULL,1,'','','div','../javascripts/mod_renderGML.php','mod_highlight.php','overview,mapframe1','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','imprint',2,0,'Information about the owner of the gui','','iframe','../x_geoportal/imprint_geoportal.html','frameborder = "0" ',1,1,1,1,5,'visibility:hidden;','','iframe','','','','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','dependentDiv',2,0,'displays infos in a sticky div-tag','','div','','',81,-19,1,1,NULL ,'visibility:visible;position:absolute;font-size: 11px;font-family: "Arial", sans-serif;','','div','mod_dependentDiv.php','','mapframe1','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','mapcheck',2,1,'alert if a map-image fails to load','','div','','',0,0,NULL ,NULL,NULL ,'','','div','','mod_mapcheck.php','mapframe1','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','searchField',3,1,'Div which shows the search field for the metadata search','','div','','',0,10,480,NULL ,20,'','		<fieldset class="searchFieldset">
		<legend id="legendSearchTextTitle" class=''searchlegend'' name="legendSearchTextTitle">Searchterm(s):</legend>
		<img class="help-dialog" id="helpSearchText" title="Help" help="{text:''Help SearchText''}"  src="../img/questionmark.png" align="right"></img>
			<p>
				<input type=''text'' class=''searchTexta'' size=30  name=''searchText'' id=''searchText''> 
			</p>
		</fieldset>','div','','','','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','form1',3,1,'Form for the extended mapbender metadata search','','form','','method=''post'' name=''form1''',0,0,400,NULL ,NULL,'','','form','../plugins/mb_div_collection.js','../plugins/mb_extendedSearch.js','searchField,button,orderBy,mb_tabs_horizontal','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','form2',3,1,'Form for the extended mapbender metadata search - this form submits the values as hidden to the search script','','form','','method=''get'' name=''form2'' action=''/index.php/de/suchergebnis'' target=''_top''',0,250,NULL ,NULL,NULL ,'','	<input type="hidden" value='''' name=''searchText''>

	<input type="hidden" value='''' name=''registratingDepartments''>

	<input type="hidden" value='''' name=''isoCategories''>

	<input type="hidden" value='''' name=''inspireThemes''>

	<input type="hidden" value='''' name=''customCategories''>

	<input type="hidden" value='''' name=''regTimeBegin''>

	<input type="hidden" value='''' name=''regTimeEnd''>

	<input type="hidden" value='''' name=''timeBegin''>

	<input type="hidden" value='''' name=''timeEnd''>

	<input type="hidden" value='''' name=''searchBbox''>

	<input type="hidden" value='''' name=''searchTypeBbox''>

	<input type="hidden" value='''' name=''searchResources''>

	<input type="hidden" value='''' name=''orderBy''>	','form','','','','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','mb_div_collection2',3,1,'Put existing divs in new div object. List the elements comma-separated under target, and make sure they have a title.','Wo?','div','','',NULL ,NULL,NULL ,NULL,NULL ,'','<fieldset>','div','../plugins/mb_div_collection.js','','spatialFilter,mb_div_collection1,spatialBackground','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','orderBy',3,1,'Div which shows orderby options for the metadata search','Order','div','','class=''orderBy''',0,80,480,NULL ,20,'','<fieldset class="searchFieldset">
<legend id="legendOrderBy" 
class=''searchlegend''
name="legendOrderBy">Sort by:</legend>

		<img class="help-dialog" id="helpOrderBy" title="Help" help="{text:''Help OrderBy''}"  src="../img/questionmark.png" align="right"></img>
		<p>
			<input type=''radio'' class=''orderByRanka'' name=''orderBy'' value=''rank'' id=''orderBy'' checked >
			<label id="labelOrderByRank" class=''orderByRankb'' name="labelOrderByRank">relevance</label>
			<input type=''radio'' class=''orderByTitlea''
name=''orderBy'' value=''title'' id=''orderBy''>
			<label id="labelOrderByTitle" 
class=''orderByTitleb''
name="labelOrderByTitle">title</label>
			<input type=''radio'' 
class=''orderByIda''
name=''orderBy'' value=''id'' id=''orderBy''>
			<label id="labelOrderById" 
class=''orderByIdb''
name="labelOrderById">identification</label>
			<input type=''radio''
class=''orderByDatea'' name=''orderBy'' value=''date'' id=''orderBy''>
			<label id="labelOrderByDate"
class=''orderByDateb''  name="labelOrderByDate">date</label>		
		</p>
	</fieldset>
','div','','','','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','actuality',3,1,'Div which shows the date field for temporal filters for metadata search','Wann?','div','','class=''actuality''',NULL ,NULL,NULL ,NULL,20,'','<fieldset class="searchFieldset">
<legend  id="legendActuality"
name="legendActuality"
class="legendActuality"
>Actuality</legend>
	<fieldset
class="searchFieldset">
		<legend id="legendDateOfPublication" name="legendDateOfPublication">Date of Publication</legend>

		<img class="help-dialog" id="helpDateOfPublication" title="Help" help="{text:''Help DateOfPublication''}"  src="../img/questionmark.png" align="right"></img>

		<table>

			<p>

			<tr>

			<td><label id="labelDateOfPublicationStart" name="labelDateOfPublicationStart"  for="regTimeBegin">Date from</label></td>

			<td><input class=''hasdatepicker'' type=''text'' size=15 name=''regTimeBegin'' id=''regTimeBegin'' > </td>

			</tr>

			<tr>

			<td><label id="labelDateOfPublicationEnd" name="labelDateOfPublicationEnd"  for="regTimeEnd">Date to</label></td>

			<td><input class=''hasdatepicker'' type=''text'' size=15 name=''regTimeEnd'' id=''regTimeEnd'' > </td> 

			</tr>

			</p>

		</table>

	</fieldset>



	<fieldset class="searchFieldset">

		<legend id="legendDateOfLastRevision" name="legendDateOfLastRevision">Data actuality</legend>

		<img class="help-dialog" id="helpDateOfLastRevision" title="Help" help="{text:''Help DateOfLastRevision''}"  src="../img/questionmark.png" align="right"></img>

			<table>

			<p>

			<tr>

				<td><label id="labelDateOfLastRevisionStart" name="labelDateOfLastRevisionStart"  for="timeBegin" >Date from</label></td>

				<td><input disabled="disabled" type=''text'' size=15 name=''timeBegin'' id=''timeBegin'' > </td>



			</tr>

			<tr>

				<td><label id="labelDateOfLastRevisionEnd" name="labelDateOfLastRevisionEnd"   for="timeEnd">Date to</label></td>
<td><input disabled="disabled" type=''text'' size=15 name=''timeEnd'' id=''timeEnd'' > </td>

			</tr>

			</p>

			</table>

	</fieldset>

</fieldset>
','div','','','','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','classifications',3,1,'Put category divs into one div collection','Themen','div','','',NULL ,NULL,NULL ,NULL,20,'','<fieldset
class="searchFieldset">
	<legend  id="classificationsLegend" name="classificationsLegend">Classifications</legend>
		<div class="inspire">
			<fieldset class="searchFieldset">
			<legend id="legendInspireThemes" name="legendInspireThemes">Inspire Themes</legend>
			<img class="help-dialog" id="helpInspireThemes" title="Help" help="{text:''Help Inspire Categories''}"  src="../img/questionmark.png" align="right"></img>
<img id="imageInspireThemes" title="Inspire" src="../img/inspire_tr_36.png" align="right"></img>
				<p>
					<select class=''selectCat'' size=''5'' name=''inspireThemes'' id=''inspireThemes'' multiple ></select>
					<br><a  id="deleteSelection2" name="deleteSelection" href="#"  onclick=''removeListSelections("inspireThemes");''>Drop current selection</a>
				</p>
			</fieldset>
		</div>
		<div class="iso">
			<fieldset class="searchFieldset">
			<legend  id="legendIsoCategories" name="legendIsoCategories">ISO19115 Themes</legend>
			<img class="help-dialog" id="helpIsoCategories" title="Help" help="{text:''Help ISO Categories''}"  src="../img/questionmark.png" align="right"></img>
				<p> 
					<select class=''selectCat'' size=''5'' name=''isoCategories'' id=''isoCategories'' width="200px"  multiple></select>
					<br><a id="deleteSelection3" name="deleteSelection" href="#"  onclick=''removeListSelections("isoCategories");''>Drop current selection</a>
				</p>
			</fieldset>
		</div>
		<div class="custom">
			<fieldset class="searchFieldset">
				<legend  id="legendCustomCategories" name="legendCustomCategories">Other Themes</legend>
				<img class="help-dialog" id="helpCustomCategories" title="Help" help="{text:''Help Custom Categories''}"  src="../img/questionmark.png" align="right"></img>
				<p> 
					<select class=''selectCat'' size=''5'' name=''customCategories'' id=''customCategories'' multiple></select>
					<br><a id="deleteSelection4" name="deleteSelection" href="#" onclick=''removeListSelections("customCategories");''>Drop current selection</a>
				</p>
			</fieldset>
		</div>
	</fieldset>','div','','','','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','provider',3,1,'Div which shows a list of providers for the metadata search','Anbieter','div','','',NULL ,NULL,NULL ,NULL,20,'','<fieldset class="searchFieldset">
<legend  id="legendDepartment" name="legendDepartment">Provider</legend>
<img class="help-dialog" id="helpProvider" title="Help" help="{text:''Help Provider''}"  src="../img/questionmark.png" align="right"></img>
		<p>
			<select class=''selectCat'' size=''5'' name=''registratingDepartments'' id=''registratingDepartments'' multiple ></select>
			<br><a id="deleteSelection1" name="deleteSelection" href="#" c onclick=''removeListSelections("registratingDepartments");''>Drop current selection</a>
		</p>
	</fieldset>','div','','','','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','classSearchResources',3,1,'Div which shows the different bsearchable resources for metadata search','Was?','div','','',NULL ,NULL,NULL ,NULL,20,'','<fieldset class="searchFieldset"><legend id="legendSearchResources" name="legendSearchResources">Types of Resources</legend>
		<img class="help-dialog" id="helpSearchResources" title="Help" help="{text:''Help SearchResources''}"  src="../img/questionmark.png" align="right"></img>

		<div id=''searchResources'' name=''searchResources''>

			<input name=''checkResourcesWms'' class=''checkResourcesWms-a'' id=''checkResourcesWms'' type=''checkbox'' value=''wms''>

			<label id="labelCheckResourcesWms" class="labekCheckResourcesWms-a" name="labelCheckResourcesWms">Viewing Services</label>

			<input name=''checkResourcesWfs'' class=''checkResourcesWfs-b'' id=''checkResourcesWfs'' type=''checkbox'' value=''wfs''>

			<label id="labelCheckResourcesWfs" class="labelCheckResourcesWfs-b" name="labelCheckResourcesWfs">Search/Download/Digitize Modules</label>

			<input name=''checkResourcesWmc'' class=''checkResourcesWmc-c'' id=''checkResourcesWmc'' type=''checkbox'' value=''wmc''>

			<label id="labelCheckResourcesWmc" class=''labelCheckResourcesWmc-c'' name="labelCheckResourcesWmc">Map Collections</label>

			<input disabled="disabled" class=''disabled-d'' name=''checkResourcesGeorss'' id=''checkResourcesGeorss'' type=''checkbox'' value=''georss''>

			<label disabled="disabled" class="disabledbox-d" id="labelCheckResourcesGeorss" name="labelCheckResourcesGeorss">GeoRSS Feeds</label>

		</div>
</fieldset>','div','','','','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','button',3,1,'Div for the button to submit extended search module','','div','','class=''button''',500,16,60,40,20,'','<button id="search" name="search" style="background:#D9E7FF; height:32px"
type="button" value="Start Search" onclick="validate();">Start Search</button>
','div','','','','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','jq_ui_button',4,1,'jQuery UI button','','','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','','../extensions/jquery-ui-1.8.1.custom/development-bundle/ui/jquery.ui.button.js','','jq_ui,jq_ui_widget','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','jq_ui_droppable',4,1,'jQuery UI droppable','','','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','','../extensions/jquery-ui-1.8.1.custom/development-bundle/ui/jquery.ui.droppable.js','','jq_ui,jq_ui_widget,jq_ui_mouse,jq_ui_draggable','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','mb_tabs_horizontal',4,1,'Puts existing elements into horizontal tabs, using jQuery UI tabs. List the elements comma-separated under target, and make sure they have a title.','','div','','',0,200,700,NULL ,NULL,'','<ul></ul><div class=''ui-layout-content''></div>','div','../plugins/mb_tabs_horizontal.js','','mb_div_collection2,actuality,classifications,provider,classSearchResources','jq_ui_tabs','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Geoportal-SL_erwSuche2','mb_tabs_horizontal','inputs','[
    {
        "type": "id",
        "method": "select",
        "title": "Select a tab",
        "linkedTo": [
            {
                "id": "mb_md_select",
                "event": "selected",
                "value": "mb_md_edit" 
            } 
        ] 
    }
] ','','var');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','jq_ui_dialog',5,1,'Dialog from jQuery UI framework','','','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','','../extensions/jquery-ui-1.8.1.custom/development-bundle/ui/jquery.ui.dialog.js','','jq_ui,jq_ui_widget,jq_ui_button,jq_ui_draggable,jq_ui_mouse,jq_ui_position,jq_ui_resizable','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','jq_ui_tabs',5,1,'horizontal tabs from the jQuery UI framework','','','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','','../extensions/jquery-ui-1.8.1.custom/development-bundle/ui/jquery.ui.tabs.js','','jq_ui,jq_ui_widget','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','jq_ui_datepicker',5,1,'Datepicker from jQuery UI framework','','div','','',NULL ,NULL,NULL ,NULL,NULL ,'','','div','','../extensions/jquery-ui-1.8.1.custom/development-bundle/ui/jquery.ui.datepicker.js','','jq_ui','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','jq_ui_draggable',5,1,'Draggable from the jQuery UI framework','','','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','','../extensions/jquery-ui-1.8.1.custom/development-bundle/ui/jquery.ui.draggable.js','','jq_ui,jq_ui_mouse,jq_ui_widget','http://jqueryui.com/demos/draggable/');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Geoportal-SL_erwSuche2','jq_ui_resizable',5,1,'Resizable from the jQuery UI framework','','','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','../plugins/jq_ui_resizable.js','../extensions/jquery-ui-1.8.1.custom/development-bundle/ui/jquery.ui.resizable.js','','jq_ui,jq_ui_mouse,jq_ui_widget','http://jqueryui.com/demos/resizable/');
