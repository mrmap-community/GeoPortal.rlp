"""

Author: Michel Peltriaux
Organization: Spatial data infrastructure Rheinland-Pfalz, Germany
Contact: michel.peltriaux@vermkv.rlp.de
Created on: 22.01.19

"""
import threading
from collections import OrderedDict

from Geoportal.utils import gerneral_helper


class Rehasher:
    """ Merges categories and filters from all search result types (wms, wfs, dataset, wmc) into one dict for better handling

    """
    def __init__(self, categories: dict, filters):
        """ Constructor

        Args:
            categories (dict): Specifies which categories/resource types shall be worked on
            filters: Specifies which filters will be used for rehashing
        """
        self.all_categories = []
        self.all_filters = filters
        for category_key, category_val in categories.items():
            self.all_categories.append(categories[category_key]["categories"]["searchMD"]["category"])
        self.rehashed_categories = OrderedDict()
        self.rehashed_filters = OrderedDict()
        self.__parent_categories = []
        self.__rehash()
        self.__sort_by_count()

    def __search_and_handle_subcat(self, c_subcat, rehashed_categories):
        """ Searches a specific subcategory and recalculates the parent category count number

        Since rehashing works multithreaded, we use these private functions for intra-class usage only!
        Recounts the occurences of a specific subcategory in the rehashed categories
        and updates the count number for the parent category.
        Only one subcategory per call will be searched and handled

        Args:
            c_subcat: Specifies the subcategory that we are looking for
            rehashed_categories (list): A list with categories that shall be handled
        Returns:
            bool: True if the subcategory was found and handled, False otherwise.
        """
        ret_val = False
        for rehashed_category in rehashed_categories:
            if rehashed_category["title"] == c_subcat["title"]:
                # found the subcat in the rehashed categories
                # update count number
                rehashed_category["count"] = int(rehashed_category["count"]) + int(c_subcat["count"])
                ret_val = True
                break
        return ret_val

    def __sort_by_count(self):
        """ Sort facets by number of count

        Returns:
            nothing
        """
        for category_key, category_val in self.rehashed_categories.items():
            category_val.sort(key=lambda x: int(x["count"]), reverse= True)

    def __rehash_single_thread(self, datatype):
        """ Rehashing of a single datatype

        This is one of multiple multithreaded calls. Each datatype has its own
        thread to be handled in.

        Args:
            datatype: Specifies the datatype that shall be handled.
        Returns:
            nothing
        """
        for category in datatype:
            # if there are no subcategories in the datatype but we haven't seen it yet, we take it anyway
            # if there are no subcategories in this datatype and we know the category itself already, we pass it
            if category.get("subcat", None) is None:
                if category["title"] not in self.rehashed_categories:
                    self.rehashed_categories[category["title"]] = []
                    continue
                else:
                    continue
            if category["title"] not in self.rehashed_categories:
                # this category is not know yet, add it!
                self.rehashed_categories[category["title"]] = category["subcat"]
            else:
                # the category is already in the rehashed list
                # we need to add the new subcategory elements to the existing ones
                for c_subcat in category["subcat"]:
                    # if the category has already a subcat with the title of c_subcat we need to update the count number
                    # otherwise if the subcat we currently iterate over is not in the subcategories of the category, we append it
                    if not self.__search_and_handle_subcat(c_subcat, self.rehashed_categories[category["title"]]):
                        # Yes, the name is shitty, but if we got in this branch it means that we found no matching subcategory
                        # So we add the c_subcat to the list, since it seems to be unknown so far
                        self.rehashed_categories[category["title"]].append(c_subcat)

    def __rehash(self):
        """ Merges all four category dicts into one large.

        Parent categories will be merged.
        Count of subcategories will be updated.

        Returns:
            nothing
        """
        thread_list = []
        # 1) Rehash categories
        for datatype in self.all_categories:
            thread_list.append(threading.Thread(target=self.__rehash_single_thread, args=(datatype,)))
        gerneral_helper.execute_threads(thread_list)
        # 2) Reorganize filter
        # Reorganize means we need to get rid of certain elements, which are useless in this system and would disturb the handling in a later process
        # only searchResources, orderFilter, maxResults and searchText from one datatype are needed, the rest is irrelevant
        delete_keys = [
            "isoCategories",
            "searchResources",
            "inspireThemes",
            "customCategories",
            "registratingDepartments"
        ]
        for key in delete_keys:
            if self.all_filters.get(key, None) is not None:
                del self.all_filters[key]
        self.rehashed_filters = self.all_filters


    def get_rehashed_categories(self):
        """ Getter for rehashed categories

        Returns:
            dict: The rehashed categories
        """
        return self.rehashed_categories

    def get_rehashed_filters(self):
        """ Getter for rehashed filters

        Returns:
            dict: The rehashed filters
        """
        return self.rehashed_filters
