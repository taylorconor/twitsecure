<?

/*
 * keyAuthority/createAuth.php
 *
 * Called from the client to the Key Authority. It responds to the client's
 * request for authentication (Diffie-Hellman). It also stores the client's
 * information (Twitter handle and the private key generated for the client)
 * in the database
 */

require_once("KeyAuthorityDB.php");

// the return values
$ret = array();

// verify the auth request
if (!isset($_REQUEST["handle"]) || !isset($_REQUEST["n"]) ||
	!isset($_REQUEST["g"]) || !isset($_REQUEST["gx"])) {
	$ret["error"] = "Invalid request";
	die(json_encode($ret));
}

// Key Authority's secret, also known as 'y'
define("SECRET", 15);

$n = $_REQUEST["n"];
$g = $_REQUEST["g"];
$gx = $_REQUEST["gx"];
$handle = $_REQUEST["handle"];

// private key
$key = bcmod(bcpow($gx, SECRET), $n);

$db = KeyAuthorityDB::instance();
if (!$db) {
	$ret["error"] = "DB fail";
	die(json_encode($ret));
}

// insert the user into staging table (pk is generated automatically by sqlite)
// the user will be moved to the client table if he passes verification
if (!$db->exec("INSERT INTO staging(handle,secret) VALUES('$handle','$key')")) {
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