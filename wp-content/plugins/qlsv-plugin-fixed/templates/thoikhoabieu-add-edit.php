<?php
/**
 * Template hiển thị form thêm/sửa thời khóa biểu
 *
 * @package QLSV
 */

// Ngăn chặn truy cập trực tiếp
if (!defined('WPINC')) {
    die;
}

// Kiểm tra quyền quản trị
if (!current_user_can('administrator')) {
    echo '<div class="qlsv-error">' . esc_html__('Bạn không có quyền truy cập chức năng này.', 'qlsv') . '</div>';
    return;
}

// Dữ liệu từ shortcode
$tkb_id = isset($tkb_id) ? $tkb_id : 0;
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'add';
$is_edit = $tkb_id > 0 && $action == 'edit';
$form_title = $is_edit ? 'Sửa lịch học' : 'Thêm lịch học mới';
$redirect_url = isset($redirect_url) ? $redirect_url : get_permalink();

// Debug info
if (defined('WP_DEBUG') && WP_DEBUG && current_user_can('administrator')) {
    echo '<div class="tkb-debug">';
    echo '<p>Template Debug:</p>';
    echo '<p>tkb_id: ' . $tkb_id . '</p>';
    echo '<p>action: ' . $action . '</p>'; 
    echo '<p>is_edit: ' . ($is_edit ? 'true' : 'false') . '</p>';
    echo '<p>redirect_url: ' . $redirect_url . '</p>';
    echo '</div>';
}

// Lấy dữ liệu của TKB nếu đang sửa
$mon_hoc_id = 0;
$lop_id = 0;
$giang_vien_id = 0;
$thu = '';
$gio_bat_dau = '';
$gio_ket_thuc = '';
$phong = '';
$tuan_hoc = '';

if ($is_edit) {
    $mon_hoc_id = get_field('mon_hoc', $tkb_id);
    $lop_id = get_field('lop', $tkb_id);
    $giang_vien_id = get_field('giang_vien', $tkb_id);
    $thu = get_field('thu', $tkb_id);
    $gio_bat_dau = get_field('gio_bat_dau', $tkb_id);
    $gio_ket_thuc = get_field('gio_ket_thuc', $tkb_id);
    $phong = get_field('phong', $tkb_id);
    $tuan_hoc = get_field('tuan_hoc', $tkb_id);
}

// Lấy danh sách môn học, lớp, giáo viên
$all_courses = get_posts(array(
    'post_type' => 'monhoc',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC'
));

$all_classes = get_posts(array(
    'post_type' => 'lop',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC'
));

$all_teachers = get_users(array(
    'role' => 'giaovien',
    'orderby' => 'display_name',
    'order' => 'ASC'
));

// Danh sách các ngày trong tuần
$weekdays = array(
    'Thứ 2' => 'Thứ 2',
    'Thứ 3' => 'Thứ 3',
    'Thứ 4' => 'Thứ 4',
    'Thứ 5' => 'Thứ 5',
    'Thứ 6' => 'Thứ 6',
    'Thứ 7' => 'Thứ 7',
    'Chủ nhật' => 'Chủ nhật'
);

?>

<div class="tkb-form-container">
    <h2 class="tkb-form-title"><?php echo esc_html($form_title); ?></h2>
    
    <?php if (isset($_GET['message']) && $_GET['message'] == 'success'): ?>
        <div class="tkb-success">
            <?php echo $is_edit ? esc_html__('Cập nhật lịch học thành công!', 'qlsv') : esc_html__('Thêm lịch học mới thành công!', 'qlsv'); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['message']) && $_GET['message'] == 'error'): ?>
        <div class="qlsv-error">
            <?php esc_html_e('Đã xảy ra lỗi. Vui lòng thử lại.', 'qlsv'); ?>
        </div>
    <?php endif; ?>
    
    <!-- Thêm nút quay lại -->
    <div class="tkb-navigation">
        <a href="<?php echo esc_url(get_permalink(get_option('qlsv_tkb_list_page', ''))); ?>" class="tkb-back-btn">
            <span class="dashicons dashicons-arrow-left-alt"></span> <?php esc_html_e('Quay lại danh sách', 'qlsv'); ?>
        </a>
    </div>
    
    <!-- Hướng dẫn sử dụng -->
    <div class="tkb-instructions">
        <p><?php esc_html_e('Vui lòng điền đầy đủ các thông tin dưới đây để tạo lịch học mới:', 'qlsv'); ?></p>
    </div>
    
    <!-- Debug info cho admin -->
    <?php if (current_user_can('administrator') && WP_DEBUG): ?>
    <div class="tkb-debug">
        <p><strong>Debug Info:</strong></p>
        <p>Form Action: <?php echo $form_action; ?></p>
        <p>TKB ID: <?php echo $tkb_id; ?></p>
        <p>Redirect URL: <?php echo $redirect_url; ?></p>
    </div>
    <?php endif; ?>
    
    <form class="tkb-edit-form" method="post" action="">
        <?php wp_nonce_field('tkb_save_action', 'tkb_nonce'); ?>
        <input type="hidden" name="tkb_action" value="<?php echo $is_edit ? 'update' : 'create'; ?>">
        <input type="hidden" name="tkb_id" value="<?php echo esc_attr($tkb_id); ?>">
        <input type="hidden" name="redirect_url" value="<?php echo esc_url($redirect_url); ?>">
        
        <div class="tkb-form-row">
            <div class="tkb-form-group">
                <label for="mon_hoc"><?php esc_html_e('Môn học', 'qlsv'); ?> <span class="required">*</span></label>
                <select name="mon_hoc" id="mon_hoc" required class="tkb-input">
                    <option value=""><?php esc_html_e('-- Chọn môn học --', 'qlsv'); ?></option>
                    <?php foreach ($all_courses as $course): ?>
                    <option value="<?php echo esc_attr($course->ID); ?>" <?php selected($mon_hoc_id, $course->ID); ?>>
                        <?php echo esc_html($course->post_title); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="tkb-form-group">
                <label for="lop"><?php esc_html_e('Lớp', 'qlsv'); ?> <span class="required">*</span></label>
                <select name="lop" id="lop" required class="tkb-input">
                    <option value=""><?php esc_html_e('-- Chọn lớp --', 'qlsv'); ?></option>
                    <?php foreach ($all_classes as $class): 
                        $class_name = get_field('ten_lop', $class->ID);
                        if (empty($class_name)) {
                            $class_name = $class->post_title;
                        }
                    ?>
                    <option value="<?php echo esc_attr($class->ID); ?>" <?php selected($lop_id, $class->ID); ?>>
                        <?php echo esc_html($class_name); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="tkb-form-row">
            <div class="tkb-form-group">
                <label for="giang_vien"><?php esc_html_e('Giảng viên', 'qlsv'); ?></label>
                <select name="giang_vien" id="giang_vien" class="tkb-input">
                    <option value=""><?php esc_html_e('-- Chọn giảng viên --', 'qlsv'); ?></option>
                    <?php foreach ($all_teachers as $teacher): ?>
                    <option value="<?php echo esc_attr($teacher->ID); ?>" <?php selected($giang_vien_id, $teacher->ID); ?>>
                        <?php echo esc_html($teacher->display_name); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="tkb-form-group">
                <label for="thu"><?php esc_html_e('Thứ', 'qlsv'); ?> <span class="required">*</span></label>
                <select name="thu" id="thu" required class="tkb-input">
                    <option value=""><?php esc_html_e('-- Chọn thứ --', 'qlsv'); ?></option>
                    <?php foreach ($weekdays as $day_value => $day_label): ?>
                    <option value="<?php echo esc_attr($day_value); ?>" <?php selected($thu, $day_value); ?>>
                        <?php echo esc_html($day_label); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="tkb-form-row">
            <div class="tkb-form-group">
                <label for="gio_bat_dau"><?php esc_html_e('Giờ bắt đầu', 'qlsv'); ?> <span class="required">*</span></label>
                <input type="time" name="gio_bat_dau" id="gio_bat_dau" value="<?php echo esc_attr($gio_bat_dau); ?>" required class="tkb-input">
            </div>
            
            <div class="tkb-form-group">
                <label for="gio_ket_thuc"><?php esc_html_e('Giờ kết thúc', 'qlsv'); ?> <span class="required">*</span></label>
                <input type="time" name="gio_ket_thuc" id="gio_ket_thuc" value="<?php echo esc_attr($gio_ket_thuc); ?>" required class="tkb-input">
            </div>
            
            <div class="tkb-form-group">
                <label for="phong"><?php esc_html_e('Phòng học', 'qlsv'); ?></label>
                <input type="text" name="phong" id="phong" value="<?php echo esc_attr($phong); ?>" placeholder="Nhập phòng học" class="tkb-input">
            </div>
        </div>
        
        <div class="tkb-form-group">
            <label for="tuan_hoc"><?php esc_html_e('Tuần học (VD: 1-10, 12, 15)', 'qlsv'); ?></label>
            <input type="text" name="tuan_hoc" id="tuan_hoc" value="<?php echo esc_attr($tuan_hoc); ?>" placeholder="Nhập các tuần học" class="tkb-input">
        </div>
        
        <div class="tkb-form-actions">
            <button type="submit" class="tkb-submit-btn">
                <?php echo $is_edit ? 
                    '<span class="dashicons dashicons-update"></span> ' . esc_html__('Cập nhật lịch học', 'qlsv') : 
                    '<span class="dashicons dashicons-plus-alt"></span> ' . esc_html__('Thêm lịch học', 'qlsv'); 
                ?>
            </button>
            
            <a href="<?php echo esc_url($redirect_url); ?>" class="tkb-cancel-btn">
                <span class="dashicons dashicons-no-alt"></span> <?php esc_html_e('Hủy bỏ', 'qlsv'); ?>
            </a>
        </div>
    </form>
</div>

<style>
    .tkb-form-container {
        max-width: 800px;
        margin: 0 auto 30px;
        padding: 30px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    }
    
    .tkb-form-title {
        margin-top: 0;
        margin-bottom: 25px;
        font-size: 28px;
        font-weight: 700;
        color: #23282d;
        border-bottom: 2px solid #eee;
        padding-bottom: 15px;
        position: relative;
    }
    
    .tkb-form-title:after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 60px;
        height: 2px;
        background-color: #00a0d2;
    }
    
    .tkb-success {
        background-color: #d4edda;
        color: #155724;
        padding: 15px;
        margin-bottom: 20px;
        border-left: 4px solid #28a745;
        border-radius: 4px;
        display: flex;
        align-items: center;
    }
    
    .tkb-success:before {
        content: '✓';
        margin-right: 10px;
        font-size: 18px;
        font-weight: bold;
    }
    
    .qlsv-error {
        background-color: #f8d7da;
        color: #721c24;
        padding: 15px;
        margin-bottom: 20px;
        border-left: 4px solid #dc3545;
        border-radius: 4px;
    }
    
    .tkb-edit-form {
        display: flex;
        flex-direction: column;
        gap: 25px;
    }
    
    .tkb-form-group {
        margin-bottom: 0;
        flex: 1;
    }
    
    .tkb-form-row {
        display: flex;
        gap: 20px;
        margin-bottom: 0;
    }
    
    .tkb-form-group label {
        display: block;
        margin-bottom: 10px;
        font-weight: 600;
        color: #333;
        font-size: 15px;
    }
    
    .tkb-form-group .required {
        color: #d63638;
        font-size: 16px;
        margin-left: 3px;
    }
    
    .tkb-input,
    .tkb-form-group input[type="text"],
    .tkb-form-group input[type="time"],
    .tkb-form-group select {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 15px;
        transition: all 0.3s ease;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.07);
        background-color: #fff;
    }
    
    .tkb-form-group input[type="text"]:focus,
    .tkb-form-group input[type="time"]:focus,
    .tkb-form-group select:focus {
        border-color: #00a0d2;
        outline: none;
        box-shadow: 0 0 0 1px #00a0d2;
    }
    
    .tkb-form-group input[type="text"]::placeholder,
    .tkb-form-group input[type="time"]::placeholder {
        color: #aaa;
    }
    
    .tkb-form-actions {
        display: flex;
        gap: 15px;
        margin-top: 35px;
        justify-content: flex-end;
    }
    
    .tkb-submit-btn {
        background-color: #00a0d2;
        color: #fff;
        border: none;
        padding: 14px 30px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        font-size: 16px;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(0, 160, 210, 0.2);
    }
    
    .tkb-submit-btn:hover {
        background-color: #008db8;
        transform: translateY(-2px);
        box-shadow: 0 6px 8px rgba(0, 160, 210, 0.25);
    }
    
    .tkb-submit-btn:active {
        transform: translateY(0);
    }
    
    .tkb-submit-btn .dashicons {
        margin-right: 10px;
        font-size: 18px;
        width: 18px;
        height: 18px;
    }
    
    .tkb-cancel-btn {
        background-color: #f7f7f7;
        color: #555;
        border: 1px solid #ddd;
        padding: 14px 25px;
        border-radius: 6px;
        text-decoration: none;
        text-align: center;
        font-weight: 600;
        font-size: 16px;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
    }
    
    .tkb-cancel-btn:hover {
        background-color: #e9e9e9;
        color: #333;
        border-color: #ccc;
    }
    
    .tkb-cancel-btn .dashicons {
        margin-right: 10px;
        font-size: 18px;
        width: 18px;
        height: 18px;
    }
    
    /* Navigation */
    .tkb-navigation {
        margin-bottom: 25px;
    }
    
    .tkb-back-btn {
        display: inline-flex;
        align-items: center;
        text-decoration: none;
        color: #00a0d2;
        font-weight: 600;
        padding: 10px 0;
        transition: all 0.2s ease;
    }
    
    .tkb-back-btn:hover {
        color: #0073aa;
        transform: translateX(-3px);
    }
    
    .tkb-back-btn .dashicons {
        margin-right: 8px;
    }
    
    /* Instructions */
    .tkb-instructions {
        background-color: #f0f8ff;
        border-left: 4px solid #00a0d2;
        padding: 18px 20px;
        margin-bottom: 30px;
        border-radius: 0 6px 6px 0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.04);
    }
    
    .tkb-instructions p {
        margin: 0;
        font-weight: 500;
        color: #444;
        font-size: 15px;
    }
    
    /* Debug info */
    .tkb-debug {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 4px;
        font-family: monospace;
        font-size: 13px;
    }
    
    .tkb-debug p {
        margin: 5px 0;
    }
    
    @media (max-width: 768px) {
        .tkb-form-container {
            padding: 20px;
        }
        
        .tkb-form-row {
            flex-direction: column;
            gap: 25px;
        }
        
        .tkb-form-actions {
            flex-direction: column;
        }
        
        .tkb-submit-btn, 
        .tkb-cancel-btn {
            width: 100%;
            justify-content: center;
        }
    }
</style> 