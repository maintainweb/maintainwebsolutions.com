<?php

// Setup  -- Probably want to keep this stuff... 
// Set up action for section registration...
add_action('pagelines_register_sections', 'base_sections');

/**
 * Hello and welcome to Base! First, lets load the PageLines core so we have access to the functions 
 */	
require_once( dirname(__FILE__) . '/setup.php' );
	
// For advanced customization tips & code see advanced file.
	//--> require_once(STYLESHEETPATH . "/advanced.php");
	
// ====================================================
// = YOUR FUNCTIONS - Where you should add your code  =
// ====================================================

// ADDING CUSTOM SECTIONS ------- //
	// Register a Drag&Drop HTML Section for the admin. 
	// A pullquote section was created here for demonstration purposes
	
	// Sections should be named: section.[your section name].php and placed in the sections folder.

	function base_sections(){
	
	/* 
		Your custom sections get registered in here...
		PageLines Register Section Arguments: 
			1. Section Class Name, 
			2. Directory name (or filename if in root of 'sections' folder), 
			3. Init Filename (if different from directory name), 
			4. Section setup and variable array
*/
	
		pagelines_register_section('BasePullQuote', 'pullquote', null, array('child' => true) );
		pagelines_register_section('BaseSidebar','sb_base', null, array('child' => true) );
		
	}

// ABOUT HOOKS --------//
	// Hooks are a way to easily add custom functions and content to the Platform theme. There are hooks placed strategically throughout the theme 
	// so that you insert code and content with ease.

// HOOKS EXAMPLE --------//
	// Below is an example of how you would add a social media icon to the icons in header (branding section)
	// We have placed a hook at the end of the icon set specifically add new icons without modifying code or having to worry about your edits 
	// getting thrown out during the upgrade process. The way to use hooks goes a little like this:
	
	// add_action('hook_name','function name');
	
	// ---> uncomment to load 
	//add_action('pagelines_branding_icons_end', 'add_icons_to_branding');

	// function name
	function add_icons_to_branding(){
		// This hook adds a stumbleupon icon to the header of your theme. The class referenced in the link can be seen in the style.css 
		// and is the image from the CSS is placed in the images folder
		?>
		<a href="http://www.stumbleupon.com/stumbler/pagelines/" class="stumbleupon"></a>
	<?php }
	// end function

// ABOUT FILTERS ----------//

	// Filters allow data modification on-the-fly. Which means you can change something after it was read and compiled from the database,
	// but before it is shown to your visitor. Or, you can modify something a visitor sent to your database, before it is actually written there.

// FILTERS EXAMPLE ---------//

	// The following filter will add the font  Ubuntu into the font array $thefoundry.
	// This makes the font available to the framework and the user via the admin panel.

add_filter ( 'pagelines_foundry', 'my_google_font' );
function my_google_font( $thefoundry ) {
	$myfont = array( 'Ubuntu' => array(
			'name' => 'Ubuntu',
			'family' => '"Ubuntu", arial, serif',
			'web_safe' => true,
			'google' => true,
			'monospace' => false
			)
		);
	return array_merge( $thefoundry, $myfont );
}

// ADDING NEW TEMPLATES --------//
	// Want another page template for drag and drop? Easy :)
	// 			1. Add File called page.[page-id].php to Base
	// 			2. Add /* Template Name: Your Page Name */ and Call to 'setup_pagelines_template();' to that file (see page.base.php)
	// 			3. Add 'pagelines_add_page('[page-id]', '[Page Name]');' to this functions.php file
			
	// Add Base Page
if ( function_exists( 'pagelines_add_page' ) ) pagelines_add_page('base', 'Custom Page');
	
// OVERRIDE SECTION TEMPLATES --------//
	// Want more customization control over any of the core section templates in PlatformPro? Just override the template file.
	// To do that, just add a file called template.[section-id].php to this child theme and it will override the section templates
	// for the section with that ID.  For example, template.boxes.php will override the boxes templates.
	// Once overridden you can copy the code from that section, paste it there and edit to your heart's content. 
	





