<?php
/*

	Section: Base Sidebar
	Author: Andrew Powers
	Description: The main widgetized sidebar
	Version: 1.0.0
	
	Note: the only other piece to make this work is the 'pagelines_register_section()' function in your functions.php
	
*/

class BaseSidebar extends PageLinesSection {

   function __construct( $registered_settings = array() ) {
	
		/* The name and ID of the section */
		$name = __('Base Sidebar', 'pagelines');
		$id = 'sidebar_base';
	
		/* Setup description, areas it works with, icon, etc...*/
		$default_settings = array(
			'description' 	=> 'A new widgetized sidebar section created by Base. It can be used in standard sidebar templates.',
			'workswith' 	=> array('sidebar1', 'sidebar2', 'sidebar_wrap', 'main'),
			'icon'			=> CORE_IMAGES . '/admin/sidebar.png'
		);
		
		/* Draw section using the section API */
		$settings = wp_parse_args( $registered_settings, $default_settings );
	   	parent::__construct($name, $id, $settings);    
   }

	/* Setup widgetized areas */
   function section_persistent() { 
		$setup = pagelines_standard_sidebar($this->name, $this->settings['description']);
		register_sidebar($setup);
	}


	/* Standard Sidebar Template e.g. widgets */
   function section_template() { 
	 	 pagelines_draw_sidebar($this->id, $this->name);
	}

}

/*
	End of section class
*/