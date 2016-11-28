<?php
date_default_timezone_set('America/Los_Angeles');
function createShortGif($tempPath, $goalId, $video_uri, $start, $duration){
	// takes: 
	// goalId(str)
	$tmp_path = "./../../tempGifs/";
	if(isset($goalId)){
		if (!file_exists($tmp_path)) {
		    mkdir($tmp_path, 0777, true);
		}
		if(!file_exists($tmp_path.$goalId."_s.gif")){
			try {
				// script [arg1 (videoUrl), arg2 (tmp path/goalId)]
				$cmd = "./beukeboom.sh ".$video_uri." ".$tmp_path.$goalId." short ".$start." ".$duration;
				// return $cmd;
				escapeshellarg(exec($cmd . " 2>&1"));
				echo("GIF processing complete:  ".$goalId."\n");
			} catch (Exception $e) {
				echo("Error creating GIF:  ". $e->getMessage() . "\n");
			}
		} else {
			echo("Goal ".$goalId." already exists in tempGif folder.\n");
		}
		unset($tmp_path, $cmd);
		return true;
	}
	return false;
}

function deleteTempFiles($goalId, $shortGif = false){ //naming different for shortgifs
	if(isset($goalId) && strlen($goalId > 0)){
		$files = array( 
			"../../tempGifs/".$goalId.".gif",
			"../../tempGifs/".$goalId.".png",
		);	
		foreach ($files as $file) {
			if (file_exists($file)){
				unlink($file);
			}
		}
		unset($files);
		return true;
	}
	return false;
}
