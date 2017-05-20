<?php
require_once($MODULES_ROOT."/phpModules/class.module.php");

if(!isset($MODULES_ROOT)) $MODULES_ROOT = "";

class phpAccessControllerModule extends Module{
	
	public $URL_DIR;
	public $PAGE_HANDLERS = array();
	private $URL_HANDLERS;
	private $HTACCESS = false;
	private $URL_PARSER = null;
	
	
	function __construct($json){
		parent::__construct($json);
		$this->URL_DIR = $json->URL_DIR;
		$this->URL_HANDLERS = $json->URL_HANDLERS;
		
		$this->HTACCESS = $json->HTACCESS == "true";
		
	}
	
	public function Load(){
		if($this->LOADED) return 1;
		if(!file_exists($this->URL_HANDLERS)){
			__APPEND_LOG("Failed to load ".$this->MODULEName.". URL handler does not exist: ".$this->URL_HANDLERS);
			return 0;
		}
		
		
		parent::Load();
		
		
		
		if($this->HTACCESS)	$this->prepHtaccess();
		
		$this->prepBuiltinFunctions();
		
		include($this->URL_HANDLERS);
		
		foreach($this->PAGE_HANDLERS as $k=>$P){
			__APPEND_LOG("Page handler registered: ".$k);
		}
		
		$this->URL_PARSER = new URL_Parser($this->URL_DIR);
		$this->LOADED = true;
	}
	
	
	// Appends the predefined page handlers for the module
	private function prepBuiltinFunctions(){
		$this->PAGE_HANDLERS['404'] = function($GET = null){
					return "error 404";
				};
				
		$this->PAGE_HANDLERS['400'] = function($GET = null){
							__APPEND_LOG(print_r($GET, true));
							return "400:Its a dead link ".$GET;
						};
						
		$this->PAGE_HANDLERS['css'] = function($GET = null){
							global $ASSETS_ROOT;
							$file = $ASSETS_ROOT.$GET['MATCHES'][0]['file'];
							if(file_exists($file)){
								
								global $ASSETS_ROOT;
								header('Content-Type: text/css');
								require($file);
							}else
								return "No such asset: ".$ASSETS_ROOT.$GET['URL'];
						};
		$this->PAGE_HANDLERS['js'] = function($GET = null){
							global $ASSETS_ROOT;
							$file = $ASSETS_ROOT.$GET['MATCHES'][0]['file'];
							if(file_exists($file)){
								global $ASSETS_ROOT;
								include($file);
							}else
								return "No such asset: ".$ASSETS_ROOT.$GET['URL'];
						};
		$this->PAGE_HANDLERS['img'] = function($GET = null){
							global $ASSETS_ROOT;
							
							$file = $ASSETS_ROOT.$GET['MATCHES'][0]['file'];
							if(file_exists($file)){
								global $ASSETS_ROOT;
								
								$remoteImage = $file;
								$imginfo = getimagesize($remoteImage);
								header("Content-type: {$imginfo['mime']}");
								readfile($remoteImage);
															
								#include($ASSETS_ROOT."/img/".$GET);
							}else
								return "No such asset: ".$ASSETS_ROOT.$GET['URL'];
						};
	}
	
	private function prepHtaccess(){
		if(file_exists(".htaccess")){
			__APPEND_LOG(".httacess file found");
		}else{
			__APPEND_LOG("no .httacess file found... creating...");
			$F = fopen(".htaccess", "w");
			fwrite($F, "RewriteEngine on\n");

			fwrite($F, "RewriteRule ^(.*)$ index.php?dir=$1 [QSA]\n");

			fclose($F);
		}
	}
	
	/*
		
		parse	url
			exacutes the function connected in the registry
				
	*/
	
	public function create($create, $args=array()){
		switch($create){
			case "parse":
				__APPEND_LOG("attempting to parse url: ".$args['url']);
				$request = $this->URL_PARSER->PARESE_URL($args['url']);
				$this->PAGE_HANDLERS[$request['TARGET']]($request);
				return 1;
		}
	}
	
	function __destruct(){
		$store = memory_get_usage();
		$this->PAGE_HANDLERS = null;
		unset($this->PAGE_HANDLERS);
		$this->URL_PARSER = null;
		unset($this->URL_PARSER);
		__APPEND_LOG($this->MODULEName." unloaded, memory freed: ".((memory_get_usage()-$store)/1024)."KB");
	}
}


?>