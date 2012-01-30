<?php
/*
Plugin Name: Gravity Forms Directory & Addons
Plugin URI: http://www.seodenver.com/gravity-forms-addons/
Description: Turn <a href="http://katz.si/gravityforms" rel="nofollow">Gravity Forms</a> into a great WordPress directory...and more!
Author: Katz Web Services, Inc.
Version: 3.2.1
Author URI: http://www.katzwebservices.com

Copyright 2011 Katz Web Services, Inc.  (email: info@katzwebservices.com)
 
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/

register_activation_hook( __FILE__, array('GFDirectory', 'activation')  );
add_action('plugins_loaded',  array('GFDirectory', 'plugins_loaded'));
add_action('plugins_loaded',  'kws_gf_load_functions');

class GFDirectory {

	private static $path = "gravity-forms-addons/gravity-forms-addons.php";
	private static $url = "http://www.gravityforms.com";
	private static $slug = "gravity-forms-addons";
	private static $version = "3.2.1";
	private static $min_gravityforms_version = "1.3.9";
	
	public static function directory_defaults($args = array()) {
    	$defaults = array(
			
			'form' => 1, // Gravity Forms form ID
			'approved' => false, // Show only entries that have been Approved (have a field in the form that is an Admin-only checkbox with a value of 'Approved' 
			'smartapproval' => true, // Auto-convert form into Approved-only when an Approved field is detected.
			'directoryview' => 'table', // Table, list or DL
			'entryview' => 'table', // Table, list or DL
			'hovertitle' => true, // Show column name as user hovers over cell
			'tableclass' => 'gf_directory widefat fixed', // Class for the <table>
			'tablestyle' => '', // inline CSS for the <table>
			'rowclass' => '', // Class for the <table>
			'rowstyle' => '', // inline CSS for all <tbody><tr>'s
			'valign' => '',
			'sort' => 'date_created', // Use the input ID ( example: 1.3 or 7 or ip )
			'dir' => 'DESC',
			
			'useredit' => false,
			'limituser' => false,
			'adminedit' => false,
			
			'status' => 'active', // Added in 2.0
			'start_date' => '', // Added in 2.0
			'end_date' => '', // Added in 2.0
			
			'wpautop' => true, // Convert bulk paragraph text to...paragraphs
			'page_size' => 20, // Number of entries to show at once
			'startpage' => 1, // If you want to show page 8 instead of 1
			
			'lightboxstyle' => 3,
			'lightboxsettings' => array('images' => true, 'entry' => null, 'websites' => null),
			
			'showcount' => true, // Do you want to show "Displaying 1-19 of 19"?
			'pagelinksshowall' => true, // Whether to show each page number, or just 7
			'next_text' => '&raquo;',
			'prev_text' => '&laquo;',
			'pagelinkstype' => 'plain', // 'plain' is just a string with the links separated by a newline character. The other possible values are either 'array' or 'list'. 
			'showrowids' => true, // Whether or not to show the row ids, which are the entry IDs.
			'fulltext' => true, // If there's a textarea or post content field, show the full content or a summary?
			'linkemail' => true, // Convert email fields to email mailto: links
			'linkwebsite' => true, // Convert URLs to links
			'linknewwindow' => false, // Open links in new window? (uses target="_blank")
			'nofollowlinks' => false, // Add nofollow to all links, including emails
			'icon' => false, // show the GF icon as it does in admin?
			'titleshow' => true, // Show a form title? By default, the title will be the form title.
			'titleprefix' => 'Entries for ', // Default GF behavior is 'Entries : '
			'tablewidth' => '100%', // 'width' attribute for the table
			'searchtabindex' => false, // adds tabindex="" to the search field
			'search' => true, // show the search field
			'tfoot' => true, // show the <tfoot>
			'thead' => true, // show the <thead>
			'showadminonly' => false, // Admin only columns aren't shown by default, but can be (added 2.0.1)
			'datecreatedformat' => get_option('date_format').' \a\t '.get_option('time_format'), // Use standard PHP date formats (http://php.net/manual/en/function.date.php)
			'credit' => true, // Credit link
			'dateformat' => false, // Override the options from Gravity Forms, and use standard PHP date formats (http://php.net/manual/en/function.date.php)
			'postimage' => 'icon', // Whether to show icon, thumbnail, or large image
			'getimagesize' => false,
			'entry' => true, // If there's an Entry ID column, link to the full entry
			'entrylink' => 'View entry details',
			'entryth' => 'More Info',
			'entryback' => '&larr; Back to directory',
			'entryonly' => true,
			'entrytitle' => 'Entry Detail',
			'entryanchor' => true,
			'truncatelink' => false,
			'appendaddress' => false,
			'hideaddresspieces' => false,
			'jssearch' => true,
			'jstable' => false,
			'lightbox' => null, // depreciated - Combining with lightboxsettings
			'entrylightbox' => null, // depreciated - Combining with lightboxsettings
		);
		
		$settings = get_option("gf_addons_settings");
		if(isset($settings['directory_defaults'])) {
			$defaults = wp_parse_args($settings['directory_defaults'], $defaults);
		}
		
		$options = wp_parse_args($args, $defaults);
		
		// Backward Compatibility
		if(!empty($args['lightbox'])) { $options['lightboxsettings']['images'] = 1; }
		if(!empty($args['entrylightbox'])) { $options['lightboxsettings']['entry'] = 1; }
		unset($options['lightbox'], $options['entrylightbox']); // Depreciated for lightboxsettings
		
		return apply_filters('kws_gf_directory_defaults', $options);
    }
    
	public static function plugins_loaded() {
		
		add_action('admin_notices', array('GFDirectory', 'gf_warning'));
		
		if(!self::is_gravityforms_installed()) { return false; }
		
		if(in_array(RG_CURRENT_PAGE, array("gf_entries", "admin.php", "admin-ajax.php"))) {
	    	self::globals_get_approved_column();
	    }
	    if(self::is_gravity_page()) {
		    self::load_functionality();
		}
	    
	    add_action('init',  array('GFDirectory', 'init'));
	    self::process_bulk_update();
	    add_shortcode('directory', array('GFDirectory', 'make_directory'));
	
	}
	
    //Plugin starting point. Will load appropriate files
    public static function init(){
		global $current_user;
		
		self::add_rewrite();
		
		if(!self::is_gravityforms_supported()){
           return;
        }
	
        if(is_admin()){
		   //creates a new Settings page on Gravity Forms' settings screen
            if(self::has_access("gravityforms_directory")){
                RGForms::add_settings_page("Directory & Addons", array("GFDirectory", "settings_page"), "");
            }
            add_filter("gform_addon_navigation", array('GFDirectory', 'create_menu')); //creates the subnav left menu
            
            //Adding "embed form" button
			add_action('media_buttons_context', array("GFDirectory", 'add_form_button'), 999);
			
			if(in_array(RG_CURRENT_PAGE, array('post.php', 'page.php', 'page-new.php', 'post-new.php'))){
				add_action('admin_footer',	array("GFDirectory", 'add_mce_popup'));
				wp_enqueue_script("gforms_ui_datepicker", GFCommon::get_base_url() . "/js/jquery-ui/ui.datepicker.js", array("jquery"), GFCommon::$version, true);
			}
		
			add_action('admin_head', array('GFDirectory', 'addons_page'));
			
        } else {
        	add_action('template_redirect', array('GFDirectory', 'enqueue_files'));
        	if(apply_filters('kws_gf_directory_canonical_add', true)) {
				add_filter('post_link', array('GFDirectory','directory_canonical'), 1, 3);
				add_filter('page_link', array('GFDirectory','directory_canonical'), 1, 3);
			}
			if(apply_filters('kws_gf_directory_shortlink', true)) {
				add_filter('get_shortlink', array('GFDirectory', 'shortlink'));
			}
//			add_filter('kws_gf_directory_lead_filter', array('GFDirectory','show_only_user_entries'));
			add_filter('kws_gf_directory_anchor_text', array('GFDirectory', 'directory_anchor_text'));
        }
        
        //integrating with Members plugin
        if(function_exists('members_get_capabilities')) {
            add_filter('members_get_capabilities', array("GFDirectory", "members_get_capabilities"));
        }
        
		add_filter('gform_pre_render',array('GFDirectory', 'show_field_ids'));
		
		if(self::is_gravity_page()) {
        	add_filter('gform_tooltips', array('GFDirectory', 'directory_tooltips')); //Filter to add a new tooltip
			add_action("gform_editor_js", array('GFDirectory', "editor_script")); //Action to inject supporting script to the form editor page
			add_action("gform_field_advanced_settings", array('GFDirectory',"use_as_entry_link_settings"), 10, 2);
			add_filter("gform_add_field_buttons", array('GFDirectory',"directory_add_field_buttons"));
    		add_filter('admin_head', array('GFDirectory','directory_add_default_values'));
    		add_filter('kws_gf_directory_td_address', array('GFDirectory','format_address'), 1, 2); // Add this filter so it can be removed or overridden by users
    		
    		// Allows for edit links to work with a link instead of a form (GET instead of POST)
			if(isset($_GET["screen_mode"])) { $_POST["screen_mode"] = $_GET["screen_mode"]; }
    	}
    	
        if(self::is_directory_page()){

            //enqueueing sack for AJAX requests
            wp_enqueue_script(array("sack", 'datepicker'));
			wp_enqueue_style('gravityforms-admin', GFCommon::get_base_url().'/css/admin.css');
			
         }
         else if(self::is_gravity_page('gf_entries')) {
         	add_filter("gform_get_field_value", array('GFDirectory','add_lead_approved_hidden_input'), 1, 3);
         }
         else if(in_array(RG_CURRENT_PAGE, array("admin-ajax.php"))){
            add_action('wp_ajax_rg_update_feed_active', array('GFDirectory', 'update_feed_active'));
            add_action('wp_ajax_gf_select_directory_form', array('GFDirectory', 'select_directory_form'));
            add_action('wp_ajax_rg_update_approved', array('GFDirectory','directory_update_approved_hook'));

        } else if(in_array(RG_CURRENT_PAGE, array("plugins.php"))){
        
	        add_filter('plugin_action_links', array('GFDirectory', 'settings_link'), 10, 2 );
	    
	    }
	    
    }
        
    public static function addons_page(){
    
    	if(self::is_gravity_page('gf_addons')) {
    	
    		$replacements = array(
    			'FreshBooks Add-On' => '<a href="https://katzwebservices.freshbooks.com/refer/www">FreshBooks</a> Add-On',
    			'AWeber email marketing service' => '<a href="http://tryemailmarketing.aweber.com/">AWeber email marketing service</a>'
    		);
    	?>
    	<script type="text/javascript">
    		jQuery(document).ready(function($) {
    			var text;
    			$("#available-addons td").each(function() {
    				var $that = $(this);
    				$(this).html(function() {
	    				text = $that.html();
		    			<?php 
		    			foreach($replacements as $key => $text) {
		    				echo "\ntext = text.replace('{$key}', '{$text}');\n\t";
		    			}
		    			?>
	    				
	    				return text;
	    			});
	    		});
    		});
    	</script>
    	<?php
    	}
	
	}
    
    //Target of Member plugin filter. Provides the plugin with Gravity Forms lists of capabilities
    public static function members_get_capabilities( $caps ) {
        return array_merge($caps, array("gravityforms_directory", "gravityforms_directory_uninstall"));
    }
    
    public function activation() {
    	self::add_activation_notice();
		self::add_permissions();
		self::flush_rules();
    }
    
    private static function is_gravityforms_installed(){
        return class_exists("RGForms");
    }
    
    public static function add_permissions(){
        global $wp_roles;
        $wp_roles->add_cap("administrator", "gravityforms_directory");
        $wp_roles->add_cap("administrator", "gravityforms_directory_uninstall");
    }
    
    // If the classes don't exist, the plugin won't do anything useful.
	function gf_warning() {
		global $pagenow; 
		$message = '';

		if($pagenow != 'plugins.php') { return; }
		
		if(!self::is_gravityforms_installed()) {
			if(file_exists(WP_PLUGIN_DIR.'/gravityforms/gravityforms.php')) {
				$message .= __(sprintf('%sGravity Forms is installed but not active. %sActivate Gravity Forms%s to use the Gravity Forms Directory & Addons plugin.%s', '<p>', '<a href="'.wp_nonce_url(admin_url('plugins.php?action=activate&plugin=gravityforms/gravityforms.php'), 'activate-plugin_gravityforms/gravityforms.php').'" style="font-weight:strong;">', '</a>', '</p>'), 'gravity-forms-addons');
			} else {
				$message = sprintf(__('%sGravity Forms cannot be found%s
				
				The %sGravity Forms plugin%s must be installed and activated for the Gravity Forms Addons plugin to work.
				
				If you haven\'t installed the plugin, you can %3$spurchase the plugin here%4$s. If you have, and you believe this notice is in error, %5$sstart a topic on the plugin support forum%4$s.
				
				%6$s%7$sBuy Gravity Forms%4$s%8$s
				', 'gravity-forms-addons'), '<strong>', '</strong>', "<a href='http://katz.si/gravityforms'>", '</a>', '<a href="http://wordpress.org/tags/gravity-forms-addons?forum_id=10#postform">', '<p class="submit">', "<a href='http://katz.si/gravityforms' style='color:white!important' class='button button-primary'>", '</p>');
			}
		}
		if(!empty($message)) {
			echo '<div id="message" class="error">'.wpautop($message).'</div>';
		} else if($message = get_transient('kws_gf_activation_notice')) {
			echo '<div id="message" class="updated">'.wpautop($message).'</div>';
			delete_transient('kws_gf_activation_notice');
		}
	}
	
    public function flush_rules() {
		global $wp_rewrite;
		self::add_rewrite();
		$wp_rewrite->flush_rules();
		return;
	}
		
	public function add_activation_notice() {
		#if(!get_option("gf_addons_settings")) {
			$message = __(sprintf('Congratulations - the Gravity Forms Directory & Addons plugin has been installed. %sGo to the settings page%s to read usage instructions and configure the plugin default settings. %sGo to settings page%s', '<a href="'.admin_url('admin.php?page=gf_settings&addon=Directory+%26+Addons&viewinstructions=true').'">', '</a>', '<p class="submit"><a href="'.admin_url('admin.php?page=gf_settings&addon=Directory+%26+Addons&viewinstructions=true').'" class="button button-secondary">', '</a></p>'), 'gravity-forms-addons');
			set_transient('kws_gf_activation_notice', $message, 60*60);
		#}
	}
	
    private function load_functionality() {
    
    	register_deactivation_hook( __FILE__, array('GFDirectory', 'uninstall') );
    	
		$settings = GFDirectory::get_settings();
		extract($settings);
		
		if($widget) {
			// Load Joost's widget
			@include_once('gravity-forms-widget.php');	
		}
		
		if($referrer) {
			// Load Joost's referrer tracker
			@include_once('gravity-forms-referrer.php');	
		}
		
		if(!empty($modify_admin)) {
			add_action('admin_head', array('GFDirectory', 'admin_head'), 1);
		}
	}
	
	public function admin_head($settings = array()) {
		if(empty($settings)) {
			$settings = self::get_settings();
		}
		if(!empty($settings['modify_admin']['expand'])) {
			if(isset($_REQUEST['page']) && $_REQUEST['page'] == 'gf_edit_forms' && isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
				$style = '<style type="text/css">
					.gforms_edit_form_expanded ul.menu li.add_field_button_container ul,
					.gforms_edit_form_expanded ul.menu li.add_field_button_container ul ol { 
						display:block!important; 
					}
					#floatMenu {padding-top:1.4em!important;}
					.gforms_expend_all_menus_form {
						position:absolute;
						top:0;
						margin:0!important;
						width:100%;
						text-align:right;
					}
				</style>';
				$style= apply_filters('kws_gf_display_all_fields', $style);
				echo $style;
			}
		}
		if(!empty($settings['modify_admin']['ids'])) {
			echo '<script src="'.GFCommon::get_base_url().'/js/jquery.simplemodal-1.3.min.js"></script>'; // Added for the new IDs popup
		}
		
		if(isset($_REQUEST['page']) && ($_REQUEST['page'] == 'gf_edit_forms' || $_REQUEST['page'] == 'gf_entries')) {
				echo self::add_edit_js(isset($_REQUEST['id']), $settings);
		}
	}
	
	private function add_edit_js($edit_forms = false, $settings = array()) {
	?>
		<script type="text/javascript">
			// Edit link for Gravity Forms entries
			jQuery(document).ready(function($) {
	<?php	if(!empty($settings['modify_admin']['expand']) && $edit_forms) { ?>
				var onScrollScript = window.onscroll;
				$('div.gforms_edit_form #add_fields #floatMenu').prepend('<div class="alignright gforms_expend_all_menus_form"><label for="expandAllMenus"><input type="checkbox" id="expandAllMenus" value="1" /> Expand All Menus</label></div>');
				$('input#expandAllMenus').live('click', function(e) {

					if($(this).is(':checked')) {
						window.onscroll = '';
						$('div.gforms_edit_form').addClass('gforms_edit_form_expanded');
						//$('ul.menu li .button-title-link').unbind().die(); // .unbind() is for the initial .click()... .die() is for the live() below
					} else {
						window.onscroll = onScrollScript;
						$('div.gforms_edit_form').removeClass('gforms_edit_form_expanded');
					}
				});
				
				<?php	
			}
			if(!empty($settings['modify_admin']['toggle']) && $edit_forms) { ?>
				
				$('ul.menu').addClass('noaccordion');
			<?php 
			}

			if(isset($_REQUEST['page']) && $_REQUEST['page'] == 'gf_entries' && !empty($settings['modify_admin']['edit'])) {
				?>
				// Changed from :contains('Delete') to :last-child to work with 1.6
				$(".row-actions span:last-child").each(function() {
					var editLink = $(this).parents('tr').find('.column-title a').attr('href');
					editLink = editLink + '&screen_mode=edit';
					//alert();
					$(this).after('<span class="edit">| <a title="<?php _e("Edit this entry", "gravity-forms-addons"); ?>" href="'+editLink+'"><?php _e("Edit", "gravity-forms-addons"); ?></a></span>');
				});
				<?php 
			}
			
			else if(isset($_REQUEST['page']) && $_REQUEST['page'] == 'gf_edit_forms' && !empty($settings['modify_admin']['ids'])) {
				?>
				// Changed from :contains('Delete') to :last-child for future-proofing
				$(".row-actions span.edit:last-child").each(function() {
					var formID = $(this).parents('tr').find('.column-id').text();;
					$(this).after('<span class="edit">| <a title="<?php _e("View form field IDs", "gravity-forms-addons"); ?>" href="<?php  echo WP_PLUGIN_URL . "/" . basename(dirname(__FILE__)) . "/field-ids.php"; ?>?id='+formID+'&amp;show_field_ids=true" class="form_ids"><?php _e("IDs", "gravity-forms-addons"); ?></a></span>');
				});
					var h = $('#gravityformspreviewidsiframe').css('height');
					
					$("a.form_ids").live('click', function(e) {
						e.preventDefault();
						var src = $(this).attr('href');
						$.modal('<iframe src="' + src + '" width="" height="" style="border:0;">', {
//							closeHTML:"<a href='#'>Close</a>",
							minHeight:400,
							minWidth: 600,
							containerCss:{
								borderColor: 'transparent',
								borderWidth: 0,
								padding:10,
								escClose: true,
								minWidth:500,
								maxWidth:800,
								minHeight:500,
							},
							overlayClose:true,
							onShow: function(dlg) {
								var iframeHeight = $('iframe', $(dlg.container)).height();
								var containerHeight = $(dlg.container).height();
								var iframeWidth = $('iframe', $(dlg.container)).width();
								var containerWidth = $(dlg.container).width();
								
								if(containerHeight < iframeHeight) { $(dlg.container).height(iframeHeight); }
								else { $('iframe', $(dlg.container)).height(containerHeight); }
								
								if(containerWidth < iframeWidth) { $(dlg.container).width(iframeWidth); }
								else { $('iframe', $(dlg.container)).width(containerWidth); }
							}
						});
			         });

				<?php } ?>
			});
		</script>
		<?php
	}
    
    public function shortlink($link = '') {
		global $post;
		if(empty($post)) { return; }
		if(empty($link) && isset($post->guid)) {
			$link = $post->guid;
			return $link;
		}
		
		$url = add_query_arg(array());
		if(preg_match('/'.sanitize_title(apply_filters('kws_gf_directory_endpoint', 'entry')).'\/([0-9]+)(?:\/|-)([0-9]+)\/?/ism',$url, $matches)) {
			$link = add_query_arg(array('form'=>(int)$matches[1], 'leadid'=>(int)$matches[2]), $link);
		} elseif(isset($_REQUEST['leadid']) && isset($_REQUEST['form'])) {
			$link = add_query_arg(array('leadid'=>(int)$_REQUEST['leadid'], 'form'=>(int)$_REQUEST['form']), $link);
		}
		return $link;
	}
    
	public function directory_canonical($permalink, $sentPost = '', $leavename = '') {
		global $post; $post->permalink = $permalink; $url = add_query_arg(array());
		$sentPostID = is_object($sentPost) ? $sentPost->ID : $sentPost;
		// $post->ID === $sentPostID is so that add_query_arg match doesn't apply to prev/next posts; just current
		preg_match('/('.sanitize_title(apply_filters('kws_gf_directory_endpoint', 'entry')).'\/([0-9]+)(?:\/|-)([0-9]+)\/?)/ism',$url, $matches);
		if(isset($post->ID) && $post->ID === $sentPostID && !empty($matches)) {
			return trailingslashit($permalink).$matches[0];
		} elseif(isset($post->ID) && $post->ID === $sentPostID && (isset($_REQUEST['leadid']) && isset($_REQUEST['form'])) || !empty($matches)) {
			if($matches)  { $leadid = $matches[2]; $form = $matches[1]; } 
			else { $leadid = $_REQUEST['leadid']; $form = $_REQUEST['form']; }
			
			return add_query_arg(array('leadid' =>$leadid, 'form'=>$form), trailingslashit($permalink));
		}
		return $permalink;
	}
    
    public function enqueue_files() {
    	global $post, $kws_gf_styles, $kws_gf_scripts,$kws_gf_directory_options;

    	$kws_gf_styles = isset($kws_gf_styles) ? $kws_gf_styles : array();
    	$kws_gf_scripts = isset($kws_gf_scripts) ? $kws_gf_scripts : array();
    	
    	if(	!empty($post) && 
    		!empty($post->post_content) && 
    		preg_match('/(.?)\[(directory)\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?(.?)/', $post->post_content, $matches)
    	) {

			$options = self::directory_defaults(shortcode_parse_atts($matches[3]));
    		if(!is_array($options['lightboxsettings'])) { $options['lightboxsettings'] = explode(',', $options['lightboxsettings']); }
    		
    		$kws_gf_directory_options = $options;
    		do_action('kws_gf_directory_enqueue', $options, $post);
			
			extract($options);
    		
    		if($jstable) {
    			$theme = apply_filters('kws_gf_tablesorter_theme', 'blue', $form);
    			wp_register_style('tablesorter-'.$theme, WP_PLUGIN_URL . "/" . basename(dirname(__FILE__))."/tablesorter/themes/{$theme}/style.css");
    			wp_register_script('tablesorter-min', WP_PLUGIN_URL . "/" . basename(dirname(__FILE__))."/tablesorter/jquery.tablesorter.min.js", array('jquery'));
    			$kws_gf_styles[] = 'tablesorter-'.$theme;
    			$kws_gf_scripts[] = 'tablesorter-min';
    		}
    		
    		if(!empty($lightboxsettings)) {
    			wp_enqueue_script('colorbox', WP_PLUGIN_URL . "/" . basename(dirname(__FILE__))."/colorbox/js/jquery.colorbox-min.js", array('jquery'));
    			wp_enqueue_style('colorbox', WP_PLUGIN_URL . "/" . basename(dirname(__FILE__))."/colorbox/example{$lightboxstyle}/colorbox.css", array());
    			$kws_gf_scripts[] = $kws_gf_styles[] = 'colorbox';
    			add_action(apply_filters('kws_gf_directory_colorbox_action', 'wp_footer'), array('GFDirectory', 'load_colorbox'), 1000);
			}
    	}
    }
    
    function format_colorbox_settings($colorboxSettings = array()) {
    	$settings = array();
    	if(!empty($colorboxSettings) && is_array($colorboxSettings)) {
			foreach($colorboxSettings as $key => $value) {
				if($value === null) { continue; }
				if($value === true) {
					$value = 'true';
				} elseif(empty($value) && $value !== 0) {
					$value = 'false';
				} else {
					$value = '"'.$value.'"';
				}
				$settings["{$key}"] = $key.':'.$value.'';
			}
		}
		return $settings;
    }
    
    public function load_colorbox() {
    	global $kws_gf_directory_options;
    	extract($kws_gf_directory_options);
    	
		$lightboxsettings = apply_filters('kws_gf_directory_lightbox_settings', $lightboxsettings);
		$colorboxSettings = apply_filters('kws_gf_directory_colorbox_settings', array(
			'width' => apply_filters('kws_gf_directory_lightbox_width', '70%'),
			'height' => apply_filters('kws_gf_directory_lightbox_height', '70%'),
			'iframe' => true,
			'maxWidth' => '95%',
			'maxHeight' => '95%',
			'current' => '{current} of {total}',
			'rel' => apply_filters('kws_gf_directory_lightbox_settings_rel', null)
		));
		
		?>
    <script type="text/javascript">
    	jQuery(document).ready(function($) {
 <?php 
    		$output = '';
    		foreach($lightboxsettings as $key => $value) {
    			$settings = $colorboxSettings;
    			if(is_numeric($key)) { $key = $value; }
    			switch($key) {
    				case "images":
	    				$settings['width'] = $settings['height'] = $settings['iframe'] = null;
	    				break;
	    			case "urls":
	    				$settings['height'] = '80%';
	    				break;
	    		}
    			$output .= "\t\t".'$(".colorbox[rel~=\'directory_'.$key.'\']").colorbox(';
    			if(!empty($settings)) {
	    			$output .= "{\n\t\t\t".implode(",\n\t\t\t",self::format_colorbox_settings(apply_filters("kws_gf_directory_lightbox_{$key}_settings", $settings)))."\n\t\t}";
    			}
    			$output .= ");\n\n";
    		}
    		echo $output;
    		do_action('kws_gf_directory_jquery', $kws_gf_directory_options);
    		?>
    	});
    </script>
    	<?php
    }
    
    
    public function add_rewrite() {
    	global $wp_rewrite,$wp;
    	
		if(!$wp_rewrite->using_permalinks()) { return; }
		$endpoint = sanitize_title(apply_filters('kws_gf_directory_endpoint', 'entry'));

		# @TODO: Make sure this works in MU
		$wp_rewrite->add_permastruct("{$endpoint}", $endpoint.'/%'.$endpoint.'%/?', true);
		$wp_rewrite->add_endpoint("{$endpoint}",EP_ALL);
	}
    
    //Returns true if the current page is one of Gravity Forms pages. Returns false if not
    private static function is_gravity_page($page = array()){
        $current_page = trim(strtolower(RGForms::get("page")));
        if(empty($page)) {
	        $gf_pages = array("gf_edit_forms","gf_new_form","gf_entries","gf_settings","gf_export","gf_help");
	    } else {
	    	$gf_pages = is_array($page) ? $page : array($page);
	    }

        return in_array($current_page, $gf_pages);
    }
    
    function directory_update_approved($lead_id = 0, $approved = 0, $form_id = 0, $approvedcolumn = 0) {
		global $wpdb, $_gform_directory_approvedcolumn;
		
		if(!empty($approvedcolumn)) { $_gform_directory_approvedcolumn = $approvedcolumn; }
		
		if(empty($_gform_directory_approvedcolumn)) { return false; }
		
		$lead_detail_table = RGFormsModel::get_lead_details_table_name();
		
		// This will be faster in the 1.6+ future.
		if(function_exists('gform_update_meta')) { gform_update_meta($lead_id, 'is_approved', $approved); }
		
		if(empty($approved)) {
			//Deleting details for this field
            $sql = $wpdb->prepare("DELETE FROM $lead_detail_table WHERE lead_id=%d AND field_number BETWEEN %f AND %f ", $lead_id, $_gform_directory_approvedcolumn - 0.001, $_gform_directory_approvedcolumn + 0.001);
            $wpdb->query($sql);
		} else {
			$current_fields = $wpdb->get_results($wpdb->prepare("SELECT id, field_number FROM $lead_detail_table WHERE lead_id=%d", $lead_id));

			$lead_detail_id = RGFormsModel::get_lead_detail_id($current_fields, $_gform_directory_approvedcolumn);
			
	        if($lead_detail_id > 0){
	        	$update = $wpdb->update($lead_detail_table, array("value" => $approved), array("lead_id" => $lead_id, 'form_id' => $form_id, 'field_number' => $_gform_directory_approvedcolumn), array("%s"), array("%d", "%d", "%f"));
	        } else {
	        	$update = $wpdb->insert($lead_detail_table, array("lead_id" => $lead_id, "form_id" => $form_id, "field_number" => $_gform_directory_approvedcolumn, "value" => $approved), array("%d", "%d", "%f", "%s"));
	        }
		}
	}

	function directory_add_field_buttons($field_groups){
		$directory_fields = array(
			'name' => 'directory_fields',
			'label' => 'Directory Fields',
			'fields' => array(
				array(
					'class' => 'button',
					'value' => __('Approved', 'gravity-forms-addons'),
					'onclick' => "StartAddField('directoryapproved');"
				), 
				array(
					'class' => 'button',
					'value' => __('Entry Link', 'gravity-forms-addons'),
					'onclick' => "StartAddField('entrylink');"
				), 
				array(
					'class' => 'button',
					'value' => __('User Edit Link', 'gravity-forms-addons'),
					'onclick' => "StartAddField('usereditlink');"
				)
			)
		);
		
		array_push($field_groups, $directory_fields);
	
		return $field_groups;
	}
    
    public function use_as_entry_link_settings($position, $form_id){
	
	    //create settings on position 50 (right after Admin Label)
	    if($position === -1){
	        ?>
	        </ul>
	      </div>
	      <div id="gform_tab_3">
		        <ul>
			        <li class="use_as_entry_link gf_directory_setting field_setting">
			            <label for="field_use_as_entry_link">
			                <?php _e("Use As Link to Single Entry", "gravity-forms-addons"); ?>
			                <?php gform_tooltip("kws_gf_directory_use_as_link_to_single_entry") ?>
			            </label>
			            <label for="field_use_as_entry_link"><input type="checkbox" value="1" id="field_use_as_entry_link" /> <?php _e("Use this field as a link to single entry view", "gravity-forms-addons"); ?></label>
			        </li>
			        <li class="use_as_entry_link_value gf_directory_setting field_setting">
			            <label>
			                <?php _e("Single Entry Link Text", "gravity-forms-addons"); ?>
			                <span class="howto"><?php _e('Note: it is a good idea to use required fields for links to single entries so there are no blank links.', 'gravity-forms-addons'); ?></span>
			            </label>
			            
			        	<label><input type="radio" name="field_use_as_entry_link_value" id="field_use_as_entry_link_value" value="on" /> <?php _e("Use field values from entry", "gravity-forms-addons"); ?></label>
			        	<label><input type="radio" name="field_use_as_entry_link_value" id="field_use_as_entry_link_label" value="label" /> <?php _e(sprintf("Use the Field Label %s as link text", '<span id="entry_link_label_text"></span>'), "gravity-forms-addons"); ?></label>
			        	<label><input type="radio" name="field_use_as_entry_link_value" id="field_use_as_entry_link_custom" value="custom" /> <?php _e("Use custom link text.", "gravity-forms-addons"); ?></label>
			        	<span class="hide-if-js" style="display:block;clear:both; margin-left:1.5em"><input type="text" class="widefat" id="field_use_as_entry_link_value_custom_text" value="" /><span class="howto"><?php _e(sprintf('%s%%value%%%s will be replaced with each entry\'s value.', "<code class='code'>", '</code>'), 'gravity-forms-addons'); ?></span></span>
			        </li>
			        <li class="hide_in_directory_view gf_directory_setting field_setting">
			            <label for="hide_in_directory_view">
			                <?php _e("Hide This Field in Directory View?", "gravity-forms-addons"); ?>
			                <?php gform_tooltip("kws_gf_directory_hide_in_directory_view") ?>
			            </label>
			        	<label><input type="checkbox" id="hide_in_directory_view" /> <?php _e("Hide this field in the directory view.", "gravity-forms-addons"); ?></label>
			        </li>
			        <li class="hide_in_single_entry_view gf_directory_setting field_setting">
			            <label for="hide_in_single_entry_view">
			                <?php _e("Hide This Field in Single Entry View?", "gravity-forms-addons"); ?>
			                <?php gform_tooltip("kws_gf_directory_hide_in_single_entry_view") ?>
			            </label>
			        	<label><input type="checkbox" id="hide_in_single_entry_view" /> <?php _e("Hide this field in the single entry view.", "gravity-forms-addons"); ?></label>
			        </li>
	        <?php
	   }
	}

	public function editor_script(){
	    ?>
	    <style type="text/css">
	    	li.gf_directory_setting, li.gf_directory_setting li {
	    		padding-bottom: 4px!important;
	    	}
	    </style>
	    <script type='text/javascript'>
	    	jQuery(document).ready(function($) {
	    		
	    		$( "#field_settings" ).tabs("add", "#gform_tab_3", "Directory");
				
	    		$('a[href="#gform_tab_3"]').parent('li').css({'width':'100px', 'padding':'0'});

		        for (var key in fieldSettings) {
		        	fieldSettings[key] += ", .gf_directory_setting";
		        }
		  		
		  		$('#field_use_as_entry_link_value_custom_text').change(function() {
		  			if($("#field_use_as_entry_link_custom").is(':checked')) {
		  				SetFieldProperty('useAsEntryLink', $(this).val());
		  			}
		  		});
		  		
				$("input:checkbox, input:radio",$('#gform_tab_3')).click(function() {
					var $li = $(this).parents('#field_settings');
					var entrylink = false;
					
					if($("#field_use_as_entry_link", $li).is(":checked")) {
						entrylink = '1';
						
						$('.use_as_entry_link_value').slideDown();
						
						if($('input[name="field_use_as_entry_link_value"]:checked').length) {
							entrylink = $('input[name="field_use_as_entry_link_value"]:checked').val();
						}
						if(entrylink == 'custom') {
							entrylink = $('#field_use_as_entry_link_value_custom_text').val();
							$('#field_use_as_entry_link_value_custom_text').parent('span').slideDown();
						} else {
							$('#field_use_as_entry_link_value_custom_text').parent('span').slideUp();
						}
					} else {
						$('.use_as_entry_link_value', $li).slideUp();
					}
					
					var hideInSingle = false;
					if($("#hide_in_single_entry_view", $li).is(':checked')) {
						hideInSingle = true;
					}
					
					var hideInDirectory = false;
					if($("#hide_in_directory_view", $li).is(':checked')) {
						hideInDirectory = true;
					}
					
					SetFieldProperty('hideInDirectory', hideInDirectory);
					SetFieldProperty('hideInSingle', hideInSingle);
					SetFieldProperty('useAsEntryLink', entrylink);
		        });
		
				$('#field_label').change(function() {
					kwsGFupdateEntryLinkLabel($(this).val());
				});
				
				function kwsGFupdateEntryLinkLabel(label) {
					$('#entry_link_label_text').html(' ("'+label+'")');
				}
				
		        //binding to the load field settings event to initialize the checkbox
		        $(document).bind("gform_load_field_settings", function(event, field, form){
			        
		        	if(typeof(field["useAsEntryLink"]) !== "undefined" && field["useAsEntryLink"] !== false && field["useAsEntryLink"] !== 'false' && field["useAsEntryLink"] !== '') {
			            $("#field_use_as_entry_link").attr("checked", true);
			            $(".use_as_entry_link_value").show();
			            $('#field_use_as_entry_link_value_custom_text').parent('span').hide();
			            switch(field["useAsEntryLink"]) {
			            	case "on": 
			            	case "":
			            	case false:
			            		$("#field_use_as_entry_link_value").attr('checked', true);
			            		break;
			            	case "label":
			            		$("#field_use_as_entry_link_label").attr('checked', true);
			            		break;
			            	default:
			            		$('#field_use_as_entry_link_value_custom_text').parent('span').show();
			            		$("#field_use_as_entry_link_custom").attr('checked', true);
			            		$("#field_use_as_entry_link_value_custom_text").val(field["useAsEntryLink"]);
			            }
			        } else {
			        	$(".use_as_entry_link_value").hide();
			        	$("#field_use_as_entry_link").attr("checked", false);
			        }
			        
			        if($('input[name="field_use_as_entry_link_value"]:checked').length === 0) {
						$('#field_use_as_entry_link_value').attr('checked', true);
					}
			        	
			        kwsGFupdateEntryLinkLabel(field.label);
	
			        
		            $("#field_use_as_entry_link_label").attr("checked", field["useAsEntryLink"] === 'label');
		            
		            $("#hide_in_single_entry_view").attr("checked", (field["hideInSingle"] === true || field["hideInSingle"] === "on"));
		            $("#hide_in_directory_view").attr("checked", (field["hideInDirectory"] === true || field["hideInDirectory"] === "on"));
		            
		            
		        });
	       });
	    </script>
	    <?php
	}
	
	public function edit_lead_detail($Form, $lead, $options) {
		global $current_user, $_gform_directory_approvedcolumn;
		
		if(empty($_gform_directory_approvedcolumn)) { $_gform_directory_approvedcolumn = self::get_approved_column($Form); }
		
		$adminonlycolumns = self::get_admin_only($Form);
		
		// If you want to allow users to edit their own approval (?) add a filter and return true.
		if(apply_filters('kws_gf_directory_allow_user_edit_approved', false) === false) {
			$Form['fields'] = self::remove_approved_column('form', $Form['fields'], $_gform_directory_approvedcolumn);
		}
		
		if(isset($_GET['edit']) && !wp_verify_nonce(RGForms::get('edit'), 'edit')) {
			_e(sprintf('%sYou do not have permission to edit this form.%s', '<div class="error">', '</div>'), 'gravity-forms-addons');
			return;
		}

		
		if(
			(!empty($options['useredit']) && is_user_logged_in() && $current_user->id === $lead['created_by']) === true || 
			(!empty($options['adminedit']) && self::has_access("gravityforms_directory")) === true
		  ) {
			
			if(RGForms::post("action") === "update") {
	            check_admin_referer('gforms_save_entry', 'gforms_save_entry');
	            $lead = apply_filters('kws_gf_directory_lead_being_updated', $lead, $Form);
	            do_action('kws_gf_directory_pre_update_lead', $lead, $Form);
	            RGFormsModel::save_lead($Form, $lead);
	            $lead = RGFormsModel::get_lead($lead["id"]);
	            do_action('kws_gf_directory_post_update_lead', $lead, $Form);
	            _e(apply_filters('kws_gf_directory_lead_updated_message', sprintf("%sThe entry was successfully updated.%s", "<p class='updated' id='message' style='padding:.5em .75em; background-color:#ffffcc; border:1px solid #ccc;'>", "</p>"), $lead, $Form), 'gravity-forms-addons');
			} elseif(isset($_GET['edit'])) {
				add_filter('kws_gf_directory_backlink', array('GFDirectory', 'edit_entry_backlink'), 1, 2); ?>
				<form method="post" id="entry_form" enctype="multipart/form-data" action="<?php echo remove_query_arg(array('gf_search','sort','dir', 'page', 'edit'), add_query_arg(array()));?>">
		            <?php wp_nonce_field('gforms_save_entry', 'gforms_save_entry') ?>
		            <input type="hidden" name="action" id="action" value="update"/>
		            <input type="hidden" name="screen_mode" id="screen_mode" value="edit" />
		            <?php
						self::lead_detail_edit($Form, $lead);
						_e('<input class="button-primary" type="submit" tabindex="4" value="'.apply_filters('kws_gf_directory_update_lead_button_text', __('Update Entry', 'gravity-forms-addons')).'" name="save" />');
					?>
				</form>
				<?php 
				return false;
			}
		}
		return $lead;
	}
	
	public static function lead_detail_edit($form, $lead){
		
		if(!class_exists('GFFormDisplay')) {  @include_once(WP_PLUGIN_DIR . "/gravityforms/form_display.php"); }
	
        $form = apply_filters("gform_admin_pre_render_" . $form["id"], apply_filters("gform_admin_pre_render", $form));
        ?>
        <script type="text/javascript" src="<?php echo GFCommon::get_base_url() ?>/js/gravityforms.js?version=<?php echo GFCommon::$version?>"></script>
        <div id="namediv" class="stuffbox">
            <h3>
                <label for="name"><?php _e("Details", "gravityforms"); ?></label>
            </h3>
            <div class="inside">
                <table class="form-table entry-details">
                    <tbody>
                    <?php
                    foreach($form["fields"] as $field){
                        switch(RGFormsModel::get_input_type($field)){
                            case "section" :
                                ?>
                                <tr valign="top">
                                    <td class="detail-view">
                                        <div style="margin-bottom:10px; border-bottom:1px dotted #ccc;"><h2 class="detail_gsection_title"><?php echo esc_html(GFCommon::get_label($field))?></h2></div>
                                    </td>
                                </tr>
                                <?php

                            break;

                            case "captcha":
                            case "html":
                            case "password":
                                //ignore certain fields
                            break;
                            default :
                                $value = RGFormsModel::get_lead_field_value($lead, $field);
                                $content = "<tr valign='top'><td class='detail-view'><label class='detail-label'>" . esc_html(GFCommon::get_label($field)) . "</label>";
                                // This requires the GFFormDisplay for drop-down fields.
                                $content .= GFCommon::get_field_input($field, $value, $lead["id"]);
                                
                                $content .= "</td></tr>";

                                $content = apply_filters("gform_field_content", $content, $field, $value, $lead["id"], $form["id"]);

                                echo $content;
                            break;
                        }
                    }
                    ?>
                    </tbody>
                </table>
                <br/>
            </div>
        </div>
        <?php
    }
	
	public function edit_entry_backlink($content = '', $href = '', $entryback = '') {
		return '<p class="entryback"><a href="'.add_query_arg(array(), remove_query_arg(array('edit'))).'">'.esc_html(__(apply_filters('kws_gf_directory_edit_entry_cancel', "&larr; Cancel Editing"), "gravity-forms-addons")).'</a></p>';
	}
	
	public function lead_detail($Form, $lead, $allow_display_empty_fields=false, $inline = true, $options = array()) {
			global $current_user, $_gform_directory_approvedcolumn;
			get_currentuserinfo();
			
			$display_empty_fields = ''; $allow_display_empty_fields = true;
			if($allow_display_empty_fields){
				$display_empty_fields = @rgget("gf_display_empty_fields", $_COOKIE);
			}
			if(empty($options)) {
				$options = self::directory_defaults();
			}
			
			// Process editing leads
			$lead = self::edit_lead_detail($Form, $lead, $options);
			
			if($lead === false) { return; }
			
			extract($options);
			
			?>
			<table cellspacing="0" class="widefat fixed entry-detail-view">
			<?php if($inline) { ?>
				<thead>
					<tr>
						<th id="details" colspan="2" scope="col">
						<?php 
							$title = $Form["title"] .' : Entry # '.$lead["id"]; 
							$title = apply_filters('kws_gf_directory_detail_title', apply_filters('kws_gf_directory_detail_title_'.(int)$lead['id'], array($title, $lead), true), true);
							if(is_array($title)) {
								echo $title[0];
							} else {
								echo $title;
							}					  	
						?>
						</th>
					</tr>
				</thead>
				<?php
				}
				?>
				<tbody>
					<?php
					$count = 0;
					$field_count = sizeof($Form["fields"]);

					foreach($Form["fields"] as $field){
						
						// Don't show fields defined as hide in single.
						if(!empty($field['hideInSingle'])) { 
							if(self::has_access("gravityforms_directory")) {
								echo "\n\t\t\t\t\t\t\t\t\t".'<!-- '.__(sprintf('(Admin-only notice) Field #%d not shown: "Hide This Field in Single Entry View" was selected.', $field['id']), 'gravity-forms-addons').' -->'."\n\n";
							}
							continue; 
						}
						
						$count++;
						$is_last = $count >= $field_count ? true : false;
	
						switch(RGFormsModel::get_input_type($field)){
							case "section" :
								?>
								<tr>
									<td colspan="2" class="entry-view-section-break<?php echo $is_last ? " lastrow" : ""?>"><?php echo esc_html(GFCommon::get_label($field))?></td>
								</tr>
								<?php
								$value = NULL;
							break;
	
							case "captcha":
							case "html":
								$value = NULL;
								//ignore captcha field
							break;
							
							case "fileupload" :
							case "post_image" :
								$value = RGFormsModel::get_lead_field_value($lead, $field);
								$valueArray = explode("|:|", $value);
								
								@list($url, $title, $caption, $description) = $valueArray;
								$size = '';
								if(!empty($url)){
									//displaying thumbnail (if file is an image) or an icon based on the extension
									 $icon = self::get_icon_url($url);
									 if(!preg_match('/icon\_image\.gif/ism', $icon)) {
									 	$lightboxclass = '';
									 	$src = $icon;
									 	if(!empty($getimagesize)) {
											$size = @getimagesize($src);
											$img = "<img src='$src' {$size[3]}/>";
										} else {
											$size = false;
											$img = "<img src='$src' />";
										}
									 } else { // No thickbox for non-images please
									 	switch(strtolower(trim($postimage))) {
									 		case 'image':
									 			$src = $url;
									 			break;
									 		case 'icon':
									 		default:
									 			$src = $icon;
									 			break;
									 	}
									 	if(!empty($getimagesize)) {
											$size = @getimagesize($src);
										} else {
											$size = false;
										}
									 }
									 $img = array(
									 	'src' => $src,
									 	'size' => $size,
									 	'title' => $title,
									 	'caption' => $caption,
									 	'description' => $description,
									 	'url' => esc_attr($url),
									 	'code' => isset($size[3]) ? "<img src='$src' {$size[3]} />" : "<img src='$src' />"
									 );
									 $img = apply_filters('kws_gf_directory_lead_image', apply_filters('kws_gf_directory_lead_image_'.$postimage, apply_filters('kws_gf_directory_lead_image_'.$lead['id'], $img)));
									 $value = $display_value = "<a href='{$url}'{$target}{$lightboxclass}>{$img['code']}</a>";
								}
							break;
							
							default :
								$value = RGFormsModel::get_lead_field_value($lead, $field);
								$display_value = GFCommon::get_lead_field_display($field, $value);
							break;
						}
						
						if($value !== NULL && ($display_empty_fields || !empty($display_value) || $display_value === "0")) {
							if(is_array($display_value)) { $display_value = implode(', ', $display_value); }
							?>
							<tr>
								<th scope="row" class="entry-view-field-name"><?php echo esc_html(GFCommon::get_label($field))?></th>
								<td class="entry-view-field-value<?php echo $is_last ? " lastrow" : ""?>"><?php echo empty($display_value) && $display_value !== "0" ? "&nbsp;" : $display_value ?></td>
							</tr>
							<?php
						}
					}
					
					// Edit link
					if(
						!empty($options['useredit']) && is_user_logged_in() && $current_user->id === $lead['created_by'] || // Is user who created the entry 
						!empty($options['adminedit']) && self::has_access("gravityforms_directory") // Or is an administrator
					) {
					
					if(!empty($options['adminedit']) && self::has_access("gravityforms_directory")) {
						$editbuttontext = apply_filters('kws_gf_directory_edit_entry_text_admin', __("Edit Entry", 'gravity-forms-addons'));
					} else {
						$editbuttontext = apply_filters('kws_gf_directory_edit_entry_text_user', __("Edit Your Entry", 'gravity-forms-addons'));
					}
					
					?>
						<tr>
							<th scope="row" class="entry-view-field-name"><?php _e(apply_filters('kws_gf_directory_edit_entry_th', "Edit"), "gravity-forms-addons"); ?></th>
								<td class="entry-view-field-value useredit"><a href="<?php echo add_query_arg(array('edit' => wp_create_nonce('edit'))); ?>"><?php _e($editbuttontext); ?></a></td>
						</tr>
					<?php
					}
					
					?>
				</tbody>
			</table>
			<?php
	}
	
	public function get_admin_only($form, $adminOnly = array()) {
		if(!is_array($form)) { return false; }

		foreach($form['fields'] as $key=>$col) {
			// Only the Go to Entry button adds disableMargins.
			
			if($col['type'] === 'hidden' && !empty($col['useAsEntryLink']) && !empty($col['disableMargins'])) {
				continue;
			}
			if(!empty($col['adminOnly'])) {
				$adminOnly[] = $col['id'];
			}
			if(isset($col['inputs']) && is_array($col['inputs'])) { 
				foreach($col['inputs'] as $key2=>$input) {
					if(!empty($col['adminOnly'])) {
						$adminOnly[] = $input['id'];
					}
				}
			}
		}
		return $adminOnly;
	}
	
	public function process_lead_detail($inline = true, $entryback = '', $showadminonly = false, $adminonlycolumns = array(), $approvedcolumn = null, $options = array(), $entryonly = true) {
		global $wp,$post,$wp_rewrite,$wpdb;
		$formid = $leadid = false;
		
		
		if(isset($wp->request) && $wp_rewrite->using_permalinks() && preg_match('/\/?'.sanitize_title(apply_filters('kws_gf_directory_endpoint', 'entry')).'\/([0-9]+)(?:\/|-)([0-9]+)/ism', $wp->request, $matches)) {
			$formid = $matches[1];
			$leadid = $matches[2];
		}
	
		$formid = isset($_REQUEST['form']) ? (int)$_REQUEST['form'] : $formid;
		$leadid = isset($_REQUEST['leadid']) ? (int)$_REQUEST['leadid'] : $leadid;
	
		if($leadid && $formid) {
			$form = apply_filters('kws_gf_directory_lead_detail_form', RGFormsModel::get_form_meta((int)$formid));
			$lead = apply_filters('kws_gf_directory_lead_detail', RGFormsModel::get_lead((int)$leadid));
			
			if(empty($approvedcolumn)) { $approvedcolumn = self::get_approved_column($form); }
			if(empty($adminonlycolumns) && !$showadminonly) { $adminonlycolumns = self::get_admin_only($form); }
			
			if(!$showadminonly)  {
				$lead = self::remove_admin_only(array($lead), $adminonlycolumns, $approvedcolumn, true, true, $form);
				$lead = $lead[0];
				$form['fields'] = self::remove_admin_only($form['fields'], $adminonlycolumns, $approvedcolumn, false, true, $form);
			}
					
			ob_start(); // Using ob_start() allows us to filter output
				@self::lead_detail($form, $lead, false, $inline, $options);
				$content = ob_get_contents(); // Get the output
			ob_end_clean(); // Clear the cache
			
			$current = remove_query_arg(array('row', 'page_id', 'leadid', 'form', 'edit'));
			$url = parse_url(add_query_arg(array(), $current));
			if(function_exists('is_multisite') && is_multisite() && $wpdb->blogid != 1) {
				$href = $current;
			} else {
				$href = isset($post->permalink) ? $post->permalink : get_permalink();
			}
			if(!empty($url['query'])) { $href .= '?'.$url['query']; }
			
			if(!empty($options['entryanchor']) && !empty($options['showrowids'])) { $href .= '#lead_row_'.$leadid; }
			
			// If there's a back link, format it
			if(!empty($entryback) && !empty($entryonly)) {
				$link = apply_filters('kws_gf_directory_backlink', '<p class="entryback"><a href="'.$href.'">'.esc_html($entryback).'</a></p>', $href, $entryback);
			} else { 
				$link = ''; 
			}
			
			$content = $link . $content;
			$content = apply_filters('kws_gf_directory_detail', apply_filters('kws_gf_directory_detail_'.(int)$leadid, $content, true), true);
			
			
			if(isset($options['entryview'])) {
				$content = self::pseudo_filter($content, $options['entryview'], true);
			}
			
			return $content;
		} else {
			return false;
		}
	}
	
	function make_directory($atts) {
		global $wpdb,$wp_rewrite,$post, $wpdb,$directory_shown,$kws_gf_scripts,$kws_gf_styles;
		
		if(!class_exists('GFEntryDetail')) { @require_once(GFCommon::get_base_path() . "/entry_detail.php"); }
		if(!class_exists('GFCommon')) { @require_once(WP_PLUGIN_DIR . "/gravityforms/common.php"); }
		if(!class_exists('RGFormsModel')) { @require_once(WP_PLUGIN_DIR . "/gravityforms/forms_model.php"); }
		
		//quit if version of wp is not supported
		if(!class_exists('GFCommon') || !GFCommon::ensure_wp_version())
			return;
	
		ob_start(); // Using ob_start() allows us to use echo instead of $output .=
		
		foreach($atts as $key => $att) { 
			if(strtolower($att) == 'false') { $atts[$key] = false; }
			if(strtolower($att) == 'true') { $atts[$key] = true; }
		}
		
		$atts['approved'] = isset($atts['approved']) ? $atts['approved'] : -1;
		
		if(!empty($atts['lightboxsettings']) && is_string($atts['lightboxsettings'])) {
			$atts['lightboxsettings'] = explode(',', $atts['lightboxsettings']);
		}
		
		$options = self::directory_defaults($atts);
		
		
		// Make sure everything is on the same page.
		if(is_array($options['lightboxsettings'])) {
			foreach($options['lightboxsettings'] as $key => $value) {
				if(is_numeric($key)) {
					$options['lightboxsettings']["{$value}"] = $value;
					unset($options['lightboxsettings']["{$key}"]);
				}
			}
		}
		
		extract( $options );
			
			$form_id = $form;
			
			$form = RGFormsModel::get_form_meta($form_id);

			if(empty($form)) { return;}
			
			$sort_field = empty($_GET["sort"]) ? $sort : $_GET["sort"];
			$sort_direction = empty($_GET["dir"]) ? $dir : $_GET["dir"];
			$search_query = !empty($_GET["gf_search"]) ? $_GET["gf_search"] : null;
			
			$start_date = !empty($_GET["start_date"]) ? $_GET["start_date"] : $start_date;
			$end_date = !empty($_GET["end_date"]) ? $_GET["end_date"] : $end_date;
			
			$page_index = empty($_GET["page"]) ? $startpage -1 : intval($_GET["page"]) - 1;
			$star = (isset($_GET["star"]) && is_numeric($_GET["star"])) ? intval($_GET["star"]) : null;
			$read = (isset($_GET["read"]) && is_numeric($_GET["read"])) ? intval($_GET["read"]) : null;
			$first_item_index = $page_index * $page_size;
			$link_params = array();
			if(!empty($page_index)) { $link_params['page'] = $page_index; }
			$formaction = remove_query_arg(array('gf_search','sort','dir', 'page', 'edit'), add_query_arg($link_params));
			$tableclass .= !empty($jstable) ? ' tablesorter' : '';
			$title = $form["title"];
			$sort_field_meta = RGFormsModel::get_field($form, $sort_field);
			$is_numeric = $sort_field_meta["type"] == "number";
			$columns = RGFormsModel::get_grid_columns($form_id, true);
	
			$approvedcolumn = false;
			if($approved || (!empty($smartapproval) && $approved === -1)) { $approvedcolumn = self::get_approved_column($form); }
			
			if(!empty($smartapproval) && $approved === -1 && !empty($approvedcolumn)) {
				$approved = true; // If there is an approved column, turn on approval
			} else {
				$approved = false; // Otherwise, show entries as normal.
			}
			
			$entrylinkcolumns = self::get_entrylink_column($form, $entry);
			$adminonlycolumns = self::get_admin_only($form);
			
			//
			// Show only a single entry
			//
			if(!empty($entry) && $detail = self::process_lead_detail(true, $entryback, $showadminonly, $adminonlycolumns, $approvedcolumn, $options, $entryonly)) {
				
				echo $detail;
				if(!empty($entryonly)) {
					do_action('kws_gf_after_directory', do_action('kws_gf_after_directory_form_'.$form_id, $form, compact($approved,$sort_field,$sort_direction,$search_query,$first_item_index,$page_size,$star,$read,$is_numeric,$start_date,$end_date)));
			
					$content = ob_get_contents(); // Get the output
					ob_end_clean(); // Clear the cache
					
					// If the form is form #2, two filters are applied: `kws_gf_directory_output_2` and `kws_gf_directory_output`
					$content = apply_filters('kws_gf_directory_output', apply_filters('kws_gf_directory_output_'.$form_id, self::pseudo_filter($content, $directoryview)));
					return $content;
				}
			};
			
			
			//
			// Or start to generate the directory
			//
			$leads = GFDirectory::get_leads($form_id, $sort_field, $sort_direction, $search_query, $first_item_index, $page_size, $star, $read, $is_numeric, $start_date, $end_date, 'active', $approvedcolumn, $limituser);
			
			if(!$showadminonly)	 {
				$columns = self::remove_admin_only($columns, $adminonlycolumns, $approvedcolumn, false, false, $form);
				$leads = self::remove_admin_only($leads, $adminonlycolumns, $approvedcolumn, true, false, $form);
			}
			
			
			// Allow lightbox to determine whether showadminonly is valid without passing a query string in URL
			if($entry === true && !empty($lightboxsettings['entry'])) {
				if(get_transient('gf_form_'.$form_id.'_post_'.$post->ID.'_showadminonly') != $showadminonly) {
					set_transient('gf_form_'.$form_id.'_post_'.$post->ID.'_showadminonly', $showadminonly, 60*60);	
				}
			} else {
				delete_transient('gf_form_'.$form_id.'_post_'.$post->ID.'_showadminonly');
			}
			
			
			// Get a list of query args for the pagination links
			if(!empty($search_query)) { $args["gf_search"] = urlencode($search_query); }
			if(!empty($sort_field)) { $args["sort"] = $sort_field; }
			if(!empty($sort_direction)) { $args["dir"] = $sort_direction; }
			if(!empty($star)) { $args["star"] = $star; }
	
			if($page_size > 0) {
				
				$lead_count = self::get_lead_count($form_id, $search_query, $star, $read, $approvedcolumn, $approved, $leads, $start_date, $end_date, $limituser);
				
				$page_links = array(
					'base' =>  @add_query_arg('page','%#%'),// get_permalink().'%_%',
					'format' => '&page=%#%',
					'add_args' => $args,
					'prev_text' => $prev_text,
					'next_text' => $next_text,
					'total' => ceil($lead_count / $page_size),
					'current' => $page_index + 1,
					'show_all' => $pagelinksshowall,
				);
						
				$page_links = apply_filters('kws_gf_results_pagination', $page_links);
				
				$page_links = paginate_links($page_links);
			} else {
				// Showing all results
				$page_links = false;
				$lead_count = sizeof($leads);
			}
			
			
			if(!isset($directory_shown)) {
				$directory_shown = true;
				
				if(!empty($lightboxsettings['images']) || !empty($lightboxsettings['entry'])) {
					if(!is_array($kws_gf_scripts) || !in_array('colorbox', $kws_gf_scripts)) { wp_print_scripts(array("colorbox")); }
					if(!is_array($kws_gf_styles) || !in_array('colorbox', $kws_gf_styles)) { wp_print_styles(array("colorbox")); }
				}
		
				if(!empty($jstable)) { ?>
				<?php if(!in_array('tablesorter-blue', $kws_gf_styles)) { ?>
					<link href="<?php echo WP_PLUGIN_URL . "/" . basename(dirname(__FILE__)); ?>/tablesorter/themes/blue/style.css" rel="stylesheet" />
				<?php } if(!in_array('tablesorter-min', $kws_gf_scripts)) { ?>
					<script type="text/javascript" src="<?php echo WP_PLUGIN_URL . "/" . basename(dirname(__FILE__)); ?>/tablesorter/jquery.tablesorter.min.js"></script>				
				<?php } } ?>
				
				<script type="text/javascript">
					<?php if(!empty($lightboxsettings['images']) || !empty($lightboxsettings['entry'])) { ?>
		
					var tb_pathToImage = "<?php echo site_url('/wp-includes/js/thickbox/loadingAnimation.gif'); ?>";
					var tb_closeImage = "<?php echo site_url('/wp-includes/js/thickbox/tb-close.png'); ?>";
					var tb_height = 600;
					<?php } ?>
					function not_empty(variable) { 
						if(variable == '' || variable == null || variable == 'undefined' || typeof(variable) == 'undefined') {
							return false;
						} else { 
							return true;
						}
					}
				
				<?php if(!empty($jstable)) { ?>
					jQuery(document).ready(function($) {
						jQuery('.tablesorter').each(function() { 
							$(this).tablesorter(<?php echo apply_filters('kws_gf_directory_tablesorter_options', '') ?>); 
						});
					});
				<?php } else if(isset($jssearch) && $jssearch) { ?>
					function Search(search, sort_field_id, sort_direction){
						if(not_empty(search)) { var search = "&gf_search=" + encodeURIComponent(search); } else {  var search = ''; }
						if(not_empty(sort_field_id)) { var sort = "&sort=" + sort_field_id; } else {  var sort = ''; }
						if(not_empty(sort_direction)) { var dir = "&dir=" + sort_direction; } else {  var dir = ''; }
						var page = '<?php if($wp_rewrite->using_permalinks()) { echo '?'; } else { echo '&'; } ?>page='+<?php echo isset($_GET['page']) ? intval($_GET['page']) : '"1"'; ?>;
						var location = "<?php echo get_permalink(); ?>"+page+search+sort+dir;
						document.location = location;
					}
				<?php } ?>
				</script>
				<link rel="stylesheet" href="<?php echo GFCommon::get_base_url() ?>/css/admin.css" type="text/css" />
			<?php } ?>
			
			<div class="wrap">
				<?php if($icon) { ?><img alt="<?php _e("Gravity Forms", "gravity-forms-addons") ?>" src="<?php echo GFCommon::get_base_url()?>/images/gravity-title-icon-32.png" style="float:left; margin:15px 7px 0 0;"/><?php } ?>
				<?php if($titleshow) { ?><h2><?php echo $titleprefix.$title; ?> </h2><?php } ?>
				<?php if($search && ($lead_count > 0 || !empty($_GET['gf_search']))) { ?>
				<form id="lead_form" method="get" action="<?php echo $formaction; ?>">
					<p class="search-box">
						<label class="hidden" for="lead_search"><?php _e("Search Entries:", "gravity-forms-addons"); ?></label>
						<input type="text" name="gf_search" id="lead_search" value="<?php echo $search_query ?>"<?php if($searchtabindex) { echo ' tabindex="'.intval($searchtabindex).'"';}?> />
						<input type="submit" class="button" id="lead_search_button" value="<?php _e("Search", "gravity-forms-addons") ?>"<?php if($searchtabindex) { echo ' tabindex="'.intval($searchtabindex++).'"';}?> />
					</p>
				</form>
				<?php } 
				
				
				//Displaying paging links if appropriate
						
					if($lead_count > 0 && $showcount || $page_links){
						if($lead_count == 0) { $first_item_index--; }
						?>
					<div class="tablenav">
						<div class="tablenav-pages">
							<?php if($showcount) {
							if(($first_item_index + $page_size) > $lead_count || $page_size <= 0) {
								$second_part = $lead_count;
							} else {
								$second_part = $first_item_index + $page_size;
							}
							?>
							<span class="displaying-num"><?php printf(__("Displaying %d - %d of %d", "gravity-forms-addons"), $first_item_index + 1, $second_part, $lead_count)  ?></span>
							<?php } if($page_links){ echo $page_links; } ?>
						</div>
						<div class="clear"></div>
					</div>
						<?php
				   }
					 
				do_action('kws_gf_before_directory_after_nav', do_action('kws_gf_before_directory_after_nav_form_'.$form_id, $form, $leads, compact($approved,$sort_field,$sort_direction,$search_query,$first_item_index,$page_size,$star,$read,$is_numeric,$start_date,$end_date)));
				?>
					
					<table class="<?php echo $tableclass; ?>" cellspacing="0"<?php if(!empty($tablewidth)) { echo ' width="'.$tablewidth.'"'; } echo $tablestyle ? ' style="'.$tablestyle.'"' : ''; ?>>
					<?php if($thead) {?>
					<thead>
						<tr>
							<?php
							
							$addressesExist = false;
							foreach($columns as $field_id => $field_info){
								$dir = $field_id == 0 ? "DESC" : "ASC"; //default every field so ascending sorting except date_created (id=0)
								if($field_id == $sort_field) { //reverting direction if clicking on the currently sorted field
									$dir = $sort_direction == "ASC" ? "DESC" : "ASC";
								}
								if(is_array($adminonlycolumns) && !in_array($field_id, $adminonlycolumns) || (is_array($adminonlycolumns) && in_array($field_id, $adminonlycolumns) && $showadminonly) || !$showadminonly) {
								if($field_info['type'] == 'address' && $appendaddress && $hideaddresspieces) { $addressesExist = true; continue; }
								?>
								<?php if(isset($jssearch) && $jssearch && !isset($jstable)) { ?>
								<th scope="col" class="manage-column" onclick="Search('<?php echo $search_query ?>', '<?php echo $field_id ?>', '<?php echo $dir ?>');" style="cursor:pointer;"><?php 
								} elseif(isset($jstable) && $jstable) {?>
									<th scope="col" class="manage-column">
								<?php } else { ?>
								<th scope="col" class="manage-column">
								<a href="<?php 
									$searchpage = isset($_GET['page']) ? intval($_GET['page']) : 1;
									echo add_query_arg(array('gf_search' => $search_query, 'sort' => $field_id, 'dir' => $dir, 'page' => $searchpage), get_permalink()); 
								?>"><?php
								}
								if($field_info['type'] == 'id' && $entry) { $label = $entryth; }
								else { $label = $field_info["label"]; }
								
								$label = apply_filters('kws_gf_directory_th', apply_filters('kws_gf_directory_th_'.$field_id, apply_filters('kws_gf_directory_th_'.sanitize_title($label), $label)));
								echo esc_html($label); 
							   if(!isset($jssearch) || !$jssearch && empty($jstable)) { ?>
							   </a>
							   <?php } ?>
							   </th>
								<?php
								}
							}
							
							if($appendaddress && $addressesExist) {
								?>
								<th scope="col" class="manage-column" onclick="Search('<?php echo $search_query ?>', '<?php echo $field_id ?>', '<?php echo $dir ?>');" style="cursor:pointer;"><?php 
								$label = apply_filters('kws_gf_directory_th', apply_filters('kws_gf_directory_th_address', 'Address'));
								echo esc_html($label) 
							   
								 ?></th>
								<?php
							}
							?>
						</tr>
					</thead>
					<?php } ?>
					<tbody class="list:user user-list">
						<?php
						if(sizeof($leads) > 0 && $lead_count > 0){
						
							$field_ids = array_keys($columns);
							
							$evenodd = '';
							foreach($leads as $lead){
								flush();
								echo "\n\t\t\t\t\t\t";
								
								$address = array(); $celltitle = '';
								
								if($approved) { $leadapproved = self::check_approval($lead, $approvedcolumn); }
								
								if((isset($leadapproved) && $leadapproved && $approved) || !$approved) {
									$target = ''; if($linknewwindow && empty($lightboxsettings['images'])) { $target = ' target="_blank"'; }
									$valignattr = ''; if($valign && $directoryview == 'table') { $valignattr = ' valign="'.$valign.'"'; } 
									$nofollow = ''; if($nofollowlinks) { $nofollow = ' rel="nofollow"'; }
									$evenodd = ($evenodd == ' odd') ? ' even' : ' odd';
									$evenodd = apply_filters('kws_gf_directory_evenodd', $evenodd);
										
								?><tr<?php if($showrowids){ ?> id="lead_row_<?php echo $lead["id"] ?>" <?php } ?> class='<?php echo trim($rowclass.$evenodd); echo $lead["is_starred"] ? " featured" : "" ?>'<?php echo $rowstyle ? ' style="'.$rowstyle.'"' : ''; echo $valignattr; ?>><?php
									$class = "";
									$is_first_column = true;
									$full_address = '';
									foreach($field_ids as $field_id) {
										$lightboxclass = '';
										if(!empty($lightboxsettings['images'])) { $lightboxclass = ' class="thickbox colorbox lightbox"';  }
										$value = isset($lead[$field_id]) ? $lead[$field_id] : '';
										$input_type = !empty($columns[$field_id]["inputType"]) ? $columns[$field_id]["inputType"] : $columns[$field_id]["type"];
										switch($input_type){
											
											case "address" :
												$address['id'] = floor((int)$field_id);
												$address[$field_id] = $value;
												if($hideaddresspieces) { $value = NULL; break; }
													break;
												
											case "checkbox" :
												$value = "";
	
												//looping through lead detail values trying to find an item identical to the column label. Mark with a tick if found.
												$lead_field_keys = array_keys($lead);
												foreach($lead_field_keys as $input_id){
													//mark as a tick if input label (from form meta) is equal to submitted value (from lead)
													if(is_numeric($input_id) && absint($input_id) == absint($field_id) && $lead[$input_id] == $columns[$field_id]["label"]){
														$value = "<img src='" . GFCommon::get_base_url() . "/images/tick.png'/>";
													}
												}
											break;
											
											case "fileupload" :
											case "post_image" :
												$valueArray = explode("|:|", $value);
												
												@list($url, $title, $caption, $description) = $valueArray;
												$size = '';
												if(!empty($url)){
													//displaying thumbnail (if file is an image) or an icon based on the extension
													 $icon = self::get_icon_url($url);
													 if(!preg_match('/icon\_image\.gif/ism', $icon)) {
													 	$src = $icon;
													 	if(!empty($getimagesize)) {
															$size = @getimagesize($src);
															$img = "<img src='$src' {$size[3]}/>";
														} else {
															$size = false;
															$img = "<img src='$src' />";
														}
													 } else { // No thickbox for non-images please
													 	switch(strtolower(trim($postimage))) {
													 		case 'image':
													 			$src = $url;
													 			break;
													 		case 'icon':
													 		default:
													 			$src = $icon;
													 			break;
													 	}
													 	if(!empty($getimagesize)) {
															$size = @getimagesize($src);
														} else {
															$size = false;
														}
													 }
													 $img = array(
													 	'src' => $src,
													 	'size' => $size,
													 	'title' => $title,
													 	'caption' => $caption,
													 	'description' => $description,
													 	'url' => esc_attr($url),
													 	'code' => isset($size[3]) ? "<img src='$src' {$size[3]} />" : "<img src='$src' />"
													 );
													 $img = apply_filters('kws_gf_directory_lead_image', apply_filters('kws_gf_directory_lead_image_'.$postimage, apply_filters('kws_gf_directory_lead_image_'.$lead['id'], $img)));

													if(in_array('images', $lightboxsettings) || !empty($lightboxsettings['images'])) {
														$lightboxclass .= ' rel="directory_all directory_images"';
													}
													$value = "<a href='{$url}'{$target}{$lightboxclass}>{$img['code']}</a>";
												}
											break;
	
											case "source_url" :
												if(in_array('urls', $lightboxsettings) || !empty($lightboxsettings['urls'])) {
													$lightboxclass .= ' rel="directory_all directory_urls"';
												}
												if($linkwebsite) {
													$value = "<a href='" . esc_attr($lead["source_url"]) . "'{$target}{$lightboxclass} title='" . esc_attr($lead["source_url"]) . "'$nofollow>.../" .esc_attr(GFCommon::truncate_url($lead["source_url"])) . "</a>";
												} else {
													$value = esc_attr(GFCommon::truncate_url($lead["source_url"]));
												}
											break;
	
											case "textarea" :
											case "post_content" :
											case "post_excerpt" :
												if($fulltext) {
													$long_text = $value = "";
													if(isset($lead[$field_id]) && strlen($lead[$field_id]) >= GFORMS_MAX_FIELD_LENGTH) {
														   $long_text = get_gf_field_value_long($lead["id"], $field_id);
													   }
													if(isset($lead[$field_id])) {
														$value = !empty($long_text) ? $long_text : $lead[$field_id];
													}
													
													if($wpautop) { $value = wpautop($value); };
													
												} else {
													$value = esc_html($value);
												}
											break;
	
											case "date_created" :
												$value = GFCommon::format_date($value, false, $datecreatedformat);
											break;
	
											case "date" :
												$field = RGFormsModel::get_field($form, $field_id);
												if($dateformat) {
													 $value = GFCommon::date_display($value, $dateformat);
												 } else {
												 	$value = GFCommon::date_display($value, $field["dateFormat"]); 
												 }
											break;
											
											case "id" :
												$linkClass = '';
											break;
											
											case "list":
												$field = RGFormsModel::get_field($form, $field_id);
												$value = GFCommon::get_lead_field_display($field, $value);
											break;
											
											default:
	
												$input_type = 'text';
												if(is_email($value) && $linkemail) {$value = "<a href='mailto:$value'$nofollow>$value</a>"; } 
												elseif(preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $value) && $linkwebsite) {
													$href = $value;
													if(!empty($lightboxsettings['images'])) {
														if(in_array('urls', $lightboxsettings) || !empty($lightboxsettings['urls'])) {
															$lightboxclass .= ' rel="directory_all directory_urls"';
														}
														$linkClass = $lightboxclass; 
													} else {
														$linkClass = isset($linkClass) ? $linkClass : '';
													}
													if($truncatelink) {
														$value = apply_filters('kws_gf_directory_anchor_text', $value);
													}
													$value = "<a href='{$href}'{$nofollow}{$target}{$linkClass}>{$value}</a>"; 
												}
												else { $value = esc_html($value); }
										}
										if($is_first_column) { echo "\n"; }
										if($value !== NULL) {
											if(isset($columns["{$field_id}"]['label']) && $hovertitle || $directoryview !== 'table') {
												$celltitle = ' title="'.esc_html(apply_filters('kws_gf_directory_th', apply_filters('kws_gf_directory_th_'.$field_id, apply_filters('kws_gf_directory_th_'.sanitize_title($columns["{$field_id}"]['label']), $columns["{$field_id}"]['label'])))).'"';
											} else {
												$celltitle = '';
											}
										 	echo "\t\t\t\t\t\t\t"; ?><td<?php echo empty($class) ? ' class="'.$input_type.'"' : ' class="'.$input_type.' '.$class.'"'; echo $valignattr; echo $celltitle; ?>><?php 
										 	
										 	
										 	$value = empty($value) ? '&nbsp;' : $value;
											
											if(isset($entrylinkcolumns[floor($field_id)])) {
												$type = $entrylinkcolumns[floor($field_id)];
												if($input_type == 'id' && $entry) {
													$linkvalue = $entrylink;
												} elseif($type === 'label') {
													$linkvalue = $columns["{$field_id}"]['label'];
												} elseif(!empty($type) && $type !== 'on') {
													$linkvalue = str_replace('%value%', $value, $type);
												} else {
													$linkvalue = $value;
												}
										 		$value = self::make_entry_link($options, $linkvalue, $lead['id'], $form_id, $field_id);
											}
											
										 	$value = apply_filters('kws_gf_directory_value', apply_filters('kws_gf_directory_value_'.$input_type, apply_filters('kws_gf_directory_value_'.$field_id, $value)));
										 echo $value;
										 
										?></td><?php
											echo "\n";
											$is_first_column = false;
										}
									}
									
									if(is_array($address) && !empty($address) && $appendaddress) {
										$address = apply_filters('kws_gf_directory_td_address', $address, $linknewwindow);
										if(!is_array($address)) {
											echo "\t\t\t\t\t\t\t".'<td class="address" title="'.esc_html(apply_filters('kws_gf_directory_th', apply_filters('kws_gf_directory_th_address', 'Address'))).'">'.$address.'</td>';
										}
									}
									
								   	?>
							</tr>
								<?php }
							}
						}
						else{
							?>
							<tr>
								<td colspan="<?php echo sizeof($columns); ?>" style="padding:20px;"><?php 
									
									if($search_query) {
										_e("This search returned no results.", "gravity-forms-addons"); 
									} elseif($limituser) {
										_e("This form does not have any visible entries.", "gravity-forms-addons"); 
									} else {
										_e("This form does not have any entries yet.", "gravity-forms-addons"); 
									}
									
								?></td>
							</tr>
							<?php
						}
						?>
					</tbody>
					<?php if($tfoot) { 
						if(isset($jssearch) && $jssearch && !isset($jstable)) {
							$th = '<th scope="col" class="manage-column" onclick="Search(\''.$search_query.'\', \''.$field_id.'\', \''.$dir.'\');" style="cursor:pointer;">';
						} else {
							$th = '<th scope="col" class="manage-column">';
						}
					?>
					<tfoot>
						<tr>
							<?php
							$addressesExist = false;
							foreach($columns as $field_id => $field_info){
								$dir = $field_id == 0 ? "DESC" : "ASC"; //default every field so ascending sorting except date_created (id=0)
								if($field_id == $sort_field) { //reverting direction if clicking on the currently sorted field
									$dir = $sort_direction == "ASC" ? "DESC" : "ASC";
								}
								if(is_array($adminonlycolumns) && !in_array($field_id, $adminonlycolumns) || (is_array($adminonlycolumns) && in_array($field_id, $adminonlycolumns) && $showadminonly) || !$showadminonly) {
								if($field_info['type'] == 'address' && $appendaddress && $hideaddresspieces) { $addressesExist = true; continue; }
								
								echo $th;
								
								if($field_info['type'] == 'id' && $entry) { $label = $entryth; }
								else { $label = $field_info["label"]; }
								
								$label = apply_filters('kws_gf_directory_th', apply_filters('kws_gf_directory_th_'.$field_id, apply_filters('kws_gf_directory_th_'.sanitize_title($label), $label)));
								echo esc_html($label) 
							   
								 ?></th>
								<?php
								}
							}
							if($appendaddress && $addressesExist) {
								?>
								<th scope="col" class="manage-column" onclick="Search('<?php echo $search_query ?>', '<?php echo $field_id ?>', '<?php echo $dir ?>');" style="cursor:pointer;"><?php 
								$label = apply_filters('kws_gf_directory_th', apply_filters('kws_gf_directory_th_address', 'Address'));
								echo esc_html($label) 
							   
								 ?></th>
								<?php
							}
							?>
						</tr>
						<?php if(!empty($credit)) { self::get_credit_link(sizeof($columns)); } ?>
					</tfoot>
					<?php } ?>
					</table>
					<?php
					
						do_action('kws_gf_after_directory_before_nav', do_action('kws_gf_after_directory_before_nav_form_'.$form_id, $form, $leads, compact($approved,$sort_field,$sort_direction,$search_query,$first_item_index,$page_size,$star,$read,$is_numeric,$start_date,$end_date)));
					
						
					//Displaying paging links if appropriate
						
					if($lead_count > 0 && $showcount || $page_links){
						if($lead_count == 0) { $first_item_index--; }
						?>
					<div class="tablenav">
						<div class="tablenav-pages">
							<?php if($showcount) {
							if(($first_item_index + $page_size) > $lead_count || $page_size <= 0) {
								$second_part = $lead_count;
							} else {
								$second_part = $first_item_index + $page_size;
							}
							?>
							<span class="displaying-num"><?php printf(__("Displaying %d - %d of %d", "gravity-forms-addons"), $first_item_index + 1, $second_part, $lead_count)  ?></span>
							<?php } if($page_links){ echo $page_links; } ?>
						</div>
						<div class="clear"></div>
					</div>
						<?php
				   }
				   
				  ?> 
			</div>
			<?php
			if(empty($credit)) {
				echo "\n".'<!-- Directory generated by Gravity Forms Directory & Addons : http://wordpress.org/extend/plugins/gravity-forms-addons/ -->'."\n";
			}
				
			do_action('kws_gf_after_directory', do_action('kws_gf_after_directory_form_'.$form_id, $form, $leads, compact($approved,$sort_field,$sort_direction,$search_query,$first_item_index,$page_size,$star,$read,$is_numeric,$start_date,$end_date)));
			
			$content = ob_get_contents(); // Get the output
			ob_end_clean(); // Clear the cache
			
			// If the form is form #2, two filters are applied: `kws_gf_directory_output_2` and `kws_gf_directory_output`
			$content = apply_filters('kws_gf_directory_output', apply_filters('kws_gf_directory_output_'.$form_id, self::pseudo_filter($content, $directoryview)));
			
			return $content; // Return it!
	}
    
    public function get_credit_link($columns = 1) {
    	global $post;// prevents calling before <HTML>
    	if(empty($post) || is_admin()) { return; }
    	
    	$settings = self::get_settings();
    	
    	// Only show credit link if the user has saved settings;
    	// this prevents existing directories adding a link without user action.
    	if(isset($settings['version'])) {
    		echo "<tr><th colspan='{$columns}'>".self::attr()."</th></tr>";
    	}
    }
    
    public function attr($default = '<span class="kws_gf_credit" style="font-weight:normal; text-align:center; display:block; margin:0 auto;">Powered by <a href="http://seodenver.com/gravity-forms-addons/">Gravity Forms Directory</a></span>') {
		
		include_once(ABSPATH . WPINC . '/feed.php');
		
		if(!$rss = fetch_feed('http://www.katzwebservices.com/development/attribution.php?site='.htmlentities(substr(get_bloginfo('url'), is_ssl() ? 8 : 7)).'&from=kws_gf_addons&version='.self::$version) ) { return $default; }
		
		if(!is_wp_error($rss)) {
			// This list is only missing 'style', 'id', and 'class' so that those don't get stripped.
			$strip = array('bgsound','expr','onclick','onerror','onfinish','onmouseover','onmouseout','onfocus','onblur','lowsrc','dynsrc');
			$rss->strip_attributes($strip); $rss_items = $rss->get_items(0, 1);
			foreach ( $rss_items as $item ) {
				return str_replace(array("\n", "\r"), ' ', $item->get_description());
			}
		}
		
		return $default;
	}
    
    public function directory_tooltips($tooltips){
   		$tooltips["kws_gf_directory_use_as_link_to_single_entry"] = __(sprintf("%sLink to single entry using this field%sIf you would like to link to the single entry view using this link, check the box.", '<h6>', '</h6>'), 'gravity-forms-addons');
   		$tooltips['kws_gf_directory_hide_in_directory_view'] = __(sprintf('%sHide in Directory View%sIf checked, this field will not be shown in the directory view. If this field is Admin Only (set in the Advanced tab), it will be hidden in the directory view unless "Show Admin-Only columns" is enabled in the directory. Even if "Show Admin-Only columns" is enabled, checking this box will hide the column in the directory view.', '<h6>', '</h6>'), 'gravity-forms-addons');
   		$tooltips['kws_gf_directory_hide_in_single_entry_view'] = __(sprintf('%sHide in Single Entry View%sIf checked, this field will not be shown in the single entry view of the directory.', '<h6>', '</h6>'), 'gravity-forms-addons');
   		return $tooltips;
	}

    public function directory_add_default_values() {
			global $_gform_directory_approvedcolumn, $process_bulk_update_message; 
			if(!self::is_gravity_page('gf_entries') && (!self::is_gravity_page('gf_edit_forms') || !isset($_GET['id']))) { return; }
			 ?>
		<style type="text/css">
		
		.lead_approved .toggleApproved {
			background: url(<?php echo GFCommon::get_base_url() ?>/images/tick.png) left top no-repeat;
		}
		.toggleApproved {
			background: url(<?php echo GFCommon::get_base_url() ?>/images/cross.png) left top no-repeat;
			width: 16px;
			height: 16px;
			display: block;
			text-indent: -9999px;
			overflow: hidden;
		}
		</style>
		<script type="text/javascript">
			
			<?php 
			
			$formID = RGForms::get("id");
	        if(empty($formID)) {
		        $forms = RGFormsModel::get_forms(null, "title");
	            $formID = $forms[0]->id;
	        }
		   	
		   	$_gform_directory_approvedcolumn = empty($_gform_directory_approvedcolumn) ? self::globals_get_approved_column($formID) : $_gform_directory_approvedcolumn;
		   	     		
			if(!empty($_gform_directory_approvedcolumn)) {
			    echo 'formID = '.$formID.';';
		       ?>
		        
		    function UpdateApproved(lead_id, approved) {
		    	var mysack = new sack("<?php echo admin_url("admin-ajax.php")?>" );
		        mysack.execute = 1;
		        mysack.method = 'POST';
		        mysack.setVar( "action", "rg_update_approved" );
		        mysack.setVar( "rg_update_approved", "<?php echo wp_create_nonce("rg_update_approved") ?>" );
		        mysack.setVar( "lead_id", lead_id);
		        mysack.setVar( "form_id", formID);
		        mysack.setVar( "approved", approved);
		        mysack.encVar( "cookie", document.cookie, false );
		        mysack.onError = function() { alert('<?php echo esc_js(__("Ajax error while setting lead meta", "gravity-forms-addons")) ?>' )};
		        mysack.runAJAX();
		        
		        return true;
		    }
		 
		 <?php 
		 	
		 if(!function_exists('gform_get_meta')) { ?>
		 	
		    function displayMessage(message, messageClass, container){

                hideMessage(container, true);

                var messageBox = jQuery('<div class="message ' + messageClass + '" style="display:none;"><p>' + message + '</p></div>');
                jQuery(messageBox).prependTo(container).slideDown();

                if(messageClass == 'updated')
                    messageTimeout = setTimeout(function(){ hideMessage(container, false); }, 10000);

            }
            
            function hideMessage(container, messageQueued){

                var messageBox = jQuery(container).find('.message');

                if(messageQueued)
                    jQuery(messageBox).remove();
                else
                    jQuery(messageBox).slideUp(function(){ jQuery(this).remove(); });

            }
            
         <?php } // end meta check for 1.6         ?>
		    
			jQuery(document).ready(function($) {
		    	
		    	<?php if(!empty($process_bulk_update_message)) { ?>
			    	displayMessage('<?php _e($process_bulk_update_message); ?>', 'updated', '#lead_form');
			    <?php } ?>
		    	
		    	$("#bulk_action,#bulk_action2").append('<optgroup label="Directory"><option value="approve-'+formID+'"><?php _e('Approve', 'gravity-forms-addons'); ?></option><option value="unapprove-'+formID+'"><?php _e('Un-approve', 'gravity-forms-addons'); ?></option></optgroup>');
		    	
		    	var approveTitle = '<?php _e('Entry not approved for directory viewing. Click to approve this entry.', 'gravity-forms-addons'); ?>';
		    	var unapproveTitle = '<?php _e('Entry approved for directory viewing. Click to un-approve this entry.', 'gravity-forms-addons'); ?>';
		    	
		    	$('.toggleApproved').live('click load', function(e) {
		    		e.preventDefault();
		    		
		    		var $tr = $(this).parents('tr');
					var is_approved = $tr.is(".lead_approved");
		
					if(e.type == 'click') {
				        $tr.toggleClass("lead_approved");
				    }
		
					// Update the title and screen-reader text
			        if(!is_approved) { $(this).text('X').attr('title', unapproveTitle); } 
			        else { $(this).text('O').attr('title', approveTitle); }
					
					if(e.type == 'click') {
				        UpdateApproved($('th input[type="checkbox"]', $tr).val(), is_approved ? 0 : 'Approved');	        
				    }
					
					UpdateApprovedColumns($(this).parents('table'), false);
					
					return false;
					
		    	});
		    	
		    	// We want to make sure that the checkboxes go away even if the Approved column is showing.
		    	// They will be in sync when loaded, so only upon click will we process.
		    	function UpdateApprovedColumns($table, onLoad) {
					var colIndex = $('th:contains("Approved")', $table).index() - 1;
					
					$('tr', $table).each(function() {
						if($(this).is('.lead_approved') || (onLoad && $("input.lead_approved", $(this)).length > 0)) {
							if(onLoad && $(this).not('.lead_approved')) { $(this).addClass('lead_approved'); }
							$('td:visible:eq('+colIndex+')', $(this)).html("<img src='<?php echo GFCommon::get_base_url(); ?>/images/tick.png'/>"); 
						} else {
							if(onLoad && $(this).is('.lead_approved')) { $(this).removeClass('lead_approved'); }
							$('td:visible:eq('+colIndex+')', $(this)).html('');
						}
					});
		    	}
		    	
		    	$('th.column-cb:not(:has("input[type=checkbox]"))', 'table:has(tbody.user-list)').after('<th class="column manage-column check-column"><a href="<?php echo add_query_arg(array('sort' => $_gform_directory_approvedcolumn)); ?>"><img src="<?php echo WP_PLUGIN_URL . "/" . basename(dirname(__FILE__)); ?>/form-button-1.png" style="text-align:center; margin:0 auto; display:block;" title="<?php _e('Show entry in directory view?'); ?>" /></span></a></th>');
				    	
		    	$('td:has(img[src*="star"])', 'table:has(tbody.user-list)').after('<td><a href="#" class="toggleApproved" title="'+approveTitle+'">X</a></td>');
		    	
		    	$('tr:has(input.lead_approved)', 'table:has(tbody.user-list)').addClass('lead_approved').find('a.toggleApproved').attr('title', unapproveTitle).text('O');
		    	
		    	UpdateApprovedColumns($('table'), true);
		    	    	
		    });
			<?php } // end if(!empty($_gform_directory_approvedcolumn)) check ?>
			function SetDefaultValues_entrylink(field) {
					field.label = "<?php _e("Go to Entry", "gravity-forms-addons"); ?>";
		            
		            field.adminOnly = true;
		            
		            if(!field.choices)
		                field.choices = new Array(new Choice("<?php _e("Go to Entry", "gravity-forms-addons"); ?>"));
		
		            field.inputs = new Array();
		            for(var i=1; i<=field.choices.length; i++)
		                field.inputs.push(new Input(field.id + (i/10), field.choices[i-1].text));
		            
		            field.hideInSingle = true;
		            field.useAsEntryLink = 'label';
		            
		            field.type = 'hidden';
		            field.disableMargins = true;
		            
		           	return field;
			}
			
			function SetDefaultValues_usereditlink(field) {
					field.label = "<?php _e("Edit", "gravity-forms-addons"); ?>";
		            
		            field.adminOnly = true;
		            
		            if(!field.choices)
		                field.choices = new Array(new Choice("<?php _e("User Edit", "gravity-forms-addons"); ?>"));
		
		            field.inputs = new Array();
		            for(var i=1; i<=field.choices.length; i++)
		                field.inputs.push(new Input(field.id + (i/10), field.choices[i-1].text));
		            
		            field.hideInSingle = false;
		            field.useAsEntryLink = false;
		            
		            field.type = 'hidden';
		            field.disableMargins = 2;
		            
		           	return field;
			}
			
			function SetDefaultValues_directoryapproved(field) {
					field.label = "<?php _e("Approved? (Admin-only)", "gravity-forms-addons"); ?>";
		            
					field.adminLabel = "<?php _e("Approved?", "gravity-forms-addons"); ?>";
					field.adminOnly = true;
					
		            if(!field.choices)
		                field.choices = new Array(new Choice("<?php _e("Approved", "gravity-forms-addons"); ?>"));
		
		            field.inputs = new Array();
		            for(var i=1; i<=field.choices.length; i++)
		                field.inputs.push(new Input(field.id + (i/10), field.choices[i-1].text));
		            
		            field.hideInDirectory = true;
		            field.hideInSingle = true;  
		            field.type = 'checkbox';
		            
		           	return field;
			} 
		</script><?php
	}
	
	public function add_lead_approved_hidden_input($value, $lead, $field = '') {
		global $_gform_directory_processed_meta, $_gform_directory_approvedcolumn;

		if(!in_array($lead['id'], $_gform_directory_processed_meta)) {
			$_gform_directory_processed_meta[] = $lead['id'];
			if(empty($_gform_directory_approvedcolumn)) {
				$forms = RGFormsModel::get_forms(null, "title");
	        	$_gform_directory_approvedcolumn = self::globals_get_approved_column($forms[0]->id);
			}
			if(self::check_approval($lead, $_gform_directory_approvedcolumn)) {
				echo '<td style="display:none;"><input type="hidden" class="lead_approved" id="lead_approved_'.$lead['id'].'" value="true" /></td>';
			}
		}
		
		return $value;
	}

	
    public function globals_get_approved_column($formID = 0) {    
	    global $_gform_directory_processed_meta, $_gform_directory_approvedcolumn, $_gform_directory_activeform;
	    
	        $_gform_directory_processed_meta = array();
	        
	        if(empty($formID)) {
		        $formID = RGForms::get("id");
		        
		        if(empty($formID)) {
			        $forms = RGFormsModel::get_forms(null, "title");
		            $formID = $forms[0]->id;
		        }
		    }
	        
	        if(!empty($formID)) {
	        	$_gform_directory_activeform = RGFormsModel::get_form_meta($formID);
	        } else if(isset($_GET['id'])) {
	        	$_gform_directory_activeform = RGFormsModel::get_form_meta($_GET['id']);
	        }
	        
	        $_gform_directory_approvedcolumn = self::get_approved_column($_gform_directory_activeform);
	        
	        return $_gform_directory_approvedcolumn;
	}
	
	public function get_approved_column($form) {
		if(!is_array($form)) { return false; }
		
		foreach(@$form['fields'] as $key=>$col) {
			if(isset($col['inputs']) && is_array($col['inputs'])) {
				foreach($col['inputs'] as $key2=>$input) {
					if(strtolower($input['label']) == 'approved' && $col['type'] == 'checkbox' && !empty($col['adminOnly'])) {
						return $input['id'];
					}
				}
			}
		}
		
		foreach(@$form['fields'] as $key=>$col) {
			if(isset($col['label']) && strtolower($col['label']) == 'approved' && $col['type'] == 'checkbox') {
				if(isset($col['inputs'][0]['id']))
				return $key;
			}
		}

		return false;
	}
    
    
    public function process_bulk_update() {
		global $process_bulk_update_message;
		
        if(RGForms::post("action") === 'bulk'){
            check_admin_referer('gforms_entry_list', 'gforms_entry_list');

            $bulk_action = !empty($_POST["bulk_action"]) ? $_POST["bulk_action"] : $_POST["bulk_action2"];
            $leads = $_POST["lead"];

            $entry_count = count($leads) > 1 ? sprintf(__("%d entries", "gravityforms"), count($leads)) : __("1 entry", "gravityforms");

			$bulk_action = explode('-', $bulk_action);
			if(!isset($bulk_action[1]) || empty($leads)) { return false; }
			
            switch($bulk_action[0]){
                case "approve":
                    self::directory_update_bulk($leads, 1, $bulk_action[1]);
                    $process_bulk_update_message = sprintf(__("%s approved.", "gravity-forms-addons"), $entry_count);
                break;

                case "unapprove":
            		self::directory_update_bulk($leads, 0, $bulk_action[1]);
                    $process_bulk_update_message = sprintf(__("%s un-approved.", "gravity-forms-addons"), $entry_count);
                break;
			}
		}
	}
    
    private function directory_update_bulk($leads, $approved, $form_id) {
    	global $_gform_directory_approvedcolumn;
    	
    	if(empty($leads)) { return false; }
    	
    	$_gform_directory_approvedcolumn = empty($_gform_directory_approvedcolumn) ? self::globals_get_approved_column($_POST['form_id']) : $_gform_directory_approvedcolumn;

		$approved = empty($approved) ? 0 : 'Approved';
    	foreach($leads as $lead_id) {
			self::directory_update_approved($lead_id, $approved, $form_id);
		}
    }
    
    public function directory_update_approved_hook(){
    	global $_gform_directory_approvedcolumn;
		check_ajax_referer('rg_update_approved','rg_update_approved');
		if(!empty($_POST["lead_id"])) {
			$_gform_directory_approvedcolumn = empty($_gform_directory_approvedcolumn) ? self::globals_get_approved_column($_POST['form_id']) : $_gform_directory_approvedcolumn;
		    self::directory_update_approved($_POST["lead_id"], $_POST["approved"], $_POST['form_id'], $_gform_directory_approvedcolumn);
		}
	}
    
    public function settings_link( $links, $file ) {
        static $this_plugin;
        if( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);
        if ( $file == $this_plugin ) {
            $settings_link = '<a href="' . admin_url( 'admin.php?page=gf_settings&addon=Directory+%26+Addons' ) . '">' . __('Settings', 'gravity-forms-addons') . '</a>';
            array_unshift( $links, $settings_link ); // before other links
        }
        return $links;
    }
    
    //Returns true if the current page is an Feed pages. Returns false if not
    private static function is_directory_page(){
    	if(empty($_GET["page"])) { return false; }
        $current_page = trim(strtolower($_GET["page"]));
        $directory_pages = array("gf_directory");

        return in_array($current_page, $directory_pages);
    }

    //Creates directory left nav menu under Forms
    public static function create_menu($menus){

        // Adding submenu if user has access
        $permission = self::has_access("gravityforms_directory");
        if(!empty($permission))
            $menus[] = array("name" => "gf_settings&addon=Directory+%26+Addons", "label" => __("Directory &amp; Addons", "gravity-forms-addons"), "callback" =>  array("GFDirectory", "settings_page"), "permission" => $permission);

        return $menus;
    }
	
	public function get_settings() {
		return get_option("gf_addons_settings", array(
        		"directory" => true,
        		"directory_defaults" => array(),
        		"referrer" => false,
        		"widget" => true,
        		"modify_admin" => array(
            		'expand' => true,
           			'toggle' => true,
            		'edit' => true,
            		'ids' => true
            	),
            	"saved" => false,
            	"version" => self::$version
        	)
        );
	}
	
    public static function settings_page(){
		$message = $validimage = false; global $plugin_page;

        if(isset($_POST["gf_addons_submit"])){
            check_admin_referer("update", "gf_directory_update");
            
            $settings = array(
            	"directory" => isset($_POST["gf_addons_directory"]),
            	"referrer" => isset($_POST["gf_addons_referrer"]),
            	"directory_defaults" => self::directory_defaults($_POST['gf_addons_directory_defaults'], true),
            	"widget" => isset($_POST["gf_addons_widget"]),
            	"modify_admin" => isset($_POST["gf_addons_modify_admin"]) ? $_POST["gf_addons_modify_admin"] : array(),
            	"version" => self::$version,
            	"saved" => true
            );
            $message = __('Settings saved.', 'gravity-forms-addons');
            update_option("gf_addons_settings", $settings);
        } else {
           $settings = self::get_settings();
	    }

        ?>
        <style type="text/css">
            .ul-square li { list-style: square!important; }
            .ol-decimal li { list-style: decimal!important; }
            .form-table label { font-size: 1em!important; margin: .4em 0; display: block;}
            li.setting-container { border: none!important; }
            #kws_gf_donate {
				float: right;
				width: 300px;
				padding: 0 10px;
				color: #333;
				margin-bottom: 10px;
			}
			#kws_gf_donate .button-primary {
				display:block; float:left; margin:5px 0; text-align:center;
			}
			#kws_gf_donate img {
				float: left;
				margin-right: 10px;
				margin-bottom: .5em;
				-moz-border-radius: 5px;
				-webkit-border-radius: 5px;
			}

        </style>
        <script type="text/javascript">
        	jQuery('document').ready(function($) {
				$('#kws_gf_advanced_settings').show();
				$('a:contains(Directory)', $('ul.subsubsub')).css('font-weight', 'bold');
				$('.wp-submenu li.current, .wp-submenu li.current a').removeClass('current');
				$('a:contains(Directory)', $('.wp-submenu')).addClass('current').parent('li').addClass('current');
				
				$('a.kws_gf_advanced_settings').hide(); //click(function(e) {  e.preventDefault(); jQuery('#kws_gf_advanced_settings').slideToggle(); return false; });
				
				$('#kws_gf_advanced_settings').change(function() {
					if($("#gf_settings_thead:checked").length || $("#gf_settings_tfoot:checked").length) {
						$('#gf_settings_jssearch').parents('li').show();
					} else {
						$('#gf_settings_jssearch').parents('li').hide();
					}
				}).trigger('change');
				
				$('label[for=gf_addons_directory]').live('load click', function() {
					if($('#gf_addons_directory').is(":checked")) {
						$("tr#directory_settings_row").show();
					} else {
						$("tr#directory_settings_row").hide();
					}
				}).trigger('load');
				
				$('#kws_gf_instructions_button').click(function(e) {
					e.preventDefault();
					visible = $('#kws_gf_instructions').is(':visible');
					if(!visible) { $('#kws_gf_donate').slideUp(150); }
					$('#kws_gf_instructions').slideToggle(function() {
						var $this = $(this);
						var $that = $('#kws_gf_instructions_button');
						$that.text(function() {
							if(visible) {
								$('#kws_gf_donate').slideDown(100);
								return '<?php _e('Hide Instructions', 'gravity-forms-addons'); ?>';
							} else {
								return '<?php _e('View Directory Instructions', 'gravity-forms-addons'); ?>';
							}
						});
					});
					return false;
				});
				
				$('#message.fade').delay(1000).fadeOut('slow');
				
			});
		</script>
		<div class="wrap">
		<?php 
			if($plugin_page !== 'gf_settings') {
			
				echo '<h2>'.__('Gravity Forms Directory Add-on',"gravity-forms-addons").'</h2>';
			}
			if($message) { 
				echo "<div class='fade below-h2 updated' id='message'>".wpautop($message)."</div>";
			}
		
		// if you must, you can filter this out...
		if(apply_filters('kws_gf_show_donate_box', true)) {
		?>
		<div id="kws_gf_donate" class="alert_gray"<?php echo isset($_GET['viewinstructions']) ? ' style="display:none;"' : ''; ?>>
			<p>
			<?php if(!is_ssl()) {?><img src="http://www.gravatar.com/avatar/f0f175f8545912adbdab86f0b586f4c3?s=64" alt="Zack Katz, plugin author" height="64" width="64" /> <?php } _e('Hi there! If you find this plugin useful, consider showing your appreciation by making a small donation to its author!', 'gravity-forms-addons'); ?>
			<a href="http://katz.si/35" target="_blank" class="button button-primary"><?php _e('Donate using PayPal', 'gravity-forms-addons'); ?></a>
			</p>
		</div>
		<?php } ?>
		<p class="submit"><span style="padding-right:.5em;" class="description">Need help getting started?</span> <a href="#" class="button button-secondary" id="kws_gf_instructions_button"><?php 
			if(!empty($settings['saved']) && !isset($_REQUEST['viewinstructions'])) {
				_e('View Directory Instructions', 'gravity-forms-addons'); 
			} else {
				_e('Hide Directory Instructions', 'gravity-forms-addons'); 
			}
		?></a></p>
		
		<div id="kws_gf_instructions"<?php if(!empty($settings['saved']) && !isset($_REQUEST['viewinstructions'])) {?>  class="hide-if-js clear" <?php } ?>>
			<div class="delete-alert alert_gray">
				<div class="alignright" style="margin:1em 1.2em;">
					<iframe width="400" height="255" src="http<?php echo is_ssl() ? 's' : '';?>://www.youtube.com/embed/PMI7Jb-RP2I?hd=1" frameborder="0" allowfullscreen></iframe>
				</div>
				<h3>To integrate a form with Directory:</h3>
				<ol class="ol-decimal">
					<li>Go to the post or page where you would like to add the directory.</li>
					<li><?php echo do_shortcode('[caption align="alignright" caption="The \'Add a Gravity Form Button\'" width="100"]<img src="'.WP_PLUGIN_URL . "/" . basename(dirname(__FILE__)).'/form-button-1.png" style="display:block; text-align:center!important; margin:0 auto!important;" width="39" height="36" />[/caption]'); _e('Click the "Add a Gravity Forms Directory" button above the editor (shown at right enlarged)', 'gravity-forms-addons'); ?></li>
					<li>Choose a form from the drop-down menu and configure settings as you would like them.</li>
					<li>Click "Insert Directory". A "shortcode" should appear in the content editor that looks similar to <code>[directory form="#"]</code></li>
					<li>Save the form</li>
				</ol>
				
				<h4><?php _e('Configuring Fields &amp; Columns', "gravity-forms-addons"); ?></h4>
			
			
				<?php echo wpautop(__('When editing a form, click on a field to expand the field. Next, click the "Directory" tab. There, you will find options to:',"gravity-forms-addons")); ?>
				
		        <ul class="ul-square">
				        <li><?php _e("Choose whether you would like the field to be a link to the Single Entry View;", "gravity-forms-addons"); ?></li>
				        <li><?php _e("Hide the field in Directory View; and", "gravity-forms-addons"); ?></li>
				        <li><?php _e("Hide the field in Single Entry View", "gravity-forms-addons"); ?></li>
				</ul>
				
			</div>
			
			<div class="hr-divider"></div>
	    </div>
        <form method="post" action="" class="clear">
            <?php wp_nonce_field("update", "gf_directory_update") ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="gf_addons_directory"><?php _e("Gravity Forms Directory", "gravity-forms-addons"); ?></label> </th>
                    <td>
                        <label for="gf_addons_directory" class="howto"><input type="checkbox" id="gf_addons_directory" name="gf_addons_directory" <?php checked($settings["directory"]); ?> /> <?php _e('Enable Gravity Forms Directory capabilities', 'gravity-forms-addons'); ?></label>
                    </td>
                </tr>
                <tr id="directory_settings_row">
                	<th scope="row"></th>
                	<td>
                		<h2 style="margin-bottom:0; padding-bottom:0;"><?php _e("Directory Default Settings", "gravity-forms-addons"); ?></h2>
                		<h3><?php _e("These defaults can be over-written when inserting a directory.", "gravity-forms-addons"); ?></h3>
                		
                		<?php 
                		self::make_popup_options(false);
                		?>
                		<div class="hr-divider"></div>
                	</td>
                </tr>
                <tr>
                    <th scope="row"><label for="gf_addons_referrer"><?php _e("Add Referrer Data to Emails", "gravity-forms-addons"); ?></label> </th>
                    <td>
                        <label for="gf_addons_referrer"><input type="checkbox" id="gf_addons_referrer" name="gf_addons_referrer" <?php checked($settings["referrer"]); ?> /> Adds referrer data to entries, including the path the user took to get to the form before submitting.</label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="gf_addons_widget"><?php _e("Load Addons Widget", "gravity-forms-addons"); ?></label> </th>
                    <td>
                        <label for="gf_addons_widget"><input type="checkbox" id="gf_addons_widget" name="gf_addons_widget" <?php checked($settings["widget"]); ?> /> Load the <a href="http://yoast.com/gravity-forms-widget-extras/" rel="nofollow">Gravity Forms Widget by Yoast</a></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="gf_addons_modify_admin"><?php _e("Modify Gravity Forms Admin", "gravity-forms-addons"); ?></label> </th>
                    <td>
                       <ul> 
	                        <li><label for="gf_addons_modify_admin_expand"><input type="checkbox" id="gf_addons_modify_admin_expand" name="gf_addons_modify_admin[expand]" <?php checked(isset($settings["modify_admin"]['expand'])); ?> /> <?php _e("Show option to expand Form Editor Field boxes", "gravity-forms-addons"); ?></label></li>
	                        
	                        <li><label for="gf_addons_modify_admin_toggle"><input type="checkbox" id="gf_addons_modify_admin_toggle" name="gf_addons_modify_admin[toggle]" <?php checked(isset($settings["modify_admin"]['toggle'])); ?> /> <?php _e('When clicking Form Editor Field boxes, toggle open and closed instead of "accordion mode" (closing all except the clicked box).', "gravity-forms-addons"); ?></label></li>
	                        
	                        <li><label for="gf_addons_modify_admin_edit"><input type="checkbox" id="gf_addons_modify_admin_edit" name="gf_addons_modify_admin[edit]" <?php checked(isset($settings["modify_admin"]['edit'])); ?> /> <?php _e(sprintf("Makes possible direct editing of entries from %sEntries list view%s", '<a href="'.admin_url('admin.php?page=gf_entries').'">', '</a>'), "gravity-forms-addons"); ?></label></li>
	                        
	                        <li><label for="gf_addons_modify_admin_ids"><input type="checkbox" id="gf_addons_modify_admin_ids" name="gf_addons_modify_admin[ids]" <?php checked(isset($settings["modify_admin"]['ids'])); ?> /> <?php _e(sprintf("Adds a link in the Forms list view to view form IDs", '<a href="'.admin_url('admin.php?page=gf_edit_forms').'">', '</a>'), "gravity-forms-addons"); ?></label></li>
                      </ul>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" ><input type="submit" name="gf_addons_submit" class="button-primary" value="<?php _e("Save Settings", "gravity-forms-addons") ?>" /></td>
                </tr>
            </table>
        </form>
        </div>
        <?php
    }
		
    public static function disable_directory(){
        delete_option("gf_directory_oid");
    }

    public static function uninstall(){

        if(!GFDirectory::has_access("gravityforms_directory_uninstall"))
            (__("You don't have adequate permission to uninstall Directory Add-On.", "gravity-forms-addons"));

        //removing options
        delete_option("gf_addons_settings");

        //Deactivating plugin
        $plugin = "gravity-forms-addons/gravity-forms-addons.php";
        deactivate_plugins($plugin);
        update_option('recently_activated', array($plugin => time()) + (array)get_option('recently_activated'));
    }

    private static function is_gravityforms_supported(){
        if(class_exists("GFCommon")){
            $is_correct_version = version_compare(GFCommon::$version, self::$min_gravityforms_version, ">=");
            return $is_correct_version;
        }
        else{
            return false;
        }
    }
	
    protected static function has_access($required_permission){
        $has_members_plugin = function_exists('members_get_capabilities');
        $has_access = $has_members_plugin ? current_user_can($required_permission) : current_user_can("level_7");
        if($has_access)
            return $has_members_plugin ? $required_permission : "level_7";
        else
            return false;
    }

    //Returns the url of the plugin's root folder
    protected function get_base_url(){
        return plugins_url(null, __FILE__);
    }

    //Returns the physical path of the plugin's root folder
    protected function get_base_path(){
        $folder = basename(dirname(__FILE__));
        return WP_PLUGIN_DIR . "/" . $folder;
    }

	public static function get_leads($form_id, $sort_field_number=0, $sort_direction='DESC', $search='', $offset=0, $page_size=30, $star=null, $read=null, $is_numeric_sort = false, $start_date=null, $end_date=null, $status='active', $approvedcolumn, $limituser = false) {
        global $wpdb;

        if($sort_field_number == 0)
            $sort_field_number = "date_created";

        if(is_numeric($sort_field_number))
            $sql = self::sort_by_custom_field_query($form_id, $sort_field_number, $sort_direction, $search, $offset, $page_size, $star, $read, $is_numeric_sort, $status, $approvedcolumn, $limituser);
        else
            $sql = self::sort_by_default_field_query($form_id, $sort_field_number, $sort_direction, $search, $offset, $page_size, $star, $read, $is_numeric_sort, $start_date, $end_date, $status, $approvedcolumn, $limituser);
		
		//initializing rownum
        $wpdb->query("select @rownum:=0");

        //getting results
        
        $results = $wpdb->get_results($sql);
        
        $return = '';
		if(function_exists('gform_get_meta')) {
			$return = @RGFormsModel::build_lead_array($results); // This is a private function until 1.6
		}
		
		if(!is_array($return)) {
			$return = self::build_lead_array($results);
		}
		
		$return = apply_filters('kws_gf_directory_lead_filter', $return, $form_id, $sort_field_number, $sort_direction, $search, $offset, $page_size, $star, $read, $is_numeric_sort, $start_date, $end_date, $status, $approvedcolumn, $limituser);
		
        return $return;
    }
    
    function is_current_user( $lead = array() ) {
		global $current_user;
		get_currentuserinfo();
		return ( (int)$current_user->ID === (int)$lead["created_by"]) ;
	}
	
	function show_only_user_entries($leads = array()) {
		return array_filter($leads, array('GFDirectory', 'is_current_user'));
	}
    
    private static function sort_by_custom_field_query($form_id, $sort_field_number=0, $sort_direction='DESC', $search='', $offset=0, $page_size=30, $star=null, $read=null, $is_numeric_sort = false, $status='active', $approvedcolumn = null, $limituser = false){
        global $wpdb, $current_user;
        if(!is_numeric($form_id) || !is_numeric($sort_field_number)|| !is_numeric($offset)|| !is_numeric($page_size))
            return "";

        $lead_detail_table_name = RGFormsModel::get_lead_details_table_name();
        $lead_table_name = RGFormsModel::get_lead_table_name();

        $orderby = $is_numeric_sort ? "ORDER BY query, (value+0) $sort_direction" : "ORDER BY query, value $sort_direction";

        //$search = empty($search) ? "" : "WHERE d.value LIKE '%$search%' ";
        $search_term = "%$search%";
        $search_filter = empty($search) ? "" : $wpdb->prepare("WHERE d.value LIKE %s", $search_term);

        //starred clause
        $where = empty($search) ? "WHERE" : "AND";
        $search_filter .= $star !== null && $status == 'active' ? $wpdb->prepare("$where is_starred=%d AND status='active' ", $star) : "";

        //read clause
        $where = empty($search) ? "WHERE" : "AND";
        $search_filter .= $read !== null && $status == 'active' ? $wpdb->prepare("$where is_read=%d AND status='active' ", $read) : "";

		//status clause
        if(function_exists('gform_get_meta')) {
        	$where = empty($search) ? "WHERE" : "AND";
	        $search_filter .= $wpdb->prepare("$where status=%s ", $status);
	    }
		
		if($limituser) {
			get_currentuserinfo();
			if((int)$current_user->ID !== 0 || ($current_user->ID === 0 && apply_filters('kws_gf_show_entries_if_not_logged_in', apply_filters('kws_gf_treat_not_logged_in_as_user', true)))) {
				$where = empty($search_filter) ? "WHERE" : "AND";
	        	if((int)$current_user->ID === 0) {
	        		$search_filter .= $wpdb->prepare("$where (created_by IS NULL OR created_by=%d)", $current_user->ID);
	        	} else {
	        		$search_filter .= $wpdb->prepare("$where l.created_by=%d ", $current_user->ID);
	        	}
			} else {
				return false;
			}
		}
		
        $field_number_min = $sort_field_number - 0.001;
        $field_number_max = $sort_field_number + 0.001;
        
        $in_filter = "";
		if(!empty($approvedcolumn)) {
			$in_filter = $wpdb->prepare("WHERE l.id IN (SELECT lead_id from $lead_detail_table_name WHERE field_number BETWEEN %f AND %f)", $approvedcolumn - 0.001, $approvedcolumn + 0.001);	
			// This will work once all the fields are converted to the meta_key after 1.6
			#$search_filter .= $wpdb->prepare(" AND m.meta_key = 'is_approved' AND m.meta_value = %s", 1);
		}
		
		$limit_filter = '';
		if($page_size > 0) { $limit_filter = "LIMIT $offset,$page_size"; }
		
        $sql = "
            SELECT filtered.sort, l.*, d.field_number, d.value
            FROM $lead_table_name l
            INNER JOIN $lead_detail_table_name d ON d.lead_id = l.id
            INNER JOIN (
                SELECT distinct sorted.sort, l.id
                FROM $lead_table_name l
                INNER JOIN $lead_detail_table_name d ON d.lead_id = l.id
                INNER JOIN (
                    SELECT @rownum:=@rownum+1 as sort, id FROM (
                        SELECT 0 as query, lead_id as id, value
                        FROM $lead_detail_table_name
                        WHERE form_id=$form_id
                        AND field_number between $field_number_min AND $field_number_max

                        UNION ALL

                        SELECT 1 as query, l.id, d.value
                        FROM $lead_table_name l
                        LEFT OUTER JOIN $lead_detail_table_name d ON d.lead_id = l.id AND field_number between $field_number_min AND $field_number_max
                        WHERE l.form_id=$form_id
                        AND d.lead_id IS NULL

                    ) sorted1
                   $orderby
                ) sorted ON d.lead_id = sorted.id
                $search_filter
                $limit_filter
            ) filtered ON filtered.id = l.id
            $in_filter
            ORDER BY filtered.sort";

        return $sql;
    }
    
    private static function sort_by_default_field_query($form_id, $sort_field, $sort_direction='DESC', $search='', $offset=0, $page_size=30, $star=null, $read=null, $is_numeric_sort = false, $start_date=null, $end_date=null, $status='active', $approvedcolumn = null, $limituser = false){
        global $wpdb, $current_user;
		
		if(!is_numeric($form_id) || !is_numeric($offset)|| !is_numeric($page_size)){
            return "";
        }

        $lead_detail_table_name = RGFormsModel::get_lead_details_table_name();
        $lead_table_name = RGFormsModel::get_lead_table_name();

        $search_term = "%$search%";
        $search_filter = empty($search) ? "" : $wpdb->prepare(" AND value LIKE %s", $search_term);

        $star_filter = $star !== null && $status == 'active' ? $wpdb->prepare(" AND is_starred=%d AND status='active' ", $star) : "";
        $read_filter = $read !== null && $status == 'active' ? $wpdb->prepare(" AND is_read=%d AND status='active' ", $read) :  "";
        if(function_exists('gform_get_meta')) {
	        $status_filter = $wpdb->prepare(" AND status=%s ", $status);
	    } else {
	    	$status_filter = '';
	    }

        $start_date_filter = empty($start_date) ? "" : " AND datediff(date_created, '$start_date') >=0";
        $end_date_filter = empty($end_date) ? "" : " AND datediff(date_created, '$end_date') <=0";
		
		$in_filter = "";
		if(!empty($approvedcolumn)) {
			$in_filter = $wpdb->prepare("l.id IN (SELECT lead_id from $lead_detail_table_name WHERE field_number BETWEEN %f AND %f) AND", $approvedcolumn - 0.001, $approvedcolumn + 0.001);	
			// This will work once all the fields are converted to the meta_key after 1.6
			#$search_filter .= $wpdb->prepare(" AND m.meta_key = 'is_approved' AND m.meta_value = %s", 1);
		}
		
		$user_filter = '';
		if($limituser) {
			get_currentuserinfo();
			if((int)$current_user->ID !== 0 || ($current_user->ID === 0 && apply_filters('kws_gf_show_entries_if_not_logged_in', apply_filters('kws_gf_treat_not_logged_in_as_user', true)))) {
	        	if((int)$current_user->ID === 0) {
	        		$user_filter = $wpdb->prepare(" AND (created_by IS NULL OR created_by=%d)", $current_user->ID);
	        	} else {
	        		$user_filter = $wpdb->prepare(" AND created_by=%d ", $current_user->ID);
	        	}
			} else {
				return false;
			}
		}
		
		$limit_filter = '';
		if($page_size > 0) { $limit_filter = "LIMIT $offset,$page_size"; }
		
        $sql = "
            SELECT filtered.sort, l.*, d.field_number, d.value
            FROM $lead_table_name l
            INNER JOIN $lead_detail_table_name d ON d.lead_id = l.id
            INNER JOIN
            (
                SELECT @rownum:=@rownum + 1 as sort, id
                FROM
                (
                    SELECT distinct l.id
                    FROM $lead_table_name l
                    INNER JOIN $lead_detail_table_name d ON d.lead_id = l.id
                    WHERE $in_filter
                    l.form_id=$form_id
                    $search_filter
                    $star_filter
                    $read_filter
                    $user_filter
                    $status_filter
                    $start_date_filter
                    $end_date_filter
                    ORDER BY $sort_field $sort_direction
                    $limit_filter
                ) page
            ) filtered ON filtered.id = l.id
            ORDER BY filtered.sort";

        return $sql;
    }
    
    function directory_anchor_text($value = null) {
	
		if(apply_filters('kws_gf_directory_anchor_text_striphttp', true)) {
			$value = str_replace('http://', '', $value);
			$value = str_replace('https://', '', $value);
		}
		
		if(apply_filters('kws_gf_directory_anchor_text_stripwww', true)) {
			$value = str_replace('www.', '', $value);
		}
		if(apply_filters('kws_gf_directory_anchor_text_rootonly', true)) {
			$value = preg_replace('/(.*?)\/(.+)/ism', '$1', $value);
		}
		if(apply_filters('kws_gf_directory_anchor_text_nosubdomain', true)) {
			$value = preg_replace('/((.*?)\.)+(.*?)\.(.*?)/ism', '$3.$4', $value);
		}
		if(apply_filters('kws_gf_directory_anchor_text_noquerystring', true)) {
			$ary = explode("?", $value);
			$value = $ary[0];
		}
		return $value;
	}
	
	private function get_entrylink_column($form, $entry = false) {
		if(!is_array($form)) { return false; }
		
		$columns = empty($entry) ? array() : array('id');
		foreach(@$form['fields'] as $key=>$col) {
			if(!empty($col['useAsEntryLink'])) {
				$columns["{$col['id']}"] = $col['useAsEntryLink'];
			}
		}
		
		return empty($columns) ? false : $columns;
	}
	
	private function prep_address_field($field) {
		return !empty($field) ? trim($field) : '';
	}
	
	public function add_form_button($context) {
		//Action target that adds the "Insert Form" button to the post/page edit screen
		$image_btn = WP_PLUGIN_URL . "/" . basename(dirname(__FILE__)) . "/form-button-1.png";
		$out = '<a href="#TB_inline?width=640&amp;inlineId=select_gf_directory" class="select_gf_directory thickbox" title="' . __("Add a Gravity Forms Directory", 'gravityforms') . '"><img src="'.$image_btn.'" alt="' . __("Add Gravity Form", 'gravityform') . '" width="13" height="12" /></a>';
		return $context . $out;
	}

	function format_address($address = array(), $linknewwindow = false) {
		$address_field_id = @self::prep_address_field($address['id']);
		$street_value = @self::prep_address_field($address[$address_field_id . ".1"]);
		$street2_value = @self::prep_address_field($address[$address_field_id . ".2"]);
		$city_value = @self::prep_address_field($address[$address_field_id . ".3"]);
		$state_value = @self::prep_address_field($address[$address_field_id . ".4"]);
		$zip_value = @self::prep_address_field($address[$address_field_id . ".5"]);
		$country_value = @self::prep_address_field($address[$address_field_id . ".6"]);
	
		$address = $street_value;
		$address .= !empty($address) && !empty($street2_value) ? "<br />$street2_value" : $street2_value;
		$address .= !empty($address) && (!empty($city_value) || !empty($state_value)) ? "<br />$city_value" : $city_value;
		$address .= !empty($address) && !empty($city_value) && !empty($state_value) ? ", $state_value" : $state_value;
		$address .= !empty($address) && !empty($zip_value) ? " $zip_value" : $zip_value;
		$address .= !empty($address) && !empty($country_value) ? "<br />$country_value" : $country_value;
	
		//adding map link
		if(!empty($address) && apply_filters('kws_gf_directory_td_address_map', 1)) {
			$address_qs = str_replace("<br />", " ", $address); //replacing <br/> with spaces
			$address_qs = urlencode($address_qs);
			$target = ''; if($linknewwindow) { $target = ' target="_blank"'; }
			$address .= "<br/>".apply_filters('kws_gf_directory_map_link', "<a href='http://maps.google.com/maps?q=$address_qs'".$target." class='map-it-link'>".__('Map It')."</a>");
		}
		return $address;
	}
	
	function show_field_ids($form = array()) {
		if(isset($_REQUEST['show_field_ids'])) {
		echo <<<EOD
		<style type="text/css">
			#input_ids th, #input_ids td { border-bottom:1px solid #999; padding:.25em 15px; }
			#input_ids th { border-bottom-color: #333; font-size:.9em; background-color: #464646; color:white; padding:.5em 15px; font-weight:bold;  } 
			#input_ids { background:#ccc; margin:0 auto; font-size:1.2em; line-height:1.4; width:100%; border-collapse:collapse;  }
			#input_ids strong { font-weight:bold; }
			#preview_hdr { display:none;}
			#input_ids caption { color:white!important;}
		</style>
EOD;
		
		if(!empty($form)) { echo '<table id="input_ids"><caption id="input_id_caption">Fields for <strong>Form ID '.$form['id'].'</strong></caption><thead><tr><th>Field Name</th><th>Field ID</th></thead><tbody>'; }
		foreach($form['fields'] as $field) {
			// If there are multiple inputs for a field; ie: address has street, city, zip, country, etc.
			if(is_array($field['inputs'])) {
				foreach($field['inputs'] as $input) {
					echo "<tr><td width='50%'><strong>{$input['label']}</strong></td><td>{$input['id']}</td></tr>";
				}
			}
			// Otherwise, it's just the one input.
			else {
				echo "<tr><td width='50%'><strong>{$field['label']}</strong></td><td>{$field['id']}</td></tr>";
			}
		}
		if(!empty($form)) { echo '</tbody></table><div style="clear:both;"></div></body></html>'; exit(); }
		} else {
			return $form;
		}
	}
	
	static function add_mce_popup(){
		//Action target that displays the popup to insert a form to a post/page
		?>
		<script type="text/javascript">
			function addslashes (str) {
				   // Escapes single quote, double quotes and backslash characters in a string with backslashes	 
				   // discuss at: http://phpjs.org/functions/addslashes
				   return (str + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
			}
						
			jQuery('document').ready(function($) { 
				
			
			    jQuery('.datepicker').each(
			        function (){
			            var element = jQuery(this);
			            var format = "yy-mm-dd";
			
			            var image = "";
			            var showOn = "focus";
			            if(element.hasClass("datepicker_with_icon")){
			                showOn = "both";
			                image = jQuery('#gforms_calendar_icon_' + this.id).val();
			            }
			
			            element.datepicker({ yearRange: '-100:+10', showOn: showOn, buttonImage: image, buttonImageOnly: true, dateFormat: format });
			        }
			    );


				$('#select_gf_directory_form').bind('submit', function(e) {
					e.preventDefault();
					var shortcode = InsertGFDirectory();
					//send_to_editor(shortcode);
					return false;
				});
				
				
				
				$('#insert_gf_directory').live('click', function(e) {
					e.preventDefault();
					
					$('#select_gf_directory_form').trigger('submit');
					return;
				});
				
				$('a.select_gf_directory').live('click', function(e) {	
					// This auto-sizes the box
					if(typeof tb_position == 'function') {
						tb_position();
					}
					return;		
				});
				
				jQuery('a.kws_gf_advanced_settings').click(function(e) {  e.preventDefault(); jQuery('#kws_gf_advanced_settings').toggle(); return false; });
				
				function InsertGFDirectory(){
					var directory_id = jQuery("#add_directory_id").val();
					if(directory_id == ""){
						alert("<?php _e("Please select a form", "gravity-forms-addons") ?>");
						jQuery('#add_directory_id').focus();
						return false;
					}
				
			<?php 
					$js = self::make_popup_options(true); 
					#print_r($js);
					$ids = $idOutputList = $setvalues = $vars = '';

					foreach($js as $j) {
						$vars .= $j['js'] ."
						";
						$ids .= $j['idcode'] . " ";
						$setvalues .= $j['setvalue']."
						";
						$idOutputList .= $j['id'].'Output' .' + ';
					}
					echo $vars;
					echo $setvalues;
			?>

				var win = window.dialogArguments || opener || parent || top;
				var shortcode = "[directory form=\"" + directory_id +"\"" + <?php echo addslashes($idOutputList); ?>"]";
				win.send_to_editor(shortcode);
				return false;
			}
		});
			
		</script>
		<style type="text/css">
				.ui-datepicker-div,
				.ui-datepicker-inline,
				#ui-datepicker-div {/*resets*/margin: 0; padding: 0; border: 0; outline: 0; line-height: 1.3; text-decoration: none; font-size: 100%; list-style: none; font-family: Verdana,Arial,sans-serif; background: #ffffff; font-size: 1em; border: 4px solid #aaaaaa; width: 15.5em; padding: 2.5em .5em .5em; position: relative}
				.ui-datepicker-div,
				#ui-datepicker-div {z-index: 9999; /*must have*/display: none}
				.ui-datepicker-inline {float: left; display: block}
				.ui-datepicker-control {display: none}
				.ui-datepicker-current {display: none}
				.ui-datepicker-next,
				.ui-datepicker-prev {position: absolute; left: .5em; top: .5em; background: #e6e6e6}
				.ui-datepicker-next {left: 14.6em}
				.ui-datepicker-next:hover,
				.ui-datepicker-prev:hover {background: #dadada}
				.ui-datepicker-next a,
				.ui-datepicker-prev a {text-indent: -999999px; width: 1.3em; height: 1.4em; display: block; font-size: 1em; background: url(../images/datepicker_arrow_left.gif) 50% 50% no-repeat; border: 1px solid #d3d3d3; cursor: pointer}
				.ui-datepicker-next a {background: url(../images/datepicker_arrow_right.gif) 50% 50% no-repeat}
				.ui-datepicker-header select {border: 1px solid #d3d3d3; color: #555555; background: #e6e6e6; font-size: 1em; line-height: 1.4em; position: absolute; top: .5em; margin: 0!important}
				.ui-datepicker-header option:focus,.ui-datepicker-header option:hover {background: #dadada}
				.ui-datepicker-header select.ui-datepicker-new-month {width: 7em; left: 2.2em}
				.ui-datepicker-header select.ui-datepicker-new-year {width: 5em; left: 9.4em}
				table.ui-datepicker {width: 15.5em; text-align: right}
				table.ui-datepicker td a {padding: .1em .3em .1em 0; display: block; color: #555555; background: #e6e6e6; cursor: pointer; border: 1px solid #ffffff}
				table.ui-datepicker td a:hover {border: 1px solid #999999; color: #212121; background: #dadada}
				table.ui-datepicker td a:active {border: 1px solid #aaaaaa; color: #212121; background: #ffffff}
				table.ui-datepicker .ui-datepicker-title-row td {padding: .3em 0; text-align: center; font-size: .9em; color: #222222; text-transform: uppercase}
				table.ui-datepicker .ui-datepicker-title-row td a {color: #222222}
				.ui-datepicker-cover {display: none; display: block; position: absolute; z-index: -1; filter: mask(); top: -4px; left: -4px; width: 193px; height: 200px}
		</style>
	<div id="select_gf_directory" style="overflow-x:hidden; overflow-y:auto;display:none;">
		<form action="#" method="get" id="select_gf_directory_form">
			<div class="wrap">
				<div>
					<div style="padding:15px 15px 0 15px;">
						<h3 style="color:#5A5A5A!important; font-family:Georgia,Times New Roman,Times,serif!important; font-size:1.8em!important; font-weight:normal!important;"><?php _e("Insert A Directory", "gravity-forms-addons"); ?></h3>
						<span>
							<?php _e("Select a form below to add it to your post or page.", "gravity-forms-addons"); ?>
						</span>
					</div>
					<div style="padding:15px 15px 0 15px;">
						<select id="add_directory_id">
							<option value="">  <?php _e("Select a Form", "gravity-forms-addons"); ?>  </option>
							<?php
								$forms = RGFormsModel::get_forms(1, "title");
								foreach($forms as $form){
									?>
									<option value="<?php echo absint($form->id) ?>"><?php echo esc_html($form->title) ?></option>
									<?php
								}
							?>
						</select> <br/>
						<div style="padding:8px 0 0 0; font-size:11px; font-style:italic; color:#5A5A5A"><?php _e("This form will be the basis of your directory.", "gravity-forms-addons"); ?></div>
					</div>
						<?php 
						
						self::make_popup_options(); 
						
						?>
					<div class="submit">
						<input type="submit" class="button-primary" style="margin-right:15px;" value="Insert Directory" id="insert_gf_directory" />
						<a class="button button-secondary" style="color:#bbb;" href="#" onclick="tb_remove(); return false;"><?php _e("Cancel", "gravity-forms-addons"); ?></a>
					</div>
				</div>
			</div>
		</form>
	</div>
		<?php
	}
	
	function make_popup_options($js = false) {
		$i = 0;
		
		$defaults = self::directory_defaults();
		
		$standard = array(
				array('text', 'page_size'  ,  20, __( "Number of entries to show at once. Use <code>0</code> to show all entries.", 'gravity-forms-addons')),
				array('select', 'directoryview' , array(
						array('value' => 'table', 'label' => __( "Table", 'gravity-forms-addons')), 
						array('value' => 'ul', 'label'=> __( "Unordered List", 'gravity-forms-addons')), 
						array('value' => 'dl', 'label' => __( "Definition List", 'gravity-forms-addons')), 
					), __( "Format for directory listings (directory view)", 'gravity-forms-addons')
				),
				array('select','entryview' , array(
						array('value' =>'table', 'label' => __( "Table", 'gravity-forms-addons')), 
						array('value' =>'ul', 'label'=>__( "Unordered List", 'gravity-forms-addons')), 
						array('value' => 'dl', 'label' => __( "Definition List", 'gravity-forms-addons')), 
					), __( "Format for single entries (single entry view)", 'gravity-forms-addons')
				),
				array('checkbox',  'search'  ,  true, __( "Show the search field", 'gravity-forms-addons')),
				array('checkbox', 'approved' , false, __("Show only entries that have been Approved (have a field in the form that is an Admin-only checkbox with a value of 'Approved'). <span class='description'><strong>Note:</strong> This will hide entries that have not been explicitly approved.</span>", 'gravity-forms-addons')),
				array('checkbox', 'smartapproval' , true, __("Automatically convert directory into Approved-only when an Approved field is detected.", 'gravity-forms-addons')),
			  );
		if(!$js) {
			echo '<ul>';
			foreach($standard as $o) {
				self::make_field($o[0], $o[1], maybe_serialize($o[2]), $o[3], $defaults);
			}
			echo '</ul>';
		} else {
			foreach($standard as $o) {
				$out[$i] = self::make_popup_js($o[0], $o[1], $defaults);
				$i++;
			}
		}
			
			$content = array(
					array('checkbox',  'wpautop'  ,  true, __( "Convert bulk paragraph text to paragraphs (using the WordPress function <code><a href='http://codex.wordpress.org/Function_Reference/wpautop'>wpautop()</a></code>)", 'gravity-forms-addons')),
					array('checkbox',  'getimagesize'  ,  false, __( "Calculate image sizes (Warning: this may slow down the directory loading speed!)", 'gravity-forms-addons')),
					array('radio'	, 'postimage' , array(
							array('label' =>'<img src="'.GFCommon::get_base_url().'/images/doctypes/icon_image.gif" /> Show image icon', 'value'=>'icon', 'default'=>'1'), 
							array('label' => __('Show full image', 'gravity-forms-addons'), 'value'=>'image')
						), __("How do you want images to appear in the directory?", 'gravity-forms-addons')
					),
					array('checkbox', 'fulltext' , true, __("Show full content of a textarea or post content field, rather than an excerpt", 'gravity-forms-addons')),
					
					array('date', 'start_date' ,  false, __('Start date (in <code>YYYY-MM-DD</code> format)', 'gravity-forms-addons')),
					array('date', 'end_date' ,  false, __('End date (in <code>YYYY-MM-DD</code> format)', 'gravity-forms-addons')),
			);
			
			$administration = array(
				array('checkbox', 'showadminonly' ,  false, __("Show Admin-Only columns <span class='description'>(in Gravity Forms, Admin-Only fields are defined by clicking the Advanced tab on a field in the Edit Form view, then editing Visibility > Admin Only)</span>", 'gravity-forms-addons')),
				array('checkbox', 'useredit' , false, __("Allow logged-in users to edit entries they created. Will add an 'Edit Your Entry' field to the Single Entry View.", 'gravity-forms-addons')),
				array('checkbox', 'limituser' , false, __("Display entries only the the creator of the entry (users will not see other people's entries).", 'gravity-forms-addons')),
				array('checkbox', 'adminedit' , false, __(sprintf('Allow %sadministrators%s to edit entries they created. Will add an \'Edit Your Entry\' field to the Single Entry View.','<strong>', '</strong>'), 'gravity-forms-addons')),
			);	
			
			$lightbox = array(
				#array('checkbox',  'lightbox'  ,  true, __( sprintf("Show images in a %slightbox%s", '<a href="http://en.wikipedia.org/wiki/Lightbox_(JavaScript)" target="_blank">', '</a>'), 'gravity-forms-addons')),
				array('radio'	, 'lightboxstyle' , 
					array(
						array('label' =>'Style 1 <a href="'.self::get_base_url().'/colorbox/example1/index.html" target="_blank">See example</a>', 'value'=>'1'),
						array('label' =>'Style 2 <a href="'.self::get_base_url().'/colorbox/example2/index.html" target="_blank">See example</a>', 'value'=>'2'),
						array('label' =>'Style 3 <a href="'.self::get_base_url().'/colorbox/example3/index.html" target="_blank">See example</a>', 'value'=>'3','default'=>'1'),
						array('label' =>'Style 4 <a href="'.self::get_base_url().'/colorbox/example4/index.html" target="_blank">See example</a>', 'value'=>'4'),
						array('label' =>'Style 5 <a href="'.self::get_base_url().'/colorbox/example5/index.html" target="_blank">See example</a>', 'value'=>'5')
					), "What style should the lightbox use?"
				),
				array('checkboxes'	, 'lightboxsettings' , 
					array(
						array('label' => __('Images', 'gravity-forms-addons'), 'value'=>'images', 'default' => '1'),
						array('label' => __( "Entry Links (Open entry details in lightbox)"), 'value'=>'entry'),
						array('label' => __('Website Links (non-entry)', 'gravity-forms-addons'), 'value'=>'urls')
					), __("Set what type of links should be loaded in the lightbox", 'gravity-forms-addons')
				),
				#array('checkbox',  'entrylightbox' ,  false, __( "Open entry details in lightbox (defaults to lightbox settings)", 'gravity-forms-addons'))
			);
			
			$formatting = array( 
				array('checkbox', 'jstable' ,  false, __('Use the TableSorter jQuery plugin to sort the table?', 'gravity-forms-addons')),
				array('checkbox', 'titleshow'  ,  true, __("<strong>Show a form title?</strong> By default, the title will be the form title.", 'gravity-forms-addons')),
				array('checkbox', 'showcount'  ,  true, __("Do you want to show 'Displaying 1-19 of 19'?", 'gravity-forms-addons')),
				array('checkbox', 'thead'  ,  true, __("Show the top heading row (<code>&lt;thead&gt;</code>)", 'gravity-forms-addons')),
				array('checkbox', 'tfoot'  ,  true, __("Show the bottom heading row (<code>&lt;tfoot&gt;</code>)", 'gravity-forms-addons')),
				array('checkbox', 'pagelinksshowall'  ,  true, __("Show each page number (eg: <a>1</a> <a>2</a> <a>3</a> <a>4</a> <a>5</a> <a>6</a> <a>7</a> <a>8</a>) instead of summary (eg: <a>1</a> <a>2</a> <a>3</a> ... <a>8</a> <a>&raquo;</a>)", 'gravity-forms-addons')),
				array('checkbox', 'showrowids'  ,  true, __("Show the row ids, which are the entry IDs, in the HTML; eg: <code>&lt;tr id=&quot;lead_row_565&quot;&gt;</code>", 'gravity-forms-addons')),
		#		array('checkbox', 'icon'  ,  false, __("Show the GF icon as it does in admin? <img src=\"". GFCommon::get_base_url()."/images/gravity-title-icon-32.png\" />", 'gravity-forms-addons')),
		#		array('checkbox', 'searchtabindex'  ,  false, __("Adds tabindex='' to the search field", 'gravity-forms-addons')),
				array('checkbox', 'jssearch' ,  true, __("Use JavaScript for sorting (otherwise, <em>links</em> will be used for sorting by column)", 'gravity-forms-addons')),
				array('checkbox', 'dateformat' ,  false, __("Override the options from Gravity Forms, and use standard PHP date formats", 'gravity-forms-addons')),
			);
					
			$links = array( 
				array('checkbox', 'linkemail'  ,  true, __("Convert email fields to email links", 'gravity-forms-addons')),
				array('checkbox', 'linkwebsite'  ,  true, __("Convert URLs to links", 'gravity-forms-addons')),
				array('checkbox', 'truncatelink' ,  false, __("Show more simple links for URLs (strip <code>http://</code>, <code>www.</code>, etc.)", 'gravity-forms-addons')),	#'truncatelink' => false,
				array('checkbox', 'linknewwindow'  ,  false, __("<strong>Open links in new window?</strong> (uses <code>target='_blank'</code>)", 'gravity-forms-addons')),
				array('checkbox', 'nofollowlinks'  ,  false, __("<strong>Add <code>nofollow</code> to all links</strong>, including emails", 'gravity-forms-addons')),
			);
					
			$address = array(
				array('checkbox', 'appendaddress'  ,  false, __("Add the formatted address as a column at the end of the table", 'gravity-forms-addons')),
				array('checkbox',  'hideaddresspieces'  ,  false, __( "Hide the pieces that make up an address (Street, City, State, ZIP, Country, etc.)", 'gravity-forms-addons'))
			);
			
			$entry = array(
				array('checkbox', 'entry' ,  true, __("If there's a displayed Entry ID column, add link to each full entry", 'gravity-forms-addons')),
				array('text',  'entrytitle' ,  __('Entry Detail', 'gravity-forms-addons'), __( "Title of entry lightbox window", 'gravity-forms-addons')),
				array('text',  'entrylink' ,  __('View entry details', 'gravity-forms-addons'), __( "Link text to show full entry", 'gravity-forms-addons')),
				array('text',  'entryth' ,  __('More Info', 'gravity-forms-addons'), __( "Entry ID column title", 'gravity-forms-addons')),
				array('text',  'entryback' ,  __('&larr; Back to directory', 'gravity-forms-addons'), __( "The text of the link to return to the directory view from the single entry view.", 'gravity-forms-addons')),
				array('checkbox',  'entryonly' ,  true, __( "When viewing full entry, show entry only? Otherwise, show entry with directory below", 'gravity-forms-addons')),
				array('checkbox',  'entryanchor' ,  true, __( "When returning to directory view from single entry view, link to specific anchor row?", 'gravity-forms-addons')),
			);
		
		$fieldsets = array(
			__('Content Settings', 'gravity-forms-addons') => $content, 
			__('Administration of Entries', 'gravity-forms-addons') =>$administration, 
			__('Lightbox Options', 'gravity-forms-addons')=>$lightbox,
			__('Formatting Options', 'gravity-forms-addons')=>$formatting,
			__('Link Settings', 'gravity-forms-addons')=>$links,
			__('Address Options', 'gravity-forms-addons')=>$address
		);
		
		if(!$js) {
			echo '<a href="#kws_gf_advanced_settings" class="kws_gf_advanced_settings">'.__('Show advanced settings', 'gravity-forms-addons').'</a>';
			echo '<div style="display:none;" id="kws_gf_advanced_settings">';
			echo "<h2 style='margin:0; padding:0; font-weight:bold; font-size:1.5em; margin-top:1em;'>Single-Entry View</h2>";
			echo '<span class="howto">These settings control whether users can view each entry as a separate page or lightbox. Single entries will show all data associated with that entry.</span>';
			echo '<ul style="padding:0 15px 0 15px; width:100%;">';
			foreach($entry as $o) {
				if(isset($o[3])) { $o3 = esc_html($o[3]); } else { $o3 = '';}
				self::make_field($o[0], $o[1], maybe_serialize($o[2]), $o3, $defaults);
			}
			echo '</ul>';
			
			echo '<div class="hr-divider label-divider"></div>';
			
			echo "<h2 style='margin:0; padding:0; font-weight:bold; font-size:1.5em; margin-top:1em;'>".__('Directory View', 'gravity-forms-addons')."</h2>";
			echo '<span class="howto">'.__('These settings affect how multiple entries are shown at once.', 'gravity-forms-addons').'</span>';
			
			foreach($fieldsets as $title => $fieldset) {
				echo "<fieldset><legend><h3 style='padding-top:1em; padding-bottom:.5em; margin:0;'>{$title}</h3></legend>";
				echo '<ul style="padding: 0 15px 0 15px; width:100%;">'; 		 
				foreach($fieldset as $o) {
					self::make_field($o[0], $o[1], maybe_serialize($o[2]), $o[3], $defaults);
				}
				echo '</ul></fieldset>';
				echo '<div class="hr-divider label-divider"></div>';
			}
			echo "<h2 style='margin:0; padding:0; font-weight:bold; font-size:1.5em; margin-top:1em;'>".__('Additional Settings', 'gravity-forms-addons')."</h2>";
			echo '<span class="howto">'.__('These settings affect both the directory view and single entry view.', 'gravity-forms-addons').'</span>';
			echo '<ul style="padding: 0 15px 0 15px; width:100%;">';
		} else {
			foreach($entry as $o) {
				$out[$i] = self::make_popup_js($o[0], $o[1], $defaults);
				$i++;
			}
			foreach($fieldsets as $title => $fieldset) {
				foreach($fieldset as $o) {
					$out[$i] = self::make_popup_js($o[0], $o[1], $defaults);
					$i++;
				}
			}
		}
			$advanced = array(
					array('text', 'tableclass' ,  'gf_directory widefat fixed', __( "Class for the <table>, <ul>, or <dl>", 'gravity-forms-addons')),
					array('text', 'tablestyle' ,  '', __( "inline CSS for the <table>, <ul>, or <dl>", 'gravity-forms-addons')),
					array('text', 'rowclass' ,  '', __( "Class for the <table>, <ul>, or <dl>", 'gravity-forms-addons')),
					array('text', 'rowstyle' ,  '', __( "Inline CSS for all <tbody><tr>'s, <ul><li>'s, or <dl><dt>'s", 'gravity-forms-addons')),
					array('text', 'valign' ,  'baseline', __("Vertical align for table cells", 'gravity-forms-addons')),
					array('text', 'sort' ,  'date_created', __( "Use the input ID ( example: 1.3 or 7 or ip)", 'gravity-forms-addons')),
					array('text', 'dir' ,  'DESC', __("Sort in ascending order (<code>ASC</code> or descending (<code>DESC</code>)", 'gravity-forms-addons')),
					array('text', 'startpage'  ,  1, __( "If you want to show page 8 instead of 1", 'gravity-forms-addons')),
					array('text', 'pagelinkstype'  ,  'plain', __( "Type of pagination links. <code>plain</code> is just a string with the links separated by a newline character. The other possible values are either <code>array</code> or <code>list</code>.", 'gravity-forms-addons')),
					array('text', 'titleprefix'  ,  'Entries for ', __( "Default GF behavior is 'Entries : '", 'gravity-forms-addons')),
					array('text', 'tablewidth'  ,  '100%', __( "Set the 'width' attribute for the <table>, <ul>, or <dl>", 'gravity-forms-addons')),
					array('text', 'datecreatedformat'  ,  get_option('date_format').' \a\t '.get_option('time_format'), __( "Use <a href='http://php.net/manual/en/function.date.php' target='_blank'>standard PHP date formats</a>", 'gravity-forms-addons')),
					array('checkbox', 'credit'  ,  true, __( "Give credit to the plugin creator (who has spent over 200 hours on this free plugin!) with a link at the bottom of the directory", 'gravity-forms-addons'))
			);
		if(!$js) { 		  
			foreach($advanced as $o) {
				self::make_field($o[0], $o[1], maybe_serialize($o[2]), esc_html($o[3]), $defaults);
			}
			echo '</ul></fieldset></div>';
		} else {
			foreach($advanced as $o) {
				$out[$i] = self::make_popup_js($o[0], $o[1], $defaults);
				$i++;
			}
			return $out;
		}
	}
	
	function make_field($type, $id, $default, $label, $defaults = array()) {
		$rawid = $id;
		$idLabel = '';
		if(self::is_gravity_page('gf_settings')){
			$id = 'gf_addons_directory_defaults['.$id.']';
			$idLabel = " <span style='color:#868686'>(".__(sprintf('%s', "<pre style='display:inline'>{$rawid}</pre>"), 'gravity-forms-addons').")</span>";
		}
		$checked = '';
		$label = str_replace('&lt;code&gt;', '<code>', str_replace('&lt;/code&gt;', '</code>', $label));
		$output = '<li class="setting-container" style="width:90%; clear:left; border-bottom: 1px solid #cfcfcf; padding:.25em .25em .4em; margin-bottom:.25em;">';
		$default = maybe_unserialize($default);
		
		$class = '';
		if($type == 'date') {
			$type = 'text';
			$class = ' class="gf_addons_datepicker datepicker"';
		}
		
		if($type == "checkbox") { 
				if(!empty($defaults["{$rawid}"]) || ($defaults["{$rawid}"] === '1' || $defaults["{$rawid}"] === 1)) { 
					$checked = ' checked="checked"';
				}
				$output .= '<label for="gf_settings_'.$rawid.'"><input type="hidden" value="" name="'.$id.'" /><input type="checkbox" id="gf_settings_'.$rawid.'"'.$checked.' name="'.$id.'" /> '.$label.$idLabel.'</label>'."\n";	
		} 
		elseif($type == "text") {
				$default = $defaults["{$rawid}"];
				$output .= '<label for="gf_settings_'.$rawid.'"><input type="text" id="gf_settings_'.$rawid.'" value="'.htmlspecialchars(stripslashes($default)).'" style="width:40%;" name="'.$id.'"'.$class.' /> <span class="howto">'.$label.$idLabel.'</span></label>'."\n";
		} elseif($type == 'radio' || $type == 'checkboxes') {
			if(is_array($default)) {
				$output .= $label.$idLabel.'<ul class="ul-disc">';
				foreach($default as $opt) {
					if($type == 'radio') {
						$id_opt = $id.'_'.sanitize_title($opt['value']);
						if(!empty($defaults["{$rawid}"]) && $defaults["{$rawid}"] == $opt['value']) { $checked = ' checked="checked"'; } else { $checked = ''; }
						$inputtype = 'radio';
						$name = $id;
						$value = $opt['value'];
						$output .= '
						<li><label for="gf_settings_'.$id_opt.'">';
					} else {
						$id_opt = $rawid.'_'.sanitize_title($opt['value']);
						if(!empty($defaults["{$rawid}"][sanitize_title($opt['value'])])) { $checked = ' checked="checked"'; } else { $checked = ''; }
						$inputtype = 'checkbox';
						$name = $id.'['.sanitize_title($opt['value']).']';
						$value = 1;
						$output .= '
							<li><label for="gf_settings_'.$id_opt.'">
								<input type="hidden" value="0" name="'.$name.'" />';
					}
					$output .= '
							<input type="'.$inputtype.'"'.$checked.' value="'.$value.'" id="gf_settings_'.$id_opt.'" name="'.$name.'" /> '.$opt['label']." <span style='color:#868686'>(".__(sprintf('%s', "<pre style='display:inline'>".sanitize_title($opt['value'])."</pre>"), 'gravity-forms-addons').")</span>".'
						</label>
					</li>'."\n";	
				}
				$output .= "</ul>";
			}
		} elseif($type == 'select') {
			if(is_array($default)) {
				$output .= '
				<label for="gf_settings_'.$rawid.'">'.$label.'
				<select name="'.$id.'" id="gf_settings_'.$rawid.'">';
				foreach($default as $opt) {
					
					if(!empty($defaults["{$rawid}"]) && $defaults["{$rawid}"] == $opt['value']) { $checked = ' selected="selected"'; } else { $checked = ''; }
					$id_opt = $id.'_'.sanitize_title($opt['value']);
					$output .= '<option'.$checked.' value="'.$opt['value'].'"> '.$opt['label'].'</option>'."\n";	
				}
				$output .= '</select>'.$idLabel.'
				</label>
				';
			} else {
				$output = '';
			}
		}
		if(!empty($output)) {
			$output .= '</li>'."\n";
			echo $output;
		}
	}
	
	function make_popup_js($type, $id, $defaults) {
		
		foreach($defaults as $key => $default) {
			if($default === true || $default === 'on') {
				$defaults[$key] = 'true';
			} elseif($default === false || ($type == 'checkbox' && empty($default))) {
				$defaults[$key] = 'false';
			}
		}
		$defaultsArray = array();
		if($type == "checkbox") {
			$js = 'var '.$id.' = jQuery("#gf_settings_'.$id.'").is(":checked") ? "true" : "false";';
		} elseif($type == "checkboxes" && is_array($defaults["{$id}"])) {
			$js = ''; $i = 0;
			$js .= "\n\t\t\tvar ".$id.' = new Array();';
			foreach($defaults["{$id}"] as $key => $value) {
				$defaultsArray[] = $key;
				$js .=  "\n\t\t\t".$id.'['.$i.'] = jQuery("input#gf_settings_'.$id.'_'.$key.'").is(":checked") ? "'.$key.'" : null;';
				$i++;
			}
		} elseif($type == "text" || $type == "date") {
			$js = 'var '.$id.' = jQuery("#gf_settings_'.$id.'").val();';
		} elseif($type == 'radio') {
			$js = '
			if(jQuery("input[name=\''.$id.'\']:checked").length > 0) { 
				var '.$id.' = jQuery("input[name=\''.$id.'\']:checked").val();
			} else {
				var '.$id.' = jQuery("input[name=\''.$id.'\']").eq(0).val();
			}';
		} elseif($type == 'select') {
			$js = '
			if(jQuery("select[name=\''.$id.'\']:selected").length > 0) { 
				var '.$id.' = jQuery("select[name=\''.$id.'\']:selected").val();
			} else {
				var '.$id.' = jQuery("select[name=\''.$id.'\']").eq(0).val();
			}';
		}
		$set = '';
		if(!is_array($defaults["{$id}"])) {
			$idCode = $id.'=\""+'.$id.'+"\"';
			$set = 'var '.$id.'Output = (jQuery.trim('.$id.') == "'.trim(addslashes(stripslashes($defaults["{$id}"]))).'") ? "" : " '.$idCode.'";';
		} else {
			
			$idCode2 = $id.'.join()';
			$idCode = '"'.$idCode2.'"';
			$set = '
			'.$id.' =  jQuery.grep('.$id.',function(n){ return(n); });
			var '.$id.'Output = (jQuery.trim('.$idCode2.') === "'.implode(',',$defaultsArray).'") ? "" : " '.$id.'=\""+ '.$idCode2.'+"\"";';
		}
		 // Debug
		
		$return = array('js'=>$js, 'id' => $id, 'idcode'=>$idCode, 'setvalue' => $set);
		
		return $return;
	}
	
	public function pseudo_filter($content = null, $type = 'table', $single = false) {
		switch($type) {
			case 'table':
				return $content;
				break;
			case 'ul':
				$content = self::convert_to_ul($content, $single);
				break;	
			case 'dl':
				$content = self::convert_to_dl($content, $single);
				break;
		}
		return $content;
	}
	
	public function convert_to_ul($content = null, $singleUL = false) {
	
		$strongHeader = apply_filters('kws_gf_convert_to_ul_strong_header', 1);
		
		// Directory View
		if(!$singleUL) { 
			$content = preg_replace("/<table([^>]*)>/ism","<ul$1>", $content);
			$content = preg_replace("/<\/table([^>]*)>/ism","</ul>", $content);
			if($strongHeader) {
				$content = preg_replace("/<tr([^>]*)>\s+/","\n\t\t\t\t\t\t\t\t\t\t\t\t<li$1><ul>", $content);
				$content = preg_replace("/<th([^>]*)\>(.*?)\<\/th\>/","$2</strong>\n\t\t\t\t\t\t\t\t\t\t\t\t\t<ul>", $content);
			} else {
				$content = preg_replace("/<tr([^>]*)>\s+/","\n\t\t\t\t\t\t\t\t\t\t\t\t<li$1>", $content);
				$content = preg_replace("/<th([^>]*)\>(.*?)\<\/th\>/","$2\n\t\t\t\t\t\t\t\t\t\t\t\t\t<ul>", $content);
			}
			$content = preg_replace("/<\/tr[^>]*>/","\t\t\t\t\t</ul>\n\t\t\t\t\t\t\t\t\t\t\t\t</li>", $content);
		} 
		// Single listing view
		else {
			$content = preg_replace("/<table([^>]*)>/ism","<ul$1>", $content);
			$content = preg_replace("/<\/table([^>]*)>/ism","</ul>", $content);
			if($strongHeader) {
				$content = preg_replace("/<tr([^>]*)>\s+/","\n\t\t\t\t\t\t\t\t\t\t\t\t<li$1><strong>", $content);
				$content = preg_replace("/<th([^>]*)\>(.*?)\<\/th\>/","$2</strong>\n\t\t\t\t\t\t\t\t\t\t\t\t\t<ul>", $content);
			} else {
				$content = preg_replace("/<tr([^>]*)>\s+/","\n\t\t\t\t\t\t\t\t\t\t\t\t<li$1>", $content);
				$content = preg_replace("/<th([^>]*)\>(.*?)\<\/th\>/","$2\n\t\t\t\t\t\t\t\t\t\t\t\t\t<ul>", $content);
			}
			$content = preg_replace("/<\/tr[^>]*>/","\t\t\t\t\t</ul>\n\t\t\t\t\t\t\t\t\t\t\t\t</li>", $content);
		}
	#	$content = preg_replace("/\<\/p\>\s+\<\/li/ism","\<\/p\>\<\/li", $content);
		$content = preg_replace("/(?:\s+)?(valign\=\"(?:.*?)\"|width\=\"(?:.*?)\"|cellspacing\=\"(?:.*?)\")(?:\s+)?/ism", ' ', $content);
		$content = preg_replace("/<\/?tbody[^>]*>/","", $content);
		$content = preg_replace("/<thead[^>]*>.*<\/thead>|<tfoot[^>]*>.*<\/tfoot>/is","", $content);
		$content = preg_replace("/\<td([^>]*)\>(\&nbsp;|)\<\/td\>/","", $content);
		$content = preg_replace("/\<td([^>]*)\>/","\t\t\t\t\t<li$1>", $content);
		$content = preg_replace("/<\/td[^>]*>/","</li>", $content);
		$content = preg_replace('/\s?colspan\="([^>]*?)"\s?/ism', ' ', $content);
		return $content;
	}
	
	public function convert_to_dl($content, $singleDL = false) {
		$back = '';
		// Get the back link, if it exists
		preg_match("/\<p\sclass=\"entryback\"\>(.*?)\<\/p\>/", $content, $matches);
		if(isset($matches[0])) { $back = $matches[0]; }
		$content = preg_replace("/\<p\sclass=\"entryback\"\>(.*?)\<\/p\>/", "", $content);
		$content = preg_replace("/<\/?table[^>]*>|<\/?tbody[^>]*>/","", $content);
		$content = preg_replace("/<thead[^>]*>.*<\/thead>|<tfoot[^>]*>.*<\/tfoot>/is","", $content);
		if(!$singleDL) {
			$content = preg_replace("/<tr([^>]*)>/","<dl$1>", $content);
			$content = preg_replace("/<\/tr[^>]*>/","</dl>", $content);
		} else {
			$content = preg_replace("/<tr([^>]*)>/","", $content);
			$content = preg_replace("/<\/tr[^>]*>/","", $content);
		}
		$content = preg_replace("/\<td([^>]*)\>(\&nbsp;|)\<\/td\>/","", $content);
		$content = preg_replace("/\<th([^>]*)\>(.*?)<\/th\>/ism","<dt$1>$2</dt>", $content);
		$content = preg_replace('/<td(.*?)(title="(.*?)")?>(.*?)<\/td[^>]*>/ism',"<dt$1>$3</dt><dd>$4</dd>", $content);
		$output = $back;
		$output .= "\n\t\t\t\t\t\t\t\t".'<dl>';
		$output .= $content;
		$output .= "\t\t\t\t\t\t".'</dl>';
		return $output;
	}

	public function make_entry_link($options = array(), $link = false, $lead_id = '', $form_id = '', $field_id = '', $field_label = '', $linkClass = '') {
		global $wp_rewrite,$post,$wp;
		extract($options);
		$entrylink = (empty($link) || $link === '&nbsp;') ? $field_label : $link; //$entrylink;
		
		$entrytitle = apply_filters('kws_gf_directory_detail_title', apply_filters('kws_gf_directory_detail_title_'.$lead_id, $entrytitle));

		if(!empty($lightboxsettings['entry'])) {
			$href = WP_PLUGIN_URL . "/" . basename(dirname(__FILE__)) . "/entry-details.php?leadid=$lead_id&amp;form={$form_id}&amp;post={$post->ID}"; 
			$linkClass = ' class="thickbox colorbox lightbox" rel="directory_all directory_entry"'; 
		} else {
			$multisite = (function_exists('is_multisite') && is_multisite() && $wpdb->blogid == 1);
			if($wp_rewrite->using_permalinks()) {
				// example.com/example-directory/entry/4/14/
				if(isset($post->ID)) {
					$url = get_permalink($post->ID);
				} else {
					$url = parse_url(add_query_arg(array()));
					$url = $url['path'];
				}
				$href = trailingslashit($url).sanitize_title(apply_filters('kws_gf_directory_endpoint', 'entry')).'/'.$form_id.apply_filters('kws_gf_directory_endpoint_separator', '/').$lead_id.'/';
				#if(!empty($url['query'])) { $href .= '?'.$url['query']; }
				$href = add_query_arg(array('gf_search' => !empty($_REQUEST['gf_search']) ? $_REQUEST['gf_search'] : null, 'sort' => isset($_REQUEST['sort']) ? $_REQUEST['sort'] : null, 'dir' => isset($_REQUEST['dir']) ? $_REQUEST['dir'] : null, 'page' => isset($_REQUEST['page']) ? $_REQUEST['page'] : null, 'start_date' => isset($_REQUEST['start_date']) ? $_REQUEST['start_date'] : null, 'end_date' => isset($_REQUEST['start_date']) ? $_REQUEST['end_date'] : null), $href);
			} else {
				// example.com/?page_id=24&leadid=14&form=4
				$href = add_query_arg(array('leadid'=>$lead_id, 'form' => $form_id));
			}
		}

		$value = '<a href="'.$href.'"'.$linkClass.' title="'.$entrytitle.'">'.$entrylink.'</a>';
		return $value;
	}
	
	function make_class($class) {
		$class = str_replace('-', '_', sanitize_title($class));
		return $class;
	}
	
	function format_date($gmt_datetime, $is_human = true, $dateformat = 'Y/m/d \a\t H:i'){
		if(empty($gmt_datetime))
			return "";

		//adjusting date to local configured Time Zone
		$lead_gmt_time = mysql2date("G", $gmt_datetime);
		$lead_local_time = GFCommon::get_local_timestamp($lead_gmt_time);
		
		$date_display = date_i18n($dateformat, $lead_local_time, true);
		
		return $date_display;
	}
	
	function get_icon_url($path){
		$info = pathinfo($path);

		switch(strtolower($info["extension"])){

			case "css" :
				$file_name = "icon_css.gif";
			break;

			case "doc" :
				$file_name = "icon_doc.gif";
			break;

			case "fla" :
				$file_name = "icon_fla.gif";
			break;

			case "html" :
			case "htm" :
			case "shtml" :
				$file_name = "icon_html.gif";
			break;

			case "js" :
				$file_name = "icon_js.gif";
			break;

			case "log" :
				$file_name = "icon_log.gif";
			break;

			case "mov" :
				$file_name = "icon_mov.gif";
			break;

			case "pdf" :
				$file_name = "icon_pdf.gif";
			break;

			case "php" :
				$file_name = "icon_php.gif";
			break;

			case "ppt" :
				$file_name = "icon_ppt.gif";
			break;

			case "psd" :
				$file_name = "icon_psd.gif";
			break;

			case "sql" :
				$file_name = "icon_sql.gif";
			break;

			case "swf" :
				$file_name = "icon_swf.gif";
			break;

			case "txt" :
				$file_name = "icon_txt.gif";
			break;

			case "xls" :
				$file_name = "icon_xls.gif";
			break;

			case "xml" :
				$file_name = "icon_xml.gif";
			break;

			case "zip" :
				$file_name = "icon_zip.gif";
			break;

			case "gif" :
			case "jpg" :
			case "jpeg":
			case "png" :
			case "bmp" :
			case "tif" :
			case "eps" :
				$file_name = "icon_image.gif";
			break;

			case "mp3" :
			case "wav" :
			case "wma" :
				$file_name = "icon_audio.gif";
			break;

			case "mp4" :
			case "avi" :
			case "wmv" :
			case "flv" :
				$file_name = "icon_video.gif";
			break;

			default:
				$file_name = "icon_generic.gif";
			break;
		}

		return GFCommon::get_base_url() . "/images/doctypes/$file_name";
	}
  
  	function get_lead_count($form_id, $search, $star=null, $read=null, $column, $approved = false, $leads = array(), $start_date = null, $end_date = null, $limituser = false){
		global $wpdb, $current_user;

		if(!is_numeric($form_id))
			return "";

		$detail_table_name = RGFormsModel::get_lead_details_table_name();
		$lead_table_name = RGFormsModel::get_lead_table_name();

		$star_filter = $star !== null ? $wpdb->prepare("AND is_starred=%d ", $star) : "";
		$read_filter = $read !== null ? $wpdb->prepare("AND is_read=%d ", $read) : "";
		if(function_exists('gform_get_meta')) {
	        $status_filter = $wpdb->prepare(" AND status=%s ", 'active');
	    } else {
	    	$status_filter = '';
	    }
		$start_date_filter = empty($start_date) ? "" : " AND datediff(date_created, '$start_date') >=0";
		$end_date_filter = empty($end_date) ? "" : " AND datediff(date_created, '$end_date') <=0";

		$search_term = "%$search%";
		$search_filter = empty($search) ? "" : $wpdb->prepare("AND ld.value LIKE %s", $search_term);
		
		$user_filter = '';
		if($limituser) {
			get_currentuserinfo();
			if((int)$current_user->ID !== 0 || ($current_user->ID === 0 && apply_filters('kws_gf_show_entries_if_not_logged_in', apply_filters('kws_gf_treat_not_logged_in_as_user', true)))) {
				if(!empty($current_user->ID)) {
	        		$user_filter = $wpdb->prepare(" AND l.created_by=%d ", $current_user->ID);
	        	} else {
	        		$user_filter = $wpdb->prepare(" AND (created_by IS NULL OR created_by=%d)", $current_user->ID);
	        	}
			} else {
				return false;
			}
			
		}
		
		$in_filter = "";
		if($approved) {
			$in_filter = $wpdb->prepare("l.id IN (SELECT lead_id from $detail_table_name WHERE field_number BETWEEN %f AND %f) AND", $column - 0.001, $column + 0.001);	
			// This will work once all the fields are converted to the meta_key after 1.6
			#$search_filter .= $wpdb->prepare(" AND m.meta_key = 'is_approved' AND m.meta_value = %s", 1);
		}
		
		$sql = "SELECT count(distinct l.id) FROM $lead_table_name as l,
				$detail_table_name as ld";
#		$sql .= function_exists('gform_get_meta') ? " INNER JOIN wp_rg_lead_meta m ON l.id = m.lead_id " : ""; // After 1.6
		$sql .= "
				WHERE $in_filter
				l.form_id=$form_id
				AND ld.form_id=$form_id
				AND l.id = ld.lead_id
				$star_filter
				$read_filter
				$status_filter
				$user_filter
				$start_date_filter
				$end_date_filter
				$search_filter";
		
		return $wpdb->get_var($sql);
	}
	
	function check_meta_approval($lead_id) {
		if(function_exists('gform_get_meta') && isset($lead['id'])) {
			$meta = gform_get_meta($lead['id'], 'is_approved');
			if(isset($meta) && !empty($meta)) { return true; }
		}
		return false;
	}
	
	function check_approval($lead, $column) {
		$meta = true;
		if(!is_array($lead) && is_numeric($lead)) {
		
			$meta = self::check_meta_approval($lead);
			if($meta === true) { return true; }
			
			// This is rather heavy, so only if necessary.
			$lead = RGFormsModel::get_lead($lead);
		}
		
		// $lead['status'] is added in 1.6
		if((isset($lead["{$column}"]) && strtolower($lead["{$column}"]) == 'approved') && ((function_exists('gform_update_meta') && empty($lead['status']) || !empty($lead['status']) && $lead['status'] == 'active') || !function_exists('gform_update_meta'))) {
			return true;
		}
		if(!$meta) { return false; } // Prevent checking twice.
		return self::check_meta_approval($lead['id']);
	}
	
	function hide_in_directory($form, $field_id) {
		return self::check_hide_in('hideInDirectory', $form, $field_id);
	}
	
	function hide_in_single($form, $field_id) {
		return self::check_hide_in('hideInSingle', $form, $field_id);
	}
	
	function check_hide_in($type, $form, $field_id) {
		foreach($form['fields'] as $field) {
#			echo $field['label'] . ' / ' . floor($field['id']).' / '.floor($field_id).' / <strong>'.$field["{$type}"].'</strong><br />'; 
			if(floor($field_id) === floor($field['id']) && !empty($field["{$type}"])) {
				return true;
			}
		}
		
		return false;
	}
	
	function remove_approved_column($type = 'form', $fields, $approvedcolumn) {
		
		foreach($fields as $key => $column) {
			if((int)floor($column['id']) === (int)floor($approvedcolumn)) {
				unset($fields["{$key}"]);
			}
		}
		
		return $fields;
	}
	
	function remove_admin_only($leads, $adminOnly, $approved, $isleads, $single = false, $form) {
		
		if(empty($adminOnly) || !is_array($adminOnly) || !is_array($leads)) { return $leads; }

		$i = 0;
		if($isleads) {
			foreach($leads as $key => $lead) {
				if(@in_array($key, $adminOnly) && $key != $approved && $key != floor($approved)) {
					if($single) {
						foreach($adminOnly as $ao) {
							unset($lead[$ao]);
						}
					} else {
						unset($leads[$i]);
					}
				}
			}
			return $leads;
		} else {
			$columns = $leads;
			foreach($columns as $key => $column) {
				// Not sure why this was coded like this. Doesn't seem to make much sense now.
				// if(@in_array($key, $adminOnly) && $key != $approved && $key != floor($approved) && !$single || ($single && (!isset($column['id']) || isset($column['id']) && in_array($column['id'], $adminOnly)))) {
				if(
					@in_array($key, $adminOnly) && $key != $approved ||
					($single && self::hide_in_single($form, $key)) ||
					(!$single && self::hide_in_directory($form, $key))
				) {
					if($single) {
						unset($columns[floor($key)]);
					} else {
						unset($columns[$key]);
					}
				}
			}
			
			return $columns;
		}
	}
	
	public static function build_lead_array($results, $use_long_values = false){	
        $leads = array();
        $lead = array();
        $form_id = 0;
        if(is_array($results) && sizeof($results) > 0){
            $form_id = $results[0]->form_id;
            $lead = array("id" => $results[0]->id, "form_id" => $results[0]->form_id, "date_created" => $results[0]->date_created, "is_starred" => intval($results[0]->is_starred), "is_read" => intval($results[0]->is_read), "ip" => $results[0]->ip, "source_url" => $results[0]->source_url, "post_id" => $results[0]->post_id, "currency" => $results[0]->currency, "payment_status" => $results[0]->payment_status, "payment_date" => $results[0]->payment_date, "transaction_id" => $results[0]->transaction_id, "payment_amount" => $results[0]->payment_amount, "is_fulfilled" => $results[0]->is_fulfilled, "created_by" => $results[0]->created_by, "transaction_type" => $results[0]->transaction_type, "user_agent" => $results[0]->user_agent);
            if(isset($results[0]->status)) { 
            	$lead["status"] = $results[0]->status;
            }
        }

        $prev_lead_id=0;
        foreach($results as $result){
            if($prev_lead_id <> $result->id && $prev_lead_id > 0){
                array_push($leads, $lead);
                $lead = array("id" => $result->id, "form_id" => $result->form_id,     "date_created" => $result->date_created,     "is_starred" => intval($result->is_starred),     "is_read" => intval($result->is_read),     "ip" => $result->ip,     "source_url" => $result->source_url,     "post_id" => $result->post_id,     "currency" => $result->currency,     "payment_status" => $result->payment_status,     "payment_date" => $result->payment_date,     "transaction_id" => $result->transaction_id,     "payment_amount" => $result->payment_amount,     "is_fulfilled" => $result->is_fulfilled,     "created_by" => $result->created_by,     "transaction_type" => $result->transaction_type,     "user_agent" => $result->user_agent);
                if(isset($result->status)) {
                	$lead["status"] = $result->status;
                }
            }

            $field_value = $result->value;
            //using long values if specified
            if($use_long_values && strlen($field_value) >= GFORMS_MAX_FIELD_LENGTH){
                $long_text = RGFormsModel::get_field_value_long($lead["id"], $result->field_number);
                $field_value = !empty($long_text) ? $long_text : $field_value;
            }

            $lead[$result->field_number] = $field_value;
            $prev_lead_id = $result->id;
        }
        //adding last lead.
        if(sizeof($lead) > 0)
            array_push($leads, $lead);

        //running entry through gform_get_field_value filter
        $form = RGFormsModel::get_form_meta($form_id);
        foreach($leads as &$lead){
            foreach($form["fields"] as $field){
                if(isset($field["inputs"]) && is_array($field["inputs"])){
                    foreach($field["inputs"] as $input){
                        $lead[(string)$input["id"]] = apply_filters("gform_get_input_value", rgar($lead, (string)$input["id"]), $lead, $field, $input["id"]);
                    }
                }
                else{

                    $lead[$field["id"]] = apply_filters("gform_get_input_value", rgar($lead, (string)$field["id"]), $lead, $field, "");
                }
            }
        }

        return $leads;

    }
}


function kws_gf_load_functions() {
	
// Get Gravity Forms over here!
#@include_once(WP_PLUGIN_DIR . "/gravityforms/gravityforms.php");
#@include_once(WP_PLUGIN_DIR . "/gravityforms/forms_model.php");
#@include_once(WP_PLUGIN_DIR . "/gravityforms/common.php"); // 1.3
#@include_once(WP_PLUGIN_DIR . "/gravityforms/form_display.php"); // 1.3

	// If Gravity Forms is installed and exists
	if(defined('RG_CURRENT_PAGE')) {
		
		function gf_field_value($leadid, $fieldid) {
			echo get_gf_field_value($leadid, $fieldid);
		}
		
		
		// To retrieve textarea inputs from a lead 
		// Example: get_gf_field_value_long(22, '14');
		function get_gf_field_value_long($leadid, $fieldid) {
			return RGFormsModel::get_field_value_long($leadid, $fieldid);
		}
		
		// To retrieve textarea inputs from a lead 
		// Example: get_gf_field_value_long(22, '14');
		function get_gf_field_value($leadid, $fieldid) {
			$lead = RGFormsModel::get_lead($leadid);
			$fieldid = floatval($fieldid);
			if(is_numeric($fieldid)) {
				$result = $lead["$fieldid"];
			}
			
			$max_length = GFORMS_MAX_FIELD_LENGTH;
			
			if(strlen($result) >= ($max_length - 50)) {
				$result = get_gf_field_value_long($lead["id"], $fieldid);
	        }
	        $result = trim($result);
	        
	        if(!empty($result)) { return $result; }
			return false;
		}
		
		function gf_field_value_long($leadid, $fieldid) {
			echo get_gf_field_value_long($leadid, $fieldid);
		}
		
		
		// Gives you the label for a form input (such as First Name). Enter in the form and the field ID to access the label.
		// Example: echo get_gf_field_label(1,1.3);
		// Gives you the label for a form input (such as First Name). Enter in the form and the field ID to access the label.
		// Example: echo get_gf_field_label(1,1.3);
		function get_gf_field_label($form_id, $field_id) {
			$form = RGFormsModel::get_form_meta($form_id);
			foreach($form["fields"] as $field){
				if($field['id'] == $field_id) {
					# $output = RGForms::escape_text($field['label']); // No longer used
					$output = esc_html($field['label']); // Using esc_html(), a WP function
				}elseif(is_array($field['inputs'])) {
					foreach($field["inputs"] as $input){
						if($input['id'] == $field_id) {
							if(class_exists('GFCommon')) {
								$output = esc_html(GFCommon::get_label($field,$field_id));
							} else {
								#$output = RGForms::escape_text(RGForms::get_label($field,$field_id));  // No longer used
								$output = esc_html(RGForms::get_label($field,$field_id));  // No longer used
							}
						}
					}
				}
			}
			return $output;
		}
		function gf_field_label($form_id, $field_id) {
			echo get_gf_field_label($form_id, $field_id);
		}	
		
		// Returns a form using php instead of shortcode
		function get_gf_form($id, $display_title=true, $display_description=true, $force_display=false, $field_values=null){
			if(class_exists('GFFormDisplay')) {	
				return GFFormDisplay::get_form($id, $display_title=true, $display_description=true, $force_display=false, $field_values=null);
			} else {
				return RGFormsModel::get_form($id, $display_title, $display_description);
			}
		}
		function gf_form($id, $display_title=true, $display_description=true, $force_display=false, $field_values=null){
			echo get_gf_form($id, $display_title, $display_description, $force_display, $field_values);
		}
		
		// Returns array of leads for a specific form
		function get_gf_leads($form_id, $sort_field_number=0, $sort_direction='DESC', $search='', $offset=0, $page_size=3000, $star=null, $read=null, $is_numeric_sort = false, $start_date=null, $end_date=null, $status = 'active', $approvedcolumn = false, $limituser = false) {
			return GFDirectory::get_leads($form_id,$sort_field_number, $sort_direction, $search, $offset, $page_size, $star, $read, $is_numeric_sort, $start_date, $end_date, $status, $approvedcolumn, $limituser);
		}
		
		function gf_leads($form_id, $sort_field_number=0, $sort_direction='DESC', $search='', $offset=0, $page_size=3000, $star=null, $read=null, $is_numeric_sort = false, $start_date=null, $end_date=null) {
			echo get_gf_leads($form_id,$sort_field_number, $sort_direction, $search, $offset, $page_size, $star, $read, $is_numeric_sort, $start_date, $end_date);
		}
		
		function kws_gf_directory($atts) {
			GFDirectory::make_directory($atts);
		}
		
		
		if(!function_exists('kws_print_r')) {
			function kws_print_r($content, $die = false) {
				echo '<pre>'.print_r($content, true).'</pre>';
				if($die) { die(); }
				return $content;
			}
		}
	
	}
}


?>