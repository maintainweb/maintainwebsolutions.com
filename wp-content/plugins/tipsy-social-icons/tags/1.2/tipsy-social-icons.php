<?php
/*
Plugin Name: Tipsy Social Icons
Plugin URI: http://tommcfarlin.com/tipsy-social-icons
Description: A widget that makes it easy to display a variety of popular social icons along with a Facebook-style tooltips.  Icons provided by Komodo Media's Social Icon Pack and Jason Frame's jQuery Tipsy plugin for effects. Customizable via CSS.
Version: 1.2
Author: Tom McFarlin
Author URI: http://tommcfarlin.com/
License:

    Copyright 2011 Tom McFarlin (tom@tommcfarlin.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class Tipsy_Social_Icons extends WP_Widget {

	/*--------------------------------------------------*/
	/* Constructor
	/*--------------------------------------------------*/
	
	/**
	 * The widget constructor. Specifies the classname and description, instantiates
	 * the widget, loads localization files, and includes necessary scripts and
	 * styles.
	 */
	function Tipsy_Social_Icons() {
	
		$widget_opts = array (
			'classname' => 'tipsy-social-icons',
			'description' => __('Displays social icons with tooltips as configured in the Tipsy Social Icon admin panel.', 'tipsy-social-icons')
		);		
		
		$this->WP_Widget('tipsy-social-icons', __('Tipsy Social Icons', 'tipsy-social-icons'), $widget_opts);
		load_plugin_textdomain('tipsy-social-icons', false, dirname(plugin_basename( __FILE__ ) ) . '/lang/' );
		$this->_register_scripts_and_styles();
		
	} // end constructor

	/*--------------------------------------------------*/
	/* API Functions
	/*--------------------------------------------------*/
	
	/**
	 * Outputs the content of the widget.
	 *
	 * @args			The array of form elements
	 * @instance
	 */
	function widget($args, $instance) {
		
		extract($args, EXTR_SKIP);
		
		echo $before_widget;
		
		$digg = empty($instance['digg']) ? '' : apply_filters('digg', $instance['digg']);
		$dribbble = empty($instance['dribbble']) ? '' : apply_filters('dribbble', $instance['dribbble']);
		$email = empty($instance['email']) ? '' : apply_filters('email', $instance['email']);
		$facebook = empty($instance['facebook']) ? '' : apply_filters('facebook', $instance['facebook']);
		$flickr = empty($instance['flickr']) ? '' : apply_filters('flickr', $instance['flickr']);
		$forrst = empty($instance['forrst']) ? '' : apply_filters('forrst', $instance['forrst']);
		$lastfm = empty($instance['lastfm']) ? '' : apply_filters('lastfm', $instance['lastfm']);
		$linkedin = empty($instance['linkedin']) ? '' : apply_filters('linkedin', $instance['linkedin']);
		$posterous = empty($instance['posterous']) ? '' : apply_filters('posterous', $instance['posterous']);
		$rss = empty($instance['rss']) ? '' : apply_filters('rss', $instance['rss']);
		$skype = empty($instance['skype']) ? '' : apply_filters('skype', $instance['skype']);
		$twitter = empty($instance['twitter']) ? '' : apply_filters('twitter', $instance['twitter']);
		$vimeo = empty($instance['vimeo']) ? '' : apply_filters('vimeo', $instance['vimeo']);
		$youtube = empty($instance['youtube']) ? '' : apply_filters('youtube', $instance['youtube']);
		$soundcloud = empty($instance['soundcloud']) ? '' : apply_filters('soundcloud', $instance['soundcloud']);
		$use_large_icons = empty($instance['use_large_icons']) ? '' : apply_filters('use_large_icons', $instance['use_large_icons']);
		$use_fade_effect = empty($instance['use_fade_effect']) ? '' : apply_filters('use_fade_effect', $instance['use_fade_effect']);
		
		include('tipsy-social-icons-content.php');
		
		echo $after_widget;
		
	} // end widget
	
	/**
	 * Processes the widget's options to be saved.
	 *
	 * @new_instance	The previous instance of values before the update.
	 * @old_instance	The new instance of values to be generated via the update.
	 */
	function update($new_instance, $old_instance) {
		
		$instance = $old_instance;
		
		$instance['digg'] = $this->_strip($new_instance, 'digg');
		$instance['dribbble'] = $this->_strip($new_instance, 'dribbble');
		$instance['email'] = $this->_strip($new_instance, 'email');
		$instance['facebook'] = $this->_strip($new_instance, 'facebook');
		$instance['flickr'] = $this->_strip($new_instance, 'flickr');
		$instance['forrst'] = $this->_strip($new_instance, 'forrst');
		$instance['lastfm'] = $this->_strip($new_instance, 'lastfm');
		$instance['linkedin'] = $this->_strip($new_instance, 'linkedin');
		$instance['posterous'] = $this->_strip($new_instance, 'posterous');
		$instance['rss'] = $this->_strip($new_instance, 'rss');
		$instance['skype'] = $this->_strip($new_instance, 'skype');
		$instance['twitter'] = $this->_strip($new_instance, 'twitter');
		$instance['vimeo'] = $this->_strip($new_instance, 'vimeo');
		$instance['youtube'] = $this->_strip($new_instance, 'youtube');
		$instance['soundcloud'] = $this->_strip($new_instance, 'soundcloud');
		$instance['use_large_icons'] = $this->_strip($new_instance, 'use_large_icons');
		$instance['use_fade_effect'] = $this->_strip($new_instance, 'use_fade_effect');
		
		return $instance;
		
	} // end widget
	
	/**
	 * Generates the administration form for the widget.
	 *
	 * @instance	The array of keys and values for the widget.
	 */
	function form($instance) {
	
		$instance = wp_parse_args(
			(array)$instance,
			array(
				'digg' => '',
				'dribbble' => '',
				'email' => '',
				'facebook' => '',
				'flickr' => '',
				'forrst' => '',
				'lastfm' => '',
				'linkedin' => '',
				'posterous' => '',
				'rss' => '',
				'skype' => '',
				'twitter' => '',
				'vimeo' => '',
				'youtube' => '',
				'soundcloud' => '',
				'use_large_icons' => '',
				'use_fade_effect' => ''
			)
		);
		
		$digg = $this->_strip($instance, 'digg');
		$dribbble = $this->_strip($instance, 'dribbble');
		$email = $this->_strip($instance, 'email');
		$facebook = $this->_strip($instance, 'facebook');
		$flickr = $this->_strip($instance, 'flickr');
		$forrst = $this->_strip($instance, 'forrst');
		$lastfm = $this->_strip($instance, 'lastfm');
		$linkedin = $this->_strip($instance, 'linkedin');
		$posterous = $this->_strip($instance, 'posterous');
		$rss = $this->_strip($instance, 'rss');
		$skype = $this->_strip($instance, 'skype');
		$twitter = $this->_strip($instance, 'twitter');
		$vimeo = $this->_strip($instance, 'vimeo');
		$youtube = $this->_strip($instance, 'youtube');
		$soundcloud = $this->_strip($instance, 'soundcloud');
		$use_large_icons = $this->_strip($instance, 'use_large_icons');
		$use_fade_effect = $this->_strip($instance, 'use_fade_effect');
		
		include('tipsy-social-icons-form.php');
		
	} // end form
	
	/*--------------------------------------------------*/
	/* Private Functions
	/*--------------------------------------------------*/
	
	/**
	 * Registers and enqueues stylesheets for the administration panel and the
	 * public facing site.
	 */
	private function _register_scripts_and_styles() {
	
		if(is_admin()) {
			$this->_load_file('tipsy-social-icons-admin', '/tipsy-social-icons/css/tipsy-social-icons-admin.css');
		} else {
			wp_enqueue_script("jquery");
			$this->_load_file('tipsy-social-icons', '/tipsy-social-icons/css/tipsy-social-icons.css');
			$this->_load_file('tipsy-social-icon-plugin-script', '/tipsy-social-icons/javascript/jquery.tipsy.js', true);
			$this->_load_file('tipsy-social-icon-plugin-styles', '/tipsy-social-icons/css/jquery.tipsy.css');
			$this->_load_file('tipsy-social-icon-script', '/tipsy-social-icons/javascript/tipsy-social-icons.js', true);
		} // end if/else
		
	} // end register_scripts_and_styles
	
	/**
	 * Convenience method for stripping tags and slashes from the content
	 * of a form input.
	 *
	 * @obj			The instance of the argument array
	 * @title		The title of the element from which we're stripping tags and slashes.
	 */
	private function _strip($obj, $title) {
		return strip_tags(stripslashes($obj[$title]));
	} // end strip
	
	/**
	 * Helper function for registering and loading scripts and styles.
	 *
	 * @name	The 	ID to register with WordPress
	 * @file_path		The path to the actual file
	 * @is_script		Optional argument for if the incoming file_path is a JavaScript source file.
	 */
	private function _load_file($name, $file_path, $is_script = false) {
		$url = WP_PLUGIN_URL . $file_path;
		$file = WP_PLUGIN_DIR . $file_path;
		if(file_exists($file)) {
			if($is_script) {
				wp_register_script($name, $url);
				wp_enqueue_script($name);
			} else {
				wp_register_style($name, $url);
				wp_enqueue_style($name);
			} // end if
		} // end if
	} // end _load_file
	
} // end class
add_action('widgets_init', create_function('', 'register_widget("Tipsy_Social_Icons");'));
?>