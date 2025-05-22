<?php
/**
 * Template cho hình ảnh avatar mặc định dạng SVG
 * 
 * @package QLSV
 */

// Đảm bảo truy cập trực tiếp
if (!defined('WPINC')) {
    // Khi truy cập trực tiếp vẫn hiển thị SVG
    header('Content-Type: image/svg+xml');
}

// Xuất ảnh SVG
header('Content-Type: image/svg+xml');
echo '<?xml version="1.0" encoding="UTF-8"?>
<svg width="300" height="300" viewBox="0 0 300 300" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
    <defs>
        <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#6a11cb;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#2575fc;stop-opacity:1" />
        </linearGradient>
    </defs>
    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
        <circle fill="url(#grad1)" cx="150" cy="150" r="150"></circle>
        <path d="M150,60 C176.51,60 198,81.49 198,108 C198,134.51 176.51,156 150,156 C123.49,156 102,134.51 102,108 C102,81.49 123.49,60 150,60 Z M150,174 C203.01,174 246,217.49 246,271 L246,275 C246,278.31 243.31,281 240,281 L60,281 C56.69,281 54,278.31 54,275 L54,271 C54,217.49 96.99,174 150,174 Z" fill="#FFFFFF" fill-rule="nonzero"></path>
    </g>
</svg>';
exit;
?> 