<?php
/**
 * Index file for QLSV plugin tools
 */

// Load WordPress
define('WP_USE_THEMES', false);
require_once('../../../wp-load.php');

// Security check - only logged in admins can access
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    wp_die('You need to be logged in as an administrator to access this page.');
}

// Display header
echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QLSV Plugin Tools</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 20px auto;
            padding: 0 20px;
        }
        h1 {
            color: #23282d;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .tool-card {
            border: 1px solid #ccd0d4;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
            background: #fff;
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 3px;
        }
        .tool-card h2 {
            margin-top: 0;
        }
        .button {
            display: inline-block;
            text-decoration: none;
            font-size: 13px;
            line-height: 2.15384615;
            min-height: 30px;
            margin: 0;
            padding: 0 10px;
            cursor: pointer;
            border-width: 1px;
            border-style: solid;
            -webkit-appearance: none;
            border-radius: 3px;
            white-space: nowrap;
            box-sizing: border-box;
            color: #0071a1;
            border-color: #0071a1;
            background: #f3f5f6;
            vertical-align: top;
        }
        .button:hover {
            background: #f1f1f1;
            border-color: #016087;
            color: #016087;
        }
        .button-primary {
            background: #0071a1;
            border-color: #0071a1;
            color: #fff;
        }
        .button-primary:hover {
            background: #016087;
            border-color: #016087;
            color: #fff;
        }
        pre {
            padding: 10px;
            background: #f6f6f6;
            border: 1px solid #e0e0e0;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>QLSV Plugin Tools</h1>
';

// Display tools
echo '<div class="tool-card">
    <h2>Điểm Danh Maintenance Tools</h2>
    <p>Các công cụ này giúp khắc phục và bảo trì chức năng Điểm Danh.</p>
    <p>
        <a href="refresh-diemdanh.php" class="button button-primary">Refresh Diemdanh Settings</a>
        <a href="fix-permalinks.php" class="button button-primary">Khắc phục Lỗi 404</a>
        <a href="fix-rishi.php" class="button button-primary">Sửa lỗi Theme Rishi</a>
        <a href="test-diemdanh.php" class="button button-primary">Kiểm tra Chi Tiết</a>
        <a href="check-diemdanh-status.php" class="button">Kiểm tra Trạng thái</a>
        <a href="diemdanh-api.php?action=get_diemdanh_data&lop=1&mon_hoc=1" class="button">Kiểm tra API</a>
    </p>
</div>';

// Show installation instructions
echo '<div class="tool-card">
    <h2>Installation Instructions</h2>';
    
if (file_exists(plugin_dir_path(__FILE__) . 'diemdanh-fix-instructions.md')) {
    $instructions = file_get_contents(plugin_dir_path(__FILE__) . 'diemdanh-fix-instructions.md');
    
    // Convert markdown headings
    $instructions = preg_replace('/^## (.*?)$/m', '<h2>$1</h2>', $instructions);
    $instructions = preg_replace('/^### (.*?)$/m', '<h3>$1</h3>', $instructions);
    
    // Convert markdown lists
    $instructions = preg_replace('/^\d+\. (.*?)$/m', '<li>$1</li>', $instructions);
    $instructions = preg_replace('/^- (.*?)$/m', '<li>$1</li>', $instructions);
    
    // Convert markdown code blocks
    $instructions = preg_replace('/`(.*?)`/', '<code>$1</code>', $instructions);
    
    // Convert lists to HTML
    $instructions = preg_replace('/<li>(.*?)<\/li>\n<li>/', '<li>$1</li><li>', $instructions);
    $instructions = preg_replace('/<li>(.*?)<\/li>(\n{2,}|\n$)/', '<ul><li>$1</li></ul>', $instructions);
    
    // Clean up line endings
    $instructions = str_replace("</ul>\n<ul>", "", $instructions);
    
    echo $instructions;
} else {
    echo '<p>Instructions file not found. Please refer to the documentation provided with the plugin.</p>';
}

echo '</div>';

// Show plugin information
echo '<div class="tool-card">
    <h2>Plugin Information</h2>
    <ul>
        <li><strong>Plugin Name:</strong> Quản lý Sinh viên</li>
        <li><strong>Version:</strong> ' . QLSV_VERSION . '</li>
        <li><strong>Plugin Path:</strong> ' . QLSV_PLUGIN_DIR . '</li>
    </ul>
    <p>
        <a href="' . admin_url() . '" class="button">Return to Admin Dashboard</a>
    </p>
</div>';

// Display footer
echo '</body>
</html>';

/**
 * Đăng ký hook khi plugin được kích hoạt
 */
register_activation_hook(__FILE__, 'qlsv_plugin_activation');

/**
 * Hàm được gọi khi plugin được kích hoạt
 */
function qlsv_plugin_activation() {
    // Tạo thư mục avatars trong plugin
    $avatars_dir = plugin_dir_path(__FILE__) . 'assets/avatars/';
    if (!file_exists($avatars_dir)) {
        wp_mkdir_p($avatars_dir);
        // Đảm bảo quyền truy cập đúng
        chmod($avatars_dir, 0755);
    }
    
    // Thêm file .htaccess để bảo vệ thư mục
    $htaccess_file = $avatars_dir . '.htaccess';
    if (!file_exists($htaccess_file)) {
        $htaccess_content = "# Deny access to PHP files
<Files *.php>
    Order Deny,Allow
    Deny from all
</Files>

# Allow access to image files
<FilesMatch '\.(jpg|jpeg|png|gif|webp|svg)$'>
    Order Allow,Deny
    Allow from all
</FilesMatch>

# Disable directory browsing
Options -Indexes";
        
        file_put_contents($htaccess_file, $htaccess_content);
    }
    
    // Thêm file index.php trống để bảo vệ thêm
    $index_file = $avatars_dir . 'index.php';
    if (!file_exists($index_file)) {
        file_put_contents($index_file, '<?php // Silence is golden');
    }
    
    // Flush rewrite rules
    flush_rewrite_rules();
}
?> 