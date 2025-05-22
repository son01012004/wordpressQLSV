<?php
/**
 * Trang hiển thị bảng điểm nhẹ
 * 
 * Trang này được thiết kế để tránh lỗi bộ nhớ
 * khi hiển thị dữ liệu điểm lớn
 */

// Kiểm tra nếu được truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

// Đảm bảo memory limit cao đủ
ini_set('memory_limit', '256M');

// Tăng thời gian thực thi
ini_set('max_execution_time', 300);

// Tải WordPress
require_once(dirname(__FILE__) . '/../../../wp-load.php');

// Lấy tiêu đề trang
$page_title = 'Kết Quả Học Tập - Phiên bản nhẹ';

// Kiểm tra đăng nhập
if (!is_user_logged_in()) {
    // Nếu không phải admin thì chuyển hướng đến trang đăng nhập
    if (!current_user_can('manage_options')) {
        wp_redirect(wp_login_url('http://localhost/wordpressQLSV/'));
        exit;
    }
}

// Header
get_header();
?>

    <div class="wrapper">
        <header>
            <h1><?php echo $page_title; ?></h1>
            <div class="breadcrumbs">
                <a href="<?php echo home_url(); ?>">Trang chủ</a> &raquo; <?php echo $page_title; ?>
            </div>
        </header>
        
        <main>
            <?php
            // Hiển thị thông báo trang đang sử dụng phiên bản nhẹ
            echo '<div class="notice notice-info" style="padding: 15px; background-color: #e7f5ff; border-left: 4px solid #0073aa; margin-bottom: 20px; border-radius: 4px;">
                <p><strong>Thông báo:</strong> Đang sử dụng phiên bản tối ưu hiển thị bảng điểm.</p>
            </div>';
            
            // Lấy thông tin người dùng hiện tại
            $current_user = wp_get_current_user();
            $user_roles = $current_user->roles;
            $is_admin = in_array('administrator', $user_roles);
            $is_teacher = in_array('giaovien', $user_roles);
            $is_student = in_array('student', $user_roles);
            
            // Kiểm tra xem có phải sinh viên không (nếu không có role 'student')
            if (!$is_student) {
                global $wpdb;
                $posts_table = $wpdb->posts;
                $meta_table = $wpdb->postmeta;
                
                $sql = $wpdb->prepare(
                    "SELECT p.ID 
                    FROM {$posts_table} p 
                    INNER JOIN {$meta_table} pm ON p.ID = pm.post_id 
                    WHERE p.post_type = 'sinhvien' 
                    AND p.post_status = 'publish' 
                    AND pm.meta_key = 'email' 
                    AND pm.meta_value = %s 
                    LIMIT 1",
                    $current_user->user_email
                );
                
                $student_id = $wpdb->get_var($sql);
                $is_student = !empty($student_id);
            }
            
            // Lấy tham số tìm kiếm từ URL
            $selected_student = isset($_GET['sinhvien']) ? intval($_GET['sinhvien']) : 0;
            $selected_course = isset($_GET['monhoc']) ? intval($_GET['monhoc']) : 0;
            $selected_class = isset($_GET['lop']) ? intval($_GET['lop']) : 0;
            
            // Nếu là sinh viên (không phải admin hoặc giáo viên)
            if ($is_student && !$is_admin && !$is_teacher) {
                // Hiển thị bảng điểm của sinh viên
                if (!empty($student_id)) {
                    echo '<div class="student-info-header" style="background: #f9f9f9; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #eee;">
                        <h2 style="margin: 0; font-size: 20px; color: #333;">Sinh viên: ' . get_the_title($student_id) . '</h2>
                    </div>';
                    
                    // Hiển thị bảng điểm
                    echo do_shortcode('[qlsv_bang_diem_lite sinhvien_id="' . $student_id . '"]');
                } else {
                    echo '<div class="qlsv-thong-bao" style="padding: 20px; background: #f8f8f8; border-left: 4px solid #ccc; margin-bottom: 20px;">
                        <p>Không tìm thấy thông tin sinh viên cho tài khoản này.</p>
                    </div>';
                }
            } 
            // Nếu là admin hoặc giáo viên
            elseif ($is_admin || $is_teacher) {
                // Hiển thị form tìm kiếm điểm
                echo do_shortcode('[qlsv_tim_kiem_diem_lite]');
            } 
            // Trường hợp khác
            else {
                echo '<div class="qlsv-thong-bao" style="padding: 20px; background: #f8f8f8; border-left: 4px solid #ccc; margin-bottom: 20px;">
                    <p>Bạn không có quyền xem bảng điểm.</p>
                </div>';
            }
            ?>
        </main>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> - Quản lý sinh viên - Phiên bản tối ưu</p>
        </footer>
    </div>
    
    <style>
        .wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }
        
        h1 {
            margin-bottom: 10px;
            color: #333;
        }
        
        .breadcrumbs {
            font-size: 14px;
            color: #666;
        }
        
        .breadcrumbs a {
            color: #0073aa;
            text-decoration: none;
        }
        
        main {
            margin-bottom: 40px;
        }
        
        footer {
            border-top: 1px solid #eee;
            padding-top: 20px;
            color: #666;
            text-align: center;
        }
        
        /* CSS cho tabs */
        .tabs-container { margin-bottom: 30px !important; overflow: hidden !important; }
        .tabs-nav { list-style: none !important; padding: 0 !important; margin: 0 !important; display: flex !important; border-bottom: 1px solid #ddd !important; }
        .tabs-nav li { margin-right: 0 !important; }
        .tabs-nav a { display: block !important; padding: 12px 20px !important; text-decoration: none !important; color: #333 !important; transition: all 0.3s ease !important; }
        .tabs-nav a:hover { background: #e9ecef !important; }
        .tabs-nav li.tab-active a { background: #fff !important; position: relative !important; color: #0073aa !important; font-weight: bold !important; border-top: 3px solid #0073aa !important; margin-top: -3px !important; }
        .tab-content { display: none !important; padding: 20px !important; animation: fadeIn 0.5s !important; }
        .tab-content.tab-active { display: block !important; }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        /* CSS cho bảng */
        .diem-table { border-collapse: collapse !important; width: 100% !important; margin-top: 20px !important; border: 1px solid #dee2e6 !important; }
        .diem-table th, .diem-table td { border: 1px solid #dee2e6 !important; padding: 10px !important; }
        .diem-table th { background-color: #f8f9fa !important; font-weight: 600 !important; text-align: left !important; }
        .diem-table tr:nth-child(even) { background-color: #f8f9fa !important; }
        .diem-table tr:hover { background-color: #f1f1f1 !important; }
        
        /* CSS cho phân trang */
        .pagination { margin-top: 20px !important; text-align: center !important; }
        .pagination a { display: inline-block !important; margin: 0 3px !important; padding: 8px 12px !important; border: 1px solid #ddd !important; text-decoration: none !important; color: #0073aa !important; background-color: #fff !important; border-radius: 4px !important; transition: all 0.3s ease !important; }
        .pagination a:hover { background-color: #e9ecef !important; }
        .pagination a.current-page { background-color: #0073aa !important; color: #fff !important; border-color: #0073aa !important; }
        
        /* Form tìm kiếm */
        .search-diem-form { margin-bottom: 30px !important; background: #f8f9fa !important; padding: 20px !important; border-radius: 8px !important; }
        .search-diem-form select, .search-diem-form input, .search-diem-form button { width: 100% !important; padding: 10px !important; border: 1px solid #ddd !important; border-radius: 4px !important; }
        .search-diem-form button { background: #0073aa !important; border: none !important; color: white !important; padding: 12px !important; cursor: pointer !important; border-radius: 4px !important; font-weight: 500 !important; }
    </style>

<?php
// Footer
get_footer();
?> 