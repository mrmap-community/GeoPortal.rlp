from django.http import HttpRequest
from django.shortcuts import render
from Geoportal.geoportalObjects import GeoportalContext
from Geoportal.settings import TWITTER_NAME


def twitter_view(request: HttpRequest):
    """ Renders the view for the twitter API.

    Args;
        request:
    Returns:
         The rendered view.
    """
    template = "twitter.html"

    params = {
        "twitter_name": TWITTER_NAME,
    }
    context = GeoportalContext(request=request)
    context.add_context(params)
    return render(request=request, context=context.get_context(), template_name=template)

