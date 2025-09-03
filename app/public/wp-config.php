<?php
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
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

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
define( 'AUTH_KEY',          'f`rL+WSYtwv$j#Q>][|Kz|J;V-G^?duF&OO<9/>/[-GzHj{!,-P!NmN;Nv3@F@ed' );
define( 'SECURE_AUTH_KEY',   'EOG]57lOsgJk kW?l4p:DcI4;vVg[47BR,iH/v`&]?>UQ+7)5D?rbTtX>j&XwS|{' );
define( 'LOGGED_IN_KEY',     'hIsS5));}#w0ZgMN42yKUk.q@2Iq3$cO-D?%3n5!w-`UcoIw$!<X6ozjjr: -IWU' );
define( 'NONCE_KEY',         'Cpc=l|i)q$Tn7t?CIsqMJ+G0pdWI!YN?lEd;,RQd}[ZMKhr[U~.:RH!ZzjO)W_$d' );
define( 'AUTH_SALT',         '%<<I9DL@~Z&0X1 QD&)GsKDmsIcz4|qFYGV[;<^9~7XqQ}F2V@lPT/UfaEQ6;-}=' );
define( 'SECURE_AUTH_SALT',  'UpV SV5=`zd+v<iMEu2s#f>2?]L6*fruj8JZ-WH5kw)m1!e-|I?[2;<$Qw=Nrj,]' );
define( 'LOGGED_IN_SALT',    '2`GR62_] u8Qvnpb&,O1Wbga4b@nR/eT=xC*Kg}nMpQOS>`Wb/T?H?2a5Q:s]<EC' );
define( 'NONCE_SALT',        'R`Qp29Np)Wp{>[xNzrJ%X.8nA}(MB-65p7jS>6R;DhiR:)@tDBz4Zd+nt[HG8qBS' );
define( 'WP_CACHE_KEY_SALT', 'e-Xr5?fwO3u__Ao47zd&i^Lt`e9$!DPR5*8Ar~rsF@tDuh:BTJ:? M>A^gD8@:$n' );


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

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
