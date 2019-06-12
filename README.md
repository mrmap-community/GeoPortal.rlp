<p>in partnership with <br>
<img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/cd/Coat_of_arms_of_Hesse.svg/165px-Coat_of_arms_of_Hesse.svg.png" height="50"/><a href="https:/www.hessen.de" target="_blank">German Federal State Hesse</a><br>
<img src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/8e/Wappen_des_Saarlands.svg/120px-Wappen_des_Saarlands.svg.png" height="50"/><a href="https:/www.saarland.de" target="_blank">German Federal State Saarland</a><br>
<img src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/89/Coat_of_arms_of_Rhineland-Palatinate.svg/165px-Coat_of_arms_of_Rhineland-Palatinate.svg.png" height="50"/><a href="https:/www.rlp.de" target="_blank">German Federal State Rhineland-Palatinate</a><br>
<img src="https://upload.wikimedia.org/wikipedia/commons/thumb/7/75/DEU_Landau_in_der_Pfalz_COA.svg/210px-DEU_Landau_in_der_Pfalz_COA.svg.png" height="50"/><a href="https:/www.landau.de" target="_blank">City of Landau in the Rhine Palatinate area</a><br>
<img src="https://upload.wikimedia.org/wikipedia/de/thumb/0/06/Kommunales_Rechenzentrum_Minden-Ravensberg-Lippe_Logo.svg/330px-Kommunales_Rechenzentrum_Minden-Ravensberg-Lippe_Logo.svg.png" height="50"/><a href="https:/www.krz.de" target="_blank">Municipal Data Center Lemgo</a><br>
<img src="https://www.lwk-rlp.de/typo3temp/_processed_/7/d/csm_logo_dummy_3b48412330.png" height="50"/><a href="https://www.lwk-rlp.de" target="_blank">Agricultural Chamber of German Federal State Rhineland-Palatinate</a><br>
</p>

# GeoPortal.rlp

A complete SDI-Suite for the management of OWS (WMS / WFS, CSW), metadata (iso19139), users, organizations, and licences. It comes with OWS-security-proxy, CKAN-Interface, map viewers, WMC and KML handling and the possbility to generate persistent URIs for all resources.

This repo will be used to develop a new FOSS SDI framework based on django. The blueprint for this framework is the former OSGEO project mapbender2. All over the world, there are many older mapbender installations online. The code of mapbender2 is hosted at https://trac.osgeo.org/mapbender/browser/trunk and will be maintained till all relevant modules are re-implemented with django. The old documentation is available at https://mb2wiki.mapbender2.org/Mapbender_Wiki .

Serverside catalogue interface documentation: https://mb2wiki.mapbender2.org/SearchInterface

ISO Metadata (for datasets and services) is build by cronjob in predefined folder. To publish this metadata a CSW interface have to be set up. Maybe geonetwork or pycsw are good candidates. Metadata and capabilities complies to the EU INSPIRE-Directive.

The development will be done using recent debian os with postgis as rdbms.

Existing mapbender2 installations with a great amount of resources and users:

- [GeoPortal Rhineland-Palatinate](http://www.geoportal.rlp.de) - 24.000 user and 17.700 layer
- [GeoPortal Hesse](http://www.geoportal.hessen.de)
- [GeoPortal Saarland](http://www.geoportal.saarland.de)

## Get Started

### Prototype

https://opendata.geoportal.rlp.de

### Try it out!

Live debian9 ISO image for testing purposes (django and mediawiki based frontend, mapbender2 backend and viewer):

http://www.geoportal.rlp.de/metadata/geoportal-live.iso

### Installation

Fast install on local system (only for testing, default passwords!):
```shell
wget --no-check-certificate https://git.osgeo.org/gitea/armin11/GeoPortal.rlp/raw/branch/master/geoportal_maintenance.sh
chmod +x geoportal_maintenance.sh
./geoportal_maintenance.sh --mode=install --ipaddress=127.0.0.1 [options]
```

Documentation can be found, in the documentation directory in the project folder under documentation/_build/html/index.html.


Requirements:

* Debian 9 with working internet connection.
* Python >= 3.5
* pip
* apache2
```shell
./geoportal_maintenance.sh --help
```

This script is for installing and maintaining your geoportal solution
You can choose from the following **options**:

	--install_dir=Directory for installation                | Default "/data/"
	--ipaddress=IPADDRESS                                   | Default "127.0.0.1"
	--proxy=ProxyIP:Port                                    | Default "None" ; Syntax --proxy=1.2.3.4:8080
	--proxyuser=username                                    | Default ""
	--proxypw=Password for proxy auth                       | Default ""
	--mapbenderdbuser=User for Database access              | Default "mapbenderdbuser"
	--mapbenderdbpw=Password for database access            | Default "mapbenderdbpassword"
	--phppgadmin_user=User for PGAdmin web access           | Default "postgresadmin"
	--phppgadmin_pw=Password for PGAdmin web access         | Default "postgresadmin_password"
	--mysqlpw=database password for MySQL                   | Default "root"
	--mode=what you want to do                              | Default "none" [install,update,delete,backup]

Description:  

	mandatory:  
	--ipaddress             -> The address of your external interface.
	--hostname              -> FQDN of your Server www.example.rlp.de
	--mode                  -> What you want to do. Choices are install | update | delete | backup.

	optional:  
	--proxy                 -> IP Address:Port of your local proxy server. Will be inserted in: apt.conf, mapbender.conf, subversion.conf
                            Special case: --proxyip=custom lets you choose a different proxy for each service above
	--proxyuser             -> Username for proxy auth
	--proxypw               -> Passwort for proxy auth
	--mapbenderdbuser       -> User for accessing the mapbender database. Will be created on install.
	--mapbenderdbpw         -> Password for mapbenderdbuser.
	--phppgadmin_user       -> User for the PHPPgAdmin Webinterface.
	--phppgadmin_pw         -> Password for phppgadmin_user.
	--mysqlpw               -> Passwort for the MySql root user.

Examples:  
Install:  
```shell
geoportal_maintenance.sh --ipaddress=192.168.0.2 --proxyip=192.168.0.254 --proxyport=3128 --mapbenderdbuser=MyPostgresDBUser --mapbenderdbpw=MyPostgresDBPassword --phppgadmin_user=MyPHPPgAdminUser ---phppgadmin_pw=MyPHPPgAdminPassword --mysqlpw=MyMySQLRootPW --mode=install
```

Update:
```shell
geoportal_maintenance.sh --mode=update
```

Delete:  
```shell
geoportal_maintenance.sh --mode=delete
```

Backup:  
```shell
geoportal_maintenance.sh --mode=backup
```

default credentials:  
mysql       -> root:root  
wiki        -> root:rootroot  
postgres    -> postgres:  
phppgadmin  -> postgresadmin:postgresadmin_password

### Things that need to be done after installation:  

If everything goes fine, you dont have to do anything else on the command line than executing the script.
Upon successful installation the system should be ready to use, to verify that, point your browser at the IP or Hostname of your GeoPortal instance. If you dont see the Geoportal design, something might have gone wrong, in this case write an issue or go to
the debugging section.

The navigation is located on the left side and its items come from the database. To change the content in the navigation bar, you go to http://IP-ADDRESS/admin, which is the django admin interface. Now login with the default credentials root:root and change them. To do so,  you click on Users and select the root user as its the only entry. The Password field refers to a form where you can change your password. 

Now you can alter the content of the navigation bar to your needs, it can be found in the table Navigations. After opening the table you should arrage the listing by position ascending, as this is the order they will be seen on the web interface. There are parent and child items. Parent items have a empty URL and parent field.

Fields of the navigation table:

* POSITION -> Order in the navigation bar, use 
* NAME -> Name that will be displayed in the navigation bar.
* PAGE_IDENTIFIER -> internal string, use NAME without upper chars and spaces
* URL -> Only for child items, dont change the "wiki" entry as it points to your mediawiki
        * use /article/NAME to create an item with NAME that refers to the mediawiki page, if you create the corresponding page in the mediawiki itself, it will be rendered transparently into the webinterface. One example is the "Meldungen" article.
* ICON_NAME -> Only for parent items, the icon you see in the navigation bar
* PARENT -> Only needed for child items, see examples

At this point the mapbender database contains three users which are: 
root:root; -> superdamin, can do everything
bereichsadmin1:bereichsadmin1; -> subadmin, can register&publish services
guest:AUTOMATIC_SESSION -> guest session, mostly for just viewing 

When your content and navigation is ready you can go ahead and start registering services.
To do so, login as root or bereichsadmin1 and again, change password first. After successful login and password change you can click on the little grid sign on the right to open the default gui, which is the mapviewer. Configuration of Mapbender and registration of services can be found in other guis. To change the gui, click on the sign with the grid and the arrow pointing upwards. Here you can select Administration_DE for service management (WMS, WFS, WMC, Metadata) and PortalAdmin_DE for user, group, role, category management and some maintenance functions. Documentation on Mapbender is currently only available in German and is located on the old portal http://www.geoportal.rlp.de/portal/hilfe/.



#### Debugging - Places to look

* /var/log/apache2/error.log -> Apache and Django errors
* /data(default)/mapbender/log/mb_err_log_* -> Mapbender specific errors


##### Important files and variables

`/data/mapbender/conf/mapbender.conf` 

```shell
# HOSTNAME WHITELIST
define("HOSTNAME_WHITELIST","xxx");
```
Where `xxx` is your servers IP address.

##### Geoportal settings.py
Back into your project to `Geoportal/settings.py`. Edit both of these variables to match your needs:
* `HOSTNAME`
    * Declares the hostname of your webpage for deploying. For now just keep the IP address inside without protocol
* `HOSTIP`
    * Declares the host IP address without protocol
    


#### Create Navigation:  
Nagivation items are stored in database and can be found in the table navigations under the django schema.
An example navigation is created upon installation and can be used as reference.

#### Create Content:  
The content for the navigation items is stored in a mediawiki (`http://IP/mediawiki`) and is rendered transparently into the django interface. After creating the navigation items in the database, you can add the corresponding mediawiki pages with the same name as the database item. Examples are present in the database and mediawiki.




## License

Licensed under the [MIT License](https://en.wikipedia.org/wiki/MIT_License).
