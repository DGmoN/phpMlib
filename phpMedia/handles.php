<?php

require_once("phpMlib/phpAccessController/parser.class.php");

class MediaHandler extends AccessHandler{
	
	function __construct(){
		
	}
	
	function handle($request=null){
		global $__MODULE_REGISTRY;
		$__MODULE_REGISTRY['phpMedia']->Load();
		$__MODULE_REGISTRY['phpMedia']->fetch_data($request['MATCHES'][0]['mid']);
	}
}

class MediaAdditionHandler extends AccessHandler{
	
	function __construct(){
		$this->TEMPLATE = "core>media/submission.php";
		
	}
	
	function onrequest($request){
		if(isset($_FILES['submission'])){
			global $__MODULE_REGISTRY;
			$__MODULE_REGISTRY['phpMedia']->Load();
			$__MODULE_REGISTRY['phpMedia']->handle_upload();			
		}
	}
	
}





?>