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
define('DB_NAME', 'wordpress_mws');

/** MySQL database username */
define('DB_USER', 'wordpress_mws');

/** MySQL database password */
define('DB_PASSWORD', 'KpT9qjrkkor4C');

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
define('AUTH_KEY',         'bBu*|/3WsmwWy^9kZ2k@,EcVxNB~Om:6v79Yswu7msIWIWx$L 8P[BurI&)mX&Rl');
define('SECURE_AUTH_KEY',  '%#T-Cx%-J5WB@,~Jz9H6-T=$$>>*JQ:i=|n!zzM#4XN;T$(H6L4{Lc6#8QT_FGx$');
define('LOGGED_IN_KEY',    'YT/KHBFxo.;p_y]G6__5qZGGtmu]62QPjl&]u$jPyYmWT.07/:]BE)=t}7?O,|1l');
define('NONCE_KEY',        '9z5]P!K]xX:=}c@MF!9:JQXTY*SbXUy96~8`LTC+s_b0(I-9a1vw{2/G#s+X<3 ]');
define('AUTH_SALT',        '.c.U=L7v8|m`N*g%9qDio`GhYz(=M?i4?6EK  P,>F>7ExqkmREM)mIEHbq~<U0-');
define('SECURE_AUTH_SALT', '|2+,QJiJg,vQcUJwH}r1]N2Bo~z+cy(dr Bi?vB/C0%.+m7+#MOFB++p#qogkRIH');
define('LOGGED_IN_SALT',   '+B+O|Sl/6Gh7LqNK5!U9Xg7q-9vT|y7C8*j._3/H!wR$u56UoKqd|T<Lh51]V<.G');
define('NONCE_SALT',       '= 0hB&JX5oxN<(@M%|nBK#}jL2`Y}cKI:Fdm$:XIBD;>Eb`!hMAT`5Yl@Fjn+Isw');
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
