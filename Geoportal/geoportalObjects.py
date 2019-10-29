"""

Author: Michel Peltriaux
Organization: Spatial data infrastructure Rheinland-Pfalz, Germany
Contact: michel.peltriaux@vermkv.rlp.de
Created on: 11.03.19

"""
from django.http.response import JsonResponse
from django.utils.translation import ugettext_lazy as _

from Geoportal import settings
from Geoportal.settings import DEFAULT_GUI, RSS_FILE, HOSTNAME, HTTP_OR_SSL, IFRAME_HEIGHT, IFRAME_WIDTH, MODERN_GUI
from Geoportal.utils import utils, php_session_data
from useroperations.conf import COOKIE_VALUE, GEOPORTAL_IDENTIFIER, LOGO_GEOPORTAL_TITLE, LOGO_COUNTRY_LINK_DE, \
    LOGO_COUNTRY_LINK_EN


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
        session_data = php_session_data.get_mb_user_session_data(request)
        self.data = {
            "navigation": utils.get_navigation_items(),
            "selected_navigation": request.path,
            "loggedin": session_data.get("loggedin"),
            'user': session_data.get("user", ""),
            'userid': session_data.get("userid", ""),
            'gui': session_data.get("gui", None),
            'guis': session_data.get("guis", ""),
            'mapviewers': {
                _("Modern"): MODERN_GUI,
                _("Klassik"): DEFAULT_GUI,
                _("Mobil"): HTTP_OR_SSL + HOSTNAME + "/mapbender/extensions/mobilemap2/index.html?wmc_id=current",
            },
            'dsgvo': session_data.get("dsgvo", "no"),
            'preferred_gui': session_data.get("preferred_gui", DEFAULT_GUI),
            'lang': request.LANGUAGE_CODE,
            "HOSTNAME": HOSTNAME,
            "HTTP_OR_SSL": HTTP_OR_SSL,
            "DEFAULT_GUI": DEFAULT_GUI,
            "basedir": settings.BASE_DIR,
            "rss_file": RSS_FILE,
            "cookie": request.COOKIES.get(COOKIE_VALUE, None),
            "sidebar_closed": utils.resolve_boolean_value(request.COOKIES.get("sdbr-clsd", 'False')),
            "is_mobile": request.user_agent.is_mobile,
            "IFRAME_HEIGHT": IFRAME_HEIGHT,
            "IFRAME_WIDTH": IFRAME_WIDTH,
            "COOKIE_VALUE": COOKIE_VALUE,
            "GEOPORTAL_IDENTIFIER": GEOPORTAL_IDENTIFIER,
            "LOGO_GEOPORTAL_TITLE": LOGO_GEOPORTAL_TITLE,
            "LOGO_COUNTRY_LINK_DE": LOGO_COUNTRY_LINK_DE,
            "LOGO_COUNTRY_LINK_EN": LOGO_COUNTRY_LINK_EN,
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
