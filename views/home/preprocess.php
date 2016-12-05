<?php
$date = isset($_GET["date"]) && is_string($_GET["date"]) && strlen($_GET["date"]) > 0 ? $_GET["date"] : date("Y-m-d");

$state['title'] = "GIF Beukeboom";

include_once("{$_SERVER["DOCUMENT_ROOT"]}/static/scripts/api.php");
echo("<link rel='stylesheet' type='text/css' href='/static/scripts/vendor/pikaday-1.5.1/css/pikaday.css' media='screen' />");

// $gameId = 2016020076;
// $uri = "https://statsapi.web.nhl.com/api/v1/schedule?gamePk=".$gameId."&expand=sched…ard,team.leaders&leaderCategories=points,goals,assists&site=en_nhl&teamId=";
// $result = json_decode(callAPI("get", $uri), true);
// echo($result);
?>