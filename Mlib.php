<?php

//	__LOGGING_ENABLED	-> 	Enables/Disables logging
//	__LOG_FILE			->	Defines logfile output, default = "log.txt";
// 	__MODULE_CONFIG		->	Designates the config file
//	__MODULE_REGISTRY	->	An array of all the registered modules

$MODULES_ROOT = $_SERVER["DOCUMENT_ROOT"]."/phpMlib";

$__LOGGING_ENABLED = true;
//$__LOG_FILE = $MODULES_ROOT."/MLib.LOG.txt";
$__MODULE_CONFIG = $MODULES_ROOT."/modules.cfg";

require_once("phpModules/Modules.php");

?>