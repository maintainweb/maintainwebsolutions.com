<?php

/*
Plugin Name: Pinboard.in for Wordpress
Version: 2.0.2
Plugin URI: http://pinboard.in/plugins/wordpress
Description: Displays your recently listened links. Based on <a href="http://rick.jinlabs.com/code/delicious">Delicious for Wordpress</a> by <a href="http://cavemonkey50.com/">Ricardo Gonz&aacute;lez</a>. 
Author: Maciej Ceglowski
Author URI: http://pinboard.in/
*/

/*  Copyright 2007  Ricardo Gonzalez Castro (rick[in]jinlabs.com)
    Copyright 2010  Maciej Ceglowski (maciej@ceglowski.com)
    
    This program is free software; you can redistribute it and/or modify
    it under version 2 of the GNU General Public License as published by
    the Free Software Foundation.
*/


define('MAGPIE_CACHE_AGE', 120);
define('MAGPIE_CACHE_ON', 1); //2.7 Cache Bug
define('MAGPIE_INPUT_ENCODING', 'UTF-8');
define('MAGPIE_OUTPUT_ENCODING', 'UTF-8');


$pinboard_options['widget_fields']['title'] = array('label'=>'Title:', 'type'=>'text', 'default'=>'');
$pinboard_options['widget_fields']['username'] = array('label'=>'Username:', 'type'=>'text', 'default'=>'');
$pinboard_options['widget_fields']['num'] = array('label'=>'Number of links:', 'type'=>'text', 'default'=>'');
$pinboard_options['widget_fields']['update'] = array('label'=>'Show timestamps:', 'type'=>'checkbox', 'default'=>false);
$pinboard_options['widget_fields']['tags'] = array('label'=>'Show tags:', 'type'=>'checkbox', 'default'=>false);
$pinboard_options['widget_fields']['filtertag'] = array('label'=>'Filter Tag(s) [cats+dogs+birds]: ', 'type'=>'text', 'default'=>'');
$pinboard_options['widget_fields']['displaydesc'] = array('label'=>'Show descriptions:', 'type'=>'checkbox', 'default'=>false);
$pinboard_options['widget_fields']['nodisplaytag'] = array('label'=>'No display tag(s) [cats+dogs+birds]:', 'type'=>'text', 'default'=>'');
$pinboard_options['widget_fields']['globaltag'] = array('label'=>'Global tags:', 'type'=>'checkbox', 'default'=>false);
//$pinboard_options['widget_fields']['encode_utf8'] = array('label'=>'UTF8 Encode:', 'type'=>'checkbox', 'default'=>false);

$pinboard_options['prefix'] = 'pinboard';
$pinboard_options['rss_url'] = 'http://feeds.pinboard.in/rss/';
$pinboard_options['tag_url'] = 'http://pinboard.in/t:';

// Display pinboard recently bookmarked links.

function pinboard_bookmarks($username = '', $num = 5, $list = true, $update = true, $tags = false, $filtertag = '', $displaydesc = false, $nodisplaytag = '', $globaltag = false, $encode_utf8 = false ) {
	
	global $pinboard_options;
	include_once(ABSPATH . WPINC . '/rss.php');
	
	$rss = $pinboard_options['rss_url']."u:".$username;
	
	if($filtertag != '') { $rss .= '/t:'.$filtertag; }

	$bookmarks = fetch_rss($rss);

	if ($list) echo '<ul class="pinboard">';
	
	if ($username == '') {
		if ($list) echo '<li>';
		echo 'Username not configured';
		if ($list) echo '</li>';
	} else {
		if ( empty($bookmarks->items) ) {
			if ($list) echo '<li>';
			echo 'No bookmarks avaliable.';
			if ($list) echo '</li>';
		} else {
			foreach ( $bookmarks->items as $bookmark ) {
		     //print_r($bookmark);
				$msg = $bookmark['title'];
				if($encode_utf8) utf8_encode($msg);					
				$link = $bookmark['link'];
				$desc = $bookmark['description'];
			
				if ($list) echo '<li class="pinboard-item">'; elseif ($num != 1) echo '<p class="pinboard">';
        		echo '<a href="'.$link.'" class="pinboard-link">'.$msg.'</a>'; // Puts a link to the... link.

        if($update) {				
          $time = strtotime($bookmark['dc']['date']);
          
          if ( ( abs( time() - $time) ) < 86400 )
            $h_time = sprintf( __('%s ago'), human_time_diff( $time ) );
          else
            $h_time = date(__('Y/m/d'), $time);

          echo sprintf( '%s',' <span class="pinboard-timestamp"><abbr title="' . date(__('Y/m/d H:i:s'), $time) . '">' . $h_time . '</abbr></span>' );
         }      
				
				if ($displaydesc && $desc != '') {
        			echo '<br />';
        			echo '<span class="pinboard-desc">'.$desc.'</span>';
				}
				
				if ($tags) {
					echo '<br />';
					echo '<div class="pinboard-tags">';
					$tagged = explode(' ', $bookmark['dc']['subject']);
					$ndtags = explode('+', $nodisplaytag);
					if ($globaltag) { $prfx = 't:'; } else { $prfx = "u:".$username ."/t:"; }
					foreach ($tagged as $tag) {
					  if (!in_array($tag,$ndtags)) 
					  {
       			            echo '<a href="http://pinboard.in/'.$prfx . $tag.'" class="pinboard-link-tag">'.$tag.'</a> '; // Puts a link to the tag.              
                        }
					}
					echo '</div>';
				}
					
				if ($list) echo '</li>'; elseif ($num != 1) echo '</p>';
			
				$i++;
				if ( $i >= $num ) break;
			}
		}	
  }
	if ($list) echo '</ul>';  
}
	
	
// pinboard widget stuff
function widget_pinboard_init() {
	
	if ( !function_exists('register_sidebar_widget') )
		return;
	
	$check_options = get_option('widget_pinboard');
  if ($check_options['number']=='') {
    $check_options['number'] = 1;
    update_option('widget_pinboard', $check_options);
  }
  	
	function widget_pinboard($args, $number = 1) {
		
		global $pinboard_options;
		
		// $args is an array of strings that help widgets to conform to
		// the active theme: before_widget, before_title, after_widget,
		// and after_title are the array keys. Default tags: li and h2.
		extract($args);

		// Each widget can store its own options. We keep strings here.
		include_once(ABSPATH . WPINC . '/rss.php');
		$options = get_option('widget_pinboard');
		
		// fill options with default values if value is not set
		$item = $options[$number];
		foreach($pinboard_options['widget_fields'] as $key => $field) {
			if (! isset($item[$key])) {
				$item[$key] = $field['default'];
			}
		}
		$bookmarks = fetch_rss($pinboard_options['rss_url'] . $username);

		// These lines generate our output.
		echo $before_widget . $before_title . '<a href="http://pinboard.in/u:'.$item['username'] . '" class="pinboard_title_link">'. $item['title'] . '</a>' . $after_title;
		pinboard_bookmarks($item['username'], $item['num'], true, $item['update'], $item['tags'], $item['filtertag'], $item['displaydesc'], $item['nodisplaytag'], $item['globaltag'], $item['encode_utf8']);
		echo $after_widget;
	}



	// This is the function that outputs the form.
	function widget_pinboard_control($number) {
		
		global $pinboard_options;
		
		// Get our options and see if we're handling a form submission.
		$options = get_option('widget_pinboard');


		if ( isset($_POST['pinboard-submit']) ) {

			foreach($pinboard_options['widget_fields'] as $key => $field) {
				$options[$number][$key] = $field['default'];
				$field_name = sprintf('%s_%s_%s', $pinboard_options['prefix'], $key, $number);

				if ($field['type'] == 'text') {
					$options[$number][$key] = strip_tags(stripslashes($_POST[$field_name]));
				} elseif ($field['type'] == 'checkbox') {
					$options[$number][$key] = isset($_POST[$field_name]);
				}
			}

			update_option('widget_pinboard', $options);
		}

		foreach($pinboard_options['widget_fields'] as $key => $field) {
			
			$field_name = sprintf('%s_%s_%s', $pinboard_options['prefix'], $key, $number);
			$field_checked = '';
			if ($field['type'] == 'text') {
				$field_value = htmlspecialchars($options[$number][$key], ENT_QUOTES);
			} elseif ($field['type'] == 'checkbox') {
				$field_value = 1;
				if (! empty($options[$number][$key])) {
					$field_checked = 'checked="checked"';
				}
			}
			
			printf('<p style="text-align:right;" class="pinboard_field"><label for="%s">%s <input id="%s" name="%s" type="%s" value="%s" class="%s" %s /></label></p>',
				$field_name, __($field['label']), $field_name, $field_name, $field['type'], $field_value, $field['type'], $field_checked);
		}
		echo '<input type="hidden" id="pinboard-submit" name="pinboard-submit" value="1" />';
	}


	function widget_pinboard_setup() {
		$options = $newoptions = get_option('widget_pinboard');
		
		//echo '<style type="text/css">.pinboard_field { text-align:right; } .pinboard_field .text { width:200px; }</style>';
		
		if ( isset($_POST['pinboard-number-submit']) ) {
			$number = (int) $_POST['pinboard-number'];
			$newoptions['number'] = $number;
		}
		
		if ( $options != $newoptions ) {
			update_option('widget_pinboard', $newoptions);
			widget_pinboard_register();
		}
	}
	
	
	function widget_pinboard_page() {
		$options = $newoptions = get_option('widget_pinboard');
	?>
		<div class="wrap">
			<form method="POST">
				<h2><?php _e('Pinboard Widgets'); ?></h2>
				<p style="line-height: 30px;"><?php _e('How many Pinboard widgets would you like?'); ?>
				<select id="pinboard-number" name="pinboard-number" value="<?php echo $options['number']; ?>">
	<?php for ( $i = 1; $i < 10; ++$i ) echo "<option value='$i' ".($options['number']==$i ? "selected='selected'" : '').">$i</option>"; ?>
				</select>
				<span class="submit"><input type="submit" name="pinboard-number-submit" id="pinboard-number-submit" value="<?php echo attribute_escape(__('Save')); ?>" /></span></p>
			</form>
		</div>
	<?php
	}
	
	
	function widget_pinboard_register() {
		
		$options = get_option('widget_pinboard');
		$dims = array('width' => 300, 'height' => 400);
		$class = array('classname' => 'widget_pinboard');

		for ($i = 1; $i <= 9; $i++) {
			$name = sprintf(__('Pinboard #%d'), $i);
			$id = "pinboard-$i"; // Never never never translate an id
			wp_register_sidebar_widget($id, $name, $i <= $options['number'] ? 'widget_pinboard' : /* unregister */ '', $class, $i);
			wp_register_widget_control($id, $name, $i <= $options['number'] ? 'widget_pinboard_control' : /* unregister */ '', $dims, $i);
		}
		
		add_action('sidebar_admin_setup', 'widget_pinboard_setup');
		add_action('sidebar_admin_page', 'widget_pinboard_page');
	}

	widget_pinboard_register();
}

// Run our code later in case this loads prior to any required plugins.
add_action('widgets_init', 'widget_pinboard_init');

?>