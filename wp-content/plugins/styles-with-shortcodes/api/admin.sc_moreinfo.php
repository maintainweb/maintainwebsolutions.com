<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

ob_start();
Header('Cache-Control: no-cache');
Header('Pragma: no-cache');
require_once('../../../../wp-load.php');
$content = ob_get_contents();
ob_end_clean();
//sleep(1);
function send_error_die($msg){
	die(json_encode(array('R'=>'ERR','MSG'=>$msg)));
}

$post_id = intval($_REQUEST['ID']);

if($post_id==0){
	send_error_die('Post ID is not valid.');
}

//-----------
if ( !wp_verify_nonce( $_REQUEST['nonce'], 'csshortcode-css-nonce' )) {
	send_error_die('Settings error, no access.');
}

if ( !current_user_can( 'edit_post', $post_id ) ){
	send_error_die('No access.');
} 
//-------------
global $wpdb;

$code = $_REQUEST['code'];


$sco = new ImportExport();
$obj = $sco->string_to_obj($code);

if(false===$obj){
	send_error_die('Error reading code. (2)');
}

if(false=== $sco->check_shortcode_obj($obj,$error)){
	send_error_die($error.print_r($obj,true));
}

$categories = array();
if(isset($obj->sc_terms) && is_array($obj->sc_terms) && count($obj->sc_terms)>0){
	foreach($obj->sc_terms as $t){
		$categories[] = $t->name;
	}
}

$data = (object)array();
$data->name = $obj->post_title;
$data->shortcode = $obj->sc_shortcode;
$data->category = $categories;
$data->bundle = $obj->sc_bundle;
$data->info = (property_exists($obj,'sc_info'))?$obj->sc_info:array('author'=>'','url'=>'');
$data->warnings = array();

$sql = "SELECT P.ID FROM `$wpdb->postmeta` M INNER JOIN `$wpdb->posts` P ON P.ID = M.post_id WHERE P.ID!={$post_id} AND M.meta_key = \"sc_shortcode\" AND M.meta_value=\"{$obj->sc_shortcode}\" AND P.post_status IN ('publish', 'draft')";

$data->duplicate_posts = $wpdb->get_col($sql,0);
$data->duplicate_links = array();
if(is_array($data->duplicate_posts)&&count($data->duplicate_posts)>0){
	foreach($data->duplicate_posts as $duplicate_id){
		$data->duplicate_links[] = sprintf("<a href=\"%s\">%s</a>",html_entity_decode(get_edit_post_link( $duplicate_id )),$duplicate_id);
	}
}

//---check that a required script is present
if(property_exists($obj,'sc_scripts') && is_array($obj->sc_scripts) && count($obj->sc_scripts)>0 ){
	global $wp_scripts;
	foreach($obj->sc_scripts as $handle){
		if(!array_key_exists($handle,$wp_scripts->registered)){
			$data->warnings[]=sprintf("Shortcode requires a registered javascript library (%s), but it is not active in the system.",$handle);
		}
	}
}
if(property_exists($obj,'sc_styles') && is_array($obj->sc_styles) && count($obj->sc_styles)>0 ){
	global $wp_styles;
	foreach($obj->sc_styles as $handle){
		if(!array_key_exists($handle,$wp_styles->registered)){
			$data->warnings[]=sprintf("Shortcode requires a registered stylesheet (%s), but it is not active in the system.",$handle);
		}
	}
}

$ret = array(
	'R'=>'OK',
	'MSG'=>'',
	'DATA'=>$data
);
die(json_encode($ret));
?>