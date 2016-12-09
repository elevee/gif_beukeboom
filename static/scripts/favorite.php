<?php
session_start();
include_once(dirname(__FILE__)."/db_connect.php");

$userId 		= isset($_SESSION['fbId']) ? userInDB(array("fbId" => $_SESSION['fbId'], "grabDBId" => true)) : null;
$goalId 		= isset($_POST["goalId"]) ? $_POST["goalId"] : null;

if(isset($userId) && isset($goalId)){
	// echo "g: ". $goalId . " u:". $userId;
	$o = array("userId" => $userId, "goalId" => $goalId);

	if (!addFavorite($o)){
		if(removeFavorite($o)){
			echo(json_encode(array("result" => "removed")));	
		};
	} else {
		echo(json_encode(array("result" => "added")));
	}

}