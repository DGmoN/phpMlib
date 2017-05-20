<?php
//	LOGGER

//	__CURRENT_MODULE	->	Designates module being used

//	__LOGGING_ENABLED	-> 	Enables/Disables logging
//	__LOG_FILE			->	Defines logfile output, default = "log.txt";
// 	__MODULE_CONFIG		->	Designates the config file
//	__MODULE_REGISTRY	->	An array of all the registered modules

$__MODULE_REGISTRY 	=	array();

if(!isset($__LOGGING_ENABLED))
	$__LOGGING_ENABLED = true;

if($__LOGGING_ENABLED){
	if(!isset($__LOG_FILE))
		$__LOG_FILE = "log.txt";

	$Log_asset = fopen($__LOG_FILE, "w");
}

function __APPEND_LOG($STR){
	global $Log_asset;
	if($Log_asset){
		$backtrace = debug_backtrace();
		$File = explode('\\', $backtrace[0]['file']);
		$File = $File[count($File)-1];
		fwrite($Log_asset, "[".date("j-n-Y G-i-s",time()).']['.$File."]\t".$STR."\n");
	}
}

__APPEND_LOG("Logging enabled");

if(!isset($__MODULE_CONFIG)){
	__APPEND_LOG("Module config file not set");
	die("Module config file not set");
}

if(file_exists($__MODULE_CONFIG)){
	__APPEND_LOG("Reading module config");
	require_once("class.module.php");
	$php_modules_config = json_decode(file_get_contents($__MODULE_CONFIG));
	foreach($php_modules_config as $st){
		__APPEND_LOG("Module loading: ".$st->MODULEName);
		
		$module_class_file = $MODULES_ROOT.'/'.$st->MODULESrc."module.php";
		if(file_exists($module_class_file)){
			require_once($module_class_file);
			$cName = $st->MODULEName."Module";
			$__MODULE_REGISTRY[$st->MODULEName] = new $cName($st);
			__APPEND_LOG("Module ok: ".$st->MODULEName);
		}else{
			__APPEND_LOG("Module loading FAILED: ".$module_class_file);
		}
			
	}
	__APPEND_LOG("Module config read");
}else{
	__APPEND_LOG("Module config file does not exist: ".$__MODULE_CONFIG);
	die("Module config file not found");
}



?>