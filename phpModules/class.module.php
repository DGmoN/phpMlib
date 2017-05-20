<?php

/*
	This class acts as the super for other module handlers.
	When defined in the __MODULE_CONFIG file the resulting json 
	should provide the propper information for it to be implemented
	propperly
*/

class Module{
	
	public $MODULEName;
	public $MODULESrc;
	public $MODULEScripts = array();
	public $LOADED = false;
	
	protected $MEMORY = 0;
	
	function __construct($json){
		$this->MODULEName = $json->MODULEName;
		$this->MODULESrc = $json->MODULESrc;
		$this->MODULEScripts = $json->MODULEScripts;
	}
	
	

	
	// Loads the module dependencies
	public function Load(){
		$this->MEMORY = memory_get_usage();
		
		foreach($this->MODULEScripts as $script){
			__APPEND_LOG("Adding script: ".$script);
			try{
				$script_dir = $this->MODULESrc.$script;
				if(!file_exists($script_dir)) throw new Exception("The script does not exits");
				require_once($script_dir);
			}catch(Exception $e){
				__APPEND_LOG("Failed to load script: ".$e->getMessage());
			}
		}
		
		$this->MEMORY = memory_get_usage()-$this->MEMORY;
		__APPEND_LOG("Module loaded: ".$this->MODULEName);
		__APPEND_LOG("Base memory usage: ".($this->MEMORY/1024)."KB");
	}
	
	// Used so that other modules can create objects native to this module
	public function create($create, $args=array()){}
}


?>