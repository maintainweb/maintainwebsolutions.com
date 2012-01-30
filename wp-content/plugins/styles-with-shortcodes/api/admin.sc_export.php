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
$sco = new ImportExport();
$data = $sco->get_shortcode_from_post_id($post_id,$error,true);
if(false===$data){
	send_error_die('Error generating export data: '.$error);
}

$ret = array(
	'R'=>'OK',
	'MSG'=>'',
	'DATA'=>$data
);
die(json_encode($ret));
?>