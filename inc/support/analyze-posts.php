<?php

function sendgrid_sga_analyzeposts($posts = false) {

	global $wpdb, $debug_page;

	$sendgrid_sga_table = sendgrid_sga_get_table();
	$sendgrid_sga_client = sendgrid_sga_get_analytics_client();
	$access_token = get_option("sendgrid_sga_accesstoken");
	$profile_id = get_option("sendgrid_sga_profile");

	if(!($access_token && $profile_id)){
		return false;
	}

	if(!$sendgrid_sga_client){
		return false;
	}

	$sendgrid_sga_client->setAccessToken($access_token);
	$analytics = new Google_AnalyticsService($sendgrid_sga_client);

	if(!$posts){
		// Find all posts that have not been observed enough
		$posts = $wpdb->get_col( 
			$wpdb->prepare( 
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
						DATE(`posts`.`post_date`) <= %s AND
						(
							`stats`.`post_id` IS NULL OR
							`stats`.`post_id` = 0
						) AND
						`post_status` = 'publish' AND
						`post_type` = 'post'
					ORDER BY `posts`.`post_date` DESC",
				date("Y-m-d", strtotime("-8days"))
			)
		);
	}

	if ( $posts ) {
		$site_url = get_site_url();
		$prepend_url = get_option("sendgrid_sga_prepend_url"); 
		foreach ($posts as $post_id) {
			$post = get_post( $post_id );

			// Generate the post URL in the way Google Analytics will understand.
			$post_url = get_permalink($post_id);
			if(strpos($post_url, $site_url) === 0){
				$post_path = $prepend_url . substr($post_url, strlen($site_url));
			}

			if(SENDGRID_SGA_DEBUG_PAGE) {
				echo $post_path . "\r\n";
			}

			// Determine the dates to observe
			$post_publishing_date = substr($post->post_date_gmt,0,10);
			$post_observation_end = strtotime("+6days", strtotime($post->post_date_gmt));
			$final_observation = (time() > $post_observation_end);
			$last_observation_time =  !$final_observation ? time() : $post_observation_end;
			$last_observation_date = date("Y-m-d", $last_observation_time);

			if($post_publishing_date == '0000-00-00'){
				continue;
			}

			try {
				// Get GA Data for a post
				$response = $analytics->data_ga->get(
					"ga:" . $profile_id,
					$post_publishing_date,
					$last_observation_date,
					"ga:visitors,ga:pageviews,ga:avgTimeOnPage,ga:entrances,ga:exits",
					array(
						"dimensions" => "ga:pagePath",
						"filters" => "ga:pagePath==" . $post_path
					)
				);

				$ga_values = $response['totalsForAllResults'];
			} catch (Google_ServiceException $e){

			}

			if($ga_values){

				// Insert the data into WordPress

				$wpdb->replace( 
					$sendgrid_sga_table, 
					array( 
						"post_id" => $post_id,
						"visits" => $ga_values['ga:visitors'],
						"pageviews" => $ga_values['ga:pageviews'],
						"avg_time_on_page" => $ga_values['ga:avgTimeOnPage'],
						"entrances" => $ga_values['ga:entrances'],
						"exits" => $ga_values['ga:exits'],
						"done" => (INT)$final_observation
					), 
					array( 
						'%d',
						'%d',
						'%d',
						'%d',
						'%d',
						'%d',
						'%d',
					)
				);

			}
			
		}
	}

}

