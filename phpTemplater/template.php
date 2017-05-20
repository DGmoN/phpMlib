<?php 

/*		XML FORMAT
<xml>
	<page label='index'> 																					#the page will be used as it is
		<assets>
			<asset type='css/script...' name='filename' />
		</assets>
		<node type="asset-(path)/static-(tag)/part-(path)/text-(text)" id='element_id' class='element_class' meta_args="arg;arg2"/>
	</page>
	
	<page label='label' parent='parent_url'>																#the page nodes will be applied to its parent e.g. index
		<assets>
			<asset type='css/script...' name='filename' />
		</assets>
		
		<node type="asset-(path)/page-(url)/static-(tag)/text-(text)" id='element_id' class='element_class' meta_args="arg;arg2" overwite='element_path-pr/po/rp'/>
	</page>
</xml>
*/



class Pagemanager{
	
	private $XML_DATA;
	public $ASSETS_ROOT;
	
	function __construct($xml_data, $asset_root){
		$this->XML_DATA = $xml_data;
		$this->ASSETS_ROOT = $asset_root;
	}
	
	function get_page($label){
		$str = "page[@label='".$label."']";
		__APPEND_LOG("Looking up page config: ".$label);
		$page = $this->XML_DATA->xpath($str);
		
		if($page){
			__APPEND_LOG("Page found");
			return new Page($page[0], $this);
		}else{
			__APPEND_LOG("No such page found");
			return 0;
		}
	}
	
	function __destruct(){
		$this->XML_DATA = null;
		unset($this->XML_DATA);
	}
}

class Page{
	
	public $MANAGER;
	private $DOCTYPE = "HTML";
	private $NODES = array();
	
	function __construct($xml_data, $pman){
		if(isset($xml_data['doctype'])){
			$this->DOCTYPE = $xml_data['doctype'];
		}
		$this->MANAGER = $pman;
		__APPEND_LOG("Building nodes");
		foreach($xml_data->node as $node){
			array_push($this->NODES, new Node($node, $this));
		}
		
		if(@$xml_data['parent']){
			$parent = $this->MANAGER->get_page($xml_data['parent']);
			if(@$parent){
				__APPEND_LOG("Applying parent overwrite.");
				$this->DOCTYPE = $parent->DOCTYPE;
				$this->NODES = $parent->apply_child($this);
			}
		}
	}
	
	function render($context){
		__APPEND_LOG("Rendering page");
		echo "<!DOCTYPE ".$this->DOCTYPE.">";
		foreach($this->NODES as $n){
			$n->render($context);
		}

	}
		
	function apply_child($page){
		__APPEND_LOG("Applying overwrite to children");
		foreach($page->NODES as $n){
			$path = explode("/",$n->OVERWRITE[0]);
			$path= array_reverse($path);
			$this->NODES[0]->overwrite($path, $n->OVERWRITE[1], $n);
		}
		
		return $this->NODES;
	}
	
	function __destruct(){
		unset($this->MANAGER);
		unset($this->NODES);
	}
}

class Node{
	
	private $TYPE, $META_ARGS, $PAGE;
	public $ID, $CLASS, $OVERWRITE, $CHILDREN = array(), $CONTEXT_VALUE;
	
	function __construct($xml_data, $Page){
		
		$typedata = explode("-",$xml_data['type']);
		$this->PAGE = $Page;
		$this->TYPE = $typedata[0];
		$this->CONTEXT_VALUE = $typedata[1];
		$this->META_ARGS = $xml_data['meta_args'];
		__APPEND_LOG("Building node: ".$this->TYPE);
		$this->CLASS = $xml_data['class'];
		$this->ID = $xml_data['id'];
		
		if($xml_data['overwrite']){
			
			$this->OVERWRITE = explode('-',$xml_data['overwrite']);
			__APPEND_LOG("Overwrite found: ".print_r($this->OVERWRITE, true));
		}
		
		
		if($this->TYPE=="part"){
			$xml = simplexml_load_file("assets/".$this->CONTEXT_VALUE.".xml");
			foreach($xml->node as $n){
				array_push($this->CHILDREN, new Node($n, $this->PAGE));
			}
		}else{
			foreach($xml_data->node as $node){
				array_push($this->CHILDREN, new Node($node, $this->PAGE));
			}
		}
	}
	
	function overwrite($path, $prot, $obj){
		__APPEND_LOG("Overwrite search -- STACK: ".print_r($path, true));
		$target = array_pop($path);
		__APPEND_LOG("Overwrite search -- CURRENT: ".$target);

		foreach($this->CHILDREN as $k=>$c){
			
			switch(substr($target, 0, 1)){
				case "#":
					if($c->ID == substr($target, 1, strlen($target)-1)){
						__APPEND_LOG("Overwrite search -- CHILD: ".$c->ID);
						if(empty($path)){
							$c->apply_overwrite($k, $obj, $prot);
						}else{
							$c->overwrite($path, $prot, $obj);
						}
						return;
					}
					break;
				case ".":
					if($c->CLASS == substr($target, 1, strlen($target)-1)){
						__APPEND_LOG("Overwrite search -- CHILD: ".$c->CLASS);
						if(empty($path)){
							$c->apply_overwrite($k, $obj, $prot);
						}else{
							$c->overwrite($path, $prot, $obj);
						}
						return;
					}
					break;
				default:
					if($c->CONTEXT_VALUE == $target){
						__APPEND_LOG("Overwrite search -- CHILD: ".$c->CONTEXT_VALUE);
						if(empty($path)){
							$c->apply_overwrite($k, $obj, $prot);
						}else{
							$c->overwrite($path, $prot, $obj);
						}
						return;
					}
					break;
			}
		}
	}
	
	private function apply_overwrite($key, $object, $prot){
		switch($prot){
			case "pr":
				array_splice($this->CHILDREN, $key, 0, array($object));
				break;
			case "po":
				array_splice($this->CHILDREN, $key+1, 0, array($object));
				break;
				
			case "rp":
				$this->CHILDREN = $object->CHILDREN;
				$this->PAGE = $object->PAGE;
				$this->TYPE = $object->TYPE;
				$this->CONTEXT_VALUE = $object->CONTEXT_VALUE;
				$this->META_ARGS = $object->META_ARGS;
				$this->CLASS = $object->CLASS;
				$this->ID = $object->ID;
				break;
				
			case 'alt':
				$this->TYPE = $object->TYPE;
				$this->META_ARGS = $object->META_ARGS;
				
				break;
		}
	}
	
	function render($context = null){
		
		switch($this->TYPE){
			case "part":
				foreach($this->CHILDREN as $c){
					$c->render($context);
				}
				break;
			case "static":

				echo "<".$this->CONTEXT_VALUE." ";
				
				if($this->ID)
					echo "id='".$this->ID."'";
				
				if($this->CLASS)
					echo "class='".$this->CLASS."'";
				
				echo $this->META_ARGS.">";
				
				foreach($this->CHILDREN as $c){
					$c->render($context);
				}
								
				echo "</".$this->CONTEXT_VALUE.">";
				break;
								
			case "asset":
				include($this->PAGE->MANAGER->ASSETS_ROOT.$this->CONTEXT_VALUE);
				break;
			
			case "text":
				echo $this->CONTEXT_VALUE;
				break;
		}
	}
	
	function __destruct(){
		unset($this->CHILDREN);
	}
}

	
?>