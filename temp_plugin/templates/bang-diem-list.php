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
?>

<div class="bang-diem-container">
    <?php if (empty($diem_list->posts)) : ?>
        <p><?php esc_html_e('Không có dữ liệu điểm nào.', 'qlsv'); ?></p>
    <?php else : ?>
        <table class="bang-diem-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('STT', 'qlsv'); ?></th>
                    <th><?php esc_html_e('Sinh viên', 'qlsv'); ?></th>
                    <th><?php esc_html_e('Môn học', 'qlsv'); ?></th>
                    <th><?php esc_html_e('Lớp', 'qlsv'); ?></th>
                    <th><?php esc_html_e('Điểm TP1', 'qlsv'); ?></th>
                    <th><?php esc_html_e('Điểm TP2', 'qlsv'); ?></th>
                    <th><?php esc_html_e('Điểm cuối kỳ', 'qlsv'); ?></th>
                    <th><?php esc_html_e('Điểm TB', 'qlsv'); ?></th>
                    <th><?php esc_html_e('Xếp loại', 'qlsv'); ?></th>
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
                    
                    // Xếp loại
                    $xeploai = '';
                    if ($tb !== '') {
                        if ($tb >= 8.5) $xeploai = 'Giỏi';
                        elseif ($tb >= 7) $xeploai = 'Khá';
                        elseif ($tb >= 5.5) $xeploai = 'Trung bình';
                        else $xeploai = 'Yếu';
                    }
                ?>
                <tr>
                    <td><?php echo $stt; ?></td>
                    <td><?php echo esc_html($sinh_vien); ?></td>
                    <td><?php echo esc_html($mon_hoc); ?></td>
                    <td><?php echo esc_html($lop); ?></td>
                    <td><?php echo ($diem1 !== '' ? esc_html($diem1) : 'N/A'); ?></td>
                    <td><?php echo ($diem2 !== '' ? esc_html($diem2) : 'N/A'); ?></td>
                    <td><?php echo ($cuoiki !== '' ? esc_html($cuoiki) : 'N/A'); ?></td>
                    <td><?php echo ($tb !== '' ? esc_html($tb) : 'N/A'); ?></td>
                    <td><?php echo ($xeploai !== '' ? esc_html($xeploai) : 'N/A'); ?></td>
                </tr>
                <?php
                    $stt++;
                endwhile; 
                wp_reset_postdata();
                ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<style>
    .bang-diem-container {
        overflow-x: auto;
        margin-bottom: 20px;
    }
    .bang-diem-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        text-align: center;
    }
    .bang-diem-table th, 
    .bang-diem-table td {
        padding: 8px;
        border: 1px solid #ddd;
    }
    .bang-diem-table th {
        background-color: #f2f2f2;
        font-weight: bold;
    }
    .bang-diem-table tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    .bang-diem-table tr:hover {
        background-color: #f5f5f5;
    }
</style> 