<?php
# $Id: 
# http://www.mapbender.org/index.php/Monitor_Capabilities
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

#	require_once(dirname(__FILE__)."/../../conf/mapbender.conf");
	require_once(dirname(__FILE__)."/../../core/globalSettings.php");
	require_once(dirname(__FILE__)."/../classes/class_administration.php");
	#echo "test<br>";
	$mb_user_password = '';
	$adm = new administration();
	$con = db_connect(DBSERVER,OWNER,PW);
	db_select_db(DB,$con);
	///Passort generieren
	$pool = "qwertzupasdfghkyxcvbnm";
	$pool .= "23456789";
	$pool .= "WERTZUPLKJHGFDSAYXCVBNM";
	srand ((double)microtime()*1000000);
	for($index = 0; $index < 7; $index++)	
	{
	    $mb_user_password .= substr($pool,(rand()%(strlen ($pool))), 1);
	}
	//Check if user will register as 'guest'
	$registerAsGuest = false;	
	if ($mb_user_name == 'guest') {
		$registerAsGuest = true;
	}
	//// überprüfen ob User und oder Mail bereits existieren
		$sql = "SELECT * FROM mb_user WHERE mb_user_name = $1 AND mb_user_email = $2";
 		$v = array($mb_user_name,$mb_user_email); // wird in unserer Lösung immer md5 genutzt?
		 $t = array('s','s');
 		$res = db_prep_query($sql,$v,$t);
 		//$row = db_fetch_array($res);
 	$emailValid = 1;
	if(db_numrows($res) == 0 and !($registerAsGuest)){
	
		$userAlreadyExists = 0;
		/// pr�fen auf email und richtigkeit
		if($adm->isValidEmail($mb_user_email))
		{
			$emailValid = 1;	
			/////INSERT
			if(!isset($mb_user_postal_code) || $mb_user_postal_code == ''){ $mb_user_postal_code = 0;}
			$sql = "INSERT INTO mb_user (mb_user_name,mb_user_password,mb_user_digest,mb_user_owner,mb_user_description,mb_user_email,mb_user_phone,mb_user_department,mb_user_organisation_name,mb_user_position_name,mb_user_city,mb_user_postal_code, mb_user_textsize, mb_user_glossar) VALUES ($1, $2,$13, '1', $3, $4, $5, $6, $7, $8, $9, $10,$11,$12)";
			$v = array($mb_user_name, md5($mb_user_password), $mb_user_description, $mb_user_email, $mb_user_phone, $mb_user_department, $mb_user_organisation_name, $mb_user_position_name, $mb_user_city, $mb_user_postal_code, 'textsize1' ,'ja',md5($mb_user_name.";".$mb_user_email.":".REALM.":".$mb_user_password));
			$t = array('s', 's', 's', 's', 's', 's', 's', 's', 's', 'i', 's', 's', 's');
			$res = db_prep_query($sql, $v, $t);
	
			//$sql = "SELECT mb_group_id FROM mb_group WHERE mb_group_name = 'guest' LIMIT 1";
			//$res = db_prep_query($sql, array(), array());
			//$row = db_fetch_array($res);
			$group_id = 37;
			$sql = "INSERT INTO mb_user_mb_group (fkey_mb_user_id, fkey_mb_group_id) VALUES ($1, $2)";
			$v = array($adm->getUserIdByUserName($mb_user_name), $group_id);
			$t = array('i', 'i');
			$res = db_prep_query($sql, $v, $t);
	
			//Mailversand
			$mailBody = $mailBody1.$mb_user_name.$mailBody2.$mb_user_password.$mailBody3;

			$mailBody = iconv("UTF-8", "ISO-8859-1", $mailBody);
			#echo $mailBody."<br>";
			$success = $adm->sendEmail(MAILADMIN, MAILADMINNAME, $mb_user_email, $mb_user_name, 'Ihre Registrierung', $mailBody, $error_msg );
		}
		else
		{
			$emailValid = 0;	
		}

	}
	else
	{
		$userAlreadyExists = 1;	
	}
?>
