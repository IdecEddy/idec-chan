<div id="navbar">
<ul id="navbarUL">
    <li><a href="index.php">Home</a></li>
    <li><a href="imageview.php">Gallery</a></li>
    <li><a href="chan.php">CH4N</a></li>
    <li><a href="#">Stats</a></li>
    <ul id="navbarUL" style="float:right; list-style-type:none;">
    <?php 
        if(!$session->is_logged_in()){
            echo "<li><a href=\"login.php\">Login</a></li>";
            echo "<li><a href=\"register.php\">Register</a><li>";
        }else{
            echo "<li><a href=\"logout.php\">logout</a></li>";
			if($user->admin_level > 0){
				echo "<li><a href=\"admin\">Admins</a></li>";

			}	
        }
        ?>
    </ul>
</ul>
</div>
