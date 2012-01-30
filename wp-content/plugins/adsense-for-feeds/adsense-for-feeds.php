<?php
/*
Plugin Name: Google Adsense for Feeds
Plugin URI: http://wordpress.org/extend/plugins/adsense-for-feeds/
Description: This puts Google RSS Ads in your feed, make sure you fill in your publisher ID by editing the plugin file.
Author: Matt Mullenweg
Version: 1.1
Author URI: http://photomatt.net/
*/

$publisher = 'ca-pub-9971280513277476';

add_filter('the_content', 'adsense_for_feeds');

function adsense_for_feeds( $content ) {
	global $post, $id;
	$blog_key = substr( md5( get_bloginfo('url') ), 0, 16 );
	if ( ! is_feed() ) return $content;
	$content = $content . "<p><map name='google_ad_map_{$id}_$blog_key'>
<area shape='rect' href='http://imageads.googleadservices.com/pagead/imgclick/$id?pos=0' coords='1,2,367,28' />
<area shape='rect' href='http://services.google.com/feedback/abg' coords='384,10,453,23'/></map>
<img usemap='#google_ad_map_{$id}_$blog_key' border='0' src='http://imageads.googleadservices.com/pagead/ads?format=468x30_aff_img&amp;client=$publisher&amp;channel=&amp;output=png&amp;cuid=$id&amp;url= " . urlencode( get_permalink() ) . "' /></p>";
	return $content;
}

?>