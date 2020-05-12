<?php
# License:
# Copyright (c) 2009, Open Source Geospatial Foundation
# This program is dual licensed under the GNU General Public License 
# and Simplified BSD license.  
# http://svn.osgeo.org/mapbender/trunk/mapbender/license/license.txt

require_once(dirname(__FILE__)."/../../core/globalSettings.php");
require_once(dirname(__FILE__)."/../classes/class_RPCEndpoint.php");
require_once(dirname(__FILE__)."/../classes/class_administration.php");

/**
 * A Mapbender user as described in the table mb_user.
 */
class User implements RPCObject{
	/**
	 * @var Integer The User ID
	 */
	var $id;
	var $name = "";
	// var $password = ""; // password is readonly, 
	var $owner = 0;  
	var $description ="";
	var $loginCount;
	var $email = "";
	var $phone ="";
	var $department ="";
	var $resolution = 72;
	var $organization ="";
	var $position = "";
	var $phone1 = "";
	var $fax = "";
	var $deliveryPoint ="";
	var $city ="";
	var $postalCode = null;
	var $country ="";
	var $url ="";
	var $realName = "";
	var $street = "";
	var $houseNumber = "";
	var $reference = "";
	var $forAttentionOf = "";
  	var $validFrom = null;
  	var $validTo = null;
  	var $passwordTicket = "";
	var $firstName = "";
	var $lastName = "";
	var $academicTitle = "";
	var $spatial = "";
	var $new_password = "FALSE";
  
    static $displayName = "User";
    static $internalName = "user";
	
	/**
	 * Constructor
	 * @param $userId Integer 	the ID of the user that	is represented by 
	 * 							this object.
	 */
	public function __construct () {
		if (func_num_args() === 1) {
			$this->id = intval(func_get_arg(0));
		}
		else {
			$this->id = Mapbender::session()->get("mb_user_id");
			if ($this->id == '' || !isset($this->id)) {
				$this->id = (integer)PUBLIC_USER;
				$e = new mb_notice("class_user: no user_id found in session use PUBLIC_USER with id - ".PUBLIC_USER." - !");
			}
		}
		try{
			$this->load();
		}
		catch(Exception $E)	{	
			new mb_exception($E->getMessage());
		}
	}	

	
	/**
	 * @return String the ID of this user
	 */
	public function __toString () {
		return (string) $this->id;	
	}


    /*
    * @return Assoc Array containing the fields to send to the user
    */
    public function getFields() {
        $result = array(
			"name" => $this->name,
			"password" =>  "*************",
			"owner" => $this->owner, 
			"description" => $this->description, 
			"loginCount" => $this->loginCount, 
			"email" => $this->email, 
			"phone" => $this->phone, 
			"department" => $this->department, 
			"resolution" => $this->resolution, 
			"organization" => $this->organization, 
			"position" => $this->position, 
			"phone1" => $this->phone1,	
			"fax" => $this->fax,	
			"deliveryPoint" => $this->deliveryPoint,	
			"city" => $this->city,	
			"postalCode" => $this->postalCode,	
			"country" => $this->country,	
			"url" => $this->url,
			"realName" => $this->realName,
			"street" => $this->street,
			"houseNumber" => $this->houseNumber,	
			"reference" => $this->reference,
			"forAttentionOf" => $this->forAttentionOf,
			"validFrom" => $this->validFrom,
			"validTo" => $this->validTo,
			"passwordTicket" => $this->passwordTicket,
			"firstName" => $this->firstName,
			"lastName" => $this->lastName,
			"academicTitle" => $this->academicTitle,
			"spatial" => $this->spatial
        );
		return $result;
	}

	public function isPublic () {
		if (defined("PUBLIC_USER") && intval($this->id) === intval(PUBLIC_USER)) {
			return true;
		}
		return false;
	}

	public function getGroupsByUser () {
		$sql = "SELECT fkey_mb_group_id FROM mb_user_mb_group WHERE fkey_mb_user_id = $1";
		$v = array($this->id);
		$t = array("i");
		$result = db_prep_query($sql,$v,$t);
		
		$groupArray = array();
		while ($row = db_fetch_array($result)) {
			$groupArray[]= intval($row["fkey_mb_group_id"]);
		}
		return $groupArray;		
	}

	public function create() {
		if ($this->name === "") { 
			$e = new Exception("Can' t create user without name");
		}
		
		$sql_user_create = "INSERT INTO mb_user (mb_user_name) VALUES ('" . 
			$this->name ."');";
		$v = array($this->name);
		$t = array("s");
	
		db_begin();
		
		$insert_result = db_query($sql_user_create);
		if($insert_result == false)	{
			db_rollback();
			throw new Exception("Could not insert new user");
		}

		$id = db_insertid($insert_result,'mb_user','mb_user_id');
		if ($id != 0) {
			$this->id = $id;
		}
	
		$commit_result = $this->commit();
		if($commit_result == false)	{
			try {
				db_rollback();
			}
			catch(Exception $E)	{
				throw new Exception("Could not set inital values of new user");
			}
		}
		db_commit();
        return true;
	}


	/*
	*	@param	$changes JSON  keys and their values of what to change in the object
	*/
	public function change($changes) {
        //FIXME: validate input
        if($changes->owner) {
          $owner = User::byName($changes->owner);
        }
		$this->name = isset($changes->name) ? $changes->name : $this->name;
		$this->owner = isset($changes->owner) ? $owner->id : $this->owner;
		$this->description = isset($changes->description) ? $changes->description : $this->description;
		$this->loginCount = isset($changes->loginCount) ? $changes->loginCount : $this->loginCount;
		$this->email = isset($changes->email) ? $changes->email : $this->email;
		$this->phone = isset($changes->phone) ? $changes->phone : $this->phone;
		$this->department = isset($changes->department) ? $changes->department : $this->department;
		$this->resolution = isset($changes->resolution) ? $changes->resolution : $this->resolution;
		$this->organization = isset($changes->organization) ? $changes->organization : $this->organization;
		$this->position = isset($changes->position) ? $changes->position : $this->position;
		$this->phone1 = isset($changes->phone1) ? $changes->phone1 : $this->phone1;
		$this->facsimile = isset($changes->facsimile) ? $changes->facsimile : $this->facsimile;
		$this->deliveryPoint = isset($changes->deliveryPoint) ? $changes->deliveryPoint : $this->deliveryPoint;
		$this->city = isset($changes->city) ? $changes->city : $this->city;
		$this->postalCode = isset($changes->postalCode) ? $changes->postalCode : $this->postalCode;
		$this->country = isset($changes->country) ? $changes->country : $this->country;
		$this->url = isset($changes->url) ? $changes->url : $this->url;
		$this->id = isset($changes->id) ? $changes->id : $this->id;
		$this->realName = isset($changes->realName) ? $changes->realName : $this->realName;
		$this->street = isset($changes->street) ? $changes->street : $this->street;
		$this->houseNumber = isset($changes->houseNumber) ? $changes->houseNumber : $this->houseNumber;
		$this->reference = isset($changes->reference) ? $changes->reference : $this->reference;
		$this->forAttentionOf = isset($changes->forAttentionOf) ? $changes->forAttentionOf : $this->forAttentionOf;
		$this->validFrom = isset($changes->validFrom) ? $changes->validFrom : $this->validFrom;
		$this->validTo = isset($changes->validTo) ? $changes->validTo : $this->validTo;
		$this->passwordTicket = isset($changes->passwordTicket) ? $changes->passwordTicket : $this->passwordTicket;
		$this->firstName = isset($changes->firstName) ? $changes->firstName : $this->firstName;
		$this->lastName = isset($changes->lastName) ? $changes->lastName : $this->lastName;
		$this->academicTitle = isset($changes->academicTitle) ? $changes->academicTitle : $this->academicTitle;
		$this->spatial = isset($changes->spatial) ? $changes->spatial : $this->spatial;
        return true;
	}

	public function commit() {

		$sql_update = "UPDATE mb_user SET ".
			"mb_user_name = $1, ".
			"mb_user_owner = $2, ".
			"mb_user_description = $3, ".
			"mb_user_email = $4, ".
			"mb_user_phone = $5, ".
			"mb_user_department = $6, ".
			"mb_user_resolution = $7, ".
			"mb_user_organisation_name = $8, ".
			"mb_user_position_name = $9, ".
			"mb_user_phone1 = $10, ".
			"mb_user_facsimile = $11, ".
			"mb_user_delivery_point = $12, ".
			"mb_user_city = $13, ".
			"mb_user_postal_code = $14, ".
			"mb_user_country = $15, ".
			"mb_user_online_resource = $16, ".
		 	"mb_user_realname = $17, ".
			"mb_user_street = $18, ".
			"mb_user_housenumber = $19, ".
			"mb_user_reference =$20, ".
			"mb_user_for_attention_of = $21, ".
			"mb_user_valid_from = $22, ".
			"mb_user_valid_to = $23, ".
			"mb_user_password_ticket = $24, ".
			"mb_user_firstname = $25, " . 
			"mb_user_lastname = $26, " . 
			"mb_user_academictitle = $27, " . 
			"mb_user_login_count = $28, " .		
			"mb_user_spatial = $29, " .
			"mb_user_new_password = $30 " .
			"WHERE mb_user_id = $31;";

		$v = array(
			$this->name,
			is_numeric($this->owner) ? intval($this->owner) : null,
			$this->description !== "" ? $this->description : null,
			$this->email !== "" ? $this->email : null,
			$this->phone !== "" ? $this->phone : null,
			$this->department !== "" ? $this->department : null,
			is_numeric($this->resolution) ? intval($this->resolution) : null,
			$this->organization !== "" ? $this->organization : null,
			$this->position !== "" ? $this->position : null,
			$this->phone1 !== "" ? $this->phone1 : null,
			$this->fax !== "" ? $this->fax : null,
			$this->deliveryPoint !== "" ? $this->deliveryPoint : null,
			$this->city !== "" ? $this->city : null,
			is_numeric($this->postalCode) ? intval($this->postalCode) : null,
			$this->country !== "" ? $this->country : null,
			$this->url !== "" ? $this->url : null,
			$this->realName !== "" ? $this->realName : null,
			$this->street !== "" ? $this->street : null,
			$this->houseNumber !== "" ? $this->houseNumber : null,	
			$this->reference !== "" ? $this->reference : null,
			$this->forAttentionOf !== "" ? $this->forAttentionOf : null,
			$this->validFrom,
			$this->validTo,
			$this->passwordTicket !== "" ? $this->passwordTicket : null,
			$this->firstName,
			$this->lastName,
			$this->academicTitle,
			is_numeric($this->loginCount) ? intval($this->loginCount) : 0,
			$this->spatial,
			$this->new_password,
			is_numeric($this->id) ? intval($this->id) : null,
		);

		$t = array(
			"s", "i", "s", "s", "s", 
			"s", "i", "s", "s", "s", 
			"s", "s", "s", "i", "s", 
			"s", "s", "s", "s", "s", 
			"s", "s", "s", "s", "s",
			"s", "s", "i", "s", "s", "i"
		);

		$update_result = db_prep_query($sql_update,$v,$t);
		if(!$update_result)	{
			throw new Exception("Database error updating User");
			return false;
		}
		return true;
	}

	public function remove() {

		$sql_user_remove = "DELETE FROM mb_user WHERE mb_user_id = $1";
		$v = array($this->id);
		$t = array("i");
		$result = db_prep_query($sql_user_remove,$v,$t);
		
		if($result == false) {
			$e = new mb_exception("Database error deleting user");
		}
		return true;
	}

	public function load() {
		$sql_user = "SELECT * from mb_user WHERE mb_user_id = $1; ";
		$v = array($this->id);
		$t = array("i");
		$res_user = db_prep_query($sql_user,$v,$t);

		if ($row = db_fetch_array($res_user)) {
			$this->name = $row['mb_user_name'];
			$this->owner = $row['mb_user_owner'];
			$this->description	= $row['mb_user_description'];
			$this->loginCount = $row['mb_user_login_count'];
			$this->email = $row['mb_user_email'];
			$this->phone = $row['mb_user_phone'];
			$this->department = $row['mb_user_department'];
			$this->resolution = $row['mb_user_resolution'];
			$this->organization = $row['mb_user_organisation_name'];
			$this->position = $row['mb_user_position_name'];
			$this->phone1 = $row['mb_user_phone1'];
			$this->fax = $row['mb_user_facsimile'];
			$this->deliveryPoint = $row['mb_user_delivery_point'];
			$this->city = $row['mb_user_city'];
			$this->postalCode = $row['mb_user_postal_code'];
			$this->country = $row['mb_user_country'];
			$this->url = $row['mb_user_online_resource'];
			$this->realName = $row['mb_user_realname'];
        	$this->street = $row['mb_user_street'];
        	$this->houseNumber = $row['mb_user_housenumber'];	
        	$this->reference = $row['mb_user_reference'];
        	$this->forAttentionOf = $row['mb_user_for_attention_of'];
        	$this->validFrom = $row['mb_user_valid_from'];
        	$this->validTo = $row['mb_user_valid_to'];
        	$this->passwordTicket = $row['mb_user_password_ticket'];
			$this->firstName = $row["mb_user_firstname"];
			$this->lastName = $row["mb_user_lastname"];
			$this->academicTitle = $row["mb_user_academictitle"];
			$this->spatial = $row["mb_user_spatial"];
			$this->new_password = $row["mb_user_new_password"];
		}
		else {
			 throw new Exception("no such User");
			 return false;
		}
		return true;
	}

	
	/*
	*	@param	$userId the Mapbender user id
	*	@param	$userTicket a user password ticket
	*/
	public function validUserPasswordTicket($userTicket) {
		$sql = "SELECT * FROM mb_user ";
		$sql .= "WHERE mb_user_id = $1 AND mb_user_password_ticket = $2";
	    $v = array($this->id,$userTicket);
		$t = array("i","s");
		$res = db_prep_query($sql,$v,$t);
		
		if($row = db_fetch_array($res)){
			if($row['mb_user_password_ticket'] == '' || $row['mb_user_password_ticket'] != $userTicket) {
				return false;
			}
		}
		else {
			throw new Exception("Database error validating user ticket.");
			return false;
		}
		return true;
	}
	
	/*
	*	@param	$newPassword values of the new password
	*	@param	$newPassword Mapbender user id
	*	@param	$newPassword Mapbender user ticket
	*/
	public function setPassword($newPassword,$userTicket) {
		//set new password in db
		$sql = "UPDATE mb_user SET mb_user_password = $1, mb_user_password_ticket = '' WHERE mb_user_id = $2 AND mb_user_password_ticket = $3";
		$v = array(md5($newPassword),$this->id,$userTicket);
		$t = array('s','i','s');
		$update_result = db_prep_query($sql,$v,$t);

		if (!$update_result)	{
			throw new Exception("Database error updating user password");
			return false;
		}
		return true;
	}
  
	
	public function setNewUserPasswordTicket () {
		$sql = "UPDATE mb_user SET mb_user_password_ticket = $1";			
		$sql.=" WHERE mb_user_id = $2";
	
		$passwordTicket = substr(md5(uniqid(rand())),0,30);
		
		$v = array($passwordTicket,$this->id);
		$t = array('s','i');     
		$res = db_prep_query($sql,$v,$t);	
		if(!$res){
			$e= new mb_exception(1);
			throw new Exception("Error setting new user password ticket");
			return false;
		}
		$this->passwordTicket = $passwordTicket;
		return true;
	}
	
	public function sendUserLoginMail () {
		$admin = new administration();
		
		$userMessage = "Your Mapbender login data:\n";
		$userMessage .= "Your login name is: ".$this->name."\n";
		$userMessage .= "Please set your password using the following link: \n";
		$mbUrl = substr(LOGIN, 0, -9);
		$userMessage .= $mbUrl."../javascripts/mod_confirmLogin.php?user_id=".$this->id."&user_name=".$this->name."&user_ticket=".$this->passwordTicket."\n";
		$userMessage .= "Follow this link to login to Mapbender: \n";
		$userMessage .= LOGIN."\n";
		
		$userMail = $admin->getEmailByUserId($this->id);
		if(!$admin->sendEmail("", "", $userMail, $this->name, utf8_decode("Your Mapbender account"), utf8_decode($userMessage), $error_msg)) {
			return "Registry data could not be send. Please check mail address.";
		}
		return "Registry data has been sent successfully.";
	}
	
    /*
    * @return Array of Users
    * @param $filter UNUSED! string that must be contained in the username
    */
	public static function getList($filter) {
		//FIXME: optimize
		$name = $filter->name ? $filter->name : null;
		$owner = $filter->owner && is_numeric($filter->owner) ? intval($filter->owner) : null;
		
		$users = Array();
		$sql_userlist = "SELECT mb_user_id FROM mb_user";
	  
		$andConditions = array();
		$v = array();
		$t = array();

		if (!is_null($name)) {
			$v[]= $name;
			$t[]= "s";
	  		$andConditions[]= "mb_user_name LIKE $" . count($v);
		}

		if (!is_null($owner)) {
			$v[]= $owner;
			$t[]= "i";
	  		$andConditions[]= "mb_user_owner = $" . count($v);
		}
		
		if (count($andConditions) > 0) {
			$sql_userlist .= " WHERE " . implode("AND", $andConditions);
		}
		
		$sql_userlist .= " ORDER BY mb_user_name";

      $res_users = db_prep_query($sql_userlist, $v, $t);

      while($row = db_fetch_array($res_users)) {
        try{
          $users[] = new User($row['mb_user_id']);
        }
        catch(Exception $E) {
          continue;
          //FIXME: should catch some errors here
        }
      }
      return $users;
    }

    /*
    * tries to initialize a userobject by Name
    * @return A user Object
    * @param $name the name of the user to find
    */

    public static function byName($name) {
    
      if($name == null) { return new User(null); }

      $sql_user = "SELECT mb_user_id FROM mb_user WHERE mb_user_name = '$name'";
      $res_user = db_query($sql_user);
      if($row = db_fetch_array($res_user))
      {
        return  new User($row['mb_user_id']);
      }
      return null;

    }
	
	/**
	 * Returns an array of application IDs that the user is allowed to access.
	 * 
	 * @return Array an array of application IDs
	 * @param $ignorepublic boolean whether or not to ignore 
	 * 								public applications (?)
	 */
	public function getApplicationsByPermission ($ignorepublic = false) {
		$mb_user_id = $this->id;
		$arrayGuis = array();
		$mb_user_groups = array();
		//exchange for the new role system - there are roles which don't include permissions explicitly
		$sql_groups = "SELECT fkey_mb_group_id FROM ";
		$sql_groups .= "(SELECT * from mb_user_mb_group left join mb_role on ";
		$sql_groups .= " mb_user_mb_group.mb_user_mb_group_type = mb_role.role_id ";
		$sql_groups .= " WHERE mb_role.role_exclude_auth != 1)  AS mb_user_mb_group WHERE fkey_mb_user_id = $1 ";
		//$sql_groups = "SELECT fkey_mb_group_id FROM mb_user_mb_group WHERE fkey_mb_user_id = $1 ";
		$v = array($mb_user_id);
		$t = array("i");
		$res_groups = db_prep_query($sql_groups,$v,$t);
		$cnt_groups = 0;
		while($row = db_fetch_array($res_groups)){
			$mb_user_groups[$cnt_groups] = $row["fkey_mb_group_id"];
			$cnt_groups++;
		}
		if($cnt_groups > 0){
			$v = array();
			$t = array();
			$sql_g = "SELECT gui.gui_id FROM gui JOIN gui_mb_group ";
			$sql_g .= " ON gui.gui_id = gui_mb_group.fkey_gui_id WHERE gui_mb_group.fkey_mb_group_id IN (";
			for($i=0; $i<count($mb_user_groups);$i++){
				if($i > 0){$sql_g .= ",";}
				$sql_g .= "$".strval($i+1);
				array_push($v,$mb_user_groups[$i]);
				array_push($t,"i");
			}
			$sql_g .= ") GROUP BY gui.gui_id";
			$res_g = db_prep_query($sql_g,$v,$t);
			while($row = db_fetch_array($res_g)){
				array_push($arrayGuis,$row["gui_id"]);
			}
		}
		$sql_guis = "SELECT gui.gui_id FROM gui JOIN gui_mb_user ON gui.gui_id = gui_mb_user.fkey_gui_id";
		$sql_guis .= " WHERE (gui_mb_user.fkey_mb_user_id = $1) ";
		if (!isset($ignore_public) OR $ignore_public== false){
			$sql_guis .= " AND gui.gui_public = 1 ";
		}
		$sql_guis .= " GROUP BY gui.gui_id";
		$v = array($mb_user_id);
		$t = array("i");
		$res_guis = db_prep_query($sql_guis,$v,$t);
		$guis = array();
		while($row = db_fetch_array($res_guis)){
			if(!in_array($row['gui_id'],$arrayGuis)){
				array_push($arrayGuis,$row["gui_id"]);
			}
		}
		return $arrayGuis;
	}	
	
	public function getOwnedWfs () {
		$sql = "SELECT wfs_id FROM wfs WHERE wfs_owner = $1";
		$res = db_prep_query($sql, array($this->id), array("i"));
		$wfsIdArray = array();
		while ($row = db_fetch_array($res)) {
			$wfsIdArray[]= $row["wfs_id"];
		}
		return $wfsIdArray;
	}
	
	public function getWfsByPermission () {
		$wfsArray = array();
		$appArray = $this->getApplicationsByPermission();
		if (is_array($appArray) && count($appArray) > 0) {
			$v = array();
			$t = array();
			$sql = "SELECT DISTINCT fkey_wfs_id FROM gui_wfs WHERE fkey_gui_id IN (";
			for ($i = 0; $i < count($appArray); $i++) {
				if($i > 0) { 
					$sql .= ",";
				}
				$sql .= "$".strval($i+1);
				
				array_push($v, $appArray[$i]);
				array_push($t, "s");
			}
			$sql .= ") ORDER BY fkey_wfs_id";
			
			$res = db_prep_query($sql,$v,$t);
			while($row = db_fetch_array($res)){
				$wfsArray[]= intval($row['fkey_wfs_id']);
			}			
		}
		return $wfsArray;
	}
	
	public function getWfsConfByWfsOwner () {
		$wfsConfIdArray = array();

		$sql = "SELECT * FROM wfs_conf, wfs WHERE wfs.wfs_owner = $1 AND " . 
			"wfs_conf.fkey_wfs_id = wfs.wfs_id ORDER BY wfs_conf.wfs_conf_id";
		$v = array($this->id);
		$t = array('i');
		$res = db_prep_query($sql,$v,$t);
		while($row = db_fetch_array($res)){
			$wfsConfIdArray[]= $row["wfs_conf_id"];
		}
		return $wfsConfIdArray;
	}
	
	/** identifies the IDs of WFS confs where the user is owner
	 * 
	 * @param Array appIdArray [optional] restrict to certain applications
	 * @return integer[] the IDs of the wfs_conf-table
	 */
	public function getWfsConfByPermission () {
		$userid = $this->id;
	 	$guisByPer = array();
//	 	1.
		$adm = new administration();
	 	$guisByPer = $adm->getGuisByPermission($userid, true);
		
		if (func_num_args() === 1) {
			$arg1 = func_get_arg(0);
			if (!is_array($arg1)) {
				$arg1 = array($arg1);
			}

			$appIdArray = $arg1;
			$guisByPer = array_intersect($guisByPer, $appIdArray);
			$guisByPer = array_keys(array_flip($guisByPer));
		}
		
//		$e = new mb_exception(serialize($guisByPer));
		
//	 	2. 
		$ownWFSconfs = array();
		if(count($guisByPer)>0){
			$v = array();
			$t = array();
			$sql = "SELECT wfs_conf.wfs_conf_id  FROM gui_wfs_conf, wfs_conf " .
					"where wfs_conf.wfs_conf_id = gui_wfs_conf.fkey_wfs_conf_id " .
					"and gui_wfs_conf.fkey_gui_id IN(";
			for($i=0; $i<count($guisByPer); $i++){
				if($i>0){ $sql .= ",";}
				$sql .= "$".strval($i+1);
				
				array_push($v, $guisByPer[$i]);
				array_push($t, "s");
			}
			$sql .= ") GROUP BY wfs_conf.wfs_conf_id ORDER BY wfs_conf.wfs_conf_id";
			
			$res = db_prep_query($sql,$v,$t);
			$i=0;
			while($row = db_fetch_array($res)){
				$ownWFSconfs[$i] = intval($row['wfs_conf_id']);
				$i++;
			}
		}
		return $ownWFSconfs;
	}
	
	/**
	 * Returns all WMCs that this user owns
	 * 
	 * @return integer[] an array of WMC ids; ids from table mb_user_wmc
	 */
	public function getWmcByOwner () {
		$sql = "SELECT wmc_serial_id FROM mb_user_wmc ";
		$sql .= "WHERE fkey_user_id = $1 GROUP BY wmc_serial_id";
		$v = array($this->id);
		$t = array("i");
		$res_wmc = db_prep_query($sql, $v, $t);

  		$wmcArray = array();
		while($row = db_fetch_array($res_wmc)){
			array_push($wmcArray, $row["wmc_serial_id"]);
		}
		return $wmcArray;
	}
	
	public function isLayerAccessible ($layerId) {
		$array_guis = $this->getApplicationsByPermission();
		$v = array();
		$t = array();
		$sql = "SELECT * FROM gui_layer WHERE fkey_gui_id IN (";
		$c = 1;
		for ($i = 0; $i < count($array_guis); $i++) {
			if ($i > 0) { 
				$sql .= ",";
			}
			$sql .= "$".$c;
			$c++;
			array_push($v, $array_guis[$i]);
			array_push($t, 's');
		}
		$sql .= ") AND fkey_layer_id = $".$c." AND gui_layer_status = 1";
		array_push($v,$layerId);
		array_push($t,'i');
		$res = db_prep_query($sql,$v,$t);
		
		return ($row = db_fetch_array($res)) ? true : false;
	}
	
	public function isWmsAccessible ($wms_id) {
		$array_guis = $this->getApplicationsByPermission();
		$v = array();
		$t = array();
		$sql = "SELECT * FROM gui_wms WHERE fkey_gui_id IN (";
		$c = 1;
		for ($i = 0; $i < count($array_guis); $i++) {
			if ($i > 0) { 
				$sql .= ",";
			}
			$sql .= "$".$c;
			$c++;
			array_push($v, $array_guis[$i]);
			array_push($t, 's');
		}
		$sql .= ") AND fkey_wms_id = $" . $c;
		array_push($v, $wms_id);
		array_push($t, 'i');
		$res = db_prep_query($sql, $v, $t);
		return ($row = db_fetch_array($res)) ? true : false;
	}
	
	public function getOwnedWms () {
		$sql = "SELECT wms_id FROM wms WHERE wms_owner = $1";
		$res = db_prep_query($sql, array($this->id), array("i"));
		$wmsIdArray = array();
		while ($row = db_fetch_array($res)) {
			$wmsIdArray[]= $row["wms_id"];
		}
		return $wmsIdArray;
	}

	public function getOwnedWmsScheduler () {
		$sql = "SELECT scheduler_id FROM scheduler, wms WHERE wms.wms_id = scheduler.fkey_wms_id AND wms.wms_owner = $1";
		$res = db_prep_query($sql, array($this->id), array("i"));
		$wmsSchedulerIdArray = array();
		while ($row = db_fetch_array($res)) {
			$wmsSchedulerIdArray[]= $row["scheduler_id"];
		}
		return $wmsSchedulerIdArray;
	}


	public function getOwnedGeodata () {
		$sql = "SELECT metadata_id FROM mb_metadata WHERE fkey_mb_user_id = $1";
		$res = db_prep_query($sql, array($this->id), array("i"));
		$geodataIdArray = array();
		while ($row = db_fetch_array($res)) {
			$e = new mb_exception("metadata_id: ".$row["metadata_id"]);
			$geodataIdArray[]= $row["metadata_id"];
		}
		return $geodataIdArray;
	}

	public function isWmsOwner ($wms_id) {
		// first get guis which deploy this wms.
        $sql = "SELECT fkey_gui_id FROM gui_wms WHERE fkey_wms_id = $1 GROUP BY fkey_gui_id";
		$v = array($wms_id);
		$t = array('i');
		$res = db_prep_query($sql,$v,$t);

		$gui = array();
		while($row = db_fetch_array($res)){
			$gui[] = $row["fkey_gui_id"];
		}

        if (count($gui) === 0) {
        	return false;
		}
		$v = array();
		$t = array();
		$c = 1;
		$sql = "(SELECT mb_user.mb_user_id FROM mb_user JOIN gui_mb_user ";
		$sql .= "ON mb_user.mb_user_id = gui_mb_user.fkey_mb_user_id ";
		$sql .= " WHERE gui_mb_user.mb_user_type = 'owner'";
		$sql .= " AND gui_mb_user.fkey_gui_id IN (";
		for ($i = 0; $i < count($gui); $i++) {
			if ($i > 0) { 
				$sql .= ",";
			}
			$sql .= "$".$c;
			$c++;
			array_push($v, $gui[$i]);
			array_push($t, 's');
		}
		$sql .= ") GROUP BY mb_user.mb_user_id";
		$sql .= ") UNION (";
		$sql .= "SELECT mb_user.mb_user_id FROM gui_mb_group JOIN mb_user_mb_group ON  mb_user_mb_group.fkey_mb_group_id = gui_mb_group.fkey_mb_group_id  JOIN mb_user ";
		$sql .= "ON mb_user.mb_user_id = mb_user_mb_group.fkey_mb_user_id ";
		$sql .= " WHERE gui_mb_group.mb_group_type = 'owner'";
		$sql .= " AND gui_mb_group.fkey_gui_id IN (";

		for ($j = 0; $j < count($gui); $j++) {
			if ($j > 0) { 
				$sql .= ",";
			}
			$sql .= "$".$c;
			$c++;
			array_push($v, $gui[$i]);
			array_push($t, 's');
		}
		$sql .= ") GROUP BY mb_user.mb_user_id)";

		$res = db_prep_query($sql,$v,$t);

		$user = array();
		while($row = db_fetch_array($res)){
			$user[] = intval($row["mb_user_id"]);
		}
		if (in_array($this->id, $user))	{
            return true;
        } 
		return false;
	}
	
	private function addSingleSubscription ($wmsId) {
		if (!is_numeric($wmsId)) {
			$e = new mb_exception("class_user.php: addSingleSubscription: WMS Id not a number.");
			return false;
		}
		$id = intval($wmsId);
		
		if ($this->cancelSingleSubscription($id)) {
			$sql = "INSERT INTO mb_user_abo_ows (fkey_mb_user_id, fkey_wms_id) VALUES ($1, $2)";   
			$v = array($this->id, $id);
			$t = array('i', 'i');
			$res = db_prep_query($sql, $v, $t);
			return ($res) ? true : false;
		}
		return false;
	}
	
	private function cancelSingleSubscription ($wmsId) {
		if (!is_numeric($wmsId)) {
			$e = new mb_exception("class_user.php: cancelSingleSubscription: WMS Id not a number.");
			return false;
		}
		$id = intval($wmsId);

		$sql = "DELETE FROM mb_user_abo_ows WHERE fkey_wms_id = $1 " . 
			"AND fkey_mb_user_id = $2";   
		$v = array($id, $this->id);
		$t = array('i', 'i');
		$res = db_prep_query($sql, $v, $t);	
		return ($res) ? true : false;
	}
	
	public function addSubscription ($wms) {
		if (is_array($wms)) {
			foreach ($wms as $wmsId) {
				$this->addSingleSubscription($wmsId);
			}
		}
		else {
			$this->addSingleSubscription($wms);
		}
	}

	public function cancelSubscription ($wms) {
		if (is_array($wms)) {
			foreach ($wms as $wmsId) {
				$this->cancelSingleSubscription($wmsId);
			}
		}
		else {
			$this->cancelSingleSubscription($wms);
		}
	}

	public function hasSubscription ($wmsId) {
		if (!is_numeric($wmsId)) {
			$e = new mb_exception("class_user.php: cancelSingleSubscription: WMS Id not a number.");
			return false;
		}
		$id = intval($wmsId);
		
		$sql = "SELECT * FROM mb_user_abo_ows WHERE fkey_wms_id = $1 AND " . 
			"fkey_mb_user_id = $2 LIMIT 1";
		$v = array($id, $this->id);
		$t = array('i', 'i');
		$res = db_prep_query($sql, $v, $t);
		$row = db_fetch_array($res);
		
		if (!isset($row['fkey_wms_id'])) {
	        return false;
		}
        return true;
	}
	
	public function isValid () {
		if (!is_null($this->name) && $this->name !== "") {
			return true;
		}
		return false;
	}
	
}
?>
