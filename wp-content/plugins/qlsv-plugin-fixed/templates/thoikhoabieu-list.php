<?php
/**
 * Template hiển thị thời khóa biểu dạng danh sách
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
?>

<div class="thoikhoabieu-container">
    <!-- Bộ lọc thời khóa biểu -->
    <div class="thoikhoabieu-filter">
        <form class="filter-form" method="get" action="<?php echo esc_url(get_permalink()); ?>">
            <?php 
            // Giữ các tham số URL khác (nếu cần)
            foreach ($_GET as $key => $value) {
                if (!in_array($key, array('lop', 'monhoc', 'view', 'teacher'))) {
                    echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
                }
            }
            ?>
            
            <div class="filter-group">
                <label for="lop_filter"><?php esc_html_e('Lớp:', 'qlsv'); ?></label>
                <select name="lop" id="lop_filter">
                    <option value="0"><?php esc_html_e('-- Tất cả lớp --', 'qlsv'); ?></option>
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
            
            <div class="filter-group">
                <label for="monhoc_filter"><?php esc_html_e('Môn học:', 'qlsv'); ?></label>
                <select name="monhoc" id="monhoc_filter">
                    <option value="0"><?php esc_html_e('-- Tất cả môn học --', 'qlsv'); ?></option>
                    <?php foreach ($all_courses as $course) : ?>
                        <option value="<?php echo esc_attr($course->ID); ?>" <?php selected($selected_course, $course->ID); ?>>
                            <?php echo esc_html($course->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="teacher_filter"><?php esc_html_e('Giáo viên:', 'qlsv'); ?></label>
                <select name="teacher" id="teacher_filter">
                    <option value="0"><?php esc_html_e('-- Tất cả giáo viên --', 'qlsv'); ?></option>
                    <?php foreach ($all_teachers as $teacher) : ?>
                        <option value="<?php echo esc_attr($teacher->ID); ?>" <?php selected($selected_teacher, $teacher->ID); ?>>
                            <?php echo esc_html($teacher->display_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="view_filter"><?php esc_html_e('Hiển thị:', 'qlsv'); ?></label>
                <select name="view" id="view_filter">
                    <option value="tuan" <?php selected(isset($_GET['view']) ? $_GET['view'] : '', 'tuan'); ?>>
                        <?php esc_html_e('Theo tuần', 'qlsv'); ?>
                    </option>
                    <option value="danh_sach" <?php selected(isset($_GET['view']) ? $_GET['view'] : 'danh_sach', 'danh_sach'); ?>>
                        <?php esc_html_e('Danh sách', 'qlsv'); ?>
                    </option>
                </select>
            </div>
            
            <div class="filter-group">
                <button type="submit" class="filter-btn" style="text-transform: none !important; font-variant: normal !important;"><span><?php esc_html_e('Lọc', 'qlsv'); ?></span></button>
            </div>
        </form>
    </div>
    
    <?php if (empty($tkb_data)) : ?>
        <p class="no-data"><?php esc_html_e('Không có thời khóa biểu nào phù hợp với điều kiện.', 'qlsv'); ?></p>
    <?php else : ?>
        <!-- Hiển thị thời khóa biểu dạng danh sách -->
        <div class="thoikhoabieu-list">
            <table class="tkb-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Thứ', 'qlsv'); ?></th>
                        <th><?php esc_html_e('Thời gian', 'qlsv'); ?></th>
                        <th><?php esc_html_e('Môn học', 'qlsv'); ?></th>
                        <th><?php esc_html_e('Lớp', 'qlsv'); ?></th>
                        <th><?php esc_html_e('Phòng', 'qlsv'); ?></th>
                        <th><?php esc_html_e('Giảng viên', 'qlsv'); ?></th>
                        <th><?php esc_html_e('Tuần học', 'qlsv'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tkb_data as $tkb_item) : ?>
                        <tr>
                            <td><?php echo esc_html($tkb_item['thu']); ?></td>
                            <td><?php echo esc_html($tkb_item['gio_bat_dau']) . ' - ' . esc_html($tkb_item['gio_ket_thuc']); ?></td>
                            <td><?php echo esc_html($tkb_item['mon_hoc']); ?></td>
                            <td><?php echo esc_html($tkb_item['lop']); ?></td>
                            <td><?php echo !empty($tkb_item['phong']) ? esc_html($tkb_item['phong']) : 'N/A'; ?></td>
                            <td><?php echo esc_html($tkb_item['giang_vien']); ?></td>
                            <td><?php echo !empty($tkb_item['tuan_hoc']) ? esc_html($tkb_item['tuan_hoc']) : 'Tất cả'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<style>
    /* CSS cơ bản cho bộ lọc (giữ giống với template thoikhoabieu-tuan.php) */
    .thoikhoabieu-container {
        margin-bottom: 30px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    }
    .thoikhoabieu-filter {
        margin-bottom: 20px;
        padding: 15px;
        background: #f5f5f5;
        border-radius: 5px;
    }
    .filter-form {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
    }
    .filter-group {
        margin-right: 15px;
        margin-bottom: 10px;
    }
    .filter-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    .filter-group select {
        padding: 8px;
        min-width: 200px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .filter-btn {
        background: #0073aa;
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
        text-transform: none !important;
        font-variant: normal !important;  
        letter-spacing: normal;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    }
    .filter-btn:hover {
        background: #005177;
    }
    
    /* CSS cho bảng thời khóa biểu */
    .thoikhoabieu-list {
        overflow-x: auto;
    }
    .tkb-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background: #fff;
    }
    .tkb-table th, 
    .tkb-table td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: left;
    }
    .tkb-table th {
        background-color: #f2f2f2;
        font-weight: bold;
    }
    .tkb-table tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    .tkb-table tr:hover {
        background-color: #f5f5f5;
    }
    
    /* CSS cho thông báo không có dữ liệu */
    .no-data {
        padding: 20px;
        background: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 5px;
        text-align: center;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .filter-group {
            width: 100%;
            margin-right: 0;
        }
        .tkb-table {
            font-size: 14px;
        }
    }
    
    /* Fix for button text case issue */
    button.filter-btn {
        text-transform: none !important;
        font-variant: normal !important;
    }
</style> 