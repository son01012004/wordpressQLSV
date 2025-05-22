<?php
/**
 * Template hiển thị archive của sinh viên
 *
 * @package QLSV
 */

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <header class="page-header">
            <h1 class="page-title"><?php esc_html_e('Thông tin sinh viên', 'qlsv'); ?></h1>
        </header>
        
        <div class="page-content">
            <?php 
            if (is_user_logged_in()) {
                // Hiển thị thông tin người dùng theo vai trò
                echo do_shortcode('[qlsv_user_profile]');
            } else {
                // Hiển thị form đăng nhập nếu chưa đăng nhập
                ?>
                <div class="qlsv-login-message">
                    <p><?php esc_html_e('Vui lòng đăng nhập để xem thông tin sinh viên.', 'qlsv'); ?></p>
                    <p><a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="button button-primary"><?php esc_html_e('Đăng nhập', 'qlsv'); ?></a></p>
                </div>
                <?php
            }
            ?>
        </div>
    </main>
</div>

<?php
get_sidebar();
get_footer();
?> 