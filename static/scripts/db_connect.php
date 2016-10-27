<?php
// include_once("../../path.php"); //for uniform path on includes
// if (file_exists("../../_env.php")){  //Dev
// 	include_once("../../_env.php");
// } else if (file_exists($_SERVER["DOCUMENT_ROOT"]."/_env.php")){ //Prod
// 	include_once($_SERVER["DOCUMENT_ROOT"]."/_env.php");
// }
// set_include_path(get_include_path() . PATH_SEPARATOR . $_SERVER["DOCUMENT_ROOT"]);
// echo("<br> Include path". get_include_path(). "<br>");
// echo("<br> Current working directory: ". getcwd(). "<br>");
// $bl = dirname(__FILE__)."/../../_env.php";
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
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	// $pdo->exec('SET NAMES "utf8"');
} catch (PDOException $e) {
	echo("Unable to connect to the DB server."); //$e."\n");
	exit();
}
// echo("DB Connection Succeeded.\n");


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
				$sql = "INSERT INTO highlights (id, type, gameId, gif_uri) VALUES (:id, :type, :gameId, :uri);";
				$stmt = $pdo->prepare($sql);
				$stmt->bindValue(':id', $goal['id']);
				$stmt->bindValue(':type', 'GOAL');
				$stmt->bindValue(':gameId', $goal['gameId']);
				$stmt->bindValue(':uri', $goal['uri']);
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
			echo("Error checking to see if ".$goal['id']." exists.\n");
			// echo($e->getMessage());
		}
	}
	return false;
}

function getGif($goalId, &$pdo){
	//returns the uri if exists in DB
	if(isset($goalId)){
		try {
			$sql = "SELECT (gif_uri) FROM highlights WHERE id = :goalId;";
			$stmt = $pdo->prepare($sql);
			$stmt->execute(['goalId' => $goalId]); 
			$result = $stmt->fetch();
			if (isset($result) && isset($result["gif_uri"]) && strlen($result["gif_uri"]) > 0){
				return $result["gif_uri"];
			}
		} catch (PDOException $e) {
			echo("Error retrieving ".$goal['id']." URI.\n");
			// echo($e->getMessage());
		}
	}
	return false;
}

function needsSeeding($apiResponse){
	// determine whether we need to run seedgifs script
	// return true to always refresh upon game detail load.
	if (isset($apiResponse) && is_array($apiResponse)){

	}

	//Compare goal totals from API with SQL statement
	return false;
}