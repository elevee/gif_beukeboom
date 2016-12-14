<?php
include_once(dirname(__FILE__)."/../../static/scripts/api.php");
include_once(dirname(__FILE__)."/../../static/scripts/db_connect.php");	

// echo("Preprocessing game");
$gameId = trim($state['id']); //scrub ID somehow to rid it of injection
$userId = userInDB(array("fbId" => $_SESSION['fbId'], "grabDBId" => true));

function findStandingsFor($res, $teamId, $type){ //type "conference", "division"
	if(isset($res) && is_array($res)){
		$str = "";
		forEach($res["records"] as $r){
			forEach($r["teamRecords"] as $t){
				if($t["team"]["id"] == $teamId){
					switch($type){
						case "conference":
							$str .= $t["conferenceRank"] . " in the " . $r["conference"]["shortName"];
							break;
						case "division":
							$str .= $t["divisionRank"] . " in the " . $t["team"]["division"]["name"];
							break;
					}
				}
			}
		}
		return $str;
	}
	return false;
}

if (isset($gameId) && strlen($gameId) > 0){
  	// getGoalsForGame();
	// $goals 	= array();
	$avail_prds = array(1,2,3,4,5); //remaining ones will show as 'No Scoring'

	$feed_uri		= "https://statsapi.web.nhl.com/api/v1/game/".$gameId."/feed/live?site=en_nhl";
	$sched_uri 		= "https://statsapi.web.nhl.com/api/v1/schedule?gamePk=".$gameId."&expand=schedule.teams,schedule.linescore,schedule.broadcasts.all,schedule.ticket,schedule.game.content.media.epg,schedule.decisions,schedule.scoringplays,schedule.game.content.highlights.scoreboard,team.leaders&leaderCategories=points,goals,assists&site=en_nhl&teamId=";
	$media_uri 		= "https://statsapi.web.nhl.com/api/v1/game/".$gameId."/content";
	$stand_uri		= "https://statsapi.web.nhl.com/api/v1/standings/wildCardWithLeaders?expand=standings.record,standings.team,standings.division,standings.conference,team.schedule.next,team.schedule.previous&season=20162017";
	$db_uri			= "/static/scripts/api_db.php";
	// $schedResponse 	= null;
	// $mediaResponse 	= null;

	$schedRes = json_decode(CallAPI('GET', $sched_uri), true);
	$mediaRes = json_decode(CallAPI('GET', $media_uri), true);
	$feedRes  = json_decode(CallAPI('GET', $feed_uri), true);

	$_game = ( $schedRes["dates"][0]["games"] && $schedRes["dates"][0]["games"][0] && count($schedRes["dates"][0]["games"]) == 1) ? $schedRes["dates"][0]["games"][0] : null;
	if (isset($_game) && is_array($_game)){
		$pdCtr = 0; //counts the periods
		$pd = "";
		$UTC 	= new DateTimeZone('UTC');
		$newTZ 	= new DateTimeZone("America/Los_Angeles");
		$date 	= new DateTime( $_game["gameDate"], $UTC );
		$today 	= date('Y-m-d');
		$date->setTimezone( $newTZ );
		$awayRecord = $_game["teams"]["away"]["leagueRecord"];
		$homeRecord = $_game["teams"]["home"]["leagueRecord"];
		$game 	= array(
			"awayTeamName" 	=> $_game["teams"]["away"]["team"]["teamName"],
			"awayId" 		=> $_game["teams"]["away"]["team"]["id"],
			"awayAbbrev" 	=> le($_game["teams"]["away"]["team"]["abbreviation"]),
			"awayTeamScore"	=> $_game["teams"]["away"]["score"],
			"awayRecord" 	=> sprintf("%s-%s-%s", $awayRecord["wins"], $awayRecord["losses"], $awayRecord["ot"]),
			"homeTeamName" 	=> $_game["teams"]["home"]["team"]["teamName"],
			"homeId" 		=> $_game["teams"]["home"]["team"]["id"],
			"homeAbbrev" 	=> le($_game["teams"]["home"]["team"]["abbreviation"]),
			"homeTeamScore"	=> $_game["teams"]["home"]["score"],
			"homeRecord" 	=> sprintf("%s-%s-%s", $homeRecord["wins"], $homeRecord["losses"], $homeRecord["ot"]),
			"status"		=> $_game["status"]["detailedState"],
			"period"		=> isset($_game["linescore"]["currentPeriodOrdinal"]) ? $_game["linescore"]["currentPeriodOrdinal"]:null,
			"time_left"		=> isset($_game["linescore"]["currentPeriodTimeRemaining"]) ? $_game["linescore"]["currentPeriodTimeRemaining"]:null,
			"date"			=> $date->format('Y-m-d'),
			"isToday"		=> ($date->format('Y-m-d') == $today) ? true : false,
			"goals"			=> array(),
		);
		if($game["isToday"]){ //no need to get standings otherwise
			$standRes = json_decode(CallAPI('GET', $stand_uri), true);
			$game["awayStandings"] = findStandingsFor($standRes, $game["awayId"], "conference");
		}
	}

	$goal_ctr = 0;
	
	// 	} else if ($milestone["type"] == "GOAL" && $milestone["period"] == $period && $milestone["periodTime"] == $scoringPlay["about"]["periodTime"]) {
	// 		// Some shootout goals that lack a highlight will share the same goal time (0:00), period, etc
	// 		$game["goals"][$goal_ctr]["scorer"] 		= $scorer;
	// 		$game["goals"][$goal_ctr]["playerId"] 		= $playerId;
	// 		$game["goals"][$goal_ctr]["period"] 		= $milestone["period"];
	// 		$game["goals"][$goal_ctr]["time"]			= $milestone["periodTime"];
	// 		$game["goals"][$goal_ctr]["isHomeTeam"] 	= $scoringPlay["team"]["name"] === $_game["teams"]["home"]["team"]["name"] ? true : false;
	// 		$goal_ctr++;
	

	if (isset($feedRes)){
		$_game_data = $feedRes["gameData"];
		$_live_data = $feedRes["liveData"];
	
		if(isset($_live_data)){
			foreach ($_live_data["plays"]["scoringPlays"] as $k => $playId) {
				$scorer;
				$playerId;
				$seasonTotal;
				foreach($_live_data["plays"]["allPlays"] as $play) {
					if($play["about"]["eventIdx"] == $playId){
						foreach ($play["players"] as $player) {
							if($player["playerType"] === "Scorer") {
								$scorer 	= $player["player"]["fullName"];
								$playerId 	= $player["player"]["id"];
								$seasonTotal= $player["seasonTotal"];
							}
							//Assists?
						}
						// SET ALL WE NEED HERE from feed
						$game["goals"][$goal_ctr]["scorer"]			= $scorer;
						// $game["goals"][$goal_ctr]["scorer_expanded"]= isset($milestone["description"]) ? $milestone["description"] : null;
						$game["goals"][$goal_ctr]["playerId"]		= $playerId;
						$game["goals"][$goal_ctr]["seasonTotal"]	= $seasonTotal;
						$game["goals"][$goal_ctr]["period"] 		= $play["about"]["period"];
						$game["goals"][$goal_ctr]["time"]			= $play["about"]["periodTime"];
						$game["goals"][$goal_ctr]["isHomeTeam"] 	= $play["team"]["name"] === $_game["teams"]["home"]["team"]["name"] ? true : false;

						// Now add media
						if(isset($mediaRes) && isset($mediaRes["media"]["milestones"]["items"]) ){
							$milestones = $mediaRes["media"]["milestones"]["items"];
							foreach ($milestones as $milestone){
								if($milestone["statsEventId"] == $play["about"]["eventId"]){ //&& $milestone["type"] === "GOAL"
									// echo("<pre>");
									// print_r($milestone);
									// echo("</pre>");
									
									if ($milestone["highlight"]){
										$goalId = $milestone["highlight"]["mediaPlaybackId"];
										$game["goals"][$goal_ctr]["description"]  		= $milestone["highlight"]["description"];
										$game["goals"][$goal_ctr]["goalId"] 			= $goalId;
										$game["goals"][$goal_ctr]["gifUri"] 			= getGif($goalId, $pdo);
										$game["goals"][$goal_ctr]["shortGifUri"] 		= getGif($goalId, $pdo, true);
										$game["goals"][$goal_ctr]["favorited"] 			= userFav( array("userId" => $userId, "goalId" => $goalId) );
										$game["goals"][$goal_ctr]["popularity"] 		= getHighlightFavs($goalId);
										$game["goals"][$goal_ctr]["videoLinkout"]		= fetchMedia("video", $milestone, "FLASH_1200K_640X360");
										$game["goals"][$goal_ctr]["placeholderImg"] 	= fetchMedia("placeholder", $milestone, "372x210"); // Image previews if GIF hasn't processed yet
									}
									// echo($scorer.":  ".$milestone['gifUri']."?<br>");
									break;
								}
							}
						}
						$goal_ctr++;
						break;
					}
				}
				// echo($scorer." (".$seasonTotal.") <br>");
			}
		}
	}
	
	unset($goal_ctr);

	// echo "<pre>";
	// print_r($game["goals"]);
	// echo "</pre>";
	// echo("count: ". count($game['goals']));

	if(isset($game["awayTeamName"]) && strlen($game["awayTeamName"]) > 0 && isset($game["homeTeamName"]) && strlen($game["homeTeamName"]) > 0){
		$state['title'] = $game["awayTeamName"]." vs. ". $game["homeTeamName"]." | GIF Beukeboom";
	} else {
		$state['title'] = "GIF Beukeboom";	
	}
	
} else {
	$state['title'] = "Error | GIF Beukeboom";
}