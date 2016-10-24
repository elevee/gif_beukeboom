<?php
$date = isset($_GET["date"]) && is_string($_GET["date"]) && strlen($_GET["date"]) > 0 ? $_GET["date"] : date("Y-m-d");

$state['title'] = "GIF Beukeboom";

include_once("{$_SERVER["DOCUMENT_ROOT"]}/static/scripts/api.php");
// include_once("./db_connect.php");
// include_once("{$_SERVER["DOCUMENT_ROOT"]}/s3_functions.php");

// $gameId = 2016020076;
// $uri = "https://statsapi.web.nhl.com/api/v1/schedule?gamePk=".$gameId."&expand=sched…ard,team.leaders&leaderCategories=points,goals,assists&site=en_nhl&teamId=";
// $result = json_decode(callAPI("get", $uri), true);
// echo($result);
?>