<?php

	function create_start_time($time, $as_string = false) {
		$created_time = isset($time) ? strtotime($time) : strtotime("-6months");
		if($as_string) {
			return date("Y-m-d", $created_time);
		}
		return $created_time;
	}

	function create_end_time($time, $as_string = false) {
		$created_time = isset($time) ? strtotime($time) : time();
		if($as_string) {
			return date("Y-m-d", $created_time);
		}
		return $created_time;
	}

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

	function create_table($period_start, $period_end) {
		global $wpdb;

		$sendgrid_sga_table = sendgrid_sga_get_table();

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

		return $create_table;
	}
	function get_top_users () {
		global $wpdb, $sendgrid_sga_table;

		$top_users = $wpdb->get_results(
			"SELECT `ID`, `post_author`, SUM(`pageviews`*`avg_time_on_page`) AS `mantime`, SUM(`visits`) AS `visits`, SUM(`pageviews`) AS `pageviews`, SUM(`avg_time_on_page`*`pageviews`)/SUM(`pageviews`) AS `avg_time_on_page`, SUM(`entrances`)/SUM(`pageviews`) AS `entrance_rate`, SUM(`exits`)/SUM(`pageviews`) AS `exit_rate`
				FROM `post_stats`
				GROUP BY `post_author`
				ORDER BY `mantime` DESC"
		);
		return $top_users;
	}

	function get_top_posts () {
		global $wpdb, $sendgrid_sga_table;

		$top_posts = $wpdb->get_results(
			"SELECT `ID`, `post_title`, `guid`, `post_author`, `pageviews`*`avg_time_on_page` AS `mantime`, `visits`, `pageviews`, `avg_time_on_page`, `entrances`/`pageviews` AS `entrance_rate`, `exits`/`pageviews` AS `exit_rate`
				FROM `post_stats`
				ORDER BY `mantime` DESC"
		);
		return $top_posts;
	}

	function format_user_info ($user_info) {
			$formatted_user_info = array();
			$formatted_user_info["post_author"] = get_userdata($user_info->post_author)->display_name;
			$formatted_user_info["mantime"] =  time_format($user_info->mantime);
			$formatted_user_info["visits"] =  number_format($user_info->visits);
			$formatted_user_info["pageviews"] =  number_format($user_info->pageviews);
			$formatted_user_info["avg_time_on_page"] =  time_format($user_info->avg_time_on_page);
			$formatted_user_info["entrance_rate"] =  round($user_info->entrance_rate*100, 2) . "%";
			$formatted_user_info["exit_rate"] =  round($user_info->exit_rate*100, 2) . "%";

			return $formatted_user_info;
	}

	function format_post_info ($post_info) {
			$formatted_post_info = array();
			$formatted_post_info["guid"] = $post_info->guid;
			$formatted_post_info["post_title"] = $post_info->post_title;
			$formatted_post_info["post_author"] = get_userdata($post_info->post_author)->display_name;
			$formatted_post_info["mantime"] = time_format($post_info->mantime);
			$formatted_post_info["visits"] = number_format($post_info->visits);
			$formatted_post_info["pageviews"] = number_format($post_info->pageviews);
			$formatted_post_info["avg_time_on_page"] = time_format($post_info->avg_time_on_page);
			$formatted_post_info["entrance_rate"] = round($post_info->entrance_rate*100, 2) . "%";
			$formatted_post_info["exit_rate"] = round($post_info->exit_rate*100, 2) . "%";

			return $formatted_post_info;
	}

	function get_post_author ($user_author_info) {
		$post_author = get_userdata($user_author_info);
		$author = array(
			"display_name" => $post_author->display_name,
			"name" => $post_author->name,
			"first_name" => $post_author->first_name,
			"last_name" => $post_author->last_name
		);
		return $author;
	}