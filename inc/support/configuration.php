<?php
	$authorize_url = $sendgrid_sga_client->createAuthUrl();
?>
<div id="configure">
	<div id="configuration-prompt" style="display: <?php echo $configured ? "block" : "none" ?>">
		<h2>Configuration</h2>
		<table class="form-table" style="clear: left; width: auto;">
			<tr valign="top">
				<th scope="row"><label for="configuration-button">Authorize</label></th>
				<td>
					<a href="#" class="button-primary" id="configuration-button">Configure</a>
					<label for="configuration-button" class="description">You've already configured this plugin, making changes here can be dangerous.</label>
				</td>
			</tr>
		</table>
		<h2>API</h2>
		<table class="form-table" style="clear: left; width: auto;">
			<tr valign="top">
				<th scope="row"><label for="apikey" id="apikeylabel">API Key</label></th>
				<td>
					<input name="apikey" type="text" id="apikey" aria-labelledby="apikeylabel" value="<?php echo get_option("sendgrid_sga_apikey") ?>" />
					<label for="apikey" class="description">Using this API Key you can query data from this plugin.</label>
				</td>
			</tr>
		</table>
	</div>
	<div id="configuration" style="display: <?php echo !$configured ? "block" : "none" ?>">
		<h3>Analytics Access</h3>
		<table class="form-table" style="clear: left; width: auto;">
			<tr valign="top">
				<th scope="row"><label for="loginwithgoogle">Authorize</label></th>
				<td>
					<a href="<?php echo $authorize_url; ?>" class="button-primary" id="loginwithgoogle" target="_blank">Authorize Google Analytics</a>
					<label for="loginwithgoogle" class="description">You must authorize Google Analytics for this plugin to do any good.</label>
				</td>
			</tr>
			<tr valign="top" id="authorization-token" style="<?php echo get_option("sendgrid_sga_authtoken") ? "" : "display:none" ?>;">
				<th scope="row"><label for="token" id="tokenlabel">Authorization Token</label></th>
				<td>
					<input name="token" type="password" id="token" aria-labelledby="tokenlabel" value="<?php echo get_option("sendgrid_sga_authtoken") ?>" />
					<label for="token" class="description">Place the authorization token provided by Google here.</label>
				</td>
			</tr>
		</table>

		<h3>Google Analytics Settings</h3>
		<table class="form-table" style="clear: left; width: auto;">
			<tr valign="top">
				<th scope="row"><label for="profileid" id="profileidlabel">Google Analytics Profile ID</label></th>
				<td>
					<input name="profileid" type="text" id="profileid" aria-labelledby="profileidlabel" value="<?php echo get_option("sendgrid_sga_profile") ?>" />
					<label for="profileid" class="description">Your Profile ID appears in the URL when using Google Analytics, <a href="http://productforums.google.com/d/msg/analytics/CIevQqWKElg/5NKOVPSekSwJ">find it using this Google Forum Post</a>.</label>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="prependurl" id="prependurllabel">Prepend URL</label></th>
				<td>
					<input name="prependurl" type="text" id="prependurl" aria-labelledby="prependurllabel" value="<?php echo get_option("sendgrid_sga_prepend_url") ?>" />
					<label for="prependurl" class="description">Most configurations of Google Analytics do not need this. However, yours may if you have URLs prepeneded with anything.</label>
				</td>
			</tr>
		</table>
	</div>
</div>
<script>
	jQuery(function ($) {
		$("#configuration-button").click(function (e) {
			e.preventDefault();
			$(this).closest("#configuration-prompt").hide();
			$("#configuration").show();
		});
		$("#loginwithgoogle").click(function (e) {
			e.preventDefault();
			window.open($(this).attr("href"), "loginwithgoogle", "width=620,height=400,resizable,scrollbars=yes,status=1");
			$("#authorization-token").show();
		});
	});
</script>