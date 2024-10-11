"""

Author: Michel Peltriaux
Organization: Spatial data infrastructure Rheinland-Pfalz, Germany
Contact: michel.peltriaux@vermkv.rlp.de
Created on: 22.01.19

"""
from Geoportal.settings import INTERNAL_SSL
from Geoportal.utils.request_handler import CustomSession
from searchCatalogue.utils.searcher import Searcher
from searchCatalogue.utils.url_conf import *


class AutoCompleter:
    """ The class instance for the auto completion on the address bar.

    """

    def __init__(self, search_text, max_results):
        """ Constructor

        Argas:
            search_text (string): Which strings shall be searched for
            max_results (int): How many hits shall be fetched/displayed
        """
        # Define search arguments
        self.search_text = search_text
        self.max_results = max_results
        self.session = CustomSession()

    def set_search_text(self, search_text):
        """ Setter for search text

        Args:
            search_text (string): The new search_text
        Returns:
            AutoCompleter: Returns the object itself
        """
        self.search_text = search_text
        return self

    def set_max_results(self, max_results):
        """ Setter for max results

        Args:
            max_results (int): The new search_text
        Returns:
            AutoCompleter: Returns the object itself
        """
        self.max_results = max_results
        return self

    def get_data_search_suggestions(self):
        """ Returns all suggestions for the search texts

        Returns:
             dict: Contains suggestions
        """
        url = URL_BASE_LOCALHOST + URL_AUTO_COMPLETE_SUFFIX
        params = {
            "searchText": self.search_text,
            "maxResults": self.max_results,
        }
        response = self.session.get(
            url=url, params=params, verify=INTERNAL_SSL)
        if not response:
            response = {
                "results": 0,
                "resultList": []
            }
        return response

    def get_location_suggestions(self):
        """ Returns location suggestions that match the search texts

        Returns:
             dict: Contains suggestions
        """
        searcher = Searcher()
        locations = searcher.search_locations(
            [self.search_text], max_results=self.max_results, srs=25832)
        return locations
