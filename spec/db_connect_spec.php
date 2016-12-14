<?php
use PHPUnit\Framework\TestCase;
include_once(dirname(__FILE__)."/../static/scripts/db_connect.php");

class gifExists extends TestCase {
	protected $goalId = 46546703; //Drew Doughty goal against Ducks exists in dev DB
	protected function setUp(){
//  		$media_uri = "https://statsapi.web.nhl.com/api/v1/game/".$gameId."/content";
//  		$sched_uri = "https://statsapi.web.nhl.com/api/v1/schedule?gamePk=".$gameId."&expand=schedule.teams,schedule.linescore,schedule.broadcasts.all,schedule.ticket,schedule.game.content.media.epg,schedule.decisions,schedule.scoringplays,schedule.game.content.highlights.scoreboard,team.leaders&leaderCategories=points,goals,assists&site=en_nhl&teamId=";
//  		$this->media = json_decode(CallAPI('GET', $media_uri), true);
//  		$this->sched = json_decode(CallAPI('GET', $sched_uri), true);
	}

	public function testgifExists() {
		$noGoalId = 00000;
		// $this->assertEquals(false, gifExists($noGoalId));
		$this->assertEquals(true, gifExists($this->goalId));
	}

	public function testAddFavorite() {
		$o = array(
			"userId" => 36, //my user on dev
			"goalId" => $this->goalId
		);
		// $this->assertEquals(null, addFavorite(array()));
		$this->assertEquals(true, addFavorite($o));
	}

	// public function testRemoveFavorite() {
	// 	$o = array(
	// 		"userId" => 36, //my user on dev
	// 		"goalId" => $this->goalId
	// 	);
	// 	$this->assertEquals(true, removeFavorite($o));
	// }
}

// class Favoriting extends TestCase {
// 	protected function setUp(){
//  		$goalId = 46546703; //Drew Doughty goal against Ducks
 		
//  		$media_uri = "https://statsapi.web.nhl.com/api/v1/game/".$gameId."/content";
//  		$sched_uri = "https://statsapi.web.nhl.com/api/v1/schedule?gamePk=".$gameId."&expand=schedule.teams,schedule.linescore,schedule.broadcasts.all,schedule.ticket,schedule.game.content.media.epg,schedule.decisions,schedule.scoringplays,schedule.game.content.highlights.scoreboard,team.leaders&leaderCategories=points,goals,assists&site=en_nhl&teamId=";
//  		$this->media = json_decode(CallAPI('GET', $media_uri), true);
//  		$this->sched = json_decode(CallAPI('GET', $sched_uri), true);
//     }
// }