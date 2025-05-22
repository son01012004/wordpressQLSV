<?php
/**
 * Template hiển thị trang archive điểm danh
 *
 * @package QLSV
 */

// Giải pháp sửa lỗi: Thiết lập một post giả để tránh lỗi từ theme Rishi
global $post;
if (!isset($post) || empty($post)) {
    // Tạo một đối tượng post để tránh lỗi
    $post = new stdClass();
    $post->ID = 0;
    $post->post_type = 'diemdanh';
    $post->post_title = 'Điểm Danh';
    $post->post_name = 'diemdanh';
    $post->post_content = '';
    $post->comment_count = 0;
    $post->post_author = 1;
    $post->post_date = date('Y-m-d H:i:s');
    $post->post_date_gmt = date('Y-m-d H:i:s');
}

get_header();

// Kiểm tra quyền truy cập
if (!is_user_logged_in()) {
    ?>
    <div class="qlsv-container">
        <div class="qlsv-thong-bao">
            <p><?php esc_html_e('Bạn cần đăng nhập để xem điểm danh.', 'qlsv'); ?></p>
            <p><a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="button"><?php esc_html_e('Đăng nhập', 'qlsv'); ?></a></p>
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
    wp_reset_postdata();
}

// Lấy danh sách lớp để hiển thị
$lop_args = array(
    'post_type' => 'lop',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC'
);
$lop_query = new WP_Query($lop_args);

?>

<div class="qlsv-container">
    <h1 class="qlsv-page-title"><?php esc_html_e('Điểm Danh', 'qlsv'); ?></h1>
    
    <?php
    // Nếu là sinh viên (không phải admin hoặc giáo viên)
    if ($is_student && !$is_admin && !$is_teacher) {
        // Tìm ID sinh viên dựa trên email
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
            $student_lop_id = get_field('lop', $student_id);
            wp_reset_postdata();
            
            echo '<div class="student-info-header">';
            echo '<h2>' . esc_html__('Sinh viên:', 'qlsv') . ' ' . esc_html($student_name) . '</h2>';
            if ($student_lop_id) {
                echo '<p>' . esc_html__('Lớp:', 'qlsv') . ' ' . get_the_title($student_lop_id) . '</p>';
            }
            echo '</div>';
            
            // Tính toán thống kê điểm danh
            if ($student_lop_id) {
                // Lấy tất cả điểm danh của lớp
                $diemdanh_args = array(
                    'post_type' => 'diemdanh',
                    'posts_per_page' => -1,
                    'meta_query' => array(
                        array(
                            'key' => 'lop',
                            'value' => $student_lop_id,
                            'compare' => '='
                        )
                    ),
                    'orderby' => 'date',
                    'order' => 'DESC'
                );
                
                $diemdanh_query = new WP_Query($diemdanh_args);
                
                if ($diemdanh_query->have_posts()) {
                    $total_sessions = $diemdanh_query->post_count;
                    $present_count = 0;
                    $absent_count = 0;
                    $subjects = array(); // Để lưu các môn học đã điểm danh
                    
                    while ($diemdanh_query->have_posts()) {
                        $diemdanh_query->the_post();
                        $diemdanh_id = get_the_ID();
                        $mon_hoc_id = get_field('mon_hoc', $diemdanh_id);
                        
                        // Thêm môn học vào danh sách
                        if (!isset($subjects[$mon_hoc_id])) {
                            $subjects[$mon_hoc_id] = array(
                                'name' => get_the_title($mon_hoc_id),
                                'total' => 0,
                                'present' => 0,
                                'absent' => 0
                            );
                        }
                        
                        // Lấy trạng thái điểm danh của sinh viên
                        $sinh_vien_status = get_post_meta($diemdanh_id, 'sinh_vien_status', true);
                        $status = isset($sinh_vien_status[$student_id]) ? $sinh_vien_status[$student_id] : 'absent';
                        
                        // Cập nhật số liệu thống kê
                        $subjects[$mon_hoc_id]['total']++;
                        
                        if ($status === 'present') {
                            $present_count++;
                            $subjects[$mon_hoc_id]['present']++;
                        } else {
                            $absent_count++;
                            $subjects[$mon_hoc_id]['absent']++;
                        }
                    }
                    wp_reset_postdata();
                    
                    // Tính tỷ lệ điểm danh
                    $attendance_rate = $total_sessions > 0 ? round(($present_count / $total_sessions) * 100, 2) : 0;
                    
                    // Hiển thị thống kê tổng quát
                    echo '<div class="attendance-stats">';
                    echo '<h3>' . esc_html__('Thống kê điểm danh', 'qlsv') . '</h3>';
                    
                    echo '<div class="stats-overview">';
                    echo '<div class="stat-card">';
                    echo '<div class="stat-value">' . count($subjects) . '</div>';
                    echo '<div class="stat-label">' . esc_html__('Môn học', 'qlsv') . '</div>';
                    echo '</div>';
                    
                    echo '<div class="stat-card">';
                    echo '<div class="stat-value">' . $total_sessions . '</div>';
                    echo '<div class="stat-label">' . esc_html__('Tổng số buổi', 'qlsv') . '</div>';
                    echo '</div>';
                    
                    echo '<div class="stat-card">';
                    echo '<div class="stat-value">' . $present_count . '</div>';
                    echo '<div class="stat-label">' . esc_html__('Có mặt', 'qlsv') . '</div>';
                    echo '</div>';
                    
                    echo '<div class="stat-card">';
                    echo '<div class="stat-value">' . $absent_count . '</div>';
                    echo '<div class="stat-label">' . esc_html__('Vắng mặt', 'qlsv') . '</div>';
                    echo '</div>';
                    
                    echo '<div class="stat-card attendance-rate">';
                    echo '<div class="stat-value">' . $attendance_rate . '%</div>';
                    echo '<div class="stat-label">' . esc_html__('Tỷ lệ điểm danh', 'qlsv') . '</div>';
                    echo '</div>';
                    echo '</div>'; // End stats-overview
                    
                    // Hiển thị chi tiết theo môn học
                    echo '<h3>' . esc_html__('Chi tiết theo môn học', 'qlsv') . '</h3>';
                    echo '<div class="subject-stats">';
                    
                    foreach ($subjects as $mon_hoc_id => $subject) {
                        $subject_rate = $subject['total'] > 0 ? round(($subject['present'] / $subject['total']) * 100, 2) : 0;
                        $rate_class = $subject_rate >= 80 ? 'good-rate' : ($subject_rate >= 50 ? 'medium-rate' : 'bad-rate');
                        
                        echo '<div class="subject-card">';
                        echo '<h4>' . esc_html($subject['name']) . '</h4>';
                        echo '<div class="subject-details">';
                        echo '<div><span>' . esc_html__('Tổng số buổi:', 'qlsv') . '</span> ' . $subject['total'] . '</div>';
                        echo '<div><span>' . esc_html__('Có mặt:', 'qlsv') . '</span> ' . $subject['present'] . '</div>';
                        echo '<div><span>' . esc_html__('Vắng mặt:', 'qlsv') . '</span> ' . $subject['absent'] . '</div>';
                        echo '<div class="subject-rate ' . $rate_class . '"><span>' . esc_html__('Tỷ lệ điểm danh:', 'qlsv') . '</span> ' . $subject_rate . '%</div>';
                        echo '</div>';
                        
                        // Link xem chi tiết
                        $detail_url = add_query_arg(array(
                            'mon_hoc' => $mon_hoc_id,
                            'tab' => 'view'
                        ), get_permalink());
                        echo '<a href="' . esc_url($detail_url) . '" class="button">' . esc_html__('Xem chi tiết', 'qlsv') . '</a>';
                        echo '</div>';
                    }
                    
                    echo '</div>'; // End subject-stats
                    echo '</div>'; // End attendance-stats
                    
                } else {
                    echo '<div class="qlsv-thong-bao">';
                    echo '<p>' . esc_html__('Chưa có dữ liệu điểm danh nào cho lớp của bạn.', 'qlsv') . '</p>';
                    echo '</div>';
                }
            }
            
            // Hiển thị điểm danh của sinh viên
            echo do_shortcode('[qlsv_diemdanh_list sinhvien_id="' . $student_id . '"]');
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
        // Hiển thị danh sách lớp để chọn
        if ($lop_query->have_posts()) {
            echo '<div class="class-selection">';
            echo '<h2>' . esc_html__('Chọn lớp để điểm danh', 'qlsv') . '</h2>';
            echo '<div class="class-grid">';
            
            while ($lop_query->have_posts()) {
                $lop_query->the_post();
                $lop_id = get_the_ID();
                $lop_name = get_the_title();
                
                echo '<div class="class-card">';
                echo '<h3>' . esc_html($lop_name) . '</h3>';
                
                // Danh sách môn học để điểm danh cho lớp này
                $monhoc_args = array(
                    'post_type' => 'monhoc',
                    'posts_per_page' => -1,
                    'orderby' => 'title',
                    'order' => 'ASC'
                );
                $monhoc_query = new WP_Query($monhoc_args);
                
                if ($monhoc_query->have_posts()) {
                    echo '<ul class="subject-list">';
                    while ($monhoc_query->have_posts()) {
                        $monhoc_query->the_post();
                        $monhoc_id = get_the_ID();
                        $monhoc_name = get_the_title();
                        
                        // URL để tạo điểm danh mới cho lớp và môn học này
                        $diemdanh_url = home_url('/diemdanh/') . '?lop=' . $lop_id . '&mon_hoc=' . $monhoc_id;
                        
                        echo '<li><a href="' . esc_url($diemdanh_url) . '">' . esc_html($monhoc_name) . '</a></li>';
                    }
                    echo '</ul>';
                } else {
                    echo '<p>' . esc_html__('Không có môn học nào.', 'qlsv') . '</p>';
                }
                wp_reset_postdata();
                
                echo '</div>'; // End class-card
            }
            echo '</div>'; // End class-grid
            echo '</div>'; // End class-selection
        } else {
            echo '<div class="qlsv-thong-bao">';
            echo '<p>' . esc_html__('Không có lớp nào được tạo.', 'qlsv') . '</p>';
            echo '</div>';
        }
        wp_reset_postdata();
        
        // Hiển thị form tạo điểm danh mới
        $selected_lop = isset($_GET['lop']) ? intval($_GET['lop']) : 0;
        $selected_monhoc = isset($_GET['mon_hoc']) ? intval($_GET['mon_hoc']) : 0;
        
        if ($selected_lop && $selected_monhoc) {
            echo '<div class="diemdanh-form-container">';
            echo '<h2>' . esc_html__('Điểm danh lớp', 'qlsv') . ' ' . get_the_title($selected_lop) . ' - ' . get_the_title($selected_monhoc) . '</h2>';
            
            // Hiển thị shortcode form điểm danh với lớp và môn học đã chọn
            echo do_shortcode('[qlsv_diemdanh_form lop_id="' . $selected_lop . '" mon_hoc_id="' . $selected_monhoc . '"]');
            
            // Hiển thị danh sách điểm danh đã có của lớp và môn học này
            echo '<h3>' . esc_html__('Danh sách điểm danh đã tạo', 'qlsv') . '</h3>';
            
            // Lấy các bản ghi điểm danh cho lớp và môn học này
            $diemdanh_args = array(
                'post_type' => 'diemdanh',
                'posts_per_page' => -1,
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => 'lop',
                        'value' => $selected_lop,
                        'compare' => '='
                    ),
                    array(
                        'key' => 'mon_hoc',
                        'value' => $selected_monhoc,
                        'compare' => '='
                    )
                ),
                'orderby' => 'meta_value',
                'meta_key' => 'ngay',
                'order' => 'DESC'
            );
            
            $diemdanh_query = new WP_Query($diemdanh_args);
            
            if ($diemdanh_query->have_posts()) {
                echo '<table class="diemdanh-table">';
                echo '<thead>';
                echo '<tr>';
                echo '<th>' . esc_html__('Ngày', 'qlsv') . '</th>';
                echo '<th>' . esc_html__('Tiêu đề', 'qlsv') . '</th>';
                echo '<th>' . esc_html__('Thao tác', 'qlsv') . '</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';
                
                while ($diemdanh_query->have_posts()) {
                    $diemdanh_query->the_post();
                    $diemdanh_id = get_the_ID();
                    $ngay = get_field('ngay', $diemdanh_id);
                    $ngay_format = $ngay ? date_i18n('d/m/Y', strtotime($ngay)) : 'N/A';
                    
                    echo '<tr>';
                    echo '<td>' . $ngay_format . '</td>';
                    echo '<td>' . get_the_title() . '</td>';
                    echo '<td>';
                    echo '<a href="' . get_permalink() . '" class="button">' . esc_html__('Xem', 'qlsv') . '</a> ';
                    echo '<a href="' . get_edit_post_link() . '" class="button button-secondary">' . esc_html__('Sửa', 'qlsv') . '</a>';
                    echo '</td>';
                    echo '</tr>';
                }
                
                echo '</tbody>';
                echo '</table>';
            } else {
                echo '<div class="qlsv-thong-bao">';
                echo '<p>' . esc_html__('Chưa có buổi điểm danh nào được tạo cho lớp và môn học này.', 'qlsv') . '</p>';
                echo '</div>';
            }
            wp_reset_postdata();
            
            echo '</div>'; // End diemdanh-form-container
        }
    } 
    // Trường hợp khác
    else {
        ?>
        <div class="qlsv-thong-bao">
            <p><?php esc_html_e('Bạn không có quyền xem điểm danh.', 'qlsv'); ?></p>
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
    
    .diemdanh-table {
        width: 100% !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
        font-size: 14px !important;
        box-shadow: 0 2px 5px rgba(0,0,0,0.08) !important;
        border-radius: 8px !important;
        overflow: hidden !important;
        margin-bottom: 30px !important;
    }
    
    .diemdanh-table th, 
    .diemdanh-table td {
        padding: 12px 15px !important;
        text-align: left !important;
        border-bottom: 1px solid #eee !important;
    }
    
    .diemdanh-table th {
        background-color: #f8f9fa !important;
        font-weight: 600 !important;
        color: #495057 !important;
        text-transform: uppercase !important;
        font-size: 12px !important;
        letter-spacing: 0.5px !important;
    }
    
    .diemdanh-table tr:last-child td {
        border-bottom: none !important;
    }
    
    .diemdanh-table tr:hover {
        background-color: rgba(0,123,255,0.04) !important;
    }
    
    .status-present {
        color: #28a745 !important;
        font-weight: 600 !important;
    }
    
    .status-absent {
        color: #dc3545 !important;
        font-weight: 600 !important;
    }
    
    .class-selection {
        margin-bottom: 30px !important;
    }
    
    .class-grid {
        display: grid !important;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)) !important;
        gap: 20px !important;
        margin-top: 20px !important;
    }
    
    .class-card {
        background: #fff !important;
        border-radius: 8px !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
        padding: 20px !important;
        transition: transform 0.2s !important;
    }
    
    .class-card:hover {
        transform: translateY(-5px) !important;
    }
    
    .class-card h3 {
        margin-top: 0 !important;
        border-bottom: 1px solid #eee !important;
        padding-bottom: 10px !important;
        margin-bottom: 15px !important;
        color: #333 !important;
    }
    
    .subject-list {
        list-style: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    
    .subject-list li {
        margin-bottom: 10px !important;
    }
    
    .subject-list a {
        display: block !important;
        padding: 8px 12px !important;
        background: #f5f5f5 !important;
        border-radius: 4px !important;
        color: #333 !important;
        text-decoration: none !important;
        transition: all 0.2s !important;
    }
    
    .subject-list a:hover {
        background: #e0e0e0 !important;
    }
    
    .diemdanh-form-container {
        background: #fff !important;
        padding: 20px !important;
        border-radius: 8px !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
        margin-top: 30px !important;
    }
    
    .button {
        display: inline-block !important;
        padding: 8px 16px !important;
        background: #f0f0f0 !important;
        color: #333 !important;
        text-decoration: none !important;
        border-radius: 4px !important;
        border: 1px solid #ddd !important;
        cursor: pointer !important;
        margin-right: 5px !important;
    }
    
    .button-secondary {
        background: #e0e0e0 !important;
    }
    
    /* Attendance Statistics Styles */
    .attendance-stats {
        margin-bottom: 30px !important;
    }
    
    .stats-overview {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 15px !important;
        margin-bottom: 25px !important;
    }
    
    .stat-card {
        background: #fff !important;
        border-radius: 8px !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
        padding: 15px !important;
        min-width: 120px !important;
        flex: 1 !important;
        text-align: center !important;
    }
    
    .stat-value {
        font-size: 28px !important;
        font-weight: bold !important;
        margin-bottom: 5px !important;
    }
    
    .stat-label {
        font-size: 14px !important;
        color: #666 !important;
    }
    
    .attendance-rate .stat-value {
        color: #007bff !important;
    }
    
    .subject-stats {
        display: grid !important;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)) !important;
        gap: 20px !important;
    }
    
    .subject-card {
        background: #fff !important;
        border-radius: 8px !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
        padding: 15px !important;
    }
    
    .subject-card h4 {
        margin-top: 0 !important;
        margin-bottom: 15px !important;
        padding-bottom: 10px !important;
        border-bottom: 1px solid #eee !important;
        font-size: 18px !important;
    }
    
    .subject-details {
        margin-bottom: 15px !important;
    }
    
    .subject-details > div {
        margin-bottom: 5px !important;
    }
    
    .subject-details span {
        font-weight: bold !important;
        margin-right: 5px !important;
    }
    
    .subject-rate {
        font-weight: bold !important;
    }
    
    .good-rate {
        color: #28a745 !important;
    }
    
    .medium-rate {
        color: #ffc107 !important;
    }
    
    .bad-rate {
        color: #dc3545 !important;
    }
    
    .diemdanh-filter-form {
        margin-bottom: 20px !important;
        padding: 15px !important;
        background: #f9f9f9 !important;
        border-radius: 8px !important;
    }
    
    .diemdanh-filter-form .form-group {
        margin-bottom: 15px !important;
    }
    
    .diemdanh-filter-form label {
        display: block !important;
        margin-bottom: 5px !important;
        font-weight: bold !important;
    }
    
    .diemdanh-filter-form select {
        width: 100% !important;
        max-width: 300px !important;
        padding: 8px !important;
        border: 1px solid #ddd !important;
        border-radius: 4px !important;
    }
    
    .diemdanh-filter-form button {
        padding: 8px 16px !important;
        background: #0073aa !important;
        color: #fff !important;
        border: none !important;
        border-radius: 4px !important;
        cursor: pointer !important;
    }
</style>

<?php get_footer(); ?> 