<?php

/**
Plugin Name: White Label Branding for WordPress
Plugin URI: http://plugins.righthere.com/white-label-branding/
Description: Add your own branding to WordPress. Replace the WordPress logo from the log-in screen and dashboard with your own identity or even your client's. Add custom dashboard panels viewable only to Editors or all users with your own welcome message or help.
Version: 1.5.0
Author: Alberto Lau (RightHere LLC)
Author URI: http://plugins.righthere.com
 **/

define('WLB_PATH', ABSPATH . 'wp-content/plugins/' . basename(dirname(__FILE__)). "/" ); 
define("WLB_URL", trailingslashit(get_option('siteurl')) . 'wp-content/plugins/' . basename(dirname(__FILE__)) . '/' ); 

load_plugin_textdomain('wlb', null, dirname( plugin_basename( __FILE__ ) ).'/languages' );

if(!function_exists('property_exists')):
function property_exists($o,$p){
	return is_object($o) && 'NULL'!==gettype($o->$p);
}
endif;
 
class plugin_white_label_branding_for_wordpress {
	var $id;
	var $plugin_page;
	var $menu;
	var $submenu;
	function plugin_white_label_branding_for_wordpress(){
		$this->id = basename(dirname(__FILE__));
		add_action('plugins_loaded',array(&$this,'plugins_loaded'));

		add_action('init',array(&$this,'init'));
		
		add_action('admin_head',array(&$this,'admin_head'));
		
		add_filter('admin_footer_text', array(&$this,'admin_footer_text'));
		
		add_action('login_head', array(&$this,'login_head'));
		
		add_action('admin_menu', array(&$this,'admin_menu'),1000);
	}
	
	function get_user_role() {
		global $current_user;
		$user_roles = $current_user->roles;
		$user_role = array_shift($user_roles);
		return $user_role;
	}
	
	function admin_menu(){
		global $menu,$submenu;

		$this->menu = $menu;//make a copy of available menus
		$this->submenu = $submenu;
		//if(!current_user_can('manage_options')){
		if($this->get_user_role()!='administrator'){
			if(isset($menu)&&is_array($menu)&&count($menu)>0){
				foreach($menu as $k => $m){
					$id = 'm_'.strtolower(str_replace(' ','_',str_replace('.','_',$m[2])));
					$option_name = $this->id.'_'.$id;
					if($m[2]==get_option($option_name)){
						unset($menu[$k]);
					} 
				}			
			}

			if(isset($submenu)&&is_array($submenu)&&count($submenu)>0){
				foreach($submenu as $key => $submenu_group){
					foreach($submenu_group as $k => $m){
						$id = 'sm_'.strtolower(str_replace(' ','_',str_replace('.','_',$m[2])));
						$option_name = $this->id.'_'.$id;		
						if($m[2]==get_option($option_name)){
							unset($submenu[$key][$k]);
						} 
					}					
				}
		
			}			
			
		}
	}
	
	function login_head(){
		if(isset($_REQUEST['wlb_skip_login'])){
			return true;
		}
	
		if(function_exists('minimeta_init')&&$_SERVER['SCRIPT_NAME']!='/wp-login.php')return;
		$vars = array('login_logo_url','login_background','login_background_color_code','login_template','login_styles_scripts','use_login_template');
		foreach($vars as $var){
			$$var = get_option(sprintf("%s_%s",$this->id,$var));
		}
		
		$bgcss='';
		if(trim($login_background)!=''){
			$tmp = array();
			foreach( array('login_background_repeat','login_background_attachments','login_background_color','login_background_position','login_background_x','login_background_y') as $var){
				$tmp[]=get_option(sprintf("%s_%s",$this->id,$var));
			}
			$login_background = ''==trim($login_background)? 'none' : "url(\"$login_background\")" ;
			array_unshift($tmp, $login_background );
			$bgcss=sprintf("background:%s;",implode(' ',$tmp));
		}else if(trim($login_background_color_code)!=''){
			$login_background_color_code = false===strpos($login_background_color_code,'#')?'#'.$login_background_color_code:$login_background_color_code;
			$bgcss=sprintf("background:%s;",$login_background_color_code);
		}
		
		$login_template = trim($login_template);
		if($login_template!=''){
			$login_template = str_replace("{loginform}","<div id=\"login-form-holder\"></div>",$login_template);
			$login_template = str_replace("{backlink}","<div id=\"login-back-link\"></div>",$login_template);
			$login_template = str_replace("{customlogo}","<div id=\"login-custom-logo\"></div>",$login_template);
			$login_template = rawurlencode($login_template);
		}
?>
<style>
#login h1 {
	display:none !important;
}

#custom-logo {
	width:100%;
	text-align: center;
	padding:0 0 10px 0;
	margin-top: 5em;
}

#login {
	margin-top: 10px !important;
}

#login-wrapper {
	<?php echo $bgcss ?>position:absolute; width:100%; height:100%; top:0; left:0; overflow:auto;
}
</style>
<script type="text/javascript" src="<?php echo WLB_URL ?>js/jquery.js"></script>
<script>
jQuery(document).ready(function($){
	$('#login h1').remove();
<?php if($login_logo_url!=''):?>
	$('body').prepend('<div id="custom-logo"><a href="<?php echo site_url();?>"><img border="0" src="<?php echo $login_logo_url?>" /></a></div>');	
<?php endif;?>
<?php if($use_login_template==1 && $login_template!=''):?>
	var tpl = $( unescape('<?php echo $login_template?>') );
	$('#login').appendTo( $(tpl).find('#login-form-holder') );
	$('#backtoblog a').appendTo( $(tpl).find('#login-back-link') );
	$('#custom-logo').appendTo( $(tpl).find('#login-custom-logo') );
	$('body').empty().append(tpl);
<?php endif;?>
<?php if($bgcss!=''):?>
	$('<div id="login-wrapper"></div>').append( $('body').children() ).appendTo('body');
<?php endif;?>
});
</script>
<?php 	
		echo $use_login_template==1?$login_styles_scripts:'';
	}
	
	function admin_footer_text(){

	}
	
	function admin_head(){
		$vars = array('header_logo','header_logo_width','header_bar_height','developer_logo','developer_name','developer_url','hide_update_nag','hide_update_download','hide_contextual_help','hide_screen_options','hide_favorite_actions');
		foreach($vars as $var){
			$$var = get_option(sprintf("%s_%s",$this->id,$var));
		}
		//---footer
		$developer_url = $developer_url==''?'javascript:void(0);':$developer_url;
	
		$footer = '';
		if($developer_logo!=''){
			$footer.=sprintf("<a href=\"%s\"><img id=\"footer-developer-logo\" src=\"%s\" height=\"32\" align=\"MIDDLE\" /></a>",$developer_url,$developer_logo);
		}	
		if($developer_name!=''){
			$footer.=sprintf("<a id=\"footer-developer-name\" href=\"%s\">%s</a>",$developer_url,$developer_name);
		}
		
		$footer = str_replace("'","\'",$footer);			
?>
<style>
#header-logo {
	display:none !important;
}
#admin-custom-head-logo{
	float:left;
	margin:7px 0 0 15px;
	vertical-align:middle;
}
#wphead{
<?php if($header_bar_height!=''):?>
	height: <?php echo intval($header_bar_height) ?>px;
<?php endif;?>
}
#footer p#footer-left{
	vertical-align:middle;
	padding:10px 15px 10px 15px !important;
}
#footer-developer-logo{
	padding:0 10px 0 0;
}
#footer-developer-name{
	position:relative;
	top:5px;
}
<?php if('administrator'!=$this->get_user_role()):?>
#dashboard_right_now .versions p, #dashboard_right_now .versions #wp-version-message  { display: none; }
<?php endif; ?>
<?php if('administrator'!=$this->get_user_role() && $hide_update_nag==1):?>
.update-nag {display: none !important;}
<?php endif; ?>
<?php if('administrator'!=$this->get_user_role() && $hide_update_download==1):?>
#footer-upgrade {display:none !important;}
<?php endif; ?>
<?php if('administrator'!=$this->get_user_role() && $hide_contextual_help==1):?>
#contextual-help-link-wrap {display:none !important;}
<?php endif; ?>
<?php if('administrator'!=$this->get_user_role() && $hide_screen_options==1):?>
#screen-options-link-wrap {display:none !important;}
<?php endif; ?>
<?php if('administrator'!=$this->get_user_role() && $hide_favorite_actions==1):?>
#favorite-actions {display:none !important;}
<?php endif; ?>
</style>
<script>
jQuery(document).ready(function($){
<?php if(trim($header_logo)!=''):?>
	$('#wphead').prepend('<img id="admin-custom-head-logo" src="<?php echo $header_logo ?>" <?php echo $header_logo_width==''?'':sprintf("width=\"%s\"",intval($header_logo_width))?>/>');
<?php endif; ?>
	$('#footer-left').html('');
	$('#footer-left').append('<?php echo $footer?>');

	if( $('input[name="<?php echo $this->id?>_use_dashboard"]:checked').val()!=1 ){
		jQuery('.use_dashboard').hide();
	}
	
	if( $('input[name="<?php echo $this->id?>_editor_appeareance"]:checked').val()!=1 ){
		//jQuery('.theme-options').hide();
	}
	
	
});


function yesno_panel(o,sel){
	if(o.value==1){
		jQuery(sel).fadeIn();
	}else{
		jQuery(sel).fadeOut();
	}
}

</script>
<?php	
	}
	
	function init(){
		$options = $this->get_options(false);
		foreach($options as $tab){
			if(count($tab->options)>0){
				foreach($tab->options as $i => $o){
					$id = property_exists($o,'id') ? $o->id : $i ;
					$method = "_".$id;				
					if(!method_exists($this,$method))
						continue;
					$this->$method($tab,$i,$o);	
				}
			}
		}	
	}
	
	function plugins_loaded(){
		if(is_admin()):
			wp_register_style($this->id.'-toggle',WLB_URL.'css/toggle.css',array(),'1.0.0');
		
			require_once WLB_PATH.'includes/class.ALOptionsPanel.php';
			new ALOptionsPanel($this,__('White Label Branding','wlb'),__('White Label Branding','wlb'));
		endif;
		

	}
	
	function _use_dashboard($tab,$i,$o){
		$option_name = sprintf("%s_%s",$this->id,$o->id);
		if(intval(get_option($option_name))){
			add_action('wp_dashboard_setup', array(&$this,'use_dashboard'));
		}	
	}
	function use_dashboard(){
	//die(sprintf("%s_%s",$this->id,'panel_content'));
		if('editor'==$this->get_user_role()){
			$editor_title = get_option(sprintf("%s_%s",$this->id,'editor_panel_title'));
			if(trim($editor_title)!=''){
				wp_add_dashboard_widget('editor_custom_panel', $editor_title, array(&$this,'editor_panel_content') );
			}		
		}
		
		$public_title = get_option(sprintf("%s_%s",$this->id,'panel_title'));
		if(trim($public_title)!=''){
			wp_add_dashboard_widget('custom_panel', $public_title, array(&$this,'use_dashboard_content') );
		}
	}
	function use_dashboard_content(){
		echo stripslashes(get_option(sprintf("%s_%s",$this->id,'panel_content')));
	}
	function editor_panel_content(){
		echo stripslashes(get_option(sprintf("%s_%s",$this->id,'editor_panel_content')));
	}
	//wp_add_dashboard_widget('custom_help_widget', get_option('wlcms_o_welcome_title'), 'wlcms_custom_dashboard_help');
	function _remove_dash_right_now($tab,$i,$o){
		$option_name = sprintf("%s_%s",$this->id,$o->id);
		if(intval(get_option($option_name))){
			add_action('wp_dashboard_setup', array(&$this,'remove_dash_right_now'));
		}
	}
			
	function _remove_dash_panels($tab,$i,$o){
		$option_name = sprintf("%s_%s",$this->id,$o->id);
		if(intval(get_option($option_name))){
			add_action('wp_dashboard_setup', array(&$this,'remove_dash_panels'));
		}
	}
	
	function remove_dash_right_now(){
   		global $wp_meta_boxes;
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);	
	}
	
	function remove_dash_panels(){	
	   global $wp_meta_boxes;
	   unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
	   unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
	   unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
	   unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
	   unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
	   unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
	   unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_recent_drafts']);
	}
	
	function _header_logo($tab,$i,$o){
	
	}
	
	function header_logo(){
	
	}
	
	function get_options($for_admin=true){
		$t = array();
		
		$i= 0;
		
		$i++;
		$t[$i]->id 			= 'Branding'; 
		$t[$i]->label 		= __('Branding','wlb');//title on tab
		$t[$i]->right_label = __('Customize header and footer logo','wlb');
		$t[$i]->page_title	= __('Branding','wlb');//title on content
		$t[$i]->options = array(
			(object)array(
				'type'=>'subtitle',
				'label'=>'Header'	
			),
			(object)array(
				'id'	=> 'header_logo',
				'type'	=>'text',
				'label'	=> __('Header logo URL','wlb'),
				'description'=> __('URL to an image that will replace the header logo','wlb'),
				'el_properties' => array('size'=>'70'),
				'save_option'=>true,
				'load_option'=>true
			),
			
			(object)array(
				'id'	=> 'header_bar_height',
				'type'=>'text',
				'label'=> __('Header bar height','wlb'),
				'description'=> __('Specify a height in px if the header logo is taller than 32px, this will adjust the header height.','wlb'),
				'el_properties' => array('size'=>'5'),
				'save_option'=>true,
				'load_option'=>true
			),
//			(object)array(
//				'type'=>'hr'
//			),
			(object)array(
				'type'=>'subtitle',
				'label'=> __('Footer','wlb')	
			),
			(object)array(
				'id'	=> 'developer_logo',
				'type'=>'text',
				'label'=>__('Developer logo URL','wlb'),
				'description'=> __('URL to an image that will be displayed in the footer.','wlb'),
				'el_properties' => array('size'=>'70'),
				'save_option'=>true,
				'load_option'=>true
			),

			(object)array(
				'id'	=> 'developer_name',
				'type'	=> 'text',
				'label'	=> __('Developer name','wlb'),
				'description'=> __('Developer name, displayed on links to the developer website in the footer.','wlb'),
				'el_properties' => array('size'=>'70'),
				'save_option'=>true,
				'load_option'=>true
			),
			(object)array(
				'id'	=> 'developer_url',
				'type'=>'text',
				'label'=>__('Developer URL','wlb'),
				'description'=> __('URL to the developer website.','wlb'),
				'el_properties' => array('size'=>'70'),
				'save_option'=>true,
				'load_option'=>true
			)
		);	
		$t[$i]->options[]=(object)array('label'=>'','type'=>'submit','class'=>'button-primary', 'value'=> __('Save changes','wlb') );
		//------------------		
		
		$i++;
		$t[$i]->id 			= 'login'; 
		$t[$i]->label 		= __('Login screen customization','wlb');//title on tab
		$t[$i]->right_label = __('Customize login logo, background and html template','wlb');
		$t[$i]->page_title	= __('Branding','wlb');//title on content
		$t[$i]->options = array(
			(object)array(
				'type'=>'subtitle',
				'label'=>__('Default login screen customization','wlb')	
			),
			(object)array(
				'id'	=> 'login_logo_url',
				'type'=>'text',
				'label'=>__('Login Logo URL','wlb'),
				'description'=> __('URL to the login logo. The standard size logo is 300 px wide and 80 px tall, but you can use any size you want. ','wlb'),
				'el_properties' => array('size'=>'50'),
				'save_option'=>true,
				'load_option'=>true
			),
			(object)array(
				'id'	=> 'login_background',
				'type'=>'text',
				'label'=>__('Login Background URL','wlb'),
				'description'=> __('URL to an image you want to use as login background','wlb'),
				'el_properties' => array('size'=>'50'),
				'save_option'=>true,
				'load_option'=>true
			),
			(object)array(
				'id'	=> 'login_background_attachments',
				'type'	=> 'select',
				'label'	=> __('background-attachments','wlb'),
				'el_properties' => array(),
				'options'=>array('scroll'=>'scroll','fixed'=>'fixed','inherit'=>'inherit'),
				'value'=>'fixed',
				'save_option'=>true,
				'load_option'=>true
			),
			(object)array(
				'id'	=> 'login_background_color',
				'type'	=> 'select',
				'label'	=> __('background-color','wlb'),
				'el_properties' => array(),
				'options'=>array(''=>'color code','transparent'=>'transparent','inherit'=>'inherit'),
				'value'=>'transparent',
				'save_option'=>true,
				'load_option'=>true
			),
			(object)array(
				'id'	=> 'login_background_color_code',
				'type'	=> 'text',
				'label'	=> __('background-color code','wlb'),
				'el_properties' => array('size'=>20),
				'save_option'=>true,
				'load_option'=>true
			),
			(object)array(
				'id'	=> 'login_background_position',
				'type'	=> 'select',
				'label'	=> __('background-position','wlb'),
				'el_properties' => array(),
				'options'=>array(
					'left top' 		=> 'left top',
					'left center'	=> 'left center',
					'left bottom'	=> 'left bottom',
					'right top'		=> 'right top',
					'right center'	=> 'right center',
					'right bottom'=>'right bottom',
					'center top'=>'center top',
					'center center'=>'center center',
					'center bottom'=>'center bottom',
					''=>'xpos ypos',
					'inherit'=>'inherit'
				),
				'value'=>'center top',
				'save_option'=>true,
				'load_option'=>true
			),
			(object)array(
				'id'	=> 'login_background_x',
				'type'	=> 'text',
				'label'	=> __('background-x(xpos)','wlb'),
				'el_properties' => array('size'=>10),
				'save_option'=>true,
				'load_option'=>true
			),
			(object)array(
				'id'	=> 'login_background_y',
				'type'	=> 'text',
				'label'	=> __('background-y(ypos)','wlb'),
				'el_properties' => array('size'=>10),
				'save_option'=>true,
				'load_option'=>true
			),
			(object)array(
				'id'	=> 'login_background_repeat',
				'type'	=> 'select',
				'label'	=> __('background-repeat','wlb'),
				'el_properties' => array(),
				'options'=>array('repeat'=>'repeat','repeat-x'=>'repeat-x','repeat-y'=>'repeat-y','no-repeat'=>'no-repeat','inherit'=>'inherit'),
				'value'=>'repeat',
				'save_option'=>true,
				'load_option'=>true
			),
			(object)array(
				'type'=>'subtitle',
				'label'=>__('Alternative login template','wlb')	
			),
			(object)array(
				'id'		=> 'use_login_template',
				'label'		=> __('Use login template','wlb'),
				'type'		=> 'yesno',
				'description'=>  sprintf(__('Choose %s to activate the custom login html template and css','wlb'),__('Yes','wlb')),
				'el_properties'	=> array(),
				'save_option'=>true,
				'load_option'=>true
				),	
			(object)array(
				'id'	=> 'login_template',
				'type'=>'textarea',
				'label'=>__('Login HTML template','wlb'),
				'description'=> __('Optionally provide an html template to display instead of the default login html.  It is required that you include the following tags in the template: {loginform}: where you want the login form to occur, and {backlink}: where you want the back link to be displayed. {customlogo}:Shows the custom logo.','wlb'),
				'el_properties' => array('cols'=>'50','rows'=>'10'),
				'save_option'=>true,
				'load_option'=>true
			),
			(object)array(
				'id'	=> 'login_styles_scripts',
				'type'=>'textarea',
				'label'=>__('Login CSS template','wlb'),
				'description'=> __('This is an optional free space so you can write css or javascript to be output on the login header, either for your html template or to modify the styles of the default login screen.','wlb'),
				'el_properties' => array('cols'=>'50','rows'=>'10'),
				'save_option'=>true,
				'load_option'=>true
			)				
		);
		$t[$i]->options[]=(object)array('label'=>'','type'=>'submit','class'=>'button-primary', 'value'=> __('Save changes','wlb') );
		
		//------------------		
		$i++;
		$t[$i]->id 			= 'dashboard'; 
		$t[$i]->label 		= __('Dashboard','wlb');//title on tab
		$t[$i]->right_label	= __('Customize Dashboard Panels','wlb');//title on tab
		$t[$i]->page_title	= __('Dashboard','wlb');//title on content
		$t[$i]->options = array(
			(object)array(
				'type'=>'subtitle',
				'label'=>__('Add your own Custom Dashboard Panel','wlb')	
			),
			(object)array(
				'id'		=> 'use_dashboard',
				'label'		=> __('Custom Dashboard Panel','wlb'),
				'type'		=> 'yesno',
				'description'=> __('This will appear on the dashboard.','wlb'),
				'el_properties'	=> array('OnClick'=>'javascript:yesno_panel(this,\'.use_dashboard\');'),
				'save_option'=>true,
				'load_option'=>true
				),
			(object)array(
				'type'=>'subtitle',
				'label'=>__('Public panel','wlb')	
			),				
			(object)array(
				'id'	=> 'panel_title',
				'type'=>'text',
				'label'=>__('Title','wlb'),
				'el_properties' => array('size'=>'70'),
				'description'=> __('Add a title for your custom panel.','wlb'),
				'save_option'=>true,
				'load_option'=>true,
				'row_class'=>'use_dashboard'
			)	,
			(object)array(
				'id'	=> 'panel_content',
				'type'=>'textarea',
				'label'=>__('Panel content','wlb'),
				'el_properties' => array('rows'=>'15','cols'=>'50'),
				'description'=> __('This is shown to any logged user.','wlb'),
				'save_option'=>true,
				'load_option'=>true,
				'row_class'=>'use_dashboard'
			),
			(object)array(
				'type'=>'subtitle',
				'label'=>__('Private panel shown to editor','wlb')	
			),	
			(object)array(
				'id'	=> 'editor_panel_title',
				'type'=>'text',
				'label'=>__('Title','wlb'),
				'el_properties' => array('size'=>'70'),
				'description'=> __('Add a title for your custom panel.','wlb'),
				'save_option'=>true,
				'load_option'=>true,
				'row_class'=>'use_dashboard'
			)	,
			(object)array(
				'id'	=> 'editor_panel_content',
				'type'=>'textarea',
				'label'=>__('Panel content','wlb'),
				'el_properties' => array('rows'=>'15','cols'=>'50'),
				'description'=> __('This is shown to the editor. Add your own unique message to your client. We recommend that you add contact details or a link to how your client can get help. You can use HTML tags in this field.','wlb'),
				'save_option'=>true,
				'load_option'=>true,
				'row_class'=>'use_dashboard'
			),						
//			(object)array(
//				'type'=>'hr'
//			),			
			(object)array(
				'type'=>'subtitle',
				'label'=>__('Additional Dashboard Settings','wlb')	
			),	
			(object)array(
				'id'		=> 'remove_dash_right_now',
				'label'		=> __('Remove Right Now panel','wlb'),
				'type'		=> 'yesno',
				'description'=> __('Hide <strong>Right Now</strong> panel','wlb'),
				'el_properties'	=> array(),
				'save_option'=>true,
				'load_option'=>true
				)	,	
			(object)array(
				'id'		=> 'remove_dash_panels',
				'label'		=> __('Remove dashboard panels','wlb'),
				'type'		=> 'yesno',
				'description'=> __('Hide default dashboard panels','wlb'),
				'el_properties'	=> array(),
				'save_option'=>true,
				'load_option'=>true
				)						
		);		
		$t[$i]->options[]=(object)array('label'=>'','type'=>'submit','class'=>'button-primary', 'value'=> __('Save changes') );	
		
		if($for_admin){
			$menu = $this->menu;	
			$submenu = $this->submenu;
		}else{
			global $menu,$submenu;
		}
		
		$i++;
		$t[$i]->id 			= 'hide_menu'; 
		$t[$i]->label 		= __('Menus','wlb');//title on tab
		$t[$i]->right_label	= __('Customize Menus','wlb');//title on tab
		$t[$i]->page_title	= __('Menus','wlb');//title on content
		$t[$i]->options = array(
			(object)array(
				'type'=>'subtitle',
				'label'=>__('Main Menu Configuration','wlb')	
			),
			(object)array(
				'type'=>'description',
				'label'=>__('Changes made here will only effect people with the user role of <b>Editor</b> or lower. You are currently logged is as the admin, so you will not see any changes in the menus until you login as <b>Editor</b> or lower.','wlb')
			)			
		);
		
		if(is_array($menu)&&count($menu)>0){
			foreach($menu as $k => $m){
				$label = trim($m[0])==''?$m[2]:$m[0];			
				if(in_array($m[2],array('plugins.php','edit-comments.php'))){
					$label = substr($label,0,strpos($label,' '));
				}
				$id = 'm_'.strtolower(str_replace(' ','_',str_replace('.','_',$m[2])));
				$t[$i]->options[] = (object)array(
					'id'	=> $id,
					'type'=>'checkbox',
					'label'=> __('Hide','wlb').' '.$label,
					'option_value'=> $m[2],
					'el_properties' => array(),
					'save_option'=>true,
					'load_option'=>true
				);					
			}		
		}
		
		//Your profile
		$id = 'm_'.strtolower(str_replace(' ','_',str_replace('.','_','profile.php')));
		$t[$i]->options[] = (object)array(
			'id'	=> $id,
			'type'=>'checkbox',
			'label'=> __('Hide','wlb').' '.__('Your Profile','wlb'),
			'option_value'=> 'profile.php',
			'el_properties' => array(),
			'save_option'=>true,
			'load_option'=>true
		);				
		
	//	$t[$i]->options[]=(object)array('type'=>'hr');
		$t[$i]->options[] = (object)array(
				'type'=>'subtitle',
				'label'=>__('Appearance Menu Configuration','wlb')	
			);
			
			
		$t[$i]->options[] = (object)array(
				'type'=>'description',
				'label'=>__('The following change will display the Widgets or Menus option in Appearance to users with the role of <b>Editor</b>.','wlb')
			);
					
		$t[$i]->options[] =	(object)array(
				'id'		=> 'editor_appeareance',
				'label'		=> __('Appearance Menu Options','wlb'),
				'type'		=> 'yesno',
				'description'=> __('Show the appearance menu for the user level <strong>Editor</strong>.','wlb'),
				//'el_properties'	=> array('OnClick'=>'javascript:yesno_panel(this,\'.theme-options\');'),
				'el_properties'	=> array(),
				'save_option'=>true,
				'load_option'=>true
				);

		global $current_user;
		$user_roles = $current_user->roles;
	
		$t[$i]->options[]=(object)array('label'=>'','type'=>'submit','class'=>'button-primary', 'value'=> __('Save changes','wlb') );	
		//------------------------------------------------------------------------------------
		//Hide submenus
		$i++;
		$t[$i]->id 			= 'hide_submenu'; 
		$t[$i]->label 		= __('Sub menus','wlb');//title on tab
		$t[$i]->right_label	= __('Customize Sub menus','wlb');//title on tab
		$t[$i]->page_title	= __('Customize Sub menus','wlb');//title on content
		$t[$i]->options = array(
			(object)array(
				'type'=>'subtitle',
				'label'=>__('Sub Menu Configuration','wlb')	
			),
			(object)array(
				'type'=>'description',
				'label'=>__('Changes made here will only effect people with the user role of <b>Editor</b> or lower. You are currently logged is as the admin, so you will not see any changes in the sub menus until you login as <b>Editor</b> or lower.','wlb')
			)			
		);		
		if(is_array($submenu)&&count($submenu)>0){
			
			foreach($submenu as $key => $submenu_group){
				//---
				foreach($menu as $mm){
					if($mm[2]==$key){
						$t[$i]->options[]=(object)array(
							'type'=>'subtitle',
							'label'=>$mm[0]	
						);
					}
				}
				
				//---
				foreach($submenu_group as $k => $m){
					if(in_array($m[2],array('theme-editor.php')))
						continue;
					$label = trim($m[0])==''?$m[2]:$m[0];			
					$id = 'sm_'.strtolower(str_replace(' ','_',str_replace('.','_',$m[2])));
					$t[$i]->options[] = (object)array(
						'id'	=> $id,
						'type'=>'checkbox',
						'label'=> _('Hide').' '.$label,
						'option_value'=> $m[2],
						'el_properties' => array(),
						'save_option'=>true,
						'load_option'=>true,
						'row_class'=>'theme-options'
					);					
				}				
			}
			
	
		}	
	
		//------------------------------------------------------------------------------------
		$i++;
		$t[$i]->id 			= 'extra'; 
		$t[$i]->label 		= __('Additional settings','wlb');//title on tab
		$t[$i]->right_label	= __('&nbsp;','wlb');//title on tab
		$t[$i]->page_title	= __('Additional settings','wlb');//title on content
		$t[$i]->options = array(
			(object)array(
				'type'=>'subtitle',
				'label'=>__('Wordpress messages','wlb')	
			),
			(object)array(
				'type'=>'description',
				'label'=>__('Hide wordpress update messages.','wlb')
			)			
		);	
		$t[$i]->options[] =	(object)array(
				'id'		=> 'hide_update_nag',
				'label'		=> __('Hide update nag','wlb'),
				'type'		=> 'yesno',
				'description'=> __('Hide wordpress update message at the top.','wlb'),
				'el_properties'	=> array(),
				'save_option'=>true,
				'load_option'=>true
				);	
		$t[$i]->options[] =	(object)array(
				'id'		=> 'hide_update_download',
				'label'		=> __('Hide update download link','wlb'),
				'type'		=> 'yesno',
				'description'=> __('Hide wordpress download link message at the bottom.','wlb'),
				'el_properties'	=> array(),
				'save_option'=>true,
				'load_option'=>true
				);	
		$t[$i]->options[] =	(object)array(
				'id'		=> 'hide_contextual_help',
				'label'		=> __('Hide contextual help','wlb'),
				'type'		=> 'yesno',
				'description'=> __('Hide wordpress contextual help.','wlb'),
				'el_properties'	=> array(),
				'save_option'=>true,
				'load_option'=>true
				);	
		$t[$i]->options[] =	(object)array(
				'id'		=> 'hide_screen_options',
				'label'		=> __('Hide screen options','wlb'),
				'type'		=> 'yesno',
				'description'=> __('Hide screen options.','wlb'),
				'el_properties'	=> array(),
				'save_option'=>true,
				'load_option'=>true
				);	
		$t[$i]->options[] =	(object)array(
				'id'		=> 'hide_favorite_actions',
				'label'		=> __('Hide favorite actions','wlb'),
				'type'		=> 'yesno',
				'description'=> __('Hide wordpress favorite actions (Dropdown located on the top right corner of wp-admin).','wlb'),
				'el_properties'	=> array(),
				'save_option'=>true,
				'load_option'=>true
				);	

		$t[$i]->options[]=(object)array('label'=>'','type'=>'submit','class'=>'button-primary', 'value'=> __('Save changes','wlb') );	
		
		return $t;
	}
	
	function plugin_deactivate(){
		$role = get_role( 'editor' );
		$role->remove_cap( 'switch_themes' );
		$role->remove_cap( 'edit_theme_options' );   
	}	
}

$wlb_plugin = new plugin_white_label_branding_for_wordpress();

register_deactivation_hook( __FILE__, array(&$wlb_plugin,'plugin_deactivate') );

//-- Installation script:---------------------------------
function wlb_install(){
	$id = basename(dirname(__FILE__));
	$use_login_template_option_name = sprintf("%s_%s",$id,'use_login_template');
	$template_option_name = sprintf("%s_%s",$id,'login_template');
	$scripts_option_name = sprintf("%s_%s",$id,'login_styles_scripts');
	if('0'==get_option($use_login_template_option_name) && ''==trim(get_option($template_option_name))){
		
$default_template=<<<TEMPLATE
<div id="shadow-wrap"><div id="my-custom-login"><table align="center" width="100%">
<tr>
<td>{customlogo}{loginform}</td>
<tr>
<td colspan=2 align="center">{backlink}</td>
<tr>
</table></div>
TEMPLATE;
$default_scripts=<<<STYLES
<style>
#shadow-wrap {
background-image: url("/wp-content/plugins/white-label-branding/images/main_shadow.png");
background-position: 10px 100%;
background-repeat: no-repeat;
margin: 20px auto 40px;
padding: 0px 10px 6px;
width:652px;
}
#my-custom-login {
margin: 40px auto 16px;
width: 700px;
padding:20px;
 -moz-box-shadow: 0 4px 18px #C8C8C8;
    background: none repeat scroll 0 0 #ebebeb;
    border: 1px solid #E5E5E5;
    height: 550px;
    width: 600px;
-moz-border-radius-topleft: 15px;
-moz-border-radius-topright: 15px;
-moz-border-radius-bottomright: 3px;
-moz-border-radius-bottomleft: 3px;
border-top-left-radius: 15px;
border-top-right-radius: 15px;
border-bottom-right-radius: 3px;
border-bottom-left-radius: 3px;
text-align:center;
}
</style>
STYLES;
		update_option($template_option_name,$default_template);
		update_option($scripts_option_name,$default_scripts);	
	}
}
register_activation_hook(__FILE__, 'wlb_install');
//-------------------------------------------------------- 
?>