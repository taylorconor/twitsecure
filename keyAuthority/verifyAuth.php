<?php

/*
 * keyAuthority/verifyAuth.php
 */

require_once("KeyAuthorityDB.php");
require_once("../crypto.php");
require_once("constants.php");

// verify the auth request
if (!isset($_REQUEST["id"]) || !isset($_REQUEST["access"])) {
	die(json_encode(array("error" => "Invalid request")));
}

$id = $_REQUEST["id"];
$access_encrypted = urldecode($_REQUEST["access"]);

$db = KeyAuthorityDB::instance();
if (!$db) {
	die(json_encode(array("error" => "DB fail")));
}

$res = $db->query("SELECT * FROM staging WHERE pk=$id");
$db_line = $res->fetchArray();
$secret = $db_line["secret"];

try {
	$access = json_decode(Crypto::decrypt($access_encrypted, $secret), true);
}
catch (Exception $ex) {
	die(json_encode(array("error" => "JSON decoding error")));
}

if (!isset($access["oauth_token"]) || !isset($access["oauth_token_secret"]) ||
	!isset($access["user_id"]) || !isset($access["screen_name"])) {
	die(json_encode(array("error" => "JSON decoding error")));
}

if ($access["screen_name"] != $db_line["handle"]) {
	die(json_encode(array(
		"error" => "screen_name mismatch between DB and request")));
}

require "../twitteroauth/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

// now verify that this user is who they say they are
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET,
	$access['oauth_token'], $access['oauth_token_secret']);

// get the credentials for this user from twitter
$user = $connection->get("account/verify_credentials");
// convert $user from stdClass into an array
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
		die(json_encode(array("error" => "DB fail")));
	}

	// return the user's new id
	die(json_encode(array("id" => $db->lastInsertRowID())));
}
else {
	// delete the user from staging since he's invalid
	$db->exec("DELETE FROM staging WHERE pk=$id");
	die(json_encode(array("error" => "Twitter verification failed")));
}