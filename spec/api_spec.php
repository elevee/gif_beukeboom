<?php
use PHPUnit\Framework\TestCase;
include_once(dirname(__FILE__)."/../static/scripts/api.php");

class nhlApi extends TestCase {

	protected function setUp(){
	}

	public function testGetTeamScoredOn() {
		$options = array(
			"gameId" => 2016020430,
			"teamId" => 6
		);
		$this->assertEquals(array(
			"teamName" => "Canadiens",
			"abbreviation" => "MTL"
		), getTeamScoredOn($options));
	}

	public function testGetScorer() {
		$playerId = 8471675; //Crosby
		$this->assertEquals(array(
			"fullName" => "Sidney Crosby"
		), getScorer($playerId));
	}
}