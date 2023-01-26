from crispy_forms.helper import FormHelper
from django import forms
from django.utils.translation import ugettext_lazy as _
from captcha.fields import CaptchaField
from django.utils.safestring import mark_safe
from Geoportal.settings import USE_RECAPTCHA

class FeedbackForm(forms.Form):
    first_name = forms.CharField(max_length=200, label=_("First name"), required=False, widget=forms.TextInput(attrs={'title':_("Please enter your first name.")}))
    family_name = forms.CharField(max_length=200, label=_("Family name"), required=False, widget=forms.TextInput(attrs={'title':_("Please enter your last name.")}))
    email = forms.EmailField(label=_("E-Mail address"),  widget=forms.EmailInput(attrs={'title':_("Please enter your email.")}))
    message = forms.CharField(label=_("Your Message"), widget=forms.Textarea(attrs={"maxlength": 3000, 'title':_("Please enter your message.")}))
    identity = forms.CharField(max_length=255, label=_("identity"), required=False, widget=forms.TextInput(attrs={'title':_("Identity.")}))
    if USE_RECAPTCHA == 0:
        captcha = CaptchaField(label=_("I'm not a robot"))

class RegistrationForm(forms.Form):
    name = forms.CharField(max_length=100, label=_("Username"), widget=forms.TextInput(attrs={'title':_("Please enter your username.")}))
    password = forms.CharField(widget=forms.PasswordInput(attrs={'title': _("Please enter your password with at least 9 characters."), 'pattern': ".{9,}", 'oninvalid':"this.setCustomValidity('" + str(_("Please use at least 9 characters.")) + "')", 'onchange':"try{setCustomValidity('')}catch(e){}", 'oninput':"setCustomValidity(' ')"}), label=_("Password"))
    passwordconfirm = forms.CharField(widget=forms.PasswordInput(attrs={'title': _("Password confirmation."), 'pattern': ".{9,}"}), label=_("PasswordConfirmation"))
    email = forms.EmailField(required=True, widget=forms.EmailInput(attrs={'title':_("Please enter your email.")}))
    organization = forms.CharField(max_length=100, label=_("Organization"), required=False, widget=forms.TextInput(attrs={'title': _("Please enter the organization you are working for.")}))
    department = forms.CharField(max_length=100, label=_("Departement"), required=False, widget=forms.TextInput(attrs={'title':_("Please enter the departement you are working in.")}))
    phone = forms.CharField(max_length=100, label=_("Phone"), required=False,widget=forms.TextInput(attrs={'title': _("Please enter your phone number.")}))
    description = forms.CharField(max_length=255, label=_("Description"), required=False, widget=forms.TextInput(attrs={'title':_("Please enter a description.")}))
    identity = forms.CharField(max_length=255, label=_("identity"), required=False, widget=forms.TextInput(attrs={'title':_("Identity.")}))
    newsletter = forms.BooleanField(initial=True, label=_("I want to sign up for the newsletter"), required=False, widget=forms.CheckboxInput(attrs={'title':_("Sign up for the newsletter.")}))
    survey = forms.BooleanField(initial=True, label=_("I want to participate in surveys"), required=False, widget=forms.CheckboxInput(attrs={'title':_("Participate in surveys.")}))
    dsgvo = forms.BooleanField(initial=False, label=_("I understand and accept that my data will be automatically processed and securely stored, as it is stated in the general data protection regulation (GDPR)."), required=True, widget=forms.CheckboxInput(attrs={'title':_("Accept privacy policy.")}))
    if USE_RECAPTCHA == 0:
        captcha = CaptchaField(label=_("I'm not a robot"))

class LoginForm(forms.Form):
    name = forms.CharField(max_length=100, label=_("Username"), widget=forms.TextInput(attrs={'title':_("Please enter your username.")}))
    password = forms.CharField(widget=forms.PasswordInput(attrs={'title': _("Please enter your password.")}), label=_("Password"))

class ChangeProfileForm(forms.Form):
    #name = forms.CharField(max_length=25, label=_("Username"),disabled=True)
    oldpassword = forms.CharField(required=False, widget=forms.PasswordInput(attrs={'title': _("Please enter your password.")}), label=_("Current password"))
    password = forms.CharField(widget=forms.PasswordInput(attrs={'title': _("Please enter your password with at least 9 characters. Allowed special chars are: @#$%&+=!:-_"), 'pattern': ".{9,}"}), label=_("New password "), required=False)
    passwordconfirm = forms.CharField(widget=forms.PasswordInput(attrs={'title': _("Password confirmation."), 'pattern':".{9,}"}), label=_("Password confirmation"), required=False)
    email = forms.EmailField(required=True, widget=forms.EmailInput(attrs={'title':_("Please enter your email.")}) )
    organization = forms.CharField(max_length=100, label=_("Organization"), required=False, widget=forms.TextInput(attrs={'title': _("Please enter the organization you are working for.")}))
    department = forms.CharField(max_length=100, label=_("Departement"), required=False, widget=forms.TextInput(attrs={'title':_("Please enter the departement you are working in.")}))
    phone = forms.CharField(max_length=100, label=_("Phone "), required=False,widget=forms.TextInput(attrs={'title': _("Please enter your phone number.")}))
    description = forms.CharField(max_length=1000, label=_("Description"), required=False, widget=forms.TextInput(attrs={'title':_("Please enter a description.")}))
    preferred_gui = forms.CharField(max_length=100, label=mark_safe(_("Preferred viewer") + ' (<a href="/mediawiki/index.php/PreferredViewer" target="_blank">' + str(_("Info")) + '</a>)'), required=False, widget=forms.Select(choices=[('Geoportal-RLP','Geoportal-RLP-Classic'),('Geoportal-RLP_2019','Geoportal-RLP-2019')]))
    newsletter = forms.BooleanField(initial=True, label=_("I want to sign up for the newsletter"), required=False, widget=forms.CheckboxInput(attrs={'title':_("Sign up for the newsletter.")}))
    survey = forms.BooleanField(initial=True, label=_("I want to participate in surveys"), required=False, widget=forms.CheckboxInput(attrs={'title':_("Participate in surveys.")}))
    create_digest = forms.BooleanField(initial=False, label=_("Use HTTP Digest Authentication for secured Services"), required=False, widget=forms.CheckboxInput(attrs={'title':_("Use HTTP Digest Authentication for secured Services.")}))
    dsgvo = forms.BooleanField(initial=False, label=mark_safe(_("I understand and accept that my data will be automatically processed and securely stored, as it is stated in the general data protection regulation (GDPR).") + '(<a href="/article/Datenschutz" target="_blank">' + str(_("privacy policy")) + '</a>)'), required=False, widget=forms.CheckboxInput(attrs={'title':_("Accept privacy policy.")}))


class PasswordResetForm(forms.Form):
    name = forms.CharField(max_length=100, label=_("Username"), widget=forms.TextInput(attrs={'title':_("Please enter your username.")}))
    email = forms.EmailField(required=True, widget=forms.EmailInput(attrs={'title':_("Please enter your email.")}))

class DeleteProfileForm(forms.Form):
    confirmation_password = forms.CharField(required=True, widget=forms.PasswordInput(attrs={'title': _("Please enter your password.")}), label=_("Confirm with password"))
    helper = FormHelper()
