<?php
/** 
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information by
 * visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wp_maintainweb');

/** MySQL database username */
define('DB_USER', 'wp_maintainweb');

/** MySQL database password */
define('DB_PASSWORD', 'LZA2nyc2yXjhhA');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link http://api.wordpress.org/secret-key/1.1/ WordPress.org secret-key service}
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'DHB7mY%OLelINzsunYWL*Y9PSd%m#UTMXWZFPMdpQYKVHCt57RMq%dDr52dsw22m');
define('SECURE_AUTH_KEY',  's0rOqPCiC*JceQEMhYsSESvw&SnG61v6$oXMj%3^Elghtw0U)KFsxaq@^qDn4UcL');
define('LOGGED_IN_KEY',    'D0g8LiD%uYA43PLkyrAct&LzrcNYm8f^s(7qXB^HPNSl@#kg#T!m7eXD3JLXZ7F$');
define('NONCE_KEY',        'ZruTqnKmT0Wz^eD(3EagkzI8)0CBO)IVc)mR$7uxysTy1yR5e)ABB6%4CVCcLYrP');
define('AUTH_SALT',        'bYHNX9*3fVRYyH#%KF!Pi&y$S6hTvqwsgF9ymfuxPV$@JK@WsTRn5Udde@6ReyG4');
define('SECURE_AUTH_SALT', 'iQI22Px#0(x$U5L2gRJixWqjyFJ6Tjl89Bl@e5fP99(R(1rYS@DJpVYjt3DEtUk$');
define('LOGGED_IN_SALT',   'bR1sgIIGYP@NnIi@&ilUN01m*jxz3C&^(wN9t1wH6RRP)wNyl^snDihU2qOSEu3q');
define('NONCE_SALT',       'ZxzF6Y9q3JLyQvW6ojKQ6)i$XyNLyU1mw0wKhrQNR(n8CoTxT)YtmjpAlSB^0rqZ');
/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress.  A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de.mo to wp-content/languages and set WPLANG to 'de' to enable German
 * language support.
 */
define ('WPLANG', 'en_US');

define ('FS_METHOD', 'direct');

define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** WordPress absolute path to the Wordpress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

?>
