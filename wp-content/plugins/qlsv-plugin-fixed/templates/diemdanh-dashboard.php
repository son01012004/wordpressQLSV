<?php
/**
 * Template hiển thị trang tổng hợp điểm danh
 *
 * @package QLSV
 */

// Ngăn chặn truy cập trực tiếp
if (!defined('WPINC')) {
    die;
}

// Kiểm tra role người dùng
$user_id = get_current_user_id();
$user = get_userdata($user_id);

$is_teacher = false;
$is_student = false;
$is_admin = false;
$student_id = 0;

if ($user) {
    $is_teacher = in_array('giaovien', $user->roles) || in_array('administrator', $user->roles);
    $is_admin = user_can($user_id, 'manage_options');
    
    // Kiểm tra sinh viên
    if (in_array('student', $user->roles)) {
        $is_student = true;
    } else {
        // Tìm sinh viên có email trùng với email người dùng
        $args = array(
            'post_type' => 'sinhvien',
            'posts_per_page' => 1,
            'meta_query' => array(
                array(
                    'key' => 'email',
                    'value' => $user->user_email,
                    'compare' => '='
                )
            )
        );
        
        $student_query = new WP_Query($args);
        
        if ($student_query->have_posts()) {
            $is_student = true;
            $student_query->the_post();
            $student_id = get_the_ID();
            wp_reset_postdata();
        }
    }
}

// Xác định tab hiện tại
$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : ($is_student ? 'view' : 'form');

// Lấy danh sách lớp giảng dạy nếu là giáo viên
$teaching_classes = array();
if ($is_teacher) {
    $args = array(
        'post_type' => 'monhoc',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'giao_vien',
                'value' => $user_id,
                'compare' => '='
            )
        )
    );
    
    $teaching_query = new WP_Query($args);
    
    if ($teaching_query->have_posts()) {
        while ($teaching_query->have_posts()) {
            $teaching_query->the_post();
            $teaching_classes[] = array(
                'id' => get_the_ID(),
                'title' => get_the_title()
            );
        }
        wp_reset_postdata();
    }
}
?>

<div class="diemdanh-dashboard-container">
    <h1 class="diemdanh-dashboard-title"><?php esc_html_e('Quản lý điểm danh', 'qlsv'); ?></h1>
    
    <?php if (!is_user_logged_in()) : ?>
        <div class="diemdanh-error-message">
            <p><?php esc_html_e('Bạn cần đăng nhập để sử dụng chức năng này.', 'qlsv'); ?></p>
            <p><a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="diemdanh-btn"><?php esc_html_e('Đăng nhập', 'qlsv'); ?></a></p>
        </div>
    <?php else : ?>
        <!-- Tabs điều hướng -->
        <div class="diemdanh-tabs">
            <ul class="diemdanh-tab-links">
                <?php if ($is_teacher || $is_admin) : ?>
                    <li class="<?php echo $current_tab === 'form' ? 'active' : ''; ?>">
                        <a href="?tab=form"><?php esc_html_e('Điểm danh', 'qlsv'); ?></a>
                    </li>
                <?php endif; ?>
                
                <li class="<?php echo $current_tab === 'view' ? 'active' : ''; ?>">
                    <a href="?tab=view"><?php esc_html_e('Xem điểm danh', 'qlsv'); ?></a>
                </li>
                
                <?php if ($is_teacher || $is_admin) : ?>
                    <li class="<?php echo $current_tab === 'stats' ? 'active' : ''; ?>">
                        <a href="?tab=stats"><?php esc_html_e('Thống kê', 'qlsv'); ?></a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
        
        <!-- Nội dung tabs -->
        <div class="diemdanh-tab-content">
            <?php if ($current_tab === 'form' && ($is_teacher || $is_admin)) : ?>
                <!-- Tab điểm danh -->
                <div class="diemdanh-tab-pane active">
                    <div class="tab-header">
                        <h2><?php esc_html_e('Điểm danh sinh viên', 'qlsv'); ?></h2>
                        <?php if (!empty($teaching_classes)) : ?>
                            <div class="teaching-classes-info">
                                <p><?php esc_html_e('Các môn học giảng dạy:', 'qlsv'); ?></p>
                                <ul>
                                    <?php foreach ($teaching_classes as $class) : ?>
                                        <li><?php echo esc_html($class['title']); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php echo do_shortcode('[qlsv_diemdanh_form]'); ?>
                </div>
            <?php elseif ($current_tab === 'view') : ?>
                <!-- Tab xem điểm danh -->
                <div class="diemdanh-tab-pane active">
                    <div class="tab-header">
                        <h2><?php esc_html_e('Xem thông tin điểm danh', 'qlsv'); ?></h2>
                    </div>
                    
                    <?php if ($is_student) : ?>
                        <?php echo do_shortcode('[qlsv_diemdanh_sinhvien sinhvien_id="' . $student_id . '"]'); ?>
                    <?php else : ?>
                        <?php 
                        $lop_id = isset($_GET['lop']) ? intval($_GET['lop']) : 0;
                        $monhoc_id = isset($_GET['monhoc']) ? intval($_GET['monhoc']) : 0;
                        echo do_shortcode('[qlsv_diemdanh lop_id="' . $lop_id . '" monhoc_id="' . $monhoc_id . '"]'); 
                        ?>
                    <?php endif; ?>
                </div>
            <?php elseif ($current_tab === 'stats' && ($is_teacher || $is_admin)) : ?>
                <!-- Tab thống kê -->
                <div class="diemdanh-tab-pane active">
                    <div class="tab-header">
                        <h2><?php esc_html_e('Thống kê điểm danh', 'qlsv'); ?></h2>
                    </div>
                    
                    <div class="diemdanh-stats">
                        <?php 
                        // Lấy thống kê tổng quát
                        $args = array(
                            'post_type' => 'diemdanh',
                            'posts_per_page' => -1
                        );
                        
                        // Nếu là giáo viên, chỉ hiển thị các buổi điểm danh của giáo viên đó
                        if ($is_teacher && !$is_admin) {
                            $args['meta_query'] = array(
                                array(
                                    'key' => 'giang_vien',
                                    'value' => $user_id,
                                    'compare' => '='
                                )
                            );
                        }
                        
                        $diemdanh_query = new WP_Query($args);
                        $total_sessions = $diemdanh_query->post_count;
                        
                        // Đếm tổng số sinh viên và phân loại điểm danh
                        $total_students = 0;
                        $present_count = 0;
                        $absent_count = 0;
                        $late_count = 0;
                        $early_leave_count = 0;
                        $excused_count = 0;
                        
                        if ($diemdanh_query->have_posts()) {
                            while ($diemdanh_query->have_posts()) {
                                $diemdanh_query->the_post();
                                $post_id = get_the_ID();
                                
                                $sinh_vien_dd = get_field('sinh_vien_dd', $post_id);
                                
                                if ($sinh_vien_dd && is_array($sinh_vien_dd)) {
                                    $total_students += count($sinh_vien_dd);
                                    
                                    foreach ($sinh_vien_dd as $sv) {
                                        switch ($sv['trang_thai']) {
                                            case 'co_mat':
                                                $present_count++;
                                                break;
                                            case 'vang':
                                                $absent_count++;
                                                break;
                                            case 'di_muon':
                                                $late_count++;
                                                break;
                                            case 've_som':
                                                $early_leave_count++;
                                                break;
                                            case 'co_phep':
                                                $excused_count++;
                                                break;
                                        }
                                    }
                                }
                            }
                            wp_reset_postdata();
                        }
                        
                        // Tính tỷ lệ
                        $present_rate = $total_students > 0 ? round(($present_count / $total_students) * 100, 1) : 0;
                        $absent_rate = $total_students > 0 ? round(($absent_count / $total_students) * 100, 1) : 0;
                        $late_rate = $total_students > 0 ? round(($late_count / $total_students) * 100, 1) : 0;
                        $early_leave_rate = $total_students > 0 ? round(($early_leave_count / $total_students) * 100, 1) : 0;
                        $excused_rate = $total_students > 0 ? round(($excused_count / $total_students) * 100, 1) : 0;
                        
                        // Lấy danh sách lớp có tỷ lệ vắng cao nhất
                        $all_classes = get_posts(array(
                            'post_type' => 'lop',
                            'posts_per_page' => -1
                        ));
                        
                        $class_stats = array();
                        
                        foreach ($all_classes as $class) {
                            $args = array(
                                'post_type' => 'diemdanh',
                                'posts_per_page' => -1,
                                'meta_query' => array(
                                    array(
                                        'key' => 'lop',
                                        'value' => $class->ID,
                                        'compare' => '='
                                    )
                                )
                            );
                            
                            $class_query = new WP_Query($args);
                            $class_total = 0;
                            $class_absent = 0;
                            
                            if ($class_query->have_posts()) {
                                while ($class_query->have_posts()) {
                                    $class_query->the_post();
                                    $post_id = get_the_ID();
                                    
                                    $sinh_vien_dd = get_field('sinh_vien_dd', $post_id);
                                    
                                    if ($sinh_vien_dd && is_array($sinh_vien_dd)) {
                                        $class_total += count($sinh_vien_dd);
                                        
                                        foreach ($sinh_vien_dd as $sv) {
                                            if ($sv['trang_thai'] === 'vang') {
                                                $class_absent++;
                                            }
                                        }
                                    }
                                }
                                wp_reset_postdata();
                                
                                if ($class_total > 0) {
                                    $class_stats[$class->ID] = array(
                                        'name' => $class->post_title,
                                        'absent_rate' => round(($class_absent / $class_total) * 100, 1)
                                    );
                                }
                            }
                        }
                        
                        // Sắp xếp các lớp theo tỷ lệ vắng giảm dần
                        if (!empty($class_stats)) {
                            uasort($class_stats, function($a, $b) {
                                return $b['absent_rate'] <=> $a['absent_rate'];
                            });
                        }
                        ?>
                        
                        <div class="stats-overview">
                            <div class="stats-section">
                                <h3><?php esc_html_e('Tổng quan', 'qlsv'); ?></h3>
                                
                                <div class="stats-cards">
                                    <div class="stat-card">
                                        <div class="stat-card-value"><?php echo $total_sessions; ?></div>
                                        <div class="stat-card-label"><?php esc_html_e('Buổi điểm danh', 'qlsv'); ?></div>
                                    </div>
                                    
                                    <div class="stat-card">
                                        <div class="stat-card-value"><?php echo $total_students; ?></div>
                                        <div class="stat-card-label"><?php esc_html_e('Lượt sinh viên', 'qlsv'); ?></div>
                                    </div>
                                    
                                    <div class="stat-card">
                                        <div class="stat-card-value"><?php echo $present_rate; ?>%</div>
                                        <div class="stat-card-label"><?php esc_html_e('Tỷ lệ có mặt', 'qlsv'); ?></div>
                                    </div>
                                    
                                    <div class="stat-card">
                                        <div class="stat-card-value"><?php echo $absent_rate; ?>%</div>
                                        <div class="stat-card-label"><?php esc_html_e('Tỷ lệ vắng mặt', 'qlsv'); ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="stats-section">
                                <h3><?php esc_html_e('Chi tiết điểm danh', 'qlsv'); ?></h3>
                                
                                <div class="stats-chart">
                                    <canvas id="attendance-chart"></canvas>
                                </div>
                            </div>
                            
                            <?php if (!empty($class_stats)) : ?>
                                <div class="stats-section">
                                    <h3><?php esc_html_e('Lớp có tỷ lệ vắng cao nhất', 'qlsv'); ?></h3>
                                    
                                    <div class="class-stats">
                                        <table class="diemdanh-table stats-table">
                                            <thead>
                                                <tr>
                                                    <th><?php esc_html_e('Lớp', 'qlsv'); ?></th>
                                                    <th><?php esc_html_e('Tỷ lệ vắng', 'qlsv'); ?></th>
                                                    <th><?php esc_html_e('Xem chi tiết', 'qlsv'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $count = 0;
                                                foreach ($class_stats as $id => $class) : 
                                                    if (++$count > 5) break; // Chỉ hiển thị 5 lớp đầu tiên
                                                ?>
                                                    <tr>
                                                        <td><?php echo esc_html($class['name']); ?></td>
                                                        <td>
                                                            <div class="progress-mini">
                                                                <div class="progress-mini-value" style="width: <?php echo $class['absent_rate']; ?>%"></div>
                                                            </div>
                                                            <span class="progress-mini-text"><?php echo $class['absent_rate']; ?>%</span>
                                                        </td>
                                                        <td>
                                                            <a href="?tab=view&lop=<?php echo $id; ?>" class="view-detail-btn">
                                                                <?php esc_html_e('Xem', 'qlsv'); ?>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Thêm Chart.js -->
                        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var ctx = document.getElementById('attendance-chart').getContext('2d');
                            
                            var myChart = new Chart(ctx, {
                                type: 'doughnut',
                                data: {
                                    labels: [
                                        '<?php esc_html_e('Có mặt', 'qlsv'); ?>',
                                        '<?php esc_html_e('Vắng', 'qlsv'); ?>',
                                        '<?php esc_html_e('Đi muộn', 'qlsv'); ?>',
                                        '<?php esc_html_e('Về sớm', 'qlsv'); ?>',
                                        '<?php esc_html_e('Có phép', 'qlsv'); ?>'
                                    ],
                                    datasets: [{
                                        data: [
                                            <?php echo $present_count; ?>,
                                            <?php echo $absent_count; ?>,
                                            <?php echo $late_count; ?>,
                                            <?php echo $early_leave_count; ?>,
                                            <?php echo $excused_count; ?>
                                        ],
                                        backgroundColor: [
                                            '#2ecc71',
                                            '#e74c3c',
                                            '#f39c12',
                                            '#9b59b6',
                                            '#1abc9c'
                                        ],
                                        borderWidth: 0
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    plugins: {
                                        legend: {
                                            position: 'right',
                                        },
                                        tooltip: {
                                            callbacks: {
                                                label: function(context) {
                                                    var label = context.label || '';
                                                    var value = context.raw || 0;
                                                    var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                    var percentage = Math.round((value / total) * 100) + '%';
                                                    return label + ': ' + value + ' (' + percentage + ')';
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                        });
                        </script>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    /* CSS cho container chính */
    .diemdanh-dashboard-container {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        margin-bottom: 40px;
    }
    .diemdanh-dashboard-title {
        margin-bottom: 20px;
        border-bottom: 2px solid #3498db;
        padding-bottom: 10px;
        color: #2c3e50;
    }
    
    /* CSS cho tabs */
    .diemdanh-tabs {
        margin-bottom: 30px;
    }
    .diemdanh-tab-links {
        display: flex;
        list-style: none;
        padding: 0;
        margin: 0;
        border-bottom: 1px solid #ddd;
    }
    .diemdanh-tab-links li {
        margin-right: 5px;
        margin-bottom: -1px;
    }
    .diemdanh-tab-links a {
        display: block;
        padding: 10px 20px;
        text-decoration: none;
        color: #555;
        border: 1px solid transparent;
        border-radius: 4px 4px 0 0;
        font-weight: 500;
        transition: all 0.2s;
    }
    .diemdanh-tab-links li:not(.active) a:hover {
        background-color: #f8f9fa;
        border-color: #eee #eee #ddd;
    }
    .diemdanh-tab-links li.active a {
        color: #3498db;
        border-color: #ddd #ddd #fff;
        background-color: #fff;
    }
    
    /* CSS cho nội dung tab */
    .diemdanh-tab-content {
        background-color: #fff;
        border-radius: 0 4px 4px 4px;
    }
    .diemdanh-tab-pane {
        display: none;
    }
    .diemdanh-tab-pane.active {
        display: block;
    }
    
    /* CSS cho headers */
    .tab-header {
        margin-bottom: 20px;
    }
    .tab-header h2 {
        margin-top: 0;
        color: #2c3e50;
    }
    
    /* CSS cho thông báo lỗi */
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
        color: white;
        padding: 8px 16px;
        text-decoration: none;
        border-radius: 4px;
        font-weight: 500;
        margin-top: 10px;
    }
    .diemdanh-btn:hover {
        background-color: #2980b9;
    }
    
    /* CSS cho thông tin lớp giảng dạy */
    .teaching-classes-info {
        background-color: #f8f9fa;
        border-left: 4px solid #3498db;
        padding: 15px;
        margin-bottom: 20px;
    }
    .teaching-classes-info p {
        margin-top: 0;
        font-weight: 600;
        color: #333;
    }
    .teaching-classes-info ul {
        margin-bottom: 0;
        padding-left: 20px;
    }
    
    /* CSS cho thống kê */
    .stats-overview {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }
    .stats-section {
        background-color: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .stats-section h3 {
        margin-top: 0;
        margin-bottom: 20px;
        color: #2c3e50;
    }
    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 15px;
    }
    .stat-card {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .stat-card-value {
        font-size: 24px;
        font-weight: bold;
        color: #3498db;
        margin-bottom: 5px;
    }
    .stat-card-label {
        font-size: 14px;
        color: #7f8c8d;
    }
    
    /* CSS cho Chart */
    .stats-chart {
        padding: 10px;
        height: 300px;
    }
    
    /* CSS cho bảng thống kê */
    .stats-table {
        width: 100%;
        border-collapse: collapse;
    }
    .stats-table th,
    .stats-table td {
        padding: 10px;
        text-align: left;
        border: 1px solid #eee;
    }
    .stats-table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    
    /* CSS cho thanh tiến trình nhỏ */
    .progress-mini {
        height: 8px;
        background-color: #f0f0f0;
        border-radius: 4px;
        overflow: hidden;
        width: 80%;
        display: inline-block;
        vertical-align: middle;
    }
    .progress-mini-value {
        height: 100%;
        background-color: #e74c3c;
        border-radius: 4px;
    }
    .progress-mini-text {
        display: inline-block;
        margin-left: 10px;
        font-weight: bold;
        color: #e74c3c;
    }
    .view-detail-btn {
        display: inline-block;
        background-color: #3498db;
        color: white;
        padding: 3px 10px;
        text-decoration: none;
        border-radius: 4px;
        font-size: 12px;
    }
    .view-detail-btn:hover {
        background-color: #2980b9;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .stats-overview {
            grid-template-columns: 1fr;
        }
    }
</style> 