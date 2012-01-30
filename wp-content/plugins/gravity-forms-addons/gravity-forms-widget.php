<?php

if(!class_exists('GravityForms_Widget')) {
class GravityForms_Widget extends WP_Widget {

	function GravityForms_Widget() {
		$widget_ops = array( 'classname' => 'gravityformswidget', 'description' => 'Gravity Forms Addons Widget' );
		$control_ops = array( 'width' => 200, 'height' => 250, 'id_base' => 'gravityformswidget' );
		$this->WP_Widget( 'gravityformswidget', 'Gravity Forms', $widget_ops, $control_ops );
	}

	function widget( $args, $instance ) {
		extract( $args );
		echo $before_widget;
		$title = apply_filters('widget_title', $instance['title'] );
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		$form = RGForms::get_form($instance['form'], $instance['showtitle'], $instance['showdesc']);

		// Fix the tab indices
		preg_match_all("/tabindex='([0-9]+)'/",$form,$matches,PREG_SET_ORDER);
		$diff = $instance['tabindex'] - $matches[0][1];
		foreach ($matches as $match) {
			$newtabindex = str_replace($match[1],$match[1]+$diff,$match[0]);
			$form = str_replace($match[0], $newtabindex, $form);
		}
		
		// Output the form
		echo $form;
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		foreach ( array('title', 'form', 'showtitle', 'showdesc','tabindex') as $val ) {
			$instance[$val] = strip_tags( $new_instance[$val] );
		}
		return $instance;
	}

	function form( $instance ) {
		$defaults = array( 
			'title' 		=> 'Gravity Forms',
			'form'			=> 1,
			'tabindex'		=> 6,
		);
		$instance = wp_parse_args( (array) $instance, $defaults ); 
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e("Title"); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:90%;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'form' ); ?>"><?php _e("Form"); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'form' ); ?>" name="<?php echo $this->get_field_name( 'form' ); ?>" style="width:90%;">
				<?php 
					global $wpdb;
					$forms = $wpdb->get_results("SELECT id, title FROM ".$wpdb->prefix."rg_form WHERE is_active = 1");
					foreach ($forms as $form) {
						$sel = '';
						if ($form->id == $instance['form'])
							$sel = ' selected="selected"';
						echo '<option value="'.$form->id.'" '.$sel.'>'.$form->title.'</option>';
					}
				?>
			</select>
		</p>
		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'showtitle' ); ?>" value="1"  id="<?php echo $this->get_field_id( 'showtitle' ); ?>" <?php checked($instance['showtitle']); ?>/> <label for="<?php echo $this->get_field_id( 'showtitle' ); ?>"><?php _e("Show the title"); ?></label><br/>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'showdesc' ); ?>" value="1" id="<?php echo $this->get_field_id( 'showdesc' ); ?>" <?php checked($instance['showdesc']); ?>/> <label for="<?php echo $this->get_field_id( 'showdesc' ); ?>"><?php _e("Show the description"); ?></label><br/>
		</p>
			<label for="<?php echo $this->get_field_id( 'tabindex' ); ?>"><?php _e("Tab Index Start"); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'tabindex' ); ?>" name="<?php echo $this->get_field_name( 'tabindex' ); ?>" value="<?php echo $instance['tabindex']; ?>" style="width:15%;" /><br/>
			<small><?php _e('Because you probably have comment forms on single pages, you\'ll need to increase the tabindex for this form, otherwise these tabindices will collide.') ?></small>
		</p>
	<?php 
	}
}

if(!function_exists('gravityformswidget_widget_func')) {
	function gravityformswidget_widget_func() {
		register_widget( 'GravityForms_Widget' );
	}
}

add_action( 'widgets_init', 'gravityformswidget_widget_func' );
}
?>