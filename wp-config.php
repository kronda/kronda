<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
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
define('DB_NAME', 'wp_kronda');

/** MySQL database username */
define('DB_USER', 'kronda');

/** MySQL database password */
define('DB_PASSWORD', 'xanderoz');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         ',-r`f@3r9V=nh$:/fk+?u5C}|mzW;jlMW<~x~]p+GA5?l:|yJ|3C#UT R*N%-r6Y');
define('SECURE_AUTH_KEY',  '&npfv6tN+y}M~Px/uX ?t+DZu#wxy$*OWE:dxM(,+-J$&6Rz38[zw6{Q0sE|J_[(');
define('LOGGED_IN_KEY',    '[7A+)yBuAVLrR%S{_F-0+:JWYH?RUO8#ff>F:2Ot>cCO}_yHLG:Yp4fhjk+~`HJ<');
define('NONCE_KEY',        'V*lLS-t|pk#7VWAt?Vp=8+4Zv4{JP[v%aZDF~gVJr+ky8|7.5g4~9UIL|A#eU:T>');
define('AUTH_SALT',        'E|!EO-x+_=O.R^m+jkJ]%~66^`(F-,__g~^FLkim^hO!J.e>@a%_M|~c;CZx>Nec');
define('SECURE_AUTH_SALT', ';c q.S}PcJ5GpHUq[Rl+06bwK]yX(*_Uz gl|8IL&yg6tf6azUM~d0>M#Rd?z+HY');
define('LOGGED_IN_SALT',   '~5Brdta[{v+50I[bvL/@+reu*+;--@:x3|K%yX#YB?AJ:=|>PcUm|#/)M^IG:ba[');
define('NONCE_SALT',       '9MOe7#R01}MJ;p-xLxg87lg@xqD|{3k}{N1Yf]>t7WKyOo*P[na!#tLT|q%=8kX=');

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
define ('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
