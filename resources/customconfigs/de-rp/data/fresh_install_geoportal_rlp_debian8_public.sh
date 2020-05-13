#!/bin/bash
date
webadmin_email="test@test.de"

mapbender_database_name="mapbender"
mapbender_database_port="5432"
mapbender_database_user="mapbenderdbuser"
mapbender_database_password="mapbenderdbpassword"

installation_folder="/data/"

typo3_database_name="typo3"
# typo3_database_port="xxxx"
typo3_database_user="typo3dbuser"
typo3_database_password="typo3dbpassword"

use_proxy="true"

http_proxy_host="proxy"
http_proxy_port="3128"
https_proxy_host="proxy"
https_proxy_port="3128"


# what should be done
install_system_packages="true"
create_folders="true"

checkout_mapbender_svn="true"
checkout_typo3_svn="true"

install_mapbender_source="true"
install_typo3_source="true"

install_mapbender_database="true"
install_typo3_database="false"

configure_mapbender="true"
configure_typo3="false"
configure_apache="true"

# set some environment variables

if [ $use_proxy = 'true' ]; then
    # set proxy env for wget from shell
    cp /etc/subversion/servers /etc/subversion/servers_backup_geoportal
    export http_proxy=http://$http_proxy_host:$http_proxy_port
    export https_proxy=http://$https_proxy_host:$https_proxy_port
    # for git access behind proxy
    # git config --global http.proxy http://$http_proxy_host:$http_proxy_port
    # git config --global https.proxy http://$https_proxy_host:$https_proxy_port

    # for apt alter or create /etc/apt/apt.conf
    # first line should be: Acquire::http::Proxy "http://$http_proxy_host:$http_proxy_port";

    # for subversion alter /etc/subversion/servers - alter following lines
    # # http-proxy-host = defaultproxy.whatever.com
    sed -i "s/# http-proxy-host = defaultproxy.whatever.com/http-proxy-host = $http_proxy_host/g" /etc/subversion/servers
    sed -i "s/# http-proxy-port = 7000/http-proxy-port = $http_proxy_port/g" /etc/subversion/servers
    # # http-proxy-port = 7000
fi


# 2018-09-16
# debian netinstall 8 - 11 
# df -h - 954MB
# install options: webserver, ssh-server, system

if [ $install_system_packages = 'true' ]; then
    ############################################################
    # install needed debian packages
    ############################################################
    apt-get install -y php5-mysql libapache2-mod-php5 php5-pgsql php5-gd php5-curl php5-cli php-gettext g++ make bison bzip2 unzip zip gdal-bin cgi-mapserver php5-imagick mysql-server imagemagick locate postgresql postgis postgresql-9.4-postgis-2.1 mc zip unzip links w3m lynx arj xpdf dbview odt2txt ca-certificates oidentd gettext phppgadmin gkdebconf subversion subversion-tools php5-memcached php5-memcache php-apc
fi

# +655MB
# mysql root password: mysqlroot - normally debian-sys-maint
# after install - 1.9GB!!!

if [ $create_folders = 'true' ]; then
    # initial installation of geoportal.rlp on debian 8
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

if [ $checkout_typo3_svn = 'true' ]; then
    svn co http://www.gdi-rp-dienste.rlp.de/svn/de-rp/data/portal
fi
if [ $checkout_mapbender_svn = 'true' ]; then
    svn co https://svn.osgeo.org/mapbender/trunk/mapbender
fi
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
# compress and create typo3
############################################################
if [ $install_typo3_source = 'true' ]; then
    cd ${installation_folder}svn/
    tar -czf typo3trunk.tar.gz portal/
    mv typo3trunk.tar.gz /tmp/
    cd ${installation_folder}
    mv /tmp/typo3trunk.tar.gz .
    tar -xzf typo3trunk.tar.gz
    svn info http://www.gdi-rp-dienste.rlp.de/svn/de-rp/data/portal | grep Revision | grep -Eo '[0-9]{1,}' > ${installation_folder}portal/lastinstalled
    rm typo3trunk.tar.gz
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
if [ $install_typo3_source = 'true' ]; then
    cd ${installation_folder}portal/
    rm -rf $(find . -type d -name .svn)
fi
echo 'done.'

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

# su postgres
# createuser  -S -D -R -P mapbenderdbuser #mapbenderdbpassword
# CREATE DATABASE yourdbname;
# CREATE USER youruser WITH ENCRYPTED PASSWORD 'yourpass';
# GRANT ALL PRIVILEGES ON DATABASE yourdbname TO youruser;

su - postgres -c "dropdb -p $mapbender_database_port $mapbender_database_name"
su - postgres -c "createdb -p $mapbender_database_port -E UTF8 $mapbender_database_name -T template0"

sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -c "CREATE USER $mapbender_database_user WITH ENCRYPTED PASSWORD '$mapbender_database_password'"

# su -c - postgres "createlang plpgsql -d $mapbender_database_name" - not needed for debian 8+

su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -f /usr/share/postgresql/9.4/contrib/postgis-2.1/postgis.sql"
su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -f /usr/share/postgresql/9.4/contrib/postgis-2.1/spatial_ref_sys.sql"
su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -f /usr/share/postgresql/9.4/contrib/postgis-2.1/legacy.sql"
su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -f /usr/share/postgresql/9.4/contrib/postgis-2.1/topology.sql"

su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'GRANT ALL PRIVILEGES ON DATABASE $mapbender_database_name TO $mapbender_database_user'"

# su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'grant all on geometry_columns to $mapbender_database_user;'"
#maybe new to postgis 2.x
# su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'grant all on geography_columns to $mapbender_database_user;'"
# su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'grant all on spatial_ref_sys to $mapbender_database_user;'"

su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'ALTER DATABASE $mapbender_database_name OWNER TO $mapbender_database_user'"

#overwrite default pg_hba.conf of main - default cluster
cp /etc/postgresql/9.4/main/pg_hba.conf /etc/postgresql/9.4/main/pg_hba.conf_backup
#####################
cat << EOF > "/etc/postgresql/9.4/main/pg_hba.conf"
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

sudo -u postgres psql -p $mapbender_database_port -d $mapbender_database_name -c 'CREATE SCHEMA mapbender'
sudo -u postgres psql -p $mapbender_database_port -d $mapbender_database_name -c 'ALTER DATABASE mapbender SET search_path TO mapbender,public,pg_catalog,topology'
#####################
sudo -u postgres psql -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/pgsql_schema_2.5.sql 

sudo -u postgres psql -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/pgsql_data_2.5.sql
sudo -u postgres psql -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/pgsql_serial_set_sequences_2.5.sql
sudo -u postgres psql -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.5_to_2.5.1rc1_pgsql_UTF-8.sql
sudo -u postgres psql -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.5.1rc1_to_2.5.1_pgsql_UTF-8.sql
sudo -u postgres psql -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.5.1_to_2.6rc1_pgsql_UTF-8.sql
sudo -u postgres psql -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.6rc1_to_2.6_pgsql_UTF-8.sql
sudo -u postgres psql -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.6_to_2.6.1_pgsql_UTF-8.sql
sudo -u postgres psql -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.6.1_to_2.6.2_pgsql_UTF-8.sql
sudo -u postgres psql -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.6.2_to_2.7rc1_pgsql_UTF-8.sql
sudo -u postgres psql -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.7rc1_to_2.7rc2_pgsql_UTF-8.sql
sudo -u postgres psql -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.7.1_to_2.7.2_pgsql_UTF-8.sql
sudo -u postgres psql -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.7.2_to_2.7.3_pgsql_UTF-8.sql
sudo -u postgres psql -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.7.3_to_2.7.4_pgsql_UTF-8.sql
sudo -u postgres psql -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.7.4_to_2.8_pgsql_UTF-8.sql
sudo -u postgres psql -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.8_pgsql_UTF-8.sql
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

UPDATE gui_category SET category_name='Anwendung' WHERE category_id=2;
UPDATE gui_category SET category_description='Anwendungen (Applications)' WHERE category_id=2;

--add anonymous user
INSERT INTO mb_user (mb_user_id, mb_user_name, mb_user_password, mb_user_owner, mb_user_description, mb_user_login_count, mb_user_email, mb_user_phone, mb_user_department, mb_user_resolution, mb_user_organisation_name, mb_user_position_name, mb_user_phone1, mb_user_facsimile, mb_user_delivery_point, mb_user_city, mb_user_postal_code, mb_user_country, mb_user_online_resource, mb_user_textsize, mb_user_glossar, mb_user_last_login_date, mb_user_digest, mb_user_realname, mb_user_street, mb_user_housenumber, mb_user_reference, mb_user_for_attention_of, mb_user_valid_from, mb_user_valid_to, mb_user_password_ticket, mb_user_firstname, mb_user_lastname, mb_user_academictitle, timestamp_create, timestamp, mb_user_spatial_suggest, mb_user_newsletter, mb_user_allow_survey, mb_user_aldigest) VALUES (2, 'guest', '084e0343a0486ff05530df6c705c8bb4', 1, 'test', 0, 'kontakt@geoportal.rlp.de', NULL, '', 72, '', '', NULL, NULL, NULL, '', NULL, NULL, NULL, 'textsize3', 'ja', '2012-01-26', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2013-07-05 08:09:25.560359', '2015-08-20 10:04:04.952796', 'nein', true, true, NULL);

INSERT INTO mb_user (mb_user_id, mb_user_name, mb_user_password, mb_user_owner, mb_user_description, mb_user_login_count, mb_user_email, mb_user_phone, mb_user_department, mb_user_resolution, mb_user_organisation_name, mb_user_position_name, mb_user_phone1, mb_user_facsimile, mb_user_delivery_point, mb_user_city, mb_user_postal_code, mb_user_country, mb_user_online_resource, mb_user_textsize, mb_user_glossar, mb_user_last_login_date, mb_user_digest, mb_user_realname, mb_user_street, mb_user_housenumber, mb_user_reference, mb_user_for_attention_of, mb_user_valid_from, mb_user_valid_to, mb_user_password_ticket, mb_user_firstname, mb_user_lastname, mb_user_academictitle, timestamp_create, timestamp, mb_user_spatial_suggest, mb_user_newsletter, mb_user_allow_survey, mb_user_aldigest) VALUES (3, 'bereichsadmin1', '3ad58afdc417b975256af7a6d3eda7a5', 1, '', 0, 'kontakt@geoportal.rlp.de', NULL, NULL, 72, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'nein', '2017-07-28', '3c345c2af80400e1e4c94ed0a967e713', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'bereichsadmin1', 'bereichsadmin1', '', '2013-07-05 08:09:25.560359', '2017-07-28 10:12:13.926954', 'nein', false, false, '2a32c845b23d82bea4653810f146397b');


INSERT INTO mb_group VALUES (21, 'Bereichsadmin', 1, 'Diensteadministratoren der Behörden', '', NULL, '', '', '', '', '', '', '', '', '', NULL, NULL, '2013-07-05 08:09:25.732456', '2018-05-25 08:57:07.988259', NULL, NULL, NULL, NULL, NULL, NULL, true);

INSERT INTO mb_group VALUES (22, 'guest', 1, 'Gastgruppe', '', NULL, '', '', '', '', '', '', '', '', '', NULL, NULL, '2013-07-05 08:09:25.732456', '2018-05-25 08:57:07.988259', NULL, NULL, NULL, NULL, NULL, NULL, true);

INSERT INTO mb_group VALUES (23, 'testgruppe1', 1, 'testgruppe1', 'testgruppe1', NULL, 'Musterstraße 11', '11111 Musterstadt', 'Musterstadt', 'DE-RP', 'DE', '1111', '1111', 'mustermail@musterdomain.com', 'http://www.geoportal.rlp.de/metadata/GDI-RP_mit_Markenschutz_RGB_70.png', NULL, NULL, '2013-07-05 08:09:25.732456', '2018-05-25 08:57:07.988259', NULL, NULL, NULL, NULL, NULL, NULL, true);

--guest user into guest group
INSERT INTO mb_user_mb_group VALUES (2, 22, 1);

--bereichsadmin1 into guest group
INSERT INTO mb_user_mb_group VALUES (3, 22, 1);

--bereichsadmin1 into Bereichsadmin group
INSERT INTO mb_user_mb_group VALUES (3, 21, 1);

--bereichsadmin1 into testgruppe1 group - role primary
INSERT INTO mb_user_mb_group VALUES (3, 23, 2);

--bereichsadmin1 into testgruppe1 group - role standard
INSERT INTO mb_user_mb_group VALUES (3, 23, 1);

--root into guest group
INSERT INTO mb_user_mb_group VALUES (1, 22, 1);

--guis: Geoportal-RLP, Geoportal-RLP_erwSuche2, Administration_DE, Portal_Admin, Owsproxy_csv - admin_metadata fehlt noch!!!!

INSERT INTO gui (gui_id, gui_name, gui_description, gui_public) VALUES ('service_container1', 'service_container1', 'service_container1', 1);

INSERT INTO gui (gui_id, gui_name, gui_description, gui_public) VALUES ('service_container1_free', 'service_container1_free', 'service_container1_free', 1);

--guis: Geoportal-RLP, Administration_DE, Owsproxy_csv, admin_metadata, .....
DELETE FROM gui WHERE gui_id IN ('Geoportal-RLP', 'Owsproxy_csv', 'admin_wms_metadata', 'admin_wfs_metadata', 'admin_wmc_metadata', 'admin_metadata', 'admin_ows_scheduler', 'PortalAdmin_DE', 'Administration_DE');
EOF
#####################
# sql for beeing executed after recreating of the guis
#####################
cat << EOF > ${installation_folder}geoportal_database_adoption_2.sql
INSERT INTO gui_gui_category (fkey_gui_id, fkey_gui_category_id) VALUES ('Geoportal-RLP', 2);
INSERT INTO gui_gui_category (fkey_gui_id, fkey_gui_category_id) VALUES ('Administration_DE', 2);
INSERT INTO gui_gui_category (fkey_gui_id, fkey_gui_category_id) VALUES ('PortalAdmin_DE', 2);


INSERT INTO gui_mb_user (fkey_gui_id, fkey_mb_user_id, mb_user_type) VALUES ('PortalAdmin_DE', 1, 'owner');
INSERT INTO gui_mb_user (fkey_gui_id, fkey_mb_user_id, mb_user_type) VALUES ('Geoportal-RLP', 1, 'owner');
INSERT INTO gui_mb_user (fkey_gui_id, fkey_mb_user_id, mb_user_type) VALUES ('Administration_DE', 1, 'owner');
INSERT INTO gui_mb_user (fkey_gui_id, fkey_mb_user_id, mb_user_type) VALUES ('Owsproxy_csv', 1, 'owner');

INSERT INTO gui_mb_user (fkey_gui_id, fkey_mb_user_id, mb_user_type) VALUES ('admin_wms_metadata', 1, 'owner');
INSERT INTO gui_mb_user (fkey_gui_id, fkey_mb_user_id, mb_user_type) VALUES ('admin_wfs_metadata', 1, 'owner');
INSERT INTO gui_mb_user (fkey_gui_id, fkey_mb_user_id, mb_user_type) VALUES ('admin_wmc_metadata', 1, 'owner');
INSERT INTO gui_mb_user (fkey_gui_id, fkey_mb_user_id, mb_user_type) VALUES ('admin_metadata', 1, 'owner');

INSERT INTO gui_mb_user (fkey_gui_id, fkey_mb_user_id, mb_user_type) VALUES ('admin_ows_scheduler', 1, 'owner');

INSERT INTO gui_mb_user (fkey_gui_id, fkey_mb_user_id, mb_user_type) VALUES ('service_container1', 3, 'owner');
INSERT INTO gui_mb_user (fkey_gui_id, fkey_mb_user_id, mb_user_type) VALUES ('service_container1_free', 3, 'owner');

INSERT INTO gui_mb_group (fkey_gui_id, fkey_mb_group_id) VALUES ('Administration_DE', 21);
INSERT INTO gui_mb_group (fkey_gui_id, fkey_mb_group_id) VALUES ('Owsproxy_csv', 21);

INSERT INTO gui_mb_group (fkey_gui_id, fkey_mb_group_id) VALUES ('admin_wmc_metadata', 21);
INSERT INTO gui_mb_group (fkey_gui_id, fkey_mb_group_id) VALUES ('admin_wms_metadata', 21);
INSERT INTO gui_mb_group (fkey_gui_id, fkey_mb_group_id) VALUES ('admin_wfs_metadata', 21);
INSERT INTO gui_mb_group (fkey_gui_id, fkey_mb_group_id) VALUES ('admin_ows_scheduler', 21);
INSERT INTO gui_mb_group (fkey_gui_id, fkey_mb_group_id) VALUES ('admin_metadata', 21);

INSERT INTO gui_mb_group (fkey_gui_id, fkey_mb_group_id) VALUES ('Geoportal-RLP', 22);
INSERT INTO gui_mb_group (fkey_gui_id, fkey_mb_group_id) VALUES ('service_container1_free', 22);
EOF
#####################
sudo -u postgres psql -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}geoportal_database_adoption_1.sql
#####################

# recreate the guis via psql
sudo -u postgres psql -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/gui_Geoportal-RLP.sql # -- maybe a problem: too long entry ...
# 
sudo -u postgres psql -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/gui_Owsproxy_csv.sql
sudo -u postgres psql -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/gui_admin_wms_metadata.sql
sudo -u postgres psql -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/gui_admin_wfs_metadata.sql
sudo -u postgres psql -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/gui_admin_wmc_metadata.sql
sudo -u postgres psql -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/gui_admin_metadata.sql
sudo -u postgres psql -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/gui_admin_ows_scheduler.sql
sudo -u postgres psql -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/gui_PortalAdmin_DE.sql
sudo -u postgres psql -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/gui_Administration_DE.sql
#####################

#####################
sudo -u postgres psql -d mapbender -f ${installation_folder}geoportal_database_adoption_2.sql

# add privilegs for mapbenderdbuser
su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'GRANT USAGE ON SCHEMA mapbender TO $mapbender_database_user'"
su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'GRANT USAGE ON SCHEMA public TO $mapbender_database_user'"

su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA mapbender TO $mapbender_database_user'"
su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO $mapbender_database_user'"
su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'GRANT ALL PRIVILEGES ON DATABASE $mapbender_database_name TO $mapbender_database_user'"
su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'GRANT SELECT, INSERT, UPDATE, DELETE ON DATABASE $mapbender_database_name TO $mapbender_database_user'"

#####################
fi # end of installation of the mapbender database

if [ $configure_mapbender = 'true' ]; then
    echo 'language compiling ... '
    cd ${installation_folder}mapbender/tools
    sh ./i18n_update_mo.sh
    #####################
    cd ${installation_folder}mapbender/
    cp conf/mapbender.conf-dist conf/mapbender.conf
    # create folder to store generated metadata xml documents 
    mkdir ${installation_folder}mapbender/metadata
    #####################
    echo -n 'change more permissones ... '
    # alter owner of folders where webserver should be able to alter data
    chown -R www-data:www-data ${installation_folder}mapbender/http/tmp/
    chown -R www-data:www-data ${installation_folder}mapbender/log/
    chown -R www-data:www-data ${installation_folder}mapbender/http/geoportal/preview/
    chown -R www-data:www-data ${installation_folder}mapbender/http/geoportal/news/
    chown -R www-data:www-data ${installation_folder}mapbender/metadata/ 
    #####################
    echo -n 'adopt mapbender.conf ... '
    # alter connection type to use curl
    sed -i "s/define(\"CONNECTION\", \"http\");/#define(\"CONNECTION\", \"http\");/g" ${installation_folder}mapbender/conf/mapbender.conf
    sed -i "s/#define(\"CONNECTION\", \"curl\");/define(\"CONNECTION\", \"curl\");/g" ${installation_folder}mapbender/conf/mapbender.conf

    # sed -i "s///g" ${installation_folder}mapbender/conf/mapbender.conf

    if [ $use_proxy = 'true' ]; then
	    sed -i "s/define(\"CONNECTION_PROXY\", \"\");/define(\"CONNECTION_PROXY\", \"$http_proxy_host\");/g" ${installation_folder}mapbender/conf/mapbender.conf
	    sed -i "s/define(\"CONNECTION_PROXY\", \"\");/define(\"CONNECTION_PORT\", \"$http_proxy_port\");/g" ${installation_folder}mapbender/conf/mapbender.conf
    fi

    sed -i "s/%%DBSERVER%%/localhost/g" ${installation_folder}mapbender/conf/mapbender.conf
    sed -i "s/%%DBPORT%%/$mapbender_database_port/g" ${installation_folder}mapbender/conf/mapbender.conf
    sed -i "s/%%DBNAME%%/$mapbender_database_name/g" ${installation_folder}mapbender/conf/mapbender.conf
    sed -i "s/%%DBOWNER%%/$mapbender_database_user/g" ${installation_folder}mapbender/conf/mapbender.conf
    sed -i "s/%%DBPASSWORD%%/$mapbender_database_password/g" ${installation_folder}mapbender/conf/mapbender.conf

    # maybe problematic!:
    sed -i "s#/data/#$installation_folder#g" ${installation_folder}mapbender/conf/mapbender.conf
fi

############################################################
# configure and install typo3
############################################################
#configure files for db connect: 
#/data/portal/fileadmin/function/util.php
#/data/portal/typo3conf/localconf.php

if [ $configure_apache = 'true' ]; then
############################################################
# create apache configuration for mapbender
############################################################
echo -n 'create apache configuration ...'
cat << EOF > ${installation_folder}geoportal-apache.conf
<VirtualHost *:80>
        ServerAdmin $webadmin_email
        ReWriteEngine On
        RewriteRule ^/registry/wfs/([\d]+)\/?$ http://127.0.0.1/http_auth/http/index.php?wfs_id=$1 [P,L,QSA,NE]
        RewriteRule ^/layer/(.*) http://%{SERVER_NAME}/mapbender/php/mod_showMetadata.php?resource=layer&languageCode=de&id=$1
        RewriteRule ^/wms/(.*) http://%{SERVER_NAME}/mapbender/php/mod_showMetadata.php?resource=wms&languageCode=de&id=$1
        RewriteRule ^/wmc/(.*) http://%{SERVER_NAME}/mapbender/php/mod_showMetadata.php?resource=wmc&languageCode=de&id=$1
#       RewriteRule ^/metadata/(.*) http://%{SERVER_NAME}/mapbender/php/mod_dataISOMetadata.php?outputFormat=iso19139&id=$1
#       For typo3 installation
        DocumentRoot ${installation_folder}portal
        <Directory />
                Options FollowSymLinks
                AllowOverride None
        </Directory>

        Alias /portal ${installation_folder}portal
        <Directory  ${installation_folder}portal/>
                Allow from all
                Options -Indexes +FollowSymLinks +Includes
                AllowOverride FileInfo
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
a2enmod rewrite
a2enmod cgi
# a2enmod serv-cgi-bin
a2enmod proxy_http
a2enmod headers
a2enmod auth_digest

# to be compatible to older apache2.2 directives:
a2enmod access_compat 
############################################################
a2ensite geoportal-apache
a2dissite 000-default
service apache2 restart

fi #end of apache configuration

############################################################
# create script to uninstall all files and clear databases
############################################################
echo -n 'create shell script to clean folders and databases'
cat << EOF > ${installation_folder}cleanup_geoportal_installation.sh
#!/bin/bash
cd ${installation_folder}
rm -R ${installation_folder}mapbender
rm -R ${installation_folder}portal
rm -R ${installation_folder}svn
rm ${installation_folder}geoportal_database_adoption_1.sql
rm ${installation_folder}geoportal_database_adoption_2.sql

rm ${installation_folder}geoportal-apache.conf
rm ${installation_folder}install.log

rm ${installation_folder}cleanup_geoportal_installation.sh

su - postgres -c "dropdb -p $mapbender_database_port $mapbender_database_name"

#restore old pg_hba.conf of main - default cluster
cp /etc/postgresql/9.4/main/pg_hba.conf_backup /etc/postgresql/9.4/main/pg_hba.conf
service postgresql restart
cp /etc/subversion/servers_backup_geoportal /etc/subversion/servers
EOF
#####################
chmod +x ${installation_folder}cleanup_geoportal_installation.sh
#####################
date



