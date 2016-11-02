<?php
// include_once("../../path.php"); //for uniform path on includes
include_once(dirname(__FILE__)."/api.php");
// if (file_exists("../../_env.php")){ include_once("../../_env.php");} else if (file_exists($_SERVER["DOCUMENT_ROOT"]."/_env.php")){ include_once($_SERVER["DOCUMENT_ROOT"]."/_env.php");}
include_once(dirname(__FILE__)."/../../_env.php");

function postToSlack($goalApiResponse, $scoreInfo, $url, $gif){
	if(isset($goalApiResponse) && is_array($goalApiResponse) && isset($gif)){
		$_r = $goalApiResponse;
		$_s = $scoreInfo;
		$sc = formatScore($_s);

		$data = array(
			"channel" => "#hockey",
			"username" => $_s["scorer"]. " (".$_s["seasonTotal"].")",
			"text" => $_r["description"] ."\n".$sc."\n<".$gif.">",
			"icon_emoji" => $_s["scoringTeam"] ? ":nhl_".le($_s["scoringTeam"]).":" : ":ghost:",
			"fallback" => $_r["description"]
		);

		$res = CallAPI("POST", $url, json_encode($data));
		unset($_r, $abbrev);
		echo("\n");
		print_r($res);
	}
}

function formatScore ($scoreInfo){
	if(isset($scoreInfo) & is_array($scoreInfo) ){
		$_s = $scoreInfo;
		$msg =  ":nhl_".le($_s['awayTeam']).": ";
		$msg .= $_s['awayTeam'] == $_s['scoringTeam'] ? "*".$_s['awayScore']."*" : $_s['awayScore'];
		$msg .= " ";
		$msg .= ":nhl_".le($_s['homeTeam']).": ";
		$msg .= $_s['homeTeam'] == $_s['scoringTeam'] ? "*".$_s['homeScore']."*" : $_s['homeScore'];
		$msg .= "   <https://www.gifbeukeboom.com/games/".$_s["gameId"]."|_".$_s["time_rem"]."  ".$_s["period"]."_>";
		return $msg;
	}
}