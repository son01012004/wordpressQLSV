<?php
/**
 * Template hiển thị bảng điểm
 *
 * @package QLSV
 */

// Ngăn chặn truy cập trực tiếp
if (!defined('WPINC')) {
    die;
}

// Nhận các tham số từ shortcode
$args = isset($args) ? $args : array();
$diem_list = isset($diem_list) ? $diem_list : array();

// Xác định điều kiện tìm kiếm để hiển thị thông báo
$has_search_conditions = false;
$search_conditions = array();

if (!empty($_GET['sinhvien']) && $_GET['sinhvien'] > 0) {
    $has_search_conditions = true;
    $student_name = get_the_title(intval($_GET['sinhvien']));
    $search_conditions[] = sprintf(__('Sinh viên: %s', 'qlsv'), $student_name);
}

if (!empty($_GET['monhoc']) && $_GET['monhoc'] > 0) {
    $has_search_conditions = true;
    $course_name = get_the_title(intval($_GET['monhoc']));
    $search_conditions[] = sprintf(__('Môn học: %s', 'qlsv'), $course_name);
}

if (!empty($_GET['lop']) && $_GET['lop'] > 0) {
    $has_search_conditions = true;
    $class_name = get_the_title(intval($_GET['lop']));
    $search_conditions[] = sprintf(__('Lớp: %s', 'qlsv'), $class_name);
}

?>

<div class="bang-diem-container">
    <?php if (empty($diem_list->posts)) : ?>
        <div class="empty-results">
            <i class="dashicons dashicons-clipboard"></i>
            <?php if ($has_search_conditions) : ?>
                <p><?php esc_html_e('Không tìm thấy kết quả nào cho điều kiện tìm kiếm:', 'qlsv'); ?></p>
                <ul class="search-conditions">
                    <?php foreach ($search_conditions as $condition) : ?>
                        <li><?php echo esc_html($condition); ?></li>
                    <?php endforeach; ?>
                </ul>
                <p><a href="http://localhost/wordpressQLSV/diem/" class="button reset-search-btn"><?php esc_html_e('Đặt lại tìm kiếm', 'qlsv'); ?></a></p>
            <?php else : ?>
                <p><?php esc_html_e('Không có dữ liệu điểm nào.', 'qlsv'); ?></p>
            <?php endif; ?>
        </div>
    <?php else : ?>
        <?php if ($has_search_conditions) : ?>
            <div class="search-result-info">
                <h3><?php esc_html_e('Kết quả tìm kiếm cho:', 'qlsv'); ?></h3>
                <ul class="search-conditions">
                    <?php foreach ($search_conditions as $condition) : ?>
                        <li><?php echo esc_html($condition); ?></li>
                    <?php endforeach; ?>
                </ul>
                <p><a href="http://localhost/wordpressQLSV/diem/" class="button reset-search-btn"><?php esc_html_e('Đặt lại tìm kiếm', 'qlsv'); ?></a></p>
            </div>
        <?php endif; ?>

        <div class="academic-stats">
            <?php
            // Tính toán thống kê
            $unique_subjects = array(); // Mảng lưu ID môn học duy nhất
            $total_score = 0;
            $passed = 0;
            $failed = 0;
            $score_categories = array(
                'gioi' => 0,
                'kha' => 0,
                'trungbinh' => 0,
                'yeu' => 0
            );
            
            foreach ($diem_list->posts as $diem_post) {
                $mon_hoc_id = get_field('mon_hoc', $diem_post->ID);
                // Đếm môn học duy nhất
                if (!in_array($mon_hoc_id, $unique_subjects) && $mon_hoc_id) {
                    $unique_subjects[] = $mon_hoc_id;
                }
                
                $diem1 = get_field('diem_thanh_phan_1_', $diem_post->ID);
                $diem2 = get_field('diem_thanh_phan_2_', $diem_post->ID);
                $cuoiki = get_field('diem_cuoi_ki_', $diem_post->ID);
                
                if (is_numeric($diem1) && is_numeric($diem2) && is_numeric($cuoiki)) {
                    $tb = round(($diem1 * 0.2 + $diem2 * 0.2 + $cuoiki * 0.6), 2);
                    $total_score += $tb;
                    
                    if ($tb >= 5) {
                        $passed++;
                    } else {
                        $failed++;
                    }
                    
                    if ($tb >= 8.5) $score_categories['gioi']++;
                    elseif ($tb >= 7) $score_categories['kha']++;
                    elseif ($tb >= 5.5) $score_categories['trungbinh']++;
                    else $score_categories['yeu']++;
                }
            }
            
            $total_subjects = count($unique_subjects);
            $avg_score = $total_subjects > 0 ? round($total_score / $total_subjects, 2) : 0;
            ?>
            
            <div class="stats-summary">
                <div class="stat-box">
                    <div class="stat-value"><?php echo $total_subjects; ?></div>
                    <div class="stat-label"><?php esc_html_e('Tổng môn học', 'qlsv'); ?></div>
                </div>
                <div class="stat-box">
                    <div class="stat-value"><?php echo $avg_score; ?></div>
                    <div class="stat-label"><?php esc_html_e('Điểm trung bình', 'qlsv'); ?></div>
                </div>
                <div class="stat-box passed">
                    <div class="stat-value"><?php echo $passed; ?></div>
                    <div class="stat-label"><?php esc_html_e('Đạt', 'qlsv'); ?></div>
                </div>
                <div class="stat-box failed">
                    <div class="stat-value"><?php echo $failed; ?></div>
                    <div class="stat-label"><?php esc_html_e('Chưa đạt', 'qlsv'); ?></div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="bang-diem-table">
                <thead>
                    <tr>
                        <th class="column-stt"><?php esc_html_e('STT', 'qlsv'); ?></th>
                        <th class="column-sinhvien"><?php esc_html_e('Sinh viên', 'qlsv'); ?></th>
                        <th class="column-monhoc"><?php esc_html_e('Môn học', 'qlsv'); ?></th>
                        <th class="column-lop"><?php esc_html_e('Lớp', 'qlsv'); ?></th>
                        <th class="column-diem"><?php esc_html_e('Điểm TP1', 'qlsv'); ?></th>
                        <th class="column-diem"><?php esc_html_e('Điểm TP2', 'qlsv'); ?></th>
                        <th class="column-diem"><?php esc_html_e('Điểm cuối kỳ', 'qlsv'); ?></th>
                        <th class="column-diemtb"><?php esc_html_e('Điểm TB', 'qlsv'); ?></th>
                        <th class="column-xeploai"><?php esc_html_e('Xếp loại', 'qlsv'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $stt = 1;
                    while ($diem_list->have_posts()) : $diem_list->the_post();
                        $post_id = get_the_ID();
                        
                        // Lấy dữ liệu
                        $sinh_vien_id = get_field('sinh_vien', $post_id);
                        $mon_hoc_id = get_field('mon_hoc', $post_id);
                        $lop_id = get_field('lop', $post_id);
                        $diem1 = get_field('diem_thanh_phan_1_', $post_id);
                        $diem2 = get_field('diem_thanh_phan_2_', $post_id);
                        $cuoiki = get_field('diem_cuoi_ki_', $post_id);
                        
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
                    <tr class="<?php echo $xeploai_class; ?>">
                        <td data-label="<?php esc_html_e('STT', 'qlsv'); ?>"><?php echo $stt; ?></td>
                        <td data-label="<?php esc_html_e('Sinh viên', 'qlsv'); ?>"><?php echo esc_html($sinh_vien); ?></td>
                        <td data-label="<?php esc_html_e('Môn học', 'qlsv'); ?>"><?php echo esc_html($mon_hoc); ?></td>
                        <td data-label="<?php esc_html_e('Lớp', 'qlsv'); ?>"><?php echo esc_html($lop); ?></td>
                        <td data-label="<?php esc_html_e('Điểm TP1', 'qlsv'); ?>" class="diem-cell">
                            <?php echo ($diem1 !== '' ? '<span class="diem-value">' . esc_html($diem1) . '</span>' : 'N/A'); ?>
                        </td>
                        <td data-label="<?php esc_html_e('Điểm TP2', 'qlsv'); ?>" class="diem-cell">
                            <?php echo ($diem2 !== '' ? '<span class="diem-value">' . esc_html($diem2) . '</span>' : 'N/A'); ?>
                        </td>
                        <td data-label="<?php esc_html_e('Điểm cuối kỳ', 'qlsv'); ?>" class="diem-cell">
                            <?php echo ($cuoiki !== '' ? '<span class="diem-value">' . esc_html($cuoiki) . '</span>' : 'N/A'); ?>
                        </td>
                        <td data-label="<?php esc_html_e('Điểm TB', 'qlsv'); ?>" class="diem-tb-cell">
                            <?php echo ($tb !== '' ? '<span class="diem-value diem-tb">' . esc_html($tb) . '</span>' : 'N/A'); ?>
                        </td>
                        <td data-label="<?php esc_html_e('Xếp loại', 'qlsv'); ?>" class="xeploai-cell <?php echo $xeploai_class; ?>">
                            <?php echo ($xeploai !== '' ? '<span class="xeploai-badge">' . esc_html($xeploai) . '</span>' : 'N/A'); ?>
                        </td>
                    </tr>
                    <?php
                        $stt++;
                    endwhile; 
                    wp_reset_postdata();
                    ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<style>
    .bang-diem-container {
        margin-bottom: 30px !important;
    }
    
    .empty-results {
        padding: 40px !important;
        text-align: center !important;
        background: #f9f9f9 !important;
        border-radius: 8px !important;
        border: 1px dashed #ddd !important;
    }
    
    .empty-results .dashicons {
        font-size: 40px !important;
        height: 40px !important;
        width: 40px !important;
        color: #999 !important;
        margin-bottom: 10px !important;
    }
    
    .empty-results p {
        font-size: 16px !important;
        color: #666 !important;
        margin: 0 !important;
    }

    .search-conditions {
        list-style: none !important;
        padding: 0 !important;
        margin: 10px 0 !important;
        text-align: center !important;
    }
    
    .search-conditions li {
        display: inline-block !important;
        margin: 5px 10px !important;
        padding: 5px 10px !important;
        background: #f0f0f0 !important;
        border-radius: 4px !important;
        font-weight: 500 !important;
    }
    
    .reset-search-btn {
        display: inline-block !important;
        margin-top: 15px !important;
        background: #0073aa !important;
        color: #fff !important;
        padding: 8px 15px !important;
        border-radius: 4px !important;
        text-decoration: none !important;
        font-weight: bold !important;
    }
    
    .reset-search-btn:hover {
        background: #005177 !important;
        color: #fff !important;
    }

    .search-result-info {
        padding: 15px !important;
        background: #f9f9f9 !important;
        border-radius: 5px !important;
        margin-bottom: 20px !important;
        border: 1px solid #eee !important;
    }

    .search-result-info h3 {
        margin-top: 0 !important;
        margin-bottom: 10px !important;
        font-size: 18px !important;
        color: #333 !important;
    }
    
    /* Thống kê */
    .academic-stats {
        margin-bottom: 20px !important;
    }
    
    .stats-summary {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 15px !important;
        margin-bottom: 20px !important;
    }
    
    .stat-box {
        flex: 1 !important;
        min-width: 120px !important;
        padding: 15px !important;
        background: #f5f5f5 !important;
        border-radius: 8px !important;
        text-align: center !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05) !important;
    }
    
    .stat-value {
        font-size: 24px !important;
        font-weight: bold !important;
        color: #333 !important;
    }
    
    .stat-label {
        font-size: 14px !important;
        color: #666 !important;
        margin-top: 5px !important;
    }
    
    .stat-box.passed .stat-value {
        color: #28a745 !important;
    }
    
    .stat-box.failed .stat-value {
        color: #dc3545 !important;
    }
    
    /* Bảng điểm */
    .table-responsive {
        overflow-x: auto !important;
    }
    
    .bang-diem-table {
        width: 100% !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
        font-size: 14px !important;
        box-shadow: 0 2px 5px rgba(0,0,0,0.08) !important;
        border-radius: 8px !important;
        overflow: hidden !important;
    }
    
    .bang-diem-table th, 
    .bang-diem-table td {
        padding: 12px 15px !important;
        text-align: left !important;
        border-bottom: 1px solid #eee !important;
    }
    
    .bang-diem-table th {
        background-color: #f8f9fa !important;
        font-weight: 600 !important;
        color: #495057 !important;
        text-transform: uppercase !important;
        font-size: 12px !important;
        letter-spacing: 0.5px !important;
    }
    
    .bang-diem-table tr:last-child td {
        border-bottom: none !important;
    }
    
    .bang-diem-table tr:hover {
        background-color: rgba(0,123,255,0.04) !important;
    }
    
    /* Cột điểm */
    .column-diem, .column-diemtb, .column-stt {
        text-align: center !important;
    }
    
    .diem-cell, .diem-tb-cell, .xeploai-cell {
        text-align: center !important;
    }
    
    .diem-value {
        display: inline-block !important;
        min-width: 40px !important;
        text-align: center !important;
    }
    
    .diem-tb {
        font-weight: bold !important;
    }
    
    /* Xếp loại */
    .xeploai-badge {
        display: inline-block !important;
        padding: 4px 8px !important;
        border-radius: 4px !important;
        font-weight: 500 !important;
        font-size: 12px !important;
        letter-spacing: 0.5px !important;
        min-width: 80px !important;
        text-align: center !important;
    }
    
    tr.gioi .xeploai-badge {
        background-color: #d4edda !important;
        color: #155724 !important;
    }
    
    tr.kha .xeploai-badge {
        background-color: #fff3cd !important;
        color: #856404 !important;
    }
    
    tr.trung-binh .xeploai-badge {
        background-color: #cce5ff !important;
        color: #004085 !important;
    }
    
    tr.yeu .xeploai-badge {
        background-color: #f8d7da !important;
        color: #721c24 !important;
    }
    
    /* Responsive */
    @media screen and (max-width: 768px) {
        .bang-diem-table thead {
            display: none !important;
        }
        
        .bang-diem-table, 
        .bang-diem-table tbody, 
        .bang-diem-table tr, 
        .bang-diem-table td {
            display: block !important;
            width: 100% !important;
        }
        
        .bang-diem-table tr {
            margin-bottom: 15px !important;
            border: 1px solid #ddd !important;
            border-radius: 8px !important;
            overflow: hidden !important;
        }
        
        .bang-diem-table td {
            text-align: right !important;
            padding: 10px 15px !important;
            position: relative !important;
            border-bottom: 1px solid #eee !important;
        }
        
        .bang-diem-table td:last-child {
            border-bottom: 0 !important;
        }
        
        .bang-diem-table td::before {
            content: attr(data-label) !important;
            position: absolute !important;
            left: 15px !important;
            width: 45% !important;
            font-weight: bold !important;
            text-align: left !important;
        }
        
        .diem-cell, .diem-tb-cell, .xeploai-cell {
            text-align: right !important;
        }
        
        .stats-summary {
            gap: 10px !important;
        }
        
        .stat-box {
            min-width: calc(50% - 10px) !important;
            flex: 0 0 calc(50% - 10px) !important;
            padding: 12px !important;
        }
    }
</style> 