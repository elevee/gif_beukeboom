<?php
include_once(dirname(__FILE__)."/db_connect.php");
include_once(dirname(__FILE__)."/s3_functions.php");
include_once(dirname(__FILE__)."/utilities.php");

$goalId 		= $_POST["goalId"] ? $_POST["goalId"] : null;
$currentTime	= $_POST["currentTime"] ? intval($_POST["currentTime"]) : null;
$duration 		= 6; //default duration
$gameId 		= $_POST["gameId"] ? $_POST["gameId"] : null;
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
		echo("Error retrieving ".$goalId." URI.\n");
		// echo($e->getMessage());
	}
	// echo json_encode(array(
	// 	"id"		=> $goalId,
	// 	"currentTime" => $currentTime,
	// 	"start"    => $start,
	// 	"duration" => $duration
	// ));
	// exit();
	// echo $video_uri;
	// Create GIF with custom start/stop times or work from set goalTime.


	if (isset($goalId) && isset($video_uri) && isset($start) && isset($duration)){
		if ($g = createShortGif("./../../tempGifs/", $goalId, $video_uri, $start, $duration)){
			// createShortGif($goalId, $video_uri, $start, $duration);
			$g["gameId"] = $gameId;
			$response = uploadGif($g, $s3);
			echo true;
		}
	}
}

// echo $_POST["video_uri"];
// echo array(
// 	"id"		=> $_POST["goalId"],
// 	"currentTime" => $_POST["currentTime"]
// );

