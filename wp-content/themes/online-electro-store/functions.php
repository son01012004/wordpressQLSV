<?php
/**
 * Online Electro Store functions and definitions
 *
 * @package online_electro_store
 * @since 1.0
 */

if ( ! function_exists( 'online_electro_store_support' ) ) :
	function online_electro_store_support() {

		load_theme_textdomain( 'online-electro-store', get_template_directory() . '/languages' );

		// Add support for block styles.
		add_theme_support( 'wp-block-styles' );

		add_theme_support('woocommerce');

		// Enqueue editor styles.
		add_editor_style(get_stylesheet_directory_uri() . '/assets/css/editor-style.css');

		/* Theme Credit link */
		define('ONLINE_ELECTRO_STORE_BUY_NOW',__('https://www.cretathemes.com/products/electro-wordpress-theme','online-electro-store'));
		define('ONLINE_ELECTRO_STORE_PRO_DEMO',__('https://pattern.cretathemes.com/online-electro-store/','online-electro-store'));
		define('ONLINE_ELECTRO_STORE_THEME_DOC',__('https://pattern.cretathemes.com/free-guide/online-electro-store/','online-electro-store'));
		define('ONLINE_ELECTRO_STORE_PRO_THEME_DOC',__('https://pattern.cretathemes.com/pro-guide/online-electro-store/','online-electro-store'));
		define('ONLINE_ELECTRO_STORE_SUPPORT',__('https://wordpress.org/support/theme/online-electro-store','online-electro-store'));
		define('ONLINE_ELECTRO_STORE_REVIEW',__('https://wordpress.org/support/theme/online-electro-store/reviews/#new-post','online-electro-store'));
		define('ONLINE_ELECTRO_STORE_PRO_THEME_BUNDLE',__('https://www.cretathemes.com/products/wordpress-theme-bundle','online-electro-store'));
		define('ONLINE_ELECTRO_STORE_PRO_ALL_THEMES',__('https://www.cretathemes.com/collections/wordpress-block-themes','online-electro-store'));

	}
endif;

add_action( 'after_setup_theme', 'online_electro_store_support' );

if ( ! function_exists( 'online_electro_store_styles' ) ) :
	function online_electro_store_styles() {
		// Register theme stylesheet.
		$theme_version = wp_get_theme()->get( 'Version' );

		$version_string = is_string( $theme_version ) ? $theme_version : false;
		wp_enqueue_style(
			'online-electro-store-style',
			get_template_directory_uri() . '/style.css',
			array(),
			$version_string
		);

		wp_enqueue_script( 'online-electro-store-custom-script', get_theme_file_uri( '/assets/custom-script.js' ), array( 'jquery' ), true );

		wp_enqueue_style( 'dashicons' );

		//font-awesome
		wp_enqueue_style( 'fontawesome', get_template_directory_uri() . '/inc/fontawesome/css/all.css'
			, array(), '6.7.0' );

	}
endif;

add_action( 'wp_enqueue_scripts', 'online_electro_store_styles' );

// Add block patterns
require get_template_directory() . '/inc/block-patterns.php';

// Add block styles
require get_template_directory() . '/inc/block-styles.php';

// Block Filters
require get_template_directory() . '/inc/block-filters.php';

// Svg icons
require get_template_directory() . '/inc/icon-function.php';

// TGM Plugin
require get_template_directory() . '/inc/tgm/tgm.php';

// Customizer
require get_template_directory() . '/inc/customizer.php';

// Get Started.
require get_template_directory() . '/inc/get-started/get-started.php';

// Add Getstart admin notice
function online_electro_store_admin_notice() { 
    global $pagenow;
    $theme_args      = wp_get_theme();
    $meta            = get_option( 'online_electro_store_admin_notice' );
    $name            = $theme_args->__get( 'Name' );
    $current_screen  = get_current_screen();

    if( !$meta ){
	    if( is_network_admin() ){
	        return;
	    }

	    if( ! current_user_can( 'manage_options' ) ){
	        return;
	    } if($current_screen->base != 'appearance_page_online-electro-store-guide-page' ) { ?>

	    <div class="notice notice-success dash-notice">
	        <h1><?php esc_html_e('Hey, Thank you for installing Online Electro Store Theme!', 'online-electro-store'); ?></h1>
	        <p><a class="button button-primary customize load-customize hide-if-no-customize get-start-btn" href="<?php echo esc_url( admin_url( 'themes.php?page=online-electro-store-guide-page' ) ); ?>"><?php esc_html_e('Navigate Getstart', 'online-electro-store'); ?></a> 
	        	<a class="button button-primary site-edit" href="<?php echo esc_url( admin_url( 'site-editor.php' ) ); ?>"><?php esc_html_e('Site Editor', 'online-electro-store'); ?></a> 
				<a class="button button-primary buy-now-btn" href="<?php echo esc_url( ONLINE_ELECTRO_STORE_BUY_NOW ); ?>" target="_blank"><?php esc_html_e('Buy Pro', 'online-electro-store'); ?></a>
				<a class="button button-primary bundle-btn" href="<?php echo esc_url( ONLINE_ELECTRO_STORE_PRO_THEME_BUNDLE ); ?>" target="_blank"><?php esc_html_e('Get Bundle', 'online-electro-store'); ?></a>
	        </p>
	        <p class="dismiss-link"><strong><a href="?online_electro_store_admin_notice=1"><?php esc_html_e( 'Dismiss', 'online-electro-store' ); ?></a></strong></p>
	    </div>
	    <?php }?>
	    <?php
	}
}

add_action( 'admin_notices', 'online_electro_store_admin_notice' );

if( ! function_exists( 'online_electro_store_update_admin_notice' ) ) :
/**
 * Updating admin notice on dismiss
*/
function online_electro_store_update_admin_notice(){
    if ( isset( $_GET['online_electro_store_admin_notice'] ) && $_GET['online_electro_store_admin_notice'] = '1' ) {
        update_option( 'online_electro_store_admin_notice', true );
    }
}
endif;
add_action( 'admin_init', 'online_electro_store_update_admin_notice' );

//After Switch theme function
add_action('after_switch_theme', 'online_electro_store_getstart_setup_options');
function online_electro_store_getstart_setup_options () {
    update_option('online_electro_store_admin_notice', FALSE );
}

function online_electro_store_google_fonts() {
 
	wp_enqueue_style( 'montserrat', 'https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap', false ); 
}
 
add_action( 'wp_enqueue_scripts', 'online_electro_store_google_fonts' );

