<?php
/**
 * Template hiển thị danh sách thông báo
 *
 * @package QLSV
 */

// Ngăn chặn truy cập trực tiếp
if (!defined('WPINC')) {
    die;
}

get_header();

// Kiểm tra quyền truy cập
if (!is_user_logged_in()) {
    ?>
    <div class="qlsv-container">
        <div class="qlsv-thong-bao">
            <p><?php esc_html_e('Bạn cần đăng nhập để xem thông báo.', 'qlsv'); ?></p>
            <p><a href="<?php echo esc_url(wp_login_url('http://localhost/wordpressQLSV/')); ?>" class="button"><?php esc_html_e('Đăng nhập', 'qlsv'); ?></a></p>
        </div>
    </div>
    <?php
    get_footer();
    return;
}
?>

<div class="qlsv-container">
    <h1 class="qlsv-archive-title"><?php _e('Thông báo', 'qlsv'); ?></h1>
    
    <div class="qlsv-thongbao-archive-content">
        <?php if (have_posts()): ?>
            <div class="qlsv-thongbao-list">
                <?php while (have_posts()): the_post(); ?>
                    <article class="qlsv-thongbao-item">
                        <h2 class="qlsv-thongbao-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>
                        
                        <div class="qlsv-thongbao-meta">
                            <span class="qlsv-thongbao-date">
                                <?php echo get_the_date(); ?>
                            </span>
                            <?php if (get_the_author()): ?>
                                <span class="qlsv-thongbao-author">
                                    <?php _e('Đăng bởi:', 'qlsv'); ?> <?php the_author(); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="qlsv-thongbao-excerpt">
                            <?php the_excerpt(); ?>
                        </div>
                        
                        <div class="qlsv-thongbao-actions">
                            <a href="<?php the_permalink(); ?>" class="qlsv-thongbao-read-more">
                                <?php _e('Xem chi tiết', 'qlsv'); ?>
                            </a>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
            
            <div class="qlsv-pagination">
                <?php
                the_posts_pagination(array(
                    'mid_size' => 2,
                    'prev_text' => __('« Trước', 'qlsv'),
                    'next_text' => __('Sau »', 'qlsv'),
                ));
                ?>
            </div>
        <?php else: ?>
            <div class="qlsv-no-posts">
                <p><?php _e('Không có thông báo nào.', 'qlsv'); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .qlsv-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    }
    .qlsv-archive-title {
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
        color: #333;
    }
    .qlsv-thongbao-archive-content {
        background: #fff;
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        padding: 20px;
    }
    .qlsv-thong-bao {
        padding: 20px !important;
        background: #f8f8f8 !important;
        border-left: 4px solid #ccc !important;
        margin-bottom: 20px !important;
    }
    .qlsv-thongbao-item {
        margin-bottom: 25px;
        padding-bottom: 25px;
        border-bottom: 1px solid #eee;
    }
    .qlsv-thongbao-title {
        margin-top: 0;
        margin-bottom: 10px;
    }
    .qlsv-thongbao-title a {
        color: #0073aa;
        text-decoration: none;
    }
    .qlsv-thongbao-title a:hover {
        color: #00a0d2;
        text-decoration: none;
    }
    .qlsv-thongbao-meta {
        font-size: 0.9em;
        color: #666;
        margin-bottom: 15px;
    }
    .qlsv-thongbao-date {
        margin-right: 15px;
    }
    .qlsv-thongbao-excerpt {
        margin-bottom: 15px;
    }
    .qlsv-thongbao-actions {
        text-align: right;
    }
    .qlsv-thongbao-read-more {
        display: inline-block;
        padding: 8px 15px;
        background: #0073aa;
        color: #fff;
        text-decoration: none;
        border-radius: 3px;
        font-size: 0.9em;
        transition: background 0.3s;
    }
    .qlsv-thongbao-read-more:hover {
        background: #005177;
        color: #fff;
        text-decoration: none;
    }
    .qlsv-pagination {
        margin-top: 30px;
        text-align: center;
    }
    .qlsv-pagination .page-numbers {
        display: inline-block;
        padding: 8px 12px;
        margin: 0 3px;
        border: 1px solid #ddd;
        background: #fff;
        color: #333;
        text-decoration: none;
        border-radius: 3px;
    }
    .qlsv-pagination .page-numbers.current {
        background: #0073aa;
        color: #fff;
        border-color: #0073aa;
    }
    .qlsv-pagination .page-numbers:hover {
        background: #f5f5f5;
    }
    .qlsv-no-posts {
        padding: 20px;
        background: #f9f9f9;
        border-radius: 5px;
        text-align: center;
    }
</style>

<?php get_footer(); ?> 