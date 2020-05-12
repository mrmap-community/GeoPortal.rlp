<?php
class DB_MYSQL {
/*
	var $host     = "localhost";
	var $database = "rlp_lab";
	var $user     = "rlp";
	var $password = "rlp";
*/

	var $host     = "localhost";
	var $database = "rlpnew";
	var $user     = "rlpnew";
	var $password = "rlpnew";

	var $debug_on	= false;

	var $link_id	= 0;
	var $query_id	= 0;
	var $errno		= 0;
	var $error		= "";
	var $row;
	var $record		= array();

	var $MagicQuotes=true;

	function DB_MYSQL() {
		// Constructor
		$this->MagicQuotes=(get_magic_quotes_gpc()==1);
	}


	function insert_id() {
		// ID des zuletzt eingefügten Datensatzes (autoincrement-Feld)
		return mysql_insert_id($this->link_id);
	}

	function num_rows() {
		// Anzahl der gefundenen Datensätze
		return mysql_num_rows($this->query_id);
	}

	function f($name) {
		// Feld "$name" des aktuellen Datensatzes zurückliefern
		return $this->record[$name];
	}

	public function v($value) {
		// Wandelt das Value in einen sicheren String um
		$this->connect();

		if($this->MagicQuotes) {
			return mysql_real_escape_string(stripslashes($value), $this->link_id);
		} else {
			return mysql_real_escape_string($value, $this->link_id);
		}
	}

	function affected_rows() {
		// Anzahl der betroffenen Datensätze (Update / Delete)
		return @mysql_affected_rows($this->link_id);
	}

	function halt($msg) {
		// Fehlerausgabe (muss an Auftritt angepasst werden!)
		printf("<br><b>Database error:</b> %s<br>\n",$msg);
		printf("<b>MYSQL ERROR: </b>%s (%s)<br>\n",$this->errno, $this->error);
		die("Session died.");
	}

	function debug_msg($msg) {
		// Debug-infos ausgeben
		if($this->debug_on) {
			printf("<br><b>DEBUG:</b> %s<br>",$msg);
		}
	}

	function connect() {
		// Datenbankverbindung aufbauen
		$this->debug_msg("Function Connect");
		if( 0 == $this->link_id) {
			$this->link_id=mysql_connect($this->host, $this->user, $this->password);
			if(!$this->link_id) {
				$this->halt("Link ID == false, connect failed");
			} else {
				$this->debug_msg("Link established");
			}
			if(	!mysql_query(sprintf("use %s", $this->database), $this->link_id)) {
				$this->halt("cannot use database ".$this->database);
			} else {
				$this->debug_msg("Database selected");
			}
		} else {
			$this->debug_msg("Link already established");
		}
	}

	function query ($query_string) {
		// SQL ausführen
		$this->debug_msg("Function Query started");
		$this->debug_msg($query_string);

		$this->connect();

		$this->query_id=mysql_query($query_string, $this->link_id);
		$this->row=0;
		$this->errno=mysql_errno();
		$this->error=mysql_error();
		if(!$this->query_id) {
			$this->halt("Invalid SQL: ".$query_string);
		}
		return $this->query_id;
	}

	function next_record() {
		// nächster Datensatz
		$this->debug_msg("Function next");
		$this->record=mysql_fetch_array($this->query_id);
		$this->row += 1;
		$this->errno=mysql_errno();
		$this->error=mysql_error();

		$stat = is_array($this->record);
		if(!$stat) {
			mysql_free_result($this->query_id);
			$this->query_id=0;
		}
		return $stat;
	}

	function seek($pos) {
		// auf bestimmten Datensatz positionieren
		$status=mysql_data_seek($this->query_id, $pos);
		if($status)
			$this->row=$pos;
		return;
	}
}
?>
