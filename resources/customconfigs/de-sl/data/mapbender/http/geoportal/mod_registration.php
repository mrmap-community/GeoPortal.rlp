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

# Integration reCAPTCHA
require_once('../extensions/recaptcha-php-1.11/recaptchalib.php');
$publickey = "6LeP0-gSAAAAAHgNp130RISMpg5FjnPejC8JbfrL";
$privatekey = "6LeP0-gSAAAAALxmaieAtwU5penJ5sAkb35i9JcR";

$group = "guest";

$msg = array(
	"usernameEmpty" => "Benutzername ausfüllen.",
	"mailSubject" => "Ihre Registrierung",
	"mailBody" => "\nVielen Dank\nBenutzername : ##NAME##\nPasswort : ##PW##",
	"errorCreateGroup" => "Benutzergruppe konnt nicht erstellt werden.\n Wenden Sie sich an ihren Admin.",
	"successMessage" => "vielen Dank",
	"errorMail" => "Mail konnte nicht versendet werden",
	"errorInsertUser" => "Benutzer konnte nicht eingetragen werden.",
	"userExists" => "Benutzername bereits vergeben.",
	"invalidEmail" => "E-Mailadresse nicht korrekt."
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



if(isset($_POST["submit"])) {
	$resp = recaptcha_check_answer ($privatekey,
			$_SERVER["REMOTE_ADDR"],
			$_POST["recaptcha_challenge_field"],
			$_POST["recaptcha_response_field"]);

	if (!$resp->is_valid) {
		$error = "Sicherheitscode nicht korrekt eingegeben.";
	}
	
	$adm = new administration();
	$mb = new MB_User();

	// USERNAME UND EMAIL TESTEN
	if(trim($_POST["username"]) == "") $error = $msg["usernameEmpty"];
	else if(!$adm->isValidEmail($_POST["email"])) $error = $msg["invalidEmail"];
			
	$username = $_POST["username"];
	$email = $_POST["email"];
	$description = @$_POST["description"];
	
	if(empty($error)) {
		$password = $mb->generateUserPw();

		if(!$mb->loadUserByName($username)) {

			// BENUTZER HINZUFÜGEN
			$mb->insertUser(array(
				"mb_user_name" => $username,
				"mb_user_password" => md5($password),
				"mb_user_email" => $email,
				"mb_user_description" => $description,
				"mb_user_owner" => 1,
				"mb_user_new_password" => "TRUE"
			));

			// BENUTZER LADEN UND IN GRUPPE "public" HINZUFÜGEN
			if($mb->loadUserByName($username)) {
				$groupID = $mb->groupExists($group);
				
				// GRUPPE EXISTIERT NICHT
				if($groupID === false) {
					// GRUPPE ERSTELLEN
					$mb->insertGroup($group);
					$groupID = $mb->groupExists($group);
				}
				

				// GRUPPE AN BENUTZER HÄNGEN
				if($groupID !== false) {
					$mb->userSetGroups(array($group));
				} else {
					$error = $msg["errorCreateGroup"];
				}

				// MAIL VERSENDEN
				$msg["mailBody"] = str_replace("##NAME##", $username, $msg["mailBody"]);
				$mailBody = str_replace("##PW##", $password, $msg["mailBody"]);

				$mailResult = $adm->sendEmail(MAILADMIN, MAILADMINNAME, $email, $username, $msg["mailSubject"], $mailBody, $error_msg);				

				if($mailResult) {
					$success = $msg["successMessage"];
				} else $error = $msg["errorMail"];			
			} else $error = $msg["errorInsertUser"];
		} else $error = $msg["userExists"];

	}
}



?>

<link rel="stylesheet" type="text/css" href="../css/login.css" />
<link rel="shortcut icon" href="../img/favicon.ico" />

<?php
	
	// GET HEADER - END
	$HEAD = ob_get_contents();
	

	// GET BODY - START
	ob_end_clean();
	ob_start();
?>
<!-- weitere Optionen gesetzt für reCAPTCHA -->
 <script type="text/javascript">
 var RecaptchaOptions = {
    theme : 'clean',
    lang : 'de'
 };
 </script>

<div id="login_form">
<?php
	if(!empty($error)) echo '<div class="error">'.$error.'</div>';
	
	if(!empty($success)) {
		/*
		 * @todo remove pw
		 */
		echo '<div class="success">'.$success." pw : ".$password.'</div>';
	} else {
?>
	
	<h3>Benutzerdaten</h3>
	<form class="mygeoportal" id="registrationForm" name="registrationForm" action ="" method="POST">
		<fieldset id="fields">
			<label>Benutzername*:</label>
			<input type="text" maxlength="50" name="username" class="text" value="<?php echo @htmlspecialchars($_POST["username"]); ?>" /><br/>
			<label>E-Mail*:</label>
			<input type="text" maxlength="50" name="email" class="text" value="<?php echo @htmlspecialchars($_POST["email"]); ?>" /><br/>
			<label for="description">Kommentare/Anregungen:</label>
			<textarea name="description" id="description" rows="5" cols="5"><?php echo @htmlspecialchars($_POST["description"]); ?></textarea><br/>
		</fieldset>
		<fieldset id="control_code">
			<label>Sicherheitscode*:</label>
		    <?php echo recaptcha_get_html($publickey); ?>	
		</fieldset>
		<fieldset class= "control" id="_button">
			<input type="submit" name="submit" value="Anmelden" />
		</fieldset>
	</form>

	<div>
		<p>Nach Absenden des Formulars wird Ihnen umgehend per eMail ein automatisch generiertes Passwort zugesandt. Dieses Passwort können Sie nach Ihrer Registrierung am Portal unter "Profil bearbeiten" ändern.</p>
		<p>Die Registrierung ermöglicht Ihnen die Nutzung folgender Funktionen:</p>
		<ul>
			<li>Abspeichern beliebiger Kartenzusammenstellungen als WMC-Dokumente (diese können in folgenden Sitzungen immer wieder geladen werden)</li>
			<li>Abspeichern von Suchanfragen</li>
			<li>Abonnieren des Verf&uuml;gbarkeitsmonitoring von registrierten Kartendiensten (EMail Benachrichtigung bei Ausf&auml;llen einzelner Dienste)</li>
			<li>Beantragung von Dienstefreischaltungen (Nutzung von abgesicherten Kartendiensten)</li>
		</ul>
	</div>
<?php
	}
?>
</div>

<?php
	// GET BODY - END
	$BODY = ob_get_contents();
	ob_end_clean();

	$htmlstring = str_replace('[%MB_HEADER%]', $HEAD, $htmlstring);
	$htmlstring = str_replace('[%MB_CONTENT%]', $BODY, $htmlstring);
	echo $htmlstring;
?>
