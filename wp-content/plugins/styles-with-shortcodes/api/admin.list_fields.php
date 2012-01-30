<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/
require '../../../../wp-load.php';
//sleep(1);
$post_id = intval($_REQUEST['ID']);

//-----------
if ( !current_user_can( 'edit_post', $post_id ) ){
	die('No access');
} 
//-------------

$fields = get_post_meta($post_id,'sc_fields',true);

$shortcode = get_post_meta($post_id,'sc_shortcode',true);

if(is_array($fields) && count($fields)>0){
//	$tmp = $tmp2 = array();
//	foreach($fields as $name => $field){
//		$tmp[]=sprintf("%s=\"{%s}\"",$name,$name);
//		$tmp2[]=sprintf("%s=\"{%s}\"",$name,$field->default);
//	}
//	$sample_template = "<div ".implode(" ",$tmp).">{content}</div>";
//	if($shortcode!=''){
//		$sample_shorcode = "[$shortcode ".implode(" ",$tmp)."]Your content[/$shortcode]";
//	}else{
//		$sample_shorcode = "";
//	}
	
?>
<div class="css-fields-cont">
<table class="widefat">
<thead>
	<tr>
		<th>&nbsp;</th>
		<th>Label</th>
		<th>Property name</th>
		<th>Default value</th>
		<th>Input type</th>
		<th>Shortcode template tag</th>
	</tr>
</thead>
<tbody>
<?php $i=0;foreach($fields as $name => $field): ?>
	<tr>
		<td>
			<span OnClick="javascript:edit_css_field('<?php echo $name?>')" class="css-field-edit">&nbsp;</span>
			<span OnClick="javascript:delete_css_field('<?php echo $name?>')" class="css-field-delete">&nbsp;</span>
			<?php if($i++>0): ?>
			<span OnClick="javascript:moveup_css_field('<?php echo $name?>')" class="css-field-moveup" title="Move up">&nbsp;</span>
			<?php endif; ?>
		</td>
		<td><?php echo $field->label?></td>
		<td><?php echo $field->name?></td>
		<td><?php echo $field->default?></td>
		<td><?php echo ($field->type=='data')?$field->type.'('.$field->field_number.' fields)':$field->type?></td>
		<td>{<?php echo $field->name?>}</td>
	</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php	
}else{
	echo "There are no configured fields.";
}
?>