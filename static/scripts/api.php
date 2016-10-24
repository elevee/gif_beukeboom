<?php

// Method: POST, PUT, GET etc
// Data: array("param" => "value") ==> index.php?param=value

function CallAPI($method, $url, $data = false)
{
    $curl = curl_init();

    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_PUT, 1);
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    // Optional Authentication:
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    // curl_setopt($curl, CURLOPT_USERPWD, "username:password");

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);

    curl_close($curl);

    return $result;
}

function getGamesForDay($day){
	if (!is_null($day) && is_string($day)){
		$uri = "https://statsapi.web.nhl.com/api/v1/schedule?startDate=".$day."&endDate=".$day;
		$res = json_decode( CallAPI('get', $uri), true);
		$gms = $res["dates"][0]["games"];
		$gameIds = array();
		foreach($gms as $gm){
			$gameIds[] = $gm["gamePk"];
		}
		unset($uri,$res,$gms);
		return $gameIds;
	}
	return null;
}

function getGoalsForGame($gameId){
	// we need to get the goal ids and the goal video uris
	// echo("we're inside getGoalsForGame \n");
	if (!is_null($gameId) && is_string($gameId)){
		$uri = "https://statsapi.web.nhl.com/api/v1/game/".$gameId."/content?site=en_nhl";
		$desired_size = "FLASH_450K_400X224";
		try {
			$res = json_decode( CallAPI('get', $uri), true);
			$milestones = $res["media"]["milestones"];
			$goals = array();
			if(isset($milestones) && count($milestones) > 0){
				foreach ($milestones["items"] as $i => $milestone) {
					if ($milestone["type"] === "GOAL" && isset($milestone["highlight"]["playbacks"])) { //some goals are duplicate/error records that don't have playback array
						echo("Goal found: ". $milestone["description"] . "\n");
						$pbs = $milestone["highlight"]["playbacks"];
						$playbackUrl;
						foreach($pbs as $pb){
							if ($pb["name"] === $desired_size){
								$playbackUrl = $pb["url"];
								// playbackWidth = pbs[i]["width"] ? pbs[i]["width"] : 400;
							}
						}
						$goals[] = array(
							"id" => strval($milestone["highlight"]["mediaPlaybackId"]),
							"videoUri" => $playbackUrl
						);
					}
				}
			} else {
				echo("Milestones not set/ready yet for game " . $gameId. "\n");
			}
			return $goals;
		} catch (Exception $e) {
			echo("Problem with API call.  ". $e->getMessage() . "\n");
		}
		unset($uri,$res,$gm,$desired_size,$milestones,$pbs,$playbackUrl);
	} else { echo("no game provided"); }
	return null;
}

function getGameInfo($gameId){
	if(isset($gameId)){
		return json_decode(
			CallAPI(
				'get',
				"https://statsapi.web.nhl.com/api/v1/schedule?site=en_nhl&gamePk=".$gameId."&expand=schedule.scoringplays,schedule.ticket,schedule.teams,schedule.ticket",
				false
			), true
		);
	}
}

function getGoalInfo($goalId){
	if(isset($goalId)){
		return json_decode(
			CallAPI(
				'get',
				"https://nhl.bamcontent.com/nhl/id/v1/".$goalId."/details/web-v1.json",
				false
			), true
		);
	}
}

function getSchedInfo($gameId){
	if(isset($gameId)){
		return json_decode(
			CallAPI(
				'get',
				"https://statsapi.web.nhl.com/api/v1/schedule?gamePk=".$gameId."&expand=schedule.linescore,schedule.scoringplays,schedule.game.content.highlights.scoreboard&site=en_nhl",
				false
			), true
		);
	}
}

function calcRemainingTime($gl_time){ //input the goal time
	if(isset($gl_time) && is_string($gl_time) && strlen($gl_time) > 0){
		$prd_time = "20:00";
		list($hours, $minutes) = explode(':', $gl_time);
		$glTimestamp = mktime($hours, $minutes);

		list($hours, $minutes) = explode(':', $prd_time);
		$prdTimestamp = mktime($hours, $minutes);

		$seconds = $prdTimestamp - $glTimestamp;
		$minutes = sprintf("%02d", ($seconds / 60) % 60);
		$hours = floor($seconds / (60 * 60));
		return "$hours:$minutes";//Using hours & mins, but it's workin!
	}
}

function getScoreInfo($goalInfo, $gameId){
	// we need to get the score at the time of the goal, and teams involved
	if(isset($goalInfo)){
		//goalInfo is response from Goal API
		if(isset($goalInfo) && is_array($goalInfo) && isset($gameId)){
			$o = array();

			$_s = getSchedInfo($gameId);

			foreach ($goalInfo["keywordsAll"] as $k => $keyword) {
				switch ($keyword["type"]){
					case "statsEventId":
						$o['eventId'] = $keyword["value"];
						break;
					case "awayTeam":
						$o['awayTeam'] = $keyword["value"];
						break;
					case "homeTeam":
						$o['homeTeam'] = $keyword["value"];
						break;
					case "teamFileCode": //goal scoring team
						$o["scoringTeam"] = $keyword["value"];
						break;
				}
			}

			foreach ($_s["dates"][0]["games"][0]["scoringPlays"] as $key => $scoringPlay) {
				if($scoringPlay["about"]["eventId"] == $o["eventId"]){
					$o["awayScore"] 	= $scoringPlay["about"]["goals"]["away"];
					$o["homeScore"] 	= $scoringPlay["about"]["goals"]["home"];
					$o["time_scored"]	= $scoringPlay["about"]["periodTime"];
					$o["time_rem"] 		= calcRemainingTime($scoringPlay["about"]["periodTime"]);
					$o["period"] 		= $scoringPlay["about"]["ordinalNum"];
					break;
				}
			}

			return $o;
			// array(
			// 	'eventId' => 70,
			// 	'homeTeam' => 'TOR',
			// 	'awayTeam' => 'MTL',
			// 	'homeScore' => 1,
			// 	'awayScore' => 4,
			//  'scoringTeam' => 'TOR'
			// );
		}
	}
	return null;
}

function le ($input) {
	//logo abbrev exceptions
	$exceptions = array(
		"SJS" => "SJ",
		"TBL" => "TB",
		"LAK" => "LA"
	);
	return (in_array($input, array_keys($exceptions)) && $exceptions[$input]) ? $exceptions[$input] : $input;
}
