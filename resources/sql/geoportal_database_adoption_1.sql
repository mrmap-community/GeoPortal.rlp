--create new database content
  --geoportal specific extensions
  ALTER TABLE mb_user ADD COLUMN mb_user_glossar character varying(5);
  --ALTER TABLE mb_user ADD COLUMN mb_user_glossar character varying(14);
  --ALTER TABLE mb_user ADD COLUMN mb_user_textsize character varying(14);
  ALTER TABLE mb_user ADD COLUMN mb_user_textsize character varying(14);
  ALTER TABLE mb_user ADD COLUMN mb_user_last_login_date date;
  ALTER TABLE mb_user ADD COLUMN mb_user_spatial_suggest character varying(5);

  UPDATE gui_category SET category_name='Anwendung' WHERE category_id=2;
  UPDATE gui_category SET category_description='Anwendungen (Applications)' WHERE category_id=2;

  --add anonymous user
  INSERT INTO mb_user (mb_user_id, mb_user_name, mb_user_password, mb_user_owner, mb_user_description, mb_user_login_count, mb_user_email, mb_user_phone, mb_user_department, mb_user_resolution, mb_user_organisation_name, mb_user_position_name, mb_user_phone1, mb_user_facsimile, mb_user_delivery_point, mb_user_city, mb_user_postal_code, mb_user_country, mb_user_online_resource, mb_user_textsize, mb_user_glossar, mb_user_last_login_date, mb_user_digest, mb_user_realname, mb_user_street, mb_user_housenumber, mb_user_reference, mb_user_for_attention_of, mb_user_valid_from, mb_user_valid_to, mb_user_password_ticket, mb_user_firstname, mb_user_lastname, mb_user_academictitle, timestamp_create, timestamp, mb_user_spatial_suggest, mb_user_newsletter, mb_user_allow_survey, mb_user_aldigest, password, is_active, activation_key, timestamp_delete) VALUES (${mapbender_guest_user_id}, 'guest', '084e0343a0486ff05530df6c705c8bb4', 1, 'test', 0, 'kontakt@geoportal.rlp.de', NULL, '', 72, '', '', NULL, NULL, NULL, '', NULL, NULL, NULL, 'textsize3', 'ja', '2012-01-26', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2013-07-05 08:09:25.560359', '2015-08-20 10:04:04.952796', 'nein', true, true, NULL, '\$2b\$12\$sT7geeXvBlGZyLcT55VxqeNF2yuU8LKnBpfKFwxkiAh147mNxF5Cq',true,'',NULL);

  INSERT INTO mb_user (mb_user_id, mb_user_name, mb_user_password, mb_user_owner, mb_user_description, mb_user_login_count, mb_user_email, mb_user_phone, mb_user_department, mb_user_resolution, mb_user_organisation_name, mb_user_position_name, mb_user_phone1, mb_user_facsimile, mb_user_delivery_point, mb_user_city, mb_user_postal_code, mb_user_country, mb_user_online_resource, mb_user_textsize, mb_user_glossar, mb_user_last_login_date, mb_user_digest, mb_user_realname, mb_user_street, mb_user_housenumber, mb_user_reference, mb_user_for_attention_of, mb_user_valid_from, mb_user_valid_to, mb_user_password_ticket, mb_user_firstname, mb_user_lastname, mb_user_academictitle, timestamp_create, timestamp, mb_user_spatial_suggest, mb_user_newsletter, mb_user_allow_survey, mb_user_aldigest, password, is_active, activation_key, timestamp_delete) VALUES (${mapbender_subadmin_default_user_id}, 'bereichsadmin1', '3ad58afdc417b975256af7a6d3eda7a5', 1, '', 0, 'kontakt@geoportal.rlp.de', NULL, NULL, 72, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'nein', '2017-07-28', '3c345c2af80400e1e4c94ed0a967e713', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'bereichsadmin1', 'bereichsadmin1', '', '2013-07-05 08:09:25.560359', '2017-07-28 10:12:13.926954', 'nein', false, false, '2a32c845b23d82bea4653810f146397b', '\$2b\$12\$hkhs1s4LrPTNWeZaTHAS5.G63JZSVCmc7xUpaYrdVTAxgeeFe1YM6',true,'',NULL);

  INSERT INTO mb_group VALUES (${mapbender_subadmin_group_id}, 'Bereichsadmin', 1, 'Diensteadministratoren der Behörden', '', NULL, '', '', '', '', '', '', '', '', '', NULL, NULL, '2013-07-05 08:09:25.732456', '2018-05-25 08:57:07.988259', NULL, NULL, NULL, NULL, NULL, NULL, true);

  INSERT INTO mb_group VALUES (${mapbender_guest_group_id}, 'guest', 1, 'Gastgruppe', '', NULL, '', '', '', '', '', '', '', '', '', NULL, NULL, '2013-07-05 08:09:25.732456', '2018-05-25 08:57:07.988259', NULL, NULL, NULL, NULL, NULL, NULL, true);

  INSERT INTO mb_group VALUES (${mapbender_subadmin_default_group_id}, 'testgruppe1', 1, 'testgruppe1', 'testgruppe1', NULL, 'Musterstraße 11', '11111 Musterstadt', 'Musterstadt', 'DE-RP', 'DE', '1111', '1111', 'mustermail@musterdomain.com', 'http://www.geoportal.rlp.de/metadata/GDI-RP_mit_Markenschutz_RGB_70.png', NULL, NULL, '2013-07-05 08:09:25.732456', '2018-05-25 08:57:07.988259', NULL, NULL, NULL, NULL, NULL, NULL, true);

  --guest user into guest group
  INSERT INTO mb_user_mb_group VALUES (${mapbender_guest_user_id}, ${mapbender_guest_group_id}, 1);

  --bereichsadmin1 into guest group
  INSERT INTO mb_user_mb_group VALUES (${mapbender_subadmin_default_user_id}, ${mapbender_guest_group_id}, 1);

  --bereichsadmin1 into Bereichsadmin group
  INSERT INTO mb_user_mb_group VALUES (${mapbender_subadmin_default_user_id}, ${mapbender_subadmin_group_id}, 1);

  --bereichsadmin1 into testgruppe1 group - role primary
  INSERT INTO mb_user_mb_group VALUES (${mapbender_subadmin_default_user_id}, ${mapbender_subadmin_default_group_id}, 2);

  --bereichsadmin1 into testgruppe1 group - role standard
  INSERT INTO mb_user_mb_group VALUES (${mapbender_subadmin_default_user_id}, ${mapbender_subadmin_default_group_id}, 1);

  --root into guest group
  INSERT INTO mb_user_mb_group VALUES (1, ${mapbender_guest_group_id}, 1);

  --guis: Geoportal-RLP, Geoportal-RLP_erwSuche2, Administration_DE, Portal_Admin, Owsproxy_csv - admin_metadata fehlt noch!!!!

  INSERT INTO gui (gui_id, gui_name, gui_description, gui_public) VALUES ('service_container1', 'service_container1', 'service_container1', 1);

  INSERT INTO gui (gui_id, gui_name, gui_description, gui_public) VALUES ('service_container1_free', 'service_container1_free', 'service_container1_free', 1);

  --guis: Geoportal-RLP, Administration_DE, Owsproxy_csv, admin_metadata, .....
  DELETE FROM gui WHERE gui_id IN ('${default_gui_name}', 'Owsproxy_csv', 'admin_wms_metadata', 'admin_wfs_metadata', 'admin_wmc_metadata', 'admin_metadata', 'admin_ows_scheduler', 'PortalAdmin_DE', 'Administration_DE', '${extended_search_default_gui_name}');
