<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wepersia_wp' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         '~TPf].L.TpEL[3gij?WmAdc2<SOC:&T]HtJ#4>2B4Lvk_b jR{r^i7%oo0Ez-Bgf' );
define( 'SECURE_AUTH_KEY',  'VASoU<1{V,V6ND_H{!COKpy~^8ybKq%]sUc&9eGH[JK9M*~7daZ2X2ozi/Z6%C=o' );
define( 'LOGGED_IN_KEY',    'e_MBm@cs0UuW>Ku+(20b(kA1@{~-%d]~&RPyQFJoCSr[YcJ=u<Je,n@@qgs.A<TJ' );
define( 'NONCE_KEY',        'a[m=pc#fa~dr()q^)YgH_]%NtLY:yWA!-(nfKw_~wy+9m5yDd&hRm#Gje(58![%{' );
define( 'AUTH_SALT',        'm>#;l.1 c5;ja-J0|vB`d0P);gXTKCQ]chOtV._?Vq2fow;%<,}m{.I>xBdBFPh7' );
define( 'SECURE_AUTH_SALT', '|O#IRsO gmXUP)+qc$raBsachA&M-6=}R0Gn#SVOeF-CR;4u/X/p#De| +zedGu^' );
define( 'LOGGED_IN_SALT',   'Uws|-j([q{w.!THKhe?RnbqMT8p/}5^*QOhz{=NGsn=13d+wDnzmED[7W$C#zOX%' );
define( 'NONCE_SALT',       '5MZ{g5+:V0>a6$*GMgTjihFVAz,bj?_H!]2xX:4%%K%triJV(zCD.lQD0ptoSLV}' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
