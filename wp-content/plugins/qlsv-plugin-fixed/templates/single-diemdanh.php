<?php
/**
 * Template cho trang single điểm danh
 *
 * @package QLSV
 */

// Thiết lập post để tránh lỗi
global $post;
if (!isset($post) || empty($post)) {
    // Tạo một đối tượng post để tránh lỗi
    $post = new stdClass();
    $post->ID = get_the_ID();
    $post->post_type = 'diemdanh';
    $post->post_title = get_the_title();
    $post->post_name = 'diemdanh';
    $post->post_content = '';
    $post->comment_count = 0;
    $post->post_author = 1;
    $post->post_date = get_the_date('Y-m-d H:i:s');
    $post->post_date_gmt = get_the_date('Y-m-d H:i:s');
}

get_header();

// Kiểm tra quyền truy cập
if (!is_user_logged_in()) {
    ?>
    <div class="qlsv-container">
        <div class="qlsv-thong-bao">
            <p><?php esc_html_e('Bạn cần đăng nhập để xem điểm danh.', 'qlsv'); ?></p>
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
    wp_reset_postdata();
}

// Lấy thông tin điểm danh
$lop_id = get_field('lop');
$mon_hoc_id = get_field('mon_hoc');
$ngay = get_field('ngay');
$ngay_format = $ngay ? date_i18n('d/m/Y', strtotime($ngay)) : 'N/A';

// Lấy danh sách trạng thái sinh viên đã điểm danh
$sinh_vien_status = get_post_meta(get_the_ID(), 'sinh_vien_status', true);
if (empty($sinh_vien_status)) {
    $sinh_vien_status = array();
}

// Xử lý cập nhật điểm danh
$update_message = '';
if (($is_admin || $is_teacher) && isset($_POST['update_diemdanh']) && isset($_POST['diemdanh_nonce']) && wp_verify_nonce($_POST['diemdanh_nonce'], 'update_diemdanh')) {
    if (isset($_POST['sinh_vien_status']) && is_array($_POST['sinh_vien_status'])) {
        $new_status = $_POST['sinh_vien_status'];
        update_post_meta(get_the_ID(), 'sinh_vien_status', $new_status);
        $sinh_vien_status = $new_status;
        $update_message = '<div class="diemdanh-success">Đã cập nhật điểm danh thành công!</div>';
    }
}

?>

<div class="qlsv-container">
    <h1 class="qlsv-page-title"><?php the_title(); ?></h1>
    
    <?php if (!empty($update_message)) echo $update_message; ?>
    
    <div class="diemdanh-info">
        <p><strong>Lớp:</strong> <?php echo esc_html(get_the_title($lop_id)); ?></p>
        <p><strong>Môn học:</strong> <?php echo esc_html(get_the_title($mon_hoc_id)); ?></p>
        <p><strong>Ngày:</strong> <?php echo esc_html($ngay_format); ?></p>
    </div>
    
    <?php if ($is_admin || $is_teacher): ?>
        <!-- FORM ĐIỂM DANH CHO GIÁO VIÊN/ADMIN -->
        <form method="post" class="diemdanh-form">
            <h2>Danh sách điểm danh</h2>
            
            <?php
            // Lấy instance của class QLSV_DiemDanh
            global $qlsv_loader;
            $diemdanh = new QLSV_DiemDanh($qlsv_loader);
            
            // Lấy danh sách sinh viên
            $students = $diemdanh->get_students_by_class($lop_id);
            
            if (!empty($students)): ?>
                <table class="diemdanh-table widefat">
                    <thead>
                        <tr>
                            <th width="5%">STT</th>
                            <th width="15%">MSSV</th>
                            <th width="25%">Họ và tên</th>
                            <th width="15%">Điểm danh</th>
                            <th width="40%">Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $stt = 1;
                        foreach ($students as $student): 
                            $student_id = $student['id'];
                            $status = isset($sinh_vien_status[$student_id]) ? $sinh_vien_status[$student_id] : 'absent';
                            $note = isset($_POST['sinh_vien_note'][$student_id]) ? sanitize_text_field($_POST['sinh_vien_note'][$student_id]) : '';
                        ?>
                            <tr>
                                <td><?php echo $stt++; ?></td>
                                <td><?php echo esc_html($student['mssv']); ?></td>
                                <td><?php echo esc_html($student['name']); ?></td>
                                <td>
                                    <label>
                                        <input type="radio" name="sinh_vien_status[<?php echo $student_id; ?>]" value="present" <?php checked($status, 'present'); ?>> Có mặt
                                    </label>
                                    <br>
                                    <label>
                                        <input type="radio" name="sinh_vien_status[<?php echo $student_id; ?>]" value="absent" <?php checked($status, 'absent'); ?>> Vắng mặt
                                    </label>
                                </td>
                                <td>
                                    <input type="text" name="sinh_vien_note[<?php echo $student_id; ?>]" value="<?php echo esc_attr($note); ?>" placeholder="Ghi chú..." class="widefat">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php wp_nonce_field('update_diemdanh', 'diemdanh_nonce'); ?>
                <p><button type="submit" name="update_diemdanh" class="button button-primary">Cập nhật điểm danh</button></p>
            <?php else: ?>
                <div class="diemdanh-error">
                    <p>Không tìm thấy sinh viên nào trong lớp này.</p>
                </div>
            <?php endif; ?>
        </form>
        
        <p>
            <?php if ($lop_id && $mon_hoc_id): ?>
                <a href="<?php echo esc_url(add_query_arg(array('lop' => $lop_id, 'mon_hoc' => $mon_hoc_id), get_post_type_archive_link('diemdanh'))); ?>" class="button">Quay lại danh sách</a>
            <?php else: ?>
                <a href="<?php echo esc_url(get_post_type_archive_link('diemdanh')); ?>" class="button">Quay lại danh sách</a>
            <?php endif; ?>
        </p>
        
    <?php elseif ($is_student): ?>
        <!-- HIỂN THỊ CHO SINH VIÊN -->
        <?php
        // Tìm ID sinh viên dựa trên email
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
        if ($student_query->have_posts()) {
            $student_query->the_post();
            $student_id = get_the_ID();
            $student_name = get_the_title();
            $student_lop_id = get_field('lop', $student_id);
            wp_reset_postdata();
            
            // Kiểm tra xem sinh viên có thuộc lớp này không
            if ($student_lop_id == $lop_id) {
                $status = isset($sinh_vien_status[$student_id]) ? $sinh_vien_status[$student_id] : 'absent';
                $status_text = $status === 'present' ? 'Có mặt' : 'Vắng mặt';
                $status_class = $status === 'present' ? 'status-present' : 'status-absent';
                
                echo '<div class="student-diemdanh-status">';
                echo '<h2>Trạng thái điểm danh</h2>';
                echo '<p>Sinh viên: <strong>' . esc_html($student_name) . '</strong></p>';
                echo '<p>Trạng thái: <span class="' . $status_class . '">' . esc_html($status_text) . '</span></p>';
                echo '</div>';
            } else {
                echo '<div class="diemdanh-error">';
                echo '<p>Bạn không thuộc lớp này.</p>';
                echo '</div>';
            }
        } else {
            echo '<div class="diemdanh-error">';
            echo '<p>Không tìm thấy thông tin sinh viên cho tài khoản này.</p>';
            echo '</div>';
        }
        ?>
    <?php else: ?>
        <div class="diemdanh-error">
            <p>Bạn không có quyền xem điểm danh này.</p>
        </div>
    <?php endif; ?>
</div>

<style>
    .qlsv-container {
        max-width: 1200px !important;
        margin: 0 auto !important;
        padding: 20px !important;
    }
    
    .qlsv-page-title {
        margin-bottom: 30px !important;
        font-size: 28px !important;
        color: #333 !important;
        border-bottom: 2px solid #f0f0f0 !important;
        padding-bottom: 15px !important;
    }
    
    .diemdanh-info {
        background: #f9f9f9 !important;
        padding: 15px 20px !important;
        border-radius: 5px !important;
        margin-bottom: 20px !important;
        border: 1px solid #eee !important;
    }
    
    .diemdanh-table {
        width: 100% !important;
        border-collapse: collapse !important;
        margin-bottom: 20px !important;
    }
    
    .diemdanh-table th, .diemdanh-table td {
        padding: 10px !important;
        border: 1px solid #ddd !important;
        text-align: left !important;
    }
    
    .diemdanh-table th {
        background-color: #f2f2f2 !important;
        font-weight: bold !important;
    }
    
    .diemdanh-error {
        color: #a94442 !important;
        background-color: #f2dede !important;
        padding: 15px !important;
        margin-bottom: 20px !important;
        border: 1px solid #ebccd1 !important;
        border-radius: 4px !important;
    }
    
    .diemdanh-success {
        color: #3c763d !important;
        background-color: #dff0d8 !important;
        padding: 15px !important;
        margin-bottom: 20px !important;
        border: 1px solid #d6e9c6 !important;
        border-radius: 4px !important;
    }
    
    .status-present {
        color: #3c763d !important;
        font-weight: bold !important;
    }
    
    .status-absent {
        color: #a94442 !important;
        font-weight: bold !important;
    }
    
    .button {
        display: inline-block !important;
        padding: 8px 16px !important;
        background: #f0f0f0 !important;
        color: #333 !important;
        text-decoration: none !important;
        border-radius: 4px !important;
        border: 1px solid #ddd !important;
        cursor: pointer !important;
    }
    
    .button-primary {
        background: #0073aa !important;
        border-color: #0073aa !important;
        color: #fff !important;
    }
    
    .student-diemdanh-status {
        background: #f9f9f9 !important;
        padding: 20px !important;
        border-radius: 5px !important;
        margin-bottom: 20px !important;
        border: 1px solid #eee !important;
    }
</style>

<?php get_footer(); ?> 