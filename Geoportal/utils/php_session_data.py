"""
This file contains methods that are needed in all apps.

Author: André Holl
Organization: Spatial data infrastructure Rheinland-Pfalz, Germany
Contact: andre.holl@vermkv.rlp.de
Created on: 02.05.19
"""


import requests
from pymemcache.client import base
from phpserialize import *
from django.http import HttpRequest
from Geoportal.settings import DEFAULT_GUI, HTTP_OR_SSL, INTERNAL_SSL, PROJECT_DIR, SESSION_NAME
from Geoportal.utils.mbConfReader import get_mapbender_config_value
from useroperations.models import MbUser

def get_mapbender_session_by_memcache(session_id):
    client = base.Client(('localhost', 11211))

    try:
        session_data = client.get('memc.sess.' + session_id)
    except ConnectionRefusedError:
        print("Connection Refused!Memcached not running?")

    try:
        session_data = loads(session_data)
    except ValueError:
        session_data = None
    return session_data

def delete_mapbender_session_by_memcache(session_id):
    client = base.Client(('localhost', 11211))
    client.delete('memc.sess.' + session_id)

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



    if request.COOKIES.get(SESSION_NAME) is not None:
        session_data = get_mapbender_session_by_memcache(request.COOKIES.get(SESSION_NAME))
        if session_data != None:
            if b'mb_user_id' in session_data:
                guest_id = get_mapbender_config_value(PROJECT_DIR,'ANONYMOUS_USER')
                user = session_data[b'mb_user_name']
                userid = session_data[b'mb_user_id']

                if session_data[b'mb_user_id'] == guest_id.encode('utf-8'):
                    gui = str(session_data[b'mb_user_gui'], 'utf-8')
                    loggedin = False
                else:
                    response = requests.post(HTTP_OR_SSL + '127.0.0.1/local/guiapi.php', verify=INTERNAL_SSL, data=session_data[b'mb_user_guis'])

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
    guest_id = get_mapbender_config_value(PROJECT_DIR,'ANONYMOUS_USER')
    guest_name = MbUser.objects.get(mb_user_id=guest_id)
    # USER
    if session_data['loggedin'] != False:
        ret_dict["user"] = str(session_data['session_data'].get(b'mb_user_name', b""), "utf-8")
        ret_dict["userid"] = int(session_data['session_data'].get(b'mb_user_id', b""))
        ret_dict["gui"] = session_data['gui']
        ret_dict["guis"] = session_data['guis']
        ret_dict["loggedin"] = session_data['loggedin']
        ret_dict["dsgvo"] = str(session_data['session_data'].get(b'dsgvo', b""), "utf-8")
    # GUEST
    else:
        ret_dict["username"] = guest_name.mb_user_name
        ret_dict["userid"] = guest_id
        ret_dict["gui"] = guest_gui
        ret_dict["guis"] = guest_gui
        ret_dict["loggedin"] = False

    return ret_dict
