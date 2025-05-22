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
                        <h4><?php esc_html_e('Chi tiết từng buổi', 'qlsv'); ?></h4>
                        
                        <?php if (empty($mon_data['buoi_list'])) : ?>
                            <p><?php esc_html_e('Không có dữ liệu chi tiết.', 'qlsv'); ?></p>
                        <?php else : ?>
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
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 20px;
    }
    .sinh-vien-info h3 {
        margin-top: 0;
        margin-bottom: 10px;
    }
    
    /* CSS cho thống kê môn học */
    .diemdanh-mon-hoc {
        background: #fff;
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        padding: 15px;
    }
    .mon-hoc-title {
        margin-top: 0;
        margin-bottom: 15px;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
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
        min-width: 120px;
        border-radius: 5px;
        padding: 12px;
        color: white;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
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
    
    /* CSS cho bảng chi tiết điểm danh */
    .diemdanh-details h4 {
        margin-top: 0;
        margin-bottom: 15px;
    }
    .diemdanh-table {
        width: 100%;
        border-collapse: collapse;
    }
    .diemdanh-table th, 
    .diemdanh-table td {
        padding: 8px 10px;
        border: 1px solid #ddd;
        text-align: left;
    }
    .diemdanh-table th {
        background-color: #f2f2f2;
        font-weight: bold;
    }
    .diemdanh-table tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    
    /* CSS cho trạng thái điểm danh */
    .trang-thai-label {
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: bold;
        white-space: nowrap;
    }
    tr.status-present .trang-thai-label {
        background-color: #e8f8f0;
        color: #27ae60;
    }
    tr.status-absent .trang-thai-label {
        background-color: #fdedeb;
        color: #c0392b;
    }
    tr.status-late .trang-thai-label {
        background-color: #fef6e7;
        color: #d35400;
    }
    tr.status-early .trang-thai-label {
        background-color: #f4ecf7;
        color: #8e44ad;
    }
    tr.status-excused .trang-thai-label {
        background-color: #e8f6f3;
        color: #16a085;
    }
    
    /* CSS cho thông báo */
    .diemdanh-thongbao {
        padding: 15px;
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
        border-radius: 4px;
        margin-bottom: 20px;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .diemdanh-summary {
            gap: 5px;
        }
        .stat-item {
            flex: 1 1 calc(33.33% - 5px);
            min-width: 100px;
            margin-bottom: 5px;
        }
        .diemdanh-table {
            font-size: 14px;
        }
    }
</style> 