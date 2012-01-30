<?php
/*
Plugin Name: Custom Fields for Feeds
Plugin URI: http://justintadlock.com/archives/2008/01/27/custom-fields-for-feeds-wordpress-plugin
Description: This puts images or videos into your feeds through the use of custom fields.  You can alter the custom field Keys and what is displayed.
Author: Justin Tadlock
Version: 1.0.1 Beta
Author URI: http://justintadlock.com
License: GPL
*/

/*  Copyright 2007 Justin Tadlock  (email : justin@justintadlock.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

add_filter('the_content', 'custom_fields_for_feeds');

function custom_fields_for_feeds( $content ) {

global $post, $id;
$blog_key = substr( md5( get_bloginfo('url') ), 0, 16 );
if ( ! is_feed() ) return $content;

// Get the custom fields ***

// Checks to see if there's an image
	$image = get_post_meta($post->ID, 'Image', $single = true);
	$image_alt = get_post_meta($post->ID, 'Image Alt', $single = true);

// If there's a Feature Full
	if($image == '') {
	$image = get_post_meta($post->ID, 'Feature Full', $single = true);
	$image_alt = get_post_meta($post->ID, 'Feature Full Alt', $single = true);
	}

// If there's a Feature Image
	if($image == '') {
	$image = get_post_meta($post->ID, 'Feature Image', $single = true);
	$image_alt = get_post_meta($post->ID, 'Feature Image Alt', $single = true);
	}

// If there's a Thumbnail Large
	if($image == '') {
	$image = get_post_meta($post->ID, 'Thumbnail Large', $single = true);
	$image_alt = get_post_meta($post->ID, 'Feature Large Alt', $single = true);
	}

// If there's a Thumbnail
	if($image == '') {
	$image = get_post_meta($post->ID, 'Thumbnail', $single = true);
	$image_alt = get_post_meta($post->ID, 'Thumbnail Alt', $single = true);
	}

// If there's no "Image Alt," "Feature Image Alt," or "Thumbnail Alt"
	if($image_alt == '') { $image_alt = 'This image has no alt text'; }

// Checks to see if there's a video (YouTube, Google Video, etc.) associated with this post
	$video = get_post_meta($post->ID, 'Video', $single=true);

// Displaying the content ***

// If there's a video, display the video with the content
	if($video !== '') {
	$content = '<p>
	<object type="application/x-shockwave-flash" data="'.$video.'" class="left" style="align: left; width: 275px; height: 230px; border: none; padding: 0; margin: 0;" id="video">
		<param name="movie" value="'.$video.'" />
		<param name="wmode" value="transparent" />
		<param name="quality" value="best" />
		<param name="bgcolor" value="#ffffff" />
		<param name="FlashVars" value="playerMode=embedded" />
	</object>
	</p>' . $content;
	return $content;
	} // End if video

// If there's not a video but an image, display the image with the content
	elseif($image !== '') {
	$content = '<p>
	<img src="'.$image.'" alt="'.$image_alt.'" />
	</p>' . $content;
	return $content;
	} // End if image

// If there's not an image or video, display the content
	else {
	$content = $content;
	return $content;
	}
} // End function
?>