from django.shortcuts import render
from django.http import HttpResponse, HttpResponseBadRequest
from pprint import pprint
from django.views.decorators.csrf import csrf_exempt
from useroperations.models import Wfs, Wms, InspireDownloads, Layer, WfsFeaturetype
from searchCatalogue.settings import PROXIES
from django.core.mail import send_mail
from Geoportal.settings import HOSTNAME, INTERNAL_SSL, HTTP_OR_SSL, DEFAULT_FROM_EMAIL, INSPIRE_ATOM_DIR, INSPIRE_ATOM_ALIAS
from django.utils.translation import gettext as _
from email.utils import parseaddr
import json
import re
import urllib
import requests
import shutil
import os

@csrf_exempt
def download(request):

    if request.META['HTTP_HOST'] not in ["127.0.0.1","localhost",HOSTNAME]:
        return HttpResponse('Only internal requests!')

    #body = (json.loads(request.body.decode('utf-8')))

    # this needs to be ascii because then open function in line 104
    # somehow uses ascii by default which is not changeable in binary mode (wb)
    body_decoded = request.body.decode('ascii','ignore')

    download_request = InspireDownloads()
    whitelist = []
    response = ""
    message = ""
    download = ""
    numURLs = 0
    secured = 0

    try:
        body = json.loads(body_decoded)
    except ValueError:
        return HttpResponse('Value error while decoding request body')

    # input validation
    if not re.match('^[0-9]{1,10}$', body['user_id']):
        return HttpResponseBadRequest('user_id should be an integer with max 10 digits')

    if not parseaddr(body['user_email']):
        return HttpResponseBadRequest('email not valid')

    if not re.match('^\d{13}$', str(body['timestamp'])):
        return HttpResponseBadRequest('timestamp should be an integer with 13 digits')

    if not re.match('^[A-Za-z0-9.-_]+$', body['scriptname']):
        return HttpResponseBadRequest('scriptname not valid, use A-Z a-z 0-9 . - _')

    if not re.match('^[A-Za-z0-9-]+$', body['uuid']):
        return HttpResponseBadRequest('uuid not valid, use A-Z a-z 0-9 -')

    if not re.match('^[A-Za-z0-9-]+$', body['session_id']):
        return HttpResponseBadRequest('sessionid not valid, use A-Z a-z 0-9 -')
    
    if len(body['urls']) > 20:
        return HttpResponse('Max 20 tiles allowed', status=409)

    

    downloadurl = urllib.parse.urlparse(urllib.parse.unquote(body['urls'][0]))
    #print(downloadurl)
    #print(downloadurl.hostname)
    #print(downloadurl.query)


    #check if it is an internal server, if so only internal email address will have access
    if re.match('.*\.rlp$', downloadurl.hostname):
        #print("rlp")
        if not re.match('.*\.rlp.de$',body['user_email'].split("@")[1]):
            #print("no rlp email")
            return HttpResponse('User is not allowed to access this ressource',status=403)
    #else:
        #print("not rlp")


    # check if user is allowed to access layer
    refererparams = urllib.parse.parse_qs(urllib.parse.unquote(request.META['HTTP_REFERER']))
    resourceType = refererparams["generateFrom"][0] # wmlayer = layer ; metadata = featuretype
    #print(refererparams)
    #print(resourceType)
    #ressource_id = refererparams["ressource_id"][0]
    #resourceType = refererparams["generateFrom"][0] # wmlayer = layer ; metadata = featuretype

    if resourceType == "wmslayer":
        resourceType="layer"
        ressource_id = refererparams["layerid"][0]
        service_id = Layer.objects.get(layer_id=ressource_id).fkey_wms_id
        secured_service_hash = Wms.objects.get(wms_id=service_id).wms_owsproxy
        #print(ressource_id)
        #print(service_id)
    elif resourceType == "wfs":
        resourceType="featuretype"
        wfs_id = refererparams["wfsid"][0]
        ressource_id = WfsFeaturetype.objects.get(fkey_wfs_id=wfs_id).featuretype_id
        secured_service_hash = Wfs.objects.get(wfs_id=wfs_id).wfs_owsproxy
        #print(ressource_id)
    else:
        return HttpResponse('No security hash for service found',status=500)

    #print(secured_service_hash)

    permission = requests.get(HTTP_OR_SSL + '127.0.0.1/mapbender/php/mod_permissionWrapper.php?userId='+body['user_id']+'&resourceType='+resourceType+'&resourceId='+str(ressource_id), verify=INTERNAL_SSL)
    permission = json.loads(permission.text)

    #print(permission)
    if secured_service_hash != "":
        if permission["result"] != True:
            return HttpResponse('User is not allowed to access this ressource',status=403)
        else:
            secured=1

    # build whitelist
    wfslist = Wfs.objects.values('wfs_getfeature').distinct()
    wmslist = Wms.objects.values('wms_getmap').distinct()
    for url in wfslist:
        parsed = urllib.parse.urlparse(url['wfs_getfeature'])
        host = '{uri.scheme}://{uri.netloc}/'.format(uri=parsed)
        whitelist.append(host)

    for url in wmslist:
        parsed = urllib.parse.urlparse(url['wms_getmap'])
        host = '{uri.scheme}://{uri.netloc}/'.format(uri=parsed)
        whitelist.append(host)

    #check whitelist
    for id,url in enumerate(body['urls']):
        numURLs = numURLs + 1
        parsed = urllib.parse.urlparse(urllib.parse.unquote(url))
        host = '{uri.scheme}://{uri.netloc}/'.format(uri=parsed)
        if host not in whitelist:
            return HttpResponse("host not in whitelist",status=418)

    # check if directory has space left
    disk = shutil.disk_usage(INSPIRE_ATOM_DIR)

    if "image" in body['urls'][0]:
        format = ".tiff"
    else:
        format = ""

    if format == ".tiff":
        if disk.free < numURLs * 60000000:
            return HttpResponse("No space left please try again later!",status=400)
    else:
        if disk.free < numURLs * 10000000:
            return HttpResponse("No space left please try again later!",status=400)

    # download and send email


    os.mkdir(INSPIRE_ATOM_DIR + body['uuid'])

    for id, url in enumerate(body['urls']):
        if "/" in body['names'][id]:
            body['names'][id] = body['names'][id].replace("/", "-")

        if secured == 0:
            download = requests.get(urllib.parse.unquote(url), stream=True, proxies=PROXIES, verify=False)
        elif secured == 1:
            query = urllib.parse.urlparse(urllib.parse.unquote(url)).query
            # transform url to local owsproxy http://localhost/owsproxy/{sessionid}/{securityhash}?{request}
            new_url = "https://www.geoportal.rlp.de/owsproxy/"+body['session_id']+"/"+secured_service_hash+"?"+query
            #print(urllib.parse.urlparse(urllib.parse.unquote(url)).query)
            #print(new_url)
            download = requests.get(new_url, stream=True, proxies=None, verify=False)
        else:
            return HttpResponse("Something went wrong, please contact an Admin",status=500)

        with open(INSPIRE_ATOM_DIR + body['uuid'] + '/' + body['names'][id] + format, mode='wb') as out_file:
            shutil.copyfileobj(download.raw, out_file)
        del download

    shutil.make_archive(INSPIRE_ATOM_DIR + 'InspireDownload_' + body['uuid'], 'zip',
                        INSPIRE_ATOM_DIR + body['uuid'])
    shutil.rmtree(INSPIRE_ATOM_DIR + body['uuid'])

    if body['lang'] == 'de':
        message = "Dies ist Ihre Inspire Download Anfrage! Der Link wird für 24 Stunden gültig sein!" + "\n Link: " \
                  + HTTP_OR_SSL + HOSTNAME + INSPIRE_ATOM_ALIAS + 'InspireDownload_' + body['uuid'] + '.zip'
    else:
        message = "This is your Inspire Download request! The Link will be valid  for 24 hours!" + "\n Link: " \
                  + HTTP_OR_SSL + HOSTNAME + INSPIRE_ATOM_ALIAS + 'InspireDownload_' + body['uuid'] + '.zip'

    download_request.user_id = body['user_id']
    download_request.user_email = body['user_email']
    download_request.service_name = body['names'][0].split("Teil",1)[0]
    download_request.no_of_tiles = numURLs
    download_request.save()


    send_mail(
        _("Inspire Download"),
        _("Hello ") + body['user_name'] +
        ", \n \n" +
        message,
        DEFAULT_FROM_EMAIL,
        [body['user_email']],
        fail_silently=False,
        )

    return HttpResponse("all done")
