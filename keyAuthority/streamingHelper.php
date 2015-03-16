<?php

require_once("constants.php");
require("StreamingAPI.php");

// keep the stream alive forever
while(true) {
	$t = new StreamingAPI();

	$t->login(CONSUMER_KEY, CONSUMER_SECRET, OAUTH_TOKEN, OAUTH_TOKEN_SECRET);

	$t->start(array('@'.GROUP_LEADER));
}