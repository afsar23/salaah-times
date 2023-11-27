<?php

namespace Afsar\wtk;
use Afsar\wtk;

use \PDO;
use \PDOException;

// used to get mysql database connection
class Database {

	// ***************  Consider standalone versus wp plugin
	//global $wpdb;
		
	//phpinfo();
	//die;

	// specify your own database credentials
	private $DB_host = \DB_HOST;
	private $DB_name = \DB_NAME;
	private $DB_user = \DB_USER;
	private $DB_pass = \DB_PASSWORD;
	
	private $DB_port = 3306;
	private $DB_port_ini;

	private $conn;

	// I think this is only needed 
	//$DB_port_ini 	= parse_ini_file(php_ini_loaded_file ( ))["mysqli.default_port"];		// add the port from the host information
	//$DB_port 		= ($Db_port_ini != "") ? $DB_port_ini : $DB_port;
	
	// get the database connection
	public function getConnection(){

		$this->conn = null;	

		$this->conn = new PDO('mysql:host=' . $this->DB_host . ';port=' . $this->DB_port . '; dbname=' . $this->DB_name 
								,$this->DB_user 
								,$this->DB_pass
								,array(
										PDO::ATTR_PERSISTENT => false
										//		PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
								  		)
								);  
		
		$this->conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		
		$this->conn->setAttribute( PDO::ATTR_EMULATE_PREPARES, false );
		// doesnt't work anyway,...allow mutliple statements to be run
		//$this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, 1);
		
		//$this->conn->setAttribute( PDO::ATTR_PERSISTENT, false );   -- needs to be in the initial connection options above!!!!
		$this->conn->setAttribute(PDO::ATTR_AUTOCOMMIT,1);

		$this->conn->exec("SET CHARACTER SET utf8");  //  return all sql requests as UTF-8  
	
		return $this->conn;
	}


	############################################################################################
	############################################################################################
	############################################################################################
	############################################################################################
	############################################################################################

	#####  REVEIW THESE AT SOME POINT #####


	##### Generic select, insert, update and delete 
	
	/*
    * Returns rows from the database based on the conditions
    * @param string name of the table
    * @param array select, where, order_by, limit and return_type conditions
    */
    public function sql_select($sql,$param=array()){

		$stmt = $this->db->prepare($sql);
		try {
			if(!empty($param) && is_array($param)){
				$stmt->execute($param);
			} else {
				$stmt->execute();
			}
			
		} catch(PDOException $e) {
			//$msg = $e->getMessage();
			switch($e->getCode()){
				//case 23000: $msg = "Name already exists";  break;
				default: $msg = $e->getMessage(); break;   
			}
			die($msg);
		}
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
	
	public function sql_keyvalue($sql,$param=array()){

		$stmt = $this->db->prepare($sql);
		if(!empty($param) && is_array($param)){
			$stmt->execute($param);
		} else {
			$stmt->execute();
		}
		return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
	}
	
	

	// fetch single record object
	public function get_row($sql,$param=array()) {

		$stmt = $this->db->prepare($sql);
		if(!empty($param) && is_array($param)){
			$stmt->execute($param);
		} else {
			$stmt->execute();
		}
		return $stmt->fetch(PDO::FETCH_OBJ);
    }	
	
    /*
     * Insert data into the database
     * @param string name of the table
     * @param array the data for inserting into the table
     */
    public function sql_insert($table,$data){  
	
		$fld = '';
		$prm = '';
		foreach ( $data as $key=>&$val ){
			if ($fld != '') $fld .= ', ';
			$fld .= $key;		
			if ($prm != '') $prm .= ', ';
			$prm .= ':'.$key;	
		}
		
		$sql = "INSERT INTO ".$table." (".$fld.") VALUES (".$prm.")";
		$stmt = $this->conn->prepare($sql);
		foreach ( $data as $key=>&$val ){
			$stmt->bindparam($key,$val);
		}	
	
		$msg = "";
		try {
			$stmt->execute();
		} catch(PDOException $e) {
			//$msg = $e->getMessage();
			switch($e->getCode()){
				case 23000: $msg = "Name already exists";  break;
				default: $msg = $e->getMessage(); break;   
			}
		}
		
		return $msg;
    }
    
    /*
     * Update data into the database
     * @param string name of the table
     * @param array the data for updating into the table
     * @param array where condition on updating data
     */
    public function sql_update($table,$data,$where){

		global $mtl;
		
		$sql = "UPDATE ".$table." SET ";
		$i = 0;
		foreach ( $data as $key=>&$val ){
			if ($i>0) $sql .= ', '; 
			$sql .= $key.'=:'.$key;
			$i = 1;
		}		
		$sql .= ' '.$where;

		//die($sql);

		$stmt = $this->conn->prepare($sql);
		foreach ( $data as $key=>&$val ){
			$stmt->bindparam(":".$key,$val);
		}	
	
		$msg = "";
		try {
			$stmt->execute();
		} catch(PDOException $e) {
			//$msg = $e->getMessage();
			switch($e->getCode()){
				case 23000: $msg = "Name already exists";  break;
				default: $msg = $e->getMessage(); break;         
			}
		}
		
		return $msg;
    }
    

	
	
	    /*
     * generic execute query
     * @param string name of the table
     * @param array where condition on deleting data
	 * returns array [recordsaffected, $errmsg]
     */
    public function sql_executenew($sql,$param=array()){

		$recsaffected = 0;
		$msg = "";
		
		$stmt = $this->db->prepare($sql);
		try {
			if(!empty($param) && is_array($param)){
				$stmt->execute($param);
			} else {
				$stmt->execute();
				$recsaffected = $stmt->rowCount();
			}
		} catch(PDOException $e) {
			//$msg = $e->getMessage();
			switch($e->getCode()){
				case 23000: $msg = "Name already exists";  break;
				default: $msg = $e->getMessage(); break;         
			}       
		}
		
		$rtn = ["rows"=>$recsaffected,"msg"=>$msg];
		return $rtn;	
		
	}
	
	
	// $fields is querystring or post data fields
	// $method = "GET" or "POST"
	public function ApiResults($url,$fields,$method) {
		
		$post = ($method=="POST") ? true : false;

		if (isset($fields) and is_array($fields)) {
			if (!$post) $url .= "?" . http_build_query($fields);
		}
		
		// set HTTP header
		$headers = array(
			'Content-Type: application/json'
		);

		// Open connection
		$ch = curl_init();

		// Set the url, number of POST vars, POST data
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, $post);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		if ($post) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
		
		// Execute post
		$result = curl_exec($ch);
		// Close connection
		curl_close($ch);

		$out = json_decode($result, true);		

		return $out;
		
	}
	

}



