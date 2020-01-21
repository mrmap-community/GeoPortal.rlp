import hashlib
import logging
import re
import smtplib
import time
import urllib.parse
from collections import OrderedDict
from pprint import pprint
from urllib import error

import bcrypt
import requests
from django.contrib import messages
from django.core.mail import send_mail
from django.http import HttpRequest
from django.shortcuts import render, redirect, render_to_response
from django.utils.translation import gettext as _

from Geoportal.decorator import check_browser
from Geoportal.geoportalObjects import GeoportalJsonResponse, GeoportalContext
from Geoportal.settings import DEFAULT_GUI, HOSTNAME, HOSTIP, HTTP_OR_SSL, INTERNAL_SSL, \
    SESSION_NAME, PROJECT_DIR, MULTILINGUAL, LANGUAGE_CODE, DEFAULT_FROM_EMAIL
from Geoportal.utils import utils, php_session_data, mbConfReader
from searchCatalogue.utils.url_conf import URL_INSPIRE_DOC
from useroperations.settings import LISTED_VIEW_AS_DEFAULT, ORDER_BY_DEFAULT
from useroperations.utils import useroperations_helper
from .forms import RegistrationForm, LoginForm, PasswordResetForm, ChangeProfileForm, DeleteProfileForm, FeedbackForm
from .models import MbUser, MbGroup, MbUserMbGroup, MbRole, GuiMbUser, MbProxyLog, Wfs, Wms

logger = logging.getLogger(__name__)


@check_browser
def index_view(request, wiki_keyword=""):
    """ Prepares the index view, and renders the page.

    This view is the main view and landing page of the project.
    It includes checking if a mediawiki page should be rendered or not.
    The default page, if no keyword was given, is landing_page.html,
     which shows an overview of the most popular wmc services.


    Args:
        request: HTTP request coming from djangos URLconf.
        wiki_keyword: If a string is present it will be used to render
         a mediawiki page transparently to the user.
        HTTPGet('status'): Checks if the login was successful or not.
         'status' comes from a mapbender php script(authentication.php)

    Returns:
        view: returns the rendered view, which can be:
         (default): landing_page.html
         (wiki): a mediawiki page
         (viewer): geoportal.html
         (error): 404.html
    """

    request.session["current_page"] = "index"
    if MULTILINGUAL:
        lang = request.LANGUAGE_CODE
    else:
        lang = LANGUAGE_CODE
    get_params = request.GET.dict()
    dsgvo_list = ["Datenschutz", "Kontakt", "Impressum", "Rechtshinweis", "Transparenzgesetz"]

    output = ""
    results = []

    # In a first run, we check if the mapbender login has worked, which is indicated by a 'status' GET parameter.
    # Since this is not nice to have in your address bar, we exchange the GET parameter with a pretty message for the user
    # and reload the same route simply again to get rid of the GET parameter.
    if request.method == 'GET' and 'status' in request.GET:
        if request.GET['status'] == "fail":
            messages.error(request, _("Login failed"))
            return redirect('useroperations:login')
        elif request.GET['status'] == "success":
            messages.success(request, _("Successfully logged in"))
            return redirect('useroperations:index')
        elif request.GET['status'] == "notactive":
            messages.error(request, _("Account not active"))
            return redirect('useroperations:index')
        elif request.GET['status'] == "fail3":
            messages.error(request, _("Password failed too many times! Account is deactivated! Activation mail was sent to you!"))
            return redirect('useroperations:index')


    geoportal_context = GeoportalContext(request)
    context_data = geoportal_context.get_context()
    if context_data['dsgvo'] == 'no' and context_data['loggedin'] == True and wiki_keyword not in dsgvo_list:
        return redirect('useroperations:change_profile')

    if wiki_keyword == "viewer":
        template = "geoportal.html"
    elif wiki_keyword != "":
        # display the wiki article in the template
        template = "wiki.html"
        try:
            output = useroperations_helper.get_wiki_body_content(wiki_keyword, lang)
        except (error.HTTPError, FileNotFoundError) as e:
            template = "404.html"
            output = ""
    else:
        # display the favourite WMCs in the template
        template = "landing_page.html"
        results = useroperations_helper.get_landing_page(lang)

    context = {
               "content": output,
               "results": results,
               }
    geoportal_context.add_context(context=context)

    # check if this is an ajax call from info search
    if get_params.get("info_search", "") == 'true':
        category = get_params.get("category", "")
        output = useroperations_helper.get_wiki_body_content(wiki_keyword, lang, category)
        return GeoportalJsonResponse(html=output).get_response()
    else:
        return render(request, template, geoportal_context.get_context())


@check_browser
def applications_view(request: HttpRequest):
    """ Renders the view for showing all available applications

    Args:
        request: The incoming request
    Returns:
         A rendered view
    """
    geoportal_context = GeoportalContext(request)

    order_by_options = OrderedDict()
    order_by_options["rank"] = _("Relevance")
    order_by_options["title"] = _("Alphabetically")

    apps = useroperations_helper.get_all_applications()
    params = {
        "apps": apps,
        "order_by_options": order_by_options,
        "ORDER_BY_DEFAULT": ORDER_BY_DEFAULT,
        "LISTED_VIEW_AS_DEFAULT": LISTED_VIEW_AS_DEFAULT,
    }
    template = "applications.html"
    geoportal_context.add_context(params)
    return render(request, template, geoportal_context.get_context())


@check_browser
def organizations_view(request: HttpRequest):
    """ Renders the view for showing all participating organizations

    Args:
        request: The incoming request
    Returns:
         A rendered view
    """

    geoportal_context = GeoportalContext(request)
    context_data = geoportal_context.get_context()
    if context_data['dsgvo'] == 'no' and context_data['loggedin'] == True:
        return redirect('useroperations:change_profile')

    template = "publishing_organizations.html"
    geoportal_context = GeoportalContext(request)
    order_by_options = OrderedDict()
    order_by_options["rank"] = _("Relevance")
    order_by_options["title"] = _("Alphabetically")

    context = {
        "organizations": useroperations_helper.get_all_organizations(),
        "order_by_options": order_by_options,
        "ORDER_BY_DEFAULT": ORDER_BY_DEFAULT,
        "LISTED_VIEW_AS_DEFAULT": LISTED_VIEW_AS_DEFAULT,
    }
    geoportal_context.add_context(context)
    return render(request, template, geoportal_context.get_context())


@check_browser
def categories_view(request: HttpRequest):
    """ Renders the view for showing all available categories

    Args:
        request: The incoming request
    Returns:
         A rendered view
    """

    geoportal_context = GeoportalContext(request)
    context_data = geoportal_context.get_context()
    if context_data['dsgvo'] == 'no' and context_data['loggedin'] == True:
        return redirect('useroperations:change_profile')

    order_by_options = OrderedDict()
    order_by_options["rank"] = _("Relevance")
    order_by_options["title"] = _("Alphabetically")

    template = "inspire_topics.html"
    context = {
        "topics": useroperations_helper.get_all_inspire_topics(request.LANGUAGE_CODE),
        "inspire_doc_uri": URL_INSPIRE_DOC,
        "order_by_options": order_by_options,
        "ORDER_BY_DEFAULT": ORDER_BY_DEFAULT,
        "LISTED_VIEW_AS_DEFAULT": LISTED_VIEW_AS_DEFAULT,
    }
    geoportal_context.add_context(context)
    return render(request, template, geoportal_context.get_context())


@check_browser
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


@check_browser
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

    context_data = geoportal_context.get_context()
    if context_data['loggedin'] == True:
        messages.error(request, _("Log out to register a new user"))
        return redirect('useroperations:index')

    if request.method == 'POST':
        form = RegistrationForm(request.POST)
        if form.is_valid():

            if MbUser.objects.filter(mb_user_name=form.cleaned_data['name']).exists():
                messages.error(request, _("The Username") + " {str_name} ".format(str_name=form.cleaned_data['name']) + _("is already taken"))
                return redirect('useroperations:register')

            if re.match(r'[A-Za-z0-9@#$%&+=!:]{9,}', form.cleaned_data['password']) is None:
                messages.error(request, _("Password does not meet specified criteria, you need at least one uppercase letter, one lowercase letter, one number, you should have at least 9 characters, allowed special chars are: @#$%&+=!:"))
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
                user.password = (str(bcrypt.hashpw(form.cleaned_data['password'].encode('utf-8'), bcrypt.gensalt(12)),'utf-8'))
            else:
                form = RegistrationForm(request.POST)
                context = {
                    'form': form,
                    'headline': _("Registration"),
                    "btn_label1": btn_label,
                    "small_labels": small_labels,
                    "disclaimer": disclaimer,
                }
                geoportal_context.add_context(context)
                messages.error(request, _("Passwords do not match"))
                return render(request, 'crispy_form_no_action.html', geoportal_context.get_context())

            try:
                realm = mbConfReader.get_mapbender_config_value(PROJECT_DIR,'REALM')
                portaladmin = mbConfReader.get_mapbender_config_value(PROJECT_DIR,'PORTAL_ADMIN_USER_ID')
                byte_aldigest = (form.cleaned_data['name'] + ":" + realm + ":" + form.cleaned_data['password']).encode('utf-8')
                user.mb_user_aldigest = hashlib.md5(byte_aldigest).hexdigest()
                user.mb_user_owner = portaladmin
            except KeyError:
                user.mb_user_owner = 1
                user.mb_user_aldigest = "Could not find realm"
                print("Could not read from Mapbender Config")


            user.mb_user_login_count = 0
            user.mb_user_resolution = 72
            user.is_active = False

            user.activation_key = useroperations_helper.random_string(50)

            send_mail(
                 _("Activation Mail"),
                _("Hello ") + user.mb_user_name +
                ", \n \n" +
                _("This is your activation link. It will be valid until the end of the day, please click it!")
              	+ "\n Link: "  + HTTP_OR_SSL + HOSTNAME + "/activate/" + user.activation_key,
                DEFAULT_FROM_EMAIL,
                [user.mb_user_email],
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
                "You have to activate your account via email before you can login!"))

            return redirect('useroperations:login')
        else:
            form = RegistrationForm(request.POST)
            context = {
                'form': form,
                'headline': _("Registration"),
                "btn_label1": btn_label,
                "small_labels": small_labels,
                "disclaimer": disclaimer,
            }
            geoportal_context.add_context(context)
            messages.error(request, _("Captcha was wrong! Please try again"))

    return render(request, 'crispy_form_no_action.html', geoportal_context.get_context())


@check_browser
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

                newpassword = useroperations_helper.random_string(20)

                user.password = (str(bcrypt.hashpw(newpassword.encode('utf-8'), bcrypt.gensalt(12)),'utf-8'))

                user.save()

                send_mail(
                        _("Lost Password"),
                        _("Hello ") + user.mb_user_name +
                        ", \n\n" +
                        _("This is your new password, please change it immediately!\n Password: ") + newpassword ,
                        'kontakt@geoportal.de',
                        [user.mb_user_email],
                        fail_silently=False,
                )


                messages.success(request, _("Password reset was successful, check your mails."))
                return redirect('useroperations:login')

    return render(request, "crispy_form_no_action.html", geoportal_context.get_context())


@check_browser
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
    dsgvo_flag = True # guest

    request.session["current_page"] = "change_profile"
    form = ChangeProfileForm()
    user = None
    if request.COOKIES.get(SESSION_NAME) is not None:
        session_data = php_session_data.get_mapbender_session_by_memcache(request.COOKIES.get(SESSION_NAME))
        if session_data != None:
            if b'mb_user_id' in session_data and session_data[b'mb_user_name'] != b'guest':
                userid = session_data[b'mb_user_id']
                user = MbUser.objects.get(mb_user_id=userid)
            else:
                return redirect('useroperations:index')

    else:
        return redirect('useroperations:index')
    if user is None:
        # we expect it to be read out of the session data until this point!!
        messages.add_message(request, messages.ERROR, _("The user could not be found. Please contact an administrator!"))
        return redirect('useroperations:index')

    if request.method == 'GET':
        geoportal_context = GeoportalContext(request)
        context_data = geoportal_context.get_context()
        userdata = {'name': user.mb_user_name,
                    'email': user.mb_user_email,
                    'department': user.mb_user_department,
                    'description': user.mb_user_description,
                    'phone': user.mb_user_phone,
                    'organization': user.mb_user_organisation_name,
                    'newsletter': user.mb_user_newsletter,
                    'survey': user.mb_user_allow_survey,
                    'create_digest' : user.create_digest,
                    'preferred_gui' : user.fkey_preferred_gui_id,
                    }
        if user.timestamp_dsgvo_accepted:
            userdata["dsgvo"] = True

        form = ChangeProfileForm(userdata)

        if context_data['dsgvo'] == 'no' and context_data['loggedin'] == True:
            dsgvo_flag = False
            messages.error(request, _("Please accept the General Data Protection Regulation or delete your account!"))

    if request.method == 'POST':
        form = ChangeProfileForm(request.POST)
        if form.is_valid():

            # Delete profile process
            if request.POST['submit'] == 'Delete Profile' or request.POST['submit'] == 'Profil entfernen':
                return redirect('useroperations:delete_profile')

                if password != user.password:
                    messages.error(request, _("Your old Password was wrong"))
                    return redirect('useroperations:change_profile')
                else:
                    return redirect('useroperations:delete_profile')

            # Save profile process
            elif request.POST['submit'] == 'Save' or request.POST['submit'] == 'Speichern':
                if form.cleaned_data['password']:

                    # user wants to change the password
                    # first, the old pasword has to be checked
                    if form.cleaned_data['oldpassword']:
                        password = useroperations_helper.bcrypt_password(form.cleaned_data["oldpassword"], user)
                        # if the old password didn't match with the one associated to the user, we can abort here!
                        if password != user.password:
                            messages.error(request, _("Your current password was wrong"))
                            return redirect('useroperations:change_profile')
                        else:
                            # if the old password is fine, we can continue with checking the new provided one
                            if form.cleaned_data['password'] == form.cleaned_data['passwordconfirm']:
                                user.password = (str(bcrypt.hashpw(form.cleaned_data['password'].encode('utf-8'), bcrypt.gensalt(12)), 'utf-8'))
                            else:
                                messages.error(request, _("Passwords do not match"))
                                return redirect('useroperations:change_profile')
                    else:
                        # user provided a new password but not the old one!
                        messages.error(request, _("For changing your password, you have to enter your current password as well."))
                        return redirect("useroperations:change_profile")
                user.mb_user_email = form.cleaned_data['email']
                user.mb_user_department = form.cleaned_data['department']
                user.mb_user_description = form.cleaned_data['description']
                user.mb_user_phone = form.cleaned_data['phone']
                user.mb_user_organisation_name = form.cleaned_data['organization']
                user.mb_user_newsletter = form.cleaned_data['newsletter']
                user.mb_user_allow_survey = form.cleaned_data['survey']
                user.create_digest = form.cleaned_data['create_digest']
                user.fkey_preferred_gui_id = form.cleaned_data['preferred_gui']

                if form.cleaned_data['dsgvo'] == True:
                    user.timestamp_dsgvo_accepted = time.time()
                    # set session variable dsgvo via session wrapper php script
                    response = requests.get(HTTP_OR_SSL + '127.0.0.1/mapbender/php/mod_sessionWrapper.php?sessionId='+request.COOKIES.get(SESSION_NAME)+'&operation=set&key=dsgvo&value=true', verify=INTERNAL_SSL)
                else:
                    response = requests.get(HTTP_OR_SSL + '127.0.0.1/mapbender/php/mod_sessionWrapper.php?sessionId='+request.COOKIES.get(SESSION_NAME) +'&operation=set&key=dsgvo&value=false', verify=INTERNAL_SSL)
                    user.timestamp_dsgvo_accepted = None

                if form.cleaned_data['preferred_gui'] == 'Geoportal-RLP_2019':
                    # set session variable preferred_gui via session wrapper php script
                    response = requests.get(HTTP_OR_SSL + '127.0.0.1/mapbender/php/mod_sessionWrapper.php?sessionId='+request.COOKIES.get(SESSION_NAME)+'&operation=set&key=preferred_gui&value=Geoportal-RLP_2019', verify=INTERNAL_SSL)
                else:
                    response = requests.get(HTTP_OR_SSL + '127.0.0.1/mapbender/php/mod_sessionWrapper.php?sessionId='+request.COOKIES.get(SESSION_NAME)+'&operation=set&key=preferred_gui&value='+DEFAULT_GUI, verify=INTERNAL_SSL)
                
                user.save()
                messages.success(request, _("Successfully changed data"))
                return redirect('useroperations:index')

    small_labels = [
        "id_newsletter",
        "id_survey",
        "id_create_digest",
        "id_dsgvo"
    ]
    btn_label_change = _("Save")
    btn_label_delete = _("Delete Profile")

    geoportal_context = GeoportalContext(request=request)
    context = {
        'btn_label1': btn_label_change,
        'btn_label2': btn_label_delete,
        'form': form,
        'headline': _("Change data"),
        'small_labels': small_labels,
        'dsgvo_flag': dsgvo_flag,
    }
    geoportal_context.add_context(context)
    return render(request, 'crispy_form_no_action.html', geoportal_context.get_context())


@check_browser
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
    geoportal_context = GeoportalContext(request=request)
    if request.COOKIES.get(SESSION_NAME) is not None:
        session_data = php_session_data.get_mapbender_session_by_memcache(request.COOKIES.get(SESSION_NAME))
        if session_data != None:
            if b'mb_user_id' in session_data and session_data[b'mb_user_name'] != b'guest':

                session_data = php_session_data.get_mb_user_session_data(request)

                request.session["current_page"] = "delete_profile"

                form = DeleteProfileForm(request.POST)
                btn_label = _("Delete Profile!")
                geoportal_context = GeoportalContext(request=request)
                context = {
                    'form': form,
                    'headline': _("Delete Profile?"),
                    "btn_label2": btn_label,
                }
                geoportal_context.add_context(context)

                if request.method == 'POST':
                    if form.is_valid():
                        # get user
                        session_id = request.COOKIES.get(SESSION_NAME)
                        session_data = php_session_data.get_mapbender_session_by_memcache(session_id)
                        try:
                            userid = session_data[b'mb_user_id']
                        except KeyError:
                            messages.error(request, _("You are not logged in"))
                            return redirect('useroperations:index')
                        userid = session_data[b'mb_user_id']
                        user = MbUser.objects.get(mb_user_id=userid)

                        # check if password is correct!
                        pw = form.cleaned_data.get("confirmation_password", None)
                        if pw is not None and user.password == useroperations_helper.bcrypt_password(pw, user):
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

                            if not error:
                                user.is_active = False
                                user.activation_key = useroperations_helper.random_string(50)
                                user.timestamp_delete = time.time()
                                user.save()

                                send_mail(
                                    _("Reactivation Mail"),
                                    _("Hello ") + user.mb_user_name +
                                    ", \n \n" +
                                    _("In case the deletion of your account was a mistake, you can reactivate it by clicking this link!")
                                    + "\n Link: " + HTTP_OR_SSL + HOSTNAME + "/activate/" + user.activation_key,
                                    'kontakt@geoportal.de',
                                    [user.mb_user_email],  # später email variable eintragen
                                    fail_silently=False,
                                )

                                php_session_data.delete_mapbender_session_by_memcache(session_id)
                                messages.success(request, _("Successfully deleted the user:")
                                                 + " {str_name} ".format(str_name=user.mb_user_name)
                                                 + _(". In case this was an accident, we sent you a link where you can reactivate "
                                                     "your account for 24 hours!"))

                                return redirect('useroperations:logout')
                        else:
                            messages.error(request, _("Password invalid. Profile not deleted."))
                            return redirect("useroperations:change_profile")
            else:
                return redirect('useroperations:index')
    else:
        return redirect('useroperations:index')

    return render(request, "crispy_form_no_action.html", geoportal_context.get_context())


@check_browser
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

    if request.COOKIES.get(SESSION_NAME) is not None:
        session_id = request.COOKIES.get(SESSION_NAME)
        php_session_data.delete_mapbender_session_by_memcache(session_id)
        messages.success(request, _("Successfully logged out"))
        return redirect('useroperations:index')
    else:
        messages.error(request, _("You are not logged in"))
    return render(request, "crispy_form_no_action.html", geoportal_context.get_context())


@check_browser
def map_viewer_view(request):
    """ Parse all important data for the map rendering from the session

    This view is used to hand over all the data that is needed
     by the mapviewer.
    The parameters come from the search interface are therefore
     included in the URL.



    Args:
        request: HTTPReqeust , this in includes all mapviewer
                  parameters coming from the search module
    Returns:
            response: a json object containing all the
             mapviewer parameters
    """

    geoportal_context = GeoportalContext(request)
    context_data = geoportal_context.get_context()
    if context_data['dsgvo'] == 'no' and context_data['loggedin'] == True:
        return redirect('useroperations:change_profile')

    lang = request.LANGUAGE_CODE
    geoportal_context = GeoportalContext(request=request)

    is_external_search = "external" in request.META.get("HTTP_REFERER", "")
    request_get_params_dict = request.GET.dict()

    # is regular call means the request comes directly from the navigation menu in the page, without selecting a search result
    is_regular_call = len(request_get_params_dict) == 0 or request_get_params_dict.get("searchResultParam", None) is None
    request_get_params = dict(urllib.parse.parse_qsl(request_get_params_dict.get("searchResultParam")))
    template = "geoportal_external.html"
    gui_id = context_data.get("preferred_gui", DEFAULT_GUI)  # get selected gui from params, use default gui otherwise!

    wmc_id = request_get_params.get("WMC", "") or request_get_params.get("wmc", "")
    if len(request_get_params) > 1:
        wms_id = urllib.parse.quote(request_get_params_dict.get("searchResultParam", "").replace("WMS=", ""), safe="")
    else:
        wms_id = urllib.parse.quote(request_get_params.get("WMS", "") or request_get_params.get("wms", ""), safe="")

    # check if the request comes from a mobile device
    is_mobile = request.user_agent.is_mobile
    if is_mobile:

        # if so, just call the mobile map viewer in a new window
        mobile_viewer_url = "{}{}/mapbender/extensions/mobilemap2/index.html?".format(HTTP_OR_SSL, HOSTNAME)
        if wmc_id != "":
            mobile_viewer_url += "&wmc_id={}".format(wmc_id)
        if wms_id != "":
            mobile_viewer_url += "&wms_id={}".format(wms_id)
        return GeoportalJsonResponse(url=mobile_viewer_url).get_response()

    # if the call targets a DE catalogue result, we need to adjust a little thing here to restore the previously splitted url
    if request_get_params.get("WMS", None) is not None:
        # yes, this happens when we have a DE catalogue call! Now merge the stuff back into "WMS" again!
        for request_get_params_key, request_get_params_val in request_get_params.items():
            if request_get_params_key == "WMS":
                continue
            request_get_params["WMS"] += "&" + request_get_params_key + "=" + request_get_params_val

    mapviewer_params_dict = {
        "LAYER[id]": request_get_params.get("LAYER[id]", None),
        "LAYER[zoom]": request_get_params.get("LAYER[zoom]", None),
        "LAYER[visible]": request_get_params.get("LAYER[visible]", 1),
        "LAYER[querylayer]": request_get_params.get("LAYER[querylayer]", 1),
        "WMS": wms_id,
        "WMC": wmc_id,
        "GEORSS": urllib.parse.urlencode(request_get_params.get("GEORSS", "")),
        "KML": urllib.parse.urlencode(request_get_params.get("KML", "")),
        "FEATURETYPE": request_get_params.get("FEATURETYPE[id]", ""),
        "ZOOM": request_get_params.get("ZOOM", None),
        "GEOJSON": request_get_params.get("GEOJSON", None),
        "GEOJSONZOOM": request_get_params.get("GEOJSONZOOM", None),
        "GEOJSONZOOMOFFSET": request_get_params.get("GEOJSONZOOMOFFSET", None),
        "gui_id": request_get_params.get("gui_id", gui_id),
    }
    mapviewer_params = ""
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
            "mapviewer_src":  HTTP_OR_SSL + HOSTIP + "/mapbender/frames/index.php?lang=" + lang + "&" + mapviewer_params,
        }
        geoportal_context.add_context(context=params)
        return render(request, template, geoportal_context.get_context())
    elif is_external_search:
        # for an external ajax call we need to deliver a url which can be used to open a new tab which leads to the geoportal
        return GeoportalJsonResponse(url=HTTP_OR_SSL + HOSTNAME, mapviewer_params=gui_id + "&" + request_get_params_dict.get("searchResultParam")).get_response()
    else:
        # for an internal search result selection, where the dynamic map viewer overlay shall be used
        return GeoportalJsonResponse(mapviewer_params=mapviewer_params).get_response()


@check_browser
def get_map_view(request):
    """ Calls a service directly using GET parameters

    There is no logic inside this function. Since all the service loading is on javascript controlled client side
    we only call the usual index page and keep the GET parameters which will be processed by the javascript.

    Args:
        request (HttpRequest):  The incoming HttpRequest
    Returns:
         the index view
    """
    return index_view(request)


@check_browser
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
        "navigation": utils.get_navigation_items(),
    }
    geoportal_context.add_context(context=context)

    pprint(geoportal_context)

    return render(request, template, context=geoportal_context.get_context())


@check_browser
def feedback_view(request: HttpRequest):

    """ Renders a feedback form for the user

    Args:
        request:
    Returns:

    """
    request.session["current_page"] = "feedback"

    geoportal_context = GeoportalContext(request)
    context_data = geoportal_context.get_context()
    if context_data['dsgvo'] == 'no' and context_data['loggedin'] == True:
        return redirect('useroperations:change_profile')

    disclaimer = _("Personal data will not be transmitted to other parties or services. "
                   "The data, you provided during the feedback process, will only be used to stay in contact regarding your feedback.\n"
                   "Further information can be found in our ")
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
                    _("Geoportal Feedback"),
                    _("Feedback from ") + form.cleaned_data["first_name"] + " " + form.cleaned_data["family_name"]
                    + ", \n \n" +
                    form.cleaned_data["message"],
                    form.cleaned_data["email"],
                    ['kontakt@geoportal.rlp.de'],  # später email variable eintragen
                    fail_silently=False,
                )
            except smtplib.SMTPException:
                logger.error("Could not send feedback mail!")
                messages.error(request, _("An error occured during sending. Please inform an administrator."))
            return index_view(request=request)
        else:
            messages.error(request, _("Captcha was wrong! Please try again"))
            template = "feedback_form.html"
            params = {
                "form": form,
                "btn_send": _("Send"),
                "disclaimer": disclaimer,
            }
            geoportal_context.add_context(params)
            return render(request=request, context=geoportal_context.get_context(), template_name=template)
    else:
        # create the form
        template = "feedback_form.html"
        feedback_form = FeedbackForm()
        params = {
            "form": feedback_form,
            "btn_send": _("Send"),
            "disclaimer": disclaimer,
        }
        geoportal_context = GeoportalContext(request=request)
        geoportal_context.add_context(params)
        return render(request=request, context=geoportal_context.get_context(), template_name=template)


@check_browser
def service_abo(request: HttpRequest):

    """ Displays the serice abos of a user

    Args:
        request:
    Returns:

    """
    request.session["current_page"] = "show_abo"

    geoportal_context = GeoportalContext(request)
    context_data = geoportal_context.get_context()
    if context_data['dsgvo'] == 'no' and context_data['loggedin'] == True:
        return redirect('useroperations:change_profile')

    template = "show_abo.html"

    geoportal_context = GeoportalContext(request=request)
    return render(request=request, context=geoportal_context.get_context(), template_name=template)

@check_browser
def open_linked_data(request: HttpRequest):

    """ Open Linked Data Page

    Args:
        request:
    Returns:

    """
    request.session["current_page"] = "open_linked_data"

    geoportal_context = GeoportalContext(request)
    context_data = geoportal_context.get_context()
    if context_data['dsgvo'] == 'no' and context_data['loggedin'] == True:
        return redirect('useroperations:change_profile')

    template = "open_linked_data.html"

    geoportal_context = GeoportalContext(request=request)
    return render(request=request, context=geoportal_context.get_context(), template_name=template)


def incompatible_browser(request: HttpRequest):
    """ Renders a template about how the user's browser is a filthy peasants tool.

    Args:
        request: The incoming request
    Returns:
         A rendered view
    """
    request.session["current_page"] = "incompatible"
    template = "unsupported_browser.html"
    params = {

    }
    return render(request, template_name=template, context=params)


def handle500(request: HttpRequest, template_name="500.html"):
    """ Handles a 404 page not found error using a custom template

    Args:
        request:
        exception:
        template_name:
    Returns:
    """
    response = render_to_response(template_name, GeoportalContext(request).get_context())
    response.status_code = 500
    return response