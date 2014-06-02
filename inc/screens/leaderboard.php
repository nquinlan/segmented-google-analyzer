<?php

	$period_start_time = isset($_GET['period_start']) ? strtotime($_GET['period_start']) : strtotime("-6months");
	$period_end_time = isset($_GET['period_end']) ? strtotime($_GET['period_end']) : time();

	$period_start = date("Y-m-d", $period_start_time);
	$period_end = date("Y-m-d", $period_end_time);

	function time_format ($inputSeconds) {
		// Source http://codeaid.net/php/convert-seconds-to-hours-minutes-and-seconds-%28php%29
		$secondsInAMinute = 60;
		$secondsInAnHour  = 60 * $secondsInAMinute;
		$secondsInADay    = 24 * $secondsInAnHour;

		// extract days
		$days = floor($inputSeconds / $secondsInADay);

		// extract hours
		$hourSeconds = $inputSeconds % $secondsInADay;
		$hours = floor($hourSeconds / $secondsInAnHour);

		// extract minutes
		$minuteSeconds = $hourSeconds % $secondsInAnHour;
		$minutes = floor($minuteSeconds / $secondsInAMinute);

		// extract the remaining seconds
		$remainingSeconds = $minuteSeconds % $secondsInAMinute;
		$seconds = ceil($remainingSeconds);

		// return the final array
		$obj = array(
		    'd' => (int) $days,
		    'h' => (int) $hours,
		    'm' => (int) $minutes,
		    's' => (int) $seconds,
		);
		return preg_replace("/^(0\w )+/", "", $days . "d " . $hours . "h " . $minutes . "m " . $seconds . "s");
	}

	$create_table = $wpdb->query( 
		$wpdb->prepare(
			"CREATE TEMPORARY TABLE `post_stats` AS (
				SELECT *
				FROM `$wpdb->posts` AS `posts`
				INNER JOIN `$sendgrid_sga_table` AS `stats`
				ON `posts`.`ID` = `stats`.`post_id`
				WHERE
					`stats`.`post_id` IS NOT NULL AND
					`post_date` >= %s AND
					`post_date` <= %s
			)",
			$period_start,
			$period_end
		)
	);

	$top_users = $wpdb->get_results(
		"SELECT `ID`, `post_author`, SUM(`pageviews`*`avg_time_on_page`) AS `mantime`, SUM(`visits`) AS `visits`, SUM(`pageviews`) AS `pageviews`, SUM(`avg_time_on_page`*`pageviews`)/SUM(`pageviews`) AS `avg_time_on_page`, SUM(`entrances`)/SUM(`pageviews`) AS `entrance_rate`, SUM(`exits`)/SUM(`pageviews`) AS `exit_rate`
			FROM `post_stats`
			GROUP BY `post_author`
			ORDER BY `mantime` DESC"
	);

	$top_posts = $wpdb->get_results(
		"SELECT `ID`, `post_title`, `guid`, `post_author`, `pageviews`*`avg_time_on_page` AS `mantime`, `visits`, `pageviews`, `avg_time_on_page`, `entrances`/`pageviews` AS `entrance_rate`, `exits`/`pageviews` AS `exit_rate`
			FROM `post_stats`
			ORDER BY `mantime` DESC"
	);
?>
<div class="wrap sendgrid_sga leaderboard">
	<div id="icon-segmented-google-analyzer" class="icon32"><br /></div><h2>Leaderboard</h2>
	<div class="clear"></div>

	<form class="timeframe">
		<input type="text" name="period_start" value="<?php echo $period_start; ?>" class="datepicker">
		-
		<input type="text" name="period_end" value="<?php echo $period_end; ?>" class="datepicker">
		<input type="hidden" name="page" value="<?php echo $_GET['page']; ?>">
		<input type="submit" class="button button-primary">
	</form>
	
	<div class="clear"></div>

	<h3>Top Users</h3>
	<table>
		<colgroup>
			<col class="rank">
			<col>
			<col class="stats mantime">
			<col class="stats" span="5">
		</colgroup>
		<thead>
			<tr>
				<th>Rank</th>
				<th>User</th>
				<th title="Man time is the amount of time spent on the page across all people. It provides some measure of engagement, plus reach." class="tooltip sorted">
					Man Time
				</th>
				<th title="Visits is an aproximation of the number of individual sessions that viewed the page." class="tooltip">
					Vists
				</th>
				<th title="Pageviews is the number of views a page saw in total." class="tooltip">
					Pageviews
				</th>
				<th title="Average Time on Page is the amount of time the average visitor spent on the post." class="tooltip">
					Average Time on Page
				</th>
				<th title="Entrance rate is the percent of people who came to SendGrid.com via this post." class="tooltip">
					Entrance Rate
				</th>
				<th title="Exit rate is the percent of people who left SendGrid.com after reading this post." class="tooltip">
					Exit Rate
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($top_users as $rank => $user_info) : ?>
			<tr>
				<td>
					<?php echo ($rank + 1); ?>
				</td>
				<td>
					<?php echo get_userdata($user_info->post_author)->display_name; ?>
				</td>
				<td>
					<?php echo time_format($user_info->mantime); ?>
				</td>
				<td>
					<?php echo number_format($user_info->visits); ?>
				</td>
				<td>
					<?php echo number_format($user_info->pageviews); ?>
				</td>
				<td>
					<?php echo time_format($user_info->avg_time_on_page); ?>
				</td>
				<td>
					<?php echo round($user_info->entrance_rate*100, 2) . "%"; ?>
				</td>
				<td>
					<?php echo round($user_info->exit_rate*100, 2) . "%"; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<div class="clear"></div>
	<h3>Top Posts</h3>
	<table>
		<colgroup>
			<col class="rank">
			<col>
			<col class="stats mantime">
			<col class="stats" span="5">
		</colgroup>
		<thead>
			<tr>
				<th>Rank</th>
				<th>Post</th>
				<th title="Man time is the amount of time spent on the page across all people. It provides some measure of engagement, plus reach." class="tooltip sorted">
					Man Time
				</th>
				<th title="Visits is an aproximation of the number of individual sessions that viewed the page." class="tooltip">
					Vists
				</th>
				<th title="Pageviews is the number of views a page saw in total." class="tooltip">
					Pageviews
				</th>
				<th title="Average Time on Page is the amount of time the average visitor spent on the post." class="tooltip">
					Average Time on Page
				</th>
				<th title="Entrance rate is the percent of people who came to SendGrid.com via this post." class="tooltip">
					Entrance Rate
				</th>
				<th title="Exit rate is the percent of people who left SendGrid.com after reading this post." class="tooltip">
					Exit Rate
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($top_posts as $rank => $post_info) : ?>
			<tr>
				<td>
					<?php echo ($rank + 1); ?>
				</td>
				<td>
					<a href="<?php echo $post_info->guid; ?>"><?php echo $post_info->post_title; ?></a> by <?php echo get_userdata($post_info->post_author)->display_name; ?>
				</td>
				<td>
					<?php echo time_format($post_info->mantime); ?>
				</td>
				<td>
					<?php echo number_format($post_info->visits); ?>
				</td>
				<td>
					<?php echo number_format($post_info->pageviews); ?>
				</td>
				<td>
					<?php echo time_format($post_info->avg_time_on_page); ?>
				</td>
				<td>
					<?php echo round($post_info->entrance_rate*100, 2) . "%"; ?>
				</td>
				<td>
					<?php echo round($post_info->exit_rate*100, 2) . "%"; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

<script>
	jQuery(function ($) {
		$.tablesorter.addParser({
		 	id: 'labeledDuration',
		 	is: function(s, table, cell) {
				return Boolean(s.match(/^\s*(((\d{1,2}d\s+)?\d{1,2}h\s+)?\d{1,2}m\s+)?\d{1,2}s\s*$/)); 
		 	},
		 	format: function(s, table, cell, cellIndex) {
				var times = s.match(/(\d{1,2})[dhms]/g);
				times.reverse();
				$.each(times, function(i, value) {
					times[i] = parseInt(value);
				});
				var time = ( ( (times[3]*24 || 0) + (times[2] || 0) )*60 + (times[1] || 0) )*60 + times[0];
		 	  return time;
		 	},
		 	parsed: true,
		 	type: 'numeric'
		});
		$(".tooltip").qtip({
			position: {
				"my" : "bottom right",
				"at" : "top center"
			},
			style: "qtip-light qtip-shadow"
		});
		$('.datepicker').each(function () {
			var picker = new Pikaday({
				field: this,
				format: 'YYYY-MM-DD',
			});
		});
		$(".sendgrid_sga table").each(function () {
			var $table = $(this);
			var $button = $('<button class="button download">Download</button>').insertAfter(this);
			$button.on("click", function () {
				var csv = $table.table2CSV({delivery:'value'});
				var blob = new Blob([csv], {type: "text/csv;charset=utf-8"});
				var tablename = $table.prev("h1,h2,h3,h4,h5,h6").text().toLowerCase().replace(/\s+/g, "-");
				var filename = tablename + "-" + $('.datepicker[name=period_start]').attr("value") + "-" + $('.datepicker[name=period_end]').attr("value") + ".csv"
				saveAs(blob, filename);
			});
			$table.tablesorter();
		});
	});
</script>
