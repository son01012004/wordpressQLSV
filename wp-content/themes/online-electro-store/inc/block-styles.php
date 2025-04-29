<?php
/**
 * Block Styles
 *
 * @package online_electro_store
 * @since 1.0
 */

if ( function_exists( 'register_block_style' ) ) {
	function online_electro_store_register_block_styles() {

		//Wp Block Padding Zero
		register_block_style(
			'core/group',
			array(
				'name'  => 'online-electro-store-padding-0',
				'label' => esc_html__( 'No Padding', 'online-electro-store' ),
			)
		);

		//Wp Block Post Author Style
		register_block_style(
			'core/post-author',
			array(
				'name'  => 'online-electro-store-post-author-card',
				'label' => esc_html__( 'Theme Style', 'online-electro-store' ),
			)
		);

		//Wp Block Button Style
		register_block_style(
			'core/button',
			array(
				'name'         => 'online-electro-store-button',
				'label'        => esc_html__( 'Plain', 'online-electro-store' ),
			)
		);

		//Post Comments Style
		register_block_style(
			'core/post-comments',
			array(
				'name'         => 'online-electro-store-post-comments',
				'label'        => esc_html__( 'Theme Style', 'online-electro-store' ),
			)
		);

		//Latest Comments Style
		register_block_style(
			'core/latest-comments',
			array(
				'name'         => 'online-electro-store-latest-comments',
				'label'        => esc_html__( 'Theme Style', 'online-electro-store' ),
			)
		);


		//Wp Block Table Style
		register_block_style(
			'core/table',
			array(
				'name'         => 'online-electro-store-wp-table',
				'label'        => esc_html__( 'Theme Style', 'online-electro-store' ),
			)
		);


		//Wp Block Pre Style
		register_block_style(
			'core/preformatted',
			array(
				'name'         => 'online-electro-store-wp-preformatted',
				'label'        => esc_html__( 'Theme Style', 'online-electro-store' ),
			)
		);

		//Wp Block Verse Style
		register_block_style(
			'core/verse',
			array(
				'name'         => 'online-electro-store-wp-verse',
				'label'        => esc_html__( 'Theme Style', 'online-electro-store' ),
			)
		);
	}
	add_action( 'init', 'online_electro_store_register_block_styles' );
}
