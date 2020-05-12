<?php

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

$Message["Benutzername"] = $L["ErrUBenutzername"];
$Message["EMail"] = $L["ErrEMail"];

$Default["Benutzername"]=$L["Benutzername"];
$Default["EMail"]=$L["EMail"];

$Checkfields="Benutzername,true,text|EMail,true,email";

if ($_REQUEST["act"]!="sendpassword" || !chkFormular()) {
	if($_REQUEST["act"]!="sendpassword") {
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

<h2><?php print $L["Benutzerdaten"]; ?></h2>

<fieldset>
<label for="benutzername"><?php print $L["Benutzername"]; ?>*:</label>
<input class="text" id="benutzername" type="text" name="Benutzername" maxlength="40" value="<?php print $DATA["Benutzername"]; ?>" onfocus="clearField(this)" />
<br class="clr" />
<label for="email"><?php print $L["EMail"]; ?>*:</label>
<input class="text" id="email" type="text" name="EMail" maxlength="40" value="<?php print $DATA["EMail"]; ?>" onfocus="clearField(this)" />
<br class="clr" />
</fieldset>

<fieldset class="control">
<input type="hidden" name="act" value="sendpassword" />
<input type="hidden" id="CHECK" name="CHECK" value="<?php print $Checkfields; ?>" />
<input type="submit" value="<?php print $L["PasswortZusenden"]; ?>" />
</fieldset>

</form>

<?php
} else {

	include(dirname(__FILE__)."/../../../mapbender/http/geoportal/forgotten_password.php");
	
	switch($success) {
		case  1: print "<p>Das Passwort wird Ihnen zugesendet.</p>"; break;
		
		case -1: print "<p>Benutzername oder E-Mail fehlerhaft.</p>"; break;
		case -2: print "<p>Benutzername in Kombination mit der Mailadresse nicht gefunden.</p>"; break;
		case -3: print "<p>Das neue Passwort konnte nicht gespeichert werden.</p>"; break;
		case -4: print "<p>Die E-Mail konnte nicht gesendet werden.</p>"; break;
	}
}

?>

