<?php
date_default_timezone_set('America/Los_Angeles');
include_once(dirname(__FILE__)."/api.php");
include_once(dirname(__FILE__)."/db_connect.php");

$m = (isset($argv[1]) && is_string($argv[1])) ? $argv[1] : null;
$days_of_month = (isset($argv[2]) && is_string($argv[2])) ? $argv[2] : null;
$d = 1;

$toProcess = array();
$today = date("Y-m-d");

$highlights_array = array();

try {
	$sql = "SELECT * FROM highlights";
	$stmt = $pdo->prepare($sql);
	echo("HIGHLIGHTS: \n\n");
	$stmt->execute();
	foreach ($stmt as $i => $row)
	{
	    $highlights_array[$i]["id"] = $row["id"];
	    $highlights_array[$i]["gameId"] = $row["gameId"];
	}
} catch (PDOException $e) {
	echo("Error fetching highlights.\n". $e);
	exit();
}
print_r($highlights_array);

foreach ($highlights_array as $i => $goal) {
	echo("For goalID: ".$goal["id"]."\n");
	// $sched_uri 		= "https://statsapi.web.nhl.com/api/v1/schedule?gamePk=".$goal["gameId"]."&expand=schedule.teams,schedule.linescore,schedule.broadcasts.all,schedule.ticket,schedule.game.content.media.epg,schedule.decisions,schedule.scoringplays,schedule.game.content.highlights.scoreboard,team.leaders&leaderCategories=points,goals,assists&site=en_nhl&teamId=";
	$media_uri 		= "https://statsapi.web.nhl.com/api/v1/game/".$goal["gameId"]."/content";
	// $schedRes = json_decode(CallAPI('GET', $sched_uri), true);
	$mediaRes = json_decode(CallAPI('GET', $media_uri), true);
	
	//teamIDs
	// if(isset($schedRes["dates"]) && isset($schedRes["dates"]["games"]) && isset($schedRes["dates"]["games"]["scoringplays"][0]) && is_array($schedRes["dates"]["games"]["scoringplays"][0]) ){
	// 	$scoringPlays = $schedRes["dates"]["games"]["scoringPlays"];
	// 	foreach ($scoringPlays as $k => $scoringPlay) {
	// 		if($scoringPlay["about"][]){

	// 		}
	// 	}
	// } else {
	// 	echo("No date/game/scoringPlays array for goal".$goal["id"]);
	// }

	//playerIds
	if(isset($mediaRes) && isset($mediaRes["media"]["milestones"]["items"]) ){
		$milestones = $mediaRes["media"]["milestones"]["items"];
		foreach ($milestones as $milestone){
			if($milestone["type"] == "GOAL" && $milestone["highlight"]["mediaPlaybackId"] === $goal["id"]){
				// echo("\t mediaPlaybackId is ". $milestone["highlight"]["mediaPlaybackId"].", goal id is ". $goal["id"]."\n");
				// echo("\t".$milestone["highlight"]["title"]."\t scored by ".$milestone["playerId"]." has teamID of ". $milestone["teamId"]."\n");
				if ( isset($milestone["playerId"]) && isset($milestone["teamId"]) ){
					try {
						$sql = "UPDATE highlights SET playerId = :playerId, teamId = :teamId WHERE id = :id;"; //
						$stmt = $pdo->prepare($sql);
						$stmt->bindValue(':id', $goal['id']);
						$stmt->bindValue(':playerId', intval($milestone["playerId"]));
						$stmt->bindValue(':teamId', intval($milestone["teamId"]));
						$stmt->execute();
					} catch (PDOException $e) {
						echo("\t Error adding teamId (".$milestone["teamId"].") and playerId(".$milestone["playerId"].") to Goal ".$goal['id']." \n". $e);
						exit();
					}
					echo("\t Adding teamId and playerId to goal ".$goal['id']." to DB. \n\n");
				}
			}
		}
	} else {
		echo("No media array for goal".$goal["id"]);
	}
}


// //in case we want custom, put date as arg 1.
// $date = (isset($argv[1]) && is_string($argv[1])) ? $argv[1] : $today; //"2016-10-26"; 
// if (isset($m) && isset($days_of_month) ){
// 	while ($d <= $days_of_month){
// 		$date = "2016-".$m."-".$d;
// 		echo("_________________".$date."_________________\n");
		
// 		$games = getGamesForDay($date);
// 		echo("\n");

		// if(isset($games) && is_array($games) && count($games) > 0){
		// 	echo("games retrieved: \n");
		// 	print_r($games);
		// 	echo("\n");
		// 	foreach ($games as $i => $g) {
		// 		$toProcess[$g] = getGoalsForGame(strval($g));
		// 	}
		// 	echo("PROCESS: \n");
		// 	print_r($toProcess);

// 			// echo("\n");
// 			foreach($toProcess as $gameId => $goals){
// 				echo("Processing game ". $gameId . "\n");
// 				foreach ($goals as $goal) {
// 					// Check to see if we already have that goal
// 					if(gifExists($goal['id'])) {
// 						// echo("Gif for goal ".$goal['id']." EXISTS!\n");
// 						try {
// 							$sql = "UPDATE highlights SET video_uri = :videoUri WHERE id = :id"; //(id, type, gameId, gif_uri, video_uri) VALUES (:id, :type, :gameId, :uri, :videoUri);";
// 							$stmt = $pdo->prepare($sql);
// 							$stmt->bindValue(':id', $goal['id']);
// 							$stmt->bindValue(':videoUri', $goal['videoUri']);
// 							echo("Adding Goal ".$goal['id']." with videoUri \n".$goal['videoUri']." to DB. \n\n");
// 							$stmt->execute();
// 						} catch (PDOException $e) {
// 							echo("Error adding Goal ".$goal['id']." to Highlight table of DB.\n". $e);
// 							exit();
// 						}
// 					} else {
// 						echo("Gif for goal ".$goal['id']." doesn't exist.\n");
// 					}
				// }
// 				echo("\n");	
			// }
		// } else {
		// 	echo("No games on:  ". $date ."!\n");
		// }
		// $d++;
// 	}
// }