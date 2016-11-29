<?php
use PHPUnit\Framework\TestCase;
include_once(dirname(__FILE__)."/../static/scripts/utilities.php");

class GifCreationTest extends TestCase {
	
	protected $gif1;
	protected $shortGif1;

	protected function setUp(){
        $gameId 	= "2016020275";
		$id 		= "46546703";
		$videoUri 	= "http://md-akc.med.nhl.com/mp4/nhl/2016/11/21/0fb6f0b4-ba60-4a4d-8e7e-6037e7b8fef0/1479694483991/asset_450k.mp4";
		$tmpPath 	= dirname(__FILE__) . "/../tempGifs";

		$this->gif1 = array(
			"id" 		=> $id,
			"gameId" 	=> $gameId,
			"videoUri" 	=> $videoUri,
			"tmpPath" 	=> $tmpPath
		);

        $this->shortGif1 = array(
        	"id" 			=> $id,
			"gameId" 		=> $gameId,
			"videoUri" 		=> $videoUri,
			"isShortGif" 	=> true,
			"start" 		=> 30,
			"duration" 		=> 6,
			"tmpPath" 		=> $tmpPath
        );
    }

	public function testGifCreation() {
		$result = createGif($this->gif1);
		$this->assertEquals($result["gameId"], $this->gif1["gameId"]);
		$this->assertEquals(true, file_exists(dirname(__FILE__)."/../tempGifs/".$result['id'].".gif"));
		$this->assertEquals(true, file_exists(dirname(__FILE__)."/../tempGifs/".$result['id'].".png"));
	}

	public function testShortGifCreation(){
		$result = createGif($this->shortGif1);
		$this->assertEquals(true, file_exists(dirname(__FILE__)."/../tempGifs/".$result['id']."_s.gif"));
		$this->assertEquals(true, file_exists(dirname(__FILE__)."/../tempGifs/".$result['id']."_s.png"));
	}

	// public function testGifDeletion() {
	// 	$gameId = "2016020275";
	// 	$id = "46546703";
	// 	$videoUri = "http://md-akc.med.nhl.com/mp4/nhl/2016/11/21/0fb6f0b4-ba60-4a4d-8e7e-6037e7b8fef0/1479694483991/asset_450k.mp4";
	// 	$settings = array(
	// 		"id" => $id,
	// 		"gameId" => $gameId,
	// 		"videoUri" => $videoUri,
	// 		"tmpPath" => dirname(__FILE__) . "/../tempGifs"
	// 	);
	// 	$result = createGif($settings);
	// 	$this->assertEquals($result["gameId"], $gameId);
	// 	$this->assertEquals(true, file_exists(dirname(__FILE__)."/../tempGifs/".$result['id'].".gif"));
	// 	$this->assertEquals(true, file_exists(dirname(__FILE__)."/../tempGifs/".$result['id'].".png"));
	// }

	protected function tearDown(){
        unset($this->gif1);
        unset($this->shortGif1);
    }
}