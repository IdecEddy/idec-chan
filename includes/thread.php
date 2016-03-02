<?php
require_once(LIB_PATH.DS.'database.php');

class thread
{
	protected static $table_name="thread";
	protected static $db_fields=array("id","time","user");
	public $id;
	public $time;
	public $user;
	
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

    public function saves()
    {
        return isset($this->id) ? $this->update() : $this->create();

    }

	public function create()
    {
        global $database;
        $att = $this->sanitized_attributes();
        $sql  = "INSERT INTO ".self::$table_name." (";
        $sql .= join(", ", array_keys($att));
        $sql .= ") VALUE ('";
        $sql .= join("', '", array_values($att));
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
