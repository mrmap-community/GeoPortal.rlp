<?php
# $Id: class_administration.php 9063 2014-09-10 11:01:22Z armin11 $
# http://www.mapbender.org/index.php/class_administration
# Copyright (C) 2002 CCGIS
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
require_once(dirname(__FILE__)."/class_user.php");

require(dirname(__FILE__) . "/phpmailer-1.72/class.phpmailer.php");

/**
 * class to wrap administration methods
 *
 * @uses phpmailer
 */ 
class administration {
    /**
     * checks whether the passed email-address is valid / following a pattern
     * @todo is this an exact representation of the RFC 2822?
     * @todo this should be checked: what about umlaut-domains and tld like '.museum'?
     * @see http://tools.ietf.org/html/rfc2822
     *
     * @param <string> a all lowercase email adress to test
     * @return <boolean> answer to "is the passed over email valid?""
     */
	function isValidEmail($email) {
        if(mb_eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email)) {
            return true;
		}
		return false;
	}

    /**
     * sends an email via php mailer
     *
     * @param string an email address for the "From"-field
     * @param string the displayed name for the "From"-field
     * @param string an email address for the "To"-field
     * @param string the displayed name for the "From"-field
     * @param string the text to be set as "Subject"
     * @param string the text of the emails body
     * @param string a reference to an error string
     * @param integer maximum line length
     */
	function sendEmail($fromAddr, $fromName, $toAddr, $toName, $subject, $body, &$error_msg, $wordWrapLength = 50){

		global $mailHost, $mailUsername, $mailPassword;
		if($fromAddr == ''){
			$fromAddr = MAILADMIN;
		}

		if($fromName == ''){
			$fromName = MAILADMINNAME;
		}

		if ($this->isValidEmail($fromAddr) && $this->isValidEmail($toAddr)) {
			$mail = new PHPMailer();

			if ($fromName != "" ) {
				$mail->FromName = $fromName;
			}

			$mail->IsSMTP();                  // set mailer to use SMTP
			$mail->Host = $mailHost;          // specify main and backup server
			$mail->SMTPAuth = true;           // turn on SMTP authentication
			$mail->Username = $mailUsername;  // SMTP username
			$mail->Password = $mailPassword;  // SMTP password

			$mail->From = $fromAddr;
			$mail->AddAddress($toAddr, $toName);
			#$mail->AddReplyTo("info@ccgis.de", "Information");

			$mail->WordWrap = $wordWrapLength;                                 // set word wrap to 50 characters
			#$mail->AddAttachment("/var/tmp/file.tar.gz");         // add attachments
			#$mail->AddAttachment("/tmp/image.jpg", "new.jpg");    // optional name
			$mail->IsHTML(false);                                  // set email format to HTML

			$mail->Subject = "[".$fromName."] ".$subject;
			$mail->Body    = $body;
			#$mail->AltBody = "This is the body in plain text for non-HTML mail clients";

			$error_msg='';

			if(!$mail->Send())
			{
			   $error_msg .= "Mailer Error: " . $mail->ErrorInfo;
			   return false;
			}

			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Removes the namespace from a tag name
	 * @return String like "gml"
	 * @param $s String like "ogc:gml"
	 */
	public static function sepNameSpace($s) {
		$c = strpos($s,":"); 
		if ($c > 0) {
			return substr($s,$c+1);	
		}
		return $s;
	}

	/**
	 * Parses an XML with PHP XML parser, see 
	 * http://de2.php.net/manual/de/book.xml.php
	 * 
	 * @return Array an associative array of tags, values, attributes and types
	 * @param $someXml String The actual XML as string.
	 */
	public static function parseXml ($someXml) {
		$values = null;
		$tags = null;
		
		$parser = xml_parser_create(CHARSET);

		// set parsing options
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		// internal encoding of Mapbender is UTF-8!!!
		xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");

		// this is the actual parsing process
		xml_parse_into_struct($parser, $someXml, $values, $tags);

		// check if an error occured
		$code = xml_get_error_code ($parser);
		if ($code) {
			// report error
			$line = xml_get_current_line_number($parser); 
			$errorMessage = xml_error_string($code) . " in line " . $line;
			$mb_exception = new mb_exception($errorMessage);
			return false;
		}
		xml_parser_free($parser);
	
		return $values;	
	}

    /**
     * returns a random password with numbers and chars both lowercase and uppercase (0-9a-zA-Z)
     *
     * @return string the new password
     */
 	function getRandomPassword() {

		// password length
		$max = 16;

		//new password
		$newpass = "";

		for ($i=0;$i <= $max;$i++) {
			//die ASCII-Zeichen 48 - 57 sind die zahlen 0-9
			//die ASCII-Zeichen 65 - 90 sind die buchstaben A-Z (Gro�)
			//die ASCII-Zeichen 97 - 122 sind die buchstaben a-z (Klein)
			$ascii = 0;
			do {
				$ascii=rand(48,122);
			} while ( ($ascii > 57 && $ascii < 65) || ($ascii > 90 && $ascii < 97));
			$newpass .= chr($ascii);
		}
		return $newpass;
 	}

    /**
     * returns the name of a mapbender user which owns the GUI identified by the passed over gui_id.
     *
     * @param string the gui_id
     * @return integer the user id of the owner
     */
 	function getOwnerByGui($gui_id){
		$sql = "(SELECT mb_user.mb_user_id";
		$sql .= "FROM mb_user ";
		$sql .= "JOIN gui_mb_user ON mb_user.mb_user_id = gui_mb_user.fkey_mb_user_id ";
		$sql .= "WHERE gui_mb_user.mb_user_type = 'owner' ";
		$sql .= "AND gui_mb_user.fkey_gui_id = $1 ";
		$sql .= "GROUP BY mb_user.mb_user_id ";
		$sql .= ") ";
		$sql .= "UNION ( ";
		$sql .= "SELECT mb_user.mb_user_id ";
		$sql .= "FROM gui_mb_group ";
		$sql .= "JOIN mb_user_mb_group ON mb_user_mb_group.fkey_mb_group_id = gui_mb_group.fkey_mb_group_id ";
		$sql .= "JOIN mb_user ON mb_user.mb_user_id = mb_user_mb_group.fkey_mb_user_id ";
		$sql .= "JOIN gui_mb_user ON mb_user.mb_user_id = gui_mb_user.fkey_mb_user_id ";
		$sql .= "WHERE gui_mb_group.mb_group_type = 'owner' ";
		$sql .= "AND gui_mb_group.fkey_gui_id = $2 ";
		$sql .= "GROUP BY mb_user.mb_user_id)";
		$owner = array();
		$v = array($gui_id,$gui_id);
		$t = array('s','s');
		$res = db_prep_query($sql,$v,$t);
		$cnt = 0;
		while($row = db_fetch_array($res)){
			$owner[$cnt] = $row["mb_user_id"];
			$cnt++;
		}
		return $owner;
 	}

    /**
     * returns the content of the field mb_user_email for the given userid.
     *
     * @param integer userid the id of the current user
     * @return string the email if one row is found or false if none is found
     */
	function getEmailByUserId($userid){
		$sql = "SELECT mb_user_email FROM mb_user ";
		$sql .= "WHERE mb_user_id = $1 GROUP by mb_user_email";
        // TODO why do we group, when userid is a primary key?
		$v = array($userid);
		$t = array('i');
		$res = db_prep_query($sql,$v,$t);
		// TODO shall the next two lines be removed?
        $count_g = 0;
		$array = array();
		$row = db_fetch_array($res);
		if ($row) {
			return $row["mb_user_email"];
		}
		else {
			return false;
		}
	}

    /**
     * returns the name of the user for the given userid.
     *
     * @param	integer		the userid
     * @return 	string		the name if one row is found or false if none is foundd
     */
	function getUserNameByUserId($userid){
		$sql = "SELECT mb_user_name FROM mb_user ";
		$sql .= "WHERE mb_user_id = $1 GROUP BY mb_user_name";
        // TODO why do we group, when userid is a primary key?
		$v = array($userid);
		$t = array("i");
		$res = db_prep_query($sql,$v,$t);
        // TODO shall the next two lines be removed?
		$count_g = 0;
		$array = array();
		$row = db_fetch_array($res);
		if ($row) {
			return $row["mb_user_name"];
		}
		else {
			return false;
		}
	}

    /**
     * returns one or more userids from the given email or false,
     * if there is no record in the database matching the given email
     *
     * @param	string	the email
     * @return	mixed	an array of userids or false when no records matches
     */
 	function getUserIdByEmail($email){
		$sql = "SELECT  mb_user_id FROM mb_user ";
		$sql .= "WHERE mb_user_email = $1 GROUP BY mb_user_id";
		$v = array($email);
		$t = array('s');
		$res = db_prep_query($sql,$v,$t);
  		$count_g = 0;
  		$array = array();
		while($row = db_fetch_array($res)){
			$array[$count_g] = $row["mb_user_id"];
			$count_g++;
		}
		if ($count_g >0)	{
			return $array;
		}
		else {
			return false;
		}
 	}

    /**
     * returns one or more subscribers for the given wm_id as an array.
     *
     * @param	integer		the wms_id
     * @return	mixed		an array of user ids which have subscribed the wms
     */
	function getSubscribersByWms($wms_id){
		$sql = "SELECT DISTINCT fkey_mb_user_id FROM mb_user_abo_ows WHERE fkey_wms_id = $1";
		$v = array($wms_id);
		$t = array('i');
		$count = 0;
		$res = db_prep_query($sql,$v,$t);
		while($row = db_fetch_array($res)){
			$user[$count] = $row["fkey_mb_user_id"];
			$count++;
		}
		if ($count > 0) {
			return $user;
		} else {
			return false;
		}
	}

    /**
     * returns one or more owners for the given wm_id. First all guis deploying
     * this wms are selected. Afterwards for each of the guis the owners are
     * selected and stored within an array.
     *
     * @param	integer		the wms_id
     * @return	mixed		an array of user ids which use the wms in their guis 
     * 						(both for persona or group ownership)
     */
	function getOwnerByWms($wms_id){
		// first get guis which deploy this wms.
        $sql = "SELECT fkey_gui_id FROM gui_wms WHERE fkey_wms_id = $1 GROUP BY fkey_gui_id";
		$v = array($wms_id);
		$t = array('i');
		$count=0;
		$res = db_prep_query($sql,$v,$t);
		while($row = db_fetch_array($res)){
			$gui[$count] = $row["fkey_gui_id"];
			$count++;
		}

		if ($count > 0) {
			// this is not needed! count($gui) is always equal to $count
            if(count($gui)>0) {
				$v = array();
				$t = array();
				$c = 1;
				$sql = "(SELECT mb_user.mb_user_id FROM mb_user JOIN gui_mb_user ";
				$sql .= "ON mb_user.mb_user_id = gui_mb_user.fkey_mb_user_id ";
				$sql .= " WHERE gui_mb_user.mb_user_type = 'owner'";
				$sql .= " AND gui_mb_user.fkey_gui_id IN (";
				for($i=0; $i<count($gui); $i++){
					if($i>0){ $sql .= ",";}
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

				for($j=0; $j<count($gui); $j++){
					if($j>0){ $sql .= ",";}
					$sql .= "$".$c;
					$c++;
					array_push($v, $gui[$i]);
					array_push($t, 's');
				}
				$sql .= ") GROUP BY mb_user.mb_user_id)";

				$user = array();
				$res = db_prep_query($sql,$v,$t);
			}
			$cnt = 0;

			while($row = db_fetch_array($res)){
				$user[$cnt] = $row["mb_user_id"];
				$cnt++;
			}
			if ($cnt>0)	{
                return $user;
            } else {
                return false;
            }
		} else {
          return false;
        }
	}
    /**
     * tests whether a gui with the passed gui_id exits and returns true or false.
     *
     * @param	string		the gui_id to test
     * @return	boolean		Does a Gui with the passed over gui_id exist?
     */
	function guiExists($id){
		$sql = "SELECT * FROM gui WHERE gui_id = $1 ";
		$v = array($id);
		$t = array('s');
		$res = db_prep_query($sql,$v,$t);
		$row = db_fetch_array($res);
		if ($row) {
			return true;
		}
		else {
			return false;
		}
	}

    /**
     * deletes a {@link http://www.mapbender.org/index.php/WMC WMC} entry specified by wmc_id and user_id
     *
     * @param	integer		the user_id
     * @param	string		the wmc_id
     * @return	boolean		Did the query run succesfull? This does not necessarily mean that 
     * 						an entry was deleted.
     * @deprecated
     */
 	function deleteWmc($wmc_id, $user_id){
		$e = new mb_notice("administration->deleteWmc is deprecated, use wmc->delete instead!"); 
		
		$wmc = new wmc();
		return $wmc->delete($wmc_id, $user_id);
 	}

    /**
     * inserts a gui with the specified gui_id, after checking the uniqueness of teh gui id.
     *
     * @uses administration::guiExists()
     * @param 	string $guiId	the name and id of the gui to insert.
     * @return 	boolean			could the gui be inserted?
     */
	function insertGui($guiId) {
		if (!$this->guiExists($guiId)) {
			$sql = "INSERT INTO gui VALUES ($1, $2, '', '1')";
			$v = array($guiId,$guiId);
			$t = array('s','s');
			$res = db_prep_query($sql,$v,$t);
			if ($res) {
				return true;
			}
		}
		return false;
	}

    /**
     * deletes links between user and guis in gui_mb_user for a certain gui.
     *
     * @param string 	the gui name
     * @return boolean 	could the sql be executed without errors. This does not 
     * 					necessarily mean, that entries were deleted
     */
	function delAllUsersOfGui($guiId) {
		$sql = "DELETE FROM gui_mb_user WHERE fkey_gui_id = $1 ";
		$v = array($guiId);
		$t = array('s');
		$res = db_prep_query($sql,$v,$t);
		if (!$res) {
			return false;
		}
		else {
			return true;
		}
	}

    /**
     * returns an array of WMS for a given user id
     * @uses getGuisByOwner
     * @param integer $user_id		the user id
     * @return integer[] 			wms ids for the user
     */
	function getWmsByOwner($user_id){
		$gui_list = $this->getGuisByOwner($user_id,true);
		return $this->getWmsByOwnGuis($gui_list);
	}

    /**
     * returns an array of WMS where the owner is the user with the passed user_id
     * @param integer	the user id
     * @return array 	wms ids for the user
     */
	function getWmsByWmsOwner($user_id){
		$sql = "SELECT wms_id FROM wms WHERE wms_owner = $1 ORDER BY wms_id";
		$v = array($user_id);
		$t = array('i');
		$res = db_prep_query($sql,$v,$t);
		$r = array();
		while($row = db_fetch_array($res)){
			array_push($r,$row["wms_id"]);
		}
		return $r;
	}

    /**
     * returns an array of user which are associated with a wms
     *
     * @param integer	the wms id
     * @return array	user_ids for the wms
     */
	function getUserByWms($wms_id){
		$sql = "SELECT fkey_gui_id FROM gui_wms WHERE fkey_wms_id = $1 GROUP BY fkey_gui_id";
		$v = array($wms_id);
		$t = array('i');
		$count=0;
		$res = db_prep_query($sql,$v,$t);
		while($row = db_fetch_array($res)){
			$gui[$count] = $row["fkey_gui_id"];
			$count++;
		}
		$c = 1;
		$v = array();
		$t = array();
		if(count($gui)>0){
			$sql = "(SELECT mb_user.mb_user_id FROM mb_user JOIN gui_mb_user ";
			$sql .= "ON mb_user.mb_user_id = gui_mb_user.fkey_mb_user_id ";
			$sql .= " WHERE gui_mb_user.fkey_gui_id IN (";
			for($i=0; $i<count($gui); $i++){
				if($i>0){ $sql .= ",";}
				$sql .= "$".$c;
				array_push($v,$gui[$i]);
				array_push($t, 's');
				$c++;
			}
			$sql .= ") GROUP BY mb_user.mb_user_id) UNION";
			$sql .= "(SELECT mb_user.mb_user_id FROM gui_mb_group JOIN mb_user_mb_group ON   mb_user_mb_group.fkey_mb_group_id = gui_mb_group.fkey_mb_group_id     JOIN mb_user ";
			$sql .= "ON mb_user.mb_user_id = mb_user_mb_group.fkey_mb_user_id ";
			$sql .= " WHERE gui_mb_group.fkey_gui_id IN (";
			for($i=0; $i<count($gui); $i++){
				if($i>0){ $sql .= ",";}
				$sql .= "$".$c;
				array_push($v,$gui[$i]);
				array_push($t, 's');
				$c++;
			}
			$sql .= ") GROUP BY mb_user.mb_user_id )";
			$user = array();
			$res = db_prep_query($sql,$v,$t);
			$cnt = 0;
			while($row = db_fetch_array($res)){
				$user[$cnt] = $row["mb_user_id"];
				$cnt++;
			}
		}
		return $user;
	}

    /**
     * selects the WMS-title for a given wms id.
     *
     * @param integer 			the wms id
     * @return string|boolean 	either the title of the wms as string or false when none exists
     */
	function getWmsTitleByWmsId($id){
		$sql = "SELECT wms_title FROM wms WHERE wms_id = $1 GROUP BY wms_title";
		$v = array($id);
		$t = array('i');
		$res = db_prep_query($sql,$v,$t);
		$row = db_fetch_array($res);
		if ($row) return $row["wms_title"]; else return false;
	}

    /**
     * selects the Layer-title for a given layer id.
     *
     * @param integer			the wms id
     * @return string|boolean	either the title of the wms as string or false when none exists
     */
	function getLayerTitleByLayerId($id){
		$sql = "SELECT layer_title FROM layer WHERE layer_id = $1 GROUP BY layer_title";
		$v = array($id);
		$t = array('i');
		$res = db_prep_query($sql,$v,$t);
		$row = db_fetch_array($res);
		if ($row) return $row["layer_title"]; else return false;
	}

    /**
     * selects an array of sublayer ids for a given layer id.
     *
     * @param integer			the layer id
     * @return array	                list of sublayer ids
     */
	function getSubLayers($layerId, $subLayer = array(), $layerPos = null, $wmsId = null) {
		if (!isset($layerPos)) {
			//get layer_pos for requested layer_id
			$sql = "SELECT layer_pos, fkey_wms_id FROM layer WHERE layer_id = $1";
			$v = array($layerId);
			$t = array("i");
			$res = db_prep_query($sql, $v, $t);
			$layerPosRow = db_fetch_array($res);
			$layerPos = $layerPosRow['layer_pos'];
			$wmsId = $layerPosRow['fkey_wms_id'];
			
		}
		//select all childs of given layer
		$sub_layer_sql = "SELECT layer_id, layer_pos FROM layer WHERE fkey_wms_id = $1 AND layer_parent = $2 ORDER BY layer_pos";
		$v = array($wmsId, $layerPos);
		$t = array("i","s");
		$res_sub_layer_sql = db_prep_query($sub_layer_sql, $v, $t);
		while ($sub_layer_row = db_fetch_array($res_sub_layer_sql)) {
			$subLayer[] = $sub_layer_row['layer_id'];
			//recursive creation
			$subLayer = $this->getSubLayers($layerId, $subLayer, $sub_layer_row['layer_pos'], $wmsId);
		}
		return $subLayer;
	}

    /**
     * selects the WMC for a given wmc_id.
     *
     * @param integer			the wms id
     * @return string|boolean	either the wmc as string or false when none exists
     * @deprecated
     */
	function getWmcById($id){
		$e = new mb_notice("administration->getWmcById is deprecated, use wmc->getDocument instead!"); 

		$wmc = new wmc();
		return $wmc->getDocument($id);
	}

    /**
     * resets the login count of a given user to 0
     * @param integer	the user id
     * @return boolean 	could the login count be reseted?
     */
	function resetLoginCount($userId) {
		// TODO: isn't mb_user_login_count a integer?
        $sql = "UPDATE mb_user SET mb_user_login_count = '0' ";
		$sql .= "WHERE mb_user_id = $1 ";
		$v = array($userId);
		$t = array('i');
		$res = db_prep_query($sql,$v,$t);
		if (!$res) {
			return false;
		}
		else {
			return true;
		}
	}

	function getAllFilteredUsers($owner) {
		$allUsers = array();
		$sql = "SELECT mb_user_id,mb_user_name,mb_user_email FROM mb_user ";
		$sql .= " WHERE mb_user_owner = $1 ORDER BY mb_user_name ";
		$v = array($owner);
		$t = array('i'); 
		$res = db_prep_query($sql, $v, $t);
		while ($row = db_fetch_array($res)) {
			array_push($allUsers, array("mb_user_id" => $row["mb_user_id"], "mb_user_name" => $row["mb_user_name"], "mb_user_email" => $row["mb_user_email"]));
		}
		$json = new Mapbender_JSON();
		$output = $json->encode($allUsers);

		header("Content-type:text/plain; charset=utf-8");
		return $output;
	}

	function getAllUserColumns($userId) {
		$userArray = array();
		$sql = "SELECT * FROM mb_user WHERE mb_user_id = $1";
		$v = array($userId); 
		$t = array('s');
		$res = db_prep_query($sql,$v,$t);
		while ($row = db_fetch_array($res)) {
			foreach ($row as $key => $value){
				if (is_int($key)==false) {
					array_push($userArray, array("id" => $key, "value" => $value));
				}
			}
		}
		$json = new Mapbender_JSON();
		$output = $json->encode($userArray);

		header("Content-type:text/plain; charset=utf-8");
		return $output;
	}

	/**
	 * Returns an array of column names and fieldtype for a table.
	 * 
	 */
	function getTableColumns($table) {
		$sql = "SELECT * FROM $table LIMIT 1";
		$columnArray = array();

		$v = array(); 
		$t = array();
		$res = db_prep_query($sql,$v,$t);

		$i = 0;
		while ($i < db_num_fields($res)) {
			if(db_field_type($res, $i)=="varchar") {
				$fieldType = "s";
			}
			else {
				$fieldType = "i";
			}
			$columnArray[db_fieldname($res, $i)] = $fieldType;
			$i++;
		}

		return $columnArray;
	}

	function deleteTableRecord($table,$keyField,$keyFieldValue) {
		$sql = "DELETE FROM $table WHERE $keyField = $1";
		$v = array($keyFieldValue); 
		$t = array('s');
		$res = db_prep_query($sql,$v,$t);
		if (!$res) {
			return false;
		}
		else {
			return true;
		}
	}

	function getUserIdByUserName($username){
		$sql = "SELECT mb_user_id FROM mb_user ";
		$sql .= "WHERE mb_user_name = $1 GROUP BY mb_user_id";
		$v = array($username);
		$t = array('s');
		$res = db_prep_query($sql,$v,$t);
		$row = db_fetch_array($res);
		if ($row) return $row["mb_user_id"]; else return false;
	}

	function setUserAsGuiOwner($guiId, $userId) {
		$sql = "UPDATE gui_mb_user SET mb_user_type = 'owner' ";
		$sql .= "WHERE fkey_gui_id = $1 AND fkey_mb_user_id = $2 ";
		$v = array($guiId,$userId);
		$t = array('s','i');
		$res = db_prep_query($sql,$v,$t);

		if (!$res) {
			return false;
		}
		else {
			return true;
		}
 	}

	function getGuiIdByGuiName($guiTitle){
		$sql = "SELECT gui_id FROM gui ";
		$sql .= "WHERE gui_name = $1 GROUP BY gui_id";
		$v = array($guiTitle);
		$t = array('s');
		$res = db_prep_query($sql,$v,$t);
  		$count_g = 0;
  		$array = array();
		while($row = db_fetch_array($res)){
			$array[$count_g] = $row["gui_id"];
			$count_g++;
		}
		if ($count_g >0)	{
			return $array;
		}
		else {
			return false;
		}
 	}

	function getGuisByOwner($user_id,$ignore_public)
	{
		$sql_guis = "SELECT gui.gui_id FROM gui,gui_mb_user ";
		$sql_guis .= "WHERE (gui.gui_id = gui_mb_user.fkey_gui_id AND gui_mb_user.fkey_mb_user_id = $1) ";
		if (!isset($ignore_public) OR $ignore_public == false){
			$sql_guis .= " AND gui.gui_public = 1 ";
		}
		$sql_guis .= " AND gui_mb_user.mb_user_type = 'owner' GROUP BY gui.gui_id";
		$sql_guis .= " ORDER by gui.gui_id";
		$v = array($user_id);
		$t = array('i');
		$res_guis = db_prep_query($sql_guis,$v,$t);
  		$count_g = 0;
  		$arrayGuis = array();
		while($row = db_fetch_array($res_guis)){
			$arrayGuis[$count_g] = $row["gui_id"];
			$count_g++;
		}
		return $arrayGuis;
 	}

	/**
	 * @deprecated
	 */
 	function getWmcByOwner($user_id){
		$e = new mb_notice("administration->getWmcByOwner is deprecated, use user->getWmcByOwner instead!"); 

		$user = new User($user_id);
		return $user->getWmcByOwner();
 	}

	/**
	 * @deprecated
	 */
	function getGuisByPermission($mb_user_id,$ignorepublic){
		$e = new mb_notice("administration->getGuisByPermission is deprecated, use user->getApplicationsByPermission instead!"); 
		$user = new User($mb_user_id);
		return $user->getApplicationsByPermission($ignorepublic);
	}

	function getWmsByOwnGuis($array_gui_ids){
		if(count($array_gui_ids)>0){
			$v = array();
			$t = array();
			$sql = "SELECT fkey_wms_id from gui_wms WHERE gui_wms.fkey_gui_id IN(";
			for($i=0; $i<count($array_gui_ids); $i++){
				if($i>0){ $sql .= ",";}
				$sql .= "$".strval($i+1);
				array_push($v, $array_gui_ids[$i]);
				array_push($t, "s");
			}
			$sql .= ") GROUP BY fkey_wms_id ORDER BY fkey_wms_id";
			$res = db_prep_query($sql,$v,$t);
			$ownguis = array();
			$i=0;
			while($row = db_fetch_array($res)){
				$ownguis[$i] = $row['fkey_wms_id'];
				$i++;
			}
		}
		return $ownguis;
	}
	
	function getRootLayerByWms($wms_id){
		$sql = "SELECT layer_id from layer WHERE fkey_wms_id = $1 AND layer_pos = '0' LIMIT 1";
		$v = array($wms_id);
		$t = array('i');
		$res = db_prep_query($sql,$v,$t);
		$layer_id_array = array();
		$row = db_fetch_array($res);
		return $row['layer_id'];
	}	

	function getLayerByWms($wms_id){
		$sql = "SELECT layer_id from layer WHERE fkey_wms_id = $1 AND layer_pos NOT IN ('0') GROUP BY layer_id, layer_title ORDER BY layer_title";
		$v = array($wms_id);
		$t = array('i');
		$res = db_prep_query($sql,$v,$t);
		$layer_id_array = array();
		while($row = db_fetch_array($res)){
			$layer_id_array[count($layer_id_array)] = $row['layer_id'];
		}
		return $layer_id_array;
	}

 function getAllLayerByWms($wms_id){
                $sql = "SELECT layer_id from layer WHERE fkey_wms_id = $1 GROUP BY layer_id, layer_title ORDER BY layer_title";
                $v = array($wms_id);
                $t = array('i');
                $res = db_prep_query($sql,$v,$t);
                $layer_id_array = array();
                while($row = db_fetch_array($res)){
                        $layer_id_array[count($layer_id_array)] = $row['layer_id'];
                }
                return $layer_id_array;
        }





	function getWmsOwner($wms_id){
		$sql = "SELECT fkey_gui_id FROM gui_wms WHERE fkey_wms_id = $1 GROUP BY fkey_gui_id";
		$v = array($wms_id);
		$t = array('i');
		$count=0;
		$res = db_prep_query($sql,$v,$t);
		while($row = db_fetch_array($res)){
			$gui[$count] = $row["fkey_gui_id"];
			$count++;
		}
		$v = array();
		$t = array();
		if(count($gui)>0){
			$sql = "SELECT mb_user.mb_user_id FROM mb_user JOIN gui_mb_user ";
			$sql .= "ON mb_user.mb_user_id = gui_mb_user.fkey_mb_user_id WHERE";
			$sql .= " gui_mb_user.fkey_gui_id IN (";
			for($i=0; $i<count($gui); $i++){
				if($i>0){ $sql .= ",";}
				$sql .= "$".($i+1);
				array_push($v,$gui[$i]);
				array_push($t,'s');
			}
			$sql .= ")";
			$sql .= " AND gui_mb_user.mb_user_type = 'owner' GROUP BY mb_user.mb_user_id";
			$res = db_prep_query($sql,$v,$t);
			$i=0;
			$wmsowner = array();
			while($row = db_fetch_array($res)){
				$wmsowner[$i]=$row['mb_user_id'];
				$i++;
			}
		}
		return $wmsowner;
	}

	function insertUserAsGuiOwner($guiId, $userId){
		$sql = "INSERT INTO gui_mb_user VALUES ($1, $2, 'owner')";
		$v = array($guiId,$userId);
		$t = array('s','i');
		$res = db_prep_query($sql,$v,$t);
		if (!$res) {
			return false;
		}
		else {
			return true;
		}
 	}

   	function checkModulePermission($arrayGuis, $modulePath, $column){
   		$check = true;
   		if($check == true){
	   		$perm = false;
	   		if(count($arrayGuis)>0){
	   			$v = array();
	   			$t = array();
		   		$sql = "SELECT ".$column." FROM gui_element WHERE fkey_gui_id IN(";
		   		for($i=0; $i<count($arrayGuis); $i++){
		   			if($i > 0){ $sql .= ","; }
		   			$sql .= "$".($i+1);
		   			array_push($v,$arrayGuis[$i]);
		   			array_push($t,'s');
		   		}
		   		$sql .= ")";
				$res = db_prep_query($sql,$v,$t);
				$cnt = 0;
				while($row = db_fetch_array($res)){
					if(mb_strpos(stripslashes($row[$column]),$modulePath) !== false){
						$perm = true;
					}
					$cnt++;
				}
	   		}
			return $perm;
   		}
   		else{
   			return true;
   		}
   	}


	/**
	 * Checks if a user is allowed to access a GUI element
	 * 
	 * @return boolean 
	 * @param $arrayGuis Object
	 * @param $modulePath Object
	 * @param $elementTag Object
	 */
   	function checkModulePermission_new($userId, $modulePath, $elementTag){
   		if (CHECK) {
			$arrayGuis = $this->getGuisByPermission($userId, true);

			switch ($elementTag) {
				case "a" :
					$column = "e_attributes";
					$pattern = "/^.*href\s*=\s*(\'|\")\.\.((\/[a-zA-Z0-9_\/\.]+)+)(\?|\'|\").*$/";
					$replace = "$2";
					break;
				case "iframe" :
					$column = "e_src";
					$pattern = "/^\.\.((\/[a-zA-Z0-9_\/\.]+)+)(\?|\'|\").*$/";
					$replace = "$1";
					break;
			}

	   		if ($column && count($arrayGuis) > 0) {
	   			$v = array();
	   			$t = array();
		   		$sql = "SELECT DISTINCT ".$column." FROM gui_element WHERE fkey_gui_id IN (";
		   		for($i=0; $i<count($arrayGuis); $i++){
		   			if($i > 0){ $sql .= ","; }
		   			$sql .= "$".($i+1);
		   			array_push($v,$arrayGuis[$i]);
		   			array_push($t,'s');
		   		}
		   		$sql .= ") ORDER BY " . $column;
				$res = db_prep_query($sql,$v,$t);
				while($row = db_fetch_array($res)){
					if ($row[$column]) {
						if (preg_match($pattern, stripslashes($row[$column]))) {
							$dbFilename = preg_replace($pattern, $replace, stripslashes($row[$column]));
							$e = new mb_notice($dbFilename . " - " . $modulePath);

							if(strpos($modulePath, $dbFilename) !== false){
								return true;
							}
						}
					}
				}
	   		}
			return false;
   		}
		return true;
   	}
	
	function getWMSOWSstring($wms_id){
   		$sql = "SELECT wms_owsproxy FROM wms WHERE wms_id = $1 ";
   		$v = array($wms_id);
   		$t = array("i");
   		$res = db_prep_query($sql,$v,$t);
   		if($row = db_fetch_array($res)){
   			return $row["wms_owsproxy"];
   		}
   		else{
   			return false;
   		}
   	}
	
	function getWMSSpatialSecurity($wms_id){
   		$sql = "SELECT wms_spatialsec FROM wms WHERE wms_id = $1 ";
   		$v = array($wms_id);
   		$t = array("i");
   		$res = db_prep_query($sql,$v,$t);
   		if($row = db_fetch_array($res)){
   			return $row["wms_spatialsec"];
   		}
   		else{
   			return false;
   		}
   	}
    
   	function setWMSSpatialSecurity($wms_id, $status){
   		$sql = "UPDATE wms SET wms_spatialsec = $1 WHERE wms_id = $2 ";
   		$t = array("s","i");
   		$v = array($status,$wms_id);
   		$res = db_prep_query($sql,$v,$t);
   	}
    
   	function setWMSOWSstring($wms_id, $status){
   		$sql = "UPDATE wms SET wms_owsproxy = $1 WHERE wms_id = $2 ";
   		$t = array("s","i");
   		if($status == 1){
   			$time = md5(uniqid());
			$v = array($time,$wms_id);
   		}
   		else{
   			$v = array("",$wms_id);
   		}
   		$res = db_prep_query($sql,$v,$t);
   	}

	/*
	 * set the log tag of the wms
	 * 
	 * @param integer the wms-id 
	 * 
	 */

   	function setWmsLogTag($wms_id,$value){
   		$sql = "UPDATE wms set wms_proxylog=$2 WHERE  wms_id = $1 ";
   		$t = array("i","i");
		$v = array($wms_id,$value);
   		$res = db_prep_query($sql,$v,$t);
   	}	

	/*
	 * get the log tag of the wms
	 * 
	 * @param integer the wms-id 
	 * @return 1 for active log and 0 or null for deactivated log
	 */

   	function getWmsLogTag($wms_id){
   		$sql = "SELECT wms_proxylog from wms WHERE  wms_id = $1 ";
   		$t = array("i");
		$v = array($wms_id);
   		$res = db_prep_query($sql,$v,$t);
		if($row = db_fetch_array($res)){
   			return $row["wms_proxylog"];
   		}
   		else{
   			return false;
   		}
   	}	
	/*
	 * set the pricevolume of the wms
	 * 
	 * @param integer the wms-id 
	 * @param integer the price for one kilobyte of wms data
	 */

   	function setWmsPrice($price,$wms_id){
   		$sql = "UPDATE wms set wms_pricevolume=$1 WHERE  wms_id = $2 ";
   		$t = array("i","i");
		$v = array($price,$wms_id);
   		$res = db_prep_query($sql,$v,$t);
   	}	

	/*
	 * get the price for one kilobyte of wms data
	 * 
	 * @param integer the wms-id 
	 * @return integer for price in cents for one kb of wms data
	 */

   	function getWmsPrice($wms_id){
   		$sql = "SELECT wms_pricevolume from wms WHERE  wms_id = $1 ";
   		$t = array("i");
		$v = array($wms_id);
   		$res = db_prep_query($sql,$v,$t);
		if($row = db_fetch_array($res)){
   			return $row["wms_pricevolume"];
   		}
   		else{
   			return false;
   		}
   	}		
    
    /*
	 * set the pricevolume of the wms feature info
	 * 
	 * @param integer the wms-id 
	 * @param integer the price for one kilobyte of wms data
	 */

   	function setWmsfiPrice($price,$wms_id){
   		$sql = "UPDATE wms set wms_price_fi=$1 WHERE  wms_id = $2 ";
   		$t = array("i","i");
		$v = array($price,$wms_id);
   		$res = db_prep_query($sql,$v,$t);
   	}	

	/*
	 * get the price for one request of wms feature info
	 * 
	 * @param integer the wms-id 
	 * @return integer for price in cents for one kb of wms data
	 */

   	function getWmsfiPrice($wms_id){
   		$sql = "SELECT wms_price_fi from wms WHERE  wms_id = $1 ";
   		$t = array("i");
		$v = array($wms_id);
   		$res = db_prep_query($sql,$v,$t);
		if($row = db_fetch_array($res)){
   			return $row["wms_price_fi"];
   		}
   		else{
   			return false;
   		}
   	}		

	/*
	 * set the log tag of the wms
	 * 
	 * @param integer the wms-id 
	 * 
	 */

   	function setWmsfiLogTag($wms_id,$value){
   		$sql = "UPDATE wms set wms_proxy_log_fi=$2 WHERE  wms_id = $1 ";
   		$t = array("i","i");
		$v = array($wms_id,$value);
   		$res = db_prep_query($sql,$v,$t);
   	}	

	/*
	 * get the log tag of the wms feature info
	 * 
	 * @param integer the wms-id 
	 * @return 1 for active log and 0 or null for deactivated log
	 */

   	function getWmsfiLogTag($wms_id){
   		$sql = "SELECT wms_proxy_log_fi from wms WHERE  wms_id = $1 ";
   		$t = array("i");
		$v = array($wms_id);
   		$res = db_prep_query($sql,$v,$t);
		if($row = db_fetch_array($res)){
   			return $row["wms_proxy_log_fi"];
   		}
   		else{
   			return false;
   		}
   	}	

	/*
	 * unset the proxy definitions and logging/pricing for the owned wms proxy list
	 * 
	 * @param string the wms-list
	 * 
	 */

   	function unsetWmsProxy($wms_list){
   		$sql = "UPDATE wms set wms_owsproxy='', wms_pricevolume=0,wms_proxylog=0,wms_proxy_log_fi=0,wms_price_fi=0  WHERE  wms_id IN ($1)";
   		$t = array("s");
		$v = array($wms_list);
   		$res = db_prep_query($sql,$v,$t);
   	}	

	/*
	 * log wms getmap proxy urls to db
	 * 
	 * @param 
	 * @return
	 */

   	function logWmsProxyRequest($wms_id,$user_id,$getmap,$price){
   		$sql = "INSERT INTO mb_proxy_log (fkey_wms_id,fkey_mb_user_id, request, pixel, price, got_result)"
            ." VALUES ($1, $2, $3, $4, $5, $6)";
   		$t = array("i","i","s","i","r", "i");
		#extract height and width
		#use regexpr
		$pattern_height = '~HEIGHT=(\d+)&~i';
		$pattern_width = '~WIDTH=(\d+)&~i';
		preg_match($pattern_width, $getmap,$sub_width);
		preg_match($pattern_height, $getmap,$sub_height);
		$width=intval($sub_width[1]);
		$height=intval($sub_height[1]);
		$pixel=intval($width*$height);
		$pricePx=$pixel*$price/1000000;
		$v = array(intval($wms_id),intval($user_id),$getmap,$pixel,$pricePx, 0);
   	        #echo print_r($v,true)."<br>";
		#var_dump($v);
		#echo $sql."<br>";	
		#echo "test<br>";
		$res = db_prep_query($sql,$v,$t) or die(db_error());
		#echo "test<br>";
		if(!$res){
			include_once(dirname(__FILE__)."/class_mb_exception.php");
			$e = new mb_exception("class_log: Writing table mb_proxy_log failed.");
			return false;
		}
		return true;
		
		#if($row = db_fetch_array($res)){
   			#return $row["wms_proxylog"];
   		#}
   		#else{
   			#return false;
   		#}
   	}
    
    function logFullWmsProxyRequest($wms_id,$user_id,$getmap,$price,$got_result,$set0Pixels=false){
   		$sql = "INSERT INTO mb_proxy_log "
            ."(fkey_wms_id,fkey_mb_user_id, request, pixel, price, layer_featuretype_list, request_type, got_result) "
            ."VALUES ($1, $2, $3, $4, $5, $6, 'getMap', $7)";
   		$t = array("i","i","s","i","r","s","i");
		#extract height and width
		#use regexpr
		$pattern_height = '~HEIGHT=(\d+)&~i';
		$pattern_width = '~WIDTH=(\d+)&~i';
        $pattern_layers = '~LAYERS=([^&.])+~i';
		preg_match($pattern_width, $getmap,$sub_width);
		preg_match($pattern_height, $getmap,$sub_height);
        preg_match($pattern_layers, $getmap,$sub_layers);
        $layers = explode("=", $sub_layers[0]);
		$width=intval($sub_width[1]);
		$height=intval($sub_height[1]);
		$pixel= $got_result === -1 ? 0 : ($set0Pixels ? 0 : intval($width*$height));
		$pricePx=$pixel*$price/1000000;
		$v = array(intval($wms_id),intval($user_id),$getmap,$pixel,$pricePx,isset($layers[1]) ? urldecode($layers[1]) : '',$got_result);
   	        #echo print_r($v,true)."<br>";
		#var_dump($v);
		#echo $sql."<br>";	
		#echo "test<br>";
		$res = db_prep_query($sql,$v,$t) or die(db_error());
		#echo "test<br>";
		if(!$res){
			include_once(dirname(__FILE__)."/class_mb_exception.php");
			$e = new mb_exception("class_log: Writing table mb_proxy_log failed.");
			return false;
		}
        #$myid = pg_last_oid($res);
        
        #$res_id = db_prep_query("SELECT log_id from mb_proxy_log where oid=$1",
        #        array(intval(pg_last_oid($res))),array("i")) or die(db_error());
        $res_id= db_query("select currval('mb_proxy_log_log_id_seq') as log_id");
        if(pg_num_rows($res_id)){
            $row = db_fetch_array($res_id);
            $id = $row["log_id"];
            return intval($id);
        } else {
            return false;
        }
   	}	
    function updateWmsLog($got_result, $error_message, $error_mime_type, $log_id){
        $sql = "UPDATE mb_proxy_log SET got_result=$1,error_message=$2,error_mime_type=$3"
            . ($got_result === -1 ? ',pixel=0' : '') ." WHERE log_id=$4";
        $t = array("i","s","s","i");
        $v = array($got_result, $error_message, $error_mime_type, $log_id);
        $res = db_prep_query($sql,$v,$t) or die(db_error());
		#echo "test<br>";
		if(!$res){
			include_once(dirname(__FILE__)."/class_mb_exception.php");
			$e = new mb_exception("class_log: Updating table mb_proxy_log failed.");
			return false;
		}
        return true;
    }	
    function logWmsGFIProxyRequest($wms_id,$user_id,$getmap,$price){
   		$sql = "INSERT INTO mb_proxy_log "
            ."(fkey_wms_id,fkey_mb_user_id, request, price, layer_featuretype_list, request_type) "
            ."VALUES ($1, $2, $3, $4, $5, 'getFeatureInfo')";
   		$t = array("i","i","s","r","s");
		#extract height and width
		#use regexpr
        $pattern_layers = '~LAYERS=([^&.])+~i';
        preg_match($pattern_layers, $getmap,$sub_layers);
        $layers = explode("=", $sub_layers[0]);
		$v = array(intval($wms_id), intval($user_id), $getmap, $price, isset($layers[1]) ? urldecode($layers[1]) : '');
   	        #echo print_r($v,true)."<br>";
		#var_dump($v);
		#echo $sql."<br>";	
		#echo "test<br>";
		$res = db_prep_query($sql,$v,$t) or die(db_error());
		#echo "test<br>";
		if(!$res){
			include_once(dirname(__FILE__)."/class_mb_exception.php");
			$e = new mb_exception("class_log: Writing table mb_proxy_log failed.");
			return false;
		}
        #$myid = pg_last_oid($res);
        
        #$res_id = db_prep_query("SELECT log_id from mb_proxy_log where oid=$1",
        #        array(intval(pg_last_oid($res))),array("i")) or die(db_error());
        $res_id= db_query("select currval('mb_proxy_log_log_id_seq') as log_id");
        if(pg_num_rows($res_id)){
            $row = db_fetch_array($res_id);
            $id = $row["log_id"];
            return intval($id);
        } else {
            return false;
        }
   	}
    function updateWmsFiLog($error_message, $error_mime_type, $log_id){
        if($error_message != null) {
            $sql = "UPDATE mb_proxy_log SET price=0"
                .",error_message=$1,error_mime_type=$2  WHERE log_id=$3";
            $t = array("s","s","i");
            $v = array($error_message, $error_mime_type, $log_id);
        } else {
            $sql = "UPDATE mb_proxy_log SET error_message=$1,error_mime_type=$2  WHERE log_id=$3";
            $t = array("s","s","i");
            $v = array($error_message, $error_mime_type, $log_id);
        }
        
        $res = db_prep_query($sql,$v,$t) or die(db_error());
		#echo "test<br>";
		if(!$res){
			include_once(dirname(__FILE__)."/class_mb_exception.php");
			$e = new mb_exception("class_log: Updating table mb_proxy_log failed.");
			return false;
		}
        return true;
    }	
	/*
	 * get the owsproxy-string of the current wfs
	 * 
	 * @param integer the wfs-id of the current wfs
	 * @return mixed the owsproxy-string or false
	 */
	   	
   	function getWfsOwsproxyString($wfs_id){
   		$sql = "SELECT wfs_owsproxy FROM wfs WHERE wfs_id = $1 ";
   		$v = array($wfs_id);
   		$t = array("i");
   		$res = db_prep_query($sql,$v,$t);
   		if($row = db_fetch_array($res)){
   			return $row["wfs_owsproxy"];
   		}
   		else{
   			return false;
   		}
   	}
   	
	/*
	 * sets or removes the owsproxy string of the current wfs
	 * 
	 * @param integer the wfs-id
	 * @param boolean set (true) or remove (false) the owsproxy-string
	 * 
	 */
   	function setWfsOwsproxyString($wfs_id, $status){
   		$sql = "UPDATE wfs SET wfs_owsproxy = $1 WHERE wfs_id = $2 ";
   		$t = array("s","i");
   		if($status == true){
   			$time = md5(microtime(1));
			$v = array($time,$wfs_id);
   		}
   		else{
   			$n = new mb_notice("removed owsproxy for wfs:".$wfs_id);
   			$v = array("",$wfs_id);
   		}
   		
   		$res = db_prep_query($sql,$v,$t);
   		$newOwsString = $this->getWfsOwsproxyString($wfs_id);
   		$n = new mb_notice("Class administration - setOWSString for wfs (".$wfs_id.") to: ". $this->getWfsOwsproxyString($wfs_id));
   		return $newOwsString;
   	}

	/*
	 * get the authentication info out of wms table
	 * 
	 * @param integer the wms-id 
	 * @return array auth - 'username', 'password', 'auth_type' if not set, return false
	 */

   	function getAuthInfoOfWMS($wms_id){
   		$sql = "SELECT wms_username, wms_password, wms_auth_type from wms WHERE  wms_id = $1 ";
   		$t = array("i");
		$v = array($wms_id);
   		$res = db_prep_query($sql,$v,$t);
		if($row = db_fetch_array($res)){
   			$auth['username'] = $row["wms_username"];
			$auth['password'] = $row["wms_password"];
			$auth['auth_type'] = $row["wms_auth_type"];
			return $auth;
   		}
   		else{
   			return false;
   		}
   	}	
	/*
	 * get the wms_id info out of wms table when wms_owsproxy is given
	 * 
	 * @param integer the owsproxy string
	 * @return wms_id - if not set, return false
	 */

   	function getWmsIdFromOwsproxyString($owsproxy){
   		$sql = "SELECT wms_id from wms WHERE  wms_owsproxy = $1 ";
   		$t = array("s");
		$v = array($owsproxy);
   		$res = db_prep_query($sql,$v,$t);
		if($row = db_fetch_array($res)){
			return $row["wms_id"];
   		}
   		else{
   			return false;
   		}
   	}	


   	function checkURL($url){
		$pos_qm = strpos($url,"?");
		if($pos_qm > 0 && $pos_qm < (mb_strlen($url)-1) && mb_substr($url,(mb_strlen($url)-1)) != "&"){
			$url = $url."&";
			return $url;
		}
		else if($pos_qm === false){
			return $url."?";
		}
		else{
			return $url;
		}
	}
	
	function getModulPermission($userID,$guiID,$elementID){
		$g = $this->getGuisByPermission($userID,true);
		if(in_array($guiID,$g)){
			$sql = "SELECT * FROM gui_element WHERE fkey_gui_id = $1 AND e_id = $2 ";
			$v = array($guiID,$elementID);
			$t = array('s','s');
			$res = db_prep_query($sql,$v,$t);
			if($row = db_fetch_array($res)){
				return true;
			}
			else{
				return false;
			}
		}
		else{
			return false;
		}
	}
	
	// deprecated! use User->isLayerAccessible
	function getLayerPermission($wms_id, $layer_name, $user_id){
		//prohibit problems with layer names
		$layer_name = urldecode($layer_name);
		$layer_id = $this->getLayerIdByLayerName($wms_id,$layer_name);
		if (!is_int($layer_id)) {//TODO: do this also in User->isLayerAccessible
			$e = new mb_exception("No id for the requested layer with name ".$layer_name." found in database!");
			return false;
		}
		$array_guis = $this->getGuisByPermission($user_id,true);
		$v = array();
		$t = array();
		$sql = "SELECT * FROM gui_layer WHERE fkey_gui_id IN (";
		$c = 1;
		for($i=0; $i<count($array_guis); $i++){
			if($i>0){ $sql .= ",";}
			$sql .= "$".$c;
			$c++;
			array_push($v, $array_guis[$i]);
			array_push($t, 's');
		}
		$sql .= ") AND fkey_layer_id = $".$c." AND gui_layer_status = 1";
		array_push($v,$layer_id);
		array_push($t,'i');
		$res = db_prep_query($sql,$v,$t);
		if($row = db_fetch_array($res)){
			return true;
		}
		else{
			return false;
		}
	}
	
	
	function getInspireDownloadOptionsForLayers($layerIdArray) {
		$v = array();
		$t = array();
		$sql = "SELECT layer_id, f_get_download_options_for_layer(layer_id) as options from layer where layer_id in(";
		$c = 1;
		for($i=0; $i<count($layerIdArray); $i++){
			if($i>0){ $sql .= ",";}
			$sql .= "$".$c;
			$c++;
			array_push($v, $layerIdArray[$i]);
			array_push($t, 'i');
		}
		$sql .= ");";
		$res = db_prep_query($sql,$v,$t);
		if($res){
			while($row = db_fetch_array($res)){
				$downloadOptions[$row['layer_id']] = $row['options'];
			}
			return $downloadOptions;
		}
		else{
			return false;
		}	
	}

	// deprecated! use User->isWmsAccessible
	function getWmsPermission($wms_id, $user_id) {
		$array_guis = $this->getGuisByPermission($user_id,true);
		$v = array();
		$t = array();
		$sql = "SELECT * FROM gui_wms WHERE fkey_gui_id IN (";
		$c = 1;
		for($i=0; $i<count($array_guis); $i++){
			if($i>0){ $sql .= ",";}
			$sql .= "$".$c;
			$c++;
			array_push($v, $array_guis[$i]);
			array_push($t, 's');
		}
		$sql .= ") AND fkey_wms_id = $".$c;
		array_push($v,$wms_id);
		array_push($t,'i');
		$res = db_prep_query($sql,$v,$t);
		if($row = db_fetch_array($res)){
			return true;
		}
		else{
			return false;
		}
	}
	
	function getLayerIdByLayerName($wms_id, $layer_name){
		$sql = "SELECT layer_id FROM layer WHERE ";
		$sql .= "fkey_wms_id = $1 AND layer_name = $2";
		$v = array($wms_id,$layer_name);
		$t = array('i','s');
		$res = db_prep_query($sql,$v,$t);
		if($row = db_fetch_array($res)){
			if (is_numeric($row['layer_id'])) {
				return intval($row['layer_id']);
			}
		}
		$e = new mb_warning("Unknown Layer (WMS ID: " . $wms_id . ", layer name: " . $layer_name . ")");
		return false;
	}

	function getWmsIdByWmsGetmap($getmap) {
		$sql = "SELECT wms_id FROM wms WHERE ";
		$sql .= "wms_getmap LIKE $1 LIMIT 1";
		$v = array($getmap."%");
		$t = array('s');
		$res = db_prep_query($sql,$v,$t);
		if($row = db_fetch_array($res)){
			return $row['wms_id'];
		}
		else{
			return false;
		}
	}

	function is_utf8_string($string) {
	    if (is_array($string))
	    {
	        $enc = implode('', $string);
	        return @!((ord($enc[0]) != 239) && (ord($enc[1]) != 187) && (ord($enc[2]) != 191));
	    }
	    else
	    {
	        return (utf8_encode(utf8_decode($string)) == $string);
	    }  
    /*
    		return preg_match('%(?:
		[\xC2-\xDF][\x80-\xBF]        # non-overlong 2-byte
		|\xE0[\xA0-\xBF][\x80-\xBF]               # excluding overlongs
		|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}      # straight 3-byte
		|\xED[\x80-\x9F][\x80-\xBF]               # excluding surrogates
		|\xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
		|[\xF1-\xF3][\x80-\xBF]{3}                  # planes 4-15
		|\xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
		)+%xs', $string);
	*/
	}
	
	function is_utf8_xml($xml) {
		return preg_match('/<\?xml[^>]+encoding="utf-8"[^>]*\?>/is', $xml);
	}
	
	function is_utf8 ($data) {
		return ($this->is_utf8_xml($data) || $this->is_utf8_string($data));
	}
	
	public static function convertIncomingString ($str) {
		if (CHARSET == "ISO-8859-1") {
			$e = new mb_notice("Conversion to UTF-8: " . $str . " to " . utf8_encode($str));
			return utf8_encode($str);
		}
		return $str;
	}
	
	public static function convertOutgoingString ($str) {
		if (CHARSET == "ISO-8859-1") {
			$e = new mb_notice("Conversion to ISO-8859-1: " . $str . " to " . utf8_decode($str));
			return utf8_decode($str);
		}
		return $str;
	}
	
	function char_encode($data) {
		if (CHARSET == "UTF-8") {
			if (!$this->is_utf8($data)) {
				$e = new mb_notice("Conversion: ISO-8859-1 to UTF-8");
				return utf8_encode($data);
			}
		}
		else {
			if ($this->is_utf8($data)) {
				$e = new mb_notice("Conversion: UTF-8 to ISO-8859-1");
				return utf8_decode($data);
			}
		}
		$e = new mb_notice("No conversion: is " . CHARSET);
		return $data;
	}

	function char_decode($data) {
		if (CHARSET == "UTF-8") {
			if ($this->is_utf8($data)) {
				$e = new mb_notice("Conversion: UTF-8 to ISO-8859-1");
				return utf8_decode($data);
			}
		}
		$e = new mb_notice("no conversion: is " . CHARSET);
		return $data;
	}
	
	/**
	 * identifies the Featureservices where the current user is owner
	 * 
	 * @param integer 		userid the user-ID of the current user
	 * @return integer[] 	the IDs of the featureservices
	 */
	 function getWfsByOwner($userid){
	 	$sql = "SELECT wfs_id FROM wfs WHERE wfs_owner = $1";
		$v = array($userid);
		$t = array('i');
		$res = db_prep_query($sql,$v,$t);
		$r = array();
		while($row = db_fetch_array($res)){
			array_push($r,$row["wfs_id"]);
		}
		return $r;
	 }
	 /**
	 * identifies the layers where the current user is has permission to access
	 * 
	 * @param integer 		userid the user-ID of the current user
	 * @return integer[] 	the IDs of the layers
	 * @boolean             if the user has no rights to access any layer
	 */
	 function getLayersByPermission($user_id){
			$arrayGuis = $this->getGuisByPermission($user_id,true);
			$v = array();
			$t = array();
			$sql = "SELECT DISTINCT fkey_layer_id FROM gui_layer WHERE fkey_gui_id IN (";
			$c = 1;
			for($i=0; $i<count($arrayGuis); $i++){
				if($i>0){ $sql .= ",";}
				$sql .= "$".$c;
				$c++;
				array_push($v, $arrayGuis[$i]);
				array_push($t, 's');
			}
			$sql .= ") AND gui_layer_status = 1";
			//array_push($t,'i');
			$res = db_prep_query($sql,$v,$t);
			$arrayLayers = array();
			if($row = db_fetch_array($res)){
				while($row = db_fetch_array($res)){
					array_push($arrayLayers,$row["fkey_layer_id"]);
				}
				return $arrayLayers;
			}
		return false;
	 }
	 
	 
	 
	 
	 
	 
	 /** identifies the Conf-FeatureServices where the current user is owner
	 * 
	 * @deprecated
	 * @param integer 		userid the user-ID of the current user
	 * @return integer[] 	the IDs of the wfs_conf-table
	 */
	 function getWfsConfByPermission($userid){
		$e = new mb_notice("administration->getWfsConfByPermission is deprecated, use user->getWfsConfByPermission instead!"); 
		$user = new User($userid); 	
		return $user->getWfsConfByPermission();
	 }
	 
    /**
     * selects the gui_categories 
     *
     * @param none			
     * @return integer[]    the IDs of the gui_categories
     */
	function getGuiCategories(){
		$sql = "SELECT * FROM gui_category order by category_id;";
		$res = db_query($sql);
		$row = db_fetch_array($res);
		if ($row) {
			$r = array();	
			while($row = db_fetch_array($res)){
				array_push($r,$row["category_id"]);
			}
			return $r;
		}

		else {
			return false;
		}
	}
	
	
	function getGuisByOwnerByGuiCategory($userid,$guicategoryid){
		$gui_list= array();
		$gui_list = $this->getGuisByOwner($userid,false);
		$v = array();
	   	$t = array();
		
			$sql = "SELECT fkey_gui_id FROM gui_gui_category ";
			$sql .= "WHERE gui_gui_category.fkey_gui_category_id = $1 ";
			$sql .= "AND gui_gui_category.fkey_gui_id IN (";
					array_push($v, $guicategoryid);
					array_push($t, "i");
			
				for($i=0; $i<count($gui_list); $i++){
					if($i>0){ $sql .= ",";}
					$sql .= "$".strval($i+2);
					array_push($v, $gui_list[$i]);
					array_push($t, "i");
				}
			$sql .= ");";
			
			$e = new mb_notice("getGuisByOwnerByGuiCategories: ".$sql);	
			$e = new mb_notice("v - t: ".count($v)." -- ".count($t));	
			$res = db_prep_query($sql,$v,$t);
			$r = array();
			while($row = db_fetch_array($res)){
				array_push($r,$row["fkey_gui_id"]);
			}
			
			return $r;		
	}
	
	public static function saveFile ($fullFilename, $content) {
		if (file_exists($fullFilename)) {
			if (!is_writable($fullFilename)) {
				$e = new mb_exception(__FILE__ . 
					": saveAsFile(): File not writable: " . $fullFilename);
				return false;
			}
		}
		else {
			$parts = pathinfo($fullFilename);
			if (!is_writable($parts["dirname"])) {
				$e = new mb_exception(__FILE__ . 
					": saveAsFile(): Folder not writable: " . 
					$parts["dirname"]);
				return false;
			}
		}
		
		if($h = fopen($fullFilename,"w")){
			if(!fwrite($h, $content)){
				$e = new mb_exception(__FILE__ . 
					": saveAsFile(): failed to write file: " . $fullFilename);
				return false;
			}
			fclose($h);
		}
		$e = new mb_notice(__FILE__ . 
			": saveAsFile(): saving RSS at " . $fullFilename);
		return true;		
	} 
}
?>
