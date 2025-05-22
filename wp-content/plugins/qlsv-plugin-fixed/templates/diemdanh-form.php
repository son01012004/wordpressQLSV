<?php
/**
 * Template hiển thị form điểm danh sinh viên
 *
 * @package QLSV
 */

// Ngăn chặn truy cập trực tiếp
if (!defined('WPINC')) {
    die;
}

// Dữ liệu từ shortcode
$lop_id = isset($lop_id) ? $lop_id : 0;
$mon_hoc_id = isset($mon_hoc_id) ? $mon_hoc_id : 0;
$all_classes = isset($all_classes) ? $all_classes : array();
$all_courses = isset($all_courses) ? $all_courses : array();
$selected_date = isset($selected_date) ? $selected_date : date('Y-m-d');
$buoi_hoc = isset($buoi_hoc) ? $buoi_hoc : 1;
$success_message = isset($success_message) ? $success_message : '';
?>

<div class="diemdanh-form-container">
    <?php if (!empty($success_message)) : ?>
        <div class="diemdanh-success-message">
            <p><?php echo esc_html($success_message); ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Form tìm kiếm lớp và môn học -->
    <div class="diemdanh-search-form">
        <form method="post" action="">
            <div class="form-row">
                <div class="form-group">
                    <label for="lop_id"><?php esc_html_e('Lớp:', 'qlsv'); ?></label>
                    <select name="lop_id" id="lop_id" class="form-control">
                        <option value=""><?php esc_html_e('-- Chọn lớp --', 'qlsv'); ?></option>
                        <?php foreach ($all_classes as $class) : ?>
                            <option value="<?php echo esc_attr($class->ID); ?>" <?php selected($lop_id, $class->ID); ?>>
                                <?php echo esc_html($class->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="mon_hoc_id"><?php esc_html_e('Môn học:', 'qlsv'); ?></label>
                    <select name="mon_hoc_id" id="mon_hoc_id" class="form-control">
                        <option value=""><?php esc_html_e('-- Chọn môn học --', 'qlsv'); ?></option>
                        <?php foreach ($all_courses as $course) : ?>
                            <option value="<?php echo esc_attr($course->ID); ?>" <?php selected($mon_hoc_id, $course->ID); ?>>
                                <?php echo esc_html($course->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="ngay_diemdanh"><?php esc_html_e('Ngày:', 'qlsv'); ?></label>
                    <input type="date" name="ngay_diemdanh" id="ngay_diemdanh" class="form-control" value="<?php echo esc_attr($selected_date); ?>">
                </div>
                
                <div class="form-group">
                    <label for="buoi_hoc"><?php esc_html_e('Buổi học:', 'qlsv'); ?></label>
                    <select name="buoi_hoc" id="buoi_hoc" class="form-control">
                        <?php for ($i = 1; $i <= 10; $i++) : ?>
                            <option value="<?php echo $i; ?>" <?php selected($buoi_hoc, $i); ?>>
                                <?php echo sprintf(__('Buổi %d', 'qlsv'), $i); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="button button-primary"><?php esc_html_e('Tìm kiếm', 'qlsv'); ?></button>
            </div>
        </form>
    </div>
    
    <?php
    // Hiển thị form điểm danh nếu đã chọn lớp và môn học
    if ($lop_id && $mon_hoc_id) :
        // Kiểm tra xem đã có điểm danh cho lớp, môn học, ngày và buổi học này chưa
        $args = array(
            'post_type' => 'diemdanh',
            'posts_per_page' => 1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'lop',
                    'value' => $lop_id,
                    'compare' => '='
                ),
                array(
                    'key' => 'mon_hoc',
                    'value' => $mon_hoc_id,
                    'compare' => '='
                ),
                array(
                    'key' => 'ngay',
                    'value' => $selected_date,
                    'compare' => '='
                ),
                array(
                    'key' => 'buoi_hoc',
                    'value' => $buoi_hoc,
                    'compare' => '='
                )
            )
        );
        
        $existing_query = new WP_Query($args);
        $existing_id = 0;
        $existing_students = array();
        $existing_note = '';
        
        if ($existing_query->have_posts()) {
            $existing_query->the_post();
            $existing_id = get_the_ID();
            $existing_students = get_field('sinh_vien_dd', $existing_id) ?: array();
            $existing_note = get_field('ghi_chu', $existing_id) ?: '';
            wp_reset_postdata();
        }
        
        // Lấy danh sách sinh viên trong lớp
        $args = array(
            'post_type' => 'sinhvien',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'lop',
                    'value' => $lop_id,
                    'compare' => '='
                )
            ),
            'orderby' => 'title',
            'order' => 'ASC'
        );
        
        $students_query = new WP_Query($args);
        
        if ($students_query->have_posts()) :
            // Lấy tên lớp và môn học
            $lop_title = get_the_title($lop_id);
            $mon_hoc_title = get_the_title($mon_hoc_id);
            $formatted_date = date_i18n('d/m/Y', strtotime($selected_date));
    ?>
            <div class="diemdanh-form-header">
                <h2><?php esc_html_e('Điểm danh sinh viên', 'qlsv'); ?></h2>
                <div class="diemdanh-info">
                    <p>
                        <strong><?php esc_html_e('Lớp:', 'qlsv'); ?></strong> <?php echo esc_html($lop_title); ?><br>
                        <strong><?php esc_html_e('Môn học:', 'qlsv'); ?></strong> <?php echo esc_html($mon_hoc_title); ?><br>
                        <strong><?php esc_html_e('Ngày:', 'qlsv'); ?></strong> <?php echo esc_html($formatted_date); ?><br>
                        <strong><?php esc_html_e('Buổi học:', 'qlsv'); ?></strong> <?php echo sprintf(__('Buổi %d', 'qlsv'), $buoi_hoc); ?>
                    </p>
                    <?php if ($existing_id) : ?>
                        <div class="diemdanh-existing-notice">
                            <p><?php esc_html_e('Đã có dữ liệu điểm danh cho buổi học này. Bạn có thể cập nhật lại.', 'qlsv'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <form method="post" action="" class="diemdanh-save-form">
                <?php wp_nonce_field('save_diemdanh_nonce', 'save_diemdanh_nonce'); ?>
                <input type="hidden" name="action" value="save_diemdanh">
                <input type="hidden" name="lop_id" value="<?php echo esc_attr($lop_id); ?>">
                <input type="hidden" name="mon_hoc_id" value="<?php echo esc_attr($mon_hoc_id); ?>">
                <input type="hidden" name="ngay_diemdanh" value="<?php echo esc_attr($selected_date); ?>">
                <input type="hidden" name="buoi_hoc" value="<?php echo esc_attr($buoi_hoc); ?>">
                <?php if ($existing_id) : ?>
                    <input type="hidden" name="existing_id" value="<?php echo esc_attr($existing_id); ?>">
                <?php endif; ?>
                
                <div class="diemdanh-table-container">
                    <table class="diemdanh-table">
                        <thead>
                            <tr>
                                <th class="column-stt"><?php esc_html_e('STT', 'qlsv'); ?></th>
                                <th class="column-masv"><?php esc_html_e('Mã SV', 'qlsv'); ?></th>
                                <th class="column-name"><?php esc_html_e('Họ tên', 'qlsv'); ?></th>
                                <th class="column-status"><?php esc_html_e('Trạng thái', 'qlsv'); ?></th>
                                <th class="column-note"><?php esc_html_e('Ghi chú', 'qlsv'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $count = 0;
                            while ($students_query->have_posts()) : 
                                $students_query->the_post();
                                $student_id = get_the_ID();
                                $ma_sv = get_field('ma_sinh_vien');
                                
                                // Kiểm tra trạng thái hiện tại nếu đã có điểm danh
                                $current_status = 'co_mat'; // Mặc định là có mặt
                                $current_note = '';
                                
                                if (!empty($existing_students)) {
                                    foreach ($existing_students as $existing_student) {
                                        if ($existing_student['sinh_vien_id'] == $student_id) {
                                            $current_status = $existing_student['trang_thai'];
                                            $current_note = isset($existing_student['ghi_chu']) ? $existing_student['ghi_chu'] : '';
                                            break;
                                        }
                                    }
                                }
                                
                                $count++;
                            ?>
                                <tr>
                                    <td class="column-stt"><?php echo $count; ?></td>
                                    <td class="column-masv"><?php echo esc_html($ma_sv); ?></td>
                                    <td class="column-name">
                                        <?php the_title(); ?>
                                        <input type="hidden" name="students[<?php echo $student_id; ?>][id]" value="<?php echo esc_attr($student_id); ?>">
                                    </td>
                                    <td class="column-status">
                                        <div class="status-options">
                                            <label>
                                                <input type="radio" name="students[<?php echo $student_id; ?>][status]" value="co_mat" <?php checked($current_status, 'co_mat'); ?>>
                                                <span class="status-label present"><?php esc_html_e('Có mặt', 'qlsv'); ?></span>
                                            </label>
                                            
                                            <label>
                                                <input type="radio" name="students[<?php echo $student_id; ?>][status]" value="vang" <?php checked($current_status, 'vang'); ?>>
                                                <span class="status-label absent"><?php esc_html_e('Vắng', 'qlsv'); ?></span>
                                            </label>
                                            
                                            <label>
                                                <input type="radio" name="students[<?php echo $student_id; ?>][status]" value="di_muon" <?php checked($current_status, 'di_muon'); ?>>
                                                <span class="status-label late"><?php esc_html_e('Đi muộn', 'qlsv'); ?></span>
                                            </label>
                                            
                                            <label>
                                                <input type="radio" name="students[<?php echo $student_id; ?>][status]" value="ve_som" <?php checked($current_status, 've_som'); ?>>
                                                <span class="status-label early"><?php esc_html_e('Về sớm', 'qlsv'); ?></span>
                                            </label>
                                            
                                            <label>
                                                <input type="radio" name="students[<?php echo $student_id; ?>][status]" value="co_phep" <?php checked($current_status, 'co_phep'); ?>>
                                                <span class="status-label excused"><?php esc_html_e('Có phép', 'qlsv'); ?></span>
                                            </label>
                                        </div>
                                    </td>
                                    <td class="column-note">
                                        <input type="text" name="students[<?php echo $student_id; ?>][note]" value="<?php echo esc_attr($current_note); ?>" placeholder="<?php esc_attr_e('Ghi chú', 'qlsv'); ?>">
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <?php wp_reset_postdata(); ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="diemdanh-form-footer">
                    <div class="form-group">
                        <label for="ghi_chu"><?php esc_html_e('Ghi chú chung:', 'qlsv'); ?></label>
                        <textarea name="ghi_chu" id="ghi_chu" rows="3" class="form-control"><?php echo esc_textarea($existing_note); ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="button button-primary"><?php echo $existing_id ? esc_html__('Cập nhật điểm danh', 'qlsv') : esc_html__('Lưu điểm danh', 'qlsv'); ?></button>
                        <button type="button" class="button button-secondary quick-mark" data-status="co_mat"><?php esc_html_e('Tất cả có mặt', 'qlsv'); ?></button>
                    </div>
                </div>
            </form>
        <?php else : ?>
            <div class="diemdanh-empty-message">
                <p><?php esc_html_e('Không tìm thấy sinh viên nào trong lớp này.', 'qlsv'); ?></p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
    /* CSS cho container chính */
    .diemdanh-form-container {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        margin-bottom: 30px;
    }
    
    /* CSS cho thông báo thành công */
    .diemdanh-success-message {
        background-color: #d4edda;
        color: #155724;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }
    
    /* CSS cho form tìm kiếm */
    .diemdanh-search-form {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 30px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .form-row {
        display: flex;
        flex-wrap: wrap;
        margin: -10px;
    }
    .form-group {
        flex: 1 1 200px;
        padding: 10px;
        margin-bottom: 0;
    }
    .form-control {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .form-actions {
        margin-top: 15px;
        text-align: right;
    }
    
    /* CSS cho header form điểm danh */
    .diemdanh-form-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
    }
    .diemdanh-form-header h2 {
        margin-top: 0;
        margin-bottom: 10px;
    }
    .diemdanh-info {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        border-left: 4px solid #3498db;
    }
    .diemdanh-info p {
        margin: 0;
    }
    .diemdanh-existing-notice {
        background-color: #fff3cd;
        color: #856404;
        padding: 10px;
        border-radius: 5px;
        margin-top: 10px;
    }
    
    /* CSS cho bảng điểm danh */
    .diemdanh-table-container {
        margin-bottom: 20px;
        overflow-x: auto;
    }
    .diemdanh-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #ddd;
    }
    .diemdanh-table th,
    .diemdanh-table td {
        padding: 12px 15px;
        text-align: left;
        border: 1px solid #ddd;
    }
    .diemdanh-table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    .diemdanh-table tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    .diemdanh-table tr:hover {
        background-color: #f5f5f5;
    }
    
    /* CSS cho các cột */
    .column-stt {
        width: 50px;
        text-align: center;
    }
    .column-masv {
        width: 100px;
    }
    .column-name {
        width: 200px;
    }
    .column-status {
        width: auto;
    }
    .column-note {
        width: 200px;
    }
    
    /* CSS cho options trạng thái */
    .status-options {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .status-options label {
        display: inline-flex;
        align-items: center;
        margin-right: 10px;
        cursor: pointer;
    }
    .status-options input[type="radio"] {
        margin-right: 5px;
    }
    .status-label {
        padding: 3px 8px;
        border-radius: 20px;
        font-size: 12px;
        white-space: nowrap;
    }
    .status-label.present {
        background-color: rgba(46, 204, 113, 0.2);
        color: #27ae60;
    }
    .status-label.absent {
        background-color: rgba(231, 76, 60, 0.2);
        color: #c0392b;
    }
    .status-label.late {
        background-color: rgba(243, 156, 18, 0.2);
        color: #d35400;
    }
    .status-label.early {
        background-color: rgba(155, 89, 182, 0.2);
        color: #8e44ad;
    }
    .status-label.excused {
        background-color: rgba(26, 188, 156, 0.2);
        color: #16a085;
    }
    
    /* CSS cho footer form */
    .diemdanh-form-footer {
        background-color: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-top: 20px;
    }
    .diemdanh-form-footer .form-group {
        margin-bottom: 15px;
    }
    .diemdanh-form-footer textarea {
        min-height: 80px;
    }
    .diemdanh-form-footer .form-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    /* CSS cho thông báo trống */
    .diemdanh-empty-message {
        background-color: #f8d7da;
        color: #721c24;
        padding: 15px;
        border-radius: 5px;
        margin-top: 20px;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .diemdanh-form-header {
            flex-direction: column;
        }
        .diemdanh-info {
            margin-top: 15px;
            width: 100%;
        }
        .status-options {
            flex-direction: column;
            gap: 5px;
        }
        .status-options label {
            margin-right: 0;
        }
    }
</style>

<script>
jQuery(document).ready(function($) {
    // Xử lý nút đánh dấu nhanh
    $('.quick-mark').on('click', function(e) {
        e.preventDefault();
        var status = $(this).data('status');
        
        // Đánh dấu tất cả các radio button có giá trị tương ứng
        $('input[type="radio"][value="' + status + '"]').prop('checked', true);
    });
});
</script> 