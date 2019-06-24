"""

Author: Michel Peltriaux
Organization: Spatial data infrastructure Rheinland-Pfalz, Germany
Contact: michel.peltriaux@vermkv.rlp.de
Created on: 11.03.19

"""
from django.http.response import JsonResponse

from Geoportal import helper, settings
from Geoportal.settings import DEFAULT_GUI, RSS_FILE, HOSTNAME, HTTP_OR_SSL, IFRAME_HEIGHT, IFRAME_WIDTH


class GeoportalJsonResponse:
    """ Generic JsonResponse wrapper for Geoportal

    Use for AJAX responses.
    There are three default values for the response: 'html', 'response' and 'url'.
    'Html' contains prerendered html content, that will be pasted by Javascript into an html element.

    IMPORTANT:
    Always(!) use this object instead of a direct JsonResponse() object.

    """

    def __init__(self, html="", url="", **kwargs: dict):
        self.response = {
            "html": html,
            "url": url,
        }
        # add optional parameters
        for arg_key, arg_val in kwargs.items():
            self.response[arg_key] = arg_val

    def get_response(self):
        return JsonResponse(self.response)


class GeoportalContext:
    """ Contains boilerplate attributes

    Parameters and attributes that are always used in rendering for pages shall be put in here.

    IMPORTANT:
    Always(!) use this object for render() calls to make sure there are all parameters available in the templates.

    """

    def __init__(self, request):
        session_data = helper.get_mb_user_session_data(request)
        self.data = {
            "navigation": helper.get_navigation_items(),
            "selected_navigation": request.path,
            "loggedin": session_data.get("loggedin"),
            'user': session_data.get("user", ""),
            'userid': session_data.get("userid", ""),
            'gui': session_data.get("gui", None),
            'guis': session_data.get("guis", ""),
            'dsgvo': session_data.get("dsgvo", "no"),
            'lang': request.LANGUAGE_CODE,
            "HOSTNAME": HOSTNAME,
            "HTTP_OR_SSL": HTTP_OR_SSL,
            "DEFAULT_GUI": DEFAULT_GUI,
            "basedir": settings.BASE_DIR,
            "rss_file": RSS_FILE,
            "cookie": request.COOKIES.get("Geoportal-RLP", None),
            "sidebar_closed": helper.resolve_boolean_value(request.COOKIES.get("sidebarClosed", 'False')),
            "is_mobile": request.user_agent.is_mobile,
            "IFRAME_HEIGHT": IFRAME_HEIGHT,
            "IFRAME_WIDTH": IFRAME_WIDTH,
        }

    def add_context(self, context: dict):
        """ Adds a complete dict to the default configuration

        Args:
            context (dict): The context dict
        Returns:
        """
        for key, val in context.items():
            self.data[key] = val

    def get_context(self):
        return self.data
