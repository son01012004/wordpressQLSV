<?php
/**
 * Template hiển thị danh sách giáo viên
 *
 * @package QLSV
 */

// Ngăn chặn truy cập trực tiếp
if (!defined('WPINC')) {
    die;
}
?>

<div class="giaovien-container">
    <h2 class="giaovien-title"><?php echo esc_html__('Danh sách giáo viên', 'qlsv'); ?></h2>
    
    <?php if (empty($teachers)) : ?>
        <p class="no-data"><?php esc_html_e('Không có giáo viên nào.', 'qlsv'); ?></p>
    <?php else : ?>
        <div class="giaovien-list">
            <?php foreach ($teachers as $teacher) : 
                // Lấy thông tin giáo viên từ ACF
                $ma_gv = get_field('ma_giaovien', 'user_' . $teacher->ID);
                $hoc_vi = get_field('hoc_vi', 'user_' . $teacher->ID);
                $khoa = get_field('khoa', 'user_' . $teacher->ID);
                $hinh_anh = get_field('hinh_anh', 'user_' . $teacher->ID);
                $chuyen_mon = get_field('chuyen_mon', 'user_' . $teacher->ID);
            ?>
                <div class="giaovien-card">
                    <div class="giaovien-header">
                        <div class="giaovien-avatar">
                            <?php if (!empty($hinh_anh)) : ?>
                                <img src="<?php echo esc_url($hinh_anh['url']); ?>" alt="<?php echo esc_attr($teacher->display_name); ?>">
                            <?php else : ?>
                                <div class="giaovien-avatar-placeholder">
                                    <span><?php echo substr($teacher->display_name, 0, 1); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="giaovien-info">
                            <h3 class="giaovien-name">
                                <?php echo esc_html(!empty($hoc_vi) ? $hoc_vi . ' ' : ''); ?>
                                <?php echo esc_html($teacher->display_name); ?>
                            </h3>
                            
                            <?php if (!empty($ma_gv)) : ?>
                                <div class="giaovien-code">
                                    <?php echo esc_html__('Mã GV: ', 'qlsv') . esc_html($ma_gv); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($khoa)) : ?>
                                <div class="giaovien-department">
                                    <?php echo esc_html__('Khoa: ', 'qlsv') . esc_html($khoa); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($chuyen_mon)) : ?>
                                <div class="giaovien-speciality">
                                    <?php echo esc_html__('Chuyên môn: ', 'qlsv') . esc_html($chuyen_mon); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="giaovien-actions">
                        <a href="<?php echo esc_url(add_query_arg('gv', $teacher->ID, get_permalink())); ?>" class="giaovien-btn giaovien-view">
                            <?php esc_html_e('Xem chi tiết', 'qlsv'); ?>
                        </a>
                        <a href="<?php echo esc_url(add_query_arg(array('gv' => $teacher->ID, 'view' => 'tkb'), get_permalink())); ?>" class="giaovien-btn giaovien-schedule">
                            <?php esc_html_e('Lịch giảng dạy', 'qlsv'); ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .giaovien-container {
        margin-bottom: 30px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    }
    .giaovien-title {
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #ddd;
        color: #333;
    }
    .giaovien-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }
    .giaovien-card {
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        background-color: #fff;
        transition: transform 0.3s, box-shadow 0.3s;
    }
    .giaovien-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    .giaovien-header {
        display: flex;
        padding: 15px;
        border-bottom: 1px solid #f0f0f0;
    }
    .giaovien-avatar {
        width: 80px;
        height: 80px;
        margin-right: 15px;
        border-radius: 50%;
        overflow: hidden;
        flex-shrink: 0;
    }
    .giaovien-avatar img {
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
        font-size: 32px;
        font-weight: bold;
    }
    .giaovien-info {
        flex-grow: 1;
    }
    .giaovien-name {
        margin: 0 0 5px;
        font-size: 18px;
        color: #333;
    }
    .giaovien-code, 
    .giaovien-department, 
    .giaovien-speciality {
        font-size: 14px;
        color: #666;
        margin-top: 3px;
    }
    .giaovien-actions {
        padding: 15px;
        display: flex;
        justify-content: space-between;
        gap: 10px;
    }
    .giaovien-btn {
        padding: 8px 15px;
        background: #0073aa;
        color: #fff;
        text-decoration: none;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
        text-align: center;
        flex-grow: 1;
        transition: background 0.2s;
    }
    .giaovien-btn:hover {
        background: #005177;
        color: #fff;
        text-decoration: none;
    }
    .giaovien-schedule {
        background: #5e81ac;
    }
    .giaovien-schedule:hover {
        background: #3b5e88;
    }
    .no-data {
        padding: 20px;
        background: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 5px;
        text-align: center;
    }

    @media (max-width: 768px) {
        .giaovien-list {
            grid-template-columns: 1fr;
        }
    }
</style> 