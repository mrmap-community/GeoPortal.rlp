<?php
session_start();
?>
<!DOCTYPE html>

<html lang="de" xmlns="http://www.w3.org/1999/xhtml">

	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />

		<title>www.geoportal.rlp.de | Metadaten</title>

		<base href="<?php print "http://".$_SERVER["SERVER_NAME"]."/portal/"; ?>" />

		<meta name="author" content="Q4U GmbH" />
		<meta name="publisher" content="Rheinland-Pfalz" />
		<meta name="copyright" content="Rheinland-Pfalz" />
		<meta name="description" content="Metadaten" />
		<meta name="keywords" content="Metadaten" />

		<link rel="stylesheet" type="text/css" href="/fileadmin/design/geoportal.rlp.css" />

		<script type="text/javascript">
			function tou(tou) {
				fenster=window.open("/fileadmin/scripts/termsofuse.php?tou="+tou, "TermsOfUse", "width=500,height=500,left=100,top=100,scrollbars=yes,resizable=no");
				fenster.focus();
				return false;
			}
		</script>
</head>

<?php

$URL="http://".$_SERVER["SERVER_NAME"].$_SERVER['REDIRECT_URL'];

include_once("chkform.php");
include_once(dirname(__FILE__)."/../function/crypt.php");
include("language.php");

if($_REQUEST["service"]!="") {
	DecodeParameter($_REQUEST["service"]);
}

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

$Message["Name"] = $L["ErrName"];
$Message["EMail"] = $L["ErrEMail"];
$Message["Telefon"] = $L["ErrTelefon"];
$Message["PLZ"] = $L["ErrPLZ"];
$Message["Ort"] = $L["ErrOrt"];
$Message["Firma"] = $L["ErrFirma"];
$Message["Dienststelle"] = $L["ErrDienststelle"];
$Message["Position"] = $L["ErrPosition"];
$Message["Beschreibung"] = $L["ErrBeschreibung"];

$Default["Name"] = $L["Name"];
$Default["EMail"] = $L["EMail"];
$Default["Telefon"] = $L["Telefon"];
$Default["PLZ"] = $L["PLZ"];
$Default["Ort"] = $L["Ort"];
$Default["Firma"] = $L["Firma"];
$Default["Dienststelle"] = $L["Dienststelle"];
$Default["Position"] = $L["Position"];
$Default["Beschreibung"] = $L["Beschreibung"];

$Checkfields="Name,true,text|EMail,true,email";


if ($_REQUEST["act"]!="register" || !chkFormular()) {
	if($_REQUEST["act"]!="register") {
		$DATA["Name"]=$_SESSION['mb_user_name'];
		$DATA["EMail"]=$_SESSION['mb_user_email'];
		$DATA["Dienststelle"]=$_SESSION['mb_user_department'];
		$DATA["Firma"]=$_SESSION['mb_user_organisation_name'];
		$DATA["Position"]=$_SESSION['mb_user_position_name'];
		$DATA["Telefon"]=$_SESSION['mb_user_phone'];
		foreach($Default as $key => $value) {
			if($DATA[$key]=="") $DATA[$key]=$value;
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

<body id="top" class="popup">

	<div id="right_grey"><div id="left_red"></div></div>

	<a id="close" href="javascript:window.close()">Fenster schließen <img src="/fileadmin/design/icn_close.png" width="18" height="18" alt="" /></a>

		<div id="center" class="content" style="padding:2em">

			<form class="mygeoportal" action="<?php print $url;?>" method="post" onsubmit="return chkFormular(this);">

			<h2><span><?php print $L["AntragsstellungFreischaltung"]; ?></span></h2>

			<fieldset>
			<label class="text" for="name"><strong title="Pflichtfeld"><?php print $L["Benutzername"]; ?></strong></label>
			<label class="text"><strong><?php print $DATA["Name"]; ?></strong></label>
			<br class="clr" />
			</fieldset>

			<fieldset>
			<label class="text" for="email"><strong title="Pflichtfeld"><?php print $L["EMail"]; ?></strong></label>
			<label class="text"><strong><?php print $DATA["EMail"]; ?></strong></label>
			<br class="clr" />
			</fieldset>

			<fieldset>
			<label class="text" for="name"><strong title="Pflichtfeld"><?php print $L["RealName"]; ?></strong></label>
			<input class="text" id="realname" type="text" name="RealName" maxlength="40" value="" onfocus="clearField(this)" />
			<br class="clr" />
			</fieldset>

			<fieldset>
			<label class="text" for="telefon"><strong><?php print $L["Telefon"]; ?></strong></label>
			<input class="text" id="telefon" type="text" name="Telefon" maxlength="40" value="" onfocus="clearField(this)" />
			<br class="clr" />
			</fieldset>

			<fieldset>
			<label class="text" for="plz"><strong title="Pflichtfeld"><?php print $L["PLZ"]; ?></strong></label>
			<input class="text" id="plz" type="text" name="PLZ" maxlength="40" value="" onfocus="clearField(this)" />
			<br class="clr" />
			</fieldset>

			<fieldset>
			<label class="text" for="ort"><strong title="Pflichtfeld"><?php print $L["Ort"]; ?></strong></label>
			<input class="text" id="ort" type="text" name="Ort" maxlength="40" value="" onfocus="clearField(this)" />
			<br class="clr" />
			</fieldset>

			<fieldset>
			<label class="text" for="firma"><strong><?php print $L["Firma"]; ?></strong></label>
			<input class="text" id="firma" type="text" name="Firma" maxlength="40" value="" onfocus="clearField(this)" />
			<br class="clr" />
			</fieldset>

			<fieldset>
			<label class="text" for="dienststelle"><strong><?php print $L["Dienststelle"]; ?></strong></label>
			<input class="text" id="dienststelle" type="text" name="Dienststelle" maxlength="40" value="" onfocus="clearField(this)" />
			<br class="clr" />
			</fieldset>

			<fieldset>
			<label class="text" for="position"><strong><?php print $L["Position"]; ?></strong></label>
			<input class="text" id="position" type="text" name="Position" maxlength="40" value="" onfocus="clearField(this)" />
			<br class="clr" />
			</fieldset>

			<fieldset class="textarea">
			<label class="textarea" for="beschreibung"><strong title="Pflichtfeld"><?php print $L["Bemerkungen"]; ?></strong></label>
			<textarea id="beschreibung" cols="" rows="4" name="Beschreibung" onfocus="clearField(this)"></textarea>
			<br class="clr" />
			</fieldset>

			<p>
			Der Nutzer <?php print $DATA["Name"]; ?> (E-Mail: <?php print $DATA["EMail"]; ?>) beantragt ueber das Geoportal.rlp die Freischaltung ihres Dienstes/Layers <?php print $_REQUEST["TITLE"]; ?> mit der ID <?php print $_REQUEST["ID"]; ?>.
			</p>

			<fieldset class="control">
			<input type="hidden" name="act" value="register" />
			<input type="hidden" name="service" value="<?php print $_REQUEST["service"]; ?>" />
			<input type="hidden" id="CHECK" name="CHECK" value="<?php print $Checkfields; ?>" />
			<input type="hidden" name="Name" value="<?php print $DATA["Name"]; ?>" />
			<input type="hidden" name="EMail" value="<?php print $DATA["EMail"]; ?>" />
			<input type="submit" value="<?php print $L["Absenden"]; ?>" />
			</fieldset>

			</form>

			<?php
			$values='id='.$_REQUEST["ID"].'ÿtype='.$_REQUEST["TYPE"].'ÿlang='.$_REQUEST["lang"];
			$code=CodeParameter($values);
			print '<p><a onclick="return tou(\''.$code.'\')">Hinweise zur Nutzung des Dienstes</a></p>';
			} else {

			$Subject="Antragsstellung auf die Freigabe eines Dienstes im GeoPortal.rlp";
			//$MailFrom="info@q4u.de";

			$MailFrom="kontakt@geoportal.rlp.de";
			$MailTo=$_REQUEST["TO"];
			//$MailTo="kontakt@lvermgeo.rlp.de";


			//$MailText='Antragsstellung auf die Freigabe eines Dienstes im GeoPortal.rlp';

			$MailText='Der Nutzer '.$_REQUEST["Name"].' (E-Mail: '.$_REQUEST["EMail"].') beantragt ueber das Geoportal.rlp
			die Freischaltung ihres Dienstes/Layers '.$_REQUEST["TITLE"].' mit der ID '.$_REQUEST["ID"].'

			Dienst: '.$_REQUEST["TITLE"].'
			Benutzername: '.$_REQUEST["Name"].'
			E-Mail: '.$_REQUEST["EMail"].'
			Name: '.$_REQUEST["RealName"].'
			Telefon: '.$_REQUEST["Telefon"].'
			PLZ: '.$_REQUEST["PLZ"].'
			Ort: '.$_REQUEST["Ort"].'
			Firma: '.$_REQUEST["Firma"].'
			Dienststelle: '.$_REQUEST["Dienststelle"].'
			Position: '.$_REQUEST["Position"].'
			Bemerkungen: '.$_REQUEST["Beschreibung"];

				if(mail($MailTo,$Subject,utf8_decode($MailText),"From: ".$MailFrom,"-f".$MailFrom)) {
					print "<p>".$L["RegisterServiceOK"]."</p>";
				} else {
					print "<p>".$L["RegisterServiceFalse"]."</p>";
				}
			}

			?>

		</div>

</body>

</html>
