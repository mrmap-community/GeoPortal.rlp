import threading
from urllib import request


from lxml import html
from pymemcache.client import base
from phpserialize import *
import re, os, configparser

from Geoportal.settings import HOSTNAME, HOSTIP, HTTP_OR_SSL, PROJECT_DIR
from searchCatalogue.utils.searcher import Searcher
import random
import string
import ssl


def random_string(stringLength=15):
    letters = string.ascii_lowercase
    return ''.join(random.choice(letters) for i in range(stringLength))

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


def get_mapbender_session_by_file(session_id):

    try:
        f = open("/var/lib/php/sessions/sess_" + session_id, "r")
        session_data = f.read()
    except FileNotFoundError:
        session_data = None


    try:
        session_data = unserialize(session_data.encode('utf-8'))
    except ValueError:
        session_data = None
    except AttributeError:
        session_data = None

    return session_data

def delete_mapbender_session_by_file(session_id):

    os.remove("/var/lib/php/sessions/sess_" + session_id)


def get_mapbender_config_value(value):
    define_pattern = re.compile(r"""\bdefine\(\s*('|")(.*)\1\s*,\s*('|")(.*)\3\)\s*;""")
    assign_pattern = re.compile(r"""(^|;)\s*\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*=\s*('|")(.*)\3\s*;""")

    php_vars = {}
    for line in open(PROJECT_DIR + "mapbender/conf/mapbender.conf", encoding="utf-8"):
        for match in define_pattern.finditer(line):
            php_vars[match.group(2)] = match.group(4)
        for match in assign_pattern.finditer(line):
            php_vars[match.group(2)] = match.group(4)

    return php_vars[value]

def get_php_config_value(section,value):
    config = configparser.ConfigParser()
    config.read("/etc/php/7.0/apache2/php.ini")
    value = config[section][value]
    return value


def __set_tag(dom, tag, attribute, prefix):
    """ Checks the DOM for a special tag and changes the attribute according to the provided value

    :param dom:
    :param tag:
    :param attribute:
    :param value:
    :return:
    """
    protocol = "http"
    searcher = Searcher()
    _list = dom.cssselect(tag)
    for elem in _list:
        attrib = elem.get(attribute)
        if tag == 'a':
            # check if the page we want to go to is an internal or external page
            title = attrib.split("/")
            title = title[len(title) - 1]
            if searcher.is_article_internal(title):
                attrib = "/article/" + title
        if protocol not in attrib:
            elem.set(attribute, prefix + attrib)

def set_links_in_dom(dom):
    """ Since the wiki (where the DOM comes from) is currently(!!!) not on the same machine as the Geoportal, we need to change all links to the machine where the wiki lives

    :param dom:
    :return:
    """
    prefix = HTTP_OR_SSL + HOSTNAME

    # handle links
    thread_list = []
    thread_list.append(threading.Thread(target=__set_tag, args=(dom, "a", "href", prefix)))
    thread_list.append(threading.Thread(target=__set_tag, args=(dom, "img", "src", prefix)))
    __execute_threads(thread_list)


def __execute_threads(thread_list):
    """ Executes a given list of threads

    :param thread_list:
    :return:
    """
    for thread in thread_list:
        thread.start()
    for thread in thread_list:
        thread.join()

def get_wiki_body_content(wiki_keyword, lang, category=None):
    """ Returns the HTML body content of the corresponding mediawiki page

    Args:
        wiki_keyword (str): A keyword that matches a mediawiki article title
        lang (str): The currently selected language
        category (str): A filter for internal or external categories
    Returns:
        str: The html content of the wiki article
    """
    # get mediawiki html
    url = HTTP_OR_SSL + HOSTIP + "/mediawiki/index.php/" + wiki_keyword + "/" + lang + "#bodyContent"
    html_raw = request.urlopen(url, context=ssl._create_unverified_context())
    html_raw = html_raw.read()
    html_con = html.fromstring(html_raw)
    # get body html div - due to translation module on mediawiki, we need to fetch the parser output
    try:
        body_con = html_con.cssselect(".mw-parser-output")
        if len(body_con) == 1:
            body_con = body_con[0]
    except KeyError:
        return "Error: Check if mediawiki translation package is installed!"
    except TypeError:
        return "Error: mw-parser-output ist not unique"
    # set correct src/link for all <img> and <a> tags
    set_links_in_dom(body_con)
    # render back to html
    return html.tostring(doc=body_con, method='html', encoding='unicode')


def get_landing_page(lang):
    """ Returns the landing page content (favourite wmcs)

    :param lang:
    :return:
    """
    ret_dict = {}
    # get favourite wmcs
    searcher = Searcher(keywords="", resource_set=["wmc"],page=1,order_by="rank",host=HOSTNAME)
    search_results = searcher.get_search_results_primary()
    ret_dict["wmc"] = search_results.get("wmc", {}).get("wmc", {}).get("wmc", {}).get("srv", [])

    return ret_dict


def get_all_organizations():
    """ Returns a list of all data publishing organizations

    Returns:
         A list of all organizations which publish data
    """
    searcher = Searcher(keywords="", resource_set=["wmc"], page=1, order_by="rank", host=HOSTNAME)

    return searcher.get_all_organizations()

def get_all_inspire_topics(language):
    """ Returns a list of all inspire topics available

    Returns:
         A list of all organizations which publish data
    """
    searcher = Searcher(keywords="", resource_set=["wmc"], page=1, order_by="rank", host=HOSTNAME)

    return searcher.get_all_topics(language)
