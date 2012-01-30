<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

class ALOptionsPanel {
	var $plugin;
	var $id;
	var $page_title;
	var $menu_text;

	function ALOptionsPanel($plugin,$page_title,$menu_text){
		$this->plugin = $plugin;
		$this->id = $plugin->id;
		$this->page_title = $page_title;
		$this->menu_text = $menu_text;
		
		add_action('admin_menu',array(&$this,'admin_menu'));
		add_action('init',array(&$this,'init'));
	}
	
	function init(){
		$this->handle_save();
	}
	
	function handle_save(){
		if(!isset($_POST[$this->id.'_options']))
			return;
		
		if(!current_user_can('manage_options'))
			return;
			
		$options = explode(',', stripslashes( $_POST[ 'page_options' ] ));

		if ( $options ) {
			foreach ( $options as $option ) {
				$option = trim($option);
				$value = null;
				if ( isset($_POST[$option]) )
					$value = $_POST[$option];
				if ( !is_array($value) ) $value = trim($value);
				$value = stripslashes_deep($value);
				update_option($option, $value);
			}
		}
		
		if(1==get_option($this->id.'_editor_appeareance')){
			$role = get_role( 'editor' );
			$role->add_cap( 'switch_themes' );
			$role->add_cap( 'edit_theme_options' );		
		}else{
			$role = get_role( 'editor' );
			$role->remove_cap( 'switch_themes' );
			$role->remove_cap( 'edit_theme_options' );
		}

		//------------------------------
		//return false;
		//other saving
		//------------------------------	
		$goback = add_query_arg( 'updated', 'true', wp_get_referer() );
		if(isset($_REQUEST['tabs_selected_tab'])&&$_REQUEST['tabs_selected_tab']!=''){
			$goback = add_query_arg( 'tabs_selected_tab', $_REQUEST['tabs_selected_tab'], $goback );
		}
		wp_redirect( $goback );
	}
	
	
	function admin_menu(){
		$handle = sprintf('%s-options',$this->id);
		$page_id = add_options_page( $this->page_title ,$this->menu_text,'manage_options',$handle,array(&$this,'body'));
		add_action( 'admin_head-'. $page_id, array(&$this,'head') );	
		
		wp_enqueue_style($this->id.'-toggle');
	
	}
	//admin_enqueue_scripts
	function head(){
		wp_print_scripts( 'jquery-ui-tabs' );
?>
  <script>
 jQuery(document).ready(function($){ 
 	$(".option-title").click(function(){
		$(this).toggleClass('open').next().slideToggle();
	});
 
 	$("#btn-open-all").click(function(){
		$('.option-title').addClass('open').next().slideDown();
	});
 });	
 </script>
<?php
	}
	
	function body(){
		global $menu,$submenu;
//		echo "<PRE>";
//		print_r($menu);
//		echo "</PRE>";
		
		$options = $this->plugin->get_options();
?>
<div class="wrap">
<?php screen_icon('options-general'); ?>
<h2><?Php echo $this->menu_text?></h2>
<?php echo isset($_REQUEST['updated'])?'<div class="updated">'.__('Options updated.','wlb').'</div>':'' ?>
<div id="sys_msg"></div>
<div id="wlb-options-cont">
<?php		
		if(count($options)>0){
			$save_fields = array();
			echo "<form method=\"post\" action=\"\">";
			echo '<input type="hidden" name="'.$this->id.'_options" value="1" />';
			echo '<input type="hidden" id="tabs_selected_tab" name="tabs_selected_tab" value="" />';
			wp_nonce_field($this->id);
			foreach($options as $tab){
				echo sprintf("<div id=\"%s\" class=\"toggle-option\">",$tab->id);
				echo sprintf("<h3 class=\"option-title\">%s<span>%s</span></h3>", $tab->label, @$tab->right_label );
				echo "<div class=\"option-content\"><table class=\"options-table\">";
				if(count($tab->options)>0){
					foreach($tab->options as $i => $o){
						$method = "_".$o->type;
						if(!method_exists($this,$method))
							continue;
								
						if(true===$o->load_option){
							$el_id = $this->get_el_id($tab,$i,$o);
							$o->value = isset($_POST[$el_id])?$_POST[$el_id]:get_option($el_id);
						}
												
						if($o->type=='subtitle'){
							echo sprintf("<tr class=\"tabs-%s\"><th colspan=3 style=\"text-align:left\"><h3 class=\"option-panel-subtitle\">%s</h3></th></tr>",$o->type,$o->label);
						}else if($o->type=='description'){
							echo sprintf("<tr class=\"tabs-%s\"><th class=\"sub-description\" colspan=3 style=\"text-align:left\"><p class=\"sub-description\">%s</p></th></tr>",$o->type,$o->label);
						}else if($o->type=='hr'){
							echo sprintf("<tr class=\"tabs-%s tr-hr\"><th colspan=3 style=\"text-align:left\"><hr class=\"hr\"/></th></tr>",$o->type);
						}else if($o->type=='checkbox'){
							echo sprintf("<tr class=\"tabs-%s %s\"><th align=\"right\">%s&nbsp;</th>",$o->type,$o->row_class,$this->$method($tab,$i,$o,$save_fields));
							echo sprintf("<td colspan=2>%s</td></tr>",$o->label);				
						}else if($o->type=='submit'){
							echo sprintf("<tr class=\"tabs-%s %s\"><th align=\"left\">%s</th>",$o->type,$o->row_class,$o->label);
							echo sprintf("<td colspan=\"2\" align=\"right\" class=\"save-button\">%s&nbsp;</td></tr>",$this->$method($tab,$i,$o,$save_fields));	
						}else{
							echo sprintf("<tr class=\"tabs-%s %s\"><th align=\"left\">%s</th>",$o->type,$o->row_class,$o->label);
							echo sprintf("<td>%s</td><td class=\"desc-cell\">%s</td></tr>",$this->$method($tab,$i,$o,$save_fields),(trim($o->description)==''?'&nbsp;':"<div class=\"description\">".$o->description."</div>"));						
						}

						//------------

					}
				}
				echo "</table></div>";
				echo "</div>";
			}
			
			echo "<div class=\"bottom-controls\">";
			echo "<input id=\"btn-open-all\" class=\"button-secondary\" type=\"button\" value=\"".__('Open all','wlb')."\" />";
			echo $this->_submit(null,null,(object)array('class'=>'button-primary','value'=>__('Save all','wlb')));
			echo "</div>";
			
			echo '<input type="hidden" name="action" value="update" />';
			echo sprintf('<input type="hidden" name="page_options" value="%s" />',implode(",",$save_fields));
			echo "</form>";
		}
?>
</div>
</div>
<?php	
	}
	
	function get_option($name){
		$option_name = sprintf("%s_$name",THEME_ID);
		return get_option($option_name);
	}
	
	function get_el_id($tab,$i,$o){
		return sprintf("%s_%s",$this->id,$o->id);
	}
	
	function get_el_properties($tab,$i,$o){
		$elp = array();
		if(count($o->el_properties)>0){
			foreach($o->el_properties as $prop => $val){
				$elp[] = sprintf("%s=\"%s\"",$prop,$val);
			}
		}
		return implode(' ',$elp);
	}
	
	function _subtitle(){
	
	}
	
	function _description(){
	
	}
	
	function _hr(){
	
	}
	
	function _textarea($tab,$i,$o,&$save_fields){
		$id = $this->get_el_id($tab,$i,$o);
		$properties = $this->get_el_properties($tab,$i,$o);
		
		if(true===$o->save_option){
			$save_fields[]=$id;	
		}
		
		return sprintf("<textarea id=\"%s\" name=\"%s\" %s>%s</textarea>",$id,$id,$properties,$o->value);
	}
	
	function _label($tab,$i,$o,&$save_fields){
		return sprintf('<label>%s</label>',$o->value );
	}
	
	function _text($tab,$i,$o,&$save_fields){
		$id = $this->get_el_id($tab,$i,$o);
		$str = sprintf('<input type="text" id="%s" name="%s" value="%s" %s />',$id,$id,$o->value, $this->get_el_properties($tab, $i, $o) );
//		if(trim($o->description)!=''){
//			$str.="<br />";
//			$str.="<small class=\"description\">".$o->description."</small>";		
//		}
		
		if(true===$o->save_option){
			$save_fields[]=$id;	
		}
		
		return $str;
	}
	
	function _checkbox($tab,$i,$o,&$save_fields){
		$id = $this->get_el_id($tab,$i,$o);
		$checked = $o->value==$o->option_value?'checked':'';
		$str = sprintf('<input type="checkbox" id="%s" name="%s" value="%s" %s %s />',$id,$id,$o->option_value, $this->get_el_properties($tab, $i, $o) , $checked);
				
		if(true===$o->save_option){
			$save_fields[]=$id;	
		}
		
		return $str;
	}
	
	function _select($tab,$i,$o,&$save_fields){
		$id = $this->get_el_id($tab,$i,$o);
		$str = sprintf('<select id="%s" name="%s"  %s />',$id,$id, $this->get_el_properties($tab, $i, $o) );
		if(!empty($o->options)){
			foreach($o->options as $value => $label){
				$selected = $o->value==$value?'selected':'';
				$str.=sprintf("<option %s value=\"%s\">%s</option>", $selected, $value, $label);
			}
		}
		$str.="</select>";
		
//		if(trim($o->description)!=''){
//			$str.="<br />";
//			$str.="<small class=\"description\">".$o->description."</small>";			
//		}
		
		if(true===$o->save_option){
			$save_fields[]=$id;	
		}
		
		return $str;
	}
	
	function _yesno($tab,$i,$o,&$save_fields){
		$o->options = array(
			'1'=>__('Yes','wlb'),
			'0'=>__('No','wlb')
		);
		return $this->_radio($tab,$i,$o,$save_fields);
	}
	
	function _radio($tab,$i,$o,&$save_fields){
		$str = '';
		if(!empty($o->options)){
			$k=0;
			foreach($o->options as $value => $label){
				$id = $this->get_el_id($tab,$i,$o).'_'.($k++);
				$name = $this->get_el_id($tab,$i,$o);
				$selected = $o->value==$value?'checked':'';
				$str.=sprintf("<input %s id=\"%s\" name=\"%s\" type=\"radio\" %s value=\"%s\" />&nbsp;<label>%s</label>&nbsp;&nbsp;", $this->get_el_properties($tab, $i, $o),$id, $name, $selected, $value, $label);
			}
//			if(trim($o->description)!=''){
//				$str.="<br />";
//				$str.="<small class=\"description\">".$o->description."</small>";			
//			}
			if(true===$o->save_option){
				$save_fields[]=$name;	
			}
		}
		return $str;
	}

	function _submit($tab,$i,$o){
		return sprintf("<input class=\"%s\" type=\"submit\" name=\"theme_options_submit\" value=\"%s\" />",$o->class, $o->value);
	}
	
	function _button($tab,$i,$o){
		$id = $this->get_el_id($tab,$i,$o);
		return sprintf("<input class=\"%s\" type=\"button\" id=\"%s\" name=\"%s\" value=\"%s\" %s />",$o->class, $id, $id, $o->value, $this->get_el_properties($tab,$i,$o) );
	}	
}
?>