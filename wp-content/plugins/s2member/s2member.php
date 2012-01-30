<?php
/**
* The main plugin file.
*
* This file loads the plugin after checking
* PHP, WordPress® and other compatibility requirements.
*
* Copyright: © 2009-2011
* {@link http://www.websharks-inc.com/ WebSharks, Inc.}
* ( coded in the USA )
*
* Released under the terms of the GNU General Public License.
* You should have received a copy of the GNU General Public License,
* along with this software. In the main directory, see: /licensing/
* If not, see: {@link http://www.gnu.org/licenses/}.
*
* @package s2Member
* @since 1.0
*/
/* -- This section for WordPress® parsing. ------------------------------------------------------------------------------

Version: 111220
Stable tag: 111220
Framework: WS-P-110523

SSL Compatible: yes
bbPress Compatible: yes
WordPress Compatible: yes
BuddyPress Compatible: yes
WP Multisite Compatible: yes
Multisite Blog Farm Compatible: yes

PayPal® Standard Compatible: yes
PayPal® Pro Compatible: yes w/ s2Member Pro
Google® Checkout Compatible: yes w/ s2Member Pro
Authorize.Net® Compatible: yes w/ s2Member Pro
ClickBank® Compatible: yes w/ s2Member Pro
AliPay® Compatible: yes w/ s2Member Pro
ccBill® Compatible: yes w/ s2Member Pro

Tested up to: 3.3
Requires at least: 3.2
Requires: WordPress® 3.2+, PHP 5.2.3+

Copyright: © 2009 WebSharks, Inc.
License: GNU General Public License
Contributors: WebSharks, PriMoThemes
Author URI: http://www.primothemes.com/
Author: PriMoThemes.com / WebSharks, Inc.
Donate link: http://www.primothemes.com/donate/

Text Domain: s2member
Domain Path: /includes/translations

Plugin Name: s2Member
Video Tutorials: http://www.s2member.com/videos/
Pro Module / Home Page: http://www.s2member.com/
Pro Module / Prices: http://www.s2member.com/prices/
Forum URI: http://www.primothemes.com/forums/viewforum.php?f=4
Privacy URI: http://www.primothemes.com/about/privacy-policy/
PayPal Pro Integration: http://www.primothemes.com/forums/viewtopic.php?f=4&t=304
Professional Installation URI: http://www.s2member.com/professional-installation/
Plugin URI: http://www.primothemes.com/post/product/s2member-membership-plugin-with-paypal/
Description: s2Member® (Membership w/ PayPal®). Powerful (free) membership capabilities. Protect/secure members only content w/ roles/capabilities for members.
Tags: membership, members, member, register, signup, paypal, paypal pro, pay pal, s2member, authorize.net, google checkout, ccbill, clickbank, alipay, subscriber, members only, buddypress, buddy press, buddy press compatible, shopping cart, checkout, api, options panel included, websharks framework, w3c validated code, includes extensive documentation, highly extensible

-- end section for WordPress® parsing. ------------------------------------------------------------------------------- */
if (realpath (__FILE__) === realpath ($_SERVER["SCRIPT_FILENAME"]))
	exit ("Do not access this file directly.");
/**
* The installed version of s2Member.
*
* @package s2Member
* @since 3.0
*
* @var str
*/
if (!defined ("WS_PLUGIN__S2MEMBER_VERSION"))
	define ("WS_PLUGIN__S2MEMBER_VERSION", "111220");
/**
* Minimum PHP version required to run s2Member.
*
* @package s2Member
* @since 3.0
*
* @var str
*/
if (!defined ("WS_PLUGIN__S2MEMBER_MIN_PHP_VERSION"))
	define ("WS_PLUGIN__S2MEMBER_MIN_PHP_VERSION", "5.2.3");
/**
* Minimum WordPress® version required to run s2Member.
*
* @package s2Member
* @since 3.0
*
* @var str
*/
if (!defined ("WS_PLUGIN__S2MEMBER_MIN_WP_VERSION"))
	define ("WS_PLUGIN__S2MEMBER_MIN_WP_VERSION", "3.2");
/**
* Minimum Pro version required by the Framework.
*
* @package s2Member
* @since 3.0
*
* @var str
*/
if (!defined ("WS_PLUGIN__S2MEMBER_MIN_PRO_VERSION"))
	define ("WS_PLUGIN__S2MEMBER_MIN_PRO_VERSION", "111220");
/*
Several compatibility checks.
If all pass, load the s2Member plugin.
*/
if (version_compare (PHP_VERSION, WS_PLUGIN__S2MEMBER_MIN_PHP_VERSION, ">=") && version_compare (get_bloginfo ("version"), WS_PLUGIN__S2MEMBER_MIN_WP_VERSION, ">=") && !isset ($GLOBALS["WS_PLUGIN__"]["s2member"]))
	{
		$GLOBALS["WS_PLUGIN__"]["s2member"]["l"] = __FILE__;
		/*
		Hook before loaded.
		*/
		do_action ("ws_plugin__s2member_before_loaded");
		/*
		System configuraton.
		*/
		include_once dirname (__FILE__) . "/includes/syscon.inc.php";
		/*
		Hooks and Filters.
		*/
		include_once dirname (__FILE__) . "/includes/hooks.inc.php";
		/*
		Hook after system config & Hooks are loaded.
		*/
		do_action ("ws_plugin__s2member_config_hooks_loaded");
		/*
		Load a possible Pro module, if/when available.
		*/
		if (apply_filters ("ws_plugin__s2member_load_pro", true) && file_exists (dirname (__FILE__) . "-pro/pro-module.php"))
			include_once dirname (__FILE__) . "-pro/pro-module.php";
		/*
		Configure options and their defaults.
		*/
		ws_plugin__s2member_configure_options_and_their_defaults ();
		/*
		Function includes.
		*/
		include_once dirname (__FILE__) . "/includes/funcs.inc.php";
		/*
		Include Shortcodes.
		*/
		include_once dirname (__FILE__) . "/includes/codes.inc.php";
		/*
		Hooks after loaded.
		*/
		do_action ("ws_plugin__s2member_loaded");
		do_action ("ws_plugin__s2member_after_loaded");
	}
/*
Else NOT compatible. Do we need admin compatibility errors now?
*/
else if (is_admin ()) /* Admin compatibility errors. */
	{
		if (!version_compare (PHP_VERSION, WS_PLUGIN__S2MEMBER_MIN_PHP_VERSION, ">="))
			{
				add_action ("all_admin_notices", create_function ('', 'echo \'<div class="error fade"><p>You need PHP v\' . WS_PLUGIN__S2MEMBER_MIN_PHP_VERSION . \'+ to use the s2Member plugin.</p></div>\';'));
			}
		else if (!version_compare (get_bloginfo ("version"), WS_PLUGIN__S2MEMBER_MIN_WP_VERSION, ">="))
			{
				add_action ("all_admin_notices", create_function ('', 'echo \'<div class="error fade"><p>You need WordPress® v\' . WS_PLUGIN__S2MEMBER_MIN_WP_VERSION . \'+ to use the s2Member plugin.</p></div>\';'));
			}
	}
?>