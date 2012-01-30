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
//-----------
if(!current_user_can('manage_options')&&!current_user_can(CSSOptions::capability)){
	send_error_die('No access');
}
//-------------

$bundle_name = $_REQUEST['bundle'];
if(trim($bundle_name)==''){
	send_error_die('Settings error, missing parameter.');
}

$import_export = new ImportExport();
$res = $import_export->restore_bundle_from_name($bundle_name,$error);
if(false===$res){
	$error = trim($error)==''?'Error restoring bundle(Undefined import/export error)':$error;
	send_error_die($error);
}

$ret = array(
	'R'=>'OK',
	'MSG'=>''
);
die(json_encode($ret));
?>