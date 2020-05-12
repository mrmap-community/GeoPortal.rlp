<?php
# $Id: mod_logout.php 6728 2010-08-10 08:31:29Z christoph $
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

require_once(dirname(__FILE__)."/../php/mb_validateSession.php");

ob_start();

require_once(dirname(__FILE__)."/../include/dyn_php.php");

ignore_user_abort();

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

session_destroy();

$dir = preg_replace("/\\\/","/", dirname($_SERVER['SCRIPT_NAME']));

if (isset($logout_location) && $logout_location != ''){
	header("Location: ".$logout_location);     
}
else {
	if (is_file(dirname($_SERVER['SCRIPT_NAME'])."/login.php")) {
		if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
			header("Location: https://".$_SERVER['HTTP_HOST'].$dir."/login.php");      
		}
		else {
			header("Location: http://".$_SERVER['HTTP_HOST'].$dir."/login.php");      
		}
	}
	else {
		if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
			header("Location: https://".$_SERVER['HTTP_HOST'].preg_replace("/\/php/","/frames",$dir)."/login.php");
		}
		else {
			header("Location: http://".$_SERVER['HTTP_HOST'].preg_replace("/\/php/","/frames",$dir)."/login.php");
		}
	}
}
?>
