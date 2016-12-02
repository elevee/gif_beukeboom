<?php
include_once(dirname(__FILE__)."/db_connect.php");
include_once(dirname(__FILE__)."/s3_functions.php");
include_once(dirname(__FILE__)."/utilities.php");

$goalId 		= isset($_POST["goalId"]) ? $_POST["goalId"] : null;
$currentTime	= isset($_POST["currentTime"]) ? intval($_POST["currentTime"]) : null;
$start 			= $currentTime - 5; //rewinding a bit from moment goal is scored
$duration 		= 7; //default duration
$gameId 		= isset($_POST["gameId"]) ? $_POST["gameId"] : null;
$video_uri  	= null;

$start = $currentTime > 5 ? $currentTime - 5 : null; //starting goal 5s from marker;

// Retrieve video_uri from DB so people don't tamper
if(isset($goalId)){
	try {
		$sql = "SELECT (video_uri) FROM highlights WHERE id = :goalId;";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(['goalId' => $goalId]);
		$result = $stmt->fetch();
		if (isset($result) && isset($result["video_uri"]) && strlen($result["video_uri"]) > 0){
			$video_uri = $result["video_uri"];
		}
	} catch (PDOException $e) {
		echo("Error retrieving video URI for ".$goalId." URI.\n");
		// echo($e->getMessage());
	}
	
	// Create GIF with custom start/stop times or work from set goalTime.
	if (isset($goalId) && isset($video_uri) && isset($currentTime) && isset($duration)){
		$gif_settings = array(
			"id"			=> $goalId,
			"gameId"		=> $gameId,
			"videoUri"		=> $video_uri,
			"isShortGif" 	=> true,
			"start" 		=> $start,
			"duration" 		=> $duration
			// "tmpPath"		=> dirname(__FILE__)."/tempGifs/"
		);
		// echo(json_encode($gif_settings));

		if ($g = createGif($gif_settings)){
			// echo("Game ID is ".$g["gameId"]);
			// echo(json_encode($g));
			// exit();
			$response = uploadGif($g, $s3, true);
			// echo("response: \n");
			// print_r($response);
			if (isset($response) && is_array($response) && !is_null($response['uri'])) {
				// Put resulting cloud URI into DB
				$added = addShortGifToDB($response, $pdo);
				if ($added){
					deleteTempFiles(array("id" => $response['id'])); //Delete temp GIF/PNG on server
				}
			} else {
				echo($goalId."  Failed to Upload! \n");
			}
			echo true;
		}
	}
}

// echo $_POST["video_uri"];
// echo array(
// 	"id"		=> $_POST["goalId"],
// 	"currentTime" => $_POST["currentTime"]
// );

