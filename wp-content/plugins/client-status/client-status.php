<?php
/*
Plugin Name: Client Status
Plugin URI: http://judenware.com/projects/wordpress/client-status/
Description: Status dashboard to keep track of updates for your clients' WordPress sites. Allows sending email to administrators and also to clients when new updates are needed.
Author: ericjuden
Version: 1.3.3
Author URI: http://www.judenware.com
Site Wide Only: false
Network: false
*/

define('CLIENT_STATUS_VERSION', '1.3.3');

$client_status_options = get_option('client_status_options');
if(!is_array($client_status_options)){
	$client_status_options = array();
}

require_once(ABSPATH . 'wp-includes/pluggable.php');
require_once('constants.php');

add_action('admin_init', 'client_status_admin_init');
add_action('admin_menu', 'client_status_admin_menu');
add_action('admin_print_scripts', 'client_status_options_js_init');
add_action('admin_print_styles', 'client_status_admin_styles');
add_action('client_status_update_all_clients_action', 'client_status_update_all_clients');
add_action('init', 'client_status_setupDB');
add_action('manage_client_status_client_posts_custom_column', 'client_status_custom_client_columns');
add_action('right_now_content_table_end', 'client_status_right_now_content_table_end');
add_action('save_post', 'client_status_metabox_client_info_save');
add_filter('manage_edit-client_status_client_columns', 'client_status_edit_client_columns');
add_filter('wp_mail_from_name', 'client_status_fix_from_name');
register_deactivation_hook(__FILE__, 'client_status_deactivate');
register_uninstall_hook(__FILE__, 'client_status_uninstall');

if($client_status_options['install_type'] == CLIENT_STATUS_INSTALL_TYPE_DASHBOARD && $client_status_options['allow_cron_updates'] > 0){
	if(array_key_exists('update_scheduled', $client_status_options) && $client_status_options['update_scheduled'] == 0){
		wp_schedule_event(time(), $client_status_options['update_frequency'], 'client_status_update_all_clients_action');
		$client_status_options['update_scheduled'] = 1;
		update_option('client_status_options', $client_status_options);
	}
}

function client_status_admin_init(){
	global $client_status_options;
	
	register_setting('client_status_group', 'client_status_options');
	
	if(!array_key_exists('install_type', $client_status_options)){
		$client_status_options['install_type'] = 1;
	}
	
	if(!array_key_exists('update_frequency', $client_status_options)){
		$client_status_options['update_frequency'] = "hourly";
	}
	
	if(!array_key_exists('allow_cron_updates', $client_status_options)){
		$client_status_options['allow_cron_updates'] = 1;
	}
	
	if(!array_key_exists('update_scheduled', $client_status_options)){
		$client_status_options['update_scheduled'] = 0;
	}
	
	if(!array_key_exists('admin_emails', $client_status_options)){
		$client_status_options['admin_emails'] = array();
	}
	
	if(!array_key_exists('expand_client_info', $client_status_options)){
		$client_status_options['expand_client_info'] = 0;
	}
	
	if(!array_key_exists('show_quick_info', $client_status_options)){
		$client_status_options['show_quick_info'] = 1;
	}
	
	update_option('client_status_options', $client_status_options);
	
	wp_register_style('client_status_stylesheet', CLIENT_STATUS_PLUGIN_URL . '/style.css');
	
	load_plugin_textdomain('client-status', false, CLIENT_STATUS_PLUGIN_DIR . '/languages');
}

function client_status_admin_menu(){
	global $client_status_options;
	
	add_options_page('Client Status Options', 'Client Status', 10, 'client-status-options', 'client_status_plugin_options');
	if($client_status_options['install_type'] == CLIENT_STATUS_INSTALL_TYPE_DASHBOARD){
		add_dashboard_page('Client Status', 'Client Status', 10, 'client-status-dashboard', 'client_status_plugin_dashboard');
		remove_meta_box('postcustom', 'client_status_client', 'normal');
	}
}

function client_status_admin_styles(){
	wp_enqueue_style('client_status_stylesheet');
}

// What data is displayed on edit.php page...for new columns
function client_status_custom_client_columns($column){
	global $post;
	switch($column){
		case 'client_type':
			echo get_the_term_list($post->ID, 'client_status_client_type', '', ', ', '');
			break;
	}
}

// Set the columns to show on the Client edit.php page
function client_status_edit_client_columns($columns){
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => 'Title',
		'client_type' => _('Client Type'),
		'date' => _x('Date', 'column name'),
	);
	
	return $columns;
}

function client_status_deactivate(){
	wp_clear_scheduled_hook('client_status_update_all_clients_action');
}

function client_status_fix_from_name($name){
	$name = get_bloginfo('blogname');
	return $name;
}

function client_status_metabox_client(){
	add_meta_box('client_status_client', __('Client Information'), 'client_status_metabox_client_info', 'client_status_client', 'normal', 'high');
}

function client_status_metabox_client_info(){
	global $post, $client_status_options;
	$custom = get_post_custom($post->ID);
?>
	<div id="client_meta_info">
		<label for="client_url"><strong><?php _e('Site URL'); ?></strong></label><br />
		<input type="url" name="client_url" id="client_url" value="<?php echo ((!empty($custom['_client_url'])) ? $custom['_client_url'][0] : ""); ?>" size="50" /><br />
		<br />
		<label for="client_email"><strong><?php _e('Client Email'); ?></strong></label><br />
		<em><?php _e('Allows sending status emails to client; <br />separate multiple emails with a comma')?></em><br />
		<textarea name="client_email" id="client_email" cols="40" rows="5"><?php echo ((!empty($custom['_client_email'])) ? $custom['_client_email'][0] : ""); ?></textarea>
	</div>
	
	<div id="client_meta_status">
<?php
	if(!empty($custom['_client_url']) && $custom['_client_url'][0] != ""){
		// Try retrieving information from server
		$ret = wp_remote_post($custom['_client_url'][0] . CLIENT_STATUS_DATA_URL . "?security_key=" . md5($client_status_options['security_key']));
		$data = simplexml_load_string($ret['body']);
		
		client_status_update_client($post->ID, &$custom, &$ret, time());
		
		if(!empty($data)){
			if(!property_exists($data, 'error')){
				$plugin_update_count = count($data->updates->plugins->plugin);
				$theme_update_count = count($data->updates->themes->theme);
				echo "<p>" . (($data->is_public == 0) ? "<img class='status' src='" . CLIENT_STATUS_IMAGE_ERROR . "' />" : "<img class='status' src='" . CLIENT_STATUS_IMAGE_OK . "' />") . _('Status: ') . (($data->is_public == 0) ? "<a class='status_error' href='". $custom['_client_url'][0] . CLIENT_STATUS_WP_PRIVACY_URL ."' target='_blank' title='". _('Adjust privacy settings') ."' alt='". _('Adjust privacy settings') ."'>" . _('Not Indexable') . "</a>" : "<a class='status_ok' href='". $custom['_client_url'][0] . CLIENT_STATUS_WP_PRIVACY_URL ."' target='_blank' title='". _('Adjust privacy settings') ."' alt='". _('Adjust privacy settings') ."'>" . _('Indexable') . "</a>") . "</p>"; 
				echo "<p>" . (($data->updates->core->response == "upgrade") ? "<img class='status' src='" . CLIENT_STATUS_IMAGE_ERROR . "' />" : "<img class='status' src='" . CLIENT_STATUS_IMAGE_OK . "' />") . _('WordPress Version: ') . "<a class='". (($data->updates->core->response == "upgrade") ? "status_error" : "status_ok") ."' href='". $custom['_client_url'][0] . CLIENT_STATUS_WP_UPDATE_CORE_URL ."' target='_blank'>" . $data->version . "</a></p>"; 
				echo "<p>" . (($plugin_update_count > 0) ? "<img class='status' src='" . CLIENT_STATUS_IMAGE_PLUGIN_ERROR . "' />" : "<img class='status' src='" . CLIENT_STATUS_IMAGE_OK . "' />") . _('Plugin Updates: ') . "<a class='". (($plugin_update_count > 0) ? "status_error" : "status_ok") ."' href='". $custom['_client_url'][0] . CLIENT_STATUS_WP_UPDATE_PLUGINS_URL ."' target='_blank'>" .  $plugin_update_count . "</a></p>"; 
				echo "<p>" . (($theme_update_count > 0) ? "<img class='status' src='" . CLIENT_STATUS_IMAGE_THEME_PROBLEM . "' />" : "<img class='status' src='" . CLIENT_STATUS_IMAGE_OK . "' />") . _('Theme Updates: ') . "<a class='". (($theme_update_count > 0) ? "status_error" : "status_ok") ."' href='". $custom['_client_url'][0] . CLIENT_STATUS_WP_UPDATE_THEMES_URL ."' target='_blank'>" .  $theme_update_count . "</a></p>";
			} else {
				echo "<p><img class='status' src='". CLIENT_STATUS_IMAGE_PROBLEM ."' /><a href='". $custom['_client_url'][0] . CLIENT_STATUS_SETTINGS_URL ."' class='status_problem' target='_blank'>" . (($data->error != '') ? $data->error : _('An error has occurred')) . "</a></p>";
			}
		}
		
		if(property_exists($data, 'plugin_version') && $data->plugin_version > 1.1){
			echo "<p>" . _('Server OS: ') . $data->os_version . "</p>";
			echo "<p>" . _('PHP Version: ') . $data->php_version . "</p>";
			echo "<p>" . _('MySQL Version: ') . $data->mysql_version . "</p>";
		}
	}
?>
	</div>
	<div class="clear"></div>
<?php
}

function client_status_metabox_client_info_save(){
	global $post;
	if($post->post_type == 'client_status_client'){
		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
    		return;
		}
		
		// Check for trailing slash...add if missing
		$client_url = $_POST['client_url'];
		if($client_url != ""){
			$last_char = substr($client_url, strlen($client_url) -1, 1);
			if($last_char != "/"){
				$client_url .= "/";
			}
		}
		$client_emails = $_POST['client_email'];
		if($client_emails != ""){
			$client_emails = str_replace("\r\n", ",", $client_emails);
		}
		update_post_meta($post->ID, "_client_url", $client_url);
		update_post_meta($post->ID, "_client_email", $client_emails);
	}
}

function client_status_options_js_init(){
	wp_enqueue_script('jquery');
}

function client_status_plugin_dashboard(){
	require_once 'dashboard.php';
}

function client_status_plugin_options(){
	require_once 'client-status-options.php';
}

function client_status_right_now_content_table_end(){
	global $client_status_options;
	if (is_admin() && $client_status_options['install_type'] == CLIENT_STATUS_INSTALL_TYPE_DASHBOARD) {
		$num_clients = wp_count_posts('client_status_client');
		$text = _n(_('Client'), _('Clients'), $num_clients->publish);
		
		echo "<tr>";
        $num = "<a href='edit.php?post_type=client_status_client'>$num_clients->publish</a>";
        $text = "<a href='index.php?page=client-status-dashboard'>$text</a>";
        
        echo '<td class="first b">' . $num . '</td>';
	    echo '<td class="t">' . $text . '</td>';
	    echo '</tr>';
    }
}

function client_status_send_status_email($toEmails, $data, $client_url){
	global $client_status_options;
	
	$headers = "From: ". get_bloginfo('admin_email') ."\r\n";
	$headers .= "Content-Type: text/html\r\n";
	$subject = "New Website Updates available";
	$message = "<html><body>";
	$strikes = 0;
	
	if(!empty($data)){
		if($data->updates->core->response == "upgrade") { $strikes++; }
		if($plugin_update_count > 0) { $strikes++; }
		if($theme_update_count > 0) { $strikes++; }
		
		if($strikes > 0){
			$message .= "<p>" . _('Your website has updates available:') . "</p>";
			if(!property_exists($data, 'error')){
				$message .= "<ul>";
				if($data->is_public == 0){
					$message .= "<li><a href='". $client_url . CLIENT_STATUS_WP_PRIVACY_URL ."' target='_blank'>" . _('Not indexable') . "</a></li>"; 
				}
				if($data->updates->core->response == "upgrade"){
					$message .= "<li><a href='". $client_url . CLIENT_STATUS_WP_UPDATE_CORE_URL ."' target='_blank'>" . _('New version of WordPress available') . "</a></li>";
				}
				$plugin_update_count = count($data->updates->plugins->plugin);
				if($plugin_update_count > 0){
					$message .= "<li><a href='". $client_url . CLIENT_STATUS_WP_UPDATE_PLUGINS_URL ."' target='_blank'>" . _('New plugin updates available') . "</a>";
					$message .= "<ul>";
					foreach($data->updates->plugins->plugin as $plugin){
						$message .= "<li><strong>" . $plugin->name . "</strong> - " . _('You have version ') . $plugin->current_version . _(' installed.') . _(' Update to ') . $plugin->new_version . ".</li>";
					}
					$message .= "</ul></li>";
				}
				$theme_update_count = count($data->updates->themes->theme);
				if($theme_update_count > 0){
					$message .= "<li><a href='". $client_url . CLIENT_STATUS_WP_UPDATE_THEMES_URL ."' target='_blank'>" . _('New theme updates available') . "</a>";
					$message .= "<ul>";
					foreach($data->updates->themes->theme as $theme){
						$message .= "<li><strong>" . $theme->name . "</strong> - " . _('You have version ') . $theme->current_version . _(' installed.') . _(' Update to ') . $theme->new_version . ".</li>";
					}
					$message .= "</ul></li>";
				}
				$message .= "</ul>";
			} else {
				$message .= "<p><a href='". $client_url . CLIENT_STATUS_SETTINGS_URL ."' target='_blank'>" . (($data->error != '') ? $data->error : _('An error has occurred')) . "</a></p>";
			}
		}
	}
	$message .= "</body></html>";
	if($strikes > 0){
		return wp_mail($toEmails, $subject, $message, $headers);
	}
	return false;
}

function client_status_setupDB() {
	global $client_status_options;
	
	if($client_status_options['install_type'] == CLIENT_STATUS_INSTALL_TYPE_DASHBOARD){
		register_post_type(
			'client_status_client',
			array(
				'capability_type' => 'post', 
				'exclude_from_search' => false,
				'hierarchical' => false,
				'labels' => array(
					'name' => __('Clients'),
					'singular_name' => __('Client'),
					'add_new' => __('Add New'),
					'add_new_item' => __('Add New Client'),
					'edit' => __('Edit'),
					'edit_item' => __('Edit Client'),
					'new_item' => __('New Client'),
					'view' => __('View'),
					'view_item' => __('View Client'),
					'search_items' => __('Search Clients'),
					'not_found' => __('No clients found'),
					'not_found_in_trash' => __('No clients found in Trash'),
				),
				'menu_icon' => CLIENT_STATUS_PLUGIN_URL . '/images/status_online.png',
				'public' => false,
				'publicly_queryable' => true,
				'query_var' => true,
				'register_meta_box_cb' => 'client_status_metabox_client',
				'rewrite' => array('slug' => 'client', 'with_front' => false),
				'show_ui' => true,
				'supports' => array('title','custom-fields'),
			) 
		);
		
		register_taxonomy(
			'client_status_client_type',
			array('client_status_client'),
			array(
				'public' => true,
				'hierarchical' => true,
				'labels' => array(
					'name' => __('Client Types'),
					'singular_name' => __('Client Type'),
					'add_new' => __('Add New'),
					'add_new_item' => __('Add New Client Type'),
                    'all_items' => __('All Client Types'),
					'edit' => __('Edit'),
					'edit_item' => __('Edit Client Type'),
					'new_item' => __('New Client Type'),
					'view' => __('View'),
					'view_item' => __('View Client Type'),
					'search_items' => __('Search Client Types'),
					'not_found' => __('No client types found'),
					'not_found_in_trash' => __('No client types found in Trash'),
				),
				'rewrite' => array('slug' => 'client-type'),
			)
		);
	}
}

function client_status_uninstall(){
    delete_option('client_status_options');
}

function client_status_update_all_clients(){
	global $post, $client_status_options, $wpdb, $blog_id, $current_user;
	
	query_posts(array('post_type' => 'client_status_client', 'orderby' => 'title', 'order' => 'ASC', 'posts_per_page' => -1));
	if(have_posts()){
		while(have_posts()){
			the_post();
			$custom = get_post_custom($post->ID);
	
			$client_url = $custom['_client_url'][0];
			if($client_url != ""){
				$ret = wp_remote_post($client_url . CLIENT_STATUS_DATA_URL . "?security_key=" . md5($client_status_options['security_key']));
				$updated = time();
				
				client_status_update_client($post->ID, &$custom, &$ret, $updated);
			}
		}
	}
}

function client_status_update_client($post_id, &$custom, &$ret, $updated){
	global $client_status_options, $wpdb, $blog_id, $current_user;
	
	// Update post custom fields
	if($custom['_client_data'][0] != $ret['body']){	// New updates...save and send email
		update_post_meta($post_id, "_client_data", $ret['body']);
		$custom['_client_data'][0] = $ret['body'];
		$client_url = $custom['_client_url'][0];
		
		$data = simplexml_load_string($custom['_client_data'][0]);
		
		// Send email to admins				
		if (empty($id)){
			$id = (int) $blog_id;
		}
		$blog_prefix = $wpdb->get_blog_prefix($id);
		$users = $wpdb->get_results( "SELECT user_id, user_id AS ID, user_login, display_name, user_email, meta_value FROM $wpdb->users, $wpdb->usermeta WHERE {$wpdb->users}.ID = {$wpdb->usermeta}.user_id AND meta_key = '{$blog_prefix}capabilities' ORDER BY {$wpdb->users}.display_name" );
			
		$toEmails = "";
		foreach($users as $u){
			if(in_array($u->ID, $client_status_options['admin_emails'])){
				if($toEmails != ""){
					$toEmails .= "," . $u->user_email;
				} else {
					$toEmails = $u->user_email;
				}
			}
		}
		if($toEmails != ""){
			client_status_send_status_email($toEmails, $data, $client_url);
		}
		
		// Send email to clients					
		if(isset($custom['_client_email']) && $custom['_client_email'][0] != ""){
			client_status_send_status_email($custom['_client_email'][0], $data, $client_url);
		}
	}
	update_post_meta($post_id, "_client_last_update", $updated);
	$custom['_client_last_update'][0] = $updated;
}
?>