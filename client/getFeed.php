<?php

session_start();

if (!isset($_SESSION["id"]) || !isset($_SESSION["key"])) {
	die("Invalid session");
}

require_once("requester.php");

$tweets = get_tweets($_SESSION["id"], $_SESSION["key"]);

foreach($tweets as $tweet) { ?>

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
<?php } ?>