<?php

function toHtml($wert) { // Program to HTML
  return htmlentities($wert, ENT_QUOTES, "UTF-8");
}

/**
 * Implements ReCaptcha // START
 */
include_once('recaptchalib.php');
$publickey = "6LfxphATAAAAALDnpO2FfOuhE7zX9i8-0r1y2Yjo";
$privatekey = "6LfxphATAAAAAPxdQ4OIuzDQB0xCTJ8f2I-nxgCp";

if ($_REQUEST["act"] == "register") {

  $reCaptchaResponse = recaptcha_check_answer ($privatekey);

}
/**
 * Implements ReCaptcha // STOP
 */

$URL="http://".$_SERVER["SERVER_NAME"].$_SERVER['REDIRECT_URL'];

include_once("chkform.php");
include("language.php");

if($Lang=="en")
	$L=$Language["en"];
else 
	$L=$Language["de"];

global $Message;
global $Default;
global $Checkfields;

$Message["ChkError"] = $L["ChkError"];
$Message["ChkIncomplete"] = $L["ChkIncomplete"];
$Message["ChkGeneral"] = $L["ChkGeneral"];

$Message["Benutzername"] = $L["ErrBenutzername"];
$Message["EMail"] = $L["ErrEMail"];
$Message["Captcha"] = $L["ErrCaptcha"];
$Default["Benutzername"] = $L["Benutzername"];
$Default["EMail"] = $L["EMail"];
$Default["Beschreibung"] = $L["Kommentar"];

$CheckfieldsJS=$Checkfields="Benutzername,true,text|EMail,true,email";

if (!$reCaptchaResponse) {
	$_REQUEST['Captcha']='';
	$Checkfields.='|Captcha,true,text';
}
if ($_REQUEST["act"]!="register" || !chkFormular()) {
	if($_REQUEST["act"]!="register") {
		foreach($Default as $key => $value) {
			$DATA[$key]=$value;
		}
	} else {
		foreach($_REQUEST as $key => $value) {
			$DATA[$key]=$value;
		}
	}		

?>
<script type="text/javascript">
<?php
foreach ($Message as $key => $value){
  print "message[\"".$key."\"] = \"".$value."\";\n";
}
foreach ($Default as $key => $value){
  print "vorgabe[\"".$key."\"] = \"".$value."\";\n";
}
?>
</script>

<form class="mygeoportal" action="<?php print $URL;?>" method="post" onsubmit="return chkFormular(this);">


<fieldset>

<h2><?php print $L["Benutzerdaten"]; ?></h2>

<label for="benutzername"><?php print $L["Benutzername"]; ?>*:</label>
<input class="text" id="benutzername" type="text" name="Benutzername" maxlength="50" value="<?php print toHtml($DATA["Benutzername"]); ?>" onfocus="clearField(this)" />
<br class="clr" />

<label for="email"><?php print $L["EMail"]; ?>*:</label>
<input class="text" id="email" type="text" name="EMail" maxlength="50" value="<?php print toHtml($DATA["EMail"]); ?>" onfocus="clearField(this)" />
<br class="clr" />

<label for="dienststelle"><?php print $L["Dienststelle"]; ?>:</label>
<input class="text" id="dienststelle" type="text" name="Dienststelle" maxlength="50" value="<?php print toHtml($DATA["Dienststelle"]); ?>" onfocus="clearField(this)" />
<br class="clr" />

<label for="beschreibung"><?php print $L["Kommentar"]; ?>:</label>
<textarea id="beschreibung" cols="5" rows="5" name="Beschreibung" onfocus="clearField(this)"><?php print toHtml($DATA["Beschreibung"]); ?></textarea>
<br class="clr" />

<!--$mb_user_phone, $mb_user_department, $mb_user_organisation_name, $mb_user_position_name, $mb_user_city, $mb_user_postal_code-->
<label for="telefon"><?php print $L["Telefon"]; ?>:</label>
<input class="text" id="telefon" type="text" name="telefon" value="<?php print toHtml($DATA["Telefon"]); ?>" onfocus="clearField(this)" />
<br class="clr" />

<!-- <label for="dienststelle"><?php print $L["Dienststelle"]; ?>:</label>
<input class="text" id="dienststelle" type="text" name="dienststelle" onfocus="clearField(this)" value="<?php print toHtml($DATA["Dienststelle"]); ?> />-->

<label for="organisation"><?php print $L["Organisation"]; ?>:</label>
<input class="text" id="organisation" type="text" name="organisation" value="<?php print toHtml($DATA["Organisation"]); ?>"  onfocus="clearField(this)" />
<br class="clr" />

</fieldset>

<fieldset class="control">
<label for="captcha"><?php print $L["Captcha"]; ?>*:</label>
    <style>
        .g-recaptcha {
            float:left;
        }
    </style>
    <?php
        echo recaptcha_get_html($publickey);
    ?>
</fieldset>

<fieldset class="control">
<input type="hidden" name="act" value="register" />
<input type="hidden" id="CHECK" name="CHECK" value="<?php print $Checkfields; ?>" />
<input type="submit" value="<?php print $L["Anmelden"]; ?>" />
</fieldset>

</form>
<?php print $L["HinweisRegistrierung"]; ?>




<?php
} else {

	$mb_user_name=$_REQUEST["Benutzername"];
	$mb_user_description=$_REQUEST["Beschreibung"];
	$mb_user_email=$_REQUEST["EMail"];
	$mb_user_phone=$_REQUEST["Telefon"];
	$mb_user_department=$_REQUEST["Dienststelle"];
	$mb_user_organisation_name=$_REQUEST["Organisation"];

	$mailBody1=$L["mailBodyRegistrierung1"];
	$mailBody2=$L["mailBodyRegistrierung2"];
	$mailBody3=$L["mailBodyRegistrierung3"];
	include(dirname(__FILE__)."/../../../mapbender/http/geoportal/insertUserDataIntoDb.php");

	#echo "User exists: ".$userAlreadyExists."<br>";
	#echo "email Valid: ".$emailValid."<br>";

	if($emailValid==0){
		print "<p>".$L["EmailInvalid"]."</p>";	
		return;
	}
	if ($registerAsGuest) {
		print "<p>".$L["RegisterNotOK"]."</p>";	
		return;	
	}
	if($userAlreadyExists==1){
		print "<p>".$L["RegisterNotOK"]."</p>";	
		return;
	}
	print "<p>".$L["RegisterOK"]."</p>";

}

?>


