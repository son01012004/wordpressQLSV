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
$selected_class = isset($atts['lop_id']) ? $atts['lop_id'] : 0;
$selected_course = isset($atts['monhoc_id']) ? $atts['monhoc_id'] : 0;
?>

<div class="thoikhoabieu-container">
    <!-- Bộ lọc thời khóa biểu -->
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
                    <option value="tuan" <?php selected(isset($atts['loai_view']) ? $atts['loai_view'] : 'tuan', 'tuan'); ?>>
                        <?php esc_html_e('Theo tuần', 'qlsv'); ?>
                    </option>
                    <option value="danh_sach" <?php selected(isset($atts['loai_view']) ? $atts['loai_view'] : 'tuan', 'danh_sach'); ?>>
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
                    
                    <?php foreach ($tkb_data[$day] as $tkb_item) : ?>
                        <div class="tkb-item">
                            <div class="tkb-time">
                                <?php echo esc_html($tkb_item['gio_bat_dau']) . ' - ' . esc_html($tkb_item['gio_ket_thuc']); ?>
                            </div>
                            
                            <div class="tkb-details">
                                <div class="tkb-course">
                                    <strong><?php echo esc_html($tkb_item['mon_hoc']); ?></strong>
                                </div>
                                
                                <div class="tkb-info">
                                    <span class="tkb-class">Lớp: <?php echo esc_html($tkb_item['lop']); ?></span>
                                    
                                    <?php if (!empty($tkb_item['phong'])) : ?>
                                        <span class="tkb-room">Phòng: <?php echo esc_html($tkb_item['phong']); ?></span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($tkb_item['giang_vien'])) : ?>
                                        <span class="tkb-lecturer">GV: <?php echo esc_html($tkb_item['giang_vien']); ?></span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($tkb_item['tuan_hoc'])) : ?>
                                        <span class="tkb-weeks">Tuần: <?php echo esc_html($tkb_item['tuan_hoc']); ?></span>
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

<style>
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
    }
    .tkb-item {
        display: flex;
        padding: 10px 15px;
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
        margin-bottom: 5px;
    }
    .tkb-info {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        font-size: 0.9em;
        color: #666;
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
            margin-bottom: 5px;
        }
        .tkb-info {
            flex-direction: column;
            gap: 5px;
        }
    }
</style> 