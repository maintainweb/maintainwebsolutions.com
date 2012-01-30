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


$code = $_REQUEST['code'];
$import_terms = isset($_REQUEST['import_terms'])?$_REQUEST['import_terms']:false;

$sco = new ImportExport();
$res = $sco->restore_from_string($post_id,$code,$error,$import_terms);
if(false===$res){
	send_error_die($error);
}

$ret = array(
	'R'=>'OK',
	'MSG'=>'',
	'URL'=> html_entity_decode(get_edit_post_link( $post_id ))
);
die(json_encode($ret));
?>