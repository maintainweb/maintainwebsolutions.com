<?php

/**
Plugin Name: Styles with Shortcodes for WordPress
Plugin URI: http://plugins.righthere.com/styles-with-shortcodes/
Description: Now you can customize your content faster and easier than ever before with custom style shortcodes. This plugin lets you easily customize your content by using Shortcodes. Choose from more than 70 built in shortcodes like; jQuery Toggles and Tabs, Tooltips, Column shortcodes, Gallery and Image shortcodes, Button Styles, Alert Box Styles, Pullquotes, Blockquotes, Twitter buttons, Retweet button, Facebook Like buttons and many more!
You can even create your own or import and export shortcodes.
Version: 1.5.0
Author: Alberto Lau (RightHere LLC)
Author URI: http://plugins.righthere.com
 **/

define('WPCSS','1.0.2'); 
define('WPCSS_PATH', ABSPATH . 'wp-content/plugins/' . basename(dirname(__FILE__)). "/" ); 
define("WPCSS_URL", trailingslashit(get_option('siteurl')) . 'wp-content/plugins/' . basename(dirname(__FILE__)) . '/' );

if(!function_exists('property_exists')):
function property_exists($o,$p){
	return is_object($o) && 'NULL'!==gettype($o->$p);
}
endif;

class custom_shortcode_styling {
	var $options;
	var $sws_scripts = array();
	var $sws_styles = array();
	var $bundles = array();
	function custom_shortcode_styling(){
		$this->options = get_option('sws_options');
		add_action('plugins_loaded',array(&$this,'plugins_loaded'));
		
		if(isset($this->options['disable_autop'])&&$this->options['disable_autop']==1){
			remove_filter ('the_content', 'wpautop');
		}
	}
	
	function plugins_loaded(){		
		wp_enqueue_script('jquery');	
//		wp_enqueue_script('jquery-ui-core');
//		wp_enqueue_script('jquery-tools',WPCSS_URL.'js/jquery.tools.min.js',array('jquery'),'1.2.5');	
		//-- register scripts ----
		require_once WPCSS_PATH.'includes/bundled_scripts_and_styles.php';	
		new bundled_scripts_and_styles();//
		
		require_once WPCSS_PATH.'includes/class.CSShortcodes.php';
		require_once WPCSS_PATH.'includes/class.ImportExport.php';		
		require_once WPCSS_PATH.'includes/class.CSShortcodesLoad.php';	
		
		if(is_admin()):
			wp_enqueue_style('wpcss-toggle',WPCSS_URL.'css/toggle.css',array(),'1.0.3');
			wp_enqueue_script('wpsws',WPCSS_URL.'js/sws.js',array(),'1.0.1');
			
			wp_enqueue_style('colorpicker',WPCSS_URL.'colorpicker/css/colorpicker.css',array(),'1.0.0');
			wp_enqueue_script('sws-colorpicker',WPCSS_URL.'colorpicker/js/colorpicker.js',array('jquery'),'1.0.0');			
			
			require_once WPCSS_PATH.'includes/class.CSSEditor.php';
			require_once WPCSS_PATH.'includes/class.CSSOptions.php';
			
			wp_enqueue_script('jquery-tools');//for rageinput support.
		endif;		
	}
	
	function add_script($id,$label,$url=''){
		$this->sws_scripts[] = (object)array('id'=>$id,'label'=>$label,'url'=>$url);
	}
	
	function add_style($id,$label,$url='',$ui_theme=false){
		$this->sws_styles[] = (object)array('id'=>$id,'label'=>$label,'url'=>$url,'ui_theme'=>$ui_theme);
	}
	
	function add_bundle($id,$label,$path){
		$this->bundles[$id]=(object)array('id'=>$id,'label'=>$label,'path'=>$path);
	}
}  

global $sws_plugin;
$sws_plugin = new custom_shortcode_styling();


//-- Installation script:---------------------------------
function sws_install(){
	global $bundle;
	require_once WPCSS_PATH.'includes/bundle.php';	
	require_once WPCSS_PATH.'includes/class.ImportExport.php';
	require_once WPCSS_PATH.'includes/class.CSShortcodes.php';
	CSShortcodes::init_taxonomy();
	CSShortcodes::init_post_type();
	$o = new ImportExport(); 
	$o->import_bundle($bundle,$error);
	return true;
}
register_activation_hook(__FILE__, 'sws_install');
//-------------------------------------------------------- 
?>