"""
This file contains all urls that need to be maintained for the search application


Author: Michel Peltriaux
Organization: Spatial data infrastructure Rheinland-Pfalz, Germany
Contact: michel.peltriaux@vermkv.rlp.de
Created on: 22.01.19
"""
from Geoportal.settings import HOSTIP, HTTP_OR_SSL, HOSTNAME

URL_BASE = HTTP_OR_SSL + HOSTIP + "/mapbender/"
URL_BASE_LOCALHOST = HTTP_OR_SSL + "127.0.0.1/mapbender/"
URL_ABSOLUTE = HTTP_OR_SSL + HOSTNAME + "/mapbender/"

URL_AUTO_COMPLETE_SUFFIX = "geoportal/mod_getCatalogueKeywordSuggestion.php"
URL_SEARCH_PRIMARY_SUFFIX = "php/mod_callMetadata.php"
URL_SEARCH_DE_SUFFIX = "php/mod_callCswMetadata.php"
URL_SEARCH_INFO = HTTP_OR_SSL + HOSTIP + "/mediawiki/api.php"

URL_GET_ORGANIZATIONS = "php/mod_showOrganizationList.php"
URL_GET_TOPICS = "php/tagCloud.php"

URL_RESOLVE_COUPLED_RESOURCES = "php/mod_getCoupledResourcesForDataset.php"

URL_GLM_MOD = "php/mod_sessionWrapper.php"

URL_SPATIAL_BASE = "http://geoportal.rlp.de/mapbender/"
URL_SPATIAL_SEARCH_SUFFIX = "geoportal/gaz_geom_mobile.php"

URL_INSPIRE_DOC = "https://inspire.ec.europa.eu/Themes/"

