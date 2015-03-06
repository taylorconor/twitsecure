<?php

/*
 * client/requestAuth.php
 *
 * Used by the client to initiate the request for authentication with the
 * Key Authority server, using the Diffie-Hellman Key Exchange method
 */

require_once("constants.php");
require_once("../crypto.php");

if (!isset($_REQUEST["handle"])) {
	die("Invalid request");
}

// twitter handle
$handle = $_REQUEST["handle"];

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
}
catch (Exception $ex) {
	die("Error connecting to server");
}

if (isset($res["error"])) {
	die($res["error"]);
} else if (!isset($res["gy"]) || !isset($res["id"])) {
	print_r($res);
	die("Response error");
}

$ret = array();

// calculate the shared key
$ret["key"] = bcmod(bcpow($res["gy"], SECRET), $n);
$ret["id"] = $res["id"];

die(json_encode($ret));