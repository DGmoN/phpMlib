<?php 

class Template{
	
	private $TFILE, $TROOT;
	
	function __construct($root, $file){
		$this->TROOT = $root;
		$this->TFILE = $file;
	}
	
	// Renders the page contents
	function render($context=array()){
		// Creates the context variables
		$CONTEXT = $context;
		
		// The instance referer
		$SRC = $this;
		
		// renders the file and stores contnt
		ob_start();
		include($this->TROOT. $this->TFILE);
		$returned = ob_get_contents();
		ob_end_clean();  
		
		// If there is a parent then it applies this to the parent
		if(isset($parent)){
			
			$CONTEXT[$parent[1]] = $returned;
			
			ob_start();
			include($this->TROOT.$parent[0]);
			$returned = ob_get_contents();
			ob_end_clean();  
		}
		
		
		echo $returned;
	}
	
	function child($CONTEXT, $ID){
		if(isset($CONTEXT[$ID])){
			return $CONTEXT[$ID];
		}else{
			ob_start();
			include($this->TROOT.$ID);
			$returned = ob_get_contents();
			ob_end_clean();  		
			return $returned;
		}
	}
	
}


?>