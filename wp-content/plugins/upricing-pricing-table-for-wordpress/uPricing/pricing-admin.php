<?php
	//delete_option(UDS_PRICING_OPTION);
	$pricing_tables = maybe_unserialize(get_option(UDS_PRICING_OPTION, array()));
	//d($pricing_tables);
	if(isset($_GET['uds_pricing_delete_nonce']) && wp_verify_nonce($_GET['uds_pricing_delete_nonce'], 'uds-pricing-delete-nonce')) {
		unset($pricing_tables[$_GET['uds_pricing_delete']]);
		update_option(UDS_PRICING_OPTION, serialize($pricing_tables));
	}
	
?>
<div class="wrap">
	<h2>Pricing Tables</h2>
	<?php if(!empty($pricing_tables)): ?>
		<div class="create-pricing-table">
			<a href="<?php echo admin_url("admin.php?page=uds_pricing_structure") ?>">Create new Pricing Table</a>
		</div>
		<table class="uds-pricing-admin-table">
			<tr>
				<th>Name</th>
				<th class="shortcode">Shortcode</th>
				<th>Properties</th>
				<th>Products</th>
				<th>Edit Structure</th>
				<th>Edit Products</th>
				<th>Delete</th>
			</tr>
			<?php foreach($pricing_tables as $name => $pricing_table): ?>
				<tr>
					<td><?php echo $name ?></td>
					<td>[uds-pricing-table name="<?php echo $name ?>"]</td>
					<td><?php echo count($pricing_table['properties']); ?></td>
					<td><?php echo count($pricing_table['products']); ?></td>
					<td>
						<a href="<?php echo admin_url('admin.php?page=uds_pricing_structure&uds_pricing_edit='.urlencode($name)) ?>" class="pricing-edit-structure">Edit</a>
					</td>
					<td>
						<a href="<?php echo admin_url('admin.php?page=uds_pricing_products&uds_pricing_edit='.urlencode($name)) ?>" class="pricing-edit-products">Edit</a>
					</td>
					<td>
						<a href="<?php echo admin_url('admin.php?page=uds_pricing_admin&uds_pricing_delete='.urlencode($name)).'&uds_pricing_delete_nonce='.wp_create_nonce('uds-pricing-delete-nonce') ?>" class="pricing-delete">Delete</a>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
	<?php else: ?>
		<p class="updated uds-warn">There are no Pricing tables defined yet. Create your first one <a href="<?php echo admin_url('admin.php?page=uds_pricing_structure') ?>">here</a>.</p>
	<?php endif; ?>
</div>