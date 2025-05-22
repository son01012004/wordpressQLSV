<?php
/**
 * Template Name: Trang Điểm Danh
 * Description: Template hiển thị trang điểm danh tổng hợp
 *
 * @package QLSV
 */

get_header();

// Kiểm tra xem theme có hỗ trợ các container chuẩn không
$has_primary = false;
ob_start();
do_action('get_template_part_content', 'page');
$content = ob_get_clean();
if (strpos($content, 'id="primary"') !== false || strpos($content, 'class="content-area"') !== false) {
    $has_primary = true;
}
?>

<?php if ($has_primary) : ?>
    <div id="primary" class="content-area">
        <main id="main" class="site-main">
<?php endif; ?>

            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                            <header class="entry-header">
                                <h1 class="entry-title"><?php the_title(); ?></h1>
                            </header>

                            <div class="entry-content">
                                <?php 
                                while (have_posts()) : 
                                    the_post(); 
                                    // Hiển thị nội dung trang nếu có
                                    the_content();
                                endwhile;
                                
                                // Hiển thị shortcode điểm danh
                                echo do_shortcode('[qlsv_diemdanh_dashboard]'); 
                                ?>
                            </div>
                        </article>
                    </div>
                </div>
            </div>

<?php if ($has_primary) : ?>
        </main>
    </div>
<?php endif; ?>

<?php
get_footer();
?> 