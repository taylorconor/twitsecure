<?php

session_start();

if (!isset($_REQUEST["oauth_token"]) || !isset($_REQUEST["oauth_verifier"])) {
	die("Invalid request");
}
if (!isset($_SESSION["oauth_token"])||!isset($_SESSION["oauth_token_secret"])) {
	die("Invalid session");
}
if ($_SESSION['oauth_token'] != $_REQUEST["oauth_token"]) {
	die("Session and request tokens don't match");
}

require_once("requestAuth.php");
$res = request_auth($_SESSION["oauth_token"], $_SESSION["oauth_token_secret"]);

?>

<html>
<head>
	<title>twitsecure</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container text-center">
	<h1>twitsecure</h1>
	<br>
	<p><?php print_r($res); ?></p>
</div>
</body>
</html>