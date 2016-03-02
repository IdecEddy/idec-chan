<?php 


//this is made for linux systems you will need to make some changes if you use windows.
defined('DS') ? null : define('DS', DIRECTORY_SEPARATOR);
defined('SITE_ROOT') ? null : define('SITE_ROOT', DS.'var'.DS.'www'.DS.'html');
defined('LIB_PATH') ? null : define('LIB_PATH', SITE_ROOT.DS.'includes');
defined('PUB') ? null : define('PUB', SITE_ROOT.DS.'public');

//this is where we make connections to the database
require_once(LIB_PATH.DS."db_connection.php");
//some functions for doing basic tasks
require_once(LIB_PATH.DS."functions.php");
require_once(LIB_PATH.DS."redirect.php");
require_once(LIB_PATH.DS."sessions.php");
require_once(LIB_PATH.DS."session.php");
require_once(LIB_PATH.DS."database.php");
require_once(LIB_PATH.DS.'user.php');
require_once(LIB_PATH.DS.'photograph.php');
require_once(LIB_PATH.DS.'loger.php');
?>
