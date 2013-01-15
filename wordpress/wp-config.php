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


/** 
*
* YOU SHOULD BE EDITING local-config.php !!!!!
*
* DON'T SET ENVIRONMENT SPECIFC SETTINGS IN THIS FILE, THEY BELONG IN local-config.php 
*
**/

if ( file_exists( dirname( __FILE__ ) . '/local-config.php' ) ) {
    include( dirname( __FILE__ ) . '/local-config.php' );
} else {
  // ** MySQL settings - You can get this info from your web host ** //
  /** The name of the database for WordPress */
define('DB_NAME', 'database_name_here');

/** MySQL database username */
define('DB_USER', 'username_here');

/** MySQL database password */
define('DB_PASSWORD', 'password_here');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');
}

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '9~M`<DG>6p.%{FjTY>Y+TQVI3CDprps{v!C)zR|O&[6{$xG^~{H|`FL@eN:Db<EC');
define('SECURE_AUTH_KEY',  'fB>`>M*0`,1LJouvb)3;c]62%[b{Y,WA-~J6R30a|}+@2TQ>n5-M%D-Qf]kRO+;C');
define('LOGGED_IN_KEY',    'v9+a/6mMg,`RBZnP $Qx^D|y+J-Hh,%e(K0Rlfe`5nZ*>4aV{{#-`+^x|2oh]C}T');
define('NONCE_KEY',        '.z+jNc-{ls:IXdRdx{l3_F-%(G<#8XzndeJh5`@~C>FW39luH0-#p=]_AO;v+HA|');
define('AUTH_SALT',        'ekTxTl%5>dw~%_-R26^`(Hu>d{|?Q!20iG=1s[>:hW[iV/RwH+B-<Ruv54t<l~^D');
define('SECURE_AUTH_SALT', 'pf5010s9!eGMg4;@tKLOIq4Y;y|Ps~<~m|@E@Iy[c,Th-|THB{YD~=+jEx}|@Nth');
define('LOGGED_IN_SALT',   '>1Kg-20hR0h(?UrOJq+D,|*%D1aw=Z+:&^X|ZYdBUOja]`Bj}o3@]kiOa~te}?,w');
define('NONCE_SALT',       '^w;@f:yfOFX^yjeg3[YkwYf`cHnG;9 O-2m=KF{msKOJs^W{|j b%|T@>(dDR7Q-');


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
