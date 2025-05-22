<?php
/**
 * Giải quyết vấn đề tương thích với các theme khác nhau
 * Đặc biệt là theme Rishi có nhiều kiểm tra về post_type
 */

// Đảm bảo không gọi trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

// Hook vào theme setup để thêm các tương thích
add_action('after_setup_theme', 'qlsv_theme_compatibility_setup');

/**
 * Thiết lập tương thích với các theme
 */
function qlsv_theme_compatibility_setup() {
    // Kiểm tra theme hiện tại
    $current_theme = wp_get_theme();
    $theme_name = $current_theme->get('Name');
    
    // Xử lý riêng cho theme Rishi
    if (strtolower($theme_name) == 'rishi' || strtolower($theme_name) == 'rishi theme') {
        // Loại bỏ kiểm tra Elementor cho các trang điểm danh
        add_filter('rishi:customizer:helpers:basic:is_elementor_activated_post', 'qlsv_disable_elementor_check_for_diemdanh', 10, 2);
        add_filter('body_class', 'qlsv_modify_body_class_for_diemdanh');
    }
}

/**
 * Tắt kiểm tra Elementor cho các trang điểm danh
 */
function qlsv_disable_elementor_check_for_diemdanh($result, $post_id = null) {
    // Nếu là trang điểm danh, trả về false để bỏ qua việc kiểm tra
    if (is_post_type_archive('diemdanh') || 
        (isset($_GET['lop']) && isset($_GET['mon_hoc'])) ||
        (is_singular() && get_post_type() == 'diemdanh')) {
        return false;
    }
    
    // Trả về kết quả gốc cho các trường hợp khác
    return $result;
}

/**
 * Thêm class cho body để tránh lỗi CSS
 */
function qlsv_modify_body_class_for_diemdanh($classes) {
    // Nếu là trang điểm danh, thêm các class cần thiết
    if (is_post_type_archive('diemdanh') || 
        (isset($_GET['lop']) && isset($_GET['mon_hoc'])) ||
        (is_singular() && get_post_type() == 'diemdanh')) {
        $classes[] = 'post-type-archive';
        $classes[] = 'post-type-archive-diemdanh';
        
        // Loại bỏ các class có thể gây xung đột
        $classes = array_diff($classes, ['wp-singular', 'singular']);
    }
    
    return $classes;
}

/**
 * Hook vào template_redirect để xử lý các lỗi với theme
 */
add_action('template_redirect', 'qlsv_handle_diemdanh_template_redirect');
function qlsv_handle_diemdanh_template_redirect() {
    // Chỉ xử lý với trang điểm danh
    if (is_post_type_archive('diemdanh') || 
        (isset($_GET['lop']) && isset($_GET['mon_hoc']))) {
        
        // Đảm bảo biến $post tồn tại để tránh lỗi
        global $post, $wp_query;
        if (!isset($post) || empty($post)) {
            // Tạo đối tượng post giả
            $dummy_post = new stdClass();
            $dummy_post->ID = 0;
            $dummy_post->post_type = 'diemdanh';
            $dummy_post->post_title = 'Điểm Danh';
            $dummy_post->post_name = 'diemdanh';
            $dummy_post->post_content = '';
            $dummy_post->comment_count = 0;
            $dummy_post->post_author = 1;
            $dummy_post->post_date = date('Y-m-d H:i:s');
            $dummy_post->post_date_gmt = date('Y-m-d H:i:s');
            
            // Thiết lập post
            $post = $dummy_post;
            
            // Thiết lập cho query
            if (empty($wp_query->posts)) {
                $wp_query->posts = array($dummy_post);
                $wp_query->post_count = 1;
                $wp_query->found_posts = 1;
            }
        }
        
        // Đánh dấu rằng đây là trang archive diemdanh
        $wp_query->is_post_type_archive = true;
        $wp_query->is_archive = true;
        $wp_query->is_singular = false;
        $wp_query->is_404 = false;
    }
}
?> 