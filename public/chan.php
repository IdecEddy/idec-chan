<?Php
// To DO: objects need to be pushed up into the init.php file 
require_once("../includes/init.php");
require_once("../includes/post.php");
require_once("../includes/thread.php");
require_once("../includes/board.php");
$user = init_user();

if(isset($_POST['submit'])){
	$post = $_POST['post'];
	$required_fields = array('post');
//	validate_presences($required_fields);
	if(!empty($errors)){
		$_SESSION["errors"] = $errors;
		redirect_to('chan.php');
	}else{
		//this is what will happen if you post with a img in the $_FILES.
		//the first if() will work on makeing a new thread obj in our database.
		if(!empty($_FILES['file_upload']['name'])){
		$new_thread = new Thread; 
		$new_thread->time = time();
		$new_thread->user = $user;
		// if a thread is saved to the databse move on to uploading a uploading a photo to the database.
			if($new_thread->saves()){
				$new_photo = new Photograph();
				$new_photo->caption = '';
				$new_photo->attach_file($_FILES['file_upload']);
				// if we can save that photo into the database work on making a new post in the database.
				if($new_photo->save("chan_img")){
					$new_post = new Post();
					$new_post->content = nl2br(htmlentities($_POST['post']));
					$new_post->img_id = $new_photo->id;
					$new_post->time = time();
					$new_post->thread_id = $new_thread->id;
					// if we can save the post into the database now we need to update the Boards $thread_array (this handels thread bumping).
					if($new_post->saves()){
						$board = Boards::find_by_id(1);
						$thread_array = explode(",", $board->thread_array);
						array_unshift($thread_array, $new_thread->id);
						$board->thread_array = implode(',',$thread_array);
						// if we can save to the board database that means everything passed.
						// so we redirect the user back to the page so we can fix the bug where refreshing the page would make a new post.
						if($board->saves()){
							redirect_to("chan.php");
						} else{
							redirect_to("chan.php");	
						}
					}
				}
			}
		}

		//And this is what will happen if you post without a IMG in the $_FILES.
		//IF the file is empty make a new thread.
		if(empty($_FILES['file_upload']['name']) && isset($_GET['post'])){
			$new_thread = new Thread; 
			$new_thread->time = time();
			$new_thread->user = $user;
			//if that thread saves move on to the post and skip the img upload.
			if($new_thread->saves()){
				$new_post = new Post();
				$new_post->content = nl2br(htmlentities($_POST['post']));
				$new_post->img_id = null;
				$new_post->time = time();
				$new_post->thread_id = $new_thread->id;
				// if the post saves move on to updating the board index.
				if($new_post->saves()){
					$board = Boards::find_by_id(1);
					$thread_array = explode(",", $board->thread_array);
					array_unshift($thread_array, $new_thread->id);
					$board->thread_array = implode(',',$thread_array);
					// if the board saves move redirect back to the page to avoid double posting.
					if($board->saves()){ 
						redirect_to("chan.php");
					}else{
						redirect_to("chan.php");
					}
				}
			}	
		}
	}
}
?>
<?php
	$errors  = errors();
    echo from_errors($errors);
?>

<html>
	<head>
		<title> IdecGames </title>
		<link href="/stylesheets/chan_thread.css" media="all" rel="stylesheet" type="text/css" />
		<link href="/stylesheets/navbar.css" media="all" rel="stylesheet" type="text/css" />
		<link href='https://fonts.googleapis.com/css?family=Droid+Sans+Mono' rel='stylesheet' type='text/css'>
		<link href='https://fonts.googleapis.com/css?family=Fjalla+One' rel='stylesheet' type='text/css'>
		<script src="js/hover.js"></script>
	</head>
	<body>
        <div id="navbar">
            <?php include("../includes/templates/navbar.php"); ?>
        </div>

		<div>
	        <p id="demo" />
        </div>

		<div id="form_id">
			<form action="chan.php" enctype="multipart/form-data" method="post">
				<input type="hidden" name="MAX_FILE_SIZE" value"" />
				<div id="post_field">
					<p id="new_post">-NEW POST-</p>
					<p><textarea id="post_form"  type="text" name="post" value="" ><?php if(isset($_GET['post'])){
						echo ">>" . $_GET['post'] . " warning this will not post to the thread yet so dont use it here." ;
					}?></textarea></p>
					<p><input type="file" name="file_upload" />
					<input type="submit" name="submit" value="Upload" />
				</div>
			</form>
		</div>
		<br />
<?php

$board = Boards::find_by_id(1);
$thread_array = explode(',',$board->thread_array);

?>
	    <div id='board'>
<?php
foreach($thread_array as $current_thread)
{
?>
			<div id='thread'>
<?php
	$thread = Thread::find_by_id($current_thread);
	// I will make a thread Obj form the database and use its ID to find all of its posts.
	// I will set a start_point and a end_point based on the lenght of the array.
	//the end_point will be the abount of post in that thread.
	$posts = Post::find_by_thread_id($thread->id);
	$end_point = count($posts);
	// the start_point will vary on how many post we have but the min will be 0 and the max will be 4.  
	// the min is 0 because the op_post is post #1 and we print that seperatly from this loop.
	switch($end_point){
		case 0:
			$count = 0;
			break;
		case 1:
			$count = 0;
			break;
		case 2:
			$count = 1;
			break;
		case 3:
			$count = 2;
			break;
		case 4:
			$count = 3;
			break;
		default:
			$count = 4;
			break;
	}
	$start_point = $end_point - $count;
	//Im now going to open a div to start the OP post.
?>
				<div id='op_post'>
<?php
	$op_photo = Photograph::find_by_id($posts[0]->img_id);
    if(is_object($op_photo)){
		$string_start_point = 1 + strpos($op_photo->type,'/');
?>
					<div id='post_header'>
                    	<a href="chan_img/<?php echo $op_photo->filename?>"><?php echo trim($op_photo->filename) .".". substr($op_photo->type,$string_start_point);?></a><br />
                	</div>
					<div id='flex_content'>
						<div id="op_img">
							<img  id='thumbnail' src="chan_img/<?php echo $op_photo->filename;?>" onmousemove=" img_hover.get_ass(event); img_hover.move_img()" onmouseenter="img_hover.get_ass(event); img_hover.make_img()" onmouseleave="img_hover.delete_img()">
							<div id="reply_header">
								<b>Anonymous</b> <?php echo strftime('%I:%M',$posts[0]->time);?>
								<a href='chan.php?post=<?php echo $posts[0]->id;?>'>>>No.<?php echo $posts[0]->id;?></a>
								[<a href='thread.php?thread=<?php echo $thread->id;?>'>Reply</a>]<br />
							</div>
							<div id='op_post_contnet'>
								<?php echo make_backlinked_thread($posts[0]->content,$thread->id); ?>
								<br />
							</div>
						</div>
					</div>
<?php
	}else{
?>
					<div id='flex_content'>
                        <div id='flex_post'>
                            <div id="reply_header">
                                <b>Anonymous</b> <?php echo strftime('%I:%M',$posts[0]->time);?>
                                <a href='chan.php?post=<?php echo $posts[0]->id;?>'>>>No.<?php echo $posts[0]->id;?></a>
                                [<a href='thread.php?thread=<?php echo $thread->id;?>'>Reply</a>]<br />
                            </div>
                            <div id='op_post_contnet'>
                                <?php echo make_backlinked_thread($posts[0]->content,$thread->id); ?>
                                <br />
                            </div>
                        </div>
                    </div>
<?php
	}
?>
				</div>
<?php
	while($start_point < $end_point){
    	$photo = Photograph::find_by_id($posts[$start_point]->img_id); 
?>
			<div id='post'>
<?php
		if(is_object($photo)){
		$string_start_point = 1 + strpos($photo->type,'/');
?>
				<div id="header">
                	<a href="chan_img/<?php echo $photo->filename?>"><?php echo trim($photo->filename) .".". substr($photo->type,$string_start_point);?></a>
                	<br />
          		</div>
				<div id="flex_content">
    	            <div id="img">
        	            <img  id='thumbnail'onmousemove=" img_hover.get_ass(event); img_hover.move_img()" onmouseenter="img_hover.get_ass(event); img_hover.make_img()" onmouseleave="img_hover.delete_img()" src="chan_img/<?php echo $photo->filename;?>">
				    	<div id="reply_header">
          					<b>Anonymous</b> <?php echo strftime('%I:%M',$posts[$start_point]->time);?>
          					<a href='chan.php?post=<?php echo $posts[$start_point]->id;?>'>>>No.<?php echo $posts[$start_point]->id;?></a>
      					</div>
      					<div id='post_contnet'>
          					<?php echo make_backlinked_thread($posts[$start_point]->content, $thread->id); ?>
          					<br />
      					</div>
  					</div>
				</div>
<?php
		}else{
?>
			<div id="flex_content">
				<div id='flex_post'>
					<div id="reply_header">
						<b>Anonymous</b> <?php echo strftime('%I:%M',$posts[$start_point]->time);?>
						<a href='chan.php?post=<?php echo $posts[$start_point]->id;?>'>>>No.<?php echo $posts[$start_point]->id;?></a>
					</div>
					<div id='post_contnet_no_img'>
						
						<?php echo make_backlinked_thread($posts[$start_point]->content, $thread->id); ?> 
						<br />
					</div>
				</div>
			</div>
<?php
		}
		$start_point++
?>
			</div>
<?php			
	}	
?>
		</div>
<?php
}
?>
</div>
</body>
</html>

