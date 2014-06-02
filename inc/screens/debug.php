<?php define("SENDGRID_SGA_DEBUG_PAGE", true) ?>
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div><h2>Debug</h2>
<h3>Output</h3>
<pre><code><?php
	if(isset($_POST['submit']) && wp_verify_nonce($_POST['sendgrid_sga_nonce'], plugin_basename( __FILE__ ))){
		if($_POST['submit'] == "Sample Posts") {
			$posts = $wpdb->get_col( 
				"SELECT `posts`.`ID`
					FROM `$wpdb->posts` AS `posts`
					LEFT JOIN (
						SELECT `post_id`
						FROM `$sendgrid_sga_table`
						WHERE
							`done` = 1
					) as `stats`
					ON `posts`.`ID` = `stats`.`post_id`
					WHERE
						(
							`stats`.`post_id` IS NULL OR
							`stats`.`post_id` = 0
						) AND
						`post_status` = 'publish' AND
						`post_type` = 'post'
					ORDER BY `posts`.`post_date` DESC
					LIMIT 0,20"
			);
			sendgrid_sga_analyzeposts($posts);
		}
		if($_POST['submit'] == "Schedule Sample") {
			wp_schedule_event( time(), 'hourly', 'sendgrid_sga_analyzeposts_hook' );
		}
	}
?></code></pre>
<form method="POST">
		<p class="submit"><input type="submit" name="submit" class="button-primary" value="Sample Posts" /></p>
		<h3>Schedule</h3>
		<pre><code><?php
			print_r( wp_next_scheduled( 'sendgrid_sga_analyzeposts_hook' ) );
		?></code></pre>
		<p class="submit"><input type="submit" name="submit" class="button-primary" value="Schedule Sample" /></p>
		<?php wp_nonce_field( plugin_basename( __FILE__ ), 'sendgrid_sga_nonce'); ?>
	</form>
