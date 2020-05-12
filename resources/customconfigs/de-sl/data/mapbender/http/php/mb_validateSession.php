<?php
# $Id: mb_validateSession.php 8563 2013-02-18 19:33:04Z armin11 $
# http://www.mapbender.org/index.php/mb_validateSession.php
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

require_once(dirname(__FILE__)."/../../core/globalSettings.php");

$e = new mb_notice("mb_validateSession.php: checking file " . $_SERVER["SCRIPT_NAME"]);

// if cookies are off
if ($_REQUEST["sessionName"] && $_REQUEST["sessionId"]) { //TODO: the request parameter won't be sessionName but maybe PHPSESSID - name of cookie! See line 101 usage of SID
	session_name($_REQUEST["sessionName"]);
	session_id($_REQUEST["sessionId"]);
}
//
// check if user data is valid; if not, return to login screen
//
if (!Mapbender::session()->get("mb_user_id") || 
	!Mapbender::session()->get("mb_user_ip") || 
	Mapbender::session()->get('mb_user_ip') != $_SERVER['REMOTE_ADDR']) {
    if(isset($INDEX_WITHOUTPASS) && $INDEX_WITHOUTPASS && defined("PUBLIC_USER")) {
            Mapbender::session()->set("mb_user_id", PUBLIC_USER);
            require_once(dirname(__FILE__)."/../classes/class_user.php");
            $user = new User();

            if (intval($user->id) == intval(PUBLIC_USER)) {
                Mapbender::session()->set("mb_user_password", $user->name);
                Mapbender::session()->set("mb_user_id", $user->id);
                Mapbender::session()->set("mb_user_name", $user->name);
                Mapbender::session()->set("mb_user_ip", $_SERVER['REMOTE_ADDR']);
                Mapbender::session()->set("HTTP_HOST", $_SERVER["HTTP_HOST"]);

                require_once(dirname(__FILE__)."/mb_getGUIs.php");
                $arrayGUIs = mb_getGUIs($user->id);
                Mapbender::session()->set("mb_user_guis", $arrayGUIs);
            } else {
                $e = new mb_exception("mb_validateSession.php: Invalid user: " . Mapbender::session()->get("mb_user_id"));
                session_write_close();
                header("Location: " . LOGIN);
                die();
            }
        } else {
                $e = new mb_exception("mb_validateSession.php: Invalid user: " . Mapbender::session()->get("mb_user_id"));
                session_write_close();
                header("Location: " . LOGIN);
                die();
        }
}

//
// set the global var gui_id
//
if (!isset($gui_id)) {
	$e = new mb_notice("gui id not set");
	if (isset($_REQUEST["guiID"])) {
		$gui_id = $_REQUEST["guiID"];
		$e = new mb_notice("gui id set to guiID: " . $gui_id);
	}
	//set this to hold the get parameters for login.php in sync
	elseif (isset($_REQUEST["mb_user_myGui"])) {
		$gui_id = $_REQUEST["mb_user_myGui"];
		$e = new mb_notice("gui id set to gui_id: " . $gui_id);
	}
	elseif (isset($_REQUEST["gui_id"])) {
		$gui_id = $_REQUEST["gui_id"];
		$e = new mb_notice("gui id set to gui_id: " . $gui_id);
	}
	elseif (Mapbender::session()->get("mb_user_gui") !== false) {
		$gui_id = Mapbender::session()->get("mb_user_gui");
		$e = new mb_notice("gui id set to gui_id: " . $gui_id);
	}
	else {
		$e = new mb_notice("mb_validateSession.php: gui_id not set in script: " . $_SERVER["SCRIPT_NAME"]);
	}
}
//
//use lang parameter to set the session var mb_lang
if (isset($_REQUEST["lang"]) & $_REQUEST["lang"] != "") {
	//validate to de, en, fr, ... give a whitelist
	$testMatch = $_REQUEST["lang"];	
 	if (!($testMatch == 'de' or $testMatch == 'en' or $testMatch == 'fr')){ 
		//echo 'lang: <b>'.$testMatch.'</b> is not valid.<br/>'; 
		echo 'Parameter <b>lang</b> is not valid (de,fr,en).<br/>'; 
		die(); 		
 	}
	$lang = $testMatch;
	//set the 
	Mapbender::session()->set("mb_lang",$lang);
	$e = new mb_notice("mb_validateSession.php: lang was set by GET to: " .$lang);
	$testMatch = NULL;
}
//
// set the global var e_id
//
if (!isset($e_id)) {
	if (isset($_REQUEST["elementID"])) {
		$e_id = $_REQUEST["elementID"];
	}
	elseif (isset($_REQUEST["e_id"])) {
		$e_id = $_REQUEST["e_id"];
	}
	else {
		$e = new mb_notice("mb_validateSession.php: e_id not set in script: " . $_SERVER["SCRIPT_NAME"]);
	}
}

//
// set variables used for form targets or links
//
$urlParameters = SID;
if (isset($gui_id)) {
	$urlParameters .= "&guiID=" . $gui_id;
}
if (isset($e_id)) {
	$urlParameters .= "&elementID=" . $e_id;
}
$self = $_SERVER["SCRIPT_NAME"] . "?" . $urlParameters;

$e = new mb_notice("mb_validateSession.php: GUI: " . $gui_id . ", checking file " . $_SERVER["SCRIPT_NAME"] . "...session valid.");
?>
