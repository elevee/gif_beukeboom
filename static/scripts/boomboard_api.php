<?php
include_once(dirname(__FILE__)."/db_connect.php");

$options = array(
	"type" => isset($_POST["type"]) ? $_POST["type"] : null,
	"season" => isset($_POST["season"]) ? $_POST["season"] : null
);
// boomBoardQuery($options);
// $output = array(
// 	"results" => boomBoardQuery($options)
// );
// echo($output);


// echo(boomQuery($options));

function boomQuery($o){
	return json_encode(
		array(
			"results" => boomBoardQuery($o)
		)
	);
}