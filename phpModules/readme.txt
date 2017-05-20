###		phpModules		###
---------------------------

PHP modules is to be a modular library that allows you to only use the most necessary
Memory per thread without having to load the entire library.


---HOW TO---
------------

	>>>CONFIG VARS<<<
	-----------------
	
		__LOGGING_ENABLED	-> 	Enables/Disables logging if unset then TRUE
		__LOG_FILE			->	Defines logfile output, default = "log.txt";
		
		__MODULE_CONFIG		->	Designates the config file, This should house a json string 
								defining module specific config values
								
		__MODULE_REGISTRY	->	An associative array of all the registered modules
	
	>>>MODULE CLASS<<<
	------------------
	
		__construct($json)
			->	This function takes a json object built from the modules.cfg and is then
				used by the Module to prepare itself for loading.
				
		Load()
			-> 	Loads all the scripts and resources that the module will be using.
				Should not be called if you are not going to use the module.
				
				
		create($create, $args=array())
			->	This function is intended to act as a central for object creation from within
				the module, keeping the need to go scout for each function declaration to a 
				minimum
				
	>>>EXECUTION<<<
	---------------
		>Start from "require"
			if __LOGGING_ENABLED
				open __LOG_FILE
				
			if __MODULE_CONFIG not set or does not exist
				die
				
			read __MODULE_CONFIG
				find and create MODULE objects
				register them in registry
				
			to load the module you then reference them by:
				__MODULE_REGISTRY[MODULE_NAME]
				
				