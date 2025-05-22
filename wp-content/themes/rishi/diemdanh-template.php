<?php
/**
 * Template Name: Trang Điểm Danh
 */

get_header();
?>

<div class="wp-block-group alignfull is-layout-constrained" style="padding-top: 40px; padding-right: 40px; padding-bottom: 40px; padding-left: 40px;">
    <div class="wp-block-group alignwide is-layout-flow">
        <h2 class="has-text-align-wide has-large-font-size"><?php the_title(); ?></h2>
        <div class="entry-content">
            <?php 
            // Hiển thị nội dung trang nếu có 
            while ( have_posts() ) : the_post();
                the_content();
            endwhile;
            ?>

            <!-- Hiển thị trực tiếp nội dung shortcode -->
            <div class="diemdanh-dashboard-container">
                <?php 
                if (!is_user_logged_in()) {
                    echo '<div class="diemdanh-error-message">';
                    echo '<p>' . __("Bạn cần đăng nhập để sử dụng chức năng này.", "qlsv") . '</p>';
                    echo '<p><a href="' . esc_url(wp_login_url(get_permalink())) . '" class="diemdanh-btn">' . __("Đăng nhập", "qlsv") . '</a></p>';
                    echo '</div>';
                } else {
                    // Gọi shortcode điểm danh
                    echo do_shortcode("[qlsv_diemdanh_dashboard]");
                }
                ?>
            </div>
        </div>
    </div>
</div>

<style>
/* CSS để đảm bảo giao diện hiển thị đúng */
.diemdanh-dashboard-container {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    margin-bottom: 40px;
}

.diemdanh-error-message {
    background-color: #f8d7da;
    color: #721c24;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.diemdanh-btn {
    display: inline-block;
    background-color: #3498db;
    color: white !important;
    padding: 8px 16px;
    text-decoration: none;
    border-radius: 4px;
    font-weight: 500;
    margin-top: 10px;
}

.diemdanh-btn:hover {
    background-color: #2980b9;
    text-decoration: none;
}
</style>

<?php
get_footer();
?>