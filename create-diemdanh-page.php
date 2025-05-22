<?php
/**
 * Script để tạo trang Điểm Danh
 */

// Load WordPress
require_once('wp-load.php');

// Kiểm tra xem trang đã tồn tại chưa
$page = get_page_by_path('diemdanh');

if (!$page) {
    echo "Đang tạo trang Điểm Danh...\n";
    
    // Tạo trang mới
    $page_id = wp_insert_post(array(
        'post_title'     => 'Điểm Danh',
        'post_name'      => 'diemdanh',
        'post_content'   => '[qlsv_diemdanh_dashboard]',
        'post_status'    => 'publish',
        'post_type'      => 'page',
        'comment_status' => 'closed'
    ));
    
    if ($page_id && !is_wp_error($page_id)) {
        // Gán template
        $result = update_post_meta($page_id, '_wp_page_template', 'diemdanh-page.php');
        
        if ($result) {
            echo "Trang Điểm Danh đã được tạo thành công với ID: {$page_id}\n";
        } else {
            echo "Trang đã được tạo nhưng không thể gán template.\n";
        }
    } else {
        echo "Có lỗi khi tạo trang: ";
        if (is_wp_error($page_id)) {
            echo $page_id->get_error_message();
        }
        echo "\n";
    }
} else {
    echo "Trang Điểm Danh đã tồn tại với ID: {$page->ID}\n";
    
    // Cập nhật template nếu cần
    $current_template = get_post_meta($page->ID, '_wp_page_template', true);
    
    if ($current_template !== 'diemdanh-page.php') {
        $result = update_post_meta($page->ID, '_wp_page_template', 'diemdanh-page.php');
        
        if ($result) {
            echo "Đã cập nhật template cho trang.\n";
        } else {
            echo "Không thể cập nhật template cho trang.\n";
        }
    } else {
        echo "Template đã được thiết lập đúng.\n";
    }
}

// Xóa cache và làm mới rewrite rules
flush_rewrite_rules();
echo "Đã làm mới rewrite rules.\n";

echo "Hoàn tất.\n"; 