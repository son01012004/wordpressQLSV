<?php
/**
 * Template hiển thị thời khóa biểu theo tuần
 *
 * @package QLSV
 */

// Ngăn chặn truy cập trực tiếp
if (!defined('WPINC')) {
    die;
}

// Dữ liệu từ shortcode
$tkb_data = isset($tkb_data) ? $tkb_data : array();
$all_classes = isset($all_classes) ? $all_classes : array();
$all_courses = isset($all_courses) ? $all_courses : array();
$all_teachers = isset($all_teachers) ? $all_teachers : array();
$selected_class = isset($selected_class) ? $selected_class : 0;
$selected_course = isset($selected_course) ? $selected_course : 0;
$selected_teacher = isset($selected_teacher) ? $selected_teacher : 0;
$is_admin = isset($is_admin) ? $is_admin : false;
$is_teacher = isset($is_teacher) ? $is_teacher : false;
$is_student = isset($is_student) ? $is_student : false;
?>

<div class="thoikhoabieu-container">
    <?php if ($is_admin): ?>
    <div class="tkb-admin-controls">
        <button type="button" id="tkb-add-new-btn" class="tkb-admin-btn">
            <span class="dashicons dashicons-plus-alt2"></span><?php esc_html_e('Thêm lịch học mới', 'qlsv'); ?>
        </button>
    </div>
    <?php endif; ?>

    <!-- Tiêu đề trang tùy theo vai trò -->
    <h2 class="tkb-title">
        <?php 
        if ($is_teacher) {
            esc_html_e('Lịch giảng dạy của giáo viên', 'qlsv');
        } elseif ($is_student) {
            esc_html_e('Lịch học của sinh viên', 'qlsv');
        } else {
            esc_html_e('Thời khóa biểu', 'qlsv');
        }
        ?>
    </h2>
    
    <?php 
    // Hiển thị thông báo nếu có
    if (isset($_GET['message'])) {
        $message_type = sanitize_text_field($_GET['message']);
        $message_class = 'tkb-message';
        $message_text = '';
        
        switch ($message_type) {
            case 'created':
                $message_class .= ' tkb-message-success';
                $message_text = __('Thêm lịch học mới thành công!', 'qlsv');
                break;
            case 'updated':
                $message_class .= ' tkb-message-success';
                $message_text = __('Cập nhật lịch học thành công!', 'qlsv');
                break;
            case 'deleted':
                $message_class .= ' tkb-message-success';
                $message_text = __('Xóa lịch học thành công!', 'qlsv');
                break;
            case 'error':
                $message_class .= ' tkb-message-error';
                $message_text = __('Đã xảy ra lỗi. Vui lòng thử lại.', 'qlsv');
                break;
        }
        
        if (!empty($message_text)) {
            echo '<div class="' . esc_attr($message_class) . '">' . esc_html($message_text) . '</div>';
        }
    }
    ?>
    
    <!-- Bộ lọc thời khóa biểu -->
    <div class="thoikhoabieu-filter">
        <form class="filter-form" method="get" action="<?php echo esc_url(get_permalink(get_option('qlsv_tkb_list_page', ''))); ?>">
            <?php 
            // Giữ các tham số URL khác (nếu cần)
            foreach ($_GET as $key => $value) {
                if (!in_array($key, array('lop', 'monhoc', 'teacher', 'view'))) {
                    echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
                }
            }
            
            ?>
            
            <?php if ($is_admin || $is_teacher): ?>
            <div class="filter-group">
                <label for="lop_filter"><?php esc_html_e('Lớp:', 'qlsv'); ?></label>
                <select name="lop" id="lop_filter">
                    <option value=""><?php esc_html_e('-- Tất cả lớp --', 'qlsv'); ?></option>
                    <?php foreach ($all_classes as $class) : 
                        // Lấy tên lớp từ trường ACF
                        $class_name = get_field('ten_lop', $class->ID);
                        if (empty($class_name)) {
                            $class_name = $class->post_title;
                        }
                    ?>
                        <option value="<?php echo esc_attr($class->ID); ?>" <?php selected($selected_class, $class->ID); ?>>
                            <?php echo esc_html($class_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            
            <div class="filter-group">
                <label for="monhoc_filter"><?php esc_html_e('Môn học:', 'qlsv'); ?></label>
                <select name="monhoc" id="monhoc_filter">
                    <option value=""><?php esc_html_e('-- Tất cả môn học --', 'qlsv'); ?></option>
                    <?php foreach ($all_courses as $course) : ?>
                        <option value="<?php echo esc_attr($course->ID); ?>" <?php selected($selected_course, $course->ID); ?>>
                            <?php echo esc_html($course->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if ($is_admin): ?>
            <div class="filter-group">
                <label for="teacher_filter"><?php esc_html_e('Giáo viên:', 'qlsv'); ?></label>
                <select name="teacher" id="teacher_filter">
                    <option value=""><?php esc_html_e('-- Tất cả giáo viên --', 'qlsv'); ?></option>
                    <?php foreach ($all_teachers as $teacher) : ?>
                        <option value="<?php echo esc_attr($teacher->ID); ?>" <?php selected($selected_teacher, $teacher->ID); ?>>
                            <?php echo esc_html($teacher->display_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            
            <div class="filter-group">
                <label for="view_filter"><?php esc_html_e('Hiển thị:', 'qlsv'); ?></label>
                <select name="view_display" id="view_filter" onchange="document.getElementById('view_hidden').value = this.value;">
                    <option value="tuan" <?php selected(isset($_GET['view']) ? $_GET['view'] : 'tuan', 'tuan'); ?>>
                        <?php esc_html_e('Theo tuần', 'qlsv'); ?>
                    </option>
                    <option value="danh_sach" <?php selected(isset($_GET['view']) ? $_GET['view'] : 'tuan', 'danh_sach'); ?>>
                        <?php esc_html_e('Danh sách', 'qlsv'); ?>
                    </option>
                </select>
                <input type="hidden" id="view_hidden" name="view" value="<?php echo isset($_GET['view']) ? esc_attr($_GET['view']) : 'tuan'; ?>">
            </div>
            
            <div class="filter-group filter-action">
                <button type="submit" class="filter-btn"><?php esc_html_e('Tìm kiếm', 'qlsv'); ?></button>
            </div>
        </form>
    </div>
    
    <?php if (empty($tkb_data)) : ?>
        <p class="no-data"><?php esc_html_e('Không có thời khóa biểu nào phù hợp với điều kiện.', 'qlsv'); ?></p>
    <?php else : ?>
        <!-- Hiển thị thời khóa biểu theo tuần -->
        <div class="thoikhoabieu-tuan">
            <?php 
            $weekdays = array(
                'Thứ 2' => 'Thứ 2',
                'Thứ 3' => 'Thứ 3',
                'Thứ 4' => 'Thứ 4',
                'Thứ 5' => 'Thứ 5',
                'Thứ 6' => 'Thứ 6',
                'Thứ 7' => 'Thứ 7',
                'Chủ nhật' => 'Chủ nhật'
            );
            
            foreach ($weekdays as $day) :
                // Bỏ qua các ngày không có lịch học
                if (!isset($tkb_data[$day]) || empty($tkb_data[$day])) continue;
            ?>
                <div class="tkb-day">
                    <h3><?php echo esc_html($day); ?></h3>
                    
                    <?php foreach ($tkb_data[$day] as $tkb) : 
                        // Lấy thông tin môn học từ trường quan hệ
                        $mon_hoc_id = get_field('mon_hoc', $tkb['ID']);
                        $mon_hoc_name = '';
                        if ($mon_hoc_id) {
                            $mon_hoc = get_post($mon_hoc_id);
                            $mon_hoc_name = $mon_hoc ? $mon_hoc->post_title : '';
                        }
                        
                        // Lấy thông tin lớp từ trường quan hệ
                        $lop_id = get_field('lop', $tkb['ID']);
                        $lop_name = '';
                        $khoa_name = '';
                        if ($lop_id) {
                            $lop = get_post($lop_id);
                            $lop_name = $lop ? $lop->post_title : '';
                            // Lấy thông tin khoa từ lớp nếu có
                            $khoa = get_field('khoa', $lop_id);
                            $khoa_name = $khoa ? $khoa : '';
                        }
                        
                        // Lấy thông tin phòng học
                        $phong = get_field('phong', $tkb['ID']);
                        
                        // Lấy thông tin giảng viên từ trường user
                        $giang_vien_id = get_field('giang_vien', $tkb['ID']);
                        $giang_vien_name = '';
                        if ($giang_vien_id) {
                            $giang_vien = get_userdata($giang_vien_id);
                            $giang_vien_name = $giang_vien ? $giang_vien->display_name : '';
                        }
                        
                        // Lấy thông tin giờ bắt đầu và kết thúc
                        $gio_bat_dau = get_field('gio_bat_dau', $tkb['ID']);
                        $gio_ket_thuc = get_field('gio_ket_thuc', $tkb['ID']);
                        
                        // Lấy thông tin tuần học
                        $tuan_hoc = get_field('tuan_hoc', $tkb['ID']);
                    ?>
                        <div class="tkb-item">
                            <div class="tkb-time">
                                <?php echo esc_html($gio_bat_dau) . ' - ' . esc_html($gio_ket_thuc); ?>
                            </div>
                            
                            <div class="tkb-details">
                                <div class="tkb-course">
                                    <strong><?php echo esc_html($mon_hoc_name); ?></strong>
                                    <div class="tkb-actions-inline">
                                        <a href="<?php 
                                            // Tạo URL với tham số redirect_url để có thể quay lại trang hiện tại
                                            $list_page = get_permalink(get_option('qlsv_tkb_list_page', ''));
                                            $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                                            echo esc_url(add_query_arg(array(
                                                'action' => 'view', 
                                                'tkb_id' => $tkb['ID'],
                                                'thoi-khoa-bieu-moi' => 'true',
                                                'redirect_url' => urlencode($current_url)
                                            ), $list_page)); 
                                        ?>" class="tkb-view-link" title="<?php esc_attr_e('Xem chi tiết', 'qlsv'); ?>">
                                            <span class="dashicons dashicons-visibility"></span>
                                        </a>
                                        
                                        <?php if ($is_admin): ?>
                                        <a href="#" class="tkb-edit-link" title="<?php esc_attr_e('Sửa', 'qlsv'); ?>"
                                           data-tkb-id="<?php echo esc_attr($tkb['ID']); ?>"
                                           data-mon-hoc="<?php echo esc_attr($mon_hoc_id); ?>"
                                           data-lop="<?php echo esc_attr($lop_id); ?>"
                                           data-giang-vien="<?php echo esc_attr($giang_vien_id); ?>"
                                           data-thu="<?php echo esc_attr($thu); ?>"
                                           data-gio-bat-dau="<?php echo esc_attr($gio_bat_dau); ?>"
                                           data-gio-ket-thuc="<?php echo esc_attr($gio_ket_thuc); ?>"
                                           data-phong="<?php echo esc_attr($phong); ?>"
                                           data-tuan-hoc="<?php echo esc_attr($tuan_hoc); ?>">
                                            <span class="dashicons dashicons-edit"></span>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="tkb-info">
                                    <span class="tkb-class">
                                        <strong>Lớp:</strong> <?php echo esc_html($lop_name); ?>
                                    </span>
                                    
                                    <?php if (!empty($khoa_name)) : ?>
                                        <span class="tkb-khoa">
                                            <strong>Khoa:</strong> <?php echo esc_html($khoa_name); ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($phong)) : ?>
                                        <span class="tkb-room">
                                            <strong>Phòng:</strong> <?php echo esc_html($phong); ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <span class="tkb-lecturer">
                                        <strong>GV:</strong> <?php echo !empty($giang_vien_name) ? esc_html($giang_vien_name) : 'Chưa phân công'; ?>
                                    </span>
                                    
                                    <?php if (!empty($tuan_hoc)) : ?>
                                        <span class="tkb-weeks">
                                            <strong>Tuần:</strong> <?php echo esc_html($tuan_hoc); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Form popup thêm lịch học mới -->
<?php if ($is_admin): ?>
<div id="tkb-add-form-popup" class="tkb-popup-overlay">
    <div class="tkb-popup-container">
        <div class="tkb-popup-header">
            <h3><?php esc_html_e('Thêm lịch học mới', 'qlsv'); ?></h3>
            <button type="button" class="tkb-popup-close">&times;</button>
        </div>
        
        <div class="tkb-popup-content">
            <form class="tkb-edit-form" method="post" action="">
                <?php wp_nonce_field('tkb_save_action', 'tkb_nonce'); ?>
                <input type="hidden" name="tkb_action" value="create">
                <input type="hidden" name="redirect_url" value="<?php echo esc_url(get_permalink()); ?>">
                
                <div class="tkb-form-row">
                    <div class="tkb-form-group">
                        <label for="mon_hoc"><?php esc_html_e('Môn học', 'qlsv'); ?> <span class="required">*</span></label>
                        <select name="mon_hoc" id="mon_hoc" required class="tkb-input">
                            <option value=""><?php esc_html_e('-- Chọn môn học --', 'qlsv'); ?></option>
                            <?php foreach ($all_courses as $course): ?>
                            <option value="<?php echo esc_attr($course->ID); ?>">
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
                            <option value="<?php echo esc_attr($class->ID); ?>">
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
                            <option value="<?php echo esc_attr($teacher->ID); ?>">
                                <?php echo esc_html($teacher->display_name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="tkb-form-group">
                        <label for="thu"><?php esc_html_e('Thứ', 'qlsv'); ?> <span class="required">*</span></label>
                        <select name="thu" id="thu" required class="tkb-input">
                            <option value=""><?php esc_html_e('-- Chọn thứ --', 'qlsv'); ?></option>
                            <option value="Thứ 2">Thứ 2</option>
                            <option value="Thứ 3">Thứ 3</option>
                            <option value="Thứ 4">Thứ 4</option>
                            <option value="Thứ 5">Thứ 5</option>
                            <option value="Thứ 6">Thứ 6</option>
                            <option value="Thứ 7">Thứ 7</option>
                            <option value="Chủ nhật">Chủ nhật</option>
                        </select>
                    </div>
                </div>
                
                <div class="tkb-form-row">
                    <div class="tkb-form-group">
                        <label for="gio_bat_dau"><?php esc_html_e('Giờ bắt đầu', 'qlsv'); ?> <span class="required">*</span></label>
                        <input type="time" name="gio_bat_dau" id="gio_bat_dau" required class="tkb-input">
                    </div>
                    
                    <div class="tkb-form-group">
                        <label for="gio_ket_thuc"><?php esc_html_e('Giờ kết thúc', 'qlsv'); ?> <span class="required">*</span></label>
                        <input type="time" name="gio_ket_thuc" id="gio_ket_thuc" required class="tkb-input">
                    </div>
                    
                    <div class="tkb-form-group">
                        <label for="phong"><?php esc_html_e('Phòng học', 'qlsv'); ?></label>
                        <input type="text" name="phong" id="phong" placeholder="Nhập phòng học" class="tkb-input">
                    </div>
                </div>
                
                <div class="tkb-form-group">
                    <label for="tuan_hoc"><?php esc_html_e('Tuần học (VD: 1-10, 12, 15)', 'qlsv'); ?></label>
                    <input type="text" name="tuan_hoc" id="tuan_hoc" placeholder="Nhập các tuần học" class="tkb-input">
                </div>
                
                <div class="tkb-form-actions">
                    <button type="submit" class="tkb-submit-btn">
                        <span class="dashicons dashicons-plus-alt"></span> <?php esc_html_e('Thêm lịch học', 'qlsv'); ?>
                    </button>
                    
                    <button type="button" class="tkb-cancel-btn tkb-popup-close">
                        <span class="dashicons dashicons-no-alt"></span> <?php esc_html_e('Hủy bỏ', 'qlsv'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Form popup sửa lịch học -->
<div id="tkb-edit-form-popup" class="tkb-popup-overlay">
    <div class="tkb-popup-container">
        <div class="tkb-popup-header">
            <h3><?php esc_html_e('Sửa lịch học', 'qlsv'); ?></h3>
            <button type="button" class="tkb-popup-close">&times;</button>
        </div>
        
        <div class="tkb-popup-content">
            <form class="tkb-edit-form" method="post" action="">
                <?php wp_nonce_field('tkb_save_action', 'tkb_nonce'); ?>
                <input type="hidden" name="tkb_action" value="update">
                <input type="hidden" name="tkb_id" id="edit_tkb_id" value="">
                <input type="hidden" name="redirect_url" value="<?php echo esc_url(get_permalink()); ?>">
                
                <div class="tkb-form-row">
                    <div class="tkb-form-group">
                        <label for="edit_mon_hoc"><?php esc_html_e('Môn học', 'qlsv'); ?> <span class="required">*</span></label>
                        <select name="mon_hoc" id="edit_mon_hoc" required class="tkb-input">
                            <option value=""><?php esc_html_e('-- Chọn môn học --', 'qlsv'); ?></option>
                            <?php foreach ($all_courses as $course): ?>
                            <option value="<?php echo esc_attr($course->ID); ?>">
                                <?php echo esc_html($course->post_title); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="tkb-form-group">
                        <label for="edit_lop"><?php esc_html_e('Lớp', 'qlsv'); ?> <span class="required">*</span></label>
                        <select name="lop" id="edit_lop" required class="tkb-input">
                            <option value=""><?php esc_html_e('-- Chọn lớp --', 'qlsv'); ?></option>
                            <?php foreach ($all_classes as $class): 
                                $class_name = get_field('ten_lop', $class->ID);
                                if (empty($class_name)) {
                                    $class_name = $class->post_title;
                                }
                            ?>
                            <option value="<?php echo esc_attr($class->ID); ?>">
                                <?php echo esc_html($class_name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="tkb-form-row">
                    <div class="tkb-form-group">
                        <label for="edit_giang_vien"><?php esc_html_e('Giảng viên', 'qlsv'); ?></label>
                        <select name="giang_vien" id="edit_giang_vien" class="tkb-input">
                            <option value=""><?php esc_html_e('-- Chọn giảng viên --', 'qlsv'); ?></option>
                            <?php foreach ($all_teachers as $teacher): ?>
                            <option value="<?php echo esc_attr($teacher->ID); ?>">
                                <?php echo esc_html($teacher->display_name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="tkb-form-group">
                        <label for="edit_thu"><?php esc_html_e('Thứ', 'qlsv'); ?> <span class="required">*</span></label>
                        <select name="thu" id="edit_thu" required class="tkb-input">
                            <option value=""><?php esc_html_e('-- Chọn thứ --', 'qlsv'); ?></option>
                            <option value="Thứ 2">Thứ 2</option>
                            <option value="Thứ 3">Thứ 3</option>
                            <option value="Thứ 4">Thứ 4</option>
                            <option value="Thứ 5">Thứ 5</option>
                            <option value="Thứ 6">Thứ 6</option>
                            <option value="Thứ 7">Thứ 7</option>
                            <option value="Chủ nhật">Chủ nhật</option>
                        </select>
                    </div>
                </div>
                
                <div class="tkb-form-row">
                    <div class="tkb-form-group">
                        <label for="edit_gio_bat_dau"><?php esc_html_e('Giờ bắt đầu', 'qlsv'); ?> <span class="required">*</span></label>
                        <input type="time" name="gio_bat_dau" id="edit_gio_bat_dau" required class="tkb-input">
                    </div>
                    
                    <div class="tkb-form-group">
                        <label for="edit_gio_ket_thuc"><?php esc_html_e('Giờ kết thúc', 'qlsv'); ?> <span class="required">*</span></label>
                        <input type="time" name="gio_ket_thuc" id="edit_gio_ket_thuc" required class="tkb-input">
                    </div>
                    
                    <div class="tkb-form-group">
                        <label for="edit_phong"><?php esc_html_e('Phòng học', 'qlsv'); ?></label>
                        <input type="text" name="phong" id="edit_phong" placeholder="Nhập phòng học" class="tkb-input">
                    </div>
                </div>
                
                <div class="tkb-form-group">
                    <label for="edit_tuan_hoc"><?php esc_html_e('Tuần học (VD: 1-10, 12, 15)', 'qlsv'); ?></label>
                    <input type="text" name="tuan_hoc" id="edit_tuan_hoc" placeholder="Nhập các tuần học" class="tkb-input">
                </div>
                
                <div class="tkb-form-actions">
                    <button type="submit" class="tkb-submit-btn">
                        <span class="dashicons dashicons-saved"></span> <?php esc_html_e('Lưu thay đổi', 'qlsv'); ?>
                    </button>
                    
                    <button type="button" class="tkb-delete-btn" id="tkb-delete-btn">
                        <span class="dashicons dashicons-trash"></span> <?php esc_html_e('Xóa lịch học', 'qlsv'); ?>
                    </button>
                    
                    <button type="button" class="tkb-cancel-btn tkb-popup-close">
                        <span class="dashicons dashicons-no-alt"></span> <?php esc_html_e('Hủy bỏ', 'qlsv'); ?>
                    </button>
                </div>
            </form>
            
            <!-- Form ẩn để xóa lịch học -->
            <form id="tkb-delete-form" method="post" action="" style="display: none;">
                <?php wp_nonce_field('tkb_delete_action', 'tkb_delete_nonce'); ?>
                <input type="hidden" name="tkb_action" value="delete">
                <input type="hidden" name="tkb_id" id="delete_tkb_id" value="">
                <input type="hidden" name="redirect_url" value="<?php echo esc_url(get_permalink()); ?>">
            </form>
        </div>
    </div>
</div>

<style>
    .thoikhoabieu-container {
        margin-bottom: 30px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    }
    
    .tkb-title {
        margin-bottom: 20px;
        font-size: 24px;
        font-weight: 600;
    }
    
    .tkb-admin-controls {
        margin-bottom: 25px;
        display: flex;
        justify-content: flex-end;
    }
    
    .tkb-admin-btn {
        display: inline-flex;
        align-items: center;
        background: #00a0d2;
        color: #fff;
        text-decoration: none;
        padding: 12px 20px;
        border-radius: 6px;
        font-weight: 600;
        box-shadow: 0 4px 6px rgba(0, 160, 210, 0.2);
        transition: all 0.3s ease;
        border: none;
        font-size: 15px;
    }
    
    .tkb-admin-btn:hover {
        background: #0091cd;
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 6px 8px rgba(0, 160, 210, 0.25);
    }
    
    .tkb-admin-btn .dashicons {
        margin-right: 8px;
        font-size: 18px;
        width: 18px;
        height: 18px;
    }
    
    .thoikhoabieu-filter {
        margin-bottom: 20px;
        padding: 20px;
        background: #f5f5f5;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .filter-form {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        align-items: center;
    }
    
    .filter-group {
        margin-right: 0;
        margin-bottom: 10px;
        flex: 1;
        min-width: 200px;
    }
    
    .filter-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
    }
    
    .filter-group select {
        width: 100%;
        padding: 10px 12px;
        min-width: 200px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background-color: #fff;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
        font-size: 14px;
    }
    
    .filter-group.filter-action {
        margin-bottom: 10px;
        align-self: center;
        flex: 0 0 auto;
        min-width: 200px;
    }
    
    .filter-btn {
        background: #0073aa;
        color: #fff;
        border: none;
        padding: 10px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 600;
        text-transform: none !important;
        font-variant: normal !important;  
        letter-spacing: normal;
        font-size: 14px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        height: 42px;
        width: 100%;
        min-width: 200px;
        box-sizing: border-box;
    }
    
    .filter-btn:hover {
        background: #005177;
    }
    
    .thoikhoabieu-tuan {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .tkb-day {
        background: #fff;
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .tkb-day h3 {
        margin: 0;
        padding: 10px 15px;
        background: #f2f2f2;
        border-bottom: 1px solid #ddd;
        font-size: 18px;
        color: #333;
        font-weight: 600;
    }
    .tkb-item {
        display: flex;
        padding: 12px 15px;
        border-bottom: 1px solid #eee;
    }
    .tkb-item:last-child {
        border-bottom: none;
    }
    .tkb-time {
        flex: 0 0 100px;
        font-weight: bold;
        color: #0073aa;
    }
    .tkb-details {
        flex: 1;
    }
    .tkb-course {
        margin-bottom: 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .tkb-actions-inline {
        display: flex;
        gap: 5px;
    }
    .tkb-view-link, .tkb-edit-link {
        color: #0073aa;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 4px;
        transition: all 0.2s ease;
    }
    .tkb-view-link {
        background-color: #f0f7fa;
        border: 1px solid #0073aa;
    }
    .tkb-edit-link {
        background-color: #fff6e5;
        border: 1px solid #ffba00;
        color: #ffba00;
    }
    .tkb-view-link:hover {
        background-color: #0073aa;
        color: #fff;
    }
    .tkb-edit-link:hover {
        background-color: #ffba00;
        color: #fff;
    }
    .tkb-info {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        font-size: 14px;
        color: #555;
    }
    .tkb-info strong {
        color: #333;
    }
    .no-data {
        padding: 20px;
        background: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 5px;
        text-align: center;
    }
    @media (max-width: 768px) {
        .filter-group {
            width: 100%;
            margin-right: 0;
        }
        .tkb-item {
            flex-direction: column;
        }
        .tkb-time {
            margin-bottom: 10px;
        }
        .tkb-info {
            flex-direction: column;
            gap: 5px;
        }
    }
    
    @media (min-width: 769px) {
        .filter-form {
            align-items: flex-end;
        }
        .filter-group.filter-action {
            margin-top: auto;
        }
    }
    
    /* Form Popup Styles */
    .tkb-popup-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        justify-content: center;
        align-items: center;
        overflow-y: auto;
        overscroll-behavior: contain;
    }
    
    .tkb-popup-container {
        background-color: #fff;
        width: 90%;
        max-width: 800px;
        border-radius: 8px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        margin: 30px auto;
        animation: tkbFadeIn 0.3s ease;
        overscroll-behavior: contain;
    }
    
    @keyframes tkbFadeIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .tkb-popup-header {
        padding: 20px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .tkb-popup-header h3 {
        margin: 0;
        font-size: 1.5rem;
        color: #23282d;
    }
    
    .tkb-popup-close {
        background: none;
        border: none;
        font-size: 24px;
        line-height: 1;
        cursor: pointer;
        color: #666;
        padding: 0;
    }
    
    .tkb-popup-close:hover {
        color: #dc3545;
    }
    
    .tkb-popup-content {
        padding: 20px;
        max-height: 70vh;
        overflow-y: auto;
        overscroll-behavior: contain;
    }
    
    .tkb-form-row {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .tkb-form-group {
        margin-bottom: 20px;
        flex: 1;
    }
    
    .tkb-form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
    }
    
    .tkb-form-group .required {
        color: #d63638;
        margin-left: 3px;
    }
    
    .tkb-input {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 15px;
        transition: all 0.3s ease;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.07);
        background-color: #fff;
    }
    
    .tkb-input:focus {
        border-color: #00a0d2;
        outline: none;
        box-shadow: 0 0 0 1px #00a0d2;
    }
    
    .tkb-form-actions {
        display: flex;
        gap: 15px;
        margin-top: 20px;
        justify-content: flex-end;
    }
    
    .tkb-submit-btn {
        background-color: #00a0d2;
        color: #fff;
        border: none;
        padding: 12px 25px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        font-size: 15px;
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
    
    .tkb-submit-btn .dashicons {
        margin-right: 8px;
        font-size: 18px;
        width: 18px;
        height: 18px;
    }
    
    .tkb-cancel-btn {
        background-color: #f7f7f7;
        color: #555;
        border: 1px solid #ddd;
        padding: 12px 25px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        font-size: 15px;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
    }
    
    .tkb-cancel-btn:hover {
        background-color: #e9e9e9;
        color: #333;
    }
    
    .tkb-cancel-btn .dashicons {
        margin-right: 8px;
        font-size: 18px;
        width: 18px;
        height: 18px;
    }
    
    @media (max-width: 768px) {
        .tkb-form-row {
            flex-direction: column;
            gap: 15px;
        }
        
        .tkb-popup-container {
            width: 95%;
            margin: 10px auto;
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
    
    .tkb-active {
        display: flex !important;
    }

    .tkb-delete-btn {
        background-color: #dc3545;
        color: #fff;
        border: none;
        padding: 12px 25px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        font-size: 15px;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(220, 53, 69, 0.2);
    }

    .tkb-delete-btn:hover {
        background-color: #c82333;
        transform: translateY(-2px);
        box-shadow: 0 6px 8px rgba(220, 53, 69, 0.25);
    }

    .tkb-delete-btn .dashicons {
        margin-right: 8px;
        font-size: 18px;
        width: 18px;
        height: 18px;
    }

    /* Spinner hiển thị khi đang tải dữ liệu */
    .tkb-loading {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 10;
    }

    .tkb-loading-spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #0073aa;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Styles for notifications */
    .tkb-message {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 5px;
        font-weight: 500;
        animation: tkbFadeIn 0.5s ease;
    }

    .tkb-message-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .tkb-message-error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Lấy các phần tử cần thiết
        const addButton = document.getElementById('tkb-add-new-btn');
        const addPopup = document.getElementById('tkb-add-form-popup');
        const editPopup = document.getElementById('tkb-edit-form-popup');
        const deleteForm = document.getElementById('tkb-delete-form');
        const deleteButton = document.getElementById('tkb-delete-btn');
        const editButtons = document.querySelectorAll('.tkb-edit-link');
        const popupContents = document.querySelectorAll('.tkb-popup-content');
        const closeButtons = document.querySelectorAll('.tkb-popup-close');
        
        // Hiển thị popup thêm mới khi nhấp vào nút thêm mới
        if(addButton) {
            addButton.addEventListener('click', function(e) {
                e.preventDefault();
                addPopup.classList.add('tkb-active');
                document.body.style.overflow = 'hidden'; // Ngăn cuộn trang nền
            });
        }
        
        // Hiển thị popup sửa khi nhấp vào nút sửa
        editButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const tkbId = this.getAttribute('data-tkb-id');
                loadTkbData(tkbId);
            });
        });
        
        // Xử lý nút xóa lịch học
        if(deleteButton) {
            deleteButton.addEventListener('click', function(e) {
                e.preventDefault();
                const tkbId = document.getElementById('edit_tkb_id').value;
                
                if(confirm('Bạn có chắc chắn muốn xóa lịch học này không?')) {
                    document.getElementById('delete_tkb_id').value = tkbId;
                    deleteForm.submit();
                }
            });
        }
        
        // Đóng popup khi nhấp vào nút đóng
        closeButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                addPopup.classList.remove('tkb-active');
                editPopup.classList.remove('tkb-active');
                document.body.style.overflow = ''; // Cho phép cuộn lại
            });
        });
        
        // Đóng popup khi nhấp vào vùng overlay
        document.querySelectorAll('.tkb-popup-overlay').forEach(function(popup) {
            popup.addEventListener('click', function(e) {
                if (e.target === this) {
                    addPopup.classList.remove('tkb-active');
                    editPopup.classList.remove('tkb-active');
                    document.body.style.overflow = ''; // Cho phép cuộn lại
                }
            });
        });
        
        // Ngăn sự kiện cuộn trang lan sang phần tử cha khi cuộn đến cuối
        popupContents.forEach(function(content) {
            content.addEventListener('wheel', function(e) {
                const scrollTop = this.scrollTop;
                const scrollHeight = this.scrollHeight;
                const height = this.clientHeight;
                const delta = e.deltaY;
                
                // Ngăn sự kiện nếu đang cuộn xuống tại cuối hoặc cuộn lên tại đầu
                if ((scrollTop === 0 && delta < 0) || 
                    (scrollTop + height >= scrollHeight && delta > 0)) {
                    e.preventDefault();
                }
            }, { passive: false });
        });
        
        // Hàm tải dữ liệu lịch học để chỉnh sửa
        function loadTkbData(tkbId) {
            // Tạo loading spinner
            const loadingElement = document.createElement('div');
            loadingElement.className = 'tkb-loading';
            loadingElement.innerHTML = '<div class="tkb-loading-spinner"></div>';
            
            // Thêm spinner vào form edit
            const editFormContent = editPopup.querySelector('.tkb-popup-content');
            editFormContent.style.position = 'relative';
            editFormContent.appendChild(loadingElement);
            
            // Hiện popup
            editPopup.classList.add('tkb-active');
            document.body.style.overflow = 'hidden';
            
            // Reset form trước khi tải dữ liệu mới
            document.getElementById('edit_tkb_id').value = tkbId;
            document.getElementById('delete_tkb_id').value = tkbId;
            
            // Gửi AJAX request để lấy dữ liệu
            const xhr = new XMLHttpRequest();
            xhr.open('POST', ajaxurl || '/wp-admin/admin-ajax.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    // Xóa spinner
                    editFormContent.removeChild(loadingElement);
                    
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                fillEditForm(response.data);
                            } else {
                                alert('Không thể tải dữ liệu lịch học. Vui lòng thử lại sau.');
                            }
                        } catch (e) {
                            console.error('Error parsing JSON:', e);
                            alert('Đã xảy ra lỗi khi xử lý dữ liệu. Vui lòng thử lại sau.');
                        }
                    } else {
                        alert('Đã xảy ra lỗi khi tải dữ liệu. Vui lòng thử lại sau.');
                    }
                }
            };
            
            // Tạo dữ liệu gửi đi
            const data = 'action=get_tkb_data&tkb_id=' + tkbId + '&nonce=' + document.querySelector('[name="tkb_nonce"]').value;
            xhr.send(data);
        }
        
        // Hàm điền dữ liệu vào form chỉnh sửa
        function fillEditForm(data) {
            document.getElementById('edit_mon_hoc').value = data.mon_hoc;
            document.getElementById('edit_lop').value = data.lop;
            document.getElementById('edit_giang_vien').value = data.giang_vien || '';
            document.getElementById('edit_thu').value = data.thu;
            document.getElementById('edit_gio_bat_dau').value = data.gio_bat_dau;
            document.getElementById('edit_gio_ket_thuc').value = data.gio_ket_thuc;
            document.getElementById('edit_phong').value = data.phong || '';
            document.getElementById('edit_tuan_hoc').value = data.tuan_hoc || '';
        }
    });
</script> 