<?php
require_once("init.php");

//functions with !! comments might not be needed for the image board 
//and should be looked at for removal

// !! 
	function authorization_test($admin_level, $requirement)
	{
		if(!isset($_SESSION['user_id'])){
			redirect_to('../index.php');
		} 
		if($admin_level >= $requirement || $admin_level == 0){
			redirect_to('../index.php');
		}
	}
// !!
	function password_encrypt($password)
	{
        $hash_format = "$2y$10$";
        $salt_length = 22;
        $salt = gen_salt($salt_length);
        $format_and_salt = $hash_format . $salt;
        $hash = crypt($password, $format_and_salt);
        return $hash;
    }
// !!    
	function gen_salt($salt_length)
	{
        $unique_random_string = md5(uniqid(mt_rand(),true));

        $base64_string = base64_encode($unique_random_string);

        $modified_base64_string = str_replace('+', '.', $base64_string);

        $salt = substr($modified_base64_string, 0, $salt_length);

        return $salt;
    }
// !!
    function password_check($password, $existing_hash) 
	{
        $hash = crypt($password, $existing_hash);
        if ($hash === $existing_hash){
            return true; 
        }else{
            return false;
        }
    }
// !!
    function attempt_login($username, $password) 
	{
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

    function confirm_query($result_set)
	{
        if(!$result_set){
            die("database query failed.");
        }
    }
// !!
    function find_user_by_username($username)
	{
        global $db;
		$safe_username = $db->escape_value($username);

        $query = "SELECT * FROM users ";
        $query .= "WHERE username = '{$safe_username}' ";
        $query .= "LIMIT 1";
        $user_set = $db->query($query);
        if($user = mysqli_fetch_assoc($user_set)){
            return $user;
        }else{
            return null;
        }
    }
// !!
    function find_user_by_any_string($field, $value)
	{
        global $database;
        $safe_value = $database->escape_value($value);
        $query = "SELECT * FROM users ";
        $query .= "WHERE {$field} = '{$safe_value}' ";
        $query .= "LIMIT 1";
        $field_set = $database->query($query);
        if($field_values = mysqli_fetch_assoc($field_set)){
            return $field_values;
        }else{
            return false;
        }
    }

// !!
    $errors = array();

// !!     
    function fieldname_as_text($fieldname)
	{
        $fieldname = str_replace("_", " ", $fieldname);
        $fieldname = ucfirst($fieldname);
        return $fieldname; 
    }
    
    function has_presence($value)
	{
       return isset($value) && $value !== "";
    }
    
    function validate_presences($required_fields)
	{
        global $errors;
        foreach($required_fields as $field){
            $value = trim($_POST[$field]);
            if(!has_presence($value)){
                $errors[$field] = fieldname_as_text($field) . " can't be blank"; 
            }
        }
    }
    
    function has_max_length($value, $max)
	{
        return strlen($value) <= $max; 
    }
    
	function validate_max_length($fields_with_max_len)
	{
        global $errors;
        
        foreach($fields_with_max_len as $field => $max){
            $value = trim($_POST[$field]);
            if(!has_max_length($value,$max)){
                $errors[$field] = fieldname_as_text($field) . " must to be less than {$max} charecters.";
            }        
        }        
    }
// !! we might need it later.
    function email_validation($value)
	{
        global $errors;
        $email_1 = trim($_POST[$value]);
        $email_1 = strstr($email_1, '@');  
        if(!isset($email_1) || $email_1 == ""){
            $errors['email'] =  fieldname_as_text($value) . " field must be a valid email.";
        }else{
            $email_2 = strstr($email_1, ".");
            if(!isset($email_2) || $email_2 == ""){
                $errors['email'] = fieldname_as_text($value) . " field must be a valid email.";
            }
        }
    }

    function validate_if_duplicate($field_with_value)
	{
        global $errors;
        foreach($field_with_value as $field){
            $value = trim($_POST[$field]);
            $field_values = find_user_by_any_string($field, $value);
            if(strtolower($field_values[$field]) == strtolower($value)){
                    $errors[$field] = fieldname_as_text($field) . " is alredy in our database!";
            }
        }
    }

    function has_min_length($value, $max)
	{
        return strlen($value) >= $min;
    }
    
    function has_inclusion_in($value, $set)
	{
        return in_array($value, $set);
    }
    
    function from_errors($errors = array() )
	{
        $output = "";
        if(!empty($errors)){
            $output .= "<div class=\"error\">";
            $output .= "please fix the following errors:";
            $output .= "<ul>";
            foreach ($errors as $key => $error){
                $output .= "<li>{$error}</li>";
            }
            $output .= "</ul><br />";
            $output .= "</div>";
        }
        return $output; 
    }
// !! we dont have users so this might not be neded.
function init_user()
{
    if(isset($_SESSION['user_id'])){
    	$user = User::find_by_id($_SESSION["user_id"]);
    }else{
    	$user = "GUEST";
    }
	return $user;
}

function photo_validation($files)
{
	$valid_file_extension = array(".jpg", ".jpeg", ".gif", ".png");
	$file_extension = strrchr($files["name"], ".");
    if (in_array($file_extension, $valid_file_extension)) {
        if (@getimagesize($files["tmp_name"]) !== false) {
        	return true; 
		}else{
			return false;
		}
    }else{
		return false;
	}
}

function make_backlinked_thread($subject, $thread)
{
    $pattern = '/&gt;&gt;\d+/';
    if(preg_match_all($pattern,$subject,$elements))
    {
        foreach($elements[0] as $match)
        {
            $string_to_replace = "/".$match."/";

            preg_match("/\d+/",$match,$post_number);

            $replacement = "<a href='http://www.idecgames.com/thread.php?thread=" . $thread . "&post=". $post_number[0] . "'>" . $match . "</a>";

            $subject = preg_replace($string_to_replace, $replacement, $subject);
        }
        return $subject;
    }
    else
    {
        return $subject;
    }
}

function get_reply_string($post)
{
    $replies_array = array();
    $pattern = '/&gt;&gt;\d+/';
    if(preg_match_all($pattern, $post, $elements));
    {
        foreach($elements[0] as $match)
        {
            preg_match("/\d+/",$match,$post_number);
            array_push( $replies_array,$post_number[0]);
        }
        return $replies_array;
    }
        return null;
}
 
?>
