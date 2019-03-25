from django.contrib import admin
from django.urls import path, include , re_path
from .views import *

app_name = "useroperations"
urlpatterns = [
    path('feedback/', feedback_view, name='feedback'),
    path('map-viewer/', map_viewer_view, name='map_viewer'),
    path('login/', login_view, name='login'),
    path('register/', register_view, name='register'),
    path('password_reset/', pw_reset_view, name='pw_reset'),
    path('change_profile/', change_profile_view, name='change_profile'),
    path('delete_profile/', delete_profile_view, name='delete_profile'),
    path('logout/', logout_view, name='logout'),
    path('article/<slug:wiki_keyword>/', index_view, name='index'),
    path('activate/<slug:activation_key>', activation_view, name='activation'),
    path('viewer/', index_view, name='index'),
    path('', index_view, name='index'),
]