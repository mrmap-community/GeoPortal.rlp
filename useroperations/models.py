from django.db import models

class Navigation(models.Model):
    name = models.CharField(max_length=100, unique=True)
    url = models.CharField(max_length=200, blank=True)
    page_identifier = models.CharField(max_length=100, unique=True)
    parent = models.ForeignKey('self', on_delete=models.CASCADE, blank=True, null=True)
    icon_name = models.CharField(max_length=200, blank=True)
    position = models.IntegerField(unique=True)
    
    def __str__(self):
        return self.name




class MbUser(models.Model):
    mb_user_id = models.AutoField(primary_key=True)
    mb_user_name = models.CharField(max_length=50,unique=True)
    mb_user_password = models.CharField(max_length=50)
    mb_user_owner = models.IntegerField()
    mb_user_description = models.CharField(max_length=255, blank=True, null=True)
    mb_user_login_count = models.IntegerField(blank=True, null=True)
    mb_user_email = models.EmailField(max_length=255, blank=True, null=True)
    mb_user_phone = models.CharField(max_length=50, blank=True, null=True)
    mb_user_department = models.CharField(max_length=255, blank=True, null=True)
    mb_user_resolution = models.IntegerField(blank=True, null=True)
    mb_user_organisation_name = models.CharField(max_length=255, blank=True, null=True)
    mb_user_position_name = models.CharField(max_length=255, blank=True, null=True)
    mb_user_phone1 = models.CharField(max_length=255, blank=True, null=True)
    mb_user_facsimile = models.CharField(max_length=255, blank=True, null=True)
    mb_user_delivery_point = models.CharField(max_length=255, blank=True, null=True)
    mb_user_city = models.CharField(max_length=255, blank=True, null=True)
    mb_user_postal_code = models.IntegerField(blank=True, null=True)
    mb_user_country = models.CharField(max_length=255, blank=True, null=True)
    mb_user_online_resource = models.CharField(max_length=255, blank=True, null=True)
    mb_user_realname = models.CharField(max_length=100, blank=True, null=True)
    mb_user_street = models.CharField(max_length=100, blank=True, null=True)
    mb_user_housenumber = models.CharField(max_length=50, blank=True, null=True)
    mb_user_reference = models.CharField(max_length=100, blank=True, null=True)
    mb_user_for_attention_of = models.CharField(max_length=100, blank=True, null=True)
    mb_user_valid_from = models.DateField(blank=True, null=True)
    mb_user_valid_to = models.DateField(blank=True, null=True)
    mb_user_password_ticket = models.CharField(max_length=100, blank=True, null=True)
    mb_user_digest = models.TextField(blank=True, null=True)
    mb_user_firstname = models.CharField(max_length=255, blank=True, null=True)
    mb_user_lastname = models.CharField(max_length=255, blank=True, null=True)
    mb_user_academictitle = models.CharField(max_length=255, blank=True, null=True)
    timestamp_create = models.DateTimeField(auto_now_add=True)
    timestamp = models.DateTimeField(auto_now_add=True)
    mb_user_newsletter = models.BooleanField(blank=True, null=True)
    mb_user_allow_survey = models.BooleanField(blank=True, null=True)
    mb_user_aldigest = models.TextField(blank=True, null=True)
    mb_user_glossar = models.CharField(max_length=5, blank=True, null=True)
    mb_user_textsize = models.CharField(max_length=14, blank=True, null=True)
    mb_user_last_login_date = models.DateField(blank=True, null=True)
    mb_user_spatial_suggest = models.CharField(max_length=5, blank=True, null=True)
    password = models.CharField(max_length=255, blank=True, null=True)
    salt = models.CharField(max_length=50, blank=True, null=True)
    is_active = models.BooleanField(blank=True, null=True)
    activation_key = models.CharField(max_length=250, blank=True, null=True)
    timestamp_delete = models.BigIntegerField(blank=True, null=True)
    timestamp_dsgvo_accepted = models.BigIntegerField(blank=True, null=True)



    class Meta:
        managed = False
        db_table = 'mb_user'

class MbUserMbGroup(models.Model):
        fkey_mb_user = models.OneToOneField(MbUser, on_delete=models.CASCADE, primary_key=True)
        #fkey_mb_user = models.ForeignKey('MbUser', models.DO_NOTHING, primary_key=True) # !!autogen!!
        fkey_mb_group = models.ForeignKey('MbGroup', models.DO_NOTHING)
        mb_user_mb_group_type = models.ForeignKey('MbRole', models.DO_NOTHING, db_column='mb_user_mb_group_type')

        class Meta:
            managed = False
            db_table = 'mb_user_mb_group'
            unique_together = (('fkey_mb_user', 'fkey_mb_group', 'mb_user_mb_group_type'),)

class MbGroup(models.Model):
    mb_group_id = models.AutoField(primary_key=True)
    mb_group_name = models.CharField(max_length=50)
    mb_group_owner = models.IntegerField(blank=True, null=True)
    mb_group_description = models.CharField(max_length=255)
    mb_group_title = models.CharField(max_length=255)
    mb_group_ext_id = models.BigIntegerField(blank=True, null=True)
    mb_group_address = models.CharField(max_length=255)
    mb_group_postcode = models.CharField(max_length=255)
    mb_group_city = models.CharField(max_length=255)
    mb_group_stateorprovince = models.CharField(max_length=255)
    mb_group_country = models.CharField(max_length=255)
    mb_group_voicetelephone = models.CharField(max_length=255)
    mb_group_facsimiletelephone = models.CharField(max_length=255)
    mb_group_email = models.CharField(max_length=255)
    mb_group_logo_path = models.TextField(blank=True, null=True)
    mb_group_homepage = models.CharField(max_length=255, blank=True, null=True)
    mb_group_admin_code = models.CharField(max_length=255, blank=True, null=True)
    timestamp_create = models.DateTimeField(auto_now_add=True)
    timestamp = models.DateTimeField(auto_now_add=True)
    mb_group_address_location = models.TextField(blank=True, null=True)  # This field type is a guess.
    uuid = models.UUIDField(blank=True, null=True)
    mb_group_ckan_uuid = models.UUIDField(blank=True, null=True)
    mb_group_ckan_api_key = models.UUIDField(blank=True, null=True)
    mb_group_csw_catalogues = models.TextField(blank=True, null=True)
    mb_group_ckan_catalogues = models.TextField(blank=True, null=True)
    mb_group_registry_url = models.CharField(max_length=1024, blank=True, null=True)
    export2ckan = models.BooleanField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'mb_group'

class MbRole(models.Model):
    role_id = models.AutoField(primary_key=True)
    role_name = models.CharField(max_length=50, blank=True, null=True)
    role_description = models.CharField(max_length=255, blank=True, null=True)
    role_exclude_auth = models.IntegerField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'mb_role'

class Wms(models.Model):
    wms_id = models.AutoField(primary_key=True)
    wms_version = models.CharField(max_length=50)
    wms_title = models.CharField(max_length=255)
    wms_abstract = models.TextField(blank=True, null=True)
    wms_getcapabilities = models.CharField(max_length=4096)
    wms_getmap = models.CharField(max_length=4096)
    wms_getfeatureinfo = models.CharField(max_length=4096)
    wms_getlegendurl = models.CharField(max_length=4096, blank=True, null=True)
    wms_filter = models.CharField(max_length=255, blank=True, null=True)
    wms_getcapabilities_doc = models.TextField(blank=True, null=True)
    wms_owsproxy = models.CharField(max_length=50, blank=True, null=True)
    wms_upload_url = models.CharField(max_length=4096, blank=True, null=True)
    fees = models.TextField(blank=True, null=True)
    accessconstraints = models.TextField(blank=True, null=True)
    contactperson = models.CharField(max_length=255, blank=True, null=True)
    contactposition = models.CharField(max_length=255, blank=True, null=True)
    contactorganization = models.CharField(max_length=255, blank=True, null=True)
    address = models.CharField(max_length=255, blank=True, null=True)
    city = models.CharField(max_length=255, blank=True, null=True)
    stateorprovince = models.CharField(max_length=255, blank=True, null=True)
    postcode = models.CharField(max_length=255, blank=True, null=True)
    country = models.CharField(max_length=255, blank=True, null=True)
    contactvoicetelephone = models.CharField(max_length=255, blank=True, null=True)
    contactfacsimiletelephone = models.CharField(max_length=255, blank=True, null=True)
    contactelectronicmailaddress = models.CharField(max_length=255, blank=True, null=True)
    wms_mb_getcapabilities_doc = models.TextField(blank=True, null=True)
    wms_owner = models.IntegerField(blank=True, null=True)
    wms_timestamp = models.IntegerField(blank=True, null=True)
    wms_supportsld = models.BooleanField(blank=True, null=True)
    wms_userlayer = models.BooleanField(blank=True, null=True)
    wms_userstyle = models.BooleanField(blank=True, null=True)
    wms_remotewfs = models.BooleanField(blank=True, null=True)
    wms_proxylog = models.IntegerField(blank=True, null=True)
    wms_pricevolume = models.IntegerField(blank=True, null=True)
    wms_username = models.CharField(max_length=255)
    wms_password = models.CharField(max_length=255)
    wms_auth_type = models.CharField(max_length=255)
    wms_timestamp_create = models.IntegerField(blank=True, null=True)
    wms_network_access = models.IntegerField(blank=True, null=True)
    fkey_mb_group_id = models.IntegerField(blank=True, null=True)
    uuid = models.UUIDField(blank=True, null=True)
    wms_max_imagesize = models.IntegerField(blank=True, null=True)
    wms_max_imagesize_x = models.IntegerField(blank=True, null=True)
    wms_max_imagesize_y = models.IntegerField(blank=True, null=True)
    inspire_annual_requests = models.BigIntegerField(blank=True, null=True)
    wms_proxy_log_fi = models.IntegerField(blank=True, null=True)
    wms_price_fi = models.IntegerField(blank=True, null=True)
    wms_license_source_note = models.TextField(blank=True, null=True)
    wms_bequeath_licence_info = models.IntegerField(blank=True, null=True)
    wms_bequeath_contact_info = models.IntegerField(blank=True, null=True)
    wms_proxy_exchange_external_urls = models.IntegerField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'wms'

class Wfs(models.Model):
    wfs_id = models.AutoField(primary_key=True)
    wfs_version = models.CharField(max_length=50)
    wfs_name = models.CharField(max_length=255, blank=True, null=True)
    wfs_title = models.CharField(max_length=255)
    wfs_abstract = models.TextField(blank=True, null=True)
    wfs_getcapabilities = models.CharField(max_length=4096)
    wfs_describefeaturetype = models.CharField(max_length=4096, blank=True, null=True)
    wfs_getfeature = models.CharField(max_length=4096, blank=True, null=True)
    wfs_transaction = models.CharField(max_length=4096, blank=True, null=True)
    wfs_owsproxy = models.CharField(max_length=50, blank=True, null=True)
    wfs_getcapabilities_doc = models.TextField(blank=True, null=True)
    wfs_upload_url = models.CharField(max_length=4096, blank=True, null=True)
    fees = models.TextField(blank=True, null=True)
    accessconstraints = models.TextField(blank=True, null=True)
    individualname = models.CharField(max_length=255, blank=True, null=True)
    positionname = models.CharField(max_length=255, blank=True, null=True)
    providername = models.CharField(max_length=255, blank=True, null=True)
    city = models.CharField(max_length=255, blank=True, null=True)
    deliverypoint = models.CharField(max_length=255, blank=True, null=True)
    administrativearea = models.CharField(max_length=255, blank=True, null=True)
    postalcode = models.CharField(max_length=255, blank=True, null=True)
    voice = models.CharField(max_length=255, blank=True, null=True)
    facsimile = models.CharField(max_length=255, blank=True, null=True)
    electronicmailaddress = models.CharField(max_length=255, blank=True, null=True)
    wfs_mb_getcapabilities_doc = models.TextField(blank=True, null=True)
    wfs_owner = models.IntegerField(blank=True, null=True)
    wfs_timestamp = models.IntegerField(blank=True, null=True)
    country = models.CharField(max_length=255, blank=True, null=True)
    wfs_timestamp_create = models.IntegerField(blank=True, null=True)
    wfs_network_access = models.IntegerField(blank=True, null=True)
    fkey_mb_group_id = models.IntegerField(blank=True, null=True)
    uuid = models.UUIDField(blank=True, null=True)
    wfs_max_features = models.IntegerField(blank=True, null=True)
    inspire_annual_requests = models.BigIntegerField(blank=True, null=True)
    wfs_username = models.CharField(max_length=255, blank=True, null=True)
    wfs_password = models.CharField(max_length=255, blank=True, null=True)
    wfs_auth_type = models.CharField(max_length=255, blank=True, null=True)
    wfs_license_source_note = models.TextField(blank=True, null=True)
    wfs_proxylog = models.IntegerField(blank=True, null=True)
    wfs_pricevolume = models.IntegerField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'wfs'

class GuiMbUser(models.Model):
    fkey_gui = models.OneToOneField('Gui', models.DO_NOTHING, primary_key=True)
    fkey_mb_user = models.ForeignKey('MbUser', models.DO_NOTHING)
    mb_user_type = models.CharField(max_length=50, blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'gui_mb_user'
        unique_together = (('fkey_gui', 'fkey_mb_user'),)


class MbProxyLog(models.Model):
    proxy_log_timestamp = models.DateTimeField(blank=True, null=True)
    fkey_wms_id = models.IntegerField()
    fkey_mb_user_id = models.IntegerField()
    request = models.CharField(max_length=4096, blank=True, null=True)
    pixel = models.BigIntegerField(blank=True, null=True)
    price = models.FloatField(blank=True, null=True)
    got_result = models.IntegerField(blank=True, null=True)
    error_message = models.TextField(blank=True, null=True)
    error_mime_type = models.CharField(max_length=50, blank=True, null=True)
    layer_featuretype_list = models.TextField(blank=True, null=True)
    request_type = models.CharField(max_length=15, blank=True, null=True)
    log_id = models.BigAutoField(primary_key=True)
    fkey_wfs_id = models.IntegerField(blank=True, null=True)
    features = models.IntegerField(blank=True, null=True)

    class Meta:
        managed = False
        db_table = 'mb_proxy_log'

class Gui(models.Model):
    gui_id = models.CharField(primary_key=True, max_length=50)
    gui_name = models.CharField(max_length=50)
    gui_description = models.CharField(max_length=255)
    gui_public = models.IntegerField()

    class Meta:
        managed = False
        db_table = 'gui'

