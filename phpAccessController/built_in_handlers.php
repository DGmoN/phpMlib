<?php
require_once("phpMlib/phpAccessController/parser.class.php");

class CSSHandle extends AccessHandler{
	
	function handle($GET=null){
		global $__MODULE_REGISTRY;
		$root_dir = $GET['MATCHES'][0]['root'];
		$file = $GET['MATCHES'][0]['file'];
		$root_dir = $__MODULE_REGISTRY['phpAccessController']->get_dir($root_dir);
		$path = $root_dir.'css/'.$file;
		if(file_exists($path)){
			header('Content-Type: text/css');
			require($path);
		}else
			echo "No such asset: ".$path;
	}
}

class JSHandle extends AccessHandler{
	
	function handle($GET=null){
		global $__MODULE_REGISTRY;
		$root_dir = $GET['MATCHES'][0]['root'];
		$file = $GET['MATCHES'][0]['file'];
		$root_dir = $__MODULE_REGISTRY['phpAccessController']->get_dir($root_dir);
		$path = $root_dir.'js/'.$file;
		if(file_exists($path)){
			header('Content-Type: text/js');
			require($path);
		}else
			echo "No such asset: ".$path;
	}
}

class NotFoundHandle extends AccessHandler{
	
	function handle($GET=null){
		__APPEND_LOG(print_r($GET, true));
		echo "404:Theres nothing here ".$GET["REQUEST"]["URL"];
	}
}

class ImgHandle extends AccessHandler{
	
	function handle($GET=null){
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
			echo "No such img asset: ".$ASSETS_ROOT.$GET['REQUEST']['URL'];
	}
}

class ConfigHandle extends AccessHandler{
	
	public $TEMPLATE = null;
	public $CONTEXT = array();
	
	function __construct(){
		$this->TEMPLATE = "core>viewCFG.php";
		global $__MODULE_CONFIG;
		$this->CONTEXT['cfg'] = file_get_contents($__MODULE_CONFIG);
	}
}

$register = function($mod){
	$mod->register_handler("\/css\/(?<root>.*)\/(?P<file>.*)$", new CSSHandle(), "CSS");
	$mod->register_handler("\/js\/(?<root>.*)\/(?P<file>.*)$", new JSHandle(), "JS");
	$mod->register_handler("\/(?P<file>img\/.*)$", new ImgHandle(), "IMG");
	$mod->register_handler("^(noPage)&", new NotFoundHandle(), "404");
	$mod->register_handler("(^\/core\/view$)", new ConfigHandle(), "CORE");
}


?>