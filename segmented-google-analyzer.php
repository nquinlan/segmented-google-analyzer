<?php
/*
Plugin Name: Segmented Google Analyzer
Plugin URI: http://wordpress.org/extend/plugins/segmented-google-analyzer
Description: Segment Google Analytics to create things like leaderboards.
Author: nquinlan
Author URI: http://nicholasquinlan.com/
Version: 0.0.1
Text Domain: segmented-google-analyzer
License: MIT
*/

$sendgrid_sga_version = "0.0.1";

define( 'SENDGRID_SGA_PATH', plugin_dir_path(__FILE__) );
define( 'SENDGRID_SGA_FILE', __FILE__);

// DEFINE GOOGLE ANALYTICS VALUES
define( 'SENDGRID_SGA_GOOGLE_CLIENTID', '913231705346-u04d98uju3rv3ghpchc8l860kv7a9hb0.apps.googleusercontent.com');
define( 'SENDGRID_SGA_GOOGLE_CLIENTSECRET', 'zgcraNtRK1Lvpo6RYoE4kjUA');
define( 'SENDGRID_SGA_GOOGLE_REDIRECTURI', 'urn:ietf:wg:oauth:2.0:oob' );
define( 'SENDGRID_SGA_GOOGLE_SCOPE', 'https://www.googleapis.com/auth/analytics.readonly');

// LOAD GOOGLE LIBRARIES
require_once(SENDGRID_SGA_PATH . "inc/lib/google/Google_Client.php");
require_once(SENDGRID_SGA_PATH . "inc/lib/google/contrib/Google_AnalyticsService.php");

// CREATE GOOGLE ANALYTICS CLIENT

function sendgrid_sga_get_analytics_client () {
	$sendgrid_sga_client = new Google_Client();
	$sendgrid_sga_client->setApplicationName("Segmented Google Analyzer");
	$sendgrid_sga_client->setClientId(SENDGRID_SGA_GOOGLE_CLIENTID);
	$sendgrid_sga_client->setClientSecret(SENDGRID_SGA_GOOGLE_CLIENTSECRET);
	$sendgrid_sga_client->setRedirectUri(SENDGRID_SGA_GOOGLE_REDIRECTURI);
	$sendgrid_sga_client->setScopes(array(SENDGRID_SGA_GOOGLE_SCOPE));
	return $sendgrid_sga_client;
}


// SET DEFAULTS

function sendgrid_sga_get_table () {
	global $wpdb;
	return $wpdb->prefix . "post_sgastats";
}

register_activation_hook( __FILE__, 'sendgrid_sga_activate' );
function sendgrid_sga_activate() {
	$sendgrid_sga_table = sendgrid_sga_get_table();

	if(!get_option("sendgrid_sga_activated")){
		update_option("sendgrid_sga_activated", true);
		update_option("sendgrid_sga_debug", false);
	}

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	$sql = "CREATE TABLE $sendgrid_sga_table (
		stats_id BIGINT NOT NULL AUTO_INCREMENT,
		post_id BIGINT NOT NULL,
		done TINYINT(1) DEFAULT 0 NOT NULL,
		visits INT(9) DEFAULT 0 NOT NULL,
		visitors INT(9) DEFAULT 0 NOT NULL,
		pageviews INT(9) DEFAULT 0 NOT NULL,
		avg_time_on_page SMALLINT(5) DEFAULT 0 NOT NULL,
		entrances INT(9) DEFAULT 0 NOT NULL,
		exits INT(9) DEFAULT 0 NOT NULL,
		last_update datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
		PRIMARY KEY  (stats_id),
		UNIQUE KEY (post_id)
	);";
	dbDelta( $sql );

	sendgrid_sga_analyzeposts();

}

register_activation_hook( __FILE__, 'sendgrid_sga_deactivate' );
function sendgrid_sga_deactivate() {
	wp_clear_scheduled_hook( 'sendgrid_sga_analyzeposts_hook' );
}

// PAGE REGISTRATION

function sendgrid_sga_page ($pagename, $can = 'activate_plugins'){

	if (!current_user_can($can))	{
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}

	global $wpdb;

	$sendgrid_sga_table = sendgrid_sga_get_table();
	$sendgrid_sga_client = sendgrid_sga_get_analytics_client();

	include(SENDGRID_SGA_PATH . "inc/screens/$pagename.php");

}


function sendgrid_sga_settings_page() {
	sendgrid_sga_page('settings');
}

function sendgrid_sga_debug_page() {
	sendgrid_sga_page('debug');
}

function sendgrid_sga_leaderboard_page() {
	sendgrid_sga_page('leaderboard', 'edit_posts');
}

function sendgrid_sga_leaderboard_scripts($hook) {
	wp_enqueue_style( 'sendgrid_sga-fontello', plugin_dir_url( __FILE__ ) . '/css/sendgrid_sga-icon.css', array(), '1.0.0' );
	wp_enqueue_style( 'sendgrid_sga', plugin_dir_url( __FILE__ ) . '/css/style.css', array('sendgrid_sga-fontello'), '1.0.0' );

    if( $hook != 'toplevel_page_segmented-google-analyzer')
        return;

    wp_enqueue_script( 'imagesloaded', plugin_dir_url( __FILE__ ) . '/js/vendor/imagesloaded.min.js', array('jquery'), '3.0.2' );
    wp_enqueue_script( 'qtip', plugin_dir_url( __FILE__ ) . '/js/vendor/qtip.jquery.min.js', array('jquery', 'imagesloaded'), '2.1.1' );
    wp_enqueue_style( 'qtip', plugin_dir_url( __FILE__ ) . '/css/vendor/qtip.min.css', array(), '2.1.1' );
    
    wp_enqueue_script( 'moment', plugin_dir_url( __FILE__ ) . '/js/vendor/moment.min.js', array(), '2.4.0' );
    wp_enqueue_script( 'pikaday', plugin_dir_url( __FILE__ ) . '/js/vendor/pikaday.min.js', array('moment'), '1.2.0' );
    wp_enqueue_style( 'pikaday', plugin_dir_url( __FILE__ ) . '/css/vendor/pikaday.min.css', array(), '1.2.0' );

    wp_enqueue_script( 'filesaver', plugin_dir_url( __FILE__ ) . '/js/vendor/FileSaver.min.js', array(), '1.0.0' );
    wp_enqueue_script( 'table2CSV', plugin_dir_url( __FILE__ ) . '/js/vendor/table2CSV.min.js', array(), '1.0.0' );
    wp_enqueue_script( 'tablesorter', plugin_dir_url( __FILE__ ) . '/js/vendor/jquery.tablesorter.min.js', array('jquery'), '1.0.0' );
}
add_action( 'admin_enqueue_scripts', 'sendgrid_sga_leaderboard_scripts' );

// Create the Menu For Pages

add_action('admin_menu', 'sendgrid_sga_create_menu');
function sendgrid_sga_create_menu() {
	//create new top-level menu
	$leaderboard_page = add_menu_page('Google Analyzer Leaderboard', 'Google Analyzer', 'edit_posts', 'segmented-google-analyzer', 'sendgrid_sga_leaderboard_page', plugins_url('img/icon.png', __FILE__) );
	$settings_page = add_submenu_page('segmented-google-analyzer', 'Google Analyzer > Settings', 'Settings', 'activate_plugins', 'segmented-google-analyzer/settings', 'sendgrid_sga_settings_page');
	if(WP_DEBUG){
		$debug_page = add_submenu_page('segmented-google-analyzer', 'Google Analyzer > Debug', 'Debug', 'activate_plugins', 'segmented-google-analyzer/debug', 'sendgrid_sga_debug_page');
	}	
}

// MEAT OF THE PLUGIN

// Every day, check for week old posts
add_action( 'wp', 'sendgrid_sga_schedule' );
function sendgrid_sga_schedule () {
	if ( ! wp_next_scheduled( 'sendgrid_sga_analyzeposts_hook' ) ) {
	  wp_schedule_event( time(), 'daily', 'sendgrid_sga_analyzeposts_hook' );
	}
}

add_action( 'sendgrid_sga_analyzeposts_hook', 'sendgrid_sga_analyzeposts' );

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


