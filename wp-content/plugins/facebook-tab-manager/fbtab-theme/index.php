<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" > 
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php
// adjustments for which filters and actions should run
fbtab_template_setup();
// hack for version included with plugin
$style =(strpos(__FILE__,'plugins')) ? plugins_url('/style.css',__FILE__) : get_bloginfo( 'stylesheet_url' );
?>
<link rel="stylesheet" type="text/css" media="all" href="<?php echo $style; ?>" />
<?php
// outputs code for stylesheet, determines whether to execute wp_head, any custom CSS or header code specified in editor
fbtab_head();
?>
</head>

<body class="fbtab">
<div id="content">
<?php
while (have_posts()) : the_post();
?>
<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
<?php
fbtab_title();
?>
<div class="entry-content">
<?php the_content(); ?>
</div><!-- .entry-content -->
</div><!-- #post-## -->
<?php
endwhile;
?>
</div>
<?php
//outputs resizing code, if specified
fbtab_footer();
?>
</body>
</html>