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

?>

<div class="qlsv-container">
    <h1 class="qlsv-page-title"><?php esc_html_e('Bảng điểm', 'qlsv'); ?></h1>
    
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
            wp_reset_postdata();
            
            echo '<div class="student-info-header">';
            echo '<h2>' . esc_html__('Sinh viên:', 'qlsv') . ' ' . esc_html($student_name) . '</h2>';
            echo '</div>';
            
            // Hiển thị bảng điểm của sinh viên
            echo do_shortcode('[qlsv_bang_diem sinhvien_id="' . $student_id . '"]');
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
        // Hiển thị form tìm kiếm điểm
        echo do_shortcode('[qlsv_tim_kiem_diem]');
    } 
    // Trường hợp khác
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
    
    /* Ensure our styling takes precedence */
    .bang-diem-container {
        margin-bottom: 30px !important;
    }
    
    .empty-results {
        padding: 40px !important;
        text-align: center !important;
        background: #f9f9f9 !important;
        border-radius: 8px !important;
        border: 1px dashed #ddd !important;
    }
    
    .empty-results .dashicons {
        font-size: 40px !important;
        height: 40px !important;
        width: 40px !important;
        color: #999 !important;
        margin-bottom: 10px !important;
    }
    
    .empty-results p {
        font-size: 16px !important;
        color: #666 !important;
        margin: 0 !important;
    }
    
    /* Thống kê */
    .academic-stats {
        margin-bottom: 20px !important;
    }
    
    .stats-summary {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 15px !important;
        margin-bottom: 20px !important;
    }
    
    .stat-box {
        flex: 1 !important;
        min-width: 120px !important;
        padding: 15px !important;
        background: #f5f5f5 !important;
        border-radius: 8px !important;
        text-align: center !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05) !important;
    }
    
    .stat-value {
        font-size: 24px !important;
        font-weight: bold !important;
        color: #333 !important;
    }
    
    .stat-label {
        font-size: 14px !important;
        color: #666 !important;
        margin-top: 5px !important;
    }
    
    .stat-box.passed .stat-value {
        color: #28a745 !important;
    }
    
    .stat-box.failed .stat-value {
        color: #dc3545 !important;
    }
    
    /* Bảng điểm */
    .table-responsive {
        overflow-x: auto !important;
    }
    
    .bang-diem-table {
        width: 100% !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
        font-size: 14px !important;
        box-shadow: 0 2px 5px rgba(0,0,0,0.08) !important;
        border-radius: 8px !important;
        overflow: hidden !important;
    }
    
    .bang-diem-table th, 
    .bang-diem-table td {
        padding: 12px 15px !important;
        text-align: left !important;
        border-bottom: 1px solid #eee !important;
    }
    
    .bang-diem-table th {
        background-color: #f8f9fa !important;
        font-weight: 600 !important;
        color: #495057 !important;
        text-transform: uppercase !important;
        font-size: 12px !important;
        letter-spacing: 0.5px !important;
    }
    
    .bang-diem-table tr:last-child td {
        border-bottom: none !important;
    }
    
    .bang-diem-table tr:hover {
        background-color: rgba(0,123,255,0.04) !important;
    }
    
    /* Cột điểm */
    .column-diem, .column-diemtb, .column-stt {
        text-align: center !important;
    }
    
    .diem-cell, .diem-tb-cell, .xeploai-cell {
        text-align: center !important;
    }
    
    .diem-value {
        display: inline-block !important;
        min-width: 40px !important;
        text-align: center !important;
    }
    
    .diem-tb {
        font-weight: bold !important;
    }
    
    /* Xếp loại */
    .xeploai-badge {
        display: inline-block !important;
        padding: 4px 8px !important;
        border-radius: 4px !important;
        font-weight: 500 !important;
        font-size: 12px !important;
        letter-spacing: 0.5px !important;
        min-width: 80px !important;
        text-align: center !important;
    }
    
    tr.gioi .xeploai-badge {
        background-color: #d4edda !important;
        color: #155724 !important;
    }
    
    tr.kha .xeploai-badge {
        background-color: #fff3cd !important;
        color: #856404 !important;
    }
    
    tr.trung-binh .xeploai-badge {
        background-color: #cce5ff !important;
        color: #004085 !important;
    }
    
    tr.yeu .xeploai-badge {
        background-color: #f8d7da !important;
        color: #721c24 !important;
    }
    
    /* Responsive */
    @media screen and (max-width: 768px) {
        .bang-diem-table thead {
            display: none !important;
        }
        
        .bang-diem-table, 
        .bang-diem-table tbody, 
        .bang-diem-table tr, 
        .bang-diem-table td {
            display: block !important;
            width: 100% !important;
        }
        
        .bang-diem-table tr {
            margin-bottom: 15px !important;
            border: 1px solid #ddd !important;
            border-radius: 8px !important;
            overflow: hidden !important;
        }
        
        .bang-diem-table td {
            text-align: right !important;
            padding: 10px 15px !important;
            position: relative !important;
            border-bottom: 1px solid #eee !important;
        }
        
        .bang-diem-table td:last-child {
            border-bottom: 0 !important;
        }
        
        .bang-diem-table td::before {
            content: attr(data-label) !important;
            position: absolute !important;
            left: 15px !important;
            width: 45% !important;
            font-weight: bold !important;
            text-align: left !important;
        }
        
        .diem-cell, .diem-tb-cell, .xeploai-cell {
            text-align: right !important;
        }
        
        .stats-summary {
            gap: 10px !important;
        }
        
        .stat-box {
            min-width: calc(50% - 10px) !important;
            flex: 0 0 calc(50% - 10px) !important;
            padding: 12px !important;
        }
    }
</style>

<?php get_footer(); ?> 