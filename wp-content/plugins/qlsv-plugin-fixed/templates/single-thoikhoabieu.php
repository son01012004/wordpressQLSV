<?php
/**
 * Template hiển thị chi tiết thời khóa biểu
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
            <p><?php esc_html_e('Bạn cần đăng nhập để xem chi tiết thời khóa biểu.', 'qlsv'); ?></p>
            <p><a href="<?php echo esc_url(wp_login_url('http://localhost/wordpressQLSV/')); ?>" class="button"><?php esc_html_e('Đăng nhập', 'qlsv'); ?></a></p>
        </div>
    </div>
    <?php
    get_footer();
    return;
}

// Lấy thông tin thời khóa biểu
$post_id = get_the_ID();
$mon_hoc_id = get_field('mon_hoc', $post_id);
$lop_id = get_field('lop', $post_id);
$giang_vien_id = get_field('giang_vien', $post_id);
$thu = get_field('thu', $post_id);
$gio_bat_dau = get_field('gio_bat_dau', $post_id);
$gio_ket_thuc = get_field('gio_ket_thuc', $post_id);
$phong = get_field('phong', $post_id);
$tuan_hoc = get_field('tuan_hoc', $post_id);

// Lấy tên môn học và lớp
$mon_hoc = $mon_hoc_id ? get_the_title($mon_hoc_id) : 'Không xác định';
$lop = $lop_id ? get_the_title($lop_id) : 'Không xác định';

// Lấy tên giảng viên
$giang_vien = 'Không xác định';
if ($giang_vien_id) {
    $user_data = get_userdata($giang_vien_id);
    if ($user_data) {
        $giang_vien = $user_data->display_name;
    }
}
?>

<div class="qlsv-container">
    <div class="qlsv-tkb-detail">
        <h1 class="qlsv-tkb-detail-title"><?php the_title(); ?></h1>
        
        <div class="qlsv-tkb-detail-content">
            <?php if (function_exists('get_field')): ?>
            
                <div class="qlsv-tkb-detail-card">
                    <h2><?php _e('Thông tin lịch học', 'qlsv'); ?></h2>
                    
                    <div class="qlsv-tkb-detail-info">
                        <div class="qlsv-tkb-detail-row">
                            <div class="qlsv-tkb-detail-label"><?php _e('Môn học:', 'qlsv'); ?></div>
                            <div class="qlsv-tkb-detail-value"><?php echo esc_html($mon_hoc); ?></div>
                        </div>
                        
                        <div class="qlsv-tkb-detail-row">
                            <div class="qlsv-tkb-detail-label"><?php _e('Lớp:', 'qlsv'); ?></div>
                            <div class="qlsv-tkb-detail-value"><?php echo esc_html($lop); ?></div>
                        </div>
                        
                        <div class="qlsv-tkb-detail-row">
                            <div class="qlsv-tkb-detail-label"><?php _e('Thứ:', 'qlsv'); ?></div>
                            <div class="qlsv-tkb-detail-value"><?php echo esc_html($thu); ?></div>
                        </div>
                        
                        <div class="qlsv-tkb-detail-row">
                            <div class="qlsv-tkb-detail-label"><?php _e('Thời gian:', 'qlsv'); ?></div>
                            <div class="qlsv-tkb-detail-value">
                                <?php echo esc_html($gio_bat_dau); ?> - <?php echo esc_html($gio_ket_thuc); ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($phong)) : ?>
                            <div class="qlsv-tkb-detail-row">
                                <div class="qlsv-tkb-detail-label"><?php _e('Phòng:', 'qlsv'); ?></div>
                                <div class="qlsv-tkb-detail-value"><?php echo esc_html($phong); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="qlsv-tkb-detail-row">
                            <div class="qlsv-tkb-detail-label"><?php _e('Giảng viên:', 'qlsv'); ?></div>
                            <div class="qlsv-tkb-detail-value"><?php echo esc_html($giang_vien); ?></div>
                        </div>
                        
                        <?php if (!empty($tuan_hoc)) : ?>
                            <div class="qlsv-tkb-detail-row">
                                <div class="qlsv-tkb-detail-label"><?php _e('Các tuần học:', 'qlsv'); ?></div>
                                <div class="qlsv-tkb-detail-value"><?php echo esc_html($tuan_hoc); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="qlsv-tkb-actions">
                    <a href="<?php echo esc_url(get_post_type_archive_link('thoikhoabieu')); ?>" class="qlsv-button">
                        <?php _e('Xem tất cả thời khóa biểu', 'qlsv'); ?>
                    </a>
                    
                    <?php if (!empty($lop_id)): ?>
                        <a href="<?php echo esc_url(add_query_arg('lop', $lop_id, get_post_type_archive_link('thoikhoabieu'))); ?>" class="qlsv-button">
                            <?php _e('Xem thời khóa biểu lớp này', 'qlsv'); ?>
                        </a>
                    <?php endif; ?>
                </div>
                
            <?php else: ?>
                <div class="qlsv-error-message">
                    <?php _e('Plugin Advanced Custom Fields chưa được kích hoạt. Vui lòng kích hoạt plugin này để xem thông tin chi tiết.', 'qlsv'); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .qlsv-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    }
    .qlsv-tkb-detail {
        background: #fff;
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        padding: 20px;
        margin-bottom: 30px;
    }
    .qlsv-tkb-detail-title {
        margin-top: 0;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
        color: #333;
    }
    .qlsv-tkb-detail-card {
        background: #f9f9f9;
        border-radius: 5px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .qlsv-tkb-detail-card h2 {
        margin-top: 0;
        margin-bottom: 15px;
        font-size: 1.3em;
        color: #444;
    }
    .qlsv-tkb-detail-info {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .qlsv-tkb-detail-row {
        display: flex;
        border-bottom: 1px solid #eee;
        padding-bottom: 8px;
    }
    .qlsv-tkb-detail-label {
        flex: 0 0 150px;
        font-weight: bold;
        color: #555;
    }
    .qlsv-tkb-detail-value {
        flex: 1;
    }
    .qlsv-tkb-actions {
        margin-top: 30px;
        display: flex;
        gap: 15px;
    }
    .qlsv-button {
        display: inline-block;
        padding: 10px 15px;
        background: #0073aa;
        color: #fff;
        text-decoration: none;
        border-radius: 4px;
        font-weight: bold;
        transition: background 0.3s;
    }
    .qlsv-button:hover {
        background: #005177;
        color: #fff;
        text-decoration: none;
    }
    .qlsv-error-message {
        background-color: #f8d7da;
        color: #721c24;
        padding: 15px;
        border: 1px solid #f5c6cb;
        border-radius: 4px;
    }
    @media (max-width: 768px) {
        .qlsv-tkb-detail-row {
            flex-direction: column;
            gap: 5px;
        }
        .qlsv-tkb-detail-label {
            flex: none;
        }
        .qlsv-tkb-actions {
            flex-direction: column;
        }
    }
</style>

<?php get_footer(); ?> 