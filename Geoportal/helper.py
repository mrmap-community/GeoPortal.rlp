"""
This file contains methods that are needed in all apps.

Author: Michel Peltriaux
Organization: Spatial data infrastructure Rheinland-Pfalz, Germany
Contact: michel.peltriaux@vermkv.rlp.de
Created on: 22.01.19
"""
from collections import OrderedDict
from copy import copy

from django.http import HttpRequest

from Geoportal.settings import EXTERNAL_INTERFACE, LOCAL_MACHINE, DEFAULT_GUI, HOSTNAME_HTTP
from useroperations.models import Navigation, MbUser
from useroperations.utils import helper_functions
import requests


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

def get_session_data(request):
    """ Parses the PHP session file

    To link between Django and old times PHP components, we need to fetch
    data from the PHP session file on the filesystem.
    This function parses the file and returns the information.

    Args:
        request (HttpRequest): The incoming request
    Returns:
         dict: Contains the session data for python
    """

    user = b'Noone'
    userid = None
    gui = DEFAULT_GUI
    guis = None
    loggedin = False
    session_data = None



    if request.COOKIES.get('PHPSESSID') is not None:
        session_data = helper_functions.get_mapbender_session_by_memcache(request.COOKIES.get('PHPSESSID'))
        #session_data = get_mapbender_session_by_file(request.COOKIES.get('PHPSESSID'))
        if session_data != None:
            if b'mb_user_id' in session_data:
                guest_id = helper_functions.get_mapbender_config_value('ANONYMOUS_USER')
                user = session_data[b'mb_user_name']
                userid = session_data[b'mb_user_id']

                if session_data[b'mb_user_id'] == guest_id.encode('utf-8'):
                    gui = str(session_data[b'mb_user_gui'], 'utf-8')
                    loggedin = False
                else:
                    #response = requests.post(LOCAL_MACHINE + '/portal/guiapi.php',data=session_data[b'mb_user_guis'])
                    response = requests.post('http://127.0.0.1/portal/guiapi.php',data=session_data[b'mb_user_guis'])
                    if session_data[b'mb_user_guis']:

                        guistring = response.text
                        guistring = guistring.replace('"', '')
                        guistring = guistring.replace('[', '')
                        guistring = guistring.replace(']', '')
                        guistring = guistring.replace('\\u00e4', 'ae')
                        guis = guistring.split(",")
                        loggedin = True
                    else:
                        guis = DEFAULT_GUI
                        loggedin = False

    data = {
        'session_data': session_data,
        'gui': gui,
        'guis': guis,
        'loggedin': loggedin,
    }

    return data


def get_mb_user_session_data(request: HttpRequest):
    """ Parse PHP session, focusing on mb_user data

    Args:
        request (HttpRequest):
    Returns:
        dict: Contains only user relevant data
    """
    session_data=get_session_data(request)
    ret_dict = {}
    guest_gui = [DEFAULT_GUI]
    guest_id = helper_functions.get_mapbender_config_value('ANONYMOUS_USER')
    guest_name = MbUser.objects.get(mb_user_id=guest_id)
    # USER
    if session_data['loggedin'] != False:
        ret_dict["user"] = str(session_data['session_data'][b'mb_user_name'], "utf-8")
        ret_dict["userid"] = int(session_data['session_data'][b'mb_user_id'])
        ret_dict["gui"] = session_data['gui']
        ret_dict["guis"] = session_data['guis']
        ret_dict["loggedin"] = session_data['loggedin']
    # GUEST
    else:
        ret_dict["username"] = guest_name.mb_user_name
        ret_dict["userid"] = guest_id
        ret_dict["gui"] = guest_gui
        ret_dict["guis"] = guest_gui
        ret_dict["loggedin"] = False

    return ret_dict


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
