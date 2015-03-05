<?

/*
 * client/requestAuth.php
 *
 * Used by the client to initiate the request for authentication with the
 * Key Authority server, using the Diffie-Hellman Key Exchange method
 */

require_once("constants.php");
require_once("../crypto.php");

// list some prime numbers
$primes = array(961751207, 961751209, 961751243, 961751257,
				961751261, 961751267, 961751321, 961751339);

$n = $primes[array_rand($primes)];
$g = rand(5, 50);
$gx = bcmod(bcpow($g, SECRET), $n);

// hardcoded just for testing
$handle = "cs3031a";

try {
	$res = json_decode(
		file_get_contents(
			KEYAUTHORITY."/createAuth?handle=$handle&n=$n&g=$g&gx=$gx"
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

// calculate the Key Authority's key
$key = bcmod(bcpow($res["gy"], SECRET), $n);
$id = $res["id"];

echo "key = $key\n";

// send Twitter password over encrypted channel so the Key Authority can
// verify that this user is who he says he is
$password = Crypto::encrypt("cs3031", $key);
try {
	// send the request to create authorisation with the Key Authority
	$res = json_decode(
		file_get_contents(
			KEYAUTHORITY."/verifyAuth?id=$id&pass=$password"
		), true
	);
}
catch (Exception $ex) {
	die("Error connecting to server");
}

die($res);