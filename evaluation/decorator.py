"""
Author: Michel Peltriaux
Organization: Spatial data infrastructure Rhineland-Palatinate, Germany
Contact: michel.peltriaux@vermkv.rlp.de
Created on: 01.07.19

"""
from evaluation.models import Access
from evaluation.utils import sha256


def log_access(function):
    """ Store all request information in the corresponding model

    Args:
        function: The function which will be continued after this decorator
    Returns:
         Nothing
    """
    def wrap(request, *args, **kwargs):
        browser_type = request.user_agent.browser.family
        browser_version = request.user_agent.browser.version_string
        browser_agent = request.user_agent.ua_string
        os = request.user_agent.os.family
        is_mobile = request.user_agent.is_mobile or request.user_agent.is_tablet
        ip = request.environ.get('REMOTE_ADDR', "")
        address = request.path

        access = Access()
        access.ip_hash = sha256(ip)
        access.browser_family = browser_type
        access.browser_version = browser_version
        access.browser = browser_agent
        access.os = os
        access.is_mobile = is_mobile
        access.called_address = address

        access.save()

        return function(request=request, *args, **kwargs)

    wrap.__doc__ = function.__doc__
    wrap.__name__ = function.__name__
    return wrap
