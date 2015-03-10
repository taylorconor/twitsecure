<?php

if (!isset($_REQUEST["id"]) || !isset($_REQUEST["t"])) {
	die("Invalid request");
}
$id = $_REQUEST["id"];
$tweet = urldecode($_REQUEST["t"]);

require_once("KeyAuthorityDB.php");
require_once("../crypto.php");
require_once("constants.php");

$db = KeyAuthorityDB::instance();
if (!$db) {
	die("DB fail");
}

// find this user in the db
$res = $db->query("SELECT * FROM clients WHERE pk=$id");
$db_line = $res->fetchArray();
if (!isset($db_line)) {
	die("Invalid ID");
}
// get the secret shared with this user
$secret = $db_line["secret"];

// decrypt the user's tweet
$tweet = Crypto::decrypt($tweet, $secret);

// encrypt the tweet with the KA secret
$tweet = Crypto::encrypt($tweet, TWEET_KEY);

// add the @cs3031 user to the tweet so it's being sent to the "group"
$tweet = "@cs3031 ".$tweet;

require "../twitteroauth/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

// make a connection as this user
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET,
	$db_line["oauth_token"], $db_line["oauth_token_secret"]);

$statues = $connection->post("statuses/update", array("status" => $tweet));