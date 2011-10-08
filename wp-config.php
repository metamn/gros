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
define('DB_NAME', 'grosdb');

/** MySQL database username */
define('DB_USER', 'admin');

/** MySQL database password */
define('DB_PASSWORD', 'almafa12');

/** MySQL hostname */
define('DB_HOST', 'gros.crdvk5ayenrb.us-east-1.rds.amazonaws.com');

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
define('AUTH_KEY',         'wR@V}XOw0^_kk*01#)hl}l0F}zB_Bu_+lP1`r;/pb[Kd]ayOtN.rn;{U{Y&XV>}L');
define('SECURE_AUTH_KEY',  'G0ZS%JgvGE<`so*gf%6dW7A~N-.|2g4t^O|$GzQ,:?PXx(O`3y4}3VvZA~i%?f_i');
define('LOGGED_IN_KEY',    'GL5_H0,vL.-TeU<aV&4vV^0<,P9?XMO|tYie$R6|*mYY!l4Dnx$K{>l?kq9Ly~=P');
define('NONCE_KEY',        'mk&R+;*PxQ Z8[:X?J9;mdg(E*Wq);ZCtsn3qAAFNC4~.BtK;S)I< eYu8tm&Z~:');
define('AUTH_SALT',        '3895c_?DJ#z_^QQs42[%4iI<IT;?]GRr~oF@*0!mda,[V(lF;56B8v;<zVs<XrSg');
define('SECURE_AUTH_SALT', 's8>L50 l&<Y0xR*b#@03eT]h(~w b>*n>,W>7|MkmbL=pjgo# 5S;3:q=@2Aod-Y');
define('LOGGED_IN_SALT',   '@/d|-3!}+SbtGzgP8yc_-Z) {wcgYY+]Ga5=e_MV**tNo2_.7+wVBFPFcOtlY5])');
define('NONCE_SALT',       '8gw)W;l(%X4-jD}| p8(9HqdmwwnvMXIsqX~zU<mRZXJe-v 4CC)eF/tr|>e#jO#');


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
