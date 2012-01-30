<?php
/*
Added By: Hirak Chattoipadhyay
Date: 23.05.2011
*/

require_once('../wp-admin/admin.php');

$crm_url = $_POST['crm_url'];

	   global $wpdb;
$last = $crm_url[strlen($crm_url)-1]; 

if($last=="/")
$crm_url = substr($crm_url,0,-1);

$crm_user = $_POST['crm_user'];
$crm_pwd = md5($_POST['crm_pwd']);

$data =array('url'=>$crm_url, 'username'=>$crm_user, 'password'=>$crm_pwd);

$where = array('id'=>0);

$fromat = array( '%s', '%s', '%s');

$where_format =array('%d');

$table_name = $wpdb->prefix . "crm_config";

$wpdb->update('wp_crm_config', $data, $where, $format = null, $where_format = null );

/*** END CONFIGURATION PARAMETERS ***/

//$config['login']['password'] = md5($config['login']['password']);



?>