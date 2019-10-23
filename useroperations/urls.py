from django.contrib import admin
from django.urls import path, include , re_path
from .views import *

app_name = "useroperations"
urlpatterns = [
    path('', index_view, name='index'),
    path('apps/', applications_view, name='apps'),
    path('organizations/', organizations_view, name='organizations'),
    path('categories/', categories_view, name='categories'),
    path('feedback/', feedback_view, name='feedback'),
    path('map-viewer/', map_viewer_view, name='map_viewer'),
    path('map', get_map_view, name='map'),

    path('login/', login_view, name='login'),
    path('register/', register_view, name='register'),
    path('password_reset/', pw_reset_view, name='pw_reset'),
    path('change-profile/', change_profile_view, name='change_profile'),
    path('delete-profile/', delete_profile_view, name='delete_profile'),
    path('logout/', logout_view, name='logout'),
    path('article/<slug:wiki_keyword>/', index_view, name='index'),
    path('activate/<slug:activation_key>', activation_view, name='activation'),
    #path('viewer/', index_view, name='index'),
    path('incompatible/', incompatible_browser, name='incompatible-browser'),
    path('service_abo/', service_abo, name='service_abo'),
    path('linked_open_data/', open_linked_data, name='open_linked_data'),
    ]
