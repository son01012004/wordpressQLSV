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
</style>

<?php get_footer(); ?> 