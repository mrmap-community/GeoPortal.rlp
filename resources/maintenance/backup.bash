#!/bin/bash
installation_folder="/data/"
mysql_root_pw="root"


if [ -d ${installation_folder}backup/geoportal_backup_$(date +"%m_%d_%Y") ]; then
  echo "I have found a Backup for today. You should remove or rename it if you want to use this function.
  Do something like: mv ${installation_folder}backup/geoportal_backup_$(date +"%m_%d_%Y") ${installation_folder}backup/geoportal_backup_$(date +"%m_%d_%Y")_old"
  exit
fi

echo "Creating backup in ${installation_folder}backup/geoportal_backup_$(date +"%m_%d_%Y")"
mkdir -pv ${installation_folder}backup/geoportal_backup_$(date +"%m_%d_%Y")/
mkdir -p ${installation_folder}backup/geoportal_backup_$(date +"%m_%d_%Y")/mapbender/conf
mkdir -p ${installation_folder}backup/geoportal_backup_$(date +"%m_%d_%Y")/mapbender/http/extensions/mobilemap2/scripts/
mkdir -p ${installation_folder}backup/geoportal_backup_$(date +"%m_%d_%Y")/mapbender/tools/wms_extent/
mkdir -p ${installation_folder}backup/geoportal_backup_$(date +"%m_%d_%Y")/GeoPortal.rlp/Geoportal/
mkdir -p ${installation_folder}backup/geoportal_backup_$(date +"%m_%d_%Y")/GeoPortal.rlp/useroperations/

# Django Backup
cp -av ${installation_folder}GeoPortal.rlp/Geoportal/settings.py ${installation_folder}backup/geoportal_backup_$(date +"%m_%d_%Y")/GeoPortal.rlp/Geoportal/
cp -av ${installation_folder}GeoPortal.rlp/useroperations/conf.py ${installation_folder}backup/geoportal_backup_$(date +"%m_%d_%Y")/GeoPortal.rlp/useroperations/conf.py
# Mapbender config files
cp -av ${installation_folder}mapbender/conf/* ${installation_folder}backup/geoportal_backup_$(date +"%m_%d_%Y")/mapbender/conf/
cp -av ${installation_folder}mapbender/http/extensions/mobilemap2/scripts/netgis/config.js ${installation_folder}backup/geoportal_backup_$(date +"%m_%d_%Y")/mapbender/http/extensions/mobilemap2/scripts/
cp -av ${installation_folder}mapbender/tools/wms_extent/extent_service.conf ${installation_folder}backup/geoportal_backup_$(date +"%m_%d_%Y")/mapbender/tools/wms_extent/
cp -av ${installation_folder}mapbender/tools/wms_extent/extents.map ${installation_folder}backup/geoportal_backup_$(date +"%m_%d_%Y")/mapbender/tools/wms_extent/

while true; do
    read -p "Do you want to dump the databases (mysqlpw neeeded, postgres needs to be local with no pw from root)y/n?" yn
    case $yn in
        [Yy]* )
        su - postgres -c "pg_dump mapbender > /tmp/geoportal_mapbender_backup.psql";
        cp -a /tmp/geoportal_mapbender_backup.psql ${installation_folder}backup/geoportal_backup_$(date +"%m_%d_%Y");
        mysqldump -uroot -p$mysql_root_pw Geoportal > ${installation_folder}backup/geoportal_backup_$(date +"%m_%d_%Y")/geoportal_mediawiki_backup.mysql;
        break;;
        [Nn]* ) break;;
        * ) echo "Please answer yes or no.";;
    esac
done

echo "Backup Done!."
