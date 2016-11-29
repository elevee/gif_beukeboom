<?php
date_default_timezone_set('America/Los_Angeles');
function createGif($s){ //$s == settings
	if(isset($s) && isset($s["videoUri"]) && strlen($s["videoUri"]) > 0 ){
		// settings: 
		$s["tmpPath"] 	= isset($s["tmpPath"]) 	? $s["tmpPath"] 	: dirname(__FILE__) . "/../../tempGifs/";

		if (!file_exists($s["tmpPath"])) {
		    mkdir($s["tmpPath"], 0777, true);
		}
		$filePath = $s["tmpPath"].$s['id'].($s["isShortGif"]?"_s.gif":".gif");
		if(!file_exists($filePath)){
			$cmd = dirname(__FILE__)."/beukeboom.sh ".$s['videoUri']." ".$s["tmpPath"]."/";
			try {
				// echo("command is ".$cmd."\n");
				// script [arg1 (videoUrl), arg2 (tmp path/goalId)]
				if (isset($s["isShortGif"])){
					$cmd .= $s['id']." short ".$s["start"]." ".$s["duration"];
				} else {
					$cmd .= $s['id'];
				}
				
				escapeshellarg(exec($cmd));
				echo("GIF processing complete:  ".$s["id"]."\n");
			} catch (Exception $e) {
				echo("Error creating GIF:  ". $e->getMessage() . "\n");
			}
		} else {
			echo("Goal ".$s["id"]." already exists in tempGif folder.\n");
		}
		unset($filePath, $cmd);
		return $s;
	}
	return null;
}

function deleteTempFiles($s){ //naming different for shortgifs
	// settings: 
	$s["tmpPath"] 	= isset($s["tmpPath"]) 	? $s["tmpPath"] 	: dirname(__FILE__) . "/../../tempGifs/";

	if(isset($s["id"]) && strlen($s["id"] > 0) && isset($s["tmpPath"])){
		$files = array( 
			$s["tmpPath"].$s["id"].(isset($s["isShortGif"])?"_s":"").".gif",
			$s["tmpPath"].$s["id"].(isset($s["isShortGif"])?"_s":"").".png",
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
