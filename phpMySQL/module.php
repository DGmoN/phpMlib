<?php
require_once($MODULES_ROOT."/phpModules/class.module.php");

class phpMySQLModule extends Module{
	
	private $UName;
	private $Password;
	private $Host;
	private $SQLConnector;
	
	
	function __construct($json){
		parent::__construct($json);
		$this->UName = $json->DBUsername;
		$this->Password = $json->DBPassword;
		$this->Host = $json->DBHost;
	}
	
	function Load(){
		parent::Load();
		$this->SQLConnector = new SQL_CONNECTER($this->UName, $this->Password, $this->Host);
	}
	
	function __destruct(){
		if($this->SQLConnector){
			$this->SQLConnector->close();
			unset($this->SQLConnector);
		}
	}
	
	function create($option, $args=array()){
		switch($option){
			case 'quick':
				$db = $args['db'];
				$table = $args['table'];
				$db = new DATABASE($this->SQLConnector, $db);
				$table = new TABLE($db, $table);
				return $table;
				
			case "database":
				return new DATABASE($this->SQLConnector, $args['name']);
			case "table":
				if(!$args['database']->has_table($args['name']))
					return new TABLE($args['database'], $args['name']);
				return $args['database']->get_table($args['name']);
		}
	}
}
?>