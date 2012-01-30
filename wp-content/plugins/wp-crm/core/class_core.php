<?php
/**
 * WP-CRM Core Framework
 *
 * @version 0.1
 * @author Andy Potanin <andy.potanin@twincitiestech.com>
 * @package WP-CRM
*/

/**
 * WP-CRM Core Framework Class
 *
 * Contains primary functions for setting up the framework of the plugin.
 *
 * @version 0.01
 * @package WP-CRM
 * @subpackage Main
 */
class WP_CRM_Core {

  /**
   * First function of WP_CRM_Core to be loaded, called by: plugins_loaded hook.
   *
   * Load premium features.
   *
   * @since 0.01
   *
   * @uses $wp_crm WP-CRM configuration array
   *
   */
  function WP_CRM_Core() {
    global $wp_crm, $wp_roles, $wpdb;

    do_action('wp_crm_pre_load');

    //* Process settings updates */
    WP_CRM_F::settings_action();

    //* Load premium features */
    WP_CRM_F::load_premium();

    // Load third-party plugin load_plugin_compatibility */
    WP_CRM_F::load_plugin_compatibility();

    //** Hook in upper init */
    add_action('init', array($this, 'init_upper'), 0);

    /** Default init hook */
    add_action('init', array($this, 'init'));

    //** Hook in lower init */
    add_action('init', array($this, 'init_lower'), 100);

    if(!$wpdb->crm_log) {
      $wpdb->crm_log = $wpdb->base_prefix . 'crm_log';
    }

    if(!$wpdb->crm_log_meta) {
      $wpdb->crm_log_meta = $wpdb->crm_log . '_meta';
    }

  }


  /**
   * Primary init of WP_CRM_Core, gets called by after_setup_theme.
   *
   * Register scripts.
   * Register styles.
   * Load premium features.
   *
   * @since 0.01
   *
   * @uses $wp_crm WP-CRM configuration array
   *
   */
  function init() {
    global $wpdb, $wp_crm, $wp_roles;

    /** Loads all the class for handling all plugin tables */
    include_once WP_CRM_Path . '/core/class_list_table.php';

    if($wp_crm['configuration']['track_detailed_user_activity'] == 'true') {
      WP_CRM_F::track_detailed_user_activity();
    }

    wp_register_script('google-jsapi', 'https://www.google.com/jsapi');
    wp_register_script('jquery-cookie', WP_CRM_URL. '/third-party/jquery.smookie.js', array('jquery'), '1.7.3' );
    wp_register_script('swfobject', WP_CRM_URL. '/third-party/swfobject.js', array('jquery'));
    wp_register_script('jquery-uploadify', WP_CRM_URL. '/third-party/uploadify/jquery.uploadify.v2.1.4.min.js', array('jquery', 'swfobject'));
    wp_register_script('jquery-position', WP_CRM_URL. '/third-party/jquery.ui.position.min.js', array('jquery-ui-core'));
    wp_register_script('jquery-slider', WP_CRM_URL. '/third-party/jquery.ui.slider.min.js', array('jquery-ui-core'));
    wp_register_script('jquery-widget', WP_CRM_URL. '/third-party/jquery.ui.widget.min.js', array('jquery-ui-core'));
    wp_register_script('jquery-datepicker', WP_CRM_URL. '/third-party/jquery.ui.datepicker.js', array('jquery-ui-core'));
    wp_register_script('jquery-autocomplete', WP_CRM_URL. '/third-party/jquery.ui.autocomplete.min.js', array('jquery-widget','jquery-position'));
    wp_register_script('wp-crm-data-tables', WP_CRM_URL. '/third-party/dataTables/jquery.dataTables.min.js', array('jquery'));
    wp_register_script('wp_crm_global', WP_CRM_URL. '/js/wp_crm_global.js', array('jquery'), WP_CRM_Version, true);
    wp_register_script('wp_crm_profile_editor', WP_CRM_URL. '/js/wp_crm_profile_editor.js', array('wp_crm_global'), WP_CRM_Version, true);

    // Find and register theme-specific style if a custom wp_properties.css does not exist in theme
    $theme_slug = get_option('stylesheet');
    if(file_exists( WP_CRM_Templates . "/theme-specific/{$theme_slug}.css")) {
      wp_register_style('wp-crm-theme-specific', WP_CRM_URL . "/templates/theme-specific/{$theme_slug}.css",  array('wp-crm-default-styles'),WP_CRM_Version);
    }

    //** Load default styles */
    if(file_exists( WP_CRM_Path . "/templates/wp-crm-default-styles.css")) {
      wp_register_style('wp-crm-default-styles', WP_CRM_URL . "/templates/wp-crm-default-styles.css",  array(),WP_CRM_Version);
    }

    if(file_exists( WP_CRM_Path . "/css/wp_crm_global.css")) {
      wp_register_style('wp_crm_global', WP_CRM_URL . "/css/wp_crm_global.css",  array(),WP_CRM_Version);
    }

    wp_register_style('wp-crm-data-tables', WP_CRM_URL . "/css/crm-data-tables.css",  array(),WP_CRM_Version);

    // Plug page actions -> Add Settings Link to plugin overview page
    add_filter('plugin_action_links', array('WP_CRM_Core', 'plugin_action_links'), 10, 2 );

    // Setup pages and overview columns
    add_action("admin_menu", array('WP_CRM_Core', "admin_menu"), 100);

    add_filter("retrieve_password_message", array('WP_CRM_F', "retrieve_password_message"));

    //** Modify default WP password reset message */
    add_filter("admin_body_class", create_function('', "return WP_CRM_Core::admin_body_class(); "));

     //** Load back-end scripts */
    add_action("admin_enqueue_scripts", array('WP_CRM_Core', "admin_enqueue_scripts"));

    add_action("wp_ajax_wp_crm_csv_export", create_function('',' WP_CRM_F::csv_export($_REQUEST["wp_crm_search"]); die();'));
    add_action("wp_ajax_wp_crm_visualize_results", create_function('',' WP_CRM_F::visualize_results($_REQUEST["filters"]); die();'));
    add_action('wp_ajax_wp_crm_check_plugin_updates', create_function("",'  echo WP_CRM_F::check_plugin_updates(); die();'));
    add_action("wp_ajax_wp_crm_user_object", create_function('',' echo "CRM Object Report: \n" . print_r(wp_crm_get_user($_REQUEST[user_id]), true) . "\nRaw Meta Report: \n" .  print_r(WP_CRM_F::show_user_meta_report($_REQUEST[user_id]), true); '));
    add_action("wp_ajax_wp_crm_show_meta_report", create_function('',' die(print_r(WP_CRM_F::show_user_meta_report(), true)); '));
    add_action("wp_ajax_wp_crm_get_user_activity_stream", create_function('',' echo WP_CRM_F::get_user_activity_stream("user_id={$_REQUEST[user_id]}"); die; '));
    add_action("wp_ajax_wp_crm_insert_activity_message", create_function('',' echo WP_CRM_F::insert_event("time={$_REQUEST[time]}&attribute=note&object_id={$_REQUEST[user_id]}&text={$_REQUEST[content]}&ajax=true"); die; '));
    add_action("wp_ajax_wp_crm_get_notification_template", create_function('',' echo WP_CRM_F::get_notification_template($_REQUEST["template_slug"]); die; '));
    add_action("wp_ajax_wp_crm_do_fake_users", create_function('',' echo WP_CRM_F::do_fake_users("number={$_REQUEST[number]}&do_what={$_REQUEST[do_what]}"); die; '));
    add_action("wp_ajax_wp_crm_list_table", create_function('',' echo WP_CRM_F::ajax_table_rows(); die; '));
    add_action("wp_ajax_wp_crm_quick_action", create_function('',' echo WP_CRM_F::quick_action(); die; '));

    add_action("admin_init", array('WP_CRM_Core', "admin_init"));

    add_action("admin_head", array('WP_CRM_Core', "admin_head"));

    //* Init action hook */
    do_action('wp_crm_init');

    add_action('load-toplevel_page_wp_crm',  array('WP_CRM_Core', 'toplevel_page_wp_crm'));
    add_action('load-crm_page_wp_crm_settings', array('WP_CRM_Core',  'crm_page_wp_crm_settings'));
    add_action('load-crm_page_wp_crm_add_new', array('WP_CRM_Core',  'crm_page_wp_crm_add_new'));

    //** Take over traditional user pages if option is enabled */
    add_action('load-user-edit.php', array('WP_CRM_Core',  'crm_page_traditional_user_page'));
    add_action('load-users.php', array('WP_CRM_Core',  'crm_page_traditional_user_page'));
    add_action('load-user-new.php', array('WP_CRM_Core',  'crm_page_traditional_user_page'));

    add_action("template_redirect", array('WP_CRM_Core', "template_redirect"));
    add_action("deleted_user", array('WP_CRM_F', "deleted_user"));

    //** Check if installed DB version is older than THIS version */
    if(is_admin()) {
      if(!get_option('wp_crm_caps_set')) {
        WP_CRM_F::manual_activation('update_caps=true&auto_redirect=true');
      }

      //** Load defaults */
      WP_CRM_F::manual_activation();
    }
  }


  /**
   * Called on init, as early as possible.
   *
   * @author Maxim Peshkov
   */
  function init_upper() {

    $locale = apply_filters( 'wp_crm_locale', get_locale() );

    $mofile = sprintf( 'wp_crm-%s.mo', $locale );

    $mofile_local  = WP_CRM_Path . '/langs/' . $mofile;
		$mofile_global = WP_LANG_DIR . '/wp_crm/' . $mofile;

		if ( file_exists( $mofile_local ) ) {
			load_textdomain( 'wp_crm', $mofile_local );
    } elseif ( file_exists( $wp_crm ) ) {
			load_textdomain( 'wp_crm', $mofile_global );
    }

  }
  

  /**
   * Secondary WPP Initialization ran towards the end of init()
   *
   * Loads things that we want make accessible for modification via other plugins.
   *
   * @author Maxim Peshkov
   */
  function init_lower() {
    global $wp_crm;

    //** Filters for CRM settings are applied */
    $wp_crm['configuration'] = apply_filters('wp_crm_configuration', $wp_crm['configuration']);
    $wp_crm['notification_actions'] = apply_filters('wp_crm_notification_actions', $wp_crm['notification_actions']);

    $wp_crm = apply_filters('wp_crm_settings_lower', $wp_crm);

  }


  /**
   * Listens for WP-CRM shortcodes
   *
   * @todo Enure displayed settings are being honored when saved.
   * @since 0.1
   *
   */
  function template_redirect() {
      global $post, $wp, $wp_query, $wp_styles;

      if(!strpos($post->post_content, "wp_crm_form")) {
        return;
      }

    //** Print front-end styles */
    add_action("wp_print_styles", array('WP_CRM_Core', "wp_print_styles"));

  }


  /**
   * Loads front-end styles
   *
   * Only ran when wp_crm_form shortcode is present in content.
   * @since 0.1
   *
   */
  function wp_print_styles() {
    global $post, $wp, $wp_query, $wp_styles;

     // Load theme-specific stylesheet if it exists
    wp_enqueue_script('jquery');
    wp_enqueue_style('wp-crm-theme-specific');
    wp_enqueue_style('wp-crm-default-styles');
  }


  /**
   * Runs pre-header functions on admin-side only for the overview page
   *
   * @todo Enure displayed settings are being honored when saved.
   * @since 0.1
   *
   */
  function toplevel_page_wp_crm() {
    add_screen_option('layout_columns', array('max' => 2, 'default' => 2) );
    add_screen_option('per_page', array('label' => __( 'Users', 'wp_crm' )) );
  }


  /**
   * Runs pre-header functions for profile page
   *
   * @since 0.22
   *
   */
  function crm_page_traditional_user_page() {
    global $wp_crm, $current_screen, $hook_suffix, $typenow, $taxnow;

    /* If avatar-delection redirection originated from CRM profile, we muts return there */
    if($_GET['delete_avatar'] == 'true' && strpos($_SERVER['HTTP_REFERER'], 'admin.php?page=wp_crm_add_new')) {
      die(wp_redirect("admin.php?page=wp_crm_add_new&user_id={$_GET['user_id']}"));
    }

    if($wp_crm['configuration']['replace_default_user_page'] != 'true') {
      return;
    }

    switch($current_screen->id) {

      case 'user-edit':

        if(isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
          die(wp_redirect("admin.php?page=wp_crm_add_new&user_id={$_GET['user_id']}"));
        } else {
          die(wp_redirect("admin.php?page=wp_crm_add_new"));
        }

      break;

      case 'users':
        die(wp_redirect("admin.php?page=wp_crm"));
      break;

      case 'user':
        die(wp_redirect("admin.php?page=wp_crm_add_new"));
      break;

    }


  }

  /**
   * Runs pre-header functions for profile page
   *
   * @since 0.22
   *
   */
  function crm_page_wp_crm_add_new() {

    WP_CRM_F::crm_profile_page_metaboxes();

  }

  /**
   * Runs pre-header functions for settings page
   *
   *
   * @since 0.1
   *
   */
  function crm_page_wp_crm_settings() {

     // Download backup of configuration
    if($_REQUEST['wp_crm_action'] == 'download-wp_crm-backup'
      && wp_verify_nonce($_REQUEST['_wpnonce'], 'download-wp_crm-backup')) {
        global $wp_crm;

        $sitename = sanitize_key( get_bloginfo( 'name' ) );
        $filename = $sitename . '-wp-crm.' . date( 'Y-m-d' ) . '.txt';

        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-Transfer-Encoding: binary");
        header( 'Content-Type: text/plain; charset=' . get_option( 'blog_charset' ), true );

        echo json_encode($wp_crm);

      die();
    }

    //** Make sure tables are up to date */
    WP_CRM_F::maybe_install_tables();
  }


  /**
   * Runs pre-header functions on admin-side only - ran on ALL admin pages
   *
   * Checks if plugin has been updated.
   *
   * @since 0.1
   *
   */
  function admin_init() {
    global $wp_rewrite, $wp_roles, $wp_crm, $wpdb, $current_user;

    //** Check if current page is profile page, and load global variable */
    WP_CRM_F::maybe_load_profile();

    do_action('wp_crm_metaboxes');

    //** Add overview table rows. Static because admin_menu is not loaded on ajax calls. */
    add_filter("manage_toplevel_page_wp_crm_columns", array('WP_CRM_Core', "overview_columns"));

    add_action('admin_print_scripts-' . $wp_crm['system']['pages']['settings'], create_function('', "wp_enqueue_script('jquery-ui-tabs');wp_enqueue_script('jquery-cookie');"));

    add_action('load-crm_page_wp_crm_add_new', array('WP_CRM_Core', 'wp_crm_save_user_data'));

    // Add metaboxes
    if(is_array($wp_crm['system']['pages'])) {

      $sidebar_boxes = array('special_actions');

      foreach($wp_crm['system']['pages'] as $screen) {

        if(!class_exists($screen)) {
          continue;
        }

        $location_prefixes = array('side_', 'normal_', 'advanced_');

        foreach(get_class_methods($screen) as $box) {

          // Set context and priority if specified for box

          $context = 'normal';

          if(strpos($box, "side_") === 0 || in_array($box, $sidebar_boxes)) {
            $context = 'side';
          }

          if(strpos($box, "advanced_") === 0) {
            $context = 'advanced';
          }

          // Get name from slug
          $label = CRM_UD_F::slug_to_label(str_replace($location_prefixes, '', $box));

          add_meta_box( $box, $label , array($screen,$box), $screen, $context, 'default');
        }
      }
    }

    //** Handle actions */
    if(isset($_REQUEST['wp_crm_action'])) {

      $_wpnonce = $_REQUEST['_wpnonce'];

      switch ($_REQUEST['wp_crm_action']) {

        case 'delete_user':
          $user_id = $_REQUEST['user_id'];

          if(wp_verify_nonce($_wpnonce, 'wp-crm-delete-user-' . $user_id)) {
            //** Get IDs of users posts */
            $post_ids = $wpdb->get_col( $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_author = %d", $user_id) );

            //** Delete user and reassign all their posts to the current user */
            if(wp_delete_user($user_id, $current_user->data->ID)) {

              //** Trash all posts */
              if(is_array($post_ids)) {
                foreach($post_ids as $trash_post) {
                  wp_trash_post($trash_post);
                }
              }

              wp_redirect(admin_url('admin.php?page=wp_crm&message=user_deleted'));
            }
          }

        break;

      }

    }

    if($wp_crm['configuration']['replace_default_user_page'] == 'true') {
      add_filter('admin_user_info_links', array('WP_CRM_Core', 'admin_user_info_links'), 10, 2);
     }

    add_filter('admin_title', array('WP_CRM_F', 'admin_title'));
  }


  /**
   * Does user info links
   *
   * @author Reid Williams
   */
  function admin_user_info_links($links, $current_user) {
    $links[8] = '<a href="admin.php?page=wp_crm_add_new&user_id=' .$current_user->ID.  '" title="' . esc_attr__('Edit your profile', 'wp_crm') . '">' . __('Your Profile', 'wp_crm') . '</a>';
    return $links;
  }


  /**
   * Primary function for updating user profiles on back-end.
   *
   * Called before hearder on user editing page.
   *
   * @since 0.01
   *
   */
  function wp_crm_save_user_data() {

    if(wp_verify_nonce($_REQUEST['wp_crm_update_user'], 'wp_crm_update_user')) {
      $args = $_REQUEST['wp_crm']['args'];

      $user_data = $_REQUEST['wp_crm']['user_data'];

      //** Add extra user_data data */
      $user_data['admin_color'][0]['value'] = $_REQUEST['admin_color'];
      $user_data['show_admin_bar_front'][0]['value'] = $_REQUEST['show_admin_bar_front'];

      $args['admin_save_action'] = true;
      wp_crm_save_user_data($user_data, $args);
    }

  }


  /**
   * Sets up sortable columns columns
     *
     * @since 0.01
     *
     */
    function sortable_columns($columns) {
        global $wp_crm;

        if(!empty($wp_crm['data_structure']) && is_array($wp_crm['data_structure']['attributes'])) {
            foreach($wp_crm['data_structure']['attributes'] as $slug => $data) {
                if(isset($data['overview_column']) && $data['overview_column'] == 'true')
                    $columns[$slug] = $slug;
            }
        }

        $columns = apply_filters('wp_crm_admin_sortable_columns', $columns);

        return $columns;
    }

    
  /**
   * Header functions
   *
   * Loads after admin_enqueue_scripts, admin_print_styles, and admin_head.
   * Loads before: favorite_actions, screen_meta
   *
   * @since 0.1
   */
    function admin_head() {
      global $current_screen, $wp_filter, $wp_crm;

      do_action("wp_crm_header_{$current_screen->id}", $current_screen->id);

      switch($current_screen->id)  {

        case 'toplevel_page_wp_crm':break;

        case 'crm_page_wp_crm_add_new':break;

      }

      if($wp_crm['configuration']['developer_mode'] == 'true') {
        echo '<script type="text/javascript">var wp_crm_dev_mode = true;</script>';
      }

    }

    
    /**
     * Returns columns for specific person type based on $_GET[page] variable
     *
     * Used by DataTables to figure out which columns to render.
     *
     * @since 0.1
     */
    function overview_columns($columns = false) {
      global $wp_crm;

      $columns['wp_crm_user_card'] = __('Information', 'wp_crm');

      if(!empty($wp_crm['data_structure']) && is_array($wp_crm['data_structure']['attributes'])) {
        foreach(apply_filters('wp_crm_overview_columns', $wp_crm['data_structure']['attributes']) as $slug => $data) {
          if(isset($data['overview_column']) && $data['overview_column'] == 'true') {
            $columns['wp_crm_' . $slug] = $data['title'];
          }
        }
      }

      return $columns;
    }


  /**
   * Sets up plugin pages and loads their scripts
   *
   * @since 0.01
   * @todo Make position incriment by one to not override anything
   *
   */
  function admin_menu() {
    global $wp_crm, $menu, $submenu, $current_user;

    do_action('wp_crm_admin_menu');

    //** Replace default user management screen if set */
    $position = ($wp_crm['configuration']['replace_default_user_page'] == 'true' ? '70' : '33');

    /** Setup main overview page */
    $wp_crm['system']['pages']['core'] = add_menu_page('CRM', 'CRM', 'WP-CRM: View Overview', 'wp_crm', array('WP_CRM_Core', 'page_loader'), '', $position);

    //* Setup child pages (first one is used to be loaded in place of 'CRM' */
    $wp_crm['system']['pages']['overview'] = add_submenu_page('wp_crm', __('All People', 'wp_crm'),__('All People', 'wp_crm'), 'WP-CRM: View Overview', 'wp_crm', array('WP_CRM_Core', 'page_loader'));
    $wp_crm['system']['pages']['add_new'] = add_submenu_page('wp_crm', __( 'New Person', 'wp_crm'),  __( 'New Person', 'wp_crm'), 'WP-CRM: View Profiles', 'wp_crm_add_new', array('WP_CRM_Core', 'page_loader'));
    /* $wp_crm['system']['pages']['your_profile'] = add_submenu_page('wp_crm', __('My Profile', 'wp_crm'), __('My Profile', 'wp_crm'), 'WP-CRM: View Profiles', 'wp_crm_add_new&user_id=' . $current_user->data->ID , array('WP_CRM_Core', 'page_loader')); */
    $wp_crm['system']['pages']['settings'] = add_submenu_page('wp_crm', __('Settings', 'wp_crm'), __('Settings', 'wp_crm'), 'WP-CRM: Manage Settings', 'wp_crm_settings', array('WP_CRM_Core', 'page_loader'));

    //** Migrate any pages that are under default user page */
    if($wp_crm['configuration']['replace_default_user_page'] == 'true') {

      $wp_crm_excluded_sub_pages = apply_filters('wp_crm_excluded_sub_pages', array(5,10,15));

      if(is_array($submenu['users.php'])) {
        foreach($submenu['users.php'] as $sub_key => $sub_pages_data) {

          if(in_array($sub_key, $wp_crm_excluded_sub_pages)) {
            continue;
          }

          //** Fix links (there may be a better way) */
          $sub_pages_data[2] = 'admin.php?page=' . $sub_pages_data[2];

          $submenu['wp_crm'][$sub_key] = $sub_pages_data;
        }
      }


    }

  }

  
  /**
   * Used for loading back-end UI
   *
   * All back-end pages call this function, which then determines that UI to load below the headers.
   *
   * @since 0.01
   */
  function page_loader() {
    global $wp_crm, $screen_layout_columns, $current_screen, $wpdb, $crm_messages, $user_ID, $wp_crm_user;

    $file_path = WP_CRM_Path . "/core/ui/{$current_screen->base}.php";

    if(file_exists($file_path)) {
      include $file_path;
    } else {
      echo "<div class='wrap'><h2>Error</h2><p>Template not found:" . $file_path. "</p></div>";
    }

  }
  

  /**
   * Can enqueue scripts on specific pages, and print content into head
   *
   * @uses $current_screen global variable
   * @since 0.01
   *
   */
  function admin_enqueue_scripts() {
    global $current_screen, $wp_properties, $wp_crm;

    //** Load scripts on specific pages */
    switch($current_screen->id)  {

      case 'toplevel_page_wp_crm':
        wp_enqueue_script('wp-crm-data-tables');
        wp_enqueue_script('google-jsapi');
        wp_enqueue_style('wp-crm-data-tables');

        $contextual_help[] = '<h3>' . __('General</h3>', 'wp_crm') . '</h3>';
        $contextual_help[] = '<p>' .  __('This page is used to filter and find various users. Visit the Settings page to select which attributes to show on the overview.', 'wp_crm') . '</p>';

        $contextual_help[] = '<p>' .  __('Exporting', 'wp_crm') . '</p>';
        $contextual_help[] = '<p>' .  __('Once you narrow down the user results to the ones you want to export, click "Show Actions" and then "Export to CSV" to generate a comma separated flle.', 'wp_crm') . '</p>';
        $contextual_help[] = '<p>' .  __('The CSV export will only include the user data as defined in Data tab, on the Settings page.', 'wp_crm') . '</p>';

        $contextual_help = apply_filters('wp_crm_contextual_help', array('page' => $current_screen->id, 'content' => $contextual_help));
        add_contextual_help($current_screen->id, implode("\n", $contextual_help['content']));

       break;

      case 'crm_page_wp_crm_add_new':
        wp_enqueue_script('post');
        wp_enqueue_script('jquery-autocomplete');
        wp_enqueue_script('jquery-datepicker');
        wp_enqueue_script('thickbox');
        wp_enqueue_style('thickbox');

        $contextual_help[] = __('<h3>User Editing</h3>', 'wp_crm');
        $contextual_help[] = __('<p>Please visit the WP-CRM Settings page to determine which fields to display on the editing page.</p>', 'wp_crm');

        $contextual_help[] = __('<h3>User Activity History</h3>', 'wp_crm');
        $contextual_help[] = __('<p>The activity history can be used to log notes regarding a user, and will display any incoming messages generated by the user when using a WP-CRM contact form.</p>', 'wp_crm');

        $contextual_help = apply_filters('wp_crm_contextual_help', array('page' => $current_screen->id, 'content' => $contextual_help));
        add_contextual_help($current_screen->id, implode("\n", $contextual_help['content']));

        if(function_exists('add_screen_option')) {
          add_screen_option('layout_columns', array('max' => 2, 'default' => 2) );
        }


      break;

      case 'crm_page_wp_crm_settings':
        wp_enqueue_script('jquery-ui-widget');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-mouse');

        $contextual_help[] = '<h3>' . __('Automation', 'wp_crm') . '</h3>';
        $contextual_help[] = '<p>' . __('Use other attribute as components. Example: <b>[last_name], [rank]</b> will become <b>Smith, Sgt.</b>', 'wp_crm') . '</p>';

        $contextual_help[] = '<h3>' . __('Roles - Hidden Attributes', 'wp_crm') . '</h3>';
        $contextual_help[] = '<p>' . __('If certain user attributes are not applicable to certain roles, such as "Client Type" to the "Administrator" role, you can elect to hide the unapplicable attributes on profile editing pages.', 'wp_crm') . '</p>';

        $contextual_help[] = '<h3>' . __('Predefined Values', 'wp_crm') . '</h3>';
        $contextual_help[] = '<p>' . __('If you want your attributes to have predefiend values, such as in a dropdown, or a checkbox list, enter a comma separated list of values you want to use.  You can also get more advanced by using taxonomies - to load all values from a taxonomy, simply type ine: <b>taxonomy:taxonomy_name</b>.', 'wp_crm') . '</p>';

        $contextual_help[] = '<h3>' . __('Shortcode Forms', 'wp_crm') . '</h3>';
        $contextual_help[] = '<p>' . __('Shortcode Forms, which can be used for contact forms, or profile editing, are setup here, and then inserted using a shortcode into a page, or a widget. The available contact form attributes are taken from the WP-CRM attributes, and when filled out by a user, are mapped over directly into their profile. User profiles are created based on the e-mail address, if one does not already exist, for keeping track of users. ', 'wp_crm') . '</p>';
        $contextual_help[] = '<p>' . __('If a new user fills out a form, an account will be created for them based on the specified role.  ', 'wp_crm') . '</p>';

        $contextual_help[] = '<h3>' . __('Notifications and Trigger Actions', 'wp_crm') . '</h3>';
        $contextual_help[] = '<p>' . __('Notification messages can be fired off when certain events, such as contact form submission, are executed.  Multiple notification events can be attached to a single <b>trigger action</b>. Multiple tags, such as [user_email] and [display_name], are available to be used as dynamically replaceable tags when setting up notifications.', 'wp_crm') . '</p>';
        $contextual_help[] = '<p>' . __('Which tags are available depend on the trigger event, but in most cases all user data slugs can be used.  On a contact form message, <b>[message_content]</b>, <b>[profile_link]</b> and <b>[trigger_action]</b> variables are also available.', 'wp_crm') . '</p>';

        $contextual_help = apply_filters('wp_crm_contextual_help', array('page' => $current_screen->id, 'content' => $contextual_help));
        add_contextual_help($current_screen->id, implode("\n", $contextual_help['content']));
      break;

    }

    //** Include on all pages */
    wp_enqueue_script('wp_crm_profile_editor');
    wp_enqueue_script('wp_crm_global');
    wp_enqueue_style('wp_crm_global');

    //** Automatically insert styles sheet if one exists with $current_screen->ID name */
    if(file_exists(WP_CRM_Path . "/css/{$current_screen->id}.css")) {
      wp_enqueue_style($current_screen->id . '-style', WP_CRM_URL . "/css/{$current_screen->id}.css", array(), WP_CRM_Version, 'screen');
    }

    //** Automatically insert JS sheet if one exists with $current_screen->ID name */
    if(file_exists(WP_CRM_Path . "/js/{$current_screen->id}.js")) {
      wp_enqueue_script($current_screen->id . '-js', WP_CRM_URL . "/js/{$current_screen->id}.js", array('jquery'), WP_CRM_Version, 'wp_crm_global');
    }

  }

  /**
   * Modify admin body class on CRM  pages for CSS
   *
   * Note: The white-space on the end of 'wp_crm ' is intentional.
   *
   * @return string|$request a modified request to query listings
   * @since 0.5
   *
   */
   function admin_body_class() {
    global $current_screen, $wp_crm_user, $current_user;

    switch($current_screen->id)  {

      case 'toplevel_page_wp_crm':
      case 'crm_page_wp_crm_settings':

        $classes[] = 'wp_crm';

      break;

      case 'crm_page_wp_crm_add_new':

        $classes[] = 'wp_crm';

        if($wp_crm_user) {

          if($current_user->data->ID == $wp_crm_user['ID']['default'][0]) {
            $classes[] = 'wp_crm_my_profile';
          }

          $classes[] = 'wp_crm_existing_user';
        } else {

          $classes[] = 'wp_crm_new_user';

        }


      break;

    }

    if(is_array($classes)) {
      echo implode(' ', $classes);
    }

  }


  /**
   * Adds "Settings" link to the plugin overview page
   *
   *
   * @since 0.60
   *
   */
   function plugin_action_links( $links, $file ){

     if ( $file == 'wp-crm/wp-crm.php' ){
      $settings_link =  '<a href="'.admin_url("admin.php?page=wp_crm_settings").'">' . __('Settings','wp_crm') . '</a>';
      array_unshift( $links, $settings_link ); // before other links
    }
    return $links;
  }
}
