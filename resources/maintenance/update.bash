#!/bin/bash
installation_folder="/data/"
geoportal_url="https://www.geoportal.rlp.de"


custom_update(){

    if [ -e ${installation_folder}"custom_files.txt" ];then
        if [ "$1" == "save" ];then
          input="${installation_folder}/custom_files.txt"
          while IFS= read -r line
          do
            directory=`echo $line | cut -d / -f 3-`
            filename=`echo $line | cut -d / -f 3- | rev | cut -d / -f -1 | rev`
            directory=${directory%$filename}

            mkdir -p /tmp/custom_files/$directory
            cp -a $line /tmp/custom_files/$directory
          done < "$input"
        fi

        if [ "$1" == "restore" ];then
            cp -a /tmp/custom_files/* ${installation_folder}
        fi
      fi


    if [ "$1" == "script" ];then

      while true; do
          read -p "Do you want to use a custom update script? Should lie under ${installation_folder}custom_update.sh y/n?" yn
          case $yn in
              [Yy]* ) source ${installation_folder}custom_update.sh;break;;
              [Nn]* ) break;;
              * ) echo "Please answer yes or no.";;
          esac
      done
    fi
}

check_settings(){
   missing_items=()

   cd /tmp/
   dottedname=`echo $1 | sed s/"\/"/"."/g`
   rm $dottedname

   if [ $2 == "django" ]; then
     wget https://raw.githubusercontent.com/mrmap-community/GeoPortal.rlp/master/$1 -O $dottedname
   fi

   if [ $2 == "mapbender" ]; then
     wget https://raw.githubusercontent.com/mrmap-community/Mapbender2.8/master/$1-dist -O $dottedname
   fi

   while IFS="" read -r p || [ -n "$p" ]
     do

        if [ $2 == "django" ]; then
          h=`printf '%s\n' "$p" | cut -d = -f 1`
          h_full=`printf '%s\n' "$p"`

          if ! grep -Fq "$h" ${installation_folder}/GeoPortal.rlp/$1
          then
              missing_items+=("$h_full")
          fi
        fi

        if [ $2 == "mapbender" ]; then
          h=`printf '%s\n' "$p" | cut -d , -f 1`
          h_full=`printf '%s\n' "$p"`
          if ! grep -Fq "$h" ${installation_folder}/mapbender/$1
          then
              missing_items+=("$h_full")
          fi
        fi

   done < /tmp/$dottedname

   if [ ${#missing_items[@]} -ne 0 ]; then
     echo "The following items are present in the masters $1 but are missing in your local $1"
     printf '%s\n' "${missing_items[@]}"

     while true; do
         read -p "Do you want to continue y/n?" yn
         case $yn in
             [Yy]* ) break;;
             [Nn]* ) exit;break;;
             * ) echo "Please answer yes or no.";;
         esac
     done
  fi

}


# needed for building new postgres python modules psycop2
apt-get update
apt-get install -y libpq-dev

custom_update "save"

echo "Checking differences in config files"
check_settings "Geoportal/settings.py" "django"
check_settings "searchCatalogue/settings.py" "django"
check_settings "conf/mapbender.conf" "mapbender"

#update mapbender
mkdir ${installation_folder}config_backup_for_update/
echo "Backing up Mapbender Configs"
cp -av ${installation_folder}mapbender/conf/mapbender.conf ${installation_folder}config_backup_for_update/mapbender.conf_$(date +"%m_%d_%Y")
cp -av ${installation_folder}mapbender/conf/geoportal.conf ${installation_folder}config_backup_for_update/geoportal.conf_$(date +"%m_%d_%Y")
cp -av ${installation_folder}mapbender/tools/wms_extent/extents.map ${installation_folder}config_backup_for_update/extents_geoportal_rlp.map_$(date +"%m_%d_%Y")
cp -av ${installation_folder}mapbender/tools/wms_extent/extent_service.conf ${installation_folder}config_backup_for_update/extent_service_geoportal_rlp.conf_$(date +"%m_%d_%Y")
cp -av ${installation_folder}mapbender/http/extensions/mobilemap2/scripts/netgis/config.js ${installation_folder}config_backup_for_update/config.js_$(date +"%m_%d_%Y")
cp -av ${installation_folder}mapbender/conf/atomFeedClient.conf ${installation_folder}config_backup_for_update/atomFeedClient.conf_$(date +"%m_%d_%Y")
cp -av ${installation_folder}mapbender/conf/ckan.conf ${installation_folder}config_backup_for_update/ckan.conf_$(date +"%m_%d_%Y")
cp -av ${installation_folder}mapbender/conf/mobilemap2.conf ${installation_folder}config_backup_for_update/mobilemap2.conf_$(date +"%m_%d_%Y")
cp -av ${installation_folder}mapbender/conf/linkedDataProxy.json ${installation_folder}config_backup_for_update/linkedDataProxy.json_$(date +"%m_%d_%Y")
cp -av ${installation_folder}mapbender/conf/twitter.conf ${installation_folder}config_backup_for_update/twitter.conf_$(date +"%m_%d_%Y")
cp -av ${installation_folder}mapbender/conf/bkgGeocoding.conf ${installation_folder}config_backup_for_update/bkgGeocoding.conf_$(date +"%m_%d_%Y")
cp -av ${installation_folder}mapbender/conf/excludeproxyurls.conf ${installation_folder}config_backup_for_update/excludeproxyurls.conf_$(date +"%m_%d_%Y")
cp -av ${installation_folder}mapbender/conf/mobilemap.conf ${installation_folder}config_backup_for_update/mobilemap.conf_$(date +"%m_%d_%Y")
cp -av ${installation_folder}mapbender/conf/excludeHarvestMetadataUrls.json ${installation_folder}config_backup_for_update/excludeHarvestMetadataUrls.json_$(date +"%m_%d_%Y")

echo "Updating Mapbender Sources"
cd ${installation_folder}svn/mapbender
su -c 'git reset --hard'
su -c 'git pull'
cp -a ${installation_folder}svn/mapbender ${installation_folder}

echo "Restoring Mapbender Configs"
cp -av ${installation_folder}config_backup_for_update/mapbender.conf_$(date +"%m_%d_%Y") ${installation_folder}mapbender/conf/mapbender.conf
cp -av ${installation_folder}config_backup_for_update/geoportal.conf_$(date +"%m_%d_%Y") ${installation_folder}mapbender/conf/geoportal.conf
cp -av ${installation_folder}config_backup_for_update/extents_geoportal_rlp.map_$(date +"%m_%d_%Y") ${installation_folder}mapbender/tools/wms_extent/extents.map
cp -av ${installation_folder}config_backup_for_update/extent_service_geoportal_rlp.conf_$(date +"%m_%d_%Y") ${installation_folder}mapbender/tools/wms_extent/extent_service.conf
cp -av ${installation_folder}config_backup_for_update/config.js_$(date +"%m_%d_%Y") ${installation_folder}mapbender/http/extensions/mobilemap2/scripts/netgis/config.js
cp -av ${installation_folder}config_backup_for_update/atomFeedClient.conf_$(date +"%m_%d_%Y") ${installation_folder}mapbender/conf/atomFeedClient.conf
cp -av ${installation_folder}config_backup_for_update/ckan.conf_$(date +"%m_%d_%Y") ${installation_folder}mapbender/conf/ckan.conf
cp -av ${installation_folder}config_backup_for_update/mobilemap2.conf_$(date +"%m_%d_%Y") ${installation_folder}mapbender/conf/mobilemap2.conf
cp -av ${installation_folder}config_backup_for_update/linkedDataProxy.json_$(date +"%m_%d_%Y") ${installation_folder}mapbender/conf/linkedDataProxy.json
cp -av ${installation_folder}config_backup_for_update/twitter.conf_$(date +"%m_%d_%Y") ${installation_folder}mapbender/conf/twitter.conf
cp -av ${installation_folder}config_backup_for_update/bkgGeocoding.conf_$(date +"%m_%d_%Y") ${installation_folder}mapbender/conf/bkgGeocoding.conf
cp -av ${installation_folder}config_backup_for_update/excludeproxyurls.conf_$(date +"%m_%d_%Y") ${installation_folder}mapbender/conf/excludeproxyurls.conf
cp -av ${installation_folder}config_backup_for_update/mobilemap.conf_$(date +"%m_%d_%Y") ${installation_folder}mapbender/conf/mobilemap.conf
cp -av ${installation_folder}config_backup_for_update/excludeHarvestMetadataUrls.json_$(date +"%m_%d_%Y") ${installation_folder}mapbender/conf/excludeHarvestMetadataUrls.json

cd ${installation_folder}mapbender/tools
sh ./i18n_update_mo.sh
cd ${installation_folder}
chown -R www-data ${installation_folder}mapbender/http/tmp/
chown -R www-data ${installation_folder}mapbender/log/
chown -R www-data ${installation_folder}mapbender/http/geoportal/preview/
chown -R www-data ${installation_folder}mapbender/http/geoportal/news/
chown -R www-data ${installation_folder}mapbender/metadata/
chown -R www-data ${installation_folder}mapbender/http/extensions/mobilemap2/
#set read possibility for locales
chmod -R 755 ${installation_folder}mapbender/resources/locale/
#cause the path of the login script has another path than the relative pathes must be adopted:
#sed -i "s/\/..\/php\/mod_showMetadata.php?/\/..\/..\/mapbender\/php\/mod_showMetadata.php?/g" /data/mapbender/http/classes/class_wms.php
sed -i "s/LOGIN.\"\/..\/..\/php\/mod_showMetadata.php?resource=layer\&id=\"/str_replace(\"portal\/anmelden.html\",\"\",LOGIN).\"layer\/\"/g" ${installation_folder}mapbender/http/classes/class_wms.php
sed -i "s/LOGIN.\"\/..\/..\/php\/mod_showMetadata.php?resource=wms\&id=\"/str_replace(\"portal\/anmelden.html\",\"\",LOGIN).\"wms\/\"/g" ${installation_folder}mapbender/http/classes/class_wms.php
#overwrite login url with baseurl for export to openlayers link
sed -i 's/url = url.replace("http\/frames\/login.php", "");/url = Mapbender.baseUrl + "\/mapbender\/";/g' ${installation_folder}mapbender/http/javascripts/mod_loadwmc.js
sed -i "s#href = 'http:\/\/www.mapbender.org'#href = '${geoportal_url}'#g" ${installation_folder}mapbender/http/php/mod_wmc2ol.php
sed -i 's/Mapbender_logo_and_text.png/logo_geoportal_neu.png/g' ${installation_folder}mapbender/http/php/mod_wmc2ol.php
sed -i 's/$maxResults = 5;/$maxResults = 20;/' ${installation_folder}mapbender/http/php/mod_callMetadata.php
sed -i "s#//metadataUrlPlaceholder#\$metadataUrl=\"${geoportal_url}/layer/\";#" ${installation_folder}/svn/mapbender/http/php/mod_abo_show.php
sed -i "s#http://ws.geonames.org/searchJSON?lang=de&#${geoportal_url}/mapbender/geoportal/gaz_geom_mobile.php#" ${installation_folder}/svn/mapbender/http/plugins/mod_jsonAutocompleteGazetteer.php
sed -i 's/options.isGeonames = true;/options.isGeonames = false;/' ${installation_folder}mapbender/http/plugins/mod_jsonAutocompleteGazetteer.php
sed -i 's/options.helpText = "";/options.helpText = "Orts- und Straßennamen sind bei der Adresssuche mit einem Komma voneinander zu trennen!<br><br>Auch Textfragmente der gesuchten Adresse reichen hierbei aus.<br><br>\&nbsp\&nbsp\&nbsp\&nbsp Beispiel:<br>\&nbsp\&nbsp\&nbsp\&nbsp\&nbsp\\"Am Zehnthof 10 , St. Goar\\" oder<br>\&nbsp\&nbsp\&nbsp\&nbsp\&nbsp\\"zehnt 10 , goar\\"<br><br>Der passende Treffer muss in der erscheinenden Auswahlliste per Mausklick ausgewählt werden!";/' ${installation_folder}mapbender/http/plugins/mod_jsonAutocompleteGazetteer.php
sed -i "s/#define(\"LOGIN\", \"http:\/\/\".\$_SERVER\['HTTP_HOST'\].\"\/mapbender\/frames\/login.php\");/define(\"LOGIN\", \"http:\/\/\".\$_SERVER\['HTTP_HOST'\].\"\/mapbender\/frames\/login.php\");/g" ${installation_folder}mapbender/conf/mapbender.conf
sed -i "s/define(\"LOGIN\", \"http:\/\/\".\$_SERVER\['HTTP_HOST'\].\"\/portal\/anmelden.html\");/#define(\"LOGIN\", \"http:\/\/\".\$_SERVER\['HTTP_HOST'\].\"\/portal\/anmelden.html\");/g" ${installation_folder}mapbender/conf/mapbender.conf


echo "Mapbender Update Done"

#update django
echo "Updating Geoportal Project"
cd ${installation_folder}GeoPortal.rlp
echo "Backing up Django Configs"
cp -av ${installation_folder}GeoPortal.rlp/Geoportal/settings.py ${installation_folder}config_backup_for_update/settings.py_$(date +"%m_%d_%Y")
cp -av ${installation_folder}GeoPortal.rlp/useroperations/conf.py ${installation_folder}config_backup_for_update/useroperations_conf.py_$(date +"%m_%d_%Y")
cp -av ${installation_folder}GeoPortal.rlp/searchCatalogue/settings.py ${installation_folder}config_backup_for_update/searchCatalogue_settings.py_$(date +"%m_%d_%Y")

git reset --hard
git pull

echo "Restoring Django Configs"
cp -av ${installation_folder}config_backup_for_update/settings.py_$(date +"%m_%d_%Y") ${installation_folder}GeoPortal.rlp/Geoportal/settings.py
cp -av ${installation_folder}config_backup_for_update/useroperations_conf.py_$(date +"%m_%d_%Y") ${installation_folder}GeoPortal.rlp/useroperations/conf.py
cp -av ${installation_folder}config_backup_for_update/searchCatalogue_settings.py_$(date +"%m_%d_%Y") ${installation_folder}GeoPortal.rlp/searchCatalogue/settings.py

# copy some scripts that are needed for django mapbender integration
cp -av ${installation_folder}GeoPortal.rlp/resources/scripts/guiapi.php ${installation_folder}mapbender/http/local/
cp -av ${installation_folder}GeoPortal.rlp/resources/sql/delete_inactive_users.sql ${installation_folder}mapbender/resources/db/delete_inactive_users.sql
#only needed if multi download should be enabled
#cp -a ${installation_folder}GeoPortal.rlp/resources/scripts/mb_downloadFeedClient/javascripts/mb_downloadFeedClient.php ${installation_folder}mapbender/http/javascripts/mb_downloadFeedClient.php
#cp -a ${installation_folder}GeoPortal.rlp/resources/scripts/mb_downloadFeedClient/plugins/mb_downloadFeedClient.php ${installation_folder}mapbender/http/plugins/mb_downloadFeedClient.php
#cp -a ${installation_folder}GeoPortal.rlp/resources/scripts/mb_downloadFeedClient/move.png ${installation_folder}mapbender/http/extensions/OpenLayers-2.13.1/img/
#cp -a ${installation_folder}GeoPortal.rlp/resources/scripts/mb_downloadFeedClient/select.png ${installation_folder}mapbender/http/extensions/OpenLayers-2.13.1/img/
#cp -a ${installation_folder}GeoPortal.rlp/resources/scripts/mb_downloadFeedClient/style.css ${installation_folder}mapbender/http/extensions/OpenLayers-2.13.1/theme/default/
#cp -a ${installation_folder}GeoPortal.rlp/resources/scripts/mb_downloadFeedClient/OpenLayers.js ${installation_folder}mapbender/http/extensions/OpenLayers-2.13.1/

# restore custom Files
custom_update "restore"
# this can used be to do some special tasks that may be needed by other users than rlp, eg. copy files that are overwritten by the update from the customconfig folder to another location
# custom_update "script"
# create and activate virtualenv
rm -r ${installation_folder}env
virtualenv -ppython3 ${installation_folder}env
source ${installation_folder}env/bin/activate
# install needed python packages
cd ${installation_folder}GeoPortal.rlp
pip install -r requirements.txt
rm -r ${installation_folder}GeoPortal.rlp/static
python manage.py collectstatic
python manage.py compilemessages
/etc/init.d/apache2 restart
echo "Update Complete"
