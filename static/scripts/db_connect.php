<?php
// ini_set('mysql.connect_timeout', 300);
// ini_set('mysql.connect_timeout',1)
// ini_set('default_socket_timeout', 300);
// ini_set("default_socket_timeout", 2);
// include_once("../../path.php"); //for uniform path on includes
$path = dirname(__FILE__)."/../../_env.php";
include($path);

// echo("<br> file exists? ". file_exists($path). "<br>");
// echo("<br> dir is ". $path. "<br>");
// echo("<br> user is ". $DB_USER. "<br>");
$user 		= $DB_USER;
$password 	= $DB_PASS;
$db 		= $DB_NAME;
$host 		= $DB_HOST;
$port 		= $DB_PORT;
$charset 	= "utf8";
global $pdo;

try {
	$dsn = "mysql:host=".$host.":".$port.";dbname=".$db.";charset=".$charset;
	$pdo = new PDO(
		$dsn,
		$user,
		$password
	);
	// $pdo->prepare("set session wait_timeout=10000,interactive_timeout=10000,net_read_timeout=10000");
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	// $pdo->setAttribute(PDO::ATTR_TIMEOUT, 10000);
	// $pdo->exec('SET NAMES "utf8"');
} catch (PDOException $e) {
	echo("Unable to connect to the DB server."); //$e."\n");
	exit();
}
// echo("DB Connection Succeeded.\n");

function userInDB($u){ //array takes either email + pw or just fbId
	// echo("checking to see if ". $email." with the password ". $pw ." is in the DB. <br>");
	global $pdo;

	if(isset($u["fbId"]) && is_string($u["fbId"]) ){ //FB users
		try {
			$sql = "SELECT id FROM users WHERE fbId = :fbId";
			$s = $pdo->prepare($sql);
			$s->bindValue(":fbId", $u["fbId"]);
			$s->execute();
		} catch (PDOException $e) {
			$error = "Error finding FB user in DB.". $e;
			include_once(dirname(__FILE__)."/../../views/partials/_error.php");
			exit();
		}
		$row = $s->fetch();
		if ($row[0] > 0){ 
			// if(isset($u["grabDBId"]) ){ //gotta delete this and just have the function return user id
				return $row[0];
			// } else {
			// 	return true; 
			// }
		}
	}
	if(isset($u["email"]) && is_string($u["email"]) && isset($u["pw"]) && is_string($u["pw"])){
		try {
			$sql = 'SELECT COUNT(*) FROM users WHERE email = :email AND password = :pw';
			$s = $pdo->prepare($sql);
			$s->bindValue(":email", $u["email"]);
			$s->bindValue(":pw", $u["pw"]);
			$s->execute();
		} catch (PDOException $e) {
			$error = "Error finding user in DB.". $e;
			include_once(dirname(__FILE__)."/views/partials/_error.php");
			exit();
		}
		$row = $s->fetch();
		if ($row[0] > 0){ return true; }
	}
	return false;
}

function createUser($u){
	global $pdo;
	if(isset($u)){ //is_array test wouldn't pass. Array-like?
		echo('<br>____creating a FB user with ID____'.$u["id"]."<br>");
		if(isset($u["id"]) && is_string($u["id"])){ //New FB user
			try {
				$sql = "SELECT COUNT(id) FROM users WHERE fbId=:fbId;";
				$stmt = $pdo->prepare($sql);
				$stmt->execute(array('fbId' => $u["id"])); 
				$count = $stmt->fetch();
				if ($count[0] == 0){ //fetch returns an array
					try {
						$sql = "INSERT INTO users (fbId, roleId) VALUES (:fbId, :roleId);";
						$stmt = $pdo->prepare($sql);
						$stmt->bindValue(':fbId', $u['id']);
						$stmt->bindValue(':roleId', 2);
						echo("<br/> Adding FB user to DB. <br/>");
						return $stmt->execute(); //returns true on success
					} catch (PDOException $e) {
						echo("Error adding FB user to Users table of DB.\n".$e);
					}
				} else {
					echo("<br /> FB user exists already. <br />");
				}
			} catch (PDOException $e) {
				echo("Error creating FB user in DB.\n");
				exit();
			}
		}
	}
}


function addGameToDB($gameId, &$pdo){
	if (isset($gameId) && is_numeric($gameId)){
		try {
			$sql = "SELECT COUNT(id) FROM games WHERE id=:gameId;";
			$stmt = $pdo->prepare($sql);
			$stmt->execute(['gameId' => $gameId]); 
			$count = $stmt->fetch();
			if ($count[0] == 0){ //fetch returns an array
				try {
					$sql = "INSERT INTO games (id) VALUES (:gameId);";
					$stmt = $pdo->prepare($sql);
					$stmt->execute(['gameId' => $gameId]);
					echo("Game $gameId added to DB. \n");
					return true;
				} catch (PDOException $e) {
					echo("Error adding Game $gameId to Game table of DB.\n".$e);
				}
			} else {
				echo("Game already created for $gameId\n");
			}
		} catch (PDOException $e) {
			echo("Error checking existence of game $gameId in Game table of DB.\n");
			exit();
		}
	}
	return false;
}

function addGifToDB($goal, &$pdo){
	if (isset($goal) && is_array($goal)){
		if(!gifExists($goal['id'], $pdo)){
			try {
				$sql = "INSERT INTO highlights (id, type, gameId, gif_uri, video_uri) VALUES (:id, :type, :gameId, :uri, :videoUri);";
				$stmt = $pdo->prepare($sql);
				$stmt->bindValue(':id', $goal['id']);
				$stmt->bindValue(':type', 'GOAL');
				$stmt->bindValue(':gameId', $goal['gameId']);
				$stmt->bindValue(':uri', $goal['uri']);
				$stmt->bindValue(':videoUri', $goal['videoUri']);
				echo("Adding Goal ".$goal['id']." to DB. \n");
				return $stmt->execute(); //true if successful
			} catch (PDOException $e) {
				echo("Error adding Goal ".$goal['id']." to Highlight table of DB.\n". $e);
			}
		} else {
			echo("Goal ".$goal['id']." already exists. \n");
		}
	}
	return false;
}

function addShortGifToDB($goal, &$pdo){
	if (isset($goal) && is_array($goal)){
		if(gifExists($goal['id'], $pdo)){
			try {
				$sql = "UPDATE highlights SET short_gif_uri = :shortGifUri WHERE id = :id";
				$stmt = $pdo->prepare($sql);
				$stmt->bindValue(':id', $goal['id']);
				$stmt->bindValue(':shortGifUri', $goal['uri']);
				// echo("Updating Goal ".$goal['id']." with shortGif in DB. \n");
				return $stmt->execute(); //true if successful
			} catch (PDOException $e) {
				echo("Error adding short GIF to goal ".$goal['id']." \n". $e);
			}
		} else {
			echo("Goal ".$goal['id']." doesn't exist as a DB row. \n");
		}
	}
	return false;
}

function deleteGifFromDB($goalId){
	global $pdo;
	if (isset($goal) && is_array($goal)){
		if(gifExists($goalId, $pdo)){
			try {
				$sql = "DELETE FROM highlights WHERE id = :goalId;";
				$stmt = $pdo->prepare($sql);
				$stmt->bindValue(':goalId', $goalId);
				echo("Deleting Goal ".$goalId." from DB. \n");
				return $stmt->execute(); //true if successful
			} catch (PDOException $e) {
				echo("Error removing Goal ".$goalId." from Highlight table of DB.\n". $e);
			}
		} else {
			echo("GIF ".$goalId." doesn't even exist, fella. \n");
		}
	}
	return false;
}

function seedGifsFromDay($day){
	if(isset($day) && is_string($day)){
		try {
			$cmd = "cd ".dirname(__FILE__)."/ && php seedgifs.php ".$day;
			echo($cmd);
			// escapeshellarg(exec($cmd));
			echo("GIF processing complete for ".$day.".");
		} catch (Exception $e) {
			echo("Error seeding GIFs for ".$day.":  ". $e->getMessage() . "\n");
		}
	}
}
// seedGifsFromDay("2016-10-25");

function gifExists($goalId, &$pdo){
	//Find out if we already converted the GIF
	if(isset($goalId)){
		try {
			$sql = "SELECT COUNT(id) FROM highlights WHERE id=:goalId;";
			$stmt = $pdo->prepare($sql);
			$stmt->execute(['goalId' => $goalId]); 
			$count = $stmt->fetch();
			if ($count[0] == 1){ //fetch returns an array
				return true;
			}
		} catch (PDOException $e) {
			echo("Error checking to see if ".$goalId." exists.\n");
			// echo($e->getMessage());
		}
	}
	return false;
}

function getGif($goalId, &$pdo, $isShortGif = false){
	//returns the uri if exists in DB
	// combined query, if we want to use later:
	// SELECT gif_uri,short_gif_uri FROM highlights WHERE id = XXXXXXX
	// echo("------ isShortGif is ".$isShortGif."--------");
	if(isset($goalId)){
		try {
			$field = (isset($isShortGif) && $isShortGif == 1)?"short_gif_uri":"gif_uri";
			$sql = "SELECT ".$field." FROM highlights WHERE id = :goalId;";
			$stmt = $pdo->prepare($sql);
			$stmt->execute(['goalId' => $goalId]); 
			$result = $stmt->fetch();
			// print_r($result);
			if ( isset($result) && isset($result[$field]) && strlen($result[$field]) > 0) { // && $result[$field] !== "NULL"){
				return $result[$field];
			}
		} catch (PDOException $e) {
			echo("Error retrieving ".$goal['id']." URI.\n");
			// echo($e->getMessage());
		}
	}
	return false;
}

function addFavorite($o){ //options: takes userId, goalId
	global $pdo;
	if ( isset($o["userId"]) && isset($o["goalId"]) ){
		try {
			$sql = "INSERT INTO favorites (userId, highlightId) VALUES (:userId, :goalId);";
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':userId', $o['userId']);
			$stmt->bindValue(':goalId', $o['goalId']);
			// echo("Adding favorite ".$o['goalId']." to userId ".$o["userId"]."'s favorites. \n");
			return $stmt->execute(); //true if successful
		} catch (PDOException $e) {
			echo("Error adding Goal ".$o['goalId']." to Favorites table of DB.\n". $e);
		}
	}
	return null;
}

function removeFavorite($o){ //options: takes userId, goalId
	global $pdo;
	if ( isset($o["userId"]) && isset($o["goalId"]) ){
		try {
			$sql = "DELETE FROM favorites WHERE userId = :userId AND highlightId = :goalId;";
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':userId', $o['userId']);
			$stmt->bindValue(':goalId', $o['goalId']);
			echo("Deleting ".$o["goalId"]." from User ".$o["userId"]."'s favorites \n");
			return $stmt->execute(); //true if successful
		} catch (PDOException $e) {
			echo("Error removing favorited goal ".$o["goalId"]." from ".$o["userId"]."'s favorites. \n". $e);
		}
	}
	return null;
}

function userFav($o){
	global $pdo;
	if ( isset($o["userId"]) && isset($o["goalId"]) ){
		try {
			$sql = "SELECT COUNT(*) FROM favorites WHERE userId = :userId AND highlightId = :goalId;";
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':userId', $o['userId']);
			$stmt->bindValue(':goalId', $o['goalId']);
			$stmt->execute(); //true if successful
			$count = $stmt->fetch();
			if ($count[0] > 0){ //fetch returns an array
				return true;
			}
		} catch (PDOException $e) {
			echo("Error fetching goal favorited info for ".$o["userId"]." from ".$o["userId"]."on goal ".$o["goalId"].". \n". $e);
		}
	}
	return null;
}

function getHighlightFavs($goalId){
	global $pdo;
	if(isset($goalId)){
		try {
			$sql = "SELECT COUNT(*) FROM favorites WHERE highlightId = :goalId;";
			$stmt = $pdo->prepare($sql);
			$stmt->bindValue(':goalId', $goalId);
			$stmt->execute();
			$count = $stmt->fetch();
			if (isset($count[0])){ //fetch returns an array
				return $count[0];
			}
		} catch (PDOException $e) {
			echo("Error getting favorites total for ".$goalId.". \n". $e);
		}
	}
}