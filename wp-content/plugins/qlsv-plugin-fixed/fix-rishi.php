<?php
/**
 * Sửa lỗi tương thích với theme Rishi
 */

// Load WordPress
define('WP_USE_THEMES', false);
require_once('../../../wp-load.php');

// Kiểm tra quyền truy cập
if (!current_user_can('manage_options')) {
    wp_die('Bạn cần có quyền quản trị để truy cập trang này.');
}

// Lấy path của file
$rishi_basic_file = ABSPATH . 'wp-content/themes/rishi/customizer/classes/Helpers/Basic.php';

// Kiểm tra file tồn tại
if (!file_exists($rishi_basic_file)) {
    wp_die('Không tìm thấy file Basic.php của theme Rishi.');
}

// Đọc nội dung file
$file_content = file_get_contents($rishi_basic_file);

// Tìm và sửa đoạn code gây ra lỗi
$original_code = 'public static function is_elementor_activated_post($post_id = null) {
		if (! $post_id) {
			global $post;
			$post_id = $post->ID;
		}

		if (! $post_id) {
			return false;
		}

		return get_post_meta($post_id, \'_elementor_edit_mode\', true) &&
			class_exists(\'Elementor\\\Plugin\') &&
			\Elementor\Plugin::$instance->db->is_built_with_elementor($post_id);
	}';

$fixed_code = 'public static function is_elementor_activated_post($post_id = null) {
		if (! $post_id) {
			global $post;
			$post_id = isset($post) ? (isset($post->ID) ? $post->ID : 0) : 0;
		}

		if (! $post_id) {
			return false;
		}

		return get_post_meta($post_id, \'_elementor_edit_mode\', true) &&
			class_exists(\'Elementor\\\Plugin\') &&
			\Elementor\Plugin::$instance->db->is_built_with_elementor($post_id);
	}';

// Kiểm tra xem có tìm thấy đoạn code cần sửa không
if (strpos($file_content, $original_code) !== false) {
    // Thay thế đoạn code
    $new_content = str_replace($original_code, $fixed_code, $file_content);
    
    // Lưu lại file sau khi sửa
    if (file_put_contents($rishi_basic_file, $new_content)) {
        echo '<div style="background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border: 1px solid #c3e6cb; border-radius: 4px;">';
        echo '<h3>Đã sửa lỗi thành công!</h3>';
        echo '<p>File theme Rishi đã được cập nhật để tương thích với chức năng điểm danh.</p>';
        echo '</div>';
    } else {
        echo '<div style="background-color: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border: 1px solid #f5c6cb; border-radius: 4px;">';
        echo '<h3>Không thể cập nhật file</h3>';
        echo '<p>Có vấn đề với quyền ghi file. Hãy kiểm tra quyền của file: ' . $rishi_basic_file . '</p>';
        echo '</div>';
        
        // Hiển thị hướng dẫn sửa thủ công
        echo '<div style="background-color: #fff3cd; color: #856404; padding: 15px; margin-bottom: 20px; border: 1px solid #ffeeba; border-radius: 4px;">';
        echo '<h3>Hướng dẫn sửa thủ công:</h3>';
        echo '<p>1. Mở file: <strong>' . $rishi_basic_file . '</strong></p>';
        echo '<p>2. Tìm đoạn code sau:</p>';
        echo '<pre style="background: #f5f5f5; padding: 10px; overflow: auto; font-family: monospace;">';
        echo htmlspecialchars($original_code);
        echo '</pre>';
        echo '<p>3. Thay thế bằng đoạn code này:</p>';
        echo '<pre style="background: #f5f5f5; padding: 10px; overflow: auto; font-family: monospace;">';
        echo htmlspecialchars($fixed_code);
        echo '</pre>';
        echo '</div>';
    }
} else {
    echo '<div style="background-color: #fff3cd; color: #856404; padding: 15px; margin-bottom: 20px; border: 1px solid #ffeeba; border-radius: 4px;">';
    echo '<h3>Không tìm thấy đoạn code cần sửa</h3>';
    echo '<p>File có thể đã được sửa hoặc cấu trúc file khác với phiên bản dự kiến.</p>';
    echo '</div>';
}

// Hiển thị link để quay lại
echo '<p><a href="' . admin_url() . '" style="display: inline-block; padding: 8px 12px; background: #0073aa; color: #fff; text-decoration: none; border-radius: 3px;">Quay lại trang quản trị</a></p>';

// Hiển thị link đến trang điểm danh để kiểm tra
echo '<p><a href="' . home_url('/diemdanh/') . '" style="display: inline-block; padding: 8px 12px; background: #28a745; color: #fff; text-decoration: none; border-radius: 3px;">Kiểm tra trang điểm danh</a></p>';
?> 