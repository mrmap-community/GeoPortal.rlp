"""

Author: Michel Peltriaux
Organization: Spatial data infrastructure Rheinland-Pfalz, Germany
Contact: michel.peltriaux@vermkv.rlp.de
Created on: 22.01.19

"""
from django.urls import path, include
from .views import *

app_name = "searchCatalogue"

urlpatterns = [
    path("", index, name="index"),
    path("external/", index_external, name="index_external"),
    path("autocompletion/", auto_completion, name="auto_completion"),
    path("search/", get_data, name="get_data"),
    # Ajax calls
    path("permission-email/", get_permission_email_form, name="get_permission_email_form"),
    path("send-permission-email/", send_permission_email, name="send_permission_email"),
    path("terms-of-use/", terms_of_use, name="terms_of_use"),
]