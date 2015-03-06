<?php

require_once("constants.php");
require '../twitteroauth/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;

// start the session
session_start();

try {
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
}
catch (Exception $ex) {
	die("Error connecting to Twitter");
}

// redirect to the main page after the user authorises their twitter account
$request_token = $connection->oauth('oauth/request_token',
	array('oauth_callback' =>
		"http://".$_SERVER["HTTP_HOST"]."/twitsecure/client/home.php"));

$_SESSION['oauth_token'] = $request_token['oauth_token'];
$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

// get the url that redirects to the twitter authentication page
$url = $connection->url('oauth/authorize',
	array('oauth_token' => $request_token['oauth_token']));

?>

<html>
	<head>
		<title>twitsecure</title>
	</head>
	<body>
		<a href="<?= $url ?>">Sign in with Twitter</a>
	</body>
</html>