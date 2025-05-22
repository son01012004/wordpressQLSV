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
                        <button type="submit" class="tkb-filter-btn"><?php esc_html_e('Lọc', 'qlsv'); ?></button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Hiển thị controls cho admin -->
        <?php if ($is_admin): ?>
        <div class="tkb-admin-controls">
            <?php 
                // Sử dụng home_url hoặc get_site_url nếu add_edit_url trống
                $add_url = $add_edit_url ? $add_edit_url : get_site_url() . '/thoikhoabieu/';
                $add_url = add_query_arg('action', 'add', $add_url);
             ?>
            <a href="<?php echo esc_url($add_url); ?>" class="tkb-admin-btn">
                <span class="dashicons dashicons-plus"></span> <?php esc_html_e('Thêm lịch học mới', 'qlsv'); ?>
            </a>
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
                        <td><?php echo esc_html($tkb['gio_bat_dau'] . ' - ' . $tkb['gio_ket_thuc']); ?></td>
                        <td><?php echo esc_html($giang_vien_name); ?></td>
                        <td><?php echo esc_html($phong); ?></td>
                        <td><?php echo esc_html($tuan_hoc); ?></td>
                        <?php if ($is_admin || $is_teacher): ?>
                        <td class="tkb-actions">
                            <?php 
                                // Tạo URL xem chi tiết
                                $view_url = $add_edit_url ? $add_edit_url : get_site_url() . '/thoikhoabieu/';
                                $view_url = add_query_arg(array('action' => 'view', 'tkb_id' => $tkb_id), $view_url);
                                
                                // Tạo URL sửa 
                                $edit_url = $add_edit_url ? $add_edit_url : get_site_url() . '/thoikhoabieu/';
                                $edit_url = add_query_arg(array('action' => 'edit', 'tkb_id' => $tkb_id), $edit_url);
                            ?>
                            <a href="<?php echo esc_url($view_url); ?>" class="tkb-view-btn" title="<?php esc_attr_e('Xem chi tiết', 'qlsv'); ?>">
                                <span class="dashicons dashicons-visibility"></span>
                            </a>
                            <?php if ($is_admin): ?>
                            <a href="<?php echo esc_url($edit_url); ?>" class="tkb-edit-btn" title="<?php esc_attr_e('Sửa', 'qlsv'); ?>">
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
            padding: 15px;
            border-radius: 5px;
        }
        
        .tkb-filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
        }
        
        .tkb-filter-group {
            margin-bottom: 10px;
        }
        
        .tkb-filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .tkb-filter-group select {
            padding: 8px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-width: 200px;
        }
        
        .tkb-filter-btn {
            background-color: #0073aa;
            color: #fff;
            border: none;
            padding: 9px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .tkb-filter-btn:hover {
            background-color: #005177;
        }
        
        .tkb-admin-controls {
            margin-bottom: 20px;
        }
        
        .tkb-admin-btn {
            display: inline-flex;
            align-items: center;
            background-color: #0073aa;
            color: #fff;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 4px;
            font-weight: 600;
        }
        
        .tkb-admin-btn:hover {
            background-color: #005177;
            color: #fff;
        }
        
        .tkb-admin-btn .dashicons {
            margin-right: 5px;
        }
        
        .tkb-table-container {
            overflow-x: auto;
        }
        
        .tkb-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #ddd;
        }
        
        .tkb-table th, .tkb-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        
        .tkb-table th {
            background-color: #f5f5f5;
            font-weight: 600;
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
        }
        
        .tkb-actions a {
            display: inline-block;
            margin: 0 5px;
            text-decoration: none;
        }
        
        .tkb-view-btn {
            color: #0073aa;
        }
        
        .tkb-view-btn:hover {
            color: #005177;
        }
        
        .tkb-edit-btn {
            color: #ffba00;
        }
        
        .tkb-edit-btn:hover {
            color: #e5a700;
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
    </style>
    <?php
} 