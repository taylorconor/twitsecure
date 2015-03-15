<?php

/*
 * client/requester.php
 *
 * Used by the client to initiate the request for authentication with the
 * Key Authority server, using the Diffie-Hellman Key Exchange method
 */

require_once("constants.php");
require_once("../crypto.php");
require "../twitteroauth/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

function request_auth($oauth_token, $oauth_token_secret) {

	// build a temporary twitter connection based on the temporary oauth keys
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET,
		$oauth_token, $oauth_token_secret);

	// get a permanent access token
	$access = $connection->oauth("oauth/access_token",
		array("oauth_verifier" => $_REQUEST['oauth_verifier']));

	if (!isset($access["oauth_token"]) || !isset($access["oauth_token_secret"])
		|| !isset($access["user_id"]) || !isset($access["screen_name"])) {
		die ("Invalid twitter response");
	}

	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET,
		$access["oauth_token"], $access["oauth_token_secret"]);

	$profile = $connection->get("users/show",
		array("screen_name" => $access["screen_name"]));
	$profile = json_decode(json_encode($profile), true);
	if (!isset($profile["profile_image_url"])) {
		die("Error connecting to Twitter");
	}
	$profile_img = $profile["profile_image_url"];

	$res = ka_verify($access["screen_name"]);
	if (isset($res["error"])) {
		return $res;
	}

	$key = $res["key"];

	// encrypt the json-encoded access array
	$access_encrypted = Crypto::encrypt(json_encode($access), $key);
	// double urlencode the encrypted data to ensure that + signs aren't
	// interpreted as spaces later on by the urldecoder
	$access_encrypted = urlencode(urlencode($access_encrypted));

	try {
		$res = json_decode(file_get_contents(
			KEYAUTHORITY."/verifyAuth.php?id=".
			$res["id"]."&access=$access_encrypted"
		), true);
		if (isset($res["error"])) {
			die($res["error"]);
		} else if (!isset($res["id"])) {
			die($res);
		}
	} catch (Exception $e) {
		die("Error requesting keyAuthority/verifyAuth");
	}

	return array(
		"key" => $key,
		"id" => $res["id"],
		"profile_img" => $profile_img
	);
}

function ka_verify($handle) {

	// list some prime numbers
	$primes = array(961751207, 961751209, 961751243, 961751257,
					961751261, 961751267, 961751321, 961751339);

	$n = $primes[array_rand($primes)];
	$g = rand(5, 50);
	$gx = bcmod(bcpow($g, SECRET), $n);

	try {
		$res = json_decode(
			file_get_contents(
				KEYAUTHORITY."/createAuth.php?handle=$handle&n=$n&g=$g&gx=$gx"
			), true
		);
	} catch (Exception $ex) {
		return array("error" => "Error connecting to server");
	}

	if (isset($res["error"])) {
		return $res;
	} else if (!isset($res["gy"]) || !isset($res["id"])) {
		return array("error" => "Response error");
	}

	return array(
		"key" => bcmod(bcpow($res["gy"], SECRET), $n),
		"id" => $res["id"]
	);
}

function utf8_decode_r($d) {
	if (is_array($d)) {
		foreach ($d as $k => $v) {
			$d[$k] = utf8_decode_r($v);
		}
	} else if (is_string ($d)) {
		return utf8_decode($d);
	}
	return $d;
}

function get_tweets($id, $key) {
	try {
		$res = file_get_contents(
			KEYAUTHORITY."/getTweets.php?id=$id"
		);
	} catch (Exception $e) {
		die("Error requesting keyAuthority/getTweets");
	}

	return utf8_decode_r(json_decode(Crypto::decrypt($res, $key), true));
}

function send_tweet($id, $tweet, $key) {
	$t = urlencode(urlencode(Crypto::encrypt($tweet, $key)));
	try {
		$res = file_get_contents(
			KEYAUTHORITY."/postTweet.php?id=$id&t=$t"
		);
	} catch (Exception $e) {
		die("Error requesting keyAuthority/getTweets");
	}
	return $res;
}