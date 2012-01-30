<?php 
define('CLIENT_STATUS_PLUGIN_DIR', WP_PLUGIN_DIR . '/client-status');
define('CLIENT_STATUS_PLUGIN_URL', plugins_url($path = '/client-status'));
define('CLIENT_STATUS_IMAGE_URL', CLIENT_STATUS_PLUGIN_URL . '/images');
define('CLIENT_STATUS_DATA_URL', 'wp-content/plugins/client-status/data.php');
define('CLIENT_STATUS_SETTINGS_URL', 'wp-admin/options-general.php?page=client-status-options');
define('CLIENT_STATUS_WP_COMMENTS_URL', 'wp-admin/edit-comments.php');
define('CLIENT_STATUS_WP_POSTS_URL', 'wp-admin/edit.php');
define('CLIENT_STATUS_WP_PRIVACY_URL', 'wp-admin/options-privacy.php');
define('CLIENT_STATUS_WP_UPDATE_CORE_URL', 'wp-admin/update-core.php');
define('CLIENT_STATUS_WP_UPDATE_PLUGINS_URL', 'wp-admin/plugins.php');
define('CLIENT_STATUS_WP_UPDATE_THEMES_URL', 'wp-admin/themes.php');
define('CLIENT_STATUS_INSTALL_TYPE_CLIENT', '1');
define('CLIENT_STATUS_INSTALL_TYPE_DASHBOARD', '2');
define('CLIENT_STATUS_CRON_HOURLY', 'hourly');
define('CLIENT_STATUS_CRON_TWICE_DAILY', 'twicedaily');
define('CLIENT_STATUS_CRON_DAILY', 'daily');

define('CLIENT_STATUS_IMAGE_OK', CLIENT_STATUS_IMAGE_URL . '/tick.png');
define('CLIENT_STATUS_IMAGE_PROBLEM', CLIENT_STATUS_IMAGE_URL . '/error.png');
define('CLIENT_STATUS_IMAGE_ERROR', CLIENT_STATUS_IMAGE_URL . '/cross.png');
define('CLIENT_STATUS_IMAGE_PLUGIN_ERROR', CLIENT_STATUS_IMAGE_URL . '/plugin_error.png');
define('CLIENT_STATUS_IMAGE_THEME_PROBLEM', CLIENT_STATUS_IMAGE_URL . '/layout_error.png');
?>