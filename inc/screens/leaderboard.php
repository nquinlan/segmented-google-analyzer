<?php
	require_once(SENDGRID_SGA_PATH . "inc/support/leaderboard.php");

	$period_start = create_start_time($_GET['period_start'], true);
	$period_end =  create_end_time($_GET['period_end'], true);

	create_table($period_start, $period_end);
	$top_users = get_top_users();
	$top_posts = get_top_posts();


?><div class="wrap sendgrid_sga leaderboard">
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
			<?php $formatted_user_info = format_user_info($user_info); ?>
			<tr>
				<td>
					<?php echo ($rank + 1); ?>
				</td>
				<td>
					<?php echo $formatted_user_info["post_author"]; ?>
				</td>
				<td>
					<?php echo $formatted_user_info["mantime"]; ?>
				</td>
				<td>
					<?php echo $formatted_user_info["visits"]; ?>
				</td>
				<td>
					<?php echo $formatted_user_info["pageviews"]; ?>
				</td>
				<td>
					<?php echo $formatted_user_info["avg_time_on_page"]; ?>
				</td>
				<td>
					<?php echo $formatted_user_info["entrance_rate"]; ?>
				</td>
				<td>
					<?php echo $formatted_user_info["exit_rate"]; ?>
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
			<?php $formatted_post_info = format_post_info($post_info); ?>
			<tr>
				<td>
					<?php echo ($rank + 1); ?>
				</td>
				<td>
					<a href="<?php echo $formatted_post_info["guid"]; ?>"><?php echo  $formatted_post_info["post_title"]; ?></a> by <?php echo  $formatted_post_info["post_author"]; ?>
				</td>
				<td>
					<?php echo $formatted_post_info["mantime"]; ?>
				</td>
				<td>
					<?php echo $formatted_post_info["visits"]; ?>
				</td>
				<td>
					<?php echo $formatted_post_info["pageviews"]; ?>
				</td>
				<td>
					<?php echo $formatted_post_info["avg_time_on_page"]; ?>
				</td>
				<td>
					<?php echo $formatted_post_info["entrance_rate"]; ?>
				</td>
				<td>
					<?php echo $formatted_post_info["exit_rate"]; ?>
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
