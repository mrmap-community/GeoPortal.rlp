<?php
	/**
	 * Copyright (C) 2011 Wheregroup
	 *
	 * This program is free software; you can redistribute it and/or modify
	 * it under the terms of the GNU General Public License as published by
	 * the Free Software Foundation; either version 2, or (at your option)
	 * any later version.
	 *
	 * This program is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with this program; if not, write to the Free Software
	 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
	 *
	 * v0.2
	 */

	class MB_User  {
		private $userID;
		private $conn;
		private $error;
		private $userdata;
		
		/**
		 *
		 * @param string $username
		 */
		public function __construct() {
			$this->conn = pg_connect("host=".DBSERVER." port=".PORT." dbname=".DB." user=".OWNER." password=".PW);
			
			if(!$this->conn) {
				$this->error = "Error connecting to MB-DB";
			}
		}
		
		/***********************************************************************
		 * U S E R 
		 **********************************************************************/
		
		/**
		 * Test user exists or not and load userID.
		 * 
		 * @param string $username
		 * @return boolean
		 */
		public function loadUserByName($username) {
			if(!empty($this->error)) return false;
			$this->userID = null;
			
			$result = pg_query_params($this->conn, 'SELECT * FROM mb_user WHERE mb_user_name = $1', array($username));
			if(pg_num_rows($result) == 1) {
				$tmp = pg_fetch_assoc($result);
				if($tmp["mb_user_id"] == intval($tmp["mb_user_id"])) {
					$this->userID = intval($tmp["mb_user_id"]);
					$this->userdata = $tmp;
					return true;
				}
			}

			return false;
		}
		
		public function loadUserById($userid) {
			if(!empty($this->error)) return false;
			$this->userID = null;
			
			$result = pg_query_params($this->conn, 'SELECT * FROM mb_user WHERE mb_user_id = $1', array($userid));
			if(pg_num_rows($result) == 1) {
				$tmp = pg_fetch_assoc($result);
				$this->userdata = $tmp;
				if($userid == intval($userid)) {
					$this->userID = $userid;
					return true;
				}
			}
			return false;
		}
		
		
		public function userGet($column) {
			if(!empty($this->error) OR empty($this->userdata)) return false;
			if(isset($this->userdata[$column])) return $this->userdata[$column];
			else return false;
		}
		
		/**
		 * Remove user from database.
		 * 
		 * @return boolean
		 */
		public function removeUser() {
			if(!empty($this->error) OR empty($this->userID)) return false;
			
			$result = pg_query_params($this->conn, 'DELETE FROM mb_user WHERE mb_user_id = $1;',array($this->userID));
			if($result) {
				$this->userID = "";
				return true;
			}
			return false;
		}

		/**
		 * Update username and password from mapbender user.
		 * 
		 * @param string $name
		 * @param string $password
		 * @return boolean 
		 */
		public function updateUser($values) {
			if(!empty($this->error) AND !empty($this->userID)) return false;
			
			$set = "";
			$i = 1;
			foreach(array_keys($values) AS $column) {
				$set .= ', '.$column.' = $'.$i++;
			}
			
			$result = pg_query_params($this->conn, 'UPDATE mb_user SET '.substr($set,1).' WHERE mb_user_id = '.$this->userID.';',array_values($values));
			if($result) {
				return true;
			}
			return false;
		}
		
		/**
		 * Insert new mapbender user.
		 * 
		 * @param string $name
		 * @param string $password
		 * @return boolean
		 */
		public function insertUser($values) {
			if(!empty($this->error)) return false;
			
			$val = "";
			for($i=0;$i<count($values);$i++) {
				$val.= ',$'.($i+1);
			}
			
			$sql = 'INSERT INTO mb_user ("'.implode('","',array_keys($values)).'") VALUES ('.substr($val,1).');';
			$result = pg_query_params($this->conn, $sql,array_values($values));
			if($result) {
				return true;
			}
			return false;
		}

		/***********************************************************************
		 * G R O U P 
		 **********************************************************************/
		/**
		 * Get user groups.
		 * 
		 * @return array or false
		 */
		public function getGroups() {
			if(!empty($this->error) AND !empty($this->userID)) return false;
			
			$result = pg_query_params($this->conn, 'SELECT mb_group.mb_group_name FROM mb_user_mb_group LEFT JOIN mb_group ON mb_group_id = fkey_mb_group_id WHERE fkey_mb_user_id = $1;',array($this->userID));
			if($result) {
				$groups = array();
				while($tmp = pg_fetch_row($result)) {
					$groups[] = $tmp[0]; 
				}
				return $groups;
			}
			return false;
		}
		
		/**
		 * Add user to groups.
		 * 
		 * @param array $groups
		 * @return boolean 
		 */
		public function userSetGroups($groups) {
			if(!empty($this->error) AND !empty($this->userID) AND is_array($groups)) return false;
			
			foreach($groups AS $group) {
				$groupID = $this->groupExists($group);
				if($groupID !== false) {
					pg_query_params($this->conn, 'INSERT INTO mb_user_mb_group ("fkey_mb_user_id","fkey_mb_group_id") VALUES ($1,$2);',array($this->userID,$groupID));
				}
			}
		}
		
		/**
		 * Remove user from all groups.
		 * @return boolean 
		 */
		public function removeGroups() {
			if(!empty($this->error) AND !empty($this->userID)) return false;
			
			$result = pg_query_params($this->conn, 'DELETE FROM mb_user_mb_group WHERE fkey_mb_user_id = $1;',array($this->userID));
			if($result) {
				return true;
			}
			return false;
		}
		

		/**
		 * Mapbender group exists?
		 * 
		 * @param string $groupname
		 * @return ID or false
		 */
		public function groupExists($groupname) {
			if(!empty($this->error)) return false;
		
			$result = pg_query_params($this->conn, 'SELECT mb_group_id FROM mb_group WHERE mb_group_name = $1;', array($groupname));
			if(pg_num_rows($result) == 1) {
				$tmp = pg_fetch_row($result);
				return $tmp[0];
			}

			return false;
		}
		
		/**
		 * Add group to mapbender database.
		 * 
		 * @param string $name
		 * @return boolean
		 */
		public function insertGroup($name) {
			if(!empty($this->error)) return false;
		
			$result = pg_query_params($this->conn, 'INSERT INTO mb_group ("mb_group_name","mb_group_owner") VALUES ($1,$2);',array($name,1));
			if($result) {
				return true;
			}
			return false;
		}
		
		/***********************************************************************
		 * F U N C TI O N S
		 **********************************************************************/		
		public function generateUserPw($len = 8) {
			$pw = "";
			
			$pool = "qwertzupasdfghkyxcvbnm23456789WERTZUPLKJHGFDSAYXCVBNM";
			srand ((double)microtime()*1000000);
			for($i=0;$i<$len;$i++) {
				$pw .= substr($pool,(rand()%(strlen($pool))), 1);
			}
			return $pw;
		}

		public function error() {
			if(empty($this->error)) return false;
			return true;
		}
			
		public function errorMessage($return = false) {
			if($return) return $this->error;
			echo $this->error;
		}
	}
?>
