<?php
/**
 * Template hiển thị danh sách sinh viên
 *
 * @package QLSV
 */

// Ngăn chặn truy cập trực tiếp
if (!defined('WPINC')) {
    die;
}

// Nhận các tham số từ shortcode
$args = isset($args) ? $args : array();
$students = isset($students) ? $students : array();
$classes = isset($classes) ? $classes : array();
$selected_class = isset($selected_class) ? $selected_class : 0;
?>

<div class="danh-sach-sinh-vien-container">
    <!-- Bộ lọc -->
    <div class="sinh-vien-filter">
        <form class="filter-form" method="get">
            <?php 
            // Giữ các tham số URL khác (nếu cần)
            foreach ($_GET as $key => $value) {
                if ($key !== 'lop') {
                    echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
                }
            }
            ?>
            
            <div class="filter-group">
                <label for="lop_filter"><?php esc_html_e('Lớp:', 'qlsv'); ?></label>
                <select name="lop" id="lop_filter">
                    <option value=""><?php esc_html_e('-- Tất cả lớp --', 'qlsv'); ?></option>
                    <?php foreach ($classes as $class) : ?>
                        <option value="<?php echo esc_attr($class->ID); ?>" <?php selected($selected_class, $class->ID); ?>>
                            <?php echo esc_html($class->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <button type="submit" class="filter-btn"><?php esc_html_e('Lọc', 'qlsv'); ?></button>
            </div>
        </form>
    </div>
    
    <?php if (empty($students)) : ?>
        <p><?php esc_html_e('Không có sinh viên nào.', 'qlsv'); ?></p>
    <?php else : ?>
        <!-- Bảng danh sách sinh viên -->
        <table class="sinh-vien-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('STT', 'qlsv'); ?></th>
                    <th><?php esc_html_e('Họ và tên', 'qlsv'); ?></th>
                    <th><?php esc_html_e('Mã sinh viên', 'qlsv'); ?></th>
                    <th><?php esc_html_e('Lớp', 'qlsv'); ?></th>
                    <th><?php esc_html_e('Khoa', 'qlsv'); ?></th>
                    <th><?php esc_html_e('Chi tiết', 'qlsv'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $stt = 1;
                foreach ($students as $student) : 
                    // Lấy thông tin sinh viên
                    $ho_ten = get_the_title($student->ID);
                    $ma_sinh_vien = get_field('ma_sinh_vien', $student->ID);
                    $lop_id = get_field('lop', $student->ID);
                    $khoa = get_field('khoa', $student->ID);
                    
                    // Lấy tên lớp
                    $ten_lop = '';
                    if ($lop_id) {
                        $ten_lop = get_the_title($lop_id);
                    }
                ?>
                <tr>
                    <td><?php echo $stt; ?></td>
                    <td><?php echo esc_html($ho_ten); ?></td>
                    <td><?php echo esc_html($ma_sinh_vien); ?></td>
                    <td><?php echo esc_html($ten_lop); ?></td>
                    <td><?php echo esc_html($khoa); ?></td>
                    <td>
                        <a href="<?php echo get_permalink($student->ID); ?>" class="view-btn">
                            <?php esc_html_e('Xem', 'qlsv'); ?>
                        </a>
                    </td>
                </tr>
                <?php 
                    $stt++;
                endforeach; 
                ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<style>
    .danh-sach-sinh-vien-container {
        margin-bottom: 30px;
    }
    .sinh-vien-filter {
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
    .sinh-vien-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    .sinh-vien-table th, 
    .sinh-vien-table td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: left;
    }
    .sinh-vien-table th {
        background-color: #f2f2f2;
        font-weight: bold;
    }
    .sinh-vien-table tr:hover {
        background-color: #f5f5f5;
    }
    .view-btn {
        display: inline-block;
        background: #4CAF50;
        color: white;
        text-decoration: none;
        padding: 5px 10px;
        border-radius: 4px;
        text-align: center;
    }
    .view-btn:hover {
        background: #45a049;
        color: white;
    }
</style> 