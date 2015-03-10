<?php

if (!isset($_REQUEST["id"])) {
	die("Invalid request");
}
$id = $_REQUEST["id"];

require_once("KeyAuthorityDB.php");

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

require_once("constants.php");
require_once("../crypto.php");
require "../twitteroauth/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

// make a connection as @cs3031
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET,
	OAUTH_TOKEN, OAUTH_TOKEN_SECRET);

$json = $connection->get("statuses/mentions_timeline");
$json = json_decode(json_encode($json), true);

// decrypt all tweets in the response with the KA's secret key. these tweets
// will be re-encrypted later in this file using the client's key instead
foreach ($json as $idx => $tweet) {
	$text = $json[$idx]["text"];
	$text = "@cs3031 ".Crypto::decrypt(substr($text, 8), TWEET_KEY);
	$json[$idx]["text"] = $text;
}

// recurse over entire array structure and UTF8 encode it
function utf8_encode_r($d) {
	if (is_array($d)) {
		foreach ($d as $k => $v) {
			$d[$k] = utf8_encode_r($v);
		}
	} else if (is_string ($d)) {
		return utf8_encode($d);
	}
	return $d;
}

// return the json encoded & encrypted tweets
die(Crypto::encrypt(json_encode(utf8_encode_r($json)), $secret));