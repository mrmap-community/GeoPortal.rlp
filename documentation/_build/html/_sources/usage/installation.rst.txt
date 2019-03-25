*************
Installation
*************



**Requirements:**

* Debian 9 with working internet connection
* Git installed

**How to install:**

./geoportal_maintenance.sh \\-\\-help

This script is for installing and maintaining your geoportal solution
You can choose from the following options:

| \\-\\-ipaddress=IPADDRESS
| Default: "127.0.0.1"

| \\-\\-proxyip=Proxy IP
| Default: "None"

| \\-\\-proxyport=Proxy Port                                  
| Default: "None"

| \\-\\-mapbenderdbuser=User for Database access              
| Default: "mapbenderdbuser"

| \\-\\-mapbenderdbpassword=Password for database access      
| Default: "mapbenderdbpassword"

| \\-\\-phppgadmin_user=User for PGAdmin web access           
| Default: "postgresadmin"

| \\-\\-phppgadmin_password=Password for PGAdmin web access   
| Default: "postgresadmin_password"

| \\-\\-mysqlpw=database password for MySQL                   
| Default: "root"

| \\-\\-mode=what you want to do                              
| Default: "none" [install,update,delete,backup]

**Description**:

mandatory:

\\-\\-ipaddress             -> The address/name of your external interface. This will be used for building links that refer to your server.

\\-\\-mode                  -> What you want to do. Choices are install | update | delete | backup.

optional:

\\-\\-proxyip               -> IP Address of your local proxy server. Will be inserted in: apt.conf, mapbender.conf, subversion.conf

\\-\\-proxyport             -> Port of your local proxy server. Will be inserted in: apt.conf, mapbender.conf, subversion.conf

\\-\\-mapbenderdbuser       -> User for accessing the mapbender database. Will be created on install.

\\-\\-mapbenderdbpassword   -> Password for mapbenderdbuser.

\\-\\-phppgadmin_user       -> User for the PHPPgAdmin Webinterface.

\\-\\-phppgadmin_password   -> Password for phppgadmin_user.

\\-\\-mysqlpw               -> Passwort for the MySql root user.

**Examples**

Full install:

geoportal_maintenance.sh \\-\\-ipaddress=192.168.0.2 \\-\\-proxyip=192.168.0.254 \\-\\-proxyport=3128 \\-\\-mapbenderdbuser=MyPostgresDBUser \\-\\-mapbenderdbpassword=MyPostgresDBPassword
\\-\\-phppgadmin_user=MyPHPPgAdminUser \\-\\-phppgadmin_password=MyPHPPgAdminPassword \\-\\-mysqlpw=MyMySQLRootPW \\-\\-mode=install

Update:

geoportal_maintenance.sh \\-\\-ipaddress=192.168.0.2 \\-\\-proxyip=192.168.0.254 \\-\\-proxyport=3128 \\-\\-mapbenderdbuser=MyPostgresDBUser \\-\\-mapbenderdbpassword=MyPostgresDBPassword
\\-\\-phppgadmin_user=MyPHPPgAdminUser \\-\\-phppgadmin_password=MyPHPPgAdminPassword \\-\\-mysqlpw=MyMySQLRootPW \\-\\-mode=update

Delete:

geoportal_maintenance.sh \\-\\-mode=delete

Backup:

geoportal_maintenance.sh \\-\\-mode=backup

default credentials:

mysql       -> root:root
wiki        -> root:rootroot
postgres    -> postgres:
phppgadmin  -> postgresadmin:postgresadmin_password


Things that need to be done after installation:

Create Navigation:
Nagivation items are stored in database and can be found in the table navigations under the django schema.
An example navigation is created upon installation and can be used as reference.
