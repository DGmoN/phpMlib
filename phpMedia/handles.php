<?php

require_once("phpMlib/phpAccessController/parser.class.php");

class MediaHandler extends AccessHandler{
	function handle($request=null){
		global $__MODULE_REGISTRY;
		$__MODULE_REGISTRY['phpMedia']->fetch_data($request['MATCHES'][0]['mid']);
		
		
	}
}


global $__MODULE_REGISTRY;
//$__MODULE_REGISTRY['phpAccessController']->Load();



?>