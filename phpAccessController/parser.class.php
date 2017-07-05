<?php

class AccessHandler{
	
	public $TEMPLATE = null;
	public $CONTEXT = array();
	
	function __construct(){
		
	}
	
	function onrequest($request){
		
	}
	
	function handle($request=null){
		$this->onrequest($request);
		$this->afterrequest($request);
	}
	
	function afterrequest($request){
		if($this->TEMPLATE!=null){
			global $__MODULE_REGISTRY;
			$__MODULE_REGISTRY['phpTemplater']->Load();
			$val = $__MODULE_REGISTRY['phpTemplater']->create("page", array("name"=>$this->TEMPLATE))->render($this->CONTEXT);
		}
	}
}



?>