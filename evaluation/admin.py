from django.contrib import admin

# Register your models here.
from evaluation.models import Access


class AccessAdmin(admin.ModelAdmin):
    list_display = ('ip_hash', 'browser_family', 'browser_version',  'os', 'is_mobile', 'timestamp')

admin.site.register(Access, AccessAdmin)
