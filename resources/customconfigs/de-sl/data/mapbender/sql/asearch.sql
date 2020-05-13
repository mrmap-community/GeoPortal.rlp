INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('Geoportal-SL','asearch',200,1,'Adresssuche, übernommen aus Solarkataster','','iframe','../x_gaz/geopolypolis_point.php','',NULL ,NULL ,190,NULL ,NULL ,'','','','','','','','');

INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('Geoportal-SL','asearch_button',201,1,'','','img','../img/button_blue_red/query_off.png','',1000,500,24,24,1,'','','','../x_gaz/asearch_button.php','','asearch','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL', 'asearch_button', 'breite', '215', '' ,'var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL', 'asearch_button', 'hoehe', '200', '' ,'var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL', 'asearch_button', 'position', '[200, 250]', '' ,'var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('Geoportal-SL', 'asearch_button', 'titel', 'Adresssuche', '' ,'var');

