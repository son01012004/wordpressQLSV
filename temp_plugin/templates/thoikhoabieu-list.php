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
$selected_class = isset($atts['lop_id']) ? $atts['lop_id'] : 0;
$selected_course = isset($atts['monhoc_id']) ? $atts['monhoc_id'] : 0;
?>

<div class="thoikhoabieu-container">
    <!-- Bộ lọc thời khóa biểu (giống như trong template thoikhoabieu-tuan.php) -->
    <div class="thoikhoabieu-filter">
        <form class="filter-form" method="get">
            <?php 
            // Giữ các tham số URL khác (nếu cần)
            foreach ($_GET as $key => $value) {
                if (!in_array($key, array('lop', 'monhoc', 'view'))) {
                    echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
                }
            }
            ?>
            
            <div class="filter-group">
                <label for="lop_filter"><?php esc_html_e('Lớp:', 'qlsv'); ?></label>
                <select name="lop" id="lop_filter">
                    <option value="0"><?php esc_html_e('-- Tất cả lớp --', 'qlsv'); ?></option>
                    <?php foreach ($all_classes as $class) : ?>
                        <option value="<?php echo esc_attr($class->ID); ?>" <?php selected($selected_class, $class->ID); ?>>
                            <?php echo esc_html($class->post_title); ?>
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
                <label for="view_filter"><?php esc_html_e('Hiển thị:', 'qlsv'); ?></label>
                <select name="view" id="view_filter">
                    <option value="tuan" <?php selected(isset($atts['loai_view']) ? $atts['loai_view'] : 'danh_sach', 'tuan'); ?>>
                        <?php esc_html_e('Theo tuần', 'qlsv'); ?>
                    </option>
                    <option value="danh_sach" <?php selected(isset($atts['loai_view']) ? $atts['loai_view'] : 'danh_sach', 'danh_sach'); ?>>
                        <?php esc_html_e('Danh sách', 'qlsv'); ?>
                    </option>
                </select>
            </div>
            
            <div class="filter-group">
                <button type="submit" class="filter-btn"><?php esc_html_e('Lọc', 'qlsv'); ?></button>
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
                            <td><?php echo !empty($tkb_item['giang_vien']) ? esc_html($tkb_item['giang_vien']) : 'N/A'; ?></td>
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
</style> 