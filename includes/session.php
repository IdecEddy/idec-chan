<?php
require_once("database.php");
require_once("functions.php");
require_once("user.php");
 
class Session{
	
	private $logged_in;
	public $user_id;
	public $admin_level;	 
	function __construct(){
		session_start();
		$this->check_login(); 	
	}
	
	public static function authenticate($username="",$password=""){
		global $database;
		$username = $database->escape_value($username);
		$password = $database->escape_value($password);
    	$user = User::find_by_username($username);
    	if($user){
        	if (password_check($password, $user->hashed_password)){
            	return $user;
        	}else{
            	return false;
        	}
    	}else{
        	return false;
    	}
	}

	public function is_logged_in(){
		return $this->logged_in;
	}	

	private function check_login(){
		if(isset($_SESSION['user_id'])){
			$this->user_id = $_SESSION['user_id'];
			$this->logged_in = true;
			$this->admin_level = $_SESSION['admin_level'];
		} else {
			unset($this->admin_level);
			unset($this->user_id);
			$this->logged_in = false;
		}
	}
	
	public function login($user){
		if($user){
			$this->admin_level = $_SESSION['admin_level'] = $user->admin_level;
			$this->user_id = $_SESSION['user_id'] = $user->id;
			$this->logged_in = true;	
		}
	}
	
	public function logout(){ 
		unset($_SESSION['admin_level']);
		unset($this->admin_level); 
		unset($_SESSION['user_id']);
		unset($this->user_id);
		$this->logged_in = false; 
	}
	
}

$session = new Session();

?>
