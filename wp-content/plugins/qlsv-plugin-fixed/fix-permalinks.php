<?php
/**
 * Script để khắc phục lỗi permalink cho Điểm Danh
 */

// Load WordPress
define('WP_USE_THEMES', false);
require_once('../../../wp-load.php');

// Kiểm tra quyền admin
if (!current_user_can('manage_options')) {
    wp_die('Bạn cần có quyền quản trị để chạy công cụ này');
}

echo '<h1>Làm mới Permalink cho Điểm Danh</h1>';

// Bước 1: Kiểm tra và cập nhật rewrite rules cho diemdanh
global $wp_rewrite;
$current_rules = $wp_rewrite->wp_rewrite_rules();

echo '<p><strong>Bước 1:</strong> Kiểm tra quy tắc rewrite hiện tại...</p>';

// Bước 2: Đăng ký lại custom post type
echo '<p><strong>Bước 2:</strong> Đăng ký lại post type và query vars...</p>';

global $wp_post_types;
if (isset($wp_post_types['diemdanh'])) {
    // Cập nhật lại post type để đảm bảo cấu hình đúng
    $wp_post_types['diemdanh']->rewrite = array(
        'slug' => 'diemdanh',
        'with_front' => false,
    );
    $wp_post_types['diemdanh']->has_archive = true;
    $wp_post_types['diemdanh']->query_var = true;
    $wp_post_types['diemdanh']->publicly_queryable = true;
    
    echo '<p>- Post type "diemdanh" đã được cập nhật cấu hình</p>';
} else {
    echo '<p>- Post type "diemdanh" không tồn tại!</p>';
}

// Bước 3: Đặt lại rewrite rules cho diemdanh
echo '<p><strong>Bước 3:</strong> Thêm quy tắc rewrite mới...</p>';

// Thêm rewrite rule mới cho diemdanh với tham số
add_rewrite_rule(
    'diemdanh/?$',
    'index.php?post_type=diemdanh',
    'top'
);

// Thêm rewrite rule cho URL có tham số lop và mon_hoc
add_rewrite_rule(
    'diemdanh/lop/([0-9]+)/mon-hoc/([0-9]+)/?$',
    'index.php?post_type=diemdanh&lop=$matches[1]&mon_hoc=$matches[2]',
    'top'
);

// Bước 4: Đăng ký các tham số query
global $wp;
$wp->add_query_var('lop');
$wp->add_query_var('mon_hoc');

echo '<p>- Đã thêm query vars "lop" và "mon_hoc"</p>';

// Bước 5: Xóa transient cache
delete_transient('rewrite_rules');
echo '<p>- Đã xóa cache rewrite rules</p>';

// Bước 6: Làm mới rewrite rules
flush_rewrite_rules();
echo '<p>- Đã làm mới rewrite rules</p>';

// Bước 7: Kiểm tra cấu hình permalink
$permalink_structure = get_option('permalink_structure');
echo '<p><strong>Bước 4:</strong> Kiểm tra cấu hình permalink...</p>';

if (empty($permalink_structure)) {
    echo '<p style="color: red; font-weight: bold;">CHÚ Ý: Permalink đang được đặt là Plain. Bạn cần thay đổi sang Post name.</p>';
    echo '<p>Hãy vào <a href="' . admin_url('options-permalink.php') . '">Settings > Permalinks</a> và chọn "Post name".</p>';
} else {
    echo '<p>- Cấu hình permalink hiện tại: ' . esc_html($permalink_structure) . '</p>';
}

// Hiển thị trang test
echo '<h2>Kiểm tra URL điểm danh</h2>';

// Lấy một lớp và môn học để test
$lop_query = new WP_Query(array(
    'post_type' => 'lop',
    'posts_per_page' => 1
));

$monhoc_query = new WP_Query(array(
    'post_type' => 'monhoc',
    'posts_per_page' => 1
));

if ($lop_query->have_posts() && $monhoc_query->have_posts()) {
    $lop_query->the_post();
    $lop_id = get_the_ID();
    $lop_title = get_the_title();
    wp_reset_postdata();
    
    $monhoc_query->the_post();
    $monhoc_id = get_the_ID();
    $monhoc_title = get_the_title();
    wp_reset_postdata();
    
    // Tạo URL test
    $test_url_1 = home_url('/diemdanh/');
    $test_url_2 = add_query_arg(array('lop' => $lop_id, 'mon_hoc' => $monhoc_id), home_url('/diemdanh/'));
    
    echo '<p><strong>URL điểm danh cơ bản:</strong> <a href="' . esc_url($test_url_1) . '" target="_blank">' . esc_html($test_url_1) . '</a></p>';
    echo '<p><strong>URL điểm danh với tham số:</strong> <a href="' . esc_url($test_url_2) . '" target="_blank">' . esc_html($test_url_2) . '</a></p>';
}

// Hiển thị hướng dẫn
echo '<h2>Các bước tiếp theo</h2>';
echo '<ol>';
echo '<li>Vào <a href="' . admin_url('options-permalink.php') . '">Settings > Permalinks</a> và nhấp vào "Save Changes" (không cần thay đổi gì)</li>';
echo '<li>Xóa cache trình duyệt (Ctrl+F5 hoặc Cmd+Shift+R)</li>';
echo '<li>Thử truy cập lại URL điểm danh</li>';
echo '<li>Nếu vẫn gặp lỗi, hãy kiểm tra xem plugin ACF có được kích hoạt không</li>';
echo '</ol>';

// Hiển thị debug info
echo '<h2>Thông tin debug</h2>';
echo '<p><a href="' . home_url('/wp-admin/options-permalink.php') . '" target="_blank">Permalink Settings</a></p>';
echo '<p><a href="' . home_url('/diemdanhh/') . '" target="_blank">Trang điểm danh</a></p>';
echo '<p><a href="' . home_url('/diemdanh/') . '" target="_blank">Archive điểm danh</a></p>';
echo '<p><a href="' . admin_url() . '">Quay lại trang quản trị</a></p>';
?> 