<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Rishi
*/
use \Rishi\Customizer\Helpers\Defaults as Defaults;
use Rishi\Customizer\Helpers\Basic as Helpers;

$defaults           = Defaults::get_layout_defaults();
$search_structure   = get_theme_mod( 'search_post_structure', Defaults::blogpost_structure_defaults() );
$search_page_layout = get_theme_mod( 'search_page_layout', $defaults['search_page_layout'] );

$itemprop = ' itemprop="text"';
$position         = 'First';
$divider_position = 'First';
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); echo rishi_print_schema('article'); ?>>
	<div class="blog-post-lay">
        <div class="post-content">
			<div class="entry-content-main-wrap">
				<?php
					if( $search_page_layout == 'listing' ){
						foreach( $search_structure as $structure ){
							$img_scale = array_key_exists('featured_image_scale', $structure) ? $structure['featured_image_scale'] : '';
							if( $structure['enabled'] == true && $structure['id'] == 'featured_image' && ( Helpers::get_meta( get_the_ID(), 'disable_featured_image', 'no' ) === 'no' ) ){
								echo rishi_single_featured_image( $structure['featured_image_ratio'],$img_scale, $structure['featured_image_size'], $structure['featured_image_visibility'] );
							}
						} ?>
						<div class="list-cont-wrap">
							<?php
								foreach( $search_structure as $structure ){
									if( $structure['enabled'] == true && $structure['id'] == 'custom_meta' ){
										rishi_post_meta( $structure, $position );
										$position = 'Second';
									}

									if( $structure['enabled'] == true && $structure['id'] == 'categories' ){
										rishi_category( $structure['separator'] );
									}

									if( $structure['enabled'] == true && $structure['id'] == 'custom_title' ){
											echo '<' . $structure["heading_tag"] . ' class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">';
												the_title();
											echo '</a>';
											echo '</' . $structure["heading_tag"] . '>';
									}

									if( $structure['enabled'] == true && $structure['id'] == 'excerpt'  ){
										if ( ( has_excerpt() && $structure['post_content'] === 'excerpt' ) || get_the_content() ){ ?>
											<div class="entry-content-wrap clear"<?php echo $itemprop; ?>>
												<?php
													if( $structure['post_content'] === 'excerpt' ){
														the_excerpt();
													}else{
														the_content(
															sprintf(
																wp_kses(
																	/* translators: %s: Name of current post. Only visible to screen readers */
																	__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'rishi' ),
																	array(
																		'span' => array(
																			'class' => array(),
																		),
																	)
																),
																wp_kses_post( get_the_title() )
															)
														);

														wp_link_pages(
															array(
																'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'rishi' ),
																'after'  => '</div>',
															)
														);
													}
												?>
											</div><!-- .entry-content -->
											<?php
										}
									}

									if( $structure['enabled'] == true && $structure['id'] == 'divider'  ){
										echo '<span class="blank-space" data-position="' . esc_attr( $divider_position ) . '"></span>';
                                        $divider_position = 'Second';
									}

									if( $structure['enabled'] == true && $structure['id'] == 'read_more' ){
										rishi_entry_footer( $structure );
									}
								}
							?>
						</div><!-- .list-cont-wrap -->
						<?php
					}else{
						foreach( $search_structure as $structure ){
							$img_scale = array_key_exists('featured_image_scale', $structure) ? $structure['featured_image_scale'] : '';
							if( $structure['enabled'] == true && $structure['id'] == 'featured_image' ){
								echo rishi_single_featured_image( $structure['featured_image_ratio'],$img_scale, $structure['featured_image_size'], $structure['featured_image_visibility'] );
							}

							if( $structure['enabled'] == true && $structure['id'] == 'custom_meta' ){
								rishi_post_meta( $structure, $position );
								$position = 'Second';
							}

							if( $structure['enabled'] == true && $structure['id'] == 'categories' ){
								rishi_category( $structure['separator'] );
							}
							
							if( $structure['enabled'] == true && $structure['id'] == 'custom_title' ){
								echo '<' . $structure["heading_tag"] . ' class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">';
									the_title();
								echo '</a>';
								echo '</' . $structure["heading_tag"] . '>';
							}

							if( $structure['enabled'] == true && $structure['id'] == 'excerpt'  ){
								if ( ( has_excerpt() && $structure['post_content'] === 'excerpt' ) || get_the_content() ){ ?>
									<div class="entry-content-wrap clear"<?php echo $itemprop; ?>>
										<?php
											if( $structure['post_content'] === 'excerpt' ){
												the_excerpt();
											}else{
												the_content(
													sprintf(
														wp_kses(
															/* translators: %s: Name of current post. Only visible to screen readers */
															__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'rishi' ),
															array(
																'span' => array(
																	'class' => array(),
																),
															)
														),
														wp_kses_post( get_the_title() )
													)
												);

												wp_link_pages(
													array(
														'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'rishi' ),
														'after'  => '</div>',
													)
												);
											}
										?>
									</div><!-- .entry-content -->
									<?php
								}
							}

							if( $structure['enabled'] == true && $structure['id'] == 'divider'  ){
								echo '<span class="blank-space" data-position="' . esc_attr( $divider_position ) . '"></span>';
                                $divider_position = 'Second';
							}

							if( $structure['enabled'] == true && $structure['id'] == 'read_more' ){
								rishi_entry_footer( $structure );
							}
						}
					}
				?>
			</div><!-- .entry-content-main-wrap -->
		</div><!-- .post-content -->
	</div><!-- .blog-post-lay -->
</article><!-- #post-## search-->
