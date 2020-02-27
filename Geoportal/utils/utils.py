"""
This file contains methods that are needed in all apps.

Author: Michel Peltriaux
Organization: Spatial data infrastructure Rheinland-Pfalz, Germany
Contact: michel.peltriaux@vermkv.rlp.de
Created on: 22.01.19
"""
import hashlib
import urllib
import requests

from collections import OrderedDict
from copy import copy
from Geoportal.settings import DEBUG, INTERNAL_SSL
from searchCatalogue.utils.url_conf import URL_SESSION_WRAPPER, URL_BASE_LOCALHOST
from useroperations.models import Navigation
from requests.packages.urllib3.exceptions import InsecureRequestWarning
requests.packages.urllib3.disable_warnings(InsecureRequestWarning)
from urllib import parse


def get_navigation_items():
    """ Returns the navigation items from the database

    Returns:
        dict: Contains upper level pagenames with associated lists of subpagenames
    """
    navigation = Navigation.objects.all().order_by('position')
    navigation_dict = OrderedDict()
    navigation = copy(navigation)
    for upper_item in navigation:
        if upper_item.parent is None:
            navigation_dict[upper_item.name] = {
                "parent": upper_item,
                "children": []
            }

    for lower_item in navigation:
        if lower_item.parent is not None:
            navigation_dict[lower_item.parent.name]["children"].append(lower_item)

    return navigation_dict

def write_gml_to_session(session_id: str, lat_lon: dict):
    """ Writes gml data to session

    Args:
        session_id: The php session id
        lat_lon: A dict containing the x and y min and max values
    Returns:
        Nothing
    """
    minx = str(lat_lon.get("minx", -1))
    miny = str(lat_lon.get("miny", -1))
    maxx = str(lat_lon.get("maxx", -1))
    maxy = str(lat_lon.get("maxy", -1))

    post_content = '<FeatureCollection xmlns:gml="http://www.opengis.net/gml"><boundedBy><Box srsName="EPSG:4326">'
    post_content += "<coordinates>" + minx + "," + miny + " " + maxx
    post_content += "," + maxy + "</coordinates></Box>"
    post_content += '</boundedBy><featureMember><gemeinde><title>BBOX</title><the_geom><MultiPolygon srsName="EPSG:'
    post_content += "4326" + '"><polygonMember><Polygon><outerBoundaryIs><LinearRing><coordinates>'
    post_content += minx + "," + miny + " " + maxx + ","
    post_content += miny + " " + maxx + "," + maxy + " "
    post_content += minx + "," + maxy + " " + minx + "," + miny
    post_content += "</coordinates></LinearRing></outerBoundaryIs></Polygon></polygonMember></MultiPolygon></the_geom></gemeinde></featureMember></FeatureCollection>"

    data = {
        "value": post_content
    }

    uri = URL_BASE_LOCALHOST + URL_SESSION_WRAPPER + "?sessionId=" + session_id + "&operation=set&key=GML&" + urllib.parse.urlencode(data)

    response = requests.post(url=uri, data=post_content, verify=INTERNAL_SSL)
    i = 0

def execute_threads(thread_list):
    """ Executes a list of threads

    Args:
        thread_list (list): A list of threads
    Returns: nothing
    """
    for thread in thread_list:
        thread.start()
    for thread in thread_list:
        thread.join()


def print_debug(string: str):
    """ Print only if we are in dev mode!

    Args:
        string:
    Returns:
    """
    if DEBUG:
        print(string)


def resolve_boolean_value(val: str):
    """ Resolve a string which represents a boolean value

    Args:
        val: The value
    Returns:
         True, False or None
    """
    val = val.upper()
    if val == "TRUE":
        return True
    elif val == "FALSE":
        return False
    else:
        return None


def sha256(_input: str):
    """ Creates a sha256 hash from the input

    Args:
        _input (str): A string
    Returns:
         A sha256 hash string
    """
    m = hashlib.sha256()
    m.update(_input.encode("UTF-8"))
    return m.hexdigest()