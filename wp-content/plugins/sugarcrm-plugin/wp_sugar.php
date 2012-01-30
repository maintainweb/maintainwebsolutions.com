<?php
/*
Plugin Name: SugarCRM Plugin
Plugin URI: http://sugar.crmformbuilder.com
Description: SugarCRM Plugin That Displays Your SugarCRM Admin Panel Within Wordpress Admin Area. This plugin is also required for use with our SugarCRM Form Builder Module.
Version: 1.3.1
Author: Pro Marketing Group Inc.
Author URI: http://sugar.crmformbuilder.com
License: GPL
*/
/*  Copyright 2011  Pro Marketing Group Inc.  (email : info@crmformbuilder.com)
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
//ob_clean();
//session_start();
ini_set('display_errors',0);
error_reporting(0);
define('SUGARCRM_ABS_PATH', dirname(__FILE__).'/');
define('ROOT_DIRECTORY', 'sugarcrm-plugin/');
define('SUGARCRM_SHORT_CODE', 'CRM_FORM');
require_once (ABSPATH .'/wp-includes/pluggable.php');
$GLOBALS['sugarEntry']=true;
// This was moved to wpr_crm.php
// add_action('admin_menu', 'wp_plugin_admin');
register_activation_hook(__FILE__,'crmtb_install');
register_activation_hook(__FILE__,'crm_install_data');
register_deactivation_hook( __FILE__, 'crmtb_uninstall');
require_once SUGARCRM_ABS_PATH.'log/logger_manager.php';
logger_manager::$log_dir  = SUGARCRM_ABS_PATH;
$GLOBALS['wpr_logger'] = logger_manager::getLogger();
$GLOBALS['wpr_logger']->setLevel('debug');
$GLOBALS['wpr_logger']->debug('SUGARCRM_ABS_PATH: '.SUGARCRM_ABS_PATH);
$wp_sugar_obj = new wp_sugar();
add_action('init', array('wp_sugar', 'get_form_html'));
add_action( 'wp_print_scripts', 'enqueue_my_scripts' );
add_action( 'wp_print_styles', 'enqueue_my_styles' );
wp_enqueue_script( 'my_awesome_script1', '/wp-content/plugins/sugarcrm-plugin/jquery.ui.core.js', array( 'jquery' ));
wp_enqueue_script( 'my_awesome_script2', '/wp-content/plugins/sugarcrm-plugin/jquery.ui.widget.js', array(  ));
wp_enqueue_script( 'my_awesome_script3', '/wp-content/plugins/sugarcrm-plugin/jquery.ui.datepicker.js', array( ));
wp_enqueue_script( 'my_awesome_script4', '/wp-content/plugins/sugarcrm-plugin/jquery.validate.js', array( ));
wp_enqueue_style( 'my_awesome_style1', '/wp-content/plugins/sugarcrm-plugin/cal_css/jquery.ui.all.css', array(  ));

add_shortcode(SUGARCRM_SHORT_CODE, array(&$wp_sugar_obj, 'get_form_html'));

require_once SUGARCRM_ABS_PATH.'classes.php';
require_once SUGARCRM_ABS_PATH.'class.Template.php';
require_once SUGARCRM_ABS_PATH.'rest_utils.php';
class wp_sugar {
	const FORM_TARGET					= 'Wordpress';
	const CAPTCHA_SESSION_VAR			= 'captcha';
	// Messages to be displayed to user or logged
	const INVALID_FORM 					= 'Invalid form';
	const HOT_LINKING_NOT_ALLOWED_MSG 	= 'Hot-linking is not allowed';
	const INVALID_CAPTCHA_CODE 			= 'Invalid captcha code entered';
	/**
	 * Database connection resource
	 * @var resource
	 */
	var $con;
	var $form_id			= '';
	var $form_title			= '';
	var $table 				= '';
	var $fields 			=	array();
	/**
	 * Values in this variable will be used for saving to DB
	 * @var array
	 */
	var $values_db			= array();
	/**
	 * Values in this variable will be used for sending email
	 * @var array
	 */
	var $values_email		= array();
	/**
	 * Label will be the values for their corresponding field_names
	 * @var array
	 */
	var $names				= array();
	/**
	 * Will contain the message to be shown after form submission
	 * @var string
	 */
	var $wordpress_content	= '';
	/**
	 * To determine whether the form should be displayed after submitting
	 * @var bool
	 */
	var $show_form			= true;
	/**
	 * field_name of the user email
	 * @var string
	 */
	var $sender_email_field	= '';
	/**
	 * Email of the user
	 * @var string
	 */
	var $sender_email		= '';
	/**
	 * to determine whether the submitted form is a duplicate
	 * @var bool
	 */
	var $is_duplicate		= false;
	// Form Details
	var $name							= '';
	var $form_name						= '';
	var $captcha						= '';
	var $redirect_to_link				= '';
	var $confirmation_message			= '';
	var $delivery_method				= '';
	var $no_duplicate					= '';
	var $no_duplicate_type				= '';
	var $no_duplicate_message			= '';
	var $no_hot_linking					= '';
	var $allowed_hot_linking_domain		= '';
	var $email_to						= '';
	var $email_cc						= '';
	var $email_bcc						= '';
	var $subject						= '';
	var $email_template					= '';
	var $email_format					= '';
	var $auto_response_message			= '';
	var $auto_response_from_email		= '';
	var $auto_response_from_name		= '';
	var $auto_response_sub				= '';
	var $use_phpmailer					= false;
	var $use_smtp						= false;
	var $smtp_host						= '';
	var $smtp_port						= '';
	var $smtp_user						= '';
	var $smtp_password					= '';
	var $smtp_security					= '';
	var $rest_session_id	='';
	var $url = 'http://localhost/crm61_3/service/v2/rest.php';
	 

	function get_form_html($attributes, $content=NULL) {
		$form_html	= '';
		if (isset($attributes['id'])) {
				$form_html	= $this->generate_form($attributes['id'],$url);
		}
	
		return $form_html;
	}
	function set_properties($properties) {
		$GLOBALS['wpr_logger']->debug('method set_properties($properties) called with array of size '.sizeof($properties));
		foreach ($properties as $name => $value) {
			if (isset($this->$name)) {
				$this->$name	= $value;
			}
		}
	}
	function wp_sugar() {
		require_once SUGARCRM_ABS_PATH.'rest_utils.php';
	global $wpdb;
$table_name = $wpdb->prefix . "crm_config";

$crm_option = $wpdb->get_row("SELECT * FROM ".$table_name." WHERE id = 0", ARRAY_A);
 $this->url = $crm_option['url']."/service/v2/rest.php";

$app_name = substr(strrchr($crm_option['url'], '/'), 1);
$result = $this->doRESTCALL(
'login',array('user_auth'=>array('user_name'=>$crm_option['username'],'password'=>$crm_option['password'], 'version'=>'.01'), 'application_name'=>$app_name, 'name_value_list' =>
array(array('name' => 'notifyonsave', 'value' => 'false'))));
if($_REQUEST['submit'])
{
$post_data =SimpleArrayTonameValuePair($_POST);

// would convert array to name-value list to save data in CRM
$parmas = array(
'session' => $this->rest_session_id,
'module' => $_POST['module'],
'name_value_list' => $post_data,
);
$res =$this->doRESTCALL('set_entry',$parmas);
if($_POST['redirect_url'])
{
	if(!empty($_POST['confirmation_message']))
			{
			$mod_strings['LBL_THANKS_FOR_SUBMITTING'] = $_POST['confirmation_message'];
            $query_str ='?thanks='.$_POST['confirmation_message'];
			}
			$red_url =$_POST['redirect_url'].$query_str;
header("Location: ".$red_url);
die();
}
}
// specify the REST web service to interact with 

// Open a curl session for making the call 
$parmas = array(
'session' => $this->rest_session_id,
'module_name' => $_POST['module'],
'query' => $where,
'order_by' => 'date_entered',
'offset' => 0,
'select_fields' => array(),
'link_name_to_fields_array' => array(),
'max_results' => 1,
'deleted' => 0,

);
}
function doRESTCALL( $method, $data) {

ob_start();
$ch = curl_init();
$headers = (function_exists('getallheaders'))?getallheaders(): array();
$_headers = array();
foreach($headers as $k=>$v){
$_headers[strtolower($k)] = $v;
}
// set URL and other appropriate options
curl_setopt($ch, CURLOPT_URL, $this->url);
curl_setopt ($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $_headers);
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0 );
$post_data = 'method=' . $method . '&input_type=JSON&response_type=JSON';
//$json = getJSONobj();
$jsonEncodedData = json_encode($data);

$post_data = $post_data . "&rest_data=" . $jsonEncodedData;

curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
$result = curl_exec($ch);

curl_close($ch);
$result = explode("\r\n\r\n", $result, 2);

$response_data = json_decode($result[1]);
if(!$this->rest_session_id)
$this->rest_session_id = $response_data->id	;
//ob_end_flush();

return $response_data;
}
	function get_form($form_name) 
	{
		$form= array();
		$where =" frm_forms.form_name = '".addslashes($form_name)."' AND form_target = '".self::FORM_TARGET."' ";
		$lnk = array(array('id'=>'id'));
		//$parameters = array( $this->rest_session_id, 'frm_Forms', $where ,'date_entered' ,1, false,$lnk1,0);
		$parmas = array(
'session' => $this->rest_session_id,
'module_name' => 'frm_Forms',
'query' => $where,
'order_by' => 'date_entered',
'offset' => 0,
'select_fields' => array(),
'link_name_to_fields_array' => array(),
'max_results' => 1,
'deleted' => 0,

);

		$frm_obj =$this->doRESTCALL('get_entry_list',$parmas);
$form_data =nameValuePairToSimpleArray($frm_obj->entry_list[0]->name_value_list);
$this->module =$form_data['form_module'];
	return $form_data;
	
	}
	function generate_form($form_name)
	{
		
		$form_data	= array();
		if($_REQUEST['thanks'])
		{
		echo '<div style="align:center"><b>'.$_REQUEST['thanks']."</b></div>";
		die();
		}
		$form_data	= $this->get_form($form_name);


		$form_html = str_replace('fm','form',$form_data['template_html']);
		
		// if no such form, target of form is not wordpress
		if (empty($form_html)) {
			$GLOBALS['wpr_logger']->warn(self::INVALID_FORM.': '.$form_name);
			return self::INVALID_FORM.': '.$form_name;
		}
		$form_html = $this->populate_fields($form_html, $form_name);
		$form_html = $this->populate_css($form_data['template_css'], $form_html);
		$form_html = $this->generateSubmitButton($form_html);
		$form_html = $this->populate_js($form_html, $form_name);
		$form_html = $this->populate_captcha($form_html);
		$form_html = $this->form_hidden_fields($form_html, $form_name ,$form_data);
		$form_html = $this->form_action($form_html);
		$form_html =html_entity_decode($form_html);
		return 	$form_data['text_before_form'].'<br>'.$form_html.$form_data['text_after_form'].'<br>';	
	}
	function populate_fields($form_html, $form_name)
	{
		
		$template = new Template();
		$fields = array();
		
		$where =" frm_formitems.id in (SELECT frm_formitems.id FROM frm_forms INNER JOIN frm_formitems ON frm_formitems.frm_forms_id_c = frm_forms.id  WHERE frm_forms.form_name = '".addslashes($form_name)."' AND frm_forms.deleted = 0 AND frm_formitems.deleted = 0 )  ";
		$lnk = array(array('id'=>'id'));
		//$parameters = array( $this->rest_session_id, 'frm_Forms', $where ,'date_entered' ,1, false,$lnk1,0);
		$parmas = array(
'session' => $this->rest_session_id,
'module_name' => 'frm_FormItems',
'query' => $where,
'order_by' => 'field_order',
'offset' => 0,
'select_fields' => array(),
'link_name_to_fields_array' => array(),
'max_results' => 100,
'deleted' => 0,
);

		$fld_obj =$this->doRESTCALL('get_entry_list',$parmas);
		foreach($fld_obj->entry_list as $field_obj){
	$form_data[] =nameValuePairToSimpleArray($field_obj->name_value_list);
		}
	
	foreach($form_data as $row)
		{
			$fields['{field_'.$row['field_name'].'}'] = $template->addField($row['field_name'], $row['field_type'], $row['field_required'], $row['field_elements'], $row['field_title']);
		}
		foreach ($fields as $key=>$field)
		{
		$form_html = str_replace($key,$field,$form_html);
		}

	return $form_html;
	
		}
	public function populate_css($template_css, $form_html)
	{
	
		$fields = array();

		$template_css ='<style type="text/css">';
		$template_css .= $template_css;
		$template_css .= '.entry-content .error{color:red; float:left; clear: both; padding:0; margin:0;}';
		$template_css .='</style>';
		$form_html = str_replace('{form_css}',$template_css,$form_html);
		return $form_html;
	}
	function generateSubmitButton($form_html)
	{
		$field = '<input type="submit" name="submit" id="submit" value="Submit">';
		$form_html = str_replace('{form_submit}',$field,$form_html);
		return $form_html;
	}
	function populate_js($form_html, $form_name)
	{

$field .= '<script type="text/javascript">
jQuery(document).ready(function(){
jQuery.validator.methods.equal = function(value, element, param) {
		return value == param;
};
jQuery("#'.$form_name.'").validate();
});
</script>';
		$form_html = str_replace('{'.$form_name.'_validate}',$field,$form_html);
		return $form_html;
	}
	function populate_captcha($form_html)
	{
		$field = '<input class="required" type="text" name="captcha" id="captcha" /> <img src="'.site_url('wp-content/plugins/sugarcrm-plugin/captcha.php?width=100&height=40&characters=5').'"/>';
		//$field .='<input type="text" name="re-captcha" id="re-captcha" value="'.$_SESSION['form_captcha_code'].'">';		
		$form_html = str_replace('{form_captcha}',$field,$form_html);
		return $form_html;
	}
	function form_hidden_fields($form_html,$form_name,$frm_data) 
	{
		$field = '<input type="hidden" name="redirect_url" id="redirect_url" value="'.$frm_data['redirect_to_link'].'">';
		$field .= '<input type="hidden" name="confirmation_message" id="confirmation_message" value="'.$frm_data['confirmation_message'].'">';
		$field .= '<input type="hidden" name="form_id" id="form_id" value="'.$form_name.'">';
		$field .= '<input type="hidden" name="module" id="module" value="'.$this->module.'">';
		$form_html = str_replace('{form_hidden_fields}',$field,$form_html);
		return $form_html;
	}
	function form_action($form_html) 
	{
$url =$this->getCurrentURL();
		$field ='';
		$form_html = str_replace('{form_action}',$field,$form_html);
		return $form_html;
	}
	function getCurrentURL()
	{
		$href = "http:";
		if(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
		{
			$href = 'https:';
		}
		$href.= "//".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		return $href;
	}
}
	function crm_config() {
		include('crm_config_options.php');
	}

	// Moved to wpr_crm.php
	/*
	function crm_config_cptions() {
		add_options_page("Sugar CRM Config", "Sugar CRM Config", 1, "crm_config", "crm_config");
	}
	add_action('admin_menu', 'crm_config_cptions');
	*/
	include_once "wpr_crm/wpr_crm.php";
?>