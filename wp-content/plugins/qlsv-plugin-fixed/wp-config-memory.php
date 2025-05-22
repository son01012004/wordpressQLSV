<?php
/**
 * Tăng giới hạn bộ nhớ trong WordPress để tránh lỗi khi hiển thị bảng điểm
 * 
 * File này nên được include trước wp-load.php trong trang riêng để tránh tràn bộ nhớ
 */

// Đặt giới hạn bộ nhớ cho PHP
ini_set('memory_limit', '256M');

// Đặt thời gian tối đa cho script chạy
ini_set('max_execution_time', 300); // 300 giây = 5 phút

// Tối ưu hóa các thiết lập bộ nhớ khác
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error-log.txt');

// Định nghĩa hằng số WordPress cho bộ nhớ
if (!defined('WP_MEMORY_LIMIT')) {
    define('WP_MEMORY_LIMIT', '256M');
}

if (!defined('WP_MAX_MEMORY_LIMIT')) {
    define('WP_MAX_MEMORY_LIMIT', '512M');
}

// Đặt giá trị SQL_BIG_SELECTS để cho phép các truy vấn lớn
add_action('init', function() {
    global $wpdb;
    if (isset($wpdb) && method_exists($wpdb, 'query')) {
        $wpdb->query("SET SQL_BIG_SELECTS=1");
    }
}, 1);

// Thông báo cho admin nếu cấu hình bộ nhớ đã được áp dụng
add_action('admin_notices', function() {
    if (current_user_can('manage_options')) {
        if (ini_get('memory_limit') != '256M') {
            echo '<div class="notice notice-warning"><p><strong>Cảnh báo:</strong> Cấu hình bộ nhớ chưa được áp dụng. Giới hạn hiện tại: ' . ini_get('memory_limit') . '</p></div>';
        } else {
            echo '<div class="notice notice-success is-dismissible"><p><strong>Thành công:</strong> Cấu hình bộ nhớ đã được tối ưu (256MB).</p></div>';
        }
    }
});

/**
 * Bổ sung cấu hình bộ nhớ cho WordPress
 * 
 * Thêm các dòng này vào file wp-config.php để tăng giới hạn bộ nhớ
 */

// Tăng giới hạn bộ nhớ cho WordPress
define('WP_MEMORY_LIMIT', '1024M');
define('WP_MAX_MEMORY_LIMIT', '1024M');

// Tăng thời gian thực thi script
@ini_set('max_execution_time', 300);

// Tăng giới hạn kích thước yêu cầu
@ini_set('post_max_size', '64M');

// Cấu hình debug (nên tắt trên môi trường production)
define('WP_DEBUG', false);
define('WP_DEBUG_DISPLAY', false);

// Tối ưu hóa truy vấn WordPress
define('SAVEQUERIES', false);
?> 