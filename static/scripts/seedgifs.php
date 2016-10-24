<?php
date_default_timezone_set('America/Los_Angeles');
// include_once("../../path.php"); //for uniform path on includes
// include_once(ABSPATH."/static/scripts/api.php");
// include_once(ABSPATH."/static/scripts/db_connect.php");
// include_once(ABSPATH."/static/scripts/s3_functions.php");
// include_once(ABSPATH."/static/scripts/slack_webhook.php");
// echo(dirname(__FILE__));
include_once(dirname(__FILE__)."/api.php");
include_once(dirname(__FILE__)."/db_connect.php");
include_once(dirname(__FILE__)."/s3_functions.php");
include_once(dirname(__FILE__)."/slack_webhook.php");


$toProcess = array();
$today = date("Y-m-d");
$games = getGamesForDay($today);
echo("\n");
// print_r(getGamesForDay("2016-10-12"));

echo("games retrieved: \n");
print_r($games);
echo("\n");
// exit();
foreach ($games as $i => $g) {
	$toProcess[$g] = getGoalsForGame(strval($g));
}
echo("PROCESS: \n");
print_r($toProcess);

// echo("\n");
foreach($toProcess as $gameId => $goals){
	// echo("This game is ". $gameId . "\n");
	addGameToDB($gameId, $pdo);
	foreach ($goals as $goal) {
		// Check to see if we already have that goal
		if(!gifExists($goal['id'], $pdo)) {
			if ( $g = createGif($gameId, $goal) ){
				$response = uploadGif($g, $s3);
				echo("response: \n");
				print_r($response);
				if (isset($response) && is_array($response) && !is_null($response['uri'])) {
					//Delete temp GIF on server
					echo("Temp Gif Deleted? \n");
					echo(deleteTempFiles($response['id']) ? "Yes" : "Nope");
					echo("\n");
					// Put resulting cloud URI into DB
					$added = addGifToDB($response, $pdo);

					if ($added){
						$url = $SLACK_WEBHOOK_URL;
						echo("Getting Goal info... \n");
						$_r = getGoalInfo($goal['id']);
						$_s = getScoreInfo($_r, $gameId);
						echo("Notifying Slack! \n");
						// print_r($_r);
						postToSlack($_r, $_s, $url, $response["uri"]);
					}
				} else {
					echo($goal['id']."  Failed to Upload! \n");
				}
			}
		}
	}
	echo("\n");	
}

function createGif($gameId, $goal){
	// takes: 
	// gameId(str) 
	// $goal (obj) 
	//    - id (str)
	//    - videoUri (str)
	$tmp_path = "../../tempGifs/";
	if(isset($goal) && isset($goal["videoUri"]) && strlen($goal["videoUri"]) > 0 ){
		if (!file_exists($tmp_path)) {
		    mkdir($tmp_path, 0777, true);
		}
		if(!file_exists($tmp_path.$goal['id'].".gif")){
			try {
				// script [arg1 (videoUrl), arg2 (tmp path/goalId)]
				$cmd = "./beukeboom.sh ".$goal['videoUri']." ".$tmp_path."/".$goal['id'];
					// "ffmpeg -i ".$goal['videoUri']." ".$tmp_path.$goal['id'].".gif";
				echo( escapeshellarg(exec($cmd)) );
				echo("GIF processing complete:  ".$goal["id"]."\n");
			} catch (Exception $e) {
				echo("Error creating GIF:  ". $e->getMessage() . "\n");
			}
		} else {
			echo("Goal ".$goal["id"]." already exists in tempGif folder.\n");
		}
		$output = $goal;
		$output["gameId"] = $gameId;
		unset($tmp_path, $cmd);
		return $output; // exports goal object but with gameId added in for next step
	}
	return null;
}

function deleteTempFiles($goalId){
	if(isset($goalId) && strlen($goalId > 0)){
		$files = array( 
			"../../tempGifs/".$goalId.".gif",
			"../../tempGifs/".$goalId.".png",
		);	
		foreach ($files as $file) {
			if (file_exists($file)){
				unlink($file);
			}
		}
		unset($files);
		return true;
	}
	return false;
}

// array(
// 	"**gameId**" => array(
// 		array(
// 			"id" => "484848484",
// 			"video_uri" => "erbfiebfua.mp4"
// 		),
// 		array(
// 			"id" => "484848484",
// 			"video_uri" => "erbfiebfua.mp4"
// 		),
// 		array(
// 			"id" => "484848484",
// 			"video_uri" => "erbfiebfua.mp4"
// 		),
// 	),
// 	"**gameId**" => array(
// 	)
// )