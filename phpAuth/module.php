<?php
require_once($MODULES_ROOT."/phpModules/class.module.php");

class phpAuthModule extends Module{
	
	private $TName;
	private $DName;
	private $TABLE;
	
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
		
		$COLS = array(
			new COLUMN("Username", "VARCHAR", 32),
			new COLUMN("Password", "VARCHAR", 128),
			new COLUMN("Sessionkey", "VARCHAR", 128),
			new COLUMN("Checkin", "TIMESTAMP"),
			new COLUMN("last_addr", "VARCHAR", 32)
		);
		
		
		
		$database = $__MODULE_REGISTRY['phpMySQL']->create("database", array('name'=>$this->DName));
		$this->TABLE = $__MODULE_REGISTRY['phpMySQL']->create("table", array('name'=>$this->TName, "database"=>$database));
		foreach($COLS as $c){
			$this->TABLE->add_column($c);
		}
		$this->COLS = $this->TABLE->get_columns();
	}
	
	
	// Authenticates the user data
	private function login($Uname, $Pword){
		$this->COLS['Username']->VALUE = $Uname;
		$this->COLS['ID']->VALUE = null;
		$data = $this->TABLE->fetch(array($this->COLS['Username'], $this->COLS['Password'], $this->COLS['ID']));
		$data = $this->TABLE->normalize($data);
		if(empty($data)) return false;

		$givenP =	$Pword;
		$StoredP = $data['Password']->VALUE;
		if(password_verify($givenP, $StoredP)){
			$this->COLS['Checkin']->VALUE = date('Y-m-d G:i:s');
			$sesh = $this->COLS['Sessionkey']->VALUE = md5($this->COLS['Checkin']->VALUE);
			$this->COLS['last_addr']->VALUE = $_SERVER['REMOTE_ADDR'];
			$this->TABLE->update(array($this->COLS['Checkin'], $this->COLS['Sessionkey'], $this->COLS['last_addr']), $data['ID']);
			return array("sessionkey"=>$sesh, "id" =>$data['ID']->VALUE, "username"=>$data['Username']->VALUE) ;
		}
	}
		
	private function verify($UID, $key){
		$this->COLS['ID']->VALUE = $UID;
		$data = $this->TABLE->fetch(array($this->COLS['ID'], $this->COLS['Sessionkey'], $this->COLS['last_addr'], $this->COLS['Checkin']));
		$data = $this->TABLE->normalize($data);
		if(!$data) return 0;
		if($key != $data['Sessionkey']->VALUE) return 0;
		if((string)$_SERVER['REMOTE_ADDR'] != $data['last_addr']->VALUE) return 0;
		
		$lastTime = strtotime($data['Checkin']->VALUE);
		if((time()-$lastTime)>3600) return 0;
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
		
		
	function create($option, $args=array()){
		switch($option){
			case "login":
				return $this->login($args['username'], $args['password']);
			case "verify":
				$veri = $this->verify($args['id'], $args['sessionkey']);
				if($veri) return true;
				else $this->logout($args['id'], $args['sessionkey']);
				return 0;
				
			case "logout":
				$this->logout($args['id'], $args['sessionkey']);
				return 1;
				
		}
	}
}
?>