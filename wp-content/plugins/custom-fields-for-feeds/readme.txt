/*
Plugin Name: Custom Fields for Feeds
Plugin URI: http://justintadlock.com/archives/2008/01/27/custom-fields-for-feeds-wordpress-plugin
Description: This puts images or videos into your feeds through the use of custom fields.  You can alter the custom field Keys and what is displayed.
Author: Justin Tadlock
Version: 1.0.1 Beta
Author URI: http://justintadlock.com
*/

Updates ******************************************

Version 1.0.1 Beta **
* I added a few custom fields so the plugin would work for users of my Options WordPress theme.
* Custom field stack order has now changed to:
- Video
- Image
- Feature Full
- Feature Image
- Thumbnail Large
- Thumbnail

Instructions for use ********************************

This "readme" file is just a starter for more serious developers, users of custom fields, or those of you following my custom fields tutorial series.  If you're using one of my themes that makes use of custom fields, then you only need to activate the plugin.  It should work fine for you.

You need to upload the "custom-fields-for-feeds" folder to your WordPress plugins folder.  Then activate it from your WordPress administration panel.

To use this plugin out of the box, you must do a few things.  First, you must know how to use custom fields <http://justintadlock.com/archives/2007/10/24/using-wordpress-custom-fields-introduction>.  This is imperative.

The plugin will display one of four different items.  Each one of these are Keys.  Note that custom field  Keys are case-sensitive.  This is the order of the list the plugin checks for the custom fields.  If one is used, then the others aren't displayed.

- Video
- Image
- Feature Image
- Thumbnail

To use the "Video" custom field and if you're not using one of my themes, you need to look at this tutorial on adding videos to your sidebar <http://justintadlock.com/archives/2008/01/25/how-to-add-videos-to-your-wordpress-sidebar>.  It explains a lot.

If you're using the "Image," "Feature Image," or "Thumbnail," it wouldn't hurt to follow this tutorial on  adding images to posts <http://justintadlock.com/archives/2007/10/27/wordpress-custom-fields-adding-images-to-posts>.  I go into detail on how to accomplish this.

I'll assume from this point that you know how to use WordPress custom fields.

To add a video (YouTube, Google, MetaCafe, etc.) to your feed using custom fields, you need to create a Key named "Video."  Give it a Value of the video's "embed URL."

To add an image, give it a Key of "Image," "Feature Image," or "Thumbnail."  The Value should be the "URL of the image" you want to use.

There's also the option of adding alt text to your images.  The Keys are:

- Image Alt
- Feature Image Alt
- Thumbnail Alt

Just give them a Value of the alt text you want for the image.

Other than what's said in here, you just have to play around with it a bit to get it to do what you want.