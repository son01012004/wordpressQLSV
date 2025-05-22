<?php
/**
 * Template hiển thị thông tin chi tiết của một sinh viên
 *
 * @package QLSV
 */

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <?php
        // Start the loop.
        while (have_posts()) :
            the_post();
            
            // Lấy thông tin sinh viên
            $sinh_vien_id = get_the_ID();
            $ho_ten = get_the_title();
            $ma_sinh_vien = get_field('ma_sinh_vien', $sinh_vien_id);
            $ngay_sinh = get_field('ngay_sinh', $sinh_vien_id);
            $lop_id = get_field('lop', $sinh_vien_id);
            $khoa = get_field('khoa', $sinh_vien_id);
            $email = get_field('email', $sinh_vien_id);
            $so_dien_thoai = get_field('so_dien_thoai', $sinh_vien_id);
            $dia_chi = get_field('dia_chi', $sinh_vien_id);
            $anh_id = get_field('anh', $sinh_vien_id);
            $trang_thai = get_field('trang_thai', $sinh_vien_id);
            
            // Format ngày sinh
            $ngay_sinh_format = $ngay_sinh ? date_i18n('d/m/Y', strtotime($ngay_sinh)) : '';
            
            // Lấy tên lớp
            $ten_lop = '';
            if ($lop_id) {
                $ten_lop = get_the_title($lop_id);
            }
            
            // HTML hiển thị thông tin sinh viên
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('sinh-vien-detail'); ?>>
                <header class="entry-header">
                    <h1 class="entry-title"><?php echo esc_html($ho_ten); ?></h1>
                </header>
                
                <div class="sinh-vien-content">
                    <div class="sinh-vien-info">
                        <div class="sinh-vien-avatar">
                            <?php 
                            if ($anh_id) {
                                echo wp_get_attachment_image($anh_id, 'medium', false, array('class' => 'avatar-image'));
                            } else {
                                // Hiển thị ảnh mặc định nếu không có ảnh
                                echo '<div class="no-avatar">No Image</div>';
                            }
                            ?>
                        </div>
                        
                        <div class="sinh-vien-details">
                            <div class="info-item">
                                <strong><?php esc_html_e('Mã sinh viên:', 'qlsv'); ?></strong> 
                                <span><?php echo esc_html($ma_sinh_vien); ?></span>
                            </div>
                            
                            <div class="info-item">
                                <strong><?php esc_html_e('Ngày sinh:', 'qlsv'); ?></strong> 
                                <span><?php echo esc_html($ngay_sinh_format); ?></span>
                            </div>
                            
                            <div class="info-item">
                                <strong><?php esc_html_e('Lớp:', 'qlsv'); ?></strong> 
                                <span><?php echo esc_html($ten_lop); ?></span>
                            </div>
                            
                            <div class="info-item">
                                <strong><?php esc_html_e('Khoa:', 'qlsv'); ?></strong> 
                                <span><?php echo esc_html($khoa); ?></span>
                            </div>
                            
                            <div class="info-item">
                                <strong><?php esc_html_e('Email:', 'qlsv'); ?></strong> 
                                <span><?php echo esc_html($email); ?></span>
                            </div>
                            
                            <div class="info-item">
                                <strong><?php esc_html_e('Số điện thoại:', 'qlsv'); ?></strong> 
                                <span><?php echo esc_html($so_dien_thoai); ?></span>
                            </div>
                            
                            <?php if ($dia_chi) : ?>
                                <div class="info-item">
                                    <strong><?php esc_html_e('Địa chỉ:', 'qlsv'); ?></strong> 
                                    <span><?php echo esc_html($dia_chi); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="info-item">
                                <strong><?php esc_html_e('Trạng thái:', 'qlsv'); ?></strong> 
                                <span class="status-<?php echo sanitize_title($trang_thai); ?>"><?php echo esc_html($trang_thai); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($lop_id) : ?>
                    <!-- Hiển thị điểm của sinh viên nếu có -->
                    <div class="sinh-vien-scores">
                        <h3><?php esc_html_e('Bảng điểm', 'qlsv'); ?></h3>
                        <?php echo do_shortcode('[bang_diem sinhvien_id="'.$sinh_vien_id.'"]'); ?>
                    </div>
                    
                    <!-- Hiển thị thông tin điểm danh của sinh viên nếu có -->
                    <div class="sinh-vien-attendance">
                        <h3><?php esc_html_e('Thông tin điểm danh', 'qlsv'); ?></h3>
                        <?php echo do_shortcode('[qlsv_diemdanh_sinhvien sinhvien_id="'.$sinh_vien_id.'"]'); ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="sinh-vien-content-description">
                        <?php the_content(); ?>
                    </div>
                </div>
                
                <footer class="entry-footer">
                    <a href="<?php echo esc_url(get_post_type_archive_link('sinhvien')); ?>" class="back-btn">
                        <?php esc_html_e('Quay lại danh sách', 'qlsv'); ?>
                    </a>
                </footer>
            </article>
        <?php
        endwhile;
        ?>
    </main>
</div>

<style>
    .sinh-vien-detail {
        margin-bottom: 30px;
    }
    .sinh-vien-info {
        display: flex;
        flex-wrap: wrap;
        margin-bottom: 30px;
    }
    .sinh-vien-avatar {
        flex: 0 0 200px;
        margin-right: 30px;
        margin-bottom: 20px;
    }
    .avatar-image {
        max-width: 100%;
        height: auto;
        border-radius: 5px;
    }
    .no-avatar {
        width: 200px;
        height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f5f5f5;
        border-radius: 5px;
        color: #888;
    }
    .sinh-vien-details {
        flex: 1;
        min-width: 300px;
    }
    .info-item {
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    .info-item strong {
        display: inline-block;
        width: 150px;
        font-weight: bold;
    }
    .sinh-vien-scores,
    .sinh-vien-attendance {
        margin-top: 30px;
        margin-bottom: 30px;
    }
    .sinh-vien-scores h3,
    .sinh-vien-attendance h3 {
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f0f0f0;
    }
    .status-dang-hoc {
        color: #4CAF50;
        font-weight: bold;
    }
    .status-bao-luu {
        color: #FF9800;
        font-weight: bold;
    }
    .status-da-tot-nghiep {
        color: #2196F3;
        font-weight: bold;
    }
    .status-nghi-hoc {
        color: #F44336;
        font-weight: bold;
    }
    .back-btn {
        display: inline-block;
        background: #0073aa;
        color: #fff;
        text-decoration: none;
        padding: 8px 15px;
        border-radius: 4px;
        margin-top: 20px;
    }
    .back-btn:hover {
        background: #005177;
        color: #fff;
    }
    
    @media (max-width: 768px) {
        .sinh-vien-info {
            flex-direction: column;
        }
        .sinh-vien-avatar {
            margin-right: 0;
        }
    }
</style>

<?php
get_sidebar();
get_footer();
?> 