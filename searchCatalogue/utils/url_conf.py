"""
This file contains all urls that need to be maintained for the search application


Author: Michel Peltriaux
Organization: Spatial data infrastructure Rheinland-Pfalz, Germany
Contact: michel.peltriaux@vermkv.rlp.de
Created on: 22.01.19
"""
from Geoportal.settings import HOSTIP

URL_BASE = "http://" + HOSTIP + "/mapbender/"
URL_AUTO_COMPLETE_SUFFIX = "geoportal/mod_getCatalogueKeywordSuggestion.php"
URL_SEARCH_RLP_SUFFIX = "php/mod_callMetadata.php"
URL_SEARCH_DE_SUFFIX = "php/mod_callCswMetadata.php"
URL_SEARCH_INFO = "http://" + HOSTIP + "/mediawiki/api.php"

URL_SPATIAL_BASE = "http://geoportal.rlp.de/mapbender/"
URL_SPATIAL_SEARCH_SUFFIX = "geoportal/gaz_geom_mobile.php"
