<?php
/**
 * Template for the Diemdanh page
 *
 * @package QLSV
 */

get_header();

// Check if user is logged in
if (!is_user_logged_in()) {
    ?>
    <div class="qlsv-container">
        <div class="qlsv-thong-bao">
            <p><?php esc_html_e('Bạn cần đăng nhập để sử dụng tính năng điểm danh.', 'qlsv'); ?></p>
            <p><a href="<?php echo esc_url(wp_login_url('http://localhost/wordpressQLSV/')); ?>" class="button"><?php esc_html_e('Đăng nhập', 'qlsv'); ?></a></p>
        </div>
    </div>
    <?php
    get_footer();
    return;
}

// Get current user info
$current_user = wp_get_current_user();
$user_roles = $current_user->roles;
$is_admin = in_array('administrator', $user_roles);
$is_teacher = in_array('giaovien', $user_roles);
$is_student = in_array('student', $user_roles);

// Check if URL has lop and mon_hoc parameters
$lop_id = isset($_GET['lop']) ? intval($_GET['lop']) : 0;
$mon_hoc_id = isset($_GET['mon_hoc']) ? intval($_GET['mon_hoc']) : 0;

// If we have parameters and user is admin/teacher, show the form
if ($lop_id && $mon_hoc_id && ($is_admin || $is_teacher)) {
    // Include the archive template which has the form handling
    include(QLSV_PLUGIN_DIR . 'templates/archive-diemdanh.php');
    return;
}

// Otherwise show the dashboard
?>
<div class="qlsv-container">
    <h1 class="qlsv-page-title"><?php esc_html_e('Điểm Danh', 'qlsv'); ?></h1>
    
    <?php echo do_shortcode('[qlsv_diemdanh_dashboard]'); ?>
</div>

<style>
    .qlsv-container {
        max-width: 1200px !important;
        margin: 0 auto !important;
        padding: 20px !important;
    }
    
    .qlsv-page-title {
        margin-bottom: 30px !important;
        font-size: 28px !important;
        color: #333 !important;
        border-bottom: 2px solid #f0f0f0 !important;
        padding-bottom: 15px !important;
    }
    
    .qlsv-thong-bao {
        padding: 20px !important;
        background: #f8f8f8 !important;
        border-left: 4px solid #ccc !important;
        margin-bottom: 20px !important;
    }
</style>

<?php get_footer(); ?> 