<?php
include_once "_env.php";

// echo("\n");
// echo(sha512(''.$salt));
// echo("\n");

function userIsLoggedIn(){
	global $salt;
	global $state;
	// echo("inside userIsLoggedIn");
	if( isset($_POST['action']) && $_POST['action'] == "login" ){
		if( !isset($_POST['email']) || $_POST['email'] == '' || !isset($_POST['password']) || $_POST['password'] == '' ){
			$state["loginError"] = "Please fill in both fields";
			return false;
		}
		$password = sha512($_POST['password'] . $salt);
		
		if(userInDB($_POST["email"], $password)){
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
		session_start();
		unset($_SESSION['loggedIn']);
		unset($_SESSION['email']);
		unset($_SESSION['password']);
		session_destroy();
		header('Location: '.$_POST['goto']);
		// exit();
	}

	// session_start();
	if ( isset($_SESSION['loggedIn']) ){
		return userInDB( $_SESSION["email"], $_SESSION["password"] );
	}
}

function userInDB($email, $pw){
	// echo("checking to see if ". $email." with the password ". $pw ." is in the DB. <br>");
	include_once(dirname(__FILE__)."/static/scripts/db_connect.php");
	global $pdo;
	try {
		$sql = 'SELECT COUNT(*) FROM users WHERE email = :email AND password = :pw';
		$s = $pdo->prepare($sql);
		$s->bindValue(":email", $email);
		$s->bindValue(":pw", $pw);
		$s->execute();
	} catch (PDOException $e) {
		$error = "Error finding user in DB.". $e;
		include_once(dirname(__FILE__)."/views/partials/_error.php");
		exit();
	}
	$row = $s->fetch();
	if ($row[0] > 0){ return true; }
	return false;
}

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