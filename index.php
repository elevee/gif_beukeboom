<?php
// if(!session_id()) {
//     session_start();
// }
date_default_timezone_set('America/Los_Angeles');
// include_once("path.php"); //for uniform path on includes
require dirname(__FILE__).'/vendor/autoload.php';
require_once(dirname(__FILE__)."/vendor/facebook/graph-sdk/src/Facebook/autoload.php");
// require ABSPATH."/static/scripts/slack_webhook.php");
include_once(dirname(__FILE__)."/fb_login.php");
include_once(dirname(__FILE__)."/_env.php");

include_once(dirname(__FILE__)."/static/scripts/error_mode.php");
include_once("access.php");

userIsLoggedIn();
// print_r(userIsLoggedIn());
// unset($_SESSION['fb_access_token']);
// exit();

//after redirect from fb_login.php
if (isset($_SESSION['fb_access_token'])) {
	echo '$_SESSION[fb_access_token] ==>' .$_SESSION['fb_access_token'];
	// $response = fbTokenCheck($_SESSION['fb_access_token']);
	$fb = new Facebook\Facebook(['app_id' => $FB_APP_ID,'app_secret' => $FB_SECRET,'default_graph_version' => $FB_GRAPH_VERSION]);
    try {  // Returns a `Facebook\FacebookResponse` object
      $response = $fb->get('/me?fields=id,name', $_SESSION['fb_access_token']);
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
      echo 'Graph returned an error: ' . $e->getMessage();
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
      echo 'Facebook SDK returned an error: ' . $e->getMessage();
    }
    $user = $response->getGraphUser();
    if (userInDB(array("fbId" => $user["id"]))){

    } else {
    	// create new FB user in DB
    }
    print_r($user);
    echo('<br>');
    echo 'Name: ' . $user['name']. "<br>";
    echo 'Id: ' . $user['id']. "<br>";
    echo 'Name: ' . $user['name']. "<br>";

}  else {
    echo "Dont know about session, FB-wise.";    
}
// $fb = new Facebook\Facebook([
// 	'app_id' 				=> $FB_APP_ID,
// 	'app_secret' 			=> $FB_SECRET,
// 	'default_graph_version' => 'v2.8',
// ]);
// $helper = $fb->getRedirectLoginHelper();
// $response = $fb->get('/me?fields=id,name', $accessToken);
// $user = $response->getGraphUser();
// print_r($user);


// session start must be first thing in doc (before HTML tags)
// session_start();

// if(!userIsLoggedIn()){
// 	include 'views/partials/_login.php';
// 	exit();
// }

// if($_SERVER["REQUEST_URI"]){
// 	echo("REQUEST URI IS:  ". $_SERVER['REQUEST_URI']);	
// }

// unset($_SESSION['loggedIn']);
// unset($_SESSION['email']);
// unset($_SESSION['password']);

// echo("POST UP! <br>"); 
// print_r($_POST);
// echo("<br>");

// echo("shaaaaa of Iran ".sha512($_POST['password'] . "getemgoineh") ."<br>");

// reset some of the state values as they change on a page-request basis
if (!isset($state)){
	$state = array();
}
$state["view"]   = "home";
$state["id"]     = null;
$state["title"]  = isset($_SESSION['loggedIn']) ? "GIF Beukeboom | Login" : "GIF Beukeboom";

// assemble a list of views and display labels for our user interface, we use the keys of the views throughout the site...
// the key name should map to the folder name that the view is stored within;
// the value associated with each key is used as the display label for the view in the naivagation bar, breadcrumb trail and page title
// adding or removing key/value pairs from the list of views affects whether they appear in the navigation bar, as well as if they are
// accessible to users;

$views = array(
	// "/"			=> "Home",
	"home"      => "Home",
	"games" 	=> "Game"
);

// on each page load we want to know what the request uri is for the page, this is the part of the url which follows the domain name of the site...
// we use this information to determine which view the user has selected and if a specific record id has been provided; if this information is present
// in the request uri, we extract it from the string using a regular expression with the named capture groups, 'view', 'id', and 'uri', these capture
// groups will become the key names for the captured values when they are stored in the $matches array - we can then inspect the $matches array to see
// the captured data (if any), and set the relevant values into our $state global array...
if(isset($_SERVER) && is_array($_SERVER)) {
	// parse the request URI and determine if a valid view has been specified...
	if(isset($_SERVER["REQUEST_URI"]) && is_string($_SERVER["REQUEST_URI"]) && strlen($_SERVER["REQUEST_URI"]) > 0) {
		$_SERVER["REQUEST_URL"] = $_SERVER["REQUEST_URI"];
		// remove the query string, if present, from the request URI...
		// if(isset($_SERVER["QUERY_STRING"]) && is_string($_SERVER["QUERY_STRING"]) && strlen(trim($_SERVER["QUERY_STRING"])) > 0) {
		// 	if(($pos = strpos($_SERVER["REQUEST_URI"], "?".$_SERVER["QUERY_STRING"])) !== false) {
		// 		$_SERVER["REQUEST_URL"] = substr($_SERVER["REQUEST_URL"], 0, $pos);
		// 	}
		// }
		
		// $matches = array();
		// $regex   = "#^/(?P<view>".implode("|", array_merge(array_keys(array_reverse($views)), array_keys(array_reverse($special_views)))).")/?(?P<key>([a-z\-]+)\:)?(?P<id>([a-z0-9\-\_]+))?(\.(?P<sid>([a-z0-9\-]+)))?/?(?P<seo>[^\/]*)/?(?P<uri>[^\/]*)?/?(?P<query>\?.*)?$#";
		
		// echo("Our regex stands as <br /> ". $regex);

		$route_array = explode('/', $_SERVER['REQUEST_URI']);

		// echo("<br />");
		// echo("<pre>");
		// print_r($route_array);
		// echo("</pre>");

		// echo("<br />");
		// echo("<pre>");
		// print_r();
		// echo("</pre>");

		if(is_array($route_array)){
			//doing this backwards so we get specific routes first
			if( isset($route_array[0]) && $route_array[0] == "" && $route_array[1] == "" ){ //might not be necessary
				// echo("index route!");
				// $state["view"] = "home";
			}
			if(isset($route_array[1]) && array_key_exists($route_array[1], $views)){ // for capturing view
				$state["view"] = $route_array[1];
			}
			if(isset($route_array[1]) && array_key_exists($route_array[1], $views) && isset($route_array[2]) && is_numeric($route_array[2]) && (!isset($route_array[3]) || $route_array[3] == "") ){ //for capturing id
				$state["id"] = $route_array[2];
			}
		}

		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// page level code starts getting loaded below....
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		// if the user has selected a valid view, we load the pre-process script which will perform any necessary initialization of data (e.g. requesting data from the api), before we output any html...
		if(!is_null($state["view"]) && is_string($state["view"]) && strlen($state["view"]) > 0) { // && in_array($state["view"], array_merge(array_keys($views), array_keys($special_views)), true)) {
			if(file_exists("{$_SERVER["DOCUMENT_ROOT"]}/views/{$state["view"]}/preprocess.php")) { // make sure that the pre-process script exists...
				// debug(">>> including %s", "{$_SERVER["DOCUMENT_ROOT"]}/views/{$state["view"]}/preprocess.php");
				include_once("{$_SERVER["DOCUMENT_ROOT"]}/views/{$state["view"]}/preprocess.php"); // if so, include it here to cause php to parse its contents...
				// debug(">>> end of including preprocess.php");
			}
		}
	
	}
}

// echo("<br />");
// echo("<pre>");
// echo("STATE: <br/>");
// print_r($state);
// echo("</pre>");


echo("<!DOCTYPE html>");
echo("<html xmlns='http://www.w3.org/1999/xhtml' lang='en'>");
	echo("<head>");
		echo("<meta charset='utf-8' />");
  		echo("<title>".$state['title']."</title>");
		echo("<meta name='viewport' content='width=device-width' />");
		echo("<meta name='viewport' content='width=device-width, initial-scale=1.0' />");
		echo("<meta name='description' content='GIF Beukeboom'>");
  		echo("<meta name='author' content=''>");
		// echo("<link rel='shortcut icon' href='/favicon.ico' />");
		echo("<link rel='stylesheet' type='text/css' href='/static/scripts/vendor/foundation-6.2.3/css/foundation.css' media='screen' />");
		echo("<link rel='stylesheet' type='text/css' href='/static/stylesheets/common.css' media='screen' />");
		echo("<link rel='stylesheet' href='/static/scripts/vendor/font-awesome-4.7.0/css/font-awesome.min.css'>");
		// echo("<link rel='stylesheet' type='text/css' href='/static/stylesheets/theme-medium.css' media='screen'");
	

		echo("<script type='text/javascript' src='/static/scripts/vendor/foundation-6.2.3/js/vendor/jquery.js'></script>");
		echo("<script src='/static/scripts/vendor/momentjs-2.15.1/moment.js'></script>");

		// if the user has selected a valid view, and a template.js file exists, we load that now...
		if(isset($state["view"]) && is_string($state["view"])) { // && in_array($state["view"], array_merge(array_keys($views), array_keys($special_views)), true)) {
			if(file_exists("{$_SERVER["DOCUMENT_ROOT"]}/views/{$state["view"]}/template.js")) { // make sure that the pre-process script exists...
				echo("\t\t<script type='text/javascript' src='/views/{$state["view"]}/template.js?".time()."'></script>\n"); // if so, include it here to cause php to parse its contents...
			}
		}
		
		// if the user has selected a valid view, and a template.css file exists, we load that now...
		if(isset($state["view"]) && is_string($state["view"])) { //&& in_array($state["view"], array_merge(array_keys($views), array_keys($special_views)), true)) {
			if(file_exists("{$_SERVER["DOCUMENT_ROOT"]}/views/{$state["view"]}/template.css")) { // make sure that the pre-process script exists...
				echo("\t\t<link rel='stylesheet' href='/views/{$state["view"]}/template.css?".time()."'/>\n"); // if so, include it here to cause php to parse its contents...
			}
		}

	echo("</head>");
	echo("<body>");
		if( !isset($_SESSION["loggedIn"]) ){
			include 'views/partials/_header.php';
			include 'views/partials/_login.php';
			exit();
		}
		include_once("views/partials/_header.php");
		// echo("<pre>");
		// print_r($_SESSION);
		// echo("</pre>");
		// if a view has been selected by the user, check to see if the view template exists and if so include it here so php will parse it...
		if(isset($state["view"]) && is_string($state["view"]) && ($view = "{$_SERVER["DOCUMENT_ROOT"]}/views/{$state["view"]}/template.php") !== null && file_exists($view)) {
			include_once($view);
		} else { // if no view has been selected simply provide some default page content...
			echo("<section id='layout-content' style='padding-top:1em;'>");
				echo("<div class='page_title'><h1>Oops! Something is missing!</h1></div>");
				echo("<p>The specified page could not be found! Please check that the web page address is correct and try again.</p>");
				echo("<p>To return to the homepage, click <a href='/'>here</a>...</p>");
			echo("</section>");
		}

		include_once("views/partials/_footer.php");
	echo("</body>");
echo("</html>");