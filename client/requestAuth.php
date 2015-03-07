<?php

/*
 * client/requestAuth.php
 *
 * Used by the client to initiate the request for authentication with the
 * Key Authority server, using the Diffie-Hellman Key Exchange method
 */

require_once("constants.php");
require_once("../crypto.php");
require "../twitteroauth/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

function request_auth($oauth_token, $oauth_token_secret) {

	// return values
	$ret = array();

	// build a temporary twitter connection based on the temporary oauth keys
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET,
		$oauth_token, $oauth_token_secret);

	// get a permanent access token
	$access = $connection->oauth("oauth/access_token",
		array("oauth_verifier" => $_REQUEST['oauth_verifier']));


	if (!isset($access["oauth_token"]) || !isset($access["oauth_token_secret"]) ||
		!isset($access["user_id"]) || !isset($access["screen_name"])) {
		die ("Invalid twitter response");
	}

	$res = ka_verify($access["screen_name"]);

	$key = $res["key"];

	// encrypt the json-encoded access array
	$access_encrypted = Crypto::encrypt(json_encode($access), $key);
	// double urlencode the encrypted data to ensure that + signs aren't interpreted
	// as spaces later on by the urldecoder
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

	$ret["key"] = $key;
	$ret["id"] = $res["id"];

	return $ret;
}

function ka_verify($handle) {

	// return values
	$ret = array();

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
		die("Error connecting to server");
	}

	if (isset($res["error"])) {
		die($res["error"]);
	} else if (!isset($res["gy"]) || !isset($res["id"])) {
		die("Response error");
	}

	// calculate the shared key
	$ret["key"] = bcmod(bcpow($res["gy"], SECRET), $n);
	$ret["id"] = $res["id"];

	return $ret;
}