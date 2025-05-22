<?php
/**
 * Template Name: Form Điểm Danh Test
 * 
 * Trang test form điểm danh
 */

get_header();
?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="page-content">
                <h1 class="entry-title">Form Điểm Danh Sinh Viên</h1>
                
                <?php echo do_shortcode('[qlsv_diemdanh_form]'); ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?> 