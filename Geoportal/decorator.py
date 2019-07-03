"""
Author: Michel Peltriaux
Organization: Spatial data infrastructure Rhineland-Palatinate, Germany
Contact: michel.peltriaux@vermkv.rlp.de
Created on: 09.05.19

"""
from django.shortcuts import redirect

allowed_browsers = [
    "Firefox",
    "Firefox Mobile",
    "Opera",
    "Chrome",  # Edge user agent has 'Chrome' as well
    "Chrome Mobile",
    "Edge",
    "Edge Mobile",
    "Chrome Mobile WebView",
    "Mobile Safari",
    "Safari",
    "Amazon Silk",
]

def check_browser(function):
    def wrap(request, *args, **kwargs):
        browser_type = request.user_agent.browser.family
        if browser_type not in allowed_browsers:
            return redirect("useroperations:incompatible-browser")
        return function(request=request, *args, **kwargs)

    wrap.__doc__ = function.__doc__
    wrap.__name__ = function.__name__
    return wrap
