<?php


// ==========================================================
// = ADVANCED FUNCTIONS - Code for advanced customizers :)  =
// ==========================================================


// How to add options and custom option types. 

// CALLING OPTIONS ------------ // 

// Custom options can be called from child template and sections 
// using the 'pagelines_option('my_option_id')' function

// ADDING OPTIONS ------------ // 

// ---> uncomment to load
//add_filter('pagelines_custom_options', 'pagelines_add_options');
function pagelines_add_options($custom_option_array){

	$my_option_array = array(

		'my_option_id' => array(
			'default' 		=> 'hello', // default value
			'type' 			=> 'text', // type of option - see class.options.ui.php in core for list
			'inputlabel' 	=> 'Option Label', // label on input
			'title' 		=> 'Test Option From Child',	// Title of option
			'shortexp' 		=> 'Added in child to demonstrate extension.', // Short explanation of option
			'exp' 			=> 'Some text to describe how this option should be used.... Cool huh?' // Inline doc for option
		), 
		'my_custom_option_type' => array(
			'default' 		=> 'hello', // default value
			'type' 			=> 'custom_cat_select', // type of option - see class.options.ui.php in core for list
			'inputlabel' 	=> 'Option Label', // label on input
			'title' 		=> 'Custom Option From Child',	// Title of option
			'shortexp' 		=> 'Added option type in child to demonstrate extension.', // Short explanation of option
			'exp' 			=> 'Some text to describe how this option should be used.... Cool huh?' // Inline doc for option
		)

	);
	
	$custom_option_array = array_merge($custom_option_array, $my_option_array);
	
	return $custom_option_array;
}


// ADDING OPTION TYPES ------------ // 

// Create new option type for theme settings: "custom_option_type"
// Hook = pagelines_options_ + 'option_type_id'

// ---> uncomment to load
//add_action('pagelines_options_custom_cat_select', 'custom_cat_select', 10, 2);
function custom_cat_select($oid, $o){ 
	global $post; 
	$categories = get_categories( $args );

?>
	<label class="context" for="<?php pagelines_option_id($oid);?>"><?php echo $o['inputlabel'];?></label><br/>
		<select id="<?php pagelines_option_id($oid);?>" name="<?php pagelines_option_name($oid);?>">
		<option value="">&mdash;<?php _e("SELECT", 'pagelines');?>&mdash;</option>

		<?php foreach($categories as $cid => $c):?>
			<option value="<?php echo $c->category_nicename;?>"><?php echo $c->cat_name;?></option>
		<?php endforeach;?>
	</select>
	
<?php }

