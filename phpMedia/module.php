<?php

require_once($MODULES_ROOT."/phpModules/class.module.php");

class phpMediaModule extends Module{
	
	public $TABLE_Name = null;
	public $DATABASE_Name = null;
	public $LOADED = false;
	public $ROOT = "";
	

	
	private $TABLE;
	
	
	function __construct($json){
		parent::__construct($json);
		$this->TABLE_Name = $json->TableName;
		$this->DATABASE_Name = $json->DatabaseName;
		$this->ROOT = $json->ROOTDir;
		
		$this->INTEGRATION["phpAccessController"] = function($module){
									require("handles.php");
									$module->register_handler(".*(\/media\/)(?P<mid>.*)", new MediaHandler, "MEDIA");
								};
		
	}
	
	
	function Load(){
		if($this->LOADED) return 1;
		parent::Load();
		
		global $__MODULE_REGISTRY;
		$__MODULE_REGISTRY['phpMySQL']->Load();
		$db = $__MODULE_REGISTRY['phpMySQL']->create("database", array("name"=>$this->DATABASE_Name));
		$this->TABLE = $__MODULE_REGISTRY['phpMySQL']->create("table", array("name"=>$this->TABLE_Name, "database"=>$db));
		$this->LOADED = true;
	}
	
	function fetch_data($mid){
		$cols = $this->TABLE->get_columns();
		$cols['media_id']->VALUE	=	$mid;
		
		$data = $this->TABLE->fetch($cols);
		$data = mysqli_fetch_assoc($data);
		
		header('Content-Type: '.$data['mime']);
		
		$file = $this->ROOT.$data['name'];
		if(file_exists($file)){
			readfile($file);
		}else{
			echo $data['media_id']." : not found";
		}
		
	}
}



?>