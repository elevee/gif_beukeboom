<?php
date_default_timezone_set('America/Los_Angeles');
include_once(dirname(__FILE__)."/api.php");
include_once(dirname(__FILE__)."/db_connect.php");

$m = (isset($argv[1]) && is_string($argv[1])) ? $argv[1] : null;
$days_of_month = (isset($argv[2]) && is_string($argv[2])) ? $argv[2] : null;
$d = 1;

$toProcess = array();
$today = date("Y-m-d");

//in case we want custom, put date as arg 1.
// $date = (isset($argv[1]) && is_string($argv[1])) ? $argv[1] : $today; //"2016-10-26"; 
if (isset($m) && isset($days_of_month) ){
	while ($d <= $days_of_month){
		$date = "2016-".$m."-".$d;
		echo("_________________".$date."_________________\n");
		
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
				echo("Processing game ". $gameId . "\n");
				foreach ($goals as $goal) {
					// Check to see if we already have that goal
					if(gifExists($goal['id'])) {
						// echo("Gif for goal ".$goal['id']." EXISTS!\n");
						try {
							$sql = "UPDATE highlights SET video_uri = :videoUri WHERE id = :id"; //(id, type, gameId, gif_uri, video_uri) VALUES (:id, :type, :gameId, :uri, :videoUri);";
							$stmt = $pdo->prepare($sql);
							$stmt->bindValue(':id', $goal['id']);
							$stmt->bindValue(':videoUri', $goal['videoUri']);
							echo("Adding Goal ".$goal['id']." with videoUri \n".$goal['videoUri']." to DB. \n\n");
							$stmt->execute();
						} catch (PDOException $e) {
							echo("Error adding Goal ".$goal['id']." to Highlight table of DB.\n". $e);
							exit();
						}
					} else {
						echo("Gif for goal ".$goal['id']." doesn't exist.\n");
					}
				}
				echo("\n");	
			}
		} else {
			echo("No games on:  ". $date ."!\n");
		}
		$d++;
	}
}