"""
Author: Michel Peltriaux
Organization: Spatial data infrastructure Rhineland-Palatinate, Germany
Contact: michel.peltriaux@vermkv.rlp.de
Created on: 04.03.20

"""
from searchCatalogue.utils.searcher import Searcher

LOCATION_QUERY_SHORTCUT = ":"

SPATIAL_FILTER_SHORTCUTS = [
    LOCATION_QUERY_SHORTCUT,
]

def uses_shortcut(_input: str):
    """ Checks whether a shortcut character is used or not

    Args:
        _input (str): The input to be checked
    Returns:
         True|False
    """
    for shortcut in SPATIAL_FILTER_SHORTCUTS:
        if shortcut in _input:
            return True
    return False

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

def process_regular_filter(_input: str):
    """ Run a regular spatial search filter

    Args:
        _input (str): The input
    Returns:
         results (dict): Contains 'looking_for' (list) and 'in' (list)
    """
    searcher = Searcher()
    search_text = _input.split(",")
    results = searcher.search_locations(search_text)
    results = prepare_spatial_data(results)
    return results

def process_shortcut_filter(_input: str):
    """ Run the spatial search filter using a shortcut

    Args:
        _input (str): The input
    Returns:
         results (dict): Contains 'looking_for' (list) and 'in' (list)
    """
    results = {
        "looking_for": [],
        "in": [],
    }

    if LOCATION_QUERY_SHORTCUT in _input:
        results = _process_location_query_shortcut_filter(_input)
    else:
        # Maybe we will add even more shortcuts in the future
        pass

    return results

def _process_location_query_shortcut_filter(_input: str):
    """ Run the spatial search filter using the location query shortcut

    Location query shortcut follows the structure   [LOCATION]:[QUERY_A],[QUERY_B],...

    Args:
        _input (str): The input
    Returns:
         results (dict): Contains 'looking_for' (list) and 'in' (list)
    """
    results = {
        "looking_for": [],
        "in": [],
    }
    searcher = Searcher()
    shortcut_search_text = _input.split(":")

    if len(shortcut_search_text) != 2:
        # The user did something wrong on the input, simply act as if there is no ':' shortcut and continue a regular
        # spatial search
        search_text = shortcut_search_text
    else:
        # First input defines a location, last input defines a query
        location = shortcut_search_text[0]
        query = shortcut_search_text[1]
        spatial_data = searcher.search_locations([location])
        results["looking_for"] = [query, ]
        results["in"] = spatial_data

    return results
