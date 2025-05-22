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

// Dữ liệu từ shortcode
$tkb_id = isset($tkb_id) ? $tkb_id : 0;

if (!$tkb_id || !get_post($tkb_id)) {
    echo '<div class="qlsv-error">' . esc_html__('Không tìm thấy thông tin lịch học.', 'qlsv') . '</div>';
    return;
}

// Lấy dữ liệu của TKB
$mon_hoc_id = get_field('mon_hoc', $tkb_id);
$lop_id = get_field('lop', $tkb_id);
$giang_vien_id = get_field('giang_vien', $tkb_id);
$thu = get_field('thu', $tkb_id);
$gio_bat_dau = get_field('gio_bat_dau', $tkb_id);
$gio_ket_thuc = get_field('gio_ket_thuc', $tkb_id);
$phong = get_field('phong', $tkb_id);
$tuan_hoc = get_field('tuan_hoc', $tkb_id);

// Lấy thông tin liên quan
$mon_hoc_name = '';
if ($mon_hoc_id) {
    $mon_hoc = get_post($mon_hoc_id);
    $mon_hoc_name = $mon_hoc ? $mon_hoc->post_title : '';
}

$lop_name = '';
if ($lop_id) {
    $lop = get_post($lop_id);
    $lop_name = $lop ? $lop->post_title : '';
}

$giang_vien_name = '';
if ($giang_vien_id) {
    $giang_vien = get_userdata($giang_vien_id);
    $giang_vien_name = $giang_vien ? $giang_vien->display_name : '';
}

?>

<div class="tkb-detail-container">
    <h2 class="tkb-detail-title"><?php esc_html_e('Thông tin lịch học', 'qlsv'); ?></h2>
    
    <div class="tkb-navigation">
        <a href="<?php echo esc_url(get_permalink(get_option('qlsv_tkb_list_page', ''))); ?>" class="tkb-back-btn">
            <span class="dashicons dashicons-arrow-left-alt"></span> <?php esc_html_e('Quay lại danh sách', 'qlsv'); ?>
        </a>
        
        <?php if (current_user_can('administrator')): ?>
        <a href="<?php echo esc_url(add_query_arg(array('action' => 'edit', 'tkb_id' => $tkb_id), get_permalink(get_option('qlsv_tkb_add_edit_page', '')))); ?>" class="tkb-edit-link">
            <span class="dashicons dashicons-edit"></span> <?php esc_html_e('Sửa lịch học', 'qlsv'); ?>
        </a>
        <?php endif; ?>
    </div>
    
    <div class="tkb-detail-info">
        <div class="tkb-detail-row">
            <div class="tkb-detail-label"><?php esc_html_e('Môn học:', 'qlsv'); ?></div>
            <div class="tkb-detail-value"><?php echo esc_html($mon_hoc_name); ?></div>
        </div>
        
        <div class="tkb-detail-row">
            <div class="tkb-detail-label"><?php esc_html_e('Lớp:', 'qlsv'); ?></div>
            <div class="tkb-detail-value"><?php echo esc_html($lop_name); ?></div>
        </div>
        
        <div class="tkb-detail-row">
            <div class="tkb-detail-label"><?php esc_html_e('Thứ:', 'qlsv'); ?></div>
            <div class="tkb-detail-value"><?php echo esc_html($thu); ?></div>
        </div>
        
        <div class="tkb-detail-row">
            <div class="tkb-detail-label"><?php esc_html_e('Thời gian:', 'qlsv'); ?></div>
            <div class="tkb-detail-value"><?php echo esc_html($gio_bat_dau) . ' - ' . esc_html($gio_ket_thuc); ?></div>
        </div>
        
        <div class="tkb-detail-row">
            <div class="tkb-detail-label"><?php esc_html_e('Giảng viên:', 'qlsv'); ?></div>
            <div class="tkb-detail-value"><?php echo !empty($giang_vien_name) ? esc_html($giang_vien_name) : 'Không xác định'; ?></div>
        </div>
        
        <div class="tkb-detail-row">
            <div class="tkb-detail-label"><?php esc_html_e('Phòng học:', 'qlsv'); ?></div>
            <div class="tkb-detail-value"><?php echo !empty($phong) ? esc_html($phong) : 'Không xác định'; ?></div>
        </div>
        
        <div class="tkb-detail-row">
            <div class="tkb-detail-label"><?php esc_html_e('Các tuần học:', 'qlsv'); ?></div>
            <div class="tkb-detail-value"><?php echo !empty($tuan_hoc) ? esc_html($tuan_hoc) : 'Tất cả'; ?></div>
        </div>
    </div>
    
    <div class="tkb-detail-actions">
        <a href="<?php echo esc_url(get_permalink(get_option('qlsv_tkb_list_page', ''))); ?>" class="tkb-btn tkb-primary-btn">
            <span class="dashicons dashicons-list-view"></span> <?php esc_html_e('Xem tất cả lịch học', 'qlsv'); ?>
        </a>
    </div>
</div>

<style>
.tkb-detail-container {
    max-width: 800px;
    margin: 0 auto 30px;
    padding: 20px;
    background-color: #fff;
    border-radius: 5px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
}

.tkb-detail-title {
    margin-top: 0;
    margin-bottom: 20px;
    font-size: 24px;
    font-weight: 600;
    color: #23282d;
    border-bottom: 2px solid #eee;
    padding-bottom: 15px;
}

.tkb-navigation {
    display: flex;
    justify-content: space-between;
    margin-bottom: 25px;
    align-items: center;
}

.tkb-back-btn {
    display: inline-flex;
    align-items: center;
    text-decoration: none;
    color: #0073aa;
    font-weight: 600;
}

.tkb-back-btn:hover {
    color: #005177;
    text-decoration: underline;
}

.tkb-back-btn .dashicons {
    margin-right: 5px;
}

.tkb-edit-link {
    display: inline-flex;
    align-items: center;
    text-decoration: none;
    background-color: #0073aa;
    color: #fff;
    padding: 8px 15px;
    border-radius: 4px;
    font-weight: 600;
    transition: background-color 0.3s;
}

.tkb-edit-link:hover {
    background-color: #005177;
    color: #fff;
}

.tkb-edit-link .dashicons {
    margin-right: 5px;
}

.tkb-detail-info {
    background-color: #f9f9f9;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 25px;
}

.tkb-detail-row {
    display: flex;
    border-bottom: 1px solid #eee;
    padding: 12px 0;
}

.tkb-detail-row:last-child {
    border-bottom: none;
}

.tkb-detail-label {
    flex: 0 0 140px;
    font-weight: 600;
    color: #444;
}

.tkb-detail-value {
    flex: 1;
    color: #333;
}

.tkb-detail-actions {
    display: flex;
    justify-content: center;
    margin-top: 30px;
}

.tkb-btn {
    display: inline-flex;
    align-items: center;
    text-decoration: none;
    padding: 10px 20px;
    border-radius: 4px;
    font-weight: 600;
    transition: background-color 0.3s;
}

.tkb-primary-btn {
    background-color: #0073aa;
    color: #fff;
}

.tkb-primary-btn:hover {
    background-color: #005177;
    color: #fff;
}

.tkb-primary-btn .dashicons {
    margin-right: 8px;
}

@media (max-width: 768px) {
    .tkb-detail-row {
        flex-direction: column;
    }
    
    .tkb-detail-label {
        margin-bottom: 5px;
    }
}
</style> 