jQuery(document).ready(function() {

  /* Force default values when value is changed */
  jQuery(".wp_crm_force_default").live("change", function(event) {
    wp_crm_handle_default_values(this, event);
  });
  
  /* Automatically set fields to have default values */
  jQuery(".wp_crm_force_default").each(function() {
    wp_crm_handle_default_values(this);
  });

  /* Incomplete function to get all available trigger actions, and add them in dropdown form below message text area */
  jQuery(".wp_crm_trigger_action").live("change", function() {

    return;

    var actions = new Array();
    var selected_triggers = new Array();

    var parent = jQuery(this).closest(".wp_crm_dynamic_table_row");
    var container = jQuery(".wp_crm_trigger_action_arguments", parent);

    var select_element;

    /* Build array of checked triggers for this notificaiton */
    jQuery("input[type=checkbox].wp_crm_trigger_action:checked", parent).each(function() {
      var this_trigger = jQuery(this).val();

      if(notification_action_arguments[this_trigger] !== undefined) {

      }
    });

    select_element = "<select>";

    for (var i = 0; i < actions.length; i++) {
      /* select_element = select_element + '<option value="'+a_slug+'">test' + a_val + '</option>';     */
    }

    select_element = select_element  + "</select>";

    jQuery(container).html(select_element);

  });

  jQuery("#wp_crm_attribute_fields tbody").sortable({
    delay: 50
  });

  jQuery("#wp_crm_attribute_fields tbody tr").live("mouseover", function() {
    jQuery(this).addClass("wp_crm_draggable_handle_show");
  });;

  jQuery("#wp_crm_attribute_fields tbody tr").live("mouseout", function() {
    jQuery(this).removeClass("wp_crm_draggable_handle_show");
  });;


// Show settings array
  jQuery("#wp_crm_show_settings_array, #wp_crm_show_settings_array_cancel").click(function() {

    if(jQuery("#wp_crm_show_settings_array_result").is(":visible")) {
      jQuery("#wp_crm_show_settings_array_cancel").hide();
      jQuery("#wp_crm_show_settings_array_result").hide();
    } else {
      jQuery("#wp_crm_show_settings_array_cancel").show();
      jQuery("#wp_crm_show_settings_array_result").show();
    }

  });

// Show settings array
  jQuery(".wp_crm_toggle_something").click(function() {

    var settings_block = jQuery(this).parents('.wp_crm_settings_block');

   if(jQuery(".wp_crm_class_pre", settings_block).is(":visible")) {
      jQuery(".wp_crm_class_pre", settings_block).hide();
      jQuery(".wp_crm_link", settings_block).hide();
    } else {
      jQuery(".wp_crm_class_pre", settings_block).show();
      jQuery(".wp_crm_link", settings_block).show();
    }

  });

// Query user object
  jQuery("#wp_crm_show_user_object").click(function() {

    var settings_block = jQuery(this).parents('.wp_crm_settings_block');

    jQuery.post(ajaxurl, {action: 'wp_crm_user_object', user_id: jQuery("#wp_crm_user_id").val()}, function(result) {
      jQuery('.wp_crm_class_pre', settings_block).show();
      jQuery('.wp_crm_class_pre', settings_block).text(result);
    });

  });

  // Generate fake users
  jQuery("#wp_crm_generate_fake_users").click(function() {

    var number_of_users = jQuery("#wp_crm_fake_users").val();

    if(!confirm("Are you sure you want to generate " + number_of_users + " user(s)?")) {
      return;
    }

    var settings_block = jQuery(this).parents('.wp_crm_settings_block');

    jQuery.post(ajaxurl, {
      action: 'wp_crm_do_fake_users',
      number: number_of_users,
      do_what: 'generate'
    }, function(result) {
      jQuery('.wp_crm_class_pre', settings_block).show();
      jQuery('.wp_crm_class_pre', settings_block).text(result);
    });

  });


  // Delete fake users
  jQuery("#wp_crm_delete_fake_users").click(function(event) {

    event.preventDefault();

    if(!confirm("Are you sure you want to delete ALL fake users you have generated?\nNone of the fake user information will not be reassigned, but completely removed.")) {
      return;
    }

    var settings_block = jQuery(this).parents('.wp_crm_settings_block');

    jQuery.post(ajaxurl, {
      action: 'wp_crm_do_fake_users',
      do_what: 'remove'
    }, function(result) {
      jQuery('.wp_crm_class_pre', settings_block).show();
      jQuery('.wp_crm_class_pre', settings_block).text(result);
    });

  });

// Return reprot of user meta usage and typical data
  jQuery("#wp_crm_show_meta_report").click(function() {

    var settings_block = jQuery(this).parents('.wp_crm_settings_block');

    jQuery.post(ajaxurl, {action: 'wp_crm_show_meta_report' }, function(result) {
      jQuery('.wp_crm_class_pre', settings_block).show();
      jQuery('.wp_crm_class_pre', settings_block).text(result);
    });


  });


});