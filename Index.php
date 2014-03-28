<?php

//värden för pdo
$host 		= "localhost";
$dbname 	= "guestbook";
$username 	= "guestbook";
$password 	= "123456";

//göra pdo
$dsn = "mysql:host=$host;dbname=$dbname";
$attr = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);


$pdo = new PDO($dsn, $username, $password, $attr);
if($pdo){
	
	if(!empty($_POST)){
		$_POST = null;
		$user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
		$post = filter_input(INPUT_POST, 'post', FILTER_SANITIZE_SPECIAL_CHARS, 
												FILTER_FLAG_STRIP_LOW);
		//echo $user_id . ", " . $post;
		$statement = $pdo->prepare("INSERT INTO posts (date, user_id, post) 
									VALUES (NOW(), :user_id, :post)");
		$statement->bindParam(":user_id", $user_id);
		$statement->bindParam(":post", $post);
		$statement->execute();
	}
	
	//har någonting postats? skriv till databas
	// visa post-formulär för att skriva inlägg
	// visa alla användare (ul)
	?>
	
	<form action="Index.php" method="POST">
		<p>
			<label for="user_id">User: </label>
			<select name="user_id">
				<?php
					// <option value=0>test</option>
					foreach($pdo->query("SELECT * FROM users ORDER BY name") as $row){
						echo "<option value=\"{$row['id']}\">{$row['name']}</option>";
					}
				?>
			</select>
		</p>
		<p>
			<label for="post">Post: </label>
			<input type="text" name="post" />
		</p>
		<input type="submit" value="Post" />
	</form>
	<hr />
	
	<?php
		//lista på användare (onordered list)
	echo "<ul>";
	echo "<li><a href=\"Index.php\">all users</a></li>";
	foreach($pdo->query("SELECT * FROM users ORDER BY name") as $row){
		echo "<li><a href=\"?user_id={$row['id']}\">{$row['name']}</a></li>";
	}
	echo "</ul>";
	echo "<hr />";
//annars visa alla inlägg.
	if(!empty($_GET)){
		//om user klickar på ett namn, visa dess inlägg.
		$_GET = null;
		$user_id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
		$statement = $pdo->prepare("SELECT posts.*, users.name FROM posts 
					JOIN users ON users.id=posts.user_id WHERE user_id=:user_id 
					ORDER BY date");
		$statement->bindParam(":user_id", $user_id);
		if($statement->execute()){
			while($row = $statement->fetch()){
				echo "<p>{$row['date']} by {$row['name']} <br /> {$row['post']}</p>";
			}
		}
	}
	else{
		//annars visa alla inlägg
		foreach($pdo->query("SELECT posts.*,users.name AS user_name FROM posts 
				JOIN users ON users.id=posts.user_id ORDER BY date") as $row){
			echo "<p>{$row['date']} by {$row['user_name']} <br /> {$row['post']}</p>";
		}
	}
}
else {
	echo "not connected";
}




?>