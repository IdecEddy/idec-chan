<?php 
ini_set('display_errors', 'On');
error_reporting(E_ALL);

require_once("../includes/init.php");
require_once("../includes/post.php");
require_once("../includes/thread.php");
require_once("../includes/board.php");
$user = init_user();

if(is_string($_GET['thread'])){
	$thread = Thread::find_by_id($_GET['thread']);
}else{
redirect_to('chan.php');
}

if(isset($_POST['submit'])){
	$post = $_POST['post'];
    $required_fields = array('post');
//    validate_presences($required_fields);
    if(!empty($errors)){
        $_SESSION["errors"] = $errors;
    	redirect_to("thread.php?thread=".$_GET['thread']);
	}else{
		//this is what will happen if you post with a img in the $_FILES.
		if(!empty($_FILES['file_upload']['name'])){
			if(photo_validation($_FILES["file_upload"])){
				$new_photo = new Photograph();
				$new_photo->caption = '';
				$new_photo->attach_file($_FILES['file_upload']);
				if($new_photo->save("chan_img", "photograph")){
					$new_post = new Post();
					$new_post->content = nl2br(htmlentities($_POST['post']));
					$new_post->replies = null;
					$new_post->img_id = $new_photo->id;
					$new_post->time = time();
					$new_post->thread_id = $thread->id;
					if($new_post->saves()){
						$board = Boards::find_by_id(1);
						$thread_array = explode(",", $board->thread_array);
						$thread_array = array_diff($thread_array, array($_GET['thread']));
						array_unshift($thread_array, $_GET['thread']);
						$board->thread_array = implode(',',$thread_array); 
						if($board->saves()){
                   			$post = $new_post->content;
                    		$replies_array = get_reply_string($post);
                        	if(!empty($replies_array)){
                            	global $database;
                            	$replies_array = array_unique($replies_array);
                            	foreach($replies_array as $reply){
                                	$sql = "UPDATE posts SET replies = CONCAT(replies,'" . $new_post->id . ",') where id =" . $reply;
                                	$database->query($sql);
                            	}
                        	}
                   			redirect_to("thread.php?thread=".$thread->id);
						}else{
							echo "somthing went wrong";
						}
					}	
				}
			}else{
				echo "the file you uploaded was not a suported img.";	
			}
		}
		//And this is what will happen if you post without a IMG in the $_FILES.
		if(empty($_FILES['file_upload']['name'])){
			$new_post = new Post();
			$new_post->content = nl2br(htmlentities($_POST['post']));
			$new_post->replies = null;
			$new_post->img_id = null;
			$new_post->time = time();
			$new_post->thread_id = $thread->id;
			if($new_post->saves()){
				$board = Boards::find_by_id(1);
				$thread_array = explode(",", $board->thread_array);
				$thread_array = array_diff($thread_array, array($_GET['thread']));
				array_unshift($thread_array, $_GET['thread']);
				$board->thread_array = implode(',', $thread_array);
				if($board->saves()){
					$post = $new_post->content;
                    $replies_array = get_reply_string($post);
						if(!empty($replies_array)){
							global $database; 
							$replies_array = array_unique($replies_array);
							foreach($replies_array as $reply){
								$sql = "UPDATE posts SET replies = CONCAT(replies,'" . $new_post->id . ",') where id =" . $reply;
								$database->query($sql);
							}
						}
					redirect_to("thread.php?thread=".$thread->id);  

				}else{
					echo "somthing went wrong";
				}
			}	
		}	
	}
}
	
?>
<script>
function img_click(){   
document.addEventListener('click', function(e){
    e = e || window.event;
    var target = e.target || e.srcElement;
    if(target.id == 'thumbnail'){
        var elem = document.getElementById(target.id);
        var style = window.getComputedStyle(target , null).getPropertyValue("width");
        if(style == '200px'){
				target.style.margin = '0px';
                target.style.height = 'auto';
                target.style.width = 'auto';
                return 0; 
        }
        if( style != '250px'){
				 target.style.margin = '10px';
                target.style.height = 'auto';
                target.style.width = '200px';
                return 0; 
        }
    }
	}, false); }
</script>


<html>
	<head>
		<script src="js/hover.js"></script>
		<link href="/stylesheets/navbar.css" media="all" rel="stylesheet" type="text/css" />
		<link href="stylesheets/chan_thread.css" media="all" rel="stylesheet" type="text/css" />
		<title> IdecGames </title>
		<link href='https://fonts.googleapis.com/css?family=Fjalla+One' rel='stylesheet' type='text/css'>
		<link href="stylesheets/chan_thread.css" media="all" rel="stylesheet" type="text/css" />
	</head>


<?php
$errors  = errors();
echo from_errors($errors);
?>


    <body>
		<div id="navbar">
            <?php include("../includes/templates/navbar.php"); ?>
        </div>

		<div>
		<p id="demo" />
		</div>

		<div id="form_id">
        <form action="thread.php?thread=<?php echo $thread->id; ?>" enctype="multipart/form-data" method="post">
            <input type="hidden" name="MAX_FILE_SIZE" value"" />
			<div id="post_field">
			<p id="new_post">-NEW POST-</p>
            <p><textarea id="post_form"  type="text" name="post" value="" COLS="72"></textarea></p>
			<p><input type="file" name="file_upload" />
			<input type="submit" name="submit" value="Upload" />
			</div>
        </form>
		</div>
        <br/>


<?php 
	
	echo "<div id='thread'>"; 
	if(is_object($thread))
	{
		$posts = Post::find_by_thread_id($thread->id);
		foreach($posts as $post)
		{
?>
		<div id="post">
<?php	$photo = Photograph::find_by_id($post->img_id);
			echo "<div id='post_header'>";
				echo "Anonymous ". strftime('%I:%M',$post->time); 

?>

				<a class="post_number" href="thread.php?thread=<?php echo $thread->id;?>&post=<?php echo $post->id;?>"><?php echo " No." .$post->id ;?></a>
<?php

			$replies = explode(",",$post->replies);
			foreach($replies as $reply){
				if(!empty($reply)){
					echo  "<a id='" .$reply ."' class='replies' href='thread.php?thread=" . $thread->id . " &post=" . $reply . "'>>>" . $reply . "</a> ";
				}
			}
			echo "<br />";
			if(is_object($photo))
			{
				$string_start_point = 1 + strpos($photo->type,'/');
?>
				<a class="img_link" href="chan_img/<?php echo $photo->filename?>"><?php echo trim($photo->filename) .".". substr($photo->type,$string_start_point);?></a><br />
			</div>
			<div id=flex_content>
				<div id="img">
					<img  id='thumbnail'onmousemove=" img_hover.get_ass(event); img_hover.move_img()" onmouseenter="img_hover.get_ass(event); img_hover.make_img()" onmouseleave="img_hover.delete_img()" src="chan_img/<?php echo $photo->filename;?>">
<?php

			}else{
			echo "</div>";
			echo "<div id='flex_contnet'>";
				echo "<div id='img'>";
			}
					$post = $post->content;
					echo "<p>" . make_backlinked_thread($post,$thread->id) . "</p><br />";
				echo "</div>";
			echo "</div>";
		echo "</div>";
		}
	}
echo '</div>';
?>

