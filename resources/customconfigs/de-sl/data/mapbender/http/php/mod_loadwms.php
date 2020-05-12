<?php
# $Id: mod_loadwms.php 8785 2014-02-28 11:51:21Z armin11 $
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

require_once(dirname(__FILE__) . "/mb_validatePermission.php");
require_once(dirname(__FILE__) . "/../classes/class_wms.php"); 

if(isset($_REQUEST["wms_id"]) == false) {
	echo "file: ".$_REQUEST["xml_file"];
    	$gui_id = $_REQUEST["guiList"];
    	$xml = $_REQUEST["xml_file"];
    	if ($_REQUEST["auth_type"] == 'basic' || $_REQUEST["auth_type"] == 'digest') {
		$auth = array();
    		$auth['username'] = $_REQUEST["username"];
    		$auth['password'] = $_REQUEST["password"];
    		$auth['auth_type'] = $_REQUEST["auth_type"];
    	}
    	$mywms = new wms();
    	if(empty($_POST['twitter_news'])) {
		$mywms->twitterNews = false;
	}
	if(empty($_POST['rss_news'])) {
		$mywms->setGeoRss = false;
	}
	if (isset($auth)) {
		$result = $mywms->createObjFromXML($xml, $auth);	
		if ($result['success']) {
			$mywms->writeObjInDB($gui_id, $auth);  
		} else {
			echo $result['message'];
			die();
		}
	} else {
		$result = $mywms->createObjFromXML($xml);
		if ($result['success']) {
			$mywms->writeObjInDB($gui_id);  
		} else {
			echo $result['message'];
			die();
		}
	}
   	$mywms->displayWMS();
	$wms_id = $mywms->wms_id;
	
	//onload wms: unlink the application cache file in cache directory
	require_once (dirname(__FILE__) . "/../classes/class_administration.php");
    $admin = new administration();
	#$admin->clearJsCacheFile($gui_id);
} else {
	$wms_id = $_REQUEST["wms_id"];
}
require_once(dirname(__FILE__)."/../php/mod_editWMS_Metadata.php");
editWMSByWMSID ($wms_id);
?>
