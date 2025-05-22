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

/**
 * Bắt đầu thực thi plugin.
 */
function run_qlsv_plugin() {
    // Khởi tạo Loader
    $loader = new QLSV_Loader();
    
    // Khởi tạo các module
    $sinh_vien = new QLSV_Sinh_Vien($loader);
    $diem = new QLSV_Diem($loader);
    $lop = new QLSV_Lop($loader);
    $monhoc = new QLSV_MonHoc($loader);
    $thoikhoabieu = new QLSV_ThoiKhoaBieu($loader);
    $diemdanh = new QLSV_DiemDanh($loader);
    
    // Chạy plugin
    $loader->run();
}

// Kích hoạt plugin
register_activation_hook(__FILE__, 'qlsv_activate');
function qlsv_activate() {
    // Tạo các Custom Post Type khi kích hoạt plugin
    require_once QLSV_PLUGIN_DIR . 'includes/class-qlsv-activator.php';
    QLSV_Activator::activate();
}

// Vô hiệu hóa plugin
register_deactivation_hook(__FILE__, 'qlsv_deactivate');
function qlsv_deactivate() {
    require_once QLSV_PLUGIN_DIR . 'includes/class-qlsv-deactivator.php';
    QLSV_Deactivator::deactivate();
}

// Chạy plugin
run_qlsv_plugin(); 