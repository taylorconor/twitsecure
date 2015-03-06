<?php

/*
 * keyAuthority/verifyAuth.php
 */

require_once("KeyAuthorityDB.php");
require_once("../crypto.php");
require_once("constants.php");

// the return values
$ret = array();

// verify the auth request
if (!isset($_REQUEST["id"]) || !isset($_REQUEST["access"])) {
	$ret["error"] = "Invalid request";
	die(json_encode($ret));
}

$id = $_REQUEST["id"];
$access_encrypted = urldecode($_REQUEST["access"]);

$db = KeyAuthorityDB::instance();
if (!$db) {
	$ret["error"] = "DB fail";
	die(json_encode($ret));
}

$res = $db->query("SELECT * FROM staging WHERE pk=$id");
$db_line = $res->fetchArray();
$secret = $db_line["secret"];

try {
	$access = json_decode(Crypto::decrypt($access_encrypted, $secret), true);
}
catch (Exception $ex) {
	$ret["error"] = "JSON decoding error";
	die(json_encode($ret));
}

if (!isset($access["oauth_token"]) || !isset($access["oauth_token_secret"]) ||
	!isset($access["user_id"]) || !isset($access["screen_name"])) {
	$ret["error"] = "JSON decoding error";
	die(json_encode($ret));
}

if ($access["screen_name"] != $db_line["handle"]) {
	$ret["error"] = "screen_name mismatch between DB and request";
	die(json_encode($ret));
}

require "../twitteroauth/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

// now verify that this user is who they say they are
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET,
	$access['oauth_token'], $access['oauth_token_secret']);

// get the credentials for this user from twitter
$user = $connection->get("account/verify_credentials");
// dirty hack to convert $user from stdClass into an array
$user = json_decode(json_encode($user), true);

if (isset($user["screen_name"]) &&
	$user["screen_name"] == $access["screen_name"]) {
	// the user is verified
	$db->exec("DELETE FROM clients WHERE handle='".$db_line["handle"]."'");
	if (!$db->exec("INSERT INTO ".
					"clients(handle,secret,oauth_token,oauth_token_secret)".
					"VALUES('".$db_line["handle"]."','".$db_line["secret"]."',".
					"'".$access['oauth_token']."',".
					"'".$access['oauth_token_secret']."')")) {
		$ret["error"] = "DB fail";
		die(json_encode($ret));
	}

	// get the user's new id
	$id = $db->lastInsertRowID();

	$ret["id"] = $id;
	die(json_encode($ret));
}
else {
	// delete the user from staging since he's invalid
	$db->exec("DELETE FROM staging WHERE pk=$id");
	$ret["error"] = "Twitter verification failed";
	die(json_encode($ret));
}