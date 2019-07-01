from datetime import datetime

from django.http import HttpRequest, JsonResponse

# Create your views here.
from evaluation import utils
from evaluation.config import JSON_RESPONSE
from evaluation.models import Access


def evaluate_access(request: HttpRequest):
    """ Evaluates the access table according to the given GET parameters

    Parameters:
    * from: A valid dateTime value. If not provided, all records from the begin of logging will be taken (could be slow!)
    * to: A valid dateTime value. If not provided, all record starting from the 'from' parameter until now will be taken (could be slow!)
    * filter_os: Only take os, which are provided by this parameter. Multiple values can be given, separated by comma
    * filter_browser: Only take browsers which are provided by this parameter. Multiple values can be given, separated by comma
    * only_mobile: If 'True' -> only mobile records will be taken into account (only_desktop must be 'False' or not provided!)
    * only_desktop: If 'True' -> only non-mobile records will be taken into account (only_mobile must be 'False' or not provided!)

    Args:
        request: The incoming request
    Returns:
        A json encoded response
    """
    GET_params = request.GET.dict()

    _from = GET_params.get("from", None)
    _to = GET_params.get("to", None)
    if _from is not None:
        _from = datetime.strptime(_from, "%m/%d/%y %H:%M:%S")
    if _to is not None:
        _to = datetime.strptime(_to, "%m/%d/%y %H:%M:%S")

    filter_os = GET_params.get("filter_os", None)
    filter_browser = GET_params.get("filter_browser", None)

    only_mobile = GET_params.get("only_mobile", 'False')
    only_mobile = utils.resolve_boolean_value(only_mobile)

    only_desktop = GET_params.get("only_desktop", 'False')
    only_desktop = utils.resolve_boolean_value(only_desktop)

    response_data = JSON_RESPONSE

    if only_mobile and only_desktop:
        # Error!
        response_data["msg"] = "'only_mobile' and 'only_desktop' set to true!"
    elif not only_mobile and not only_desktop:
        # get all!
        only_mobile = None
    else:
        # only one of them
        only_mobile = only_mobile or not only_desktop

    try:
        access_list = Access.objects.filter(timestamp__date__gt=_from,
                                            timestamp__date__lt=_to,
                                            os=filter_os,
                                            browser_family=filter_browser,
                                            is_mobile=only_mobile,
                                            )
    except ValueError as e:
        # A parameter might not follow the documentation!
        response_data["msg"] = e.args
    # ToDo: calculate browser usage
    # ToDo: calculate OS usage
    # ToDo: calculate number of different(!) IPs
    # ToDo: calculate number of revisits (average)
    # ToDo: calculate number of total visits

    return JsonResponse(response_data)