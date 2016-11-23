<?php
// include_once("../../path.php"); //for uniform path on includes
require dirname(__FILE__).'/../../vendor/autoload.php';
use Aws\S3\S3Client;

include_once(dirname(__FILE__)."/../../_env.php");

if (is_string($AWS_ACCESS_KEY_ID) && !is_null($AWS_ACCESS_KEY_ID) && is_string($AWS_SECRET_ACCESS_KEY) & !is_null($AWS_SECRET_ACCESS_KEY) && putenv("AWS_ACCESS_KEY_ID=$AWS_ACCESS_KEY_ID") && putenv("AWS_SECRET_ACCESS_KEY=$AWS_SECRET_ACCESS_KEY")){
	// echo("Environment variables set <br />". getenv("AWS_ACCESS_KEY_ID") . "<br />");
	$bucket = 'gifbarn';
	// $filepath should be absolute path to a file on disk						
	$filepath = $_SERVER["DOCUMENT_ROOT"].'/output.gif';
							
	// Instantiate the client.
	$s3 = S3Client::factory(
		array(
			"region" => "us-west-2",
			"version" => "2006-03-01",
		)
	);
	// Poll the bucket until it is accessible
	$s3->waitUntil('BucketExists', array('Bucket' => $bucket));
	echo("S3 open for business.");
	// Upload a file.
	// $result = $s3->putObject(array(
	//     'Bucket'       => $bucket,
	//     'Key'          => $keyname,
	//     'SourceFile'   => $filepath,
	//     'ContentType'  => 'image/gif',
	//     'ACL'          => 'public-read',
	//     'StorageClass' => 'REDUCED_REDUNDANCY',
	//     'Metadata'     => array(    
	//         'param1' => 'value 1',
	//         'param2' => 'value 2'
	//     )
	// ));
} else {
	echo("Problem with setting environment variables");
}

// echo "does object exist? \n";
// $s3->doesObjectExist($bucket, "/45400503.gif");

function uploadGif($goal, &$s3) {
	global $bucket;
	$key = $goal['gameId']."/".$goal['id'].".gif";
	$file = "../../tempGifs/".$goal['id'].".gif";
	// echo("Key:  ". $key. "\n");
	if (file_exists($file)) {  //!$s3->doesObjectExist($bucket, $key)
		//TODO: check for already uploaded gif first?
		try {
			echo("uploadGif: started \n");
		    $res = $s3->putObject(array( 
				'Bucket' 		=> $bucket,
				'Key'    		=> $key,
				'SourceFile'   	=> $file,
				'ContentType'  	=> 'image/gif',
				'StorageClass' 	=> 'REDUCED_REDUNDANCY',
				'ACL'    		=> 'authenticated-read'
			));
			echo $res['Expiration'] . "\n";
			echo $res['ServerSideEncryption'] . "\n";
			echo $res['ETag'] . "\n";
			echo $res['VersionId'] . "\n";
			echo $res['RequestId'] . "\n";
			echo $res['ObjectURL'] . "\n";
			// We can poll the object until it is accessible
			// $s3->waitUntil('ObjectExists', array(
			//     'Bucket' => $bucket,
			//     'Key'    => $key
			// ));
			return array(
				'id' 		=> $goal["id"],
				'gameId' 	=> $goal["gameId"],
				'uri' 		=> $res["ObjectURL"],
				'videoUri'  => $goal["videoUri"]
			);
		} catch (\Aws\S3\Exception\S3Exception $e) {
		    // The AWS error code (e.g., )
		    echo $e->getAwsErrorCode() . "\n";
		    // The bucket couldn't be created
		    echo $e->getMessage() . "\n";
		}
	} else {
		sprintf("%s:   Goal doesn't exist in tmp or already exists on server.", $goal["id"]);
	}
	return false;
}