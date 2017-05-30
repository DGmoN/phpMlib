<?php
require_once($MODULES_ROOT."/phpModules/class.module.php");

if(!isset($MODULES_ROOT)) $MODULES_ROOT = "";

class phpAccessControllerModule extends Module{
	
	public $URL_DIR;
	public $PAGE_HANDLERS = array();
	private $URL_HANDLER;
	private $HTACCESS = false;
	private $URL_PARSER = null;
	public $ASSETS_ROOT;
	
	
	function __construct($json){
		parent::__construct($json);
		$this->URL_DIR = $json->URL_DIR;
		$this->URL_HANDLER = $json->URL_HANDLER;
		global $ASSETS_ROOT;
		$ASSETS_ROOT = $json->ASSETS_ROOT;
		
		$this->HTACCESS = $json->HTACCESS == "true";
		
	}
	
	public function Load(){
		if($this->LOADED) return 1;
		if(!file_exists($this->URL_HANDLER)){
			__APPEND_LOG("Failed to load ".$this->MODULEName.". URL handler does not exist: ".$this->URL_HANDLER);
			return 0;
		}
		
		
		parent::Load();
		
		
		
		if($this->HTACCESS)	$this->prepHtaccess();
		
		$this->prepBuiltinFunctions();
				
		foreach($this->PAGE_HANDLERS as $k=>$P){
			__APPEND_LOG("Page handler registered: ".$k);
		}
		
		include($this->URL_HANDLER);
		
		
		
		$this->URL_PARSER = new URL_Parser($this->URL_DIR);
		$this->LOADED = true;
	}
	
	
	// Appends the predefined page handlers for the module
	private function prepBuiltinFunctions(){
		$this->PAGE_HANDLERS['404'] = function($GET = null){
					die("Theres nothing here my dude");
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
								echo "No such asset: ".$ASSETS_ROOT.$GET['REQUEST']['URL'];
						};
		$this->PAGE_HANDLERS['js'] = function($GET = null){
							global $ASSETS_ROOT;
							$file = $ASSETS_ROOT.$GET['MATCHES'][0]['file'];
							if(file_exists($file)){
								global $ASSETS_ROOT;
								include($file);
							}else
								return "No such asset: ".$ASSETS_ROOT.$GET['REQUEST']['URL'];
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
								return "No such asset: ".$ASSETS_ROOT.$GET['REQUEST']['URL'];
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
			case "remote_render":
				__APPEND_LOG("attempting to parse request: ".$args['url']);
				$request = $this->URL_PARSER->PARESE_URL($args['url']);
				$request['REQUEST']['TYPE'] = "REMOTE";
				$this->PAGE_HANDLERS[$request['TARGET']]($request);
		}
	}
	
	function reverse($label, $request){
		$this->PAGE_HANDLERS[$label]($request);
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