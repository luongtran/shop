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
define('DB_NAME', 'signturn');

/** MySQL database username */
define('DB_USER', 'bravo');

/** MySQL database password */
define('DB_PASSWORD', '123');

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
define('AUTH_KEY',         'y6.yQb604/1-I:W daRAE.I7@s`N=INvJE*RW-GqdyXwG&EHVa+P7JYH@g=hw+qj');
define('SECURE_AUTH_KEY',  '-3nFYT<?:b|&-T)1%mgj`/%uud07OtiA-xl>6|]3ljCC-;+yT1u(K{Y)]E!h7P|y');
define('LOGGED_IN_KEY',    'W+i-(WQU,jfy-,Lk._.AvFEeV$lIQdvpI0ig:o8p+2AUv[}eMcksy=%3&ScNd= p');
define('NONCE_KEY',        'i&/*$6)uW|7A@z?eiOkQNTI;MDh37q)|K{dSz=;]Milj2nV9NF+[-j1>[~NK!LKi');
define('AUTH_SALT',        '3({lnKAPgT}m36MG= Q*3-G]*D)(G6hD%-EV)N9<aTK8y!%J|#5qu2=n95vGgbKE');
define('SECURE_AUTH_SALT', 'H9+JD[&&^Int^*;XKnhQjCTlEIXIF-H2 }SlLNg%,{VkpDeAC@t]D_Ai.la_RNK-');
define('LOGGED_IN_SALT',   '+W<W%Dm2Ar&VwKQ{[K~r{dd])uu_u5`]O--ZQ*-%,,ynQDvK7x#ezP/_/Jbp&{x8');
define('NONCE_SALT',       'K_,?6{d@Gq5S|F*eI= ldL,okHWQj.ZJ(m%2GdB=dUr-$&)1k/jktBu0izB_db4|');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);
// Turns WordPress debugging on
//define('WP_DEBUG', true);
//
//// Tells WordPress to log everything to the /wp-content/debug.log file
//define('WP_DEBUG_LOG', true);
//
//// Doesn't force the PHP 'display_errors' variable to be on
//define('WP_DEBUG_DISPLAY', false);
//
//// Hides errors from being displayed on-screen
//@ini_set('display_errors', 0);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
