<?php
/*
Plugin Name: WP-Table Reloaded Extensions
Plugin URI: http://tobias.baethge.com/wordpress-plugins/wp-table-reloaded-english/extensions/
Description: Custom Extensions for WP-Table Reloaded
Version: 1.0
Author: YOU, Tobias Baethge
*/
 
/**
 * Converts URLs (www, ftp, and email) in table cells to full HTML links
 * @author Tobias Baethge
 * @see http://tobias.baethge.com/2009/12/extension-1-url-to-link-conversion/
 */
function wp_table_reloaded_url_link_converter( $cell_content ) {
    return make_clickable( $cell_content );
}
add_filter( 'wp_table_reloaded_cell_content', 'wp_table_reloaded_url_link_converter' );
 
?>

