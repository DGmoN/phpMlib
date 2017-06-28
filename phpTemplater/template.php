<?php 

class Template{
	
	private $TFILE, $TROOT;
	
	function __construct($Roots, $file){
		$this->TROOT = $Roots;
		$this->TFILE = $file;
	}
	
	// Renders the page contents
	function render($context=array()){
		// Creates the context variables
		$CONTEXT = $context;
		
		// The instance referer
		$SRC = $this;
		
		// renders the file and stores contnt
		__APPEND_LOG("Rendering: ".$this->TFILE);
		ob_start();
		include($this->parse_id($this->TFILE));
		$returned = ob_get_contents();
		ob_end_clean();  
		
		// If there is a parent then it applies this to the parent
		if(isset($parent)){
			
			$CONTEXT[$parent[1]] = $returned;
			ob_start();
			include($this->parse_id($parent[0]));
			$returned = ob_get_contents();
			ob_end_clean();  
		}
		
		
		echo $returned;
	}
	
	function render_abstract($context=array()){
		// Creates the context variables
		$CONTEXT = $context;
		
		// The instance referer
		$SRC = $this;
		
		// renders the file and stores contnt
		__APPEND_LOG("Rendering: ".$this->TFILE);
		ob_start();
		include($this->parse_id($this->TFILE));
		$returned = ob_get_contents();
		ob_end_clean();  
		
		return $returned;
	}
	
	// parse the ID to the correct target
	private function parse_id($id){
		if(strpos($id, ">")>0){
			$split = explode(">",$id);
			$alias = $split[0];
			return $this->TROOT->$alias.$split[1];
		}
	}
	
	
	function child($CONTEXT, $ID){
		if(isset($CONTEXT[$ID])){
			return $CONTEXT[$ID];
		}else{
			
			$file = $this->parse_id($ID);
			if(!file_exists($file)){
				__APPEND_LOG("Failed to load file: ". $ID);
				return;
			}
			ob_start();
			include($file);
			$returned = ob_get_contents();
			ob_end_clean();  		
			return $returned;
		}
	}
	
}


?>