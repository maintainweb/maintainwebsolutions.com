=== Styles with [Shortcodes] ===
Author: Alberto Lau (RightHere LLC)
Author URL: http://plugins.righthere.com/styles-with-shortcodes/
Tags: WordPress, Shortcodes, Shortcode API, jQuery UI, jQuery TOOLS, Toggle, Tabs, Accordion, Syntax Highlighter, Overlay, Buttons, Columns, Google Maps, Blockquotes, Pullquotes, Tables, Dividers, Colored Boxes, Picture frames, Tooltips, Facebook, Twitter
Requires at least: 3.0
Tested up to: 3.0.3
Stable tag: 1.5.0



======== Changelog ========
1.5.0 - December 19, 2010
* added 24 new Shortcodes 
* improved the Shortcode creator tool; added more info to Shortcode list, added new field types, author info, bundle info
* improved the Shortcode generator tool; added descriptions to fields
* added option to recover original Shortcodes (bundles)
* added loading of scripts and styles in a separate file, from an add-on 
* reorganized core, so all scripts and styles are only loaded when a Shortcode requires it.

1.0.1 - November 30, 2010
* Added support for user Roles with Edit Post and Edit Page capabilities

1.0.0 (November 27, 2010)
* First release.

======== Description ========

This plugin lets you customize content faster and easier than ever before by using Shortcodes. Choose from more than 70 built in shortcodes like; jQuery Toggles and Tabs, Tooltips, Column shortcodes, Gallery and Image shortcodes, Button Styles, Alert Box Styles, Animated Alert Box Styles, Pullquotes, Blockquotes, Tables, Unordered Lists, Twitter buttons, Retweet button, Facebook Like buttons and many more!
You can create your own shortcakes and share them with friends and other people who also uses the Styles with Shortcodes plugin. 

== Installation ==

1. Upload the 'style-with-shortcodes' folder to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Click on 'Shortcodes' in the left admin bar of your dashboard

== Frequently Asked Questions ==

Q: How do I export a shortcode to a friend that also has Styles with Shortcodes?
A: Click on Shortcodes in the menu to the left. Find the shortcode you would like to export (share with your friend). Click on Edit and then scroll down to the bottom and click on "Export this shortcodes settings". When you see the code copy it and simply send it to your friend by email. Your friend will need to have a valid license for the Styles with Shortcodes plugin in order to import your shortcode.

Q: Can other developers create shortcodes for this plugin?
A: Yes, the idea is that anyone can develop shortcakes and then you can import them if you have a licensed copy of Styles with Shortcodes.

Q: How do I insert a shortcode?
A: First you need to create a Post or a Page. Then click on the "S" icon above the visual editor. You will find it in the same line as the default WordPress icons (Upload/Insert). When you click the icon you will get a "Add Styles with Shortcodes" box opening. First you select the Shortcode Category. Right now there are 16 different categories with over 50 different variations of shortcodes. When you have selected the category then you select the shortcode. And then you will get a easy to user interface that tells you exactly what information is needed to insert the chosen shortcode.

Q: How do I create a new shortcode?
A: Click on "Add new Shortcode" in the left menu. The plugin utilizes the Shortcode API that was introduced with WordPress 2.5. It is a simple set of functions for creating macro codes for use in post and page content. The API handles all the tricky parsing, eliminating the need for writing a custom regular expression for each shortcode. Fist define your shortcode fields and then create your shortcode template, style (CSS) and you can even include Javascript if it is needed for your shortcode.

Please notice that you need some knowledge of HTML, PHP, CSS (and Javascript) in order to create your own shortcakes. If you don't know this you can still use all the built in shortcakes and get additional shortcakes. 

Q: How do I import a shortcode from a friend?
A: A shortcode can have many different shortcode fields, template, styles (CSS) and Javascript and it would be a tedious process to manually copy each field and settings to a new shortcode. Therefore we have created a really easy to use export/import feature. A Exported Shortcode will look something like this:

Tzo4OiJzdGRDbGFzcyI6OTp7czoxMDoicG9zdF90aXRsZSI7czoxODoiU3ludGF4IEhpZ2hsaWdodGVyIjtzOjEyOiJzY19zaG9ydGNvZGUiO3M6ODoic3dzX2NvZGUiO3M6MTM6InNjX3Nob3J0Y29kZXMiO2E6MDp7fXM6MTE6InNjX3RlbXBsYXRlIjtzOjYxOiI8cHJlIG5hbWU9ImNvZGUiIGNsYXNzPSJicnVzaDp7bGFuZ3VhZ2V9Ij4NCntjb250ZW50fQ0KPC9wcmU+IjtzOjY6InNjX2NzcyI7czoyOTQ6IjxsaW5rIHR5cGU9InRleHQvY3NzIiByZWw9InN0eWxlc2hlZXQiIGhyZWY9IntwbHVnaW51cmx9anMvc3ludGF4aGlnaGxpZ2h0ZXJfMy4wLjgzL3N0eWxlcy9zaENvcmUuY3NzIj48L2xpbms+PGxpbmsgdHlwZT0idGV4dC9jc3MiIHJlbD0ic3R5bGVzaGVldCIgaHJlZj0ie3BsdWdpbnVybH1qcy9zeW50YXhoaWdobGlnaHRlcl8zLjAuODMvc3R5bGVzL3NoVGhlbWVEZWZhdWx0LmNzcyI+PC9saW5rPg0KPHN0eWxlPg0KLnN5bnRheGhpZ2hsaWdodGVyIGNvZGUgew0KZGlzcGxheTppbmxpbmU7DQp9DQo8L3N0eWxlPiI7czo1OiJzY19qcyI7czoxNzk3OiI8c2NyaXB0IHNyYz0ie3BsdWdpbnVybH1qcy94cmVnZXhwLW1pbi5qcyIgdHlwZT0idGV4dC9qYXZhc2Nyat9fX0=

In order to import a Exported Shortcode click on "Create new Shortcode" and then scroll down to the "Import" field. Copy and paste the code into the field and click "More info". The system will now analyze the shortcode and tell you what type of code it is e.g.

Name:		Syntax Highlighter
Shortcode:	sws_code
Bundle
Categories:	Code

And then just click "Confirm Import shortcode settings". No need to know anything about HTML, PHP, CSS or Javascript!



==Sources and Credits ==

I've used the following opensource projects, graphics, fonts, API's or other files as listed. Thanks to the author for the creative work they made.

1) jQuery TOOLS UI library by Tero Piirainen (http://flowplayer.org/tools/)

2) Syntax Highlighter by Alex Gorbatchev (http://alexgorbatchev.com/SyntaxHighlighter/)

3) jQuery UI library (http://jqueryui.com/)

4) TimThumb by Ben Gillbanks (http://www.binarymoon.co.uk/projects/timthumb/)

5) Google Maps API version 3.0 (http://code.google.com/apis/maps/documentation/javascript/)

6) jQuery Color Picker (http://www.eyecon.ro/colorpicker/)

7) Facebook (http://developers.facebook.com/docs/reference/plugins/like)

8) Twitter (http://twitter.com/about/resources/tweetbutton)

9) ReTweet (http://tweetmeme.com/about/retweet_button)

10) Prelodify (Extended License, http://codecanyon.net/item/preloadify/133636) Item Purchase Code: a780ced4-3ae9-4634-a336-dd9b419df18e, Licensor's Author Username: 5thSenseLabs, Licensee: RightHere LLC
