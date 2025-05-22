<?php
/**
 * Tạo trang thông tin cá nhân
 * 
 * Tạo một trang WordPress để hiển thị thông tin người dùng
 * dựa theo vai trò của họ khi đăng nhập
 */

// Tạo trang thông tin cá nhân khi kích hoạt plugin
function qlsv_create_profile_page() {
    // Kiểm tra xem trang đã tồn tại chưa
    $page_exists = get_page_by_path('thong-tin-ca-nhan');
    
    if (!$page_exists) {
        // Tạo trang mới
        $page_data = array(
            'post_title'    => 'Thông tin cá nhân',
            'post_name'     => 'thong-tin-ca-nhan',
            'post_content'  => '[qlsv_user_profile]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_author'   => 1,
        );
        
        // Chèn trang vào cơ sở dữ liệu
        $page_id = wp_insert_post($page_data);
        
        if ($page_id) {
            // Có thể làm gì đó sau khi tạo trang thành công
            error_log('Đã tạo trang thông tin cá nhân: ID=' . $page_id);
        }
    }
} 