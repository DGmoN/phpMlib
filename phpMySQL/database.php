<?php 

$CURRENT_MODULE = "DATABASE REWORK";
__APPEND_LOG("Loading databases");

class SQL_CONNECTER{
	
	private $LINK;
	
	function __construct($uname, $pword, $host='localhost'){
		__APPEND_LOG("attempting loggin in with: \nUSERNAME:\t".$uname."\nPASSWORD:\t".$pword."\nHOST:\t".$host."\n");
		$this->LINK = mysqli_connect($host, $uname,$pword);
		__APPEND_LOG("Connection established");
	}
	
	// sends a query to the server and returns the responce
	function talk($QUERY){
		__APPEND_LOG("Sending query: ". $QUERY);
		$responce = mysqli_query($this->LINK, $QUERY);
		if(mysqli_error($this->LINK))
			__APPEND_LOG(mysqli_error($this->LINK));
		return $responce;
	}
	
	// returns an array of all databases
	function get_databases(){
		$query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA";
		$ret = array();
		while(($e = mysqli_fetch_array($ret))!= null)
			array_push($ret, $e);
		
		return $ret;
	}
	
	
	// selects a spesific database
	function select_database($name){
		mysqli_select_db($this->LINK, $name);
	}
	
	function close(){
		mysqli_close($this->LINK);
		__APPEND_LOG("SQL connection closed");
	}
	
}

class DATABASE{
	public $NAME;
	private $CONNECTOR, $TABELS = array();
	
	function __construct($connector, $name){
		$this->NAME = $name;
		$this->CONNECTOR = $connector;
		if(!$this->check_exist())
			$this->create();
		$this->populate_tables();
	}
	
	// checks if database exists
	private function check_exist(){
		$query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '".$this->NAME."'";
		$responce = $this->CONNECTOR->talk($query);
		return !empty(mysqli_fetch_array($responce));
	}
	
	// creates a database
	function create(){
		$query = "CREATE DATABASE IF NOT EXISTS ".$this->NAME;
		if($this->CONNECTOR->talk($query))
			__APPEND_LOG("Database created: ".$this->NAME);
		
	}
	
	function populate_tables(){
		$query = "SHOW TABLES";
		$res = $this->relay($query);
		while(@$q = mysqli_fetch_array($res)){
			__APPEND_LOG("Tabled registered: ".$q[0]);
			$this->TABELS[$q[0]] = new TABLE($this, $q[0]);
		}
	}
	
	// drops a database
	function drop(){
		$query = "DROP DATABASE ".$this->NAME;
		if($this->CONNECTOR->talk($query))
			__APPEND_LOG("Database has been dropped: ".$this->NAME);
	}
	
	// returns if the table is in the database
	function has_table($tname){
		return $this->get_table($tname) != null;
	}
	
	function get_table($name){
		return $this->TABELS[$name];
	}
	
	//gets the tables in the database
	function get_tables(){
		$query = "SHOW TABLES";
		$res = $this->relay($query);
		if($res)
			while( ($e = mysqli_fetch_array($res)))
				print_r($e);
		else return false;
	}
	
	// relays a query to the connector after selecting the database
	function relay($query){
		$this->CONNECTOR->select_database($this->NAME);
		return $this->CONNECTOR->talk($query);
	}
}

class TABLE{
	
	private $DATABASE, $NAME, $COLUMNS = array();
	
	function __construct($database, $name){
		$this->DATABASE = $database;
		$this->NAME = $name;
		$this->COLUMNS  = array("ID"=>new COLUMN("ID", "INT", null, null, null, "AUTO_INCREMENT"));
		if(!$this->exists()){
			$this->create();
		}
		$this->read_columns();
	}
	
	function get_columns(){
		
		
		return $this->COLUMNS;
	}
	
	// Tests if tabel exists
	function exists(){
		$query = "SHOW TABLES LIKE '".$this->NAME."'";
		$res = $this->DATABASE->relay($query);
		return $res->num_rows;
	}
	
	// creates database
	function create(){
		$query = "CREATE TABLE ".$this->NAME." (".$this->compile_columns().") ";
		$this->DATABASE->relay($query);
	}
	
	// drops the table
	function drop(){
		$query = "DROP TABLE ".$this->TABLE;
		$this->DATABASE->relay($query);
	}
	
	// empties the table
	function truncate(){
		$query = "TRUNCATE ".$this->TABLE;
		$this->DATABASE->relay($query);
	}
	

	// creates a column set from the assoc array
	function localize($data){
		$ret = array();
		foreach ($this->COLUMNS as $e){
			if(isset($data[$e->NAME])){
				$hold = $e->duplicate();
				$hold->VALUE = $data[$e->NAME];
				$ret[$e->NAME]=$hold;
			}
		}
		__APPEND_LOG(print_r($ret, true));
		//__APPEND_LOG(print_r($this->COLUMNS, true));
		return $ret;
	}
	
	// Generates columns for the table
	private function read_columns(){
		$query = "SELECT * FROM `INFORMATION_SCHEMA`.`COLUMNS` WHERE `TABLE_SCHEMA`='".$this->DATABASE->NAME."' AND `TABLE_NAME`='".$this->NAME."'";
		$rep = $this->DATABASE->relay($query);
		$this->COLUMNS = array();
		while(($e = mysqli_fetch_array($rep))){
			$name = $e['COLUMN_NAME'];
			$default = $e['COLUMN_DEFAULT'];
			$null = $e["IS_NULLABLE"] != "NO";
			$type = $e['DATA_TYPE'];
			$size = $e['CHARACTER_MAXIMUM_LENGTH'];
			$c = new COLUMN($name, $type, $size, $default, $null);
			__APPEND_LOG("created column: ".$c);
			$this->COLUMNS[$c->NAME] = $c;
		}
	}
	
	// compiles column string
	private function compile_columns(){
		$ret = implode(', ', $this->COLUMNS);
		$ret .= ", PRIMARY KEY (ID)";
		return $ret;
	}
	
	//adds a new column
	function add_column($clm){
		$query = "ALTER TABLE ".$this->NAME." ADD ".$clm;
		if($this->DATABASE->relay($query))
			array_push($this->COLUMNS, $clm);
	}
	
	// fetch the rows matching the provided columns
	function fetch($COLUMNS=null){
		
		$select = "*";
		$where = "";
		
		if($COLUMNS and $COLUMNS!="*"){
			$select = array();
			$where = array();
			foreach($COLUMNS as $c){
				
				if($c->VALUE){
					array_push($where, $c->NAME."='".$c->VALUE."'");
				}else{
					array_push($select, $c->NAME);
				}
			}
			if(empty($select)){
				$select = "*";
			}else
				$select = implode(", ", $select);
			if(!empty($where)){
				$where = "WHERE ".implode(", ", $where);
			}else{
				$where = "";
			}
		}
		
		$query = "SELECT ".$select." FROM ".$this->NAME." ".$where;
		return $this->DATABASE->relay($query);
	}
	
	// converts the fetched query into useable data
	function normalize($request){
		if(!$request) return 0;
		if($request->num_rows<=0){
			return 0;
		}
		
		$rows = array();
		
		if($request->num_rows==1){
			$hold = mysqli_fetch_assoc($request);
			$ret = array();
			foreach($hold as $k=>$c){
				$ret[$k] = $this->COLUMNS[$k]->duplicate();
				$ret[$k]->VALUE = $c;
			}
			
			
			return $ret;
		}
				
		while($hold = mysqli_fetch_assoc($request)){
			$ret = array();
			foreach($hold as $k=>$c){
				$ret[$k] = $this->COLUMNS[$k]->duplicate();
				$ret[$k]->VALUE = $c;
			}
			array_push($rows, $ret);
		}
		return $rows;
	}
	
	// inserts a new row into the database
	function insert($columns){
		$names = array(); 
		$values = array();
		
		foreach($columns as $c){
			array_push($names, $c->NAME);
			array_push($values, $c->VALUE);
		}
		
		$names = implode(", ", $names);
		$values = "'".implode("', '", $values)."'";
		
		$query = "INSERT INTO ".$this->NAME." (".$names.") VALUES (".$values.")";
		$this->DATABASE->relay($query);
	}
	
	// Updates cells depending on the provided referances
	function update($alterations, $refrence = null){
		
		$alter = array();
		foreach($alterations as $c){
			array_push($alter, $c->NAME."='".$c->VALUE."'");
		}
		
		$ref = "";
		if($refrence){
			$ref = " WHERE ".$refrence->NAME."='".$refrence->VALUE."'";
		}
		
		$query = "UPDATE ".$this->NAME." SET ".implode(', ',$alter)." ".$ref;
		return $this->DATABASE->relay($query);
	}
	
	
	function relay($sql){
		return $this->DATABASE->relay($sql);
	}
	
}

class COLUMN{
	
	private $TYPE, $SIZE, $DEFAULT, $NULL, $META;
	public $VALUE, $NAME;
	
	
	function __construct($name, $type, $size=null, $default=null, $NULL=false, $Meta=""){
		$this->NAME = $name;
		$this->TYPE = $type;
		$this->SIZE = $size;
		$this->META = $Meta;
		$this->DEFAULT = $default;
	}
	
	function __toString(){
		$ret = "".$this->NAME." ".$this->TYPE;
		if($this->SIZE) $ret .= "(".$this->SIZE.")";
		if($this->NULL) $ret .= " NULL";
		else			$ret .= " NOT NULL";
		if($this->DEFAULT) $ret .= " DEFAULT '".$this->DEFAULT."'";
		$ret .= " ".$this->META;
		return $ret;
	}
	
	function duplicate(){
		$ret = new COLUMN($this->NAME, $this->TYPE, $this->SIZE, $this->DEFAULT, $this->NULL);
		$ret->VALUE = $this->VALUE;
		return $ret;
	}
}



?>