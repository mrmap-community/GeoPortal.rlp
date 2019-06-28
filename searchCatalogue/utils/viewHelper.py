
"""
This class provides all functions that are needed in view.py.
For beautiful code-reasons we put these functions in here.


Author: Michel Peltriaux
Organization: Spatial data infrastructure Rheinland-Pfalz, Germany
Contact: michel.peltriaux@vermkv.rlp.de
Created on: 22.01.19

"""
import threading
import urllib
import hashlib
from collections import OrderedDict
from urllib.parse import urlparse
from django.utils.translation import gettext as _

import math

import requests
from requests.packages.urllib3.exceptions import InsecureRequestWarning

from Geoportal.helper import execute_threads, write_gml_to_session, sha256

requests.packages.urllib3.disable_warnings(InsecureRequestWarning)

from Geoportal import helper
from Geoportal.settings import INTERNAL_PAGES_CATEGORY, HOSTNAME, HOSTIP, HTTP_OR_SSL, INTERNAL_SSL, PRIMARY_SRC_IMG, \
    DE_SRC_IMG, EU_SRC_IMG
from searchCatalogue.settings import *


####    SINGLE HELPER FUNCTIONS
from searchCatalogue.utils.searcher import Searcher, URL_BASE


def get_source_catalogues(external_call: bool=False):
    """ Returns a dict which holds all valid catalogue sources

    Args:
        external_call (bool): States whether the call is internal or external
    Returns:
         sources (OrderedDict): Contains all catalogues as key-value pairs
    """
    sources = OrderedDict()
    sources["primary"] = {
        "key": _("Own catalogue"),
        "img": PRIMARY_SRC_IMG,
    }
    if not external_call:
        sources["de"] = {
            "key": _("Germany"),
            "img": DE_SRC_IMG,
        }
        sources["eu"] = {
            "key": _("Europe"),
            "img": EU_SRC_IMG,
        }
        sources["info"] = {
            "key": _("Info"),
            "title": _("Info pages"),
        }
    return sources

def parse_extended_params(params: dict):
    """ Convert one long GET url parameter into a well formed dict.

    Represents the incoming params, which are expected to be in a decoded url pattern.

    Args:
        params (dict): Contains the extended search parameters as one long GET url parameter
    Returns:
        dict: Contains the splitted extended search parameters
    """
    extended_search_params = {}
    extended_search_params_raw = urllib.parse.unquote(params["extended"]).split("&")
    for entry in extended_search_params_raw:
        entry_arr = entry.split("=")
        if len(entry_arr) == 2:
            # this is a real key-value pair
            extended_search_params[entry_arr[0]] = entry_arr[1]

    # remove orderBy value in this dict, since this is a default value and would override the user's choice value!
    if extended_search_params.get("orderBy", None) is not None:
        del extended_search_params["orderBy"]
    return extended_search_params


def generate_page_list(max_page, current_page, max_displayed_pages):
    """ Generates a list of elements for the pagination

    Content of returned list may look like this:
    [1, '...', 50, 51, 52, 53, 54, '...', 3210]
    or
    [1, 2, 3]

    Args:
        max_page: The maximum page available for the search results
        current_page: The current page number
        max_displayed_pages: How many pages before and after the current shall be displayed until '...' is showed
    Returns:
        list: Contains all page elements
    """
    final_list = []
    if max_page >= 0:
        # add the first page, it should always be there if we have at least one page
        final_list.append(1)
        available_pages = range(current_page - max_displayed_pages, current_page + max_displayed_pages)
        if 1 not in available_pages:
            final_list.append("...")
        for page in available_pages:
            # iterate over the 'pageregion' around our current page. Add the page numbers if they are logically valid
            if page > 0 and page <= max_page and page not in final_list:
                final_list.append(page)
        if max_page > final_list[len(final_list) - 1] and max_page not in final_list:
            # if we didn't reach the max_page yet in our 'pageregion', we need to add it to the list
            if max_page > final_list[len(final_list) - 1] + 1:
                final_list.append("...")
            final_list.append(max_page)
    return final_list

def calculate_pages_to_render_de(search_results, requested_page: int, requested_page_res: str):
    """ Returns a dict of page configurations for all requested resource types of the german catalogue

    Page configuration means:
    * current_page = which page is requested by the user
    * range_page = list of integers from 1 to max_page (for a loop in the view template necessary)
    * max_page = the maximum number of displayed pages before and after the current page
    * display_pages_before_current = which pages shall be displayed before the currently selected
    * display_pages_after_current = which pages shall be displayed after the currently selected

    Args:
        search_results: The dict of search results, which is an output of searcher.py
        requested_page: The requested_page means which page has been fetched
        requested_page_res: This stands for the resource which gets this new page
    Returns:
        dict: Contains every resource encoded with it's current selected page
    """
    pages = {}
    for resource_key, resource_val in search_results.items():
        md = resource_val[resource_key]["md"]
        total_number_of_results = md["nresults"]
        results_per_page = md["rpp"]
        max_page = int(math.ceil(int(total_number_of_results) / int(results_per_page)))
        max_displayed_pages = 3
        _page = -1
        if resource_key == requested_page_res:
            _page = requested_page
        else:
            _page = int(md["p"])

        result_pages = {
            "current_page": _page,
            "page_list": generate_page_list(max_page=max_page, current_page=_page, max_displayed_pages=max_displayed_pages),
            "max_page": max_page,
        }
        if resource_key != requested_page_res:
            result_pages["current_page"] = 1
        pages[resource_key] = result_pages
    return pages

def calculate_pages_to_render(search_results, requested_page: int, requested_page_res: str):
    """ Returns a dict of page configurations for all requested resource types.

    Page configuration means:
    * current_page = which page is requested by the user
    * range_page = list of integers from 1 to max_page (for a loop in the view template necessary)
    * max_page = the maximum number of displayed pages before and after the current page
    * display_pages_before_current = which pages shall be displayed before the currently selected
    * display_pages_after_current = which pages shall be displayed after the currently selected

    Args:
        search_results: The dict of search results, which is an output of searcher.py
        requested_page: The requested_page means which page has been fetched
        requested_page_res: This stands for the resource which gets this new page
    Returns:
        dict: Contains every resource encoded with it's current selected page
    """
    pages = {}
    for result_key, result_val in search_results.items():
        key = result_key
        md = result_val[key][key]["md"]
        total_number_of_results = md["nresults"]
        if total_number_of_results is None:
            total_number_of_results = 0
            md["nresults"] = 0
        results_per_page = md["rpp"]
        max_page = int(math.ceil(int(total_number_of_results) / int(results_per_page)))
        max_displayed_pages = 3
        _page = -1
        if result_key == requested_page_res:
            _page = requested_page
        else:
            _page = int(md["p"])

        result_pages = {
            "current_page": _page,
            "page_list": generate_page_list(max_page=max_page, current_page=_page, max_displayed_pages=max_displayed_pages),
            "max_page": max_page,
        }
        if key != requested_page_res:
            result_pages["current_page"] = 1
        pages[result_key] = result_pages
    return pages

def set_children_data_wfs(search_results):
    """ Set the important top-level attributes to all children for all wfs results.

    Otherwise some top-level information might be missing on lower level sublayers.
    This way it is much easier to render the corresponding organization logo for all entries in the template.

    Args:
        search_results: All search results - output of searcher.py
    Returns:
        Returns the modified search_results
    """
    if search_results.get("wfs", None) is None:
        return search_results
    for srv in search_results["wfs"]["wfs"]["wfs"]["srv"]:
        if srv is not dict:
            return search_results   # ToDo: Change this workaround as soon as the bug related to this is removed
        logo_url = srv["logoUrl"]
        resp_org = srv["respOrg"]
        data_date = srv["date"]
        symb_link = srv["symbolLink"]
        # set this attribute for all children
        ftypes = srv["ftype"]
        for ftype in ftypes:
            ftype["logoUrl"] = logo_url
            ftype["respOrg"] = resp_org
            ftype["date"] = data_date
            ftype["symbolLink"] = symb_link
            if ftype.get("modul", None) is None:
                continue
            for _module in ftype["modul"]:
                _module["logoUrl"] = logo_url
                _module["respOrg"] = resp_org
                _module["date"] = data_date
                _module["symbolLink"] = symb_link

    return search_results

def prepare_spatial_data(spatial_data):
    """ Organizes keywords in spatial_data

    The results of the spatial data search does not differentiate between a search word that encodes a location,
    like a city name, and a "thing" the user is looking for, like "environment".
    This function analyzes the output of the spatial search and transforms it into a dict
    where the location ('in') and the "thing" ('looking_for') can be differentiated.

    Args:
        spatial_data: The results of the search results from the spatial search
    Returns:
        dict: Contains the same data but sorted by "is it a location or a thing we are looking for in the location?"
    """
    looking_for = []
    _in = []
    for data in spatial_data:
        looking_for.append(data.get("keyword"))
        if data.get("totalResultsCount", 0) == 0:
            continue
        _in.append(data)

    result = {
        "looking_for": looking_for,
        "in": _in,
    }
    return result

def translate_filter_titles(filters):
    """ Translates filter titles according to the selected language

    Args:
        filters (dict): Contains the filters that shall be translated
    Returns:
        Returns the modified filters
    """
    if filters.get("searchText", {}).get("title", "") != "":
        filters["searchText"]["title"] = _("Search terms(e):")
    if filters.get("searchBbox", {}).get("title", "") != "":
        filters["searchBbox"]["title"] = _("Spatial Restriction:")
    if filters.get("maxResults", {}).get("header", "") != "":
        filters["maxResults"]["header"] = _("Hits per Page:")
    if filters.get("orderFilter", {}).get("header", "") != "":
        filters["orderFilter"]["header"] = _("Sort by:")
    return filters


def gen_resource_arr(search_results: dict):
    """ Generates an array which contains only the names of the current selected resources

    Args:
        search_results (dict): The output of searcher.py
    Returns:
        list: All resource keys that show up in the search_results dict.
    """
    resources = []
    # only wms and dataset might have downloadable content
    for search_results_key, search_results_val in search_results.items():
        resources.append(search_results_key)

    return resources

####    DOWNLOAD OPTIONS
def __group_download_options(download_options: dict):
    """
    Args:
        download_options (dict): Contains all possible download options
    Returns:
        dict: Contains the amount of download options, the grouped title and uuid for each
    """
    grouped_downloads = {}
    for download_options_key, download_options_val in download_options.items():
        grouped_downloads["options"] = {
            "title": download_options_val["title"],
            "uuid": download_options_val["uuid"],
            "resources": [],
        }
        grouped_downloads["num"] = len(download_options_val["option"])

    for download_options_key, download_options_val in download_options.items():
        grouped_downloads["options"]["resources"].append(download_options_val["option"][0].get("resourceId", None))

    return grouped_downloads

def group_all_download_options(search_results):
    """ Sets all download Options in search_results to a grouped format, including the amount of available options

    Args:
        search_results: The output of searcher.py
    Returns:
        list: Contains the modified search_results
    """
    resources = []
    # only wms and dataset might have downloadable content
    if search_results.get('wms', None) is not None:
        resources.append('wms')
    if search_results.get('dataset', None) is not None:
        resources.append('dataset')

    for resource in resources:
        srv = search_results[resource][resource][resource]["srv"]
        for result in srv:
            res_layer = result.get("layer", None)
            if res_layer is None:
                res_layer = result["coupledResources"]["layer"][0]["srv"]["layer"]
                if res_layer is None:
                    continue
            for layer in res_layer:
                if layer["downloadOptions"] is not None:
                    layer["downloadOptions"] = __group_download_options(layer["downloadOptions"])
    return search_results

####    INSPIRE URL
def __type_inspire_url(uuid, option:dict):
    """

    Args:
        uuid: The uuid of the object
        option (dict): The  option dict
    Returns:
        string: The url according to the option type of the resource
    """
    url = None
    try:
        _type = option["type"]
    except TypeError:
        # there could be option variables which are no dicts but only contain a digit -> no idea what this should be!
        return url
    # base_url = LOCAL_MACHINE + "/mapbender/plugins/mb_downloadFeedClient.php?url="
    base_url = HTTP_OR_SSL + HOSTNAME + "/mapbender/plugins/mb_downloadFeedClient.php?url="
    if _type == "wmslayergetmap":
	# url = base_url + urllib.parse.quote_plus(LOCAL_MACHINE + "/mapbender/php/mod_inspireDownloadFeed.php?id=" + uuid + "&type=SERVICE&generateFrom=wmslayer&layerid=" + option["resourceId"])
        url = base_url + urllib.parse.quote_plus(HTTP_OR_SSL + HOSTNAME + "/mapbender/php/mod_inspireDownloadFeed.php?id=" + uuid + "&type=SERVICE&generateFrom=wmslayer&layerid=" + option["resourceId"])
    if _type == "wmslayerdataurl":
        url = base_url + urllib.parse.quote_plus(HTTP_OR_SSL + HOSTNAME + "/mapbender/php/mod_inspireDownloadFeed.php?id=" + uuid + "&type=SERVICE&generateFrom=wmslayer&layerid=" + option["resourceId"])
    if _type == "wfsrequest":
        url = base_url + urllib.parse.quote_plus(HTTP_OR_SSL + HOSTNAME + "/mapbender/php/mod_inspireDownloadFeed.php?id=" + uuid + "&type=SERVICE&generateFrom=wfs&wfsid=" + option["serviceId"])
    if _type == "downloadlink":
        url = base_url + urllib.parse.quote_plus(HTTP_OR_SSL + HOSTNAME + "/mapbender/php/mod_inspireDownloadFeed.php?id=" + uuid + "&type=SERVICE&generateFrom=metadata")
    return url


def gen_inspire_url(search_results):
    """ Generates the inspire download urls and stores them in search_results

    Args:
        search_results: The output of searcher.py
    Returns:
        Returns the modified search_results
    """
    resources = gen_resource_arr(search_results)

    for resource in resources:
        results = search_results[resource][resource][resource]["srv"]
        thread_list = []
        for result in results:
            if resource == "dataset":
                try:
                    feeds = result["coupledResources"]["inspireAtomFeeds"]
                except KeyError:
                    # this is quicker than checking if the key exists
                    continue
                if isinstance(feeds, dict):
                    for feed_key, feed_val in feeds.items():
                        feed_val["download_url"] = __type_inspire_url(result["uuid"], feed_val)
                else:
                    for feed in feeds:
                        feed["download_url"] = __type_inspire_url(result["uuid"], feed)
            elif resource == "wms":
                layers = result.get("layer", None)
                if layers is None:
                    continue
                for layer in layers:
                    if layer.get("downloadOptions", None) is None:
                        continue
                    for download_options_key, download_options_val in layer["downloadOptions"].items():
                        try:
                            opt = download_options_val["option"][0]
                        except KeyError:
                            opt = None
                        layer["download_url"] = __type_inspire_url(download_options_val["uuid"], opt)
        execute_threads(thread_list)
    return search_results

####    EXTENT GRAPHICS
def __gen_single_extent_graphic_url(result: dict):
    """
    Args:
        result: A single result from the search_results
    Results:
        string: The extent graphic url for a single search result
    """
    url = ""
    try:
        bbox = list(map(float, result.get("bbox").split(',')))
    except AttributeError:
        return url
    area_bbox = list(map(float, EXTENT_SERVICE_BBOX.split(',')))

    d_x = float(bbox[2] - bbox[0]) / 2
    d_y = float(bbox[3] - bbox[1]) / 2

    new_minx = bbox[0] - d_x
    new_maxx = bbox[2] + d_x
    new_miny = bbox[1] - d_y
    new_maxy = bbox[3] + d_y

    if new_minx < -180:
        area_bbox[0] = -180
    else:
        area_bbox[0] = new_minx
    if new_maxx > 180:
        area_bbox[2] = 180
    else:
        area_bbox[2] = new_maxx
    if new_miny < -90:
        area_bbox[1] = -90
    else:
        area_bbox[1] = new_miny
    if new_maxy > 90:
        area_bbox[3] = 90
    else:
        area_bbox[3] = new_maxy

    area_bbox = list(map(str, area_bbox))
    bbox = list(map(str, bbox))
    url = EXTENT_SERVICE_URL + "VERSION=1.1.1&REQUEST=GetMap&SERVICE=WMS&LAYERS=" + EXTENT_SERVICE_LAYER + "&STYLES=&SRS=EPSG:4326&BBOX=" + area_bbox[0] + "," + area_bbox[1] + "," + area_bbox[2] + "," + area_bbox[3] + "&WIDTH=120&HEIGHT=120&FORMAT=image/png&BGCOLOR=0xffffff&TRANSPARENT=TRUE&EXCEPTIONS=application/vnd.ogc.se_inimage&minx=" + bbox[0] + "&miny=" + bbox[1] + "&maxx=" + bbox[2] + "&maxy=" + bbox[3]
    return url

def __handle_extent_graphics_for_layers_recursive(child_layers: dict):
    """ Walk recursively through all sublayers and generates the urls for extent graphics

    Args:
        child_layers: The sublayers of an element
    Returns:
        list: Contains all sublayers with extent graphic urls
    """
    a = []
    for child_layer in child_layers:
        if child_layer.get("srv", None) is not None:
            # this is a dataset child, we need to handle this differently
            child_layer = child_layer["srv"]
        child_layer["extent_url"] = __gen_single_extent_graphic_url(child_layer)

        if child_layer.get("layer", None) is None:
            # no more childdren in this child
            return child_layer
        else:
            a.append(__handle_extent_graphics_for_layers_recursive(child_layer.get("layer")))
    return a

def gen_extent_graphic_url(search_results):
    """ Generates the url which leads to the web generated map graphics for each search result

    Args:
        search_results: The output of searcher.py
    Returns:
        Returns the modified search_results
    """
    resources = gen_resource_arr(search_results)
    for resource in resources:
        # iterate over all resources
        srv_s = search_results[resource][resource][resource]["srv"]
        for srv in srv_s:
            if resource == "dataset":
                srv["bbox"] = srv.get("bbox", EXTENT_SERVICE_BBOX)[0]
                # generate extent url for top level parent
                srv["extent_url"] = __gen_single_extent_graphic_url(srv)
                coup_res = srv.get("coupledResources", None)
                if coup_res is None:
                    continue
                layers = srv["coupledResources"].get("layer", [])
            elif resource == "wms":
                # generate extent url for top level parent
                srv["extent_url"] = __gen_single_extent_graphic_url(srv)
                if srv.get("layer", None) is None:
                    continue
                layers = srv["layer"]
            elif resource == "wmc":
                srv["bbox"] = srv.get("bbox", EXTENT_SERVICE_BBOX)[0]
                srv["extent_url"] = __gen_single_extent_graphic_url(srv)
                layers = None
            else:  # == wfs
                srv["extent_url"] = __gen_single_extent_graphic_url(srv)
                layers = srv.get("ftype", None)

            if layers is not None:
                layers = __handle_extent_graphics_for_layers_recursive(layers)
    return search_results


####    DISCLAIMER INFOS
def __dataset_single_layer_disclaimer(layer, language):
    """ Single thread handles a single dataset layer in here

    Args:
        layer: A single sublayer
        language: Which language shall be used in the url
    Returns:
        nothing
    """
    if layer.get("srv", None) is not None:
        service_id = layer.get("srv", {}).get("id", None)
        if service_id is None:
            return
        #url = HTTP_OR_SSL + HOSTIP + "/mapbender/php/mod_getServiceDisclaimer.php?type=" + "wms" + "&id=" + str(
        url = URL_BASE + "php/mod_getServiceDisclaimer.php?type=" + "wms" + "&id=" + str(
            service_id) + "&languageCode=" + language + "&withHeader=true"
        layer["srv"]["disclaimer_html"] = requests.get(url, verify=INTERNAL_SSL).content.decode()


def __dataset_srv_disclaimer(srv, language):
    """ Handles a dataset srv set, creates threads to handle each layer of the set parallel, sets the disclaimer info

    Args:
        srv: The served results from the resource type 'dataset'
        language: The language that shall be used
    Returns:
        nothing
    """
    thread_list = []
    if srv.get("coupledResources", None) is not None:
        for layer in srv["coupledResources"].get("layer", []):
            thread_list.append(threading.Thread(target=__dataset_single_layer_disclaimer, args=(layer, language)))
    helper.execute_threads(thread_list)


def __wms_srv_disclaimer(layer, language, resource):
    """ Handles a wms srv set and sets the disclaimer info

    Args:
        layer: The current sublayer working on
        language: The language that shall be used
        resource: The resource that shall be used in the url
    Returns:
        nothing
    """
    service_id = layer.get("id", None)
    if service_id is None:
        return
    url = URL_BASE + "php/mod_getServiceDisclaimer.php?type=" + resource + "&id=" + str(service_id) + "&languageCode=" + language + "&withHeader=true"
    layer["disclaimer_html"] = requests.get(url, verify=INTERNAL_SSL).content.decode()

def __wfs_srv_disclaimer(srv, language, resource):
    """ Handles a wfs srv set and sets the disclaimer info

    Args:
        srv: The current service working on
        language: The language that shall be used
        resource: The resource that shall be used in the url
    Returns:
        nothing
    """
    service_id = srv.get("id", None)
    if service_id is None:
        return
    url = URL_BASE + "php/mod_getServiceDisclaimer.php?type=" + resource + "&id=" + str(service_id) + "&languageCode=" + language + "&withHeader=true"
    srv["disclaimer_html"] = requests.get(url, verify=INTERNAL_SSL).content.decode()

def generic_srv_disclaimer(resource, service_id, language):
    """ Handles a generic srv set and returns the fetched disclaimer html

    Args:
        service_id: The service id
        language: The language that shall be used
        resource: The resource that shall be used in the url
    Returns:
        nothing
    """
    url = URL_BASE + "php/mod_getServiceDisclaimer.php?type=" + resource + "&id=" + str(service_id) + "&languageCode=" + language + "&withHeader=true"
    return requests.get(url, verify=INTERNAL_SSL).content.decode()

def __set_single_service_disclaimer_url(search_results, resource):
    """ Function handles a single resource from search_results. This function is needed for multithreading.

    Args:
        search_results: Output of searcher.py
        resource: A specific resource from the search_results
    Returns:
        nothing
    """
    language = "de"
    thread_list = []
    if resource == "dataset":
        for srv in search_results[resource][resource][resource]["srv"]:
            thread_list.append(threading.Thread(target=__dataset_srv_disclaimer, args=(srv, language)))
    elif resource == "wmc":
        # wmc has no disclaimer info -> set it to None
        for srv in search_results["wmc"]["wmc"]["wmc"]["srv"]:
            srv["disclaimer_html"] = None
    elif resource == "wms":
        for layer in search_results[resource][resource][resource]["srv"]:
            thread_list.append(threading.Thread(target=__wms_srv_disclaimer, args=(layer, language, resource)))
    elif resource == "wfs":
        for srv in search_results["wfs"]["wfs"]["wfs"]["srv"]:
            thread_list.append(threading.Thread(target=__wfs_srv_disclaimer, args=(srv, language, resource)))

    # Run threads
    helper.execute_threads(thread_list)


def set_service_disclaimer_url(search_results):
    """ Sets for all search results the service disclaimer url

    Args:
        search_results: Output of searcher.py
    Returns:
        Returns the modified search_results
    """
    resources = gen_resource_arr(search_results)
    thread_list = []
    for resource in resources:
        #__set_single_service_disclaimer_url(search_results, resource)
        thread_list.append(threading.Thread(target=__set_single_service_disclaimer_url, args=(search_results, resource)))
    helper.execute_threads(thread_list)
    return search_results


####    ISO 3166
def __gen_single_iso3166_icon_path(title: str):
    """ Find the iso3166 icon path

    Args:
        title (string): The state title (RLP, NRW, ...)
    Returns:
        string: The icon file name for a state title
    """
    for state_key, state_val in ISO3166_FILES.items():
        if title == state_key:
            return state_val


def set_iso3166_icon_path(search_results):
    """ Set the state icon file path for all search results

    Args:
        search_results:
    Returns:
        Returns the modified search_results
    """
    resources = gen_resource_arr(search_results)
    for resource in resources:
        for srv in search_results[resource][resource][resource]["srv"]:
            if resource == "dataset":
                layers = srv.get("coupledResources", {}).get("layer", {})
                for layer in layers:
                    layer_srv = layer.get("srv", None)
                    if layer_srv is None:
                        continue
                    if layer_srv.get("iso3166", None) is None:
                        continue
                    layer_srv["iso3166_path"] = __gen_single_iso3166_icon_path(layer_srv["iso3166"])
            else:
                if srv.get("iso3166", None) is None:
                    continue
                srv["iso3166_path"] = __gen_single_iso3166_icon_path(srv["iso3166"])
    return search_results


####    FACETS/CATEGORIES
def prepare_selected_facets(selected_facets):
    """ Selected facets are sent as a triple of (parentCategory, title, id) which needs to be transformed into a dict for better handling

    Args:
        selected_facets: An array of facets which were selected by the user
    Returns:
        dict: Contains (parentCategory, title, id)
    """
    ret_dict = {}
    for facet in selected_facets:
        if facet == "":
            break
        facet = facet.split(",")
        facet_dict = {
            "parent_category": facet[0],
            "title": facet[1],
            "id": facet[2],
        }
        if facet_dict.get("parent_category") not in ret_dict:
            ret_dict[(facet_dict.get("parent_category"))] = []
        ret_dict[facet_dict.get("parent_category")].append(facet_dict)

    return ret_dict

def __resolve_single_facet(preselected_categories, all_categories):
    """ Search for a single id which encodes the facet/category data.

    Args:
        preselected_categories:
        all_categories:
    Returns:
        list: Contains all resolved category pseudo-objects
    """
    ret_arr = []
    if len(preselected_categories) > 0:
        iso_cat_arr = preselected_categories.split(",")
        for iso_id in iso_cat_arr:
            for iso_subcat in all_categories.get("subcat", []):
                if iso_subcat["id"] == iso_id:
                    parent_category = all_categories["title"]
                    title = iso_subcat["title"]
                    id = iso_subcat["id"]
                    facet = {
                        "parent_category": parent_category,
                        "title": title,
                        "id": id,
                    }
                    ret_arr.append(facet)
                    break
    return ret_arr

def get_preselected_facets(params, all_categories):
    """ Resolve all facets that have been determined by the GET parameters.

    Args:
        params: Contains the categories/facets
        all_categories:
    Returns:
        dict: Contains all sorted facets
    """
    ret_arr = {}

    iso_cat = params.get("isoCategories", "")
    custom_cat = params.get("customCategories", "")
    inspire_cat = params.get("inspireThemes", "")
    org_cat = params.get("registratingDepartments", "")

    # resolve ids by iterating all_categories
    all_iso_cat = all_categories[0]
    all_inspire_cat = all_categories[1]
    all_custom_cat = all_categories[2]
    all_org_cat = all_categories[3]

    iso_preselect = __resolve_single_facet(iso_cat, all_iso_cat)
    inspire_preselect = __resolve_single_facet(inspire_cat, all_inspire_cat)
    custom_preselect = __resolve_single_facet(custom_cat, all_custom_cat)
    org_preselect = __resolve_single_facet(org_cat, all_org_cat)

    if len(iso_preselect) > 0:
        ret_arr["ISO 19115"] = iso_preselect
    if len(inspire_preselect) > 0:
        ret_arr["INSPIRE"] = inspire_preselect
    if len(custom_preselect) > 0:
        ret_arr["Sonstige"] = custom_preselect
    if len(org_preselect) > 0:
        ret_arr["Organisationen"] = org_preselect

    return ret_arr

def get_search_filters(search_results):
    """ Find any search filter from the search results. Which one does not matter, since they contain all the same searchText and so on.

    Args:
        search_results: Output of searcher.py
    Returns:
        OrderedDict: Contains the search_filter object from the search_results
    """
    search_filters = OrderedDict()
    for resource_key, resource_val in search_results.items():
        if len(search_filters) == 0:
            search_filters = resource_val["filter"]["searchFilter"]
        else:
            search_filters["classes"].append(resource_val["filter"]["searchFilter"]["classes"][0])
    return search_filters

def prepare_requested_resources(requ_res):
    """ Ajax sends an array as a string. We need to decompose each element from that string and reforge it into a "true" array

    Args:
        requ_res:
    Returns:
        list: Represents the string array requ_res as a real list
    """
    requested_resources = requ_res.replace("[", "").replace("]", "").replace("\"","")
    requested_resources = requested_resources.split(",")
    if len(requested_resources) == 1 and requested_resources[0] == '':
        requested_resources = []
    return requested_resources


def prepare_info_search_results(search_results, list_all: bool, lang: str):
    """ Merge info search results

    Merges all search results for each keyword into one list.
    Simplifies later usage in template rendering

    Args:
        search_results (dict): The info search results dict
        list_all (bool): Flag indicates if this function handles the results of a real search or simple listing of all
        lang (str): Contains the selected language for the page
    Returns:
         nothing

    """
    ret_list = {}
    if list_all:
        ret_list["all"] = []
        for result in search_results.get("query", {}).get("allpages", []):
            if "/" + lang in result.get("title"):
                ret_list["all"].append(result)
    else:
        for search_result_key, search_result_val in search_results.items():
            ret_list[search_result_key] = []
            for result in search_result_val:
                res = result["query"].get("search", [])
                if len(res) > 0:
                    for hit in res:
                        if ret_list.get(search_result_key, None) is None:
                            ret_list[search_result_key] = []
                        if "/" + lang in hit.get("title", "") and hit not in ret_list[search_result_key]:
                            # This way we try to fetch only translated pages with '.../de' or '.../en'
                            # Since the mediawiki API is ***** we have no direct way to fetch only translated ones
                            ret_list[search_result_key].append(hit)

    return ret_list


def resolve_internal_external_info(search_results, searcher: Searcher):
    """ Checks if a mediawiki article is an internal or external article

    Args:
        search_results:
        searcher:
    Returns:
        nothing

    """
    for search_result_key, search_result_val in search_results.items():
        for result in search_result_val:
            result["is_intern"] = False
            categories = searcher.get_info_result_category(result)
            for cat in categories:
                result["is_intern"] = INTERNAL_PAGES_CATEGORY in cat.get("title", "")
                break
    return search_results


def __hash_single_inspire_id(results):
    """

    Args:
        results:
    Returns:

    """

    for result in results["srv"]:
        m = hashlib.md5()
        id = result.get("id", "").encode("utf-8")
        m.update(id)
        result["id"] = m.hexdigest()


def hash_inspire_ids(search_results):
    """ Hash the inspire ids

    The inspire ids contain symbols that are not allowed to use as IDs in javascript. Therefore
    many functions would not work if we do not hash these.
    Args:
        search_results: Contains all search results
    Returns:
         search_results
    """
    thread_list = []
    for search_result_key, search_result_val in search_results.items():
        thread_list.append(
            threading.Thread(
                target=__hash_single_inspire_id, args=(search_result_val[search_result_key],)
            )
        )
    helper.execute_threads(thread_list)
    return search_results

def check_previewUrls(search_results):
    """ Checks if a result provides a previewUrl for thumbnails. Otherwise a placeholder will be set.

    This function is needed to avoid too much logic in template rendering.

    Args:
        search_results: Contains all search results
    Returns:
        search_results
    """
    for result_key, result_val in search_results.items():
        results = result_val.get(result_key, {}).get("srv", [])
        for result in results:
            if len(result.get("previewUrl", "")) == 0:
                result["previewUrl"] = None
    return search_results


def check_search_bbox(session_id, bbox):
    """ Checks whether a bounding box exists for this search and writes the bounding box geometry into the session

    Args:
        session_id: Which session shall be written to
        bbox: The bounding box as comma separated string
    Returns:
         nothing
    """
    if bbox != '':
        # set glm to session
        lat_lon = bbox.split(",")
        lat_lon = {
            "minx": lat_lon[0],
            "miny": lat_lon[1],
            "maxx": lat_lon[2],
            "maxy": lat_lon[3],
        }
        write_gml_to_session(session_id=session_id, lat_lon=lat_lon)


def resolve_coupled_resources(md_link: str):
    """ Resolves series and dataset coupled resources for secondary catalogues such as DE and EU

    Args:
        uri: The metadata uri for which coupled resources shall be resolved
    Returns:
         resources (dict): Contains the accessUrl and serviceTitle
    """
    val = {
        "view_links": None,
        "download_links": None,
    }
    searcher = Searcher(host=HOSTNAME)
    resources = searcher.get_coupled_resource(md_link).get("result", {}).get("service", [])
    if resources is not None:
        for resource in resources:
            _type = resource.get("serviceType", "")
            uri = resource.get("accessUrl", None)
            data = {
                    "uri": uri,
                    "showMapUrl": uri,
                    "title": resource.get("serviceTitle", None),
                    "id": sha256(md_link),
                    "mdLink": resource.get("htmlLink", None),
                }
            if _type == "view":
                val["view_links"] = data
            elif _type == "download":
                val["download_links"] = data
            else:
                pass
    return val