<?

/*
 * keyAuthority/createAuth.php
 *
 * Called from the client to the Key Authority. It responds to the client's
 * request for authentication (Diffie-Hellman). It also stores the client's
 * information (Twitter handle and the private key generated for the client)
 * in the database
 */

require_once("constants.php");
require_once("KeyAuthorityDB.php");

// verify the auth request
if (!isset($_REQUEST["handle"]) || !isset($_REQUEST["n"]) ||
	!isset($_REQUEST["g"]) || !isset($_REQUEST["gx"])) {
	die(json_encode(array("error" => "Invalid request")));
}

$n = $_REQUEST["n"];
$g = $_REQUEST["g"];
$gx = $_REQUEST["gx"];
$handle = $_REQUEST["handle"];

// private key
$key = bcmod(bcpow($gx, SECRET), $n);

$db = KeyAuthorityDB::instance();
if (!$db) {
	die(json_encode(array("error" => "DB fail")));
}

// insert the user into staging table (pk is generated automatically by sqlite)
// the user will be moved to the client table if he passes verification
if (!$db->exec("INSERT INTO staging(handle,secret) VALUES('$handle','$key')")) {
	die(json_encode(array("error" => "DB fail")));
}

die(json_encode(array(
	"gy" => bcmod(bcpow($g, SECRET), $n),
	"id" => $db->lastInsertRowID()
)));