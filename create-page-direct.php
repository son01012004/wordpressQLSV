<?php
/**
 * Script tạo trang Điểm Danh trực tiếp qua SQL
 */

// Load WordPress
define('WP_USE_THEMES', false);
require_once('wp-load.php');

global $wpdb;

// Xóa trang điểm danh cũ nếu có
$existing_page = get_page_by_path('diemdanh');
if ($existing_page) {
    echo "Đang xóa trang điểm danh cũ...\n";
    wp_delete_post($existing_page->ID, true);
    echo "Đã xóa trang điểm danh cũ.\n";
}

// Tạo trang mới
echo "Đang tạo trang mới...\n";

// Chuẩn bị dữ liệu
$page_data = array(
    'post_title'    => 'Điểm Danh',
    'post_name'     => 'diemdanh',
    'post_content'  => '[qlsv_diemdanh_dashboard]',
    'post_status'   => 'publish',
    'post_type'     => 'page',
    'post_author'   => 1, // Người dùng admin
    'post_date'     => current_time('mysql'),
    'post_date_gmt' => current_time('mysql', 1),
    'comment_status' => 'closed'
);

// Thêm trang
$page_id = wp_insert_post($page_data);

if (is_wp_error($page_id)) {
    echo "Lỗi: " . $page_id->get_error_message() . "\n";
} else {
    echo "Trang đã được tạo với ID: " . $page_id . "\n";
    
    // Thêm template
    update_post_meta($page_id, '_wp_page_template', 'diemdanh-template.php');
    echo "Đã thiết lập template cho trang.\n";
}

// Cập nhật các quy tắc permalink
echo "Đang cập nhật permalink...\n";
flush_rewrite_rules();
echo "Đã cập nhật permalink.\n";

echo "Hoàn tất quá trình!\n"; 