<?php
# $Id: class_kml_geometry.php 1966 2008-01-15 08:25:15Z christoph $
# http://www.mapbender.org/index.php/class_wmc.php
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
require_once(dirname(__FILE__)."/../classes/class_RPCEndpoint.php");
require_once(dirname(__FILE__)."/../classes/class_user.php");

/**
 * A Mapbender user as described in the table mb_group.
 */
class Group implements RPCObject {
	/**
	 * @var Integer The Group ID
	 */
	protected $id;
	var $name;
	var $owner = 0;
	var $description ="";
	var $title;
	var $address;
	var $postcode;
	var $city;
	var $stateorprovince;
	var $country;
	var $voicetelephone;
	var $facsimiletelephone;
	var $email;
	var $logo_path;
    var $spatial;

    static $displayName = "Group";
    static $internalName = "group";

	/**
	 * Constructor
	 * @param groupId Integer 	the ID of the group that is represented by
	 * 							this object. If null, create an empty object
	 */
	public function __construct ($groupId) {
		if (!is_numeric($groupId)) {
			return;
		}
		$this->id = $groupId;
		try{
			$this->load();
		}
		catch(Exception $e) {
			new mb_exception($e->getMessage());
			return;
		}
	}


	/**
	 * @return String the ID of this group
	 */
	public function __toString () {
		return (string) $this->id;
	}

	public function getId () {
		return $this->id;
	}

    /**
     * @return Assoc Array containing the fields to send to the user
     */
	public function getFields () {
		return array(
			"name" => $this->name,
			"owner" => $this->owner,
			"description" => $this->description,
			"title" => $this->title,
	        "address" => $this->address,
	        "postcode" => $this->postcode,
	        "city" => $this->city,
	        "stateorprovince" => $this->stateorprovince,
	        "country" => $this->country,
	        "voicetelephone" => $this->voicetelephone,
	        "facsimiletelephone" => $this->facsimiletelephone,
	        "email" => $this->email,
	        "logo_path" => $this->logo_path,
            "spatial" => $this->spatial
		);
	}
	
	public function create() {
		if (is_null($this->name) || $this->name == "") {
			$e = new Exception("Can't create group without name");
		}
		
		db_begin();

		$sql_group_create = "INSERT INTO mb_group (mb_group_name) VALUES ($1)";
		$v = array($this->name);
		$t = array("s");
		$insert_result = db_prep_query($sql_group_create, $v, $t);

		if (!$insert_result) {
			db_rollback();
			$e = new Exception("Could not insert new group");
			return false;
		}
		
		$id = db_insertid($insert_result,'mb_group','mb_group_id');
		if ($id != 0) {
			$this->id = $id;
		}
		
		$commit_result = $this->commit();
		if ($commit_result == false) {
			try {
				db_rollback();
			}
			catch (Exception $E)	{
				$newE = new Exception("Could not set inital values of new group");
				throw $newE;
				return false;
			}
			return false;
		}
		
		db_commit();
		return true;
	}


	/**
	 *	@param	$changes JSON  keys and their values of what to change in the object
	 */
	public function change($changes) {
        //FIXME: validate input

		$this->name = isset($changes->name) ? $changes->name : $this->name;
		$this->owner = isset($changes->owner) ? $changes->owner : $this->owner;
		$this->description = isset($changes->description) ? $changes->description : $this->description;
		$this->id = isset($changes->id) ? $changes->id : $this->id;
     	$this->title = isset($changes->title) ? $changes->title : $this->title;
		$this->address = isset($changes->address) ? $changes->address : $this->address;
		$this->postcode = isset($changes->postcode) ? $changes->postcode : $this->postcode;
		$this->city = isset($changes->city) ? $changes->city : $this->city;
		$this->stateorprovince = isset($changes->stateorprovince) ? $changes->stateorprovince : $this->stateorprovince;
		$this->country = isset($changes->country) ? $changes->country : $this->country;
		$this->voicetelephone = isset($changes->voicetelephone) ? $changes->voicetelephone : $this->voicetelephone;
		$this->facsimiletelephone = isset($changes->facsimiletelephone) ? $changes->facsimiletelephone : $this->facsimiletelephone;
		$this->email = isset($changes->email) ? $changes->email : $this->email;
		$this->logo_path = isset($changes->logo_path) ? $changes->logo_path : $this->logo_path;
		$this->spatial = isset($changes->spatial) ? $changes->spatial : $this->spatial;
        
		return true;
	}

	public function commit() {

		$sql_update = "UPDATE mb_group SET ".
			"mb_group_name = $1, ".
			"mb_group_owner = $2, ".
			"mb_group_description = $3, ".
			"mb_group_title = $4, ".
			"mb_group_address = $5, ".
			"mb_group_postcode = $6, ".
			"mb_group_city = $7, ".
			"mb_group_stateorprovince = $8, ".
			"mb_group_country = $9, ".
			"mb_group_voicetelephone = $10, ".
			"mb_group_facsimiletelephone = $11, ".
			"mb_group_email = $12, ".
			"mb_group_logo_path = $13, ".
			"mb_group_spatial = $14 ".
			"WHERE mb_group_id = $15";


			$v = array(
				$this->name,
				$this->owner,
				$this->description,
				$this->title,
				$this->address,
				$this->postcode,
				$this->city,
				$this->stateorprovince,
				$this->country,
				$this->voicetelephone,
				$this->facsimiletelephone,
				$this->email,
				$this->logo_path,
                $this->spatial,
				$this->id
			);

			$t = array(
				"s", "i", "s", "s", "s",
				"i", "s", "s", "s", "s", 
				"s", "s", "s", "s", "i"
			);

			$update_result = db_prep_query($sql_update,$v,$t);
			if(!$update_result)	{
				throw new Exception("Database error updating Group");
				return false;
			}

		return true;
	}

	public function remove() {

        //throw new Exception("I AM   : ". $this->id);
        $sql_group_remove = "DELETE FROM mb_group WHERE mb_group_id = $1";
		$v = array($this->id);
		$t = array("i");
		$result = db_prep_query($sql_group_remove,$v,$t);
		if($result == false)
		{
			throw new Exception("Database error deleting group");
		}
		return true;
	}

	public function exists () {
		$sql_group = "SELECT group_id from mb_group WHERE mb_group_id = $1; ";
		$v = array($this->id);
		$t = array("i");
		$res_group = db_prep_query($sql_group,$v,$t);
		if ($row = db_fetch_array($res_group)) {
			return true;
		}
		return false;
	}

	public function load() {
		$sql_group = "SELECT * from mb_group WHERE mb_group_id = $1; ";
		$v = array($this->id);
		$t = array("i");
		$res_group = db_prep_query($sql_group,$v,$t);
		if($row = db_fetch_array($res_group)){

			$this->name = $row['mb_group_name'];

            //FIXME: needs checking
            $this->owner = $row['mb_group_owner'];
            $this->description	= $row['mb_group_description'];
            $this->title = $row["mb_group_title"];
            $this->address = $row["mb_group_address"];
            $this->postcode = $row["mb_group_postcode"];
            $this->city = $row["mb_group_city"];
            $this->stateorprovince = $row["mb_group_stateorprovince"];
            $this->country = $row["mb_group_country"];
            $this->voicetelephone = $row["mb_group_voicetelephone"];
            $this->facsimiletelephone = $row["mb_group_facsimiletelephone"];
            $this->email = $row["mb_group_email"];
            $this->logo_path = $row["mb_group_logo_path"];	
            $this->spatial = $row["mb_group_spatial"];	
		}
		else{
			 throw new Exception("Group with ID " . $this->id . " does not exist.");
			 return false;
		}
		return true;
	}

    /*
    * @return Array of Groups
    * @param $filter UNUSED! AssocArray, valid keys "id","name". Use SQL's % and _ to perform simple matching
    */
    public static function getList($filter) {

		$name = $filter->name ? $filter->name : null;
		$id = $filter->id && is_numeric($filter->id) ? 
			intval($filter->id) : null;
		$owner = $filter->owner && is_numeric($filter->owner) ? 
			intval($filter->owner) : null;
		
		$groups = Array();
		$sql_grouplist = "SELECT mb_group_id FROM mb_group";
	  
		$andConditions = array();
		$v = array();
		$t = array();

		if (!is_null($name)) {
			$v[]= $name;
			$t[]= "s";
	  		$andConditions[]= "mb_group_name LIKE $" . count($v);
		}

		if (!is_null($id)) {
			$v[]= $id;
			$t[]= "i";
	  		$andConditions[]= "mb_group_id = $" . count($v);
		}
		
		if (!is_null($owner)) {
			$v[]= $owner;
			$t[]= "i";
	  		$andConditions[]= "mb_group_owner = $" . count($v);
		}
		
		if (count($andConditions) > 0) {
			$sql_grouplist .= " WHERE " . implode("AND", $andConditions);
		}
		
		$sql_grouplist .= " ORDER BY mb_group_name";

		$res_groups = db_prep_query($sql_grouplist,$v,$t);
		
		while ($row = db_fetch_array($res_groups)) {
			try {
				$groups[] = new Group($row['mb_group_id']);
			}
			catch (Exception $E) {
				continue;
				//FIXME: should catch some errors here
			}
		}
		return $groups;
	}

    /*
    * tries to initialize a Groupobject by Name
    * @return A group Object
    * @param $name the name of the group to find
    */

    public static function byName($name) {

		if (is_null($name)) { 
			return new Group(null); 
		}

		$sql_group = "SELECT mb_group_id FROM mb_group WHERE mb_group_name = $1";
		$res_group = db_prep_query($sql_group, array($name), array("s"));
		
		if ($row = db_fetch_array($res_group)) {
			return new Group($row['mb_group_id']);
		}
		return null;
    }
	
	public function isValid () {
		if (!is_null($this->name)) {
			return true;
		}
//		new mb_warning("Group with ID " . $this->id . " does not exist.");
		return false;
	}
	
	public static function getGroupsByUser ($id) {
		$user = new User($id);
		if (!$user->isValid()) {
			new mb_exception("User ID " . $id . " invalid.");
			return array();		
		}
		$groups = $user->getGroupsByUser();
		if (!is_array($groups)) {
			new mb_notice("User " . $id . " is not member in any group.");
			return array();
		}
		return $groups;
	}
	
	public function getUser () {
		if (!$this->isValid()) {
			return array();
		}
		$sql = "SELECT fkey_mb_user_id FROM mb_user_mb_group WHERE fkey_mb_group_id = $1";
		$v = array($this->id);
		$t = array("i");
		$res = db_prep_query($sql, $v, $t);
		$users = array();
		while ($row = db_fetch_assoc($res)) {
			$users[]= new User($row["fkey_mb_user_id"]);
		}
		return $users;
	}

	public function getOwner () {
		if (!$this->isValid()) {
			return null;
		}
		$sql = "SELECT mb_group_owner FROM mb_group WHERE mb_group_id = $1";
		$v = array($this->id);
		$t = array("i");
		$res = db_prep_query($sql, $v, $t);
		$row = db_fetch_assoc($res);
		$owner = new User(intval($row["mb_group_owner"]));
		return $owner;
	}

	}
?>
