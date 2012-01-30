<?php 
global $client_status_options; 
?>
<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery('#install_type').change(function(){
			if(jQuery('#install_type option:selected').val() == <?php echo CLIENT_STATUS_INSTALL_TYPE_DASHBOARD; ?>){
				jQuery('.client_status_dashboard_only').show();
			} else {
				jQuery('.client_status_dashboard_only').hide();
			}
		}).trigger('change');

		jQuery('#allow_cron_updates').change(function(){
			if(jQuery('#allow_cron_updates').is(':checked')){
				jQuery('#update_frequency').removeAttr('disabled');
			} else {
				jQuery('#update_frequency').attr('disabled', 'disabled');
			}
		}).trigger('change');
	});
</script>

<div class="wrap">
	<h2><?php _e('Client Status Options')?></h2>
	
	<?php 
	$action = "";
	if(isset($_GET['action'])){
		$action = $_GET['action'];
	}
	
	switch($action){
		case "update":
			if(isset($_POST['security_key'])){
				$client_status_options['security_key'] = $_POST['security_key'];
			}
			if(isset($_POST['install_type'])){
				$client_status_options['install_type'] = $_POST['install_type'];
			}
			if($client_status_options['install_type'] == CLIENT_STATUS_INSTALL_TYPE_DASHBOARD){
				if(isset($_POST['update_frequency'])){
					$client_status_options['update_frequency'] = $_POST['update_frequency'];
					
					// Clear cron job and reschedule
					wp_clear_scheduled_hook('client_status_update_all_clients_action');
					$client_status_options['update_scheduled'] = 0;
				}
				
				if(isset($_POST['allow_cron_updates'])){
					$client_status_options['allow_cron_updates'] = $_POST['allow_cron_updates'];
				} else {
					$client_status_options['allow_cron_updates'] = 0;
					
					// Remove cron job
					wp_clear_scheduled_hook('client_status_update_all_clients_action');
					$client_status_options['update_scheduled'] = 0;
				}
				
				$client_status_options['admin_emails'] = array();
				if(isset($_POST['admin_emails'])){					
					$client_status_options['admin_emails'] = $_POST['admin_emails'];
				}
				
				if(isset($_POST['expand_client_info'])){
					$client_status_options['expand_client_info'] = $_POST['expand_client_info'];
				} else {
					$client_status_options['expand_client_info'] = 0;
				}
				
				if(isset($_POST['show_quick_info'])){
					$client_status_options['show_quick_info'] = $_POST['show_quick_info'];
				} else {
					$client_status_options['show_quick_info'] = 0;
				}
			}
			update_option('client_status_options', $client_status_options);
			
	?>
		<script>
			window.location="options-general.php?page=client-status-options&updated=true&updatedmsg=<?php echo urlencode(__('Settings Saved')); ?>";
		</script>
	<?php
			break;
			
		default:
	?>
		<form method="post" action="options-general.php?page=client-status-options&action=update">
		<?php wp_nonce_field('update-options'); ?>
		<table class="form-table">
		<tr valign="top">
			<th scope="row">
				<strong><?php _e('Client or Dashboard?'); ?></strong>
				<em><?php _e('Setting to dashboard will allow you to pull data from client sites.'); ?></em>
			</th>
			<td>
				<select id="install_type" name="install_type">
					<option value="<?php echo CLIENT_STATUS_INSTALL_TYPE_CLIENT; ?>"<?php echo (($client_status_options['install_type'] == CLIENT_STATUS_INSTALL_TYPE_CLIENT) ? ' selected="selected"' : '')?>><?php _e("Client"); ?></option>
					<option value="<?php echo CLIENT_STATUS_INSTALL_TYPE_DASHBOARD; ?>"<?php echo (($client_status_options['install_type'] == CLIENT_STATUS_INSTALL_TYPE_DASHBOARD) ? ' selected="selected"' : '')?>><?php _e("Dashboard"); ?></option>
				</select>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<strong><?php _e('Security Key'); ?></strong><br />
				<em class="client_status_dashboard_only"><?php _e('This key is needed for your client sites'); ?></em>
			</th>
			<td>
				<input <?php echo (($client_status_options['install_type'] == CLIENT_STATUS_INSTALL_TYPE_DASHBOARD) ? 'type="text"' : 'type="password"'); ?> name="security_key" id="security_key" value="<?php echo $client_status_options['security_key']; ?>" />
			</td>
		</tr>
		<tr valign="top" class="client_status_dashboard_only">
			<th scope="row">
				<strong><?php _e('Client Update Frequency'); ?></strong><br />
				<em><?php _e('Uses WP-Cron'); ?></em>
			</th>
			<td>
				<input type="checkbox" name="allow_cron_updates" id="allow_cron_updates" value="1"<?php echo (($client_status_options['allow_cron_updates'] == 1) ? ' checked="checked"' : '')?> /><label for="allow_cron_updates"><?php _e('Allow Client Updates'); ?></label>
				<select name="update_frequency" id="update_frequency">
					<option value="<?php echo CLIENT_STATUS_CRON_HOURLY; ?>"<?php echo (($client_status_options['update_frequency'] == CLIENT_STATUS_CRON_HOURLY) ? ' selected="selected"' : '')?>><?php _e('Hourly'); ?></option>
					<option value="<?php echo CLIENT_STATUS_CRON_TWICE_DAILY; ?>"<?php echo (($client_status_options['update_frequency'] == CLIENT_STATUS_CRON_TWICE_DAILY) ? ' selected="selected"' : '')?>><?php _e('Twice Daily'); ?></option>
					<option value="<?php echo CLIENT_STATUS_CRON_DAILY; ?>"<?php echo (($client_status_options['update_frequency'] == CLIENT_STATUS_CRON_DAILY) ? ' selected="selected"' : '')?>><?php _e('Daily'); ?></option>
				</select>
			</td>
		</tr>
		<tr valign="top" class="client_status_dashboard_only">
			<th scope="row">
				<strong><?php _e('Email Notifications'); ?></strong><br />
				<em><?php _e('Which administrators should receive status emails? Selecting none will disable.')?></em>
			</th>
			<td>
				<select name="admin_emails[]" multiple="multiple" size="5" style="height: 80px;">
					<?php 
						global $wpdb, $blog_id;
						if ( empty($id) )
							$id = (int) $blog_id;
						$blog_prefix = $wpdb->get_blog_prefix($id);
						$users = $wpdb->get_results( "SELECT user_id, user_id AS ID, user_login, display_name, user_email, meta_value FROM $wpdb->users, $wpdb->usermeta WHERE {$wpdb->users}.ID = {$wpdb->usermeta}.user_id AND meta_key = '{$blog_prefix}capabilities' ORDER BY {$wpdb->users}.display_name" );
						
						foreach($users as $user){
							$user_details = get_user_by('login', $user->user_login);
							if($user_details->user_level == 10){
					?>
					<option value="<?php echo $user->ID; ?>"<?php echo ((in_array($user->ID, $client_status_options['admin_emails'])) ? ' selected="selected"': ''); ?>><?php echo $user->display_name; ?></option>
					<?php
							}
						}
					?>
				</select>
			</td>
		</tr>
		<tr valign="top" class="client_status_dashboard_only">
			<th scope="row">
				<strong><?php _e('Expand Client Details'); ?></strong><br />
				<em><?php _e('On the dashboard, should all the client details be expanded or collapsed?')?></em>
			</th>
			<td>
				<select name="expand_client_info" id="expand_client_info">
					<option value="1"<?php echo (($client_status_options['expand_client_info'] == 1) ? ' selected="selected"' : '')?>><?php _e('Yes'); ?></option>
					<option value="0"<?php echo (($client_status_options['expand_client_info'] == 0) ? ' selected="selected"' : '')?>><?php _e('No'); ?></option>
				</select>
			</td>
		</tr>
		<tr valign="top" class="client_status_dashboard_only">
			<th scope="row">
				<strong><?php _e('Show Client Quick Info'); ?></strong><br />
				<em><?php _e('On the dashboard, for each client, should we show quick information about the client\'s site? This seems most useful if <strong>Expand Client Details</strong> is set to <strong>No</strong>.')?></em>
			</th>
			<td>
				<select name="show_quick_info" id="show_quick_info">
					<option value="1"<?php echo (($client_status_options['show_quick_info'] == 1) ? ' selected="selected"' : '')?>><?php _e('Yes'); ?></option>
					<option value="0"<?php echo (($client_status_options['show_quick_info'] == 0) ? ' selected="selected"' : '')?>><?php _e('No'); ?></option>
				</select>
			</td>
		</tr>
		</table>
		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="install_type,security_key,update_frequency,allow_cron_updates,admin_emails[]" />
		<?php settings_fields('client_status_group'); ?>
		<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>" /></p>
		</form>
	<?php	
			break;
	}
	?>
</div>