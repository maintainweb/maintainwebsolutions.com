<?php 
require_once(ABSPATH . 'wp-admin/includes/plugin-install.php');

global $client_status_options, $post;
$plugin_info = array();
$theme_info = array();
$date_format = get_option('date_format') . " " . get_option('time_format');
$timezone_offset = get_option('gmt_offset');

$upate_clients = array();
if(is_array($_REQUEST)){
	foreach($_REQUEST as $key=>$value){
		if(strstr($key, 'refresh-')){
			$id = substr($key, 8, -2);
			$update_clients[$id] = $id; 
		}
	}
}
?>

<script type="text/javascript">
	jQuery(document).ready(function(){		
		jQuery('span.wp-has-submenu').click(function(){
			jQuery(this).next('.wp-submenu').slideToggle(600);
		});

		jQuery(".post-edit-link").click(function(event){event.stopPropagation();});
	});
	
	function switchTabs(tab){
        jQuery('.client_status_client').hide();
        jQuery('.'+tab).show();
    }
</script>

<div class="wrap">
	<h2><?php _e('Client Status'); ?></h2>
	
	<form method="post">
<?php 
$client_types = get_terms(array('client_status_client_type'));

echo "<ul class='client_status_tabs'>";
echo "<li><a id='0' href='javascript:switchTabs(\"client_status_tab_all\");'>" . __('All') . "</a></li>";
foreach($client_types as $key=>$client_type){
	echo "<li><a id='". $client_type->term_id ."' href='javascript:switchTabs(\"client_status_tab_". $client_type->term_id ."\");'>" . $client_type->name . "</a></li>";
}
echo "</ul>";

query_posts(array('post_type' => 'client_status_client', 'orderby' => 'title', 'order' => 'ASC', 'posts_per_page' => -1));
if(have_posts()){
	echo "<ul id='client_status_list'>";
	
	while(have_posts()){
		the_post();
		$custom = get_post_custom($post->ID);
		$client_url = $custom['_client_url'][0];

		$terms = wp_get_post_terms($post->ID, 'client_status_client_type');
		$term_classes = ""; 
		foreach($terms as $key=>$term){
			$term_classes .= " client_status_tab_" . $term->term_id;
		}
		
		echo "<li class='wp-has-submenu menu-top wp-menu-open client_status_client client_status_tab_all". $term_classes ."'>";

		$data_url = "";
		if($client_url != ""){
			$data_url = $client_url . CLIENT_STATUS_DATA_URL . "?security_key=" . md5($client_status_options['security_key']);
			echo "<span class='wp-has-submenu menu-top'><div class='client_status_client_title'>". get_the_title();
			
			$data = "";
			if(!empty($update_clients) && array_key_exists($post->ID, $update_clients)){
				$ret = wp_remote_post($data_url);
				$updated = time();
				
				client_status_update_client($post->ID, &$custom, $ret, $updated);	
			}
			$data = simplexml_load_string($custom['_client_data'][0]);
			if(!empty($data)){
				$last_update = $custom['_client_last_update'][0];
				$last_update = date_i18n($date_format, $last_update + $timezone_offset * 3600); 

				if(!property_exists($data, 'error')){
					$plugin_update_count = count($data->updates->plugins->plugin);
					$theme_update_count = count($data->updates->themes->theme);
					
					$strikes = 0;
					$update_count = 0;
					if($data->is_public == 0){ $strikes++; }
					if($data->updates->core->response == "upgrade") { $strikes++;  $update_count++; }
					if($plugin_update_count > 0) { $strikes++; $update_count+=$plugin_update_count; }
					if($theme_update_count > 0) { $strikes++; $update_count+=$theme_update_count; }
					
					echo "</div>"; // Closing client title div
					
					$core_text = (($data->updates->core->response == "upgrade") ? "<img class='status' src='" . CLIENT_STATUS_IMAGE_ERROR . "' />" : "<img class='status' src='" . CLIENT_STATUS_IMAGE_OK . "' />") . _('WordPress Version: ') . "<a class='". (($data->updates->core->response == "upgrade") ? "status_error" : "status_ok") ."' href='". $client_url . CLIENT_STATUS_WP_UPDATE_CORE_URL ."' target='_blank' alt='". $data->version ."' title='". $data->version ."'>" . $data->version . "</a>";
					$plugin_text = (($plugin_update_count > 0) ? "<img class='status' src='" . CLIENT_STATUS_IMAGE_PLUGIN_ERROR . "' />" : "<img class='status' src='" . CLIENT_STATUS_IMAGE_OK . "' />") . _('Plugin Updates: ') . "<a class='". (($plugin_update_count > 0) ? "status_error" : "status_ok") ."' href='". $client_url . CLIENT_STATUS_WP_UPDATE_PLUGINS_URL ."' target='_blank' alt='". $plugin_update_count ."' title='". $plugin_update_count ."'>" .  $plugin_update_count . "</a>";
					$theme_text = (($theme_update_count > 0) ? "<img class='status' src='" . CLIENT_STATUS_IMAGE_THEME_PROBLEM . "' />" : "<img class='status' src='" . CLIENT_STATUS_IMAGE_OK . "' />") . _('Theme Updates: ') . "<a class='". (($theme_update_count > 0) ? "status_error" : "status_ok") ."' href='". $client_url . CLIENT_STATUS_WP_UPDATE_THEMES_URL ."' target='_blank' alt='". $theme_update_count ."' title='". $theme_update_count ."'>" .  $theme_update_count . "</a>";
					$index_status_text = _('Status: ') . (($data->is_public == 0) ? "<a href='". $client_url . CLIENT_STATUS_WP_PRIVACY_URL ."' target='_blank' title='". _('Adjust privacy settings') ."' alt='". _('Adjust privacy settings') ."'>" . _('Not Indexable') . "</a>" : "<a href='". $client_url . CLIENT_STATUS_WP_PRIVACY_URL ."' target='_blank' title='". _('Adjust privacy settings') ."' alt='". _('Adjust privacy settings') ."'>" . _('Indexable') . "</a>");
					
					// Show update count for site
					if($update_count > 0){
						echo "<div class='update-site'><span class='update-count'>". $update_count ."</span></div>";
					}

					// Show/hide quick information
					if($client_status_options['show_quick_info']){
						echo "<span class='quick-info'>" . $core_text . ", " . $plugin_text . ", " . $theme_text . ", " . $index_status_text . "</span>";
					}

					edit_post_link('Edit', ' &nbsp;', '');
					echo "<div class='clear'></div>";	// Take care of any funny stuff
					echo "</span>";	// Closing span for client title bar
					echo "<div class='wp-submenu" . (($client_status_options['expand_client_info'] > 0) ? ' client_status_show_submenu' : '') . "'><div class='wp-submenu-content'>";
					
					//echo "<div class='client_status ". (($strikes > 0) ? "status_error" : "status_ok") ."'>";
					//echo "<span><strong>" . get_the_title() . "</strong></a>" . client_status_build_refresh($post->ID) ."</span>";
					echo "<div style='float: left; width: 45%;'>";
					echo "<h3>" . _('Updates') . "</h3>";
					echo "<p><em>" . _('Last Update: ') . $last_update . "</em>" . client_status_build_refresh($post->ID) . "</p>"; 
					echo "<p>" . $core_text . "</p>"; 
					echo "<p>" . $plugin_text . "</p>";
					
					if($plugin_update_count > 0){
						foreach($data->updates->plugins->plugin as $plugin){
							echo "<div class='plugin'>";
							$info = array();
							$slug = strval($plugin->slug);
							if(array_key_exists($slug, $plugin_info)){
								$info = $plugin_info[$slug];
							} else {
								$info = plugins_api('plugin_information', array('slug' => $slug ));
								$plugin_info[$slug] = $info;
							}
							
							echo "<strong>" . $plugin->name . "</strong><br />" . _('You have version ') . $plugin->current_version . _(' installed.') . _(' Update to ') . $plugin->new_version . ".";
							
							if ( isset($info->tested) && version_compare($info->tested, $data->version, '>=') ) {
								echo '<br />' . sprintf(__('Compatibility with WordPress %1$s: 100%% (according to its author)'), $data->version);
							} elseif ( isset($info->compatibility[$data->version][$plugin->new_version]) ) {
								echo $info->compatibility[$data->version][$plugin->new_version];
								echo '<br />' . sprintf(__('Compatibility with WordPress %1$s: %2$d%% (%3$d "works" votes out of %4$d total)'), $data->version, $compat[0], $compat[2], $compat[1]);
							} else {
								echo '<br />' . sprintf(__('Compatibility with WordPress %1$s: Unknown'), $data->version);
							}
								echo "</div>";
							}
					} 
					echo "<p>" . $theme_text . "</p>";
					if($theme_update_count > 0){
						foreach($data->updates->themes->theme as $theme){
							echo "<div class='theme'>";
							echo "<strong>" . $theme->name . "</strong><br />" . _('You have version ') . $theme->current_version . _(' installed.') . _(' Update to ') . $theme->new_version . ".";
							echo "</div>";
						}
					}
					echo "</div>";
					
					if(property_exists($data, 'plugin_version') && $data->plugin_version > 1.1){
						echo "<div style='float: left; width: 45%;'>";
						echo "<h3>" . _('Server Information') . "<a href='". $data_url ."' target='_blank' alt='". _('View client data file (for debugging)') ."' title='". _('View client data file (for debugging)') ."'><img class='helper' src='". CLIENT_STATUS_IMAGE_URL ."/transmit_blue.png' /></a></h3>";
						echo "<p>" . _('Server OS: ') . $data->os_version . "</p>";
						echo "<p>" . _('PHP Version: ') . $data->php_version . "</p>";
						echo "<p>" . _('MySQL Version: ') . $data->mysql_version . "</p>";
						echo "<p>" . $index_status_text . "</p>";
						echo "<p>" . _('Pending Comments: ') . "<a href='" . $client_url . CLIENT_STATUS_WP_COMMENTS_URL . "'>" . $data->comments->pending . "</a></p>";
						echo "<p>" . _('Pending Posts: ') . "<a href='". $client_url . CLIENT_STATUS_WP_POSTS_URL . "'>" . $data->posts->pending . "</a></p>";
						echo "</div>";
					}
					echo '<div class="clear"></div>';
				} else {
					echo "</div>";
					edit_post_link('Edit', ' &nbsp;', '');
					echo "<div class='clear'></div>";	// Take care of any funny stuff
					echo "</span>";	// Closing span for client title
					echo "<div class='wp-submenu'><div class='wp-submenu-content'>";
					echo "<span><strong>". get_the_title() . "</strong>" . client_status_build_refresh($post->ID) ."</span>";
					echo "<p><em>" . _('Last Update: ') . $last_update . "</em></p>";
					echo "<p><img class='status' src='". CLIENT_STATUS_IMAGE_PROBLEM ."' /><a href='". $client_url . CLIENT_STATUS_SETTINGS_URL ."' class='status_problem' target='_blank'>" . (($data->error != '') ? $data->error : _('An error has occurred')) . "</a></p>";
				}
			}
		} else {
			echo "<span class='wp-has-submenu menu-top'><div class='client_status_client_title'>". get_the_title();
			echo "</div>";
			edit_post_link('Edit', ' &nbsp;', '');
			echo '<div class="clear"></div>';
			echo "</span>";	// Closing span for client title
			echo "<div class='wp-submenu'><div class='wp-submenu-content'>";
			echo "<span><strong>". get_the_title() . "</strong>" . client_status_build_refresh($post->ID) ."</span>";
			echo "<p><img class='status' src='". CLIENT_STATUS_IMAGE_PROBLEM ."' /><a href='post.php?post=". $post->ID ."&action=edit' class='status_problem'>" . _('You must enter a site URL to retrieve data') . "</a></p>";		
		}
		echo "</div></div>";
		echo "</li>";
	}
	echo "</ul>";
}

function client_status_build_refresh($post_id){
	$text = "<input type='image' name='refresh-". $post_id."' id='refresh-". $post_id."' src='". CLIENT_STATUS_IMAGE_URL ."/arrow_refresh.png' />";
	return $text;
}
?>
	</form>
</div>