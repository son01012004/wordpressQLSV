<?php
/**
 * Trang hướng dẫn sử dụng trang đăng nhập tùy chỉnh
 *
 * @package QLSV
 */

// Ngăn chặn truy cập trực tiếp
if (!defined('WPINC')) {
    die;
}

/**
 * Hướng dẫn sử dụng trang đăng nhập tùy chỉnh
 * 
 * 1. Sử dụng trang đăng nhập mặc định của WordPress (đã được tùy chỉnh CSS)
 *    - Plugin tự động áp dụng CSS tùy chỉnh cho trang wp-login.php
 *    - Không cần thêm bất kỳ shortcode nào
 * 
 * 2. Sử dụng shortcode trong trang WordPress
 *    - Tạo một trang mới trong WordPress
 *    - Thêm shortcode [qlsv_login_form] vào nội dung trang
 *    - Xuất bản trang
 * 
 * 3. Sử dụng trang đăng nhập tùy chỉnh hoàn toàn
 *    - Tạo một trang mới trong WordPress với slug là "login"
 *    - Không cần thêm nội dung gì, plugin sẽ tự động hiển thị form đăng nhập
 *    - Xuất bản trang
 *    - Cũng có thể thêm tham số ?custom-login vào bất kỳ URL nào để hiển thị trang đăng nhập
 * 
 * 4. Tùy chỉnh theme trang đăng nhập tùy chỉnh
 *    - Chỉnh sửa file CSS tại: wp-content/plugins/qlsv-plugin-fixed/assets/css/qlsv-login.css
 *    - Thay đổi logo tại: wp-content/plugins/qlsv-plugin-fixed/assets/images/logo.png
 */ 