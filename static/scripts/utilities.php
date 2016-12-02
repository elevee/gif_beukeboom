<?php
date_default_timezone_set('America/Los_Angeles');
function createGif($s){ //$s == settings
	if(isset($s) && isset($s["videoUri"]) && strlen($s["videoUri"]) > 0 ){
		// settings: 
		$s["tmpPath"] 	= isset($s["tmpPath"]) 	? $s["tmpPath"] 	: dirname(__FILE__) . "/../../tempGifs/";

		if (!file_exists($s["tmpPath"])) {
		    mkdir($s["tmpPath"], 0777, true);
		}
		$filePath = $s["tmpPath"].$s['id'].(isset($s["isShortGif"])?"_s.gif":".gif");
		echo("\nFilepath:".$filePath."\n");
		if(!file_exists($filePath)){
			// echo("\nFile doesn't exist. Good...\n");
			$cmd = dirname(__FILE__)."/beukeboom.sh ".$s['videoUri']." ".$s["tmpPath"];
			try {
				// script [arg1 (videoUrl), arg2 (tmp path/goalId)]
				if (isset($s["isShortGif"])){
					$cmd .= $s['id']." short ".$s["start"]." ".$s["duration"];
				} else {
					$cmd .= $s['id'];
				}
				// echo("\ncommand is: ".$cmd."\n");
				escapeshellarg(shell_exec($cmd));
			} catch (Exception $e) {
				echo("Error creating GIF:  ". $e->getMessage() . "\n");
			}
			// echo("GIF processing complete:  ".$s["id"].(isset($s["isShortGif"])?"_s.gif":".gif"));
		} else {
			// echo("Goal ".$s["id"].(isset($s["isShortGif"])?"_s.gif":".gif")." already exists in tempGif folder.\n");
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
