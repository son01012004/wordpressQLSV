<?php
/**
 * Form tạo trang Điểm Danh
 */

// Nhận dữ liệu gửi từ form
if (isset($_POST['create_page'])) {
    // Load WordPress
    require_once('wp-load.php');
    
    // Xóa trang cũ nếu có
    if (isset($_POST['delete_old']) && $_POST['delete_old'] == 1) {
        $existing_page = get_page_by_path('diemdanh');
        if ($existing_page) {
            wp_delete_post($existing_page->ID, true);
            echo "<div style='color: green; padding: 10px; background-color: #f0f0f0; margin: 10px 0;'>Đã xóa trang Điểm Danh cũ.</div>";
        }
    }
    
    // Tạo trang mới
    $page_data = array(
        'post_title'    => 'Điểm Danh',
        'post_name'     => 'diemdanh',
        'post_content'  => '[qlsv_diemdanh_dashboard]',
        'post_status'   => 'publish',
        'post_type'     => 'page',
        'comment_status' => 'closed'
    );
    
    $page_id = wp_insert_post($page_data);
    
    if (is_wp_error($page_id)) {
        echo "<div style='color: red; padding: 10px; background-color: #f0f0f0; margin: 10px 0;'>Lỗi: " . $page_id->get_error_message() . "</div>";
    } else {
        // Thiết lập template
        update_post_meta($page_id, '_wp_page_template', 'diemdanh-template.php');
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        echo "<div style='color: green; padding: 10px; background-color: #f0f0f0; margin: 10px 0;'>Đã tạo trang Điểm Danh thành công với ID: $page_id</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tạo trang Điểm Danh</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
        }
        form {
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .info {
            background-color: #e7f3fe;
            border-left: 6px solid #2196F3;
            padding: 10px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Tạo trang Điểm Danh</h1>
        
        <div class="info">
            <p>Công cụ này sẽ tạo trang Điểm Danh với shortcode [qlsv_diemdanh_dashboard] và thiết lập template thích hợp.</p>
        </div>
        
        <form method="post" action="">
            <div class="form-group">
                <label>
                    <input type="checkbox" name="delete_old" value="1" checked>
                    Xóa trang Điểm Danh cũ (nếu có)
                </label>
            </div>
            
            <button type="submit" name="create_page">Tạo trang Điểm Danh</button>
        </form>
        
        <div style="margin-top: 30px;">
            <h3>Các bước tiếp theo:</h3>
            <ol>
                <li>Sau khi tạo trang, hãy truy cập vào <a href="wp-admin/options-permalink.php">Cài đặt Permalink</a> và nhấn "Lưu thay đổi" mà không cần thay đổi gì.</li>
                <li>Sau đó truy cập vào <a href="/wordpressQLSV/diemdanh/">Trang Điểm Danh</a> để kiểm tra.</li>
            </ol>
        </div>
    </div>
</body>
</html> 