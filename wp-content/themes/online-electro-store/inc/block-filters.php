<?php
/**
 * Block Filters
 *
 * @package online_electro_store
 * @since 1.0
 */

function online_electro_store_block_wrapper( $online_electro_store_block_content, $online_electro_store_block ) {

	if ( 'core/button' === $online_electro_store_block['blockName'] ) {
		
		if( isset( $online_electro_store_block['attrs']['className'] ) && strpos( $online_electro_store_block['attrs']['className'], 'has-arrow' ) ) {
			$online_electro_store_block_content = str_replace( '</a>', online_electro_store_get_svg( array( 'icon' => esc_attr( 'caret-circle-right' ) ) ) . '</a>', $online_electro_store_block_content );
			return $online_electro_store_block_content;
		}
	}

	if( ! is_single() ) {
	
		if ( 'core/post-terms'  === $online_electro_store_block['blockName'] ) {
			if( 'post_tag' === $online_electro_store_block['attrs']['term'] ) {
				$online_electro_store_block_content = str_replace( '<div class="taxonomy-post_tag wp-block-post-terms">', '<div class="taxonomy-post_tag wp-block-post-terms flex">' . online_electro_store_get_svg( array( 'icon' => esc_attr( 'tags' ) ) ), $online_electro_store_block_content );
			}

			if( 'category' ===  $online_electro_store_block['attrs']['term'] ) {
				$online_electro_store_block_content = str_replace( '<div class="taxonomy-category wp-block-post-terms">', '<div class="taxonomy-category wp-block-post-terms flex">' . online_electro_store_get_svg( array( 'icon' => esc_attr( 'category' ) ) ), $online_electro_store_block_content );
			}
			return $online_electro_store_block_content;
		}
		if ( 'core/post-date' === $online_electro_store_block['blockName'] ) {
			$online_electro_store_block_content = str_replace( '<div class="wp-block-post-date">', '<div class="wp-block-post-date flex">' . online_electro_store_get_svg( array( 'icon' => esc_attr( 'calendar' ) ) ), $online_electro_store_block_content );
			return $online_electro_store_block_content;
		}
		if ( 'core/post-author' === $online_electro_store_block['blockName'] ) {
			$online_electro_store_block_content = str_replace( '<div class="wp-block-post-author">', '<div class="wp-block-post-author flex">' . online_electro_store_get_svg( array( 'icon' => esc_attr( 'user' ) ) ), $online_electro_store_block_content );
			return $online_electro_store_block_content;
		}
	}
	if( is_single() ){

		// Add chevron icon to the navigations
		if ( 'core/post-navigation-link' === $online_electro_store_block['blockName'] ) {
			if( isset( $online_electro_store_block['attrs']['type'] ) && 'previous' === $online_electro_store_block['attrs']['type'] ) {
				$online_electro_store_block_content = str_replace( '<span class="post-navigation-link__label">', '<span class="post-navigation-link__label">' . online_electro_store_get_svg( array( 'icon' => esc_attr( 'prev' ) ) ), $online_electro_store_block_content );
			}
			else {
				$online_electro_store_block_content = str_replace( '<span class="post-navigation-link__label">Next Post', '<span class="post-navigation-link__label">Next Post' . online_electro_store_get_svg( array( 'icon' => esc_attr( 'next' ) ) ), $online_electro_store_block_content );
			}
			return $online_electro_store_block_content;
		}
		if ( 'core/post-date' === $online_electro_store_block['blockName'] ) {
            $online_electro_store_block_content = str_replace( '<div class="wp-block-post-date">', '<div class="wp-block-post-date flex">' . online_electro_store_get_svg( array( 'icon' => 'calendar' ) ), $online_electro_store_block_content );
            return $online_electro_store_block_content;
        }
		if ( 'core/post-author' === $online_electro_store_block['blockName'] ) {
            $online_electro_store_block_content = str_replace( '<div class="wp-block-post-author">', '<div class="wp-block-post-author flex">' . online_electro_store_get_svg( array( 'icon' => 'user' ) ), $online_electro_store_block_content );
            return $online_electro_store_block_content;
        }

	}
    return $online_electro_store_block_content;
}
	
add_filter( 'render_block', 'online_electro_store_block_wrapper', 10, 2 );
