from django.shortcuts import render
from django.http import HttpResponse
from pprint import pprint
from django.views.decorators.csrf import csrf_exempt
from useroperations.models import Wfs
import json
import re
import urllib
import requests
import shutil
import os


def index(request):
        return HttpResponse("Hello, world. You're at the polls index.")


@csrf_exempt
def download(request):
    body_unicode = request.body.decode('utf-8')

    wfslist = Wfs.objects.values('wfs_getcapabilities').distinct()
    whitelist = []
    response = ""
    try:
        body = json.loads(body_unicode)
    except ValueError:
        response = HttpResponse('Value error while decoding request body')

    # input validation
    #pprint(body)
    if not re.match('^[0-9]{1,10}$', body['user_id']):
        response = HttpResponse('user_id should be an integer with max 10 digits')

    if not re.match('^\d{13}$', str(body['timestamp'])):
        response = HttpResponse('timestamp should be an integer with 13 digits')

    if not re.match('^[A-Za-z0-9.-_]+$', body['scriptname']):
        response = HttpResponse('scriptname not valid, use A-Z a-z 0-9 . - _')

    if not re.match('^[A-Za-z0-9-]+$', body['uuid']):
        response = HttpResponse('uuid not valid, use A-Z a-z 0-9 -')

    if response is "":

        for url in wfslist:
            parsed = urllib.parse.urlparse(url['wfs_getcapabilities'])
            host='{uri.scheme}://{uri.netloc}/'.format(uri=parsed)
            whitelist.append(host)

        os.mkdir("/tmp/"+body['uuid'])

        for id,url in enumerate(body['urls']):
            parsed = urllib.parse.urlparse(urllib.parse.unquote(url))
            host = '{uri.scheme}://{uri.netloc}/'.format(uri=parsed)

            if host not in whitelist:
                print("HOST NOT IN WHITELIST!")
                exit()

            response = requests.get(urllib.parse.unquote(url), stream=True)
            with open("/tmp/" + body['uuid']+ '/' + body['names'][id].replace('/', '-'), 'wb') as out_file:
                shutil.copyfileobj(response.raw, out_file)
            del response

        shutil.make_archive('/tmp/InspireDownload_'+body['uuid'], 'zip', '/tmp/' + body['uuid'])
        shutil.rmtree("/tmp/" + body['uuid'])

        response = HttpResponse('/tmp/InspireDownload_'+body['uuid'], content_type='application/zip')
        response['Content-Disposition'] = 'attachment; filename='+'/tmp/InspireDownload_'+body['uuid']+'.zip'

    return response

    #download('/tmp/InspireDownload'+body['uuid'])
    #return HttpResponse("Hello, world. You're at the polls index.")