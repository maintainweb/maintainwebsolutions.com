<?php

/**
Plugin Name: SWS: CSS Tooltip add-on
Plugin URI: http://plugins.righthere.com/add-ons/sws-css-tooltip
Description: This plugin is a add-on for Styles with Shortcodes. It adds two additional Shortcodes to the Styles with Shortcodes plugin for creating beautiful looking Tooltips in WordPress. Choose between 30 color schemes. All based on CSS without any images.
Version: 1.1.0
Author: Righthere LLC
Author URI: http://plugins.righthere.com
 **/
if(defined('WPCSS')&&WPCSS>'1.0.1'){

}else{
	return;
}

class plugin_sws_css_tooltip {
	function plugin_sws_css_tooltip(){
		add_action('plugins_loaded',array(&$this,'wp_register'),100);
	}
	
	function wp_register(){
		global $sws_plugin;
		
		$sws_plugin->add_bundle('css-tooltip','CSS Tooltip',ABSPATH . 'wp-content/plugins/' . basename(dirname(__FILE__)).'/includes/bundle.php');
		wp_register_style( 'css-tooltip', trailingslashit(get_option('siteurl')) . 'wp-content/plugins/' . basename(dirname(__FILE__)) . '/thetooltip/thetooltip.css', array(),'1.0.0' );			
		$sws_plugin->add_style('css-tooltip','CSS Tooltip','http://plugins.righthere.com/');
	}
}

new plugin_sws_css_tooltip();

//-- Installation script:---------------------------------
function plugin_sws_css_tooltip_install(){
	global $bundle;
	require_once ABSPATH . 'wp-content/plugins/' . basename(dirname(__FILE__)).'/includes/bundle.php';	
	$o = new ImportExport(); 
	$o->import_bundle($bundle,$error);	
	return true;
}
register_activation_hook(__FILE__, 'plugin_sws_css_tooltip_install');
//-------------------------------------------------------- 
?>