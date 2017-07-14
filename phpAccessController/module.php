<?php
require_once($MODULES_ROOT."/phpModules/class.module.php");
require_once("phpMlib/phpAccessController/parser.class.php");

if(!isset($MODULES_ROOT)) $MODULES_ROOT = "";

class phpAccessControllerModule extends Module{
		
	public $URL_DIR;
	private $HTACCESS = false;
	private $URL_PARSER = null;
	public $ASSET_DIRS;
	
	private $REGISTRY = array();
	private $REGISTRY_ROOT = null;
	
	function __construct($json){
		parent::__construct($json);
		$this->ASSET_DIRS = $json->ASSET_DIRS;
		$this->REGISTRY_ROOT = $json->REGISTRY;
		$this->HTACCESS = $json->HTACCESS == "true";
		
	}
	
	public function Load(){
		__APPEND_LOG("CONTROLL: ".$this->LOADED);
		if($this->LOADED) return 1;

		parent::Load();

		if($this->HTACCESS)	$this->prepHtaccess();
		
		$this->prepBuiltinFunctions();
						
		$files = scandir($this->REGISTRY_ROOT);

		foreach($files as $f){
			if($f != "." && $f != ".."){
				require_once($this->REGISTRY_ROOT.$f);
				$register($this);
			}
		}
		
		$this->LOADED = true;
	}
	
	
	// Registers a url and its handler to the registry
	// Registry looks like:
	//			[ID:	(REX, HANDLER)]
	
	function register_handler($regex, $handler, $ID=null){
		
		$pair = array("REX" => "/".$regex."/", "HANDLER"=>$handler);
		if($ID)
			$this->REGISTRY[$ID] = $pair;
		else
			array_push($this->REGISTRY, $pair);
		
		$ID = count($this->REGISTRY);
		
		__APPEND_LOG("RegisteredURL: ".$pair["REX"]);
	}
	
	function get_dir($handle){
		return $this->ASSET_DIRS->$handle;
	}
	
	// Appends the predefined page handlers for the module
	private function prepBuiltinFunctions(){
		require("built_in_handlers.php");
		$register($this);									
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
				
			case "remote_render":
				__APPEND_LOG("attempting to parse request: ".$args['url']);
				$request = $this->URL_PARSER->PARESE_URL($args['url']);
				$request['REQUEST']['TYPE'] = "REMOTE";
				$this->PAGE_HANDLERS[$request['TARGET']]($request);
				break;
				
			case "parse":
				__APPEND_LOG("attempting to parse url: ".$args['url']);
				$request = $this->parse($args['url']);	
				$request['HANDLER']->handle($request);
		}
	}
	
	
	// The new parser, not that new really
	private function parse($URL){
		__APPEND_LOG("Parsing URL: ".$URL);
		
		foreach($this->REGISTRY as $k=>$v){
			__APPEND_LOG($URL."->".$v['REX']);
			if(preg_match_all($v["REX"], $URL, $matches, PREG_SET_ORDER, 0)){
				__APPEND_LOG(print_r($matches, true));
				
				$e = $v;
				@$e["REQUEST"] = array("URL"=>$URL, "REFERER"=>$_SERVER['HTTP_REFERER'], "TYPE"=>"DIRECT");
				$e["MATCHES"] = $matches;
				return $e;
			}
		}
		
		@$referer = $_SERVER['HTTP_REFERER'];
		$cont = $this->REGISTRY["404"];
		$cont["REQUEST"] = array("URL"=>$URL, "REFERER"=>$referer, "TYPE"=>"DIRECT");
		
		return $cont;
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