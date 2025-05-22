<?php
/**
 * Template hiển thị archive của sinh viên
 *
 * @package QLSV
 */

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <header class="page-header">
            <h1 class="page-title"><?php esc_html_e('Danh sách sinh viên', 'qlsv'); ?></h1>
        </header>
        
        <div class="page-content">
            <?php 
            // Sử dụng shortcode để hiển thị danh sách sinh viên
            echo do_shortcode('[qlsv_danh_sach_sinh_vien]'); 
            ?>
        </div>
    </main>
</div>

<?php
get_sidebar();
get_footer();
?> 