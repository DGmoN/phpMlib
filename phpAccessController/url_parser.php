<?php

class URL_Parser{
	private $URLS, $BUILTINS = array("/^.*(?P<file>css\/.*)$/:css:css", "/^.*(?P<file>js\/.*)$/:js:js", "/^.*(?P<file>img\/.*)$/:img:img");
	function __construct($url_config){
		$URLS_FILE = fopen($url_config, "r");
		if($URLS_FILE){
			$URLS = array();
			while(($line = fgets($URLS_FILE)) != null){
				$QQ = explode(":",$line);
				$REGEX = $QQ[0];
				$TARGET = $QQ[1];
				$ID = trim($QQ[2]);
				$URLS[$ID] = array("REX" =>$REGEX, "TARGET"=>$TARGET);
			}
			
			foreach($this->BUILTINS as $line){
				$QQ = explode(":",$line);
				$REGEX = trim($QQ[0]);
				$TARGET = $QQ[1];
				$ID = trim($QQ[2]);
				$URLS[$ID] = array("REX" =>$REGEX, "TARGET"=>$TARGET);
			}
			
			$this->URLS = $URLS;
		}else{
			__APPEND_LOG($url_config.": Not found");
		}
		__APPEND_LOG("Created URL parser");
	}
	
	function get_url_for_label($label){
		
		return $this->URLS[$label];
	}
	
	function PARESE_URL($URL){
		if($URL == '/index' or $URL =="/" or $URL=='')
			$URL = "/index/";
		__APPEND_LOG("Parsing URL: ".$URL);
		
		foreach($this->URLS as $k=>$v){
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
		return array("TARGET"=>"404", "REQUEST"=> array("URL"=>$URL, "REFERER"=>$referer, "TYPE"=>"DIRECT"));
	}
	
	function __destruct(){
		$this->URLS = null;
		unset($this->URLS);
	}
}

?>