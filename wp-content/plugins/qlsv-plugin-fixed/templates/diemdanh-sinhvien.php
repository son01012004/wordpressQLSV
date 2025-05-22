<?php
/**
 * Template hiển thị thông tin điểm danh của sinh viên
 *
 * @package QLSV
 */

// Ngăn chặn truy cập trực tiếp
if (!defined('WPINC')) {
    die;
}

// Dữ liệu từ shortcode
$sinh_vien = isset($sinh_vien) ? $sinh_vien : null;
$mon_hoc_list = isset($mon_hoc_list) ? $mon_hoc_list : array();
$diemdanh_stats = isset($diemdanh_stats) ? $diemdanh_stats : array();

// Nếu không có sinh viên
if (!$sinh_vien) {
    echo '<p class="diemdanh-thongbao">' . esc_html__('Không tìm thấy thông tin sinh viên.', 'qlsv') . '</p>';
    return;
}
?>

<div class="diemdanh-sinhvien-container">
    <h2 class="diemdanh-title"><?php esc_html_e('Thống kê điểm danh', 'qlsv'); ?></h2>
    
    <!-- Thông tin sinh viên -->
    <div class="sinh-vien-info">
        <h3><?php echo esc_html($sinh_vien->post_title); ?></h3>
        <?php 
        $ma_sv = get_field('ma_sinh_vien', $sinh_vien->ID);
        $lop_id = get_field('lop', $sinh_vien->ID);
        $lop = $lop_id ? get_the_title($lop_id) : '';
        ?>
        <p>
            <strong><?php esc_html_e('Mã sinh viên:', 'qlsv'); ?></strong> <?php echo esc_html($ma_sv); ?><br>
            <strong><?php esc_html_e('Lớp:', 'qlsv'); ?></strong> <?php echo esc_html($lop); ?>
        </p>
    </div>
    
    <?php if (empty($mon_hoc_list)) : ?>
        <p class="diemdanh-thongbao">
            <?php esc_html_e('Chưa có dữ liệu điểm danh cho sinh viên này.', 'qlsv'); ?>
        </p>
    <?php else : ?>
        <!-- Tổng hợp điểm danh các môn học -->
        <div class="diemdanh-summary-all">
            <h3><?php esc_html_e('Tổng hợp điểm danh', 'qlsv'); ?></h3>
            
            <?php 
            // Tính tổng hợp từ tất cả các môn
            $total_all = 0;
            $co_mat_all = 0;
            $vang_all = 0;
            $di_muon_all = 0;
            $ve_som_all = 0;
            $co_phep_all = 0;
            
            foreach ($diemdanh_stats as $mon_hoc_id => $mon_data) {
                $total_all += $mon_data['stats']['tong_so_buoi'];
                $co_mat_all += $mon_data['stats']['co_mat'];
                $vang_all += $mon_data['stats']['vang'];
                $di_muon_all += $mon_data['stats']['di_muon'];
                $ve_som_all += $mon_data['stats']['ve_som'];
                $co_phep_all += $mon_data['stats']['co_phep'];
            }
            
            // Tính phần trăm
            $percent_present = $total_all > 0 ? round(($co_mat_all / $total_all) * 100) : 0;
            $percent_absent = $total_all > 0 ? round(($vang_all / $total_all) * 100) : 0;
            $percent_late = $total_all > 0 ? round(($di_muon_all / $total_all) * 100) : 0;
            $percent_early = $total_all > 0 ? round(($ve_som_all / $total_all) * 100) : 0;
            $percent_excused = $total_all > 0 ? round(($co_phep_all / $total_all) * 100) : 0;
            ?>
            
            <div class="diemdanh-overview-cards">
                <div class="overview-card total">
                    <div class="card-icon">
                        <i class="dashicons dashicons-calendar-alt"></i>
                    </div>
                    <div class="card-content">
                        <div class="card-value"><?php echo $total_all; ?></div>
                        <div class="card-label"><?php esc_html_e('Tổng số buổi', 'qlsv'); ?></div>
                    </div>
                </div>
                
                <div class="overview-card present">
                    <div class="card-icon">
                        <i class="dashicons dashicons-yes-alt"></i>
                    </div>
                    <div class="card-content">
                        <div class="card-value"><?php echo $co_mat_all; ?></div>
                        <div class="card-label"><?php esc_html_e('Có mặt', 'qlsv'); ?></div>
                    </div>
                </div>
                
                <div class="overview-card absent">
                    <div class="card-icon">
                        <i class="dashicons dashicons-no-alt"></i>
                    </div>
                    <div class="card-content">
                        <div class="card-value"><?php echo $vang_all; ?></div>
                        <div class="card-label"><?php esc_html_e('Vắng mặt', 'qlsv'); ?></div>
                    </div>
                </div>
                
                <div class="overview-card late">
                    <div class="card-icon">
                        <i class="dashicons dashicons-clock"></i>
                    </div>
                    <div class="card-content">
                        <div class="card-value"><?php echo $di_muon_all; ?></div>
                        <div class="card-label"><?php esc_html_e('Đi muộn', 'qlsv'); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="progress-stats">
                <div class="progress-item">
                    <div class="progress-label">
                        <span><?php esc_html_e('Tỷ lệ có mặt', 'qlsv'); ?></span>
                        <span class="progress-percent"><?php echo $percent_present; ?>%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-value present" style="width: <?php echo $percent_present; ?>%"></div>
                    </div>
                </div>
                
                <div class="progress-item">
                    <div class="progress-label">
                        <span><?php esc_html_e('Tỷ lệ vắng', 'qlsv'); ?></span>
                        <span class="progress-percent"><?php echo $percent_absent; ?>%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-value absent" style="width: <?php echo $percent_absent; ?>%"></div>
                    </div>
                </div>
                
                <div class="progress-item">
                    <div class="progress-label">
                        <span><?php esc_html_e('Tỷ lệ đi muộn/về sớm', 'qlsv'); ?></span>
                        <span class="progress-percent"><?php echo $percent_late + $percent_early; ?>%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-value late" style="width: <?php echo $percent_late + $percent_early; ?>%"></div>
                    </div>
                </div>
                
                <div class="progress-item">
                    <div class="progress-label">
                        <span><?php esc_html_e('Tỷ lệ có phép', 'qlsv'); ?></span>
                        <span class="progress-percent"><?php echo $percent_excused; ?>%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-value excused" style="width: <?php echo $percent_excused; ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hiển thị thống kê cho từng môn học -->
        <div class="diemdanh-stats-container">
            <?php foreach ($diemdanh_stats as $mon_hoc_id => $mon_data) : ?>
                <div class="diemdanh-mon-hoc">
                    <h3 class="mon-hoc-title"><?php echo esc_html($mon_data['ten_mon']); ?></h3>
                    
                    <!-- Tóm tắt thống kê -->
                    <div class="diemdanh-summary">
                        <div class="stat-item total">
                            <span class="stat-number"><?php echo $mon_data['stats']['tong_so_buoi']; ?></span>
                            <span class="stat-label"><?php esc_html_e('Tổng buổi', 'qlsv'); ?></span>
                        </div>
                        <div class="stat-item present">
                            <span class="stat-number"><?php echo $mon_data['stats']['co_mat']; ?></span>
                            <span class="stat-label"><?php esc_html_e('Có mặt', 'qlsv'); ?></span>
                        </div>
                        <div class="stat-item absent">
                            <span class="stat-number"><?php echo $mon_data['stats']['vang']; ?></span>
                            <span class="stat-label"><?php esc_html_e('Vắng', 'qlsv'); ?></span>
                        </div>
                        <div class="stat-item late">
                            <span class="stat-number"><?php echo $mon_data['stats']['di_muon']; ?></span>
                            <span class="stat-label"><?php esc_html_e('Đi muộn', 'qlsv'); ?></span>
                        </div>
                        <div class="stat-item early">
                            <span class="stat-number"><?php echo $mon_data['stats']['ve_som']; ?></span>
                            <span class="stat-label"><?php esc_html_e('Về sớm', 'qlsv'); ?></span>
                        </div>
                        <div class="stat-item excused">
                            <span class="stat-number"><?php echo $mon_data['stats']['co_phep']; ?></span>
                            <span class="stat-label"><?php esc_html_e('Có phép', 'qlsv'); ?></span>
                        </div>
                    </div>
                    
                    <!-- Chi tiết điểm danh theo từng buổi -->
                    <div class="diemdanh-details">
                        <h4><?php esc_html_e('Chi tiết từng buổi', 'qlsv'); ?> 
                            <button type="button" class="toggle-details"><?php esc_html_e('Hiển thị', 'qlsv'); ?></button>
                        </h4>
                        
                        <?php if (empty($mon_data['buoi_list'])) : ?>
                            <p><?php esc_html_e('Không có dữ liệu chi tiết.', 'qlsv'); ?></p>
                        <?php else : ?>
                            <div class="details-content" style="display: none;">
                                <table class="diemdanh-table">
                                    <thead>
                                        <tr>
                                            <th><?php esc_html_e('Buổi', 'qlsv'); ?></th>
                                            <th><?php esc_html_e('Ngày', 'qlsv'); ?></th>
                                            <th><?php esc_html_e('Trạng thái', 'qlsv'); ?></th>
                                            <th><?php esc_html_e('Ghi chú', 'qlsv'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($mon_data['buoi_list'] as $buoi) : 
                                            // Hiển thị trạng thái dễ đọc
                                            $trang_thai_text = '';
                                            $trang_thai_class = '';
                                            
                                            switch ($buoi['trang_thai']) {
                                                case 'co_mat':
                                                    $trang_thai_text = __('Có mặt', 'qlsv');
                                                    $trang_thai_class = 'status-present';
                                                    break;
                                                case 'vang':
                                                    $trang_thai_text = __('Vắng', 'qlsv');
                                                    $trang_thai_class = 'status-absent';
                                                    break;
                                                case 'di_muon':
                                                    $trang_thai_text = __('Đi muộn', 'qlsv');
                                                    $trang_thai_class = 'status-late';
                                                    break;
                                                case 've_som':
                                                    $trang_thai_text = __('Về sớm', 'qlsv');
                                                    $trang_thai_class = 'status-early';
                                                    break;
                                                case 'co_phep':
                                                    $trang_thai_text = __('Có phép', 'qlsv');
                                                    $trang_thai_class = 'status-excused';
                                                    break;
                                                default:
                                                    $trang_thai_text = __('Không xác định', 'qlsv');
                                                    $trang_thai_class = '';
                                                    break;
                                            }
                                        ?>
                                        <tr class="<?php echo esc_attr($trang_thai_class); ?>">
                                            <td><?php echo esc_html($buoi['buoi']); ?></td>
                                            <td><?php echo esc_html($buoi['ngay_format']); ?></td>
                                            <td><span class="trang-thai-label"><?php echo esc_html($trang_thai_text); ?></span></td>
                                            <td><?php echo esc_html($buoi['ghi_chu']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    /* CSS cho container chính */
    .diemdanh-sinhvien-container {
        margin-bottom: 30px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    }
    .diemdanh-title {
        margin-bottom: 20px;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }
    
    /* CSS cho thông tin sinh viên */
    .sinh-vien-info {
        background: #f9f9f9;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .sinh-vien-info h3 {
        margin-top: 0;
        margin-bottom: 10px;
        color: #333;
    }
    
    /* CSS cho tổng hợp điểm danh */
    .diemdanh-summary-all {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        margin-bottom: 30px;
        padding: 20px;
    }
    .diemdanh-summary-all h3 {
        margin-top: 0;
        margin-bottom: 20px;
        color: #333;
    }
    
    /* Cards tổng quan */
    .diemdanh-overview-cards {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 20px;
    }
    .overview-card {
        flex: 1 1 calc(25% - 15px);
        min-width: 200px;
        display: flex;
        align-items: center;
        padding: 15px;
        border-radius: 8px;
        color: #fff;
    }
    .overview-card.total {
        background-color: #3498db;
    }
    .overview-card.present {
        background-color: #2ecc71;
    }
    .overview-card.absent {
        background-color: #e74c3c;
    }
    .overview-card.late {
        background-color: #f39c12;
    }
    .card-icon {
        font-size: 36px;
        margin-right: 15px;
    }
    .card-content {
        flex-grow: 1;
    }
    .card-value {
        font-size: 28px;
        font-weight: bold;
        line-height: 1;
    }
    .card-label {
        font-size: 14px;
        opacity: 0.9;
        margin-top: 5px;
    }
    
    /* Progress bars */
    .progress-stats {
        margin-top: 30px;
    }
    .progress-item {
        margin-bottom: 15px;
    }
    .progress-label {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }
    .progress-percent {
        font-weight: bold;
    }
    .progress-bar {
        height: 8px;
        background-color: #f0f0f0;
        border-radius: 4px;
        overflow: hidden;
    }
    .progress-value {
        height: 100%;
        border-radius: 4px;
    }
    .progress-value.present {
        background-color: #2ecc71;
    }
    .progress-value.absent {
        background-color: #e74c3c;
    }
    .progress-value.late {
        background-color: #f39c12;
    }
    .progress-value.excused {
        background-color: #1abc9c;
    }
    
    /* CSS cho thống kê môn học */
    .diemdanh-mon-hoc {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        margin-bottom: 20px;
        padding: 20px;
    }
    .mon-hoc-title {
        margin-top: 0;
        margin-bottom: 15px;
        position: relative;
    }
    .mon-hoc-title::after {
        content: '';
        display: block;
        width: 50px;
        height: 3px;
        background-color: #3498db;
        margin-top: 8px;
    }
    
    /* CSS cho tóm tắt thống kê */
    .diemdanh-summary {
        display: flex;
        flex-wrap: wrap;
        margin-bottom: 20px;
        gap: 10px;
    }
    .stat-item {
        flex: 1 1 calc(16.66% - 10px);
        min-width: 100px;
        border-radius: 8px;
        padding: 15px;
        color: white;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        transition: transform 0.2s;
    }
    .stat-item:hover {
        transform: translateY(-5px);
    }
    .stat-number {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .stat-label {
        font-size: 14px;
    }
    .stat-item.total { background-color: #3498db; }
    .stat-item.present { background-color: #2ecc71; }
    .stat-item.absent { background-color: #e74c3c; }
    .stat-item.late { background-color: #f39c12; }
    .stat-item.early { background-color: #9b59b6; }
    .stat-item.excused { background-color: #1abc9c; }
    
    /* CSS cho chi tiết điểm danh */
    .diemdanh-details h4 {
        margin-top: 20px;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .toggle-details {
        background: #eee;
        border: none;
        padding: 5px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 13px;
        transition: all 0.2s;
    }
    .toggle-details:hover {
        background: #ddd;
    }
    
    /* CSS cho bảng chi tiết điểm danh */
    .diemdanh-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #eee;
    }
    .diemdanh-table th, 
    .diemdanh-table td {
        padding: 10px 12px;
        border: 1px solid #eee;
        text-align: left;
    }
    .diemdanh-table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    .diemdanh-table tr.status-present {
        background-color: rgba(46, 204, 113, 0.1);
    }
    .diemdanh-table tr.status-absent {
        background-color: rgba(231, 76, 60, 0.1);
    }
    .diemdanh-table tr.status-late,
    .diemdanh-table tr.status-early {
        background-color: rgba(243, 156, 18, 0.1);
    }
    .diemdanh-table tr.status-excused {
        background-color: rgba(26, 188, 156, 0.1);
    }
    
    /* CSS cho trạng thái điểm danh */
    .trang-thai-label {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
        white-space: nowrap;
    }
    tr.status-present .trang-thai-label {
        background-color: rgba(46, 204, 113, 0.2);
        color: #27ae60;
    }
    tr.status-absent .trang-thai-label {
        background-color: rgba(231, 76, 60, 0.2);
        color: #c0392b;
    }
    tr.status-late .trang-thai-label,
    tr.status-early .trang-thai-label {
        background-color: rgba(243, 156, 18, 0.2);
        color: #d35400;
    }
    tr.status-excused .trang-thai-label {
        background-color: rgba(26, 188, 156, 0.2);
        color: #16a085;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .diemdanh-summary {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
        }
        .overview-card {
            flex: 1 1 calc(50% - 10px);
        }
    }
    
    @media (max-width: 480px) {
        .diemdanh-summary {
            grid-template-columns: 1fr;
        }
        .overview-card {
            flex: 1 1 100%;
        }
    }
</style>

<script>
jQuery(document).ready(function($) {
    // Toggle chi tiết điểm danh
    $('.toggle-details').on('click', function() {
        var detailsContent = $(this).closest('.diemdanh-details').find('.details-content');
        
        if (detailsContent.is(':visible')) {
            $(this).text('Hiển thị');
            detailsContent.slideUp(300);
        } else {
            $(this).text('Ẩn');
            detailsContent.slideDown(300);
        }
    });
});
</script> 