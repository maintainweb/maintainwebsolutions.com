<?php
global $uds_errors; 

if(!empty($_GET['uds_pricing_edit'])) {
	$pricing_table_name = $_GET['uds_pricing_edit'];
} else {
	$pricing_table_name = '';
}

$pricing_table_name = !empty($_POST['uds-pricing-table-name']) ? $_POST['uds-pricing-table-name'] : $pricing_table_name;

$editing = true;
if(empty($pricing_table_name)) {
	$editing = false;
}

$pricing_tables = maybe_unserialize(get_option(UDS_PRICING_OPTION, array()));

$pricing_table = $pricing_tables[$pricing_table_name];

$edit = "";
if(!empty($pricing_table_name)) {
	$edit = "&uds_pricing_edit=$pricing_table_name";
}

?>
<div class="wrap">
	<?php if(!$editing): ?>
		<h2>Create new Pricing Table structure</h2>
	<?php else: ?>
		<h2>Edit Pricing Table Structure</h2>
	<?php endif; ?>
	<?php if(!empty($pricing_tables)): ?>
		<div class="uds-pricing-edit">
			<label for="">Edit</label>
			<select class="uds-load-pricing-table">
				<?php foreach($pricing_tables as $name => $table): ?>
					<option <?php echo $pricing_table_name == $name ? 'selected="selected"' : '' ?>><?php echo $name ?></option>
				<?php endforeach; ?>
			</select>
			<input type="submit" name="" value="Load" class="submit button-primary uds-change-table" />
		</div>
	<?php endif; ?>
	<?php if(!empty($uds_errors)): ?>
		<div class="updated uds-warn">
			<ul>
				<?php foreach($uds_errors as $error): ?>
					<li><?php echo $error->get_error_message() ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>
	<div id="uds-pricing-structure" class="uds-pricing">
		<form action="<?php echo admin_url("admin.php?page=uds_pricing_structure$edit") ?>" method="post">
			<input type="hidden" name="uds_pricing_nonce" value="<?php echo wp_create_nonce('uds-pricing-nonce') ?>" />
			<input type="hidden" name="uds_pricing_name_original" value="<?php echo $pricing_table_name ?>" />
			<h3>General Options</h3>
			<?php if($editing): ?>
				<a href="<?php echo admin_url("admin.php?page=uds_pricing_products$edit") ?>" class="backlink">Add/Edit Products</a>
			<?php endif; ?>
			<div id="uds-pricing-options">
				<?php uds_pricing_render_general_options($pricing_table) ?>
			</div>
			<h3>Properties</h3>
			<div id="uds-pricing-properties">
				<table>
					<tr>
						<th class="label">Label</th>
						<th class="type">Type</th>
						<th colspan="3" class="actions">Actions</th>
					</tr>
					<?php if(!empty($pricing_table['properties'])): ?>
						<?php foreach($pricing_table['properties'] as $name => $type): ?>
						<tr>
							<td>
								<input type="text" name="labels[]" value="<?php echo $name ?>" />
							</td>
							<td>
								<select name="types[]">
									<option value="text" <?php if($type == 'text') echo "selected='selected'"?>>Text</option>
									<option value="checkbox" <?php if($type == 'checkbox') echo "selected='selected'"?>>Checkbox</option>
								</select>
							</td>
							<td>
								<div class="move">Move</div>
							</td>
							<td>
								<div class="delete">Delete</div>
							</td>
							<td>
							</td>
						</tr>
						<?php endforeach; ?>
					<?php endif; ?>
					<tr>
						<td>
							<input type="text" name="labels[]" value="" />
						</td>
						<td>
							<select name="types[]">
								<option value="text">Text</option>
								<option value="checkbox">Checkbox</option>
							</select>
						</td>
						<td>
							<div class="move">Move</div>
						</td>
						<td>
							<div class="delete">Delete</div>
						</td>
						<td>
							<div class="add">Add</div>
						</td>
					</tr>
				</table>
				<input type="submit" name="" class="submit button-primary" value="Update" />
				<div class="clear"></div>
			</div>
		</form>
	</div>
</div>