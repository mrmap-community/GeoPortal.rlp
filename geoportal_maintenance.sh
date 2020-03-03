#!/bin/bash
############################################################
# Geoportal-RLP install script for debian 9 server environment
# 2019-07-04
# debian netinstall 9
# André Holl
# Armin Retterath
# Michel Peltriaux
############################################################


# Variables
options_file="options.txt"
installation_folder="/data/"
ipaddress="127.0.0.1"
hostname="127.0.0.1"
mode="none"

# mapbender/phppgadmin database config
mapbender_database_name="mapbender"
mapbender_database_host="127.0.0.1"
mapbender_database_port="5432"
mapbender_database_user="mapbenderdbuser"
mapbender_database_password="mapbenderdbpassword"
phppgadmin_user="postgresadmin"
phppgadmin_password="postgresadmin_password"
mysql_user="geowiki"
mysql_user_pw="geoportal"
mysql_root_pw="root"

#proxy config
http_proxy=""
http_proxy_host=""
http_proxy_port=""
http_proxy_user=""
http_proxy_pass=""

# misc
webadmin_email="test@test.de"
email_hosting_server="mail.domain.tld"
use_ssl="false"
not_proxy_hosts="localhost,127.0.0.1"


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
bbox_wgs84="6.05 48.9 8.6 50.96"
initial_scale_i="1500000"
map_extent_csv=$bbox_wgs84

background_hybrid_tms_url="http://www.gdi-rp-dienste2.rlp.de/mapcache/tms/1.0.0/topplusbkg@UTM32"
background_aerial_wms_url="http://geo4.service24.rlp.de/wms/dop_basis.fcgi"

# what should be done
install_system_packages="true"
create_folders="true"
checkout_mapbender_svn="true"
checkout_geoportal_git="true"
install_mapbender_source="true"
install_mapbender_database="true"
checkout_mapbender_conf="true"
install_mapbender_conf="true"
configure_mapbender="true"
configure_apache="true"
configure_cronjobs="true"

if [ $use_ssl = 'true' ]; then
    server_url="https://"$hostname
fi
if [ $use_ssl = 'false' ]; then
    server_url="http://"$hostname
fi

# mobilemap.conf
dhm_wms_url="http://www.gdi-rp-dienste2.rlp.de/cgi-bin/mapserv.fcgi?map=/data/umn/geoportal/dhm_query/dhm.map&"
catalogue_interface=$server_url"/mapbender/php/mod_callMetadata.php?"
background_wms_csv="1819,1382,1635"

# initial services
wms_1_url="'http://www.geoportal.rlp.de/mapbender/php/wms.php?layer_id=55468&REQUEST=GetCapabilities&VERSION=1.1.1&SERVICE=WMS'"
wms_2_url="'http://map.krz.de/cgi-bin/mapserv7?map=/opt/gdi/wms/overview_600.map&REQUEST=GetCapabilities&VERSION=1.1.1&SERVICE=wms'"
wms_3_url="'http://map.krz.de/cgi-bin/mapserv7?map=/opt/gdi/wms/overview_owl.map&VERSION=1.1.1&REQUEST=GetCapabilities&SERVICE=WMS'"
# demo wms
wms_4_url="'https://gis.mffjiv.rlp.de/cgi-bin/mapserv?map=/data/mapserver/mapfiles/institutions_0601.map&REQUEST=GetCapabilities&VERSION=1.1.1&SERVICE=WMS'"

#colors for install
red=`tput setaf 1`
green=`tput setaf 2`
reset=`tput sgr0`

determineEmailSettings(){
  sed -i s/"EMAIL_HOST = 'server.domain.tld'"/"EMAIL_HOST = \"$email_hosting_server\""/g ${installation_folder}GeoPortal.rlp/Geoportal/settings.py
  sed -i s/"EMAIL_HOST_USER = 'geoportal@server.domain.tld'"/"EMAIL_HOST_USER = \"$webadmin_email\""/g ${installation_folder}GeoPortal.rlp/Geoportal/settings.py
}

install_full(){

if [ -f $options_file ];then
source $options_file
fi


##################### Geoportal-RLP
wms_1_register_cmd="/usr/bin/php -f ${installation_folder}mapbender/tools/registerOwsCli.php userId=1 guiId='$default_gui_name' serviceType='wms' serviceAccessUrl=$wms_1_url"
wms_2_register_cmd="/usr/bin/php -f ${installation_folder}mapbender/tools/registerOwsCli.php userId=1 guiId='$default_gui_name' serviceType='wms' serviceAccessUrl=$wms_2_url"
wms_3_register_cmd="/usr/bin/php -f ${installation_folder}mapbender/tools/registerOwsCli.php userId=1 guiId='$default_gui_name' serviceType='wms' serviceAccessUrl=$wms_3_url"
##################### Geoportal-RLP_erwSuche2
wms_4_register_cmd="/usr/bin/php -f ${installation_folder}mapbender/tools/registerOwsCli.php userId=1 guiId='$extended_search_default_gui_name' serviceType='wms' serviceAccessUrl=$wms_1_url"
wms_5_register_cmd="/usr/bin/php -f ${installation_folder}mapbender/tools/registerOwsCli.php userId=1 guiId='$extended_search_default_gui_name' serviceType='wms' serviceAccessUrl=$wms_2_url"
##################### demo service -
wms_6_register_cmd="/usr/bin/php -f ${installation_folder}mapbender/tools/registerOwsCli.php userId=3 guiId='service_container1_free' serviceType='wms' serviceAccessUrl=$wms_4_url"

#+#+#+#+#+#+#+##+#+#+#+#+#+#+##+#+#+#+#+#+#+##+#+#+#+#+#+#+#
# Mapbender installation
#+#+#+#+#+#+#+##+#+#+#+#+#+#+##+#+#+#+#+#+#+##+#+#+#+#+#+#+#

# create directories
if [ $create_folders = 'true' ]; then
    echo -e "\n Creating directories for Mapbender! \n"
    # initial installation of geoportal.rlp on debian 9
    ############################################################
    # create folder structure
    ############################################################
    mkdir -pv $installation_folder
    touch $installation_log
    mkdir -pv ${installation_folder}svn/ | tee -a $installation_log
    mkdir -pv ${installation_folder}access/ | tee -a $installation_log

    echo -e "\n${green} Successfully created directories! ${reset}\n" | tee -a $installation_log
fi

#set hostname to ipaddress if no hostname was given
if [ $ipaddress != "127.0.0.1" ] && [ $hostname == "127.0.0.1" ] ;then
  hostname=$ipaddress
fi

if [ "$http_proxy_user" != "" ];then
  echo "Please enter your proxy password"
  read  -sp "Password for $http_proxy_user: " http_proxy_pass
fi

# proxy config
# hexlify credentials for export
if [ "$http_proxy_user" != "" ] && [ "$http_proxy_pass" != "" ];then
  http_proxy_user_hex=`echo $http_proxy_user | xxd -ps -c 200 | tr -d '\n' |  fold -w2 | paste -sd'%' -`
  http_proxy_user_hex=%$http_proxy_user_hex
  http_proxy_user_hex=${http_proxy_user_hex::len-3}

  http_proxy_pass_hex=`echo $http_proxy_pass | xxd -ps -c 200 | tr -d '\n' |  fold -w2 | paste -sd'%' -`
  http_proxy_pass_hex=%$http_proxy_pass_hex
  http_proxy_pass_hex=${http_proxy_pass_hex::len-3}
else
  http_proxy_user_hex=""
  http_proxy_pass_hex=""
fi

# proxy configuration
if [ "$http_proxy" != "" ];then
    # special case, if you need seperate for proxies for apt,svn,mapbender
    if [ $http_proxy == "custom" ];then
      echo "You have chosen custom proxy config, please enter your proxies one after another, leave blank for none, syntax ipaddress:port"
      read  -p "APT Proxy: " apt_proxy
      read  -p "SVN Proxy: " svn_proxy
      read  -p "Mapbender Proxy: " mb_proxy
      custom_proxy= true
      if [ "$apt_proxy" != "" ];then
        http_proxy_host=`echo $apt_proxy | cut -d: -f1`
        http_proxy_port=`echo $apt_proxy | cut -d: -f2`
      else
        http_proxy_host=""
        http_proxy_port=""
      fi
    else
      http_proxy_host=`echo $http_proxy | cut -d: -f1`
      http_proxy_port=`echo $http_proxy | cut -d: -f2`
    fi

    if [ "$http_proxy_host" != "" ] && [  "$http_proxy_port" != "" ];then

    # system proxy
      if [ "$http_proxy_user_hex" != "" ] && [ "$http_proxy_pass_hex" != "" ];then
      	export http_proxy="http://$http_proxy_user_hex:$http_proxy_pass_hex@$http_proxy_host:$http_proxy_port"
      	export https_proxy="http://$http_proxy_user_hex:$http_proxy_pass_hex@$http_proxy_host:$http_proxy_port"
      else
      	export http_proxy="http://$http_proxy_host:$http_proxy_port"
      	export https_proxy="http://$http_proxy_host:$http_proxy_port"
      fi
      # apt_proxy
      if [ -e "/etc/apt/apt.conf" ]; then
          echo -e "\n Apt Config File exists, Backing it up \n"
          cp /etc/apt/apt.conf /etc/apt/apt.conf_backup_geoportal
          echo "Acquire::http::Proxy \"http://$http_proxy_user_hex:$http_proxy_pass_hex@$http_proxy_host:$http_proxy_port\";" > /etc/apt/apt.conf
      else
          echo -e "\n Apt Conf File does not exist, creating it \n"
          touch /etc/apt/apt.conf
          echo "Acquire::http::Proxy \"http://$http_proxy_user_hex:$http_proxy_pass_hex@$http_proxy_host:$http_proxy_port\";" > /etc/apt/apt.conf
      fi
    fi

apt-get update | tee -a $installation_log
apt-get install -y subversion | tee -a $installation_log

    if [ "$custom_proxy" == true ];then
      if [ "$svn_proxy" != "" ];then
        http_proxy_host=`echo $svn_proxy | cut -d: -f1`
        http_proxy_port=`echo $svn_proxy | cut -d: -f2`
      else
        http_proxy_host=""
        http_proxy_port=""
      fi
    fi

    if [ "$http_proxy_host" != "" ] && [ "$http_proxy_port" != "" ];then
      # svn proxy
      cp /etc/subversion/servers /etc/subversion/servers_backup_geoportal
      sed -i "s/# http-proxy-host = defaultproxy.whatever.com/http-proxy-host = $http_proxy_host/g" /etc/subversion/servers
      sed -i "s/# http-proxy-port = 7000/http-proxy-port = $http_proxy_port/g" /etc/subversion/servers
      if [ "$http_proxy_user" != "" ]  && [  "$http_proxy_pass" != "" ];then
        sed -i "s/# http-proxy-username = defaultusername/http-proxy-username = $http_proxy_user/g" /etc/subversion/servers
        sed -i "s/# http-proxy-password = defaultpassword/http-proxy-password = $http_proxy_pass/g" /etc/subversion/servers
      fi
    fi
fi

export no_proxy="localhost,127.0.0.1"

echo -e "
Geoportal Installation $(date).\n
Parameters:\n
--hostname=$hostname\n
--ipaddress=$ipaddress\n
--proxy=$http_proxy\n
--mapbender_database_name=$mapbender_database_name\n
--mapbender_database_user=$mapbender_database_user\n
--mapbender_database_password=$mapbender_database_password\n
--phppgadmin_user=$phppgadmin_user\n
--phppgadmin_password=$phppgadmin_password\n"  > $installation_log

if [[ ${installation_folder} =~ ^/.+/$ ]] ; then
  echo -e "\n Installing into '${installation_folder}' ... \n"  | tee -a $installation_log
else
  echo -e "\n Invalid installation folder '${installation_folder}'! \n Please use preceding and trailing slashed! eg: /data/"  | tee -a $installation_log
  exit
fi

############################################################
# install needed debian packages
############################################################
if [ $install_system_packages = 'true' ]; then
  echo -e "\n Installing needed Debian packages for Mapbender! \n" | tee -a $installation_log
  apt-get update | tee -a $installation_log
  apt-get install -y git php7.0-mysql libapache2-mod-php7.0 php7.0-pgsql php7.0-gd php7.0-curl php7.0-cli  php-gettext g++ make bison bzip2 unzip zip gdal-bin cgi-mapserver php-imagick mysql-server imagemagick locate postgresql postgis postgresql-9.6-postgis-2.3 mc zip unzip links w3m lynx arj xpdf dbview odt2txt ca-certificates oidentd gettext phppgadmin gkdebconf subversion subversion-tools memcached php-memcached php-memcache php-apcu php-apcu-bc curl libproj-dev libapache2-mod-security2 | tee -a $installation_log
  echo -e "\n ${green}Successfully installed Debian packages for Mapbender!${reset} \n" | tee -a $installation_log
fi

############################################################
# adopt php logging - only log errors - needed here, else the installation will throw many notices
############################################################
cp /etc/php/7.0/cli/php.ini /etc/php/7.0/cli/php.ini_geoportal_backup
cp /etc/php/7.0/apache2/php.ini /etc/php/7.0/apache2/php.ini_geoportal_backup


sed -i "s/error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT/error_reporting = E_ERROR/g" /etc/php/7.0/cli/php.ini
sed -i "s/;error_log = php_errors.log/error_log = \/tmp\/php7_cli_errors\.log/g" /etc/php/7.0/cli/php.ini
############################################################

############################################################
# check out svn repositories initially
############################################################

if [ $checkout_mapbender_svn = 'true' ]; then

    if [ -d ${installation_folder}"svn/mapbender" ];then
      cd ${installation_folder}"svn/mapbender"
      svn cleanup
    fi
    cd ${installation_folder}svn/
    echo -e "\n Downloading Mapbender Sources from Git! \n" | tee -a $installation_log
    git clone --progress https://git.osgeo.org/gitea/hollsandre/Mapbender2.8 mapbender | tee -a $installation_log
    if [ $? -eq 0 ];then
      echo -e "\n ${green}Successfully downloaded Mapbender Source!${reset} \n"  | tee -a $installation_log
    else
      echo -e "\n ${red}Downloading of Mapbender failed! Check internet connection and proxy settings in /etc/subversion/servers!${reset} \n" | tee -a $installation_log
      exit
    fi
fi

if [ "$checkout_geoportal_git" = 'true' ]; then
  if [ -d "${installation_folder}GeoPortal.rlp" ];then
  	echo -e "\n ${red} Folder ${installation_folder}GeoPortal.rlp found, please remove it!${reset}\n" | tee -a $installation_log
    exit
  fi
  cd ${installation_folder}
  echo -e "\n Downloading Geoportal Source to ${installation_folder}! \n" | tee -a $installation_log
  git clone --progress https://git.osgeo.org/gitea/armin11/GeoPortal.rlp | tee -a $installation_log
  if [ $? -eq 0 ];then
    echo -e "\n ${green}Successfully downloaded Geoportal Source! ${reset}\n" | tee -a $installation_log
  else
    echo -e "\n ${red}Downloading Geoportal Source failed! Check internet connection or proxy!${reset}\n" | tee -a $installation_log
    exit
  fi
fi

if [ $checkout_mapbender_conf = 'true' ]; then
    cd ${installation_folder}svn
    echo -e "\n Download Mapbender conf from SVN \n"  | tee -a $installation_log
    svn co http://www.gdi-rp-dienste.rlp.de/svn/de-rp/data/conf | tee -a $installation_log
    if [ $? -eq 0 ];then
      echo -e "\n ${green}Successfully downloaded Mapbender Conf!${reset} \n"  | tee -a $installation_log
    else
      echo -e "\n ${red}Downloading of Mapbender Conf failed! Check internet connection and proxy settings in /etc/subversion/servers!${reset} \n" | tee -a $installation_log
      exit
    fi
fi

############################################################
# compress and create mapbender
############################################################
if [ $install_mapbender_source = 'true' ]; then
    echo -e "\n Copying Mapbender Source to ${installation_folder} \n"  | tee -a $installation_log
    cp -a ${installation_folder}svn/mapbender ${installation_folder}
    svn info https://svn.osgeo.org/mapbender/trunk/mapbender | grep Revision | grep -Eo '[0-9]{1,}' > ${installation_folder}mapbender/lastinstalled
    echo -e "\n ${green}Successfully copied Mapbender Source to ${installation_folder}! ${reset}\n"  | tee -a $installation_log
fi

############################################################
# compress and create geoportal default conf from /data/conf
############################################################
if [ $install_mapbender_conf = 'true' ]; then
    echo -e "\n Copying Mapbender Conf to ${installation_folder} \n"  | tee -a $installation_log
    cp -a ${installation_folder}svn/conf ${installation_folder}
    svn info http://www.gdi-rp-dienste.rlp.de/svn/de-rp/data/conf | grep Revision | grep -Eo '[0-9]{1,}' > ${installation_folder}conf/lastinstalled
    echo -e "\n ${green}Successfully copied Mapbender Conf to ${installation_folder}! ${reset}\n"  | tee -a $installation_log
fi

############################################################
# cleanup .svn relicts
############################################################
if [ $install_mapbender_source = 'true' ]; then
    echo -e "\n Delete .svn files \n "
    cd ${installation_folder}mapbender/
    rm -rf $(find . -type d -name .svn)
    echo -e "\n ${green} Successfully deleted .svn files! ${reset}\n "
fi

############################################################
# configure and install mapbender
############################################################
if [ $create_folders = 'true' ]; then
    mkdir -p ${installation_folder}mapbender/http/tmp/wmc
fi

############################################################
# mapbender db
############################################################
if [ $install_mapbender_database = 'true' ]; then

  echo -e "\n Installing Mapbender database \n" | tee -a $installation_log

  su - postgres -c "dropdb --if-exists -p $mapbender_database_port $mapbender_database_name" | tee -a $installation_log
  su - postgres -c "createdb -p $mapbender_database_port -E UTF8 $mapbender_database_name -T template0" | tee -a $installation_log
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -c "DROP USER IF EXISTS $mapbender_database_user" | tee -a $installation_log
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -c "CREATE USER $mapbender_database_user WITH ENCRYPTED PASSWORD '$mapbender_database_password'" | tee -a $installation_log
  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -f /usr/share/postgresql/9.6/contrib/postgis-2.3/postgis.sql" | tee -a $installation_log
  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -f /usr/share/postgresql/9.6/contrib/postgis-2.3/spatial_ref_sys.sql" | tee -a $installation_log
  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -f /usr/share/postgresql/9.6/contrib/postgis-2.3/legacy.sql" | tee -a $installation_log
  su - postgres -c "PGOPTIONS='--client-min-messages=warning' psql -q -p $mapbender_database_port -d $mapbender_database_name -f /usr/share/postgresql/9.6/contrib/postgis-2.3/topology.sql" | tee -a $installation_log
  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'GRANT ALL PRIVILEGES ON DATABASE $mapbender_database_name TO $mapbender_database_user'" | tee -a $installation_log
  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'ALTER DATABASE $mapbender_database_name OWNER TO $mapbender_database_user'" | tee -a $installation_log

  #overwrite default pg_hba.conf of main - default cluster
  cp /etc/postgresql/9.6/main/pg_hba.conf /etc/postgresql/9.6/main/pg_hba.conf_backup
  #####################
  cp -a ${installation_folder}GeoPortal.rlp/scripts/pg_hba.conf /etc/postgresql/9.6/main/pg_hba.conf
  sed -i "s/mapbender_database_name/$mapbender_database_name/g" /etc/postgresql/9.6/main/pg_hba.conf
  sed -i "s/mapbender_database_user/$mapbender_database_user/g" /etc/postgresql/9.6/main/pg_hba.conf

  #####################
  service postgresql restart
  #####################
  cp /etc/phppgadmin/config.inc.php /etc/phppgadmin/config.inc.php_geoportal_backup
  sed -i "s/conf\['extra_login_security'\] = true/conf\['extra_login_security'\] = false/g" /etc/phppgadmin/config.inc.php
  #####################
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -c "CREATE SCHEMA django AUTHORIZATION $mapbender_database_user" | tee -a $installation_log
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'CREATE SCHEMA mapbender' | tee -a $installation_log
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -c "ALTER DATABASE $mapbender_database_name SET search_path TO mapbender,public,pg_catalog,topology" | tee -a $installation_log
  #####################
  sudo -u postgres PGOPTIONS='--client-min-messages=warning' psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/pgsql_schema_2.5.sql | tee -a $installation_log
  sudo -u postgres PGOPTIONS='--client-min-messages=warning' psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/pgsql_data_2.5.sql | tee -a $installation_log
  sudo -u postgres PGOPTIONS='--client-min-messages=warning' psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/pgsql_serial_set_sequences_2.5.sql | tee -a $installation_log
  sudo -u postgres PGOPTIONS='--client-min-messages=warning' psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.5_to_2.5.1rc1_pgsql_UTF-8.sql | tee -a $installation_log
  sudo -u postgres PGOPTIONS='--client-min-messages=warning' psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.5.1rc1_to_2.5.1_pgsql_UTF-8.sql | tee -a $installation_log
  sudo -u postgres PGOPTIONS='--client-min-messages=warning' psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.5.1_to_2.6rc1_pgsql_UTF-8.sql | tee -a $installation_log
  sudo -u postgres PGOPTIONS='--client-min-messages=warning' psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.6rc1_to_2.6_pgsql_UTF-8.sql | tee -a $installation_log
  sudo -u postgres PGOPTIONS='--client-min-messages=warning' psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.6_to_2.6.1_pgsql_UTF-8.sql | tee -a $installation_log
  sudo -u postgres PGOPTIONS='--client-min-messages=warning' psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.6.1_to_2.6.2_pgsql_UTF-8.sql | tee -a $installation_log
  sudo -u postgres PGOPTIONS='--client-min-messages=warning' psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.6.2_to_2.7rc1_pgsql_UTF-8.sql | tee -a $installation_log
  sudo -u postgres PGOPTIONS='--client-min-messages=warning' psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.7rc1_to_2.7rc2_pgsql_UTF-8.sql | tee -a $installation_log
  sudo -u postgres PGOPTIONS='--client-min-messages=warning' psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.7.1_to_2.7.2_pgsql_UTF-8.sql | tee -a $installation_log
  sudo -u postgres PGOPTIONS='--client-min-messages=warning' psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.7.2_to_2.7.3_pgsql_UTF-8.sql | tee -a $installation_log
  sudo -u postgres PGOPTIONS='--client-min-messages=warning' psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.7.3_to_2.7.4_pgsql_UTF-8.sql | tee -a $installation_log
  sudo -u postgres PGOPTIONS='--client-min-messages=warning' psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.7.4_to_2.8_pgsql_UTF-8.sql | tee -a $installation_log
  sudo -u postgres PGOPTIONS='--client-min-messages=warning' psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.8_pgsql_UTF-8.sql | tee -a $installation_log
  #####################
  echo  -e '\n Adopting mapbenders default database to geoportal default options. \n'

  cp -a ${installation_folder}GeoPortal.rlp/scripts/geoportal_database_adoption_1.sql ${installation_folder}
  sed -i "s/\${mapbender_guest_user_id}/${mapbender_guest_user_id}/g" ${installation_folder}geoportal_database_adoption_1.sql
  sed -i "s/\${mapbender_subadmin_default_user_id}/${mapbender_subadmin_default_user_id}/g" ${installation_folder}geoportal_database_adoption_1.sql
  sed -i "s/\${mapbender_subadmin_group_id}/${mapbender_subadmin_group_id}/g" ${installation_folder}geoportal_database_adoption_1.sql
  sed -i "s/\${mapbender_subadmin_default_group_id}/${mapbender_subadmin_default_group_id}/g" ${installation_folder}geoportal_database_adoption_1.sql
  sed -i "s/\${mapbender_guest_group_id}/${mapbender_guest_group_id}/g" ${installation_folder}geoportal_database_adoption_1.sql
  sed -i "s/\${default_gui_name}/${default_gui_name}/g" ${installation_folder}geoportal_database_adoption_1.sql
  sed -i "s/\${extended_search_default_gui_name}/${extended_search_default_gui_name}/g" ${installation_folder}geoportal_database_adoption_1.sql


  #####################
  # sql for beeing executed after recreating of the guis
  #####################

  cp -a ${installation_folder}GeoPortal.rlp/scripts/geoportal_database_adoption_2.sql ${installation_folder}geoportal_database_adoption_2.sql
  sed -i "s/\${mapbender_subadmin_default_user_id}/${mapbender_subadmin_default_user_id}/g" ${installation_folder}geoportal_database_adoption_2.sql
  sed -i "s/\${mapbender_subadmin_group_id}/${mapbender_subadmin_group_id}/g" ${installation_folder}geoportal_database_adoption_2.sql
  sed -i "s/\${mapbender_guest_group_id}/${mapbender_guest_group_id}/g" ${installation_folder}geoportal_database_adoption_2.sql
  sed -i "s/\${default_gui_name}/${default_gui_name}/g" ${installation_folder}geoportal_database_adoption_2.sql
  sed -i "s/\${extended_search_default_gui_name}/${extended_search_default_gui_name}/g" ${installation_folder}geoportal_database_adoption_2.sql
  sed -i "s/\${mapbender_database_user}/${mapbender_database_user}/g" ${installation_folder}geoportal_database_adoption_2.sql
  sed -i "s/\$mapbender_database_user/$mapbender_database_user/g" ${installation_folder}geoportal_database_adoption_2.sql

  #####################
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}geoportal_database_adoption_1.sql | tee -a $installation_log
  #####################

  # recreate the guis via psql
  cp ${installation_folder}mapbender/resources/db/gui_Geoportal-RLP.sql ${installation_folder}gui_${default_gui_name}.sql
  cp ${installation_folder}mapbender/resources/db/gui_Geoportal-RLP_2019.sql ${installation_folder}gui_${default_gui_name}_2019.sql
  # exchange all occurences of old default gui name in sql
  sed -i "s/Geoportal-RLP/${default_gui_name}/g" ${installation_folder}gui_${default_gui_name}.sql
  sed -i "s/Geoportal-RLP_2019/${default_gui_name}_2019/g" ${installation_folder}gui_${default_gui_name}_2019.sql
  # recreate the guis via psql - default gui definition is in installation folder!
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}gui_${default_gui_name}.sql | tee -a $installation_log
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}gui_${default_gui_name}_2019.sql | tee -a $installation_log
  # do the same for extended search gui
  # alter the name of the default extended search gui in the gui definition
  cp ${installation_folder}mapbender/resources/db/gui_Geoportal-RLP_erwSuche2.sql ${installation_folder}gui_${extended_search_default_gui_name}.sql
  # exchange all occurences of old default gui name in sql
  sed -i "s/Geoportal-RLP_erwSuche2/${extended_search_default_gui_name}/g" ${installation_folder}gui_${extended_search_default_gui_name}.sql
  # recreate the guis via psql - default gui definition is in installation folder!
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}gui_${extended_search_default_gui_name}.sql | tee -a $installation_log
  #####################
  # fix invocation of javascript functions for digitize module
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -c "UPDATE gui_element SET e_pos = '3' where e_id = 'kml' AND fkey_gui_id = '${default_gui_name}'" | tee -a $installation_log

  sudo -u postgres PGOPTIONS='--client-min-messages=warning' psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/gui_Owsproxy_csv.sql | tee -a $installation_log
  sudo -u postgres PGOPTIONS='--client-min-messages=warning' psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/gui_admin_wms_metadata.sql | tee -a $installation_log
  sudo -u postgres PGOPTIONS='--client-min-messages=warning' psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/gui_admin_wfs_metadata.sql | tee -a $installation_log
  sudo -u postgres PGOPTIONS='--client-min-messages=warning' psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/gui_admin_wmc_metadata.sql | tee -a $installation_log
  sudo -u postgres PGOPTIONS='--client-min-messages=warning' psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/gui_admin_metadata.sql | tee -a $installation_log
  sudo -u postgres PGOPTIONS='--client-min-messages=warning' psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/gui_admin_ows_scheduler.sql | tee -a $installation_log
  sudo -u postgres PGOPTIONS='--client-min-messages=warning' psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/gui_PortalAdmin_DE.sql | tee -a $installation_log
  sudo -u postgres PGOPTIONS='--client-min-messages=warning' psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/gui_Administration_DE.sql | tee -a $installation_log
  #####################

  # changes for 2019 gui
  sudo -u postgres psql -d mapbender -c "INSERT INTO gui_mb_user (fkey_gui_id, fkey_mb_user_id, mb_user_type) values ('${default_gui_name}_2019',1,'owner')"
  sudo -u postgres psql -d mapbender -c "INSERT INTO gui_mb_group (fkey_gui_id, fkey_mb_group_id) values ('${default_gui_name}_2019',$mapbender_guest_group_id)"
  sudo -u postgres psql -d mapbender -c "INSERT INTO gui_gui_category (fkey_gui_id, fkey_gui_category_id) values ('${default_gui_name}_2019',2);"

  #####################
  sudo -u postgres psql -q -d $mapbender_database_name -f ${installation_folder}geoportal_database_adoption_2.sql | tee -a $installation_log

  # add privilegs for mapbenderdbuser
  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'GRANT USAGE ON SCHEMA mapbender TO $mapbender_database_user'" | tee -a $installation_log
  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'GRANT USAGE ON SCHEMA public TO $mapbender_database_user'" | tee -a $installation_log
  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA mapbender TO $mapbender_database_user'" | tee -a $installation_log
  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO $mapbender_database_user'" | tee -a $installation_log
  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'GRANT ALL PRIVILEGES ON DATABASE $mapbender_database_name TO $mapbender_database_user'" | tee -a $installation_log
  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'GRANT  INSERT, UPDATE, DELETE ON DATABASE $mapbender_database_name TO $mapbender_database_user'" | tee -a $installation_log
  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA mapbender TO $mapbender_database_user'" | tee -a $installation_log
  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO $mapbender_database_user'" | tee -a $installation_log
  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA mapbender TO $mapbender_database_user'" | tee -a $installation_log
  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO $mapbender_database_user'" | tee -a $installation_log
  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'GRANT CREATE ON DATABASE $mapbender_database_name TO $mapbender_database_user'" | tee -a $installation_log
  su - postgres -c "psql -q -p $mapbender_database_port -d $mapbender_database_name -c 'GRANT CREATE ON SCHEMA mapbender TO $mapbender_database_user'" | tee -a $installation_log

  #####################
  # add precise coordinate transformation to proj and postgis extension
  #####################
  wget http://crs.bkg.bund.de/crseu/crs/descrtrans/BeTA/BETA2007.gsb || (echo "Failed to Download BETA2007.gsb" | tee -a $installation_log;exit;)
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
  sudo -u postgres psql -q -d $mapbender_database_name -f ${installation_folder}geoportal_database_proj_adaption.sql | tee -a $installation_log
fi

  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/pgsql_serial_set_sequences_2.7.sql | tee -a $installation_log

  sed -i "s/mapbenderdbuser/$mapbender_database_user/g" ${installation_folder}GeoPortal.rlp/scripts/change_owner.psql
  sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}GeoPortal.rlp/scripts/change_owner.psql | tee -a $installation_log

  echo -e "\n ${green}Successfully installed Mapbender Database!${reset} \n" | tee -a $installation_log

  if [ $checkout_mapbender_conf = 'true' ]; then

    echo -e "\n Configuring Mapbender. \n" | tee -a $installation_log

    cd ${installation_folder}mapbender/tools
    sh ./i18n_update_mo.sh

    ###################
    # create folder to store generated metadata xml documents
    mkdir -p ${installation_folder}mapbender/metadata
    #####################
    echo -e "\n Change owner of ${installation_folder} to www-data... \n" | tee -a $installation_log
    # alter owner of folders where webserver should be able to alter data
    chown -R www-data:www-data ${installation_folder}mapbender/http/tmp/
    chown -R www-data:www-data ${installation_folder}mapbender/log/
    chown -R www-data:www-data ${installation_folder}mapbender/http/geoportal/preview/
    chown -R www-data:www-data ${installation_folder}mapbender/http/geoportal/news/
    chown -R www-data:www-data ${installation_folder}mapbender/metadata/

    # alter connection type to use curl
    #####################
    sed -i "s/define(\"CONNECTION\", \"http\");/#define(\"CONNECTION\", \"http\");/g" ${installation_folder}conf/mapbender.conf
    sed -i "s/#define(\"CONNECTION\", \"curl\");/define(\"CONNECTION\", \"curl\");/g" ${installation_folder}conf/mapbender.conf
    ####################

    #####################
    # define proxy settings
    #####################

    if [ "$custom_proxy" == true ];then
      if [ "$mb_proxy" != "" ];then
        http_proxy_host=`echo $mb_proxy | cut -d: -f1`
        http_proxy_port=`echo $mb_proxy | cut -d: -f2`
      else
        http_proxy_host=""
        http_proxy_port=""
      fi
    fi

    if [ "$http_proxy_host" != "" ]  && [  "$http_proxy_port" != "" ];then
      sed -i "s/define(\"CONNECTION_PROXY\", \"\");/define(\"CONNECTION_PROXY\", \"$http_proxy_host\");/g" ${installation_folder}conf/mapbender.conf
      sed -i "s/define(\"CONNECTION_PORT\", \"\");/define(\"CONNECTION_PORT\", \"$http_proxy_port\");/g" ${installation_folder}conf/mapbender.conf
      sed -i "s/define(\"NOT_PROXY_HOSTS\", \"<ip>,<ip>,<ip>\");/define(\"NOT_PROXY_HOSTS\", \"localhost,127.0.0.1\");/g" ${installation_folder}conf/mapbender.conf

      if [ "$http_proxy_user" != "" ] && [ "$http_proxy_pass" != "" ];then
        sed -i "s/define(\"CONNECTION_USER\", \"\");/ s/define(\"CONNECTION_USER\", \"$http_proxy_user\");/g" ${installation_folder}conf/mapbender.conf
        sed -i "s/define(\"CONNECTION_PASSWORD\", \"\");/ s/define(\"CONNECTION_PASSWORD\", \"$http_proxy_pass\");/g" ${installation_folder}conf/mapbender.conf
      fi
    fi

    if [ "$http_proxy_host" != "" ]  && [  "$http_proxy_port" != "" ];then
        #####################
        # integrated mapserver mapfile for metadata footprints
        #####################
        sed -i "s/#\"wms_proxy_host\" \"%%PROXYHOST%%\"/\"wms_proxy_host\" \"${http_proxy_host}\"/g" ${installation_folder}conf/extents_geoportal_rlp.map
        sed -i "s/#\"wms_proxy_port\" \"%%PROXYPORT%%\"/\"wms_proxy_port\" \"${http_proxy_port}\"/g" ${installation_folder}conf/extents_geoportal_rlp.map

        if [ "$http_proxy_user" != "" ] && [ "$http_proxy_pass" != "" ];then
          sed -i "s/#\"wms_auth_type\" \"%%AUTHTYPE%%\"/\"wms_auth_type\" \"digest\"/g" ${installation_folder}conf/extents_geoportal_rlp.map
          sed -i "s/#\"wms_auth_username\" \"%%USERNAME%%\"/\"wms_auth_username\" \"$http_proxy_user\"/g" ${installation_folder}conf/extents_geoportal_rlp.map
          sed -i "s/#\"wms_auth_password\" \"%%PASSWORD%%\"/\"wms_auth_password\" \"$http_proxy_pass\"/g" ${installation_folder}conf/extents_geoportal_rlp.map
        fi
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
    sed -i "s/%%WEBADMINMAIL%%/$webadmin_email/g" ${installation_folder}conf/mapbender.conf
    sed -i "s#localhost,127.0.0.1,%%DOMAINNAME%%#localhost,127.0.0.1,$hostname,$hostip#g" ${installation_folder}conf/mapbender.conf
    sed -i "s#http://%%DOMAINNAME%%#http://$hostname#g" ${installation_folder}conf/mapbender.conf
    sed -i "s/%%DOMAINNAME%%,vmlxgeoportal1/$hostname,$hostip/g" ${installation_folder}conf/mapbender.conf


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
    sed -i "s/#define(\"PUBLIC_USER_DEFAULT_GUI\", \"Geoportal-RLP\");/define(\"PUBLIC_USER_DEFAULT_GUI\", \"${default_gui_name}\");/g" ${installation_folder}conf/mapbender.conf
    sed -i "s/#define(\"PUBLIC_USER_DEFAULT_SRS\", \"EPSG:25832\");/define(\"PUBLIC_USER_DEFAULT_SRS\", \"EPSG:25832\");/g" ${installation_folder}conf/mapbender.conf

    echo -e "\n Copying configurations! \n" | tee -a $installation_log
    # copy conf files to right places
    cp -v ${installation_folder}conf/geoportal.conf ${installation_folder}mapbender/conf/ | tee -a $installation_log
    cp -v ${installation_folder}conf/mapbender.conf ${installation_folder}mapbender/conf/ | tee -a $installation_log
    # alter other conf files
    sed -i "s#%%INSTALLATIONFOLDER%%#${installation_folder}#g" ${installation_folder}conf/geoportal.conf
    if  ! grep -q "SESSION_NAME"  ${installation_folder}conf/mapbender.conf ;then
      echo 'define("SESSION_NAME", "PHPSESSID");' >> ${installation_folder}conf/mapbender.conf
    fi
    echo -e "\n ${green}Successfully copied configurations! ${reset}\n" | tee -a $installation_log

    # mapfile for metadata wms
    sed -i "s#%%INSTALLATIONFOLDER%%#${installation_folder}#g" ${installation_folder}conf/extents_geoportal_rlp.map
    sed -i "s/dbname=geoportal /dbname=$mapbender_database_name /g" ${installation_folder}conf/extents_geoportal_rlp.map
    sed -i "s/user=postgres /user=$mapbender_database_user password=$mapbender_database_password /g" ${installation_folder}conf/extents_geoportal_rlp.map
    sed -i "s/port=5436 /port=$mapbender_database_port /g" ${installation_folder}conf/extents_geoportal_rlp.map
    sed -i "s/%%BBOXWGS84SPACE%%/$bbox_wgs84/g" ${installation_folder}conf/extents_geoportal_rlp.map
    sed -i "s/\"wms_proxy_host\" \"%%PROXYHOST%%\"/#\"wms_proxy_host\" \"%%PROXYHOST%%\"/g" ${installation_folder}conf/extents_geoportal_rlp.map
    sed -i "s/\"wms_proxy_port\" \"%%PROXYPORT%%\"/#\"wms_proxy_port\" \"%%PROXYPORT%%\"/g" ${installation_folder}conf/extents_geoportal_rlp.map

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

    # alter group id for subadministrators in monitoring tool - use group_id 21 - this is the subadmin mb_group_id
    echo ". /etc/profile
    [ -f /tmp/wmsmonitorlock ] && : || /usr/bin/php7.0 ${installation_folder}mapbender/tools/mod_monitorCapabilities_main.php group:${mapbender_subadmin_group_id} > /dev/null" >> ${installation_folder}mapbender/tools/monitorCapabilities.sh

    #####################
    # register initial services for default and extended search GUIs
    #####################
    cd ${installation_folder}mapbender/tools/
    echo -e '\n Register initial default services. \n' | tee -a $installation_log
    ##################### Geoportal-RLP
    eval $wms_1_register_cmd | tee -a $installation_log
    echo -e "\n"
    eval $wms_2_register_cmd | tee -a $installation_log
    echo -e "\n"
    eval $wms_3_register_cmd | tee -a $installation_log
    echo -e "\n"
    ##################### Geoportal-RLP_erwSuche2
    eval $wms_4_register_cmd | tee -a $installation_log
    echo -e "\n"
    eval $wms_5_register_cmd | tee -a $installation_log
    echo -e "\n"
    ##################### demo service
    eval $wms_6_register_cmd | tee -a $installation_log


    if [ $? -eq 0 ];then
  		echo -e "\n ${green}Successfully registered services! ${reset}\n" | tee -a $installation_log
	else
    	echo -e "\n ${red}Registering services failed! ${reset}\n" | tee -a $installation_log
    fi

    #####################
    # qualify the main gui
    # update database to set initial extent and epsg for Main GUI: TODO: maybe use a hidden layer !
    sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -c "UPDATE gui_wms SET gui_wms_epsg = '$epsg' WHERE fkey_gui_id = '${default_gui_name}'" | tee -a $installation_log
    sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -c "UPDATE layer_epsg SET minx = '$minx', miny = '$miny', maxx = '$maxx', maxy = '$maxy' WHERE fkey_layer_id IN (SELECT layer_id FROM layer WHERE fkey_wms_id IN (SELECT fkey_wms_id FROM gui_wms WHERE fkey_gui_id = '${default_gui_name}' AND gui_wms_position = 0) AND layer_parent='') AND epsg = '$epsg'" | tee -a $installation_log
    # set first wms to be seen in the overview mapframe
    sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -c "UPDATE gui_element_vars SET var_value = '0' WHERE fkey_gui_id='${default_gui_name}' AND fkey_e_id='overview' AND var_name = 'overview_wms'" | tee -a $installation_log
    # set resize option to auto
    sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -c "UPDATE gui_element_vars SET var_value = 'auto' WHERE fkey_gui_id='${default_gui_name}' AND fkey_e_id='resizeMapsize' AND var_name = 'resize_option'" | tee -a $installation_log
    # set max height and width for resize
    sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -c "INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('${default_gui_name}', 'resizeMapsize', 'max_width', '1000', 'define a max mapframe width (units pixel) f.e. 700 or false' ,'var')" | tee -a $installation_log
    sudo -u postgres psql -q -p $mapbender_database_port -d $mapbender_database_name -c "INSERT INTO gui_element_vars(fkey_gui_id, fkey_e_id, var_name, var_value, context, var_type) VALUES('${default_gui_name}', 'resizeMapsize', 'max_height', '600', 'define a max mapframe height (units pixel) f.e. 700 or false' ,'var')" | tee -a $installation_log

    # run these two update scripts again to fix Administration_GUI, 2.7.4 to 2.8 destroys search_wms_view, change mapbender_subadmin_group_id
    sed -i "s/s.fkey_mb_group_id = 36/s.fkey_mb_group_id = ${mapbender_subadmin_group_id}/g" ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.7.4_to_2.8_pgsql_UTF-8.sql
    sudo -u postgres PGOPTIONS='--client-min-messages=warning' psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.7.4_to_2.8_pgsql_UTF-8.sql | tee -a $installation_log
    sudo -u postgres PGOPTIONS='--client-min-messages=warning' psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/pgsql/UTF-8/update/update_2.8_pgsql_UTF-8.sql | tee -a $installation_log


    echo -e "\n ${green}Successfully configured Mapbender! ${reset}\n" | tee -a $installation_log
  fi

  if [ $configure_apache = 'true' ]; then
  ############################################################
  # create apache configuration for mapbender
  ############################################################
  echo -e  "\n Copying and altering apache configuration. \n" | tee -a $installation_log
  cp -a ${installation_folder}/GeoPortal.rlp/scripts/geoportal-apache.conf ${installation_folder}
  sed -i "s/hostname/$hostname/g" ${installation_folder}geoportal-apache.conf
  sed -i "s/webadmin_email/$webadmin_email/g" ${installation_folder}geoportal-apache.conf
  sed -i "s#installation_folder#$installation_folder#g" ${installation_folder}geoportal-apache.conf
  ############################################################
  # copy conf to apache directory and configure apache24+
  ############################################################
  cp -av ${installation_folder}geoportal-apache.conf /etc/apache2/sites-available/ | tee -a $installation_log
  ############################################################
  # activate modules
  ############################################################
  echo -e "\n Enabling apache2 modules. \n" | tee -a $installation_log
  a2enmod rewrite | tee -a $installation_log
  a2enmod cgi | tee -a $installation_log
  # a2enmod serv-cgi-bin
  a2enmod proxy_http | tee -a $installation_log
  a2enmod headers | tee -a $installation_log
  a2enmod auth_digest | tee -a $installation_log
  # to be compatible to older apache2.2 directives:
  a2enmod access_compat | tee -a $installation_log
  ############################################################
  # alter central apache2 conf to allow access to /
  ############################################################
  cp /etc/apache2/apache2.conf /etc/apache2/apache2.conf_backup_geoportal
  # replace only the first occurence of Require all denied - this is the entry for /
  sed -i "0,/Require all denied/{s/Require all denied/Require all granted/}" /etc/apache2/apache2.conf
  sed -i "s/error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT/error_reporting = E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR/g" /etc/php/7.0/apache2/php.ini
  ############################################################
  #
  ############################################################
  ############################################################
  # configure phppgadmin
  ############################################################
  echo -e '\n Adopt phppgadmin default apache24 configuration. \n' | tee -a $installation_log
  cp -a /etc/apache2/conf-available/phppgadmin.conf /etc/apache2/conf-available/phppgadmin.conf_backup_geoportal
  cp -a ${installation_folder}GeoPortal.rlp/scripts/phppgadmin.conf /etc/apache2/conf-available/phppgadmin.conf
  sed -i "s#installation_folder#$installation_folder#g" /etc/apache2/conf-available/phppgadmin.conf
  ############################################################
  # security stuff
  ############################################################

  # iptables rule to restrict connections per ip to 25
  if [ ! -f "/etc/firewall.conf"  ]; then
    cat << EOF > /etc/firewall.conf
# Generated by iptables-save v1.6.0 on Wed Apr 10 08:53:32 2019
*filter
:INPUT ACCEPT [34:2360]
:FORWARD ACCEPT [0:0]
:OUTPUT ACCEPT [18:1640]
-A INPUT -p tcp -m tcp --dport 80 --tcp-flags FIN,SYN,RST,ACK SYN -m connlimit --connlimit-above 100 --connlimit-mask 32 --connlimit-saddr -j REJECT --reject-with tcp-reset
COMMIT
# Completed on Wed Apr 10 08:53:32 2019
EOF
  fi

  # make startup file for iptables conf
  if [ ! -f "/etc/network/if-up.d/iptables"  ]; then
  cat << EOF > /etc/network/if-up.d/iptables
  #!/bin/sh
  iptables-restore < /etc/firewall.conf
EOF
  fi
  iptables-restore < /etc/firewall.conf

  chmod +x /etc/network/if-up.d/iptables

  if  ! grep -q "MaxRequestsPerChild"  /etc/apache2/apache2.conf ;then
    sed -i '/^MaxKeepAliveRequests*/a MaxRequestsPerChild 10000' /etc/apache2/apache2.conf
  fi

  if  ! grep -q "FileETag None"  /etc/apache2/apache2.conf ;then
    sed -i '/^MaxKeepAliveRequests*/a FileETag None' /etc/apache2/apache2.conf
  fi

  if  ! grep -q "Header set X-XSS-Protection \"1; mode=block\""  /etc/apache2/conf-available/security.conf ;then
    echo  "Header set X-XSS-Protection \"1; mode=block\"" >>/etc/apache2/conf-available/security.conf
  fi

  if  ! grep -q "Timeout"  /etc/apache2/conf-available/security.conf ;then
    echo  "Timeout 60" >>/etc/apache2/conf-available/security.conf
  fi

  if  ! grep -q -w "session.cookie_httponly = On"  /etc/php/7.0/apache2/php.ini ;then
    sed -i s/"session.cookie_httponly ="/"session.cookie_httponly = On"/g /etc/php/7.0/apache2/php.ini
  fi

  if  ! grep -q -w "session.cookie_lifetime = 14400"  /etc/php/7.0/apache2/php.ini ;then
    sed -i s/"session.cookie_lifetime = 0"/"session.cookie_lifetime = 14400"/g /etc/php/7.0/apache2/php.ini
  fi

  if  ! grep -q -w "session.hash_function = 0"  /etc/php/7.0/apache2/php.ini ;then
    sed -i s/"session.hash_function = 0"/"session.hash_function = 1"/g /etc/php/7.0/apache2/php.ini
  fi

  if  ! grep -q -w "allow_webdav_methods = Off"  /etc/php/7.0/apache2/php.ini ;then
    sed -i "/^doc_root*/a allow_webdav_methods = Off" /etc/php/7.0/apache2/php.ini
  fi

  if  ! grep -q "\-I"  /etc/memcached.conf ;then
	echo  "-I 10m" >>/etc/memcached.conf
  fi


  #if  ! grep -q "Header always append X-Frame-Options SAMEORIGIN"  /etc/apache2/conf-enabled/security.conf ;then
  #  echo  "Header always append X-Frame-Options SAMEORIGIN" >>/etc/apache2/conf-enabled/security.conf
  #fi

  sed -i s/"ServerTokens OS"/"ServerTokens Prod"/g /etc/apache2/conf-available/security.conf
  sed -i s/"ServerSignature On"/"ServerSignature Off"/g /etc/apache2/conf-available/security.conf
  cp -a  /etc/modsecurity/modsecurity.conf-recommended  /etc/modsecurity/modsecurity.conf
  #sed -i s/"SecRuleEngine DetectionOnly"/"SecRuleEngine On"/g /etc/modsecurity/modsecurity.conf
  rm -rf /usr/share/modsecurity-crs
  echo -e "\n Downloading Modsecurity Ruleset! \n" | tee -a $installation_log
  git clone --progress https://github.com/SpiderLabs/owasp-modsecurity-crs.git /usr/share/modsecurity-crs | tee -a $installation_log
  if [ $? -eq 0 ];then
    echo -e "\n ${green}Successfully downloaded Modsecurity Ruleset! ${reset}\n" | tee -a $installation_log
  else
    echo -e "\n ${red}Downloading Modsecurity Ruleset failed! Check internet connection or proxy!${reset}\n" | tee -a $installation_log
    exit
  fi
  cd /usr/share/modsecurity-crs
  mv crs-setup.conf.example crs-setup.conf


  if [ ! -f "/etc/apache2/mods-available/security2.conf_backup_$(date +"%d_%m_%Y")"  ]; then
    mv /etc/apache2/mods-available/security2.conf /etc/apache2/mods-available/security2.conf_backup_$(date +"%d_%m_%Y")
  fi

  echo "<IfModule security2_module>
  SecDataDir /var/cache/modsecurity
  IncludeOptional /etc/modsecurity/*.conf
  IncludeOptional /usr/share/modsecurity-crs/*.conf
  IncludeOptional /usr/share/modsecurity-crs/rules/*.conf
  SecRequestBodyNoFilesLimit 10485760
  SecRuleRemoveById 920350

  </IfModule>" > /etc/apache2/mods-available/security2.conf

  ############################################################
  # activate apache conf and reload
  ############################################################
  a2enmod security2 | tee -a $installation_log
  a2ensite geoportal-apache | tee -a $installation_log
  a2dissite 000-default | tee -a $installation_log
  service apache2 restart | tee -a $installation_log

  echo -e  "\n ${green}Successfully configured Apache! ${reset}\n" | tee -a $installation_log
fi #end of apache configuration

echo -e  "\n Configuring Cron!\n" | tee -a $installation_log
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
  mkdir -pv ${installation_folder}cronjobs/ | tee -a $installation_log
  # create script to call metadata via localhost
  cat << EOF > ${installation_folder}cronjobs/generateMetadata.sh
#!/bin/bash
curl http://localhost/mapbender/php/mod_exportISOMetadata.php?Type=ALL > /tmp/metadataGeneration.log
EOF
  chmod u+x ${installation_folder}cronjobs/generateMetadata.sh
  ############################################################
  # install cronjobs for root account
  ############################################################

  ############################################################
  # 1. delete old monitoring xmls
  croncmd1="find ${installation_folder}mapbender/tools/tmp -type f -print | xargs rm -f"
  cronjob1="25 1,3,5,7,9,11,13,15,17,19,21,23 * * * $croncmd1"
  if [ -f "/var/spool/cron/crontabs/root" ];then
    ( crontab -l | grep -v -F "$croncmd1" ; echo "$cronjob1" ) | crontab -
  else
    echo "$cronjob1" | crontab -
  fi
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
  croncmd8="su - postgres -c \"PGOPTIONS='--client-min-messages=warning' psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/materialize_wms_view.sql\""
  cronjob8="0,30 * * * * $croncmd8"
  ( crontab -l | grep -v -F "$croncmd8" ; echo "$cronjob8" ) | crontab -
  ############################################################
  # 9. materialize dataset search view
  croncmd9="su - postgres -c \"PGOPTIONS='--client-min-messages=warning' psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/materialize_dataset_view.sql\""
  cronjob9="1,31 * * * * $croncmd9"
  ( crontab -l | grep -v -F "$croncmd9" ; echo "$cronjob9" ) | crontab -
  ############################################################
  # 10. materialize wfs search view
  croncmd10="su - postgres -c \"PGOPTIONS='--client-min-messages=warning' psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/materialize_wfs_view.sql\""
  cronjob10="2,32 * * * * $croncmd10"
  ( crontab -l | grep -v -F "$croncmd10" ; echo "$cronjob10" ) | crontab -
  ############################################################
  # 11. monitor capabilities (wms/wfs)
  croncmd11="sh ${installation_folder}mapbender/tools/monitorCapabilities.sh"
  cronjob11="7 2,4,6,8,10,12,14,16,18,20,22 * * * $croncmd11"
  ( crontab -l | grep -v -F "$croncmd11" ; echo "$cronjob11" ) | crontab -
  ############################################################
  # 12. materialize wmc search view
  croncmd12="su - postgres -c \"PGOPTIONS='--client-min-messages=warning' psql -q -p $mapbender_database_port -d $mapbender_database_name -f ${installation_folder}mapbender/resources/db/materialize_wmc_view.sql\""
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
eval $croncmd12

echo -e  "\n ${green}Successfully configured Cron! ${reset}\n" | tee -a $installation_log

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
echo -e "\n Installing needed Debian packages for Django \n" | tee -a $installation_log
apt-get update | tee -a $installation_log
apt-get install -y apache2 apache2-dev python3 python3-dev git python3-pip virtualenv libapache2-mod-wsgi-py3 composer zip mysql-utilities zlib1g-dev libjpeg-dev libfreetype6-dev python-dev | tee -a $installation_log
echo -e "\n ${green}Successfully installed Debian packages for Django! ${reset}\n" | tee -a $installation_log
cd ${installation_folder}

echo -e "\n Configuring Django. \n" | tee -a $installation_log

# this directory is used to store php helper scripts for the intermediate geoportal solution
mkdir -pv ${installation_folder}mapbender/http/local | tee -a $installation_log

# copy some mapbender related scripts
cp -a ${installation_folder}GeoPortal.rlp/scripts/guiapi.php ${installation_folder}mapbender/http/local
cp -a ${installation_folder}mapbender/http/geoportal/authentication.php ${installation_folder}mapbender/http/geoportal/authentication.php.backup
cp -a ${installation_folder}GeoPortal.rlp/scripts/authentication.php ${installation_folder}mapbender/http/geoportal/authentication.php
cp -a ${installation_folder}GeoPortal.rlp/scripts/delete_inactive_users.sql ${installation_folder}mapbender/resources/db/delete_inactive_users.sql
cp -a ${installation_folder}mapbender/conf/mapbender.conf /${installation_folder}mapbender/conf/mapbender.conf.backup
#only neeed if mass download should be enabled
#cp -a ${installation_folder}GeoPortal.rlp/scripts/mb_downloadFeedClient/javascripts/mb_downloadFeedClient.php ${installation_folder}/mapbender/http/javascripts/
#cp -a ${installation_folder}GeoPortal.rlp/scripts/mb_downloadFeedClient/plugins/mb_downloadFeedClient.php ${installation_folder}/mapbender/http/plugins/
#cp -a ${installation_folder}GeoPortal.rlp/scripts/mb_downloadFeedClient/style.css ${installation_folder}/mapbender/http/extensions/OpenLayers-2.13.1/theme/default/
#cp -a ${installation_folder}GeoPortal.rlp/scripts/mb_downloadFeedClient/move.png ${installation_folder}/mapbender/http/extensions/OpenLayers-2.13.1/theme/default/img/
#cp -a ${installation_folder}GeoPortal.rlp/scripts/mb_downloadFeedClient/select.png ${installation_folder}/mapbender/http/extensions/OpenLayers-2.13.1/theme/default/img/
#cp -a ${installation_folder}GeoPortal.rlp/scripts/mb_downloadFeedClient/OpenLayers.js ${installation_folder}mapbender/http/extensions/OpenLayers-2.13.1/

# change mapbender login path
sed -i "s/#define(\"LOGIN\", \"http:\/\/\".\$_SERVER\['HTTP_HOST'\].\"\/mapbender\/frames\/login.php\");/define(\"LOGIN\", \"http:\/\/\".\$_SERVER\['HTTP_HOST'\].\"\/mapbender\/frames\/login.php\");/g" ${installation_folder}mapbender/conf/mapbender.conf
sed -i "s/define(\"LOGIN\", \"http:\/\/\".\$_SERVER\['HTTP_HOST'\].\"\/portal\/anmelden.html\");/#define(\"LOGIN\", \"http:\/\/\".\$_SERVER\['HTTP_HOST'\].\"\/portal\/anmelden.html\");/g" ${installation_folder}mapbender/conf/mapbender.conf

# django code
sed -i s/"HOSTNAME = \"localhost\""/"HOSTNAME = \"$hostname\""/g ${installation_folder}GeoPortal.rlp/Geoportal/settings.py
sed -i "s#PROJECT_DIR = \"/data/\"#PROJECT_DIR = \"${installation_folder}\"#g" ${installation_folder}GeoPortal.rlp/Geoportal/settings.py
sed -i s/"        'USER':'mapbenderdbuser',"/"        'USER':'$mapbender_database_user',"/g ${installation_folder}GeoPortal.rlp/Geoportal/settings.py
sed -i s/"        'PASSWORD':'mapbenderdbpassword',"/"        'PASSWORD':'$mapbender_database_password',"/g ${installation_folder}GeoPortal.rlp/Geoportal/settings.py
sed -i s/"        'NAME':'mapbender',"/"        'NAME':'$mapbender_database_name',"/g ${installation_folder}GeoPortal.rlp/Geoportal/settings.py
determineEmailSettings

# enable php_serialize
if ! grep -q "php_serialize"  /etc/php/7.0/apache2/php.ini;then
	sed -i s/"session.serialize_handler = php"/"session.serialize_handler = php_serialize"/g /etc/php/7.0/apache2/php.ini
fi

# activate memcached
sed -i s/"session.save_handler = files"/"session.save_handler = memcached"/g /etc/php/7.0/apache2/php.ini
sed -i s"/;     session.save_path = \"N;\/path\""/"session.save_path = \"127.0.0.1:11211\""/g /etc/php/7.0/apache2/php.ini

cd ${installation_folder}GeoPortal.rlp/
echo -e "\n Creating Virtualenv in ${installation_folder}env. \n"
# create and activate virtualenv
virtualenv -ppython3 ${installation_folder}env | tee -a $installation_log

if [ $? -eq 0 ];then
  echo -e "\n ${green} Successfully created virtualenv in ${installation_folder}env! ${reset}\n" | tee -a $installation_log
else
  echo -e "\n ${green} Creation of virtualenv in ${installation_folder}env failed! ${reset}\n" | tee -a $installation_log
  exit
fi

source ${installation_folder}env/bin/activate

# install needed python packages
echo -e "\n Installing needed python libraries. \n" | tee -a $installation_log
pip install -r requirements.txt | tee -a $installation_log
echo -e "\n ${green} Successfully installed python libraries!${reset} \n" | tee -a $installation_log

rm -r ${installation_folder}GeoPortal.rlp/static | tee -a $installation_log
python manage.py collectstatic | tee -a $installation_log
python manage.py migrate --fake sessions zero | tee -a $installation_log
python manage.py migrate --fake-initial | tee -a $installation_log
python manage.py makemigrations useroperations | tee -a $installation_log
python manage.py migrate useroperations | tee -a $installation_log
python manage.py makemessages | tee -a $installation_log
python manage.py compilemessages | tee -a $installation_log
python manage.py loaddata useroperations/fixtures/navigation.json | tee -a $installation_log


/etc/init.d/apache2 restart | tee -a $installation_log

echo -e "\n ${green}Successfully configured Django!${reset} \n" | tee -a $installation_log

# mediawiki
# repo for backports
if [ ! -f "/etc/apt/sources.list.d/backports.list"  ]; then
	echo "deb http://ftp.debian.org/debian stretch-backports main" >> /etc/apt/sources.list.d/backports.list
fi
echo -e "\n Installing Mediawiki! \n" | tee -a $installation_log
apt-get update | tee -a $installation_log
apt-get install -y -t stretch-backports mediawiki | tee -a $installation_log
echo -e "\n ${green}Successfully installed Mediawiki${reset} ! \n" | tee -a $installation_log
#mysql_secure_installation

echo -e "\n Configuring Mysql! \n" | tee -a $installation_log
mysql -uroot -e "UPDATE mysql.user SET Password=PASSWORD('$mysql_root_pw') WHERE User='root';"
mysql -uroot -e "update mysql.user set plugin='' where user='root';"
mysql -uroot -e "flush privileges;"

mysql -uroot -p$mysql_root_pw -e "DELETE FROM mysql.user WHERE User='';"
mysql -uroot -p$mysql_root_pw -e "DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');"
mysql -uroot -p$mysql_root_pw -e "DROP DATABASE IF EXISTS test;"
mysql -uroot -p$mysql_root_pw -e "DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';"
mysql -uroot -p$mysql_root_pw -e "FLUSH PRIVILEGES;"
mysql -uroot -p$mysql_root_pw -e "create database Geoportal;"
mysql -uroot -p$mysql_root_pw -e "CREATE USER '$mysql_user'@'localhost' IDENTIFIED BY '$mysql_user_pw';"
mysql -uroot -p$mysql_root_pw -e "GRANT ALL PRIVILEGES ON Geoportal.* TO '$mysql_user'@'localhost' WITH GRANT OPTION;"
mysql -uroot -p$mysql_root_pw Geoportal < ${installation_folder}GeoPortal.rlp/scripts/geoportal.sql

echo -e "\n ${green}Successfully configured Mysql! ${reset}\n" | tee -a $installation_log

echo -e "\n Downloading MediaWikiLanguageExtensionBundle! \n" | tee -a $installation_log
cd /tmp/
wget https://translatewiki.net/mleb/MediaWikiLanguageExtensionBundle-2019.01.tar.bz2 | tee -a $installation_log
if [ $? -eq 0 ];then
  echo -e "\n ${green}Successfully downloaded MediaWikiLanguageExtensionBundle! ${reset}\n" | tee -a $installation_log
else
  echo -e "\n ${red}Failed to Download MediawikiLanguageExtensionBundle! Check connection or proxy!${reset}\n" | tee -a $installation_log
  exit
fi
tar -kxjf MediaWikiLanguageExtensionBundle-2019.01.tar.bz2
rm /usr/share/mediawiki/extensions/LocalisationUpdate
cp -a /tmp/extensions/* /usr/share/mediawiki/extensions
rm -r /tmp/extensions
rm /tmp/MediaWikiLanguageExtensionBundle-2019.01.tar.bz2

cp -a ${installation_folder}GeoPortal.rlp/scripts/LocalSettings.php /etc/mediawiki/LocalSettings.php
cp -a ${installation_folder}GeoPortal.rlp/scripts/mediawiki_css/* /usr/share/mediawiki/skins/

echo -e "\n Configuring Composer for SemanticMediaWiki extension! \n" | tee -a $installation_log
cd /usr/share/mediawiki
useradd composer
mkdir -p /usr/share/mediawiki/extensions/SemanticMediaWiki/
touch /usr/share/mediawiki/composer.json
chown composer /usr/share/mediawiki/extensions/SemanticMediaWiki/
chown composer /usr/share/mediawiki/composer.json
chown composer /usr/share/mediawiki/
chown -R composer /usr/share/mediawiki/vendor/
su composer -c "composer require mediawiki/semantic-media-wiki "~2.5" --update-no-dev" | tee -a $installation_log

echo -e "\n ${green}Successfully configured composer!${reset} \n" | tee -a $installation_log

sed -i s/"\$wgDefaultSkin = \"vector\";/\$wgDefaultSkin = \"timeless\";"/g /etc/mediawiki/LocalSettings.php
sed -i s/"\$wgServer = \"http:\/\/192.168.56.222\";"/"\$wgServer = \"http:\/\/$hostname\";"/g /etc/mediawiki/LocalSettings.php
sed -i s/"\$wgEmergencyContact = \"apache@192.168.56.222\";"/"\$wgEmergencyContact = \"apache@$hostname\";"/g /etc/mediawiki/LocalSettings.php
sed -i s/"\$wgPasswordSender = \"apache@192.168.56.222\";"/"\$wgPasswordSender = \"apache@$hostname\";"/g /etc/mediawiki/LocalSettings.php
sed -i s/"\$wgDBuser = \"geowiki\";"/"\$wgDBuser = \"$mysql_user\";"/g /etc/mediawiki/LocalSettings.php
sed -i s/"\$wgDBpassword = \"root\";"/"\$wgDBpassword = \"$mysql_user_pw\";"/g /etc/mediawiki/LocalSettings.php
sed -i s/"enableSemantics( '192.168.56.222' );"/"enableSemantics( '$hostname' );"/g /etc/mediawiki/LocalSettings.php
if ! grep -q "\$wgRawHtml ="  /etc/mediawiki/LocalSettings.php;then
	echo "\$wgRawHtml = true;" >> /etc/mediawiki/LocalSettings.php
fi

php maintenance/update.php --quiet --skip-external-dependencies | tee -a $installation_log

echo -e "\n Installation Complete! Doing some checks! \n" | tee -a $installation_log

pgrep apache2 > /dev/null
if [ $? -ne 0 ];then
  echo -e "\n ${red}Apache is not running! Going to check syntax and restart it! \n${reset}" | tee -a $installation_log
  apachectl configtest
  service apache2 restart
else
  echo -e "\n ${green}Apache is up and running! \n${reset}" | tee -a $installation_log
fi

echo -e "\n Trying to reach the index page! \n" | tee -a $installation_log

curl --noproxy 127.0.0.1 -H "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.89 Safari/537.36" -s -L 127.0.0.1 | grep "Geoportal RLP" >> /dev/null

if [ $? -ne 0 ];then
  echo -e "\n ${red}Index page does not look right! Check /var/log/apache2/error.log \n${reset}" | tee -a $installation_log
else
  echo -e "\n ${green}Index pages seems ok! Have Fun! \n${reset}" | tee -a $installation_log
fi

echo -e "\n Details can be found in $installation_log \n" | tee -a $installation_log

}

update(){

  if [ -f $options_file ];then
  source $options_file
  fi

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
              [Nn]* ) exit;break;;
              * ) echo "Please answer yes or no.";;
          esac
      done
    fi
}

  check_django_settings(){
     missing_items=()

     rm /tmp/settings.py
     cd /tmp/
     wget https://git.osgeo.org/gitea/armin11/GeoPortal.rlp/raw/branch/master/Geoportal/settings.py

     while IFS="" read -r p || [ -n "$p" ]
       do
          h=`printf '%s\n' "$p" | cut -d = -f 1`
          h_full=`printf '%s\n' "$p"`
          if ! grep -Fq "$h" ${installation_folder}/GeoPortal.rlp/Geoportal/settings.py
          then
              missing_items+=("$h_full")
           fi

     done < /tmp/settings.py

     if [ ${#missing_items[@]} -ne 0 ]; then
       echo "The following items are present in the masters settings.py but are missing in your local settings.py!"
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

# check for backup
while true; do
    read -p "Do you want me to make a backup before updating y/n?" yn
    case $yn in
        [Yy]* ) backup; break;;
        [Nn]* ) break;;
        * ) echo "Please answer yes or no.";;
    esac
done

custom_update "save"

echo "Checking differences in config files"
check_django_settings

#update mapbender
echo "Backing up Mapbender Configs"
cp -av ${installation_folder}mapbender/conf/mapbender.conf ${installation_folder}mapbender.conf_$(date +"%m_%d_%Y")
cp -av ${installation_folder}mapbender/conf/geoportal.conf ${installation_folder}geoportal.conf_$(date +"%m_%d_%Y")
cp -av ${installation_folder}mapbender/tools/wms_extent/extents.map ${installation_folder}extents_geoportal_rlp.map_$(date +"%m_%d_%Y")
cp -av ${installation_folder}mapbender/tools/wms_extent/extent_service.conf ${installation_folder}extent_service_geoportal_rlp.conf_$(date +"%m_%d_%Y")
cp -av ${installation_folder}mapbender/http/extensions/mobilemap2/scripts/netgis/config.js ${installation_folder}config.js_$(date +"%m_%d_%Y")
cp -av ${installation_folder}mapbender/conf/atomFeedClient.conf ${installation_folder}atomFeedClient.conf_$(date +"%m_%d_%Y")
cp -av ${installation_folder}mapbender/conf/ckan.conf ${installation_folder}ckan.conf_$(date +"%m_%d_%Y")
cp -av ${installation_folder}mapbender/conf/mobilemap2.conf ${installation_folder}mobilemap2.conf_$(date +"%m_%d_%Y")
cp -av ${installation_folder}mapbender/conf/linkedDataProxy.json ${installation_folder}linkedDataProxy.json_$(date +"%m_%d_%Y")
cp -av ${installation_folder}mapbender/conf/twitter.conf ${installation_folder}twitter.conf_$(date +"%m_%d_%Y")
cp -av ${installation_folder}mapbender/conf/bkgGeocoding.conf ${installation_folder}bkgGeocoding.conf_$(date +"%m_%d_%Y")
cp -av ${installation_folder}mapbender/conf/excludeproxyurls.conf ${installation_folder}excludeproxyurls.conf_$(date +"%m_%d_%Y")

echo "Updating Mapbender Sources"
cd ${installation_folder}svn/mapbender
su -c 'git reset --hard'
su -c 'git pull'
cp -a ${installation_folder}svn/mapbender ${installation_folder}

echo "Restoring Mapbender Configs"
cp -av ${installation_folder}mapbender.conf_$(date +"%m_%d_%Y") ${installation_folder}mapbender/conf/mapbender.conf
cp -av ${installation_folder}geoportal.conf_$(date +"%m_%d_%Y") ${installation_folder}mapbender/conf/geoportal.conf
cp -av ${installation_folder}extents_geoportal_rlp.map_$(date +"%m_%d_%Y") ${installation_folder}mapbender/tools/wms_extent/extents.map
cp -av ${installation_folder}extent_service_geoportal_rlp.conf_$(date +"%m_%d_%Y") ${installation_folder}mapbender/tools/wms_extent/extent_service.conf
cp -av ${installation_folder}config.js_$(date +"%m_%d_%Y") ${installation_folder}mapbender/http/extensions/mobilemap2/scripts/netgis/config.js
cp -av ${installation_folder}atomFeedClient.conf_$(date +"%m_%d_%Y") ${installation_folder}mapbender/conf/atomFeedClient.conf
cp -av ${installation_folder}ckan.conf_$(date +"%m_%d_%Y") ${installation_folder}mapbender/conf/ckan.conf
cp -av ${installation_folder}mobilemap2.conf_$(date +"%m_%d_%Y") ${installation_folder}mapbender/conf/mobilemap2.conf
cp -av ${installation_folder}linkedDataProxy.json_$(date +"%m_%d_%Y") ${installation_folder}mapbender/conf/linkedDataProxy.json
cp -av ${installation_folder}twitter.conf_$(date +"%m_%d_%Y") ${installation_folder}mapbender/conf/twitter.conf
cp -av ${installation_folder}bkgGeocoding.conf_$(date +"%m_%d_%Y") ${installation_folder}mapbender/conf/bkgGeocoding.conf
cp -av ${installation_folder}excludeproxyurls.conf_$(date +"%m_%d_%Y") ${installation_folder}mapbender/conf/excludeproxyurls.conf


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
sed -i "s/href = 'http:\/\/www.mapbender.org'/href = 'http:\/\/www.geoportal.rlp.de'/g" ${installation_folder}mapbender/http/php/mod_wmc2ol.php
#sed -i 's/http:\/\/www.mapbender.org/http:\/\/www.geoportal.rlp.de/g' /data/mapbender/http/php/mod_wmc2ol.php
sed -i 's/Mapbender_logo_and_text.png/logo_geoportal_neu.png/g' ${installation_folder}mapbender/http/php/mod_wmc2ol.php
sed -i 's/$maxResults = 5;/$maxResults = 20;/' ${installation_folder}mapbender/http/php/mod_callMetadata.php
sed -i 's/\/\/metadataUrlPlaceholder/$metadataUrl="http:\/\/www.geoportal.rlp.de\/layer\/";/' ${installation_folder}mapbender/http/php/mod_abo_show.php
sed -i 's/http:\/\/ws.geonames.org\/searchJSON?lang=de&/http:\/\/www.geoportal.rlp.de\/mapbender\/geoportal\/gaz_geom_mobile.php/' ${installation_folder}mapbender/http/plugins/mod_jsonAutocompleteGazetteer.php
sed -i 's/options.isGeonames = true;/options.isGeonames = false;/' ${installation_folder}mapbender/http/plugins/mod_jsonAutocompleteGazetteer.php
sed -i 's/options.helpText = "";/options.helpText = "Orts- und Straßennamen sind bei der Adresssuche mit einem Komma voneinander zu trennen!<br><br>Auch Textfragmente der gesuchten Adresse reichen hierbei aus.<br><br>\&nbsp\&nbsp\&nbsp\&nbsp Beispiel:<br>\&nbsp\&nbsp\&nbsp\&nbsp\&nbsp\\"Am Zehnthof 10 , St. Goar\\" oder<br>\&nbsp\&nbsp\&nbsp\&nbsp\&nbsp\\"zehnt 10 , goar\\"<br><br>Der passende Treffer muss in der erscheinenden Auswahlliste per Mausklick ausgewählt werden!";/' ${installation_folder}mapbender/http/plugins/mod_jsonAutocompleteGazetteer.php
sed -i "s/#define(\"LOGIN\", \"http:\/\/\".\$_SERVER\['HTTP_HOST'\].\"\/mapbender\/frames\/login.php\");/define(\"LOGIN\", \"http:\/\/\".\$_SERVER\['HTTP_HOST'\].\"\/mapbender\/frames\/login.php\");/g" ${installation_folder}mapbender/conf/mapbender.conf
sed -i "s/define(\"LOGIN\", \"http:\/\/\".\$_SERVER\['HTTP_HOST'\].\"\/portal\/anmelden.html\");/#define(\"LOGIN\", \"http:\/\/\".\$_SERVER\['HTTP_HOST'\].\"\/portal\/anmelden.html\");/g" ${installation_folder}mapbender/conf/mapbender.conf

echo "Mapbender Update Done"

#update django
echo "Updating Geoportal Project"
cd ${installation_folder}GeoPortal.rlp
echo "Backing up Django Configs"
cp -av ${installation_folder}GeoPortal.rlp/Geoportal/settings.py ${installation_folder}settings.py_$(date +"%m_%d_%Y")
cp -av ${installation_folder}GeoPortal.rlp/useroperations/conf.py ${installation_folder}useroperations_conf.py_$(date +"%m_%d_%Y")

git reset --hard
git pull

echo "Restoring Django Configs"
cp -av ${installation_folder}settings.py_$(date +"%m_%d_%Y") ${installation_folder}GeoPortal.rlp/Geoportal/settings.py
cp -av ${installation_folder}useroperations_conf.py_$(date +"%m_%d_%Y") ${installation_folder}GeoPortal.rlp/useroperations/conf.py

# copy some scripts that are needed for django mapbender integration
cp -av ${installation_folder}GeoPortal.rlp/scripts/guiapi.php ${installation_folder}mapbender/http/local
cp -av ${installation_folder}GeoPortal.rlp/scripts/authentication.php ${installation_folder}mapbender/http/geoportal/authentication.php
cp -av ${installation_folder}GeoPortal.rlp/scripts/delete_inactive_users.sql ${installation_folder}mapbender/resources/db/delete_inactive_users.sql
#only needed if multi download should be enabled
#cp -a ${installation_folder}GeoPortal.rlp/scripts/mb_downloadFeedClient/javascripts/mb_downloadFeedClient.php ${installation_folder}mapbender/http/javascripts/mb_downloadFeedClient.php
#cp -a ${installation_folder}GeoPortal.rlp/scripts/mb_downloadFeedClient/plugins/mb_downloadFeedClient.php ${installation_folder}mapbender/http/plugins/mb_downloadFeedClient.php
#cp -a ${installation_folder}GeoPortal.rlp/scripts/mb_downloadFeedClient/move.png ${installation_folder}mapbender/http/extensions/OpenLayers-2.13.1/img/
#cp -a ${installation_folder}GeoPortal.rlp/scripts/mb_downloadFeedClient/select.png ${installation_folder}mapbender/http/extensions/OpenLayers-2.13.1/img/
#cp -a ${installation_folder}GeoPortal.rlp/scripts/mb_downloadFeedClient/style.css ${installation_folder}mapbender/http/extensions/OpenLayers-2.13.1/theme/default/
#cp -a ${installation_folder}GeoPortal.rlp/scripts/mb_downloadFeedClient/OpenLayers.js ${installation_folder}mapbender/http/extensions/OpenLayers-2.13.1/

# restore custom Files
custom_update "restore"

# create and activate virtualenv
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

}

delete(){

if [ -f $options_file ];then
  source $options_file
fi

#django deletion
rm -r ${installation_folder}env
rm -r ${installation_folder}GeoPortal.rlp

# mapbender deletion
cd ${installation_folder}
rm -R ${installation_folder}mapbender
rm -R ${installation_folder}portal
rm -R ${installation_folder}svn
rm -R ${installation_folder}conf
rm -R ${installation_folder}access
rm -R ${installation_folder}db_backup
rm -r /var/spool/cron/crontabs/root

rm ${installation_folder}geoportal_database_adoption_1.sql
rm ${installation_folder}geoportal_database_adoption_2.sql

rm ${installation_folder}geoportal-apache.conf
rm ${installation_folder}install.log

if [ -e "/etc/apt/apt.conf_backup_geoportal" ]; then
        echo "Restoring APT Conf"
        cp /etc/apt/apt.conf_backup_geoportal /etc/apt/apt.conf
fi

if [ -e "/etc/apache2/apache2.conf_backup_geoportal" ]; then
        echo "Restoring Apache2 Conf"
        cp /etc/apache2/apache2.conf_backup_geoportal /etc/apache2/apache2.conf
fi
if [ -e "/etc/apache2/phppgadmin.conf_backup_geoportal" ]; then
        echo "Restoring Apache Conf for PHPPgAdmin"
        cp /etc/apache2/conf-available/phppgadmin.conf_backup_geoportal /etc/apache2/conf-available/phppgadmin.conf
fi
if [ -e "/etc/php/7.0/apache2/php.ini_geoportal_backup" ]; then
        echo "Restoring Apache2 PHP.ini"
        cp /etc/php/7.0/apache2/php.ini_geoportal_backup /etc/php/7.0/apache2/php.ini
fi
if [ -e "/etc/php/7.0/cli/php.ini_geoportal_backup" ]; then
        echo "Restoring CLI PHP.ini"
        cp /etc/php/7.0/cli/php.ini_geoportal_backup /etc/php/7.0/cli/php.ini
fi
if [ -e "/etc/phppgadmin/config.inc.php_geoportal_backup" ]; then
        echo "Restoring PHPPgAdmin Conf"
        cp  /etc/phppgadmin/config.inc.php_geoportal_backup /etc/phppgadmin/config.inc.php
fi

if [ -e "/etc/apache2/sites-enabled/geoportal-apache.conf" ]; then
        echo "Restoring Apache Site Conf"
        a2dissite geoportal-apache
        a2ensite 000-default
        service apache2 restart

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

# drop MySQL
mysql -uroot -p$mysql_root_pw -e "DROP DATABASE Geoportal;"

}

backup () {

if [ -f $options_file ];then
  source $options_file
fi

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
}

check_mode() {

if [ $mode = "install" ]; then
  echo -n " "
  # log has to be defined here as it depends on installation_folder
  installation_log=${installation_folder}"geoportal_install_$(date +"%m_%d_%Y").log"
  echo -e "\n Performing complete installation \n"
	install_full
elif [ $mode = "update" ];then
  date
  echo -e "\n Updating your geoportal solution \n"
	update
elif [ $mode = "delete" ];then
  date
  echo -e "\n Deleting your geoportal solution \n"
	delete
elif [ $mode = "backup" ];then
  date
  echo -e "\n Backing up your geoportal solution \n"
  backup
fi

}

usage () {

echo "
This script is for installing and maintaining your geoportal solution
You can choose from the following options:

      --options_file=File with install options 		| Default \"options.txt\"
    	--mode=what you want to do			| Default \"none\" [install,update,delete,backup]

"

}

while getopts h-: arg; do
  case $arg in
    h )  usage;exit;;
    - )  LONG_OPTARG="${OPTARG#*=}"
         case $OPTARG in
	   help				)  usage;;
	   options_file=?*		)  options_file=$LONG_OPTARG;;
	   mode=?*			)  mode=$LONG_OPTARG;;
           '' 				)  break ;; # "--" terminates argument processing
           * 				)  echo "Illegal option --$OPTARG" >&2; usage; exit 2 ;;
         esac ;;
    \? ) exit 2 ;;  # getopts already reported the illegal option
  esac
done
shift $((OPTIND-1))

if [ $mode != "none" ];then
	check_mode
fi
