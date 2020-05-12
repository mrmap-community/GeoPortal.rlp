<?php
# $Id: mod_editFilteredUser.php 7217 2010-12-11 15:06:07Z apour $
# http://www.mapbender.org/index.php/Administration
#
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

$e_id="editFilteredUser";

require_once(dirname(__FILE__)."/../php/mb_validatePermission.php");

/*  
 * @security_patch irv done
 */ 
//security_patch_log(__FILE__,__LINE__);
//import_request_variables("PG");
$selected_user = $_POST["selected_user"];
$name = $_POST["name"];
$firstname = $_POST["firstname"];
$lastname = $_POST["lastname"];
$academic_title = $_POST["academic_title"];
$password = $_POST["password"];
$password_plain = $_POST["password_plain"];
$v_password = $_POST["v_password"];
$description = $_POST["description"];
$email = $_POST["email"];
$phone = $_POST["phone"];
$facsimile = $_POST["facsimile"];
$street = $_POST["street"];
$housenumber = $_POST["housenumber"];
$delivery_point = $_POST["delivery_point"];
$postal_code = $_POST["postal_code"];
$city = $_POST["city"];
$organization = $_POST["organization"];
$department = $_POST["department"];
$position = $_POST["position"];
$country = $_POST["country"];
$owner_name = $_POST["owner_name"];
$owner_id = $_POST["owner_id"];
$login_count = $_POST["login_count"];
$resolution = $_POST["resolution"];
$action = $_POST["action"];
$spatial = implode(",",$_POST["spatial"]);

require_once(dirname(__FILE__)."/../classes/class_user.php");
$myUser = true;

include "../../lib/editUser.php";
?>
