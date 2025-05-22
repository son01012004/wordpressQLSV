<?php
/**
 * Template hiển thị thông tin chi tiết giáo viên
 *
 * @package QLSV
 */

// Ngăn chặn truy cập trực tiếp
if (!defined('WPINC')) {
    die;
}
?>

<div class="giaovien-profile-container">
    <div class="giaovien-profile-header">
        <div class="giaovien-profile-avatar">
            <?php if (!empty($hinh_anh)) : ?>
                <?php 
                // Kiểm tra xem $hinh_anh có phải là ID hình ảnh hay là array
                if (is_numeric($hinh_anh)) {
                    $img_url = wp_get_attachment_image_url($hinh_anh, 'medium');
                    if ($img_url) {
                        $img_url = $img_url . (isset($cache_bust) ? $cache_bust : '');
                    }
                } else if (is_array($hinh_anh) && isset($hinh_anh['url'])) {
                    $img_url = $hinh_anh['url'] . (isset($cache_bust) ? $cache_bust : '');
                }
                
                if (!empty($img_url)) {
                    ?>
                    <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($teacher->display_name); ?>">
                    <?php
                } else {
                    ?>
                    <div class="giaovien-avatar-placeholder">
                        <span><?php echo substr($teacher->display_name, 0, 1); ?></span>
                    </div>
                    <?php
                }
                ?>
            <?php else : ?>
                <div class="giaovien-avatar-placeholder">
                    <span><?php echo substr($teacher->display_name, 0, 1); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="giaovien-profile-details">
            <h1 class="giaovien-profile-name">
                <?php echo esc_html(!empty($hoc_vi) ? $hoc_vi . ' ' : ''); ?>
                <?php echo esc_html($teacher->display_name); ?>
            </h1>
            
            <?php if (!empty($khoa)) : ?>
                <div class="giaovien-profile-department">
                    <i class="dashicons dashicons-building"></i>
                    <?php echo esc_html($khoa); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($ma_gv)) : ?>
                <div class="giaovien-profile-id">
                    <i class="dashicons dashicons-id"></i>
                    <?php echo esc_html__('Mã giảng viên: ', 'qlsv') . esc_html($ma_gv); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="giaovien-profile-body">
        <div class="giaovien-profile-section">
            <h3 class="giaovien-section-title"><?php esc_html_e('Thông tin liên hệ', 'qlsv'); ?></h3>
            
            <div class="giaovien-contact-info">
                <?php if (!empty($email_gv)) : ?>
                    <div class="giaovien-contact-item">
                        <i class="dashicons dashicons-email"></i>
                        <span><?php echo esc_html($email_gv); ?></span>
                    </div>
                <?php else : ?>
                    <div class="giaovien-contact-item">
                        <i class="dashicons dashicons-email"></i>
                        <span><?php echo esc_html($teacher->user_email); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($sdt)) : ?>
                    <div class="giaovien-contact-item">
                        <i class="dashicons dashicons-phone"></i>
                        <span><?php echo esc_html($sdt); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($chuyen_mon)) : ?>
            <div class="giaovien-profile-section">
                <h3 class="giaovien-section-title"><?php esc_html_e('Chuyên môn', 'qlsv'); ?></h3>
                <div class="giaovien-speciality-content">
                    <?php echo esc_html($chuyen_mon); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($gioi_thieu)) : ?>
            <div class="giaovien-profile-section">
                <h3 class="giaovien-section-title"><?php esc_html_e('Giới thiệu', 'qlsv'); ?></h3>
                <div class="giaovien-bio-content">
                    <?php echo wpautop($gioi_thieu); ?>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="giaovien-profile-section">
            <h3 class="giaovien-section-title"><?php esc_html_e('Lịch giảng dạy', 'qlsv'); ?></h3>
            
            <div class="giaovien-schedule-content">
                <?php echo do_shortcode('[qlsv_thoikhoabieu giang_vien="' . $teacher->ID . '"]'); ?>
            </div>
        </div>
    </div>
    
    <div class="giaovien-profile-footer">
        <a href="javascript:history.back();" class="giaovien-back-btn">
            <i class="dashicons dashicons-arrow-left-alt"></i>
            <?php esc_html_e('Quay lại', 'qlsv'); ?>
        </a>
    </div>
</div>

<style>
    .giaovien-profile-container {
        margin-bottom: 30px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    }
    .giaovien-profile-header {
        display: flex;
        align-items: center;
        margin-bottom: 30px;
        padding: 20px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    .giaovien-profile-avatar {
        width: 120px;
        height: 120px;
        margin-right: 25px;
        border-radius: 50%;
        overflow: hidden;
        box-shadow: 0 3px 8px rgba(0,0,0,0.2);
        flex-shrink: 0;
    }
    .giaovien-profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .giaovien-avatar-placeholder {
        width: 100%;
        height: 100%;
        background-color: #0073aa;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        font-weight: bold;
    }
    .giaovien-profile-details {
        flex-grow: 1;
    }
    .giaovien-profile-name {
        margin: 0 0 10px;
        font-size: 28px;
        color: #333;
    }
    .giaovien-profile-department,
    .giaovien-profile-id {
        display: flex;
        align-items: center;
        font-size: 16px;
        color: #666;
        margin-top: 5px;
    }
    .giaovien-profile-department i,
    .giaovien-profile-id i {
        margin-right: 8px;
    }
    
    .giaovien-profile-body {
        margin-bottom: 20px;
    }
    .giaovien-profile-section {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }
    .giaovien-section-title {
        margin-top: 0;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
        color: #0073aa;
    }
    .giaovien-contact-info {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }
    .giaovien-contact-item {
        display: flex;
        align-items: center;
        margin-right: 20px;
    }
    .giaovien-contact-item i {
        margin-right: 8px;
        color: #0073aa;
    }
    .giaovien-speciality-content,
    .giaovien-bio-content {
        color: #444;
        line-height: 1.6;
    }
    .giaovien-schedule-content {
        margin-top: 10px;
    }
    
    .giaovien-profile-footer {
        padding: 10px 0;
    }
    .giaovien-back-btn {
        display: inline-flex;
        align-items: center;
        padding: 8px 16px;
        background: #f0f0f0;
        color: #333;
        text-decoration: none;
        border-radius: 4px;
        transition: background 0.2s;
    }
    .giaovien-back-btn i {
        margin-right: 5px;
    }
    .giaovien-back-btn:hover {
        background: #e0e0e0;
        color: #0073aa;
        text-decoration: none;
    }
    
    @media (max-width: 768px) {
        .giaovien-profile-header {
            flex-direction: column;
            text-align: center;
        }
        .giaovien-profile-avatar {
            margin-right: 0;
            margin-bottom: 15px;
        }
        .giaovien-profile-department,
        .giaovien-profile-id {
            justify-content: center;
        }
    }
</style> 