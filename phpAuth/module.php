<?php
require_once($MODULES_ROOT."/phpModules/class.module.php");

class phpAuthModule extends Module{
	
	private $TName;
	private $DName;
	private $TABLE;
	private $META;
	
	
	private $COLS;
	
	function __construct($json){
		parent::__construct($json);
		$this->TName = $json->TableName;
		$this->DName = $json->DatabaseName;
	}
	
	function Load(){
		parent::Load();
		global $__MODULE_REGISTRY;
		$__MODULE_REGISTRY['phpMySQL']->Load();		
		
		$database = $__MODULE_REGISTRY['phpMySQL']->create("database", array('name'=>$this->DName));
		$this->TABLE = $__MODULE_REGISTRY['phpMySQL']->create("table", array('name'=>$this->TName, "database"=>$database));
		
		$this->COLS = $this->TABLE->get_columns();
	}
	
	
	// Authenticates the user data
	private function login($uid, $Pword){
		$cols = $this->TABLE->localize(array("ID"=>$uid));
		$cols = $this->TABLE->fetch($cols);
		$cols = mysqli_fetch_assoc($cols);
		if(empty($cols)) return false;
		$givenP =	$Pword;
		$StoredP = $cols['hash'];
		if(password_verify($givenP, $StoredP)){
			//$this->COLS['Checkin']->VALUE = date('Y-m-d G:i:s');
			//$sesh = $this->COLS['Sessionkey']->VALUE = md5($this->COLS['Checkin']->VALUE);
			//$this->COLS['last_addr']->VALUE = $_SERVER['REMOTE_ADDR'];
			//$this->TABLE->update(array($this->COLS['Checkin'], $this->COLS['Sessionkey'], $this->COLS['last_addr']), $data['ID']);
			
			return true;
		}
	}
	
	private function fetch_meta($UID){
		$cols = $this->META->get_columns();
		$cols['userid']->VALUE = $UID;
		
		$data = $this->META->fetch($cols);
		return mysqli_fetch_assoc($data);
	}
		
	private function verify($UID, $key){
		
		$cols = $this->TABLE->get_columns();
		$cols['ID']->VALUE = $UID;
		
		$data = $this->TABLE->fetch($cols);
		$data = $this->TABLE->normalize($data);
		
		if(!$data){ 
			
			return 0;
		}
		if($key != $data['Sessionkey']->VALUE){ 
			
			return 0;
		}
		if((string)$_SERVER['REMOTE_ADDR'] != $data['last_addr']->VALUE) return 0;
		
		return 1;
	}
		
	private function logout($UID, $key){
		$this->COLS['ID']->VALUE = $UID;
		$data = $this->TABLE->fetch(array($this->COLS['ID'], $this->COLS['Sessionkey']));
		$data = $this->TABLE->normalize($data);
		if($key != $data['Sessionkey']->VALUE) return 0;
		$data['Sessionkey']->VALUE = "none";
		$this->TABLE->update(array($data['Sessionkey']), $data['ID']);
		return 1;
	}
		
	private function register($PWord){
		$cols = $this->TABLE->localize(array("hash"=>password_hash($PWord, PASSWORD_BCRYPT), "ID"=>0));
		$this->TABLE->insert($cols);
		
		$ret = $this->TABLE->relay("SElECT MAX(ID) FROM ".$this->TName);
		$ret = mysqli_fetch_array($ret);
		$UID = $ret[0];
		return $UID;
	}
		
	function checkAvailable($uname){
		$cols = $this->TABLE->get_columns();
		$cols['Username']->VALUE = $uname;
		$ret = $this->TABLE->fetch($cols);
		$ret = mysqli_fetch_assoc($ret);
		return !$ret['Username'] == $uname;
	}
		
	function create($option, $args=array()){
		switch($option){
			case "login":
				return $this->login($args['uid'], $args['password']);
			case "verify":
				$veri = $this->verify($args['id'], $args['sessionkey']);
				if($veri) return true;
				else $this->logout($args['id'], $args['sessionkey']);
				return 0;
			case "logout":
				$this->logout($args['id'], $args['sessionkey']);
				return 1;
				
			case "register":
				return $this->register($args['password']);
		}
	}
}
?>