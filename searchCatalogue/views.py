"""

Author: Michel Peltriaux
Organization: Spatial data infrastructure Rheinland-Pfalz, Germany
Contact: michel.peltriaux@vermkv.rlp.de
Created on: 22.01.19

"""
import json
import smtplib
import time
import logging
from collections import OrderedDict

from django.core.mail import send_mail
from django.http import HttpRequest, JsonResponse
from django.shortcuts import render
from django.template.loader import render_to_string
from django.utils import translation
from django_extensions import settings
from django.utils.translation import gettext as _

from Geoportal import helper
from Geoportal.decorator import check_browser
from Geoportal.geoportalObjects import GeoportalJsonResponse, GeoportalContext
from Geoportal.helper import write_gml_to_session, print_debug
from Geoportal.settings import DE_CATALOGUE, EU_CATALOGUE, PRIMARY_CATALOGUE, PRIMARY_SRC_IMG, DE_SRC_IMG, \
    EU_SRC_IMG, OPEN_DATA_URL, HOSTNAME, HTTP_OR_SSL
from searchCatalogue.utils import viewHelper
from searchCatalogue.utils.autoCompleter import AutoCompleter
from searchCatalogue.utils.rehasher import Rehasher
from searchCatalogue.utils.searcher import Searcher
from useroperations.models import MbUser

EXEC_TIME_PRINT = "Exec time for %s: %1.5fs"

app_name = ""

logger = logging.getLogger(__name__)

@check_browser
def index_external(request: HttpRequest):
    """ Renders the index template for external embedded calls.

    This route is for external embedded calls in iFrames and so on.
    The template provides an own searchbar, which is not necessary on the geoportal homepage.

    Args:
        request (HttpRequest): The incoming request
    Returns:
        Redirect: Redirect to the real render functionality with a flag for external_call
    """
    external_call = True
    params_get = request.GET
    start_search = helper.resolve_boolean_value(params_get.get("start", "False"))

    return index(request=request, external_call=external_call, start_search=start_search)

@check_browser
def index(request: HttpRequest, external_call=False, start_search=False):
    """ Renders the index template for all calls.

    If the external_call flag is set to True, this function will change the template to be rendered.

    Args:
        request (HttpRequest): The incoming request
        external_call: A flag that indicates if the call comes from an external source
    Returns:
        The rendered page
    """
    language_cookie = request.COOKIES.get("django_language", None)
    if language_cookie is None:
        # user needs to see the default language!
        default_language = "de"
        translation.activate(default_language)
        request.LANGUAGE_CODE = translation.get_language()
    #template_name = "search_forms.html"    # comment this in to enable extended search
    template_name = "index.html"            # comment this out if you comment the upper line in
    get_params = request.GET.dict()
    searcher = Searcher()
    facets = searcher.get_categories_list()
    preselected_facets = viewHelper.get_preselected_facets(get_params, facets)

    sources = viewHelper.get_source_catalogues(external_call)

    params = {
        "title": _("Search"),
        "basedir": settings.BASE_DIR,
        "sources": sources,
        "value_form_map": "",
        "value_form": "",
        "value_form_map_as_json": "",
        "selected_facets": preselected_facets,
        "external_call": external_call,
        "start_search": start_search,
    }
    geoportal_context = GeoportalContext(request=request)
    geoportal_context.add_context(params)

    if external_call:
        geoportal_context.add_context(context={"extended_template": "none.html"})
    else:
        geoportal_context.add_context(context={"extended_template": "base.html"})

    return render(request, template_name, geoportal_context.get_context())

@check_browser
def auto_completion(request: HttpRequest):
    """ Returns suggestions for searchfield input

    The call comes from an ajax function, therefore we respond using a JsonResponse,
    which can be parsed by ajax.

    Args:
        request (HttpRequest): The incoming request
    Returns:
        JsonResponse: Contains auto-completion suggestions
    """
    max_results = 7

    if request.method == 'POST' and request.POST.dict()["type"] == "autocomplete":
        search_text = request.POST.dict()["terms"]
        # clean for UMLAUTE!
        search_text = search_text.replace("ö", "oe")
        search_text = search_text.replace("Ö", "Oe")
        search_text = search_text.replace("ä", "ae")
        search_text = search_text.replace("Ä", "Ae")
        search_text = search_text.replace("ü", "ue")
        search_text = search_text.replace("U", "Ue")
        search_text = search_text.replace("ß", "ss")

        auto_completer = AutoCompleter(search_text, max_results)
        results = auto_completer.get_auto_completion_suggestions()
    elif request.method == 'GET':
        # This is for debugging
        search_text = "Koblenz"
        auto_completer = AutoCompleter(search_text, max_results)
        results = auto_completer.get_auto_completion_suggestions()
    else:
        results = None

    return GeoportalJsonResponse(results=results["results"], resultList=results["resultList"]).get_response()

@check_browser
def get_data(request: HttpRequest):
    """ Redistributor for general get_data requests.

    Decides which kind of data needs to be fetched and redirects to the according view.

    Args:
        request (HttpRequest): The incoming request
    Returns:
        JsonResponse: If nothing was found, an empty JsonResponse will be returned to reduce the harm
        Redirects otherwise to working functions.
    """
    if not request.is_ajax():
        return GeoportalJsonResponse().get_response()

    post_params = request.POST.dict()
    # Check if spatial search is required
    spatial = post_params.get("spatial", "") == "true"
    search_box = post_params.get("searchBbox", "")
    if spatial is not None:
        if spatial and search_box == '':
            # spatial is selected but there are no search_bbox parameters yet -> A spatial search result was not selected yet -> Show them!
            return get_spatial_results(request)

    # Check which source is requested
    source = post_params.get("source", None)
    if source is not None:
        if source == "primary":
            # call primary search method
            return get_data_primary(request)
        elif source == "de":
            # call other search method
            return get_data_other(request, catalogue_id=DE_CATALOGUE)
        elif source == "eu":
            # call other search method
            return get_data_other(request, catalogue_id=EU_CATALOGUE)
        elif source == "info":
            # call info search method
            return get_data_info(request)
        else:
            return GeoportalJsonResponse().get_response()
    else:
        return GeoportalJsonResponse().get_response()

@check_browser
def get_spatial_results(request: HttpRequest):
    """ Returns the data for a spatial search.

    Args:
        request (HttpRequest): The incoming request
    Returns:
        JsonResponse: Contains the content for the ajax call
    """
    template = app_name + "spatial/spatial_search_results.html"
    post_params = request.POST.dict()
    search_text = post_params.get("terms").split(",")

    searcher = Searcher()
    spatial_data = searcher.get_spatial_data(search_text)
    spatial_data = viewHelper.prepare_spatial_data(spatial_data)

    view_content = render_to_string(template, spatial_data)

    return GeoportalJsonResponse(html=view_content).get_response()

@check_browser
def get_data_other(request: HttpRequest, catalogue_id):
    """ Returns data for other search catalogues than the primary.

    Args:
        request (HttpRequest): The incoming request
        catalogue_id: Specifies which catalogue (API) shall be used
    Returns:
        JsonResponse: Contains the content for the ajax call
    """
    post_params = request.POST.dict()
    template_name = app_name + "search_results.html"
    host = request.META.get("HTTP_HOST")

    # extract parameters
    start_time = time.time()
    search_words = post_params.get("terms")
    is_eu_search = False
    is_de_search = True

    search_pages = int(post_params.get("page-geoportal"))
    requested_page_res = post_params.get("data-geoportal")
    requested_resources = viewHelper.prepare_requested_resources(post_params.get("resources"))
    source = post_params.get("source", "")
    if source == 'eu':
        is_eu_search = True
        is_de_search = False
        all_resources = {
            "dataset": _("Datasets"),
            "series": _("Series"),
            "service": _("Services"),
        }
    else:
        all_resources = {
            "dataset": _("Datasets"),
            "series": _("Series"),
            "service": _("Services"),
            "application": _("Applications"),
            "nonGeographicDataset": _("Miscellaneous Datasets"),
        }

    print_debug(EXEC_TIME_PRINT % ("extracting parameters", time.time() - start_time))

    # run search DE
    searcher = Searcher(page_res=requested_page_res,
                        keywords=search_words,
                        page=search_pages,
                        resource_set=requested_resources,
                        language_code=request.LANGUAGE_CODE,
                        catalogue_id=catalogue_id,
			host=host,
                        )
    start_time = time.time()
    search_results = searcher.get_search_results_de()
    print_debug(EXEC_TIME_PRINT % ("total search in catalogue with ID " + str(catalogue_id), time.time() - start_time))

    # split used searchFilters from searchResults
    search_filters = {}
    for resource_key, resource_val in search_results.items():
        if len(search_filters) == 0:
            search_filters = resource_val["searchFilter"]

    start_time = time.time()
    # prepare pages to render for all resources
    pages = viewHelper.calculate_pages_to_render_de(search_results, search_pages, requested_page_res)
    print_debug(EXEC_TIME_PRINT % ("calculating pages to render", time.time() - start_time))

    # ONLY FOR EU
    if is_eu_search:
        start_time = time.time()
        # hash inspire id, so we can use them in a better way with javascript
        search_results = viewHelper.hash_inspire_ids(search_results)
        print_debug(EXEC_TIME_PRINT % ("hash inspire ids", time.time() - start_time))

    start_time = time.time()
    # prepare preview images
    search_results = viewHelper.check_previewUrls(search_results)
    print_debug(EXEC_TIME_PRINT % ("checking previewUrls", time.time() - start_time))


    results = {
        "source": source,
        "search_results": search_results,
        "search_filters": search_filters,
        "is_de_search": is_de_search,
        "is_eu_search": is_eu_search,
        "resources": requested_resources,
        "pages": pages,
        "all_resources": all_resources,
        "OPEN_DATA_URL": OPEN_DATA_URL,
        "sources": viewHelper.get_source_catalogues(False)
    }
    # since we need to return plain text to the ajax handler, we need to use render_to_string
    start_time = time.time()
    view_content = render_to_string(template_name, results)
    print_debug(EXEC_TIME_PRINT % ("rendering view", time.time() - start_time))

    return GeoportalJsonResponse(resources=requested_resources, html=view_content).get_response()

@check_browser
def get_data_primary(request: HttpRequest):
    """ Returns data for the primary search catalogue

    Args:
        request (HttpRequest): The incoming request
    Returns:
        JsonResponse: Contains data for the ajax call
    """
    post_params = request.POST.dict()
    host = request.META.get("HTTP_HOST")
    template_name = app_name + "search_results.html"
    resources = {
        "dataset": _("Datasets"),
        "wms": _("Web Map Services"),
        "wfs": _("Search-, Download-,Gathering-modules"),
        "wmc": _("Map Combinations"),
    }
    lang_code = request.LANGUAGE_CODE

    # get user php session info
    session_data = helper.get_mb_user_session_data(request)

    # prepare bbox parameter
    search_bbox = post_params.get("searchBbox", "")
    search_type_bbox = post_params.get("searchTypeBbox", "")

    # prepare order parameter
    order_by = post_params.get("orderBy")

    # prepare selected facets for rendering
    selected_facets = post_params.get("facet").split(";")

    start_time = time.time()
    # prepare extended search parameters
    extended_search_params = viewHelper.parse_extended_params(post_params)
    selected_facets = viewHelper.prepare_selected_facets(selected_facets)
    print_debug(EXEC_TIME_PRINT % ("prepare extended search params", float(time.time() - start_time)))

    # prepare search tags (keywords)
    keywords = post_params["terms"].split(",")

    # prepare requeste resources to be an array of strings
    # requested_resources: str
    requested_resources = viewHelper.prepare_requested_resources(post_params["resources"])

    # get requested page and for which resource it is requested
    requested_page = int(post_params["page-geoportal"])
    requested_page_res = post_params["data-geoportal"]

    # get data source (rlp, other, ...)
    source = post_params.get("source", "")

    # get open data info
    only_open_data = post_params.get("onlyOpenData", 'false')

    start_time = time.time()
    # run search
    catalogue_id = PRIMARY_CATALOGUE
    searcher = Searcher(",".join(keywords),
                        requested_resources,
                        extended_search_params,
                        requested_page,
                        requested_page_res,
                        selected_facets,
                        order_by,
                        search_bbox,
                        search_type_bbox,
                        only_open_data=only_open_data,
                        language_code=lang_code,
                        catalogue_id=catalogue_id,
			host=host)
    search_results = searcher.get_search_results_primary(user_id=session_data.get("userid", ""))
    print_debug(EXEC_TIME_PRINT % ("total search in catalogue with ID " + str(catalogue_id), time.time() - start_time))

    # prepare search filters
    search_filters = viewHelper.get_search_filters(search_results)

    start_time = time.time()
    # rehash facets
    rehasher = Rehasher(search_results, search_filters)
    facets = rehasher.get_rehashed_categories()
    # set flag to indicate that the facet is one of the selected
    for facet_key, facet_val in selected_facets.items():
        for chosen_facet in facet_val:
            _id = chosen_facet["id"]
            for facet in facets[facet_key]:
                if facet["id"] == _id:
                    facet["is_selected"] = True
                    break
    search_filters = rehasher.get_rehashed_filters()
    del rehasher
    print_debug(EXEC_TIME_PRINT % ("rehashing of facets", time.time() - start_time))

    start_time = time.time()
    # prepare pages to render for all resources
    pages = viewHelper.calculate_pages_to_render(search_results, requested_page, requested_page_res)
    print_debug(EXEC_TIME_PRINT % ("calculating pages to render", time.time() - start_time))

    start_time = time.time()
    # generate inspire feed urls
    search_results = viewHelper.gen_inspire_url(search_results)
    print_debug(EXEC_TIME_PRINT % ("preparing inspire urls", time.time() - start_time))

    start_time = time.time()
    # generate extent graphics url
    search_results = viewHelper.gen_extent_graphic_url(search_results)
    print_debug(EXEC_TIME_PRINT % ("generating extent graphic urls", time.time() - start_time))

    start_time = time.time()
    # set attributes for wfs child modules
    search_results = viewHelper.set_children_data_wfs(search_results)
    print_debug(EXEC_TIME_PRINT % ("setting wfs children data", time.time() - start_time))

    start_time = time.time()
    # set state icon file paths
    search_results = viewHelper.set_iso3166_icon_path(search_results)
    print_debug(EXEC_TIME_PRINT % ("setting iso3166 icons", time.time() - start_time))

    # check for bounding box
    bbox = post_params.get("searchBbox", '')
    if bbox != '':
        # set glm to session
        session_id = request.COOKIES.get("PHPSESSID", "")
        lat_lon = bbox.split(",")
        lat_lon = {
            "minx": lat_lon[0],
            "miny": lat_lon[1],
            "maxx": lat_lon[2],
            "maxy": lat_lon[3],
        }
        write_gml_to_session(session_id=session_id, lat_lon=lat_lon)


    # prepare data for rendering
    types = {
        'Suchbegriff(e):': 'searchText',
        'INSPIRE Themen:': 'inspireThemes',
        'ISO Kategorien:': 'isoCategories',
        'RP Kategorien:': 'customCategories',
        'Räumliche Einschränkung:': 'searchBbox',
        'Anbietende Stelle(n):': 'registratingDepartments',
        'Registrierung/Aktualisierung von:': 'regTimeBegin',
        'Registrierung/Aktualisierung bis:': 'regTimeEnd',
        'Datenaktualität von:': 'timeBegin',
        'Datenaktualität bis:': 'timeEnd',
    }
    results = {
        "user": session_data.get("user", ""),
        "userid": session_data.get("userid", ""),
        "loggedin": session_data.get("loggedin", ""),
        "source": source,
        "types": types,
        "keywords": keywords,
        "all_resources": resources,
        "resources": requested_resources,
        "search_results": search_results,
        "search_filters": search_filters,
        "facets": facets,
        "show_facets_count": 5,
        "selected_facets": selected_facets,
        "pages": pages,
        "download_url": host + "/mapbender/php/mod_getDownloadOptions.php?id=",
        "download_feed_url": host + "/mapbender/plugins/mb_downloadFeedClient.php?url=",
        "download_feed_inspire": host + "/mapbender/php/mod_inspireDownloadFeed.php?id=",
        "view_map_url": "//localhost/portal/karten.html?",
        "wms_action_url": HTTP_OR_SSL + HOSTNAME + "/mapbender/php/wms.php?",
        "OPEN_DATA_URL": OPEN_DATA_URL,
        "sources": viewHelper.get_source_catalogues(False)
    }

    # since we need to return plain text to the ajax handler, we need to use render_to_string
    start_time = time.time()
    view_content = render_to_string(template_name, results)
    print_debug(EXEC_TIME_PRINT % ("rendering view", time.time() - start_time))

    return GeoportalJsonResponse(resources=requested_resources, html=view_content).get_response()

@check_browser
def get_data_info(request: HttpRequest):
    """ Searches for results in the mediawiki

    THIS IS A FEATURE THAT ISN'T IMPLEMENTED YET

    Args:
        request (HttpRequest): The incoming request
    Returns:
        JsonResponse: Contains data for the ajax call
    """
    post_params = request.POST.dict()
    template_name = "search_results.html"
    host = HTTP_OR_SSL + HOSTNAME
    # get language
    lang = request.LANGUAGE_CODE

    # prepare search tags (keywords)
    keywords = post_params["terms"].split(",")
    list_all = False
    if len(keywords) == 1 and keywords[0] == '' or keywords[0] == '*':
        keywords = ["*"]
        list_all = True

    searcher = Searcher(keywords=keywords,
                        language_code=lang)
    if list_all:
        search_results = searcher.get_info_all_pages()
    else:
        search_results = searcher.get_info_search_results()
    search_results = viewHelper.prepare_info_search_results(search_results, list_all, lang)
    search_results = viewHelper.resolve_internal_external_info(search_results, searcher)
    # calculate number of all info hits
    nresults = 0
    for res_val in search_results.values():
        nresults += len(res_val)

    params = {
        "lang": lang,
        "list_all": list_all,
        "HOSTNAME": host,
        "search_results": search_results,
        "is_info_search": True,
        "source": "info",
        "sources": viewHelper.get_source_catalogues(False),
    }
    # since we need to return plain text to the ajax handler, we need to use render_to_string
    #start_time = time.time()
    view_content = render_to_string(template_name, params)
    #print_debug(EXEC_TIME_PRINT % ("rendering view", time.time() - start_time))

    return GeoportalJsonResponse(html=view_content, nresults=nresults).get_response()

@check_browser
def get_permission_email_form(request: HttpRequest):
    """ Returns rendered email permission template.

    Reacts on an ajax call and renders an email form for requesting access permission to a specific resource.

    Args:
        request (HttpRequest): The incoming request
    Returns:
        JsonResponse: Contains the prerendered html for the form
    """
    template = "permission_email_form.html"
    params_GET = request.GET.dict()
    session_data = helper.get_mb_user_session_data(request)
    user = session_data.get("user", "")
    mb_user = MbUser.objects.get(mb_user_name=user)
    mb_user_mail = mb_user.mb_user_email
    data_id = params_GET.get("layerId")
    data_name = params_GET.get("layerName")
    title = _("Send permission request")
    subject = _("[Geoportal.RLP] Permission request for ") + str(data_id)
    draft = _("Please give me permission to view the resource \n'") + data_name +\
            _("'\n It has the ID ") + str(data_id) +\
            _(".\n\n Thank you very much\n\n") +\
            user + "\n" +\
            mb_user_mail
    params = {
        "data_provider": params_GET.get("dataProvider", ""),
        "subject": subject,
        "title": title,
        "draft": draft,
    }
    html = render_to_string(template_name=template, context=params, request=request)

    return GeoportalJsonResponse(html=html).get_response()

@check_browser
def send_permission_email(request: HttpRequest):
    """ Sends a permission email

    Args:
        request (HttpRequest): The incoming request
    Returns:
        JsonResponse: Contains the success/fail status
    """
    params_GET = request.GET.dict()
    address = params_GET.get("address", None)
    subject = params_GET.get("subject", None)
    message = params_GET.get("message", None)
    success = True

    try:
        send_mail(
            subject=subject,
            message=message,
            from_email="",  # ToDo: Insert webserver mailer
            recipient_list=[address],
            fail_silently=False
        )
    except smtplib.SMTPException:
        logger.error("Could not send mail: " + subject + ", to " + address)
        success = False

    return GeoportalJsonResponse(success=success).get_response()

@check_browser
def terms_of_use(request: HttpRequest):
    """ Fetches the terms of use for a specific search result

    Args:
        request (HttpRequest): The incoming request
    Returns:
        JsonResponse: Contains the required data
    """
    html = ""
    params_GET = request.GET.dict()
    lang_code = request.LANGUAGE_CODE
    href = params_GET.get("href")
    id = params_GET.get("id")
    resource = params_GET.get("resourceType")
    if resource == "dataset":
        resource = "wms"
    if resource == "wmc" or resource == "other-catalogue":
        # wmc has no disclaimer
        return GeoportalJsonResponse(html=html).get_response()

    html = viewHelper.generic_srv_disclaimer(resource=resource, service_id=id, language=lang_code)

    if len(html) > 0:
        template = "terms_of_use.html"
        params = {
            "content": html,
            "href": href
        }

        html = render_to_string(template_name=template, context=params)
    return GeoportalJsonResponse(html=html).get_response()

@check_browser
def write_gml_session(request: HttpRequest):
    params_GET = request.GET.dict()
    lat_lon = params_GET.get("latLon", "{}")
    lat_lon = json.loads(lat_lon)
    session_id = request.COOKIES.get("sessionid", "")
    write_gml_to_session(lat_lon=lat_lon, session_id=session_id)
