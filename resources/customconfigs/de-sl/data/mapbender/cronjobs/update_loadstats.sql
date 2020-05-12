INSERT INTO mapbender.loadstat(load_count, title, abstract, type, date, fkey_id) SELECT 
  load_count, wmc_title, abstract ,'wmc', current_date, fkey_wmc_serial_id
FROM 
  mapbender.wmc_load_count, 
  mapbender.mb_user_wmc
WHERE 
  wmc_load_count.fkey_wmc_serial_id = mb_user_wmc.wmc_serial_id;

DELETE FROM wmc_load_count;

INSERT INTO mapbender.loadstat(load_count, title, abstract, type, date, fkey_id) SELECT 
  load_count, layer_title, layer_abstract, 'layer', current_date, fkey_layer_id 
FROM 
  mapbender.layer_load_count, 
  mapbender.layer
WHERE 
  layer_load_count.fkey_layer_id = layer.layer_id;

DELETE FROM layer_load_count;
