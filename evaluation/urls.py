"""
Author: Michel Peltriaux
Organization: Spatial data infrastructure Rhineland-Palatinate, Germany
Contact: michel.peltriaux@vermkv.rlp.de
Created on: 01.07.19

"""
from django.urls import path

from evaluation.views import evaluate_access

app_name = "eval"
urlpatterns = [
    #path("access/", evaluate_access, name="eval_access"),  # Not ready for deployment, yet!
]
