<?php
/**
 * Template hiển thị danh sách thời khóa biểu
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
            <p><?php esc_html_e('Bạn cần đăng nhập để xem thời khóa biểu.', 'qlsv'); ?></p>
            <p><a href="<?php echo esc_url(wp_login_url('http://localhost/wordpressQLSV/')); ?>" class="button"><?php esc_html_e('Đăng nhập', 'qlsv'); ?></a></p>
        </div>
    </div>
    <?php
    get_footer();
    return;
}
?>

<div class="qlsv-container">
    <h1 class="qlsv-archive-title"><?php _e('Thời khóa biểu', 'qlsv'); ?></h1>
    
    <div class="qlsv-tkb-archive-content">
        <?php echo do_shortcode('[qlsv_thoikhoabieu]'); ?>
    </div>
</div>

<style>
    .qlsv-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    }
    .qlsv-archive-title {
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
        color: #333;
    }
    .qlsv-tkb-archive-content {
        background: #fff;
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        padding: 20px;
    }
    .qlsv-thong-bao {
        padding: 20px !important;
        background: #f8f8f8 !important;
        border-left: 4px solid #ccc !important;
        margin-bottom: 20px !important;
    }
</style>

<?php get_footer(); ?> 