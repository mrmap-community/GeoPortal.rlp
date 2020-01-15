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

### Example Websites

https://www.geoportal.rlp.de/

### Installation

Fast install on local system (only for testing, default passwords!):
```shell
wget --no-check-certificate https://git.osgeo.org/gitea/armin11/GeoPortal.rlp/raw/branch/master/geoportal_maintenance.sh
chmod +x geoportal_maintenance.sh
./geoportal_maintenance.sh --mode=install --ipaddress=127.0.0.1 [options]
```


Requirements:

* Debian 9 with working internet connection.   

```shell
./geoportal_maintenance.sh --help
```

This script is for installing and maintaining your geoportal solution
You can choose from the following options:

        --ip=ipaddress             			                | Default "127.0.0.1"
        --hostname=hostname              			        | Default "127.0.0.1"
        --proxy=Proxy IP     	 			                | Default "None" ; Syntax --proxy=1.2.3.4:5555
        --proxyuser=username                      	        | Default "" ; Password will be prompted
        --mapbenderdbuser=User for Database access	        | Default "mapbenderdbuser"
    	--mapbenderdbpw=Password for database access    	| Default "mapbenderdbpassword"
    	--phppgadmin_user=User for PGAdmin web access		| Default "postgresadmin"
    	--phppgadmin_pw=Password for PGAdmin web access   	| Default "postgresadmin_password"
	    --install_dir=Directory for installation		    | Default "/data/"
        --webadmin_email=email address for send mail        | Default "test@test.de"
    	--mysqlpw=database password for MySQL		     	| Default "root"
    	--mode=what you want to do				            | Default "none" [install,update,delete,backup]



Description:  

	mandatory:  
	--ip                    -> The address of your external interface.
	--hostname              -> FQDN of your Server www.example.rlp.de
	--mode                  -> What you want to do. Choices are install | update | delete | backup.

	optional:  
	--proxy                 -> IP Address:Port of your local proxy server. Will be inserted in: apt.conf, mapbender.conf, subversion.conf
                            Special case: --proxyip=custom lets you choose a different proxy for each service above
	--proxyuser             -> Username for proxy auth, Password will be prompted
	--mapbenderdbuser       -> User for accessing the mapbender database. Will be created on install.
	--mapbenderdbpw         -> Password for mapbenderdbuser.
	--phppgadmin_user       -> User for the PHPPgAdmin Webinterface.
	--phppgadmin_pw         -> Password for phppgadmin_user.
	--mysqlpw               -> Passwort for the MySql root user.
    --webadmin_email        -> The email address e.g. registration mails are send from

Examples:  

Install:  
```shell
geoportal_maintenance.sh --ip=192.168.0.2 --proxy=192.168.0.254:3128 --mapbenderdbuser=MyPostgresDBUser --mapbenderdbpw=MyPostgresDBPassword --phppgadmin_user=MyPHPPgAdminUser ---phppgadmin_pw=MyPHPPgAdminPassword --mysqlpw=MyMySQLRootPW --mode=install --install_dir=/opt/ --webadmin_email=geoportal@yourdomain.tld
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
mysql        -> root:root  
wiki         -> root:rootroot  
postgres     -> postgres:  
phppgadmin   -> postgresadmin:postgresadmin_password  
django-admin -> root:root  

### Things that need to be done after installation:  

If everything goes fine, you dont have to do anything else on the command line than executing the script.
Upon successful installation the system should be ready to use, to verify that, point your browser at the IP or Hostname of your GeoPortal instance. If you dont see the Geoportal design, something might have gone wrong, in this case write an issue or go to
the debugging section.

The navigation is located on the left side and its items come from the database. To change the content in the navigation bar, you go to http://IP-ADDRESS/admin, which is the django admin interface. Now login with the default credentials root:root and change them. To do so,  you click on Users and select the root user as its the only entry. The Password field refers to a form where you can change your password. 

Now you can alter the content of the navigation bar to your needs, it can be found in the table Navigations. After opening the table you should arrage the listing by position ascending, as this is the order they will be seen on the web interface. There are parent and child items. Parent items have a empty URL and parent field.

Fields of the navigation table:

* POSITION        -> Order in the navigation bar, use 
* NAME            -> Name that will be displayed in the navigation bar.
* PAGE_IDENTIFIER -> internal string, use NAME without upper chars and spaces
* URL             -> Only for child items, dont change the "wiki" entry as it points to your mediawiki
                  * use /article/NAME to create an item with NAME that refers to the mediawiki page, if you create the corresponding page  in the mediawiki itself, it will be rendered transparently into the webinterface. One example is the "Meldungen" article.
* ICON_NAME       -> Only for parent items, the icon you see in the navigation bar
* PARENT          -> Only needed for child items, see examples

At this point the mapbender database contains three users which are: 
root:root; -> superdamin, can do everything
bereichsadmin1:bereichsadmin1; -> subadmin, can register&publish services
guest:AUTOMATIC_SESSION -> guest session, mostly for just viewing 

When your content and navigation is ready you can go ahead and start registering services.
To do so, login as root or bereichsadmin1 and again, change password first. After successful login and password change you can click on the little grid sign on the right to open the default gui, which is the mapviewer. Configuration of Mapbender and registration of services can be found in other guis. To change the gui, click on the sign with the grid and the arrow pointing upwards. Here you can select Administration_DE for service management (WMS, WFS, WMC, Metadata) and PortalAdmin_DE for user, group, role, category management and some maintenance functions. Documentation on Mapbender is currently only available in German and is located on the wiki https://www.geoportal.rlp.de/mediawiki/index.php/Hilfe.



#### Debugging - Places to look

* /var/log/apache2/error.log -> Apache and Django errors
* /data(default)/mapbender/log/mb_err_log_* -> Mapbender specific errors


##### Important files and variables

* /data(default)/mapbender/conf/mapbender.conf  

```shell
# HOSTNAME WHITELIST
define("HOSTNAME_WHITELIST","xxx"); # Where `xxx` is your servers IP address.

# HOSTs not for Proxy (curl)
define("NOT_PROXY_HOSTS", "localhost,127.0.0.1,$HOSTNAME");

# database information
define("DBSERVER", "localhost");
define("PORT", "5432");
define("DB", "mapbender");
define("OWNER", "mapbenderdbuser");
define("PW", "mapbenderdbpassword");

```

* /data(default)/GeoPortal.rlp/Geoportal/settings.py

```shell

HOSTNAME = "192.168.56.111"
HOSTIP = "192.168.56.111"
HTTP_OR_SSL = "http://"

# Database
DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.postgresql_psycopg2',
        'OPTIONS' : {
                    'options': '-c search_path=django,mapbender,public'
                    },
        'NAME':'mapbender',
        'USER':'mapbenderdbuser',
        'PASSWORD':'mapbenderdbpassword',
        'HOST':'127.0.0.1',
        'PORT':''
    }
}

# email setting
EMAIL_BACKEND = 'django.core.mail.backends.smtp.EmailBackend'
EMAIL_USE_TLS = False / True
EMAIL_HOST = 'IP OF YOUR SMTP SERVER'
EMAIL_HOST_USER = 'kontakt@geoportal.de'
DEFAULT_FROM_EMAIL = EMAIL_HOST_USER
EMAIL_PORT = 25 / 456 / 587
ROOT_EMAIL_ADDRESS = "root@debian"

```

# Customization
Always remember: always change the images, scripts or style sheets which are located in the subfolders of the project
such as `useroperations`. All of these so called static files will be automatically copied into `static` folder when the command
```commandline
python manage.py collectstatic
```
is called.
## Colours
Surely you are not interested in using the default colours or images. 
All colours can be found in `useroperations/static/useroperations/css/color_schema.css`
You will find e.g. `primary_color` and `secondary_color` as well as their related colors `xyz_hover`, `xyz_light` and so on.
Changing these colours will affect the whole web app since all usages of colours are referenced onto these
settings. For the best user expierence you may take a look on https://www.colorhexa.com/ which provides rich
information about harmonizing and related or complementary colours of your choice. 

## Images
All organization related images can be found in `useroperations/static/useroperations/images/` and have the prefix
`logo-`. Simply changing these (while keeping the same name) will directly change the appearance on the pages.
All other images that have the prefix `icn_` are used as icons and can be changed the same way.



## License

Licensed under the [MIT License](https://en.wikipedia.org/wiki/MIT_License).
