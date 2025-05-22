<?php
/**
 * Template Name: Điểm danh Test
 * 
 * Trang test chức năng điểm danh
 */

get_header();
?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="page-content">
                <h1 class="entry-title">Quản lý điểm danh sinh viên</h1>
                
                <?php echo do_shortcode('[qlsv_diemdanh_dashboard]'); ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?> 