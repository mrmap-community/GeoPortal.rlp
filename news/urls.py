from django.urls import path
from .views import *

app_name = "news"
urlpatterns = [
    path('twitter/', twitter_view, name='twitter_view'),
]