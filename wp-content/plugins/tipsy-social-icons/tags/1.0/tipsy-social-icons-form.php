<p class="description">
	<?php _e('Specify the full URL to your profile page. If not specified, the icon will not display.', 'tipsy-social-icons'); ?>
</p>
<fieldset class="tipsy-social-icons">
	<legend>
		<?php _e('Social Icons', 'tipsy-social-icons'); ?>
	</legend><?php
	foreach($instance as $key => $val) { 
		if($key != 'use_large_icons' && $key != 'use_fade_effect') { ?>
		<div>
			<img src="<?php echo  WP_PLUGIN_URL . '/tipsy-social-icons/images/16/' . $key . '_16.png';  ?>" alt="<?php echo $key; ?>" title="<?php echo ucfirst($key); ?>" />
			<input type="text" value="<?php echo esc_attr($val) ?>" name="<?php echo $this->get_field_name($key); ?>" id="<?php echo $this->get_field_id($key); ?>" />
		</div><?php 
		} // end if
	} // end foreach ?>
</fieldset>
<fieldset class="tipsy-social-icons">
	<legend>
		<?php _e('Display Options', 'tipsy-social-icons'); ?>
	</legend>
	<div>
		<input type="checkbox" id="<?php echo $this->get_field_id('use_large_icons'); ?>" name="<?php echo $this->get_field_name('use_large_icons'); ?>" <?php if($instance['use_large_icons'] == 'on') { echo 'checked="checked"'; } ?> />
		<label for="clear_cache">
			<?php _e('Display large icons?', 'tipsy-social-icons'); ?>
		</label>
	</div>
	<div>
		<input type="checkbox" id="<?php echo $this->get_field_id('use_fade_effect'); ?>" name="<?php echo $this->get_field_name('use_fade_effect'); ?>" <?php if($instance['use_fade_effect'] == 'on') { echo 'checked="checked"'; } ?> />
		<label for="clear_cache">
			<?php _e('Apply hover fade effect?', 'tipsy-social-icons'); ?>
		</label>
	</div>
</fieldset>