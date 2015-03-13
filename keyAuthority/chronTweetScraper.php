<?php

require_once("constants.php");
require_once("../crypto.php");
require "../twitteroauth/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

// make a connection as the group leader
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET,
	OAUTH_TOKEN, OAUTH_TOKEN_SECRET);

$json = $connection->get("search/tweets", array("q" => "@".GROUP_LEADER));

$fh = fopen(LOCAL_FEED, "w");
fwrite($fh, json_encode($json));