<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class CSSOptions {
	var $capability = 'manage_sws';
	function CSSOptions(){
		add_action('admin_menu',array(&$this,'admin_menu'));
	}
	function admin_menu(){
		if(current_user_can('manage_options')||current_user_can($this->capability)){
			$plugin_page = add_submenu_page('edit.php?post_type=csshortcode', __('Options','sws'), __('Options','sws'), 0, 'shortcode-options', array(&$this, 'shortcode_options') );
			add_action( 'admin_head-'. $plugin_page, array(&$this,'options_head') );	
		}			
	}
	
	function shortcode_options(){
		$sys_msg='';
		if(isset($_POST['f_save'])){
			$options = array(
				'disable_autop' => isset($_POST['disable_autop'])?1:0,
				'skip_shortcode_priority'=>isset($_POST['skip_shortcode_priority'])?1:0
			);
			update_option('sws_options',$options);
			$sys_msg='<div id="message" class="updated below-h2">Options saved.</div>';
		}
		
		$options = get_option('sws_options');
		$options = empty($options)?array():$options;
		//----------		
?>
<div class="css-options-main wrap">
<h2>Styles with Shortcodes Options</h2>
<?php echo $sys_msg ?>
<form name="sform" method="post" action="">

<div id="css-options-cont">
	<div id="css-defaults" class="toggle-option">
		<h3 class="option-title">General Settings<span>General settings</span></h3>
		<div class="option-content">			
			<div class="description">Some Shortcodes may break when autop is active.  By checking this option the plugin will disable the WordPress autop filter.</div>	
			<div class="pt-option">
				<input type="checkbox" <?php echo isset($options['disable_autop'])&&$options['disable_autop']==1?'checked="checked"':''?> name="disable_autop" value="1">&nbsp;<?php _e('Disable autop','css')?>		
			</div>
			<div class="clear"></div>
		</div>
	</div>	
	
	<div id="css-defaults" class="toggle-option">
		<h3 class="option-title">Bundles<span>Restore bundles</span></h3>
		<div class="option-content">			
			<div class="description">Bundles can be added by plugin add-ons or SWS updates. Because you can customize Shortcodes to make it perfect for your theme, the plugin does not overwrite existing Shortcodes when activating them, if it was previously installed on the system; instead you need to manually choose a bundle and click on restore to return Shortcodes to their initial configuration.</div>	
			<div class="pt-option">
				<label for="restore-bundle">Restore bundle:</label>
				<?php echo $this->bundles_dropdown();?>		
			</div>
			<br />
			<div class="pt-option">
				<p><input type="button" id="btn_restore_bundle" class="button-secondary" value="Click to restore bundle" /></p>
				<p><div id="restore_status"></div></p>
			</div>
			<div class="clear"></div>
		</div>
	</div>	
</div>

<input type="submit" class="button-primary save-button" name="f_save" value="Save" />
</form>
</div>
<?php	
	}

	function options_head(){
?>
<script>
 jQuery(document).ready(function($){ 
 	$(".option-title").click(function(){$(this).toggleClass('open').next().toggle();});
	$("#btn_restore_bundle").click(function(){restore_bundle( $('#bundle_dropdown').val() );});
 });	
 
 function restore_bundle(bundle){
 	 jQuery(document).ready(function($){ 
	 	$('#restore_status').addClass('left-loading').html('');
		var _url = '<?php echo WPCSS_URL?>api/admin.restore_bundle.php';
		$.post(_url,{'bundle':bundle},function(data){
			if(data.R=='OK'){
				$('#restore_status').html('Operation completed');
			}else if(data.R=='ERR'){
				$('#restore_status').html(data.MSG);
			}else{
				$('#restore_status').html('Unknown error while processing. Please reload and try again.');
			}
			$('#restore_status').removeClass('left-loading');
		},'json');
	 });	 
 }
 </script>
<?php
	}
	
	function bundles_dropdown($id='bundle_dropdown',$name='bundle_dropdown',$extra='',$value=''){
		global $sws_plugin;
		$str = sprintf("<select id=\"%s\" name=\"%s\" %s>",$id,$name,$extra);
		if(is_array($sws_plugin->bundles)&&count($sws_plugin->bundles)>0){
			foreach($sws_plugin->bundles as $id => $b ){
				$str.=sprintf("<option %s value=\"%s\">%s</option>", ($b->id==$value?'selected':''),$b->id,$b->label);
			}
		}
		$str.="</select>";
		return $str;
	}
}

new CSSOptions();
?>