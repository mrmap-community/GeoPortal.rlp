#!/bin/bash
############################################################
# Geoportal-RLP install script for debian 9 server environment
# 2019-02-25
# debian netinstall 9
# André Holl
# Armin Retterath
# Michel Peltriaux
############################################################

# Variables
ipaddress="127.0.0.1"
mysqlpw="root"
mode="none"

# mapbender/phppgadmin database config
mapbender_database_name="mapbender"
mapbender_database_port="5432"
mapbender_database_user="mapbenderdbuser"
mapbender_database_password="mapbenderdbpassword"
phppgadmin_user="postgresadmin"
phppgadmin_password="postgresadmin_password"

# mapbender specific stuff
mapbender_guest_user_id="2"
mapbender_guest_group_id="22"
mapbender_subadmin_group_id="21"
mapbender_subadmin_default_user_id="3"
mapbender_subadmin_default_group_id="23"

minx="289000"
miny="5423000"
maxx="465000"
maxy="5647000"
epsg="EPSG:25832"
bbox_current_epsg_csv="$minx,$miny,$maxx,$maxy"
bbox_current_epsg_space="$minx $miny $maxx $maxy"

############################################################
# define name of the default gui -
# don't use spaces, cause the gui names are used for filenames!!!! - TBD not already operabel!
#default_gui_name="Geoportal-Default-GUI"
#extended_search_default_gui_name="Geoportal-Extended-Search-GUI"
default_gui_name="Geoportal-RLP"
extended_search_default_gui_name="Geoportal-RLP_erwSuche2"
############################################################

# atomFeedClient.conf / config.js - mm2_config.js
center_x_i="385000"
center_y_i="5543000"
# config.js - mm2_config.js
initial_scale_i="1500000"
map_extent_csv=$bbox_wgs84

background_hybrid_tms_url="http://www.gdi-rp-dienste2.rlp.de/mapcache/tms/1.0.0/topplusbkg@UTM32"
background_aerial_wms_url="http://geo4.service24.rlp.de/wms/dop_basis.fcgi"

# proxy config
use_proxy_system="true"
use_proxy_svn="true"
use_proxy_apt="true"
use_proxy_mb="true"

http_proxy_host=""
http_proxy_port=""
https_proxy_host=""
https_proxy_port=""
not_proxy_hosts="localhost,127.0.0.1"

# misc
webadmin_email="test@test.de"
use_ssl="false"
domain_name="127.0.0.1"
not_proxy_hosts="localhost,127.0.0.1"
installation_folder="/data/"

# what should be done
install_system_packages="true"
create_folders="true"
checkout_mapbender_svn="true"
install_mapbender_source="true"
install_mapbender_database="true"
checkout_mapbender_conf="true"
install_mapbender_conf="true"
configure_mapbender="true"
configure_apache="true"
configure_cronjobs="true"
checkout_custom_svn="false"
custom_svn_url="http://www.gdi-rp-dienste.rlp.de/svn/de-rp-landau/data"

if [ $use_ssl = 'true' ]; then
    server_url="https://"$domain_name
fi
if [ $use_ssl = 'false' ]; then
    server_url="http://"$domain_name
fi

# mobilemap.conf
dhm_wms_url="http://www.gdi-rp-dienste2.rlp.de/cgi-bin/mapserv.fcgi?map=/data/umn/geoportal/dhm_query/dhm.map&"
catalogue_interface=$server_url"/mapbender/php/mod_callMetadata.php?"
background_wms_csv="1819,1382,1635"
bbox_wgs84="6.05 48.9 8.6 50.96"

# initial services
wms_1_url="'http://www.geoportal.rlp.de/mapbender/php/wms.php?layer_id=55468&REQUEST=GetCapabilities&VERSION=1.1.1&SERVICE=WMS'"
wms_2_url="'http://map.krz.de/cgi-bin/mapserv7?map=/opt/gdi/wms/overview_600.map&REQUEST=GetCapabilities&VERSION=1.1.1&SERVICE=wms'"
wms_3_url="'http://map.krz.de/cgi-bin/mapserv7?map=/opt/gdi/wms/overview_owl.map&VERSION=1.1.1&REQUEST=GetCapabilities&SERVICE=WMS'"
# demo wms
wms_4_url="'https://gis.mffjiv.rlp.de/cgi-bin/mapserv?map=/data/mapserver/mapfiles/institutions_0601.map&REQUEST=GetCapabilities&VERSION=1.1.1&SERVICE=WMS'"
##################### Geoportal-RLP
wms_1_register_cmd="/usr/bin/php -f ${installation_folder}mapbender/tools/registerOwsCli.php userId=1 guiId='$default_gui_name' serviceType='wms' serviceAccessUrl=$wms_1_url"
wms_2_register_cmd="/usr/bin/php -f ${installation_folder}mapbender/tools/registerOwsCli.php userId=1 guiId='$default_gui_name' serviceType='wms' serviceAccessUrl=$wms_2_url"
wms_3_register_cmd="/usr/bin/php -f ${installation_folder}mapbender/tools/registerOwsCli.php userId=1 guiId='$default_gui_name' serviceType='wms' serviceAccessUrl=$wms_3_url"
##################### Geoportal-RLP_erwSuche2
wms_4_register_cmd="/usr/bin/php -f ${installation_folder}mapbender/tools/registerOwsCli.php userId=1 guiId='$extended_search_default_gui_name' serviceType='wms' serviceAccessUrl=$wms_1_url"
wms_5_register_cmd="/usr/bin/php -f ${installation_folder}mapbender/tools/registerOwsCli.php userId=1 guiId='$extended_search_default_gui_name' serviceType='wms' serviceAccessUrl=$wms_2_url"
##################### demo service -
wms_6_register_cmd="/usr/bin/php -f ${installation_folder}mapbender/tools/registerOwsCli.php userId=3 guiId='service_container1_free' serviceType='wms' serviceAccessUrl=$wms_4_url"
############################################################

install_full(){

#+#+#+#+#+#+#+##+#+#+#+#+#+#+##+#+#+#+#+#+#+##+#+#+#+#+#+#+#
# Mapbender installation
#+#+#+#+#+#+#+##+#+#+#+#+#+#+##+#+#+#+#+#+#+##+#+#+#+#+#+#+#

date

if [ $use_proxy_svn = 'true' ]; then
    # set proxy env for wget from shell
    cp /etc/subversion/servers /etc/subversion/servers_backup_geoportal
fi
if [ $use_proxy_system = 'true' ]; then
    export http_proxy=http://$http_proxy_host:$http_proxy_port
    export https_proxy=http://$https_proxy_host:$https_proxy_port
    # for git access behind proxy
    # git config --global http.proxy http://$http_proxy_host:$http_proxy_port
    # git config --global https.proxy http://$https_proxy_host:$https_proxy_port
fi
if [ $use_proxy_apt = 'true' ]; then
    # for apt alter or create /etc/apt/apt.conf
    if [ -e "/etc/apt/apt.conf" ]; then
        echo "File exists"
	cp /etc/apt/apt.conf /etc/apt/apt.conf_backup_geoportal
	echo "Acquire::http::Proxy \"http://$http_proxy_host:$http_proxy_port\";" > /etc/apt/apt.conf
    else
        echo "File does not exist"
	touch /etc/apt/apt.conf
	echo "Acquire::http::Proxy \"http://$http_proxy_host:$http_proxy_port\";" > /etc/apt/apt.conf
    fi
    # first line should be: Acquire::http::Proxy "http://$http_proxy_host:$http_proxy_port";
    # for subversion alter /etc/subversion/servers - alter following lines
    # # http-proxy-host = defaultproxy.whatever.com
    # sed -i "s/# http-proxy-host = defaultproxy.whatever.com/http-proxy-host = $http_proxy_host/g" /etc/subversion/servers
    # sed -i "s/# http-proxy-port = 7000/http-proxy-port = $http_proxy_port/g" /etc/subversion/servers
    # # http-proxy-port = 7000
fi
############################################################
if [ $install_system_packages = 'true' ]; then
    ############################################################
    # install needed debian packages
    ############################################################
apt update
apt-get install -y php7.0-mysql libapache2-mod-php7.0 php7.0-pgsql php7.0-gd php7.0-curl php7.0-cli  php-gettext g++ make bison bzip2 unzip zip gdal-bin cgi-mapserver php-imagick mysql-server imagemagick locate postgresql postgis postgresql-9.6-postgis-2.3 mc zip unzip links w3m lynx arj xpdf dbview odt2txt ca-certificates oidentd gettext phppgadmin gkdebconf subversion subversion-tools memcached php-memcached php-memcache php-apcu php-apcu-bc curl libproj-dev libapache2-mod-security2
fi
# +655MB
# mysql root password: mysqlroot - normally debian-sys-maint
# after install - 1.9GB!!!
############################################################
# adopt php logging - only log errors
############################################################
cp /etc/php/7.0/cli/php.ini /etc/php/7.0/cli/php.ini_geoportal_backup
cp /etc/php/7.0/apache2/php.ini /etc/php/7.0/apache2/php.ini_geoportal_backup


sed -i "s/error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT/error_reporting = E_ERROR/g" /etc/php/7.0/cli/php.ini
sed -i "s/;error_log = php_errors.log/error_log = \/tmp\/php7_cli_errors\.log/g" /etc/php/7.0/cli/php.ini


sed -i "s/error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT/error_reporting = E_ERROR/g" /etc/php/7.0/apache2/php.ini
sed -i "s/;error_log = php_errors.log/error_log = \/tmp\/php7_apache_errors\.log/g" /etc/php/7.0/apache2/php.ini


# set some environment variables
############################################################
if [ $use_proxy_svn = 'true' ]; then
    # for subversion alter /etc/subversion/servers - alter following lines
    # # http-proxy-host = defaultproxy.whatever.com
    sed -i "s/# http-proxy-host = defaultproxy.whatever.com/http-proxy-host = $http_proxy_host/g" /etc/subversion/servers
    sed -i "s/# http-proxy-port = 7000/http-proxy-port = $http_proxy_port/g" /etc/subversion/servers
    # # http-proxy-port = 7000
fi
############################################################
if [ $create_folders = 'true' ]; then
    # initial installation of geoportal.rlp on debian 9
    ############################################################
    # create folder structure
    ############################################################
    mkdir $installation_folder
    mkdir ${installation_folder}svn/
    mkdir ${installation_folder}access/
fi
############################################################
# check out svn repositories initially
############################################################
cd ${installation_folder}svn/


if [ $checkout_mapbender_svn = 'true' ]; then
    svn co -q https://svn.osgeo.org/mapbender/trunk/mapbender
fi

if [ $checkout_mapbender_conf = 'true' ]; then
    echo "checkout mapbender conf folder for geoportal ..."
    svn co -q http://www.gdi-rp-dienste.rlp.de/svn/de-rp/data/conf
fi

if  [ $checkout_custom_svn = 'true' ]; then
	svn co -q $custom_svn_url
fi

############################################################
# adopt configuration files and copy them to right folder - if exists
############################################################
############################################################
# mapbender
############################################################
#cp /data/mapbender.conf /data/mapbender/conf/
#cp /data/geoportal.conf /data/mapbender/conf/
#cp /data/extents_geoportal_rlp.map /data/mapbender/tools/wms_extent/extents.map
#cp /data/extent_service_geoportal_rlp.conf /data/mapbender/tools/wms_extent/extent_service.conf


############################################################
# compress and create mapbender
############################################################
if [ $install_mapbender_source = 'true' ]; then
    cd ${installation_folder}svn/
    tar -czf mapbender_trunk.tar.gz mapbender/
    mv mapbender_trunk.tar.gz /tmp/
    cd ${installation_folder}
    mv /tmp/mapbender_trunk.tar.gz .
    tar -xzf mapbender_trunk.tar.gz
    svn info https://svn.osgeo.org/mapbender/trunk/mapbender | grep Revision | grep -Eo '[0-9]{1,}' > ${installation_folder}mapbender/lastinstalled
    rm mapbender_trunk.tar.gz
    echo 'done.'
fi

############################################################
# compress and create geoportal default conf from /data/conf
############################################################
if [ $install_mapbender_conf = 'true' ]; then
    echo "install default geoportal configuration files ..."
    cd ${installation_folder}svn/
    tar -czf conf.tar.gz conf/
    mv conf.tar.gz /tmp/
    cd ${installation_folder}
    mv /tmp/conf.tar.gz .
    tar -xzf conf.tar.gz
    svn info http://www.gdi-rp-dienste.rlp.de/svn/de-rp/data/conf | grep Revision | grep -Eo '[0-9]{1,}' > ${installation_folder}conf/lastinstalled
    rm conf.tar.gz
    echo 'done.'
fi

############################################################
# compress and create custom conf files
############################################################
if  [ $checkout_custom_svn = 'true' ]; then
    cd ${installation_folder}svn/
    tar -czf custom.tar.gz data/
    mv custom.tar.gz /tmp/
    cd ${installation_folder}
    mv /tmp/custom.tar.gz .
    tar -xzf custom.tar.gz
    cd ${installation_folder}data/conf/
    rm -rf $(find . -type d -name .svn)
    cd ${installation_folder}data/portal
    rm -rf $(find . -type d -name .svn)
    cd ${installation_folder}data/mapbender/
    rm -rf $(find . -type d -name .svn)
    cd ${installation_folder}
    cp -r -a ${installation_folder}data/conf ${installation_folder}
    cp -r -a ${installation_folder}data/portal ${installation_folder}
    cp -r -a ${installation_folder}data/mapbender ${installation_folder}
    rm -R ${installation_folder}data/
    #svn info $custom_svn_url"/conf" | grep Revision | grep -Eo '[0-9]{1,}' > ${installation_folder}conf/lastinstalled
    rm custom.tar.gz
    echo 'done.'
fi
############################################################
# cleanup .svn relicts
############################################################
echo -n 'delete .svn files ... '
if [ $install_mapbender_source = 'true' ]; then
    cd ${installation_folder}mapbender/
    rm -rf $(find . -type d -name .svn)
fi

############################################################
# configure and install mapbender
############################################################
if [ $create_folders = 'true' ]; then
    mkdir ${installation_folder}mapbender/http/tmp/wmc
fi

############################################################
# mapbender db
############################################################
if [ $install_mapbender_database = 'true' ]; then

  echo -n 'install mapbender database ... '
  # su postgres
  # createuser  -S -D -R -P $mapbender_database_user #mapbenderdbpassword
  # CREATE DATABASE yourdbname;
  # CREATE USER youruser WITH ENCRYPTED PASSWORD 'yourpass';
  # GRANT ALL PRIVILEGES ON DATABASE yourdbname TO youruser;

  su - postgres -c "dropdb -p $mapbender_database_port $mapbender_database_name"
  su - postgres -c "createdb -p $mapbender_database_port -E UTF8 $mapbender_database_name -T template0"

  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -c "DROP USER $mapbender_database_user"
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -c "CREATE USER $mapbender_database_user WITH ENCRYPTED PASSWORD '$mapbender_database_password'"

  # su -c - postgres "createlang plpgsql -d $mapbender_database_name" - not needed for debian 8+

  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -f /usr/share/postgresql/9.6/contrib/postgis-2.3/postgis.sql"
  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -f /usr/share/postgresql/9.6/contrib/postgis-2.3/spatial_ref_sys.sql"
  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -f /usr/share/postgresql/9.6/contrib/postgis-2.3/legacy.sql"
  su - postgres -c "PGOPTIONS='--client-min-messages=warning' psql -q -p $mapbender_database_port -d $mapbender_database_name -f /usr/share/postgresql/9.6/contrib/postgis-2.3/topology.sql"

  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'GRANT ALL PRIVILEGES ON DATABASE $mapbender_database_name TO $mapbender_database_user'"

  # su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'grant all on geometry_columns to $mapbender_database_user;'"
  #maybe new to postgis 2.x
  # su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'grant all on geography_columns to $mapbender_database_user;'"
  # su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'grant all on spatial_ref_sys to $mapbender_database_user;'"

  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'ALTER DATABASE $mapbender_database_name OWNER TO $mapbender_database_user'"

  #overwrite default pg_hba.conf of main - default cluster
  cp /etc/postgresql/9.6/main/pg_hba.conf /etc/postgresql/9.6/main/pg_hba.conf_backup
  #####################
cat << EOF > "/etc/postgresql/9.6/main/pg_hba.conf"
  # Database administrative login by Unix domain socket
  local   all             postgres                                peer

  # TYPE  DATABASE        USER            ADDRESS                 METHOD

  # "local" is for Unix domain socket connections only
  local   all             postgres                                peer
  local   $mapbender_database_name        $mapbender_database_user                        md5
  # IPv4 local connections:
  host    all             postgres        127.0.0.1/32            trust
  host    $mapbender_database_name             $mapbender_database_user 127.0.0.1/32            md5
  # IPv6 local connections:

  host    all             postgres        ::1/128                 trust
  host    $mapbender_database_name             $mapbender_database_user ::1/128                 md5
EOF

  #####################
  service postgresql restart
  #####################
  cp /etc/phppgadmin/config.inc.php /etc/phppgadmin/config.inc.php_geoportal_backup
  sed -i "s/conf\['extra_login_security'\] = true/conf\['extra_login_security'\] = false/g" /etc/phppgadmin/config.inc.php
  #####################
  sudo -u postgres psql -p $mapbender_database_port -d $mapbender_database_name -c "CREATE SCHEMA django AUTHORIZATION $mapbender_database_user"
  sudo -u postgres psql -p $mapbender_database_port -d $mapbender_database_name -c 'CREATE SCHEMA mapbender'
  sudo -u postgres psql -p $mapbender_database_port -d $mapbender_database_name -c 'ALTER DATABASE mapbender SET search_path TO mapbender,public,pg_catalog,topology'
  #####################
  sudo -u postgres psql -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/pgsql_schema_2.5.sql

  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/pgsql_data_2.5.sql
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/pgsql_serial_set_sequences_2.5.sql
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.5_to_2.5.1rc1_pgsql_UTF-8.sql
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.5.1rc1_to_2.5.1_pgsql_UTF-8.sql
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.5.1_to_2.6rc1_pgsql_UTF-8.sql
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.6rc1_to_2.6_pgsql_UTF-8.sql
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.6_to_2.6.1_pgsql_UTF-8.sql
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.6.1_to_2.6.2_pgsql_UTF-8.sql
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.6.2_to_2.7rc1_pgsql_UTF-8.sql
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.7rc1_to_2.7rc2_pgsql_UTF-8.sql
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.7.1_to_2.7.2_pgsql_UTF-8.sql
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.7.2_to_2.7.3_pgsql_UTF-8.sql
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.7.3_to_2.7.4_pgsql_UTF-8.sql
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.7.4_to_2.8_pgsql_UTF-8.sql
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.8_pgsql_UTF-8.sql
  #####################
  echo 'adopting mapbenders default database to geoportal default options  ... '

  cat << EOF > ${installation_folder}geoportal_database_adoption_1.sql
  --create new database content
  --geoportal specific extensions
  ALTER TABLE mb_user ADD COLUMN mb_user_glossar character varying(5);
  --ALTER TABLE mb_user ADD COLUMN mb_user_glossar character varying(14);
  --ALTER TABLE mb_user ADD COLUMN mb_user_textsize character varying(14);
  ALTER TABLE mb_user ADD COLUMN mb_user_textsize character varying(14);
  ALTER TABLE mb_user ADD COLUMN mb_user_last_login_date date;
  ALTER TABLE mb_user ADD COLUMN mb_user_spatial_suggest character varying(5);
  ALTER TABLE mb_user ADD COLUMN salt character varying(100);
  ALTER TABLE mb_user ADD COLUMN password character varying(255);
  ALTER TABLE mb_user ADD COLUMN is_active boolean;
  ALTER TABLE mb_user ADD COLUMN activation_key character varying(255);
  ALTER TABLE mb_user ADD COLUMN timestamp_delete bigint;
  ALTER TABLE mb_user ADD COLUMN timestamp_dsgvo_accepted bigint;

  UPDATE gui_category SET category_name='Anwendung' WHERE category_id=2;
  UPDATE gui_category SET category_description='Anwendungen (Applications)' WHERE category_id=2;

  --add anonymous user
  INSERT INTO mb_user (mb_user_id, mb_user_name, mb_user_password, mb_user_owner, mb_user_description, mb_user_login_count, mb_user_email, mb_user_phone, mb_user_department, mb_user_resolution, mb_user_organisation_name, mb_user_position_name, mb_user_phone1, mb_user_facsimile, mb_user_delivery_point, mb_user_city, mb_user_postal_code, mb_user_country, mb_user_online_resource, mb_user_textsize, mb_user_glossar, mb_user_last_login_date, mb_user_digest, mb_user_realname, mb_user_street, mb_user_housenumber, mb_user_reference, mb_user_for_attention_of, mb_user_valid_from, mb_user_valid_to, mb_user_password_ticket, mb_user_firstname, mb_user_lastname, mb_user_academictitle, timestamp_create, timestamp, mb_user_spatial_suggest, mb_user_newsletter, mb_user_allow_survey, mb_user_aldigest, salt, password, is_active, activation_key, timestamp_delete) VALUES (${mapbender_guest_user_id}, 'guest', '084e0343a0486ff05530df6c705c8bb4', 1, 'test', 0, 'kontakt@geoportal.rlp.de', NULL, '', 72, '', '', NULL, NULL, NULL, '', NULL, NULL, NULL, 'textsize3', 'ja', '2012-01-26', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2013-07-05 08:09:25.560359', '2015-08-20 10:04:04.952796', 'nein', true, true, NULL, '014082d179fd7099c21ccd5581127e76', 'e03c8506fb6466bbeedc851aca87d3a3784ad39a38f5e5cd9e5a6d9f43a211c6',true,'',NULL);

  INSERT INTO mb_user (mb_user_id, mb_user_name, mb_user_password, mb_user_owner, mb_user_description, mb_user_login_count, mb_user_email, mb_user_phone, mb_user_department, mb_user_resolution, mb_user_organisation_name, mb_user_position_name, mb_user_phone1, mb_user_facsimile, mb_user_delivery_point, mb_user_city, mb_user_postal_code, mb_user_country, mb_user_online_resource, mb_user_textsize, mb_user_glossar, mb_user_last_login_date, mb_user_digest, mb_user_realname, mb_user_street, mb_user_housenumber, mb_user_reference, mb_user_for_attention_of, mb_user_valid_from, mb_user_valid_to, mb_user_password_ticket, mb_user_firstname, mb_user_lastname, mb_user_academictitle, timestamp_create, timestamp, mb_user_spatial_suggest, mb_user_newsletter, mb_user_allow_survey, mb_user_aldigest, salt, password, is_active, activation_key, timestamp_delete) VALUES (${mapbender_subadmin_default_user_id}, 'bereichsadmin1', '3ad58afdc417b975256af7a6d3eda7a5', 1, '', 0, 'kontakt@geoportal.rlp.de', NULL, NULL, 72, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'nein', '2017-07-28', '3c345c2af80400e1e4c94ed0a967e713', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'bereichsadmin1', 'bereichsadmin1', '', '2013-07-05 08:09:25.560359', '2017-07-28 10:12:13.926954', 'nein', false, false, '2a32c845b23d82bea4653810f146397b', '2d7b43f3a5302c9dc87e1132d486610f', '233f7404797483c943bf2d8cf5a38d0f42d35f8ab6832c54dd15d9f514e76e0b',true,'',NULL);

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
EOF
  #####################
  # sql for beeing executed after recreating of the guis
  #####################
  cat << EOF > ${installation_folder}geoportal_database_adoption_2.sql
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

EOF

  #####################
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}geoportal_database_adoption_1.sql
  #####################

  # recreate the guis via psql
  cp ${installation_folder}mapbender/resources/db/gui_Geoportal-RLP.sql ${installation_folder}gui_${default_gui_name}.sql
  # exchange all occurences of old default gui name in sql
  sed -i "s/Geoportal-RLP/${default_gui_name}/g" ${installation_folder}gui_${default_gui_name}.sql
  # recreate the guis via psql - default gui definition is in installation folder!
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}gui_${default_gui_name}.sql
  # do the same for extended search gui
  # alter the name of the default extended search gui in the gui definition
  cp ${installation_folder}mapbender/resources/db/gui_Geoportal-RLP_erwSuche2.sql ${installation_folder}gui_${extended_search_default_gui_name}.sql
  # exchange all occurences of old default gui name in sql
  sed -i "s/Geoportal-RLP_erwSuche2/${extended_search_default_gui_name}/g" ${installation_folder}gui_${extended_search_default_gui_name}.sql
  # recreate the guis via psql - default gui definition is in installation folder!
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}gui_${extended_search_default_gui_name}.sql
  #####################
  # fix invocation of javascript functions for digitize module
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -c "UPDATE gui_element SET e_pos = '3' where e_id = 'kml' AND fkey_gui_id = '${default_gui_name}'"

  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/gui_Owsproxy_csv.sql
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/gui_admin_wms_metadata.sql
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/gui_admin_wfs_metadata.sql
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/gui_admin_wmc_metadata.sql
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/gui_admin_metadata.sql
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/gui_admin_ows_scheduler.sql
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/gui_PortalAdmin_DE.sql
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/gui_Administration_DE.sql
  #####################

  #####################
  sudo -u postgres psql -q -d mapbender -f ${installation_folder}geoportal_database_adoption_2.sql

  # add privilegs for mapbenderdbuser
  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'GRANT USAGE ON SCHEMA mapbender TO $mapbender_database_user'"
  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'GRANT USAGE ON SCHEMA public TO $mapbender_database_user'"

  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA mapbender TO $mapbender_database_user'"
  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO $mapbender_database_user'"
  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'GRANT ALL PRIVILEGES ON DATABASE $mapbender_database_name TO $mapbender_database_user'"
  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'GRANT SELECT, INSERT, UPDATE, DELETE ON DATABASE $mapbender_database_name TO $mapbender_database_user'"
  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA mapbender TO $mapbender_database_user'"

  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA mapbender TO $mapbender_database_user'"
  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO $mapbender_database_user'"
  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'GRANT CREATE ON DATABASE mapbender TO $mapbender_database_user'"
  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'GRANT CREATE ON SCHEMA mapbender TO $mapbender_database_user'"


  #su - postgres -c "psql -q -p 5432 -d mapbender -c 'GRANT CREATE ON DATABASE mapbender TO $mapbender_database_user'"
  #####################
  # add precise coordinate transformation to proj and postgis extension
  #####################
  wget http://crs.bkg.bund.de/crseu/crs/descrtrans/BeTA/BETA2007.gsb
  cp BETA2007.gsb /usr/share/proj/
  cp /usr/share/proj/epsg /usr/share/proj/epsg_backup_geoportal
  sed -i "s/<31466> +proj=tmerc +lat_0=0 +lon_0=6 +k=1 +x_0=2500000 +y_0=0 +datum=potsdam +units=m +no_defs  <>/<31466> +proj=tmerc +lat_0=0 +lon_0=6 +k=1 +x_0=2500000 +y_0=0 +datum=potsdam +ellps=bessel +nadgrids=@BETA2007.gsb,null +units=m +no_defs  <>/g" /usr/share/proj/epsg
  sed -i "s/<31467> +proj=tmerc +lat_0=0 +lon_0=9 +k=1 +x_0=3500000 +y_0=0 +datum=potsdam +units=m +no_defs  <>/<31467> +proj=tmerc +lat_0=0 +lon_0=9 +k=1 +x_0=3500000 +y_0=0 +datum=potsdam +ellps=bessel +nadgrids=@BETA2007.gsb,null +units=m +no_defs  <>/g" /usr/share/proj/epsg
  sed -i "s/<31468> +proj=tmerc +lat_0=0 +lon_0=12 +k=1 +x_0=4500000 +y_0=0 +datum=potsdam +units=m +no_defs  <>/<31468> +proj=tmerc +lat_0=0 +lon_0=12 +k=1 +x_0=4500000 +y_0=0 +datum=potsdam +ellps=bessel +nadgrids=@BETA2007.gsb,null +units=m +no_defs  <>/g" /usr/share/proj/epsg
  sed -i "s/<31469> +proj=tmerc +lat_0=0 +lon_0=15 +k=1 +x_0=5500000 +y_0=0 +datum=potsdam +units=m +no_defs  <>/<31469> +proj=tmerc +lat_0=0 +lon_0=15 +k=1 +x_0=5500000 +y_0=0 +datum=potsdam +ellps=bessel +nadgrids=@BETA2007.gsb,null +units=m +no_defs  <>/g" /usr/share/proj/epsg
  #####################
  cat << EOF > ${installation_folder}geoportal_database_proj_adaption.sql
  UPDATE spatial_ref_sys SET proj4text='+proj=tmerc +lat_0=0 +lon_0=6 +k=1 +x_0=2500000 +y_0=0 +datum=potsdam +ellps=bessel +nadgrids=@BETA2007.gsb,null +units=m +no_defs' WHERE srid = 31466;
  UPDATE spatial_ref_sys SET proj4text='+proj=tmerc +lat_0=0 +lon_0=9 +k=1 +x_0=3500000 +y_0=0 +datum=potsdam +ellps=bessel +nadgrids=@BETA2007.gsb,null +units=m +no_defs' WHERE srid = 31467;
  UPDATE spatial_ref_sys SET proj4text='+proj=tmerc +lat_0=0 +lon_0=12 +k=1 +x_0=4500000 +y_0=0 +datum=potsdam +ellps=bessel +nadgrids=@BETA2007.gsb,null +units=m +no_defs' WHERE srid = 31468;
  UPDATE spatial_ref_sys SET proj4text='+proj=tmerc +lat_0=0 +lon_0=15 +k=1 +x_0=5500000 +y_0=0 +datum=potsdam +ellps=bessel +nadgrids=@BETA2007.gsb,null +units=m +no_defs' WHERE srid = 31469;
EOF
  sudo -u postgres psql -q -d mapbender -f ${installation_folder}geoportal_database_proj_adaption.sql
  fi

  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/pgsql_serial_set_sequences_2.7.sql

  if [ $configure_mapbender = 'true' ]; then
      echo 'configure mapbender ... '
      echo 'language compiling ... '
      cd ${installation_folder}mapbender/tools
      sh ./i18n_update_mo.sh
      #####################
      cd ${installation_folder}mapbender/
      cp /data/mapbender/conf/mapbender.conf-dist /data/mapbender/conf/mapbender.conf
      # create folder to store generated metadata xml documents
      mkdir ${installation_folder}mapbender/metadata
      #####################
      echo 'change more permissones ... '
      # alter owner of folders where webserver should be able to alter data
      chown -R www-data:www-data ${installation_folder}mapbender/http/tmp/
      chown -R www-data:www-data ${installation_folder}mapbender/log/
      chown -R www-data:www-data ${installation_folder}mapbender/http/geoportal/preview/
      chown -R www-data:www-data ${installation_folder}mapbender/http/geoportal/news/
      chown -R www-data:www-data ${installation_folder}mapbender/metadata/
      #####################
      echo -n 'adopt mapbender.conf ... '
      #####################
      # alter connection type to use curl
      #####################
      sed -i "s/define(\"CONNECTION\", \"http\");/#define(\"CONNECTION\", \"http\");/g" ${installation_folder}mapbender/conf/mapbender.conf
      sed -i "s/#define(\"CONNECTION\", \"curl\");/define(\"CONNECTION\", \"curl\");/g" ${installation_folder}mapbender/conf/mapbender.conf
      #####################
      # define proxy settings
      #####################
      # sed -i "s///g" ${installation_folder}mapbender/conf/mapbender.conf
      if [ $use_proxy_mb = 'true' ]; then
  	    sed -i "s/define(\"CONNECTION_PROXY\", \"\");/define(\"CONNECTION_PROXY\", \"$http_proxy_host\");/g" ${installation_folder}mapbender/conf/mapbender.conf
  	    sed -i "s/define(\"CONNECTION_PORT\", \"\");/define(\"CONNECTION_PORT\", \"$http_proxy_port\");/g" ${installation_folder}mapbender/conf/mapbender.conf
  	    sed -i "s/define(\"NOT_PROXY_HOSTS\", \"<ip>,<ip>,<ip>\");/define(\"NOT_PROXY_HOSTS\", \"localhost,127.0.0.1\");/g" ${installation_folder}mapbender/conf/mapbender.conf
      fi
      #####################
      # set database connection
      #####################
      sed -i "s/%%DBSERVER%%/localhost/g" ${installation_folder}mapbender/conf/mapbender.conf
      sed -i "s/%%DBPORT%%/$mapbender_database_port/g" ${installation_folder}mapbender/conf/mapbender.conf
      sed -i "s/%%DBNAME%%/$mapbender_database_name/g" ${installation_folder}mapbender/conf/mapbender.conf
      sed -i "s/%%DBOWNER%%/$mapbender_database_user/g" ${installation_folder}mapbender/conf/mapbender.conf
      sed -i "s/%%DBPASSWORD%%/$mapbender_database_password/g" ${installation_folder}mapbender/conf/mapbender.conf
      # maybe problematic!:
      sed -i "s#/data/#$installation_folder#g" ${installation_folder}mapbender/conf/mapbender.conf
      #####################
      # special users & groups
      #####################
      sed -i "s/#define(\"PUBLIC_USER\", \"\");/#define(\"PUBLIC_USER\", \"${mapbender_guest_user_id}\");/g" ${installation_folder}mapbender/conf/mapbender.conf
      sed -i "s/#define(\"REGISTRATING_GROUP\",36);/define(\"REGISTRATING_GROUP\",${mapbender_subadmin_group_id});/g" ${installation_folder}mapbender/conf/mapbender.conf
      sed -i "s/#define(\"PORTAL_ADMIN_USER_ID\",\"1\");/define(\"PORTAL_ADMIN_USER_ID\",\"1\");/g" ${installation_folder}mapbender/conf/mapbender.conf
      sed -i "s/#define(\"CATALOGUE_MAINTENANCE_USER\",\"1\");/define(\"CATALOGUE_MAINTENANCE_USER\",\"1\");/g" ${installation_folder}mapbender/conf/mapbender.conf
      #####################
      # enable public user auto session
      #####################
      sed -i "s/#define(\"PUBLIC_USER_AUTO_CREATE_SESSION\", true);/define(\"PUBLIC_USER_AUTO_CREATE_SESSION\", true);/g" ${installation_folder}mapbender/conf/mapbender.conf
      sed -i "s/#define(\"PUBLIC_USER_DEFAULT_GUI\", \"${default_gui_name}\");/define(\"PUBLIC_USER_DEFAULT_GUI\", \"${default_gui_name}\");/g" ${installation_folder}mapbender/conf/mapbender.conf
      sed -i "s/#define(\"PUBLIC_USER_DEFAULT_SRS\", \"EPSG:25832\");/define(\"PUBLIC_USER_DEFAULT_SRS\", \"EPSG:25832\");/g" ${installation_folder}mapbender/conf/mapbender.conf
      #####################
      # metadata management
      #####################
      sed -i "s/define(\"MD_OVERWRITE\", true);/define(\"MD_OVERWRITE\", false);/g" ${installation_folder}mapbender/conf/mapbender.conf
      #####################
      # default language
      #####################
      sed -i "s/define(\"USE_I18N\", false);/define(\"USE_I18N\", true);/g" ${installation_folder}mapbender/conf/mapbender.conf
      sed -i "s/define(\"LANGUAGE\", \"en\");/define(\"LANGUAGE\", \"de\");/g" ${installation_folder}mapbender/conf/mapbender.conf

      # alter group id for subadministrators in monitoring tool - use group_id 21 - this is the subadmin mb_group_id
  cat << EOF > ${installation_folder}mapbender/tools/monitorCapabilities.sh
  . /etc/profile
  [ -f /tmp/wmsmonitorlock ] && : || /usr/bin/php7.0 ${installation_folder}mapbender/tools/mod_monitorCapabilities_main.php group:${mapbender_subadmin_group_id} > /dev/null
EOF
      #####################
      # register initial services for default and extended search GUIs
      #####################
    cd ${installation_folder}mapbender/tools/
  echo -n 'register initial default services ... '
      ##################### Geoportal-RLP
  eval $wms_1_register_cmd
  eval $wms_2_register_cmd
  eval $wms_3_register_cmd
      ##################### Geoportal-RLP_erwSuche2
  eval $wms_4_register_cmd
  eval $wms_5_register_cmd
      ##################### demo service
  eval $wms_6_register_cmd
      #####################
  # qualify the main gui
  # update database to set initial extent and epsg for Main GUI: TODO: maybe use a hidden layer !
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -c "UPDATE gui_wms SET gui_wms_epsg = '$epsg' WHERE fkey_gui_id = '${default_gui_name}'"
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -c "UPDATE layer_epsg SET minx = '$minx', miny = '$miny', maxx = '$maxx', maxy = '$maxy' WHERE fkey_layer_id IN (SELECT layer_id FROM layer WHERE fkey_wms_id IN (SELECT fkey_wms_id FROM gui_wms WHERE fkey_gui_id = '${default_gui_name}' AND gui_wms_position = 0) AND layer_parent='') AND epsg = '$epsg'"
  # set first wms to be seen in the overview mapframe
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -c "UPDATE gui_element_vars SET var_value = '0' WHERE fkey_gui_id='${default_gui_name}' AND fkey_e_id='overview' AND var_name = 'overview_wms'"
  # set resize option to auto
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -c "UPDATE gui_element_vars SET var_value = 'auto' WHERE fkey_gui_id='${default_gui_name}' AND fkey_e_id='resizeMapsize' AND var_name = 'resize_option'"
  # set max height and width for resize
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -c "INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('${default_gui_name}', 'resizeMapsize', 'max_width', '1000', 'define a max mapframe width (units pixel) f.e. 700 or false' ,'var')"
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -c "INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('${default_gui_name}', 'resizeMapsize', 'max_height', '600', 'define a max mapframe width (units pixel) f.e. 700 or false' ,'var')"
  #####################
  fi

  if [ $checkout_mapbender_conf = 'true' ]; then
      # override information in custom conf files
      # mapbender.conf
      #####################
      echo -n 'adopt mapbender.conf from default geoportal conf files ... '
      #####################
      # alter connection type to use curl
      #####################
      sed -i "s/define(\"CONNECTION\", \"http\");/#define(\"CONNECTION\", \"http\");/g" ${installation_folder}conf/mapbender.conf
      sed -i "s/#define(\"CONNECTION\", \"curl\");/define(\"CONNECTION\", \"curl\");/g" ${installation_folder}conf/mapbender.conf
      #####################
      # define proxy settings
      #####################
      # sed -i "s///g" ${installation_folder}mapbender/conf/mapbender.conf
      if [ $use_proxy_mb = 'true' ]; then
  	    sed -i "s/define(\"CONNECTION_PROXY\", \"\");/define(\"CONNECTION_PROXY\", \"$http_proxy_host\");/g" ${installation_folder}conf/mapbender.conf
  	    sed -i "s/define(\"CONNECTION_PORT\", \"\");/define(\"CONNECTION_PORT\", \"$http_proxy_port\");/g" ${installation_folder}conf/mapbender.conf
  	    sed -i "s/define(\"NOT_PROXY_HOSTS\", \"<ip>,<ip>,<ip>\");/define(\"NOT_PROXY_HOSTS\", \"localhost,127.0.0.1\");/g" ${installation_folder}conf/mapbender.conf
      fi
      #####################
      # set database connection
      #####################
      sed -i "s/%%DBSERVER%%/localhost/g" ${installation_folder}conf/mapbender.conf
      sed -i "s/%%DBPORT%%/$mapbender_database_port/g" ${installation_folder}conf/mapbender.conf
      sed -i "s/%%DBNAME%%/$mapbender_database_name/g" ${installation_folder}conf/mapbender.conf
      sed -i "s/%%DBOWNER%%/$mapbender_database_user/g" ${installation_folder}conf/mapbender.conf
      sed -i "s/%%DBPASSWORD%%/$mapbender_database_password/g" ${installation_folder}conf/mapbender.conf
      sed -i "s#%%INSTALLATIONFOLDER%%#${installation_folder}#g" ${installation_folder}conf/mapbender.conf
      sed -i "s/%%DOMAINNAME%%/$domain_name/g" ${installation_folder}conf/mapbender.conf
      sed -i "s/%%WEBADMINMAIL%%/$webadmin_email/g" ${installation_folder}conf/mapbender.conf
      #####################
      # special users & groups%%INSTALLATIONFOLDER%%
      #####################
      sed -i "s/%%PUBLICUSERID%%/$mapbender_guest_user_id/g" ${installation_folder}conf/mapbender.conf
      sed -i "s/%%PORTALADMINUSERID%%/1/g" ${installation_folder}conf/mapbender.conf
      sed -i "s/%%ANONYMOUSUSER%%/$mapbender_guest_user_id/g" ${installation_folder}conf/mapbender.conf
      sed -i "s/%%ANONYMOUSGROUP%%/$mapbender_guest_group_id/g" ${installation_folder}conf/mapbender.conf
      sed -i "s/%%REGISTRATINGGROUP%%/$mapbender_subadmin_group_id/g" ${installation_folder}conf/mapbender.conf
      #####################
      # enable public user auto session
      #####################
      sed -i "s/#define(\"PUBLIC_USER_AUTO_CREATE_SESSION\", true);/define(\"PUBLIC_USER_AUTO_CREATE_SESSION\", true);/g" ${installation_folder}conf/mapbender.conf
      sed -i "s/#define(\"PUBLIC_USER_DEFAULT_GUI\", \"${default_gui_name}\");/define(\"PUBLIC_USER_DEFAULT_GUI\", \"${default_gui_name}\");/g" ${installation_folder}conf/mapbender.conf
      sed -i "s/#define(\"PUBLIC_USER_DEFAULT_SRS\", \"EPSG:25832\");/define(\"PUBLIC_USER_DEFAULT_SRS\", \"EPSG:25832\");/g" ${installation_folder}conf/mapbender.conf
      #sed -i "s/%%INSTALLATIONFOLDER%%/$installation_folder/g" ${installation_folder}conf/mapbender.conf
      # copy conf files to right places
      echo "copy mapbender.conf from ${installation_folder}/mapbender.conf to ${installation_folder}mapbender/conf/ ..."
      cp ${installation_folder}conf/mapbender.conf ${installation_folder}mapbender/conf/
      # alter other conf files
      sed -i "s#%%INSTALLATIONFOLDER%%#${installation_folder}#g" ${installation_folder}conf/geoportal.conf
      # copy conf file to right places
      cp ${installation_folder}conf/geoportal.conf ${installation_folder}mapbender/conf/
      # mapfile for metadata wms
      sed -i "s#%%INSTALLATIONFOLDER%%#${installation_folder}#g" ${installation_folder}conf/extents_geoportal_rlp.map
      sed -i "s/dbname=geoportal /dbname=$mapbender_database_name /g" ${installation_folder}conf/extents_geoportal_rlp.map
      sed -i "s/user=postgres /user=$mapbender_database_user password=$mapbender_database_password /g" ${installation_folder}conf/extents_geoportal_rlp.map
      sed -i "s/port=5436 /port=$mapbender_database_port /g" ${installation_folder}conf/extents_geoportal_rlp.map
      #sed -i "s/%%http_proxy_host%%/$http_proxy_host/g" ${installation_folder}conf/extents_geoportal_rlp.map
      #sed -i "s/%%http_proxy_port%%/$http_proxy_port/g" ${installation_folder}conf/extents_geoportal_rlp.map
      sed -i "s/%%BBOXWGS84SPACE%%/$bbox_wgs84_space/g" ${installation_folder}conf/extents_geoportal_rlp.map
      sed -i "s/\"wms_proxy_host\" \"%%PROXYHOST%%\"/#\"wms_proxy_host\" \"%%PROXYHOST%%\"/g" ${installation_folder}conf/extents_geoportal_rlp.map
      sed -i "s/\"wms_proxy_port\" \"%%PROXYPORT%%\"/#\"wms_proxy_port\" \"%%PROXYPORT%%\"/g" ${installation_folder}conf/extents_geoportal_rlp.map

      if [ $use_proxy_mb = 'true' ]; then
          #####################
          # integrated mapserver mapfile for metadata footprints
          #####################
          sed -i "s/#\"wms_proxy_host\" \"%%PROXYHOST%%\"/\"wms_proxy_host\" \"${http_proxy_host}\"/g" ${installation_folder}conf/extents_geoportal_rlp.map
          sed -i "s/#\"wms_proxy_port\" \"%%PROXYPORT%%\"/\"wms_proxy_port\" \"${http_proxy_port}\"/g" ${installation_folder}conf/extents_geoportal_rlp.map
      fi

      cp ${installation_folder}conf/extents_geoportal_rlp.map ${installation_folder}mapbender/tools/wms_extent/extents.map
      # conf file for invocation of metadata wms
      sed -i "s#%%INSTALLATIONFOLDER%%#${installation_folder}#g" ${installation_folder}conf/extent_service_geoportal_rlp.conf
      sed -i "s/%%BBOXWGS84%%/$bbox_wgs84/g" ${installation_folder}conf/extent_service_geoportal_rlp.conf
      cp ${installation_folder}conf/extent_service_geoportal_rlp.conf ${installation_folder}mapbender/tools/wms_extent/extent_service.conf

      # conf file for atomFeedClient
      sed -i "s#%%center_x_i%%#${center_x_i}#g" ${installation_folder}conf/atomFeedClient.conf
      sed -i "s#%%center_y_i%%#${center_y_i}#g" ${installation_folder}conf/atomFeedClient.conf
      cp ${installation_folder}conf/atomFeedClient.conf ${installation_folder}mapbender/conf/atomFeedClient.conf
      # conf file for mobilemap client
      sed -i "s#%%center_x_i%%#${center_x_i}#g" ${installation_folder}conf/config.js
      sed -i "s#%%center_y_i%%#${center_y_i}#g" ${installation_folder}conf/config.js
      sed -i "s#%%initial_scale_i%%#${initial_scale_i}#g" ${installation_folder}conf/config.js
      sed -i "s#%%map_extent_csv%%#${bbox_current_epsg_csv}#g" ${installation_folder}conf/config.js
      sed -i "s#%%server_url%%#${server_url}#g" ${installation_folder}conf/config.js
      sed -i "s#%%background_hybrid_tms_url%%#${background_hybrid_tms_url}#g" ${installation_folder}conf/config.js
      sed -i "s#%%background_aerial_wms_url%%#${background_aerial_wms_url}#g" ${installation_folder}conf/config.js
      cp ${installation_folder}conf/config.js ${installation_folder}mapbender/http/extensions/mobilemap2/scripts/netgis/config.js
      # conf file for mobilemap.conf
      sed -i "s#%%dhm_wms_url%%#${dhm_wms_url}#g" ${installation_folder}conf/mobilemap.conf
      sed -i "s#%%catalogue_interface%%#${catalogue_interface}#g" ${installation_folder}conf/mobilemap.conf
      sed -i "s#%%background_wms_csv%%#${background_wms_csv}#g" ${installation_folder}conf/mobilemap.conf
      cp ${installation_folder}conf/mobilemap.conf ${installation_folder}mapbender/conf/mobilemap.conf

  fi
  if [ $checkout_custom_svn = 'true' ]; then
      # override information in custom conf files
      # mapbender.conf
      #####################
      echo -n 'adopt mapbender.conf from custom geoportal conf files ...'
      #####################
      # alter connection type to use curl
      #####################
      sed -i "s/define(\"CONNECTION\", \"http\");/#define(\"CONNECTION\", \"http\");/g" ${installation_folder}conf/mapbender.conf
      sed -i "s/#define(\"CONNECTION\", \"curl\");/define(\"CONNECTION\", \"curl\");/g" ${installation_folder}conf/mapbender.conf
      #####################
      # define proxy settings
      #####################
      # sed -i "s///g" ${installation_folder}mapbender/conf/mapbender.conf
      if [ $use_proxy_mb = 'true' ]; then
  	    sed -i "s/define(\"CONNECTION_PROXY\", \"\");/define(\"CONNECTION_PROXY\", \"$http_proxy_host\");/g" ${installation_folder}conf/mapbender.conf
  	    sed -i "s/define(\"CONNECTION_PORT\", \"\");/define(\"CONNECTION_PORT\", \"$http_proxy_port\");/g" ${installation_folder}conf/mapbender.conf
  	    sed -i "s/define(\"NOT_PROXY_HOSTS\", \"<ip>,<ip>,<ip>\");/define(\"NOT_PROXY_HOSTS\", \"localhost,127.0.0.1\");/g" ${installation_folder}conf/mapbender.conf
      fi
      #####################
      # set database connection
      #####################
      sed -i "s/%%DBSERVER%%/localhost/g" ${installation_folder}conf/mapbender.conf
      sed -i "s/%%DBPORT%%/$mapbender_database_port/g" ${installation_folder}conf/mapbender.conf
      sed -i "s/%%DBNAME%%/$mapbender_database_name/g" ${installation_folder}conf/mapbender.conf
      sed -i "s/%%DBOWNER%%/$mapbender_database_user/g" ${installation_folder}conf/mapbender.conf
      sed -i "s/%%DBPASSWORD%%/$mapbender_database_password/g" ${installation_folder}conf/mapbender.conf
      sed -i "s#%%INSTALLATIONFOLDER%%#${installation_folder}#g" ${installation_folder}conf/mapbender.conf
      sed -i "s/%%DOMAINNAME%%/$domain_name/g" ${installation_folder}conf/mapbender.conf
      sed -i "s/%%WEBADMINMAIL%%/$webadmin_email/g" ${installation_folder}conf/mapbender.conf
      #####################
      # special users & groups%%INSTALLATIONFOLDER%%
      #####################
      sed -i "s/%%PUBLICUSERID%%/$mapbender_guest_user_id/g" ${installation_folder}conf/mapbender.conf
      sed -i "s/%%PORTALADMINUSERID%%/1/g" ${installation_folder}conf/mapbender.conf
      sed -i "s/%%ANONYMOUSUSER%%/$mapbender_guest_user_id/g" ${installation_folder}conf/mapbender.conf
      sed -i "s/%%ANONYMOUSGROUP%%/$mapbender_guest_group_id/g" ${installation_folder}conf/mapbender.conf
      sed -i "s/%%REGISTRATINGGROUP%%/$mapbender_subadmin_group_id/g" ${installation_folder}conf/mapbender.conf
      #####################
      # enable public user auto session
      #####################
      sed -i "s/#define(\"PUBLIC_USER_AUTO_CREATE_SESSION\", true);/define(\"PUBLIC_USER_AUTO_CREATE_SESSION\", true);/g" ${installation_folder}conf/mapbender.conf
      sed -i "s/#define(\"PUBLIC_USER_DEFAULT_GUI\", \"${default_gui_name}\");/define(\"PUBLIC_USER_DEFAULT_GUI\", \"${default_gui_name}\");/g" ${installation_folder}conf/mapbender.conf
      sed -i "s/#define(\"PUBLIC_USER_DEFAULT_SRS\", \"EPSG:25832\");/define(\"PUBLIC_USER_DEFAULT_SRS\", \"EPSG:25832\");/g" ${installation_folder}conf/mapbender.conf
      #sed -i "s/%%INSTALLATIONFOLDER%%/$installation_folder/g" ${installation_folder}conf/mapbender.conf
      # copy conf files to right places
      cp ${installation_folder}conf/mapbender.conf ${installation_folder}mapbender/conf/
      # alter other conf files
      sed -i "s#%%INSTALLATIONFOLDER%%#${installation_folder}#g" ${installation_folder}conf/geoportal.conf
      # copy conf file to right places
      cp ${installation_folder}conf/geoportal.conf ${installation_folder}mapbender/conf/
      # mapfile for metadata wms
      sed -i "s#%%INSTALLATIONFOLDER%%#${installation_folder}#g" ${installation_folder}conf/extents_geoportal_rlp.map
      sed -i "s/dbname=geoportal /dbname=$mapbender_database_name /g" ${installation_folder}conf/extents_geoportal_rlp.map
      sed -i "s/user=postgres /user=$mapbender_database_user password=$mapbender_database_password /g" ${installation_folder}conf/extents_geoportal_rlp.map
      sed -i "s/port=5436 /port=$mapbender_database_port /g" ${installation_folder}conf/extents_geoportal_rlp.map
      sed -i "s/%%BBOXWGS84SPACE%%/$bbox_wgs84_space/g" ${installation_folder}conf/extents_geoportal_rlp.map
      cp ${installation_folder}conf/extents_geoportal_rlp.map ${installation_folder}mapbender/tools/wms_extent/extents.map
      # conf file for invocation of metadata wms
      sed -i "s#%%INSTALLATIONFOLDER%%#${installation_folder}#g" ${installation_folder}conf/extent_service_geoportal_rlp.conf
      sed -i "s/%%BBOXWGS84%%/$bbox_wgs84/g" ${installation_folder}conf/extent_service_geoportal_rlp.conf
      cp ${installation_folder}conf/extent_service_geoportal_rlp.conf ${installation_folder}mapbender/tools/wms_extent/extent_service.conf
  fi


  if [ $configure_apache = 'true' ]; then
  ############################################################
  # create apache configuration for mapbender
  ############################################################
  echo -n 'create apache configuration ...'
  cat << EOF > ${installation_folder}geoportal-apache.conf
  <VirtualHost *:80>
          ServerAdmin $webadmin_email
          ReWriteEngine On
          RewriteRule ^/registry/wfs/([\d]+)\/?$ http://127.0.0.1/http_auth/http/index.php?wfs_id=\$1 [P,L,QSA,NE]
          RewriteRule ^/layer/(.*) http://%{SERVER_NAME}/mapbender/php/mod_showMetadata.php?resource=layer&languageCode=de&id=\$1
          RewriteRule ^/wms/(.*) http://%{SERVER_NAME}/mapbender/php/mod_showMetadata.php?resource=wms&languageCode=de&id=\$1
          RewriteRule ^/wmc/(.*) http://%{SERVER_NAME}/mapbender/php/mod_showMetadata.php?resource=wmc&languageCode=de&id=\$1
          RewriteRule ^/dataset/(.*) http://%{SERVER_NAME}/mapbender/php/mod_dataISOMetadata.php?outputFormat=iso19139&id=\$1

          # for mobilemap2 api
          RewriteCond %{QUERY_STRING} ^(.*)wmcid(.*)$
          RewriteRule /mapbender/extensions/mobilemap/map.php http://%{HTTP_HOST}/mapbender/extensions/mobilemap2/index.html?%1wmc_id%2
          RewriteCond %{QUERY_STRING} ^(.*)layerid(.*)$
          RewriteRule /mapbender/extensions/mobilemap/map.php https://%{HTTP_HOST}/mapbender/extensions/mobilemap2/index.html?%1layerid%2
          # for digitizing module
          RewriteRule ^/icons/maki/([^/]+)/([^/]+)/([^[/]+).png$ http://127.0.0.1/mapbender/php/mod_getSymbolFromRepository.php?marker-color=\$1&marker-size=\$2&marker-symbol=\$3 [P,L,QSA,NE]


  	      Alias /static/ /opt/GeoPortal.rlp/static/

  	      <Directory /opt/GeoPortal.rlp/static>
  		  Options -Indexes -FollowSymlinks
	      Require all granted
  	      </Directory>


          DocumentRoot /data/portal
          Alias /portal /data/portal
	      <Directory /data/portal>
          Options -Indexes -FollowSymlinks
          AllowOverride None
  		  Require ip 127.0.0.1
  	      </Directory>



          ScriptAlias /cgi-bin/ /usr/lib/cgi-bin/
          <Directory "/usr/lib/cgi-bin">
                  AllowOverride None
                  Options +ExecCGI -MultiViews +SymLinksIfOwnerMatch
                  #SetEnv http_proxy http://[IP}:{PORT}
                  Order allow,deny
                  Allow from all
          </Directory>

          ErrorLog /var/log/apache2/error.log

          # Possible values include: debug, info, notice, warn, error, crit,
          # alert, emerg.
          LogLevel error

          CustomLog /var/log/apache2/access.log combined

          Alias /mapbender ${installation_folder}mapbender/http
          <Directory ${installation_folder}mapbender/http/>
             Options MultiViews
             AllowOverride None
             Order deny,allow
             Allow from all
             #Allow from 127.0.0.0/255.0.0.0 ::1/128
             # Insert filter
             SetOutputFilter DEFLATE
             # Netscape 4.x has some problems...
             BrowserMatch ^Mozilla/4 gzip-only-text/html
             # Netscape 4.06-4.08 have some more problems
             BrowserMatch ^Mozilla/4\.0[678] no-gzip
             # MSIE masquerades as Netscape, but it is fine
             # BrowserMatch \bMSIE !no-gzip !gzip-only-text/html
             # NOTE: Due to a bug in mod_setenvif up to Apache 2.0.48
             # the above regex won't work. You can use the following
             # workaround to get the desired effect:
             BrowserMatch \bMSI[E] !no-gzip !gzip-only-text/html
             # Don't compress images
             SetEnvIfNoCase Request_URI \
             \.(?:gif|jpe?g|png)$ no-gzip dont-vary
             # Make sure proxies don't deliver the wrong content
             Header append Vary User-Agent env=!dont-vary
          </Directory>

          #Part for proxy function
          ProxyPreserveHost On
          #ReWriteEngine On
          SetEnv force-proxy-request-1.0 1
          SetEnv proxy-nokeepalive 1
          ProxyTimeout 50
          #NoProxy localhost
          #ProxyBadHeader Ignore
          ProxyMaxForwards 3
          #RewriteLog "/tmp/rewrite.log"
          #RewriteLogLevel 3

          Alias /owsproxy ${installation_folder}mapbender/owsproxy
          <Directory ${installation_folder}mapbender/owsproxy/>
                  Options +FollowSymLinks
                  ReWriteEngine On
                  RewriteBase /owsproxy
                  RewriteRule  ^([\w\d]+)\/([\w\d]+)\/?$ http://127.0.0.1/owsproxy/http/index.php?sid=$1\&wms=$2\& [P,L,QSA,NE]
                  Options +Indexes
                  Allow from all
          </Directory>

          Alias /tools ${installation_folder}mapbender/tools
          <Directory ${installation_folder}mapbender/tools/>
                  Options +FollowSymLinks
                  AllowOverride None
                  AuthType Digest
                  AuthName "mb_tools"
                  AuthDigestProvider file
                  AuthUserFile ${installation_folder}access/.mb_tools
                  Require valid-user
                  order deny,allow
                  deny from all
                  Options +Indexes
                  Allow from all
          </Directory>

          Alias /http_auth ${installation_folder}mapbender/http_auth
          <Directory ${installation_folder}mapbender/http_auth/>
                  Options +FollowSymLinks +Indexes
                  ReWriteEngine On
                  RewriteBase /http_auth
                  RewriteRule  ^([\w\d]+)\/?$ http://127.0.0.1/http_auth/http/index.php?layer_id=$1 [P,L,QSA,NE]
                  Order allow,deny
                  Allow from all
          </Directory>
  </VirtualHost>
EOF
  ############################################################
  # copy conf to apache directory and configure apache24+
  ############################################################
  cp ${installation_folder}geoportal-apache.conf /etc/apache2/sites-available/
  ############################################################
  # activate modules
  ############################################################
  a2enmod rewrite
  a2enmod cgi
  # a2enmod serv-cgi-bin
  a2enmod proxy_http
  a2enmod headers
  a2enmod auth_digest
  # to be compatible to older apache2.2 directives:
  a2enmod access_compat
  ############################################################
  # alter central apache2 conf to allow access to /
  ############################################################
  cp /etc/apache2/apache2.conf /etc/apache2/apache2.conf_backup_geoportal
  # replace only the first occurence of Require all denied - this is the entry for /
  sed -i "0,/Require all denied/{s/Require all denied/Require all granted/}" /etc/apache2/apache2.conf
  ############################################################
  #
  ############################################################
  ############################################################
  # configure phppgadmin
  ############################################################
  echo -n 'adopt phppgadmin default apache24 configuration'
  cp /etc/apache2/conf-available/phppgadmin.conf /etc/apache2/conf-available/phppgadmin.conf_backup_geoportal
  cat << EOF > /etc/apache2/conf-available/phppgadmin.conf
  Alias /phppgadmin /usr/share/phppgadmin

  <Directory /usr/share/phppgadmin>

  DirectoryIndex index.php
  AllowOverride None

  # Only allow connections from localhost:

  AuthType Digest
  AuthName "phppgadmin"
  AuthDigestProvider file
  AuthUserFile ${installation_folder}access/.phppgadmin
  Require valid-user

  <IfModule mod_php.c>
    php_flag magic_quotes_gpc Off
    php_flag track_vars On
    #php_value include_path .
  </IfModule>
  <IfModule !mod_php.c>
    <IfModule mod_actions.c>
      <IfModule mod_cgi.c>
        AddType application/x-httpd-php .php
        Action application/x-httpd-php /cgi-bin/php
      </IfModule>
      <IfModule mod_cgid.c>
        AddType application/x-httpd-php .php
        Action application/x-httpd-php /cgi-bin/php
      </IfModule>
    </IfModule>
  </IfModule>

  </Directory>

EOF

############################################################
# security stuff
############################################################

# iptables rule to restrict connections per ip to 25
if [ ! -f "/etc/firewall.conf"  ]; then
cat << EOF > /etc/firewall.conf
  *filter
  :INPUT ACCEPT [8292:4951608]
  :FORWARD ACCEPT [0:0]
  :OUTPUT ACCEPT [5381:471699]
  -A INPUT -p tcp -m tcp --dport 80 --tcp-flags FIN,SYN,RST,ACK SYN -m connlimit --connlimit-above 25 --connlimit-mask 32 --connlimit-saddr -j REJECT --reject-with tcp-reset
  COMMIT
EOF
fi

# make startup file for iptables conf
if [ ! -f "/etc/network/if-up.d/iptables"  ]; then
cat << EOF > /etc/network/if-up.d/iptables
  #!/bin/sh
  iptables-restore < /etc/firewall.conf
EOF
fi

chmod +x /etc/network/if-up.d/iptables

if [ ! grep -q "MaxRequestsPerChild"  /etc/apache2/apache2.conf ];then
  sed -i '/MaxKeepAliveRequests*/a MaxRequestsPerChild 10000' /etc/apache2/apache2.conf
fi

if [ ! grep -q "Header edit Set-Cookie"  /etc/apache2/conf-enabled/security.conf ];then
  echo  "Header edit Set-Cookie ^(.*)\$ \$1;HttpOnly;Secure" >>/etc/apache2/conf-enabled/security.conf
fi

if [ ! grep -q "Header set X-XSS-Protection \"1; mode=block\""  /etc/apache2/conf-enabled/security.conf ];then
  echo  "Header set X-XSS-Protection \"1; mode=block\"" >>/etc/apache2/conf-enabled/security.conf
fi

if [ ! grep -q "Timeout"  /etc/apache2/conf-enabled/security.conf ];then
  echo  "Timeout 60" >>/etc/apache2/conf-enabled/security.conf
fi


  sed -i s/"#Header set X-Frame-Options: \"sameorigin\""/"Header set X-Frame-Options: \"sameorigin\""/g /etc/apache2/conf-enabled/security.conf
  sed -i s/"#Header set X-Content-Type-Options: \"nosniff\""/"Header set X-Content-Type-Options: \"nosniff\""/g /etc/apache2/conf-enabled/security.conf
  sed -i s/"ServerTokens OS"/"ServerTokens Prod"/g /etc/apache2/conf-enabled/security.conf
  sed -i s/"ServerSignature On"/"ServerSignature Off"/g /etc/apache2/conf-enabled/security.conf
  cp -a  /etc/modsecurity/modsecurity.conf-recommended  /etc/modsecurity/modsecurity.conf

  rm -rf /usr/share/modsecurity-crs
  git clone https://github.com/SpiderLabs/owasp-modsecurity-crs.git /usr/share/modsecurity-crs
  cd /usr/share/modsecurity-crs
  mv crs-setup.conf.example crs-setup.conf

  if [ ! -f "/etc/apache2/mods-enabled/security2.conf_backup_$(date +"%d_%m_%Y")"  ]; then
    mv /etc/apache2/mods-enabled/security2.conf /etc/apache2/mods-enabled/security2.conf_backup_$(date +"%d_%m_%Y")
  fi

  echo "<IfModule security2_module>" > /etc/apache2/mods-enabled/security2.conf
  echo "SecDataDir /var/cache/modsecurity" >> /etc/apache2/mods-enabled/security2.conf
  echo "IncludeOptional /etc/modsecurity/*.conf " >> /etc/apache2/mods-enabled/security2.conf
  echo "IncludeOptional /usr/share/modsecurity-crs/*.conf" >> /etc/apache2/mods-enabled/security2.conf
  echo "IncludeOptional /usr/share/modsecurity-crs/rules/*.conf " >> /etc/apache2/mods-enabled/security2.conf
  echo "</IfModule>" >> /etc/apache2/mods-enabled/security2.conf


  #cd owasp-modsecurity-crs
  #mv crs-setup.conf.example /etc/modsecurity/crs-setup.conf
  #mv rules/ /etc/modsecurity/
  #sed -i s/"IncludeOptional \/usr\/share\/modsecurity-crs\/owasp-crs.load"/"Include \/etc\/modsecurity\/rules\/*.conf"/g /etc/apache2/conf-enabled/security.conf
  ############################################################
  # activate apache conf and reload
  ############################################################
  a2ensite geoportal-apache
  a2dissite 000-default
  service apache2 restart
fi #end of apache configuration
  ############################################################
  # add privileges on search tables to mapbender database user from installation
  ############################################################
  echo "GRANT ALL ON TABLE wms_search_table TO $mapbender_database_user;" >> ${installation_folder}mapbender/resources/db/materialize_wms_view.sql
  echo "ALTER TABLE wms_search_table OWNER TO $mapbender_database_user;" >> ${installation_folder}mapbender/resources/db/materialize_wms_view.sql
  echo "ALTER TABLE wms_list OWNER TO $mapbender_database_user;" >> ${installation_folder}mapbender/resources/db/materialize_wms_view.sql

  echo "GRANT ALL ON TABLE wfs_search_table TO $mapbender_database_user;" >> ${installation_folder}mapbender/resources/db/materialize_wfs_view.sql
  echo "ALTER TABLE wfs_search_table OWNER TO $mapbender_database_user;" >> ${installation_folder}mapbender/resources/db/materialize_wfs_view.sql

  echo "GRANT ALL ON TABLE dataset_search_table TO $mapbender_database_user;" >> ${installation_folder}mapbender/resources/db/materialize_dataset_view.sql
  echo "ALTER TABLE dataset_search_table OWNER TO $mapbender_database_user;" >> ${installation_folder}mapbender/resources/db/materialize_dataset_view.sql

  echo "GRANT ALL ON TABLE wmc_search_table TO $mapbender_database_user;" >> ${installation_folder}mapbender/resources/db/materialize_wmc_view.sql
  echo "ALTER TABLE wmc_search_table OWNER TO $mapbender_database_user;" >> ${installation_folder}mapbender/resources/db/materialize_wmc_view.sql

  ############################################################
  if [ $configure_cronjobs = 'true' ]; then

  mkdir ${installation_folder}cronjobs/

  # create script to call metadata via localhost
  cat << EOF > ${installation_folder}cronjobs/generateMetadata.sh
  #!/bin/bash
  curl http://localhost/mapbender/php/mod_exportISOMetadata.php?Type=ALL > /tmp/metadataGeneration.log
EOF
  chmod u+x ${installation_folder}cronjobs/generateMetadata.sh
  ############################################################
  # install cronjobs for root account
  ############################################################
  # https://stackoverflow.com/questions/878600/how-to-create-a-cron-job-using-bash-automatically-without-the-interactive-editor
  ############################################################
  # 1. delete old monitoring xmls
  croncmd1="find ${installation_folder}mapbender/tools/tmp -type f -print | xargs rm -f"
  cronjob1="25 1,3,5,7,9,11,13,15,17,19,21,23 * * * $croncmd1"
  ( crontab -l | grep -v -F "$croncmd1" ; echo "$cronjob1" ) | crontab -
  ############################################################
  # 2. delete mapbender tmp files
  croncmd2="find ${installation_folder}mapbender/http/tmp  -name '*.*' -mmin +40 -type f | xargs rm -f"
  cronjob2="55 * * * * $croncmd2"
  ( crontab -l | grep -v -F "$croncmd2" ; echo "$cronjob2" ) | crontab -
  ############################################################
  # 3. delete mapbender tmp wmc files
  croncmd3="find ${installation_folder}mapbender/http/tmp/wmc  -name '*' -mmin +40 -type f | xargs rm -f"
  cronjob3="55 * * * * $croncmd3"
  ( crontab -l | grep -v -F "$croncmd3" ; echo "$cronjob3" ) | crontab -
  ############################################################
  # 4. delete mapbender log files
  croncmd4="find ${installation_folder}mapbender/log  -name '*.log' -mmin +40 -type f | xargs rm -f"
  cronjob4="5 * * * * $croncmd4"
  ( crontab -l | grep -v -F "$croncmd4" ; echo "$cronjob4" ) | crontab -
  ############################################################
  # 5. generate metadata xml files
  croncmd5="sh ${installation_folder}cronjobs/generateMetadata.sh"
  cronjob5="45 23 * * * $croncmd5"
  ( crontab -l | grep -v -F "$croncmd5" ; echo "$cronjob5" ) | crontab -
  ############################################################
  # 6. run ows scheduler - to update ows capabilities
  croncmd6="/usr/bin/php ${installation_folder}mapbender/tools/mod_runScheduler.php"
  cronjob6="57 23 * * * $croncmd6"
  ( crontab -l | grep -v -F "$croncmd6" ; echo "$cronjob6" ) | crontab -
  ############################################################
  # 8. materialize wms search view
  croncmd8="su - postgres -c \"psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/materialize_wms_view.sql\""
  cronjob8="0,30 * * * * $croncmd8"
  ( crontab -l | grep -v -F "$croncmd8" ; echo "$cronjob8" ) | crontab -
  ############################################################
  # 9. materialize dataset search view
  croncmd9="su - postgres -c \"psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/materialize_dataset_view.sql\""
  cronjob9="1,31 * * * * $croncmd9"
  ( crontab -l | grep -v -F "$croncmd9" ; echo "$cronjob9" ) | crontab -
  ############################################################
  # 10. materialize wfs search view
  croncmd10="su - postgres -c \"psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/materialize_wfs_view.sql\""
  cronjob10="2,32 * * * * $croncmd10"
  ( crontab -l | grep -v -F "$croncmd10" ; echo "$cronjob10" ) | crontab -
  ############################################################
  # 11. monitor capabilities (wms/wfs)
  croncmd11="sh ${installation_folder}mapbender/tools/monitorCapabilities.sh"
  cronjob11="7 2,4,6,8,10,12,14,16,18,20,22 * * * $croncmd11"
  ( crontab -l | grep -v -F "$croncmd11" ; echo "$cronjob11" ) | crontab -
  ############################################################
  # 12. materialize wmc search view
  croncmd12="su - postgres -c \"psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/materialize_wmc_view.sql\""
  cronjob12="3,13,23,33,43,53 * * * * $croncmd12"
  ( crontab -l | grep -v -F "$croncmd12" ; echo "$cronjob12" ) | crontab -
  ############################################################
  # 13. delete inactive users
  croncmd13="su - postgres -c \"psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/delete_inactive_users.sql\""
  cronjob13="0 0 * * * $croncmd13"
  ( crontab -l | grep -v -F "$croncmd13" ; echo "$cronjob13" ) | crontab -




  ##TODO: send mails
  fi

  ############################################################
  # initially monitor registrated services and add them to catalogue (materialize search tables)
  ############################################################
  eval $croncmd11
  eval $croncmd8
  eval $croncmd9
  eval $croncmd10
  ############################################################
  # things after cli
  ############################################################
  chown -R www-data:www-data ${installation_folder}mapbender/log/

  phppgadmin_realm="phppgadmin"
  digest_phppgadmin="$( printf "%s:%s:%s" "$phppgadmin_user" "$phppgadmin_realm" "$phppgadmin_password" |
             md5sum | awk '{print $1}' )"
  printf "%s:%s:%s\n" "$phppgadmin_user" "$phppgadmin_realm" "$digest_phppgadmin" >> ${installation_folder}"access/.phppgadmin"


#+#+#+#+#+#+#+##+#+#+#+#+#+#+##+#+#+#+#+#+#+##+#+#+#+#+#+#+#
#                                       Django Installation
#+#+#+#+#+#+#+##+#+#+#+#+#+#+##+#+#+#+#+#+#+##+#+#+#+#+#+#+#

apt-get update
apt-get install -y apache2 apache2-dev python3 python3-dev git python3-pip virtualenv libapache2-mod-wsgi-py3 composer zip mysql-utilities zlib1g-dev libjpeg-dev libfreetype6-dev python-dev

cd /opt/
git clone https://git.osgeo.org/gitea/armin11/GeoPortal.rlp

# this directory is used to store php helper scripts for the intermediate geoportal solution
mkdir -p /data/portal

# copy some mapbender related scripts
cp -a /opt/GeoPortal.rlp/scripts/guiapi.php /data/portal

cp -a /data/mapbender/http/geoportal/authentication.php /data/mapbender/http/geoportal/authentication.php.backup
cp -a /opt/GeoPortal.rlp/scripts/authentication.php /data/mapbender/http/geoportal/authentication.php

cp -a /opt/GeoPortal.rlp/scripts/delete_inactive_users.sql /data/mapbender/resources/db/delete_inactive_users.sql

cp -a /data/mapbender/conf/mapbender.conf /data/mapbender/conf/mapbender.conf.backup

# change ip address in various locations
# mapbender
sed -i s/"Location: http:\/\/192.168.56.222"/"Location: http:\/\/$ipaddress"/g /data/mapbender/http/geoportal/authentication.php

# change mapbender login path
sed -i "s/#define(\"LOGIN\", \"http:\/\/\".\$_SERVER\['HTTP_HOST'\].\"\/mapbender\/frames\/login.php\");/define(\"LOGIN\", \"http:\/\/\".\$_SERVER\['HTTP_HOST'\].\"\/mapbender\/frames\/login.php\");/g" /data/mapbender/conf/mapbender.conf

sed -i "s/define(\"LOGIN\", \"http:\/\/\".\$_SERVER\['HTTP_HOST'\].\"\/portal\/anmelden.html\");/#define(\"LOGIN\", \"http:\/\/\".\$_SERVER\['HTTP_HOST'\].\"\/portal\/anmelden.html\");/g" /data/mapbender/conf/mapbender.conf

# django code
sed -i s/"EXTERNAL_INTERFACE = \"127.0.0.1\""/"EXTERNAL_INTERFACE = \"$ipaddress\""/g /opt/GeoPortal.rlp/Geoportal/settings.py


# enable php_serialize
if ! grep -q "php_serialize"  /etc/php/7.0/apache2/php.ini
then
	sed -i s/"session.serialize_handler = php"/"session.serialize_handler = php_serialize"/g /etc/php/7.0/apache2/php.ini
fi

# activate memcached
sed -i s/"session.save_handler = files"/"session.save_handler = memcached"/g /etc/php/7.0/apache2/php.ini
sed -i s"/;     session.save_path = \"N;\/path\""/"session.save_path = \"127.0.0.1:11211\""/g /etc/php/7.0/apache2/php.ini
sed -i s/"Require ip 192.168.56.222"/"Require ip $ipaddress"/g /etc/apache2/sites-enabled/geoportal-apache.conf


cd /opt/GeoPortal.rlp/

# create and activate virtualenv
virtualenv -ppython3 /opt/env
source /opt/env/bin/activate

# install needed python packages
pip install -r requirements.txt
rm -r /opt/GeoPortal.rlp/static
python manage.py collectstatic
python manage.py migrate --fake sessions zero
python manage.py migrate --fake-initial
python manage.py makemigrations useroperations
python manage.py migrate useroperations
python manage.py loaddata useroperations/fixtures/navigation.json



# apache config
if [ ! -f "/etc/apache2/conf-available/wsgi.conf"  ]; then
	echo "WSGIScriptAlias / /opt/GeoPortal.rlp/Geoportal/wsgi.py" >> /etc/apache2/conf-available/wsgi.conf
	echo "WSGIPythonPath /opt/GeoPortal.rlp" >> /etc/apache2/conf-available/wsgi.conf
	echo "WSGIPythonHome /opt/env" >> /etc/apache2/conf-available/wsgi.conf
	echo "<Directory /opt/GeoPortal.rlp/Geoportal>" >> /etc/apache2/conf-available/wsgi.conf
	echo "<Files wsgi.py>" >> /etc/apache2/conf-available/wsgi.conf
	echo "Require all Granted" >> /etc/apache2/conf-available/wsgi.conf
	echo "</Files>" >> /etc/apache2/conf-available/wsgi.conf
	echo "</Directory>" >> /etc/apache2/conf-available/wsgi.conf

fi

a2enconf wsgi
/etc/init.d/apache2 restart

# mediawiki
# repo for backports
if [ ! -f "/etc/apt/sources.list.d/backports.list"  ]; then
	echo "deb http://ftp.debian.org/debian stretch-backports main" >> /etc/apt/sources.list.d/backports.list
fi
apt update
apt-get install -y -t stretch-backports mediawiki

#mysql_secure_installation

mysql -uroot -e "UPDATE mysql.user SET Password=PASSWORD('$mysqlpw') WHERE User='root';"
mysql -uroot -e "update mysql.user set plugin='' where user='root';"
mysql -uroot -e "flush privileges;"

mysql -uroot -p$mysqlpw -e "DELETE FROM mysql.user WHERE User='';"
mysql -uroot -p$mysqlpw -e "DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');"
mysql -uroot -p$mysqlpw -e "DROP DATABASE IF EXISTS test;"
mysql -uroot -p$mysqlpw -e "DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';"
mysql -uroot -p$mysqlpw -e "FLUSH PRIVILEGES;"

mysql -uroot -p$mysqlpw -e "create database Geoportal;"
mysql -uroot -p$mysqlpw Geoportal < /opt/GeoPortal.rlp/scripts/geoportal.sql

cd /tmp/
wget https://translatewiki.net/mleb/MediaWikiLanguageExtensionBundle-2019.01.tar.bz2
tar -kxjf MediaWikiLanguageExtensionBundle-2019.01.tar.bz2
cp -a /tmp/extensions/* /usr/share/mediawiki/extensions
rm -r /tmp/extensions
rm /tmp/MediaWikiLanguageExtensionBundle-2019.01.tar.bz2

cp -a /opt/GeoPortal.rlp/scripts/LocalSettings.php /etc/mediawiki/LocalSettings.php

cd /usr/share/mediawiki
composer require mediawiki/semantic-media-wiki "~2.5" --update-no-dev
php maintenance/update.php --skip-external-dependencies

sed -i s/"\$wgServer = \"http:\/\/192.168.56.222\";"/"\$wgServer = \"http:\/\/$ipaddress\";"/g /etc/mediawiki/LocalSettings.php
sed -i s/"$wgEmergencyContact = \"apache@192.168.56.222\";"/"$wgEmergencyContact = \"apache@$ipaddress\";"/g /etc/mediawiki/LocalSettings.php
sed -i s/"$wgPasswordSender = \"apache@192.168.56.222\";"/"$wgPasswordSender = \"apache@$ipaddress\";"/g /etc/mediawiki/LocalSettings.php

sed -i s/"$wgDBpassword = \"root\";"/"$wgDBpassword = \"$mysqlpw\";"/g /etc/mediawiki/LocalSettings.php

sed -i s/"enableSemantics( '192.168.56.222' );"/"enableSemantics( '$ipaddress' );"/g /etc/mediawiki/LocalSettings.php

}

update(){

#update mapbender
echo update svn
cd /data/svn/mapbender
#su -c 'svn update -r 10053' retterath
su -c 'svn update' retterath
cd /data/svn/
tar -czvf mapbender_trunk.tar.gz mapbender/
mv mapbender_trunk.tar.gz /tmp/
echo copying...
cd /data/
mv /tmp/mapbender_trunk.tar.gz .
echo untar...
tar -xzvf mapbender_trunk.tar.gz
cp /data/mapbender.conf /data/mapbender/conf/
cp /data/geoportal.conf /data/mapbender/conf/
cp /data/extents_geoportal_rlp.map /data/mapbender/tools/wms_extent/extents.map
cp /data/extent_service_geoportal_rlp.conf /data/mapbender/tools/wms_extent/extent_service.conf
cd /data/mapbender/tools
sh ./i18n_update_mo.sh
cd /data/mapbender
rm -rf $(find . -type d -name .svn)
cd /data
chown -R www-data /data/mapbender/http/tmp/
chown -R www-data /data/mapbender/log/
chown -R www-data /data/mapbender/http/geoportal/preview/
chown -R www-data /data/mapbender/http/geoportal/news/
chown -R www-data /data/mapbender/metadata/
chown -R www-data /data/mapbender/http/extensions/mobilemap2/
#set read possibility for locales
chmod -R 755 /data/mapbender/resources/locale/
#cause the path of the login script has another path than the relative pathes must be adopted:
#sed -i "s/\/..\/php\/mod_showMetadata.php?/\/..\/..\/mapbender\/php\/mod_showMetadata.php?/g" /data/mapbender/http/classes/class_wms.php
sed -i "s/LOGIN.\"\/..\/..\/php\/mod_showMetadata.php?resource=layer\&id=\"/str_replace(\"portal\/anmelden.html\",\"\",LOGIN).\"layer\/\"/g" /data/mapbender/http/classes/class_wms.php
sed -i "s/LOGIN.\"\/..\/..\/php\/mod_showMetadata.php?resource=wms\&id=\"/str_replace(\"portal\/anmelden.html\",\"\",LOGIN).\"wms\/\"/g" /data/mapbender/http/classes/class_wms.php
#overwrite login url with baseurl for export to openlayers link
sed -i 's/url = url.replace("http\/frames\/login.php", "");/url = Mapbender.baseUrl + "\/mapbender\/";/g' /data/mapbender/http/javascripts/mod_loadwmc.js
sed -i "s/href = 'http:\/\/www.mapbender.org'/href = 'http:\/\/www.geoportal.rlp.de'/g" /data/mapbender/http/php/mod_wmc2ol.php
#sed -i 's/http:\/\/www.mapbender.org/http:\/\/www.geoportal.rlp.de/g' /data/mapbender/http/php/mod_wmc2ol.php
sed -i 's/Mapbender_logo_and_text.png/logo_geoportal_neu.png/g' /data/mapbender/http/php/mod_wmc2ol.php
sed -i 's/$maxResults = 5;/$maxResults = 20;/' /data/mapbender/http/php/mod_callMetadata.php
sed -i 's/\/\/metadataUrlPlaceholder/$metadataUrl="http:\/\/www.geoportal.rlp.de\/layer\/";/' /data/mapbender/http/php/mod_abo_show.php
sed -i 's/http:\/\/ws.geonames.org\/searchJSON?lang=de&/http:\/\/www.geoportal.rlp.de\/mapbender\/geoportal\/gaz_geom_mobile.php/' /data/mapbender/http/plugins/mod_jsonAutocompleteGazetteer.php
sed -i 's/options.isGeonames = true;/options.isGeonames = false;/' /data/mapbender/http/plugins/mod_jsonAutocompleteGazetteer.php
sed -i 's/options.helpText = "";/options.helpText = "Orts- und Straßennamen sind bei der Adresssuche mit einem Komma voneinander zu trennen!<br><br>Auch Textfragmente der gesuchten Adresse reichen hierbei aus.<br><br>\&nbsp\&nbsp\&nbsp\&nbsp Beispiel:<br>\&nbsp\&nbsp\&nbsp\&nbsp\&nbsp\\"Am Zehnthof 10 , St. Goar\\" oder<br>\&nbsp\&nbsp\&nbsp\&nbsp\&nbsp\\"zehnt 10 , goar\\"<br><br>Der passende Treffer muss in der erscheinenden Auswahlliste per Mausklick ausgewählt werden!";/' /data/mapbender/http/plugins/mod_jsonAutocompleteGazetteer.php

#update django

cd /opt/GeoPortal.rlp
git pull

cp -a /opt/GeoPortal.rlp/scripts/guiapi.php /data/portal

cp -a /opt/GeoPortal.rlp/scripts/authentication.php /data/mapbender/http/geoportal/authentication.php

cp -a /opt/GeoPortal.rlp/scripts/delete_inactive_users.sql /data/mapbender/resources/db/delete_inactive_users.sql

cp -a /data/mapbender/conf/mapbender.conf /data/mapbender/conf/mapbender.conf.backup


# change ip address in various locations
# mapbender
sed -i "s/#define(\"LOGIN\", \"http:\/\/\".\$_SERVER\['HTTP_HOST'\].\"\/mapbender\/frames\/login.php\");/define(\"LOGIN\", \"http:\/\/\".\$_SERVER\['HTTP_HOST'\].\"\/mapbender\/frames\/login.php\");/g" /data/mapbender/conf/mapbender.conf

sed -i "s/define(\"LOGIN\", \"http:\/\/\".\$_SERVER\['HTTP_HOST'\].\"\/portal\/anmelden.html\");/#define(\"LOGIN\", \"http:\/\/\".\$_SERVER\['HTTP_HOST'\].\"\/portal\/anmelden.html\");/g" /data/mapbender/conf/mapbender.conf

# create and activate virtualenv
virtualenv -ppython3 /opt/env
source /opt/env/bin/activate

# install needed python packages
cd /opt/GeoPortal.rlp
pip install -r requirements.txt
rm -r /opt/GeoPortal.rlp/static
python manage.py collectstatic

# change ip address in various locations
OLDADDRESS=`grep -m 1 -oE '((1?[0-9][0-9]?|2[0-4][0-9]|25[0-5])\.){3}(1?[0-9][0-9]?|2[0-4][0-9]|25[0-5])' /data/mapbender/http/geoportal/authentication.php`
sed -i s/"Location: http:\/\/$OLDADDRESS"/"Location: http:\/\/$ipaddress"/g /data/mapbender/http/geoportal/authentication.php

OLDADDRESS=`grep -m 1 -oE '((1?[0-9][0-9]?|2[0-4][0-9]|25[0-5])\.){3}(1?[0-9][0-9]?|2[0-4][0-9]|25[0-5])' /opt/GeoPortal.rlp/useroperations/templates/geoportal.html`
sed -i s/"http:\/\/$OLDADDRESS\/mapbender\/frames\/index.php"/"http:\/\/$ipaddress\/mapbender\/frames\/index.php"/g /opt/GeoPortal.rlp/useroperations/templates/geoportal.html

OLDADDRESS=`grep -m 1 -oE '((1?[0-9][0-9]?|2[0-4][0-9]|25[0-5])\.){3}(1?[0-9][0-9]?|2[0-4][0-9]|25[0-5])' /opt/GeoPortal.rlp/Geoportal/settings.py`
sed -i s/"EXTERNAL_INTERFACE = \"$OLDADDRESS\""/"EXTERNAL_INTERFACE = \"$ipaddress\""/g /opt/GeoPortal.rlp/Geoportal/settings.py
sed -i s/"Require ip $OLDADDRESS"/"Require ip $ipaddress"/g /etc/apache2/sites-enabled/geoportal-apache.conf

OLDADDRESS=`grep -m 1 -oE '((1?[0-9][0-9]?|2[0-4][0-9]|25[0-5])\.){3}(1?[0-9][0-9]?|2[0-4][0-9]|25[0-5])' /etc/mediawiki/LocalSettings.php`
sed -i s/"\$wgServer = \"http:\/\/$OLDADDRESS\";"/"\$wgServer = \"http:\/\/$ipaddress\";"/g /etc/mediawiki/LocalSettings.php
sed -i s/"$wgEmergencyContact = \"apache@$OLDADDRESS\";"/"$wgEmergencyContact = \"apache@$ipaddress\";"/g /etc/mediawiki/LocalSettings.php
sed -i s/"$wgPasswordSender = \"apache@$OLDADDRESS\";"/"$wgPasswordSender = \"apache@$ipaddress\";"/g /etc/mediawiki/LocalSettings.php
sed -i s/"$wgDBpassword = \"root\";"/"$wgDBpassword = \"$mysqlpw\";"/g /etc/mediawiki/LocalSettings.php
sed -i s/"enableSemantics( '$OLDADDRESS' );"/"enableSemantics( '$ipaddress' );"/g /etc/mediawiki/LocalSettings.php

/etc/init.d/apache2 restart


echo "\n"
echo "You may have to check /etc/apache2/sites-enabled/geoportal-apache.conf for the right ip in the Require statement"

}


delete(){

#django deletion

rm -r /opt/env
rm -r /opt/GeoPortal.rlp

# mapbender deletion
cd ${installation_folder}
rm -R ${installation_folder}mapbender
rm -R ${installation_folder}portal
rm -R ${installation_folder}svn
rm -R ${installation_folder}conf
rm -R ${installation_folder}access
rm -R ${installation_folder}db_backup

rm ${installation_folder}geoportal_database_adoption_1.sql
rm ${installation_folder}geoportal_database_adoption_2.sql

rm ${installation_folder}geoportal-apache.conf
rm ${installation_folder}install.log

if [ $use_proxy_apt = 'true' ]; then
    if [ -e "/etc/apt/apt.conf_backup_geoportal" ]; then
        echo "Backup version of file exists - overwrite it with the original one"
        cp /etc/apt/apt.conf_backup_geoportal /etc/apt/apt.conf
        #echo "File does not exist"
        #touch /etc/apt/apt.conf
        #echo "Acquire::http::Proxy \"http://$http_proxy_host:$http_proxy_port\";" > /etc/apt/apt.conf
    fi
fi
if [ -e "/etc/apache2/apache2.conf_backup_geoportal" ]; then
        echo "Backup version of file exists - overwrite it with the original one"
        cp /etc/apache2/apache2.conf_backup_geoportal /etc/apache2/apache2.conf
fi
if [ -e "/etc/apache2/phppgadmin.conf_backup_geoportal" ]; then
        echo "Backup version of file exists - overwrite it with the original one"
        cp /etc/apache2/conf-available/phppgadmin.conf_backup_geoportal /etc/apache2/conf-available/phppgadmin.conf
fi
if [ -e "/etc/php/7.0/apache2/php.ini_geoportal_backup" ]; then
        echo "Backup version of file exists - overwrite it with the original one"
        cp /etc/php/7.0/apache2/php.ini_geoportal_backup /etc/php/7.0/apache2/php.ini
fi
if [ -e "/etc/php/7.0/cli/php.ini_geoportal_backup" ]; then
        echo "Backup version of file exists - overwrite it with the original one"
        cp /etc/php/7.0/cli/php.ini_geoportal_backup /etc/php/7.0/cli/php.ini
fi
if [ -e "/etc/phppgadmin/config.inc.php_geoportal_backup" ]; then
        echo "Backup version of file exists - overwrite it with the original one"
        cp  /etc/phppgadmin/config.inc.php_geoportal_backup /etc/phppgadmin/config.inc.php
fi

rm *.tar.gz*
rm *.sql
rm -R ${installation_folder}cronjobs
rm ${installation_folder}cleanup_geoportal_installation.sh
############################################################
# drop postgresql data
############################################################
su - postgres -c "dropdb -p $mapbender_database_port $mapbender_database_name"
#restore old pg_hba.conf of main - default cluster
cp /etc/postgresql/9.6/main/pg_hba.conf_backup /etc/postgresql/9.6/main/pg_hba.conf
service postgresql restart
cp /etc/subversion/servers_backup_geoportal /etc/subversion/servers

}

backup () {


if [ -d /opt/backup/geoportal_backup_$(date +"%m_%d_%Y") ]; then
  echo "Backup for today already exists. Remove or rename it if you want to use this function"
  exit
fi

mkdir /opt/backup
mkdir /opt/backup/geoportal_backup_$(date +"%m_%d_%Y")


# Django Backup
cp -a /opt/GeoPortal.rlp/Geoportal/settings.py /opt/backup/geoportal_backup_$(date +"%m_%d_%Y")
cp -a /data/mapbender/conf/mapbender.conf /opt/backup/geoportal_backup_$(date +"%m_%d_%Y")

su - postgres -c "pg_dump mapbender > /tmp/geoportal_mapbender_backup.psql"
cp -a /tmp/geoportal_mapbender_backup.psql /opt/backup/geoportal_backup_$(date +"%m_%d_%Y")
mysqldump -uroot -p$mysqlpw Geoportal > /opt/backup/geoportal_backup_$(date +"%m_%d_%Y")/geoportal_mediawiki_backup.mysql


}

check_mode() {

if [ $mode = "install" ]; then
  echo "Performing complete installation"
	install_full
elif [ $mode = "update" ];then
  echo "Updating your geoportal solution"
	update
elif [ $mode = "delete" ];then
  echo "Deleting your geoportal solution"
	delete
elif [ $mode = "backup" ];then
  echo "Backing up your geoportal solution"
  backup
fi


}

usage () {

echo "
This script is for installing and maintaining your geoportal solution
You can choose from the following options:

	--ipaddress=IPADDRESS             			| Default \"127.0.0.1\"
	--proxyip=Proxy IP     	 			        | Default \"None\"
	--proxyport=Proxy Port		  		        | Default \"None\"
	--mapbenderdbuser=User for Database access		| Default \"mapbenderdbuser\"
	--mapbenderdbpassword=Password for database access	| Default \"mapbenderdbpassword\"
	--phppgadmin_user=User for PGAdmin web access		| Default \"postgresadmin\"
	--phppgadmin_password=Password for PGAdmin web access	| Default \"postgresadmin_password\"
	--mysqlpw=database password for MySQL			| Default \"root\"
	--mode=what you want to do				| Default \"none\" [install,update,delete,backup]

"

}


while getopts h-: arg; do
  case $arg in
    h )  usage;exit;;
    - )  LONG_OPTARG="${OPTARG#*=}"
         case $OPTARG in
	   help				)  usage;;
     proxyip=?*     		)  http_proxy_host=$LONG_OPTARG;https_proxy_host=$LONG_OPTARG;;
     proxyport=?*   		)  http_proxy_port=$LONG_OPTARG;https_proxy_port=$LONG_OPTARG;;
	   mapbenderdbuser=?*		)  mapbenderdbuser=$LONG_OPTARG;;
	   mapbenderdbpassword=?*	)  mapbenderdbpassword=$LONG_OPTARG;;
	   phppgadmin_user=?*		)  phppgadmin_user=$LONG_OPTARG;;
	   phppgadmin_password=?*	)  phppgadmin_password=$LONG_OPTARG;;
	   ipaddress=?*			)  ipaddress=$LONG_OPTARG;;
	   mysqlpw=?*			)  mysqlpw=$LONG_OPTARG;;
	   mode=?*			)  mode=$LONG_OPTARG;;
           '' 				)  break ;; # "--" terminates argument processing
           * 				)  echo "Illegal option --$OPTARG" >&2; exit 2 ;;
         esac ;;
    \? ) exit 2 ;;  # getopts already reported the illegal option
  esac
done
shift $((OPTIND-1))

if [ $mode != "none" ];then
	check_mode
fi
