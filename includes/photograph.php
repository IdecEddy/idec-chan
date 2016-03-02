<?php
require_once(LIB_PATH.DS.'database.php');

class Photograph 
{
protected static $table_name = "photographs";
protected static $db_fields=array("id","filename", "type", "size");
public $id;
public $filename;
public $type;
public $size;
public $caption;

private $temp_path;
protected $upload_dir ="images";
public $errors = array();
protected $upload_errors = array (
UPLOAD_ERR_OK => "No errors.",
UPLOAD_ERR_INI_SIZE => "Larger than upload_max_filesize.",
UPLOAD_ERR_FORM_SIZE => "Larger than form MAX_FILE_SIZE.",
UPLOAD_ERR_PARTIAL => "Partial Upload.",
UPLOAD_ERR_NO_FILE => "No file.",
UPLOAD_ERR_NO_TMP_DIR => "No temporary directory.",
UPLOAD_ERR_CANT_WRITE => "Cant write to disk",
UPLOAD_ERR_EXTENSION => "File upload stopped by extension."
);
	
		

	
public function attach_file($file)
{
	if (!$file || empty($file) || !is_array($file)) {
		$this->errors[]	 = "no file uploaded.";
		return false;
	} elseif ($file['error'] != 0) { 
		$this->errors[] = $this->upload_errors[$file['error']];
		return false;
	} else {
		$this->temp_path = $file['tmp_name'];
		$this->filename = time();
		$this->type = $file['type'];
		$this->size = $file['size'];
		return true;
	}
}
	
	

static public function find_all()
{
	return self::find_by_sql("SELECT * FROM ". self::$table_name);
}

static public function find_by_id($id = 0)
{		
	$result_array = self::find_by_sql("SELECT * FROM ".self::$table_name. " WHERE id = {$id} LIMIT 1");
	return !empty($result_array) ? array_shift($result_array) : false;
}

static public function find_by_sql($sql = "")
{
	global $database;
	$result_set = $database->query($sql);
	$object_array = array();
	while($row = $database->fetch_array($result_set)){
		$object_array[] = self::instantiate($row);
	}
	return $object_array;
}

private static function instantiate($record)
{
	$object = new self;
	foreach($record as $attribute=>$value){
		if($object->has_attribute($attribute)){
			$object->$attribute = $value;
		}
	}
	return $object;
}

public function has_attribute($attribute)
{
	$object_vars = $this->attributes_get($this);
	return array_key_exists($attribute, $object_vars);
}

public function attributes_get()
{
	$att = array();
	foreach(self::$db_fields as $field){
		if(property_exists($this, $field)){
			$att[$field] = $this->$field;
		}
	}
	return $att;
}

protected function sanitized_attributes(){
	global $database;
	$clean_att = array();
	foreach($this->attributes_get() as $key => $value){
		$clean_att[$key] = $database->escape_value($value);
	}
	return $clean_att;
}

public function save($path) 
{
	if (isset($this->id)) {
		$this->update();
	} else {
		if (!empty($this->errors)) { return false;}
		
		if (strlen($this->caption) > 255) {
			$this->error[] = "the caption can only be 255 characters long."; 
			return false;
		}
		
		if(empty($this->filename) || empty($this->temp_path)) {
			$this->errors[] = "The file was not abailable.";
			return false;
		}
			
		$target_path = SITE_ROOT.DS.'public'.DS.$path.DS.$this->filename;
		
		if (file_exists($target_path)){
			$this->errors[] = "The file {$this->filename} alredy exists.";
			return false;
		}
		if(move_uploaded_file($this->temp_path, $target_path)){
			//success
			if ($this->create()) { 
				unset($this->temp_path);
				return true;
			}
		} else { 
			//failure
			$this->errors[] = "The file upload failed, possibly due to incorrect permissions on the upload dir";
			return false; 
		}
	}
}

/* 
replaiced woth new save for photo class.
public function saves()
{
	return isset($this->id) ? $this->update() : $this->create();

}
*/	

public function create()
{
	global $database;
	$att = $this->sanitized_attributes();
	$sql  = "INSERT INTO ".self::$table_name." (";
	$sql .= join(", ", array_keys($att));
	$sql .= ") VALUE ('";
	$sql .= join(" ', '", array_values($att));
	$sql .= "')";
	if($database->query($sql)){
		$this->id = $database->insert_id();
		return true;
	} else{
		return false;
	}
}

public function update()
{
	global $database;
	$att = $this->sanitized_attributes();
	$att_pairs = array();
	foreach($att as $key => $value){
		$att_pairs[] = "{$key}='{$value}'";
	}
	$sql  = "UPDATE ".self::$table_name." SET ";
	$sql .= join(", ", $att_pairs);
	$sql .= " WHERE id =". $database->escape_value($this->id);
	$database->query($sql);
	return($database->affected_rows() == 1) ? true : false;

}

public function deletes()
{
	global $database;
	$sql = "DELETE FROM ".self::$table_name." WHERE id =" . $database->escape_value($this->id);
	$sql .= " LIMIT 1";
	$databse->query($sql);
	return ($database->affected_rows() == 1) ? true : false;
}



}

?>
