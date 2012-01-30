<div class="wrap">
			<?php    echo "<h2>" . __( 'CRM Config', 'crmcon_trdom' ) . "</h2>"; ?>
<?php
if($_POST['crm-settings-save']){
echo '<strong>Options saved</strong></p></div>';

require_once 'crm-setting.php';
}
global $wpdb;
$table_name = $wpdb->prefix . "crm_config";
$crm_option = $wpdb->get_row("SELECT * FROM ".$table_name." WHERE id = 0", ARRAY_A);

require_once "iframe-setting.php";
    echo '<hr/>';

?>
			
	