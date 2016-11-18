<?php
if(!session_id()) {
    session_start();
}
require_once(dirname(__FILE__)."/vendor/facebook/graph-sdk/src/Facebook/autoload.php");
include_once(dirname(__FILE__)."/_env.php");
include_once(dirname(__FILE__)."/static/scripts/error_mode.php");

//shake my hand!
$fb = new Facebook\Facebook([
	'app_id' 				=> $FB_APP_ID,
	'app_secret' 			=> $FB_SECRET,
	'default_graph_version' => 'v2.8',
]);
$helper = $fb->getRedirectLoginHelper();

// http://stackoverflow.com/questions/32029116/facebook-sdk-returned-an-error-cross-site-request-forgery-validation-failed-th
// $_SESSION['FBRLH_state']=$_GET['state'];

try {
    $accessToken = $helper->getAccessToken();
} catch(Facebook\Exceptions\FacebookResponseException $e) {
    // When Graph returns an error
    echo 'Graph returned an error: ' . $e->getMessage();
    //exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
    // When validation fails or other local issues
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    //exit;
}
if (! isset($accessToken)) {
    if ($helper->getError()) {
        header('HTTP/1.0 401 Unauthorized');
        echo "Error: " . $helper->getError() . "\n";
        echo "Error Code: " . $helper->getErrorCode() . "\n";
        echo "Error Reason: " . $helper->getErrorReason() . "\n";
        echo "Error Description: " . $helper->getErrorDescription() . "\n";
        exit;
    } else {
        // DOING ALL THIS ON HEADER PARTIAL
        // $permissions = ['email']; // Optional permissions
        // $loginUrl = $helper->getLoginUrl($FB_LOGIN_URL, $permissions);
        // echo '<a href="' . htmlspecialchars($loginUrl) . '">Log in with Facebook!</a>';
        // header('HTTP/1.0 400 Bad Request');
        // echo 'Bad request';
    }
} else { //logged in
    // try {
    //     $accessToken = $helper->getAccessToken();
    // } catch(Facebook\Exceptions\FacebookResponseException $e) {
    //     // When Graph returns an error
    //     echo 'Graph returned an error: ' . $e->getMessage();
    //     //exit;
    // } catch(Facebook\Exceptions\FacebookSDKException $e) {
    //     // When validation fails or other local issues
    //     echo 'Facebook SDK returned an error: ' . $e->getMessage();
    //     //exit;
    // }
    
    try {
        $response = $fb->get('/me?fields=id,name,email,picture,first_name', $accessToken);
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
        echo 'Graph returned an error: ' . $e->getMessage();
        exit;
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;
    }
    $user = $response->getGraphUser();
    
    // echo 'Name: ' . $user['name'] . "<br>";
    // echo 'ID: ' . $user['id'];
    // OR
    // echo 'Name: ' . $user->getName();
    // Logged in
    // echo '<h3>Access Token</h3>';
    // var_dump($accessToken->getValue());
    // The OAuth 2.0 client handler helps us manage access tokens
    $oAuth2Client = $fb->getOAuth2Client();
    // Get the access token metadata from /debug_token
    $tokenMetadata = $oAuth2Client->debugToken($accessToken);
    // echo '<h3>Metadata</h3>';
    // var_dump($tokenMetadata);
    $tokenMetadata->validateAppId($FB_APP_ID);
    $tokenMetadata->validateExpiration();
    if (! $accessToken->isLongLived()) {
      // Exchanges a short-lived access token for a long-lived one
      try {
        $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
      } catch (Facebook\Exceptions\FacebookSDKException $e) {
        echo "<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n";
        exit;
      }
      // echo '<h3>Long-lived</h3>';
      // var_dump($accessToken->getValue());
    }
    // echo '<h3>Storing Session Information</h3>';
    // echo "-->". $accessToken;
    $_SESSION['fb_access_token'] = (string) $accessToken;
    // echo "-->". $user['name'];
    header("Location: ".$FB_LOGIN_URL); /* Redirect browser */
}