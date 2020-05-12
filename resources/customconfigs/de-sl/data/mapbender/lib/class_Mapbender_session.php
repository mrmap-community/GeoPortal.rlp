<?php
/**
 * manages the session-handling and the access to session-variables 
 * @class
 */
 
 require_once(dirname(__FILE__)."/../http/classes/class_mb_exception.php");
 require_once(dirname(__FILE__)."/../http/classes/class_mb_warning.php");
 require_once(dirname(__FILE__)."/../http/classes/class_mb_notice.php");
 require_once(dirname(__FILE__)."/class_Singleton.php");

 class Mapbender_session extends Singleton{
 	
 	private $id ;
 	
 	/**
	 * @constructor
	 */
 	protected function __construct() {
 		$id = false;
 		new mb_notice("session.mapbender_session.instantiated ... ");
 	}

 	public static function singleton()
    {
        return parent::singleton(__CLASS__);
    }
 	
 	/**
	 * sets the value of the session-variable  
	 * @param String name the name of the session-variable
	 * @param Mixed value the new value of the session-variable
	 */
 	public function set($name, $value){
		$_SESSION[$name] = $value;
		
 		$this->start();
 		$_SESSION[$name] = $value;
 		new mb_notice("session.setSessionVariable.set: " . $name ." = ". $value);
 		session_write_close(); 
 	}
 	
	/**
	 * Unsets a session variable
	 * @param String name name of the session variable
	 */
	public function delete ($name) {
		unset($_SESSION[$name]);
		
 		$this->start();
 		unset($_SESSION[$name]);
 		new mb_notice("session.setSessionVariable.unset: " . $name);
 		session_write_close(); 
	}
	
 	/* pushs the array of the session-variable  
	 * @param String name the name of the session-variable
	 * @param Mixed value the new value of the session-variable
	 */
 	public function push($name, $value){
 		//not implemented yet
 		// todo
 	}
 	
 	/**
	 * returns the value of the session-variable  
	 * @param String name the name of the session-variable
	 */
 	public function get($name){
 		$this->start();
 		if(isset($_SESSION[$name])){
			$sessionValue = $_SESSION[$name];
 		}
 		else{
 			new mb_warning("the sessionVariable: ".$name." is read but it's not set!'");
			$sessionValue = false;
 		}
 		session_write_close();
		return $sessionValue;
 	}
 	
 	/**
	 * checks if a session variable is set
	 * @param String name the name of the session variable
	 */
 	public function exists ($name){
 		$this->start();
 		if(isset($_SESSION[$name])){
			$sessionExists = true;
 		}
 		else{
 			new mb_notice("exists(): The session variable: ".$name." is not set!'");
			$sessionExists = false;
 		}
 		session_write_close();
		return $sessionExists;
 	}
 	
 	/**
	 * sets a new session-id   
	 * @param String id the new id of the session
	 */
 	public function setId($id){
		$this->id = $id;
		
	}
	
	/**
	 * starts the new session   
	 */
	private function start(){
		if(session_id() == ''){
			new mb_notice("session.sessionStart.noActiveId");
		}
		if($this->id){
			session_id($this->id);
			new mb_notice("session.sessionStart.changeId to: ". session_id());
		}
		session_start();
		new mb_notice("session.sessionStart.id: ". session_id());
	}
	
	/*
	 * test if session file or object on server exists
	 */
	public function storageExists($id) {
		switch (ini_get('session.save_handler')) {
			case "memcache":
				$memcache_obj = new Memcache;
				if (defined("MEMCACHED_IP") && MEMCACHED_IP != "" && defined("MEMCACHED_PORT") && MEMCACHED_PORT != "") {
					$memcache_obj->connect(MEMCACHED_IP, MEMCACHED_PORT);
				} else {
					//use standard options
					$memcache_obj->connect('localhost', 11211);
				}
				new mb_notice("sessions stored via memcache");
				$session = $memcache_obj->get($id);
				$memcache_obj->close();
				if ($session !== false){
					return true;
				} else {
					return false;
				}
			break;
			case "memcached":
				$memcached_obj = new Memcached;
				if (defined("MEMCACHED_IP") && MEMCACHED_IP != "" && defined("MEMCACHED_PORT") && MEMCACHED_PORT != "") {
					$memcached_obj->addServer(MEMCACHED_IP, MEMCACHED_PORT);
				} else {
					//use standard options
					$memcached_obj->addServer('localhost', 11211);
				}
				new mb_notice("sessions stored via memcacheD");
				$prefix = ini_get('memcached.sess_prefix');
				if (empty($prefix) || $prefix =='') {
					$prefix = "memc.sess.key.";
				}
				$session = $memcached_obj->get($prefix.$id);
				//$memcached_obj->close();
				if ($session !== false){
					return true;
				} else {
					return false;
				}
			break;
			case "files":
				//check if file exists
				if(file_exists(ini_get('session.save_path')."/sess_".$id)) {
					return true;
				} else {
					return false;
				}
			break;
		}
	}	

	/*
	 * destroy session file or object on server
	 */
	public function storageDestroy($id) {
		switch (ini_get('session.save_handler')) {
			case "memcache":
				$memcache_obj = new Memcache;
				if (defined("MEMCACHED_IP") && MEMCACHED_IP != "" && defined("MEMCACHED_PORT") && MEMCACHED_PORT != "") {
					$memcache_obj->connect(MEMCACHED_IP, MEMCACHED_PORT);
				} else {
					//use standard options
					$memcache_obj->connect('localhost', 11211);
				}
				new mb_notice("sessions stored via memcache");
				$session = $memcache_obj->get($id);
				if ($session !== false){		
					$memcache_obj->delete($id);
					$memcache_obj->close();
					return true;
				} else {
					$memcache_obj->close();
					return false;
				}
			break;
			case "memcached":
				$memcached_obj = new Memcached;
				if (defined("MEMCACHED_IP") && MEMCACHED_IP != "" && defined("MEMCACHED_PORT") && MEMCACHED_PORT != "") {
					$memcached_obj->addServer(MEMCACHED_IP, MEMCACHED_PORT);
				} else {
					//use standard options
					$memcached_obj->addServer('localhost', 11211);
				}
				new mb_notice("sessions stored via memcacheD");
				$prefix = ini_get('memcached.sess_prefix');
				if (empty($prefix) || $prefix =='') {
					$prefix = "memc.sess.key.";
				}
				$session = $memcached_obj->get($prefix.$id);
				if ($session !== false){
					$memcached_obj->delete($prefix.$id);
					//$memcached_obj->close();
					return true;
				} else {
					//$memcached_obj->close();
					return false;
				}
			break;
			case "files":
				//check if file exists
				if(file_exists(ini_get('session.save_path')."/sess_".$id)) {
					return @unlink(ini_get('session.save_path')."/sess_".$id);
				} else {
					return false;
				}
			break;
		}
	}
	
	/*
	 * kills the current session
	 */
	 public function kill(){
	 	if (isset($_COOKIE[session_name()])) {
    			setcookie(session_name(), '', time()-42000, '/');
		}
		if(session_id()){
			session_destroy();
		}
	 } 	
 }
?>
