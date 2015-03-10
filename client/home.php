<?php

session_start();

require "../twitteroauth/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

if (!isset($_SESSION["id"]) || !isset($_SESSION["key"])) {
	if (!isset($_REQUEST["oauth_token"]) || !isset($_REQUEST["oauth_verifier"])) {
		die("Invalid request");
	}
	if (!isset($_SESSION["oauth_token"]) || !isset($_SESSION["oauth_token_secret"])) {
		die("Invalid session");
	}
	if ($_SESSION['oauth_token'] != $_REQUEST["oauth_token"]) {
		die("Session and request tokens don't match");
	}

	require_once("requester.php");
	require_once("constants.php");

	// request authorisation with the Key Authority
	$res = request_auth($_SESSION["oauth_token"], $_SESSION["oauth_token_secret"]);

	// set session variables of our auth with the Key Authority
	$_SESSION["id"] = $res["id"];
	$_SESSION["key"] = $res["key"];
}

?>

<html>
<head>
	<title>twitsecure</title>
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/corgi.css">
	<script src="js/jquery.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script>
	$(function() {
		$('form').on('submit', function(e) {
			$.ajax({
				type: 'POST',
				url: 'tweet.php',
				data: { t: $('#tweetbox').val() }
			});
			e.preventDefault();
			// clear the tweet text from the tweetbox after it's been sent
			$('#tweetbox').val('');
		});
	});
	setInterval(function(){
		$.ajax({
			type: 'GET',
			url: 'getFeed.php',
			success: function(result) {
				console.log("run!");
				$("#tweet-container").html(result);
			}
		});
	},5000);
	</script>
</head>
<body>
<div class="container">
	<h1 class="text-center">twitsecure</h1>
	<br>
	<div class="row">
	<div class="corgi_feed_well col-xs-4">
		<div class="feed_body">
			<div class="row">
				<form>
					<input id="tweetbox" name="t" style="width:275px">
					<input type="submit" value="tweet" class="btn btn-primary">
				</form>
			</div>
		</div>
	</div>

	<div id="tweet-container" class="corgi_feed_well col-xs-7 col-xs-offset-1">
		<div class="feed_stacked">
			<div class="feed_body">
				<div class="row">
					<div class="feed_text">
						<p>No tweets to this group yet</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	</div>
</div>
</body>
</html>