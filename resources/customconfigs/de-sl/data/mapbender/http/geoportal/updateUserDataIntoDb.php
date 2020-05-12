<?php
#  
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

#Script which is included by a typo3 script to register the users

	require_once(dirname(__FILE__)."/../../core/globalSettings.php");
	require_once(dirname(__FILE__)."/../classes/class_administration.php");
	$adm = new administration();
	$con = db_connect(DBSERVER,OWNER,PW);
	db_select_db(DB,$con);
	// fields which should be updated
	$fields = array();
	// entries
    $v = array();
    // types
    $t = array();
	// start sql statement
	$sql = "UPDATE mb_user SET";
    $sql .= " mb_user_owner = 1"; #all users belong to the central administrator
     //change username if field is not empty - the variables are from the calling script
    if ($mb_user_name != '') {
        $fields[] = array('mb_user_name', $mb_user_name, 's');
    } else {
		$mb_user_name = $_SESSION['mb_user_name'];
	}
    // change password if it was set
    if ($mb_user_password != '') {
        $fields[] = array('mb_user_password', md5($mb_user_password), 's');
    #$fields[] = array('mb_user_digest', md5($mb_user_name.";".$mb_user_email.":".REALM.":".$mb_user_password), 's');
    }
	#else
	#{
	#	$mb_user_password = $_SESSION['mb_user_password']; # Don't update the password for a guest user - cause no one can get a anonymous user any more - this is done with password guest!
	#}
  
    //update other fields
    $fields[] = array('mb_user_description', $mb_user_description, 's');
    $fields[] = array('mb_user_email', $mb_user_email, 's');
    $fields[] = array('mb_user_phone', $mb_user_phone, 's');
    #$fields[] = array('mb_user_department', $mb_user_department, 's'); # don't update department cause it is used for some relation - TODO uncomment it when relation is obsolet
    $fields[] = array('mb_user_organisation_name', $mb_user_organisation_name, 's');
    $fields[] = array('mb_user_position_name', $mb_user_position_name, 's');
    $fields[] = array('mb_user_city', $mb_user_city, 's');
	$e = new mb_exception("###### geoportal/updateUserIntoDb.php: digest new: ".md5($mb_user_name.";".$mb_user_email.":".REALM.":".$mb_user_password));
	$fields[] = array('mb_user_digest', md5($mb_user_name.";".$mb_user_email.":".REALM.":".$mb_user_password), 's');
    if(is_int($mb_user_postal_code) && $mb_user_postal_code >= 0){
        $fields[] = array('mb_user_postal_code', $mb_user_postal_code, 'i'); //postal_code als integer?
	}
    $fields[] = array('mb_user_textsize', $Textsize, 's');
    $fields[] = array('mb_user_glossar', $Glossar, 's');
    $fields[] = array('mb_user_spatial_suggest', $mb_user_spatial_suggest, 's');
    // build sql statement
    foreach ($fields as $idx => $field) {
        $sql .= ', '.$field[0].' = $'.($idx + 1);
        $v[] = $field[1];
        $t[] = $field[2];
    }
    // + where condition

    $v[] = $_SESSION["mb_user_id"];
    $t[] = 'i';
    $sql .= 'WHERE mb_user_id = $'.count($v);//.' AND mb_user_name != \'guest\'';
	if  ($_SESSION["mb_user_id"] != ANONYMOUS_USER) {   	
		$res = db_prep_query($sql, $v, $t);
	}
	if(!$res){
		$e = new mb_exception("db_query($qstring)=$ret db_error=".db_error());	
	}
	//UPDATE of the SESSION VARS
	$_SESSION["mb_user_email"] = $mb_user_email; 
	$_SESSION["mb_user_department"] = $mb_user_department; 
	$_SESSION["mb_user_organisation_name"] = $mb_user_organisation_name; 
	$_SESSION["mb_user_position_name"] = $mb_user_position_name; 
	$_SESSION["mb_user_phone"] = $mb_user_phone; 
	$_SESSION["Textsize"] = $Textsize;
	$_SESSION["Glossar"] = $Glossar;	
	$_SESSION["mb_user_spatial_suggest"] = $mb_user_spatial_suggest;	
	$_SESSION["mb_user_description"]= $mb_user_description;
	$_SESSION["mb_user_city"]= $mb_user_city;
	$_SESSION["mb_user_postal_code"]= $mb_user_postal_code;
	
	
	
	
/*	//Push registrated user into ANONYMOUS_GROUP guest????
	$sql = "SELECT mb_group_id FROM mb_group WHERE mb_group_name = 'guest' LIMIT 1";
	$res = db_prep_query($sql, array(), array());
	$row = db_fetch_array($res);
	$group_id = $row['mb_group_id'];
	$sql = "INSERT INTO mb_user_mb_group (fkey_mb_user_id, fkey_mb_group_id) VALUES ($1, $2)";
	$v = array($adm->getUserIdByUserName($mb_user_name), $group_id);
	$t = array('i', 'i');
	$res = db_prep_query($sql, $v, $t);
*/
?>
