INSERT INTO gui_gui_category (fkey_gui_id, fkey_gui_category_id) VALUES ('${default_gui_name}', 2);
INSERT INTO gui_gui_category (fkey_gui_id, fkey_gui_category_id) VALUES ('Administration_DE', 2);
INSERT INTO gui_gui_category (fkey_gui_id, fkey_gui_category_id) VALUES ('PortalAdmin_DE', 2);


INSERT INTO gui_mb_user (fkey_gui_id, fkey_mb_user_id, mb_user_type) VALUES ('PortalAdmin_DE', 1, 'owner');
INSERT INTO gui_mb_user (fkey_gui_id, fkey_mb_user_id, mb_user_type) VALUES ('${default_gui_name}', 1, 'owner');
INSERT INTO gui_mb_user (fkey_gui_id, fkey_mb_user_id, mb_user_type) VALUES ('Administration_DE', 1, 'owner');
INSERT INTO gui_mb_user (fkey_gui_id, fkey_mb_user_id, mb_user_type) VALUES ('Owsproxy_csv', 1, 'owner');
INSERT INTO gui_mb_user (fkey_gui_id, fkey_mb_user_id, mb_user_type) VALUES ('${extended_search_default_gui_name}', 1, 'owner');

INSERT INTO gui_mb_user (fkey_gui_id, fkey_mb_user_id, mb_user_type) VALUES ('admin_wms_metadata', 1, 'owner');
INSERT INTO gui_mb_user (fkey_gui_id, fkey_mb_user_id, mb_user_type) VALUES ('admin_wfs_metadata', 1, 'owner');
INSERT INTO gui_mb_user (fkey_gui_id, fkey_mb_user_id, mb_user_type) VALUES ('admin_wmc_metadata', 1, 'owner');
INSERT INTO gui_mb_user (fkey_gui_id, fkey_mb_user_id, mb_user_type) VALUES ('admin_metadata', 1, 'owner');

INSERT INTO gui_mb_user (fkey_gui_id, fkey_mb_user_id, mb_user_type) VALUES ('admin_ows_scheduler', 1, 'owner');

INSERT INTO gui_mb_user (fkey_gui_id, fkey_mb_user_id, mb_user_type) VALUES ('service_container1', ${mapbender_subadmin_default_user_id}, 'owner');
INSERT INTO gui_mb_user (fkey_gui_id, fkey_mb_user_id, mb_user_type) VALUES ('service_container1_free', ${mapbender_subadmin_default_user_id}, 'owner');

INSERT INTO gui_mb_group (fkey_gui_id, fkey_mb_group_id) VALUES ('Administration_DE', ${mapbender_subadmin_group_id});
INSERT INTO gui_mb_group (fkey_gui_id, fkey_mb_group_id) VALUES ('Owsproxy_csv', ${mapbender_subadmin_group_id});

INSERT INTO gui_mb_group (fkey_gui_id, fkey_mb_group_id) VALUES ('admin_wmc_metadata', ${mapbender_subadmin_group_id});
INSERT INTO gui_mb_group (fkey_gui_id, fkey_mb_group_id) VALUES ('admin_wms_metadata', ${mapbender_subadmin_group_id});
INSERT INTO gui_mb_group (fkey_gui_id, fkey_mb_group_id) VALUES ('admin_wfs_metadata', ${mapbender_subadmin_group_id});
INSERT INTO gui_mb_group (fkey_gui_id, fkey_mb_group_id) VALUES ('admin_ows_scheduler', ${mapbender_subadmin_group_id});
INSERT INTO gui_mb_group (fkey_gui_id, fkey_mb_group_id) VALUES ('admin_metadata', ${mapbender_subadmin_group_id});
--INSERT INTO gui_mb_group (fkey_gui_id, fkey_mb_group_id) VALUES ('admin1', ${mapbender_subadmin_group_id});


INSERT INTO gui_mb_group (fkey_gui_id, fkey_mb_group_id) VALUES ('${default_gui_name}', ${mapbender_guest_group_id});
INSERT INTO gui_mb_group (fkey_gui_id, fkey_mb_group_id) VALUES ('${extended_search_default_gui_name}', ${mapbender_guest_group_id});

INSERT INTO gui_mb_group (fkey_gui_id, fkey_mb_group_id) VALUES ('service_container1_free', ${mapbender_guest_group_id});
--alter views to integrate real subadmin and guest user ids
-- View: groups_for_publishing

-- DROP VIEW groups_for_publishing;

CREATE OR REPLACE VIEW groups_for_publishing AS
 SELECT mb_group.mb_group_id AS fkey_mb_group_id,
    mb_group.mb_group_id,
    mb_group.mb_group_name,
    mb_group.mb_group_owner,
    mb_group.mb_group_description,
    mb_group.mb_group_title,
    mb_group.mb_group_ext_id,
    mb_group.mb_group_address,
    mb_group.mb_group_postcode,
    mb_group.mb_group_city,
    mb_group.mb_group_stateorprovince,
    mb_group.mb_group_country,
    mb_group.mb_group_voicetelephone,
    mb_group.mb_group_facsimiletelephone,
    mb_group.mb_group_email,
    mb_group.mb_group_logo_path,
    mb_group.mb_group_homepage,
    mb_group.mb_group_admin_code,
    mb_group.timestamp_create,
    mb_group."timestamp",
    mb_group.mb_group_address_location,
    mb_group.uuid,
    mb_group.mb_group_ckan_uuid,
    mb_group.mb_group_ckan_api_key
   FROM mb_group
  WHERE (mb_group.mb_group_id IN ( SELECT DISTINCT f.fkey_mb_group_id
           FROM mb_user_mb_group f,
            mb_user_mb_group s
          WHERE (f.mb_user_mb_group_type = ANY (ARRAY[2, 3])) AND s.fkey_mb_group_id = ${mapbender_subadmin_group_id} AND f.fkey_mb_user_id = s.fkey_mb_user_id));

  ALTER TABLE groups_for_publishing
    OWNER TO postgres;
  GRANT ALL ON TABLE groups_for_publishing TO postgres;
  GRANT ALL ON TABLE groups_for_publishing TO $mapbender_database_user;

  ALTER TABLE groups_for_publishing
    OWNER TO postgres;
  GRANT ALL ON TABLE groups_for_publishing TO postgres;
  GRANT ALL ON TABLE groups_for_publishing TO $mapbender_database_user;

  -- View: registrating_groups

  -- DROP VIEW registrating_groups;

  CREATE OR REPLACE VIEW registrating_groups AS
   SELECT f.fkey_mb_group_id,
      f.fkey_mb_user_id
     FROM mb_user_mb_group f,
      mb_user_mb_group s
    WHERE f.mb_user_mb_group_type = 2 AND s.fkey_mb_group_id = ${mapbender_subadmin_group_id} AND f.fkey_mb_user_id = s.fkey_mb_user_id
    ORDER BY f.fkey_mb_group_id, f.fkey_mb_user_id;

  ALTER TABLE registrating_groups
    OWNER TO postgres;
  GRANT ALL ON TABLE registrating_groups TO postgres;
  GRANT ALL ON TABLE registrating_groups TO $mapbender_database_user;

  -- View: users_for_publishing

  -- DROP VIEW users_for_publishing;

  CREATE OR REPLACE VIEW users_for_publishing AS
   SELECT DISTINCT f.fkey_mb_user_id,
      f.fkey_mb_group_id AS primary_group_id
     FROM mb_user_mb_group f,
      mb_user_mb_group s
    WHERE f.mb_user_mb_group_type = 2 AND s.fkey_mb_group_id = ${mapbender_subadmin_group_id} AND f.fkey_mb_user_id = s.fkey_mb_user_id
    ORDER BY f.fkey_mb_user_id;

  ALTER TABLE users_for_publishing
    OWNER TO postgres;
  GRANT ALL ON TABLE users_for_publishing TO postgres;
  GRANT ALL ON TABLE users_for_publishing TO $mapbender_database_user;

  --add csw information

  INSERT INTO cat VALUES (1, '2.0.2', 'GeoDatenKatalog.De', 'Bereitstellung des Geodatenkatalog.de der GeoDatenInfrastruktur Deutschland (GDI-DE)', 'http://gdk.gdi-de.org/gdi-de/srv/eng/csw?SERVICE=CSW&VERSION=2.0.2&REQUEST=GetCapabilities', 'none', 'none', NULL, NULL, 'admin admin', 'Administrator', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, 'iserted via sql - no caps available!', 5299, 1502980960);

  INSERT INTO cat_op_conf VALUES (1, 'get', 'http://ims.geoportal.de/gdi-de/srv/eng/csw', 'getcapabilities');
  INSERT INTO cat_op_conf VALUES (1, 'post', 'http://ims.geoportal.de/gdi-de/srv/eng/csw', 'getcapabilities');
  INSERT INTO cat_op_conf VALUES (1, 'post_xml', 'http://ims.geoportal.de/gdi-de/srv/eng/csw', 'getcapabilities');
  INSERT INTO cat_op_conf VALUES (1, 'get', 'http://ims.geoportal.de/gdi-de/srv/eng/csw', 'describerecord');
  INSERT INTO cat_op_conf VALUES (1, 'post', 'http://ims.geoportal.de/gdi-de/srv/eng/csw', 'describerecord');
  INSERT INTO cat_op_conf VALUES (1, 'post_xml', 'http://ims.geoportal.de/gdi-de/srv/eng/csw', 'describerecord');
  INSERT INTO cat_op_conf VALUES (1, 'get', 'http://ims.geoportal.de/gdi-de/srv/eng/csw', 'getdomain');
  INSERT INTO cat_op_conf VALUES (1, 'post', 'http://ims.geoportal.de/gdi-de/srv/eng/csw', 'getdomain');
  INSERT INTO cat_op_conf VALUES (1, 'get', 'http://ims.geoportal.de/gdi-de/srv/eng/csw-publication', 'transaction');
  INSERT INTO cat_op_conf VALUES (1, 'post', 'http://ims.geoportal.de/gdi-de/srv/eng/csw-publication', 'transaction');
  INSERT INTO cat_op_conf VALUES (1, 'get', 'http://ims.geoportal.de/gdi-de/srv/eng/csw-publication', 'harvest');
  INSERT INTO cat_op_conf VALUES (1, 'post', 'http://ims.geoportal.de/gdi-de/srv/eng/csw-publication', 'harvest');
  INSERT INTO cat_op_conf VALUES (1, 'post', 'https://gdk.gdi-de.org/gdi-de/srv/eng/csw', 'getrecords');
  INSERT INTO cat_op_conf VALUES (1, 'post_xml', 'https://gdk.gdi-de.org/gdi-de/srv/eng/csw', 'getrecords');
  INSERT INTO cat_op_conf VALUES (1, 'get', 'https://gdk.gdi-de.org/gdi-de/srv/eng/csw', 'getrecordbyid');
  INSERT INTO cat_op_conf VALUES (1, 'post_xml', 'https://gdk.gdi-de.org/gdi-de/srv/eng/csw', 'getrecordbyid');
  INSERT INTO cat_op_conf VALUES (1, 'get', 'https://gdk.gdi-de.org/gdi-de/srv/eng/csw', 'getrecords');
  INSERT INTO cat_op_conf VALUES (1, 'post', 'https://gdk.gdi-de.org/gdi-de/srv/eng/csw', 'getrecordbyid');


  CREATE TABLE gp_csw (
      csw_id integer,
      csw_name text,
      fkey_cat_id integer,
      csw_p integer,
      csw_h integer,
      hierachylevel character(50)
  );


  ALTER TABLE gp_csw OWNER TO postgres;
  GRANT ALL ON TABLE gp_csw TO $mapbender_database_user;

  INSERT INTO gp_csw VALUES (5, 'GeodatenkatalogDE - Sonstige Informationen', 1, 1, 5, 'nonGeographicDataset');
  INSERT INTO gp_csw VALUES (3, 'GeodatenkatalogDE - Dienste', 1, 1, 5, 'service');
  INSERT INTO gp_csw VALUES (4, 'GeodatenkatalogDE - Anwendungen', 1, 1, 5, 'application');
  INSERT INTO gp_csw VALUES (2, 'GeodatenkatalogDE - Geodaten', 1, 1, 5, 'dataset/series');