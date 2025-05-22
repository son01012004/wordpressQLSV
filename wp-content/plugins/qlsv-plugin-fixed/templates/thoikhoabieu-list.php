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

// Lấy đường dẫn đến trang thêm/sửa
$add_edit_page_id = get_option('qlsv_tkb_add_edit_page', 0);
$add_edit_url = $add_edit_page_id ? get_permalink($add_edit_page_id) : '';

// Debug info để kiểm tra settings
$display_debug = WP_DEBUG && current_user_can('administrator');
if ($display_debug) {
    echo '<div class="tkb-debug">';
    echo '<p>Debug Info:</p>';
    echo '<p>is_admin: ' . ($is_admin ? 'true' : 'false') . '</p>';
    echo '<p>add_edit_page_id: ' . $add_edit_page_id . '</p>';
    echo '<p>add_edit_url: ' . $add_edit_url . '</p>';
    echo '<p>list_page_id: ' . get_option('qlsv_tkb_list_page', 0) . '</p>';
    echo '</div>';
}

// Kiểm tra xem có dữ liệu không
if (empty($tkb_data)) {
    echo '<div class="qlsv-message">' . esc_html__('Không có lịch học nào.', 'qlsv') . '</div>';
} else {
    ?>
    
    <div class="tkb-container">
        <h2 class="tkb-title"><?php esc_html_e('Thời khóa biểu', 'qlsv'); ?></h2>
        
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
        
        <!-- Hiển thị bộ lọc nếu cần -->
        <div class="tkb-filters">
            <form method="get" action="">
                <?php
                // Giữ lại các query string khác
                foreach ($_GET as $key => $value) {
                    if (!in_array($key, array('lop', 'monhoc', 'teacher', 'view'))) {
                        echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
                    }
                }
                ?>
                
                <div class="tkb-filter-row">
                    <?php if ($is_admin || $is_teacher): ?>
                    <div class="tkb-filter-group">
                        <label for="lop"><?php esc_html_e('Lớp:', 'qlsv'); ?></label>
                        <select name="lop" id="lop">
                            <option value=""><?php esc_html_e('Tất cả lớp', 'qlsv'); ?></option>
                            <?php foreach ($all_classes as $class): 
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
                    
                    <div class="tkb-filter-group">
                        <label for="monhoc"><?php esc_html_e('Môn học:', 'qlsv'); ?></label>
                        <select name="monhoc" id="monhoc">
                            <option value=""><?php esc_html_e('Tất cả môn', 'qlsv'); ?></option>
                            <?php foreach ($all_courses as $course): ?>
                            <option value="<?php echo esc_attr($course->ID); ?>" <?php selected($selected_course, $course->ID); ?>>
                                <?php echo esc_html($course->post_title); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <?php if ($is_admin): ?>
                    <div class="tkb-filter-group">
                        <label for="teacher"><?php esc_html_e('Giảng viên:', 'qlsv'); ?></label>
                        <select name="teacher" id="teacher">
                            <option value=""><?php esc_html_e('Tất cả giảng viên', 'qlsv'); ?></option>
                            <?php foreach ($all_teachers as $teacher): ?>
                            <option value="<?php echo esc_attr($teacher->ID); ?>" <?php selected($selected_teacher, $teacher->ID); ?>>
                                <?php echo esc_html($teacher->display_name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                    <div class="tkb-filter-group">
                        <label for="view"><?php esc_html_e('Hiển thị:', 'qlsv'); ?></label>
                        <select name="view" id="view">
                            <option value="danh_sach" <?php selected(isset($_GET['view']) ? $_GET['view'] : 'danh_sach', 'danh_sach'); ?>>
                                <?php esc_html_e('Danh sách', 'qlsv'); ?>
                            </option>
                            <option value="tuan" <?php selected(isset($_GET['view']) ? $_GET['view'] : 'danh_sach', 'tuan'); ?>>
                                <?php esc_html_e('Theo tuần', 'qlsv'); ?>
                            </option>
                        </select>
                    </div>
                    
                    <div class="tkb-filter-action">
                        <button type="submit" class="tkb-filter-btn"><?php esc_html_e('Tìm kiếm', 'qlsv'); ?></button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Hiển thị controls cho admin -->
        <?php if ($is_admin): ?>
        <div class="tkb-admin-controls">
            <button type="button" id="tkb-add-new-btn" class="tkb-admin-btn">
                <span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e('Thêm lịch học mới', 'qlsv'); ?>
            </button>
        </div>
        <?php endif; ?>
        
        <!-- Hiển thị bảng thời khóa biểu -->
        <div class="tkb-table-container">
            <table class="tkb-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('STT', 'qlsv'); ?></th>
                        <th><?php esc_html_e('Môn học', 'qlsv'); ?></th>
                        <th><?php esc_html_e('Lớp', 'qlsv'); ?></th>
                        <th><?php esc_html_e('Thứ', 'qlsv'); ?></th>
                        <th><?php esc_html_e('Thời gian', 'qlsv'); ?></th>
                        <th><?php esc_html_e('Giảng viên', 'qlsv'); ?></th>
                        <th><?php esc_html_e('Phòng', 'qlsv'); ?></th>
                        <th><?php esc_html_e('Tuần học', 'qlsv'); ?></th>
                        <?php if ($is_admin || $is_teacher): ?>
                        <th><?php esc_html_e('Thao tác', 'qlsv'); ?></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $count = 0;
                    foreach ($tkb_data as $tkb): 
                        $count++;
                        $tkb_id = $tkb['ID'];
                        
                        // Lấy thông tin chi tiết
                        $mon_hoc_id = get_field('mon_hoc', $tkb_id);
                        $lop_id = get_field('lop', $tkb_id);
                        $giang_vien_id = get_field('giang_vien', $tkb_id);
                        $phong = get_field('phong', $tkb_id);
                        $tuan_hoc = get_field('tuan_hoc', $tkb_id);
                        
                        // Lấy tên các thực thể liên quan
                        $mon_hoc_name = $mon_hoc_id ? get_the_title($mon_hoc_id) : '';
                        
                        $lop_name = '';
                        if ($lop_id) {
                            $lop_name = get_field('ten_lop', $lop_id);
                            if (empty($lop_name)) {
                                $lop_name = get_the_title($lop_id);
                            }
                        }
                        
                        $giang_vien_name = '';
                        if ($giang_vien_id) {
                            $giang_vien = get_userdata($giang_vien_id);
                            if ($giang_vien) {
                                $giang_vien_name = $giang_vien->display_name;
                            }
                        }
                    ?>
                    <tr>
                        <td><?php echo esc_html($count); ?></td>
                        <td><?php echo esc_html($mon_hoc_name); ?></td>
                        <td><?php echo esc_html($lop_name); ?></td>
                        <td><?php echo esc_html($tkb['thu']); ?></td>
                        <td class="tkb-time-cell"><?php echo esc_html($tkb['gio_bat_dau'] . ' - ' . $tkb['gio_ket_thuc']); ?></td>
                        <td><?php echo esc_html($giang_vien_name); ?></td>
                        <td><?php echo esc_html($phong); ?></td>
                        <td class="tkb-week-cell"><?php echo esc_html($tuan_hoc); ?></td>
                        <?php if ($is_admin || $is_teacher): ?>
                        <td class="tkb-actions">
                            <?php 
                                // Tạo URL xem chi tiết - sử dụng trang thời khóa biểu chi tiết
                                $list_page = get_permalink(get_option('qlsv_tkb_list_page', ''));
                                $view_url = add_query_arg(array(
                                    'action' => 'view', 
                                    'tkb_id' => $tkb_id,
                                    'thoi-khoa-bieu-moi' => 'true'
                                ), $list_page);
                            ?>
                            <a href="<?php echo esc_url($view_url); ?>" class="tkb-view-btn" title="<?php esc_attr_e('Xem chi tiết', 'qlsv'); ?>">
                                <span class="dashicons dashicons-visibility"></span>
                            </a>
                            <?php if ($is_admin): ?>
                            <a href="#" class="tkb-edit-btn" title="<?php esc_attr_e('Sửa', 'qlsv'); ?>" 
                               data-tkb-id="<?php echo esc_attr($tkb_id); ?>"
                               data-mon-hoc="<?php echo esc_attr($mon_hoc_id); ?>"
                               data-lop="<?php echo esc_attr($lop_id); ?>"
                               data-giang-vien="<?php echo esc_attr($giang_vien_id); ?>"
                               data-thu="<?php echo esc_attr($tkb['thu']); ?>"
                               data-gio-bat-dau="<?php echo esc_attr($tkb['gio_bat_dau']); ?>"
                               data-gio-ket-thuc="<?php echo esc_attr($tkb['gio_ket_thuc']); ?>"
                               data-phong="<?php echo esc_attr($phong); ?>"
                               data-tuan-hoc="<?php echo esc_attr($tuan_hoc); ?>">
                                <span class="dashicons dashicons-edit"></span>
                            </a>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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
    <?php endif; ?>
    
    <style>
        .tkb-container {
            margin-bottom: 30px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        }
        
        .tkb-title {
            font-size: 24px;
            margin-bottom: 20px;
        }
        
        .tkb-filters {
            margin-bottom: 20px;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .tkb-filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: flex-end;
        }
        
        .tkb-filter-group {
            margin-bottom: 10px;
            flex: 1;
            min-width: 200px;
        }
        
        .tkb-filter-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .tkb-filter-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #fff;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
            font-size: 14px;
        }
        
        .tkb-filter-action {
            align-self: flex-end;
            margin-bottom: 10px;
        }
        
        .tkb-filter-btn {
            background-color: #0073aa;
            color: #fff;
            border: none;
            padding: 10px 25px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            text-transform: none !important;
            font-variant: normal !important;
            letter-spacing: normal;
            font-size: 15px;
            height: 42px;
        }
        
        .tkb-filter-btn:hover {
            background-color: #005177;
        }
        
        .tkb-admin-controls {
            margin-bottom: 25px;
            display: flex;
            justify-content: flex-end;
        }
        
        .tkb-admin-btn {
            display: inline-flex;
            align-items: center;
            background-color: #00a0d2;
            color: #fff;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 4px;
            font-weight: 600;
            box-shadow: 0 4px 6px rgba(0, 160, 210, 0.2);
            transition: all 0.3s ease;
            border: none;
            font-size: 15px;
        }
        
        .tkb-admin-btn:hover {
            background-color: #0091cd;
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
        
        .tkb-table-container {
            overflow-x: auto;
            margin-top: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            border-radius: 8px;
        }
        
        .tkb-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid #ddd;
            font-size: 14px;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .tkb-table th, .tkb-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
            border-right: 1px solid #ddd;
            text-align: center;
            vertical-align: middle;
        }
        
        .tkb-table th:last-child, .tkb-table td:last-child {
            border-right: none;
        }
        
        .tkb-table tr:last-child td {
            border-bottom: none;
        }
        
        .tkb-table th {
            background-color: #f5f5f5;
            font-weight: 600;
            color: #333;
            position: sticky;
            top: 0;
            z-index: 10;
            border-bottom: 2px solid #ddd;
        }
        
        .tkb-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .tkb-table tr:hover {
            background-color: #f1f1f1;
        }
        
        .tkb-actions {
            white-space: nowrap;
            text-align: center;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        
        .tkb-actions a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background-color: #f8f8f8;
            border-radius: 4px;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .tkb-view-btn {
            color: #0073aa;
            border: 1px solid #0073aa;
        }
        
        .tkb-view-btn:hover {
            color: #fff;
            background-color: #0073aa;
            transform: translateY(-2px);
        }
        
        .tkb-edit-btn {
            color: #ffba00;
            border: 1px solid #ffba00;
        }
        
        .tkb-edit-btn:hover {
            color: #fff;
            background-color: #ffba00;
            transform: translateY(-2px);
        }
        
        .tkb-table .dashicons {
            font-size: 18px;
            width: 18px;
            height: 18px;
            line-height: 1;
        }
        
        .qlsv-message {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-weight: 600;
            color: #666;
        }
        
        @media (max-width: 768px) {
            .tkb-filter-row {
                flex-direction: column;
                align-items: stretch;
            }
            
            .tkb-filter-group select {
                width: 100%;
                min-width: unset;
            }
            
            .tkb-table {
                font-size: 14px;
            }
            
            .tkb-table th, .tkb-table td {
                padding: 8px 5px;
            }
        }
        
        .tkb-time-cell {
            font-weight: 600;
            color: #0073aa;
        }
        
        .tkb-week-cell {
            font-weight: 600;
            color: #333;
            background-color: #f0f7fa;
        }
        
        .tkb-table tr td:first-child {
            font-weight: bold;
            background-color: #f9f9f9;
            width: 50px;
        }
        
        /* Thiết kế cho cột thao tác */
        th:last-child {
            min-width: 100px;
        }
        
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
            const editButtons = document.querySelectorAll('.tkb-edit-btn');
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
    <?php
} 