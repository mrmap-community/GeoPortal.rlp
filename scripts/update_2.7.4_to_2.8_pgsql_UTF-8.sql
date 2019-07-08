\set group_id 36
\set db_owner "postgres"
-- new file for db changes to 2.8.0
-- new option to allow edited categories to be overwritten by service information (wms 1.3.0 - layer categories)
ALTER TABLE scheduler ADD COLUMN scheduler_overwrite_categories integer;
-- for those who have already used the scheduler ;-)
UPDATE scheduler SET scheduler_overwrite_categories = 0 WHERE scheduler_overwrite_categories IS NULL;
-- Drop column: inspire_daily_requests
ALTER TABLE wms DROP COLUMN inspire_daily_requests;
ALTER TABLE wms ADD COLUMN inspire_annual_requests bigint;
ALTER TABLE wfs DROP COLUMN inspire_daily_requests;
ALTER TABLE wfs ADD COLUMN inspire_annual_requests bigint;

-- Function: f_collect_custom_cat_dataset(integer)

-- DROP FUNCTION f_collect_custom_cat_dataset(integer);

CREATE OR REPLACE FUNCTION f_collect_custom_cat_dataset(integer)
  RETURNS text AS
$BODY$DECLARE
  i_dataset_id ALIAS FOR $1;
  custom_cat_string  TEXT;
  custom_cat_record  RECORD;

BEGIN
custom_cat_string := '';

FOR custom_cat_record IN SELECT DISTINCT mb_metadata_custom_category.fkey_custom_category_id from mb_metadata_custom_category WHERE mb_metadata_custom_category.fkey_metadata_id=$1  LOOP
custom_cat_string := custom_cat_string || '{' ||custom_cat_record.fkey_custom_category_id || '}';
END LOOP ;

RETURN custom_cat_string;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE STRICT
  COST 100;
ALTER FUNCTION f_collect_custom_cat_dataset(integer)
  OWNER TO :db_owner;

-- Function: f_collect_inspire_cat_dataset(integer)

-- DROP FUNCTION f_collect_inspire_cat_dataset(integer);

CREATE OR REPLACE FUNCTION f_collect_inspire_cat_dataset(integer)
  RETURNS text AS
$BODY$DECLARE
  i_dataset_id ALIAS FOR $1;
  inspire_cat_string  TEXT;
  inspire_cat_record  RECORD;

BEGIN
inspire_cat_string := '';

FOR inspire_cat_record IN SELECT DISTINCT mb_metadata_inspire_category.fkey_inspire_category_id from mb_metadata_inspire_category WHERE mb_metadata_inspire_category.fkey_metadata_id=$1  LOOP
inspire_cat_string := inspire_cat_string || '{' ||inspire_cat_record.fkey_inspire_category_id || '}';
END LOOP ;

RETURN inspire_cat_string;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE STRICT
  COST 100;
ALTER FUNCTION f_collect_inspire_cat_dataset(integer)
  OWNER TO :db_owner;

-- Function: f_collect_topic_cat_dataset(integer)

-- DROP FUNCTION f_collect_topic_cat_dataset(integer);

CREATE OR REPLACE FUNCTION f_collect_topic_cat_dataset(integer)
  RETURNS text AS
$BODY$DECLARE
  i_dataset_serial_id ALIAS FOR $1;
  topic_cat_string  TEXT;
  topic_cat_record  RECORD;

BEGIN
topic_cat_string := '';

FOR topic_cat_record IN SELECT mb_metadata_md_topic_category.fkey_md_topic_category_id from mb_metadata_md_topic_category WHERE mb_metadata_md_topic_category.fkey_metadata_id=$1  LOOP
topic_cat_string := topic_cat_string || '{' ||topic_cat_record.fkey_md_topic_category_id || '}';
END LOOP ;

RETURN topic_cat_string;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE STRICT
  COST 100;
ALTER FUNCTION f_collect_topic_cat_dataset(integer)
  OWNER TO :db_owner;


-- Function: f_collect_searchtext_dataset(integer)

-- DROP FUNCTION f_collect_searchtext_dataset(integer);

CREATE OR REPLACE FUNCTION f_collect_searchtext_dataset(integer)
  RETURNS text AS
$BODY$
DECLARE
    p_dataset_id ALIAS FOR $1;

    r_keywords RECORD;
    l_result TEXT;
BEGIN
    l_result := '';
    l_result := l_result || (SELECT COALESCE(title, '') || ' ' || COALESCE(abstract, '') FROM mb_metadata WHERE metadata_id = p_dataset_id);
    FOR r_keywords IN SELECT DISTINCT keyword FROM
        (SELECT keyword FROM mb_metadata_keyword L JOIN keyword K ON (K.keyword_id = L.fkey_keyword_id AND L.fkey_metadata_id = p_dataset_id)
        ) AS __keywords__ LOOP
        l_result := l_result || ' ' || COALESCE(r_keywords.keyword, '');
    END LOOP;
   l_result := UPPER(l_result);
   l_result := replace(replace(replace(replace(replace(replace(replace(l_result,'Ä','AE'),'ß','SS'),'Ö','OE'),'Ü','UE'),'ä','AE'),'ü','UE'),'ö','OE');

    RETURN l_result;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION f_collect_searchtext_dataset(integer)
  OWNER TO :db_owner;

--DROP VIEW search_dataset_view;
--DROP FUNCTION f_collect_custom_cat_dataset(integer);
-- DROP FUNCTION f_collect_inspire_cat_dataset(integer);
-- DROP FUNCTION f_collect_topic_cat_dataset(integer);
-- DROP FUNCTION f_collect_searchtext_dataset(integer);

--new option to search for datasets in the internal mb_metadata table
-- View: search_dataset_view

DROP VIEW search_dataset_view;

CREATE OR REPLACE VIEW search_dataset_view AS
 SELECT dataset_dep.fkey_mb_user_id AS user_id, dataset_dep.dataset_id, dataset_dep.srs AS dataset_srs, dataset_dep.title, dataset_dep.abstract AS dataset_abstract, f_collect_searchtext_dataset(dataset_dep.dataset_id) AS searchtext, dataset_dep.dataset_timestamp, dataset_dep.department, dataset_dep.mb_group_name, dataset_dep.mb_group_title, dataset_dep.mb_group_country, 0 AS load_count, dataset_dep.mb_group_stateorprovince, f_collect_inspire_cat_dataset(dataset_dep.dataset_id) AS md_inspire_cats, f_collect_custom_cat_dataset(dataset_dep.dataset_id) AS md_custom_cats, f_collect_topic_cat_dataset(dataset_dep.dataset_id) AS md_topic_cats, dataset_dep.bbox, dataset_dep.mb_group_logo_path
   FROM ( SELECT mb_metadata.the_geom as bbox, mb_metadata.ref_system as srs, mb_metadata.metadata_id AS dataset_id, mb_metadata.title, mb_metadata.abstract, mb_metadata.lastchanged as dataset_timestamp, mb_metadata.fkey_mb_user_id, user_dep.mb_group_id AS department, user_dep.mb_group_name, user_dep.mb_group_title, user_dep.mb_group_country, user_dep.mb_group_stateorprovince, user_dep.mb_group_logo_path
           FROM ( SELECT registrating_groups.fkey_mb_user_id AS mb_user_id, mb_group.mb_group_id, mb_group.mb_group_name, mb_group.mb_group_title, mb_group.mb_group_country, mb_group.mb_group_stateorprovince, mb_group.mb_group_logo_path
                   FROM registrating_groups, mb_group
                  WHERE registrating_groups.fkey_mb_group_id = mb_group.mb_group_id) user_dep, mb_metadata
          WHERE user_dep.mb_user_id = mb_metadata.fkey_mb_user_id) dataset_dep
  ORDER BY dataset_dep.dataset_id;

ALTER TABLE search_dataset_view
  OWNER TO :db_owner;

ALTER TABLE custom_category ALTER COLUMN custom_category_key TYPE varchar(255);

-- Column: inspire_download

-- ALTER TABLE mb_metadata DROP COLUMN inspire_download;

ALTER TABLE mb_metadata ADD COLUMN inspire_download integer;
UPDATE mb_metadata SET inspire_download = 0 WHERE inspire_download IS NULL;

alter table layer_style alter column legendurl type text;

-- Function: f_wms_searchable_layers(integer)

-- DROP FUNCTION f_wms_searchable_layers(integer);

CREATE OR REPLACE FUNCTION f_wms_searchable_layers(integer)
  RETURNS integer AS
$BODY$
DECLARE

  i_wms_id ALIAS FOR $1;
  n_count INTEGER;

BEGIN

n_count := count(layer_id) from layer where fkey_wms_id = i_wms_id and layer_searchable = 1;

RETURN n_count;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE COST 100;
ALTER FUNCTION f_wms_searchable_layers(integer)
  OWNER TO :db_owner;

-- View: wms_list_view
DROP VIEW wms_list_view;

CREATE OR REPLACE VIEW wms_list_view AS

SELECT DISTINCT btrim(wms_group.wms_title::text) AS wms_title, replace(replace(replace(replace(replace(replace(replace(upper(btrim(wms_group.wms_title::text)), 'Ä'::text, 'A'::text), 'Ö'::text, 'O'::text), 'Ü'::text, 'U'::text), 'ä'::text, 'A'::text), 'ö'::text, 'O'::text), 'ü'::text, 'U'::text), 'ß'::text, 'SS'::text) AS wms_title_upper, wms_group.wms_abstract, wms_group.wms_id, wms_group.mb_group_name, replace(replace(replace(replace(replace(replace(replace(upper(btrim(wms_group.mb_group_name::text)), 'Ä'::text, 'A'::text), 'Ö'::text, 'O'::text), 'Ü'::text, 'U'::text), 'ä'::text, 'A'::text), 'ö'::text, 'O'::text), 'ü'::text, 'U'::text), 'ß'::text, 'SS'::text) AS mb_group_name_upper, wms_group.layer_id
   FROM ( SELECT wms.wms_id, wms.wms_title, wms.wms_abstract, user_dep.fkey_mb_group_id AS wms_department, user_dep.mb_group_name, user_dep.mb_group_logo_path, layer.layer_id
           FROM ( SELECT registrating_groups.fkey_mb_user_id, mb_group.mb_group_id AS fkey_mb_group_id, mb_group.mb_group_name, mb_group.mb_group_title, mb_group.mb_group_country, mb_group.mb_group_stateorprovince, mb_group.mb_group_logo_path
                   FROM registrating_groups, mb_group
                  WHERE registrating_groups.fkey_mb_group_id = mb_group.mb_group_id) user_dep, wms, layer
          WHERE wms.wms_owner = user_dep.fkey_mb_user_id AND wms.wms_id = layer.fkey_wms_id AND layer.layer_pos = 0 AND f_wms_searchable_layers(layer.fkey_wms_id) <> 0) wms_group
  WHERE wms_group.wms_title IS NOT NULL
  ORDER BY btrim(wms_group.wms_title::text), replace(replace(replace(replace(replace(replace(replace(upper(btrim(wms_group.wms_title::text)), 'Ä'::text, 'A'::text), 'Ö'::text, 'O'::text), 'Ü'::text, 'U'::text), 'ä'::text, 'A'::text), 'ö'::text, 'O'::text), 'ü'::text, 'U'::text), 'ß'::text, 'SS'::text), wms_group.wms_abstract, wms_group.wms_id, wms_group.mb_group_name, replace(replace(replace(replace(replace(replace(replace(upper(btrim(wms_group.mb_group_name::text)), 'Ä'::text, 'A'::text), 'Ö'::text, 'O'::text), 'Ü'::text, 'U'::text), 'ä'::text, 'A'::text), 'ö'::text, 'O'::text), 'ü'::text, 'U'::text), 'ß'::text, 'SS'::text), wms_group.layer_id;


ALTER TABLE wms_list_view
  OWNER TO :db_owner;

-- Function: f_tou_isopen(integer)

-- DROP FUNCTION f_tou_isopen(integer);

CREATE OR REPLACE FUNCTION f_tou_isopen(integer)
  RETURNS integer AS
$BODY$
DECLARE
   tou_isopen int4;
BEGIN
tou_isopen := isopen from termsofuse where termsofuse.termsofuse_id = $1;
RETURN tou_isopen;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION f_tou_isopen(integer)
  OWNER TO :db_owner;

--enhance search view with isopen classification for resources
-- View: search_wms_view

DROP VIEW search_wms_view CASCADE;

CREATE OR REPLACE VIEW search_wms_view AS
 SELECT DISTINCT ON (wms_unref.layer_id) wms_unref.wms_id, wms_unref.availability, wms_unref.status, wms_unref.wms_title, wms_unref.wms_abstract, wms_unref.stateorprovince, wms_unref.country, wms_unref.accessconstraints, wms_unref.termsofuse, wms_unref.isopen, wms_unref.wms_owner, wms_unref.layer_id, wms_unref.epsg, wms_unref.layer_title, wms_unref.layer_abstract, wms_unref.layer_name, wms_unref.layer_parent, wms_unref.layer_pos, wms_unref.layer_queryable, wms_unref.load_count, wms_unref.searchtext, wms_unref.wms_timestamp, wms_unref.department, wms_unref.mb_group_name, f_collect_custom_cat_layer(wms_unref.layer_id) AS md_custom_cats, f_collect_inspire_cat_layer(wms_unref.layer_id) AS md_inspire_cats, f_collect_topic_cat_layer(wms_unref.layer_id) AS md_topic_cats, geometryfromtext(((((((((((((((((((('POLYGON(('::text || layer_epsg.minx::text) || ' '::text) || layer_epsg.miny::text) || ','::text) || layer_epsg.minx::text) || ' '::text) || layer_epsg.maxy::text) || ','::text) || layer_epsg.maxx::text) || ' '::text) || layer_epsg.maxy::text) || ','::text) || layer_epsg.maxx::text) || ' '::text) || layer_epsg.miny::text) || ','::text) || layer_epsg.minx::text) || ' '::text) || layer_epsg.miny::text) || '))'::text, 4326) AS the_geom, (((((layer_epsg.minx::text || ','::text) || layer_epsg.miny::text) || ','::text) || layer_epsg.maxx::text) || ','::text) || layer_epsg.maxy::text AS bbox, wms_unref.wms_proxylog, wms_unref.wms_network_access, wms_unref.wms_pricevolume, wms_unref.mb_group_logo_path
   FROM ( SELECT wms_uncat.wms_id, wms_uncat.availability, wms_uncat.status, wms_uncat.wms_title, wms_uncat.wms_abstract, wms_uncat.stateorprovince, wms_uncat.country, wms_uncat.accessconstraints, wms_uncat.termsofuse, wms_uncat.isopen, wms_uncat.wms_owner, wms_uncat.layer_id, wms_uncat.epsg, wms_uncat.layer_title, wms_uncat.layer_abstract, wms_uncat.layer_name, wms_uncat.layer_parent, wms_uncat.layer_pos, wms_uncat.layer_queryable, wms_uncat.load_count, wms_uncat.searchtext, wms_uncat.wms_timestamp, wms_uncat.department, wms_uncat.mb_group_name, wms_uncat.wms_proxylog, wms_uncat.wms_network_access, wms_uncat.wms_pricevolume, wms_uncat.mb_group_logo_path
           FROM ( SELECT wms_dep.wms_id, wms_dep.availability, wms_dep.status, wms_dep.wms_title, wms_dep.wms_abstract, wms_dep.stateorprovince, wms_dep.country, wms_dep.accessconstraints, wms_dep.termsofuse, wms_dep.isopen, wms_dep.wms_owner, layer.layer_id, f_collect_epsg(layer.layer_id) AS epsg, layer.layer_title, layer.layer_abstract, layer.layer_name, layer.layer_parent, layer.layer_pos, layer.layer_queryable, f_layer_load_count(layer.layer_id) AS load_count, f_collect_searchtext(wms_dep.wms_id, layer.layer_id) AS searchtext, wms_dep.wms_timestamp, wms_dep.department, wms_dep.mb_group_name, wms_dep.wms_proxylog, wms_dep.wms_network_access, wms_dep.wms_pricevolume, wms_dep.mb_group_logo_path
                   FROM ( SELECT wms.wms_id, wms.wms_title, wms.wms_abstract, wms.stateorprovince, wms.country, mb_wms_availability.availability, mb_wms_availability.last_status AS status, wms.accessconstraints, f_getwms_tou(wms.wms_id) AS termsofuse, f_tou_isopen(f_getwms_tou(wms.wms_id)) as isopen, wms.wms_timestamp, wms.wms_owner, wms.wms_proxylog, wms.wms_network_access, wms.wms_pricevolume, user_dep.fkey_mb_group_id AS department, user_dep.fkey_mb_group_id, user_dep.fkey_mb_group_id AS wms_department, user_dep.mb_group_name, user_dep.mb_group_logo_path
                           FROM ( SELECT registrating_groups.fkey_mb_user_id, mb_group.mb_group_id AS fkey_mb_group_id, mb_group.mb_group_name, mb_group.mb_group_title, mb_group.mb_group_country, mb_group.mb_group_stateorprovince, mb_group.mb_group_logo_path
                                   FROM registrating_groups, mb_group
                                  WHERE registrating_groups.fkey_mb_group_id = mb_group.mb_group_id) user_dep, wms, mb_wms_availability
                          WHERE wms.wms_owner = user_dep.fkey_mb_user_id AND wms.wms_id = mb_wms_availability.fkey_wms_id) wms_dep, layer
                  WHERE layer.fkey_wms_id = wms_dep.wms_id AND layer.layer_searchable = 1) wms_uncat) wms_unref, layer_epsg
  WHERE layer_epsg.epsg::text = 'EPSG:4326'::text AND wms_unref.layer_id = layer_epsg.fkey_layer_id
  ORDER BY wms_unref.layer_id;

ALTER TABLE search_wms_view
  OWNER TO :db_owner;

-- View: search_wfs_view

DROP VIEW search_wfs_view CASCADE;

CREATE OR REPLACE VIEW search_wfs_view AS
 SELECT wfs_dep.wfs_id, wfs_dep.wfs_title, wfs_dep.wfs_abstract, wfs_dep.administrativearea, wfs_dep.country, wfs_dep.accessconstraints, wfs_dep.termsofuse, wfs_dep.isopen, wfs_dep.wfs_owner, wfs_featuretype.featuretype_id, wfs_featuretype.featuretype_srs, wfs_featuretype.featuretype_title, wfs_featuretype.featuretype_abstract, f_collect_searchtext_wfs(wfs_dep.wfs_id, wfs_featuretype.featuretype_id) AS searchtext, wfs_element.element_type, wfs_conf.wfs_conf_id, wfs_conf.wfs_conf_abstract, wfs_conf.wfs_conf_description, f_getwfsmodultype(wfs_conf.wfs_conf_id) AS modultype, wfs_dep.wfs_timestamp, wfs_dep.department, wfs_dep.mb_group_name, wfs_dep.mb_group_logo_path
   FROM ( SELECT wfs.wfs_id, wfs.wfs_title, wfs.wfs_abstract, wfs.administrativearea, wfs.country, wfs.accessconstraints, f_getwfs_tou(wfs.wfs_id) AS termsofuse, f_tou_isopen(f_getwfs_tou(wfs.wfs_id)) as isopen, wfs.wfs_timestamp, wfs.wfs_owner, user_dep.mb_group_id AS department, user_dep.mb_group_name, user_dep.mb_group_logo_path
           FROM ( SELECT registrating_groups.fkey_mb_user_id AS mb_user_id, mb_group.mb_group_id, mb_group.mb_group_name, mb_group.mb_group_logo_path
                   FROM registrating_groups, mb_group
                  WHERE registrating_groups.fkey_mb_group_id = mb_group.mb_group_id) user_dep, wfs
          WHERE user_dep.mb_user_id = wfs.wfs_owner) wfs_dep, wfs_featuretype, wfs_element, wfs_conf
  WHERE wfs_featuretype.fkey_wfs_id = wfs_dep.wfs_id AND wfs_featuretype.featuretype_searchable = 1 AND wfs_element.element_type::text ~~ '%Type'::text AND wfs_featuretype.featuretype_id = wfs_element.fkey_featuretype_id AND wfs_featuretype.featuretype_id = wfs_conf.fkey_featuretype_id
  ORDER BY wfs_featuretype.featuretype_id;

ALTER TABLE search_wfs_view
  OWNER TO :db_owner;

-- Column: wfs_username

-- ALTER TABLE wfs DROP COLUMN wfs_username;

ALTER TABLE wfs ADD COLUMN wfs_username character varying(255);
ALTER TABLE wfs ALTER COLUMN wfs_username SET DEFAULT ''::character varying;

-- Column: wfs_password

-- ALTER TABLE wfs DROP COLUMN wfs_password;

ALTER TABLE wfs ADD COLUMN wfs_password character varying(255);
ALTER TABLE wfs ALTER COLUMN wfs_password SET DEFAULT ''::character varying;

-- Column: wfs_auth_type

-- ALTER TABLE wfs DROP COLUMN wfs_auth_type;

ALTER TABLE wfs ADD COLUMN wfs_auth_type character varying(255);
ALTER TABLE wfs ALTER COLUMN wfs_auth_type SET DEFAULT ''::character varying;

UPDATE wfs SET wfs_username = '' WHERE wfs_username IS NULL;
UPDATE wfs SET wfs_password = '' WHERE wfs_password IS NULL;
UPDATE wfs SET wfs_auth_type = '' WHERE wfs_auth_type IS NULL;

--Erweiterung Tabelle mb_proxy_log
ALTER TABLE mb_proxy_log ADD COLUMN got_result integer;
ALTER TABLE mb_proxy_log ADD COLUMN error_message text;
ALTER TABLE mb_proxy_log ADD COLUMN error_mime_type character varying(50);
ALTER TABLE mb_proxy_log ADD COLUMN layer_featuretype_list text;
ALTER TABLE mb_proxy_log ADD COLUMN request_type character varying(15);
ALTER TABLE mb_proxy_log ADD COLUMN log_id serial;
ALTER TABLE mb_proxy_log ADD PRIMARY KEY (log_id);
--ALTER TABLE mb_proxy_log SET WITH OIDS;

--neue Anwendung Owsproxy_csv
INSERT INTO gui (gui_id, gui_name, gui_description, gui_public) VALUES ('Owsproxy_csv','Owsproxy_csv','GUI combining most of the Mapbender functionality',1);
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Owsproxy_csv','i18n',1,0,'Internationalization module, collects data from all elements and sends them to the server in a single POST request. The strings are translated via gettext only.','Internationalization','div','','',NULL ,NULL,NULL ,NULL,NULL ,'','','div','../plugins/mb_i18n.js','','','','http://www.mapbender.org/Gettext');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Owsproxy_csv','jq_upload',1,1,'','','','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','','../plugins/jq_upload.js','','','');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Owsproxy_csv','jq_validate',1,0,'The jQuery validation plugin','','','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','../javascripts/jq_validate.js','../extensions/jquery-validate/jquery.validate.min.js','','','http://docs.jquery.com/Plugins/Validation');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Owsproxy_csv','jq_validate','css','label.error { float: none; color: red; padding-left: .5em; vertical-align: top; }','','text/css');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Owsproxy_csv','jq_datatables',1,1,'Includes the jQuery plugin datatables, use like this
$(selector).datatables(options)','','','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','../plugins/jq_datatables.js','../extensions/dataTables-1.5/media/js/jquery.dataTables.min.js','','','http://www.datatables.net/');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Owsproxy_csv','jq_datatables','defaultCss','../extensions/dataTables-1.5/media/css/demo_table_jui.css','','file/css');

--use core element instead of single ui elements
--INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Owsproxy_csv','jq_ui',1,1,'The jQuery UI core','','','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','','../extensions/jquery-ui-1.8.16.custom/development-bundle/ui/jquery.ui.core.js','','','');

INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Owsproxy_csv','jq_ui',1,1,'The jQuery UI core','','','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','','../extensions/jquery-ui-1.8.16.custom/js/jquery-ui-1.8.16.custom.min.js','','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Owsproxy_csv','jq_ui','css','../extensions/jquery-ui-1.8.16.custom/css/ui-lightness/jquery-ui-1.8.16.custom.css','','file/css');

INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Owsproxy_csv','body',1,1,'body (obligatory)','','body','','',NULL ,NULL,NULL ,550,NULL ,'position:relative !important;overflow:visible;','','body','','','','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Owsproxy_csv','body','css_file_body','../css/mapbender.css','file/css','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Owsproxy_csv','body','css_file_feedtree','../css/feedtree.css','file/css','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Owsproxy_csv','body','css_file_wfstree','../css/wfsconftree.css','file/css','file/css');
--INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Owsproxy_csv','body','css_mapviewer','../css/map_sl.css','','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Owsproxy_csv','body','favicon','../img/favicon.ico','favicon','php_var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Owsproxy_csv','body','includeWhileLoading','../include/gui1_splash.php','','php_var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Owsproxy_csv','body','jq_ui_theme','../extensions/jquery-ui-1.7.2.custom/css/smoothness/jquery-ui-1.7.2.custom.css','UI Theme from Themeroller','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Owsproxy_csv','body','jquery_datatables','../extensions/dataTables-1.5/media/css/demo_table_jui.css','','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Owsproxy_csv','body','popupcss','../css/popup.css','file css','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Owsproxy_csv','body','print_css','../css/print_div.css','','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Owsproxy_csv','body','tablesortercss','../css/tablesorter.css','file css','file/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Owsproxy_csv','body','use_load_message','true','','php_var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Owsproxy_csv','body','css_owsproxy_log','../css/owsproxy_logs.css','','file/css');


INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Owsproxy_csv','tabs',2,0,'vertical tabs to handle iframes','','div','','',5,295,195,20,3,'font-family: Arial,Helvetica;font-weight:bold;','','div','mod_tab.php','','','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Owsproxy_csv','tabs','tab_frameHeight[0]','230','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Owsproxy_csv','tabs','tab_frameHeight[1]','230','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Owsproxy_csv','tabs','tab_frameHeight[2]','230','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Owsproxy_csv','tabs','tab_ids[1]','wfsConfTree','','php_var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Owsproxy_csv','tabs','tab_ids[2]','feeds','','php_var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Owsproxy_csv','tabs','open_tab','0','define which tab should be opened when a gui is opened','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Owsproxy_csv','tabs','tab_ids[0]','treeGDE','','php_var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Owsproxy_csv','tabs','tab_prefix','  ','','var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Owsproxy_csv','tabs','expandable','0','1 = expand the tabs to fit the document vertically, default is 0','php_var');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Owsproxy_csv','tabs','tab_style','position:absolute;visibility:visible;cursor:pointer;','','var');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Owsproxy_csv','logout',2,0,'Logout','Abmelden','img','../img/button_blue_red/logout_off.png','onClick="window.location.href=''../php/mod_logout.php?sessionID''" border=''0'' onmouseover=''this.src="../img/button_blink_red/logout_over.png"'' onmouseout=''this.src="../img/button_blink_red/logout_off.png"'' ',704,126,24,24,1,'','','','','','','','http://www.mapbender.org/index.php/Logout');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Owsproxy_csv','logout','logout_location','http://www.mapbender.org/','webside to show after logout','php_var');
INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Owsproxy_csv','owsproxy_log_csv',2,1,'Load owsproxy log data as csv','Load oxsproxy log csv','div','','',NULL ,NULL,NULL ,NULL,2,'overflow:auto;background-color: white;width:100%;height:100%;padding:20px;','','div','../plugins/mb_owsproxy_log_csv.js','','','','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES ('Owsproxy_csv','owsproxy_log_csv','owsproxy_log_css','#owsproxy-log-query{width:500px;}
#owsproxy-log-query div.field label { width:150px;}','','text/css');

INSERT INTO gui_element (fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element,e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires,e_url) VALUES ('Owsproxy_csv','ig_ui_timepicker',5,1,'Includes the jQuery plugin timepicker','','','','',NULL ,NULL,NULL ,NULL,NULL ,'','','','../extensions/jquery-ui-1.8.16.custom/development-bundle/ui/jquery.ui.timepicker.js','','','','');

--Eintragen der neuen Owsproxy-Calc-Elemente in Gui Administration_DE
INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('Administration_DE','owsproxy_calc',2,1,'secure services','OWSProxy WMS Calculation','a','','href = "../frames/index.php?guiID=Owsproxy_csv"',80,10,NULL ,NULL ,NULL ,'font-family: Arial, Helvetica, sans-serif; font-size : 12px; text-decoration : none; color: #808080;','Sicherheits Proxy (Abrechnung)','a','','','','','');

INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('Administration_DE','owsproxy_calc_collection',2,1,'Put existing divs in new div object. List the elements comma-separated under target, and make sure they have a title.','','div','','',150,160,200,30,NULL ,'','','div','../plugins/mb_div_collection.js','','owsproxy_calc,owsproxy_calc_icon','','');

INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('Administration_DE','owsproxy_calc_icon',2,1,'icon','','img','../img/gnome/emblem-readonly.png','',0,0,NULL ,NULL ,2,'','','','','','','','');

--Eintragen der neuen Gui Owsproxy_csv für User root in Tabelle gui_mb_user
INSERT INTO gui_mb_user (fkey_gui_id,  fkey_mb_user_id, mb_user_type) VALUES ('Owsproxy_csv', '1', 'owner');

--ergänze Eintrag owsproxy_calc_collection in Target von Element menu_wms in Administration_DE
UPDATE gui_element SET e_target = replace(e_target, 'owsproxy_collection', 'owsproxy_collection,owsproxy_calc_collection') WHERE fkey_gui_id = 'Administration_DE' AND e_id = 'menu_wms';

-- Column: wms_proxy_log_fi

-- ALTER TABLE wms DROP COLUMN wms_proxy_log_fi;

ALTER TABLE wms ADD COLUMN wms_proxy_log_fi integer;

-- Column: wms_price_fi

-- ALTER TABLE wms DROP COLUMN wms_price_fi;

ALTER TABLE wms ADD COLUMN wms_price_fi integer;

ALTER TABLE mb_proxy_log  ALTER COLUMN log_id TYPE BIGINT;
ALTER TABLE mb_metadata ALTER COLUMN inspire_whole_area TYPE varchar;
ALTER TABLE mb_metadata ALTER COLUMN inspire_actual_coverage TYPE varchar;

--
--new function to get all related download options for a given layer_id
--
-- Function: f_get_download_options_for_layer(integer)

-- DROP FUNCTION f_get_download_options_for_layer(integer);

CREATE OR REPLACE FUNCTION f_get_download_options_for_layer(integer)
  RETURNS text AS
$BODY$DECLARE
  i_layer_id ALIAS FOR $1;
  md_uuids_string  TEXT;
  md_uuid_record  RECORD;

BEGIN
md_uuids_string := '';

FOR md_uuid_record IN SELECT DISTINCT

uuid from mb_metadata ,

(

select layer.layer_id as resource_id, 'layer' as resource_type , fkey_metadata_id as md_id from layer inner join ows_relation_metadata on layer.layer_id = ows_relation_metadata.fkey_layer_id where ows_relation_metadata.fkey_metadata_id  in
(select mb_metadata.metadata_id from ows_relation_metadata inner join mb_metadata on ows_relation_metadata.fkey_metadata_id = mb_metadata.metadata_id where fkey_layer_id = $1)
and layer.inspire_download = 1 and layer_searchable = 1

union

select wfs_featuretype.featuretype_id as resource_id, 'featuretype' as resource_type , fkey_metadata_id as md_id from wfs_featuretype inner join ows_relation_metadata on wfs_featuretype.featuretype_id = ows_relation_metadata.fkey_featuretype_id where ows_relation_metadata.fkey_metadata_id  in
(select mb_metadata.metadata_id from ows_relation_metadata inner join mb_metadata on ows_relation_metadata.fkey_metadata_id = mb_metadata.metadata_id where fkey_layer_id = $1) and wfs_featuretype.inspire_download = 1 and wfs_featuretype.featuretype_searchable = 1

union

select  mb_metadata.metadata_id as resource_id, 'metadata' as resource_type , mb_metadata.metadata_id as md_id from mb_metadata where metadata_id in (select mb_metadata.metadata_id from ows_relation_metadata inner join mb_metadata on ows_relation_metadata.fkey_metadata_id = mb_metadata.metadata_id where fkey_layer_id = $1) and mb_metadata.inspire_download = 1

) as download_options where mb_metadata.metadata_id = download_options.md_id

LOOP
md_uuids_string := md_uuids_string || '{' ||md_uuid_record.uuid || '}';
END LOOP ;

RETURN md_uuids_string;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE STRICT
  COST 100;
ALTER FUNCTION f_get_download_options_for_layer(integer)
  OWNER TO :db_owner;

-- alter view for list of metadata uuids that are connected to download services


DROP VIEW search_wms_view CASCADE;

CREATE OR REPLACE VIEW search_wms_view AS
 SELECT DISTINCT ON (wms_unref.layer_id) wms_unref.wms_id, wms_unref.availability, wms_unref.status, wms_unref.wms_title, wms_unref.wms_abstract, wms_unref.stateorprovince, wms_unref.country, wms_unref.accessconstraints, wms_unref.termsofuse, wms_unref.isopen, wms_unref.wms_owner, wms_unref.layer_id, wms_unref.epsg, wms_unref.layer_title, wms_unref.layer_abstract, wms_unref.layer_name, wms_unref.layer_parent, wms_unref.layer_pos, wms_unref.layer_queryable, wms_unref.load_count, wms_unref.searchtext, wms_unref.wms_timestamp, wms_unref.department, wms_unref.mb_group_name, f_collect_custom_cat_layer(wms_unref.layer_id) AS md_custom_cats, f_collect_inspire_cat_layer(wms_unref.layer_id) AS md_inspire_cats, f_collect_topic_cat_layer(wms_unref.layer_id) AS md_topic_cats, f_get_download_options_for_layer(wms_unref.layer_id) as md_download_options, geometryfromtext(((((((((((((((((((('POLYGON(('::text || layer_epsg.minx::text) || ' '::text) || layer_epsg.miny::text) || ','::text) || layer_epsg.minx::text) || ' '::text) || layer_epsg.maxy::text) || ','::text) || layer_epsg.maxx::text) || ' '::text) || layer_epsg.maxy::text) || ','::text) || layer_epsg.maxx::text) || ' '::text) || layer_epsg.miny::text) || ','::text) || layer_epsg.minx::text) || ' '::text) || layer_epsg.miny::text) || '))'::text, 4326) AS the_geom, (((((layer_epsg.minx::text || ','::text) || layer_epsg.miny::text) || ','::text) || layer_epsg.maxx::text) || ','::text) || layer_epsg.maxy::text AS bbox, wms_unref.wms_proxylog, wms_unref.wms_network_access, wms_unref.wms_pricevolume, wms_unref.mb_group_logo_path
   FROM ( SELECT wms_uncat.wms_id, wms_uncat.availability, wms_uncat.status, wms_uncat.wms_title, wms_uncat.wms_abstract, wms_uncat.stateorprovince, wms_uncat.country, wms_uncat.accessconstraints, wms_uncat.termsofuse, wms_uncat.isopen, wms_uncat.wms_owner, wms_uncat.layer_id, wms_uncat.epsg, wms_uncat.layer_title, wms_uncat.layer_abstract, wms_uncat.layer_name, wms_uncat.layer_parent, wms_uncat.layer_pos, wms_uncat.layer_queryable, wms_uncat.load_count, wms_uncat.searchtext, wms_uncat.wms_timestamp, wms_uncat.department, wms_uncat.mb_group_name, wms_uncat.wms_proxylog, wms_uncat.wms_network_access, wms_uncat.wms_pricevolume, wms_uncat.mb_group_logo_path
           FROM ( SELECT wms_dep.wms_id, wms_dep.availability, wms_dep.status, wms_dep.wms_title, wms_dep.wms_abstract, wms_dep.stateorprovince, wms_dep.country, wms_dep.accessconstraints, wms_dep.termsofuse, wms_dep.isopen, wms_dep.wms_owner, layer.layer_id, f_collect_epsg(layer.layer_id) AS epsg, layer.layer_title, layer.layer_abstract, layer.layer_name, layer.layer_parent, layer.layer_pos, layer.layer_queryable, f_layer_load_count(layer.layer_id) AS load_count, f_collect_searchtext(wms_dep.wms_id, layer.layer_id) AS searchtext, wms_dep.wms_timestamp, wms_dep.department, wms_dep.mb_group_name, wms_dep.wms_proxylog, wms_dep.wms_network_access, wms_dep.wms_pricevolume, wms_dep.mb_group_logo_path
                   FROM ( SELECT wms.wms_id, wms.wms_title, wms.wms_abstract, wms.stateorprovince, wms.country, mb_wms_availability.availability, mb_wms_availability.last_status AS status, wms.accessconstraints, f_getwms_tou(wms.wms_id) AS termsofuse, f_tou_isopen(f_getwms_tou(wms.wms_id)) as isopen, wms.wms_timestamp, wms.wms_owner, wms.wms_proxylog, wms.wms_network_access, wms.wms_pricevolume, user_dep.fkey_mb_group_id AS department, user_dep.fkey_mb_group_id, user_dep.fkey_mb_group_id AS wms_department, user_dep.mb_group_name, user_dep.mb_group_logo_path
                           FROM ( SELECT registrating_groups.fkey_mb_user_id, mb_group.mb_group_id AS fkey_mb_group_id, mb_group.mb_group_name, mb_group.mb_group_title, mb_group.mb_group_country, mb_group.mb_group_stateorprovince, mb_group.mb_group_logo_path
                                   FROM registrating_groups, mb_group
                                  WHERE registrating_groups.fkey_mb_group_id = mb_group.mb_group_id) user_dep, wms, mb_wms_availability
                          WHERE wms.wms_owner = user_dep.fkey_mb_user_id AND wms.wms_id = mb_wms_availability.fkey_wms_id) wms_dep, layer
                  WHERE layer.fkey_wms_id = wms_dep.wms_id AND layer.layer_searchable = 1) wms_uncat) wms_unref, layer_epsg
  WHERE layer_epsg.epsg::text = 'EPSG:4326'::text AND wms_unref.layer_id = layer_epsg.fkey_layer_id
  ORDER BY wms_unref.layer_id;

ALTER TABLE search_wms_view
  OWNER TO :db_owner;

-- Column: wmc_local_data_public

-- ALTER TABLE mb_user_wmc DROP COLUMN wmc_local_data_public;

ALTER TABLE mb_user_wmc ADD COLUMN wmc_local_data_public integer;
UPDATE mb_user_wmc SET wmc_local_data_public = 0 WHERE wmc_local_data_public IS NULL;
ALTER TABLE mb_user_wmc ALTER COLUMN wmc_local_data_public SET DEFAULT 0;

-- Column: wmc_local_data_size

-- ALTER TABLE mb_user_wmc DROP COLUMN wmc_local_data_size;

ALTER TABLE mb_user_wmc ADD COLUMN wmc_local_data_size varchar(50);
UPDATE mb_user_wmc SET wmc_local_data_size = '0' WHERE wmc_local_data_size IS NULL;
ALTER TABLE mb_user_wmc ALTER COLUMN wmc_local_data_size SET DEFAULT '0';

-- Column: wmc_has_local_data

-- ALTER TABLE mb_user_wmc DROP COLUMN wmc_has_local_data;

ALTER TABLE mb_user_wmc ADD COLUMN wmc_has_local_data integer;
UPDATE mb_user_wmc SET wmc_has_local_data = 0 WHERE wmc_has_local_data IS NULL;
ALTER TABLE mb_user_wmc ALTER COLUMN wmc_has_local_data SET DEFAULT 0;

-- Option for allow publication of spatial data **************

-- Column: wmc_local_data_fkey_termsofuse_id

-- ALTER TABLE mb_user_wmc DROP COLUMN wmc_local_data_fkey_termsofuse_id;

ALTER TABLE mb_user_wmc ADD COLUMN wmc_local_data_fkey_termsofuse_id integer;

-- Foreign Key: wmc_local_data_fkey_termsofuse_id

-- ALTER TABLE mb_user_wmc DROP CONSTRAINT wmc_local_data_fkey_termsofuse_id;

ALTER TABLE mb_user_wmc
  ADD CONSTRAINT wmc_local_data_termsofuse_fkey FOREIGN KEY (wmc_local_data_fkey_termsofuse_id)
      REFERENCES termsofuse (termsofuse_id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE RESTRICT;
-- ************************************************************
-- Handle different outputformats for wfs featuretypes
-- Table: wfs_featuretype_output_formats

-- DROP TABLE wfs_featuretype_output_formats;

CREATE TABLE wfs_featuretype_output_formats
(
  fkey_featuretype_id integer NOT NULL DEFAULT 0,
  output_format character varying(50) NOT NULL DEFAULT ''::character varying,
  CONSTRAINT wfs_featuretype_output_formats_ibfk_1 FOREIGN KEY (fkey_featuretype_id)
      REFERENCES wfs_featuretype (featuretype_id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
)
WITH (
  OIDS=FALSE
);
ALTER TABLE wfs_featuretype_output_formats
  OWNER TO :db_owner;
--have also to be done for wfs itself parameter outputFormat

-- Table: wfs_output_formats

-- DROP TABLE wfs_output_formats;

CREATE TABLE wfs_output_formats
(
  fkey_wfs_id integer NOT NULL DEFAULT 0,
  output_format character varying(50) NOT NULL DEFAULT ''::character varying,
  CONSTRAINT wfs_output_formats_ibfk_1 FOREIGN KEY (fkey_wfs_id)
      REFERENCES wfs (wfs_id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
)
WITH (
  OIDS=FALSE
);
ALTER TABLE wfs_output_formats
  OWNER TO :db_owner;


-- Index für proxy-Log-Tabelle
CREATE INDEX idx_mb_proxy_log_timestamp
ON mb_proxy_log
USING btree
(proxy_log_timestamp);

-- printPDF: new element_var reverseLegend for element printPDF
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type)
SELECT gui_element.fkey_gui_id, 'printPDF', 'reverseLegend', 'false', 'define whether the legend should be printed in reverse order' ,'var'
from gui_element
WHERE
gui_element.e_id = 'printPDF' AND
gui_element.fkey_gui_id
NOT IN (SELECT fkey_gui_id FROM gui_element_vars WHERE fkey_e_id = 'printPDF' AND var_name = 'reverseLegend');

-- Table: inspire_dls_log
-- DROP TABLE inspire_dls_log;

CREATE TABLE inspire_dls_log
(
  log_id serial NOT NULL,
  createdate timestamp without time zone,
  lastchanged timestamp without time zone NOT NULL DEFAULT now(),
  link text, --capabilities and/or atom feed url
  linktype character varying(100), -- e.g. ATOM Feed, WFS
--  dls_service_uuid character varying(100),
--  metadata_uuid character varying(100),
  service_title character varying(250),
  datasetid text,
  --datasetid_codespace text,
  log_count bigint
)
WITH (
  OIDS=FALSE
);
ALTER TABLE inspire_dls_log
  OWNER TO :db_owner;

-- Trigger: update_inspire_dls_log_lastchanged on inspire_dls_log

-- DROP TRIGGER update_inspire_dls_log_lastchanged ON inspire_dls_log;

CREATE TRIGGER update_inspire_dls_log_lastchanged
  BEFORE UPDATE
  ON inspire_dls_log
  FOR EACH ROW
  EXECUTE PROCEDURE update_lastchanged_column();

-- Index: idx_ inspire_dls_log_link

-- DROP INDEX idx_ inspire_dls_log_link;

CREATE INDEX idx_inspire_dls_log_link
  ON inspire_dls_log
  USING btree
  (link );


-- Index für proxy-Log-Tabelle
CREATE INDEX idx_mb_proxy_log_timestamp
ON mb_proxy_log
USING btree
(proxy_log_timestamp);

--Title for exportMapimage module
INSERT INTO translations (locale, msgid, msgstr) values ('de','Export current mapimage','Export des aktuellen Kartenbilds');
insert into translations (locale,msgid,msgstr) values ('en','Export des aktuellen Kartenbilds','Export current mapimage');

-- Column: transfer_size

-- ALTER TABLE mb_metadata DROP COLUMN transfer_size;

ALTER TABLE mb_metadata ADD COLUMN transfer_size real;

ALTER TABLE mb_metadata ALTER column uuid type varchar(300);
ALTER TABLE mb_metadata ALTER column link type varchar(400);

ALTER TABLE mb_user ADD COLUMN mb_user_newsletter boolean;
ALTER TABLE mb_user ALTER COLUMN mb_user_newsletter SET DEFAULT false;

-- Column: mb_user_allow_survey

-- ALTER TABLE mb_user DROP COLUMN mb_user_allow_survey;

ALTER TABLE mb_user ADD COLUMN mb_user_allow_survey boolean;
ALTER TABLE mb_user ALTER COLUMN mb_user_allow_survey SET DEFAULT false;

-- Initialize values
UPDATE mb_user SET mb_user_newsletter = false WHERE mb_user_newsletter IS NULL;
UPDATE mb_user SET mb_user_allow_survey = true WHERE mb_user_allow_survey IS NULL;

--Add possibility to prohibit export of layer metadata to external catalogues
ALTER TABLE layer ADD COLUMN export2csw boolean DEFAULT true;

DROP VIEW search_wms_view CASCADE;

CREATE OR REPLACE VIEW search_wms_view AS
 SELECT DISTINCT ON (wms_unref.layer_id) wms_unref.wms_id, wms_unref.availability, wms_unref.status, wms_unref.wms_title, wms_unref.wms_abstract, wms_unref.stateorprovince, wms_unref.country, wms_unref.accessconstraints, wms_unref.termsofuse, wms_unref.isopen, wms_unref.wms_owner, wms_unref.layer_id, wms_unref.epsg, wms_unref.layer_title, wms_unref.layer_abstract, wms_unref.layer_name, wms_unref.layer_parent, wms_unref.layer_pos, wms_unref.layer_queryable, wms_unref.export2csw, wms_unref.load_count, wms_unref.searchtext, wms_unref.wms_timestamp, wms_unref.department, wms_unref.mb_group_name, f_collect_custom_cat_layer(wms_unref.layer_id) AS md_custom_cats, f_collect_inspire_cat_layer(wms_unref.layer_id) AS md_inspire_cats, f_collect_topic_cat_layer(wms_unref.layer_id) AS md_topic_cats, geometryfromtext(((((((((((((((((((('POLYGON(('::text || layer_epsg.minx::text) || ' '::text) || layer_epsg.miny::text) || ','::text) || layer_epsg.minx::text) || ' '::text) || layer_epsg.maxy::text) || ','::text) || layer_epsg.maxx::text) || ' '::text) || layer_epsg.maxy::text) || ','::text) || layer_epsg.maxx::text) || ' '::text) || layer_epsg.miny::text) || ','::text) || layer_epsg.minx::text) || ' '::text) || layer_epsg.miny::text) || '))'::text, 4326) AS the_geom, (((((layer_epsg.minx::text || ','::text) || layer_epsg.miny::text) || ','::text) || layer_epsg.maxx::text) || ','::text) || layer_epsg.maxy::text AS bbox, wms_unref.wms_proxylog, wms_unref.wms_network_access, wms_unref.wms_pricevolume, wms_unref.mb_group_logo_path
   FROM ( SELECT wms_uncat.wms_id, wms_uncat.availability, wms_uncat.status, wms_uncat.wms_title, wms_uncat.wms_abstract, wms_uncat.stateorprovince, wms_uncat.country, wms_uncat.accessconstraints, wms_uncat.termsofuse, wms_uncat.isopen, wms_uncat.wms_owner, wms_uncat.layer_id, wms_uncat.epsg, wms_uncat.layer_title, wms_uncat.layer_abstract, wms_uncat.layer_name, wms_uncat.layer_parent, wms_uncat.layer_pos, wms_uncat.layer_queryable, wms_uncat.export2csw, wms_uncat.load_count, wms_uncat.searchtext, wms_uncat.wms_timestamp, wms_uncat.department, wms_uncat.mb_group_name, wms_uncat.wms_proxylog, wms_uncat.wms_network_access, wms_uncat.wms_pricevolume, wms_uncat.mb_group_logo_path
           FROM ( SELECT wms_dep.wms_id, wms_dep.availability, wms_dep.status, wms_dep.wms_title, wms_dep.wms_abstract, wms_dep.stateorprovince, wms_dep.country, wms_dep.accessconstraints, wms_dep.termsofuse, wms_dep.isopen, wms_dep.wms_owner, layer.layer_id, f_collect_epsg(layer.layer_id) AS epsg, layer.layer_title, layer.layer_abstract, layer.layer_name, layer.layer_parent, layer.layer_pos, layer.layer_queryable, layer.export2csw, f_layer_load_count(layer.layer_id) AS load_count, f_collect_searchtext(wms_dep.wms_id, layer.layer_id) AS searchtext, wms_dep.wms_timestamp, wms_dep.department, wms_dep.mb_group_name, wms_dep.wms_proxylog, wms_dep.wms_network_access, wms_dep.wms_pricevolume, wms_dep.mb_group_logo_path
                   FROM ( SELECT wms.wms_id, wms.wms_title, wms.wms_abstract, wms.stateorprovince, wms.country, mb_wms_availability.availability, mb_wms_availability.last_status AS status, wms.accessconstraints, f_getwms_tou(wms.wms_id) AS termsofuse, f_tou_isopen(f_getwms_tou(wms.wms_id)) AS isopen, wms.wms_timestamp, wms.wms_owner, wms.wms_proxylog, wms.wms_network_access, wms.wms_pricevolume, user_dep.fkey_mb_group_id AS department, user_dep.fkey_mb_group_id, user_dep.fkey_mb_group_id AS wms_department, user_dep.mb_group_name, user_dep.mb_group_logo_path
                           FROM ( SELECT registrating_groups.fkey_mb_user_id, mb_group.mb_group_id AS fkey_mb_group_id, mb_group.mb_group_name, mb_group.mb_group_title, mb_group.mb_group_country, mb_group.mb_group_stateorprovince, mb_group.mb_group_logo_path
                                   FROM registrating_groups, mb_group
                                  WHERE registrating_groups.fkey_mb_group_id = mb_group.mb_group_id) user_dep, wms, mb_wms_availability
                          WHERE wms.wms_owner = user_dep.fkey_mb_user_id AND wms.wms_id = mb_wms_availability.fkey_wms_id) wms_dep, layer
                  WHERE layer.fkey_wms_id = wms_dep.wms_id AND layer.layer_searchable = 1) wms_uncat) wms_unref, layer_epsg
  WHERE layer_epsg.epsg::text = 'EPSG:4326'::text AND wms_unref.layer_id = layer_epsg.fkey_layer_id
  ORDER BY wms_unref.layer_id;

ALTER TABLE search_wms_view
  OWNER TO :db_owner;

DROP VIEW search_dataset_view;

CREATE OR REPLACE VIEW search_dataset_view AS
 SELECT dataset_dep.fkey_mb_user_id AS user_id, dataset_dep.dataset_id, dataset_dep.srs AS dataset_srs, dataset_dep.title, dataset_dep.abstract AS dataset_abstract, f_collect_searchtext_dataset(dataset_dep.dataset_id) AS searchtext, dataset_dep.dataset_timestamp, dataset_dep.department, dataset_dep.mb_group_name, dataset_dep.mb_group_title, dataset_dep.mb_group_country, 0 AS load_count, dataset_dep.mb_group_stateorprovince, f_collect_inspire_cat_dataset(dataset_dep.dataset_id) AS md_inspire_cats, f_collect_custom_cat_dataset(dataset_dep.dataset_id) AS md_custom_cats, f_collect_topic_cat_dataset(dataset_dep.dataset_id) AS md_topic_cats, dataset_dep.bbox as the_geom, (ST_XMin(dataset_dep.bbox)::text || ','::text || ST_YMin(dataset_dep.bbox)::text || ','::text || ST_XMax(dataset_dep.bbox)::text || ','::text || ST_YMax(dataset_dep.bbox)::text) as bbox, dataset_dep.preview_url, dataset_dep.fileidentifier, dataset_dep.mb_group_logo_path
   FROM ( SELECT mb_metadata.the_geom AS bbox, mb_metadata.ref_system AS srs, mb_metadata.metadata_id AS dataset_id, mb_metadata.title, mb_metadata.abstract, mb_metadata.lastchanged AS dataset_timestamp, mb_metadata.uuid as fileidentifier, mb_metadata.preview_image as preview_url, mb_metadata.fkey_mb_user_id, user_dep.mb_group_id AS department, user_dep.mb_group_name, user_dep.mb_group_title, user_dep.mb_group_country, user_dep.mb_group_stateorprovince, user_dep.mb_group_logo_path
           FROM ( SELECT registrating_groups.fkey_mb_user_id AS mb_user_id, mb_group.mb_group_id, mb_group.mb_group_name, mb_group.mb_group_title, mb_group.mb_group_country, mb_group.mb_group_stateorprovince, mb_group.mb_group_logo_path
                   FROM registrating_groups, mb_group
                  WHERE registrating_groups.fkey_mb_group_id = mb_group.mb_group_id) user_dep, mb_metadata
          WHERE user_dep.mb_user_id = mb_metadata.fkey_mb_user_id AND mb_metadata.the_geom IS NOT NULL) dataset_dep
  ORDER BY dataset_dep.dataset_id;

ALTER TABLE search_dataset_view
  OWNER TO :db_owner;

DROP TABLE IF EXISTS dataset_search_table;
SELECT * INTO dataset_search_table FROM search_dataset_view;

-- Column: uuid

-- ALTER TABLE mb_user_wmc DROP COLUMN uuid;

ALTER TABLE mb_user_wmc ADD COLUMN uuid uuid;

-- Table: md_termsofuse

-- DROP TABLE md_termsofuse;

CREATE TABLE md_termsofuse
(
  fkey_metadata_id integer,
  fkey_termsofuse_id integer,
  CONSTRAINT md_termsofuse_termsofuse_fkey FOREIGN KEY (fkey_termsofuse_id)
      REFERENCES termsofuse (termsofuse_id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT md_termsofuse_md_fkey FOREIGN KEY (fkey_metadata_id)
      REFERENCES mb_metadata (metadata_id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
)
WITH (
  OIDS=FALSE
);
ALTER TABLE md_termsofuse
  OWNER TO :db_owner;

-- Column: wms_license_source_note

-- ALTER TABLE wms DROP COLUMN wms_license_source_note;

ALTER TABLE wms ADD COLUMN wms_license_source_note text DEFAULT null;

-- Column: wfs_license_source_note

-- ALTER TABLE wfs DROP COLUMN wfs_license_source_note;

ALTER TABLE wfs ADD COLUMN wfs_license_source_note text DEFAULT null;

-- Column: md_license_source_note

-- ALTER TABLE mb_metadata DROP COLUMN md_license_source_note;

ALTER TABLE mb_metadata ADD COLUMN md_license_source_note text DEFAULT null;

UPDATE gui_element SET e_content='<div id="printPDF_working_bg"></div><div id="printPDF_working"><img src="../img/indicator_wheel.gif" style="padding:10px 0 0 10px">Generating PDF</div><div id="printPDF_input"><form id="printPDF_form" action="../print/printFactory.php"><div id="printPDF_selector"></div><div class="print_option"><input type="hidden" id="map_url" name="map_url" value=""/><input type="hidden" id="legend_url" name="legend_url" value=""/><input type="hidden" id="opacity" name="opacity" value=""/> <input type="hidden" id="overview_url" name="overview_url" value=""/><input type="hidden" id="map_scale" name="map_scale" value=""/><input type="hidden" name="measured_x_values" /><input type="hidden" name="measured_y_values" /><input type="hidden" name="map_svg_kml" /><input type="hidden" name="svg_extent" /><input type="hidden" name="map_svg_measures" /><br /></div><div class="print_option" id="printPDF_formsubmit"><input id="submit" type="submit" value="Print"><br /></div></form><div id="printPDF_result"></div></div>' WHERE e_id='printPDF';

--new function for resolving coupled resources from metadata id
-- Function: f_get_coupled_resources(integer)

-- DROP FUNCTION f_get_coupled_resources(integer);

DROP VIEW search_dataset_view; --depends in following function


CREATE OR REPLACE FUNCTION f_get_coupled_resources(integer)
  RETURNS text AS
$BODY$DECLARE
  i_metadata_id ALIAS FOR $1;
  layer_id_str  TEXT;
  featuretype_id_str TEXT;
  layer_id_record RECORD;
  featuretype_id_record RECORD;

BEGIN
layer_id_str := '"layerIds":[';

FOR layer_id_record IN SELECT DISTINCT

layer.layer_id from layer inner join ows_relation_metadata on layer.layer_id = ows_relation_metadata.fkey_layer_id where ows_relation_metadata.fkey_metadata_id = $1 and layer_searchable = 1

LOOP
layer_id_str := layer_id_str || layer_id_record.layer_id || ',';
END LOOP ;

featuretype_id_str := '"featuretypeIds":[';

FOR featuretype_id_record IN SELECT DISTINCT

wfs_featuretype.featuretype_id from wfs_featuretype inner join ows_relation_metadata on wfs_featuretype.featuretype_id = ows_relation_metadata.fkey_featuretype_id where ows_relation_metadata.fkey_metadata_id = $1 and featuretype_searchable = 1

LOOP
featuretype_id_str := featuretype_id_str || featuretype_id_record.featuretype_id || ',';
END LOOP ;

featuretype_id_str := trim(trailing ',' from featuretype_id_str);
layer_id_str := trim(trailing ',' from layer_id_str);

RETURN '{"coupledResources":' || '{' || layer_id_str || ']' || ',' || featuretype_id_str || ']' || '}}';

END;

$BODY$
  LANGUAGE plpgsql VOLATILE STRICT
  COST 100;
ALTER FUNCTION f_get_coupled_resources(integer)
  OWNER TO :db_owner;

--adopt search view for dataset metadata

-- View: search_dataset_view



CREATE OR REPLACE VIEW search_dataset_view AS
 SELECT dataset_dep.fkey_mb_user_id AS user_id, dataset_dep.dataset_id ,dataset_dep.dataset_id as metadata_id, dataset_dep.srs AS dataset_srs, dataset_dep.title, dataset_dep.abstract AS dataset_abstract, f_collect_searchtext_dataset(dataset_dep.dataset_id) AS searchtext, dataset_dep.dataset_timestamp, dataset_dep.department, dataset_dep.mb_group_name, dataset_dep.mb_group_title, dataset_dep.mb_group_country, 0 AS load_count, dataset_dep.mb_group_stateorprovince, f_collect_inspire_cat_dataset(dataset_dep.dataset_id) AS md_inspire_cats, f_collect_custom_cat_dataset(dataset_dep.dataset_id) AS md_custom_cats, f_collect_topic_cat_dataset(dataset_dep.dataset_id) AS md_topic_cats, dataset_dep.bbox AS the_geom, (((((st_xmin(dataset_dep.bbox::box3d)::text || ','::text) || st_ymin(dataset_dep.bbox::box3d)::text) || ','::text) || st_xmax(dataset_dep.bbox::box3d)::text) || ','::text) || st_ymax(dataset_dep.bbox::box3d)::text AS bbox, dataset_dep.preview_url, dataset_dep.fileidentifier, f_get_coupled_resources(dataset_dep.dataset_id) as coupled_resources, dataset_dep.mb_group_logo_path
   FROM ( SELECT mb_metadata.the_geom AS bbox, mb_metadata.ref_system AS srs, mb_metadata.metadata_id AS dataset_id, mb_metadata.title, mb_metadata.abstract, mb_metadata.lastchanged AS dataset_timestamp, mb_metadata.uuid AS fileidentifier, mb_metadata.preview_image AS preview_url, mb_metadata.fkey_mb_user_id, user_dep.mb_group_id AS department, user_dep.mb_group_name, user_dep.mb_group_title, user_dep.mb_group_country, user_dep.mb_group_stateorprovince, user_dep.mb_group_logo_path
           FROM ( SELECT registrating_groups.fkey_mb_user_id AS mb_user_id, mb_group.mb_group_id, mb_group.mb_group_name, mb_group.mb_group_title, mb_group.mb_group_country, mb_group.mb_group_stateorprovince, mb_group.mb_group_logo_path
                   FROM registrating_groups, mb_group
                  WHERE registrating_groups.fkey_mb_group_id = mb_group.mb_group_id) user_dep, mb_metadata
          WHERE user_dep.mb_user_id = mb_metadata.fkey_mb_user_id AND mb_metadata.the_geom IS NOT NULL) dataset_dep
  ORDER BY dataset_dep.dataset_id;

ALTER TABLE search_dataset_view
  OWNER TO :db_owner;

-- Column: mb_user_aldigest

-- ALTER TABLE mb_user DROP COLUMN mb_user_aldigest;

ALTER TABLE mb_user ADD COLUMN mb_user_aldigest text DEFAULT null;

-- Column: wfs_proxylog

-- ALTER TABLE wfs DROP COLUMN wfs_proxylog;

ALTER TABLE wfs ADD COLUMN wfs_proxylog integer DEFAULT 0;

-- Column: wfs_pricevolume

-- ALTER TABLE wfs DROP COLUMN wfs_pricevolume;

ALTER TABLE wfs ADD COLUMN wfs_pricevolume integer DEFAULT 0;

-- Column: fkey_wfs_id

-- ALTER TABLE mb_proxy_log DROP COLUMN fkey_wfs_id;

ALTER TABLE mb_proxy_log ADD COLUMN fkey_wfs_id integer;

-- Column: features

-- ALTER TABLE mb_proxy_log DROP COLUMN features;

ALTER TABLE mb_proxy_log ADD COLUMN features integer;

-- weitere Indizes für proxy-Log-Tabelle
CREATE INDEX idx_mb_proxy_log_fkey_wms_id
ON mb_proxy_log
USING btree
(fkey_wms_id);

CREATE INDEX idx_mb_proxy_log_fkey_mb_user_id
ON mb_proxy_log
USING btree
(fkey_mb_user_id);

CREATE INDEX idx_mb_proxy_log_fkey_wfs_id
ON mb_proxy_log
USING btree
(fkey_wfs_id);

DROP VIEW search_dataset_view;

ALTER TABLE mb_metadata ALTER COLUMN ref_system TYPE character varying(50);

CREATE OR REPLACE VIEW search_dataset_view AS
 SELECT dataset_dep.fkey_mb_user_id AS user_id, dataset_dep.dataset_id ,dataset_dep.dataset_id as metadata_id, dataset_dep.srs AS dataset_srs, dataset_dep.title, dataset_dep.abstract AS dataset_abstract, f_collect_searchtext_dataset(dataset_dep.dataset_id) AS searchtext, dataset_dep.dataset_timestamp, dataset_dep.department, dataset_dep.mb_group_name, dataset_dep.mb_group_title, dataset_dep.mb_group_country, 0 AS load_count, dataset_dep.mb_group_stateorprovince, f_collect_inspire_cat_dataset(dataset_dep.dataset_id) AS md_inspire_cats, f_collect_custom_cat_dataset(dataset_dep.dataset_id) AS md_custom_cats, f_collect_topic_cat_dataset(dataset_dep.dataset_id) AS md_topic_cats, dataset_dep.bbox AS the_geom, (((((st_xmin(dataset_dep.bbox::box3d)::text || ','::text) || st_ymin(dataset_dep.bbox::box3d)::text) || ','::text) || st_xmax(dataset_dep.bbox::box3d)::text) || ','::text) || st_ymax(dataset_dep.bbox::box3d)::text AS bbox, dataset_dep.preview_url, dataset_dep.fileidentifier, f_get_coupled_resources(dataset_dep.dataset_id) as coupled_resources, dataset_dep.mb_group_logo_path
   FROM ( SELECT mb_metadata.the_geom AS bbox, mb_metadata.ref_system AS srs, mb_metadata.metadata_id AS dataset_id, mb_metadata.title, mb_metadata.abstract, mb_metadata.lastchanged AS dataset_timestamp, mb_metadata.uuid AS fileidentifier, mb_metadata.preview_image AS preview_url, mb_metadata.fkey_mb_user_id, user_dep.mb_group_id AS department, user_dep.mb_group_name, user_dep.mb_group_title, user_dep.mb_group_country, user_dep.mb_group_stateorprovince, user_dep.mb_group_logo_path
           FROM ( SELECT registrating_groups.fkey_mb_user_id AS mb_user_id, mb_group.mb_group_id, mb_group.mb_group_name, mb_group.mb_group_title, mb_group.mb_group_country, mb_group.mb_group_stateorprovince, mb_group.mb_group_logo_path
                   FROM registrating_groups, mb_group
                  WHERE registrating_groups.fkey_mb_group_id = mb_group.mb_group_id) user_dep, mb_metadata
          WHERE user_dep.mb_user_id = mb_metadata.fkey_mb_user_id AND mb_metadata.the_geom IS NOT NULL) dataset_dep
  ORDER BY dataset_dep.dataset_id;

ALTER TABLE search_dataset_view
  OWNER TO :db_owner;

--add trigger for wms and wfs table to document changes!
-- Function: update_wms_timestamp_column()

-- DROP FUNCTION update_wms_timestamp_column();

CREATE OR REPLACE FUNCTION update_wms_timestamp_column()
  RETURNS trigger AS
$BODY$
BEGIN
   NEW.wms_timestamp = EXTRACT(EPOCH FROM NOW())::INTEGER;
   RETURN NEW;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION update_wms_timestamp_column()
  OWNER TO :db_owner;


--ALTER DATABASE mapbender_trunk SET search_path = 'mapbender', 'public';

-- Trigger: update_wms_timestamp on wms

DROP TRIGGER IF EXISTS update_wms_timestamp ON wms;

CREATE TRIGGER update_wms_timestamp
  BEFORE UPDATE
  ON wms
  FOR EACH ROW
  EXECUTE PROCEDURE update_wms_timestamp_column();

-- Function: update_wfs_timestamp_column()

-- DROP FUNCTION update_wfs_timestamp_column();

CREATE OR REPLACE FUNCTION update_wfs_timestamp_column()
  RETURNS trigger AS
$BODY$
BEGIN
   NEW.wfs_timestamp = EXTRACT(EPOCH FROM NOW())::INTEGER;
   RETURN NEW;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION update_wfs_timestamp_column()
  OWNER TO :db_owner;


--ALTER DATABASE mapbender_trunk SET search_path = 'mapbender', 'public';

-- Trigger: update_wfs_timestamp on wfs

DROP TRIGGER IF EXISTS update_wfs_timestamp ON wfs;

CREATE TRIGGER update_wfs_timestamp
  BEFORE UPDATE
  ON wfs
  FOR EACH ROW
  EXECUTE PROCEDURE update_wfs_timestamp_column();

-- Column: source_required

-- ALTER TABLE termsofuse DROP COLUMN source_required;

ALTER TABLE termsofuse ADD COLUMN source_required boolean;
ALTER TABLE termsofuse ALTER COLUMN source_required SET DEFAULT false;

-- Column: responsible_party_name

-- ALTER TABLE mb_metadata DROP COLUMN responsible_party_name;

ALTER TABLE mb_metadata ADD COLUMN responsible_party_name character varying(255) DEFAULT null;

-- Column: responsible_party_email

-- ALTER TABLE mb_metadata DROP COLUMN responsible_party_email;

ALTER TABLE mb_metadata ADD COLUMN responsible_party_email character varying(255) DEFAULT null;

-- New module for maintainence
INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('PortalAdmin_DE','menu_maintenance',2,1,'GUI admin menu','Wartung','div','','',NULL ,NULL ,NULL ,NULL ,NULL ,'','','div','../plugins/mb_div_collection.js','','editMaintenance_collection','','');
INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('PortalAdmin_DE','editMaintenance_collection',2,1,'Put existing divs in new div object. List the elements comma-separated under target, and make sure they have a title.','','div','','',0,0,200,30,NULL ,'','','div','../plugins/mb_div_collection.js','','orphanWMS,orphanWMS_icon','','');
INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('PortalAdmin_DE','orphanWMS',2,1,'display orphaned wms','','a','','href = "../php/mod_orphanWMS.php?sessionID" target="AdminFrame"',80,15,200,20,NULL ,'font-family: Arial, Helvetica, sans-serif; font-size : 12px; text-decoration : none; color: #808080;','Verwaiste WMS löschen','a','','','','AdminFrame','http://www.mapbender.org/index.php/DeleteWMS');
INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('PortalAdmin_DE','orphanWMS_icon',2,1,'icon','','img','../img/gnome/edit-clear.png','',0,0,NULL ,NULL ,2,'','','','','','','','');
UPDATE gui_element SET e_target='menu_maintenance,menu_group,menu_user,menu_role,menu_category' WHERE fkey_gui_id='PortalAdmin_DE' AND e_id='mb_horizontal_accordion';


-- Function: f_getmd_tou(integer)

-- DROP FUNCTION f_getmd_tou(integer);

CREATE OR REPLACE FUNCTION f_getmd_tou(integer)
  RETURNS integer AS
$BODY$
DECLARE
   md_tou int4;
BEGIN
md_tou := fkey_termsofuse_id from md_termsofuse where md_termsofuse.fkey_metadata_id=$1;
RETURN md_tou;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION f_getmd_tou(integer)
  OWNER TO :db_owner;

-- Table: metadata_load_count

-- DROP TABLE metadata_load_count;

CREATE TABLE metadata_load_count
(
  fkey_metadata_id integer,
  load_count bigint,
  CONSTRAINT metadata_load_count_fkey_metadata_id_fkey FOREIGN KEY (fkey_metadata_id)
      REFERENCES mb_metadata (metadata_id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
)
WITH (
  OIDS=FALSE
);
ALTER TABLE metadata_load_count
  OWNER TO :db_owner;

--new view which integrates the termsofuse for datasets if given

DROP VIEW search_dataset_view;

CREATE OR REPLACE VIEW search_dataset_view AS

SELECT DISTINCT ON (metadata_id) * FROM ( SELECT dataset_dep.fkey_mb_user_id AS user_id, dataset_dep.dataset_id, dataset_dep.dataset_id AS metadata_id, dataset_dep.srs AS dataset_srs, dataset_dep.title, dataset_dep.abstract AS dataset_abstract, dataset_dep.accessconstraints, dataset_dep.isopen, dataset_dep.termsofuse, f_collect_searchtext_dataset(dataset_dep.dataset_id) AS searchtext, dataset_dep.dataset_timestamp, dataset_dep.department, dataset_dep.mb_group_name, dataset_dep.mb_group_title, dataset_dep.mb_group_country, CASE WHEN load_count IS NULL THEN 0 ELSE load_count END as load_count, dataset_dep.mb_group_stateorprovince, f_collect_inspire_cat_dataset(dataset_dep.dataset_id) AS md_inspire_cats, f_collect_custom_cat_dataset(dataset_dep.dataset_id) AS md_custom_cats, f_collect_topic_cat_dataset(dataset_dep.dataset_id) AS md_topic_cats, dataset_dep.bbox AS the_geom, (((((st_xmin(dataset_dep.bbox::box3d)::text || ','::text) || st_ymin(dataset_dep.bbox::box3d)::text) || ','::text) || st_xmax(dataset_dep.bbox::box3d)::text) || ','::text) || st_ymax(dataset_dep.bbox::box3d)::text AS bbox, dataset_dep.preview_url, dataset_dep.fileidentifier, f_get_coupled_resources(dataset_dep.dataset_id) AS coupled_resources, dataset_dep.mb_group_logo_path, dataset_dep.timebegin::date, CASE WHEN update_frequency = 'continual' THEN now()::date WHEN update_frequency = 'daily' THEN now()::date WHEN update_frequency = 'weekly' THEN (now() - interval '7 day')::date WHEN update_frequency = 'fortnightly' THEN (now() - interval '14 day')::date WHEN update_frequency = 'monthly' THEN (now() -interval '1 month')::date WHEN update_frequency = 'quarterly' THEN (now() - interval '3 month')::date WHEN update_frequency = 'biannually' THEN (now() - interval '6 month')::date WHEN update_frequency = 'annually' THEN (now() - interval '12 month')::date ELSE dataset_dep.timeend::date END AS timeend
   FROM ( SELECT mb_metadata.the_geom AS bbox, mb_metadata.ref_system AS srs, mb_metadata.metadata_id AS dataset_id, mb_metadata.title, mb_metadata.abstract, mb_metadata.lastchanged AS dataset_timestamp, mb_metadata.tmp_reference_1 as timeBegin, mb_metadata.tmp_reference_2 as timeEnd, mb_metadata.uuid AS fileidentifier, mb_metadata.preview_image AS preview_url, mb_metadata.load_count as load_count, mb_metadata.fkey_mb_user_id, mb_metadata.constraints as accessconstraints,mb_metadata.update_frequency as update_frequency, f_getmd_tou(mb_metadata.metadata_id) AS termsofuse, f_tou_isopen(f_getmd_tou(mb_metadata.metadata_id)) AS isopen, user_dep.mb_group_id AS department, user_dep.mb_group_name, user_dep.mb_group_title, user_dep.mb_group_country, user_dep.mb_group_stateorprovince, user_dep.mb_group_logo_path
           FROM ( SELECT registrating_groups.fkey_mb_user_id AS mb_user_id, mb_group.mb_group_id, mb_group.mb_group_name, mb_group.mb_group_title, mb_group.mb_group_country, mb_group.mb_group_stateorprovince, mb_group.mb_group_logo_path
                   FROM registrating_groups, mb_group
                  WHERE registrating_groups.fkey_mb_group_id = mb_group.mb_group_id) user_dep, (SELECT mb_metadata.* ,metadata_load_count.load_count FROM mb_metadata LEFT JOIN metadata_load_count ON mb_metadata.metadata_id = metadata_load_count.fkey_metadata_id) AS mb_metadata
          WHERE user_dep.mb_user_id = mb_metadata.fkey_mb_user_id AND mb_metadata.the_geom IS NOT NULL) dataset_dep
  ORDER BY dataset_dep.dataset_id) AS datasets;

ALTER TABLE search_dataset_view
  OWNER TO :db_owner;

--new search wfs view with all featuretypes
DROP VIEW search_wfs_view;
CREATE OR REPLACE VIEW search_wfs_view AS

SELECT wfs_without_geom.wfs_id, wfs_without_geom.wfs_title, wfs_without_geom.wfs_abstract, wfs_without_geom.administrativearea, wfs_without_geom.country, wfs_without_geom.accessconstraints, wfs_without_geom.termsofuse, wfs_without_geom.isopen, wfs_without_geom.wfs_owner, wfs_without_geom.featuretype_id, wfs_without_geom.featuretype_srs, wfs_without_geom.featuretype_title, wfs_without_geom.featuretype_abstract, wfs_without_geom.searchtext, wfs_without_geom.element_type, wfs_without_geom.wfs_conf_id, wfs_without_geom.wfs_conf_abstract, wfs_without_geom.wfs_conf_description, wfs_without_geom.modultype, wfs_without_geom.wfs_timestamp, wfs_without_geom.department, wfs_without_geom.mb_group_name, wfs_without_geom.mb_group_logo_path, wfs_without_geom.wfs_network_access, wfs_without_geom.wfs_pricevolume, wfs_without_geom.wfs_proxylog, wfs_without_geom.featuretype_latlon_bbox, wfs_without_geom.featuretype_latlon_array, geometryfromtext(((((((((((((((((((('POLYGON(('::text || wfs_without_geom.featuretype_latlon_array[1]) || ' '::text) || wfs_without_geom.featuretype_latlon_array[2]) || ','::text) || wfs_without_geom.featuretype_latlon_array[1]) || ' '::text) || wfs_without_geom.featuretype_latlon_array[4]) || ','::text) || wfs_without_geom.featuretype_latlon_array[3]) || ' '::text) || wfs_without_geom.featuretype_latlon_array[4]) || ','::text) || wfs_without_geom.featuretype_latlon_array[3]) || ' '::text) || wfs_without_geom.featuretype_latlon_array[2]) || ','::text) || wfs_without_geom.featuretype_latlon_array[1]) || ' '::text) || wfs_without_geom.featuretype_latlon_array[2]) || '))'::text, 4326) AS the_geom, (((((wfs_without_geom.featuretype_latlon_array[1] || ','::text) || wfs_without_geom.featuretype_latlon_array[2]) || ','::text) || wfs_without_geom.featuretype_latlon_array[3]) || ','::text) || wfs_without_geom.featuretype_latlon_array[4] AS bbox
   FROM (


SELECT wfs_element.*, wfs_conf.wfs_conf_id, wfs_conf.wfs_conf_abstract, wfs_conf.wfs_conf_description, f_getwfsmodultype(wfs_conf.wfs_conf_id) AS modultype FROM (SELECT wfs_dep.wfs_id, wfs_dep.wfs_title, wfs_dep.wfs_abstract, wfs_dep.administrativearea, wfs_dep.country, wfs_dep.accessconstraints, wfs_dep.termsofuse, wfs_dep.isopen, wfs_dep.wfs_owner, wfs_featuretype.featuretype_id, wfs_featuretype.featuretype_srs, wfs_featuretype.featuretype_title, wfs_featuretype.featuretype_abstract, f_collect_searchtext_wfs(wfs_dep.wfs_id, wfs_featuretype.featuretype_id) AS searchtext, wfs_element.element_type, wfs_dep.wfs_timestamp, wfs_dep.department, wfs_dep.mb_group_name, wfs_dep.mb_group_logo_path, wfs_dep.wfs_network_access, wfs_dep.wfs_pricevolume, wfs_dep.wfs_proxylog, wfs_featuretype.featuretype_latlon_bbox,
                CASE
                    WHEN wfs_featuretype.featuretype_latlon_bbox::text = ''::text THEN string_to_array('-180,-90,180,90'::text, ','::text)
                    WHEN wfs_featuretype.featuretype_latlon_bbox IS NULL THEN string_to_array('-180,-90,180,90'::text, ','::text)
                    ELSE string_to_array(wfs_featuretype.featuretype_latlon_bbox::text, ','::text)
                END AS featuretype_latlon_array
           FROM ( SELECT wfs.wfs_id, wfs.wfs_title, wfs.wfs_abstract, wfs.administrativearea, wfs.country, wfs.accessconstraints, f_getwfs_tou(wfs.wfs_id) AS termsofuse, f_tou_isopen(f_getwfs_tou(wfs.wfs_id)) AS isopen, wfs.wfs_timestamp, wfs.wfs_owner, wfs.wfs_proxylog, wfs.wfs_network_access, wfs.wfs_pricevolume, user_dep.mb_group_id AS department, user_dep.mb_group_name, user_dep.mb_group_logo_path
                   FROM ( SELECT registrating_groups.fkey_mb_user_id AS mb_user_id, mb_group.mb_group_id, mb_group.mb_group_name, mb_group.mb_group_logo_path
                           FROM registrating_groups, mb_group
                          WHERE registrating_groups.fkey_mb_group_id = mb_group.mb_group_id) user_dep, wfs
                  WHERE user_dep.mb_user_id = wfs.wfs_owner) wfs_dep, wfs_featuretype, wfs_element
          WHERE wfs_featuretype.fkey_wfs_id = wfs_dep.wfs_id AND wfs_featuretype.featuretype_searchable = 1 AND wfs_element.element_type::text ~~ '%Type'::text AND wfs_featuretype.featuretype_id = wfs_element.fkey_featuretype_id
          ORDER BY wfs_featuretype.featuretype_id) AS wfs_element LEFT OUTER JOIN wfs_conf ON wfs_element.featuretype_id = wfs_conf.fkey_featuretype_id

) wfs_without_geom;


ALTER TABLE search_wfs_view
  OWNER TO :db_owner;




--new table to log invocation of mapbender clients by referrer

-- Table: external_api_log

-- DROP TABLE external_api_log;

CREATE TABLE external_api_log
(
  log_id serial NOT NULL,
  createdate timestamp without time zone,
  lastchanged timestamp without time zone NOT NULL DEFAULT now(),
  referrer text,
  api_type integer,
  fkey_wmc_serial_id integer,
  log_count bigint,
  CONSTRAINT wmc_keyword_fkey_wmc_serial_id_fkey FOREIGN KEY (fkey_wmc_serial_id)
      REFERENCES mb_user_wmc (wmc_serial_id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
)
WITH (
  OIDS=FALSE
);
ALTER TABLE external_api_log
  OWNER TO :db_owner;

-- Index: idx_external_api_log_referrer

-- DROP INDEX idx_external_api_log_referrer;

CREATE INDEX idx_external_api_log_referrer
  ON external_api_log
  USING btree
  (referrer);

-- Trigger: update_external_api_log_lastchanged on external_api_log

-- DROP TRIGGER update_external_api_log_lastchanged ON external_api_log;

CREATE TRIGGER update_external_api_log_lastchanged
  BEFORE UPDATE
  ON external_api_log
  FOR EACH ROW
  EXECUTE PROCEDURE update_lastchanged_column();

--create function to prepare searchtext for fulltext search

-- Function: f_collect_searchtext_dataset_ts(integer)

-- DROP FUNCTION f_collect_searchtext_dataset_ts(integer);

CREATE OR REPLACE FUNCTION f_collect_searchtext_dataset_ts(integer)
  RETURNS text AS
$BODY$
DECLARE
    p_dataset_id ALIAS FOR $1;

    r_keywords RECORD;
    l_result TEXT;
BEGIN
    l_result := '';
    l_result := l_result || (SELECT COALESCE(title, '') || ' ' || COALESCE(abstract, '') FROM mb_metadata WHERE metadata_id = p_dataset_id);
    FOR r_keywords IN SELECT DISTINCT keyword FROM
        (SELECT keyword FROM mb_metadata_keyword L JOIN keyword K ON (K.keyword_id = L.fkey_keyword_id AND L.fkey_metadata_id = p_dataset_id)
        ) AS __keywords__ LOOP
        l_result := l_result || ' ' || COALESCE(r_keywords.keyword, '');
    END LOOP;

    RETURN l_result;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION f_collect_searchtext_dataset_ts(integer)
  OWNER TO :db_owner;

--change metadata addon editor for wfs metadataeditor to central one
DELETE FROM gui_element WHERE fkey_gui_id = 'admin_wfs_metadata' AND e_id = 'mb_md_showMetadataAddonWfs';
DELETE FROM gui_element WHERE fkey_gui_id = 'admin_wfs_metadata' AND e_id = 'mb_metadata_xml_import';

INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('admin_wfs_metadata','mb_md_showMetadataAddon',2,1,'Show addon editor for metadata','Metadata Addon Editor','div','','',NULL ,NULL ,NULL ,NULL ,NULL ,'display:none;','','div','../plugins/mb_metadata_showMetadataAddon.js','','','jq_ui_dialog','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('admin_wfs_metadata', 'mb_md_showMetadataAddon', 'differentFromOriginalCss', '.differentFromOriginal{
background-color:#FFFACD;
}', 'css for class differentFromOriginal' ,'text/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('admin_wfs_metadata', 'mb_md_showMetadataAddon', 'inputs', '[
    {
        "method": "init",
        "title": "initialize",
        "linkedTo": [
            {
                "id": "mb_md_edit",
                "event": "showOriginalMetadata",
                "attr": "data"
            }
        ]
    },
    {
        "method": "initLayer",
        "title": "initialize",
        "linkedTo": [
            {
                "id": "mb_md_layer",
                "event": "showOriginalLayerMetadata",
                "attr": "data"
            }
        ]
    }
]', '' ,'var');

INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('admin_wfs_metadata','mb_metadata_xml_import',1,1,'','','div','','',NULL ,NULL ,NULL ,NULL ,NULL ,'','','div','../plugins/mb_metadata_xml_import.js','','','','');

DELETE FROM gui_element WHERE fkey_gui_id = 'admin_wfs_metadata' AND e_id = 'mb_metadata_gml_import';

INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('admin_wfs_metadata','mb_metadata_gml_import',1,1,'','','div','','',NULL ,NULL ,NULL ,NULL ,NULL ,'','','div','../plugins/mb_metadata_gml_import.js','','','','');


DROP VIEW search_dataset_view;

ALTER TABLE mb_metadata ALTER COLUMN ref_system TYPE VARCHAR(150);
CREATE OR REPLACE VIEW search_dataset_view AS

SELECT DISTINCT ON (metadata_id) * FROM ( SELECT dataset_dep.fkey_mb_user_id AS user_id, dataset_dep.dataset_id, dataset_dep.dataset_id AS metadata_id, dataset_dep.srs AS dataset_srs, dataset_dep.title, dataset_dep.abstract AS dataset_abstract, dataset_dep.accessconstraints, dataset_dep.isopen, dataset_dep.termsofuse, f_collect_searchtext_dataset(dataset_dep.dataset_id) AS searchtext, dataset_dep.dataset_timestamp, dataset_dep.department, dataset_dep.mb_group_name, dataset_dep.mb_group_title, dataset_dep.mb_group_country, CASE WHEN load_count IS NULL THEN 0 ELSE load_count END as load_count, dataset_dep.mb_group_stateorprovince, f_collect_inspire_cat_dataset(dataset_dep.dataset_id) AS md_inspire_cats, f_collect_custom_cat_dataset(dataset_dep.dataset_id) AS md_custom_cats, f_collect_topic_cat_dataset(dataset_dep.dataset_id) AS md_topic_cats, dataset_dep.bbox AS the_geom, (((((st_xmin(dataset_dep.bbox::box3d)::text || ','::text) || st_ymin(dataset_dep.bbox::box3d)::text) || ','::text) || st_xmax(dataset_dep.bbox::box3d)::text) || ','::text) || st_ymax(dataset_dep.bbox::box3d)::text AS bbox, dataset_dep.preview_url, dataset_dep.fileidentifier, f_get_coupled_resources(dataset_dep.dataset_id) AS coupled_resources, dataset_dep.mb_group_logo_path, dataset_dep.timebegin::date, CASE WHEN update_frequency = 'continual' THEN now()::date WHEN update_frequency = 'daily' THEN now()::date WHEN update_frequency = 'weekly' THEN (now() - interval '7 day')::date WHEN update_frequency = 'fortnightly' THEN (now() - interval '14 day')::date WHEN update_frequency = 'monthly' THEN (now() -interval '1 month')::date WHEN update_frequency = 'quarterly' THEN (now() - interval '3 month')::date WHEN update_frequency = 'biannually' THEN (now() - interval '6 month')::date WHEN update_frequency = 'annually' THEN (now() - interval '12 month')::date ELSE dataset_dep.timeend::date END AS timeend
   FROM ( SELECT mb_metadata.the_geom AS bbox, mb_metadata.ref_system AS srs, mb_metadata.metadata_id AS dataset_id, mb_metadata.title, mb_metadata.abstract, mb_metadata.lastchanged AS dataset_timestamp, mb_metadata.tmp_reference_1 as timeBegin, mb_metadata.tmp_reference_2 as timeEnd, mb_metadata.uuid AS fileidentifier, mb_metadata.preview_image AS preview_url, mb_metadata.load_count as load_count, mb_metadata.fkey_mb_user_id, mb_metadata.constraints as accessconstraints,mb_metadata.update_frequency as update_frequency, f_getmd_tou(mb_metadata.metadata_id) AS termsofuse, f_tou_isopen(f_getmd_tou(mb_metadata.metadata_id)) AS isopen, user_dep.mb_group_id AS department, user_dep.mb_group_name, user_dep.mb_group_title, user_dep.mb_group_country, user_dep.mb_group_stateorprovince, user_dep.mb_group_logo_path
           FROM ( SELECT registrating_groups.fkey_mb_user_id AS mb_user_id, mb_group.mb_group_id, mb_group.mb_group_name, mb_group.mb_group_title, mb_group.mb_group_country, mb_group.mb_group_stateorprovince, mb_group.mb_group_logo_path
                   FROM registrating_groups, mb_group
                  WHERE registrating_groups.fkey_mb_group_id = mb_group.mb_group_id) user_dep, (SELECT mb_metadata.* ,metadata_load_count.load_count FROM mb_metadata LEFT JOIN metadata_load_count ON mb_metadata.metadata_id = metadata_load_count.fkey_metadata_id) AS mb_metadata
          WHERE user_dep.mb_user_id = mb_metadata.fkey_mb_user_id AND mb_metadata.the_geom IS NOT NULL) dataset_dep
  ORDER BY dataset_dep.dataset_id) AS datasets;

ALTER TABLE search_dataset_view
  OWNER TO :db_owner;

--maintenance tools for catalogue

INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('PortalAdmin_DE','Start_Monitor_for_WMS',2,1,'Start Monitor for WMS','Start Monitor for WMS','a','','href = "../php/mod_catalogueMaintenance.php?sessionID&resourceType=wms&maintenanceFunction=monitor"',80,15,210,20,5,'font-family: Arial, Helvetica, sans-serif; font-size : 12px; text-decoration : none; color: #808080;','Start Monitor for WMS','a','','','','','http://www.mapbender.org/');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('PortalAdmin_DE', 'Start_Monitor_for_WMS', 'file css', '../css/administration_alloc.css', 'file css' ,'file/css');

INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('PortalAdmin_DE','Start_Monitor_for_WMS_collection',2,1,'Put existing divs in new div object. List the elements comma-separated under target, and make sure they have a title.','','div','','',0,160,200,30,NULL ,'','','div','../plugins/mb_div_collection.js','','Start_Monitor_for_WMS,Start_Monitor_for_WMS_icon','','');

INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('PortalAdmin_DE','Start_Monitor_for_WMS_icon',2,1,'icon','','img','../img/gnome/system-run.png','',0,0,NULL ,NULL ,2,'','','','','','','','');

INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('PortalAdmin_DE','reindexDATASET',2,1,'Reindex search DATASET','Reindex search DATASET','a','','href = "../php/mod_catalogueMaintenance.php?sessionID&resourceType=dataset&maintenanceFunction=reindex"',80,15,210,20,5,'font-family: Arial, Helvetica, sans-serif; font-size : 12px; text-decoration : none; color: #808080;','Reindex search DATASET','a','','','','','http://www.mapbender.org/');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('PortalAdmin_DE', 'reindexDATASET', 'file css', '../css/administration_alloc.css', 'file css' ,'file/css');

INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('PortalAdmin_DE','reindexDATASET_collection',2,1,'Put existing divs in new div object. List the elements comma-separated under target, and make sure they have a title.','','div','','',0,120,200,30,NULL ,'','','div','../plugins/mb_div_collection.js','','reindexDATASET,reindexDATASET_icon','','');

INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('PortalAdmin_DE','reindexDATASET_icon',2,1,'icon','','img','../img/gnome/system-run.png','',0,0,NULL ,NULL ,2,'','','','','','','','');

INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('PortalAdmin_DE','reindexWFS',2,1,'Reindex search WFS','Reindex search WFS','a','','href = "../php/mod_catalogueMaintenance.php?sessionID&resourceType=wfs&maintenanceFunction=reindex"',80,15,210,20,5,'font-family: Arial, Helvetica, sans-serif; font-size : 12px; text-decoration : none; color: #808080;','Reindex search WFS','a','','','','','http://www.mapbender.org/');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('PortalAdmin_DE', 'reindexWFS', 'file css', '../css/administration_alloc.css', 'file css' ,'file/css');

INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('PortalAdmin_DE','reindexWFS_collection',2,1,'Put existing divs in new div object. List the elements comma-separated under target, and make sure they have a title.','','div','','',0,80,200,30,NULL ,'','','div','../plugins/mb_div_collection.js','','reindexWFS,reindexWFS_icon','','');

INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('PortalAdmin_DE','reindexWFS_icon',2,1,'icon','','img','../img/gnome/system-run.png','',0,0,NULL ,NULL ,2,'','','','','','','','');

INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('PortalAdmin_DE','reindexWMS',2,1,'Reindex search WMS','Reindex search WMS','a','','href = "../php/mod_catalogueMaintenance.php?sessionID&resourceType=wms&maintenanceFunction=reindex"',80,15,210,20,5,'font-family: Arial, Helvetica, sans-serif; font-size : 12px; text-decoration : none; color: #808080;','Reindex search WMS','a','','','','','http://www.mapbender.org/');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('PortalAdmin_DE', 'reindexWMS', 'file css', '../css/administration_alloc.css', 'file css' ,'file/css');

INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('PortalAdmin_DE','reindexWMS_collection',2,1,'Put existing divs in new div object. List the elements comma-separated under target, and make sure they have a title.','','div','','',0,40,200,30,NULL ,'','','div','../plugins/mb_div_collection.js','','reindexWMS,reindexWMS_icon','','');

INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('PortalAdmin_DE','reindexWMS_icon',2,1,'icon','','img','../img/gnome/system-run.png','',0,0,NULL ,NULL ,2,'','','','','','','','');

UPDATE gui_element SET e_target = 'editMaintenance_collection, reindexWMS_collection,reindexWFS_collection,reindexDATASET_collection,Start_Monitor_for_WMS_collection' WHERE fkey_gui_id = 'PortalAdmin_DE' AND e_id = 'menu_maintenance';

--dirty fix for more than one entry in wfs_termsofuse - should not happen under normal circumstances
-- Function: f_getwfs_tou(integer)

-- DROP FUNCTION f_getwfs_tou(integer);

CREATE OR REPLACE FUNCTION f_getwfs_tou(integer)
  RETURNS integer AS
$BODY$
DECLARE
   wfs_tou int4;
BEGIN
wfs_tou := fkey_termsofuse_id from wfs_termsofuse where wfs_termsofuse.fkey_wfs_id=$1 LIMIT 1;
RETURN wfs_tou;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION f_getwfs_tou(integer)
  OWNER TO :db_owner;

-- fix for search featuretype view
UPDATE wfs_featuretype SET featuretype_latlon_bbox='-180,-90,180,90' WHERE featuretype_latlon_bbox = ',,,';

--View to pull keywords as suggestions for catalogue search
CREATE VIEW keyword_search_view AS SELECT keyword, to_tsvector('german', keyword) as keyword_ts, replace(replace(replace(replace(replace(replace(replace(UPPER(keyword),'Ä','AE'),'ß','SS'),'Ö','OE'),'Ü','UE'),'ä','AE'),'ü','UE'),'ö','OE') as keyword_upper FROM keyword;

-- Table: layer_dimension

-- DROP TABLE layer_dimension;

CREATE TABLE layer_dimension
(
  fkey_layer_id integer NOT NULL DEFAULT 0,
  name character varying(512) NOT NULL DEFAULT ''::character varying,
  units character varying(512) NOT NULL DEFAULT ''::character varying,
  unitSymbol character varying(512) DEFAULT ''::character varying,
  "default" character varying(512) DEFAULT ''::character varying,
  multipleValues character varying(512)  DEFAULT ''::character varying,
  nearestValue character varying(512) DEFAULT ''::character varying,
  current character varying(512) DEFAULT ''::character varying,
  extent text NOT NULL DEFAULT ''::character varying,
  inherited boolean NOT NULL DEFAULT FALSE,
  CONSTRAINT layer_dimension_ibfk_1 FOREIGN KEY (fkey_layer_id)
      REFERENCES layer (layer_id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
)
WITH (
  OIDS=FALSE
);
ALTER TABLE layer_dimension
  OWNER TO :db_owner;

DROP VIEW search_wfs_view;

ALTER TABLE wfs ALTER COLUMN accessconstraints TYPE text;

ALTER TABLE wfs ALTER COLUMN fees TYPE text;

-- View: search_wfs_view

-- DROP VIEW search_wfs_view;

CREATE OR REPLACE VIEW search_wfs_view AS
 SELECT wfs_without_geom.wfs_id, wfs_without_geom.wfs_title, wfs_without_geom.wfs_abstract, wfs_without_geom.administrativearea, wfs_without_geom.country, wfs_without_geom.accessconstraints, wfs_without_geom.termsofuse, wfs_without_geom.isopen, wfs_without_geom.wfs_owner, wfs_without_geom.featuretype_id, wfs_without_geom.featuretype_srs, wfs_without_geom.featuretype_title, wfs_without_geom.featuretype_abstract, wfs_without_geom.searchtext, wfs_without_geom.element_type, wfs_without_geom.wfs_conf_id, wfs_without_geom.wfs_conf_abstract, wfs_without_geom.wfs_conf_description, wfs_without_geom.modultype, wfs_without_geom.wfs_timestamp, wfs_without_geom.department, wfs_without_geom.mb_group_name, wfs_without_geom.mb_group_logo_path, wfs_without_geom.wfs_network_access, wfs_without_geom.wfs_pricevolume, wfs_without_geom.wfs_proxylog, wfs_without_geom.featuretype_latlon_bbox, wfs_without_geom.featuretype_latlon_array, geometryfromtext(((((((((((((((((((('POLYGON(('::text || wfs_without_geom.featuretype_latlon_array[1]) || ' '::text) || wfs_without_geom.featuretype_latlon_array[2]) || ','::text) || wfs_without_geom.featuretype_latlon_array[1]) || ' '::text) || wfs_without_geom.featuretype_latlon_array[4]) || ','::text) || wfs_without_geom.featuretype_latlon_array[3]) || ' '::text) || wfs_without_geom.featuretype_latlon_array[4]) || ','::text) || wfs_without_geom.featuretype_latlon_array[3]) || ' '::text) || wfs_without_geom.featuretype_latlon_array[2]) || ','::text) || wfs_without_geom.featuretype_latlon_array[1]) || ' '::text) || wfs_without_geom.featuretype_latlon_array[2]) || '))'::text, 4326) AS the_geom, (((((wfs_without_geom.featuretype_latlon_array[1] || ','::text) || wfs_without_geom.featuretype_latlon_array[2]) || ','::text) || wfs_without_geom.featuretype_latlon_array[3]) || ','::text) || wfs_without_geom.featuretype_latlon_array[4] AS bbox
   FROM ( SELECT wfs_dep.wfs_id, wfs_dep.wfs_title, wfs_dep.wfs_abstract, wfs_dep.administrativearea, wfs_dep.country, wfs_dep.accessconstraints, wfs_dep.termsofuse, wfs_dep.isopen, wfs_dep.wfs_owner, wfs_featuretype.featuretype_id, wfs_featuretype.featuretype_srs, wfs_featuretype.featuretype_title, wfs_featuretype.featuretype_abstract, f_collect_searchtext_wfs(wfs_dep.wfs_id, wfs_featuretype.featuretype_id) AS searchtext, wfs_element.element_type, wfs_conf.wfs_conf_id, wfs_conf.wfs_conf_abstract, wfs_conf.wfs_conf_description, f_getwfsmodultype(wfs_conf.wfs_conf_id) AS modultype, wfs_dep.wfs_timestamp, wfs_dep.department, wfs_dep.mb_group_name, wfs_dep.mb_group_logo_path, wfs_dep.wfs_network_access, wfs_dep.wfs_pricevolume, wfs_dep.wfs_proxylog, wfs_featuretype.featuretype_latlon_bbox,
                CASE
                    WHEN wfs_featuretype.featuretype_latlon_bbox::text = ''::text THEN string_to_array('-180,-90,180,90'::text, ','::text)
                    WHEN wfs_featuretype.featuretype_latlon_bbox IS NULL THEN string_to_array('-180,-90,180,90'::text, ','::text)
                    ELSE string_to_array(wfs_featuretype.featuretype_latlon_bbox::text, ','::text)
                END AS featuretype_latlon_array
           FROM ( SELECT wfs.wfs_id, wfs.wfs_title, wfs.wfs_abstract, wfs.administrativearea, wfs.country, wfs.accessconstraints, f_getwfs_tou(wfs.wfs_id) AS termsofuse, f_tou_isopen(f_getwfs_tou(wfs.wfs_id)) AS isopen, wfs.wfs_timestamp, wfs.wfs_owner, wfs.wfs_proxylog, wfs.wfs_network_access, wfs.wfs_pricevolume, user_dep.mb_group_id AS department, user_dep.mb_group_name, user_dep.mb_group_logo_path
                   FROM ( SELECT registrating_groups.fkey_mb_user_id AS mb_user_id, mb_group.mb_group_id, mb_group.mb_group_name, mb_group.mb_group_logo_path
                           FROM registrating_groups, mb_group
                          WHERE registrating_groups.fkey_mb_group_id = mb_group.mb_group_id) user_dep, wfs
                  WHERE user_dep.mb_user_id = wfs.wfs_owner) wfs_dep, wfs_featuretype, wfs_element, wfs_conf
          WHERE wfs_featuretype.fkey_wfs_id = wfs_dep.wfs_id AND wfs_featuretype.featuretype_searchable = 1 AND wfs_element.element_type::text ~~ '%Type'::text AND wfs_featuretype.featuretype_id = wfs_element.fkey_featuretype_id AND wfs_featuretype.featuretype_id = wfs_conf.fkey_featuretype_id
          ORDER BY wfs_featuretype.featuretype_id) wfs_without_geom;

ALTER TABLE search_wfs_view
  OWNER TO :db_owner;

ALTER TABLE layer_style ALTER COLUMN name TYPE VARCHAR(100);
ALTER TABLE gui_layer ALTER COLUMN gui_layer_style TYPE VARCHAR(100);

ALTER TABLE mb_group ADD COLUMN uuid UUID;
ALTER TABLE mb_metadata ADD COLUMN fkey_mb_group_id INTEGER;

-- simple function to exchange some layer information of old layers with new content - maybe usefull if service structure will change sometimes - the layer_id is persistent
-- Function: f_exchange_layer_info(integer, integer)

-- DROP FUNCTION f_exchange_layer_info(integer, integer);

CREATE OR REPLACE FUNCTION f_exchange_layer_info(integer, integer)
  RETURNS text AS
$BODY$DECLARE
    p_old_layer_id ALIAS FOR $1;
    p_new_layer_id ALIAS FOR $2;

BEGIN
    --attributes: fkey_wms_id, layer_pos, layer_parent, layer_name, layer_title, layer_queryable, layer_minscale, layer_maxscale, layer_dataurl, layer_metadataurl, layer_abstract
    -- which other technical tables should be adopted:
    -- layer_dimension, layer_epsg, layer_style
    UPDATE layer SET fkey_wms_id = l2.fkey_wms_id, layer_title = l2.layer_title, layer_pos = l2.layer_pos, layer_parent = l2.layer_parent, layer_name = l2.layer_name, layer_queryable = l2.layer_queryable, layer_minscale = l2. layer_minscale, layer_maxscale = l2.layer_maxscale, layer_dataurl = l2.layer_dataurl, layer_metadataurl = l2.layer_metadataurl, layer_abstract = l2.layer_abstract
    FROM layer AS l2
    WHERE layer.layer_id = p_old_layer_id and l2.layer_id = p_new_layer_id;
    DELETE FROM layer_dimension WHERE fkey_layer_id = p_old_layer_id;
    DELETE FROM layer_epsg WHERE fkey_layer_id = p_old_layer_id;
    DELETE FROM layer_style WHERE fkey_layer_id = p_old_layer_id;
    UPDATE layer_dimension SET fkey_layer_id = p_old_layer_id WHERE fkey_layer_id = p_new_layer_id;
    UPDATE layer_epsg SET fkey_layer_id = p_old_layer_id WHERE fkey_layer_id = p_new_layer_id;
    UPDATE layer_style SET fkey_layer_id = p_old_layer_id WHERE fkey_layer_id = p_new_layer_id;
    DELETE FROM layer WHERE layer_id = p_new_layer_id;

    RETURN 'Layer information exchanged!';
END;
$BODY$
  LANGUAGE plpgsql VOLATILE STRICT
  COST 100;
ALTER FUNCTION f_exchange_layer_info(integer, integer)
  OWNER TO :db_owner;
ALTER TABLE wms ADD COLUMN wms_bequeath_licence_info INTEGER;
ALTER TABLE wms ADD COLUMN wms_bequeath_contact_info INTEGER;
-- New column for metadata proxy - if some information from service should be inherited!
ALTER TABLE mb_metadata ADD COLUMN md_proxy BOOLEAN;


-- Loesche die abhängigen Sichten, um anschließend die Tabelle wfs_featuretype anpassen zu können
DROP VIEW search_wfs_view;
DROP VIEW wfs_service_metadata_new;
DROP VIEW wfs_service_metadata;

-- Änderung Tabellenspalte auf varchar(200)
alter table wfs_featuretype alter column featuretype_title TYPE varchar(200);


-- Erneutes anlegen der 3 zuvor gelöschten Sichten search_wfs_view,wfs_service_metadata_new,wfs_service_metadata
CREATE OR REPLACE VIEW search_wfs_view AS
 SELECT wfs_without_geom.wfs_id,
    wfs_without_geom.wfs_title,
    wfs_without_geom.wfs_abstract,
    wfs_without_geom.administrativearea,
    wfs_without_geom.country,
    wfs_without_geom.accessconstraints,
    wfs_without_geom.termsofuse,
    wfs_without_geom.isopen,
    wfs_without_geom.wfs_owner,
    wfs_without_geom.featuretype_id,
    wfs_without_geom.featuretype_srs,
    wfs_without_geom.featuretype_title,
    wfs_without_geom.featuretype_abstract,
    wfs_without_geom.searchtext,
    wfs_without_geom.element_type,
    wfs_without_geom.wfs_conf_id,
    wfs_without_geom.wfs_conf_abstract,
    wfs_without_geom.wfs_conf_description,
    wfs_without_geom.modultype,
    wfs_without_geom.wfs_timestamp,
    wfs_without_geom.department,
    wfs_without_geom.mb_group_name,
    wfs_without_geom.mb_group_logo_path,
    wfs_without_geom.wfs_network_access,
    wfs_without_geom.wfs_pricevolume,
    wfs_without_geom.wfs_proxylog,
    wfs_without_geom.featuretype_latlon_bbox,
    wfs_without_geom.featuretype_latlon_array,
    geometryfromtext(((((((((((((((((((('POLYGON(('::text || wfs_without_geom.featuretype_latlon_array[1]) || ' '::text) || wfs_without_geom.featuretype_latlon_array[2]) || ','::text) || wfs_without_geom.featuretype_latlon_array[1]) || ' '::text) || wfs_without_geom.featuretype_latlon_array[4]) || ','::text) || wfs_without_geom.featuretype_latlon_array[3]) || ' '::text) || wfs_without_geom.featuretype_latlon_array[4]) || ','::text) || wfs_without_geom.featuretype_latlon_array[3]) || ' '::text) || wfs_without_geom.featuretype_latlon_array[2]) || ','::text) || wfs_without_geom.featuretype_latlon_array[1]) || ' '::text) || wfs_without_geom.featuretype_latlon_array[2]) || '))'::text, 4326) AS the_geom,
    (((((wfs_without_geom.featuretype_latlon_array[1] || ','::text) || wfs_without_geom.featuretype_latlon_array[2]) || ','::text) || wfs_without_geom.featuretype_latlon_array[3]) || ','::text) || wfs_without_geom.featuretype_latlon_array[4] AS bbox
   FROM ( SELECT wfs_element.wfs_id,
            wfs_element.wfs_title,
            wfs_element.wfs_abstract,
            wfs_element.administrativearea,
            wfs_element.country,
            wfs_element.accessconstraints,
            wfs_element.termsofuse,
            wfs_element.isopen,
            wfs_element.wfs_owner,
            wfs_element.featuretype_id,
            wfs_element.featuretype_srs,
            wfs_element.featuretype_title,
            wfs_element.featuretype_abstract,
            wfs_element.searchtext,
            wfs_element.element_type,
            wfs_element.wfs_timestamp,
            wfs_element.department,
            wfs_element.mb_group_name,
            wfs_element.mb_group_logo_path,
            wfs_element.wfs_network_access,
            wfs_element.wfs_pricevolume,
            wfs_element.wfs_proxylog,
            wfs_element.featuretype_latlon_bbox,
            wfs_element.featuretype_latlon_array,
            wfs_conf.wfs_conf_id,
            wfs_conf.wfs_conf_abstract,
            wfs_conf.wfs_conf_description,
            f_getwfsmodultype(wfs_conf.wfs_conf_id) AS modultype
           FROM ( SELECT wfs_dep.wfs_id,
                    wfs_dep.wfs_title,
                    wfs_dep.wfs_abstract,
                    wfs_dep.administrativearea,
                    wfs_dep.country,
                    wfs_dep.accessconstraints,
                    wfs_dep.termsofuse,
                    wfs_dep.isopen,
                    wfs_dep.wfs_owner,
                    wfs_featuretype.featuretype_id,
                    wfs_featuretype.featuretype_srs,
                    wfs_featuretype.featuretype_title,
                    wfs_featuretype.featuretype_abstract,
                    f_collect_searchtext_wfs(wfs_dep.wfs_id, wfs_featuretype.featuretype_id) AS searchtext,
                    wfs_element_1.element_type,
                    wfs_dep.wfs_timestamp,
                    wfs_dep.department,
                    wfs_dep.mb_group_name,
                    wfs_dep.mb_group_logo_path,
                    wfs_dep.wfs_network_access,
                    wfs_dep.wfs_pricevolume,
                    wfs_dep.wfs_proxylog,
                    wfs_featuretype.featuretype_latlon_bbox,
                        CASE
                            WHEN wfs_featuretype.featuretype_latlon_bbox::text = ''::text THEN string_to_array('-180,-90,180,90'::text, ','::text)
                            WHEN wfs_featuretype.featuretype_latlon_bbox IS NULL THEN string_to_array('-180,-90,180,90'::text, ','::text)
                            ELSE string_to_array(wfs_featuretype.featuretype_latlon_bbox::text, ','::text)
                        END AS featuretype_latlon_array
                   FROM ( SELECT wfs.wfs_id,
                            wfs.wfs_title,
                            wfs.wfs_abstract,
                            wfs.administrativearea,
                            wfs.country,
                            wfs.accessconstraints,
                            f_getwfs_tou(wfs.wfs_id) AS termsofuse,
                            f_tou_isopen(f_getwfs_tou(wfs.wfs_id)) AS isopen,
                            wfs.wfs_timestamp,
                            wfs.wfs_owner,
                            wfs.wfs_proxylog,
                            wfs.wfs_network_access,
                            wfs.wfs_pricevolume,
                            user_dep.mb_group_id AS department,
                            user_dep.mb_group_name,
                            user_dep.mb_group_logo_path
                           FROM ( SELECT registrating_groups.fkey_mb_user_id AS mb_user_id,
                                    mb_group.mb_group_id,
                                    mb_group.mb_group_name,
                                    mb_group.mb_group_logo_path
                                   FROM registrating_groups,
                                    mb_group
                                  WHERE registrating_groups.fkey_mb_group_id = mb_group.mb_group_id) user_dep,
                            wfs
                          WHERE user_dep.mb_user_id = wfs.wfs_owner) wfs_dep,
                    wfs_featuretype,
                    wfs_element wfs_element_1
                  WHERE wfs_featuretype.fkey_wfs_id = wfs_dep.wfs_id AND wfs_featuretype.featuretype_searchable = 1 AND wfs_element_1.element_type::text ~~ '%Type'::text AND wfs_featuretype.featuretype_id = wfs_element_1.fkey_featuretype_id
                  ORDER BY wfs_featuretype.featuretype_id) wfs_element
             LEFT JOIN wfs_conf ON wfs_element.featuretype_id = wfs_conf.fkey_featuretype_id) wfs_without_geom;

ALTER TABLE search_wfs_view
  OWNER TO :db_owner;


CREATE OR REPLACE VIEW wfs_service_metadata_new AS
 SELECT wfs_dep.wfs_id,
    wfs_dep.wfs_title,
    wfs_dep.wfs_abstract,
    wfs_dep.administrativearea,
    wfs_dep.country,
    wfs_dep.accessconstraints,
    wfs_dep.termsofuse,
    wfs_dep.wfs_owner,
    wfs_featuretype.featuretype_id,
    wfs_featuretype.featuretype_srs,
    wfs_featuretype.featuretype_title,
    wfs_featuretype.featuretype_abstract,
    f_collect_searchtext_wfs(wfs_dep.wfs_id, wfs_featuretype.featuretype_id) AS searchtext,
    wfs_element.element_type,
    wfs_conf.wfs_conf_id,
    wfs_conf.wfs_conf_abstract,
    wfs_conf.wfs_conf_description,
    f_getwfsmodultype(wfs_conf.wfs_conf_id) AS modultype,
    wfs_dep.wfs_timestamp,
    wfs_dep.department,
    wfs_dep.mb_group_name
   FROM ( SELECT wfs.wfs_id,
            wfs.wfs_title,
            wfs.wfs_abstract,
            wfs.administrativearea,
            wfs.country,
            wfs.accessconstraints,
            f_getwfs_tou(wfs.wfs_id) AS termsofuse,
            wfs.wfs_timestamp,
            wfs.wfs_owner,
            user_dep.mb_group_description AS department,
            user_dep.mb_group_name
           FROM ( SELECT mb_user.mb_user_id,
                    mb_group.mb_group_description,
                    mb_group.mb_group_name
                   FROM mb_user,
                    mb_group
                  WHERE mb_user.mb_user_department::text <> ''::text AND mb_user.mb_user_department::text = mb_group.mb_group_description::text) user_dep,
            wfs
          WHERE user_dep.mb_user_id = wfs.wfs_owner) wfs_dep,
    wfs_featuretype,
    wfs_element,
    wfs_conf
  WHERE wfs_featuretype.fkey_wfs_id = wfs_dep.wfs_id AND wfs_featuretype.featuretype_searchable = 1 AND wfs_element.element_type::text ~~ '%Type'::text AND wfs_featuretype.featuretype_id = wfs_element.fkey_featuretype_id AND wfs_featuretype.featuretype_id = wfs_conf.fkey_featuretype_id
  ORDER BY wfs_featuretype.featuretype_id;

ALTER TABLE wfs_service_metadata_new
  OWNER TO :db_owner;

CREATE OR REPLACE VIEW wfs_service_metadata AS
 SELECT wfs.wfs_id,
    wfs.wfs_version,
    wfs.wfs_name,
    wfs.wfs_title,
    wfs.wfs_abstract,
    wfs.wfs_getcapabilities,
    wfs.wfs_describefeaturetype,
    wfs.wfs_getfeature,
    wfs.wfs_transaction,
    wfs.wfs_owsproxy,
    wfs.wfs_getcapabilities_doc,
    wfs.wfs_upload_url,
    wfs.fees,
    wfs.accessconstraints,
    wfs.individualname,
    wfs.positionname,
    wfs.providername,
    wfs.city,
    wfs.deliverypoint,
    wfs.postalcode,
    wfs.voice,
    wfs.facsimile,
    wfs.electronicmailaddress,
    wfs.wfs_mb_getcapabilities_doc,
    wfs.wfs_owner,
    wfs.wfs_timestamp,
    wfs.country,
    wfs.administrativearea,
    wfs_featuretype.fkey_wfs_id,
    wfs_featuretype.featuretype_id,
    wfs_featuretype.featuretype_name,
    wfs_featuretype.featuretype_title,
    wfs_featuretype.featuretype_srs,
    wfs_featuretype.featuretype_searchable,
    wfs_featuretype.featuretype_abstract,
    f_collect_searchtext_wfs(wfs.wfs_id, wfs_featuretype.featuretype_id) AS searchtext,
    mb_user.mb_user_id,
    mb_user.mb_user_department,
    mb_group.mb_group_description,
    mb_group.mb_group_name,
    wfs_conf.wfs_conf_id,
    wfs_conf.wfs_conf_abstract,
    wfs_conf.wfs_conf_description
   FROM wfs
     LEFT JOIN wfs_featuretype ON wfs.wfs_id = wfs_featuretype.fkey_wfs_id
     JOIN mb_user ON wfs.wfs_owner = mb_user.mb_user_id
     JOIN mb_group ON mb_user.mb_user_department::text = mb_group.mb_group_description::text
     LEFT JOIN wfs_conf ON wfs_featuretype.featuretype_id = wfs_conf.fkey_featuretype_id;

ALTER TABLE wfs_service_metadata
  OWNER TO :db_owner;

--New columns to sync metadata to arbitrary ckan instances
ALTER TABLE mb_group ADD COLUMN mb_group_ckan_uuid uuid;
ALTER TABLE mb_group ADD COLUMN mb_group_ckan_api_key uuid;

ALTER TABLE mb_metadata ADD COLUMN inspire_interoperability BOOLEAN DEFAULT false;
ALTER TABLE mb_metadata ADD COLUMN searchable BOOLEAN DEFAULT true;

--Alter view to react on searchable = true field
-- View: search_dataset_view

DROP VIEW search_dataset_view;

CREATE OR REPLACE VIEW search_dataset_view AS
 SELECT DISTINCT ON (datasets.metadata_id) datasets.user_id, datasets.dataset_id, datasets.metadata_id, datasets.dataset_srs, datasets.title, datasets.dataset_abstract, datasets.accessconstraints, datasets.isopen, datasets.termsofuse, datasets.searchtext, datasets.dataset_timestamp, datasets.department, datasets.mb_group_name, datasets.mb_group_title, datasets.mb_group_country, datasets.load_count, datasets.mb_group_stateorprovince, datasets.md_inspire_cats, datasets.md_custom_cats, datasets.md_topic_cats, datasets.the_geom, datasets.bbox, datasets.preview_url, datasets.fileidentifier, datasets.coupled_resources, datasets.mb_group_logo_path, datasets.timebegin, datasets.timeend
   FROM ( SELECT dataset_dep.fkey_mb_user_id AS user_id, dataset_dep.dataset_id, dataset_dep.dataset_id AS metadata_id, dataset_dep.srs AS dataset_srs, dataset_dep.title, dataset_dep.abstract AS dataset_abstract, dataset_dep.accessconstraints, dataset_dep.isopen, dataset_dep.termsofuse, f_collect_searchtext_dataset(dataset_dep.dataset_id) AS searchtext, dataset_dep.dataset_timestamp, dataset_dep.department, dataset_dep.mb_group_name, dataset_dep.mb_group_title, dataset_dep.mb_group_country,
                CASE
                    WHEN dataset_dep.load_count IS NULL THEN 0::bigint
                    ELSE dataset_dep.load_count
                END AS load_count, dataset_dep.mb_group_stateorprovince, f_collect_inspire_cat_dataset(dataset_dep.dataset_id) AS md_inspire_cats, f_collect_custom_cat_dataset(dataset_dep.dataset_id) AS md_custom_cats, f_collect_topic_cat_dataset(dataset_dep.dataset_id) AS md_topic_cats, dataset_dep.bbox AS the_geom, (((((st_xmin(dataset_dep.bbox::box3d)::text || ','::text) || st_ymin(dataset_dep.bbox::box3d)::text) || ','::text) || st_xmax(dataset_dep.bbox::box3d)::text) || ','::text) || st_ymax(dataset_dep.bbox::box3d)::text AS bbox, dataset_dep.preview_url, dataset_dep.fileidentifier, f_get_coupled_resources(dataset_dep.dataset_id) AS coupled_resources, dataset_dep.mb_group_logo_path, dataset_dep.timebegin::date AS timebegin,
                CASE
                    WHEN dataset_dep.update_frequency::text = 'continual'::text THEN now()::date
                    WHEN dataset_dep.update_frequency::text = 'daily'::text THEN now()::date
                    WHEN dataset_dep.update_frequency::text = 'weekly'::text THEN (now() - '7 days'::interval)::date
                    WHEN dataset_dep.update_frequency::text = 'fortnightly'::text THEN (now() - '14 days'::interval)::date
                    WHEN dataset_dep.update_frequency::text = 'monthly'::text THEN (now() - '1 mon'::interval)::date
                    WHEN dataset_dep.update_frequency::text = 'quarterly'::text THEN (now() - '3 mons'::interval)::date
                    WHEN dataset_dep.update_frequency::text = 'biannually'::text THEN (now() - '6 mons'::interval)::date
                    WHEN dataset_dep.update_frequency::text = 'annually'::text THEN (now() - '1 year'::interval)::date
                    ELSE dataset_dep.timeend::date
                END AS timeend
           FROM ( SELECT mb_metadata.the_geom AS bbox, mb_metadata.ref_system AS srs, mb_metadata.metadata_id AS dataset_id, mb_metadata.title, mb_metadata.abstract, mb_metadata.lastchanged AS dataset_timestamp, mb_metadata.tmp_reference_1 AS timebegin, mb_metadata.tmp_reference_2 AS timeend, mb_metadata.uuid AS fileidentifier, mb_metadata.preview_image AS preview_url, mb_metadata.load_count, mb_metadata.fkey_mb_user_id, mb_metadata.constraints AS accessconstraints, mb_metadata.update_frequency, f_getmd_tou(mb_metadata.metadata_id) AS termsofuse, f_tou_isopen(f_getmd_tou(mb_metadata.metadata_id)) AS isopen, user_dep.mb_group_id AS department, user_dep.mb_group_name, user_dep.mb_group_title, user_dep.mb_group_country, user_dep.mb_group_stateorprovince, user_dep.mb_group_logo_path
                   FROM ( SELECT registrating_groups.fkey_mb_user_id AS mb_user_id, mb_group.mb_group_id, mb_group.mb_group_name, mb_group.mb_group_title, mb_group.mb_group_country, mb_group.mb_group_stateorprovince, mb_group.mb_group_logo_path
                           FROM registrating_groups, mb_group
                          WHERE registrating_groups.fkey_mb_group_id = mb_group.mb_group_id) user_dep, ( SELECT mb_metadata.metadata_id, mb_metadata.uuid, mb_metadata.origin, mb_metadata.includeincaps, mb_metadata.schema, mb_metadata.createdate, mb_metadata.changedate, mb_metadata.lastchanged, mb_metadata.data, mb_metadata.link, mb_metadata.linktype, mb_metadata.md_format, mb_metadata.title, mb_metadata.abstract, mb_metadata.searchtext, mb_metadata.status, mb_metadata.type, mb_metadata.harvestresult, mb_metadata.harvestexception, mb_metadata.export2csw, mb_metadata.tmp_reference_1, mb_metadata.tmp_reference_2, mb_metadata.spatial_res_type, mb_metadata.spatial_res_value, mb_metadata.ref_system, mb_metadata.format, mb_metadata.inspire_charset, mb_metadata.inspire_top_consistence, mb_metadata.fkey_mb_user_id, mb_metadata.responsible_party, mb_metadata.individual_name, mb_metadata.visibility, mb_metadata.locked, mb_metadata.copyof, mb_metadata.constraints, mb_metadata.fees, mb_metadata.classification, mb_metadata.browse_graphic, mb_metadata.inspire_conformance, mb_metadata.preview_image, mb_metadata.the_geom, mb_metadata.lineage, mb_metadata.datasetid, mb_metadata.randomid, mb_metadata.update_frequency, mb_metadata.datasetid_codespace, mb_metadata.bounding_geom, mb_metadata.inspire_whole_area, mb_metadata.inspire_actual_coverage, mb_metadata.datalinks, mb_metadata.inspire_download, mb_metadata.transfer_size, mb_metadata.md_license_source_note, mb_metadata.responsible_party_name, mb_metadata.responsible_party_email, mb_metadata.searchable, metadata_load_count.load_count
                           FROM mb_metadata
                      LEFT JOIN metadata_load_count ON mb_metadata.metadata_id = metadata_load_count.fkey_metadata_id) mb_metadata
                  WHERE user_dep.mb_user_id = mb_metadata.fkey_mb_user_id AND mb_metadata.the_geom IS NOT NULL AND mb_metadata.searchable IS TRUE) dataset_dep
          ORDER BY dataset_dep.dataset_id) datasets;

ALTER TABLE search_dataset_view
  OWNER TO :db_owner;

UPDATE mb_metadata SET searchable = TRUE WHERE searchable IS NULL;

--alter admin menu css to make editors mor usefull
UPDATE gui_element_vars SET var_value='body{ background-color: #ffffff; margin: 5 5 5 5 }
* {font-family: Verdana;font-size: 96%;box-sizing: border-box;}
#ui-datepicker-div {display: none;}
div.ui-layout-content {padding:0px; !important}
div.ui-tabs-panel{padding:0 !important;margin:0 !important;}
label {width: 40%;float: left;}
label>input {width:190px}
p {clear: both;display: table;width: 100%;}
input {font-weight: bold;vertical-align: top;width: 50%;}
input[type=''checkbox''],input[type=''radio''],input[type=''button''],input[type=''submit''] {width:auto;height:auto;}
input[type=''button''],input[type=''submit'']{margin:10px}
input[disabled]{background-color:lightgrey;color:grey;cursor:no-drop;}
input[readonly]{background-color:lightgrey;color:grey;cursor:no-drop;}
fieldset {margin-top: 10px;}
.dataTables_wrapper{min-height:unset;}
.metadata_span {padding-right:0;vertical-align: top;}
.help-dialog, .original-metadata-wms, .original-metadata-layer, .clickable, .original-metadata-wfs, .original-metadata-featuretype {cursor: pointer;}
.metadata_img, .help-dialog, .original-metadata-wms, .original-metadata-layer, .original-metadata-wfs, .original-metadata-featuretype {width: 25px;height: 25px;vertical-align: middle;}
.metadata_selectbox {height:66px;width:250px;vertical-align:top;}
.label_classification {width: 150px;height: 40px;float: left;}
div#choose {float: left;width: 30%;height:100%;position:relative;overflow:hidden;}
div#choose:hover{overflow:initial !important;background-color:white;display:inline-block;width:unset;min-width:30%;}
div#layer {margin-left: 31%;width: 69%;}
div#preview {max-width:305px;float: left;}
div#classification {float: left;max-width: 305px;}
div#buttons {float: left;}
div#selectbox {margin-left: 280px;padding-top: 60px;}' WHERE var_name='css_class_bg' and fkey_gui_id LIKE 'admin%';

ALTER TABLE wfs_featuretype ADD COLUMN featuretype_schema text;
ALTER TABLE wfs_featuretype ADD COLUMN featuretype_schema_problem boolean;

-- solve multiple results for wfs_conf - fixing bug!
-- View: search_wfs_view

DROP VIEW search_wfs_view;

CREATE OR REPLACE VIEW search_wfs_view AS
 SELECT wfs_without_geom.wfs_id, wfs_without_geom.wfs_title, wfs_without_geom.wfs_abstract, wfs_without_geom.administrativearea, wfs_without_geom.country, wfs_without_geom.accessconstraints, wfs_without_geom.termsofuse, wfs_without_geom.isopen, wfs_without_geom.wfs_owner, wfs_without_geom.featuretype_id, wfs_without_geom.featuretype_srs, wfs_without_geom.featuretype_title, wfs_without_geom.featuretype_abstract, wfs_without_geom.searchtext, wfs_without_geom.element_type, wfs_without_geom.wfs_conf_id, wfs_without_geom.wfs_conf_abstract, wfs_without_geom.wfs_conf_description, wfs_without_geom.modultype, wfs_without_geom.wfs_timestamp, wfs_without_geom.department, wfs_without_geom.mb_group_name, wfs_without_geom.mb_group_logo_path, wfs_without_geom.wfs_network_access, wfs_without_geom.wfs_pricevolume, wfs_without_geom.wfs_proxylog, wfs_without_geom.featuretype_latlon_bbox, wfs_without_geom.featuretype_latlon_array, geometryfromtext(((((((((((((((((((('POLYGON(('::text || wfs_without_geom.featuretype_latlon_array[1]) || ' '::text) || wfs_without_geom.featuretype_latlon_array[2]) || ','::text) || wfs_without_geom.featuretype_latlon_array[1]) || ' '::text) || wfs_without_geom.featuretype_latlon_array[4]) || ','::text) || wfs_without_geom.featuretype_latlon_array[3]) || ' '::text) || wfs_without_geom.featuretype_latlon_array[4]) || ','::text) || wfs_without_geom.featuretype_latlon_array[3]) || ' '::text) || wfs_without_geom.featuretype_latlon_array[2]) || ','::text) || wfs_without_geom.featuretype_latlon_array[1]) || ' '::text) || wfs_without_geom.featuretype_latlon_array[2]) || '))'::text, 4326) AS the_geom, (((((wfs_without_geom.featuretype_latlon_array[1] || ','::text) || wfs_without_geom.featuretype_latlon_array[2]) || ','::text) || wfs_without_geom.featuretype_latlon_array[3]) || ','::text) || wfs_without_geom.featuretype_latlon_array[4] AS bbox
   FROM ( SELECT DISTINCT wfs_featuretype.featuretype_id, wfs_dep.wfs_id, wfs_dep.wfs_title, wfs_dep.wfs_abstract, wfs_dep.administrativearea, wfs_dep.country, wfs_dep.accessconstraints, wfs_dep.termsofuse, wfs_dep.isopen, wfs_dep.wfs_owner, wfs_featuretype.featuretype_srs, wfs_featuretype.featuretype_title, wfs_featuretype.featuretype_abstract, f_collect_searchtext_wfs(wfs_dep.wfs_id, wfs_featuretype.featuretype_id) AS searchtext, wfs_element.element_type, wfs_conf.wfs_conf_id, wfs_conf.wfs_conf_abstract, wfs_conf.wfs_conf_description, f_getwfsmodultype(wfs_conf.wfs_conf_id) AS modultype, wfs_dep.wfs_timestamp, wfs_dep.department, wfs_dep.mb_group_name, wfs_dep.mb_group_logo_path, wfs_dep.wfs_network_access, wfs_dep.wfs_pricevolume, wfs_dep.wfs_proxylog, wfs_featuretype.featuretype_latlon_bbox,
                CASE
                    WHEN wfs_featuretype.featuretype_latlon_bbox::text = ''::text THEN string_to_array('-180,-90,180,90'::text, ','::text)
                    WHEN wfs_featuretype.featuretype_latlon_bbox IS NULL THEN string_to_array('-180,-90,180,90'::text, ','::text)
                    ELSE string_to_array(wfs_featuretype.featuretype_latlon_bbox::text, ','::text)
                END AS featuretype_latlon_array
           FROM ( SELECT wfs.wfs_id, wfs.wfs_title, wfs.wfs_abstract, wfs.administrativearea, wfs.country, wfs.accessconstraints, f_getwfs_tou(wfs.wfs_id) AS termsofuse, f_tou_isopen(f_getwfs_tou(wfs.wfs_id)) AS isopen, wfs.wfs_timestamp, wfs.wfs_owner, wfs.wfs_proxylog, wfs.wfs_network_access, wfs.wfs_pricevolume, user_dep.mb_group_id AS department, user_dep.mb_group_name, user_dep.mb_group_logo_path
                   FROM ( SELECT registrating_groups.fkey_mb_user_id AS mb_user_id, mb_group.mb_group_id, mb_group.mb_group_name, mb_group.mb_group_logo_path
                           FROM registrating_groups, mb_group
                          WHERE registrating_groups.fkey_mb_group_id = mb_group.mb_group_id) user_dep, wfs
                  WHERE user_dep.mb_user_id = wfs.wfs_owner) wfs_dep, wfs_featuretype, wfs_element, wfs_conf
          WHERE wfs_featuretype.fkey_wfs_id = wfs_dep.wfs_id AND wfs_featuretype.featuretype_searchable = 1 AND wfs_element.element_type::text ~~ '%Type'::text AND wfs_featuretype.featuretype_id = wfs_element.fkey_featuretype_id AND wfs_featuretype.featuretype_id = wfs_conf.fkey_featuretype_id
          ORDER BY wfs_featuretype.featuretype_id) wfs_without_geom;

ALTER TABLE search_wfs_view
  OWNER TO :db_owner;

-- View: groups_for_publishing

DROP VIEW groups_for_publishing;

CREATE OR REPLACE VIEW groups_for_publishing AS

SELECT mb_group_id AS fkey_mb_group_id, mb_group_name, mb_group_title, mb_group_country, mb_group_stateorprovince, mb_group_logo_path, mb_group_email FROM mb_group WHERE mb_group_id IN (

SELECT DISTINCT f.fkey_mb_group_id
   FROM mb_user_mb_group f, mb_user_mb_group s
  WHERE f.mb_user_mb_group_type IN (2,3)  AND s.fkey_mb_group_id = :group_id AND f.fkey_mb_user_id = s.fkey_mb_user_id

);

ALTER TABLE groups_for_publishing
  OWNER TO :db_owner;

-- View: users_for_publishing

DROP VIEW users_for_publishing;

CREATE OR REPLACE VIEW users_for_publishing AS

SELECT DISTINCT f.fkey_mb_user_id, f.fkey_mb_group_id AS primary_group_id
   FROM mb_user_mb_group f, mb_user_mb_group s
  WHERE f.mb_user_mb_group_type =2 AND s.fkey_mb_group_id = :group_id AND f.fkey_mb_user_id = s.fkey_mb_user_id
  ORDER BY f.fkey_mb_user_id;

ALTER TABLE users_for_publishing
  OWNER TO :db_owner;

--f_get_responsible_organization(integer, integer)

-- DROP FUNCTION f_get_responsible_organization(integer, integer);

CREATE OR REPLACE FUNCTION f_get_responsible_organization(i_user_id integer, i_group_id integer)
  RETURNS integer AS
$BODY$DECLARE
  --i_user_id ALIAS FOR $1;
  --i_group_id ALIAS FOR $2;
  -- give i_group_id in form: coalesce(i_group_id,0), because otherwise NULL values will not be interpreted!
  -- select f_get_responsible_organization(1,coalesce(21,0));
  i_resp_orga_id INTEGER;

BEGIN

RAISE NOTICE 'group_id = %', i_group_id;

IF i_group_id IS NULL OR i_group_id <= 0 THEN
    i_resp_orga_id := fkey_mb_group_id FROM mb_user_mb_group WHERE mb_user_mb_group_type = 2 AND fkey_mb_user_id = i_user_id LIMIT 1;
ELSE
    i_resp_orga_id = i_group_id;
END IF;


RETURN i_resp_orga_id;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE STRICT
  COST 100;
ALTER FUNCTION f_get_responsible_organization(integer, integer)
  OWNER TO :db_owner;

-- Function: f_get_geometry_type(integer)

-- DROP FUNCTION f_get_geometry_type(integer);

CREATE OR REPLACE FUNCTION f_get_geometry_type(integer)
  RETURNS text AS
$BODY$DECLARE
  i_featuretype_id ALIAS FOR $1;
  geometry_type TEXT;

BEGIN

geometry_type := element_type FROM wfs_element WHERE fkey_featuretype_id = i_featuretype_id AND element_type in ('GeometryPropertyType','MultiPolygonPropertyType','GeometryAssociationType','PointPropertyType','gml:MultiSurfacePropertyType','MultiSurfacePropertyType','MultiCurvePropertyType','MultiPointPropertyType','MultiLineStringPropertyType','LineStringPropertyType')  LIMIT 1;

RETURN geometry_type;

END;

$BODY$
  LANGUAGE plpgsql VOLATILE STRICT
  COST 100;
ALTER FUNCTION f_get_geometry_type(integer)
  OWNER TO :db_owner;

-- new view for wfs search

-- View: search_wfs_view_2

DROP VIEW search_wfs_view;

CREATE OR REPLACE VIEW search_wfs_view AS

SELECT wfs_new.*, isopen FROM (SELECT wfs_table.*, wfs_termsofuse.fkey_termsofuse_id FROM
(SELECT wfs_info.*, mb_group.mb_group_id as department, mb_group.mb_group_name, mb_group_logo_path  FROM (SELECT featuretype.*, geometryfromtext(((((((((((((((((((('POLYGON(('::text || featuretype.featuretype_latlon_array[1]) || ' '::text) || featuretype.featuretype_latlon_array[2]) || ','::text) || featuretype.featuretype_latlon_array[1]) || ' '::text) || featuretype.featuretype_latlon_array[4]) || ','::text) || featuretype.featuretype_latlon_array[3]) || ' '::text) || featuretype.featuretype_latlon_array[4]) || ','::text) || featuretype.featuretype_latlon_array[3]) || ' '::text) || featuretype.featuretype_latlon_array[2]) || ','::text) || featuretype.featuretype_latlon_array[1]) || ' '::text) || featuretype.featuretype_latlon_array[2]) || '))'::text, 4326) AS the_geom, (((((featuretype.featuretype_latlon_array[1] || ','::text) || featuretype.featuretype_latlon_array[2]) || ','::text) || featuretype.featuretype_latlon_array[3]) || ','::text) || featuretype.featuretype_latlon_array[4] AS bbox, f_collect_searchtext_wfs(wfs_id, featuretype.featuretype_id) AS searchtext, wfs_conf_id, wfs_conf_abstract, wfs_conf_description, wfs_conf_type as modultype FROM

(SELECT DISTINCT featuretype_id, featuretype_srs, featuretype_title, featuretype_abstract,  featuretype_latlon_bbox, f_get_geometry_type(featuretype_id) AS element_type, CASE
                    WHEN wfs_featuretype.featuretype_latlon_bbox::text = ''::text THEN string_to_array('-180,-90,180,90'::text, ','::text)
                    WHEN wfs_featuretype.featuretype_latlon_bbox IS NULL THEN string_to_array('-180,-90,180,90'::text, ','::text)
                    ELSE string_to_array(wfs_featuretype.featuretype_latlon_bbox::text, ','::text)
                END AS featuretype_latlon_array, wfs.*  FROM

(SELECT DISTINCT wfs_id, wfs_title, wfs_abstract, wfs_timestamp_create, wfs_timestamp, wfs_network_access, wfs_pricevolume, wfs_proxylog, wfs_owner, country, administrativearea, accessconstraints, fkey_mb_group_id, f_get_responsible_organization(wfs_owner, COALESCE(fkey_mb_group_id, 0)) AS orga_id

FROM wfs WHERE wfs_owner IN (SELECT fkey_mb_user_id FROM users_for_publishing) ORDER BY wfs_id) AS wfs

LEFT JOIN wfs_featuretype ON wfs.wfs_id = wfs_featuretype.fkey_wfs_id WHERE wfs_featuretype.featuretype_searchable = 1) AS featuretype

LEFT JOIN wfs_conf ON featuretype.featuretype_id = wfs_conf.fkey_featuretype_id) AS wfs_info LEFT JOIN mb_group ON wfs_info.orga_id = mb_group.mb_group_id) AS wfs_table LEFT JOIN wfs_termsofuse ON wfs_table.wfs_id = wfs_termsofuse.fkey_wfs_id) AS wfs_new LEFT JOIN termsofuse ON wfs_new.fkey_termsofuse_id = termsofuse.termsofuse_id

 WHERE element_type IS NOT NULL;

ALTER TABLE search_wfs_view
  OWNER TO :db_owner;

-- Function: f_collect_searchtext_dataset(integer)

-- DROP FUNCTION f_collect_searchtext_dataset(integer);

CREATE OR REPLACE FUNCTION f_collect_searchtext_dataset(integer)
  RETURNS text AS
$BODY$
DECLARE
    p_dataset_id ALIAS FOR $1;

    r_keywords RECORD;
    l_result TEXT;
BEGIN
    l_result := '';
    l_result := l_result || (SELECT COALESCE(title, '') || ' ' || COALESCE(abstract, '') || ' ' || COALESCE(metadata_id::text, '')  || ' ' || COALESCE(uuid, '') FROM mb_metadata WHERE metadata_id = p_dataset_id);
    FOR r_keywords IN SELECT DISTINCT keyword FROM
        (SELECT keyword FROM mb_metadata_keyword L JOIN keyword K ON (K.keyword_id = L.fkey_keyword_id AND L.fkey_metadata_id = p_dataset_id)
        ) AS __keywords__ LOOP
        l_result := l_result || ' ' || COALESCE(r_keywords.keyword, '');
    END LOOP;
   l_result := UPPER(l_result);
   l_result := replace(replace(replace(replace(replace(replace(replace(l_result,'Ä','AE'),'ß','SS'),'Ö','OE'),'Ü','UE'),'ä','AE'),'ü','UE'),'ö','OE');

    RETURN l_result;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION f_collect_searchtext_dataset(integer)
  OWNER TO :db_owner;

--new views to make search  views somewhat easier

-- View: groups_for_publishing

DROP VIEW groups_for_publishing CASCADE;

CREATE OR REPLACE VIEW groups_for_publishing AS
 SELECT mb_group.mb_group_id AS fkey_mb_group_id, mb_group.*
   FROM mb_group
  WHERE (mb_group.mb_group_id IN ( SELECT DISTINCT f.fkey_mb_group_id
           FROM mb_user_mb_group f, mb_user_mb_group s
          WHERE (f.mb_user_mb_group_type = ANY (ARRAY[2, 3])) AND s.fkey_mb_group_id = :group_id AND f.fkey_mb_user_id = s.fkey_mb_user_id));

ALTER TABLE groups_for_publishing
  OWNER TO :db_owner;

--View: users_for_publishing

DROP VIEW users_for_publishing CASCADE;

CREATE OR REPLACE VIEW users_for_publishing AS
 SELECT DISTINCT f.fkey_mb_user_id, f.fkey_mb_group_id AS primary_group_id
   FROM mb_user_mb_group f, mb_user_mb_group s
  WHERE f.mb_user_mb_group_type = 2 AND s.fkey_mb_group_id = :group_id AND f.fkey_mb_user_id = s.fkey_mb_user_id
  ORDER BY f.fkey_mb_user_id;

ALTER TABLE users_for_publishing
  OWNER TO :db_owner;

-- View: search_wms_view

DROP VIEW IF EXISTS search_wms_view;

CREATE OR REPLACE VIEW search_wms_view AS
 SELECT DISTINCT ON (wms_unref.layer_id) wms_unref.wms_id, wms_unref.availability, wms_unref.status, wms_unref.wms_title, wms_unref.wms_abstract, wms_unref.stateorprovince, wms_unref.country, wms_unref.accessconstraints, wms_unref.termsofuse, wms_unref.isopen, wms_unref.wms_owner, wms_unref.layer_id, wms_unref.epsg, wms_unref.layer_title, wms_unref.layer_abstract, wms_unref.layer_name, wms_unref.layer_parent, wms_unref.layer_pos, wms_unref.layer_queryable, wms_unref.export2csw, wms_unref.load_count, wms_unref.searchtext, wms_unref.wms_timestamp, wms_unref.department, wms_unref.mb_group_name, f_collect_custom_cat_layer(wms_unref.layer_id) AS md_custom_cats, f_collect_inspire_cat_layer(wms_unref.layer_id) AS md_inspire_cats, f_collect_topic_cat_layer(wms_unref.layer_id) AS md_topic_cats, geometryfromtext(((((((((((((((((((('POLYGON(('::text || layer_epsg.minx::text) || ' '::text) || layer_epsg.miny::text) || ','::text) || layer_epsg.minx::text) || ' '::text) || layer_epsg.maxy::text) || ','::text) || layer_epsg.maxx::text) || ' '::text) || layer_epsg.maxy::text) || ','::text) || layer_epsg.maxx::text) || ' '::text) || layer_epsg.miny::text) || ','::text) || layer_epsg.minx::text) || ' '::text) || layer_epsg.miny::text) || '))'::text, 4326) AS the_geom, (((((layer_epsg.minx::text || ','::text) || layer_epsg.miny::text) || ','::text) || layer_epsg.maxx::text) || ','::text) || layer_epsg.maxy::text AS bbox, wms_unref.wms_proxylog, wms_unref.wms_network_access, wms_unref.wms_pricevolume, wms_unref.mb_group_logo_path
   FROM ( SELECT wms_uncat.wms_id, wms_uncat.availability, wms_uncat.status, wms_uncat.wms_title, wms_uncat.wms_abstract, wms_uncat.stateorprovince, wms_uncat.country, wms_uncat.accessconstraints, wms_uncat.termsofuse, wms_uncat.isopen, wms_uncat.wms_owner, wms_uncat.layer_id, wms_uncat.epsg, wms_uncat.layer_title, wms_uncat.layer_abstract, wms_uncat.layer_name, wms_uncat.layer_parent, wms_uncat.layer_pos, wms_uncat.layer_queryable, wms_uncat.export2csw, wms_uncat.load_count, wms_uncat.searchtext, wms_uncat.wms_timestamp, wms_uncat.department, wms_uncat.mb_group_name, wms_uncat.wms_proxylog, wms_uncat.wms_network_access, wms_uncat.wms_pricevolume, wms_uncat.mb_group_logo_path
           FROM ( SELECT wms_dep.wms_id, wms_dep.availability, wms_dep.status, wms_dep.wms_title, wms_dep.wms_abstract, wms_dep.stateorprovince, wms_dep.country, wms_dep.accessconstraints, wms_dep.termsofuse, wms_dep.isopen, wms_dep.wms_owner, layer.layer_id, f_collect_epsg(layer.layer_id) AS epsg, layer.layer_title, layer.layer_abstract, layer.layer_name, layer.layer_parent, layer.layer_pos, layer.layer_queryable, layer.export2csw, f_layer_load_count(layer.layer_id) AS load_count, f_collect_searchtext(wms_dep.wms_id, layer.layer_id) AS searchtext, wms_dep.wms_timestamp, wms_dep.department, wms_dep.mb_group_name, wms_dep.wms_proxylog, wms_dep.wms_network_access, wms_dep.wms_pricevolume, wms_dep.mb_group_logo_path
                   FROM (
 --adoption to pull two different kinds of wms metadata, based on mapbenders role system
SELECT wms.wms_id, wms.wms_title, wms.wms_abstract, wms.stateorprovince, wms.country, mb_wms_availability.availability, mb_wms_availability.last_status AS status, wms.accessconstraints, f_getwms_tou(wms.wms_id) AS termsofuse, f_tou_isopen(f_getwms_tou(wms.wms_id)) AS isopen, wms.wms_timestamp, wms.wms_owner, wms.wms_proxylog, wms.wms_network_access, wms.wms_pricevolume, publishing_registrating_authorities.mb_group_id AS department, publishing_registrating_authorities.mb_group_id AS fkey_mb_group_id, wms.fkey_mb_group_id AS wms_department, publishing_registrating_authorities.mb_group_name, publishing_registrating_authorities.mb_group_logo_path
                           FROM groups_for_publishing AS publishing_registrating_authorities, wms, mb_wms_availability
                          WHERE wms.fkey_mb_group_id = publishing_registrating_authorities.mb_group_id AND wms.wms_id = mb_wms_availability.fkey_wms_id

UNION ALL

--now pull the resources for whith the primary group of the registrating user
SELECT wms.wms_id, wms.wms_title, wms.wms_abstract, wms.stateorprovince, wms.country, mb_wms_availability.availability, mb_wms_availability.last_status AS status, wms.accessconstraints, f_getwms_tou(wms.wms_id) AS termsofuse, f_tou_isopen(f_getwms_tou(wms.wms_id)) AS isopen, wms.wms_timestamp, wms.wms_owner, wms.wms_proxylog, wms.wms_network_access, wms.wms_pricevolume, user_dep.fkey_mb_group_id AS department, user_dep.fkey_mb_group_id, wms.fkey_mb_group_id AS wms_department, user_dep.mb_group_name, user_dep.mb_group_logo_path
                           FROM (

SELECT publishing_registrating_authorities.*,  users_for_publishing.fkey_mb_user_id FROM groups_for_publishing AS publishing_registrating_authorities, users_for_publishing WHERE users_for_publishing.primary_group_id = publishing_registrating_authorities.fkey_mb_group_id) user_dep,

 wms, mb_wms_availability
                          WHERE (wms.fkey_mb_group_id IS null OR wms.fkey_mb_group_id = 0) AND wms.wms_owner = user_dep.fkey_mb_user_id AND
 wms.wms_id = mb_wms_availability.fkey_wms_id) wms_dep, layer
                  WHERE layer.fkey_wms_id = wms_dep.wms_id AND layer.layer_searchable = 1) wms_uncat) wms_unref, layer_epsg
  WHERE layer_epsg.epsg::text = 'EPSG:4326'::text AND wms_unref.layer_id = layer_epsg.fkey_layer_id
  ORDER BY wms_unref.layer_id;

ALTER TABLE search_wms_view
  OWNER TO :db_owner;

-- View: search_dataset_view

DROP VIEW IF EXISTS search_dataset_view;

CREATE OR REPLACE VIEW search_dataset_view AS
 SELECT DISTINCT ON (datasets.metadata_id) datasets.user_id, datasets.dataset_id, datasets.metadata_id, datasets.dataset_srs, datasets.title, datasets.dataset_abstract, datasets.accessconstraints, datasets.isopen, datasets.termsofuse, datasets.searchtext, datasets.dataset_timestamp, datasets.department, datasets.mb_group_name, datasets.mb_group_title, datasets.mb_group_country, datasets.load_count, datasets.mb_group_stateorprovince, datasets.md_inspire_cats, datasets.md_custom_cats, datasets.md_topic_cats, datasets.the_geom, datasets.bbox, datasets.preview_url, datasets.fileidentifier, datasets.coupled_resources, datasets.mb_group_logo_path, datasets.timebegin, datasets.timeend
   FROM ( SELECT dataset_dep.fkey_mb_user_id AS user_id, dataset_dep.dataset_id, dataset_dep.dataset_id AS metadata_id, dataset_dep.srs AS dataset_srs, dataset_dep.title, dataset_dep.abstract AS dataset_abstract, dataset_dep.accessconstraints, dataset_dep.isopen, dataset_dep.termsofuse, f_collect_searchtext_dataset(dataset_dep.dataset_id) AS searchtext, dataset_dep.dataset_timestamp, dataset_dep.department, dataset_dep.mb_group_name, dataset_dep.mb_group_title, dataset_dep.mb_group_country,
                CASE
                    WHEN dataset_dep.load_count IS NULL THEN 0::bigint
                    ELSE dataset_dep.load_count
                END AS load_count, dataset_dep.mb_group_stateorprovince, f_collect_inspire_cat_dataset(dataset_dep.dataset_id) AS md_inspire_cats, f_collect_custom_cat_dataset(dataset_dep.dataset_id) AS md_custom_cats, f_collect_topic_cat_dataset(dataset_dep.dataset_id) AS md_topic_cats, dataset_dep.bbox AS the_geom, (((((st_xmin(dataset_dep.bbox::box3d)::text || ','::text) || st_ymin(dataset_dep.bbox::box3d)::text) || ','::text) || st_xmax(dataset_dep.bbox::box3d)::text) || ','::text) || st_ymax(dataset_dep.bbox::box3d)::text AS bbox, dataset_dep.preview_url, dataset_dep.fileidentifier, f_get_coupled_resources(dataset_dep.dataset_id) AS coupled_resources, dataset_dep.mb_group_logo_path, dataset_dep.timebegin::date AS timebegin,
                CASE
                    WHEN dataset_dep.update_frequency::text = 'continual'::text THEN now()::date
                    WHEN dataset_dep.update_frequency::text = 'daily'::text THEN now()::date
                    WHEN dataset_dep.update_frequency::text = 'weekly'::text THEN (now() - '7 days'::interval)::date
                    WHEN dataset_dep.update_frequency::text = 'fortnightly'::text THEN (now() - '14 days'::interval)::date
                    WHEN dataset_dep.update_frequency::text = 'monthly'::text THEN (now() - '1 mon'::interval)::date
                    WHEN dataset_dep.update_frequency::text = 'quarterly'::text THEN (now() - '3 mons'::interval)::date
                    WHEN dataset_dep.update_frequency::text = 'biannually'::text THEN (now() - '6 mons'::interval)::date
                    WHEN dataset_dep.update_frequency::text = 'annually'::text THEN (now() - '1 year'::interval)::date
                    ELSE dataset_dep.timeend::date
                END AS timeend
           FROM ( SELECT mb_metadata.the_geom AS bbox, mb_metadata.ref_system AS srs, mb_metadata.metadata_id AS dataset_id, mb_metadata.title, mb_metadata.abstract, mb_metadata.lastchanged AS dataset_timestamp, mb_metadata.tmp_reference_1 AS timebegin, mb_metadata.tmp_reference_2 AS timeend, mb_metadata.uuid AS fileidentifier, mb_metadata.preview_image AS preview_url, mb_metadata.load_count, mb_metadata.fkey_mb_user_id, mb_metadata.constraints AS accessconstraints, mb_metadata.update_frequency, f_getmd_tou(mb_metadata.metadata_id) AS termsofuse, f_tou_isopen(f_getmd_tou(mb_metadata.metadata_id)) AS isopen, mb_metadata.mb_group_id AS department, mb_metadata.mb_group_name, mb_metadata.mb_group_title, mb_metadata.mb_group_country, mb_metadata.mb_group_stateorprovince, mb_metadata.mb_group_logo_path
                   FROM (

--begin union select
SELECT * FROM (SELECT mb_metadata.metadata_id, mb_metadata.uuid, mb_metadata.origin, mb_metadata.includeincaps, mb_metadata.fkey_mb_group_id, mb_metadata.schema, mb_metadata.createdate, mb_metadata.changedate, mb_metadata.lastchanged,
--mb_metadata.data,
 mb_metadata.link, mb_metadata.linktype, mb_metadata.md_format, mb_metadata.title, mb_metadata.abstract, mb_metadata.searchtext, mb_metadata.status, mb_metadata.type, mb_metadata.harvestresult, mb_metadata.harvestexception, mb_metadata.export2csw, mb_metadata.tmp_reference_1, mb_metadata.tmp_reference_2, mb_metadata.spatial_res_type, mb_metadata.spatial_res_value, mb_metadata.ref_system, mb_metadata.format, mb_metadata.inspire_charset, mb_metadata.inspire_top_consistence, mb_metadata.fkey_mb_user_id, mb_metadata.responsible_party, mb_metadata.individual_name, mb_metadata.visibility, mb_metadata.locked, mb_metadata.copyof, mb_metadata.constraints, mb_metadata.fees, mb_metadata.classification, mb_metadata.browse_graphic, mb_metadata.inspire_conformance, mb_metadata.preview_image, mb_metadata.the_geom, mb_metadata.lineage, mb_metadata.datasetid, mb_metadata.randomid, mb_metadata.update_frequency, mb_metadata.datasetid_codespace, mb_metadata.bounding_geom, mb_metadata.inspire_whole_area, mb_metadata.inspire_actual_coverage, mb_metadata.datalinks, mb_metadata.inspire_download, mb_metadata.transfer_size, mb_metadata.md_license_source_note, mb_metadata.responsible_party_name, mb_metadata.responsible_party_email, mb_metadata.searchable, metadata_load_count.load_count
                           FROM mb_metadata
                      LEFT JOIN metadata_load_count ON mb_metadata.metadata_id = metadata_load_count.fkey_metadata_id) mb_metadata,
(
--
SELECT groups_for_publishing.fkey_mb_group_id, groups_for_publishing.mb_group_id, groups_for_publishing.mb_group_name, groups_for_publishing.mb_group_title, groups_for_publishing.mb_group_country, groups_for_publishing.mb_group_stateorprovince, groups_for_publishing.mb_group_logo_path, 0 AS fkey_mb_user_id_from_users FROM groups_for_publishing
--
) user_dep

 WHERE (mb_metadata.fkey_mb_group_id = user_dep.mb_group_id) AND mb_metadata.the_geom IS NOT NULL AND mb_metadata.searchable IS TRUE

UNION ALL

SELECT * FROM (SELECT mb_metadata.metadata_id, mb_metadata.uuid, mb_metadata.origin, mb_metadata.includeincaps, mb_metadata.fkey_mb_group_id, mb_metadata.schema, mb_metadata.createdate, mb_metadata.changedate, mb_metadata.lastchanged,
--mb_metadata.data,
 mb_metadata.link, mb_metadata.linktype, mb_metadata.md_format, mb_metadata.title, mb_metadata.abstract, mb_metadata.searchtext, mb_metadata.status, mb_metadata.type, mb_metadata.harvestresult, mb_metadata.harvestexception, mb_metadata.export2csw, mb_metadata.tmp_reference_1, mb_metadata.tmp_reference_2, mb_metadata.spatial_res_type, mb_metadata.spatial_res_value, mb_metadata.ref_system, mb_metadata.format, mb_metadata.inspire_charset, mb_metadata.inspire_top_consistence, mb_metadata.fkey_mb_user_id, mb_metadata.responsible_party, mb_metadata.individual_name, mb_metadata.visibility, mb_metadata.locked, mb_metadata.copyof, mb_metadata.constraints, mb_metadata.fees, mb_metadata.classification, mb_metadata.browse_graphic, mb_metadata.inspire_conformance, mb_metadata.preview_image, mb_metadata.the_geom, mb_metadata.lineage, mb_metadata.datasetid, mb_metadata.randomid, mb_metadata.update_frequency, mb_metadata.datasetid_codespace, mb_metadata.bounding_geom, mb_metadata.inspire_whole_area, mb_metadata.inspire_actual_coverage, mb_metadata.datalinks, mb_metadata.inspire_download, mb_metadata.transfer_size, mb_metadata.md_license_source_note, mb_metadata.responsible_party_name, mb_metadata.responsible_party_email, mb_metadata.searchable, metadata_load_count.load_count
                           FROM mb_metadata
                      LEFT JOIN metadata_load_count ON mb_metadata.metadata_id = metadata_load_count.fkey_metadata_id) mb_metadata,
(
--
SELECT publishing_registrating_authorities.fkey_mb_group_id, publishing_registrating_authorities.mb_group_id, publishing_registrating_authorities.mb_group_name, publishing_registrating_authorities.mb_group_title, publishing_registrating_authorities.mb_group_country, publishing_registrating_authorities.mb_group_stateorprovince, publishing_registrating_authorities.mb_group_logo_path,  users_for_publishing.fkey_mb_user_id AS fkey_mb_user_id_from_users FROM groups_for_publishing AS publishing_registrating_authorities, users_for_publishing WHERE users_for_publishing.primary_group_id = publishing_registrating_authorities.fkey_mb_group_id
--
) user_dep

 WHERE (mb_metadata.fkey_mb_group_id IS null OR mb_metadata.fkey_mb_group_id = 0) AND mb_metadata.fkey_mb_user_id = user_dep.fkey_mb_user_id_from_users AND mb_metadata.the_geom IS NOT NULL AND mb_metadata.searchable IS TRUE
--end for union select
) mb_metadata ) dataset_dep

          ORDER BY dataset_dep.dataset_id) datasets;

ALTER TABLE search_dataset_view
  OWNER TO :db_owner;

-- recreate view, cause it depends on earlier view :-(

-- new view for wfs search

-- View: search_wfs_view_2

DROP VIEW search_wfs_view;

CREATE OR REPLACE VIEW search_wfs_view AS

SELECT wfs_new.*, isopen FROM (SELECT wfs_table.*, wfs_termsofuse.fkey_termsofuse_id FROM
(SELECT wfs_info.*, mb_group.mb_group_id as department, mb_group.mb_group_name, mb_group_logo_path  FROM (SELECT featuretype.*, geometryfromtext(((((((((((((((((((('POLYGON(('::text || featuretype.featuretype_latlon_array[1]) || ' '::text) || featuretype.featuretype_latlon_array[2]) || ','::text) || featuretype.featuretype_latlon_array[1]) || ' '::text) || featuretype.featuretype_latlon_array[4]) || ','::text) || featuretype.featuretype_latlon_array[3]) || ' '::text) || featuretype.featuretype_latlon_array[4]) || ','::text) || featuretype.featuretype_latlon_array[3]) || ' '::text) || featuretype.featuretype_latlon_array[2]) || ','::text) || featuretype.featuretype_latlon_array[1]) || ' '::text) || featuretype.featuretype_latlon_array[2]) || '))'::text, 4326) AS the_geom, (((((featuretype.featuretype_latlon_array[1] || ','::text) || featuretype.featuretype_latlon_array[2]) || ','::text) || featuretype.featuretype_latlon_array[3]) || ','::text) || featuretype.featuretype_latlon_array[4] AS bbox, f_collect_searchtext_wfs(wfs_id, featuretype.featuretype_id) AS searchtext, wfs_conf_id, wfs_conf_abstract, wfs_conf_description, wfs_conf_type as modultype FROM

(SELECT DISTINCT featuretype_id, featuretype_srs, featuretype_title, featuretype_abstract,  featuretype_latlon_bbox, f_get_geometry_type(featuretype_id) AS element_type, CASE
                    WHEN wfs_featuretype.featuretype_latlon_bbox::text = ''::text THEN string_to_array('-180,-90,180,90'::text, ','::text)
                    WHEN wfs_featuretype.featuretype_latlon_bbox IS NULL THEN string_to_array('-180,-90,180,90'::text, ','::text)
                    ELSE string_to_array(wfs_featuretype.featuretype_latlon_bbox::text, ','::text)
                END AS featuretype_latlon_array, wfs.*  FROM

(SELECT DISTINCT wfs_id, wfs_title, wfs_abstract, wfs_timestamp_create, wfs_timestamp, wfs_network_access, wfs_pricevolume, wfs_proxylog, wfs_owner, country, administrativearea, accessconstraints, fkey_mb_group_id, f_get_responsible_organization(wfs_owner, COALESCE(fkey_mb_group_id, 0)) AS orga_id

FROM wfs WHERE wfs_owner IN (SELECT fkey_mb_user_id FROM users_for_publishing) ORDER BY wfs_id) AS wfs

LEFT JOIN wfs_featuretype ON wfs.wfs_id = wfs_featuretype.fkey_wfs_id WHERE wfs_featuretype.featuretype_searchable = 1) AS featuretype

LEFT JOIN wfs_conf ON featuretype.featuretype_id = wfs_conf.fkey_featuretype_id) AS wfs_info LEFT JOIN mb_group ON wfs_info.orga_id = mb_group.mb_group_id) AS wfs_table LEFT JOIN wfs_termsofuse ON wfs_table.wfs_id = wfs_termsofuse.fkey_wfs_id) AS wfs_new LEFT JOIN termsofuse ON wfs_new.fkey_termsofuse_id = termsofuse.termsofuse_id

 WHERE element_type IS NOT NULL;

ALTER TABLE search_wfs_view
  OWNER TO :db_owner;

-- Column: wms_proxy_exchange_external_urls

-- ALTER TABLE wms DROP COLUMN wms_proxy_exchange_external_urls;

ALTER TABLE wms ADD COLUMN wms_proxy_exchange_external_urls integer;
ALTER TABLE wms ALTER COLUMN wms_proxy_exchange_external_urls SET DEFAULT 1;

UPDATE wms SET wms_proxy_exchange_external_urls = 1 WHERE wms_proxy_exchange_external_urls IS NULL;

 -- Table: json_schema

-- DROP TABLE json_schema;

CREATE TABLE json_schema
(
  id serial NOT NULL,
  uuid uuid,
  version character varying(10) NOT NULL DEFAULT 'draft-04'::character varying, --draft-04, draft-05, draft-06
  title character varying(255),
  abstract text,
  usecase integer, -- 1 - digitize, 2 - ...
  geomtype integer, -- 1 - point, 2 - linestring, 3 - polygon
  schema text,
  created integer, --timestamp
  updated integer, --timestamp
  deleted integer, --timestamp
  fkey_mb_user_id integer,
  fkey_mb_group_id integer,
  public boolean DEFAULT TRUE,
  CONSTRAINT pk_json_schema_id PRIMARY KEY (id)
)
WITH (
  OIDS=FALSE
);

ALTER TABLE json_schema
  OWNER TO :db_owner;

-- Index: idx_json_schema_id

-- DROP INDEX idx_json_schema_id;

CREATE INDEX idx_json_schema_id
  ON json_schema
  USING btree
  (id);

-- Function: update_json_schema_updated_column()

-- DROP FUNCTION update_json_schema_updated_column();

CREATE OR REPLACE FUNCTION update_json_schema_updated_column()
  RETURNS trigger AS
$BODY$
BEGIN
   NEW.updated = EXTRACT(EPOCH FROM NOW())::INTEGER;
   RETURN NEW;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION update_json_schema_updated_column()
  OWNER TO :db_owner;

-- Trigger: update_updated on json_schema

-- DROP TRIGGER update_updated ON json_schema;

CREATE TRIGGER update_updated
  BEFORE UPDATE
  ON json_schema
  FOR EACH ROW
  EXECUTE PROCEDURE update_json_schema_updated_column();

-- Column: mb_group_csw_catalogues

-- ALTER TABLE mb_group_csw_catalogues DROP COLUMN mb_group_csw_catalogues;

ALTER TABLE mb_group ADD COLUMN mb_group_csw_catalogues text;

--Eintragen der neuen Ckan-Sync-Elemente in Gui Administration_DE
INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('Administration_DE','ckan_sync',2,1,'ckan sync module','Ckan Synchronisierung','a','','href = "../javascripts/mod_syncCkan_client.php?compareTimestamps=true"',80,10,NULL ,NULL ,NULL ,'font-family: Arial, Helvetica, sans-serif; font-size : 12px; text-decoration : none; color: #808080;','Ckan Synchronisierungsmodul','a','','','','','');

INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('Administration_DE','ckan_sync_collection',2,1,'Put existing divs in new div object. List the elements comma-separated under target, and make sure they have a title.','','div','','',0,80,200,30,NULL ,'','','div','../plugins/mb_div_collection.js','','ckan_sync,ckan_sync_icon','','');

INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('Administration_DE','ckan_sync_icon',2,1,'icon','','img','../img/misc/ckan.png','',0,0,NULL ,NULL ,2,'','','','','','','','');

--ergänze Eintrag ckan_sync_collection in Target von Element menu_metadata in Administration_DE
UPDATE gui_element SET e_target = replace(e_target, 'dataset_metadata_collection', 'dataset_metadata_collection,ckan_sync_collection') WHERE fkey_gui_id = 'Administration_DE' AND e_id = 'menu_metadata' AND e_target = 'dataset_metadata_collection';

-- Column: mb_group_ckan_catalogues

-- ALTER TABLE mb_group DROP COLUMN mb_group_ckan_catalogues;

ALTER TABLE mb_group ADD COLUMN mb_group_ckan_catalogues text;

-- Column: mb_group_registry_url

-- ALTER TABLE mb_group DROP COLUMN mb_group_registry_url;

ALTER TABLE mb_group ADD COLUMN mb_group_registry_url character varying(1024);

INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('admin_metadata','jq_metadata',1,1,'Metadata plugin','','','','',NULL ,NULL ,NULL ,NULL ,NULL ,'','','','','../extensions/jquery.metadata.2.1/jquery.metadata.min.js','','','http://plugins.jquery.com/project/metadata');

INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('admin_metadata','jq_ui_datepicker',5,1,'Datepicker from jQuery UI framework','','','','',NULL ,NULL ,NULL ,NULL ,NULL ,'','','','../plugins/jq_ui_datepicker.js','../extensions/jquery-ui-1.8.16.custom/development-bundle/ui/jquery.ui.datepicker.js','','jq_ui','');

INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('admin_metadata','jq_ui_tabs',5,1,'horizontal tabs from the jQuery UI framework','','','','',NULL ,NULL ,NULL ,NULL ,NULL ,'','','','','../extensions/jquery-ui-1.8.16.custom/development-bundle/ui/minified/jquery.ui.tabs.min.js','','jq_ui,jq_ui_widget','');

INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('admin_metadata','jq_upload',1,1,'Allows to upload files into Mapbender''s temporary files folder','','','','',NULL ,NULL ,NULL ,NULL ,NULL ,'','','','','../plugins/jq_upload.js','','','');

INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('admin_metadata','jq_validate',1,1,'The jQuery validation plugin','','','','',NULL ,NULL ,NULL ,NULL ,NULL ,'','','','../javascripts/jq_validate.js','../extensions/jquery-validate/jquery.validate.min.js','','','http://docs.jquery.com/Plugins/Validation');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('admin_metadata', 'jq_validate', 'css', 'label.error { float: none; color: red; padding-left: .5em; vertical-align: top; }', '' ,'text/css');

INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('admin_metadata','mb_geodata_import',1,0,'Allows to upload files into Mapbender''s temporary files folder','','','','',NULL ,NULL ,NULL ,NULL ,NULL ,'','','','../plugins/mb_metadata_import.js','','','','');

INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('admin_metadata','mb_md_showMetadataAddon',2,1,'Show addon editor for metadata','Metadata Addon Editor','div','','',NULL ,NULL ,NULL ,NULL ,NULL ,'display:none;','','div','../plugins/mb_metadata_showMetadataAddon.js','','','jq_ui_dialog','');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('admin_metadata', 'mb_md_showMetadataAddon', 'differentFromOriginalCss', '.differentFromOriginal{
background-color:#FFFACD;
}', 'css for class differentFromOriginal' ,'text/css');
INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('admin_metadata', 'mb_md_showMetadataAddon', 'inputs', '[
    {
        "method": "init",
        "title": "initialize",
        "linkedTo": [
            {
                "id": "mb_md_edit",
                "event": "showOriginalMetadata",
                "attr": "data"
            }
        ]
    },
    {
        "method": "initLayer",
        "title": "initialize",
        "linkedTo": [
            {
                "id": "mb_md_layer",
                "event": "showOriginalLayerMetadata",
                "attr": "data"
            }
        ]
    }
]', '' ,'var');

INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('admin_metadata','mb_metadata_gml_import',1,1,'','','div','','',NULL ,NULL ,NULL ,NULL ,NULL ,'','','div','../plugins/mb_metadata_gml_import.js','','','','');

INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod, e_target, e_requires, e_url) VALUES('admin_metadata','mb_metadata_xml_import',1,1,'','','div','','',NULL ,NULL ,NULL ,NULL ,NULL ,'','','div','../plugins/mb_metadata_xml_import.js','','','','');


-- add possibility to store wfs monitoring information in database

-- first add new primary key

ALTER TABLE mb_monitor DROP CONSTRAINT pk_mb_monitor;
ALTER TABLE mb_monitor ADD COLUMN id SERIAL PRIMARY KEY;
ALTER TABLE mb_monitor ALTER COLUMN fkey_wms_id DROP NOT NULL;
ALTER TABLE mb_monitor ALTER COLUMN fkey_wms_id DROP DEFAULT;

-- Column: fkey_wfs_id

-- ALTER TABLE mb_monitor DROP COLUMN fkey_wfs_id;

ALTER TABLE mb_monitor ADD COLUMN fkey_wfs_id integer;

-- Foreign Key: fkey_monitor_wfs_id_wfs_id

-- ALTER TABLE mb_monitor DROP CONSTRAINT fkey_monitor_wfs_id_wfs_id;

ALTER TABLE mb_monitor
  ADD CONSTRAINT fkey_monitor_wfs_id_wfs_id FOREIGN KEY (fkey_wfs_id)
      REFERENCES wfs (wfs_id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE;

-- add new columns for json which holds the results of the getfeature requests and urls

-- Column: feature_urls

-- ALTER TABLE mb_monitor DROP COLUMN feature_urls;

ALTER TABLE mb_monitor ADD COLUMN feature_urls character varying;

-- Column: feature_content

-- ALTER TABLE mb_monitor DROP COLUMN feature_content;

ALTER TABLE mb_monitor ADD COLUMN feature_content character varying;

-- Table: mb_wfs_availability

-- DROP TABLE mb_wfs_availability;

CREATE TABLE mb_wfs_availability
(
  fkey_wfs_id integer,
  fkey_upload_id character varying,
  last_status integer,
  availability real,
  feature_content character varying,
  status_comment character varying,
  average_resp_time real,
  upload_url character varying,
  feature_urls character varying,
  cap_diff text DEFAULT ''::text,
  CONSTRAINT mb_wfs_availability_fkey_wfs_id_wfs_id FOREIGN KEY (fkey_wfs_id)
      REFERENCES wfs (wfs_id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
)
WITH (
  OIDS=FALSE
);
ALTER TABLE mb_wfs_availability
  OWNER TO :db_owner;


-- alter trigger function for handling of wfs monitors
-- Function: mb_monitor_after()

-- DROP FUNCTION mb_monitor_after();

CREATE OR REPLACE FUNCTION mb_monitor_after()
  RETURNS trigger AS
$BODY$DECLARE
   availability_new REAL;
   average_res_cap REAL;
   count_monitors REAL;
    BEGIN
     IF TG_OP = 'UPDATE' THEN
     	IF NEW.fkey_wms_id != null THEN
     		count_monitors := count(fkey_wms_id) from mb_monitor where fkey_wms_id=NEW.fkey_wms_id;
      		--the following should be adopted if the duration of storing is changed!!!
      		average_res_cap := ((select average_resp_time from mb_wms_availability where fkey_wms_id=NEW.fkey_wms_id)*count_monitors+(NEW.timestamp_end-NEW.timestamp_begin))/(count_monitors+1);

     		IF NEW.status > -1 THEN --service gives caps
      			availability_new := round(cast(((select availability from mb_wms_availability where fkey_wms_id=NEW.fkey_wms_id)*count_monitors + 100)/(count_monitors+1) as numeric),2);
     		ELSE --service has problems with caps
      			availability_new := round(cast(((select availability from mb_wms_availability where fkey_wms_id=NEW.fkey_wms_id)*count_monitors)/(count_monitors+1) as numeric),2);
     		END IF;
      		UPDATE mb_wms_availability SET average_resp_time=average_res_cap,last_status=NEW.status, availability=availability_new, image=NEW.image, status_comment=NEW.status_comment,upload_url=NEW.upload_url,map_url=NEW.map_url, cap_diff=NEW.cap_diff WHERE mb_wms_availability.fkey_wms_id=NEW.fkey_wms_id;
      		RETURN NEW;
	ELSE
		IF NEW.fkey_wfs_id != null THEN
			count_monitors := count(fkey_wfs_id) from mb_monitor where fkey_wfs_id=NEW.fkey_wfs_id;
      			--the following should be adopted if the duration of storing is changed!!!
      			average_res_cap := ((select average_resp_time from mb_wfs_availability where fkey_wfs_id=NEW.fkey_wfs_id)*count_monitors+(NEW.timestamp_end-NEW.timestamp_begin))/(count_monitors+1);

     			IF NEW.status > -1 THEN --service gives caps
      				availability_new := round(cast(((select availability from mb_wfs_availability where fkey_wfs_id=NEW.fkey_wfs_id)*count_monitors + 100)/(count_monitors+1) as numeric),2);
     			ELSE --service has problems with caps
      				availability_new := round(cast(((select availability from mb_wfs_availability where fkey_wfs_id=NEW.fkey_wfs_id)*count_monitors)/(count_monitors+1) as numeric),2);
     			END IF;

      			UPDATE mb_wfs_availability SET average_resp_time=average_res_cap,last_status=NEW.status, availability=availability_new, feature_content=NEW.feature_content, status_comment=NEW.status_comment,upload_url=NEW.upload_url,feature_urls=NEW.feature_urls, cap_diff=NEW.cap_diff WHERE mb_wfs_availability.fkey_wfs_id=NEW.fkey_wfs_id;
			RETURN NEW;
		END IF;
	END IF;
     END IF;

     IF TG_OP = 'INSERT' THEN
	IF NEW.fkey_wms_id IS NOT NULL THEN
		IF (select count(fkey_wms_id) from mb_wms_availability where fkey_wms_id=NEW.fkey_wms_id) > 0  THEN -- service is not new
			UPDATE mb_wms_availability set fkey_upload_id=NEW.upload_id,last_status=NEW.status,status_comment=NEW.status_comment,upload_url=NEW.upload_url, cap_diff=NEW.cap_diff where fkey_wms_id=NEW.fkey_wms_id;
		ELSE --service has not yet been monitored
			INSERT INTO mb_wms_availability (fkey_upload_id,fkey_wms_id,last_status,status_comment,upload_url,map_url,cap_diff,average_resp_time,availability) VALUES (NEW.upload_id,NEW.fkey_wms_id,NEW.status,NEW.status_comment,NEW.upload_url::text,NEW.map_url,NEW.cap_diff,0,100);
		END IF;
      		RETURN NEW;
	ELSE
		IF NEW.fkey_wfs_id IS NOT NULL THEN
			IF (select count(fkey_wfs_id) from mb_wfs_availability where fkey_wfs_id=NEW.fkey_wfs_id) > 0  then -- service is not new
				UPDATE mb_wfs_availability set fkey_upload_id=NEW.upload_id,last_status=NEW.status,status_comment=NEW.status_comment,upload_url=NEW.upload_url, cap_diff=NEW.cap_diff where fkey_wfs_id=NEW.fkey_wfs_id;
			ELSE --service has not yet been monitored
				INSERT INTO mb_wfs_availability (fkey_upload_id,fkey_wfs_id,last_status,status_comment,upload_url,feature_urls,cap_diff,average_resp_time,availability) VALUES (NEW.upload_id,NEW.fkey_wfs_id,NEW.status,NEW.status_comment,NEW.upload_url::text,NEW.feature_urls,NEW.cap_diff,0,100);
			END IF;
      			RETURN NEW;
		END IF;
	END IF;
     END IF;
     RETURN NEW;
    END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION mb_monitor_after()
  OWNER TO :db_owner;

-- Column: monitor_count

-- ALTER TABLE mb_wfs_availability DROP COLUMN monitor_count;

ALTER TABLE mb_wfs_availability ADD COLUMN monitor_count integer;

-- Column: monitor_count

-- ALTER TABLE mb_wms_availability DROP COLUMN monitor_count;

ALTER TABLE mb_wms_availability ADD COLUMN monitor_count integer;

-- Function: mb_monitor_after()
CREATE OR REPLACE FUNCTION mb_monitor_after()
  RETURNS trigger AS
$BODY$DECLARE
   availability_new REAL;
   average_res_cap REAL;
   count_monitors REAL;
    BEGIN
     IF TG_OP = 'UPDATE' THEN
     	IF NEW.fkey_wms_id IS NOT NULL THEN
     		count_monitors := count(fkey_wms_id) from mb_monitor where fkey_wms_id=NEW.fkey_wms_id;
      		--the following should be adopted if the duration of storing is changed!!!
      		average_res_cap := ((select average_resp_time from mb_wms_availability where fkey_wms_id=NEW.fkey_wms_id)*count_monitors+(NEW.timestamp_end-NEW.timestamp_begin))/(count_monitors+1);

     		IF NEW.status > -1 THEN --service gives caps
      			availability_new := round(cast(((select availability from mb_wms_availability where fkey_wms_id=NEW.fkey_wms_id)*count_monitors + 100)/(count_monitors+1) as numeric),2);
     		ELSE --service has problems with caps
      			availability_new := round(cast(((select availability from mb_wms_availability where fkey_wms_id=NEW.fkey_wms_id)*count_monitors)/(count_monitors+1) as numeric),2);
     		END IF;
      		UPDATE mb_wms_availability SET average_resp_time=average_res_cap,last_status=NEW.status, availability=availability_new, image=NEW.image, status_comment=NEW.status_comment,upload_url=NEW.upload_url,map_url=NEW.map_url, cap_diff=NEW.cap_diff, monitor_count=count_monitors::INTEGER WHERE mb_wms_availability.fkey_wms_id=NEW.fkey_wms_id;
	ELSE
		IF NEW.fkey_wfs_id IS NOT NULL THEN
			count_monitors := count(fkey_wfs_id) from mb_monitor where fkey_wfs_id=NEW.fkey_wfs_id;
      			--the following should be adopted if the duration of storing is changed!!!
      			average_res_cap := ((select average_resp_time from mb_wfs_availability where fkey_wfs_id=NEW.fkey_wfs_id)*count_monitors+(NEW.timestamp_end-NEW.timestamp_begin))/(count_monitors+1);

     			IF NEW.status > -1 THEN --service gives caps
      				availability_new := round(cast(((select availability from mb_wfs_availability where fkey_wfs_id=NEW.fkey_wfs_id)*count_monitors + 100)/(count_monitors+1) as numeric),2);
     			ELSE --service has problems with caps
      				availability_new := round(cast(((select availability from mb_wfs_availability where fkey_wfs_id=NEW.fkey_wfs_id)*count_monitors)/(count_monitors+1) as numeric),2);
     			END IF;
      			UPDATE mb_wfs_availability SET average_resp_time=average_res_cap,last_status=NEW.status, availability=availability_new, feature_content=NEW.feature_content, status_comment=NEW.status_comment,upload_url=NEW.upload_url,feature_urls=NEW.feature_urls, cap_diff=NEW.cap_diff, monitor_count=count_monitors::INTEGER WHERE mb_wfs_availability.fkey_wfs_id=NEW.fkey_wfs_id;
		END IF;
	END IF;
	RETURN NEW;
     END IF;

     IF TG_OP = 'INSERT' THEN
	IF NEW.fkey_wms_id IS NOT NULL THEN
		IF (select count(fkey_wms_id) from mb_wms_availability where fkey_wms_id=NEW.fkey_wms_id) > 0  THEN -- service is not new
			UPDATE mb_wms_availability set fkey_upload_id=NEW.upload_id,last_status=NEW.status,status_comment=NEW.status_comment,upload_url=NEW.upload_url, cap_diff=NEW.cap_diff where fkey_wms_id=NEW.fkey_wms_id;
		ELSE --service has not yet been monitored
			INSERT INTO mb_wms_availability (fkey_upload_id,fkey_wms_id,last_status,status_comment,upload_url,map_url,cap_diff,average_resp_time,availability,monitor_count) VALUES (NEW.upload_id,NEW.fkey_wms_id,NEW.status,NEW.status_comment,NEW.upload_url::text,NEW.map_url,NEW.cap_diff,0,100,0);
		END IF;
	ELSE
		IF NEW.fkey_wfs_id IS NOT NULL THEN
			IF (select count(fkey_wfs_id) from mb_wfs_availability where fkey_wfs_id=NEW.fkey_wfs_id) > 0  then -- service is not new
				UPDATE mb_wfs_availability set fkey_upload_id=NEW.upload_id,last_status=NEW.status,status_comment=NEW.status_comment,upload_url=NEW.upload_url, cap_diff=NEW.cap_diff where fkey_wfs_id=NEW.fkey_wfs_id;
			ELSE --service has not yet been monitored
				INSERT INTO mb_wfs_availability (fkey_upload_id,fkey_wfs_id,last_status,status_comment,upload_url,feature_urls,cap_diff,average_resp_time,availability,monitor_count) VALUES (NEW.upload_id,NEW.fkey_wfs_id,NEW.status,NEW.status_comment,NEW.upload_url::text,NEW.feature_urls,NEW.cap_diff,0,100,0);
			END IF;
		END IF;
	END IF;
	RETURN NEW;
     END IF;
    END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION mb_monitor_after()
  OWNER TO :db_owner;

-- new function to pull responsible organization

-- Function: f_get_responsible_organization_for_ressource(integer, varchar)

-- DROP FUNCTION f_get_responsible_organization_for_ressource(integer, varchar);

CREATE OR REPLACE FUNCTION f_get_responsible_organization_for_ressource(i_ressource_id integer, s_ressource_type varchar)
  RETURNS integer AS
$BODY$DECLARE
  -- s_ressource_type is 'metadata', 'wms' or 'wfs'
  -- select f_get_responsible_organization_for_ressource(1,'metadata');
  i_resp_orga_id INTEGER;

BEGIN

IF s_ressource_type='metadata' THEN
     i_resp_orga_id := fkey_mb_group_id FROM mb_metadata WHERE metadata_id = i_ressource_id;
     IF i_resp_orga_id IS NULL OR i_resp_orga_id = 0 THEN
        --get primary group for fkey_user_id
	i_resp_orga_id := fkey_mb_group_id FROM mb_user_mb_group WHERE mb_user_mb_group_type = 2 AND fkey_mb_user_id = (SELECT fkey_mb_user_id FROM mb_metadata WHERE metadata_id = i_ressource_id) LIMIT 1;
        --get primary group for fkey_user_id
     END IF;
ELSIF s_ressource_type='wms' THEN
     i_resp_orga_id := fkey_mb_group_id FROM wms WHERE wms_id = i_ressource_id;
     IF i_resp_orga_id IS NULL OR i_resp_orga_id = 0THEN
        --get primary group for fkey_user_id
	i_resp_orga_id := fkey_mb_group_id FROM mb_user_mb_group WHERE mb_user_mb_group_type = 2 AND fkey_mb_user_id = (SELECT wms_owner FROM wms WHERE wms_id = i_ressource_id) LIMIT 1;
        --get primary group for fkey_user_id
     END IF;
ELSIF s_ressource_type='wfs' THEN
     i_resp_orga_id := fkey_mb_group_id FROM wfs WHERE wfs_id = i_ressource_id;
     IF i_resp_orga_id IS NULL  OR i_resp_orga_id = 0 THEN
        --get primary group for fkey_user_id
	i_resp_orga_id := fkey_mb_group_id FROM mb_user_mb_group WHERE mb_user_mb_group_type = 2 AND fkey_mb_user_id = (SELECT wfs_owner FROM wfs WHERE wfs_id = i_ressource_id) LIMIT 1;
        --get primary group for fkey_user_id
     END IF;
END IF;

RETURN i_resp_orga_id;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE STRICT
  COST 100;
ALTER FUNCTION f_get_responsible_organization_for_ressource(integer, varchar)
  OWNER TO :db_owner;

-- Column: export2ckan

-- ALTER TABLE mb_group DROP COLUMN export2ckan;

ALTER TABLE mb_group ADD COLUMN export2ckan boolean;
ALTER TABLE mb_group ALTER COLUMN export2ckan SET DEFAULT true;

UPDATE mb_group SET export2ckan = TRUE WHERE export2ckan IS NULL;

-- Function: f_get_availability_of_ressource(integer, character varying)

-- DROP FUNCTION f_get_availability_of_ressource(integer, character varying);

CREATE OR REPLACE FUNCTION f_get_availability_of_ressource(i_ressource_id integer, s_ressource_type character varying)
  RETURNS real AS
$BODY$DECLARE
  -- s_ressource_type is 'wms' or 'wfs'
  -- select f_get_availability_of_ressource(1,'wms');
  r_availability real;

BEGIN

IF s_ressource_type='wms' THEN
     r_availability := availability FROM mb_wms_availability WHERE fkey_wms_id = i_ressource_id;
ELSIF s_ressource_type='wfs' THEN
     r_availability := availability FROM mb_wfs_availability WHERE fkey_wfs_id = i_ressource_id;
END IF;

RETURN r_availability;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE STRICT
  COST 100;
ALTER FUNCTION f_get_availability_of_ressource(integer, character varying)
  OWNER TO :db_owner;

-- Function: f_get_status_of_ressource(integer, character varying)

-- DROP FUNCTION f_get_status_of_ressource(integer, character varying);

CREATE OR REPLACE FUNCTION f_get_status_of_ressource(i_ressource_id integer, s_ressource_type character varying)
  RETURNS integer AS
$BODY$DECLARE
  -- s_ressource_type is 'wms' or 'wfs'
  -- select f_get_availability_of_ressource(1,'wms');
  i_status integer;

BEGIN

IF s_ressource_type='wms' THEN
     i_status := last_status FROM mb_wms_availability WHERE fkey_wms_id = i_ressource_id;
ELSIF s_ressource_type='wfs' THEN
     i_status := last_status FROM mb_wfs_availability WHERE fkey_wfs_id = i_ressource_id;
END IF;

RETURN i_status;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE STRICT
  COST 100;
ALTER FUNCTION f_get_status_of_ressource(integer, character varying)
  OWNER TO :db_owner;

-- View: search_wfs_view

DROP VIEW search_wfs_view;

CREATE OR REPLACE VIEW search_wfs_view AS


SELECT wfs_new.featuretype_id, wfs_new.featuretype_srs, wfs_new.featuretype_title, wfs_new.featuretype_abstract, wfs_new.featuretype_latlon_bbox, wfs_new.element_type, wfs_new.featuretype_latlon_array, wfs_new.wfs_id,  f_collect_custom_cat_wfs_featuretype(wfs_new.featuretype_id) AS md_custom_cats, f_collect_inspire_cat_wfs_featuretype(wfs_new.featuretype_id) AS md_inspire_cats, f_collect_topic_cat_wfs_featuretype(wfs_new.featuretype_id) AS md_topic_cats, f_get_availability_of_ressource(wfs_new.wfs_id ,'wfs') as availability,  f_get_status_of_ressource(wfs_new.wfs_id ,'wfs') as status, wfs_new.wfs_title, wfs_new.wfs_abstract, wfs_new.wfs_timestamp_create, wfs_new.wfs_timestamp, wfs_new.wfs_network_access, wfs_new.wfs_pricevolume, wfs_new.wfs_proxylog, wfs_new.wfs_owner, wfs_new.country, wfs_new.administrativearea, wfs_new.accessconstraints, wfs_new.fkey_mb_group_id, wfs_new.orga_id, wfs_new.the_geom, wfs_new.bbox, wfs_new.searchtext, wfs_new.wfs_conf_id, wfs_new.wfs_conf_abstract, wfs_new.wfs_conf_description, wfs_new.modultype, wfs_new.department, wfs_new.mb_group_name, wfs_new.mb_group_logo_path, wfs_new.fkey_termsofuse_id, termsofuse.isopen
   FROM ( SELECT wfs_table.featuretype_id, wfs_table.featuretype_srs, wfs_table.featuretype_title, wfs_table.featuretype_abstract, wfs_table.featuretype_latlon_bbox, wfs_table.element_type, wfs_table.featuretype_latlon_array, wfs_table.wfs_id, wfs_table.wfs_title, wfs_table.wfs_abstract, wfs_table.wfs_timestamp_create, wfs_table.wfs_timestamp, wfs_table.wfs_network_access, wfs_table.wfs_pricevolume, wfs_table.wfs_proxylog, wfs_table.wfs_owner, wfs_table.country, wfs_table.administrativearea, wfs_table.accessconstraints, wfs_table.fkey_mb_group_id, wfs_table.orga_id, wfs_table.the_geom, wfs_table.bbox, wfs_table.searchtext, wfs_table.wfs_conf_id, wfs_table.wfs_conf_abstract, wfs_table.wfs_conf_description, wfs_table.modultype, wfs_table.department, wfs_table.mb_group_name, wfs_table.mb_group_logo_path, wfs_termsofuse.fkey_termsofuse_id
           FROM ( SELECT wfs_info.featuretype_id, wfs_info.featuretype_srs, wfs_info.featuretype_title, wfs_info.featuretype_abstract, wfs_info.featuretype_latlon_bbox, wfs_info.element_type, wfs_info.featuretype_latlon_array, wfs_info.wfs_id, wfs_info.wfs_title, wfs_info.wfs_abstract, wfs_info.wfs_timestamp_create, wfs_info.wfs_timestamp, wfs_info.wfs_network_access, wfs_info.wfs_pricevolume, wfs_info.wfs_proxylog, wfs_info.wfs_owner, wfs_info.country, wfs_info.administrativearea, wfs_info.accessconstraints, wfs_info.fkey_mb_group_id, wfs_info.orga_id, wfs_info.the_geom, wfs_info.bbox, wfs_info.searchtext, wfs_info.wfs_conf_id, wfs_info.wfs_conf_abstract, wfs_info.wfs_conf_description, wfs_info.modultype, mb_group.mb_group_id AS department, mb_group.mb_group_name, mb_group.mb_group_logo_path
                   FROM ( SELECT featuretype.featuretype_id, featuretype.featuretype_srs, featuretype.featuretype_title, featuretype.featuretype_abstract, featuretype.featuretype_latlon_bbox, featuretype.element_type, featuretype.featuretype_latlon_array, featuretype.wfs_id, featuretype.wfs_title, featuretype.wfs_abstract, featuretype.wfs_timestamp_create, featuretype.wfs_timestamp, featuretype.wfs_network_access, featuretype.wfs_pricevolume, featuretype.wfs_proxylog, featuretype.wfs_owner, featuretype.country, featuretype.administrativearea, featuretype.accessconstraints, featuretype.fkey_mb_group_id, featuretype.orga_id, geometryfromtext(((((((((((((((((((('POLYGON(('::text || featuretype.featuretype_latlon_array[1]) || ' '::text) || featuretype.featuretype_latlon_array[2]) || ','::text) || featuretype.featuretype_latlon_array[1]) || ' '::text) || featuretype.featuretype_latlon_array[4]) || ','::text) || featuretype.featuretype_latlon_array[3]) || ' '::text) || featuretype.featuretype_latlon_array[4]) || ','::text) || featuretype.featuretype_latlon_array[3]) || ' '::text) || featuretype.featuretype_latlon_array[2]) || ','::text) || featuretype.featuretype_latlon_array[1]) || ' '::text) || featuretype.featuretype_latlon_array[2]) || '))'::text, 4326) AS the_geom, (((((featuretype.featuretype_latlon_array[1] || ','::text) || featuretype.featuretype_latlon_array[2]) || ','::text) || featuretype.featuretype_latlon_array[3]) || ','::text) || featuretype.featuretype_latlon_array[4] AS bbox, f_collect_searchtext_wfs(featuretype.wfs_id, featuretype.featuretype_id) AS searchtext, wfs_conf.wfs_conf_id, wfs_conf.wfs_conf_abstract, wfs_conf.wfs_conf_description, wfs_conf.wfs_conf_type AS modultype
                           FROM ( SELECT DISTINCT wfs_featuretype.featuretype_id, wfs_featuretype.featuretype_srs, wfs_featuretype.featuretype_title, wfs_featuretype.featuretype_abstract, wfs_featuretype.featuretype_latlon_bbox, f_get_geometry_type(wfs_featuretype.featuretype_id) AS element_type,
                                        CASE
                                            WHEN wfs_featuretype.featuretype_latlon_bbox::text = ''::text THEN string_to_array('-180,-90,180,90'::text, ','::text)
                                            WHEN wfs_featuretype.featuretype_latlon_bbox IS NULL THEN string_to_array('-180,-90,180,90'::text, ','::text)
                                            ELSE string_to_array(wfs_featuretype.featuretype_latlon_bbox::text, ','::text)
                                        END AS featuretype_latlon_array, wfs.wfs_id, wfs.wfs_title, wfs.wfs_abstract, wfs.wfs_timestamp_create, wfs.wfs_timestamp, wfs.wfs_network_access, wfs.wfs_pricevolume, wfs.wfs_proxylog, wfs.wfs_owner, wfs.country, wfs.administrativearea, wfs.accessconstraints, wfs.fkey_mb_group_id, wfs.orga_id
                                   FROM ( SELECT DISTINCT wfs.wfs_id, wfs.wfs_title, wfs.wfs_abstract, wfs.wfs_timestamp_create, wfs.wfs_timestamp, wfs.wfs_network_access, wfs.wfs_pricevolume, wfs.wfs_proxylog, wfs.wfs_owner, wfs.country, wfs.administrativearea, wfs.accessconstraints, wfs.fkey_mb_group_id, f_get_responsible_organization(wfs.wfs_owner, COALESCE(wfs.fkey_mb_group_id, 0)) AS orga_id
                                           FROM wfs
                                          WHERE (wfs.wfs_owner IN ( SELECT users_for_publishing.fkey_mb_user_id
                                                   FROM users_for_publishing))
                                          ORDER BY wfs.wfs_id) wfs
                              LEFT JOIN wfs_featuretype ON wfs.wfs_id = wfs_featuretype.fkey_wfs_id
                             WHERE wfs_featuretype.featuretype_searchable = 1) featuretype
                      LEFT JOIN wfs_conf ON featuretype.featuretype_id = wfs_conf.fkey_featuretype_id) wfs_info
              LEFT JOIN mb_group ON wfs_info.orga_id = mb_group.mb_group_id) wfs_table
      LEFT JOIN wfs_termsofuse ON wfs_table.wfs_id = wfs_termsofuse.fkey_wfs_id) wfs_new
   LEFT JOIN termsofuse ON wfs_new.fkey_termsofuse_id = termsofuse.termsofuse_id
  WHERE wfs_new.element_type IS NOT NULL;

ALTER TABLE search_wfs_view
  OWNER TO :db_owner;

DROP VIEW IF EXISTS wfs_service_metadata;
DROP VIEW IF EXISTS wfs_service_metadata_new;

ALTER TABLE wfs ALTER COLUMN wfs_getcapabilities TYPE varchar(4096);
ALTER TABLE wfs ALTER COLUMN wfs_describefeaturetype TYPE varchar(4096);
ALTER TABLE wfs ALTER COLUMN wfs_getfeature TYPE varchar(4096);
ALTER TABLE wfs ALTER COLUMN wfs_transaction TYPE varchar(4096);
ALTER TABLE wfs ALTER COLUMN wfs_upload_url TYPE varchar(4096);

ALTER TABLE wms ALTER COLUMN wms_getcapabilities TYPE varchar(4096);
ALTER TABLE wms ALTER COLUMN wms_getmap TYPE varchar(4096);
ALTER TABLE wms ALTER COLUMN wms_getfeatureinfo TYPE varchar(4096);
ALTER TABLE wms ALTER COLUMN wms_getlegendurl TYPE varchar(4096);
ALTER TABLE wms ALTER COLUMN wms_upload_url TYPE varchar(4096);

UPDATE gui_element_vars SET var_value = '../css/metadataeditor.css' , var_type = 'file/css' WHERE fkey_gui_id like 'admin_%' AND fkey_e_id = 'body' AND var_name = 'css_class_bg';

-- Function: f_collect_topic_cat_dataset(integer)

-- DROP FUNCTION f_collect_topic_cat_dataset(integer);

CREATE OR REPLACE FUNCTION f_collect_topic_cat_dataset(integer)
  RETURNS text AS
$BODY$DECLARE
  i_dataset_serial_id ALIAS FOR $1;
  topic_cat_string  TEXT;
  topic_cat_record  RECORD;

BEGIN
topic_cat_string := '';

FOR topic_cat_record IN SELECT DISTINCT fkey_md_topic_category_id FROM (

SELECT mb_metadata_md_topic_category.fkey_md_topic_category_id from mb_metadata_md_topic_category WHERE mb_metadata_md_topic_category.fkey_metadata_id= $1

UNION

SELECT layer_md_topic_category.fkey_md_topic_category_id from layer_md_topic_category WHERE fkey_layer_id IN (SELECT fkey_layer_id FROM ows_relation_metadata WHERE fkey_metadata_id = $1)

UNION

SELECT wfs_featuretype_md_topic_category.fkey_md_topic_category_id from wfs_featuretype_md_topic_category WHERE fkey_featuretype_id IN (SELECT fkey_featuretype_id FROM ows_relation_metadata WHERE fkey_metadata_id = $1)) as md_topic_category LOOP

topic_cat_string := topic_cat_string || '{' ||topic_cat_record.fkey_md_topic_category_id || '}';
END LOOP ;

RETURN topic_cat_string;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE STRICT
  COST 100;
ALTER FUNCTION f_collect_topic_cat_dataset(integer)
  OWNER TO :db_owner;

-- Function: f_collect_inspire_cat_dataset(integer)

-- DROP FUNCTION f_collect_inspire_cat_dataset(integer);

CREATE OR REPLACE FUNCTION f_collect_inspire_cat_dataset(integer)
  RETURNS text AS
$BODY$DECLARE
  i_dataset_id ALIAS FOR $1;
  inspire_cat_string  TEXT;
  inspire_cat_record  RECORD;

BEGIN
inspire_cat_string := '';

FOR inspire_cat_record IN SELECT DISTINCT fkey_inspire_category_id FROM (

SELECT mb_metadata_inspire_category.fkey_inspire_category_id from mb_metadata_inspire_category WHERE mb_metadata_inspire_category.fkey_metadata_id= $1

UNION

SELECT layer_inspire_category.fkey_inspire_category_id from layer_inspire_category WHERE fkey_layer_id IN (SELECT fkey_layer_id FROM ows_relation_metadata WHERE fkey_metadata_id = $1)

UNION

SELECT wfs_featuretype_inspire_category.fkey_inspire_category_id from wfs_featuretype_inspire_category WHERE fkey_featuretype_id IN (SELECT fkey_featuretype_id FROM ows_relation_metadata WHERE fkey_metadata_id = $1)) as inspire_category LOOP
inspire_cat_string := inspire_cat_string || '{' ||inspire_cat_record.fkey_inspire_category_id || '}';
END LOOP ;

RETURN inspire_cat_string;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE STRICT
  COST 100;
ALTER FUNCTION f_collect_inspire_cat_dataset(integer)
  OWNER TO :db_owner;

-- Function: f_collect_custom_cat_dataset(integer)

-- DROP FUNCTION f_collect_custom_cat_dataset(integer);

CREATE OR REPLACE FUNCTION f_collect_custom_cat_dataset(integer)
  RETURNS text AS
$BODY$DECLARE
  i_dataset_id ALIAS FOR $1;
  custom_cat_string  TEXT;
  custom_cat_record  RECORD;

BEGIN
custom_cat_string := '';

FOR custom_cat_record IN SELECT DISTINCT fkey_custom_category_id FROM (

SELECT mb_metadata_custom_category.fkey_custom_category_id from mb_metadata_custom_category WHERE mb_metadata_custom_category.fkey_metadata_id= $1

UNION

SELECT layer_custom_category.fkey_custom_category_id from layer_custom_category WHERE fkey_layer_id IN (SELECT fkey_layer_id FROM ows_relation_metadata WHERE fkey_metadata_id = $1)

UNION

SELECT wfs_featuretype_custom_category.fkey_custom_category_id from wfs_featuretype_custom_category WHERE fkey_featuretype_id IN (SELECT fkey_featuretype_id FROM ows_relation_metadata WHERE fkey_metadata_id = $1)) as custom_category LOOP
custom_cat_string := custom_cat_string || '{' ||custom_cat_record.fkey_custom_category_id || '}';
END LOOP ;

RETURN custom_cat_string;

END;
$BODY$
  LANGUAGE plpgsql VOLATILE STRICT
  COST 100;
ALTER FUNCTION f_collect_custom_cat_dataset(integer)
  OWNER TO :db_owner;

ALTER TABLE layer_style ALTER COLUMN legendurl TYPE varchar(2048);

--FUNCTION to get couplings between layers and featuretypes via sql to support navigation from layertree layer elements to wfs getfeature tables in guis

-- Function: f_get_layer_featuretype_coupling()

-- DROP FUNCTION f_get_layer_featuretype_coupling();

    -- examples:
    --select f_get_layer_featuretype_coupling(array[20370,20g_id9], FALSE);
    --select f_get_layer_featuretype_coupling(ARRAY(select layer_id from layer), TRUE);

CREATE OR REPLACE FUNCTION f_get_layer_featuretype_coupling(INT[], only_with_wfs_conf boolean default FALSE) RETURNS text AS
$BODY$
DECLARE
    layer_featuretype_relations_json TEXT; --json representation
    layer_featuretype_relations_record  RECORD;
BEGIN
    layer_featuretype_relations_json := '[';

    IF only_with_wfs_conf = TRUE THEN
        FOR layer_featuretype_relations_record IN SELECT layer_metadata_featuretype.*, CASE WHEN wfs_conf_id IS NULL THEN 0 ELSE wfs_conf_id END, CASE WHEN wfs_conf_type IS NULL THEN 0 ELSE wfs_conf_type END FROM (SELECT a.fkey_layer_id, CASE WHEN a.fkey_metadata_id IS NULL THEN 0 ELSE a.fkey_metadata_id END, CASE WHEN b.fkey_featuretype_id IS NULL THEN 0 ELSE b.fkey_featuretype_id END FROM (SELECT fkey_layer_id, fkey_metadata_id FROM ows_relation_metadata WHERE fkey_layer_id = ANY ( $1 )) AS a, (SELECT fkey_featuretype_id, fkey_metadata_id FROM ows_relation_metadata WHERE fkey_featuretype_id IS NOT null) AS b WHERE a.fkey_metadata_id = b.fkey_metadata_id) AS layer_metadata_featuretype INNER JOIN wfs_conf ON layer_metadata_featuretype.fkey_featuretype_id = wfs_conf.fkey_featuretype_id  ORDER by fkey_layer_id DESC LOOP

            layer_featuretype_relations_json := layer_featuretype_relations_json || '{"layerId":' ||layer_featuretype_relations_record.fkey_layer_id || ',"metadataId":' || layer_featuretype_relations_record.fkey_metadata_id || ',"featuretypeId":'|| layer_featuretype_relations_record.fkey_featuretype_id || ',"wfsConfId":'|| layer_featuretype_relations_record.wfs_conf_id || ',"wfsConfType":'|| layer_featuretype_relations_record.wfs_conf_type || '},';
        END LOOP;
    ELSIF only_with_wfs_conf = FALSE THEN
        FOR layer_featuretype_relations_record IN SELECT layer_metadata_featuretype.*, CASE WHEN wfs_conf_id IS NULL THEN 0 ELSE wfs_conf_id END, CASE WHEN wfs_conf_type IS NULL THEN 0 ELSE wfs_conf_type END FROM (SELECT a.fkey_layer_id, CASE WHEN a.fkey_metadata_id IS NULL THEN 0 ELSE a.fkey_metadata_id END, CASE WHEN b.fkey_featuretype_id IS NULL THEN 0 ELSE b.fkey_featuretype_id END FROM (SELECT fkey_layer_id, fkey_metadata_id FROM ows_relation_metadata WHERE fkey_layer_id = ANY ( $1 )) AS a, (SELECT fkey_featuretype_id, fkey_metadata_id FROM ows_relation_metadata WHERE fkey_featuretype_id IS NOT null) AS b WHERE a.fkey_metadata_id = b.fkey_metadata_id) AS layer_metadata_featuretype LEFT JOIN wfs_conf ON layer_metadata_featuretype.fkey_featuretype_id = wfs_conf.fkey_featuretype_id  ORDER by fkey_layer_id DESC LOOP

            layer_featuretype_relations_json := layer_featuretype_relations_json || '{"layerId":' ||layer_featuretype_relations_record.fkey_layer_id || ',"metadataId":' || layer_featuretype_relations_record.fkey_metadata_id || ',"featuretypeId":'|| layer_featuretype_relations_record.fkey_featuretype_id || ',"wfsConfId":'|| layer_featuretype_relations_record.wfs_conf_id || ',"wfsConfType":'|| layer_featuretype_relations_record.wfs_conf_type || '},';
        END LOOP;
    END IF;
    layer_featuretype_relations_json = rtrim(layer_featuretype_relations_json, ',');
    layer_featuretype_relations_json := layer_featuretype_relations_json || ']';
    RETURN layer_featuretype_relations_json;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE STRICT
  COST 100;
ALTER FUNCTION f_get_layer_featuretype_coupling(INT[], boolean)
  OWNER TO :db_owner;

--add new fields for better password hashing
ALTER TABLE mb_user ADD COLUMN salt character varying(100);

ALTER TABLE mb_user ADD COLUMN password character varying(255);

ALTER TABLE mb_user ADD COLUMN is_active boolean;

ALTER TABLE mb_user ADD COLUMN activation_key character varying(255);

ALTER TABLE mb_user ADD COLUMN timestamp_delete bigint;

ALTER TABLE mb_user ADD COLUMN timestamp_dsgvo_accepted bigint;

-- Column: inspire_category_description_en

ALTER TABLE inspire_category DROP COLUMN inspire_category_description_en;
ALTER TABLE inspire_category DROP COLUMN inspire_category_uri;

ALTER TABLE inspire_category ADD COLUMN inspire_category_description_en text;
ALTER TABLE inspire_category ADD COLUMN inspire_category_uri text;

-- ANNEX I

UPDATE inspire_category SET inspire_category_description_en = 'Systems for uniquely referencing spatial information in space as a set of coordinates (x, y, z) and/or latitude and longitude and height, based on a geodetic horizontal and vertical datum.' WHERE inspire_category_key = '1.1';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/rs' WHERE inspire_category_key = '1.1';

UPDATE inspire_category SET inspire_category_description_en = 'Harmonised multi-resolution grid with a common point of origin and standardised location and size of grid cells.' WHERE inspire_category_key = '1.2';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/gg' WHERE inspire_category_key = '1.2';

UPDATE inspire_category SET inspire_category_description_en = 'Names of areas, regions, localities, cities, suburbs, towns or settlements, or any geographical or topographical feature of public or historical interest.' WHERE inspire_category_key = '1.3';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/gn' WHERE inspire_category_key = '1.3';

UPDATE inspire_category SET inspire_category_description_en = 'Units of administration, dividing areas where Member States have and/or exercise jurisdictional rights, for local, regional and national governance, separated by administrative boundaries.' WHERE inspire_category_key = '1.4';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/au' WHERE inspire_category_key = '1.4';

UPDATE inspire_category SET inspire_category_description_en = 'Location of properties based on address identifiers, usually by road name, house number, postal code.' WHERE inspire_category_key = '1.5';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/ad' WHERE inspire_category_key = '1.5';

UPDATE inspire_category SET inspire_category_description_en = 'Areas defined by cadastral registers or equivalent.' WHERE inspire_category_key = '1.6';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/cp' WHERE inspire_category_key = '1.6';

UPDATE inspire_category SET inspire_category_description_en = 'Road, rail, air and water transport networks and related infrastructure. Includes links between different networks. Also includes the trans-European transport network as defined in Decision No 1692/96/EC of the European Parliament and of the Council of 23 July 1996 on Community Guidelines for the development of the trans-European transport network (1) and future revisions of that Decision.' WHERE inspire_category_key = '1.7';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/tn' WHERE inspire_category_key = '1.7';

UPDATE inspire_category SET inspire_category_description_en = 'Hydrographic elements, including marine areas and all other water bodies and items related to them, including river basins and sub-basins. Where appropriate, according to the definitions set out in Directive 2000/60/EC of the European Parliament and of the Council of 23 October 2000 establishing a framework for Community action in the field of water policy (2) and in the form of networks.' WHERE inspire_category_key = '1.8';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/hy' WHERE inspire_category_key = '1.8';

UPDATE inspire_category SET inspire_category_description_en = 'Area designated or managed within a framework of international, Community and Member States'' legislation to achieve specific conservation objectives.' WHERE inspire_category_key = '1.9';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/ps' WHERE inspire_category_key = '1.9';

-- ANNEX II

UPDATE inspire_category SET inspire_category_description_en = 'Digital elevation models for land, ice and ocean surface. Includes terrestrial elevation, bathymetry and shoreline.' WHERE inspire_category_key = '2.1';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/el' WHERE inspire_category_key = '2.1';


UPDATE inspire_category SET inspire_category_description_en = 'Physical and biological cover of the earth''s surface including artificial surfaces, agricultural areas, forests, (semi-)natural areas, wetlands, water bodies.' WHERE inspire_category_key = '2.2';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/lc' WHERE inspire_category_key = '2.2';

UPDATE inspire_category SET inspire_category_description_en = 'Geo-referenced image data of the Earth''s surface, from either satellite or airborne sensors.' WHERE inspire_category_key = '2.3';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/oi' WHERE inspire_category_key = '2.3';


UPDATE inspire_category SET inspire_category_description_en = 'Geology characterised according to composition and structure. Includes bedrock, aquifers and geomorphology.' WHERE inspire_category_key = '2.4';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/ge' WHERE inspire_category_key = '2.4';

-- ANNEX III

UPDATE inspire_category SET inspire_category_description_en = 'Units for dissemination or use of statistical information.' WHERE inspire_category_key = '3.1';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/su' WHERE inspire_category_key = '3.1';

UPDATE inspire_category SET inspire_category_description_en = 'Geographical location of buildings.' WHERE inspire_category_key = '3.2';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/bu' WHERE inspire_category_key = '3.2';

UPDATE inspire_category SET inspire_category_description_en = 'Soils and subsoil characterised according to depth, texture, structure and content of particles and organic material, stoniness, erosion, where appropriate mean slope and anticipated water storage capacity.' WHERE inspire_category_key = '3.3';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/so' WHERE inspire_category_key = '3.3';

UPDATE inspire_category SET inspire_category_description_en = 'Territory characterised according to its current and future planned functional dimension or socio-economic purpose (e.g. residential, industrial, commercial, agricultural, forestry, recreational).' WHERE inspire_category_key = '3.4';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/lu' WHERE inspire_category_key = '3.4';

UPDATE inspire_category SET inspire_category_description_en = 'Geographical distribution of dominance of pathologies (allergies, cancers, respiratory diseases, etc.), information indicating the effect on health (biomarkers, decline of fertility, epidemics) or well-being of humans (fatigue, stress, etc.) linked directly (air pollution, chemicals, depletion of the ozone layer, noise, etc.) or indirectly (food, genetically modified organisms, etc.) to the quality of the environment.' WHERE inspire_category_key = '3.5';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/hh' WHERE inspire_category_key = '3.5';

UPDATE inspire_category SET inspire_category_description_en = 'Includes utility facilities such as sewage, waste management, energy supply and water supply, administrative and social governmental services such as public administrations, civil protection sites, schools and hospitals.' WHERE inspire_category_key = '3.6';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/us' WHERE inspire_category_key = '3.6';

UPDATE inspire_category SET inspire_category_description_en = 'Location and operation of environmental monitoring facilities includes observation and measurement of emissions, of the state of environmental media and of other ecosystem parameters (biodiversity, ecological conditions of vegetation, etc.) by or on behalf of public authorities.' WHERE inspire_category_key = '3.7';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/ef' WHERE inspire_category_key = '3.7';

UPDATE inspire_category SET inspire_category_description_en = 'Industrial production sites, including installations covered by Council Directive 96/61/EC of 24 September 1996 concerning integrated pollution prevention and control (1) and water abstraction facilities, mining, storage sites.' WHERE inspire_category_key = '3.8';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/pf' WHERE inspire_category_key = '3.8';

UPDATE inspire_category SET inspire_category_description_en = 'Farming equipment and production facilities (including irrigation systems, greenhouses and stables).' WHERE inspire_category_key = '3.9';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/af' WHERE inspire_category_key = '3.9';

UPDATE inspire_category SET inspire_category_description_en = 'Geographical distribution of people, including population characteristics and activity levels, aggregated by grid, region, administrative unit or other analytical unit.' WHERE inspire_category_key = '3.10';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/pd' WHERE inspire_category_key = '3.10';

UPDATE inspire_category SET inspire_category_description_en = 'Areas managed, regulated or used for reporting at international, European, national, regional and local levels. Includes dumping sites, restricted areas around drinking water sources, nitrate-vulnerable zones, regulated fairways at sea or large inland waters, areas for the dumping of waste, noise restriction zones, prospecting and mining permit areas, river basin districts, relevant reporting units and coastal zone management areas.' WHERE inspire_category_key = '3.11';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/am' WHERE inspire_category_key = '3.11';

UPDATE inspire_category SET inspire_category_description_en = 'Vulnerable areas characterised according to natural hazards (all atmospheric, hydrologic, seismic, volcanic and wildfire phenomena that, because of their location, severity, and frequency, have the potential to seriously affect society), e.g. floods, landslides and subsidence, avalanches, forest fires, earthquakes, volcanic eruptions.' WHERE inspire_category_key = '3.12';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/nz' WHERE inspire_category_key = '3.12';

UPDATE inspire_category SET inspire_category_description_en = 'Physical conditions in the atmosphere. Includes spatial data based on measurements, on models or on a combination thereof and includes measurement locations.' WHERE inspire_category_key = '3.13';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/ac' WHERE inspire_category_key = '3.13';

UPDATE inspire_category SET inspire_category_description_en = 'Weather conditions and their measurements; precipitation, temperature, evapotranspiration, wind speed and direction.' WHERE inspire_category_key = '3.14';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/mf' WHERE inspire_category_key = '3.14';

UPDATE inspire_category SET inspire_category_description_en = 'Physical conditions of oceans (currents, salinity, wave heights, etc.).' WHERE inspire_category_key = '3.15';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/of' WHERE inspire_category_key = '3.15';

UPDATE inspire_category SET inspire_category_description_en = 'Physical conditions of seas and saline water bodies divided into regions and sub-regions with common characteristics.' WHERE inspire_category_key = '3.16';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/sr' WHERE inspire_category_key = '3.16';

UPDATE inspire_category SET inspire_category_description_en = 'Areas of relatively homogeneous ecological conditions with common characteristics.' WHERE inspire_category_key = '3.17';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/br' WHERE inspire_category_key = '3.17';

UPDATE inspire_category SET inspire_category_description_en = 'Geographical areas characterised by specific ecological conditions, processes, structure, and (life support) functions that physically support the organisms that live there. Includes terrestrial and aquatic areas distinguished by geographical, abiotic and biotic features, whether entirely natural or semi-natural.' WHERE inspire_category_key = '3.18';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/hb' WHERE inspire_category_key = '3.18';

UPDATE inspire_category SET inspire_category_description_en = 'Geographical distribution of occurrence of animal and plant species aggregated by grid, region, administrative unit or other analytical unit.' WHERE inspire_category_key = '3.19';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/sd' WHERE inspire_category_key = '3.19';

UPDATE inspire_category SET inspire_category_description_en = 'Energy resources including hydrocarbons, hydropower, bio-energy, solar, wind, etc., where relevant including depth/height information on the extent of the resource.' WHERE inspire_category_key = '3.20';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/er' WHERE inspire_category_key = '3.20';

UPDATE inspire_category SET inspire_category_description_en = 'Mineral resources including metal ores, industrial minerals, etc., where relevant including depth/height information on the extent of the resource.' WHERE inspire_category_key = '3.21';
UPDATE inspire_category SET inspire_category_uri = 'http://inspire.ec.europa.eu/theme/mr' WHERE inspire_category_key = '3.21';

--topic category
ALTER TABLE md_topic_category DROP COLUMN md_topic_category_description_en;
ALTER TABLE md_topic_category DROP COLUMN md_topic_category_uri;

ALTER TABLE md_topic_category ADD COLUMN md_topic_category_description_en text;
ALTER TABLE md_topic_category ADD COLUMN md_topic_category_uri text;

-- Column: uuid for mb_user table

-- ALTER TABLE mb_user DROP COLUMN uuid;

ALTER TABLE mb_user ADD COLUMN uuid uuid;

ALTER TABLE mb_user ADD COLUMN mb_user_digest_hash character varying(100) DEFAULT 'MD5';

ALTER TABLE mb_user ALTER COLUMN mb_user_password TYPE varchar(255);

ALTER TABLE mb_user ADD COLUMN create_digest boolean DEFAULT false;

--set all current users default to be active
--UPDATE mb_user SET is_active = true;

ALTER TABLE mb_group ADD COLUMN searchable BOOLEAN DEFAULT true;

-- Column: md_topic_category_description_de

-- ALTER TABLE md_topic_category DROP COLUMN md_topic_category_description_de;

ALTER TABLE md_topic_category ADD COLUMN md_topic_category_description_de text default 'TODO: Beschreibung einfügen';
