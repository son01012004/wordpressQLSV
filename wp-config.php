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
define( 'DB_NAME', 'ql_sv' );

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
define( 'AUTH_KEY',         'NJ7u?<X<k?fL9I2{zMt~6[i>|Px|L}9NaG~R7o]f/8yDccO~,a}6{Kw_/OolH; h' );
define( 'SECURE_AUTH_KEY',  'TN}pJ;:.G|oVuuK_j{:R#ekO?@Vao+0~kv2pqqhAZI)p#@N^^FO&bWk Q&T@C/!r' );
define( 'LOGGED_IN_KEY',    ',9sT;#.^N<m;`KEJ:H{;PJw>9+oAQcJ>]%mcR.Ya<4K=I7pGu? =8YY^lb0Ld0C#' );
define( 'NONCE_KEY',        'SQ-NMEdLf7  Q j<ZXDsXa3FzG$OX{`YI<.?rWvTZwO_vqGLU3VM fRyeeBwAPAa' );
define( 'AUTH_SALT',        '{C%&]A$j<$;3s53``i]OWe+KwLVu^4L<Z1[wbxKQ_K;peUuG#`Oy6cZOI]L8Mq3A' );
define( 'SECURE_AUTH_SALT', 'w2+$g6XpC&J%kK_>izQcXFc!*hc9$4N|<?G@e)&=Z~bK^[tNZ/vPvrS8cL6isz]:' );
define( 'LOGGED_IN_SALT',   'RtOp8](V`oX^_DVYv)qNPCgng)XV;!{_GD|9]KX[)!d)6h&0_Q&fH]FMO;=XVETK' );
define( 'NONCE_SALT',       '/3#!nRC&-?(VPXQKT:Cef~!n,{+>$UscdJ8UnYgp[TVikEV/_&Z1a#!0_bN]P+-K' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
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
