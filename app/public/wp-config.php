<?php
define('WP_CACHE', true); // WP-Optimize Cache
/** WP 2FA plugin data encryption key. For more information please visit melapress.com */
define( 'WP2FA_ENCRYPT_KEY', '0Jx//nZr+9tKnjsY1J5DRw==' );
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */
// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'staging_db');
/** Database username */
define( 'DB_USER', 'staging_user');
/** Database password */
define( 'DB_PASSWORD', 'd/ER!qmbQ%S+Y?wh#.<f*5Wr@u8C}HN]' );
/** Database hostname */
define( 'DB_HOST', 'localhost' );
/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );
/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );
/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          '=KJ%7w#GT>C`Ul+ExbQPk2I[+:B{[{ d/&:&/N%7`2*z|5XV:cLGxwfs^XPzA~0@' );
define( 'SECURE_AUTH_KEY',   'jk+a7`s/&}vLBXhP%9e^;F[J#EZ9,!4T[gBRjtw$^w&^c&d::G+;;qqE?+G^ly2T' );
define( 'LOGGED_IN_KEY',     '5>aYcBHb!iEA>xZNE-1KK_NoEy8=yU:9<S&N4hXph_F9uABB$2xVBpm.pMIWCxo?' );
define( 'NONCE_KEY',         'W(;XVzCii@O6~PWvi+TWGM*1|Nux23~>EqEm4.SJs|_${epzuSF!(fCsMAkqDae(' );
define( 'AUTH_SALT',         'aoHsQUSpd4f|]fOe+|:ipjvUcbjX2#GY%h|)U`&4`XPL=JDlYm]v DX]8o6&X0Gb' );
define( 'SECURE_AUTH_SALT',  'UWSnbRjMXc0VX*QpjEoEaI4G& _GJ;c`_<!Xdtr3xoSnhpGaG34WzqcT:a}Sqr&}' );
define( 'LOGGED_IN_SALT',    '_(eko}Q2Go,Jv.<X<ZQ-Y+;9_48jpzJRB*iqbhy,RN9=CrgA&kT<9`q{Pjb3T6tT' );
define( 'NONCE_SALT',        'nfP~Qk{w@/3FC=yj~`9T]YQ|s$dVqu)4>c%Nw9=>KqT*!%z~feau3R1Bh$y!)NjW' );
define( 'WP_CACHE_KEY_SALT', '?(}Zx2-PjpLn:0t-x4W=LA.nhXh-ZU QPW6L)w 6XPC~jZ]i2Ao?^_dWdL!I/fW:' );
/**#@-*/
/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';
/* Add any custom values between this line and the "stop editing" line. */
/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}
define( 'MO_SAML_LOGGING', false );
/* That's all, stop editing! Happy publishing. */
/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';