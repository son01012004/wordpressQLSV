<?php
/**
 * Template hiển thị thông tin người dùng dựa theo vai trò
 *
 * @package QLSV
 */

// Ngăn chặn truy cập trực tiếp
if (!defined('WPINC')) {
    die;
}

// Hiển thị shortcode cho thông tin người dùng
echo do_shortcode('[qlsv_user_profile]'); 