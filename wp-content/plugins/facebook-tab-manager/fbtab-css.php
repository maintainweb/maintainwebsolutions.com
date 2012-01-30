<?php
if($_GET["show"] == 'text')
	header('Content-type: text/plain');
else
	header('Content-type: text/css');

// load wordpress into external file
// buffer to avoid display of session error
ob_start();
$wp_config = '../wp-config.php';
	
for($i = 0; $i < 5; $i++)
	{
	if(file_exists($wp_config) )
		{
		require($wp_config);
		break;
		}
	$wp_config = '../'.$wp_config;
	}

if($_GET["postid"])
{
$id = $_GET["postid"];
$custom_fields = get_post_custom($id);
}

$width = ($custom_fields["_canvas"][0] || $_GET["canvas"]) ? 'max-width:710px;' : 'max-width:510px;';

ob_get_clean();
$fbt_theme = get_option('fbt_theme');
$style =($fbt_theme) ? WP_CONTENT_DIR.'/themes/'.$fbt_theme.'/style.css' : WP_PLUGIN_DIR.'/facebook-tab-manager/fbtab-theme/style.css';
if($_GET["show"] == 'text')
	echo "/* styles from $style */\n\n";
else
	echo "html .mceContentBody, #content {width: ".$width.";}\n";
include $style;
echo "\n".$custom_fields["_style"][0]; ?>