<?php
include_once(dirname(__FILE__)."/../../static/scripts/api.php");
include_once(dirname(__FILE__)."/../../static/scripts/db_connect.php");
include_once(dirname(__FILE__)."/../../static/scripts/boomboard_api.php");

// perhaps support query strings here so people can just
// look up ?type=team&team=BOS best BOS goals
// or
// ?type=all Best goals
// or 
// ?type=against&team=DAL  best goals against DAL

$userId = userInDB(array("fbId" => $_SESSION['fbId'], "grabDBId" => true));

$options = array(
	"type" => isset($_POST["type"]) ? $_POST["type"] : null,
	"season" => isset($_POST["season"]) ? $_POST["season"] : null
);
$boomRes = json_decode(boomQuery($options), true);
// print_r(json_decode($boomRes));
$goals = null;
$_goals = isset($boomRes["results"]) && count($boomRes["results"]) > 0 ? $boomRes["results"] : null;
if(isset($_goals) && is_array($_goals)){
	$goals = $_goals;
	foreach ($goals as $i => $gl) {
		$milestone = fetchMilestoneForGoal($gl["gameId"], $gl["id"]);
		// $goals
		$goals[$i]["scorer"] 			= getScorer($gl["playerId"])["fullName"];
		$goals[$i]["favorited"] 		= userFav( array("userId" => $userId, "goalId" => $gl["id"]));
		// Don't think we need this because we're pulling from DB.
		// $goals[$i]["videoLinkout"] 		= fetchMedia("video", $milestone, "FLASH_1200K_640X360");
		$goals[$i]["placeholderImg"] 	= fetchMedia("placeholder", $milestone, "372x210"); // Image previews if GIF hasn't processed yet

	}
}
// echo("<pre>");
// print_r($goals);
// echo("</pre>");

$state['title'] = "BoomBoard | GIF Beukeboom";