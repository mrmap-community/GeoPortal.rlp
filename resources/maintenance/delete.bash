#!/bin/bash
installation_folder="/data/"
mapbender_database_name="mapbender"
mapbender_database_port="5432"
mysql_root_pw="root"

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
