<?php

// start the session
session_start();

require_once("constants.php");
require '../twitteroauth/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;

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
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container text-center">
	<h1>twitsecure</h1>
	<br>
	<a href="<?= $url ?>">
		<img src="images/sign-in-with-twitter-gray.png"/>
	</a>
</div>
</body>
</html>