<?

/*
 * keyAuthority/verifyAuth.php
 */

require_once("KeyAuthorityDB.php");

// the return values
$ret = array();

// verify the auth request
if (!isset($_REQUEST["id"]) || !isset($_REQUEST["pass"])) {
	$ret["error"] = "Invalid request";
	die(json_encode($ret));
}

$id = $_REQUEST["id"];
$pass = $_REQUEST["pass"];

$db = KeyAuthorityDB::instance();
if (!$db) {
	$ret["error"] = "DB fail";
	die(json_encode($ret));
}

$handle = $db->exec("SELECT handle FROM staging WHERE id=$id");

die($handle);