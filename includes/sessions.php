<?php

function errors(){
    if(isset($_SESSION["errors"])){
        $errors = $_SESSION["errors"];
        $_SESSION["errors"] = null;
        return $errors;
    }
}
?>
