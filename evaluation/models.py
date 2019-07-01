from django.db import models

# Create your models here.
from django.utils import timezone


class Access(models.Model):
    ip_hash = models.CharField(max_length=500, null=True, blank=True)
    os = models.CharField(max_length=500, null=True, blank=True)
    browser_family = models.CharField(max_length=500, null=True, blank=True)
    browser_version = models.CharField(max_length=500, null=True, blank=True)
    browser = models.CharField(max_length=500, null=True, blank=True)
    is_mobile = models.BooleanField(default=False)
    called_address = models.CharField(max_length=1000, null=True, blank=True)
    timestamp = models.DateTimeField(default=timezone.now)

    def __str__(self):
        return self.ip_hash
