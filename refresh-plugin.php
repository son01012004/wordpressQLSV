<?php
/**
 * Script để tắt và kích hoạt lại plugin QLSV
 */

// Load WordPress
define('WP_USE_THEMES', false);
require_once('wp-load.php');

// Đường dẫn plugin
$plugin_path = 'qlsv-plugin-fixed/qlsv-plugin.php';

echo "Đang tắt plugin QLSV...\n";
deactivate_plugins('wp-content/plugins/' . $plugin_path);
echo "Đã tắt plugin.\n";

echo "Đang kích hoạt lại plugin QLSV...\n";
activate_plugin('wp-content/plugins/' . $plugin_path);
echo "Đã kích hoạt lại plugin.\n";

// Đảm bảo trang điểm danh được tạo
echo "Đảm bảo trang điểm danh được tạo...\n";
$page = get_page_by_path('diemdanh');
if ($page) {
    echo "Trang điểm danh đã tồn tại với ID: " . $page->ID . "\n";
    
    // Kiểm tra template
    $template = get_post_meta($page->ID, '_wp_page_template', true);
    echo "Template hiện tại: " . ($template ?: 'Không có') . "\n";
    
    if ($template != 'diemdanh-page.php') {
        echo "Đang cập nhật template...\n";
        update_post_meta($page->ID, '_wp_page_template', 'diemdanh-page.php');
        echo "Đã cập nhật template.\n";
    }
} else {
    echo "Trang điểm danh chưa tồn tại, đang tạo mới...\n";
    
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
        echo "Đã tạo trang điểm danh với ID: " . $page_id . "\n";
        
        // Gán template
        update_post_meta($page_id, '_wp_page_template', 'diemdanh-page.php');
        echo "Đã gán template cho trang.\n";
    } else {
        echo "Không thể tạo trang điểm danh.\n";
        if (is_wp_error($page_id)) {
            echo "Lỗi: " . $page_id->get_error_message() . "\n";
        }
    }
}

// Flush rewrite rules
echo "Đang cập nhật permalink...\n";
flush_rewrite_rules();
echo "Hoàn tất cập nhật permalink.\n";

echo "Hoàn tất quá trình!\n"; 