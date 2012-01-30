<?php
function crmtb_install() {
   global $wpdb;
   $table_name = $wpdb->prefix ."crm_config";
   
	$sql = "CREATE TABLE `".$table_name."` (
		  `id` int(11) NOT NULL,
		  `url` varchar(500) default NULL,
		  `username` varchar(255) default NULL,
		  `password` varchar(255) default NULL,
		  PRIMARY KEY  (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
	
	   require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	   dbDelta($sql);
}

function crm_install_data() {
	  global $wpdb;
	  $table_name = $wpdb->prefix ."crm_config";
	  $url = "http://example.com";
      $username = "admin";
      $password = "admin";
      $rows_affected = $wpdb->insert( $table_name, array( 'url' => $url, 'username' => $username, 'password' => md5($password) ) );
}

function crmtb_uninstall()
{
	   global $wpdb;
	   $table_name = $wpdb->prefix . "crm_config";
	   $sql = "DROP TABLE ".$table_name ;
	   require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	   $wpdb->query($sql);
}

function wp_plugin_admin(){
	ob_start();
	if (function_exists('add_menu_page')){
    $capability = 'level_0';
    $icon_url = '/wp-content/plugins/sugarcrm-plugin/images/sugar_icon.png';
    $position = '';
	// add_menu_page('page_title, menu_title, capability, menu_slug, function, icon_url, position');
	add_menu_page('Sugar CRM', 'SugarCRM', $capability, 'sugar_crm', 'include_crm', $icon_url);
	}
	if (function_exists('add_submenu_page')){
	// add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);
	add_submenu_page('sugar_crm', '', '', $capability, 'sugar_crm', 'include_crm');
	add_submenu_page('sugar_crm', 'Sugar CRM', 'SugarCRM Admin', $capability, 'sugar_crm', 'include_crm');
	add_submenu_page('sugar_crm', 'SugarCRMConfig', 'SugarCRM Config', $capability, 'crm_config', 'crm_config');
	}
}
add_action('admin_menu', 'wp_plugin_admin');

function include_crm() {
global $wpdb;
$table_name = $wpdb->prefix . "crm_config";
require_once('lib/nusoap.php'); 
$crm_option = $wpdb->get_row("SELECT * FROM ".$table_name." WHERE id = 0", ARRAY_A);
$config['sugar_server'] = $crm_option['url']."/soap.php?wsdl";

// the Sugar username and password to login via SOAP
$config['login'] = array(
    'user_name' => $crm_option['username'],
    'password' => $crm_option['password']
);

$config['application_name'] = substr(strrchr($crm_option['url'], '/'), 1);

?>


<?php
	
//print_r($config);
// open a connection to the server
$sugarClient = new nusoap_client($config['sugar_server'], 'wsdl');

if(!$sugarClient){
echo 'Please check your settings here';
exit();
}
/* echo "<pre>";
print_r($sugarClient);
die; */
$err = $sugarClient->getError();
if ($err) {
    var_dump($err);
    die("asdfas");
}

$sugarClientProxy = $sugarClient->getProxy();
	
if(!$sugarClientProxy){
echo 'URL is not valid for SugarCRM config settings , please check it out ';
echo '<a href='.site_url('wp-admin/admin.php?page=crm_config').'>Here</a>';
exit();
}

// login using the credentials above
$result = $sugarClientProxy->login($config['login'], $config['application_name']);

$session_id = $result['id'];
/*
if($session_id  ){
echo  'UserName or PassWord was wrong. Please Check it out ';
echo '<a href='.site_url('wp-admin/admin.php?page=crm_config').'>Here</a>';
exit();
}
*/
$result = $sugarClientProxy->seamless_login($session_id);
?>

<div id="crm_panel">
<iframe src="<?php echo $crm_option['url'] ?>/index.php?module=Home&action=index&MSID=<?php echo $session_id ?>" scrolling="auto" frameborder="0" width="100%" height="2000"></iframe>
</div>
<?php 
}
?>
