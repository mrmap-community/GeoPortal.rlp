ALTER TABLE mapbender.mb_user ADD COLUMN mb_user_new_password boolean;

UPDATE mapbender.mb_user SET mb_user_new_password = 'FALSE';
