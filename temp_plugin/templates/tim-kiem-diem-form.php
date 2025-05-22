<?php
/**
 * Template form tìm kiếm điểm
 *
 * @package QLSV
 */

// Ngăn chặn truy cập trực tiếp
if (!defined('WPINC')) {
    die;
}

// Nhận các tham số từ shortcode
$students = isset($students) ? $students : array();
$courses = isset($courses) ? $courses : array();
$classes = isset($classes) ? $classes : array();
$selected_student = isset($selected_student) ? $selected_student : 0;
$selected_course = isset($selected_course) ? $selected_course : 0;
$selected_class = isset($selected_class) ? $selected_class : 0;
?>

<div class="tim-kiem-diem-container">
    <h3><?php esc_html_e('Tìm kiếm bảng điểm', 'qlsv'); ?></h3>
    <form class="tim-kiem-diem-form" method="get">
        <?php 
        // Giữ các tham số URL khác (nếu cần)
        foreach ($_GET as $key => $value) {
            if (!in_array($key, ['sinhvien', 'monhoc', 'lop', 'submit'])) {
                echo '<input type="hidden" name="'.esc_attr($key).'" value="'.esc_attr($value).'">';
            }
        }
        ?>
        
        <div class="form-row">
            <div class="form-group">
                <label for="sinhvien"><?php esc_html_e('Sinh viên:', 'qlsv'); ?></label>
                <select name="sinhvien" id="sinhvien">
                    <option value="0"><?php esc_html_e('-- Tất cả sinh viên --', 'qlsv'); ?></option>
                    <?php foreach ($students as $student) : ?>
                        <option value="<?php echo esc_attr($student->ID); ?>" <?php selected($selected_student, $student->ID); ?>>
                            <?php echo esc_html($student->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="monhoc"><?php esc_html_e('Môn học:', 'qlsv'); ?></label>
                <select name="monhoc" id="monhoc">
                    <option value="0"><?php esc_html_e('-- Tất cả môn học --', 'qlsv'); ?></option>
                    <?php foreach ($courses as $course) : ?>
                        <option value="<?php echo esc_attr($course->ID); ?>" <?php selected($selected_course, $course->ID); ?>>
                            <?php echo esc_html($course->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="lop"><?php esc_html_e('Lớp:', 'qlsv'); ?></label>
                <select name="lop" id="lop">
                    <option value="0"><?php esc_html_e('-- Tất cả lớp --', 'qlsv'); ?></option>
                    <?php foreach ($classes as $class) : ?>
                        <option value="<?php echo esc_attr($class->ID); ?>" <?php selected($selected_class, $class->ID); ?>>
                            <?php echo esc_html($class->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>&nbsp;</label>
                <button type="submit" name="submit" class="search-btn"><?php esc_html_e('Tìm kiếm', 'qlsv'); ?></button>
            </div>
        </div>
    </form>
</div>

<style>
    .tim-kiem-diem-container {
        margin-bottom: 30px;
        padding: 20px;
        background: #f9f9f9;
        border-radius: 5px;
        border: 1px solid #ddd;
    }
    .tim-kiem-diem-container h3 {
        margin-top: 0;
        margin-bottom: 20px;
    }
    .tim-kiem-diem-form .form-row {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -10px;
    }
    .tim-kiem-diem-form .form-group {
        padding: 0 10px;
        margin-bottom: 15px;
        flex: 1;
        min-width: 200px;
    }
    .tim-kiem-diem-form label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    .tim-kiem-diem-form select {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .tim-kiem-diem-form .search-btn {
        background-color: #0073aa;
        color: #fff;
        border: none;
        padding: 8px 15px;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
        transition: background-color 0.3s;
        width: 100%;
    }
    .tim-kiem-diem-form .search-btn:hover {
        background-color: #005177;
    }
    @media (max-width: 768px) {
        .tim-kiem-diem-form .form-group {
            min-width: 100%;
        }
    }
</style> 