<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class bundled_scripts_and_styles {
	function bundled_scripts_and_styles(){
		global $sws_plugin;
		
		$sws_plugin->add_bundle('starter','Starter',WPCSS_PATH.'includes/bundle.php');
		
		wp_register_style('sws-button',WPCSS_URL.'css/sws_button.css',array(),'1.0.0');	
		$sws_plugin->add_style('sws-button','SWS Button','');
		
		//registered by wp
		//$sws_plugin->add_script('jquery-ui-core','jQuery UI core','http://jqueryui.com');
		
		wp_register_script('jquery-tools',WPCSS_URL.'js/jquery.tools.min.js',array('jquery'),'1.2.5');		
		$sws_plugin->add_script('jquery-tools','jQuery TOOLS','http://flowplayer.org/tools/index.html');

		wp_register_script('jquery-ui',WPCSS_URL.'js/jquery-ui-1.8.7.custom.min.js',array(),'1.8.7');
		$sws_plugin->add_script('jquery-ui','jQuery UI','http://jqueryui.com');		
		
		wp_register_style('ui-smoothness',WPCSS_URL.'css/smoothness/jquery-ui-1.8.7.custom.css',array(),'1.8.7');	
		$sws_plugin->add_style('ui-smoothness','UI Smoothness','',true);	
		
		wp_register_style('start',WPCSS_URL.'css/start/jquery-ui-1.8.7.custom.css',array(),'1.8.7');	
		$sws_plugin->add_style('start','UI Start','',true);		
		
		wp_register_script('preloadify',WPCSS_URL.'js/preloadify/jquery.preloadify.js',array('jquery'),'1.0.1');		
		$sws_plugin->add_script('preloadify','Preloadify','');		
		
		wp_register_style('preloadify',WPCSS_URL.'js/preloadify/plugin/css/style.css',array(),'1.0.0');	
		$sws_plugin->add_style('preloadify','Preloadify','');		
		
		wp_register_style('sws-tables',WPCSS_URL.'css/tables.css',array(),'1.0.0');	
		$sws_plugin->add_style('sws-tables','Table templates','');		
	}
}
?>