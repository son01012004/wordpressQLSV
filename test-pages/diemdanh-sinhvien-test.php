<?php
/**
 * Template Name: Điểm Danh Sinh Viên Test
 * 
 * Trang test xem điểm danh sinh viên
 */

get_header();
?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="page-content">
                <h1 class="entry-title">Thống Kê Điểm Danh Sinh Viên</h1>
                
                <?php 
                // Lấy sinh viên đầu tiên để test
                $args = array(
                    'post_type' => 'sinhvien',
                    'posts_per_page' => 1
                );
                
                $student_query = new WP_Query($args);
                
                if ($student_query->have_posts()) {
                    $student_query->the_post();
                    $student_id = get_the_ID();
                    wp_reset_postdata();
                    
                    echo do_shortcode('[qlsv_diemdanh_sinhvien sinhvien_id="' . $student_id . '"]');
                } else {
                    echo '<p>Không tìm thấy sinh viên nào để test.</p>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?> 