<?php
/**
 * Recommended plugins.
 */
	
require get_template_directory() . '/inc/tgm/class-tgm-plugin-activation.php';

function online_electro_store_register_recommended_plugins() {
	$plugins = array(
		array(
			'name'             => __( 'WooCommerce', 'online-electro-store' ),
			'slug'             => 'woocommerce',
			'required'         => false,
			'force_activation' => false,
		)
	);
	$config = array();
	online_electro_store_tgmpa( $plugins, $config );
}
add_action( 'tgmpa_register', 'online_electro_store_register_recommended_plugins' );