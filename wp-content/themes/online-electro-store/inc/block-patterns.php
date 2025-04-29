<?php
/**
 * Block Patterns
 *
 * @package online_electro_store
 * @since 1.0
 */

function online_electro_store_register_block_patterns() {
	$online_electro_store_block_pattern_categories = array(
		'online-electro-store' => array( 'label' => esc_html__( 'Online Electro Store', 'online-electro-store' ) ),
		'pages' => array( 'label' => esc_html__( 'Pages', 'online-electro-store' ) ),
	);

	$online_electro_store_block_pattern_categories = apply_filters( 'online_electro_store_online_electro_store_block_pattern_categories', $online_electro_store_block_pattern_categories );

	foreach ( $online_electro_store_block_pattern_categories as $name => $properties ) {
		if ( ! WP_Block_Pattern_Categories_Registry::get_instance()->is_registered( $name ) ) {
			register_block_pattern_category( $name, $properties );
		}
	}
}
add_action( 'init', 'online_electro_store_register_block_patterns', 9 );