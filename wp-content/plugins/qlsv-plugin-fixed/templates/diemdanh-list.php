<?php
/**
 * Template hiển thị danh sách điểm danh
 *
 * @package QLSV
 */

// Ngăn chặn truy cập trực tiếp
if (!defined('WPINC')) {
    die;
}

// Dữ liệu từ shortcode
$diemdanh_query = isset($diemdanh_query) ? $diemdanh_query : null;
$all_classes = isset($all_classes) ? $all_classes : array();
$all_courses = isset($all_courses) ? $all_courses : array();
$selected_class = isset($atts['lop_id']) ? $atts['lop_id'] : 0;
$selected_course = isset($atts['monhoc_id']) ? $atts['monhoc_id'] : 0;
?>

<div class="diemdanh-container">
    <!-- Bộ lọc điểm danh -->
    <div class="diemdanh-filter">
        <form class="filter-form" method="get">
            <?php 
            // Giữ các tham số URL khác (nếu cần)
            foreach ($_GET as $key => $value) {
                if (!in_array($key, array('lop', 'monhoc'))) {
                    echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
                }
            }
            ?>
            
            <div class="filter-group">
                <label for="lop_filter"><?php esc_html_e('Lớp:', 'qlsv'); ?></label>
                <select name="lop" id="lop_filter">
                    <option value="0"><?php esc_html_e('-- Tất cả lớp --', 'qlsv'); ?></option>
                    <?php foreach ($all_classes as $class) : ?>
                        <option value="<?php echo esc_attr($class->ID); ?>" <?php selected($selected_class, $class->ID); ?>>
                            <?php echo esc_html($class->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="monhoc_filter"><?php esc_html_e('Môn học:', 'qlsv'); ?></label>
                <select name="monhoc" id="monhoc_filter">
                    <option value="0"><?php esc_html_e('-- Tất cả môn học --', 'qlsv'); ?></option>
                    <?php foreach ($all_courses as $course) : ?>
                        <option value="<?php echo esc_attr($course->ID); ?>" <?php selected($selected_course, $course->ID); ?>>
                            <?php echo esc_html($course->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <button type="submit" class="filter-btn"><?php esc_html_e('Lọc', 'qlsv'); ?></button>
            </div>
        </form>
    </div>
    
    <?php if (!$diemdanh_query || !$diemdanh_query->have_posts()) : ?>
        <p class="no-data"><?php esc_html_e('Không có dữ liệu điểm danh nào phù hợp với điều kiện.', 'qlsv'); ?></p>
    <?php else : ?>
        <!-- Hiển thị danh sách buổi điểm danh -->
        <div class="diemdanh-list">
            <h3><?php echo $diemdanh_query->found_posts; ?> <?php esc_html_e('buổi điểm danh', 'qlsv'); ?></h3>
            
            <table class="diemdanh-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('STT', 'qlsv'); ?></th>
                        <th><?php esc_html_e('Ngày', 'qlsv'); ?></th>
                        <th><?php esc_html_e('Buổi', 'qlsv'); ?></th>
                        <th><?php esc_html_e('Lớp', 'qlsv'); ?></th>
                        <th><?php esc_html_e('Môn học', 'qlsv'); ?></th>
                        <th><?php esc_html_e('Có mặt', 'qlsv'); ?></th>
                        <th><?php esc_html_e('Vắng', 'qlsv'); ?></th>
                        <th><?php esc_html_e('Chi tiết', 'qlsv'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $stt = 1;
                    while ($diemdanh_query->have_posts()) :
                        $diemdanh_query->the_post();
                        $post_id = get_the_ID();
                        
                        // Lấy thông tin cơ bản
                        $mon_hoc_id = get_field('mon_hoc', $post_id);
                        $lop_id = get_field('lop', $post_id);
                        $ngay = get_field('ngay', $post_id);
                        $buoi_hoc = get_field('buoi_hoc', $post_id);
                        $sinh_vien_dd = get_field('sinh_vien_dd', $post_id);
                        
                        // Tên lớp và môn học
                        $lop = $lop_id ? get_the_title($lop_id) : '';
                        $mon_hoc = $mon_hoc_id ? get_the_title($mon_hoc_id) : '';
                        
                        // Format ngày
                        $ngay_format = $ngay ? date_i18n('d/m/Y', strtotime($ngay)) : '';
                        
                        // Tính thống kê điểm danh
                        $count_co_mat = 0;
                        $count_vang = 0;
                        
                        if ($sinh_vien_dd && is_array($sinh_vien_dd)) {
                            foreach ($sinh_vien_dd as $sv) {
                                if ($sv['trang_thai'] === 'co_mat' || $sv['trang_thai'] === 'di_muon' || $sv['trang_thai'] === 've_som') {
                                    $count_co_mat++;
                                } else {
                                    $count_vang++;
                                }
                            }
                        }
                        
                        // Đường dẫn chi tiết
                        $detail_url = add_query_arg('diemdanh_id', $post_id, get_permalink());
                    ?>
                    <tr>
                        <td><?php echo $stt++; ?></td>
                        <td><?php echo esc_html($ngay_format); ?></td>
                        <td><?php echo esc_html($buoi_hoc); ?></td>
                        <td><?php echo esc_html($lop); ?></td>
                        <td><?php echo esc_html($mon_hoc); ?></td>
                        <td><?php echo esc_html($count_co_mat); ?></td>
                        <td><?php echo esc_html($count_vang); ?></td>
                        <td>
                            <a href="#" class="diemdanh-detail-btn" data-post-id="<?php echo $post_id; ?>">
                                <?php esc_html_e('Xem', 'qlsv'); ?>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Modal chi tiết điểm danh -->
        <div id="diemdanh-detail-modal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <div id="diemdanh-detail-content">
                    <p><?php esc_html_e('Đang tải...', 'qlsv'); ?></p>
                </div>
            </div>
        </div>
        
        <!-- JavaScript để xử lý modal -->
        <script>
        jQuery(document).ready(function($) {
            // Mở modal khi nhấp vào nút chi tiết
            $('.diemdanh-detail-btn').on('click', function(e) {
                e.preventDefault();
                var postId = $(this).data('post-id');
                var modal = $('#diemdanh-detail-modal');
                
                // Hiển thị modal
                modal.css('display', 'block');
                
                // Tải dữ liệu chi tiết
                $('#diemdanh-detail-content').html('<p>Đang tải dữ liệu...</p>');
                
                // Lấy thông tin điểm danh
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'get_diemdanh_detail',
                        post_id: postId
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#diemdanh-detail-content').html(response.data);
                        } else {
                            $('#diemdanh-detail-content').html('<p>Có lỗi xảy ra khi tải dữ liệu.</p>');
                        }
                    },
                    error: function() {
                        $('#diemdanh-detail-content').html('<p>Có lỗi xảy ra khi kết nối với server.</p>');
                    }
                });
            });
            
            // Đóng modal khi nhấp vào nút close
            $('.close').on('click', function() {
                $('#diemdanh-detail-modal').css('display', 'none');
            });
            
            // Đóng modal khi nhấp bên ngoài modal
            $(window).on('click', function(e) {
                var modal = $('#diemdanh-detail-modal');
                if ($(e.target).is(modal)) {
                    modal.css('display', 'none');
                }
            });
        });
        </script>
        
        <?php wp_reset_postdata(); ?>
    <?php endif; ?>
</div>

<style>
    /* CSS cho bộ lọc */
    .diemdanh-container {
        margin-bottom: 30px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    }
    .diemdanh-filter {
        margin-bottom: 20px;
        padding: 15px;
        background: #f5f5f5;
        border-radius: 5px;
    }
    .filter-form {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
    }
    .filter-group {
        margin-right: 15px;
        margin-bottom: 10px;
    }
    .filter-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    .filter-group select {
        padding: 8px;
        min-width: 200px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .filter-btn {
        background: #0073aa;
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
    }
    .filter-btn:hover {
        background: #005177;
    }
    
    /* CSS cho bảng điểm danh */
    .diemdanh-list {
        overflow-x: auto;
    }
    .diemdanh-list h3 {
        margin-bottom: 15px;
    }
    .diemdanh-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
    }
    .diemdanh-table th, 
    .diemdanh-table td {
        padding: 10px;
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
    .diemdanh-table tr:hover {
        background-color: #f5f5f5;
    }
    .diemdanh-detail-btn {
        display: inline-block;
        background: #4CAF50;
        color: white;
        text-decoration: none;
        padding: 5px 10px;
        border-radius: 4px;
        text-align: center;
    }
    .diemdanh-detail-btn:hover {
        background: #45a049;
        color: white;
    }
    
    /* CSS cho modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.4);
    }
    .modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 800px;
        border-radius: 5px;
        position: relative;
        max-height: 80vh;
        overflow-y: auto;
    }
    .close {
        color: #aaa;
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 28px;
        font-weight: bold;
    }
    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }
    
    /* CSS cho thông báo không có dữ liệu */
    .no-data {
        padding: 20px;
        background: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 5px;
        text-align: center;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .filter-group {
            width: 100%;
            margin-right: 0;
        }
        .modal-content {
            width: 95%;
            margin: 10% auto;
        }
    }
</style> 