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
// return the json encoded & encrypted tweets
die(Crypto::encrypt(json_encode($json), $secret));