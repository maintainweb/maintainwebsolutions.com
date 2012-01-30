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
require_once('../../../wp-load.php');
$content = ob_get_contents();
ob_end_clean();

function send_error_die($msg){
	die(json_encode(array('R'=>'ERR','MSG'=>$msg)));
}

if(!current_user_can('manage_options')){
	send_error_die('No access');
}

$_REQUEST['bundle'] = 'css-tooltip';
if(!isset($_REQUEST['bundle'])){
	send_error_die('Missing parameter');
}

$bundle = addslashes(stripslashes($_REQUEST['bundle']));
$author = addslashes(stripslashes($_REQUEST['author']));

class BundleImportExport {
	var $bundle='starter';
	var $author='http://plugins.righthere.com/';
	function BundleImportExport($bundle,$author){
		if(''!=trim($bundle)){
			$this->bundle = $bundle;
		}
		if(''!=trim($author)){
			$this->author = $author;
		}
	}
		
	function dump_bundle(&$error,$serialized=true){
		global $wpdb;
		
		$ids = $wpdb->get_col("SELECT DISTINCT(P.ID) FROM `{$wpdb->posts}` P INNER JOIN `{$wpdb->postmeta}` M ON (M.post_id=P.ID AND M.meta_key='sc_bundle') WHERE P.post_type=\"csshortcode\" AND P.post_status=\"publish\" AND M.meta_value=\"{$this->bundle}\" GROUP BY P.ID",0);
		if(empty($ids)){
			$error = sprintf("No published shortcodes in bundle %s",$this->bundle);
			return false;
		}
		$bundle = array();
		foreach($ids as $post_id){
			$tmp = $this->get_shortcode_from_post_id($post_id, $error, false);
			if(false===$tmp){
				return false;
			}
			
			$bundle[]=$tmp;
		}
		return $serialized?base64_encode(serialize($bundle)):$bundle;
	}
	
	function get_bundle_file(&$error,$path){
		
		$bundle = (object)array(
			'name'		=>$this->bundle,
			'author'	=>$this->author,
			'shortcodes'=>$this->dump_bundle($error, false)
		);
		
//		if(!is_writable($path)){
//			$error = sprintf('Bundle file is not writable.  Check permissions on path %s',$path);
//			return false;
//		}
		
		if(false===$bundle->shortcodes)
			return false;
		if($fp=fopen($path,'w+')){
			$content = "<?php\r\n";
			$content.= "\$bundle = <<<EOT\r\n";
			$content.= base64_encode(serialize($bundle))."\r\n";
			$content.= "EOT;\r\n";
			$content.= "?>";
			
			fwrite($fp,$content);
			fclose($fp);
			return true;
		}else{
			$error = sprintf('Error opening bundle file: %s',$path);
			return false;
		}
	}
	
	function get_shortcode_from_post_id($post_id,&$error,$serialized=true){
		global $wpdb;
		
		$sco = (object)array();
		$sco->post_title = $wpdb->get_var("SELECT post_title FROM `{$wpdb->posts}` WHERE ID=$post_id AND post_type=\"csshortcode\" AND post_status=\"publish\"",0,0);
		foreach(array('sc_shortcode','sc_shortcodes','sc_template','sc_css','sc_js','sc_fields','sc_bundle','sc_scripts','sc_styles','sc_info') as $meta_name){
			$sco->$meta_name = get_post_meta($post_id,$meta_name,true);
		}
		
		$sco->sc_bundle = $this->bundle;
		$sco->sc_fields = is_array($sco->sc_fields)?$sco->sc_fields:array();

		$groups = get_the_terms($post_id, 'csscategory');
		$tmp = array();
		if(is_array($groups)&&count($groups)>0){
			foreach($groups as $group){
				$tmp[]= (object)array(
					'name'=>$group->name,
					'slug'=>$group->slug,
					'description'=>$group->description
				); 
			}
		}
		$sco->sc_terms = $tmp;
		
		if(!$this->check_shortcode_obj($sco, $error)){
			return false;
		}
		
		return $serialized?base64_encode(serialize($sco)):$sco;
	}
	
	function string_to_obj($str){
		return unserialize(base64_decode($str));
	}
	
	function check_shortcode_obj($o,&$error){
		$error = '';
		foreach(array('post_title','sc_shortcode','sc_template','sc_css','sc_js','sc_fields') as $field){
			if(!isset($o->$field)){
				$error = sprintf( __("Field not set (%s)",'wpcss') ,$field);
				return false;
			}
		}
		
		if(!is_array($o->sc_fields)){
			$error = __("Shortcode fields property is not an array.","wpcss");
			return false;
		}
		
		foreach(array('post_title','sc_shortcode','sc_template') as $field){
			if(trim($o->$field)==''){
				$error = sprintf( __("Field is empty (%s)",'wpcss') ,$field);
				return false;	
			}
		}
		return true;
	}
}

$e = new BundleImportExport($bundle,$author);
if(false===$e->get_bundle_file($error, ABSPATH.'wp-content/plugins/'.basename(dirname(__FILE__)).'/includes/bundle.php')){
	send_error_die("ERR:$error");
}

echo die(json_encode(array('R'=>'OK')));
?>