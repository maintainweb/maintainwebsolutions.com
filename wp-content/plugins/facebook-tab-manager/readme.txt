=== Facebook Tab Manager ===
Contributors: davidfcarr, hplogsdon
Tags: facebook, fb, rich text editor, custom post type, social media, facebook tab, iframe
Donate link: http://facebooktabmanager.com
Requires at least: 3.0
Tested up to: 3.3.1
Stable tag: 2.9.9

Makes WordPress function as an editor for tabs you can embed in a Facebook page for your business, campaign, or organization.

== Description ==

The Facebook Tab Manager allows you to create landing pages and other types of content to be displayed within Facebook, particularly within the tabs that appear on Facebook business pages and pages for other types of organizations. This provides a way of putting more interesting layouts and functionality into your Facebook pages, without the need to get too deep into fancy programming.

The Facebook Tab Manager was specifically designed to take advantage of a recent Facebook page redesign that added support for iframe tabs on Facebook pages. Optionally, you can now also specify content to be displayed on an associated canvas page.

Tab content can include most any WordPress content, including output from Shortcodes and other plugin functions.

[__Facebook Tab Manager for WordPress Documentation and Blog__](http://facebooktabmanager.com/)

[__Live examples at www.facebook.com/carrcomm__](http://www.facebook.com/carrcomm)

**Note:** Facebook requires all "apps" and page tabs to be displayed within facebook.com be available from both an HTTP and an HTTPS address. This means you must obtain an SSL certificate for your web domain and configure it on your server. When Facebook users browse the website in HTTPS mode, you need to be able to present your embedded content at an HTTPS address also.

As an alternative, Carr Communications a subscription service including WordPress accounts with Facebook Tab Manager and SSL security pre-configured at [__TabMgr.com__](https://tabmgr.com/).

See also [__WP FB Commerce__](http://wordpress.org/extend/plugins/wp-fb-commerce/), an extension to the WP e-Commerce plugin that lets you offer selected products or your full catalog embedded in a Facebook tab.

== Installation ==

1. Upload the entire `facebook-tab-manager` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Create/edit content through the Facebook Tabs menu. The editing screen provides guidelines for how to fill in the Facebook Developer form to register your tab content as a Facebook app.
1. Optionally, copy the fbtab-theme folder to your `/wp-content/themes/` directory and modify as desired. Themes for use with Facebook Tab Manager are activated through the plugin's settings screen, independently of your site's theme for website visitors.

= Shortcode Options =

* To include a JavaScript widget, such as one of the [Facebook Social Plugins](http://developers.facebook.com/docs/plugins/), paste it into the WordPress editor in `Visual` mode and wrap it with `[fbtab]WIDGET-CONTENT-HERE[/fbtab]`. The shortcode processing function will fix the HTML entities the editor adds on angle brackets and quotation marks.
* To include blog post or paste content, you can use `[fbtab query="QUERY-STRING"]` where the query string is something like query="p=1" or query="category_name=facebook-tab-manager" -- see the documentation for the [query_posts function](http://codex.wordpress.org/Function_Reference/query_posts) for possible values. You can also use a format attribute of format="headline" or format="excerpt" -- see the Tab Manager tab at [www.facebook.com/carrcomm](http://www.facebook.com/carrcomm) for an example using `[fbtab query="category_name=facebook-tab-manager" format="excerpt"]`
* To include a block of content that should only be shown to people who have yet to click the Like button on your page, use the like=0 parameter, and for to only show something to people who have Liked your page, use like=1. You can do this with either the `[fbtab]` shortcode or with a variation called `[fblike]`
* The `[fblike]` shortcode is intended to enclose blocks of content, whereas `[fbtab]` can be used to enclose blocks of inline code such as JavaScript. These variations accept the same parameters, but `[fblike]` is equivalent to `[fbtab decode="0"]` where the parameter says not to run HTML entity decode transformations on the content within the shortcode tags.
* Example `[fblike like="0"]IMAGE TO SHOW TO NEW VISITORS[/fblike] [fblike like="1"]FAN-ONLY CONTENT[/fblike]`

== Frequently Asked Questions ==

= Where can I get more information about using Facebook Tab Manager? =

See the [plugin homepage](http://www.carrcommunications.com/wordpress-plugins/facebook-tab-manager/).

Also discussions on the [Carr Communications Facebook page](http://www.facebook.com/carrcomm).


== Screenshots ==

1. Blog content displayed on a Facebook business page.
2. Sample Facebook app configuration screen
3. Reveal Tab Setup screen

== Credits ==

    Facebook Tab Manager
    Copyright (C) 2011 David F. Carr

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    See the GNU General Public License at <http://www.gnu.org/licenses/>.
	
== Changelog ==

= 2.9.9 =

Bug fix for settings screen (footer options).

= 2.9.8 =

Cleanup of deprecated WordPress functions and minor PHP coding errors.

= 2.9.7 =

Continuing to address recent changes in the Facebook platform, which made it necessary for Facebook Tab Manager to supply its own Add to Page function to let you connect your tab to a specific Facebook page.

The new Add to Page Links screen for admins lets you generate an Add to Page link for reveal tabs created with the Reveal Tab Setup utility, or tabs based on existing WordPress pages or posts using the ?fb=tab query string at the end of a URL.

= 2.9.6 =

Important update to keep pace with changes in the Facebook platform. Now, after recording the App ID # assigned by Facebook, you can record this in Facebook Tab Manager, and an "Add to Page" link will be displayed in the editor to allow you to add your tab to one or more pages. The previous installation method must be phased out because of Facebook's decision to eliminate application profile pages, which is where the Add to Page link used to be displayed.

= 2.9.5 =

Fix for sites that don't have pretty permalinks enabled.

= 2.9.4 =

2 new checkboxes.

* "Remove all but essential filters on post content" on Facebook Tab editor screen. Can be simpler than turning off filters individually.
* "Use redirect instead of AJAX / loading animation" on Reveal Tab Setup screen. The AJAX method is now the default. Works well for most content but may not work with some posts featuring inline JavaScript. Check here to use a redirect instead.

= 2.9.3 =

Misc. bugs

= 2.9.2 =

Renamed function that decodes Facebook signed request to prevent namespace conflicts with other plugins.

= 2.9.1 =

Fixes for alternate paths to the includes directory to make jQuery load properly for the preloader effect.

= 2.8.9 and 2.9 =

Bug fixes and tweaks. Reveal Tab function now uses JavaScript preloader by default. You can change it to a redirect by adding &redirect=1 to the end of the query string.

= 2.8.8 =

Bug fix in that fancy new theme / template code.

= 2.8.7 =

* Now supporting themes and custom templates that can be developed independently of the plugin. Should introduce more flexibility for those who want to alter the default presentation. The theme bundled with the plugin will be used by default, but you can also copy the fbtab-theme folder to wp-content/themes and modify as desired. Documentation at http://bit.ly/oTYyMB
* Previous hack for supporting custom templates deprecated.

= 2.8.6 =

Bug fix. Error in the code for saving customization settings.

= 2.8.5 =

* CSS changes to prevent display of scroll bars, even when too-wide content such as a large image is included (width set on body, with overflow hidden)
* Changed the way custom CSS is loaded into template. Should perform better.
* Reveal Tab Setup utility now displays 10 most recent combinations of Fan / Non-fan pages and corresponding URLs.

= 2.8.4 =

Update to Reveal Tab Setup utility. Looks like Facebook has made some platform changes that were preventing this from working properly, and I've made my own changes to compensate. This version also supports using a form plugin such as Contact Form 7 in the fan-only version of the tab, which wasn't working right previously.

= 2.8.3 =

Updating template.php code for better handling of wp_head and wp_footer output with filters selectively deactivated.

= 2.8.2 =

Session key set for Liked pages so site will "remember" that setting even after following a link or form post. Important for scenarios where you want to embed a form inside an fblike shortcode.

= 2.8.1 =

Correcting error in Reveal Tab Setup screen

= 2.8 =

* Fixed the fbtab / fblike shortcodes so they can contain other shortcodes - for example, to output a form only to be displayed to new people or only to fans of the page. Function now recursively calls do_shortcode to check if other shortcodes are embedded in the content. Thanks to Jason Lane for pointing out the error.
* Tested with WordPress 3.2.1

= 2.7.4 =

Thanks to H.P. Logsdon for some fixing some bugs I missed.

= 2.7.3 =

Reveal Tab Setup screen added. Includes a utility for constructing a special url pointing to alternate post IDs for fans and non-fans. On-screen documentation explains tradeoffs between this, shortcode methods.

= 2.7.2 =

You can now use ?fb=tab&minfilters=1 as a query string parameters to display existing content in the fbtab template with most filters on the_content turned off.

= 2.7.1 =

Bug fix. Some checkbox parameters weren't being saved properly.

= 2.7 =

Added Documentation as a submenu on Facebook Tabs. Pulls in content from the plugin home page on carrcommunications.com

= 2.6.9 =

Bug fix for CSS display, particularly in IE9

= 2.6.7 =

Fixed bug in CSS code that prevented custom styles from loading properly.

= 2.6.6 =

* Better instructions for secure canvas / secure tab setup.
* Clarified the UI for selectively deactivating filters on the body of a post, which may be appropriate for a blog page but not for Facebook.
* Fixed a bug in the function for deactivating filters.

= 2.6.5 =

Tweaked the template to reference https addresses for the the stylesheet and Facebook JavaScript library when https version of content is viewed. This is to prevent the Internet Explorer mixed content warning when Facebook users have https browsing turned on. This function will also attempt to correct src image and script references in the body of the post to https (only for local files within your domain).

= 2.6.2 =

* Added `[fblike]` shortcode as an alias to `[fbtab decode="1"]`. Recommended for situations where you are wrapping a block of text and image content inside a shortcode that indicates whether it should only be shown to people who have not yet liked, or who have liked your page. Example `[fblike like="0"]IMAGE TO SHOW TO NEW VISITORS[/fblike] [fblike like="1"]FAN-ONLY CONTENT[/fblike]`
* To display an existing page in the fbtab template, you can add the query string ?fb=tab to the end of the page url. You can also use resize=1 to invoke the resizing JavaScript. Example: http://www.carrcommunications.com/contact/davids-resume/?fb=tab&resize=1
* Users who don't like my template can now specify their own should be used instead. Only recommended for advanced users with their own plugin/template development skills.
* Better conformance with WordPress coding standards.

= 2.6.1 =

Changed custom post type setup in response to a report of a conflict with a theme that also uses custom post types.

= 2.6 =

* Several changes to keep up with changes in the structure of the Facebook app/tab registration form.
* Misc performance tweaks.

= 2.5 =

New input box: More code to add to head (Scripts, External Styles) - may be a better way of adding a single JavaScript or CSS include, as opposed to bringing in everything associated with wp_head. Template order is now wp_head output, CSS (default and added through interface), then additional head code.

= 2.4 =

Fix to options screen

= 2.3 =

Additional shortcode options

* Add like="1" to the fbtab shortcode if content should only be displayed to people who have liked the page
* Add like="0" to the fbtab shortcode if content should only be displayed to people who have NOT liked the page
* You can add an explanation in either of these two cases such as message="You must like this page before this super-special content will be displayed."

= 2.2 =

Better handling / explanation for checkboxes to turn off filters and actions that aren't appropriate for a Facebook tab.

= 2.1 =

Bug fix

= 2.0 =

Fixing template.php display for canvas pages

= 1.9 =

Correcting tab/canvas setup code.

= 1.8 =

* Added checkbox option to set resize / auto resize for tabs taller than 800 pixels
* You can now create both a tab and a canvas page for your application.

= 1.7 =

* Added options page for setting defaults, such as filters to ignore and CSS to apply
* Clarified documentation for how to fill out the Facebook Developers form
* Flush Rewrite Rules set to run every time on initialization (not supposed to be necessary, according to the documentation, but seems to work better on some configurations)

= 1.6 =

Refinements to shortcode function

= 1.5 =

Added `[fbtab]` Shortcode documented on the Installation section of this document. Makes it easier to integrate blog content and JavaScript widgets such as Facebook plugins.

= 1.4 =

* Added the ability to deactivate content filters when the fbtab template is displayed. This lets you eliminate plugin content modifications that are not appropriate for your Facebook tab.
* Updated the instructions for configuration on Facebook.

= 1.1, 1.2, 1.3 =

Fixes to default styles handling, directory locations

= 1.0 =

First public release February 2011

== Upgrade Notice ==

Version 2.8.7 introduces a new, more flexible model for creating themes and custom templates that work with Facebook Tab Manager. If you previously provided a custom theme as an include file path, you will have to modify it to make it work with this upgrade.