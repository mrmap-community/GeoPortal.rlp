import binascii
import hashlib
import smtplib
import urllib.parse
from urllib import error
import re
import logging
import time

from django.contrib import messages
from django.http import HttpRequest
from django.shortcuts import render, redirect

from Geoportal import helper
from Geoportal.geoportalObjects import GeoportalJsonResponse, GeoportalContext
from Geoportal.settings import EXTERNAL_INTERFACE, LOCAL_MACHINE, ROOT_EMAIL_ADDRESS, DEFAULT_GUI
from useroperations.utils import helper_functions

from .forms import RegistrationForm, LoginForm, PasswordResetForm, ChangeProfileForm, DeleteProfileForm, FeedbackForm
from .models import MbUser, MbGroup, MbUserMbGroup, MbRole, GuiMbUser, MbProxyLog, Wfs, Wms
from django.utils.translation import gettext as _
from django.core.mail import send_mail
from pprint import pprint

logger = logging.getLogger(__name__)

def index_view(request, wiki_keyword=""):
    """ Prepares the index view, and renders the page.

    This view is the main view and landing page of the project. 
    It includes checking if a mediawiki page should be rendered or not.
    The default page, if no keyword was given, is favourite_wmcs.html,
     which shows an overview of the most popular wmc services.


    Args:
        request: HTTP request coming from djangos URLconf.
        wiki_keyword: If a string is present it will be used to render
         a mediawiki page transparently to the user.
        HTTPGet('status'): Checks if the login was successful or not.
         'status' comes from a mapbender php script(authentication.php)

    Returns:
        view: returns the rendered view, which can be:
         (default): favourite_wmcs.html
         (wiki): a mediawiki page
         (viewer): geoportal.html
         (error): 404.html
    """
    
    request.session["current_page"] = "index"
    lang = request.LANGUAGE_CODE
    get_params = request.GET.dict()

    output = ""
    results = []

    if request.method == 'GET' and 'status' in request.GET:
        if request.GET['status'] == "fail":
            messages.error(request, _("Login failed"))
            return redirect('useroperations:login')
        elif request.GET['status'] == "success":
            messages.success(request, _("Successfully logged in"))
        elif request.GET['status'] == "notactive":
            messages.error(request, _("Account not active"))


    if wiki_keyword == "viewer":
        template = "geoportal.html"
    elif wiki_keyword != "":
        template = "wiki.html"
        # display the wiki article in the template
        try:
            output = helper_functions.get_wiki_body_content(wiki_keyword, lang)
        except error.HTTPError:
            template = "404.html"
            output = ""
    else:
        template = "favourite_wmcs.html"
        # display the favourite WMCs in the template
        results = helper_functions.get_landing_page(lang)


    context = {
               'content': output,
               "wmc_results": results,
               }
    geoportal_context = GeoportalContext(request)
    geoportal_context.add_context(context=context)

    # check if this is an ajax call from info search
    if get_params.get("info_search", "") == 'true':
        category = get_params.get("category", "")
        output = helper_functions.get_wiki_body_content(wiki_keyword, lang, category)
        return GeoportalJsonResponse(html=output).get_response()
    else:
        return render(request, template, geoportal_context.get_context())


def login_view(request):
    """ View that handles the login

    Login is handled by a mapbender php script(authentication.php),
    this view just takes credentials and forwards it to the script.
    The script returns a status message to the index view.

    Args:
        request: HTTPRequest

    Returns:
        The login page
    """
    request.session["current_page"] = "login"
    form = LoginForm()
    btn_label_pw = _("Forgot Password?")
    btn_label_login = _("Login")
    geoportal_context = GeoportalContext(request=request)
    context = {
        'form': form,
        "btn_label_pw": btn_label_pw,
        "btn_label_login": btn_label_login,
        'headline': _("Login"),
    }
    geoportal_context.add_context(context)

    return render(request, "crispy_form_auth.html", geoportal_context.get_context())


def register_view(request):
    """ View for user registration.

    On HTTPGet it renders the registration Form in forms.py.
    On HTTPPost it checks if the username is taken and validates password specification.
    It uses pythons hashlib.pbkdf2_hmac for password storage.
    Further it tries to read the mapbender.conf to get the id of the admin user.

    Args:
        request: HTTPRequest

    Returns:
        RegistrationForm

    """
    request.session["current_page"] = "register"
    btn_label = _("Registration")
    small_labels = [
        "id_newsletter",
        "id_survey",
        "id_dsgvo",
    ]
    form = RegistrationForm()
    geoportal_context = GeoportalContext(request=request)
    disclaimer = _("Personal data will not be transmitted to other parties or services. "
                   "Further information can be found in our ")
    context = {
        'form': form,
        'headline': _("Registration"),
        "btn_label1": btn_label,
        "small_labels": small_labels,
        "disclaimer": disclaimer,
    }

    geoportal_context.add_context(context)

    if request.method == 'POST':
        form = RegistrationForm(request.POST)
        if form.is_valid():

            if MbUser.objects.filter(mb_user_name=form.cleaned_data['name']).exists():
                messages.error(request, _("The Username") + " {str_name} ".format(str_name=form.cleaned_data['name']) + _("is already taken"))
                return redirect('useroperations:register')

            if re.match(r'[A-Za-z0-9@#$%&+=!]{9,}', form.cleaned_data['password']) is None:
                messages.error(request, _("Password does not meet specified criteria, you need at least one uppercase letter, one lowercase letter, one number, you should have at least 9 characters, allowed special chars are: @#$%&+=!"))
                return redirect('useroperations:register')

            user = MbUser()
            user.mb_user_name = form.cleaned_data['name']
            user.mb_user_email = form.cleaned_data['email']
            user.mb_user_department = form.cleaned_data['department']
            user.mb_user_description = form.cleaned_data['description']
            user.mb_user_phone = form.cleaned_data['phone']
            user.mb_user_organisation_name = form.cleaned_data['organization']
            user.mb_user_newsletter = form.cleaned_data['newsletter']
            user.mb_user_allow_survey = form.cleaned_data['survey']
            user.timestamp_dsgvo_accepted = time.time()

            # check if passwords match
            if form.cleaned_data['password'] == form.cleaned_data['passwordconfirm']:
                salt = binascii.hexlify(helper_functions.os.urandom(16))
                user.salt = str(salt,'utf-8')
                bytepw = form.cleaned_data['password'].encode('utf-8')
                user.password = str(binascii.hexlify(hashlib.pbkdf2_hmac('sha256', bytepw, salt, 100000)),'utf-8')
            else:
                messages.error(request, _("Passwords do not match"))
                return redirect('useroperations:register')

            try:
                portaladmin = helper_functions.get_mapbender_config_value('PORTAL_ADMIN_USER_ID')
                user.mb_user_owner = portaladmin
            except KeyError:
                user.mb_user_owner = 1


            user.mb_user_login_count = 0
            user.mb_user_resolution = 72
            user.is_active = False

            user.activation_key = helper_functions.random_string(50)
            #activation_key = user.activation_key

            send_mail(
                 _("Activation Mail"),
                _("Hello ") + user.mb_user_name +
                _(",\n\nThis is your activation link. It will be valid until the end of the day, "
                  "please click it!\n Link: http://" + EXTERNAL_INTERFACE + "/activate/" + user.activation_key),
                'kontakt@geoportal.de',
                ['root@debian'],  # später email variable eintragen
                fail_silently=False,
            )

            user.save()

            user = MbUser.objects.get(mb_user_name=user.mb_user_name)
            UserGroupRel = MbUserMbGroup()
            UserGroupRel.fkey_mb_user = user

            group = MbGroup.objects.get(mb_group_name='guest')
            UserGroupRel.fkey_mb_group = group

            role = MbRole.objects.get(role_id=1)
            UserGroupRel.mb_user_mb_group_type = role
            UserGroupRel.save()

            messages.success(request, _("Account creation was successful. "
                                        "You have to activate your account via email before you can login!" + EXTERNAL_INTERFACE + "/activate/" + user.activation_key))

            return redirect('useroperations:login')

    return render(request, 'crispy_form_no_action.html', geoportal_context.get_context())


def pw_reset_view(request):
    """ View to reset password

    This view has the purpose to regain access if a password is lost.
    To achieve this the user has to enter the correct username and password combination.
    After doing so he gets an email with a new password.

    Args:
        request: HTTPRequest
    Returns:
        PasswordResetForm
        Email confirmation
    """
    
    request.session["current_page"] = "pw_reset"
    geoportal_context = GeoportalContext(request=request)

    form = PasswordResetForm()
    btn_label = _("Submit")
    context = {
        'form': form,
        "btn_label2": btn_label,
    }
    geoportal_context.add_context(context)

    if request.method == 'POST':
        form = PasswordResetForm(request.POST)
        if form.is_valid():

            username = form.cleaned_data['name']
            email = form.cleaned_data['email']


            if not MbUser.objects.filter(mb_user_name=username, mb_user_email=email):
                messages.error(request, _("No Account with this Username or Email found"))
            else:
                user = MbUser.objects.get(mb_user_name=username, mb_user_email=email)
                email = user.mb_user_email

                newpassword = helper_functions.random_string(20)

                salt = binascii.hexlify(helper_functions.os.urandom(16))
                user.salt = str(salt, 'utf-8')
                bytepw = newpassword.encode('utf-8')
                user.mb_user_password = hashlib.md5(newpassword.encode()).hexdigest()
                user.password = str(binascii.hexlify(hashlib.pbkdf2_hmac('sha256', bytepw, salt, 100000)), 'utf-8')

                user.save()
                send_mail(
                    subject=_("Lost Password"),
                    message=_("This is your new password, please change it immediately!\n Password: " + newpassword ),
                    from_email='kontakt@geoportal.de',
                    recipient_list=[ROOT_EMAIL_ADDRESS],
                    fail_silently=False,
                )
                messages.success(request, _("Password reset was successful, check your mails. Password: " + newpassword))
                return redirect('useroperations:login')

    return render(request, "crispy_form_no_action.html", geoportal_context.get_context())


def change_profile_view(request):
    """ View to change or delete profile data

    This view is needed if a user wants to change his personal data.
    To achieve this he has to enter his valid password.
    Further the user has the option to delete his account.

    Args:
        request: HTTPRequest
    Returns:
        On HTTPGet it renders the ChangeProfileForm in forms.py.
        On HTTPPost it checks whether the user wants to delete or change his profile.
         Afterwards password is checked for both options and action is takes on.

    """
    request.session["current_page"] = "change_profile"

    if request.COOKIES.get('PHPSESSID') is not None:
        session_data = helper_functions.get_mapbender_session_by_memcache(request.COOKIES.get('PHPSESSID'))
        if session_data != None:
            if b'mb_user_id' in session_data:
                userid = session_data[b'mb_user_id']
                user = MbUser.objects.get(mb_user_id=userid)

    if request.method == 'GET':
        userdata = {'name': user.mb_user_name,
                    'email': user.mb_user_email,
                    'department': user.mb_user_department,
                    'description': user.mb_user_description,
                    'phone': user.mb_user_phone,
                    'organization': user.mb_user_organisation_name,
                    'newsletter': user.mb_user_newsletter,
                    'survey': user.mb_user_allow_survey,
                    }
        form = ChangeProfileForm(userdata)

    if request.method == 'POST':
        form = ChangeProfileForm(request.POST)
        if form.is_valid():
            if request.POST['submit'] == 'Delete Profile' or request.POST['submit'] == 'Profil entfernen':
                if form.cleaned_data['oldpassword']:
                    salt = user.salt.encode('utf-8')
                    password = str(binascii.hexlify(
                        hashlib.pbkdf2_hmac('sha256', form.cleaned_data['oldpassword'].encode('utf-8'), salt, 100000)),'utf-8')
                    if password != user.password:
                        messages.error(request, _("Your old Password was wrong"))
                        return redirect('useroperations:change_profile')
                    else:
                        return redirect('useroperations:delete_profile')

            elif request.POST['submit'] == 'Change Profile' or request.POST['submit'] == 'Profil bearbeiten':
                if form.cleaned_data['oldpassword']:
                    salt = user.salt.encode('utf-8')
                    password = str(binascii.hexlify(hashlib.pbkdf2_hmac('sha256', form.cleaned_data['oldpassword'].encode('utf-8'), salt, 100000)), 'utf-8')
                    if password != user.password:
                        messages.error(request, _("Your old Password was wrong"))
                        return redirect('useroperations:change_profile')
                if form.cleaned_data['password']:
                    if form.cleaned_data['password'] == form.cleaned_data['passwordconfirm']:
                        salt = binascii.hexlify(helper_functions.os.urandom(16))
                        user.salt = str(salt, 'utf-8')
                        bytepw = form.cleaned_data['password'].encode('utf-8')
                        user.password = str(binascii.hexlify(hashlib.pbkdf2_hmac('sha256', bytepw, salt, 100000)), 'utf-8')
                    else:
                        messages.error(request, _("Passwords do not match"))
                        return redirect('useroperations:change_profile')
                user.mb_user_email = form.cleaned_data['email']
                user.mb_user_department = form.cleaned_data['department']
                user.mb_user_description = form.cleaned_data['description']
                user.mb_user_phone = form.cleaned_data['phone']
                user.mb_user_organisation_name = form.cleaned_data['organization']
                user.mb_user_newsletter = form.cleaned_data['newsletter']
                user.mb_user_allow_survey = form.cleaned_data['survey']

                user.save()
                messages.success(request, _("Successfully changed data"))
                return redirect('useroperations:index')

    small_labels = [
        "id_newsletter",
        "id_survey"
    ]
    btn_label_change = _("Change Profile")
    btn_label_delete = _("Delete Profile")

    geoportal_context = GeoportalContext(request=request)
    context = {
        'btn_label1': btn_label_change,
        'btn_label2': btn_label_delete,
        'form': form,
        'headline': _("Change data"),
        'small_labels': small_labels,
    }
    geoportal_context.add_context(context)
    return render(request, 'crispy_form_no_action.html', geoportal_context.get_context())


def delete_profile_view(request):
    """ View for profile deletion

    This view handles the deletion of profiles.
    Users get redirected to this view if they click on "delete profile"
     in the change profile form and provided a valid password.

    Args
        request: HTTPRequest
    Returns:
        DeleteProfileForm
    """

    session_data = helper.get_mb_user_session_data(request)
    username = session_data.get("user", "")

    request.session["current_page"] = "delete_profile"

    form = DeleteProfileForm()
    btn_label = _("Delete Profile!")
    geoportal_context = GeoportalContext(request=request)
    context = {
        'form': form,
        'headline': _("Delete Profile?"),
        "btn_label2": btn_label,
    }
    geoportal_context.add_context(context)

    if request.method == 'POST':
        if request.COOKIES.get('PHPSESSID') is not None:
            session_id = request.COOKIES.get('PHPSESSID')
            session_data = helper_functions.get_mapbender_session_by_memcache(session_id)
            try:
                userid = session_data[b'mb_user_id']
            except KeyError:
                messages.error(request, _("You are not logged in"))
                return redirect('useroperations:index')

            userid = session_data[b'mb_user_id']
            error = False
            if Wms.objects.filter(wms_owner=userid).exists() or Wfs.objects.filter(wfs_owner=userid).exists():
                messages.error(request, _("You are owner of registrated services - please delete them or give the ownership to another user."))
                error = True
            if GuiMbUser.objects.filter(fkey_mb_user_id=userid).exists() and GuiMbUser.objects.filter(mb_user_type='owner'):
                messages.error(request, _("You are owner of guis/applications - please delete them or give the ownership to another user."))
                error = True
            if MbProxyLog.objects.filter(fkey_mb_user_id=userid).exists():
                messages.error(request, _("There are logged service accesses for this user profile. Please connect the service administrators for the billing first."))
                error = True

            if error is False:
                user = MbUser.objects.get(mb_user_id=userid)
                user.is_active = False
                user.activation_key = helper_functions.random_string(50)
                user.timestamp_delete = time.time()
                user.save()

                send_mail(
                    _("Reactivation Mail"),
                    _("Hello ") + user.mb_user_name +
                    _(
                        ",\n\n In case the deletion of your account was a mistake, you can reactivate it by clicking this link! "
                        "\n Link: http://" + EXTERNAL_INTERFACE + "/activate/" + user.activation_key),
                    'kontakt@geoportal.de',
                    ['root@debian'],  # später email variable eintragen
                    fail_silently=False,
                )

                # user.delete()
                helper_functions.delete_mapbender_session_by_memcache(session_id)
                messages.success(request, _("Successfully deleted the user:")
                                 + " {str_name} ".format(str_name=user.mb_user_name)
                                 + _(". In case this was an accident, we sent you a link where you can reactivate "
                                     "your account for 24 hours!" + "Link: http://" + EXTERNAL_INTERFACE + "/activate/" + user.activation_key))

                return redirect('useroperations:index')

    return render(request, "crispy_form_no_action.html", geoportal_context.get_context())


def logout_view(request):
    """ View for logging out users

    This view deletes the session if a user logs out.

    Args:
        request: HTTPRequest

    Returns:
        LogoutForm
    """
    
    request.session["current_page"] = "logout"
    geoportal_context = GeoportalContext(request=request)

    if request.COOKIES.get('PHPSESSID') is not None:
        session_id = request.COOKIES.get('PHPSESSID')
        helper_functions.delete_mapbender_session_by_memcache(session_id)
        messages.success(request, _("Successfully logged out"))
        return redirect('useroperations:index')
    else:
        messages.error(request, _("You are not logged in"))
    return render(request, "crispy_form_no_action.html", geoportal_context.get_context())


def map_viewer_view(request):
    """ Parse all important data for the map rendering from the session

    This view is used to hand over all the data that is needed
     by the mapviewer, which currently is mapbender2.
    The parameters come from the search interface are therefore
     included in the URL.



    Args:
        request: HTTPReqeust , this in includes all mapviewer
                  parameters coming from the search module
    Returns:
            response: a json object containing all the
             mapviewer parameters
    """

    lang = request.LANGUAGE_CODE
    geoportal_context = GeoportalContext(request=request)

    is_external_search = "external" in request.META.get("HTTP_REFERER", "")
    request_get_params_dict = request.GET.dict()

    # is regular call means the request comes directly from the navigation menu in the page, without selecting a search result
    is_regular_call = len(request_get_params_dict) == 0 or request_get_params_dict.get("searchResultParam", None) is None
    request_get_params = dict(urllib.parse.parse_qsl(request_get_params_dict.get("searchResultParam")))
    template = "geoportal_external.html"
    gui_id = request_get_params_dict.get("g", DEFAULT_GUI) # get selected gui from params, use default gui otherwise!

    # if the call targets a DE catalogue result, we need to adjust a little thing here to restore the previously splitted url
    if request_get_params.get("WMS", None) is not None:
        # yes, this happens when we have a DE catalogue call! Now merge the stuff back into "WMS" again!
        for request_get_params_key, request_get_params_val in request_get_params.items():
            if request_get_params_key == "WMS":
                continue
            request_get_params["WMS"] += "&" + request_get_params_key + "=" + request_get_params_val

    # ToDo: Implement mb_user_myGui parsing

    mapviewer_params_dict = {
        "LAYER[id]": request_get_params.get("LAYER[id]", None),
        "LAYER[zoom]": request_get_params.get("LAYER[zoom]", None),
        "LAYER[visible]": request_get_params.get("LAYER[visible]", 1),
        "LAYER[querylayer]": request_get_params.get("LAYER[querylayer]", 1),
        "WMS": urllib.parse.quote(request_get_params.get("WMS", ""), safe=""),
        "WMC": request_get_params.get("WMC", ""),
        "GEORSS": urllib.parse.urlencode(request_get_params.get("GEORSS", "")),
        "KML": urllib.parse.urlencode(request_get_params.get("KML", "")),
        "FEATURETYPE": request_get_params.get("FEATURETYPE", {}).get("id", None),
        "ZOOM": request_get_params.get("ZOOM", None),
        "GEOJSON": request_get_params.get("GEOJSON", None),
        "GEOJSONZOOM": request_get_params.get("GEOJSONZOOM", None),
        "GEOJSONZOOMOFFSET": request_get_params.get("GEOJSONZOOMOFFSET", None),
    }
    mapviewer_params = gui_id
    for param_key, param_val in mapviewer_params_dict.items():
        if param_val is not None:
            if isinstance(param_val, int):
                mapviewer_params += "&" + param_key + "=" + str(param_val)
            elif len(param_val) > 0:
                mapviewer_params += "&" + param_key + "=" + param_val

    if is_regular_call:
        # an internal call from our geoportal should lead to the map viewer page without problems
        params = {
            "mapviewer_params": mapviewer_params,
            "mapviewer_src":  LOCAL_MACHINE + "/mapbender/frames/index.php?lang=" + lang + "&mb_user_myGui=" + mapviewer_params,
        }
        geoportal_context.add_context(context=params)
        return render(request, template, geoportal_context.get_context())
    elif is_external_search:
        # for an external ajax call we need to deliver a url which can be used to open a new tab which leads to the geoportal
        return GeoportalJsonResponse(url=LOCAL_MACHINE + ":8000/map-viewer/?" + request_get_params_dict.get("searchResultParam")).get_response()
    else:
        # for an internal search result selection, where the dynamic map viewer overlay shall be used
        return GeoportalJsonResponse(mapviewer_params=mapviewer_params).get_response()


def activation_view(request, activation_key=""):
    """
    View for activating user account after creation or deletion

    After creating an account, a user has to verify his email by clicking a link that was send to him.
    After deleting an account, a user can reactivate his account by clicking a link in the email.

    Args:
        request: HTTPRequest
        activation_key (slug): Key to activate the user, stored in database
    Returns:
        Template:
         (activation.html) in case activation was successful
         (404.html) in case activation failed for some reason

    """
    geoportal_context = GeoportalContext(request=request)

    if MbUser.objects.filter(activation_key=activation_key, is_active=True):
        messages.error(request, _("Account already active"))
        activated = True
        template = '404.html'

    elif not MbUser.objects.filter(activation_key=activation_key, is_active=False):
        messages.error(request, _("Invalid data"))
        activated = False
        template = '404.html'

    else:
        user = MbUser.objects.get(activation_key=activation_key, is_active=False)
        user.is_active = True
        activated = True
        user.save()
        template = 'activation.html'

    context = {
        "headline": _('Account activation'),
        "activated": activated,
        "navigation": helper.get_navigation_items(),
    }

    #geoportal_context = GeoportalContext(request=request)
    geoportal_context.add_context(context=context)

    pprint(geoportal_context)

    return render(request, template, context=geoportal_context.get_context())


def feedback_view(request: HttpRequest):

    """ Renders a feedback form for the user

    Args:
        request:
    Returns:

    """
    if request.method == 'POST':
        # form is returning
        form = FeedbackForm(request.POST)
        if form.is_valid():
            messages.success(request, _("Feedback sent. Thank you!"))
            msg = {
                "sender": form.cleaned_data["first_name"] + " " + form.cleaned_data["family_name"],
                "address": form.cleaned_data["email"],
                "message": form.cleaned_data["message"],
            }
            try:
                send_mail(
                    subject="[Geoportal] Feedback",
                    message=msg["message"],
                    from_email=msg["address"],
                    recipient_list=[ROOT_EMAIL_ADDRESS],
                    fail_silently=False
                )
            except smtplib.SMTPException:
                logger.error("Could not send feedback mail!")
                messages.error(request, _("An error occured during sending. Please inform an administrator."))
        else:
            messages.error(request, _("An error occured during sending. Please inform an administrator."))
        return index_view(request=request)
    else:
        # create the form
        template = "feedback_form.html"
        feedback_form = FeedbackForm()
        disclaimer = _("Personal data will not be transmitted to other parties or services. "
                       "The data, you provided during the feedback process, will only be used to stay in contact regarding your feedback.\n"
                       "Further information can be found in our ")
        params = {
            "form": feedback_form,
            "btn_send": _("Send"),
            "disclaimer": disclaimer,
        }
        geoportal_context = GeoportalContext(request=request)
        geoportal_context.add_context(params)
        return render(request=request, context=geoportal_context.get_context(), template_name=template)