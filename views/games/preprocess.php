<?php
include_once(dirname(__FILE__)."/../../static/scripts/api.php");
include_once(dirname(__FILE__)."/../../static/scripts/db_connect.php");	

// echo("Preprocessing game");
$gameId = trim($state['id']); //scrub ID somehow to rid it of injection

//settings
$_linkout_video 		= "FLASH_1200K_640X360";
$_placeholder_image 	= "372x210";

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

	$sched_uri 		= "https://statsapi.web.nhl.com/api/v1/schedule?gamePk=".$gameId."&expand=schedule.teams,schedule.linescore,schedule.broadcasts.all,schedule.ticket,schedule.game.content.media.epg,schedule.decisions,schedule.scoringplays,schedule.game.content.highlights.scoreboard,team.leaders&leaderCategories=points,goals,assists&site=en_nhl&teamId=";
	$media_uri 		= "https://statsapi.web.nhl.com/api/v1/game/".$gameId."/content";
	$stand_uri		= "https://statsapi.web.nhl.com/api/v1/standings/wildCardWithLeaders?expand=standings.record,standings.team,standings.division,standings.conference,team.schedule.next,team.schedule.previous&season=20162017";
	$db_uri			= "/static/scripts/api_db.php";
	// $schedResponse 	= null;
	// $mediaResponse 	= null;

	$schedRes = json_decode(CallAPI('GET', $sched_uri), true);
	$mediaRes = json_decode(CallAPI('GET', $media_uri), true);

	$_game = ( $schedRes["dates"][0]["games"] && $schedRes["dates"][0]["games"][0] && count($schedRes["dates"][0]["games"]) == 1) ? $schedRes["dates"][0]["games"][0] : null;
	if (isset($_game) && is_array($_game)){
		$pdCtr = 0; //counts the periods
		$pd = "";
		$tz 	= new DateTimeZone('UTC');
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
			"period"		=> $_game["linescore"]["currentPeriodOrdinal"],
			"time_left"		=> $_game["linescore"]["currentPeriodTimeRemaining"],
			"date"			=> $date->format('Y-m-d'),
			"isToday"		=> ($date->format('Y-m-d') == $today) ? true : false,
			"goals"			=> array(),
		);
		if($game["isToday"]){ //no need to get standings otherwise
			$standRes = json_decode(CallAPI('GET', $stand_uri), true);
			$game["awayStandings"] = findStandingsFor($standRes, $awayId, "conference");
		}
	}

	$goal_ctr = 0;
	foreach ($_game["scoringPlays"] as $index => $scoringPlay) {
		$period = $scoringPlay["about"]["period"];
		$scorer;
		$scorerId;
		$milestones = $mediaRes["media"]["milestones"]["items"];

		foreach ($scoringPlay["players"] as $player) {
			if($player["playerType"] === "Scorer") {
				$scorer 	= $player["player"]["fullName"];
				$playerId 	= $player["player"]["id"];
				$seasonTotal= $player["seasonTotal"];
			}
			//Assists?
		}

		// echo "<pre>";
		// print_r($scoringPlay);
		// echo "</pre>";
		
		if(isset($milestones) && count($milestones) > 0){	
			foreach ($milestones as $i => $milestone) {
				if($milestone["type"] == "GOAL" && $milestone["statsEventId"] == $scoringPlay["about"]["eventId"] ){ //$milestone["period"] == $period wasn't nec., $milestone["playerId"] == $playerId was giving false positives
					// $game["goals"][] = $milestone; //if we want the whole thing
					$game["goals"][$goal_ctr]["scorer"]			= $scorer;
					$game["goals"][$goal_ctr]["scorer_expanded"]= $milestone["description"];
					$game["goals"][$goal_ctr]["playerId"]		= $playerId;
					$game["goals"][$goal_ctr]["seasonTotal"]	= $seasonTotal;
					$game["goals"][$goal_ctr]["period"] 		= $milestone["period"];
					$game["goals"][$goal_ctr]["time"]			= $milestone["periodTime"];
					$game["goals"][$goal_ctr]["description"] 	= $milestone["highlight"]["description"];
					$game["goals"][$goal_ctr]["isHomeTeam"] 	= $scoringPlay["team"]["name"] === $_game["teams"]["home"]["team"]["name"] ? true : false;
					$game["goals"][$goal_ctr]["goalId"] 		= $milestone["highlight"]["mediaPlaybackId"];
					$game["goals"][$goal_ctr]["gifUri"] 		= getGif($milestone["highlight"]["mediaPlaybackId"], $pdo);

					$pbs = $milestone["highlight"]["playbacks"];
					foreach ($pbs as $k => $v) {
						if($v["name"] == $_linkout_video){
							$game["goals"][$goal_ctr]["video_linkout"]	= $v["url"];
						}
					}
					// Image previews if GIF hasn't processed yet
					$prevs = $milestone["highlight"]["image"]["cuts"];
					foreach ($prevs as $k => $v) {
						if($k == $_placeholder_image){
							$game["goals"][$goal_ctr]["placeholder_img"] = $v["src"];
						}
					}
					$goal_ctr++;
					break; //there could be duplicates of the same goal. Putting an end to that.
				} else if ($milestone["type"] == "GOAL" && $milestone["period"] == $period && $milestone["periodTime"] == $scoringPlay["about"]["periodTime"]) {
					// Some shootout goals that lack a highlight will share the same goal time (0:00), period, etc
					$game["goals"][$goal_ctr]["scorer"] 		= $scorer;
					$game["goals"][$goal_ctr]["playerId"] 		= $playerId;
					$game["goals"][$goal_ctr]["period"] 		= $milestone["period"];
					$game["goals"][$goal_ctr]["time"]			= $milestone["periodTime"];
					$game["goals"][$goal_ctr]["isHomeTeam"] 	= $scoringPlay["team"]["name"] === $_game["teams"]["home"]["team"]["name"] ? true : false;
					$goal_ctr++;
				}
			}
		}
	}
	unset($goal_ctr);

	// echo("GOALS PARSED FROM MEDIA BY TYPE GOAL, (dupes in past): <br />");
	// echo "<pre>";
	// print_r($game); 
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