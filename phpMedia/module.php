<?php

require_once($MODULES_ROOT."/phpModules/class.module.php");

class phpMediaModule extends Module{
	
	public $TABLE_Name = null;
	public $DATABASE_Name = null;
	public $LOADED = false;
	public $ROOT = "";
	

	
	private $TABLE;
	
	
	function __construct($json){
		parent::__construct($json);
		$this->TABLE_Name = $json->TableName;
		$this->DATABASE_Name = $json->DatabaseName;
		$this->ROOT = $json->ROOTDir;
		
		$this->INTEGRATION["phpAccessController"] = function($module){
									require("handles.php");
									$module->register_handler(".*(\/media\/)(?P<mid>.*)", new MediaHandler, "MEDIA");
									$module->register_handler("^(\/mediasubmit\/)$", new MediaAdditionHandler, "MEDIAADD");
								};
		
	}
	
	
	function Load(){
		if($this->LOADED) return 1;
		parent::Load();
		
		global $__MODULE_REGISTRY;
		$__MODULE_REGISTRY['phpMySQL']->Load();
		$db = $__MODULE_REGISTRY['phpMySQL']->create("database", array("name"=>$this->DATABASE_Name));
		$this->TABLE = $__MODULE_REGISTRY['phpMySQL']->create("table", array("name"=>$this->TABLE_Name, "database"=>$db));
		$this->LOADED = true;
	}
	
	function handle_upload(){
		global $__MODULE_REGISTRY;
			
			$File = $_FILES['submission']['tmp_name'];
			$finfo = new finfo(FILEINFO_MIME_TYPE);
			$mime = $finfo->file($File);
			$name = md5(rand(0, 9999))."-".$_FILES['submission']['name'];
			$size = $_FILES['submission']['size'];
			$id = md5($name);
			
			$__MODULE_REGISTRY['phpMySQL']->Load();
			$table = $__MODULE_REGISTRY['phpMySQL']->create("quick", array("db"=>"honinworx", "table"=>"media"));
			$data = array(	"media_id"	=>	$id,
							"mime"		=>	$mime,
							"name"		=>	$name,
							"size"		=>	$size
						);
						
			$cols = $table->localize($data);

			if(move_uploaded_file($File, $this->ROOT.$name)){			
				$table->insert($cols);
				return $id;
			}else{
				return false;
			}
			
	}
	
	function fetch_data($mid){
		$cols = $this->TABLE->localize(array("media_id"=>""));
		$cols['media_id']->VALUE=$mid;
		
		$data = $this->TABLE->fetch($cols);
		$data = mysqli_fetch_assoc($data);
		__APPEND_LOG(print_r($data, true));
		header('Content-Type: '.$data['mime']);
		
		$file = $this->ROOT.$data['name'];
		if(file_exists($file)){
			readfile($file);
		}else{
			echo $data['media_id']." : not found";
		}
		
	}
}



?>