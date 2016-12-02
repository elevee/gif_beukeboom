<?php
use PHPUnit\Framework\TestCase;
include_once(dirname(__FILE__)."/../views/games/preprocess.php");

class GoalRetrieval extends TestCase {
	
	protected $media;
	protected $sched;
	// protected $shortGif1;
	// protected $tmpPath;

	protected function setUp(){
 		
 		$gameId = 2016020331; //Bruins-Flyers (went into shootout)
 		$media_uri = "https://statsapi.web.nhl.com/api/v1/game/".$gameId."/content";
 		$sched_uri = "https://statsapi.web.nhl.com/api/v1/schedule?gamePk=".$gameId."&expand=schedule.teams,schedule.linescore,schedule.broadcasts.all,schedule.ticket,schedule.game.content.media.epg,schedule.decisions,schedule.scoringplays,schedule.game.content.highlights.scoreboard,team.leaders&leaderCategories=points,goals,assists&site=en_nhl&teamId=";
 		$this->media = json_decode(CallAPI('GET', $media_uri), true);
 		$this->sched = json_decode(CallAPI('GET', $sched_uri), true);
    }

	public function testAPIResponses() {
		$this->assertEquals(true, count($this->sched["dates"][0]["games"][0]["scoringPlays"]) > 0);

		$this->assertEquals(true, count($this->media["media"]) > 0);
	}

	// public function testGetScorer(){
		// $this->assertEquals("David Krejci", count($this->media["media"]) > 0);
		// $this->assertEquals("David Krejci", count($this->media["media"]) > 0);
	// }

	// protected function tearDown(){
 //        deleteTempFiles($this->gif1);
 //        deleteTempFiles($this->shortGif1);
 //        unset($this->gif1);
 //        unset($this->shortGif1);
 //    }
}