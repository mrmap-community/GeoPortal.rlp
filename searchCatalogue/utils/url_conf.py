"""
This file contains all urls that need to be maintained for the search application


Author: Michel Peltriaux
Organization: Spatial data infrastructure Rheinland-Pfalz, Germany
Contact: michel.peltriaux@vermkv.rlp.de
Created on: 22.01.19
"""
from Geoportal.settings import CONTAINER, DEBUG, HOSTNAME, HTTP_OR_SSL

# URL_BASE_LOCALHOST is used for internal calls
# URL_BASE is used for everything which can be seen from outside
URL_BASE_LOCALHOST = HTTP_OR_SSL + \
    "172.17.0.1:8001/mapbender/" if CONTAINER else HTTP_OR_SSL + "127.0.0.1/mapbender/"
URL_BASE_PRIMARY_SEARCH = HTTP_OR_SSL + "geoportal.rlp.de/mapbender/"

URL_BASE = URL_BASE_LOCALHOST if DEBUG else HTTP_OR_SSL + HOSTNAME + "/mapbender/"

URL_AUTO_COMPLETE_SUFFIX = "geoportal/mod_getCatalogueKeywordSuggestion.php"
URL_SEARCH_PRIMARY_SUFFIX = "php/mod_callMetadata.php"
URL_SEARCH_DE_SUFFIX = "php/mod_callCswMetadata.php"
# URL_SEARCH_DE_SUFFIX = "search"
URL_SEARCH_INFO = HTTP_OR_SSL + '127.0.0.1' + "/mediawiki/api.php"

URL_GET_ORGANIZATIONS = "php/mod_showOrganizationList.php"
URL_GET_TOPICS = "php/tagCloud.php"

URL_RESOLVE_COUPLED_RESOURCES = "php/mod_getCoupledResourcesForDataset.php"

URL_SESSION_WRAPPER = "php/mod_sessionWrapper.php"

URL_BASE_GEOPORTAL = "http://geoportal.rlp.de/mapbender/"
URL_LOCATION_SEARCH_SUFFIX = "geoportal/gaz_geom_mobile.php"

URL_INSPIRE_DOC = "https://inspire.ec.europa.eu/Themes/"
