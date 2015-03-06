<?php

session_start();

require_once("constants.php");
require_once("../crypto.php");

if (!isset($_REQUEST["oauth_token"]) || !isset($_REQUEST["oauth_verifier"])) {
	die("Invalid request");
}
if (!isset($_SESSION["oauth_token"])||!isset($_SESSION["oauth_token_secret"])) {
	die("Invalid session");
}
if ($_SESSION['oauth_token'] != $_REQUEST["oauth_token"]) {
	die("Session and request tokens don't match");
}

require "../twitteroauth/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

// build a temporary twitter connection based on the temporary oauth keys
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET,
	$_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

// get a permanent access token
$access = $connection->oauth("oauth/access_token",
	array("oauth_verifier" => $_REQUEST['oauth_verifier']));


if (!isset($access["oauth_token"]) || !isset($access["oauth_token_secret"]) ||
	!isset($access["user_id"]) || !isset($access["screen_name"])) {
	die ("Invalid twitter response");
}

include_once("requestAuth.php");
$res = request_auth($access["screen_name"]);

$key = $res["key"];

// encrypt the json-encoded access array
$access_encrypted = Crypto::encrypt(json_encode($access), $key);
// double urlencode the encrypted data to ensure that + signs aren't interpreted
// as spaces later on by the urldecoder
$access_encrypted = urlencode(urlencode($access_encrypted));

try {
	$res = file_get_contents(
		"http://localhost/twitsecure/".
		"keyAuthority/verifyAuth.php?id=".$res["id"]."&access=$access_encrypted"
	);
	if (isset($res["error"])) {
		die($res["error"]);
	}
	else if (!isset($res["id"])) {
		die($res);
	}
}
catch (Exception $e) {
	die("Error requesting keyAuthority/verifyAuth");
}

$id = $res["id"];

?>

<html>
<head>
	<title>twitsecure</title>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">

	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css">

	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
</head>
<body>

<?php

print_r($res);

?>
</body>
</html>