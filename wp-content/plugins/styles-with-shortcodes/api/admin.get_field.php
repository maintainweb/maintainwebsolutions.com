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

$name = $_REQUEST['name'];
if(trim($name)==''){
	send_error_die('Property name is not valid');
}
/*
$new_field = array();
foreach(array('name','label','default','content','type','options','min','max') as $field){
	$new_field[$field] = $_POST[$field];
}
*/
$fields = get_post_meta($post_id,'sc_fields',true);
$fields = is_array($fields)?$fields:array();
if(!isset($fields[$name])){
	send_error_die('Field not found.');
}

$response = $fields[$name];
$response->default_value = $response->default;
$response->content = intval($response->content);

$ret = array(
	'R'=>'OK',
	'MSG'=>'',
	'DATA'=> $response
);
die(json_encode($ret));
?>