<?php
date_default_timezone_set('America/Los_Angeles');
function createGif($s){ //$s == settings
	// settings: 
	$s["gameId"] 		? $s["gameId"] 		: null;
	$s["id"] 			? $s["id"] 			: null;
	$s["videoUri"] 		? $s["videoUri"]	: null;
	$s["tmpPath"] 		? $s["tmpPath"] 	: null;
	$s["isShortGif"] 	? true 				: false;

	if(isset($s) && isset($s["videoUri"]) && strlen($s["videoUri"]) > 0 ){
		if (!file_exists($s["tmpPath"])) {
		    mkdir($s["tmpPath"], 0777, true);
		}
		$filePath = $s["tmpPath"].$s['id']. ($s["isShortGif"]?"_s.gif":".gif");
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
		unset($tmp_path, $cmd);
		return $s;
	}
	return null;
}

function deleteTempFiles($goalId, $shortGif = false){ //naming different for shortgifs
	// settings: 
	$s["id"] 			? $s["id"] 			: null; //goal id
	$s["videoUri"] 		? $s["videoUri"]	: null;
	$s["tmpPath"] 		? $s["tmpPath"] 	: null;
	$s["isShortGif"] 	? true 				: false;

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
