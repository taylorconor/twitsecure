<?

/*
 * translator/createAuth.php
 *
 * Called from the client to the translator. It responds to the client's
 * request for authentication (Diffie-Hellman). It also stores the client's
 * information (Twitter handle and the private key generated for the client)
 * in the database
 */

// the return values
$ret = array();

// verify the auth request
if (!isset($_REQUEST["handle"]) || !isset($_REQUEST["n"]) ||
	!isset($_REQUEST["g"]) || !isset($_REQUEST["gx"])) {
	$ret["error"] = "Invalid request";
	die(json_encode($ret));
}

class TranslatorDB extends SQLite3 {
	function __construct() {
		$this->open('db');
	}
}

// translator's secret, also known as 'y'
define("SECRET", 15);

$n = $_REQUEST["n"];
$g = $_REQUEST["g"];
$gx = $_REQUEST["gx"];
$handle = $_REQUEST["handle"];

// private key
$key = bcmod(bcpow($gx, SECRET), $n);

$db = new TranslatorDB();
if (!$db){
	$ret["error"] = "DB fail";
	die(json_encode($ret));
}

// delete this user from the database if he was present already
if (!$db->exec("DELETE FROM clients WHERE handle='$handle'")) {
	$ret["error"] = "DB fail";
	die(json_encode($ret));
}
// insert the user (pk is generated automatically by sqlite)
if (!$db->exec("INSERT INTO clients(handle,secret) VALUES('$handle','$key')")) {
	$ret["error"] = "DB fail";
	die(json_encode($ret));
}
// get the user's id
$id = $db->lastInsertRowID();


// gy to be sent back the client
$gy = bcmod(bcpow($g, SECRET), $n);

$res["gy"] = $gy;
$res["id"] = $id;

die(json_encode($res));