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
include_once(dirname(__FILE__)."/utilities.php");

$toProcess = array();
$today = date("Y-m-d");

//in case we want custom, put date as arg 1.
$date = (isset($argv[1]) && is_string($argv[1])) ? $argv[1] : $today; //"2016-10-26"; 
$games = getGamesForDay($date);
echo("\n");

if(isset($games) && is_array($games) && count($games) > 0){
	echo("games retrieved: \n");
	print_r($games);
	echo("\n");
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
				$gif_settings = array(
					"gameId" 	=> $gameId,
					"id"		=> $goal["id"],
					"playerId"  => $goal["playerId"],
					"teamId"  	=> $goal["teamId"],
					"videoUri"	=> $goal["videoUri"],
				);
				if ( $g = createGif($gif_settings) ){
					$response = uploadGif($g, $s3);
					echo("response: \n");
					print_r($response);
					if (isset($response) && is_array($response) && !is_null($response['uri'])) {
						//Delete temp GIF on server
						echo("Temp Gif Deleted? \n");
						echo(deleteTempFiles(array("id" => $response['id'])) ? "Yes" : "Nope");
						echo("\n");
						// Put resulting cloud URI into DB
						$added = addGifToDB($response, $pdo);

						if ($added){
							$url = $SLACK_WEBHOOK_URL;
							echo("Getting Goal info... \n");
							$_r = getGoalInfo($goal['id']);
							$_s = getScoreInfo($_r, $gameId);
							if ($date == $today){
								echo("Notifying Slack! GAME ID IS ". $gameId ." \n");
								postToSlack($_r, $_s, $url, $response["uri"]);
							}
						}
					} else {
						echo($goal['id']."  Failed to Upload! \n");
					}
				}
			}
		}
		echo("\n");	
	}
} else {
	echo("No games on:  ". $date ."!\n");
}



// function createGif($gameId, $goal){
// 	// takes: 
// 	// gameId(str) 
// 	// $goal (obj) 
// 	//    - id (str)
// 	//    - videoUri (str)
// 	$tmp_path = "../../tempGifs/";
// 	if(isset($goal) && isset($goal["videoUri"]) && strlen($goal["videoUri"]) > 0 ){
// 		if (!file_exists($tmp_path)) {
// 		    mkdir($tmp_path, 0777, true);
// 		}
// 		if(!file_exists($tmp_path.$goal['id'].".gif")){
// 			try {
// 				// script [arg1 (videoUrl), arg2 (tmp path/goalId)]
// 				$cmd = "./beukeboom.sh ".$goal['videoUri']." ".$tmp_path."/".$goal['id'];
// 					// "ffmpeg -i ".$goal['videoUri']." ".$tmp_path.$goal['id'].".gif";
// 				escapeshellarg(exec($cmd));
// 				echo("GIF processing complete:  ".$goal["id"]."\n");
// 			} catch (Exception $e) {
// 				echo("Error creating GIF:  ". $e->getMessage() . "\n");
// 			}
// 		} else {
// 			echo("Goal ".$goal["id"]." already exists in tempGif folder.\n");
// 		}
// 		$output = $goal;
// 		$output["gameId"] = $gameId;
// 		$output["videoUri"] = $goal["videoUri"];
// 		unset($tmp_path, $cmd);
// 		return $output; // exports goal object but with gameId and videoUri added in for next step
// 	}
// 	return null;
// }

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