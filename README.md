# Voraussetzungen

## Mapbender Debian 9
Damit dieses Projekt für die Entwicklung verwendet werden kann, muss eine **Debian9** Installation in einer virtuellen Umgebung (z.B. Virtualbox) laufen und Mapbender installiert haben.
Das **Installationsskript für Mapbender** findet sich (https://vmlxgit.lvermgeo.vermkv/holla/install-skripte-geoportal-alt)[hier].

Dies ist notwendig, da das Login- und Registrierungsmodul in `UserOperations/` an die Mapbender Datenbank anknüpft.
Entsprechend der `UserOperations/settings.py` Datenbankeinstellungen muss auch die Installation eingerichtet werden:

```python
# Database
# https://docs.djangoproject.com/en/2.1/ref/settings/#databases

DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.postgresql_psycopg2',
        'NAME': 'mapbender',
        'USER':'postgres',
        'PASSWORD':'postgres',
        'HOST' : '192.168.56.222',
        'PORT' : ''
    }
}
```
## Statische IP
Wie in den Einstellungen zu sehen ist, muss eine feste IP Adresse für die virtuelle Mapbender Installation vergeben werden. 
Hierzu muss in der virtuellen Maschine in `/etc/network/interfaces` folgendes hinzugefügt werden:

```bash
auto [INTERFACE]
iface [INTERFACE] inet static
        address 192.168.56.222
        netmask 255.255.255.0
        gateway 192.168.56.1
```
**[INTERFACE]** ist die Schnittstelle, über die mit ssh zugegriffen werden kann.

## Module
Die notwendigen Module Dritter können über den folgenden Befehl auf Projektroot Ebene installiert werden.
Bitte ggf. proxies einrichten! 

`pip install -r requirements.txt`

## Installation

Zur Installation stehen Skripte unter https://vmlxgit.lvermgeo.vermkv/holla/install-skripte-geoportal-alt bereit

1. Internetverbindung sicherstellen und proxy in install_geoportal_mapbender.sh eintragen
2. bash ./install_geoportal_mapbender.sh
3. IP-Adresse und MYSQL-Root Passwort in install_geoportal_django.sh
4. bash ./install_geoportal_django.sh

Möglicherweise schlagen die Migrationen fehl, in diesem fall folgende Kommandos in der virtualenv absetzen
1. python manage.py migrate --fake sessions zero
2. python manage.py migrate --fake-initial

Zum Einspielen der initialen Navigationselemente folgenden Befehl ausführen:
1. python manage.py loaddata useroperations/fixtures/navigation.json