"""
Constants for the search catalogue app
"""
from Geoportal.settings import HOSTNAME, HTTP_OR_SSL, PROJECT_DIR

EXTENT_SERVICE_URL = HTTP_OR_SSL + HOSTNAME + "/cgi-bin/mapserv?map=" + PROJECT_DIR + "/mapbender/tools/wms_extent/extents.map&"
EXTENT_SERVICE_LAYER = "demis,ows_layer_target,extent,metadata_polygon"
EXTENT_SERVICE_BBOX = "6.05,48.9,8.6,50.96"

PROXIES = {
    "http": "http://10.240.20.164:8080/",
    "https": "http://10.240.20.164:8080/",
}

iso3166_folder = "iso3166States/"
__DE_RP = iso3166_folder + "wappen_DE-RP.png"
__DE_SN = iso3166_folder + "wappen_DE-SN.png"
__DE_BB = iso3166_folder + "wappen_DE-BB.png"
__DE_BE = iso3166_folder + "wappen_DE-BE.png"
__DE_BW = iso3166_folder + "wappen_DE-BW.png"
__DE_HB = iso3166_folder + "wappen_DE-HB.png"
__DE_HE = iso3166_folder + "wappen_DE-HE.png"
__DE_HH = iso3166_folder + "wappen_DE-HH.png"
__DE_NI = iso3166_folder + "wappen_DE-NI.png"
__DE_NW = iso3166_folder + "wappen_DE-NW.png"
__DE_SH = iso3166_folder + "wappen_DE-SH.png"
__DE_SL = iso3166_folder + "wappen_DE-SL.png"
__DE_TH = iso3166_folder + "wappen_DE-TH.png"
__DE_DE = iso3166_folder + "wappen_DE.png"


ISO3166_FILES = {
    "RLP": __DE_RP,
    "DE-RP": __DE_RP,
    "Rheinland Pfalz": __DE_RP,
    "Rheinland-Pfalz": __DE_RP,

    "Sachsen": __DE_SN,
    "DE-SN": __DE_SN,
    "SN": __DE_SN,

    "Hessen": __DE_HE,
    "DE-HE": __DE_HE,
    "HE": __DE_HE,

    "Brandenburg": __DE_BB,
    "DE-BB": __DE_BB,
    "BB": __DE_BB,

    "Berlin": __DE_BE,
    "DE-BE": __DE_BE,
    "BE": __DE_BE,

    "Baden-Württemberg": __DE_BW,
    "Baden-Wuerttemberg": __DE_BW,
    "DE-BW": __DE_BW,
    "BW": __DE_BW,

    "Bremen": __DE_HB,
    "DE-HB": __DE_HB,
    "HB": __DE_HB,

    "Hamburg": __DE_HH,
    "DE-HH": __DE_HH,
    "HH": __DE_HH,

    "Niedersachsen": __DE_NI,
    "DE-NI": __DE_NI,
    "NI": __DE_NI,

    "Nordrhein-Westfalen": __DE_NW,
    "DE-NW": __DE_NW,
    "NW": __DE_NW,

    "Schleswig-Holstein": __DE_SH,
    "DE-SH": __DE_SH,
    "SH": __DE_SH,

    "Sachsen-Anhalt": __DE_SL,
    "DE-SL": __DE_SL,
    "SL": __DE_SL,

    "Thüringen": __DE_TH,
    "Thueringen": __DE_TH,
    "DE-TH": __DE_TH,
    "TH": __DE_TH,

    "Bundesrepublik Deutschland": __DE_DE,
    "DE-DE": __DE_DE,
    "DE": __DE_DE,
}
