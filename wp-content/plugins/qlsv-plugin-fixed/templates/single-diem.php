<?php
/**
 * Template hiển thị trang single cho post type 'diem'
 *
 * @package QLSV
 */

get_header();

// Kiểm tra quyền truy cập
if (!is_user_logged_in()) {
    ?>
    <div class="qlsv-container">
        <div class="qlsv-thong-bao">
            <p><?php esc_html_e('Bạn cần đăng nhập để xem thông tin điểm.', 'qlsv'); ?></p>
            <p><a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="button"><?php esc_html_e('Đăng nhập', 'qlsv'); ?></a></p>
        </div>
    </div>
    <?php
    get_footer();
    return;
}

// Lấy thông tin người dùng hiện tại
$current_user = wp_get_current_user();
$user_roles = $current_user->roles;
$is_admin = in_array('administrator', $user_roles);
$is_teacher = in_array('giaovien', $user_roles);
$is_student = in_array('student', $user_roles);

// Kiểm tra xem có phải sinh viên không (nếu không có role 'student')
if (!$is_student) {
    $args = array(
        'post_type' => 'sinhvien',
        'posts_per_page' => 1,
        'meta_query' => array(
            array(
                'key' => 'email',
                'value' => $current_user->user_email,
                'compare' => '='
            )
        )
    );
    
    $student_query = new WP_Query($args);
    $is_student = $student_query->have_posts();
    
    if ($is_student) {
        $student_query->the_post();
        $student_id = get_the_ID();
        wp_reset_postdata();
    }
}

// Lấy thông tin điểm hiện tại
$diem_id = get_the_ID();
$sinh_vien_id = get_field('sinh_vien', $diem_id);
$mon_hoc_id = get_field('mon_hoc', $diem_id);
$lop_id = get_field('lop', $diem_id);

// Kiểm tra quyền xem
$can_view = false;

// Admin và giáo viên có thể xem tất cả
if ($is_admin || $is_teacher) {
    $can_view = true;
}
// Sinh viên chỉ có thể xem điểm của mình
else if ($is_student && $sinh_vien_id == $student_id) {
    $can_view = true;
}

if (!$can_view) {
    ?>
    <div class="qlsv-container">
        <div class="qlsv-thong-bao">
            <p><?php esc_html_e('Bạn không có quyền xem thông tin điểm này.', 'qlsv'); ?></p>
            <p><a href="<?php echo esc_url(home_url('/diem/')); ?>" class="button"><?php esc_html_e('Quay lại', 'qlsv'); ?></a></p>
        </div>
    </div>
    <?php
    get_footer();
    return;
}

// Lấy dữ liệu
$diem1 = get_field('diem_thanh_phan_1_', $diem_id);
$diem2 = get_field('diem_thanh_phan_2_', $diem_id);
$cuoiki = get_field('diem_cuoi_ki_', $diem_id);

// Tên sinh viên, môn học và lớp
$sinh_vien = $sinh_vien_id ? get_the_title($sinh_vien_id) : 'N/A';
$mon_hoc = $mon_hoc_id ? get_the_title($mon_hoc_id) : 'N/A';
$lop = $lop_id ? get_the_title($lop_id) : 'N/A';

// Tính trung bình
$tb = '';
if (is_numeric($diem1) && is_numeric($diem2) && is_numeric($cuoiki)) {
    $tb = round(($diem1 * 0.2 + $diem2 * 0.2 + $cuoiki * 0.6), 2);
}

// Xếp loại và lớp CSS tương ứng
$xeploai = '';
$xeploai_class = '';
if ($tb !== '') {
    if ($tb >= 8.5) {
        $xeploai = 'Giỏi';
        $xeploai_class = 'gioi';
    } elseif ($tb >= 7) {
        $xeploai = 'Khá';
        $xeploai_class = 'kha';
    } elseif ($tb >= 5.5) {
        $xeploai = 'Trung bình';
        $xeploai_class = 'trung-binh';
    } else {
        $xeploai = 'Yếu';
        $xeploai_class = 'yeu';
    }
}
?>

<div class="qlsv-container">
    <h1 class="qlsv-page-title"><?php esc_html_e('Chi tiết điểm', 'qlsv'); ?></h1>
    
    <div class="notice notice-info">
        <p><strong>Thông báo:</strong> Đang sử dụng phiên bản tối ưu hiển thị bảng điểm.</p>
    </div>
    
    <div class="diem-detail-card <?php echo $xeploai_class; ?>">
        <div class="diem-header">
            <h2><?php echo esc_html($sinh_vien); ?></h2>
            <div class="diem-meta">
                <span class="diem-meta-item">
                    <strong><?php esc_html_e('Môn học:', 'qlsv'); ?></strong> <?php echo esc_html($mon_hoc); ?>
                </span>
                <?php if ($lop && $lop !== 'N/A'): ?>
                <span class="diem-meta-item">
                    <strong><?php esc_html_e('Lớp:', 'qlsv'); ?></strong> <?php echo esc_html($lop); ?>
                </span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="diem-body">
            <div class="diem-row">
                <div class="diem-label"><?php esc_html_e('Điểm thành phần 1:', 'qlsv'); ?></div>
                <div class="diem-value"><?php echo ($diem1 !== '' ? esc_html($diem1) : 'N/A'); ?></div>
            </div>
            <div class="diem-row">
                <div class="diem-label"><?php esc_html_e('Điểm thành phần 2:', 'qlsv'); ?></div>
                <div class="diem-value"><?php echo ($diem2 !== '' ? esc_html($diem2) : 'N/A'); ?></div>
            </div>
            <div class="diem-row">
                <div class="diem-label"><?php esc_html_e('Điểm cuối kỳ:', 'qlsv'); ?></div>
                <div class="diem-value"><?php echo ($cuoiki !== '' ? esc_html($cuoiki) : 'N/A'); ?></div>
            </div>
            <div class="diem-row diem-row-highlight">
                <div class="diem-label"><?php esc_html_e('Điểm trung bình:', 'qlsv'); ?></div>
                <div class="diem-value diem-tb"><?php echo ($tb !== '' ? esc_html($tb) : 'N/A'); ?></div>
            </div>
            <div class="diem-row diem-row-highlight">
                <div class="diem-label"><?php esc_html_e('Xếp loại:', 'qlsv'); ?></div>
                <div class="diem-value">
                    <?php if ($xeploai !== ''): ?>
                    <span class="xeploai-badge <?php echo $xeploai_class; ?>"><?php echo esc_html($xeploai); ?></span>
                    <?php else: ?>
                    N/A
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="diem-footer">
            <a href="<?php echo esc_url(home_url('/diem/')); ?>" class="button"><?php esc_html_e('Quay lại danh sách', 'qlsv'); ?></a>
            
            <?php if ($is_admin || $is_teacher): ?>
            <a href="<?php echo esc_url(admin_url('post.php?post=' . $diem_id . '&action=edit')); ?>" class="button button-primary"><?php esc_html_e('Chỉnh sửa', 'qlsv'); ?></a>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .qlsv-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .qlsv-page-title {
        margin-bottom: 30px;
        font-size: 28px;
        color: #333;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 15px;
    }
    
    .qlsv-thong-bao {
        padding: 20px;
        background: #f8f8f8;
        border-left: 4px solid #ccc;
        margin-bottom: 20px;
    }
    
    .diem-detail-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .diem-header {
        padding: 20px;
        border-bottom: 1px solid #eee;
        background: #f9f9f9;
    }
    
    .diem-header h2 {
        margin: 0 0 10px 0;
        font-size: 24px;
        color: #333;
    }
    
    .diem-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        font-size: 16px;
        color: #666;
    }
    
    .diem-body {
        padding: 20px;
    }
    
    .diem-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .diem-row:last-child {
        border-bottom: none;
    }
    
    .diem-row-highlight {
        background: #f9f9f9;
        padding: 15px;
        border-radius: 4px;
        margin-top: 10px;
    }
    
    .diem-label {
        font-weight: 500;
        color: #555;
    }
    
    .diem-value {
        font-weight: 500;
        color: #333;
    }
    
    .diem-tb {
        font-size: 18px;
        font-weight: bold;
    }
    
    .diem-footer {
        padding: 20px;
        border-top: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        gap: 10px;
    }
    
    /* Xếp loại */
    .xeploai-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 4px;
        font-weight: 500;
        font-size: 14px;
        letter-spacing: 0.5px;
        min-width: 80px;
        text-align: center;
    }
    
    .gioi .xeploai-badge.gioi {
        background-color: #d4edda;
        color: #155724;
    }
    
    .kha .xeploai-badge.kha {
        background-color: #fff3cd;
        color: #856404;
    }
    
    .trung-binh .xeploai-badge.trung-binh {
        background-color: #cce5ff;
        color: #004085;
    }
    
    .yeu .xeploai-badge.yeu {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    /* Responsive */
    @media screen and (max-width: 768px) {
        .diem-meta {
            flex-direction: column;
            gap: 5px;
        }
        
        .diem-row {
            flex-direction: column;
            gap: 5px;
        }
        
        .diem-footer {
            flex-direction: column;
        }
        
        .diem-footer .button {
            width: 100%;
            text-align: center;
        }
    }
    
    .notice-info {
        background-color: #e7f5ff;
        border-left: 4px solid #0073aa;
        margin-bottom: 20px;
        padding: 15px;
        border-radius: 4px;
    }
    
    /* Đảm bảo full layout với header và footer */
    body.single-diem {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }
    
    body.single-diem #content,
    body.single-diem main,
    body.single-diem .rt-container-fluid,
    body.single-diem .rt-main {
        flex: 1;
    }
    
    body.single-diem header,
    body.single-diem footer {
        flex-shrink: 0;
        display: block !important;
        visibility: visible !important;
    }
</style>

<?php get_footer(); ?> 