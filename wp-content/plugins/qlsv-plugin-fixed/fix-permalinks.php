<?php
/**
 * Script khắc phục các vấn đề về permalink và phân quyền trong plugin QLSV
 */

// Load WordPress
define('WP_USE_THEMES', false);
require_once('../../../wp-load.php');

// Kiểm tra quyền truy cập
if (!current_user_can('manage_options')) {
    wp_die('Bạn cần có quyền quản trị để truy cập trang này.');
}

// Tiêu đề trang
echo '<div style="padding: 20px; font-family: Arial, sans-serif;">';
echo '<h1>Công cụ sửa chữa Plugin QLSV</h1>';

// Kiểm tra xem có hành động nào được yêu cầu không
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action === 'fix_permalinks') {
    // Đăng ký lại custom post type cho diem
    register_post_type('diem', array(
        'public' => true,
        'has_archive' => true,
        'rewrite' => array(
            'slug' => 'diem',
            'with_front' => false
        ),
    ));
    
    // Đăng ký lại custom post type cho diemdanh
    register_post_type('diemdanh', array(
        'public' => true,
        'has_archive' => true,
        'rewrite' => array(
            'slug' => 'diemdanh',
            'with_front' => false
        ),
    ));
    
    // Cập nhật biến trong database
    update_option('qlsv_diem_flush_rewrite', false);
    
    // Flush rewrite rules
    flush_rewrite_rules();
    
    echo '<div style="background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border: 1px solid #c3e6cb; border-radius: 4px;">';
    echo '<h3>Đã khắc phục permalink!</h3>';
    echo '<p>Các permalink đã được cập nhật thành công. Hiện tại các URL sau nên hoạt động:</p>';
    echo '<ul>';
    echo '<li>' . home_url('/diem/') . ' - Trang danh sách điểm</li>';
    echo '<li>' . home_url('/diemdanh/') . ' - Trang danh sách điểm danh</li>';
    echo '</ul>';
    echo '</div>';
} elseif ($action === 'fix_roles') {
    // Thêm vai trò "giaovien" nếu chưa có
    $teacher_role = get_role('giaovien');
    if (!$teacher_role) {
        add_role('giaovien', 'Giáo Viên', array(
            'read' => true,
            'edit_posts' => true,
            'delete_posts' => false,
            'publish_posts' => true,
            'upload_files' => true,
        ));
    } else {
        // Cập nhật quyền cho vai trò giáo viên
        $teacher_role->add_cap('read');
        $teacher_role->add_cap('edit_posts');
        $teacher_role->add_cap('publish_posts');
        $teacher_role->add_cap('upload_files');
    }
    
    // Đảm bảo giáo viên có quyền chỉnh sửa điểm
    $teacher_role = get_role('giaovien');
    if ($teacher_role) {
        $teacher_role->add_cap('edit_diem');
        $teacher_role->add_cap('edit_diems');
        $teacher_role->add_cap('edit_published_diems');
        $teacher_role->add_cap('edit_private_diems');
        $teacher_role->add_cap('edit_others_diems');
        $teacher_role->add_cap('publish_diems');
    }
    
    // Cập nhật quyền cho admin
    $admin_role = get_role('administrator');
    if ($admin_role) {
        $admin_role->add_cap('edit_diem');
        $admin_role->add_cap('edit_diems');
        $admin_role->add_cap('edit_published_diems');
        $admin_role->add_cap('edit_private_diems');
        $admin_role->add_cap('edit_others_diems');
        $admin_role->add_cap('publish_diems');
        $admin_role->add_cap('delete_diems');
    }
    
    // Cập nhật quyền cho sinh viên (hạn chế)
    $student_role = get_role('student');
    if (!$student_role) {
        add_role('student', 'Sinh Viên', array(
            'read' => true
        ));
    }
    
    echo '<div style="background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border: 1px solid #c3e6cb; border-radius: 4px;">';
    echo '<h3>Đã cập nhật phân quyền!</h3>';
    echo '<p>Phân quyền đã được cập nhật:</p>';
    echo '<ul>';
    echo '<li><strong>Giáo viên:</strong> Có quyền thêm và chỉnh sửa điểm</li>';
    echo '<li><strong>Sinh viên:</strong> Chỉ có quyền xem điểm</li>';
    echo '<li><strong>Admin:</strong> Có đầy đủ quyền</li>';
    echo '</ul>';
    echo '</div>';
} elseif ($action === 'create_pages') {
    // Trang Điểm
    $diem_page_id = 0;
    $diem_page = get_page_by_path('ket-qua-diem');
    
    if (!$diem_page) {
        // Tạo trang mới
        $diem_page_id = wp_insert_post(array(
            'post_title'     => 'Kết Quả Điểm',
            'post_name'      => 'ket-qua-diem',
            'post_content'   => '[qlsv_tim_kiem_diem]',
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'comment_status' => 'closed'
        ));
        
        echo '<div style="background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border: 1px solid #c3e6cb; border-radius: 4px;">';
        echo '<h3>Đã tạo trang điểm!</h3>';
        echo '<p>Trang kết quả điểm đã được tạo thành công. Truy cập tại: <a href="' . get_permalink($diem_page_id) . '">Kết Quả Điểm</a></p>';
        echo '</div>';
    } else {
        $diem_page_id = $diem_page->ID;
        
        // Cập nhật nội dung trang
        wp_update_post(array(
            'ID' => $diem_page_id,
            'post_content' => '[qlsv_tim_kiem_diem]'
        ));
        
        echo '<div style="background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border: 1px solid #c3e6cb; border-radius: 4px;">';
        echo '<h3>Đã cập nhật trang điểm!</h3>';
        echo '<p>Trang kết quả điểm đã được cập nhật. Truy cập tại: <a href="' . get_permalink($diem_page_id) . '">Kết Quả Điểm</a></p>';
        echo '</div>';
    }
}

// Hiển thị form các hành động
echo '<h2>Các hành động sửa chữa</h2>';

echo '<div style="display: flex; gap: 20px; flex-wrap: wrap;">';

// Form sửa permalink
echo '<div style="background-color: #f5f5f5; border-radius: 8px; padding: 20px; flex: 1; min-width: 300px;">';
echo '<h3>1. Khắc phục lỗi 404 (Permalink)</h3>';
echo '<p>Sử dụng chức năng này nếu bạn gặp lỗi 404 khi truy cập trang điểm hoặc điểm danh.</p>';
echo '<form method="post">';
echo '<input type="hidden" name="action" value="fix_permalinks">';
echo '<button type="submit" style="background-color: #0073aa; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer;">Khắc phục permalink</button>';
echo '</form>';
echo '</div>';

// Form cập nhật phân quyền
echo '<div style="background-color: #f5f5f5; border-radius: 8px; padding: 20px; flex: 1; min-width: 300px;">';
echo '<h3>2. Cập nhật phân quyền</h3>';
echo '<p>Sử dụng chức năng này để đảm bảo giáo viên có quyền nhập điểm và sinh viên chỉ có quyền xem điểm.</p>';
echo '<form method="post">';
echo '<input type="hidden" name="action" value="fix_roles">';
echo '<button type="submit" style="background-color: #0073aa; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer;">Cập nhật phân quyền</button>';
echo '</form>';
echo '</div>';

// Form tạo trang điểm
echo '<div style="background-color: #f5f5f5; border-radius: 8px; padding: 20px; flex: 1; min-width: 300px;">';
echo '<h3>3. Tạo/cập nhật trang kết quả điểm</h3>';
echo '<p>Sử dụng chức năng này để tạo hoặc cập nhật trang kết quả điểm với shortcode đúng.</p>';
echo '<form method="post">';
echo '<input type="hidden" name="action" value="create_pages">';
echo '<button type="submit" style="background-color: #0073aa; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer;">Tạo/cập nhật trang</button>';
echo '</form>';
echo '</div>';

echo '</div>';

// Hướng dẫn thủ công
echo '<h2 style="margin-top: 30px;">Hướng dẫn khắc phục thủ công</h2>';
echo '<div style="background-color: #fff3cd; color: #856404; padding: 15px; margin-bottom: 20px; border: 1px solid #ffeeba; border-radius: 4px;">';
echo '<p><strong>1. Khắc phục lỗi 404:</strong></p>';
echo '<ol>';
echo '<li>Truy cập vào Cài đặt > Liên kết tĩnh</li>';
echo '<li>Không cần thay đổi cài đặt, chỉ cần nhấn "Lưu thay đổi" để làm mới permalink</li>';
echo '<li>Nếu vẫn gặp lỗi, hãy vào trang quản trị plugin, tắt và bật lại plugin QLSV</li>';
echo '</ol>';
echo '<p><strong>2. Phân quyền:</strong></p>';
echo '<ol>';
echo '<li>Đảm bảo bạn đã cài đặt plugin "Members" để quản lý vai trò và quyền</li>';
echo '<li>Truy cập vào Thành viên > Vai trò > Giáo viên > thêm quyền edit_posts, edit_diem, edit_diems</li>';
echo '<li>Đối với sinh viên, chỉ cho phép quyền read</li>';
echo '</ol>';
echo '</div>';

// Link quay lại
echo '<p style="margin-top: 20px;"><a href="' . admin_url() . '" style="background-color: #6c757d; color: white; text-decoration: none; padding: 10px 15px; border-radius: 4px;">Quay lại trang quản trị</a></p>';

echo '</div>'; // End main container
?> 