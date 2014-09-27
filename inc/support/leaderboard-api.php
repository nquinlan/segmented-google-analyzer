<?php

require_once(SENDGRID_SGA_PATH . "inc/support/leaderboard.php");
function sendgrid_sga_data_api () {
	$period_start = create_start_time($_POST['period_start'], true);
	$period_end =  create_end_time($_POST['period_end'], true);

	create_table($period_start, $period_end);

	header("Content-Type: application/json");

	$output =  array("error" => true, "message" => "Unknown error.");
	$body = array();

	if($_POST['data'] == "users") {
		$top_users = get_top_users();
		
		foreach ($top_users as $rank => $user_info) {
			$formatted_user_info = format_user_info($user_info);
			$body[] = array(
				"raw" => array(
					"post_author" => get_post_author($user_info->post_author),
					"mantime" =>  (int)$user_info->mantime,
					"visits" =>  (int)$user_info->visits,
					"pageviews" =>  (int)$user_info->pageviews,
					"avg_time_on_page" =>  (float)$user_info->avg_time_on_page,
					"entrance_rate" =>  (float)$user_info->entrance_rate,
					"exit_rate" => (float)$user_info->exit_rate
				),
				"formatted" => $formatted_user_info
			);
		}

		$output =  array("error" => false, "body" => $body, "period_start" => $period_start, "period_end" => $period_end);
	}elseif($_POST['data'] == "posts") {
		$top_posts = get_top_posts();

		foreach ($top_posts as $rank => $post_info) {
			$formatted_post_info = format_post_info($post_info);
			$body[] = array(
				"raw" => array(
					"guid" => $post_info->guid,
					"post_title" => $post_info->post_title,
					"post_author" => get_post_author($post_info->post_author),
					"mantime" =>  (int)$post_info->mantime,
					"visits" =>  (int)$post_info->visits,
					"pageviews" =>  (int)$post_info->pageviews,
					"avg_time_on_page" =>  (float)$post_info->avg_time_on_page,
					"entrance_rate" =>  (float)$post_info->entrance_rate,
					"exit_rate" => (float)$post_info->exit_rate
				),
				"formatted" => $formatted_post_info
			);
		}

		$output =  array("error" => false, "body" => $body, "period_start" => $period_start, "period_end" => $period_end);
	}else{
		$output = array("error" => true, "message" => "Data attribute not understood, please provide either users or posts");
	}
	
	echo json_encode( $output );

	die();
}