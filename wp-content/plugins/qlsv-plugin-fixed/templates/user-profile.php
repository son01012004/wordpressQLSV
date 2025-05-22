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

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <?php
        // Hiển thị shortcode cho thông tin người dùng
        echo do_shortcode('[qlsv_user_profile]');
        ?>
    </main>
</div>

<style>
    /* Đảm bảo header và footer hiển thị đúng */
    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }
    
    #content,
    main,
    .rt-container-fluid,
    .rt-main {
        flex: 1;
    }
    
    header,
    footer {
        flex-shrink: 0;
        display: block !important;
        visibility: visible !important;
    }
    
    /* Đảm bảo thông tin sinh viên hiển thị đúng */
    .thong-tin-sinh-vien-container {
        margin: 20px auto !important;
        max-width: 800px !important;
    }
</style>

<?php
get_sidebar();
get_footer();
?> 