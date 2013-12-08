<?php
	global $wpdb, $wp_roles;
?>
<div class="wrap">
<!--
Hi There <?php 
global $current_user;
echo $current_user->display_name;
?>!
You're reading the code, that means I think you're pretty awesome. <?php /* Especially if you're reading the PHP code. */ ?>
This plugin queries Google Analytics 
If you have a better way of doing this or anything else, or want to talk WordPress, PHP, email delivery, or similarly nerdy things drop me an email: <nick@sendgrid.com>.
Enjoy The Plugin!
--
Nick of SendGrid
-->
	<div id="icon-options-general" class="icon32"><br /></div><h2>Segmented Google Analyzer Settings</h2>
	<?php
		if(isset($_POST['submit']) && $_POST['submit'] == "Save Changes" && wp_verify_nonce($_POST['sendgrid_sga_nonce'], plugin_basename( __FILE__ ))){

			if(isset($_POST['token']) && $_POST['token'] != get_option("sendgrid_sga_authtoken")){
				$auth_token = $_POST['token'];
				try {
				    $access_token = $sendgrid_sga_client->authenticate($auth_token);
				} catch( Exception $e ) {
				    echo '<div id="setting-error" class="error settings-error"><p><strong>The access token provided did not work, please try re-authenticating.</strong></p></div>';
				}

				if($access_token) {
				    $sendgrid_sga_client->setAccessToken($access_token);
				    update_option('sendgrid_sga_authtoken', $auth_token);
				    update_option('sendgrid_sga_accesstoken', $access_token);
				}
			}

			update_option('sendgrid_sga_profile', $_POST['profileid']);

			

			echo '<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>Settings saved.</strong></p></div>';
		}
		$properties = get_option("sendgrid_sga_properties");
	?>
	<form method="POST">
		<?php
			if(!(get_option("sendgrid_sga_accesstoken") && get_option("sendgrid_sga_profile"))) {
				$configured = false;
			}

			if(get_option("sendgrid_sga_accesstoken") && get_option("sendgrid_sga_profile")) {
				$configured = true;
			}

			include(SENDGRID_SGA_PATH . "inc/support/configuration.php");
		?>
		<?php wp_nonce_field( plugin_basename( __FILE__ ), 'sendgrid_sga_nonce'); ?>
		<p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="Save Changes"	/></p>
	</form>
</div>
<script>
jQuery(function ($){
	// TODO CSS HIDE ONLY/ONLY
	$("#show-advanced").click(function () {
		$("body").addClass("gau-advanced");
		$(".gau-advanced-only").show();
		repeater( "#properties", ".property-group" );
	});
});

</script>