from django.contrib import admin
from django.contrib.admin import register
from useroperations.models import InspireDownloads

# Register your models here.


@register(InspireDownloads)
class InspireDownloadsAdmin(admin.ModelAdmin):

    list_display = ('id','user_id','user_email','service_name','no_of_tiles','date') # tupel mit 1 elemente x = ( ele1 ,)

    pass