<?php

  /**
   * Metaboxes for the main overview page
   *
   * @since 0.01
   *
   */
  class toplevel_page_wp_crm {

  /**
   * Actions metabox used for primary filtering purposes
   *
   *
   * @uses CRM_User_List_Table class
   * @since 0.01
   *
   */
    function actions($wp_list_table) {

    ?>
    <div class="misc-pub-section">

    <?php $wp_list_table->search_box( 'Search', 'wp_crm_text_search' ); ?>

    <?php $wp_list_table->views(); ?>

    </div>

    <div class="major-publishing-actions">
      <div class="other-action">
        <span class="wp_crm_subtle_link wp_crm_toggle" toggle="wp_crm_user_actions"><?php _e('Show Actions', 'wp_crm'); ?></span>
      </div>
      <div class="publishing-action">
        <?php submit_button( __('Filter Results'), 'button', false, false, array('id' => 'search-submit') ); ?>
      </div>
      <br class='clear' />
    </div>

    <div class="wp_crm_user_actions hidden">
      <ul class="wp_crm_action_list">
      <li class="wp_crm_orange_link wp_crm_export_to_csv"><?php _e('Export to CSV', 'wp_crm'); ?></li>
      <?php if(WP_CRM_F::get_quantifiable_attributes()) { ?>
      <li class="wp_crm_orange_link wp_crm_visualize_results"><?php _e('Visualize User Data', 'wp_crm'); ?></li>
      <?php } ?>
      <?php do_action('wp_crm_user_actions'); ?>
      </ul>
    </div>


    <?php
    }

    function performed_actions($wp_list_table) { ?>
    <div class="wp_crm_quick_report_wrapper"></div>
    <?php
    }

  }



class crm_page_wp_crm_add_new {



  /**
   * Contact history and messages for a user
   *
   *
   * @todo Fix delete link to be handled internally and not depend on built-in user management
   * @since 0.01
   *
   */
  function user_activity_history($object) {
    global $wpdb;

    $user_id = WP_CRM_F::get_first_value($object['ID']);

    $all_messages = WP_CRM_F::get_events('import_count=&object_id=' . $user_id);

    $limited_messages = WP_CRM_F::get_events('object_id=' . $user_id);

    $per_page = 10;

    if(current_user_can('WP-CRM: Add User Messages')) { ?>
  <div class="wp_crm_activity_top">
    <input class='wp_crm_toggle_message_entry button' type='button' value='<?php _e('Add Message'); ?>' />
    <?php do_action('wp_crm_user_activity_history_top', $object); ?>
  </div>
  <?php } ?>

  <div class="wp_crm_new_message hidden">
    <textarea id='wp_crm_message_content'></textarea>

    <div class="wp_crm_new_message_options_line">

      <div class="alignleft">
        <div class="wp_crm_show_message_options"><?php _e('Show Options', 'wp_crm'); ?></div>
        <div class="wp_crm_message_options hidden">
        <?php _e('Date:', 'wp_crm'); ?>
        <input class="datepicker" />
        </div>
      </div>
      <div class="alignright"><input type='button' id='wp_crm_add_message' value='<?php _e('Add Message', 'wp_crm'); ?>'/></div>
    </div>
   </div>

   <table id="wp_crm_user_activity_stream" cellpadding="0" cellspacing="0">
    <thead></thead>
    <tbody>
    <?php if($user_id) { WP_CRM_F::get_user_activity_stream("user_id={$user_id} ", $limited_messages); } ?>
    </tbody>
   </table>

  <div class="wp_crm_stream_status wp_crm_load_more_stream" limited_messages="<?php echo count($limited_messages); ?>" all_messages="<?php echo count($all_messages); ?>"  per_page="<?php echo $per_page; ?>">
    <?php /* if($limited_messages < $all_messages) { ?>
    <span class="wp_crm_counts"><?php printf(__('Showing <span class="current_count">%1s</span> messages of <span class="total_count">%2s</span>. Load <span class="more_count">%3s</span> more.', 'wp_crm'),count($limited_messages),  count($all_messages), $per_page); ?><span>
    <?php } */ ?>
  </div>

  <?php

  }

  function primary_information($user_object) {
    global $wp_crm;
    $user_role = WP_CRM_F::get_first_value($user_object['role']);

    ?>
    <table class="form-table">
    <?php if(!empty($wp_crm['data_structure']) && is_array($wp_crm['data_structure']['attributes'])) : ?>
      <?php foreach($wp_crm['data_structure']['attributes'] as $slug => $attribute):

        $row_classes = array();

        $row_classes[] = (@$attribute['has_options'] ? 'wp_crm_has_options' : 'wp_crm_no_options');
        $row_classes[] = (@$attribute['required'] == 'true' ? 'wp_crm_required_field' : '');
        $row_classes[] = (@$attribute['primary'] == 'true' ? 'primary' : 'not_primary');
        $row_classes[] = ((is_array($wp_crm['hidden_attributes'][$user_role]) && in_array($slug, $wp_crm['hidden_attributes'][$user_role])) ? 'hidden' : '');
        $row_classes[] = 'wp_crm_user_entry_row';
        $row_classes[] = "wp_crm_{$slug}_row";

        ?>
        <tr meta_key="<?php echo esc_attr($slug); ?>" wp_crm_input_type="<?php echo esc_attr($attribute['input_type']); ?>" class="<?php echo implode(' ', $row_classes); ?>">
          <th>
          <?php if(@$attribute['input_type'] != 'checkbox' || isset($attribute['options'])): ?>
            <?php ob_start();?>
            <label for="wp_crm_<?php echo $slug; ?>_field">
              <?php echo $attribute['title']; ?>
            </label>
            <div class="wp_crm_description"><?php echo $attribute['description']; ?></div>
            <?php $label = ob_get_contents(); ob_end_clean(); ?>
            <?php echo apply_filters('wp_crm_user_input_label', $label, $slug, $attribute, $user_object); ?>
          <?php endif; ?>
          </th>
          <td class="wp_crm_user_data_row"  wp_crm_attribute="<?php echo $slug; ?>">
            <div class="blank_slate hidden" show_attribute="<?php echo $slug; ?>"><?php echo (!empty($attribute['blank_message']) ? $attribute['blank_message'] : "Add {$attribute['title']}"); ?></div>
            <?php echo WP_CRM_F::user_input_field($slug, $user_object[$slug], $attribute, $user_object); ?>

            <?php if(isset($attribute['allow_multiple']) && $attribute['allow_multiple'] == 'true'): ?>
              <div class="add_another"><?php _('Add Another'); ?></div>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
    </table>
  <?php

  }

  /**
   * Sidebar metabox for administrative user actions
   *
   *
   * @todo Fix delete link to be handled internally and not depend on built-in user management
   * @since 0.01
   *
   */
  function special_actions($object) {
    global $current_user, $wpdb, $wp_filter, $user_id;

    $current_user_id = $current_user->ID;
    $user_id = $object['ID']['default'][0];
    $profileuser = get_user_to_edit($user_id);

    if($user_id == $current_user_id) {
      $own_profile = true;
    }

   ?>
   
  <div id="minor-publishing">
  
  <ul class="wp_crm_advanced_user_actions_wrapper">

    <li class="wp_crm_advanced_user_actions">
      <div class="wp_crm_toggle_advanced_user_actions wp_crm_link"><?php _e('Toggle Settings'); ?></div>
      <div class="wp_crm_advanced_user_actions wp-tab-panel">

      <?php if(current_user_can( 'edit_users' )) { ?>

      <?php if(current_user_can('WP-CRM: Change Passwords')) { ?>
      <?php _e('Set Password:', 'wp_crm'); ?>
      <ul class="wp_crm_edit_password">
        <li>
          <input type="password" autocomplete="off" value="" size="16" class="wp_crm_user_password" id="wp_crm_password_1" name="wp_crm[user_data][user_pass][<?php echo rand(1000,9999); ?>][value]" />
          <span class="description"><?php _e('Type in new password twice to change.'); ?></span>
        </li>

        <li>
          <input type="password" autocomplete="off" value="" size="16" class="wp_crm_user_password" id="wp_crm_password_2" />
          <span class="description"><?php _e('Type your new password again.'); ?></span>
        </li>

      </ul>
      <?php } ?>

      <ul>
        <?php if(current_user_can('edit_roles')) { ?>
        <li class="wp_crm_edit_roles">
          <label for="wp_crm_role"><?php _e('Capability Role:', 'wp_crm'); ?></label>
          <select id="wp_crm_role" <?php echo ($own_profile ? ' disabled="true" ' : ''); ?> name="wp_crm[user_data][role][<?php echo rand(1000,9999); ?>][value]">
            <option value=""></option>
            <?php wp_dropdown_roles($object['role']['default'][0]); ?>
          </select>
        </li>
        <?php }?>

        <li class="wp_crm_capability_bar">
          <input name="show_admin_bar_front" type="hidden" value="false"  />
          <input name="show_admin_bar_front" type="checkbox" id="show_admin_bar_front" value="true" <?php checked( _get_admin_bar_pref( 'front', $profileuser->ID ) ); ?> />
          <label for="show_admin_bar_front"><?php _e('Show Admin Bar when viewing site.'); ?> </label>
        </li>

      </ul>

      <?php } ?>

      <?php if(current_user_can('WP-CRM: Change Color Scheme')) {
        _e('Color Scheme:', 'wp_crm');
        do_action( 'admin_color_scheme_picker' );
        } ?>

      </div>
    </li>

  </ul>

  <?php if(count($wp_filter['show_user_profile']) || count($wp_filter['profile_personal_options'])) { ?>
  <div class="wp_crm_user_api_actions">
  <?php
  add_filter( 'wpi_user_information', array('WP_CRM_F', 'wpi_user_information') );
  if($own_profile) {
    do_action( 'show_user_profile', $profileuser );
  } else {
    do_action( 'edit_user_profile', $profileuser );
  }
  ?>

  </div>
  <?php } ?>

  <?php if(current_user_can( 'edit_users' ))  { do_action('wp_crm_metabox_special_actions'); } ?>

</div>

  <div class="major-publishing-actions">

    <div class="other-action">
      <span class="wp_crm_subtle_link wp_crm_toggle" toggle="wp_crm_user_actions"><?php _e('Show Actions'); ?></span>
    </div>


  <div id="publishing-action">
      <input type="hidden" value="Publish" id="original_publish" name="original_publish">
      <?php if(current_user_can( 'edit_users' ) || (current_user_can('add_users') && $object['new'])) { ?>
      <input type="submit" accesskey="p" tabindex="5" value="<?php echo ($object['new'] ? __('Save', 'wpp_crm') : __('Update', 'wpp_crm')); ?>" class="button-primary" id="publish" name="publish">
      <?php } else { ?>
      <input type="submit" accesskey="p" tabindex="5" value="<?php echo ($object['new'] ? __('Save', 'wpp_crm') : __('Update', 'wpp_crm')); ?>" class="button-primary" id="publish" name="publish" disabled="true">
      <?php } ?>
    </div>
  <div class="clear"></div>

</div>


<div class="wp_crm_user_actions hidden">
  <ul class="wp_crm_action_list">

<?php if(current_user_can( 'WP-CRM: Add User Messages' )) { ?>
  <li class="wp_crm_orange_link wp_crm_toggle_message_entry"><?php _e('Add a general note.', 'wp_crm'); ?></li>
<?php } ?>


<?php do_action('wp_crm_single_user_actions', $object); ?>

<?php if((current_user_can( 'remove_users' ) || current_user_can( 'delete_users' )) && (!$object['new'] && $user_id != $current_user->ID)){ ?>
  <li class="wp_crm_orange_link"><a href="<?php echo  wp_nonce_url( "admin.php?wp_crm_action=delete_user&page=wp_crm&user_id={$user_id}", 'wp-crm-delete-user-' . $user_id ); ?>" class="submitdelete deletion"><?php _e('Delete'); ?></a></li>
<?php } ?>



  </ul>
</div>



<?php



  }


}


