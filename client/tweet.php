<?php

/*
 * client/tweet.php
 *
 * Used by the client to begin the tweet-sending process
 */

if (!isset($_REQUEST["t"])) {
	die("Invalid request");
}

session_start();

if (!isset($_SESSION["key"]) || !isset($_SESSION["id"])) {
	die("Invalid session");
}

require_once("requester.php");

die(send_tweet($_SESSION["id"], $_REQUEST["t"], $_SESSION["key"]));