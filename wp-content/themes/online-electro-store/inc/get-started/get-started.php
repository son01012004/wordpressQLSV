<?php
add_action( 'admin_menu', 'online_electro_store_getting_started' );
function online_electro_store_getting_started() {
	add_theme_page( esc_html__('Get Started', 'online-electro-store'), esc_html__('Get Started', 'online-electro-store'), 'edit_theme_options', 'online-electro-store-guide-page', 'online_electro_store_test_guide');
}

// Add a Custom CSS file to WP Admin Area
function online_electro_store_admin_theme_style() {
   wp_enqueue_style('custom-admin-style', esc_url(get_template_directory_uri()) . '/inc/get-started/get-started.css');
}
add_action('admin_enqueue_scripts', 'online_electro_store_admin_theme_style');

//guidline for about theme
function online_electro_store_test_guide() { 
	//custom function about theme customizer
	$return = add_query_arg( array()) ;
	$theme = wp_get_theme( 'online-electro-store' );
?>
	<div class="wrapper-outer">
		<div class="left-main-box">
			<div class="intro"><h3><?php echo esc_html( $theme->Name ); ?></h3></div>
			<div class="left-inner">
				<div class="about-wrapper">
					<div class="col-left">
						<p><?php echo esc_html( $theme->get( 'Description' ) ); ?></p>
					</div>
					<div class="col-right">
						<img role="img" src="<?php echo esc_url(get_template_directory_uri()); ?>/inc/get-started/images/screenshot.png" alt="" />
					</div>
				</div>
				<div class="link-wrapper">
					<h4><?php esc_html_e('Important Links', 'online-electro-store'); ?></h4>
					<div class="link-buttons">
						<a href="<?php echo esc_url( ONLINE_ELECTRO_STORE_THEME_DOC ); ?>" target="_blank"><?php esc_html_e('Free Setup Guide', 'online-electro-store'); ?></a>
						<a href="<?php echo esc_url( ONLINE_ELECTRO_STORE_SUPPORT ); ?>" target="_blank"><?php esc_html_e('Support Forum', 'online-electro-store'); ?></a>
						<a href="<?php echo esc_url( ONLINE_ELECTRO_STORE_PRO_DEMO ); ?>" target="_blank"><?php esc_html_e('Live Demo', 'online-electro-store'); ?></a>
						<a href="<?php echo esc_url( ONLINE_ELECTRO_STORE_PRO_THEME_DOC ); ?>" target="_blank"><?php esc_html_e('Pro Setup Guide', 'online-electro-store'); ?></a>
					</div>
				</div>
				<div class="support-wrapper">
					<div class="editor-box">
						<i class="dashicons dashicons-admin-appearance"></i>
						<h4><?php esc_html_e('Theme Customization', 'online-electro-store'); ?></h4>
						<p><?php esc_html_e('Effortlessly modify & maintain your site using editor.', 'online-electro-store'); ?></p>
						<div class="support-button">
							<a class="button button-primary" href="<?php echo esc_url( admin_url( 'site-editor.php' ) ); ?>" target="_blank"><?php esc_html_e('Site Editor', 'online-electro-store'); ?></a>
						</div>
					</div>
					<div class="support-box">
						<i class="dashicons dashicons-microphone"></i>
						<h4><?php esc_html_e('Need Support?', 'online-electro-store'); ?></h4>
						<p><?php esc_html_e('Go to our support forum to help you in case of queries.', 'online-electro-store'); ?></p>
						<div class="support-button">
							<a class="button button-primary" href="<?php echo esc_url( ONLINE_ELECTRO_STORE_SUPPORT ); ?>" target="_blank"><?php esc_html_e('Get Support', 'online-electro-store'); ?></a>
						</div>
					</div>
					<div class="review-box">
						<i class="dashicons dashicons-star-filled"></i>
						<h4><?php esc_html_e('Leave Us A Review', 'online-electro-store'); ?></h4>
						<p><?php esc_html_e('Are you enjoying Our Theme? We would Love to hear your Feedback.', 'online-electro-store'); ?></p>
						<div class="support-button">
							<a class="button button-primary" href="<?php echo esc_url( ONLINE_ELECTRO_STORE_REVIEW ); ?>" target="_blank"><?php esc_html_e('Rate Us', 'online-electro-store'); ?></a>
						</div>
					</div>
				</div>
			</div>
			<div class="go-premium-box">
				<h4><?php esc_html_e('Why Go For Premium?', 'online-electro-store'); ?></h4>
				<ul class="pro-list">
					<li><?php esc_html_e('Advanced Customization Options', 'online-electro-store');?></li>
					<li><?php esc_html_e('One-Click Demo Import', 'online-electro-store');?></li>
					<li><?php esc_html_e('WooCommerce Integration & Enhanced Features', 'online-electro-store');?></li>
					<li><?php esc_html_e('Performance Optimization & SEO-Ready', 'online-electro-store');?></li>
					<li><?php esc_html_e('Premium Support & Regular Updates', 'online-electro-store');?></li>
				</ul>
			</div>
		</div>
		<div class="right-main-box">
			<div class="right-inner">
				<div class="pro-boxes">
					<h4><?php esc_html_e('Get Theme Bundle', 'online-electro-store'); ?></h4>
					<img role="img" src="<?php echo esc_url(get_template_directory_uri()); ?>/inc/get-started/images/bundle.png" alt="bundle image" />
					<p><?php esc_html_e('SUMMER SALE: ', 'online-electro-store'); ?><strong><?php esc_html_e('Extra 20%', 'online-electro-store'); ?></strong><?php esc_html_e(' OFF on WordPress Theme Bundle Use Code: ', 'online-electro-store'); ?><strong><?php esc_html_e('“HEAT20”', 'online-electro-store'); ?></strong></p>
					<a href="<?php echo esc_url( ONLINE_ELECTRO_STORE_PRO_THEME_BUNDLE ); ?>" target="_blank"><?php esc_html_e('Get Theme Bundle For $86', 'online-electro-store'); ?></a>
				</div>
				<div class="pro-boxes">
					<h4><?php esc_html_e('Online Electro Store Pro', 'online-electro-store'); ?></h4>
					<img role="img" src="<?php echo esc_url(get_template_directory_uri()); ?>/inc/get-started/images/premium.png" alt="premium image" />
					<p><?php esc_html_e('SUMMER SALE: ', 'online-electro-store'); ?><strong><?php esc_html_e('Extra 25%', 'online-electro-store'); ?></strong><?php esc_html_e(' OFF on WordPress Block Themes! Use Code: ', 'online-electro-store'); ?><strong><?php esc_html_e('“SUMMER25”', 'online-electro-store'); ?></strong></p>
					<a href="<?php echo esc_url( ONLINE_ELECTRO_STORE_BUY_NOW ); ?>" target="_blank"><?php esc_html_e('Upgrade To Pro', 'online-electro-store'); ?></a>
				</div>
				<div class="pro-boxes last-pro-box">
					<h4><?php esc_html_e('View All Our Themes', 'online-electro-store'); ?></h4>
					<img role="img" src="<?php echo esc_url(get_template_directory_uri()); ?>/inc/get-started/images/all-themes.png" alt="all themes image" />
					<a href="<?php echo esc_url( ONLINE_ELECTRO_STORE_PRO_ALL_THEMES ); ?>" target="_blank"><?php esc_html_e('View All Our Premium Themes', 'online-electro-store'); ?></a>
				</div>
			</div>
		</div>
	</div>
<?php } ?>