<?php
use PHPUnit\Framework\TestCase;
include_once(dirname(__FILE__)."/../static/scripts/utilities.php");

class GifCreationTest extends TestCase {
	
	protected $gif1;
	protected $gif2;
	protected $shortGif1;
	protected $tmpPath;

	protected function setUp(){
        $gameId 	= "2016020275";
		$id 		= "46546703";
		$videoUri 	= "http://md-akc.med.nhl.com/mp4/nhl/2016/11/21/0fb6f0b4-ba60-4a4d-8e7e-6037e7b8fef0/1479694483991/asset_450k.mp4";
		$this->tmpPath 	= dirname(__FILE__) . "/../tempGifs/";

		$this->gif1 = array(
			"id" 		=> $id,
			"gameId" 	=> $gameId,
			"videoUri" 	=> $videoUri,
			// "tmpPath" 	=> $tmpPath
		);

        $this->shortGif1 = array(
        	"id" 			=> $id,
			"gameId" 		=> $gameId,
			"videoUri" 		=> $videoUri,
			"isShortGif" 	=> true,
			"start" 		=> 30,
			"duration" 		=> 6,
			// "tmpPath" 		=> $tmpPath
        );
    }

	public function testGifCreation() {
		$this->assertEquals(false, file_exists($this->tmpPath.$result['id'].".gif"));
		$this->assertEquals(false, file_exists($this->tmpPath.$result['id'].".png"));
		$result = createGif($this->gif1);
		$this->assertEquals($result["gameId"], $result["gameId"]);
		$this->assertEquals(true, file_exists($result["tmpPath"].$result['id'].".gif"));
		$this->assertEquals(true, file_exists($result["tmpPath"].$result['id'].".png"));
	}

	public function testShortGifCreation(){
		$this->assertEquals(false, file_exists($this->tmpPath.$result['id']."_s.gif"));
		$this->assertEquals(false, file_exists($this->tmpPath.$result['id']."_s.png"));
		$result = createGif($this->shortGif1);
		$this->assertEquals(true, file_exists($result["tmpPath"].$result['id']."_s.gif"));
		$this->assertEquals(true, file_exists($result["tmpPath"].$result['id']."_s.png"));
	}

	public function testGifDeletion() {
		$result = createGif($this->gif1);
		$this->assertEquals(true, file_exists($result["tmpPath"].$this->gif1['id'].".gif"));
		$this->assertEquals(true, file_exists($result["tmpPath"].$this->gif1['id'].".png"));
		deleteTempFiles($this->gif1);
		$this->assertEquals(false, file_exists($result["tmpPath"].$this->gif1['id'].".gif"));
		$this->assertEquals(false, file_exists($result["tmpPath"].$this->gif1['id'].".png"));
	}

	public function testShortGifDeletion() {
		$result2 = createGif($this->shortGif1);
		$this->assertEquals(true, file_exists($this->tmpPath.$this->shortGif1['id']."_s.gif"));
		$this->assertEquals(true, file_exists($this->tmpPath.$this->shortGif1['id']."_s.png"));
		deleteTempFiles($this->shortGif1);
		$this->assertEquals(false, file_exists($this->tmpPath.$this->shortGif1['id']."_s.gif"));
		$this->assertEquals(false, file_exists($this->tmpPath.$this->shortGif1['id']."_s.png"));
	}

	protected function tearDown(){
        deleteTempFiles($this->gif1);
        deleteTempFiles($this->shortGif1);
        unset($this->gif1);
        unset($this->shortGif1);
    }
}