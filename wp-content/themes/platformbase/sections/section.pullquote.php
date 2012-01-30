<?php
/*
	Section: Pullquote Section
	Author: PageLines
	Description: Shows a pull quote taken from 
	Version: 1.0.0

	Demonstrates how to create a cool site section in 50 lines of code! 
	Note: the only other piece to make this work is the 'pagelines_register_section()' function in your functions.php
*/
class BasePullQuote extends PageLinesSection {
   function __construct( $registered_settings = array() ) {
	
		// BASIC INFO
			/* The name and ID of the section */
			$name = __('Pull Quote Section', 'pagelines');
			$id = 'pullquote';
		
		// SETTINGS
		
			// Setup description of the section..
			$default_settings['description'] = 'Displays one of several quotes; edit the section code to change quotes.<br/> <small>(Used to demonstrate adding a section with a Platform Child Theme)</small>';
		
			// The template areas this section works with.. 
			// Examples areas: 'main', 'content', 'header', 'footer', 'morefoot', sidebar1, array('main', 'content'), etc....
			$default_settings['workswith'] = array('main');
		
			// The icon users will see in the admin.. add the full url here 
			$default_settings['icon'] = CHILD_IMAGES . '/icon-pullquote.png';
		
		// OPS
			/* Draw section using the section API - don't need to touch this*/
			$settings = wp_parse_args( $registered_settings, $default_settings );
		   	parent::__construct($name, $id, $settings);    
   }
	
	/* Use this function to create the template for the section */	
 	function section_template() { 
	
		// The Quotes
		$thequotes = array(
				'Benjamin Franklin'	=> 'Anger is never without a reason, but seldom with a good one.', 
				'Latin Proverb'		=> 'Fortune favors the bold.', 
				'George Washington'	=> 'It is better be alone than in bad company.',
				'Thomas Edison'		=> 'Everything comes to him who hustles while he waits.',
				'Mark Twain'		=> 'Action speaks louder than words but not nearly as often.',
				'Albert Einstein'	=> 'A man should look for what is, and not for what he thinks should be.',
				'Thomas Jefferson'	=> 'Delay is preferable to error.'
			);
		
		// Randomly Select One
		$quote_key = array_rand($thequotes);
	
		// Draw the HTML... ?>
	
	<div class="thepullquote"><?php echo $thequotes[$quote_key];?></div><div class="thecitation"> &mdash; <?php echo $quote_key;?></div>
	
<?php }
	// Some of the optional functions not used here.
	function section_options($optionset = null, $location = null) {} /* Adds Options in the admin. Use this function to add them; see PageLines.com for more info */
	function section_persistent(){} /* Runs in every page, including in admin (not used in this section) */
	function section_head(){} /* Runs in site header, only if section is active (not used here) */

} /* End of section class - No closing tag needed */