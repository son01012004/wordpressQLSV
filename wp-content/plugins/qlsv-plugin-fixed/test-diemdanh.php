<?php
/**
 * Trang kiểm tra chức năng điểm danh
 */

// Tải WordPress
define('WP_USE_THEMES', false);
require_once('../../../wp-load.php');

// Kiểm tra người dùng đăng nhập
if (!is_user_logged_in()) {
    wp_die('Bạn cần đăng nhập để xem trang này');
}

// Lấy tham số
$lop_id = isset($_REQUEST['lop']) ? intval($_REQUEST['lop']) : 0;
$mon_hoc_id = isset($_REQUEST['mon_hoc']) ? intval($_REQUEST['mon_hoc']) : 0;

// Hiển thị header
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Kiểm tra chức năng Điểm Danh</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.5;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2, h3 {
            color: #333;
        }
        .section {
            background: #fff;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table th, table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        table th {
            background: #f5f5f5;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
        .button {
            display: inline-block;
            padding: 8px 15px;
            background: #0073aa;
            color: #fff;
            text-decoration: none;
            border-radius: 3px;
            margin-right: 10px;
        }
        .back-button {
            background: #555;
        }
        .class-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .class-card {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            background: #f9f9f9;
        }
        .class-card h3 {
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .subject-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .subject-list li {
            margin-bottom: 8px;
        }
        .subject-list a {
            display: block;
            padding: 8px 10px;
            background: #f0f0f0;
            text-decoration: none;
            color: #333;
            border-radius: 3px;
        }
        .subject-list a:hover {
            background: #e0e0e0;
        }
    </style>
</head>
<body>
    <h1>Kiểm tra chức năng Điểm Danh</h1>
    
    <div class="section">
        <h2>Thông tin</h2>
        <p>Trang này giúp kiểm tra và phát hiện lỗi trong chức năng Điểm Danh.</p>
        <p>
            <a href="index.php" class="button back-button">Quay lại</a>
            <a href="fix-permalinks.php" class="button">Khắc phục Lỗi 404</a>
        </p>
    </div>
    
    <?php if (!$lop_id && !$mon_hoc_id): ?>
    <div class="section">
        <h2>Chọn Lớp và Môn học để kiểm tra</h2>
        
        <div class="class-grid">
            <?php
            // Lấy danh sách lớp
            $lop_query = new WP_Query(array(
                'post_type' => 'lop',
                'posts_per_page' => -1,
                'orderby' => 'title',
                'order' => 'ASC'
            ));
            
            if ($lop_query->have_posts()) {
                while ($lop_query->have_posts()) {
                    $lop_query->the_post();
                    $lop_id = get_the_ID();
                    $lop_name = get_the_title();
                    ?>
                    <div class="class-card">
                        <h3><?php echo esc_html($lop_name); ?></h3>
                        
                        <?php
                        // Lấy danh sách môn học
                        $monhoc_query = new WP_Query(array(
                            'post_type' => 'monhoc',
                            'posts_per_page' => -1,
                            'orderby' => 'title',
                            'order' => 'ASC'
                        ));
                        
                        if ($monhoc_query->have_posts()) {
                            echo '<ul class="subject-list">';
                            while ($monhoc_query->have_posts()) {
                                $monhoc_query->the_post();
                                $monhoc_id = get_the_ID();
                                $monhoc_name = get_the_title();
                                ?>
                                <li>
                                    <a href="<?php echo esc_url(add_query_arg(array('lop' => $lop_id, 'mon_hoc' => $monhoc_id))); ?>">
                                        <?php echo esc_html($monhoc_name); ?>
                                    </a>
                                </li>
                                <?php
                            }
                            echo '</ul>';
                        } else {
                            echo '<p>Không có môn học nào</p>';
                        }
                        wp_reset_postdata();
                        ?>
                    </div>
                    <?php
                }
            } else {
                echo '<p>Không có lớp nào</p>';
            }
            wp_reset_postdata();
            ?>
        </div>
    </div>
    <?php else: ?>
    <div class="section">
        <h2>Kiểm tra Điểm Danh cho Lớp <?php echo get_the_title($lop_id); ?> - <?php echo get_the_title($mon_hoc_id); ?></h2>
        
        <h3>1. Kiểm tra URL</h3>
        <?php
        $archive_url = home_url('/diemdanh/');
        $param_url = add_query_arg(array('lop' => $lop_id, 'mon_hoc' => $mon_hoc_id), $archive_url);
        $pretty_url = home_url('/diemdanh/lop/' . $lop_id . '/mon-hoc/' . $mon_hoc_id . '/');
        
        echo '<p><strong>URL cơ bản:</strong> <a href="' . esc_url($archive_url) . '" target="_blank">' . esc_html($archive_url) . '</a></p>';
        echo '<p><strong>URL với tham số:</strong> <a href="' . esc_url($param_url) . '" target="_blank">' . esc_html($param_url) . '</a></p>';
        echo '<p><strong>URL thân thiện:</strong> <a href="' . esc_url($pretty_url) . '" target="_blank">' . esc_html($pretty_url) . '</a></p>';
        ?>
        
        <h3>2. Kiểm tra Sinh viên</h3>
        <?php
        // Lấy danh sách sinh viên trong lớp
        $student_args = array(
            'post_type' => 'sinhvien',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'lop',
                    'value' => $lop_id,
                    'compare' => '='
                )
            ),
            'orderby' => 'title',
            'order' => 'ASC'
        );
        
        $student_query = new WP_Query($student_args);
        
        if ($student_query->have_posts()) {
            echo '<p class="success">✓ Tìm thấy ' . $student_query->post_count . ' sinh viên trong lớp này</p>';
            
            echo '<table>';
            echo '<tr><th>ID</th><th>Tên sinh viên</th><th>MSSV</th></tr>';
            
            while ($student_query->have_posts()) {
                $student_query->the_post();
                $sv_id = get_the_ID();
                $sv_name = get_the_title();
                $sv_mssv = get_field('ma_sinh_vien', $sv_id);
                
                echo '<tr>';
                echo '<td>' . $sv_id . '</td>';
                echo '<td>' . $sv_name . '</td>';
                echo '<td>' . $sv_mssv . '</td>';
                echo '</tr>';
            }
            
            echo '</table>';
        } else {
            echo '<p class="error">✗ Không tìm thấy sinh viên nào trong lớp này</p>';
        }
        wp_reset_postdata();
        ?>
        
        <h3>3. Kiểm tra điểm danh hiện có</h3>
        <?php
        // Kiểm tra các bản ghi điểm danh hiện có
        $diemdanh_args = array(
            'post_type' => 'diemdanh',
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'lop',
                    'value' => $lop_id,
                    'compare' => '='
                ),
                array(
                    'key' => 'mon_hoc',
                    'value' => $mon_hoc_id,
                    'compare' => '='
                )
            ),
            'orderby' => 'meta_value',
            'meta_key' => 'ngay',
            'order' => 'DESC'
        );
        
        $diemdanh_query = new WP_Query($diemdanh_args);
        
        if ($diemdanh_query->have_posts()) {
            echo '<p class="success">✓ Tìm thấy ' . $diemdanh_query->post_count . ' buổi điểm danh</p>';
            
            echo '<table>';
            echo '<tr><th>ID</th><th>Ngày</th><th>Tiêu đề</th><th>Sinh viên đã điểm danh</th></tr>';
            
            while ($diemdanh_query->have_posts()) {
                $diemdanh_query->the_post();
                $dd_id = get_the_ID();
                $dd_title = get_the_title();
                $ngay = get_field('ngay', $dd_id);
                $ngay_format = $ngay ? date_i18n('d/m/Y', strtotime($ngay)) : 'N/A';
                
                // Đếm số sinh viên đã điểm danh
                $sinh_vien_status = get_post_meta($dd_id, 'sinh_vien_status', true);
                $status_count = is_array($sinh_vien_status) ? count($sinh_vien_status) : 0;
                
                echo '<tr>';
                echo '<td>' . $dd_id . '</td>';
                echo '<td>' . $ngay_format . '</td>';
                echo '<td>' . $dd_title . '</td>';
                echo '<td>' . $status_count . '</td>';
                echo '</tr>';
            }
            
            echo '</table>';
        } else {
            echo '<p class="error">✗ Không tìm thấy buổi điểm danh nào</p>';
            
            // Hiện form tạo mới
            echo '<h4>Tạo buổi điểm danh mới</h4>';
            echo '<form method="post" action="' . admin_url('admin-post.php') . '">';
            echo '<input type="hidden" name="action" value="create_diemdanh">';
            echo '<input type="hidden" name="lop_id" value="' . $lop_id . '">';
            echo '<input type="hidden" name="mon_hoc_id" value="' . $mon_hoc_id . '">';
            echo '<label>Ngày: <input type="date" name="ngay" value="' . date('Y-m-d') . '" required></label><br>';
            echo '<button type="submit" style="margin-top: 10px;">Tạo buổi điểm danh</button>';
            echo '</form>';
        }
        wp_reset_postdata();
        ?>
        
        <h3>4. Kiểm tra Template</h3>
        <?php
        $templates = array(
            'archive-diemdanh.php' => QLSV_PLUGIN_DIR . 'templates/archive-diemdanh.php',
            'single-diemdanh.php' => QLSV_PLUGIN_DIR . 'templates/single-diemdanh.php',
            'page-diemdanh.php' => QLSV_PLUGIN_DIR . 'templates/page-diemdanh.php',
        );
        
        echo '<ul>';
        foreach ($templates as $name => $path) {
            if (file_exists($path)) {
                echo '<li class="success">✓ ' . esc_html($name) . ' - Tìm thấy</li>';
            } else {
                echo '<li class="error">✗ ' . esc_html($name) . ' - Không tìm thấy</li>';
            }
        }
        echo '</ul>';
        ?>
        
        <p>
            <a href="<?php echo esc_url(remove_query_arg(array('lop', 'mon_hoc'))); ?>" class="button back-button">Chọn lớp khác</a>
            <a href="<?php echo esc_url($param_url); ?>" class="button" target="_blank">Mở trang điểm danh</a>
        </p>
    </div>
    <?php endif; ?>
</body>
</html>
<?php
// Xử lý tạo điểm danh nếu cần
add_action('admin_post_create_diemdanh', 'create_diemdanh');
function create_diemdanh() {
    if (!isset($_POST['lop_id']) || !isset($_POST['mon_hoc_id']) || !isset($_POST['ngay'])) {
        wp_die('Thiếu dữ liệu cần thiết');
    }
    
    $lop_id = intval($_POST['lop_id']);
    $mon_hoc_id = intval($_POST['mon_hoc_id']);
    $ngay = sanitize_text_field($_POST['ngay']);
    
    // Tạo bản ghi điểm danh mới
    $diemdanh_id = wp_insert_post(array(
        'post_title' => 'Đang tạo...',
        'post_status' => 'publish',
        'post_type' => 'diemdanh'
    ));
    
    if ($diemdanh_id > 0) {
        // Cập nhật trường ACF
        update_field('lop', $lop_id, $diemdanh_id);
        update_field('mon_hoc', $mon_hoc_id, $diemdanh_id);
        update_field('ngay', $ngay, $diemdanh_id);
        
        // Cập nhật tiêu đề
        $mon_hoc_name = get_the_title($mon_hoc_id);
        $lop_name = get_the_title($lop_id);
        $ngay_format = date_i18n('d/m/Y', strtotime($ngay));
        
        // Cập nhật tiêu đề
        $title = sprintf('Điểm danh %s - %s - %s', $lop_name, $mon_hoc_name, $ngay_format);
        
        wp_update_post(array(
            'ID' => $diemdanh_id,
            'post_title' => $title,
        ));
    }
    
    // Chuyển hướng lại trang test với thông báo thành công
    wp_redirect(add_query_arg(array(
        'lop' => $lop_id, 
        'mon_hoc' => $mon_hoc_id,
        'created' => 1
    ), wp_get_referer()));
    exit;
}
?> 