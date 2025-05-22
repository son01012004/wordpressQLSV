<?php
/**
 * Plugin Name: Quản lý Sinh viên
 * Plugin URI: #
 * Description: Plugin quản lý sinh viên, điểm và lớp học
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: #
 * License: GPL-2.0+
 * Text Domain: qlsv
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Plugin version
define('QLSV_VERSION', '1.0.0');

// Plugin directory path
define('QLSV_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('QLSV_PLUGIN_URL', plugin_dir_url(__FILE__));
define('QLSV_PLUGIN_FILE', __FILE__);

// Thêm file tương thích với các theme khác nhau
require_once(QLSV_PLUGIN_DIR . 'theme-compatibility.php');

// Kiểm tra ACF plugin
function qlsv_check_required_plugins() {
    if (!function_exists('acf_get_field') && !function_exists('get_field')) {
        add_action('admin_notices', 'qlsv_acf_missing_notice');
        add_action('wp_footer', 'qlsv_acf_missing_frontend_notice');
        return false;
    }
    return true;
}

// Thông báo khi thiếu plugin ACF (backend)
function qlsv_acf_missing_notice() {
    ?>
    <div class="notice notice-error">
        <p><?php _e('Plugin <strong>Quản lý Sinh viên</strong> yêu cầu plugin <strong>Advanced Custom Fields</strong> được cài đặt và kích hoạt.', 'qlsv'); ?></p>
    </div>
    <?php
}

// Thông báo khi thiếu plugin ACF (frontend)
function qlsv_acf_missing_frontend_notice() {
    ?>
    <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; margin: 20px 0;">
        <p><?php _e('Plugin <strong>Quản lý Sinh viên</strong> yêu cầu plugin <strong>Advanced Custom Fields</strong> được cài đặt và kích hoạt.', 'qlsv'); ?></p>
    </div>
    <?php
}

// Include các file cần thiết
require_once QLSV_PLUGIN_DIR . 'includes/class-qlsv-loader.php';
require_once QLSV_PLUGIN_DIR . 'includes/class-qlsv-activator.php';
require_once QLSV_PLUGIN_DIR . 'includes/class-qlsv-deactivator.php';

// Gọi các module
require_once QLSV_PLUGIN_DIR . 'modules/sinh-vien/class-qlsv-sinh-vien.php';
require_once QLSV_PLUGIN_DIR . 'modules/diem/class-qlsv-diem.php';
require_once QLSV_PLUGIN_DIR . 'modules/lop/class-qlsv-lop.php';
require_once QLSV_PLUGIN_DIR . 'modules/monhoc/class-qlsv-monhoc.php';
require_once QLSV_PLUGIN_DIR . 'modules/thoikhoabieu/class-qlsv-thoikhoabieu.php';
require_once QLSV_PLUGIN_DIR . 'modules/diemdanh/class-qlsv-diemdanh.php';
require_once QLSV_PLUGIN_DIR . 'modules/giaovien/class-qlsv-giaovien.php';
require_once QLSV_PLUGIN_DIR . 'modules/user-profile/class-qlsv-user-profile.php';

/**
 * Bắt đầu thực thi plugin.
 */
function run_qlsv_plugin() {
    // Kiểm tra xem ACF có được kích hoạt không
    if (!qlsv_check_required_plugins()) {
        return;
    }
    
    // Khởi tạo Loader
    global $qlsv_loader;
    $qlsv_loader = new QLSV_Loader();
    
    // Khởi tạo các module
    $sinh_vien = new QLSV_Sinh_Vien($qlsv_loader);
    $diem = new QLSV_Diem($qlsv_loader);
    $lop = new QLSV_Lop($qlsv_loader);
    $monhoc = new QLSV_MonHoc($qlsv_loader);
    $thoikhoabieu = new QLSV_ThoiKhoaBieu($qlsv_loader);
    $diemdanh = new QLSV_DiemDanh($qlsv_loader);
    $giaovien = new QLSV_GiaoVien($qlsv_loader);
    $user_profile = new QLSV_User_Profile($qlsv_loader);
    
    // Chạy plugin
    $qlsv_loader->run();
}

// Kích hoạt plugin
register_activation_hook(__FILE__, 'qlsv_activate');
function qlsv_activate() {
    // Tạo các Custom Post Type khi kích hoạt plugin
    require_once QLSV_PLUGIN_DIR . 'includes/class-qlsv-activator.php';
    QLSV_Activator::activate();
    
    // Đảm bảo cập nhật rewrite rules
    flush_rewrite_rules();
    
    // Đảm bảo tạo các trang cần thiết
    qlsv_create_required_pages();
}

// Vô hiệu hóa plugin
register_deactivation_hook(__FILE__, 'qlsv_deactivate');
function qlsv_deactivate() {
    require_once QLSV_PLUGIN_DIR . 'includes/class-qlsv-deactivator.php';
    QLSV_Deactivator::deactivate();
    
    flush_rewrite_rules();
}

// Thêm action để chạy plugin
add_action('plugins_loaded', 'run_qlsv_plugin'); 

// Đăng ký các hook để xử lý URL và trang
add_action('init', 'qlsv_register_post_types_with_fixed_urls', 5);
add_action('init', 'qlsv_create_required_pages', 20);
add_action('wp_loaded', 'qlsv_flush_rewrite_rules', 99);
add_filter('wp_nav_menu_objects', 'qlsv_custom_nav_menu');

// Cập nhật các post type để tránh xung đột URL
function qlsv_register_post_types_with_fixed_urls() {
    // Cập nhật post type diemdanh với slug mới
    if (post_type_exists('diemdanh')) {
        global $wp_post_types;
        if (isset($wp_post_types['diemdanh'])) {
            $wp_post_types['diemdanh']->rewrite = array(
                'slug' => 'diemdanh',
                'with_front' => false
            );
            // Đảm bảo hiển thị trang archive
            $wp_post_types['diemdanh']->has_archive = true;
        }
    }
    
    // Cập nhật post type diem với slug mới
    if (post_type_exists('diem')) {
        global $wp_post_types;
        if (isset($wp_post_types['diem'])) {
            $wp_post_types['diem']->rewrite = array(
                'slug' => 'diem-record',
                'with_front' => false
            );
        }
    }
}

// Tạo hoặc cập nhật các trang cần thiết
function qlsv_create_required_pages() {
    // Trang Điểm danh
    $diemdanh_page_id = 0;
    $diemdanh_page = get_page_by_path('diemdanhh');
    
    if (!$diemdanh_page) {
        // Tạo trang mới
        $diemdanh_page_id = wp_insert_post(array(
            'post_title'     => 'Điểm Danh',
            'post_name'      => 'diemdanhh',
            'post_content'   => '[qlsv_diemdanh_dashboard]',
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'comment_status' => 'closed'
        ));
    } else {
        $diemdanh_page_id = $diemdanh_page->ID;
        
        // Cập nhật nội dung trang nếu cần
        wp_update_post(array(
            'ID' => $diemdanh_page_id,
            'post_content' => '[qlsv_diemdanh_dashboard]'
        ));
    }
    
    // Kiểm tra và cập nhật template
    if ($diemdanh_page_id > 0) {
        // Không cần thiết lập template, để WordPress sử dụng template mặc định
        $current_template = get_post_meta($diemdanh_page_id, '_wp_page_template', true);
        if (!empty($current_template)) {
            // Xóa template nếu đã được thiết lập
            delete_post_meta($diemdanh_page_id, '_wp_page_template');
        }
    }
    
    // Trang Kết quả học tập
    $ketqua_page_id = 0;
    $ketqua_page = get_page_by_path('ket-qua-hoc-tap');
    
    if (!$ketqua_page) {
        // Tạo trang mới
        $ketqua_page_id = wp_insert_post(array(
            'post_title'     => 'Kết Quả Học Tập',
            'post_name'      => 'ket-qua-hoc-tap',
            'post_content'   => '[qlsv_tim_kiem_diem]',
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'comment_status' => 'closed'
        ));
    } else {
        $ketqua_page_id = $ketqua_page->ID;
        
        // Cập nhật nội dung trang nếu cần
        wp_update_post(array(
            'ID' => $ketqua_page_id,
            'post_content' => '[qlsv_tim_kiem_diem]'
        ));
    }
    
    // Kiểm tra và cập nhật template
    if ($ketqua_page_id > 0) {
        $current_template = get_post_meta($ketqua_page_id, '_wp_page_template', true);
        if (empty($current_template) || $current_template == 'default') {
            update_post_meta($ketqua_page_id, '_wp_page_template', 'ket-qua-hoc-tap-template.php');
        }
    }
    
    // Thêm các trang vào menu nếu chưa có
    qlsv_add_pages_to_menu($diemdanh_page_id, $ketqua_page_id);
}

// Thêm trang vào menu
function qlsv_add_pages_to_menu($diemdanh_page_id, $ketqua_page_id) {
    // Lấy menu chính (primary/main menu)
    $menu_locations = get_nav_menu_locations();
    $primary_menu_id = 0;
    
    // Kiểm tra các vị trí menu thường dùng
    $menu_locations_to_check = array('primary', 'main-menu', 'primary-menu', 'main', 'top', 'header-menu');
    
    foreach ($menu_locations_to_check as $location) {
        if (isset($menu_locations[$location])) {
            $primary_menu_id = $menu_locations[$location];
            break;
        }
    }
    
    if ($primary_menu_id == 0) {
        // Nếu không tìm thấy menu theo location, thử lấy menu đầu tiên
        $menus = wp_get_nav_menus();
        if (!empty($menus)) {
            $primary_menu_id = $menus[0]->term_id;
        }
    }
    
    if ($primary_menu_id) {
        // Kiểm tra xem đã có trang Điểm Danh trong menu chưa
        $menu_items = wp_get_nav_menu_items($primary_menu_id);
        $diemdanh_exists = false;
        $ketqua_exists = false;
        
        if ($menu_items) {
            foreach ($menu_items as $item) {
                if ($item->object == 'page' && $item->object_id == $diemdanh_page_id) {
                    $diemdanh_exists = true;
                }
                if ($item->object == 'page' && $item->object_id == $ketqua_page_id) {
                    $ketqua_exists = true;
                }
            }
        }
        
        // Thêm vào menu nếu chưa có
        if (!$diemdanh_exists && $diemdanh_page_id) {
            wp_update_nav_menu_item($primary_menu_id, 0, array(
                'menu-item-title' => 'Điểm Danh',
                'menu-item-object' => 'page',
                'menu-item-object-id' => $diemdanh_page_id,
                'menu-item-type' => 'post_type',
                'menu-item-status' => 'publish'
            ));
        }
        
        if (!$ketqua_exists && $ketqua_page_id) {
            wp_update_nav_menu_item($primary_menu_id, 0, array(
                'menu-item-title' => 'Kết Quả Học Tập',
                'menu-item-object' => 'page',
                'menu-item-object-id' => $ketqua_page_id,
                'menu-item-type' => 'post_type',
                'menu-item-status' => 'publish'
            ));
        }
    }
}

// Sửa menu để thêm Điểm danh nếu menu được tạo động
function qlsv_custom_nav_menu($menu_items) {
    $diemdanh_page = get_page_by_path('diemdanhh');
    $ketqua_page = get_page_by_path('ket-qua-hoc-tap');
    
    // Kiểm tra xem trang Điểm Danh có trong menu không
    $diemdanh_exists = false;
    $ketqua_exists = false;
    foreach ($menu_items as $item) {
        if ($item->object == 'page' && $diemdanh_page && $item->object_id == $diemdanh_page->ID) {
            $diemdanh_exists = true;
            $item->title = 'Điểm Danh';  // Đảm bảo tiêu đề hiển thị đúng
            $item->url = get_permalink($diemdanh_page->ID);  // Đảm bảo URL được cập nhật
        }
        if ($item->object == 'page' && $ketqua_page && $item->object_id == $ketqua_page->ID) {
            $ketqua_exists = true;
        }
    }
    
    return $menu_items;
}

// Flush rewrite rules khi cần
function qlsv_flush_rewrite_rules() {
    // Add custom rewrite rules for diemdanh with parameters
    add_rewrite_rule(
        'diemdanh/lop/([0-9]+)/mon-hoc/([0-9]+)/?$',
        'index.php?post_type=diemdanh&lop=$matches[1]&mon_hoc=$matches[2]',
        'top'
    );
    
    // Add shorter version with query parameters
    add_rewrite_rule(
        'diemdanh/?$',
        'index.php?post_type=diemdanh',
        'top'
    );
    
    // Flush rewrite rules
    flush_rewrite_rules();
} 