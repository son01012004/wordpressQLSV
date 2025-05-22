<?php
/**
 * Template hiển thị thông tin chi tiết của một sinh viên
 *
 * @package QLSV
 */

// Ngăn chặn truy cập trực tiếp
if (!defined('WPINC')) {
    die;
}

get_header();

// Kiểm tra quyền truy cập
if (!is_user_logged_in()) {
    ?>
    <div class="qlsv-container">
        <div class="qlsv-thong-bao">
            <p><?php esc_html_e('Bạn cần đăng nhập để xem thông tin sinh viên.', 'qlsv'); ?></p>
            <p><a href="<?php echo esc_url(wp_login_url('http://localhost/wordpressQLSV/')); ?>" class="button button-primary"><?php esc_html_e('Đăng nhập', 'qlsv'); ?></a></p>
        </div>
    </div>
    <?php
    get_footer();
    return;
}
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <header class="page-header">
            <h1 class="page-title"><?php esc_html_e('Thông tin sinh viên', 'qlsv'); ?></h1>
        </header>
        
        <div class="page-content">
        <?php
            // Hiển thị thông tin người dùng theo vai trò
            echo do_shortcode('[qlsv_user_profile]');
            ?>
            </div>
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
    
    /* Đảm bảo full layout với header và footer */
    body.single-sinhvien {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }
    
    body.single-sinhvien #content,
    body.single-sinhvien main,
    body.single-sinhvien .rt-container-fluid,
    body.single-sinhvien .rt-main {
        flex: 1;
    }
    
    body.single-sinhvien header,
    body.single-sinhvien footer {
        flex-shrink: 0;
        display: block !important;
        visibility: visible !important;
    }
    
    /* Đảm bảo profile container nằm đúng vị trí */
    .thong-tin-sinh-vien-container {
        margin: 20px auto !important;
        max-width: 800px !important;
    }
    
    .qlsv-thong-bao {
        padding: 20px !important;
        background: #f8f8f8 !important;
        border-left: 4px solid #ccc !important;
        margin-bottom: 20px !important;
    }
    
    .qlsv-container {
        max-width: 1200px !important;
        margin: 0 auto !important;
        padding: 20px !important;
    }
</style>

<?php
get_sidebar();
get_footer();
?> 