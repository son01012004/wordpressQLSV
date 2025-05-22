<?php
/**
 * Template Name: Xem Điểm Danh Test
 * 
 * Trang test xem điểm danh
 */

get_header();
?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="page-content">
                <h1 class="entry-title">Xem Điểm Danh Sinh Viên</h1>
                
                <?php echo do_shortcode('[qlsv_diemdanh]'); ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?> 