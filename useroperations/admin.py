from django.contrib import admin
from django.contrib.admin import register
from .models import MbUser, Navigation


@register(MbUser)
class MbUserAdmin(admin.ModelAdmin):

    list_display = ('mb_user_name','mb_user_email') # tupel mit 1 elemente x = ( ele1 ,)

    pass

@register(Navigation)
class NavigationAdmin(admin.ModelAdmin):
    list_display = ('id','position','name', 'page_identifier', 'url','icon_name','parent')

    pass
