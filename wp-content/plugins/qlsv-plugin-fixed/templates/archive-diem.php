<?php
/**
 * Template hiển thị trang archive cho post type 'diem'
 *
 * @package QLSV
 */

get_header();

// Kiểm tra quyền truy cập
if (!is_user_logged_in()) {
    ?>
    <div class="qlsv-container">
        <div class="qlsv-thong-bao">
            <p><?php esc_html_e('Bạn cần đăng nhập để xem bảng điểm.', 'qlsv'); ?></p>
            <p><a href="<?php echo esc_url(wp_login_url('http://localhost/wordpressQLSV/')); ?>" class="button"><?php esc_html_e('Đăng nhập', 'qlsv'); ?></a></p>
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
        wp_reset_postdata();
    }
}

?>

<div class="qlsv-container">
    <h1 class="qlsv-page-title"><?php esc_html_e('Bảng điểm', 'qlsv'); ?></h1>
    
    <?php
    // Lấy tham số tìm kiếm từ URL với GET parameters trực tiếp
    $selected_student = isset($_GET['sinhvien']) ? intval($_GET['sinhvien']) : 0;
    $selected_course = isset($_GET['monhoc']) ? intval($_GET['monhoc']) : 0;
    $selected_class = isset($_GET['lop']) ? intval($_GET['lop']) : 0;
    $diem_page = isset($_GET['diem_page']) ? intval($_GET['diem_page']) : 1;
    
    // Nếu là sinh viên (không phải admin hoặc giáo viên)
    if ($is_student && !$is_admin && !$is_teacher) {
        // Tìm ID sinh viên dựa trên email
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
            
            // Hiển thị bảng điểm của sinh viên sử dụng phiên bản lite
            echo do_shortcode('[qlsv_bang_diem_lite sinhvien_id="' . $student_id . '"]');
        } else {
            ?>
            <div class="qlsv-thong-bao">
                <p><?php esc_html_e('Không tìm thấy thông tin sinh viên cho tài khoản này.', 'qlsv'); ?></p>
            </div>
            <?php
        }
    } 
    // Nếu là admin hoặc giáo viên
    elseif ($is_admin || $is_teacher) {
        // Form tìm kiếm điểm với hướng dẫn cụ thể
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
        // Hiển thị kết quả - luôn hiển thị bảng điểm (dù có tìm kiếm hay không)
        echo '<h3 style="margin-top: 30px; border-bottom: 2px solid #0073aa; padding-bottom: 8px;">Bảng điểm</h3>';
        
        // Hiển thị kết quả sử dụng shortcode 
        echo do_shortcode('[qlsv_bang_diem_lite sinhvien_id="' . $selected_student . '" monhoc_id="' . $selected_course . '" lop_id="' . $selected_class . '" page="' . $diem_page . '"]');
    } 
    // Trường hợp khác (không phải sinh viên, admin hoặc giáo viên)
    else {
        ?>
        <div class="qlsv-thong-bao">
            <p><?php esc_html_e('Bạn không có quyền xem bảng điểm.', 'qlsv'); ?></p>
        </div>
        <?php
    }
    ?>
</div>

<style>
    .qlsv-container {
        max-width: 1200px !important;
        margin: 0 auto !important;
        padding: 20px !important;
    }
    
    .qlsv-page-title {
        margin-bottom: 30px !important;
        font-size: 28px !important;
        color: #333 !important;
        border-bottom: 2px solid #f0f0f0 !important;
        padding-bottom: 15px !important;
    }
    
    .qlsv-thong-bao {
        padding: 20px !important;
        background: #f8f8f8 !important;
        border-left: 4px solid #ccc !important;
        margin-bottom: 20px !important;
    }
    
    .student-info-header {
        background: #f9f9f9 !important;
        padding: 15px 20px !important;
        border-radius: 8px !important;
        margin-bottom: 20px !important;
        border: 1px solid #eee !important;
    }
    
    .student-info-header h2 {
        margin: 0 !important;
        font-size: 20px !important;
        color: #333 !important;
    }
    
    /* Form tìm kiếm */
    .search-diem-form {
        margin-bottom: 30px !important;
    }
    
    .search-diem-form select, 
    .search-diem-form input, 
    .search-diem-form button {
        width: 100% !important;
        padding: 10px !important;
        border: 1px solid #ddd !important;
        border-radius: 4px !important;
    }
    
    .search-diem-form button {
        background: #0073aa !important;
        border: none !important;
        color: white !important;
        padding: 12px !important;
        cursor: pointer !important;
        border-radius: 4px !important;
        font-weight: 500 !important;
    }
    
    /* Đảm bảo bảng điểm hiện thị đúng */
    .diem-table {
        width: 100% !important;
        border-collapse: collapse !important;
        margin-top: 20px !important;
        border: 1px solid #dee2e6 !important;
    }
    
    .diem-table th,
    .diem-table td {
        border: 1px solid #dee2e6 !important;
        padding: 10px !important;
        text-align: left !important;
    }
    
    .diem-table th {
        background-color: #f8f9fa !important;
        font-weight: 600 !important;
    }
    
    .diem-table tr:nth-child(even) {
        background-color: #f8f9fa !important;
    }
    
    .diem-table tr:hover {
        background-color: #f1f1f1 !important;
    }
    
    /* CSS cho phân trang */
    .pagination {
        margin-top: 20px !important;
        text-align: center !important;
    }
    
    .pagination a {
        display: inline-block !important;
        margin: 0 3px !important;
        padding: 8px 12px !important;
        border: 1px solid #ddd !important;
        text-decoration: none !important;
        color: #0073aa !important;
        background-color: #fff !important;
        border-radius: 4px !important;
    }
    
    .pagination a:hover {
        background-color: #e9ecef !important;
    }
    
    .pagination a.current-page {
        background-color: #0073aa !important;
        color: #fff !important;
        border-color: #0073aa !important;
    }
    
    /* Đảm bảo full layout với header và footer */
    body.archive.post-type-archive-diem {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }
    
    body.archive.post-type-archive-diem #content,
    body.archive.post-type-archive-diem main,
    body.archive.post-type-archive-diem .rt-container-fluid,
    body.archive.post-type-archive-diem .rt-main {
        flex: 1;
    }
    
    body.archive.post-type-archive-diem header,
    body.archive.post-type-archive-diem footer {
        flex-shrink: 0;
        display: block !important;
        visibility: visible !important;
    }
    
    .qlsv-notice-info {
        background-color: #e7f5ff !important;
        border-left: 4px solid #0073aa !important;
    }
</style>

<?php
get_footer();
?> 