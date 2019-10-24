import threading
import random
import string
import ssl

from urllib import request

import bcrypt
from lxml import html
from Geoportal.settings import HOSTNAME, HOSTIP, HTTP_OR_SSL
from searchCatalogue.utils.searcher import Searcher
from useroperations.models import MbUser


def random_string(stringLength=15):
    letters = string.ascii_lowercase
    return ''.join(random.choice(letters) for i in range(stringLength))



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
    searcher = Searcher(keywords="", result_target="", resource_set=["wmc"], page=1, order_by="rank", host=HOSTNAME, max_results=10)
    search_results = searcher.get_search_results_primary()
    ret_dict["wmc"] = search_results.get("wmc", {}).get("wmc", {}).get("srv", [])

    # get number of wmc's
    ret_dict["num_wmc"] = search_results.get("wmc", {}).get("wmc", {}).get("md", {}).get("nresults")

    # get number of organizations
    ret_dict["num_orgs"] = len(get_all_organizations())

    # get number of applications
    ret_dict["num_apps"] = len(get_all_applications())

    # get number of topics
    ret_dict["num_topics"] = len(get_all_inspire_topics(lang).get("tags", []))

    # get number of datasets and layers
    tmp = {
        "dataset": "num_dataset",
        "wms": "num_wms",
    }
    for key, val in tmp.items():
        searcher = Searcher(keywords="", result_target="", resource_set=[key], host=HOSTNAME)
        search_results = searcher.get_search_results_primary()
        ret_dict[val] = search_results.get(key, {}).get(key, {}).get("md", {}).get("nresults")

    return ret_dict


def get_all_organizations():
    """ Returns a list of all data publishing organizations

    Returns:
         A list of all organizations which publish data
    """
    searcher = Searcher(keywords="", resource_set=["wmc"], page=1, order_by="rank", host=HOSTNAME, max_results=1000)

    return searcher.get_all_organizations()


def get_all_applications():
    """ Returns a list of all available applications

    Returns:
         A list of all applications
    """
    searcher = Searcher(keywords="", resource_set=["application"], host=HOSTNAME)
    return searcher.get_search_results_primary()["application"]["application"]["application"]["srv"]


def get_all_inspire_topics(language):
    """ Returns a list of all inspire topics available

    Returns:
         A list of all organizations which publish data
    """
    searcher = Searcher(keywords="", resource_set=["wmc"], page=1, order_by="rank", host=HOSTNAME)

    return searcher.get_all_topics(language)


def bcrypt_password(pw: str, user: MbUser):
    """ Encrypts the given password using a user salt.

    Needed for checking if a given password matches a user's password

    Args:
        pw (str): The given password
        user (MbUser): The MbUser object
    Returns:
         The encrypted password string
    """
    return (str(bcrypt.hashpw(pw.encode('utf-8'), user.password.encode('utf-8')), 'utf-8'))