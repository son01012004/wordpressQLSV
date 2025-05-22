<?php
/**
 * Template hiển thị thông tin giáo viên
 * 
 * @package QLSV
 */

// Kiểm tra tồn tại biến
if (!isset($teacher) || !isset($ma_gv) || !isset($hinh_anh_url)) {
    return;
}
?>

<div class="giaovien-profile-container">
    <h2><?php echo esc_html($teacher->display_name); ?></h2>
    
    <div class="giaovien-profile-header">
        <div class="giaovien-profile-avatar">
            <?php if ($hinh_anh_url) : ?>
                <img src="<?php echo esc_url($hinh_anh_url); ?>" alt="<?php echo esc_attr($teacher->display_name); ?>" class="avatar">
            <?php else : ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100" fill="none">
                    <circle cx="50" cy="50" r="50" fill="#2575fc"/>
                    <path d="M50,20 C58.8,20 66,27.2 66,36 C66,44.8 58.8,52 50,52 C41.2,52 34,44.8 34,36 C34,27.2 41.2,20 50,20 Z M50,58 C67.7,58 82,72.3 82,90.3 L82,92 C82,93.1 81.1,94 80,94 L20,94 C18.9,94 18,93.1 18,92 L18,90.3 C18,72.3 32.3,58 50,58 Z" fill="white"/>
                </svg>
            <?php endif; ?>
        </div>
        
        <div class="giaovien-info-preview">
            <?php if ($hoc_vi) : ?>
                <div class="giaovien-hoc-vi"><?php echo esc_html($hoc_vi); ?></div>
            <?php endif; ?>
            
            <?php if ($ma_gv) : ?>
                <div class="giaovien-ma">
                    <span class="label"><?php _e('Mã giáo viên:', 'qlsv'); ?></span>
                    <span class="value"><?php echo esc_html($ma_gv); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($khoa) : ?>
                <div class="giaovien-khoa">
                    <span class="label"><?php _e('Khoa:', 'qlsv'); ?></span>
                    <span class="value"><?php echo esc_html($khoa); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($chuyen_mon) : ?>
                <div class="giaovien-chuyen-mon">
                    <span class="label"><?php _e('Chuyên môn:', 'qlsv'); ?></span>
                    <span class="value"><?php echo esc_html($chuyen_mon); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="giaovien-profile-content">
        <div class="giaovien-profile-section">
            <h3><?php _e('Thông tin liên hệ', 'qlsv'); ?></h3>
            <table class="giaovien-contact-table">
                <?php if ($email_gv) : ?>
                <tr>
                    <th><?php _e('Email:', 'qlsv'); ?></th>
                    <td><a href="mailto:<?php echo esc_attr($email_gv); ?>"><?php echo esc_html($email_gv); ?></a></td>
                </tr>
                <?php endif; ?>
                
                <?php if ($sdt) : ?>
                <tr>
                    <th><?php _e('Số điện thoại:', 'qlsv'); ?></th>
                    <td><?php echo esc_html($sdt); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        
        <?php if ($gioi_thieu) : ?>
        <div class="giaovien-profile-section">
            <h3><?php _e('Giới thiệu', 'qlsv'); ?></h3>
            <div class="giaovien-gioi-thieu">
                <?php echo wpautop(esc_html($gioi_thieu)); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.giaovien-profile-container {
    max-width: 800px;
    margin: 0 auto;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    padding: 20px;
    overflow: hidden;
}

.giaovien-profile-container h2 {
    margin-top: 0;
    border-bottom: 1px solid #eee;
    padding-bottom: 15px;
    margin-bottom: 20px;
}

.giaovien-profile-header {
    display: flex;
    flex-wrap: wrap;
    margin-bottom: 30px;
}

.giaovien-profile-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    overflow: hidden;
    margin-right: 30px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: transparent;
    cursor: pointer;
}

.giaovien-profile-avatar img.avatar {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.giaovien-profile-avatar svg {
    width: 100%;
    height: 100%;
}

.giaovien-info-preview {
    flex-grow: 1;
}

.giaovien-hoc-vi {
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 10px;
}

.giaovien-ma, .giaovien-khoa, .giaovien-chuyen-mon {
    margin-bottom: 8px;
}

.giaovien-ma .label, .giaovien-khoa .label, .giaovien-chuyen-mon .label {
    font-weight: 600;
    margin-right: 5px;
}

.giaovien-profile-section {
    margin-bottom: 25px;
}

.giaovien-profile-section h3 {
    border-bottom: 1px solid #f0f0f0;
    padding-bottom: 10px;
    margin-bottom: 15px;
}

.giaovien-contact-table {
    width: 100%;
    border-collapse: collapse;
}

.giaovien-contact-table th, .giaovien-contact-table td {
    padding: 8px 10px;
    border-bottom: 1px solid #f0f0f0;
    text-align: left;
}

.giaovien-contact-table th {
    width: 30%;
    font-weight: 600;
}

.giaovien-gioi-thieu {
    line-height: 1.6;
}

/* Styling for the avatar upload form */
.avatar-upload-container {
    margin-top: 20px;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 8px;
    margin-bottom: 20px;
}

.avatar-preview {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    overflow: hidden;
    margin: 0 auto 15px;
}

.avatar-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

@media (max-width: 768px) {
    .giaovien-profile-header {
        flex-direction: column;
    }
    
    .giaovien-profile-avatar {
        margin-bottom: 20px;
    }
}
</style> 