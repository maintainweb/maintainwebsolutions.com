<?php 
  
if(!empty($wp_crm['data_structure']) && is_array($wp_crm['data_structure']['attributes'])) {
    $attribute_keys = array_keys($wp_crm['data_structure']['attributes']);
} else {
    $attribute_keys = array();
}

 if($_REQUEST['message'] == 'created') {
  WP_CRM_F::add_message(__('Profile created.', 'wp_crm'));
}elseif($_REQUEST['message'] == 'updated') {
  WP_CRM_F::add_message(__('Profile updated.', 'wp_crm'));
}

if($wp_crm_user)  {
	$user_id = $_REQUEST['user_id'];
	$object = $wp_crm_user;  
	$title =  WP_CRM_F::get_primary_display_value($object);
} else {
	$object = array();
	$object['new'] = true;
  $object['user_role']['default'][0] = get_option('default_role');
	$title = __('Add New Person', 'wp_crm');
}

$wp_crm_js = array(
  'user_id' => is_numeric($user_id) ? $user_id : false,
  'hidden_attributes' => $wp_crm['hidden_attributes']
);

if($wp_crm['configuration']['standardize_display_name'] == 'true' && !empty($wp_crm['configuration']['display_name_rule'])) {
  $wp_crm_js['standardize_display_name'] = true;
  $wp_crm_js['display_name_rule'] = $wp_crm['configuration']['display_name_rule'];
} 

if(is_array($wp_crm_js)) {
  echo '<script type="text/javascript">var wp_crm = jQuery.parseJSON(' . json_encode(json_encode($wp_crm_js)) . '); </script>';
}  

?>

<div class="wp_crm_profile_wrapper wrap">
  <div class="wp_crm_ajax_result"></div>
  <?php screen_icon(); ?>
  <h2 class="wp_crm_page_title"><?php echo $title; ?></h2>
  <?php WP_CRM_F::print_messages(); ?>

  <form enctype="multipart/form-data"  name="crm_user" action="admin.php?page=wp_crm_add_new<?php echo ($user_id ? "&user_id=$user_id" : ''); ?>" method="post" id="crm_user">
  <input type="hidden" id="user_id" name="wp_crm[user_data][user_id][0][value]" value="<?php echo $user_id; ?>" />
  <?php
  wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
  wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
  wp_nonce_field( 'wp_crm_update_user', 'wp_crm_update_user', false );
  ?>

  <div id="poststuff"  class="metabox-holder<?php echo 2 == $screen_layout_columns ? ' has-right-sidebar' : ''; ?>">
  <div id="side-info-column" class="inner-sidebar">
    <?php $side_meta_boxes = do_meta_boxes($current_screen->id, 'side', $object); ?>
  </div>

  <div id="post-body">
  <div id="post-body-content">	
  <div class="wp_crm_secondary_ajax_result"></div>
  <?php do_meta_boxes($current_screen->id, 'normal', $object); ?>
  <?php do_meta_boxes($current_screen->id, 'advanced', $object); ?>
  </div>
  </div>
  <br class="clear" />
  </div><!-- /poststuff -->
  </form>
</div>
