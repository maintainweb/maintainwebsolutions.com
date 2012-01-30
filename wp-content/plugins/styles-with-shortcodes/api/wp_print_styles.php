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

$style = addslashes(stripslashes(trim(isset($_REQUEST['style'])?$_REQUEST['style']:'')));
if(''!=$style){
	wp_print_styles($style);
}
?>