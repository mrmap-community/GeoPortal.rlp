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
	require_once dirname(__FILE__) ."/../../core/globalSettings.php";
	require_once dirname(__FILE__) . "/../classes/class_connector.php";
	
	if(empty($_SESSION["mb_user_id"])) die("Sie sind nicht angemeldet!");

	$id = $_SESSION["mb_user_id"];
	$conn = pg_connect("host=".DBSERVER." port=".PORT." dbname=".DB." user=".OWNER." password=".PW);
			
	if($conn) {
		
		// INSERT
		if(empty($_REQUEST["id"]) AND !empty($_REQUEST["name"]) AND !empty($_REQUEST["searchtext"])) {
			pg_query_params($conn, 'INSERT INTO mb_user_search ("user_id","name","searchtext") VALUES ($1,$2,$3);', array($id,$_REQUEST["name"],$_REQUEST["searchtext"]));
		// UPDATE
		} else if(!empty($_REQUEST["id"]) AND !empty($_REQUEST["name"]) AND !empty($_REQUEST["searchtext"])) {
			pg_query_params($conn, 'UPDATE mb_user_search SET name = $3, searchtext = $4 WHERE user_id = $1 AND id = $2;', array($id,$_REQUEST["id"],$_REQUEST["name"],$_REQUEST["searchtext"]));
		// REMOVE
		} else if(!empty($_REQUEST["id"]) AND empty($_REQUEST["name"]) AND empty($_REQUEST["searchtext"])) {
			pg_query_params($conn, 'DELETE FROM mb_user_search WHERE user_id = $1 AND id = $2;', array($id,$_REQUEST["id"]));
		}
		
		// SELECT
		$result = pg_query_params($conn, 'SELECT id,name,searchtext FROM mb_user_search WHERE user_id = $1', array($id));
		while($tmp = pg_fetch_assoc($result)) {
			$data[] = $tmp;
		}
	}
	
	echo json_encode($data);
	
?>