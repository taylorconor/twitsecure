<?

/*
 * client/requestAuth.php
 *
 * Used by the client to initiate the request for authentication with the
 * translation server, using the Diffie-Hellman Key Exchange method
 */

// client's secret, also known as 'x'
define("SECRET", 6);
define("TRANSLATOR", "http://localhost/translator");

// list some prime numbers
$primes = array(961751207, 961751209, 961751243, 961751257,
				961751261, 961751267, 961751321, 961751339);

$n = $primes[array_rand($primes)];
$g = rand(5, 50);
$gx = bcmod(bcpow($g, SECRET), $n);

// hardcoded just for testing
$handle = "taylorconor95";

try {
	// send the request to create authorisation with the translator
	$res = json_decode(
		file_get_contents(
			TRANSLATOR."/createAuth?handle=$handle&n=$n&g=$g&gx=$gx"
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

// finally calculate the key
$key = bcmod(bcpow($res["gy"], SECRET), $n);

die($key);