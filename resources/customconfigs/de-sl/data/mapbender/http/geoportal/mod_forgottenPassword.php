<?php
# $Id: mod_forgottenPassword.php 7286 2010-12-12 10:56:32Z apour $
# http://www.mapbender.org/index.php/Administration
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

//require_once(dirname(__FILE__)."/../../core/globalSettings.php");

/*  
 * @security_patch irv done
 */ 
require_once(dirname(__FILE__)."/../../core/globalSettings.php");
//security_patch_log(__FILE__,__LINE__);
//import_request_variables("PG");

# Integration reCAPTCHA
require_once('../extensions/recaptcha-php-1.11/recaptchalib.php');
$publickey = "6LeP0-gSAAAAAHgNp130RISMpg5FjnPejC8JbfrL";
$privatekey = "6LeP0-gSAAAAALxmaieAtwU5penJ5sAkb35i9JcR";

$postvars = explode(",", "username,email,upd,sendnew");
foreach ($postvars as $value) {
   $$value = $_POST[$value];
}

require_once(dirname(__FILE__)."/../classes/class_administration.php");
require_once dirname(__FILE__) . "/../classes/class_connector.php";

if(isset($_REQUEST['lang'])) $lang = $_REQUEST['lang']; // Sprachunterscheidung
else $lang = 'de_DE';

$pageULR = 'http://'.$_SERVER["HTTP_HOST"].'/'; // URL der Seite

if(strstr($lang,'en')){
	$x = new connector($pageULR.'index.php/en/cardviewer/kartenvieweren?sid='.session_id());
	$htmlstring = $x->file;
	//$htmlstring = file_get_contents($pageULR.'index.php/en/cardviewer/kartenvieweren?sid='.session_id());
}
else if(strstr($lang,'fr')){
	$x = new connector($pageULR.'index.php/fr/cardvue/kartenviewerfr?sid='.session_id());
	$htmlstring = $x->file;
	//$htmlstring = file_get_contents($pageULR.'index.php/fr/cardvue/kartenviewerfr?sid='.session_id());
}
else{
	$x = new connector($pageULR.'index.php/de/kartenviewer/kartenviewerde?sid='.session_id());
	$htmlstring = $x->file;
	//$htmlstring = file_get_contents($pageULR.'index.php/de/kartenviewer/kartenviewerde?sid='.session_id());
}
// GET HEADER - START
ob_start();

echo '<meta http-equiv="Content-Type" content="text/html; charset='.CHARSET.'">';	
?>
<title>Generate New Password</title>

<!-- weitere Optionen gesetzt für reCAPTCHA -->
 <script type="text/javascript">
 var RecaptchaOptions = {
    theme : 'clean',
    lang : 'de'
 };
 </script>
 
<style type="text/css">
<!--

body{
	font-family: Arial, Helvetica, sans-serif;
	font-size: 10px;
}
.desc{
	font-family: Arial, Helvetica, sans-serif;
	font-size: 9px;
}
.myButton{
   font-family: Arial, Helvetica, sans-serif;
   width : 150px;
}
#form1 {
    background: none repeat scroll 0 0 #F0F0F0;
    border-bottom: 1px solid #858585;
    border-top: 1px solid #858585;
    margin: 2em auto;
    padding: 1em 0.5em 0.5em;
    width: 42em;
    }
#fields label {
    display: block;
    float: left;
    font-size: 115%;
    font-weight: bold;
    margin: 0 0 0.6em;
    width: 14em;
}
#control_code label {
    display: block;
    float: none;
    font-size: 115%;
    font-weight: bold;
    margin: 0 0 0.6em;
    width: 14em;
}
#fields input {
    display: block;
    float: left;
    font-size: 12px;
    margin: 0 0 0.6em;
    width: 40em;
}

fieldset br.clr {
    clear: both;
    font-size: 0;
    height: 0;
    line-height: 0;
}

#_button input {
    background: none repeat scroll 0 0 #FFFFFF;
    margin: 1em 0 0 16em;
    width: 16em;
}
-->
</style>

<?php

	// GET HEADER - END
	$HEAD = ob_get_contents();
	ob_end_clean();

	// GET BODY - START
	ob_start();


if (!USE_PHP_MAILING) {
	echo "<script language='javascript'>";
	echo "alert('PHP mailing is currently disabled. Please adjust the settings in mapbender.conf.');";
	echo "window.close();";
	echo "</script>";
}
else {
	$logged_user_name = Mapbender::session()->get("mb_user_name");
	$logged_user_id = Mapbender::session()->get("mb_user_id");
	
	$admin = new administration();
	$upd = false;
	
	if (htmlspecialchars($_POST["sendnew"])) {
		if ($_POST["username"] && $_POST["email"]) {
			$id = $admin->getUserIdByUserName($_POST["username"]);
			$mailAddressMatch = ($admin->getEmailByUserId($id) == $_POST["email"]) && ($_POST["email"] != '');
			$user_id = $id;
	
			if ($user_id && $mailAddressMatch) {
				$upd=true;
			}
			else {
				echo "Either your username could not be found or you have registered another or no mail address.<br><br>";
			}
		}
		else {
			echo "Please fill in your username and mail address.<br><br>";
		}
	}
	
	
	/*handle INSERT and DELETE************************************************************************************/
	if($upd){
	 
	    $sql_password = $admin->getRandomPassword();
		$mailToAddr = $admin->getEmailByUserId($user_id);
		$mailToName = $admin->getUsernameByUserId($user_id);
		
		if (!$mailToAddr) {
		      echo "<script language='javascript'>";
		      echo "alert('You didn\'t enter an email address when registering with Mapbender. Unfortunately there is no way to send you a new password.');";
		      echo "window.back();";
		      echo "</script>";
		}
		elseif ($user_id) {
		    $resp = recaptcha_check_answer ($privatekey,
					$_SERVER["REMOTE_ADDR"],
					$_POST["recaptcha_challenge_field"],
					$_POST["recaptcha_response_field"]);

			if (!$resp->is_valid) {
				echo "<script language='javascript'>";
				echo "alert('Sicherheitscode nicht korrekt eingegeben.');";
				echo "window.back();";
				echo "</script>";
			}
	
		   if ($admin->sendEmail("", "", $mailToAddr, $mailToName, "Your new Mapbender password", "login:    " . $mailToName . "\npassword: " . $sql_password, $error_msg)) {
		      //set new password in db
		      	$sql_update = "UPDATE mb_user SET mb_user_password = $1, mb_user_new_password = 'TRUE'";
		      	$sql_update .= " WHERE mb_user_id = $2";
		      #echo $sql_update;
				$v = array(md5($sql_password),$user_id);
				$t = array('s','i');		      
		      db_prep_query($sql_update,$v,$t);
              require_once(dirname(__FILE__)."/updateUserDigest.php");
              updateUserDigest($user_id, $sql_password);
		      
		      //reset login count
		      $admin->resetLoginCount($user_id);
		      
		      echo "<script language='javascript'>";
		      echo "alert('A new password will be sent to your e-mail-address!');";
		      echo "window.close();";
		      echo "</script>";
		   }
		   else {
		      echo "<script language='javascript'>";
		      echo "alert('An error occured while sending the new password to your e-mail-address! " . $error_msg . " Please try again later.');";
		      echo "window.back();";
		      echo "</script>";
		   }
	   }
	   $upd = false;
	}
	else {
	
	
	/*HTML*****************************************************************************************************/
	echo '<div>';
        echo '<h3>'._mb("Passwort vergessen").' ?</h3>';
        echo '<div id="login_form">';
        echo '<form id="form1" name="form1" method="POST">';
        echo '<fieldset id="fields">';
        echo '<label for="username">'._mb("Username").':</label>';
        echo '<input id="username" class="text" name="username" type="text"  value=""/>';
        echo '<br class="clr" />';
        echo '<label for="email">'._mb("Email").':</label>';
        echo '<input id="email" class="text" name="email" type="text" />';
        echo '<br class="clr" />';
        echo '</fieldset>';
        echo '<fieldset id="control_code">';
        echo '<label>Sicherheitscode:</label>';
        echo recaptcha_get_html($publickey);
        echo '</fieldset>';
        echo '<fieldset id="_button">';
        echo '<input type="submit" id="_button input" name="sendnew" value="'._mb("Neues Passwort anfordern").'" />';
        echo '</fieldset>';
        echo '</form>';
        echo '</div>';

//	echo "<fieldset><legend>Forgot your Passwort ?</legend>";
//	#echo "<fieldset><legend>Passwort vergessen ?</legend>";
//	#echo "<form name='form1' action='" . $_SERVER["SCRIPT_NAME"] . "' method='post'>";
//	echo "<form name='form1' method='post'>";
//	echo "<table cellpadding='5' cellspacing='0' border='0'>";
//	echo "<tr><td>";
//	echo "Username:";
//	echo "</td>";
//	echo "<td>";
//	echo "<input type='text' name='username' value=''>";
//	echo "</td>";
//	echo"</tr>";
//	echo "<tr><td>";
//	echo "E-Mail:";
//	echo "</td>";
//	echo "<td>";
//	echo "<input type='text' name='email' value=''>";
//	echo "</td>";
//	echo"</tr>";
//	echo"<tr><td>";
//	echo "<input type='hidden' name='upd' value=''>";
//	echo "<center><br><input type='submit' name='sendnew' value='Order a new Password'></center>";
//	#echo "<center><br><input type='submit' name='sendnew' value='Neues Passwort anfordern'></center>";
//	echo"<td></tr></table>";
//	echo "</form>";
//	echo"</fieldset><br />";
	/*********************************************************************/
	}
}

// GET BODY - END
$BODY = ob_get_contents();
ob_end_clean();

$htmlstring = str_replace('[%MB_HEADER%]', $HEAD, $htmlstring);
$htmlstring = str_replace('[%MB_CONTENT%]', $BODY, $htmlstring);
echo $htmlstring;
?>
