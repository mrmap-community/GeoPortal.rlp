select * into wms_search_table_tmp from search_wms_view;


-- DROP TABLE wms_search_table;

ALTER TABLE wms_search_table_tmp RENAME TO  wms_search_table;

UPDATE wms_search_table SET load_count=0 WHERE load_count is NULL;

-- Index: gist_wst_the_geom

-- DROP INDEX gist_wst_the_geom;

CREATE INDEX gist_wst_the_geom
  ON wms_search_table
  USING gist
  (the_geom);

-- Index: idx_wst_department

-- DROP INDEX idx_wst_department;

CREATE INDEX idx_wst_department
  ON wms_search_table
  USING btree
  (department);
-- Index: idx_wst_md_topic_cats

-- DROP INDEX idx_wst_md_topic_cats;

CREATE INDEX idx_wst_md_topic_cats
  ON wms_search_table
  USING btree
  (md_topic_cats);
-- Index: idx_wst_layer_id

-- DROP INDEX idx_wst_layer_id;

CREATE INDEX idx_wst_layer_id
  ON wms_search_table
  USING btree
  (layer_id);

-- Index: idx_wst_load_count

-- DROP INDEX idx_wst_load_count;

CREATE INDEX idx_wst_load_count
  ON wms_search_table
  USING btree
  (load_count);
-- Index: idx_wst_searchtext

-- DROP INDEX idx_wst_searchtext;

CREATE INDEX idx_wst_searchtext
  ON wms_search_table
  USING btree
  (searchtext);

-- Index: idx_wst_wms_timestamp

-- DROP INDEX idx_wst_wms_timestamp;

CREATE INDEX idx_wst_wms_timestamp
  ON wms_search_table
  USING btree
  (wms_timestamp);
--vacuum analyze;
--VACUUM ANALYZE wms_search_table;
