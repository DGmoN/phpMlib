<?php
require_once($MODULES_ROOT."/phpModules/class.module.php");

if(!isset($MODULES_ROOT)) $MODULES_ROOT = "";

class phpTemplaterModule extends Module{
	
	private $TEMPLATE_ROOTS;
	private $ASSET_ROOT;
	private $TEMPLATE_ROOT;
	
	function __construct($json){
		$this->MODULEName = "phpTemplater";
		$this->MODULESrc = "phpTemplater/";
		$this->MODULEScripts = $json->MODULEScripts;
		$this->TEMPLATE_ROOT = $json->ROOT_DIR;
		$this->ASSET_ROOT = $json->ASSET_ROOT;
	}
	
	public function Load(){
		if($this->LOADED) return 1;
		parent::Load();
		$MEM = memory_get_usage();
		$this->LOADED = true;
	}
	
	// ARGS
	/*		page
				LABEL	-	The label of the page
	*/
	
	
	public function create($create, $args=array()){
		switch($create){
			case "page":
				return (new Template($this->TEMPLATE_ROOT, $args["name"]));
				break;
			case "abstract":
				
				break;
		}
	}
	
	function __destruct(){
		$hold = memory_get_usage();
		__APPEND_LOG($this->MODULEName." destructed, freed ".((memory_get_usage()-$hold)/1024)."KB");
	}
}


?>