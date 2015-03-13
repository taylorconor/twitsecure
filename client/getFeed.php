<?php

session_start();

if (!isset($_SESSION["id"]) || !isset($_SESSION["key"])) {
	die("Invalid session");
}

require_once("requester.php");
require_once("constants.php");

$tweets = get_tweets($_SESSION["id"], $_SESSION["key"]);

if (isset($tweets["errors"]) && isset($tweets["errors"][0])
	&& isset($tweets["errors"][0]["message"])) { ?>
<h3 style="color: #F00"><?= $tweets["errors"][0]["message"] ?> </h3>
<?php
} else {
foreach($tweets["statuses"] as $tweet) {
if ($tweet["in_reply_to_screen_name"] == GROUP_LEADER) {?>
<div class="feed_stacked">
<div class="feed_body">
	<div class="row">
		<div class="feed_profile_pic">
			<img src="<?= $tweet["user"]["profile_image_url"] ?>"
				 alt="meta image" class="meta_image">
		</div>
		<div class="feed_text">
			<p><?= $tweet["text"] ?></p>
		</div>
	</div>
</div>
</div>
<?php }}} ?>