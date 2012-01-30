=== Client Status ===
Contributors: ericjuden
Tags: wordpress, client, status, dashboard, updates, core, themes, plugins, admin
Requires at least: 3.0
Tested up to: 3.1
Stable tag: trunk

== Description ==

Client Status is a dashboard that keeps tabs on your clients WordPress installations by checking for the latest updates to the WordPress core, plugins and themes. You have the option to enable emails to administrators of your site and the clients the site belongs to. Client Status can check for updates hourly, twice daily, or daily (uses the WordPress Cron system). This is meant to be a free alternative to the WordPress Status Dashboard found on codecanyon.net.

If you like the plugin, please rate it. Your reviews keep it going.

The icons used are from the silk icon set: http://www.famfamfam.com/lab/icons/silk/

== Installation ==

1. Copy the plugin files to <code>wp-content/plugins/</code>

2. Activate plugin from Plugins page

3. Go to Settings -> Client Status to adjust plugin settings

4. Must repeat above steps on client WordPress installs as well

6. From dashboard WordPress install, go to Clients -> Add New

5. Go to Dashboard -> Client Status for an overview of updates

== Screenshots ==

1. The dashboard screen.
2. The edit client screen.
3. The dashboard settings page.
4. The client settings page.

== Changelog ==

= 1.3.3 =
- Fixed a couple character encoding issus in data.php

= 1.3.2 =
- Fix for only first 10 clients being updated.
- Fix for emails being sent where it only reports the server is not indexable.
- Updated some text for the client type taxonomy.

= 1.3.1 =
- Fixed broken display on dashboard page when the client site was missing the client URL or if the security key was wrong on the client.

= 1.3 =
- Added number of clients to the Right Now widget on the dashboard. This is just the total number, not the number that needs updating. The actual number takes you to the edit page for the clients. The word Client(s) takes you to the dashboard.
- Added quick information to each client on the dashboard. There is a setting to enable/disable in the Settings.
- On the dashboard for each client, added a debugging link to view the client data being passed. You can find this to the right of the Server Information heading.
- Added the beginnings for translations.

= 1.2 =
- Added client type to edit.php for Clients.
- Added post, comment, and server information to data file. Note: this won't show up until you've updated the plugin on your clients servers and the data has refreshed on your main dashboard server.
- Added new setting to allow expanding all client information by default.
- Updated style.css to make green color not as annoying.
- Fixed bug on settings page where "Settings Saved" message would display twice.

= 1.1 =
- Added client type to allow categorizing your clients.
- Fix to data.php to hopefully resolve any xml errors people are getting.
- Fixed timezone offset on dashboard to show correct latest update time (Dan Coulter)
- Fixed manual refresh of client not working when post id is greater than 2 digits (Dan Coulter)

= 1.0.1 =
- Fix to dashboard.php found by Dan Coulter 

= 1.0 =
* Initial release