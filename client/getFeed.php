<?php

session_start();

if (!isset($_SESSION["id"]) || !isset($_SESSION["key"])) {
	die("Invalid session");
}

require_once("requester.php");
require_once("constants.php");

$tweets = get_tweets($_SESSION["id"], $_SESSION["key"]);

if (!is_array($tweets)) {
	if (empty($tweets)) {?>
		<div class="feed_stacked">
			<div class="feed_body">
				<div class="row">
					<div class="feed_text">
						<p>No tweets to this group yet</p>
					</div>
				</div>
			</div>
		</div>
		<?php } else { ?>
<h3 style="color: #F00">Strange response:
	<?php echo $tweets ?> </h3>
<?php }} else if (isset($tweets["errors"]) && isset($tweets["errors"][0])
	&& isset($tweets["errors"][0]["message"])) { ?>
<h3 style="color: #F00"><?= $tweets["errors"][0]["message"] ?> </h3>
<?php
} else {
foreach(array_reverse($tweets) as $tweet) { ?>
<div class="feed_stacked">
<div class="feed_body">
	<div class="row">
		<div class="feed_profile_pic">
			<img src="<?= $tweet["pic"] ?>"
				 alt="meta image" class="meta_image">
		</div>
		<div class="feed_text">
			<p><?= $tweet["text"] ?></p>
		</div>
	</div>
</div>
</div>
<?php }} ?>