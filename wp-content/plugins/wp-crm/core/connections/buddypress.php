<?php

/**
  * Name: BuddyPress plugin connector
  * Description: Adds Extra functionality to WP-CRM when the BuddyPress plugin is active.
  * Author: Usability Dynamics, Inc.
  * Version: 1.0
  *
  */
   
//** All BuddyPress actions are initialized after bb_include action is ran. */
add_action( 'bp_include', array('WPC_BuddyPress', 'bp_init' ));

class WPC_BuddyPress {

  /**
   * Only load code that needs BuddyPress to run once BP is loaded and initialized. 
   *
   * @author potanin@UD
   */  
  function bp_init() {

    WP_CRM_F::console_log(sprintf(__('Executing: %1s.', 'wp_crm'), 'WPC_BuddyPress::bb_init()'));
    add_filter('wp_crm_settings_lower', array('WPC_BuddyPress','wp_crm_settings_lower'));
    add_filter('wp_crm_user_action', array('WPC_BuddyPress','wp_crm_user_action'));

  }

  /**
   * Declare new buddypress_profile action.
   *
   * @author potanin@UD
   */
  function wp_crm_settings_lower($wp_crm) {
    $wp_crm['overview_user_actions']['buddypress_profile']['label'] = __('BuddyPress Profile Link','wp_crm');
    return $wp_crm;
  }

 
  /**
   * Modify the default buddypress_profile action's HTML to include a link to the profile.
   *
   * @author potanin@UD
   */ 
  function wp_crm_user_action($action) {
    if($action['action'] != 'buddypress_profile') {
      return $action;
    }
    
    $action['html'] = sprintf(__('<a href="%1s">BuddyPress Profile</a>', 'wp_crm'), bp_core_get_userlink( $action['user_id'], false, true));
    
    return $action;
  }

}



