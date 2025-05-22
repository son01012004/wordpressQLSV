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

<style>
    /* Định dạng thông báo đăng nhập */
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
    
    /* Đảm bảo full layout với header và footer */
    body.post-type-archive-sinhvien {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }
    
    body.post-type-archive-sinhvien #content,
    body.post-type-archive-sinhvien main,
    body.post-type-archive-sinhvien .rt-container-fluid,
    body.post-type-archive-sinhvien .rt-main {
        flex: 1;
    }
    
    body.post-type-archive-sinhvien header,
    body.post-type-archive-sinhvien footer {
        flex-shrink: 0;
        display: block !important;
        visibility: visible !important;
    }
    
    /* Đảm bảo profile container nằm đúng vị trí */
    .thong-tin-sinh-vien-container {
        margin: 20px auto !important;
        max-width: 800px !important;
    }
</style>

<?php
get_sidebar();
get_footer();
?> 