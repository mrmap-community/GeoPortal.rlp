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

require_once dirname(__FILE__) . "/../../conf/mapbender.conf";
require_once dirname(__FILE__) . "/../classes/class_connector.php";

function auth_user($name,$pw){
	$setEncPw = false;
	$sql = "SELECT * FROM mb_user WHERE mb_user_name = $1 AND mb_user_password = $2";
	$v = array($name,md5($pw));
	$t = array('s','s');
	$res = db_prep_query($sql,$v,$t);
	if($row = db_fetch_array($res)){
		return $row;
	}
	else if(SYS_DBTYPE == 'pgsql' && $setEncPw == true){
		// 	unencrypted pw in postgres without md5-support?
		$sql = "SELECT * FROM mb_user WHERE mb_user_name = $1 AND mb_user_password = $2";
		$v = array($name,$pw);
		$t = array('s','s');
		$resn = db_prep_query($sql,$v,$t);
		if($rown = db_fetch_array($resn)){
			$sqlu = "UPDATE mb_user SET mb_user_password = $1 WHERE mb_user_id = $2";
			$vu = array(md5($pw),$rown["mb_user_id"]);
			$tu = array('s','i');
			$rowu = db_prep_query($sqlu,$vu,$tu);
            require_once(dirname(__FILE__)."/updateUserDigest.php");
            updateUserDigest($rown["mb_user_id"], $pw);
			return $rown;
		}
	}
}

function redirectToLogin ($name = "") {
	if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
		header ("Location: https://".$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME'])."/mod_login.php?name=".$name);
	}
	else {
		header ("Location: http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME'])."/mod_login.php?name=".$name);
	}
	die;
}


/*****************************************************/
// Laedt die HTML Datei

if(isset($_REQUEST['lang'])) $lang = $_REQUEST['lang']; // Sprachunterscheidung
else $lang = 'de_DE';


$pageULR = 'http://'.$_SERVER["HTTP_HOST"].'/'; // URL der Seite


$helpURL = "";
$contactURL = "";
if(strstr($lang,'en')){
        $contactURL = "../../../en/kontakt.html";
        $helpURL = $pageULR."de/hilfe/benutzung-des-geogortal-saarland/anmeldung.html";
	$x = new connector($pageULR.'index.php/en/cardviewer/kartenvieweren?sid='.session_id());
	$htmlstring = $x->file;
	//$htmlstring = file_get_contents($pageULR.'index.php/en/cardviewer/kartenvieweren?sid='.session_id());
}
else if(strstr($lang,'fr')){
        $contactURL = "../../../fr/kontakt.html";
        $helpURL = $pageULR."de/hilfe/benutzung-des-geogortal-saarland/anmeldung.html";
	$x = new connector($pageULR.'index.php/fr/cardvue/kartenviewerfr?sid='.session_id());
	$htmlstring = $x->file;
	//$htmlstring = file_get_contents($pageULR.'index.php/fr/cardvue/kartenviewerfr?sid='.session_id());
}
else{
        $contactURL = "../../../de/kontakt.html";
        $helpURL = $pageULR."de/hilfe/benutzung-des-geogortal-saarland/anmeldung.html";
	$x = new connector($pageULR.'index.php/de/kartenviewer/kartenviewerde?sid='.session_id());
	$htmlstring = $x->file;
	//$htmlstring = file_get_contents($pageULR.'index.php/de/kartenviewer/kartenviewerde?sid='.session_id());
}


/*******************************************************************************
 *																	H E A D E R 
 *******************************************************************************/
ob_start();
?>
<!--
Licensing: See the GNU General Public License for more details.
http://www.gnu.org/copyleft/gpl.html
or:
mapbender/licence/
-->
<meta http-equiv="cache-control" content="no-cache">
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="expires" content="0">
<META http-equiv="Content-Style-Type" content="text/css">
<META http-equiv="Content-Script-Type" content="text/javascript">
<?php
echo '<meta http-equiv="Content-Type" content="text/html; charset='.CHARSET.'">';
?>
<title>Login</title>
<script type="text/javascript" src="../extensions/jquery-ui-1.8.1.custom/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="../extensions/jquery-ui-1.8.1.custom/js/jquery-ui-1.8.1.custom.min.js"></script>
<script type="text/javascript" src="../extensions/jquery-ui-1.8.1.custom/development-bundle/ui/jquery.ui.tabs.js"></script>
<link rel="stylesheet" type="text/css" href="../extensions/jquery-ui-1.8.1.custom/development-bundle/themes/base/jquery.ui.all.css" />
<link rel="stylesheet" type="text/css" href="../extensions/jquery-ui-1.8.1.custom/development-bundle/themes/base/jquery.ui.tabs.css" />
<?php
$css_folder = "";
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"../css/" . $css_folder . "login.css\">";
echo "<link rel=\"shortcut icon\" href=\"../img/favicon.ico\">";
$name = $name_req = $_REQUEST["name"];
$password = $password_req = $_REQUEST["password"];

if(isset($_REQUEST["mb_user_myGui"]) && $_REQUEST["mb_user_myGui"] != "") {
	Mapbender::session()->set("mb_user_gui",$_REQUEST["mb_user_myGui"]);
}

if(!isset($name) || $name == ''){
  echo "<script type='text/javascript'>";
  echo "<!--". chr(13).chr(10);
  echo "function setFocus(){";
     echo "if(document.loginForm){";
        echo "document.loginForm.name.focus();";
     echo "}";
  echo "}";
  echo "// -->". chr(13).chr(10);
  echo "</script>";
}
else{
  echo "<script type='text/javascript'>";
  echo "<!--". chr(13).chr(10);
  echo "function setFocus(){";
     echo "if(document.loginForm){";
        echo "document.loginForm.password.focus();";
     echo "}";
  echo "}";
  echo "// -->". chr(13).chr(10);
  echo "</script>";
}

?>

<script type='text/javascript'>
$(document).ready(function () {
	$(function() {
		$("#guiListTabs").tabs({
			event: 'mouseover'
		});
		//$("a", ".gui_list").button();
		//$("a", ".gui_list").click(function() { return false; });

	});
});
</script>

<?php

	// GET HEADER - END
	$HEAD = ob_get_contents();
	ob_end_clean();

/*******************************************************************************
 *																	     B O D Y 
 *******************************************************************************/
	ob_start();

if(!Mapbender::session()->get("mb_user_id") OR Mapbender::session()->get("mb_user_id") == 2) {
	if(empty($name) OR empty($password)) {
	    echo '<div>';
	    echo '<div>';
	    echo '<h3>Anmeldung im GeoPortal Saarland</h3>';
	    echo '<p>Durch das Anmelden stehen Ihnen diverse Zusatzfunktionen zur Verfügung, andere Anwendungen können Sie erst nach Freigabe durch die jeweiligen Diensteanbieter nutzen.</p>';
	    echo '<p>Falls Sie sich noch nicht registriert haben, füllen Sie unter <a href="../geoportal/mod_registration.php" >"Registrieren"</a> das Formular aus. Danach erhalten Sie eine E-Mail mit einem automatisch generierten Passwort, welches Sie nach Ihrer erstmaligen Anmeldung unter "Profil bearbeiten" ändern können.</p>';
	    echo '<p>Falls Sie Ihr Passwort vergessen haben, können Sie unter <a href="../geoportal/mod_forgottenPassword.php" style="font-size:120%;color:red;">"Passwort vergessen"</a> ein neues Passwort beantragen.</p>';
	    echo '</div>';
	    echo '<div id="login_form">';
	    //echo '<form id="loginForm" name="loginForm" action ="' . 'https://' . $_SERVER["SCRIPT_NAME"] . '" method="POST">';
        echo '<form id="loginForm" name="loginForm" action ="' . 'https://' . $_SERVER['HTTP_HOST'] .'/mapbender/geoportal/authentication.php" method="POST">';
	    echo '<fieldset id="fields">';
	    echo '<label for="user">Benutzername:</label>';
	//    echo '<input id="user" class="text" name="name" type="text"  value="' . htmlentities($name, ENT_QUOTES, "UTF-8") . '"/>';
		echo '<input id="user" class="text" name="name" type="text"  value=""/>';
	    echo '<br class="clr" />';
	    echo '<label for="password">Passwort:</label>';
	    echo '<input id="password" class="text" name="password" type="password" />';
	    echo '<br class="clr" />';
	    echo '</fieldset>';
	    
	    echo '<fieldset id="_button">';
	    echo '<input type="submit" value="Anmelden" />';
	    echo '</fieldset>';
	    echo '</form>';
	    echo '</div>';
	    echo '<div>';
	    echo '<p>Bei Fragen oder Problemen lesen Sie bitte <a href="'.$helpURL.'" style="font-size:120%;" >hier</a> oder wenden sich direkt an <a href="'.$contactURL.'" style="font-size:120%;">uns</a>.</p>';
	    echo '</div>';
	    echo '</div>';
	}	
}
	


if(Mapbender::session()->get("mb_user_id")) {
	$name = Mapbender::session()->get("mb_user_name");
	$password = Mapbender::session()->get("mb_user_password");
}


if((!empty($name) AND !empty($password))) {

	require_once dirname(__FILE__)."/../../core/system.php";

	if(!Mapbender::session()->get("mb_user_id")) {
		$sql_count = "SELECT mb_user_login_count, mb_user_new_password FROM mb_user WHERE mb_user_name = $1";
		$params = array($name);
		$types = array('s');
		$res_count = db_prep_query($sql_count,$params,$types);

		if($row = db_fetch_array($res_count)){
			if($row["mb_user_login_count"] > MAXLOGIN){
				echo "Permission denied. Login failed ".MAXLOGIN." times. Your account has been deactivated. Please contact your administrator!";
				die;
			}
		}

		require_once dirname(__FILE__)."/../../lib/class_Mapbender.php";
		require_once dirname(__FILE__)."/../../lib/class_Mapbender_session.php";
		$row = auth_user($name, $password);

		// if given user data is found in database, set session data (db_fetch_array returns false if no row is found)
		if($row) {
			require_once dirname(__FILE__) . "/../../core/globalSettings.php";
			include(dirname(__FILE__) . "/../../conf/session.conf");

			if($row["mb_user_login_count"] <= MAXLOGIN) {
				$sql_del_cnt =  "UPDATE mb_user SET mb_user_login_count = 0 WHERE mb_user_id = $1";
				$v = array(Mapbender::session()->get('mb_user_id'));
				$t = array("i");
				
				db_prep_query($sql_del_cnt, $v, $t);
				require_once(dirname(__FILE__)."/../php/mb_getGUIs.php");
				$arrayGUIs = mb_getGUIs($row["mb_user_id"]);
				new mb_notice("login.setSession.mb_user_guis: ".serialize($arrayGUIs)." in session: " .session_id());
				Mapbender::session()->set("mb_user_guis",$arrayGUIs);
				Mapbender::session()->set("mb_login",$login);
				Mapbender::session()->set("mb_user_email", $row["mb_user_email"]);
				Mapbender::session()->set("mb_user_department",$row["mb_user_department"]);
				Mapbender::session()->set("mb_user_organisation_name",$row["mb_user_organisation_name"]);
				Mapbender::session()->set("mb_user_position_name",$row["mb_user_position_name"]);
				Mapbender::session()->set("mb_user_phone",$row["mb_user_phone"]);
				Mapbender::session()->set("mb_user_description",$row["mb_user_description"]);
				Mapbender::session()->set("mb_user_city",$row["mb_user_city"]);
				Mapbender::session()->set("mb_user_postal_code",$row["mb_user_postal_code"]);
				//new mb_notice("=========== ".($arrayGUIs)." in session: " .session_id());
				

				# a gui is explicitly ordered
				if((isset($_REQUEST["mb_user_myGui"]) || Mapbender::session()->get("mb_user_myGui")) && in_array($_REQUEST["mb_user_myGui"], $arrayGUIs)){
					unset($arrayGUIs);
					if(isset($_REQUEST["mb_user_myGui"])){ $arrayGUIs[0] = $_REQUEST["mb_user_myGui"];}
					else{ $arrayGUIs[0] = Mapbender::session()->set("mb_user_myGui");}
				}
                require_once(dirname(__FILE__)."/updateUserDigest.php");
                updateUserDigest($row["mb_user_id"], $password);
			}
			
			if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
				if($row['mb_user_new_password'] == "t") {
					$myURL = "Location: https://".$_SERVER['HTTP_HOST']."mapbender/geoportal/mod_profil.php";
				}
				else {
					$myURL = "Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
				}
			} else {
				if($row['mb_user_new_password'] == "t") {
					$myURL = "Location: http://".$_SERVER['HTTP_HOST']."mapbender/geoportal/mod_profil.php";
				}
				else {
					$myURL = "Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
				}
			}

			header ($myURL);
			die();
		} else {
			redirectToLogin($name);
		}
	} else if(Mapbender::session()->get("mb_user_id") && Mapbender::session()->get("mb_user_id") != 2){
		$arrayGUIs = Mapbender::session()->get("mb_user_guis");
		require_once(dirname(__FILE__)."/../geoportal/mb_listGUIs.php");
		mb_listGUIs($arrayGUIs);
        require_once(dirname(__FILE__)."/updateUserDigest.php");
        updateUserDigest(Mapbender::session()->get("mb_user_id"), $password);

        $sql = "UPDATE mb_user SET";
        $sql .= " mb_user_last_login_date = now()";
        $V[0] = Mapbender::session()->get('mb_user_id');
        $T[0] = 'i';
        $sql .= 'WHERE mb_user_id = $1';
        $res = db_prep_query($sql, $V, $T);
        
        if($row['mb_user_new_password'] == "t") {
        	if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
        		$myURL = "Location: https://".$_SERVER['HTTP_HOST']."mapbender/geoportal/mod_profil.php";
        	}
        	else {
        		$myURL = "Location: http://".$_SERVER['HTTP_HOST']."mapbender/geoportal/mod_profil.php";
        	}
        	header ($myURL);
        }
    }
	else{
		Mapbender::session()->kill();
		$sql_set_cnt = "UPDATE mb_user SET mb_user_login_count = (mb_user_login_count + 1) WHERE mb_user_name = $1";
		$v = array($name);
		$t = array('s');
		db_prep_query($sql_set_cnt,$v,$t);
		redirectToLogin($name);
	}
}

// GET BODY - END
$BODY = ob_get_contents();
ob_end_clean();
/*******************************************************************************
 *														         B O D Y  - ende
 *******************************************************************************/

$htmlstring = str_replace('[%MB_HEADER%]', $HEAD.$redirect, $htmlstring);
$htmlstring = str_replace('[%MB_CONTENT%]', $BODY, $htmlstring);
echo $htmlstring;

?>
