<?php
/**
 * Công cụ chẩn đoán và khắc phục vấn đề bộ nhớ cho QLSV Plugin
 */

// Load WordPress
define('WP_USE_THEMES', false);
require_once('../../../wp-load.php');

// Kiểm tra quyền truy cập
if (!current_user_can('manage_options')) {
    wp_die('Bạn cần có quyền quản trị để truy cập trang này.');
}

// Tăng giới hạn bộ nhớ PHP
ini_set('memory_limit', '1024M');

// Thực hiện hành động nếu được yêu cầu
$action = isset($_POST['action']) ? $_POST['action'] : '';
$message = '';

if ($action === 'clear_cache') {
    // Xóa các transients
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%_transient_%'");
    
    // Xóa các cache khác
    wp_cache_flush();
    
    $message = '<div class="notice notice-success"><p>Đã xóa cache thành công!</p></div>';
}

if ($action === 'optimize_db') {
    // Tối ưu hóa các bảng database
    global $wpdb;
    $tables = $wpdb->get_results('SHOW TABLES', ARRAY_N);
    
    foreach ($tables as $table) {
        $wpdb->query("OPTIMIZE TABLE {$table[0]}");
    }
    
    $message = '<div class="notice notice-success"><p>Đã tối ưu hóa database thành công!</p></div>';
}

if ($action === 'update_config') {
    // Cập nhật cấu hình trong wp-config.php
    $config_file = ABSPATH . 'wp-config.php';
    $config_content = file_get_contents($config_file);
    
    // Kiểm tra xem đã có cấu hình bộ nhớ chưa
    if (strpos($config_content, 'WP_MEMORY_LIMIT') === false) {
        // Thêm cấu hình bộ nhớ
        $config_content = str_replace(
            "/* Add any custom values between this line and the \"stop editing\" line. */",
            "/* Add any custom values between this line and the \"stop editing\" line. */\n\n// Tăng giới hạn bộ nhớ cho WordPress\ndefine('WP_MEMORY_LIMIT', '512M');\ndefine('WP_MAX_MEMORY_LIMIT', '1024M');\n\n// Tăng thời gian thực thi script\n@ini_set('max_execution_time', 300);\n\n// Tăng giới hạn kích thước yêu cầu\n@ini_set('post_max_size', '64M');\n",
            $config_content
        );
        
        // Lưu lại file cấu hình
        file_put_contents($config_file, $config_content);
        
        $message = '<div class="notice notice-success"><p>Đã cập nhật cấu hình bộ nhớ trong wp-config.php thành công!</p></div>';
    } else {
        $message = '<div class="notice notice-warning"><p>Cấu hình bộ nhớ đã tồn tại trong wp-config.php.</p></div>';
    }
}

if ($action === 'enable_lite_mode') {
    // Kích hoạt chế độ lite cho plugin
    update_option('qlsv_use_lite_mode', 'yes');
    
    // Cập nhật nội dung các trang sử dụng shortcode
    $pages = get_posts(array(
        'post_type' => 'page',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        's' => '[qlsv_tim_kiem_diem]'
    ));
    
    foreach ($pages as $page) {
        $content = str_replace('[qlsv_tim_kiem_diem]', '[qlsv_tim_kiem_diem_lite]', $page->post_content);
        $content = str_replace('[qlsv_bang_diem]', '[qlsv_bang_diem_lite]', $content);
        
        wp_update_post(array(
            'ID' => $page->ID,
            'post_content' => $content
        ));
    }
    
    $message = '<div class="notice notice-success"><p>Đã kích hoạt chế độ lite cho plugin QLSV thành công!</p></div>';
}

// Kiểm tra thông tin hệ thống
$memory_limit = ini_get('memory_limit');
$max_execution_time = ini_get('max_execution_time');
$post_max_size = ini_get('post_max_size');
$upload_max_filesize = ini_get('upload_max_filesize');

// Kiểm tra xem plugin đã sử dụng phiên bản lite chưa
$using_lite_mode = get_option('qlsv_use_lite_mode', 'no');

// Kiểm tra số lượng bản ghi trong các bảng liên quan
global $wpdb;
$diem_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'diem' AND post_status = 'publish'");
$sinhvien_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'sinhvien' AND post_status = 'publish'");
$monhoc_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'monhoc' AND post_status = 'publish'");
$lop_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'lop' AND post_status = 'publish'");

// HTML header
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Công cụ chẩn đoán bộ nhớ - QLSV Plugin</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            color: #444;
            line-height: 1.6;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f1f1f1;
        }
        .wrap {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h1 {
            color: #23282d;
            font-size: 24px;
            margin-top: 0;
        }
        h2 {
            color: #23282d;
            font-size: 20px;
            margin-top: 30px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table th, table td {
            text-align: left;
            padding: 10px;
            border: 1px solid #e5e5e5;
        }
        table th {
            background: #f9f9f9;
        }
        .button {
            background: #0073aa;
            border: none;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            font-size: 14px;
            border-radius: 3px;
            cursor: pointer;
            display: inline-block;
            margin-right: 10px;
        }
        .button:hover {
            background: #006799;
        }
        .button-secondary {
            background: #f7f7f7;
            border: 1px solid #ccc;
            color: #555;
        }
        .button-secondary:hover {
            background: #f0f0f0;
            color: #23282d;
        }
        .notice {
            background: #fff;
            border-left: 4px solid #fff;
            box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
            margin: 20px 0;
            padding: 12px;
        }
        .notice-success {
            border-left-color: #46b450;
        }
        .notice-warning {
            border-left-color: #ffb900;
        }
        .notice-error {
            border-left-color: #dc3232;
        }
        .card {
            background: #fff;
            border: 1px solid #e5e5e5;
            box-shadow: 0 1px 1px rgba(0,0,0,0.04);
            margin-bottom: 20px;
            padding: 15px;
        }
        .card h3 {
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .status-good {
            color: #46b450;
            font-weight: bold;
        }
        .status-warning {
            color: #ffb900;
            font-weight: bold;
        }
        .status-bad {
            color: #dc3232;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <h1>Công cụ chẩn đoán và khắc phục vấn đề bộ nhớ - QLSV Plugin</h1>
        
        <?php echo $message; ?>
        
        <div class="card">
            <h3>Tóm tắt tình trạng</h3>
            <p>
                Công cụ này giúp chẩn đoán và khắc phục các vấn đề bộ nhớ trong plugin QLSV.
                Dưới đây là thông tin về cấu hình hiện tại và các giải pháp khả thi.
            </p>
        </div>
        
        <h2>Thông tin hệ thống</h2>
        <table>
            <tr>
                <th>Cấu hình</th>
                <th>Giá trị hiện tại</th>
                <th>Trạng thái</th>
                <th>Khuyến nghị</th>
            </tr>
            <tr>
                <td>Giới hạn bộ nhớ PHP</td>
                <td><?php echo $memory_limit; ?></td>
                <td>
                    <?php 
                    $memory_value = intval($memory_limit);
                    if ($memory_value >= 512) {
                        echo '<span class="status-good">Tốt</span>';
                    } elseif ($memory_value >= 256) {
                        echo '<span class="status-warning">Có thể cải thiện</span>';
                    } else {
                        echo '<span class="status-bad">Cần tăng</span>';
                    }
                    ?>
                </td>
                <td>Tối thiểu 512M, khuyến nghị 1024M</td>
            </tr>
            <tr>
                <td>Thời gian thực thi tối đa</td>
                <td><?php echo $max_execution_time; ?> giây</td>
                <td>
                    <?php 
                    if ($max_execution_time >= 300 || $max_execution_time == 0) {
                        echo '<span class="status-good">Tốt</span>';
                    } elseif ($max_execution_time >= 60) {
                        echo '<span class="status-warning">Có thể cải thiện</span>';
                    } else {
                        echo '<span class="status-bad">Cần tăng</span>';
                    }
                    ?>
                </td>
                <td>Tối thiểu 60 giây, khuyến nghị 300 giây</td>
            </tr>
            <tr>
                <td>Kích thước post tối đa</td>
                <td><?php echo $post_max_size; ?></td>
                <td>
                    <?php 
                    $post_size = intval($post_max_size);
                    if ($post_size >= 32) {
                        echo '<span class="status-good">Tốt</span>';
                    } elseif ($post_size >= 8) {
                        echo '<span class="status-warning">Có thể cải thiện</span>';
                    } else {
                        echo '<span class="status-bad">Cần tăng</span>';
                    }
                    ?>
                </td>
                <td>Tối thiểu 8M, khuyến nghị 64M</td>
            </tr>
            <tr>
                <td>Chế độ Lite</td>
                <td><?php echo $using_lite_mode === 'yes' ? 'Đã kích hoạt' : 'Chưa kích hoạt'; ?></td>
                <td>
                    <?php 
                    if ($using_lite_mode === 'yes') {
                        echo '<span class="status-good">Tốt</span>';
                    } else {
                        echo '<span class="status-warning">Nên kích hoạt</span>';
                    }
                    ?>
                </td>
                <td>Kích hoạt để giảm sử dụng bộ nhớ</td>
            </tr>
        </table>
        
        <h2>Thống kê dữ liệu</h2>
        <table>
            <tr>
                <th>Loại dữ liệu</th>
                <th>Số lượng</th>
                <th>Tác động đến bộ nhớ</th>
            </tr>
            <tr>
                <td>Điểm</td>
                <td><?php echo $diem_count; ?></td>
                <td>
                    <?php 
                    if ($diem_count > 5000) {
                        echo '<span class="status-bad">Cao</span>';
                    } elseif ($diem_count > 1000) {
                        echo '<span class="status-warning">Trung bình</span>';
                    } else {
                        echo '<span class="status-good">Thấp</span>';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td>Sinh viên</td>
                <td><?php echo $sinhvien_count; ?></td>
                <td>
                    <?php 
                    if ($sinhvien_count > 1000) {
                        echo '<span class="status-warning">Trung bình</span>';
                    } else {
                        echo '<span class="status-good">Thấp</span>';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td>Môn học</td>
                <td><?php echo $monhoc_count; ?></td>
                <td>
                    <?php 
                    if ($monhoc_count > 100) {
                        echo '<span class="status-warning">Trung bình</span>';
                    } else {
                        echo '<span class="status-good">Thấp</span>';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td>Lớp</td>
                <td><?php echo $lop_count; ?></td>
                <td>
                    <?php 
                    if ($lop_count > 100) {
                        echo '<span class="status-warning">Trung bình</span>';
                    } else {
                        echo '<span class="status-good">Thấp</span>';
                    }
                    ?>
                </td>
            </tr>
        </table>
        
        <h2>Các giải pháp khắc phục</h2>
        
        <div class="card">
            <h3>1. Kích hoạt chế độ Lite</h3>
            <p>Chế độ Lite sử dụng các truy vấn SQL trực tiếp thay vì WP_Query và giới hạn số lượng kết quả hiển thị để giảm sử dụng bộ nhớ.</p>
            <form method="post">
                <input type="hidden" name="action" value="enable_lite_mode">
                <button type="submit" class="button" <?php echo $using_lite_mode === 'yes' ? 'disabled' : ''; ?>>
                    <?php echo $using_lite_mode === 'yes' ? 'Đã kích hoạt' : 'Kích hoạt chế độ Lite'; ?>
                </button>
            </form>
        </div>
        
        <div class="card">
            <h3>2. Cập nhật cấu hình bộ nhớ</h3>
            <p>Cập nhật file wp-config.php để tăng giới hạn bộ nhớ cho WordPress.</p>
            <form method="post">
                <input type="hidden" name="action" value="update_config">
                <button type="submit" class="button">Cập nhật cấu hình</button>
            </form>
        </div>
        
        <div class="card">
            <h3>3. Xóa cache</h3>
            <p>Xóa các transient và cache để giải phóng bộ nhớ.</p>
            <form method="post">
                <input type="hidden" name="action" value="clear_cache">
                <button type="submit" class="button">Xóa cache</button>
            </form>
        </div>
        
        <div class="card">
            <h3>4. Tối ưu hóa database</h3>
            <p>Tối ưu hóa các bảng trong database để giảm kích thước và cải thiện hiệu suất.</p>
            <form method="post">
                <input type="hidden" name="action" value="optimize_db">
                <button type="submit" class="button">Tối ưu hóa database</button>
            </form>
        </div>
        
        <div class="card">
            <h3>5. Sử dụng trang Lite</h3>
            <p>Sử dụng trang lite-page.php để xem kết quả học tập mà không gặp lỗi bộ nhớ.</p>
            <a href="<?php echo plugins_url('lite-page.php', dirname(__FILE__)); ?>" class="button" target="_blank">Mở trang Lite</a>
        </div>
        
        <div class="card">
            <h3>Thông tin bổ sung</h3>
            <p>Nếu vẫn gặp vấn đề sau khi áp dụng các giải pháp trên, hãy xem xét:</p>
            <ul>
                <li>Nâng cấp hosting để có nhiều tài nguyên hơn</li>
                <li>Chia nhỏ dữ liệu thành nhiều nhóm nhỏ hơn</li>
                <li>Sử dụng phân trang với số lượng mục nhỏ hơn mỗi trang</li>
                <li>Tắt các plugin không cần thiết để giảm sử dụng bộ nhớ</li>
            </ul>
        </div>
    </div>
</body>
</html> 