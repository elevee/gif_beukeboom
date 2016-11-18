<?php
include_once(dirname(__FILE__)."/_env.php");
include_once(dirname(__FILE__)."/static/scripts/db_connect.php");
// require_once(dirname(__FILE__)."/vendor/facebook/graph-sdk/src/Facebook/autoload.php");
// echo("\n");
// echo(sha512(''.$salt));
// echo("\n");

// session_start();
// require_once __DIR__ . '/src/Facebook/autoload.php';
// echo '<h3>Getting Access Token information</h3>';
// if (isset($_SESSION['fb_access_token'])) {
//         echo '$_SESSION[fb_access_token] ==>' .$_SESSION['fb_access_token'];
        
//     $fb = new Facebook\Facebook(['app_id' => '1115623171853481','app_secret' => '5d34f81d31b2ff2e60926e6c32f7e466','default_graph_version' => 'v2.4',]);
//     try {  // Returns a `Facebook\FacebookResponse` object
//       $response = $fb->get('/me?fields=id,name', $_SESSION['fb_access_token']);
//     } catch(Facebook\Exceptions\FacebookResponseException $e) {
//       echo 'Graph returned an error: ' . $e->getMessage();
//       exit;
//     } catch(Facebook\Exceptions\FacebookSDKException $e) {
//       echo 'Facebook SDK returned an error: ' . $e->getMessage();
//       exit;
//     }
//     $user = $response->getGraphUser();
//     echo 'Name: ' . $user['name']. "<br>";    
// }  else {
//     echo "Dont know about session";    
// }

function userIsLoggedIn($u = null){ //for FB users: pass in $user array
	global $salt;
	global $state;
	
	// echo("checking ". $u["id"]." <br>");
	// userInDB(array("fbId" => $u["id"]));
	// exit();

	//handle FB users
	if( isset($u["id"]) ){
		// echo( !userInDB(array("fbId" => $u["id"])) ? "returned true" : "returned false");
		if (!userInDB(array("fbId" => $u["id"]))){
			createUser($u);
		}

		if( userInDB( array("fbId" => $u["id"]) ) ){
			$_SESSION['loggedIn'] = true;
			// $_SESSION['email'] = $u["email"];
		}
	}
	
	if( isset($_POST['action']) && $_POST['action'] == "login" ){
		if( !isset($_POST['email']) || $_POST['email'] == '' || !isset($_POST['password']) || $_POST['password'] == '' ){
			$state["loginError"] = "Please fill in both fields";
			return false;
		}
		$password = sha512($_POST['password'] . $salt);
		
		if( userInDB( array("email" => $_POST["email"], "pw" => $password) )){
			session_start();
			$_SESSION['loggedIn'] = true;
			$_SESSION['email'] = $_POST['email'];
			$_SESSION['password'] = $password;
			return true;
		} else {
			session_start();
			unset($_SESSION['loggedIn']);
			unset($_SESSION['email']);
			unset($_SESSION['password']);
			$state['loginError'] = "The specified email address or password was incorrect.";
			return false;
		}
	}

	if ( isset($_POST['action']) && $_POST['action'] == "logout" ){
		if(!isset($_SESSION)){ 
	        session_start(); 
	    } 
		unset($_SESSION['loggedIn']);
		unset($_SESSION['email']);
		unset($_SESSION['password']);
		unset($_SESSION['fb_access_token']);
		session_destroy();
		header('Location: '.$_POST['goto']);
		// exit();
	}

	// This is used if we're doing native usernames/pwds
	// if ( isset($_SESSION['loggedIn']) ){
	// 	return userInDB( array( "email" => $_SESSION["email"], "pw" => $_SESSION["password"]) );
	// }
}

// function fbTokenCheck($token){
	// include_once(dirname(__FILE__)."/_env.php");
	// echo("<br> FB APP ID: ".$FB_APP_ID);
	// exit();
	// $fb = new Facebook\Facebook(['app_id' => $FB_APP_ID,'app_secret' => $FB_SECRET,'default_graph_version' => $FB_GRAPH_VERSION]);
 //    try {  // Returns a `Facebook\FacebookResponse` object
 //      $response = $fb->get('/me?fields=id,name', $token);
 //    } catch(Facebook\Exceptions\FacebookResponseException $e) {
 //      echo 'Graph returned an error: ' . $e->getMessage();
 //      return false;
 //    } catch(Facebook\Exceptions\FacebookSDKException $e) {
 //      echo 'Facebook SDK returned an error: ' . $e->getMessage();
 //      return false;
 //    }
 //    return $response;
// }

function sha512($string) {
    return hash('sha512', $string);
}

// function userHasRole($role){
// 	include_once dirname(__FILE__)."/static/scripts/db_connect.php";
// 	try {
// 		$sql = "SELECT * FROM users INNER JOIN roles ON users.id = roles.id WHERE email = :email AND role.id = :roleId";
// 		$s = $pdo->prepare($sql);
// 		$s->bindValue(":email", $email);
// 		$s->bindValue(":roleId", $role);
// 		$s->execute();
// 	} catch (PDOException $e) {
// 		$error = "Error searching for user roles.";
// 		include dirname(__FILE__)."/views/partials/_error.php";
// 	}
// 	$row = $s->fetch();
// 	if ($row[0] > 0){ return true; }
// 	return false;
// }