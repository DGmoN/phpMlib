<?php
require_once("phpMlib/phpAccessController/parser.class.php");

class CSSHandle extends AccessHandler{
	
	function handle($GET=null){
		global $ASSETS_ROOT;
		$file = $ASSETS_ROOT.$GET['MATCHES'][0]['file'];
		if(file_exists($file)){
			
			global $ASSETS_ROOT;
			header('Content-Type: text/css');
			require($file);
		}else
			echo "No such asset: ".$ASSETS_ROOT.$GET['REQUEST']['URL'];
	}
}

class JSHandle extends AccessHandler{
	
	function handle($GET=null){
		global $ASSETS_ROOT;
		$file = $ASSETS_ROOT.$GET['MATCHES'][0]['file'];
		if(file_exists($file)){
			
			global $ASSETS_ROOT;
			header('Content-Type: text/js');
			require($file);
		}else
			echo "No such asset: ".$ASSETS_ROOT.$GET['REQUEST']['URL'];
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



$register = function($mod){
	$mod->register_handler("^\/(?P<file>css\/.*)$", new CSSHandle(), "CSS");
	$mod->register_handler("^\/(?P<file>js\/.*)$", new JSHandle(), "JS");
	$mod->register_handler("^\/(?P<file>img\/.*)$", new ImgHandle(), "IMG");
	$mod->register_handler("^(noPage)&", new NotFoundHandle(), "404");
}


?>