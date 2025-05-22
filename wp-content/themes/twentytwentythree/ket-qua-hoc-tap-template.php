<?php
/**
 * Template Name: Trang Kết Quả Học Tập
 */

get_header();
?>

<div class="wp-block-group alignfull is-layout-constrained" style="padding-top: 40px; padding-right: 40px; padding-bottom: 40px; padding-left: 40px;">
    <div class="wp-block-group alignwide is-layout-flow">
        <h2 class="has-text-align-wide has-large-font-size"><?php the_title(); ?></h2>
        <div class="entry-content">
            <?php 
            // Hiển thị nội dung trang nếu có 
            while (have_posts()) : the_post();
                the_content();
            endwhile;
            
            // Hiển thị shortcode kết quả học tập
            echo do_shortcode('[qlsv_tim_kiem_diem]');
            ?>
        </div>
    </div>
</div>

<?php
get_footer();
?> 