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

// Tải cấu hình bộ nhớ tối ưu
require_once(QLSV_PLUGIN_DIR . 'wp-config-memory.php');

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
// Sử dụng phiên bản lite của module điểm để giảm sử dụng bộ nhớ
require_once QLSV_PLUGIN_DIR . 'modules/diem/class-qlsv-diem-lite.php';
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
    $diem = new QLSV_Diem_Lite($qlsv_loader);
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

// Thêm menu công cụ chẩn đoán bộ nhớ
add_action('admin_menu', 'qlsv_add_memory_diagnostic_menu');

/**
 * Thêm menu công cụ chẩn đoán bộ nhớ
 */
function qlsv_add_memory_diagnostic_menu() {
    add_management_page(
        'Công cụ chẩn đoán bộ nhớ QLSV',
        'Chẩn đoán bộ nhớ QLSV',
        'manage_options',
        'qlsv-memory-diagnostic',
        'qlsv_memory_diagnostic_redirect'
    );
}

/**
 * Chuyển hướng đến trang chẩn đoán bộ nhớ
 */
function qlsv_memory_diagnostic_redirect() {
    $url = plugins_url('memory-diagnostic.php', __FILE__);
    echo '<script>window.location.href = "' . esc_url($url) . '";</script>';
    echo '<p>Đang chuyển hướng đến <a href="' . esc_url($url) . '">công cụ chẩn đoán bộ nhớ</a>...</p>';
}

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
                'slug' => 'diem',
                'with_front' => false
            );
            // Đảm bảo hiển thị trang archive
            $wp_post_types['diem']->has_archive = true;
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
            'post_content'   => '[qlsv_tim_kiem_diem_lite]',
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'comment_status' => 'closed'
        ));
    } else {
        $ketqua_page_id = $ketqua_page->ID;
        
        // Cập nhật nội dung trang nếu cần
        wp_update_post(array(
            'ID' => $ketqua_page_id,
            'post_content' => '[qlsv_tim_kiem_diem_lite]'
        ));
    }

    // Trang Điểm
    $diem_page_id = 0;
    $diem_page = get_page_by_path('ket-qua-diem');
    
    if (!$diem_page) {
        // Tạo trang mới
        $diem_page_id = wp_insert_post(array(
            'post_title'     => 'Kết Quả Điểm',
            'post_name'      => 'ket-qua-diem',
            'post_content'   => '[qlsv_tim_kiem_diem_lite]',
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'comment_status' => 'closed'
        ));
    } else {
        $diem_page_id = $diem_page->ID;
        
        // Cập nhật nội dung trang nếu cần
        wp_update_post(array(
            'ID' => $diem_page_id,
            'post_content' => '[qlsv_tim_kiem_diem_lite]'
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
    qlsv_add_pages_to_menu($diemdanh_page_id, $ketqua_page_id, $diem_page_id);
}

// Thêm trang vào menu
function qlsv_add_pages_to_menu($diemdanh_page_id, $ketqua_page_id, $diem_page_id = 0) {
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
        $diem_exists = false;
        
        if ($menu_items) {
            foreach ($menu_items as $item) {
                if ($item->object == 'page' && $item->object_id == $diemdanh_page_id) {
                    $diemdanh_exists = true;
                }
                if ($item->object == 'page' && $item->object_id == $ketqua_page_id) {
                    $ketqua_exists = true;
                }
                if ($item->object == 'page' && $item->object_id == $diem_page_id) {
                    $diem_exists = true;
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
        
        if (!$diem_exists && $diem_page_id) {
            wp_update_nav_menu_item($primary_menu_id, 0, array(
                'menu-item-title' => 'Kết Quả Điểm',
                'menu-item-object' => 'page',
                'menu-item-object-id' => $diem_page_id,
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

// Đăng ký template login tùy chỉnh
function qlsv_custom_login_template($template) {
    if (is_page('login') || isset($_GET['custom-login'])) {
        $custom_template = plugin_dir_path(__FILE__) . 'templates/login-template.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    return $template;
}
add_filter('template_include', 'qlsv_custom_login_template');

// Thêm CSS vào trang đăng nhập mặc định của WordPress
function qlsv_custom_login_css() {
    wp_enqueue_style('qlsv-login-styles', plugins_url('assets/css/qlsv-login.css', __FILE__), array(), '1.0.0');
    
    // Thêm script để bổ sung placeholder và tùy chỉnh form
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Thêm placeholder cho trường nhập liệu
        var usernameField = document.getElementById('user_login');
        var passwordField = document.getElementById('user_pass');
        
        if (usernameField) {
            usernameField.placeholder = 'username';
        }
        
        if (passwordField) {
            passwordField.placeholder = 'password';
            
            // Tạo container cho password field để thêm mắt xem mật khẩu
            var parent = passwordField.parentNode;
            var wrapper = document.createElement('div');
            wrapper.className = 'password-field-container';
            parent.replaceChild(wrapper, passwordField);
            wrapper.appendChild(passwordField);
            
            // Thêm icon mắt xem mật khẩu
            var toggleIcon = document.createElement('span');
            toggleIcon.className = 'password-toggle';
            toggleIcon.innerHTML = '👁️';
            toggleIcon.onclick = function() {
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    this.innerHTML = '👁️‍🗨️';
                } else {
                    passwordField.type = 'password';
                    this.innerHTML = '👁️';
                }
            };
            wrapper.appendChild(toggleIcon);
        }
        
        // Ẩn phần "go to qlsv"
        var navLinks = document.querySelectorAll('#nav a');
        if (navLinks.length > 1) {
            for (var i = 1; i < navLinks.length; i++) {
                navLinks[i].style.display = 'none';
            }
        }
        
        // Ẩn checkbox ghi nhớ
        var rememberMe = document.querySelector('.forgetmenot');
        if (rememberMe) {
            rememberMe.style.display = 'none';
        }
    });
    </script>
    <?php
}
add_action('login_enqueue_scripts', 'qlsv_custom_login_css');

// Thay đổi URL logo trang đăng nhập
function qlsv_login_logo_url() {
    return home_url();
}
add_filter('login_headerurl', 'qlsv_login_logo_url');

// Thay đổi title của logo trang đăng nhập
function qlsv_login_logo_url_title() {
    return 'Quản lý sinh viên';
}
add_filter('login_headertext', 'qlsv_login_logo_url_title');

// Tạo shortcode để hiển thị form đăng nhập tùy chỉnh
function qlsv_login_shortcode() {
    if (is_user_logged_in()) {
        return '<p>Bạn đã đăng nhập. <a href="' . wp_logout_url(home_url()) . '">Đăng xuất</a></p>';
    }
    
    // Đảm bảo CSS đã được enqueue
    wp_enqueue_style('qlsv-login-styles', plugins_url('assets/css/qlsv-login.css', __FILE__), array(), '1.0.0');
    
    $error = '';
    if (isset($_GET['login']) && $_GET['login'] == 'failed') {
        $error = '<div class="login-error">Tên đăng nhập hoặc mật khẩu không chính xác.</div>';
    } elseif (isset($_GET['login']) && $_GET['login'] == 'empty') {
        $error = '<div class="login-error">Vui lòng nhập tên đăng nhập và mật khẩu.</div>';
    }
    
    $output = '
    <div id="login">
        <form name="loginform" id="loginform" action="' . esc_url(site_url('wp-login.php', 'login_post')) . '" method="post">
            ' . $error . '
            
            <p>
                <input type="text" name="log" id="user_login" class="input" value="" size="20" autocapitalize="off" autocomplete="username" required placeholder="username">
            </p>

            <p>
                <div class="password-field-container">
                    <input type="password" name="pwd" id="user_pass" class="input" value="" size="20" autocomplete="current-password" required placeholder="password">
                    <span class="password-toggle" onclick="togglePassword()">👁️</span>
                </div>
            </p>

            <p class="submit">
                <input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="LOG IN">
                <input type="hidden" name="redirect_to" value="' . esc_url(home_url()) . '">
                <input type="hidden" name="testcookie" value="1">
            </p>
            
            <p id="nav">
                <a href="' . esc_url(wp_lostpassword_url()) . '">Forgot password?</a>
            </p>
        </form>
    </div>
    
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById("user_pass");
            const passwordIcon = document.querySelector(".password-toggle");
            
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                passwordIcon.textContent = "👁️‍🗨️";
            } else {
                passwordInput.type = "password";
                passwordIcon.textContent = "👁️";
            }
        }
    </script>';
    
    return $output;
}
add_shortcode('qlsv_login_form', 'qlsv_login_shortcode');

// Thêm CSS styles từ file mới tạo
function qlsv_enqueue_scripts() {
    // Đăng ký CSS để hiển thị form điểm cho sinh viên
    wp_enqueue_style('qlsv-styles', plugin_dir_url(__FILE__) . 'assets/css/qlsv-styles.css', array(), QLSV_VERSION);
    
    // Đăng ký JS
    wp_enqueue_script('qlsv-script', plugin_dir_url(__FILE__) . 'assets/js/qlsv-script.js', array('jquery'), QLSV_VERSION, true);

    // CSS
    wp_enqueue_style('qlsv-admin-style', plugins_url('assets/css/qlsv-admin.css', __FILE__), array(), '1.0.0');
    
    // JavaScript
    wp_enqueue_script('qlsv-admin-script', plugins_url('assets/js/qlsv-admin.js', __FILE__), array('jquery'), '1.0.0', true);
    
    // Localize script với các tham số AJAX
    wp_localize_script('qlsv-admin-script', 'qlsv_ajax_params', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('qlsv_ajax_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'qlsv_enqueue_scripts');
add_action('admin_enqueue_scripts', 'qlsv_enqueue_scripts');

// Đăng ký route cho avatar placeholder
function qlsv_register_avatar_placeholder() {
    add_rewrite_rule(
        'qlsv-avatar-placeholder/?$',
        'index.php?qlsv_avatar_placeholder=1',
        'top'
    );
}
add_action('init', 'qlsv_register_avatar_placeholder');

// Thêm query var
function qlsv_query_vars($vars) {
    $vars[] = 'qlsv_avatar_placeholder';
    return $vars;
}
add_filter('query_vars', 'qlsv_query_vars');

// Xử lý template cho avatar placeholder
function qlsv_template_include($template) {
    if (get_query_var('qlsv_avatar_placeholder') == 1) {
        $placeholder_template = QLSV_PLUGIN_DIR . 'templates/avatar-placeholder.php';
        if (file_exists($placeholder_template)) {
            return $placeholder_template;
        }
    }
    return $template;
}
add_filter('template_include', 'qlsv_template_include');

// Hàm chuyển hướng người dùng về trang chủ sau khi đăng nhập
function qlsv_login_redirect($redirect_to, $request, $user) {
    // Nếu không có lỗi trong quá trình đăng nhập và người dùng tồn tại
    if (!is_wp_error($user) && $user) {
        // Chuyển hướng tất cả người dùng về trang chủ, kể cả admin
        return home_url();
    }
    
    // Trường hợp khác, giữ nguyên chuyển hướng mặc định
    return $redirect_to;
}
add_filter('login_redirect', 'qlsv_login_redirect', 10, 3); 