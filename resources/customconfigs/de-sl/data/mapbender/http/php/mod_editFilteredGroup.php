<?php
# $Id: mod_editFilteredGroup.php 7223 2010-12-11 15:19:46Z apour $
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

$e_id="editFilteredGroup";
require_once(dirname(__FILE__)."/../php/mb_validatePermission.php");

/*  
 * @security_patch irv done
 */ 
$selected_group = $_POST["selected_group"];
$name = $_POST["name"];
$title = $_POST["title"];
$owner_name = $_POST["owner_name"];
$owner_id = $_POST["owner_id"];
$description = $_POST["description"];
$address = $_POST["address"];
$postcode = $_POST["postcode"];
$city = $_POST["city"];
$stateorprovince = $_POST["stateorprovince"];
$country = $_POST["country"];
$voicetelephone = $_POST["voicetelephone"];
$facsimiletelephone = $_POST["facsimiletelephone"];
$email = $_POST["email"];
$logo_path = $_POST["logo_path"];
$action = $_POST["action"];
$spatial = implode(",",$_POST["spatial"]);

require_once(dirname(__FILE__)."/../classes/class_user.php");
require_once(dirname(__FILE__)."/../classes/class_group.php");
$myGroup = true;


include "../../lib/editGroup.php";
?>

