<?php
/**
 * Rishi Custom Functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Rishi
 */

$theme_data = wp_get_theme();
if( ! defined( 'RISHI_VERSION' ) ) define( 'RISHI_VERSION', $theme_data->get( 'Version' ) );
if( ! defined( 'RISHI_NAME' ) ) define( 'RISHI_NAME', $theme_data->get( 'Name' ) );
if( ! defined( 'RISHI_TEXTDOMAIN' ) ) define( 'RISHI_TEXTDOMAIN', $theme_data->get( 'TextDomain' ) );   


// Customizer Builder directory.
defined( 'THEME_CUSTOMIZER_BUILDER_DIR_' ) || define( 'THEME_CUSTOMIZER_BUILDER_DIR_', get_template_directory() . '/customizer' );

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Google Fonts.
 */
require get_template_directory() . '/inc/typography/google-fonts.php';

/**
 * Custom Functions for the theme
 */
require get_template_directory() . '/inc/custom-functions.php';

/**
 * Extras Code
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}
/**
 * Customizer Init Files
 */
require get_template_directory() . '/customizer/init.php';

/**
 * Dynamic Editor Styles
 */
require get_template_directory() . '/inc/editor.php';

/**
 * Elementor Compatibility for the theme
 */
if ( rishi_is_elementor_activated() ) require get_template_directory() . '/inc/elementor-compatibility.php';

/**
 * Load WooCommerce compatibility file.
 */
if ( class_exists( 'WooCommerce' ) ) require get_template_directory() . '/inc/woocommerce.php';
/**
 * Load google fonts locally
 */
require get_template_directory() . '/inc/class-webfont-loader.php';

/** 
* Custom Dashboard Functions here
*/
require get_template_directory() . '/inc/classes/class-dashboard.php';

/**
 * Schema Markup here
 */
require get_template_directory() . '/inc/classes/class-microdata.php';

/**
 * Theme Updater
*/
require get_template_directory() . '/updater/theme-updater.php';

/**
 * Static CSS 
 *
 * Requires all the path of static_css folder
 *
 * @since 1.0.0
 */
foreach ( glob( get_template_directory() . '/inc/assets/css/static_css/*.php' ) as $file ) {
    require $file;
}

/**
 * Notices
 */
require get_template_directory() . '/updater/notice.php';