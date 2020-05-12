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

function updateUserDigest($userId, $userPassword){
    if($userId && $userPassword) {
        $admin = new administration();
        $userName = $admin->getUsernameByUserId($userId);
        $userEmail = $admin->getEmailByUserId($userId);
        if($userName && $userEmail){
            $con = db_connect(DBSERVER,OWNER,PW);
            db_select_db(DB,$con);
            // start sql statement
            $sql = "UPDATE mb_user SET mb_user_digest=$1 WHERE mb_user_id=$2";
            $digest = $userId != 2 ? md5($userName.";".$userEmail.":".REALM.":".$userPassword) : "";
            $e = new mb_exception("###### geoportal/updateUserDigest.php: digest new: ".$digest);
            $v = array($digest, $userId);
            $t = array("s", "i");
            $res = db_prep_query($sql, $v, $t);

            if(!$res){
                $e = new mb_exception("db_query($qstring)=$ret db_error=".db_error());	
            }
			$con = db_connect(DBSERVER,OWNER,PW);
            db_select_db(DB,$con);
            // start sql statement
            $sql = "UPDATE mb_user SET mb_user_aldigest=$1 WHERE mb_user_id=$2";
            $digest = $userId != 2 ? md5($userName.":".REALM.":".$userPassword) : "";
            $e = new mb_exception("###### geoportal/updateUseralternateDigest.php: digest new: ".$digest);
            $v = array($digest, $userId);
            $t = array("s", "i");
            $res = db_prep_query($sql, $v, $t);
            if(!$res){
                $e = new mb_exception("db_query($qstring)=$ret db_error=".db_error());
            }

        }
    }
}
?>
