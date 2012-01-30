<?php
require_once (preg_replace("/wp-content.*/","wp-blog-header.php",__FILE__));
require_once (preg_replace("/wp-content.*/","/wp-admin/includes/admin.php",__FILE__));

header("HTTP/1.1 200 OK");

wp_enqueue_style('gf-admin', GFCommon::get_base_url() .'/css/admin.css');

wp_enqueue_script('jquery');

register_admin_color_schemes();

function show_table() {
	if(isset($_REQUEST['leadid']) && isset($_REQUEST['form'])) {
			require_once("gravity-forms-addons.php");
			
			$transient = false;
			if(isset($_REQUEST['post'])) {
				$transient = get_transient('gf_form_'.$_REQUEST['form'].'_post_'.$_REQUEST['post'].'_showadminonly');
			}
			
			echo "<div class='wrap' style='padding:1.25em .5em'>".apply_filters('kws_gf_directory_detail', apply_filters('kws_gf_directory_detail_'.(int)$_REQUEST['leadid'], GFDirectory::process_lead_detail(false, '', apply_filters('kws_gf_directory_showadminonly_lightbox', apply_filters('kws_gf_directory_showadminonly_lightbox_'.$_REQUEST['form'], $transient)))))."</div>";
	}
}

wp_iframe('show_table');

?>