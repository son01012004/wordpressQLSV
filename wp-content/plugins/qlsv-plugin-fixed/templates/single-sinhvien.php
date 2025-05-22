<?php
/**
 * Template hiển thị thông tin chi tiết của một sinh viên
 *
 * @package QLSV
 */

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <?php
        if (is_user_logged_in()) {
            // Hiển thị thông tin người dùng theo vai trò
            echo do_shortcode('[qlsv_user_profile]');
        } else {
            // Hiển thị form đăng nhập nếu chưa đăng nhập
            ?>
            <header class="page-header">
                <h1 class="page-title"><?php esc_html_e('Thông tin sinh viên', 'qlsv'); ?></h1>
            </header>
            <div class="qlsv-login-message">
                <p><?php esc_html_e('Vui lòng đăng nhập để xem thông tin sinh viên.', 'qlsv'); ?></p>
                <p><a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="button button-primary"><?php esc_html_e('Đăng nhập', 'qlsv'); ?></a></p>
            </div>
            <?php
        }
        ?>
    </main>
</div>

<style>
    .qlsv-login-message {
        background: #f8f8f8;
        border-left: 4px solid #0073aa;
        padding: 20px;
        margin: 20px 0;
    }
    .qlsv-login-message p {
        margin: 0 0 15px;
    }
    .qlsv-login-message p:last-child {
        margin-bottom: 0;
    }
    .button-primary {
        background: #0073aa;
        color: #fff;
        text-decoration: none;
        padding: 8px 15px;
        border-radius: 4px;
        display: inline-block;
    }
    .button-primary:hover {
        background: #005177;
        color: #fff;
    }
</style>

<?php
get_sidebar();
get_footer();
?> 