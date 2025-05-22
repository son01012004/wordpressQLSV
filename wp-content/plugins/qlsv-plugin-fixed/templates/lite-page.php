<?php
/**
 * Template Name: Kết quả học tập (Lite)
 * Description: Template hiển thị kết quả học tập với phiên bản tối ưu hóa bộ nhớ
 */

get_header();

// Kiểm tra quyền truy cập
if (!is_user_logged_in()) {
    ?>
    <div class="qlsv-container">
        <div class="qlsv-thong-bao">
            <p><?php esc_html_e('Bạn cần đăng nhập để sử dụng tính năng này.', 'qlsv'); ?></p>
            <p><a href="<?php echo esc_url(wp_login_url('http://localhost/wordpressQLSV/')); ?>" class="button button-primary"><?php esc_html_e('Đăng nhập', 'qlsv'); ?></a></p>
        </div>
    </div>
    <?php
    get_footer();
    return;
}

// Lấy thông tin người dùng hiện tại
$current_user = wp_get_current_user();
$user_roles = $current_user->roles;
$is_admin = in_array('administrator', $user_roles);
$is_teacher = in_array('giaovien', $user_roles);
$is_student = in_array('student', $user_roles);

// Kiểm tra xem có phải sinh viên không (nếu không có role 'student')
if (!$is_student) {
    $args = array(
        'post_type' => 'sinhvien',
        'posts_per_page' => 1,
        'meta_query' => array(
            array(
                'key' => 'email',
                'value' => $current_user->user_email,
                'compare' => '='
            )
        )
    );
    
    $student_query = new WP_Query($args);
    $is_student = $student_query->have_posts();
    
    if ($is_student) {
        $student_query->the_post();
        $student_id = get_the_ID();
        $student_name = get_the_title();
        wp_reset_postdata();
    }
}
?>

<div class="qlsv-container">
    <h1 class="qlsv-page-title"><?php esc_html_e('Kết quả học tập', 'qlsv'); ?></h1>
    
    <?php
    // Hiển thị thông báo trang đang sử dụng phiên bản nhẹ
    echo '<div class="notice notice-info" style="padding: 15px; background-color: #e7f5ff; border-left: 4px solid #0073aa; margin-bottom: 20px; border-radius: 4px;">
        <p><strong>Thông báo:</strong> Đang sử dụng phiên bản tối ưu hiển thị bảng điểm.</p>
    </div>';
    
    // Lấy tham số tìm kiếm từ URL
    $selected_student = isset($_GET['sinhvien']) ? intval($_GET['sinhvien']) : 0;
    $selected_course = isset($_GET['monhoc']) ? intval($_GET['monhoc']) : 0;
    $selected_class = isset($_GET['lop']) ? intval($_GET['lop']) : 0;
    $diem_page = isset($_GET['diem_page']) ? intval($_GET['diem_page']) : 1;
    
    // Nếu là sinh viên (không phải admin hoặc giáo viên)
    if ($is_student && !$is_admin && !$is_teacher) {
        // Tìm ID sinh viên dựa trên email nếu chưa có
        if (!isset($student_id)) {
            $args = array(
                'post_type' => 'sinhvien',
                'posts_per_page' => 1,
                'meta_query' => array(
                    array(
                        'key' => 'email',
                        'value' => $current_user->user_email,
                        'compare' => '='
                    )
                )
            );
            
            $student_query = new WP_Query($args);
            if ($student_query->have_posts()) {
                $student_query->the_post();
                $student_id = get_the_ID();
                $student_name = get_the_title();
                wp_reset_postdata();
            }
        }
        
        if (isset($student_id)) {
            echo '<div class="student-info-header">';
            echo '<h2>' . esc_html__('Sinh viên:', 'qlsv') . ' ' . esc_html($student_name) . '</h2>';
            echo '</div>';
            
            // Hiển thị bảng điểm của sinh viên
            echo do_shortcode('[qlsv_bang_diem_lite sinhvien_id="' . $student_id . '"]');
        } else {
            ?>
            <div class="qlsv-thong-bao qlsv-error">
                <p><?php esc_html_e('Không tìm thấy thông tin sinh viên cho tài khoản này.', 'qlsv'); ?></p>
            </div>
            <?php
        }
    } 
    // Nếu là admin hoặc giáo viên - hiển thị luôn bảng điểm
    elseif ($is_admin || $is_teacher) {
        // Form tìm kiếm điểm
        ?>
        <div class="search-form-container" style="margin-bottom: 30px; background: #f8f9fa; padding: 20px; border-radius: 8px;">
            <h3 style="margin-top: 0; margin-bottom: 15px; color: #0073aa;">Tìm kiếm kết quả học tập</h3>
            
            <form method="get" action="" class="search-diem-form">
                <div style="display: flex; flex-wrap: wrap; gap: 15px;">
                    <div style="flex: 1; min-width: 250px;">
                        <label for="sinhvien" style="display: block; margin-bottom: 8px; font-weight: 500;">Sinh viên:</label>
                        <select name="sinhvien" id="sinhvien" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="">-- Tất cả sinh viên --</option>
                            <?php
                            // Lấy danh sách sinh viên
                            global $wpdb;
                            $posts_table = $wpdb->posts;
                            
                            $sql = "SELECT ID, post_title FROM {$posts_table} WHERE post_type = 'sinhvien' AND post_status = 'publish' ORDER BY post_title ASC LIMIT 100";
                            $students = $wpdb->get_results($sql);
                            
                            foreach ($students as $student) {
                                $selected = ($selected_student == $student->ID) ? 'selected' : '';
                                echo '<option value="' . $student->ID . '" ' . $selected . '>' . $student->post_title . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div style="flex: 1; min-width: 250px;">
                        <label for="monhoc" style="display: block; margin-bottom: 8px; font-weight: 500;">Môn học:</label>
                        <select name="monhoc" id="monhoc" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="">-- Tất cả môn học --</option>
                            <?php
                            // Lấy danh sách môn học
                            $sql = "SELECT ID, post_title FROM {$posts_table} WHERE post_type = 'monhoc' AND post_status = 'publish' ORDER BY post_title ASC LIMIT 100";
                            $courses = $wpdb->get_results($sql);
                            
                            foreach ($courses as $course) {
                                $selected = ($selected_course == $course->ID) ? 'selected' : '';
                                echo '<option value="' . $course->ID . '" ' . $selected . '>' . $course->post_title . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div style="flex: 1; min-width: 250px;">
                        <label for="lop" style="display: block; margin-bottom: 8px; font-weight: 500;">Lớp:</label>
                        <select name="lop" id="lop" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="">-- Tất cả lớp --</option>
                            <?php
                            // Lấy danh sách lớp
                            $sql = "SELECT ID, post_title FROM {$posts_table} WHERE post_type = 'lop' AND post_status = 'publish' ORDER BY post_title ASC LIMIT 100";
                            $classes = $wpdb->get_results($sql);
                            
                            foreach ($classes as $class) {
                                $selected = ($selected_class == $class->ID) ? 'selected' : '';
                                echo '<option value="' . $class->ID . '" ' . $selected . '>' . $class->post_title . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div style="flex: 1; align-self: flex-end; min-width: 250px;">
                        <button type="submit" style="width: 100%; background: #0073aa; border: none; color: white; padding: 12px; cursor: pointer; border-radius: 4px; font-weight: 500;">Tìm kiếm</button>
                    </div>
                </div>
            </form>
        </div>
        
        <?php
        // Hiển thị kết quả - luôn hiển thị bảng điểm 
        echo '<h3 style="margin-top: 30px; border-bottom: 2px solid #0073aa; padding-bottom: 8px;">Bảng điểm</h3>';
        
        // Hiển thị bảng điểm dựa trên tiêu chí tìm kiếm nếu có, ngược lại hiển thị tất cả
        echo do_shortcode('[qlsv_bang_diem_lite sinhvien_id="' . $selected_student . '" monhoc_id="' . $selected_course . '" lop_id="' . $selected_class . '" page="' . $diem_page . '"]');
    } 
    // Trường hợp khác
    else {
        ?>
        <div class="qlsv-thong-bao qlsv-error">
            <p><?php esc_html_e('Bạn không có quyền xem bảng điểm.', 'qlsv'); ?></p>
        </div>
        <?php
    }
    ?>
</div>

<style>
    .qlsv-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .qlsv-page-title {
        margin-bottom: 30px;
        font-size: 28px;
        color: #333;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 15px;
    }
    
    .qlsv-thong-bao {
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 4px;
    }
    
    .qlsv-error {
        background-color: #f8d7da;
        border-left: 4px solid #dc3545;
    }
    
    .student-info-header {
        background: #f9f9f9;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid #eee;
    }
    
    .student-info-header h2 {
        margin: 0;
        font-size: 20px;
        color: #333;
    }
    
    /* Form tìm kiếm */
    .search-diem-form {
        margin-bottom: 30px;
    }
    
    .search-diem-form select, 
    .search-diem-form input, 
    .search-diem-form button {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    
    .search-diem-form button {
        background: #0073aa;
        border: none;
        color: white;
        padding: 12px;
        cursor: pointer;
        border-radius: 4px;
        font-weight: 500;
    }
    
    /* Đảm bảo bảng điểm hiện thị đúng */
    .diem-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    
    .diem-table th,
    .diem-table td {
        border: 1px solid #ddd;
        padding: 8px;
    }
    
    .diem-table th {
        background-color: #f2f2f2;
        font-weight: bold;
    }
    
    .diem-table tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    
    .diem-table tr:hover {
        background-color: #f1f1f1;
    }
</style>

<?php get_footer(); ?> 