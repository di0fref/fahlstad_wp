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
define('DB_NAME', 's6411_wp');

/** MySQL database username */
define('DB_USER', 's6411');

/** MySQL database password */
define('DB_PASSWORD', 'Tfqnm');

/** MySQL hostname */
define('DB_HOST', 'sql.i8t.com');

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
define('AUTH_KEY',         'f@+zP*n$hTUruV1+CN5(/mGD$U9&3U^:d|Tl]-^|tt1  X~Tc+QyD8X.lJL1j/l?');
define('SECURE_AUTH_KEY',  '[/7 e#4+f|BdrM(UvcdI!t)|C_8v|7}hJ>N/JKb*g;M4Pac)?@I6).qG<.N%*>)-');
define('LOGGED_IN_KEY',    'l#=nhc4xU,T|w?S#)r+)ozTzj9[DypaPw5KROD|gnbd2VOq4S<Noz]_R~+s&n]_O');
define('NONCE_KEY',        'G16<G(N6]6|8)q&dm-KuP,2n2&;3W$wp. B/^aD h:5~dw5hCH#cl.(>nz&&L>CD');
define('AUTH_SALT',        'y;XM1LS*yRyD@wlL&3`%7)R:AU@[2v*(qb#,gEy@)9~?=J+Kd}JvOR4~Z=OK#]|z');
define('SECURE_AUTH_SALT', '-MvaqV|VW0qoK)kqMy%;N!AWz6.#p;5._KG-v5[~j/Lo+Q9+S#KJZqktEE/%0o:s');
define('LOGGED_IN_SALT',   'F926z.ufcpPhjhWDnl)xt&]ZsM>#+f.PT)LC7?M30dO)ADFH590<PGQR3-{!*C|[');
define('NONCE_SALT',       'OH?X}F[$.5|Jc)=@_.])C&;@!Pe:1I=u`#~-`/tqH3T=PhQBS)FG^9;h:O`:d(de');

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
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

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
