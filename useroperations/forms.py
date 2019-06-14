from crispy_forms.helper import FormHelper
from crispy_forms.layout import Submit
from django import forms
from django.utils.translation import ugettext_lazy as _
from captcha.fields import CaptchaField
from Geoportal.settings import HOSTNAME
from django.utils.safestring import mark_safe

class FeedbackForm(forms.Form):
    first_name = forms.CharField(max_length=200, label=_("First name"), required=False)
    family_name = forms.CharField(max_length=200, label=_("Family name"), required=False)
    email = forms.EmailField(label=_("E-Mail address"))
    message = forms.CharField(label=_("Your Message"), widget=forms.Textarea(attrs={"maxlength": 3000}))
    captcha = CaptchaField(label=_("I'm not a robot"))

class RegistrationForm(forms.Form):
    name = forms.CharField(max_length=25, label=_("Username"))
    password = forms.CharField(widget=forms.PasswordInput(attrs={'title':_("Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters"),'pattern':"(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"}), label=_("Password"))
    passwordconfirm = forms.CharField(widget=forms.PasswordInput, label=_("PasswordConfirmation"))
    email = forms.EmailField(required=True)
    department = forms.CharField(max_length=50, label=_("Departement"),required=False)
    description = forms.CharField(max_length=1000, label=_("Description"),required=False)
    phone = forms.CharField(max_length=20, label=_("Phone"),required=False)
    organization = forms.CharField(max_length=15, label=_("Organization"), required=False)
    newsletter = forms.BooleanField(initial=True, label=_("I want to sign up for the newsletter"),required=False)
    survey = forms.BooleanField(initial=True, label=_("I want to participate in surveys"),required=False)
    dsgvo = forms.BooleanField(initial=False, label=_("I understand and accept that my data will be automatically processed and securely stored, as it is stated in the general data protection regulation (GDPR)."), required=True)
    captcha = CaptchaField(label=_("I'm not a robot"))

class LoginForm(forms.Form):
    name = forms.CharField(max_length=25, label=_("Username"))
    password = forms.CharField(widget=forms.PasswordInput, label=_("Password"))

class ChangeProfileForm(forms.Form):
    #name = forms.CharField(max_length=25, label=_("Username"),disabled=True)
    oldpassword = forms.CharField(widget=forms.PasswordInput, label=_("Old password "))
    password = forms.CharField(widget=forms.PasswordInput(attrs={'title': _("Must contain at least one number and one uppercase and lowercase letter, and at least 9 or more characters"),'pattern': "(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"}), label=_("New password "), required=False)
    passwordconfirm = forms.CharField(widget=forms.PasswordInput, label=_("Password confirmation"), required=False)
    email = forms.EmailField(required=True)
    department = forms.CharField(max_length=50, label=_("Departement"),required=False)
    description = forms.CharField(max_length=1000, label=_("Description"),required=False)
    phone = forms.CharField(max_length=20, label=_("Phone "),required=False)
    organization = forms.CharField(max_length=15, label=_("Organization "),required=False)
    newsletter = forms.BooleanField(initial=True, label=_("I want to sign up for the newsletter"),required=False)
    survey = forms.BooleanField(initial=True, label=_("I want to participate in surveys"),required=False)
    create_digest = forms.BooleanField(initial=False, label=_("Use HTTP Digest Authentication for secured Services"), required=False)
    dsgvo = forms.BooleanField(initial=False, label=mark_safe(_("I understand and accept that my data will be automatically processed and securely stored, as it is stated in the general data protection regulation (GDPR).") + '(<a href="/article/Datenschutz" target="_blank">'+str(_("privacy policy"))+'</a>)'), required=False)

class PasswordResetForm(forms.Form):
    name = forms.CharField(max_length=25, label=_("Username"))
    email = forms.EmailField(required=True)

class DeleteProfileForm(forms.Form):
    helper = FormHelper()
