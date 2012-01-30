<ul class="tipsy-social-icons"><?php 
	$icon_size = $use_large_icons == 'on' ? '32' : '16';
	foreach($instance as $key => $val) { 
		if($key != 'use_large_icons' && $key != 'use_fade_effect') { 
			if($instance[$key] != '') { ?>
				<li>
					<a href="<?php echo $key == 'email' ? 'mailto:' . $val : $val; ?>" class="<?php echo $use_fade_effect == 'on' ? 'fade' : ''; ?>" target="_blank">
						<img src="<?php echo  WP_PLUGIN_URL . '/tipsy-social-icons/images/' . $icon_size . '/' . $key . '_' . $icon_size . '.png'; ?>" alt="<?php echo ucfirst($key); ?>" class="tipsy-social-icon" />
					</a>
				</li><?php
			} // end if
		} // end if
	} // end foreach 
?></ul>