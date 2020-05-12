<?php
# $Id: login.php 7138 2010-11-16 14:37:08Z christoph $
# Copyright (C) 2002 CCGIS
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2, or (at your option)
# any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.

//require_once dirname(__FILE__) . "/../../conf/mapbender.conf";
require_once dirname(__FILE__) ."/../../core/globalSettings.php";
require_once dirname(__FILE__) . "/../classes/class_connector.php";
require_once dirname(__FILE__) . "/../classes/class_administration.php";
require_once dirname(__FILE__) . "/../classes/MB-User.class.php";


if(empty($_SESSION["mb_user_id"])) die("Sie sind nicht angemeldet!");


$msg = array(
	"successMessage" => "Update erfolgreich",
	"errorLoadUser" => "Benutzer konnte nicht geladen werden.",
	"userExists" => "Benutzername bereits vergeben.",
	"invalidEmail" => "E-Mailadresse nicht korrekt.",
	"invalidPassword" => "Passwörter müssen identisch sein",
	"invalidOldPassword" => "Altes Passwort nicht korrekt gesetzt",
	"errorUpdate" => "Benutzerdaten konnten nicht aktualisiert werden"
);


$error = "";
$success = "";

	/***************************************************************************
	 * ANBINDUNG CMS
	 **************************************************************************/
	if(isset($_REQUEST['lang'])) $lang = $_REQUEST['lang']; // Sprachunterscheidung
	else $lang = 'de_DE';


	$pageULR = 'http://'.$_SERVER["HTTP_HOST"].'/'; // URL der Seite


	$helpURL = "";
	$contactURL = "";
	if(strstr($lang,'en')){
			$contactURL = "../../../en/kontakt.html";
			$helpURL = $pageULR."en/hilfe.html";
		$x = new connector($pageULR.'index.php/en/cardviewer/kartenvieweren?sid='.session_id());
		$htmlstring = $x->file;
		//$htmlstring = file_get_contents($pageULR.'index.php/en/cardviewer/kartenvieweren?sid='.session_id());
	}
	else if(strstr($lang,'fr')){
			$contactURL = "../../../fr/kontakt.html";
			$helpURL = $pageULR."fr/hilfe.html";
		$x = new connector($pageULR.'index.php/fr/cardvue/kartenviewerfr?sid='.session_id());
		$htmlstring = $x->file;
		//$htmlstring = file_get_contents($pageULR.'index.php/fr/cardvue/kartenviewerfr?sid='.session_id());
	}
	else{
			$contactURL = "../../../de/kontakt.html";
			$helpURL = $pageULR."de/hilfe.html";
		$x = new connector($pageULR.'index.php/de/kartenviewer/kartenviewerde?sid='.session_id());
		$htmlstring = $x->file;
		//$htmlstring = file_get_contents($pageULR.'index.php/de/kartenviewer/kartenviewerde?sid='.session_id());
	}

	// GET HEADER - START
	ob_end_clean();
	ob_start();

	
	/***************************************************************************
	 * 
	 **************************************************************************/
	
	$mb = new MB_User();

	if(!$mb->loadUserById($_SESSION["mb_user_id"])) {
		die($msg["errorLoadUser"]);
	}

	if(isset($_POST["submit"])) {
		$adm = new administration();

		// ALTES PASSWORT TESTEN
		if(!isset($_POST["old_pw"]) OR md5($_POST["old_pw"]) != $mb->userGet("mb_user_password")) {
			$error = $msg["invalidOldPassword"];
		}	
		
		// USERNAME UND EMAIL TESTEN
		if(!$adm->isValidEmail($_POST["email"])) $error = $msg["invalidEmail"];
		if(isset($_POST["pw1"]) AND $_POST["pw1"] != $_POST["pw2"])  $error = $msg["invalidPassword"];


		if(empty($error)) {

			$userdata = array();
			$userdata["mb_user_email"] = $_POST["email"];
			
			//Neues Passwort wurde gesetzt
			$userdata["mb_user_new_password"] = "FALSE";

			if(trim($_POST["pw1"])!="") {
				$userdata["mb_user_password"] = md5($_POST["pw1"]);
				$userdata["mb_user_digest"] = md5($mb->userGet("mb_user_name").";".$mb->userGet("mb_user_email").":".REALM.":".$_POST["pw1"]);
				$userdata["mb_user_aldigest"] = md5($mb->userGet("mb_user_name").":".REALM.":".$_POST["pw1"]);
				}
				
			if(trim($_POST["description"])!="") 
				$userdata["mb_user_description"] = $_POST["description"];


			if($mb->updateUser($userdata)) {
				$success = $msg["successMessage"];
			} else $error = $msg["errorUpdate"];
		}
	}


	if(!$mb->loadUserById($_SESSION["mb_user_id"])) {
		die($msg["errorLoadUser"]);
	}

	
?>
<meta http-equiv="cache-control" content="no-cache">
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="expires" content="0">
<META http-equiv="Content-Style-Type" content="text/css">
<META http-equiv="Content-Script-Type" content="text/javascript">
<?php
echo '<meta http-equiv="Content-Type" content="text/html; charset='.CHARSET.'">';
?>
<title>Profil</title>
<script type="text/javascript" src="../extensions/jquery-ui-1.8.1.custom/js/jquery-1.4.2.min.js"></script>

<link rel="stylesheet" type="text/css" href="../css/login.css" />
<link rel="shortcut icon" href="../img/favicon.ico" />

<script type="text/javascript">
	//Passwortstärke prüfen
	$(document).ready(function() {
		$('#pw1').keyup(function() {
			var validatePw = pruefStaerke($('#pw1').val());
		});
	});

	function pruefStaerke(password) {
		var staerke = 0; // interner Wert

		if(password.length > 7) {
            staerke += 1;
            $('#minimum span').show();
            $('#minimum').addClass('boldgreen');
        }
        else {
            $('#minimum span').hide();
            $('#minimum').removeClass('boldgreen');
        }

        if(password.match(/([a-z])/) && password.match(/([A-Z])/)  ) {     
            staerke += 1;
            $('#grossundklein span').show();
            $('#grossundklein').addClass('boldgreen');
        } 
        else {
            $('#grossundklein span').hide();
            $('#grossundklein').removeClass('boldgreen');
        }

        if(password.match(/([0-9])/) && password.match(/([a-zA-Z])/)) {
            staerke += 1;
            $('#buchstabenundzahlen span').show();
            $('#buchstabenundzahlen').addClass('boldgreen');
        }
        else {
            $('#buchstabenundzahlen span').hide();
            $('#buchstabenundzahlen').removeClass('boldgreen');
        }

        if(password.match(/([!,%,&,@,#,$,^,*,?,_,~])/)){
            staerke += 1;
            $('#sonderzeichen span').show();
            $('#sonderzeichen').addClass('boldgreen');
        }
        else{
            $('#sonderzeichen span').hide();
            $('#sonderzeichen').removeClass('boldgreen');
        }

        if(password.match(/(.*[!,%,&,@,#,$,^,*,?,_,~].*[!,%,&,@,#,$,^,*,?,_,~])/)) {
            staerke += 1;
        }

        if (password.length < 6) {
			$('#pw1').removeClass();
			$('#pw1').addClass('kurz');
		}
		if (staerke < 2 ) {
			$('#pw1').removeClass();
			$('#pw1').addClass('schwach');
		} 
		else if (staerke == 2 ) {
			$('#pw1').removeClass();
			$('#pw1').addClass('gut');
		}
		else if (staerke == 3 ) {
            $('#pw1').removeClass();
            $('#pw1').addClass('stark');
        }  
		else {
			$('#pw1').removeClass();
			$('#pw1').addClass('sehrstark');
		}

		if (password.length == 0) {
			$('#pw1').removeClass();
			//$('#pw1').addClass('leer');
		}
	}
</script>

<style>
	.leer{background-color:#ffffff;}
	.kurz{background-color:#ff2b00;}
	.schwach{background-color: #ff7400;}
	.gut{background-color:#bed534;}
	.stark{background-color:#4fb427;}
	.sehrstark{background-color:#318311;}

    .boldgreen{font-weight:bold;color:green;}

    #minimum span{display:none;padding-left:10px;color:green;}
    #grossundklein span{display:none;padding-left:10px;color:green;}
    #buchstabenundzahlen span{display:none;padding-left:10px;color:green;}
    #sonderzeichen span{display:none;padding-left:10px;color:green;}
</style>

<?php
	
	// GET HEADER - END
	$HEAD = ob_get_contents();
	

	// GET BODY - START
	ob_end_clean();
	ob_start();
?>

<div id="login_form">
<?php
	if($mb->userGet('mb_user_new_password') == "t") echo '<div class="error">Bitte setzen Sie Ihr Passwort neu!</div><br/>';
	if(!empty($error)) echo '<div class="error">'.$error.'</div>';
	if(!empty($success)) echo '<div class="success">'.$success.'</div>';
?>
	
	<h3>Benutzerdaten für <?php echo $mb->userGet("mb_user_name"); ?></h3>
	<form class="mygeoportal" id="registrationForm" name="registrationForm" action ="" method="POST">
		<fieldset id="fields">
			<label>Altes Passwort:</label>
			<input type="password" maxlength="50" name="old_pw" /><br/>
			<label id="pw1Label">Passwort:</label>
			<input type="password" maxlength="50" name="pw1" id="pw1" /><br/>
			<label>Passwortwiederholung:</label>
			<input type="password" maxlength="50" name="pw2" /><br/>
			<label>E-Mail:</label>
			<input type="text" maxlength="50" name="email" class="text" value="<?php echo $mb->userGet("mb_user_email"); ?>" /><br/>
			<label>Kommentare/Anregungen:</label>
			<textarea name="description" rows="5" cols="5"><?php echo $mb->userGet("mb_user_description"); ?></textarea><br/>
		</fieldset>
		<fieldset class= "control" id="_button">
			<input type="submit" name="submit" value="Speichern" />
		</fieldset>
		<fieldset>
			<b>Regeln f&uuml;r ein sicheres Passwort:</b>
			<ul>
			    <li id="minimum">mindestens 6 Zeichen lang<span>&radic;</span></li>
			    <li id="grossundklein">Gro&szlig;- und Kleinbuchstaben vorhanden<span>&radic;</span></li>
			    <li id="buchstabenundzahlen">nicht nur Buchstaben, auch Zahlen verwenden<span>&radic;</span></li>
			    <li id="sonderzeichen">mindestens 1, besser 2 Sonderzeichen verwenden<span>&radic;</span></li>
			</ul>
		</fieldset>
	</form>
</div>

<?php
	// GET BODY - END
	$BODY = ob_get_contents();
	ob_end_clean();

	$htmlstring = str_replace('[%MB_HEADER%]', $HEAD, $htmlstring);
	$htmlstring = str_replace('[%MB_CONTENT%]', $BODY, $htmlstring);
	echo $htmlstring;
?>