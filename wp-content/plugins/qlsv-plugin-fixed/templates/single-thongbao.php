<?php
/**
 * Template hiển thị chi tiết thông báo
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
            <p><?php esc_html_e('Bạn cần đăng nhập để xem chi tiết thông báo.', 'qlsv'); ?></p>
            <p><a href="<?php echo esc_url(wp_login_url('http://localhost/wordpressQLSV/')); ?>" class="button"><?php esc_html_e('Đăng nhập', 'qlsv'); ?></a></p>
        </div>
    </div>
    <?php
    get_footer();
    return;
}
?>

<div class="qlsv-container">
    <div class="qlsv-thongbao-detail">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('qlsv-thongbao-article'); ?>>
                <header class="qlsv-thongbao-header">
                    <h1 class="qlsv-thongbao-title"><?php the_title(); ?></h1>
                    
                    <div class="qlsv-thongbao-meta">
                        <span class="qlsv-thongbao-date">
                            <i class="dashicons dashicons-calendar-alt"></i> <?php echo get_the_date(); ?>
                        </span>
                        
                        <?php if (get_the_author()) : ?>
                            <span class="qlsv-thongbao-author">
                                <i class="dashicons dashicons-admin-users"></i> <?php the_author(); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </header>
                
                <?php if (has_post_thumbnail()) : ?>
                    <div class="qlsv-thongbao-thumbnail">
                        <?php the_post_thumbnail('large'); ?>
                    </div>
                <?php endif; ?>
                
                <div class="qlsv-thongbao-content">
                    <?php the_content(); ?>
                </div>
                
                <footer class="qlsv-thongbao-footer">
                    <?php if (has_tag()) : ?>
                        <div class="qlsv-thongbao-tags">
                            <span class="qlsv-thongbao-tags-label"><?php _e('Thẻ:', 'qlsv'); ?></span>
                            <?php the_tags('', ', ', ''); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="qlsv-thongbao-navigation">
                        <div class="qlsv-thongbao-prev">
                            <?php previous_post_link('%link', '« %title'); ?>
                        </div>
                        <div class="qlsv-thongbao-next">
                            <?php next_post_link('%link', '%title »'); ?>
                        </div>
                    </div>
                </footer>
            </article>
        <?php endwhile; endif; ?>
        
        <div class="qlsv-thongbao-actions">
            <a href="<?php echo esc_url(get_post_type_archive_link('thongbao')); ?>" class="qlsv-button">
                <?php _e('Quay lại danh sách thông báo', 'qlsv'); ?>
            </a>
        </div>
    </div>
</div>

<style>
    .qlsv-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    }
    .qlsv-thongbao-detail {
        background: #fff;
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        padding: 30px;
        margin-bottom: 30px;
    }
    .qlsv-thongbao-header {
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
    }
    .qlsv-thongbao-title {
        margin-top: 0;
        margin-bottom: 15px;
        color: #333;
        font-size: 2em;
    }
    .qlsv-thongbao-meta {
        color: #666;
        font-size: 0.9em;
    }
    .qlsv-thongbao-date, .qlsv-thongbao-author {
        margin-right: 15px;
        display: inline-block;
    }
    .qlsv-thongbao-thumbnail {
        margin-bottom: 20px;
    }
    .qlsv-thongbao-thumbnail img {
        max-width: 100%;
        height: auto;
        border-radius: 5px;
    }
    .qlsv-thongbao-content {
        line-height: 1.6;
        color: #333;
        margin-bottom: 30px;
    }
    .qlsv-thongbao-content p, .qlsv-thongbao-content ul, .qlsv-thongbao-content ol {
        margin-bottom: 20px;
    }
    .qlsv-thongbao-footer {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }
    .qlsv-thongbao-tags {
        margin-bottom: 20px;
        color: #666;
        font-size: 0.9em;
    }
    .qlsv-thongbao-tags-label {
        font-weight: bold;
        margin-right: 5px;
    }
    .qlsv-thongbao-navigation {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }
    .qlsv-thongbao-prev, .qlsv-thongbao-next {
        max-width: 45%;
    }
    .qlsv-thongbao-prev a, .qlsv-thongbao-next a {
        color: #0073aa;
        text-decoration: none;
    }
    .qlsv-thongbao-prev a:hover, .qlsv-thongbao-next a:hover {
        color: #00a0d2;
        text-decoration: underline;
    }
    .qlsv-thongbao-actions {
        margin-top: 30px;
        text-align: center;
    }
    .qlsv-button {
        display: inline-block;
        padding: 10px 20px;
        background: #0073aa;
        color: #fff;
        text-decoration: none;
        border-radius: 4px;
        font-weight: 600;
        transition: background 0.3s;
    }
    .qlsv-button:hover {
        background: #005177;
        color: #fff;
        text-decoration: none;
    }
    .qlsv-thong-bao {
        padding: 20px !important;
        background: #f8f8f8 !important;
        border-left: 4px solid #ccc !important;
        margin-bottom: 20px !important;
    }
    @media (max-width: 768px) {
        .qlsv-thongbao-navigation {
            flex-direction: column;
            gap: 15px;
        }
        .qlsv-thongbao-prev, .qlsv-thongbao-next {
            max-width: 100%;
        }
    }
</style>

<?php get_footer(); ?> 