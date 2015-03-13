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

$fh = fopen(LOCAL_FEED, "r");
$json = fread($fh, filesize(LOCAL_FEED));
fclose($fh);
$json = json_decode($json, true);

if (isset($json["errors"])) {
	die(Crypto::encrypt(json_encode(utf8_encode_r($json)), $secret));
}

// decrypt all tweets in the response with the KA's secret key. these tweets
// will be re-encrypted later in this file using the client's key instead
foreach ($json["statuses"] as $idx => $tweet) {
	$text = $json["statuses"][$idx]["text"];
	$text = Crypto::decrypt(substr($text, strlen(GROUP_LEADER)+2), TWEET_KEY);
	$json["statuses"][$idx]["text"] = $text;
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