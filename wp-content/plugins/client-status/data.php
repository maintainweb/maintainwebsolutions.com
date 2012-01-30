<?php header('Content-type: text/xml; charset=utf-8');
echo "<?xml version='1.0' encoding='UTF-8'?>\n";
	
require_once('../../../wp-load.php');
require_once('../../../wp-admin/includes/update.php');
require_once('../../../wp-admin/includes/plugin.php');

global $client_status_options, $wpdb;

if(isset($_REQUEST['security_key']) && $_REQUEST['security_key'] != ''){
	if(md5($client_status_options['security_key']) == $_REQUEST['security_key']){
		$version = get_bloginfo('version');
		$num_comm = wp_count_comments();
		$num_posts = wp_count_posts();
		
		echo "<wordpress>\r\n";
		echo "<is_public>". (get_option('blog_public') == 1) ."</is_public>\r\n";
		echo "<version>". $version ."</version>\r\n";
		echo "<plugin_version>" . CLIENT_STATUS_VERSION . "</plugin_version>\r\n";
		echo "<mysql_version>" . $wpdb->get_var('SELECT VERSION()') . "</mysql_version>\r\n";
		echo "<php_version>" . phpversion() . "</php_version>\r\n";
		echo "<os_version>" . PHP_OS . "</os_version>\r\n";
		echo "<posts>\r\n";
		echo "<draft>" . $num_posts->draft . "</draft>\r\n";
		echo "<future>" . $num_posts->future . "</future>\r\n";
		echo "<inherit>" . $num_posts->inherit . "</inherit>\r\n";
		echo "<pending>" . $num_posts->pending . "</pending>\r\n";
		echo "<published>" . $num_posts->publish . "</published>\r\n";
		echo "<trash>" . $num_posts->trash . "</trash>\r\n";
		echo "</posts>\r\n";
		echo "<comments>\r\n";
		echo "<approved>" . $num_comm->approved . "</approved>\r\n";
		echo "<pending>" . $num_comm->moderated . "</pending>\r\n";
		echo "<spam>" . $num_comm->spam . "</spam>\r\n";
		echo "<total>" . $num_comm->total_comments . "</total>\r\n";
		echo "<trash>" . $num_comm->trash . "</trash>\r\n";
		echo "</comments>\r\n";
		echo "<updates>\r\n";

		$core_updates = get_core_updates();
		if(is_array($core_updates) && array_key_exists(0, $core_updates)){
			echo "<core>\r\n";
			echo "<response>" . $core_updates[0]->response . "</response>\r\n";
			echo "<url>" . $core_updates[0]->url . "</url>\r\n";
			echo "<package>" . $core_updates[0]->package . "</package>\r\n";
			echo "<current>" . $core_updates[0]->current . "</current>\r\n";
			echo "<locale>" . $core_updates[0]->locale . "</locale>\r\n";
			echo "<php_version>" . $core_updates[0]->php_version . "</php_version>\r\n";
			echo "<mysql_version>" . $core_updates[0]->mysql_version . "</mysql_version>\r\n";
			echo "<dismissed>" . $core_updates[0]->dismissed . "</dismissed>\r\n";
			echo "</core>\r\n";
		}
		
		// Plugins		
		$plugin_updates = get_plugin_updates();
		if(count($plugin_updates) > 0){
			echo "<plugins>\r\n";
			foreach($plugin_updates as $key=>$plugin){
				echo "<plugin>\r\n";
				echo "<name><![CDATA[" . $plugin->Name . "]]></name>\r\n";
				echo "<title><![CDATA[" . $plugin->Title . "]]></title>\r\n";
				echo "<description><![CDATA[" . $plugin->Description . "]]></description>\r\n";
				echo "<uri><![CDATA[" . $plugin->PluginURI . "]]></uri>\r\n";
				echo "<id>" . $plugin->update->id . "</id>\r\n";
				echo "<file>" . $key . "</file>\r\n";
				echo "<slug>" . $plugin->update->slug . "</slug>\r\n";
				echo "<author><![CDATA[" . $plugin->Author . "]]></author>\r\n";
				echo "<author_uri><![CDATA[" . $plugin->AuthorURI . "]]></author_uri>\r\n";
				echo "<text_domain>" . $plugin->TextDomain . "</text_domain>\r\n";
				echo "<domain_path>" . $plugin->DomainPath . "</domain_path>\r\n";
				echo "<network>" . $plugin->Network . "</network>\r\n";
				echo "<current_version>" . $plugin->Version . "</current_version>\r\n";
				echo "<new_version>" . $plugin->update->new_version . "</new_version>\r\n";
				echo "<url><![CDATA[" . $plugin->update->url . "]]></url>\r\n";
				echo "<package><![CDATA[" . $plugin->update->package . "]]></package>\r\n";
				echo "</plugin>\r\n";
			}
			echo "</plugins>\r\n";
		}
		
		// Themes
		$theme_updates = get_theme_updates();
		if(count($theme_updates) > 0){
			echo "<themes>\r\n";
			foreach($theme_updates as $key=>$theme){
				// Change to array...some object properties have a space in them
				$theme = get_object_vars($theme);
				echo "<theme>\r\n";
				echo "<name><![CDATA[" . $theme['Name'] . "]]></name>\r\n";
				echo "<title><![CDATA[" . $theme['Title'] . "]]></title>\r\n";
				echo "<description><![CDATA[" . $theme['Description'] . "]]></description>\r\n";
				echo "<author><![CDATA[" . $theme['Author'] . "]]></author>\r\n";
				echo "<author_name><![CDATA[" . $theme['Author Name'] . "]]></author_name>\r\n";
				echo "<author_uri><![CDATA[" . $theme['Author URI'] . "]]></author_uri>\r\n";
				echo "<current_version>" . $theme['Version'] . "</current_version>\r\n";
				echo "<new_version>" . $theme['update']['new_version'] . "</new_version>\r\n";
				echo "<template>" . $theme['Template'] . "</template>\r\n";
				echo "<stylesheet>" . $theme['Stylesheet'] . "</stylesheet>\r\n";
				if(count($theme['Template Files']) > 0){
					echo "<template_files>\r\n";
					foreach($theme['Template Files'] as $file){
						echo "<template_file>" . $file . "</template_file>\r\n";
					}		
					echo "</template_files>\r\n";
				}
				if(count($theme['Stylesheet Files']) > 0){
					echo "<stylesheet_files>\r\n";
					foreach($theme['Stylesheet Files'] as $file){
						echo "<stylesheet_file>" . $file . "</stylesheet_file>\r\n";
					}		
					echo "</stylesheet_files>\r\n";
				}
				echo "<status><![CDATA[" . $theme['Status'] . "]]></status>\r\n";
				echo "<screenshot>" . $theme['Screenshot'] . "</screenshot>\r\n";
				if(count($theme['Tags']) > 0){
					echo "<tags>\r\n";
					foreach($theme['Tags'] as $tag){
						echo "<tag><![CDATA[" . $tag . "]]></tag>\r\n";
					}
					echo "</tags>\r\n";
				}
				echo "<theme_root>" . $theme['Theme Root'] . "</theme_root>\r\n";
				echo "<theme_root_uri><![CDATA[" . $theme['Theme Root URI'] . "]]></theme_root_uri>\r\n";
				echo "<parent_theme>" . $theme['Parent Theme'] . "</parent_theme>\r\n";
				echo "<url><![CDATA[" . $theme['update']['url'] . "]]></url>\r\n";
				echo "<package><![CDATA[" . $theme['update']['package'] . "]]></package>\r\n";
				echo "</theme>\r\n";
			}
			echo "</themes>\r\n";
		}
		echo "</updates>\r\n";
		echo "</wordpress>\r\n";
	} else {	
		// Security keys did not match
		echo "<wordpress><error>" . _('Invalid security key') . "</error></wordpress>";
		return;
	}
}
?>