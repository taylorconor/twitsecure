<?php

/*
 * keyAuthority/streamingHelper.php
 *
 * This script should be run on the Key Authority server to manage the
 * Twitter stream. Without this, no new tweets will be seen by the group
 */

require_once("constants.php");
require("StreamingAPI.php");

// keep the stream alive forever
while(true) {
	$t = new StreamingAPI();

	$t->login(CONSUMER_KEY, CONSUMER_SECRET, OAUTH_TOKEN, OAUTH_TOKEN_SECRET);

	echo "starting!\n";
	$t->start(array('@'.GROUP_LEADER));
}
