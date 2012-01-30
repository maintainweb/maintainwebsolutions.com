/*
Plugin Name: Tipsy Social Icons
Description: A widget that makes it easy to display a variety of popular social icons along with a Facebook-style tooltips.  Icons provided by Komodo Media's Social Icon Pack and Jason Frame's jQuery Tipsy plugin for effects.
Version: 1.1
Author: Tom McFarlin
Author URI: http://tommcfarlin.com
*/
jQuery(function() {
	jQuery('.tipsy-social-icon').each(function() {
		jQuery(this).tipsy({
			fade: false,
			title: 'alt',
			gravity: 'n'
		});
	});
});