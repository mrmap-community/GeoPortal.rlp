"""

Author: Michel Peltriaux
Organization: Spatial data infrastructure Rheinland-Pfalz, Germany
Contact: michel.peltriaux@vermkv.rlp.de
Created on: 22.01.19

"""
import hashlib
import random
import threading
import requests

from copy import copy
from collections import OrderedDict
from json import JSONDecodeError

from requests.packages.urllib3.exceptions import InsecureRequestWarning

from Geoportal.utils import utils
from Geoportal.settings import PRIMARY_CATALOGUE, INTERNAL_SSL, SEARCH_API_PROTOCOL, INTERNAL_PAGES_CATEGORY

from searchCatalogue.settings import PROXIES
from searchCatalogue.utils.url_conf import *

requests.packages.urllib3.disable_warnings(InsecureRequestWarning)


class Searcher:

    def __init__(self, keywords="",
                 resource_set="wms",
                 extended_search_params="",
                 page=1,
                 page_res="",
                 selected_facets={},
                 order_by="",
                 max_results=5,
                 bbox=None,
                 type_bbox=None,
                 language_code="de",
                 catalogue_id=PRIMARY_CATALOGUE,
                 only_open_data='false',
                 host=None,
                 result_target="webclient",):
        """ Constructor

        Args:
            keywords: The search text
            resource_set: The resource for that a search shall be started
            extended_search_params: The search parameters from the extended search menu
            page: The page that is requested
            page_res: For which resource is the page requested
            selected_facets: Which facets/filters/categories are currently selected in the search module
            order_by: Which order shall be used
            bbox: The bbox for e.g. intersection
            type_bbox: The type of bbox
            language_code: In which language shall the results be returned
            catalogue_id: Which catalogue is fetched
        """
        self.keywords = keywords
        self.output_format = "json"
        self.result_target = result_target
        self.search_pages = page
        self.search_resources = resource_set
        self.extended_params = extended_search_params
        self.search_page_resource = page_res
        self.selected_facets = selected_facets
        self.order_by = order_by
        self.max_results = max_results
        self.bbox = bbox
        self.typeBbox = type_bbox
        self.catalogue_id = catalogue_id
        self.language_code = language_code
        self.only_open_data = only_open_data
        self.host = host

        self.org_ids = []
        self.iso_ids = []
        self.custom_ids = []
        self.inspire_ids = []
        self.lock = threading.BoundedSemaphore()

        # get random search id
        md_5 = hashlib.md5()
        microseconds = str(random.getrandbits(128)).encode("utf-8")
        md_5.update(microseconds)
        self.search_id = md_5.hexdigest()

    def _prepare_selected_facets(self):
        """ Find the ids of the selected facets in all facets

        Returns:
            nothing
        """
        # prepare registrating departments facets
        for facet_key, facet_val in self.selected_facets.items():
            for facet in facet_val:
                if facet.get("parent_category") == "ISO 19115":
                    self.iso_ids.append(facet.get("id"))
                elif facet.get("parent_category") == "INSPIRE":
                    self.inspire_ids.append(facet.get("id"))
                elif facet.get("parent_category") == "Custom":
                    self.custom_ids.append(facet.get("id"))
                elif facet.get("parent_category") == "Organizations":
                    self.org_ids.append(facet.get("id"))

    def _get_resource_results(self, url, params: dict, resource, result: dict):
        """ Use a GET request to retrieve the search results for a specific data resource

        Args:
            url: The url to be fetched from
            params: The parameters for the GET request as dict
            resource: The name of the data resource that shall be fetched
            result: The return dict that will be changed during this function
        Returns:
            nothing
        """
        response = requests.get(url, params, verify=INTERNAL_SSL)
        result[resource] = response.json()

    def search_categories_list(self, lang):
        """ Get a list of all categories/facets from the database using a GET request

        Returns:
            Returns the categories which have been found during the search
        """
        url = URL_BASE + URL_SEARCH_PRIMARY_SUFFIX
        params = {
            "outputFormat": self.output_format,
            "resultTarget": self.result_target,
            "searchResources": self.search_resources,
            "searchId": self.search_id,
            "languageCode": lang,
            "hostName": HOSTNAME,
            "protocol": SEARCH_API_PROTOCOL,
        }
        if self.host is not None:
            params["hostName"] = self.host
        response = requests.get(url, params, verify=INTERNAL_SSL)
        response = response.json()
        categories = response["categories"]["searchMD"]["category"]
        return categories

    def search_all_organizations(self):
        """ Get a list of all organizations that published data

        Returns:
             dict: Contains a json list of all organizations
        """
        # get overview of all organizations
        uri = URL_BASE + URL_GET_ORGANIZATIONS
        response = requests.get(uri, {}, verify=INTERNAL_SSL)
        if response.status_code == 200:
            response = response.json().get("organizations")
            return response
        return {}

    def search_all_topics(self, language):
        """ Get a list of all topics that can be found in the database

        Returns:
             dict: Contains a json list of all topics
        """
        uri = URL_BASE + URL_GET_TOPICS
        params = {
            "type": "inspireCategories",
            "scale": "absolute",
            "maxObjects": 35,
            "maxFontSize": 30,
            "languageCode": language,
            "outputFormat": "json",
            "hostName": HOSTNAME,
            "protocol": SEARCH_API_PROTOCOL,
        }
        response = requests.get(uri, params, verify=INTERNAL_SSL)
        if response.status_code == 200:
            response = response.json().get("tagCloud")
            return response
        return {}

    def search_coupled_resource(self, md_link):
        """ Resolve coupled dataset/series resources for secondary catalogues

        Args:
            md_link: The metadata link of the resource that needs to be resolved
        Returns:
            response(dict): The response body as json
        """
        uri = URL_BASE + URL_RESOLVE_COUPLED_RESOURCES
        params = {
            "getRecordByIdUrl": md_link,
            "hostName": self.host,
        }
        response = requests.get(uri, params, verify=INTERNAL_SSL)
        if response.status_code == 200:
            try:
                response = response.json()
            except JSONDecodeError:
                return {}
            return response
        return {}

    def search_primary_catalogue_data(self, user_id=None):
        """ Performs the search

        Search parameters will be used from the Searcher object itself.

        Returns:
            dict: Contains the search results
        """
        url = URL_BASE + URL_SEARCH_PRIMARY_SUFFIX
        self._prepare_selected_facets()
        params = {
            "searchText": self.keywords,
            "outputFormat": self.output_format,
            "resultTarget": self.result_target,
            "searchPages": 1,   # default for non directly requested categories
            "searchResources": self.search_resources,
            "searchId": self.search_id,
            "resolveCoupledResources": 'true',
            "registratingDepartments": ",".join(self.org_ids),
            "isoCategories": ",".join(self.iso_ids),
            "customCategories": ",".join(self.custom_ids),
            "inspireThemes": ",".join(self.inspire_ids),
            "orderBy": self.order_by,
            "maxResults": self.max_results,
            "searchBbox": self.bbox,
            "searchTypeBbox": self.typeBbox,
            "languageCode": self.language_code,
            "restrictToOpenData": self.only_open_data,
            "hostName": HOSTNAME,
            "userId": user_id,
            "protocol": SEARCH_API_PROTOCOL,
        }
        if self.host is not None:
            params["hostName"] = self.host

        params.update(self.extended_params)
        result = {}
        thread_list = []
        if len(self.search_resources) == 1 and self.search_resources[0] == '':
            return result
        for resource in self.search_resources:
            _session = requests.Session()
            if resource == self.search_page_resource:
                # use requested page
                params["searchPages"] = self.search_pages
            else:
                params["searchPages"] = 1
            params["searchResources"] = resource
            thread = threading.Thread(target=self._get_resource_results, args=(url, copy(params), resource, result))
            thread_list.append(thread)
            #self.__get_resource_results(url, params, resource, result)
        utils.execute_threads(thread_list)
        return result

    def _get_external_resource_results(self, resource, params: dict, results: dict, url):
        """ Executes API calls for the german catalogue for each resource in an own thread

        Args:
            resource:    The name of the data resource that will be fetched
            params:      The parameters for the GET request as a dict
            results:     The result dict which will be filled with the search results during this function's call
            url:         The GET url
        Returns:
            nothing
        """

        response = requests.get(url, params, verify=INTERNAL_SSL)
        try:
            response = response.json()
            results[resource] = response
        except JSONDecodeError:
            return

    def search_external_catalogue_data(self):
        """ Main function for calling the german catalogue

        Returns:
            dict: Contains all search results
        """
        url = URL_BASE + URL_SEARCH_DE_SUFFIX
        params = {
            "catalogueId": self.catalogue_id,
            "searchText": self.keywords,
            "searchResources": "",
            "searchPages": self.search_pages,
            "searchBbox": self.bbox,
            "typeBbox": self.typeBbox,
            "maxResults": 5,
            "hostName": HOSTNAME,
            "protocol": SEARCH_API_PROTOCOL,
            "languageCode": self.language_code,
        }
        if self.host is not None:
            params["hostName"] = self.host
        thread_list = []
        results = OrderedDict()
        for resource in self.search_resources:
            if resource == self.search_page_resource:
                # use requested page
                params["searchPages"] = self.search_pages
            else:
                params["searchPages"] = 1
            params["searchResources"] = resource
            thread_list.append(threading.Thread(target=self._get_external_resource_results, args=(resource, copy(params), results, url)))
        utils.execute_threads(thread_list)

        return results

    def search_locations(self, search_texts):
        """ Performs a spatial filtered search

        Args:
            search_texts: All search words in a list
        Returns:
            Returns the spatial search results from the database
        """
        ret_val = []
        url = URL_SPATIAL_BASE + URL_SPATIAL_SEARCH_SUFFIX
        for search_text in search_texts:
            params = {
                "outputFormat": self.output_format,
                "resultTarget": "web",
                "searchEPSG": 4326,
                "maxResults": 15,
                "maxRows": 15,
                "searchText": search_text,
                "hostName": HOSTNAME,
                "protocol": SEARCH_API_PROTOCOL,
            }
            if self.host is not None:
                params["hostName"] = self.host
            response = requests.get(url, params, proxies=PROXIES, verify=INTERNAL_SSL)
            result = response.json()
            result["keyword"] = search_text
            ret_val.append(result)


        return ret_val

    def _get_single_info_result(self, params: dict, results: dict):
        """ Runs a single thread GET request

        Args:
            params: Parameters for the GET request
            results: The dict to be modified
        Returns:
            nothing
        """
        response = requests.get(url=URL_SEARCH_INFO, params=params, verify=INTERNAL_SSL)
        response = response.json()
        params["srsearch"] = params["srsearch"].replace("*", "")
        if results.get(params["srsearch"], None) is None:
            results[params["srsearch"]] = []
        # remove asterisks to avoid rendering them to the user!
        results[params["srsearch"]].append(response)

    def get_info_result_category(self, search_result):
        """

        Args:
            search_result (dict): The search result that shall be checked
        Returns:
             category (str): The categories for the search result
        """
        params = {
            "titles": search_result.get("title", ""),
            "action": "query",
            "format": "json",
            "prop": "categories",
            "hostName": HOSTNAME,
            "protocol": SEARCH_API_PROTOCOL,
        }
        response = requests.get(url=URL_SEARCH_INFO, params=params, verify=INTERNAL_SSL)
        response = response.json()
        response = response["query"]["pages"]
        for resp_key, resp_val in response.items():
            return resp_val.get("categories", [])

    def is_article_internal(self, title):
        """ Checks if the provided title is associated with an internal article

        Args:
            title (str): The title of the article
        Returns:
             bool: True if the article is internal, False otherwise
        """
        tmp = {
            "title": title
        }
        resp = self.get_info_result_category(tmp)
        for category in resp:
            if INTERNAL_PAGES_CATEGORY in category.get("title", ""):
                return True
        return False

    def get_info_search_results(self):
        params = {
            "action": "query",
            "list": "search",
            "srsearch": self.keywords,
            "format": "json",
            "srwhat": ["text", "title", "nearmatch"]
        }
        # since the mediawiki API does not handle multiple search words, we need to iterate over all
        # keywords in the keywords array and search every time for all three srwhat types for hits
        thread_list = []
        results = {}
        for keyword in self.keywords:
            # append a asterisks for matching everything that contains this part
            params["srsearch"] = "*" + keyword + "*"
            for what in params["srwhat"]:
                params_cp = copy(params)
                params_cp["srwhat"] = what
                # create thread
                thread_list.append(threading.Thread(target=self._get_single_info_result, args=(params_cp, results)))
            thread_list.append(threading.Thread(target=self.get_info_pdf_files, args=(keyword, results)))
        utils.execute_threads(thread_list)
        return results

    def get_info_all_pages(self):
        """ Returns all mediawiki pages directly

        Returns:
             A dict containing all pages
        """
        params = {
            "action": "query",
            "list": "allpages",
            "apprefix": "",
            "format": "json",
            "aplimit": 500,
        }
        results = {}
        response = requests.get(url=URL_SEARCH_INFO, params=params, verify=INTERNAL_SSL)
        response = response.json()
        results = response
        return results

    def get_info_pdf_files(self, keyword: str, results: dict):
        """ Searches for all given

        Args:
            keyword (str): The keyword, we are looking for
        Returns:
             A dict, containing the results
        """
        params = {
            "action": "query",
            "list": "allimages",
            "format": "json",
            "aifrom": "*",
            "aiprop": "canonicaltitle|mime|url",
            "ailimit": 500,
        }
        response = requests.get(url=URL_SEARCH_INFO, params=params, verify=INTERNAL_SSL)
        response = response.json().get("query", {}).get("allimages", [])

        # the mediawiki API does not provide a way to fetch directly files with a certain mimeType or even with a title match
        # therefore we need to iterate by hand
        pdf_files = []
        for item in response:
            if keyword.upper() in item.get("name", "").upper() and item.get("mime", "") == "application/pdf":
                pdf_files.append(item)
        if results.get(keyword, None) is not None:
            results[keyword] += pdf_files
        else:
            results[keyword] = pdf_files
