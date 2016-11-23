<?php
include_once(dirname(__FILE__)."/db_connect.php");
include_once(dirname(__FILE__)."/s3_functions.php");
include_once(dirname(__FILE__)."/utilities.php");

$goalId 	= $_POST["goalId"] ? $_POST["goalId"] : null;
$start 		= $_POST["currentTime"] ? $_POST["currentTime"] : null;
$duration 	= 5; //default duration
$video_uri  = null;

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
}

echo $_POST["video_uri"];
echo array(
	"id"		=> $_POST["goalId"],
	"currentTime" => $_POST["currentTime"]
);




// Create GIF with custom start/stop times or work from set goalTime.
// if (isset($goalId) && isset($start) && isset($duration)){
// 	if (createShortGif($goalId, $video_uri, $start, $duration)){

// 	}
// }