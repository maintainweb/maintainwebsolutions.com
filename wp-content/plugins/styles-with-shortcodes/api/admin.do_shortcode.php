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
//-----------
if ( !current_user_can( 'edit_post', $post_id ) ){
	die('No access');
} 
//-------------
$content = $_REQUEST['content'];
$content = do_shortcode($content);

$ret = array(
	'R'=>'OK',
	'MSG'=>'',
	'DATA'=> $content
);
die(json_encode($ret));
?>